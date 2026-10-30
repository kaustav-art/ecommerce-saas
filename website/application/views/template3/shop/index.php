<?php
/**
 * Template 3 — Bazaro Fresh Grocery Store Shop / Category Catalog Page
 * Matches Template 2 category hero & sidebar navigation structure with
 * Home Page Grocery product cards (.grc-product-style / .aq-product-item.aq-product-main).
 */
$bazaro = isset($tpl_assets) ? rtrim($tpl_assets, '/') . '/' : base_url('assets/templates/bazaro/');
$curr   = isset($currency_symbol) ? $currency_symbol : '₹';

if (!function_exists('grc_product_img')) {
    function grc_product_img($img, $bazaro_url, $fallback_idx = 1) {
        if (!empty($img)) {
            if (strpos($img, 'http') === 0) return $img;
            if (strpos($img, 'assets/') === 0) return base_url($img);
            $clean = ltrim($img, '/');
            if (file_exists(FCPATH . 'assets/images/' . $clean)) {
                return base_url('assets/images/' . $clean);
            }
            if (file_exists(FCPATH . 'assets/templates/bazaro/img/' . $clean)) {
                return base_url('assets/templates/bazaro/img/' . $clean);
            }
            return base_url('assets/images/products/' . $clean);
        }
        $idx = (($fallback_idx - 1) % 16) + 1;
        return $bazaro_url . 'img/product/grocery-p-' . $idx . '.jpg';
    }
}

$shop_products   = !empty($products) ? $products : [];
$cats_list       = !empty($all_categories) ? $all_categories : (!empty($nav_categories) ? $nav_categories : []);
$active_cat_slug = $filters['category_slug'] ?? ($selected_category['slug'] ?? '');
$heading_text    = !empty($page_heading) ? $page_heading : (!empty($selected_category['name']) ? $selected_category['name'] : 'Shop All Groceries');
$grc_qs          = empty($is_user_project) ? '?preview_tpl=grocery' : '';
$total_all_cnt   = isset($total_all_products) ? (int)$total_all_products : count($shop_products);

// Dynamic max price for price slider & quick bracket chips
$highest_p = isset($max_catalog_price) ? (float)$max_catalog_price : 25.0;
foreach ($shop_products as $_sp) {
    $_ep = (!empty($_sp['sale_price']) && (float)$_sp['sale_price'] > 0) ? (float)$_sp['sale_price'] : (float)($_sp['price'] ?? 0);
    if ($_ep > $highest_p) {
        $highest_p = $_ep;
    }
}
$slider_max  = max(25, (int)(ceil($highest_p / 10) * 10));
$slider_step = $slider_max > 100 ? 5 : 1;
$chip_p1     = max(5, (int)(round(($slider_max * 0.25) / 5) * 5));
$chip_p2     = max(10, (int)(round(($slider_max * 0.50) / 5) * 5));
$chip_p3     = max(15, (int)(round(($slider_max * 0.75) / 5) * 5));

// Dynamic brands from DB
$dynamic_brands_list = [];
if (!empty($brands) && is_array($brands)) {
    foreach ($brands as $b_row) {
        if (!empty($b_row['name'])) {
            $dynamic_brands_list[] = [
                'name'  => $b_row['name'],
                'slug'  => $b_row['slug'] ?? '',
                'count' => (int)($b_row['product_count'] ?? 0),
            ];
        }
    }
}
$grc_brands = ['NatureFresh Organic', 'GreenValley Farms', 'HarvestGold Dairy', 'Artisan Bakehouse', 'EarthBounty Organics', 'PureOrchard Juices'];
if (empty($dynamic_brands_list) && empty($is_user_project)) {
    foreach ($grc_brands as $gb_idx => $gb_name) {
        $dynamic_brands_list[] = [
            'name'  => $gb_name,
            'slug'  => url_title(strtolower($gb_name), '-', TRUE),
            'count' => (($gb_idx * 2 + 3) % 7) + 2,
        ];
    }
}

