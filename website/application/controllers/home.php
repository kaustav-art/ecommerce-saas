<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class home extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $tpl_key = $this->template_registry->get_tpl_key($this->active_template);

        // Load Superadmin Template Customizer Section Headings
        $tpl_settings = [];
        if (isset($this->db) && $this->db->table_exists('template_layout_settings')) {
            $s_rows = $this->db->where('template_key', $tpl_key)->get('template_layout_settings')->result_array();
            foreach ($s_rows as $sr) {
                $tpl_settings[$sr['section_key']][$sr['setting_key']] = $sr['setting_value'];
            }
        }

        // Load dynamic Hero Banners, Categories, and Products from Superadmin panel
        $banners        = $this->get_dynamic_template_banners($tpl_key);
        $categories     = $this->get_dynamic_template_categories($tpl_key);
        $products       = $this->get_dynamic_template_products($tpl_key);
        $total_products = count($products);
        $brands         = Static_template_data::get('brands');

        // New Arrival products (limit 10, desc order)
        $new_arrival_products = array_values(array_filter($products, function($p) {
            return !empty($p['is_new']);
        }));
        if (empty($new_arrival_products)) {
            $new_arrival_products = $products;
        }
        usort($new_arrival_products, function($a, $b) {
            return (int)($b['id'] ?? 0) - (int)($a['id'] ?? 0);
        });
        $new_arrival_products = array_slice($new_arrival_products, 0, 10);

        // Popular products (limit 10, desc order)
        $popular_products = array_values(array_filter($products, function($p) {
            return !empty($p['is_featured']) || !empty($p['is_trending']);
        }));
        if (empty($popular_products)) {
            $popular_products = $products;
        }
        usort($popular_products, function($a, $b) {
            return (int)($b['id'] ?? 0) - (int)($a['id'] ?? 0);
        });
        $popular_products = array_slice($popular_products, 0, 10);

        // Flash Sale / Deal of the Week
        $now_str = date('Y-m-d H:i:s');
        $flash_sale = null;
        if (isset($this->db) && $this->db->table_exists('flash_sales')) {
            $flash_sale = $this->db->where('tenant_id', $this->tenant_id)
                                   ->where('status !=', 'terminated')
                                   ->where('start_time <=', $now_str)
                                   ->where('end_time >=', $now_str)
                                   ->order_by('start_time', 'ASC')
                                   ->get('flash_sales')
                                   ->row_array();
        }

        $flash_sale_products = [];
        if ($flash_sale && isset($this->db) && $this->db->table_exists('flash_sale_products')) {
            $fsp_pids = $this->db->select('product_id')
                                 ->where('flash_sale_id', $flash_sale['id'])
                                 ->order_by('id', 'ASC')
                                 ->get('flash_sale_products')
                                 ->result_array();
            $fs_id_list = array_column($fsp_pids, 'product_id');
            $prod_by_id = [];
            foreach ($products as $pr) {
                $prod_by_id[(int)$pr['id']] = $pr;
            }
            foreach ($fs_id_list as $fpid) {
                if (isset($prod_by_id[(int)$fpid])) {
                    $flash_sale_products[] = $prod_by_id[(int)$fpid];
                }
            }
        }

        $deal_products = (!empty($flash_sale) && !empty($flash_sale_products)) ? $flash_sale_products : [];
        $sale_products = !empty($deal_products) ? $deal_products : array_values(array_filter($products, function($p) {
            return !empty($p['sale_price']);
        }));

        $new_products        = $new_arrival_products;
        $featured_products   = $popular_products;
        $sale_products       = $deal_products;
        $bestseller_products = $popular_products;
        $featured_product    = !empty($popular_products[0]) ? $popular_products[0] : (!empty($products[0]) ? $products[0] : Static_template_data::get('common_product'));

        $data = [
            'title'                => $this->site_name . ' - Multipurpose eCommerce',
            'active_page'          => 'home',
            'tpl_settings'         => $tpl_settings,
            'banners'              => $banners,
            'products'             => $products,
            'new_arrival_products' => $new_arrival_products,
            'popular_products'     => $popular_products,
            'flash_sale'           => $flash_sale,
            'flash_sale_products'  => $flash_sale_products,
            'deal_products'        => $deal_products,
            'new_products'         => $new_products,
            'featured_products'    => $featured_products,
            'sale_products'        => $sale_products,
            'bestseller_products'  => $bestseller_products,
            'featured_product'     => $featured_product,
            'categories'           => $categories,
            'nav_categories'       => $categories,
            'category_tree'        => $categories,
            'total_products'       => $total_products,
            'brands'               => $brands
        ];

        $this->render('home/index', $data);
    }

    public function load_more()
    {
        $offset = (int) $this->input->get('offset');
        $limit  = (int) ($this->input->get('limit') ?: 16);
        if ($limit <= 0 || $limit > 50) {
            $limit = 16;
        }
        if ($offset < 0) {
            $offset = 0;
        }

        $tpl_key      = $this->template_registry->get_tpl_key($this->active_template);
        $all_products = $this->get_dynamic_template_products($tpl_key);
        $products     = array_slice($all_products, $offset, $limit);
        $total        = count($all_products);
        $has_more     = ($offset + count($products)) < $total;

        $currency_symbol = $this->store_settings['currency_symbol'] ?? '$';

        $html = '';
        if (!empty($products)) {
            foreach ($products as $p) {
                $html .= $this->load->view('template1/home/_product_card', [
                    'p'               => $p,
                    'currency_symbol' => $currency_symbol
                ], TRUE);
            }
        }

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode([
                 'status'   => 'success',
                 'count'    => count($products),
                 'has_more' => $has_more,
                 'html'     => $html
             ]));
    }
}
