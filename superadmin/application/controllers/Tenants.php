<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tenants extends Superadmin_Controller {

    public function index() {
        if ($this->input->method() === 'post') {
            $action = $this->input->post('action', TRUE);

            if ($action === 'create_tenant') {
                require_once dirname(FCPATH) . '/saas_core.php';
                $pdo = saas_db();

                $plan_id = (int)$this->input->post('plan_id');
                $plan = saas_get_plan($plan_id) ?: saas_get_plan(2);

                $business_name = trim((string)$this->input->post('business_name', TRUE));
                $slug = saas_unique_slug($this->input->post('store_slug', TRUE) ?: $business_name);
                $admin_name = trim((string)$this->input->post('admin_name', TRUE));
                $admin_email = strtolower(trim((string)$this->input->post('admin_email', TRUE)));
                $admin_phone = trim((string)$this->input->post('admin_phone', TRUE));
                $admin_pass = (string)$this->input->post('admin_password');
                if (strlen($admin_pass) < 6) {
                    $admin_pass = 'admin123';
                }
                $template = strtolower(trim((string)$this->input->post('template', TRUE)));
                if (!in_array($template, array('fashion', 'electronics', 'grocery'))) {
                    $template = 'fashion';
                }

                $order_number = 'SAAS-ADM-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
                $stmt = $pdo->prepare("INSERT INTO `saas_payments`
                    (`order_number`, `plan_id`, `amount`, `currency`, `gateway`, `transaction_id`, `payment_status`,
                     `customer_name`, `customer_email`, `customer_phone`, `business_name`, `store_slug`, `chosen_template`, `setup_payload`, `paid_at`)
                    VALUES (?, ?, ?, ?, 'manual', ?, 'pending', ?, ?, ?, ?, ?, ?, ?, NOW())");
                $payload = json_encode(array(
                    'owner_name'        => $admin_name,
                    'owner_email'       => $admin_email,
                    'owner_phone'       => $admin_phone,
                    'password_hash'     => password_hash($admin_pass, PASSWORD_BCRYPT),
                    'business_name'     => $business_name,
                    'tagline'           => '',
                    'store_slug'        => $slug,
                    'business_address'  => '',
                    'currency_code'     => 'USD',
                    'currency_symbol'   => '$',
                    'template'          => $template
                ));
                $stmt->execute(array(
                    $order_number,
                    (int)$plan['id'],
                    (float)$plan['price'],
                    $plan['currency'] ?: 'USD',
                    'MANUAL-' . time(),
                    $admin_name,
                    $admin_email,
                    $admin_phone,
                    $business_name,
                    $slug,
                    $template,
                    $payload
                ));

                $res = provision_saas_tenant($order_number, 'MANUAL-' . time(), array('gateway' => 'manual'));
                if (!empty($res['success'])) {
                    $this->session->set_flashdata('success', 'New tenant store "' . $business_name . '" provisioned! Store URL: /website/s/' . $slug);
                } else {
                    $this->session->set_flashdata('error', 'Failed to provision tenant: ' . (isset($res['message']) ? $res['message'] : 'Unknown error'));
                }
            } elseif ($action === 'edit_tenant') {
                $tenant_id = (int)$this->input->post('tenant_id');
                $business_name = trim((string)$this->input->post('business_name', TRUE));
                $plan_id = (int)$this->input->post('plan_id');
                $template = strtolower(trim((string)$this->input->post('template', TRUE)));
                if (!in_array($template, array('fashion', 'electronics', 'grocery'))) {
                    $template = 'fashion';
                }
                $status = $this->input->post('status', TRUE) === 'suspended' ? 'suspended' : 'active';
                $sub_status = $this->input->post('subscription_status', TRUE);
                if (!in_array($sub_status, array('active', 'expired', 'suspended', 'Trial'))) {
                    $sub_status = 'active';
                }
                $expires_at = trim((string)$this->input->post('subscription_expires_at', TRUE));

                if ($tenant_id > 0) {
                    $upd = array(
                        'business_name'       => $business_name,
                        'plan_id'             => $plan_id,
                        'template'            => $template,
                        'status'              => $status,
                        'subscription_status' => $sub_status
                    );
                    if (!empty($expires_at)) {
                        $upd['subscription_expires_at'] = date('Y-m-d H:i:s', strtotime($expires_at));
                    }
                    $this->db->where('id', $tenant_id)->update('saas_tenants', $upd);

                    // Sync active template in saas_tenant_templates & settings
                    if ($this->db->table_exists('saas_tenant_templates')) {
                        $exists = $this->db->where('tenant_id', $tenant_id)->get('saas_tenant_templates')->row();
                        if ($exists) {
                            $this->db->where('tenant_id', $tenant_id)->update('saas_tenant_templates', array('template_key' => $template));
                        } else {
                            $this->db->insert('saas_tenant_templates', array('tenant_id' => $tenant_id, 'template_key' => $template));
                        }
                    }
                    if ($this->db->table_exists('settings')) {
                        $s_name = $this->db->where('tenant_id', $tenant_id)->where('setting_key', 'site_name')->get('settings')->row();
                        if ($s_name) {
                            $this->db->where('id', $s_name->id)->update('settings', array('setting_value' => $business_name));
                        }
                    }
                    if ($this->db->table_exists('saas_subscriptions')) {
                        $sub_upd = array('plan_id' => $plan_id, 'template_key' => $template, 'status' => strtolower($sub_status));
                        if (!empty($expires_at)) {
                            $sub_upd['end_date'] = date('Y-m-d H:i:s', strtotime($expires_at));
                        }
                        $this->db->where('tenant_id', $tenant_id)->update('saas_subscriptions', $sub_upd);
                    }

                    $this->session->set_flashdata('success', 'Tenant store #' . $tenant_id . ' (' . $business_name . ') updated successfully.');
                }
            }

            redirect(superadmin_url('tenants'));
            return;
        }

        // Load Tenants with Plan Name + Product & Order Counts
        $this->db->select('t.*, p.name as plan_name, p.product_limit');
        $this->db->from('saas_tenants t');
        $this->db->join('saas_plans p', 'p.id = t.plan_id', 'left');
        $this->db->order_by('t.id', 'ASC');
        $tenants = $this->db->get()->result();

        foreach ($tenants as $t) {
            $t->products_count = $this->db->where('tenant_id', (int)$t->id)->count_all_results('products');
            $t->orders_count   = $this->db->where('tenant_id', (int)$t->id)->count_all_results('orders');
        }

        $data['saas_tenants'] = $tenants;
        $data['total_saas_tenants'] = count($tenants);
        $data['plans'] = $this->db->order_by('sort_order', 'ASC')->get('saas_plans')->result();
        $data['templates'] = $this->db->order_by('sort_order', 'ASC')->get('saas_templates')->result();

        $this->render('tenants/index', $data, 'SaaS Multi-Tenant Store Management');
    }

    /**
     * Toggle Tenant Status (Active / Suspended)
     */
    public function toggle_status($id) {
        $tenant = $this->db->where('id', (int)$id)->get('saas_tenants')->row();
        if ($tenant) {
            $new_status = ($tenant->status === 'active') ? 'suspended' : 'active';
            $this->db->where('id', (int)$id)->update('saas_tenants', array(
                'status'              => $new_status,
                'subscription_status' => $new_status
            ));
            $this->session->set_flashdata('success', 'Tenant "' . $tenant->business_name . '" status changed to ' . strtoupper($new_status));
        }
        redirect(superadmin_url('tenants'));
    }
}
