<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class inventory_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    protected function tid()
    {
        $ci =& get_instance();
        return isset($ci->tenant_id) ? (int) $ci->tenant_id : 1;
    }

    public function get_stock_items($filters = [], $limit = 20, $offset = 0)
    {
        $this->db->select('p.id as product_id, p.title as product_title, p.sku as product_sku, p.stock_quantity as product_stock, p.stock_status, p.low_stock_threshold, p.main_image, c.name as category_name, b.name as brand_name')
                 ->from('products p')
                 ->join('categories c', 'c.id = p.category_id', 'left')
                 ->join('brands b', 'b.id = p.brand_id', 'left')
                 ->where('p.tenant_id', $this->tid());

        if (!empty($filters['search'])) {
            $this->db->group_start()
                     ->like('p.title', $filters['search'])
                     ->or_like('p.sku', $filters['search'])
                     ->group_end();
        }

        if (!empty($filters['low_stock'])) {
            $this->db->where('p.stock_quantity <= p.low_stock_threshold');
        }

        if (!empty($filters['status'])) {
            $this->db->where('p.stock_status', $filters['status']);
        }

        return $this->db->order_by('p.stock_quantity', 'ASC')
                        ->limit($limit, $offset)
                        ->get()
                        ->result_array();
    }

    public function count_stock_items($filters = [])
    {
        $this->db->from('products p')->where('p.tenant_id', $this->tid());
        if (!empty($filters['search'])) {
            $this->db->group_start()
                     ->like('p.title', $filters['search'])
                     ->or_like('p.sku', $filters['search'])
                     ->group_end();
        }
        if (!empty($filters['low_stock'])) {
            $this->db->where('p.stock_quantity <= p.low_stock_threshold');
        }
        return $this->db->count_all_results();
    }

    public function adjust_stock($product_id, $variant_id, $type, $quantity, $reason, $admin_id)
    {
        $tid = $this->tid();
        $product = $this->db->where('tenant_id', $tid)->where('id', (int) $product_id)->get('products')->row_array();
        if (!$product) return false;

        $qty_change = ($type === 'in') ? (int)$quantity : -(int)$quantity;
        $new_stock = max(0, $product['stock_quantity'] + $qty_change);
        $new_status = ($new_stock > 0) ? 'in_stock' : 'out_of_stock';

        $this->db->where('tenant_id', $tid)->where('id', (int) $product_id)->update('products', [
            'stock_quantity' => $new_stock,
            'stock_status'   => $new_status
        ]);

        if (!empty($variant_id)) {
            $var = $this->db->where('id', (int) $variant_id)->get('product_variants')->row_array();
            if ($var) {
                $new_v_stock = max(0, $var['stock_quantity'] + $qty_change);
                $new_v_status = ($new_v_stock > 0) ? 'in_stock' : 'out_of_stock';
                $this->db->where('id', (int) $variant_id)->update('product_variants', [
                    'stock_quantity' => $new_v_stock,
                    'stock_status'   => $new_v_status
                ]);
            }
        }

        $this->db->insert('stock_adjustments', [
            'tenant_id'  => $tid,
            'product_id' => (int) $product_id,
            'variant_id' => $variant_id ? (int) $variant_id : NULL,
            'type'       => $type,
            'quantity'   => (int) $quantity,
            'reason'     => $reason,
            'admin_id'   => (int) $admin_id
        ]);

        return true;
    }

    public function get_history($limit = 30, $offset = 0)
    {
        return $this->db->select('sa.*, p.title as product_title, p.sku as product_sku, a.name as admin_name')
                        ->from('stock_adjustments sa')
                        ->join('products p', 'p.id = sa.product_id', 'left')
                        ->join('admins a', 'a.id = sa.admin_id', 'left')
                        ->where('sa.tenant_id', $this->tid())
                        ->order_by('sa.id', 'DESC')
                        ->limit($limit, $offset)
                        ->get()
                        ->result_array();
    }
}
