<?php
$bazaro = isset($tpl_assets) ? rtrim($tpl_assets, '/') . '/' : base_url('assets/templates/bazaro/');
$curr   = isset($currency_symbol) ? $currency_symbol : '$';

if (!function_exists('elt_shop_prod_img')) {
    function elt_shop_prod_img($p, $fallback_idx = 1) {
        $bazaro_base = base_url('assets/templates/bazaro/');
        $img = '';
        if (is_array($p)) {
            $img = !empty($p['primary_image']) ? $p['primary_image'] : (!empty($p['main_image']) ? $p['main_image'] : ($p['image'] ?? ''));
        } elseif (is_string($p)) {
            $img = $p;
        }
        if (!empty($img)) {
            if (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0) {
                return $img;
            }
            $clean = ltrim($img, '/');
            if ((strpos($clean, 'assets/') === 0 || strpos($clean, 'uploads/') === 0) && file_exists(FCPATH . $clean)) {
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
        }
        $idx = (($fallback_idx - 1) % 8) + 1;
        return $bazaro_base . 'img/electronics/arrival-product/product-' . $idx . '/product-1.jpg';
    }
}

$cats_list = !empty($all_categories) ? $all_categories : (!empty($nav_categories) ? $nav_categories : []);
$active_cat_slug = $filters['category_slug'] ?? ($selected_category['slug'] ?? '');
$elt_qs = empty($is_user_project) ? '?preview_tpl=electronics' : '';
$total_all_cnt = isset($total_all_products) ? (int)$total_all_products : count($products);
if (!empty($is_user_project)) {
    $tech_brands = [];
    if (!empty($brands) && is_array($brands)) {
        foreach ($brands as $b) {
            if (!empty($b['name'])) {
                $tech_brands[] = $b['name'];
            }
        }
    }
    $tech_specs = [];
} else {
    $tech_brands = ['Apple', 'Sony', 'Samsung', 'Bose', 'ASUS ROG', 'DJI', 'Logitech'];
    $tech_specs  = [
        'Active Noise Cancelling',
        'Wireless Bluetooth 5.3',
        'Fast USB-C Charging',
        '5G Ultra Wideband',
        '4K UHD Display / Camera',
        'Water Resistant IP68'
    ];
}
?>

<style>
   /* ==========================================================================
      Template 2 (Bazaro Electronics) Dedicated Shop Page Styles
      Theme: Dark Carbon (#0B0D0E) + Lime Accent (#E1EEB7) + Crisp Tech Cards
      ========================================================================== */
   .elt-shop-hero {
      background: linear-gradient(135deg, #0B0D0E 0%, #181c20 60%, #11161b 100%);
      color: #ffffff;
      padding: 44px 0 34px;
      position: relative;
      overflow: hidden;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
   }
   .elt-shop-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image: radial-gradient(rgba(225, 238, 183, 0.09) 1px, transparent 1px);
      background-size: 24px 24px;
      pointer-events: none;
   }
   .elt-shop-hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      background: rgba(225, 238, 183, 0.14);
      color: #E1EEB7;
      border: 1px solid rgba(225, 238, 183, 0.3);
      font-size: 11.5px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      padding: 5px 13px;
      border-radius: 30px;
      margin-bottom: 12px;
   }
   .elt-shop-hero-title {
      font-size: 40px;
      font-weight: 600;
      color: #ffffff;
      margin: 0 0 8px;
      letter-spacing: -0.6px;
   }
   .elt-shop-breadcrumbs {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 8px;
      list-style: none;
      margin: 0;
      padding: 0;
      font-size: 13px;
      color: rgba(255, 255, 255, 0.65);
   }
   .elt-shop-breadcrumbs a {
      color: rgba(255, 255, 255, 0.75);
      text-decoration: none;
      transition: color 0.15s ease;
   }
   .elt-shop-breadcrumbs a:hover {
      color: #E1EEB7;
   }
   .elt-shop-breadcrumbs .active {
      color: #E1EEB7;
      font-weight: 600;
   }

   /* Quick Category Filter Chips inside Hero */
   .elt-hero-cat-strip {
      display: flex;
      align-items: center;
      gap: 10px;
      overflow-x: auto;
      scrollbar-width: none;
      margin-top: 24px;
      padding-bottom: 4px;
   }
   .elt-hero-cat-strip::-webkit-scrollbar {
      display: none;
   }
   .elt-hero-cat-chip {
      display: inline-flex;
      align-items: center;
      gap: 9px;
      padding: 7px 16px;
      border-radius: 10px;
      background: rgba(255, 255, 255, 0.07);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: rgba(255, 255, 255, 0.88);
      font-size: 13px;
      font-weight: 600;
      text-decoration: none;
      white-space: nowrap;
      transition: all 0.18s ease;
      flex-shrink: 0;
   }
   .elt-hero-cat-chip:hover {
      background: rgba(255, 255, 255, 0.14);
      color: #ffffff;
      border-color: rgba(225, 238, 183, 0.45);
   }
   .elt-hero-cat-chip.is-active {
      background: #E1EEB7;
      color: #0B0D0E;
      border-color: #E1EEB7;
      box-shadow: 0 4px 16px rgba(225, 238, 183, 0.25);
   }

   /* Main Shop Layout */
   .elt-shop-section {
      background: #f6f7f9;
      padding: 40px 0 90px;
      min-height: 70vh;
   }

   /* Left Tech Spec Filter Sidebar */
   .elt-filter-card {
      background: #ffffff;
      border-radius: 16px;
      border: 1px solid #e7eaf0;
      padding: 22px;
      box-shadow: 0 4px 18px rgba(11, 13, 14, 0.03);
   }
   .elt-filter-block {
      padding-bottom: 20px;
      margin-bottom: 20px;
      border-bottom: 1px solid #eef0f4;
   }
   .elt-filter-block:last-child {
      padding-bottom: 0;
      margin-bottom: 0;
      border-bottom: none;
   }
   .elt-filter-heading {
      font-size: 13.5px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #0B0D0E;
      margin: 0 0 14px;
      display: flex;
      align-items: center;
      justify-content: space-between;
   }
   .elt-dept-list {
      list-style: none;
      margin: 0;
      padding: 0;
      display: flex;
      flex-direction: column;
      gap: 4px;
   }
   .elt-dept-link {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 8px 11px;
      border-radius: 8px;
      font-size: 13.5px;
      font-weight: 500;
      color: #334155;
      text-decoration: none;
      transition: all 0.15s ease;
   }
   .elt-dept-link:hover {
      background: #f1f5f9;
      color: #0B0D0E;
   }
   .elt-dept-link.is-active {
      background: #0B0D0E;
      color: #ffffff;
      font-weight: 700;
   }
   .elt-dept-count {
      font-size: 11px;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 20px;
      background: rgba(0, 0, 0, 0.06);
      color: #475569;
   }
   .elt-dept-link.is-active .elt-dept-count {
      background: #E1EEB7;
      color: #0B0D0E;
   }

   /* Checkboxes & Price Brackets */
   .elt-check-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 5px 0;
      cursor: pointer;
      font-size: 13.5px;
      color: #334155;
   }
   .elt-check-row input[type="checkbox"],
   .elt-check-row input[type="radio"] {
      width: 16px;
      height: 16px;
      accent-color: #0B0D0E;
      margin-right: 9px;
      cursor: pointer;
   }
   .elt-price-brackets {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 7px;
      margin-top: 10px;
   }
   .elt-price-chip {
      border: 1px solid #e2e8f0;
      background: #f8fafc;
      color: #334155;
      font-size: 12px;
      font-weight: 600;
      padding: 6px 8px;
      border-radius: 8px;
      text-align: center;
      cursor: pointer;
      transition: all 0.15s ease;
   }
   .elt-price-chip:hover,
   .elt-price-chip.is-active {
      background: #0B0D0E;
      color: #E1EEB7;
      border-color: #0B0D0E;
   }

   /* Sidebar Tech Promo Banner */
   .elt-sidebar-promo {
      background: #0B0D0E;
      color: #ffffff;
      border-radius: 16px;
      padding: 22px 20px;
      margin-top: 20px;
      position: relative;
      overflow: hidden;
   }
   .elt-sidebar-promo-tag {
      display: inline-block;
      background: #E1EEB7;
      color: #0B0D0E;
      font-size: 11px;
      font-weight: 800;
      text-transform: uppercase;
      padding: 3px 10px;
      border-radius: 20px;
      margin-bottom: 10px;
   }

   /* Top Control Bar */
   .elt-control-bar {
      padding: 12px 18px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      justify-content: flex-end;
      flex-wrap: wrap;
      gap: 12px;
      box-shadow: 0 2px 10px rgba(11, 13, 14, 0.02);
   }
   .elt-inline-filter-search {
      display: flex;
      align-items: center;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 0 12px;
      height: 38px;
      min-width: 220px;
   }
   .elt-inline-filter-search input {
      border: none !important;
      background: transparent !important;
      outline: none !important;
      font-size: 13px;
      color: #111827;
      width: 100%;
      padding-left: 8px;
      height: 34px !important;
   }
   .elt-sale-pill-toggle {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 7px 14px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      color: #334155;
      cursor: pointer;
      transition: all 0.15s ease;
      user-select: none;
   }
   .elt-sale-pill-toggle.is-active {
      background: #0B0D0E;
      color: #E1EEB7;
      border-color: #0B0D0E;
   }
   .elt-view-btn {
      width: 36px;
      height: 36px;
      border-radius: 8px;
      border: 1px solid #e2e8f0;
      background: #ffffff;
      color: #64748b;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.15s ease;
   }
   .elt-view-btn.is-active,
   .elt-view-btn:hover {
      background: #0B0D0E;
      color: #E1EEB7;
      border-color: #0B0D0E;
   }
   .elt-sort-select {
      height: 38px;
      border-radius: 8px;
      border: 1px solid #e2e8f0;
      background: #f8fafc;
      padding: 0 12px;
      font-size: 13px;
      font-weight: 600;
      color: #0B0D0E;
      outline: none;
      cursor: pointer;
   }

   /* Electronics Product Card Enhancements for Shop Grid */
   .elt-shop-card {
      background: #ffffff;
      border-radius: 14px;
      border: 1px solid #eaeef4;
      padding: 16px;
      height: 100%;
      transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
   }
   .elt-shop-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 14px 32px rgba(11, 13, 14, 0.08);
      border-color: #cbd5e1;
   }
   .elt-shop-card .aq-product-thumb {
      background: #f8fafc;
      border-radius: 10px;
      overflow: hidden;
      text-align: center;
      padding: 12px;
      margin-bottom: 14px;
   }
   .elt-shop-card .aq-product-img {
      max-height: 210px;
      width: 100%;
      object-fit: contain;
      transition: transform 0.3s ease;
   }
   .elt-shop-card:hover .aq-product-img {
      transform: scale(1.05);
   }
   .elt-card-spec-badges {
      display: flex;
      flex-wrap: wrap;
      gap: 5px;
      margin: 8px 0 10px;
   }
   .elt-spec-tag {
      font-size: 11px;
      font-weight: 600;
      padding: 2px 8px;
      border-radius: 5px;
      background: #f1f5f9;
      color: #475569;
   }
   .elt-card-bottom-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding-top: 12px;
      margin-top: 10px;
      border-top: 1px solid #f1f5f9;
   }
   .elt-add-cart-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #0B0D0E;
      color: #ffffff;
      border: none;
      border-radius: 8px;
      padding: 7px 13px;
      font-size: 12.5px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.18s ease;
   }
   .elt-add-cart-pill:hover {
      background: #E1EEB7;
      color: #0B0D0E;
   }

   /* List View Mode for Tech Specs Comparison */
   #eltProductsGrid.is-list-view > .elt-product-col {
      width: 100% !important;
      flex: 0 0 100% !important;
   }
   #eltProductsGrid.is-list-view .elt-shop-card {
      flex-direction: row;
      align-items: center;
      gap: 24px;
      padding: 20px;
   }
   #eltProductsGrid.is-list-view .elt-shop-card .aq-product-thumb {
      width: 220px;
      flex-shrink: 0;
      margin-bottom: 0;
   }
   #eltProductsGrid.is-list-view .elt-shop-card .elt-card-body {
      flex: 1;
      text-align: left !important;
   }
   @media (max-width: 767.98px) {
      .elt-shop-hero-title {
         font-size: 26px;
      }
      #eltProductsGrid.is-list-view .elt-shop-card {
         flex-direction: column;
      }
      #eltProductsGrid.is-list-view .elt-shop-card .aq-product-thumb {
         width: 100%;
      }
   }
