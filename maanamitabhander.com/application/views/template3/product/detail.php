<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$bazaro = isset($tpl_assets) ? rtrim($tpl_assets, '/') . '/' : base_url('assets/templates/bazaro/');
$currency_symbol = isset($currency_symbol) ? $currency_symbol : '₹';

if (!function_exists('grc_detail_resolve_img')) {
    function grc_detail_resolve_img($img, $bazaro_url, $fallback_idx = 3) {
        if (!empty($img)) {
            if (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0) return $img;
            $clean = ltrim($img, '/');
            if (strpos($clean, 'assets/') === 0 && file_exists(FCPATH . $clean)) {
                return base_url($clean);
            }
            if (file_exists(FCPATH . 'assets/images/' . $clean)) {
                return base_url('assets/images/' . $clean);
            }
            if (file_exists(FCPATH . 'assets/templates/bazaro/img/' . $clean)) {
                return $bazaro_url . 'img/' . $clean;
            }
            if (file_exists(FCPATH . 'assets/images/products/' . $clean)) {
                return base_url('assets/images/products/' . $clean);
            }
        }
        $idx = (($fallback_idx - 1) % 16) + 1;
        return $bazaro_url . 'img/product/grocery-p-' . $idx . '.jpg';
    }
}

/**
 * Parse a grocery unit label (e.g. "400g Punnet", "500g Pack", "1 kg Bag", "4 pcs (800g)", "12 Large Eggs", "1 pc", "kg", "pc")
 * and return normalized unit metadata + calculated per-kg or per-pc unit price.
 */
if (!function_exists('grc_calculate_unit_pricing')) {
    function grc_calculate_unit_pricing($unit_label, $price, $force_unit_type = null) {
        $raw = trim((string)$unit_label);
        $lower = strtolower($raw);
        $price = (float)$price;

        $unit_type = 'kg'; // 'kg' or 'pc'
        $qty_in_unit = 1.0; // weight in kg OR count in pcs
        $display_pack = $raw !== '' ? $raw : '1 kg';

        if ($force_unit_type === 'pc' || $force_unit_type === 'kg') {
            $unit_type = $force_unit_type;
        } else {
            // Detect if unit is piece-based ('pc', 'pcs', 'piece', 'pieces', 'egg', 'eggs', 'loaf', 'bun')
            if (preg_match('/\b(pc|pcs|piece|pieces|egg|eggs|loaf|loaves|count|unit|units)\b/i', $lower)) {
                $unit_type = 'pc';
            } elseif (preg_match('/\b(kg|kgs|kilogram|kilograms|g|gm|gms|gram|grams)\b/i', $lower)) {
                $unit_type = 'kg';
            }
        }

        if ($unit_type === 'pc') {
            if (preg_match('/(\d+(?:\.\d+)?)\s*(?:pc|pcs|piece|pieces|large\s+eggs|eggs|egg|loaf|count|pack)?/i', $lower, $m)) {
                $qty_in_unit = max(1.0, (float)$m[1]);
            } else {
                $qty_in_unit = 1.0;
            }
            $unit_rate = $qty_in_unit > 0 ? round($price / $qty_in_unit, 2) : $price;
            $unit_suffix = 'pc';
            $unit_Heading = 'Per Pc Price';
            $qty_formatted = ((int)$qty_in_unit == $qty_in_unit) ? (int)$qty_in_unit : number_format($qty_in_unit, 1);
            $formula_text = $qty_formatted . ' ' . ($qty_in_unit > 1 ? 'pcs' : 'pc');
        } else {
            // Weight in kg or g
            if (preg_match('/(\d+(?:\.\d+)?)\s*(kg|kgs|kilogram|kilograms)\b/i', $lower, $m)) {
                $qty_in_unit = max(0.01, (float)$m[1]);
            } elseif (preg_match('/(\d+(?:\.\d+)?)\s*(g|gm|gms|gram|grams)\b/i', $lower, $m)) {
                $qty_in_unit = max(0.01, ((float)$m[1]) / 1000.0);
            } elseif (preg_match('/(\d+(?:\.\d+)?)/', $lower, $m)) {
                $val = (float)$m[1];
                $qty_in_unit = ($val >= 50) ? ($val / 1000.0) : max(0.01, $val);
            } else {
                $qty_in_unit = 1.0;
            }
            $unit_rate = $qty_in_unit > 0 ? round($price / $qty_in_unit, 2) : $price;
            $unit_suffix = 'kg';
            $unit_Heading = 'Per Kg Price';
            $grams = round($qty_in_unit * 1000);
            $formula_text = ($qty_in_unit < 1)
                ? ($grams . 'g (' . rtrim(rtrim(number_format($qty_in_unit, 2), '0'), '.') . ' kg)')
                : (rtrim(rtrim(number_format($qty_in_unit, 2), '0'), '.') . ' kg');
        }

        return [
            'unit_type'    => $unit_type,       // 'kg' or 'pc'
            'qty_in_unit'  => $qty_in_unit,     // e.g. 0.4 (kg) or 12 (pc)
            'unit_rate'    => $unit_rate,       // price per 1 kg or per 1 pc
            'unit_suffix'  => $unit_suffix,     // 'kg' or 'pc'
            'unit_heading' => $unit_Heading,    // 'Per Kg Price' or 'Per Pc Price'
            'display_pack' => $display_pack,
            'formula_text' => $formula_text,
        ];
    }
}

// Product core details
$grc_qs        = empty($is_user_project) ? '?preview_tpl=grocery' : '';
$prod_id       = (int)($product['id'] ?? 19);
$prod_slug     = !empty($product['slug']) ? $product['slug'] : 'sweet-red-strawberries-box';
$prod_title    = !empty($product['name']) ? $product['name'] : (!empty($product['title']) ? $product['title'] : 'Sweet Red Strawberries Box');
$prod_cat      = !empty($product['category_name']) ? $product['category_name'] : 'Organic Fruits';
$prod_cat_slug = !empty($product['category_slug']) ? $product['category_slug'] : url_title(strtolower($prod_cat), '-', TRUE);
$prod_brand    = !empty($product['brand_name']) && $product['brand_name'] !== 'Modave Luxe' ? $product['brand_name'] : 'NatureFresh Organic Farms';
$prod_sku      = !empty($product['sku']) && strpos($product['sku'], 'MOD-') !== 0 ? $product['sku'] : ('GRC-ORG-' . str_pad((string)$prod_id, 3, '0', STR_PAD_LEFT));
$prod_unit_raw = !empty($product['unit_label']) ? $product['unit_label'] : (!empty($product['unit_value']) ? $product['unit_value'] : '400g');
$prod_badge    = !empty($product['badge_text']) ? $product['badge_text'] : '100% Organic';
$prod_stock    = isset($product['stock_quantity']) ? (int)$product['stock_quantity'] : 25;

// Allow optional URL query ?unit=pc or ?unit=kg for quick preview/testing, otherwise auto-detect from product's unit_label
$req_unit = strtolower(trim((string)($_GET['unit'] ?? '')));
$force_unit = in_array($req_unit, ['kg', 'pc'], true) ? $req_unit : null;

$regular_price = (float)($product['price'] ?? 6.50);
$sale_price    = (!empty($product['sale_price']) && (float)$product['sale_price'] > 0) ? (float)$product['sale_price'] : null;
$current_price = ($sale_price && $sale_price < $regular_price) ? $sale_price : $regular_price;
$discount_percent = ($regular_price > 0 && $sale_price && $sale_price < $regular_price)
    ? round((($regular_price - $sale_price) / $regular_price) * 100)
    : 0;

$unit_info = grc_calculate_unit_pricing($prod_unit_raw, $current_price, $force_unit);
$initial_unit_type = $unit_info['unit_type']; // 'kg' or 'pc'

// Build gallery images dynamically from the database product & variants
$primary_img_url = grc_detail_resolve_img($product['primary_image'] ?? ($product['image'] ?? ($product['main_image'] ?? '')), $bazaro, 3);
$gallery_urls = [$primary_img_url];

$raw_gal_list = [];
if (!empty($product['gallery_images_decoded']) && is_array($product['gallery_images_decoded'])) {
    $raw_gal_list = $product['gallery_images_decoded'];
} elseif (!empty($product['gallery_images']) && is_string($product['gallery_images'])) {
    $dec = json_decode($product['gallery_images'], true);
    if (is_array($dec)) {
        $raw_gal_list = $dec;
    }
}
foreach ($raw_gal_list as $g_item) {
    if (!empty($g_item)) {
        $resolved_g = grc_detail_resolve_img($g_item, $bazaro, 3);
        if (!in_array($resolved_g, $gallery_urls, true)) {
            $gallery_urls[] = $resolved_g;
        }
    }
}
if (!empty($product['variants']) && is_array($product['variants'])) {
    foreach ($product['variants'] as $v_row) {
        if (!empty($v_row['image'])) {
            $rv_img = grc_detail_resolve_img($v_row['image'], $bazaro, 3);
            if (!in_array($rv_img, $gallery_urls, true)) {
                $gallery_urls[] = $rv_img;
            }
        }
        if (!empty($v_row['gallery_images'])) {
            $v_gal = is_array($v_row['gallery_images']) ? $v_row['gallery_images'] : (json_decode((string)$v_row['gallery_images'], true) ?: []);
            foreach ($v_gal as $vg_item) {
                if (!empty($vg_item)) {
                    $rvg = grc_detail_resolve_img($vg_item, $bazaro, 3);
                    if (!in_array($rvg, $gallery_urls, true)) {
                        $gallery_urls[] = $rvg;
                    }
                }
            }
        }
    }
}

// Only add fallback demo gallery images in template demo mode when no gallery images were uploaded
if (empty($is_user_project) && count($gallery_urls) < 2) {
    $grc_extra_imgs = [
        $bazaro . 'img/product/grocery-p-3.jpg',
        $bazaro . 'img/product/grocery-p-7.jpg',
        $bazaro . 'img/product/grocery-p-2.jpg',
        $bazaro . 'img/product/grocery-p-1.jpg',
        $bazaro . 'img/product/grocery-p-6.jpg',
    ];
    foreach ($grc_extra_imgs as $ex_img) {
        if (!in_array($ex_img, $gallery_urls, true) && count($gallery_urls) < 5) {
            $gallery_urls[] = $ex_img;
        }
    }
}

// Build Pack Variants:
// 1) If the product has real variants in `product_variants` (Admin Panel), build from those exact database variants!
// 2) Otherwise, on a user project with a simple product, build a single pack entry from the product's own price & unit_label.
// 3) On the template demo site (or when no DB variants exist in demo mode), provide the showcase kg & pc pack options.
$kg_variants = [];
$pc_variants = [];
$has_db_variants = !empty($product['variants']) && is_array($product['variants']);

if ($has_db_variants) {
    foreach ($product['variants'] as $v_idx => $dbv) {
        $v_id    = (string)($dbv['id'] ?? ('var-' . $v_idx));
        $v_label = trim((string)($dbv['variant_label'] ?? ''));
        if ($v_label === '' && !empty($dbv['values']) && is_array($dbv['values'])) {
            $v_parts = [];
            foreach ($dbv['values'] as $vv) {
                if (!empty($vv['attribute_value'])) {
                    $v_parts[] = $vv['attribute_value'];
                }
            }
            $v_label = implode(' / ', $v_parts);
        }
        if ($v_label === '' && !empty($dbv['title']) && strpos($dbv['title'], '/') !== false) {
            $t_parts = explode('/', $dbv['title']);
            $v_label = trim((string)end($t_parts));
        }
        if ($v_label === '') {
            $v_label = $prod_unit_raw ?: ('Option ' . ($v_idx + 1));
        }

        $v_reg  = (!empty($dbv['price']) && (float)$dbv['price'] > 0) ? (float)$dbv['price'] : $regular_price;
        $v_sale = (!empty($dbv['sale_price']) && (float)$dbv['sale_price'] > 0) ? (float)$dbv['sale_price'] : $v_reg;
        if ($v_reg < $v_sale) {
            $v_reg = $v_sale;
        }
        $v_uinfo = grc_calculate_unit_pricing($v_label, $v_sale, $force_unit);
        $v_entry = [
            'id'          => $v_id,
            'variant_id'  => (int)($dbv['id'] ?? 0),
            'label'       => $v_label,
            'short_label' => $v_label,
            'unit_type'   => $v_uinfo['unit_type'],
            'unit_qty'    => (float)$v_uinfo['qty_in_unit'],
            'unit_desc'   => $v_uinfo['formula_text'],
            'reg_price'   => $v_reg,
            'sale_price'  => $v_sale,
            'sku'         => !empty($dbv['sku']) ? $dbv['sku'] : ($prod_sku . '-' . ($v_idx + 1)),
            'stock'       => isset($dbv['stock_quantity']) ? (int)$dbv['stock_quantity'] : $prod_stock,
            'badge'       => ($v_idx === 0 && count($product['variants']) > 1) ? 'Popular' : ''
        ];
        if ($v_uinfo['unit_type'] === 'pc') {
            $pc_variants[] = $v_entry;
        } else {
            $kg_variants[] = $v_entry;
        }
    }
} elseif (!empty($is_user_project)) {
    // Simple product in a user-setup project: use exact product price, sale_price, unit_label, SKU, and stock
    $s_entry = [
        'id'          => (string)$prod_id,
        'variant_id'  => 0,
        'label'       => $unit_info['display_pack'],
        'short_label' => $unit_info['display_pack'],
        'unit_type'   => $unit_info['unit_type'],
        'unit_qty'    => (float)$unit_info['qty_in_unit'],
        'unit_desc'   => $unit_info['formula_text'],
        'reg_price'   => $regular_price,
        'sale_price'  => $current_price,
        'sku'         => $prod_sku,
        'stock'       => $prod_stock,
        'badge'       => ''
    ];
    if ($unit_info['unit_type'] === 'pc') {
        $pc_variants[] = $s_entry;
    } else {
        $kg_variants[] = $s_entry;
    }
}

