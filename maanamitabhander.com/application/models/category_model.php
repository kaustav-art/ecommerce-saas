<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class category_model extends CI_Model {

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
        $categories = $this->db->order_by('sort_order', 'ASC')
                               ->order_by('name', 'ASC')
                               ->get('categories')
                               ->result_array();

        $cat_map = [];
        foreach ($categories as $c) {
            $cat_map[$c['id']] = $c;
        }

        foreach ($categories as &$c) {
            $descendants = $this->get_descendant_ids($c['id']);
            $all_ids     = array_merge([(int) $c['id']], $descendants);
            $c['product_count'] = $this->db->where('tenant_id', $tid)
                                           ->where_in('category_id', $all_ids)
                                           ->where('status', 'published')
                                           ->count_all_results('products');
            $c['breadcrumb_path'] = $this->build_path_string($c['id'], $cat_map);
        }
        return $categories;
    }

    public function get_root_categories()
    {
        $tid = $this->tid();
        $categories = $this->db->where('tenant_id', $tid)
                               ->where('parent_id', 0)
                               ->where('status', 'active')
                               ->order_by('sort_order', 'ASC')
                               ->order_by('name', 'ASC')
                               ->get('categories')
                               ->result_array();

        foreach ($categories as &$c) {
            $descendants = $this->get_descendant_ids($c['id']);
            $all_ids     = array_merge([(int) $c['id']], $descendants);
            $c['product_count'] = $this->db->where('tenant_id', $tid)
                                           ->where_in('category_id', $all_ids)
                                           ->where('status', 'published')
                                           ->count_all_results('products');
        }
        return $categories;
    }

    public function get_subcategories($parent_id)
    {
        $tid = $this->tid();
        $categories = $this->db->where('tenant_id', $tid)
                               ->where('parent_id', (int) $parent_id)
                               ->where('status', 'active')
                               ->order_by('sort_order', 'ASC')
                               ->order_by('name', 'ASC')
                               ->get('categories')
                               ->result_array();

        foreach ($categories as &$c) {
            $descendants = $this->get_descendant_ids($c['id']);
            $all_ids     = array_merge([(int) $c['id']], $descendants);
            $c['product_count'] = $this->db->where('tenant_id', $tid)
                                           ->where_in('category_id', $all_ids)
                                           ->where('status', 'published')
                                           ->count_all_results('products');
        }
        return $categories;
    }

    public function get_featured($limit = 12)
    {
        $tid = $this->tid();
        $categories = $this->db->where('tenant_id', $tid)
                               ->where('status', 'active')
                               ->where('is_featured', 1)
                               ->where('parent_id', 0)
                               ->order_by('sort_order', 'DESC')
                               ->order_by('id', 'DESC')
                               ->limit($limit)
                               ->get('categories')
                               ->result_array();

        // Fallback to active top-level categories if none are explicitly marked as featured
        if (empty($categories)) {
            $categories = $this->db->where('tenant_id', $tid)
                                   ->where('status', 'active')
                                   ->where('parent_id', 0)
                                   ->order_by('sort_order', 'DESC')
                                   ->order_by('id', 'DESC')
                                   ->limit($limit)
                                   ->get('categories')
                                   ->result_array();
        }

        foreach ($categories as &$c) {
            $descendants = $this->get_descendant_ids($c['id']);
            $all_ids     = array_merge([(int) $c['id']], $descendants);
            $c['product_count'] = $this->db->where('tenant_id', $tid)
                                           ->where_in('category_id', $all_ids)
                                           ->where('status', 'published')
                                           ->count_all_results('products');
        }
        return $categories;
    }

    public function get_by_slug($slug)
    {
        return $this->db->where('tenant_id', $this->tid())
                        ->where('slug', $slug)
                        ->where('status', 'active')
                        ->get('categories')
                        ->row_array();
    }

    public function get_by_id($id)
    {
        return $this->db->where('tenant_id', $this->tid())
                        ->where('id', (int) $id)
                        ->where('status', 'active')
                        ->get('categories')
                        ->row_array();
    }

    public function get_breadcrumbs($category_id)
    {
        $tid = $this->tid();
        $trail = [];
        $curr_id = (int) $category_id;
        $seen = [];

        while ($curr_id > 0 && !isset($seen[$curr_id])) {
            $seen[$curr_id] = true;
            $cat = $this->db->where('tenant_id', $tid)->where('id', $curr_id)->get('categories')->row_array();
            if (!$cat) {
                break;
            }
            array_unshift($trail, $cat);
            $curr_id = (int) ($cat['parent_id'] ?? 0);
        }
        return $trail;
    }

    public function get_descendant_ids($parent_id)
    {
        $tid = $this->tid();
        $descendants = [];
        $children = $this->db->select('id')->where('tenant_id', $tid)->where('parent_id', (int) $parent_id)->get('categories')->result_array();
        foreach ($children as $child) {
            $descendants[] = (int) $child['id'];
            $sub = $this->get_descendant_ids((int) $child['id']);
            if (!empty($sub)) {
                $descendants = array_merge($descendants, $sub);
            }
        }
        return $descendants;
    }

    public function get_all_category_ids_including_descendants($category_id)
    {
        $descendants = $this->get_descendant_ids($category_id);
        return array_merge([(int) $category_id], $descendants);
    }

    public function build_path_string($category_id, array $cat_map)
    {
        $trail = [];
        $curr_id = (int) $category_id;
        $seen = [];
        while ($curr_id > 0 && isset($cat_map[$curr_id]) && !isset($seen[$curr_id])) {
            $seen[$curr_id] = true;
            array_unshift($trail, $cat_map[$curr_id]['name']);
            $curr_id = (int) ($cat_map[$curr_id]['parent_id'] ?? 0);
        }
        return !empty($trail) ? implode(' > ', $trail) : '';
    }

    public function get_tree()
    {
        $all = $this->db->where('tenant_id', $this->tid())
                        ->where('status', 'active')
                        ->order_by('sort_order', 'ASC')
                        ->order_by('name', 'ASC')
                        ->get('categories')
                        ->result_array();

        $by_parent = [];
        foreach ($all as $c) {
            $by_parent[$c['parent_id']][] = $c;
        }

        $build = function($parent_id) use (&$build, &$by_parent) {
            $branch = [];
            if (isset($by_parent[$parent_id])) {
                foreach ($by_parent[$parent_id] as $item) {
                    $item['children'] = $build($item['id']);
                    $branch[] = $item;
                }
            }
            return $branch;
        };

        return $build(0);
    }
}
