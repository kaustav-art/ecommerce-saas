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

        // Global App Toast Notification
        function showAppToast(message, type) {
            type = type || 'success';
            var existingToast = document.getElementById('app-toast-notice');
            if (existingToast) existingToast.remove();

            var toast = document.createElement('div');
            toast.id = 'app-toast-notice';
            var bgClass = (type === 'success') ? 'bg-success text-white' : ((type === 'danger') ? 'bg-danger text-white' : 'bg-dark text-white');
            var icon = (type === 'success') ? 'fa-circle-check text-white' : 'fa-circle-info text-warning';
            toast.className = 'position-fixed bottom-0 end-0 p-3';
            toast.style.zIndex = '999999';
            toast.innerHTML = 
                '<div class="toast show ' + bgClass + ' border-0 shadow-lg rounded-3" role="alert" style="max-width: 400px;">' +
                    '<div class="d-flex align-items-center justify-content-between py-2 ps-3 pe-2 gap-2">' +
                        '<div class="toast-body d-flex align-items-center gap-2 p-0 flex-grow-1" style="font-size: 13.5px; line-height: 1.45;">' +
                            '<i class="fa-solid ' + icon + ' fs-5 flex-shrink-0"></i>' +
                            '<span class="fw-medium">' + message + '</span>' +
                        '</div>' +
                        '<button type="button" class="fk-toast-close-btn" aria-label="Close" onclick="this.closest(\'#app-toast-notice\').remove()" style="position: static !important; width: 28px !important; height: 28px !important; min-width: 28px !important; min-height: 28px !important; padding: 0 !important; margin: 0 !important; border: none !important; border-radius: 6px !important; background: transparent !important; color: inherit !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0 !important; opacity: 0.85 !important; cursor: pointer !important; box-shadow: none !important; transform: none !important;">' +
                            '<i class="fa-solid fa-xmark" style="font-size: 15px; line-height: 1;"></i>' +
                        '</button>' +
                    '</div>' +
                '</div>';
            document.body.appendChild(toast);
            setTimeout(function() {
                if (toast && toast.parentNode) toast.remove();
            }, 4000);
        }
        window.showAppToast = showAppToast;

        // Global Wishlist AJAX Toggle (No reload, toaster, toggle heart icon, sync badge)
        function toggleProductWishlist(productId, btnEl) {
            if (typeof event !== 'undefined' && event) {
                event.preventDefault();
                event.stopPropagation();
            }
            productId = parseInt(productId, 10);
            if (!productId || isNaN(productId)) return;

            if (!btnEl && typeof event !== 'undefined' && event) {
                btnEl = event.currentTarget || (event.target ? event.target.closest('a, button') : null);
            }

            var fd = new FormData();
            fd.append('product_id', productId);

            fetch('<?= site_url("wishlist/toggle"); ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res && res.success) {
                    var isAdded = (res.action === 'added' || res.is_wishlisted === true);

                    // Update all buttons for this product across the page
                    var selectors = [
                        '[data-product-id="' + productId + '"]',
                        '.box-icon.wishlist[href*="/' + productId + '"]',
                        '.btn-icon-action[href*="/' + productId + '"]',
                        '.wishlist-btn[onclick*="' + productId + '"]'
                    ];
                    var buttons = document.querySelectorAll(selectors.join(', '));
                    buttons.forEach(function(btn) {
                        var icon = btn.querySelector('i');
                        var tooltip = btn.querySelector('.tooltip');
                        if (isAdded) {
                            btn.classList.add('active', 'in-wishlist');
                            btn.setAttribute('title', 'Remove from Wishlist');
                            if (icon) {
                                icon.className = 'fa-solid fa-heart text-danger';
                            }
                            if (tooltip) {
                                tooltip.textContent = 'Remove';
                            }
                        } else {
                            btn.classList.remove('active', 'in-wishlist');
                            btn.setAttribute('title', 'Add to Wishlist');
                            if (icon) {
                                icon.className = 'fa-regular fa-heart';
                            }
                            if (tooltip) {
                                tooltip.textContent = 'Wishlist';
                            }
                        }
                    });

                    // Update header & page wishlist counter badges
                    if (typeof res.wishlist_count !== 'undefined') {
                        document.querySelectorAll('.wishlist-count, .wishlist-badge').forEach(function(el) {
                            el.textContent = res.wishlist_count;
                        });
                        var headerCounter = document.getElementById('wishlist-counter');
                        if (headerCounter) {
                            headerCounter.textContent = res.wishlist_count;
                            headerCounter.style.display = (res.wishlist_count > 0) ? '' : 'none';
                        }
                    }

                    // Show Toast Notification
                    var toastMsg = res.message || (isAdded ? 'Added to your wishlist!' : 'Removed from your wishlist.');
                    showAppToast(toastMsg, 'success');
                } else {
                    showAppToast((res && res.message) ? res.message : 'Unable to update wishlist.', 'danger');
                }
            })
            .catch(function(err) {
                showAppToast('Network error updating wishlist.', 'danger');
            });
        }
        window.toggleProductWishlist = toggleProductWishlist;
        window.grcToggleWishlist = toggleProductWishlist;
        window.toggleWishlistItem = toggleProductWishlist;
