<?php
/**
 * SaaS Payment Return Callback Handler (order_callback.php)
 * Verifies Stripe & PayU responses on the server before activating a tenant subscription.
 */
require_once __DIR__ . '/saas_core.php';

$pdo = get_saas_pdo();
$gateway     = strtolower(trim($_GET['gateway'] ?? $_POST['gateway'] ?? ''));
$orderNumber = trim($_GET['order_number'] ?? $_POST['udf1'] ?? $_POST['txnid'] ?? '');
$statusParam = strtolower(trim($_GET['status'] ?? ''));

if ($orderNumber === '') {
    header('Location: ' . saas_base_url('subscribe.php?error=missing_order'));
    exit;
}

// 1. STRIPE CALLBACK VERIFICATION
if ($gateway === 'stripe') {
    if ($statusParam === 'cancelled') {
        $pdo->prepare("UPDATE `saas_payments` SET `payment_status` = 'cancelled', `updated_at` = NOW() WHERE `order_number` = ? AND `payment_status` != 'paid'")
            ->execute([$orderNumber]);
        header('Location: ' . saas_base_url('subscribe.php?retry_order=' . urlencode($orderNumber) . '&payment_error=cancelled'));
        exit;
    }

    $sessionId = trim($_GET['session_id'] ?? '');
    $secretKey = get_saas_setting('stripe_secret_key', '');
    if ($sessionId === '' || $secretKey === '') {
        header('Location: ' . saas_base_url('subscribe.php?retry_order=' . urlencode($orderNumber) . '&payment_error=verification_failed'));
        exit;
    }

    // Verify directly against Stripe API on the server
    $ch = curl_init('https://api.stripe.com/v1/checkout/sessions/' . urlencode($sessionId));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD        => $secretKey . ':',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 20,
    ]);
    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $sessionData = json_decode($resp, true);
    if ($httpCode === 200 && isset($sessionData['payment_status']) && $sessionData['payment_status'] === 'paid') {
        $txId = $sessionData['payment_intent'] ?? $sessionData['id'];
        $result = provision_saas_tenant($orderNumber, $txId, $sessionData);
        if ($result['success']) {
            unset($_SESSION['saas_pending_order_number']);
            header('Location: ' . saas_base_url('subscribe.php?activated_order=' . urlencode($orderNumber)));
            exit;
        }
    }

    $pdo->prepare("UPDATE `saas_payments` SET `payment_status` = 'failed', `updated_at` = NOW() WHERE `order_number` = ? AND `payment_status` != 'paid'")
        ->execute([$orderNumber]);
    header('Location: ' . saas_base_url('subscribe.php?retry_order=' . urlencode($orderNumber) . '&payment_error=stripe_unpaid'));
    exit;
}

// 2. PAYU CALLBACK VERIFICATION
if ($gateway === 'payu') {
    $payuStatus   = strtolower(trim($_POST['status'] ?? 'failure'));
    $txnid        = trim($_POST['txnid'] ?? $orderNumber);
    $mihpayid     = trim($_POST['mihpayid'] ?? '');
    $amount       = trim($_POST['amount'] ?? '');
    $productinfo  = trim($_POST['productinfo'] ?? '');
    $firstname    = trim($_POST['firstname'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $udf1         = trim($_POST['udf1'] ?? '');
    $udf2         = trim($_POST['udf2'] ?? '');
    $udf3         = trim($_POST['udf3'] ?? '');
    $udf4         = trim($_POST['udf4'] ?? '');
    $udf5         = trim($_POST['udf5'] ?? '');
    $postedHash   = strtolower(trim($_POST['hash'] ?? ''));

    $merchantKey  = get_saas_setting('payu_merchant_key', '');
    $merchantSalt = get_saas_setting('payu_merchant_salt', '');

    // Verify PayU reverse SHA-512 hash
    $retHashSeq = "$merchantSalt|$payuStatus||||||$udf5|$udf4|$udf3|$udf2|$udf1|$email|$firstname|$productinfo|$amount|$txnid|$merchantKey";
    $expectedHash = strtolower(hash('sha512', $retHashSeq));

    if ($payuStatus === 'success' && $postedHash !== '' && hash_equals($expectedHash, $postedHash)) {
        $result = provision_saas_tenant($orderNumber, $mihpayid ?: $txnid, $_POST);
        if ($result['success']) {
            unset($_SESSION['saas_pending_order_number']);
            header('Location: ' . saas_base_url('subscribe.php?activated_order=' . urlencode($orderNumber)));
            exit;
        }
    }

    $pdo->prepare("UPDATE `saas_payments` SET `payment_status` = 'failed', `gateway_response` = ?, `updated_at` = NOW() WHERE `order_number` = ? AND `payment_status` != 'paid'")
        ->execute([json_encode($_POST), $orderNumber]);
    header('Location: ' . saas_base_url('subscribe.php?retry_order=' . urlencode($orderNumber) . '&payment_error=payu_failed'));
    exit;
}

header('Location: ' . saas_base_url('subscribe.php'));
exit;
