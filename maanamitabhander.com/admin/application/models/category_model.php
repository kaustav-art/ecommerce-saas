<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class category_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    protected function tid()
    {
        $ci =& get_instance();
        return isset($ci->tenant_id) ? (int) $ci->tenant_id : 1;
    }

    public function get_all()
    {
        $tid = $this->tid();
        $categories = $this->db->where('tenant_id', $tid)->get('categories')->result_array();
        $cat_map = [];
        $children_map = [];
        foreach ($categories as $cat) {
            $cat_map[$cat['id']] = $cat;
            $pid = (int) ($cat['parent_id'] ?? 0);
            $children_map[$pid][] = $cat;
        }

        foreach ($categories as &$cat) {
            $cat['product_count'] = $this->db->where('tenant_id', $tid)->where('category_id', $cat['id'])->count_all_results('products');
            $cat['breadcrumb_path'] = $this->build_path_string($cat['id'], $cat_map);
            $cat['parent_name'] = (!empty($cat['parent_id']) && isset($cat_map[$cat['parent_id']]))
                ? $cat_map[$cat['parent_id']]['name']
                : 'Top Level';
        }
        unset($cat);

        // Update cat_map with enriched fields (breadcrumb_path, product_count, parent_name)
        foreach ($categories as $cat) {
            $cat_map[$cat['id']] = $cat;
        }

        // Arrange alphabetically by main categories, then their subcategories, then sub-subcategories
        // Structure: a, a>1>1, a>1>2, a>2>1, then b, b>1>1, b>1>2
        $ordered = [];
        $visited = [];
        $this->walk_tree_alphabetical(0, $children_map, $cat_map, $ordered, $visited, 0);

        // Append any orphan categories if any exist
        foreach ($categories as $c) {
            if (!isset($visited[$c['id']])) {
                $c['depth'] = 0;
                $ordered[] = $c;
            }
        }

        return $ordered;
    }

    private function walk_tree_alphabetical($parent_id, &$children_map, &$cat_map, &$result, &$visited, $depth = 0)
    {
        if (!isset($children_map[$parent_id])) {
            return;
        }

        $children = $children_map[$parent_id];
        // Sort direct children alphabetically by name (case-insensitive)
        usort($children, function ($a, $b) {
            return strcasecmp(trim($a['name']), trim($b['name']));
        });

        foreach ($children as $child) {
            $id = $child['id'];
            if (isset($visited[$id])) {
                continue;
            }
            $visited[$id] = true;

            $item = $cat_map[$id];
            $item['depth'] = $depth;
            $result[] = $item;

            // Recursively walk children (subcategories and sub-subcategories)
            $this->walk_tree_alphabetical($id, $children_map, $cat_map, $result, $visited, $depth + 1);
        }
    }

    public function get_paginated($offset = 0, $limit = 10, $search = '')
    {
        $tid = $this->tid();
        // Cache map of all categories for instant O(1) breadcrumb resolution
        $all_cats = $this->db->select('id, parent_id, name')->where('tenant_id', $tid)->get('categories')->result_array();
        $cat_map = [];
        foreach ($all_cats as $c) {
            $cat_map[$c['id']] = $c;
        }

        $this->db->from('categories')->where('tenant_id', $tid);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('name', $search);
            $this->db->or_like('slug', $search);
            $this->db->or_like('description', $search);
            $this->db->group_end();
        }

        $total = $this->db->count_all_results('', FALSE);

        $categories = $this->db->order_by('sort_order', 'ASC')
                               ->order_by('id', 'ASC')
                               ->limit($limit, $offset)
                               ->get()
                               ->result_array();

        foreach ($categories as &$cat) {
            $cat['product_count'] = $this->db->where('tenant_id', $tid)->where('category_id', $cat['id'])->count_all_results('products');
            $cat['breadcrumb_path'] = $this->build_path_string($cat['id'], $cat_map);
            $cat['parent_name'] = (!empty($cat['parent_id']) && isset($cat_map[$cat['parent_id']]))
                ? $cat_map[$cat['parent_id']]['name']
                : 'Top Level';
        }

        return [
            'success'    => true,
            'categories' => $categories,
            'total'      => $total,
            'offset'     => $offset,
            'limit'      => $limit,
            'has_more'   => ($offset + count($categories)) < $total
        ];
    }

    public function get_by_id($id)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->get('categories')->row_array();
    }

    public function get_by_slug($slug)
    {
        return $this->db->where('tenant_id', $this->tid())->where('slug', $slug)->get('categories')->row_array();
    }

    public function get_parent_options($exclude_id = NULL)
    {
        $all = $this->db->where('tenant_id', $this->tid())->order_by('sort_order', 'ASC')->order_by('name', 'ASC')->get('categories')->result_array();
        $cat_map = [];
        foreach ($all as $c) {
            $cat_map[$c['id']] = $c;
        }

        $excluded_ids = [];
        if (!empty($exclude_id)) {
            $excluded_ids[] = (int) $exclude_id;
            $descendants = $this->get_descendant_ids((int) $exclude_id);
            $excluded_ids = array_merge($excluded_ids, $descendants);
        }

        $result = [];
        foreach ($all as $c) {
            if (in_array((int)$c['id'], $excluded_ids, true)) {
                continue;
            }
            $c['full_path'] = $this->build_path_string($c['id'], $cat_map);
            $result[] = $c;
        }

        // Sort by full path for clean hierarchical dropdown
        usort($result, function ($a, $b) {
            return strcasecmp($a['full_path'], $b['full_path']);
        });

        return $result;
    }

    public function get_descendant_ids($parent_id)
    {
        $descendants = [];
        $children = $this->db->where('tenant_id', $this->tid())->where('parent_id', (int) $parent_id)->get('categories')->result_array();
        foreach ($children as $child) {
            $descendants[] = (int) $child['id'];
            $sub = $this->get_descendant_ids((int) $child['id']);
            if (!empty($sub)) {
                $descendants = array_merge($descendants, $sub);
            }
        }
        return $descendants;
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

    public function create($data)
    {
        $data['tenant_id'] = $this->tid();
        $this->db->insert('categories', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->update('categories', $data);
    }

    public function delete($id)
    {
        $tid = $this->tid();
        // Re-parent children to 0 or delete
        $this->db->where('tenant_id', $tid)->where('parent_id', (int) $id)->update('categories', ['parent_id' => 0]);
        return $this->db->where('tenant_id', $tid)->where('id', (int) $id)->delete('categories');
    }
}
