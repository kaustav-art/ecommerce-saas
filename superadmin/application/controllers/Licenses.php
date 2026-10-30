<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Licenses extends Superadmin_Controller {

    public function index() {
        $filter_status = $this->input->get('status', TRUE);
        $filter_plan = $this->input->get('plan', TRUE);
        $search = $this->input->get('search', TRUE);

        $this->db->select('s.*, t.business_name, t.slug, t.license_key, t.admin_email, t.template, p.name as plan_name');
        $this->db->from('saas_subscriptions s');
        $this->db->join('saas_tenants t', 't.id = s.tenant_id', 'left');
        $this->db->join('saas_plans p', 'p.id = s.plan_id', 'left');

        if (!empty($filter_status)) {
            $this->db->where('s.status', $filter_status);
        }
        if (!empty($filter_plan)) {
            $this->db->where('s.plan_id', (int)$filter_plan);
        }
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('t.license_key', $search);
            $this->db->or_like('t.business_name', $search);
            $this->db->or_like('t.admin_email', $search);
            $this->db->group_end();
        }

        $this->db->order_by('s.id', 'DESC');
        $data['licenses'] = $this->db->get()->result();

        $data['plans'] = $this->db->order_by('sort_order', 'ASC')->get('saas_plans')->result();
        $data['filter_status'] = $filter_status;
        $data['filter_plan'] = $filter_plan;
        $data['search'] = $search;

        $data['active_count'] = $this->db->where('status', 'active')->count_all_results('saas_subscriptions');
        $data['suspended_count'] = $this->db->where_in('status', array('suspended', 'expired', 'cancelled'))->count_all_results('saas_subscriptions');
        $data['total_count'] = $this->db->count_all('saas_subscriptions');

        $this->render('licenses/index', $data, 'Subscriptions & Tenant Licenses');
    }

    public function manage() {
        if ($this->input->method() === 'post') {
            $action = $this->input->post('action', TRUE);

            if ($action === 'update_status') {
                $sub_id = (int)$this->input->post('subscription_id');
                $tenant_id = (int)$this->input->post('tenant_id');
                $new_status = $this->input->post('status', TRUE);

                if ($sub_id > 0 && in_array($new_status, array('active', 'suspended', 'expired'))) {
                    $this->db->where('id', $sub_id)->update('saas_subscriptions', array('status' => $new_status));
                    if ($tenant_id > 0) {
                        $t_status = ($new_status === 'active') ? 'active' : 'suspended';
                        $this->db->where('id', $tenant_id)->update('saas_tenants', array(
                            'status'              => $t_status,
                            'subscription_status' => $new_status
                        ));
                    }
                    $this->session->set_flashdata('success', 'Subscription status updated to ' . strtoupper($new_status));
                }
            } elseif ($action === 'extend_subscription') {
                $sub_id = (int)$this->input->post('subscription_id');
                $tenant_id = (int)$this->input->post('tenant_id');
                $days = max(1, (int)$this->input->post('extend_days'));
                $new_end = date('Y-m-d H:i:s', strtotime('+' . $days . ' days'));
                if ($sub_id > 0) {
                    $this->db->where('id', $sub_id)->update('saas_subscriptions', array('end_date' => $new_end, 'status' => 'active'));
                }
                if ($tenant_id > 0) {
                    $this->db->where('id', $tenant_id)->update('saas_tenants', array('subscription_expires_at' => $new_end, 'subscription_status' => 'active', 'status' => 'active'));
                }
                $this->session->set_flashdata('success', 'Subscription extended by ' . $days . ' days (until ' . date('d M Y', strtotime($new_end)) . ').');
            }

            redirect(superadmin_url('licenses'));
            return;
        }

        redirect(superadmin_url('licenses'));
    }

    public function verify() {
        $key = trim((string)$this->input->post('license_key', TRUE));
        if (empty($key)) {
            $this->json_response(array('valid' => false, 'message' => 'Please enter a license key to verify.'), 400);
        }

        $tenant = $this->db->where('license_key', $key)->get('saas_tenants')->row();
        if ($tenant) {
            $this->json_response(array(
                'valid'          => true,
                'status'         => $tenant->status,
                'plan_code'      => 'Plan #' . $tenant->plan_id . ' (' . strtoupper($tenant->template) . ')',
                'customer_email' => $tenant->admin_email,
                'created_at'     => $tenant->created_at,
                'message'        => 'License verified for "' . $tenant->business_name . '" (' . strtoupper($tenant->status) . ').'
            ));
        } else {
            $this->json_response(array('valid' => false, 'message' => 'License key not found in SaaS platform.'), 404);
        }
    }
}
