<?php
define('BASEPATH', '1');
require 'C:/xampp/htdocs/ecommerce/website/application/libraries/Static_template_data.php';

$pdo = new PDO('mysql:host=localhost;dbname=ecommerce_main;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 1. Seed template1 hero banners from Static_template_data
$pdo->exec("DELETE FROM template_hero_banners WHERE template_key = 'template1'");
$banners = Static_template_data::get('banners');
$stmtB = $pdo->prepare("INSERT INTO template_hero_banners (template_key, layout_number, badge, title, description, button_text, button_url, image, background_image, sort_order, status) VALUES ('template1', 1, ?, ?, ?, ?, ?, ?, '', ?, 'active')");
foreach ($banners as $i => $b) {
    $img = ltrim((string)$b['image'], '/');
    if (strpos($img, 'assets/images/') !== 0) {
        $img = 'assets/images/' . $img;
    }
    $stmtB->execute([
        $b['subtitle'] ?? 'NEW COLLECTION',
        $b['title'],
        $b['description'] ?? '',
        $b['button_text'] ?: 'Explore Collection',
        $b['button_link'] ?: 'shop',
        $img,
        $i + 1
    ]);
}

// 2. Seed template1 categories (first 10 categories) from Static_template_data
$pdo->exec("DELETE FROM template_categories WHERE template_key = 'template1'");
$cats = array_slice(Static_template_data::get('category_tree'), 0, 10);
$stmtC = $pdo->prepare("INSERT INTO template_categories (template_key, layout_number, name, slug, subtitle, image, sort_order, status) VALUES ('template1', 1, ?, ?, ?, ?, ?, 'active')");
foreach ($cats as $i => $c) {
    $fallback = 'collections/collection-circle/cls-circle' . ((($i + 1) % 17) ?: 1) . '.jpg';
    $rawImg = !empty($c['image']) ? $c['image'] : $fallback;
    $rawImg = ltrim((string)$rawImg, '/');
    if (strpos($rawImg, 'assets/images/') !== 0) {
        $rawImg = 'assets/images/' . $rawImg;
    }
    $stmtC->execute([
        $c['name'],
        $c['slug'],
        ($c['product_count'] ?? 12) . ' Items',
        $rawImg,
        $i + 1
    ]);
}

// 3. Seed template1 products (11 products) from Static_template_data
$pdo->exec("DELETE FROM template_products WHERE template_key = 'template1'");
$prods = Static_template_data::get('products');
$stmtP = $pdo->prepare("INSERT INTO template_products (template_key, layout_number, category_name, name, slug, price, sale_price, image, hover_image, badge_text, unit_label, short_desc, rating, reviews_count, sort_order, status) VALUES ('template1', 1, ?, ?, ?, ?, ?, ?, ?, ?, '', ?, ?, ?, ?, 'active')");
foreach ($prods as $i => $p) {
    $img = ltrim((string)$p['main_image'], '/');
    if (strpos($img, 'assets/images/') !== 0) {
        $img = 'assets/images/' . $img;
    }
    $hover = !empty($p['gallery_images_decoded'][0]) ? ltrim((string)$p['gallery_images_decoded'][0], '/') : $img;
    if (strpos($hover, 'assets/images/') !== 0) {
        $hover = 'assets/images/' . $hover;
    }
    $badge = (!empty($p['sale_price']) && $p['sale_price'] < $p['price'])
        ? ('-' . round((($p['price'] - $p['sale_price']) / $p['price']) * 100) . '%')
        : '';
    $stmtP->execute([
        $p['category_name'] ?? 'Fashion',
        $p['title'],
        $p['slug'],
        (float)$p['price'],
        (!empty($p['sale_price']) && (float)$p['sale_price'] > 0) ? (float)$p['sale_price'] : null,
        $img,
        $hover,
        $badge,
        $p['short_description'] ?? '',
        (float)($p['rating'] ?? 4.8),
        (int)($p['reviews_count'] ?? 12),
        $i + 1
    ]);
}
echo "Seeded template1: " . count($banners) . " banners, " . count($cats) . " categories, " . count($prods) . " products.\n";
