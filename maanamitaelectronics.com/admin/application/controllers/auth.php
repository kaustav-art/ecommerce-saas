<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class auth extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin_model');
        $this->load->model('role_model');
    }

    public function login()
    {
        // Support one-click Superadmin Impersonation login (?impersonate_tenant=X&ts=Y&sig=Z)
        $imp_tid = (int) $this->input->get('impersonate_tenant');
        $imp_ts  = (int) $this->input->get('ts');
        $imp_sig = (string) $this->input->get('sig');
        if ($imp_tid > 0 && $imp_ts > 0 && $imp_sig !== '') {
            $expected = hash_hmac('sha256', $imp_tid . '|' . $imp_ts, 'SAAS_SUPERADMIN_IMPERSONATE_SECRET_2026');
            if (hash_equals($expected, $imp_sig) && abs(time() - $imp_ts) <= 3600) {
                $admin = $this->db->select('a.*, r.name as role_name, r.slug as role_slug')
                                  ->from('admins a')
                                  ->join('roles r', 'r.id = a.role_id', 'left')
                                  ->where('a.tenant_id', $imp_tid)
                                  ->order_by('a.id', 'ASC')
                                  ->limit(1)
                                  ->get()
                                  ->row_array();
                if (!$admin) {
                    $admin = $this->db->select('a.*, r.name as role_name, r.slug as role_slug')
                                      ->from('admins a')
                                      ->join('roles r', 'r.id = a.role_id', 'left')
                                      ->where('a.tenant_id', 1)
                                      ->order_by('a.id', 'ASC')
                                      ->limit(1)
                                      ->get()
                                      ->row_array();
                }
                if ($admin) {
                    $permissions = $this->role_model->get_role_permission_slugs($admin['role_id']);
                    $this->session->set_userdata([
                        'admin_id'          => (int) $admin['id'],
                        'admin_tenant_id'   => (int) ($admin['tenant_id'] ?? 1),
                        'admin_name'        => $admin['name'],
                        'admin_email'       => $admin['email'],
                        'admin_role_id'     => $admin['role_id'],
                        'admin_role_name'   => $admin['role_name'] ?: 'Store Owner',
                        'admin_role_slug'   => $admin['role_slug'] ?: 'super-admin',
                        'admin_permissions' => $permissions,
                        'admin_logged_in'   => TRUE
                    ]);
                    $this->session->set_flashdata('success', 'Logged into tenant dashboard: ' . $admin['name']);
                    redirect('dashboard');
                }
            }
        }

        if ($this->is_logged_in()) {
            redirect('dashboard');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
            $this->form_validation->set_rules('password', 'Password', 'required|trim');

            if ($this->form_validation->run() === TRUE) {
                $email    = $this->input->post('email', TRUE);
                $password = $this->input->post('password');

                if (!empty($this->folder_tenant_id) && $this->folder_tenant_id > 0) {
                    $admin = $this->admin_model->get_by_email_and_tenant($email, $this->folder_tenant_id);
                } else {
                    $admin = $this->admin_model->get_by_email($email);
                }

                if ($admin && password_verify($password, $admin['password'])) {
                    if ($admin['status'] !== 'active') {
                        $this->session->set_flashdata('error', 'Your administrator account is deactivated.');
                        redirect('auth/login');
                    }

                    $tenant_id = (int) ($admin['tenant_id'] ?? 1);
                    if ($tenant_id > 1 && $this->db->table_exists('saas_tenants')) {
                        $t_row = $this->db->where('id', $tenant_id)->get('saas_tenants')->row_array();
                        if ($t_row && strtolower($t_row['status'] ?? 'active') === 'suspended') {
                            $this->session->set_flashdata('error', 'Your store account has been suspended by the platform administrator.');
                            redirect('auth/login');
                        }
                    }

                    // Get role permissions
                    $permissions = $this->role_model->get_role_permission_slugs($admin['role_id']);

                    $session_data = [
                        'admin_id'          => (int) $admin['id'],
                        'admin_tenant_id'   => $tenant_id,
                        'admin_name'        => $admin['name'],
                        'admin_email'       => $admin['email'],
                        'admin_role_id'     => $admin['role_id'],
                        'admin_role_name'   => $admin['role_name'] ?: 'Store Owner',
                        'admin_role_slug'   => $admin['role_slug'] ?: 'super-admin',
                        'admin_permissions' => $permissions,
                        'admin_logged_in'   => TRUE
                    ];

                    $this->session->set_userdata($session_data);
                    $this->admin_model->update_last_login($admin['id']);

                    $this->session->set_flashdata('success', 'Welcome back, ' . $admin['name'] . '!');
                    redirect('dashboard');
                } else {
                    $this->session->set_flashdata('error', 'Invalid email or password.');
                    redirect('auth/login');
                }
            }
        }

        $data['title'] = 'Sign In | Admin Panel';
        $this->render_blank('auth/login', $data);
    }

    public function logout()
    {
        $this->session->unset_userdata([
            'admin_id',
            'admin_tenant_id',
            'admin_name',
            'admin_email',
            'admin_role_id',
            'admin_role_name',
            'admin_role_slug',
            'admin_permissions',
            'admin_logged_in'
        ]);
        $this->session->sess_destroy();
        redirect('auth/login');
    }
}
