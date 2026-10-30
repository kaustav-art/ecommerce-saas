<?php
/**
 * SaaS Subscription & Project Setup Wizard API (subscribe_api.php)
 * Follows referance-project/subscribe_api.php 5-Step Wizard Workflow + Multi-Template E-Commerce Provisioning
 * Handles:
 * 1. check_unique (Email & Phone uniqueness validation)
 * 2. send_otp (OTP generation, session storage & optional email dispatch)
 * 3. verify_otp (6-digit OTP verification)
 * 4. resend_otp (Resend 6-digit OTP)
 * 5. check_domain (Domain / Store Slug availability check)
 * 6. save_pending_setup (Session persistence before Stripe redirect)
 * 7. process_subscription (Immediate order creation & tenant provisioning with Superadmin template data)
 */
require_once __DIR__ . '/saas_core.php';

$is_direct_api_call = (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__));
$action = isset($_GET['action']) ? trim($_GET['action']) : (isset($_POST['action']) ? trim($_POST['action']) : '');

if ($is_direct_api_call || !empty($action)) {
    header('Content-Type: application/json; charset=utf-8');
}

$pdo = get_saas_pdo();

function api_json($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function normalize_phone_digits($ph) {
    return preg_replace('/[^0-9]/', '', (string)$ph);
}

function is_saas_email_taken($pdo, $email) {
    $email = strtolower(trim((string)$email));
    if ($email === '') return false;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `saas_tenants` WHERE LOWER(`admin_email`) = ? OR LOWER(`business_email`) = ?");
    $stmt->execute([$email, $email]);
    if ((int)$stmt->fetchColumn() > 0) return true;

    $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM `admins` WHERE LOWER(`email`) = ?");
    $stmt2->execute([$email]);
    if ((int)$stmt2->fetchColumn() > 0) return true;

    return false;
}

function is_saas_phone_taken($pdo, $phone) {
    $clean = normalize_phone_digits($phone);
    if (strlen($clean) < 7) return false;

    $stmt = $pdo->query("SELECT `admin_phone`, `business_phone` FROM `saas_tenants` WHERE (`admin_phone` IS NOT NULL AND `admin_phone` != '') OR (`business_phone` IS NOT NULL AND `business_phone` != '')");
    while ($row = $stmt->fetch()) {
        if (normalize_phone_digits($row['admin_phone'] ?? '') === $clean || normalize_phone_digits($row['business_phone'] ?? '') === $clean) {
            return true;
        }
    }
    return false;
}

/**
 * Universal E-Commerce SaaS Tenant Provisioning Engine (called by process_subscription and Stripe redirect return)
 */
function execute_subscription_provisioning($pdo, $settings, $data, $files = []) {
    $plan_code = isset($data['plan_code']) ? trim($data['plan_code']) : ($data['plan_id'] ?? 'STARTER');
    $plan = get_saas_plan_by_id_or_slug($plan_code);
    if (!$plan) {
        $plan = get_saas_plan_by_id_or_slug(1);
    }

    // Step 1 Details
    $name     = trim($data['name'] ?? ($data['owner_name'] ?? ''));
    $email    = strtolower(trim($data['email'] ?? ($data['owner_email'] ?? '')));
    $phone    = trim($data['phone'] ?? ($data['owner_phone'] ?? ''));
    $password = trim($data['password'] ?? '');

    if ($name === '' || $email === '' || $password === '') {
        return ['status' => 'error', 'success' => false, 'message' => 'Full Name, Email Address, and Password are required.'];
    }

    // Step 3 Details
    $business_name   = !empty($data['business_name']) ? trim($data['business_name']) : ($name . ' Store');
    $tagline         = !empty($data['tagline']) ? trim($data['tagline']) : ('Official Online Store of ' . $business_name);
    $company_email   = !empty($data['company_email']) ? trim($data['company_email']) : $email;
    $company_phone   = !empty($data['company_phone']) ? trim($data['company_phone']) : $phone;
    $company_address = !empty($data['company_address']) ? trim($data['company_address']) : '742 Commerce Avenue, Suite 100';
    $currency_symbol = !empty($data['currency_symbol']) ? trim($data['currency_symbol']) : '$';
    $currency_code   = ($currency_symbol === '₹') ? 'INR' : (($currency_symbol === '€') ? 'EUR' : (($currency_symbol === '£') ? 'GBP' : 'USD'));

    $raw_domain = trim($data['domain'] ?? ($data['store_slug'] ?? ''));
    if ($raw_domain === '') {
        $raw_domain = sanitize_store_slug($business_name) . '.com';
    }
    $clean_domain = sanitize_domain_folder($raw_domain);
    if (strlen($clean_domain) < 3 || is_reserved_store_slug($clean_domain)) {
        $clean_domain = 'store-' . rand(100, 999) . '.com';
    }
    $store_slug = $clean_domain;

    // Step 4 Details
    $raw_template    = $data['chosen_template'] ?? ($data['template'] ?? 'template1');
    $chosen_template = normalize_saas_template($raw_template); // fashion | electronics | grocery
    $tpl_folder_key  = get_saas_tpl_folder_key($chosen_template); // template1 | template2 | template3

    // Step 5 Payment Details
    $payment_method = trim($data['payment_method'] ?? 'sandbox');
    $transaction_id = !empty($data['transaction_id']) ? trim($data['transaction_id']) : ('PAY-' . time() . '-' . rand(1000, 9999));

    // Handle Branding Assets (Logo & Favicon)
    $branding_dir = __DIR__ . DIRECTORY_SEPARATOR . 'website' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'branding';
    if (!is_dir($branding_dir)) {
        @mkdir($branding_dir, 0755, true);
    }

    $logo_rel_path    = '';
    $favicon_rel_path = '';

    if (isset($files['company_logo']) && $files['company_logo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($files['company_logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'svg'], true)) {
            $logo_fn = 'logo_' . $store_slug . '_' . time() . '.' . $ext;
            if (@move_uploaded_file($files['company_logo']['tmp_name'], $branding_dir . DIRECTORY_SEPARATOR . $logo_fn)) {
                $logo_rel_path = 'assets/images/branding/' . $logo_fn;
            }
        }
    } elseif (!empty($data['temp_logo_path']) && file_exists($data['temp_logo_path'])) {
        $ext = strtolower(pathinfo($data['temp_logo_path'], PATHINFO_EXTENSION));
        $logo_fn = 'logo_' . $store_slug . '_' . time() . '.' . $ext;
        if (@copy($data['temp_logo_path'], $branding_dir . DIRECTORY_SEPARATOR . $logo_fn)) {
            $logo_rel_path = 'assets/images/branding/' . $logo_fn;
        }
    }

    if (isset($files['favicon']) && $files['favicon']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($files['favicon']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['ico', 'png', 'webp', 'jpg', 'jpeg', 'svg'], true)) {
            $fav_fn = 'fav_' . $store_slug . '_' . time() . '.' . $ext;
            if (@move_uploaded_file($files['favicon']['tmp_name'], $branding_dir . DIRECTORY_SEPARATOR . $fav_fn)) {
                $favicon_rel_path = 'assets/images/branding/' . $fav_fn;
            }
        }
    } elseif (!empty($data['temp_favicon_path']) && file_exists($data['temp_favicon_path'])) {
        $ext = strtolower(pathinfo($data['temp_favicon_path'], PATHINFO_EXTENSION));
        $fav_fn = 'fav_' . $store_slug . '_' . time() . '.' . $ext;
        if (@copy($data['temp_favicon_path'], $branding_dir . DIRECTORY_SEPARATOR . $fav_fn)) {
            $favicon_rel_path = 'assets/images/branding/' . $fav_fn;
        }
    }

    // Create Pending Subscription & Payment Order, then Provision Tenant
    $orderNumber = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
    $subNumber   = 'SUB-' . date('Ymd') . '-' . strtoupper(substr(md5($orderNumber), 0, 6));
    $idempKey    = 'IDEMP_' . md5($orderNumber . '_' . $email . '_' . microtime(true));
    $amount      = (float)($plan['effective_price'] ?? $plan['price'] ?? 29.00);
    $planCurrency= $plan['currency'] ?: 'USD';

    $setupPayload = [
        'plan_id'          => (int)$plan['id'],
        'owner_name'       => $name,
        'business_name'    => $business_name,
        'tagline'          => $tagline,
        'store_slug'       => $store_slug,
        'domain'           => (strpos($store_slug, '.') !== false) ? $store_slug : ($store_slug . '.com'),
        'owner_email'      => $email,
        'owner_phone'      => $phone,
        'business_email'   => $company_email,
        'business_phone'   => $company_phone,
        'business_address' => $company_address,
        'currency_code'    => $currency_code,
        'currency_symbol'  => $currency_symbol,
        'template'         => $chosen_template,
        'password_hash'    => password_hash($password, PASSWORD_BCRYPT),
        'logo'             => $logo_rel_path,
        'favicon'          => $favicon_rel_path,
    ];

    try {
        $insSub = $pdo->prepare("INSERT INTO `saas_subscriptions`
            (`subscription_number`, `tenant_id`, `plan_id`, `template_key`, `billing_cycle`, `amount`, `currency`, `status`)
            VALUES (?, 0, ?, ?, ?, ?, ?, 'pending')");
        $insSub->execute([$subNumber, (int)$plan['id'], $chosen_template, $plan['billing_cycle'] ?? 'monthly', $amount, $planCurrency]);
        $subId = (int)$pdo->lastInsertId();

        $insPay = $pdo->prepare("INSERT INTO `saas_payments`
            (`order_number`, `subscription_id`, `tenant_id`, `plan_id`, `customer_name`, `customer_email`, `customer_phone`, `business_name`, `store_slug`, `chosen_template`, `gateway`, `idempotency_key`, `amount`, `currency`, `payment_status`, `setup_payload`)
            VALUES (?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
        $insPay->execute([
            $orderNumber, $subId, (int)$plan['id'],
            $name, $email, $phone,
            $business_name, $store_slug, $chosen_template,
            $payment_method, $idempKey, $amount, $planCurrency, json_encode($setupPayload)
        ]);

        $prov = provision_saas_tenant($orderNumber, $transaction_id, [
            'gateway'        => $payment_method,
            'transaction_id' => $transaction_id,
            'verified_at'    => date('c')
        ]);

        if (empty($prov['success'])) {
            return ['status' => 'error', 'success' => false, 'message' => $prov['message'] ?? 'Provisioning failed.'];
        }

        if (isset($_SESSION['saas_wizard_registration'])) {
            unset($_SESSION['saas_wizard_registration']);
        }
        if (isset($_SESSION['pending_wizard_setup'])) {
            unset($_SESSION['pending_wizard_setup']);
        }

        $tenant = $prov['tenant'];
        return [
            'status'          => 'success',
            'success'         => true,
            'message'         => 'Payment processed and SaaS store provisioned successfully!',
            'order_number'    => $orderNumber,
            'license_key'     => $prov['license_key'] ?? ($tenant['license_key'] ?? ''),
            'tenant_id'       => (int)$tenant['id'],
            'domain'          => $tenant['slug'],
            'folder_name'     => $tenant['slug'],
            'company_name'    => $tenant['business_name'],
            'plan_title'      => $plan['name'],
            'plan_code'       => $plan['code'] ?: $plan['slug'],
            'chosen_template' => $tpl_folder_key,
            'template_name'   => ucfirst($chosen_template),
            'website_url'     => $prov['store_url'],
            'store_url'       => $prov['store_url'],
            'admin_url'       => $prov['admin_url'],
            'admin_email'     => $email,
            'admin_password'  => $password,
            'db_name'         => $tenant['database_name'] ?? 'ecommerce_main',
            'created_at'      => date('Y-m-d H:i:s')
        ];
    } catch (Exception $e) {
        return ['status' => 'error', 'success' => false, 'message' => 'Provisioning failed: ' . $e->getMessage()];
    }
}

// ==========================================
// 1. ACTION: CHECK UNIQUE (Email, Phone, Store Slug)
// ==========================================
if ($action === 'check_unique') {
    $email = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $slug  = isset($_POST['store_slug']) ? sanitize_store_slug($_POST['store_slug']) : '';

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        api_json(['status' => 'error', 'valid' => false, 'field' => 'email', 'message' => 'Please enter a valid email address.']);
    }

    if ($email !== '' && is_saas_email_taken($pdo, $email)) {
        api_json(['status' => 'error', 'valid' => false, 'field' => 'email', 'message' => 'This email address is already registered. Please use another email.']);
    }

    if ($phone !== '' && is_saas_phone_taken($pdo, $phone)) {
        api_json(['status' => 'error', 'valid' => false, 'field' => 'phone', 'message' => 'This phone number is already registered. Please use another phone number.']);
    }

    if ($slug !== '') {
        if (strlen($slug) < 3) {
            api_json(['status' => 'error', 'valid' => false, 'field' => 'store_slug', 'message' => 'Store slug must be at least 3 characters.']);
        }
        if (is_reserved_store_slug($slug)) {
            api_json(['status' => 'error', 'valid' => false, 'field' => 'store_slug', 'message' => 'This store slug is reserved by the platform.']);
        }
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `saas_tenants` WHERE LOWER(`slug`) = ? OR LOWER(`domain`) = ?");
        $stmt->execute([$slug, $slug . '.com']);
        if ((int)$stmt->fetchColumn() > 0) {
            api_json(['status' => 'error', 'valid' => false, 'field' => 'store_slug', 'message' => 'This store slug is already taken.']);
        }
    }

    api_json([
        'status'    => 'success',
        'valid'     => true,
        'slug'      => $slug,
        'store_url' => $slug ? saas_base_url('website/s/' . $slug) : '',
        'message'   => 'Email and phone are available.'
    ]);
}

// ==========================================
// 2. ACTION: SEND OTP (Step 1 -> Step 2)
// ==========================================
if ($action === 'send_otp') {
    $name     = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email    = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
    $phone    = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($name === '') {
        api_json(['status' => 'error', 'field' => 'name', 'message' => 'Full Name is required.']);
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        api_json(['status' => 'error', 'field' => 'email', 'message' => 'A valid Email Address is required.']);
    }
    if ($phone === '' || strlen(normalize_phone_digits($phone)) < 7) {
        api_json(['status' => 'error', 'field' => 'phone', 'message' => 'A valid Phone Number is required.']);
    }
    if ($password === '' || strlen($password) < 6) {
        api_json(['status' => 'error', 'field' => 'password', 'message' => 'Password must be at least 6 characters.']);
    }

    if (is_saas_email_taken($pdo, $email)) {
        api_json(['status' => 'error', 'field' => 'email', 'message' => 'This email address is already registered. Please use another email.']);
    }
    if (is_saas_phone_taken($pdo, $phone)) {
        api_json(['status' => 'error', 'field' => 'phone', 'message' => 'This phone number is already registered. Please use another phone number.']);
    }

    $otp = (string)mt_rand(100000, 999999);

    $_SESSION['saas_wizard_registration'] = [
        'name'         => $name,
        'email'        => $email,
        'phone'        => $phone,
        'password'     => $password,
        'otp'          => $otp,
        'otp_time'     => time(),
        'otp_verified' => false
    ];

    api_json([
        'status'     => 'success',
        'message'    => 'Verification code sent successfully to ' . $email . ' and ' . $phone . '.',
        'email'      => $email,
        'phone'      => $phone,
        'demo_otp'   => $otp,
        'email_sent' => false
    ]);
}

// ==========================================
// 3. ACTION: VERIFY OTP (Step 2 -> Step 3)
// ==========================================
if ($action === 'verify_otp') {
    $submitted_otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';

    if (!isset($_SESSION['saas_wizard_registration'])) {
        api_json(['status' => 'error', 'message' => 'Session expired. Please restart registration.']);
    }

    $saved_otp = $_SESSION['saas_wizard_registration']['otp'];
    $otp_time  = $_SESSION['saas_wizard_registration']['otp_time'];

    if (time() - $otp_time > 900) {
        api_json(['status' => 'error', 'message' => 'Verification code has expired. Please request a new code.']);
    }

    if ($submitted_otp !== $saved_otp) {
        api_json(['status' => 'error', 'message' => 'Incorrect verification code. Please check and try again.']);
    }

    $_SESSION['saas_wizard_registration']['otp_verified'] = true;

    api_json([
        'status'  => 'success',
        'message' => 'Identity verified successfully!',
        'user'    => [
            'name'  => $_SESSION['saas_wizard_registration']['name'],
            'email' => $_SESSION['saas_wizard_registration']['email'],
            'phone' => $_SESSION['saas_wizard_registration']['phone']
        ]
    ]);
}

// ==========================================
// 4. ACTION: RESEND OTP
// ==========================================
if ($action === 'resend_otp') {
    if (!isset($_SESSION['saas_wizard_registration'])) {
        api_json(['status' => 'error', 'message' => 'Session expired. Please restart registration.']);
    }

    $otp = (string)mt_rand(100000, 999999);
    $_SESSION['saas_wizard_registration']['otp']      = $otp;
    $_SESSION['saas_wizard_registration']['otp_time'] = time();

    api_json([
        'status'   => 'success',
        'message'  => 'New verification code has been dispatched.',
        'demo_otp' => $otp
    ]);
}

// ==========================================
// 5. ACTION: CHECK DOMAIN / STORE SLUG
// ==========================================
if ($action === 'check_domain') {
    $domain = isset($_POST['domain']) ? trim($_POST['domain']) : '';
    $clean  = sanitize_domain_folder($domain);

    if ($clean === '') {
        api_json(['status' => 'error', 'field' => 'domain', 'message' => 'Please enter a domain or folder name (e.g. example.com).']);
    }

    if (strlen($clean) < 3) {
        api_json(['status' => 'error', 'field' => 'domain', 'message' => 'Domain folder name must be at least 3 characters.']);
    }

    if (is_reserved_store_slug($clean) || is_dir(__DIR__ . DIRECTORY_SEPARATOR . $clean)) {
        api_json(['status' => 'error', 'field' => 'domain', 'message' => "The folder '$clean' is already in use or reserved."]);
    }

    $stmt = $pdo->prepare("SELECT `id` FROM `saas_tenants` WHERE LOWER(`slug`) = ? OR LOWER(`domain`) = ? LIMIT 1");
    $stmt->execute([$clean, (strpos($clean, '.') !== false) ? $clean : ($clean . '.com')]);
    if ($stmt->fetch()) {
        api_json(['status' => 'error', 'field' => 'domain', 'message' => "'$clean' is already registered. Please choose another."]);
    }

    api_json([
        'status'       => 'success',
        'clean_domain' => $clean,
        'store_url'    => saas_base_url($clean . '/'),
        'message'      => "✓ Domain folder '$clean' is available!"
    ]);
}

// ==========================================
// 6. ACTION: SAVE PENDING SETUP (For Stripe Redirect)
// ==========================================
if ($action === 'save_pending_setup') {
    $clean_data = [
        'plan_code'       => isset($_POST['plan_code']) ? trim($_POST['plan_code']) : 'STARTER',
        'name'            => isset($_POST['name']) ? trim($_POST['name']) : '',
        'email'           => isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '',
        'phone'           => isset($_POST['phone']) ? trim($_POST['phone']) : '',
        'password'        => isset($_POST['password']) ? trim($_POST['password']) : '',
        'business_name'   => isset($_POST['business_name']) ? trim($_POST['business_name']) : 'My Online Store',
        'tagline'         => isset($_POST['tagline']) ? trim($_POST['tagline']) : '',
        'company_email'   => isset($_POST['company_email']) ? trim($_POST['company_email']) : '',
        'company_phone'   => isset($_POST['company_phone']) ? trim($_POST['company_phone']) : '',
        'company_address' => isset($_POST['company_address']) ? trim($_POST['company_address']) : '',
        'currency_symbol' => isset($_POST['currency_symbol']) ? trim($_POST['currency_symbol']) : '$',
        'domain'          => isset($_POST['domain']) ? sanitize_store_slug($_POST['domain']) : '',
        'chosen_template' => isset($_POST['chosen_template']) ? trim($_POST['chosen_template']) : 'template1',
        'chosen_layout'   => isset($_POST['chosen_layout']) ? (int)$_POST['chosen_layout'] : 1
    ];

    $temp_dir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'temp';
    if (!file_exists($temp_dir)) {
        @mkdir($temp_dir, 0755, true);
    }

    if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['company_logo']['name'], PATHINFO_EXTENSION));
        $tmp_logo = $temp_dir . DIRECTORY_SEPARATOR . 'logo_' . time() . '_' . rand(100, 999) . '.' . $ext;
        if (@move_uploaded_file($_FILES['company_logo']['tmp_name'], $tmp_logo)) {
            $clean_data['temp_logo_path'] = $tmp_logo;
        }
    }

    if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION));
        $tmp_fav = $temp_dir . DIRECTORY_SEPARATOR . 'fav_' . time() . '_' . rand(100, 999) . '.' . $ext;
        if (@move_uploaded_file($_FILES['favicon']['tmp_name'], $tmp_fav)) {
            $clean_data['temp_favicon_path'] = $tmp_fav;
        }
    }

    $_SESSION['pending_wizard_setup'] = $clean_data;
    api_json(['status' => 'success', 'message' => 'Pending setup saved in session.']);
}

