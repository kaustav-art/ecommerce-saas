<?php
/**
 * Complete Multi-Template Database Seeder & Tenant DB Cloner
 * Seeds Template 1 (Fashion, tenant_id=1), Template 2 (Electronics, tenant_id=3),
 * and Template 3 (Grocery, tenant_id=2) into ecommerce_main so 100% of storefront
 * and admin data comes from MySQL with zero hardcoded static arrays.
 */

function get_seeder_pdo($dbName = 'ecommerce_main') {
    $pdo = new PDO("mysql:host=localhost;dbname={$dbName};charset=utf8mb4", 'root', '', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}

function seed_all_templates_in_main_db(PDO $pdo) {
    // 1. Ensure saas_tenants 1, 2, 3 point to ecommerce_main for preview/demo
    $pdo->exec("UPDATE `saas_tenants` SET `database_name` = 'ecommerce_main', `template` = 'fashion', `status` = 'active' WHERE `id` = 1");
    $pdo->exec("UPDATE `saas_tenants` SET `database_name` = 'ecommerce_main', `template` = 'grocery', `status` = 'active' WHERE `id` = 2");
    $pdo->exec("UPDATE `saas_tenants` SET `database_name` = 'ecommerce_main', `template` = 'electronics', `status` = 'active' WHERE `id` = 3");

    // 2. Clean existing tenant 2 (Grocery) and tenant 3 (Electronics) catalog/order/user rows in ecommerce_main to re-seed cleanly
    $tenantTables = [
        'categories', 'banners', 'brands', 'products', 'attributes', 'attribute_values',
        'product_variants', 'reviews', 'users', 'user_addresses', 'cart_items',
        'orders', 'order_items', 'notifications', 'settings', 'payment_gateways'
    ];

    // Delete child rows linked to tenant 2 & 3 products/variants first
    $pIdsStmt = $pdo->query("SELECT `id` FROM `products` WHERE `tenant_id` IN (2, 3)");
    $oldProdIds = $pIdsStmt->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($oldProdIds)) {
        $inList = implode(',', array_map('intval', $oldProdIds));
        $pdo->exec("DELETE FROM `product_specifications` WHERE `product_id` IN ($inList)");
        $pdo->exec("DELETE FROM `product_attributes` WHERE `product_id` IN ($inList)");
    }
    $vIdsStmt = $pdo->query("SELECT `id` FROM `product_variants` WHERE `tenant_id` IN (2, 3)");
    $oldVarIds = $vIdsStmt->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($oldVarIds)) {
        $vInList = implode(',', array_map('intval', $oldVarIds));
        $pdo->exec("DELETE FROM `product_variant_values` WHERE `variant_id` IN ($vInList)");
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    foreach ($tenantTables as $tbl) {
        $pdo->exec("DELETE FROM `{$tbl}` WHERE `tenant_id` IN (2, 3)");
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // Also ensure Tenant 1 (Fashion) has demo cart_items, demo orders, notifications, and reviews if missing
    ensure_tenant1_fashion_complete($pdo);

    // 3. Seed Tenant 3: Template 2 (Electronics)
    seed_tenant3_electronics($pdo);

    // 4. Seed Tenant 2: Template 3 (Grocery)
    seed_tenant2_grocery($pdo);
}

function ensure_tenant1_fashion_complete(PDO $pdo) {
    $tenantId = 1;

    // Ensure demo user #1 exists for tenant 1
    $uCheck = $pdo->query("SELECT `id` FROM `users` WHERE `tenant_id` = 1 ORDER BY `id` ASC LIMIT 1")->fetchColumn();
    if (!$uCheck) {
        $stmt = $pdo->prepare("INSERT INTO `users` (`tenant_id`, `customer_group_id`, `first_name`, `last_name`, `email`, `password`, `phone`, `avatar`, `status`)
            VALUES (1, 1, 'Alex', 'Morgan', 'alex.morgan@example.com', ?, '9876543210', 'default-user.png', 'active')");
        $stmt->execute([password_hash('password123', PASSWORD_BCRYPT)]);
        $userId = (int)$pdo->lastInsertId();
    } else {
        $userId = (int)$uCheck;
    }

    // Ensure address for tenant 1 demo user
    $addrCheck = $pdo->query("SELECT COUNT(*) FROM `user_addresses` WHERE `tenant_id` = 1")->fetchColumn();
    if ((int)$addrCheck === 0) {
        $pdo->prepare("INSERT INTO `user_addresses` (`tenant_id`, `user_id`, `type`, `first_name`, `last_name`, `company`, `address_1`, `address_2`, `city`, `state`, `postcode`, `country`, `phone`, `is_default`)
            VALUES (1, ?, 'HOME', 'Alex', 'Morgan', 'Modave Studio', 'Flat 402, Emerald Heights', 'Park Street', 'Kolkata', 'West Bengal', '700016', 'India', '9876543210', 1)")
            ->execute([$userId]);
    }

    // Ensure notifications for tenant 1
    $notifCheck = $pdo->query("SELECT COUNT(*) FROM `notifications` WHERE `tenant_id` = 1")->fetchColumn();
    if ((int)$notifCheck === 0) {
        $insNotif = $pdo->prepare("INSERT INTO `notifications` (`tenant_id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES (1, ?, ?, ?, ?, ?, ?)");
        $insNotif->execute([$userId, 'Order Delivered Successfully', 'Your order #ORD-2026-88412 has been delivered. Thank you for shopping with us!', 'order', 1, '2026-09-18 16:45:00']);
        $insNotif->execute([$userId, 'Season Finale Sale is Live!', 'Enjoy up to 40% off on our new Autumn/Winter Fashion Collection.', 'promo', 0, '2026-09-20 09:00:00']);
    }

    // Ensure template_products for template1 are synced with tenant 1 products
    $pdo->exec("DELETE FROM `template_products` WHERE `template_key` = 'template1'");
    $t1Prods = $pdo->query("SELECT p.*, c.name AS category_name FROM `products` p LEFT JOIN `categories` c ON c.id = p.category_id AND c.tenant_id = 1 WHERE p.tenant_id = 1 ORDER BY p.id ASC")->fetchAll();
    $insTp1 = $pdo->prepare("INSERT INTO `template_products`
        (`template_key`, `category_name`, `name`, `slug`, `unit_label`, `price`, `sale_price`, `rating`, `reviews_count`, `image`, `hover_image`, `badge_text`, `short_desc`, `sort_order`, `status`)
        VALUES ('template1', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
    foreach ($t1Prods as $idx => $p) {
        $gal = !empty($p['gallery_images']) ? json_decode($p['gallery_images'], true) : [];
        $hover = (!empty($gal[0])) ? $gal[0] : $p['main_image'];
        $imgPath = (strpos($p['main_image'], 'assets/') === 0) ? $p['main_image'] : ('assets/images/' . ltrim($p['main_image'], '/'));
        $hoverPath = (strpos($hover, 'assets/') === 0) ? $hover : ('assets/images/' . ltrim($hover, '/'));
        $insTp1->execute([
            $p['category_name'] ?: 'Womens Fashion',
            $p['title'],
            $p['slug'],
            $p['unit_label'] ?? '',
            $p['price'],
            $p['sale_price'],
            $p['rating'] ?: 4.9,
            $p['reviews_count'] ?: 18,
            $imgPath,
            $hoverPath,
            !empty($p['sale_price']) ? 'Sale' : 'Trending',
            $p['short_description'] ?: $p['title'],
            $idx + 1
        ]);
    }
}

function seed_tenant3_electronics(PDO $pdo) {
    $tenantId = 3; // Electronics (template2)

    // 1. Settings
    $settings = [
        ['site_name', 'TechZone Electronics', 'general'],
        ['site_email', 'support@techzone.com', 'general'],
        ['admin_email', 'admin@techzone.com', 'general'],
        ['site_phone', '+1 (800) 555-0192', 'general'],
        ['support_phone', '+1 (800) 555-0192', 'general'],
        ['site_address', '500 Silicon Valley Blvd, San Jose, CA', 'general'],
        ['store_address', '500 Silicon Valley Blvd, San Jose, CA', 'general'],
        ['currency_code', 'INR', 'general'],
        ['currency', 'INR', 'general'],
        ['currency_symbol', '₹', 'general'],
        ['active_template', 'electronics', 'general'],
        ['shipping_flat_rate', '0.00', 'shipping'],
        ['free_shipping_min', '150.00', 'shipping'],
        ['shipping_free_threshold', '150.00', 'shipping'],
        ['shipping_charges', '[{"name":"Insured Priority Tech Courier","value":0}]', 'shipping'],
        ['tax_rate_percent', '10', 'tax'],
        ['tax_rate', '10', 'tax'],
        ['tax_enabled', '1', 'tax'],
        ['tax_inclusive', '1', 'tax'],
        ['enable_stripe', '1', 'payment'],
        ['enable_razorpay', '1', 'payment'],
        ['enable_cod', '1', 'payment'],
        ['active_online_gateway', 'razorpay', 'payment'],
    ];
    $insSet = $pdo->prepare("INSERT INTO `settings` (`tenant_id`, `setting_key`, `setting_value`, `setting_group`) VALUES (?, ?, ?, ?)");
    foreach ($settings as $s) {
        $insSet->execute([$tenantId, $s[0], $s[1], $s[2]]);
    }

    // 2. Payment Gateways
    $gwRows = $pdo->query("SELECT `gateway_code`, `gateway_name`, `is_active`, `environment`, `credentials` FROM `payment_gateways` WHERE `tenant_id` = 1")->fetchAll();
    $insGw = $pdo->prepare("INSERT INTO `payment_gateways` (`tenant_id`, `gateway_code`, `gateway_name`, `is_active`, `environment`, `credentials`) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($gwRows as $gw) {
        $insGw->execute([$tenantId, $gw['gateway_code'], $gw['gateway_name'], $gw['is_active'], $gw['environment'], $gw['credentials']]);
    }

    // 3. Categories
    $categories = [
        ['name' => 'Headphones & Audio', 'slug' => 'headphones-audio', 'desc' => 'Studio ANC Headphones & Hi-Res Wireless Audio', 'img' => 'assets/templates/bazaro/img/electronics/category/cat-1.jpg'],
        ['name' => 'Smart Watches',      'slug' => 'smart-watches',    'desc' => 'Titanium GPS Smartwatches & Fitness Trackers',  'img' => 'assets/templates/bazaro/img/electronics/category/cat-2.jpg'],
        ['name' => '5G Smartphones',     'slug' => 'smartphones',      'desc' => 'Flagship AI Camera 5G Smartphones',             'img' => 'assets/templates/bazaro/img/electronics/category/cat-3.jpg'],
        ['name' => 'Laptops & MacBooks', 'slug' => 'laptops-pcs',      'desc' => 'Pro Creator Laptops & Gaming Workstations',     'img' => 'assets/templates/bazaro/img/electronics/category/cat-4.jpg'],
        ['name' => 'Cameras & Drones',   'slug' => 'cameras-drones',   'desc' => '4K HDR Aerial Drones & Mirrorless Cameras',     'img' => 'assets/templates/bazaro/img/electronics/category/cat-5.jpg'],
        ['name' => 'Gaming Consoles',    'slug' => 'gaming-consoles',  'desc' => '4K 120Hz Consoles & Wireless Controllers',      'img' => 'assets/templates/bazaro/img/electronics/category/cat-6.jpg'],
    ];
    $catMap = [];
    $insCat = $pdo->prepare("INSERT INTO `categories` (`tenant_id`, `parent_id`, `name`, `slug`, `description`, `image`, `is_featured`, `sort_order`, `status`) VALUES (?, 0, ?, ?, ?, ?, 1, ?, 'active')");
    $pdo->exec("DELETE FROM `template_categories` WHERE `template_key` = 'template2'");
    $insTplCat = $pdo->prepare("INSERT INTO `template_categories` (`template_key`, `name`, `slug`, `subtitle`, `image`, `sort_order`, `status`) VALUES ('template2', ?, ?, ?, ?, ?, 'active')");
    foreach ($categories as $idx => $c) {
        $insCat->execute([$tenantId, $c['name'], $c['slug'], $c['desc'], $c['img'], $idx + 1]);
        $catId = (int)$pdo->lastInsertId();
        $catMap[$c['slug']] = $catId;
        $catMap[strtolower($c['name'])] = $catId;
        $insTplCat->execute([$c['name'], $c['slug'], ($idx + 12) . ' Products', $c['img'], $idx + 1]);
    }

    // 4. Hero Banners
    $banners = [
        [
            'title' => 'Pro Studio Wireless ANC Headphones',
            'badge' => 'Next-Gen Flagship Audio',
            'desc'  => 'Experience 30-hour battery life, adaptive spatial audio, and ultra-low latency Bluetooth 5.4.',
            'btn'   => 'Shop Audio',
            'url'   => 'shop/headphones-audio',
            'img'   => 'assets/templates/bazaro/img/electronics/slider/slide-1.jpg',
        ],
        [
            'title' => 'Ultra-Fast M3 Max Creator Laptops',
            'badge' => 'Limited Time Tech Deal',
            'desc'  => 'Built for 8K rendering, AI workflows, and extreme gaming performance with Liquid Retina XDR display.',
            'btn'   => 'Explore Laptops',
            'url'   => 'shop/laptops-pcs',
            'img'   => 'assets/templates/bazaro/img/electronics/slider/slide-2.jpg',
        ],
        [
            'title' => 'Upgrade Your Smart Ecosystem 2026',
            'badge' => 'Smart Ecosystem',
            'desc'  => 'Save up to 30% on 5G smartphones, titanium smartwatches, and 4K HDR aerial drones.',
            'btn'   => 'View Deals',
            'url'   => 'shop',
            'img'   => 'assets/templates/bazaro/img/electronics/slider/slide-3.jpg',
        ],
    ];
    $insBan = $pdo->prepare("INSERT INTO `banners` (`tenant_id`, `title`, `subtitle`, `button_text`, `button_link`, `image`, `sort_order`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
    $pdo->exec("DELETE FROM `template_hero_banners` WHERE `template_key` = 'template2'");
    $insTplBan = $pdo->prepare("INSERT INTO `template_hero_banners` (`template_key`, `badge`, `title`, `description`, `button_text`, `button_url`, `image`, `sort_order`, `status`) VALUES ('template2', ?, ?, ?, ?, ?, ?, ?, 'active')");
    foreach ($banners as $idx => $b) {
        $insBan->execute([$tenantId, $b['title'], $b['badge'], $b['btn'], $b['url'], $b['img'], $idx + 1]);
        $insTplBan->execute([$b['badge'], $b['title'], $b['desc'], $b['btn'], $b['url'], $b['img'], $idx + 1]);
    }

    // 5. Brands
    $brands = [
        ['name' => 'Sony',     'slug' => 'sony'],
        ['name' => 'Apple',    'slug' => 'apple'],
        ['name' => 'Samsung',  'slug' => 'samsung'],
        ['name' => 'DJI',      'slug' => 'dji'],
        ['name' => 'ASUS ROG', 'slug' => 'asus-rog'],
        ['name' => 'Bose',     'slug' => 'bose'],
    ];
    $brandMap = [];
    $insBrand = $pdo->prepare("INSERT INTO `brands` (`tenant_id`, `name`, `slug`, `logo`, `status`) VALUES (?, ?, ?, '', 'active')");
    foreach ($brands as $b) {
        $insBrand->execute([$tenantId, $b['name'], $b['slug']]);
        $brandMap[$b['name']] = (int)$pdo->lastInsertId();
    }

    // 6. Attributes & Attribute Values for Electronics
    $insAttr = $pdo->prepare("INSERT INTO `attributes` (`tenant_id`, `name`, `slug`, `type`) VALUES (?, ?, ?, ?)");
    $insAttr->execute([$tenantId, 'Color', 'color', 'color']);
    $colorAttrId = (int)$pdo->lastInsertId();
    $insAttr->execute([$tenantId, 'Edition', 'size', 'multiple_select']);
    $editionAttrId = (int)$pdo->lastInsertId();

    $colorValues = [
        'Matte Black'      => '#1e293b',
        'Platinum Silver'  => '#cbd5e1',
        'Natural Titanium' => '#94a3b8',
        'Space Black'      => '#0f172a',
        'Glacier White'    => '#f8fafc',
        'Titanium Gray'    => '#64748b',
    ];
    $colorValMap = [];
    $insAttrVal = $pdo->prepare("INSERT INTO `attribute_values` (`tenant_id`, `attribute_id`, `value`, `color_code`, `sort_order`) VALUES (?, ?, ?, ?, ?)");
    $sOrd = 1;
    foreach ($colorValues as $cName => $hex) {
        $insAttrVal->execute([$tenantId, $colorAttrId, $cName, $hex, $sOrd++]);
        $colorValMap[$cName] = (int)$pdo->lastInsertId();
    }
    $editionValues = ['Standard', '49mm GPS + Cellular', '1TB SSD', '2TB SSD', '2TB Edition', 'Fly More Combo', '256GB', '512GB'];
    $editionValMap = [];
    $sOrd = 1;
    foreach ($editionValues as $eName) {
        $insAttrVal->execute([$tenantId, $editionAttrId, $eName, null, $sOrd++]);
        $editionValMap[$eName] = (int)$pdo->lastInsertId();
    }

    // 7. Products (8 rich Electronics products)
    $products = [
        [
            'title'        => 'Sony WH-1000XM5 Wireless ANC Headphones',
            'slug'         => 'sony-wh-1000xm5-wireless-anc-headphones',
            'sku'          => 'TECH-AUD-301',
            'cat_slug'     => 'headphones-audio',
            'cat_name'     => 'Headphones & Audio',
            'brand'        => 'Sony',
            'price'        => 399.00,
            'sale_price'   => 349.00,
            'unit_label'   => '1 Unit',
            'warranty'     => '1 Year Official Manufacturer Warranty',
            'main_image'   => 'products/electronics/elec-p-1.jpg',
            'hover_image'  => 'products/electronics/elec-p-2.jpg',
            'gallery'      => ['products/electronics/elec-p-1.jpg', 'products/electronics/elec-p-2.jpg', 'products/electronics/elec-p-7.jpg', 'products/electronics/elec-p-8.jpg'],
            'badge'        => 'Flagship ANC',
            'rating'       => 4.90,
            'reviews_cnt'  => 64,
            'stock'        => 22,
            'short_desc'   => 'Industry-leading 8-microphone active noise cancellation with 30-hour battery life and LDAC Hi-Res Wireless Audio.',
            'desc'         => 'Engineered with dual processors (Integrated Processor V1 + HD Noise Cancelling Processor QN1) and Auto NC Optimizer for studio-grade silence, multipoint Bluetooth 5.3 connectivity, and crystal-clear beamforming voice pickup.',
            'highlights'   => [
                ['key' => 'Noise Cancellation', 'value' => 'Dual Processor 8-Mic Auto NC Optimizer'],
                ['key' => 'Battery Life', 'value' => 'Up to 30 Hours with ANC (3 min charge = 3 hrs playback)'],
                ['key' => 'Audio Codec', 'value' => 'LDAC, AAC, SBC Hi-Res Audio Wireless'],
                ['key' => 'Connectivity', 'value' => 'Bluetooth 5.3 Multipoint + 3.5mm Audio Jack'],
            ],
            'specs'        => [
                ['Driver Unit', '30mm Precision Carbon Fiber Composite Dome'],
                ['Frequency Response', '4 Hz - 40,000 Hz (JEITA)'],
                ['Weight', 'Approx. 250g Ultra-Light Soft Fit Leather'],
                ['Charging Port', 'USB Type-C PD Fast Charge'],
                ['In The Box', 'Headphones, Collapsible Carrying Case, Connection Cable, USB Cable'],
            ],
            'colors'       => ['Matte Black', 'Platinum Silver'],
            'editions'     => ['Standard'],
        ],
        [
            'title'        => 'Apple Watch Ultra 2 Titanium 49mm GPS + Cellular',
            'slug'         => 'apple-watch-ultra-2-titanium-49mm',
            'sku'          => 'TECH-WCH-302',
            'cat_slug'     => 'smart-watches',
            'cat_name'     => 'Smart Watches',
            'brand'        => 'Apple',
            'price'        => 799.00,
            'sale_price'   => 749.00,
            'unit_label'   => '49mm Watch',
            'warranty'     => '1 Year Apple Care Standard Warranty',
            'main_image'   => 'products/electronics/elec-p-2.jpg',
            'hover_image'  => 'products/electronics/elec-p-3.jpg',
            'gallery'      => ['products/electronics/elec-p-2.jpg', 'products/electronics/elec-p-3.jpg', 'products/electronics/elec-p-1.jpg'],
            'badge'        => '3000 Nits OLED',
            'rating'       => 4.90,
            'reviews_cnt'  => 48,
            'stock'        => 15,
            'short_desc'   => 'Aerospace-grade titanium case, S9 SiP chip, precision dual-frequency GPS, and up to 72 hours battery in low power mode.',
            'desc'         => 'Built for endurance athletes, outdoor adventurers, and water sports with 100m water resistance, MIL-STD 810H durability certification, and customizable Action button.',
            'highlights'   => [
                ['key' => 'Case Material', 'value' => '49mm Aerospace-Grade Titanium'],
                ['key' => 'Display', 'value' => '3000-Nit Always-On Retina LTPO OLED Sapphire Crystal'],
                ['key' => 'Processor', 'value' => 'S9 SiP with 64-bit Dual-Core & 4-Core Neural Engine'],
                ['key' => 'Water Resistance', 'value' => '100m Swimproof & EN13319 Recreational Dive up to 40m'],
            ],
            'specs'        => [
                ['GPS System', 'Precision Dual-Frequency L1 and L5 GPS, GLONASS, Galileo'],
                ['Sensors', 'Blood Oxygen, Electrical Heart Sensor (ECG), Depth Gauge, Water Temp'],
                ['Battery Life', 'Up to 36 hours normal use / Up to 72 hours Low Power Mode'],
                ['Connectivity', 'LTE and UMTS Cellular, Wi-Fi 4, Bluetooth 5.3, Ultra Wideband Gen 2'],
            ],
            'colors'       => ['Natural Titanium', 'Matte Black'],
            'editions'     => ['49mm GPS + Cellular'],
        ],
        [
            'title'        => 'MacBook Pro 16" M3 Max (16-Core CPU, 48GB RAM)',
            'slug'         => 'macbook-pro-16-m3-max',
            'sku'          => 'TECH-LAP-303',
            'cat_slug'     => 'laptops-pcs',
            'cat_name'     => 'Laptops & MacBooks',
            'brand'        => 'Apple',
            'price'        => 2199.00,
            'sale_price'   => 1899.00,
            'unit_label'   => '1 Unit',
            'warranty'     => '1 Year Official Limited Warranty',
            'main_image'   => 'products/electronics/elec-p-3.jpg',
            'hover_image'  => 'products/electronics/elec-p-4.jpg',
            'gallery'      => ['products/electronics/elec-p-3.jpg', 'products/electronics/elec-p-4.jpg', 'products/electronics/elec-p-6.jpg'],
            'badge'        => 'Pro Workstation',
            'rating'       => 5.00,
            'reviews_cnt'  => 82,
            'stock'        => 10,
            'short_desc'   => 'Liquid Retina XDR 120Hz ProMotion display, Hardware-Accelerated Ray Tracing, Thunderbolt 4, and 22-hour battery.',
            'desc'         => 'Extreme workstation performance for 8K video rendering, 3D graphics, and local AI model training with unified high-bandwidth memory architecture.',
            'highlights'   => [
                ['key' => 'Chipset', 'value' => 'Apple M3 Max (16-Core CPU, 40-Core GPU, 16-Core Neural Engine)'],
                ['key' => 'Memory', 'value' => '48GB Unified High-Bandwidth Memory (400GB/s)'],
                ['key' => 'Display', 'value' => '16.2-inch Liquid Retina XDR (3456 x 2234) 120Hz ProMotion'],
                ['key' => 'Ports', 'value' => '3x Thunderbolt 4, HDMI 2.1, SDXC Card Slot, MagSafe 3'],
            ],
            'specs'        => [
                ['Storage', '1TB / 2TB Ultra-Fast PCIe Gen4 NVMe SSD'],
                ['Audio', 'High-Fidelity Six-Speaker Sound System with Force-Cancelling Woofers'],
                ['Camera', '1080p FaceTime HD Camera with Advanced Image Signal Processor'],
                ['Wireless', 'Wi-Fi 6E (802.11ax) + Bluetooth 5.3'],
            ],
            'colors'       => ['Space Black', 'Platinum Silver'],
            'editions'     => ['1TB SSD', '2TB SSD'],
        ],
        [
            'title'        => 'PlayStation 5 Pro 2TB Console + DualSense Wireless',
            'slug'         => 'playstation-5-pro-2tb-console',
            'sku'          => 'TECH-GAM-304',
            'cat_slug'     => 'gaming-consoles',
            'cat_name'     => 'Gaming Consoles',
            'brand'        => 'Sony',
            'price'        => 699.00,
            'sale_price'   => 649.00,
            'unit_label'   => 'Console Bundle',
            'warranty'     => '1 Year Sony PlayStation Warranty',
            'main_image'   => 'products/electronics/elec-p-4.jpg',
            'hover_image'  => 'products/electronics/elec-p-5.jpg',
            'gallery'      => ['products/electronics/elec-p-4.jpg', 'products/electronics/elec-p-5.jpg', 'products/electronics/elec-p-1.jpg'],
            'badge'        => '4K 120fps Ray-Tracing',
            'rating'       => 4.90,
            'reviews_cnt'  => 55,
            'stock'        => 18,
            'short_desc'   => 'PlayStation Spectral Super Resolution (PSSR), 2TB ultra-high-speed NVMe SSD, Wi-Fi 7, and adaptive haptic triggers.',
            'desc'         => 'Next-generation console gaming with super-sharp 4K 120Hz frame rates, advanced ray tracing reflections, and DualSense immersive haptic feedback.',
            'highlights'   => [
                ['key' => 'GPU Architecture', 'value' => 'Upgraded RDNA GPU with 67% More Compute Units & PSSR AI Upscaling'],
                ['key' => 'Internal Storage', 'value' => '2TB Custom Ultra-High-Speed PCIe Gen4 SSD'],
                ['key' => 'Video Output', 'value' => '4K 120Hz, 8K Output Support, VRR & HDR10+'],
                ['key' => 'Controller', 'value' => 'DualSense Wireless Controller with Adaptive Triggers'],
            ],
            'specs'        => [
                ['CPU', 'x86-64-AMD Ryzen Zen 2 8 Cores / 16 Threads'],
                ['Memory', '16GB GDDR6 + 2GB DDR5 System Memory'],
                ['Networking', 'IEEE 802.11be (Wi-Fi 7) + Gigabit Ethernet + Bluetooth 5.1'],
                ['Audio', 'Tempest 3D AudioTech Engine'],
            ],
            'colors'       => ['Glacier White', 'Matte Black'],
            'editions'     => ['2TB Edition'],
        ],
        [
            'title'        => 'DJI Air 3S Fly More Combo 4K HDR Dual-Camera Drone',
            'slug'         => 'dji-air-3s-fly-more-combo-drone',
            'sku'          => 'TECH-CAM-305',
            'cat_slug'     => 'cameras-drones',
            'cat_name'     => 'Cameras & Drones',
            'brand'        => 'DJI',
            'price'        => 1249.00,
            'sale_price'   => 1099.00,
            'unit_label'   => 'Fly More Kit',
            'warranty'     => '1 Year DJI Care Express Warranty',
            'main_image'   => 'products/electronics/elec-p-5.jpg',
            'hover_image'  => 'products/electronics/elec-p-6.jpg',
            'gallery'      => ['products/electronics/elec-p-5.jpg', 'products/electronics/elec-p-6.jpg', 'products/electronics/elec-p-3.jpg'],
            'badge'        => '45-Min Flight',
            'rating'       => 4.80,
            'reviews_cnt'  => 39,
            'stock'        => 12,
            'short_desc'   => '1-inch CMOS primary camera + 70mm medium tele camera, omnidirectional obstacle sensing, and 20km O4 HD transmission.',
            'desc'         => 'Capture breathtaking cinematic aerial 4K 60fps HDR footage with 14 stops of dynamic range, forward-facing LiDAR night obstacle sensing, and 45-minute max flight time.',
            'highlights'   => [
                ['key' => 'Camera System', 'value' => '1-Inch CMOS 50MP Wide + 1/1.3-Inch 48MP 3x Medium Tele Camera'],
                ['key' => 'Max Flight Time', 'value' => '45 Minutes Per Battery (3x Intelligent Flight Batteries Included)'],
                ['key' => 'Video Transmission', 'value' => 'DJI O4 10-bit 1080p/60fps up to 20 km Range'],
                ['key' => 'Safety Sensing', 'value' => 'Nightscape Omnidirectional Obstacle Sensing with Forward LiDAR'],
            ],
            'specs'        => [
                ['Takeoff Weight', '724 g Foldable Airframe'],
                ['Internal Storage', '42GB Built-in + microSDXC Slot up to 512GB'],
                ['Video Resolution', '4K/60fps HDR, 4K/120fps Slow Motion, 10-bit D-Log M'],
                ['Controller', 'DJI RC 2 with 5.5-inch FHD 700-nit Built-in Screen'],
            ],
            'colors'       => ['Titanium Gray'],
            'editions'     => ['Fly More Combo'],
        ],
        [
            'title'        => 'Samsung Galaxy S24 Ultra 5G (512GB, Titanium Gray)',
            'slug'         => 'samsung-galaxy-s24-ultra-5g-512gb',
            'sku'          => 'TECH-PHN-306',
            'cat_slug'     => 'smartphones',
            'cat_name'     => '5G Smartphones',
            'brand'        => 'Samsung',
            'price'        => 1299.00,
            'sale_price'   => 1149.00,
            'unit_label'   => '512GB Unlocked',
            'warranty'     => '1 Year Samsung Official Warranty',
            'main_image'   => 'products/electronics/elec-p-6.jpg',
            'hover_image'  => 'products/electronics/elec-p-7.jpg',
            'gallery'      => ['products/electronics/elec-p-6.jpg', 'products/electronics/elec-p-7.jpg', 'products/electronics/elec-p-8.jpg'],
            'badge'        => '200MP Pro Camera',
            'rating'       => 4.90,
            'reviews_cnt'  => 71,
            'stock'        => 28,
            'short_desc'   => 'Snapdragon 8 Gen 3 for Galaxy, Dynamic AMOLED 2X QHD+ 120Hz anti-reflective armor glass, and built-in S Pen.',
            'desc'         => 'Flagship Android powerhouse featuring Galaxy AI live translation, ProVisual Engine 200MP quad telephoto system with 5x optical zoom, and Grade 2 Titanium frame.',
            'highlights'   => [
                ['key' => 'Processor', 'value' => 'Snapdragon 8 Gen 3 for Galaxy (4nm Octa-Core)'],
                ['key' => 'Display', 'value' => '6.8" QHD+ Dynamic AMOLED 2X 1-120Hz (2600 Nits Corning Gorilla Armor)'],
                ['key' => 'Pro Camera', 'value' => '200MP Main + 50MP 5x Periscope + 10MP 3x Tele + 12MP Ultra-Wide'],
                ['key' => 'Battery & Stylus', 'value' => '5000mAh with 45W Super Fast Charging + Integrated Bluetooth S Pen'],
            ],
            'specs'        => [
                ['RAM & Storage', '12GB LPDDR5X RAM + 512GB UFS 4.0 Storage'],
                ['Water & Dust Resistance', 'IP68 Rated (Up to 1.5m freshwater for 30 mins)'],
                ['Operating System', 'Android 14 with One UI 6.1 & 7 Years OS Upgrades'],
                ['Connectivity', '5G Sub6/mmWave, Wi-Fi 7, Bluetooth 5.3, UWB, NFC'],
            ],
            'colors'       => ['Titanium Gray', 'Space Black'],
            'editions'     => ['256GB', '512GB'],
        ],
        [
            'title'        => '360° Hi-Fi Smart Home Studio Speaker',
            'slug'         => '360-hi-fi-smart-home-speaker',
            'sku'          => 'TECH-AUD-307',
            'cat_slug'     => 'headphones-audio',
            'cat_name'     => 'Headphones & Audio',
            'brand'        => 'Bose',
            'price'        => 199.00,
            'sale_price'   => 159.00,
            'unit_label'   => '1 Unit',
            'warranty'     => '1 Year Manufacturer Warranty',
            'main_image'   => 'products/electronics/elec-p-7.jpg',
            'hover_image'  => 'products/electronics/elec-p-8.jpg',
            'gallery'      => ['products/electronics/elec-p-7.jpg', 'products/electronics/elec-p-8.jpg', 'products/electronics/elec-p-1.jpg'],
            'badge'        => 'Dolby Atmos',
            'rating'       => 4.80,
            'reviews_cnt'  => 34,
            'stock'        => 30,
            'short_desc'   => 'Room-filling 360-degree acoustic waveguide speaker with spatial audio, Wi-Fi AirPlay 2, and automatic room calibration.',
            'desc'         => 'Custom high-excursion woofer paired with five beamforming tweeters delivers deep bass and crisp highs anywhere in your home.',
            'highlights'   => [
                ['key' => 'Acoustic Design', 'value' => '4-Inch High-Excursion Woofer + 5x Beamforming Tweeters'],
                ['key' => 'Spatial Audio', 'value' => 'Dolby Atmos & Real-Time Acoustic Room Sensing'],
                ['key' => 'Streaming', 'value' => 'Wi-Fi 6, Apple AirPlay 2, Spotify Connect, Bluetooth 5.3'],
            ],
            'specs'        => [
                ['Dimensions', '163 mm x 142 mm'],
                ['Multi-Room', 'Stereo Pair & Multi-Room Sync Capable'],
            ],
            'colors'       => ['Matte Black', 'Glacier White'],
            'editions'     => ['Standard'],
        ],
        [
            'title'        => 'True Wireless ANC Earbuds Pro 2 (USB-C MagSafe)',
            'slug'         => 'true-wireless-anc-earbuds-pro-2',
            'sku'          => 'TECH-AUD-308',
            'cat_slug'     => 'headphones-audio',
            'cat_name'     => 'Headphones & Audio',
            'brand'        => 'Apple',
            'price'        => 249.00,
            'sale_price'   => 199.00,
            'unit_label'   => '1 Pair',
            'warranty'     => '1 Year Apple Warranty',
            'main_image'   => 'products/electronics/elec-p-8.jpg',
            'hover_image'  => 'products/electronics/elec-p-1.jpg',
            'gallery'      => ['products/electronics/elec-p-8.jpg', 'products/electronics/elec-p-1.jpg', 'products/electronics/elec-p-2.jpg'],
            'badge'        => '2x Active ANC',
            'rating'       => 4.90,
            'reviews_cnt'  => 95,
            'stock'        => 40,
            'short_desc'   => 'H2 audio chip with Adaptive Audio, Conversation Awareness, Personalized Spatial Audio, and IP54 dust/water resistance.',
            'desc'         => 'Up to 2x more Active Noise Cancellation with Adaptive Transparency, touch volume control on stem, and Precision Finding speaker in MagSafe charging case.',
            'highlights'   => [
                ['key' => 'Audio Chip', 'value' => 'Custom H2 Headphone Chip + Low-Distortion Driver'],
                ['key' => 'Battery', 'value' => '6 Hours Listening (30 Hours Total with MagSafe Case)'],
                ['key' => 'Durability', 'value' => 'IP54 Dust, Sweat, and Water Resistant (Earbuds & Case)'],
            ],
            'specs'        => [
                ['Charging', 'USB-C, MagSafe, Apple Watch Charger, or Qi Wireless'],
                ['Bluetooth', 'Bluetooth 5.3 Ultra-Low Latency'],
            ],
            'colors'       => ['Glacier White'],
            'editions'     => ['Standard'],
        ],
    ];

    $insProd = $pdo->prepare("INSERT INTO `products`
        (`tenant_id`, `category_id`, `brand_id`, `product_type`, `title`, `slug`, `sku`, `unit_label`, `warranty_info`, `price`, `sale_price`, `tax_rate`, `stock_quantity`, `low_stock_threshold`, `max_purchase_quantity`, `stock_status`, `short_description`, `description`, `highlights`, `main_image`, `gallery_images`, `is_featured`, `is_trending`, `is_new`, `is_cod_allowed`, `rating`, `reviews_count`, `status`)
        VALUES (?, ?, ?, 'variable', ?, ?, ?, ?, ?, ?, ?, 10.00, ?, 5, 5, 'in_stock', ?, ?, ?, ?, ?, 1, 1, 1, 1, ?, ?, 'published')");

    $insSpec = $pdo->prepare("INSERT INTO `product_specifications` (`product_id`, `spec_name`, `spec_value`, `sort_order`) VALUES (?, ?, ?, ?)");
    $insProdAttr = $pdo->prepare("INSERT INTO `product_attributes` (`product_id`, `attribute_id`) VALUES (?, ?)");
    $insVar = $pdo->prepare("INSERT INTO `product_variants` (`tenant_id`, `product_id`, `title`, `sku`, `price`, `sale_price`, `stock_quantity`, `max_purchase_quantity`, `stock_status`, `image`, `gallery_images`) VALUES (?, ?, ?, ?, ?, ?, ?, 5, 'in_stock', ?, ?)");
    $insVarVal = $pdo->prepare("INSERT INTO `product_variant_values` (`variant_id`, `attribute_id`, `attribute_value_id`) VALUES (?, ?, ?)");
    $insRev = $pdo->prepare("INSERT INTO `reviews` (`tenant_id`, `product_id`, `user_id`, `order_number`, `customer_name`, `customer_email`, `rating`, `title`, `review`, `status`, `created_at`) VALUES (?, ?, 1, 'ORD-2026-88412', ?, ?, ?, ?, ?, 'approved', ?)");

    $pdo->exec("DELETE FROM `template_products` WHERE `template_key` = 'template2'");
    $insTplProd = $pdo->prepare("INSERT INTO `template_products`
        (`template_key`, `category_name`, `name`, `slug`, `unit_label`, `price`, `sale_price`, `rating`, `reviews_count`, `image`, `hover_image`, `badge_text`, `short_desc`, `sort_order`, `status`)
        VALUES ('template2', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");

    $prodIdMap = [];
    foreach ($products as $idx => $p) {
        $catId = $catMap[$p['cat_slug']] ?? reset($catMap);
        $brandId = $brandMap[$p['brand']] ?? reset($brandMap);
        $galJson = json_encode($p['gallery']);
        $hlJson = json_encode($p['highlights']);

        $insProd->execute([
            $tenantId, $catId, $brandId, $p['title'], $p['slug'], $p['sku'],
            $p['unit_label'], $p['warranty'], $p['price'], $p['sale_price'],
            $p['stock'], $p['short_desc'], $p['desc'], $hlJson,
            $p['main_image'], $galJson, $p['rating'], $p['reviews_cnt']
        ]);
        $prodId = (int)$pdo->lastInsertId();
        $prodIdMap[$p['sku']] = $prodId;

        // Specifications
        foreach ($p['specs'] as $sIdx => $sp) {
            $insSpec->execute([$prodId, $sp[0], $sp[1], $sIdx + 1]);
        }

        // Product attributes & variants
        $insProdAttr->execute([$prodId, $colorAttrId]);
        $insProdAttr->execute([$prodId, $editionAttrId]);
        $vIdx = 1;
        foreach ($p['colors'] as $cName) {
            foreach ($p['editions'] as $eName) {
                $vTitle = $p['title'] . ' - ' . $cName . ' / ' . $eName;
                $vSku = $p['sku'] . '-V' . $vIdx++;
                $insVar->execute([$tenantId, $prodId, $vTitle, $vSku, $p['price'], $p['sale_price'], $p['stock'], $p['main_image'], $galJson]);
                $varId = (int)$pdo->lastInsertId();
                if (isset($colorValMap[$cName])) {
                    $insVarVal->execute([$varId, $colorAttrId, $colorValMap[$cName]]);
                }
                if (isset($editionValMap[$eName])) {
                    $insVarVal->execute([$varId, $editionAttrId, $editionValMap[$eName]]);
                }
            }
        }

        // Reviews
        $insRev->execute([$tenantId, $prodId, 'Alex Morgan', 'alex.morgan@example.com', 5, 'Outstanding flagship build quality & performance!', 'Genuine sealed product with instant warranty registration. Battery life and speed exceeded expectations.', '2026-10-18 14:20:00']);
        $insRev->execute([$tenantId, $prodId, 'Rohan Verma', 'rohan.v@example.com', 5, 'Best tech upgrade of 2026', 'Super fast insured courier delivery and rock-solid performance right out of the box.', '2026-10-20 11:05:00']);

        // Template customizer row
        $insTplProd->execute([
            $p['cat_name'], $p['title'], $p['slug'], $p['unit_label'],
            $p['price'], $p['sale_price'], $p['rating'], $p['reviews_cnt'],
            'assets/images/' . $p['main_image'], 'assets/images/' . $p['hover_image'],
            $p['badge'], $p['short_desc'], $idx + 1
        ]);
    }

    // 8. Demo Customer, Address, Cart Items, Orders, Notifications for Electronics
    $insUser = $pdo->prepare("INSERT INTO `users` (`tenant_id`, `customer_group_id`, `first_name`, `last_name`, `email`, `password`, `phone`, `avatar`, `status`) VALUES (?, 1, 'Alex', 'Morgan', 'alex.morgan@example.com', ?, '9876543210', 'default-user.png', 'active')");
    $insUser->execute([$tenantId, password_hash('password123', PASSWORD_BCRYPT)]);
    $userId = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `user_addresses` (`tenant_id`, `user_id`, `type`, `first_name`, `last_name`, `company`, `address_1`, `address_2`, `city`, `state`, `postcode`, `country`, `phone`, `is_default`)
        VALUES (?, ?, 'HOME', 'Alex', 'Morgan', 'TechZone Labs', 'Flat 402, Emerald Heights', 'Park Street', 'Kolkata', 'West Bengal', '700016', 'India', '9876543210', 1)")
        ->execute([$tenantId, $userId]);

    $p1Id = $prodIdMap['TECH-AUD-301'];
    $p2Id = $prodIdMap['TECH-WCH-302'];
    $p4Id = $prodIdMap['TECH-GAM-304'];

    $insCart = $pdo->prepare("INSERT INTO `cart_items` (`tenant_id`, `user_id`, `product_id`, `variant_id`, `quantity`) VALUES (?, ?, ?, 0, ?)");
    $insCart->execute([$tenantId, $userId, $p1Id, 1]);
    $insCart->execute([$tenantId, $userId, $p2Id, 1]);

    $addrText = "Alex Morgan (HOME)\nFlat 402, Emerald Heights, Park Street\nKolkata, West Bengal 700016\nIndia\nPhone: 9876543210";
    $insOrd = $pdo->prepare("INSERT INTO `orders`
        (`tenant_id`, `order_number`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `address_type`, `shipping_address`, `billing_address`, `subtotal`, `discount_amount`, `shipping_fee`, `tax_amount`, `total_amount`, `currency`, `payment_method`, `payment_status`, `order_status`, `courier_name`, `tracking_number`, `tracking_url`, `shipped_at`, `delivered_at`, `created_at`)
        VALUES (?, ?, ?, 'Alex Morgan', 'alex.morgan@example.com', '9876543210', 'HOME', ?, ?, ?, ?, 0.00, 0.00, ?, 'INR', 'razorpay', 'paid', ?, 'BlueDart Tech Air Express', ?, 'https://www.bluedart.com/tracking', ?, ?, ?)");

    $insOrdItem = $pdo->prepare("INSERT INTO `order_items`
        (`tenant_id`, `order_id`, `product_id`, `variant_id`, `product_title`, `variant_title`, `product_sku`, `product_image`, `price`, `quantity`, `total`, `item_status`, `courier_name`, `tracking_number`, `tracking_url`, `shipped_at`, `delivered_at`, `created_at`)
        VALUES (?, ?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, 'BlueDart Tech Air Express', ?, 'https://www.bluedart.com/tracking', ?, ?, ?)");

    // Order 1 (Delivered)
    $insOrd->execute([$tenantId, 'ORD-2026-88412', $userId, $addrText, $addrText, 1098.00, 100.00, 1098.00, 'delivered', 'ELT884920192IN', '2026-10-18 11:30:00', '2026-10-20 16:45:00', '2026-10-17 09:15:00']);
    $ord1Id = (int)$pdo->lastInsertId();
    $insOrdItem->execute([$tenantId, $ord1Id, $p1Id, 'Sony WH-1000XM5 Wireless ANC Headphones', 'Color: Matte Black | 1-Year Official Warranty', 'TECH-AUD-301', 'products/electronics/elec-p-1.jpg', 349.00, 1, 349.00, 'delivered', 'ELT884920192IN', '2026-10-18 11:30:00', '2026-10-20 16:45:00', '2026-10-17 09:15:00']);
    $insOrdItem->execute([$tenantId, $ord1Id, $p2Id, 'Apple Watch Ultra 2 Titanium 49mm GPS + Cellular', 'Case: Natural Titanium | 49mm GPS + Cellular', 'TECH-WCH-302', 'products/electronics/elec-p-2.jpg', 749.00, 1, 749.00, 'delivered', 'ELT884920192IN', '2026-10-18 11:30:00', '2026-10-20 16:45:00', '2026-10-17 09:15:00']);

    // Order 2 (Shipped)
    $insOrd->execute([$tenantId, 'ORD-2026-77319', $userId, $addrText, $addrText, 649.00, 50.00, 649.00, 'shipped', 'ELT773190441IN', '2026-10-25 14:30:00', null, '2026-10-25 10:15:00']);
    $ord2Id = (int)$pdo->lastInsertId();
    $insOrdItem->execute([$tenantId, $ord2Id, $p4Id, 'PlayStation 5 Pro 2TB Console + DualSense Wireless', 'Edition: 2TB NVMe SSD | DualSense Wireless Included', 'TECH-GAM-304', 'products/electronics/elec-p-4.jpg', 649.00, 1, 649.00, 'shipped', 'ELT773190441IN', '2026-10-25 14:30:00', null, '2026-10-25 10:15:00']);

    // Notifications
    $insNotif = $pdo->prepare("INSERT INTO `notifications` (`tenant_id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insNotif->execute([$tenantId, $userId, 'Tech Order #ORD-2026-88412 Delivered', 'Your Sony WH-1000XM5 Headphones & Apple Watch Ultra 2 have been delivered with digital warranty activation.', 'order', 1, '2026-10-20 16:45:00']);
    $insNotif->execute([$tenantId, $userId, 'Next-Gen Hardware Drop Is Live!', 'Save up to $300 on M3 Max Laptops, 4K OLED Monitors, and Flagship Wireless Audio.', 'promo', 0, '2026-10-26 08:00:00']);
}

function seed_tenant2_grocery(PDO $pdo) {
    $tenantId = 2; // Grocery (template3)

    // 1. Settings
    $settings = [
        ['site_name', 'FreshMart Organics', 'general'],
        ['site_email', 'support@freshmart.com', 'general'],
        ['admin_email', 'admin@freshmart.com', 'general'],
        ['site_phone', '+1 (800) 555-0144', 'general'],
        ['support_phone', '+1 (800) 555-0144', 'general'],
        ['site_address', '88 Green Orchard Way, Austin, TX', 'general'],
        ['store_address', '88 Green Orchard Way, Austin, TX', 'general'],
        ['currency_code', 'INR', 'general'],
        ['currency', 'INR', 'general'],
        ['currency_symbol', '₹', 'general'],
        ['active_template', 'grocery', 'general'],
        ['shipping_flat_rate', '0.00', 'shipping'],
        ['free_shipping_min', '50.00', 'shipping'],
        ['shipping_free_threshold', '50.00', 'shipping'],
        ['shipping_charges', '[{"name":"Chilled Cold-Chain Express Delivery","value":0}]', 'shipping'],
        ['tax_rate_percent', '0', 'tax'],
        ['tax_rate', '0', 'tax'],
        ['tax_enabled', '0', 'tax'],
        ['tax_inclusive', '1', 'tax'],
        ['enable_stripe', '1', 'payment'],
        ['enable_razorpay', '1', 'payment'],
        ['enable_cod', '1', 'payment'],
        ['active_online_gateway', 'razorpay', 'payment'],
    ];
    $insSet = $pdo->prepare("INSERT INTO `settings` (`tenant_id`, `setting_key`, `setting_value`, `setting_group`) VALUES (?, ?, ?, ?)");
    foreach ($settings as $s) {
        $insSet->execute([$tenantId, $s[0], $s[1], $s[2]]);
    }

    // 2. Payment Gateways
    $gwRows = $pdo->query("SELECT `gateway_code`, `gateway_name`, `is_active`, `environment`, `credentials` FROM `payment_gateways` WHERE `tenant_id` = 1")->fetchAll();
    $insGw = $pdo->prepare("INSERT INTO `payment_gateways` (`tenant_id`, `gateway_code`, `gateway_name`, `is_active`, `environment`, `credentials`) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($gwRows as $gw) {
        $insGw->execute([$tenantId, $gw['gateway_code'], $gw['gateway_name'], $gw['is_active'], $gw['environment'], $gw['credentials']]);
    }

    // 3. Categories
    $categories = [
        ['name' => 'Fresh Vegetables',       'slug' => 'fresh-vegetables',     'desc' => 'Crisp Hydroponic & Organic Farm Greens',     'img' => 'grocery/broccoli.png'],
        ['name' => 'Organic Fruits',         'slug' => 'organic-fruits',       'desc' => 'Orchard Fresh Berries, Avocados & Apples',   'img' => 'grocery/apple.png'],
        ['name' => 'Dairy & Farm Eggs',      'slug' => 'dairy-eggs',           'desc' => 'Pasture-Raised A2 Milk, Yogurt & Brown Eggs','img' => 'grocery/eggs.png'],
        ['name' => 'Bakery & Breads',        'slug' => 'bakery-breads',        'desc' => 'Stoneground Sourdough & Morning Pastries',   'img' => 'grocery/bread.png'],
        ['name' => 'Organic Pantry',         'slug' => 'meats-seafood',        'desc' => 'Raw Wildflower Honey, Nuts & Cold Oils',     'img' => 'grocery/groceries.png'],
        ['name' => 'Organic Juices',         'slug' => 'beverages-juices',     'desc' => '100% Cold-Pressed Juices & Wellness Drinks', 'img' => 'grocery/orange-juice.png'],
        ['name' => 'Root & Farm Greens',     'slug' => 'root-farm-greens',     'desc' => 'Farm-Fresh Carrots, Beets & Root Produce',   'img' => 'grocery/carrot.png'],
        ['name' => 'Snacks & Branded Foods', 'slug' => 'snacks-branded-foods', 'desc' => 'Wholesome Snacks, Cereals & Packaged Foods', 'img' => 'grocery/grocery.png'],
        ['name' => 'Healthy & Diet Foods',   'slug' => 'healthy-diet-foods',   'desc' => 'Nutrient-Rich Superfoods & Healthy Staples', 'img' => 'grocery/healthy-food.png'],
        ['name' => 'Daily Essentials',       'slug' => 'daily-essentials',     'desc' => 'Everyday Household & Grocery Must-Haves',    'img' => 'grocery/shopping-bag.png'],
    ];
    $catMap = [];
    $insCat = $pdo->prepare("INSERT INTO `categories` (`tenant_id`, `parent_id`, `name`, `slug`, `description`, `image`, `is_featured`, `sort_order`, `status`) VALUES (?, 0, ?, ?, ?, ?, 1, ?, 'active')");
    $pdo->exec("DELETE FROM `template_categories` WHERE `template_key` = 'template3'");
    $insTplCat = $pdo->prepare("INSERT INTO `template_categories` (`template_key`, `name`, `slug`, `subtitle`, `image`, `sort_order`, `status`) VALUES ('template3', ?, ?, ?, ?, ?, 'active')");
    foreach ($categories as $idx => $c) {
        $insCat->execute([$tenantId, $c['name'], $c['slug'], $c['desc'], $c['img'], $idx + 1]);
        $catId = (int)$pdo->lastInsertId();
        $catMap[$c['slug']] = $catId;
        $catMap[strtolower($c['name'])] = $catId;
        $insTplCat->execute([$c['name'], $c['slug'], '100% Organic', $c['img'], $idx + 1]);
    }

    // 4. Hero Banners
    $banners = [
        [
            'title' => 'Fresh Organic Groceries Delivered To Your Door',
            'badge' => '100% Farm Fresh & Organic',
            'desc'  => 'Hand-picked vegetables, pasture-raised dairy, and artisan sourdough delivered chilled within 30 minutes.',
            'btn'   => 'Order Fresh Now',
            'url'   => 'shop',
            'img'   => 'assets/templates/bazaro/img/grocery/grocery-hero-thumb-1.png',
        ],
        [
            'title' => 'Save Up To 35% On Daily Pantry Essentials',
            'badge' => 'Weekly Supermarket Specials',
            'desc'  => 'Zero pesticides, non-GMO certified produce sourced directly from local sustainable family farms.',
            'btn'   => 'Browse Specials',
            'url'   => 'shop',
            'img'   => 'assets/templates/bazaro/img/grocery/grocery-hero-thumb-2.png',
        ],
    ];
    $insBan = $pdo->prepare("INSERT INTO `banners` (`tenant_id`, `title`, `subtitle`, `button_text`, `button_link`, `image`, `sort_order`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
    $pdo->exec("DELETE FROM `template_hero_banners` WHERE `template_key` = 'template3'");
    $insTplBan = $pdo->prepare("INSERT INTO `template_hero_banners` (`template_key`, `badge`, `title`, `description`, `button_text`, `button_url`, `image`, `sort_order`, `status`) VALUES ('template3', ?, ?, ?, ?, ?, ?, ?, 'active')");
    foreach ($banners as $idx => $b) {
        $insBan->execute([$tenantId, $b['title'], $b['badge'], $b['btn'], $b['url'], $b['img'], $idx + 1]);
        $insTplBan->execute([$b['badge'], $b['title'], $b['desc'], $b['btn'], $b['url'], $b['img'], $idx + 1]);
    }

    // 5. Brands
    $brands = [
        ['name' => 'NatureFresh Organic',  'slug' => 'naturefresh-organic'],
        ['name' => 'GreenValley Farms',    'slug' => 'greenvalley-farms'],
        ['name' => 'HarvestGold Dairy',    'slug' => 'harvestgold-dairy'],
        ['name' => 'Artisan Bakehouse',    'slug' => 'artisan-bakehouse'],
        ['name' => 'EarthBounty Organics', 'slug' => 'earthbounty-organics'],
        ['name' => 'PureOrchard Juices',   'slug' => 'pureorchard-juices'],
    ];
    $brandMap = [];
    $insBrand = $pdo->prepare("INSERT INTO `brands` (`tenant_id`, `name`, `slug`, `logo`, `status`) VALUES (?, ?, ?, '', 'active')");
    foreach ($brands as $b) {
        $insBrand->execute([$tenantId, $b['name'], $b['slug']]);
        $brandMap[$b['name']] = (int)$pdo->lastInsertId();
    }

    // 6. Attributes & Values (Pack Size)
    $insAttr = $pdo->prepare("INSERT INTO `attributes` (`tenant_id`, `name`, `slug`, `type`) VALUES (?, 'Pack Size', 'size', 'multiple_select')");
    $insAttr->execute([$tenantId]);
    $packAttrId = (int)$pdo->lastInsertId();

    $packSizes = ['250g', '400g', '500g', '1 kg', '1 Liter', '4 pcs', '6 pcs', '12 pcs', '1 pc'];
    $packValMap = [];
    $insAttrVal = $pdo->prepare("INSERT INTO `attribute_values` (`tenant_id`, `attribute_id`, `value`, `color_code`, `sort_order`) VALUES (?, ?, ?, 0, ?)");
    foreach ($packSizes as $idx => $ps) {
        $insAttrVal->execute([$tenantId, $packAttrId, $ps, $idx + 1]);
        $packValMap[$ps] = (int)$pdo->lastInsertId();
    }

    // 7. Products (12 rich Grocery products with clean unit_label values)
    $products = [
        [
            'title'       => 'Sweet Red Strawberries Box',
            'slug'        => 'sweet-red-strawberries-box',
            'sku'         => 'GRC-FRT-201',
            'cat_slug'    => 'organic-fruits',
            'cat_name'    => 'Organic Fruits',
            'brand'       => 'NatureFresh Organic',
            'price'       => 4.80,
            'sale_price'  => 3.49,
            'unit_label'  => '400g',
            'main_image'  => 'products/grocery/grocery-p-3.jpg',
            'hover_image' => 'products/grocery/grocery-p-7.jpg',
            'badge'       => 'Organic',
            'rating'      => 4.90,
            'reviews_cnt' => 38,
            'stock'       => 32,
            'short_desc'  => 'Hand-picked farm-fresh organic red strawberries, naturally ripened for peak sweetness and rich vitamin C.',
            'desc'        => 'Grown in local sustainable organic orchards with zero synthetic pesticides. Chilled immediately after morning harvest to preserve juicy texture and sweet aroma.',
            'sizes'       => ['250g', '400g', '1 kg'],
        ],
        [
            'title'       => 'Organic Hass Avocados Pack',
            'slug'        => 'organic-hass-avocados-pack',
            'sku'         => 'GRC-FRT-202',
            'cat_slug'    => 'organic-fruits',
            'cat_name'    => 'Organic Fruits',
            'brand'       => 'GreenValley Farms',
            'price'       => 6.80,
            'sale_price'  => 5.20,
            'unit_label'  => '4 pcs',
            'main_image'  => 'products/grocery/grocery-p-1.jpg',
            'hover_image' => 'products/grocery/grocery-p-2.jpg',
            'badge'       => 'Ripe & Creamy',
            'rating'      => 4.80,
            'reviews_cnt' => 29,
            'stock'       => 25,
            'short_desc'  => 'Creamy, nutrient-dense organic Hass avocados sorted for ready-to-eat ripeness.',
            'desc'        => 'Rich in heart-healthy monounsaturated fats, fiber, and potassium. Ideal for guacamole, sourdough toast, and fresh salads.',
            'sizes'       => ['1 pc', '4 pcs'],
        ],
        [
            'title'       => 'Farm Fresh Organic Whole Milk',
            'slug'        => 'farm-fresh-organic-whole-milk',
            'sku'         => 'GRC-DRY-203',
            'cat_slug'    => 'dairy-eggs',
            'cat_name'    => 'Dairy & Farm Eggs',
            'brand'       => 'HarvestGold Dairy',
            'price'       => 3.40,
            'sale_price'  => 2.85,
            'unit_label'  => '1 Liter',
            'main_image'  => 'products/grocery/grocery-p-4.jpg',
            'hover_image' => 'products/grocery/grocery-p-5.jpg',
            'badge'       => 'Pasture Raised',
            'rating'      => 4.90,
            'reviews_cnt' => 54,
            'stock'       => 40,
            'short_desc'  => 'Creamy A2 organic whole milk from pasture-raised cows, pasteurized and cold-chain delivered.',
            'desc'        => '100% hormone-free and antibiotic-free organic whole milk delivered chilled at 2°C–4°C from local dairy farms.',
            'sizes'       => ['500g', '1 Liter'],
        ],
        [
            'title'       => 'Artisan Sourdough Country Loaf',
            'slug'        => 'artisan-sourdough-country-loaf',
            'sku'         => 'GRC-BKR-204',
            'cat_slug'    => 'bakery-breads',
            'cat_name'    => 'Bakery & Breads',
            'brand'       => 'Artisan Bakehouse',
            'price'       => 5.00,
            'sale_price'  => 4.20,
            'unit_label'  => '1 pc',
            'main_image'  => 'products/grocery/grocery-p-6.jpg',
            'hover_image' => 'products/grocery/grocery-p-8.jpg',
            'badge'       => 'Fresh Baked',
            'rating'      => 4.70,
            'reviews_cnt' => 19,
            'stock'       => 18,
            'short_desc'  => '24-hour slow-fermented stoneground organic wheat sourdough loaf with a golden crackling crust.',
            'desc'        => 'Baked fresh every morning using stone-milled organic flour, filtered water, sea salt, and our signature wild starter.',
            'sizes'       => ['1 pc'],
        ],
        [
            'title'       => 'Raw Wildflower Organic Honey',
            'slug'        => 'raw-wildflower-organic-honey',
            'sku'         => 'GRC-PNT-205',
            'cat_slug'    => 'meats-seafood',
            'cat_name'    => 'Organic Pantry',
            'brand'       => 'EarthBounty Organics',
            'price'       => 8.20,
            'sale_price'  => 6.49,
            'unit_label'  => '500g',
            'main_image'  => 'products/grocery/grocery-p-5.jpg',
            'hover_image' => 'products/grocery/grocery-p-9.jpg',
            'badge'       => '100% Raw',
            'rating'      => 5.00,
            'reviews_cnt' => 62,
            'stock'       => 30,
            'short_desc'  => 'Unfiltered, unheated raw wildflower honey packed with natural enzymes, pollen, and antioxidants.',
            'desc'        => 'Harvested ethically from mountain wildflower meadows. Never pasteurized or blended with syrups.',
            'sizes'       => ['250g', '500g', '1 kg'],
        ],
        [
            'title'       => 'Free-Range Brown Farm Eggs',
            'slug'        => 'free-range-brown-farm-eggs',
            'sku'         => 'GRC-DRY-206',
            'cat_slug'    => 'dairy-eggs',
            'cat_name'    => 'Dairy & Farm Eggs',
            'brand'       => 'HarvestGold Dairy',
            'price'       => 4.99,
            'sale_price'  => 3.99,
            'unit_label'  => '12 pcs',
            'main_image'  => 'products/grocery/grocery-p-7.jpg',
            'hover_image' => 'products/grocery/grocery-p-10.jpg',
            'badge'       => 'Free Range',
            'rating'      => 4.90,
            'reviews_cnt' => 44,
            'stock'       => 35,
            'short_desc'  => 'Rich golden-yolk brown eggs from pasture-raised hens fed an organic grain and flaxseed diet.',
            'desc'        => 'Collected daily from open-pasture farms and packed in eco-pulp protective cartons.',
            'sizes'       => ['6 pcs', '12 pcs'],
        ],
        [
            'title'       => 'Baby Spinach & Kale Salad Mix',
            'slug'        => 'baby-spinach-kale-salad-mix',
            'sku'         => 'GRC-VEG-207',
            'cat_slug'    => 'fresh-vegetables',
            'cat_name'    => 'Fresh Vegetables',
            'brand'       => 'GreenValley Farms',
            'price'       => 3.20,
            'sale_price'  => 2.49,
            'unit_label'  => '250g',
            'main_image'  => 'products/grocery/grocery-p-8.jpg',
            'hover_image' => 'products/grocery/grocery-p-11.jpg',
            'badge'       => 'Triple Washed',
            'rating'      => 4.80,
            'reviews_cnt' => 31,
            'stock'       => 28,
            'short_desc'  => 'Triple-washed tender organic baby spinach and curly kale leaves, ready to toss into salads or smoothies.',
            'desc'        => 'Hydroponically and organically grown tender leafy greens rich in iron, folate, and vitamin K.',
            'sizes'       => ['250g', '500g', '1 kg'],
        ],
        [
            'title'       => 'Cold-Pressed Valencia Orange Juice',
            'slug'        => 'cold-pressed-valencia-orange-juice',
            'sku'         => 'GRC-BEV-208',
            'cat_slug'    => 'beverages-juices',
            'cat_name'    => 'Organic Juices',
            'brand'       => 'PureOrchard Juices',
            'price'       => 5.60,
            'sale_price'  => 4.50,
            'unit_label'  => '1 Liter',
            'main_image'  => 'products/grocery/grocery-p-9.jpg',
            'hover_image' => 'products/grocery/grocery-p-12.jpg',
            'badge'       => 'No Added Sugar',
            'rating'      => 4.90,
            'reviews_cnt' => 41,
            'stock'       => 22,
            'short_desc'  => '100% pure cold-pressed Valencia oranges with natural pulp and zero added sugar or preservatives.',
            'desc'        => 'Pressed daily from sun-ripened organic Valencia oranges to retain maximum vitamin C and fresh citrus flavor.',
            'sizes'       => ['500g', '1 Liter'],
        ],
        [
            'title'       => 'California Raw Organic Almonds',
            'slug'        => 'california-raw-organic-almonds',
            'sku'         => 'GRC-NUT-209',
            'cat_slug'    => 'meats-seafood',
            'cat_name'    => 'Organic Pantry',
            'brand'       => 'EarthBounty Organics',
            'price'       => 9.50,
            'sale_price'  => 7.80,
            'unit_label'  => '500g',
            'main_image'  => 'products/grocery/grocery-p-10.jpg',
            'hover_image' => 'products/grocery/grocery-p-13.jpg',
            'badge'       => 'Superfood',
            'rating'      => 4.90,
            'reviews_cnt' => 50,
            'stock'       => 45,
            'short_desc'  => 'Crunchy, non-pareil supreme grade organic raw almonds packed in a resealable freshness pouch.',
            'desc'        => 'Naturally rich in plant protein, vitamin E, and healthy fats. Great for snacking, baking, or homemade almond milk.',
            'sizes'       => ['250g', '500g', '1 kg'],
        ],
        [
            'title'       => 'Organic Cherry Tomatoes On Vine',
            'slug'        => 'organic-cherry-tomatoes-on-vine',
            'sku'         => 'GRC-VEG-210',
            'cat_slug'    => 'fresh-vegetables',
            'cat_name'    => 'Fresh Vegetables',
            'brand'       => 'NatureFresh Organic',
            'price'       => 3.80,
            'sale_price'  => 2.99,
            'unit_label'  => '500g',
            'main_image'  => 'products/grocery/grocery-p-11.jpg',
            'hover_image' => 'products/grocery/grocery-p-14.jpg',
            'badge'       => 'Vine Ripened',
            'rating'      => 4.80,
            'reviews_cnt' => 27,
            'stock'       => 36,
            'short_desc'  => 'Sweet, burst-in-your-mouth organic cherry tomatoes harvested on the vine for lingering garden flavor.',
            'desc'        => 'High in lycopene and vitamin C. Perfect for roasting, caprese skewers, pasta sauces, and crisp salads.',
            'sizes'       => ['250g', '500g', '1 kg'],
        ],
        [
            'title'       => 'Greek Probiotic Natural Yogurt',
            'slug'        => 'greek-probiotic-natural-yogurt',
            'sku'         => 'GRC-DRY-211',
            'cat_slug'    => 'dairy-eggs',
            'cat_name'    => 'Dairy & Farm Eggs',
            'brand'       => 'HarvestGold Dairy',
            'price'       => 4.40,
            'sale_price'  => 3.60,
            'unit_label'  => '500g',
            'main_image'  => 'products/grocery/grocery-p-12.jpg',
            'hover_image' => 'products/grocery/grocery-p-15.jpg',
            'badge'       => 'Live Cultures',
            'rating'      => 4.80,
            'reviews_cnt' => 33,
            'stock'       => 29,
            'short_desc'  => 'Thick, strained authentic Greek yogurt with billions of live probiotic cultures and 18g protein per serving.',
            'desc'        => 'Crafted from organic pasture-raised milk with zero thickeners, gums, or artificial sweeteners.',
            'sizes'       => ['250g', '500g', '1 kg'],
        ],
        [
            'title'       => 'Organic Crisp Honeycrisp Apples',
            'slug'        => 'organic-crisp-honeycrisp-apples',
            'sku'         => 'GRC-FRT-212',
            'cat_slug'    => 'organic-fruits',
            'cat_name'    => 'Organic Fruits',
            'brand'       => 'NatureFresh Organic',
            'price'       => 6.00,
            'sale_price'  => 4.80,
            'unit_label'  => '4 pcs',
            'main_image'  => 'products/grocery/grocery-p-2.jpg',
            'hover_image' => 'products/grocery/grocery-p-16.jpg',
            'badge'       => 'Orchard Fresh',
            'rating'      => 4.90,
            'reviews_cnt' => 47,
            'stock'       => 50,
            'short_desc'  => 'Explosively crisp and honey-sweet organic Honeycrisp apples picked from high-altitude orchards.',
            'desc'        => 'Naturally wax-free and pesticide-free orchard apples with a balanced sweet-tart crunch.',
            'sizes'       => ['1 pc', '4 pcs'],
        ],
    ];

    $insProd = $pdo->prepare("INSERT INTO `products`
        (`tenant_id`, `category_id`, `brand_id`, `product_type`, `title`, `slug`, `sku`, `unit_label`, `warranty_info`, `price`, `sale_price`, `tax_rate`, `stock_quantity`, `low_stock_threshold`, `max_purchase_quantity`, `stock_status`, `short_description`, `description`, `highlights`, `main_image`, `gallery_images`, `is_featured`, `is_trending`, `is_new`, `is_cod_allowed`, `rating`, `reviews_count`, `status`)
        VALUES (?, ?, ?, 'simple', ?, ?, ?, ?, '100% Freshness Guaranteed', ?, ?, 0.00, ?, 5, 10, 'in_stock', ?, ?, ?, ?, ?, 1, 1, 1, 1, ?, ?, 'published')");

    $insSpec = $pdo->prepare("INSERT INTO `product_specifications` (`product_id`, `spec_name`, `spec_value`, `sort_order`) VALUES (?, ?, ?, ?)");
    $insProdAttr = $pdo->prepare("INSERT INTO `product_attributes` (`product_id`, `attribute_id`) VALUES (?, ?)");
    $insVar = $pdo->prepare("INSERT INTO `product_variants` (`tenant_id`, `product_id`, `title`, `sku`, `price`, `sale_price`, `stock_quantity`, `max_purchase_quantity`, `stock_status`, `image`, `gallery_images`) VALUES (?, ?, ?, ?, ?, ?, ?, 10, 'in_stock', ?, ?)");
    $insVarVal = $pdo->prepare("INSERT INTO `product_variant_values` (`variant_id`, `attribute_id`, `attribute_value_id`) VALUES (?, ?, ?)");
    $insRev = $pdo->prepare("INSERT INTO `reviews` (`tenant_id`, `product_id`, `user_id`, `order_number`, `customer_name`, `customer_email`, `rating`, `title`, `review`, `status`, `created_at`) VALUES (?, ?, 1, 'ORD-2026-88412', ?, ?, ?, ?, ?, 'approved', ?)");

    $pdo->exec("DELETE FROM `template_products` WHERE `template_key` = 'template3'");
    $insTplProd = $pdo->prepare("INSERT INTO `template_products`
        (`template_key`, `category_name`, `name`, `slug`, `unit_label`, `price`, `sale_price`, `rating`, `reviews_count`, `image`, `hover_image`, `badge_text`, `short_desc`, `sort_order`, `status`)
        VALUES ('template3', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");

    $prodIdMap = [];
    foreach ($products as $idx => $p) {
        $catId = $catMap[$p['cat_slug']] ?? reset($catMap);
        $brandId = $brandMap[$p['brand']] ?? reset($brandMap);
        $galJson = json_encode([$p['main_image'], $p['hover_image']]);
        $hlJson = json_encode([
            ['key' => 'Certification', 'value' => '100% Certified Organic & Non-GMO'],
            ['key' => 'Pack Size', 'value' => $p['unit_label']],
            ['key' => 'Cold-Chain Storage', 'value' => 'Temperature Controlled 2°C - 6°C'],
        ]);

        $insProd->execute([
            $tenantId, $catId, $brandId, $p['title'], $p['slug'], $p['sku'],
            $p['unit_label'], $p['price'], $p['sale_price'],
            $p['stock'], $p['short_desc'], $p['desc'], $hlJson,
            $p['main_image'], $galJson, $p['rating'], $p['reviews_cnt']
        ]);
        $prodId = (int)$pdo->lastInsertId();
        $prodIdMap[$p['sku']] = $prodId;

        $insSpec->execute([$prodId, 'Net Weight / Unit', $p['unit_label'], 1]);
        $insSpec->execute([$prodId, 'Farming Method', '100% Certified Organic, Pesticide-Free', 2]);
        $insSpec->execute([$prodId, 'Shelf Life', 'Freshly Harvested (Keep Chilled)', 3]);

        $insProdAttr->execute([$prodId, $packAttrId]);
        foreach ($p['sizes'] as $sIdx => $sz) {
            $vTitle = $p['title'] . ' - ' . $sz;
            $vSku = $p['sku'] . '-S' . ($sIdx + 1);
            $insVar->execute([$tenantId, $prodId, $vTitle, $vSku, $p['price'], $p['sale_price'], $p['stock'], $p['main_image'], $galJson]);
            $varId = (int)$pdo->lastInsertId();
            if (isset($packValMap[$sz])) {
                $insVarVal->execute([$varId, $packAttrId, $packValMap[$sz]]);
            }
        }

        $insRev->execute([$tenantId, $prodId, 'Alex Morgan', 'alex.morgan@example.com', 5, 'Super fresh morning harvest quality!', 'Delivered chilled in eco-friendly packaging. Tastes genuinely farm fresh and organic.', '2026-10-24 11:30:00']);

        $insTplProd->execute([
            $p['cat_name'], $p['title'], $p['slug'], $p['unit_label'],
            $p['price'], $p['sale_price'], $p['rating'], $p['reviews_cnt'],
            'assets/images/' . $p['main_image'], 'assets/images/' . $p['hover_image'],
            $p['badge'], $p['short_desc'], $idx + 1
        ]);
    }

    // 8. Demo Customer, Address, Cart Items, Orders, Notifications for Grocery
    $insUser = $pdo->prepare("INSERT INTO `users` (`tenant_id`, `customer_group_id`, `first_name`, `last_name`, `email`, `password`, `phone`, `avatar`, `status`) VALUES (?, 1, 'Alex', 'Morgan', 'alex.morgan@example.com', ?, '9876543210', 'default-user.png', 'active')");
    $insUser->execute([$tenantId, password_hash('password123', PASSWORD_BCRYPT)]);
    $userId = (int)$pdo->lastInsertId();

    $pdo->prepare("INSERT INTO `user_addresses` (`tenant_id`, `user_id`, `type`, `first_name`, `last_name`, `company`, `address_1`, `address_2`, `city`, `state`, `postcode`, `country`, `phone`, `is_default`)
        VALUES (?, ?, 'HOME', 'Alex', 'Morgan', 'Green Home', 'Flat 402, Emerald Heights', 'Park Street', 'Kolkata', 'West Bengal', '700016', 'India', '9876543210', 1)")
        ->execute([$tenantId, $userId]);

    $g1 = $prodIdMap['GRC-FRT-201'];
    $g2 = $prodIdMap['GRC-FRT-202'];
    $g3 = $prodIdMap['GRC-DRY-203'];
    $g5 = $prodIdMap['GRC-PNT-205'];
    $g9 = $prodIdMap['GRC-NUT-209'];

    $insCart = $pdo->prepare("INSERT INTO `cart_items` (`tenant_id`, `user_id`, `product_id`, `variant_id`, `quantity`) VALUES (?, ?, ?, 0, ?)");
    $insCart->execute([$tenantId, $userId, $g1, 2]);
    $insCart->execute([$tenantId, $userId, $g2, 1]);
    $insCart->execute([$tenantId, $userId, $g3, 1]);

    $addrText = "Alex Morgan (HOME)\nFlat 402, Emerald Heights, Park Street\nKolkata, West Bengal 700016\nIndia\nPhone: 9876543210";
    $insOrd = $pdo->prepare("INSERT INTO `orders`
        (`tenant_id`, `order_number`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `address_type`, `shipping_address`, `billing_address`, `subtotal`, `discount_amount`, `shipping_fee`, `tax_amount`, `total_amount`, `currency`, `payment_method`, `payment_status`, `order_status`, `courier_name`, `tracking_number`, `tracking_url`, `shipped_at`, `delivered_at`, `created_at`)
        VALUES (?, ?, ?, 'Alex Morgan', 'alex.morgan@example.com', '9876543210', 'HOME', ?, ?, ?, ?, 0.00, 0.00, ?, 'INR', 'razorpay', 'paid', ?, 'NatureFresh Cold-Chain Express', ?, 'https://www.bluedart.com/tracking', ?, ?, ?)");

    $insOrdItem = $pdo->prepare("INSERT INTO `order_items`
        (`tenant_id`, `order_id`, `product_id`, `variant_id`, `product_title`, `variant_title`, `product_sku`, `product_image`, `price`, `quantity`, `total`, `item_status`, `courier_name`, `tracking_number`, `tracking_url`, `shipped_at`, `delivered_at`, `created_at`)
        VALUES (?, ?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, 'NatureFresh Cold-Chain Express', ?, 'https://www.bluedart.com/tracking', ?, ?, ?)");

    // Order 1 (Delivered)
    $insOrd->execute([$tenantId, 'ORD-2026-88412', $userId, $addrText, $addrText, 15.03, 4.77, 15.03, 'delivered', 'GRC884920192IN', '2026-10-24 08:30:00', '2026-10-24 11:15:00', '2026-10-24 07:15:00']);
    $ord1Id = (int)$pdo->lastInsertId();
    $insOrdItem->execute([$tenantId, $ord1Id, $g1, 'Sweet Red Strawberries Box', 'Pack: 400g | Rate: ₹8.73/kg', 'GRC-FRT-201', 'products/grocery/grocery-p-3.jpg', 3.49, 2, 6.98, 'delivered', 'GRC884920192IN', '2026-10-24 08:30:00', '2026-10-24 11:15:00', '2026-10-24 07:15:00']);
    $insOrdItem->execute([$tenantId, $ord1Id, $g2, 'Organic Hass Avocados Pack', 'Pack: 4 pcs | Rate: ₹1.30/pc', 'GRC-FRT-202', 'products/grocery/grocery-p-1.jpg', 5.20, 1, 5.20, 'delivered', 'GRC884920192IN', '2026-10-24 08:30:00', '2026-10-24 11:15:00', '2026-10-24 07:15:00']);
    $insOrdItem->execute([$tenantId, $ord1Id, $g3, 'Farm Fresh Organic Whole Milk', 'Pack: 1 Liter | Rate: ₹2.85/L', 'GRC-DRY-203', 'products/grocery/grocery-p-4.jpg', 2.85, 1, 2.85, 'delivered', 'GRC884920192IN', '2026-10-24 08:30:00', '2026-10-24 11:15:00', '2026-10-24 07:15:00']);

    // Order 2 (Shipped)
    $insOrd->execute([$tenantId, 'ORD-2026-77319', $userId, $addrText, $addrText, 14.29, 3.41, 14.29, 'shipped', 'GRC773190441IN', '2026-10-26 09:00:00', null, '2026-10-26 07:45:00']);
    $ord2Id = (int)$pdo->lastInsertId();
    $insOrdItem->execute([$tenantId, $ord2Id, $g5, 'Raw Wildflower Organic Honey', 'Pack: 500g | Rate: ₹12.98/kg', 'GRC-PNT-205', 'products/grocery/grocery-p-5.jpg', 6.49, 1, 6.49, 'shipped', 'GRC773190441IN', '2026-10-26 09:00:00', null, '2026-10-26 07:45:00']);
    $insOrdItem->execute([$tenantId, $ord2Id, $g9, 'California Raw Organic Almonds', 'Pack: 500g | Rate: ₹15.60/kg', 'GRC-NUT-209', 'products/grocery/grocery-p-10.jpg', 7.80, 1, 7.80, 'shipped', 'GRC773190441IN', '2026-10-26 09:00:00', null, '2026-10-26 07:45:00']);

    // Notifications
    $insNotif = $pdo->prepare("INSERT INTO `notifications` (`tenant_id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insNotif->execute([$tenantId, $userId, 'Chilled Grocery Order Delivered!', 'Your fresh grocery order #ORD-2026-88412 (Strawberries, Hass Avocados & Whole Milk) was delivered in cold-chain packaging.', 'order', 1, '2026-10-24 11:15:00']);
    $insNotif->execute([$tenantId, $userId, 'Morning Organic Harvest Sale — Up to 30% Off!', 'Freshly picked organic berries, leafy greens, and pasture-raised dairy are now live with per-kg & per-pc value pricing.', 'promo', 0, '2026-10-26 07:00:00']);
    $insNotif->execute([$tenantId, $userId, 'Order #ORD-2026-77319 Out for Delivery', 'Your NatureFresh Cold-Chain van is on the way with your Raw Wildflower Honey & California Almonds.', 'order', 0, '2026-10-26 09:05:00']);
}

if (php_sapi_name() === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $pdo = get_seeder_pdo('ecommerce_main');
    seed_all_templates_in_main_db($pdo);
    echo "Successfully seeded all 3 templates into ecommerce_main!\n";
    foreach ([1 => 'Fashion (template1)', 2 => 'Grocery (template3)', 3 => 'Electronics (template2)'] as $tid => $label) {
        $pCnt = $pdo->query("SELECT COUNT(*) FROM products WHERE tenant_id = $tid")->fetchColumn();
        $cCnt = $pdo->query("SELECT COUNT(*) FROM categories WHERE tenant_id = $tid")->fetchColumn();
        $bCnt = $pdo->query("SELECT COUNT(*) FROM banners WHERE tenant_id = $tid")->fetchColumn();
        $oCnt = $pdo->query("SELECT COUNT(*) FROM orders WHERE tenant_id = $tid")->fetchColumn();
        echo " - Tenant #$tid [$label]: $pCnt products, $cCnt categories, $bCnt banners, $oCnt orders\n";
    }
}
