<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class product_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    protected function tid()
    {
        $ci =& get_instance();
        return isset($ci->tenant_id) ? (int) $ci->tenant_id : 1;
    }

    public function get_all($limit = NULL, $offset = NULL, $category_id = NULL, $search = NULL, $product_type = NULL)
    {
        $this->db->select('p.*, c.name as category_name, b.name as brand_name')
                 ->from('products p')
                 ->join('categories c', 'c.id = p.category_id', 'left')
                 ->join('brands b', 'b.id = p.brand_id', 'left')
                 ->where('p.tenant_id', $this->tid());

        if (!empty($category_id)) {
            $this->db->where('p.category_id', (int) $category_id);
        }
        if (!empty($product_type)) {
            $this->db->where('p.product_type', $product_type);
        }
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('p.title', $search);
            $this->db->or_like('p.sku', $search);
            $this->db->group_end();
        }

        $this->db->order_by('p.id', 'DESC');

        if ($limit !== NULL) {
            $this->db->limit($limit, $offset);
        }

        $products = $this->db->get()->result_array();
        foreach ($products as &$p) {
            $p['variants_count'] = $this->db->where('product_id', $p['id'])->count_all_results('product_variants');
        }
        return $products;
    }

    public function count_all($category_id = NULL, $search = NULL, $product_type = NULL)
    {
        $this->db->where('tenant_id', $this->tid());
        if (!empty($category_id)) {
            $this->db->where('category_id', (int) $category_id);
        }
        if (!empty($product_type)) {
            $this->db->where('product_type', $product_type);
        }
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('title', $search);
            $this->db->or_like('sku', $search);
            $this->db->group_end();
        }
        return $this->db->count_all_results('products');
    }

    public function get_by_id($id)
    {
        $product = $this->db->select('p.*, c.name as category_name, b.name as brand_name')
                            ->from('products p')
                            ->join('categories c', 'c.id = p.category_id', 'left')
                            ->join('brands b', 'b.id = p.brand_id', 'left')
                            ->where('p.tenant_id', $this->tid())
                            ->where('p.id', (int) $id)
                            ->get()
                            ->row_array();

        if ($product) {
            $product['specifications'] = $this->db->where('product_id', $product['id'])
                                                  ->order_by('sort_order', 'ASC')
                                                  ->get('product_specifications')
                                                  ->result_array();
            $product['variants'] = $this->db->where('product_id', $product['id'])
                                            ->get('product_variants')
                                            ->result_array();
        }
        return $product;
    }

    public function create($data)
    {
        $data['tenant_id'] = $this->tid();
        $this->db->insert('products', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->update('products', $data);
    }

    public function delete($id)
    {
        $this->db->where('product_id', (int) $id)->delete('product_variants');
        $this->db->where('product_id', (int) $id)->delete('product_attributes');
        $this->db->where('product_id', (int) $id)->delete('product_specifications');
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->delete('products');
    }

    public function save_specifications($product_id, $specs)
    {
        $this->db->where('product_id', (int) $product_id)->delete('product_specifications');
        if (!empty($specs) && is_array($specs)) {
            foreach ($specs as $idx => $s) {
                if (!empty($s['name']) && !empty($s['value'])) {
                    $this->db->insert('product_specifications', [
                        'product_id' => (int) $product_id,
                        'spec_name'  => trim($s['name']),
                        'spec_value' => trim($s['value']),
                        'sort_order' => $idx
                    ]);
                }
            }
        }
    }

    public function get_low_stock_count($threshold = 10)
    {
        return $this->db->where('tenant_id', $this->tid())->where('stock_quantity <=', $threshold)->count_all_results('products');
    }

    public function get_top_selling($limit = 5)
    {
        return $this->db->select('p.id, p.title, p.sku, p.price, p.sale_price, p.main_image, p.stock_quantity, p.stock_status, c.name as category_name, COALESCE(SUM(oi.quantity), 0) as units_sold, COALESCE(SUM(oi.total), 0) as total_revenue')
                        ->from('products p')
                        ->join('categories c', 'c.id = p.category_id', 'left')
                        ->join('order_items oi', 'oi.product_id = p.id', 'left')
                        ->where('p.tenant_id', $this->tid())
                        ->group_by('p.id')
                        ->order_by('units_sold', 'DESC')
                        ->order_by('p.id', 'ASC')
                        ->limit($limit)
                        ->get()
                        ->result_array();
    }

    public function get_low_stock_items($threshold = 10, $limit = 5)
    {
        return $this->db->select('p.*, c.name as category_name')
                        ->from('products p')
                        ->join('categories c', 'c.id = p.category_id', 'left')
                        ->where('p.tenant_id', $this->tid())
                        ->where('p.stock_quantity <=', (int) $threshold)
                        ->order_by('p.stock_quantity', 'ASC')
                        ->limit($limit)
                        ->get()
                        ->result_array();
    }
}
