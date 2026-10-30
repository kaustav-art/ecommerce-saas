<?php
/**
 * Asynchronous SaaS Subscription Payment Webhook Endpoint (saas_webhook.php)
 * Supports Stripe (checkout.session.completed, payment_intent.succeeded)
 * and Razorpay (order.paid, payment.captured) with cryptographic signature verification.
 * Idempotently activates the tenant store via provision_saas_tenant().
 */
require_once __DIR__ . '/saas_core.php';

header('Content-Type: application/json; charset=utf-8');

$pdo     = get_saas_pdo();
$gateway = strtolower(trim($_GET['gateway'] ?? 'stripe'));
$rawBody = file_get_contents('php://input');

if ($rawBody === '' || $rawBody === false) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Empty webhook payload']);
    exit;
}

// 1. STRIPE WEBHOOK
if ($gateway === 'stripe') {
    $webhookSecret = get_saas_setting('stripe_webhook_secret', '');
    $sigHeader     = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

    if ($webhookSecret !== '') {
        if ($sigHeader === '') {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing Stripe-Signature header']);
            exit;
        }
        $parts = explode(',', $sigHeader);
        $timestamp = '';
        $v1Sig = '';
        foreach ($parts as $p) {
            [$k, $v] = array_pad(explode('=', trim($p), 2), 2, '');
            if ($k === 't') $timestamp = $v;
            if ($k === 'v1') $v1Sig = $v;
        }
        $signedPayload = $timestamp . '.' . $rawBody;
        $expectedSig   = hash_hmac('sha256', $signedPayload, $webhookSecret);
        if (!hash_equals($expectedSig, $v1Sig)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid Stripe webhook signature']);
            exit;
        }
    }

    $event = json_decode($rawBody, true);
    $type  = $event['type'] ?? '';
    $obj   = $event['data']['object'] ?? [];

    if ($type === 'checkout.session.completed' && ($obj['payment_status'] ?? '') === 'paid') {
        $orderNumber = $obj['client_reference_id'] ?? ($obj['metadata']['order_number'] ?? '');
        $txId        = $obj['payment_intent'] ?? ($obj['id'] ?? '');
        if ($orderNumber !== '') {
            $res = provision_saas_tenant($orderNumber, $txId, $event);
            echo json_encode(['status' => 'ok', 'result' => $res]);
            exit;
        }
    } elseif ($type === 'payment_intent.payment_failed') {
        $orderNumber = $obj['metadata']['order_number'] ?? '';
        if ($orderNumber !== '') {
            $pdo->prepare("UPDATE `saas_payments` SET `payment_status` = 'failed', `updated_at` = NOW() WHERE `order_number` = ? AND `payment_status` != 'paid'")
                ->execute([$orderNumber]);
        }
    }

    echo json_encode(['status' => 'ignored', 'type' => $type]);
    exit;
}

// 2. RAZORPAY WEBHOOK
if ($gateway === 'razorpay') {
    $keySecret = get_saas_setting('razorpay_key_secret', '');
    $sigHeader = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

    if ($keySecret !== '' && $sigHeader !== '') {
        $expectedSig = hash_hmac('sha256', $rawBody, $keySecret);
        if (!hash_equals($expectedSig, $sigHeader)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid Razorpay webhook signature']);
            exit;
        }
    }

    $event   = json_decode($rawBody, true);
    $evName  = $event['event'] ?? '';
    $paymentEntity = $event['payload']['payment']['entity'] ?? [];
    $orderNumber   = $paymentEntity['notes']['order_number'] ?? '';
    $txId          = $paymentEntity['id'] ?? '';

    if (in_array($evName, ['order.paid', 'payment.captured'], true) && $orderNumber !== '') {
        $res = provision_saas_tenant($orderNumber, $txId, $event);
        echo json_encode(['status' => 'ok', 'result' => $res]);
        exit;
    }

    echo json_encode(['status' => 'ignored', 'event' => $evName]);
    exit;
}

http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Unsupported gateway']);
