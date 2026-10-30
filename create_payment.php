<?php
/**
 * Multi-Gateway SaaS Payment Initializer (create_payment.php)
 * Reads gateway credentials exclusively from Superadmin saas_settings.
 * Never exposes secret keys to the client.
 */
require_once __DIR__ . '/saas_core.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = get_saas_pdo();
$orderNumber = trim($_POST['order_number'] ?? '');
$gateway     = strtolower(trim($_POST['gateway'] ?? ''));

if ($orderNumber === '' || $gateway === '') {
    echo json_encode(['success' => false, 'message' => 'Order number and payment gateway are required.']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM `saas_payments` WHERE `order_number` = ? LIMIT 1");
$stmt->execute([$orderNumber]);
$payment = $stmt->fetch();

if (!$payment) {
    echo json_encode(['success' => false, 'message' => 'Pending subscription order not found.']);
    exit;
}
if ($payment['payment_status'] === 'paid') {
    echo json_encode(['success' => false, 'message' => 'This subscription order has already been paid.']);
    exit;
}

$plan   = get_saas_plan_by_id_or_slug($payment['plan_id']);
$amount = (float)$payment['amount'];
$curr   = strtoupper($payment['currency'] ?: 'USD');

// Update selected gateway on the pending payment record
$pdo->prepare("UPDATE `saas_payments` SET `gateway` = ?, `payment_status` = 'pending', `updated_at` = NOW() WHERE `id` = ?")
    ->execute([$gateway, (int)$payment['id']]);

// 1. STRIPE CHECKOUT SESSION
if ($gateway === 'stripe') {
    if (get_saas_setting('stripe_enabled', '0') !== '1') {
        echo json_encode(['success' => false, 'message' => 'Stripe gateway is currently disabled by Superadmin.']);
        exit;
    }
    $secretKey = get_saas_setting('stripe_secret_key', '');
    if ($secretKey === '') {
        echo json_encode(['success' => false, 'message' => 'Stripe secret key is not configured in Superadmin settings.']);
        exit;
    }

    $successUrl = saas_base_url('order_callback.php?gateway=stripe&order_number=' . urlencode($orderNumber) . '&session_id={CHECKOUT_SESSION_ID}');
    $cancelUrl  = saas_base_url('order_callback.php?gateway=stripe&status=cancelled&order_number=' . urlencode($orderNumber));

    $unitAmount = (int)round($amount * 100);
    $postFields = http_build_query([
        'payment_method_types' => ['card'],
        'mode'                 => 'payment',
        'customer_email'       => $payment['customer_email'],
        'client_reference_id'  => $orderNumber,
        'line_items'           => [[
            'price_data' => [
                'currency'     => strtolower($curr),
                'unit_amount'  => $unitAmount,
                'product_data' => [
                    'name'        => ($plan['name'] ?? 'SaaS Plan') . ' — ' . ucfirst($payment['chosen_template']) . ' Store',
                    'description' => 'Store: ' . $payment['business_name'] . ' (' . $payment['store_slug'] . ')',
                ],
            ],
            'quantity' => 1,
        ]],
        'metadata' => [
            'order_number' => $orderNumber,
            'store_slug'   => $payment['store_slug'],
            'template'     => $payment['chosen_template'],
            'plan_id'      => $payment['plan_id'],
        ],
        'success_url' => $successUrl,
        'cancel_url'  => $cancelUrl,
    ]);

    $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_USERPWD        => $secretKey . ':',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 20,
    ]);
    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($resp, true);
    if ($httpCode === 200 && !empty($data['url'])) {
        $pdo->prepare("UPDATE `saas_payments` SET `gateway_session_id` = ? WHERE `id` = ?")
            ->execute([$data['id'], (int)$payment['id']]);
        echo json_encode([
            'success'      => true,
            'gateway'      => 'stripe',
            'checkout_url' => $data['url'],
            'session_id'   => $data['id'],
        ]);
        exit;
    }

    $errMsg = $data['error']['message'] ?? 'Could not connect to Stripe API. Check your network or sandbox settings.';
    echo json_encode(['success' => false, 'message' => 'Stripe initialization error: ' . $errMsg]);
    exit;
}

