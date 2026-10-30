<?php
/**
 * Repeatable SaaS Multi-Tenant & Multi-Template Database Migration & Seeder
 * Target Database: ecommerce_main
 * Preserves all existing data and assigns existing store data to Default Tenant (tenant_id = 1).
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_OFF);

$db = new mysqli('localhost', 'root', '', 'ecommerce_main');
if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error . "\n");
}
$db->set_charset('utf8mb4');

function columnExists($db, $table, $column) {
    $res = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return ($res && $res->num_rows > 0);
}

function indexExists($db, $table, $indexName) {
    $res = $db->query("SHOW INDEX FROM `$table` WHERE Key_name = '$indexName'");
    return ($res && $res->num_rows > 0);
}

function addColumnIfNotExists($db, $table, $column, $definition) {
    if (!columnExists($db, $table, $column)) {
        $sql = "ALTER TABLE `$table` ADD COLUMN `$column` $definition";
        if (!$db->query($sql)) {
            echo "  [WARN] Failed to add $table.$column: " . $db->error . "\n";
        } else {
            echo "  [OK] Added $table.$column\n";
        }
    }
}

echo "=== STEP 1: Creating / Upgrading SaaS Platform Tables ===\n";

// 1. saas_templates
$db->query("CREATE TABLE IF NOT EXISTS `saas_templates` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `template_key` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(120) NOT NULL,
    `badge` VARCHAR(60) DEFAULT '',
    `tagline` VARCHAR(255) DEFAULT '',
    `description` TEXT,
    `features` TEXT,
    `preview_image` VARCHAR(255) DEFAULT '',
    `demo_url` VARCHAR(255) DEFAULT '',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// 2. Extend saas_plans
addColumnIfNotExists($db, 'saas_plans', 'code', "VARCHAR(50) DEFAULT NULL AFTER `id`");
addColumnIfNotExists($db, 'saas_plans', 'subtitle', "VARCHAR(255) DEFAULT '' AFTER `slug`");
addColumnIfNotExists($db, 'saas_plans', 'discounted_price', "DECIMAL(10,2) DEFAULT NULL AFTER `price`");
addColumnIfNotExists($db, 'saas_plans', 'currency', "VARCHAR(10) DEFAULT 'USD' AFTER `discounted_price`");
addColumnIfNotExists($db, 'saas_plans', 'duration_days', "INT NOT NULL DEFAULT 30 AFTER `billing_cycle`");
addColumnIfNotExists($db, 'saas_plans', 'trial_days', "INT NOT NULL DEFAULT 0 AFTER `duration_days`");
addColumnIfNotExists($db, 'saas_plans', 'allowed_templates', "TEXT DEFAULT NULL AFTER `template`");
addColumnIfNotExists($db, 'saas_plans', 'product_limit', "INT NOT NULL DEFAULT -1 AFTER `allowed_templates`");
addColumnIfNotExists($db, 'saas_plans', 'order_limit', "INT NOT NULL DEFAULT -1 AFTER `product_limit`");
addColumnIfNotExists($db, 'saas_plans', 'storage_limit_mb', "INT NOT NULL DEFAULT 5120 AFTER `order_limit`");
addColumnIfNotExists($db, 'saas_plans', 'feature_permissions', "TEXT DEFAULT NULL AFTER `features`");

// 3. Extend saas_tenants
$db->query("ALTER TABLE `saas_tenants` MODIFY COLUMN `status` VARCHAR(30) NOT NULL DEFAULT 'active'");
addColumnIfNotExists($db, 'saas_tenants', 'tenant_code', "VARCHAR(50) DEFAULT NULL AFTER `id`");
addColumnIfNotExists($db, 'saas_tenants', 'slug', "VARCHAR(120) DEFAULT NULL AFTER `business_name`");
addColumnIfNotExists($db, 'saas_tenants', 'tagline', "VARCHAR(255) DEFAULT '' AFTER `business_name`");
addColumnIfNotExists($db, 'saas_tenants', 'owner_admin_id', "INT UNSIGNED DEFAULT NULL AFTER `template`");
addColumnIfNotExists($db, 'saas_tenants', 'business_email', "VARCHAR(150) DEFAULT '' AFTER `admin_phone`");
addColumnIfNotExists($db, 'saas_tenants', 'business_phone', "VARCHAR(50) DEFAULT '' AFTER `business_email`");
addColumnIfNotExists($db, 'saas_tenants', 'business_address', "TEXT DEFAULT NULL AFTER `business_phone`");
addColumnIfNotExists($db, 'saas_tenants', 'currency_code', "VARCHAR(10) DEFAULT 'USD' AFTER `business_address`");
addColumnIfNotExists($db, 'saas_tenants', 'currency_symbol', "VARCHAR(10) DEFAULT '$' AFTER `currency_code`");
addColumnIfNotExists($db, 'saas_tenants', 'website_url', "VARCHAR(255) DEFAULT '' AFTER `favicon`");
addColumnIfNotExists($db, 'saas_tenants', 'admin_url', "VARCHAR(255) DEFAULT '' AFTER `website_url`");
addColumnIfNotExists($db, 'saas_tenants', 'subscription_id', "INT UNSIGNED DEFAULT NULL AFTER `plan_id`");
addColumnIfNotExists($db, 'saas_tenants', 'subscription_status', "VARCHAR(30) DEFAULT 'active' AFTER `transaction_id`");
addColumnIfNotExists($db, 'saas_tenants', 'subscription_expires_at', "DATETIME DEFAULT NULL AFTER `subscription_status`");
addColumnIfNotExists($db, 'saas_tenants', 'license_key', "VARCHAR(100) DEFAULT NULL AFTER `subscription_id`");
if (!indexExists($db, 'saas_tenants', 'idx_tenant_slug')) {
    $db->query("ALTER TABLE `saas_tenants` ADD UNIQUE KEY `idx_tenant_slug` (`slug`)");
}

// 4. saas_subscriptions
$db->query("CREATE TABLE IF NOT EXISTS `saas_subscriptions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `subscription_number` VARCHAR(60) NOT NULL UNIQUE,
    `tenant_id` INT NOT NULL,
    `plan_id` INT NOT NULL,
    `template_key` VARCHAR(50) NOT NULL DEFAULT 'fashion',
    `billing_cycle` VARCHAR(30) NOT NULL DEFAULT 'monthly',
    `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
    `status` VARCHAR(30) NOT NULL DEFAULT 'pending',
    `start_date` DATETIME DEFAULT NULL,
    `end_date` DATETIME DEFAULT NULL,
    `trial_ends_at` DATETIME DEFAULT NULL,
    `cancelled_at` DATETIME DEFAULT NULL,
    `license_key` VARCHAR(100) DEFAULT NULL,
    `notes` TEXT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_sub_tenant` (`tenant_id`),
    INDEX `idx_sub_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// 5. saas_payments
$db->query("CREATE TABLE IF NOT EXISTS `saas_payments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(60) NOT NULL UNIQUE,
    `subscription_id` INT UNSIGNED DEFAULT NULL,
    `tenant_id` INT DEFAULT NULL,
    `plan_id` INT NOT NULL,
    `customer_name` VARCHAR(150) NOT NULL,
    `customer_email` VARCHAR(150) NOT NULL,
    `customer_phone` VARCHAR(50) DEFAULT '',
    `business_name` VARCHAR(150) NOT NULL,
    `store_slug` VARCHAR(120) NOT NULL,
    `chosen_template` VARCHAR(50) NOT NULL DEFAULT 'fashion',
    `gateway` VARCHAR(50) NOT NULL DEFAULT 'stripe',
    `gateway_session_id` VARCHAR(255) DEFAULT NULL,
    `transaction_id` VARCHAR(150) DEFAULT NULL,
    `idempotency_key` VARCHAR(120) DEFAULT NULL UNIQUE,
    `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
    `payment_status` VARCHAR(30) NOT NULL DEFAULT 'pending',
    `setup_payload` LONGTEXT,
    `gateway_response` LONGTEXT,
    `paid_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_pay_tenant` (`tenant_id`),
    INDEX `idx_pay_status` (`payment_status`),
    INDEX `idx_pay_tx` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// 6. saas_tenant_templates
$db->query("CREATE TABLE IF NOT EXISTS `saas_tenant_templates` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `template_key` VARCHAR(50) NOT NULL,
    `custom_settings` LONGTEXT,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_tenant_tpl` (`tenant_id`, `template_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// 7. saas_enquiries
$db->query("CREATE TABLE IF NOT EXISTS `saas_enquiries` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(50) DEFAULT '',
    `store_type` VARCHAR(50) DEFAULT '',
    `subject` VARCHAR(255) DEFAULT '',
    `message` TEXT NOT NULL,
    `status` VARCHAR(30) NOT NULL DEFAULT 'new',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

echo "=== STEP 2: Adding tenant_id Scoping to Business Tables ===\n";

$tenantTables = [
    'admins', 'roles', 'users', 'user_addresses', 'customer_groups',
    'categories', 'brands', 'products', 'attributes', 'attribute_values',
    'product_variants', 'banners', 'orders', 'order_items', 'order_returns',
    'cart_items', 'cart_saved_items', 'wishlists', 'reviews', 'coupons',
    'flash_sales', 'newsletter_subscribers', 'notifications', 'stock_adjustments',
    'settings', 'payment_gateways'
];

foreach ($tenantTables as $tbl) {
    if (!columnExists($db, $tbl, 'tenant_id')) {
        $db->query("ALTER TABLE `$tbl` ADD COLUMN `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1 FIRST");
        echo "  [OK] Added tenant_id to $tbl\n";
    }
    if (!indexExists($db, $tbl, 'idx_tenant_id')) {
        $db->query("ALTER TABLE `$tbl` ADD INDEX `idx_tenant_id` (`tenant_id`)");
    }
}

// Product fields for Electronics (warranty_info) & Grocery (unit_label)
addColumnIfNotExists($db, 'products', 'unit_label', "VARCHAR(100) DEFAULT NULL AFTER `sku`");
addColumnIfNotExists($db, 'products', 'warranty_info', "VARCHAR(150) DEFAULT NULL AFTER `unit_label`");

echo "=== STEP 3: Converting Single-Column Unique Indexes to Tenant-Scoped Composite Unique Indexes ===\n";

$compositeUniques = [
    'admins'                 => ['old' => ['email'], 'new_name' => 'uniq_tenant_admin_email', 'cols' => '`tenant_id`, `email`'],
    'roles'                  => ['old' => ['slug'], 'new_name' => 'uniq_tenant_role_slug', 'cols' => '`tenant_id`, `slug`'],
    'users'                  => ['old' => ['email'], 'new_name' => 'uniq_tenant_user_email', 'cols' => '`tenant_id`, `email`'],
    'categories'             => ['old' => ['slug'], 'new_name' => 'uniq_tenant_cat_slug', 'cols' => '`tenant_id`, `slug`'],
    'brands'                 => ['old' => ['slug'], 'new_name' => 'uniq_tenant_brand_slug', 'cols' => '`tenant_id`, `slug`'],
    'products_slug'          => ['table' => 'products', 'old' => ['slug'], 'new_name' => 'uniq_tenant_prod_slug', 'cols' => '`tenant_id`, `slug`'],
    'products_sku'           => ['table' => 'products', 'old' => ['sku'], 'new_name' => 'uniq_tenant_prod_sku', 'cols' => '`tenant_id`, `sku`'],
    'product_variants'       => ['old' => ['sku'], 'new_name' => 'uniq_tenant_var_sku', 'cols' => '`tenant_id`, `sku`'],
    'attributes'             => ['old' => ['slug'], 'new_name' => 'uniq_tenant_attr_slug', 'cols' => '`tenant_id`, `slug`'],
    'orders'                 => ['old' => ['order_number'], 'new_name' => 'uniq_tenant_order_num', 'cols' => '`tenant_id`, `order_number`'],
    'coupons'                => ['old' => ['code'], 'new_name' => 'uniq_tenant_coupon_code', 'cols' => '`tenant_id`, `code`'],
    'flash_sales'            => ['old' => ['slug'], 'new_name' => 'uniq_tenant_flash_slug', 'cols' => '`tenant_id`, `slug`'],
    'newsletter_subscribers' => ['old' => ['email'], 'new_name' => 'uniq_tenant_news_email', 'cols' => '`tenant_id`, `email`'],
    'settings'               => ['old' => ['setting_key'], 'new_name' => 'uniq_tenant_setting_key', 'cols' => '`tenant_id`, `setting_key`'],
    'payment_gateways'       => ['old' => ['gateway_code'], 'new_name' => 'uniq_tenant_gateway_code', 'cols' => '`tenant_id`, `gateway_code`'],
    'cart_items'             => ['old' => ['unique_user_item'], 'new_name' => 'uniq_tenant_cart_item', 'cols' => '`tenant_id`, `user_id`, `product_id`, `variant_id`'],
    'wishlists'              => ['old' => ['user_product_wishlist'], 'new_name' => 'uniq_tenant_wishlist', 'cols' => '`tenant_id`, `user_id`, `product_id`'],
];

foreach ($compositeUniques as $key => $cfg) {
    $tbl = $cfg['table'] ?? $key;
    foreach ($cfg['old'] as $oldIdx) {
        if (indexExists($db, $tbl, $oldIdx)) {
            $db->query("ALTER TABLE `$tbl` DROP INDEX `$oldIdx`");
            echo "  [OK] Dropped global unique index $tbl.$oldIdx\n";
        }
    }
    if (!indexExists($db, $tbl, $cfg['new_name'])) {
        if ($db->query("ALTER TABLE `$tbl` ADD UNIQUE KEY `{$cfg['new_name']}` ({$cfg['cols']})")) {
            echo "  [OK] Added composite unique index $tbl.{$cfg['new_name']}\n";
        } else {
            echo "  [WARN] Could not add {$cfg['new_name']} on $tbl: " . $db->error . "\n";
        }
    }
}

echo "=== STEP 4: Seeding Core Templates, Plans, Settings & Superadmin ===\n";

// Seed 3 Core Storefront Templates
$templatesData = [
    [
        'template_key'  => 'fashion',
        'name'          => 'Fashion Store',
        'badge'         => 'Template 1 • Flagship',
        'tagline'       => 'Modern Apparel, Clothing, Footwear & Lifestyle Storefront',
        'description'   => 'Our signature high-converting Fashion & Lifestyle storefront featuring dynamic lookbook sliders, color/size variant swatches, quick-add cards, product comparisons, and seamless multi-gateway checkout.',
        'features'      => json_encode([
            'Apparel Color Swatches & Size Variations',
            'Interactive Quick View & Product Comparison',
            'Lookbook Hero Sliders & Brand Showcases',
            'Dynamic Filtering by Category, Brand, Color, Size & Price',
            'Customer Wishlist, Saved Cart & Order Tracking',
            'Integrated Stripe, Razorpay, PayU & COD Checkout'
        ]),
        'preview_image' => 'website/assets/images/templates/fashion.jpg',
        'demo_url'      => 'website/home?preview_tpl=fashion',
        'is_active'     => 1,
        'sort_order'    => 1
    ],
    [
        'template_key'  => 'electronics',
        'name'          => 'Electronics Store',
        'badge'         => 'Template 2 • High-Tech',
        'tagline'       => 'Gadgets, Smartphones, Laptops, Smartwatches & Audio Storefront',
        'description'   => 'Built for electronics and gadget retailers. Features high-impact widescreen tech sliders, circular department carousels, live flash-deal countdown timers, color-swatch image switching, and spotlight buy boxes.',
        'features'      => json_encode([
            'Live Today\'s Best Deals with Countdown Timer',
            'Interactive Color Variant Swatch Thumbnail Switching',
            'Tech Department Carousel & Coupon Strip',
            'Featured Gadget Spotlight with Direct Buy Box',
            'Technical Specifications & Warranty Badges',
            'Slide-out Mini Cart Drawer with Free Shipping Meter'
        ]),
        'preview_image' => 'website/assets/images/templates/electronics.jpg',
        'demo_url'      => 'website/home?preview_tpl=electronics',
        'is_active'     => 1,
        'sort_order'    => 2
    ],
    [
        'template_key'  => 'grocery',
        'name'          => 'Grocery Store',
        'badge'         => 'Template 3 • Supermarket',
        'tagline'       => 'Fresh Produce, Organic Food, Bakery, Dairy & Daily Mart Storefront',
        'description'   => 'Designed for supermarkets, organic grocers, and daily delivery marts. Includes custom SVG grocery department icons, tabbed category product grids, Deal of the Week carousels, pack/weight labels, and 1-click Add to Bag buttons.',
        'features'      => json_encode([
            'SVG Department Icon Bar (Organic, Produce, Meat, Dairy, Bakery)',
            'Instant Category Tab Switching (6-Column Supermarket Grid)',
            'Deal of the Week Carousel with Live Countdown',
            'Unit / Weight / Pack Size Badges on Product Cards',
            'One-Click Direct "Add to Bag" Card Buttons',
            'Live Social-Proof Purchase Notification Toast'
        ]),
        'preview_image' => 'website/assets/images/templates/grocery.jpg',
        'demo_url'      => 'website/home?preview_tpl=grocery',
        'is_active'     => 1,
        'sort_order'    => 3
    ]
];

foreach ($templatesData as $tpl) {
    $stmt = $db->prepare("INSERT INTO `saas_templates` (`template_key`, `name`, `badge`, `tagline`, `description`, `features`, `preview_image`, `demo_url`, `is_active`, `sort_order`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        `name` = VALUES(`name`),
        `badge` = VALUES(`badge`),
        `tagline` = VALUES(`tagline`),
        `description` = VALUES(`description`),
        `features` = VALUES(`features`),
        `preview_image` = VALUES(`preview_image`),
        `demo_url` = VALUES(`demo_url`),
        `is_active` = VALUES(`is_active`),
        `sort_order` = VALUES(`sort_order`)");
    $stmt->bind_param(
        'ssssssssii',
        $tpl['template_key'], $tpl['name'], $tpl['badge'], $tpl['tagline'],
        $tpl['description'], $tpl['features'], $tpl['preview_image'],
        $tpl['demo_url'], $tpl['is_active'], $tpl['sort_order']
    );
    $stmt->execute();
}

// Update saas_plans with full SaaS fields
$plansUpdate = [
    1 => [
        'code' => 'STARTER',
        'name' => 'Starter Store Plan',
        'slug' => 'starter',
        'template' => 'fashion',
        'subtitle' => 'Ideal for New Boutiques & Single-Niche Stores',
        'price' => 29.00,
        'discounted_price' => 29.00,
        'currency' => 'USD',
        'billing_cycle' => 'monthly',
        'duration_days' => 30,
        'trial_days' => 7,
        'badge' => 'Starter',
        'allowed_templates' => json_encode(['fashion', 'electronics', 'grocery']),
        'product_limit' => 100,
        'order_limit' => 500,
        'storage_limit_mb' => 2048,
        'features' => json_encode([
            'Choose Any 1 Storefront Template (Fashion, Electronics, or Grocery)',
            'Up to 100 Products & Variants',
            'Up to 500 Monthly Customer Orders',
            'Dedicated Tenant Admin Dashboard',
            'Stripe, Razorpay, PayU & COD Customer Payments',
            'Coupons, Reviews & Inventory Management'
        ]),
        'feature_permissions' => json_encode(['products', 'categories', 'orders', 'customers', 'coupons', 'settings']),
        'preview_image' => 'website/assets/images/templates/fashion.jpg',
        'preview_url' => 'website/home?preview_tpl=fashion',
        'is_featured' => 0,
        'is_active' => 1,
        'sort_order' => 1
    ],
    2 => [
        'code' => 'GROWTH',
        'name' => 'Growth Pro Plan',
        'slug' => 'growth',
        'template' => 'electronics',
        'subtitle' => 'Best for Growing Retailers & Multi-Category Brands',
        'price' => 59.00,
        'discounted_price' => 49.00,
        'currency' => 'USD',
        'billing_cycle' => 'monthly',
        'duration_days' => 30,
        'trial_days' => 14,
        'badge' => 'Most Popular',
        'allowed_templates' => json_encode(['fashion', 'electronics', 'grocery']),
        'product_limit' => 1000,
        'order_limit' => 5000,
        'storage_limit_mb' => 10240,
        'features' => json_encode([
            'Access All 3 Storefront Templates (Fashion, Electronics & Grocery)',
            'Switch Storefront Templates Anytime in Dashboard',
            'Up to 1,000 Products with Multi-Attribute Variants',
            'Up to 5,000 Monthly Orders & Shipment Tracking',
            'Flash Sales, Promotional Banners & Discount Coupons',
            'Full Sales, Tax & Inventory Analytics Reports'
        ]),
        'feature_permissions' => json_encode(['products', 'variants', 'categories', 'brands', 'banners', 'orders', 'customers', 'coupons', 'flash_sales', 'reports', 'settings', 'template_switch']),
        'preview_image' => 'website/assets/images/templates/electronics.jpg',
        'preview_url' => 'website/home?preview_tpl=electronics',
        'is_featured' => 1,
        'is_active' => 1,
        'sort_order' => 2
    ],
    3 => [
        'code' => 'ENTERPRISE',
        'name' => 'Enterprise Unlimited',
        'slug' => 'enterprise',
        'template' => 'grocery',
        'subtitle' => 'For High-Volume Supermarkets, Tech Chains & Enterprises',
        'price' => 99.00,
        'discounted_price' => 79.00,
        'currency' => 'USD',
        'billing_cycle' => 'yearly',
        'duration_days' => 365,
        'trial_days' => 14,
        'badge' => 'Unlimited Value',
        'allowed_templates' => json_encode(['fashion', 'electronics', 'grocery']),
        'product_limit' => -1,
        'order_limit' => -1,
        'storage_limit_mb' => 51200,
        'features' => json_encode([
            'All 3 Storefront Templates Included (Fashion, Electronics, Grocery)',
            'Unlimited Products, Categories, Brands & Variants',
            'Unlimited Customer Orders & Staff Operator Accounts',
            'Role-Based Access Control (RBAC) for Store Staff',
            'Custom Domain & Store Slug Routing Support',
            'Priority 24/7 Enterprise Onboarding & Support'
        ]),
        'feature_permissions' => json_encode(['all']),
        'preview_image' => 'website/assets/images/templates/grocery.jpg',
        'preview_url' => 'website/home?preview_tpl=grocery',
        'is_featured' => 0,
        'is_active' => 1,
        'sort_order' => 3
    ]
];

foreach ($plansUpdate as $id => $p) {
    $stmt = $db->prepare("INSERT INTO `saas_plans`
        (`id`, `code`, `name`, `slug`, `subtitle`, `template`, `allowed_templates`, `product_limit`, `order_limit`, `storage_limit_mb`, `price`, `discounted_price`, `currency`, `billing_cycle`, `duration_days`, `trial_days`, `badge`, `tagline`, `description`, `features`, `feature_permissions`, `preview_image`, `preview_url`, `is_featured`, `is_active`, `sort_order`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        `code`=VALUES(`code`), `name`=VALUES(`name`), `subtitle`=VALUES(`subtitle`), `allowed_templates`=VALUES(`allowed_templates`),
        `product_limit`=VALUES(`product_limit`), `order_limit`=VALUES(`order_limit`), `storage_limit_mb`=VALUES(`storage_limit_mb`),
        `price`=VALUES(`price`), `discounted_price`=VALUES(`discounted_price`), `currency`=VALUES(`currency`),
        `billing_cycle`=VALUES(`billing_cycle`), `duration_days`=VALUES(`duration_days`), `trial_days`=VALUES(`trial_days`),
        `badge`=VALUES(`badge`), `features`=VALUES(`features`), `feature_permissions`=VALUES(`feature_permissions`),
        `preview_image`=VALUES(`preview_image`), `preview_url`=VALUES(`preview_url`), `is_featured`=VALUES(`is_featured`), `is_active`=VALUES(`is_active`), `sort_order`=VALUES(`sort_order`)");
    $tagline = $p['subtitle'];
    $desc = $p['subtitle'];
    $stmt->bind_param(
        'issssssiiiddssiisssssssiii',
        $id, $p['code'], $p['name'], $p['slug'], $p['subtitle'], $p['template'], $p['allowed_templates'],
        $p['product_limit'], $p['order_limit'], $p['storage_limit_mb'], $p['price'], $p['discounted_price'],
        $p['currency'], $p['billing_cycle'], $p['duration_days'], $p['trial_days'], $p['badge'],
        $tagline, $desc, $p['features'], $p['feature_permissions'], $p['preview_image'], $p['preview_url'],
        $p['is_featured'], $p['is_active'], $p['sort_order']
    );
    $stmt->execute();
}
// Disable plan 4 (medical) on landing page by default since the 3 required templates are Fashion, Electronics, Grocery
$db->query("UPDATE `saas_plans` SET `is_active` = 0 WHERE `id` = 4");

// Copy Gateway Credentials from Default Tenant's payment_gateways into saas_settings for Superadmin SaaS Checkout
$stripeRow = $db->query("SELECT credentials FROM `payment_gateways` WHERE `gateway_code`='stripe' LIMIT 1")->fetch_assoc();
$rzpRow    = $db->query("SELECT credentials FROM `payment_gateways` WHERE `gateway_code`='razorpay' LIMIT 1")->fetch_assoc();
$payuRow   = $db->query("SELECT credentials FROM `payment_gateways` WHERE `gateway_code`='payu' LIMIT 1")->fetch_assoc();

$stripeCreds = $stripeRow ? json_decode($stripeRow['credentials'], true) : [];
$rzpCreds    = $rzpRow ? json_decode($rzpRow['credentials'], true) : [];
$payuCreds   = $payuRow ? json_decode($payuRow['credentials'], true) : [];

$defaultSaasSettings = [
    'site_title'               => 'Codeulas Cloud — Multi-Template SaaS E-Commerce Platform',
    'platform_name'            => 'Codeulas Cloud',
    'hero_badge'               => '🚀 Launch Your Online Store in 60 Seconds',
    'hero_heading'             => 'Build & Scale Your E-Commerce Brand With Industry-Crafted Templates',
    'hero_subheading'          => 'Choose from Fashion, Electronics, or Grocery storefronts. Select a flexible plan, connect your domain, and get a complete eCommerce storefront & inventory dashboard instantly.',
    'cta_button_text'          => 'Start Your Store Now',
    'currency_code'            => 'USD',
    'currency_symbol'          => '$',
    'contact_email'            => 'support@codeulas-saas.com',
    'contact_phone'            => '+1 (800) 555-0199',
    'contact_address'          => '123 Commerce Way, Suite 500, New York, NY 10001',
    'stripe_enabled'           => '1',
    'stripe_environment'       => 'test',
    'stripe_publishable_key'   => $stripeCreds['publishable_key'] ?? '',
    'stripe_secret_key'        => $stripeCreds['secret_key'] ?? '',
    'stripe_webhook_secret'    => $stripeCreds['webhook_secret'] ?? '',
    'razorpay_enabled'         => '1',
    'razorpay_environment'     => 'test',
    'razorpay_key_id'          => $rzpCreds['key_id'] ?? '',
    'razorpay_key_secret'      => $rzpCreds['key_secret'] ?? '',
    'payu_enabled'             => '1',
    'payu_environment'         => 'test',
    'payu_merchant_key'        => $payuCreds['merchant_key'] ?? '',
    'payu_merchant_salt'       => $payuCreds['merchant_salt'] ?? '',
    'sandbox_enabled'          => '1',
    'offline_enabled'          => '1',
    'offline_instructions'     => 'Transfer subscription fee to Account #9012883412 (IFSC: HDFC0001234) and enter your reference ID.',
    'terms_content'            => '<h3>1. SaaS Platform Terms of Service</h3><p>Welcome to Codeulas Cloud. By registering a tenant store or subscribing to any plan, you agree to comply with our acceptable use, subscription billing, and data protection policies.</p><h3>2. Subscriptions & Renewals</h3><p>Subscriptions are billed in advance according to the billing interval of your selected plan (Monthly or Yearly). Stores with expired or suspended subscriptions may be placed in read-only or maintenance mode until renewed.</p><h3>3. Tenant Data Ownership</h3><p>Each business owner retains full ownership of their product catalog, customer records, and order history. Strict multi-tenant isolation prevents unauthorized cross-tenant access.</p>',
    'privacy_content'          => '<h3>1. Privacy & Data Isolation Policy</h3><p>We respect your privacy and protect both merchant and customer data using strict server-side tenant isolation, encrypted credentials, and PCI-compliant payment gateways.</p><h3>2. Information We Collect</h3><p>We collect account registration details (name, business name, email, phone, billing address) solely to provision and maintain your SaaS store and process subscription payments.</p><h3>3. Payment Security</h3><p>We never store raw credit card numbers. All payments are processed directly through verified gateways (Stripe, Razorpay, PayU) using cryptographic signature verification.</p>',
];

foreach ($defaultSaasSettings as $k => $v) {
    $stmt = $db->prepare("INSERT INTO `saas_settings` (`key_name`, `key_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `key_value` = IF(`key_value` = '' OR `key_name` LIKE 'hero_%', VALUES(`key_value`), `key_value`)");
    $stmt->bind_param('ss', $k, $v);
    $stmt->execute();
}

// Ensure Superadmin accounts exist (both superadmin@ecommerce.com and admin@ecommerce.com)
$passHash = password_hash('admin123', PASSWORD_BCRYPT);
$db->query("INSERT INTO `saas_superadmins` (`name`, `email`, `password`) VALUES ('Super Administrator', 'admin@ecommerce.com', '$passHash') ON DUPLICATE KEY UPDATE `name` = VALUES(`name`)");
$db->query("INSERT INTO `saas_superadmins` (`name`, `email`, `password`) VALUES ('SaaS Super Admin', 'superadmin@ecommerce.com', '$passHash') ON DUPLICATE KEY UPDATE `password` = '$passHash'");

echo "=== STEP 5: Ensuring Default Tenant (ID=1, Fashion) & Existing Tenants (ID=2 Grocery, ID=3 Electronics) ===\n";

// Insert Tenant 1 (Default Fashion Store)
$stmt = $db->prepare("INSERT INTO `saas_tenants`
    (`id`, `tenant_code`, `plan_id`, `business_name`, `tagline`, `slug`, `domain`, `folder_path`, `database_name`, `template`, `owner_admin_id`, `admin_name`, `admin_email`, `admin_phone`, `business_email`, `business_phone`, `business_address`, `currency_code`, `currency_symbol`, `website_url`, `admin_url`, `payment_status`, `amount_paid`, `payment_method`, `transaction_id`, `subscription_status`, `subscription_expires_at`, `status`)
    VALUES (1, 'TNT-DEFAULT-0001', 3, 'Codeulas Fashion Store', 'Flagship Apparel & Fashion Storefront', 'fashion-store', 'fashion.com', 'C:\\xampp\\htdocs\\ecommerce\\website', 'ecommerce_main', 'fashion', 1, 'Super Administrator', 'admin@ecommerce.com', '+1 555 0192', 'contact@modave-shop.com', '+1 (800) 555-0199', '123 Commerce Way, Suite 500, New York, NY 10001', 'INR', '₹', 'http://localhost/ecommerce/website/home', 'http://localhost/ecommerce/admin/auth/login', 'paid', 99.00, 'system', 'TXN_DEFAULT_0001', 'active', '2030-12-31 23:59:59', 'active')
    ON DUPLICATE KEY UPDATE
    `tenant_code` = 'TNT-DEFAULT-0001',
    `slug` = 'fashion-store',
    `template` = 'fashion',
    `owner_admin_id` = 1,
    `website_url` = 'http://localhost/ecommerce/website/home',
    `admin_url` = 'http://localhost/ecommerce/admin/auth/login',
    `subscription_status` = 'active',
    `status` = 'active'");
$stmt->execute();

// Update Tenant 2 (Fresh Mart Grocery)
$db->query("UPDATE `saas_tenants` SET
    `tenant_code` = 'TNT-2026-0002',
    `slug` = 'fresh-mart-grocery',
    `tagline` = '100% Organic Produce & Daily Essentials',
    `plan_id` = 2,
    `template` = 'grocery',
    `business_email` = 'owner@grocery.com',
    `business_phone` = '+1 555 9876',
    `currency_code` = 'USD',
    `currency_symbol` = '$',
    `website_url` = 'http://localhost/ecommerce/website/s/fresh-mart-grocery',
    `admin_url` = 'http://localhost/ecommerce/admin/auth/login',
    `subscription_status` = 'active',
    `subscription_expires_at` = '2027-12-31 23:59:59',
    `status` = 'active'
    WHERE `id` = 2");

// Update Tenant 3 (TechZone Electronics)
$db->query("UPDATE `saas_tenants` SET
    `tenant_code` = 'TNT-2026-0003',
    `slug` = 'techzone-electronics',
    `tagline` = 'Next-Gen Smart Gadgets, Audio & Computing',
    `plan_id` = 2,
    `template` = 'electronics',
    `business_email` = 'admin@electronics.com',
    `business_phone` = '+1 555 4321',
    `currency_code` = 'USD',
    `currency_symbol` = '$',
    `website_url` = 'http://localhost/ecommerce/website/s/techzone-electronics',
    `admin_url` = 'http://localhost/ecommerce/admin/auth/login',
    `subscription_status` = 'active',
    `subscription_expires_at` = '2027-12-31 23:59:59',
    `status` = 'active'
    WHERE `id` = 3");

// Ensure subscriptions exist for Tenants 1, 2, 3
$subsSeed = [
    [1, 'SUB-2026-0001', 1, 3, 'fashion', 'yearly', 99.00, 'USD', 'active', '2026-01-01 00:00:00', '2030-12-31 23:59:59', 'LIC-ENT-0001'],
    [2, 'SUB-2026-0002', 2, 2, 'grocery', 'monthly', 49.00, 'USD', 'active', '2026-10-01 00:00:00', '2027-12-31 23:59:59', 'LIC-GRO-0002'],
    [3, 'SUB-2026-0003', 3, 2, 'electronics', 'monthly', 49.00, 'USD', 'active', '2026-10-01 00:00:00', '2027-12-31 23:59:59', 'LIC-ELT-0003'],
];
foreach ($subsSeed as $s) {
    $stmt = $db->prepare("INSERT INTO `saas_subscriptions` (`id`, `subscription_number`, `tenant_id`, `plan_id`, `template_key`, `billing_cycle`, `amount`, `currency`, `status`, `start_date`, `end_date`, `license_key`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE `status`=VALUES(`status`), `template_key`=VALUES(`template_key`)");
    $stmt->bind_param('isiissdsssss', $s[0], $s[1], $s[2], $s[3], $s[4], $s[5], $s[6], $s[7], $s[8], $s[9], $s[10], $s[11]);
    $stmt->execute();
}
$db->query("UPDATE `saas_tenants` SET `subscription_id` = `id` WHERE `id` IN (1, 2, 3)");

echo "=== STEP 6: Seeding Tenant 2 (Fresh Mart Grocery) & Tenant 3 (TechZone Electronics) Store Data ===\n";

function seedTenantFoundation($db, $tenantId, $siteName, $siteEmail, $sitePhone, $currencyCode, $currencySymbol, $adminName, $adminEmail, $adminPass) {
    // 1. Role for tenant
    $db->query("INSERT INTO `roles` (`tenant_id`, `name`, `slug`, `description`, `is_system`)
        VALUES ($tenantId, 'Store Owner', 'super-admin', 'Full control of tenant store', 1)
        ON DUPLICATE KEY UPDATE `name`=VALUES(`name`)");
    $roleRow = $db->query("SELECT id FROM `roles` WHERE `tenant_id`=$tenantId AND `slug`='super-admin' LIMIT 1")->fetch_assoc();
    $roleId = $roleRow ? (int)$roleRow['id'] : 1;

    // 2. Admin account for tenant
    $hash = password_hash($adminPass, PASSWORD_BCRYPT);
    $stmt = $db->prepare("INSERT INTO `admins` (`tenant_id`, `role_id`, `name`, `email`, `password`, `phone`, `avatar`, `status`)
        VALUES (?, ?, ?, ?, ?, ?, 'default-avatar.png', 'active')
        ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `role_id`=VALUES(`role_id`)");
    $stmt->bind_param('iissss', $tenantId, $roleId, $adminName, $adminEmail, $hash, $sitePhone);
    $stmt->execute();
    $adminRow = $db->query("SELECT id FROM `admins` WHERE `tenant_id`=$tenantId AND `email`='" . $db->real_escape_string($adminEmail) . "' LIMIT 1")->fetch_assoc();
    if ($adminRow) {
        $db->query("UPDATE `saas_tenants` SET `owner_admin_id`=" . (int)$adminRow['id'] . " WHERE `id`=$tenantId");
    }

    // 3. Settings for tenant
    $settings = [
        ['site_name', $siteName, 'general'],
        ['site_email', $siteEmail, 'general'],
        ['site_phone', $sitePhone, 'general'],
        ['site_address', '450 Market Street, Suite 200, San Francisco, CA', 'general'],
        ['currency_code', $currencyCode, 'general'],
        ['currency', $currencyCode, 'general'],
        ['currency_symbol', $currencySymbol, 'general'],
        ['shipping_flat_rate', '10.00', 'shipping'],
        ['free_shipping_min', '99.00', 'shipping'],
        ['shipping_free_threshold', '99.00', 'shipping'],
        ['tax_rate_percent', '8', 'tax'],
        ['tax_rate', '8', 'tax'],
        ['tax_enabled', '1', 'tax'],
        ['tax_inclusive', '1', 'tax'],
        ['enable_stripe', '1', 'payment'],
        ['enable_razorpay', '1', 'payment'],
        ['enable_payu', '1', 'payment'],
        ['enable_cod', '1', 'payment'],
        ['active_online_gateway', 'razorpay', 'payment'],
        ['shipping_charges', '[{"name":"Standard Delivery","value":10}]', 'shipping'],
    ];
    foreach ($settings as $st) {
        $stmt = $db->prepare("INSERT INTO `settings` (`tenant_id`, `setting_key`, `setting_value`, `setting_group`)
            VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`)");
        $stmt->bind_param('isss', $tenantId, $st[0], $st[1], $st[2]);
        $stmt->execute();
    }

    // 4. Payment Gateways for tenant (clone from tenant 1)
    $gwRes = $db->query("SELECT gateway_code, gateway_name, is_active, environment, credentials FROM `payment_gateways` WHERE `tenant_id`=1");
    while ($gw = $gwRes->fetch_assoc()) {
        $stmt = $db->prepare("INSERT INTO `payment_gateways` (`tenant_id`, `gateway_code`, `gateway_name`, `is_active`, `environment`, `credentials`)
            VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `gateway_name`=VALUES(`gateway_name`)");
        $stmt->bind_param('ississ', $tenantId, $gw['gateway_code'], $gw['gateway_name'], $gw['is_active'], $gw['environment'], $gw['credentials']);
        $stmt->execute();
    }
}

// Seed Tenant 2: Fresh Mart Grocery
seedTenantFoundation($db, 2, 'Fresh Mart Grocery', 'owner@grocery.com', '+1 555 9876', 'USD', '$', 'Store Owner', 'owner@grocery.com', 'admin123');

$groceryCats = [
    ['Organic Food', 'organic-food', '100% certified organic fruits, nuts, and pantry staples', 'grocery/grocery-banner-1.jpg', 1, 1],
    ['Vegetables', 'vegetables', 'Farm-fresh daily harvested green vegetables', 'grocery/grocery-p-3.jpg', 1, 2],
    ['Meat & Poultry', 'meat-poultry', 'Fresh farm poultry, eggs, and protein', 'grocery/grocery-p-9.jpg', 1, 3],
    ['Bakery', 'bakery', 'Artisan baked pastries, breads, and croissants', 'grocery/grocery-p-4.jpg', 1, 4],
    ['Milk & Dairy', 'milk-dairy', 'Fresh milk, plant-based milks, cheese, and butter', 'grocery/grocery-p-12.jpg', 1, 5],
    ['Chips & Snacks', 'chips-snacks', 'Healthy snacks, soups, honey, and quick bites', 'grocery/grocery-p-11.jpg', 1, 6],
];
$gCatMap = [];
foreach ($groceryCats as $c) {
    $stmt = $db->prepare("INSERT INTO `categories` (`tenant_id`, `parent_id`, `name`, `slug`, `description`, `image`, `is_featured`, `sort_order`, `status`)
        VALUES (2, 0, ?, ?, ?, ?, ?, ?, 'active')
        ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `image`=VALUES(`image`)");
    $stmt->bind_param('ssssii', $c[0], $c[1], $c[2], $c[3], $c[4], $c[5]);
    $stmt->execute();
    $row = $db->query("SELECT id FROM `categories` WHERE `tenant_id`=2 AND `slug`='{$c[1]}' LIMIT 1")->fetch_assoc();
    $gCatMap[$c[1]] = (int)$row['id'];
}

$groceryProducts = [
    ['Walnut Akhrot (Premium Quality)', 'walnut-akhrot-premium', 'GRC-001', '500 gm', $gCatMap['organic-food'], 48.00, 40.00, 'grocery/grocery-p-3.jpg', 1, 1, 1, 'Crunchy California walnuts rich in Omega-3 fatty acids, hygienically vacuum packed.'],
    ['Strawberry Fruit Narrowleaved', 'strawberry-fruit-narrowleaved', 'GRC-002', '250 gm Pack', $gCatMap['organic-food'], 15.00, 12.00, 'grocery/grocery-p-4.jpg', 1, 1, 1, 'Sweet, hand-picked organic red strawberries bursting with natural antioxidants.'],
    ['Kohlrabi (Chinese Cabbage Leaves)', 'kohlrabi-chinese-cabbage', 'GRC-003', '1 Bunch (400 gm)', $gCatMap['vegetables'], 12.00, 10.00, 'grocery/grocery-p-9.jpg', 1, 1, 0, 'Crisp, tender organic kohlrabi and cabbage greens harvested fresh this morning.'],
    ['Pain Au Chocolat Platter 4 Pieces', 'pain-au-chocolat-platter', 'GRC-004', '4 Pieces (320 gm)', $gCatMap['bakery'], 24.00, 20.00, 'grocery/grocery-p-11.jpg', 1, 1, 1, 'Flaky French-style butter pastry rolls filled with rich Belgian dark chocolate.'],
    ['Nestle Maggi Healthy Soup Vegetables', 'nestle-maggi-healthy-soup', 'GRC-005', 'Sachet 25 gm', $gCatMap['chips-snacks'], 8.00, 5.00, 'grocery/grocery-p-12.jpg', 1, 0, 1, 'Warm, comforting real-vegetable soup mix with zero added preservatives.'],
    ['Fresh Green Broccoli Crown', 'fresh-green-broccoli', 'GRC-006', '500 gm', $gCatMap['vegetables'], 18.00, 14.00, 'grocery/grocery-p-13.jpg', 1, 1, 0, 'Farm-fresh organic broccoli florets packed with vitamins C and K.'],
    ['Organic Free-Range Brown Eggs', 'organic-brown-eggs', 'GRC-007', '12 Eggs Pack', $gCatMap['meat-poultry'], 14.00, 11.00, 'grocery/grocery-p-3.jpg', 1, 1, 1, 'Pasture-raised organic brown eggs with rich golden yolks and high protein.'],
    ['Fresh Vine-Ripened Red Tomatoes', 'fresh-red-tomatoes', 'GRC-008', '1 kg', $gCatMap['vegetables'], 10.00, 8.00, 'grocery/grocery-p-4.jpg', 1, 0, 1, 'Juicy, sun-ripened heirloom red tomatoes ideal for salads, sauces, and cooking.'],
    ['Raw Wildflower Natural Honey Jar', 'natural-honey-jar', 'GRC-009', '500 gm Glass Jar', $gCatMap['organic-food'], 30.00, 25.00, 'grocery/grocery-p-9.jpg', 1, 1, 0, '100% pure, unfiltered wildflower honey sourced directly from sustainable apiaries.'],
    ['Fresh Organic Carrots Bundle', 'fresh-carrots-bundle', 'GRC-010', '750 gm Bundle', $gCatMap['vegetables'], 9.00, 6.00, 'grocery/grocery-p-11.jpg', 0, 1, 1, 'Sweet, crunchy organic orange carrots with tops, rich in beta-carotene.'],
    ['Artisan Whole Wheat Sourdough Bread', 'whole-wheat-bread', 'GRC-011', '450 gm Loaf', $gCatMap['bakery'], 10.00, 7.00, 'grocery/grocery-p-12.jpg', 1, 0, 1, 'Freshly stone-baked 100% whole grain loaf with a golden crust and soft crumb.'],
    ['Organic Almond Milk Unsweetened', 'almond-milk-unsweetened', 'GRC-012', '1 Liter Carton', $gCatMap['milk-dairy'], 19.00, 15.00, 'grocery/grocery-p-13.jpg', 1, 1, 1, 'Smooth, dairy-free organic almond milk with zero added sugar and 50% more calcium.'],
];

foreach ($groceryProducts as $gp) {
    $stmt = $db->prepare("INSERT INTO `products`
        (`tenant_id`, `category_id`, `product_type`, `title`, `slug`, `sku`, `unit_label`, `price`, `sale_price`, `stock_quantity`, `stock_status`, `short_description`, `description`, `main_image`, `is_featured`, `is_trending`, `is_new`, `rating`, `reviews_count`, `status`)
        VALUES (2, ?, 'simple', ?, ?, ?, ?, ?, ?, 85, 'in_stock', ?, ?, ?, ?, ?, ?, 4.90, 18, 'published')
        ON DUPLICATE KEY UPDATE `title`=VALUES(`title`), `price`=VALUES(`price`), `sale_price`=VALUES(`sale_price`), `unit_label`=VALUES(`unit_label`), `main_image`=VALUES(`main_image`)");
    $stmt->bind_param('issssddsssiii', $gp[4], $gp[0], $gp[1], $gp[2], $gp[3], $gp[5], $gp[6], $gp[11], $gp[11], $gp[7], $gp[8], $gp[9], $gp[10]);
    $stmt->execute();
}

// Seed Grocery Hero Banners
$groceryBanners = [
    ['Healthy Living Starts With Food', 'All Natural • Save up to 30% This Offer Only This Week', 'Shop Now', 'shop', 'grocery/grocery-banner-1.jpg', 1],
    ['Clean Organic Everyday Essentials', 'Freshly Harvested • 100% Certified Farm Produce', 'Explore Organic', 'shop', 'grocery/grocery-p-3.jpg', 2],
];
foreach ($groceryBanners as $idx => $b) {
    $sort = $b[5];
    $exists = $db->query("SELECT id FROM `banners` WHERE `tenant_id`=2 AND `sort_order`=$sort LIMIT 1")->fetch_assoc();
    if (!$exists) {
        $stmt = $db->prepare("INSERT INTO `banners` (`tenant_id`, `title`, `subtitle`, `button_text`, `button_link`, `image`, `sort_order`, `status`) VALUES (2, ?, ?, ?, ?, ?, ?, 'active')");
        $stmt->bind_param('sssssi', $b[0], $b[1], $b[2], $b[3], $b[4], $sort);
        $stmt->execute();
    }
}

// Seed Tenant 3: TechZone Electronics
seedTenantFoundation($db, 3, 'TechZone Electronics', 'admin@electronics.com', '+1 555 4321', 'USD', '$', 'Alex Rivera', 'admin@electronics.com', 'admin123');

$eltCats = [
    ['Phones', 'phones', 'Flagship 5G smartphones and mobile devices', 'electronics/category/cat-1.jpg', 1, 1],
    ['Earphones', 'earphones', 'True wireless ANC earbuds and studio headphones', 'electronics/category/cat-2.jpg', 1, 2],
    ['Cables', 'cables', 'High-speed USB-C, Thunderbolt, and MagSafe chargers', 'electronics/category/cat-3.jpg', 1, 3],
    ['Smart Watches', 'smart-watches', 'Fitness trackers and AMOLED smartwatches', 'electronics/category/cat-4.jpg', 1, 4],
    ['Laptop', 'laptop', 'Ultrabooks, gaming laptops, and workstations', 'electronics/category/cat-5.jpg', 1, 5],
    ['TV & Home', 'tv-home', 'Smart home speakers, security cameras, and displays', 'electronics/category/cat-6.jpg', 1, 6],
    ['Accessories', 'accessories', 'Power banks, instant cameras, and tech gear', 'electronics/category/cat-7.jpg', 1, 7],
];
$eCatMap = [];
foreach ($eltCats as $c) {
    $stmt = $db->prepare("INSERT INTO `categories` (`tenant_id`, `parent_id`, `name`, `slug`, `description`, `image`, `is_featured`, `sort_order`, `status`)
        VALUES (3, 0, ?, ?, ?, ?, ?, ?, 'active')
        ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `image`=VALUES(`image`)");
    $stmt->bind_param('ssssii', $c[0], $c[1], $c[2], $c[3], $c[4], $c[5]);
    $stmt->execute();
    $row = $db->query("SELECT id FROM `categories` WHERE `tenant_id`=3 AND `slug`='{$c[1]}' LIMIT 1")->fetch_assoc();
    $eCatMap[$c[1]] = (int)$row['id'];
}

$eltProducts = [
    // New Arrivals (8 items)
    ['Apple HomePod Mini - Smart Speaker', 'apple-homepod-mini-smart-speaker', 'ELT-001', '360° Spatial Audio', '1 Year Apple Warranty', $eCatMap['tv-home'], 48.00, 40.00, 'electronics/arrival-product/product-1/product-1.jpg', json_encode(['electronics/arrival-product/product-1/product-1.jpg','electronics/arrival-product/product-1/product-2.jpg']), 1, 1, 1, 'Compact smart speaker with room-filling 360-degree audio, Siri intelligence, and smart home control.'],
    ['Amazfit Bip 5 Unity Smart Watch', 'amazfit-bip-5-unity-smart-watch', 'ELT-002', '1.91" Display • 11-Day Battery', '1 Year Manufacturer Warranty', $eCatMap['smart-watches'], 150.00, 120.00, 'electronics/arrival-product/product-2/product-1.jpg', json_encode(['electronics/arrival-product/product-2/product-1.jpg','electronics/arrival-product/product-2/product-2.jpg','electronics/arrival-product/product-2/product-3.jpg']), 1, 1, 1, 'Ultra-large 1.91" high-resolution display with Bluetooth calling, 120+ sports modes, and 24/7 heart rate monitoring.'],
    ['Wireless Earbuds with Mic & Case', 'wireless-earbuds-with-mic-case', 'ELT-003', 'Bluetooth 5.3 • 32H Playtime', '1 Year Replacement Warranty', $eCatMap['earphones'], 80.00, 60.00, 'electronics/arrival-product/product-3/product-1.jpg', json_encode(['electronics/arrival-product/product-3/product-1.jpg','electronics/arrival-product/product-3/product-2.jpg','electronics/arrival-product/product-3/product-3.jpg']), 1, 0, 1, 'Crystal-clear quad-mic environmental noise cancellation with ergonomic fit and wireless charging case.'],
    ['Instax Mini 12 Instant Film Camera', 'instax-mini-12-instant-camera', 'ELT-004', 'Auto Exposure • Selfie Mirror', '2 Years Official Warranty', $eCatMap['accessories'], 250.00, 220.00, 'electronics/arrival-product/product-4/product-1.jpg', json_encode(['electronics/arrival-product/product-4/product-1.jpg','electronics/arrival-product/product-4/product-3.jpg']), 1, 1, 1, 'Capture bright, credit-card sized instant photos in 5 seconds with twist-to-turn-on lens and close-up mode.'],
    ['Smart Watch with Call Function', 'smart-watch-with-call-function', 'ELT-005', 'AMOLED • IP68 Waterproof', '1 Year Warranty', $eCatMap['smart-watches'], 48.00, 40.00, 'electronics/arrival-product/product-5/product-1.jpg', json_encode(['electronics/arrival-product/product-5/product-1.jpg','electronics/arrival-product/product-5/product-2.jpg','electronics/arrival-product/product-5/product-3.jpg']), 0, 1, 1, 'Sleek aluminum alloy smartwatch featuring HD voice calling, SpO2 blood oxygen tracking, and custom watch faces.'],
    ['MagSafe Wireless Power Bank', 'magsafe-wireless-power-bank', 'ELT-006', '10000mAh • 20W PD + 15W MagSafe', '18 Months Warranty', $eCatMap['accessories'], 95.00, 80.00, 'electronics/arrival-product/product-6/product-1.jpg', json_encode(['electronics/arrival-product/product-6/product-1.jpg','electronics/arrival-product/product-6/product-2.jpg','electronics/arrival-product/product-6/product-3.jpg']), 1, 0, 1, 'Snap-and-charge magnetic wireless battery pack with foldable kickstand and USB-C Power Delivery.'],
    ['Dual USB-C Fast Wall Charger', 'dual-usb-c-fast-wall-charger', 'ELT-007', '65W GaN Tech • Dual Port', '2 Years Warranty', $eCatMap['cables'], 25.00, 18.00, 'electronics/arrival-product/product-7/product-1.jpg', json_encode(['electronics/arrival-product/product-7/product-1.jpg','electronics/arrival-product/product-7/product-2.jpg','electronics/arrival-product/product-7/product-3.jpg']), 0, 0, 1, 'Next-gen Gallium Nitride (GaN) fast charger capable of powering a MacBook Air and iPhone simultaneously.'],
    ['360° HD Home Security Camera', '360-hd-home-security-camera', 'ELT-008', '2K QHD • AI Motion Tracking', '1 Year Warranty', $eCatMap['tv-home'], 160.00, 140.00, 'electronics/arrival-product/product-8/product-1.jpg', json_encode(['electronics/arrival-product/product-8/product-1.jpg']), 1, 1, 1, 'Pan-and-tilt 360-degree smart indoor security camera with color night vision and two-way audio.'],
    // Today's Best Deals (6 items)
    ['Laptop Model X (Core i5, 8GB RAM)', 'laptop-model-x-core-i5-8gb', 'ELT-009', 'Core i5 • 8GB RAM • 512GB SSD', '2 Years Onsite Warranty', $eCatMap['laptop'], 450.00, 400.00, 'electronics/deals-product/deals-product-1.jpg', json_encode(['electronics/deals-product/deals-product-1.jpg']), 1, 1, 0, 'Ultra-thin aluminum productivity laptop with 14-inch FHD IPS anti-glare display and all-day battery life.'],
    ['True Wireless Earbuds (ANC)', 'true-wireless-earbuds-anc', 'ELT-010', '42dB Hybrid Active Noise Canceling', '1 Year Warranty', $eCatMap['earphones'], 48.00, 40.00, 'electronics/deals-product/deals-product-2.jpg', json_encode(['electronics/deals-product/deals-product-2.jpg']), 1, 1, 0, 'Immersive studio-grade sound with adaptive active noise cancellation and transparency mode.'],
    ['Smart Watch X3 (41mm)', 'smart-watch-x3-41mm', 'ELT-011', '41mm Case • Always-On Retina', '1 Year Warranty', $eCatMap['smart-watches'], 25.00, 20.00, 'electronics/deals-product/deals-product-3.jpg', json_encode(['electronics/deals-product/deals-product-3.jpg']), 1, 1, 0, 'Precision fitness and sleep companion with ECG app, workout detection, and water resistance to 50m.'],
    ['Wireless Smart Camera (IP65 Rated)', 'wireless-smart-camera-ip65', 'ELT-012', 'IP65 Weatherproof • Solar Ready', '1 Year Warranty', $eCatMap['tv-home'], 300.00, 260.00, 'electronics/deals-product/deals-product-4.jpg', json_encode(['electronics/deals-product/deals-product-4.jpg']), 1, 1, 0, 'Wire-free outdoor smart security camera with spotlight siren, person detection, and local/cloud storage.'],
    ['Bluetooth Speaker (360° Sound)', 'bluetooth-speaker-360-sound', 'ELT-013', '24W Stereo • IPX7 Waterproof', '1 Year Warranty', $eCatMap['tv-home'], 54.00, 45.00, 'electronics/deals-product/deals-product-5.jpg', json_encode(['electronics/deals-product/deals-product-5.jpg']), 1, 1, 0, 'Portable wireless speaker delivering deep bass and 360-degree sound for outdoor adventures.'],
    ['Noise Canceling Headphones Pro', 'noise-canceling-headphones-pro', 'ELT-014', 'Over-Ear • 40H Battery • Hi-Res', '2 Years Warranty', $eCatMap['earphones'], 48.00, 40.00, 'electronics/deals-product/deals-product-6.jpg', json_encode(['electronics/deals-product/deals-product-6.jpg']), 1, 1, 0, 'Plush memory-foam over-ear wireless headphones with multi-point Bluetooth connection and fast charging.'],
];

foreach ($eltProducts as $ep) {
    $stmt = $db->prepare("INSERT INTO `products`
        (`tenant_id`, `category_id`, `product_type`, `title`, `slug`, `sku`, `unit_label`, `warranty_info`, `price`, `sale_price`, `stock_quantity`, `stock_status`, `short_description`, `description`, `main_image`, `gallery_images`, `is_featured`, `is_trending`, `is_new`, `rating`, `reviews_count`, `status`)
        VALUES (3, ?, 'simple', ?, ?, ?, ?, ?, ?, ?, 60, 'in_stock', ?, ?, ?, ?, ?, ?, ?, 4.85, 24, 'published')
        ON DUPLICATE KEY UPDATE `title`=VALUES(`title`), `price`=VALUES(`price`), `sale_price`=VALUES(`sale_price`), `unit_label`=VALUES(`unit_label`), `warranty_info`=VALUES(`warranty_info`), `main_image`=VALUES(`main_image`), `gallery_images`=VALUES(`gallery_images`)");
    $stmt->bind_param('isssssddssssiii', $ep[5], $ep[0], $ep[1], $ep[2], $ep[3], $ep[4], $ep[6], $ep[7], $ep[13], $ep[13], $ep[8], $ep[9], $ep[10], $ep[11], $ep[12]);
    $stmt->execute();
}

// Seed Electronics Hero Banners
$eltBanners = [
    ['Smart Watch With Midnight Aluminum Case', 'New Arrival • From $299', 'Shop Now', 'shop', 'electronics/slider/slide-1.jpg', 1],
    ['Next-Gen Wireless Noise Cancelling Headphones', 'Just Arrived • Studio Acoustic Precision', 'Shop Now', 'shop', 'electronics/slider/slide-2.jpg', 2],
    ['Next-Gen Smartwatch For Active Lifestyle', 'Latest Drop • Built for Endurance', 'Shop Now', 'shop', 'electronics/slider/slide-3.jpg', 3],
];
foreach ($eltBanners as $b) {
    $sort = $b[5];
    $exists = $db->query("SELECT id FROM `banners` WHERE `tenant_id`=3 AND `sort_order`=$sort LIMIT 1")->fetch_assoc();
    if (!$exists) {
        $stmt = $db->prepare("INSERT INTO `banners` (`tenant_id`, `title`, `subtitle`, `button_text`, `button_link`, `image`, `sort_order`, `status`) VALUES (3, ?, ?, ?, ?, ?, ?, 'active')");
        $stmt->bind_param('sssssi', $b[0], $b[1], $b[2], $b[3], $b[4], $sort);
        $stmt->execute();
    }
}

echo "=== Migration & Seeding Completed Successfully! ===\n";
