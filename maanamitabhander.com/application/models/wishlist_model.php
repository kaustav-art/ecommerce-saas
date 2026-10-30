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
            $this->product_model->apply_tax_pricing($p);
        }
        return $products;
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
