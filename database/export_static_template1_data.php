<?php
/**
 * Reads exact catalog, category tree, product detail, banners, brands, and settings from `ecommerce_single`
 * and generates `C:\xampp\htdocs\ecommerce\website\application\libraries\Static_template_data.php`
 * so Template 1 (Fashion) runs 100% on static data with zero database queries!
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pdo = new PDO('mysql:host=localhost;dbname=ecommerce_single;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// 1. Settings
$settings = [];
foreach ($pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll() as $r) {
    $settings[$r['setting_key']] = $r['setting_value'];
}
$settings['site_name']       = $settings['site_name'] ?? 'Modave Fashion';
$settings['currency_symbol'] = $settings['currency_symbol'] ?? '₹';
$settings['currency_code']   = $settings['currency_code'] ?? 'INR';

// 2. Banners
$banners = $pdo->query("SELECT * FROM banners WHERE status = 'active' ORDER BY sort_order ASC, id ASC")->fetchAll();

// 3. Categories & Category Tree
$allCats = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.status = 'published') as product_count FROM categories c WHERE c.status = 'active' ORDER BY c.sort_order ASC, c.name ASC")->fetchAll();

function buildCatTree(array $elements, $parentId = null) {
    $branch = [];
    foreach ($elements as $element) {
        $elParent = !empty($element['parent_id']) ? (int)$element['parent_id'] : null;
        if ($elParent === $parentId) {
            $children = buildCatTree($elements, (int)$element['id']);
            if ($children) {
                $element['children'] = $children;
            } else {
                $element['children'] = [];
            }
            $branch[] = $element;
        }
    }
    return $branch;
}
$categoryTree = buildCatTree($allCats, null);
$rootCategories = array_values(array_filter($allCats, function($c) { return empty($c['parent_id']); }));

// 4. Brands
$brands = $pdo->query("SELECT b.*, (SELECT COUNT(*) FROM products p WHERE p.brand_id = b.id AND p.status = 'published') as product_count FROM brands b WHERE b.status = 'active' ORDER BY b.name ASC")->fetchAll();

// 5. Products (with color swatches & gallery images matching Product_model::get_products)
$rawProducts = $pdo->query("SELECT p.*, c.name as category_name, c.slug as category_slug, b.name as brand_name, b.slug as brand_slug
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN brands b ON b.id = p.brand_id
    WHERE p.status = 'published'
    ORDER BY p.id ASC")->fetchAll();

$products = [];
foreach ($rawProducts as $p) {
    $pid = (int)$p['id'];
    $p['gallery_images_decoded'] = !empty($p['gallery_images']) ? (json_decode($p['gallery_images'], true) ?: []) : [];
    $p['highlights_decoded']     = !empty($p['highlights']) ? (json_decode($p['highlights'], true) ?: []) : [];

    // Fetch variants
    $vStmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY id ASC");
    $vStmt->execute([$pid]);
    $variants = $vStmt->fetchAll();

    foreach ($variants as &$v) {
        $vvStmt = $pdo->prepare("SELECT pvv.*, av.value as attribute_value, av.color_code, a.name as attribute_name, a.slug as attribute_slug, a.type as attribute_type
            FROM product_variant_values pvv
            JOIN attribute_values av ON av.id = pvv.attribute_value_id
            JOIN attributes a ON a.id = av.attribute_id
            WHERE pvv.variant_id = ?");
        $vvStmt->execute([(int)$v['id']]);
        $v['values'] = $vvStmt->fetchAll();
    }
    unset($v);
    $p['variants'] = $variants;

    // Build color_swatches for product card
    $swatches = [];
    foreach ($variants as $v) {
        foreach ($v['values'] as $val) {
            if (strtolower($val['attribute_slug']) === 'color') {
                $cName = $val['attribute_value'];
                if (!isset($swatches[$cName])) {
                    $swatches[$cName] = [
                        'name'       => $cName,
                        'value'      => $cName,
                        'color_code' => $val['color_code'] ?: '#222222',
                        'image'      => !empty($v['image']) ? $v['image'] : $p['main_image'],
                    ];
                }
            }
        }
    }
    $p['color_swatches'] = array_values($swatches);

    // Build attributes map for product detail
    $attrsMap = [];
    foreach ($variants as $v) {
        foreach ($v['values'] as $val) {
            $aSlug = $val['attribute_slug'];
            if (!isset($attrsMap[$aSlug])) {
                $attrsMap[$aSlug] = [
                    'name'   => $val['attribute_name'],
                    'slug'   => $aSlug,
                    'type'   => $val['attribute_type'],
                    'values' => []
                ];
            }
            $valId = (int)$val['attribute_value_id'];
            $exists = false;
            foreach ($attrsMap[$aSlug]['values'] as $ev) {
                if ((int)$ev['id'] === $valId) { $exists = true; break; }
            }
            if (!$exists) {
                $attrsMap[$aSlug]['values'][] = [
                    'id'         => $valId,
                    'value'      => $val['attribute_value'],
                    'color_code' => $val['color_code']
                ];
            }
        }
    }
    $p['attributes'] = $attrsMap;

    // Specifications
    $spStmt = $pdo->prepare("SELECT * FROM product_specifications WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
    $spStmt->execute([$pid]);
    $p['specifications'] = $spStmt->fetchAll();

    // Reviews
    $rvStmt = $pdo->prepare("SELECT * FROM reviews WHERE product_id = ? AND status = 'approved' ORDER BY id DESC");
    $rvStmt->execute([$pid]);
    $p['reviews'] = $rvStmt->fetchAll();
    if (empty($p['reviews'])) {
        $p['reviews'] = [
            [
                'id'             => 1,
                'product_id'     => $pid,
                'user_id'        => 1,
                'customer_name'  => 'Sophia Reynolds',
                'customer_email' => 'sophia@example.com',
                'rating'         => 5,
                'title'          => 'Exceptional fabric quality and perfect fit!',
                'review'         => 'Ordered this last week and I am genuinely impressed with the stitching, breathable fabric, and rich color. Fits true to size!',
                'images'         => null,
                'status'         => 'approved',
                'created_at'     => '2026-09-14 14:20:00'
            ],
            [
                'id'             => 2,
                'product_id'     => $pid,
                'user_id'        => 2,
                'customer_name'  => 'Aarav Mehta',
                'customer_email' => 'aarav@example.com',
                'rating'         => 5,
                'title'          => 'Value for money & fast delivery',
                'review'         => 'Looks exactly like the photos. Very comfortable for all-day wear and the packaging was premium.',
                'images'         => null,
                'status'         => 'approved',
                'created_at'     => '2026-09-16 10:15:00'
            ]
        ];
    }

    $products[] = $p;
}

// Pick the best product with variants as the Common Product Detail item!
$commonProduct = $products[0];
foreach ($products as $cand) {
    if (!empty($cand['variants']) && !empty($cand['attributes']['color'])) {
        $commonProduct = $cand;
        break;
    }
}

// Filter sizes & colors for Shop sidebar
$filterSizes = [
    ['id' => 1, 'value' => 'S', 'product_count' => 8],
    ['id' => 2, 'value' => 'M', 'product_count' => 11],
    ['id' => 3, 'value' => 'L', 'product_count' => 10],
    ['id' => 4, 'value' => 'XL', 'product_count' => 7],
    ['id' => 5, 'value' => 'XXL', 'product_count' => 4],
];
$filterColors = [
    ['id' => 10, 'value' => 'Black', 'color_code' => '#111111', 'product_count' => 7],
    ['id' => 11, 'value' => 'White', 'color_code' => '#ffffff', 'product_count' => 6],
    ['id' => 12, 'value' => 'Navy Blue', 'color_code' => '#1e3a8a', 'product_count' => 5],
    ['id' => 13, 'value' => 'Olive Green', 'color_code' => '#3f6212', 'product_count' => 4],
    ['id' => 14, 'value' => 'Beige', 'color_code' => '#d6d3d1', 'product_count' => 5],
    ['id' => 15, 'value' => 'Crimson Red', 'color_code' => '#b91c1c', 'product_count' => 3],
];

// Static Logged-In Customer (so navbar always shows After-Login Dropdown Menu, never Login page)
$staticUser = [
    'id'         => 1,
    'first_name' => 'Alex',
    'last_name'  => 'Morgan',
    'email'      => 'alex.morgan@example.com',
    'phone'      => '+91 98765 43210',
    'avatar'     => '',
    'status'     => 'active',
    'created_at' => '2026-09-01 10:00:00'
];

// Static Addresses
$staticAddresses = [
    [
        'id'              => 1,
        'user_id'         => 1,
        'type'            => 'shipping',
        'first_name'      => 'Alex',
        'last_name'       => 'Morgan',
        'company'         => 'HOME',
        'address_1'       => 'Flat 402, Emerald Heights, Park Street',
        'address_2'       => 'Near Metro Station',
        'landmark'        => 'Opposite Central Plaza',
        'city'            => 'Kolkata',
        'state'           => 'West Bengal',
        'postcode'        => '700016',
        'country'         => 'India',
        'phone'           => '9876543210',
        'alternate_phone' => '9123456780',
        'is_default'      => 1
    ],
    [
        'id'              => 2,
        'user_id'         => 1,
        'type'            => 'shipping',
        'first_name'      => 'Alex',
        'last_name'       => 'Morgan',
        'company'         => 'WORK',
        'address_1'       => 'Suite 8B, Technopolis IT Park, Sector V',
        'address_2'       => 'Salt Lake City',
        'landmark'        => 'Near Webel More',
        'city'            => 'Kolkata',
        'state'           => 'West Bengal',
        'postcode'        => '700091',
        'country'         => 'India',
        'phone'           => '9876543210',
        'alternate_phone' => '',
        'is_default'      => 0
    ]
];

// Static Cart Items (using first 2 products)
$pA = $products[0];
$pB = $products[1] ?? $products[0];
$pA_price = !empty($pA['sale_price']) ? (float)$pA['sale_price'] : (float)$pA['price'];
$pB_price = !empty($pB['sale_price']) ? (float)$pB['sale_price'] : (float)$pB['price'];
$pA_reg   = (float)$pA['price'];
$pB_reg   = (float)$pB['price'];

$staticCartItems = [
    (string)$pA['id'] => [
        'cart_key'         => (string)$pA['id'],
        'id'               => (int)$pA['id'],
        'product_id'       => (int)$pA['id'],
        'variant_id'       => null,
        'title'            => $pA['title'],
        'slug'             => $pA['slug'],
        'sku'              => $pA['sku'],
        'image'            => $pA['main_image'],
        'price'            => $pA_price,
        'regular_price'    => $pA_reg,
        'unit_base_price'  => $pA_price,
        'unit_tax_amount'  => 0.00,
        'tax_rate'         => 0.00,
        'quantity'         => 1,
        'stock_quantity'   => 25,
        'max_qty'          => 10,
        'variant_name'     => 'Color: Black | Size: M',
        'color'            => 'Black',
        'size'             => 'M',
        'is_cod_allowed'   => 1,
        'total'            => $pA_price,
        'mrp_total'        => $pA_reg,
    ],
    (string)$pB['id'] => [
        'cart_key'         => (string)$pB['id'],
        'id'               => (int)$pB['id'],
        'product_id'       => (int)$pB['id'],
        'variant_id'       => null,
        'title'            => $pB['title'],
        'slug'             => $pB['slug'],
        'sku'              => $pB['sku'],
        'image'            => $pB['main_image'],
        'price'            => $pB_price,
        'regular_price'    => $pB_reg,
        'unit_base_price'  => $pB_price,
        'unit_tax_amount'  => 0.00,
        'tax_rate'         => 0.00,
        'quantity'         => 1,
        'stock_quantity'   => 18,
        'max_qty'          => 10,
        'variant_name'     => 'Color: White | Size: L',
        'color'            => 'White',
        'size'             => 'L',
        'is_cod_allowed'   => 1,
        'total'            => $pB_price,
        'mrp_total'        => $pB_reg,
    ],
];

$subTotal = $pA_price + $pB_price;
$mrpTotal = max($subTotal, $pA_reg + $pB_reg);
$mrpDisc  = max(0, $mrpTotal - $subTotal);

$staticCartSummary = [
    'item_count'          => 2,
    'mrp_total'           => $mrpTotal,
    'mrp_discount'        => $mrpDisc,
    'subtotal'            => $subTotal,
    'base_subtotal'       => $subTotal,
    'tax'                 => 0.00,
    'tax_inclusive'       => true,
    'discount'            => 0.00,
    'coupon_code'         => '',
    'shipping'            => 0.00,
    'shipping_method'     => 'standard',
    'shipping_breakdown'  => [['name' => 'Standard Delivery', 'value' => 0]],
    'free_shipping_min'   => 499.00,
    'amount_to_free_ship' => 0.00,
    'free_ship_percent'   => 100,
    'total'               => $subTotal,
    'total_savings'       => $mrpDisc,
];

// Static Common Order & Orders List
$commonOrder = [
    'id'               => 101,
    'order_number'     => 'ORD-2026-88412',
    'user_id'          => 1,
    'customer_name'    => 'Alex Morgan',
    'customer_email'   => 'alex.morgan@example.com',
    'customer_phone'   => '9876543210',
    'address_type'     => 'HOME',
    'shipping_address' => "Alex Morgan (HOME)\nFlat 402, Emerald Heights, Park Street\nKolkata, West Bengal 700016\nIndia\nPhone: 9876543210",
    'billing_address'  => "Alex Morgan (HOME)\nFlat 402, Emerald Heights, Park Street\nKolkata, West Bengal 700016\nIndia\nPhone: 9876543210",
    'subtotal'         => $subTotal,
    'discount_amount'  => $mrpDisc,
    'shipping_fee'     => 0.00,
    'shipping_amount'  => 0.00,
    'tax_amount'       => 0.00,
    'total_amount'     => $subTotal,
    'currency'         => $settings['currency_code'],
    'payment_method'   => 'razorpay',
    'payment_status'   => 'paid',
    'order_status'     => 'delivered',
    'courier_name'     => 'BlueDart Express',
    'tracking_number'  => 'BD884920192IN',
    'tracking_url'     => 'https://www.bluedart.com/tracking',
    'shipped_at'       => '2026-09-15 11:30:00',
    'delivered_at'     => '2026-09-18 16:45:00',
    'created_at'       => '2026-09-14 09:15:00',
    'updated_at'       => '2026-09-18 16:45:00',
    'items'            => [
        [
            'id'              => 501,
            'order_id'        => 101,
            'product_id'      => (int)$pA['id'],
            'product_title'   => $pA['title'],
            'product_slug'    => $pA['slug'],
            'product_image'   => $pA['main_image'],
            'sku'             => $pA['sku'],
            'variant_title'   => 'Color: Black | Size: M',
            'price'           => $pA_price,
            'regular_price'   => $pA_reg,
            'quantity'        => 1,
            'total'           => $pA_price,
            'item_status'     => 'delivered',
            'courier_name'    => 'BlueDart Express',
            'tracking_number' => 'BD884920192IN',
            'tracking_url'    => 'https://www.bluedart.com/tracking',
            'shipped_at'      => '2026-09-15 11:30:00',
            'delivered_at'    => '2026-09-18 16:45:00',
        ],
        [
            'id'              => 502,
            'order_id'        => 101,
            'product_id'      => (int)$pB['id'],
            'product_title'   => $pB['title'],
            'product_slug'    => $pB['slug'],
            'product_image'   => $pB['main_image'],
            'sku'             => $pB['sku'],
            'variant_title'   => 'Color: White | Size: L',
            'price'           => $pB_price,
            'regular_price'   => $pB_reg,
            'quantity'        => 1,
            'total'           => $pB_price,
            'item_status'     => 'delivered',
            'courier_name'    => 'BlueDart Express',
            'tracking_number' => 'BD884920192IN',
            'tracking_url'    => 'https://www.bluedart.com/tracking',
            'shipped_at'      => '2026-09-15 11:30:00',
            'delivered_at'    => '2026-09-18 16:45:00',
        ]
    ]
];

$staticOrders = [
    $commonOrder,
    array_merge($commonOrder, [
        'id'           => 102,
        'order_number' => 'ORD-2026-77319',
        'order_status' => 'shipped',
        'created_at'   => '2026-09-20 14:10:00',
        'updated_at'   => '2026-09-21 10:00:00',
    ])
];

$staticNotifications = [
    [
        'id'         => 1,
        'user_id'    => 1,
        'title'      => 'Order Delivered Successfully',
        'message'    => 'Your order #ORD-2026-88412 has been delivered. Thank you for shopping with us!',
        'type'       => 'order',
        'is_read'    => 1,
        'created_at' => '2026-09-18 16:45:00'
    ],
    [
        'id'         => 2,
        'user_id'    => 1,
        'title'      => 'Season Finale Sale is Live!',
        'message'    => 'Enjoy up to 40% off on our new Autumn/Winter Fashion Collection.',
        'type'       => 'promo',
        'is_read'    => 0,
        'created_at' => '2026-09-20 09:00:00'
    ]
];

$staticGateways = [
    ['gateway_code' => 'razorpay', 'gateway_name' => 'Razorpay (UPI, Cards, NetBanking)', 'is_active' => 1],
    ['gateway_code' => 'stripe', 'gateway_name' => 'Stripe (Credit / Debit Card)', 'is_active' => 1],
    ['gateway_code' => 'cod', 'gateway_name' => 'Cash on Delivery (COD)', 'is_active' => 1],
];

$exportData = [
    'settings'        => $settings,
    'banners'         => $banners,
    'all_categories'  => $allCats,
    'root_categories' => $rootCategories,
    'category_tree'   => $categoryTree,
    'brands'          => $brands,
    'products'        => $products,
    'common_product'  => $commonProduct,
    'filter_sizes'    => $filterSizes,
    'filter_colors'   => $filterColors,
    'user'            => $staticUser,
    'addresses'       => $staticAddresses,
    'cart_items'      => $staticCartItems,
    'cart_summary'    => $staticCartSummary,
    'common_order'    => $commonOrder,
    'orders'          => $staticOrders,
    'notifications'   => $staticNotifications,
    'gateways'        => $staticGateways,
];

$code = "<?php\ndefined('BASEPATH') OR exit('No direct script access allowed');\n\n"
      . "/**\n * Static_template_data\n * Provides 100% static template data for Template 1 (Fashion) cloned from ecommerce-single/website\n * with zero database queries required.\n */\n"
      . "class Static_template_data {\n\n"
      . "    public static function get_all() {\n"
      . "        static \$data = null;\n"
      . "        if (\$data === null) {\n"
      . "            \$data = " . var_export($exportData, true) . ";\n"
      . "        }\n"
      . "        return \$data;\n"
      . "    }\n\n"
      . "    public static function get(\$key, \$default = null) {\n"
      . "        \$all = self::get_all();\n"
      . "        return \$all[\$key] ?? \$default;\n"
      . "    }\n"
      . "}\n";

$targetFile = 'C:/xampp/htdocs/ecommerce/website/application/libraries/Static_template_data.php';
file_put_contents($targetFile, $code);
echo "Generated $targetFile (" . strlen($code) . " bytes)\n";
