<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends Superadmin_Controller {

    public function index() {
        if ($this->input->method() === 'post') {
            $action = $this->input->post('action', TRUE);
            $active_tab = $this->input->post('active_tab', TRUE);
            if (!$active_tab) $active_tab = 'admins';

            if ($action === 'create_user') {
                $name = $this->input->post('name', TRUE);
                $email = trim($this->input->post('email', TRUE));
                $phone = $this->input->post('phone', TRUE);
                $role_id = (int)$this->input->post('role_id');
                $password = $this->input->post('password');

                $exists = $this->db->where('email', $email)->count_all_results('users');
                if ($exists > 0) {
                    $this->session->set_flashdata('error', 'User with email ' . $email . ' already exists.');
                } else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $this->db->insert('users', array(
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                        'role_id' => $role_id > 0 ? $role_id : 1,
                        'password' => $hash,
                        'status' => 'active'
                    ));
                    $this->session->set_flashdata('success', 'User account ' . $name . ' created successfully.');
                }
            } elseif ($action === 'update_user') {
                $user_id = (int)$this->input->post('user_id');
                $name = $this->input->post('name', TRUE);
                $email = trim($this->input->post('email', TRUE));
                $phone = $this->input->post('phone', TRUE);
                $role_id = (int)$this->input->post('role_id');
                $status = $this->input->post('status', TRUE);
                $new_pass = $this->input->post('new_password');

                $data = array(
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'role_id' => $role_id,
                    'status' => in_array($status, array('active', 'inactive')) ? $status : 'active'
                );

                if (!empty($new_pass)) {
                    $data['password'] = password_hash($new_pass, PASSWORD_BCRYPT);
                }

                $this->db->where('id', $user_id)->update('users', $data);
                $this->session->set_flashdata('success', 'User details updated successfully.');
            } elseif ($action === 'delete_user') {
                $user_id = (int)$this->input->post('user_id');
                $curr_id = $this->session->userdata('superadmin_user_id');

                if ($user_id === $curr_id) {
                    $this->session->set_flashdata('error', 'You cannot delete your own logged in administrator account.');
                } else {
                    $this->db->where('id', $user_id)->delete('users');
                    $this->session->set_flashdata('success', 'User account deleted successfully.');
                }
            }

            redirect(superadmin_url('users?tab=' . urlencode($active_tab)));
            return;
        }

        // 1. Platform Administrators
        $this->db->select('u.*, r.role_name');
        $this->db->from('users u');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');
        $this->db->order_by('u.id', 'ASC');
        $data['users'] = $this->db->get()->result();

        $data['roles'] = $this->db->get('roles')->result();

        // 2. Customer & Client Accounts (From marketplace purchases)
        $this->db->select('
            customer_email,
            customer_name,
            customer_phone,
            business_name,
            COUNT(id) as total_orders,
            SUM(amount) as total_spent,
            MAX(created_at) as latest_order_date,
            GROUP_CONCAT(license_key SEPARATOR ", ") as license_keys,
            MAX(payment_status) as latest_status
        ');
        $this->db->from('marketplace_orders');
        $this->db->group_by('customer_email');
        $this->db->order_by('latest_order_date', 'DESC');
        $data['customers'] = $this->db->get()->result();

        $data['currency_symbol'] = get_setting('currency_symbol', '$');
        $data['currency_position'] = get_setting('currency_position', 'left');
        $data['active_tab'] = $this->input->get('tab', TRUE) ? $this->input->get('tab', TRUE) : 'admins';

        $this->render('users/index', $data, 'Users & Accounts Management');
    }
}
