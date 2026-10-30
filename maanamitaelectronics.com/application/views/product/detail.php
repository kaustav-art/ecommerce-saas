<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Collect all images (main image first, followed by gallery images)
$all_images = [];
if (!empty($product['main_image'])) {
    $all_images[] = $product['main_image'];
}
if (!empty($product['gallery_images_decoded']) && is_array($product['gallery_images_decoded'])) {
    foreach ($product['gallery_images_decoded'] as $g_img) {
        if (!empty($g_img) && !in_array($g_img, $all_images)) {
            $all_images[] = $g_img;
        }
    }
}
if (empty($all_images)) {
    $all_images[] = 'products/womens/women-1.jpg';
}
$total_images = count($all_images);

// Price & discount calculations
$regular_price = (float) $product['price'];
$sale_price = !empty($product['sale_price']) ? (float) $product['sale_price'] : null;
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
$has_color = !empty($product['attributes']['color']);

// Detect secondary option attribute (e.g., Size, Storage, or any active multiple_select attribute)
$multi_attr      = null;
$multi_attr_slug = '';
$multi_attr_name = 'Option';

if (!empty($product['attributes'])) {
    // First priority: look for attribute with multiple_select type or size or storage
    foreach ($product['attributes'] as $slug => $attr_info) {
        if ($slug === 'color') continue;
        if (!empty($attr_info['values']) && (!empty($attr_info['type']) && $attr_info['type'] === 'multiple_select')) {
            $multi_attr      = $attr_info;
            $multi_attr_slug = $slug;
            $multi_attr_name = $attr_info['name'];
            break;
        }
    }
    // Second priority: any non-color attribute with values
    if (empty($multi_attr)) {
        foreach ($product['attributes'] as $slug => $attr_info) {
            if ($slug === 'color') continue;
            if (!empty($attr_info['values'])) {
                $multi_attr      = $attr_info;
                $multi_attr_slug = $slug;
                $multi_attr_name = $attr_info['name'];
                break;
            }
        }
    }
}
$has_size = !empty($multi_attr);

// Color map with variant photos & size/option inventory
$color_map = [];
$initial_color = '';
$initial_size = '';
$initial_variant_id = 0;
$initial_sku = $product['sku'] ?? ('SKU-' . ($product['id'] ?? '100'));

