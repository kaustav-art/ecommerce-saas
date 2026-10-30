<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class settings extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('setting_model');
    }

    public function index()
    {
        $tab = $this->input->get('tab') ?: 'general';
        $this->_handle_settings($tab);
    }

    public function general()
    {
        $this->_handle_settings('general');
    }

    public function shipping()
    {
        $this->_handle_settings('shipping');
    }

    public function tax()
    {
        $this->_handle_settings('tax');
    }

    public function email()
    {
        $this->_handle_settings('email');
    }

    public function sms()
    {
        $this->_handle_settings('sms');
    }

    public function seo()
    {
        $this->_handle_settings('seo');
    }

    private function _handle_settings($group)
    {
        $this->require_permission('settings.manage');

        $allowed_groups = ['general', 'shipping', 'tax', 'email', 'sms', 'seo'];
        if (!in_array($group, $allowed_groups)) {
            $group = 'general';
        }

        if ($this->input->method() === 'post') {
            $submitted_group = $this->input->post('setting_group', TRUE) ?: $group;
            $post_data = $this->input->post(NULL, TRUE);

            // Handle Logo upload for general settings
            if ($submitted_group === 'general' && !empty($_FILES['site_logo_file']['name'])) {
                $logo_dir = admin_storefront_fs_path('assets/images/logo/');
                if (!is_dir($logo_dir)) {
                    @mkdir($logo_dir, 0777, TRUE);
                }
                $admin_logo_dir = FCPATH . 'assets/img/branding/';
                if (!is_dir($admin_logo_dir)) {
                    @mkdir($admin_logo_dir, 0777, TRUE);
                }

                $config = [
                    'upload_path'   => $logo_dir,
                    'allowed_types' => 'gif|jpg|jpeg|png|webp|svg',
                    'max_size'      => 5120,
                    'file_name'     => 'logo_' . time() . '_' . rand(100, 999)
                ];

                if (!isset($this->upload)) {
                    $this->load->library('upload', $config);
                } else {
                    $this->upload->initialize($config);
                }

                if ($this->upload->do_upload('site_logo_file')) {
                    $upload_data = $this->upload->data();
                    $logo_file_name = $upload_data['file_name'];
                    @copy($upload_data['full_path'], $admin_logo_dir . $logo_file_name);
                    @copy($upload_data['full_path'], $admin_logo_dir . 'logo.webp');
                    @copy($upload_data['full_path'], $logo_dir . 'logo.webp');

                    $this->setting_model->set('site_logo', $logo_file_name, 'general');
                } else {
                    $this->session->set_flashdata('error', 'Logo upload error: ' . $this->upload->display_errors('', ''));
                    redirect('settings/general');
                    return;
                }
            }

            // Handle Favicon upload for general settings
            if ($submitted_group === 'general' && !empty($_FILES['site_favicon_file']['name'])) {
                $fav_dir = admin_storefront_fs_path('assets/images/logo/');
                if (!is_dir($fav_dir)) {
                    @mkdir($fav_dir, 0777, TRUE);
                }
                $admin_fav_dir = FCPATH . 'assets/img/favicon/';
                if (!is_dir($admin_fav_dir)) {
                    @mkdir($admin_fav_dir, 0777, TRUE);
                }

                $fav_config = [
                    'upload_path'   => $fav_dir,
                    'allowed_types' => 'gif|jpg|jpeg|png|webp|svg|ico',
                    'max_size'      => 2048,
                    'file_name'     => 'favicon_' . time() . '_' . rand(100, 999)
                ];

                if (!isset($this->upload)) {
                    $this->load->library('upload', $fav_config);
                } else {
                    $this->upload->initialize($fav_config);
                }

                if ($this->upload->do_upload('site_favicon_file')) {
                    $fav_data = $this->upload->data();
                    $fav_file_name = $fav_data['file_name'];
                    @copy($fav_data['full_path'], $admin_fav_dir . $fav_file_name);
                    @copy($fav_data['full_path'], $admin_fav_dir . 'codeulas_logo_small.webp');
                    @copy($fav_data['full_path'], $fav_dir . 'codeulas_logo_small.webp');

                    $this->setting_model->set('site_favicon', $fav_file_name, 'general');
                } else {
                    $this->session->set_flashdata('error', 'Favicon upload error: ' . $this->upload->display_errors('', ''));
                    redirect('settings/general');
                    return;
                }
            }

            // Dynamic shipping charges handling
            if ($submitted_group === 'shipping') {
                $names  = $this->input->post('shipping_charge_names');
                $values = $this->input->post('shipping_charge_values');
                $charges = [];
                $total_shipping = 0.00;
                if (is_array($names) && is_array($values)) {
                    for ($i = 0; $i < count($names); $i++) {
                        $c_name = trim($names[$i] ?? '');
                        $c_val  = (float) ($values[$i] ?? 0);
                        if ($c_name !== '') {
                            $charges[] = [
                                'name'  => $c_name,
                                'value' => $c_val
                            ];
                            $total_shipping += $c_val;
                        }
                    }
                }
                $this->setting_model->set('shipping_charges', json_encode($charges), 'shipping');
                $this->setting_model->set('shipping_flat_rate', number_format($total_shipping, 2, '.', ''), 'shipping');

                unset($post_data['shipping_charge_names']);
                unset($post_data['shipping_charge_values']);
            }

            // Ensure tax settings synchronization
            if ($submitted_group === 'tax') {
                if (isset($post_data['tax_rate'])) {
                    $this->setting_model->set('tax_rate_percent', $post_data['tax_rate'], 'tax');
                }
            }

            // Save text settings
            foreach ($post_data as $key => $val) {
                if ($key !== 'setting_group' && !is_array($val)) {
                    $this->setting_model->set($key, $val, $submitted_group);
                }
            }

            // Ensure both currency and currency_code are synchronized
            if ($submitted_group === 'general' && isset($post_data['currency_code'])) {
                $this->setting_model->set('currency', $post_data['currency_code'], 'general');
            }

            $this->session->set_flashdata('success', ucfirst($submitted_group) . ' settings updated successfully.');
            redirect('settings/' . $submitted_group);
        }

        $titles = [
            'general'  => 'General Store Settings',
            'shipping' => 'Shipping Settings & Rates',
            'tax'      => 'Tax Calculation Rules',
            'email'    => 'Email (SMTP) Settings',
            'sms'      => 'SMS Notification Gateway',
            'seo'      => 'SEO & Meta Settings'
        ];

        $data = [
            'title'          => ($titles[$group] ?? 'Store Settings') . ' | Modave Admin',
            'page_heading'   => $titles[$group] ?? 'Store Settings',
            'active_menu'    => 'settings',
            'active_submenu' => $group,
            'active_tab'     => $group,
            'settings'       => $this->setting_model->get_all()
        ];

        $this->render('settings/index', $data);
    }

    public function payment()
    {
        $this->require_permission('gateways.manage');

        if ($this->input->method() === 'post') {
            $this->setting_model->get_payment_gateways(); // Ensure tenant gateway rows exist
            $gateway = $this->input->post('gateway', TRUE);

            if ($gateway === 'active_online_gateway') {
                $chosen_online = $this->input->post('active_online_gateway', TRUE);
                $allowed = ['razorpay', 'stripe', 'payu', 'none'];
                if (in_array($chosen_online, $allowed)) {
                    if ($chosen_online !== 'none') {
                        $gw_check = $this->setting_model->get_payment_gateway($chosen_online);
                        $gw_creds = !empty($gw_check['credentials_decoded']) 
                            ? $gw_check['credentials_decoded'] 
                            : (!empty($gw_check['credentials']) ? (is_array($gw_check['credentials']) ? $gw_check['credentials'] : json_decode($gw_check['credentials'], true)) : []);
                        $has_creds = false;
                        if ($chosen_online === 'razorpay' && !empty(trim($gw_creds['key_id'] ?? '')) && !empty(trim($gw_creds['key_secret'] ?? ''))) {
                            $has_creds = true;
                        } elseif ($chosen_online === 'stripe' && !empty(trim($gw_creds['publishable_key'] ?? '')) && !empty(trim($gw_creds['secret_key'] ?? ''))) {
                            $has_creds = true;
                        } elseif ($chosen_online === 'payu' && !empty(trim($gw_creds['merchant_key'] ?? '')) && !empty(trim($gw_creds['merchant_salt'] ?? ''))) {
                            $has_creds = true;
                        }
                        if (!$has_creds) {
                            $this->session->set_flashdata('error', 'Cannot activate ' . ucfirst($chosen_online) . ' because API keys are not configured. Please enter and save the keys first.');
                            redirect('settings/payment');
                            return;
                        }
                    }
                    // Only one online gateway allowed: activate the chosen one, deactivate others
                    foreach (['razorpay', 'stripe', 'payu'] as $gw_code) {
                        $is_on = ($gw_code === $chosen_online) ? 1 : 0;
                        $this->db->where('tenant_id', $this->tenant_id)->where('gateway_code', $gw_code)->update('payment_gateways', ['is_active' => $is_on]);
                    }
                    $this->setting_model->set('active_online_gateway', $chosen_online, 'payment');
                    if ($chosen_online === 'none') {
                        $this->setting_model->set('default_payment_gateway', 'cod', 'payment');
                        $this->session->set_flashdata('success', 'All online payment gateways disabled. Store is now operating in Cash on Delivery (COD) only mode.');
                    } else {
                        $this->setting_model->set('default_payment_gateway', $chosen_online, 'payment');
                        $this->session->set_flashdata('success', strtoupper($chosen_online) . ' is now the ACTIVE online payment gateway. Other online gateways have been deactivated.');
                    }
                }
                redirect('settings/payment');
                return;
            }

            if ($gateway === 'stripe') {
                $is_active = $this->input->post('stripe_active') ? 1 : 0;
                $credentials = [
                    'publishable_key' => trim($this->input->post('stripe_publishable_key', TRUE) ?: ''),
                    'secret_key'      => trim($this->input->post('stripe_secret_key', TRUE) ?: ''),
                    'webhook_secret'  => trim($this->input->post('stripe_webhook_secret', TRUE) ?: '')
                ];
                if ($is_active && (empty($credentials['publishable_key']) || empty($credentials['secret_key']))) {
                    $this->session->set_flashdata('error', 'Stripe Publishable Key and Secret Key are required to activate Stripe.');
                    redirect('settings/payment');
                    return;
                }
                $this->setting_model->update_payment_gateway(
                    'stripe',
                    $is_active,
                    $this->input->post('stripe_env', TRUE) ?: 'test',
                    $credentials
                );
                if ($is_active) {
                    // Only one online gateway is allowed: deactivate razorpay and payu
                    $this->db->where('tenant_id', $this->tenant_id)->where_in('gateway_code', ['razorpay', 'payu'])->update('payment_gateways', ['is_active' => 0]);
                    $this->setting_model->set('active_online_gateway', 'stripe', 'payment');
                    $this->setting_model->set('default_payment_gateway', 'stripe', 'payment');
                    $this->session->set_flashdata('success', 'Stripe configuration saved and activated as the active online payment gateway.');
                } else {
                    $this->db->where('tenant_id', $this->tenant_id)->where('gateway_code', 'stripe')->update('payment_gateways', ['is_active' => 0]);
                    $this->setting_model->set('active_online_gateway', 'none', 'payment');
                    $this->setting_model->set('default_payment_gateway', 'cod', 'payment');
                    $this->session->set_flashdata('success', 'Stripe configuration saved. Stripe is deactivated (COD only mode).');
                }
                redirect('settings/payment');
                return;
            } elseif ($gateway === 'razorpay') {
                $is_active = $this->input->post('razorpay_active') ? 1 : 0;
                $credentials = [
                    'key_id'     => trim($this->input->post('razorpay_key_id', TRUE) ?: ''),
                    'key_secret' => trim($this->input->post('razorpay_key_secret', TRUE) ?: '')
                ];
                if ($is_active && (empty($credentials['key_id']) || empty($credentials['key_secret']))) {
                    $this->session->set_flashdata('error', 'Razorpay Key ID and Key Secret are required to activate Razorpay.');
                    redirect('settings/payment');
                    return;
                }
                $this->setting_model->update_payment_gateway(
                    'razorpay',
                    $is_active,
                    $this->input->post('razorpay_env', TRUE) ?: 'test',
                    $credentials
                );
                if ($is_active) {
                    // Only one online gateway is allowed: deactivate stripe and payu
                    $this->db->where('tenant_id', $this->tenant_id)->where_in('gateway_code', ['stripe', 'payu'])->update('payment_gateways', ['is_active' => 0]);
                    $this->setting_model->set('active_online_gateway', 'razorpay', 'payment');
                    $this->setting_model->set('default_payment_gateway', 'razorpay', 'payment');
                    $this->session->set_flashdata('success', 'Razorpay configuration saved and activated as the active online payment gateway.');
                } else {
                    $this->db->where('tenant_id', $this->tenant_id)->where('gateway_code', 'razorpay')->update('payment_gateways', ['is_active' => 0]);
                    $this->setting_model->set('active_online_gateway', 'none', 'payment');
                    $this->setting_model->set('default_payment_gateway', 'cod', 'payment');
                    $this->session->set_flashdata('success', 'Razorpay configuration saved. Razorpay is deactivated (COD only mode).');
                }
                redirect('settings/payment');
                return;
            } elseif ($gateway === 'payu') {
                $is_active = $this->input->post('payu_active') ? 1 : 0;
                $credentials = [
                    'merchant_key'  => trim($this->input->post('payu_merchant_key', TRUE) ?: ''),
                    'merchant_salt' => trim($this->input->post('payu_merchant_salt', TRUE) ?: ''),
                    'test_url'      => 'https://test.payu.in/_payment',
                    'live_url'      => 'https://secure.payu.in/_payment'
                ];
                if ($is_active && (empty($credentials['merchant_key']) || empty($credentials['merchant_salt']))) {
                    $this->session->set_flashdata('error', 'PayU Merchant Key and Salt are required to activate PayU.');
                    redirect('settings/payment');
                    return;
                }
                $this->setting_model->update_payment_gateway(
                    'payu',
                    $is_active,
                    $this->input->post('payu_env', TRUE) ?: 'test',
                    $credentials
                );
                if ($is_active) {
                    // Only one online gateway is allowed: deactivate stripe and razorpay
                    $this->db->where('tenant_id', $this->tenant_id)->where_in('gateway_code', ['stripe', 'razorpay'])->update('payment_gateways', ['is_active' => 0]);
                    $this->setting_model->set('active_online_gateway', 'payu', 'payment');
                    $this->setting_model->set('default_payment_gateway', 'payu', 'payment');
                    $this->session->set_flashdata('success', 'PayU configuration saved and activated as the active online payment gateway.');
                } else {
                    $this->db->where('tenant_id', $this->tenant_id)->where('gateway_code', 'payu')->update('payment_gateways', ['is_active' => 0]);
                    $this->setting_model->set('active_online_gateway', 'none', 'payment');
                    $this->setting_model->set('default_payment_gateway', 'cod', 'payment');
                    $this->session->set_flashdata('success', 'PayU configuration saved. PayU is deactivated (COD only mode).');
                }
                redirect('settings/payment');
                return;
            } elseif ($gateway === 'cod') {
                $credentials = [
                    'instructions' => $this->input->post('cod_instructions', TRUE) ?: 'Pay with cash upon physical delivery of your package.'
                ];
                $cod_active = $this->input->post('cod_active') ? 1 : 0;
                $this->setting_model->update_payment_gateway(
                    'cod',
                    $cod_active,
                    'live',
                    $credentials
                );
                $this->session->set_flashdata('success', 'Cash on Delivery (COD) settings updated (' . ($cod_active ? 'Enabled' : 'Disabled') . ').');
                redirect('settings/payment');
                return;
            }

            $this->session->set_flashdata('success', strtoupper($gateway) . ' gateway configuration updated.');
            redirect('settings/payment');
            return;
        }

        $gateways = $this->setting_model->get_payment_gateways();
        $stored_online = $this->setting_model->get('active_online_gateway', 'none');

        // Check which online gateway is actually active in payment_gateways table
        $active_online = 'none';
        foreach ($gateways as $g) {
            if (in_array($g['gateway_code'], ['razorpay', 'stripe', 'payu']) && !empty($g['is_active'])) {
                $active_online = $g['gateway_code'];
                break;
            }
        }

        // Keep settings table synchronized with actual active status
        if ($stored_online !== $active_online) {
            $this->setting_model->set('active_online_gateway', $active_online, 'payment');
        }

        $data = [
            'title'                 => 'Payment Gateways Configuration | Admin',
            'active_menu'           => 'settings',
            'active_submenu'        => 'payment_gateways',
            'gateways'              => $gateways,
            'active_online_gateway' => $active_online
        ];

        $this->render('settings/payment', $data);
    }
}
