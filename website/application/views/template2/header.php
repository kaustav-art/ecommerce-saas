<?php
$bazaro     = isset($tpl_assets) ? rtrim($tpl_assets, '/') . '/' : base_url('assets/templates/bazaro/');
$curr       = isset($currency_symbol) ? $currency_symbol : '$';
$cart_total = isset($cart_summary['subtotal']) ? (float)$cart_summary['subtotal'] : 0.00;
$cart_cnt   = isset($cart_count) ? (int)$cart_count : 0;
$wish_cnt   = isset($wishlist_count) ? (int)$wishlist_count : 0;

if (!function_exists('elt_resolve_img')) {
    function elt_resolve_img($img, $fallback_rel = 'img/electronics/arrival-product/product-1/product-1.jpg') {
        $bazaro_base = base_url('assets/templates/bazaro/');
        if (empty($img)) {
            return $bazaro_base . $fallback_rel;
        }
        if (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0) {
            return $img;
        }
        $clean = ltrim($img, '/');
        if (strpos($clean, 'assets/') === 0) {
            return base_url($clean);
        }
        if (file_exists(FCPATH . 'assets/images/products/' . $clean)) {
            return base_url('assets/images/products/' . $clean);
        }
        if (file_exists(FCPATH . 'assets/images/' . $clean)) {
            return base_url('assets/images/' . $clean);
        }
        if (file_exists(FCPATH . 'assets/templates/bazaro/img/' . $clean)) {
            return $bazaro_base . 'img/' . $clean;
        }
        return base_url('assets/images/products/' . $clean);
    }
}
?>
<!doctype html>
<html class="no-js" lang="en">
<head>
   <meta charset="utf-8">
   <meta http-equiv="x-ua-compatible" content="ie=edge">
   <title><?= isset($page_title) ? html_escape($page_title) : html_escape($site_name ?? 'TechZone Electronics'); ?></title>
   <meta name="description" content="<?= html_escape($meta_description ?? ($store_settings['meta_description'] ?? 'Premium Electronics Store')); ?>">
   <meta name="viewport" content="width=device-width, initial-scale=1">

   <?php
   $raw_fav = trim((string)($store_settings['site_favicon'] ?? ''));
   $web_favicon = ($raw_fav !== '')
       ? ((strpos($raw_fav, 'assets/') === 0) ? base_url($raw_fav) : base_url('assets/images/logo/' . $raw_fav))
       : base_url('assets/images/logo/codeulas_logo_small.webp');
   $raw_logo = trim((string)($store_settings['site_logo'] ?? ''));
   $web_logo = ($raw_logo !== '')
       ? ((strpos($raw_logo, 'assets/') === 0) ? base_url($raw_logo) : base_url('assets/images/logo/' . $raw_logo))
       : base_url('assets/images/logo/logo.webp');
   ?>
   <link rel="shortcut icon" href="<?= $web_favicon; ?>">
   <link rel="icon" href="<?= $web_favicon; ?>">

   <!-- CSS here (Exact Bazaro Electronics stack) -->
   <link rel="stylesheet" href="<?= $bazaro; ?>css/bootstrap.css">
   <link rel="stylesheet" href="<?= $bazaro; ?>css/animate.css">
   <link rel="stylesheet" href="<?= $bazaro; ?>css/slick.css">
   <link rel="stylesheet" href="<?= $bazaro; ?>css/nice-select.css">
   <link rel="stylesheet" href="<?= $bazaro; ?>css/custom-animation.css">
   <link rel="stylesheet" href="<?= $bazaro; ?>css/swiper-bundle.css">
   <link rel="stylesheet" href="<?= $bazaro; ?>css/magnific-popup.css">
   <link rel="stylesheet" href="<?= $bazaro; ?>css/font-awesome-pro.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
   <link rel="stylesheet" href="<?= $bazaro; ?>css/spacing.css">
   <link rel="stylesheet" href="<?= $bazaro; ?>css/main.css">

   <link rel="stylesheet" type="text/css" href="<?= base_url('assets/fonts/font-icons.css'); ?>">
   <link rel="stylesheet" type="text/css" href="<?= base_url('assets/fonts/fonts.css'); ?>">

   <style>
      :root {
         --elt-dark: #0B0D0E;
         --elt-lime: #E1EEB7;
         --elt-slate: #f6f7f9;
         --bs-primary: #0B0D0E;
         --bs-primary-rgb: 11, 13, 14;
      }
      /* Preload Spinner (Matching Fashion Template) */
      #loading { display: none !important; }
      .preload-container {
         display: flex;
         position: fixed;
         width: 100%;
         height: 100%;
         top: 0;
         left: 0;
         background: #ffffff;
         z-index: 99999999999;
         align-items: center;
         justify-content: center;
         overflow: hidden;
      }
      .preload-logo {
         position: relative;
         width: 65px;
         height: 65px;
         display: flex;
         align-items: center;
         justify-content: center;
      }
      .preload-logo .spinner {
         width: 60px;
         height: 60px;
         border: 3px solid rgba(0, 0, 0, 0.08);
         border-top: 3px solid #111;
         border-radius: 50%;
         position: absolute;
         top: 0;
         bottom: 0;
         left: 0;
         right: 0;
         margin: auto;
         animation: spin 0.8s infinite linear;
      }
      @keyframes spin {
         0% { transform: rotate(0deg); }
         100% { transform: rotate(360deg); }
      }

      /* Wide Search Bar & Autocomplete (Matching Fashion Template) */
      .store-search-col {
         max-width: 680px;
         margin: 0 1.25rem;
      }
      .store-search-input-wrap {
         display: flex;
         align-items: center;
         background-color: #f4f6f8;
         border-radius: 8px;
         height: 44px;
         padding: 0 14px;
         transition: background-color 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
         border: 1px solid #e2e8f0;
      }
      .store-search-input-wrap:focus-within {
         background-color: #ffffff;
         border-color: #0B0D0E;
         box-shadow: 0 0 0 3px rgba(11, 13, 14, 0.1);
      }
      .store-search-icon-btn {
         background: transparent;
         border: none;
         color: #475569;
         font-size: 15px;
         padding: 0;
         margin-right: 12px;
         cursor: pointer;
         display: flex;
         align-items: center;
         justify-content: center;
      }
      .store-search-icon-btn:hover {
         color: #0B0D0E;
      }
      .store-search-input {
         height: 34px !important;
         border: 0 !important;
         background-color: transparent !important;
         outline: none !important;
         width: 100%;
         font-size: 14px;
         color: #0B0D0E;
         font-weight: 400;
         padding: 0 8px !important;
         border-radius: 4px;
         transition: background-color 0.2s ease;
         box-shadow: none !important;
      }
      .store-search-input:focus,
      #storeNavSearchInput:focus,
      .store-search-input-wrap:focus-within .store-search-input {
         background-color: #ffffff !important;
      }
      .store-search-input::placeholder {
         color: #717478;
         font-size: 14px;
      }
      @media (max-width: 767.98px) {
         .store-search-col {
            width: 100%;
            max-width: 100%;
            flex: 0 0 100%;
            margin: 8px 0 2px 0;
         }
         .store-search-input-wrap {
            height: 40px;
            background-color: #ffffff;
            border: 1px solid #d4d5d9;
            border-radius: 8px;
            padding: 0 12px;
         }
         .store-search-input {
            background-color: #ffffff !important;
            font-size: 13.5px;
         }
      }

      /* Autocomplete Dropdown */
      .store-autocomplete-dropdown {
         position: absolute;
         top: calc(100% + 6px);
         left: 0;
         right: 0;
         background: #ffffff;
         border: 1px solid #e2e8f0;
         border-radius: 8px;
         z-index: 1100;
         max-height: 420px;
         overflow-y: auto;
      }
      .store-auto-item {
         display: flex;
         align-items: center;
         gap: 12px;
         padding: 9px 14px;
         text-decoration: none;
         border-bottom: 1px solid #f5f5f5;
         transition: background-color 0.15s ease;
      }
      .store-auto-item:hover {
         background-color: #f4f8ff;
      }
      .store-auto-img {
         width: 44px;
         height: 44px;
         object-fit: cover;
         border-radius: 6px;
         border: 1px solid #eee;
         flex-shrink: 0;
      }
      .store-auto-title {
         font-size: 13.5px;
         font-weight: 500;
         color: #212121;
         overflow: hidden;
         text-overflow: ellipsis;
         white-space: nowrap;
      }
      .store-auto-cat {
         font-size: 11.5px;
         color: #888;
         margin-top: 2px;
      }
      .store-auto-price {
         font-size: 13.5px;
         font-weight: 700;
         color: #2874f0;
         flex-shrink: 0;
      }
      .store-auto-viewall {
         display: block;
         text-align: center;
         padding: 10px;
         font-size: 13px;
         font-weight: 600;
         color: #2874f0;
         text-decoration: none;
         background: #fbfcfe;
      }
      .store-auto-viewall:hover {
         background-color: #f0f5ff;
         text-decoration: underline;
      }

      .elt-toast-notify {
         position: fixed; bottom: 28px; right: 28px; z-index: 99999;
         background: #111827; color: #fff; padding: 13px 20px; border-radius: 10px;
         font-size: 14px; font-weight: 600; box-shadow: 0 10px 25px rgba(0,0,0,0.22);
         display: none; align-items: center; gap: 10px;
      }
      .elt-logo-text {
         font-size: 24px; font-weight: 800; color: #0B0D0E; text-decoration: none; letter-spacing: -0.5px;
      }

      /* =====================================================================
       * Bazaro Electronics Theme Overrides for Shared Inner Pages
       * Palette: Dark Carbon (#0B0D0E) + Neon Lime (#E1EEB7) + Slate (#f6f7f9)
       * ===================================================================== */
      section[style*="background: #f1f3f6"],
      section[style*="background:#f1f3f6"],
      section[style*="background-color: #f1f3f6"],
      div[style*="background-color: #f1f3f6"],
      .flat-spacing[style*="background: #f1f3f6"] {
         background-color: #f6f7f9 !important;
      }
      .text-primary {
         color: #0B0D0E !important;
      }
      .bg-primary {
         background-color: #0B0D0E !important;
      }
      .border-primary {
         border-color: #0B0D0E !important;
      }
      .btn-primary,
      .btn-deliver-here,
      .btn-proceed-buy,
      .tf-btn.btn-fill {
         background-color: #0B0D0E !important;
         border-color: #0B0D0E !important;
         color: #ffffff !important;
         font-weight: 700 !important;
         border-radius: 10px !important;
         transition: all 0.2s ease !important;
         box-shadow: 0 4px 14px rgba(11, 13, 14, 0.14) !important;
      }
      .btn-primary:hover,
      .btn-primary:focus,
      .btn-deliver-here:hover,
      .btn-proceed-buy:hover,
      .tf-btn.btn-fill:hover {
         background-color: #E1EEB7 !important;
         border-color: #E1EEB7 !important;
         color: #0B0D0E !important;
      }
      .btn-outline-primary {
         color: #0B0D0E !important;
         border-color: #0B0D0E !important;
         font-weight: 700 !important;
         border-radius: 10px !important;
      }
      .btn-outline-primary:hover,
      .btn-outline-primary:focus {
         background-color: #0B0D0E !important;
         border-color: #0B0D0E !important;
         color: #E1EEB7 !important;
      }

      /* Account Sidebar & Profile Cards (Electronics Dark Carbon + Lime Theme) */
      .fk-sidebar-wrap .card,
      section .card.border-0.shadow-sm,
      .cart-address-card,
      .cart-items-card,
      .cart-summary-card,
      .fk-step-card {
         border: 1px solid #e7eaf0 !important;
         border-radius: 16px !important;
         box-shadow: 0 4px 18px rgba(11, 13, 14, 0.04) !important;
      }
      .fk-menu-group-header {
         color: #0B0D0E !important;
         font-weight: 800 !important;
      }
      .fk-menu-group-header i {
         color: #0B0D0E !important;
         background: #E1EEB7;
         width: 28px !important;
         height: 28px;
         border-radius: 7px;
         display: inline-flex;
         align-items: center;
         justify-content: center;
         font-size: 13px !important;
      }
      .fk-sub-link {
         border-radius: 8px;
         margin: 2px 10px;
         padding: 9px 14px !important;
         font-weight: 500;
         color: #334155;
      }
      .fk-sub-link:hover {
         color: #0B0D0E !important;
         background-color: #f1f5f9 !important;
      }
      .fk-sub-link.active {
         color: #E1EEB7 !important;
         background-color: #0B0D0E !important;
         font-weight: 700 !important;
      }
      .fk-Single-link:hover {
         color: #0B0D0E !important;
         background-color: #f1f5f9 !important;
      }
      .fk-input-control {
         border-radius: 10px !important;
         border-color: #d8dee6 !important;
      }
      .fk-input-control:focus,
      .form-control:focus,
      .form-select:focus {
         border-color: #0B0D0E !important;
         box-shadow: 0 0 0 3px rgba(11, 13, 14, 0.1) !important;
      }

      /* Reset Bazaro global input rules on checkboxes, radios, and range sliders */
      input[type="checkbox"],
      input[type="radio"],
      .form-check-input {
         width: 18px !important;
         height: 18px !important;
         min-width: 18px !important;
         min-height: 18px !important;
         padding: 0 !important;
         padding-inline-start: 0 !important;
         padding-inline-end: 0 !important;
         margin-top: 0 !important;
         line-height: 1 !important;
         flex-shrink: 0 !important;
         border: 1.5px solid #94a3b8 !important;
         background-color: #ffffff !important;
         box-shadow: none !important;
         cursor: pointer;
         vertical-align: middle;
      }
      input[type="checkbox"],
      .form-check-input[type="checkbox"] {
         border-radius: 4px !important;
      }
      input[type="radio"],
      .form-check-input[type="radio"] {
         border-radius: 50% !important;
      }
      input[type="checkbox"]:checked,
      input[type="radio"]:checked,
      .form-check-input:checked {
         background-color: #0B0D0E !important;
         border-color: #0B0D0E !important;
         accent-color: #0B0D0E !important;
      }
      input[type="checkbox"]:focus,
      input[type="radio"]:focus,
      .form-check-input:focus {
         border-color: #0B0D0E !important;
         box-shadow: 0 0 0 3px rgba(11, 13, 14, 0.12) !important;
      }
      input[type="range"],
      .form-range {
         width: 100% !important;
         height: 6px !important;
         padding: 0 !important;
         padding-inline-start: 0 !important;
         padding-inline-end: 0 !important;
         border: none !important;
         box-shadow: none !important;
         accent-color: #0B0D0E !important;
      }

      /* Cart, Checkout, Order Timeline & Inner Page Headers */
      .fk-step-header.active {
         background: #0B0D0E !important;
         color: #ffffff !important;
         border-radius: 16px 16px 0 0;
      }
      .fk-step-header.active .step-num {
         background: #E1EEB7 !important;
         color: #0B0D0E !important;
      }
      .step-num {
         color: #0B0D0E !important;
         background: #f1f5f9 !important;
      }
      .btn-change-addr {
         color: #0B0D0E !important;
         border-color: #cbd5e1 !important;
         background: #f8fafc !important;
         border-radius: 8px !important;
      }
      .btn-change-addr:hover {
         background: #0B0D0E !important;
         color: #E1EEB7 !important;
         border-color: #0B0D0E !important;
      }
      .fk-addr-item.selected,
      .fk-pay-item.selected {
         background: #f8fafc !important;
         border-color: #0B0D0E !important;
      }
      .fk-order-card:hover {
         border-color: #0B0D0E !important;
         box-shadow: 0 6px 18px rgba(11, 13, 14, 0.08) !important;
      }
      .fk-item-title:hover {
         color: #0B0D0E !important;
      }
      .fk-step-item.completed .fk-step-circle,
      .fk-step-item.active .fk-step-circle {
         background: #0B0D0E !important;
         border-color: #0B0D0E !important;
      }
      .fk-step-item.completed:not(:last-child)::after {
         background: #0B0D0E !important;
      }
      .tf-page-title {
         background: linear-gradient(135deg, #0B0D0E 0%, #181c20 60%, #11161b 100%) !important;
         padding: 36px 0 !important;
         color: #ffffff !important;
      }
      .tf-page-title .heading,
      .tf-page-title h1,
      .tf-page-title h2,
      .tf-page-title h3,
      .tf-page-title .breadcrumbs,
      .tf-page-title .breadcrumbs a,
      .tf-page-title .breadcrumbs li,
      .tf-page-title .text-secondary {
         color: #ffffff !important;
      }
      .tf-page-title .breadcrumbs a:hover {
         color: #E1EEB7 !important;
      }
   </style>

   <script>
      window.BASE_URL = '<?= rtrim(site_url(), '/') . '/'; ?>';
      window.SITE_URL = '<?= rtrim(site_url(), '/'); ?>';
      window.ASSET_URL = '<?= rtrim(base_url(), '/') . '/'; ?>';
      window.CURRENCY_SYMBOL = '<?= addslashes($curr); ?>';
      window.IS_USER_LOGGED_IN = <?= !empty($is_logged_in) ? 'true' : 'false'; ?>;

      // Auto-fade preloader on window load
      window.addEventListener('load', function() {
         setTimeout(function() {
            var p = document.querySelector('.preload');
            if (p) {
               p.style.transition = 'opacity 0.4s ease';
               p.style.opacity = '0';
               setTimeout(function() { if (p && p.parentNode) p.parentNode.removeChild(p); }, 400);
            }
         }, 500);
      });
   </script>
</head>

<body>

   <!-- preload -->
   <div class="preload preload-container">
      <div class="preload-logo">
         <div class="spinner"></div>
      </div>
   </div>
   <!-- /preload -->

   <!-- back to top start -->
   <div class="progress-wrap">
      <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
         <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98"></path>
      </svg>
   </div>
   <!-- back to top end -->

   <!-- Toast Notification -->
   <div id="eltToast" class="elt-toast-notify">
      <i class="fa-solid fa-circle-check" style="color:#E1EEB7;"></i>
      <span id="eltToastText">Added to cart!</span>
   </div>

   <!-- Quick View Modal -->
   <div class="modal fade aq-product-modal" id="producQuickViewModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
         <div class="modal-content" style="border-radius:16px;overflow:hidden;">
            <div class="modal-body p-4 p-lg-5 position-relative">
               <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
               <div class="row align-items-center g-4">
                  <div class="col-md-6 text-center">
                     <img id="eltQvImage" src="<?= $bazaro; ?>img/electronics/arrival-product/product-1/product-1.jpg" alt="Product" class="img-fluid rounded-3" style="max-height:360px;object-fit:contain;">
                  </div>
                  <div class="col-md-6">
                     <span id="eltQvCat" class="badge bg-light text-dark mb-2">Electronics</span>
                     <h3 id="eltQvTitle" class="mb-2" style="font-size:22px;font-weight:700;">Product Title</h3>
                     <div class="mb-3">
                        <span id="eltQvPrice" style="font-size:24px;font-weight:700;color:#0B0D0E;"><?= $curr; ?>0.00</span>
                     </div>
                     <p id="eltQvDesc" class="text-muted mb-4" style="font-size:14px;line-height:1.6;"></p>
                     <div class="d-flex align-items-center gap-3 mb-3">
                        <input type="number" id="eltQvQty" value="1" min="1" class="form-control" style="width:85px;height:46px;text-align:center;font-weight:600;">
                        <button type="button" id="eltQvAddBtn" class="aq-btn-black radius-30 flex-grow-1" style="height:46px;border:none;">
                           Add To Cart
                        </button>
                     </div>
                     <div class="small text-muted">
                        SKU: <span id="eltQvSku" class="text-dark fw-semibold">N/A</span> &bull;
                        <a id="eltQvLink" href="#" class="text-decoration-underline text-dark fw-semibold">View Full Details</a>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>

   <!-- cartmini area start -->
   <div class="aq-cartmini-area aq-cartmini-active d-flex flex-column justify-content-between" id="eltCartMiniDrawer">
      <div class="aq-cartmini-top">
         <div class="aq-cartmini-header d-flex align-items-center justify-content-between">
            <h4 class="aq-cartmini-title">Shopping Cart</h4>
            <div class="aq-cartmini-close">
               <button class="aq-cartmini-close-btn">
                  <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                     <path d="M11 1L1 11M1 1L11 11" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
               </button>
            </div>
         </div>
         <div class="aq-cartmini-body">
            <div class="aq-cartmini-product-list" id="eltCartMiniBody">
               <?php if (!empty($cart_items)): ?>
                  <?php foreach ($cart_items as $c_key => $c_item):
                     $c_name = $c_item['name'] ?? ($c_item['title'] ?? ($c_item['product_title'] ?? 'Electronics Product'));
                     $c_img = elt_resolve_img($c_item['image'] ?? ($c_item['main_image'] ?? ''));
                     $c_slug = !empty($c_item['slug']) ? site_url('product/' . $c_item['slug']) : site_url('product/' . ($c_item['product_id'] ?? ''));
                  ?>
                  <div class="aq-cartmini-product-item d-flex align-items-center justify-content-between">
                     <div class="aq-cartmini-product-info d-flex align-items-center">
                        <div class="aq-cartmini-product-img">
                           <a href="<?= $c_slug; ?>"><img src="<?= $c_img; ?>" alt="<?= html_escape($c_name); ?>"></a>
                        </div>
                        <div class="aq-cartmini-product-content">
                           <h4 class="aq-cartmini-product-title">
                              <a href="<?= $c_slug; ?>"><?= html_escape($c_name); ?></a>
                           </h4>
                           <span class="aq-cartmini-product-price"><?= (int)($c_item['quantity'] ?? 1); ?> &times; <?= $curr . number_format((float)($c_item['price'] ?? 0), 2); ?></span>
                        </div>
                     </div>
                     <div class="aq-cartmini-product-Action">
                        <button type="button" onclick="removeCartItemAjax('<?= html_escape($c_item['cart_key'] ?? $c_key); ?>')">
                           <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path d="M9 1L1 9M1 1L9 9" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                           </svg>
                        </button>
                     </div>
                  </div>
                  <?php endforeach; ?>
               <?php else: ?>
                  <div class="text-center py-5">
                     <p class="mb-3 text-muted">Your Cart is empty</p>
                     <a class="aq-btn-black radius-30" href="<?= site_url('shop'); ?>">Go to Shop</a>
                  </div>
               <?php endif; ?>
            </div>
         </div>
      </div>
      <div class="aq-cartmini-footer">
         <div class="aq-cartmini-total d-flex align-items-center justify-content-between mb-15">
            <h4>Subtotal</h4>
            <span id="eltCartMiniSubtotal"><?= $curr . number_format($cart_total, 2); ?></span>
         </div>
         <div class="aq-cartmini-btn d-flex justify-content-between">
            <a class="aq-btn-border" href="<?= site_url('cart'); ?>">View Cart</a>
            <a class="aq-btn-black" href="<?= site_url('checkout'); ?>" onclick="return handleCheckoutClick(event, '<?= site_url('checkout'); ?>')">Checkout</a>
         </div>
      </div>
   </div>
   <!-- cartmini area end -->

   <!-- body overlay here -->
   <div class="body-overlay"></div>
   <!-- body overlay end -->

   <!-- bottom menu area start -->
   <div class="aq-bottom-menu d-md-none">
      <div class="aq-bottom-menu-wrap d-flex align-items-center justify-content-between">
         <div class="aq-bottom-menu-item">
            <a href="<?= site_url('home'); ?>">
               <svg xmlns="http://www.w3.org/2000/svg" width="18" height="19" viewBox="0 0 18 19" fill="none">
                  <path d="M6.4 18V11.7H11.2V18M0.8 7.15L8.8 1L16.8 7.15V16.65C16.8 17.008 16.6314 17.3514 16.3314 17.6046C16.0313 17.8577 15.6243 18 15.2 18H2.4C1.97565 18 1.56869 17.8577 1.26863 17.6046C0.968571 17.3514 0.8 17.008 0.8 16.65V7.15Z" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
               </svg>
               <span>Home</span>
            </a>
         </div>
         <div class="aq-bottom-menu-item">
            <a href="<?= site_url('shop'); ?>">
               <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                  <path d="M0.75 0.75H6.08333V6.08333H0.75V0.75ZM9.41667 0.75H14.75V6.08333H9.41667V0.75ZM9.41667 9.41667H14.75V14.75H9.41667V9.41667ZM0.75 9.41667H6.08333V14.75H0.75V9.41667Z" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
               </svg>
               <span>Shop</span>
            </a>
         </div>
         <div class="aq-bottom-menu-item">
            <button type="button" onclick="var s=document.getElementById('storeNavSearchInput');if(s){window.scrollTo({top:0,behavior:'smooth'});s.focus();}">
               <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                  <path d="M18.7504 18.7499L14.4004 14.3999M16.75 8.75C16.75 13.1683 13.1683 16.75 8.75 16.75C4.33172 16.75 0.75 13.1683 0.75 8.75C0.75 4.33172 4.33172 0.75 8.75 0.75C13.1683 0.75 16.75 4.33172 16.75 8.75Z" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
               </svg>
               <span>Search</span>
            </button>
         </div>
         <div class="aq-bottom-menu-item">
            <a href="<?= site_url('wishlist'); ?>">
               <svg xmlns="http://www.w3.org/2000/svg" width="21" height="20" viewBox="0 0 21 20" fill="none">
                  <path d="M6.50726 4.80303C5.44195 5.14334 4.68503 6.09974 4.59044 7.22502M10.4856 18.6038C12.6562 17.2679 14.6755 15.6957 16.5073 13.9152C17.7951 12.633 18.7756 11.0698 19.3735 9.3454C20.4494 6.00032 19.1927 2.17084 15.6755 1.03753C13.827 0.442448 11.8081 0.782566 10.2505 1.95149C8.69225 0.783989 6.67412 0.443991 4.82552 1.03753C1.30833 2.17084 0.0425004 6.00032 1.11845 9.3454C1.71636 11.0698 2.69679 12.633 3.98465 13.9152C5.81647 15.6957 7.83575 17.2679 10.0064 18.6038L10.2414 18.75L10.4856 18.6038Z" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
               </svg>
               <span>Wishlist</span>
            </a>
         </div>
         <div class="aq-bottom-menu-item">
            <?php if (!empty($is_logged_in)): ?>
            <a href="<?= site_url('account'); ?>">
               <svg xmlns="http://www.w3.org/2000/svg" width="17" height="20" viewBox="0 0 17 20" fill="none">
                  <path d="M16.212 18.75C16.212 15.267 12.747 12.45 8.481 12.45C4.215 12.45 0.75 15.267 0.75 18.75M12.9805 5.25C12.9805 7.73528 10.9657 9.75 8.48047 9.75C5.99519 9.75 3.98047 7.73528 3.98047 5.25C3.98047 2.76472 5.99519 0.75 8.48047 0.75C10.9657 0.75 12.9805 2.76472 12.9805 5.25Z" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
               </svg>
               <span>Account</span>
            </a>
            <?php else: ?>
            <a href="#exampleModalToggle" data-bs-toggle="modal">
               <svg xmlns="http://www.w3.org/2000/svg" width="17" height="20" viewBox="0 0 17 20" fill="none">
                  <path d="M16.212 18.75C16.212 15.267 12.747 12.45 8.481 12.45C4.215 12.45 0.75 15.267 0.75 18.75M12.9805 5.25C12.9805 7.73528 10.9657 9.75 8.48047 9.75C5.99519 9.75 3.98047 7.73528 3.98047 5.25C3.98047 2.76472 5.99519 0.75 8.48047 0.75C10.9657 0.75 12.9805 2.76472 12.9805 5.25Z" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
               </svg>
               <span>Sign In</span>
            </a>
            <?php endif; ?>
         </div>
      </div>
   </div>
   <!-- bottom menu area end -->

   <header class="elt-site-header">

      <!-- header area start -->
      <div class="aq-header-area elt-sticky-top-bar p-relative py-3">
         <div class="container">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 gap-md-3">
               <!-- Logo -->
               <div class="aq-header-logo d-flex align-items-center">
                  <a href="<?= site_url('home'); ?>" class="d-inline-flex align-items-center">
                     <img data-width="120" src="<?= base_url('assets/images/logo/logo.webp'); ?>" alt="<?= html_escape($site_name); ?>" style="max-height:42px;width:auto;object-fit:contain;">
                  </a>
               </div>

               <!-- Centered Wide Search Box (Matches Fashion Template, wraps to 2nd row on mobile) -->
               <div class="store-search-col flex-grow-1 order-3 order-md-2 position-relative">
                  <form action="<?= site_url('shop'); ?>" method="GET" class="store-search-form" id="storeNavSearchForm">
                     <?php if (empty($is_user_project)): ?>
                     <input type="hidden" name="preview_tpl" value="electronics">
                     <?php endif; ?>
                     <div class="store-search-input-wrap">
                        <button type="submit" class="store-search-icon-btn" aria-label="Search">
                           <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                        <input type="text"
                               name="q"
                               id="storeNavSearchInput"
                               class="store-search-input"
                               placeholder="Search for Products, Brands and More"
                               value="<?= html_escape($search_query ?? ($this->input->get('q') ?: '')); ?>"
                               autocomplete="off">
                     </div>
                  </form>
                  <!-- Search Autocomplete Dropdown -->
                  <div id="storeAutocompleteResults" class="store-autocomplete-dropdown shadow-lg d-none"></div>
               </div>

               <!-- Right Options -->
               <div class="aq-header-right-options text-end order-2 order-md-3">
                  <ul>
                     <li class="aq-header-top-account d-none d-md-inline-block">
                        <?php if (!empty($is_logged_in)): ?>
                        <a href="<?= site_url('account'); ?>" title="<?= html_escape($current_user['first_name'] ?? 'Account'); ?>">
                           <i>
                              <svg xmlns="http://www.w3.org/2000/svg" width="17" height="20" viewBox="0 0 17 20" fill="none">
                                 <path d="M16.212 18.75C16.212 15.267 12.747 12.45 8.481 12.45C4.215 12.45 0.75 15.267 0.75 18.75M12.9805 5.25C12.9805 7.73528 10.9657 9.75 8.48047 9.75C5.99519 9.75 3.98047 7.73528 3.98047 5.25C3.98047 2.76472 5.99519 0.75 8.48047 0.75C10.9657 0.75 12.9805 2.76472 12.9805 5.25Z" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                              </svg>
                           </i>
                        </a>
                        <?php else: ?>
                        <a href="#exampleModalToggle" data-bs-toggle="modal" title="Sign In">
                           <i>
                              <svg xmlns="http://www.w3.org/2000/svg" width="17" height="20" viewBox="0 0 17 20" fill="none">
                                 <path d="M16.212 18.75C16.212 15.267 12.747 12.45 8.481 12.45C4.215 12.45 0.75 15.267 0.75 18.75M12.9805 5.25C12.9805 7.73528 10.9657 9.75 8.48047 9.75C5.99519 9.75 3.98047 7.73528 3.98047 5.25C3.98047 2.76472 5.99519 0.75 8.48047 0.75C10.9657 0.75 12.9805 2.76472 12.9805 5.25Z" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                              </svg>
                           </i>
                        </a>
                        <?php endif; ?>
                     </li>
                     <li class="aq-header-top-wishlist d-none d-md-inline-block">
                        <a href="<?= site_url('wishlist'); ?>" class="text-decoration-none" title="My Wishlist" aria-label="My Wishlist">
                           <span class="count-box wishlist-count"><?= $wish_cnt; ?></span>
                           <i>
                              <svg xmlns="http://www.w3.org/2000/svg" width="21" height="20" viewBox="0 0 21 20" fill="none">
                                 <path d="M6.50726 4.80303C5.44195 5.14334 4.68503 6.09974 4.59044 7.22502M10.4856 18.6038C12.6562 17.2679 14.6755 15.6957 16.5073 13.9152C17.7951 12.633 18.7756 11.0698 19.3735 9.3454C20.4494 6.00032 19.1927 2.17084 15.6755 1.03753C13.827 0.442448 11.8081 0.782566 10.2505 1.95149C8.69225 0.783989 6.67412 0.443991 4.82552 1.03753C1.30833 2.17084 0.0425004 6.00032 1.11845 9.3454C1.71636 11.0698 2.69679 12.633 3.98465 13.9152C5.81647 15.6957 7.83575 17.2679 10.0064 18.6038L10.2414 18.75L10.4856 18.6038Z" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                              </svg>
                           </i>
                        </a>
                     </li>
                     <li class="aq-header-top-cart aq-cart-btn">
                        <button type="button">
                           <span class="count-box cart-count"><?= $cart_cnt; ?></span>
                           <i>
                              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="21" viewBox="0 0 20 21" fill="none">
                                 <path d="M5.48681 5.07041C5.48681 2.68433 7.4211 0.750039 9.80717 0.750039C10.9562 0.74517 12.0598 1.1982 12.874 2.00895C13.6882 2.81971 14.1459 3.92139 14.1458 5.07041M6.84107 9.57384H6.88684M12.6721 9.57388H12.7179M5.62368 19.972H13.9715C17.0379 19.972 19.3903 18.8645 18.7221 14.4068L17.944 8.3656C17.5321 6.14134 16.1134 5.29008 14.8685 5.29008H4.69004C3.42688 5.29008 2.0905 6.20542 1.61453 8.3656L0.836493 14.4068C0.268988 18.361 2.55732 19.972 5.62368 19.972Z" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                              </svg>
                           </i>
                        </button>
                     </li>
                  </ul>
               </div>
            </div>
         </div>
      </div>
      <!-- header area end -->

      <?php
      // Build clean Electronics Categories & Subcategories for the Header Row
      $elt_header_cats = !empty($nav_categories) ? $nav_categories : (!empty($category_tree) ? $category_tree : []);
      $elt_subcat_map = [
         'headphones' => [
            ['name' => 'Wireless Earbuds',     'slug' => 'wireless-earbuds'],
            ['name' => 'Noise Cancelling',     'slug' => 'noise-cancelling-headphones'],
            ['name' => 'Over-Ear Studio',      'slug' => 'over-ear-studio'],
            ['name' => 'Bluetooth Speakers',   'slug' => 'bluetooth-speakers'],
            ['name' => 'Home Soundbars',       'slug' => 'home-soundbars'],
            ['name' => 'Gaming Headsets',      'slug' => 'gaming-headsets'],
         ],
         'watch' => [
            ['name' => 'AMOLED Smartwatches',  'slug' => 'amoled-smartwatches'],
            ['name' => 'Fitness & Sport Bands','slug' => 'fitness-trackers'],
            ['name' => 'Luxury Titanium',      'slug' => 'luxury-smartwatches'],
            ['name' => 'Outdoor & GPS',        'slug' => 'outdoor-gps-watches'],
            ['name' => 'Kids Smartwatches',    'slug' => 'kids-smartwatches'],
            ['name' => 'Straps & Chargers',    'slug' => 'watch-straps-chargers'],
         ],
         'phone' => [
            ['name' => 'Flagship 5G Phones',   'slug' => 'flagship-5g-phones'],
            ['name' => 'Foldable Phones',      'slug' => 'foldable-smartphones'],
            ['name' => 'Gaming Smartphones',   'slug' => 'gaming-smartphones'],
            ['name' => 'Pro Camera Phones',    'slug' => 'camera-phones'],
            ['name' => 'Mid-Range 5G',         'slug' => 'mid-range-5g'],
            ['name' => 'Cases & MagSafe',      'slug' => 'phone-accessories'],
         ],
         'laptop' => [
            ['name' => 'MacBook Air & Pro',    'slug' => 'macbooks'],
            ['name' => 'RTX Gaming Laptops',   'slug' => 'gaming-laptops'],
            ['name' => 'Ultrabooks & 2-in-1',  'slug' => 'ultrabooks'],
            ['name' => 'Creator Workstations', 'slug' => 'creator-laptops'],
            ['name' => '4K Monitors',          'slug' => 'monitors-displays'],
            ['name' => 'USB-C Hubs & Docks',   'slug' => 'laptop-docks-hubs'],
         ],
         'camera' => [
            ['name' => 'Mirrorless Cameras',   'slug' => 'mirrorless-cameras'],
            ['name' => '4K Camera Drones',     'slug' => 'camera-drones'],
            ['name' => 'Action & Vlog Cams',   'slug' => 'action-cameras'],
            ['name' => 'Prime & Zoom Lenses',  'slug' => 'camera-lenses'],
            ['name' => 'Gimbals & Stabilizers','slug' => 'gimbals-stabilizers'],
            ['name' => 'SD Cards & Lighting',  'slug' => 'camera-accessories'],
         ],
         'gaming' => [
            ['name' => 'PlayStation 5',        'slug' => 'playstation-5'],
            ['name' => 'Xbox Series X|S',      'slug' => 'xbox-series'],
            ['name' => 'Handheld Consoles',    'slug' => 'handheld-consoles'],
            ['name' => 'Pro Controllers',      'slug' => 'pro-controllers'],
            ['name' => 'VR Headsets',          'slug' => 'vr-headsets'],
            ['name' => 'Mechanical Keyboards', 'slug' => 'gaming-keyboards-mice'],
         ],
      ];

      $elt_prepared_cats = [];
      foreach ($elt_header_cats as $idx => $hc) {
         $c_name = $hc['name'] ?? 'Electronics';
         $c_slug = $hc['slug'] ?? 'electronics';
         $c_key_lower = strtolower($c_name . ' ' . $c_slug);
         $subs = !empty($hc['children']) ? $hc['children'] : [];

         if (empty($subs)) {
            if (strpos($c_key_lower, 'headphone') !== false || strpos($c_key_lower, 'audio') !== false) {
               $subs = $elt_subcat_map['headphones'];
            } elseif (strpos($c_key_lower, 'watch') !== false || strpos($c_key_lower, 'wearable') !== false) {
               $subs = $elt_subcat_map['watch'];
            } elseif (strpos($c_key_lower, 'phone') !== false || strpos($c_key_lower, 'mobile') !== false) {
               $subs = $elt_subcat_map['phone'];
            } elseif (strpos($c_key_lower, 'laptop') !== false || strpos($c_key_lower, 'macbook') !== false || strpos($c_key_lower, 'pc') !== false) {
               $subs = $elt_subcat_map['laptop'];
            } elseif (strpos($c_key_lower, 'camera') !== false || strpos($c_key_lower, 'drone') !== false) {
               $subs = $elt_subcat_map['camera'];
            } elseif (strpos($c_key_lower, 'gaming') !== false || strpos($c_key_lower, 'console') !== false) {
               $subs = $elt_subcat_map['gaming'];
            } else {
               $subs = [
                  ['name' => 'New Releases',    'slug' => $c_slug],
                  ['name' => 'Best Sellers',    'slug' => $c_slug],
                  ['name' => 'Top Rated',       'slug' => $c_slug],
                  ['name' => 'Deals & Offers',  'slug' => $c_slug],
                  ['name' => 'Accessories',     'slug' => $c_slug],
               ];
            }
         }

         $fallback_cat_idx = (($idx) % 7) + 1;
         $c_img = elt_resolve_img($hc['image'] ?? '', 'img/electronics/category/cat-' . $fallback_cat_idx . '.jpg');

         $elt_icon = 'fa-microchip';
         if (strpos($c_key_lower, 'headphone') !== false || strpos($c_key_lower, 'audio') !== false) {
            $elt_icon = 'fa-headphones-simple';
         } elseif (strpos($c_key_lower, 'watch') !== false || strpos($c_key_lower, 'wearable') !== false) {
            $elt_icon = 'fa-clock';
         } elseif (strpos($c_key_lower, 'phone') !== false || strpos($c_key_lower, 'mobile') !== false) {
            $elt_icon = 'fa-mobile-screen-button';
         } elseif (strpos($c_key_lower, 'laptop') !== false || strpos($c_key_lower, 'macbook') !== false || strpos($c_key_lower, 'pc') !== false) {
            $elt_icon = 'fa-laptop';
         } elseif (strpos($c_key_lower, 'camera') !== false || strpos($c_key_lower, 'drone') !== false) {
            $elt_icon = 'fa-camera';
         } elseif (strpos($c_key_lower, 'gaming') !== false || strpos($c_key_lower, 'console') !== false) {
            $elt_icon = 'fa-gamepad';
         }

         $elt_prepared_cats[] = [
            'id'       => (int)($hc['id'] ?? ($idx + 1)),
            'name'     => $c_name,
            'slug'     => $c_slug,
            'icon'     => $elt_icon,
            'image'    => $c_img,
            'fallback' => $bazaro . 'img/electronics/category/cat-' . $fallback_cat_idx . '.jpg',
            'subs'     => $subs,
         ];
      }
      ?>

      <?php if (!empty($elt_prepared_cats)): ?>
      <style>
         /* =========================================================
            Dark Tech Bar + All-Departments Split Flyout
            + Floating Hover Subcategory Popover Cards
            ========================================================= */
         .elt-v2-navbar {
            background: #0B0D0E;
            color: #ffffff;
            position: relative;
            z-index: 1025;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
         }
         .elt-v2-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-height: 50px;
         }
         /* Left All Departments Trigger & Split Mega Dropdown */
         .elt-v2-dept-wrap {
            position: relative;
            flex-shrink: 0;
         }
         .elt-v2-dept-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #E1EEB7;
            color: #0B0D0E;
            font-size: 13.5px;
            font-weight: 700;
            padding: 9px 18px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
         }
         .elt-v2-dept-btn:hover {
            background: #d4e798;
         }
         .elt-v2-dept-flyout {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            width: 660px;
            max-width: 92vw;
            background: #ffffff;
            color: #111827;
            border-radius: 14px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.18);
            border: 1px solid #e5e7eb;
            display: flex;
            overflow: hidden;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translateY(8px);
            transition: all 0.2s ease;
            z-index: 1100;
         }
         .elt-v2-dept-wrap::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            height: 12px;
         }
         .elt-v2-dept-wrap:hover .elt-v2-dept-flyout {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateY(0);
         }
         .elt-v2-dept-sidebar {
            width: 235px;
            background: #f8fafc;
            border-right: 1px solid #eef1f6;
            padding: 10px 8px;
            flex-shrink: 0;
         }
         .elt-v2-dept-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            color: #334155;
            text-decoration: none;
            transition: all 0.15s ease;
            cursor: pointer;
         }
         .elt-v2-dept-item i.dept-icon {
            width: 20px;
            text-align: center;
            color: #64748b;
            transition: color 0.15s ease;
         }
         .elt-v2-dept-item:hover,
         .elt-v2-dept-item.is-active {
            background: #0B0D0E;
            color: #ffffff;
         }
         .elt-v2-dept-item:hover i.dept-icon,
         .elt-v2-dept-item.is-active i.dept-icon {
            color: #E1EEB7;
         }
         .elt-v2-dept-content {
            flex: 1;
            padding: 20px 22px;
            background: #ffffff;
         }
         .elt-v2-dept-pane {
            display: none;
         }
         .elt-v2-dept-pane.is-active {
            display: block;
         }
         .elt-v2-pane-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 12px;
            margin-bottom: 14px;
         }
         .elt-v2-pane-header h5 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #0B0D0E;
         }
         .elt-v2-sub-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px 14px;
         }
         .elt-v2-sub-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 10px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            color: #334155;
            text-decoration: none;
            background: #f8fafc;
            border: 1px solid transparent;
            transition: all 0.15s ease;
         }
         .elt-v2-sub-item:hover {
            background: #f0f5ff;
            border-color: #cbd5e1;
            color: #0B0D0E;
            transform: translateX(2px);
         }

         /* Horizontal Category Tabs with Floating Subcategory Popover */
         .elt-v2-nav-list {
            display: flex;
            align-items: center;
            gap: 4px;
            margin: 0;
            padding: 0;
            list-style: none;
            flex-grow: 1;
            justify-content: center;
         }
         .elt-v2-nav-item {
            position: relative;
         }
         .elt-v2-nav-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 14px;
            color: rgba(255, 255, 255, 0.88);
            font-size: 13.5px;
            font-weight: 500;
            text-decoration: none;
            white-space: nowrap;
            transition: color 0.18s ease;
            position: relative;
         }
         .elt-v2-nav-link i.cat-tab-icon {
            color: #E1EEB7;
            font-size: 13px;
         }
         .elt-v2-nav-link .cat-tab-arrow {
            font-size: 9px;
            opacity: 0.65;
            transition: transform 0.2s ease;
         }
         .elt-v2-nav-item:hover .elt-v2-nav-link {
            color: #E1EEB7;
         }
         .elt-v2-nav-item:hover .elt-v2-nav-link .cat-tab-arrow {
            transform: rotate(180deg);
            opacity: 1;
         }
         .elt-v2-nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 14px;
            right: 14px;
            height: 2.5px;
            background: #E1EEB7;
            transform: scaleX(0);
            transition: transform 0.2s ease;
         }
         .elt-v2-nav-item:hover .elt-v2-nav-link::after {
            transform: scaleX(1);
         }

         /* Floating Subcategory Popover under each Horizontal Tab */
         .elt-v2-popover {
            position: absolute;
            top: 100%;
            left: 0;
            width: 390px;
            background: #ffffff;
            color: #111827;
            border-radius: 0 0 14px 14px;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.18);
            border: 1px solid #e5e7eb;
            border-top: 2px solid #E1EEB7;
            padding: 16px;
            display: flex;
            gap: 16px;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translateY(8px);
            transition: all 0.18s ease;
            z-index: 1100;
         }
         .elt-v2-nav-item:nth-last-child(-n+2) .elt-v2-popover {
            left: auto;
            right: 0;
         }
         .elt-v2-nav-item:hover .elt-v2-popover {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateY(0);
         }
         .elt-v2-pop-card {
            width: 125px;
            background: #f8fafc;
            border-radius: 10px;
            padding: 12px 10px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 1px solid #eef1f6;
         }
         .elt-v2-pop-card img {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 8px;
            background: #fff;
            border: 2px solid #E1EEB7;
         }
         .elt-v2-pop-card span {
            font-size: 12px;
            font-weight: 700;
            color: #0B0D0E;
            line-height: 1.25;
            margin-bottom: 8px;
         }
         .elt-v2-pop-card a {
            font-size: 11px;
            font-weight: 700;
            color: #0B0D0E;
            background: #E1EEB7;
            padding: 4px 10px;
            border-radius: 20px;
            text-decoration: none;
         }
         .elt-v2-pop-links {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 3px;
         }
         .elt-v2-pop-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94a3b8;
            margin-bottom: 4px;
            padding-left: 8px;
         }
         .elt-v2-pop-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            color: #334155;
            text-decoration: none;
            transition: all 0.15s ease;
         }
         .elt-v2-pop-link i {
            font-size: 10px;
            opacity: 0;
            transform: translateX(-4px);
            transition: all 0.15s ease;
            color: #0B0D0E;
         }
         .elt-v2-pop-link:hover {
            background: #f1f5f9;
            color: #0B0D0E;
            font-weight: 600;
         }
         .elt-v2-pop-link:hover i {
            opacity: 1;
            transform: translateX(0);
         }
         @media (max-width: 991.98px) {
            .elt-v2-nav-list {
               overflow-x: auto;
               justify-content: flex-start;
               scrollbar-width: none;
            }
            .elt-v2-nav-list::-webkit-scrollbar {
               display: none;
            }
            .elt-v2-popover {
               display: none !important;
            }
         }
         /* Sticky Top Header Row (Logo + Search Bar + Icons ONLY) */
         header.elt-site-header {
            position: relative !important;
            padding-top: 74px;
         }
         .aq-header-area.elt-sticky-top-bar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            z-index: 1050 !important;
            background-color: #ffffff !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.07);
         }
         @media (max-width: 767.98px) {
            header.elt-site-header {
               padding-top: 122px;
            }
         }
      </style>

      <!-- Dark Tech Bar + All Departments Split Flyout + Hover Subcategory Popover Cards -->
      <div class="elt-v2-navbar" id="eltCatSubcatNavV2">
         <div class="container">
            <div class="elt-v2-inner">
               <!-- Left: All Departments Split Flyout -->
               <div class="elt-v2-dept-wrap">
                  <button type="button" class="elt-v2-dept-btn">
                     <i class="fa-solid fa-bars-staggered"></i>
                     <span>All Categories</span>
                     <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i>
                  </button>

                  <?php $elt_qs = empty($is_user_project) ? '?preview_tpl=electronics' : ''; ?>
                  <div class="elt-v2-dept-flyout">
                     <!-- Left Category List -->
                     <div class="elt-v2-dept-sidebar">
                        <?php foreach ($elt_prepared_cats as $c_idx => $cat_item): ?>
                           <a href="<?= site_url('shop/' . $cat_item['slug'] . $elt_qs); ?>"
                              class="elt-v2-dept-item <?= $c_idx === 0 ? 'is-active' : ''; ?>"
                              data-v2-pane="eltV2Pane-<?= $cat_item['id']; ?>">
                              <span class="d-flex align-items-center gap-2">
                                 <i class="fa-solid <?= $cat_item['icon']; ?> dept-icon"></i>
                                 <?= html_escape($cat_item['name']); ?>
                              </span>
                              <i class="fa-solid fa-chevron-right" style="font-size:10px;opacity:0.6;"></i>
                           </a>
                        <?php endforeach; ?>
                     </div>

                     <!-- Right Subcategories Panel -->
                     <div class="elt-v2-dept-content">
                        <?php foreach ($elt_prepared_cats as $c_idx => $cat_item): ?>
                           <div class="elt-v2-dept-pane <?= $c_idx === 0 ? 'is-active' : ''; ?>" id="eltV2Pane-<?= $cat_item['id']; ?>">
                              <div class="elt-v2-pane-header">
                                 <div class="d-flex align-items-center gap-2">
                                    <img src="<?= $cat_item['image']; ?>"
                                         alt="<?= html_escape($cat_item['name']); ?>"
                                         style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:1px solid #e2e8f0;"
                                         onerror="this.src='<?= $cat_item['fallback']; ?>'">
                                    <h5><?= html_escape($cat_item['name']); ?></h5>
                                 </div>
                                 <a href="<?= site_url('shop/' . $cat_item['slug'] . $elt_qs); ?>" class="small fw-bold text-decoration-none" style="color:#2874f0;">
                                    Explore All <i class="fa-solid fa-arrow-right ms-1"></i>
                                 </a>
                              </div>
                              <div class="elt-v2-sub-grid">
                                 <?php foreach ($cat_item['subs'] as $sub): ?>
                                    <a href="<?= site_url('shop/' . ($sub['slug'] ?? $cat_item['slug']) . $elt_qs); ?>" class="elt-v2-sub-item">
                                       <i class="fa-solid fa-angle-right text-muted" style="font-size:11px;"></i>
                                       <span><?= html_escape($sub['name']); ?></span>
                                    </a>
                                 <?php endforeach; ?>
                              </div>
                           </div>
                        <?php endforeach; ?>
                     </div>
                  </div>
               </div>

               <!-- Center/Right: Horizontal Category Tabs with Floating Subcategory Popover -->
               <ul class="elt-v2-nav-list">
                  <?php foreach ($elt_prepared_cats as $cat_item): ?>
                     <li class="elt-v2-nav-item">
                        <a href="<?= site_url('shop/' . $cat_item['slug'] . $elt_qs); ?>" class="elt-v2-nav-link">
                           <i class="fa-solid <?= $cat_item['icon']; ?> cat-tab-icon"></i>
                           <span><?= html_escape($cat_item['name']); ?></span>
                           <i class="fa-solid fa-chevron-down cat-tab-arrow"></i>
                        </a>
                        <!-- Floating Subcategory Card -->
                        <div class="elt-v2-popover">
                           <div class="elt-v2-pop-card">
                              <img src="<?= $cat_item['image']; ?>"
                                   alt="<?= html_escape($cat_item['name']); ?>"
                                   onerror="this.src='<?= $cat_item['fallback']; ?>'">
                              <span><?= html_escape($cat_item['name']); ?></span>
                              <a href="<?= site_url('shop/' . $cat_item['slug'] . $elt_qs); ?>">Shop All</a>
                           </div>
                           <div class="elt-v2-pop-links">
                              <div class="elt-v2-pop-title">Subcategories</div>
                              <?php foreach ($cat_item['subs'] as $sub): ?>
                                 <a href="<?= site_url('shop/' . ($sub['slug'] ?? $cat_item['slug']) . $elt_qs); ?>" class="elt-v2-pop-link">
                                    <span><?= html_escape($sub['name']); ?></span>
                                    <i class="fa-solid fa-arrow-right"></i>
                                 </a>
                              <?php endforeach; ?>
                           </div>
                        </div>
                     </li>
                  <?php endforeach; ?>
               </ul>
            </div>
         </div>
      </div>
      <?php endif; ?>

      <script>
      document.addEventListener('DOMContentLoaded', function() {
<?php if (empty($is_user_project)): ?>
         var siteBase = '<?= rtrim(base_url(), "/"); ?>';
         document.querySelectorAll('a[href]').forEach(function(a) {
            var href = a.getAttribute('href');
            if (!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0 || href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0) return;
            if (href.indexOf(siteBase) === 0 && href.indexOf('preview_tpl=') === -1) {
               var sep = href.indexOf('?') === -1 ? '?' : '&';
               a.setAttribute('href', href + sep + 'preview_tpl=electronics');
            }
         });
<?php endif; ?>

         // 0. Sync Sticky Top Bar (Logo + Search Bar) Height with Header Padding
         var stickyTopBar = document.querySelector('.aq-header-area.elt-sticky-top-bar');
         var siteHeader = document.querySelector('header.elt-site-header');
         function syncStickyHeaderOffset() {
            if (stickyTopBar && siteHeader) {
               var h = stickyTopBar.offsetHeight;
               if (h > 0) {
                  siteHeader.style.setProperty('padding-top', h + 'px', 'important');
               }
            }
         }
         syncStickyHeaderOffset();
         window.addEventListener('resize', syncStickyHeaderOffset);
         window.addEventListener('load', syncStickyHeaderOffset);

         // 1. All Categories Split Flyout Hover Switching
         var deptItems = document.querySelectorAll('.elt-v2-dept-item[data-v2-pane]');
         var deptPanes = document.querySelectorAll('.elt-v2-dept-pane');
         if (deptItems.length > 0 && deptPanes.length > 0) {
            deptItems.forEach(function(item) {
               item.addEventListener('mouseenter', function() {
                  var paneId = this.getAttribute('data-v2-pane');
                  deptItems.forEach(function(i) { i.classList.remove('is-active'); });
                  deptPanes.forEach(function(p) { p.classList.remove('is-active'); });
                  this.classList.add('is-active');
                  var targetPane = document.getElementById(paneId);
                  if (targetPane) {
                     targetPane.classList.add('is-active');
                  }
               });
            });
         }

         // 2. Search Autocomplete
         var searchInput = document.getElementById('storeNavSearchInput');
         var resultsDropdown = document.getElementById('storeAutocompleteResults');
         var autoTimer = null;

         if (searchInput && resultsDropdown) {
            searchInput.addEventListener('input', function() {
               clearTimeout(autoTimer);
               var query = this.value.trim();
               if (query.length < 2) {
                  resultsDropdown.classList.add('d-none');
                  resultsDropdown.innerHTML = '';
                  return;
               }

               autoTimer = setTimeout(function() {
                  var autoUrl = '<?= site_url("shop/autocomplete"); ?><?= empty($is_user_project) ? "?preview_tpl=electronics&q=" : "?q="; ?>' + encodeURIComponent(query);
                  fetch(autoUrl)
                     .then(function(res) { return res.json(); })
                     .then(function(data) {
                        if (data.results && data.results.length > 0) {
                           var html = '';
                           data.results.forEach(function(item) {
                              var itemUrl = item.url || '#';
                              <?php if (empty($is_user_project)): ?>
                              if (itemUrl.indexOf('preview_tpl=') === -1 && itemUrl !== '#') {
                                 itemUrl += (itemUrl.indexOf('?') === -1 ? '?' : '&') + 'preview_tpl=electronics';
                              }
                              <?php endif; ?>
                              html += '<a href="' + itemUrl + '" class="store-auto-item">';
                              html += '  <img src="' + item.image + '" class="store-auto-img" alt="' + item.title + '" onerror="this.src=\'<?= $bazaro; ?>img/electronics/arrival-product/product-1/product-1.jpg\'">';
                              html += '  <div class="flex-grow-1 overflow-hidden">';
                              html += '    <div class="store-auto-title">' + item.title + '</div>';
                              html += '    <div class="store-auto-cat">' + (item.category_name || 'Electronics') + '</div>';
                              html += '  </div>';
                              html += '  <div class="store-auto-price">' + item.price_html + '</div>';
                              html += '</a>';
                           });
                           var viewAllUrl = '<?= site_url("shop"); ?><?= empty($is_user_project) ? "?preview_tpl=electronics&q=" : "?q="; ?>' + encodeURIComponent(query);
                           html += '<a href="' + viewAllUrl + '" class="store-auto-viewall">';
                           html += '  View all results for "<strong>' + query.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</strong>" <i class="fa-solid fa-arrow-right ms-1"></i>';
                           html += '</a>';

                           resultsDropdown.innerHTML = html;
                           resultsDropdown.classList.remove('d-none');
                        } else {
                           resultsDropdown.innerHTML = '<div class="p-3 text-center text-muted small">No products found for "' + query.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '"</div>';
                           resultsDropdown.classList.remove('d-none');
                        }
                     })
                     .catch(function() {
                        resultsDropdown.classList.add('d-none');
                     });
               }, 220);
            });

            document.addEventListener('click', function(e) {
               if (!searchInput.contains(e.target) && !resultsDropdown.contains(e.target)) {
                  resultsDropdown.classList.add('d-none');
               }
            });
         }
      });
      </script>

   </header>