if (!function_exists('grc_shop_unit_calc')) {
    function grc_shop_unit_calc($prod, $idx = 0) {
        $label  = trim((string)($prod['unit_label'] ?? ''));
        $u_type = isset($prod['unit_type']) ? $prod['unit_type'] : '';
        $u_qty  = isset($prod['unit_qty']) ? (float)$prod['unit_qty'] : 0;

        if (!$u_type || $u_qty <= 0) {
            $defaults = ['500g', '4 pcs', '400g', '1 kg', '500g', '1 pc', '12 pcs', '250g'];
            if ($label === '') {
                $label = $defaults[$idx % count($defaults)];
            }
            $lower = strtolower($label);
            if (preg_match('/(\d+(?:\.\d+)?)\s*(pc|pcs|piece|pieces|loaf|box|carton)/i', $lower, $m)) {
                $u_type = 'pc';
                $u_qty  = max(1, (float)$m[1]);
            } elseif (preg_match('/(\d+(?:\.\d+)?)\s*(g|gm|gram)/i', $lower, $m)) {
                $u_type = 'kg';
                $u_qty  = max(0.05, (float)$m[1] / 1000);
            } elseif (preg_match('/(\d+(?:\.\d+)?)\s*(kg|l|ltr|liter|litre)/i', $lower, $m)) {
                $u_type = 'kg';
                $u_qty  = max(0.1, (float)$m[1]);
            } else {
                $u_type = ($idx % 3 === 1) ? 'pc' : 'kg';
                $u_qty  = ($u_type === 'pc') ? 4 : 0.5;
            }
        }

        if (preg_match('/^(\d+(?:\.\d+)?)\s*(kg|g|gm|ml|l|ltr|liter|litre|liters|litres|pc|pcs|piece|pieces)\b/i', $label, $um)) {
            $u_word = $um[2];
            if (stripos($u_word, 'piece') === 0) {
                $u_word = ((float)$um[1] > 1) ? 'pcs' : 'pc';
            }
            $label = $um[1] . (in_array(strtolower($u_word), ['g', 'gm', 'ml'], true) ? strtolower($u_word) : ' ' . $u_word);
        } elseif (preg_match('/^(\d+)\b/', $label, $um)) {
            $label = $um[1] . (((int)$um[1] > 1) ? ' pcs' : ' pc');
        }

        $sale = (!empty($prod['sale_price']) && (float)$prod['sale_price'] > 0)
            ? (float)$prod['sale_price']
            : (float)($prod['price'] ?? 3.49);
        if ($sale <= 0) $sale = 3.49;

        $reg = (float)($prod['regular_price'] ?? ($prod['price'] ?? $sale));
        if ($reg < $sale) $reg = $sale;

        $unit_rate = $u_qty > 0 ? round($sale / $u_qty, 2) : $sale;
        return [
            'pack_label' => $label ?: ($u_type === 'pc' ? ((int)$u_qty . ' pcs') : ($u_qty < 1 ? (int)round($u_qty * 1000) . 'g' : $u_qty . ' kg')),
            'unit_type'  => $u_type,
            'unit_qty'   => $u_qty,
            'sale'       => $sale,
            'reg'        => $reg,
            'unit_rate'  => $unit_rate,
        ];
    }
}
?>