// 2. RAZORPAY ORDER CREATION
if ($gateway === 'razorpay') {
    if (get_saas_setting('razorpay_enabled', '0') !== '1') {
        echo json_encode(['success' => false, 'message' => 'Razorpay gateway is currently disabled by Superadmin.']);
        exit;
    }
    $keyId     = get_saas_setting('razorpay_key_id', '');
    $keySecret = get_saas_setting('razorpay_key_secret', '');
    if ($keyId === '' || $keySecret === '') {
        echo json_encode(['success' => false, 'message' => 'Razorpay credentials are not configured in Superadmin settings.']);
        exit;
    }

    $rzpCurrency = in_array($curr, ['INR', 'USD', 'EUR', 'GBP'], true) ? $curr : 'INR';
    $amountSubunits = (int)round($amount * 100);

    $payload = json_encode([
        'amount'   => $amountSubunits,
        'currency' => $rzpCurrency,
        'receipt'  => $orderNumber,
        'notes'    => [
            'order_number'  => $orderNumber,
            'business_name' => $payment['business_name'],
            'template'      => $payment['chosen_template'],
        ],
    ]);

    $ch = curl_init('https://api.razorpay.com/v1/orders');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_USERPWD        => $keyId . ':' . $keySecret,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $rzpOrder = json_decode($resp, true);
    $rzpOrderId = ($httpCode === 200 && !empty($rzpOrder['id'])) ? $rzpOrder['id'] : '';

    if ($rzpOrderId !== '') {
        $pdo->prepare("UPDATE `saas_payments` SET `gateway_session_id` = ? WHERE `id` = ?")
            ->execute([$rzpOrderId, (int)$payment['id']]);
    }

    echo json_encode([
        'success'           => true,
        'gateway'           => 'razorpay',
        'key_id'            => $keyId, // Public Key ID only — never secret!
        'razorpay_order_id' => $rzpOrderId,
        'amount'            => $amountSubunits,
        'currency'          => $rzpCurrency,
        'order_number'      => $orderNumber,
        'customer_name'     => $payment['customer_name'],
        'customer_email'    => $payment['customer_email'],
        'customer_phone'    => $payment['customer_phone'],
        'business_name'     => $payment['business_name'],
    ]);
    exit;
}

// 3. PAYU HOSTED CHECKOUT
if ($gateway === 'payu') {
    if (get_saas_setting('payu_enabled', '0') !== '1') {
        echo json_encode(['success' => false, 'message' => 'PayU gateway is currently disabled by Superadmin.']);
        exit;
    }
    $merchantKey  = get_saas_setting('payu_merchant_key', '');
    $merchantSalt = get_saas_setting('payu_merchant_salt', '');
    $env          = get_saas_setting('payu_environment', 'test');
    if ($merchantKey === '' || $merchantSalt === '') {
        echo json_encode(['success' => false, 'message' => 'PayU merchant credentials are not configured in Superadmin settings.']);
        exit;
    }

    $actionUrl   = ($env === 'live') ? 'https://secure.payu.in/_payment' : 'https://test.payu.in/_payment';
    $txnid       = $orderNumber;
    $amountFormatted = number_format($amount, 2, '.', '');
    $productinfo = 'SaaS Plan ' . ($plan['name'] ?? '') . ' (' . $payment['chosen_template'] . ')';
    $firstname   = explode(' ', trim($payment['customer_name']))[0] ?: 'Merchant';
    $email       = $payment['customer_email'];
    $udf1        = $orderNumber;
    $udf2        = $payment['store_slug'];
    $udf3        = $payment['chosen_template'];
    $udf4        = '';
    $udf5        = '';

    $hashString = "$merchantKey|$txnid|$amountFormatted|$productinfo|$firstname|$email|$udf1|$udf2|$udf3|$udf4|$udf5||||||$merchantSalt";
    $hash = strtolower(hash('sha512', $hashString));

    echo json_encode([
        'success' => true,
        'gateway' => 'payu',
        'action'  => $actionUrl,
        'params'  => [
            'key'         => $merchantKey,
            'txnid'       => $txnid,
            'amount'      => $amountFormatted,
            'productinfo' => $productinfo,
            'firstname'   => $firstname,
            'email'       => $email,
            'phone'       => $payment['customer_phone'],
            'udf1'        => $udf1,
            'udf2'        => $udf2,
            'udf3'        => $udf3,
            'surl'        => saas_base_url('order_callback.php?gateway=payu'),
            'furl'        => saas_base_url('order_callback.php?gateway=payu&status=failed'),
            'hash'        => $hash,
        ],
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unsupported payment gateway.']);
