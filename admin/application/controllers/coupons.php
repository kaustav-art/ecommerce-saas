<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class coupons extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('marketing_model');
    }

    public function index()
    {
        $this->require_permission('settings.manage');

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('code', 'Coupon Code', 'required|trim');
            $this->form_validation->set_rules('discount_value', 'Discount Value', 'required|numeric');

            if ($this->form_validation->run() === TRUE) {
                $id = $this->input->post('id');
                $data = [
                    'code'           => strtoupper(trim($this->input->post('code', TRUE))),
                    'discount_type'  => $this->input->post('discount_type', TRUE) ?: 'percent',
                    'discount_value' => (float) $this->input->post('discount_value'),
                    'min_spend'      => (float) $this->input->post('min_spend'),
                    'usage_limit'    => (int) $this->input->post('usage_limit'),
                    'expires_at'     => $this->input->post('expires_at') ?: NULL,
                    'status'         => $this->input->post('status', TRUE) ?: 'active'
                ];

                $this->marketing_model->save_coupon($data, $id);
                $this->session->set_flashdata('success', 'Coupon saved successfully.');
                redirect('coupons');
            }
        }

        $data = [
            'title'          => 'Discount Coupons | Admin',
            'active_menu'    => 'marketing',
            'active_submenu' => 'coupons',
            'coupons'        => $this->marketing_model->get_coupons()
        ];
        $this->render('coupons/index', $data);
    }

    public function delete($id)
    {
        $this->require_permission('settings.manage');
        $this->marketing_model->delete_coupon($id);
        $this->session->set_flashdata('success', 'Coupon deleted.');
        redirect('coupons');
    }
}