<style>
   /* ==========================================================================
      Template 3 (Bazaro Grocery) Dedicated Shop & Category Page Styles
      Theme: Deep Forest Green (#003D29) + Fresh Harvest Green (#108910)
      ========================================================================== */
   .grc-shop-hero {
      background: linear-gradient(135deg, #003D29 0%, #07593e 60%, #0b6b4b 100%);
      color: #ffffff;
      padding: 44px 0 34px;
      position: relative;
      overflow: hidden;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
   }
   .grc-shop-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image: radial-gradient(rgba(187, 247, 208, 0.12) 1px, transparent 1px);
      background-size: 24px 24px;
      pointer-events: none;
   }
   .grc-shop-hero-title {
      font-size: 40px;
      font-weight: 600;
      color: #ffffff;
      margin: 0 0 8px;
      letter-spacing: -0.6px;
   }
   .grc-shop-breadcrumbs {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 8px;
      list-style: none;
      margin: 0;
      padding: 0;
      font-size: 13px;
      color: rgba(255, 255, 255, 0.7);
   }
   .grc-shop-breadcrumbs a {
      color: rgba(255, 255, 255, 0.8);
      text-decoration: none;
      transition: color 0.15s ease;
   }
   .grc-shop-breadcrumbs a:hover {
      color: #bbf7d0;
   }
   .grc-shop-breadcrumbs .active {
      color: #bbf7d0;
      font-weight: 600;
   }

   /* Main Shop Layout */
   .grc-shop-section {
      background: #f4f8f5;
      padding: 40px 0 90px;
      min-height: 70vh;
   }

   /* Left Filter Sidebar */
   .grc-filter-card {
      background: #ffffff;
      border-radius: 16px;
      border: 1px solid #e2efe6;
      padding: 22px;
      box-shadow: 0 4px 18px rgba(0, 61, 41, 0.04);
   }
   .grc-filter-block {
      padding-bottom: 20px;
      margin-bottom: 20px;
      border-bottom: 1px solid #e8f2ec;
   }
   .grc-filter-block:last-child {
      padding-bottom: 0;
      margin-bottom: 0;
      border-bottom: none;
   }
   .grc-filter-heading {
      font-size: 13.5px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #003D29;
      margin: 0 0 14px;
      display: flex;
      align-items: center;
      justify-content: space-between;
   }
   .grc-dept-list {
      list-style: none;
      margin: 0;
      padding: 0;
      display: flex;
      flex-direction: column;
      gap: 4px;
   }
   .grc-dept-link {
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
   .grc-dept-link:hover {
      background: #ecfdf5;
      color: #003D29;
   }
   .grc-dept-link.is-active {
      background: #003D29;
      color: #ffffff;
      font-weight: 700;
   }
   .grc-dept-count {
      font-size: 11px;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 20px;
      background: rgba(0, 61, 41, 0.07);
      color: #003D29;
   }
   .grc-dept-link.is-active .grc-dept-count {
      background: #108910;
      color: #ffffff;
   }

   /* Checkboxes & Price Brackets */
   .grc-check-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 5px 0;
      cursor: pointer;
      font-size: 13.5px;
      color: #334155;
   }
   .grc-price-brackets {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 7px;
      margin-top: 10px;
   }
   .grc-price-chip {
      border: 1px solid #d1e7dd;
      background: #f4fbf6;
      color: #003D29;
      font-size: 12px;
      font-weight: 600;
      padding: 6px 8px;
      border-radius: 8px;
      text-align: center;
      cursor: pointer;
      transition: all 0.15s ease;
   }
   .grc-price-chip:hover,
   .grc-price-chip.is-active {
      background: #003D29;
      color: #ffffff;
      border-color: #003D29;
   }

   /* Top Control Bar (Right-aligned like Electronics) */
   .grc-control-bar {
      padding: 12px 18px;
      margin-bottom: 24px;
      display: flex;
      align-items: center;
      justify-content: flex-end;
      flex-wrap: wrap;
      gap: 12px;
      background: #ffffff;
      border-radius: 14px;
      border: 1px solid #e2efe6;
      box-shadow: 0 2px 10px rgba(0, 61, 41, 0.03);
   }
   .grc-view-btn {
      width: 36px;
      height: 36px;
      border-radius: 8px;
      border: 1px solid #d1e7dd;
      background: #ffffff;
      color: #64748b;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.15s ease;
   }
   .grc-view-btn.is-active,
   .grc-view-btn:hover {
      background: #003D29;
      color: #ffffff;
      border-color: #003D29;
   }
   .grc-sort-select {
      height: 38px;
      border-radius: 8px;
      border: 1px solid #d1e7dd;
      background: #f4fbf6;
      padding: 0 12px;
      font-size: 13px;
      font-weight: 600;
      color: #003D29;
      outline: none;
      cursor: pointer;
   }

   /* Home Page Product Card Styling inside Shop Grid */
   #grcShopProductsGrid .aq-product-item.aq-product-main {
      background: #ffffff;
      border-radius: 14px;
      border: 1px solid #e2efe6;
      padding: 14px;
      margin-bottom: 0 !important;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: all 0.22s ease;
   }
   #grcShopProductsGrid .aq-product-item.aq-product-main:hover {
      border-color: #86efac;
      box-shadow: 0 10px 26px rgba(0, 61, 41, 0.08);
      transform: translateY(-3px);
   }
   #grcShopProductsGrid .aq-product-content {
      flex-grow: 1;
      margin-bottom: 10px;
   }

   /* List View Mode */
   #grcShopProductsGrid.is-list-view > .grc-aisle-col {
      width: 100% !important;
      flex: 0 0 100% !important;
   }
   #grcShopProductsGrid.is-list-view .aq-product-item.aq-product-main {
      flex-direction: row;
      align-items: center;
      gap: 24px;
      padding: 18px;
   }
   #grcShopProductsGrid.is-list-view .aq-product-thumb {
      width: 210px;
      flex-shrink: 0;
      margin-bottom: 0 !important;
   }
   #grcShopProductsGrid.is-list-view .grc-product-btn {
      width: 170px;
      flex-shrink: 0;
   }
   @media (max-width: 767.98px) {
      .grc-shop-hero-title {
         font-size: 26px;
      }
      #grcShopProductsGrid.is-list-view .aq-product-item.aq-product-main {
         flex-direction: column;
         align-items: stretch;
      }
      #grcShopProductsGrid.is-list-view .aq-product-thumb,
      #grcShopProductsGrid.is-list-view .grc-product-btn {
         width: 100%;
      }
   }
