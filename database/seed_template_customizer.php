<?php
/**
 * Database Migration & Seeder for Superadmin Template Customizer
 * Creates and seeds:
 *  - template_layout_settings
 *  - template_hero_banners
 *  - template_categories
 *  - template_products
 * For template1 (Fashion), template2 (Electronics), and template3 (Grocery).
 */

$pdo = new PDO('mysql:host=localhost;dbname=ecommerce_main;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// 1. template_layout_settings
$pdo->exec("CREATE TABLE IF NOT EXISTS `template_layout_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `template_key` VARCHAR(50) NOT NULL DEFAULT 'template1',
    `layout_number` INT NOT NULL DEFAULT 1,
    `section_key` VARCHAR(100) NOT NULL,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` LONGTEXT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_tpl_lay_sec_key` (`template_key`, `layout_number`, `section_key`, `setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// 2. template_hero_banners
$pdo->exec("CREATE TABLE IF NOT EXISTS `template_hero_banners` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `template_key` VARCHAR(50) NOT NULL DEFAULT 'template1',
    `layout_number` INT NOT NULL DEFAULT 1,
    `badge` VARCHAR(150) NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `button_text` VARCHAR(100) DEFAULT 'Shop Now',
    `button_url` VARCHAR(255) DEFAULT 'shop',
    `image` VARCHAR(255) NOT NULL,
    `background_image` VARCHAR(255) NULL,
    `sort_order` INT NOT NULL DEFAULT 1,
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_tpl_hero` (`template_key`, `layout_number`, `status`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// 3. template_categories
$pdo->exec("CREATE TABLE IF NOT EXISTS `template_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `template_key` VARCHAR(50) NOT NULL DEFAULT 'template1',
    `layout_number` INT NOT NULL DEFAULT 1,
    `name` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(160) NOT NULL,
    `subtitle` VARCHAR(150) DEFAULT '12 Items',
    `image` VARCHAR(255) NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 1,
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_tpl_cat` (`template_key`, `layout_number`, `status`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// 4. template_products
$pdo->exec("CREATE TABLE IF NOT EXISTS `template_products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `template_key` VARCHAR(50) NOT NULL DEFAULT 'template1',
    `layout_number` INT NOT NULL DEFAULT 1,
    `category_name` VARCHAR(150) DEFAULT 'Featured',
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `sale_price` DECIMAL(10,2) NULL DEFAULT NULL,
    `image` VARCHAR(255) NOT NULL,
    `hover_image` VARCHAR(255) NULL,
    `badge_text` VARCHAR(60) DEFAULT '',
    `unit_label` VARCHAR(60) DEFAULT '',
    `short_desc` TEXT NULL,
    `rating` DECIMAL(3,1) NOT NULL DEFAULT 4.8,
    `reviews_count` INT NOT NULL DEFAULT 12,
    `sort_order` INT NOT NULL DEFAULT 1,
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_tpl_prod` (`template_key`, `layout_number`, `status`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Seed default section headers in template_layout_settings
$defaultSettings = [
    ['template1', 1, 'categories_header', 'tagline', 'CURATED COLLECTIONS'],
    ['template1', 1, 'categories_header', 'title', 'Shop by Category'],
    ['template1', 1, 'products_header', 'tagline', 'TRENDING NOW'],
    ['template1', 1, 'products_header', 'title', 'Featured Fashion Arrivals'],
    ['template2', 1, 'categories_header', 'tagline', 'BROWSE DEPARTMENTS'],
    ['template2', 1, 'categories_header', 'title', 'Popular Tech Categories'],
    ['template2', 1, 'products_header', 'tagline', 'TOP RATED GEAR'],
    ['template2', 1, 'products_header', 'title', 'Trending Electronics'],
    ['template3', 1, 'categories_header', 'tagline', 'FARM TO TABLE'],
    ['template3', 1, 'categories_header', 'title', 'Shop By Fresh Category'],
    ['template3', 1, 'products_header', 'tagline', 'DAILY HARVEST'],
    ['template3', 1, 'products_header', 'title', 'Popular Organic Products'],
];
$stmtSet = $pdo->prepare("INSERT IGNORE INTO `template_layout_settings` (`template_key`, `layout_number`, `section_key`, `setting_key`, `setting_value`) VALUES (?, ?, ?, ?, ?)");
foreach ($defaultSettings as $s) {
    $stmtSet->execute($s);
}

$pdo->exec("TRUNCATE TABLE `template_hero_banners`");
$pdo->exec("TRUNCATE TABLE `template_categories`");
$pdo->exec("TRUNCATE TABLE `template_products`");

// Seed Hero Banners
$heroes = [
    // Template 1: Fashion
    [
        'template_key'     => 'template1',
        'layout_number'    => 1,
        'badge'            => 'BIKINIS & SWIMSUITS',
        'title'            => 'Flash Sale Madness',
        'description'      => 'Discover our signature luxury fashion collection crafted for timeless style and comfort.',
        'button_text'      => 'Explore Collection',
        'button_url'       => 'shop',
        'image'            => 'assets/images/slider/slider-women1.jpg',
        'background_image' => '',
        'sort_order'       => 1,
        'status'           => 'active',
    ],
    [
        'template_key'     => 'template1',
        'layout_number'    => 1,
        'badge'            => 'SUMMER 2026 COLLECTION',
        'title'            => 'Effortless Chic & Modern Tailoring',
        'description'      => 'Refresh your wardrobe with breathable linens, silk separates, and statement accessories.',
        'button_text'      => 'Shop New Arrivals',
        'button_url'       => 'shop',
        'image'            => 'assets/images/slider/slider-women2.jpg',
        'background_image' => '',
        'sort_order'       => 2,
        'status'           => 'active',
    ],
    [
        'template_key'     => 'template1',
        'layout_number'    => 1,
        'badge'            => 'EXCLUSIVE DESIGNER EDIT',
        'title'            => 'Timeless Wardrobe Essentials',
        'description'      => 'Up to 40% off curated seasonal staples and limited-edition runway pieces.',
        'button_text'      => 'Discover Now',
        'button_url'       => 'shop',
        'image'            => 'assets/images/slider/slider-fashion-main1.jpg',
        'background_image' => '',
        'sort_order'       => 3,
        'status'           => 'active',
    ],

    // Template 2: Electronics
    [
        'template_key'     => 'template2',
        'layout_number'    => 1,
        'badge'            => 'Next-Gen Flagship Audio',
        'title'            => 'Pro Studio Wireless ANC Headphones',
        'description'      => 'Spatial audio with dynamic head tracking, 40-hour battery life, and lossless USB-C audio.',
        'button_text'      => 'Shop Now',
        'button_url'       => 'shop',
        'image'            => 'assets/templates/bazaro/img/electronics/slider/slide-1.jpg',
        'background_image' => '',
        'sort_order'       => 1,
        'status'           => 'active',
    ],
    [
        'template_key'     => 'template2',
        'layout_number'    => 1,
        'badge'            => 'Limited Time Tech Deal',
        'title'            => 'Ultra-Fast M4 Pro Creator Laptops',
        'description'      => 'Engineered for developers, creators, and gamers with Liquid Retina XDR display.',
        'button_text'      => 'Explore Deals',
        'button_url'       => 'shop',
        'image'            => 'assets/templates/bazaro/img/electronics/slider/slide-2.jpg',
        'background_image' => '',
        'sort_order'       => 2,
        'status'           => 'active',
    ],
    [
        'template_key'     => 'template2',
        'layout_number'    => 1,
        'badge'            => 'Smart Ecosystem',
        'title'            => 'Upgrade Your Smart Setup 2026',
        'description'      => 'Connected home devices, wearables, and next-gen accessories.',
        'button_text'      => 'Shop Now',
        'button_url'       => 'shop',
        'image'            => 'assets/templates/bazaro/img/electronics/slider/slide-3.jpg',
        'background_image' => '',
        'sort_order'       => 3,
        'status'           => 'active',
    ],

    // Template 3: Grocery
    [
        'template_key'     => 'template3',
        'layout_number'    => 1,
        'badge'            => '100% Farm Fresh & Organic',
        'title'            => 'Fresh Organic Groceries Delivered To Your Door',
        'description'      => 'Hand-picked seasonal fruits, crisp organic vegetables, and farm-fresh dairy every morning.',
        'button_text'      => 'Order Fresh Now',
        'button_url'       => 'shop',
        'image'            => 'assets/templates/bazaro/img/grocery/grocery-hero-thumb-1.png',
        'background_image' => '',
        'sort_order'       => 1,
        'status'           => 'active',
    ],
    [
        'template_key'     => 'template3',
        'layout_number'    => 1,
        'badge'            => 'Weekly Supermarket Specials',
        'title'            => 'Save Up To 35% On Daily Pantry Essentials',
        'description'      => 'Zero preservatives, sustainably sourced organic produce for your healthy family kitchen.',
        'button_text'      => 'Browse Aisles',
        'button_url'       => 'shop',
        'image'            => 'assets/templates/bazaro/img/grocery/grocery-hero-thumb-2.png',
        'background_image' => '',
        'sort_order'       => 2,
        'status'           => 'active',
    ],
];

$insHero = $pdo->prepare("INSERT INTO `template_hero_banners`
    (`template_key`, `layout_number`, `badge`, `title`, `description`, `button_text`, `button_url`, `image`, `background_image`, `sort_order`, `status`)
    VALUES (:template_key, :layout_number, :badge, :title, :description, :button_text, :button_url, :image, :background_image, :sort_order, :status)");
foreach ($heroes as $h) {
    $insHero->execute($h);
}

// Seed Categories
$cats = [
    // Template 1: Fashion
    ['template1', 1, "Women's Wear", 'womens-wear', '24 Items', 'assets/images/collections/collection-circle/cls-circle1.jpg', 1, 'active'],
    ['template1', 1, "Men's Collection", 'mens-collection', '18 Items', 'assets/images/collections/collection-circle/cls-circle2.jpg', 2, 'active'],
    ['template1', 1, 'Handbags & Totes', 'handbags-totes', '15 Items', 'assets/images/collections/collection-circle/cls-circle3.jpg', 3, 'active'],
    ['template1', 1, 'Footwear & Heels', 'footwear-heels', '20 Items', 'assets/images/collections/collection-circle/cls-circle4.jpg', 4, 'active'],
    ['template1', 1, 'Luxury Jewelry', 'luxury-jewelry', '14 Items', 'assets/images/collections/collection-circle/cls-circle5.jpg', 5, 'active'],
    ['template1', 1, 'Sunglasses & Hats', 'sunglasses-hats', '12 Items', 'assets/images/collections/collection-circle/cls-circle6.jpg', 6, 'active'],

    // Template 2: Electronics
    ['template2', 1, 'Headphones & Audio', 'headphones-audio', '18 Products', 'assets/templates/bazaro/img/electronics/category/cat-1.jpg', 1, 'active'],
    ['template2', 1, 'Smart Watches', 'smart-watches', '14 Products', 'assets/templates/bazaro/img/electronics/category/cat-2.jpg', 2, 'active'],
    ['template2', 1, '5G Smartphones', 'smartphones', '22 Products', 'assets/templates/bazaro/img/electronics/category/cat-3.jpg', 3, 'active'],
    ['template2', 1, 'Laptops & MacBooks', 'laptops-pcs', '16 Products', 'assets/templates/bazaro/img/electronics/category/cat-4.jpg', 4, 'active'],
    ['template2', 1, 'Cameras & Drones', 'cameras-drones', '11 Products', 'assets/templates/bazaro/img/electronics/category/cat-5.jpg', 5, 'active'],
    ['template2', 1, 'Gaming Consoles', 'gaming-consoles', '19 Products', 'assets/templates/bazaro/img/electronics/category/cat-6.jpg', 6, 'active'],

    // Template 3: Grocery
    ['template3', 1, 'Fresh Vegetables',       'fresh-vegetables',     '32 Items', 'grocery/broccoli.png',     1,  'active'],
    ['template3', 1, 'Organic Fruits',         'organic-fruits',       '28 Items', 'grocery/apple.png',        2,  'active'],
    ['template3', 1, 'Dairy & Farm Eggs',      'dairy-eggs',           '19 Items', 'grocery/eggs.png',         3,  'active'],
    ['template3', 1, 'Bakery & Breads',        'bakery-breads',        '15 Items', 'grocery/bread.png',        4,  'active'],
    ['template3', 1, 'Organic Pantry',         'meats-seafood',        '14 Items', 'grocery/groceries.png',    5,  'active'],
    ['template3', 1, 'Organic Juices',         'beverages-juices',     '24 Items', 'grocery/orange-juice.png', 6,  'active'],
    ['template3', 1, 'Root & Farm Greens',     'root-farm-greens',     '18 Items', 'grocery/carrot.png',       7,  'active'],
    ['template3', 1, 'Snacks & Branded Foods', 'snacks-branded-foods', '20 Items', 'grocery/grocery.png',      8,  'active'],
    ['template3', 1, 'Healthy & Diet Foods',   'healthy-diet-foods',   '16 Items', 'grocery/healthy-food.png', 9,  'active'],
    ['template3', 1, 'Daily Essentials',       'daily-essentials',     '25 Items', 'grocery/shopping-bag.png', 10, 'active'],
];

$insCat = $pdo->prepare("INSERT INTO `template_categories`
    (`template_key`, `layout_number`, `name`, `slug`, `subtitle`, `image`, `sort_order`, `status`)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
foreach ($cats as $c) {
    $insCat->execute($c);
}

// Seed Products
$prods = [
    // Template 1: Fashion (8 products)
    ['template1', 1, "Women's Wear", 'Ribbed Modal Fitted T-Shirt', 'ribbed-modal-fitted-tshirt', 68.00, 49.00, 'assets/images/products/womens/women-1.jpg', 'assets/images/products/womens/women-2.jpg', 'Sale', '', 'Soft breathable modal jersey tee with flattering slim silhouette.', 4.9, 28, 1, 'active'],
    ['template1', 1, "Women's Wear", 'Oversized Linen Tailored Blazer', 'oversized-linen-tailored-blazer', 145.00, 115.00, 'assets/images/products/womens/women-3.jpg', 'assets/images/products/womens/women-4.jpg', 'New', '', 'Relaxed single-breasted blazer crafted from 100% European flax linen.', 4.8, 19, 2, 'active'],
    ['template1', 1, "Women's Wear", 'Pleated Satin Midi Skirt', 'pleated-satin-midi-skirt', 98.00, 78.00, 'assets/images/products/womens/women-8.jpg', 'assets/images/products/womens/women-9.jpg', 'Hot', '', 'Fluid high-shine satin midi skirt with an effortless drape.', 4.9, 34, 3, 'active'],
    ['template1', 1, "Women's Wear", 'High-Waist Wide Leg Denim Jeans', 'high-waist-wide-leg-denim-jeans', 110.00, null, 'assets/images/products/womens/women-19.jpg', 'assets/images/products/womens/women-20.jpg', '', '', 'Vintage-wash organic cotton denim with a tailored high rise.', 4.7, 22, 4, 'active'],
    ['template1', 1, "Women's Wear", 'Cashmere Blend Knit Cardigan', 'cashmere-blend-knit-cardigan', 135.00, 99.00, 'assets/images/products/womens/women-29.jpg', 'assets/images/products/womens/women-30.jpg', 'Sale', '', 'Ultra-soft Mongolian cashmere blend cardigan with horn buttons.', 5.0, 41, 5, 'active'],
    ['template1', 1, "Men's Collection", 'Structured Cotton Trench Coat', 'structured-cotton-trench-coat', 210.00, 169.00, 'assets/images/products/mens/men-1.jpg', 'assets/images/products/mens/men-2.jpg', 'Sale', '', 'Water-resistant double-breasted trench coat with storm flap.', 4.9, 17, 6, 'active'],
    ['template1', 1, "Men's Collection", 'Merino Wool Crewneck Sweater', 'merino-wool-crewneck-sweater', 95.00, null, 'assets/images/products/mens/men-3.jpg', 'assets/images/products/mens/men-4.jpg', 'New', '', 'Fine-gauge Australian merino wool pullover for year-round layering.', 4.8, 15, 7, 'active'],
    ['template1', 1, 'Handbags & Totes', 'Quilted Leather Crossbody Bag', 'quilted-leather-crossbody-bag', 160.00, 128.00, 'assets/images/products/womens/women-37.jpg', 'assets/images/products/womens/women-38.jpg', 'Sale', '', 'Supple full-grain Italian leather bag with brushed gold hardware.', 4.9, 31, 8, 'active'],

    // Template 2: Electronics (8 products)
    ['template2', 1, 'Headphones & Audio', 'Pro Studio Wireless ANC Headphones', 'pro-studio-wireless-anc-headphones', 349.00, 279.00, 'assets/templates/bazaro/img/electronics/arrival-product/product-1/product-1.jpg', 'assets/templates/bazaro/img/electronics/arrival-product/product-1/product-1.jpg', '-20%', '', 'Active noise cancellation with 40-hour battery and spatial audio.', 4.9, 64, 1, 'active'],
    ['template2', 1, 'Smart Watches', 'Titanium Smart Watch Ultra GPS', 'titanium-smart-watch-ultra-gps', 499.00, 429.00, 'assets/templates/bazaro/img/electronics/arrival-product/product-2/product-1.jpg', 'assets/templates/bazaro/img/electronics/arrival-product/product-2/product-1.jpg', 'Hot', '', 'Aerospace-grade titanium case with dual-frequency GPS and ECG.', 4.8, 42, 2, 'active'],
    ['template2', 1, '5G Smartphones', 'Flagship 5G Smartphone 256GB OLED', 'flagship-5g-smartphone-256gb', 999.00, 899.00, 'assets/templates/bazaro/img/electronics/arrival-product/product-3/product-1.jpg', 'assets/templates/bazaro/img/electronics/arrival-product/product-3/product-1.jpg', 'Sale', '', '120Hz LTPO AMOLED display with 200MP pro camera system.', 4.9, 89, 3, 'active'],
    ['template2', 1, 'Laptops & MacBooks', 'UltraBook Pro 16" M4 Max 32GB', 'ultrabook-pro-16-m4-max', 1899.00, 1699.00, 'assets/templates/bazaro/img/electronics/arrival-product/product-4/product-1.jpg', 'assets/templates/bazaro/img/electronics/arrival-product/product-4/product-1.jpg', '-11%', '', 'Next-generation silicon workstation laptop with Liquid Retina display.', 5.0, 53, 4, 'active'],
    ['template2', 1, 'Cameras & Drones', '4K Mirrorless Full-Frame Camera', '4k-mirrorless-full-frame-camera', 1299.00, 1149.00, 'assets/templates/bazaro/img/electronics/arrival-product/product-5/product-1.jpg', 'assets/templates/bazaro/img/electronics/arrival-product/product-5/product-1.jpg', 'Sale', '', '33MP full-frame CMOS sensor with 10-bit 4K 60p video recording.', 4.9, 27, 5, 'active'],
    ['template2', 1, 'Gaming Consoles', 'DualSense Pro Wireless Controller', 'dualsense-pro-wireless-controller', 89.00, 69.00, 'assets/templates/bazaro/img/electronics/arrival-product/product-6/product-1.jpg', 'assets/templates/bazaro/img/electronics/arrival-product/product-6/product-1.jpg', 'New', '', 'Haptic feedback and adaptive triggers for immersive next-gen gaming.', 4.8, 76, 6, 'active'],
    ['template2', 1, 'Headphones & Audio', '360° Hi-Fi Smart Home Speaker', '360-hifi-smart-home-speaker', 199.00, 159.00, 'assets/templates/bazaro/img/electronics/arrival-product/product-7/product-1.jpg', 'assets/templates/bazaro/img/electronics/arrival-product/product-7/product-1.jpg', 'Sale', '', 'Room-filling acoustic sound with built-in voice assistant.', 4.7, 38, 7, 'active'],
    ['template2', 1, 'Headphones & Audio', 'True Wireless ANC Earbuds Pro 2', 'true-wireless-anc-earbuds-pro-2', 249.00, 199.00, 'assets/templates/bazaro/img/electronics/arrival-product/product-8/product-1.jpg', 'assets/templates/bazaro/img/electronics/arrival-product/product-8/product-1.jpg', '-20%', '', 'Adaptive transparency mode with MagSafe wireless charging case.', 4.9, 95, 8, 'active'],

    // Template 3: Grocery (8 products)
    ['template3', 1, 'Fresh Vegetables', 'Fresh Organic Green Broccoli', 'fresh-organic-green-broccoli', 4.99, 3.49, 'assets/templates/bazaro/img/product/grocery-p-1.jpg', 'assets/templates/bazaro/img/product/grocery-p-1.jpg', 'Organic', '500g Pack', 'Crisp farm-fresh organic broccoli crowns rich in vitamins.', 4.9, 44, 1, 'active'],
    ['template3', 1, 'Organic Fruits', 'Creamy Hass Avocados (4 Pack)', 'creamy-hass-avocados-4-pack', 7.99, 5.99, 'assets/templates/bazaro/img/product/grocery-p-2.jpg', 'assets/templates/bazaro/img/product/grocery-p-2.jpg', 'Fresh', '4 pcs (800g)', 'Naturally ripened organic Hass avocados from sustainable orchards.', 4.9, 62, 2, 'active'],
    ['template3', 1, 'Organic Fruits', 'Sweet Red Strawberries Box', 'sweet-red-strawberries-box', 6.50, 4.99, 'assets/templates/bazaro/img/product/grocery-p-3.jpg', 'assets/templates/bazaro/img/product/grocery-p-3.jpg', 'Sale', '400g Punnet', 'Hand-picked sweet and juicy organic red strawberries.', 4.8, 51, 3, 'active'],
    ['template3', 1, 'Dairy & Farm Eggs', 'Free-Range Organic Brown Eggs', 'free-range-organic-brown-eggs', 5.99, 4.79, 'assets/templates/bazaro/img/product/grocery-p-4.jpg', 'assets/templates/bazaro/img/product/grocery-p-4.jpg', 'Farm Fresh', '12 Large Eggs', 'Pasture-raised organic brown eggs with rich golden yolks.', 5.0, 39, 4, 'active'],
    ['template3', 1, 'Bakery & Breads', 'Artisan Country Sourdough Loaf', 'artisan-country-sourdough-loaf', 6.99, 5.49, 'assets/templates/bazaro/img/product/grocery-p-5.jpg', 'assets/templates/bazaro/img/product/grocery-p-5.jpg', 'Baked Today', '650g Loaf', 'Slow-fermented stone-baked sourdough bread with a crisp crust.', 4.9, 28, 5, 'active'],
    ['template3', 1, 'Organic Juices', '100% Cold-Pressed Orange Juice', 'cold-pressed-orange-juice', 8.50, 6.99, 'assets/templates/bazaro/img/product/grocery-p-6.jpg', 'assets/templates/bazaro/img/product/grocery-p-6.jpg', 'No Sugar', '1 Liter Bottle', 'Pure freshly squeezed Valencia oranges with zero added sugar.', 4.8, 33, 6, 'active'],
    ['template3', 1, 'Organic Fruits', 'Organic Honeycrisp Apples', 'organic-honeycrisp-apples', 5.99, 4.50, 'assets/templates/bazaro/img/product/grocery-p-7.jpg', 'assets/templates/bazaro/img/product/grocery-p-7.jpg', 'Crisp', '1 kg Bag', 'Sweet, ultra-crisp orchard apples ideal for snacking.', 4.9, 47, 7, 'active'],
    ['template3', 1, 'Fresh Meats & Fish', 'Wild-Caught Atlantic Salmon Fillet', 'wild-caught-atlantic-salmon', 16.99, 13.99, 'assets/templates/bazaro/img/product/grocery-p-8.jpg', 'assets/templates/bazaro/img/product/grocery-p-8.jpg', 'Omega-3', '500g Fillet', 'Sustainably sourced fresh Atlantic salmon fillet, skin-on.', 4.9, 36, 8, 'active'],
];

$insProd = $pdo->prepare("INSERT INTO `template_products`
    (`template_key`, `layout_number`, `category_name`, `name`, `slug`, `price`, `sale_price`, `image`, `hover_image`, `badge_text`, `unit_label`, `short_desc`, `rating`, `reviews_count`, `sort_order`, `status`)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
foreach ($prods as $p) {
    $insProd->execute($p);
}

echo "Template Customizer tables seeded with verified asset paths!\n";
echo "Hero Banners: " . $pdo->query("SELECT COUNT(*) FROM `template_hero_banners`")->fetchColumn() . "\n";
echo "Categories:   " . $pdo->query("SELECT COUNT(*) FROM `template_categories`")->fetchColumn() . "\n";
echo "Products:     " . $pdo->query("SELECT COUNT(*) FROM `template_products`")->fetchColumn() . "\n";
