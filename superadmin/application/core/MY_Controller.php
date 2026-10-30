<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Superadmin_Controller - Base Controller for SaaS Super Admin Control Panel
 */
class Superadmin_Controller extends CI_Controller {

    protected $current_user = null;

    public function __construct() {
        parent::__construct();

        // Handle URL fallback like ?page=plans or ?page=orders
        $page_query = $this->input->get('page', TRUE);
        if (!empty($page_query)) {
            $allowed_pages = array('website', 'layouts', 'plans', 'orders', 'licenses', 'tenants', 'users', 'settings', 'packages', 'dashboard');
            if (in_array(strtolower($page_query), $allowed_pages)) {
                redirect(superadmin_url(strtolower($page_query)));
                exit;
            }
        }

        $controller = strtolower($this->router->fetch_class());
        $method = strtolower($this->router->fetch_method());

        // Auth exemption
        $exempt = array('auth');
        if (!in_array($controller, $exempt)) {
            if (!$this->session->userdata('superadmin_logged_in')) {
                redirect(superadmin_url('auth/login'));
                exit;
            }

            // Fetch superadmin user info from saas_superadmins
            $user_id = (int)$this->session->userdata('superadmin_user_id');
            if ($user_id) {
                $user = $this->db->where('id', $user_id)
                                 ->get('saas_superadmins')
                                 ->row();
                if ($user) {
                    $user->role_name = 'SaaS Super Admin';
                    $this->current_user = $user;
                } else {
                    $this->session->sess_destroy();
                    redirect(superadmin_url('auth/login'));
                    exit;
                }
            }
        }
    }

    /**
     * Render page inside Super Admin master layout
     */
    protected function render($view, $data = array(), $page_title = 'SaaS Super Admin Control Panel') {
        $data['page_title'] = $page_title;
        $data['current_user'] = $this->current_user;
        $data['active_controller'] = strtolower($this->router->fetch_class());
        $data['active_method'] = strtolower($this->router->fetch_method());

        // Platform counters for sidebar badges
        $data['sidebar_orders_count']   = $this->db->where('payment_status', 'paid')->count_all_results('saas_payments');
        $data['sidebar_licenses_count'] = $this->db->where('status', 'active')->count_all_results('saas_tenants');
        $data['sidebar_plans_count']    = $this->db->count_all('saas_plans');

        $data['content_view'] = $view;
        $data['data'] = $data;

        $this->load->view('layouts/main', $data);
    }

    /**
     * JSON API response
     */
    protected function json_response($data, $status_code = 200) {
        $this->output
            ->set_status_header($status_code)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            ->_display();
        exit;
    }
}