</style>

<main>
   <!-- 1. Forest Green Grocery Category Hero Banner -->
   <section class="grc-shop-hero">
      <div class="container position-relative" style="z-index:2;">
         <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
            <div>
               <h1 class="grc-shop-hero-title" id="grcShopPageHeading"><?= html_escape($heading_text); ?></h1>
               <ul class="grc-shop-breadcrumbs">
                  <li><a href="<?= site_url('home' . $grc_qs); ?>"><i class="fa-solid fa-house me-1"></i>Home</a></li>
                  <li><i class="fa-solid fa-angle-right" style="font-size:11px;"></i></li>
                  <li><a href="<?= site_url('shop' . $grc_qs); ?>">Grocery Catalog</a></li>
                  <?php if (!empty($breadcrumbs) && is_array($breadcrumbs)): ?>
                     <?php foreach ($breadcrumbs as $bc_i => $bc_item):
                        $is_last_bc = ($bc_i === count($breadcrumbs) - 1);
                     ?>
                        <li><i class="fa-solid fa-angle-right" style="font-size:11px;"></i></li>
                        <?php if ($is_last_bc || empty($bc_item['slug'])): ?>
                           <li class="active"><?= html_escape($bc_item['name']); ?></li>
                        <?php else: ?>
                           <li><a href="<?= site_url('shop/' . $bc_item['slug'] . $grc_qs); ?>"><?= html_escape($bc_item['name']); ?></a></li>
                        <?php endif; ?>
                     <?php endforeach; ?>
                  <?php elseif (!empty($selected_category['name'])): ?>
                     <li><i class="fa-solid fa-angle-right" style="font-size:11px;"></i></li>
                     <li class="active"><?= html_escape($selected_category['name']); ?></li>
                  <?php endif; ?>
               </ul>
            </div>
         </div>
      </div>
   </section>

   <!-- 2. Grocery Catalog & Sidebar Filters Section -->
   <section class="grc-shop-section">
      <div class="container">
         <div class="row g-4">

            <!-- Left Sidebar: Grocery Filters -->
            <div class="col-xl-3 col-lg-4">
               <div class="grc-filter-card">
                  <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                     <span class="fw-bold d-flex align-items-center gap-2" style="font-size:15px; color:#003D29;">
                        <i class="fa-solid fa-sliders text-success"></i> Grocery Filters
                     </span>
                     <button type="button" id="grcResetFiltersBtn" class="btn btn-sm p-0 border-0 text-danger fw-semibold" style="font-size:12.5px;" onclick="resetGrcAisleFilters()">
                        Reset All
                     </button>
                  </div>

                  <!-- Departments Filter (Dynamic Categories from Template 3) -->
                  <div class="grc-filter-block">
                     <h6 class="grc-filter-heading">Departments</h6>
                     <ul class="grc-dept-list">
                        <li>
                           <a href="<?= site_url('shop' . $grc_qs); ?>" class="grc-dept-link <?= empty($active_cat_slug) ? 'is-active' : ''; ?>">
                              <span>All Groceries</span>
                              <span class="grc-dept-count"><?= $total_all_cnt; ?></span>
                           </a>
                        </li>
                        <?php foreach ($cats_list as $cat):
                           $is_curr = (!empty($active_cat_slug) && strcasecmp($active_cat_slug, $cat['slug']) === 0);
                           $in_branch = $is_curr || (!empty($active_cat_slug) && !empty($cat['descendant_slugs']) && in_array(strtolower($active_cat_slug), $cat['descendant_slugs'], true));
                        ?>
                        <li>
                           <a href="<?= site_url('shop/' . $cat['slug'] . $grc_qs); ?>" class="grc-dept-link <?= $is_curr ? 'is-active' : ''; ?>">
                              <span><?= html_escape($cat['name']); ?></span>
                              <span class="grc-dept-count"><?= (int)($cat['product_count'] ?? 0); ?></span>
                           </a>
                           <?php if ($in_branch && !empty($cat['children'])): ?>
                           <ul class="list-unstyled ps-3 mt-1 mb-1">
                              <?php foreach ($cat['children'] as $subcat):
                                 $is_sub_curr = (!empty($active_cat_slug) && strcasecmp($active_cat_slug, $subcat['slug']) === 0);
                              ?>
                              <li>
                                 <a href="<?= site_url('shop/' . $subcat['slug'] . $grc_qs); ?>" class="grc-dept-link py-1 <?= $is_sub_curr ? 'is-active' : ''; ?>" style="font-size:12.5px;">
                                    <span>&rsaquo; <?= html_escape($subcat['name']); ?></span>
                                    <span class="grc-dept-count"><?= (int)($subcat['product_count'] ?? 0); ?></span>
                                 </a>
                                 <?php if (!empty($subcat['children'])): ?>
                                 <ul class="list-unstyled ps-3 mt-1">
                                    <?php foreach ($subcat['children'] as $gchild):
                                       $is_gc_curr = (!empty($active_cat_slug) && strcasecmp($active_cat_slug, $gchild['slug']) === 0);
                                    ?>
                                    <li>
                                       <a href="<?= site_url('shop/' . $gchild['slug'] . $grc_qs); ?>" class="grc-dept-link py-1 <?= $is_gc_curr ? 'is-active' : ''; ?>" style="font-size:12px;">
                                          <span>&bull; <?= html_escape($gchild['name']); ?></span>
                                          <span class="grc-dept-count"><?= (int)($gchild['product_count'] ?? 0); ?></span>
                                       </a>
                                    </li>
                                    <?php endforeach; ?>
                                 </ul>
                                 <?php endif; ?>
                              </li>
                              <?php endforeach; ?>
                           </ul>
                           <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                     </ul>
                  </div>

                  <!-- Selling Unit Type Filter (Per Kg vs Per Pc) -->
                  <div class="grc-filter-block">
                     <h6 class="grc-filter-heading">Selling Unit Type</h6>
                     <label class="grc-check-row">
                        <span class="d-flex align-items-center gap-2">
                           <input type="radio" name="grc_unit_filter" class="form-check-input" value="all" checked onchange="filterGrcAisleCards()">
                           <span>All Units (Kg &amp; Pc)</span>
                        </span>
                     </label>
                     <label class="grc-check-row">
                        <span class="d-flex align-items-center gap-2">
                           <input type="radio" name="grc_unit_filter" class="form-check-input" value="kg" onchange="filterGrcAisleCards()">
                           <span>By Weight (Per Kg Price)</span>
                        </span>
                     </label>
                     <label class="grc-check-row">
                        <span class="d-flex align-items-center gap-2">
                           <input type="radio" name="grc_unit_filter" class="form-check-input" value="pc" onchange="filterGrcAisleCards()">
                           <span>By Piece (Per Pc Price)</span>
                        </span>
                     </label>
                  </div>

                  <!-- Price Range Filter -->
                  <div class="grc-filter-block">
                     <h6 class="grc-filter-heading">
                        <span>Max Pack Price</span>
                        <span id="grcPriceRangeLabel" class="badge" style="background:#003D29; color:#fff;"><?= html_escape($curr) . number_format($slider_max, 2); ?></span>
                     </h6>
                     <input type="range" id="grcMaxPriceSlider" class="form-range" min="1" max="<?= $slider_max; ?>" step="<?= $slider_step; ?>" value="<?= $slider_max; ?>" data-default-max="<?= $slider_max; ?>" oninput="filterGrcAisleCards()">
                     <div class="d-flex justify-content-between small text-muted mb-2">
                        <span><?= html_escape($curr); ?>1.00</span>
                        <span><?= html_escape($curr) . number_format($slider_max, 2); ?>+</span>
                     </div>
                     <div class="grc-price-brackets">
                        <button type="button" class="grc-price-chip" data-max-price="<?= $chip_p1; ?>" onclick="selectGrcPriceChip(this, <?= $chip_p1; ?>)">Under <?= html_escape($curr) . $chip_p1; ?></button>
                        <button type="button" class="grc-price-chip" data-max-price="<?= $chip_p2; ?>" onclick="selectGrcPriceChip(this, <?= $chip_p2; ?>)">Under <?= html_escape($curr) . $chip_p2; ?></button>
                        <button type="button" class="grc-price-chip" data-max-price="<?= $chip_p3; ?>" onclick="selectGrcPriceChip(this, <?= $chip_p3; ?>)">Under <?= html_escape($curr) . $chip_p3; ?></button>
                        <button type="button" class="grc-price-chip is-active" data-max-price="<?= $slider_max; ?>" onclick="selectGrcPriceChip(this, <?= $slider_max; ?>)">Any Price</button>
                     </div>
                  </div>

                  <!-- Organic Farm Brands -->
                  <?php if (!empty($dynamic_brands_list)): ?>
                  <div class="grc-filter-block">
                     <h6 class="grc-filter-heading">Brands &amp; Farms</h6>
                     <?php foreach ($dynamic_brands_list as $b_item): ?>
                        <label class="grc-check-row">
                           <span class="d-flex align-items-center gap-2">
                              <input type="checkbox" class="grc-brand-checkbox form-check-input" value="<?= html_escape($b_item['name']); ?>" onchange="filterGrcAisleCards()">
                              <span><?= html_escape($b_item['name']); ?></span>
                           </span>
                           <span class="text-muted small">(<?= (int)$b_item['count']; ?>)</span>
                        </label>
                     <?php endforeach; ?>
                  </div>
                  <?php endif; ?>
               </div>
            </div>

            <!-- Right Content: Control Bar + Home Page Style Grocery Product Grid -->
            <div class="col-xl-9 col-lg-8">

               <!-- Top Control Bar -->
               <div class="grc-control-bar">
                  <div class="d-flex align-items-center gap-2 ms-auto">
                     <!-- Grid / List View Switcher -->
                     <div class="d-none d-md-flex align-items-center gap-1 me-1">
                        <button type="button" class="grc-view-btn is-active" data-view-mode="grid-3" title="3-Column Grid" onclick="switchGrcViewMode(this, 'grid-3')">
                           <i class="fa-solid fa-table-cells-large"></i>
                        </button>
                        <button type="button" class="grc-view-btn" data-view-mode="grid-4" title="Compact 4-Column Grid" onclick="switchGrcViewMode(this, 'grid-4')">
                           <i class="fa-solid fa-table-cells"></i>
                        </button>
                        <button type="button" class="grc-view-btn" data-view-mode="list" title="List View" onclick="switchGrcViewMode(this, 'list')">
                           <i class="fa-solid fa-list-ul"></i>
                        </button>
                     </div>

                     <!-- Sort Select -->
                     <select id="grcSortSelect" class="grc-sort-select" aria-label="Sort products" onchange="sortGrcAisleCards()">
                        <option value="featured">Sort: Featured Harvest</option>
                        <option value="price_asc">Price: Low to High</option>
                        <option value="price_desc">Price: High to Low</option>
                        <option value="unit_asc">Unit Rate: Low to High</option>
                        <option value="a_z">Name: A to Z</option>
                     </select>
                  </div>
               </div>

               <!-- Products Grid (Exact Home Page Product Card Structure) -->
               <div class="grc-product-style">
                  <div class="row g-3 row-cols-xl-3 row-cols-md-2 row-cols-sm-2 row-cols-1" id="grcShopProductsGrid">
                     <?php $this->load->view('template3/shop/partials/_product_items', [
                        'shop_products'     => $shop_products,
                        'user_wishlist_ids' => $user_wishlist_ids ?? [],
                        'curr'              => $curr,
                        'bazaro'            => $bazaro,
                        'grc_qs'            => $grc_qs,
                        'grc_brands'        => $grc_brands,
                        'selected_category' => $selected_category ?? [],
                        'offset'            => $offset ?? 0
                     ]); ?>
                  </div>
               </div>

               <!-- Infinite Scroll Sentinel & Loading Indicator -->
               <div id="grcInfiniteScrollSentinel" class="text-center py-4 my-2" data-has-more="<?= !empty($has_more) ? '1' : '0'; ?>" data-next-offset="<?= !empty($next_offset) ? (int)$next_offset : count($shop_products); ?>" data-limit="<?= !empty($limit) ? (int)$limit : 9; ?>">
                  <div id="grcInfiniteLoading" style="display: none;">
                     <div class="spinner-border spinner-border-sm text-success me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                     </div>
                     <span class="text-muted fw-semibold small">Loading next products...</span>
                  </div>
                  <div id="grcInfiniteEnd" style="<?= (empty($has_more) && !empty($shop_products)) ? 'display: block;' : 'display: none;'; ?>" class="text-muted small">
                     <i class="fa-solid fa-check-double text-success me-1"></i> You've viewed all <?= !empty($total_products) ? (int)$total_products : count($shop_products); ?> products.
                  </div>
               </div>

               <!-- Empty Filter State -->
               <div id="grcAisleEmptyState" class="text-center bg-white rounded-4 p-5 border mt-3" style="<?= empty($shop_products) ? 'display:block;' : 'display:none;'; ?>">
                  <i class="fa-solid fa-seedling fs-1 text-success mb-3"></i>
                  <h5 class="fw-bold" style="color:#003D29;">No matching grocery items found</h5>
                  <p class="text-muted small mb-3">Try adjusting your unit type, farm brand, or price range filter.</p>
                  <button type="button" class="btn btn-primary btn-sm px-4" onclick="resetGrcAisleFilters()">Reset Grocery Filters</button>
               </div>

            </div>

         </div>
      </div>
   </section>
