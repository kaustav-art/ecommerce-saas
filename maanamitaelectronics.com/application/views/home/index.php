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

        <!-- All Products Section -->
        <section class="flat-spacing-3 py-4">
            <div class="container">

                <!-- 4 Column Grid for Desktop (16 initial items = 4 rows of 4) -->
                <div class="tf-grid-layout tf-col-2 lg-col-3 xl-col-4" id="homeProductGrid">
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $p): ?>
                            <?php $this->load->view('home/_product_card', ['p' => $p, 'currency_symbol' => $currency_symbol ?? '$']); ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <p class="text-muted">No products available at the moment.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Infinite Scroll Sentinel & Loader -->
                <div id="homeInfiniteScrollSentinel" class="text-center py-4 my-3 <?= ($total_products <= 16) ? 'd-none' : ''; ?>">
                    <div id="homeScrollLoader" class="d-none">
                        <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="text-muted small mt-2 fw-medium">Loading more products...</div>
                    </div>
                    <div id="homeScrollEndMessage" class="d-none text-muted small py-2">
                        <i class="fa-solid fa-circle-check text-success me-1"></i> You've viewed all <?= (int)$total_products; ?> products
                    </div>
                </div>
            </div>
        </section>
        <!-- /All Products Section -->


        <!-- Features / Service Iconbox -->
        <section class="flat-spacing">
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

        <!-- Product Badge & Infinite Scroll Styles -->
        <style>
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
        .animate-fade-in {
            animation: productFadeIn 0.35s ease-out;
        }
        @keyframes productFadeIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        </style>

        <!-- Infinite Scroll Script -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var grid = document.getElementById('homeProductGrid');
            var sentinel = document.getElementById('homeInfiniteScrollSentinel');
            var loader = document.getElementById('homeScrollLoader');
            var endMsg = document.getElementById('homeScrollEndMessage');

            if (!grid || !sentinel) return;

            var offset = 16;
            var limit = 16;
            var isLoading = false;
            var hasMore = <?= ($total_products > 16) ? 'true' : 'false'; ?>;

            if (!hasMore) return;

            var loadMoreProducts = function() {
                if (isLoading || !hasMore) return;
                isLoading = true;
                if (loader) loader.classList.remove('d-none');

                var url = '<?= site_url("home/load_more"); ?>?offset=' + offset + '&limit=' + limit;
                var xhr = new XMLHttpRequest();
                xhr.open('GET', url, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        isLoading = false;
                        if (loader) loader.classList.add('d-none');

                        if (xhr.status === 200) {
                            try {
                                var data = JSON.parse(xhr.responseText);
                                if (data.status === 'success' && data.html) {
                                    var temp = document.createElement('div');
                                    temp.innerHTML = data.html;
                                    while (temp.firstChild) {
                                        if (temp.firstChild.nodeType === 1) {
                                            grid.appendChild(temp.firstChild);
                                        } else {
                                            temp.removeChild(temp.firstChild);
                                        }
                                    }

                                    offset += (data.count || limit);
                                    hasMore = data.has_more;

                                    if (!hasMore) {
                                        if (observer) observer.disconnect();
                                        if (endMsg) endMsg.classList.remove('d-none');
                                    }
                                } else {
                                    hasMore = false;
                                    if (observer) observer.disconnect();
                                    if (endMsg) endMsg.classList.remove('d-none');
                                }
                            } catch (e) {
                                console.error('Error parsing product JSON:', e);
                            }
                        }
                    }
                };
                xhr.send();
            };

            var observer = null;
            if ('IntersectionObserver' in window) {
                observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            loadMoreProducts();
                        }
                    });
                }, { rootMargin: '300px' });
                observer.observe(sentinel);
            } else {
                window.addEventListener('scroll', function() {
                    if (isLoading || !hasMore) return;
                    var rect = sentinel.getBoundingClientRect();
                    if (rect.top <= window.innerHeight + 300) {
                        loadMoreProducts();
                    }
                });
            }
        });
        </script>
