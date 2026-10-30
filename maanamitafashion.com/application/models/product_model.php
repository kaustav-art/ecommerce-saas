<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class product_model extends CI_Model {

    private $tax_multiplier = null;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('setting_model');
    }

    private function tid()
    {
        $CI =& get_instance();
        return isset($CI->tenant_id) ? (int) $CI->tenant_id : 1;
    }

    public function refresh_tax_multiplier()
    {
        $this->tax_multiplier = null;
        return $this->get_tax_multiplier();
    }

    public function get_tax_multiplier()
    {
        if ($this->tax_multiplier !== null) {
            return $this->tax_multiplier;
        }
        // Catalog prices entered in the database are customer-facing display prices.
        // Tax computation (inclusive or exclusive) is handled by the cart checkout summary.
        $this->tax_multiplier = 1.0;
        return $this->tax_multiplier;
    }

    public function get_active_flash_sale_info()
    {
        static $cached_fs = null;
        if ($cached_fs !== null) {
            return $cached_fs;
        }

        $tid = $this->tid();
        $now_str = date('Y-m-d H:i:s');
        $fs = $this->db->where('tenant_id', $tid)
                       ->where('status !=', 'terminated')
                       ->where('start_time <=', $now_str)
                       ->where('end_time >=', $now_str)
                       ->order_by('start_time', 'ASC')
                       ->get('flash_sales')
                       ->row_array();

        if ($fs && !empty($fs['id'])) {
            $items = $this->db->select('product_id')->where('flash_sale_id', (int)$fs['id'])->get('flash_sale_products')->result_array();
            $fs['product_ids'] = array_map('intval', array_column($items, 'product_id'));
        } else {
            $fs = false;
        }

        $cached_fs = $fs;
        return $cached_fs;
    }

    public function apply_flash_sale_pricing(&$prod)
    {
        if (empty($prod) || !is_array($prod) || empty($prod['id'])) {
            return;
        }
        $fs = $this->get_active_flash_sale_info();
        if (!$fs || empty($fs['product_ids']) || !in_array((int)$prod['id'], $fs['product_ids'], true)) {
            return;
        }

        $discount_pct = (float)($fs['discount_percent'] ?? 0);
        if ($discount_pct <= 0) {
            return;
        }

        $reg = (isset($prod['base_price']) && is_numeric($prod['base_price'])) ? (float)$prod['base_price'] : ((!empty($prod['price']) && (float)$prod['price'] > 0) ? (float)$prod['price'] : 0);
        $selling = (isset($prod['base_sale_price']) && is_numeric($prod['base_sale_price']) && (float)$prod['base_sale_price'] > 0) ? (float)$prod['base_sale_price'] : ((!empty($prod['sale_price']) && (float)$prod['sale_price'] > 0) ? (float)$prod['sale_price'] : $reg);
        $final_price = round($selling * (1 - ($discount_pct / 100)), 2);
        $total_pct = ($reg > 0 && $final_price < $reg) ? round((($reg - $final_price) / $reg) * 100) : 0;

        $prod['is_flash_sale'] = 1;
        $prod['flash_sale_discount_percent'] = $discount_pct;
        $prod['original_selling_price'] = $selling;
        $prod['regular_price'] = $reg;
        $prod['price'] = $reg;
        $prod['sale_price'] = $final_price;
        $prod['base_price'] = $reg;
        $prod['base_sale_price'] = $final_price;
        $prod['total_discount_percent'] = $total_pct;
    }

    public function apply_variant_flash_sale_pricing(&$v)
    {
        if (empty($v) || !is_array($v) || empty($v['product_id'])) {
            return;
        }
        $fs = $this->get_active_flash_sale_info();
        if (!$fs || empty($fs['product_ids']) || !in_array((int)$v['product_id'], $fs['product_ids'], true)) {
            return;
        }

        $discount_pct = (float)($fs['discount_percent'] ?? 0);
        if ($discount_pct <= 0) {
            return;
        }

        $v_reg = (isset($v['base_price']) && is_numeric($v['base_price'])) ? (float)$v['base_price'] : ((!empty($v['price']) && (float)$v['price'] > 0) ? (float)$v['price'] : 0);
        $v_selling = (isset($v['base_sale_price']) && is_numeric($v['base_sale_price']) && (float)$v['base_sale_price'] > 0) ? (float)$v['base_sale_price'] : ((!empty($v['sale_price']) && (float)$v['sale_price'] > 0) ? (float)$v['sale_price'] : $v_reg);
        $v_final = round($v_selling * (1 - ($discount_pct / 100)), 2);
        $total_pct = ($v_reg > 0 && $v_final < $v_reg) ? round((($v_reg - $v_final) / $v_reg) * 100) : 0;

        $v['is_flash_sale'] = 1;
        $v['flash_sale_discount_percent'] = $discount_pct;
        $v['original_selling_price'] = $v_selling;
        $v['regular_price'] = $v_reg;
        $v['price'] = $v_reg;
        $v['sale_price'] = $v_final;
        $v['base_price'] = $v_reg;
        $v['base_sale_price'] = $v_final;
        $v['total_discount_percent'] = $total_pct;
    }

    public function apply_tax_pricing(&$prod)
    {
        if (empty($prod) || !is_array($prod)) {
            return;
        }
        if (!isset($prod['name']) && isset($prod['title'])) {
            $prod['name'] = $prod['title'];
        }
        if (!isset($prod['primary_image']) && isset($prod['main_image'])) {
            $prod['primary_image'] = $prod['main_image'];
        }
        if (!isset($prod['image']) && isset($prod['main_image'])) {
            $prod['image'] = $prod['main_image'];
        }
        $rev_cnt = isset($prod['reviews_count']) ? (int)$prod['reviews_count'] : 0;
        if ($rev_cnt <= 0) {
            $prod['reviews_count'] = 0;
            $prod['rating']        = 0.0;
        } else {
            $prod['reviews_count'] = $rev_cnt;
            $prod['rating']        = isset($prod['rating']) ? round((float)$prod['rating'], 1) : 0.0;
        }
        if (isset($prod['base_price'])) {
            $this->apply_flash_sale_pricing($prod);
            return;
        }
        $mult = $this->get_tax_multiplier();
        if (isset($prod['price']) && is_numeric($prod['price'])) {
            $prod['base_price'] = (float) $prod['price'];
            if ($mult > 1.0) {
                $prod['price'] = round($prod['base_price'] * $mult, 2);
            }
            if (!isset($prod['regular_price'])) {
                $prod['regular_price'] = (float) $prod['price'];
            }
        }
        if (isset($prod['sale_price']) && is_numeric($prod['sale_price']) && (float)$prod['sale_price'] > 0) {
            $prod['base_sale_price'] = (float) $prod['sale_price'];
            if ($mult > 1.0) {
                $prod['sale_price'] = round($prod['base_sale_price'] * $mult, 2);
            }
        }
        $this->apply_flash_sale_pricing($prod);
    }

    public function apply_variant_tax_pricing(&$v)
    {
        if (empty($v) || !is_array($v)) {
            return;
        }
        if (isset($v['base_price'])) {
            $this->apply_variant_flash_sale_pricing($v);
            return;
        }
        $mult = $this->get_tax_multiplier();
        if (isset($v['price']) && is_numeric($v['price'])) {
            $v['base_price'] = (float) $v['price'];
            if ($mult > 1.0) {
                $v['price'] = round($v['base_price'] * $mult, 2);
            }
        }
        if (isset($v['sale_price']) && is_numeric($v['sale_price']) && (float)$v['sale_price'] > 0) {
            $v['base_sale_price'] = (float) $v['sale_price'];
            if ($mult > 1.0) {
                $v['sale_price'] = round($v['base_sale_price'] * $mult, 2);
            }
        }
        $this->apply_variant_flash_sale_pricing($v);
    }

    public function get_products($filters = [], $limit = 12, $offset = 0)
    {
        $tid = $this->tid();
        $this->db->select('p.*, c.name as category_name, c.slug as category_slug, b.name as brand_name')
                 ->from('products p')
                 ->join('categories c', 'c.id = p.category_id', 'left')
                 ->join('brands b', 'b.id = p.brand_id', 'left')
                 ->where('p.tenant_id', $tid)
                 ->where('p.status', 'published');

        if (!empty($filters['category_ids']) && is_array($filters['category_ids'])) {
            $this->db->where_in('p.category_id', $filters['category_ids']);
        } elseif (!empty($filters['category_id'])) {
            $this->db->where('p.category_id', (int) $filters['category_id']);
        } elseif (!empty($filters['category_slug'])) {
            $this->db->where('c.slug', $filters['category_slug']);
        }
        if (!empty($filters['brand_id'])) {
            $this->db->where('p.brand_id', (int) $filters['brand_id']);
        }
        if (!empty($filters['brand_slugs']) && is_array($filters['brand_slugs'])) {
            $this->db->where_in('b.slug', $filters['brand_slugs']);
        } elseif (!empty($filters['brand_slug'])) {
            $this->db->where('b.slug', $filters['brand_slug']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('p.title', $filters['search']);
            $this->db->or_like('p.description', $filters['search']);
            $this->db->or_like('p.sku', $filters['search']);
            $this->db->group_end();
        }
        $tax_mult = $this->get_tax_multiplier();
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $min_val = ($tax_mult > 1.0) ? ((float) $filters['min_price'] / $tax_mult) : (float) $filters['min_price'];
            $this->db->where('COALESCE(p.sale_price, p.price) >=', $min_val);
        }
        if (isset($filters['max_price']) && is_numeric($filters['max_price']) && $filters['max_price'] > 0) {
            $max_val = ($tax_mult > 1.0) ? ((float) $filters['max_price'] / $tax_mult) : (float) $filters['max_price'];
            $this->db->where('COALESCE(p.sale_price, p.price) <=', $max_val);
        }
        if (!empty($filters['on_sale'])) {
            $this->db->where('p.sale_price IS NOT NULL', NULL, FALSE);
            $this->db->where('p.sale_price >', 0);
        }
        if (!empty($filters['rating']) && is_numeric($filters['rating'])) {
            $this->db->where('p.rating >=', (float) $filters['rating']);
        }
        if (!empty($filters['in_stock'])) {
            $this->db->where('p.stock_quantity >', 0);
        }
        if (!empty($filters['attr_value_ids']) && is_array($filters['attr_value_ids'])) {
            $val_ids = array_map('intval', $filters['attr_value_ids']);
            if (!empty($val_ids)) {
                $this->db->where("p.id IN (SELECT DISTINCT pv.product_id FROM product_variants pv JOIN product_variant_values pvv ON pvv.variant_id = pv.id WHERE pvv.attribute_value_id IN (" . implode(',', $val_ids) . "))", NULL, FALSE);
            }
        }
        if (isset($filters['is_new'])) {
            $this->db->where('p.is_new', (int) $filters['is_new']);
        }
        if (isset($filters['is_featured'])) {
            $this->db->where('p.is_featured', (int) $filters['is_featured']);
        }
        if (isset($filters['is_trending'])) {
            $this->db->where('p.is_trending', (int) $filters['is_trending']);
        }

        // Sorting
        $sort = isset($filters['sort']) ? $filters['sort'] : 'a-z';
        switch ($sort) {
            case 'price_low':
            case 'price-low-high':
                $this->db->order_by('COALESCE(p.sale_price, p.price)', 'ASC');
                break;
            case 'price_high':
            case 'price-high-low':
                $this->db->order_by('COALESCE(p.sale_price, p.price)', 'DESC');
                break;
            case 'z-a':
                $this->db->order_by('p.title', 'DESC');
                break;
            case 'a-z':
            default:
                $this->db->order_by('p.title', 'ASC');
                break;
        }

        if ($limit !== NULL) {
            $this->db->limit($limit, $offset);
        }

        $products = $this->db->get()->result_array();
        foreach ($products as &$prod) {
            $prod['gallery_images_decoded'] = json_decode($prod['gallery_images'], true) ?: [];
            $this->apply_tax_pricing($prod);
        }
        return $products;
    }

    public function count_products($filters = [])
    {
        $tid = $this->tid();
        $this->db->from('products p')
                 ->join('categories c', 'c.id = p.category_id', 'left')
                 ->join('brands b', 'b.id = p.brand_id', 'left')
                 ->where('p.tenant_id', $tid)
                 ->where('p.status', 'published');

        if (!empty($filters['category_ids']) && is_array($filters['category_ids'])) {
            $this->db->where_in('p.category_id', $filters['category_ids']);
        } elseif (!empty($filters['category_id'])) {
            $this->db->where('p.category_id', (int) $filters['category_id']);
        } elseif (!empty($filters['category_slug'])) {
            $this->db->where('c.slug', $filters['category_slug']);
        }
        if (!empty($filters['brand_id'])) {
            $this->db->where('p.brand_id', (int) $filters['brand_id']);
        }
        if (!empty($filters['brand_slugs']) && is_array($filters['brand_slugs'])) {
            $this->db->where_in('b.slug', $filters['brand_slugs']);
        } elseif (!empty($filters['brand_slug'])) {
            $this->db->where('b.slug', $filters['brand_slug']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('p.title', $filters['search']);
            $this->db->or_like('p.sku', $filters['search']);
            $this->db->group_end();
        }
        $tax_mult = $this->get_tax_multiplier();
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $min_val = ($tax_mult > 1.0) ? ((float) $filters['min_price'] / $tax_mult) : (float) $filters['min_price'];
            $this->db->where('COALESCE(p.sale_price, p.price) >=', $min_val);
        }
        if (isset($filters['max_price']) && is_numeric($filters['max_price']) && $filters['max_price'] > 0) {
            $max_val = ($tax_mult > 1.0) ? ((float) $filters['max_price'] / $tax_mult) : (float) $filters['max_price'];
            $this->db->where('COALESCE(p.sale_price, p.price) <=', $max_val);
        }
        if (!empty($filters['on_sale'])) {
            $this->db->where('p.sale_price IS NOT NULL', NULL, FALSE);
            $this->db->where('p.sale_price >', 0);
        }
        if (!empty($filters['rating']) && is_numeric($filters['rating'])) {
            $this->db->where('p.rating >=', (float) $filters['rating']);
        }
        if (!empty($filters['in_stock'])) {
            $this->db->where('p.stock_quantity >', 0);
        }
        if (!empty($filters['attr_value_ids']) && is_array($filters['attr_value_ids'])) {
            $val_ids = array_map('intval', $filters['attr_value_ids']);
            if (!empty($val_ids)) {
                $this->db->where("p.id IN (SELECT DISTINCT pv.product_id FROM product_variants pv JOIN product_variant_values pvv ON pvv.variant_id = pv.id WHERE pvv.attribute_value_id IN (" . implode(',', $val_ids) . "))", NULL, FALSE);
            }
        }
        if (isset($filters['is_new'])) {
            $this->db->where('p.is_new', (int) $filters['is_new']);
        }
        if (isset($filters['is_featured'])) {
            $this->db->where('p.is_featured', (int) $filters['is_featured']);
        }
        if (isset($filters['is_trending'])) {
            $this->db->where('p.is_trending', (int) $filters['is_trending']);
        }

        return $this->db->count_all_results();
    }

    public function get_by_slug($slug)
    {
        $tid = $this->tid();
        $product = $this->db->select('p.*, c.name as category_name, c.slug as category_slug, b.name as brand_name, b.slug as brand_slug')
                            ->from('products p')
                            ->join('categories c', 'c.id = p.category_id', 'left')
                            ->join('brands b', 'b.id = p.brand_id', 'left')
                            ->where('p.tenant_id', $tid)
                            ->where('p.slug', $slug)
                            ->where('p.status', 'published')
                            ->get()
                            ->row_array();

        if ($product) {
            $product['gallery_images_decoded'] = json_decode($product['gallery_images'], true) ?: [];
            $product['highlights_decoded']     = !empty($product['highlights']) ? (json_decode($product['highlights'], true) ?: []) : [];
            $product['reviews'] = $this->get_product_reviews($product['id']);
            $product['variants'] = $this->get_variants($product['id']);
            $product['attributes'] = $this->get_product_attributes_and_values($product['id']);
            $product['specifications'] = $this->get_specifications($product['id']);
            $this->apply_tax_pricing($product);
        }
        return $product;
    }

    public function get_by_id($id)
    {
        $tid = $this->tid();
        $product = $this->db->select('p.*, c.name as category_name, c.slug as category_slug, b.name as brand_name')
                            ->from('products p')
                            ->join('categories c', 'c.id = p.category_id', 'left')
                            ->join('brands b', 'b.id = p.brand_id', 'left')
                            ->where('p.tenant_id', $tid)
                            ->where('p.id', (int) $id)
                            ->where('p.status', 'published')
                            ->get()
                            ->row_array();

        if ($product) {
            $product['gallery_images_decoded'] = json_decode($product['gallery_images'], true) ?: [];
            $product['highlights_decoded']     = !empty($product['highlights']) ? (json_decode($product['highlights'], true) ?: []) : [];
            $product['variants'] = $this->get_variants($product['id']);
            $product['attributes'] = $this->get_product_attributes_and_values($product['id']);
            $product['specifications'] = $this->get_specifications($product['id']);
            $this->apply_tax_pricing($product);
        }
        return $product;
    }

    public function get_featured($limit = 8)
    {
        return $this->get_products(['is_featured' => 1], $limit);
    }

    public function get_trending($limit = 8)
    {
        $tid = $this->tid();
        $this->db->select('p.*, c.name as category_name, c.slug as category_slug, b.name as brand_name')
                 ->from('products p')
                 ->join('categories c', 'c.id = p.category_id', 'left')
                 ->join('brands b', 'b.id = p.brand_id', 'left')
                 ->where('p.tenant_id', $tid)
                 ->where('p.status', 'published')
                 ->where('p.is_trending', 1)
                 ->order_by('p.id', 'DESC')
                 ->limit($limit);
        $products = $this->db->get()->result_array();
        foreach ($products as &$prod) {
            $this->apply_tax_pricing($prod);
        }
        return $products;
    }

    public function get_related($category_id, $exclude_id, $limit = 8)
    {
        $tid = $this->tid();
        $products = $this->db->select('p.*, c.name as category_name, b.name as brand_name')
                        ->from('products p')
                        ->join('categories c', 'c.id = p.category_id', 'left')
                        ->join('brands b', 'b.id = p.brand_id', 'left')
                        ->where('p.tenant_id', $tid)
                        ->where('p.category_id', (int) $category_id)
                        ->where('p.id !=', (int) $exclude_id)
                        ->where('p.status', 'published')
                        ->limit($limit)
                        ->get()
                        ->result_array();
        foreach ($products as &$prod) {
            $prod['gallery_images_decoded'] = !empty($prod['gallery_images']) ? (json_decode($prod['gallery_images'], true) ?: []) : [];
            $this->apply_tax_pricing($prod);
        }
        return $products;
    }

    public function get_product_reviews($product_id)
    {
        $tid = $this->tid();
        return $this->db->select('r.*, u.avatar AS user_avatar')
                        ->from('reviews r')
                        ->join('users u', 'u.id = r.user_id AND u.tenant_id = r.tenant_id', 'left')
                        ->where('r.tenant_id', $tid)
                        ->where('r.product_id', (int) $product_id)
                        ->where('r.status', 'approved')
                        ->order_by('r.id', 'DESC')
                        ->get()
                        ->result_array();
    }

    public function add_review($data)
    {
        $tid = $this->tid();
        $data['tenant_id'] = $tid;
        $this->db->insert('reviews', $data);
        $review_id = $this->db->insert_id();

        // Recalculate rating and reviews_count
        $stats = $this->db->select_avg('rating', 'avg_rating')
                          ->select_max('id', 'count')
                          ->where('tenant_id', $tid)
                          ->where('product_id', $data['product_id'])
                          ->where('status', 'approved')
                          ->get('reviews')
                          ->row_array();

        $count = $this->db->where('tenant_id', $tid)->where('product_id', $data['product_id'])->where('status', 'approved')->count_all_results('reviews');

        $this->db->where('tenant_id', $tid)->where('id', $data['product_id'])->update('products', [
            'rating'        => ($count > 0 && !empty($stats['avg_rating'])) ? round((float)$stats['avg_rating'], 1) : 0.0,
            'reviews_count' => $count
        ]);

        return $review_id;
    }

    public function get_variants($product_id)
    {
        $variants = $this->db->where('tenant_id', $this->tid())
                             ->where('product_id', (int) $product_id)
                             ->order_by('id', 'ASC')
                             ->get('product_variants')
                             ->result_array();

        foreach ($variants as &$v) {
            $this->apply_variant_tax_pricing($v);
            $v['values'] = $this->db->select('pvv.*, a.name as attribute_name, a.slug as attribute_slug, a.type as attribute_type, av.value as attribute_value, av.color_code')
                                    ->from('product_variant_values pvv')
                                    ->join('attributes a', 'a.id = pvv.attribute_id')
                                    ->join('attribute_values av', 'av.id = pvv.attribute_value_id')
                                    ->where('pvv.variant_id', $v['id'])
                                    ->get()
                                    ->result_array();

            $attr_map = [];
            foreach ($v['values'] as $val) {
                $attr_map[$val['attribute_id']] = (int) $val['attribute_value_id'];
            }
            $v['attr_map'] = $attr_map;
        }
        return $variants;
    }

    public function get_product_attributes_and_values($product_id)
    {
        $attrs = $this->db->select('a.id, a.name, a.slug, a.type')
                          ->from('product_attributes pa')
                          ->join('attributes a', 'a.id = pa.attribute_id')
                          ->where('pa.product_id', (int) $product_id)
                          ->order_by('a.id', 'ASC')
                          ->get()
                          ->result_array();

        if (empty($attrs)) {
            $attrs = $this->db->select('DISTINCT a.id, a.name, a.slug, a.type', FALSE)
                              ->from('product_variant_values pvv')
                              ->join('product_variants pv', 'pv.id = pvv.variant_id')
                              ->join('attributes a', 'a.id = pvv.attribute_id')
                              ->where('pv.product_id', (int) $product_id)
                              ->order_by('a.id', 'ASC')
                              ->get()
                              ->result_array();
        }

        $result = [];
        foreach ($attrs as $a) {
            $values = $this->db->select('DISTINCT av.id, av.value, av.color_code, av.sort_order', FALSE)
                               ->from('product_variant_values pvv')
                               ->join('product_variants pv', 'pv.id = pvv.variant_id')
                               ->join('attribute_values av', 'av.id = pvv.attribute_value_id')
                               ->where('pv.product_id', (int) $product_id)
                               ->where('pvv.attribute_id', $a['id'])
                               ->order_by('av.sort_order', 'ASC')
                               ->order_by('av.id', 'ASC')
                               ->get()
                               ->result_array();

            if (!empty($values)) {
                $a['values'] = $values;
                $result[$a['slug']] = $a;
            }
        }
        return $result;
    }

    public function get_specifications($product_id)
    {
        return $this->db->where('product_id', (int) $product_id)
                        ->order_by('sort_order', 'ASC')
                        ->get('product_specifications')
                        ->result_array();
    }

    public function get_variant_by_id($variant_id)
    {
        $v = $this->db->where('tenant_id', $this->tid())->where('id', (int) $variant_id)->get('product_variants')->row_array();
        if ($v) {
            $this->apply_variant_tax_pricing($v);
            $v['values'] = $this->db->select('pvv.*, a.name as attribute_name, a.slug as attribute_slug, av.value as attribute_value, av.color_code')
                                    ->from('product_variant_values pvv')
                                    ->join('attributes a', 'a.id = pvv.attribute_id')
                                    ->join('attribute_values av', 'av.id = pvv.attribute_value_id')
                                    ->where('pvv.variant_id', $v['id'])
                                    ->get()
                                    ->result_array();
        }
        return $v;
    }

    public function get_by_ids($ids)
    {
        if (empty($ids) || !is_array($ids)) {
            return [];
        }
        $clean_ids = [];
        foreach ($ids as $id) {
            $int_id = (int) $id;
            if ($int_id > 0) $clean_ids[] = $int_id;
        }
        if (empty($clean_ids)) return [];

        $products = $this->db->select('p.*, c.name as category_name, c.slug as category_slug, b.name as brand_name')
                             ->from('products p')
                             ->join('categories c', 'c.id = p.category_id', 'left')
                             ->join('brands b', 'b.id = p.brand_id', 'left')
                             ->where('p.tenant_id', $this->tid())
                             ->where_in('p.id', $clean_ids)
                             ->where('p.status', 'published')
                             ->get()
                             ->result_array();

        foreach ($products as &$p) {
            $p['gallery_images_decoded'] = json_decode($p['gallery_images'], true) ?: [];
            $p['specifications']         = $this->get_specifications($p['id']);
            $this->apply_tax_pricing($p);
        }
        return $products;
    }

    public function search_autocomplete($query, $limit = 8)
    {
        if (empty($query)) return [];
        $items = $this->db->select('p.id, p.title, p.slug, p.sku, p.price, p.sale_price, p.main_image, c.name as category_name')
                        ->from('products p')
                        ->join('categories c', 'c.id = p.category_id', 'left')
                        ->where('p.tenant_id', $this->tid())
                        ->where('p.status', 'published')
                        ->group_start()
                            ->like('p.title', $query)
                            ->or_like('p.sku', $query)
                        ->group_end()
                        ->limit($limit)
                        ->get()
                        ->result_array();
        foreach ($items as &$item) {
            $this->apply_tax_pricing($item);
        }
        return $items;
    }

    public function get_frequently_bought_together($product_id, $category_id, $limit = 2)
    {
        $products = $this->db->select('p.*, c.name as category_name, b.name as brand_name')
                        ->from('products p')
                        ->join('categories c', 'c.id = p.category_id', 'left')
                        ->join('brands b', 'b.id = p.brand_id', 'left')
                        ->where('p.tenant_id', $this->tid())
                        ->where('p.category_id', (int) $category_id)
                        ->where('p.id !=', (int) $product_id)
                        ->where('p.status', 'published')
                        ->where('p.stock_quantity >', 0)
                        ->order_by('p.rating', 'DESC')
                        ->limit($limit)
                        ->get()
                        ->result_array();
        foreach ($products as &$p) {
            $this->apply_tax_pricing($p);
        }
        return $products;
    }
}
