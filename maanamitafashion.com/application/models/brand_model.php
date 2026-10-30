<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class brand_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    private function tid()
    {
        $CI =& get_instance();
        return isset($CI->tenant_id) ? (int) $CI->tenant_id : 1;
    }

    public function get_all($active_only = TRUE)
    {
        $tid = $this->tid();
        $this->db->where('tenant_id', $tid);
        if ($active_only) {
            $this->db->where('status', 'active');
        }
        $brands = $this->db->order_by('name', 'ASC')->get('brands')->result_array();
        foreach ($brands as &$b) {
            $b['product_count'] = $this->db->where('tenant_id', $tid)->where('brand_id', $b['id'])->where('status', 'published')->count_all_results('products');
        }
        return $brands;
    }

    public function get_brands_by_category_ids($category_ids = [])
    {
        $tid = $this->tid();
        $this->db->select('b.id, b.name, b.slug, COUNT(DISTINCT p.id) as product_count')
                 ->from('brands b')
                 ->join('products p', 'p.brand_id = b.id AND p.status = "published" AND p.tenant_id = ' . $tid)
                 ->where('b.tenant_id', $tid)
                 ->where('b.status', 'active');

        if (!empty($category_ids) && is_array($category_ids)) {
            $this->db->where_in('p.category_id', $category_ids);
        }

        return $this->db->group_by('b.id')
                        ->order_by('b.name', 'ASC')
                        ->get()
                        ->result_array();
    }

    public function get_by_slug($slug)
    {
        return $this->db->where('tenant_id', $this->tid())->where('slug', $slug)->where('status', 'active')->get('brands')->row_array();
    }
}
