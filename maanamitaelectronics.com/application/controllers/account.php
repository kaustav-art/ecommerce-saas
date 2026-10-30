<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Account Controller
 * Operates 100% statically using Static_template_data.
 * Provides a single common Order Details page (`account/order/<any>`) and all after-login dropdown pages.
 */
class account extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
        $this->load->model('order_model');
        $this->load->model('product_model');
    }

    /**
     * Resolve order by order_number from database with normalized items
     */
    protected function _get_order_by_number($order_number)
    {
        $order_number = trim((string) $order_number);
        $tid = (int) $this->tenant_id;

        if ($order_number !== '') {
            $order = $this->db->where('tenant_id', $tid)
                              ->where('order_number', $order_number)
                              ->get('orders')
                              ->row_array();
            if ($order) {
                $oid = (int) $order['id'];
                $raw_items = $this->db->select('oi.*, p.slug AS product_slug, p.price AS reg_price, p.main_image AS prod_main_image')
                                      ->from('order_items oi')
                                      ->join('products p', 'p.id = oi.product_id AND p.tenant_id = oi.tenant_id', 'left')
                                      ->where('oi.order_id', $oid)
                                      ->order_by('oi.id', 'ASC')
                                      ->get()
                                      ->result_array();
                $items = [];
                foreach ($raw_items as $oi) {
                    $img = !empty($oi['product_image']) ? $oi['product_image'] : ($oi['prod_main_image'] ?? '');
                    $img = ltrim(trim((string) $img), '/');
                    if (strpos($img, 'assets/images/') === 0) {
                        $img = substr($img, strlen('assets/images/'));
                    }
                    $oi['id']            = (int) $oi['id'];
                    $oi['order_id']      = (int) $oi['order_id'];
                    $oi['product_id']    = (int) $oi['product_id'];
                    $oi['sku']           = $oi['product_sku'] ?? '';
                    $oi['product_slug']  = !empty($oi['product_slug']) ? $oi['product_slug'] : 'product';
                    $oi['product_image'] = $img;
                    $oi['price']         = (float) $oi['price'];
                    $oi['regular_price'] = !empty($oi['reg_price']) ? (float) $oi['reg_price'] : $oi['price'];
                    $oi['quantity']      = (int) $oi['quantity'];
                    $oi['total']         = (float) $oi['total'];
                    $items[] = $oi;
                }
                $order['id']              = $oid;
                $order['subtotal']        = (float) ($order['subtotal'] ?? $order['total_amount']);
                $order['discount_amount'] = (float) ($order['discount_amount'] ?? 0);
                $order['shipping_fee']    = (float) ($order['shipping_fee'] ?? 0);
                $order['shipping_amount'] = (float) ($order['shipping_fee'] ?? ($order['shipping_amount'] ?? 0));
                $order['tax_amount']      = (float) ($order['tax_amount'] ?? 0);
                $order['total_amount']    = (float) $order['total_amount'];
                $order['items']           = $items;
                $order['returns']         = $this->db->table_exists('order_returns')
                    ? $this->db->where('order_id', $oid)->get('order_returns')->result_array()
                    : [];
                return $order;
            }
        }

        $all_orders = Static_template_data::get('orders') ?: [];
        foreach ($all_orders as $o) {
            if (!empty($o['order_number']) && strcasecmp($o['order_number'], $order_number) === 0) {
                return $o;
            }
        }

        return Static_template_data::get('common_order');
    }

    /**
     * Recalculate product rating and reviews_count after review insert/update
     */
    protected function _recalculate_product_rating($product_id)
    {
        $product_id = (int) $product_id;
        if ($product_id <= 0) {
            return;
        }
        $tid = (int) $this->tenant_id;

        $stats = $this->db->select_avg('rating', 'avg_rating')
                          ->where('tenant_id', $tid)
                          ->where('product_id', $product_id)
                          ->where('status', 'approved')
                          ->get('reviews')
                          ->row_array();

        $count = (int) $this->db->where('tenant_id', $tid)
                                ->where('product_id', $product_id)
                                ->where('status', 'approved')
                                ->count_all_results('reviews');

        $avg = ($count > 0 && isset($stats['avg_rating']) && $stats['avg_rating'] !== null) ? round((float) $stats['avg_rating'], 1) : 0.0;

        $this->db->where('tenant_id', $tid)
                 ->where('id', $product_id)
                 ->update('products', [
                     'rating'        => $avg,
                     'reviews_count' => $count
                 ]);

        Static_template_data::clear_cache();
    }

    /**
     * Resolve active user from session/DB when logged in, or fallback to demo user
     */
    protected function _get_account_user()
    {
        if ($this->is_logged_in() && !empty($this->current_user)) {
            if (!empty($this->current_user['id'])) {
                $db_user = $this->user_model->get_by_id((int) $this->current_user['id']);
                if ($db_user) {
                    return $db_user;
                }
            }
            return $this->current_user;
        }
        return Static_template_data::get('user');
    }

    public function index()
    {
        redirect('account/profile');
    }

    public function profile()
    {
        $user    = $this->_get_account_user();
        $user_id = !empty($this->current_user['id']) ? (int) $this->current_user['id'] : (!empty($user['id']) ? (int) $user['id'] : 0);
        $tid     = (int) $this->tenant_id;

        if ($this->input->method() === 'post') {
            $action = trim((string) $this->input->post('action', TRUE));

            if ($action === 'change_password' && $user_id > 0) {
                $new_pass     = (string) $this->input->post('new_password');
                $confirm_pass = (string) $this->input->post('confirm_password');
                if ($new_pass !== '' && $new_pass === $confirm_pass && strlen($new_pass) >= 6) {
                    $this->user_model->update($user_id, [
                        'password' => password_hash($new_pass, PASSWORD_BCRYPT)
                    ]);
                    $this->session->set_flashdata('success', 'Password updated successfully.');
                } else {
                    $this->session->set_flashdata('error', 'New password and confirmation must match and be at least 6 characters.');
                }
                redirect('account/profile');
                return;
            }

            if ($user_id > 0) {
                $update_data = [];
                $first_name  = $this->input->post('first_name', TRUE);
                $last_name   = $this->input->post('last_name', TRUE);
                $phone       = $this->input->post('phone', TRUE);

                if ($first_name !== NULL && trim((string) $first_name) !== '') {
                    $update_data['first_name'] = trim((string) $first_name);
                }
                if ($last_name !== NULL) {
                    $update_data['last_name'] = trim((string) $last_name);
                }
                if ($phone !== NULL) {
                    $update_data['phone'] = trim((string) $phone);
                }

                // Handle remove profile picture request
                if ($this->input->post('remove_avatar') === '1') {
                    $update_data['avatar'] = 'default-user.png';
                }

                // Handle profile picture upload
                if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    $orig_ext    = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                    if (in_array($orig_ext, $allowed_ext, true)) {
                        $upload_dir = FCPATH . 'assets/images/avatars/';
                        if (!is_dir($upload_dir)) {
                            @mkdir($upload_dir, 0775, true);
                        }
                        $filename = 'user_' . $tid . '_' . $user_id . '_' . time() . '.' . $orig_ext;
                        if (@move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_dir . $filename)) {
                            $update_data['avatar'] = 'avatars/' . $filename;
                        }
                    } else {
                        $this->session->set_flashdata('error', 'Please upload a valid image file (JPG, PNG, WEBP, GIF).');
                        redirect('account/profile');
                        return;
                    }
                }

                if (!empty($update_data)) {
                    $this->user_model->update($user_id, $update_data);
                }

                $fresh_user = $this->user_model->get_by_id($user_id);
                if ($fresh_user) {
                    unset($fresh_user['password']);
                    $this->session->set_userdata('user_data', $fresh_user);
                    $this->session->set_userdata('user_first_name', $fresh_user['first_name'] ?? '');
                    $this->session->set_userdata('user_last_name', $fresh_user['last_name'] ?? '');
                    $this->session->set_userdata('user_phone', $fresh_user['phone'] ?? '');
                    $this->session->set_userdata('user_avatar', $fresh_user['avatar'] ?? '');
                }
                Static_template_data::clear_cache();
                $this->session->set_flashdata('success', 'Profile details updated successfully.');
            } else {
                $this->session->set_flashdata('success', 'Profile details updated successfully.');
            }
            redirect('account/profile');
            return;
        }

        $data = [
            'title'              => 'My Profile - ' . $this->site_name,
            'active_page'        => 'account',
            'active_account_tab' => 'profile',
            'user'               => $user
        ];

        $this->render('account/profile', $data);
    }

    public function orders()
    {
        $user       = $this->_get_account_user();
        $user_id    = !empty($this->current_user['id']) ? (int) $this->current_user['id'] : (!empty($user['id']) ? (int) $user['id'] : 0);
        $all_orders = Static_template_data::get('orders') ?: [];
        $orders     = $all_orders;

        if ($this->is_logged_in() && $user_id > 0) {
            $user_orders = array_values(array_filter($all_orders, function($o) use ($user_id, $user) {
                if (!empty($o['user_id']) && (int) $o['user_id'] === $user_id) {
                    return true;
                }
                if (!empty($user['email']) && !empty($o['customer_email']) && strcasecmp($o['customer_email'], $user['email']) === 0) {
                    return true;
                }
                return false;
            }));
            if (!empty($user_orders)) {
                $orders = $user_orders;
            }
        }

        $data = [
            'title'              => 'My Orders - ' . $this->site_name,
            'active_page'        => 'account',
            'active_account_tab' => 'orders',
            'user'               => $user,
            'orders'             => $orders
        ];

        $this->render('account/orders', $data);
    }

    public function order_detail($order_number = 'ORD-2026-88412')
    {
        $user    = $this->_get_account_user();
        $user_id = !empty($this->current_user['id']) ? (int) $this->current_user['id'] : (!empty($user['id']) ? (int) $user['id'] : 0);
        $order   = $this->_get_order_by_number($order_number);

        $user_reviews = [];
        $tid = (int) $this->tenant_id;
        $this->db->where('tenant_id', $tid);
        if (!empty($order['order_number']) && $user_id > 0) {
            $this->db->group_start()
                     ->where('order_number', $order['order_number'])
                     ->or_where('user_id', $user_id)
                     ->group_end();
        } elseif (!empty($order['order_number'])) {
            $this->db->where('order_number', $order['order_number']);
        } elseif ($user_id > 0) {
            $this->db->where('user_id', $user_id);
        }
        $rev_rows = $this->db->order_by('id', 'DESC')->get('reviews')->result_array();
        foreach ($rev_rows as $rev) {
            $r_pid = (int) ($rev['product_id'] ?? 0);
            if ($r_pid > 0 && !isset($user_reviews[$r_pid])) {
                $user_reviews[$r_pid] = $rev;
            }
        }

        $data = [
            'title'              => 'Order Details #' . $order['order_number'] . ' - ' . $this->site_name,
            'active_page'        => 'account',
            'active_account_tab' => 'orders',
            'user'               => $user,
            'order'              => $order,
            'user_reviews'       => $user_reviews
        ];

        $this->render('account/order_detail', $data);
    }

    public function rate_review($order_number = 'ORD-2026-88412', $product_id = null)
    {
        $user    = $this->_get_account_user();
        $user_id = !empty($this->current_user['id']) ? (int) $this->current_user['id'] : (!empty($user['id']) ? (int) $user['id'] : 0);
        $order   = $this->_get_order_by_number($order_number);
        $items   = $order['items'] ?? [];
        $tid     = (int) $this->tenant_id;

        $selected_item = null;
        $target_pid    = (int) $product_id;
        if ($target_pid > 0 && !empty($items)) {
            foreach ($items as $it) {
                if ((int) ($it['product_id'] ?? 0) === $target_pid) {
                    $selected_item = $it;
                    break;
                }
            }
        }
        if (!$selected_item && !empty($items)) {
            $selected_item = $items[0];
            if ($target_pid <= 0) {
                $target_pid = (int) ($selected_item['product_id'] ?? 0);
            }
        }

        $product = null;
        if ($target_pid > 0) {
            $product = $this->product_model->get_by_id($target_pid);
            if (!$product) {
                $all_products = Static_template_data::get('products') ?: [];
                foreach ($all_products as $p) {
                    if ((int) ($p['id'] ?? 0) === $target_pid) {
                        $product = $p;
                        break;
                    }
                }
            }
        }
        if (!$product) {
            $product = Static_template_data::get('common_product');
        }

        if (!$selected_item) {
            $img = $product['main_image'] ?? '';
            $img = ltrim(trim((string) $img), '/');
            if (strpos($img, 'assets/images/') === 0) {
                $img = substr($img, strlen('assets/images/'));
            }
            $selected_item = [
                'id'            => 0,
                'product_id'    => $target_pid ?: (int) ($product['id'] ?? 0),
                'product_title' => $product['title'] ?? 'Product',
                'product_image' => $img,
                'price'         => (float) ($product['sale_price'] ?? ($product['price'] ?? 0)),
                'quantity'      => 1,
            ];
            $target_pid = (int) $selected_item['product_id'];
        }

        // Check if a review already exists for this order/user and product
        $existing_review = null;
        if ($target_pid > 0) {
            $this->db->where('tenant_id', $tid)->where('product_id', $target_pid);
            if (!empty($order['order_number']) && $user_id > 0) {
                $this->db->group_start()
                         ->where('order_number', $order['order_number'])
                         ->or_where('user_id', $user_id)
                         ->group_end();
            } elseif (!empty($order['order_number'])) {
                $this->db->where('order_number', $order['order_number']);
            } elseif ($user_id > 0) {
                $this->db->where('user_id', $user_id);
            }
            $existing_review = $this->db->order_by('id', 'DESC')->get('reviews')->row_array();
        }

        if ($this->input->method() === 'post') {
            $rating      = max(1, min(5, (int) ($this->input->post('rating') ?: 5)));
            $title       = trim((string) $this->input->post('title', TRUE));
            $review_text = trim((string) $this->input->post('review', TRUE));

            $customer_name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
            if ($customer_name === '') {
                $customer_name = !empty($user['name']) ? trim((string) $user['name']) : (!empty($order['customer_name']) ? trim((string) $order['customer_name']) : 'Verified Customer');
            }
            $customer_email = !empty($user['email']) ? trim((string) $user['email']) : (!empty($order['customer_email']) ? trim((string) $order['customer_email']) : '');

            // Handle optional uploaded review images
            $uploaded_images = [];
            if ($existing_review && !empty($existing_review['images'])) {
                $prev_imgs = json_decode($existing_review['images'], true);
                if (is_array($prev_imgs)) {
                    $uploaded_images = $prev_imgs;
                }
            }

            if (!empty($_FILES['review_images']['name']) && is_array($_FILES['review_images']['name'])) {
                $upload_dir = FCPATH . 'assets/images/reviews/';
                if (!is_dir($upload_dir)) {
                    @mkdir($upload_dir, 0775, true);
                }
                $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $new_files   = [];
                $file_count  = count($_FILES['review_images']['name']);
                for ($i = 0; $i < $file_count && count($new_files) < 5; $i++) {
                    if (empty($_FILES['review_images']['name'][$i]) || $_FILES['review_images']['error'][$i] !== UPLOAD_ERR_OK) {
                        continue;
                    }
                    $tmp_name = $_FILES['review_images']['tmp_name'][$i];
                    $orig_ext = strtolower(pathinfo($_FILES['review_images']['name'][$i], PATHINFO_EXTENSION));
                    if (!in_array($orig_ext, $allowed_ext, true)) {
                        continue;
                    }
                    $filename = 'rev_' . $tid . '_' . $target_pid . '_' . time() . '_' . $i . '.' . $orig_ext;
                    if (@move_uploaded_file($tmp_name, $upload_dir . $filename)) {
                        $new_files[] = 'reviews/' . $filename;
                    }
                }
                if (!empty($new_files)) {
                    $uploaded_images = $new_files;
                }
            }

            $images_json = !empty($uploaded_images) ? json_encode(array_values($uploaded_images)) : null;

            if ($target_pid > 0) {
                $review_payload = [
                    'tenant_id'      => $tid,
                    'product_id'     => $target_pid,
                    'user_id'        => $user_id > 0 ? $user_id : null,
                    'order_number'   => $order['order_number'] ?? $order_number,
                    'customer_name'  => $customer_name,
                    'customer_email' => $customer_email,
                    'rating'         => $rating,
                    'title'          => $title !== '' ? $title : null,
                    'review'         => $review_text,
                    'images'         => $images_json,
                    'status'         => 'approved',
                ];

                if ($existing_review && !empty($existing_review['id'])) {
                    $this->db->where('id', (int) $existing_review['id'])
                             ->where('tenant_id', $tid)
                             ->update('reviews', $review_payload);
                } else {
                    $review_payload['created_at'] = date('Y-m-d H:i:s');
                    $this->db->insert('reviews', $review_payload);
                }

                $this->_recalculate_product_rating($target_pid);
            }

            $this->session->set_flashdata('success', 'Thank you! Your rating and review have been submitted successfully.');
            $redirect_url = 'account/order/' . ($order['order_number'] ?? $order_number);
            if (!empty($selected_item['id'])) {
                $redirect_url .= '?item_id=' . (int) $selected_item['id'];
            }
            redirect($redirect_url);
            return;
        }

        $prefill_rating = (int) ($this->input->get('rating') ?: ($existing_review['rating'] ?? 5));
        $prefill_rating = max(1, min(5, $prefill_rating));

        $data = [
            'title'           => 'Rate & Review - ' . ($selected_item['product_title'] ?? ($product['title'] ?? 'Product')) . ' - ' . $this->site_name,
            'active_page'     => 'account',
            'order'           => $order,
            'item'            => $selected_item,
            'product'         => $product,
            'existing_review' => $existing_review,
            'prefill_rating'  => $prefill_rating,
            'user'            => $user
        ];

        $this->render('account/rate_review', $data);
    }

    public function quick_rate()
    {
        $user         = $this->_get_account_user();
        $user_id      = !empty($this->current_user['id']) ? (int) $this->current_user['id'] : (!empty($user['id']) ? (int) $user['id'] : 0);
        $tid          = (int) $this->tenant_id;
        $order_number = trim((string) $this->input->post('order_number', TRUE));
        $product_id   = (int) $this->input->post('product_id');
        $rating       = max(1, min(5, (int) ($this->input->post('rating') ?: 5)));

        if ($product_id > 0) {
            $this->db->where('tenant_id', $tid)->where('product_id', $product_id);
            if ($order_number !== '' && $user_id > 0) {
                $this->db->group_start()
                         ->where('order_number', $order_number)
                         ->or_where('user_id', $user_id)
                         ->group_end();
            } elseif ($order_number !== '') {
                $this->db->where('order_number', $order_number);
            } elseif ($user_id > 0) {
                $this->db->where('user_id', $user_id);
            }
            $existing = $this->db->order_by('id', 'DESC')->get('reviews')->row_array();

            $customer_name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
            if ($customer_name === '') {
                $customer_name = !empty($user['name']) ? trim((string) $user['name']) : 'Verified Customer';
            }
            $customer_email = !empty($user['email']) ? trim((string) $user['email']) : '';

            if ($existing && !empty($existing['id'])) {
                $this->db->where('id', (int) $existing['id'])
                         ->where('tenant_id', $tid)
                         ->update('reviews', [
                             'rating'       => $rating,
                             'order_number' => $order_number !== '' ? $order_number : ($existing['order_number'] ?? null),
                             'status'       => 'approved'
                         ]);
            } else {
                $labels = [1 => 'Very Bad', 2 => 'Bad', 3 => 'Good', 4 => 'Very Good', 5 => 'Excellent'];
                $this->db->insert('reviews', [
                    'tenant_id'      => $tid,
                    'product_id'     => $product_id,
                    'user_id'        => $user_id > 0 ? $user_id : null,
                    'order_number'   => $order_number !== '' ? $order_number : null,
                    'customer_name'  => $customer_name,
                    'customer_email' => $customer_email,
                    'rating'         => $rating,
                    'title'          => $labels[$rating] ?? 'Rated ' . $rating . ' stars',
                    'review'         => '',
                    'status'         => 'approved',
                    'created_at'     => date('Y-m-d H:i:s')
                ]);
            }

            $this->_recalculate_product_rating($product_id);
        }

        $this->json_response([
            'success' => true,
            'message' => 'Rating saved successfully!',
            'rating'  => $rating
        ]);
    }

    public function cancel_order($order_number = 'ORD-2026-88412')
    {
        $user    = $this->_get_account_user();
        $user_id = !empty($this->current_user['id']) ? (int) $this->current_user['id'] : NULL;
        $result  = $this->order_model->cancel_order($order_number, $user_id);

        if (!empty($result['success'])) {
            Static_template_data::clear_cache();
            $this->session->set_flashdata('success', $result['message']);
        } else {
            $this->session->set_flashdata('error', $result['message'] ?? 'Unable to cancel this order.');
        }
        redirect('account/order/' . $order_number);
    }

    public function invoice($order_number = 'ORD-2026-88412')
    {
        $order = $this->_get_order_by_number($order_number);
        $data = [
            'title'          => 'Invoice #' . $order['order_number'],
            'order'          => $order,
            'store_settings' => $this->store_settings
        ];
        $this->load->view('template1/order/invoice', $data);
    }

    public function returns()
    {
        $user    = $this->_get_account_user();
        $user_id = !empty($this->current_user['id']) ? (int) $this->current_user['id'] : (!empty($user['id']) ? (int) $user['id'] : 0);
        $returns = ($user_id > 0 && $this->db->table_exists('order_returns'))
            ? $this->order_model->get_returns_by_user($user_id)
            : [];

        $data = [
            'title'              => 'Return & Refund Requests - ' . $this->site_name,
            'active_page'        => 'account',
            'active_account_tab' => 'returns',
            'user'               => $user,
            'returns'            => $returns
        ];

        $this->render('account/returns', $data);
    }

    public function request_return($order_number = 'ORD-2026-88412')
    {
        $user    = $this->_get_account_user();
        $user_id = !empty($this->current_user['id']) ? (int) $this->current_user['id'] : (!empty($user['id']) ? (int) $user['id'] : 0);
        $order   = $this->_get_order_by_number($order_number);

        if ($this->input->method() === 'post' && !empty($order['id']) && $this->db->table_exists('order_returns')) {
            $type   = trim((string) ($this->input->post('type', TRUE) ?: 'return'));
            $reason = trim((string) ($this->input->post('reason', TRUE) ?: 'Return requested by customer'));
            $amount = (float) ($this->input->post('amount') ?: ($order['total_amount'] ?? 0));

            $this->order_model->create_return([
                'order_id'   => (int) $order['id'],
                'user_id'    => $user_id > 0 ? $user_id : 0,
                'type'       => $type,
                'reason'     => $reason,
                'amount'     => $amount,
                'status'     => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            Static_template_data::clear_cache();
        }

        $this->session->set_flashdata('success', 'Your return / refund request has been submitted successfully.');
        redirect('account/returns');
    }

    public function address()
    {
        $user    = $this->_get_account_user();
        $user_id = !empty($this->current_user['id']) ? (int) $this->current_user['id'] : (!empty($user['id']) ? (int) $user['id'] : 0);

        if ($this->input->method() === 'post') {
            $addr_id   = (int) $this->input->post('id', TRUE);
            $full_name = trim((string) $this->input->post('name', TRUE));
            if ($full_name !== '') {
                $parts      = preg_split('/\s+/', $full_name, 2);
                $first_name = $parts[0] ?? '';
                $last_name  = $parts[1] ?? '';
            } else {
                $first_name = trim((string) $this->input->post('first_name', TRUE));
                $last_name  = trim((string) $this->input->post('last_name', TRUE));
            }

            $phone     = trim((string) $this->input->post('phone', TRUE));
            $postcode  = trim((string) $this->input->post('postcode', TRUE));
            $locality  = trim((string) $this->input->post('locality', TRUE));
            $address_2 = $locality !== '' ? $locality : trim((string) $this->input->post('address_2', TRUE));
            $landmark  = trim((string) $this->input->post('landmark', TRUE));
            if ($landmark !== '' && stripos($address_2, $landmark) === FALSE) {
                $address_2 = $address_2 !== '' ? ($address_2 . ', Near ' . $landmark) : ('Near ' . $landmark);
            }

            $address_1 = trim((string) $this->input->post('address_1', TRUE));
            $city      = trim((string) $this->input->post('city', TRUE));
            $state     = trim((string) $this->input->post('state', TRUE));
            $company   = strtoupper(trim((string) ($this->input->post('address_type', TRUE) ?: $this->input->post('company', TRUE) ?: 'HOME')));
            if (!in_array($company, ['HOME', 'WORK'], TRUE)) {
                $company = 'HOME';
            }

            if ($user_id > 0) {
                $existing = ($addr_id > 0) ? $this->user_model->get_address_by_id($addr_id) : NULL;
                $existing_list = $this->user_model->get_addresses($user_id);
                $is_default = $existing ? (int) ($existing['is_default'] ?? 0) : (empty($existing_list) ? 1 : 0);
                if ($this->input->post('is_default') !== NULL) {
                    $is_default = $this->input->post('is_default') ? 1 : 0;
                }

                $save_data = [
                    'user_id'    => $existing ? (int) $existing['user_id'] : $user_id,
                    'type'       => $existing['type'] ?? 'shipping',
                    'first_name' => $first_name,
                    'last_name'  => $last_name,
                    'company'    => $company,
                    'address_1'  => $address_1,
                    'address_2'  => $address_2,
                    'city'       => $city,
                    'state'      => $state,
                    'postcode'   => $postcode,
                    'country'    => $existing['country'] ?? 'India',
                    'phone'      => $phone,
                    'is_default' => $is_default,
                ];
                if ($existing) {
                    $save_data['id'] = (int) $existing['id'];
                }

                $saved_id = $this->user_model->save_address($save_data);
                $msg      = $existing ? 'Address updated successfully.' : 'Address added successfully.';

                if ($this->input->is_ajax_request()) {
                    $this->json_response(['status' => 'success', 'success' => true, 'message' => $msg, 'id' => $saved_id]);
                    return;
                }
                $this->session->set_flashdata('success', $msg);
                redirect('account/address');
                return;
            }

            if ($this->input->is_ajax_request()) {
                $this->json_response(['status' => 'success', 'success' => true, 'message' => 'Address saved.', 'id' => 1]);
                return;
            }
            $this->session->set_flashdata('success', 'Address saved.');
            redirect('account/address');
            return;
        }

        $addresses = [];
        if ($user_id > 0) {
            $addresses = $this->user_model->get_addresses($user_id);
        }
        if (empty($addresses) && !$this->is_user_project) {
            $addresses = Static_template_data::get('addresses');
        }

        $data = [
            'title'              => 'Manage Addresses - ' . $this->site_name,
            'active_page'        => 'account',
            'active_account_tab' => 'address',
            'user'               => $user,
            'addresses'          => $addresses
        ];

        $this->render('account/address', $data);
    }

    public function get_address_ajax($id = 1)
    {
        $user    = $this->_get_account_user();
        $user_id = !empty($this->current_user['id']) ? (int) $this->current_user['id'] : (!empty($user['id']) ? (int) $user['id'] : 0);
        $matched = NULL;
        if ($user_id > 0) {
            $matched = $this->user_model->get_address_by_id((int) $id, $user_id);
        }
        if (!$matched) {
            $addresses = Static_template_data::get('addresses');
            $matched   = $addresses[0] ?? NULL;
            foreach ($addresses as $addr) {
                if ((int)$addr['id'] === (int)$id) {
                    $matched = $addr;
                    break;
                }
            }
        }
        $this->json_response(['status' => 'success', 'address' => $matched]);
    }

    public function delete_address($id = 1)
    {
        $user    = $this->_get_account_user();
        $user_id = !empty($this->current_user['id']) ? (int) $this->current_user['id'] : (!empty($user['id']) ? (int) $user['id'] : 0);
        if ($user_id > 0 && (int) $id > 0) {
            $this->user_model->delete_address((int) $id, $user_id);
            $this->session->set_flashdata('success', 'Address removed successfully.');
        } else {
            $this->session->set_flashdata('success', 'Address removed.');
        }
        redirect('account/address');
    }

    public function wishlist()
    {
        if (!$this->is_logged_in() || empty($this->current_user['id'])) {
            $this->session->set_flashdata('error', 'Please login first to view your wishlist.');
            redirect('auth/login?redirect=' . urlencode('wishlist'));
            return;
        }

        $user     = $this->_get_account_user();
        $this->load->model('wishlist_model');
        $wishlist = $this->wishlist_model->get_by_user((int)$this->current_user['id']);

        $data = [
            'title'              => 'My Wishlist - ' . $this->site_name,
            'active_page'        => 'wishlist',
            'active_account_tab' => 'wishlist',
            'user'               => $user,
            'wishlist'           => $wishlist
        ];

        $this->render('account/wishlist', $data);
    }

    public function wishlist_toggle($product_id = null)
    {
        if ($product_id === null) {
            $product_id = $this->input->post('product_id') ?: $this->input->get('product_id');
        }
        $product_id = (int) $product_id;

        if ($product_id <= 0) {
            if ($this->input->is_ajax_request()) {
                $this->json_response(['success' => false, 'status' => 'error', 'message' => 'Invalid product.']);
                return;
            }
            redirect($_SERVER['HTTP_REFERER'] ?? 'wishlist');
            return;
        }

        // Support guest wishlist when not logged in
        if (!$this->is_logged_in() || empty($this->current_user['id'])) {
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

            if (in_array($product_id, $guest_wl, true)) {
                $guest_wl = array_values(array_diff($guest_wl, [$product_id]));
                $status = 'removed';
                $is_wishlisted = false;
                $msg = 'Removed from your wishlist.';
            } else {
                $guest_wl[] = $product_id;
                $status = 'added';
                $is_wishlisted = true;
                $msg = 'Added to your wishlist!';
            }

            $this->session->set_userdata($sess_key, $guest_wl);
            setcookie('guest_wishlist', json_encode($guest_wl), time() + (86400 * 30), '/');
            $wishlist_count = count($guest_wl);

            if ($this->input->is_ajax_request()) {
                $this->json_response([
                    'success'        => true,
                    'status'         => 'success',
                    'action'         => $status,
                    'is_wishlisted'  => $is_wishlisted,
                    'wishlist_count' => $wishlist_count,
                    'product_id'     => $product_id,
                    'message'        => $msg
                ]);
                return;
            }

            $this->session->set_flashdata('success', $msg);
            redirect($_SERVER['HTTP_REFERER'] ?? 'home');
            return;
        }

        $user_id = (int) $this->current_user['id'];
        $this->load->model('wishlist_model');
        $status  = $this->wishlist_model->toggle($user_id, $product_id);

        $wishlist_count = (int) $this->db->where('tenant_id', $this->tenant_id)
                                         ->where('user_id', $user_id)
                                         ->count_all_results('wishlists');

        $is_wishlisted = ($status === 'added');
        $msg = $is_wishlisted ? 'Added to your wishlist!' : 'Removed from your wishlist.';

        if ($this->input->is_ajax_request()) {
            $this->json_response([
                'success'        => true,
                'status'         => 'success',
                'action'         => $status,
                'is_wishlisted'  => $is_wishlisted,
                'wishlist_count' => $wishlist_count,
                'product_id'     => $product_id,
                'message'        => $msg
            ]);
            return;
        }

        $this->session->set_flashdata('success', $msg);
        redirect($_SERVER['HTTP_REFERER'] ?? 'wishlist');
    }

    public function notifications()
    {
        $user          = $this->_get_account_user();
        $notifications = Static_template_data::get('notifications');

        $data = [
            'title'              => 'Notifications - ' . $this->site_name,
            'active_page'        => 'account',
            'active_account_tab' => 'notifications',
            'user'               => $user,
            'notifications'      => $notifications
        ];

        $this->render('account/notifications', $data);
    }
}
