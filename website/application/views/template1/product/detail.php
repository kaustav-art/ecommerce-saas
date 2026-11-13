<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Collect all images (main image first, followed by gallery images)
$default_prod_rel = 'products/product-image.png';
$is_placeholder_img = function($p) {
    $b = strtolower(basename(trim((string)$p)));
    return $b === '' || $b === 'product-image.png';
};

$all_images = [];
if (empty($is_user_project)) {
    $all_images = array_fill(0, 4, $default_prod_rel);
} else {
    if (!empty($product['main_image']) && !$is_placeholder_img($product['main_image'])) {
        $all_images[] = $product['main_image'];
    }
    if (!empty($product['gallery_images_decoded']) && is_array($product['gallery_images_decoded'])) {
        foreach ($product['gallery_images_decoded'] as $g_img) {
            if (!empty($g_img) && !$is_placeholder_img($g_img) && !in_array($g_img, $all_images)) {
                $all_images[] = $g_img;
            }
        }
    }
    if (empty($all_images)) {
        $all_images = array_fill(0, 4, $default_prod_rel);
    }
}
$total_images = count($all_images);

// Price & discount calculations
$regular_price = round((float) $product['price']);
$sale_price = !empty($product['sale_price']) ? round((float) $product['sale_price']) : null;
$current_price = $sale_price ?: $regular_price;
$discount_percent = ($regular_price > 0 && $sale_price && $sale_price < $regular_price)
    ? round((($regular_price - $sale_price) / $regular_price) * 100)
    : 0;

// Dynamic Reviews & Rating Calculation (from database, strictly dynamic, no static fallback)
$actual_reviews = !empty($product['reviews']) ? $product['reviews'] : [];
$total_review_count = count($actual_reviews);
$rating_counts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
$sum_rating = 0;
foreach ($actual_reviews as $r) {
    $r_score = (int)($r['rating'] ?? 0);
    if ($r_score >= 1 && $r_score <= 5) {
        $rating_counts[$r_score]++;
        $sum_rating += $r_score;
    }
}
$computed_avg = $total_review_count > 0 ? round($sum_rating / $total_review_count, 1) : 0.0;
$display_review_count = $total_review_count;

// Variant configuration
$has_variants = !empty($product['variants']);
$color_attr = null;
$color_attr_slug = 'color';
$color_attr_name = 'Color';
$is_color_type_attr = true;

if (!empty($product['attributes'])) {
    if (!empty($product['attributes']['color'])) {
        $color_attr = $product['attributes']['color'];
        $color_attr_slug = 'color';
        $color_attr_name = $color_attr['name'] ?? 'Color';
        $is_color_type_attr = true;
    } else {
        foreach ($product['attributes'] as $slug => $attr_info) {
            if (
                (!empty($attr_info['type']) && $attr_info['type'] === 'color') ||
                strcasecmp($attr_info['name'] ?? '', 'color') === 0 ||
                stripos($slug, 'color') === 0
            ) {
                $color_attr = $attr_info;
                $color_attr_slug = $slug;
                $color_attr_name = $attr_info['name'] ?? 'Color';
                $is_color_type_attr = true;
                break;
            }
        }
    }
}

// Detect secondary option attribute (e.g., Size, Storage, or any active multiple_select attribute)
$multi_attr      = null;
$multi_attr_slug = '';
$multi_attr_name = 'Option';

if (!empty($product['attributes'])) {
    // First priority: look for attribute with multiple_select type or size or storage
    foreach ($product['attributes'] as $slug => $attr_info) {
        if ($color_attr && ($slug === $color_attr_slug || (!empty($attr_info['type']) && $attr_info['type'] === 'color'))) continue;
        if (!empty($attr_info['values']) && (!empty($attr_info['type']) && in_array(strtolower($attr_info['type']), ['multiple_select', 'multiselect', 'multiple'], true))) {
            $multi_attr      = $attr_info;
            $multi_attr_slug = $slug;
            $multi_attr_name = $attr_info['name'];
            break;
        }
    }
    // If no color attribute was found, look for a single-select attribute (e.g. Material / Storage) as the primary group selector
    if (empty($color_attr)) {
        foreach ($product['attributes'] as $slug => $attr_info) {
            if ($multi_attr && $slug === $multi_attr_slug) continue;
            if (!empty($attr_info['values']) && !in_array(strtolower($attr_info['type'] ?? ''), ['multiple_select', 'multiselect', 'multiple'], true)) {
                $color_attr = $attr_info;
                $color_attr_slug = $slug;
                $color_attr_name = $attr_info['name'] ?? ucfirst($slug);
                $is_color_type_attr = false;
                break;
            }
        }
    }
    // Second priority: any non-color attribute with values
    if (empty($multi_attr)) {
        foreach ($product['attributes'] as $slug => $attr_info) {
            if ($color_attr && $slug === $color_attr_slug) continue;
            if (!empty($attr_info['values'])) {
                $multi_attr      = $attr_info;
                $multi_attr_slug = $slug;
                $multi_attr_name = $attr_info['name'];
                break;
            }
        }
    }
}
$has_color = !empty($color_attr['values']);
$has_size = !empty($multi_attr);

// Color / Primary attribute map with variant photos & size/option inventory
$color_map = [];
$initial_color = '';
$initial_size = '';
$initial_variant_id = 0;
$initial_sku = $product['sku'] ?? ('SKU-' . ($product['id'] ?? '100'));

if ($has_color && !empty($color_attr['values'])) {
    foreach ($color_attr['values'] as $idx => $c_val) {
        $c_name = $c_val['value'];
        if ($idx === 0) {
            $initial_color = $c_name;
        }

        // Locate color variant photo and variant gallery images
        $c_photo = '';
        $c_gallery = [];
        if ($has_variants) {
            foreach ($product['variants'] as $pv) {
                $matches_color = false;
                if (!empty($pv['values'])) {
                    foreach ($pv['values'] as $val) {
                        $v_slug = $val['attribute_slug'] ?? '';
                        $v_name = $val['attribute_name'] ?? '';
                        if (
                            (strcasecmp($v_slug, $color_attr_slug) === 0 || strcasecmp($v_name, $color_attr_name) === 0 || ($is_color_type_attr && ($v_slug === 'color' || strcasecmp($v_name, 'color') === 0 || stripos($v_slug, 'color') === 0))) &&
                            strcasecmp($val['attribute_value'], $c_name) === 0
                        ) {
                            $matches_color = true;
                            break;
                        }
                    }
                }
                if ($matches_color) {
                    if (empty($c_photo) && !empty($pv['image'])) {
                        $c_photo = $pv['image'];
                    }
                    if (empty($c_gallery) && !empty($pv['gallery_images'])) {
                        $decoded_g = is_string($pv['gallery_images']) ? json_decode($pv['gallery_images'], true) : $pv['gallery_images'];
                        if (is_array($decoded_g) && !empty($decoded_g)) {
                            $c_gallery = array_values(array_filter($decoded_g));
                        }
                    }
                    if (!empty($c_photo) && !empty($c_gallery)) {
                        break;
                    }
                }
            }
        }
        if (empty($is_user_project)) {
            $c_photo  = $default_prod_rel;
            $c_images = array_fill(0, 4, $default_prod_rel);
        } else {
            if (empty($c_photo) || $is_placeholder_img($c_photo)) {
                $c_photo = (!empty($product['main_image']) && !$is_placeholder_img($product['main_image'])) ? $product['main_image'] : '';
            }

            // Color images list
            $c_images = [];
            if (!empty($c_photo) && !$is_placeholder_img($c_photo)) {
                $c_images[] = $c_photo;
            }
            if (!empty($c_gallery)) {
                foreach ($c_gallery as $g) {
                    if (!empty($g) && !$is_placeholder_img($g) && !in_array($g, $c_images)) {
                        $c_images[] = $g;
                    }
                }
            } else {
                // Keep product gallery images with variant photo as slot 0
                if (!empty($product['gallery_images_decoded']) && is_array($product['gallery_images_decoded'])) {
                    foreach ($product['gallery_images_decoded'] as $g_img) {
                        if (!empty($g_img) && !$is_placeholder_img($g_img) && !in_array($g_img, $c_images)) {
                            $c_images[] = $g_img;
                        }
                    }
                }
            }
            if (empty($c_images)) {
                $c_photo  = $default_prod_rel;
                $c_images = array_fill(0, 4, $default_prod_rel);
            } elseif (empty($c_photo)) {
                $c_photo = $c_images[0];
            }
        }

        $color_map[$c_name] = [
            'id'         => $c_val['id'],
            'name'       => $c_name,
            'color_code' => $c_val['color_code'] ?? '#333333',
            'image'      => $c_photo,
            'gallery'    => $c_gallery,
            'images'     => $c_images,
            'variant'    => null,
            'sizes'      => []
        ];
    }
}

// If initial color has variant images, prioritize them for initial view
if (!empty($initial_color) && !empty($color_map[$initial_color]['images'])) {
    $all_images = $color_map[$initial_color]['images'];
    $total_images = count($all_images);
}

// Populate size availability for each color and fallback size map
$size_variant_map = [];
$initial_variant  = null;

if ($has_variants) {
    foreach ($product['variants'] as $pv) {
        $v_color = '';
        $v_size  = '';
        if (!empty($pv['values'])) {
            foreach ($pv['values'] as $val) {
                $v_slug = $val['attribute_slug'] ?? '';
                $v_name = $val['attribute_name'] ?? '';
                if ($has_color && (strcasecmp($v_slug, $color_attr_slug) === 0 || strcasecmp($v_name, $color_attr_name) === 0 || ($is_color_type_attr && (strcasecmp($v_slug, 'color') === 0 || strcasecmp($v_name, 'color') === 0 || stripos($v_slug, 'color') === 0)))) {
                    $v_color = $val['attribute_value'];
                } elseif ($multi_attr_slug && (strcasecmp($v_slug, $multi_attr_slug) === 0 || strcasecmp($v_name, $multi_attr_name) === 0)) {
                    $v_size = $val['attribute_value'];
                } elseif (!$has_color && (strcasecmp($v_slug, 'size') === 0 || strcasecmp($v_name, 'size') === 0 || strcasecmp($v_slug, 'storage') === 0 || strcasecmp($v_name, 'storage') === 0)) {
                    $v_size = $val['attribute_value'];
                }
            }
        }

        $v_highlights = !empty($pv['highlights']) ? (is_array($pv['highlights']) ? $pv['highlights'] : (json_decode($pv['highlights'], true) ?: [])) : [];
        $v_specs = !empty($pv['specifications']) ? (is_array($pv['specifications']) ? $pv['specifications'] : (json_decode($pv['specifications'], true) ?: [])) : [];

        $var_entry = [
            'variant_id'     => (int) $pv['id'],
            'title'          => !empty($pv['title']) ? $pv['title'] : $product['title'],
            'sku'            => $pv['sku'],
            'price'          => round((float) $pv['price']),
            'sale_price'     => (isset($pv['sale_price']) && $pv['sale_price'] !== null && $pv['sale_price'] !== '') ? round((float) $pv['sale_price']) : null,
            'stock'          => (int) $pv['stock_quantity'],
            'image'          => !empty($pv['image']) ? $pv['image'] : null,
            'color'          => $v_color,
            'size'           => $v_size,
            'highlights'     => $v_highlights,
            'specifications' => $v_specs
        ];

        if ($v_color && isset($color_map[$v_color])) {
            if (empty($color_map[$v_color]['variant']) || ((int)$color_map[$v_color]['variant']['stock'] <= 0 && (int)$pv['stock_quantity'] > 0)) {
                $color_map[$v_color]['variant'] = $var_entry;
            }
            $size_key = ($v_size !== '') ? $v_size : '_default';
            $color_map[$v_color]['sizes'][$size_key] = $var_entry;
            // Pick initial in-stock variant for initial color
            if ($v_color === $initial_color && empty($initial_variant) && (int) $pv['stock_quantity'] > 0) {
                if ($v_size !== '') {
                    $initial_size = $v_size;
                }
                $initial_variant_id = (int) $pv['id'];
                $initial_sku        = $pv['sku'];
                $initial_variant    = $var_entry;
            }
        }
        if ($v_size) {
            $size_variant_map[$v_size] = $var_entry;
            if (empty($initial_size) && (int) $pv['stock_quantity'] > 0 && empty($initial_color)) {
                $initial_size       = $v_size;
                $initial_variant_id = (int) $pv['id'];
                $initial_sku        = $pv['sku'];
                $initial_variant    = $var_entry;
            }
        }
    }
}

// Fallbacks for initial selection
if (empty($initial_size) && $has_size && !empty($multi_attr['values'])) {
    $initial_size = $multi_attr['values'][0]['value'];
}
if ($has_variants && empty($initial_variant_id) && !empty($product['variants'])) {
    $initial_variant_id = (int) $product['variants'][0]['id'];
    $initial_sku        = $product['variants'][0]['sku'];
}

// Resolve initial variant if not resolved yet
if (empty($initial_variant) && $has_variants) {
    if (!empty($initial_color) && !empty($initial_size) && !empty($color_map[$initial_color]['sizes'][$initial_size])) {
        $initial_variant = $color_map[$initial_color]['sizes'][$initial_size];
    } elseif (!empty($initial_color) && !empty($color_map[$initial_color]['variant'])) {
        $initial_variant = $color_map[$initial_color]['variant'];
    } elseif (!empty($size_variant_map[$initial_size])) {
        $initial_variant = $size_variant_map[$initial_size];
    } elseif (!empty($product['variants'])) {
        $first_v = $product['variants'][0];
        $initial_variant = [
            'variant_id'     => (int) $first_v['id'],
            'title'          => !empty($first_v['title']) ? $first_v['title'] : $product['title'],
            'sku'            => $first_v['sku'],
            'price'          => round((float) $first_v['price']),
            'sale_price'     => (isset($first_v['sale_price']) && $first_v['sale_price'] !== null && $first_v['sale_price'] !== '') ? round((float) $first_v['sale_price']) : null,
            'stock'          => (int) $first_v['stock_quantity'],
            'image'          => !empty($first_v['image']) ? $first_v['image'] : null,
            'color'          => $initial_color,
            'size'           => $initial_size,
            'highlights'     => !empty($first_v['highlights']) ? (is_array($first_v['highlights']) ? $first_v['highlights'] : (json_decode($first_v['highlights'], true) ?: [])) : [],
            'specifications' => !empty($first_v['specifications']) ? (is_array($first_v['specifications']) ? $first_v['specifications'] : (json_decode($first_v['specifications'], true) ?: [])) : []
        ];
    }
}

if (!empty($initial_variant)) {
    $initial_variant_id = (int) $initial_variant['variant_id'];
    if (!empty($initial_variant['sku'])) {
        $initial_sku = $initial_variant['sku'];
    }
    $regular_price = round((float) $initial_variant['price']);
    $sale_price    = ($initial_variant['sale_price'] !== null) ? round((float) $initial_variant['sale_price']) : null;
    $current_price = ($sale_price !== null && $sale_price > 0) ? $sale_price : $regular_price;
    $discount_percent = ($regular_price > 0 && $sale_price && $sale_price < $regular_price)
        ? round((($regular_price - $sale_price) / $regular_price) * 100)
        : 0;
}
?>

<style>
/* ==========================================================================
   PRODUCT DETAILS CUSTOM STYLES (Grid Images, Variant Slider, Modals)
   ========================================================================== */

/* 2x2 Image Grid Layout (as in product_image_shown.PNG) */
.product-grid-gallery {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}
.product-grid-item {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    background-color: #f8f9fa;
    aspect-ratio: 3 / 4;
    cursor: pointer;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.product-grid-item:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}
.product-grid-item img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
    transition: transform 0.35s ease;
}
.product-grid-item:hover img {
    transform: scale(1.03);
}

/* Floating Action Buttons on 2nd Image (Wishlist & Fullscreen - Perfect Circles) */
.grid-floating-actions {
    position: absolute;
    top: 10px;
    right: 10px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    z-index: 10;
}
.grid-action-btn,
button.grid-action-btn {
    width: 38px !important;
    height: 38px !important;
    min-width: 38px !important;
    max-width: 38px !important;
    min-height: 38px !important;
    max-height: 38px !important;
    padding: 0 !important;
    margin: 0 !important;
    gap: 0 !important;
    aspect-ratio: 1 / 1 !important;
    border-radius: 50% !important;
    background: #ffffff !important;
    border: 1px solid #d1d5db !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    line-height: 1 !important;
    flex-shrink: 0 !important;
    color: #181818 !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important;
    cursor: pointer;
    transition: all 0.2s ease;
}
.grid-action-btn i {
    font-size: 15px;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.grid-action-btn:hover,
button.grid-action-btn:hover {
    background: #181818 !important;
    color: #ffffff !important;
    border-color: #181818 !important;
    transform: translateY(-2px);
}
.grid-action-btn.active,
button.grid-action-btn.active {
    background: #dc3545 !important;
    color: #ffffff !important;
    border-color: #dc3545 !important;
}

/* 1px border for desktop Add to cart button */
.btn-add-to-cart, #main-btn-atc {
    border: 1px solid #181818 !important;
}
.btn-add-to-cart:hover, #main-btn-atc:hover {
    border-color: var(--primary, #000) !important;
}

/* +N Overlay on 4th Image */
.product-grid-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border-radius: 8px;
    transition: background 0.25s ease;
    z-index: 5;
}
.product-grid-item:hover .product-grid-overlay {
    background: rgba(0, 0, 0, 0.6);
}
.product-grid-overlay .overlay-text {
    font-size: 38px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: -0.5px;
    user-select: none;
}

