<?php
/**
 * Reverts all database schema changes and data additions made on 2026-10-23.
 * Usage: php database/revert_today_changes.php [db_name] [--drop-all-saas]
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_OFF);

$targetDb = $argv[1] ?? 'ecommerce_single';
$dropAllSaas = in_array('--drop-all-saas', $argv, true);

$db = new mysqli('localhost', 'root', '', $targetDb);
if ($db->connect_error) {
    die("Connection failed to $targetDb: " . $db->connect_error . "\n");
}
$db->set_charset('utf8mb4');
$db->query("SET FOREIGN_KEY_CHECKS = 0");

echo "=== Reverting 2026-10-23 changes in database: `$targetDb` ===\n";

function colExists($db, $table, $col) {
    $r = $db->query("SHOW COLUMNS FROM `$table` LIKE '$col'");
    return ($r && $r->num_rows > 0);
}

function idxExists($db, $table, $idx) {
    $r = $db->query("SHOW INDEX FROM `$table` WHERE Key_name = '$idx'");
    return ($r && $r->num_rows > 0);
}

function tblExists($db, $table) {
    $r = $db->query("SHOW TABLES LIKE '$table'");
    return ($r && $r->num_rows > 0);
}

// 1. Drop tables created today (2026-10-23)
$todayTables = [
    'template_products',
    'template_categories',
    'template_hero_banners',
    'template_layout_settings',
    'saas_enquiries',
    'saas_tenant_templates',
    'saas_payments',
    'saas_subscriptions',
    'saas_templates',
];

if ($dropAllSaas) {
    $todayTables = array_merge($todayTables, [
        'saas_tenants',
        'saas_plans',
        'saas_settings',
        'saas_superadmins',
    ]);
}

foreach ($todayTables as $t) {
    if (tblExists($db, $t)) {
        $db->query("DROP TABLE `$t`");
        echo "  [OK] Dropped table `$t`\n";
    }
}

// 2. Delete rows added today (tenant_id != 1) from all business tables
$tenantTables = [
    'admins', 'roles', 'users', 'user_addresses', 'customer_groups',
    'categories', 'brands', 'products', 'attributes', 'attribute_values',
    'product_variants', 'banners', 'orders', 'order_items', 'order_returns',
    'cart_items', 'cart_saved_items', 'wishlists', 'reviews', 'coupons',
    'flash_sales', 'newsletter_subscribers', 'notifications', 'stock_adjustments',
    'settings', 'payment_gateways'
];

foreach ($tenantTables as $tbl) {
    if (tblExists($db, $tbl) && colExists($db, $tbl, 'tenant_id')) {
        $db->query("DELETE FROM `$tbl` WHERE `tenant_id` != 1");
        $affected = $db->affected_rows;
        if ($affected > 0) {
            echo "  [OK] Deleted $affected rows added today from `$tbl`\n";
        }
    }
}

// 3. Drop composite unique indexes added today and restore original single-column unique indexes
$compositeUniques = [
    'admins'                 => ['old_name' => 'email', 'new_name' => 'uniq_tenant_admin_email', 'cols' => '`email`'],
    'roles'                  => ['old_name' => 'slug', 'new_name' => 'uniq_tenant_role_slug', 'cols' => '`slug`'],
    'users'                  => ['old_name' => 'email', 'new_name' => 'uniq_tenant_user_email', 'cols' => '`email`'],
    'categories'             => ['old_name' => 'slug', 'new_name' => 'uniq_tenant_cat_slug', 'cols' => '`slug`'],
    'brands'                 => ['old_name' => 'slug', 'new_name' => 'uniq_tenant_brand_slug', 'cols' => '`slug`'],
    'products_slug'          => ['table' => 'products', 'old_name' => 'slug', 'new_name' => 'uniq_tenant_prod_slug', 'cols' => '`slug`'],
    'products_sku'           => ['table' => 'products', 'old_name' => 'sku', 'new_name' => 'uniq_tenant_prod_sku', 'cols' => '`sku`'],
    'product_variants'       => ['old_name' => 'sku', 'new_name' => 'uniq_tenant_var_sku', 'cols' => '`sku`'],
    'attributes'             => ['old_name' => 'slug', 'new_name' => 'uniq_tenant_attr_slug', 'cols' => '`slug`'],
    'orders'                 => ['old_name' => 'order_number', 'new_name' => 'uniq_tenant_order_num', 'cols' => '`order_number`'],
    'coupons'                => ['old_name' => 'code', 'new_name' => 'uniq_tenant_coupon_code', 'cols' => '`code`'],
    'flash_sales'            => ['old_name' => 'slug', 'new_name' => 'uniq_tenant_flash_slug', 'cols' => '`slug`'],
    'newsletter_subscribers' => ['old_name' => 'email', 'new_name' => 'uniq_tenant_news_email', 'cols' => '`email`'],
    'settings'               => ['old_name' => 'setting_key', 'new_name' => 'uniq_tenant_setting_key', 'cols' => '`setting_key`'],
    'payment_gateways'       => ['old_name' => 'gateway_code', 'new_name' => 'uniq_tenant_gateway_code', 'cols' => '`gateway_code`'],
    'cart_items'             => ['old_name' => 'unique_user_item', 'new_name' => 'uniq_tenant_cart_item', 'cols' => '`user_id`, `product_id`, `variant_id`'],
    'wishlists'              => ['old_name' => 'user_product_wishlist', 'new_name' => 'uniq_tenant_wishlist', 'cols' => '`user_id`, `product_id`'],
];

foreach ($compositeUniques as $key => $cfg) {
    $tbl = $cfg['table'] ?? $key;
    if (!tblExists($db, $tbl)) continue;
    if (idxExists($db, $tbl, $cfg['new_name'])) {
        $db->query("ALTER TABLE `$tbl` DROP INDEX `{$cfg['new_name']}`");
    }
    if (!idxExists($db, $tbl, $cfg['old_name'])) {
        $db->query("ALTER TABLE `$tbl` ADD UNIQUE KEY `{$cfg['old_name']}` ({$cfg['cols']})");
    }
}

// 4. Drop tenant_id column and idx_tenant_id from all 26 business tables
foreach ($tenantTables as $tbl) {
    if (!tblExists($db, $tbl)) continue;
    if (idxExists($db, $tbl, 'idx_tenant_id')) {
        $db->query("ALTER TABLE `$tbl` DROP INDEX `idx_tenant_id`");
    }
    if (colExists($db, $tbl, 'tenant_id')) {
        $db->query("ALTER TABLE `$tbl` DROP COLUMN `tenant_id`");
        echo "  [OK] Dropped tenant_id column from `$tbl`\n";
    }
}

// 5. Drop unit_label and warranty_info columns added today from products
foreach (['unit_label', 'warranty_info'] as $col) {
    if (colExists($db, 'products', $col)) {
        $db->query("ALTER TABLE `products` DROP COLUMN `$col`");
        echo "  [OK] Dropped `$col` column from `products`\n";
    }
}

// 6. If saas_plans / saas_tenants / saas_superadmins remain, revert today's changes on them
if (tblExists($db, 'saas_plans')) {
    $planCols = ['code', 'subtitle', 'discounted_price', 'currency', 'duration_days', 'trial_days', 'allowed_templates', 'product_limit', 'order_limit', 'storage_limit_mb', 'feature_permissions'];
    foreach ($planCols as $c) {
        if (colExists($db, 'saas_plans', $c)) {
            $db->query("ALTER TABLE `saas_plans` DROP COLUMN `$c`");
        }
    }
    echo "  [OK] Reverted today's columns on `saas_plans`\n";
}

if (tblExists($db, 'saas_tenants')) {
    $db->query("DELETE FROM `saas_tenants` WHERE DATE(`created_at`) = '2026-10-23'");
    if (idxExists($db, 'saas_tenants', 'idx_tenant_slug')) {
        $db->query("ALTER TABLE `saas_tenants` DROP INDEX `idx_tenant_slug`");
    }
    $tenantCols = ['tenant_code', 'slug', 'tagline', 'owner_admin_id', 'business_email', 'business_phone', 'business_address', 'currency_code', 'currency_symbol', 'website_url', 'admin_url', 'subscription_id', 'subscription_status', 'subscription_expires_at', 'license_key'];
    foreach ($tenantCols as $c) {
        if (colExists($db, 'saas_tenants', $c)) {
            $db->query("ALTER TABLE `saas_tenants` DROP COLUMN `$c`");
        }
    }
    echo "  [OK] Reverted today's columns & rows on `saas_tenants`\n";
}

if (tblExists($db, 'saas_superadmins')) {
    $db->query("DELETE FROM `saas_superadmins` WHERE `id` > 1");
}

echo "=== Completed reverting 2026-10-23 changes in `$targetDb`! ===\n";
