<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends Superadmin_Controller {

    public function index() {
        if ($this->input->method() === 'post') {
            $action = $this->input->post('action', TRUE);
            $active_tab = $this->input->post('active_tab', TRUE) ?: 'currency';

            if ($action === 'update_general_currency') {
                $support_email = $this->input->post('support_email', TRUE);
                $currency_symbol = $this->input->post('currency_symbol', TRUE);
                $currency_code = strtoupper(trim((string)$this->input->post('currency_code', TRUE)));
                $currency_position = $this->input->post('currency_position', TRUE);
                $currency_decimals = $this->input->post('currency_decimals', TRUE);
                $demo_mode = $this->input->post('demo_mode', TRUE);
                $timezone = $this->input->post('timezone', TRUE);

                set_setting('support_email', $support_email, 'general');
                set_setting('saas_support_email', $support_email, 'general');
                set_setting('currency_symbol', $currency_symbol, 'localization');
                set_setting('saas_currency_symbol', $currency_symbol, 'localization');
                set_setting('currency_code', !empty($currency_code) ? $currency_code : 'USD', 'localization');
                set_setting('saas_currency_code', !empty($currency_code) ? $currency_code : 'USD', 'localization');
                set_setting('currency_position', in_array($currency_position, array('left', 'right')) ? $currency_position : 'left', 'localization');
                set_setting('currency_decimals', in_array($currency_decimals, array('0', '2')) ? $currency_decimals : '2', 'localization');
                set_setting('demo_mode', $demo_mode === '1' ? '1' : '0', 'general');
                if (!empty($timezone)) {
                    set_setting('timezone', $timezone, 'localization');
                }

                $this->session->set_flashdata('success', 'General, Dynamic Currency & Branding settings saved successfully.');
                redirect(superadmin_url('settings?tab=currency'));
                return;

            } elseif ($action === 'update_payment_gateways') {
                $active_gw = $this->input->post('active_payment_gateway', TRUE);
                $valid_gws = array('stripe', 'razorpay', 'payu', 'offline', 'sandbox');
                if (!in_array($active_gw, $valid_gws)) {
                    $active_gw = 'sandbox';
                }
                // Normalize 'offline' radio option in view to 'sandbox' used by create_payment.php
                $normalized_gw = ($active_gw === 'offline') ? 'sandbox' : $active_gw;
                set_setting('active_payment_gateway', $normalized_gw, 'payment');

                // Stripe Settings (save both prefixed and unprefixed keys)
                $stripe_pub = trim((string)$this->input->post('gateway_stripe_publishable_key'));
                $stripe_sec = trim((string)$this->input->post('gateway_stripe_secret_key'));
                $stripe_mode = $this->input->post('gateway_stripe_mode') === 'live' ? 'live' : 'test';
                $stripe_wh = trim((string)$this->input->post('gateway_stripe_webhook_secret'));

                set_setting('gateway_stripe_publishable_key', $stripe_pub, 'payment');
                set_setting('stripe_publishable_key', $stripe_pub, 'payment');
                set_setting('gateway_stripe_secret_key', $stripe_sec, 'payment');
                set_setting('stripe_secret_key', $stripe_sec, 'payment');
                set_setting('gateway_stripe_mode', $stripe_mode, 'payment');
                set_setting('gateway_stripe_webhook_secret', $stripe_wh, 'payment');
                set_setting('stripe_webhook_secret', $stripe_wh, 'payment');

                // Razorpay Settings
                $rzp_id = trim((string)$this->input->post('gateway_razorpay_key_id'));
                $rzp_sec = trim((string)$this->input->post('gateway_razorpay_key_secret'));
                $rzp_mode = $this->input->post('gateway_razorpay_mode') === 'live' ? 'live' : 'test';

                set_setting('gateway_razorpay_key_id', $rzp_id, 'payment');
                set_setting('razorpay_key_id', $rzp_id, 'payment');
                set_setting('gateway_razorpay_key_secret', $rzp_sec, 'payment');
                set_setting('razorpay_key_secret', $rzp_sec, 'payment');
                set_setting('gateway_razorpay_mode', $rzp_mode, 'payment');

                // PayU Settings
                $payu_key = trim((string)$this->input->post('gateway_payu_merchant_key'));
                $payu_salt = trim((string)$this->input->post('gateway_payu_merchant_salt'));
                $payu_mode = $this->input->post('gateway_payu_mode') === 'live' ? 'live' : 'test';

                set_setting('gateway_payu_merchant_key', $payu_key, 'payment');
                set_setting('payu_merchant_key', $payu_key, 'payment');
                set_setting('gateway_payu_merchant_salt', $payu_salt, 'payment');
                set_setting('payu_merchant_salt', $payu_salt, 'payment');
                set_setting('gateway_payu_mode', $payu_mode, 'payment');
                set_setting('payu_mode', $payu_mode, 'payment');

                $this->session->set_flashdata('success', 'SaaS Subscription Payment Gateway updated! Active gateway: ' . strtoupper($normalized_gw));
                redirect(superadmin_url('settings?tab=gateways'));
                return;

            } elseif ($action === 'update_smtp_email') {
                set_setting('smtp_status', $this->input->post('smtp_status') === 'enabled' ? 'enabled' : 'disabled', 'email');
                set_setting('smtp_host', trim((string)$this->input->post('smtp_host')), 'email');
                set_setting('smtp_port', trim((string)$this->input->post('smtp_port')), 'email');
                set_setting('smtp_user', trim((string)$this->input->post('smtp_user')), 'email');

                $pass = $this->input->post('smtp_pass');
                if (!empty($pass)) {
                    set_setting('smtp_pass', $pass, 'email');
                }

                set_setting('smtp_crypto', in_array($this->input->post('smtp_crypto'), array('tls', 'ssl', 'none')) ? $this->input->post('smtp_crypto') : 'tls', 'email');
                set_setting('smtp_from_email', trim((string)$this->input->post('smtp_from_email')), 'email');
                set_setting('smtp_from_name', trim((string)$this->input->post('smtp_from_name')), 'email');

                $this->session->set_flashdata('success', 'SMTP Email Gateway configuration updated successfully.');
                redirect(superadmin_url('settings?tab=smtp'));
                return;

            } elseif ($action === 'clear_cache') {
                $cache_path = APPPATH . 'cache/';
                $files = glob($cache_path . '*');
                $count = 0;
                if (is_array($files)) {
                    foreach ($files as $file) {
                        if (is_file($file) && basename($file) !== 'index.html' && basename($file) !== '.htaccess') {
                            @unlink($file);
                            $count++;
                        }
                    }
                }
                $this->session->set_flashdata('success', 'Cleared ' . $count . ' cache file(s).');
                redirect(superadmin_url('settings?tab=system'));
                return;
            }
        }

        // Diagnostics
        $data['php_version'] = phpversion();
        $data['server_software'] = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Apache/PHP';
        $data['memory_limit'] = ini_get('memory_limit');
        $data['max_execution_time'] = ini_get('max_execution_time') . 's';
        $data['upload_max_filesize'] = ini_get('upload_max_filesize');

        $tables = $this->db->query("SHOW TABLE STATUS")->result();
        $data['tables'] = $tables;
        $total_size = 0;
        foreach ($tables as $t) {
            $total_size += ($t->Data_length + $t->Index_length);
        }
        $data['total_db_size_mb'] = round($total_size / (1024 * 1024), 2);

        // Load all platform settings from saas_settings
        $settings_query = $this->db->get('saas_settings')->result();
        $settings = array();
        foreach ($settings_query as $row) {
            if (isset($row->key_name)) {
                $settings[$row->key_name] = $row->key_value;
            }
        }
        $data['settings'] = $settings;

        $data['support_email'] = isset($settings['support_email']) ? $settings['support_email'] : (isset($settings['saas_support_email']) ? $settings['saas_support_email'] : 'support@ecommerce.com');
        $data['currency_symbol'] = isset($settings['currency_symbol']) ? $settings['currency_symbol'] : (isset($settings['saas_currency_symbol']) ? $settings['saas_currency_symbol'] : '$');
        $data['currency_code'] = isset($settings['currency_code']) ? $settings['currency_code'] : (isset($settings['saas_currency_code']) ? $settings['saas_currency_code'] : 'USD');
        $data['currency_position'] = isset($settings['currency_position']) ? $settings['currency_position'] : 'left';
        $data['currency_decimals'] = isset($settings['currency_decimals']) ? $settings['currency_decimals'] : '2';
        $data['demo_mode'] = isset($settings['demo_mode']) ? $settings['demo_mode'] : '1';
        $data['timezone'] = isset($settings['timezone']) ? $settings['timezone'] : 'UTC';

        $gw = isset($settings['active_payment_gateway']) ? $settings['active_payment_gateway'] : 'sandbox';
        $data['active_payment_gateway'] = ($gw === 'sandbox') ? 'offline' : $gw;

        $data['active_tab'] = $this->input->get('tab', TRUE) ?: 'currency';

        $this->render('settings/index', $data, 'Platform Settings & SaaS Payment Gateways');
    }

    public function backup() {
        $this->load->dbutil();
        $prefs = array(
            'format'     => 'txt',
            'filename'   => 'ecommerce_saas_backup_' . date('Y-m-d_H-i-s') . '.sql',
            'add_drop'   => TRUE,
            'add_insert' => TRUE,
            'newline'    => "\n"
        );
        $backup = $this->dbutil->backup($prefs);
        $this->load->helper('download');
        force_download('ecommerce_saas_backup_' . date('Y-m-d_His') . '.sql', $backup);
    }
}
