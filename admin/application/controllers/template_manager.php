<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class template_manager extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('setting_model');
    }

    public function index()
    {
        $tid = (int) $this->tenant_id;
        $tenant = $this->db->where('id', $tid)->get('saas_tenants')->row_array();
        $plan   = !empty($tenant['plan_id'])
            ? $this->db->where('id', (int) $tenant['plan_id'])->get('saas_plans')->row_array()
            : null;

        $templates = $this->db->where('is_active', 1)
                              ->order_by('sort_order', 'ASC')
                              ->get('saas_templates')
                              ->result_array();

        $allowed_list = !empty($plan['allowed_templates']) ? json_decode($plan['allowed_templates'], true) : null;
        if (!is_array($allowed_list)) {
            $allowed_list = !empty($plan['allowed_templates'])
                ? array_map('trim', explode(',', strtolower($plan['allowed_templates'])))
                : ['fashion', 'electronics', 'grocery'];
        }

        $data = [
            'title'          => 'Storefront Template Manager | Admin',
            'active_menu'    => 'saas_subscription',
            'active_submenu' => 'template_manager',
            'tenant'         => $tenant,
            'current_plan'   => $plan,
            'templates'      => $templates,
            'allowed_list'   => $allowed_list
        ];

        $this->render('template_manager/index', $data);
    }

    public function activate()
    {
        if ($this->input->method() !== 'post') {
            redirect('template_manager');
            return;
        }

        $tpl_slug = strtolower(trim($this->input->post('template_slug', TRUE) ?: ''));
        $tpl = $this->db->where('template_key', $tpl_slug)->where('is_active', 1)->get('saas_templates')->row_array();
        if (!$tpl) {
            $this->session->set_flashdata('error', 'Selected template is invalid or unavailable.');
            redirect('template_manager');
            return;
        }

        $tid = (int) $this->tenant_id;
        $tenant = $this->db->where('id', $tid)->get('saas_tenants')->row_array();
        $plan   = !empty($tenant['plan_id'])
            ? $this->db->where('id', (int) $tenant['plan_id'])->get('saas_plans')->row_array()
            : null;

        $allowed_list = !empty($plan['allowed_templates']) ? json_decode($plan['allowed_templates'], true) : null;
        if (!is_array($allowed_list)) {
            $allowed_list = !empty($plan['allowed_templates'])
                ? array_map('trim', explode(',', strtolower($plan['allowed_templates'])))
                : ['fashion', 'electronics', 'grocery'];
        }

        if (!in_array($tpl_slug, $allowed_list, true)) {
            $this->session->set_flashdata('error', 'The "' . html_escape($tpl['name']) . '" template is not included in your current plan (' . html_escape($plan['name'] ?? 'Current Plan') . '). Please upgrade your subscription plan to unlock it.');
            redirect('subscription');
            return;
        }

        // Update tenant's active template
        $this->db->where('id', $tid)->update('saas_tenants', [
            'template' => $tpl_slug
        ]);

        // Update saas_tenant_templates record
        if ($this->db->table_exists('saas_tenant_templates')) {
            $exists = $this->db->where('tenant_id', $tid)->get('saas_tenant_templates')->row_array();
            if ($exists) {
                $this->db->where('tenant_id', $tid)->update('saas_tenant_templates', [
                    'template_key' => $tpl_slug
                ]);
            } else {
                $this->db->insert('saas_tenant_templates', [
                    'tenant_id'    => $tid,
                    'template_key' => $tpl_slug
                ]);
            }
        }

        $this->session->set_flashdata('success', 'Storefront theme switched to "' . html_escape($tpl['name']) . '"! Your live store now renders this template.');
        redirect('template_manager');
    }
}
