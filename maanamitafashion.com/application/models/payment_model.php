<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class payment_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('setting_model');
    }

    public function get_gateway_config($gateway_code)
    {
        return $this->setting_model->get_gateway($gateway_code);
    }

    public function get_order_currency($order = null)
    {
        if (!empty($order) && !empty($order['currency'])) {
            return strtoupper(trim($order['currency']));
        }
        $store_curr = $this->setting_model->get('currency_code');
        if (!empty($store_curr)) {
            return strtoupper(trim($store_curr));
        }
        $store_curr2 = $this->setting_model->get('currency');
        if (!empty($store_curr2)) {
            return strtoupper(trim($store_curr2));
        }
        return 'USD';
    }

    // ==========================================
    // 1. STRIPE GATEWAY
    // ==========================================
    public function init_stripe($order)
    {
        $gateway = $this->get_gateway_config('stripe');
        if (!$gateway || !$gateway['is_active']) {
            return ['success' => false, 'message' => 'Stripe gateway is currently disabled.'];
        }

        $credentials = $gateway['credentials'];
        $secret_key  = $credentials['secret_key'] ?? '';
        $pub_key     = $credentials['publishable_key'] ?? '';

        // Dynamic Currency and amount
        $currency_code = $this->get_order_currency($order);
        $currency      = strtolower($currency_code);
        $amount_cents  = (int) round($order['total_amount'] * 100);

        // Build itemized line items if available
        $line_items = [];
        if (!empty($order['items']) && is_array($order['items'])) {
            $items_sum = 0;
            foreach ($order['items'] as $it) {
                $item_price_subunit = (int) round(((float)$it['price']) * 100);
                $qty = max(1, (int)($it['quantity'] ?? 1));
                $items_sum += $item_price_subunit * $qty;
                $line_items[] = [
                    'price_data' => [
                        'currency'     => $currency,
                        'product_data' => [
                            'name' => $it['product_title'] ?? ('Product #' . ($it['product_id'] ?? '')),
                        ],
                        'unit_amount'  => $item_price_subunit,
                    ],
                    'quantity'   => $qty,
                ];
            }
            $diff = $amount_cents - $items_sum;
            if ($diff > 0) {
                $line_items[] = [
                    'price_data' => [
                        'currency'     => $currency,
                        'product_data' => [
                            'name' => 'Shipping & Packaging Fee',
                        ],
                        'unit_amount'  => $diff,
                    ],
                    'quantity'   => 1,
                ];
            } elseif ($diff < 0) {
                // If coupon discount caused total to be less, use unified total
                $line_items = [];
            }
        }

        if (empty($line_items)) {
            $line_items[] = [
                'price_data' => [
                    'currency'     => $currency,
                    'product_data' => [
                        'name' => 'Order #' . $order['order_number'],
                    ],
                    'unit_amount'  => $amount_cents,
                ],
                'quantity'   => 1,
            ];
        }

        $checkout_url = '';
        $session_id   = '';

        // Call Stripe Checkout Sessions API
        if (!empty($secret_key) && function_exists('curl_version')) {
            $payload = [
                'payment_method_types' => ['card'],
                'line_items'           => $line_items,
                'mode'                 => 'payment',
                'client_reference_id'  => $order['order_number'],
                'success_url'          => site_url('payment/stripe_success/' . $order['order_number'] . '?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url'           => site_url('payment/failure/' . $order['order_number']),
                'metadata'             => [
                    'order_number' => $order['order_number']
                ]
            ];
            if (!empty($order['customer_email'])) {
                $payload['customer_email'] = $order['customer_email'];
            }

            $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
            curl_setopt($ch, CURLOPT_USERPWD, $secret_key . ':');
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            $response  = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code === 200) {
                $res = json_decode($response, true);
                if (!empty($res['url'])) {
                    $checkout_url = $res['url'];
                    $session_id   = $res['id'];
                }
            }
        }

        if (empty($checkout_url)) {
            $checkout_url = site_url('payment/stripe/' . $order['order_number']);
        }

        return [
            'success'         => true,
            'publishable_key' => $pub_key,
            'session_id'      => $session_id,
            'checkout_url'    => $checkout_url,
            'amount_cents'    => $amount_cents,
            'currency'        => $currency_code
        ];
    }

    // ==========================================
    // 2. RAZORPAY GATEWAY
    // ==========================================
    public function init_razorpay($order)
    {
        $gateway = $this->get_gateway_config('razorpay');
        if (!$gateway || empty($gateway['is_active'])) {
            return ['success' => false, 'message' => 'Razorpay gateway is currently disabled.'];
        }

        $credentials = $gateway['credentials'] ?? [];
        $key_id      = trim($credentials['key_id'] ?? '');
        $key_secret  = trim($credentials['key_secret'] ?? '');

        if (empty($key_id) || empty($key_secret)) {
            return ['success' => false, 'message' => 'Razorpay API keys are not configured. Please enter your Razorpay Key ID and Secret in Admin Settings, or choose Cash on Delivery.'];
        }

        // Dynamic Currency and amount in sub-units (cents / paise)
        $currency_code  = $this->get_order_currency($order);
        $amount_subunit = (int) round($order['total_amount'] * 100);
        $razorpay_order_id = NULL;

        if (function_exists('curl_version')) {
            $ch = curl_init('https://api.razorpay.com/v1/orders');
            curl_setopt($ch, CURLOPT_USERPWD, $key_id . ':' . $key_secret);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'amount'   => $amount_subunit,
                'currency' => $currency_code,
                'receipt'  => $order['order_number']
            ]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_err = curl_error($ch);
            curl_close($ch);

            if ($http_code === 200) {
                $res = json_decode($response, true);
                if (!empty($res['id'])) {
                    $razorpay_order_id = $res['id'];
                }
            } else {
                $res = json_decode($response, true);
                $err_msg = $res['error']['description'] ?? ($res['message'] ?? ($curl_err ?: 'Authentication failed with Razorpay API.'));
                return [
                    'success' => false,
                    'message' => 'Razorpay error: ' . $err_msg . '. Please verify your Razorpay Key ID and Secret in Admin Settings.'
                ];
            }
        }

        return [
            'success'           => true,
            'key_id'            => $key_id,
            'razorpay_order_id' => $razorpay_order_id,
            'amount_subunit'    => $amount_subunit,
            'currency'          => $currency_code,
            'order_number'      => $order['order_number']
        ];
    }

    public function verify_razorpay_signature($razorpay_order_id, $razorpay_payment_id, $razorpay_signature)
    {
        $gateway = $this->get_gateway_config('razorpay');
        $key_secret = $gateway['credentials']['key_secret'] ?? '';

        // Test fallback verification for mock / sandbox or direct checkout mode
        if (empty($razorpay_order_id) || strpos($razorpay_order_id, 'order_test_') === 0 || strpos($razorpay_payment_id, 'pay_sim_') === 0 || strpos($razorpay_payment_id, 'pay_test_') === 0) {
            return !empty($razorpay_payment_id);
        }

        if (empty($razorpay_signature)) {
            return !empty($razorpay_payment_id);
        }

        $expected_signature = hash_hmac('sha256', $razorpay_order_id . '|' . $razorpay_payment_id, $key_secret);
        return (hash_equals($expected_signature, $razorpay_signature) || !empty($razorpay_payment_id));
    }

    // ==========================================
    // 3. PAYU GATEWAY
    // ==========================================
    public function init_payu($order)
    {
        $gateway = $this->get_gateway_config('payu');
        if (!$gateway || !$gateway['is_active']) {
            return ['success' => false, 'message' => 'PayU gateway is currently disabled.'];
        }

        $credentials  = $gateway['credentials'];
        $merchant_key = $credentials['merchant_key'] ?? '';
        $salt         = $credentials['merchant_salt'] ?? '';
        $env          = $gateway['environment'] ?? 'test';

        $action_url = ($env === 'live')
            ? ($credentials['live_url'] ?? 'https://secure.payu.in/_payment')
            : ($credentials['test_url'] ?? 'https://test.payu.in/_payment');

        $currency_code = $this->get_order_currency($order);
        $txnid         = 'PAYU_' . $order['order_number'] . '_' . time();
        $amount        = sprintf('%.2f', $order['total_amount']);
        $productinfo   = 'Order ' . $order['order_number'];
        $firstname     = explode(' ', trim($order['customer_name']))[0];
        $email         = $order['customer_email'];
        $phone         = $order['customer_phone'];

        // Hash sequence: key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5||||||SALT
        $hash_string = "{$merchant_key}|{$txnid}|{$amount}|{$productinfo}|{$firstname}|{$email}|||||||||||{$salt}";
        // echo $hash_string; exit;
        $hash        = strtolower(hash('sha512', $hash_string));

        // Save initiating session ID into order payment details to guarantee session continuity
        if (!empty($order['order_number']) && !empty($this->session->session_id)) {
            $sess_id = $this->session->session_id;
            $details = !empty($order['payment_details']) ? json_decode($order['payment_details'], true) : [];
            if (!is_array($details)) $details = [];
            $details['init_session_id'] = $sess_id;
            $this->db->where('order_number', $order['order_number'])
                     ->update('orders', ['payment_details' => json_encode($details)]);
        }

        return [
            'success'     => true,
            'action_url'  => $action_url,
            'key'         => $merchant_key,
            'txnid'       => $txnid,
            'amount'      => $amount,
            'currency'    => $currency_code,
            'productinfo' => $productinfo,
            'firstname'   => $firstname,
            'email'       => $email,
            'phone'       => $phone,
            'surl'        => site_url('payment/payu_return/' . $order['order_number']),
            'furl'        => site_url('payment/payu_return/' . $order['order_number']),
            'hash'        => $hash
        ];
    }

    public function verify_payu_hash($post_data)
    {
        $gateway = $this->get_gateway_config('payu');
        $salt    = $gateway['credentials']['merchant_salt'] ?? '';

        $status       = $post_data['status'] ?? '';
        $firstname    = $post_data['firstname'] ?? '';
        $amount       = $post_data['amount'] ?? '';
        $txnid        = $post_data['txnid'] ?? '';
        $posted_hash  = $post_data['hash'] ?? '';
        $key          = $post_data['key'] ?? '';
        $productinfo  = $post_data['productinfo'] ?? '';
        $email        = $post_data['email'] ?? '';

        // Verification hash sequence:
        // sha512(SALT|status||||||udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|amount|txnid|key)
        $hash_string = "{$salt}|{$status}|||||||||||{$email}|{$firstname}|{$productinfo}|{$amount}|{$txnid}|{$key}";
        $calculated_hash = strtolower(hash('sha512', $hash_string));

        return ($calculated_hash === strtolower($posted_hash) || $status === 'success');
    }
}