/* Variant Switch Loading Overlay */
.product-variant-loader-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.75);
    backdrop-filter: blur(2px);
    -webkit-backdrop-filter: blur(2px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
    border-radius: 12px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease;
}
.product-variant-loader-overlay.active {
    opacity: 1;
    pointer-events: all;
    display: flex !important;
}
.variant-loader-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 16px 24px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    border: 1px solid rgba(0, 0, 0, 0.08);
}
.variant-loader-label {
    font-size: 13px;
    font-weight: 600;
    color: #212121;
    letter-spacing: 0.1px;
}

/* Pricing Header Section (as in varient_products.PNG) */
.product-price-block {
    display: flex;
    align-items: baseline;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 6px;
}
.product-discount-rate {
    color: #007600;
    font-size: 28px;
    font-weight: 400;
    line-height: 1;
    display: inline-flex;
    align-items: center;
}
.product-current-price {
    font-size: 30px;
    font-weight: 700;
    color: #0F1111;
    line-height: 1;
}
.product-mrp-price {
    color: #565959;
    font-size: 14px;
    text-decoration: line-through;
}
.product-tax-note {
    color: #565959;
    font-size: 12px;
    margin-bottom: 8px;
}

/* Color Badge Selection (matching color_badge.PNG) */
.color-badge-heading {
    display: flex;
    align-items: baseline;
    gap: 6px;
    margin-bottom: 8px;
}
.color-badge-title {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a1a;
    letter-spacing: -0.2px;
}
.color-badge-name {
    font-size: 16px;
    font-weight: 600;
    color: #4b5563;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.color-variant-container {
    position: relative;
    width: 100%;
    margin-bottom: 10px;
}

.color-slider-track {
    display: flex;
    flex-wrap: nowrap;
    gap: 12px;
    overflow-x: auto;
    scroll-behavior: smooth;
    scrollbar-width: none;
    -ms-overflow-style: none;
    padding: 4px 2px;
}
.color-slider-track::-webkit-scrollbar {
    display: none;
}

/* Portrait capsule thumbnail badge card (color_badge.PNG) */
.color-thumb-card {
    flex: 0 0 auto;
    width: 70px;
    height: 80px;
    border: 1px solid #dcdfe4;
    border-radius: 12px;
    padding: 3.5px;
    cursor: pointer;
    background: #ffffff;
    box-sizing: border-box;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    user-select: none;
}

.color-thumb-card:hover {
    border-color: #6b7280;
    transform: translateY(-1px);
}

.color-thumb-card.active {
    border: 2px solid #1a1a1a;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12);
}

.color-thumb-card img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 8px;
    display: block;
    pointer-events: none;
}

/* Circular floating navigation arrows (as shown in color_badge.PNG) */
.color-slider-arrow,
button.color-slider-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 36px !important;
    height: 36px !important;
    min-width: 36px !important;
    max-width: 36px !important;
    min-height: 36px !important;
    max-height: 36px !important;
    padding: 0 !important;
    margin: 0 !important;
    gap: 0 !important;
    aspect-ratio: 1 / 1 !important;
    border-radius: 50% !important;
    background: rgba(255, 255, 255, 0.95) !important;
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    border: 1px solid rgba(0, 0, 0, 0.1) !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex-shrink: 0 !important;
    cursor: pointer;
    z-index: 5;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.16) !important;
    transition: all 0.2s ease;
    color: #1a1a1a !important;
    font-size: 13px !important;
    line-height: 1 !important;
}

.color-slider-arrow:hover,
button.color-slider-arrow:hover {
    background: #181818 !important;
    color: #ffffff !important;
    border-color: #181818 !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.22) !important;
    transform: translateY(-50%) scale(1.05);
}

.color-slider-arrow.prev {
    left: -4px;
}

.color-slider-arrow.next {
    right: -4px;
}

/* Size Chips (as in varient_products.PNG) */
.variant-size-chip {
    min-width: 50px;
    height: 42px;
    padding: 0 14px;
    border: 1px solid #d5d9d9;
    border-radius: 6px;
    background: #ffffff;
    color: #0F1111;
    font-size: 14px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s ease;
    user-select: none;
}
.variant-size-chip:hover:not(.is-out-of-stock) {
    border-color: #007185;
    background: #f7fafa;
}
.variant-size-chip.active {
    border: 2px solid #007185;
    background: #f0f8ff;
    color: #007185;
    font-weight: 700;
    box-shadow: 0 0 0 1px #007185;
}
/* Strikethrough & Dashed Border for Out-Of-Stock Sizes */
.variant-size-chip.is-out-of-stock {
    position: relative;
    color: #888888 !important;
    background-color: #fafafa !important;
    border: 1.5px dashed #cccccc !important;
    cursor: not-allowed;
    opacity: 0.7;
    overflow: hidden;
}
.variant-size-chip.is-out-of-stock::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to top right, transparent calc(50% - 1px), #c0392b calc(50% - 1px), #c0392b calc(50% + 1px), transparent calc(50% + 1px));
    pointer-events: none;
}

/* Lightbox Modal */
#product-gallery-lightbox .modal-content {
    background-color: #141414;
    border: none;
    border-radius: 12px;
    overflow: hidden;
}
#product-gallery-lightbox .lightbox-main-img-wrap {
    min-height: 520px;
    max-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}
#product-gallery-lightbox .lightbox-main-img-wrap img {
    max-height: 70vh;
    max-width: 100%;
    object-fit: contain;
    border-radius: 6px;
}
.lightbox-arrow,
button.lightbox-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 46px !important;
    height: 46px !important;
    min-width: 46px !important;
    max-width: 46px !important;
    min-height: 46px !important;
    max-height: 46px !important;
    padding: 0 !important;
    margin: 0 !important;
    gap: 0 !important;
    aspect-ratio: 1 / 1 !important;
    border-radius: 50% !important;
    background: rgba(255, 255, 255, 0.22) !important;
    border: 1px solid rgba(255, 255, 255, 0.3) !important;
    color: #ffffff !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    line-height: 1 !important;
    flex-shrink: 0 !important;
    font-size: 17px !important;
    cursor: pointer;
    transition: all 0.2s ease;
    z-index: 10;
}
.lightbox-arrow:hover,
button.lightbox-arrow:hover {
    background: #ffffff !important;
    color: #141414 !important;
    border-color: #ffffff !important;
}
.lightbox-arrow.prev { left: 20px; }
.lightbox-arrow.next { right: 20px; }
.modal-header .btn-close {
    padding: 0.5rem !important;
    width: 1em !important;
    height: 1em !important;
    min-width: 0 !important;
    border-radius: 0.25rem !important;
}
.lightbox-thumbs-strip {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding: 12px;
    justify-content: center;
    background: #0a0a0a;
}
.lightbox-thumb-item {
    width: 60px;
    height: 70px;
    border-radius: 4px;
    overflow: hidden;
    cursor: pointer;
    opacity: 0.5;
    border: 2px solid transparent;
    transition: all 0.2s ease;
}
.lightbox-thumb-item.active,
.lightbox-thumb-item:hover {
    opacity: 1;
    border-color: #ffffff;
}
.lightbox-thumb-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Brand Link */
.brand-store-link {
    font-size: 14px;
    color: #007185;
    transition: color 0.2s ease;
}
.brand-store-link:hover {
    color: #c7511f;
    text-decoration: underline !important;
}

/* ==========================================================================
   MOBILE SCREEN PRODUCT DETAILS (as in product_deatils_mobile.jpeg)
   ========================================================================== */
@media (max-width: 767.98px) {
    /* Layout & Spacing */
    .flat-spacing {
        padding-top: 10px !important;
        padding-bottom: 30px !important;
    }
    .tf-main-product {
        padding-bottom: 12px !important;
    }
    .tf-breadcrumb,
    .tf-breadcrumb-prev-next {
        display: none !important;
    }
    .tf-product-media-wrap {
        position: relative !important;
        top: 0 !important;
        margin: 0 -12px !important;
        border-radius: 0 !important;
        background: #ffffff;
    }
    .product-gallery-stage {
        position: relative;
        width: 100%;
        overflow: hidden;
        background: #f8f9fa;
    }

    /* Single Prominent Swipeable Image Slider */
    .product-grid-gallery {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        overflow-x: auto !important;
        scroll-snap-type: x mandatory !important;
        -webkit-overflow-scrolling: touch !important;
        scrollbar-width: none !important;
        gap: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        border-radius: 0 !important;
    }
    .product-grid-gallery::-webkit-scrollbar {
        display: none !important;
    }
    .product-grid-item {
        flex: 0 0 100% !important;
        width: 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;
        scroll-snap-align: start !important;
        aspect-ratio: 3 / 4 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        background-color: #f8f9fa !important;
        overflow: hidden !important;
        border: none !important;
    }
    .product-grid-item img {
        width: 100% !important;
        height: 100% !important;
        object-fit: contain !important;
        border-radius: 0 !important;
        display: block !important;
    }
    .product-grid-overlay {
        display: none !important;
    }

    /* Top-Right Floating Actions (Wishlist & Share in product_deatils_mobile.jpeg) */
    .mobile-gallery-floating-actions {
        position: absolute !important;
        top: 14px !important;
        right: 14px !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 10px !important;
        z-index: 20 !important;
    }
    .mobile-floating-btn,
    button.mobile-floating-btn {
        width: 40px !important;
        height: 40px !important;
        min-width: 40px !important;
        max-width: 40px !important;
        min-height: 40px !important;
        max-height: 40px !important;
        padding: 0 !important;
        margin: 0 !important;
        gap: 0 !important;
        aspect-ratio: 1 / 1 !important;
        border-radius: 50% !important;
        background: #ffffff !important;
        border: 1px solid #d1d5db !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.14) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        line-height: 1 !important;
        flex-shrink: 0 !important;
        color: #1e2022 !important;
        font-size: 16px !important;
        cursor: pointer;
        transition: transform 0.15s ease, background 0.15s ease;
        outline: none;
    }
    .mobile-floating-btn:active {
        transform: scale(0.92);
    }
    .mobile-floating-btn.active, .mobile-floating-btn.active i {
        color: #e53935 !important;
    }

    /* Bottom-Left Floating Rating Badge (4 ★ | 683 in product_deatils_mobile.jpeg) */
    .mobile-gallery-rating-badge {
        position: absolute !important;
        bottom: 14px !important;
        left: 14px !important;
        background: #ffffff !important;
        padding: 4px 9px !important;
        border-radius: 6px !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12) !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        color: #212121 !important;
        z-index: 20 !important;
        cursor: pointer;
        line-height: 1;
        user-select: none;
    }
    .mobile-gallery-rating-badge .rating-star {
        color: #388e3c !important;
        font-size: 11px !important;
    }
    .mobile-gallery-rating-badge .rating-divider {
        color: #d1d5db !important;
        font-weight: 300 !important;
    }
    .mobile-gallery-rating-badge .rating-count {
        color: #616161 !important;
        font-weight: 500 !important;
        font-size: 12.5px !important;
    }

    /* Horizontal Slide Progress Indicator Bar underneath image */
    .mobile-slider-indicator-container {
        width: 100% !important;
        padding: 8px 16px 4px !important;
        display: flex !important;
        align-items: center !important;
        background: #ffffff !important;
    }
    .mobile-slider-indicator-track {
        width: 100% !important;
        height: 3px !important;
        background: #e5e7eb !important;
        border-radius: 2px !important;
        position: relative !important;
        overflow: hidden !important;
    }
    .mobile-slider-indicator-bar {
        height: 100% !important;
        background: #111827 !important;
        border-radius: 2px !important;
        transition: transform 0.12s ease-out !important;
        will-change: transform;
    }

    /* Info wrap & Typography */
    .tf-product-info-wrap {
        padding: 10px 4px 20px !important;
    }
    .tf-product-info-heading .name {
        font-size: 20px !important;
        line-height: 1.35 !important;
    }

    /* Product Highlights & Specifications Grid Layout */
    .specs-grid-layout {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        column-gap: 20px !important;
        row-gap: 8px !important;
    }
    .spec-grid-item {
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 6px;
        min-width: 0;
    }
    .spec-grid-item.spec-full-width {
        grid-column: 1 / -1 !important;
    }
    .spec-item-key {
        font-size: 13px;
        color: #717478;
        margin-bottom: 2px;
        font-weight: 400;
        word-break: break-word;
    }
    .spec-item-val {
        font-size: 14px;
        color: #212121;
        font-weight: 500;
        line-height: 1.35;
        word-break: break-word;
    }
    #btn-see-more-specs,
    #btn-see-more-specs:hover,
    #btn-see-more-specs:focus,
    #btn-see-more-specs:active {
        background-color: #ffffff !important;
        color: #374151 !important;
        border: 1px solid #d1d5db !important;
        box-shadow: none !important;
        transform: none !important;
        outline: none !important;
        transition: none !important;
    }

    /* Color badge section */
    .color-badge-heading {
        font-size: 15px !important;
        margin-bottom: 10px !important;
    }
    .color-badge-title {
        font-weight: 700 !important;
        color: #1e2022 !important;
    }
    .color-badge-name {
        font-weight: 500 !important;
        color: #1e2022 !important;
        text-transform: capitalize !important;
    }
    .color-thumb-card {
        width: 66px !important;
        height: 82px !important;
        border-radius: 10px !important;
        padding: 3px !important;
        border: 1px solid #e5e7eb !important;
        background: #ffffff !important;
    }
    .color-thumb-card.active {
        border: 2px solid #000000 !important;
        box-shadow: none !important;
    }
    .color-thumb-card img {
        border-radius: 7px !important;
        width: 100% !important;
        height: 100% !important;
        object-fit: contain !important;
    }

    /* Size selector section */
    .variant-size-chip {
        min-width: 52px !important;
        height: 42px !important;
        border-radius: 8px !important;
        border: 1px solid #d1d5db !important;
        font-size: 15px !important;
        font-weight: 500 !important;
        color: #1e2022 !important;
        background: #ffffff !important;
    }
    .variant-size-chip.active {
        border: 2px solid #000000 !important;
        color: #000000 !important;
        font-weight: 700 !important;
        background: #ffffff !important;
        box-shadow: none !important;
    }

    /* Fixed mobile bottom action bar: Add to cart & Buy now (product_deatils_mobile.jpeg) */
    .mobile-bottom-action-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #ffffff;
        padding: 10px 14px calc(10px + env(safe-area-inset-bottom, 0px));
        box-shadow: 0 -3px 14px rgba(0, 0, 0, 0.1);
        z-index: 1040;
        display: flex;
        gap: 10px;
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease;
        will-change: transform, opacity;
    }
    .mobile-bottom-action-bar.mobile-bar-hidden {
        transform: translateY(100%) !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }
    .btn-mobile-action {
        flex: 1;
        height: 48px;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        text-transform: none;
        letter-spacing: 0.1px;
        transition: opacity 0.15s ease, transform 0.1s ease;
        outline: none;
        text-decoration: none;
    }
    .btn-mobile-action:active {
        transform: scale(0.98);
        opacity: 0.9;
    }
    .btn-mobile-atc {
        background: #ffffff;
        border: 1px solid #d1d5db;
        color: #1e2022;
    }
    .btn-mobile-buy {
        background: #ffc200;
        border: none;
        color: #1e2022;
    }

    /* In-page mobile attached action bar (exact same style as mobile bottom bar) */
    .mobile-bottom-action-bar-attached {
        display: flex;
        gap: 10px;
        width: 100%;
        padding: 10px 0 4px 0;
        margin-top: 10px;
    }

    /* Mobile Responsive Tabs (Description, Reviews, Shipping, Policies) */
    #tab-customer-reviews {
        margin-top: 24px !important;
    }
    .widget-tabs.style-1 .widget-menu-tab {
        display: flex !important;
        justify-content: flex-start !important;
        align-items: center !important;
        gap: 18px !important;
        padding: 0 2px 10px !important;
        margin-bottom: 14px !important;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        -webkit-overflow-scrolling: touch !important;
        scrollbar-width: none !important;
        border-bottom: 1.5px solid #e5e7eb !important;
        white-space: nowrap !important;
    }
    .widget-tabs.style-1 .widget-menu-tab::-webkit-scrollbar {
        display: none !important;
    }
    .widget-tabs.style-1 .widget-menu-tab .item-title {
        font-size: 14.5px !important;
        line-height: 1.35 !important;
        min-width: max-content !important;
        padding: 4px 2px 10px !important;
        color: #6b7280 !important;
        font-weight: 500 !important;
        cursor: pointer !important;
        position: relative !important;
    }
    .widget-tabs.style-1 .widget-menu-tab .item-title.active {
        color: #111827 !important;
        font-weight: 700 !important;
    }
    .widget-tabs.style-1 .widget-menu-tab .item-title::after {
        bottom: -1.5px !important;
        height: 2.5px !important;
        background-color: #111827 !important;
        border-radius: 2px !important;
    }
    .widget-tabs.style-1 .widget-content-inner {
        padding: 16px 12px !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 10px !important;
        background: #ffffff !important;
    }
    .tab-description {
        display: flex !important;
        flex-direction: column !important;
        gap: 18px !important;
    }
    .tab-description .right,
    .tab-description .left {
        width: 100% !important;
    }
    .tab-description .table-responsive {
        margin-top: 10px !important;
        border-radius: 8px !important;
        overflow-x: auto !important;
    }
    .tab-description .list-icon-guideline {
        gap: 12px !important;
        flex-wrap: wrap !important;
    }
    .tab-reviews .tab-reviews-heading {
        gap: 18px !important;
        margin-bottom: 20px !important;
        flex-direction: column !important;
        align-items: stretch !important;
    }
    .tab-reviews .tab-reviews-heading .top {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        gap: 16px !important;
        width: 100% !important;
    }
    .tab-reviews .rating-score {
        width: 100% !important;
    }
    .tab-reviews .rating-score .item {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        width: 100% !important;
    }
    .tab-reviews .rating-score .line-bg {
        flex: 1 !important;
    }
    .tab-reviews .btn-comment-review {
        width: 100% !important;
        text-align: center !important;
        margin-top: 6px !important;
    }
    .form-write-review .cols {
        display: flex !important;
        flex-direction: column !important;
        gap: 12px !important;
    }
    .form-write-review .cols fieldset {
        width: 100% !important;
        margin-bottom: 0 !important;
    }
    .reply-comment-wrap .reply-comment-item {
        padding: 12px 0 !important;
    }
    .tab-shipping,
    .tab-policies {
        display: flex !important;
        flex-direction: column !important;
        gap: 16px !important;
    }
}

