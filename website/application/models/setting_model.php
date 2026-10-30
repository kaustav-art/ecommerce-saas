<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class setting_model extends CI_Model {

    private $settings_cache = null;
    private $cached_tenant_id = null;

    public function __construct()
    {
        parent::__construct();
    }

    private function tid()
    {
        $CI =& get_instance();
        return isset($CI->tenant_id) ? (int) $CI->tenant_id : 1;
    }

    public function get_all()
    {
        $tid = $this->tid();
        if ($this->settings_cache !== null && $this->cached_tenant_id === $tid) {
            return $this->settings_cache;
        }
        $rows = $this->db->where('tenant_id', $tid)->get('settings')->result_array();
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }
        $this->settings_cache = $settings;
        $this->cached_tenant_id = $tid;
        return $settings;
    }

    public function refresh_cache()
    {
        $this->settings_cache = null;
        return $this->get_all();
    }

    public function get($key, $default = NULL)
    {
        if ($this->settings_cache === null || $this->cached_tenant_id !== $this->tid()) {
            $this->get_all();
        }
        return isset($this->settings_cache[$key]) ? $this->settings_cache[$key] : $default;
    }

    public function get_active_gateways()
    {
        $rows = $this->db->where('tenant_id', $this->tid())->where('is_active', 1)->get('payment_gateways')->result_array();
        $active = [];
        foreach ($rows as $row) {
            $creds = json_decode($row['credentials'], true) ?: [];
            $row['credentials'] = $creds;

            // Online gateways must have required credentials provided to be active on storefront
            if ($row['gateway_code'] === 'razorpay') {
                $key_id = trim($creds['key_id'] ?? '');
                $key_secret = trim($creds['key_secret'] ?? '');
                if (empty($key_id) || empty($key_secret)) {
                    continue;
                }
            } elseif ($row['gateway_code'] === 'stripe') {
                $pub_key = trim($creds['publishable_key'] ?? '');
                $sec_key = trim($creds['secret_key'] ?? '');
                if (empty($pub_key) || empty($sec_key)) {
                    continue;
                }
            } elseif ($row['gateway_code'] === 'payu') {
                $m_key = trim($creds['merchant_key'] ?? '');
                $m_salt = trim($creds['merchant_salt'] ?? '');
                if (empty($m_key) || empty($m_salt)) {
                    continue;
                }
            }
            $active[] = $row;
        }
        return $active;
    }

    public function get_gateway($code)
    {
        $row = $this->db->where('tenant_id', $this->tid())->where('gateway_code', $code)->get('payment_gateways')->row_array();
        if ($row) {
            $row['credentials'] = json_decode($row['credentials'], true) ?: [];
        }
        return $row;
    }
}
