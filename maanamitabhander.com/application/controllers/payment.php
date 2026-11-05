<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class payment extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    public function stripe($order_number = 'ORD-2026-88412')
    {
        $this->success($order_number);
    }

    public function stripe_success($order_number = 'ORD-2026-88412')
    {
        $this->success($order_number);
    }

    public function stripe_confirm($order_number = 'ORD-2026-88412')
    {
        $this->success($order_number);
    }

    public function razorpay($order_number = 'ORD-2026-88412')
    {
        $this->success($order_number);
    }

    public function razorpay_verify($order_number = 'ORD-2026-88412')
    {
        $this->success($order_number);
    }

    public function payu($order_number = 'ORD-2026-88412')
    {
        $this->success($order_number);
    }

    public function payu_return($order_number = 'ORD-2026-88412')
    {
        $this->success($order_number);
    }

    public function success($order_number = 'ORD-2026-88412')
    {
        $this->load->model('order_model');
        $order = $this->order_model->get_by_order_number($order_number);
        if (!$order) {
            $order = Static_template_data::get('common_order');
        }

        $data = [
            'title'       => 'Order Confirmed - #' . ($order['order_number'] ?? $order_number),
            'active_page' => 'checkout',
            'order'       => $order
        ];

        $this->render('payment/success', $data);
    }

    public function failure($order_number = 'ORD-2026-88412')
    {
        $this->load->model('order_model');
        $order = $this->order_model->get_by_order_number($order_number);
        if (!$order) {
            $order = Static_template_data::get('common_order');
        }

        $data = [
            'title'       => 'Payment Failed - #' . ($order['order_number'] ?? $order_number),
            'active_page' => 'checkout',
            'order'       => $order
        ];

        $this->render('payment/failure', $data);
    }

    /**
     * Dedicated Razorpay Webhook Endpoint
     * URL: /payment/razorpay_webhook
     */
    public function razorpay_webhook()
    {
        return $this->webhook('razorpay');
    }

    /**
     * Unified Webhook Endpoint
     * URL: /payment/webhook/<gateway>
     */
    public function webhook($gateway = 'stripe')
    {
        if ($gateway === 'razorpay') {
            return $this->_handle_razorpay_webhook();
        }

        return $this->output
            ->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'success', 'received' => true]));
    }

    /**
     * Handle Razorpay Webhook Events Server-to-Server
     */
    private function _handle_razorpay_webhook()
    {
        $raw_body = file_get_contents('php://input');
        if (empty($raw_body)) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Empty webhook payload']));
        }

        $signature = $this->input->get_request_header('X-Razorpay-Signature', TRUE);
        if (!$signature && isset($_SERVER['HTTP_X_RAZORPAY_SIGNATURE'])) {
            $signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'];
        }

        $this->load->model('payment_model');
        $this->load->model('order_model');

        // Verify webhook signature if configured
        if (!empty($signature)) {
            $is_valid = $this->payment_model->verify_razorpay_webhook_signature($raw_body, $signature);
            if (!$is_valid) {
                log_message('error', 'Razorpay Webhook: Invalid signature.');
                return $this->output
                    ->set_status_header(400)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['status' => 'error', 'message' => 'Invalid signature']));
            }
        }

        $data = json_decode($raw_body, true);
        if (!is_array($data) || empty($data['event'])) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Malformed JSON payload']));
        }

        $event = $data['event'];
        $payment_entity = $data['payload']['payment']['entity'] ?? [];
        $order_entity   = $data['payload']['order']['entity'] ?? [];

        $payment_id       = $payment_entity['id'] ?? null;
        $gateway_order_id = $payment_entity['order_id'] ?? ($order_entity['id'] ?? null);
        $order_number     = $order_entity['receipt'] ?? ($payment_entity['notes']['order_number'] ?? null);

        // Attempt to find pending checkout intent
        $pending = null;
        if ($this->db->table_exists('pending_checkouts')) {
            $this->db->where('status', 'pending');
            $this->db->group_start();
            if (!empty($order_number)) {
                $this->db->where('temp_order_number', $order_number);
            }
            if (!empty($gateway_order_id)) {
                $this->db->or_where('gateway_order_id', $gateway_order_id);
            }
            $this->db->group_end();
            $pending = $this->db->get('pending_checkouts')->row_array();
            if ($pending && empty($order_number)) {
                $order_number = $pending['temp_order_number'];
            }
        }

        // Align tenant context
        if (!empty($pending['tenant_id'])) {
            $this->tenant_id = (int)$pending['tenant_id'];
        }

        // Check if order already exists in orders table
        $existing_order = null;
        if (!empty($order_number)) {
            $existing_order = $this->order_model->get_by_order_number($order_number);
            if ($existing_order && !empty($existing_order['tenant_id'])) {
                $this->tenant_id = (int)$existing_order['tenant_id'];
            }
        }

        if ($event === 'payment.captured' || $event === 'order.paid') {
            if ($existing_order) {
                // Order already created (e.g. from checkout or COD pay-online)
                if ($existing_order['payment_status'] !== 'paid') {
                    $this->order_model->update_payment(
                        $existing_order['order_number'],
                        'paid',
                        $payment_id,
                        [
                            'gateway'             => 'razorpay',
                            'razorpay_order_id'   => $gateway_order_id,
                            'razorpay_payment_id' => $payment_id,
                            'webhook_event'       => $event,
                            'paid_at'             => date('Y-m-d H:i:s')
                        ]
                    );
                    $this->order_model->update_status($existing_order['order_number'], 'processing');
                }
            } elseif ($pending) {
                // Checkout modal was closed or frontend connection dropped, but customer completed payment:
                // Create the verified order now and deduct inventory
                $order_data = json_decode($pending['order_data'], true);
                $items_data = json_decode($pending['items_data'], true);

                if (is_array($order_data) && is_array($items_data)) {
                    $order_data['payment_status'] = 'paid';
                    $order_data['payment_method'] = 'razorpay';
                    $order_data['order_number']   = $pending['temp_order_number'];

                    $created_order_number = $this->order_model->create_order($order_data, $items_data);
                    $this->order_model->update_payment(
                        $created_order_number,
                        'paid',
                        $payment_id,
                        [
                            'gateway'             => 'razorpay',
                            'razorpay_order_id'   => $gateway_order_id,
                            'razorpay_payment_id' => $payment_id,
                            'webhook_event'       => $event,
                            'paid_at'             => date('Y-m-d H:i:s')
                        ]
                    );
                    $this->order_model->update_status($created_order_number, 'processing');

                    $this->db->where('id', $pending['id'])
                             ->update('pending_checkouts', ['status' => 'completed']);
                }
            }
        } elseif ($event === 'payment.failed') {
            if ($existing_order && $existing_order['payment_status'] === 'pending') {
                $this->order_model->update_payment($existing_order['order_number'], 'failed');
            }
            if ($pending) {
                $this->db->where('id', $pending['id'])
                         ->update('pending_checkouts', ['status' => 'failed']);
            }
        }

        return $this->output
            ->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'   => 'success',
                'event'    => $event,
                'handled'  => true
            ]));
    }
}
