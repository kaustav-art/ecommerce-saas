<?php
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

        $reg   = (float)($prod['regular_price'] ?? $prod['price'] ?? 0);
        $sal   = (!empty($prod['sale_price']) && (float)$prod['sale_price'] > 0 && (float)$prod['sale_price'] < $reg) ? (float)$prod['sale_price'] : $reg;
        $crate = ($u_qty > 0) ? ($sal / $u_qty) : $sal;

        return [
            'pack_label' => $label,
            'unit_type'  => $u_type,
            'unit_qty'   => $u_qty,
            'unit_rate'  => round($crate, 2),
        ];
    }
}

$bazaro     = isset($bazaro) ? rtrim($bazaro, '/') . '/' : base_url('assets/templates/bazaro/');
$curr       = isset($curr) ? $curr : (isset($currency_symbol) ? $currency_symbol : '₹');
$grc_qs     = isset($grc_qs) ? $grc_qs : '';
$grc_brands = isset($grc_brands) ? $grc_brands : ['NatureFresh Organic', 'GreenValley Farms', 'HarvestGold Dairy', 'Artisan Bakehouse', 'EarthBounty Organics', 'PureOrchard Juices'];
$base_idx   = isset($offset) ? (int)$offset : 0;

if (!empty($shop_products)):
   foreach ($shop_products as $rel_idx => $p):
      $p_idx   = $base_idx + $rel_idx;
      $u_calc  = grc_shop_unit_calc($p, $p_idx);
      $p_id    = (int)($p['id'] ?? ($p_idx + 1));
      $p_name  = $p['name'] ?? ($p['title'] ?? 'Organic Grocery Item');
      $p_slug  = $p['slug'] ?? 'sweet-red-strawberries-box';
      $p_img   = grc_product_img($p['primary_image'] ?? ($p['main_image'] ?? ($p['image'] ?? '')), $bazaro, $p_idx + 1);
      $p_reg   = (float)($p['regular_price'] ?? $p['price'] ?? 0);
      $p_sal   = (!empty($p['sale_price']) && (float)$p['sale_price'] > 0 && (float)$p['sale_price'] < $p_reg) ? (float)$p['sale_price'] : $p_reg;
      $p_pct   = !empty($p['total_discount_percent']) ? (int)$p['total_discount_percent'] : (($p_sal < $p_reg && $p_reg > 0) ? round((($p_reg - $p_sal) / $p_reg) * 100) : 0);
      $p_url   = site_url('product/' . $p_slug . $grc_qs);
      $p_unit  = !empty($p['unit_label']) ? $p['unit_label'] : $u_calc['pack_label'];
      $p_cat   = !empty($p['category_name']) ? $p['category_name'] : (!empty($selected_category['name']) ? $selected_category['name'] : 'Organic Food');
      $p_brand = !empty($p['brand_name']) ? $p['brand_name'] : $grc_brands[$p_idx % count($grc_brands)];
?>
<div class="col grc-aisle-col"
     data-title="<?= html_escape(strtolower($p_name)); ?>"
     data-brand="<?= html_escape(strtolower($p_brand)); ?>"
     data-unit="<?= html_escape($u_calc['unit_type']); ?>"
     data-price="<?= $p_sal; ?>"
     data-unitrate="<?= $u_calc['unit_rate']; ?>"
     data-index="<?= $p_idx; ?>">
   <div class="aq-product-item aq-product-main mb-40">
      <div class="aq-product-thumb p-relative mb-10">
         <?php if (!empty($p['is_flash_sale']) || $p_pct > 0): ?>
         <div class="aq-product-badge">
            <?php if (!empty($p['is_flash_sale'])): ?>
            <span class="clr-sale" style="background:#DA3F3F; color:#fff; font-size:12px; font-weight:700; padding:4px 10px; text-transform:uppercase; letter-spacing:0.5px;">SALE</span>
            <?php endif; ?>
            <?php if ($p_pct > 0): ?>
            <span class="clr-sale">-<?= $p_pct; ?>%</span>
            <?php endif; ?>
         </div>
         <?php endif; ?>
         <a href="<?= $p_url; ?>">
            <img class="aq-product-img-hover" src="<?= $p_img; ?>" alt="<?= html_escape($p_name); ?>" onerror="this.src='<?= $bazaro; ?>img/product/grocery-p-<?= (($p_idx % 12) + 1); ?>.jpg'">
         </a>
         <div class="aq-product-action-2 d-flex flex-column">
            <?php $p_is_wish = in_array((int)$p_id, $user_wishlist_ids ?? []); ?>
            <button type="button" onclick="grcToggleWishlist(<?= $p_id; ?>, this)" class="aq-product-action-btn-2 aq-wishlist-btn aq-tooltip <?= $p_is_wish ? 'active in-wishlist' : ''; ?>" data-product-id="<?= $p_id; ?>" title="<?= $p_is_wish ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>">
               <i class="<?= $p_is_wish ? 'fa-solid fa-heart text-danger' : 'fa-regular fa-heart'; ?>" style="font-size: 16px; <?= $p_is_wish ? 'color: #ef4444 !important;' : ''; ?>"></i>
               <span class="aq-tooltip-item"><?= $p_is_wish ? 'Remove' : 'Wishlist'; ?></span>
            </button>
         </div>
      </div>
      <div class="aq-product-content">
         <div class="d-flex align-items-center justify-content-between mb-1">
            <span class="aqf-product-3-category"><?= html_escape($p_cat); ?></span>
            <span class="grc-unit-pill"><?= html_escape($p_unit); ?></span>
         </div>
         <h4 class="aq-product-title"><a href="<?= $p_url; ?>"><?= html_escape($p_name); ?></a></h4>
         <?php
         $p_rev_cnt = (isset($p['reviews']) && is_array($p['reviews'])) ? count($p['reviews']) : (int)($p['reviews_count'] ?? 0);
         $p_rating  = $p_rev_cnt > 0 ? (float)($p['rating'] ?? 0) : 0.0;
         $p_stars   = (int)round($p_rating);
         ?>
         <div class="aq-product-ratting text-warning small mb-1 d-flex align-items-center gap-1">
            <span>
               <?php for ($si = 1; $si <= 5; $si++): ?>
                  <i class="fa-<?= ($si <= $p_stars) ? 'solid' : 'regular'; ?> fa-star"></i>
               <?php endfor; ?>
            </span>
            <span class="text-muted" style="font-size:12px;">(<?= $p_rev_cnt; ?>)</span>
         </div>
         <div class="aq-product-price">
            <ins><span class="aq-product-new-price"><?= html_escape($curr) . number_format($p_sal, 2); ?></span></ins>
            <?php if ($p_sal < $p_reg): ?>
               <del><span class="aq-product-old-price"><?= html_escape($curr) . number_format($p_reg, 2); ?></span></del>
            <?php endif; ?>
         </div>
      </div>
      <div class="grc-product-btn">
         <button type="button" onclick="grcAddToCart(<?= $p_id; ?>, 1)" class="product-btn-green w-100">Add to bag</button>
      </div>
   </div>
</div>
<?php
   endforeach;
endif;
?>
