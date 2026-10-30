<?php
/**
 * SaaS Core Engine & Multi-Tenant Provisioning Service
 * Shared across Landing Website, Registration Wizard, Payment Callbacks, Webhooks, and Superadmin.
 */

if (session_status() === PHP_SESSION_NONE && !defined('BASEPATH')) {
    session_start();
}

function get_saas_pdo() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=localhost;dbname=ecommerce_main;charset=utf8mb4';
        $pdo = new PDO($dsn, 'root', '', [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

function get_saas_mysqli() {
    static $mysqli = null;
    if ($mysqli === null && class_exists('mysqli')) {
        if (function_exists('mysqli_report')) {
            mysqli_report(MYSQLI_REPORT_OFF);
        }
        $mysqli = new mysqli('localhost', 'root', '', 'ecommerce_main');
        $mysqli->set_charset('utf8mb4');
    }
    return $mysqli;
}

function saas_base_url($path = '') {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = $protocol . $host . '/ecommerce/';
    return $base . ltrim($path, '/');
}

function get_all_saas_settings() {
    $pdo = get_saas_pdo();
    $stmt = $pdo->query("SELECT `key_name`, `key_value` FROM `saas_settings`");
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['key_name']] = $row['key_value'];
    }
    return $settings;
}

function get_saas_setting($key, $default = '') {
    static $cache = null;
    if ($cache === null) {
        $cache = get_all_saas_settings();
    }
    return isset($cache[$key]) && $cache[$key] !== '' ? $cache[$key] : $default;
}

function set_saas_setting($key, $value) {
    $pdo = get_saas_pdo();
    $stmt = $pdo->prepare("INSERT INTO `saas_settings` (`key_name`, `key_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `key_value` = VALUES(`key_value`)");
    return $stmt->execute([$key, (string)$value]);
}

function get_active_saas_templates() {
    $pdo = get_saas_pdo();
    $stmt = $pdo->query("SELECT * FROM `saas_templates` WHERE `is_active` = 1 ORDER BY `sort_order` ASC, `id` ASC");
    $rows = $stmt->fetchAll();
    foreach ($rows as &$r) {
        $r['features_list'] = !empty($r['features']) ? (json_decode($r['features'], true) ?: []) : [];
    }
    return $rows;
}

function get_all_saas_templates() {
    $pdo = get_saas_pdo();
    $stmt = $pdo->query("SELECT * FROM `saas_templates` ORDER BY `sort_order` ASC, `id` ASC");
    $rows = $stmt->fetchAll();
    foreach ($rows as &$r) {
        $r['features_list'] = !empty($r['features']) ? (json_decode($r['features'], true) ?: []) : [];
    }
    return $rows;
}

function get_active_saas_plans() {
    $pdo = get_saas_pdo();
    $stmt = $pdo->query("SELECT * FROM `saas_plans` WHERE `is_active` = 1 ORDER BY `sort_order` ASC, `id` ASC");
    $rows = $stmt->fetchAll();
    foreach ($rows as &$r) {
        $r['features_list'] = !empty($r['features']) ? (json_decode($r['features'], true) ?: []) : [];
        $r['allowed_templates_list'] = !empty($r['allowed_templates']) ? (json_decode($r['allowed_templates'], true) ?: ['fashion', 'electronics', 'grocery']) : ['fashion', 'electronics', 'grocery'];
        $r['effective_price'] = (!empty($r['discounted_price']) && (float)$r['discounted_price'] > 0 && (float)$r['discounted_price'] < (float)$r['price'])
            ? (float)$r['discounted_price']
            : (float)$r['price'];
    }
    return $rows;
}

function normalize_saas_template($tpl) {
    $t = strtolower(trim((string)$tpl));
    if ($t === 'template2' || $t === 'electronics') return 'electronics';
    if ($t === 'template3' || $t === 'grocery') return 'grocery';
    return 'fashion';
}

function get_saas_tpl_folder_key($tpl) {
    $c = normalize_saas_template($tpl);
    if ($c === 'electronics') return 'template2';
    if ($c === 'grocery') return 'template3';
    return 'template1';
}