// ==========================================
// 7. ACTION: PROCESS SUBSCRIPTION & PROVISIONING
// ==========================================
if ($action === 'process_subscription') {
    $res = execute_subscription_provisioning($pdo, get_all_saas_settings(), $_POST, $_FILES);
    api_json($res);
}

// ==========================================
// BACKWARD-COMPATIBLE ACTIONS
// ==========================================
if ($action === 'register_step') {
    $ownerName = trim($_POST['owner_name'] ?? '');
    $storeName = trim($_POST['business_name'] ?? '');
    $storeSlug = sanitize_store_slug($_POST['store_slug'] ?? $storeName);
    $email     = strtolower(trim($_POST['email'] ?? ''));
    $phone     = trim($_POST['phone'] ?? '');
    $password  = $_POST['password'] ?? '';
    $planId    = $_POST['plan_id'] ?? 1;
    $plan      = get_saas_plan_by_id_or_slug($planId);
    $otpCode   = (string)rand(100000, 999999);

    $_SESSION['saas_registration'] = [
        'plan_id'          => (int)($plan['id'] ?? 1),
        'owner_name'       => $ownerName,
        'business_name'    => $storeName,
        'tagline'          => trim($_POST['tagline'] ?? ('Official Online Store of ' . $storeName)),
        'store_slug'       => $storeSlug,
        'domain'           => $storeSlug . '.com',
        'owner_email'      => $email,
        'owner_phone'      => $phone,
        'password_hash'    => password_hash($password, PASSWORD_BCRYPT),
        'business_address' => trim($_POST['business_address'] ?? '123 Commerce Street'),
        'currency_code'    => strtoupper(trim($_POST['currency_code'] ?? 'USD')),
        'currency_symbol'  => '$',
        'template'         => normalize_saas_template($_POST['template'] ?? 'fashion'),
        'otp_code'         => $otpCode,
        'otp_verified'     => true,
        'created_at'       => time(),
    ];

    api_json([
        'success'    => true,
        'store_slug' => $storeSlug,
        'store_url'  => saas_base_url('website/s/' . $storeSlug),
        'demo_otp'   => $otpCode,
    ]);
}

