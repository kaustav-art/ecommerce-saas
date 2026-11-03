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
                        <span class="fsh-section-eyebrow">JUST DROPPED</span>
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

        <!-- 2. Running Flash Sales Section (Unique Fashion Editorial Showcase) -->
        <?php if (!empty($flash_sale) && !empty($deals_list)): ?>
        <?php
            $fs_end_time = !empty($flash_sale['end_time']) ? $flash_sale['end_time'] : date('Y-m-d H:i:s', strtotime('+2 days'));
            $fs_disc_pct = !empty($flash_sale['discount_percent']) ? (float)$flash_sale['discount_percent'] : 0;
        ?>
        <section class="fsh-flash-sale-section py-4 my-2">
            <div class="container">
                <div class="fsh-flash-showcase">
                    <!-- Decorative Ambient Glows -->
                    <div class="fsh-flash-glow fsh-glow-1"></div>
                    <div class="fsh-flash-glow fsh-glow-2"></div>

                    <!-- Top Header Banner & Live Countdown -->
                    <div class="fsh-flash-header">
                        <div class="fsh-flash-title-area">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <span class="fsh-live-pill">
                                    <span class="fsh-live-dot"></span>
                                    LIVE FLASH EVENT
                                </span>
                                <?php if ($fs_disc_pct > 0): ?>
                                    <span class="fsh-extra-off-pill">
                                        <i class="fa-solid fa-bolt me-1"></i>Extra <?= rtrim(rtrim(number_format($fs_disc_pct, 2), '0'), '.'); ?>% OFF
                                    </span>
                                <?php endif; ?>
                            </div>
                            <h2 class="fsh-flash-heading mb-1">
                                <?= html_escape(!empty($flash_sale['title']) ? $flash_sale['title'] : 'Runway Flash Sale'); ?>
                            </h2>
                            <p class="fsh-flash-subtext mb-0">
                                Exclusive limited-time markdowns on curated fashion pieces before the timer runs out.
                            </p>
                        </div>

                        <div class="fsh-flash-timer-wrap">
                            <div class="fsh-timer-label">
                                <i class="fa-regular fa-clock me-1"></i> HURRY! OFFER ENDS IN
                            </div>
                            <div class="fsh-countdown-boxes" id="fshFlashCountdown" data-end-time="<?= html_escape($fs_end_time); ?>">
                                <div class="fsh-time-card">
                                    <span class="fsh-time-num" id="fsh-days">00</span>
                                    <span class="fsh-time-unit">Days</span>
                                </div>
                                <span class="fsh-time-sep">:</span>
                                <div class="fsh-time-card">
                                    <span class="fsh-time-num" id="fsh-hours">00</span>
                                    <span class="fsh-time-unit">Hours</span>
                                </div>
                                <span class="fsh-time-sep">:</span>
                                <div class="fsh-time-card">
                                    <span class="fsh-time-num" id="fsh-minutes">00</span>
                                    <span class="fsh-time-unit">Mins</span>
                                </div>
                                <span class="fsh-time-sep">:</span>
                                <div class="fsh-time-card fsh-time-sec">
                                    <span class="fsh-time-num" id="fsh-seconds">00</span>
                                    <span class="fsh-time-unit">Secs</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Flash Sale Products Grid -->
                    <div class="fsh-flash-grid">
                        <?php foreach ($deals_list as $dp):
                            $d_id    = (int)($dp['id'] ?? 0);
                            $d_title = $dp['title'] ?? ($dp['name'] ?? 'Fashion Piece');
                            $d_slug  = !empty($dp['slug']) ? site_url('product/' . $dp['slug']) : site_url('product/' . $d_id);
                            $d_img   = !empty($dp['main_image']) ? base_url('assets/images/' . $dp['main_image']) : base_url('assets/images/products/womens/women-1.jpg');
                            $d_hover = !empty($dp['gallery_images_decoded'][0])
                                ? base_url('assets/images/' . $dp['gallery_images_decoded'][0])
                                : (!empty($dp['hover_image']) ? base_url('assets/images/' . $dp['hover_image']) : $d_img);
                            $d_reg   = (float)($dp['regular_price'] ?? $dp['price'] ?? 0);
                            $d_sal   = (!empty($dp['sale_price']) && (float)$dp['sale_price'] > 0 && (float)$dp['sale_price'] < $d_reg)
                                ? (float)$dp['sale_price']
                                : $d_reg;
                            $d_disc  = !empty($dp['total_discount_percent'])
                                ? (int)$dp['total_discount_percent']
                                : (($d_reg > $d_sal && $d_reg > 0) ? round((($d_reg - $d_sal) / $d_reg) * 100) : (int)$fs_disc_pct);
                            $d_saved = max(0, $d_reg - $d_sal);
                            $d_cat   = $dp['category_name'] ?? ($dp['brand_name'] ?? 'Fashion');
                            $d_rev_cnt = (isset($dp['reviews']) && is_array($dp['reviews'])) ? count($dp['reviews']) : (int)($dp['reviews_count'] ?? 0);
                            $d_rating  = $d_rev_cnt > 0 ? (float)($dp['rating'] ?? 0) : 0.0;
                            $d_stars   = (int)round($d_rating);
                        ?>
                        <div class="fsh-deal-card">
                            <div class="fsh-deal-media">
                                <a href="<?= $d_slug; ?>" class="fsh-deal-img-link">
                                    <img src="<?= $d_img; ?>" alt="<?= html_escape($d_title); ?>" class="fsh-deal-img-main" onerror="this.src='<?= base_url('assets/images/products/womens/women-1.jpg'); ?>'">
                                    <img src="<?= $d_hover; ?>" alt="<?= html_escape($d_title); ?>" class="fsh-deal-img-hover" onerror="this.src='<?= base_url('assets/images/products/womens/women-2.jpg'); ?>'">
                                </a>

                                <div class="fsh-deal-badges">
                                    <span class="fsh-ribbon-bolt"><i class="fa-solid fa-bolt me-1"></i>FLASH</span>
                                    <?php if ($d_disc > 0): ?>
                                        <span class="fsh-ribbon-disc">-<?= $d_disc; ?>%</span>
                                    <?php endif; ?>
                                </div>

                                <a href="<?= site_url('wishlist/toggle/' . $d_id); ?>" class="fsh-deal-wish-btn" title="Add to Wishlist">
                                    <i class="fa-regular fa-heart"></i>
                                </a>
                            </div>

                            <div class="fsh-deal-body">
                                <div class="d-flex align-items-center justify-content-between gap-1 mb-1">
                                    <span class="fsh-deal-cat"><?= html_escape($d_cat); ?></span>
                                    <div class="fsh-deal-stars text-warning">
                                        <?php for ($s = 1; $s <= 5; $s++): ?>
                                            <i class="fa-<?= ($s <= $d_stars) ? 'solid' : 'regular'; ?> fa-star"></i>
                                        <?php endfor; ?>
                                        <span class="text-muted ms-1">(<?= $d_rev_cnt; ?>)</span>
                                    </div>
                                </div>

                                <h5 class="fsh-deal-title">
                                    <a href="<?= $d_slug; ?>"><?= html_escape($d_title); ?></a>
                                </h5>

                                <div class="fsh-deal-price-row">
                                    <div class="fsh-deal-prices">
                                        <span class="fsh-deal-new-price"><?= html_escape($curr) . number_format($d_sal, 2); ?></span>
                                        <?php if ($d_reg > $d_sal): ?>
                                            <del class="fsh-deal-old-price"><?= html_escape($curr) . number_format($d_reg, 2); ?></del>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($d_saved > 0): ?>
                                        <span class="fsh-deal-save-tag">Save <?= html_escape($curr) . number_format($d_saved, 0); ?></span>
                                    <?php endif; ?>
                                </div>

                                <form action="<?= site_url('cart/add'); ?>" method="POST" class="mt-auto pt-2">
                                    <input type="hidden" name="product_id" value="<?= $d_id; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="fsh-deal-cart-btn w-100">
                                        <i class="fa-solid fa-bag-shopping me-1"></i> Grab Flash Deal
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Dynamic Countdown Script for Fashion Flash Sale -->
        <script>
        (function() {
            function initFshFlashCountdown() {
                var el = document.getElementById('fshFlashCountdown');
                if (!el) return;

                var daysEl  = document.getElementById('fsh-days');
                var hoursEl = document.getElementById('fsh-hours');
                var minsEl  = document.getElementById('fsh-minutes');
                var secsEl  = document.getElementById('fsh-seconds');

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
                if (window._fshFlashInterval) {
                    clearInterval(window._fshFlashInterval);
                }
                window._fshFlashInterval = setInterval(tick, 1000);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initFshFlashCountdown);
            } else {
                initFshFlashCountdown();
            }
        })();
        </script>
        <?php endif; ?>
        <!-- /Running Flash Sales Section -->

        <!-- 3. Popular Products Section -->
        <section class="flat-spacing-3 pt-4 pb-5">
            <div class="container">
                <div class="fsh-section-header d-flex flex-wrap align-items-end justify-content-between mb-4 pb-2 border-bottom">
                    <div>
                        <span class="fsh-section-eyebrow">TRENDING NOW</span>
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

        <!-- Section & Fashion Flash Sale Styles -->
        <style>
        /* Section Headings */
        .fsh-section-eyebrow {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #e11d48;
            margin-bottom: 4px;
        }
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
            color: #e11d48;
            border-bottom-color: #e11d48;
            transform: translateX(2px);
        }

        /* Product Badges */
        .product-badge-group {
            position: absolute;
            top: 10px;
            left: 10px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
            z-index: 5;
            pointer-events: none;
        }
        .product-badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            line-height: 1.1;
            padding: 3px 7px;
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        }
        .product-badge.badge-sale {
            background-color: #ef4444;
        }
        .product-badge.badge-flash-sale {
            background: linear-gradient(135deg, #e11d48, #be123c);
            letter-spacing: 0.6px;
        }
        .animate-fade-in {
            animation: productFadeIn 0.35s ease-out;
        }
        @keyframes productFadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* =========================================================
           Unique Fashion Editorial Flash Sale Showcase
           ========================================================= */
        .fsh-flash-showcase {
            position: relative;
            overflow: hidden;
            border-radius: 22px;
            background: linear-gradient(135deg, #111827 0%, #1f162b 52%, #3b1026 100%);
            padding: 36px 32px;
            box-shadow: 0 22px 50px rgba(17, 24, 39, 0.22);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .fsh-flash-glow {
            position: absolute;
            border-radius: 50%;
            filter: blur(75px);
            pointer-events: none;
            z-index: 0;
        }
        .fsh-glow-1 {
            width: 320px;
            height: 320px;
            background: rgba(225, 29, 72, 0.28);
            top: -110px;
            right: -60px;
        }
        .fsh-glow-2 {
            width: 260px;
            height: 260px;
            background: rgba(245, 158, 11, 0.16);
            bottom: -90px;
            left: -50px;
        }
        .fsh-flash-header {
            position: relative;
            z-index: 1;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            padding-bottom: 26px;
            margin-bottom: 28px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        }
        .fsh-live-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(225, 29, 72, 0.22);
            border: 1px solid rgba(251, 113, 133, 0.45);
            color: #fda4af;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.2px;
            padding: 5px 13px;
            border-radius: 999px;
            text-transform: uppercase;
        }
        .fsh-live-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #fb7185;
            box-shadow: 0 0 0 0 rgba(251, 113, 133, 0.7);
            animation: fshPulseDot 1.6s infinite;
        }
        @keyframes fshPulseDot {
            0% { box-shadow: 0 0 0 0 rgba(251, 113, 133, 0.7); }
            70% { box-shadow: 0 0 0 8px rgba(251, 113, 133, 0); }
            100% { box-shadow: 0 0 0 0 rgba(251, 113, 133, 0); }
        }
        .fsh-extra-off-pill {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.7px;
            padding: 5px 13px;
            border-radius: 999px;
            text-transform: uppercase;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }
        .fsh-flash-heading {
            font-size: 32px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.5px;
            line-height: 1.2;
        }
        .fsh-flash-subtext {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.72);
        }
        .fsh-flash-timer-wrap {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 16px;
            padding: 14px 20px;
            text-align: center;
        }
        .fsh-timer-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.2px;
            color: #fda4af;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .fsh-countdown-boxes {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .fsh-time-card {
            min-width: 58px;
            background: rgba(15, 23, 42, 0.78);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 10px;
            padding: 8px 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .fsh-time-card.fsh-time-sec {
            border-color: rgba(251, 113, 133, 0.55);
            background: rgba(225, 29, 72, 0.22);
        }
        .fsh-time-num {
            font-size: 20px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
        }
        .fsh-time-unit {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: rgba(255, 255, 255, 0.65);
            margin-top: 2px;
        }
        .fsh-time-sep {
            font-size: 18px;
            font-weight: 800;
            color: rgba(255, 255, 255, 0.45);
        }

        /* Flash Sale Product Cards Grid */
        .fsh-flash-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 20px;
        }
        .fsh-deal-card {
            background: #ffffff;
            border-radius: 14px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.18);
        }
        .fsh-deal-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 18px 34px rgba(0, 0, 0, 0.28);
        }
        .fsh-deal-media {
            position: relative;
            aspect-ratio: 3 / 3.6;
            overflow: hidden;
            background: #f8fafc;
        }
        .fsh-deal-img-link {
            display: block;
            width: 100%;
            height: 100%;
        }
        .fsh-deal-img-main,
        .fsh-deal-img-hover {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: opacity 0.4s ease, transform 0.5s ease;
        }
        .fsh-deal-img-hover {
            position: absolute;
            inset: 0;
            opacity: 0;
        }
        .fsh-deal-card:hover .fsh-deal-img-hover {
            opacity: 1;
            transform: scale(1.04);
        }
        .fsh-deal-badges {
            position: absolute;
            top: 10px;
            left: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
            z-index: 3;
        }
        .fsh-ribbon-bolt {
            background: #111827;
            color: #fbbf24;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.6px;
            padding: 4px 9px;
            border-radius: 6px;
            text-transform: uppercase;
        }
        .fsh-ribbon-disc {
            background: #e11d48;
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            padding: 4px 8px;
            border-radius: 6px;
        }
        .fsh-deal-wish-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.92);
            color: #111827;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.12);
            transition: all 0.2s ease;
            z-index: 3;
        }
        .fsh-deal-wish-btn:hover {
            background: #e11d48;
            color: #ffffff;
        }
        .fsh-deal-body {
            padding: 14px 16px 16px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }
        .fsh-deal-cat {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
        }
        .fsh-deal-stars {
            font-size: 11px;
        }
        .fsh-deal-title {
            font-size: 15px;
            font-weight: 700;
            margin: 4px 0 8px;
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .fsh-deal-title a {
            color: #111827;
            text-decoration: none;
            transition: color 0.2s;
        }
        .fsh-deal-title a:hover {
            color: #e11d48;
        }
        .fsh-deal-price-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 6px;
        }
        .fsh-deal-new-price {
            font-size: 17px;
            font-weight: 800;
            color: #e11d48;
        }
        .fsh-deal-old-price {
            font-size: 13px;
            color: #9ca3af;
            margin-left: 6px;
        }
        .fsh-deal-save-tag {
            font-size: 11px;
            font-weight: 700;
            background: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
            padding: 2px 8px;
            border-radius: 999px;
        }
        .fsh-deal-cart-btn {
            background: #111827;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 9px 14px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.3px;
            transition: background 0.25s ease;
        }
        .fsh-deal-cart-btn:hover {
            background: #e11d48;
            color: #ffffff;
        }

        @media (max-width: 1199.98px) {
            .fsh-flash-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        @media (max-width: 991.98px) {
            .fsh-flash-showcase {
                padding: 28px 20px;
            }
            .fsh-flash-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .fsh-flash-heading {
                font-size: 26px;
            }
        }
        @media (max-width: 575.98px) {
            .fsh-flash-showcase {
                padding: 22px 14px;
                border-radius: 16px;
            }
            .fsh-flash-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px;
            }
            .fsh-section-title {
                font-size: 22px;
            }
            .fsh-time-card {
                min-width: 48px;
                padding: 6px 8px;
            }
            .fsh-time-num {
                font-size: 16px;
            }
            .fsh-deal-body {
                padding: 10px 10px 12px;
            }
            .fsh-deal-title {
                font-size: 13px;
            }
            .fsh-deal-new-price {
                font-size: 14px;
            }
        }
        </style>