function get_saas_plan_by_id_or_slug($identifier) {
    $pdo = get_saas_pdo();
    $row = null;
    if (is_numeric($identifier)) {
        $stmt = $pdo->prepare("SELECT * FROM `saas_plans` WHERE `id` = ? LIMIT 1");
        $stmt->execute([(int)$identifier]);
        $row = $stmt->fetch();
    } else {
        $clean = trim((string)$identifier);
        $stmt = $pdo->prepare("SELECT * FROM `saas_plans` WHERE LOWER(`slug`) = LOWER(?) OR LOWER(`code`) = LOWER(?) LIMIT 1");
        $stmt->execute([$clean, $clean]);
        $row = $stmt->fetch();

        // Map legacy or reference project codes (STARTER, GROWTH, ENTERPRISE, FASHION, ELECTRONICS, GROCERY, SALON, SPA)
        if (!$row) {
            $upper = strtoupper($clean);
            if (in_array($upper, ['STARTER', 'STARTER_FASHION', 'FASHION', 'SALON'], true)) {
                $mappedId = 1;
            } elseif (in_array($upper, ['GROWTH', 'GROWTH_PRO', 'ELECTRONICS', 'SPA'], true)) {
                $mappedId = 2;
            } else {
                $mappedId = 3;
            }
            $stmt2 = $pdo->prepare("SELECT * FROM `saas_plans` WHERE `id` = ? LIMIT 1");
            $stmt2->execute([$mappedId]);
            $row = $stmt2->fetch();
        }
    }
    if (!$row) {
        $stmtFallback = $pdo->query("SELECT * FROM `saas_plans` WHERE `is_active` = 1 ORDER BY `sort_order` ASC, `id` ASC LIMIT 1");
        $row = $stmtFallback->fetch();
    }
    if ($row) {
        $row['features_list'] = !empty($row['features']) ? (json_decode($row['features'], true) ?: []) : [];
        $rawAllowed = !empty($row['allowed_templates']) ? (json_decode($row['allowed_templates'], true) ?: ['fashion', 'electronics', 'grocery']) : ['fashion', 'electronics', 'grocery'];
        $normAllowed = [];
        foreach ($rawAllowed as $at) {
            $c = normalize_saas_template($at);
            $normAllowed[] = $c;
            $normAllowed[] = get_saas_tpl_folder_key($c);
        }
        $row['allowed_templates_list'] = array_values(array_unique($normAllowed));
        $row['effective_price'] = (!empty($row['discounted_price']) && (float)$row['discounted_price'] > 0 && (float)$row['discounted_price'] < (float)$row['price'])
            ? (float)$row['discounted_price']
            : (float)$row['price'];
    }
    return $row;
}

function sanitize_store_slug($input) {
    $slug = strtolower(trim($input));
    $slug = preg_replace('/^https?:\/\//', '', $slug);
    $slug = preg_replace('/^www\./', '', $slug);
    $slug = preg_replace('/\.[a-z]{2,6}$/', '', $slug);
    $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

function sanitize_domain_folder($input) {
    $d = strtolower(trim((string)$input));
    $d = preg_replace('/^https?:\/\//', '', $d);
    $d = preg_replace('/^www\./', '', $d);
    $d = explode('/', $d)[0];
    $d = preg_replace('/[^a-z0-9\-\.]/', '-', $d);
    $d = preg_replace('/\.+/', '.', $d);
    $d = preg_replace('/-+/', '-', $d);
    return trim($d, '-.');
}

function is_reserved_store_slug($slug) {
    $clean = strtolower(trim((string)$slug));
    $base  = preg_replace('/\.[a-z]{2,6}$/', '', $clean);
    $reserved = [
        'admin', 'admin-template', 'website', 'superadmin', 'templates', 'database',
        'referance-project', 'assets', 'uploads', 'system', 'application', 'vendor',
        'install', 'api', 'login', 'register', 'subscribe', 'checkout', 'cart', 'shop',
        'product', 'order', 'account', 'payment', 'page', 'home', 's', 'store', 'demo'
    ];
    return in_array($clean, $reserved, true) || in_array($base, $reserved, true);
}

function rrmdir_saas($dir) {
    if (!is_dir($dir)) return;
    $items = @scandir($dir);
    if (!$items) return;
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            rrmdir_saas($path);
        } else {
            @unlink($path);
        }
    }
    @rmdir($dir);
}

/**
 * Provision a standalone physical domain folder (e.g. C:\xampp\htdocs\ecommerce\example.com)
 * with storefront at root, admin panel at /admin, unselected templates pruned,
 * configured to use the common database `ecommerce_main` with isolated tenant_id.
 * No separate database is created.
 */