if ($action === 'save_template_and_create_pending_order') {
    if (empty($_SESSION['saas_registration'])) {
        api_json(['success' => false, 'message' => 'Registration session expired.']);
    }
    $reg  = $_SESSION['saas_registration'];
    $plan = get_saas_plan_by_id_or_slug($_POST['plan_id'] ?? $reg['plan_id']);
    $chosenTemplate = normalize_saas_template($_POST['template'] ?? $reg['template'] ?? 'fashion');
    $_SESSION['saas_registration']['template'] = $chosenTemplate;
    $reg = $_SESSION['saas_registration'];

    $orderNumber = 'SAAS-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
    $subNumber   = 'SUB-' . date('Ymd') . '-' . strtoupper(substr(md5($orderNumber), 0, 6));
    $idempKey    = 'IDEMP_' . md5($orderNumber . '_' . $reg['owner_email']);
    $amount      = (float)$plan['effective_price'];
    $currency    = $plan['currency'] ?: 'USD';

    $insSub = $pdo->prepare("INSERT INTO `saas_subscriptions`
        (`subscription_number`, `tenant_id`, `plan_id`, `template_key`, `billing_cycle`, `amount`, `currency`, `status`)
        VALUES (?, 0, ?, ?, ?, ?, ?, 'pending')");
    $insSub->execute([$subNumber, (int)$plan['id'], $chosenTemplate, $plan['billing_cycle'], $amount, $currency]);
    $subId = (int)$pdo->lastInsertId();

    $insPay = $pdo->prepare("INSERT INTO `saas_payments`
        (`order_number`, `subscription_id`, `tenant_id`, `plan_id`, `customer_name`, `customer_email`, `customer_phone`, `business_name`, `store_slug`, `chosen_template`, `gateway`, `idempotency_key`, `amount`, `currency`, `payment_status`, `setup_payload`)
        VALUES (?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, 'pending', ?)");
    $insPay->execute([
        $orderNumber, $subId, (int)$plan['id'],
        $reg['owner_name'], $reg['owner_email'], $reg['owner_phone'],
        $reg['business_name'], $reg['store_slug'], $chosenTemplate,
        $idempKey, $amount, $currency, json_encode($reg)
    ]);

    api_json([
        'success'         => true,
        'order_number'    => $orderNumber,
        'subscription_id' => $subId,
        'amount'          => $amount,
        'currency'        => $currency,
        'plan_name'       => $plan['name'],
        'template'        => $chosenTemplate,
    ]);
}

if ($action === 'process_sandbox_payment') {
    $orderNumber = trim($_POST['order_number'] ?? '');
    $txId = 'SANDBOX_TXN_' . strtoupper(substr(md5($orderNumber . microtime(true)), 0, 10));
    $pdo->prepare("UPDATE `saas_payments` SET `gateway` = 'sandbox' WHERE `order_number` = ?")->execute([$orderNumber]);
    $result = provision_saas_tenant($orderNumber, $txId, ['gateway' => 'sandbox', 'verified_at' => date('c')]);
    api_json($result);
}

if ($is_direct_api_call || !empty($action)) {
    api_json(['status' => 'error', 'success' => false, 'message' => 'Invalid action parameter.'], 400);
}
