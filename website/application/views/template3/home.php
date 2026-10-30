<?php
$bazaro = isset($tpl_assets) ? rtrim($tpl_assets, '/') . '/' : base_url('assets/templates/bazaro/');
$curr   = isset($currency_symbol) ? $currency_symbol : '$';

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
?>

   <main>

      <!-- grc hero area start -->
      <div class="grc-hero-ptb grc-hero-style">
         <div class="swiper grc-hero-slider-active p-relative">
            <div class="swiper-wrapper">
               <?php
               $hero_bgs = ['#E1EEB7', '#E5F0E7', '#F4E3C5'];
               if (!empty($banners)):
                  foreach ($banners as $b_idx => $banner):
                     $bg_col = $hero_bgs[$b_idx % count($hero_bgs)];
                     $b_img = '';
                     if (!empty($banner['image'])) {
                        if (strpos($banner['image'], 'http') === 0) {
                           $b_img = $banner['image'];
                        } elseif (strpos($banner['image'], 'assets/') === 0) {
                           $b_img = base_url($banner['image']);
                        } elseif (file_exists(FCPATH . 'assets/images/' . ltrim($banner['image'], '/'))) {
                           $b_img = base_url('assets/images/' . ltrim($banner['image'], '/'));
                        } else {
                           $b_img = base_url('assets/images/banners/' . ltrim($banner['image'], '/'));
                        }
                     } else {
                        $b_img = $bazaro . 'img/grocery/grocery-hero-thumb-' . (($b_idx % 3) + 1) . '.png';
                     }
                     $b_link = !empty($banner['button_link']) ? (strpos($banner['button_link'], 'http') === 0 ? $banner['button_link'] : site_url($banner['button_link'])) : site_url('shop');
               ?>
               <div class="swiper-slide" data-bg-color="<?= $bg_col; ?>" style="background-color:<?= $bg_col; ?>;">
                  <div class="grc-hero-slider-item p-relative">
                     <div class="grc-hero-shapes">
                        <div class="shape-1"><img src="<?= $bazaro; ?>img/grocery/grocery-hero-shape-1.png" alt=""></div>
                        <div class="shape-2"><img src="<?= $bazaro; ?>img/grocery/grocery-hero-shape-2.png" alt=""></div>
                        <div class="shape-3"><img src="<?= $bazaro; ?>img/grocery/grocery-hero-shape-3.png" alt=""></div>
                     </div>
                     <div class="container container-1630">
                        <div class="row align-items-end">
                           <div class="col-lg-6">
                              <div class="grc-hero-content pt-120 pb-120">
                                 <?php if (!empty($banner['subtitle'])): ?>
                                 <span class="grc-hero-subtitle"><?= html_escape($banner['subtitle']); ?></span>
                                 <?php endif; ?>
                                 <h4 class="grc-hero-title"><?= html_escape($banner['title'] ?? 'Healthy Living Starts With Fresh Food'); ?></h4>
                                 <div class="grc-hero-btn">
                                    <a class="aq-btn-white btn-green-bg radius-30" href="<?= $b_link; ?>">
                                       <?= html_escape(!empty($banner['button_text']) ? $banner['button_text'] : 'Shop Now'); ?>
                                       <span>
                                          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                                             <path d="M0.75 5.75H10.75M10.75 5.75L5.75 0.75M10.75 5.75L5.75 10.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                          </svg>
                                       </span>
                                    </a>
                                 </div>
                              </div>
                           </div>
                           <div class="col-lg-6">
                              <div class="grc-hero-thumb-wrap pt-10 text-center">
                                 <img src="<?= $b_img; ?>" alt="<?= html_escape($banner['title'] ?? 'Fresh Grocery'); ?>" onerror="this.src='<?= $bazaro; ?>img/grocery/grocery-hero-thumb-2.png'">
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <?php
                  endforeach;
               else:
                  $default_grc_slides = [
                     ['bg' => '#E1EEB7', 'sub' => '100% Organic Harvest', 'title' => 'Fresh Organic Produce Delivered Daily', 'img' => $bazaro . 'img/grocery/grocery-hero-thumb-1.png'],
                     ['bg' => '#E5F0E7', 'sub' => 'All Natural', 'title' => 'Healthy Living Starts With Food', 'img' => $bazaro . 'img/grocery/grocery-hero-thumb-2.png'],
                     ['bg' => '#F4E3C5', 'sub' => 'Artisan Bakery & Dairy', 'title' => 'Clean Organic Everyday Essentials', 'img' => $bazaro . 'img/grocery/grocery-hero-thumb-3.png'],
                  ];
                  foreach ($default_grc_slides as $ds):
               ?>
               <div class="swiper-slide" data-bg-color="<?= $ds['bg']; ?>" style="background-color:<?= $ds['bg']; ?>;">
                  <div class="grc-hero-slider-item p-relative">
                     <div class="container container-1630">
                        <div class="row align-items-end">
                           <div class="col-lg-6">
                              <div class="grc-hero-content pt-120 pb-120">
                                 <span class="grc-hero-subtitle"><?= html_escape($ds['sub']); ?></span>
                                 <h4 class="grc-hero-title"><?= html_escape($ds['title']); ?></h4>
                                 <div class="grc-hero-btn">
                                    <a class="aq-btn-white btn-green-bg radius-30" href="<?= site_url('shop'); ?>">
                                       shop now
                                       <span>
                                          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                                             <path d="M0.75 5.75H10.75M10.75 5.75L5.75 0.75M10.75 5.75L5.75 10.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                          </svg>
                                       </span>
                                    </a>
                                 </div>
                              </div>
                           </div>
                           <div class="col-lg-6">
                              <div class="grc-hero-thumb-wrap pt-10">
                                 <img src="<?= $ds['img']; ?>" alt="">
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <?php
                  endforeach;
               endif;
               ?>
            </div>
            <div class="elt-slider-arrow grc-slider-arrow">
               <button class="aqf-slider-prev" type="button" aria-label="Previous slide">
                  <span>
                     <svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12" fill="none">
                        <path d="M5.75 10.75L0.75 5.75L5.75 0.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                     </svg>
                  </span>
               </button>
               <button class="aqf-slider-next" type="button" aria-label="Next slide">
                  <span>
                     <svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12" fill="none">
                        <path d="M0.75 10.75L5.75 5.75L0.75 0.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                     </svg>
                  </span>
               </button>
            </div>
         </div>
      </div>
      <!-- grc hero area end -->

      <!-- Hero Banner Swiper Slider script -->
      <script>
      (function() {
         function initHeroSwiper() {
            var heroEl = document.querySelector('.grc-hero-slider-active');
            if (heroEl && !heroEl.swiper && typeof Swiper !== 'undefined') {
               new Swiper(heroEl, {
                  slidesPerView: 1,
                  loop: true,
                  autoplay: {
                     delay: 4500,
                     disableOnInteraction: false,
                  },
                  speed: 1000,
                  spaceBetween: 0,
                  navigation: {
                     prevEl: heroEl.querySelector('.aqf-slider-prev'),
                     nextEl: heroEl.querySelector('.aqf-slider-next'),
                  },
               });
            }
         }
         if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initHeroSwiper);
         } else {
            initHeroSwiper();
         }
      })();
      </script>

      <!-- feature area start -->
      <div class="aqf-shop-feature-ptb grc-feature-style bdr-radius pt-40 pb-50">
         <div class="container container-1630">
            <div class="aqf-shop-feature-wrap d-flex flex-wrap justify-content-between align-items-center" data-bg-color="#FAFAFA" style="background-color:#FAFAFA;">
               <div class="aqf-shop-feature-item d-flex align-items-center mb-30">
                  <div class="aqf-shop-feature-icon">
                     <span><i class="fa-solid fa-truck-fast fs-3 text-success"></i></span>
                  </div>
                  <div class="aqf-shop-feature-content">
                     <h4>Free Same-Day Delivery</h4>
                     <p>Free delivery on grocery orders over <?= html_escape($curr); ?>50</p>
                  </div>
               </div>
               <div class="aqf-shop-feature-item d-flex align-items-center mb-30">
                  <div class="aqf-shop-feature-icon">
                     <span><i class="fa-solid fa-leaf fs-3 text-success"></i></span>
                  </div>
                  <div class="aqf-shop-feature-content">
                     <h4>100% Freshness Guarantee</h4>
                     <p>Farm-to-table organic quality assured</p>
                  </div>
               </div>
               <div class="aqf-shop-feature-item d-flex align-items-center mb-30">
                  <div class="aqf-shop-feature-icon">
                     <span><i class="fa-solid fa-credit-card fs-3 text-success"></i></span>
                  </div>
                  <div class="aqf-shop-feature-content">
                     <h4>Flexible Payment</h4>
                     <p>Pay with Cards, Wallets &amp; Cash on Delivery</p>
                  </div>
               </div>
               <div class="aqf-shop-feature-item d-flex align-items-center mb-30">
                  <div class="aqf-shop-feature-icon">
                     <span><i class="fa-solid fa-headset fs-3 text-success"></i></span>
                  </div>
                  <div class="aqf-shop-feature-content">
                     <h4>Support Online</h4>
                     <p>24 hours a day, 7 days a week</p>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- feature area end -->

      <!-- New Arrivals start -->
      <?php
      $all_prods = !empty($products) ? $products : [];
      ?>
      <style>
         @media (min-width: 1200px) {
            .grc-col-5 {
               flex: 0 0 auto;
               width: 20% !important;
            }
         }
      </style>
      <div class="grc-product-ptb pb-50">
         <div class="container container-1630">
            <div class="grc-product-top pb-20">
               <div class="row align-items-center">
                  <div class="col-sm-6">
                     <div class="grc-product-heading mb-20">
                        <h4 class="aq-section-title ff-satoshi-bold fs-30 mb-0">New Arrivals</h4>
                     </div>
                  </div>
                  <div class="col-sm-6">
                     <div class="grc-product-btn text-sm-end">
                        <a class="aq-btn-link aq-btn-underline" href="<?= site_url('shop'); ?>">
                           See all
                           <span>
                              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                                 <path d="M0.75 5.75H10.75M10.75 5.75L5.75 0.75M10.75 5.75L5.75 10.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                              </svg>
                           </span>
                        </a>
                     </div>
                  </div>
               </div>
            </div>
            <div class="grc-product-style">
               <div class="row">
                  <?php 
                  $display_new = !empty($new_arrival_products) ? $new_arrival_products : array_slice($all_prods, 0, 10);
                  if (!empty($display_new)):
                  foreach ($display_new as $n_idx => $p):
                     $p_img  = grc_product_img($p['primary_image'] ?? ($p['image'] ?? ''), $bazaro, $n_idx + 1);
                     $p_reg  = (float)($p['regular_price'] ?? $p['price'] ?? 0);
                     $p_sal  = (!empty($p['sale_price']) && (float)$p['sale_price'] > 0 && (float)$p['sale_price'] < $p_reg) ? (float)$p['sale_price'] : $p_reg;
                     $p_pct  = !empty($p['total_discount_percent']) ? (int)$p['total_discount_percent'] : (($p_sal < $p_reg && $p_reg > 0) ? round((($p_reg - $p_sal) / $p_reg) * 100) : 0);
                      $p_url  = site_url('product/' . $p['slug']);
                      $p_unit = !empty($p['unit_label']) ? $p['unit_label'] : '1 Pack';
                   ?>
                   <div class="col-lg-4 col-sm-6 grc-col-5">
                      <div class="aq-product-item aq-product-main mb-40">
                         <div class="aq-product-thumb p-relative mb-10">
                            <?php if (!empty($p['is_flash_sale']) || $p_pct > 0 || !empty($p['is_new'])): ?>
                            <div class="aq-product-badge">
                               <?php if (!empty($p['is_flash_sale'])): ?>
                               <span class="clr-sale" style="background:#DA3F3F; color:#fff; font-size:12px; font-weight:700; padding:4px 10px; text-transform:uppercase; letter-spacing:0.5px;">SALE</span>
                               <?php endif; ?>
                               <?php if ($p_pct > 0): ?>
                               <span class="clr-sale">-<?= $p_pct; ?>%</span>
                               <?php elseif (!empty($p['is_new'])): ?>
                               <span class="clr-sale" style="background:#003D29; color:#fff;">New</span>
                               <?php endif; ?>
                            </div>
                            <?php endif; ?>
                           <a href="<?= $p_url; ?>">
                              <img class="aq-product-img-hover" src="<?= $p_img; ?>" alt="<?= html_escape($p['name']); ?>" onerror="this.src='<?= $bazaro; ?>img/product/grocery-p-<?= (($n_idx % 12) + 1); ?>.jpg'">
                           </a>
                           <div class="aq-product-action-2 d-flex flex-column">
                              <?php $is_wish = in_array((int)$p['id'], $user_wishlist_ids ?? []); ?>
                              <button type="button" onclick="grcToggleWishlist(<?= (int)$p['id']; ?>, this)" class="aq-product-action-btn-2 aq-wishlist-btn aq-tooltip <?= $is_wish ? 'active in-wishlist' : ''; ?>" data-product-id="<?= (int)$p['id']; ?>" title="<?= $is_wish ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>">
                                 <i class="<?= $is_wish ? 'fa-solid fa-heart text-danger' : 'fa-regular fa-heart'; ?>" style="font-size: 16px; <?= $is_wish ? 'color: #ef4444 !important;' : ''; ?>"></i>
                                 <span class="aq-tooltip-item"><?= $is_wish ? 'Remove' : 'Wishlist'; ?></span>
                              </button>
                           </div>
                        </div>
                        <div class="aq-product-content">
                           <div class="d-flex align-items-center justify-content-between mb-1">
                              <span class="aqf-product-3-category"><?= html_escape($p['category_name'] ?? 'Organic Food'); ?></span>
                              <span class="grc-unit-pill"><?= html_escape($p_unit); ?></span>
                           </div>
                           <h4 class="aq-product-title"><a href="<?= $p_url; ?>"><?= html_escape($p['name']); ?></a></h4>
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
                           <button type="button" onclick="grcAddToCart(<?= (int)$p['id']; ?>, 1, <?= !empty($p['primary_variant_id']) ? (int)$p['primary_variant_id'] : 'null'; ?>)" class="product-btn-green w-100">Add to bag</button>
                        </div>
                     </div>
                  </div>
                  <?php endforeach; ?>
                  <?php else: ?>
                  <div class="col-12 text-center py-5">
                     <p class="text-muted mb-0">No products available at the moment.</p>
                  </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>
      <!-- New Arrivals end -->

      <?php if (!empty($flash_sale) && (!empty($flash_sale_products) || !empty($deal_products))): ?>
      <!-- Deal of the Week area start -->
      <div class="aqf-deals-area grc-product-style pb-80">
         <div class="container container-1630">
            <div class="aqf-deals-wrap grc-deals-style" data-bg-color="#F5F5F0" style="background-color:#F5F5F0;">
               <div class="row gx-50">
                  <div class="col-lg-3">
                     <div class="grc-banner-style h-100">
                        <div class="aqf-banner-2-box p-relative h-100">
                           <div class="aqf-banner-2-thumb h-100">
                              <img class="w-100 h-100" src="<?= $bazaro; ?>img/grocery/grocery-banner-4.jpg" alt="Special Savings" style="object-fit:cover;">
                           </div>
                           <div class="aqf-banner-2-content">
                              <span class="aq-section-subtitle aq-uppercase aq-text-white mb-15">Special</span>
                              <h4 class="aq-section-title ff-satoshi-bold aq-text-white fs-40 mb-20">Savings!</h4>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="col-lg-9">
                     <div class="grc-deals-wrapper mt-60 pe-lg-4">
                        <div class="aqf-deals-slider-top mb-20">
                           <div class="row align-items-center">
                              <div class="col-lg-4 col-md-6">
                                 <div class="grc-deals-heading mb-20">
                                    <h4 class="aq-section-title ff-satoshi-bold fs-30 mb-0"><?= html_escape(!empty($flash_sale['title']) ? $flash_sale['title'] : 'Deal of the Week'); ?></h4>
                                 </div>
                              </div>
                              <div class="col-lg-8 col-md-6">
                                 <div class="aq-product-3-top-right d-flex justify-content-md-end align-items-center flex-wrap">
                                    <div class="aqf-deals-countbox d-flex align-items-center justify-content-end">
                                       <div class="aq-countdown-text mr-20"><span>Ends in</span></div>
                                       <?php 
                                           $fs_end_time = !empty($flash_sale['end_time']) ? $flash_sale['end_time'] : date('Y-m-d H:i:s', strtotime('+2 days'));
                                        ?>
                                        <div id="grcCountdown" class="aq-countdown-style-2 d-inline-flex align-items-center" data-end-time="<?= html_escape($fs_end_time); ?>">
                                           <div class="count-box"><span id="days" class="grc-days">00</span><i>:</i></div>
                                           <div class="count-box"><span id="hours" class="grc-hours">00</span><i>:</i></div>
                                           <div class="count-box"><span id="minutes" class="grc-minutes">00</span><i>:</i></div>
                                           <div class="count-box"><span id="seconds" class="grc-seconds">00</span></div>
                                        </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="aqf-deals-slider-main">
                           <div class="aqf-deals-slider-wrap mb-10">
                              <div class="swiper grc-deals-slider-active">
                                 <div class="swiper-wrapper">
                                    <?php
                                    $fs_products = !empty($flash_sale_products) ? $flash_sale_products : (!empty($deal_products) ? $deal_products : []);
                                    foreach ($fs_products as $d_idx => $dp):
                                       $d_img  = grc_product_img($dp['primary_image'] ?? ($dp['image'] ?? ''), $bazaro, $d_idx + 5);
                                       $d_reg  = (float)($dp['regular_price'] ?? $dp['price'] ?? 0);
                                       $d_sal  = (!empty($dp['sale_price']) && (float)$dp['sale_price'] > 0 && (float)$dp['sale_price'] < $d_reg) ? (float)$dp['sale_price'] : $d_reg;
                                       $d_pct  = !empty($dp['total_discount_percent']) ? (int)$dp['total_discount_percent'] : (($d_sal < $d_reg && $d_reg > 0) ? round((($d_reg - $d_sal) / $d_reg) * 100) : 15);
                                        $d_url  = site_url('product/' . $dp['slug']);
                                        $d_unit = !empty($dp['unit_label']) ? $dp['unit_label'] : '500g';
                                     ?>
                                     <div class="swiper-slide">
                                        <div class="aq-product-item aq-product-main mb-40">
                                           <div class="aq-product-thumb p-relative mb-10">
                                              <div class="aq-product-badge">
                                                 <span class="clr-sale" style="background:#DA3F3F; color:#fff; font-size:12px; font-weight:700; padding:4px 10px; text-transform:uppercase; letter-spacing:0.5px;">SALE</span>
                                                 <?php if ($d_pct > 0): ?>
                                                 <span class="clr-sale">-<?= $d_pct; ?>%</span>
                                                 <?php endif; ?>
                                              </div>
                                             <a href="<?= $d_url; ?>">
                                                <img class="aq-product-img-hover" src="<?= $d_img; ?>" alt="<?= html_escape($dp['name']); ?>" onerror="this.src='<?= $bazaro; ?>img/product/grocery-p-<?= (($d_idx % 12) + 1); ?>.jpg'">
                                             </a>
                                             <div class="aq-product-action-2 d-flex flex-column">
                                                <?php $d_is_wish = in_array((int)$dp['id'], $user_wishlist_ids ?? []); ?>
                                                <button type="button" onclick="grcToggleWishlist(<?= (int)$dp['id']; ?>, this)" class="aq-product-action-btn-2 aq-wishlist-btn aq-tooltip <?= $d_is_wish ? 'active in-wishlist' : ''; ?>" data-product-id="<?= (int)$dp['id']; ?>" title="<?= $d_is_wish ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>">
                                                   <i class="<?= $d_is_wish ? 'fa-solid fa-heart text-danger' : 'fa-regular fa-heart'; ?>" style="font-size: 16px; <?= $d_is_wish ? 'color: #ef4444 !important;' : ''; ?>"></i>
                                                   <span class="aq-tooltip-item"><?= $d_is_wish ? 'Remove' : 'Wishlist'; ?></span>
                                                </button>
                                             </div>
                                          </div>
                                          <div class="aq-product-content">
                                             <div class="d-flex align-items-center justify-content-between mb-1">
                                                <span class="aqf-product-3-category"><?= html_escape($dp['category_name'] ?? 'Organic Pantry'); ?></span>
                                                <span class="grc-unit-pill"><?= html_escape($d_unit); ?></span>
                                             </div>
                                             <h4 class="aq-product-title"><a href="<?= $d_url; ?>"><?= html_escape($dp['name']); ?></a></h4>
                                             <?php
                                             $d_rev_cnt = (isset($dp['reviews']) && is_array($dp['reviews'])) ? count($dp['reviews']) : (int)($dp['reviews_count'] ?? 0);
                                             $d_rating  = $d_rev_cnt > 0 ? (float)($dp['rating'] ?? 0) : 0.0;
                                             $d_stars   = (int)round($d_rating);
                                             ?>
                                             <div class="aq-product-ratting text-warning small mb-1 d-flex align-items-center gap-1">
                                                <span>
                                                   <?php for ($si = 1; $si <= 5; $si++): ?>
                                                      <i class="fa-<?= ($si <= $d_stars) ? 'solid' : 'regular'; ?> fa-star"></i>
                                                   <?php endfor; ?>
                                                </span>
                                                <span class="text-muted" style="font-size:12px;">(<?= $d_rev_cnt; ?>)</span>
                                             </div>
                                             <div class="aq-product-price">
                                                <ins><span class="aq-product-new-price"><?= html_escape($curr) . number_format($d_sal, 2); ?></span></ins>
                                                <?php if ($d_sal < $d_reg): ?>
                                                   <del><span class="aq-product-old-price"><?= html_escape($curr) . number_format($d_reg, 2); ?></span></del>
                                                <?php endif; ?>
                                             </div>
                                          </div>
                                          <div class="grc-product-btn">
                                             <button type="button" onclick="grcAddToCart(<?= (int)$dp['id']; ?>, 1, <?= !empty($dp['primary_variant_id']) ? (int)$dp['primary_variant_id'] : 'null'; ?>)" class="product-btn-green w-100">Add to bag</button>
                                          </div>
                                       </div>
                                    </div>
                                    <?php endforeach; ?>
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
      <!-- Deal of the Week area end -->

      <!-- Dynamic countdown script for Deal of the Week -->
      <script>
      (function() {
         function initGrcCountdown() {
            var el = document.getElementById('grcCountdown');
            if (!el) return;

            var daysEl  = el.querySelector('#days') || el.querySelector('.grc-days');
            var hoursEl = el.querySelector('#hours') || el.querySelector('.grc-hours');
            var minsEl  = el.querySelector('#minutes') || el.querySelector('.grc-minutes');
            var secsEl  = el.querySelector('#seconds') || el.querySelector('.grc-seconds');

            var endTimeStr = el.getAttribute('data-end-time') || '';
            var targetDate = NaN;

            if (endTimeStr) {
               var parts = endTimeStr.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?$/);
               if (parts) {
                  targetDate = new Date(
                     parseInt(parts[1], 10),
                     parseInt(parts[2], 10) - 1,
                     parseInt(parts[3], 10),
                     parseInt(parts[4], 10),
                     parseInt(parts[5], 10),
                     parseInt(parts[6] || '0', 10)
                  ).getTime();
               } else {
                  targetDate = new Date(endTimeStr.replace(' ', 'T')).getTime();
               }
            }

            if (isNaN(targetDate) || targetDate <= 0) {
               targetDate = new Date().getTime() + (2 * 86400000);
            }

            function tick() {
               var now = new Date().getTime();
               var diff = targetDate - now;

               if (isNaN(diff) || diff <= 0) {
                  if (daysEl)  daysEl.innerText  = '00';
                  if (hoursEl) hoursEl.innerText = '00';
                  if (minsEl)  minsEl.innerText  = '00';
                  if (secsEl)  secsEl.innerText  = '00';
                  return;
               }

               var second = 1000, minute = second * 60, hour = minute * 60, day = hour * 24;
               var d = Math.floor(diff / day);
               var h = Math.floor((diff % day) / hour);
               var m = Math.floor((diff % hour) / minute);
               var s = Math.floor((diff % minute) / second);

               if (daysEl)  daysEl.innerText  = isNaN(d) ? '00' : String(d).padStart(2, '0');
               if (hoursEl) hoursEl.innerText = isNaN(h) ? '00' : String(h).padStart(2, '0');
               if (minsEl)  minsEl.innerText  = isNaN(m) ? '00' : String(m).padStart(2, '0');
               if (secsEl)  secsEl.innerText  = isNaN(s) ? '00' : String(s).padStart(2, '0');
            }

            tick();
            if (window._grcCountdownInterval) {
               clearInterval(window._grcCountdownInterval);
            }
            window._grcCountdownInterval = setInterval(tick, 1000);
         }

         if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initGrcCountdown);
         } else {
            initGrcCountdown();
         }
      })();
      </script>
      <?php endif; ?>

      <!-- Popular Products Carousel start -->
      <div class="grc-product-ptb pb-50">
         <div class="container container-1630">
            <div class="grc-product-top pb-20">
               <div class="row align-items-center">
                  <div class="col-sm-6">
                     <div class="grc-product-heading mb-20">
                        <h4 class="aq-section-title ff-satoshi-bold fs-30 mb-0">Popular Products</h4>
                     </div>
                  </div>
                  <div class="col-sm-6">
                     <div class="grc-product-btn text-sm-end">
                        <a class="aq-btn-link aq-btn-underline" href="<?= site_url('shop?filter=popular'); ?>">
                           See all
                           <span>
                              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                                 <path d="M0.75 5.75H10.75M10.75 5.75L5.75 0.75M10.75 5.75L5.75 10.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                              </svg>
                           </span>
                        </a>
                     </div>
                  </div>
               </div>
            </div>
            <div class="grc-product-style">
               <div class="row">
                  <div class="grc-product-slider-wrap">
                     <div class="swiper grc-slider-active">
                        <div class="swiper-wrapper">
                           <?php
                           $display_pop = !empty($popular_products) ? $popular_products : array_slice($all_prods, 0, 10);
                           if (!empty($display_pop)):
                           foreach ($display_pop as $c_idx => $cp):
                              $c_img  = grc_product_img($cp['primary_image'] ?? ($cp['image'] ?? ''), $bazaro, $c_idx + 1);
                              $c_reg  = (float)($cp['regular_price'] ?? $cp['price'] ?? 0);
                              $c_sal  = (!empty($cp['sale_price']) && (float)$cp['sale_price'] > 0 && (float)$cp['sale_price'] < $c_reg) ? (float)$cp['sale_price'] : $c_reg;
                              $c_pct  = !empty($cp['total_discount_percent']) ? (int)$cp['total_discount_percent'] : (($c_sal < $c_reg && $c_reg > 0) ? round((($c_reg - $c_sal) / $c_reg) * 100) : 0);
                               $c_url  = site_url('product/' . $cp['slug']);
                               $c_unit = !empty($cp['unit_label']) ? $cp['unit_label'] : '1 Pack';
                            ?>
                            <div class="swiper-slide">
                               <div class="aq-product-item aq-product-main mb-40">
                                  <div class="aq-product-thumb p-relative mb-10">
                                     <?php if (!empty($cp['is_flash_sale']) || $c_pct > 0): ?>
                                     <div class="aq-product-badge">
                                        <?php if (!empty($cp['is_flash_sale'])): ?>
                                        <span class="clr-sale" style="background:#DA3F3F; color:#fff; font-size:12px; font-weight:700; padding:4px 10px; text-transform:uppercase; letter-spacing:0.5px;">SALE</span>
                                        <?php endif; ?>
                                        <?php if ($c_pct > 0): ?>
                                        <span class="clr-sale">-<?= $c_pct; ?>%</span>
                                        <?php endif; ?>
                                     </div>
                                     <?php endif; ?>
                                    <a href="<?= $c_url; ?>">
                                       <img class="aq-product-img-hover" src="<?= $c_img; ?>" alt="<?= html_escape($cp['name']); ?>" onerror="this.src='<?= $bazaro; ?>img/product/grocery-p-<?= (($c_idx % 12) + 1); ?>.jpg'">
                                    </a>
                                    <div class="aq-product-action-2 d-flex flex-column">
                                       <?php $c_is_wish = in_array((int)$cp['id'], $user_wishlist_ids ?? []); ?>
                                       <button type="button" onclick="grcToggleWishlist(<?= (int)$cp['id']; ?>, this)" class="aq-product-action-btn-2 aq-wishlist-btn aq-tooltip <?= $c_is_wish ? 'active in-wishlist' : ''; ?>" data-product-id="<?= (int)$cp['id']; ?>" title="<?= $c_is_wish ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>">
                                          <i class="<?= $c_is_wish ? 'fa-solid fa-heart text-danger' : 'fa-regular fa-heart'; ?>" style="font-size: 16px; <?= $c_is_wish ? 'color: #ef4444 !important;' : ''; ?>"></i>
                                          <span class="aq-tooltip-item"><?= $c_is_wish ? 'Remove' : 'Wishlist'; ?></span>
                                       </button>
                                    </div>
                                 </div>
                                 <div class="aq-product-content">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                       <span class="aqf-product-3-category"><?= html_escape($cp['category_name'] ?? 'Organic Food'); ?></span>
                                       <span class="grc-unit-pill"><?= html_escape($c_unit); ?></span>
                                    </div>
                                    <h4 class="aq-product-title"><a href="<?= $c_url; ?>"><?= html_escape($cp['name']); ?></a></h4>
                                    <?php
                                    $c_rev_cnt = (isset($cp['reviews']) && is_array($cp['reviews'])) ? count($cp['reviews']) : (int)($cp['reviews_count'] ?? 0);
                                    $c_rating  = $c_rev_cnt > 0 ? (float)($cp['rating'] ?? 0) : 0.0;
                                    $c_stars   = (int)round($c_rating);
                                    ?>
                                    <div class="aq-product-ratting text-warning small mb-1 d-flex align-items-center gap-1">
                                       <span>
                                          <?php for ($si = 1; $si <= 5; $si++): ?>
                                             <i class="fa-<?= ($si <= $c_stars) ? 'solid' : 'regular'; ?> fa-star"></i>
                                          <?php endfor; ?>
                                       </span>
                                       <span class="text-muted" style="font-size:12px;">(<?= $c_rev_cnt; ?>)</span>
                                    </div>
                                    <div class="aq-product-price">
                                       <ins><span class="aq-product-new-price"><?= html_escape($curr) . number_format($c_sal, 2); ?></span></ins>
                                       <?php if ($c_sal < $c_reg): ?>
                                          <del><span class="aq-product-old-price"><?= html_escape($curr) . number_format($c_reg, 2); ?></span></del>
                                       <?php endif; ?>
                                    </div>
                                 </div>
                                 <div class="grc-product-btn">
                                    <button type="button" onclick="grcAddToCart(<?= (int)$cp['id']; ?>, 1, <?= !empty($cp['primary_variant_id']) ? (int)$cp['primary_variant_id'] : 'null'; ?>)" class="product-btn-green w-100">Add to bag</button>
                                 </div>
                              </div>
                           </div>
                           <?php endforeach; ?>
                           <?php else: ?>
                           <div class="col-12 text-center py-5 w-100">
                              <p class="text-muted mb-0">No products available at the moment.</p>
                           </div>
                           <?php endif; ?>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- Popular Products Carousel end -->

      <!-- text slide area start -->
      <div class="aqf-text-slide-area grc-text-slider-style fix" data-bg-color="#003D29" style="background-color:#003D29;">
         <div class="aqf-text-slide-wrap pt-20 pb-20">
            <?php for ($m = 0; $m < 4; $m++): ?>
            <div class="aqf-text-slide-item">
               <p>100% Certified Organic Harvest — Farm Direct</p>
               <span>
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="18" viewBox="0 0 16 18" fill="none">
                     <path d="M8.88889 0L0 10.8H8L7.11111 18L16 7.2H8L8.88889 0Z" fill="white"/>
                  </svg>
               </span>
            </div>
            <div class="aqf-text-slide-item">
               <p>Free Same-Day Grocery Delivery on Orders Over <?= html_escape($curr); ?>50.00</p>
               <span>
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="18" viewBox="0 0 16 18" fill="none">
                     <path d="M8.88889 0L0 10.8H8L7.11111 18L16 7.2H8L8.88889 0Z" fill="white"/>
                  </svg>
               </span>
            </div>
            <?php endfor; ?>
         </div>
      </div>
      <!-- text slide area end -->

      <!-- brand area start -->
      <div class="aq-brand-ptb py-5">
         <div class="grc-brand-wrapper">
            <div class="swiper grc-brand-active">
               <div class="swiper-wrapper">
                  <?php for ($b = 1; $b <= 6; $b++): ?>
                  <div class="swiper-slide">
                     <div class="aq-brand-item text-center">
                        <img src="<?= $bazaro; ?>img/grocery/grocery-brand-<?= $b; ?>.png" alt="Brand <?= $b; ?>">
                     </div>
                  </div>
                  <?php endfor; ?>
               </div>
            </div>
         </div>
      </div>
      <!-- brand area end -->

   </main>
