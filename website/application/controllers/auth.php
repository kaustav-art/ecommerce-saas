<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class auth extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
    }

    private function _resolve_login_redirect()
    {
        $redirect_to = trim((string) $this->input->post('redirect_to', TRUE));
        if (empty($redirect_to)) {
            $redirect_to = trim((string) $this->input->get('redirect', TRUE));
        }
        if (empty($redirect_to)) {
            $redirect_to = trim((string) $this->session->userdata('redirect_url'));
            if (!empty($redirect_to)) {
                $this->session->unset_userdata('redirect_url');
            }
        }
        if (empty($redirect_to)) {
            $referer = $this->input->server('HTTP_REFERER');
            if (!empty($referer) && strpos($referer, 'auth/login') === false && strpos($referer, 'auth/logout') === false && strpos($referer, 'auth/register') === false) {
                $redirect_to = $referer;
            }
        }

        if (!empty($redirect_to)) {
            if (strpos($redirect_to, 'auth/login') !== false || strpos($redirect_to, 'auth/logout') !== false || strpos($redirect_to, 'auth/register') !== false) {
                $redirect_to = '';
            }
        }

        if (empty($redirect_to)) {
            $redirect_to = site_url('home');
        } elseif (strpos($redirect_to, 'http') !== 0) {
            $redirect_to = site_url(ltrim($redirect_to, '/'));
        }

        return $redirect_to;
    }

    public function login()
    {
        $redirect_to = $this->_resolve_login_redirect();

        if ($this->is_logged_in()) {
            redirect($redirect_to);
        }

        if ($this->input->method() === 'post') {
            $email    = trim((string) $this->input->post('email', TRUE));
            $password = (string) $this->input->post('password');

            $user = $this->user_model->get_by_email($email);
            if ($user && password_verify($password, $user['password'])) {
                if (($user['status'] ?? 'active') !== 'active') {
                    if ($this->input->is_ajax_request()) {
                        $this->json_response(['success' => false, 'message' => 'Your account has been deactivated.']);
                    }
                    $this->session->set_flashdata('error', 'Your account has been deactivated.');
                    redirect($redirect_to);
                }

                $this->session->set_userdata([
                    'user_id'         => (int) $user['id'],
                    'user_tenant_id'  => (int) $this->tenant_id,
                    'user_first_name' => $user['first_name'],
                    'user_last_name'  => $user['last_name'] ?? '',
                    'user_email'      => $user['email'],
                    'user_phone'      => $user['phone'] ?? '',
                    'user_avatar'     => $user['avatar'] ?? '',
                    'user_logged_in'  => TRUE
                ]);

                $this->_sync_guest_cart((int) $user['id']);
                $this->_sync_guest_wishlist((int) $user['id']);

                $user_addresses = $this->user_model->get_addresses((int) $user['id']);
                $default_address = $this->user_model->get_default_address((int) $user['id']);
                if (empty($default_address) && !empty($user_addresses)) {
                    $default_address = $user_addresses[0];
                }

                if ($this->input->is_ajax_request()) {
                    $this->json_response([
                        'success'         => true,
                        'status'          => 'logged_in',
                        'message'         => 'Welcome back, ' . $user['first_name'] . '!',
                        'redirect'        => $redirect_to,
                        'user'            => $user,
                        'has_address'     => !empty($user_addresses),
                        'default_address' => $default_address,
                        'user_addresses'  => $user_addresses
                    ]);
                }

                $this->session->set_flashdata('success', 'Welcome back, ' . $user['first_name'] . '!');
                redirect($redirect_to);
            } else {
                if ($this->input->is_ajax_request()) {
                    $this->json_response(['success' => false, 'message' => 'Invalid email address or password.']);
                }
                $this->session->set_flashdata('error', 'Invalid email address or password.');
                redirect($redirect_to);
            }
        }

        redirect($redirect_to);
    }

    public function register()
    {
        redirect('home');
    }

    public function logout()
    {
        $tid = (int)$this->tenant_id;
        $keys_to_unset = [
            'user_id',
            'user_tenant_id',
            'user_first_name',
            'user_last_name',
            'user_email',
            'user_phone',
            'user_avatar',
            'user_logged_in',
            'otp_auth',
            'pending_reg',
            'cart_t_' . $tid,
            'cart',
            'guest_wishlist_t_' . $tid,
            'guest_wishlist',
            'pending_wishlist',
            'coupon_code',
            'coupon_discount',
            'coupon_data',
            'applied_coupon',
            'selected_address_id',
            'shipping_method'
        ];

        foreach (array_keys($this->session->all_userdata()) as $sess_key) {
            if (strpos($sess_key, 'cart_t_') === 0 || strpos($sess_key, 'guest_wishlist_t_') === 0) {
                $keys_to_unset[] = $sess_key;
            }
        }
        $this->session->unset_userdata(array_unique($keys_to_unset));

        // Reset guest cookies
        setcookie('guest_wishlist', '', time() - 3600, '/');
        setcookie('pending_wishlist', '', time() - 3600, '/');
        unset($_COOKIE['guest_wishlist'], $_COOKIE['pending_wishlist']);

        redirect('home');
    }

    public function send_otp()
    {
        $identifier    = trim((string) $this->input->post('identifier', TRUE));
        $email_context = trim((string) $this->input->post('email', TRUE));

        if (empty($identifier)) {
            $this->json_response(['success' => false, 'message' => 'Please enter a valid mobile number or email address.']);
        }

        $is_email = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;

        // If user submitted an email as the initial identifier, ask for mobile number next
        if ($is_email) {
            $this->json_response([
                'success' => true,
                'step'    => 'ask_phone',
                'email'   => strtolower($identifier),
                'message' => 'Please enter your mobile number to continue.'
            ]);
        }

        // Otherwise it is a mobile number
        $clean_phone = preg_replace('/[^0-9+]/', '', $identifier);
        $digits      = preg_replace('/[^0-9]/', '', $clean_phone);
        if (strlen($digits) < 7 || strlen($digits) > 15) {
            $this->json_response(['success' => false, 'message' => 'Please enter a valid mobile number (7 to 15 digits).']);
        }

        $attached_email = (!empty($email_context) && filter_var($email_context, FILTER_VALIDATE_EMAIL))
            ? strtolower($email_context)
            : null;

        // Check if phone number (or attached email) already exists in database for this tenant
        $existing_user = $this->user_model->get_by_phone($clean_phone);
        if (!$existing_user && !empty($attached_email)) {
            $existing_user = $this->user_model->get_by_email($attached_email);
        }
        $phone_exists = (bool) $existing_user;

        $otp = (string) mt_rand(100000, 999999);

        // Store in session
        $this->session->set_userdata('otp_auth', [
            'phone'          => $clean_phone,
            'attached_email' => $attached_email,
            'code'           => $otp,
            'phone_exists'   => $phone_exists,
            'user_id'        => $existing_user ? (int) $existing_user['id'] : null,
            'expires_at'     => time() + 600 // 10 minutes
        ]);

        $this->json_response([
            'success'      => true,
            'step'         => 'otp',
            'phone'        => $clean_phone,
            'email'        => $attached_email,
            'phone_exists' => $phone_exists,
            'demo_otp'     => $otp,
            'message'      => 'A 6-digit verification code has been sent to ' . html_escape($clean_phone) . '.'
        ]);
    }

    public function verify_otp()
    {
        $phone       = trim((string) $this->input->post('phone', TRUE)) ?: trim((string) $this->input->post('identifier', TRUE));
        $otp         = trim((string) $this->input->post('otp', TRUE));
        $redirect_to = $this->_resolve_login_redirect();

        if (empty($otp)) {
            $this->json_response(['success' => false, 'message' => 'Please enter the 6-digit verification code.']);
        }

        $otp_session = $this->session->userdata('otp_auth');
        if (!$otp_session || !isset($otp_session['code'])) {
            $this->json_response(['success' => false, 'message' => 'OTP expired or not requested. Please request a new code.']);
        }

        if (time() > $otp_session['expires_at']) {
            $this->session->unset_userdata('otp_auth');
            $this->json_response(['success' => false, 'message' => 'OTP code has expired. Please request a new code.']);
        }

        if ($otp_session['code'] !== $otp) {
            $this->json_response(['success' => false, 'message' => 'Invalid OTP code. Please enter the correct code.']);
        }

        // Case 1: Phone number ALREADY exists in database -> Immediately login
        if (!empty($otp_session['phone_exists']) && !empty($otp_session['user_id'])) {
            $user = $this->user_model->get_by_id($otp_session['user_id']);
            if (!$user) {
                $user = $this->user_model->get_by_phone($otp_session['phone']);
            }

            if ($user && ($user['status'] ?? 'active') !== 'active') {
                $this->json_response(['success' => false, 'message' => 'Your account has been deactivated.']);
            }

            if ($user) {
                $this->session->unset_userdata('otp_auth');
                $this->session->unset_userdata('pending_reg');

                $this->session->set_userdata([
                    'user_id'         => (int) $user['id'],
                    'user_tenant_id'  => (int) $this->tenant_id,
                    'user_first_name' => $user['first_name'],
                    'user_last_name'  => $user['last_name'] ?? '',
                    'user_email'      => $user['email'],
                    'user_phone'      => $user['phone'] ?? '',
                    'user_avatar'     => $user['avatar'] ?? '',
                    'user_logged_in'  => TRUE
                ]);

                $this->_sync_guest_cart((int) $user['id']);
                $this->_sync_guest_wishlist((int) $user['id']);

                $user_addresses = $this->user_model->get_addresses((int) $user['id']);
                $default_address = $this->user_model->get_default_address((int) $user['id']);
                if (empty($default_address) && !empty($user_addresses)) {
                    $default_address = $user_addresses[0];
                }

                $this->json_response([
                    'success'         => true,
                    'status'          => 'logged_in',
                    'is_new'          => false,
                    'message'         => 'Welcome back, ' . $user['first_name'] . '!',
                    'redirect'        => $redirect_to,
                    'user'            => [
                        'id'         => (int) $user['id'],
                        'first_name' => $user['first_name'],
                        'last_name'  => $user['last_name'] ?? '',
                        'email'      => $user['email'],
                        'phone'      => $user['phone'] ?? ''
                    ],
                    'has_address'     => !empty($user_addresses),
                    'default_address' => $default_address,
                    'user_addresses'  => $user_addresses
                ]);
            }
        }

        // Case 2: Phone number is NEW -> Mark mobile as verified in session
        $this->session->set_userdata('pending_reg', [
            'phone'          => $otp_session['phone'],
            'email'          => $otp_session['attached_email'] ?? null,
            'phone_verified' => true,
            'verified_at'    => time()
        ]);
        $this->session->unset_userdata('otp_auth');

        if (!empty($otp_session['attached_email'])) {
            $this->json_response([
                'success' => true,
                'status'  => 'need_name',
                'is_new'  => true,
                'phone'   => $otp_session['phone'],
                'email'   => $otp_session['attached_email'],
                'message' => 'Mobile verified! Please enter your name to complete registration.'
            ]);
        } else {
            $this->json_response([
                'success' => true,
                'status'  => 'need_email_name',
                'is_new'  => true,
                'phone'   => $otp_session['phone'],
                'message' => 'Mobile verified! Please enter your email and name to complete registration.'
            ]);
        }
    }

    public function complete_registration()
    {
        $pending = $this->session->userdata('pending_reg');
        if (!$pending || empty($pending['phone_verified']) || empty($pending['phone'])) {
            $this->json_response(['success' => false, 'message' => 'Verification session expired. Please start again with your mobile or email.']);
        }

        $name        = trim((string) $this->input->post('name', TRUE));
        $first_name  = trim((string) $this->input->post('first_name', TRUE));
        $last_name   = trim((string) $this->input->post('last_name', TRUE));
        $post_email  = trim((string) $this->input->post('email', TRUE));
        $redirect_to = $this->_resolve_login_redirect();

        $final_email = !empty($pending['email']) ? strtolower(trim($pending['email'])) : strtolower($post_email);
        if (empty($final_email) || !filter_var($final_email, FILTER_VALIDATE_EMAIL)) {
            $this->json_response(['success' => false, 'message' => 'Please provide a valid email address.']);
        }

        $existing_user_email = $this->user_model->get_by_email($final_email);
        if ($existing_user_email) {
            $this->json_response(['success' => false, 'message' => 'An account with email ' . html_escape($final_email) . ' already exists. Please use another email address or sign in.']);
        }

        if (empty($first_name)) {
            if (empty($name)) {
                $this->json_response(['success' => false, 'message' => 'Please enter your name.']);
            }
            $parts      = explode(' ', $name, 2);
            $first_name = $parts[0];
            $last_name  = !empty($parts[1]) ? $parts[1] : '';
        }

        $new_user_id = $this->user_model->register([
            'tenant_id'  => (int) $this->tenant_id,
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'email'      => $final_email,
            'phone'      => $pending['phone'],
            'password'   => password_hash('User@' . mt_rand(10000, 99999), PASSWORD_BCRYPT),
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if (!$new_user_id) {
            $this->json_response(['success' => false, 'message' => 'Failed to create account. Please try again.']);
        }

        $user = $this->user_model->get_by_id($new_user_id);

        $this->session->unset_userdata('pending_reg');
        $this->session->unset_userdata('otp_auth');

        $this->session->set_userdata([
            'user_id'         => (int) $user['id'],
            'user_tenant_id'  => (int) $this->tenant_id,
            'user_first_name' => $user['first_name'],
            'user_last_name'  => $user['last_name'] ?? '',
            'user_email'      => $user['email'],
            'user_phone'      => $user['phone'] ?? '',
            'user_avatar'     => '',
            'user_logged_in'  => TRUE
        ]);

        $this->_sync_guest_cart((int) $user['id']);
        $this->_sync_guest_wishlist((int) $user['id']);

        $user_addresses = $this->user_model->get_addresses((int) $user['id']);
        $default_address = $this->user_model->get_default_address((int) $user['id']);
        if (empty($default_address) && !empty($user_addresses)) {
            $default_address = $user_addresses[0];
        }

        $this->json_response([
            'success'         => true,
            'status'          => 'registered_and_logged_in',
            'message'         => 'Registration successful! Welcome to ' . $this->site_name . ', ' . $user['first_name'] . '.',
            'redirect'        => $redirect_to,
            'user'            => [
                'id'         => (int) $user['id'],
                'first_name' => $user['first_name'],
                'last_name'  => $user['last_name'] ?? '',
                'email'      => $user['email'],
                'phone'      => $user['phone'] ?? ''
            ],
            'has_address'     => !empty($user_addresses),
            'default_address' => $default_address,
            'user_addresses'  => $user_addresses
        ]);
    }

    protected function _sync_guest_cart($user_id)
    {
        $this->load->model('cart_model');
        $this->cart_model->sync_session_to_db((int) $user_id);
    }

    protected function _sync_guest_wishlist($user_id)
    {
        $this->load->model('wishlist_model');
        $user_id = (int) $user_id;
        $items_to_add = [];

        // 1. Pending wishlist item from cookie
        $pending_cookie = isset($_COOKIE['pending_wishlist']) ? (int)$_COOKIE['pending_wishlist'] : 0;
        if ($pending_cookie > 0) {
            $items_to_add[] = $pending_cookie;
            setcookie('pending_wishlist', '', time() - 3600, '/');
            unset($_COOKIE['pending_wishlist']);
        }

        // 2. Pending wishlist item from session
        $pending_session = (int) $this->session->userdata('pending_wishlist');
        if ($pending_session > 0) {
            $items_to_add[] = $pending_session;
            $this->session->unset_userdata('pending_wishlist');
        }

        // 3. Guest wishlist from session
        $sess_wl = $this->session->userdata('guest_wishlist_t_' . $this->tenant_id);
        if (!empty($sess_wl) && is_array($sess_wl)) {
            foreach ($sess_wl as $wid) {
                $items_to_add[] = (int) $wid;
            }
        }
        $this->session->unset_userdata('guest_wishlist');
        $this->session->unset_userdata('guest_wishlist_t_' . $this->tenant_id);

        // 4. Guest wishlist from cookie
        if (isset($_COOKIE['guest_wishlist'])) {
            $cookie_raw = $_COOKIE['guest_wishlist'];
            $decoded = json_decode($cookie_raw, true);
            if (is_array($decoded)) {
                foreach ($decoded as $wid) {
                    $items_to_add[] = (int) $wid;
                }
            } elseif (is_numeric($cookie_raw)) {
                $items_to_add[] = (int) $cookie_raw;
            }
            setcookie('guest_wishlist', '', time() - 3600, '/');
            unset($_COOKIE['guest_wishlist']);
        }

        $items_to_add = array_unique(array_filter($items_to_add));

        $added_any = false;
        foreach ($items_to_add as $pid) {
            $pid = (int) $pid;
            if ($pid > 0 && !$this->wishlist_model->is_wishlisted($user_id, $pid)) {
                $this->db->query(
                    "INSERT INTO wishlists (tenant_id, user_id, product_id, created_at) " .
                    "VALUES (?, ?, ?, NOW()) " .
                    "ON DUPLICATE KEY UPDATE product_id = VALUES(product_id)",
                    [(int)$this->tenant_id, (int)$user_id, (int)$pid]
                );
                $added_any = true;
            }
        }

        if ($added_any) {
            $this->session->set_flashdata('success', 'Wishlist items synchronized to your account!');
        }
    }
}