</main>

<script>
(function() {
    window.selectGrcPriceChip = function(btn, maxP) {
        document.querySelectorAll('.grc-price-chip').forEach(function(c) {
            c.classList.remove('is-active');
        });
        btn.classList.add('is-active');
        var slider = document.getElementById('grcMaxPriceSlider');
        if (slider) slider.value = maxP;
        filterGrcAisleCards();
    };

    window.filterGrcAisleCards = function() {
        var unitRadio = document.querySelector('input[name="grc_unit_filter"]:checked');
        var unitVal = unitRadio ? unitRadio.value : 'all';
        var sliderEl = document.getElementById('grcMaxPriceSlider');
        var defMax = sliderEl ? parseFloat(sliderEl.getAttribute('data-default-max') || '25') : 25;
        var maxPrice = parseFloat(sliderEl?.value || String(defMax));
        var priceLbl = document.getElementById('grcPriceRangeLabel');
        if (priceLbl) priceLbl.textContent = (window.CURRENCY_SYMBOL || '₹') + maxPrice.toFixed(2);

        var selectedBrands = [];
        document.querySelectorAll('.grc-brand-checkbox:checked').forEach(function(cb) {
            selectedBrands.push(cb.value.toLowerCase());
        });

        var cols = document.querySelectorAll('#grcShopProductsGrid .grc-aisle-col');
        var visible = 0;
        cols.forEach(function(col) {
            var unit  = col.getAttribute('data-unit') || 'kg';
            var price = parseFloat(col.getAttribute('data-price') || '0');
            var brand = col.getAttribute('data-brand') || '';

            var matchU = (unitVal === 'all') || (unit === unitVal);
            var matchP = price <= maxPrice;
            var matchB = (selectedBrands.length === 0) || (selectedBrands.indexOf(brand) !== -1);

            if (matchU && matchP && matchB) {
                col.style.display = '';
                visible++;
            } else {
                col.style.display = 'none';
            }
        });

        var emptyEl = document.getElementById('grcAisleEmptyState');
        if (emptyEl) emptyEl.style.display = visible === 0 ? 'block' : 'none';
    };

    window.sortGrcAisleCards = function() {
        var sortBy = document.getElementById('grcSortSelect')?.value || 'featured';
        var grid = document.getElementById('grcShopProductsGrid');
        if (!grid) return;
        var cols = Array.prototype.slice.call(grid.querySelectorAll('.grc-aisle-col'));
        cols.sort(function(a, b) {
            if (sortBy === 'price_asc') {
                return parseFloat(a.getAttribute('data-price')) - parseFloat(b.getAttribute('data-price'));
            }
            if (sortBy === 'price_desc') {
                return parseFloat(b.getAttribute('data-price')) - parseFloat(a.getAttribute('data-price'));
            }
            if (sortBy === 'unit_asc') {
                return parseFloat(a.getAttribute('data-unitrate')) - parseFloat(b.getAttribute('data-unitrate'));
            }
            if (sortBy === 'a_z') {
                return (a.getAttribute('data-title') || '').localeCompare(b.getAttribute('data-title') || '');
            }
            return parseInt(a.getAttribute('data-index'), 10) - parseInt(b.getAttribute('data-index'), 10);
        });
        cols.forEach(function(c) { grid.appendChild(c); });
    };

    window.switchGrcViewMode = function(btn, mode) {
        document.querySelectorAll('.grc-view-btn').forEach(function(b) {
            b.classList.remove('is-active');
        });
        btn.classList.add('is-active');
        var grid = document.getElementById('grcShopProductsGrid');
        if (!grid) return;
        grid.classList.remove('row-cols-xl-3', 'row-cols-xl-4', 'is-list-view');
        if (mode === 'grid-4') {
            grid.classList.add('row-cols-xl-4');
        } else if (mode === 'list') {
            grid.classList.add('row-cols-xl-3', 'is-list-view');
        } else {
            grid.classList.add('row-cols-xl-3');
        }
    };

    window.resetGrcAisleFilters = function() {
        var allUnit = document.querySelector('input[name="grc_unit_filter"][value="all"]');
        if (allUnit) allUnit.checked = true;
        var slider = document.getElementById('grcMaxPriceSlider');
        var defMax = slider ? (slider.getAttribute('data-default-max') || '25') : '25';
        if (slider) slider.value = defMax;
        document.querySelectorAll('.grc-price-chip').forEach(function(c) { c.classList.remove('is-active'); });
        var lastChip = document.querySelector('.grc-price-chip[data-max-price="' + defMax + '"]');
        if (lastChip) lastChip.classList.add('is-active');
        document.querySelectorAll('.grc-brand-checkbox').forEach(function(cb) { cb.checked = false; });
        var sortEl = document.getElementById('grcSortSelect');
        if (sortEl) sortEl.value = 'featured';
        sortGrcAisleCards();
        filterGrcAisleCards();
    };

    // Infinite Scroll Implementation
    (function() {
        var sentinel = document.getElementById('grcInfiniteScrollSentinel');
        var grid = document.getElementById('grcShopProductsGrid');
        if (!sentinel || !grid) return;

        var isLoading = false;

        function loadNextBatch() {
            var hasMore = sentinel.getAttribute('data-has-more');
            if (isLoading || hasMore !== '1') return;

            var nextOffset = parseInt(sentinel.getAttribute('data-next-offset') || '9', 10);
            var limit = parseInt(sentinel.getAttribute('data-limit') || '9', 10);

            isLoading = true;
            var loadingEl = document.getElementById('grcInfiniteLoading');
            var endEl = document.getElementById('grcInfiniteEnd');
            if (loadingEl) loadingEl.style.display = 'inline-block';
            if (endEl) endEl.style.display = 'none';

            var currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('ajax', '1');
            currentUrl.searchParams.set('offset', nextOffset);
            currentUrl.searchParams.set('limit', limit);

            fetch(currentUrl.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(res) {
                if (!res.ok) throw new Error('Network error');
                return res.json();
            })
            .then(function(data) {
                if (data.status === 'success' && data.html) {
                    var temp = document.createElement('div');
                    temp.innerHTML = data.html;
                    while (temp.firstChild) {
                        grid.appendChild(temp.firstChild);
                    }

                    sentinel.setAttribute('data-next-offset', data.next_offset);
                    sentinel.setAttribute('data-has-more', data.has_more ? '1' : '0');

                    if (!data.has_more) {
                        if (endEl) {
                            endEl.innerHTML = '<i class="fa-solid fa-check-double text-success me-1"></i> You\'ve viewed all ' + data.total_products + ' products.';
                            endEl.style.display = 'block';
                        }
                    }

                    if (typeof window.filterGrcAisleCards === 'function') {
                        window.filterGrcAisleCards();
                    }
                }
            })
            .catch(function(err) {
                console.error('Infinite scroll error:', err);
            })
            .finally(function() {
                isLoading = false;
                if (loadingEl) loadingEl.style.display = 'none';
            });
        }

        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        loadNextBatch();
                    }
                });
            }, {
                root: null,
                rootMargin: '300px',
                threshold: 0.05
            });
            observer.observe(sentinel);
        } else {
            window.addEventListener('scroll', function() {
                var rect = sentinel.getBoundingClientRect();
                if (rect.top <= window.innerHeight + 300) {
                    loadNextBatch();
                }
            }, { passive: true });
        }
    })();
})();
</script>
