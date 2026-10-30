<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$bazaro = isset($tpl_assets) ? rtrim($tpl_assets, '/') . '/' : base_url('assets/templates/bazaro/');
$currency_symbol = isset($currency_symbol) ? $currency_symbol : '₹';
$elt_qs = empty($is_user_project) ? '?preview_tpl=electronics' : '';

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
$regular_price = (float) ($product['price'] ?? 89.00);
$sale_price = !empty($product['sale_price']) ? (float) $product['sale_price'] : null;
$current_price = $sale_price ?: $regular_price;
$discount_percent = ($regular_price > 0 && $sale_price && $sale_price < $regular_price)
    ? round((($regular_price - $sale_price) / $regular_price) * 100)
    : 0;

// Dynamic Reviews & Rating Calculation
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
    foreach ($product['attributes'] as $slug => $attr_info) {
        if ($slug === 'color') continue;
        if (!empty($attr_info['values']) && (!empty($attr_info['type']) && $attr_info['type'] === 'multiple_select')) {
            $multi_attr      = $attr_info;
            $multi_attr_slug = $slug;
            $multi_attr_name = $attr_info['name'];
            break;
        }
    }
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
$initial_sku = $product['sku'] ?? 'MOD-W-001-BG-S';

if ($has_color && !empty($product['attributes']['color']['values'])) {
    foreach ($product['attributes']['color']['values'] as $idx => $c_val) {
        $c_name = $c_val['value'];
        if ($idx === 0) {
            $initial_color = $c_name;
        }

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
            $c_photo = $product['main_image'] ?? 'products/womens/women-1.jpg';
        }

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

if (empty($initial_size) && $has_size && !empty($multi_attr['values'])) {
    $initial_size = $multi_attr['values'][0]['value'];
}
if ($has_variants && empty($initial_variant_id) && !empty($product['variants'])) {
    $initial_variant_id = (int) $product['variants'][0]['id'];
    $initial_sku        = $product['variants'][0]['sku'];
}

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
            'highlights'     => !empty($first_v['highlights']) ? (is_array($first_v['highlights']) ? $first_v['highlights'] : (json_decode($first_v['highlights'], true) ?: [])) : [],
            'specifications' => !empty($first_v['specifications']) ? (is_array($first_v['specifications']) ? $first_v['specifications'] : (json_decode($first_v['specifications'], true) ?: [])) : []
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
   PRODUCT DETAILS RIGHT COLUMN STYLES (Variants, Accordions, Action Buttons)
   ========================================================================== */
.tf-product-info-wrap {
    font-family: inherit;
}
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

/* Pricing Header Section */
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

/* Color Badge Selection */
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
.color-slider-arrow.prev { left: -4px; }
.color-slider-arrow.next { right: -4px; }

/* Size Chips */
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

/* Brand Link */
.brand-store-link {
    font-size: 14px;
    color: #007185;
    text-decoration: none;
    transition: color 0.2s ease;
}
.brand-store-link:hover {
    color: #c7511f;
    text-decoration: underline !important;
}

/* Typography & Info List Scoped Rules */
.text-caption-1 {
    font-size: 14px;
    line-height: 22px;
}
.text-btn-uppercase {
    font-size: 12px;
    line-height: 20px;
    text-transform: uppercase;
    letter-spacing: 0.1em;
}
.tf-product-info-list .tf-product-info-heading {
    padding-bottom: 20px;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 20px;
}
.tf-product-info-list .tf-product-info-name {
    margin-bottom: 20px;
}
.tf-product-info-list .tf-product-info-name .name {
    margin-bottom: 12px;
    color: #181818;
}
.tf-product-info-list .tf-product-info-name > .sub {
    display: flex;
    align-items: center;
    gap: 10px 16px;
    flex-wrap: wrap;
}
.tf-product-info-list .tf-product-info-rate {
    display: flex;
    gap: 4px;
    align-items: center;
}
.tf-product-info-list .tf-product-info-desc {
    display: flex;
    gap: 8px;
    flex-direction: column;
}
.tf-product-info-list .tf-product-info-choose-option {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.tf-product-info-list .tf-product-info-help {
    padding-bottom: 19px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.tf-product-info-list .tf-product-info-help .tf-product-info-extra-link {
    display: flex;
    gap: 15px 34px;
    align-items: center;
    flex-wrap: wrap;
}
.tf-product-info-list .tf-product-info-help .tf-product-info-extra-link .tf-product-extra-icon {
    display: flex;
    gap: 4px;
    align-items: center;
    position: relative;
    color: #181818;
}
.tf-product-info-list .tf-product-info-help .tf-product-info-extra-link .tf-product-extra-icon:hover {
    color: #e43131;
}
.tf-product-info-list .tf-product-info-help .tf-product-info-extra-link .tf-product-extra-icon .icon {
    font-size: 20px;
    line-height: 20px;
}
.tf-product-info-list .tf-product-info-help .tf-product-info-extra-link .tf-product-extra-icon:not(:last-child)::after {
    position: absolute;
    content: "";
    width: 1px;
    height: 20px;
    right: -18px;
    top: 1px;
    background-color: #e5e7eb;
}
.tf-product-info-list .tf-product-info-help .tf-product-info-return,
.tf-product-info-list .tf-product-info-help .tf-product-info-time {
    display: flex;
    gap: 4px;
    align-items: center;
}
.tf-product-info-list .tf-product-info-help .tf-product-info-return .icon,
.tf-product-info-list .tf-product-info-help .tf-product-info-time .icon {
    font-size: 20px;
    line-height: 20px;
    color: #181818;
}
.tf-product-info-list .tf-product-info-help .tf-product-info-return p,
.tf-product-info-list .tf-product-info-help .tf-product-info-time p {
    color: #565959;
}
.tf-product-info-list .tf-product-info-help .tf-product-info-return p span,
.tf-product-info-list .tf-product-info-help .tf-product-info-time p span {
    color: #181818;
    font-weight: 600;
}
.tf-product-info-list ul.tf-product-info-sku {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding-bottom: 19px;
    border-bottom: 1px solid #e5e7eb;
}
.tf-product-info-list ul.tf-product-info-sku li {
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Action Buttons (Add to cart & Buy now) */
.btn-style-2 {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 14px 15px;
    border-radius: 999px;
    background-color: #181818;
    color: #ffffff;
    letter-spacing: 0.1em;
    border: 1px solid #181818 !important;
    transition: all 0.25s ease;
}
.btn-style-2:hover {
    background-color: #e43131;
    color: #ffffff;
    border-color: #e43131 !important;
}
.btn-style-3 {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 14px 15px;
    border-radius: 999px;
    background-color: #e43131;
    color: #ffffff;
    letter-spacing: 0.1em;
    border: 1px solid #e43131;
    transition: all 0.25s ease;
}
.btn-style-3:hover {
    background-color: #181818;
    color: #ffffff;
    border-color: #181818;
}

/* Mobile Action Buttons */
@media (max-width: 767.98px) {
    .mobile-bottom-action-bar-attached {
        display: flex;
        gap: 10px;
        width: 100%;
        padding: 10px 0 4px 0;
        margin-top: 10px;
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
}
</style>


   <!-- Question Modal (#exampleModalToggle4) -->
   <div class="aq-login-modal-style">
      <div class="modal fade" id="exampleModalToggle4" role="dialog" aria-hidden="true" aria-labelledby="exampleModalToggle4" tabindex="-1">
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               <div class="aq-login-wrapper">
                  <div class="aq-login-top mb-30">
                     <h3 class="aq-login-title">Ask Question</h3>
                  </div>
                  <div class="aq-login-option">
                     <div class="aq-login-input-wrapper">
                        <div class="aq-login-input-box">
                           <div class="aq-login-input-title">
                              <label class="aq-form-label">Your Question <span>*</span></label>
                           </div>
                           <div class="aq-login-input">
                              <textarea class="aq-form-input" placeholder="Your Question"></textarea>
                           </div>
                        </div>
                     </div>
                     <div class="aq-login-bottom-wrap text-center mb-20">
                        <button type="button" class="aq-login-btn w-100 mb-10" data-bs-dismiss="modal" onclick="if(typeof showEltToast==='function')showEltToast('Your question has been submitted!');">Submit</button>
                        <button type="button" class="aq-login-btn btn-transparent w-100 mb-10" data-bs-dismiss="modal" aria-label="Close">Back</button>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>

   <!-- Return Policy Modal (#exampleModalToggle5) -->
   <div class="aq-return-modal-style">
      <div class="modal fade" id="exampleModalToggle5" role="dialog" aria-hidden="true" aria-labelledby="exampleModalToggle5" tabindex="-1">
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               <div class="product-details-tab-item">
                  <div class="row">
                     <div class="col-xl-12">
                        <div class="product-details-policy">
                           <h4 class="product-details-policy-title">Shipping policy</h4>
                           <p>
                              We strive to provide fast and reliable shipping to our customers. Here's everything you need to know about our shipping process:
                           </p>
                           <ul class="product-details-policy-list mb-30">
                              <li>Dispatch: Within 24 Hours</li>
                              <li>Cash on delivery might be available</li>
                              <li>Easy 30 days returns and exchanges</li>
                           </ul>
                           <h4 class="product-details-policy-title">Returns policy</h4>
                           <p>
                              We want you to be completely satisfied with your purchase from our website. If for any reason you are not entirely happy with your order, we're here to help. <br>
                           </p>
                           <ul class="product-details-policy-list mb-30">
                              <li>All orders shipped with UPS Express.</li>
                              <li>Always free shipping for orders over US $250.</li>
                              <li>All orders are shipped with a UPS tracking number.</li>
                           </ul>
                           <h4 class="product-details-policy-title">Help</h4>
                           <p>Give us a shout if you have any other questions and/or concerns.</p>
                           <ul class="product-details-policy-info mb-30">
                              <li><a href="tel:0123456789">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                 <path d="M0.770285 10.7567L4.53911 9.06793L4.54951 9.06313C4.74517 8.97944 4.9586 8.94586 5.1705 8.96541C5.3824 8.98497 5.58608 9.05705 5.76312 9.17513C5.78396 9.18889 5.804 9.20385 5.82312 9.21993L7.77033 10.8799C9.00394 10.2807 10.2776 9.01673 10.8768 7.79911L9.21434 5.82229C9.19834 5.80229 9.18314 5.78229 9.16874 5.76069C9.0532 5.58413 8.9831 5.38175 8.96467 5.17155C8.94625 4.96135 8.98008 4.74985 9.06314 4.55588V4.54628L10.7568 0.771045C10.8666 0.517655 11.0554 0.306573 11.295 0.169311C11.5346 0.0320479 11.8122 -0.0240334 12.0864 0.0094382C13.1704 0.152085 14.1654 0.684459 14.8856 1.50713C15.6058 2.3298 16.002 3.3865 16 4.47988C16 10.8319 10.832 16 4.47991 16C3.38654 16.002 2.32983 15.6058 1.50717 14.8856C0.684499 14.1654 0.152126 13.1704 0.00947881 12.0864C-0.0240543 11.8123 0.0319102 11.5348 0.169022 11.2952C0.306134 11.0556 0.517044 10.8667 0.770285 10.7567ZM4.47991 14.72C7.19484 14.717 9.79772 13.6372 11.7175 11.7174C13.6372 9.7977 14.717 7.19481 14.72 4.47988C14.723 3.69867 14.4415 2.94306 13.9282 2.35419C13.4148 1.76531 12.7047 1.38342 11.9304 1.27985C11.9307 1.28304 11.9307 1.28626 11.9304 1.28945L10.2504 5.04949L11.904 7.02871C11.9207 7.04802 11.936 7.06861 11.9496 7.09031C12.07 7.27504 12.1406 7.48775 12.1546 7.7078C12.1686 7.92786 12.1255 8.1478 12.0296 8.34632C11.3048 9.82873 9.81115 11.3111 8.31274 12.0352C8.11276 12.1302 7.89155 12.1718 7.6707 12.1558C7.44985 12.1398 7.23693 12.0668 7.05273 11.944C7.03219 11.9301 7.01243 11.9152 6.99353 11.8992L5.04872 10.2399L1.28869 11.924C1.28869 11.924 1.28229 11.924 1.27989 11.924C1.3822 12.6994 1.76351 13.411 2.35249 13.9256C2.94148 14.4402 3.69778 14.7226 4.47991 14.72Z" fill="#343330"/>
                                 </svg> (303) 555-0105
                              </a></li>
                              <li><a href="mailto:Global@store.com">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="16" height="15" viewBox="0 0 16 15" fill="none">
                                 <path d="M15.7262 5.10495L8.34154 0.105091C8.24041 0.0365682 8.12157 0 8 0C7.87843 0 7.75959 0.0365682 7.65846 0.105091L0.273846 5.10495C0.189547 5.16208 0.120442 5.23946 0.0726691 5.33022C0.0248963 5.42099 -6.56915e-05 5.52233 1.29834e-07 5.62525V13.75C1.29834e-07 14.0815 0.12967 14.3995 0.360484 14.6339C0.591298 14.8683 0.904349 15 1.23077 15H14.7692C15.0957 15 15.4087 14.8683 15.6395 14.6339C15.8703 14.3995 16 14.0815 16 13.75V5.62525C16.0001 5.52233 15.9751 5.42099 15.9273 5.33022C15.8796 5.23946 15.8105 5.16208 15.7262 5.10495ZM8 1.37615L14.3008 5.64244L9.14692 9.37515H6.85462L1.70077 5.64244L8 1.37615ZM1.23077 13.75V6.8385L6.3 10.5103C6.40437 10.586 6.52943 10.6267 6.65769 10.6267H9.34231C9.47057 10.6267 9.59563 10.586 9.7 10.5103L14.7692 6.8385V13.75H1.23077Z" fill="#343330"/>
                                 </svg>Global@store.com
                              </a></li>
                           </ul>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>

   <!-- Share Modal (#exampleModalToggle6) -->
   <div class="aq-share-modal-style">
      <div class="modal fade" id="exampleModalToggle6" role="dialog" aria-hidden="true" aria-labelledby="exampleModalToggle6" tabindex="-1">
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               <div class="aq-share-wrapper">
                  <div class="aq-share-top mb-15">
                     <h3 class="aq-login-title">Share link</h3>
                  </div>
                  <div class="aq-share-container">
                     <div class="aq-share-link-wrapper">
                        <input type="text" class="aq-share-input" value="https://html.bajaro.shop/shofy-prv..." readonly>
                        <button type="button" class="aq-share-copy-btn">
                           <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                              <path d="M3.69994 12.7001H2.79996C2.32258 12.7001 1.86476 12.5104 1.5272 12.1728C1.18964 11.8353 1 11.3774 1 10.9001V2.80001C1 2.32262 1.18964 1.86478 1.5272 1.52721C1.86476 1.18964 2.32258 1 2.79996 1H10.8998C11.3772 1 11.835 1.18964 12.1726 1.52721C12.5101 1.86478 12.6998 2.32262 12.6998 2.80001V3.70001M9.10074 7.30005H17.2006C18.1947 7.30005 19.0005 8.10594 19.0005 9.10006V17.2001C19.0005 18.1942 18.1947 19.0001 17.2006 19.0001H9.10074C8.10665 19.0001 7.30078 18.1942 7.30078 17.2001V9.10006C7.30078 8.10594 8.10665 7.30005 9.10074 7.30005Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                           </svg>
                        </button>
                     </div>
                     <div class="aq-share-socials">
                        <a href="#" class="aq-share-social-item">
                           <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                              <path d="M8.775 0C7.03947 0 5.34292 0.514645 3.89987 1.47885C2.45683 2.44306 1.33212 3.81353 0.667961 5.41695C0.00380206 7.02037 -0.169972 8.78473 0.168613 10.4869C0.507198 12.1891 1.34294 13.7527 2.57014 14.9799C3.79735 16.2071 5.3609 17.0428 7.06309 17.3814C8.76527 17.72 10.5296 17.5462 12.133 16.882C13.7365 16.2179 15.1069 15.0932 16.0711 13.6501C17.0354 12.2071 17.55 10.5105 17.55 8.775C17.5475 6.44848 16.6222 4.21795 14.9772 2.57285C13.3321 0.927752 11.1015 0.00245685 8.775 0ZM9.45 16.1688V10.8H11.475C11.654 10.8 11.8257 10.7289 11.9523 10.6023C12.0789 10.4757 12.15 10.304 12.15 10.125C12.15 9.94598 12.0789 9.77429 11.9523 9.6477C11.8257 9.52111 11.654 9.45 11.475 9.45H9.45V7.425C9.45 7.06696 9.59224 6.72358 9.84541 6.47041C10.0986 6.21723 10.442 6.075 10.8 6.075H12.15C12.329 6.075 12.5007 6.00388 12.6273 5.8773C12.7539 5.75071 12.825 5.57902 12.825 5.4C12.825 5.22098 12.7539 5.04929 12.6273 4.9227C12.5007 4.79612 12.329 4.725 12.15 4.725H10.8C10.0839 4.725 9.39716 5.00946 8.89082 5.51581C8.38447 6.02216 8.1 6.70891 8.1 7.425V9.45H6.075C5.89598 9.45 5.72429 9.52111 5.59771 9.6477C5.47112 9.77429 5.4 9.94598 5.4 10.125C5.4 10.304 5.47112 10.4757 5.59771 10.6023C5.72429 10.7289 5.89598 10.8 6.075 10.8H8.1V16.1688C6.19717 15.9951 4.43455 15.0945 3.17878 13.6544C1.923 12.2142 1.27075 10.3454 1.35769 8.43668C1.44464 6.52791 2.26408 4.72614 3.64557 3.40614C5.02707 2.08614 6.86426 1.34953 8.775 1.34953C10.6857 1.34953 12.5229 2.08614 13.9044 3.40614C15.2859 4.72614 16.1054 6.52791 16.1923 8.43668C16.2793 10.3454 15.627 12.2142 14.3712 13.6544C13.1155 15.0945 11.3528 15.9951 9.45 16.1688Z" fill="currentColor"/>
                           </svg>
                        </a>
                        <a href="#" class="aq-share-social-item">
                           <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                              <path d="M15.1002 14.8859L10.6987 7.96852L15.0419 3.19078C15.1401 3.08006 15.1907 2.93508 15.1828 2.78727C15.1748 2.63947 15.1089 2.50076 14.9994 2.40121C14.8899 2.30166 14.7455 2.2493 14.5976 2.25548C14.4497 2.26166 14.3102 2.32588 14.2094 2.43422L10.0722 6.98484L7.22524 2.51086C7.17447 2.43095 7.10436 2.36515 7.02139 2.31955C6.93843 2.27395 6.8453 2.25003 6.75063 2.25H3.37563C3.27477 2.24995 3.17576 2.27702 3.08896 2.32837C3.00215 2.37972 2.93075 2.45347 2.88223 2.54188C2.83371 2.6303 2.80985 2.73014 2.81316 2.83094C2.81646 2.93174 2.84681 3.0298 2.90102 3.11484L7.30258 10.0315L2.95938 14.8127C2.90866 14.8672 2.86926 14.9312 2.84346 15.001C2.81766 15.0709 2.80598 15.1451 2.80908 15.2195C2.81219 15.2939 2.83003 15.3669 2.86156 15.4343C2.8931 15.5017 2.9377 15.5622 2.99279 15.6123C3.04787 15.6623 3.11235 15.701 3.18247 15.7259C3.2526 15.7509 3.32698 15.7617 3.40131 15.7577C3.47564 15.7537 3.54844 15.735 3.61548 15.7026C3.68252 15.6703 3.74248 15.625 3.79188 15.5693L7.92907 11.0187L10.776 15.4927C10.8272 15.5719 10.8975 15.637 10.9804 15.682C11.0634 15.727 11.1563 15.7503 11.2506 15.75H14.6256C14.7264 15.75 14.8253 15.7229 14.912 15.6716C14.9987 15.6202 15.07 15.5466 15.1185 15.4583C15.167 15.37 15.1909 15.2703 15.1877 15.1696C15.1845 15.0689 15.1543 14.9709 15.1002 14.8859ZM11.5593 14.625L4.40008 3.375H6.43915L13.6012 14.625H11.5593Z" fill="currentColor"/>
                           </svg>
                        </a>
                        <a href="#" class="aq-share-social-item">
                           <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                              <path d="M9 5.625C8.33249 5.625 7.67997 5.82294 7.12495 6.19379C6.56993 6.56464 6.13735 7.09174 5.88191 7.70844C5.62646 8.32514 5.55962 9.00374 5.68985 9.65843C5.82008 10.3131 6.14151 10.9145 6.61351 11.3865C7.08552 11.8585 7.68688 12.1799 8.34157 12.3102C8.99626 12.4404 9.67486 12.3735 10.2916 12.1181C10.9083 11.8626 11.4354 11.4301 11.8062 10.875C12.1771 10.32 12.375 9.66751 12.375 9C12.3741 8.10518 12.0182 7.24728 11.3855 6.61454C10.7527 5.98181 9.89482 5.62593 9 5.625ZM9 11.25C8.55499 11.25 8.11998 11.118 7.74997 10.8708C7.37996 10.6236 7.09157 10.2722 6.92127 9.86104C6.75097 9.4499 6.70642 8.9975 6.79323 8.56105C6.88005 8.12459 7.09434 7.72368 7.40901 7.40901C7.72368 7.09434 8.12459 6.88005 8.56105 6.79323C8.9975 6.70642 9.4499 6.75097 9.86104 6.92127C10.2722 7.09157 10.6236 7.37996 10.8708 7.74997C11.118 8.11998 11.25 8.55499 11.25 9C11.25 9.59674 11.0129 10.169 10.591 10.591C10.169 11.0129 9.59674 11.25 9 11.25ZM12.375 1.6875H5.625C4.58105 1.68862 3.58018 2.10382 2.842 2.842C2.10382 3.58018 1.68862 4.58105 1.6875 5.625V12.375C1.68862 13.4189 2.10382 14.4198 2.842 15.158C3.58018 15.8962 4.58105 16.3114 5.625 16.3125H12.375C13.4189 16.3114 14.4198 15.8962 15.158 15.158C15.8962 14.4198 16.3114 13.4189 16.3125 12.375V5.625C16.3114 4.58105 15.8962 3.58018 15.158 2.842C14.4198 2.10382 13.4189 1.68862 12.375 1.6875ZM15.1875 12.375C15.1875 13.1209 14.8912 13.8363 14.3637 14.3637C13.8363 14.8912 13.1209 15.1875 12.375 15.1875H5.625C4.87908 15.1875 4.16371 14.8912 3.63626 14.3637C3.10882 13.8363 2.8125 13.1209 2.8125 12.375V5.625C2.8125 4.87908 3.10882 4.16371 3.63626 3.63626C4.16371 3.10882 4.87908 2.8125 5.625 2.8125H12.375C13.1209 2.8125 13.8363 3.10882 14.3637 3.63626C14.8912 4.16371 15.1875 4.87908 15.1875 5.625V12.375ZM13.5 5.34375C13.5 5.51063 13.4505 5.67376 13.3578 5.81251C13.2651 5.95127 13.1333 6.05941 12.9791 6.12327C12.825 6.18713 12.6553 6.20384 12.4916 6.17129C12.328 6.13873 12.1776 6.05837 12.0596 5.94037C11.9416 5.82237 11.8613 5.67203 11.8287 5.50836C11.7962 5.34469 11.8129 5.17504 11.8767 5.02086C11.9406 4.86669 12.0487 4.73491 12.1875 4.6422C12.3262 4.54948 12.4894 4.5 12.6562 4.5C12.88 4.5 13.0946 4.58889 13.2529 4.74713C13.4111 4.90536 13.5 5.11997 13.5 5.34375Z" fill="currentColor"/>
                           </svg>
                        </a>
                        <a href="#" class="aq-share-social-item">
                           <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                              <path d="M17.3254 8.66261C17.3254 10.4083 16.7144 12.0039 15.606 13.1571C14.5712 14.2314 13.1682 14.8501 11.7566 14.8501C10.3737 14.8501 9.45025 14.3969 8.86167 13.922L8.0279 17.4659C7.99613 17.6018 7.91932 17.7231 7.80993 17.8098C7.70054 17.8966 7.56502 17.9439 7.42539 17.9439C7.3775 17.9438 7.32976 17.9384 7.28308 17.9276C7.20367 17.9094 7.12864 17.8757 7.06229 17.8284C6.99595 17.7811 6.93961 17.7211 6.89649 17.652C6.85337 17.5829 6.82433 17.5059 6.81104 17.4255C6.79774 17.3451 6.80045 17.2629 6.81902 17.1836L9.29402 6.66482C9.33156 6.50502 9.43103 6.36669 9.57057 6.28024C9.71011 6.19379 9.87827 6.16631 10.0381 6.20385C10.1979 6.24139 10.3362 6.34087 10.4226 6.4804C10.5091 6.61994 10.5366 6.7881 10.499 6.9479L9.19192 12.5012C9.43633 12.8392 10.1556 13.6126 11.7566 13.6126C13.8859 13.6126 16.0879 11.761 16.0879 8.66261C16.0876 7.80863 15.8908 6.96616 15.5129 6.20036C15.135 5.43456 14.586 4.76593 13.9084 4.24618C13.2308 3.72643 12.4427 3.36949 11.6051 3.20295C10.7676 3.03641 9.90289 3.06473 9.07801 3.28573C8.25312 3.50673 7.49011 3.91449 6.84798 4.47747C6.20584 5.04045 5.70179 5.74358 5.3748 6.53247C5.04781 7.32137 4.90663 8.1749 4.96219 9.02708C5.01775 9.87925 5.26855 10.7072 5.69521 11.447C5.77218 11.5887 5.79076 11.7549 5.747 11.9101C5.70324 12.0654 5.6006 12.1974 5.46094 12.278C5.32128 12.3587 5.15565 12.3817 4.99932 12.342C4.84299 12.3023 4.70832 12.2032 4.624 12.0657C4.10226 11.1616 3.79548 10.1496 3.72739 9.10791C3.65929 8.06626 3.83171 7.0229 4.23129 6.05853C4.63088 5.09416 5.24694 4.23463 6.03181 3.54641C6.81668 2.85818 7.74934 2.35972 8.75765 2.08956C9.76596 1.8194 10.8229 1.78479 11.8467 1.9884C12.8706 2.19201 13.8338 2.62839 14.662 3.26379C15.4903 3.89919 16.1612 4.71657 16.6231 5.65273C17.0849 6.58889 17.3252 7.61873 17.3254 8.66261Z" fill="currentColor"/>
                           </svg>
                        </a>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>

   <!-- Size Chart Modal (#exampleModalToggle7) -->
   <div class="aq-size-modal-style">
      <div class="modal fade" id="exampleModalToggle7" role="dialog" aria-hidden="true" aria-labelledby="exampleModalToggle7" tabindex="-1">
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               <div class="aq-size-modal-wrap">
                  <img src="<?= $bazaro; ?>img/size-chart/woman-size.jpg" alt="">
               </div>
            </div>
         </div>
      </div>
   </div>

   <main>

      <!-- breadcrumb area start -->
      <div class="pd-breadcrumb-area">
         <div class="container">
            <div class="row">
               <div class="col-xl-8">
                  <div class="pd-breadcrumb-content">
                     <div class="pd-breadcrumb-list">
                        <span><a href="<?= site_url('home' . $elt_qs); ?>">home</a></span>
                        <span>/</span>
                        <span><a href="<?= site_url('shop' . $elt_qs); ?>">shop</a></span>
                        <span>/</span>
                        <span><a href="#"><?= html_escape($product['title'] ?? 'Product Details'); ?></a></span>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- breadcrumb area end -->

      <!-- product details area start -->
      <?php
      $elt_gallery_urls = [];
      foreach ($all_images as $raw_img) {
          $clean_g = ltrim((string)$raw_img, '/');
          if (strpos($clean_g, 'http') === 0) {
              $elt_gallery_urls[] = $clean_g;
          } elseif (strpos($clean_g, 'assets/') === 0) {
              $elt_gallery_urls[] = base_url($clean_g);
          } else {
              $elt_gallery_urls[] = base_url('assets/images/' . $clean_g);
          }
      }
      while (count($elt_gallery_urls) < 4) {
          $elt_gallery_urls[] = $elt_gallery_urls[0] ?? base_url('assets/images/products/electronics/elec-p-1.jpg');
      }
      ?>
      <div class="aq-product-details-thumbnails-style pb-120">
         <div class="container">
            <div class="row">
               <div class="col-lg-6 order-lg-1 order-2">
                  <div class="product-details-slider-wrap">
                     <div class="product-slider-main-wrap p-relative mb-10">
                        <div class="product-slider-main slider-for p-relative">
                           <?php foreach ($elt_gallery_urls as $g_url): ?>
                           <div class="product-slider-for-img">
                              <a class="popup-image" href="<?= $g_url; ?>">
                                 <img src="<?= $g_url; ?>" alt="<?= html_escape($product['title'] ?? ''); ?>">
                              </a>
                           </div>
                           <?php endforeach; ?>
                        </div>
                        <div class="product-slider-arrow"></div>
                     </div>
                     <div class="product-slider-nav-2 slider-nav-2">
                        <?php foreach ($elt_gallery_urls as $g_url): ?>
                        <div class="product-slider-nav-img">
                           <img src="<?= $g_url; ?>" alt="<?= html_escape($product['title'] ?? ''); ?>">
                        </div>
                        <?php endforeach; ?>
                     </div>
                  </div>
               </div>
               <div class="col-lg-6 order-lg-2 order-1">
                  <div class="tf-product-info-wrap position-relative ml-30">
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
                        
                        <!-- ALL PRODUCT VARIANTS (Color, Size / Storage / Option) - Top of Product Name -->
                        <?php if (($has_color && !empty($color_map)) || ($has_size && !empty($multi_attr['values']))): ?>
                           <div class="tf-product-info-variants mb-2">
                              <!-- 1. COLOR SELECTION WITH SLIDER & PRODUCT PHOTOS -->
                              <?php if ($has_color && !empty($color_map)): ?>
                                 <div class="variant-picker-item mb-2">
                                    <div class="color-badge-heading mb-2">
                                       <span class="color-badge-title">Selected Color:</span>
                                       <span class="color-badge-name" id="selected-color-name"><?= ucwords(html_escape($initial_color)); ?></span>
                                    </div>

                                    <!-- Horizontal color slider with navigation arrows -->
                                    <div class="color-variant-container">
                                       <button type="button" class="color-slider-arrow prev" id="color-arrow-prev" onclick="slideColorTrack(-1)" aria-label="Previous Color" style="display: none;">
                                          <i class="fa-solid fa-chevron-left"></i>
                                       </button>
                                       
                                       <div class="color-slider-track" id="color-slider-track" onscroll="updateColorSliderArrows()">
                                          <?php foreach ($color_map as $c_name => $c_info): 
                                             $is_active_color = ($c_name === $initial_color);
                                             $c_img = !empty($c_info['image']) ? $c_info['image'] : ($product['main_image'] ?? 'products/womens/women-1.jpg');
                                          ?>
                                             <div class="color-thumb-card <?= $is_active_color ? 'active' : ''; ?>" 
                                                  data-color="<?= html_escape($c_name); ?>"
                                                  data-image="<?= base_url('assets/images/' . $c_img); ?>"
                                                  onclick="selectColor('<?= html_escape($c_name); ?>', this)"
                                                  title="<?= html_escape($c_name); ?>">
                                                <img src="<?= base_url('assets/images/' . $c_img); ?>" alt="<?= html_escape($c_name); ?>" onerror="this.src='<?= base_url('assets/images/' . ($product['main_image'] ?? 'products/womens/women-1.jpg')); ?>'">
                                             </div>
                                          <?php endforeach; ?>
                                       </div>

                                       <button type="button" class="color-slider-arrow next" id="color-arrow-next" onclick="slideColorTrack(1)" aria-label="Next Color" style="display: none;">
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
                                          <a href="#exampleModalToggle7" data-bs-toggle="modal" class="text-decoration-none fw-semibold" style="color: #2874f0 !important; font-size: 14px;">
                                             Size Chart <i class="fa-solid fa-chevron-right ms-1" style="font-size: 11px;"></i>
                                          </a>
                                       <?php endif; ?>
                                    </div>

                                    <div class="d-flex flex-wrap gap-2" id="size-chips-wrapper">
                                       <?php 
                                       foreach ($multi_attr['values'] as $s_val): 
                                          $s_name = $s_val['value'];
                                          $is_active_size = ($s_name === $initial_size);
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
                              
                              <!-- Brand Store Link -->
                              <?php if (!empty($product['brand_name']) || !empty($product['category_name'])): ?>
                                 <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                    <?php if (!empty($product['brand_name'])): ?>
                                       <a href="<?= site_url('shop?brand=' . ($product['brand_slug'] ?? '') . (empty($is_user_project) ? '&preview_tpl=electronics' : '')); ?>" class="brand-store-link fw-semibold">
                                          Visit the <?= html_escape($product['brand_name']); ?> Store
                                       </a>
                                       <?php if (!empty($product['category_name'])): ?>
                                          <span class="text-secondary" style="font-size: 11px;">&bull;</span>
                                       <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if (!empty($product['category_name'])): ?>
                                       <a href="<?= site_url('shop/' . ($product['category_slug'] ?? '') . $elt_qs); ?>" class="text-secondary text-decoration-none small"><?= html_escape($product['category_name']); ?></a>
                                    <?php endif; ?>
                                 </div>
                              <?php endif; ?>

                              <h3 class="name fw-bold mb-2" style="font-size: 26px !important; line-height: 1.35;"><?= html_escape($product['title'] ?? 'V-neck Linen Blend Summer Dress'); ?></h3>

                              <!-- Rating & Review Count -->
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
                                    <a href="#navPresentationTab" class="text-secondary small text-decoration-none" onclick="activateReviewTab()">See reviews</a>
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
                                    <a href="#navPresentationTab" class="text-secondary small text-decoration-none" onclick="activateReviewTab()">See reviews</a>
                                 <?php endif; ?>
                              </div>

                           </div>

                           <!-- Pricing Block -->
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
                              
                              <?php if (!empty($store_settings['tax_inclusive']) || !isset($store_settings['tax_inclusive'])): ?>
                                 <div class="product-tax-note">Inclusive of all taxes</div>
                              <?php elseif (!empty($store_settings['tax_enabled'])): ?>
                                 <div class="product-tax-note text-secondary" style="font-size: 12px;">Exclusive of taxes (calculated at checkout)</div>
                              <?php endif; ?>

                              <?php if (!empty($product['short_description'])): ?>
                                 <p class="text-secondary mb-2"><?= nl2br(html_escape($product['short_description'])); ?></p>
                              <?php endif; ?>

                           </div>
                        </div>

                        <!-- Product Options & Specifications Accordions -->
                        <div class="tf-product-info-choose-option mt-2">

                           <!-- PRODUCT HIGHLIGHTS & SPECIFICATIONS ACCORDIONS -->
                           <?php
                           $merged_specs = [];
                           $seen_spec_keys = [];

                           $spec_brand = !empty($product['brand_name']) ? trim($product['brand_name']) : '';
                           if (!empty($spec_brand)) {
                              $merged_specs[] = ['name' => 'Brand', 'value' => $spec_brand];
                              $seen_spec_keys['brand'] = true;
                           }

                           $spec_cat = !empty($product['category_name']) ? trim($product['category_name']) : '';
                           if (!empty($spec_cat)) {
                              $merged_specs[] = ['name' => 'Category', 'value' => $spec_cat];
                              $seen_spec_keys['category'] = true;
                           }

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
                              $merged_specs[] = ['name' => 'Color', 'value' => implode(', ', array_map('ucfirst', array_unique($spec_colors)))];
                              $seen_spec_keys['color'] = true;
                           }

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

                           $product_highlights = !empty($product['highlights_decoded']) ? $product['highlights_decoded'] : [];
                           if (empty($product_highlights) && !empty($product['highlights'])) {
                              $product_highlights = is_array($product['highlights']) ? $product['highlights'] : (json_decode($product['highlights'], true) ?: []);
                           }
                           if (!empty($initial_variant['highlights'])) {
                              $v_hl = is_array($initial_variant['highlights']) ? $initial_variant['highlights'] : (json_decode($initial_variant['highlights'], true) ?: []);
                              if (!empty($v_hl)) {
                                 $product_highlights = $v_hl;
                              }
                           }

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
                              <div class="product-highlights-block mb-2" id="product-highlights-block" style="<?= empty($product_highlights) ? 'display: none;' : 'display: block;'; ?>">
                                 <div class="d-flex justify-content-between align-items-center cursor-pointer py-1 user-select-none" onclick="toggleProductHighlights()" style="cursor: pointer;">
                                    <h4 class="m-0" style="font-size: 22px; font-weight: 700; color: #212121; letter-spacing: -0.2px;">Product highlights</h4>
                                    <button type="button" class="btn-collapse-arrow d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #f1f3f6; border-radius: 8px; border: none; padding: 0; color: #212121;" aria-label="Toggle Product Highlights">
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
                                          <div class="spec-grid-item <?= $hl_is_full ? 'spec-full-width' : ''; ?>" style="border-bottom: 1px solid #f0f0f0; padding-bottom: 6px; <?= $hl_is_full ? 'grid-column: 1 / -1;' : ''; ?>">
                                             <div class="spec-item-key" style="font-size: 13px; color: #717478; margin-bottom: 2px; font-weight: 400;"><?= html_escape($hl_k); ?></div>
                                             <div class="spec-item-val" style="font-size: 14px; color: #212121; font-weight: 500; line-height: 1.3;"><?= html_escape($hl_v); ?></div>
                                          </div>
                                       <?php endforeach; ?>
                                    </div>
                                 </div>
                                 
                                 <div class="section-divider" style="border-bottom: 1px solid #f0f0f0; margin-top: 10px; margin-bottom: 10px;"></div>
                              </div>

                              <!-- Product Specifications (Collapsed by Default, 14 items limit) -->
                              <div class="product-specifications-block mb-2" id="product-specifications-block" style="<?= empty($merged_specs) ? 'display: none;' : 'display: block;'; ?>">
                                 <div class="d-flex justify-content-between align-items-center cursor-pointer py-1 user-select-none" onclick="toggleProductSpecifications()" style="cursor: pointer;">
                                    <h4 class="m-0" style="font-size: 22px; font-weight: 700; color: #212121; letter-spacing: -0.2px;">Specifications</h4>
                                    <button type="button" class="btn-collapse-arrow d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #f1f3f6; border-radius: 8px; border: none; padding: 0; color: #212121;" aria-label="Toggle Specifications">
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
                                          <div class="spec-grid-item <?= $is_extra ? 'spec-overflow-item' : ''; ?> <?= $is_full_width ? 'spec-full-width' : ''; ?>" style="border-bottom: 1px solid #f0f0f0; padding-bottom: 6px; <?= $is_full_width ? 'grid-column: 1 / -1;' : ''; ?> <?= $is_extra ? 'display: none;' : ''; ?>">
                                             <div class="spec-item-key" style="font-size: 13px; color: #717478; margin-bottom: 2px; font-weight: 400;"><?= html_escape($sp['name']); ?></div>
                                             <div class="spec-item-val" style="font-size: 14px; color: #212121; font-weight: 500; line-height: 1.3;"><?= html_escape($sp['value']); ?></div>
                                          </div>
                                       <?php endforeach; ?>
                                    </div>

                                    <div class="text-center mt-2 pt-2" id="specs-see-more-wrap" style="<?= (count($merged_specs) > 14) ? 'display: block;' : 'display: none;'; ?>">
                                       <button type="button" class="btn btn-sm px-4 py-1 fw-semibold d-inline-flex align-items-center gap-1" id="btn-see-more-specs" onclick="toggleSeeMoreSpecs(event)" style="font-size: 13px; border-radius: 20px; border: 1px solid #d1d5db; color: #374151; background: #ffffff;">
                                          <span id="btn-see-more-text">See More</span>
                                          <i class="fa-solid fa-chevron-down ms-1" id="btn-see-more-icon" style="font-size: 10px;"></i>
                                       </button>
                                    </div>
                                 </div>

                                 <div class="section-divider" style="border-bottom: 1px solid #f0f0f0; margin-top: 10px; margin-bottom: 10px;"></div>
                              </div>

                           </div>

                           <!-- Extra Links & Delivery Info -->
                           <div class="tf-product-info-help mt-2">
                              <div class="tf-product-info-extra-link d-flex justify-content-between">
                                 <a href="#exampleModalToggle5" data-bs-toggle="modal" class="tf-product-extra-icon d-flex align-items-center gap-2 text-decoration-none">
                                    <div class="icon"><i class="icon-shipping"></i></div>
                                    <p class="text-caption-1 mb-0">Delivery &amp; Return</p>
                                 </a>
                                 <a href="#exampleModalToggle4" data-bs-toggle="modal" class="tf-product-extra-icon d-flex align-items-center gap-2 text-decoration-none">
                                    <div class="icon"><i class="icon-question"></i></div>
                                    <p class="text-caption-1 mb-0">Ask A Question</p>
                                 </a>
                                 <a href="#exampleModalToggle6" data-bs-toggle="modal" class="tf-product-extra-icon d-flex align-items-center gap-2 text-decoration-none">
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
                                 <p class="text-caption-1 mb-0 fw-bold text-success" id="display-stock-status">In Stock (<?= !empty($initial_variant['stock']) ? (int)$initial_variant['stock'] : 15; ?> available)</p>
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
                                 <p class="text-caption-1 mb-0"><a href="<?= site_url('shop/' . ($product['category_slug'] ?? '') . $elt_qs); ?>" class="text-primary text-decoration-none fw-semibold"><?= html_escape($product['category_name'] ?? 'Electronics'); ?></a></p>
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

                           <!-- MOBILE ATTACHED ACTION BUTTONS -->
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
            </div>
         </div>
      </div>
      <!-- product details area end -->

      <!-- Content -->
      <div class="product-details-tab pb-140">
         <div class="container">
            <div class="row">
               <div class="col-xl-12">
                  <div class="product-details-tab-nav">
                     <nav>
                        <div class="product-details-nav nav nav-tab justify-content-center p-relative" id="navPresentationTab" role="tablist">
                           <button class="nav-links active" id="nav-description-tab" data-bs-toggle="tab" data-bs-target="#nav-description" type="button" role="tab" aria-controls="nav-description" aria-selected="true">Description</button>
                           <button class="nav-links" id="nav-addInfo-tab" data-bs-toggle="tab" data-bs-target="#nav-addInfo" type="button" role="tab" aria-controls="nav-addInfo" aria-selected="false">Additional information</button>
                           <button class="nav-links" id="nav-review-tab" data-bs-toggle="tab" data-bs-target="#nav-review" type="button" role="tab" aria-controls="nav-review" aria-selected="false">Customer Reviews (<?= (int)$display_review_count; ?>)</button>
                           <button class="nav-links" id="nav-question-tab" data-bs-toggle="tab" data-bs-target="#nav-question" type="button" role="tab" aria-controls="nav-question" aria-selected="false">Ask & Question</button>
                           <button class="nav-links" id="nav-faq-tab" data-bs-toggle="tab" data-bs-target="#nav-faq" type="button" role="tab" aria-controls="nav-faq" aria-selected="false">Faq</button>
                        </div>
                     </nav>  
                     <div class="tab-content" id="navPresentationTabContent">
                        <div class="tab-pane fade show active" id="nav-description" role="tabpanel" aria-labelledby="nav-description-tab" tabindex="0">
                           <div class="product-details-tab-item">
                              <div class="product-details-desc-wrap">
                                 <h3 class="product-details-desc-title"><?= html_escape($product['title'] ?? 'Product Overview'); ?></h3>
                                 <p class="mb-30">
                                    <?= nl2br(html_escape(!empty($product['description']) ? $product['description'] : ($product['short_description'] ?? ''))); ?>
                                 </p>
                                 <div class="row">
                                    <div class="col-xl-8">
                                       <div class="row">
                                          <?php if (!empty($product_highlights)): ?>
                                          <div class="col-xl-6">
                                             <div class="product-details-desc-list">
                                                <h4 class="product-details-desc-list-title">Key Highlights</h4>
                                                <ul>
                                                   <?php foreach ($product_highlights as $hl): ?>
                                                      <li><strong><?= html_escape($hl['key'] ?? $hl['name'] ?? ''); ?>:</strong> <?= html_escape($hl['value'] ?? ''); ?></li>
                                                   <?php endforeach; ?>
                                                </ul>
                                             </div>
                                          </div>
                                          <?php endif; ?>
                                          <div class="col-xl-6">
                                             <div class="product-details-desc-list">
                                                <h4 class="product-details-desc-list-title">Warranty &amp; Package</h4>
                                                <ul>
                                                   <li>Warranty: <?= html_escape($product['warranty_info'] ?? '1 Year Official Manufacturer Warranty'); ?></li>
                                                   <li>SKU: <?= html_escape($initial_sku); ?></li>
                                                   <li>100% Authentic Sealed Package</li>
                                                </ul>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-xl-4">
                                       <div class="product-details-desc-img">
                                          <img src="<?= $elt_gallery_urls[0] ?? ''; ?>" alt="<?= html_escape($product['title'] ?? ''); ?>" style="border-radius:12px;max-height:260px;object-fit:contain;">
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="tab-pane fade" id="nav-addInfo" role="tabpanel" aria-labelledby="nav-addInfo-tab" tabindex="0">
                           <div class="product-details-tab-item">
                              <div class="product-details-additional-info">
                                 <div class="row">
                                    <div class="col-xl-12">
                                       <h4 class="product-details-additional-info-title">Additional information</h4>
                                       <table>
                                          <tbody>
                                             <?php if (!empty($merged_specs)): ?>
                                                <?php foreach ($merged_specs as $msp): ?>
                                                <tr>
                                                   <td><?= html_escape($msp['name']); ?></td>
                                                   <td><?= html_escape($msp['value']); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                             <?php else: ?>
                                                <tr>
                                                   <td>Brand</td>
                                                   <td><?= html_escape($product['brand_name'] ?? 'Official Brand'); ?></td>
                                                </tr>
                                                <tr>
                                                   <td>Category</td>
                                                   <td><?= html_escape($product['category_name'] ?? 'Electronics'); ?></td>
                                                </tr>
                                             <?php endif; ?>
                                          </tbody>
                                       </table>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="tab-pane fade" id="nav-review" role="tabpanel" aria-labelledby="nav-review-tab" tabindex="0">
                           <div class="product-details-tab-item pt-4">
                              <div class="row g-4">
                                 <!-- Left Column: Product Rating Summary & Breakdown Bar -->
                                 <div class="col-lg-4 col-md-5">
                                    <div class="p-4 rounded-3 border bg-white" style="border-color:#e5e7eb !important;">
                                       <h5 class="fw-bold mb-3" style="color:#0B0D0E; font-size:17px;">Customer Ratings</h5>
                                       <?php
                                       $bar_total     = $total_review_count;
                                       $bar_avg       = $computed_avg;
                                       $bar_counts    = $rating_counts;
                                       $bar_avg_round = round($bar_avg * 2) / 2;
                                       ?>
                                       <div class="d-flex align-items-center gap-3">
                                          <div class="text-center px-2">
                                             <div class="fw-bold" style="font-size:38px; line-height:1.1; color:#0B0D0E;">
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
                                    <h4 class="fw-bold mb-3" style="color:#0B0D0E; font-size:18px;">
                                       Customer Reviews (<?= number_format($bar_total); ?>)
                                    </h4>
                                    <?php if (!empty($actual_reviews)):
                                       $latest_reviews = array_slice($actual_reviews, 0, 5);
                                       $prod_slug_for_rev = !empty($product['slug']) ? $product['slug'] : (int)($product['id'] ?? 1);
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
                                             <a href="<?= site_url('product/reviews/' . $prod_slug_for_rev . $elt_qs); ?>" class="btn btn-outline-dark fw-semibold px-4 py-2 rounded-2" style="font-size:14px;">
                                                Show All Reviews (<?= number_format(count($actual_reviews)); ?>) <i class="fa-solid fa-arrow-right ms-1"></i>
                                             </a>
                                          </div>
                                       <?php endif; ?>
                                    <?php else: ?>
                                       <div class="p-4 rounded-3 border bg-white text-center text-muted">
                                          <i class="fa-regular fa-comment-dots fs-2 mb-2 text-secondary"></i>
                                          <p class="mb-0">No customer reviews yet for <strong><?= html_escape($product['title'] ?? $product['name'] ?? 'this product'); ?></strong>.</p>
                                       </div>
                                    <?php endif; ?>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="tab-pane fade" id="nav-question" role="tabpanel" aria-labelledby="nav-question-tab" tabindex="0">
                           <div class="product-details-tab-item">
                              <div class="product-details-question-container">
                                 <div class="product-details-question-header">
                                    <div class="product-details-question-header-left">
                                       <h3 class="product-details-policy-title">Questions (4)</h3>
                                       <p class="m-0">Have question about this product? Get specific details about this product from expert.</p>
                                    </div>
                                    <div class="product-details-question-header-right">
                                       <button type="button" class="aq-btn-black btn-red-bg btn-square" data-bs-target="#exampleModalToggle4" data-bs-toggle="modal">Ask Question</button>
                                    </div>
                                 </div>

                                 <div class="product-details-question-list">
                                    
                                    <div class="product-details-question-item">
                                       <p class="product-details-question-meta">
                                          <span class="product-details-question-user">Faiza Rahman</span> on 03 Dec 2025
                                       </p>
                                       <div class="product-details-question-row">
                                          <span class="product-details-question-label">Q:</span>
                                          <p class="product-details-question-text product-details-question-text--bold">What is the screen size of the COLMI P81?</p>
                                       </div>
                                       <div class="product-details-question-row">
                                          <span class="product-details-question-label">A:</span>
                                          <p class="product-details-question-text text-ans">COLMI P81 Smart Watch has a 1.9-inch TFT HD display with 240×284 resolution and a 95% screen-to-body ratio</p>
                                       </div>
                                    </div>

                                    <div class="product-details-question-item">
                                       <p class="product-details-question-meta">
                                          <span class="product-details-question-user">Akbar Islam</span> on 24 Feb 2025
                                       </p>
                                       <div class="product-details-question-row">
                                          <span class="product-details-question-label">Q:</span>
                                          <p class="product-details-question-text product-details-question-text--bold">How long does the battery last on the COLMI P81?</p>
                                       </div>
                                       <div class="product-details-question-row">
                                          <span class="product-details-question-label">A:</span>
                                          <p class="product-details-question-text text-ans">This COLMI P81 Smart Watch battery lasts up to 7 days on normal usage.</p>
                                       </div>
                                    </div>

                                    <div class="product-details-question-item">
                                       <p class="product-details-question-meta">
                                          <span class="product-details-question-user">Fazle Hasan Abed</span> on 02 Feb 2025
                                       </p>
                                       <div class="product-details-question-row">
                                          <span class="product-details-question-label">Q:</span>
                                          <p class="product-details-question-text product-details-question-text--bold">What is the resolution of the COLMI P81 Smart Watch?</p>
                                       </div>
                                       <div class="product-details-question-row">
                                          <span class="product-details-question-label">A:</span>
                                          <p class="product-details-question-text text-ans">The resolution of the COLMI P81 Smart Watch is 240x284 pixels.</p>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="tab-pane fade" id="nav-faq" role="tabpanel" aria-labelledby="nav-faq-tab" tabindex="0">
                           <div class="product-details-tab-item">
                              <div class="aq-faq-wrap">
                                 <div class="accordion" id="accordionExample">
                                    <div class="accordion-items">
                                       <div class="accordion-header">
                                          <button class="accordion-buttons" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                             What is Emeritus Education System?
                                             <span class="aq-faq-icon"></span>
                                          </button>
                                       </div>
                                       <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
                                          <div class="accordion-body">
                                             <p>
                                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt <br> ut labore et dolore magna aliqua.
                                             </p>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="accordion-items">
                                       <div class="accordion-header">
                                          <button class="accordion-buttons collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                             Can I get a refund for my Premium Membership payment?
                                             <span class="aq-faq-icon"></span>
                                          </button>
                                       </div>
                                       <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                                          <div class="accordion-body">
                                             <p>
                                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt <br> ut labore et dolore magna aliqua.
                                             </p>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="accordion-items">
                                       <div class="accordion-header">
                                          <button class="accordion-buttons collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                             How does th Affiliate Program work?
                                             <span class="aq-faq-icon"></span>
                                          </button>
                                       </div>
                                       <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                                          <div class="accordion-body">
                                             <p>
                                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt <br> ut labore et dolore magna aliqua.
                                             </p>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="accordion-items">
                                       <div class="accordion-header">
                                          <button class="accordion-buttons collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                             What is included in Standard membership plan?
                                             <span class="aq-faq-icon"></span>
                                          </button>
                                       </div>
                                       <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                                          <div class="accordion-body">
                                             <p>
                                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt <br> ut labore et dolore magna aliqua.
                                             </p>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="accordion-items">
                                       <div class="accordion-header">
                                          <button class="accordion-buttons collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                             How to choose the right class for me?
                                             <span class="aq-faq-icon"></span>
                                          </button>
                                       </div>
                                       <div id="collapseFive" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                                          <div class="accordion-body">
                                             <p>
                                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt <br> ut labore et dolore magna aliqua.
                                             </p>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="accordion-items">
                                       <div class="accordion-header">
                                          <button class="accordion-buttons collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                                             How does th Affiliate Program work?
                                             <span class="aq-faq-icon"></span>
                                          </button>
                                       </div>
                                       <div id="collapseSix" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                                          <div class="accordion-body">
                                             <p>
                                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt <br> ut labore et dolore magna aliqua.
                                             </p>
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
      <?php if (!empty($related_products)): ?>
      <div class="elt-arrival-area elt-arrival-style pb-100">
         <div class="container">
            <div class="elt-arrival-top mb-35">
               <div class="row align-items-center">
                  <div class="col-md-6">
                     <div class="elt-arrival-title-box text-center text-md-start">
                        <h4 class="aq-section-title ff-onest-bold fs-32 mb-0">Related Products</h4>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="elt-arrival-top-btn text-center text-md-end">
                        <a class="aq-btn-link aq-btn-underline" href="<?= site_url('shop' . $elt_qs); ?>">View All Electronics</a>
                     </div>
                  </div>
               </div>
            </div>
            <div class="row gx-15 row-cols-xl-4 row-cols-lg-3 row-cols-md-2 row-cols-sm-2 row-cols-1">
               <?php foreach (array_slice($related_products, 0, 4) as $rp_idx => $rp):
                  $rp_id      = (int)($rp['id'] ?? ($rp_idx + 1));
                  $rp_name    = !empty($rp['name']) ? $rp['name'] : ($rp['title'] ?? 'Electronics Item');
                  $rp_slug    = !empty($rp['slug']) ? site_url('product/' . $rp['slug'] . $elt_qs) : site_url('product/' . $rp_id . $elt_qs);
                  $rp_raw_img = !empty($rp['primary_image']) ? $rp['primary_image'] : (!empty($rp['main_image']) ? $rp['main_image'] : ($rp['image'] ?? ''));
                  $rp_img     = $bazaro . 'img/electronics/arrival-product/product-' . (($rp_idx % 8) + 1) . '/product-1.jpg';
                  if (!empty($rp_raw_img)) {
                      if (strpos($rp_raw_img, 'http://') === 0 || strpos($rp_raw_img, 'https://') === 0) {
                          $rp_img = $rp_raw_img;
                      } else {
                          $clean_rp = ltrim($rp_raw_img, '/');
                          if (strpos($clean_rp, 'assets/') === 0 || strpos($clean_rp, 'uploads/') === 0) {
                              $rp_img = base_url($clean_rp);
                          } else {
                              $rp_img = base_url('assets/images/' . $clean_rp);
                          }
                      }
                  }
                  $rp_reg     = (float)($rp['regular_price'] ?? $rp['price'] ?? 199.00);
                  $rp_sal     = (!empty($rp['sale_price']) && (float)$rp['sale_price'] > 0 && (float)$rp['sale_price'] < $rp_reg) ? (float)$rp['sale_price'] : $rp_reg;
                  $rp_disc    = ($rp_sal < $rp_reg && $rp_reg > 0) ? round((($rp_reg - $rp_sal) / $rp_reg) * 100) : 0;
                  $rp_rev_cnt = (isset($rp['reviews']) && is_array($rp['reviews'])) ? count($rp['reviews']) : (int)($rp['reviews_count'] ?? 0);
                  $rp_rating  = $rp_rev_cnt > 0 ? (float)($rp['rating'] ?? 0) : 0.0;
                  $rp_stars   = (int)round($rp_rating);
               ?>
               <div class="col">
                  <div class="aq-product-item aq-product-main d-flex flex-column justify-content-between mb-15">
                     <div class="aq-product-thumb aq-img-hover-wrap p-relative mb-10">
                        <?php if ($rp_disc > 0): ?>
                        <div class="aq-product-badge">
                           <span class="clr-sale">-<?= $rp_disc; ?>%</span>
                        </div>
                        <?php endif; ?>
                        <a href="<?= $rp_slug; ?>">
                           <img class="aq-product-img" src="<?= $rp_img; ?>" alt="<?= html_escape($rp_name); ?>">
                        </a>
                     </div>
                     <div class="aq-product-content text-center">
                        <h4 class="aq-product-title mb-1"><a href="<?= $rp_slug; ?>"><?= html_escape($rp_name); ?></a></h4>
                        <div class="aq-product-ratting text-warning small mb-1 d-flex align-items-center justify-content-center gap-1">
                           <span>
                              <?php for ($si = 1; $si <= 5; $si++): ?>
                                 <i class="fa-<?= ($si <= $rp_stars) ? 'solid' : 'regular'; ?> fa-star"></i>
                              <?php endfor; ?>
                           </span>
                           <span class="text-muted" style="font-size:12px;">(<?= $rp_rev_cnt; ?>)</span>
                        </div>
                        <div class="aq-product-price">
                           <ins><span class="aq-product-new-price"><?= $currency_symbol . number_format($rp_sal, 2); ?></span></ins>
                           <?php if ($rp_reg > $rp_sal): ?>
                           <del><span class="aq-product-old-price"><?= $currency_symbol . number_format($rp_reg, 2); ?></span></del>
                           <?php endif; ?>
                        </div>
                     </div>
                  </div>
               </div>
               <?php endforeach; ?>
            </div>
         </div>
      </div>
      <?php endif; ?>
      <!-- Content -->

   </main>

<script>
(function() {
    'use strict';

    var productId          = <?= (int) ($product['id'] ?? 1); ?>;
    var currencySymbol     = "<?= $currency_symbol; ?>";
    var colorMap           = <?= json_encode($color_map); ?>;
    var sizeVariantMap     = <?= json_encode($size_variant_map); ?>;
    var baseHighlights     = <?= json_encode($product_highlights); ?>;
    var baseSpecifications = <?= json_encode(!empty($product['specifications']) ? $product['specifications'] : []); ?>;
    var baseBrand          = <?= json_encode(!empty($product['brand_name']) ? $product['brand_name'] : ''); ?>;
    var baseCategory       = <?= json_encode(!empty($product['category_name']) ? $product['category_name'] : ''); ?>;
    var hasVariants        = <?= $has_variants ? 'true' : 'false'; ?>;
    var hasColor           = <?= $has_color ? 'true' : 'false'; ?>;
    var hasSize            = <?= $has_size ? 'true' : 'false'; ?>;
    var multiAttrName      = "<?= !empty($multi_attr_name) ? addslashes($multi_attr_name) : 'Size'; ?>";

    var selectedColor      = "<?= addslashes($initial_color); ?>";
    var selectedSize       = "<?= addslashes($initial_size); ?>";
    var selectedVariantId  = <?= (int) $initial_variant_id; ?>;
    var currentPrice       = <?= (float) $current_price; ?>;
    var regularPrice       = <?= (float) $regular_price; ?>;
    var loaderTimeout      = null;

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

        var mergedSpecs = [];
        var seenKeys = {};

        if (baseBrand) {
            mergedSpecs.push({ name: 'Brand', value: baseBrand });
            seenKeys['brand'] = true;
        }
        if (baseCategory) {
            mergedSpecs.push({ name: 'Category', value: baseCategory });
            seenKeys['category'] = true;
        }
        var curSize = (variantData && variantData.size) ? variantData.size : selectedSize;
        if (curSize) {
            var optSpecLabel = multiAttrName || 'Option';
            mergedSpecs.push({ name: optSpecLabel, value: curSize });
            seenKeys[optSpecLabel.toLowerCase()] = true;
            seenKeys['size'] = true;
        }
        var curColor = (variantData && variantData.color) ? variantData.color : selectedColor;
        if (curColor) {
            var formattedColor = curColor.charAt(0).toUpperCase() + curColor.slice(1);
            mergedSpecs.push({ name: 'Color', value: formattedColor });
            seenKeys['color'] = true;
        }

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
            return;
        }

        if (specsBlock) specsBlock.style.display = 'block';

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
    }

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

        var currentSizeData = colorSizes[selectedSize];
        if (currentSizeData && currentSizeData.stock <= 0 && firstInStockSize) {
            chips.forEach(function(ch) {
                if (ch.getAttribute('data-size') === firstInStockSize) {
                    selectSize(firstInStockSize, ch);
                }
            });
        }
    }

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

            var salePriceEl = document.getElementById('display-sale-price');
            if (salePriceEl) {
                salePriceEl.textContent = currencySymbol + effectivePrice.toFixed(2);
            }

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

            var skuEl = document.getElementById('display-sku');
            if (skuEl && variantData.sku) {
                skuEl.textContent = variantData.sku;
            }

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

            updateVariantHighlights(variantData);
            updateVariantSpecifications(variantData);
        }
    }

    window.selectColor = function(colorName, cardEl) {
        if (selectedColor === colorName) return;
        showVariantLoader();
        selectedColor = colorName;

        var label = document.getElementById('selected-color-name');
        if (label) {
            label.textContent = colorName.charAt(0).toUpperCase() + colorName.slice(1);
        }

        var cards = document.querySelectorAll('.color-thumb-card');
        cards.forEach(function(c) { c.classList.remove('active'); });
        if (cardEl) {
            cardEl.classList.add('active');
            if (cardEl.scrollIntoView) {
                cardEl.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                setTimeout(updateColorSliderArrows, 250);
            }
        }

        updateSizeAvailability();
        syncCurrentVariant();
        hideVariantLoader(250);
    };

    window.selectSize = function(sizeName, chipEl) {
        if (chipEl && chipEl.classList.contains('is-out-of-stock')) {
            alert(multiAttrName + ' ' + sizeName + ' is currently out of stock for color ' + selectedColor + '.');
            return;
        }
        showVariantLoader();
        selectedSize = sizeName;

        var label = document.getElementById('selected-size-name');
        if (label) label.textContent = sizeName;

        var chips = document.querySelectorAll('.variant-size-chip');
        chips.forEach(function(ch) { ch.classList.remove('active'); });
        if (chipEl) chipEl.classList.add('active');

        syncCurrentVariant();
        hideVariantLoader(250);
    };

    window.addToCartAjax = function(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        var qtyInput = document.getElementById('product-qty-input');
        var qty = parseInt(qtyInput ? qtyInput.value : '1', 10) || 1;
        if (typeof bazaroAddToCart === 'function') {
            bazaroAddToCart(productId, qty, true);
            return;
        }
        var payload = new URLSearchParams();
        payload.append('product_id', productId);
        payload.append('quantity', qty);
        if (selectedVariantId) payload.append('variant_id', selectedVariantId);
        fetch('<?= site_url("cart/add"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: payload.toString()
        }).then(function() {
            var cartDrawer = document.querySelector('.cartmini__area');
            if (cartDrawer) cartDrawer.classList.add('cartmini-opened');
        });
    };

    window.buyNow = function() {
        var qty = parseInt(document.getElementById('product-qty-input')?.value || '1', 10) || 1;
        var payload = new URLSearchParams();
        payload.append('product_id', productId);
        payload.append('quantity', qty);
        if (selectedVariantId) payload.append('variant_id', selectedVariantId);

        fetch('<?= site_url("cart/add"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: payload.toString()
        }).finally(function() {
            window.location.href = '<?= site_url("checkout" . $elt_qs); ?>';
        });
    };

    window.activateReviewTab = function() {
        var revTab = document.getElementById('nav-review-tab');
        if (revTab) {
            revTab.click();
            revTab.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    };

    updateSizeAvailability();
    syncCurrentVariant();
    updateColorSliderArrows();
    window.addEventListener('resize', updateColorSliderArrows);
})();

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
    } else {
        if (btnText) btnText.textContent = 'See More';
        if (btnIcon) btnIcon.className = 'fa-solid fa-chevron-down ms-1';
    }
};
</script>