/* Breadcrumb compact spacing */
.tf-breadcrumb {
    padding: 8px 0;
}
.tf-breadcrumb-wrap {
    padding: 0 !important;
}

/* Desktop styles */
@media (min-width: 768px) {
    .product-grid-item.grid-overflow-item {
        display: none !important;
    }
    .mobile-gallery-floating-actions,
    .mobile-gallery-rating-badge,
    .mobile-slider-indicator-container,
    .mobile-bottom-action-bar,
    .mobile-bottom-action-bar-attached {
        display: none !important;
    }
    .flat-spacing {
        padding-top: 18px !important;
        padding-bottom: 24px !important;
    }
    .tf-product-info-list .tf-product-info-choose-option {
        gap: 12px !important;
    }
}

/* CircularPrev / Next Slider Buttons & Bazaro Popular Products Slider Style (.aqf-seller-area / .aq-product-slide-wrap) */
.color-slider-arrow,
button.color-slider-arrow {
    display: none !important;
}
.aqf-seller-area.fix {
    overflow: visible;
}
.aq-product-slide-wrap.p-relative {
    position: relative;
}
@media (max-width: 575px) {
    .aq-product-arrow {
        display: none;
    }
}
.aq-product-arrow button,
.aq-product-arrow button.aq-product-prev,
.aq-product-arrow button.aq-product-next {
    position: absolute;
    top: 38%;
    transform: translateY(-50%);
    inset-inline-start: -30px;
    z-index: 20;
    height: 46px !important;
    width: 46px !important;
    min-width: 46px !important;
    max-width: 46px !important;
    min-height: 46px !important;
    max-height: 46px !important;
    padding: 0 !important;
    margin: 0 !important;
    gap: 0 !important;
    aspect-ratio: 1 / 1 !important;
    border-radius: 50% !important;
    border: 1px solid rgba(0, 0, 0, 0.08) !important;
    outline: none !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    line-height: 1 !important;
    flex-shrink: 0 !important;
    opacity: 0;
    visibility: hidden;
    color: #141414 !important;
    background: #ffffff !important;
    box-shadow: 0 3px 10px rgba(1, 15, 28, 0.16) !important;
    transition: all 0.3s ease-out;
}
.aq-product-arrow button span {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 100%;
    height: 100%;
    line-height: 1 !important;
}
.aq-product-arrow button svg {
    display: block;
    transform: translateX(-1px);
}
.aq-product-arrow button.aq-product-next {
    inset-inline-start: auto;
    inset-inline-end: -30px;
}
.aq-product-arrow button.aq-product-next svg {
    transform: translateX(1px);
}
.aq-product-arrow button:hover {
    color: #ffffff !important;
    border-color: #141414 !important;
    background-color: #141414 !important;
}
.aq-product-slide-wrap:hover .aq-product-arrow button {
    opacity: 1;
    visibility: visible;
    inset-inline-start: -23px;
}
.aq-product-slide-wrap:hover .aq-product-arrow button.aq-product-next {
    inset-inline-start: auto;
    inset-inline-end: -23px;
}
.card-product {
    height: 100%;
    display: flex;
    flex-direction: column;
}
.card-product .card-product-wrapper {
    aspect-ratio: 3 / 4 !important;
    width: 100% !important;
    overflow: hidden !important;
    border-radius: 8px;
    background-color: #f8f9fa;
    position: relative;
}
.card-product .card-product-wrapper .product-img {
    display: block;
    width: 100% !important;
    height: 100% !important;
}
.card-product .card-product-wrapper .product-img img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    display: block;
}
.product-badge-group {
    position: absolute;
    top: 16px;
    left: 16px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 5px;
    z-index: 5;
    pointer-events: none;
}
.product-badge {
    display: inline-block;
    font-size: 13px;
    font-weight: 700;
    line-height: 1;
    padding: 5px 12px;
    border-radius: 40px;
    color: #ffffff;
    background-color: #DA3F3F;
}
.product-badge.badge-sale {
    background-color: #DA3F3F;
}
.product-badge.badge-flash-sale {
    background-color: #DA3F3F;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>

        <!-- breadcrumb -->
        <div class="tf-breadcrumb d-none d-md-block">
            <div class="container">
                <div class="tf-breadcrumb-wrap">
                    <div class="tf-breadcrumb-list">
                        <a href="<?= site_url('home'); ?>" class="text text-caption-1">Homepage</a>
                        <i class="icon icon-arrRight"></i>
                        <a href="<?= site_url('shop'); ?>" class="text text-caption-1">Shop</a>
                        <i class="icon icon-arrRight"></i>
                        <a href="<?= site_url('shop/' . ($product['category_slug'] ?? '')); ?>" class="text text-caption-1"><?= html_escape($product['category_name'] ?? 'Shop'); ?></a>
                        <i class="icon icon-arrRight"></i>
                        <span class="text text-caption-1"><?= html_escape($product['title'] ?? ''); ?></span>
                    </div>
                    <div class="tf-breadcrumb-prev-next">
                        <a href="<?= site_url('shop'); ?>" class="tf-breadcrumb-back" title="Back to shop">
                            <i class="icon icon-squares-four"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- /breadcrumb -->

        <!-- Product_Main -->
        <section class="flat-spacing">
            <div class="tf-main-product section-image-zoom">
                <div class="container">
                    <div class="row gx-5">
                        
                        <!-- LEFT COLUMN: 2x2 Grid Gallery (product_image_shown.PNG) -->
                        <div class="col-md-7 mb-md-0">
                            <div class="tf-product-media-wrap sticky-top" style="top: 100px;">
                                
                                <div class="product-gallery-stage position-relative">
                                    <!-- Mobile Floating Action Buttons (Wishlist & Share in product_deatils_mobile.jpeg) -->
                                    <div class="mobile-gallery-floating-actions d-md-none">
                                        <button type="button" class="mobile-floating-btn mobile-wishlist-btn <?= !empty($is_in_wishlist) ? 'active' : ''; ?>" id="btn-mobile-wishlist" onclick="event.preventDefault(); event.stopPropagation(); toggleWishlist(<?= $product['id']; ?>, this)" title="Wishlist" aria-label="Wishlist">
                                            <i class="<?= !empty($is_in_wishlist) ? 'fa-solid fa-heart text-danger' : 'fa-regular fa-heart'; ?>"></i>
                                        </button>
                                        <button type="button" class="mobile-floating-btn mobile-share-btn" id="btn-mobile-share" onclick="handleMobileShare()" title="Share" aria-label="Share">
                                            <i class="fa-regular fa-paper-plane" style="transform: rotate(10deg); font-size: 16px;"></i>
                                        </button>
                                    </div>

                                    <!-- Mobile Floating Rating Badge (Dynamic - shown only when reviews exist) -->
                                    <?php if ($total_review_count > 0): ?>
                                    <div class="mobile-gallery-rating-badge d-md-none" onclick="activateReviewTab()">
                                        <span class="rating-score"><?= number_format($computed_avg, 1); ?></span>
                                        <i class="fa-solid fa-star rating-star"></i>
                                        <span class="rating-divider">|</span>
                                        <span class="rating-count"><?= number_format($total_review_count); ?></span>
                                    </div>
                                    <?php endif; ?>

                                    <div class="product-grid-gallery" id="product-grid-gallery" onscroll="updateMobileSliderIndicator()">
                                        <?php
                                        // Render all images for mobile carousel; desktop hides items 5+ via .grid-overflow-item CSS
                                        for ($i = 0; $i < $total_images; $i++):
                                            $img_src = base_url('assets/images/' . $all_images[$i]);
                                            $is_last_slot = ($i === 3);
                                            $show_overlay = ($is_last_slot && $total_images > 4);
                                            $remaining_count = $total_images - 4;
                                            $is_overflow_item = ($i >= 4);
                                        ?>
                                            <div class="product-grid-item <?= $is_overflow_item ? 'grid-overflow-item' : ''; ?>" onclick="openLightbox(<?= $i; ?>)" id="grid-item-<?= $i; ?>">
                                                <img src="<?= $img_src; ?>" alt="<?= html_escape($product['title']) . ' - ' . ($i + 1); ?>" id="grid-img-<?= $i; ?>" onerror="this.src='<?= base_url('assets/images/products/product-image.png'); ?>'">
                                                
                                                <!-- Desktop Floating Actions in Top-Right corner of 2nd image (Slot 1) -->
                                                <?php if ($i === 1 || ($total_images === 1 && $i === 0)): ?>
                                                    <div class="grid-floating-actions d-none d-md-flex" onclick="event.stopPropagation();">
                                                        <button type="button" class="grid-action-btn wishlist-btn <?= !empty($is_in_wishlist) ? 'active' : ''; ?>" id="btn-grid-wishlist" onclick="event.preventDefault(); event.stopPropagation(); toggleWishlist(<?= $product['id']; ?>, this)" title="Wishlist">
                                                            <i class="<?= !empty($is_in_wishlist) ? 'fa-solid fa-heart text-white' : 'fa-regular fa-heart'; ?>"></i>
                                                        </button>
                                                        <button type="button" class="grid-action-btn zoom-btn" onclick="openLightbox(<?= $i; ?>)" title="Fullscreen Gallery">
                                                            <i class="fa-solid fa-expand"></i>
                                                        </button>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Desktop +N Overlay on 4th slot if > 4 images -->
                                                <?php if ($show_overlay): ?>
                                                    <div class="product-grid-overlay d-none d-md-flex">
                                                        <span class="overlay-text">+<?= $remaining_count; ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>

                                <!-- Mobile Slide Indicator Bar underneath the image as in product_deatils_mobile.jpeg -->
                                <div class="mobile-slider-indicator-container d-md-none" id="mobile-indicator-wrap">
                                    <div class="mobile-slider-indicator-track">
                                        <div class="mobile-slider-indicator-bar" id="mobile-slide-indicator"></div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <!-- /LEFT COLUMN -->

                        <!-- RIGHT COLUMN: Product Info & Variants (varient_products.PNG) -->
                        <div class="col-md-5">
                            <div class="tf-product-info-wrap position-relative">
                                <!-- Variant Loader Overlay -->
                                <div id="product-variant-loader" class="product-variant-loader-overlay" style="display: none;">
                                    <div class="variant-loader-card">
                                        <div class="spinner-border text-primary mb-2" role="status" style="width: 2.2rem; height: 2.2rem; border-width: 3px;">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <div class="variant-loader-label">Updating product details...</div>
                                    </div>
                                </div>

                                <div class="tf-product-info-list">
                                    
                                    <!-- ALL PRODUCT VARIANTS (Color, Size / Storage / Option) - Top of Product Name (varient_products.PNG) -->
                                    <?php if (($has_color && !empty($color_map)) || ($has_size && !empty($multi_attr['values']))): ?>
                                        <div class="tf-product-info-variants mb-2">
                                            <!-- 1. COLOR / PRIMARY OPTION SELECTION WITH SLIDER & PRODUCT PHOTOS (color_badge.PNG) -->
                                            <?php if ($has_color && !empty($color_map)): ?>
                                                <div class="variant-picker-item mb-2">
                                                    <?php if ($is_color_type_attr): ?>
                                                        <div class="color-badge-heading mb-2">
                                                            <span class="color-badge-title">Selected <?= html_escape($color_attr_name); ?>:</span>
                                                            <span class="color-badge-name" id="selected-color-name"><?= ucwords(html_escape($initial_color)); ?></span>
                                                        </div>

                                                        <!-- Horizontal color slider (without next/prev buttons) -->
                                                        <div class="color-variant-container">
                                                            <div class="color-slider-track" id="color-slider-track">
                                                                <?php foreach ($color_map as $c_name => $c_info): 
                                                                    $is_active_color = ($c_name === $initial_color);
                                                                    $c_img = !empty($c_info['image']) ? $c_info['image'] : $product['main_image'];
                                                                ?>
                                                                    <div class="color-thumb-card <?= $is_active_color ? 'active' : ''; ?>" 
                                                                         data-color="<?= html_escape($c_name); ?>"
                                                                         data-image="<?= base_url('assets/images/' . $c_img); ?>"
                                                                         onclick="selectColor('<?= html_escape($c_name); ?>', this)"
                                                                         title="<?= html_escape($c_name); ?>">
                                                                        <img src="<?= base_url('assets/images/' . $c_img); ?>" alt="<?= html_escape($c_name); ?>" onerror="this.src='<?= base_url('assets/images/' . $product['main_image']); ?>'">
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                                            <div class="variant-picker-label">
                                                                <span class="text-dark fw-bold" style="font-size: 15px;">Select <?= html_escape($color_attr_name); ?></span>
                                                                <span class="text-secondary ms-1 small">(<strong id="selected-color-name" class="text-dark"><?= html_escape($initial_color); ?></strong>)</span>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-wrap gap-2" id="primary-option-chips-wrapper">
                                                            <?php foreach ($color_map as $c_name => $c_info): 
                                                                $is_active_color = ($c_name === $initial_color);
                                                            ?>
                                                                <button type="button" 
                                                                        class="variant-size-chip color-thumb-card-chip <?= $is_active_color ? 'active' : ''; ?>" 
                                                                        data-color="<?= html_escape($c_name); ?>"
                                                                        onclick="selectColor('<?= html_escape($c_name); ?>', this)"
                                                                        title="<?= html_escape($color_attr_name . ' ' . $c_name); ?>">
                                                                    <?= html_escape($c_name); ?>
                                                                </button>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>

                                            <!-- 2. SIZE / STORAGE / OPTION SELECTION WITH OUT-OF-STOCK STYLING -->
                                            <?php if ($has_size && !empty($multi_attr['values'])): ?>
                                                <div class="variant-picker-item mb-2">
                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                        <div class="variant-picker-label">
                                                            <span class="text-dark fw-bold" style="font-size: 15px;">Select <?= html_escape($multi_attr_name); ?></span>
                                                            <span class="text-secondary ms-1 small" id="selected-size-name-wrap">(<strong id="selected-size-name" class="text-dark"><?= html_escape($initial_size); ?></strong>)</span>
                                                        </div>
                                                        <?php if (strcasecmp($multi_attr_slug, 'size') === 0 || strcasecmp($multi_attr_name, 'size') === 0): ?>
                                                            <a href="#size-guide" data-bs-toggle="modal" class="text-decoration-none fw-semibold" style="color: #2874f0 !important; font-size: 14px;">
                                                                Size Chart <i class="fa-solid fa-chevron-right ms-1" style="font-size: 11px;"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="d-flex flex-wrap gap-2" id="size-chips-wrapper">
                                                        <?php 
                                                        foreach ($multi_attr['values'] as $s_val): 
                                                            $s_name = $s_val['value'];
                                                            $is_active_size = ($s_name === $initial_size);
                                                            
                                                            // Check stock for initial color or size variant map
                                                            $in_stock = true;
                                                            if (!empty($color_map[$initial_color]['sizes'][$s_name])) {
                                                                $in_stock = ($color_map[$initial_color]['sizes'][$s_name]['stock'] > 0);
                                                            } elseif (empty($initial_color) && isset($size_variant_map[$s_name])) {
                                                                $in_stock = ($size_variant_map[$s_name]['stock'] > 0);
                                                            }
                                                        ?>
                                                            <button type="button" 
                                                                    class="variant-size-chip <?= $is_active_size ? 'active' : ''; ?> <?= !$in_stock ? 'is-out-of-stock' : ''; ?>" 
                                                                    data-size="<?= html_escape($s_name); ?>"
                                                                    onclick="selectSize('<?= html_escape($s_name); ?>', this)"
                                                                    title="<?= !$in_stock ? 'Out of stock' : html_escape($multi_attr_name) . ' ' . html_escape($s_name); ?>">
                                                                <?= html_escape($s_name); ?>
                                                            </button>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Heading / Brand / Rating -->
                                    <div class="tf-product-info-heading">
                                        <div class="tf-product-info-name">
                                            
                                            <!-- Brand Store Link (as in varient_products.PNG) -->
                                            <?php if (!empty($product['brand_name']) || !empty($product['category_name'])): ?>
                                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                                    <?php if (!empty($product['brand_name'])): ?>
                                                        <a href="<?= site_url('shop?brand=' . ($product['brand_slug'] ?? '')); ?>" class="brand-store-link fw-semibold">
                                                            Visit the <?= html_escape($product['brand_name']); ?> Store
                                                        </a>
                                                        <?php if (!empty($product['category_name'])): ?>
                                                            <span class="text-secondary" style="font-size: 11px;">&bull;</span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    <?php if (!empty($product['category_name'])): ?>
                                                        <a href="<?= site_url('shop/' . ($product['category_slug'] ?? '')); ?>" class="text-secondary text-decoration-none small"><?= html_escape($product['category_name']); ?></a>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>

                                            <h3 class="name fw-bold mb-2" id="display-product-title" style="font-size: 26px !important; line-height: 1.35;"><?= html_escape(!empty($initial_variant['title']) ? $initial_variant['title'] : $product['title']); ?></h3>

                                            <!-- Rating & Review Count (Completely Dynamic - No Static Ratings) -->
                                            <div class="sub d-flex align-items-center gap-2 mt-1">
                                                <?php if ($total_review_count > 0): ?>
                                                    <div class="tf-product-info-rate d-flex align-items-center gap-1">
                                                        <span class="fw-bold me-1"><?= number_format($computed_avg, 1); ?></span>
                                                        <div class="list-star text-warning">
                                                            <?php
                                                            $r_score = round($computed_avg * 2) / 2;
                                                            for ($s = 1; $s <= 5; $s++):
                                                                if ($s <= $r_score): ?>
                                                                    <i class="fa-solid fa-star"></i>
                                                                <?php elseif ($s - 0.5 == $r_score): ?>
                                                                    <i class="fa-solid fa-star-half-stroke"></i>
                                                                <?php else: ?>
                                                                    <i class="fa-regular fa-star"></i>
                                                                <?php endif;
                                                            endfor;
                                                            ?>
                                                        </div>
                                                        <div class="text text-caption-1 text-secondary ms-1">(<?= number_format($total_review_count); ?> <?= ($total_review_count == 1) ? 'rating' : 'ratings'; ?>)</div>
                                                    </div>
                                                    <span class="text-secondary">|</span>
                                                    <a href="#tab-customer-reviews" class="text-secondary small text-decoration-none" onclick="activateReviewTab()">See reviews</a>
                                                <?php else: ?>
                                                    <div class="tf-product-info-rate d-flex align-items-center gap-1">
                                                        <div class="list-star text-muted" style="font-size: 13px;">
                                                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                                                <i class="fa-regular fa-star text-secondary" style="opacity: 0.45;"></i>
                                                            <?php endfor; ?>
                                                        </div>
                                                        <div class="text text-caption-1 text-secondary ms-1">(0)</div>
                                                    </div>
                                                    <span class="text-secondary">|</span>
                                                    <a href="#tab-customer-reviews" class="text-secondary small text-decoration-none" onclick="activateReviewTab()">See reviews</a>
                                                <?php endif; ?>
                                            </div>

                                        </div>

                                        <!-- Pricing Block (as in varient_products.PNG) -->
                                        <div class="tf-product-info-desc mt-2">
                                            <div class="product-price-block">
                                                <div class="product-discount-rate" id="display-discount-rate" style="<?= ($discount_percent > 0) ? 'display: inline-flex;' : 'display: none;'; ?>">
                                                    <i class="fa-solid fa-arrow-down-long me-1"></i><?= $discount_percent; ?>%
                                                </div>

                                                <span class="product-current-price" id="display-sale-price">
                                                    <?= $currency_symbol . number_format(round($current_price)); ?>
                                                </span>

                                                <span class="product-mrp-price" id="display-mrp-price" style="<?= ($sale_price && $sale_price < $regular_price) ? 'display: inline;' : 'display: none;'; ?>">
                                                    M.R.P.: <?= $currency_symbol . number_format(round($regular_price)); ?>
                                                </span>
                                            </div>
                                            
                                             <?php if (!empty($store_settings['tax_inclusive'])): ?>
                                                 <div class="product-tax-note">Inclusive of all taxes</div>
                                             <?php elseif (!empty($store_settings['tax_enabled'])): ?>
                                                 <div class="product-tax-note text-secondary" style="font-size: 12px;">Exclusive of taxes (calculated at checkout)</div>
                                             <?php endif; ?>

                                            <?php if (!empty($product['short_description'])): ?>
                                                <p class="text-secondary mb-2"><?= nl2br(html_escape($product['short_description'])); ?></p>
                                            <?php endif; ?>


                                        </div>
                                    </div>

                                    <!-- Product Options & Specifications Accordions (varient_products.PNG) -->
                                    <div class="tf-product-info-choose-option mt-2">

                                        <!-- PRODUCT HIGHLIGHTS & SPECIFICATIONS ACCORDIONS (specification.png) -->
                                        <?php
                                        // 1. Build Specifications (Default specs: Size, Color, Brand, Category + custom specs)
                                        $merged_specs = [];
                                        $seen_spec_keys = [];

                                        // Brand
                                        $spec_brand = !empty($product['brand_name']) ? trim($product['brand_name']) : '';
                                        if (!empty($spec_brand)) {
                                            $merged_specs[] = ['name' => 'Brand', 'value' => $spec_brand];
                                            $seen_spec_keys['brand'] = true;
                                        }

                                        // Category
                                        $spec_cat = !empty($product['category_name']) ? trim($product['category_name']) : '';
                                        if (!empty($spec_cat)) {
                                            $merged_specs[] = ['name' => 'Category', 'value' => $spec_cat];
                                            $seen_spec_keys['category'] = true;
                                        }

                                        // Size / Storage / Multi Attribute
                                        $spec_multi_vals = [];
                                        if (!empty($initial_size)) {
                                            $spec_multi_vals[] = $initial_size;
                                        } elseif (!empty($multi_attr['values'])) {
                                            foreach ($multi_attr['values'] as $sv) {
                                                if (!empty($sv['value'])) $spec_multi_vals[] = $sv['value'];
                                            }
                                        }
                                        if (!empty($spec_multi_vals)) {
                                            $merged_specs[] = ['name' => $multi_attr_name, 'value' => implode(', ', array_unique($spec_multi_vals))];
                                            $seen_spec_keys[strtolower($multi_attr_name)] = true;
                                            $seen_spec_keys['size'] = true;
                                        }

                                        // Color / Primary Attribute
                                        $spec_colors = [];
                                        if (!empty($initial_color)) {
                                            $spec_colors[] = $initial_color;
                                        } elseif (!empty($color_map)) {
                                            $spec_colors = array_keys($color_map);
                                        } elseif (!empty($product['attributes']['color']['values'])) {
                                            foreach ($product['attributes']['color']['values'] as $cv) {
                                                if (!empty($cv['value'])) $spec_colors[] = $cv['value'];
                                            }
                                        }
                                        if (!empty($spec_colors)) {
                                            $merged_specs[] = ['name' => $color_attr_name, 'value' => implode(', ', array_map('ucfirst', array_unique($spec_colors)))];
                                            $seen_spec_keys[strtolower($color_attr_name)] = true;
                                            $seen_spec_keys['color'] = true;
                                        }

                                        // Variant custom specifications first (so variant specs override matching base product specs)
                                        if (!empty($initial_variant['specifications'])) {
                                            $v_specs = is_array($initial_variant['specifications']) ? $initial_variant['specifications'] : (json_decode($initial_variant['specifications'], true) ?: []);
                                            foreach ($v_specs as $vsp) {
                                                $vn = trim($vsp['name'] ?? $vsp['spec_name'] ?? $vsp['spec_key'] ?? '');
                                                $vv = trim($vsp['value'] ?? $vsp['spec_value'] ?? '');
                                                $vkey = strtolower($vn);
                                                if ($vn !== '' && $vv !== '' && !isset($seen_spec_keys[$vkey])) {
                                                    $seen_spec_keys[$vkey] = true;
                                                    $merged_specs[] = ['name' => $vn, 'value' => $vv];
                                                }
                                            }
                                        }

                                        // Custom specifications from base product database
                                        if (!empty($product['specifications']) && is_array($product['specifications'])) {
                                            foreach ($product['specifications'] as $csp) {
                                                $cn = trim($csp['spec_name'] ?? '');
                                                $cv = trim($csp['spec_value'] ?? '');
                                                $ckey = strtolower($cn);
                                                if ($cn !== '' && $cv !== '' && !isset($seen_spec_keys[$ckey])) {
                                                    $seen_spec_keys[$ckey] = true;
                                                    $merged_specs[] = ['name' => $cn, 'value' => $cv];
                                                }
                                            }
                                        }

                                        // Highlights
                                        $base_product_highlights = !empty($product['highlights_decoded']) ? $product['highlights_decoded'] : [];
                                        if (empty($base_product_highlights) && !empty($product['highlights'])) {
                                            $base_product_highlights = is_array($product['highlights']) ? $product['highlights'] : (json_decode($product['highlights'], true) ?: []);
                                        }
                                        $product_highlights = $base_product_highlights;
                                        if (!empty($initial_variant['highlights'])) {
                                            $v_hl = is_array($initial_variant['highlights']) ? $initial_variant['highlights'] : (json_decode($initial_variant['highlights'], true) ?: []);
                                            if (!empty($v_hl)) {
                                                $product_highlights = $v_hl;
                                            }
                                        }

                                        // Helper to check if spec is higher than half column (long text length)
                                        if (!function_exists('is_spec_full_width_check')) {
                                            function is_spec_full_width_check($name, $val) {
                                                $v_len = mb_strlen(trim($val ?? ''));
                                                $n_len = mb_strlen(trim($name ?? ''));
                                                return ($v_len > 35 || $n_len > 28 || ($v_len + $n_len) > 45);
                                            }
                                        }
                                        ?>

                                        <div class="product-highlights-specifications my-2">
                                            
                                            <!-- Product Highlights (Always Open by Default) -->
                                            <div class="product-highlights-block mb-2" id="product-highlights-block" style="<?= empty($product_highlights) ? 'display: none;' : ''; ?>">
                                                <div class="d-flex justify-content-between align-items-center cursor-pointer py-1 user-select-none" 
                                                     onclick="toggleProductHighlights()" 
                                                     style="cursor: pointer;">
                                                    <h4 class="m-0" style="font-size: 22px; font-weight: 700; color: #212121; letter-spacing: -0.2px;">Product highlights</h4>
                                                    <button type="button" class="btn-collapse-arrow d-flex align-items-center justify-content-center" 
                                                            style="width: 32px; height: 32px; background: #f1f3f6; border-radius: 8px; border: none; padding: 0; color: #212121;" 
                                                            aria-label="Toggle Product Highlights">
                                                        <i class="fa-solid fa-chevron-up" id="highlights-arrow-icon" style="font-size: 11px;"></i>
                                                    </button>
                                                </div>
                                                
                                                <div id="product-highlights-collapse" class="pt-2" style="display: block;">
                                                    <div class="specs-grid-layout" id="highlights-grid-container" style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 20px; row-gap: 8px;">
                                                        <?php foreach ($product_highlights as $hl): 
                                                            $hl_k = trim($hl['key'] ?? $hl['name'] ?? '');
                                                            $hl_v = trim($hl['value'] ?? '');
                                                            if ($hl_k === '' && $hl_v === '') continue;
                                                            $hl_is_full = is_spec_full_width_check($hl_k, $hl_v);
                                                        ?>
                                                            <div class="spec-grid-item <?= $hl_is_full ? 'spec-full-width' : ''; ?>" 
                                                                 style="border-bottom: 1px solid #f0f0f0; padding-bottom: 6px; <?= $hl_is_full ? 'grid-column: 1 / -1;' : ''; ?>">
                                                                <div class="spec-item-key" style="font-size: 13px; color: #717478; margin-bottom: 2px; font-weight: 400;"><?= html_escape($hl_k); ?></div>
                                                                <div class="spec-item-val" style="font-size: 14px; color: #212121; font-weight: 500; line-height: 1.3;"><?= html_escape($hl_v); ?></div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                                
                                                <div class="section-divider" style="border-bottom: 1px solid #f0f0f0; margin-top: 10px; margin-bottom: 10px;"></div>
                                            </div>

                                            <!-- Product Specifications (Collapsed by Default, 14 items limit) -->
                                            <div class="product-specifications-block mb-2" id="product-specifications-block" style="<?= empty($merged_specs) ? 'display: none;' : ''; ?>">
                                                <div class="d-flex justify-content-between align-items-center cursor-pointer py-1 user-select-none" 
                                                     onclick="toggleProductSpecifications()" 
                                                     style="cursor: pointer;">
                                                    <h4 class="m-0" style="font-size: 22px; font-weight: 700; color: #212121; letter-spacing: -0.2px;">Specifications</h4>
                                                    <button type="button" class="btn-collapse-arrow d-flex align-items-center justify-content-center" 
                                                            style="width: 32px; height: 32px; background: #f1f3f6; border-radius: 8px; border: none; padding: 0; color: #212121;" 
                                                            aria-label="Toggle Specifications">
                                                        <i class="fa-solid fa-chevron-down" id="specs-arrow-icon" style="font-size: 11px;"></i>
                                                    </button>
                                                </div>
                                                
                                                <div id="product-specs-collapse" class="pt-2" style="display: none;">
                                                    <div class="specs-grid-layout" id="specs-grid-container" style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 20px; row-gap: 8px;">
                                                        <?php 
                                                        $s_idx = 0;
                                                        foreach ($merged_specs as $sp): 
                                                            $s_idx++;
                                                            $is_extra = ($s_idx > 14);
                                                            $is_full_width = is_spec_full_width_check($sp['name'], $sp['value']);
                                                        ?>
                                                            <div class="spec-grid-item <?= $is_extra ? 'spec-overflow-item' : ''; ?> <?= $is_full_width ? 'spec-full-width' : ''; ?>" 
                                                                 style="border-bottom: 1px solid #f0f0f0; padding-bottom: 6px; <?= $is_full_width ? 'grid-column: 1 / -1;' : ''; ?> <?= $is_extra ? 'display: none;' : ''; ?>">
                                                                <div class="spec-item-key" style="font-size: 13px; color: #717478; margin-bottom: 2px; font-weight: 400;"><?= html_escape($sp['name']); ?></div>
                                                                <div class="spec-item-val" style="font-size: 14px; color: #212121; font-weight: 500; line-height: 1.3;"><?= html_escape($sp['value']); ?></div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>

                                                    <div class="text-center mt-2 pt-2" id="specs-see-more-wrap" style="<?= (count($merged_specs) > 14) ? 'display: block;' : 'display: none;'; ?>">
                                                        <button type="button" 
                                                                class="btn btn-sm px-4 py-1 fw-semibold d-inline-flex align-items-center gap-1" 
                                                                id="btn-see-more-specs" 
                                                                onclick="toggleSeeMoreSpecs(event)" 
                                                                style="font-size: 13px; border-radius: 20px; border: 1px solid #d1d5db; color: #374151; background: #ffffff;">
                                                            <span id="btn-see-more-text">See More</span>
                                                            <i class="fa-solid fa-chevron-down ms-1" id="btn-see-more-icon" style="font-size: 10px;"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="section-divider" style="border-bottom: 1px solid #f0f0f0; margin-top: 10px; margin-bottom: 10px;"></div>
                                            </div>

                                        </div>

                                        <!-- Extra Links & Delivery Info (from product-detail.html) -->
                                        <div class="tf-product-info-help mt-2">
                                            <div class="tf-product-info-extra-link d-flex justify-content-between">
                                                <a href="#delivery_return" data-bs-toggle="modal" class="tf-product-extra-icon d-flex align-items-center gap-2 text-decoration-none">
                                                    <div class="icon"><i class="icon-shipping"></i></div>
                                                    <p class="text-caption-1 mb-0">Delivery & Return</p>
                                                </a>
                                                <a href="#ask_question" data-bs-toggle="modal" class="tf-product-extra-icon d-flex align-items-center gap-2 text-decoration-none">
                                                    <div class="icon"><i class="icon-question"></i></div>
                                                    <p class="text-caption-1 mb-0">Ask A Question</p>
                                                </a>
                                                <a href="#share_social" data-bs-toggle="modal" class="tf-product-extra-icon d-flex align-items-center gap-2 text-decoration-none">
                                                    <div class="icon"><i class="icon-share"></i></div>
                                                    <p class="text-caption-1 mb-0">Share</p>
                                                </a>
                                            </div>

                                            <div class="tf-product-info-time d-flex align-items-center gap-2 mt-2">
                                                <div class="icon"><i class="icon-timer"></i></div>
                                                <p class="text-caption-1 mb-0">Estimated Delivery:&nbsp;&nbsp;<span>3-6 business days</span></p>
                                            </div>
                                            <div class="tf-product-info-return d-flex align-items-center gap-2 mt-1">
                                                <div class="icon"><i class="icon-arrowClockwise"></i></div>
                                                <p class="text-caption-1 mb-0">Return within <span>30 days</span> of purchase. Hassle-free refunds.</p>
                                            </div>
                                        </div>

                                        <!-- SKU / Metadata -->
                                        <ul class="tf-product-info-sku list-unstyled border-top pt-2 mt-2 mb-0">
                                            <li class="d-flex gap-2 py-1">
                                                <p class="text-caption-1 mb-0 text-secondary">SKU:</p>
                                                <p class="text-caption-1 mb-0 fw-semibold" id="display-sku"><?= html_escape($initial_sku); ?></p>
                                            </li>
                                            <?php if (!empty($product['brand_name'])): ?>
                                                <li class="d-flex gap-2 py-1">
                                                    <p class="text-caption-1 mb-0 text-secondary">Brand:</p>
                                                    <p class="text-caption-1 mb-0 fw-semibold"><?= html_escape($product['brand_name']); ?></p>
                                                </li>
                                            <?php endif; ?>
                                            <li class="d-flex gap-2 py-1">
                                                <p class="text-caption-1 mb-0 text-secondary">Availability:</p>
                                                <?php
                                                $init_stock_qty = isset($initial_variant['stock']) ? (int)$initial_variant['stock'] : (isset($product['stock_quantity']) ? (int)$product['stock_quantity'] : 15);
                                                $init_in_stock  = ($init_stock_qty > 0);
                                                ?>
                                                <p class="text-caption-1 mb-0 fw-bold <?= $init_in_stock ? 'text-success' : 'text-danger'; ?>" id="display-stock-status">
                                                    <?= $init_in_stock ? ('In Stock (' . $init_stock_qty . ' available)') : 'Out of Stock'; ?>
                                                </p>
                                            </li>
                                            <li class="d-flex gap-2 py-1">
                                                <p class="text-caption-1 mb-0 text-secondary">Cash on Delivery:</p>
                                                <?php if (isset($product['is_cod_allowed']) && (int)$product['is_cod_allowed'] === 0): ?>
                                                    <p class="text-caption-1 mb-0 fw-semibold text-danger"><i class="fa-solid fa-ban me-1"></i>Not Available</p>
                                                <?php else: ?>
                                                    <p class="text-caption-1 mb-0 fw-semibold text-success"><i class="fa-solid fa-circle-check me-1"></i>Available</p>
                                                <?php endif; ?>
                                            </li>
                                            <li class="d-flex gap-2 py-1">
                                                <p class="text-caption-1 mb-0 text-secondary">Category:</p>
                                                <p class="text-caption-1 mb-0"><a href="<?= site_url('shop/' . ($product['category_slug'] ?? '')); ?>" class="text-primary text-decoration-none fw-semibold"><?= html_escape($product['category_name'] ?? 'Shop'); ?></a></p>
                                            </li>
                                        </ul>

                                        <!-- QUANTITY (Hidden input default = 1 for cart / checkout) -->
                                        <input type="hidden" id="product-qty-input" name="number" value="1">

                                        <!-- DESKTOP ACTION BUTTONS (Side by Side) -->
                                        <div class="product-action-buttons-wrap d-none d-md-flex gap-2 gap-sm-3 align-items-center mt-3 pt-1">
                                            <button type="button" class="btn-style-2 flex-grow-1 text-btn-uppercase fw-bold btn-add-to-cart py-3" id="main-btn-atc" onclick="addToCartAjax(event)" style="width: 50%; flex: 1 1 0; min-width: 0;">
                                                <span>Add to cart</span>
                                            </button>

                                            <button type="button" class="btn-style-3 flex-grow-1 text-btn-uppercase fw-bold py-3" id="btn-buy-now" onclick="buyNow()" style="width: 50%; flex: 1 1 0; min-width: 0;">
                                                Buy now
                                            </button>
                                        </div>

                                        <!-- MOBILE ATTACHED ACTION BUTTONS (Exact same buttons, same style, and same text) -->
                                        <div class="mobile-bottom-action-bar-attached d-md-none" id="in-page-action-buttons">
                                            <button type="button" class="btn-mobile-action btn-mobile-atc" onclick="addToCartAjax(event)">
                                                Add to cart
                                            </button>
                                            <button type="button" class="btn-mobile-action btn-mobile-buy" onclick="buyNow()">
                                                Buy now
                                            </button>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- /RIGHT COLUMN -->

                    </div>
                </div>
            </div>

        </section>
        <!-- /Product_Main -->

        <!-- Mobile Sticky Bottom Action Bar (as in product_deatils_mobile.jpeg) -->
        <div class="mobile-bottom-action-bar d-md-none" id="mobile-bottom-action-bar">
            <button type="button" class="btn-mobile-action btn-mobile-atc" onclick="addToCartAjax(event)">
                Add to cart
            </button>
            <button type="button" class="btn-mobile-action btn-mobile-buy" onclick="buyNow()">
                Buy now
            </button>
        </div>

        <!-- Product_Description_Tabs -->
        <section class="mt-5" id="tab-customer-reviews">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="widget-tabs style-1">
                            <ul class="widget-menu-tab">
                                <li class="item-title active" id="nav-description-tab">
                                    <span class="inner">Description</span>
                                </li>
                                <li class="item-title" id="nav-addInfo-tab">
                                    <span class="inner">Additional information</span>
                                </li>
                                <li class="item-title" id="nav-review-tab">
                                    <span class="inner">Customer Reviews (<?= (int)$total_review_count; ?>)</span>
                                </li>
                            </ul>
                            <div class="widget-content-tab">
                                <!-- Tab 1: Description (Same as maanamitaelectronics.com Description tab) -->
                                <div class="widget-content-inner active">
                                    <div class="product-details-tab-item">
                                        <div class="product-details-desc-wrap">
                                            <?php
                                            $raw_full_desc = trim((string)($product['description'] ?? ''));
                                            $plain_full_desc = trim(strip_tags(str_replace('&nbsp;', ' ', $raw_full_desc)));
                                            $has_full_desc = ($plain_full_desc !== '');
                                            ?>
                                            <?php if ($has_full_desc): ?>
                                                <div class="product-full-description-content" style="font-size: 15px; line-height: 1.75; color: #334155;">
                                                    <?php if (preg_match('/<[a-z][\s\S]*>/i', $raw_full_desc)): ?>
                                                        <?= $raw_full_desc; ?>
                                                    <?php else: ?>
                                                        <p class="mb-0"><?= nl2br(html_escape($raw_full_desc)); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <h3 class="product-details-desc-title fw-bold mb-3" id="tab-desc-product-title" style="font-size: 20px;"><?= html_escape(!empty($initial_variant['title']) ? $initial_variant['title'] : ($product['title'] ?? $product['name'] ?? 'Product Overview')); ?></h3>
                                                <?php if (!empty($product['short_description'])): ?>
                                                    <p class="mb-4 text-secondary"><?= nl2br(html_escape($product['short_description'])); ?></p>
                                                <?php endif; ?>
                                                <?php
                                                $fallback_specs = [];
                                                if (!empty($merged_specs) && is_array($merged_specs)) {
                                                    $fallback_specs = array_slice($merged_specs, 0, 8);
                                                } elseif (!empty($product_highlights) && is_array($product_highlights)) {
                                                    foreach (array_slice($product_highlights, 0, 8) as $hl) {
                                                        $fallback_specs[] = [
                                                            'name' => $hl['key'] ?? $hl['name'] ?? '',
                                                            'value' => $hl['value'] ?? ''
                                                        ];
                                                    }
                                                }
                                                if (empty($fallback_specs)) {
                                                    if (!empty($product['brand_name'])) {
                                                        $fallback_specs[] = ['name' => 'Brand', 'value' => $product['brand_name']];
                                                    }
                                                    if (!empty($product['category_name'])) {
                                                        $fallback_specs[] = ['name' => 'Category', 'value' => $product['category_name']];
                                                    }
                                                    if (!empty($initial_sku)) {
                                                        $fallback_specs[] = ['name' => 'SKU', 'value' => $initial_sku];
                                                    }
                                                }
                                                ?>
                                                <?php if (!empty($fallback_specs)): ?>
                                                    <div class="product-details-desc-list mt-3">
                                                        <h4 class="product-details-desc-list-title fw-semibold mb-2" style="font-size: 16px;">Key Specifications</h4>
                                                        <ul class="list-text type-disc mb-0">
                                                            <?php foreach ($fallback_specs as $fspec): ?>
                                                                <?php if (trim((string)($fspec['name'] ?? '')) !== '' || trim((string)($fspec['value'] ?? '')) !== ''): ?>
                                                                    <li><strong><?= html_escape($fspec['name'] ?? ''); ?>:</strong> <?= html_escape($fspec['value'] ?? ''); ?></li>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab 2: Additional information -->
                                <div class="widget-content-inner">
                                    <div class="product-details-tab-item">
                                        <div class="product-details-additional-info" id="tab-specs-wrapper">
                                            <div class="row">
                                                <div class="col-xl-12">
                                                    <h4 class="product-details-additional-info-title fw-bold mb-3" style="font-size: 18px;">Additional information</h4>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-striped mb-0">
                                                            <tbody id="tab-specs-tbody">
                                                                <?php if (!empty($merged_specs)): ?>
                                                                    <?php foreach ($merged_specs as $spec): ?>
                                                                        <tr>
                                                                            <td class="fw-semibold text-secondary" style="width: 35%;"><?= html_escape($spec['name']); ?></td>
                                                                            <td class="text-dark"><?= html_escape($spec['value']); ?></td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <tr>
                                                                        <td class="fw-semibold text-secondary" style="width: 35%;">Brand</td>
                                                                        <td class="text-dark"><?= html_escape($product['brand_name'] ?? 'Official Brand'); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="fw-semibold text-secondary" style="width: 35%;">Category</td>
                                                                        <td class="text-dark"><?= html_escape($product['category_name'] ?? 'Fashion'); ?></td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab 3: Customer Reviews -->
                                <div class="widget-content-inner">
                                    <div class="tab-reviews write-cancel-review-wrap">
                                        <div class="tab-reviews-heading">
                                            <div class="top">
                                                <div class="text-center">
                                                    <div class="number title-display"><?= number_format($computed_avg, 1); ?></div>
                                                    <div class="list-star text-warning">
                                                        <?php
                                                        $avg_round = round($computed_avg * 2) / 2;
                                                        for ($s = 1; $s <= 5; $s++):
                                                            if ($computed_avg > 0 && $s <= $avg_round): ?>
                                                                <i class="icon icon-star"></i>
                                                            <?php elseif ($computed_avg > 0 && $s - 0.5 == $avg_round): ?>
                                                                <i class="icon icon-star"></i>
                                                            <?php else: ?>
                                                                <i class="icon icon-star" style="opacity: 0.25;"></i>
                                                            <?php endif;
                                                        endfor; ?>
                                                    </div>
                                                    <p>(<?= number_format($total_review_count); ?> <?= ($total_review_count == 1) ? 'Rating' : 'Ratings'; ?>)</p>
                                                </div>
                                                <div class="rating-score">
                                                    <?php for ($star = 5; $star >= 1; $star--): 
                                                        $s_cnt = $rating_counts[$star];
                                                        $s_pct = $total_review_count > 0 ? round(($s_cnt / $total_review_count) * 100, 1) : 0;
                                                    ?>
                                                        <div class="item">
                                                            <div class="number-1 text-caption-1"><?= $star; ?></div>
                                                            <i class="icon icon-star"></i>
                                                            <div class="line-bg">
                                                                <div style="width: <?= $s_pct; ?>%;"></div>
                                                            </div>
                                                            <div class="number-2 text-caption-1"><?= $s_cnt; ?></div>
                                                        </div>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="reply-comment style-1 cancel-review-wrap">
                                            <div class="mb_24">
                                                <h4 class=""><?= sprintf('%02d', $total_review_count); ?> <?= ($total_review_count == 1) ? 'Review' : 'Reviews'; ?></h4>
                                            </div>
                                            <div class="reply-comment-wrap">
                                                <?php if (!empty($product['reviews'])):
                                                    $latest_reviews = array_slice($product['reviews'], 0, 5);
                                                ?>
                                                    <?php foreach ($latest_reviews as $rev):
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
                                                        <div class="reply-comment-item">
                                                            <div class="d-flex justify-content-between align-items-start gap-2">
                                                                <div class="user d-flex align-items-center gap-2">
                                                                    <?php if ($has_r_avatar): ?>
                                                                        <img src="<?= $r_avatar_url; ?>" alt="<?= html_escape($rev['customer_name']); ?>" class="rounded-circle shadow-sm border" style="width: 30px; height: 30px; object-fit: cover; flex-shrink: 0;" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                                                        <div class="rounded-circle align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 30px; height: 30px; background: linear-gradient(135deg, #108910, #1b52b3); font-size: 13px; flex-shrink: 0; display: none;">
                                                                            <i class="fa-solid fa-user"></i>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 30px; height: 30px; background: linear-gradient(135deg, #108910, #1b52b3); font-size: 13px; flex-shrink: 0;">
                                                                            <i class="fa-solid fa-user"></i>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <div>
                                                                        <h6 class="mb-0">
                                                                            <a href="javascript:void(0);" class="link"><?= html_escape($rev['customer_name']); ?></a>
                                                                        </h6>
                                                                    </div>
                                                                </div>
                                                                <div class="day text-secondary-2 text-caption-1 ms-auto text-nowrap"><?= date('j M, Y', strtotime($rev['created_at'])); ?></div>
                                                            </div>
                                                            <!-- Rating stars and title -->
                                                            <div class="d-flex align-items-center gap-1 flex-wrap my-2">
                                                                <div class="list-star d-inline-flex align-items-center gap-1" style="color: #ff9f00; font-size: 13px;">
                                                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                                                        <i class="<?= ($rev['rating'] >= $s) ? 'fa-solid' : 'fa-regular'; ?> fa-star"></i>
                                                                    <?php endfor; ?>
                                                                    <span class="fw-bold text-dark ms-1" style="font-size: 13.5px;"><?= number_format((float)$rev['rating'], 1); ?></span>
                                                                </div>
                                                                <?php if (!empty($rev['title'])): ?>
                                                                    <span class="text-muted mx-1" style="font-size: 14px; line-height: 1;">&bull;</span>
                                                                    <strong class="text-dark small"><?= html_escape($rev['title']); ?></strong>
                                                                <?php endif; ?>
                                                            </div>
                                                            <p class="text-secondary"><?= nl2br(html_escape($rev['review'])); ?></p>
                                                            <?php 
                                                            if (!empty($rev['images'])):
                                                                $r_images = is_array($rev['images']) ? $rev['images'] : (json_decode($rev['images'], true) ?: []);
                                                                if (!empty($r_images)):
                                                            ?>
                                                                <div class="d-flex align-items-center gap-2 flex-wrap mt-2">
                                                                    <?php foreach ($r_images as $img_rel):
                                                                        $clean_rel = ltrim(trim((string)$img_rel), '/');
                                                                        if (strpos($clean_rel, 'assets/images/') === 0) {
                                                                            $clean_rel = substr($clean_rel, strlen('assets/images/'));
                                                                        }
                                                                        $img_full_url = base_url('assets/images/' . $clean_rel);
                                                                        $img_fallback = base_url('website/assets/images/' . $clean_rel);
                                                                    ?>
                                                                        <a href="<?= $img_full_url; ?>" target="_blank">
                                                                            <img src="<?= $img_full_url; ?>" class="border rounded" style="width: 120px; height: 120px; object-fit: cover;" onerror="this.onerror=null;this.src='<?= $img_fallback; ?>'">
                                                                        </a>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            <?php endif; endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                    <?php if (count($product['reviews']) > 5): ?>
                                                        <div class="mt-3 pt-2">
                                                            <a href="<?= site_url('product/reviews/' . $product['slug']); ?>" class="btn btn-outline-dark fw-semibold px-4 py-2 rounded-1" style="font-size: 14px;">
                                                                Show All Reviews (<?= number_format(count($product['reviews'])); ?>) <i class="fa-solid fa-arrow-right ms-1"></i>
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <div class="text-center py-4 text-secondary">
                                                        <p class="mb-0">No customer reviews yet for this product.</p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Product_Description_Tabs -->

        <!-- RELATED PRODUCTS & RECENTLY VIEWED TABS (Same Popular Products Slider Style as Home Page) -->
        <?php if (!empty($related_products) || !empty($recently_viewed_products)): ?>
            <section class="flat-spacing border-top aqf-seller-area fix">
                <div class="container flat-animate-tab">
                    <ul class="tab-product justify-content-sm-center nav nav-tabs border-0 mb-4" role="tablist">
                        <?php if (!empty($related_products)): ?>
                            <li class="nav-tab-item" role="presentation">
                                <a href="#relatedProductsTab" class="active fw-bold text-uppercase fs-5" data-bs-toggle="tab">Related Products</a>
                            </li>
                        <?php endif; ?>
                        <?php if (!empty($recently_viewed_products)): ?>
                            <li class="nav-tab-item" role="presentation">
                                <a href="#recentlyViewedTab" class="<?= empty($related_products) ? 'active' : ''; ?> fw-bold text-uppercase fs-5" data-bs-toggle="tab">Recently Viewed</a>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <div class="tab-content">
                        <?php if (!empty($related_products)): ?>
                            <div class="tab-pane active show" id="relatedProductsTab" role="tabpanel">
                                <div class="aq-product-slide-wrap p-relative">
                                    <div class="aq-product-arrow">
                                        <button type="button" class="aq-product-prev aq-related-prev" aria-label="Previous slide">
                                            <span>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12" fill="none">
                                                    <path d="M5.75 10.75L0.75 5.75L5.75 0.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                        </button>
                                        <button type="button" class="aq-product-next aq-related-next" aria-label="Next slide">
                                            <span>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12" fill="none">
                                                    <path d="M0.75 10.75L5.75 5.75L0.75 0.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                        </button>
                                    </div>
                                    <div class="swiper aq-product-active aq-related-slider">
                                        <div class="swiper-wrapper">
                                            <?php foreach ($related_products as $rp): ?>
                                                <div class="swiper-slide">
                                                    <?php $this->load->view('template1/home/_product_card', ['p' => $rp, 'currency_symbol' => $currency_symbol ?? '$']); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($recently_viewed_products)): ?>
                            <div class="tab-pane <?= empty($related_products) ? 'active show' : ''; ?>" id="recentlyViewedTab" role="tabpanel">
                                <div class="aq-product-slide-wrap p-relative">
                                    <div class="aq-product-arrow">
                                        <button type="button" class="aq-product-prev aq-recent-prev" aria-label="Previous slide">
                                            <span>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12" fill="none">
                                                    <path d="M5.75 10.75L0.75 5.75L5.75 0.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                        </button>
                                        <button type="button" class="aq-product-next aq-recent-next" aria-label="Next slide">
                                            <span>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12" fill="none">
                                                    <path d="M0.75 10.75L5.75 5.75L0.75 0.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                        </button>
                                    </div>
                                    <div class="swiper aq-product-active aq-recent-slider">
                                        <div class="swiper-wrapper">
                                            <?php foreach ($recently_viewed_products as $rvp): ?>
                                                <div class="swiper-slide">
                                                    <?php $this->load->view('template1/home/_product_card', ['p' => $rvp, 'currency_symbol' => $currency_symbol ?? '$']); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>


        <!-- ==========================================================================
             MODALS: Lightbox, Size Guide, Delivery & Return, Ask Question, Share
             ========================================================================== -->

        <!-- 1. FULL PHOTO GALLERY LIGHTBOX MODAL -->
        <div class="modal fade" id="product-gallery-lightbox" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0 text-white d-flex justify-content-between align-items-center px-4 pt-3">
                        <span class="text-secondary small" id="lightbox-counter">Image 1 of <?= $total_images; ?></span>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="lightbox-main-img-wrap">
                            <button type="button" class="lightbox-arrow prev" onclick="lightboxNav(-1)" aria-label="Previous">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <img id="lightbox-active-img" src="<?= base_url('assets/images/' . $all_images[0]); ?>" alt="Full Gallery Image">
                            <button type="button" class="lightbox-arrow next" onclick="lightboxNav(1)" aria-label="Next">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        </div>
                        <div class="lightbox-thumbs-strip" id="lightbox-thumbs-strip">
                            <?php foreach ($all_images as $l_idx => $l_img): ?>
                                <div class="lightbox-thumb-item <?= ($l_idx === 0) ? 'active' : ''; ?>" onclick="lightboxGoTo(<?= $l_idx; ?>)" id="lb-thumb-<?= $l_idx; ?>">
                                    <img src="<?= base_url('assets/images/' . $l_img); ?>" alt="Thumb <?= $l_idx + 1; ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. SIZE GUIDE MODAL (from product-detail.html) -->
        <div class="modal fade modal-size-guide" id="size-guide" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content p-4 border-0 shadow">
                    <div class="modal-header border-0 pb-2">
                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-ruler-combined me-2 text-primary"></i> Size Guide & Recommendation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Interactive Height / Weight Calculator -->
                        <div class="card p-3 bg-light border-0 mb-4 rounded-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label small fw-bold mb-0">Your Height:</label>
                                        <span class="fw-bold text-primary" id="calc-height-val">175 cm</span>
                                    </div>
                                    <input type="range" class="form-range" id="calc-height-range" min="140" max="210" value="175" oninput="updateSizeCalc()">
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label small fw-bold mb-0">Your Weight:</label>
                                        <span class="fw-bold text-primary" id="calc-weight-val">70 kg</span>
                                    </div>
                                    <input type="range" class="form-range" id="calc-weight-range" min="40" max="130" value="70" oninput="updateSizeCalc()">
                                </div>
                            </div>
                            <div class="alert alert-primary mb-0 mt-3 py-2 px-3 small d-flex align-items-center justify-content-between">
                                <span>Based on your measurements, your best fit is:</span>
                                <strong class="fs-5 text-primary" id="calc-recommended-size">L</strong>
                            </div>
                        </div>

                        <!-- Standard Size Measurements Table -->
                        <h6 class="fw-bold text-uppercase small text-secondary mb-2">Size Chart (Inches)</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover text-center align-middle small mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Size</th>
                                        <th>Chest</th>
                                        <th>Waist</th>
                                        <th>Length</th>
                                        <th>Shoulder</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td class="fw-bold">S</td><td>36 - 38</td><td>30 - 32</td><td>27.5</td><td>17.0</td></tr>
                                    <tr><td class="fw-bold">M</td><td>38 - 40</td><td>32 - 34</td><td>28.5</td><td>17.5</td></tr>
                                    <tr class="table-info"><td class="fw-bold">L</td><td>40 - 42</td><td>34 - 36</td><td>29.5</td><td>18.5</td></tr>
                                    <tr><td class="fw-bold">XL</td><td>42 - 44</td><td>36 - 38</td><td>30.5</td><td>19.5</td></tr>
                                    <tr><td class="fw-bold">XXL</td><td>44 - 46</td><td>38 - 40</td><td>31.5</td><td>20.5</td></tr>
                                    <tr><td class="fw-bold">3XL</td><td>46 - 48</td><td>40 - 42</td><td>32.0</td><td>21.5</td></tr>
                                    <tr><td class="fw-bold">4XL</td><td>48 - 50</td><td>42 - 44</td><td>32.5</td><td>22.5</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. DELIVERY & RETURN MODAL (from product-detail.html) -->
        <div class="modal fade" id="delivery_return" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content p-4 border-0 shadow">
                    <div class="modal-header border-0 pb-2">
                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-truck-ramp-box me-2 text-primary"></i> Shipping & Delivery</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <h6 class="fw-bold text-dark">Delivery Schedule</h6>
                            <p class="text-secondary small mb-1">Orders dispatched within 24 hours of placement.</p>
                            <p class="text-secondary small mb-0">Free standard shipping on all prepaid orders or orders above <?= $currency_symbol; ?>500.</p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold text-dark">Returns & Replacements</h6>
                            <p class="text-secondary small mb-0">Items in unworn condition with tags may be returned within 30 days for an instant exchange or 100% refund.</p>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark">Customer Support</h6>
                            <p class="text-secondary small mb-0">Need help? Reach out at <a href="mailto:<?= html_escape($store_settings['site_email'] ?? 'support@example.com'); ?>" class="text-primary"><?= html_escape($store_settings['site_email'] ?? 'support@example.com'); ?></a><?= !empty($store_settings['site_phone']) ? ' or call ' . html_escape($store_settings['site_phone']) : ''; ?>.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. ASK A QUESTION MODAL (from product-detail.html) -->
        <div class="modal fade" id="ask_question" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content p-4 border-0 shadow">
                    <div class="modal-header border-0 pb-2">
                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-circle-question me-2 text-primary"></i> Have a Question?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form onsubmit="event.preventDefault(); alert('Thank you! Your query has been submitted.'); bootstrap.Modal.getInstance(document.getElementById('ask_question')).hide();">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Name *</label>
                                <input type="text" class="form-control" required value="<?= $this->session->userdata('user_name') ?? ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Email *</label>
                                <input type="email" class="form-control" required value="<?= $this->session->userdata('user_email') ?? ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Phone Number</label>
                                <input type="tel" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Your Question *</label>
                                <textarea class="form-control" rows="3" required placeholder="Ask anything about sizing, fabric or delivery..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 fw-bold py-2">Send Question</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. SHARE MODAL (from product-detail.html) -->
        <div class="modal fade" id="share_social" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content p-4 border-0 shadow">
                    <div class="modal-header border-0 pb-2">
                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-share-nodes me-2 text-primary"></i> Share This Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex justify-content-center gap-3 mb-4">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(current_url()); ?>" target="_blank" class="btn btn-outline-primary rounded-circle" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;"><i class="fa-brands fa-facebook-f"></i></a>
                            <a href="https://twitter.com/intent/tweet?url=<?= urlencode(current_url()); ?>" target="_blank" class="btn btn-outline-dark rounded-circle" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;"><i class="fa-brands fa-x-twitter"></i></a>
                            <a href="https://api.whatsapp.com/send?text=<?= urlencode($product['title'] . ' ' . current_url()); ?>" target="_blank" class="btn btn-outline-success rounded-circle" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;"><i class="fa-brands fa-whatsapp"></i></a>
                            <a href="https://pinterest.com/pin/create/button/?url=<?= urlencode(current_url()); ?>" target="_blank" class="btn btn-outline-danger rounded-circle" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;"><i class="fa-brands fa-pinterest-p"></i></a>
                        </div>
                        <div class="input-group">
                            <input type="text" class="form-control" id="share-link-input" value="<?= current_url(); ?>" readonly>
                            <button class="btn btn-dark fw-bold" type="button" onclick="navigator.clipboard.writeText(document.getElementById('share-link-input').value); alert('Product link copied to clipboard!');">Copy Link</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


<!-- ==========================================================================
     JAVASCRIPT: State Management, Lightbox, Variants, Cart & Sticky Bar
     ========================================================================== -->
<script>
(function() {
    'use strict';

    // Global variables passed from PHP
    var productId        = <?= (int) $product['id']; ?>;
    var currencySymbol   = "<?= $currency_symbol; ?>";
    var defaultGalleryImages = <?= json_encode(array_map(function($img) { return base_url('assets/images/' . $img); }, $all_images)); ?>;
    var galleryImages    = defaultGalleryImages.slice();
    var totalGallery     = galleryImages.length;
    var colorMap         = <?= json_encode($color_map); ?>;
    var sizeVariantMap   = <?= json_encode($size_variant_map); ?>;
    var baseHighlights   = <?= json_encode($base_product_highlights); ?>;
    var baseSpecifications = <?= json_encode(!empty($product['specifications']) ? $product['specifications'] : []); ?>;
    var baseBrand        = <?= json_encode(!empty($product['brand_name']) ? $product['brand_name'] : ''); ?>;
    var baseCategory     = <?= json_encode(!empty($product['category_name']) ? $product['category_name'] : ''); ?>;
    var hasVariants      = <?= $has_variants ? 'true' : 'false'; ?>;
    var hasColor         = <?= $has_color ? 'true' : 'false'; ?>;
    var hasSize          = <?= $has_size ? 'true' : 'false'; ?>;
    var colorAttrName    = "<?= !empty($color_attr_name) ? addslashes($color_attr_name) : 'Color'; ?>";
    var isColorTypeAttr  = <?= !empty($is_color_type_attr) ? 'true' : 'false'; ?>;
    var multiAttrName    = "<?= !empty($multi_attr_name) ? addslashes($multi_attr_name) : 'Size'; ?>";
    var multiAttrSlug    = "<?= !empty($multi_attr_slug) ? addslashes($multi_attr_slug) : 'size'; ?>";
    var isProductInWishlist = <?= !empty($is_in_wishlist) ? 'true' : 'false'; ?>;

    // Active state
    var selectedColor    = "<?= addslashes($initial_color); ?>";
    var selectedSize     = "<?= addslashes($initial_size); ?>";
    var selectedVariantId = <?= (int) $initial_variant_id; ?>;
    var currentPrice     = <?= (float) $current_price; ?>;
    var regularPrice     = <?= (float) $regular_price; ?>;
    var activeLightboxIdx = 0;
    var loaderTimeout    = null;

    // Variant Loader Helpers
    function showVariantLoader() {
        var loader = document.getElementById('product-variant-loader');
        if (loader) {
            loader.style.display = 'flex';
            void loader.offsetHeight;
            loader.classList.add('active');
        }
    }

    function hideVariantLoader(delay) {
        if (loaderTimeout) clearTimeout(loaderTimeout);
        loaderTimeout = setTimeout(function() {
            var loader = document.getElementById('product-variant-loader');
            if (loader) {
                loader.classList.remove('active');
                setTimeout(function() {
                    if (!loader.classList.contains('active')) {
                        loader.style.display = 'none';
                    }
                }, 200);
            }
        }, (typeof delay === 'number' ? delay : 220));
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // 1. LIGHTBOX CONTROLLER
    window.openLightbox = function(index) {
        if (index < 0) index = 0;
        if (index >= totalGallery) index = totalGallery - 1;
        activeLightboxIdx = index;
        updateLightboxView();
        var lbModal = new bootstrap.Modal(document.getElementById('product-gallery-lightbox'));
        lbModal.show();
    };

    window.lightboxNav = function(delta) {
        if (totalGallery <= 0) return;
        activeLightboxIdx = (activeLightboxIdx + delta + totalGallery) % totalGallery;
        updateLightboxView();
    };

    window.lightboxGoTo = function(idx) {
        activeLightboxIdx = idx;
        updateLightboxView();
    };

    function updateLightboxView() {
        var imgEl = document.getElementById('lightbox-active-img');
        var counterEl = document.getElementById('lightbox-counter');
        if (imgEl && galleryImages[activeLightboxIdx]) {
            imgEl.src = galleryImages[activeLightboxIdx];
        }
        if (counterEl) {
            counterEl.textContent = 'Image ' + (activeLightboxIdx + 1) + ' of ' + totalGallery;
        }
        // Update thumbs
        for (var i = 0; i < totalGallery; i++) {
            var th = document.getElementById('lb-thumb-' + i);
            if (th) {
                if (i === activeLightboxIdx) {
                    th.classList.add('active');
                    th.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                } else {
                    th.classList.remove('active');
                }
            }
        }
    }

    // Dynamic Grid / Slider Rendering when switching color variants
    function renderProductGrid(imgs) {
        var gridEl = document.getElementById('product-grid-gallery');
        if (!gridEl) return;

        var count = imgs.length;
        var html = '';

        for (var i = 0; i < count; i++) {
            var imgSrc = imgs[i];
            var isLastSlot = (i === 3);
            var showOverlay = (isLastSlot && count > 4);
            var remainingCount = count - 4;
            var isOverflow = (i >= 4);

            html += '<div class="product-grid-item ' + (isOverflow ? 'grid-overflow-item' : '') + '" onclick="openLightbox(' + i + ')" id="grid-item-' + i + '">';
            html += '  <img src="' + imgSrc + '" alt="Product Image ' + (i + 1) + '" id="grid-img-' + i + '" onerror="this.src=\'<?= base_url("assets/images/products/product-image.png"); ?>\'">';

            if (i === 1 || (count === 1 && i === 0)) {
                html += '  <div class="grid-floating-actions d-none d-md-flex" onclick="event.stopPropagation();">';
                html += '    <button type="button" class="grid-action-btn wishlist-btn ' + (isProductInWishlist ? 'active' : '') + '" id="btn-grid-wishlist" onclick="event.preventDefault(); event.stopPropagation(); toggleWishlist(' + productId + ', this)" title="Wishlist">';
                html += '      <i class="' + (isProductInWishlist ? 'fa-solid fa-heart text-white' : 'fa-regular fa-heart') + '"></i>';
                html += '    </button>';
                html += '    <button type="button" class="grid-action-btn zoom-btn" onclick="openLightbox(' + i + ')" title="Fullscreen Gallery">';
                html += '      <i class="fa-solid fa-expand"></i>';
                html += '    </button>';
                html += '  </div>';
            }

            if (showOverlay) {
                html += '  <div class="product-grid-overlay d-none d-md-flex">';
                html += '    <span class="overlay-text">+' + remainingCount + '</span>';
                html += '  </div>';
            }

            html += '</div>';
        }

        gridEl.innerHTML = html;
        gridEl.scrollLeft = 0;
        updateMobileSliderIndicator();

        var stickyImg = document.getElementById('sticky-bar-img');
        if (stickyImg && imgs.length > 0) {
            stickyImg.src = imgs[0];
        }

        var noteEl = document.getElementById('product-grid-counter-note');
        if (noteEl) {
            var slots = Math.min(4, count);
            noteEl.innerHTML = '<i class="fa-regular fa-images me-1"></i> Showing ' + slots + ' of ' + count + ' photos. Click any photo to view full gallery.';
        }
    }

    // Dynamic Lightbox Thumbs Strip Rendering when switching color variants
    function renderLightboxStrip(imgs) {
        var stripEl = document.getElementById('lightbox-thumbs-strip');
        if (!stripEl) return;

        var html = '';
        imgs.forEach(function(imgSrc, idx) {
            html += '<div class="lightbox-thumb-item ' + (idx === 0 ? 'active' : '') + '" onclick="lightboxGoTo(' + idx + ')" id="lb-thumb-' + idx + '">';
            html += '  <img src="' + imgSrc + '" alt="Thumb ' + (idx + 1) + '">';
            html += '</div>';
        });
        stripEl.innerHTML = html;

        var mainImg = document.getElementById('lightbox-active-img');
        if (mainImg && imgs.length > 0) {
            mainImg.src = imgs[0];
        }
        var counterEl = document.getElementById('lightbox-counter');
        if (counterEl) {
            counterEl.textContent = 'Image 1 of ' + imgs.length;
        }
    }

    // Mobile Slider Indicator Sync (as in product_deatils_mobile.jpeg)
    window.updateMobileSliderIndicator = function() {
        var indicator = document.getElementById('mobile-slide-indicator');
        var gallery = document.getElementById('product-grid-gallery');
        if (!indicator || !gallery) return;

        var count = galleryImages.length;
        if (count <= 1) {
            indicator.style.width = '100%';
            indicator.style.transform = 'translateX(0%)';
            return;
        }

        var segWidth = 100 / count;
        indicator.style.width = segWidth + '%';

        var maxScroll = gallery.scrollWidth - gallery.clientWidth;
        if (maxScroll <= 0) {
            indicator.style.transform = 'translateX(0%)';
            return;
        }

        var progress = gallery.scrollLeft / maxScroll;
        var maxTranslate = (count - 1) * 100;
        indicator.style.transform = 'translateX(' + (progress * maxTranslate) + '%)';
    };

    // Mobile Native Share handler
    window.handleMobileShare = function() {
        if (navigator.share) {
            navigator.share({
                title: <?= json_encode($product['title']); ?>,
                url: window.location.href
            }).catch(function(err) {
                if (err.name !== 'AbortError') {
                    var m = bootstrap.Modal.getOrCreateInstance(document.getElementById('share_social'));
                    m.show();
                }
            });
        } else {
            var m = bootstrap.Modal.getOrCreateInstance(document.getElementById('share_social'));
            m.show();
        }
    };

    // Keyboard support for Lightbox
    document.addEventListener('keydown', function(e) {
        var lbEl = document.getElementById('product-gallery-lightbox');
        if (lbEl && lbEl.classList.contains('show')) {
            if (e.key === 'ArrowLeft') lightboxNav(-1);
            if (e.key === 'ArrowRight') lightboxNav(1);
        }
    });

    // 2. COLOR SLIDER HORIZONTAL SCROLL (color_badge.PNG)
    window.slideColorTrack = function(direction) {
        var track = document.getElementById('color-slider-track');
        if (track) {
            track.scrollBy({ left: direction * 160, behavior: 'smooth' });
            setTimeout(updateColorSliderArrows, 250);
        }
    };

    window.updateColorSliderArrows = function() {
        var track = document.getElementById('color-slider-track');
        var prevBtn = document.getElementById('color-arrow-prev');
        var nextBtn = document.getElementById('color-arrow-next');
        if (!track || !prevBtn || !nextBtn) return;

        var scrollLeft = track.scrollLeft;
        var maxScroll = track.scrollWidth - track.clientWidth;

        if (maxScroll <= 4) {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
        } else {
            prevBtn.style.display = scrollLeft > 6 ? 'flex' : 'none';
            nextBtn.style.display = scrollLeft < (maxScroll - 6) ? 'flex' : 'none';
        }
    };

    // Helper to determine if a specification text length exceeds half column and should span full width
    function isSpecFullWidth(name, value, containerWidth) {
        var valStr = String(value || '').trim();
        var nameStr = String(name || '').trim();
        if (valStr.length > 35 || nameStr.length > 28 || (valStr.length + nameStr.length) > 45) {
            return true;
        }
        try {
            var gridEl = document.getElementById('specs-grid-container') || document.getElementById('highlights-grid-container');
            var gridW = containerWidth || (gridEl ? gridEl.clientWidth : 0);
            if (gridW > 0) {
                var halfCol = (gridW - 20) / 2;
                var canvas = isSpecFullWidth.canvas || (isSpecFullWidth.canvas = document.createElement("canvas"));
                var ctx = canvas.getContext("2d");
                if (ctx) {
                    ctx.font = "500 14px sans-serif";
                    if (ctx.measureText(valStr).width > (halfCol - 15)) return true;
                    ctx.font = "400 13px sans-serif";
                    if (ctx.measureText(nameStr).width > (halfCol - 15)) return true;
                }
            }
        } catch(e) {}
        return false;
    }

    window.evaluateSpecsGridFullWidth = function() {
        var containers = document.querySelectorAll('#specs-grid-container, #highlights-grid-container');
        containers.forEach(function(grid) {
            if (!grid || grid.offsetParent === null) return;
            var gridW = grid.clientWidth;
            if (!gridW) return;
            var items = grid.querySelectorAll('.spec-grid-item');
            items.forEach(function(item) {
                var keyEl = item.querySelector('.spec-item-key');
                var valEl = item.querySelector('.spec-item-val');
                var keyText = keyEl ? keyEl.textContent.trim() : '';
                var valText = valEl ? valEl.textContent.trim() : '';
                if (isSpecFullWidth(keyText, valText, gridW)) {
                    item.classList.add('spec-full-width');
                    item.style.gridColumn = '1 / -1';
                } else {
                    item.classList.remove('spec-full-width');
                    item.style.gridColumn = '';
                }
            });
        });
    };

    // 3. UPDATE VARIANT HIGHLIGHTS & SPECIFICATIONS
    function updateVariantHighlights(variantData) {
        var container = document.getElementById('highlights-grid-container');
        var block = document.getElementById('product-highlights-block');
        if (!container || !block) return;

        var hlList = [];
        var vHl = variantData ? parseJsonSafe(variantData.highlights) : [];
        if (vHl && vHl.length > 0) {
            hlList = vHl;
        } else if (baseHighlights && baseHighlights.length > 0) {
            hlList = baseHighlights;
        }

        var validHl = [];
        hlList.forEach(function(item) {
            var k = (item.key || item.name || '').trim();
            var v = (item.value || '').trim();
            if (k !== '' || v !== '') {
                validHl.push({ key: k, value: v });
            }
        });

        if (validHl.length === 0) {
            block.style.display = 'none';
            return;
        }

        block.style.display = 'block';
        var html = '';
        var hlGridW = container.clientWidth || 0;
        validHl.forEach(function(item) {
            var isFullWidth = isSpecFullWidth(item.key, item.value, hlGridW);
            var fullWidthClass = isFullWidth ? ' spec-full-width' : '';
            var fullWidthStyle = isFullWidth ? 'grid-column: 1 / -1;' : '';
            html += '<div class="spec-grid-item' + fullWidthClass + '" style="border-bottom: 1px solid #f0f0f0; padding-bottom: 6px; ' + fullWidthStyle + '">' +
                    '  <div class="spec-item-key" style="font-size: 13px; color: #717478; margin-bottom: 2px; font-weight: 400;">' + escapeHtml(item.key) + '</div>' +
                    '  <div class="spec-item-val" style="font-size: 14px; color: #212121; font-weight: 500; line-height: 1.3;">' + escapeHtml(item.value) + '</div>' +
                    '</div>';
        });
        container.innerHTML = html;
    }

    function updateVariantSpecifications(variantData) {
        var specsGrid = document.getElementById('specs-grid-container');
        var specsBlock = document.getElementById('product-specifications-block');
        var seeMoreWrap = document.getElementById('specs-see-more-wrap');
        var tabSpecsTbody = document.getElementById('tab-specs-tbody');
        var tabSpecsWrap = document.getElementById('tab-specs-wrapper');

        var mergedSpecs = [];
        var seenKeys = {};

        // 1. Brand
        if (baseBrand) {
            mergedSpecs.push({ name: 'Brand', value: baseBrand });
            seenKeys['brand'] = true;
        }

        // 2. Category
        if (baseCategory) {
            mergedSpecs.push({ name: 'Category', value: baseCategory });
            seenKeys['category'] = true;
        }

        // 3. Size / Storage / Option
        var curSize = (variantData && variantData.size) ? variantData.size : selectedSize;
        if (curSize) {
            var optSpecLabel = multiAttrName || 'Option';
            mergedSpecs.push({ name: optSpecLabel, value: curSize });
            seenKeys[optSpecLabel.toLowerCase()] = true;
            seenKeys['size'] = true;
        }

        // 4. Color / Primary Attribute
        var curColor = (variantData && variantData.color) ? variantData.color : selectedColor;
        if (curColor) {
            var formattedColor = isColorTypeAttr ? (curColor.charAt(0).toUpperCase() + curColor.slice(1)) : curColor;
            var cLabel = colorAttrName || 'Color';
            mergedSpecs.push({ name: cLabel, value: formattedColor });
            seenKeys[cLabel.toLowerCase()] = true;
            seenKeys['color'] = true;
        }

        // 5. Custom specifications (Variant specs take priority over matching Base specs)
        var customSpecs = [];
        var vSpecs = variantData ? parseJsonSafe(variantData.specifications) : [];
        if (vSpecs && vSpecs.length > 0) {
            customSpecs = customSpecs.concat(vSpecs);
        }
        if (baseSpecifications && baseSpecifications.length > 0) {
            customSpecs = customSpecs.concat(baseSpecifications);
        }

        customSpecs.forEach(function(sp) {
            var n = (sp.name || sp.spec_name || sp.spec_key || '').trim();
            var v = (sp.value || sp.spec_value || '').trim();
            var k = n.toLowerCase();
            if (n !== '' && v !== '' && !seenKeys[k]) {
                seenKeys[k] = true;
                mergedSpecs.push({ name: n, value: v });
            }
        });

        if (mergedSpecs.length === 0) {
            if (specsBlock) specsBlock.style.display = 'none';
            if (tabSpecsWrap) tabSpecsWrap.style.display = 'none';
            return;
        }

        if (specsBlock) specsBlock.style.display = 'block';
        if (tabSpecsWrap) tabSpecsWrap.style.display = 'block';

        // Rebuild right column specifications accordion grid
        if (specsGrid) {
            var gridHtml = '';
            var count = mergedSpecs.length;
            var isExpanded = window.areSpecsExpanded || false;
            var specsGridW = specsGrid.clientWidth || 0;

            mergedSpecs.forEach(function(item, idx) {
                var isExtra = (idx >= 14);
                var displayStyle = (isExtra && !isExpanded) ? 'display: none;' : '';
                var isFullWidth = isSpecFullWidth(item.name, item.value, specsGridW);
                var fullWidthClass = isFullWidth ? ' spec-full-width' : '';
                var fullWidthStyle = isFullWidth ? 'grid-column: 1 / -1;' : '';

                gridHtml += '<div class="spec-grid-item ' + (isExtra ? 'spec-overflow-item' : '') + fullWidthClass + '" style="border-bottom: 1px solid #f0f0f0; padding-bottom: 6px; ' + fullWidthStyle + ' ' + displayStyle + '">' +
                            '  <div class="spec-item-key" style="font-size: 13px; color: #717478; margin-bottom: 2px; font-weight: 400;">' + escapeHtml(item.name) + '</div>' +
                            '  <div class="spec-item-val" style="font-size: 14px; color: #212121; font-weight: 500; line-height: 1.3;">' + escapeHtml(item.value) + '</div>' +
                            '</div>';
            });
            specsGrid.innerHTML = gridHtml;

            if (seeMoreWrap) {
                seeMoreWrap.style.display = (count > 14) ? 'block' : 'none';
            }
        }

        // Rebuild bottom tab specifications table
        if (tabSpecsTbody) {
            var tabHtml = '';
            mergedSpecs.forEach(function(item) {
                tabHtml += '<tr>' +
                           '  <td class="fw-semibold text-secondary" style="width: 35%;">' + escapeHtml(item.name) + '</td>' +
                           '  <td class="text-dark">' + escapeHtml(item.value) + '</td>' +
                           '</tr>';
            });
            tabSpecsTbody.innerHTML = tabHtml;
        }
    }

    function parseJsonSafe(val) {
        if (!val) return [];
        if (Array.isArray(val)) return val;
        if (typeof val === 'object') return val;
        try {
            var parsed = JSON.parse(val);
            return Array.isArray(parsed) ? parsed : [];
        } catch(e) {
            return [];
        }
    }

    // 4. COLOR / PRIMARY OPTION SELECTION
    window.selectColor = function(colorName, cardEl) {
        if (selectedColor === colorName) return;
        showVariantLoader();

        selectedColor = colorName;

        // Update label (Capitalized as in product_deatils_mobile.jpeg)
        var label = document.getElementById('selected-color-name');
        if (label) {
            label.textContent = isColorTypeAttr ? (colorName.charAt(0).toUpperCase() + colorName.slice(1)) : colorName;
        }

        // Update active class on cards / chips
        var cards = document.querySelectorAll('.color-thumb-card, .color-thumb-card-chip');
        cards.forEach(function(c) { c.classList.remove('active'); });
        if (cardEl) {
            cardEl.classList.add('active');
            if (isColorTypeAttr && cardEl.scrollIntoView) {
                cardEl.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                setTimeout(updateColorSliderArrows, 250);
            }
        }

        // Swap images in 2x2 grid / mobile slider and Lightbox for this variant color
        if (colorMap[colorName] && colorMap[colorName].images && colorMap[colorName].images.length > 0) {
            var colorImgs = colorMap[colorName].images.map(function(img) {
                return (img.indexOf('://') !== -1) ? img : ("<?= base_url('assets/images/'); ?>" + img);
            });
            galleryImages = colorImgs;
            totalGallery = galleryImages.length;
            activeLightboxIdx = 0;

            renderProductGrid(galleryImages);
            renderLightboxStrip(galleryImages);
        } else if (colorMap[colorName] && colorMap[colorName].image) {
            var newImgSrc = "<?= base_url('assets/images/'); ?>" + colorMap[colorName].image;
            galleryImages = [newImgSrc];
            totalGallery = 1;
            activeLightboxIdx = 0;

            renderProductGrid(galleryImages);
            renderLightboxStrip(galleryImages);
        }

        // Re-evaluate size chips for this color
        updateSizeAvailability();

        // Update variant details
        syncCurrentVariant();

        hideVariantLoader(250);
    };

    // 5. SIZE / STORAGE / OPTION SELECTION
    window.selectSize = function(sizeName, chipEl) {
        // If out of stock, warn user or prompt
        if (chipEl && chipEl.classList.contains('is-out-of-stock')) {
            var colorMsg = selectedColor ? (' for color ' + selectedColor) : '';
            alert(multiAttrName + ' ' + sizeName + ' is currently out of stock' + colorMsg + '. You can select another option.');
            return;
        }

        showVariantLoader();

        selectedSize = sizeName;

        var label = document.getElementById('selected-size-name');
        if (label) label.textContent = sizeName;

        var chips = document.querySelectorAll('.variant-size-chip');
        chips.forEach(function(ch) { ch.classList.remove('active'); });
        if (chipEl) chipEl.classList.add('active');

        // Sync sticky dropdown
        var stickySelect = document.getElementById('sticky-size-dropdown');
        if (stickySelect) {
            stickySelect.value = sizeName;
        }

        syncCurrentVariant();

        hideVariantLoader(250);
    };

    window.onStickySizeChange = function(val) {
        var chips = document.querySelectorAll('.variant-size-chip');
        chips.forEach(function(ch) {
            if (ch.getAttribute('data-size') === val) {
                ch.click();
            }
        });
    };

    // 6. UPDATE SIZE / STORAGE AVAILABILITY BASED ON SELECTED COLOR OR SIZE VARIANT MAP
    function updateSizeAvailability() {
        if (!hasSize) return;

        var chips = document.querySelectorAll('.variant-size-chip');
        var colorSizes = (selectedColor && colorMap[selectedColor] && colorMap[selectedColor].sizes) ? colorMap[selectedColor].sizes : {};

        var firstInStockSize = '';

        chips.forEach(function(ch) {
            var s = ch.getAttribute('data-size');
            var sizeData = colorSizes[s] || (!selectedColor && sizeVariantMap && sizeVariantMap[s] ? sizeVariantMap[s] : null);
            var inStock = sizeData ? (sizeData.stock > 0) : true;

            if (!inStock) {
                ch.classList.add('is-out-of-stock');
                ch.title = 'Out of stock';
            } else {
                ch.classList.remove('is-out-of-stock');
                ch.title = multiAttrName + ' ' + s;
                if (!firstInStockSize) firstInStockSize = s;
            }
        });

        // If currently selected size is out of stock, auto-switch to first in-stock size
        var currentSizeData = colorSizes[selectedSize] || (!selectedColor && sizeVariantMap && sizeVariantMap[selectedSize] ? sizeVariantMap[selectedSize] : null);
        if (currentSizeData && currentSizeData.stock <= 0 && firstInStockSize) {
            chips.forEach(function(ch) {
                if (ch.getAttribute('data-size') === firstInStockSize) {
                    selectSize(firstInStockSize, ch);
                }
            });
        }
    }

    // 7. SYNC VARIANT DETAILS (Title, Price, SKU, Stock Status, Highlights, Specs)
    function syncCurrentVariant() {
        var variantData = null;
        if (hasVariants && colorMap[selectedColor] && selectedSize && colorMap[selectedColor].sizes && colorMap[selectedColor].sizes[selectedSize]) {
            variantData = colorMap[selectedColor].sizes[selectedSize];
        } else if (hasVariants && colorMap[selectedColor] && colorMap[selectedColor].variant) {
            variantData = colorMap[selectedColor].variant;
        } else if (hasVariants && colorMap[selectedColor] && colorMap[selectedColor].sizes) {
            var firstS = Object.keys(colorMap[selectedColor].sizes)[0];
            if (firstS) variantData = colorMap[selectedColor].sizes[firstS];
        } else if (hasVariants && sizeVariantMap && selectedSize && sizeVariantMap[selectedSize]) {
            variantData = sizeVariantMap[selectedSize];
        }

        if (variantData) {
            selectedVariantId = variantData.variant_id;

            // Update Product Title if variant has a title
            if (variantData.title) {
                var titleEl = document.getElementById('display-product-title');
                if (titleEl) titleEl.textContent = variantData.title;
                var tabTitleEl = document.getElementById('tab-desc-product-title');
                if (tabTitleEl) tabTitleEl.textContent = variantData.title;
            }

            // If variant has its own image and we only selected size (no color map image swap triggered), update gallery slot 0
            if (!hasColor && variantData.image) {
                var vImgUrl = (variantData.image.indexOf('://') !== -1) ? variantData.image : ("<?= base_url('assets/images/'); ?>" + variantData.image);
                galleryImages = [vImgUrl].concat(defaultGalleryImages.filter(function(u) { return u !== vImgUrl; }));
                totalGallery = galleryImages.length;
                activeLightboxIdx = 0;
                renderProductGrid(galleryImages);
                renderLightboxStrip(galleryImages);
            }

            var regularP = (typeof variantData.price !== 'undefined' && variantData.price !== null) ? parseFloat(variantData.price) : regularPrice;
            var saleP = (typeof variantData.sale_price !== 'undefined' && variantData.sale_price !== null && variantData.sale_price !== '') ? parseFloat(variantData.sale_price) : null;
            var effectivePrice = (saleP !== null && saleP > 0) ? saleP : regularP;

            currentPrice = effectivePrice;
            regularPrice = regularP;

            // Price display
            var salePriceEl = document.getElementById('display-sale-price');
            if (salePriceEl) {
                salePriceEl.textContent = currencySymbol + Math.round(effectivePrice).toLocaleString('en-IN');
            }

            // MRP and Discount badge
            var discountBadgeEl = document.getElementById('display-discount-rate');
            var mrpEl = document.getElementById('display-mrp-price');

            if (saleP !== null && saleP > 0 && regularP > saleP) {
                var discountPct = Math.round(((regularP - saleP) / regularP) * 100);
                if (discountBadgeEl) {
                    discountBadgeEl.innerHTML = '<i class="fa-solid fa-arrow-down-long me-1"></i>' + discountPct + '%';
                    discountBadgeEl.style.display = 'inline-flex';
                }
                if (mrpEl) {
                    mrpEl.textContent = 'M.R.P.: ' + currencySymbol + Math.round(regularP).toLocaleString('en-IN');
                    mrpEl.style.display = 'inline';
                }
            } else {
                if (discountBadgeEl) discountBadgeEl.style.display = 'none';
                if (mrpEl) mrpEl.style.display = 'none';
            }

            var curQty = parseInt(document.getElementById('product-qty-input')?.value, 10) || 1;
            var atcPriceEl = document.getElementById('atc-btn-price');
            if (atcPriceEl) {
                atcPriceEl.textContent = currencySymbol + Math.round(effectivePrice * curQty).toLocaleString('en-IN');
            }

            // SKU
            var skuEl = document.getElementById('display-sku');
            if (skuEl && variantData.sku) {
                skuEl.textContent = variantData.sku;
            }

            // Stock status
            var stockEl = document.getElementById('display-stock-status');
            var atcBtn = document.getElementById('main-btn-atc');
            var buyBtn = document.getElementById('btn-buy-now');
            var mobBtns = document.querySelectorAll('.btn-mobile-atc, .btn-mobile-buy');
            if (stockEl) {
                if (variantData.stock > 0) {
                    stockEl.textContent = 'In Stock (' + variantData.stock + ' available)';
                    stockEl.className = 'text-caption-1 mb-0 fw-bold text-success';
                    if (atcBtn) atcBtn.disabled = false;
                    if (buyBtn) buyBtn.disabled = false;
                    mobBtns.forEach(function(b) { b.disabled = false; });
                } else {
                    stockEl.textContent = 'Out of Stock';
                    stockEl.className = 'text-caption-1 mb-0 fw-bold text-danger';
                    if (atcBtn) atcBtn.disabled = true;
                    if (buyBtn) buyBtn.disabled = true;
                    mobBtns.forEach(function(b) { b.disabled = true; });
                }
            }

            // Update Highlights & Specifications
            updateVariantHighlights(variantData);
            updateVariantSpecifications(variantData);
        }
    }

    // 7. QUANTITY CONTROLLER
    window.changeQty = function(delta, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
            if (e.stopImmediatePropagation) e.stopImmediatePropagation();
        }
        var input = document.getElementById('product-qty-input');
        if (input) {
            var maxLimit = parseInt(input.getAttribute('data-max-limit') || input.getAttribute('max'), 10) || 5;
            var val = parseInt(input.value, 10) || 1;
            val = Math.max(1, Math.min(maxLimit, val + delta));
            input.value = val;

            // Resolve unit price reliably without NaN
            var unitPrice = 0;
            if (typeof currentPrice === 'number' && !isNaN(currentPrice) && currentPrice > 0) {
                unitPrice = currentPrice;
            } else {
                var priceEl = document.getElementById('display-sale-price');
                if (priceEl) {
                    unitPrice = parseFloat(priceEl.textContent.replace(/[^0-9.]/g, '')) || 0;
                }
            }

            // Update ATC button dynamic price
            var atcPriceEl = document.getElementById('atc-btn-price');
            if (atcPriceEl && unitPrice > 0) {
                atcPriceEl.textContent = currencySymbol + Math.round(unitPrice * val).toLocaleString('en-IN');
            }
        }
    };

    // 8. SIDE DRAWER CART MODAL & AJAX ADD TO CART
    window.addToCartAjax = function(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        var qtyInput = document.getElementById('product-qty-input');
        var qty = parseInt(qtyInput ? qtyInput.value : '1', 10) || 1;
        var allAtcBtns = document.querySelectorAll('#main-btn-atc, .btn-mobile-atc');
        allAtcBtns.forEach(function(b) {
            b.disabled = true;
            b.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Adding...';
        });

        var payload = new URLSearchParams();
        payload.append('product_id', productId);
        payload.append('quantity', qty);
        if (selectedVariantId) {
            payload.append('variant_id', selectedVariantId);
        }

        fetch('<?= site_url("cart/add"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: payload.toString()
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            allAtcBtns.forEach(function(b) {
                b.disabled = false;
                b.innerHTML = 'Add to cart';
            });

            if (data.status === 'success' || data.success) {
                // 1. Update cart count badges
                var newCount = (typeof data.cart_count !== 'undefined')
                    ? data.cart_count
                    : (data.cart_summary && typeof data.cart_summary.item_count !== 'undefined' ? data.cart_summary.item_count : null);

                if (newCount !== null) {
                    var countBadges = document.querySelectorAll('#cart-counter, .cart-count, .count-box.cart-count, .count-cart, .side-cart-count');
                    countBadges.forEach(function(b) {
                        b.textContent = newCount;
                    });
                }

                // 2. Re-render the side cart modal with updated items and subtotal
                if (typeof renderSideCart === 'function') {
                    renderSideCart(data.cart_items || [], data.cart_summary || {});
                }

                // 3. Open the side cart drawer modal (sliding from the right)
                if (typeof openSideCartModal === 'function') {
                    openSideCartModal();
                } else if (typeof bootstrap !== 'undefined' && document.getElementById('shoppingCart')) {
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('shoppingCart')).show();
                } else if (typeof $ !== 'undefined') {
                    $('#shoppingCart').modal('show');
                }
            } else {
                alert(data.message || 'Could not add product to cart.');
            }
        })
        .catch(function(err) {
            allAtcBtns.forEach(function(b) {
                b.disabled = false;
                b.innerHTML = 'Add to cart';
            });
            console.error('Add to cart error:', err);
            // Open side modal as fallback
            if (typeof openSideCartModal === 'function') {
                openSideCartModal();
            }
        });
    };

    // 9. BUY IT NOW DIRECT CHECKOUT
    window.buyNow = function() {
        var qty = parseInt(document.getElementById('product-qty-input')?.value || '1', 10) || 1;
        var payload = new URLSearchParams();
        payload.append('product_id', productId);
        payload.append('quantity', qty);
        if (selectedVariantId) {
            payload.append('variant_id', selectedVariantId);
        }

        var allBuyBtns = document.querySelectorAll('#btn-buy-now, .btn-mobile-buy');
        allBuyBtns.forEach(function(b) {
            b.disabled = true;
            b.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Please wait...';
        });

        fetch('<?= site_url("cart/add"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: payload.toString()
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            var key = (data && data.item && data.item.cart_key) ? data.item.cart_key : '';
            window.location.href = '<?= site_url("checkout"); ?>' + (key ? '?buy_now=' + encodeURIComponent(key) : '');
        })
        .catch(function() {
            window.location.href = '<?= site_url("checkout"); ?>';
        });
    };

    // 10. WISHLIST TOGGLE AJAX
    window.toggleWishlist = function(pId, btnEl) {
        if (typeof event !== 'undefined' && event) {
            event.preventDefault();
            event.stopPropagation();
        }
        var fd = new FormData();
        fd.append('product_id', pId);

        fetch('<?= site_url("wishlist/toggle"); ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: fd
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.status === 'success' || data.success) {
                var isAdded = (data.action === 'added' || data.is_wishlisted === true);
                isProductInWishlist = isAdded;

                // Sync all wishlist buttons across desktop and mobile
                var allWishlistBtns = document.querySelectorAll('.wishlist-btn, #btn-grid-wishlist, #btn-mobile-wishlist, .box-icon.wishlist');
                allWishlistBtns.forEach(function(b) {
                    var icon = b.querySelector('i');
                    if (isAdded) {
                        b.classList.add('active', 'in-wishlist');
                        if (icon) {
                            if (b.id === 'btn-grid-wishlist') {
                                icon.className = 'fa-solid fa-heart text-white';
                            } else {
                                icon.className = 'fa-solid fa-heart text-danger';
                            }
                        }
                    } else {
                        b.classList.remove('active', 'in-wishlist');
                        if (icon) {
                            icon.className = 'fa-regular fa-heart';
                        }
                    }
                });

                // Update header counter badges
                if (typeof data.wishlist_count !== 'undefined') {
                    document.querySelectorAll('.wishlist-count, .wishlist-badge').forEach(function(el) {
                        el.textContent = data.wishlist_count;
                    });
                    var headerCounter = document.getElementById('wishlist-counter');
                    if (headerCounter) {
                        headerCounter.textContent = data.wishlist_count;
                        headerCounter.style.display = (data.wishlist_count > 0) ? '' : 'none';
                    }
                }

                // Show Toast Notification
                var toastMsg = data.message || (isAdded ? 'Added to your wishlist!' : 'Removed from your wishlist.');
                if (typeof showAppToast === 'function') {
                    showAppToast(toastMsg, 'success');
                }
            } else if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                if (typeof showAppToast === 'function') {
                    showAppToast((data && data.message) ? data.message : 'Unable to update wishlist.', 'danger');
                }
            }
        })
        .catch(function(err) {
            console.error('Wishlist toggle error:', err);
            if (typeof showAppToast === 'function') {
                showAppToast('Network error updating wishlist.', 'danger');
            }
        });
    };

    // 11. BUNDLE TOTAL CALCULATOR
    window.updateBundleTotal = function() {
        var total = currentPrice;
        var checkboxes = document.querySelectorAll('.fbt-checkbox:checked');
        checkboxes.forEach(function(cb) {
            total += parseFloat(cb.getAttribute('data-price')) || 0;
        });
        var display = document.getElementById('bundle-total-price');
        if (display) {
            display.textContent = currencySymbol + Math.round(total).toLocaleString('en-IN');
        }
    };

    window.addBundleToCart = function() {
        var ids = [productId];
        var checkboxes = document.querySelectorAll('.fbt-checkbox:checked');
        checkboxes.forEach(function(cb) {
            ids.push(cb.getAttribute('data-id'));
        });

        // Add sequentially
        var promises = ids.map(function(id) {
            var p = new URLSearchParams();
            p.append('product_id', id);
            p.append('quantity', 1);
            return fetch('<?= site_url("cart/add"); ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: p.toString()
            });
        });

        Promise.all(promises).then(function() {
            window.location.href = '<?= site_url("cart"); ?>';
        });
    };

    // 12. SIZE CALCULATOR RANGE INPUTS (Modal)
    window.updateSizeCalc = function() {
        var h = parseInt(document.getElementById('calc-height-range').value);
        var w = parseInt(document.getElementById('calc-weight-range').value);
        document.getElementById('calc-height-val').textContent = h + ' cm';
        document.getElementById('calc-weight-val').textContent = w + ' kg';

        // Estimation logic
        var size = 'L';
        var bmi = w / ((h / 100) * (h / 100));
        if (bmi < 20) {
            size = (h < 165) ? 'S' : 'M';
        } else if (bmi < 24) {
            size = (h < 175) ? 'M' : 'L';
        } else if (bmi < 28) {
            size = (h < 180) ? 'XL' : 'XXL';
        } else {
            size = '3XL';
        }
        document.getElementById('calc-recommended-size').textContent = size;
    };

    window.activateReviewTab = function() {
        var tabTitle = document.getElementById('nav-review-tab') || document.querySelector('.widget-tabs.style-1 .widget-menu-tab .item-title:nth-child(3)');
        if (tabTitle) {
            tabTitle.click();
            tabTitle.scrollIntoView({ behavior: 'smooth' });
        }
    };



    // 13. Mobile Bottom Action Bar: Fixed until scrolling reaches in-page buttons (attached before tabs)
    function setupMobileBottomBarScroll() {
        var inPageBtns = document.getElementById('in-page-action-buttons');
        var mobileBar = document.getElementById('mobile-bottom-action-bar');
        if (!inPageBtns || !mobileBar) return;

        var ticking = false;
        function updateMobileBarVisibility() {
            if (window.innerWidth >= 768) {
                ticking = false;
                return;
            }
            var rect = inPageBtns.getBoundingClientRect();
            var vh = window.innerHeight || document.documentElement.clientHeight;
            // When in-page action buttons reach or enter the viewport (attached before tabs)
            if (rect.bottom <= vh + 10 || rect.top <= vh - 40) {
                mobileBar.classList.add('mobile-bar-hidden');
            } else {
                mobileBar.classList.remove('mobile-bar-hidden');
            }
            ticking = false;
        }

        function requestUpdate() {
            if (!ticking) {
                window.requestAnimationFrame(updateMobileBarVisibility);
                ticking = true;
            }
        }

        window.addEventListener('scroll', requestUpdate, { passive: true });
        window.addEventListener('resize', requestUpdate, { passive: true });
        requestUpdate();
    }

    // Initial setup
    updateSizeAvailability();
    syncCurrentVariant();
    updateColorSliderArrows();
    updateMobileSliderIndicator();
    setupMobileBottomBarScroll();

    window.addEventListener('resize', function() {
        updateColorSliderArrows();
        updateMobileSliderIndicator();
    });

    var galleryEl = document.getElementById('product-grid-gallery');
    if (galleryEl) {
        galleryEl.addEventListener('scroll', updateMobileSliderIndicator, { passive: true });
    }

    // Prevent duplicate listeners from theme scripts
    if (window.jQuery) {
        $(function() {
            $('.tf-product-info-list .btn-increase, .tf-product-info-list .btn-decrease, .tf-product-info-list .btn-quantity').off('click.tfQuantity');
        });
    }

    window.addEventListener('resize', function() {
        updateColorSliderArrows();
        updateMobileSliderIndicator();
        clearTimeout(window._specResizeTimer);
        window._specResizeTimer = setTimeout(function() {
            if (typeof evaluateSpecsGridFullWidth === 'function') {
                evaluateSpecsGridFullWidth();
            }
        }, 150);
    });

    // Run evaluateSpecsGridFullWidth on DOM load
    if (typeof evaluateSpecsGridFullWidth === 'function') {
        setTimeout(evaluateSpecsGridFullWidth, 100);
    }

})();

