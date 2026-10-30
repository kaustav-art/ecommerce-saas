<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Controller
 * Base Controller for Multi-Tenant Admin Panel with RBAC security, Subscription Enforcement & layout rendering
 */
class MY_Controller extends CI_Controller {

    public $current_admin    = NULL;
    public $tenant_id        = 1;
    public $folder_tenant_id = 0;
    public $tenant           = [];
    public $tenant_plan      = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['url', 'html', 'form', 'text', 'security']);
        $this->load->library(['session', 'form_validation']);

        // 1. Detect if running inside a tenant-specific folder
        $this->folder_tenant_id = 0;
        if (file_exists(APPPATH . 'config/tenant.php')) {
            $this->config->load('tenant', TRUE, TRUE);
            $cfg_tid = (int)$this->config->item('tenant_id', 'tenant');
            if ($cfg_tid > 0) {
                $this->folder_tenant_id = $cfg_tid;
            }
        }
        if ($this->folder_tenant_id === 0) {
            $parent_folder = strtolower(trim(basename(realpath(FCPATH . '/..'))));
            if (!in_array($parent_folder, ['ecommerce', 'htdocs', 'website', 'admin'], true) && $this->db->table_exists('saas_tenants')) {
                $t_row = $this->db->where('slug', $parent_folder)
                                  ->or_where('domain', $parent_folder)
                                  ->or_like('folder_path', $parent_folder)
                                  ->get('saas_tenants')->row_array();
                if ($t_row) {
                    $this->folder_tenant_id = (int)$t_row['id'];
                }
            }
        }

        // 2. Resolve active tenant_id
        if ($this->folder_tenant_id > 0) {
            $this->tenant_id = $this->folder_tenant_id;
            // Invalidate session if logged into another tenant's admin
            $sess_tid = (int)$this->session->userdata('admin_tenant_id');
            if ($sess_tid > 0 && $sess_tid !== $this->folder_tenant_id) {
                $this->session->unset_userdata([
                    'admin_id', 'admin_tenant_id', 'admin_name', 'admin_email',
                    'admin_role_id', 'admin_role_name', 'admin_role_slug',
                    'admin_permissions', 'admin_logged_in'
                ]);
            }
        } else {
            $this->tenant_id = (int) ($this->session->userdata('admin_tenant_id') ?: 1);
        }

        // Load current tenant and plan metadata directly from ecommerce_main
        if ($this->db->table_exists('saas_tenants')) {
            $this->tenant = $this->db->where('id', $this->tenant_id)->get('saas_tenants')->row_array() ?: [];
        }
        if (empty($this->tenant)) {
            $s_name = 'Store';
            $s_tpl  = 'fashion';
            if ($this->db->table_exists('settings')) {
                foreach ($this->db->where('tenant_id', $this->tenant_id)->where_in('setting_key', ['site_name', 'active_template'])->get('settings')->result_array() as $sr) {
                    if ($sr['setting_key'] === 'site_name' && !empty($sr['setting_value'])) $s_name = $sr['setting_value'];
                    if ($sr['setting_key'] === 'active_template' && !empty($sr['setting_value'])) $s_tpl = $sr['setting_value'];
                }
            }
            $this->tenant = [
                'id'                  => $this->tenant_id,
                'business_name'       => $s_name,
                'slug'                => 'store',
                'template'            => $s_tpl,
                'status'              => 'active',
                'subscription_status' => 'active',
                'plan_id'             => 3
            ];
        }

        if (!empty($this->tenant['plan_id']) && $this->db->table_exists('saas_plans')) {
            $this->tenant_plan = $this->db->where('id', (int) $this->tenant['plan_id'])->get('saas_plans')->row_array() ?: [];
        }

        // Exclude 'auth' controller from forced login check
        $controller = strtolower($this->router->class);
        if ($controller !== 'auth') {
            if (!$this->is_logged_in()) {
                redirect('auth/login');
            }
            $this->current_admin = [
                'id'          => $this->session->userdata('admin_id'),
                'tenant_id'   => $this->tenant_id,
                'name'        => $this->session->userdata('admin_name'),
                'email'       => $this->session->userdata('admin_email'),
                'role_id'     => $this->session->userdata('admin_role_id'),
                'role_name'   => $this->session->userdata('admin_role_name'),
                'role_slug'   => $this->session->userdata('admin_role_slug'),
                'permissions' => (array) $this->session->userdata('admin_permissions')
            ];

            // Enforce Tenant Suspension & Subscription Expiry for non-default tenants
            if ($this->tenant_id > 1) {
                $t_status   = strtolower($this->tenant['status'] ?? 'active');
                $sub_status = strtolower($this->tenant['subscription_status'] ?? 'active');
                $expires_at = $this->tenant['subscription_expires_at'] ?? NULL;
                $is_expired = (!empty($expires_at) && strtotime($expires_at) < time());

                if ($t_status === 'suspended' || $sub_status === 'suspended') {
                    $this->session->unset_userdata('admin_logged_in');
                    $this->session->set_flashdata('error', 'Your tenant store has been suspended by the platform administrator.');
                    redirect('auth/login');
                }

                if (($is_expired || in_array($sub_status, ['expired', 'cancelled', 'pending'], true)) && !in_array($controller, ['subscription', 'profile'], true)) {
                    $this->session->set_flashdata('error', 'Your SaaS subscription has expired or is inactive. Please renew your plan below to continue managing your store.');
                    redirect('subscription');
                }
            }
        }
    }

    public function is_logged_in()
    {
        return (bool) $this->session->userdata('admin_logged_in');
    }

    public function can($permission_slug)
    {
        if (!$this->is_logged_in()) {
            return false;
        }
        $role_slug = (string) $this->session->userdata('admin_role_slug');
        if (in_array($role_slug, ['super-admin', 'store-owner', 'tenant-owner', 'admin'], true) || (int) $this->session->userdata('admin_role_id') === 1) {
            return true;
        }
        $perms = (array) $this->session->userdata('admin_permissions');
        return in_array($permission_slug, $perms);
    }

    public function require_permission($permission_slug)
    {
        if (!$this->can($permission_slug)) {
            $this->session->set_flashdata('error', 'Access Denied: You do not have permission to access this resource.');
            redirect('dashboard');
        }
    }

    public function render($view, $data = [], $return = FALSE)
    {
        $data['title']          = isset($data['title']) ? $data['title'] : 'Admin Panel - ' . ($this->tenant['business_name'] ?? 'eCommerce');
        $data['active_menu']    = isset($data['active_menu']) ? $data['active_menu'] : 'dashboard';
        $data['active_submenu'] = isset($data['active_submenu']) ? $data['active_submenu'] : '';
        $data['current_admin']  = $this->current_admin;
        $data['tenant_id']      = $this->tenant_id;
        $data['tenant']         = $this->tenant;
        $data['tenant_plan']    = $this->tenant_plan;

        if (!isset($data['store_settings'])) {
            $this->load->model('setting_model');
            $data['store_settings'] = $this->setting_model->get_all();
        }
        if (!isset($data['currency_symbol'])) {
            $data['currency_symbol'] = $data['store_settings']['currency_symbol'] ?? '$';
        }
        if (!isset($data['currency_code'])) {
            $data['currency_code'] = $data['store_settings']['currency_code'] ?? ($data['store_settings']['currency'] ?? 'USD');
        }

        if ($return) {
            $content  = $this->load->view('layouts/header', $data, TRUE);
            $content .= $this->load->view('layouts/sidebar', $data, TRUE);
            $content .= $this->load->view('layouts/navbar', $data, TRUE);
            $content .= $this->load->view($view, $data, TRUE);
            $content .= $this->load->view('layouts/footer', $data, TRUE);
            return $content;
        }

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar', $data);
        $this->load->view('layouts/navbar', $data);
        $this->load->view($view, $data);
        $this->load->view('layouts/footer', $data);
    }

    public function render_blank($view, $data = [], $return = FALSE)
    {
        $data['title'] = isset($data['title']) ? $data['title'] : 'Admin Panel';

        if (!isset($data['store_settings'])) {
            $this->load->model('setting_model');
            $data['store_settings'] = $this->setting_model->get_all();
        }
        if (!isset($data['currency_symbol'])) {
            $data['currency_symbol'] = $data['store_settings']['currency_symbol'] ?? '$';
        }
        if (!isset($data['currency_code'])) {
            $data['currency_code'] = $data['store_settings']['currency_code'] ?? ($data['store_settings']['currency'] ?? 'USD');
        }

        if ($return) {
            $content  = $this->load->view('layouts/auth_header', $data, TRUE);
            $content .= $this->load->view($view, $data, TRUE);
            $content .= $this->load->view('layouts/auth_footer', $data, TRUE);
            return $content;
        }

        $this->load->view('layouts/auth_header', $data);
        $this->load->view($view, $data);
        $this->load->view('layouts/auth_footer', $data);
    }

    public function json_response($data, $status = 200)
    {
        $this->output
             ->set_status_header($status)
             ->set_content_type('application/json', 'utf-8')
             ->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
             ->_display();
        exit;
    }

    public function upload_image_file($field_name, $subfolder = 'products', $prefix = 'img')
    {
        if (empty($_FILES[$field_name]['name'])) {
            return null;
        }

        $target_dir = admin_storefront_fs_path('assets/images/' . trim($subfolder, '/') . '/');
        if (!is_dir($target_dir)) {
            @mkdir($target_dir, 0777, TRUE);
        }

        $config = [];
        $config['upload_path']   = $target_dir;
        $config['allowed_types'] = 'gif|jpg|jpeg|png|webp|svg';
        $config['max_size']      = 10240; // 10MB
        $config['file_name']     = $prefix . '_' . time() . '_' . rand(100, 999);

        $this->load->library('upload');
        $this->upload->initialize($config);

        if ($this->upload->do_upload($field_name)) {
            $data = $this->upload->data();
            return trim($subfolder, '/') . '/' . $data['file_name'];
        }

        return false;
    }

    public function upload_multiple_images($field_name, $subfolder = 'products', $prefix = 'gallery')
    {
        if (empty($_FILES[$field_name]['name']) || !is_array($_FILES[$field_name]['name'])) {
            return [];
        }

        $target_dir = admin_storefront_fs_path('assets/images/' . trim($subfolder, '/') . '/');
        if (!is_dir($target_dir)) {
            @mkdir($target_dir, 0777, TRUE);
        }

        $uploaded_files = [];
        $files_count = count($_FILES[$field_name]['name']);

        $this->load->library('upload');

        for ($i = 0; $i < $files_count; $i++) {
            if (empty($_FILES[$field_name]['name'][$i])) {
                continue;
            }

            $_FILES['single_upload']['name']     = $_FILES[$field_name]['name'][$i];
            $_FILES['single_upload']['type']     = $_FILES[$field_name]['type'][$i];
            $_FILES['single_upload']['tmp_name'] = $_FILES[$field_name]['tmp_name'][$i];
            $_FILES['single_upload']['error']    = $_FILES[$field_name]['error'][$i];
            $_FILES['single_upload']['size']     = $_FILES[$field_name]['size'][$i];

            $config = [];
            $config['upload_path']   = $target_dir;
            $config['allowed_types'] = 'gif|jpg|jpeg|png|webp|svg';
            $config['max_size']      = 10240;
            $config['file_name']     = $prefix . '_' . time() . '_' . $i . '_' . rand(100, 999);

            $this->upload->initialize($config);
            if ($this->upload->do_upload('single_upload')) {
                $data = $this->upload->data();
                $uploaded_files[] = trim($subfolder, '/') . '/' . $data['file_name'];
            }
        }

        return $uploaded_files;
    }
}
