<?php
$curr = $currency_symbol ?? '$';
$arrivals_list = !empty($new_arrival_products) ? array_slice($new_arrival_products, 0, 10) : (!empty($products) ? array_slice($products, 0, 10) : []);
$popular_list  = !empty($popular_products) ? array_slice($popular_products, 0, 10) : (!empty($products) ? array_slice($products, 0, 10) : []);
$deals_list    = (!empty($flash_sale) && (!empty($flash_sale_products) || !empty($deal_products)))
    ? (!empty($flash_sale_products) ? $flash_sale_products : $deal_products)
    : [];

if (!function_exists('fsh_resolve_prod_img')) {
    function fsh_resolve_prod_img($img_str, $fallback = '') {
        if (empty($img_str)) {
            return $fallback ?: base_url('assets/images/products/womens/women-1.jpg');
        }
        if (strpos($img_str, 'http://') === 0 || strpos($img_str, 'https://') === 0) {
            return $img_str;
        }
        $clean = ltrim($img_str, '/');
        if ((strpos($clean, 'assets/') === 0 || strpos($clean, 'uploads/') === 0) && file_exists(FCPATH . $clean)) {
            return base_url($clean);
        }
        if (file_exists(FCPATH . 'assets/images/products/' . $clean)) {
            return base_url('assets/images/products/' . $clean);
        }
        if (file_exists(FCPATH . 'assets/images/' . $clean)) {
            return base_url('assets/images/' . $clean);
        }
        return base_url('assets/images/' . $clean);
    }
}