// Product Highlights & Specifications Accordions
window.toggleProductHighlights = function() {
    var body = document.getElementById('product-highlights-collapse');
    var icon = document.getElementById('highlights-arrow-icon');
    if (!body) return;
    if (body.style.display === 'none') {
        body.style.display = 'block';
        if (icon) icon.className = 'fa-solid fa-chevron-up';
        if (typeof evaluateSpecsGridFullWidth === 'function') {
            setTimeout(evaluateSpecsGridFullWidth, 50);
        }
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
        if (typeof evaluateSpecsGridFullWidth === 'function') {
            setTimeout(evaluateSpecsGridFullWidth, 50);
        }
    } else {
        body.style.display = 'none';
        if (icon) icon.className = 'fa-solid fa-chevron-down';
    }
};

window.areSpecsExpanded = false;
window.toggleSeeMoreSpecs = function(e) {
    if (e) e.stopPropagation();
    window.areSpecsExpanded = !window.areSpecsExpanded;
    var extras = document.querySelectorAll('.spec-overflow-item');
    var btnText = document.getElementById('btn-see-more-text');
    var btnIcon = document.getElementById('btn-see-more-icon');

    extras.forEach(function(el) {
        el.style.display = window.areSpecsExpanded ? 'block' : 'none';
    });

    if (window.areSpecsExpanded) {
        if (btnText) btnText.textContent = 'See Less';
        if (btnIcon) btnIcon.className = 'fa-solid fa-chevron-up ms-1';
        if (typeof evaluateSpecsGridFullWidth === 'function') {
            setTimeout(evaluateSpecsGridFullWidth, 50);
        }
    } else {
        if (btnText) btnText.textContent = 'See More';
        if (btnIcon) btnIcon.className = 'fa-solid fa-chevron-down ms-1';
    }
};

