<!doctype html>
<html class="no-js" lang="en">

<head>
   <meta charset="utf-8">
   <meta http-equiv="x-ua-compatible" content="ie=edge">
   <title><?= isset($title) ? html_escape($title) : html_escape($site_name ?? 'Fresh Grocery Store'); ?></title>
   <meta name="description" content="<?= html_escape($site_name ?? 'Fresh Grocery Store'); ?> - Farm-Fresh Organic Groceries &amp; Daily Essentials">
   <meta name="viewport" content="width=device-width, initial-scale=1">

   <?php
   $bazaro = isset($tpl_assets) ? rtrim($tpl_assets, '/') . '/' : base_url('assets/templates/bazaro/');
   $curr   = isset($currency_symbol) ? $currency_symbol : '$';
   $is_home_page = (($active_page ?? 'home') === 'home');
   $raw_fav = trim((string)($store_settings['site_favicon'] ?? ''));
   if ($raw_fav !== '') {
       $web_favicon = (strpos($raw_fav, 'assets/') === 0) ? base_url($raw_fav) : base_url('assets/images/logo/' . $raw_fav);
   } else {
       $web_favicon = base_url('assets/images/logo/codeulas_logo_small.webp');
   }
   $raw_logo = trim((string)($store_settings['site_logo'] ?? ''));
   if ($raw_logo !== '') {
       $web_logo = (strpos($raw_logo, 'assets/') === 0) ? base_url($raw_logo) : base_url('assets/images/logo/' . $raw_logo);
   } else {
       $web_logo = base_url('assets/images/logo/logo.webp');
   }
   ?>
   <link rel="shortcut icon" href="<?= $web_favicon; ?>">
   <link rel="icon" href="<?= $web_favicon; ?>">

   <?php
   $is_grc_product_page = (($this->uri->segment(1) ?? '') === 'product');
   ?>
   <link rel="stylesheet" href="<?= base_url('assets/fonts/font-icons.css'); ?>">
   <link rel="stylesheet" href="<?= base_url('assets/fonts/fonts.css'); ?>">

   <!-- Bazaro Grocery Template CSS -->
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

   <style>
      :root {
         --grc-primary: #003D29;
         --grc-accent: #108910;
         --grc-mint: #ecfdf5;
         --grc-mint-border: #bbf7d0;
         --grc-bg-soft: #f4f8f5;
         --bs-primary: #108910;
         --bs-primary-rgb: 16, 137, 16;
      }
      .grc-unit-pill {
         display: inline-block;
         background: #ecfdf5;
         color: #047857;
         border: 1px solid #a7f3d0;
         font-size: 11px;
         font-weight: 700;
         padding: 2px 8px;
         border-radius: 99px;
         margin-bottom: 5px;
      }
      .aq-product-thumb img.aq-product-img-hover {
         height: 210px;
         width: 100%;
         object-fit: cover;
         border-radius: 10px;
      }
      .grc-toast-msg {
         position: fixed;
         bottom: 24px;
         right: 24px;
         z-index: 99999;
         background: #003D29;
         color: #fff;
         padding: 12px 20px;
         border-radius: 10px;
         font-size: 14px;
         font-weight: 600;
         box-shadow: 0 10px 25px rgba(0,0,0,0.25);
         display: none;
         align-items: center;
         gap: 10px;
      }
      /* Ensure preloader never blocks interaction if JS is slow */
      #loading { display: none !important; }

      .btn-grc-wish.active,
      .btn-grc-wish.in-wishlist {
         border-color: #ef4444 !important;
         background: #fef2f2 !important;
         color: #ef4444 !important;
      }
      .btn-grc-wish.active i,
      .btn-grc-wish.in-wishlist i {
         color: #ef4444 !important;
      }
      .grc-product-style .aq-product-action-2 .aq-wishlist-btn.active,
      .grc-product-style .aq-product-action-2 .aq-wishlist-btn.in-wishlist {
         background: #fee2e2 !important;
         color: #ef4444 !important;
      }
      .grc-product-style .aq-product-action-2 .aq-wishlist-btn.active svg path,
      .grc-product-style .aq-product-action-2 .aq-wishlist-btn.in-wishlist svg path {
         fill: #ef4444 !important;
         stroke: #ef4444 !important;
      }

      /* =====================================================================
       * Grocery Theme Overrides for Shared Inner Pages
       * (Account, Orders, Wishlist, Cart, Checkout, Track Order, Static Pages)
       * ===================================================================== */
      body {
         color: #1f2937;
      }
      section[style*="background: #f1f3f6"],
      section[style*="background:#f1f3f6"],
      .flat-spacing[style*="background: #f1f3f6"] {
         background: #f3f8f4 !important;
      }
      /* Primary colors -> Fresh Harvest Green / Deep Forest Green */
      .text-primary {
         color: #108910 !important;
      }
      .bg-primary {
         background-color: #003D29 !important;
      }
      .border-primary {
         border-color: #108910 !important;
      }
      .btn-primary {
         background-color: #108910 !important;
         border-color: #108910 !important;
         color: #ffffff !important;
         font-weight: 600;
         border-radius: 10px;
         transition: all 0.2s ease;
      }
      .btn-primary:hover,
      .btn-primary:focus {
         background-color: #003D29 !important;
         border-color: #003D29 !important;
         color: #ffffff !important;
      }
      .btn-outline-primary {
         color: #003D29 !important;
         border-color: #108910 !important;
         font-weight: 600;
         border-radius: 10px;
      }
      .btn-outline-primary:hover,
      .btn-outline-primary:focus {
         background-color: #108910 !important;
         border-color: #108910 !important;
         color: #ffffff !important;
      }
      /* Account Sidebar & Profile Cards */
      .fk-sidebar-wrap .card,
      section .card.border-0.shadow-sm {
         border: 1px solid #e2efe6 !important;
         border-radius: 14px !important;
         box-shadow: 0 6px 20px rgba(0, 61, 41, 0.04) !important;
      }
      .fk-menu-group-header {
         color: #003D29 !important;
      }
      .fk-menu-group-header i {
         color: #108910 !important;
      }
      .fk-sub-link:hover {
         color: #108910 !important;
         background-color: #f0fdf4 !important;
      }
      .fk-sub-link.active {
         color: #003D29 !important;
         background-color: #ecfdf5 !important;
         font-weight: 700 !important;
         border-left: 3px solid #108910;
      }
      .fk-Single-link:hover {
         color: #108910 !important;
         background-color: #f0fdf4 !important;
      }
      .fk-input-control:focus,
      .form-control:focus,
      .form-select:focus {
         border-color: #108910 !important;
         box-shadow: 0 0 0 3px rgba(16, 137, 16, 0.12) !important;
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
         border: 1.5px solid #9ca3af !important;
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
         background-color: #108910 !important;
         border-color: #108910 !important;
         accent-color: #108910 !important;
      }
      input[type="checkbox"]:focus,
      input[type="radio"]:focus,
      .form-check-input:focus {
         border-color: #108910 !important;
         box-shadow: 0 0 0 3px rgba(16, 137, 16, 0.15) !important;
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
         accent-color: #108910 !important;
      }
      /* Cart & Checkout Grocery Theme Styling */
      .cart-address-card,
      .cart-items-card,
      .cart-summary-card,
      .fk-step-card {
         border: 1px solid #e2efe6 !important;
         border-radius: 14px !important;
         box-shadow: 0 6px 20px rgba(0, 61, 41, 0.04) !important;
      }
      .fk-step-header.active {
         background: #003D29 !important;
         color: #ffffff !important;
         border-radius: 14px 14px 0 0;
      }
      .fk-step-header.active .step-num {
         background: #108910 !important;
         color: #ffffff !important;
      }
      .step-num {
         color: #003D29 !important;
         background: #ecfdf5 !important;
      }
      .btn-deliver-here,
      .tf-btn.btn-fill {
         background: #108910 !important;
         border-color: #108910 !important;
         color: #ffffff !important;
         border-radius: 10px !important;
         font-weight: 700 !important;
         box-shadow: 0 4px 14px rgba(16, 137, 16, 0.22) !important;
      }
      .btn-deliver-here:hover,
      .tf-btn.btn-fill:hover {
         background: #003D29 !important;
         border-color: #003D29 !important;
         color: #ffffff !important;
      }
      /* Proceed To Buy Button Style - Theme Color with Simple Hover Effect */
      .btn-proceed-buy {
         background: #108910 !important;
         background-color: #108910 !important;
         border: 1px solid #108910 !important;
         border-radius: 10px !important;
         color: #ffffff !important;
         font-weight: 700 !important;
         font-size: 15px !important;
         padding: 12px 24px !important;
         box-shadow: 0 4px 14px rgba(16, 137, 16, 0.2) !important;
         transition: background-color 0.2s ease, border-color 0.2s ease !important;
         text-decoration: none;
         display: inline-flex;
         align-items: center;
         justify-content: center;
         cursor: pointer;
         letter-spacing: 0.2px;
      }
      .btn-proceed-buy:hover,
      .btn-proceed-buy:focus {
         background: #003D29 !important;
         background-color: #003D29 !important;
         border-color: #003D29 !important;
         color: #ffffff !important;
      }
      .btn-change-addr {
         color: #108910 !important;
         border-color: #bbf7d0 !important;
         background: #f0fdf4 !important;
         border-radius: 8px !important;
      }
      .btn-change-addr:hover {
         background: #108910 !important;
         color: #ffffff !important;
      }
      .address-type-badge {
         background: #ecfdf5 !important;
         color: #047857 !important;
         border: 1px solid #a7f3d0;
      }
      .fk-addr-item.selected,
      .fk-pay-item.selected {
         background: #f4fbf6 !important;
         border-color: #bbf7d0 !important;
      }
      .qty-circle-btn:hover:not(:disabled) {
         border-color: #108910 !important;
         color: #108910 !important;
      }
      .item-action-btn:hover {
         color: #108910 !important;
      }
      /* Inner page titles / breadcrumbs */
      .tf-page-title {
         background: linear-gradient(135deg, #003D29 0%, #07593e 100%) !important;
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
         color: #86efac !important;
      }
      /* Alert Close Button Normalization */
      .alert {
         position: relative;
      }
      .alert .btn-close,
      .alert-dismissible .btn-close {
         position: absolute !important;
         top: 50% !important;
         right: 14px !important;
         transform: translateY(-50%) !important;
         padding: 0 !important;
         margin: 0 !important;
         width: 24px !important;
         height: 24px !important;
         font-size: 13px !important;
         line-height: 24px !important;
         opacity: 0.65 !important;
         border: none !important;
         background: transparent !important;
      }
      .alert .btn-close:hover,
      .alert-dismissible .btn-close:hover {
         opacity: 1 !important;
         transform: translateY(-50%) !important;
      }
      .btn-close-alert {
         background: transparent !important;
         border: none !important;
         padding: 4px 8px !important;
         margin: 0 !important;
         color: inherit !important;
         opacity: 0.6 !important;
         font-size: 16px !important;
         line-height: 1 !important;
         cursor: pointer !important;
         transition: opacity 0.2s ease !important;
      }
      .btn-close-alert:hover {
         opacity: 1 !important;
      }
      .breadcrumb-item + .breadcrumb-item::before {
         content: var(--bs-breadcrumb-divider, "/") !important;
      }
      /* Active Wishlist Heart State */
      .aq-wishlist-btn.active,
      .aq-wishlist-btn.in-wishlist,
      .btn-grc-wish.active,
      .btn-grc-wish.in-wishlist {
         color: #ef4444 !important;
         border-color: #ef4444 !important;
      }
      .aq-wishlist-btn.active i,
      .aq-wishlist-btn.in-wishlist i,
      .btn-grc-wish.active i,
      .btn-grc-wish.in-wishlist i {
         color: #ef4444 !important;
      }
      .aq-wishlist-btn.active svg,
      .aq-wishlist-btn.in-wishlist svg,
      .btn-grc-wish.active svg,
      .btn-grc-wish.in-wishlist svg {
         fill: #ef4444 !important;
         stroke: #ef4444 !important;
      }
      .aq-wishlist-btn.active svg path,
      .aq-wishlist-btn.in-wishlist svg path,
      .btn-grc-wish.active svg path,
      .btn-grc-wish.in-wishlist svg path {
         fill: #ef4444 !important;
         stroke: #ef4444 !important;
      }
      /* Order status timeline & order cards */
      .fk-order-card:hover {
         border-color: #108910 !important;
         box-shadow: 0 6px 18px rgba(0, 61, 41, 0.08) !important;
      }
      .fk-item-title:hover {
         color: #108910 !important;
      }
      .fk-step-item.completed .fk-step-circle,
      .fk-step-item.active .fk-step-circle {
         background: #108910 !important;
         border-color: #108910 !important;
      }
      .fk-step-item.completed:not(:last-child)::after {
         background: #108910 !important;
      }

      /* =====================================================================
       * Sticky Main Header (Logo, Search, User, Wishlist, Cart)
       * ===================================================================== */
      .grc-main-sticky-header {
         position: sticky !important;
         top: 0 !important;
         left: 0 !important;
         right: 0 !important;
         width: 100% !important;
         z-index: 1050 !important;
         background-color: #003D29 !important;
         transition: box-shadow 0.25s ease;
      }
      .grc-main-sticky-header.is-scrolled {
         box-shadow: 0 6px 24px rgba(0, 0, 0, 0.35) !important;
      }
      .grc-main-sticky-header .grc-header-main-nav {
         transition: padding 0.2s ease;
      }
      .grc-main-sticky-header.is-scrolled .grc-header-main-nav {
         padding-top: 10px !important;
         padding-bottom: 10px !important;
      }

      /* Category Aisles Bar underneath sticky header */
      .grc-category-aisles-bar {
         background-color: #003D29 !important;
         border-top: 1px solid rgba(255, 255, 255, 0.08);
         position: relative;
         z-index: 1040;
      }
      .grc-category-aisles-bar .grc-categories-wrap {
         gap: 4px;
         flex-wrap: nowrap !important;
      }
      @media (max-width: 1399px) {
         .grc-category-aisles-bar .grc-categories-wrap {
            flex-wrap: wrap !important;
            justify-content: center !important;
         }
      }
      .grc-category-aisles-bar .grc-categories-item {
         position: relative;
      }
      .grc-category-aisles-bar .grc-categories-item > a {
         color: #EAFDE8 !important;
         padding: 12px 14px !important;
         display: inline-flex !important;
         flex-direction: column;
         align-items: center;
         justify-content: center;
         border-radius: 10px !important;
         text-decoration: none !important;
         transition: background-color 0.2s ease, transform 0.2s ease !important;
      }
      .grc-category-aisles-bar .grc-categories-item:hover > a,
      .grc-category-aisles-bar .grc-categories-item > a.show {
         background-color: #0F4936 !important;
         color: #ffffff !important;
      }
      .grc-category-aisles-bar .grc-categories-item > a .grc-categories-icon {
         margin-bottom: 8px !important;
         height: 28px !important;
         display: flex !important;
         align-items: center;
         justify-content: center;
      }
      .grc-category-aisles-bar .grc-categories-item > a .grc-categories-title {
         display: inline-flex !important;
         align-items: center;
         gap: 5px;
         font-weight: 500 !important;
         font-size: 14px !important;
         line-height: 1.2 !important;
         color: #ffffff !important;
         white-space: nowrap;
         text-transform: capitalize;
      }
      .grc-category-aisles-bar .grc-more-cats-dropdown > a {
         padding: 8px 10px !important;
         border-radius: 50% !important;
      }
      .grc-category-aisles-bar .grc-more-cats-dropdown > a .grc-categories-icon {
         margin-bottom: 0 !important;
         width: 36px !important;
         height: 36px !important;
         border-radius: 50% !important;
         background: rgba(255, 255, 255, 0.16) !important;
         transition: background-color 0.2s ease, transform 0.2s ease !important;
      }
      .grc-category-aisles-bar .grc-more-cats-dropdown > a:hover .grc-categories-icon,
      .grc-category-aisles-bar .grc-more-cats-dropdown > a.show .grc-categories-icon {
         background: #108910 !important;
      }
      .grc-category-aisles-bar .dropdown-toggle.hide-arrow::after {
         display: none !important;
      }

      /* More Categories Dropdown Menu (Click Only) */
      .grc-category-aisles-bar .grc-more-cats-dropdown .dropdown-menu {
         background: #ffffff !important;
         border: 1px solid #d1fae5 !important;
         border-top: 3px solid #108910 !important;
         border-radius: 14px !important;
         box-shadow: 0 18px 40px rgba(0, 61, 41, 0.22) !important;
         padding: 8px !important;
         min-width: 245px !important;
         margin-top: 8px !important;
         z-index: 1055 !important;
         text-align: left !important;
      }
      .grc-category-aisles-bar .grc-more-cats-dropdown .dropdown-menu.show {
         animation: grcDropdownFadeIn 0.18s ease-out;
      }
      @keyframes grcDropdownFadeIn {
         from {
            opacity: 0;
            transform: translateY(6px);
         }
         to {
            opacity: 1;
            transform: translateY(0);
         }
      }
      /* Prevent main.css (.grc-categories-item a) from bleeding into dropdown links */
      .grc-category-aisles-bar .grc-more-cats-dropdown .dropdown-menu a.dropdown-item {
         display: flex !important;
         align-items: center !important;
         gap: 11px !important;
         padding: 8px 11px !important;
         border-radius: 10px !important;
         color: #1f2937 !important;
         background-color: transparent !important;
         font-size: 13.5px !important;
         font-weight: 600 !important;
         line-height: 1.3 !important;
         text-decoration: none !important;
         text-transform: capitalize;
         white-space: nowrap;
         transition: background-color 0.16s ease, color 0.16s ease, transform 0.16s ease !important;
      }
      .grc-category-aisles-bar .grc-more-cats-dropdown .dropdown-menu a.dropdown-item:hover,
      .grc-category-aisles-bar .grc-more-cats-dropdown .dropdown-menu a.dropdown-item:focus {
         background-color: #ecfdf5 !important;
         color: #003D29 !important;
         transform: translateX(2px);
      }
      .grc-category-aisles-bar .grc-dd-icon-box {
         width: 32px;
         height: 32px;
         border-radius: 8px;
         background: #f0fdf4;
         border: 1px solid #dcfce7;
         display: flex;
         align-items: center;
         justify-content: center;
         flex-shrink: 0;
         transition: background-color 0.16s ease, border-color 0.16s ease;
      }
      .grc-category-aisles-bar a.dropdown-item:hover .grc-dd-icon-box {
         background: #dcfce7;
         border-color: #86efac;
      }
      .grc-category-aisles-bar .grc-dd-arrow {
         margin-left: auto;
         font-size: 11px;
         color: #9ca3af;
         transition: transform 0.16s ease, color 0.16s ease;
      }
      .grc-category-aisles-bar a.dropdown-item:hover .grc-dd-arrow {
         color: #108910;
         transform: translateX(3px);
      }

      /* Strictly 1 line product titles with ellipsis (...) on overflow across all product cards */
      .aq-product-title,
      .aq-product-title a,
      .grc-product-style .aq-product-title,
      .grc-product-style .aq-product-title a,
      .aq-product-item .aq-product-title,
      .aq-product-item .aq-product-title a,
      .aq-product-main .aq-product-title,
      .aq-product-main .aq-product-title a,
      .aq-cartmini-product-summary .aq-product-title,
      .aq-cartmini-product-summary .aq-product-title a {
         display: block !important;
         white-space: nowrap !important;
         overflow: hidden !important;
         text-overflow: ellipsis !important;
         max-width: 100% !important;
         word-break: normal !important;
      }

      /* =====================================================================
       * Modern Search Bar Design (Desktop & Mobile)
       * ===================================================================== */
      .grc-header-search-wrap {
         margin-inline-start: 0 !important;
         position: relative;
         width: 100%;
         margin: 0;
      }
      .grc-search-pill-container {
         position: relative;
         display: flex;
         align-items: center;
         background: #ffffff;
         border: 2px solid transparent;
         border-radius: 50px;
         box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
         height: 48px;
         padding-right: 120px;
         width: 100%;
      }
      .grc-search-form:focus-within .grc-search-pill-container {
         border-color: #108910;
         box-shadow: 0 0 0 3px rgba(16, 137, 16, 0.25);
      }
      .grc-search-glass-icon {
         position: absolute;
         left: 18px;
         top: 50%;
         transform: translateY(-50%);
         color: #047857;
         font-size: 16px;
         pointer-events: none;
         z-index: 2;
         display: flex;
         align-items: center;
         justify-content: center;
      }
      .grc-search-input {
         width: 100% !important;
         height: 100% !important;
         background: transparent !important;
         border: none !important;
         outline: none !important;
         box-shadow: none !important;
         padding-left: 48px !important;
         padding-right: 36px !important;
         font-size: 14.5px !important;
         font-weight: 500 !important;
         color: #111827 !important;
         line-height: normal !important;
      }
      .grc-search-input::placeholder {
         color: #6b7280 !important;
         font-size: 13.5px !important;
         font-weight: 400 !important;
      }
      .grc-search-clear-btn {
         position: absolute;
         right: 125px;
         top: 50%;
         transform: translateY(-50%);
         background: transparent;
         border: none;
         color: #9ca3af;
         font-size: 14px;
         cursor: pointer;
         padding: 4px 6px;
         display: none;
         align-items: center;
         justify-content: center;
         z-index: 2;
         transition: color 0.15s ease;
      }
      .grc-search-clear-btn:hover {
         color: #111827;
      }
      .grc-search-btn-submit {
         position: absolute;
         right: 4px;
         top: 4px;
         bottom: 4px;
         height: calc(100% - 8px);
         border: none;
         background: #108910 !important;
         color: #ffffff !important;
         font-weight: 600;
         font-size: 14px;
         padding: 0 24px;
         border-radius: 50px;
         cursor: pointer;
         display: inline-flex;
         align-items: center;
         justify-content: center;
         gap: 6px;
         transition: background-color 0.2s ease, color 0.2s ease;
         z-index: 2;
      }
      .grc-search-btn-submit:hover,
      .grc-search-btn-submit:focus {
         background: #003D29 !important;
         color: #ffffff !important;
      }

      /* Autocomplete Dropdown */
      .grc-search-autocomplete {
         position: absolute;
         top: calc(100% + 8px);
         left: 0;
         right: 0;
         background: #ffffff;
         border-radius: 14px;
         border: 1px solid #e5e7eb;
         max-height: 400px;
         overflow-y: auto;
         z-index: 1060;
         padding: 8px 0;
         box-shadow: 0 14px 38px rgba(0, 0, 0, 0.25);
         display: none;
      }
      .grc-autocomplete-item {
         display: flex;
         align-items: center;
         padding: 10px 16px;
         text-decoration: none;
         color: #1f2937;
         border-bottom: 1px solid #f3f4f6;
         transition: background 0.15s ease;
      }
      .grc-autocomplete-item:last-of-type {
         border-bottom: none;
      }
      .grc-autocomplete-item:hover,
      .grc-autocomplete-item.active {
         background-color: #f0fdf4;
         color: #003D29;
      }
      .grc-autocomplete-thumb {
         width: 44px;
         height: 44px;
         object-fit: cover;
         border-radius: 8px;
         margin-right: 12px;
         background: #f9fafb;
         border: 1px solid #e5e7eb;
         flex-shrink: 0;
      }
      .grc-autocomplete-info {
         flex: 1;
         min-width: 0;
      }
      .grc-autocomplete-title {
         font-size: 13.5px;
         font-weight: 600;
         color: #1f2937;
         margin-bottom: 2px;
         white-space: nowrap;
         overflow: hidden;
         text-overflow: ellipsis;
      }
      .grc-autocomplete-item:hover .grc-autocomplete-title {
         color: #108910;
      }
      .grc-autocomplete-cat {
         font-size: 11px;
         color: #6b7280;
      }
      .grc-autocomplete-price {
         font-size: 14px;
         font-weight: 700;
         color: #047857;
         margin-left: 12px;
         flex-shrink: 0;
      }
      .grc-autocomplete-footer {
         display: block;
         padding: 10px 16px;
         text-align: center;
         background: #f8fafc;
         color: #108910;
         font-size: 13px;
         font-weight: 700;
         text-decoration: none;
         border-top: 1px solid #e5e7eb;
         border-radius: 0 0 13px 13px;
      }
      .grc-autocomplete-footer:hover {
         background: #ecfdf5;
         color: #003D29;
      }

      /* Hover on header action icons (User, Wishlist, Cart) */
      .aq-header-right-options.grc-header-right-option > ul > li .grc-option-style {
         transition: background-color 0.2s ease, color 0.2s ease !important;
      }
      .aq-header-right-options.grc-header-right-option > ul > li .grc-option-style:hover {
         background: #108910 !important;
         color: #ffffff !important;
      }
      .aq-header-right-options.grc-header-right-option > ul > li .grc-option-style:hover svg path {
         stroke: #ffffff !important;
      }
      .aq-header-right-options.grc-header-right-option .aq-header-top-account-item {
         transition: opacity 0.2s ease;
      }
      .aq-header-right-options.grc-header-right-option .aq-header-top-account-item:hover {
         opacity: 0.92;
      }
   </style>

   <script>
      window.IS_USER_LOGGED_IN = <?= !empty($is_logged_in) ? 'true' : 'false'; ?>;
      window.BASE_URL = '<?= base_url(); ?>';
      window.SITE_URL = '<?= site_url(); ?>';
      window.STORE_NAME = '<?= html_escape($site_name ?? 'Fresh Grocery Store'); ?>';
      window.CURRENCY_SYMBOL = '<?= html_escape($curr); ?>';
      window.handleCheckoutClick = function(e, redirectUrl) {
         if (!window.IS_USER_LOGGED_IN) {
            if (e && e.preventDefault) e.preventDefault();
            if (typeof window.closeGrcCartDrawer === 'function') {
               window.closeGrcCartDrawer();
            }
            if (typeof window.jQuery !== 'undefined') {
               jQuery('.aq-cartmini-active, #grcCartMiniDrawer, .cartmini__area, .cartmini-area').removeClass('opened cartmini-opened active show');
               jQuery('.body-overlay, .cartmini-overlay').removeClass('opened active show');
            }
            var drawers = document.querySelectorAll('.aq-cartmini-active, #grcCartMiniDrawer, .cartmini__area, .cartmini-area, .body-overlay, .cartmini-overlay');
            drawers.forEach(function(d) {
               d.classList.remove('opened', 'cartmini-opened', 'active', 'show');
            });
            var targetUrl = redirectUrl || '<?= site_url("checkout"); ?>';
            if (typeof window.openLoginModal === 'function') {
               window.openLoginModal(targetUrl, 'Please sign in to proceed to checkout.');
            } else {
               window.location.href = '<?= site_url("cart?login=1"); ?>';
            }
            return false;
         }
         return true;
      };
   </script>
</head>

<body>

   <!-- Toast Notification -->
   <div id="grcToast" class="grc-toast-msg">
      <i class="fa-solid fa-basket-shopping text-warning"></i>
      <span id="grcToastText">Added to grocery bag!</span>
   </div>

   <!-- back to top start -->
   <div class="progress-wrap">
      <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
         <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98"></path>
      </svg>
   </div>
   <!-- back to top end -->

   <!-- Cartmini area -->
   <div class="aq-cartmini-area aq-cartmini-active d-flex flex-column justify-content-between" id="grcCartMiniDrawer">
      <div class="aq-cartmini-header" style="border-bottom: 1px solid #ebebeb; padding-bottom: 18px;">
         <i class="aq-cartmini-close aq-cartmini-close-icon fa-solid fa-xmark" onclick="closeGrcCartDrawer()" role="button" aria-label="Close" title="Close" style="font-size: 18px; color: #111111;"></i>
         <h4 class="aq-cartmini-title mb-0">Grocery Bag (<span class="cart-count"><?= (int)($cart_count ?? 0); ?></span>)</h4>
      </div>
      <div class="aq-cartmini-body" id="grcCartMiniBody" style="height: 100%; flex: 1 1 auto; overflow-y: auto;">
         <?php if (!empty($cart_items)): ?>
            <?php foreach ($cart_items as $c_key => $c_item):
               $c_name = $c_item['name'] ?? ($c_item['title'] ?? ($c_item['product_title'] ?? 'Fresh Grocery Item'));
               $raw_img = !empty($c_item['image']) ? ltrim($c_item['image'], '/') : '';
               if (empty($raw_img)) {
                  $c_img = $bazaro . 'img/product/grocery-p-1.jpg';
               } elseif (strpos($raw_img, 'http') === 0) {
                  $c_img = $raw_img;
               } elseif (strpos($raw_img, 'assets/') === 0) {
                  $c_img = base_url($raw_img);
               } elseif (strpos($raw_img, 'products/') === 0) {
                  $c_img = base_url('assets/images/' . $raw_img);
               } else {
                  $c_img = base_url('assets/images/products/' . $raw_img);
               }
            ?>
            <div class="aq-cartmini-product-item mb-15 item-delete d-flex align-items-center">
               <div class="aq-cartmini-product-thumbnail">
                  <a href="<?= site_url('product/' . ($c_item['slug'] ?? $c_item['product_id'])); ?>">
                     <img src="<?= $c_img; ?>" alt="<?= html_escape($c_name); ?>" onerror="this.src='<?= $bazaro; ?>img/product/grocery-p-1.jpg'">
                  </a>
               </div>
               <div class="aq-cartmini-product-summary">
                  <h4 class="aq-product-title"><a href="<?= site_url('product/' . ($c_item['slug'] ?? $c_item['product_id'])); ?>"><?= html_escape($c_name); ?></a></h4>
                  <?php
                     $v_lbl = !empty($c_item['size']) ? $c_item['size'] : (!empty($c_item['variant_title']) ? $c_item['variant_title'] : '');
                     if (!empty($v_lbl) && strpos($v_lbl, '/') !== false) {
                        $v_parts = explode('/', $v_lbl);
                        $v_lbl = trim(end($v_parts));
                     }
                  ?>
                  <?php if (!empty($v_lbl)): ?>
                     <span class="aq-cartmini-product-variant text-muted" style="font-size: 12px; display: block; margin-bottom: 2px;"><?= html_escape($v_lbl); ?></span>
                  <?php endif; ?>
                  <span class="aq-cartmini-product-price"><?= html_escape($curr) . number_format((float)$c_item['price'], 2); ?> &times; <?= (int)$c_item['quantity']; ?></span>
                  <div class="aq-product-details-quantity d-flex align-items-center mt-1">
                     <button type="button" onclick="grcRemoveCartItem('<?= html_escape($c_key); ?>')" class="aq-line-anim aq-cartmini-remove">Remove</button>
                  </div>
               </div>
            </div>
            <?php endforeach; ?>
         <?php else: ?>
            <div class="cartmini-empty text-center py-5">
               <p class="text-muted">Your grocery bag is empty</p>
               <a href="<?= site_url('shop'); ?>" class="aq-btn-black border-btn mt-2">Browse Fresh Aisles</a>
            </div>
         <?php endif; ?>
      </div>
      <div class="aq-cartmini-footer">
         <div class="aq-cartmini-total d-flex justify-content-between align-items-center">
            <span class="aq-cartmini-total-title">Subtotal</span>
            <span class="aq-cartmini-total-value" id="grcCartMiniSubtotal"><?= html_escape($curr) . number_format((float)($cart_total ?? 0), 2); ?></span>
         </div>
         <div class="aq-cartmini-main-btn d-flex justify-content-between gap-2">
            <a href="<?= site_url('cart'); ?>" class="aq-btn-black btn-red-bg text-center w-100">View Bag</a>
            <a href="<?= site_url('checkout'); ?>" onclick="return handleCheckoutClick(event, '<?= site_url('checkout'); ?>')" class="aq-btn-black text-center border-btn w-100">Checkout</a>
         </div>
      </div>
   </div>
   <!-- Cartmini area end -->

   <!-- offcanvas area start -->
   <div class="aq-offcanvas-wrap">
      <div class="aq-offcanvas-top d-flex align-items-center justify-content-between">
         <div class="aq-offcanvas-logo">
            <a href="<?= site_url('home'); ?>"><img width="115" src="<?= $web_logo; ?>" alt="<?= html_escape($site_name); ?>" style="max-height:40px;width:auto;object-fit:contain;" onerror="this.src='<?= base_url('assets/images/logo/logo.webp'); ?>'"></a>
         </div>
         <div class="aq-offcanvas-close">
            <span>
               <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                  <path d="M10.75 0.75L0.75 10.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M0.75 0.75L10.75 10.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
               </svg>
            </span>
         </div>
      </div>
      <div class="aq-offcanvas-menu-wrap">
         <div class="aq-offcanvas-menu">
            <nav>
               <ul class="list-unstyled">
                  <li class="py-2 border-bottom"><a href="<?= site_url('home'); ?>" class="fw-bold text-dark">Home</a></li>
                  <li class="py-2 border-bottom"><a href="<?= site_url('shop'); ?>" class="fw-bold text-dark">Shop All Groceries</a></li>
                  <?php if (!empty($nav_categories)): ?>
                     <?php foreach ($nav_categories as $cat): ?>
                        <li class="py-2 border-bottom ps-2"><a href="<?= site_url('shop/' . $cat['slug']); ?>" class="text-secondary">&bull; <?= html_escape($cat['name']); ?></a></li>
                     <?php endforeach; ?>
                  <?php endif; ?>
                  <li class="py-2 border-bottom"><a href="<?= site_url('order/track'); ?>" class="fw-bold text-dark">Track Order</a></li>
                  <li class="py-2 border-bottom"><a href="<?= site_url('contact'); ?>" class="fw-bold text-dark">Contact Us</a></li>
               </ul>
            </nav>
         </div>
      </div>
      <div class="aq-offcanvas-bottom">
         <div class="aq-offcanvas-btn-wrap d-flex justify-content-between align-items-center">
            <?php if (!empty($is_logged_in)): ?>
            <a class="aq-offcanvas-btn" href="<?= site_url('account'); ?>">My Account</a>
            <?php else: ?>
            <a class="aq-offcanvas-btn" href="#exampleModalToggle" data-bs-toggle="modal">Login</a>
            <?php endif; ?>
            <a class="aq-offcanvas-btn btn-black-bg" href="<?= site_url('wishlist'); ?>">Wishlist (<?= (int)($wishlist_count ?? 0); ?>)</a>
         </div>
      </div>
   </div>
   <div class="body-overlay" onclick="closeGrcCartDrawer()"></div>
   <!-- offcanvas area end -->

   <!-- bottom-sticky header -->
   <div class="aq-bottom-menu d-md-none">
      <div class="container">
         <div class="row row-cols-5">
            <div class="col">
               <a href="<?= site_url('home'); ?>">
                  <div class="aq-bottom-menu-item">
                     <i><i class="fa-solid fa-house"></i></i>
                     <span>Home</span>
                  </div>
               </a>
            </div>
            <div class="col">
               <?php if (!empty($is_logged_in)): ?>
               <a href="<?= site_url('account'); ?>">
                  <div class="aq-bottom-menu-item">
                     <i><i class="fa-regular fa-user"></i></i>
                     <span>Account</span>
                  </div>
               </a>
               <?php else: ?>
               <a href="#exampleModalToggle" data-bs-toggle="modal">
                  <div class="aq-bottom-menu-item">
                     <i><i class="fa-regular fa-user"></i></i>
                     <span>Sign In</span>
                  </div>
               </a>
               <?php endif; ?>
            </div>
            <div class="col">
               <a href="<?= site_url('shop'); ?>">
                  <div class="aq-bottom-menu-item">
                     <i><i class="fa-solid fa-store"></i></i>
                     <span>Aisles</span>
                  </div>
               </a>
            </div>
            <div class="col">
               <a href="<?= site_url('wishlist'); ?>">
                  <div class="aq-bottom-menu-item">
                     <button class="p-relative">
                        <span class="count-box wishlist-count"><?= (int)($wishlist_count ?? 0); ?></span>
                        <i><i class="fa-regular fa-heart"></i></i>
                     </button>
                     <span>Wishlist</span>
                  </div>
               </a>
            </div>
            <div class="col">
               <div class="aq-bottom-menu-item aq-cart-btn" onclick="openGrcCartDrawer()">
                  <button class="p-relative">
                     <span class="count-box cart-count"><?= (int)($cart_count ?? 0); ?></span>
                     <i><i class="fa-solid fa-basket-shopping"></i></i>
                  </button>
                  <span>Bag</span>
               </div>
            </div>
         </div>
      </div>
   </div>
   <!-- bottom-sticky header -->

   <!-- Sticky Main Header (Logo, Search Bar, User, Wishlist, Cart) -->
   <header class="grc-main-sticky-header">
      <div class="grc-header-main-nav grc-header-ptb pt-20 pb-20" data-bg-color="#003D29" style="background-color:#003D29;">
         <div class="container container-1630">
            <div class="grc-header-main-wrap">
               <div class="row align-items-center">
                  <div class="col-lg-2 col-md-3 col-6">
                     <div class="aq-header-logo">
                        <a href="<?= site_url('home'); ?>" class="text-decoration-none d-inline-flex align-items-center gap-2">
                           <img data-width="140" src="<?= $web_logo; ?>" alt="<?= html_escape($site_name); ?>" style="max-height:42px;width:auto;object-fit:contain;" onerror="this.src='<?= base_url('assets/images/logo/logo.webp'); ?>'">
                        </a>
                     </div>
                  </div>
                  <div class="col-lg-7 col-md-5 d-none d-md-block">
                     <div class="grc-header-search-wrap">
                        <form action="<?= site_url('shop'); ?>" method="GET" class="grc-search-form" autocomplete="off">
                           <div class="grc-search-pill-container">
                              <span class="grc-search-glass-icon">
                                 <i class="fa-solid fa-magnifying-glass"></i>
                              </span>
                              <input type="text"
                                     name="q"
                                     id="grcHeaderSearchInput"
                                     class="grc-search-input"
                                     value="<?= html_escape($this->input->get('q') ?? ''); ?>"
                                     placeholder="Search for fresh groceries (e.g. avocados, milk, sourdough, berries)..."
                                     autocomplete="off">
                              <button type="button" class="grc-search-clear-btn" id="grcSearchClearBtn" onclick="clearGrcSearchInput('grcHeaderSearchInput')" title="Clear search">
                                 <i class="fa-solid fa-xmark"></i>
                              </button>
                              <button type="submit" class="grc-search-btn-submit" aria-label="Search">
                                 <span>Search</span>
                              </button>
                           </div>
                           <div class="grc-search-autocomplete" id="grcSearchAutocomplete"></div>
                        </form>
                     </div>
                  </div>
                  <div class="col-lg-3 col-md-4 col-6">
                     <div class="aq-header-right-options grc-header-right-option text-end">
                        <ul>
                           <li class="aq-header-top-account d-none d-md-inline-flex">
                              <?php if (!empty($is_logged_in)): ?>
                              <a href="<?= site_url('account'); ?>" class="text-decoration-none">
                                 <div class="aq-header-top-account-item">
                                    <span class="grc-option-style d-inline-flex align-items-center justify-content-center">
                                       <i>
                                          <svg xmlns="http://www.w3.org/2000/svg" width="17" height="20" viewBox="0 0 17 20" fill="none">
                                             <path d="M16.212 18.75C16.212 15.267 12.747 12.45 8.481 12.45C4.215 12.45 0.75 15.267 0.75 18.75M12.9805 5.25C12.9805 7.73528 10.9657 9.75 8.48047 9.75C5.99519 9.75 3.98047 7.73528 3.98047 5.25C3.98047 2.76472 5.99519 0.75 8.48047 0.75C10.9657 0.75 12.9805 2.76472 12.9805 5.25Z" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                          </svg>
                                       </i>
                                    </span>
                                    <div class="aq-header-top-account-content text-start">
                                       <span><?= html_escape($current_user['first_name'] ?? 'Member'); ?></span>
                                       <p>Account</p>
                                    </div>
                                 </div>
                              </a>
                              <?php else: ?>
                              <a href="#exampleModalToggle" data-bs-toggle="modal" class="text-decoration-none">
                                 <div class="aq-header-top-account-item">
                                    <span class="grc-option-style d-inline-flex align-items-center justify-content-center">
                                       <i>
                                          <svg xmlns="http://www.w3.org/2000/svg" width="17" height="20" viewBox="0 0 17 20" fill="none">
                                             <path d="M16.212 18.75C16.212 15.267 12.747 12.45 8.481 12.45C4.215 12.45 0.75 15.267 0.75 18.75M12.9805 5.25C12.9805 7.73528 10.9657 9.75 8.48047 9.75C5.99519 9.75 3.98047 7.73528 3.98047 5.25C3.98047 2.76472 5.99519 0.75 8.48047 0.75C10.9657 0.75 12.9805 2.76472 12.9805 5.25Z" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                          </svg>
                                       </i>
                                    </span>
                                    <div class="aq-header-top-account-content text-start">
                                       <span>Hello</span>
                                       <p>Sign In</p>
                                    </div>
                                 </div>
                              </a>
                              <?php endif; ?>
                           </li>
                           <li class="aq-header-top-wishlist d-inline-flex">
                              <a href="<?= site_url('wishlist'); ?>" class="grc-option-style d-inline-flex align-items-center justify-content-center position-relative text-decoration-none" title="My Wishlist" aria-label="My Wishlist">
                                 <span class="count-box wishlist-count"><?= (int)($wishlist_count ?? 0); ?></span>
                                 <i>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="21" height="20" viewBox="0 0 21 20" fill="none">
                                       <path d="M6.50726 4.80303C5.44195 5.14334 4.68503 6.09974 4.59044 7.22502M10.4856 18.6038C12.6562 17.2679 14.6755 15.6957 16.5073 13.9152C17.7951 12.633 18.7756 11.0698 19.3735 9.3454C20.4494 6.00032 19.1927 2.17084 15.6755 1.03753C13.827 0.442448 11.8081 0.782566 10.2505 1.95149C8.69225 0.783989 6.67412 0.443991 4.82552 1.03753C1.30833 2.17084 0.0425004 6.00032 1.11845 9.3454C1.71636 11.0698 2.69679 12.633 3.98465 13.9152C5.81647 15.6957 7.83575 17.2679 10.0064 18.6038L10.2414 18.75L10.4856 18.6038Z" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                 </i>
                              </a>
                           </li>
                           <li class="aq-header-top-cart aq-cart-btn">
                              <button type="button" class="grc-option-style d-inline-flex align-items-center justify-content-center position-relative" onclick="openGrcCartDrawer()" title="View Grocery Bag" aria-label="View Grocery Bag">
                                 <span class="count-box cart-count"><?= (int)($cart_count ?? 0); ?></span>
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
                  <!-- Mobile Search Bar (under logo & cart on small screens) -->
                  <div class="col-12 d-md-none mt-2">
                     <div class="grc-header-search-wrap">
                        <form action="<?= site_url('shop'); ?>" method="GET" class="grc-search-form" autocomplete="off">
                           <div class="grc-search-pill-container" style="height: 42px;">
                              <span class="grc-search-glass-icon" style="left: 14px;">
                                 <i class="fa-solid fa-magnifying-glass" style="font-size: 14px;"></i>
                              </span>
                              <input type="text"
                                     name="q"
                                     id="grcMobileSearchInput"
                                     class="grc-search-input"
                                     value="<?= html_escape($this->input->get('q') ?? ''); ?>"
                                     placeholder="Search groceries..."
                                     autocomplete="off"
                                     style="font-size: 13.5px !important; padding-left: 38px !important; padding-right: 90px !important;">
                              <button type="submit" class="grc-search-btn-submit" style="padding: 0 16px; font-size: 13px;">
                                 <span>Search</span>
                              </button>
                           </div>
                           <div class="grc-search-autocomplete" id="grcMobileSearchAutocomplete"></div>
                        </form>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </header>

   <!-- Department Category Bar (Dark Green) -->
   <nav class="grc-category-aisles-bar grc-header-ptb pt-10 pb-15" data-bg-color="#003D29" style="background-color:#003D29;">
      <div class="container container-1630">
         <div class="grc-categories-wrap d-lg-flex d-none justify-content-between align-items-center">
            <?php
            $dept_icons = ['fa-apple-whole', 'fa-carrot', 'fa-cheese', 'fa-bread-slice', 'fa-mug-hot', 'fa-jar', 'fa-Seedling', 'fa-fish', 'fa-cookie-bite', 'fa-bottle-water', 'fa-bowl-rice', 'fa-egg'];
            $dept_default_imgs = [
               'grocery/broccoli.png',
               'grocery/apple.png',
               'grocery/eggs.png',
               'grocery/bread.png',
               'grocery/groceries.png',
               'grocery/orange-juice.png',
               'grocery/carrot.png',
               'grocery/grocery.png',
               'grocery/healthy-food.png',
               'grocery/shopping-bag.png'
            ];
            if (!function_exists('grc_resolve_cat_img')) {
               function grc_resolve_cat_img($cat_arr, $fallback_url) {
                  $raw = !empty($cat_arr['image']) ? $cat_arr['image'] : (!empty($cat_arr['raw_image']) ? $cat_arr['raw_image'] : '');
                  if (empty($raw)) return $fallback_url;
                  if (strpos($raw, 'http://') === 0 || strpos($raw, 'https://') === 0) return $raw;
                  if (strpos($raw, 'assets/') === 0) return base_url($raw);
                  return base_url('assets/images/' . ltrim($raw, '/'));
               }
            }
            // Strictly parent categories only (no sub categories at top level)
            $parent_nav_cats = array_values(array_filter($nav_categories ?? [], function($c) {
               return empty($c['parent_id']) || (int)$c['parent_id'] === 0;
            }));
            $has_more_cats = count($parent_nav_cats) > 8;
            $visible_cats = $has_more_cats ? array_slice($parent_nav_cats, 0, 8) : $parent_nav_cats;
            $dropdown_cats = $has_more_cats ? array_slice($parent_nav_cats, 8) : [];

            if (!empty($visible_cats)):
               foreach ($visible_cats as $c_i => $cat):
                  $ic = $dept_icons[$c_i % count($dept_icons)];
                  $fallback_cat_img = base_url('assets/images/' . $dept_default_imgs[$c_i % count($dept_default_imgs)]);
                  $cat_img_url = grc_resolve_cat_img($cat, $fallback_cat_img);
            ?>
            <div class="grc-categories-item text-center">
               <a href="<?= site_url('shop/' . $cat['slug']); ?>">
                  <div class="grc-categories-icon d-flex align-items-center justify-content-center">
                     <img src="<?= html_escape($cat_img_url); ?>" alt="<?= html_escape($cat['name']); ?>" style="max-height: 28px; max-width: 28px; width: auto; height: auto; object-fit: contain;" onerror="this.onerror=null;this.src='<?= html_escape($fallback_cat_img); ?>';">
                  </div>
                  <span class="grc-categories-title"><?= html_escape($cat['name']); ?></span>
               </a>
            </div>
            <?php
               endforeach;
            endif;
            ?>
            <?php if (!empty($dropdown_cats)): ?>
            <!-- More Categories Dropdown (shown if more than 8 parent categories, opens on click only) -->
            <div class="grc-categories-item text-center dropdown grc-more-cats-dropdown position-relative">
               <a href="javascript:void(0);" class="text-decoration-none dropdown-toggle hide-arrow" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" role="button" title="More Categories">
                  <div class="grc-categories-icon d-flex align-items-center justify-content-center">
                     <i class="fa-solid fa-angles-right text-white" style="font-size: 13px;"></i>
                  </div>
               </a>
               <ul class="dropdown-menu dropdown-menu-end" style="max-height: 380px; overflow-y: auto;">
                  <?php foreach ($dropdown_cats as $d_i => $dcat): 
                     $fallback_dcat_img = base_url('assets/images/' . $dept_default_imgs[(8 + $d_i) % count($dept_default_imgs)]);
                     $dcat_img_url = grc_resolve_cat_img($dcat, $fallback_dcat_img);
                  ?>
                  <li>
                     <a class="dropdown-item" href="<?= site_url('shop/' . $dcat['slug']); ?>">
                        <span class="grc-dd-icon-box">
                           <img src="<?= html_escape($dcat_img_url); ?>" alt="<?= html_escape($dcat['name']); ?>" style="width: 20px; height: 20px; object-fit: contain;" onerror="this.onerror=null;this.src='<?= html_escape($fallback_dcat_img); ?>';">
                        </span>
                        <span><?= html_escape($dcat['name']); ?></span>
                        <i class="fa-solid fa-chevron-right grc-dd-arrow"></i>
                     </a>
                  </li>
                  <?php endforeach; ?>
               </ul>
            </div>
            <?php endif; ?>
         </div>
      </div>
   </nav>

   <script>
   (function() {
      // 1. Sticky Header Scroll Shadow & Compact State
      var stickyHeader = document.querySelector('.grc-main-sticky-header');
      if (stickyHeader) {
         var handleHeaderScroll = function() {
            if (window.scrollY > 20) {
               stickyHeader.classList.add('is-scrolled');
            } else {
               stickyHeader.classList.remove('is-scrolled');
            }
         };
         window.addEventListener('scroll', handleHeaderScroll, { passive: true });
         handleHeaderScroll();
      }

      // 2. Live Search Autocomplete Functionality
      function initSearchAutocomplete(inputId, dropdownId, clearBtnId) {
         var input = document.getElementById(inputId);
         var dropdown = document.getElementById(dropdownId);
         var clearBtn = clearBtnId ? document.getElementById(clearBtnId) : null;
         if (!input || !dropdown) return;

         var debounceTimer = null;
         var currentQuery = '';

         var updateClearBtn = function() {
            if (clearBtn) {
               clearBtn.style.display = input.value.trim().length > 0 ? 'inline-flex' : 'none';
            }
         };

         input.addEventListener('input', function() {
            var val = this.value.trim();
            updateClearBtn();
            clearTimeout(debounceTimer);
            if (val.length < 2) {
               dropdown.style.display = 'none';
               dropdown.innerHTML = '';
               return;
            }
            currentQuery = val;
            debounceTimer = setTimeout(function() {
               var url = '<?= site_url("shop/autocomplete"); ?>?q=' + encodeURIComponent(val);
               fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                  .then(function(res) { return res.json(); })
                  .then(function(data) {
                     if (currentQuery !== val) return;
                     var items = (data && data.results) ? data.results : [];
                     if (items.length === 0) {
                        dropdown.innerHTML = '<div class="px-3 py-3 text-center text-muted" style="font-size:13px;">No fresh groceries found for "<b>' + val.replace(/</g, '&lt;') + '</b>"</div>';
                        dropdown.style.display = 'block';
                        return;
                     }
                     var html = '';
                     items.forEach(function(item) {
                        var cat = item.category_name ? '<div class="grc-autocomplete-cat">' + item.category_name + '</div>' : '';
                        var img = item.image ? '<img src="' + item.image + '" class="grc-autocomplete-thumb" alt="' + (item.title || '') + '" onerror="this.src=\'<?= $bazaro; ?>img/product/grocery-p-1.jpg\'">' : '';
                        html += '<a href="' + item.url + '" class="grc-autocomplete-item">' +
                                   img +
                                   '<div class="grc-autocomplete-info">' +
                                      '<div class="grc-autocomplete-title">' + item.title + '</div>' +
                                      cat +
                                   '</div>' +
                                   '<div class="grc-autocomplete-price">' + item.price_html + '</div>' +
                                '</a>';
                     });
                     html += '<a href="<?= site_url("shop"); ?>?q=' + encodeURIComponent(val) + '" class="grc-autocomplete-footer">' +
                                'View all results for "' + val.replace(/</g, '&lt;') + '" <i class="fa-solid fa-arrow-right ms-1"></i>' +
                             '</a>';
                     dropdown.innerHTML = html;
                     dropdown.style.display = 'block';
                  })
                  .catch(function(err) {
                     console.error('Autocomplete error:', err);
                  });
            }, 220);
         });

         document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
               dropdown.style.display = 'none';
            }
         });

         input.addEventListener('focus', function() {
            if (this.value.trim().length >= 2 && dropdown.innerHTML.trim() !== '') {
               dropdown.style.display = 'block';
            }
         });

         input.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
               dropdown.style.display = 'none';
            }
         });

         updateClearBtn();
      }

      window.clearGrcSearchInput = function(inputId) {
         var input = document.getElementById(inputId);
         if (input) {
            input.value = '';
            input.focus();
            var clearBtn = document.getElementById('grcSearchClearBtn');
            if (clearBtn) clearBtn.style.display = 'none';
            var dropdown = document.getElementById('grcSearchAutocomplete');
            if (dropdown) { dropdown.style.display = 'none'; dropdown.innerHTML = ''; }
         }
      };

      if (document.readyState === 'loading') {
         document.addEventListener('DOMContentLoaded', function() {
            initSearchAutocomplete('grcHeaderSearchInput', 'grcSearchAutocomplete', 'grcSearchClearBtn');
            initSearchAutocomplete('grcMobileSearchInput', 'grcMobileSearchAutocomplete', null);
         });
      } else {
         initSearchAutocomplete('grcHeaderSearchInput', 'grcSearchAutocomplete', 'grcSearchClearBtn');
         initSearchAutocomplete('grcMobileSearchInput', 'grcMobileSearchAutocomplete', null);
      }
   })();
   </script>

   <?php if (!isset($active_page) || !in_array($active_page, ['cart', 'checkout', 'account', 'wishlist'])): ?>
      <?php if ($this->session->flashdata('success')): ?>
         <div class="container container-1630 mt-3">
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center justify-content-between" role="alert">
               <div class="d-flex align-items-center">
                  <i class="fa-solid fa-circle-check me-2 fs-5 text-success"></i>
                  <span><?= html_escape($this->session->flashdata('success')); ?></span>
               </div>
               <button type="button" class="btn-close-alert" onclick="this.closest('.alert').remove();" aria-label="Close">
                  <i class="fa-solid fa-xmark"></i>
               </button>
            </div>
         </div>
      <?php endif; ?>
      <?php if ($this->session->flashdata('error')): ?>
         <div class="container container-1630 mt-3">
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center justify-content-between" role="alert">
               <div class="d-flex align-items-center">
                  <i class="fa-solid fa-triangle-exclamation me-2 fs-5 text-danger"></i>
                  <span><?= html_escape($this->session->flashdata('error')); ?></span>
               </div>
               <button type="button" class="btn-close-alert" onclick="this.closest('.alert').remove();" aria-label="Close">
                  <i class="fa-solid fa-xmark"></i>
               </button>
            </div>
         </div>
      <?php endif; ?>
   <?php endif; ?>
