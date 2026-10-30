<?php
$bazaro = isset($tpl_assets) ? rtrim($tpl_assets, '/') . '/' : base_url('assets/templates/bazaro/');
$curr   = isset($currency_symbol) ? $currency_symbol : '$';

if (!function_exists('elt_product_img')) {
    function elt_product_img($p, $fallback_idx = 1, $type = 'arrival') {
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
        if ($type === 'deals') {
            $idx = (($fallback_idx - 1) % 6) + 1;
            return $bazaro_base . 'img/electronics/deals-product/deals-product-' . $idx . '.jpg';
        }
        $idx = (($fallback_idx - 1) % 8) + 1;
        return $bazaro_base . 'img/electronics/arrival-product/product-' . $idx . '/product-1.jpg';
    }
}

if (!function_exists('elt_cat_img')) {
    function elt_cat_img($cat, $fallback_idx = 1) {
        $bazaro_base = base_url('assets/templates/bazaro/');
        $img = $cat['image'] ?? '';
        if (!empty($img)) {
            if (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0) {
                return $img;
            }
            $clean = ltrim($img, '/');
            if ((strpos($clean, 'assets/') === 0 || strpos($clean, 'uploads/') === 0) && file_exists(FCPATH . $clean)) {
                return base_url($clean);
            }
            if (file_exists(FCPATH . 'assets/images/categories/' . $clean)) {
                return base_url('assets/images/categories/' . $clean);
            }
            if (file_exists(FCPATH . 'assets/images/' . $clean)) {
                return base_url('assets/images/' . $clean);
            }
            if (file_exists(FCPATH . 'assets/templates/bazaro/img/' . $clean)) {
                return $bazaro_base . 'img/' . $clean;
            }
        }
        $idx = (($fallback_idx - 1) % 7) + 1;
        return $bazaro_base . 'img/electronics/category/cat-' . $idx . '.jpg';
    }
}

// Merge product collections cleanly
$all_products = [];
$seen_ids     = [];
foreach ([$products ?? [], $featured_products ?? [], $new_products ?? [], $bestseller_products ?? [], $sale_products ?? [], $deal_products ?? []] as $list) {
    if (is_array($list)) {
        foreach ($list as $item) {
            $pid = $item['id'] ?? 0;
            if ($pid && !isset($seen_ids[$pid])) {
                $seen_ids[$pid] = true;
                $all_products[] = $item;
            }
        }
    }
}

