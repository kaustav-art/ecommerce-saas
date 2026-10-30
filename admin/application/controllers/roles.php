<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class roles extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('role_model');
    }

    public function index()
    {
        $this->require_permission('roles.manage');

        $roles = $this->role_model->get_all();

        $data = [
            'title'          => 'Role-Based Access Control (RBAC) | Admin',
            'active_menu'    => 'users_management',
            'active_submenu' => 'roles',
            'roles'          => $roles
        ];

        $this->render('roles/index', $data);
    }

    public function add()
    {
        $this->require_permission('roles.manage');

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('name', 'Role Name', 'required|trim');

            if ($this->form_validation->run() === TRUE) {
                $name = $this->input->post('name', TRUE);
                $slug = url_title($name, 'dash', TRUE);

                $role_id = $this->role_model->create([
                    'name'        => $name,
                    'slug'        => $slug,
                    'description' => $this->input->post('description', TRUE),
                    'is_system'   => 0
                ]);

                $permissions = (array) $this->input->post('permissions');
                $this->role_model->sync_permissions($role_id, $permissions);

                $this->session->set_flashdata('success', 'Role created with permissions successfully.');
                redirect('roles');
            }
        }

        $data = [
            'title'              => 'Create New Role | Admin',
            'active_menu'        => 'users_management',
            'active_submenu'     => 'roles',
            'permissions_module' => $this->role_model->get_all_permissions(),
            'role_perms'         => []
        ];

        $this->render('roles/edit', $data);
    }

    public function edit($id)
    {
        $this->require_permission('roles.manage');

        $role = $this->role_model->get_by_id($id);
        if (!$role) {
            $this->session->set_flashdata('error', 'Role not found.');
            redirect('roles');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('name', 'Role Name', 'required|trim');

            if ($this->form_validation->run() === TRUE) {
                $update_data = [
                    'name'        => $this->input->post('name', TRUE),
                    'description' => $this->input->post('description', TRUE)
                ];

                $this->role_model->update($id, $update_data);

                // Update permissions (if not system super-admin)
                if ($role['slug'] !== 'super-admin') {
                    $permissions = (array) $this->input->post('permissions');
                    $this->role_model->sync_permissions($id, $permissions);
                }

                $this->session->set_flashdata('success', 'Role and permissions updated successfully.');
                redirect('roles');
            }
        }

        $data = [
            'title'              => 'Edit Role: ' . $role['name'] . ' | Admin',
            'active_menu'        => 'users_management',
            'active_submenu'     => 'roles',
            'role'               => $role,
            'permissions_module' => $this->role_model->get_all_permissions(),
            'role_perms'         => $this->role_model->get_role_permission_ids($id)
        ];

        $this->render('roles/edit', $data);
    }

    public function delete($id)
    {
        $this->require_permission('roles.manage');

        $deleted = $this->role_model->delete($id);
        if ($deleted) {
            $this->session->set_flashdata('success', 'Role deleted.');
        } else {
            $this->session->set_flashdata('error', 'Cannot delete a protected system role.');
        }
        redirect('roles');
    }
}