function provision_tenant_standalone_folder($cleanDomain, $chosenTemplate, $tplFolderKey, $tenantId, $ownerConfig) {
    $rootDir   = __DIR__;
    $targetDir = $rootDir . DIRECTORY_SEPARATOR . $cleanDomain;
    $srcWeb    = $rootDir . DIRECTORY_SEPARATOR . 'website';
    $srcAdmin  = $rootDir . DIRECTORY_SEPARATOR . 'admin';

    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0777, true);
    }

    // 1. Copy website -> {cleanDomain} and admin -> {cleanDomain}/admin using fast robocopy on Windows
    if (stripos(PHP_OS, 'WIN') === 0) {
        $cmdWeb = sprintf('robocopy %s %s /E /XD .git node_modules /NFL /NDL /NJH /NJS /NC /NS /NP', escapeshellarg($srcWeb), escapeshellarg($targetDir));
        @shell_exec($cmdWeb);
        $targetAdmin = $targetDir . DIRECTORY_SEPARATOR . 'admin';
        $cmdAdm = sprintf('robocopy %s %s /E /XD .git node_modules /NFL /NDL /NJH /NJS /NC /NS /NP', escapeshellarg($srcAdmin), escapeshellarg($targetAdmin));
        @shell_exec($cmdAdm);
    }

    // Remove any unused templates folder if present
    $unusedTplDir = $targetDir . DIRECTORY_SEPARATOR . 'templates';
    if (is_dir($unusedTplDir)) {
        rrmdir_saas($unusedTplDir);
    }

    // 2. Prune unselected template view folders so the project folder contains only the chosen template + common inner pages
    $viewsDir = $targetDir . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'views';
    foreach (['template1', 'template2', 'template3'] as $tFolder) {
        if ($tFolder !== $tplFolderKey) {
            $prunePath = $viewsDir . DIRECTORY_SEPARATOR . $tFolder;
            if (is_dir($prunePath)) {
                rrmdir_saas($prunePath);
            }
        }
    }

    // 3. Configure database.php in both storefront and admin inside {cleanDomain} to use the COMMON database ecommerce_main
    $dbConfigFiles = [
        $targetDir . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php',
        $targetDir . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php',
    ];
    foreach ($dbConfigFiles as $cfgPath) {
        if (file_exists($cfgPath)) {
            $cfgContent = file_get_contents($cfgPath);
            $cfgContent = preg_replace("/'database'\s*=>\s*'[^']*'/", "'database' => 'ecommerce_main'", $cfgContent);
            file_put_contents($cfgPath, $cfgContent);
        }
    }

    // 4. Create tenant.php config in both storefront and admin inside {cleanDomain}
    // This explicitly binds the provisioned folder to this tenant_id in ecommerce_main
    $tenantConfigPhp = "<?php\n"
        . "defined('BASEPATH') OR exit('No direct script access allowed');\n"
        . "/* Tenant Configuration for common database ecommerce_main */\n"
        . "\$config['tenant_id']   = " . (int)$tenantId . ";\n"
        . "\$config['tenant_slug'] = " . var_export($cleanDomain, true) . ";\n"
        . "\$config['template']    = " . var_export($chosenTemplate, true) . ";\n";

    @file_put_contents($targetDir . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'tenant.php', $tenantConfigPhp);
    @file_put_contents($targetDir . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'tenant.php', $tenantConfigPhp);

    return [
        'folder_path'   => $targetDir,
        'database_name' => 'ecommerce_main',
        'website_url'   => saas_base_url($cleanDomain . '/'),
        'admin_url'     => saas_base_url($cleanDomain . '/admin/auth/login'),
    ];
}

function provision_tenant_standalone_folder_and_db($cleanDomain, $chosenTemplate, $tplFolderKey, $ownerConfig) {
    return provision_tenant_standalone_folder($cleanDomain, $chosenTemplate, $tplFolderKey, 0, $ownerConfig);
}

function saas_db() {
    return get_saas_pdo();
}

function saas_get_plan($identifier) {
    return get_saas_plan_by_id_or_slug($identifier);
}

function saas_unique_slug($input) {
    $base = sanitize_store_slug($input);
    if ($base === '' || is_reserved_store_slug($base)) {
        $base = 'store-' . rand(1000, 9999);
    }
    $pdo = get_saas_pdo();
    $slug = $base;
    $i = 1;
    while (true) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `saas_tenants` WHERE `slug` = ?");
        $stmt->execute([$slug]);
        if ((int)$stmt->fetchColumn() === 0) {
            return $slug;
        }
        $i++;
        $slug = $base . '-' . $i;
    }
}