$deals_list     = (!empty($flash_sale) && (!empty($flash_sale_products) || !empty($deal_products))) ? (!empty($flash_sale_products) ? $flash_sale_products : $deal_products) : [];
$arrivals_list  = !empty($new_products) ? array_slice($new_products, 0, 8) : array_slice($all_products, 0, 8);
$spotlight_item = !empty($featured_products[0]) ? $featured_products[0] : (!empty($all_products[0]) ? $all_products[0] : (empty($is_user_project) && !empty($featured_product) ? $featured_product : null));
?>

   <main>

      <style>
         .elt-slider-content .aq-section-title {
            width: 650px;
            max-width: 100%;
         }
         .elt-slider-content .aq-section-subtitle,
         .elt-slider-content .elt-slider-price {
            display: block;
            width: 650px;
            max-width: 100%;
            line-height: 30px !important;
         }
         @media (max-width: 991.98px) {
            .elt-slider-content .aq-section-title,
            .elt-slider-content .aq-section-subtitle,
            .elt-slider-content .elt-slider-price {
               width: 100% !important;
               max-width: 100% !important;
            }
         }
         @media (max-width: 767.98px) {
            .elt-slider-content .aq-section-title {
               font-size: 30px !important;
               line-height: 1.25 !important;
            }
            .elt-slider-content .aq-section-subtitle,
            .elt-slider-content .elt-slider-price {
               font-size: 15px !important;
               line-height: 26px !important;
            }
         }
      </style>

      <!-- slider area start -->
      <div class="elt-slider-area p-relative mb-60 fix">
         <div class="elt-slider-arrow">
            <button class="aq-slider-prev">
               <span>
                  <svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12" fill="none">
                     <path d="M5.75 10.75L0.75 5.75L5.75 0.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
               </span>
            </button>
            <button class="aq-slider-next">
               <span>
                  <svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12" fill="none">
                     <path d="M0.75 10.75L5.75 5.75L0.75 0.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
               </span>
            </button>
         </div>
         <div class="swiper elt-slider-active">
            <div class="swiper-wrapper">
               <?php
               $default_slides = [
                   [
                       'subtitle' => 'New Flagship Release',
                       'title'    => "Get the <br> Best Collection <br> " . date('Y'),
                       'price'    => 'From ' . $curr . '299',
                       'desktop'  => $bazaro . 'img/electronics/slider/slide-1.jpg',
                       'mobile'   => $bazaro . 'img/electronics/slider/slide-1-1.jpg',
                       'link'     => site_url('shop'),
                   ],
                   [
                       'subtitle' => 'Just Arrived',
                       'title'    => "Explore the <br> Premium Tech <br> " . date('Y'),
                       'price'    => 'Starting at ' . $curr . '199',
                       'desktop'  => $bazaro . 'img/electronics/slider/slide-2.jpg',
                       'mobile'   => $bazaro . 'img/electronics/slider/slide-2-1.jpg',
                       'link'     => site_url('shop'),
                   ],
                   [
                       'subtitle' => 'Latest Drop',
                       'title'    => "Upgrade Your <br> Smart Setup <br> " . date('Y'),
                       'price'    => 'From ' . $curr . '149',
                       'desktop'  => $bazaro . 'img/electronics/slider/slide-3.jpg',
                       'mobile'   => $bazaro . 'img/electronics/slider/slide-3-1.jpg',
                       'link'     => site_url('shop'),
                   ],
               ];

               $slides_to_render = $default_slides;
               if (!empty($banners)) {
                   $custom_slides = [];
                   foreach ($banners as $idx => $b) {
                       $s_idx = ($idx % 3) + 1;
                       $b_img = $bazaro . 'img/electronics/slider/slide-' . $s_idx . '.jpg';
                       $b_mob = $bazaro . 'img/electronics/slider/slide-' . $s_idx . '-1.jpg';
                       if (!empty($b['image'])) {
                           if (strpos($b['image'], 'http') === 0) {
                               $b_img = $b['image'];
                               $b_mob = $b_img;
                           } else {
                               $clean_b = ltrim($b['image'], '/');
                               if ((strpos($clean_b, 'assets/') === 0 || strpos($clean_b, 'uploads/') === 0) && file_exists(FCPATH . $clean_b)) {
                                   $b_img = base_url($clean_b);
                                   $b_mob = $b_img;
                               } elseif (file_exists(FCPATH . 'assets/images/banners/' . $clean_b)) {
                                   $b_img = base_url('assets/images/banners/' . $clean_b);
                                   $b_mob = $b_img;
                               } elseif (file_exists(FCPATH . 'assets/images/' . $clean_b)) {
                                   $b_img = base_url('assets/images/' . $clean_b);
                                   $b_mob = $b_img;
                               }
                           }
                       }
                       $sub_txt = $b['subtitle'] ?? ($b['badge'] ?? 'Featured Release');
                       $lnk_url = $b['link_url'] ?? ($b['button_link'] ?? ($b['button_url'] ?? 'shop'));
                       $custom_slides[] = [
                           'subtitle' => !empty($sub_txt) ? html_escape($sub_txt) : 'Featured Release',
                           'title'    => !empty($b['title']) ? html_escape($b['title']) : $default_slides[$idx % 3]['title'],
                           'price'    => !empty($b['description']) ? html_escape($b['description']) : $default_slides[$idx % 3]['price'],
                           'desktop'  => $b_img,
                           'mobile'   => $b_mob,
                           'link'     => (strpos($lnk_url, 'http') === 0) ? $lnk_url : site_url($lnk_url ?: 'shop'),
                       ];
                   }
                   if (!empty($custom_slides)) {
                       $slides_to_render = $custom_slides;
                   }
               }
               foreach ($slides_to_render as $slide):
               ?>
               <div class="swiper-slide">
                  <div class="elt-slider-item p-relative d-flex align-items-center">
                     <div class="elt-slider-thumb">
                        <img class="d-none d-md-block" src="<?= $slide['desktop']; ?>" alt="Slide">
                        <img class="d-md-none" src="<?= $slide['mobile']; ?>" alt="Slide">
                     </div>
                     <div class="elt-slider-content z-index-1">
                        <span class="aq-section-subtitle ff-satoshi-reg mb-15"><?= $slide['subtitle']; ?></span>
                        <h3 class="aq-section-title ff-onest-bold fs-56 mb-30"><?= $slide['title']; ?></h3>
                        <span class="elt-slider-price mb-40"><?= $slide['price']; ?></span>
                        <a class="aq-btn-black radius-30" href="<?= $slide['link']; ?>">Shop Now</a>
                     </div>
                  </div>
               </div>
               <?php endforeach; ?>
            </div>
         </div>
      </div>
      <!-- slider area end -->

      <!-- feature area start -->
      <div class="aqf-shop-feature-ptb aqf-shop-feature-style bdr-radius pb-50">
         <div class="container">
            <div class="aqf-shop-feature-wrap d-flex flex-wrap justify-content-xl-between align-items-center" data-bg-color="#F9F9F9" style="background-color:#F9F9F9;">
               <div class="aqf-shop-feature-item d-flex align-items-center mb-30">
                  <div class="aqf-shop-feature-icon">
                     <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="20" viewBox="0 0 24 20" fill="none">
                          <path d="M7.60001 13.6001V0.600098H22.5999V13.6001H7.60001ZM7.60001 13.6001L0.599976 13.6001V8.60011L3.59996 5.6001H7.59995L7.60001 13.6001ZM15.6 16.1001C15.6 17.4808 16.7193 18.6001 18.1 18.6001C19.4807 18.6001 20.5999 17.4808 20.5999 16.1001C20.5999 14.7194 19.4807 13.6001 18.1 13.6001C16.7193 13.6001 15.6 14.7194 15.6 16.1001ZM2.59997 16.1001C2.59997 17.4808 3.71925 18.6001 5.09996 18.6001C6.48066 18.6001 7.59995 17.4808 7.59995 16.1001C7.59995 14.7194 6.48066 13.6001 5.09996 13.6001C3.71925 13.6001 2.59997 14.7194 2.59997 16.1001Z" stroke="currentcolor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                     </span>
                  </div>
                  <div class="aqf-shop-feature-content">
                     <h4>Free Shipping</h4>
                     <p>Free Shipping for orders over <?= $curr; ?>130</p>
                  </div>
               </div>
               <div class="aqf-shop-feature-item d-flex align-items-center mb-30">
                  <div class="aqf-shop-feature-icon">
                     <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="26" viewBox="0 0 15 26" fill="none">
                           <path d="M7.24487 0.700195V24.7002M12.7 5.06348H4.51813C3.50549 5.06348 2.53432 5.46575 1.81827 6.1818C1.10222 6.89784 0.699951 7.86901 0.699951 8.88166C0.699951 9.8943 1.10222 10.8655 1.81827 11.5815C2.53432 12.2976 3.50549 12.6998 4.51813 12.6998H9.97268C10.9853 12.6998 11.9565 13.1021 12.6725 13.8182C13.3886 14.5342 13.7909 15.5054 13.7909 16.518C13.7909 17.5307 13.3886 18.5018 12.6725 19.2179C11.9565 19.9339 10.9853 20.3362 9.97268 20.3362H0.699951" stroke="currentcolor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                     </span>
                  </div>
                  <div class="aqf-shop-feature-content">
                     <h4>Free Returns</h4>
                     <p>Within 30 days for an exchange.</p>
                  </div>
               </div>
               <div class="aqf-shop-feature-item d-flex align-items-center mb-30">
                  <div class="aqf-shop-feature-icon">
                     <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none">
                           <path d="M2.52966 14.4778L14.4797 2.52783M9.70154 16.8772L10.9015 15.6772M12.3929 14.1874L14.7829 11.7974M0.599976 20.5972H20.6M2.20125 8.83763L8.84125 2.19763C10.9612 0.0776333 12.0212 0.0676333 14.1212 2.16763L19.0312 7.07763C21.1312 9.17763 21.1212 10.2376 19.0012 12.3576L12.3612 18.9976C10.2412 21.1176 9.18124 21.1276 7.08124 19.0276L2.17125 14.1176C0.0712457 12.0176 0.0712454 10.9676 2.20125 8.83763Z" stroke="currentcolor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                     </span>
                  </div>
                  <div class="aqf-shop-feature-content">
                     <h4>Flexible Payment</h4>
                     <p>Pay with Multiple Credit Cards</p>
                  </div>
               </div>
               <div class="aqf-shop-feature-item d-flex align-items-center mb-30">
                  <div class="aqf-shop-feature-icon">
                     <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                           <path d="M0.599976 15.6V9.60003C0.599976 7.2131 1.54817 4.92393 3.23597 3.23612C4.92378 1.5483 7.21293 0.600098 9.59984 0.600098C11.9868 0.600098 14.2759 1.5483 15.9637 3.23612C17.6515 4.92393 18.5997 7.2131 18.5997 9.60003V15.6M18.5997 16.6001C18.5997 17.1305 18.389 17.6392 18.0139 18.0143C17.6389 18.3893 17.1302 18.6 16.5997 18.6H15.5997C15.0693 18.6 14.5606 18.3893 14.1856 18.0143C13.8105 17.6392 13.5998 17.1305 13.5998 16.6001V13.6001C13.5998 13.0697 13.8105 12.5609 14.1856 12.1859C14.5606 11.8108 15.0693 11.6001 15.5997 11.6001H18.5997V16.6001ZM0.599976 16.6001C0.599976 17.1305 0.810686 17.6392 1.18575 18.0143C1.56082 18.3893 2.06952 18.6 2.59995 18.6H3.59993C4.13035 18.6 4.63905 18.3893 5.01412 18.0143C5.38919 17.6392 5.5999 17.1305 5.5999 16.6001V13.6001C5.5999 13.0697 5.38919 12.5609 5.01412 12.1859C4.63905 11.8108 4.13035 11.6001 3.59993 11.6001H0.599976V16.6001Z" stroke="currentcolor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                     </span>
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

      <!-- categories area start -->
      <div class="aqf-categories-area elt-categories-style pt-30 pb-30">
         <div class="container">
            <div class="aqf-categories-wrap mb-60 p-relative">
               <div class="aqf-categories-arrow">
                  <button class="aqf-categories-prev">
                     <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12" fill="none">
                           <path d="M5.75 10.75L0.75 5.75L5.75 0.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                     </span>
                  </button>
                  <button class="aqf-categories-next">
                     <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12" fill="none">
                           <path d="M0.75 10.75L5.75 5.75L0.75 0.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                     </span>
                  </button>
               </div>
               <div class="swiper aqf-categories-3-active">
                  <div class="swiper-wrapper">
                     <?php
                     $cats_list = !empty($categories) ? $categories : ($nav_categories ?? []);
                     if (!empty($cats_list)):
                         $ci = 1;
                         foreach ($cats_list as $cat):
                             $c_img = elt_cat_img($cat, $ci++);
                     ?>
                     <div class="swiper-slide">
                        <div class="aqf-categories-item text-center">
                           <a href="<?= site_url('shop/' . $cat['slug']); ?>">
                              <div class="aqf-categories-img">
                                 <img src="<?= $c_img; ?>" alt="<?= html_escape($cat['name']); ?>">
                              </div>
                              <span><?= html_escape($cat['name']); ?></span>
                           </a>
                        </div>
                     </div>
                     <?php
                         endforeach;
                     else:
                         $demo_cats = ['Phones', 'Earphones', 'Cables', 'Smart Watches', 'Laptop', 'TV & Home', 'Accessories'];
                         foreach ($demo_cats as $di => $dname):
                     ?>
                     <div class="swiper-slide">
                        <div class="aqf-categories-item text-center">
                           <a href="<?= site_url('shop'); ?>">
                              <div class="aqf-categories-img">
                                 <img src="<?= $bazaro; ?>img/electronics/category/cat-<?= ($di % 7) + 1; ?>.jpg" alt="<?= $dname; ?>">
                              </div>
                              <span><?= $dname; ?></span>
                           </a>
                        </div>
                     </div>
                     <?php endforeach; endif; ?>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- categories area end -->

      <?php if (!empty($flash_sale) && !empty($deals_list)): ?>
      <!-- deals area start -->
      <div class="aq-product-3-area pt-100 pb-120">
         <div class="container">
            <div class="aq-product-3-top mb-35">
               <div class="row">
                  <div class="col-xl-4 col-lg-6">
                     <div class="aq-product-3-title-box text-center text-lg-start">
                        <h4 class="aq-section-title ff-onest-bold fs-36"><?= html_escape(!empty($flash_sale['title']) ? $flash_sale['title'] : "Today's best Deals"); ?></h4>
                     </div>
                  </div>
                  <div class="col-xl-8 col-lg-6">
                     <div class="aq-product-3-top-right d-flex justify-content-center justify-content-lg-end align-items-center">
                        <div class="aqf-deals-countbox d-flex align-items-center justify-content-center justify-content-lg-end">
                           <div class="aq-countdown-text mr-20"><span>Ends in</span></div>
                           <?php
                               $fs_end_time = !empty($flash_sale['end_time']) ? $flash_sale['end_time'] : date('Y-m-d H:i:s', strtotime('+2 days'));
                           ?>
                           <div id="eltCountdown" class="aq-countdown-style-2 d-inline-flex align-items-center" data-end-time="<?= html_escape($fs_end_time); ?>">
                              <div class="count-box"><span id="days" class="elt-days">00</span><i>:</i></div>
                              <div class="count-box"><span id="hours" class="elt-hours">00</span><i>:</i></div>
                              <div class="count-box"><span id="minutes" class="elt-minutes">00</span><i>:</i></div>
                              <div class="count-box"><span id="seconds" class="elt-seconds">00</span></div>
                           </div>
                        </div>
                        <div class="aq-product-3-top-btn">
                           <a class="aq-btn-link aq-btn-underline" href="<?= site_url('shop'); ?>">
                              See all Products
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
            </div>
            <div class="aq-product-3-wrap">
               <div class="row gx-5 row-cols-xxl-3 row-cols-xl-2 row-cols-lg-2 row-cols-md-1 row-cols-sm-2 row-cols-1">
                  <?php
                  $di = 1;
                  foreach ($deals_list as $dp):
                      $d_id    = (int)$dp['id'];
                      $d_name  = $dp['name'] ?? 'Electronics Product';
                      $d_slug  = !empty($dp['slug']) ? site_url('product/' . $dp['slug']) : site_url('product/' . $d_id);
                      $d_img   = elt_product_img($dp, $di++, 'deals');
                      $d_cat   = !empty($dp['category_name']) ? $dp['category_name'] : (!empty($dp['brand']) ? $dp['brand'] : 'Electronics');
                      $d_price = isset($dp['display_price']) ? (float)$dp['display_price'] : (float)(!empty($dp['sale_price']) && $dp['sale_price'] < $dp['price'] ? $dp['sale_price'] : $dp['price']);
                      $d_old   = isset($dp['original_price']) ? (float)$dp['original_price'] : (float)($dp['mrp'] ?? $dp['price'] ?? 0);
                      $d_disc  = !empty($dp['total_discount_percent']) ? (int)$dp['total_discount_percent'] : (($d_old > $d_price && $d_old > 0) ? round((($d_old - $d_price) / $d_old) * 100) : 15);
                      $d_rev_cnt = (isset($dp['reviews']) && is_array($dp['reviews'])) ? count($dp['reviews']) : (int)($dp['reviews_count'] ?? 0);
                      $d_rating  = $d_rev_cnt > 0 ? (float)($dp['rating'] ?? 0) : 0.0;
                      $d_stars   = (int)round($d_rating);
                  ?>
                  <div class="col">
                     <div class="aq-product-3-item d-flex align-items-center mb-5">
                        <div class="aq-product-3-thumb p-relative">
                           <a href="<?= $d_slug; ?>">
                              <img src="<?= $d_img; ?>" alt="<?= html_escape($d_name); ?>">
                           </a>
                           <div class="aq-product-badge">
                              <span class="clr-sale">-<?= $d_disc; ?>%</span>
                           </div>
                        </div>
                        <div class="aq-product-content aq-product-3-content">
                           <span class="aq-product-category"><?= html_escape($d_cat); ?></span>
                           <h4 class="aq-product-title ff-onest fs-18 mb-1">
                              <a href="<?= $d_slug; ?>"><?= html_escape($d_name); ?></a>
                           </h4>
                           <div class="aq-product-ratting text-warning small mb-1 d-flex align-items-center gap-1">
                              <span>
                                 <?php for ($si = 1; $si <= 5; $si++): ?>
                                    <i class="fa-<?= ($si <= $d_stars) ? 'solid' : 'regular'; ?> fa-star"></i>
                                 <?php endfor; ?>
                              </span>
                              <span class="text-muted" style="font-size:12px;">(<?= $d_rev_cnt; ?>)</span>
                           </div>
                           <div class="aq-product-price">
                              <ins><span class="aq-product-new-price"><?= $curr . number_format($d_price, 2); ?></span></ins>
                              <?php if ($d_old > $d_price): ?>
                              <del><span class="aq-product-old-price"><?= $curr . number_format($d_old, 2); ?></span></del>
                              <?php endif; ?>
                           </div>
                        </div>
                     </div>
                  </div>
                  <?php endforeach; ?>
               </div>
            </div>
         </div>
      </div>
      <!-- deals area end -->

      <!-- Dynamic countdown script for Today's best Deals -->
      <script>
      (function() {
         function initEltCountdown() {
            var el = document.getElementById('eltCountdown');
            if (!el) return;

            var daysEl  = el.querySelector('#days') || el.querySelector('.elt-days');
            var hoursEl = el.querySelector('#hours') || el.querySelector('.elt-hours');
            var minsEl  = el.querySelector('#minutes') || el.querySelector('.elt-minutes');
            var secsEl  = el.querySelector('#seconds') || el.querySelector('.elt-seconds');

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
            if (window._eltCountdownInterval) {
               clearInterval(window._eltCountdownInterval);
            }
            window._eltCountdownInterval = setInterval(tick, 1000);
         }

         if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initEltCountdown);
         } else {
            initEltCountdown();
         }
      })();
      </script>
      <?php endif; ?>

      <!-- arrivals area start -->
      <div class="elt-arrival-area elt-arrival-style pb-120">
         <div class="container">
            <?php if (!empty($arrivals_list)): ?>
            <div class="elt-arrival-top mb-35">
               <div class="row align-items-center">
                  <div class="col-md-6">
                     <div class="elt-arrival-title-box text-center text-md-start">
                        <h4 class="aq-section-title ff-onest-bold fs-36">New Arrivals</h4>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="elt-arrival-top-btn text-center text-md-end">
                        <a class="aq-btn-link aq-btn-underline" href="<?= site_url('shop'); ?>">
                           See all Products
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
            <?php endif; ?>
            <div class="row gx-15 row-cols-xl-4 row-cols-lg-3 row-cols-md-2 row-cols-sm-2 row-cols-1">
               <?php if (empty($arrivals_list)): ?>
               <div class="col-12 w-100 py-5 text-center text-muted">
                  <p class="mb-0">No products available at the moment.</p>
               </div>
               <?php endif; ?>
               <?php
               $ai = 1;
               foreach ($arrivals_list as $ap):
                   $a_id    = (int)$ap['id'];
                   $a_name  = $ap['name'] ?? 'Electronics Item';
                   $a_slug  = !empty($ap['slug']) ? site_url('product/' . $ap['slug']) : site_url('product/' . $a_id);
                   $a_img   = elt_product_img($ap, $ai++, 'arrival');
                   $a_cat   = !empty($ap['category_name']) ? $ap['category_name'] : (!empty($ap['brand']) ? $ap['brand'] : 'Electronics');
                   $a_price = isset($ap['display_price']) ? (float)$ap['display_price'] : (float)(!empty($ap['sale_price']) && $ap['sale_price'] < $ap['price'] ? $ap['sale_price'] : $ap['price']);
                   $a_old   = isset($ap['original_price']) ? (float)$ap['original_price'] : (float)($ap['mrp'] ?? $ap['price'] ?? 0);
                   $a_disc  = ($a_old > $a_price && $a_old > 0) ? round((($a_old - $a_price) / $a_old) * 100) : 0;
                   $a_rev_cnt = (isset($ap['reviews']) && is_array($ap['reviews'])) ? count($ap['reviews']) : (int)($ap['reviews_count'] ?? 0);
                   $a_rating  = $a_rev_cnt > 0 ? (float)($ap['rating'] ?? 0) : 0.0;
                   $a_stars   = (int)round($a_rating);
                   $qv_json = htmlspecialchars(json_encode([
                       'id'       => $a_id,
                       'title'    => $a_name,
                       'price'    => $curr . number_format($a_price, 2),
                       'image'    => $a_img,
                       'category' => $a_cat,
                       'desc'     => mb_substr(strip_tags($ap['short_description'] ?? $ap['description'] ?? ''), 0, 160),
                       'sku'      => $ap['sku'] ?? ('SKU-' . $a_id),
                       'url'      => $a_slug,
                   ]), ENT_QUOTES, 'UTF-8');
               ?>
               <div class="col">
                  <div class="aq-product-item aq-product-main d-flex flex-column justify-content-center justify-content-between mb-15" data-lazy="true">
                     <div class="aq-product-thumb aq-img-hover-wrap p-relative mb-10">
                        <?php if ($a_disc > 0): ?>
                        <div class="aq-product-badge">
                           <span class="clr-sale">-<?= $a_disc; ?>%</span>
                        </div>
                        <?php endif; ?>
                        <div class="aq-product-action pos-bottom-center">
                           <button type="button" class="aq-product-action-btn aq-tooltip-top" onclick="bazaroAddToCart(<?= $a_id; ?>, 1, true)">
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
                           <button type="button" class="aq-product-action-btn aq-wishlist-btn aq-tooltip-top" onclick="bazaroToggleWishlist(<?= $a_id; ?>, this)">
                              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="16" viewBox="0 0 18 16" fill="none">
                                 <path d="M14.7197 1.52347C12.5744 0.244089 10.7019 0.759666 9.57712 1.58092C9.11591 1.91766 8.88531 2.08602 8.74963 2.08602C8.61396 2.08602 8.38336 1.91766 7.92215 1.58092C6.79733 0.759666 4.9249 0.244089 2.77958 1.52347C-0.0359114 3.20253 -0.67299 8.7418 5.82126 13.4151C7.05821 14.3052 7.67668 14.7502 8.74963 14.7502C9.82258 14.7502 10.4411 14.3052 11.678 13.4151C18.1723 8.7418 17.5352 3.20253 14.7197 1.52347Z" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round"/>
                              </svg>
                              <span class="aq-tooltip-item">Add To Wishlist</span>
                           </button>
                           <a href="<?= site_url('compare'); ?>" class="aq-product-action-btn aq-compare-btn aq-tooltip-top">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="14" viewBox="0 0 16 14" fill="none">
                                 <path d="M11.6755 5.91828L14.2612 3.33412M14.2612 3.33412L11.6755 0.75M14.2612 3.33412L1.74999 3.33374M3.33562 8.07153L0.75 10.6557L3.33562 13.2398M13.7724 10.75H1.26122" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                              </svg>
                              <span class="aq-tooltip-item">Compare</span>
                           </a>
                        </div>
                        <a href="<?= $a_slug; ?>">
                           <img class="lazyload aq-product-img" src="<?= $a_img; ?>" alt="<?= html_escape($a_name); ?>">
                        </a>
                     </div>
                     <div class="aq-product-content text-center">
                        <div class="aq-product-color">
                           <ul>
                              <li class="aq-product-color-item aq-tooltip-top aq-color-swatch active">
                                 <span class="aq-tooltip-item">Space Gray</span>
                                 <span class="aq-product-color-value" data-bg-color="#4E4C4B" style="background-color:#4E4C4B;"></span>
                                 <img class="lazyload" data-src="<?= $a_img; ?>" src="<?= $a_img; ?>" alt="image-product">
                              </li>
                              <li class="aq-product-color-item aq-tooltip-top aq-color-swatch">
                                 <span class="aq-tooltip-item">Silver</span>
                                 <span class="aq-product-color-value" data-bg-color="#F1F0EE" style="background-color:#F1F0EE;"></span>
                                 <img class="lazyload" data-src="<?= $a_img; ?>" src="<?= $a_img; ?>" alt="image-product">
                              </li>
                           </ul>
                        </div>
                        <h4 class="aq-product-title mb-1"><a href="<?= $a_slug; ?>"><?= html_escape($a_name); ?></a></h4>
                        <div class="aq-product-ratting text-warning small mb-1 d-flex align-items-center justify-content-center gap-1">
                           <span>
                              <?php for ($si = 1; $si <= 5; $si++): ?>
                                 <i class="fa-<?= ($si <= $a_stars) ? 'solid' : 'regular'; ?> fa-star"></i>
                              <?php endfor; ?>
                           </span>
                           <span class="text-muted" style="font-size:12px;">(<?= $a_rev_cnt; ?>)</span>
                        </div>
                        <div class="aq-product-price">
                           <ins><span class="aq-product-new-price"><?= $curr . number_format($a_price, 2); ?></span></ins>
                           <?php if ($a_old > $a_price): ?>
                           <del><span class="aq-product-old-price"><?= $curr . number_format($a_old, 2); ?></span></del>
                           <?php endif; ?>
                        </div>
                     </div>
                  </div>
               </div>
               <?php endforeach; ?>
            </div>
         </div>
      </div>
      <!-- arrivals area end -->

      <!-- product buy area start -->
      <?php if (!empty($spotlight_item) || empty($is_user_project)):
      $sp_id      = !empty($spotlight_item['id']) ? (int)$spotlight_item['id'] : 1;
      $sp_name    = !empty($spotlight_item['name']) ? $spotlight_item['name'] : 'Apple Watch Ultra 2 Titanium';
      $sp_cat     = !empty($spotlight_item['category_name']) ? $spotlight_item['category_name'] : 'Smart Wearables';
      $sp_price   = isset($spotlight_item['display_price']) ? (float)$spotlight_item['display_price'] : (float)($spotlight_item['price'] ?? 799.00);
      $sp_rev_cnt = (isset($spotlight_item['reviews']) && is_array($spotlight_item['reviews'])) ? count($spotlight_item['reviews']) : (int)($spotlight_item['reviews_count'] ?? 0);
      $sp_rating  = $sp_rev_cnt > 0 ? (float)($spotlight_item['rating'] ?? 0) : 0.0;
      $sp_stars   = (int)round($sp_rating);
      ?>
      <div class="elt-product-watch-area mb-120">
         <div class="container">
            <div class="elt-product-watch-wrap fix" data-bg-color="#EEEBE5" style="background-color:#EEEBE5;">
               <div class="row justify-content-center">
                  <div class="col-xxl-7 col-lg-6">
                     <div class="elt-product-watch-thumb text-end">
                        <img src="<?= $bazaro; ?>img/watch/watch-1.png" alt="<?= html_escape($sp_name); ?>">
                     </div>
                  </div>
                  <div class="col-xxl-5 col-lg-6">
                     <div class="aq-product-details-wrap pt-80 pb-80 pr-105">

                        <!-- product info  -->
                        <div class="aq-product-details-category"><span><?= html_escape($sp_cat); ?></span></div>
                        <h3 class="aq-product-details-title mb-10"><?= html_escape($sp_name); ?></h3>

                        <!-- inventory details  -->
                        <div class="tp-product-details-inventory">
                           <div class="aq-product-details-rating-wrapper d-flex align-items-center">
                              <div class="aq-product-details-rating-box d-flex align-items-center mb-10">
                                 <div class="aq-product-details-rating text-warning">
                                    <?php for ($r = 1; $r <= 5; $r++): ?>
                                       <i class="fa-<?= ($r <= $sp_stars) ? 'solid' : 'regular'; ?> fa-star"></i>
                                    <?php endfor; ?>
                                 </div>
                                 <div class="aq-product-details-reviews ms-1">
                                    <span>(<?= $sp_rev_cnt; ?>)</span>
                                 </div>
                              </div>
                           </div>
                        </div>

                        <!-- price info  -->
                        <div class="aq-product-details-price-wrap mb-30">
                           <ins><span class="aq-product-details-price new-price"><?= $curr . number_format($sp_price, 2); ?></span></ins>
                        </div>

                        <!-- product-variation -->
                        <div class="aq-product-details-variation mb-30">
                           <h4 class="aq-product-details-title-sm mb-15">
                              <label>Finish:</label> Titanium
                           </h4>
                           <div class="aq-product-details-variation-wrap d-flex align-items-center">
                              <div class="aq-product-details-variation-item active">
                                 <img src="<?= $bazaro; ?>img/watch/watch-1.png" alt="">
                              </div>
                              <div class="aq-product-details-variation-item">
                                 <img src="<?= $bazaro; ?>img/watch/watch-2.png" alt="">
                              </div>
                              <div class="aq-product-details-variation-item">
                                 <img src="<?= $bazaro; ?>img/watch/watch-3.png" alt="">
                              </div>
                           </div>
                        </div>

                        <!-- product-action -->
                        <div class="aq-product-details-action-wrapper">
                           <div class="aq-product-details-action-item-wrapper radius-style d-sm-flex align-items-center">
                              <div class="aq-product-details-quantity">
                                 <div class="aq-product-quantity mb-10 mr-10">
                                    <span class="aq-cart-minus">
                                       <svg width="11" height="2" viewBox="0 0 11 2" fill="none" xmlns="http://www.w3.org/2000/svg">
                                          <path d="M1 1H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                       </svg>
                                    </span>
                                    <input class="aq-cart-input" id="eltSpotlightQty" type="text" value="1">
                                    <span class="aq-cart-plus">
                                       <svg width="11" height="12" viewBox="0 0 11 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                          <path d="M1 6H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                          <path d="M5.5 10.5V1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                       </svg>
                                    </span>
                                 </div>
                              </div>
                              <div class="aq-product-details-add-to-cart product-btn-style-2 d-flex align-items-center mb-10 w-100">
                                 <button type="button" onclick="bazaroAddToCart(<?= $sp_id; ?>, parseInt(document.getElementById('eltSpotlightQty').value||1), true)" class="aq-product-details-add-to-cart-btn aq-btn-black radius-30 w-100">Add To Cart</button>
                                 <button type="button" onclick="bazaroToggleWishlist(<?= $sp_id; ?>, this)" class="aq-product-action-btn action-btn-2 aq-wishlist-btn aq-tooltip-top">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="21" height="18" viewBox="0 0 21 18" fill="none">
                                       <path d="M6.55662 4.24829C5.48234 4.55333 4.71904 5.41063 4.62364 6.41931M10.5691 16.619C12.758 15.4216 14.7943 14.0123 16.6416 12.4163C17.9403 11.2669 18.929 9.86571 19.5319 8.32001C20.617 5.32154 19.3496 1.88887 15.8028 0.872994C13.9387 0.339571 11.9028 1.65287 10.3321 2.70068C8.76068 1.65415 6.72554 0.340954 4.86136 0.872994C1.31453 1.88887 0.0380309 5.32154 1.12305 8.32001C1.726 9.86571 2.7147 11.2669 4.0134 12.4163C5.86066 14.0123 7.89697 15.4216 10.0859 16.619L10.3229 16.7501L10.5691 16.619Z" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span class="aq-tooltip-item">Wishlist</span>
                                 </button>
                                 <a href="<?= site_url('compare'); ?>" class="aq-product-action-btn action-btn-2 aq-compare-btn aq-tooltip-top">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="18" viewBox="0 0 22 18" fill="none">
                                    <path d="M0.75 1.63646V6.97119M0.75 6.97119H6.08467M0.75 6.97119L4.87548 3.09518C5.83105 2.13912 7.01324 1.44071 8.31173 1.06511C9.61022 0.689518 10.9827 0.648983 12.3011 0.947289C13.6195 1.2456 14.8408 1.87302 15.8512 2.77102C16.8615 3.66902 17.6279 4.80833 18.0788 6.08263M20.3107 15.8629V10.5281M20.3107 10.5281H14.976M20.3107 10.5281L16.185 14.4048C15.2294 15.3609 14.0472 16.0593 12.7487 16.4349C11.4502 16.8105 10.0778 16.851 8.75937 16.5527C7.44098 16.2544 6.21964 15.627 5.20931 14.729C4.19898 13.831 3.4326 12.6917 2.98167 11.4174" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span class="aq-tooltip-item">Compare</span>
                                 </a>
                              </div>
                           </div>
                           <button type="button" onclick="bazaroBuyNow(<?= $sp_id; ?>, parseInt(document.getElementById('eltSpotlightQty').value||1))" class="aq-btn-black btn-red-bg btn-square radius-30 w-100">Buy Now</button>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <?php endif; ?>
      <!-- product buy area end -->

      <!-- brand area start -->
      <div class="aq-brand-area mb-120">
         <div class="container">
            <div class="aq-brand-wrap">
               <div class="swiper aq-brand-active">
                  <div class="swiper-wrapper">
                     <div class="swiper-slide">
                        <div class="aq-brand-item">
                           <img src="<?= $bazaro; ?>img/brand/brand-1.png" alt="Brand">
                        </div>
                     </div>
                     <div class="swiper-slide">
                        <div class="aq-brand-item">
                           <img src="<?= $bazaro; ?>img/brand/brand-2.png" alt="Brand">
                        </div>
                     </div>
                     <div class="swiper-slide">
                        <div class="aq-brand-item">
                           <img src="<?= $bazaro; ?>img/brand/brand-3.png" alt="Brand">
                        </div>
                     </div>
                     <div class="swiper-slide">
                        <div class="aq-brand-item">
                           <img src="<?= $bazaro; ?>img/brand/brand-4.png" alt="Brand">
                        </div>
                     </div>
                     <div class="swiper-slide">
                        <div class="aq-brand-item">
                           <img src="<?= $bazaro; ?>img/brand/brand-5.png" alt="Brand">
                        </div>
                     </div>
                     <div class="swiper-slide">
                        <div class="aq-brand-item">
                           <img src="<?= $bazaro; ?>img/brand/brand-2.png" alt="Brand">
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- brand area end -->

   </main>
