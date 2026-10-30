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
        $order = Static_template_data::get('common_order');

        $data = [
            'title'       => 'Order Confirmed - #' . $order['order_number'],
            'active_page' => 'checkout',
            'order'       => $order
        ];

        $this->render('payment/success', $data);
    }

    public function failure($order_number = 'ORD-2026-88412')
    {
        $order = Static_template_data::get('common_order');

        $data = [
            'title'       => 'Payment Failed - #' . $order['order_number'],
            'active_page' => 'checkout',
            'order'       => $order
        ];

        $this->render('payment/failure', $data);
    }

    public function webhook($gateway = 'stripe')
    {
        $this->output
            ->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'success', 'received' => true]));
    }
}