if (!function_exists('fsh_get_deal_swatches')) {
    function fsh_get_deal_swatches($dp, $default_img) {
        $swatches = [];
        $seen = [];
        if (!empty($dp['color_swatches']) && is_array($dp['color_swatches'])) {
            foreach ($dp['color_swatches'] as $sw) {
                $name = $sw['name'] ?? 'Color';
                if (!isset($seen[$name])) {
                    $seen[$name] = true;
                    $swatches[] = [
                        'name'       => $name,
                        'color_code' => !empty($sw['color_code']) ? $sw['color_code'] : '#73272D',
                        'image'      => !empty($sw['image']) ? fsh_resolve_prod_img($sw['image'], $default_img) : $default_img,
                    ];
                }
            }
        }
        if (empty($swatches) && !empty($dp['variants']) && is_array($dp['variants'])) {
            foreach ($dp['variants'] as $pv) {
                if (!empty($pv['values']) && is_array($pv['values'])) {
                    foreach ($pv['values'] as $val) {
                        $rSlug = $val['attribute_slug'] ?? '';
                        $isCol = ($rSlug === 'color' || ($val['attribute_type'] ?? '') === 'color' || strpos($rSlug, 'color-') === 0 || strcasecmp($val['attribute_name'] ?? '', 'color') === 0);
                        if ($isCol) {
                            $vName = $val['attribute_value'] ?? 'Color';
                            if (!isset($seen[$vName])) {
                                $seen[$vName] = true;
                                $swatches[] = [
                                    'name'       => $vName,
                                    'color_code' => !empty($val['color_code']) ? $val['color_code'] : '#2B2929',
                                    'image'      => !empty($pv['image']) ? fsh_resolve_prod_img($pv['image'], $default_img) : $default_img,
                                ];
                            }
                        }
                    }
                }
            }
        }
        return $swatches;
    }
}
?>
        <!-- Slider -->
        <section class="tf-slideshow slider-default slider-effect-fade">
            <div dir="ltr" class="swiper tf-sw-slideshow" data-effect="fade" data-preview="1" data-tablet="1" data-mobile="1" data-centered="false" data-space="0" data-space-mb="0" data-loop="true" data-auto-play="true">
                <div class="swiper-wrapper">
                    <?php if (!empty($banners)): ?>
                        <?php foreach ($banners as $b): ?>
                            <div class="swiper-slide">
                                <div class="wrap-slider">
                                    <img src="<?= base_url('assets/images/' . $b['image']); ?>" alt="<?= html_escape($b['title']); ?>" onerror="this.src='<?= base_url('assets/images/slider/slider-women1.jpg'); ?>'">
                                    <div class="box-content">
                                        <div class="content-slider">
                                            <div class="box-title-slider">
                                                <?php if (!empty($b['subtitle'])): ?>
                                                    <p class="fade-item fade-item-1 subheading text-btn-uppercase text-white"><?= html_escape($b['subtitle']); ?></p>
                                                <?php endif; ?>
                                                <div class="fade-item fade-item-2 heading text-white title-display"><?= nl2br(html_escape($b['title'])); ?></div>
                                            </div>
                                            <div class="fade-item fade-item-3 box-btn-slider">
                                                <a href="<?= site_url($b['button_link'] ?: 'shop'); ?>" class="tf-btn btn-fill btn-white">
                                                    <span class="text"><?= html_escape($b['button_text'] ?: 'Explore Collection'); ?></span>
                                                    <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="swiper-slide">
                            <div class="wrap-slider">
                                <img src="<?= base_url('assets/images/slider/slider-women1.jpg'); ?>" alt="Default Slideshow">
                                <div class="box-content">
                                    <div class="content-slider">
                                        <div class="box-title-slider">
                                            <p class="fade-item fade-item-1 subheading text-btn-uppercase text-white">SUMMER SALE</p>
                                            <div class="fade-item fade-item-2 heading text-white title-display">Flash Sale Madness</div>
                                        </div>
                                        <div class="fade-item fade-item-3 box-btn-slider">
                                            <a href="<?= site_url('shop'); ?>" class="tf-btn btn-fill btn-white">
                                                <span class="text">Explore Collection</span>
                                                <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="wrap-pagination">
                <div class="container">
                    <div class="sw-dots sw-pagination-slider type-circle white-circle justify-content-center"></div>
                </div>
            </div>
        </section>
        <!-- /Slider -->

        <!-- 1. New Arrivals Section -->
        <section class="flat-spacing-3 pt-5 pb-4">
            <div class="container">
                <div class="fsh-section-header d-flex flex-wrap align-items-end justify-content-between mb-4 pb-2 border-bottom">
                    <div>
                        <h3 class="fsh-section-title mb-0">New Arrivals</h3>
                    </div>
                    <a href="<?= site_url('shop'); ?>" class="fsh-see-all-link">
                        <span>See all Products</span>
                        <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="tf-grid-layout tf-col-2 lg-col-4 xl-col-5">
                    <?php if (!empty($arrivals_list)): ?>
                        <?php foreach ($arrivals_list as $p): ?>
                            <?php $this->load->view('home/_product_card', ['p' => $p, 'currency_symbol' => $curr]); ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <p class="text-muted mb-0">No products available at the moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <!-- /New Arrivals Section -->

        <!-- 2. Running Flash Sales Section (Bazaro Deals Area) -->
        <?php if (!empty($flash_sale) && !empty($deals_list)): ?>
        <?php
            $fs_end_time = !empty($flash_sale['end_time']) ? $flash_sale['end_time'] : date('Y-m-d H:i:s', strtotime('+2 days'));
            $fs_end_iso  = date('Y-m-d\TH:i:s', strtotime($fs_end_time));
            $fs_disc_pct = !empty($flash_sale['discount_percent']) ? (float)$flash_sale['discount_percent'] : 0;

            $fs_max_disc = (int)round($fs_disc_pct);
            if ($fs_max_disc <= 0) {
                foreach ($deals_list as $dp_chk) {
                    $chk_reg  = (float)($dp_chk['regular_price'] ?? $dp_chk['price'] ?? 0);
                    $chk_sal  = (!empty($dp_chk['sale_price']) && (float)$dp_chk['sale_price'] > 0 && (float)$dp_chk['sale_price'] < $chk_reg) ? (float)$dp_chk['sale_price'] : $chk_reg;
                    $chk_disc = !empty($dp_chk['total_discount_percent']) ? (int)$dp_chk['total_discount_percent'] : (($chk_reg > $chk_sal && $chk_reg > 0) ? round((($chk_reg - $chk_sal) / $chk_reg) * 100) : 0);
                    if ($chk_disc > $fs_max_disc) {
                        $fs_max_disc = $chk_disc;
                    }
                }
            }
            if ($fs_max_disc <= 0) {
                $fs_max_disc = 25;
            }

            $fs_default_desktop = base_url('assets/templates/bazaro/img/fashion-1/banner/banner-1.jpg');
            $fs_default_mobile  = base_url('assets/templates/bazaro/img/fashion-1/banner/banner-1-1.jpg');
            $fs_banner_desktop  = $fs_default_desktop;
            $fs_banner_mobile   = $fs_default_mobile;

            if (!empty($flash_sale['banner'])) {
                $clean_fs_b = ltrim($flash_sale['banner'], '/');
                if (strpos($clean_fs_b, 'http://') === 0 || strpos($clean_fs_b, 'https://') === 0) {
                    $fs_banner_desktop = $clean_fs_b;
                    $fs_banner_mobile  = $clean_fs_b;
                } elseif (file_exists(FCPATH . 'assets/images/collections/' . $clean_fs_b)) {
                    $fs_banner_desktop = base_url('assets/images/collections/' . $clean_fs_b);
                    $fs_banner_mobile  = $fs_banner_desktop;
                } elseif (file_exists(FCPATH . 'assets/images/' . $clean_fs_b)) {
                    $fs_banner_desktop = base_url('assets/images/' . $clean_fs_b);
                    $fs_banner_mobile  = $fs_banner_desktop;
                }
            }

            $fs_title_raw = trim((string)($flash_sale['title'] ?? ''));
            if (!empty($fs_title_raw)) {
                $words = preg_split('/\s+/', $fs_title_raw);
                if (count($words) >= 3) {
                    $half = (int)ceil(count($words) / 2);
                    $first_part  = implode(' ', array_slice($words, 0, $half));
                    $second_part = implode(' ', array_slice($words, $half));
                    $fs_banner_heading_html = html_escape($first_part) . ' <br> <span>' . html_escape($second_part) . '</span>';
                } else {
                    $fs_banner_heading_html = nl2br(html_escape($fs_title_raw));
                }
            } else {
                $fs_banner_heading_html = 'Coastal <br> Bag <span>Collection <br> In Situ</span>';
            }
        ?>
        <!-- deals area start -->
        <div class="aqf-deals-area pb-120 pt-4">
           <div class="container">
              <div class="aqf-deals-wrap" data-bg-color="#FAFAFA" style="background-color: rgb(250, 250, 250);">
                 <div class="row">
                    <div class="col-xl-5 col-lg-6">
                       <div class="aqf-deals-banner-wrap p-relative mr-30">
                          <div class="aqf-deals-banner-thumb">
                             <img class="w-100 d-none d-md-block" src="<?= $fs_banner_desktop; ?>" alt="<?= html_escape($fs_title_raw ?: 'Flash Sale'); ?>" onerror="this.src='<?= $fs_default_desktop; ?>'">
                             <img class="w-100 d-md-none" src="<?= $fs_banner_mobile; ?>" alt="<?= html_escape($fs_title_raw ?: 'Flash Sale'); ?>" onerror="this.src='<?= $fs_default_mobile; ?>'">
                          </div>
                          <div class="aqf-deals-banner-content">
                             <h4 class="aq-section-title fs-44 aq-text-white mb-20"><?= $fs_banner_heading_html; ?></h4>
                             <span>Up to <?= $fs_max_disc; ?>%</span>
                          </div>
                          <div class="aqf-deals-banner-btn">
                             <a class="aq-btn-black blur-bg w-100 text-center" href="<?= site_url('shop'); ?>">Shop Collection</a>
                          </div>
                       </div>
                    </div>
                    <div class="col-xl-7 col-lg-6">
                       <div class="aqf-deals-slider-main pt-60 pb-40">
                          <div class="aqf-deals-slider-top mb-15">
                             <div class="row">
                                <div class="col-xl-6 col-lg-8 col-md-6">
                                   <div class="aq-product-3-top-right mb-15">
                                      <div class="aqf-deals-countbox d-flex align-items-center">
                                         <div class="aq-date-countdown" data-date="<?= html_escape($fs_end_iso); ?>"></div>
                                         <div class="aq-countdown-text mr-20"><span>Ends in</span></div>
                                         <div id="countdown" class="aq-countdown-style-2 d-inline-flex align-items-center" data-end-time="<?= html_escape($fs_end_time); ?>">
                                            <div class="count-box"><span id="days">00</span><i>:</i></div>
                                            <div class="count-box"><span id="hours">00</span><i>:</i></div>
                                            <div class="count-box"><span id="minutes">00</span><i>:</i></div>
                                            <div class="count-box"><span id="seconds">00</span></div>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="col-xl-6 col-lg-4 col-md-6">
                                   <div class="aqf-deals-slider-arrow d-flex justify-content-start justify-content-md-end align-items-center mb-15 mr-60">
                                      <button type="button" class="aqf-deals-prev" aria-label="Previous slide">
                                         <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                                            <path d="M12.75 6.75H0.75M0.75 6.75L6.75 0.75M0.75 6.75L6.75 12.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                         </svg>
                                      </button>
                                      <span class="aqf-arrow-border"></span>
                                      <button type="button" class="aqf-deals-next" aria-label="Next slide">
                                         <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                                            <path d="M0.75 6.75H12.75M12.75 6.75L6.75 0.75M12.75 6.75L6.75 12.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                         </svg>
                                      </button>
                                   </div>
                                </div>
                             </div>
                          </div>
                          <div class="aqf-deals-slider-wrap fix">
                             <div class="swiper aqf-deals-slider-active">
                                <div class="swiper-wrapper">
                                   <?php foreach ($deals_list as $dp):
                                       $d_id    = (int)($dp['id'] ?? 0);
                                       $d_title = $dp['title'] ?? ($dp['name'] ?? 'Fashion Piece');
                                       $d_slug  = !empty($dp['slug']) ? site_url('product/' . $dp['slug']) : site_url('product/' . $d_id);
                                       $d_img   = fsh_resolve_prod_img($dp['main_image'] ?? ($dp['primary_image'] ?? ''), base_url('assets/images/products/womens/women-1.jpg'));
                                       $d_hover_raw = !empty($dp['gallery_images_decoded'][0])
                                           ? $dp['gallery_images_decoded'][0]
                                           : (!empty($dp['hover_image']) ? $dp['hover_image'] : ($dp['main_image'] ?? ''));
                                       $d_hover = fsh_resolve_prod_img($d_hover_raw, $d_img);
                                       $d_reg   = (float)($dp['regular_price'] ?? $dp['price'] ?? 0);
                                       $d_sal   = (!empty($dp['sale_price']) && (float)$dp['sale_price'] > 0 && (float)$dp['sale_price'] < $d_reg)
                                           ? (float)$dp['sale_price']
                                           : $d_reg;
                                       $d_disc  = !empty($dp['total_discount_percent'])
                                           ? (int)$dp['total_discount_percent']
                                           : (($d_reg > $d_sal && $d_reg > 0) ? round((($d_reg - $d_sal) / $d_reg) * 100) : (int)$fs_disc_pct);
                                       $d_rev_cnt = (isset($dp['reviews']) && is_array($dp['reviews'])) ? count($dp['reviews']) : (int)($dp['reviews_count'] ?? 0);
                                       $d_rating  = $d_rev_cnt > 0 ? (float)($dp['rating'] ?? 0) : 0.0;
                                       $d_stars   = (int)round($d_rating);
                                       $d_swatches = fsh_get_deal_swatches($dp, $d_img);
                                   ?>
                                   <div class="swiper-slide">
                                      <div class="aq-product-item aq-product-main text-center" data-lazy="true">
                                         <div class="aq-product-thumb aq-img-hover-wrap p-relative mb-10">
                                            <div class="aq-product-badge">
                                               <?php if ($d_disc > 0): ?>
                                               <span class="clr-sale">-<?= $d_disc; ?>%</span>
                                               <?php endif; ?>
                                               <span class="clr-sale" style="text-transform: uppercase;">SALE</span>
                                            </div>
                                            <div class="aq-product-action">
                                               <form action="<?= site_url('cart/add'); ?>" method="POST" class="d-inline">
                                                  <input type="hidden" name="product_id" value="<?= $d_id; ?>">
                                                  <input type="hidden" name="quantity" value="1">
                                                  <button type="submit" class="aq-product-action-btn aq-tooltip">
                                                     <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                                        <path d="M6.19751 0.75L3.30151 3.654M11.3015 0.75L14.1975 3.654M6.95776 10.3501V13.1901M10.6375 10.3501V13.1901M1.94997 7.14993L3.07797 14.0619C3.33397 15.6139 3.94997 16.7499 6.23796 16.7499H11.062C13.55 16.7499 13.918 15.6619 14.206 14.1579L15.55 7.14993M0.75 5.42996C0.75 3.94996 1.542 3.82996 2.526 3.82996H14.974C15.958 3.82996 16.75 3.94996 16.75 5.42996C16.75 7.14996 15.958 7.02996 14.974 7.02996H2.526C1.542 7.02996 0.75 7.14996 0.75 5.42996Z" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round"></path>
                                                     </svg>
                                                     <span class="aq-tooltip-item">Add to Cart</span>
                                                  </button>
                                               </form>
                                               <button type="button" class="aq-product-action-btn aq-tooltip" onclick="window.location.href='<?= $d_slug; ?>'">
                                                  <svg xmlns="http://www.w3.org/2000/svg" width="19" height="16" viewBox="0 0 19 16" fill="none">
                                                     <path d="M12.0557 7.75429C12.0557 9.42922 10.7022 10.7827 9.0273 10.7827C7.35238 10.7827 5.99891 9.42922 5.99891 7.75429C5.99891 6.07937 7.35238 4.72589 9.0273 4.72589C10.7022 4.72589 12.0557 6.07937 12.0557 7.75429Z" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                     <path d="M9.02734 14.75C12.0134 14.75 14.7965 12.9905 16.7337 9.94517C17.495 8.75242 17.495 6.74758 16.7337 5.55483C14.7965 2.50952 12.0134 0.75 9.02734 0.75C6.04124 0.75 3.25816 2.50952 1.321 5.55483C0.559668 6.74758 0.559668 8.75242 1.321 9.94517C3.25816 12.9905 6.04124 14.75 9.02734 14.75Z" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </svg>
                                                  <span class="aq-tooltip-item">Quick View</span>
                                               </button>
                                               <button type="button" class="aq-product-action-btn aq-wishlist-btn aq-tooltip" onclick="window.location.href='<?= site_url('wishlist/toggle/' . $d_id); ?>'">
                                                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="16" viewBox="0 0 18 16" fill="none">
                                                     <path d="M14.7197 1.52347C12.5744 0.244089 10.7019 0.759666 9.57712 1.58092C9.11591 1.91766 8.88531 2.08602 8.74963 2.08602C8.61396 2.08602 8.38336 1.91766 7.92215 1.58092C6.79733 0.759666 4.9249 0.244089 2.77958 1.52347C-0.0359114 3.20253 -0.67299 8.7418 5.82126 13.4151C7.05821 14.3052 7.67668 14.7502 8.74963 14.7502C9.82258 14.7502 10.4411 14.3052 11.678 13.4151C18.1723 8.7418 17.5352 3.20253 14.7197 1.52347Z" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round"></path>
                                                  </svg>
                                                  <span class="aq-tooltip-item">Add To Wishlist</span>
                                               </button>
                                               <button type="button" class="aq-product-action-btn aq-compare-btn aq-tooltip" onclick="window.location.href='<?= site_url('compare'); ?>'">
                                                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="14" viewBox="0 0 16 14" fill="none">
                                                     <path d="M11.6755 5.91828L14.2612 3.33412M14.2612 3.33412L11.6755 0.75M14.2612 3.33412L1.74999 3.33374M3.33562 8.07153L0.75 10.6557L3.33562 13.2398M13.7724 10.75H1.26122" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </svg>
                                                  <span class="aq-tooltip-item">Add To Compare</span>
                                               </button>
                                            </div>
                                            <a href="<?= $d_slug; ?>">
                                               <img class="lazyload aq-product-img" src="<?= $d_img; ?>" alt="<?= html_escape($d_title); ?>" onerror="this.src='<?= base_url('assets/images/products/womens/women-1.jpg'); ?>'">
                                               <img class="aq-img-hover lazyload" src="<?= $d_hover; ?>" alt="<?= html_escape($d_title); ?>" onerror="this.src='<?= base_url('assets/images/products/womens/women-2.jpg'); ?>'">
                                            </a>
                                         </div>
                                         <div class="aq-product-content">
                                            <div class="aq-product-ratting">
                                               <?php for ($si = 1; $si <= 5; $si++): ?>
                                               <span>
                                                  <svg width="12" height="11" viewBox="0 0 12 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                     <path d="M5.21608 0.307961C5.38688 -0.102685 5.9686 -0.102684 6.1394 0.307962L7.34463 3.20568C7.41664 3.3788 7.57944 3.49709 7.76634 3.51207L10.8947 3.76287C11.338 3.79841 11.5178 4.35166 11.18 4.641L8.79653 6.68268C8.65414 6.80466 8.59195 6.99605 8.63546 7.17843L9.36364 10.2311C9.46684 10.6638 8.99621 11.0057 8.61666 10.7739L5.93837 9.13797C5.77836 9.04024 5.57712 9.04024 5.41711 9.13797L2.73882 10.7739C2.35927 11.0057 1.88865 10.6638 1.99184 10.2311L2.72003 7.17843C2.76353 6.99605 2.70135 6.80466 2.55895 6.68268L0.175492 4.641C-0.162276 4.35166 0.0174878 3.79841 0.460815 3.76287L3.58915 3.51207C3.77604 3.49709 3.93885 3.3788 4.01085 3.20568L5.21608 0.307961Z" fill="<?= ($d_stars === 0 || $si <= $d_stars) ? 'currentcolor' : '#d1d5db'; ?>"></path>
                                                  </svg>
                                               </span>
                                               <?php endfor; ?>
                                            </div>
                                            <h4 class="aq-product-title mb-10"><a href="<?= $d_slug; ?>"><?= html_escape($d_title); ?></a></h4>
                                            <div class="aq-product-price">
                                               <ins><span class="aq-product-new-price"><?= html_escape($curr) . number_format($d_sal, 2); ?></span></ins>
                                               <?php if ($d_reg > $d_sal): ?>
                                               <del><span class="aq-product-old-price"><?= html_escape($curr) . number_format($d_reg, 2); ?></span></del>
                                               <?php endif; ?>
                                            </div>
                                            <?php if (!empty($d_swatches)): ?>
                                            <div class="aq-product-color">
                                               <ul>
                                                  <?php foreach ($d_swatches as $sw_idx => $sw): ?>
                                                  <li class="aq-product-color-item aq-tooltip-top aq-color-swatch <?= $sw_idx === 0 ? 'active' : ''; ?>">
                                                     <span class="aq-tooltip-item"><?= html_escape($sw['name']); ?></span>
                                                     <span class="aq-product-color-value" data-bg-color="<?= html_escape($sw['color_code']); ?>" style="background-color: <?= html_escape($sw['color_code']); ?>;"></span>
                                                     <img class="lazyload" data-src="<?= html_escape($sw['image']); ?>" src="<?= html_escape($sw['image']); ?>" alt="image-product">
                                                  </li>
                                                  <?php endforeach; ?>
                                               </ul>
                                            </div>
                                            <?php endif; ?>
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
        <!-- deals area end -->

        <!-- Dynamic Countdown & Swiper Initialization for Bazaro Deals Area -->
        <script>
        (function() {
            function initBazaroDealsSection() {
                // 1. Live Countdown
                var countdownEl = document.getElementById('countdown');
                if (countdownEl) {
                    var daysEl  = document.getElementById('days');
                    var hoursEl = document.getElementById('hours');
                    var minsEl  = document.getElementById('minutes');
                    var secsEl  = document.getElementById('seconds');

                    var endTimeStr = countdownEl.getAttribute('data-end-time') || '';
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
                            if (daysEl)  daysEl.textContent  = '00';
                            if (hoursEl) hoursEl.textContent = '00';
                            if (minsEl)  minsEl.textContent  = '00';
                            if (secsEl)  secsEl.textContent  = '00';
                            return;
                        }

                        var second = 1000, minute = second * 60, hour = minute * 60, day = hour * 24;
                        var d = Math.floor(diff / day);
                        var h = Math.floor((diff % day) / hour);
                        var m = Math.floor((diff % hour) / minute);
                        var s = Math.floor((diff % minute) / second);

                        if (daysEl)  daysEl.textContent  = String(d).padStart(2, '0');
                        if (hoursEl) hoursEl.textContent = String(h).padStart(2, '0');
                        if (minsEl)  minsEl.textContent  = String(m).padStart(2, '0');
                        if (secsEl)  secsEl.textContent  = String(s).padStart(2, '0');
                    }

                    tick();
                    if (window._aqfDealsTimer) {
                        clearInterval(window._aqfDealsTimer);
                    }
                    window._aqfDealsTimer = setInterval(tick, 1000);
                }

                // 2. Swiper Slider (.aqf-deals-slider-active)
                var sliderEl = document.querySelector('.aqf-deals-slider-active');
                if (sliderEl && typeof Swiper !== 'undefined' && !sliderEl.swiper) {
                    new Swiper('.aqf-deals-slider-active', {
                        slidesPerView: 3,
                        loop: true,
                        autoplay: true,
                        spaceBetween: 10,
                        speed: 1000,
                        navigation: {
                            prevEl: '.aqf-deals-prev',
                            nextEl: '.aqf-deals-next',
                        },
                        breakpoints: {
                            1400: { slidesPerView: 3 },
                            1200: { slidesPerView: 2 },
                            992:  { slidesPerView: 2 },
                            768:  { slidesPerView: 2 },
                            576:  { slidesPerView: 2 },
                            0:    { slidesPerView: 1 }
                        }
                    });
                }

                // 3. Color Swatch Hover/Click Preview
                document.querySelectorAll('.aqf-deals-area .aq-color-swatch').forEach(function(swatch) {
                    ['click', 'mouseover'].forEach(function(evtName) {
                        swatch.addEventListener(evtName, function() {
                            var imgEl = swatch.querySelector('img');
                            var card  = swatch.closest('.aq-product-main');
                            if (imgEl && card) {
                                var mainImg = card.querySelector('.aq-product-img');
                                if (mainImg && imgEl.getAttribute('src')) {
                                    mainImg.setAttribute('src', imgEl.getAttribute('src'));
                                }
                            }
                            if (swatch.parentElement) {
                                swatch.parentElement.querySelectorAll('.aq-color-swatch.active').forEach(function(sib) {
                                    sib.classList.remove('active');
                                });
                            }
                            swatch.classList.add('active');
                        });
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initBazaroDealsSection);
            } else {
                initBazaroDealsSection();
            }
            window.addEventListener('load', initBazaroDealsSection);
        })();
        </script>
        <?php endif; ?>
        <!-- /Running Flash Sales Section -->

        <!-- 3. Popular Products Section -->
        <section class="flat-spacing-3 pt-4 pb-5">
            <div class="container">
                <div class="fsh-section-header d-flex flex-wrap align-items-end justify-content-between mb-4 pb-2 border-bottom">
                    <div>
                        <h3 class="fsh-section-title mb-0">Popular Products</h3>
                    </div>
                    <a href="<?= site_url('shop?filter=popular'); ?>" class="fsh-see-all-link">
                        <span>See all Products</span>
                        <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="tf-grid-layout tf-col-2 lg-col-4 xl-col-5">
                    <?php if (!empty($popular_list)): ?>
                        <?php foreach ($popular_list as $p): ?>
                            <?php $this->load->view('home/_product_card', ['p' => $p, 'currency_symbol' => $curr]); ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <p class="text-muted mb-0">No products available at the moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <!-- /Popular Products Section -->

        <!-- Features / Service Iconbox -->
        <section class="flat-spacing border-top">
            <div class="container">
                <div dir="ltr" class="swiper tf-sw-iconbox" data-preview="4" data-tablet="3" data-mobile-sm="2" data-mobile="1" data-space-lg="30" data-space-md="30" data-space="15" data-pagination="1" data-pagination-sm="2" data-pagination-md="3" data-pagination-lg="4">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div class="tf-icon-box">
                                <div class="icon-box"><i class="fa-solid fa-rotate-left"></i></div>
                                <div class="content text-center">
                                    <h6>14-Day Returns</h6>
                                    <p class="text-secondary">Risk-free shopping with easy returns.</p>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="tf-icon-box">
                                <div class="icon-box"><i class="fa-solid fa-truck-fast"></i></div>
                                <div class="content text-center">
                                    <h6>Free Shipping</h6>
                                    <p class="text-secondary">Free standard shipping on all major orders.</p>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="tf-icon-box">
                                <div class="icon-box"><i class="fa-solid fa-headset"></i></div>
                                <div class="content text-center">
                                    <h6>24/7 Support</h6>
                                    <p class="text-secondary">Dedicated customer support team anytime.</p>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="tf-icon-box">
                                <div class="icon-box"><i class="fa-solid fa-shield-halved"></i></div>
                                <div class="content text-center">
                                    <h6>Secure Payments</h6>
                                    <p class="text-secondary">Encrypted transactions via Stripe, Razorpay & PayU.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sw-pagination-iconbox sw-dots type-circle justify-content-center"></div>
                </div>
            </div>
        </section>
        <!-- /Features -->

        <!-- Section, Badges & Bazaro Deals Area Styles -->
        <style>
        @font-face {
            font-family: "Satoshi-Light";
            font-style: normal;
            font-weight: normal;
            src: local("Satoshi-Light"), url("<?= base_url('assets/templates/bazaro/fonts/Satoshi-Light.woff'); ?>") format("woff");
        }
        @font-face {
            font-family: "Satoshi-Regular";
            src: url("<?= base_url('assets/templates/bazaro/fonts/Satoshi-Regular.woff2'); ?>") format("woff2"),
                 url("<?= base_url('assets/templates/bazaro/fonts/Satoshi-Regular.woff'); ?>") format("woff"),
                 url("<?= base_url('assets/templates/bazaro/fonts/Satoshi-Regular.ttf'); ?>") format("truetype");
            font-weight: 400;
            font-display: swap;
            font-style: normal;
        }
        @font-face {
            font-family: "Satoshi-Medium";
            src: url("<?= base_url('assets/templates/bazaro/fonts/Satoshi-Medium.woff2'); ?>") format("woff2"),
                 url("<?= base_url('assets/templates/bazaro/fonts/Satoshi-Medium.woff'); ?>") format("woff"),
                 url("<?= base_url('assets/templates/bazaro/fonts/Satoshi-Medium.ttf'); ?>") format("truetype");
            font-weight: 500;
            font-display: swap;
            font-style: normal;
        }
        @font-face {
            font-family: "Satoshi-Bold";
            src: url("<?= base_url('assets/templates/bazaro/fonts/Satoshi-Bold.woff2'); ?>") format("woff2"),
                 url("<?= base_url('assets/templates/bazaro/fonts/Satoshi-Bold.woff'); ?>") format("woff"),
                 url("<?= base_url('assets/templates/bazaro/fonts/Satoshi-Bold.ttf'); ?>") format("truetype");
            font-weight: 700;
            font-display: swap;
            font-style: normal;
        }

        :root {
            --aq-ff-satoshi-light: 'Satoshi-Light', sans-serif;
            --aq-ff-satoshi-regular: 'Satoshi-Regular', sans-serif;
            --aq--ff-satoshi-reg: 'Satoshi-Regular', sans-serif;
            --aq-ff-satoshi-medium: 'Satoshi-Medium', sans-serif;
            --aq-ff-satoshi-bold: 'Satoshi-Bold', sans-serif;
            --aq-common-white: #FFFFFF;
            --aq-common-black: #141414;
            --aq-common-black-light: #2A2C2D;
            --aq-common-red: #DE1518;
            --aq-common-coffee: #9C362D;
        }

        /* Section Headings */
        .fsh-section-title {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            letter-spacing: -0.4px;
        }
        .fsh-see-all-link {
            display: inline-flex;
            align-items: center;
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            text-decoration: none;
            padding-bottom: 2px;
            border-bottom: 1.5px solid #111827;
            transition: all 0.25s ease;
        }
        .fsh-see-all-link:hover {
            color: #181818;
            opacity: 0.75;
            transform: translateX(2px);
        }

        /* Product Card Badges (New Arrivals / Popular Products) */
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
        .animate-fade-in {
            animation: productFadeIn 0.35s ease-out;
        }
        @keyframes productFadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* =========================================================
           Bazaro Fashion Deals Area (.aqf-deals-area) Exact Styles
           ========================================================= */
        .aqf-deals-area.pb-120 {
            padding-bottom: 120px;
        }
        @media only screen and (min-width: 992px) and (max-width: 1199px),
               only screen and (min-width: 768px) and (max-width: 991px),
               (max-width: 767px) {
            .aqf-deals-area.pb-120 {
                padding-bottom: 70px;
            }
        }
        .aqf-deals-area .p-relative {
            position: relative;
        }
        .aqf-deals-area .fix {
            overflow: hidden;
        }
        .aqf-deals-area .pt-60 {
            padding-top: 60px;
        }
        .aqf-deals-area .pb-40 {
            padding-bottom: 40px;
        }
        .aqf-deals-area .mb-10 {
            margin-bottom: 10px;
        }
        .aqf-deals-area .mb-15 {
            margin-bottom: 15px;
        }
        .aqf-deals-area .mb-20 {
            margin-bottom: 20px;
        }
        .aqf-deals-area .mr-20 {
            margin-right: 20px;
        }
        .aqf-deals-area .mr-30 {
            margin-right: 30px;
        }
        .aqf-deals-area .mr-60 {
            margin-right: 60px;
        }
        .aqf-deals-area a,
        .aqf-deals-area button {
            color: inherit;
            outline: none;
            border: none;
            background: transparent;
            text-decoration: none;
            transition: all 0.3s ease-out;
        }
        .aqf-deals-area ul {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        /* Left Banner */
        .aqf-deals-banner-wrap {
            height: 100%;
        }
        @media only screen and (min-width: 992px) and (max-width: 1199px),
               only screen and (min-width: 768px) and (max-width: 991px),
               (max-width: 767px) {
            .aqf-deals-banner-wrap {
                margin-inline-end: 0 !important;
                margin-right: 0 !important;
            }
        }
        .aqf-deals-banner-thumb {
            height: 100%;
            position: relative;
        }
        .aqf-deals-banner-thumb::after {
            position: absolute;
            content: "";
            top: 0;
            inset-inline-start: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(27, 12, 4, 0.1);
        }
        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .aqf-deals-banner-thumb {
                height: 720px;
            }
        }
        .aqf-deals-banner-thumb img {
            height: 100%;
            object-fit: cover;
        }
        .aqf-deals-banner-content {
            position: absolute;
            top: 0;
            inset-inline-start: 0;
            padding: 45px 40px;
            z-index: 2;
        }
        @media (max-width: 767px) {
            .aqf-deals-banner-content {
                padding: 15px 20px;
            }
        }
        .aqf-deals-area .aq-section-title {
            font-size: 100px;
            line-height: 1.06;
            display: block;
            color: var(--aq-common-black);
            font-family: var(--aq-ff-satoshi-regular);
            font-weight: 500;
        }
        .aqf-deals-area .aq-section-title.fs-44 {
            font-size: 44px;
            letter-spacing: -2.2px;
        }
        @media (max-width: 767px) {
            .aqf-deals-area .aq-section-title.fs-44 {
                font-size: 35px;
            }
        }
        .aqf-deals-area .aq-section-title.fs-44 span {
            font-family: var(--aq-ff-satoshi-bold);
        }
        .aqf-deals-area .aq-text-white {
            color: var(--aq-common-white) !important;
        }
        .aqf-deals-banner-content > span {
            font-size: 20px;
            line-height: 1;
            text-transform: uppercase;
            color: var(--aq-common-white);
            font-family: var(--aq--ff-satoshi-reg);
            display: inline-block;
        }
        .aqf-deals-banner-btn {
            position: absolute;
            bottom: 0;
            inset-inline-start: 0;
            width: 100%;
            padding: 40px;
            z-index: 2;
        }
        @media (max-width: 767px) {
            .aqf-deals-banner-btn {
                padding: 15px;
            }
        }
        .aqf-deals-area .aq-btn-black {
            padding: 18px 30px;
            font-weight: 600;
            line-height: 1;
            font-size: 14px;
            transition: 0.3s;
            display: inline-block;
            text-transform: uppercase;
            color: var(--aq-common-white);
            border: 1px solid transparent;
            font-family: var(--aq-ff-satoshi-bold);
            background-color: var(--aq-common-black);
        }
        @media only screen and (min-width: 1200px) and (max-width: 1399px),
               only screen and (min-width: 992px) and (max-width: 1199px) {
            .aqf-deals-area .aq-btn-black {
                padding: 12px 30px;
            }
        }
        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .aqf-deals-area .aq-btn-black {
                padding: 14px 18px;
                font-size: 12px;
            }
        }
        @media (max-width: 767px) {
            .aqf-deals-area .aq-btn-black {
                padding: 12px 15px;
                font-size: 12px;
            }
        }
        .aqf-deals-area .aq-btn-black.blur-bg {
            border: 1px solid rgba(255, 255, 255, 0.5);
            background: rgba(20, 20, 20, 0.1);
            backdrop-filter: blur(7px);
        }
        .aqf-deals-area .aq-btn-black.blur-bg:hover {
            color: var(--aq-common-black);
            border-color: var(--aq-common-white);
            background-color: var(--aq-common-white);
        }

        /* Countdown & Slider Top */
        @media only screen and (min-width: 768px) and (max-width: 991px),
               (max-width: 767px) {
            .aq-product-3-top-right {
                flex-direction: column;
            }
            .aq-product-3-top-right .aqf-deals-countbox {
                margin-bottom: 20px;
            }
            .aq-countdown-text {
                display: none;
            }
        }
        .aq-countdown-text span {
            color: #414243;
            font-size: 16px;
            line-height: 14px;
            letter-spacing: 0.16px;
            font-family: var(--aq-ff-satoshi-medium);
        }
        .aq-countdown-style-2 .count-box span {
            height: 36px;
            width: 36px;
            font-size: 14px;
            line-height: 36px;
            border-radius: 50%;
            text-align: center;
            display: inline-block;
            letter-spacing: 0.14px;
            color: var(--aq-common-white);
            background-color: var(--aq-common-black);
            font-family: var(--aq-ff-satoshi-bold);
        }
        .aq-countdown-style-2 .count-box i {
            margin: 0 5px;
            font-size: 18px;
            font-style: normal;
            display: inline-block;
            color: var(--aq-common-black);
        }
        .aqf-arrow-border {
            height: 25px;
            width: 2px;
            margin: 0 25px;
            display: inline-block;
            transform: translateY(1px);
            background-color: rgba(0, 0, 0, 0.1);
        }
        @media only screen and (min-width: 992px) and (max-width: 1199px),
               only screen and (min-width: 768px) and (max-width: 991px) {
            .aqf-deals-slider-arrow {
                margin-inline-end: 15px !important;
                margin-right: 15px !important;
            }
        }
        .aqf-deals-slider-arrow button {
            color: var(--aq-common-black);
            padding: 4px;
        }
        .aqf-deals-slider-arrow button:hover {
            color: var(--aq-common-red);
        }

        /* Deals Slider Main & Active Container */
        .aqf-deals-slider-active {
            margin-inline-end: -244px;
        }
        @media only screen and (min-width: 1200px) and (max-width: 1399px),
               only screen and (min-width: 992px) and (max-width: 1199px),
               only screen and (min-width: 768px) and (max-width: 991px),
               (max-width: 767px) {
            .aqf-deals-slider-active {
                margin-inline-end: 0;
            }
        }
        @media only screen and (min-width: 1200px) and (max-width: 1399px) {
            .aqf-deals-slider-main {
                padding-top: 50px !important;
                padding-bottom: 30px !important;
            }
        }
        @media only screen and (min-width: 768px) and (max-width: 991px),
               (max-width: 767px) {
            .aqf-deals-slider-main {
                padding: 30px !important;
                padding-bottom: 0 !important;
            }
        }

        /* Deals Product Card (.aq-product-item) */
        .aqf-deals-area .aq-product-thumb {
            overflow: hidden;
            aspect-ratio: 3 / 3.8;
            background: #f5f5f5;
        }
        .aqf-deals-area .aq-product-thumb img {
            height: 100%;
            width: 100%;
            object-fit: cover;
            object-position: center;
        }
        .aqf-deals-area .aq-img-hover {
            position: absolute;
            inset: 0;
            opacity: 0;
            transform: scale(1);
            transition: opacity 0.3s ease, transform 1s cubic-bezier(0, 0, 0.44, 1.18);
        }
        .aqf-deals-area .aq-img-hover-wrap:hover .aq-img-hover {
            opacity: 1;
            transform: scale(1.05);
        }
        .aqf-deals-area .aq-product-badge {
            position: absolute;
            top: 0;
            inset-inline-start: 0;
            z-index: 2;
            margin: 16px;
            display: flex;
            gap: 5px;
            align-items: start;
            flex-direction: column;
        }
        .aqf-deals-area .aq-product-badge span {
            font-size: 15px;
            line-height: 1;
            border-radius: 40px;
            padding: 6px 13.5px;
            text-transform: capitalize;
            color: var(--aq-common-white);
            background: var(--aq-common-coffee);
            font-family: var(--aq-ff-satoshi-bold);
        }
        .aqf-deals-area .aq-product-badge span.clr-sale {
            background-color: #DA3F3F;
            color: var(--aq-common-white);
        }
        .aqf-deals-area .aq-product-action {
            position: absolute;
            top: 16px;
            inset-inline-end: 0;
            z-index: 2;
            opacity: 0;
            display: flex;
            visibility: hidden;
            flex-direction: column;
            transition: all 0.3s 0s ease-out;
        }
        .aqf-deals-area .aq-product-item:hover .aq-product-action {
            inset-inline-end: 16px;
            visibility: visible;
            opacity: 1;
        }
        .aqf-deals-area .aq-product-action-btn {
            width: 42px;
            height: 42px;
            display: grid;
            text-align: center;
            font-size: 18px;
            position: relative;
            border-radius: 50%;
            margin-bottom: 6px;
            place-content: center;
            transition: all 0.3s ease-out;
            color: var(--aq-common-black);
            background-color: var(--aq-common-white);
            box-shadow: 0 1px 2px 0 rgba(1, 15, 28, 0.2);
        }
        @media (max-width: 1399px) {
            .aqf-deals-area .aq-product-action-btn {
                width: 38px;
                height: 38px;
            }
        }
        .aqf-deals-area .aq-product-action-btn:hover {
            color: var(--aq-common-white);
            background-color: var(--aq-common-black);
        }

        /* Tooltips */
        .aqf-deals-area .aq-tooltip {
            position: relative;
        }
        .aqf-deals-area .aq-tooltip-item {
            position: absolute;
            top: 50%;
            opacity: 0;
            z-index: 3;
            inset-inline-end: 100%;
            font-weight: 500;
            font-size: 11px;
            line-height: 1;
            padding: 6px 7px;
            border-radius: 4px;
            width: max-content;
            visibility: hidden;
            display: inline-block;
            color: var(--aq-common-white);
            font-family: var(--aq-ff-satoshi-bold);
            background-color: var(--aq-common-black);
            transform: translateY(-50%) translateX(6px);
            transition: opacity 0.3s ease, visibility 0.3s ease, transform 0.3s cubic-bezier(0.71, 1.7, 0.77, 1.24);
        }
        .aqf-deals-area .aq-tooltip-item::before {
            position: absolute;
            content: "";
            inset-inline-end: -4px;
            top: 50%;
            width: 0;
            height: 0;
            border-top: 8px solid transparent;
            border-bottom: 8px solid transparent;
            transform: translateY(-50%);
            border-left: 8px solid var(--aq-common-black);
        }
        .aqf-deals-area .aq-tooltip:hover .aq-tooltip-item {
            visibility: visible;
            opacity: 1;
            transform: translateX(-6px) translateY(-50%);
        }
        .aqf-deals-area .aq-tooltip-top {
            position: relative;
        }
        .aqf-deals-area .aq-tooltip-top .aq-tooltip-item {
            top: auto;
            bottom: 100%;
            inset-inline-end: auto;
            inset-inline-start: 50%;
            transform: translateX(-50%) translateY(6px);
        }
        .aqf-deals-area .aq-tooltip-top .aq-tooltip-item::before {
            inset-inline-end: auto;
            top: 16px;
            inset-inline-start: 50%;
            border-left: 0;
            transform: translateX(-50%) rotate(-90deg);
            border-right: 8px solid var(--aq-common-black);
        }
        .aqf-deals-area .aq-tooltip-top:hover .aq-tooltip-item {
            visibility: visible;
            opacity: 1;
            transform: translateY(-6px) translateX(-50%);
        }

        /* Product Rating, Title, Price, Swatches */
        .aqf-deals-area .aq-product-ratting {
            margin-bottom: 4px;
            color: var(--aq-common-black);
        }
        .aqf-deals-area .aq-product-title {
            font-size: 16px;
            line-height: 1.4;
            margin-bottom: 8px;
            color: var(--aq-common-black-light);
            font-family: var(--aq-ff-satoshi-medium);
        }
        .aqf-deals-area .aq-product-title a:hover {
            color: var(--aq-common-red);
        }
        .aqf-deals-area .aq-product-price {
            margin-bottom: 12px;
        }
        .aqf-deals-area .aq-product-price ins {
            text-decoration: none;
        }
        .aqf-deals-area .aq-product-new-price {
            font-size: 16px;
            line-height: 1;
            margin-inline-end: 6px;
            display: inline-block;
            transform: translateY(1px);
            color: var(--aq-common-black);
            font-family: var(--aq-ff-satoshi-bold);
        }
        .aqf-deals-area .aq-product-old-price {
            font-size: 14px;
            line-height: 1;
            color: var(--aq-common-black-light);
            font-family: var(--aq-ff-satoshi-regular);
        }
        .aqf-deals-area .aq-product-color-item {
            position: relative;
            height: 21px;
            width: 21px;
            margin: 0 1px;
            cursor: pointer;
            border-radius: 50%;
            display: inline-block;
            transition: all 0.3s ease-out;
            border: 1px solid rgba(42, 44, 45, 0.1);
        }
        .aqf-deals-area .aq-product-color-item:hover,
        .aqf-deals-area .aq-product-color-item.active {
            border-color: var(--aq-common-black);
        }
        .aqf-deals-area .aq-product-color-item:hover .aq-product-color-value,
        .aqf-deals-area .aq-product-color-item.active .aq-product-color-value {
            transform: scale(0.7);
        }
        .aqf-deals-area .aq-product-color-item img {
            visibility: hidden;
            width: 18px;
            height: 18px;
            position: absolute;
        }
        .aqf-deals-area .aq-product-color-value {
            position: relative;
            height: 100%;
            width: 100%;
            display: inline-block;
            border-radius: 50%;
            transition: all 0.3s ease-out;
        }
        </style>
