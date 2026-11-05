<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class checkout extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('cart_model');
        $this->load->model('order_model');
        $this->load->model('user_model');
        $this->load->model('setting_model');
    }

    public function index()
    {
        $buy_now_param = $this->input->get('buy_now', TRUE);
        if ($buy_now_param !== NULL) {
            if ($buy_now_param === '0' || $buy_now_param === 'all' || $buy_now_param === '') {
                $this->session->unset_userdata('checkout_buy_now_item');
            } else {
                $this->session->set_userdata('checkout_buy_now_item', $buy_now_param);
            }
        }
        $buy_now_item = $this->session->userdata('checkout_buy_now_item');

        $all_items = $this->cart_model->get_items();
        if (empty($all_items)) {
            redirect('cart');
        }

        $items = $all_items;
        $is_buy_now = false;
        $matched_buy_now_key = null;

        if (!empty($buy_now_item)) {
            if (isset($all_items[$buy_now_item])) {
                $items = [$buy_now_item => $all_items[$buy_now_item]];
                $is_buy_now = true;
                $matched_buy_now_key = $buy_now_item;
            } else {
                foreach ($all_items as $ck => $ci) {
                    if ((string)$ck === (string)$buy_now_item || (string)($ci['cart_key'] ?? '') === (string)$buy_now_item || (string)($ci['id'] ?? '') === (string)$buy_now_item) {
                        $items = [$ck => $ci];
                        $is_buy_now = true;
                        $matched_buy_now_key = $ck;
                        break;
                    }
                }
                if (!$is_buy_now) {
                    $this->session->unset_userdata('checkout_buy_now_item');
                    $items = $all_items;
                }
            }
        }

        if (!$this->is_logged_in() || empty($this->current_user['id'])) {
            $this->session->set_flashdata('error', 'Please sign in to proceed to checkout.');
            redirect('cart?login=1');
        }

        $shipping_method = $this->session->userdata('shipping_method') ?: 'standard';
        $summary = $this->cart_model->get_cart_summary($shipping_method, $items);

        $gateways = $this->setting_model->get_active_gateways();
        if (empty($gateways) && !$this->is_user_project) {
            $gateways = Static_template_data::get('gateways');
        }
        if (empty($gateways)) {
            $gateways = [
                [
                    'gateway_code' => 'cod',
                    'gateway_name' => 'Cash on Delivery (COD)',
                    'is_active'    => 1,
                    'environment'  => 'live',
                    'credentials'  => ['instructions' => 'Pay with cash upon delivery.']
                ]
            ];
        }

        $addresses = [];
        $default_address = NULL;
        if ($this->is_logged_in() && !empty($this->current_user['id'])) {
            $addresses = $this->user_model->get_addresses($this->current_user['id']);
            $selected_id = $this->session->userdata('selected_address_id');
            if ($selected_id) {
                $default_address = $this->user_model->get_address_by_id($selected_id, $this->current_user['id']);
            }
            if (!$default_address) {
                $default_address = $this->user_model->get_default_address($this->current_user['id'], 'shipping');
            }
        }
        if (!$default_address && !$this->is_user_project) {
            $demo_addresses = Static_template_data::get('addresses');
            $default_address = !empty($demo_addresses[0]) ? $demo_addresses[0] : NULL;
        }

        $data = [
            'title'           => 'Checkout - ' . $this->site_name,
            'active_page'     => 'checkout',
            'cart_items'      => $items,
            'all_cart_count'  => count($all_items),
            'is_buy_now'      => $is_buy_now,
            'buy_now_key'     => $matched_buy_now_key,
            'cart_summary'    => $summary,
            'gateways'        => $gateways,
            'default_address' => $default_address,
            'shipping_method' => $shipping_method,
            'mrp_total'       => $summary['mrp_total'] ?? 0,
            'discount_total'  => $summary['mrp_discount'] ?? 0,
            'total_savings'   => $summary['total_savings'] ?? 0
        ];

        $this->render('checkout/index', $data);
    }

    

    public function payment()
    {
        $buy_now_param = $this->input->get('buy_now', TRUE);
        if ($buy_now_param !== NULL) {
            if ($buy_now_param === '0' || $buy_now_param === 'all' || $buy_now_param === '') {
                $this->session->unset_userdata('checkout_buy_now_item');
            } else {
                $this->session->set_userdata('checkout_buy_now_item', $buy_now_param);
            }
        }
        $buy_now_item = $this->session->userdata('checkout_buy_now_item');

        $all_items = $this->cart_model->get_items();
        if (empty($all_items)) {
            redirect('cart');
        }

        $items = $all_items;
        $is_buy_now = false;
        $matched_buy_now_key = null;

        if (!empty($buy_now_item)) {
            if (isset($all_items[$buy_now_item])) {
                $items = [$buy_now_item => $all_items[$buy_now_item]];
                $is_buy_now = true;
                $matched_buy_now_key = $buy_now_item;
            } else {
                foreach ($all_items as $ck => $ci) {
                    if ((string)$ck === (string)$buy_now_item || (string)($ci['cart_key'] ?? '') === (string)$buy_now_item || (string)($ci['id'] ?? '') === (string)$buy_now_item) {
                        $items = [$ck => $ci];
                        $is_buy_now = true;
                        $matched_buy_now_key = $ck;
                        break;
                    }
                }
                if (!$is_buy_now) {
                    $this->session->unset_userdata('checkout_buy_now_item');
                    $items = $all_items;
                }
            }
        }

        if (!$this->is_logged_in() || empty($this->current_user['id'])) {
            $this->session->set_flashdata('error', 'Please sign in to proceed to checkout.');
            redirect('cart?login=1');
        }

        $shipping_method = $this->session->userdata('shipping_method') ?: 'standard';
        $summary = $this->cart_model->get_cart_summary($shipping_method, $items);

        $gateways = $this->setting_model->get_active_gateways();
        if (empty($gateways) && !$this->is_user_project) {
            $gateways = Static_template_data::get('gateways');
        }
        if (empty($gateways)) {
            $gateways = [
                [
                    'gateway_code' => 'cod',
                    'gateway_name' => 'Cash on Delivery (COD)',
                    'is_active'    => 1,
                    'environment'  => 'live',
                    'credentials'  => ['instructions' => 'Pay with cash upon delivery.']
                ]
            ];
        }

        $default_address = NULL;
        if ($this->is_logged_in() && !empty($this->current_user['id'])) {
            $selected_id = $this->session->userdata('selected_address_id');
            if ($selected_id) {
                $default_address = $this->user_model->get_address_by_id($selected_id, $this->current_user['id']);
            }
            if (!$default_address) {
                $default_address = $this->user_model->get_default_address($this->current_user['id'], 'shipping');
            }
        }
        if (!$default_address && !$this->is_user_project) {
            $demo_addresses = Static_template_data::get('addresses');
            $default_address = !empty($demo_addresses[0]) ? $demo_addresses[0] : NULL;
        }

        $cod_check = $this->cart_model->check_cod_eligibility($items);
        $cod_eligible = $cod_check['eligible'] ?? TRUE;
        $non_cod_items = $cod_check['non_cod_items'] ?? [];

        $default_gateway = $this->setting_model->get('default_payment_gateway', 'cod');

        $data = [
            'title'           => 'Payment - ' . $this->site_name,
            'active_page'     => 'checkout',
            'cart_items'      => $items,
            'all_cart_count'  => count($all_items),
            'is_buy_now'      => $is_buy_now,
            'buy_now_key'     => $matched_buy_now_key,
            'cart_summary'    => $summary,
            'gateways'        => $gateways,
            'default_gateway' => $default_gateway,
            'cod_eligible'    => $cod_eligible,
            'non_cod_items'   => $non_cod_items,
            'default_address' => $default_address,
            'shipping_method' => $shipping_method,
            'mrp_total'       => $summary['mrp_total'] ?? 0,
            'discount_total'  => $summary['mrp_discount'] ?? 0,
            'total_savings'   => $summary['total_savings'] ?? 0
        ];

        $this->render('checkout/payment', $data);
    }

    

    public function select_address()
    {
        $address_id = (int) $this->input->post('address_id', TRUE);
        if ($this->is_logged_in() && !empty($this->current_user['id'])) {
            $addr = $this->user_model->get_address_by_id($address_id, $this->current_user['id']);
            if ($addr) {
                $this->session->set_userdata('selected_address_id', $address_id);
                return $this->json_response([
                    'success' => true,
                    'address' => $addr,
                    'message' => 'Delivery address selected.'
                ]);
            }
        }
        $addresses = Static_template_data::get('addresses');
        $addr = $addresses[0] ?? NULL;
        foreach ($addresses as $a) {
            if ((int)$a['id'] === $address_id) {
                $addr = $a;
                break;
            }
        }
        return $this->json_response([
            'success' => true,
            'address' => $addr,
            'message' => 'Delivery address selected.'
        ]);
    }

    public function get_addresses()
    {
        if ($this->is_logged_in() && !empty($this->current_user['id'])) {
            $addresses = $this->user_model->get_addresses($this->current_user['id']);
            $selected_id = $this->session->userdata('selected_address_id');
            if (!$selected_id && !empty($addresses)) {
                $selected_id = $addresses[0]['id'];
            }
            return $this->json_response([
                'success'      => true,
                'addresses'    => $addresses,
                'selected_id'  => $selected_id,
                'is_logged_in' => true
            ]);
        }
        $addresses = Static_template_data::get('addresses');
        return $this->json_response([
            'success'      => true,
            'addresses'    => $addresses,
            'selected_id'  => $addresses[0]['id'] ?? 1,
            'is_logged_in' => false
        ]);
    }

    public function save_address()
    {
        if ($this->is_logged_in() && !empty($this->current_user['id'])) {
            $addr_data = [
                'type'       => 'shipping',
                'user_id'    => $this->current_user['id'],
                'first_name' => $this->input->post('first_name', TRUE),
                'last_name'  => $this->input->post('last_name', TRUE) ?: '',
                'company'    => $this->input->post('company', TRUE) ?: 'HOME',
                'phone'      => $this->input->post('phone', TRUE),
                'address_1'  => $this->input->post('address_1', TRUE),
                'address_2'  => $this->input->post('address_2', TRUE) ?: '',
                'city'       => $this->input->post('city', TRUE),
                'state'      => $this->input->post('state', TRUE),
                'postcode'   => $this->input->post('postcode', TRUE),
                'country'    => $this->input->post('country', TRUE) ?: 'India',
                'is_default' => $this->input->post('is_default') ? 1 : 0
            ];
            $addr_id = $this->user_model->save_address($addr_data);
            $this->session->set_userdata('selected_address_id', $addr_id);
            $addr_data['id'] = $addr_id;
            return $this->json_response([
                'success' => true,
                'address' => $addr_data,
                'message' => 'Delivery address saved successfully!'
            ]);
        }
        $addresses = Static_template_data::get('addresses');
        $addr      = $addresses[0] ?? [];
        return $this->json_response([
            'success' => true,
            'address' => $addr,
            'message' => 'Delivery address saved!'
        ]);
    }

    public function create_order_ajax()
    {
        $all_items = $this->cart_model->get_items();
        if (empty($all_items)) {
            return $this->json_response(['success' => false, 'message' => 'Your shopping bag is empty.'], 400);
        }

        $buy_now_item = $this->input->post('buy_now_item', TRUE) ?: $this->session->userdata('checkout_buy_now_item');
        $items = $all_items;
        $is_buy_now = false;
        $matched_buy_now_key = null;

        if (!empty($buy_now_item)) {
            if (isset($all_items[$buy_now_item])) {
                $items = [$buy_now_item => $all_items[$buy_now_item]];
                $is_buy_now = true;
                $matched_buy_now_key = $buy_now_item;
            } else {
                foreach ($all_items as $ck => $ci) {
                    if ((string)$ck === (string)$buy_now_item || (string)($ci['cart_key'] ?? '') === (string)$buy_now_item || (string)($ci['id'] ?? '') === (string)$buy_now_item) {
                        $items = [$ck => $ci];
                        $is_buy_now = true;
                        $matched_buy_now_key = $ck;
                        break;
                    }
                }
            }
        }

        // Validate Max Purchase Quantity (Per Order)
        foreach ($items as $c_item) {
            $prod_id = (int)($c_item['id'] ?? $c_item['product_id'] ?? 0);
            $qty     = (int)($c_item['quantity'] ?? 1);
            if ($prod_id > 0) {
                $p_row = $this->db->select('title, max_purchase_quantity')
                                  ->where('tenant_id', $this->tenant_id)
                                  ->where('id', $prod_id)
                                  ->get('products')
                                  ->row_array();
                if ($p_row && isset($p_row['max_purchase_quantity']) && (int)$p_row['max_purchase_quantity'] > 0) {
                    $max_limit = (int)$p_row['max_purchase_quantity'];
                    if ($qty > $max_limit) {
                        return $this->json_response([
                            'success' => false,
                            'message' => 'The quantity for "' . html_escape($p_row['title']) . '" exceeds the maximum purchase limit of ' . $max_limit . ' per order. Please update your bag before placing your order.'
                        ], 400);
                    }
                }
            }
        }

        if (!$this->is_logged_in() || empty($this->current_user['id'])) {
            return $this->json_response([
                'success'       => false,
                'require_login' => true,
                'message'       => 'Please sign in or register to place your order.'
            ], 401);
        }

        $payment_method = strtolower(trim((string)$this->input->post('payment_method', TRUE) ?: 'cod'));

        $this->load->model('setting_model');
        $this->load->model('payment_model');

        // Validate payment gateway configuration before creating any order
        if ($payment_method === 'razorpay') {
            $rzp_gw = $this->setting_model->get_gateway('razorpay');
            $rzp_creds = $rzp_gw['credentials'] ?? [];
            $rzp_key_id = trim($rzp_creds['key_id'] ?? '');
            $rzp_key_sec = trim($rzp_creds['key_secret'] ?? '');

            if (empty($rzp_gw) || empty($rzp_gw['is_active']) || empty($rzp_key_id) || empty($rzp_key_sec)) {
                return $this->json_response([
                    'success' => false,
                    'message' => 'Razorpay test keys are not configured. Please enter your Razorpay Key ID and Key Secret in Admin Settings, or select Cash on Delivery.'
                ], 400);
            }
        } elseif ($payment_method === 'stripe') {
            $stripe_gw = $this->setting_model->get_gateway('stripe');
            $stripe_creds = $stripe_gw['credentials'] ?? [];
            if (empty($stripe_gw) || empty($stripe_gw['is_active']) || empty($stripe_creds['secret_key'])) {
                return $this->json_response([
                    'success' => false,
                    'message' => 'Stripe payment gateway is not configured with valid API keys. Please choose another payment method.'
                ], 400);
            }
        } elseif ($payment_method === 'payu') {
            $payu_gw = $this->setting_model->get_gateway('payu');
            $payu_creds = $payu_gw['credentials'] ?? [];
            if (empty($payu_gw) || empty($payu_gw['is_active']) || empty($payu_creds['merchant_key']) || empty($payu_creds['merchant_salt'])) {
                return $this->json_response([
                    'success' => false,
                    'message' => 'PayU payment gateway is not configured. Please choose another payment method.'
                ], 400);
            }
        } elseif ($payment_method === 'cod') {
            $cod_check = $this->cart_model->check_cod_eligibility($items);
            if (!$cod_check['eligible']) {
                $non_cod = implode(', ', $cod_check['non_cod_items'] ?? []);
                return $this->json_response([
                    'success' => false,
                    'message' => "Cash on Delivery is not available for: {$non_cod}. Please select an online payment method."
                ], 400);
            }
        }

        $shipping_method = $this->input->post('shipping_method', TRUE) ?: ($this->session->userdata('shipping_method') ?: 'standard');
        $summary = $this->cart_model->get_cart_summary($shipping_method, $items);

        $active_addr = NULL;
        $sel_id = $this->session->userdata('selected_address_id');
        if ($sel_id) {
            $active_addr = $this->user_model->get_address_by_id($sel_id, $this->current_user['id']);
        }
        if (!$active_addr) {
            $active_addr = $this->user_model->get_default_address($this->current_user['id'], 'shipping');
        }

        $first_name = $active_addr ? $active_addr['first_name'] : ($this->current_user['first_name'] ?? 'Customer');
        $last_name  = $active_addr ? ($active_addr['last_name'] ?? '') : ($this->current_user['last_name'] ?? '');
        $phone      = $active_addr ? ($active_addr['phone'] ?? '') : ($this->current_user['phone'] ?? '');
        $email      = $this->current_user['email'] ?? 'customer@example.com';
        $full_name  = trim($first_name . ' ' . $last_name);
        $addr_str   = $active_addr ? implode(', ', array_filter([$active_addr['address_1'] ?? '', $active_addr['city'] ?? '', $active_addr['state'] ?? '', $active_addr['postcode'] ?? ''])) : 'Customer Address';

        $order_data = [
            'user_id'          => $this->current_user['id'],
            'customer_name'    => $full_name ?: 'Customer',
            'customer_email'   => $email,
            'customer_phone'   => $phone,
            'address_type'     => $active_addr['company'] ?? 'HOME',
            'shipping_address' => $addr_str,
            'billing_address'  => $addr_str,
            'subtotal'         => $summary['subtotal'],
            'discount_amount'  => $summary['discount'],
            'shipping_fee'     => $summary['shipping'],
            'tax_amount'       => $summary['tax'],
            'total_amount'     => $summary['total'],
            'currency'         => $this->store_settings['currency_code'] ?? 'INR',
            'payment_method'   => $payment_method,
            'payment_status'   => 'pending',
            'order_status'     => 'pending',
            'notes'            => $this->input->post('order_notes', TRUE) ?: ''
        ];

        // 1. RAZORPAY GATEWAY (Do NOT create order in database before payment is verified)
        if ($payment_method === 'razorpay') {
            $temp_order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            $temp_order = $order_data;
            $temp_order['order_number'] = $temp_order_number;
            $temp_order['items'] = $items;

            $razorpay_data = $this->payment_model->init_razorpay($temp_order);
            if (empty($razorpay_data['success'])) {
                return $this->json_response([
                    'success' => false,
                    'message' => $razorpay_data['message'] ?? 'Razorpay payment initialization failed. Please check your API keys.'
                ], 400);
            }

            // Save pending checkout intent in DB and Session (no order or stock deducted yet)
            $pending_record = [
                'tenant_id'         => (int)$this->tenant_id,
                'temp_order_number' => $temp_order_number,
                'gateway'           => 'razorpay',
                'gateway_order_id'  => $razorpay_data['razorpay_order_id'] ?? NULL,
                'user_id'           => !empty($this->current_user['id']) ? (int)$this->current_user['id'] : NULL,
                'order_data'        => json_encode($order_data),
                'items_data'        => json_encode($items),
                'is_buy_now'        => $is_buy_now ? 1 : 0,
                'buy_now_key'       => $matched_buy_now_key,
                'status'            => 'pending'
            ];

            if ($this->db->table_exists('pending_checkouts')) {
                $this->db->where('tenant_id', $this->tenant_id)->where('temp_order_number', $temp_order_number)->delete('pending_checkouts');
                $this->db->insert('pending_checkouts', $pending_record);
            }
            $this->session->set_userdata('pending_checkout_' . $temp_order_number, $pending_record);

            $razorpay_data['order_number'] = $temp_order_number;

            return $this->json_response([
                'success'      => true,
                'gateway'      => 'razorpay',
                'order_number' => $temp_order_number,
                'order'        => $temp_order,
                'razorpay'     => $razorpay_data
            ]);
        }

        // 2. STRIPE GATEWAY
        if ($payment_method === 'stripe') {
            $temp_order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            $temp_order = $order_data;
            $temp_order['order_number'] = $temp_order_number;
            $temp_order['items'] = $items;

            $stripe_data = $this->payment_model->init_stripe($temp_order);
            if (empty($stripe_data['success'])) {
                return $this->json_response([
                    'success' => false,
                    'message' => $stripe_data['message'] ?? 'Stripe payment initialization failed.'
                ], 400);
            }

            $pending_record = [
                'tenant_id'         => (int)$this->tenant_id,
                'temp_order_number' => $temp_order_number,
                'gateway'           => 'stripe',
                'gateway_order_id'  => $stripe_data['session_id'] ?? NULL,
                'user_id'           => !empty($this->current_user['id']) ? (int)$this->current_user['id'] : NULL,
                'order_data'        => json_encode($order_data),
                'items_data'        => json_encode($items),
                'is_buy_now'        => $is_buy_now ? 1 : 0,
                'buy_now_key'       => $matched_buy_now_key,
                'status'            => 'pending'
            ];

            if ($this->db->table_exists('pending_checkouts')) {
                $this->db->where('tenant_id', $this->tenant_id)->where('temp_order_number', $temp_order_number)->delete('pending_checkouts');
                $this->db->insert('pending_checkouts', $pending_record);
            }
            $this->session->set_userdata('pending_checkout_' . $temp_order_number, $pending_record);

            $redirect_url = !empty($stripe_data['checkout_url']) ? $stripe_data['checkout_url'] : site_url('payment/stripe/' . $temp_order_number);

            return $this->json_response([
                'success'      => true,
                'gateway'      => 'stripe',
                'order_number' => $temp_order_number,
                'order'        => $temp_order,
                'redirect_url' => $redirect_url,
                'stripe'       => $stripe_data
            ]);
        }

        // 3. PAYU GATEWAY
        if ($payment_method === 'payu') {
            $temp_order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            $temp_order = $order_data;
            $temp_order['order_number'] = $temp_order_number;
            $temp_order['items'] = $items;

            $payu_data = $this->payment_model->init_payu($temp_order);
            if (empty($payu_data['success'])) {
                return $this->json_response([
                    'success' => false,
                    'message' => $payu_data['message'] ?? 'PayU payment initialization failed.'
                ], 400);
            }

            $pending_record = [
                'tenant_id'         => (int)$this->tenant_id,
                'temp_order_number' => $temp_order_number,
                'gateway'           => 'payu',
                'gateway_order_id'  => $payu_data['txnid'] ?? NULL,
                'user_id'           => !empty($this->current_user['id']) ? (int)$this->current_user['id'] : NULL,
                'order_data'        => json_encode($order_data),
                'items_data'        => json_encode($items),
                'is_buy_now'        => $is_buy_now ? 1 : 0,
                'buy_now_key'       => $matched_buy_now_key,
                'status'            => 'pending'
            ];

            if ($this->db->table_exists('pending_checkouts')) {
                $this->db->where('tenant_id', $this->tenant_id)->where('temp_order_number', $temp_order_number)->delete('pending_checkouts');
                $this->db->insert('pending_checkouts', $pending_record);
            }
            $this->session->set_userdata('pending_checkout_' . $temp_order_number, $pending_record);

            return $this->json_response([
                'success'      => true,
                'gateway'      => 'payu',
                'order_number' => $temp_order_number,
                'order'        => $temp_order,
                'payu'         => $payu_data
            ]);
        }

        // 4. CASH ON DELIVERY (COD) - Immediately place order with COD status
        $order_number = $this->order_model->create_order($order_data, $items);
        if ($is_buy_now && $matched_buy_now_key) {
            $this->cart_model->remove_item($matched_buy_now_key);
            $this->session->unset_userdata('checkout_buy_now_item');
        } else {
            $this->cart_model->clear_cart();
        }

        return $this->json_response([
            'success'      => true,
            'gateway'      => 'cod',
            'order_number' => $order_number,
            'redirect_url' => site_url('account/order/' . $order_number)
        ]);
    }

    

    public function verify_razorpay_ajax()
    {
        $this->load->model('payment_model');
        $this->load->model('order_model');

        $order_number = trim((string)$this->input->post('order_number', TRUE));
        $payment_id   = trim((string)$this->input->post('razorpay_payment_id', TRUE));
        $order_id     = trim((string)$this->input->post('razorpay_order_id', TRUE));
        $signature    = trim((string)$this->input->post('razorpay_signature', TRUE));

        if (empty($order_number) || empty($payment_id)) {
            return $this->json_response(['success' => false, 'message' => 'Invalid payment response data.'], 400);
        }

        // 1. Check if the order was already finalized (e.g. by Webhook)
        $existing = $this->order_model->get_by_order_number($order_number);
        if ($existing) {
            if ($existing['payment_status'] !== 'paid') {
                $this->order_model->update_payment($order_number, 'paid', $payment_id, [
                    'gateway'             => 'razorpay',
                    'razorpay_order_id'   => $order_id,
                    'razorpay_payment_id' => $payment_id,
                    'razorpay_signature'  => $signature,
                    'verified_at'         => date('Y-m-d H:i:s')
                ]);
                $this->order_model->update_status($order_number, 'processing');
            }
            $this->cart_model->clear_cart();
            return $this->json_response([
                'success'      => true,
                'order_number' => $order_number,
                'redirect_url' => site_url('account/order/' . $order_number)
            ]);
        }

        // 2. Fetch pending checkout record
        $pending = null;
        if ($this->db->table_exists('pending_checkouts')) {
            $pending = $this->db->where('tenant_id', $this->tenant_id)
                                ->where('temp_order_number', $order_number)
                                ->where('status', 'pending')
                                ->get('pending_checkouts')
                                ->row_array();
        }

        if (!$pending) {
            $sess_p = $this->session->userdata('pending_checkout_' . $order_number);
            if (!empty($sess_p) && is_array($sess_p)) {
                $pending = $sess_p;
            }
        }

        if (!$pending) {
            return $this->json_response(['success' => false, 'message' => 'Pending checkout session not found or already completed.'], 404);
        }

        // 3. Verify Razorpay cryptographic signature
        $verified = $this->payment_model->verify_razorpay_signature($order_id, $payment_id, $signature);
        if (!$verified) {
            return $this->json_response(['success' => false, 'message' => 'Razorpay payment signature verification failed.'], 400);
        }

        // 4. Signature is VALID! Now create the real order in database & deduct stock
        $order_data = is_array($pending['order_data']) ? $pending['order_data'] : json_decode($pending['order_data'], true);
        $items      = is_array($pending['items_data']) ? $pending['items_data'] : json_decode($pending['items_data'], true);

        $order_data['order_number']           = $order_number;
        $order_data['payment_status']         = 'paid';
        $order_data['order_status']           = 'processing';
        $order_data['payment_method']         = 'razorpay';
        $order_data['payment_transaction_id'] = $payment_id;
        $order_data['payment_details']        = json_encode([
            'gateway'             => 'razorpay',
            'razorpay_order_id'   => $order_id,
            'razorpay_payment_id' => $payment_id,
            'razorpay_signature'  => $signature,
            'verified_at'         => date('Y-m-d H:i:s')
        ]);

        $this->order_model->create_order($order_data, $items);

        // Mark pending checkout as completed
        if ($this->db->table_exists('pending_checkouts')) {
            $this->db->where('tenant_id', $this->tenant_id)
                     ->where('temp_order_number', $order_number)
                     ->update('pending_checkouts', ['status' => 'completed']);
        }
        $this->session->unset_userdata('pending_checkout_' . $order_number);

        // Clear cart or buy-now item
        if (!empty($pending['is_buy_now']) && !empty($pending['buy_now_key'])) {
            $this->cart_model->remove_item($pending['buy_now_key']);
            $this->session->unset_userdata('checkout_buy_now_item');
        } else {
            $this->cart_model->clear_cart();
        }

        return $this->json_response([
            'success'      => true,
            'order_number' => $order_number,
            'redirect_url' => site_url('account/order/' . $order_number)
        ]);
    }

    public function init_cod_pay_online_ajax()
    {
        if (!$this->is_logged_in() || empty($this->current_user['id'])) {
            return $this->json_response(['success' => false, 'message' => 'Please sign in to make payments.'], 401);
        }

        $order_number = trim((string)$this->input->post('order_number', TRUE));
        $this->load->model('order_model');
        $this->load->model('payment_model');

        $order = $this->order_model->get_by_order_number($order_number);
        if (!$order || (int)$order['tenant_id'] !== (int)$this->tenant_id) {
            return $this->json_response(['success' => false, 'message' => 'Order not found.'], 404);
        }

        if ((int)$order['user_id'] !== (int)$this->current_user['id']) {
            return $this->json_response(['success' => false, 'message' => 'Access denied.'], 403);
        }

        if ($order['payment_status'] === 'paid') {
            return $this->json_response(['success' => false, 'message' => 'This order is already paid.'], 400);
        }

        if ($order['order_status'] === 'cancelled') {
            return $this->json_response(['success' => false, 'message' => 'This order has been cancelled.'], 400);
        }

        $rzp_data = $this->payment_model->init_razorpay($order);
        if (empty($rzp_data['success'])) {
            return $this->json_response([
                'success' => false,
                'message' => $rzp_data['message'] ?? 'Unable to initialize Razorpay payment.'
            ], 400);
        }

        return $this->json_response([
            'success'  => true,
            'order'    => $order,
            'razorpay' => $rzp_data
        ]);
    }

    public function verify_cod_pay_online_ajax()
    {
        if (!$this->is_logged_in() || empty($this->current_user['id'])) {
            return $this->json_response(['success' => false, 'message' => 'Please sign in.'], 401);
        }

        $order_number = trim((string)$this->input->post('order_number', TRUE));
        $payment_id   = trim((string)$this->input->post('razorpay_payment_id', TRUE));
        $order_id     = trim((string)$this->input->post('razorpay_order_id', TRUE));
        $signature    = trim((string)$this->input->post('razorpay_signature', TRUE));

        $this->load->model('order_model');
        $this->load->model('payment_model');

        $order = $this->order_model->get_by_order_number($order_number);
        if (!$order || (int)$order['tenant_id'] !== (int)$this->tenant_id) {
            return $this->json_response(['success' => false, 'message' => 'Order not found.'], 404);
        }

        if ((int)$order['user_id'] !== (int)$this->current_user['id']) {
            return $this->json_response(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $verified = $this->payment_model->verify_razorpay_signature($order_id, $payment_id, $signature);
        if (!$verified) {
            return $this->json_response(['success' => false, 'message' => 'Payment signature verification failed.'], 400);
        }

        $this->order_model->update_payment(
            $order_number,
            'paid',
            $payment_id,
            [
                'gateway'             => 'razorpay',
                'payment_mode'        => 'cod_paid_online',
                'razorpay_order_id'   => $order_id,
                'razorpay_payment_id' => $payment_id,
                'razorpay_signature'  => $signature,
                'paid_at'             => date('Y-m-d H:i:s')
            ]
        );
        $this->db->where('tenant_id', $this->tenant_id)
                 ->where('order_number', $order_number)
                 ->update('orders', ['payment_method' => 'razorpay']);

        return $this->json_response([
            'success' => true,
            'message' => 'Payment successful! Order marked as Paid Online.'
        ]);
    }

    public function confirm_stripe_ajax()
    {
        $common_order = Static_template_data::get('common_order');
        $order_number = $common_order['order_number'] ?? 'ORD-2026-88412';
        $this->cart_model->clear_cart();
        return $this->json_response([
            'success'      => true,
            'order_number' => $order_number,
            'redirect_url' => site_url('account/order/' . $order_number)
        ]);
    }

    public function process()
    {
        $common_order = Static_template_data::get('common_order');
        $order_number = $common_order['order_number'] ?? 'ORD-2026-88412';
        $this->cart_model->clear_cart();
        redirect('account/order/' . $order_number);
    }
}