// Fill synthetic demo variants only when running in master template demo mode
if (empty($is_user_project)) {
    $base_per_kg_reg  = $unit_info['unit_type'] === 'kg'
        ? round($regular_price / max(0.05, $unit_info['qty_in_unit']), 2)
        : round($regular_price * 1.25, 2);
    $base_per_kg_sale = $unit_info['unit_type'] === 'kg'
        ? round($current_price / max(0.05, $unit_info['qty_in_unit']), 2)
        : round($current_price * 1.25, 2);

    $base_per_pc_reg  = $unit_info['unit_type'] === 'pc'
        ? round($regular_price / max(1, $unit_info['qty_in_unit']), 2)
        : round($regular_price / 8, 2);
    $base_per_pc_sale = $unit_info['unit_type'] === 'pc'
        ? round($current_price / max(1, $unit_info['qty_in_unit']), 2)
        : round($current_price / 8, 2);

    if (empty($kg_variants)) {
        $kg_variants = [
            [
                'id'          => 'kg-250g',
                'variant_id'  => 0,
                'label'       => '250 g',
                'short_label' => '250 g',
                'unit_type'   => 'kg',
                'unit_qty'    => 0.25,
                'unit_desc'   => '0.25 kg',
                'reg_price'   => round($base_per_kg_reg * 0.25, 2),
                'sale_price'  => round($base_per_kg_sale * 0.25, 2),
                'sku'         => $prod_sku . '-250G',
                'stock'       => 32,
                'badge'       => ''
            ],
            [
                'id'          => 'kg-400g',
                'variant_id'  => 0,
                'label'       => ($unit_info['unit_type'] === 'kg' && abs($unit_info['qty_in_unit'] - 0.5) < 0.02) ? '500 g' : '400 g',
                'short_label' => ($unit_info['unit_type'] === 'kg' && abs($unit_info['qty_in_unit'] - 0.5) < 0.02) ? '500 g' : '400 g',
                'unit_type'   => 'kg',
                'unit_qty'    => ($unit_info['unit_type'] === 'kg' && abs($unit_info['qty_in_unit'] - 0.5) < 0.02) ? 0.50 : 0.40,
                'unit_desc'   => ($unit_info['unit_type'] === 'kg' && abs($unit_info['qty_in_unit'] - 0.5) < 0.02) ? '0.50 kg' : '0.40 kg',
                'reg_price'   => ($unit_info['unit_type'] === 'kg' && $unit_info['qty_in_unit'] < 0.9) ? $regular_price : round($base_per_kg_reg * 0.40, 2),
                'sale_price'  => ($unit_info['unit_type'] === 'kg' && $unit_info['qty_in_unit'] < 0.9) ? $current_price : round($base_per_kg_sale * 0.40, 2),
                'sku'         => $prod_sku . '-400G',
                'stock'       => 25,
                'badge'       => 'Popular'
            ],
            [
                'id'          => 'kg-1kg',
                'variant_id'  => 0,
                'label'       => '1 kg',
                'short_label' => '1 kg',
                'unit_type'   => 'kg',
                'unit_qty'    => 1.00,
                'unit_desc'   => '1.00 kg',
                'reg_price'   => ($unit_info['unit_type'] === 'kg' && abs($unit_info['qty_in_unit'] - 1.0) < 0.05) ? $regular_price : round($base_per_kg_reg * 1.00, 2),
                'sale_price'  => ($unit_info['unit_type'] === 'kg' && abs($unit_info['qty_in_unit'] - 1.0) < 0.05) ? $current_price : round($base_per_kg_sale * 0.95, 2),
                'sku'         => $prod_sku . '-1KG',
                'stock'       => 18,
                'badge'       => 'Best Value'
            ],
            [
                'id'          => 'kg-2kg',
                'variant_id'  => 0,
                'label'       => '2 kg',
                'short_label' => '2 kg',
                'unit_type'   => 'kg',
                'unit_qty'    => 2.00,
                'unit_desc'   => '2.00 kg',
                'reg_price'   => round($base_per_kg_reg * 2.00, 2),
                'sale_price'  => round($base_per_kg_sale * 1.85, 2),
                'sku'         => $prod_sku . '-2KG',
                'stock'       => 12,
                'badge'       => 'Save Extra'
            ],
        ];
    }

    if (empty($pc_variants)) {
        $pc_variants = [
            [
                'id'          => 'pc-1',
                'variant_id'  => 0,
                'label'       => '1 pc',
                'short_label' => '1 pc',
                'unit_type'   => 'pc',
                'unit_qty'    => 1,
                'unit_desc'   => '1 piece',
                'reg_price'   => ($unit_info['unit_type'] === 'pc' && (int)$unit_info['qty_in_unit'] === 1) ? $regular_price : max(0.50, round($base_per_pc_reg * 1, 2)),
                'sale_price'  => ($unit_info['unit_type'] === 'pc' && (int)$unit_info['qty_in_unit'] === 1) ? $current_price : max(0.40, round($base_per_pc_sale * 1, 2)),
                'sku'         => $prod_sku . '-1PC',
                'stock'       => 40,
                'badge'       => ''
            ],
            [
                'id'          => 'pc-4',
                'variant_id'  => 0,
                'label'       => '4 pcs',
                'short_label' => '4 pcs',
                'unit_type'   => 'pc',
                'unit_qty'    => 4,
                'unit_desc'   => '4 pieces',
                'reg_price'   => ($unit_info['unit_type'] === 'pc' && (int)$unit_info['qty_in_unit'] === 4) ? $regular_price : round($base_per_pc_reg * 4, 2),
                'sale_price'  => ($unit_info['unit_type'] === 'pc' && (int)$unit_info['qty_in_unit'] === 4) ? $current_price : round($base_per_pc_sale * 4, 2),
                'sku'         => $prod_sku . '-4PC',
                'stock'       => 28,
                'badge'       => 'Popular'
            ],
            [
                'id'          => 'pc-8',
                'variant_id'  => 0,
                'label'       => '8 pcs',
                'short_label' => '8 pcs',
                'unit_type'   => 'pc',
                'unit_qty'    => 8,
                'unit_desc'   => '8 pieces',
                'reg_price'   => ($unit_info['unit_type'] === 'pc' && (int)$unit_info['qty_in_unit'] === 8) ? $regular_price : round($base_per_pc_reg * 8, 2),
                'sale_price'  => ($unit_info['unit_type'] === 'pc' && (int)$unit_info['qty_in_unit'] === 8) ? $current_price : round($base_per_pc_sale * 7.6, 2),
                'sku'         => $prod_sku . '-8PC',
                'stock'       => 20,
                'badge'       => 'Value Pack'
            ],
            [
                'id'          => 'pc-12',
                'variant_id'  => 0,
                'label'       => '12 pcs',
                'short_label' => '12 pcs',
                'unit_type'   => 'pc',
                'unit_qty'    => 12,
                'unit_desc'   => '12 pieces',
                'reg_price'   => ($unit_info['unit_type'] === 'pc' && (int)$unit_info['qty_in_unit'] === 12) ? $regular_price : round($base_per_pc_reg * 12, 2),
                'sale_price'  => ($unit_info['unit_type'] === 'pc' && (int)$unit_info['qty_in_unit'] === 12) ? $current_price : round($base_per_pc_sale * 11, 2),
                'sku'         => $prod_sku . '-12PC',
                'stock'       => 15,
                'badge'       => 'Best Saver'
            ],
        ];
    }
}

// Ensure initial_unit_type points to a non-empty variant list
if ($initial_unit_type === 'pc' && empty($pc_variants) && !empty($kg_variants)) {
    $initial_unit_type = 'kg';
} elseif ($initial_unit_type === 'kg' && empty($kg_variants) && !empty($pc_variants)) {
    $initial_unit_type = 'pc';
}

// Pick initial active variant matching the product's default unit
if ($initial_unit_type === 'pc' && !empty($pc_variants)) {
    $initial_variant = $pc_variants[0];
    foreach ($pc_variants as $pv) {
        if ((int)$pv['unit_qty'] === (int)$unit_info['qty_in_unit']) {
            $initial_variant = $pv;
            break;
        }
    }
} else {
    $initial_variant = !empty($kg_variants[0]) ? $kg_variants[0] : [
        'id' => (string)$prod_id, 'variant_id' => 0, 'label' => $prod_unit_raw, 'short_label' => $prod_unit_raw,
        'unit_type' => 'kg', 'unit_qty' => 1.0, 'unit_desc' => '1 kg',
        'reg_price' => $regular_price, 'sale_price' => $current_price, 'sku' => $prod_sku, 'stock' => $prod_stock, 'badge' => ''
    ];
    foreach ($kg_variants as $kv) {
        if (abs($kv['unit_qty'] - $unit_info['qty_in_unit']) < 0.06) {
            $initial_variant = $kv;
            break;
        }
    }
}

$init_reg_price  = (float)$initial_variant['reg_price'];
$init_sale_price = (float)$initial_variant['sale_price'];
$init_unit_qty   = (float)$initial_variant['unit_qty'];
$init_unit_rate  = $init_unit_qty > 0 ? round($init_sale_price / $init_unit_qty, 2) : $init_sale_price;
$init_reg_rate   = $init_unit_qty > 0 ? round($init_reg_price / $init_unit_qty, 2) : $init_reg_price;
$init_discount   = ($init_reg_price > $init_sale_price && $init_reg_price > 0)
    ? round((($init_reg_price - $init_sale_price) / $init_reg_price) * 100)
    : 0;

$actual_reviews = (!empty($product['reviews']) && is_array($product['reviews'])) ? $product['reviews'] : [];
$rating_counts  = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
$sum_rating     = 0;
foreach ($actual_reviews as $r_item) {
    $r_score = max(1, min(5, (int)($r_item['rating'] ?? 5)));
    $rating_counts[$r_score]++;
    $sum_rating += $r_score;
}
$total_review_count = count($actual_reviews);
$computed_avg       = $total_review_count > 0 ? round($sum_rating / $total_review_count, 1) : 0.0;
$rating_val         = $computed_avg;
$review_count       = $total_review_count;

$short_desc = !empty($product['short_description']) && strpos($product['short_description'], 'linen') === false
    ? $product['short_description']
    : (!empty($product['description']) ? strip_tags($product['description']) : 'Hand-picked farm-fresh organic harvest, naturally ripened for peak sweetness, vibrant flavor, and rich antioxidant nutrition.');
$full_desc = !empty($product['description']) ? $product['description'] : $short_desc;

// Dynamic Highlights from Database
$grc_highlights = [
    ['key' => 'Selling Unit',     'value' => ($initial_unit_type === 'pc' ? 'Per Piece (pc)' : 'By Weight (kg)'), 'id' => 'hl-selling-unit'],
    ['key' => 'Net Pack Content', 'value' => $initial_variant['label'] . ' (' . $initial_variant['unit_desc'] . ')', 'id' => 'hl-pack-content'],
    ['key' => 'Unit Rate',        'value' => $currency_symbol . number_format($init_unit_rate, 2) . ' / ' . $initial_unit_type, 'id' => 'hl-unit-rate'],
];
$raw_hl_list = [];
if (!empty($product['highlights_decoded']) && is_array($product['highlights_decoded'])) {
    $raw_hl_list = $product['highlights_decoded'];
} elseif (!empty($product['highlights']) && is_string($product['highlights'])) {
    $raw_hl_list = json_decode($product['highlights'], true) ?: [];
}
if (!empty($raw_hl_list)) {
    foreach ($raw_hl_list as $hl_row) {
        $hk = trim((string)($hl_row['key'] ?? ($hl_row['name'] ?? '')));
        $hv = trim((string)($hl_row['value'] ?? ''));
        if ($hk !== '' || $hv !== '') {
            $grc_highlights[] = ['key' => $hk ?: 'Highlight', 'value' => $hv, 'id' => ''];
        }
    }
} elseif (empty($is_user_project)) {
    $grc_highlights[] = ['key' => 'Farming Method', 'value' => '100% Certified Organic & Pesticide-Free', 'id' => ''];
    $grc_highlights[] = ['key' => 'Shelf Life',     'value' => '4 – 6 Days (Keep Refrigerated at 2°C – 4°C)', 'id' => ''];
    $grc_highlights[] = ['key' => 'Harvest Origin', 'value' => 'Local Sustainable Organic Orchards', 'id' => ''];
}

