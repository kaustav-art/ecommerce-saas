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
            // Select primary variant: prefer '1 kg' if present, else first variant (matching Home page product card)
            $primaryVar = $variants[0];
            foreach ($variants as $vCand) {
                $vTitle = strtolower($vCand['title'] ?? '');
                if (strpos($vTitle, '1 kg') !== false || strpos($vTitle, '1kg') !== false) {
                    $primaryVar = $vCand;
                    break;
                }
            }

            $v_price = (!empty($primaryVar['price']) && (float)$primaryVar['price'] > 0) ? (float)$primaryVar['price'] : (float)($p['price'] ?? 0);
            $v_sale = (!empty($primaryVar['sale_price']) && (float)$primaryVar['sale_price'] > 0) ? (float)$primaryVar['sale_price'] : 0;

            if ($v_sale > 0 && $v_price > 0 && $v_sale < $v_price) {
                $p['price'] = round($v_price);
                $p['sale_price'] = round($v_sale);
            } elseif ($v_sale > 0) {
                $p['sale_price'] = round($v_sale);
                if (!empty($v_price)) {
                    $p['price'] = round($v_price);
                }
            } elseif ($v_price > 0) {
                $p['price'] = round($v_price);
                $p['sale_price'] = null;
            }

            if (!empty($primaryVar['image']) && (empty($p['main_image']) || $p['main_image'] === 'placeholder.jpg')) {
                $p['main_image'] = $primaryVar['image'];
            }
        } else {
            if (isset($p['price'])) $p['price'] = round((float)$p['price']);
            if (isset($p['sale_price']) && (float)$p['sale_price'] > 0) $p['sale_price'] = round((float)$p['sale_price']);
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
