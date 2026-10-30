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
        $items = $this->cart_model->get_items();
        if (empty($items)) {
            redirect('cart');
        }

        if (!$this->is_logged_in() || empty($this->current_user['id'])) {
            $this->session->set_flashdata('error', 'Please sign in to proceed to checkout.');
            redirect('cart?login=1');
        }

        $shipping_method = $this->session->userdata('shipping_method') ?: 'standard';
        $summary = $this->cart_model->get_cart_summary($shipping_method);

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
        $items = $this->cart_model->get_items();
        if (empty($items)) {
            redirect('cart');
        }

        if (!$this->is_logged_in() || empty($this->current_user['id'])) {
            $this->session->set_flashdata('error', 'Please sign in to proceed to checkout.');
            redirect('cart?login=1');
        }

        $shipping_method = $this->session->userdata('shipping_method') ?: 'standard';
        $summary = $this->cart_model->get_cart_summary($shipping_method);

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

        $cod_check = $this->cart_model->check_cod_eligibility();
        $cod_eligible = $cod_check['eligible'] ?? TRUE;
        $non_cod_items = $cod_check['non_cod_items'] ?? [];

        $default_gateway = $this->setting_model->get('default_payment_gateway', 'cod');

        $data = [
            'title'           => 'Payment - ' . $this->site_name,
            'active_page'     => 'checkout',
            'cart_items'      => $items,
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
        $items = $this->cart_model->get_items();
        if (empty($items)) {
            return $this->json_response(['success' => false, 'message' => 'Your shopping bag is empty.'], 400);
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
            $cod_check = $this->cart_model->check_cod_eligibility();
            if (!$cod_check['eligible']) {
                $non_cod = implode(', ', $cod_check['non_cod_items'] ?? []);
                return $this->json_response([
                    'success' => false,
                    'message' => "Cash on Delivery is not available for: {$non_cod}. Please select an online payment method."
                ], 400);
            }
        }

        $shipping_method = $this->input->post('shipping_method', TRUE) ?: ($this->session->userdata('shipping_method') ?: 'standard');
        $summary = $this->cart_model->get_cart_summary($shipping_method);

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

        // 1. RAZORPAY GATEWAY
        if ($payment_method === 'razorpay') {
            $temp_order = $order_data;
            $temp_order['order_number'] = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
            $temp_order['items'] = $items;

            $razorpay_data = $this->payment_model->init_razorpay($temp_order);
            if (empty($razorpay_data['success'])) {
                return $this->json_response([
                    'success' => false,
                    'message' => $razorpay_data['message'] ?? 'Razorpay payment initialization failed. Please check your API keys.'
                ], 400);
            }

            // Create order with pending status
            $order_number = $this->order_model->create_order($order_data, $items);
            $order = $this->order_model->get_by_order_number($order_number);
            $razorpay_data['order_number'] = $order_number;

            return $this->json_response([
                'success'      => true,
                'gateway'      => 'razorpay',
                'order_number' => $order_number,
                'order'        => $order,
                'razorpay'     => $razorpay_data
            ]);
        }

        // 2. STRIPE GATEWAY
        if ($payment_method === 'stripe') {
            $temp_order = $order_data;
            $temp_order['order_number'] = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
            $temp_order['items'] = $items;

            $stripe_data = $this->payment_model->init_stripe($temp_order);
            if (empty($stripe_data['success'])) {
                return $this->json_response([
                    'success' => false,
                    'message' => $stripe_data['message'] ?? 'Stripe payment initialization failed.'
                ], 400);
            }

            $order_number = $this->order_model->create_order($order_data, $items);
            $order = $this->order_model->get_by_order_number($order_number);
            $redirect_url = !empty($stripe_data['checkout_url']) ? $stripe_data['checkout_url'] : site_url('payment/stripe/' . $order_number);

            return $this->json_response([
                'success'      => true,
                'gateway'      => 'stripe',
                'order_number' => $order_number,
                'order'        => $order,
                'redirect_url' => $redirect_url,
                'stripe'       => $stripe_data
            ]);
        }

        // 3. PAYU GATEWAY
        if ($payment_method === 'payu') {
            $temp_order = $order_data;
            $temp_order['order_number'] = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
            $temp_order['items'] = $items;

            $payu_data = $this->payment_model->init_payu($temp_order);
            if (empty($payu_data['success'])) {
                return $this->json_response([
                    'success' => false,
                    'message' => $payu_data['message'] ?? 'PayU payment initialization failed.'
                ], 400);
            }

            $order_number = $this->order_model->create_order($order_data, $items);
            $order = $this->order_model->get_by_order_number($order_number);

            return $this->json_response([
                'success'      => true,
                'gateway'      => 'payu',
                'order_number' => $order_number,
                'order'        => $order,
                'payu'         => $payu_data
            ]);
        }

        // 4. CASH ON DELIVERY (COD)
        $order_number = $this->order_model->create_order($order_data, $items);
        $this->cart_model->clear_cart();

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

        $order_number = $this->input->post('order_number', TRUE);
        $order = $this->order_model->get_by_order_number($order_number);
        if (!$order) {
            return $this->json_response(['success' => false, 'message' => 'Order not found.'], 404);
        }

        $payment_id = $this->input->post('razorpay_payment_id', TRUE);
        $order_id   = $this->input->post('razorpay_order_id', TRUE);
        $signature  = $this->input->post('razorpay_signature', TRUE);

        $verified = $this->payment_model->verify_razorpay_signature($order_id, $payment_id, $signature);
        if ($verified) {
            $this->order_model->update_payment(
                $order_number,
                'paid',
                $payment_id,
                [
                    'gateway'             => 'razorpay',
                    'razorpay_order_id'   => $order_id,
                    'razorpay_payment_id' => $payment_id,
                    'razorpay_signature'  => $signature,
                    'verified_at'         => date('Y-m-d H:i:s')
                ]
            );
            $this->order_model->update_status($order_number, 'processing');
            $this->cart_model->clear_cart();

            return $this->json_response([
                'success'      => true,
                'order_number' => $order_number,
                'redirect_url' => site_url('account/order/' . $order_number)
            ]);
        } else {
            $this->order_model->update_payment($order_number, 'failed');
            return $this->json_response(['success' => false, 'message' => 'Razorpay payment verification failed.'], 400);
        }
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
