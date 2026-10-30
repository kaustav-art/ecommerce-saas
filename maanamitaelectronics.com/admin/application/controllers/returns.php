<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class returns extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('return_model');
        $this->load->model('order_model');
    }

    public function index()
    {
        $this->require_permission('orders.manage');

        $filters = [
            'status' => $this->input->get('status'),
            'type'   => $this->input->get('type')
        ];

        $returns = $this->return_model->get_all($filters);

        $data = [
            'title'          => 'Returns & Refunds | Admin',
            'active_menu'    => 'sales',
            'active_submenu' => 'returns',
            'returns'        => $returns,
            'filters'        => $filters
        ];

        $this->render('returns/index', $data);
    }

    public function update_status($id)
    {
        $this->require_permission('orders.manage');
        $status      = $this->input->post('status', TRUE);
        $admin_notes = $this->input->post('admin_notes', TRUE);

        if ($status) {
            $this->return_model->update_status($id, $status, $admin_notes);
            $this->session->set_flashdata('success', 'Return request status updated to ' . ucfirst($status) . '.');
        }
        redirect('returns');
    }
}
