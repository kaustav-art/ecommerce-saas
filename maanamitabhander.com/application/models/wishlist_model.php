<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class wishlist_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    private function tid()
    {
        $CI =& get_instance();
        return isset($CI->tenant_id) ? (int) $CI->tenant_id : 1;
    }

    public function get_by_user($user_id)
    {
        $tid = $this->tid();
        $products = $this->db->select('w.id as wishlist_id, w.created_at as wishlisted_at, p.*, c.name as category_name, c.slug as category_slug')
                             ->from('wishlists w')
                             ->join('products p', 'p.id = w.product_id AND p.tenant_id = ' . $tid)
                             ->join('categories c', 'c.id = p.category_id', 'left')
                             ->where('w.tenant_id', $tid)
                             ->where('w.user_id', (int) $user_id)
                             ->where('p.status', 'published')
                             ->order_by('w.id', 'DESC')
                             ->get()
                             ->result_array();

        $this->load->model('product_model');
        foreach ($products as &$p) {
            $this->enrich_with_variant_pricing($p);
            $this->product_model->apply_tax_pricing($p);
        }
        return $products;
    }

    public function get_by_ids($product_ids)
    {
        if (empty($product_ids)) return [];
        $tid = $this->tid();
        $product_ids = array_values(array_filter(array_map('intval', (array)$product_ids)));
        if (empty($product_ids)) return [];

        $products = $this->db->select('p.id as wishlist_id, p.created_at as wishlisted_at, p.*, c.name as category_name, c.slug as category_slug')
                             ->from('products p')
                             ->join('categories c', 'c.id = p.category_id', 'left')
                             ->where('p.tenant_id', $tid)
                             ->where_in('p.id', $product_ids)
                             ->where('p.status', 'published')
                             ->get()
                             ->result_array();

        $this->load->model('product_model');
        foreach ($products as &$p) {
            $this->enrich_with_variant_pricing($p);
            $this->product_model->apply_tax_pricing($p);
        }
        return $products;
    }

    public function enrich_with_variant_pricing(&$p)
    {
        if (empty($p['id'])) return;
        $tid = $this->tid();
        $variants = $this->db->where('tenant_id', $tid)
                             ->where('product_id', (int)$p['id'])
                             ->order_by('id', 'ASC')
                             ->get('product_variants')
                             ->result_array();
        if (!empty($variants)) {
            $min_sale = null;
            $min_price = null;
            $first_img = null;
            foreach ($variants as $v) {
                if (empty($first_img) && !empty($v['image'])) {
                    $first_img = $v['image'];
                }
                $v_price = (float)($v['price'] ?? 0);
                $v_sale = (!empty($v['sale_price']) && (float)$v['sale_price'] > 0) ? (float)$v['sale_price'] : 0;
                if ($v_sale > 0) {
                    if ($min_sale === null || $v_sale < $min_sale) {
                        $min_sale = $v_sale;
                        $min_price = $v_price > 0 ? $v_price : (float)($p['price'] ?? 0);
                    }
                } elseif ($min_sale === null && $v_price > 0) {
                    if ($min_price === null || $v_price < $min_price) {
                        $min_price = $v_price;
                    }
                }
            }
            if ($min_sale !== null && $min_sale > 0) {
                $p['sale_price'] = $min_sale;
                if ($min_price !== null && $min_price > 0) {
                    $p['price'] = $min_price;
                }
            } elseif ($min_price !== null && $min_price > 0 && empty($p['price'])) {
                $p['price'] = $min_price;
            }
            if (!empty($first_img) && (empty($p['main_image']) || $p['main_image'] === 'placeholder.jpg')) {
                $p['main_image'] = $first_img;
            }
        }
    }

    public function is_wishlisted($user_id, $product_id)
    {
        return $this->db->where('tenant_id', $this->tid())
                        ->where('user_id', (int) $user_id)
                        ->where('product_id', (int) $product_id)
                        ->count_all_results('wishlists') > 0;
    }

    public function toggle($user_id, $product_id)
    {
        $tid = $this->tid();
        $existing = $this->db->where('tenant_id', $tid)
                             ->where('user_id', (int) $user_id)
                             ->where('product_id', (int) $product_id)
                             ->get('wishlists')
                             ->row_array();

        if ($existing) {
            $this->db->where('id', $existing['id'])->delete('wishlists');
            return 'removed';
        } else {
            $this->db->insert('wishlists', [
                'tenant_id'  => $tid,
                'user_id'    => (int) $user_id,
                'product_id' => (int) $product_id
            ]);
            return 'added';
        }
    }

    public function remove($user_id, $product_id)
    {
        return $this->db->where('tenant_id', $this->tid())
                        ->where('user_id', (int) $user_id)
                        ->where('product_id', (int) $product_id)
                        ->delete('wishlists');
    }
}
