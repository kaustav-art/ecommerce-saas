<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class customer_groups extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('customer_group_model');
    }

    public function index()
    {
        $this->require_permission('customers.view');

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('name', 'Group Name', 'required|trim');
            $this->form_validation->set_rules('discount_percent', 'Discount %', 'required|numeric');

            if ($this->form_validation->run() === TRUE) {
                $id = $this->input->post('id');
                $data = [
                    'name'             => $this->input->post('name', TRUE),
                    'discount_percent' => (float) $this->input->post('discount_percent'),
                    'description'      => $this->input->post('description', TRUE)
                ];

                if (!empty($id)) {
                    $this->customer_group_model->update($id, $data);
                    $this->session->set_flashdata('success', 'Customer group updated successfully.');
                } else {
                    $this->customer_group_model->create($data);
                    $this->session->set_flashdata('success', 'Customer group created successfully.');
                }
                redirect('customer_groups');
            }
        }

        $data = [
            'title'          => 'Customer Groups | Admin',
            'active_menu'    => 'customers',
            'active_submenu' => 'customer_groups',
            'groups'         => $this->customer_group_model->get_all()
        ];
        $this->render('customer_groups/index', $data);
    }

    public function delete($id)
    {
        $this->require_permission('customers.view');
        $this->customer_group_model->delete($id);
        $this->session->set_flashdata('success', 'Customer group deleted.');
        redirect('customer_groups');
    }
}