// Dynamic Specifications & Nutrition Facts from Database
$grc_specifications = [
    ['name' => 'Brand / Farm', 'value' => $prod_brand],
    ['name' => 'Department',   'value' => $prod_cat],
    ['name' => 'Unit Type',    'value' => strtoupper($initial_unit_type) . ($initial_unit_type === 'pc' ? ' (Per Piece Calculation)' : ' (Per Kilogram Calculation)')],
];
if (!empty($product['specifications']) && is_array($product['specifications'])) {
    foreach ($product['specifications'] as $sp_row) {
        $sn = trim((string)($sp_row['spec_name'] ?? ($sp_row['name'] ?? ($sp_row['key'] ?? ''))));
        $sv = trim((string)($sp_row['spec_value'] ?? ($sp_row['value'] ?? '')));
        if ($sn !== '' || $sv !== '') {
            $grc_specifications[] = ['name' => $sn ?: 'Specification', 'value' => $sv];
        }
    }
} elseif (empty($is_user_project)) {
    $grc_specifications[] = ['name' => 'Energy (per 100g)',   'value' => '32 kcal'];
    $grc_specifications[] = ['name' => 'Vitamin C',           'value' => '58.8 mg (98% Daily Value)'];
    $grc_specifications[] = ['name' => 'Dietary Fiber',       'value' => '2.0 g per 100g'];
    $grc_specifications[] = ['name' => 'Natural Sugars',      'value' => '4.9 g (Zero Added Sugar)'];
    $grc_specifications[] = ['name' => 'Dietary Suitability', 'value' => '100% Vegan, Gluten-Free, Non-GMO'];
    $grc_specifications[] = ['name' => 'Country of Origin',   'value' => 'India'];
}
?>

<style>
/* ==========================================================================
   GROCERY PRODUCT DETAILS PAGE STYLES (Template 3 - Bazaro Grocery)
   ========================================================================== */
.aq-product-details-thumbnails-style {
    padding-top: 24px;
}

/* Gallery Card & Vertical Thumbnail Slider (Sticky on Desktop while Right Column Scrolls) */
@media (min-width: 992px) {
    html, body, main, .aq-product-details-thumbnails-style {
        overflow: visible !important;
        overflow-x: clip !important;
    }
    .grc-sticky-left-col {
        position: -webkit-sticky;
        position: sticky;
        top: 24px;
        align-self: flex-start;
        z-index: 9;
    }
}
.grc-gallery-card {
    background: #ffffff;
    border: 1px solid #e5efe8;
    border-radius: 18px;
    padding: 20px;
    box-shadow: 0 6px 24px rgba(0, 61, 41, 0.04);
    position: relative;
}
.product-slider-main:not(.slick-initialized) .product-slider-for-img:not(:first-child) {
    display: none;
}
.product-slider-for-img {
    height: 430px;
    border-radius: 14px;
    overflow: hidden;
    background: #f9fbf9;
    text-align: center;
}
.product-slider-for-img img {
    width: 100%;
    height: 430px;
    object-fit: contain;
    margin: 0 auto;
}
.product-slider-nav .slick-list {
    height: auto !important;
}
.product-slider-nav .product-slider-nav-img {
    width: 100%;
    height: 92px;
    border-radius: 10px;
    overflow: hidden;
    border: 2px solid #e5efe8;
    margin-bottom: 10px;
    cursor: pointer;
    background: #f9fbf9;
    transition: all 0.2s ease;
}
.product-slider-nav .product-slider-nav-img.slick-current {
    border-color: #108910;
    box-shadow: 0 2px 8px rgba(16, 137, 16, 0.2);
}
.product-slider-nav .product-slider-nav-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Product Title */
.grc-pd-title {
    color: #003D29;
    font-size: 22px;
    font-weight: 700;
    line-height: 1.35;
    margin-bottom: 8px;
}

