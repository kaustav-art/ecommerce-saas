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
        $shipping_method = $this->session->userdata('shipping_method') ?: 'standard';

        $user_id = $this->is_logged_in() && !empty($this->current_user['id']) ? (int)$this->current_user['id'] : NULL;
        $wishlist_items = [];
        if ($user_id && $this->wishlist_model) {
            $wishlist_items = $this->wishlist_model->get_by_user($user_id);
        }

        $items = $this->cart_model->get_items();
        $summary = $this->cart_model->get_cart_summary($shipping_method);

        $recently_viewed_ids = $this->session->userdata('recently_viewed') ?: [];
        $recently_viewed_products = [];
        if (!empty($recently_viewed_ids) && is_array($recently_viewed_ids)) {
            $recently_viewed_products = $this->product_model->get_by_ids($recently_viewed_ids);
        }
        if (empty($recently_viewed_products)) {
            $recently_viewed_products = $this->product_model->get_products([], 6);
        }

        $all_products = Static_template_data::get('products');

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
                $this->session->set_flashdata('success', $result['message']);
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

        if (!$this->is_logged_in()) {
            if ($this->input->is_ajax_request() || !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->json_response([
                    'success' => false,
                    'require_login' => true,
                    'message' => 'Please sign in to move items to your wishlist.'
                ]);
                return;
            } else {
                $this->session->set_flashdata('error', 'Please sign in to move items to your wishlist.');
                redirect('cart');
            }
        }

        $user_id = (int) ($this->current_user['id'] ?? 0);
        if ($product_id > 0 && $user_id > 0) {
            if (!$this->wishlist_model->is_wishlisted($user_id, $product_id)) {
                $this->wishlist_model->toggle($user_id, $product_id);
            }
        }

        // Remove from cart
        $result = $this->cart_model->remove_item($cart_key);

        if ($this->input->is_ajax_request() || !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $this->json_response([
                'success'      => true,
                'message'      => 'Item moved to your wishlist.',
                'cart_count'   => $result['cart_count'] ?? 0,
                'cart_summary' => $result['cart_summary'] ?? $this->cart_model->get_cart_summary(),
                'cart_items'   => array_values($this->cart_model->get_items())
            ]);
        } else {
            $this->session->set_flashdata('success', 'Item moved to your wishlist.');
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