<?php if (empty($is_user_project)): ?>
        (function() {
            var siteBase = '<?= rtrim(base_url(), "/"); ?>';
            var tplParam = 'fashion';

            // Ensure current browser URL always displays ?preview_tpl=fashion on all pages
            if (window.location.search.indexOf('preview_tpl=') === -1 && window.history && window.history.replaceState) {
                var curUrl = window.location.pathname + (window.location.search ? window.location.search + '&preview_tpl=' + tplParam : '?preview_tpl=' + tplParam) + window.location.hash;
                window.history.replaceState({}, '', curUrl);
            }

            function appendPreviewTpl(root) {
                if (!root || !root.querySelectorAll) return;
                root.querySelectorAll('a[href]').forEach(function(a) {
                    var href = a.getAttribute('href');
                    if (!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0 || href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0) return;
                    if (href.indexOf(siteBase) === 0 && href.indexOf('preview_tpl=') === -1) {
                        var hashIdx = href.indexOf('#');
                        var baseHref = hashIdx !== -1 ? href.substring(0, hashIdx) : href;
                        var hashPart = hashIdx !== -1 ? href.substring(hashIdx) : '';
                        var sep = baseHref.indexOf('?') === -1 ? '?' : '&';
                        a.setAttribute('href', baseHref + sep + 'preview_tpl=' + tplParam + hashPart);
                    }
                });

                root.querySelectorAll('form[action]').forEach(function(f) {
                    var act = f.getAttribute('action');
                    if (act && act.indexOf(siteBase) === 0) {
                        if (act.indexOf('preview_tpl=') === -1) {
                            var sep = act.indexOf('?') === -1 ? '?' : '&';
                            f.setAttribute('action', act + sep + 'preview_tpl=' + tplParam);
                        }
                        var method = (f.getAttribute('method') || 'get').toLowerCase();
                        if (method === 'get' && !f.querySelector('input[name="preview_tpl"]')) {
                            var inp = document.createElement('input');
                            inp.type = 'hidden';
                            inp.name = 'preview_tpl';
                            inp.value = tplParam;
                            f.appendChild(inp);
                        }
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                appendPreviewTpl(document);
                if (typeof MutationObserver !== 'undefined') {
                    var obs = new MutationObserver(function(mutations) {
                        mutations.forEach(function(m) {
                            m.addedNodes.forEach(function(node) {
                                if (node.nodeType === 1) {
                                    appendPreviewTpl(node);
                                }
                            });
                        });
                    });
                    obs.observe(document.body, { childList: true, subtree: true });
                }
            });
        })();
<?php endif; ?>
    </script>
</body>
</html>
