<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Super Admin Helper Functions (Ecommerce SaaS Platform)
 */

if (!function_exists('superadmin_url')) {
    function superadmin_url($uri = '') {
        return base_url($uri);
    }
}

if (!function_exists('superadmin_asset')) {
    function superadmin_asset($path = '') {
        $p = ltrim($path, '/');
        return base_url('assets/' . $p);
    }
}

if (!function_exists('tenant_admin_url')) {
    function tenant_admin_url($uri = '') {
        $base = base_url();
        $target = str_replace('/superadmin/', '/admin/', $base);
        return rtrim($target, '/') . '/' . ltrim($uri, '/');
    }
}

if (!function_exists('main_site_url')) {
    function main_site_url($uri = '') {
        $base = base_url();
        $target = str_replace('/superadmin/', '/', $base);
        return rtrim($target, '/') . '/' . ltrim($uri, '/');
    }
}

if (!function_exists('tenant_site_url')) {
    function tenant_site_url($uri = '') {
        $base = base_url();
        $target = str_replace('/superadmin/', '/website/', $base);
        return rtrim($target, '/') . '/' . ltrim($uri, '/');
    }
}

if (!function_exists('superadmin_impersonate_url')) {
    function superadmin_impersonate_url($tenant_id) {
        $tid = (int)$tenant_id;
        $ts  = time();
        $sig = hash_hmac('sha256', $tid . '|' . $ts, 'SAAS_SUPERADMIN_IMPERSONATE_SECRET_2026');
        return tenant_admin_url('auth/login?impersonate_tenant=' . $tid . '&ts=' . $ts . '&sig=' . $sig);
    }
}

if (!function_exists('get_settings_cache')) {
    function &get_settings_cache() {
        static $settings = null;
        if ($settings === null) {
            $CI =& get_instance();
            $settings = array();
            if (isset($CI->db) && $CI->db->table_exists('saas_settings')) {
                $query = $CI->db->get('saas_settings');
                if ($query) {
                    foreach ($query->result() as $row) {
                        if (isset($row->key_name)) {
                            $settings[$row->key_name] = isset($row->key_value) ? $row->key_value : '';
                        }
                    }
                }
            }
        }
        return $settings;
    }
}

if (!function_exists('get_setting')) {
    function get_setting($key, $default = '') {
        $settings =& get_settings_cache();
        return isset($settings[$key]) && $settings[$key] !== '' ? $settings[$key] : $default;
    }
}

if (!function_exists('set_setting')) {
    function set_setting($key, $value, $group = 'general') {
        $CI =& get_instance();
        $settings =& get_settings_cache();
        $settings[$key] = $value;

        $check = $CI->db->get_where('saas_settings', array('key_name' => $key))->row();
        if ($check) {
            $CI->db->where('key_name', $key)->update('saas_settings', array(
                'key_value' => (string)$value
            ));
        } else {
            $CI->db->insert('saas_settings', array(
                'key_name'  => $key,
                'key_value' => (string)$value
            ));
        }
        return true;
    }
}

if (!function_exists('format_currency')) {
    function format_currency($amount, $show_symbol = true) {
        $symbol = get_setting('currency_symbol', '$');
        $pos = get_setting('currency_position', 'left');
        $formatted = number_format((float)$amount, 2);

        if (!$show_symbol) {
            return $formatted;
        }

        return ($pos === 'left') ? ($symbol . $formatted) : ($formatted . ' ' . $symbol);
    }
}

if (!function_exists('format_custom_date')) {
    function format_custom_date($date, $format = 'd M Y, h:i A') {
        if (!$date || $date === '0000-00-00 00:00:00' || $date === '0000-00-00') return 'N/A';
        return date($format, strtotime($date));
    }
}

if (!function_exists('status_badge')) {
    function status_badge($status) {
        $s = strtolower((string)$status);
        switch ($s) {
            case 'paid':
            case 'active':
            case 'completed':
            case 'success':
            case '1':
                return '<span class="badge bg-success-subtle text-success border border-success-subtle fw-semibold px-2 py-1"><i class="fas fa-check-circle me-1"></i>' . ($s === '1' ? 'Active' : ucfirst($s)) . '</span>';
            case 'pending':
            case 'trial':
                return '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-semibold px-2 py-1"><i class="fas fa-clock me-1"></i>' . ucfirst($s) . '</span>';
            case 'suspended':
            case 'failed':
            case 'cancelled':
            case 'expired':
                return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-semibold px-2 py-1"><i class="fas fa-times-circle me-1"></i>' . ucfirst($s) . '</span>';
            case 'revoked':
            case 'inactive':
            case '0':
                return '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle fw-semibold px-2 py-1"><i class="fas fa-ban me-1"></i>' . ($s === '0' ? 'Inactive' : ucfirst($s)) . '</span>';
            default:
                return '<span class="badge bg-light text-dark border px-2 py-1">' . htmlspecialchars(ucfirst($s)) . '</span>';
        }
    }
}

if (!function_exists('template_badge')) {
    function template_badge($tpl) {
        $c = strtolower(trim((string)$tpl));
        if ($c === 'fashion') {
            return '<span class="badge bg-info text-white fw-semibold px-2 py-1"><i class="fas fa-shirt me-1"></i>Fashion</span>';
        } elseif ($c === 'electronics') {
            return '<span class="badge bg-primary text-white fw-semibold px-2 py-1"><i class="fas fa-microchip me-1"></i>Electronics</span>';
        } elseif ($c === 'grocery') {
            return '<span class="badge bg-success text-white fw-semibold px-2 py-1"><i class="fas fa-basket-shopping me-1"></i>Grocery</span>';
        }
        return '<span class="badge bg-light text-dark border px-2 py-1">' . htmlspecialchars(ucfirst($c ?: 'Fashion')) . '</span>';
    }
}

