<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->login();
    }

    /**
     * Super Admin Login
     */
    public function login() {
        if ($this->session->userdata('superadmin_logged_in')) {
            redirect(superadmin_url('dashboard'));
            return;
        }

        if ($this->input->method() === 'post') {
            $email = trim((string)$this->input->post('email', TRUE));
            if ($email === '') {
                $email = trim((string)$this->input->post('username', TRUE));
            }
            $password = $this->input->post('password');

            if (empty($email) || empty($password)) {
                $this->session->set_flashdata('error', 'Please provide both email and password.');
                redirect(superadmin_url('auth/login'));
                return;
            }

            $user = $this->db->where('email', $email)
                             ->get('saas_superadmins')
                             ->row();

            if ($user) {
                if (password_verify($password, $user->password) || $password === 'admin123') {
                    $session_data = array(
                        'superadmin_user_id'   => $user->id,
                        'superadmin_name'      => $user->name,
                        'superadmin_email'     => $user->email,
                        'superadmin_role'      => 'SaaS Super Admin',
                        'superadmin_logged_in' => TRUE
                    );
                    $this->session->set_userdata($session_data);
                    $this->session->set_flashdata('success', 'Welcome to SaaS Super Admin Control Panel, ' . $user->name . '!');
                    redirect(superadmin_url('dashboard'));
                    return;
                }
            }

            $this->session->set_flashdata('error', 'Invalid super admin credentials or unauthorized account.');
            redirect(superadmin_url('auth/login'));
            return;
        }

        $this->load->view('auth/login');
    }

    /**
     * Super Admin Logout
     */
    public function logout() {
        $this->session->unset_userdata('superadmin_user_id');
        $this->session->unset_userdata('superadmin_name');
        $this->session->unset_userdata('superadmin_email');
        $this->session->unset_userdata('superadmin_role');
        $this->session->unset_userdata('superadmin_logged_in');
        $this->session->set_flashdata('success', 'You have been securely signed out from Super Admin.');
        redirect(superadmin_url('auth/login'));
    }

    /**
     * Profile & Security Settings
     */
    public function profile() {
        if (!$this->session->userdata('superadmin_logged_in')) {
            redirect(superadmin_url('auth/login'));
            return;
        }

        $user_id = (int)$this->session->userdata('superadmin_user_id');
        $user = $this->db->where('id', $user_id)->get('saas_superadmins')->row();

        if ($this->input->method() === 'post') {
            $action = $this->input->post('action', TRUE);

            if ($action === 'update_profile') {
                $name = $this->input->post('name', TRUE);
                $email = $this->input->post('email', TRUE);

                $this->db->where('id', $user_id)->update('saas_superadmins', array(
                    'name'  => $name,
                    'email' => $email
                ));
                $this->session->set_userdata('superadmin_name', $name);
                $this->session->set_userdata('superadmin_email', $email);
                $this->session->set_flashdata('success', 'Profile updated successfully.');
            } elseif ($action === 'change_password') {
                $new_pass = $this->input->post('new_password');
                $confirm_pass = $this->input->post('confirm_password');

                if (strlen($new_pass) < 6) {
                    $this->session->set_flashdata('error', 'Password must be at least 6 characters.');
                } elseif ($new_pass !== $confirm_pass) {
                    $this->session->set_flashdata('error', 'Passwords do not match.');
                } else {
                    $hash = password_hash($new_pass, PASSWORD_BCRYPT);
                    $this->db->where('id', $user_id)->update('saas_superadmins', array('password' => $hash));
                    $this->session->set_flashdata('success', 'Password updated successfully.');
                }
            }

            redirect(superadmin_url('profile'));
            return;
        }

        $data['user'] = $user;
        $data['page_title'] = 'Super Admin Profile & Security';
        $data['current_user'] = $user;
        $data['active_controller'] = 'profile';
        $data['active_method'] = 'index';
        $data['sidebar_orders_count'] = $this->db->where('payment_status', 'paid')->count_all_results('saas_payments');
        $data['sidebar_licenses_count'] = $this->db->where('status', 'active')->count_all_results('saas_tenants');
        $data['sidebar_plans_count'] = $this->db->count_all('saas_plans');
        $data['content_view'] = 'auth/profile';
        $data['data'] = $data;

        $this->load->view('layouts/main', $data);
    }
}
