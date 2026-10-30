<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Plans extends Superadmin_Controller {

    public function index() {
        if ($this->input->method() === 'post') {
            $action = $this->input->post('action', TRUE);

            if ($action === 'save_plan') {
                $plan_id = (int)$this->input->post('plan_id');
                $name = trim((string)$this->input->post('name', TRUE));
                $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9\-]+/', '-', (string)$this->input->post('slug', TRUE))));
                if ($slug === '') {
                    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9\-]+/', '-', $name)));
                }
                $description = trim((string)$this->input->post('description', TRUE));
                $price = (float)$this->input->post('price');
                $billing_cycle = $this->input->post('billing_cycle', TRUE);
                if (!in_array($billing_cycle, array('monthly', 'yearly', 'lifetime'))) {
                    $billing_cycle = 'monthly';
                }
                $duration_days = (int)$this->input->post('duration_days');
                if ($duration_days <= 0) {
                    $duration_days = ($billing_cycle === 'yearly') ? 365 : 30;
                }
                $trial_days = max(0, (int)$this->input->post('trial_days'));
                $product_limit = (int)$this->input->post('product_limit');
                $order_limit = (int)$this->input->post('order_limit');
                $storage_limit_mb = max(100, (int)$this->input->post('storage_limit_mb'));
                $is_featured = $this->input->post('is_featured') ? 1 : 0;
                $is_active = ($this->input->post('status', TRUE) === 'inactive') ? 0 : 1;
                $sort_order = (int)$this->input->post('sort_order');

                $tpl_arr = $this->input->post('allowed_templates');
                if (!is_array($tpl_arr) || empty($tpl_arr)) {
                    $valid_tpls = array('fashion', 'electronics', 'grocery');
                } else {
                    $valid_tpls = array_values(array_intersect($tpl_arr, array('fashion', 'electronics', 'grocery')));
                    if (empty($valid_tpls)) {
                        $valid_tpls = array('fashion');
                    }
                }
                $allowed_templates = json_encode($valid_tpls);

                $features_raw = (string)$this->input->post('features', TRUE);
                $features_array = array_filter(array_map('trim', explode("\n", $features_raw)));
                $features_json = json_encode(array_values($features_array));

                $plan_data = array(
                    'name'              => $name,
                    'slug'              => $slug,
                    'subtitle'          => $description,
                    'description'       => $description,
                    'price'             => $price,
                    'discounted_price'  => $price,
                    'billing_cycle'     => $billing_cycle,
                    'duration_days'     => $duration_days,
                    'trial_days'        => $trial_days,
                    'product_limit'     => $product_limit,
                    'order_limit'       => $order_limit,
                    'storage_limit_mb'  => $storage_limit_mb,
                    'allowed_templates' => $allowed_templates,
                    'features'          => $features_json,
                    'is_featured'       => $is_featured,
                    'is_active'         => $is_active,
                    'sort_order'        => $sort_order
                );

                if ($plan_id > 0) {
                    $this->db->where('id', $plan_id)->update('saas_plans', $plan_data);
                    $this->session->set_flashdata('success', 'Subscription plan "' . $name . '" updated successfully.');
                } else {
                    $plan_data['code'] = strtoupper(str_replace('-', '_', $slug));
                    $plan_data['template'] = $valid_tpls[0];
                    $this->db->insert('saas_plans', $plan_data);
                    $this->session->set_flashdata('success', 'New subscription plan "' . $name . '" created successfully.');
                }
            } elseif ($action === 'delete_plan') {
                $plan_id = (int)$this->input->post('plan_id');
                if ($plan_id > 0) {
                    $in_use = $this->db->where('plan_id', $plan_id)->count_all_results('saas_tenants');
                    if ($in_use > 0) {
                        $this->session->set_flashdata('error', 'Cannot delete plan because ' . $in_use . ' tenant store(s) are subscribed to it. Set it to Inactive instead.');
                    } else {
                        $this->db->where('id', $plan_id)->delete('saas_plans');
                        $this->session->set_flashdata('success', 'Plan deleted successfully.');
                    }
                }
            }

            redirect(superadmin_url('plans'));
            return;
        }

        $data['plans'] = $this->db->order_by('sort_order', 'ASC')->get('saas_plans')->result();
        $data['templates'] = $this->db->order_by('sort_order', 'ASC')->get('saas_templates')->result();
        $this->render('plans/index', $data, 'Manage SaaS Subscription Plans');
    }
}
