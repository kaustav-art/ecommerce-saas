<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Template_registry
 * Central abstraction for managing and rendering the 3 SaaS storefront templates:
 * 1. template1 / fashion     (Template 1 - Modave Fashion Storefront -> views/template1/)
 * 2. template2 / electronics (Template 2 - Bazaro Electronics Storefront -> views/template2/)
 * 3. template3 / grocery     (Template 3 - Bazaro Grocery Supermarket Storefront -> views/template3/)
 */
class Template_registry {

    protected $CI;
    protected $templates = [];

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->register_defaults();
    }

    protected function register_defaults()
    {
        $fashion_cfg = [
            'key'         => 'fashion',
            'tpl_key'     => 'template1',
            'folder'      => 'template1',
            'name'        => 'Fashion Store (Template 1)',
            'header_view' => 'template1/layouts/header',
            'navbar_view' => 'template1/layouts/navbar',
            'footer_view' => 'template1/layouts/footer',
            'modals_view' => 'template1/layouts/modals',
            'home_view'   => 'template1/home/index',
            'assets_path' => 'assets/',
        ];

        $electronics_cfg = [
            'key'         => 'electronics',
            'tpl_key'     => 'template2',
            'folder'      => 'template2',
            'name'        => 'Electronics Store (Template 2)',
            'header_view' => 'template2/header',
            'navbar_view' => null, // integrated in header
            'footer_view' => 'template2/footer',
            'modals_view' => 'template1/modals',
            'home_view'   => 'template2/home',
            'assets_path' => 'assets/templates/bazaro/',
        ];

        $grocery_cfg = [
            'key'         => 'grocery',
            'tpl_key'     => 'template3',
            'folder'      => 'template3',
            'name'        => 'Grocery Store (Template 3)',
            'header_view' => 'template3/header',
            'navbar_view' => null, // integrated in header
            'footer_view' => 'template3/footer',
            'modals_view' => 'template1/modals',
            'home_view'   => 'template3/home',
            'assets_path' => 'assets/templates/bazaro/',
        ];

        $this->templates = [
            'fashion'     => $fashion_cfg,
            'template1'   => $fashion_cfg,
            'electronics' => $electronics_cfg,
            'template2'   => $electronics_cfg,
            'grocery'     => $grocery_cfg,
            'template3'   => $grocery_cfg,
        ];
    }

    public function is_valid($key)
    {
        return isset($this->templates[strtolower(trim((string)$key))]);
    }

    public function get($key)
    {
        $key = strtolower(trim((string)$key));
        return $this->templates[$key] ?? $this->templates['fashion'];
    }

    public function get_tpl_key($key)
    {
        $tpl = $this->get($key);
        return $tpl['tpl_key'] ?? 'template1';
    }

    public function get_canonical_key($key)
    {
        $tpl = $this->get($key);
        return $tpl['key'] ?? 'fashion';
    }

    public function get_all()
    {
        return [
            'template1' => $this->templates['template1'],
            'template2' => $this->templates['template2'],
            'template3' => $this->templates['template3'],
        ];
    }

    /**
     * Resolve view file for the active template.
     * For home/index, dispatches to the template's dedicated homepage view in views/template1, template2, or template3.
     */
    public function resolve_view($template_key, $view)
    {
        $tpl = $this->get($template_key);
        if ($view === 'home/index' || $view === 'home') {
            return $tpl['home_view'];
        }
        $folder = $tpl['folder'] ?? 'template1';
        $override1 = $folder . '/' . $view;
        if (file_exists(APPPATH . 'views/' . $override1 . '.php')) {
            return $override1;
        }
        if ($tpl['key'] !== 'fashion') {
            $override2 = 'templates/' . $tpl['key'] . '/' . $view;
            if (file_exists(APPPATH . 'views/' . $override2 . '.php')) {
                return $override2;
            }
        }
        return $view;
    }

    public function render($template_key, $view, $data = [], $return = FALSE)
    {
        $tpl = $this->get($template_key);
        $resolved_view = $this->resolve_view($tpl['key'], $view);
        $is_bazaro_home = ($tpl['key'] !== 'fashion');

        $data['active_template']      = $tpl['key'];
        $data['active_tpl_key']       = $tpl['tpl_key'];
        $data['active_template_meta'] = $tpl;
        $data['tpl_assets']           = base_url($tpl['assets_path']);

        if ($return) {
            $content = '';
            if (!empty($tpl['header_view'])) {
                $content .= $this->CI->load->view($tpl['header_view'], $data, TRUE);
            }
            if (!empty($tpl['navbar_view'])) {
                $content .= $this->CI->load->view($tpl['navbar_view'], $data, TRUE);
            }
            $content .= $this->CI->load->view($resolved_view, $data, TRUE);
            if (!empty($data['is_preview_mode']) && file_exists(APPPATH . 'views/layouts/demo_preview_bar.php')) {
                $content .= $this->CI->load->view('layouts/demo_preview_bar', $data, TRUE);
            }
            if (!empty($tpl['footer_view'])) {
                $content .= $this->CI->load->view($tpl['footer_view'], $data, TRUE);
            }
            if (!empty($tpl['modals_view']) && !$is_bazaro_home && file_exists(APPPATH . 'views/' . $tpl['modals_view'] . '.php')) {
                $content .= $this->CI->load->view($tpl['modals_view'], $data, TRUE);
            }
            if (file_exists(APPPATH . 'views/layouts/auth_login_modal.php')) {
                $content .= $this->CI->load->view('layouts/auth_login_modal', $data, TRUE);
            }
            if (file_exists(APPPATH . 'views/layouts/address_modal.php')) {
                $content .= $this->CI->load->view('layouts/address_modal', $data, TRUE);
            }
            return $content;
        }

        if (!empty($tpl['header_view'])) {
            $this->CI->load->view($tpl['header_view'], $data);
        }
        if (!empty($tpl['navbar_view'])) {
            $this->CI->load->view($tpl['navbar_view'], $data);
        }
        $this->CI->load->view($resolved_view, $data);
        if (!empty($data['is_preview_mode']) && file_exists(APPPATH . 'views/layouts/demo_preview_bar.php')) {
            $this->CI->load->view('layouts/demo_preview_bar', $data);
        }
        if (!empty($tpl['footer_view'])) {
            $this->CI->load->view($tpl['footer_view'], $data);
        }
        if (!empty($tpl['modals_view']) && !$is_bazaro_home && file_exists(APPPATH . 'views/' . $tpl['modals_view'] . '.php')) {
            $this->CI->load->view($tpl['modals_view'], $data);
        }
        if (file_exists(APPPATH . 'views/layouts/auth_login_modal.php')) {
            $this->CI->load->view('layouts/auth_login_modal', $data);
        }
        if (file_exists(APPPATH . 'views/layouts/address_modal.php')) {
            $this->CI->load->view('layouts/address_modal', $data);
        }
    }
}
