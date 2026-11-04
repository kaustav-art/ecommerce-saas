<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class cart extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('cart_model');
        $this->load->model('product_model');
        $this->load->model('wishlist_model');
    }

    public function index()
    {
        if (!$this->input->get('login')) {
            $this->session->unset_userdata('checkout_buy_now_item');
        }
        $shipping_method = $this->session->userdata('shipping_method') ?: 'standard';

        $user_id = $this->is_logged_in() && !empty($this->current_user['id']) ? (int)$this->current_user['id'] : NULL;
        $wishlist_items = [];
        if ($user_id && $this->wishlist_model) {
            $wishlist_items = $this->wishlist_model->get_by_user($user_id);
        } elseif ($this->wishlist_model) {
            $guest_wl_ids = $this->get_user_wishlist_ids();
            if (!empty($guest_wl_ids)) {
                $wishlist_items = $this->wishlist_model->get_by_ids($guest_wl_ids);
            }
        }

        $items = $this->cart_model->get_items();
        $summary = $this->cart_model->get_cart_summary($shipping_method);

        $all_products = Static_template_data::get('products');
        $prod_map = [];
        if (!empty($all_products) && is_array($all_products)) {
            foreach ($all_products as $ap) {
                if (!empty($ap['id'])) {
                    $prod_map[(int)$ap['id']] = $ap;
                }
            }
        }

        $recently_viewed_ids = $this->session->userdata('recently_viewed') ?: [];
        $recently_viewed_products = [];
        if (!empty($recently_viewed_ids) && is_array($recently_viewed_ids)) {
            foreach ($recently_viewed_ids as $rvid) {
                $rvid_int = (int)$rvid;
                if (isset($prod_map[$rvid_int])) {
                    $recently_viewed_products[] = $prod_map[$rvid_int];
                }
            }
            if (empty($recently_viewed_products)) {
                $recently_viewed_products = $this->product_model->get_by_ids($recently_viewed_ids);
            }
        }
        if (empty($recently_viewed_products)) {
            $recently_viewed_products = !empty($all_products) ? array_slice($all_products, 0, 5) : $this->product_model->get_products([], 5);
        }

        $data = [
            'title'                    => 'Shopping Cart - ' . $this->site_name,
            'active_page'              => 'cart',
            'cart_items'               => $items,
            'saved_items'              => $this->cart_model->get_saved_items(),
            'cart_summary'             => $summary,
            'cart_recommendations'     => !empty($all_products) ? array_slice($all_products, 0, 8) : $this->product_model->get_products([], 8),
            'recently_viewed_products' => $recently_viewed_products,
            'wishlist_items'           => $wishlist_items
        ];

        $this->render('cart/index', $data);
    }

    public function add()
    {
        $product_id = (int) $this->input->post('product_id');
        $quantity   = max(1, (int) $this->input->post('quantity'));
        $variant_id = $this->input->post('variant_id') ? (int) $this->input->post('variant_id') : NULL;

        $result = $this->cart_model->add_item($product_id, $quantity, $variant_id);
        if (!empty($result['success'])) {
            $result['cart_items'] = array_values($this->cart_model->get_items());
        }

        $is_ajax = $this->input->is_ajax_request()
            || !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

        if ($is_ajax) {
            $this->json_response($result);
        } else {
            if (!empty($result['success'])) {
                if (empty($result['message']) || stripos($result['message'], 'Item added to your shopping cart') === false) {
                    $this->session->set_flashdata('success', $result['message']);
                }
            } else {
                $this->session->set_flashdata('error', $result['message'] ?? 'Could not add product to cart.');
            }
            redirect('cart');
        }
    }

    public function data()
    {
        $summary = $this->cart_model->get_cart_summary();
        $this->json_response([
            'success'      => true,
            'cart_count'   => $summary['item_count'],
            'cart_summary' => $summary,
            'cart_items'   => array_values($this->cart_model->get_items())
        ]);
    }

    public function add_bundle()
    {
        $product_ids = $this->input->post('product_ids');
        if (empty($product_ids) || !is_array($product_ids)) {
            $this->session->set_flashdata('error', 'No bundle products selected.');
            redirect($_SERVER['HTTP_REFERER'] ?? 'shop');
        }

        $added_count = 0;
        foreach ($product_ids as $pid) {
            $int_pid = (int) $pid;
            if ($int_pid > 0) {
                $res = $this->cart_model->add_item($int_pid, 1);
                if (!empty($res['success'])) {
                    $added_count++;
                }
            }
        }

        if ($added_count > 0) {
            $this->session->set_flashdata('success', "Added {$added_count} bundle items to your shopping cart!");
        } else {
            $this->session->set_flashdata('error', 'Could not add selected items.');
        }

        redirect('cart');
    }

    public function update()
    {
        $cart_key = $this->input->post('cart_key', TRUE) ?: (string) $this->input->post('product_id');
        $quantity = (int) $this->input->post('quantity');

        $result = $this->cart_model->update_item($cart_key, $quantity);
        $items = $this->cart_model->get_items();
        $bn_key = $this->input->post('buy_now_key', TRUE) ?: ($this->input->get('buy_now', TRUE) ?: $this->session->userdata('checkout_buy_now_item'));
        if ($bn_key && isset($items[$bn_key])) {
            $result['cart_summary'] = $this->cart_model->get_cart_summary('standard', [$bn_key => $items[$bn_key]]);
        }
        $result['cart_items'] = array_values($items);
        $result['item_total'] = isset($items[$cart_key]) ? (float) $items[$cart_key]['total'] : 0.00;
        $result['item_price'] = isset($items[$cart_key]) ? (float) $items[$cart_key]['price'] : 0.00;
        $result['item_regular_price'] = isset($items[$cart_key]) ? (float) $items[$cart_key]['regular_price'] : 0.00;
        $result['item_quantity'] = isset($items[$cart_key]) ? (int) $items[$cart_key]['quantity'] : 0;

        $is_ajax = $this->input->is_ajax_request()
            || !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

        if ($is_ajax) {
            $this->json_response($result);
        } else {
            redirect('cart');
        }
    }

    public function remove($cart_key = '')
    {
        $cart_key = urldecode($cart_key ?: (string) $this->input->post('cart_key'));
        if ($this->session->userdata('checkout_buy_now_item') === $cart_key) {
            $this->session->unset_userdata('checkout_buy_now_item');
        }
        $result = $this->cart_model->remove_item($cart_key);

        $is_ajax = $this->input->is_ajax_request()
            || !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

        if ($is_ajax) {
            $this->json_response([
                'success'      => true,
                'message'      => 'Item removed from shopping cart.',
                'cart_count'   => $result['cart_count'] ?? 0,
                'cart_summary' => $result['cart_summary'] ?? $this->cart_model->get_cart_summary(),
                'cart_items'   => array_values($this->cart_model->get_items())
            ]);
        } else {
            $this->session->set_flashdata('success', 'Item removed from shopping cart.');
            redirect('cart');
        }
    }

    public function move_to_wishlist()
    {
        $cart_key = urldecode($this->input->post('cart_key', TRUE) ?: (string) $this->input->get('cart_key'));
        $product_id = (int) $this->input->post('product_id') ?: (int) $this->input->get('product_id');

        if (!$product_id && $cart_key) {
            $items = $this->cart_model->get_items();
            if (isset($items[$cart_key])) {
                $product_id = (int) $items[$cart_key]['id'];
            }
        }

        if ($product_id <= 0) {
            $this->session->set_flashdata('error', 'Product not found.');
            redirect('cart');
            return;
        }

        $wishlist_count = 0;
        if ($this->is_logged_in() && !empty($this->current_user['id'])) {
            $user_id = (int) $this->current_user['id'];
            if (!$this->wishlist_model->is_wishlisted($user_id, $product_id)) {
                $this->wishlist_model->toggle($user_id, $product_id);
            }
            $wishlist_count = (int) $this->db->where('tenant_id', $this->tenant_id)
                                             ->where('user_id', $user_id)
                                             ->count_all_results('wishlists');
        } else {
            // Guest wishlist
            $sess_key = 'guest_wishlist_t_' . $this->tenant_id;
            $guest_wl = $this->session->userdata($sess_key);
            if (!is_array($guest_wl)) {
                $guest_wl = [];
                if (isset($_COOKIE['guest_wishlist'])) {
                    $c_arr = json_decode($_COOKIE['guest_wishlist'], true);
                    if (is_array($c_arr)) $guest_wl = $c_arr;
                }
            }
            $guest_wl = array_values(array_map('intval', array_filter($guest_wl)));
            if (!in_array($product_id, $guest_wl, true)) {
                $guest_wl[] = $product_id;
            }
            $this->session->set_userdata($sess_key, $guest_wl);
            setcookie('guest_wishlist', json_encode($guest_wl), time() + (86400 * 30), '/');
            $wishlist_count = count($guest_wl);
        }

        // Remove from cart
        $result = $this->cart_model->remove_item($cart_key);

        // Flash message removed as requested

        if ($this->input->is_ajax_request() || !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $this->json_response([
                'success'        => true,
                'message'        => 'Item moved to your wishlist.',
                'wishlist_count' => $wishlist_count,
                'cart_count'     => $result['cart_count'] ?? 0,
                'cart_summary'   => $result['cart_summary'] ?? $this->cart_model->get_cart_summary(),
                'cart_items'     => array_values($this->cart_model->get_items())
            ]);
        } else {
            redirect('cart');
        }
    }

    public function save_for_later($cart_key = '')
    {
        $cart_key = urldecode($cart_key ?: (string)$this->input->post('cart_key'));
        $success = $this->cart_model->save_for_later($cart_key);

        if ($success) {
            $this->session->set_flashdata('success', 'Item moved to Saved for Later.');
        } else {
            $this->session->set_flashdata('error', 'Item could not be saved.');
        }
        redirect('cart');
    }

    public function move_to_cart($saved_id = '')
    {
        $success = $this->cart_model->move_to_cart($saved_id);

        if ($success) {
            $this->session->set_flashdata('success', 'Item moved back to your cart.');
        } else {
            $this->session->set_flashdata('error', 'Item could not be moved back to cart (check stock availability).');
        }
        redirect('cart');
    }

    public function remove_saved($saved_id = '')
    {
        $this->cart_model->remove_saved_item($saved_id);
        $this->session->set_flashdata('success', 'Saved item removed.');
        redirect('cart');
    }

    public function clear()
    {
        $this->cart_model->clear_cart();
        redirect('cart');
    }

    public function set_shipping_method()
    {
        $method = $this->input->post('shipping_method', TRUE);
        if (in_array($method, ['standard', 'express'])) {
            $this->session->set_userdata('shipping_method', $method);
        }

        if ($this->input->is_ajax_request() || !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $summary = $this->cart_model->get_cart_summary($method);
            $this->json_response(['success' => true, 'cart_summary' => $summary]);
        } else {
            redirect($_SERVER['HTTP_REFERER'] ?? 'cart');
        }
    }

    public function apply_coupon()
    {
        $code = $this->input->post('coupon_code', TRUE);
        $res  = $this->cart_model->apply_coupon($code);

        if ($res['success']) {
            $this->session->set_flashdata('success', $res['message']);
        } else {
            $this->session->set_flashdata('error', $res['message']);
        }
        redirect('cart');
    }

    public function remove_coupon()
    {
        $this->cart_model->remove_coupon();
        $this->session->set_flashdata('success', 'Coupon removed.');
        redirect('cart');
    }
}