</style>

<main>
   <!-- 1. Dark Carbon Electronics Category Hero Banner -->
   <section class="elt-shop-hero">
      <div class="container position-relative" style="z-index:2;">
         <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
            <div>
               <h1 class="elt-shop-hero-title" id="eltShopPageHeading"><?= html_escape($page_heading); ?></h1>
               <ul class="elt-shop-breadcrumbs">
                  <li><a href="<?= site_url('home' . $elt_qs); ?>"><i class="fa-solid fa-house me-1"></i>Home</a></li>
                  <li><i class="fa-solid fa-angle-right" style="font-size:11px;"></i></li>
                  <li><a href="<?= site_url('shop' . $elt_qs); ?>">Electronics Catalog</a></li>
                  <?php if (!empty($selected_category['name'])): ?>
                     <li><i class="fa-solid fa-angle-right" style="font-size:11px;"></i></li>
                     <li class="active"><?= html_escape($selected_category['name']); ?></li>
                  <?php endif; ?>
               </ul>
            </div>
         </div>
      </div>
   </section>

   <!-- 2. Electronics Catalog & Tech Spec Filters Section -->
   <section class="elt-shop-section">
      <div class="container">
         <div class="row g-4">

            <!-- Left Sidebar: Tech Spec Filters -->
            <div class="col-xl-3 col-lg-4">
               <div class="elt-filter-card">
                  <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                     <span class="fw-bold text-dark d-flex align-items-center gap-2" style="font-size:15px;">
                        <i class="fa-solid fa-sliders"></i> Tech Filters
                     </span>
                     <button type="button" id="eltResetFiltersBtn" class="btn btn-sm p-0 border-0 text-danger fw-semibold" style="font-size:12.5px;">
                        Reset All
                     </button>
                  </div>

                  <!-- Department Filter -->
                  <div class="elt-filter-block">
                     <h6 class="elt-filter-heading">Departments</h6>
                     <ul class="elt-dept-list">
                        <li>
                           <a href="<?= site_url('shop' . $elt_qs); ?>" class="elt-dept-link <?= empty($active_cat_slug) ? 'is-active' : ''; ?>">
                              <span>All Electronics</span>
                              <span class="elt-dept-count"><?= $total_all_cnt; ?></span>
                           </a>
                        </li>
                        <?php foreach ($cats_list as $cat):
                           $is_curr = (!empty($active_cat_slug) && strcasecmp($active_cat_slug, $cat['slug']) === 0);
                        ?>
                        <li>
                           <a href="<?= site_url('shop/' . $cat['slug'] . $elt_qs); ?>" class="elt-dept-link <?= $is_curr ? 'is-active' : ''; ?>">
                              <span><?= html_escape($cat['name']); ?></span>
                              <span class="elt-dept-count"><?= (int)($cat['product_count'] ?? 0); ?></span>
                           </a>
                        </li>
                        <?php endforeach; ?>
                     </ul>
                  </div>

                  <!-- Price Range Filter -->
                  <div class="elt-filter-block">
                     <h6 class="elt-filter-heading">
                        <span>Max Price</span>
                        <span id="eltPriceValLabel" class="badge bg-dark text-white"><?= $curr; ?>2,500</span>
                     </h6>
                     <input type="range" id="eltPriceRange" class="form-range" min="50" max="2500" step="25" value="2500">
                     <div class="d-flex justify-content-between small text-muted mb-2">
                        <span><?= $curr; ?>50</span>
                        <span><?= $curr; ?>2,500+</span>
                     </div>
                     <div class="elt-price-brackets">
                        <button type="button" class="elt-price-chip" data-max-price="150">Under <?= $curr; ?>150</button>
                        <button type="button" class="elt-price-chip" data-max-price="350">Under <?= $curr; ?>350</button>
                        <button type="button" class="elt-price-chip" data-max-price="750">Under <?= $curr; ?>750</button>
                        <button type="button" class="elt-price-chip is-active" data-max-price="2500">Any Price</button>
                     </div>
                  </div>

                  <!-- Hardware Brands Filter -->
                  <?php if (!empty($tech_brands)): ?>
                  <div class="elt-filter-block">
                     <h6 class="elt-filter-heading">Hardware Brands</h6>
                     <?php foreach ($tech_brands as $b_idx => $b_name): ?>
                        <label class="elt-check-row">
                           <span class="d-flex align-items-center">
                              <input type="checkbox" class="elt-brand-checkbox" value="<?= html_escape($b_name); ?>">
                              <?= html_escape($b_name); ?>
                           </span>
                        </label>
                     <?php endforeach; ?>
                  </div>
                  <?php endif; ?>

                  <!-- Key Tech Specifications -->
                  <?php if (!empty($tech_specs)): ?>
                  <div class="elt-filter-block">
                     <h6 class="elt-filter-heading">Key Tech Specs</h6>
                     <?php foreach ($tech_specs as $spec): ?>
                        <label class="elt-check-row">
                           <span class="d-flex align-items-center">
                              <input type="checkbox" class="elt-spec-checkbox" value="<?= html_escape($spec); ?>">
                              <?= html_escape($spec); ?>
                           </span>
                        </label>
                     <?php endforeach; ?>
                  </div>
                  <?php endif; ?>
               </div>
            </div>

            <!-- Right Content: Control Bar + Electronics Product Grid -->
            <div class="col-xl-9 col-lg-8">

               <!-- Top Control Bar -->
               <div class="elt-control-bar">
                  <div class="d-flex align-items-center gap-2 ms-auto">
                     <!-- Grid / List View Switcher -->
                     <div class="d-none d-md-flex align-items-center gap-1 me-1">
                        <button type="button" class="elt-view-btn is-active" data-view-mode="grid-3" title="3-Column Grid">
                           <i class="fa-solid fa-table-cells-large"></i>
                        </button>
                        <button type="button" class="elt-view-btn" data-view-mode="grid-4" title="Compact 4-Column Grid">
                           <i class="fa-solid fa-table-cells"></i>
                        </button>
                        <button type="button" class="elt-view-btn" data-view-mode="list" title="Tech Spec List View">
                           <i class="fa-solid fa-list-ul"></i>
                        </button>
                     </div>

                     <!-- Sort Select -->
                     <select id="eltSortSelect" class="elt-sort-select" aria-label="Sort products">
                        <option value="featured">Sort: Featured Tech</option>
                        <option value="price-low-high" <?= ($filters['sort'] ?? '') === 'price-low-high' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price-high-low" <?= ($filters['sort'] ?? '') === 'price-high-low' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="a-z" <?= ($filters['sort'] ?? '') === 'a-z' ? 'selected' : ''; ?>>Name: A to Z</option>
                        <option value="z-a" <?= ($filters['sort'] ?? '') === 'z-a' ? 'selected' : ''; ?>>Name: Z to A</option>
                     </select>
                  </div>
               </div>

               <!-- Products Grid -->
               <div class="row g-3 row-cols-xl-3 row-cols-md-2 row-cols-1" id="eltProductsGrid">
                  <?php
                  $spec_pairs = [
                     ['Bluetooth 5.3', '40h Battery'],
                     ['AMOLED Display', 'IP68 Rated'],
                     ['5G Ready', '120Hz ProMotion'],
                     ['M3 / RTX Chip', 'Thunderbolt 4'],
                     ['4K 60fps HDR', '3-Axis Gimbal'],
                     ['120fps UHD', 'Haptic Triggers'],
                  ];
                  $pi = 1;
                  foreach ($products as $idx => $p):
                     $p_id     = (int)($p['id'] ?? $pi);
                     $p_name   = $p['name'] ?? ($p['title'] ?? 'Electronics Product');
                     $p_slug   = !empty($p['slug']) ? site_url('product/' . $p['slug'] . $elt_qs) : site_url('product/' . $p_id . $elt_qs);
                     $p_img    = elt_shop_prod_img($p, $pi++);
                     $p_cat    = !empty($p['category_name']) ? $p['category_name'] : ($selected_category['name'] ?? 'Electronics');
                     $p_price  = (!empty($p['sale_price']) && (float)$p['sale_price'] > 0 && (float)$p['sale_price'] < (float)$p['price'])
                                 ? (float)$p['sale_price']
                                 : (float)($p['price'] ?? 199);
                     $p_old    = (float)($p['price'] ?? $p_price);
                     $p_disc   = ($p_old > $p_price && $p_old > 0) ? round((($p_old - $p_price) / $p_old) * 100) : 0;
                     $p_rev    = (isset($p['reviews']) && is_array($p['reviews'])) ? count($p['reviews']) : (int)($p['reviews_count'] ?? 0);
                     $p_rating = $p_rev > 0 ? (float)($p['rating'] ?? 0) : 0.0;
                     $p_stars  = (int)round($p_rating);
                     $p_brand  = !empty($p['brand_name']) ? $p['brand_name'] : (!empty($p['brand']) ? $p['brand'] : (!empty($tech_brands) ? $tech_brands[$idx % count($tech_brands)] : ''));
                     $p_specs  = $spec_pairs[$idx % count($spec_pairs)];
                     $p_desc   = !empty($p['short_description']) ? $p['short_description'] : 'Engineered with next-gen hardware architecture, studio-grade acoustics, and all-day battery performance.';

                     $qv_json = htmlspecialchars(json_encode([
                        'id'       => $p_id,
                        'title'    => $p_name,
                        'price'    => $curr . number_format($p_price, 2),
                        'image'    => $p_img,
                        'category' => $p_cat,
                        'desc'     => mb_substr(strip_tags($p_desc), 0, 160),
                        'sku'      => $p['sku'] ?? ('TECH-' . (1000 + $p_id)),
                        'url'      => $p_slug,
                     ]), ENT_QUOTES, 'UTF-8');
                  ?>
                  <div class="col elt-product-col"
                       data-title="<?= html_escape(strtolower($p_name . ' ' . $p_cat . ' ' . $p_brand . ' ' . implode(' ', $p_specs))); ?>"
                       data-price="<?= $p_price; ?>"
                       data-rating="<?= $p_rating; ?>"
                       data-sale="<?= $p_disc > 0 ? '1' : '0'; ?>"
                       data-brand="<?= html_escape($p_brand); ?>"
                       data-order="<?= $idx; ?>">
                     <div class="elt-shop-card aq-product-item aq-product-main">
                        <div class="aq-product-thumb aq-img-hover-wrap p-relative">
                           <?php if ($p_disc > 0): ?>
                              <div class="aq-product-badge">
                                 <span class="clr-sale">-<?= $p_disc; ?>%</span>
                              </div>
                           <?php elseif ($p_brand !== ''): ?>
                              <div class="aq-product-badge">
                                 <span style="background:#0B0D0E;color:#E1EEB7;font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;"><?= html_escape($p_brand); ?></span>
                              </div>
                           <?php endif; ?>

                           <div class="aq-product-action pos-bottom-center">
                              <button type="button" class="aq-product-action-btn aq-tooltip-top" onclick="bazaroAddToCart(<?= $p_id; ?>, 1, true)">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                    <path d="M6.19751 0.75L3.30151 3.654M11.3015 0.75L14.1975 3.654M6.95776 10.3501V13.1901M10.6375 10.3501V13.1901M1.94997 7.14993L3.07797 14.0619C3.33397 15.6139 3.94997 16.7499 6.23796 16.7499H11.062C13.55 16.7499 13.918 15.6619 14.206 14.1579L15.55 7.14993M0.75 5.42996C0.75 3.94996 1.542 3.82996 2.526 3.82996H14.974C15.958 3.82996 16.75 3.94996 16.75 5.42996C16.75 7.14996 15.958 7.02996 14.974 7.02996H2.526C1.542 7.02996 0.75 7.14996 0.75 5.42996Z" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round"/>
                                 </svg>
                                 <span class="aq-tooltip-item">Add to Cart</span>
                              </button>
                              <button type="button" class="aq-product-action-btn aq-tooltip-top" onclick='bazaroQuickView(<?= $qv_json; ?>)'>
                                 <svg xmlns="http://www.w3.org/2000/svg" width="19" height="16" viewBox="0 0 19 16" fill="none">
                                    <path d="M12.0557 7.75429C12.0557 9.42922 10.7022 10.7827 9.0273 10.7827C7.35238 10.7827 5.99891 9.42922 5.99891 7.75429C5.99891 6.07937 7.35238 4.72589 9.0273 4.72589C10.7022 4.72589 12.0557 6.07937 12.0557 7.75429Z" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9.02734 14.75C12.0134 14.75 14.7965 12.9905 16.7337 9.94517C17.495 8.75242 17.495 6.74758 16.7337 5.55483C14.7965 2.50952 12.0134 0.75 9.02734 0.75C6.04124 0.75 3.25816 2.50952 1.321 5.55483C0.559668 6.74758 0.559668 8.75242 1.321 9.94517C3.25816 12.9905 6.04124 14.75 9.02734 14.75Z" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                 </svg>
                                 <span class="aq-tooltip-item">Quick View</span>
                              </button>
                              <button type="button" class="aq-product-action-btn aq-wishlist-btn aq-tooltip-top" onclick="bazaroToggleWishlist(<?= $p_id; ?>, this)">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="18" height="16" viewBox="0 0 18 16" fill="none">
                                    <path d="M14.7197 1.52347C12.5744 0.244089 10.7019 0.759666 9.57712 1.58092C9.11591 1.91766 8.88531 2.08602 8.74963 2.08602C8.61396 2.08602 8.38336 1.91766 7.92215 1.58092C6.79733 0.759666 4.9249 0.244089 2.77958 1.52347C-0.0359114 3.20253 -0.67299 8.7418 5.82126 13.4151C7.05821 14.3052 7.67668 14.7502 8.74963 14.7502C9.82258 14.7502 10.4411 14.3052 11.678 13.4151C18.1723 8.7418 17.5352 3.20253 14.7197 1.52347Z" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round"/>
                                 </svg>
                                 <span class="aq-tooltip-item">Wishlist</span>
                              </button>
                           </div>

                           <a href="<?= $p_slug; ?>">
                              <img class="aq-product-img" src="<?= $p_img; ?>" alt="<?= html_escape($p_name); ?>">
                           </a>
                        </div>

                        <div class="elt-card-body">
                           <div class="d-flex align-items-center justify-content-between mb-1">
                              <span class="text-uppercase fw-bold" style="font-size:11px;color:#64748b;letter-spacing:0.4px;">
                                 <?= html_escape($p_cat); ?>
                              </span>
                              <span class="small fw-semibold d-inline-flex align-items-center gap-1" style="font-size:12px;color:#f59e0b;">
                                 <span>
                                    <?php for ($si = 1; $si <= 5; $si++): ?>
                                       <i class="fa-<?= ($si <= $p_stars) ? 'solid' : 'regular'; ?> fa-star"></i>
                                    <?php endfor; ?>
                                 </span>
                                 <span class="text-muted fw-normal">(<?= $p_rev; ?>)</span>
                              </span>
                           </div>

                           <h4 class="aq-product-title mb-1" style="font-size:15.5px;font-weight:700;line-height:1.35;">
                              <a href="<?= $p_slug; ?>" class="text-dark text-decoration-none"><?= html_escape($p_name); ?></a>
                           </h4>

                           <!-- Hardware Spec Pills -->
                           <div class="elt-card-spec-badges">
                              <span class="elt-spec-tag"><i class="fa-solid fa-microchip me-1"></i><?= html_escape($p_specs[0]); ?></span>
                              <span class="elt-spec-tag"><?= html_escape($p_specs[1]); ?></span>
                           </div>

                           <div class="elt-card-bottom-row">
                              <div class="aq-product-price mb-0">
                                 <ins><span class="aq-product-new-price" style="font-size:18px;font-weight:800;color:#0B0D0E;"><?= $curr . number_format($p_price, 2); ?></span></ins>
                                 <?php if ($p_old > $p_price): ?>
                                    <del class="ms-1"><span class="aq-product-old-price" style="font-size:13px;color:#94a3b8;"><?= $curr . number_format($p_old, 2); ?></span></del>
                                 <?php endif; ?>
                              </div>
                              <button type="button" class="elt-add-cart-pill" onclick="bazaroAddToCart(<?= $p_id; ?>, 1, true)">
                                 <i class="fa-solid fa-cart-plus"></i>
                                 <span>Add</span>
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
                  <?php endforeach; ?>
               </div>

               <!-- Empty Filter State -->
               <div id="eltNoResultsBox" class="text-center py-5 bg-white rounded-4 border d-none">
                  <i class="fa-solid fa-microchip mb-3" style="font-size:38px;color:#94a3b8;"></i>
                  <h5 class="fw-bold text-dark">No matching electronics found</h5>
                  <p class="text-muted small mb-3">Try adjusting your price range, brand selection, or search keyword.</p>
                  <button type="button" class="aq-btn-black radius-30 border-0" onclick="document.getElementById('eltResetFiltersBtn').click();">
                     Reset Tech Filters
                  </button>
               </div>

            </div>
         </div>
      </div>
   </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
   var grid          = document.getElementById('eltProductsGrid');
   var cols          = Array.from(document.querySelectorAll('.elt-product-col'));
   var quickInput    = document.getElementById('eltQuickFilterInput');
   var saleBtn       = document.getElementById('eltSaleOnlyBtn');
   var sortSelect    = document.getElementById('eltSortSelect');
   var priceRange    = document.getElementById('eltPriceRange');
   var priceLabel    = document.getElementById('eltPriceValLabel');
   var priceChips    = document.querySelectorAll('.elt-price-chip');
   var brandChecks   = document.querySelectorAll('.elt-brand-checkbox');
   var specChecks    = document.querySelectorAll('.elt-spec-checkbox');
   var ratingRadios  = document.querySelectorAll('.elt-rating-radio');
   var resetBtn      = document.getElementById('eltResetFiltersBtn');
   var noResultsBox  = document.getElementById('eltNoResultsBox');
   var countBadge    = document.getElementById('eltHeroCountBadge');
   var viewBtns      = document.querySelectorAll('.elt-view-btn');
   var currSymbol    = window.CURRENCY_SYMBOL || '$';

   function applyTechFilters() {
      var q        = (quickInput ? quickInput.value.trim().toLowerCase() : '');
      var saleOnly = saleBtn && saleBtn.classList.contains('is-active');
      var maxPrice = priceRange ? parseFloat(priceRange.value) : 99999;
      var minRate  = 0;
      ratingRadios.forEach(function(r) { if (r.checked) minRate = parseFloat(r.value); });

      var selectedBrands = [];
      brandChecks.forEach(function(b) { if (b.checked) selectedBrands.push(b.value.toLowerCase()); });

      var visibleCount = 0;
      cols.forEach(function(col) {
         var title  = col.getAttribute('data-title') || '';
         var price  = parseFloat(col.getAttribute('data-price') || '0');
         var rating = parseFloat(col.getAttribute('data-rating') || '5');
         var isSale = col.getAttribute('data-sale') === '1';
         var brand  = (col.getAttribute('data-brand') || '').toLowerCase();

         var match = true;
         if (q && title.indexOf(q) === -1) match = false;
         if (saleOnly && !isSale) match = false;
         if (price > maxPrice) match = false;
         if (rating < minRate) match = false;
         if (selectedBrands.length > 0 && selectedBrands.indexOf(brand) === -1) match = false;

         if (match) {
            col.classList.remove('d-none');
            visibleCount++;
         } else {
            col.classList.add('d-none');
         }
      });

      // Sort visible items
      var sortVal = sortSelect ? sortSelect.value : 'featured';
      var sorted = cols.slice().sort(function(a, b) {
         var pa = parseFloat(a.getAttribute('data-price') || '0');
         var pb = parseFloat(b.getAttribute('data-price') || '0');
         var ta = a.getAttribute('data-title') || '';
         var tb = b.getAttribute('data-title') || '';
         var oa = parseInt(a.getAttribute('data-order') || '0', 10);
         var ob = parseInt(b.getAttribute('data-order') || '0', 10);

         if (sortVal === 'price-low-high') return pa - pb;
         if (sortVal === 'price-high-low') return pb - pa;
         if (sortVal === 'a-z') return ta.localeCompare(tb);
         if (sortVal === 'z-a') return tb.localeCompare(ta);
         return oa - ob;
      });
      sorted.forEach(function(col) { grid.appendChild(col); });

      if (countBadge) {
         countBadge.textContent = visibleCount + ' Verified Item' + (visibleCount === 1 ? '' : 's');
      }
      if (noResultsBox) {
         if (visibleCount === 0) noResultsBox.classList.remove('d-none');
         else noResultsBox.classList.add('d-none');
      }
   }

   if (quickInput) quickInput.addEventListener('input', applyTechFilters);
   if (saleBtn) {
      saleBtn.addEventListener('click', function() {
         this.classList.toggle('is-active');
         applyTechFilters();
      });
   }
   if (sortSelect) sortSelect.addEventListener('change', applyTechFilters);
   if (priceRange) {
      priceRange.addEventListener('input', function() {
         if (priceLabel) priceLabel.textContent = currSymbol + parseInt(this.value, 10).toLocaleString();
         priceChips.forEach(function(c) { c.classList.remove('is-active'); });
         applyTechFilters();
      });
   }
   priceChips.forEach(function(chip) {
      chip.addEventListener('click', function() {
         var maxP = this.getAttribute('data-max-price');
         priceChips.forEach(function(c) { c.classList.remove('is-active'); });
         this.classList.add('is-active');
         if (priceRange) {
            priceRange.value = maxP;
            if (priceLabel) priceLabel.textContent = currSymbol + parseInt(maxP, 10).toLocaleString();
         }
         applyTechFilters();
      });
   });
   brandChecks.forEach(function(cb) { cb.addEventListener('change', applyTechFilters); });
   specChecks.forEach(function(cb) { cb.addEventListener('change', applyTechFilters); });
   ratingRadios.forEach(function(rb) { rb.addEventListener('change', applyTechFilters); });

   if (resetBtn) {
      resetBtn.addEventListener('click', function() {
         if (quickInput) quickInput.value = '';
         if (saleBtn) saleBtn.classList.remove('is-active');
         if (priceRange) priceRange.value = 2500;
         if (priceLabel) priceLabel.textContent = currSymbol + '2,500';
         priceChips.forEach(function(c) { c.classList.remove('is-active'); });
         if (priceChips.length > 0) priceChips[priceChips.length - 1].classList.add('is-active');
         brandChecks.forEach(function(cb) { cb.checked = false; });
         specChecks.forEach(function(cb) { cb.checked = false; });
         ratingRadios.forEach(function(rb) { rb.checked = (rb.value === '0'); });
         if (sortSelect) sortSelect.value = 'featured';
         applyTechFilters();
      });
   }

   // View Mode Switcher (3-col, 4-col, Spec List)
   viewBtns.forEach(function(btn) {
      btn.addEventListener('click', function() {
         viewBtns.forEach(function(b) { b.classList.remove('is-active'); });
         this.classList.add('is-active');
         var mode = this.getAttribute('data-view-mode');
         grid.classList.remove('row-cols-xl-3', 'row-cols-xl-4', 'is-list-view');
         if (mode === 'grid-4') {
            grid.classList.add('row-cols-xl-4');
         } else if (mode === 'list') {
            grid.classList.add('row-cols-xl-3', 'is-list-view');
         } else {
            grid.classList.add('row-cols-xl-3');
         }
      });
   });
});
</script>
