<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Superadmin_Controller {

    public function index() {
        // Aggregate statistics from saas_payments
        $orders_stats = $this->db->select('COUNT(id) as total_orders, SUM(amount) as total_revenue')
                                 ->from('saas_payments')
                                 ->where('payment_status', 'paid')
                                 ->get()->row();

        $data['total_orders'] = $orders_stats && $orders_stats->total_orders ? (int)$orders_stats->total_orders : 0;
        $data['total_revenue'] = $orders_stats && $orders_stats->total_revenue ? (float)$orders_stats->total_revenue : 0.00;
        $data['active_tenants_count'] = $this->db->where('status', 'active')->count_all_results('saas_tenants');
        $data['total_templates'] = $this->db->where('is_active', 1)->count_all_results('saas_templates');

        // Recent Subscription Payments
        $this->db->select('o.*, p.name as plan_name');
        $this->db->from('saas_payments o');
        $this->db->join('saas_plans p', 'p.id = o.plan_id', 'left');
        $data['recent_orders'] = $this->db->order_by('o.id', 'DESC')->limit(6)->get()->result();

        // Recent Tenant Stores with plan names
        $this->db->select('t.*, p.name as plan_name');
        $this->db->from('saas_tenants t');
        $this->db->join('saas_plans p', 'p.id = t.plan_id', 'left');
        $data['recent_tenants'] = $this->db->order_by('t.id', 'DESC')->limit(6)->get()->result();

        // SaaS Plans (active first)
        $data['plans'] = $this->db->where('is_active', 1)->order_by('sort_order', 'ASC')->get('saas_plans')->result();

        // SaaS Templates
        $data['templates'] = $this->db->order_by('sort_order', 'ASC')->get('saas_templates')->result();

        $this->render('dashboard/index', $data, 'SaaS Super Admin Dashboard');
    }
}