/* Right Column Info Wrap */
.grc-product-info-wrap {
    background: #ffffff;
}
.grc-cat-farm-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 10px;
}
.grc-dept-pill {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
    font-size: 12px;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 999px;
    text-decoration: none;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.grc-dept-pill:hover {
    background: #047857;
    color: #ffffff;
}
.grc-farm-link {
    font-size: 13px;
    color: #003D29;
    font-weight: 600;
    text-decoration: none;
}
.grc-stock-pill {
    font-size: 12px;
    font-weight: 700;
    color: #108910;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    padding: 3px 10px;
    border-radius: 999px;
}

/* Pricing & Unit Rate Box */
.grc-price-calculator-card {
    background: linear-gradient(135deg, #f4fbf6 0%, #ecf8f0 100%);
    border: 1.5px solid #bbf7d0;
    border-radius: 16px;
    padding: 18px 20px;
    margin: 16px 0;
}
.grc-main-price-row {
    display: flex;
    align-items: baseline;
    flex-wrap: wrap;
    gap: 12px;
}
.grc-discount-tag {
    background: #108910;
    color: #ffffff;
    font-size: 15px;
    font-weight: 700;
    padding: 4px 11px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.grc-current-price {
    font-size: 34px;
    font-weight: 800;
    color: #003D29;
    line-height: 1;
}
.grc-mrp-price {
    font-size: 15px;
    color: #6b7280;
    text-decoration: line-through;
    font-weight: 500;
}
.grc-pack-badge-inline {
    background: #ffffff;
    color: #003D29;
    border: 1px solid #a7f3d0;
    font-size: 13px;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 999px;
}

/* Highlighted Per-Kg / Per-Pc Unit Rate Banner */
.grc-unit-rate-highlight {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px dashed #a7f3d0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
}
.grc-unit-rate-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #003D29;
    color: #ffffff;
    padding: 7px 16px;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 700;
    box-shadow: 0 3px 10px rgba(0, 61, 41, 0.15);
}
.grc-unit-rate-pill i {
    color: #4ade80;
}
.grc-unit-formula-note {
    font-size: 13px;
    color: #065f46;
    font-weight: 600;
}

/* Unit Type Switcher (Weight kg vs Piece pc) */
.grc-unit-mode-switch {
    display: inline-flex;
    background: #f1f5f9;
    padding: 4px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    gap: 4px;
}
.grc-unit-mode-btn {
    border: none;
    background: transparent;
    color: #475569;
    font-size: 13px;
    font-weight: 700;
    padding: 7px 16px;
    border-radius: 9px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.grc-unit-mode-btn.active {
    background: #003D29;
    color: #ffffff;
    box-shadow: 0 2px 6px rgba(0, 61, 41, 0.2);
}

/* Pack Size Variant Cards */
.grc-pack-variants-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
    margin-top: 10px;
}
@media (min-width: 576px) {
    .grc-pack-variants-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}
.grc-pack-card {
    border: 1.5px solid #d1d5db;
    border-radius: 12px;
    padding: 11px 10px;
    background: #ffffff;
    cursor: pointer;
    text-align: left;
    position: relative;
    transition: all 0.18s ease;
}
.grc-pack-card:hover {
    border-color: #108910;
    background: #f9fefb;
    transform: translateY(-1px);
}
.grc-pack-card.active {
    border: 2px solid #108910;
    background: #f0fdf4;
    box-shadow: 0 4px 12px rgba(16, 137, 16, 0.12);
}
.grc-pack-card-tag {
    position: absolute;
    top: -9px;
    right: 8px;
    background: #108910;
    color: #ffffff;
    font-size: 10px;
    font-weight: 700;
    padding: 1px 7px;
    border-radius: 99px;
    text-transform: uppercase;
}
.grc-pack-card-weight {
    font-size: 14px;
    font-weight: 800;
    color: #111827;
    margin-bottom: 3px;
}
.grc-pack-card-price {
    font-size: 14px;
    font-weight: 700;
    color: #003D29;
}
.grc-pack-card-price del {
    font-size: 11.5px;
    color: #9ca3af;
    font-weight: 400;
    margin-left: 4px;
}
.grc-pack-card-unitrate {
    font-size: 11.5px;
    font-weight: 600;
    color: #108910;
    margin-top: 2px;
}

/* Quantity Stepper & Action Buttons */
.grc-qty-action-row {
    display: flex;
    align-items: stretch;
    gap: 12px;
    margin-top: 18px;
    flex-wrap: wrap;
}
.grc-qty-box {
    display: inline-flex;
    align-items: center;
    border: 1.5px solid #cbd5e1;
    border-radius: 999px;
    background: #ffffff;
    padding: 4px;
    height: 52px;
}
.grc-qty-btn {
    width: 40px;
    height: 42px;
    border-radius: 50%;
    border: none;
    background: #f1f5f9;
    color: #003D29;
    font-size: 16px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s ease;
}
.grc-qty-btn:hover {
    background: #003D29;
    color: #ffffff;
}
.grc-qty-input {
    width: 46px;
    text-align: center;
    border: none;
    font-size: 16px;
    font-weight: 800;
    color: #003D29;
    background: transparent;
    outline: none;
}
.btn-grc-atc {
    flex: 1 1 180px;
    height: 52px;
    border-radius: 999px;
    background: #108910;
    color: #ffffff;
    font-size: 15px;
    font-weight: 700;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 0 24px;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 6px 16px rgba(16, 137, 16, 0.25);
}
.btn-grc-atc:hover {
    background: #003D29;
    color: #ffffff;
}
.btn-grc-buy {
    flex: 1 1 150px;
    height: 52px;
    border-radius: 999px;
    background: #003D29;
    color: #ffffff;
    font-size: 15px;
    font-weight: 700;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 0 22px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.btn-grc-buy:hover {
    background: #108910;
    color: #ffffff;
}
.btn-grc-wish {
    position: absolute;
    top: 14px;
    right: 14px;
    z-index: 15;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: 1.5px solid #e2e8f0;
    background: rgba(255, 255, 255, 0.95);
    color: #003D29;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.2s ease;
}
.btn-grc-wish:hover {
    border-color: #dc2626;
    color: #dc2626;
    background: #fef2f2;
    transform: scale(1.06);
}
.btn-grc-wish.active,
.btn-grc-wish.in-wishlist {
    border-color: #dc2626;
    color: #dc2626;
    background: #fef2f2;
}
.btn-grc-wish.active i,
.btn-grc-wish.in-wishlist i {
    color: #ef4444 !important;
}

/* Total Order Summary Strip */
.grc-live-total-strip {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 14px;
    margin-top: 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    font-size: 13.5px;
}

/* Highlights & Specifications Grid */
.grc-specs-grid-layout {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    column-gap: 20px;
    row-gap: 10px;
}
.grc-accordion-heading {
    font-size: 18px;
    font-weight: 700;
    color: #003D29;
}

/* ==========================================================================
   MOBILE & TABLET RESPONSIVE STYLES
   ========================================================================== */
@media (max-width: 1199.98px) {
    /* Place thumbnail slider horizontally underneath the main product image */
    .product-slider-nav {
        margin-top: 10px;
        overflow: hidden;
    }
    .product-slider-nav .slick-list {
        margin: 0 -4px !important;
        padding: 2px 0 !important;
    }
    .product-slider-nav .slick-track {
        display: flex !important;
        align-items: center;
    }
    .product-slider-nav .product-slider-nav-img,
    .product-slider-nav .slick-slide,
    .product-slider-nav .slick-slide.slick-current {
        width: 72px !important;
        height: 72px !important;
        flex: 0 0 72px !important;
        margin: 0 4px !important;
        border-radius: 10px;
    }
    .product-slider-nav .product-slider-nav-img img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover;
    }
}

@media (max-width: 991.98px) {
    .aq-product-details-thumbnails-style {
        padding-top: 16px;
        padding-bottom: 45px !important;
    }
    .product-slider-for-img,
    .product-slider-for-img img {
        height: 340px;
    }
    .product-details-tab.pb-90 {
        padding-bottom: 45px !important;
    }
    .grc-product-ptb.pb-80 {
        padding-bottom: 45px !important;
    }
}

@media (max-width: 767.98px) {
    .aq-product-details-thumbnails-style {
        padding-top: 12px;
        padding-bottom: 36px !important;
    }
    .grc-gallery-card {
        padding: 12px;
        border-radius: 14px;
    }
    .product-slider-for-img {
        border-radius: 10px;
        height: 260px;
    }
    .product-slider-for-img img {
        height: 260px;
    }
    .product-slider-nav .product-slider-nav-img,
    .product-slider-nav .slick-slide,
    .product-slider-nav .slick-slide.slick-current {
        width: 62px !important;
        height: 62px !important;
        flex: 0 0 62px !important;
        margin: 0 3px !important;
        border-radius: 8px;
    }
    .btn-grc-wish {
        width: 38px;
        height: 38px;
        top: 10px;
        right: 10px;
        font-size: 16px;
    }
    .grc-pd-title {
        font-size: 18px;
        line-height: 1.35;
        margin-bottom: 6px;
    }
    .grc-dept-pill {
        font-size: 11px;
        padding: 3px 10px;
    }
    .grc-farm-link {
        font-size: 12px;
    }
    .grc-price-calculator-card {
        padding: 14px;
        border-radius: 12px;
        margin: 12px 0;
    }
    .grc-current-price {
        font-size: 24px;
    }
    .grc-discount-tag {
        font-size: 12.5px;
        padding: 3px 8px;
    }
    .grc-mrp-price {
        font-size: 13px;
    }
    .grc-pack-badge-inline {
        font-size: 11.5px;
        padding: 3px 10px;
    }
    .grc-unit-rate-highlight {
        margin-top: 10px;
        padding-top: 10px;
        gap: 8px;
    }
    .grc-unit-rate-pill {
        font-size: 13px;
        padding: 6px 12px;
        width: 100%;
        justify-content: center;
    }
    .grc-unit-formula-note {
        font-size: 12px;
        width: 100%;
        text-align: center;
    }
    .grc-unit-mode-switch {
        width: 100%;
        display: flex;
    }
    .grc-unit-mode-btn {
        flex: 1 1 50%;
        justify-content: center;
        font-size: 12px;
        padding: 7px 8px;
    }
    .grc-pack-variants-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }
    .grc-pack-card {
        padding: 9px 8px;
        border-radius: 10px;
    }
    .grc-pack-card-weight {
        font-size: 13px;
    }
    .grc-pack-card-price {
        font-size: 13px;
    }
    .grc-pack-card-unitrate {
        font-size: 11px;
    }
    .grc-accordion-heading {
        font-size: 15.5px;
    }
    .grc-specs-grid-layout {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        column-gap: 12px;
        row-gap: 8px;
    }
    .grc-qty-action-row {
        flex-wrap: nowrap;
        gap: 8px;
        margin-top: 14px !important;
    }
    .btn-grc-atc,
    .btn-grc-buy {
        flex: 1 1 50%;
        height: 46px;
        font-size: 13.5px;
        padding: 0 12px;
        gap: 6px;
        white-space: nowrap;
    }
    .product-details-nav {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        justify-content: flex-start !important;
        gap: 8px;
        padding-bottom: 6px;
        -webkit-overflow-scrolling: touch;
    }
    .product-details-nav .nav-links {
        white-space: nowrap;
        font-size: 13.5px !important;
        padding: 8px 14px !important;
        flex: 0 0 auto;
    }
    .product-details-desc-title {
        font-size: 18px !important;
        line-height: 1.4 !important;
    }
    .aq-section-title.fs-30 {
        font-size: 20px !important;
    }
}

@media (max-width: 400px) {
    .grc-specs-grid-layout {
        grid-template-columns: 1fr;
    }
    .btn-grc-atc,
    .btn-grc-buy {
        font-size: 12.5px;
        padding: 0 8px;
    }
}
</style>

   <!-- Question Modal (#exampleModalToggle4) -->
   <div class="aq-login-modal-style">
      <div class="modal fade" id="exampleModalToggle4" role="dialog" aria-hidden="true" tabindex="-1">
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               <div class="aq-login-wrapper">
                  <div class="aq-login-top mb-30">
                     <h3 class="aq-login-title">Ask About This Grocery Item</h3>
                  </div>
                  <div class="aq-login-option">
                     <div class="aq-login-input-wrapper">
                        <div class="aq-login-input-box">
                           <div class="aq-login-input-title">
                              <label class="aq-form-label">Your Question (Sourcing, Freshness, or Nutrition) <span>*</span></label>
                           </div>
                           <div class="aq-login-input">
                              <textarea class="aq-form-input" placeholder="Ask our farm specialists..."></textarea>
                           </div>
                        </div>
                     </div>
                     <div class="aq-login-bottom-wrap text-center mb-20">
                        <button type="button" class="aq-login-btn w-100 mb-10" style="background:#108910;border-color:#108910;" data-bs-dismiss="modal" onclick="if(typeof showGrcToast==='function')showGrcToast('Your question has been sent to our farm team!');">Submit Question</button>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>

   <!-- Delivery & Freshness Policy Modal (#exampleModalToggle5) -->
   <div class="aq-return-modal-style">
      <div class="modal fade" id="exampleModalToggle5" role="dialog" aria-hidden="true" tabindex="-1">
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               <div class="product-details-tab-item">
                  <div class="product-details-policy">
                     <h4 class="product-details-policy-title">Cold-Chain Grocery Delivery</h4>
                     <p>All fresh fruits, vegetables, dairy, and meats are stored and transported in temperature-controlled vans (2°C–5°C) to preserve farm-picked freshness.</p>
                     <ul class="product-details-policy-list mb-30">
                        <li>Same-Day Express Delivery within 2–4 Hours</li>
                        <li>Free delivery on grocery orders over <?= html_escape($currency_symbol); ?>50.00</li>
                        <li>Cash on Delivery &amp; Contactless Doorstep Drop available</li>
                     </ul>
                     <h4 class="product-details-policy-title">100% Freshness Replacement Guarantee</h4>
                     <p>Not happy with the freshness or quality of any item? Request an instant doorstep replacement or 100% refund within 24 hours of delivery—no questions asked.</p>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>

   <main>

      <!-- Grocery Product Details Main Area -->
      <div class="aq-product-details-thumbnails-style pb-90">
         <div class="container container-1630">
            <div class="row g-4 align-items-start">

               <!-- Left Column: Product Gallery (Fixed/Sticky while Right Column Scrolls) -->
               <div class="col-lg-6 grc-sticky-left-col">
                  <div class="grc-gallery-card">
                     <div class="product-details-slider-wrap">
                        <div class="row">
                           <div class="col-xl-2 order-2 order-xl-1">
                              <div class="product-slider-nav slider-nav">
                                 <?php foreach ($gallery_urls as $g_url): ?>
                                 <div class="product-slider-nav-img">
                                    <img src="<?= $g_url; ?>" alt="<?= html_escape($prod_title); ?>">
                                 </div>
                                 <?php endforeach; ?>
                              </div>
                           </div>
                           <div class="col-xl-10 order-1 order-xl-2">
                              <div class="product-slider-main-wrap p-relative">
                                 <button type="button" class="btn-grc-wish <?= !empty($is_in_wishlist) ? 'active in-wishlist' : ''; ?>" onclick="grcToggleWishlist(<?= $prod_id; ?>, this)" title="<?= !empty($is_in_wishlist) ? 'Remove from Wishlist' : 'Save to Wishlist'; ?>" data-product-id="<?= $prod_id; ?>">
                                    <i class="<?= !empty($is_in_wishlist) ? 'fa-solid fa-heart' : 'fa-regular fa-heart'; ?>" <?= !empty($is_in_wishlist) ? 'style="color: #ef4444;"' : ''; ?>></i>
                                 </button>
                                 <div class="product-slider-main slider-for p-relative">
                                    <?php foreach ($gallery_urls as $g_url): ?>
                                    <div class="product-slider-for-img">
                                       <a class="popup-image" href="<?= $g_url; ?>">
                                          <img src="<?= $g_url; ?>" alt="<?= html_escape($prod_title); ?>">
                                       </a>
                                    </div>
                                    <?php endforeach; ?>
                                 </div>
                                 <div class="product-slider-arrow"></div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>

               <!-- Right Column: Grocery Details, Unit Price Calculator (Per Kg / Per Pc), Pack Variants -->
               <div class="col-lg-6">
                  <div class="grc-product-info-wrap ps-lg-3">

                     <!-- Department & Farm -->
                     <div class="grc-cat-farm-row">
                        <a href="<?= site_url('shop/' . $prod_cat_slug . $grc_qs); ?>" class="grc-dept-pill">
                           <i class="fa-solid fa-basket-shopping me-1"></i><?= html_escape($prod_cat); ?>
                        </a>
                        <span class="text-muted">&bull;</span>
                        <a href="<?= site_url('shop' . $grc_qs); ?>" class="grc-farm-link">
                           <i class="fa-solid fa-tractor text-success me-1"></i><?= html_escape($prod_brand); ?>
                        </a>
                     </div>

                     <!-- Product Title -->
                     <h2 class="grc-pd-title">
                        <?php if (!empty($product['is_flash_sale'])): ?>
                           <span class="badge bg-danger me-2" style="font-size:13px; font-weight:700; padding:5px 12px; letter-spacing:0.5px; vertical-align:middle;">SALE</span>
                        <?php endif; ?>
                        <?= html_escape($prod_title); ?>
                     </h2>

                     <!-- Rating, Reviews & Harvest Note -->
                     <?php $hdr_stars = (int)round($rating_val); ?>
                     <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                        <div class="d-inline-flex align-items-center gap-1 bg-warning bg-opacity-10 px-2 py-1 rounded">
                           <?php if ($review_count > 0): ?>
                              <span class="fw-bold text-dark" style="font-size:14px;"><?= number_format($rating_val, 1); ?></span>
                           <?php endif; ?>
                           <span class="text-warning" style="font-size:13px;">
                              <?php for ($si = 1; $si <= 5; $si++): ?>
                                 <i class="fa-<?= ($si <= $hdr_stars) ? 'solid' : 'regular'; ?> fa-star"></i>
                              <?php endfor; ?>
                           </span>
                           <span class="text-muted fw-semibold" style="font-size:12.5px;">(<?= $review_count; ?>)</span>
                        </div>
                        <a href="#navPresentationTab" onclick="activateReviewTab()" class="text-secondary small text-decoration-none fw-semibold">
                           (<?= $review_count; ?> Customer <?= $review_count === 1 ? 'Review' : 'Reviews'; ?>)
                        </a>
                        <span class="text-muted">|</span>
                        <span class="small fw-semibold" style="color:#108910;">
                           <i class="fa-solid fa-clock-rotate-left me-1"></i>Harvested Fresh Today
                        </span>
                     </div>

                     <!-- PRICING & PER-PC / PER-KG UNIT PRICE CALCULATOR CARD -->
                     <div class="grc-price-calculator-card">
                        <div class="grc-main-price-row">
                           <span class="grc-discount-tag" id="display-discount-rate" style="<?= $init_discount > 0 ? 'display:inline-flex;' : 'display:none;'; ?>">
                              <i class="fa-solid fa-arrow-down"></i> <span id="display-discount-pct"><?= $init_discount; ?></span>% OFF
                           </span>

                           <span class="grc-current-price" id="display-sale-price">
                              <?= $currency_symbol . number_format($init_sale_price, 2); ?>
                           </span>

                           <span class="grc-mrp-price" id="display-mrp-price" style="<?= ($init_reg_price > $init_sale_price) ? 'display:inline;' : 'display:none;'; ?>">
                              M.R.P.: <?= $currency_symbol . number_format($init_reg_price, 2); ?>
                           </span>

                           <span class="grc-pack-badge-inline" id="display-pack-pill">
                              <i class="fa-solid fa-box-open me-1 text-success"></i><span id="display-pack-label"><?= html_escape($initial_variant['label']); ?></span>
                           </span>
                        </div>


                        <div class="d-flex align-items-center justify-content-between mt-2 pt-1 text-secondary" style="font-size:12px;">
                           <span><i class="fa-solid fa-circle-check text-success me-1"></i>Inclusive of all taxes</span>
                           <span id="display-regular-unit-rate" style="<?= ($init_reg_price > $init_sale_price) ? 'display:inline;' : 'display:none;'; ?>">
                              Regular Rate: <del><?= $currency_symbol . number_format($init_reg_rate, 2); ?> / <?= $initial_unit_type; ?></del>
                           </span>
                        </div>
                     </div>

                     <!-- Short Description -->
                     <p class="text-secondary mb-3" style="font-size: 14.5px; line-height: 1.6;">
                        <?= nl2br(html_escape($short_desc)); ?>
                     </p>

                     <!-- UNIT TYPE & PACK SIZE SELECTOR (Supports Both KG Weight & PC Pieces) -->
                     <div class="grc-unit-selector-section mb-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                           <div>
                              <span class="fw-bold text-dark" style="font-size:15px;">Select Pack Size / Unit:</span>
                              <span class="text-secondary ms-1 small">(<strong id="selected-pack-summary" style="color:#108910;"><?= html_escape($initial_variant['label']); ?></strong>)</span>
                           </div>

                           <!-- Unit Mode Toggle: By Weight (kg) vs By Piece (pc) -->
                           <?php if (!empty($kg_variants) && !empty($pc_variants)): ?>
                           <div class="grc-unit-mode-switch" role="group" aria-label="Product Unit Type">
                              <button type="button"
                                      class="grc-unit-mode-btn <?= $initial_unit_type === 'kg' ? 'active' : ''; ?>"
                                      id="btn-mode-kg"
                                      onclick="switchGrcUnitMode('kg')">
                                 <i class="fa-solid fa-scale-balanced"></i> Unit: Kg (Per Kg)
                              </button>
                              <button type="button"
                                      class="grc-unit-mode-btn <?= $initial_unit_type === 'pc' ? 'active' : ''; ?>"
                                      id="btn-mode-pc"
                                      onclick="switchGrcUnitMode('pc')">
                                 <i class="fa-solid fa-apple-whole"></i> Unit: Pc (Per Pc)
                              </button>
                           </div>
                           <?php endif; ?>
                        </div>

                        <!-- Weight (KG) Pack Variants Grid -->
                        <div class="grc-pack-variants-grid" id="grc-variants-kg" style="<?= $initial_unit_type === 'kg' ? 'display:grid;' : 'display:none;'; ?>">
                           <?php foreach ($kg_variants as $kv):
                              $kv_rate = round($kv['sale_price'] / max(0.01, $kv['unit_qty']), 2);
                              $is_active = ($initial_unit_type === 'kg' && $kv['id'] === $initial_variant['id']);
                           ?>
                           <div class="grc-pack-card <?= $is_active ? 'active' : ''; ?>"
                                data-id="<?= html_escape($kv['id']); ?>"
                                data-variant-id="<?= (int)($kv['variant_id'] ?? 0); ?>"
                                data-unit-type="kg"
                                data-label="<?= html_escape($kv['label']); ?>"
                                data-unit-qty="<?= (float)$kv['unit_qty']; ?>"
                                data-unit-desc="<?= html_escape($kv['unit_desc']); ?>"
                                data-reg="<?= (float)$kv['reg_price']; ?>"
                                data-sale="<?= (float)$kv['sale_price']; ?>"
                                data-sku="<?= html_escape($kv['sku']); ?>"
                                data-stock="<?= (int)$kv['stock']; ?>"
                                onclick="selectGrcPackVariant(this)">
                              <div class="grc-pack-card-weight"><?= html_escape($kv['short_label']); ?></div>
                              <div class="grc-pack-card-price">
                                 <?= $currency_symbol . number_format($kv['sale_price'], 2); ?>
                                 <?php if ($kv['reg_price'] > $kv['sale_price']): ?>
                                    <del><?= $currency_symbol . number_format($kv['reg_price'], 2); ?></del>
                                 <?php endif; ?>
                              </div>
                              <div class="grc-pack-card-unitrate">
                                 <?= $currency_symbol . number_format($kv_rate, 2); ?> / kg
                              </div>
                           </div>
                           <?php endforeach; ?>
                        </div>

                        <!-- Piece (PC) Pack Variants Grid -->
                        <div class="grc-pack-variants-grid" id="grc-variants-pc" style="<?= $initial_unit_type === 'pc' ? 'display:grid;' : 'display:none;'; ?>">
                           <?php foreach ($pc_variants as $pv):
                              $pv_rate = round($pv['sale_price'] / max(1, $pv['unit_qty']), 2);
                              $is_active = ($initial_unit_type === 'pc' && $pv['id'] === $initial_variant['id']);
                           ?>
                           <div class="grc-pack-card <?= $is_active ? 'active' : ''; ?>"
                                data-id="<?= html_escape($pv['id']); ?>"
                                data-variant-id="<?= (int)($pv['variant_id'] ?? 0); ?>"
                                data-unit-type="pc"
                                data-label="<?= html_escape($pv['label']); ?>"
                                data-unit-qty="<?= (float)$pv['unit_qty']; ?>"
                                data-unit-desc="<?= html_escape($pv['unit_desc']); ?>"
                                data-reg="<?= (float)$pv['reg_price']; ?>"
                                data-sale="<?= (float)$pv['sale_price']; ?>"
                                data-sku="<?= html_escape($pv['sku']); ?>"
                                data-stock="<?= (int)$pv['stock']; ?>"
                                onclick="selectGrcPackVariant(this)">
                              <div class="grc-pack-card-weight"><?= html_escape($pv['short_label']); ?></div>
                              <div class="grc-pack-card-price">
                                 <?= $currency_symbol . number_format($pv['sale_price'], 2); ?>
                                 <?php if ($pv['reg_price'] > $pv['sale_price']): ?>
                                    <del><?= $currency_symbol . number_format($pv['reg_price'], 2); ?></del>
                                 <?php endif; ?>
                              </div>
                              <div class="grc-pack-card-unitrate">
                                 <?= $currency_symbol . number_format($pv_rate, 2); ?> / pc
                              </div>
                           </div>
                           <?php endforeach; ?>
                        </div>
                     </div>

                     <!-- GROCERY HIGHLIGHTS & NUTRITIONAL SPECIFICATIONS ACCORDIONS -->
                     <div class="product-highlights-specifications my-3 pt-2 border-top">
                        <!-- Grocery Highlights (Open by Default) -->
                        <div class="product-highlights-block mb-2" id="product-highlights-block">
                           <div class="d-flex justify-content-between align-items-center py-2 user-select-none" onclick="toggleProductHighlights()" style="cursor: pointer;">
                              <h4 class="m-0 grc-accordion-heading">
                                 <i class="fa-solid fa-leaf text-success me-2"></i>Grocery Highlights &amp; Freshness Info
                              </h4>
                              <button type="button" class="btn-collapse-arrow d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; background: #ecfdf5; border-radius: 8px; border: none; color: #003D29;">
                                 <i class="fa-solid fa-chevron-up" id="highlights-arrow-icon" style="font-size: 11px;"></i>
                              </button>
                           </div>

                           <div id="product-highlights-collapse" class="pt-2" style="display: block;">
                              <div class="grc-specs-grid-layout">
                                 <?php foreach ($grc_highlights as $hl): ?>
                                    <div class="spec-grid-item" style="border-bottom: 1px solid #eef2f0; padding-bottom: 6px;">
                                       <div class="spec-item-key" style="font-size: 12.5px; color: #6b7280; margin-bottom: 2px;"><?= html_escape($hl['key']); ?></div>
                                       <div class="spec-item-val" <?= !empty($hl['id']) ? 'id="' . html_escape($hl['id']) . '"' : ''; ?> style="font-size: 14px; color: #003D29; font-weight: 600;"><?= html_escape($hl['value']); ?></div>
                                    </div>
                                 <?php endforeach; ?>
                              </div>
                           </div>
                        </div>

                        <!-- Nutritional Facts & Storage Specifications -->
                        <div class="product-specifications-block mb-2 border-top pt-2" id="product-specifications-block">
                           <div class="d-flex justify-content-between align-items-center py-2 user-select-none" onclick="toggleProductSpecifications()" style="cursor: pointer;">
                              <h4 class="m-0 grc-accordion-heading">
                                 <i class="fa-solid fa-apple-whole text-success me-2"></i>Nutrition Facts &amp; Storage Specs
                              </h4>
                              <button type="button" class="btn-collapse-arrow d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; background: #ecfdf5; border-radius: 8px; border: none; color: #003D29;">
                                 <i class="fa-solid fa-chevron-down" id="specs-arrow-icon" style="font-size: 11px;"></i>
                              </button>
                           </div>

                           <div id="product-specs-collapse" class="pt-2" style="display: none;">
                              <div class="grc-specs-grid-layout">
                                 <?php foreach ($grc_specifications as $sp): ?>
                                    <div class="spec-grid-item" style="border-bottom: 1px solid #eef2f0; padding-bottom: 6px;">
                                       <div class="spec-item-key" style="font-size: 12.5px; color: #6b7280; margin-bottom: 2px;"><?= html_escape($sp['name']); ?></div>
                                       <div class="spec-item-val" style="font-size: 13.5px; color: #1f2937; font-weight: 500;"><?= html_escape($sp['value']); ?></div>
                                    </div>
                                 <?php endforeach; ?>
                              </div>
                           </div>
                        </div>
                     </div>

                     <!-- Delivery, Return & Metadata Footer -->
                     <div class="p-3 rounded-3 mt-2" style="background:#f8faf9; border:1px solid #e5efe8;">
                        <div class="d-flex flex-wrap gap-4 mb-2 pb-2 border-bottom">
                           <a href="#exampleModalToggle5" data-bs-toggle="modal" class="text-decoration-none d-inline-flex align-items-center gap-2 fw-semibold" style="color:#003D29; font-size:13.5px;">
                              <i class="fa-solid fa-truck-fast text-success"></i> Cold-Chain Delivery &amp; Replacement Policy
                           </a>
                           <a href="#exampleModalToggle4" data-bs-toggle="modal" class="text-decoration-none d-inline-flex align-items-center gap-2 fw-semibold" style="color:#003D29; font-size:13.5px;">
                              <i class="fa-regular fa-circle-question text-success"></i> Ask Our Farm Specialist
                           </a>
                        </div>
                        <div class="row g-2 small text-secondary">
                           <div class="col-sm-6">
                              <strong>SKU:</strong> <span class="text-dark fw-semibold" id="display-sku"><?= html_escape($initial_variant['sku']); ?></span>
                           </div>
                           <div class="col-sm-6">
                              <strong>Availability:</strong> <span class="text-success fw-bold" id="display-stock-status">In Stock (<?= (int)$initial_variant['stock']; ?> packs available)</span>
                           </div>
                           <div class="col-sm-6">
                              <strong>Delivery Slot:</strong> <span class="text-dark fw-semibold">Today within 2 – 4 Hours</span>
                           </div>
                           <div class="col-sm-6">
                              <strong>Cash on Delivery:</strong> <span class="text-success fw-semibold"><i class="fa-solid fa-circle-check me-1"></i>Available</span>
                           </div>
                        </div>
                     </div>

                     <!-- Action Buttons (At the end of the right card) -->
                     <input type="hidden" id="product-qty-input" value="1">
                     <div class="grc-qty-action-row mt-4">
                        <button type="button" class="btn-grc-atc" id="main-btn-atc" onclick="grcDetailAddToCart(event)">
                           <i class="fa-solid fa-basket-shopping"></i>
                           <span>Add to Bag</span>
                        </button>

                        <button type="button" class="btn-grc-buy" onclick="grcDetailBuyNow()">
                           <i class="fa-solid fa-bolt"></i>
                           <span>Buy Now</span>
                        </button>
                     </div>

                  </div>
               </div>

            </div>
         </div>
      </div>

      <!-- Grocery Product Details Tabs (Description, Nutrition, Reviews, FAQ) -->
      <div class="product-details-tab pb-90">
         <div class="container container-1630">
            <div class="row">
               <div class="col-xl-12">
                  <div class="product-details-tab-nav">
                     <nav>
                        <div class="product-details-nav nav nav-tab justify-content-center p-relative" id="navPresentationTab" role="tablist">
                           <button class="nav-links active" id="nav-description-tab" data-bs-toggle="tab" data-bs-target="#nav-description" type="button" role="tab" aria-selected="true">Farm Description &amp; Quality</button>
                           <button class="nav-links" id="nav-addInfo-tab" data-bs-toggle="tab" data-bs-target="#nav-addInfo" type="button" role="tab" aria-selected="false">Nutrition &amp; Unit Pricing</button>
                           <button class="nav-links" id="nav-review-tab" data-bs-toggle="tab" data-bs-target="#nav-review" type="button" role="tab" aria-selected="false">Customer Reviews (<?= $review_count; ?>)</button>
                           <button class="nav-links" id="nav-faq-tab" data-bs-toggle="tab" data-bs-target="#nav-faq" type="button" role="tab" aria-selected="false">Grocery FAQs</button>
                        </div>
                     </nav>

                     <div class="tab-content" id="navPresentationTabContent">
                        <!-- Tab 1: Grocery Description -->
                        <div class="tab-pane fade show active" id="nav-description" role="tabpanel">
                           <div class="product-details-tab-item pt-4">
                              <div class="product-details-desc-wrap">
                                 <h3 class="product-details-desc-title" style="color:#003D29;"><?= html_escape($prod_title); ?></h3>
                                 <div class="mb-30 text-secondary" style="font-size:15px; line-height:1.75;">
                                    <?php if (!empty($product['description'])): ?>
                                       <?= nl2br(html_escape($product['description'])); ?>
                                    <?php else: ?>
                                       <?= nl2br(html_escape($short_desc)); ?>
                                    <?php endif; ?>
                                 </div>
                                 <?php if (!empty($raw_hl_list)): ?>
                                 <div class="row g-3 mt-2">
                                    <?php foreach ($raw_hl_list as $hl_card):
                                       $hc_k = trim((string)($hl_card['key'] ?? ($hl_card['name'] ?? '')));
                                       $hc_v = trim((string)($hl_card['value'] ?? ''));
                                       if ($hc_k === '' && $hc_v === '') continue;
                                    ?>
                                    <div class="col-md-3 col-sm-6">
                                       <div class="p-3 rounded-3 h-100" style="background:#f4fbf6; border:1px solid #d1fae5;">
                                          <div class="small text-muted mb-1"><?= html_escape($hc_k); ?></div>
                                          <div class="fw-bold" style="color:#003D29; font-size:16px;"><?= html_escape($hc_v); ?></div>
                                       </div>
                                    </div>
                                    <?php endforeach; ?>
                                 </div>
                                 <?php elseif (empty($is_user_project)): ?>
                                 <div class="row g-4">
                                    <div class="col-md-4">
                                       <div class="p-4 rounded-3 h-100" style="background:#f4fbf6; border:1px solid #d1fae5;">
                                          <h5 class="fw-bold mb-3" style="color:#003D29;"><i class="fa-solid fa-leaf text-success me-2"></i>Why Choose Our Organic Produce</h5>
                                          <ul class="mb-0 ps-3 text-secondary" style="line-height:1.9;">
                                             <li>100% Certified Organic &amp; Non-GMO Harvest</li>
                                             <li>Hand-graded for uniform size and zero bruising</li>
                                             <li>Transparent per-kg and per-pc unit pricing</li>
                                             <li>Harvested within 24 hours of dispatch</li>
                                          </ul>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="p-4 rounded-3 h-100" style="background:#f4fbf6; border:1px solid #d1fae5;">
                                          <h5 class="fw-bold mb-3" style="color:#003D29;"><i class="fa-solid fa-temperature-arrow-down text-success me-2"></i>Storage &amp; Freshness Tips</h5>
                                          <ul class="mb-0 ps-3 text-secondary" style="line-height:1.9;">
                                             <li>Keep refrigerated between 2°C and 4°C in original ventilated pack</li>
                                             <li>Do not wash until just before eating to prevent moisture spoilage</li>
                                             <li>Bring to room temperature 15 mins before serving for best flavor</li>
                                          </ul>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="p-4 rounded-3 h-100" style="background:#f4fbf6; border:1px solid #d1fae5;">
                                          <h5 class="fw-bold mb-3" style="color:#003D29;"><i class="fa-solid fa-utensils text-success me-2"></i>Serving &amp; Culinary Ideas</h5>
                                          <ul class="mb-0 ps-3 text-secondary" style="line-height:1.9;">
                                             <li>Enjoy fresh out of the box as a healthy antioxidant snack</li>
                                             <li>Pair with organic Greek yogurt, oats, or sourdough toast</li>
                                             <li>Blend into cold-pressed smoothies, salads, or artisan desserts</li>
                                          </ul>
                                       </div>
                                    </div>
                                 </div>
                                 <?php endif; ?>
                              </div>
                           </div>
                        </div>

                        <!-- Tab 2: Nutrition & Unit Price Breakdown -->
                        <div class="tab-pane fade" id="nav-addInfo" role="tabpanel">
                           <div class="product-details-tab-item pt-4">
                              <div class="product-details-additional-info">
                                 <div class="row g-4">
                                    <div class="col-lg-6">
                                       <h4 class="product-details-additional-info-title mb-3" style="color:#003D29;">Product Specifications &amp; Nutritional Facts</h4>
                                       <table>
                                          <tbody>
                                             <?php foreach ($grc_specifications as $sp_item): ?>
                                             <tr>
                                                <td><?= html_escape($sp_item['name']); ?></td>
                                                <td><?= html_escape($sp_item['value']); ?></td>
                                             </tr>
                                             <?php endforeach; ?>
                                          </tbody>
                                       </table>
                                    </div>
                                    <div class="col-lg-6">
                                       <h4 class="product-details-additional-info-title mb-3" style="color:#003D29;">Per-Kg &amp; Per-Pc Unit Price Guide</h4>
                                       <table>
                                          <tbody>
                                             <?php foreach ($kg_variants as $kv):
                                                $r = round($kv['sale_price'] / max(0.01, $kv['unit_qty']), 2);
                                             ?>
                                             <tr>
                                                <td><?= html_escape($kv['label']); ?> (Weight Unit: Kg)</td>
                                                <td><strong><?= $currency_symbol . number_format($kv['sale_price'], 2); ?></strong> &bull; <span class="text-success fw-bold"><?= $currency_symbol . number_format($r, 2); ?> / kg</span></td>
                                             </tr>
                                             <?php endforeach; ?>
                                             <?php foreach ($pc_variants as $pv):
                                                $r = round($pv['sale_price'] / max(1, $pv['unit_qty']), 2);
                                             ?>
                                             <tr>
                                                <td><?= html_escape($pv['label']); ?> (Piece Unit: Pc)</td>
                                                <td><strong><?= $currency_symbol . number_format($pv['sale_price'], 2); ?></strong> &bull; <span class="text-success fw-bold"><?= $currency_symbol . number_format($r, 2); ?> / pc</span></td>
                                             </tr>
                                             <?php endforeach; ?>
                                          </tbody>
                                       </table>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>

                        <!-- Tab 3: Customer Reviews -->
                        <div class="tab-pane fade" id="nav-review" role="tabpanel">
                           <div class="product-details-tab-item pt-4">
                              <div class="row g-4">
                                 <!-- Left Column: Product Rating Summary & Breakdown Bar -->
                                 <div class="col-lg-4 col-md-5">
                                    <div class="p-4 rounded-3 border bg-white" style="border-color:#e5e7eb !important;">
                                       <h5 class="fw-bold mb-3" style="color:#003D29; font-size:17px;">Customer Ratings</h5>
                                       <?php
                                       $bar_total = $total_review_count;
                                       $bar_avg   = $computed_avg;
                                       $bar_counts = $rating_counts;
                                       if ($bar_total === 0 && empty($is_user_project)) {
                                           $bar_total  = max(1, (int)$review_count);
                                           $bar_avg    = (float)$rating_val;
                                           $bar_counts = [
                                               5 => (int)round($bar_total * 0.78),
                                               4 => (int)round($bar_total * 0.16),
                                               3 => max(0, $bar_total - (int)round($bar_total * 0.78) - (int)round($bar_total * 0.16)),
                                               2 => 0,
                                               1 => 0
                                           ];
                                       }
                                       $bar_avg_round = round($bar_avg * 2) / 2;
                                       ?>
                                       <div class="d-flex align-items-center gap-3">
                                          <div class="text-center px-2">
                                             <div class="fw-bold" style="font-size:38px; line-height:1.1; color:#003D29;">
                                                <?= number_format($bar_avg, 1); ?>
                                             </div>
                                             <div class="text-warning my-1" style="font-size:14px;">
                                                <?php for ($s = 1; $s <= 5; $s++): ?>
                                                   <?php if ($bar_avg > 0 && $s <= floor($bar_avg_round)): ?>
                                                      <i class="fa-solid fa-star"></i>
                                                   <?php elseif ($bar_avg > 0 && ($s - 0.5) == $bar_avg_round): ?>
                                                      <i class="fa-solid fa-star-half-stroke"></i>
                                                   <?php else: ?>
                                                      <i class="fa-regular fa-star" style="color:#d1d5db;"></i>
                                                   <?php endif; ?>
                                                <?php endfor; ?>
                                             </div>
                                             <div class="text-muted small" style="font-size:12px;">
                                                <?= number_format($bar_total); ?> <?= ($bar_total === 1) ? 'Rating' : 'Ratings'; ?>
                                             </div>
                                          </div>

                                          <div class="flex-grow-1 ps-2 border-start">
                                             <?php
                                             $bar_colors = [
                                                 5 => '#108910',
                                                 4 => '#26a541',
                                                 3 => '#26a541',
                                                 2 => '#ff9f00',
                                                 1 => '#ff6161'
                                             ];
                                             for ($star = 5; $star >= 1; $star--):
                                                 $s_cnt = (int)($bar_counts[$star] ?? 0);
                                                 $s_pct = $bar_total > 0 ? round(($s_cnt / $bar_total) * 100, 1) : 0;
                                                 $s_col = $bar_colors[$star];
                                             ?>
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                   <div class="d-inline-flex align-items-center gap-1 fw-semibold text-dark" style="width:32px; font-size:12px;">
                                                      <span><?= $star; ?></span>
                                                      <i class="fa-solid fa-star" style="font-size:10px; color:#f59e0b;"></i>
                                                   </div>
                                                   <div class="flex-grow-1 rounded-pill overflow-hidden" style="height:8px; background-color:#f1f5f9;">
                                                      <div class="h-100 rounded-pill" style="width:<?= $s_pct; ?>%; background-color:<?= $s_col; ?>; transition:width 0.3s ease;"></div>
                                                   </div>
                                                   <div class="text-muted text-end" style="width:32px; font-size:12px;">
                                                      <?= number_format($s_cnt); ?>
                                                   </div>
                                                </div>
                                             <?php endfor; ?>
                                          </div>
                                       </div>
                                    </div>
                                 </div>

                                 <!-- Right Column: Customer Reviews & Customer Images (Latest 5) -->
                                 <div class="col-lg-8 col-md-7">
                                    <h4 class="fw-bold mb-3" style="color:#003D29; font-size:18px;">
                                       Customer Reviews (<?= number_format($bar_total); ?>)
                                    </h4>
                                    <?php if (!empty($actual_reviews)):
                                       $latest_reviews = array_slice($actual_reviews, 0, 5);
                                    ?>
                                       <?php foreach ($latest_reviews as $rev):
                                          $r_name   = $rev['customer_name'] ?? ($rev['name'] ?? 'Customer');
                                          $r_stars  = max(1, min(5, (int)($rev['rating'] ?? 5)));
                                          $r_title  = trim((string)($rev['title'] ?? ''));
                                          $r_text   = trim((string)($rev['comment'] ?? ($rev['review'] ?? '')));
                                          $r_date   = !empty($rev['created_at']) ? date('d M, Y', strtotime($rev['created_at'])) : '';
                                          $r_images = [];
                                          if (!empty($rev['images'])) {
                                              $decoded_imgs = is_array($rev['images']) ? $rev['images'] : json_decode($rev['images'], true);
                                              if (is_array($decoded_imgs)) {
                                                  $r_images = array_values(array_filter($decoded_imgs));
                                              }
                                          }
                                          $raw_r_avatar = trim((string)($rev['user_avatar'] ?? ($rev['avatar'] ?? '')));
                                          $has_r_avatar = ($raw_r_avatar !== '' && !in_array(strtolower($raw_r_avatar), ['default-user.png', 'user-default.jpg', 'default.png'], true));
                                          $r_avatar_url = '';
                                          if ($has_r_avatar) {
                                              $clean_r_av = ltrim($raw_r_avatar, '/');
                                              if (strpos($clean_r_av, 'assets/images/') === 0) {
                                                  $clean_r_av = substr($clean_r_av, strlen('assets/images/'));
                                              }
                                              $r_avatar_url = (strpos($clean_r_av, 'http://') === 0 || strpos($clean_r_av, 'https://') === 0)
                                                  ? $clean_r_av
                                                  : base_url('assets/images/' . $clean_r_av);
                                          }
                                       ?>
                                       <div class="p-3 mb-3 rounded-3 border bg-white" style="border-color:#e5e7eb !important;">
                                          <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                             <div class="d-flex align-items-center gap-2">
                                                <?php if ($has_r_avatar): ?>
                                                   <img src="<?= $r_avatar_url; ?>" alt="<?= html_escape($r_name); ?>" class="rounded-circle shadow-sm border" style="width: 30px; height: 30px; object-fit: cover; flex-shrink: 0;" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                                   <div class="rounded-circle align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 30px; height: 30px; background: linear-gradient(135deg, #108910, #1b52b3); font-size: 13px; flex-shrink: 0; display: none;">
                                                      <i class="fa-solid fa-user"></i>
                                                   </div>
                                                <?php else: ?>
                                                   <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 30px; height: 30px; background: linear-gradient(135deg, #108910, #1b52b3); font-size: 13px; flex-shrink: 0;">
                                                      <i class="fa-solid fa-user"></i>
                                                   </div>
                                                <?php endif; ?>
                                                <div class="fw-semibold text-dark" style="font-size:14px;"><?= html_escape($r_name); ?></div>
                                             </div>
                                             <?php if ($r_date !== ''): ?>
                                                <span class="text-muted small ms-auto text-nowrap" style="font-size:12px;"><?= html_escape($r_date); ?></span>
                                             <?php endif; ?>
                                          </div>

                                          <div class="d-flex align-items-center gap-1 flex-wrap mb-2">
                                             <span class="d-inline-flex align-items-center gap-1" style="color:#f59e0b; font-size:13px;">
                                                <?php for ($si = 1; $si <= 5; $si++): ?>
                                                   <i class="fa-<?= $si <= $r_stars ? 'solid' : 'regular'; ?> fa-star"></i>
                                                <?php endfor; ?>
                                                <span class="fw-bold text-dark ms-1" style="font-size:13.5px;"><?= number_format($r_stars, 1); ?></span>
                                             </span>
                                             <?php if ($r_title !== ''): ?>
                                                <span class="text-muted mx-1" style="font-size:14px; line-height:1;">&bull;</span>
                                                <strong class="text-dark" style="font-size:14px;"><?= html_escape($r_title); ?></strong>
                                             <?php endif; ?>
                                          </div>

                                          <?php if ($r_text !== ''): ?>
                                             <p class="mb-2 text-secondary" style="font-size:13.5px; line-height:1.55;"><?= nl2br(html_escape($r_text)); ?></p>
                                          <?php endif; ?>

                                          <?php if (!empty($r_images)): ?>
                                             <div class="d-flex align-items-center gap-2 flex-wrap mt-2">
                                                <?php foreach ($r_images as $img_rel):
                                                   $clean_rel = ltrim(trim((string)$img_rel), '/');
                                                   if (strpos($clean_rel, 'assets/images/') === 0) {
                                                       $clean_rel = substr($clean_rel, strlen('assets/images/'));
                                                   }
                                                   $img_full_url = base_url('assets/images/' . $clean_rel);
                                                   $img_fallback = base_url('website/assets/images/' . $clean_rel);
                                                ?>
                                                   <a href="<?= $img_full_url; ?>" target="_blank" class="d-inline-block border rounded-2 overflow-hidden" style="width:120px; height:120px; border-color:#e5e7eb !important;">
                                                      <img src="<?= $img_full_url; ?>" alt="Review photo" style="width:120px; height:120px; object-fit:cover;" onerror="this.onerror=null;this.src='<?= $img_fallback; ?>';">
                                                   </a>
                                                <?php endforeach; ?>
                                             </div>
                                          <?php endif; ?>
                                       </div>
                                       <?php endforeach; ?>

                                       <?php if (count($actual_reviews) > 5): ?>
                                          <div class="mt-3">
                                             <a href="<?= site_url('product/reviews/' . $prod_slug . $grc_qs); ?>" class="btn btn-outline-success fw-semibold px-4 py-2 rounded-2" style="border-color:#108910; color:#108910; font-size:14px;">
                                                Show All Reviews (<?= number_format(count($actual_reviews)); ?>) <i class="fa-solid fa-arrow-right ms-1"></i>
                                             </a>
                                          </div>
                                       <?php endif; ?>
                                    <?php elseif (empty($is_user_project)): ?>
                                       <div class="p-3 mb-3 rounded-3 border bg-white">
                                          <div class="d-flex justify-content-between align-items-center mb-1">
                                             <strong>Ananya Sharma</strong>
                                             <span class="text-warning small"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></span>
                                          </div>
                                          <p class="mb-1 text-secondary small">Super fresh, firm, and naturally sweet! Delivered chilled in under 2 hours. Love that the page clearly shows the per-kg and per-pc price so comparing pack sizes is effortless.</p>
                                       </div>
                                    <?php else: ?>
                                       <div class="p-4 rounded-3 border bg-white text-center text-muted">
                                          <i class="fa-regular fa-comment-dots fs-2 mb-2 text-success"></i>
                                          <p class="mb-0">No customer reviews yet for <strong><?= html_escape($prod_title); ?></strong>.</p>
                                       </div>
                                    <?php endif; ?>
                                 </div>
                              </div>
                           </div>
                        </div>

                        <!-- Tab 4: Grocery FAQs -->
                        <div class="tab-pane fade" id="nav-faq" role="tabpanel">
                           <div class="product-details-tab-item pt-4">
                              <div class="aq-faq-wrap">
                                 <div class="accordion" id="grcFaqAccordion">
                                    <div class="accordion-items">
                                       <div class="accordion-header">
                                          <button class="accordion-buttons" type="button" data-bs-toggle="collapse" data-bs-target="#grcFaq1" aria-expanded="true">
                                             How is the Per Kg or Per Pc price calculated?
                                             <span class="aq-faq-icon"></span>
                                          </button>
                                       </div>
                                       <div id="grcFaq1" class="accordion-collapse collapse show" data-bs-parent="#grcFaqAccordion">
                                          <div class="accordion-body">
                                             <p>When a product unit is measured by weight (<strong>kg / g</strong>), we divide the pack price by its net weight in kilograms (for example, a 400g pack is 0.40 kg, so Per Kg Price = Pack Price &divide; 0.40). When a product unit is measured by count (<strong>pc / pcs</strong>), we divide the pack price by the number of pieces in the pack (Per Pc Price = Pack Price &divide; Piece Count).</p>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="accordion-items">
                                       <div class="accordion-header">
                                          <button class="accordion-buttons collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#grcFaq2" aria-expanded="false">
                                             How do you keep fresh produce cold during delivery?
                                             <span class="aq-faq-icon"></span>
                                          </button>
                                       </div>
                                       <div id="grcFaq2" class="accordion-collapse collapse" data-bs-parent="#grcFaqAccordion">
                                          <div class="accordion-body">
                                             <p>All perishable produce is packed in insulated cold-chain totes kept between 2°C and 4°C from our local dark store right to your doorstep.</p>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- Related Grocery Products Carousel / Grid (with Per-Kg & Per-Pc Unit Prices) -->
      <?php if (!empty($related_products)): ?>
      <div class="grc-product-ptb pb-80">
         <div class="container container-1630">
            <div class="grc-product-top pb-20">
               <div class="row align-items-center">
                  <div class="col-sm-8">
                     <h4 class="aq-section-title ff-satoshi-bold fs-30 mb-0" style="color:#003D29;">Fresh From The Same Aisle</h4>
                  </div>
                  <div class="col-sm-4 text-sm-end">
                     <a class="aq-btn-link aq-btn-underline" href="<?= site_url('shop' . $grc_qs); ?>">View All Groceries</a>
                  </div>
               </div>
            </div>
            <div class="row grc-product-style">
               <?php foreach (array_slice($related_products, 0, 6) as $rp_idx => $rp):
                  $rp_img  = grc_detail_resolve_img($rp['primary_image'] ?? ($rp['image'] ?? ($rp['main_image'] ?? '')), $bazaro, $rp_idx + 1);
                  $rp_reg  = (float)($rp['regular_price'] ?? $rp['price'] ?? 5.99);
                  $rp_sal  = (!empty($rp['sale_price']) && (float)$rp['sale_price'] > 0 && (float)$rp['sale_price'] < $rp_reg) ? (float)$rp['sale_price'] : $rp_reg;
                  $rp_pct  = ($rp_sal < $rp_reg && $rp_reg > 0) ? round((($rp_reg - $rp_sal) / $rp_reg) * 100) : 0;
                  $rp_slug = !empty($rp['slug']) ? $rp['slug'] : 'sweet-red-strawberries-box';
                  $rp_name = !empty($rp['name']) ? $rp['name'] : ($rp['title'] ?? 'Fresh Grocery Item');
                  $rp_unit = !empty($rp['unit_label']) ? $rp['unit_label'] : '500g Pack';
                  $rp_uinfo = grc_calculate_unit_pricing($rp_unit, $rp_sal);
               ?>
               <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6 col-6">
                  <div class="aq-product-item aq-product-main mb-40">
                     <div class="aq-product-thumb p-relative mb-10">
                        <?php if ($rp_pct > 0): ?>
                        <div class="aq-product-badge">
                           <span class="clr-sale">-<?= $rp_pct; ?>%</span>
                        </div>
                        <?php endif; ?>
                        <a href="<?= site_url('product/' . $rp_slug . $grc_qs); ?>">
                           <img class="aq-product-img-hover" src="<?= $rp_img; ?>" alt="<?= html_escape($rp_name); ?>">
                        </a>
                     </div>
                     <div class="aq-product-content">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                           <span class="aqf-product-3-category"><?= html_escape($rp['category_name'] ?? 'Organic'); ?></span>
                           <span class="grc-unit-pill"><?= html_escape($rp_unit); ?></span>
                        </div>
                        <h4 class="aq-product-title">
                           <a href="<?= site_url('product/' . $rp_slug . $grc_qs); ?>"><?= html_escape($rp_name); ?></a>
                        </h4>
                        <?php
                        $rp_rev_cnt = (isset($rp['reviews']) && is_array($rp['reviews'])) ? count($rp['reviews']) : (int)($rp['reviews_count'] ?? 0);
                        $rp_rating  = $rp_rev_cnt > 0 ? (float)($rp['rating'] ?? 0) : 0.0;
                        $rp_stars   = (int)round($rp_rating);
                        ?>
                        <div class="aq-product-ratting text-warning small mb-1 d-flex align-items-center gap-1">
                           <span>
                              <?php for ($si = 1; $si <= 5; $si++): ?>
                                 <i class="fa-<?= ($si <= $rp_stars) ? 'solid' : 'regular'; ?> fa-star"></i>
                              <?php endfor; ?>
                           </span>
                           <span class="text-muted" style="font-size:12px;">(<?= $rp_rev_cnt; ?>)</span>
                        </div>
                        <div class="aq-product-price mb-1">
                           <ins><span class="aq-product-new-price"><?= html_escape($currency_symbol) . number_format($rp_sal, 2); ?></span></ins>
                           <?php if ($rp_sal < $rp_reg): ?>
                              <del><span class="aq-product-old-price"><?= html_escape($currency_symbol) . number_format($rp_reg, 2); ?></span></del>
                           <?php endif; ?>
                        </div>
                        <div class="small fw-semibold mb-2" style="color:#108910; font-size:12px;">
                           <?= html_escape($currency_symbol) . number_format($rp_uinfo['unit_rate'], 2); ?> / <?= html_escape($rp_uinfo['unit_suffix']); ?>
                        </div>
                     </div>
                     <div class="grc-product-btn">
                        <button type="button" onclick="grcAddToCart(<?= (int)$rp['id']; ?>, 1)" class="product-btn-green w-100">Add to bag</button>
                     </div>
                  </div>
               </div>
               <?php endforeach; ?>
            </div>
         </div>
      </div>
      <?php endif; ?>

   </main>

<script>
(function() {
    'use strict';

    var productId      = <?= (int)$prod_id; ?>;
    var currencySymbol = <?= json_encode($currency_symbol); ?>;
    var activeUnitType = <?= json_encode($initial_unit_type); ?>; // 'kg' or 'pc'
    var activePack     = {
        id:        <?= json_encode($initial_variant['id']); ?>,
        variantId: <?= (int)($initial_variant['variant_id'] ?? 0); ?>,
        unitType:  <?= json_encode($initial_variant['unit_type']); ?>,
        label:     <?= json_encode($initial_variant['label']); ?>,
        unitQty:   <?= (float)$initial_variant['unit_qty']; ?>,
        unitDesc:  <?= json_encode($initial_variant['unit_desc']); ?>,
        regPrice:  <?= (float)$initial_variant['reg_price']; ?>,
        salePrice: <?= (float)$initial_variant['sale_price']; ?>,
        sku:       <?= json_encode($initial_variant['sku']); ?>,
        stock:     <?= (int)$initial_variant['stock']; ?>
    };

    function formatMoney(val) {
        return currencySymbol + parseFloat(val || 0).toFixed(2);
    }

    function recalculateGrcPricing() {
        var qtyInput = document.getElementById('product-qty-input');
        var orderQty = Math.max(1, parseInt(qtyInput ? qtyInput.value : '1', 10) || 1);

        var packSale  = parseFloat(activePack.salePrice) || 0;
        var packReg   = parseFloat(activePack.regPrice) || packSale;
        var unitQty   = parseFloat(activePack.unitQty) || 1;
        var uType     = activePack.unitType === 'pc' ? 'pc' : 'kg';

        // Calculate Per Pc price if unit is 'pc', or Per Kg price if unit is 'kg'
        var unitRate    = unitQty > 0 ? (packSale / unitQty) : packSale;
        var regUnitRate = unitQty > 0 ? (packReg / unitQty) : packReg;
        var discountPct = (packReg > packSale && packReg > 0)
            ? Math.round(((packReg - packSale) / packReg) * 100)
            : 0;

        // 1. Update Main Sale Price & MRP
        var saleEl = document.getElementById('display-sale-price');
        if (saleEl) saleEl.textContent = formatMoney(packSale);

        var mrpEl = document.getElementById('display-mrp-price');
        if (mrpEl) {
            if (packReg > packSale) {
                mrpEl.textContent = 'M.R.P.: ' + formatMoney(packReg);
                mrpEl.style.display = 'inline';
            } else {
                mrpEl.style.display = 'none';
            }
        }

        // 2. Update Discount Badges
        var discRateEl = document.getElementById('display-discount-rate');
        var discPctEl  = document.getElementById('display-discount-pct');
        var galDiscEl  = document.getElementById('gallery-discount-badge');
        if (discountPct > 0) {
            if (discPctEl) discPctEl.textContent = discountPct;
            if (discRateEl) discRateEl.style.display = 'inline-flex';
            if (galDiscEl) {
                galDiscEl.textContent = 'Save ' + discountPct + '% Today';
                galDiscEl.style.display = 'inline-flex';
            }
        } else {
            if (discRateEl) discRateEl.style.display = 'none';
            if (galDiscEl) galDiscEl.style.display = 'none';
        }

        // 3. Update Pack Label Pill & Summary
        var packLblEl = document.getElementById('display-pack-label');
        if (packLblEl) packLblEl.textContent = activePack.label;
        var packSumEl = document.getElementById('selected-pack-summary');
        if (packSumEl) packSumEl.textContent = activePack.label;

        // 4. Update Per-Kg / Per-Pc Unit Rate Pill & Formula Breakdown
        var rateIconEl    = document.getElementById('unit-rate-icon');
        var rateHeadingEl = document.getElementById('unit-rate-heading');
        var rateValueEl   = document.getElementById('display-unit-rate');
        var formulaEl     = document.getElementById('display-unit-formula');
        var regRateEl     = document.getElementById('display-regular-unit-rate');

        if (uType === 'pc') {
            if (rateIconEl) rateIconEl.className = 'fa-solid fa-apple-whole';
            if (rateHeadingEl) rateHeadingEl.textContent = 'Per Pc Price:';
            if (rateValueEl) rateValueEl.textContent = formatMoney(unitRate) + ' / pc';
            var pcWord = unitQty > 1 ? 'pcs' : 'pc';
            if (formulaEl) {
                formulaEl.innerHTML = 'Calculated: ' + formatMoney(packSale) + ' &divide; ' + unitQty + ' ' + pcWord + ' = <strong>' + formatMoney(unitRate) + ' / pc</strong>';
            }
            if (regRateEl) {
                if (packReg > packSale) {
                    regRateEl.innerHTML = 'Regular Rate: <del>' + formatMoney(regUnitRate) + ' / pc</del>';
                    regRateEl.style.display = 'inline';
                } else {
                    regRateEl.style.display = 'none';
                }
            }
        } else {
            if (rateIconEl) rateIconEl.className = 'fa-solid fa-scale-balanced';
            if (rateHeadingEl) rateHeadingEl.textContent = 'Per Kg Price:';
            if (rateValueEl) rateValueEl.textContent = formatMoney(unitRate) + ' / kg';
            if (formulaEl) {
                formulaEl.innerHTML = 'Calculated: ' + formatMoney(packSale) + ' &divide; ' + activePack.unitDesc + ' = <strong>' + formatMoney(unitRate) + ' / kg</strong>';
            }
            if (regRateEl) {
                if (packReg > packSale) {
                    regRateEl.innerHTML = 'Regular Rate: <del>' + formatMoney(regUnitRate) + ' / kg</del>';
                    regRateEl.style.display = 'inline';
                } else {
                    regRateEl.style.display = 'none';
                }
            }
        }

        // 5. Update Total Price & Net Quantity for Selected Order Qty
        var totalPriceEl   = document.getElementById('display-total-price');
        var totalNetQtyEl  = document.getElementById('display-total-net-qty');
        var effRateSumEl   = document.getElementById('display-effective-rate-summary');
        var totalOrderCost = packSale * orderQty;
        var totalUnits     = unitQty * orderQty;

        if (totalPriceEl) totalPriceEl.textContent = formatMoney(totalOrderCost);
        if (totalNetQtyEl) {
            var totalNetLabel = uType === 'pc'
                ? (Math.round(totalUnits) + ' ' + (totalUnits > 1 ? 'pcs' : 'pc') + ' total')
                : (totalUnits < 1 ? Math.round(totalUnits * 1000) + 'g total' : parseFloat(totalUnits.toFixed(2)) + ' kg total');
            totalNetQtyEl.textContent = '(' + orderQty + ' × ' + activePack.label + ' = ' + totalNetLabel + ')';
        }
        if (effRateSumEl) {
            effRateSumEl.textContent = 'Effective Rate: ' + formatMoney(unitRate) + ' / ' + uType;
        }

        // 6. Update Highlights & SKU / Stock
        var hlUnitEl = document.getElementById('hl-selling-unit');
        var hlPackEl = document.getElementById('hl-pack-content');
        var hlRateEl = document.getElementById('hl-unit-rate');
        if (hlUnitEl) hlUnitEl.textContent = uType === 'pc' ? 'Per Piece (pc)' : 'By Weight (kg)';
        if (hlPackEl) hlPackEl.textContent = activePack.label + ' (' + activePack.unitDesc + ')';
        if (hlRateEl) hlRateEl.textContent = formatMoney(unitRate) + ' / ' + uType;

        var skuEl = document.getElementById('display-sku');
        if (skuEl && activePack.sku) skuEl.textContent = activePack.sku;

        var stockEl = document.getElementById('display-stock-status');
        if (stockEl) stockEl.textContent = 'In Stock (' + activePack.stock + ' packs available)';
    }

    window.switchGrcUnitMode = function(mode) {
        mode = (mode === 'pc') ? 'pc' : 'kg';
        activeUnitType = mode;

        var btnKg = document.getElementById('btn-mode-kg');
        var btnPc = document.getElementById('btn-mode-pc');
        var gridKg = document.getElementById('grc-variants-kg');
        var gridPc = document.getElementById('grc-variants-pc');

        if (mode === 'pc') {
            if (btnKg) btnKg.classList.remove('active');
            if (btnPc) btnPc.classList.add('active');
            if (gridKg) gridKg.style.display = 'none';
            if (gridPc) {
                gridPc.style.display = 'grid';
                var activeCard = gridPc.querySelector('.grc-pack-card.active') || gridPc.querySelectorAll('.grc-pack-card')[1] || gridPc.querySelector('.grc-pack-card');
                if (activeCard) selectGrcPackVariant(activeCard);
            }
        } else {
            if (btnPc) btnPc.classList.remove('active');
            if (btnKg) btnKg.classList.add('active');
            if (gridPc) gridPc.style.display = 'none';
            if (gridKg) {
                gridKg.style.display = 'grid';
                var activeCardKg = gridKg.querySelector('.grc-pack-card.active') || gridKg.querySelectorAll('.grc-pack-card')[1] || gridKg.querySelector('.grc-pack-card');
                if (activeCardKg) selectGrcPackVariant(activeCardKg);
            }
        }
    };

    window.selectGrcPackVariant = function(cardEl) {
        if (!cardEl) return;
        var parentGrid = cardEl.parentElement;
        if (parentGrid) {
            parentGrid.querySelectorAll('.grc-pack-card').forEach(function(c) {
                c.classList.remove('active');
            });
        }
        cardEl.classList.add('active');

        activePack = {
            id:        cardEl.getAttribute('data-id') || '',
            variantId: parseInt(cardEl.getAttribute('data-variant-id') || cardEl.getAttribute('data-id') || '0', 10) || null,
            unitType:  cardEl.getAttribute('data-unit-type') || activeUnitType,
            label:     cardEl.getAttribute('data-label') || '',
            unitQty:   parseFloat(cardEl.getAttribute('data-unit-qty') || '1'),
            unitDesc:  cardEl.getAttribute('data-unit-desc') || '',
            regPrice:  parseFloat(cardEl.getAttribute('data-reg') || '0'),
            salePrice: parseFloat(cardEl.getAttribute('data-sale') || '0'),
            sku:       cardEl.getAttribute('data-sku') || '',
            stock:     parseInt(cardEl.getAttribute('data-stock') || '20', 10)
        };

        recalculateGrcPricing();
    };

    window.changeGrcQty = function(delta) {
        var input = document.getElementById('product-qty-input');
        if (!input) return;
        var current = parseInt(input.value, 10) || 1;
        var maxStock = (activePack && activePack.stock) ? parseInt(activePack.stock, 10) : 20;
        var next = Math.max(1, Math.min(maxStock, current + delta));
        input.value = next;
        recalculateGrcPricing();
    };

    window.grcDetailAddToCart = function(e) {
        if (e) e.preventDefault();
        var qty = parseInt(document.getElementById('product-qty-input')?.value || '1', 10) || 1;
        var variantId = (activePack && (activePack.variantId || activePack.id))
            ? parseInt(activePack.variantId || activePack.id, 10)
            : null;
        if (isNaN(variantId) || variantId <= 0) variantId = null;

        var btn = document.getElementById('main-btn-atc');
        var origHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Adding...</span>';
            btn.disabled = true;
        }

        if (typeof grcAddToCart === 'function') {
            grcAddToCart(productId, qty, variantId);
            setTimeout(function() {
                if (btn) {
                    btn.innerHTML = origHtml;
                    btn.disabled = false;
                }
            }, 600);
        } else {
            var fd = new FormData();
            fd.append('product_id', productId);
            fd.append('quantity', qty);
            if (variantId) fd.append('variant_id', variantId);
            fetch('<?= site_url("cart/add"); ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (btn) {
                    btn.innerHTML = origHtml;
                    btn.disabled = false;
                }
                if (res && res.success) {
                    if (typeof renderGrcCartDrawer === 'function') renderGrcCartDrawer(res);
                    if (typeof showGrcToast === 'function') showGrcToast(res.message || 'Added to grocery bag!');
                    if (typeof openGrcCartDrawer === 'function') openGrcCartDrawer();
                } else {
                    if (typeof showGrcToast === 'function') showGrcToast((res && res.message) ? res.message : 'Could not add to bag', true);
                }
            })
            .catch(function() {
                if (btn) {
                    btn.innerHTML = origHtml;
                    btn.disabled = false;
                }
                if (typeof showGrcToast === 'function') showGrcToast('Network error adding to bag', true);
            });
        }
    };

    window.grcDetailBuyNow = function() {
        var qty = parseInt(document.getElementById('product-qty-input')?.value || '1', 10) || 1;
        var variantId = (activePack && (activePack.variantId || activePack.id))
            ? parseInt(activePack.variantId || activePack.id, 10)
            : null;
        if (isNaN(variantId) || variantId <= 0) variantId = null;

        var fd = new FormData();
        fd.append('product_id', productId);
        fd.append('quantity', qty);
        if (variantId) fd.append('variant_id', variantId);

        var btn = document.querySelector('.btn-grc-buy');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Processing...</span>';
        }

        fetch('<?= site_url("cart/add"); ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res && res.success) {
                window.location.href = '<?= site_url("checkout" . $grc_qs); ?>';
            } else {
                if (typeof showGrcToast === 'function') {
                    showGrcToast(res && res.message ? res.message : 'Could not add to bag', true);
                }
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-bolt"></i> <span>Buy Now</span>';
                }
            }
        })
        .catch(function() {
            window.location.href = '<?= site_url("checkout" . $grc_qs); ?>';
        });
    };

    window.activateReviewTab = function() {
        var revTab = document.getElementById('nav-review-tab');
        if (revTab) {
            revTab.click();
            revTab.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    };

    window.toggleProductHighlights = function() {
        var body = document.getElementById('product-highlights-collapse');
        var icon = document.getElementById('highlights-arrow-icon');
        if (!body) return;
        if (body.style.display === 'none') {
            body.style.display = 'block';
            if (icon) icon.className = 'fa-solid fa-chevron-up';
        } else {
            body.style.display = 'none';
            if (icon) icon.className = 'fa-solid fa-chevron-down';
        }
    };

    window.toggleProductSpecifications = function() {
        var body = document.getElementById('product-specs-collapse');
        var icon = document.getElementById('specs-arrow-icon');
        if (!body) return;
        if (body.style.display === 'none') {
            body.style.display = 'block';
            if (icon) icon.className = 'fa-solid fa-chevron-up';
        } else {
            body.style.display = 'none';
            if (icon) icon.className = 'fa-solid fa-chevron-down';
        }
    };

    recalculateGrcPricing();

    // Ensure both main product image slider (.slider-for) and thumbnail slider (.slider-nav)
    // are initialized and synced (1 image at a time in main slider, vertical thumbnails on desktop,
    // 4 horizontal thumbnails underneath on mobile/tablet)
    function initGrcProductSliders() {
        if (!window.jQuery || !jQuery.fn || !jQuery.fn.slick) return;
        var $for = jQuery('.product-slider-main.slider-for');
        var $nav = jQuery('.product-slider-nav.slider-nav');

        if ($for.length && !$for.hasClass('slick-initialized')) {
            $for.slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: true,
                fade: false,
                infinite: true,
                autoplay: false,
                asNavFor: '.slider-nav',
                appendArrows: '.product-slider-arrow',
                prevArrow: '<button type="button" class="slick-prev"><svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12" fill="none"><path d="M5.75 10.75L0.75 5.75L5.75 0.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button>',
                nextArrow: '<button type="button" class="slick-next"><svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12" fill="none"><path d="M0.75 10.75L5.75 5.75L0.75 0.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button>'
            });
        }

        if ($nav.length && !$nav.hasClass('slick-initialized')) {
            $nav.on('init reInit setPosition breakpoint', function() {
                $nav.find('.slick-list').css('height', '');
            });
            $nav.slick({
                slidesToShow: 4,
                slidesToScroll: 1,
                vertical: true,
                verticalSwiping: true,
                infinite: false,
                asNavFor: '.slider-for',
                centerMode: false,
                dots: false,
                arrows: false,
                autoplay: false,
                focusOnSelect: true,
                responsive: [
                    {
                        breakpoint: 1200,
                        settings: {
                            vertical: false,
                            verticalSwiping: false,
                            centerMode: false,
                            infinite: false,
                            slidesToShow: 4,
                            slidesToScroll: 1
                        }
                    }
                ]
            });
        }
        if ($nav.length) {
            $nav.find('.slick-list').css('height', '');
        }
    }

    document.addEventListener('DOMContentLoaded', initGrcProductSliders);
    window.addEventListener('load', initGrcProductSliders);
})();
</script>