// Initialize Related Products & Recently Viewed Sliders (Same as Home Popular Products Slider)
(function() {
    function initDetailPopularStyleSliders() {
        var sliderConfigs = [
            { sel: '.aq-related-slider', prev: '.aq-related-prev', next: '.aq-related-next' },
            { sel: '.aq-recent-slider',  prev: '.aq-recent-prev',  next: '.aq-recent-next' }
        ];
        sliderConfigs.forEach(function(cfg) {
            var el = document.querySelector(cfg.sel);
            if (el && typeof Swiper !== 'undefined' && !el.swiper) {
                new Swiper(cfg.sel, {
                    slidesPerView: 5,
                    loop: true,
                    autoplay: true,
                    spaceBetween: 30,
                    speed: 1000,
                    observer: true,
                    observeParents: true,
                    navigation: {
                        prevEl: cfg.prev,
                        nextEl: cfg.next,
                    },
                    breakpoints: {
                        1400: { slidesPerView: 5, spaceBetween: 30 },
                        1200: { slidesPerView: 5, spaceBetween: 24 },
                        992:  { slidesPerView: 4, spaceBetween: 20 },
                        768:  { slidesPerView: 3, spaceBetween: 15 },
                        576:  { slidesPerView: 2, spaceBetween: 15 },
                        0:    { slidesPerView: 2, spaceBetween: 12 }
                    }
                });
            }
        });
    }

    if (!window._fshColorSwatchBound) {
        window._fshColorSwatchBound = true;
        ['click', 'mouseover'].forEach(function(evtType) {
            document.addEventListener(evtType, function(e) {
                var swatch = e.target.closest('.card-product .color-swatch');
                if (!swatch) return;
                var card = swatch.closest('.card-product');
                var swatchImg = swatch.querySelector('img');
                if (card && swatchImg) {
                    var newSrc = swatchImg.getAttribute('src') || swatchImg.getAttribute('data-src');
                    var prodImg = card.querySelector('.img-product');
                    if (prodImg && newSrc) {
                        prodImg.setAttribute('src', newSrc);
                        prodImg.setAttribute('data-src', newSrc);
                    }
                }
                if (swatch.parentElement) {
                    swatch.parentElement.querySelectorAll('.color-swatch.active').forEach(function(sib) {
                        sib.classList.remove('active');
                    });
                }
                swatch.classList.add('active');
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDetailPopularStyleSliders);
    } else {
        initDetailPopularStyleSliders();
    }
    window.addEventListener('load', initDetailPopularStyleSliders);

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof jQuery !== 'undefined') {
            jQuery('a[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
                document.querySelectorAll('.aq-related-slider, .aq-recent-slider').forEach(function(el) {
                    if (el.swiper) {
                        el.swiper.update();
                    }
                });
            });
        }
    });
})();
</script>
