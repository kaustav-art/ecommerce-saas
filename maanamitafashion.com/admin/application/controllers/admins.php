<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class admins extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin_model');
        $this->load->model('role_model');
    }

    public function index()
    {
        $this->require_permission('admins.manage');

        $admins = $this->admin_model->get_all();
        $roles  = $this->role_model->get_all();

        $data = [
            'title'          => 'Admin Operators & Staff | Admin',
            'active_menu'    => 'users_management',
            'active_submenu' => 'admins_list',
            'admins'         => $admins,
            'roles'          => $roles
        ];

        $this->render('admins/index', $data);
    }

    public function add()
    {
        $this->require_permission('admins.manage');

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('name', 'Full Name', 'required|trim');
            $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[admins.email]');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
            $this->form_validation->set_rules('role_id', 'Role', 'required|numeric');

            if ($this->form_validation->run() === TRUE) {
                $insert_data = [
                    'name'     => $this->input->post('name', TRUE),
                    'email'    => $this->input->post('email', TRUE),
                    'password' => password_hash($this->input->post('password'), PASSWORD_BCRYPT),
                    'role_id'  => (int) $this->input->post('role_id'),
                    'phone'    => $this->input->post('phone', TRUE),
                    'status'   => $this->input->post('status', TRUE) ?: 'active'
                ];

                $this->admin_model->create($insert_data);
                $this->session->set_flashdata('success', 'Operator account created successfully.');
                redirect('admins');
            }
        }

        $data = [
            'title'          => 'Add New Operator | Admin',
            'active_menu'    => 'users_management',
            'active_submenu' => 'admins_add',
            'roles'          => $this->role_model->get_all()
        ];

        $this->render('admins/add', $data);
    }

    public function edit($id)
    {
        $this->require_permission('admins.manage');

        $admin = $this->admin_model->get_by_id($id);
        if (!$admin) {
            $this->session->set_flashdata('error', 'Administrator not found.');
            redirect('admins');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('name', 'Full Name', 'required|trim');
            $this->form_validation->set_rules('role_id', 'Role', 'required|numeric');

            if ($this->form_validation->run() === TRUE) {
                $update_data = [
                    'name'    => $this->input->post('name', TRUE),
                    'role_id' => (int) $this->input->post('role_id'),
                    'phone'   => $this->input->post('phone', TRUE),
                    'status'  => $this->input->post('status', TRUE) ?: 'active'
                ];

                $new_pass = $this->input->post('password');
                if (!empty($new_pass)) {
                    $update_data['password'] = password_hash($new_pass, PASSWORD_BCRYPT);
                }

                $this->admin_model->update($id, $update_data);
                $this->session->set_flashdata('success', 'Operator account updated successfully.');
                redirect('admins');
            }
        }

        $data = [
            'title'          => 'Edit Operator: ' . $admin['name'] . ' | Admin',
            'active_menu'    => 'users_management',
            'active_submenu' => 'admins_list',
            'admin_user'     => $admin,
            'roles'          => $this->role_model->get_all()
        ];

        $this->render('admins/edit', $data);
    }

    public function delete($id)
    {
        $this->require_permission('admins.manage');

        // Prevent self deletion or deletion of primary super admin
        if ((int) $id === (int) $this->current_admin['id'] || (int) $id === 1) {
            $this->session->set_flashdata('error', 'Cannot delete primary administrator or current active account.');
            redirect('admins');
        }

        $this->admin_model->delete($id);
        $this->session->set_flashdata('success', 'Operator account removed.');
        redirect('admins');
    }
}