/**
 * Provision or Finalize a Tenant Store Idempotently after Verified Payment
 */
function provision_saas_tenant($paymentOrderOrId, $transactionId = '', $gatewayResponse = []) {
    $pdo = get_saas_pdo();
    $db  = get_saas_mysqli();

    // Fetch payment record by numeric ID or order_number string
    if (is_numeric($paymentOrderOrId)) {
        $stmt = $pdo->prepare("SELECT * FROM `saas_payments` WHERE `id` = ? LIMIT 1");
        $stmt->execute([(int)$paymentOrderOrId]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM `saas_payments` WHERE `order_number` = ? LIMIT 1");
        $stmt->execute([(string)$paymentOrderOrId]);
    }
    $payment = $stmt->fetch();

    if (!$payment) {
        return ['success' => false, 'message' => 'Payment order record not found.'];
    }
    $paymentOrderNumber = $payment['order_number'];

    // Idempotency check: if already paid and tenant_id exists, return existing tenant immediately!
    if ($payment['payment_status'] === 'paid' && !empty($payment['tenant_id'])) {
        $tStmt = $pdo->prepare("SELECT * FROM `saas_tenants` WHERE `id` = ? LIMIT 1");
        $tStmt->execute([(int)$payment['tenant_id']]);
        $existingTenant = $tStmt->fetch();
        if ($existingTenant) {
            return [
                'success'        => true,
                'already_active' => true,
                'already_done'   => true,
                'slug'           => $existingTenant['slug'],
                'tenant'         => $existingTenant,
                'payment'        => $payment,
                'store_url'      => $existingTenant['website_url'],
                'admin_url'      => $existingTenant['admin_url'],
            ];
        }
    }

    $setup = !empty($payment['setup_payload']) ? (json_decode($payment['setup_payload'], true) ?: []) : [];
    $plan  = get_saas_plan_by_id_or_slug($payment['plan_id']);
    if (!$plan) {
        $plan = get_saas_plan_by_id_or_slug(1);
    }

    $chosenTemplate = normalize_saas_template($payment['chosen_template'] ?: ($setup['template'] ?? 'fashion'));
    // Enforce plan template eligibility
    if (!in_array($chosenTemplate, $plan['allowed_templates_list'], true)) {
        $chosenTemplate = normalize_saas_template($plan['allowed_templates_list'][0] ?? 'fashion');
    }
    $tplFolderKey = get_saas_tpl_folder_key($chosenTemplate);

    $rawDomainInput = $setup['domain'] ?? ($payment['store_slug'] ?: ($setup['store_slug'] ?? $payment['business_name']));
    $cleanDomain    = sanitize_domain_folder($rawDomainInput);
    if (empty($cleanDomain) || is_reserved_store_slug($cleanDomain)) {
        $cleanDomain = 'store-' . strtolower(substr(md5($paymentOrderNumber), 0, 6)) . '.com';
    }
    $storeSlug    = sanitize_store_slug($cleanDomain);
    if (empty($storeSlug)) {
        $storeSlug = $cleanDomain;
    }
    $domain       = (strpos($cleanDomain, '.') !== false) ? $cleanDomain : ($cleanDomain . '.com');
    $businessName = $payment['business_name'] ?: ($setup['business_name'] ?? 'My Store');
    $tagline      = $setup['tagline'] ?? ('Official Online Store of ' . $businessName);
    $ownerName    = $payment['customer_name'] ?: ($setup['owner_name'] ?? 'Store Owner');
    $ownerEmail   = strtolower(trim($payment['customer_email'] ?: ($setup['owner_email'] ?? '')));
    $ownerPhone   = $payment['customer_phone'] ?: ($setup['owner_phone'] ?? '');
    $passwordHash = $setup['password_hash'] ?? password_hash('admin123', PASSWORD_BCRYPT);
    $address      = $setup['business_address'] ?? '123 Business Avenue';
    $currencyCode = $setup['currency_code'] ?? ($payment['currency'] ?: 'USD');
    $currencySym  = $setup['currency_symbol'] ?? ($currencyCode === 'INR' ? '₹' : '$');
    $logoPath     = $setup['logo'] ?? '';
    $faviconPath  = $setup['favicon'] ?? '';

    if (empty($transactionId)) {
        $transactionId = $payment['transaction_id'] ?: ('TXN_' . strtoupper(substr(md5($paymentOrderNumber . time()), 0, 10)));
    }

    try {
        // Check if slug/domain already exists in saas_tenants
        $chkStmt = $pdo->prepare("SELECT * FROM `saas_tenants` WHERE `slug` = ? OR `domain` = ? LIMIT 1");
        $chkStmt->execute([$cleanDomain, $domain]);
        $existingTenant = $chkStmt->fetch();

        if ($existingTenant && (int)$existingTenant['id'] > 3 && $existingTenant['admin_email'] !== $ownerEmail) {
            $cleanDomain    = $storeSlug . '-' . rand(100, 999) . '.com';
            $domain         = $cleanDomain;
            $existingTenant = null;
        }

        // Configure URLs & Paths (All tenants use common database ecommerce_main)
        $rootDir      = __DIR__;
        $websiteUrl   = saas_base_url($cleanDomain . '/');
        $adminUrl     = saas_base_url($cleanDomain . '/admin/auth/login');
        $folderPath   = $rootDir . DIRECTORY_SEPARATOR . $cleanDomain;
        $tenantDbName = 'ecommerce_main';

        $pdo->beginTransaction();

        $durationDays = max(1, (int)($plan['duration_days'] ?? 30));
        $trialDays    = max(0, (int)($plan['trial_days'] ?? 0));
        $startDate    = date('Y-m-d H:i:s');
        $endDate      = date('Y-m-d H:i:s', strtotime("+{$durationDays} days"));
        $trialEndsAt  = $trialDays > 0 ? date('Y-m-d H:i:s', strtotime("+{$trialDays} days")) : null;
        $licenseKey   = 'LIC-' . strtoupper(substr($chosenTemplate, 0, 3)) . '-' . date('Y') . '-' . strtoupper(substr(md5($paymentOrderNumber), 0, 6));

        if ($existingTenant) {
            $tenantId   = (int)$existingTenant['id'];
            $tenantCode = $existingTenant['tenant_code'] ?: ('TNT-' . date('Y') . '-' . str_pad($tenantId, 4, '0', STR_PAD_LEFT));
            $updTenant  = $pdo->prepare("UPDATE `saas_tenants` SET
                `plan_id` = ?, `business_name` = ?, `tagline` = ?, `folder_path` = ?, `database_name` = ?, `template` = ?,
                `payment_status` = 'paid', `amount_paid` = ?, `payment_method` = ?, `transaction_id` = ?,
                `subscription_status` = 'active', `subscription_expires_at` = ?, `license_key` = ?,
                `website_url` = ?, `admin_url` = ?, `status` = 'active', `updated_at` = NOW()
                WHERE `id` = ?");
            $updTenant->execute([
                (int)$plan['id'], $businessName, $tagline, $folderPath, $tenantDbName, $chosenTemplate,
                (float)$payment['amount'], $payment['gateway'], $transactionId,
                $endDate, $licenseKey, $websiteUrl, $adminUrl, $tenantId
            ]);
        } else {
            $tenantCode = 'TNT-' . date('Y') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 5));
            $insTenant  = $pdo->prepare("INSERT INTO `saas_tenants`
                (`tenant_code`, `plan_id`, `license_key`, `business_name`, `tagline`, `slug`, `domain`, `folder_path`, `database_name`, `template`,
                 `admin_name`, `admin_email`, `admin_phone`, `business_email`, `business_phone`, `business_address`,
                 `currency_code`, `currency_symbol`, `logo`, `favicon`, `website_url`, `admin_url`,
                 `payment_status`, `amount_paid`, `payment_method`, `transaction_id`, `subscription_status`, `subscription_expires_at`, `status`, `installed_at`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'paid', ?, ?, ?, 'active', ?, 'active', NOW())");
            $insTenant->execute([
                $tenantCode, (int)$plan['id'], $licenseKey, $businessName, $tagline, $cleanDomain, $domain, $folderPath, $tenantDbName, $chosenTemplate,
                $ownerName, $ownerEmail, $ownerPhone, $ownerEmail, $ownerPhone, $address,
                $currencyCode, $currencySym, $logoPath, $faviconPath, $websiteUrl, $adminUrl,
                (float)$payment['amount'], $payment['gateway'], $transactionId, $endDate
            ]);
            $tenantId = (int)$pdo->lastInsertId();
        }

        // Create or Update Subscription Record
        $subNumber = 'SUB-' . date('Ymd') . '-' . str_pad($tenantId, 4, '0', STR_PAD_LEFT) . '-' . strtoupper(substr(md5($paymentOrderNumber), 0, 4));
        if (!empty($payment['subscription_id'])) {
            $subId = (int)$payment['subscription_id'];
            $updSub = $pdo->prepare("UPDATE `saas_subscriptions` SET
                `tenant_id` = ?, `plan_id` = ?, `template_key` = ?, `status` = 'active',
                `start_date` = ?, `end_date` = ?, `trial_ends_at` = ?, `license_key` = ?, `updated_at` = NOW()
                WHERE `id` = ?");
            $updSub->execute([$tenantId, (int)$plan['id'], $chosenTemplate, $startDate, $endDate, $trialEndsAt, $licenseKey, $subId]);
        } else {
            $insSub = $pdo->prepare("INSERT INTO `saas_subscriptions`
                (`subscription_number`, `tenant_id`, `plan_id`, `template_key`, `billing_cycle`, `amount`, `currency`, `status`, `start_date`, `end_date`, `trial_ends_at`, `license_key`)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?)");
            $insSub->execute([
                $subNumber, $tenantId, (int)$plan['id'], $chosenTemplate,
                $plan['billing_cycle'] ?? 'monthly', (float)$payment['amount'], $payment['currency'] ?: 'USD',
                $startDate, $endDate, $trialEndsAt, $licenseKey
            ]);
            $subId = (int)$pdo->lastInsertId();
        }

        // Link subscription_id back to saas_tenants
        $pdo->prepare("UPDATE `saas_tenants` SET `subscription_id` = ? WHERE `id` = ?")->execute([$subId, $tenantId]);

        // Update Payment Record
        $updPay = $pdo->prepare("UPDATE `saas_payments` SET
            `subscription_id` = ?, `tenant_id` = ?, `transaction_id` = ?,
            `payment_status` = 'paid', `gateway_response` = ?, `paid_at` = NOW(), `updated_at` = NOW()
            WHERE `id` = ?");
        $updPay->execute([$subId, $tenantId, $transactionId, json_encode($gatewayResponse), (int)$payment['id']]);

        // Save Tenant Template Configuration
        $tplCfgStmt = $pdo->prepare("INSERT INTO `saas_tenant_templates` (`tenant_id`, `template_key`, `custom_settings`)
            VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `template_key` = VALUES(`template_key`)");
        $tplCfgStmt->execute([$tenantId, $chosenTemplate, json_encode(['site_name' => $businessName, 'tagline' => $tagline])]);

        // Create Tenant Role & Owner Admin Account in ecommerce_main
        $pdo->prepare("INSERT INTO `roles` (`tenant_id`, `name`, `slug`, `description`, `is_system`)
            VALUES (?, 'Store Owner', 'super-admin', 'Full unrestricted control of tenant store', 1)
            ON DUPLICATE KEY UPDATE `name` = VALUES(`name`)")->execute([$tenantId]);
        $rStmt = $pdo->prepare("SELECT `id` FROM `roles` WHERE `tenant_id` = ? AND `slug` = 'super-admin' LIMIT 1");
        $rStmt->execute([$tenantId]);
        $roleId = (int)$rStmt->fetchColumn();

        $admStmt = $pdo->prepare("INSERT INTO `admins` (`tenant_id`, `role_id`, `name`, `email`, `password`, `phone`, `avatar`, `status`)
            VALUES (?, ?, ?, ?, ?, ?, 'default-avatar.png', 'active')
            ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `role_id` = VALUES(`role_id`), `password` = VALUES(`password`), `status` = 'active'");
        $admStmt->execute([$tenantId, $roleId, $ownerName, $ownerEmail, $passwordHash, $ownerPhone]);

        $admIdStmt = $pdo->prepare("SELECT `id` FROM `admins` WHERE `tenant_id` = ? AND `email` = ? LIMIT 1");
        $admIdStmt->execute([$tenantId, $ownerEmail]);
        $ownerAdminId = (int)$admIdStmt->fetchColumn();
        $pdo->prepare("UPDATE `saas_tenants` SET `owner_admin_id` = ? WHERE `id` = ?")->execute([$ownerAdminId, $tenantId]);

        // Seed Tenant Store Settings in ecommerce_main
        $tenantSettings = [
            ['site_name', $businessName, 'general'],
            ['site_email', $ownerEmail, 'general'],
            ['admin_email', $ownerEmail, 'general'],
            ['site_phone', $ownerPhone ?: '+1 (800) 555-0199', 'general'],
            ['support_phone', $ownerPhone ?: '+1 (800) 555-0199', 'general'],
            ['site_address', $address, 'general'],
            ['store_address', $address, 'general'],
            ['currency_code', $currencyCode, 'general'],
            ['currency', $currencyCode, 'general'],
            ['currency_symbol', $currencySym, 'general'],
            ['active_template', $chosenTemplate, 'general'],
            ['shipping_flat_rate', '10.00', 'shipping'],
            ['free_shipping_min', '99.00', 'shipping'],
            ['shipping_free_threshold', '99.00', 'shipping'],
            ['shipping_charges', '[{"name":"Standard Courier","value":10}]', 'shipping'],
            ['tax_rate_percent', '10', 'tax'],
            ['tax_rate', '10', 'tax'],
            ['tax_enabled', '1', 'tax'],
            ['tax_inclusive', '1', 'tax'],
            ['enable_stripe', '1', 'payment'],
            ['enable_razorpay', '1', 'payment'],
            ['enable_payu', '1', 'payment'],
            ['enable_cod', '1', 'payment'],
            ['active_online_gateway', 'razorpay', 'payment'],
        ];
        if (!empty($logoPath)) {
            $tenantSettings[] = ['site_logo', $logoPath, 'general'];
        }
        if (!empty($faviconPath)) {
            $tenantSettings[] = ['site_favicon', $faviconPath, 'general'];
        }
        $stStmt = $pdo->prepare("INSERT INTO `settings` (`tenant_id`, `setting_key`, `setting_value`, `setting_group`)
            VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)");
        foreach ($tenantSettings as $st) {
            $stStmt->execute([$tenantId, $st[0], $st[1], $st[2]]);
        }

        // Seed Tenant Payment Gateways (Clean default: only COD active, online gateways inactive with empty keys until configured)
        $defaultGateways = [
            [
                'gateway_code' => 'cod',
                'gateway_name' => 'Cash on Delivery (COD)',
                'is_active'    => 1,
                'environment'  => 'live',
                'credentials'  => json_encode(['instructions' => 'Pay with cash upon physical delivery of your package.'])
            ],
            [
                'gateway_code' => 'razorpay',
                'gateway_name' => 'Razorpay Gateway (UPI, Cards, NetBanking)',
                'is_active'    => 0,
                'environment'  => 'test',
                'credentials'  => json_encode(['key_id' => '', 'key_secret' => ''])
            ],
            [
                'gateway_code' => 'stripe',
                'gateway_name' => 'Stripe Payments (Credit / Debit Card)',
                'is_active'    => 0,
                'environment'  => 'test',
                'credentials'  => json_encode(['publishable_key' => '', 'secret_key' => '', 'webhook_secret' => ''])
            ],
            [
                'gateway_code' => 'payu',
                'gateway_name' => 'PayU Gateway (PayU Money / Biz)',
                'is_active'    => 0,
                'environment'  => 'test',
                'credentials'  => json_encode(['merchant_key' => '', 'merchant_salt' => '', 'test_url' => 'https://test.payu.in/_payment', 'live_url' => 'https://secure.payu.in/_payment'])
            ]
        ];
        $gwStmt = $pdo->prepare("INSERT INTO `payment_gateways` (`tenant_id`, `gateway_code`, `gateway_name`, `is_active`, `environment`, `credentials`)
            VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `gateway_name` = VALUES(`gateway_name`)");
        foreach ($defaultGateways as $gw) {
            $gwStmt->execute([$tenantId, $gw['gateway_code'], $gw['gateway_name'], $gw['is_active'], $gw['environment'], $gw['credentials']]);
        }

        $sourceTenantId = ($chosenTemplate === 'grocery') ? 2 : (($chosenTemplate === 'electronics') ? 3 : 1);

        // Seed ONLY dummy Categories and Hero Banners for a newly registered project.
        // Do NOT seed products, attributes & values, brands, product reviews, orders, customers, customer groups, coupons, or flash sales.

        // 1. Seed Categories from Superadmin template_categories (or source tenant fallback) if this tenant has 0 categories
        $catCountStmt = $pdo->prepare("SELECT COUNT(*) FROM `categories` WHERE `tenant_id` = ?");
        $catCountStmt->execute([$tenantId]);
        if ((int)$catCountStmt->fetchColumn() === 0) {
            $tplCatsStmt = $pdo->prepare("SELECT * FROM `template_categories` WHERE `template_key` = ? AND `status` = 'active' ORDER BY `sort_order` ASC");
            $tplCatsStmt->execute([$tplFolderKey]);
            $tplCats = $tplCatsStmt->fetchAll();

            $insCat = $pdo->prepare("INSERT INTO `categories` (`tenant_id`, `parent_id`, `name`, `slug`, `description`, `image`, `is_featured`, `sort_order`, `status`)
                VALUES (?, 0, ?, ?, ?, ?, 1, ?, 'active')");

            if (!empty($tplCats)) {
                foreach ($tplCats as $tc) {
                    $cSlug = ($tc['slug'] ?: sanitize_store_slug($tc['name'])) . '-t' . $tenantId;
                    $insCat->execute([$tenantId, $tc['name'], $cSlug, $tc['subtitle'] ?? '', $tc['image'], (int)$tc['sort_order']]);
                }
            } else {
                $srcCats = $pdo->prepare("SELECT * FROM `categories` WHERE `tenant_id` = ? AND `parent_id` = 0 ORDER BY `sort_order` ASC LIMIT 8");
                $srcCats->execute([$sourceTenantId]);
                foreach ($srcCats->fetchAll() as $sc) {
                    $cSlug = $sc['slug'] . '-t' . $tenantId;
                    $insCat->execute([$tenantId, $sc['name'], $cSlug, $sc['description'], $sc['image'], $sc['sort_order']]);
                }
            }
        }

        // 2. Seed Hero Banners from Superadmin template_hero_banners (or source tenant fallback) if this tenant has 0 banners
        $banCountStmt = $pdo->prepare("SELECT COUNT(*) FROM `banners` WHERE `tenant_id` = ?");
        $banCountStmt->execute([$tenantId]);
        if ((int)$banCountStmt->fetchColumn() === 0) {
            $tplBanStmt = $pdo->prepare("SELECT * FROM `template_hero_banners` WHERE `template_key` = ? AND `status` = 'active' ORDER BY `sort_order` ASC");
            $tplBanStmt->execute([$tplFolderKey]);
            $tplBans = $tplBanStmt->fetchAll();

            $insBanner = $pdo->prepare("INSERT INTO `banners` (`tenant_id`, `title`, `subtitle`, `button_text`, `button_link`, `image`, `sort_order`, `status`)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");

            if (!empty($tplBans)) {
                foreach ($tplBans as $tb) {
                    $insBanner->execute([
                        $tenantId,
                        $tb['title'],
                        $tb['badge'] ?: ($tb['description'] ?? ''),
                        $tb['button_text'] ?: 'Shop Now',
                        $tb['button_url'] ?: 'shop',
                        $tb['image'],
                        (int)$tb['sort_order']
                    ]);
                }
            } else {
                $srcBans = $pdo->prepare("SELECT * FROM `banners` WHERE `tenant_id` = ? AND `status` = 'active' ORDER BY `sort_order` ASC");
                $srcBans->execute([$sourceTenantId]);
                foreach ($srcBans->fetchAll() as $sb) {
                    $insBanner->execute([
                        $tenantId,
                        $sb['title'],
                        $sb['subtitle'] ?? '',
                        $sb['button_text'] ?: 'Shop Now',
                        $sb['button_link'] ?: 'shop',
                        $sb['image'],
                        (int)$sb['sort_order']
                    ]);
                }
            }
        }

        $pdo->commit();

        // Provision Standalone Domain Folder with common database ecommerce_main and tenant.php
        $standalone = provision_tenant_standalone_folder($cleanDomain, $chosenTemplate, $tplFolderKey, $tenantId, [
            'owner_name'      => $ownerName,
            'owner_email'     => $ownerEmail,
            'owner_phone'     => $ownerPhone,
            'password_hash'   => $passwordHash,
            'business_name'   => $businessName,
            'tagline'         => $tagline,
            'address'         => $address,
            'currency_code'   => $currencyCode,
            'currency_symbol' => $currencySym,
            'logo'            => $logoPath,
            'favicon'         => $faviconPath,
        ]);

        $tStmt = $pdo->prepare("SELECT * FROM `saas_tenants` WHERE `id` = ? LIMIT 1");
        $tStmt->execute([$tenantId]);
        $finalTenant = $tStmt->fetch();

        return [
            'success'        => true,
            'already_active' => false,
            'tenant'         => $finalTenant,
            'subscription_id'=> $subId,
            'license_key'    => $licenseKey,
            'store_url'      => $websiteUrl,
            'admin_url'      => $adminUrl,
        ];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ['success' => false, 'message' => 'Provisioning failed: ' . $e->getMessage()];
    }
}
