<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class customer_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    protected function tid()
    {
        $ci =& get_instance();
        return isset($ci->tenant_id) ? (int) $ci->tenant_id : 1;
    }

    public function get_all($limit = NULL, $offset = NULL, $filters = [])
    {
        $tid = $this->tid();
        $this->db->select('u.*, cg.name as group_name, cg.discount_percent as group_discount')
                 ->from('users u')
                 ->join('customer_groups cg', 'cg.id = u.customer_group_id', 'left')
                 ->where('u.tenant_id', $tid);

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $this->db->group_start();
            $this->db->like('u.first_name', $search);
            $this->db->or_like('u.last_name', $search);
            $this->db->or_like('u.email', $search);
            $this->db->or_like('u.phone', $search);
            $this->db->group_end();
        }

        if (!empty($filters['status'])) {
            $this->db->where('u.status', $filters['status']);
        }

        if (!empty($filters['group_id'])) {
            $this->db->where('u.customer_group_id', (int) $filters['group_id']);
        }

        $this->db->order_by('u.id', 'DESC');

        if ($limit !== NULL) {
            $this->db->limit($limit, $offset);
        }

        $customers = $this->db->get()->result_array();

        foreach ($customers as &$c) {
            $c['orders_count']      = $this->db->where('tenant_id', $tid)->where('user_id', $c['id'])->count_all_results('orders');
            $c['total_spent']       = (float) $this->db->select_sum('total_amount')->where('tenant_id', $tid)->where('user_id', $c['id'])->where('payment_status', 'paid')->get('orders')->row()->total_amount;
            $c['cart_items_count']  = $this->db->where('tenant_id', $tid)->where('user_id', $c['id'])->count_all_results('cart_items');
            $c['wishlist_count']    = $this->db->where('tenant_id', $tid)->where('user_id', $c['id'])->count_all_results('wishlists');
            $c['reviews_count']     = $this->db->where('tenant_id', $tid)->where('user_id', $c['id'])->count_all_results('reviews');
            $c['addresses_count']   = $this->db->where('tenant_id', $tid)->where('user_id', $c['id'])->count_all_results('user_addresses');
        }

        return $customers;
    }

    public function count_all($filters = [])
    {
        $this->db->from('users u')->where('u.tenant_id', $this->tid());

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $this->db->group_start();
            $this->db->like('u.first_name', $search);
            $this->db->or_like('u.last_name', $search);
            $this->db->or_like('u.email', $search);
            $this->db->or_like('u.phone', $search);
            $this->db->group_end();
        }

        if (!empty($filters['status'])) {
            $this->db->where('u.status', $filters['status']);
        }

        if (!empty($filters['group_id'])) {
            $this->db->where('u.customer_group_id', (int) $filters['group_id']);
        }

        return $this->db->count_all_results();
    }

    public function get_summary_stats()
    {
        $tid = $this->tid();
        $total_customers = $this->db->where('tenant_id', $tid)->count_all_results('users');
        $active_customers = $this->db->where('tenant_id', $tid)->where('status', 'active')->count_all_results('users');
        $banned_customers = $this->db->where('tenant_id', $tid)->where('status', 'banned')->count_all_results('users');
        
        $total_revenue = (float) $this->db->select_sum('total_amount')
                                          ->where('tenant_id', $tid)
                                          ->where('payment_status', 'paid')
                                          ->get('orders')->row()->total_amount;

        $abandoned_carts_count = $this->db->select('COUNT(DISTINCT user_id) as total')
                                          ->from('cart_items')
                                          ->where('tenant_id', $tid)
                                          ->get()->row()->total ?? 0;

        return [
            'total_customers'       => (int) $total_customers,
            'active_customers'      => (int) $active_customers,
            'banned_customers'      => (int) $banned_customers,
            'total_revenue'         => (float) $total_revenue,
            'abandoned_carts_count' => (int) $abandoned_carts_count
        ];
    }

    public function get_customer_groups()
    {
        return $this->db->where('tenant_id', $this->tid())->order_by('name', 'ASC')->get('customer_groups')->result_array();
    }

    public function get_by_id($id)
    {
        $tid = $this->tid();
        $customer = $this->db->select('u.*, cg.name as group_name, cg.discount_percent as group_discount')
                             ->from('users u')
                             ->join('customer_groups cg', 'cg.id = u.customer_group_id', 'left')
                             ->where('u.tenant_id', $tid)
                             ->where('u.id', (int) $id)
                             ->get()->row_array();

        if (!$customer) {
            return NULL;
        }

        // Addresses
        $customer['addresses'] = $this->db->where('tenant_id', $tid)
                                          ->where('user_id', $customer['id'])
                                          ->order_by('is_default', 'DESC')
                                          ->order_by('id', 'DESC')
                                          ->get('user_addresses')
                                          ->result_array();

        // Orders History
        $orders = $this->db->where('tenant_id', $tid)
                           ->where('user_id', $customer['id'])
                           ->order_by('id', 'DESC')
                           ->get('orders')
                           ->result_array();

        foreach ($orders as &$ord) {
            $ord['items_count'] = $this->db->where('order_id', $ord['id'])->count_all_results('order_items');
        }
        $customer['orders'] = $orders;

        // Abandoned Cart Items
        $cart_rows = $this->db->select('ci.id as cart_item_id, ci.user_id, ci.product_id, ci.variant_id, ci.quantity, ci.created_at as added_at, ci.updated_at,
                                        p.title as product_title, p.slug as product_slug, p.main_image, p.price as product_price, p.sale_price as product_sale_price, p.stock_quantity as product_stock,
                                        pv.sku as variant_sku, pv.price as variant_price, pv.sale_price as variant_sale_price')
                              ->from('cart_items ci')
                              ->join('products p', 'p.id = ci.product_id', 'left')
                              ->join('product_variants pv', 'pv.id = ci.variant_id', 'left')
                              ->where('ci.tenant_id', $tid)
                              ->where('ci.user_id', $customer['id'])
                              ->order_by('ci.id', 'DESC')
                              ->get()->result_array();

        $cart_total = 0;
        foreach ($cart_rows as &$item) {
            $unit_price = !empty($item['variant_sale_price']) && (float)$item['variant_sale_price'] > 0
                ? (float)$item['variant_sale_price']
                : (!empty($item['variant_price']) && (float)$item['variant_price'] > 0
                    ? (float)$item['variant_price']
                    : (!empty($item['product_sale_price']) && (float)$item['product_sale_price'] > 0
                        ? (float)$item['product_sale_price']
                        : (float)$item['product_price']));

            $item['effective_price'] = $unit_price;
            $item['line_total']      = $unit_price * (int)$item['quantity'];
            $cart_total             += $item['line_total'];

            // Variant attribute values
            $item['variant_attributes'] = [];
            if (!empty($item['variant_id'])) {
                $attr_rows = $this->db->select('a.name as attr_name, av.value as attr_value, av.color_code')
                                      ->from('product_variant_values pvv')
                                      ->join('attributes a', 'a.id = pvv.attribute_id')
                                      ->join('attribute_values av', 'av.id = pvv.attribute_value_id')
                                      ->where('pvv.variant_id', (int) $item['variant_id'])
                                      ->get()->result_array();
                $item['variant_attributes'] = $attr_rows;
            }
        }
        $customer['abandoned_cart']       = $cart_rows;
        $customer['abandoned_cart_total'] = $cart_total;

        // Wishlist Items
        $wishlist_rows = $this->db->select('w.id as wishlist_id, w.created_at as wishlisted_at,
                                           p.id as product_id, p.title as product_title, p.slug as product_slug, p.main_image, p.price as product_price, p.sale_price as product_sale_price, p.stock_quantity as product_stock, p.status as product_status,
                                           c.name as category_name')
                                  ->from('wishlists w')
                                  ->join('products p', 'p.id = w.product_id', 'left')
                                  ->join('categories c', 'c.id = p.category_id', 'left')
                                  ->where('w.tenant_id', $tid)
                                  ->where('w.user_id', $customer['id'])
                                  ->order_by('w.id', 'DESC')
                                  ->get()->result_array();

        $customer['wishlist_items'] = $wishlist_rows;

        // Customer Reviews & Ratings
        $reviews = $this->db->select('r.*, p.title as product_title, p.slug as product_slug, p.main_image')
                            ->from('reviews r')
                            ->join('products p', 'p.id = r.product_id', 'left')
                            ->where('r.tenant_id', $tid)
                            ->where('r.user_id', $customer['id'])
                            ->order_by('r.id', 'DESC')
                            ->get()->result_array();

        $total_rating = 0;
        foreach ($reviews as &$rev) {
            $rev['images_decoded'] = !empty($rev['images']) ? (json_decode($rev['images'], true) ?: []) : [];
            $total_rating += (int) $rev['rating'];
        }
        $customer['reviews']          = $reviews;
        $customer['reviews_count']    = count($reviews);
        $customer['avg_rating_given'] = !empty($reviews) ? round($total_rating / count($reviews), 1) : 0;

        // Summary Calculations
        $customer['total_orders'] = count($orders);
        $customer['total_spent']  = (float) $this->db->select_sum('total_amount')
                                                     ->where('tenant_id', $tid)
                                                     ->where('user_id', $customer['id'])
                                                     ->where('payment_status', 'paid')
                                                     ->get('orders')->row()->total_amount;

        return $customer;
    }

    public function create($data)
    {
        $data['tenant_id'] = $this->tid();
        $this->db->insert('users', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->update('users', $data);
    }

    public function delete($id)
    {
        $tid = $this->tid();
        // Delete user related addresses, cart, wishlist, or user
        $this->db->where('tenant_id', $tid)->where('user_id', (int) $id)->delete('cart_items');
        $this->db->where('tenant_id', $tid)->where('user_id', (int) $id)->delete('wishlists');
        $this->db->where('tenant_id', $tid)->where('user_id', (int) $id)->delete('user_addresses');
        return $this->db->where('tenant_id', $tid)->where('id', (int) $id)->delete('users');
    }

    public function get_address_by_id($address_id, $user_id = NULL)
    {
        $this->db->where('tenant_id', $this->tid())->where('id', (int) $address_id);
        if ($user_id !== NULL) {
            $this->db->where('user_id', (int) $user_id);
        }
        return $this->db->get('user_addresses')->row_array();
    }

    public function add_address($data)
    {
        $tid = $this->tid();
        $data['tenant_id'] = $tid;
        if (!empty($data['is_default'])) {
            $this->db->where('tenant_id', $tid)->where('user_id', $data['user_id'])->update('user_addresses', ['is_default' => 0]);
        }
        $this->db->insert('user_addresses', $data);
        return $this->db->insert_id();
    }

    public function update_address($id, $data)
    {
        $tid = $this->tid();
        if (!empty($data['is_default']) && !empty($data['user_id'])) {
            $this->db->where('tenant_id', $tid)->where('user_id', $data['user_id'])->where('id !=', (int) $id)->update('user_addresses', ['is_default' => 0]);
        }
        return $this->db->where('tenant_id', $tid)->where('id', (int) $id)->update('user_addresses', $data);
    }

    public function delete_address($address_id, $user_id)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $address_id)->where('user_id', (int) $user_id)->delete('user_addresses');
    }

    public function remove_cart_item($cart_item_id, $user_id)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $cart_item_id)->where('user_id', (int) $user_id)->delete('cart_items');
    }

    public function clear_cart($user_id)
    {
        return $this->db->where('tenant_id', $this->tid())->where('user_id', (int) $user_id)->delete('cart_items');
    }

    public function remove_wishlist_item($wishlist_id, $user_id)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $wishlist_id)->where('user_id', (int) $user_id)->delete('wishlists');
    }

    public function update_review_status($review_id, $status)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $review_id)->update('reviews', ['status' => $status]);
    }

    public function delete_review($review_id)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $review_id)->delete('reviews');
    }
}
