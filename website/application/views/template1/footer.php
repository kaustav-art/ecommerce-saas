        <!-- Footer -->
        <footer id="footer" class="footer bg-dark text-white pt-5 pb-4 mt-5">
            <div class="container">
                <div class="row gy-4">
                    <!-- Brand info -->
                    <div class="col-lg-4 col-md-6">
                        <div class="footer-logo mb-3">
                            <a href="<?= site_url('home'); ?>" class="d-inline-block mb-3">
                                <?php
                                  $footer_logo = !empty($store_settings['site_logo'])
                                    ? base_url('assets/images/logo/' . $store_settings['site_logo'])
                                    : base_url('assets/images/logo/logo.webp');
                                ?>
                                <img src="<?= $footer_logo; ?>" alt="<?= html_escape($site_name ?? ($store_settings['site_name'] ?? 'Store')); ?>" style="max-height: 42px; width: auto; background: #ffffff; padding: 5px 12px; border-radius: 6px;" onerror="this.src='<?= base_url('assets/images/logo/logo.webp'); ?>'">
                            </a>
                            <p class="text-white-50 small">Multipurpose eCommerce Platform powered by CodeIgniter 3 MVC</p>
                        </div>
                        <ul class="list-unstyled text-white-50 small">
                            <li class="mb-2"><i class="fa-solid fa-location-dot me-2 text-primary"></i> <?= html_escape($store_settings['site_address'] ?? '123 Commerce St'); ?></li>
                            <li class="mb-2"><i class="fa-solid fa-phone me-2 text-primary"></i> <?= html_escape($store_settings['site_phone'] ?? '+1 800 555-0199'); ?></li>
                            <li class="mb-2"><i class="fa-solid fa-envelope me-2 text-primary"></i> <?= html_escape($store_settings['site_email'] ?? 'support@example.com'); ?></li>
                        </ul>
                    </div>

                    <!-- Quick Links -->
                    <div class="col-lg-2 col-md-3 col-6">
                        <h6 class="text-white text-uppercase fw-bold mb-3">Quick Links</h6>
                        <ul class="list-unstyled text-white-50 small">
                            <li class="mb-2"><a href="<?= site_url('shop'); ?>" class="text-white-50 text-decoration-none hover-white">Catalog</a></li>
                            <li class="mb-2"><a href="<?= site_url('compare'); ?>" class="text-white-50 text-decoration-none hover-white">Compare Products</a></li>
                            <li class="mb-2"><a href="<?= site_url('order/track'); ?>" class="text-white-50 text-decoration-none hover-white">Track Order</a></li>
                            <li class="mb-2"><a href="<?= site_url('terms'); ?>" class="text-white-50 text-decoration-none hover-white">Terms of Service</a></li>
                            <li class="mb-2"><a href="<?= site_url('privacy'); ?>" class="text-white-50 text-decoration-none hover-white">Privacy Policy</a></li>
                            <li class="mb-2"><a href="<?= site_url('returns-policy'); ?>" class="text-white-50 text-decoration-none hover-white">Returns & Refunds</a></li>
                        </ul>
                    </div>

                    <!-- Categories -->
                    <div class="col-lg-2 col-md-3 col-6">
                        <h6 class="text-white text-uppercase fw-bold mb-3">Categories</h6>
                        <ul class="list-unstyled text-white-50 small">
                            <?php if (!empty($nav_categories)): ?>
                                <?php $count = 0; foreach ($nav_categories as $cat): if ($count++ >= 5) break; ?>
                                    <li class="mb-2">
                                        <a href="<?= site_url('shop/' . $cat['slug']); ?>" class="text-white-50 text-decoration-none hover-white">
                                            <?= html_escape($cat['name']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <!-- Newsletter & Gateways -->
                    <div class="col-lg-4 col-md-6">
                        <h6 class="text-white text-uppercase fw-bold mb-2">Newsletter</h6>
                        <p class="text-white-50 small mb-2">Subscribe for exclusive flash sales, seasonal deals, and new arrivals:</p>
                        <form action="<?= site_url('newsletter/subscribe'); ?>" method="POST" class="d-flex gap-2 mb-3">
                            <input type="email" name="email" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Enter your email address..." required>
                            <button class="btn btn-primary btn-sm px-3" type="submit">Join</button>
                        </form>

                        <h6 class="text-white text-uppercase fw-bold mb-2">Accepted Payments</h6>
                        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                            <span class="badge bg-primary p-2">Stripe</span>
                            <span class="badge bg-info text-dark p-2">Razorpay</span>
                            <span class="badge bg-warning text-dark p-2">PayU</span>
                            <span class="badge bg-success p-2">Cash on Delivery</span>
                        </div>
                        <p class="text-white-50 small mb-0"><i class="fa-solid fa-shield-halved text-success me-1"></i> 256-Bit SSL Encryption Guaranteed</p>
                    </div>
                </div>

                <div class="border-top border-secondary pt-3 mt-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 gap-sm-0 text-white-50 small text-center text-sm-start">
                    <div>
                        © <?= date('Y'); ?> <?= html_escape($site_name ?? ($store_settings['site_name'] ?? 'Store')); ?>. All Rights Reserved.
                    </div>
                    <div class="d-flex gap-3">
                        <a href="<?= site_url('terms'); ?>" class="text-white-50 text-decoration-none">Terms</a>
                        <a href="<?= site_url('privacy'); ?>" class="text-white-50 text-decoration-none">Privacy</a>
                        <a href="<?= site_url('returns-policy'); ?>" class="text-white-50 text-decoration-none">Returns</a>
                    </div>
                </div>
            </div>
        </footer>
        <!-- /Footer -->
    </div>
    <!-- /wrapper -->

    <script type="text/javascript" src="<?= base_url('assets/js/jquery.min.js'); ?>"></script>
    <script type="text/javascript" src="<?= base_url('assets/js/bootstrap.min.js'); ?>"></script>
    <script type="text/javascript" src="<?= base_url('assets/js/swiper-bundle.min.js'); ?>"></script>
    <script type="text/javascript" src="<?= base_url('assets/js/carousel.js'); ?>"></script>
    <script type="text/javascript" src="<?= base_url('assets/js/lazysize.min.js'); ?>"></script>
    <script type="text/javascript" src="<?= base_url('assets/js/count-down.js'); ?>"></script>
    <script type="text/javascript" src="<?= base_url('assets/js/wow.min.js'); ?>"></script>
    <script type="text/javascript" src="<?= base_url('assets/js/main.js?v=' . (file_exists(FCPATH . 'assets/js/main.js') ? filemtime(FCPATH . 'assets/js/main.js') : '1.1')); ?>"></script>
    <script>
        if (typeof WOW !== 'undefined') {
            new WOW().init();
        }
    </script>
</body>
</html>