if (!function_exists('plan_badge')) {
    function plan_badge($code) {
        $c = strtolower((string)$code);
        if (strpos($c, 'fashion') !== false || strpos($c, 'starter') !== false) {
            return '<span class="badge bg-info text-white fw-bold px-2 py-1"><i class="fas fa-layer-group me-1"></i>' . htmlspecialchars((string)$code) . '</span>';
        } elseif (strpos($c, 'electronics') !== false || strpos($c, 'growth') !== false) {
            return '<span class="badge bg-primary text-white fw-bold px-2 py-1"><i class="fas fa-rocket me-1"></i>' . htmlspecialchars((string)$code) . '</span>';
        } elseif (strpos($c, 'grocery') !== false || strpos($c, 'enterprise') !== false) {
            return '<span class="badge bg-warning text-dark fw-bold px-2 py-1"><i class="fas fa-crown me-1"></i>' . htmlspecialchars((string)$code) . '</span>';
        }
        return '<span class="badge bg-warning text-dark fw-bold px-2 py-1"><i class="fas fa-crown me-1"></i>' . htmlspecialchars((string)$code) . '</span>';
    }
}

if (!function_exists('fallback_image_url')) {
    function fallback_image_url($url = '') {
        $root_url = rtrim(main_site_url(), '/') . '/';
        $default_img = $root_url . 'website/assets/images/slider/slider-women1.jpg';
        if (empty($url)) {
            return $default_img;
        }
        if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
            return $url;
        }
        $rel = ltrim($url, '/\\');
        $root_path = dirname(FCPATH) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
        if (file_exists($root_path)) {
            return $root_url . $rel;
        }
        return $default_img;
    }
}

if (!function_exists('superadmin_logo_url')) {
    function superadmin_logo_url() {
        $logo = get_setting('landing_site_logo', 'admin/assets/img/branding/logo.webp');
        if (empty($logo)) {
            $logo = 'admin/assets/img/branding/logo.webp';
        }
        if (strpos($logo, 'http://') === 0 || strpos($logo, 'https://') === 0) {
            return $logo;
        }
        return main_site_url(ltrim($logo, '/\\'));
    }
}

if (!function_exists('superadmin_favicon_url')) {
    function superadmin_favicon_url() {
        $fav = get_setting('landing_site_favicon', 'admin/assets/img/branding/logo.webp');
        if (empty($fav)) {
            $fav = 'admin/assets/img/branding/logo.webp';
        }
        if (strpos($fav, 'http://') === 0 || strpos($fav, 'https://') === 0) {
            return $fav;
        }
        return main_site_url(ltrim($fav, '/\\'));
    }
}

if (!function_exists('get_tpl_setting')) {
    function get_tpl_setting($template_key, $layout_number, $section_key, $setting_key, $default = '') {
        $CI =& get_instance();
        if (!$CI->db->table_exists('template_layout_settings')) {
            return $default;
        }
        $row = $CI->db->where('template_key', $template_key)
                      ->where('layout_number', (int)$layout_number)
                      ->where('section_key', $section_key)
                      ->where('setting_key', $setting_key)
                      ->get('template_layout_settings')
                      ->row();
        return ($row && $row->setting_value !== null && $row->setting_value !== '') ? $row->setting_value : $default;
    }
}

if (!function_exists('set_tpl_setting')) {
    function set_tpl_setting($template_key, $layout_number, $section_key, $setting_key, $value) {
        $CI =& get_instance();
        if (!$CI->db->table_exists('template_layout_settings')) {
            return false;
        }
        $existing = $CI->db->where('template_key', $template_key)
                           ->where('layout_number', (int)$layout_number)
                           ->where('section_key', $section_key)
                           ->where('setting_key', $setting_key)
                           ->get('template_layout_settings')
                           ->row();
        if ($existing) {
            $CI->db->where('id', $existing->id)->update('template_layout_settings', array(
                'setting_value' => (string)$value
            ));
        } else {
            $CI->db->insert('template_layout_settings', array(
                'template_key'  => $template_key,
                'layout_number' => (int)$layout_number,
                'section_key'   => $section_key,
                'setting_key'   => $setting_key,
                'setting_value' => (string)$value
            ));
        }
        return true;
    }
}

if (!function_exists('resolve_tpl_image_url')) {
    function resolve_tpl_image_url($path, $default = 'website/assets/images/slider/slider-women1.jpg') {
        $root_url  = rtrim(main_site_url(), '/') . '/';
        $web_url   = rtrim(tenant_site_url(), '/') . '/';
        if (empty($path)) {
            return $root_url . ltrim($default, '/');
        }
        if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
            return $path;
        }
        $clean = ltrim($path, '/\\');
        if (strpos($clean, 'website/') === 0) {
            return $root_url . $clean;
        }
        if (strpos($clean, 'assets/') === 0 || strpos($clean, 'uploads/') === 0) {
            return $web_url . $clean;
        }
        return $web_url . 'assets/images/' . $clean;
    }
}

