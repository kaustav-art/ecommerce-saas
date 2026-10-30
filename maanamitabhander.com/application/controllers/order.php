<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class order extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    public function track()
    {
        $order    = Static_template_data::get('common_order');
        $searched = (bool) $this->input->get('order_number');

        $data = [
            'title'       => 'Order Tracking - ' . $this->site_name,
            'active_page' => 'track',
            'searched'    => $searched,
            'order'       => $searched ? $order : NULL
        ];

        $this->render('order/track', $data);
    }
}
