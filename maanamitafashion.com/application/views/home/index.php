<?php
$curr = $currency_symbol ?? '$';
$arrivals_list = !empty($new_arrival_products) ? array_slice($new_arrival_products, 0, 10) : (!empty($products) ? array_slice($products, 0, 10) : []);
$popular_list  = !empty($popular_products) ? array_slice($popular_products, 0, 10) : (!empty($products) ? array_slice($products, 0, 10) : []);
$deals_list    = (!empty($flash_sale) && (!empty($flash_sale_products) || !empty($deal_products)))
    ? (!empty($flash_sale_products) ? $flash_sale_products : $deal_products)
    : [];
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
                        <span>See all</span>
                        <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="tf-grid-layout tf-col-2 lg-col-4 xl-col-5">
                    <?php if (!empty($arrivals_list)): ?>
                        <?php foreach ($arrivals_list as $p): ?>
                            <?php $this->load->view('template1/home/_product_card', ['p' => $p, 'currency_symbol' => $curr]); ?>
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
                                       $dp['is_flash_sale'] = 1;
                                   ?>
                                   <div class="swiper-slide">
                                      <?php $this->load->view('template1/home/_product_card', ['p' => $dp, 'currency_symbol' => $curr]); ?>
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
        <?php endif; ?>
        <!-- /Running Flash Sales Section -->

        <!-- 3. Popular Products Section (Slider like Bazaro Best Sellers) -->
        <section class="flat-spacing-3 pt-4 pb-5 aqf-seller-area fix">
            <div class="container">
                <div class="fsh-section-header d-flex flex-wrap align-items-end justify-content-between mb-4 pb-2 border-bottom">
                    <div>
                        <h3 class="fsh-section-title mb-0">Popular Products</h3>
                    </div>
                    <a href="<?= site_url('shop?filter=popular'); ?>" class="fsh-see-all-link">
                        <span>See all</span>
                        <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>

                <?php if (!empty($popular_list)): ?>
                    <div class="aq-product-slide-wrap p-relative">
                        <div class="aq-product-arrow">
                            <button type="button" class="aq-product-prev" aria-label="Previous slide">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12" fill="none">
                                        <path d="M5.75 10.75L0.75 5.75L5.75 0.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </button>
                            <button type="button" class="aq-product-next" aria-label="Next slide">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12" fill="none">
                                        <path d="M0.75 10.75L5.75 5.75L0.75 0.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                        <div class="swiper aq-product-active">
                            <div class="swiper-wrapper">
                                <?php foreach ($popular_list as $p): ?>
                                    <div class="swiper-slide">
                                        <?php $this->load->view('template1/home/_product_card', ['p' => $p, 'currency_symbol' => $curr]); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <p class="text-muted mb-0">No products available at the moment.</p>
                    </div>
                <?php endif; ?>
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

        <!-- Dynamic Countdown, Sliders & Color Swatch Initialization -->
        <script>
        (function() {
            function initFashionHomeSlidersAndCountdown() {
                // 1. Live Countdown (Flash Sales)
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

                // 2. Flash Sales Swiper Slider (.aqf-deals-slider-active)
                var dealsSliderEl = document.querySelector('.aqf-deals-slider-active');
                if (dealsSliderEl && typeof Swiper !== 'undefined' && !dealsSliderEl.swiper) {
                    new Swiper('.aqf-deals-slider-active', {
                        slidesPerView: 3,
                        loop: true,
                        autoplay: true,
                        spaceBetween: 18,
                        speed: 1000,
                        navigation: {
                            prevEl: '.aqf-deals-prev',
                            nextEl: '.aqf-deals-next',
                        },
                        breakpoints: {
                            1400: { slidesPerView: 3, spaceBetween: 18 },
                            1200: { slidesPerView: 3, spaceBetween: 15 },
                            992:  { slidesPerView: 2, spaceBetween: 15 },
                            768:  { slidesPerView: 2, spaceBetween: 15 },
                            576:  { slidesPerView: 2, spaceBetween: 15 },
                            0:    { slidesPerView: 2, spaceBetween: 12 }
                        }
                    });
                }

                // 3. Popular Products Swiper Slider (.aq-product-active) - 5 products in a row
                var popularSliderEl = document.querySelector('.aq-product-active');
                if (popularSliderEl && typeof Swiper !== 'undefined' && !popularSliderEl.swiper) {
                    new Swiper('.aq-product-active', {
                        slidesPerView: 5,
                        loop: true,
                        autoplay: true,
                        spaceBetween: 30,
                        speed: 1000,
                        navigation: {
                            prevEl: '.aq-product-prev',
                            nextEl: '.aq-product-next',
                        },
                        breakpoints: {
                            1400: { slidesPerView: 5, spaceBetween: 30 },
                            1200: { slidesPerView: 5, spaceBetween: 24 },
                            992:  { slidesPerView: 4, spaceBetween: 20 },
                            768:  { slidesPerView: 3, spaceBetween: 15 },
                            576:  { slidesPerView: 2, spaceBetween: 15 },
                            0:    { slidesPerView: 2, spaceBetween: 12 }
                        }
                    });
                }
            }

            // 4. Delegated Color Swatch Hover/Click Preview for all .card-product (including Swiper loop clones)
            if (!window._fshColorSwatchBound) {
                window._fshColorSwatchBound = true;
                ['click', 'mouseover'].forEach(function(evtType) {
                    document.addEventListener(evtType, function(e) {
                        var swatch = e.target.closest('.card-product .color-swatch');
                        if (!swatch) return;
                        var card = swatch.closest('.card-product');
                        var swatchImg = swatch.querySelector('img');
                        if (card && swatchImg) {
                            var newSrc = swatchImg.getAttribute('src') || swatchImg.getAttribute('data-src');
                            var prodImg = card.querySelector('.img-product');
                            if (prodImg && newSrc) {
                                prodImg.setAttribute('src', newSrc);
                                prodImg.setAttribute('data-src', newSrc);
                            }
                        }
                        if (swatch.parentElement) {
                            swatch.parentElement.querySelectorAll('.color-swatch.active').forEach(function(sib) {
                                sib.classList.remove('active');
                            });
                        }
                        swatch.classList.add('active');
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initFashionHomeSlidersAndCountdown);
            } else {
                initFashionHomeSlidersAndCountdown();
            }
            window.addEventListener('load', initFashionHomeSlidersAndCountdown);
        })();
        </script>

        <!-- Section, Badges, Bazaro Deals Area & Popular Products Slider Styles -->
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
            font-size: 30px;
            /* font-weight: 700; */
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

        /* Product Card Badges (New Arrivals / Flash Sales / Popular Products) */
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

        /* Uniform Product Card Size across New Arrivals, Flash Sales & Popular Products */
        .card-product {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .card-product .card-product-wrapper {
            aspect-ratio: 3 / 4 !important;
            width: 100% !important;
            overflow: hidden !important;
            border-radius: 8px;
            background-color: #f8f9fa;
            position: relative;
        }
        .card-product .card-product-wrapper .product-img {
            display: block;
            width: 100% !important;
            height: 100% !important;
        }
        .card-product .card-product-wrapper .product-img img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            display: block;
        }

        /* =========================================================
           Bazaro Best Sellers Slider Styles (.aqf-seller-area / .aq-product-slide-wrap)
           ========================================================= */
        .aqf-seller-area.fix {
            overflow: visible;
        }
        .aq-product-slide-wrap.p-relative {
            position: relative;
        }
        @media (max-width: 575px) {
            .aq-product-arrow {
                display: none;
            }
        }
        .aq-product-arrow button,
        .aq-product-arrow button.aq-product-prev,
        .aq-product-arrow button.aq-product-next {
            position: absolute;
            top: 38%;
            transform: translateY(-50%);
            inset-inline-start: -30px;
            z-index: 20;
            height: 46px !important;
            width: 46px !important;
            min-width: 46px !important;
            max-width: 46px !important;
            min-height: 46px !important;
            max-height: 46px !important;
            padding: 0 !important;
            margin: 0 !important;
            gap: 0 !important;
            aspect-ratio: 1 / 1 !important;
            border-radius: 50% !important;
            border: 1px solid rgba(0, 0, 0, 0.08) !important;
            outline: none !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            line-height: 1 !important;
            flex-shrink: 0 !important;
            opacity: 0;
            visibility: hidden;
            color: var(--aq-common-black) !important;
            background: var(--aq-common-white) !important;
            box-shadow: 0 3px 10px rgba(1, 15, 28, 0.16) !important;
            transition: all 0.3s ease-out;
        }
        .aq-product-arrow button span {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 100%;
            height: 100%;
            line-height: 1 !important;
        }
        .aq-product-arrow button svg {
            display: block;
            transform: translateX(-1px);
        }
        .aq-product-arrow button.aq-product-next {
            inset-inline-start: auto;
            inset-inline-end: -30px;
        }
        .aq-product-arrow button.aq-product-next svg {
            transform: translateX(1px);
        }
        .aq-product-arrow button:hover {
            color: var(--aq-common-white) !important;
            border-color: var(--aq-common-black) !important;
            background-color: var(--aq-common-black) !important;
        }
        .aq-product-slide-wrap:hover .aq-product-arrow button {
            opacity: 1;
            visibility: visible;
            inset-inline-start: -23px;
        }
        .aq-product-slide-wrap:hover .aq-product-arrow button.aq-product-next {
            inset-inline-start: auto;
            inset-inline-end: -23px;
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
        .aqf-deals-banner-wrap a,
        .aqf-deals-slider-arrow button {
            color: inherit;
            outline: none;
            border: none;
            background: transparent;
            text-decoration: none;
            transition: all 0.3s ease-out;
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
            color: var(--aq-common-black) !important;
            background: transparent !important;
            padding: 4px !important;
            width: auto !important;
            height: auto !important;
            min-width: 0 !important;
            border-radius: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 0 !important;
        }
        .aqf-deals-slider-arrow button:hover {
            color: var(--aq-common-red) !important;
        }

        /* Deals Slider Main & Active Container - Keep 3 cards equal in width (~230px) to 5-per-row cards */
        .aqf-deals-slider-main {
            padding-right: 20px;
        }
        .aqf-deals-slider-active {
            margin-inline-end: 0 !important;
        }
        @media only screen and (min-width: 1200px) and (max-width: 1399px) {
            .aqf-deals-slider-main {
                padding-top: 50px !important;
                padding-bottom: 30px !important;
                padding-right: 15px !important;
            }
        }
        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .aqf-deals-slider-main {
                padding: 30px !important;
                padding-bottom: 20px !important;
            }
        }
        @media (max-width: 767.98px) {
            .fsh-section-title {
                font-size: 22px;
            }
            .product-badge-group {
                top: 10px;
                left: 10px;
            }
            .product-badge {
                font-size: 10px;
                padding: 4px 9px;
            }
            .aqf-deals-slider-main {
                padding: 20px 12px 16px !important;
            }
            .aqf-deals-slider-arrow {
                margin-right: 0 !important;
            }
        }
        </style>