if ($has_color && !empty($product['attributes']['color']['values'])) {
    foreach ($product['attributes']['color']['values'] as $idx => $c_val) {
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
                        if ($val['attribute_slug'] === 'color' && strcasecmp($val['attribute_value'], $c_name) === 0) {
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
        if (empty($c_photo)) {
            $c_photo = $product['main_image'];
        }

        // Color images list
        $c_images = [];
        if (!empty($c_photo)) {
            $c_images[] = $c_photo;
        }
        if (!empty($c_gallery)) {
            foreach ($c_gallery as $g) {
                if (!in_array($g, $c_images)) {
                    $c_images[] = $g;
                }
            }
        } else {
            // Keep product gallery images with variant photo as slot 0
            if (!empty($product['gallery_images_decoded']) && is_array($product['gallery_images_decoded'])) {
                foreach ($product['gallery_images_decoded'] as $g_img) {
                    if (!empty($g_img) && !in_array($g_img, $c_images)) {
                        $c_images[] = $g_img;
                    }
                }
            }
        }
        if (empty($c_images)) {
            $c_images = $all_images;
        }

        $color_map[$c_name] = [
            'id'         => $c_val['id'],
            'name'       => $c_name,
            'color_code' => $c_val['color_code'] ?? '#333333',
            'image'      => $c_photo,
            'gallery'    => $c_gallery,
            'images'     => $c_images,
            'sizes'      => []
        ];
    }
}

// If initial color has custom gallery images, prioritize them for initial view
if (!empty($initial_color) && !empty($color_map[$initial_color]['gallery'])) {
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
                if (strcasecmp($val['attribute_slug'], 'color') === 0 || strcasecmp($val['attribute_name'], 'color') === 0) {
                    $v_color = $val['attribute_value'];
                }
                if ($multi_attr_slug && (strcasecmp($val['attribute_slug'], $multi_attr_slug) === 0 || strcasecmp($val['attribute_name'], $multi_attr_name) === 0)) {
                    $v_size = $val['attribute_value'];
                } elseif (strcasecmp($val['attribute_slug'], 'size') === 0 || strcasecmp($val['attribute_name'], 'size') === 0 || strcasecmp($val['attribute_slug'], 'storage') === 0 || strcasecmp($val['attribute_name'], 'storage') === 0) {
                    $v_size = $val['attribute_value'];
                }
            }
        }

        $v_highlights = !empty($pv['highlights']) ? (is_array($pv['highlights']) ? $pv['highlights'] : (json_decode($pv['highlights'], true) ?: [])) : [];
        $v_specs = !empty($pv['specifications']) ? (is_array($pv['specifications']) ? $pv['specifications'] : (json_decode($pv['specifications'], true) ?: [])) : [];

        $var_entry = [
            'variant_id'     => (int) $pv['id'],
            'sku'            => $pv['sku'],
            'price'          => (float) $pv['price'],
            'sale_price'     => (isset($pv['sale_price']) && $pv['sale_price'] !== null && $pv['sale_price'] !== '') ? (float) $pv['sale_price'] : null,
            'stock'          => (int) $pv['stock_quantity'],
            'image'          => !empty($pv['image']) ? $pv['image'] : null,
            'color'          => $v_color,
            'size'           => $v_size,
            'highlights'     => $v_highlights,
            'specifications' => $v_specs
        ];

        if ($v_color && $v_size && isset($color_map[$v_color])) {
            $color_map[$v_color]['sizes'][$v_size] = $var_entry;
            // Pick initial in-stock size for initial color
            if ($v_color === $initial_color && empty($initial_size) && (int) $pv['stock_quantity'] > 0) {
                $initial_size       = $v_size;
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
    if (!empty($initial_color) && !empty($color_map[$initial_color]['sizes'][$initial_size])) {
        $initial_variant = $color_map[$initial_color]['sizes'][$initial_size];
    } elseif (!empty($size_variant_map[$initial_size])) {
        $initial_variant = $size_variant_map[$initial_size];
    } elseif (!empty($product['variants'])) {
        $first_v = $product['variants'][0];
        $initial_variant = [
            'variant_id'     => (int) $first_v['id'],
            'sku'            => $first_v['sku'],
            'price'          => (float) $first_v['price'],
            'sale_price'     => (isset($first_v['sale_price']) && $first_v['sale_price'] !== null && $first_v['sale_price'] !== '') ? (float) $first_v['sale_price'] : null,
            'stock'          => (int) $first_v['stock_quantity'],
            'image'          => !empty($first_v['image']) ? $first_v['image'] : null,
            'color'          => $initial_color,
            'size'           => $initial_size,
            'highlights'     => !empty($first_v['highlights']) ? (json_decode($first_v['highlights'], true) ?: []) : [],
            'specifications' => !empty($first_v['specifications']) ? (json_decode($first_v['specifications'], true) ?: []) : []
        ];
    }
}

if (!empty($initial_variant)) {
    $regular_price = (float) $initial_variant['price'];
    $sale_price    = ($initial_variant['sale_price'] !== null) ? (float) $initial_variant['sale_price'] : null;
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

/* Floating Action Buttons on 2nd Image */
.grid-floating-actions {
    position: absolute;
    top: 10px;
    right: 10px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    z-index: 10;
}
.grid-action-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #ffffff;
    border: 1px solid #d1d5db;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #181818;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    cursor: pointer;
    transition: all 0.2s ease;
}
.grid-action-btn:hover {
    background: #181818;
    color: #ffffff;
    border-color: #181818;
    transform: translateY(-2px);
}
.grid-action-btn.active {
    background: #dc3545;
    color: #ffffff;
    border-color: #dc3545;
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
.color-slider-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    border: 1px solid rgba(0, 0, 0, 0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 5;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.16);
    transition: all 0.2s ease;
    color: #1a1a1a;
    font-size: 13px;
    padding: 0;
    line-height: 1;
}

.color-slider-arrow:hover {
    background: #ffffff;
    color: #000000;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.22);
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
.lightbox-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    cursor: pointer;
    transition: background 0.2s ease;
    z-index: 10;
}
.lightbox-arrow:hover {
    background: rgba(255, 255, 255, 0.4);
}
.lightbox-arrow.prev { left: 20px; }
.lightbox-arrow.next { right: 20px; }
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
    .mobile-floating-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #ffffff;
        border: 1px solid #d1d5db;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.14);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1e2022;
        font-size: 16px;
        cursor: pointer;
        transition: transform 0.15s ease, background 0.15s ease;
        padding: 0;
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
                                        <button type="button" class="mobile-floating-btn mobile-wishlist-btn <?= !empty($is_in_wishlist) ? 'active' : ''; ?>" id="btn-mobile-wishlist" onclick="toggleWishlist(<?= $product['id']; ?>, this)" title="Wishlist" aria-label="Wishlist">
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
                                                <img src="<?= $img_src; ?>" alt="<?= html_escape($product['title']) . ' - ' . ($i + 1); ?>" id="grid-img-<?= $i; ?>" onerror="this.src='<?= base_url('assets/images/products/womens/women-1.jpg'); ?>'">
                                                
                                                <!-- Desktop Floating Actions in Top-Right corner of 2nd image (Slot 1) -->
                                                <?php if ($i === 1 || ($total_images === 1 && $i === 0)): ?>
                                                    <div class="grid-floating-actions d-none d-md-flex" onclick="event.stopPropagation();">
                                                        <button type="button" class="grid-action-btn wishlist-btn <?= !empty($is_in_wishlist) ? 'active' : ''; ?>" id="btn-grid-wishlist" onclick="toggleWishlist(<?= $product['id']; ?>, this)" title="Wishlist">
                                                            <i class="<?= !empty($is_in_wishlist) ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
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
                                            <!-- 1. COLOR SELECTION WITH SLIDER & PRODUCT PHOTOS (color_badge.PNG) -->
                                            <?php if ($has_color && !empty($color_map)): ?>
                                                <div class="variant-picker-item mb-2">
                                                    <div class="color-badge-heading mb-2">
                                                        <span class="color-badge-title">Selected Color:</span>
                                                        <span class="color-badge-name" id="selected-color-name"><?= ucwords(html_escape($initial_color)); ?></span>
                                                    </div>

                                                    <!-- Horizontal color slider with navigation arrows (color_badge.PNG) -->
                                                    <div class="color-variant-container">
                                                        <button type="button" class="color-slider-arrow prev" id="color-arrow-prev" onclick="slideColorTrack(-1)" aria-label="Previous Color" style="display: none;">
                                                            <i class="fa-solid fa-chevron-left"></i>
                                                        </button>
                                                        
                                                        <div class="color-slider-track" id="color-slider-track" onscroll="updateColorSliderArrows()">
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

                                                        <button type="button" class="color-slider-arrow next" id="color-arrow-next" onclick="slideColorTrack(1)" aria-label="Next Color">
                                                            <i class="fa-solid fa-chevron-right"></i>
                                                        </button>
                                                    </div>
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
                                                            
                                                            // Check stock for initial color
                                                            $in_stock = true;
                                                            if (!empty($color_map[$initial_color]['sizes'][$s_name])) {
                                                                $in_stock = ($color_map[$initial_color]['sizes'][$s_name]['stock'] > 0);
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

                                            <h3 class="name fw-bold mb-2" style="font-size: 26px !important; line-height: 1.35;"><?= html_escape($product['title']); ?></h3>

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
                                                    <?= $currency_symbol . number_format($current_price, 2); ?>
                                                </span>

                                                <span class="product-mrp-price" id="display-mrp-price" style="<?= ($sale_price && $sale_price < $regular_price) ? 'display: inline;' : 'display: none;'; ?>">
                                                    M.R.P.: <?= $currency_symbol . number_format($regular_price, 2); ?>
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
                                        if (!empty($multi_attr['values'])) {
                                            foreach ($multi_attr['values'] as $sv) {
                                                if (!empty($sv['value'])) $spec_multi_vals[] = $sv['value'];
                                            }
                                        } elseif (!empty($initial_size)) {
                                            $spec_multi_vals[] = $initial_size;
                                        }
                                        if (!empty($spec_multi_vals)) {
                                            $merged_specs[] = ['name' => $multi_attr_name, 'value' => implode(', ', array_unique($spec_multi_vals))];
                                            $seen_spec_keys[strtolower($multi_attr_name)] = true;
                                            $seen_spec_keys['size'] = true;
                                        }

                                        // Color
                                        $spec_colors = [];
                                        if (!empty($color_map)) {
                                            $spec_colors = array_keys($color_map);
                                        } elseif (!empty($product['attributes']['color']['values'])) {
                                            foreach ($product['attributes']['color']['values'] as $cv) {
                                                if (!empty($cv['value'])) $spec_colors[] = $cv['value'];
                                            }
                                        } elseif (!empty($initial_color)) {
                                            $spec_colors[] = $initial_color;
                                        }
                                        if (!empty($spec_colors)) {
                                            $merged_specs[] = ['name' => 'Color', 'value' => implode(', ', array_map('ucfirst', array_unique($spec_colors)))];
                                            $seen_spec_keys['color'] = true;
                                        }

                                        // Custom specifications from database
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

                                        // Variant custom specifications (if variable product)
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

                                        // Highlights
                                        $product_highlights = !empty($product['highlights_decoded']) ? $product['highlights_decoded'] : [];
                                        if (empty($product_highlights) && !empty($product['highlights'])) {
                                            $product_highlights = json_decode($product['highlights'], true) ?: [];
                                        }
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
                                                <p class="text-caption-1 mb-0 fw-bold text-success" id="display-stock-status">In Stock</p>
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
                                <li class="item-title active">
                                    <span class="inner">Description</span>
                                </li>
                                <li class="item-title">
                                    <span class="inner">Customer Reviews</span>
                                </li>
                                <li class="item-title">
                                    <span class="inner">Shipping & Returns</span>
                                </li>
                                <li class="item-title">
                                    <span class="inner">Return Policies</span>
                                </li>
                            </ul>
                            <div class="widget-content-tab">
                                <div class="widget-content-inner active">
                                    <div class="tab-description">
                                        <div class="right">
                                            <div class="letter-1 text-btn-uppercase mb_12"><?= html_escape($product['title']); ?></div>
                                            <p class="mb_12 text-secondary"><?= !empty($product['description']) ? $product['description'] : 'Designed with utmost care and attention to detail, this premium item combines timeless style with modern performance.'; ?></p>
                                            
                                             <div id="tab-specs-wrapper" style="<?= empty($merged_specs) ? 'display: none;' : ''; ?>">
                                                 <div class="letter-1 text-btn-uppercase mb_12 mt-4">Specifications & Details</div>
                                                 <div class="table-responsive">
                                                     <table class="table table-bordered table-striped table-sm mb-0">
                                                         <tbody id="tab-specs-tbody">
                                                             <?php foreach ($merged_specs as $spec): ?>
                                                                 <tr>
                                                                     <td class="fw-semibold text-secondary" style="width: 35%;"><?= html_escape($spec['name']); ?></td>
                                                                     <td class="text-dark"><?= html_escape($spec['value']); ?></td>
                                                                 </tr>
                                                             <?php endforeach; ?>
                                                         </tbody>
                                                     </table>
                                                 </div>
                                             </div>
                                        </div>
                                        <div class="left">
                                            <div class="letter-1 text-btn-uppercase mb_12">COMPOSITION, ORIGIN AND CARE GUIDELINES</div>
                                            <ul class="list-text type-disc mb_12 gap-6">
                                                <li class="font-2">Composition: 100% Breathable High Quality Fabric</li>
                                                <li class="font-2">Designed for all-day comfort and long-lasting durability</li>
                                                <li class="font-2">Origin: India / Imported</li>
                                                <li class="font-2">Manufacture: Certified Quality Standards</li>
                                            </ul>
                                            <div class="d-flex gap-20 mb_12 list-icon-guideline">
                                                <div class="d-flex">
                                                    <svg width="24" height="22" viewBox="0 0 24 22" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                                        <rect width="24" height="21.6" fill="url(#pattern0_15741_41601)"/>
                                                        <defs>
                                                        <pattern id="pattern0_15741_41601" patternContentUnits="objectBoundingBox" width="1" height="1">
                                                        <use xlink:href="#image0_15741_41601" transform="scale(0.0125 0.0138889)"/>
                                                        </pattern>
                                                        <image id="image0_15741_41601" width="80" height="72" xlink:href="data:image/png;base64,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"/>
                                                        </defs>
                                                    </svg>
                                                </div>
                                                <div class="d-flex">
                                                    <svg width="24" height="22" viewBox="0 0 24 22" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                                        <rect width="24" height="21.6" fill="url(#pattern0_15741_41602)"/>
                                                        <defs>
                                                        <pattern id="pattern0_15741_41602" patternContentUnits="objectBoundingBox" width="1" height="1">
                                                        <use xlink:href="#image0_15741_41602" transform="scale(0.0125 0.0138889)"/>
                                                        </pattern>
                                                        <image id="image0_15741_41602" width="80" height="72" xlink:href="data:image/png;base64,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"/>
                                                        </defs>
                                                    </svg>
                                                </div>
                                                <div class="d-flex">
                                                    <svg width="24" height="22" viewBox="0 0 24 22" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                                        <rect width="24" height="21.6" fill="url(#pattern0_15741_41603)"/>
                                                        <defs>
                                                        <pattern id="pattern0_15741_41603" patternContentUnits="objectBoundingBox" width="1" height="1">
                                                        <use xlink:href="#image0_15741_41603" transform="scale(0.0125 0.0138889)"/>
                                                        </pattern>
                                                        <image id="image0_15741_41603" width="80" height="72" xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFAAAABICAYAAABhlHJbAAAAAXNSR0IArs4c6QAAAERlWElmTU0AKgAAAAgAAYdpAAQAAAABAAAAGgAAAAAAA6ABAAMAAAABAAEAAKACAAQAAAABAAAAUKADAAQAAAABAAAASAAAAABhcJAMAAAErUlEQVR4Ae2bzVXbQBSFbR/D3i04axaglAAlkBJMCbiEuAQoAUogJcSwYO8S4j32wbl3oqeMpBl5RgJJ2M/nmJFkzd+n+35GEoOBfpSAElACSkAJKAEloASUgBJQAm0TGNbt8Pz8/LJu3T7W2263q9fX11Xs2MaxFeT84XD4xG2UcuhLl6enpwtMYB47iVFshUM9f7fb1bKow5BPg6tKVzQajYw1LZfLaB5Hr8CXl5dfwr+OXz96gIQH812mEBOBGVoqwH+kDEAExGkoODlPAYIEFCjpiypQlBFZigKTs7OzSUxdVSBo2YFkPB5HqVABpnKrG0gUYAoQAcSkM8gJVYExPkzOrRtIVIFCcDCQXHAaE0gUYArw+flZAA5iAokC/K9A5oOyrAv2gwrQAohAIvlg8J0ZBWgBrBNIFKAFcLPZGBOGEicIJFPrJ++mArTQ8JY+VLjmIdyhDvKDCtACmG4aP/j+/q4KLLPZf0QiMcw4KJCoAgtMsZQzt7ZCAUY/Ayj0592lE0ZCKmZQ8idcc+JqR9068nb2sT9MAM+MF+P7bifYrm5qP9YsNpY+T0h45YKvXv8fiRJktkIpzpn7jQBSZYhWM1ypGaB51SR+RQaAc9dw0pUDk3O7KDG+a3wpBrEg7zBqASS4k5OTW3QwY8soTQcAtcQ2c6kltlf75G8q9fAPrIlzoospuZ7icKMBovFb+K+fVkMrqOker0Y81nk1wmqnT5vBS7rgIJKq7oFXJp0pk84FVHbfp5l/xFgw1wlc0x+2hTlWBpKgNObi4iKByf4WeFDcHE/xvx0iPEKDJXE1YtIZALzkMd9nrwkTHio/Ad4Eja3x/WE/hPE1fADHacZTzLsykFQqkFJGI3cCD9tXRwJvYGUJ4rKcmqhUIPzAHWoxGlHSV181qjpnvv+gBJJKgN4gkiTJNfp4YD8AeNOmv6PysYoxKRL7hwWs2+yfffIDBjuWUKPX8pwK5AQAjabL+o9tDt72uexcPjg+w/26q9TBy+FPLcGAeS0VyK/c7s/16fSBvPri997e3qLf2sz1ELmDfpmgl1Y1nIityshm654uZuwNJE6AnAR7xBVYdJAc03U4P7EPvZ2NRBzE/PfemSkBpKmIArC66FWSjAmVlBnBo86pRoGo6H1WXAIIeJI4cmlmbm/X6blBHXnVzNVE1W+u8xsds1M2uA9nNC4BRI/GhHC1nU6z0YjCKj/6TsOYvL/56jQ9jj5FhfsB2u8II+K1PlhOFkFrgUHnXAf2eftrbiuiKZjQ+rBIIySf/y2mMYYyqXdkvrIOvUEqNRez6QKcAAaLFSBy16nAHECcOE0rimylndbL9AJ25Ubs+QoLBpJpMSsp+kADkNTtFo55216+up4VFwEamcLeFaClGgjKWAL8sFho9msOIEzY5Fk4sYv0JRtU3zbAxZgxSknxsiHmAGZHdSNHwHJppUCSAaSDlFpdRj0ZQ89KUWDppaMMIFKGDGDPBt/5cBhIoELj1oqBJJfGyEixHjb/vSj7WuYI0IyzRYYToMtZ5po44h0oMecHM4D8l3eYcav3/o74OujUlYASUAJKQAkoASWgBJSAElACDQj8BQtLlbWiLn8cAAAAAElFTkSuQmCC"/>
                                                        </defs>
                                                    </svg>
                                                </div>
                                                <div class="d-flex">
                                                    <svg width="24" height="22" viewBox="0 0 24 22" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                                        <rect width="24" height="21.6" fill="url(#pattern0_15741_41604)"/>
                                                        <defs>
                                                        <pattern id="pattern0_15741_41604" patternContentUnits="objectBoundingBox" width="1" height="1">
                                                        <use xlink:href="#image0_15741_41604" transform="scale(0.0125 0.0138889)"/>
                                                        </pattern>
                                                        <image id="image0_15741_41604" width="80" height="72" xlink:href="data:image/png;base64,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"/>
                                                        </defs>
                                                    </svg>
                                                </div>
                                                <div class="d-flex">
                                                    <svg width="24" height="22" viewBox="0 0 24 22" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                                        <rect width="24" height="21.6" fill="url(#pattern0_15741_41605)"/>
                                                        <defs>
                                                        <pattern id="pattern0_15741_41605" patternContentUnits="objectBoundingBox" width="1" height="1">
                                                        <use xlink:href="#image0_15741_41605" transform="scale(0.0125 0.0138889)"/>
                                                        </pattern>
                                                        <image id="image0_15741_41605" width="80" height="72" xlink:href="data:image/png;base64,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"/>
                                                        </defs>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="text-caption-2">MACHINE WASHING MAX 30°C / 85ºF SHORT SPIN DRY</div>
                                        </div>
                                    </div>
                                </div>
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
                                <div class="widget-content-inner">
                                    <div class="tab-shipping">
                                        <div class="w-100">
                                            <div class="text-btn-uppercase mb_12">We've got your back</div>
                                            <p class="mb_12">One delivery fee to most locations (check our Orders & Delivery page)</p>
                                            <p class="">Free returns within 14 days (excludes final sale and made-to-order items, face masks and certain products containing hazardous or flammable materials, such as fragrances and aerosols)</p>
                                        </div>
                                        <div class="w-100">
                                            <div class="text-btn-uppercase mb_12">Import duties information</div>
                                            <p>Let us handle the legwork. Delivery duties are included in the item price when shipping to all EU countries (excluding the Canary Islands), plus The United Kingdom, USA, Canada, China Mainland, Australia, New Zealand, Puerto Rico, Switzerland, Singapore, Republic Of Korea, Kuwait, Mexico, Qatar, India, Norway, Saudi Arabia, Taiwan Region, Thailand, U.A.E., Japan, Brazil, Isle of Man, San Marino, Colombia, Chile, Argentina, Egypt, Lebanon, Hong Kong SAR, Bahrain and Turkey. All import duties are included in your order – the price you see is the price you pay.</p>
                                        </div>
                                        <div class="w-100">
                                            <div class="text-btn-uppercase mb_12">Estimated delivery</div>
                                            <p class="mb_6 font-2">Express: May 10 - May 17</p>
                                            <p class="font-2">Sending from USA</p>
                                        </div>
                                        <div class="w-100">
                                            <div class="text-btn-uppercase mb_12">Need more information?</div>
                                            <div>
                                                <a href="<?= site_url('contact'); ?>" class="link text-secondary text-decoration-underline mb_6 font-2">Orders & delivery</a>
                                            </div>
                                            <div>
                                                <a href="<?= site_url('contact'); ?>" class="link text-secondary text-decoration-underline mb_6 font-2">Returns & refunds</a>
                                            </div>
                                            <div>
                                                <a href="<?= site_url('contact'); ?>" class="link text-secondary text-decoration-underline font-2">Duties & taxes</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="widget-content-inner">
                                    <div class="tab-policies">
                                        <div class="text-btn-uppercase mb_12">Return Policies</div>
                                        <p class="mb_12 text-secondary">At <?= html_escape($site_name ?? ($store_settings['site_name'] ?? 'our store')); ?>, we stand behind the quality of our products. If you're not completely satisfied with your purchase, we offer hassle-free returns within 30 days of delivery.</p>
                                        <div class="text-btn-uppercase mb_12">Easy Exchanges or Refunds</div>
                                        <ul class="list-text type-disc mb_12 gap-6">
                                            <li class="text-secondary font-2">Exchange your item for a different size, color, or style, or receive a full refund.</li>
                                            <li class="text-secondary font-2">All returned items must be unworn, in their original packaging, and with tags attached.</li>
                                        </ul>
                                        <div class="text-btn-uppercase mb_12">Simple Process</div>
                                        <ul class="list-text type-number">
                                            <li class="text-secondary font-2">Initiate your return online or contact our customer service team for assistance.</li>
                                            <li class="text-secondary font-2">Pack your item securely and include the original packing slip.</li>
                                            <li class="text-secondary font-2">Ship your return back to us using our prepaid shipping label.</li>
                                            <li class="text-secondary font-2">Once received, your refund will be processed promptly.</li>
                                        </ul>
                                        <p class="text-secondary font-2">For any questions or concerns regarding returns, don't hesitate to reach out to our dedicated customer service team. Your satisfaction is our priority.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Product_Description_Tabs -->

        <!-- RELATED PRODUCTS & RECENTLY VIEWED TABS -->
        <?php if (!empty($related_products) || !empty($recently_viewed_products)): ?>
            <section class="flat-spacing border-top">
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
                                <div class="position-relative">
                                    <div dir="ltr" class="swiper tf-sw-latest" data-preview="4" data-tablet="3" data-mobile="2" data-space-lg="30" data-space-md="30" data-space="15" data-pagination="1" data-pagination-md="1" data-pagination-lg="1">
                                        <div class="swiper-wrapper">
                                            <?php foreach ($related_products as $rp): ?>
                                                <div class="swiper-slide">
                                                    <?php $this->load->view('home/_product_card', ['p' => $rp, 'currency_symbol' => $currency_symbol ?? '$']); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="sw-pagination-latest sw-dots type-circle justify-content-center mt-4"></div>
                                    </div>
                                    <div class="nav-next-latest nav-sw nav-sw-left d-none d-md-flex position-absolute top-50 start-0 translate-middle-y shadow-sm" style="z-index: 5; margin-left: -16px; cursor: pointer;" title="Previous">
                                        <i class="fa-solid fa-chevron-left" style="font-size: 13px;"></i>
                                    </div>
                                    <div class="nav-prev-latest nav-sw nav-sw-right d-none d-md-flex position-absolute top-50 end-0 translate-middle-y shadow-sm" style="z-index: 5; margin-right: -16px; cursor: pointer;" title="Next">
                                        <i class="fa-solid fa-chevron-right" style="font-size: 13px;"></i>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($recently_viewed_products)): ?>
                            <div class="tab-pane <?= empty($related_products) ? 'active show' : ''; ?>" id="recentlyViewedTab" role="tabpanel">
                                <div class="position-relative">
                                    <div dir="ltr" class="swiper tf-sw-recent" data-preview="4" data-tablet="3" data-mobile="2" data-space-lg="30" data-space-md="30" data-space="15" data-pagination="1" data-pagination-md="1" data-pagination-lg="1">
                                        <div class="swiper-wrapper">
                                            <?php foreach ($recently_viewed_products as $rvp): ?>
                                                <div class="swiper-slide">
                                                    <?php $this->load->view('home/_product_card', ['p' => $rvp, 'currency_symbol' => $currency_symbol ?? '$']); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="sw-pagination-recent sw-dots type-circle justify-content-center mt-4"></div>
                                    </div>
                                    <div class="nav-next-recent nav-sw nav-sw-left d-none d-md-flex position-absolute top-50 start-0 translate-middle-y shadow-sm" style="z-index: 5; margin-left: -16px; cursor: pointer;" title="Previous">
                                        <i class="fa-solid fa-chevron-left" style="font-size: 13px;"></i>
                                    </div>
                                    <div class="nav-prev-recent nav-sw nav-sw-right d-none d-md-flex position-absolute top-50 end-0 translate-middle-y shadow-sm" style="z-index: 5; margin-right: -16px; cursor: pointer;" title="Next">
                                        <i class="fa-solid fa-chevron-right" style="font-size: 13px;"></i>
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
    var baseHighlights   = <?= json_encode($product_highlights); ?>;
    var baseSpecifications = <?= json_encode(!empty($product['specifications']) ? $product['specifications'] : []); ?>;
    var baseBrand        = <?= json_encode(!empty($product['brand_name']) ? $product['brand_name'] : ''); ?>;
    var baseCategory     = <?= json_encode(!empty($product['category_name']) ? $product['category_name'] : ''); ?>;
    var hasVariants      = <?= $has_variants ? 'true' : 'false'; ?>;
    var hasColor         = <?= $has_color ? 'true' : 'false'; ?>;
    var hasSize          = <?= $has_size ? 'true' : 'false'; ?>;
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
            html += '  <img src="' + imgSrc + '" alt="Product Image ' + (i + 1) + '" id="grid-img-' + i + '" onerror="this.src=\'<?= base_url("assets/images/products/womens/women-1.jpg"); ?>\'">';

            if (i === 1 || (count === 1 && i === 0)) {
                html += '  <div class="grid-floating-actions d-none d-md-flex" onclick="event.stopPropagation();">';
                html += '    <button type="button" class="grid-action-btn wishlist-btn ' + (isProductInWishlist ? 'active' : '') + '" id="btn-grid-wishlist" onclick="toggleWishlist(' + productId + ', this)" title="Wishlist">';
                html += '      <i class="' + (isProductInWishlist ? 'fa-solid' : 'fa-regular') + ' fa-heart"></i>';
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

        // 4. Color
        var curColor = (variantData && variantData.color) ? variantData.color : selectedColor;
        if (curColor) {
            var formattedColor = curColor.charAt(0).toUpperCase() + curColor.slice(1);
            mergedSpecs.push({ name: 'Color', value: formattedColor });
            seenKeys['color'] = true;
        }

        // 5. Custom specifications
        var customSpecs = [];
        var vSpecs = variantData ? parseJsonSafe(variantData.specifications) : [];
        if (vSpecs && vSpecs.length > 0) {
            customSpecs = vSpecs;
        } else if (baseSpecifications && baseSpecifications.length > 0) {
            customSpecs = baseSpecifications;
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

    // 4. COLOR SELECTION
    window.selectColor = function(colorName, cardEl) {
        if (selectedColor === colorName) return;
        showVariantLoader();

        selectedColor = colorName;

        // Update label (Capitalized as in product_deatils_mobile.jpeg)
        var label = document.getElementById('selected-color-name');
        if (label) {
            label.textContent = colorName.charAt(0).toUpperCase() + colorName.slice(1);
        }

        // Update active class on cards
        var cards = document.querySelectorAll('.color-thumb-card');
        cards.forEach(function(c) { c.classList.remove('active'); });
        if (cardEl) {
            cardEl.classList.add('active');
            if (cardEl.scrollIntoView) {
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
            alert(multiAttrName + ' ' + sizeName + ' is currently out of stock for color ' + selectedColor + '. You can select another option or color.');
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

    // 6. UPDATE SIZE / STORAGE AVAILABILITY BASED ON SELECTED COLOR
    function updateSizeAvailability() {
        if (!hasSize) return;

        var chips = document.querySelectorAll('.variant-size-chip');
        var colorSizes = (colorMap[selectedColor] && colorMap[selectedColor].sizes) ? colorMap[selectedColor].sizes : {};

        var firstInStockSize = '';

        chips.forEach(function(ch) {
            var s = ch.getAttribute('data-size');
            var sizeData = colorSizes[s];
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

        // If currently selected size is out of stock for new color, auto-switch to first in-stock size
        var currentSizeData = colorSizes[selectedSize];
        if (currentSizeData && currentSizeData.stock <= 0 && firstInStockSize) {
            chips.forEach(function(ch) {
                if (ch.getAttribute('data-size') === firstInStockSize) {
                    selectSize(firstInStockSize, ch);
                }
            });
        }
    }

    // 7. SYNC VARIANT DETAILS (Price, SKU, Stock Status, Highlights, Specs)
    function syncCurrentVariant() {
        var variantData = null;
        if (hasVariants && colorMap[selectedColor] && colorMap[selectedColor].sizes && colorMap[selectedColor].sizes[selectedSize]) {
            variantData = colorMap[selectedColor].sizes[selectedSize];
        } else if (hasVariants && sizeVariantMap && sizeVariantMap[selectedSize]) {
            variantData = sizeVariantMap[selectedSize];
        } else if (hasVariants && colorMap[selectedColor] && colorMap[selectedColor].sizes) {
            var firstS = Object.keys(colorMap[selectedColor].sizes)[0];
            if (firstS) variantData = colorMap[selectedColor].sizes[firstS];
        }

        if (variantData) {
            selectedVariantId = variantData.variant_id;

            var regularP = (typeof variantData.price !== 'undefined' && variantData.price !== null) ? parseFloat(variantData.price) : regularPrice;
            var saleP = (typeof variantData.sale_price !== 'undefined' && variantData.sale_price !== null && variantData.sale_price !== '') ? parseFloat(variantData.sale_price) : null;
            var effectivePrice = (saleP !== null && saleP > 0) ? saleP : regularP;

            currentPrice = effectivePrice;
            regularPrice = regularP;

            // Price display
            var salePriceEl = document.getElementById('display-sale-price');
            if (salePriceEl) {
                salePriceEl.textContent = currencySymbol + effectivePrice.toFixed(2);
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
                    mrpEl.textContent = 'M.R.P.: ' + currencySymbol + regularP.toFixed(2);
                    mrpEl.style.display = 'inline';
                }
            } else {
                if (discountBadgeEl) discountBadgeEl.style.display = 'none';
                if (mrpEl) mrpEl.style.display = 'none';
            }

            var curQty = parseInt(document.getElementById('product-qty-input')?.value, 10) || 1;
            var atcPriceEl = document.getElementById('atc-btn-price');
            if (atcPriceEl) {
                atcPriceEl.textContent = currencySymbol + (effectivePrice * curQty).toFixed(2);
            }

            // SKU
            var skuEl = document.getElementById('display-sku');
            if (skuEl && variantData.sku) {
                skuEl.textContent = variantData.sku;
            }

            // Stock status
            var stockEl = document.getElementById('display-stock-status');
            var atcBtn = document.getElementById('main-btn-atc');
            if (stockEl) {
                if (variantData.stock > 0) {
                    stockEl.textContent = 'In Stock (' + variantData.stock + ' available)';
                    stockEl.className = 'text-caption-1 mb-0 fw-bold text-success';
                    if (atcBtn) atcBtn.disabled = false;
                } else {
                    stockEl.textContent = 'Out of Stock';
                    stockEl.className = 'text-caption-1 mb-0 fw-bold text-danger';
                    if (atcBtn) atcBtn.disabled = true;
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
                atcPriceEl.textContent = currencySymbol + (unitPrice * val).toFixed(2);
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
        .then(function() {
            window.location.href = '<?= site_url("checkout"); ?>';
        })
        .catch(function() {
            window.location.href = '<?= site_url("checkout"); ?>';
        });
    };

    // 10. WISHLIST TOGGLE AJAX
    window.toggleWishlist = function(pId, btnEl) {
        fetch('<?= site_url("wishlist/toggle"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'product_id=' + pId
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.status === 'success' || data.success) {
                isProductInWishlist = !isProductInWishlist;
                // Sync all wishlist buttons across desktop and mobile
                var allWishlistBtns = document.querySelectorAll('.wishlist-btn, #btn-grid-wishlist, #btn-mobile-wishlist, .box-icon.wishlist');
                allWishlistBtns.forEach(function(b) {
                    if (isProductInWishlist) {
                        b.classList.add('active');
                        var icon = b.querySelector('i');
                        if (icon) {
                            icon.className = 'fa-solid fa-heart text-danger';
                        }
                    } else {
                        b.classList.remove('active');
                        var icon = b.querySelector('i');
                        if (icon) {
                            icon.className = 'fa-regular fa-heart';
                        }
                    }
                });
            } else if (data.redirect) {
                window.location.href = data.redirect;
            }
        })
        .catch(function(err) {
            console.error('Wishlist toggle error:', err);
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
            display.textContent = currencySymbol + total.toFixed(2);
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
        var tabTitle = document.querySelector('.widget-tabs.style-1 .widget-menu-tab .item-title:nth-child(2)');
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

// Update Swiper sliders when switching tabs
document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined') {
        jQuery('a[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
            document.querySelectorAll('.tf-sw-latest, .tf-sw-recent').forEach(function(el) {
                if (el.swiper) {
                    el.swiper.update();
                }
            });
        });
    }
});
</script>
