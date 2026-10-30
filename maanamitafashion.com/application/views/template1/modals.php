    <!-- Search Modal with Real-Time Autocomplete -->
    <div class="modal fade" id="search" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content p-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-magnifying-glass me-2 text-primary"></i>Search Products</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="<?= site_url('shop'); ?>" method="GET" id="autocompleteSearchForm">
                        <div class="input-group input-group-lg mb-3">
                            <input
                                type="text"
                                name="q"
                                id="autocompleteInput"
                                class="form-control"
                                placeholder="Search by product name, SKU, or keywords..."
                                autocomplete="off"
                                required
                                autofocus>
                            <button class="btn btn-primary px-4" type="submit">Search</button>
                        </div>
                    </form>

                    <!-- Autocomplete Dropdown List Container -->
                    <div id="autocompleteResults" class="border rounded p-2 bg-white shadow-sm" style="display: none; max-height: 380px; overflow-y: auto;">
                        <!-- Injected by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var input = document.getElementById('autocompleteInput');
        var resultsBox = document.getElementById('autocompleteResults');
        var timer = null;

        if (input && resultsBox) {
            input.addEventListener('input', function() {
                clearTimeout(timer);
                var q = this.value.trim();
                if (q.length < 2) {
                    resultsBox.style.display = 'none';
                    resultsBox.innerHTML = '';
                    return;
                }

                timer = setTimeout(function() {
                    fetch('<?= site_url("shop/autocomplete"); ?>?q=' + encodeURIComponent(q))
                        .then(function(res) { return res.json(); })
                        .then(function(data) {
                            if (data.results && data.results.length > 0) {
                                var html = '<div class="list-group list-group-flush">';
                                data.results.forEach(function(item) {
                                    html += '<a href="' + item.url + '" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2 px-2">';
                                    html += '<img src="' + item.image + '" class="rounded border" style="width: 46px; height: 46px; object-fit: cover;" onerror="this.src=\'<?= base_url("assets/images/products/womens/women-1.jpg"); ?>\'">';
                                    html += '<div class="flex-grow-1 text-truncate">';
                                    html += '<div class="fw-semibold text-dark text-truncate">' + item.title + '</div>';
                                    html += '<small class="text-muted">' + (item.category_name ? item.category_name + ' • ' : '') + 'SKU: ' + (item.sku || 'N/A') + '</small>';
                                    html += '</div>';
                                    html += '<span class="fw-bold text-primary">' + item.price_html + '</span>';
                                    html += '</a>';
                                });
                                html += '</div>';
                                html += '<div class="text-center pt-2 border-top mt-2"><a href="<?= site_url("shop"); ?>?q=' + encodeURIComponent(q) + '" class="small text-decoration-none fw-bold">View all matching products <i class="fa-solid fa-arrow-right ms-1"></i></a></div>';
                                resultsBox.innerHTML = html;
                                resultsBox.style.display = 'block';
                            } else {
                                resultsBox.innerHTML = '<div class="p-3 text-center text-muted small"><i class="fa-solid fa-circle-question me-1"></i> No matching products found for "<strong>' + q + '</strong>".</div>';
                                resultsBox.style.display = 'block';
                            }
                        })
                        .catch(function() {
                            resultsBox.style.display = 'none';
                        });
                }, 250);
            });
        }
    })();
    </script>

    <!-- Mobile Menu Offcanvas -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="mobileMenuLabel">Menu</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="list-unstyled">
                <li class="py-2 border-bottom"><a href="<?= site_url('home'); ?>" class="text-dark text-decoration-none fw-bold">Home</a></li>
                <li class="py-2 border-bottom"><a href="<?= site_url('shop'); ?>" class="text-dark text-decoration-none fw-bold">Shop All</a></li>
                <li class="py-2 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="<?= site_url('shop'); ?>" class="text-dark text-decoration-none fw-bold">Categories</a>
                        <button class="btn btn-sm btn-link text-secondary p-0" type="button" data-bs-toggle="collapse" data-bs-target="#mobileCatCollapse" aria-expanded="false">
                            <i class="fa-solid fa-chevron-down small"></i>
                        </button>
                    </div>
                    <div class="collapse mt-2 ps-2 border-start" id="mobileCatCollapse">
                        <?php if (!empty($category_tree)): ?>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($category_tree as $m_cat): ?>
                                    <li class="py-1">
                                        <a href="<?= site_url('shop/' . $m_cat['slug']); ?>" class="text-dark text-decoration-none small fw-semibold">
                                            <?= html_escape($m_cat['name']); ?>
                                        </a>
                                        <?php if (!empty($m_cat['children'])): ?>
                                            <ul class="list-unstyled ps-3 my-1">
                                                <?php foreach ($m_cat['children'] as $m_sub): ?>
                                                    <li class="py-1">
                                                        <a href="<?= site_url('shop/' . $m_sub['slug']); ?>" class="text-muted text-decoration-none small">
                                                            &bull; <?= html_escape($m_sub['name']); ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </li>
                <li class="py-2 border-bottom"><a href="<?= site_url('cart'); ?>" class="text-dark text-decoration-none fw-bold">Shopping Cart (<?= $cart_count; ?>)</a></li>
                <?php if ($this->is_logged_in()): 
                    $u_name = trim(($current_user['first_name'] ?? '') . ' ' . ($current_user['last_name'] ?? ''));
                    if (empty($u_name)) $u_name = 'My Account';
                    $u_email = $current_user['email'] ?? '';
                    $u_avatar = (!empty($current_user['avatar']) && $current_user['avatar'] !== 'default-user.png' && file_exists(FCPATH . $current_user['avatar'])) ? base_url($current_user['avatar']) : null;
                ?>
                    <li class="py-3 border-bottom bg-light rounded px-3 my-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0 shadow-sm overflow-hidden" style="width: 44px; height: 44px; background: linear-gradient(135deg, #2874f0, #1b52b3); font-size: 18px;">
                                <?php if (!empty($u_avatar)): ?>
                                    <img src="<?= $u_avatar; ?>" alt="<?= html_escape($u_name); ?>" class="w-100 h-100 object-fit-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                                    <i class="fa-solid fa-user d-none"></i>
                                <?php else: ?>
                                    <i class="fa-solid fa-user"></i>
                                <?php endif; ?>
                            </div>
                            <div class="overflow-hidden" style="min-width: 0;">
                                <div class="text-muted text-uppercase" style="font-size: 10px; letter-spacing: 0.5px; font-weight: 600;">Welcome</div>
                                <div class="fw-bold text-dark text-truncate" style="font-size: 14px;"><?= html_escape($u_name); ?></div>
                                <div class="text-secondary small text-truncate" style="font-size: 11.5px;"><?= html_escape($u_email); ?></div>
                            </div>
                        </div>
                    </li>
                    <li class="py-2 border-bottom"><a href="<?= site_url('account/profile'); ?>" class="text-dark text-decoration-none"><i class="fa-regular fa-user text-primary me-2"></i>My Profile</a></li>
                    <li class="py-2 border-bottom"><a href="<?= site_url('account/orders'); ?>" class="text-dark text-decoration-none"><i class="fa-solid fa-box-archive text-primary me-2"></i>Orders</a></li>
                    <li class="py-2 border-bottom"><a href="<?= site_url('account/address'); ?>" class="text-dark text-decoration-none"><i class="fa-solid fa-location-dot text-primary me-2"></i>Saved Address</a></li>
                    <li class="py-2 border-bottom"><a href="<?= site_url('wishlist'); ?>" class="text-dark text-decoration-none"><i class="fa-regular fa-heart text-primary me-2"></i>Wishlist</a></li>
                    <li class="py-2 border-bottom"><a href="<?= site_url('account/notifications'); ?>" class="text-dark text-decoration-none"><i class="fa-regular fa-bell text-primary me-2"></i>Notification</a></li>
                    <li class="py-2 border-bottom"><a href="<?= site_url('logout'); ?>" class="text-danger text-decoration-none fw-bold"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i>Logout</a></li>
                <?php else: ?>
                    <li class="py-2 border-bottom"><a href="#loginModal" data-bs-toggle="modal" data-bs-dismiss="offcanvas" class="text-dark text-decoration-none fw-bold">Sign In / Register</a></li>
                    <li class="py-2 border-bottom"><a href="#loginModal" data-bs-toggle="modal" data-bs-dismiss="offcanvas" data-redirect-to="<?= site_url('wishlist'); ?>" data-login-message="Please sign in to view your wishlist." onclick="if(typeof openLoginModal==='function'){openLoginModal('<?= site_url('wishlist'); ?>', 'Please sign in to view your wishlist.');}" class="text-dark text-decoration-none"><i class="fa-regular fa-heart text-primary me-2"></i>Wishlist</a></li>
                <?php endif; ?>
                <li class="py-2 border-bottom"><a href="<?= site_url('order/track'); ?>" class="text-dark text-decoration-none fw-bold">Order Tracking</a></li>
                <li class="py-2 border-bottom"><a href="<?= site_url('about'); ?>" class="text-dark text-decoration-none">About Us</a></li>
                <li class="py-2"><a href="<?= site_url('contact'); ?>" class="text-dark text-decoration-none">Contact</a></li>
            </ul>
        </div>
    </div>

    <!-- shoppingCart Side Drawer Modal (Offcanvas Full Right) -->
    <div class="modal fullRight fade modal-shopping-cart <?= !empty($recommended_products) ? 'has-recommendations' : ''; ?>" id="shoppingCart" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <?php if (!empty($recommended_products)): ?>
                <div class="tf-minicart-recommendations d-none d-lg-flex flex-column">
                    <h6 class="title fw-bold">You May Also Like</h6>
                    <div class="wrap-recommendations">
                        <div class="list-cart">
                            <?php foreach ($recommended_products as $rec): 
                                $rec_price = !empty($rec['sale_price']) ? $rec['sale_price'] : $rec['price'];
                                $rec_img = !empty($rec['main_image']) 
                                    ? (strpos($rec['main_image'], 'http') === 0 ? $rec['main_image'] : base_url('assets/images/' . $rec['main_image']))
                                    : base_url('assets/images/products/womens/women-1.jpg');
                            ?>
                            <div class="list-cart-item">
                                <div class="image">
                                    <img src="<?= $rec_img; ?>" alt="<?= html_escape($rec['title']); ?>" onerror="this.src='<?= base_url('assets/images/products/womens/women-1.jpg'); ?>'">
                                </div>
                                <div class="content">
                                    <div class="name">
                                        <a class="link text-line-clamp-1 fw-semibold text-dark text-decoration-none" href="<?= site_url('product/' . $rec['slug']); ?>"><?= html_escape($rec['title']); ?></a>
                                    </div>
                                    <div class="cart-item-bot">
                                        <div class="text-button price"><?= $currency_symbol . number_format($rec_price, 2); ?></div>
                                        <a class="link text-button" href="<?= site_url('product/' . $rec['slug']); ?>">View</a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="d-flex flex-column flex-grow-1 h-100">
                    <div class="header">
                        <h5 class="title fw-bold">Shopping Cart (<span class="side-cart-count"><?= $cart_count; ?></span>)</h5>
                        <span class="icon-close icon-close-popup" data-bs-dismiss="modal" title="Close"></span>
                    </div>
                    <div class="wrap">
                        <div class="tf-mini-cart-wrap">
                            <div class="tf-mini-cart-main">
                                <div class="tf-mini-cart-sroll">
                                    <div class="tf-mini-cart-items" id="side-cart-items-container">
                                        <?php if (!empty($cart_items)): ?>
                                            <?php foreach ($cart_items as $item): 
                                                $item_img = !empty($item['image']) 
                                                    ? (strpos($item['image'], 'http') === 0 ? $item['image'] : base_url('assets/images/' . $item['image']))
                                                    : base_url('assets/images/products/womens/women-1.jpg');
                                                $item_key = $item['cart_key'] ?? $item['id'];
                                            ?>
                                            <div class="tf-mini-cart-item file-delete" id="side-cart-item-<?= html_escape($item_key); ?>">
                                                <div class="tf-mini-cart-image">
                                                    <img src="<?= $item_img; ?>" alt="<?= html_escape($item['title']); ?>" onerror="this.src='<?= base_url('assets/images/products/womens/women-1.jpg'); ?>'">
                                                </div>
                                                <div class="tf-mini-cart-info flex-grow-1" style="min-width: 0;">
                                                    <div class="mb_12 d-flex align-items-center justify-content-between flex-nowrap gap-2">
                                                        <div class="text-title me-2 flex-grow-1 text-truncate" style="min-width: 0;">
                                                            <a href="<?= site_url('product/' . $item['slug']); ?>" class="link fw-semibold text-dark text-decoration-none text-truncate d-block" title="<?= html_escape($item['title']); ?>">
                                                                <?= html_escape($item['title']); ?>
                                                            </a>
                                                        </div>
                                                        <div class="text-button tf-btn-remove remove flex-shrink-0" style="white-space: nowrap;" onclick="removeSideCartItem('<?= html_escape($item_key); ?>')">Remove</div>
                                                    </div>
                                                    <div class="d-flex align-items-center justify-content-between flex-nowrap gap-2">
                                                        <div class="text-secondary-2 small text-truncate me-2" style="min-width: 0;"><?= html_escape($item['variant_title'] ?? ''); ?></div>
                                                        <div class="text-button fw-bold flex-shrink-0" style="white-space: nowrap;"><?= $item['quantity']; ?> × <?= $currency_symbol . number_format($item['price'], 2); ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="text-center py-5 empty-cart-msg" id="side-cart-empty-msg">
                                                <div class="mb-3 text-muted" style="font-size: 48px;">
                                                    <i class="fa-solid fa-basket-shopping"></i>
                                                </div>
                                                <h6 class="fw-bold mb-2">Your cart is currently empty</h6>
                                                <p class="text-muted small mb-4">Discover our top products and start shopping.</p>
                                                <a href="<?= site_url('shop'); ?>" class="tf-btn btn-fill radius-4 px-4 py-2" data-bs-dismiss="modal">
                                                    <span class="text">Explore Products</span>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="tf-mini-cart-bottom" id="side-cart-bottom-bar" style="<?= empty($cart_items) ? 'display: none;' : ''; ?>">
                                    <div class="tf-mini-cart-bottom-wrap">
                                    <div class="tf-cart-totals-discounts">
                                        <h5 class="fw-semibold">Subtotal</h5>
                                        <h5 class="tf-totals-total-value fw-bold text-dark" id="side-cart-subtotal"><?= $currency_symbol . number_format($cart_total, 2); ?></h5>
                                    </div>
                                    <div class="tf-mini-cart-view-checkout">
                                         <a href="<?= site_url('cart'); ?>" class="tf-btn w-100 btn-outline radius-4 text-decoration-none text-center"><span class="text">View cart</span></a>
                                         <a href="<?= site_url('checkout'); ?>" class="tf-btn w-100 btn-fill radius-4 text-decoration-none text-center" onclick="return handleCheckoutClick(event, '<?= site_url('checkout'); ?>')"><span class="text">Check Out</span></a>
                                     </div>
                                     <div class="text-center mt-2">
                                         <a class="link text-btn-uppercase small text-muted text-decoration-none" href="javascript:void(0);" data-bs-dismiss="modal">Or continue shopping</a>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </div>

    <!-- Login / OTP Authentication Side Drawer Modal (Offcanvas Full Right) -->
    <div class="modal fullRight fade modal-login-side" id="loginModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="header">
                    <h5 class="title fw-bold">Sign In / Register</h5>
                    <span class="icon-close icon-close-popup" data-bs-dismiss="modal" title="Close"></span>
                </div>
                
                <div class="modal-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <!-- Subtitle / Instruction -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-1" id="login-modal-step-title">Welcome to <?= html_escape($site_name ?? ($store_settings['site_name'] ?? 'our store')); ?></h6>
                            <p class="text-secondary-2 small mb-0" id="login-modal-step-desc">Enter your Mobile Number or Email to sign in or create an account with a quick OTP.</p>
                        </div>

                        <!-- Alert Box for Error / Success Notifications -->
                        <div id="login-modal-alert" class="alert d-none py-2 px-3 small rounded-3 mb-3"></div>

                        <!-- STEP 1: Phone / Email Input Form -->
                        <div id="login-step-identifier">
                            <form id="otpRequestForm" onsubmit="handleSendOtp(event)">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small mb-1">Mobile Number or Email</label>
                                    <input 
                                        type="text" 
                                        id="login-identifier-input" 
                                        class="form-control rounded-3 py-2 px-3" 
                                        placeholder="e.g. 9876543210 or user@example.com"
                                        autocomplete="username"
                                        required>
                                    <div class="text-muted small mt-1" style="font-size: 11px;">
                                        <i class="fa-solid fa-shield-halved text-success me-1"></i> Quick & secure authentication with OTP.
                                    </div>
                                </div>

                                <button type="submit" id="btn-send-otp" class="tf-btn w-100 btn-fill radius-4 py-2 mt-2">
                                    <span class="text">Continue <i class="fa-solid fa-arrow-right ms-1"></i></span>
                                </button>
                            </form>

                            <div class="text-center my-4 position-relative">
                                <hr class="my-0">
                                <span class="bg-white px-2 text-muted small position-absolute top-50 start-50 translate-middle" style="font-size: 11px;">OR</span>
                            </div>

                            <div class="text-center">
                                <a href="<?= site_url('login'); ?>" class="small text-decoration-underline text-secondary-2">Sign in using password instead</a>
                            </div>
                        </div>

                        <!-- STEP 2 (When User enters Email first): Enter Mobile Number Form -->
                        <div id="login-step-phone-for-email" class="d-none">
                            <div class="bg-surface p-2 rounded-3 mb-3 border d-flex align-items-center justify-content-between">
                                <div class="text-truncate small">
                                    <i class="fa-solid fa-envelope text-primary me-2"></i>
                                    <span class="text-muted" style="font-size: 11px;">Email: </span>
                                    <strong id="email-preview-label" class="text-dark"></strong>
                                </div>
                                <a href="javascript:void(0);" onclick="backToIdentifierStep()" class="small text-primary text-decoration-underline ms-2 flex-shrink-0">Change</a>
                            </div>

                            <form id="phoneForEmailForm" onsubmit="handlePhoneForEmail(event)">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small mb-1">Enter Mobile Number</label>
                                    <input 
                                        type="tel" 
                                        id="login-phone-input" 
                                        class="form-control rounded-3 py-2 px-3" 
                                        placeholder="e.g. 9876543210"
                                        required>
                                    <div class="text-muted small mt-1" style="font-size: 11px;">
                                        <i class="fa-solid fa-shield-halved text-success me-1"></i> We'll send a 6-digit OTP code to verify this phone number.
                                    </div>
                                </div>

                                <button type="submit" id="btn-phone-for-email" class="tf-btn w-100 btn-fill radius-4 py-2 mt-2">
                                    <span class="text">Continue with OTP <i class="fa-solid fa-arrow-right ms-1"></i></span>
                                </button>
                            </form>
                        </div>

                        <!-- STEP 3: 6-Digit OTP Verification Form -->
                        <div id="login-step-otp" class="d-none">
                            <form id="otpVerifyForm" onsubmit="handleVerifyOtp(event)">
                                <div class="bg-surface p-3 rounded-3 mb-3 border">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="text-muted small" style="font-size: 11px;">Verification code sent to</div>
                                            <div class="fw-bold text-dark" id="otp-target-label"></div>
                                            <div id="otp-email-sublabel" class="text-muted small d-none" style="font-size: 11px;"></div>
                                        </div>
                                        <a href="javascript:void(0);" onclick="handleChangeFromOtp()" class="small text-primary text-decoration-underline">Change</a>
                                    </div>
                                    <div id="demo-otp-badge" class="mt-2 py-1 px-2 bg-warning-subtle text-warning-emphasis border border-warning rounded small text-center" style="font-size: 12px; display: none;">
                                        <i class="fa-solid fa-key me-1"></i> Demo OTP: <strong id="demo-otp-code"></strong>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small mb-2 text-center d-block">Enter 6-Digit OTP Code</label>
                                    <div class="d-flex justify-content-center gap-2 mb-2" id="otp-inputs-container">
                                        <input type="text" maxlength="1" class="form-control text-center fw-bold fs-5 otp-digit-input rounded-3" inputmode="numeric" required>
                                        <input type="text" maxlength="1" class="form-control text-center fw-bold fs-5 otp-digit-input rounded-3" inputmode="numeric" required>
                                        <input type="text" maxlength="1" class="form-control text-center fw-bold fs-5 otp-digit-input rounded-3" inputmode="numeric" required>
                                        <input type="text" maxlength="1" class="form-control text-center fw-bold fs-5 otp-digit-input rounded-3" inputmode="numeric" required>
                                        <input type="text" maxlength="1" class="form-control text-center fw-bold fs-5 otp-digit-input rounded-3" inputmode="numeric" required>
                                        <input type="text" maxlength="1" class="form-control text-center fw-bold fs-5 otp-digit-input rounded-3" inputmode="numeric" required>
                                    </div>
                                </div>

                                <button type="submit" id="btn-verify-otp" class="tf-btn w-100 btn-fill radius-4 py-2 mt-3">
                                    <span class="text">Verify & Continue <i class="fa-solid fa-check ms-1"></i></span>
                                </button>

                                <div class="text-center mt-3 small">
                                    <span class="text-muted">Didn't receive the code? </span>
                                    <a href="javascript:void(0);" id="btn-resend-otp" onclick="resendOtp()" class="fw-semibold text-primary text-decoration-none disabled">Resend in <span id="resend-timer-count">30</span>s</a>
                                </div>
                            </form>
                        </div>

                        <!-- STEP 4 (Phone was entered first, Phone is new): Asked for Email and Enter Name -->
                        <div id="login-step-complete-email-name" class="d-none">
                            <div class="alert alert-success py-2 px-3 small rounded-3 mb-3 d-flex align-items-center">
                                <i class="fa-solid fa-circle-check fs-5 me-2 flex-shrink-0 text-success"></i>
                                <div>
                                    <span class="d-block text-muted" style="font-size: 11px;">Mobile number verified:</span>
                                    <strong id="verified-phone-label-1" class="text-dark"></strong>
                                </div>
                            </div>

                            <form id="completeEmailNameForm" onsubmit="handleCompleteEmailName(event)">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small mb-1">Your Full Name <span class="text-danger">*</span></label>
                                    <input 
                                        type="text" 
                                        id="reg-name-1" 
                                        class="form-control rounded-3 py-2 px-3" 
                                        placeholder="e.g. John Doe" 
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small mb-1">Email Address <span class="text-danger">*</span></label>
                                    <input 
                                        type="email" 
                                        id="reg-email-1" 
                                        class="form-control rounded-3 py-2 px-3" 
                                        placeholder="e.g. user@example.com" 
                                        required>
                                    <div class="text-muted small mt-1" style="font-size: 11px;">
                                        Order tracking and invoices will be sent to this email.
                                    </div>
                                </div>

                                <button type="submit" id="btn-complete-1" class="tf-btn w-100 btn-fill radius-4 py-2 mt-2">
                                    <span class="text">Complete Registration & Sign In <i class="fa-solid fa-arrow-right ms-1"></i></span>
                                </button>
                            </form>
                        </div>

                        <!-- STEP 5 (Email was entered first, Phone was new too): Next OTP verified then asked for enter name -->
                        <div id="login-step-complete-name" class="d-none">
                            <div class="bg-surface p-2 rounded-3 mb-3 border small">
                                <div class="d-flex align-items-center text-muted mb-1">
                                    <i class="fa-solid fa-circle-check text-success me-2"></i> Email: <strong id="verified-email-label-2" class="text-dark ms-1"></strong>
                                </div>
                                <div class="d-flex align-items-center text-muted">
                                    <i class="fa-solid fa-circle-check text-success me-2"></i> Mobile: <strong id="verified-phone-label-2" class="text-dark ms-1"></strong>
                                </div>
                            </div>

                            <form id="completeNameForm" onsubmit="handleCompleteName(event)">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small mb-1">Your Full Name <span class="text-danger">*</span></label>
                                    <input 
                                        type="text" 
                                        id="reg-name-2" 
                                        class="form-control rounded-3 py-2 px-3" 
                                        placeholder="e.g. John Doe" 
                                        required>
                                </div>

                                <button type="submit" id="btn-complete-2" class="tf-btn w-100 btn-fill radius-4 py-2 mt-2">
                                    <span class="text">Complete Registration & Sign In <i class="fa-solid fa-arrow-right ms-1"></i></span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="pt-4 border-top text-center">
                        <p class="text-muted small mb-0" style="font-size: 11px;">
                            By continuing, you agree to <?= html_escape($site_name ?? ($store_settings['site_name'] ?? 'our')); ?>'s <a href="<?= site_url('terms'); ?>" class="text-decoration-underline text-dark">Terms of Use</a> and <a href="<?= site_url('privacy'); ?>" class="text-decoration-underline text-dark">Privacy Policy</a>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Address Selection Side Drawer Modal (Offcanvas Full Right) -->
    <div class="modal fullRight fade modal-address-side" id="addressModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="header">
                    <h5 class="title fw-bold"><i class="fa-solid fa-location-dot me-2 text-primary"></i>Delivery Address</h5>
                    <span class="icon-close icon-close-popup" data-bs-dismiss="modal" title="Close"></span>
                </div>

                <div class="modal-body p-4" style="overflow-y: auto;">
                    <!-- Alert Box for notifications -->
                    <div id="address-modal-alert" class="alert d-none py-2 px-3 small rounded-3 mb-3"></div>

                    <!-- Button to Toggle New Address Form -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-primary w-100 py-2 fw-semibold" id="btn-toggle-add-address" onclick="toggleNewAddressForm()">
                            <i class="fa-solid fa-plus me-1"></i> Add a new address
                        </button>
                    </div>

                    <!-- Add New Address Form Container -->
                    <div id="add-address-form-box" class="card border p-3 mb-4 rounded-3 bg-light shadow-sm" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-map-pin me-1"></i> Add New Delivery Address</h6>
                            <button type="button" class="btn-close small" onclick="toggleNewAddressForm(false)"></button>
                        </div>
                        <form id="newAddressForm" onsubmit="handleSaveAddress(event)">
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold mb-1">First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" class="form-control form-control-sm" required placeholder="First Name">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold mb-1">Last Name</label>
                                    <input type="text" name="last_name" class="form-control form-control-sm" placeholder="Last Name">
                                </div>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold mb-1">Mobile Number <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control form-control-sm" required placeholder="10-digit number">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold mb-1">Pincode <span class="text-danger">*</span></label>
                                    <input type="text" name="postcode" class="form-control form-control-sm" required placeholder="6-digit Pincode">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold mb-1">Flat, House No., Building <span class="text-danger">*</span></label>
                                <input type="text" name="address_1" class="form-control form-control-sm" required placeholder="House/Flat No., Apartment">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold mb-1">Area, Street, Sector, Landmark</label>
                                <input type="text" name="address_2" class="form-control form-control-sm" placeholder="Area, Landmark (optional)">
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold mb-1">City / Town <span class="text-danger">*</span></label>
                                    <input type="text" name="city" class="form-control form-control-sm" required placeholder="City">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold mb-1">State <span class="text-danger">*</span></label>
                                    <input type="text" name="state" class="form-control form-control-sm" required placeholder="State">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold d-block mb-1">Address Type</label>
                                <div class="btn-group btn-group-sm w-100" role="group">
                                    <input type="radio" class="btn-check" name="company" id="addr-type-home" value="HOME" checked>
                                    <label class="btn btn-outline-secondary" for="addr-type-home"><i class="fa-solid fa-house me-1"></i> HOME (All day)</label>
                                    
                                    <input type="radio" class="btn-check" name="company" id="addr-type-work" value="WORK">
                                    <label class="btn btn-outline-secondary" for="addr-type-work"><i class="fa-solid fa-briefcase me-1"></i> WORK (10am-5pm)</label>
                                </div>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="is_default" id="addr-is-default" value="1" checked>
                                <label class="form-check-label small" for="addr-is-default">
                                    Make this my default address
                                </label>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm flex-grow-1 py-2 fw-semibold" id="btn-save-addr">
                                    Save and Deliver Here
                                </button>
                                <button type="button" class="btn btn-light btn-sm px-3" onclick="toggleNewAddressForm(false)">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Saved Addresses List Header -->
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase small fw-bold text-muted" style="letter-spacing: 0.5px;">Saved Addresses</span>
                        <span class="badge bg-light text-dark border" id="saved-address-count"><?= !empty($user_addresses) ? count($user_addresses) : (empty($active_address) ? '0' : '1'); ?></span>
                    </div>

                    <!-- Saved Addresses Container -->
                    <div id="saved-addresses-container">
                        <?php 
                            $curr_selected_id = $active_address['id'] ?? null;
                            $addresses_to_show = !empty($user_addresses) ? $user_addresses : (!empty($active_address) ? [$active_address] : []);
                        ?>
                        <?php if (!empty($addresses_to_show)): ?>
                            <?php foreach ($addresses_to_show as $addr): 
                                $is_active = ($curr_selected_id && $addr['id'] == $curr_selected_id);
                                $addr_tag = !empty($addr['company']) ? strtoupper($addr['company']) : 'HOME';
                            ?>
                            <div class="card border rounded-3 p-3 mb-3 address-select-card <?= $is_active ? 'border-primary bg-light' : 'border-light-subtle'; ?>" id="address-card-<?= $addr['id']; ?>" onclick="selectDeliveryAddress('<?= $addr['id']; ?>')">
                                <div class="d-flex align-items-start gap-2">
                                    <input class="form-check-input mt-1 flex-shrink-0" type="radio" name="selected_addr_radio" value="<?= $addr['id']; ?>" <?= $is_active ? 'checked' : ''; ?>>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                            <strong class="text-dark"><?= html_escape($addr['first_name'] . ' ' . ($addr['last_name'] ?? '')); ?></strong>
                                            <span class="badge bg-secondary text-white text-uppercase" style="font-size: 10px;"><?= html_escape($addr_tag); ?></span>
                                            <span class="text-muted small ms-auto fw-bold"><?= html_escape($addr['phone'] ?? ''); ?></span>
                                        </div>
                                        <div class="text-secondary small mb-2">
                                            <?= html_escape(implode(', ', array_filter([$addr['address_1'], $addr['address_2'] ?? '', $addr['city'], $addr['state'] . ' - ' . $addr['postcode']]))); ?>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <button type="button" class="btn btn-sm <?= $is_active ? 'btn-primary' : 'btn-outline-primary'; ?> px-3 py-1 fw-bold" onclick="event.stopPropagation(); selectDeliveryAddress('<?= $addr['id']; ?>')">
                                                <?= $is_active ? '<i class="fa-solid fa-check me-1"></i> Selected' : 'Deliver Here'; ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted small" id="no-addresses-placeholder">
                                <i class="fa-solid fa-location-dot fs-3 mb-2 text-secondary d-block"></i>
                                No saved addresses found.<br>Click "Add a new address" above to set your delivery location.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    .modal.fullRight .modal-dialog {
        transform: translate(100%, 0);
        min-width: 100%;
        height: 100%;
        margin: 0;
        transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }
    .modal.fullRight.show .modal-dialog {
        transform: none !important;
    }
    .modal-login-side .modal-content {
        max-width: 440px !important;
        width: 100% !important;
        height: 100% !important;
        box-shadow: -10px 0 30px rgba(0, 0, 0, 0.15);
        background-color: #ffffff;
        display: flex;
        flex-direction: column;
    }
    .modal-login-side .header {
        padding: 20px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid var(--line);
    }
    .modal-login-side .header .title {
        font-size: 20px;
        margin-bottom: 0;
    }
    .modal-login-side .header .icon-close-popup {
        cursor: pointer;
        font-size: 16px;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background-color: #f3f4f6;
        color: #4b5563;
        transition: all 0.2s ease;
    }
    .modal-login-side .header .icon-close-popup:hover {
        background-color: #181818;
        color: #ffffff;
    }
    .modal-address-side .modal-content {
        max-width: 480px !important;
        width: 100% !important;
        height: 100% !important;
        box-shadow: -10px 0 30px rgba(0, 0, 0, 0.15);
        background-color: #ffffff;
        display: flex;
        flex-direction: column;
    }
    .modal-address-side .header {
        padding: 20px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid var(--line);
    }
    .modal-address-side .header .title {
        font-size: 19px;
        margin-bottom: 0;
    }
    .modal-address-side .header .icon-close-popup {
        cursor: pointer;
        font-size: 16px;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background-color: #f3f4f6;
        color: #4b5563;
        transition: all 0.2s ease;
    }
    .modal-address-side .header .icon-close-popup:hover {
        background-color: #181818;
        color: #ffffff;
    }
    .address-select-card {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .address-select-card:hover {
        border-color: #0c2340 !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    .otp-digit-input {
        width: 44px;
        height: 52px;
        font-size: 20px;
        font-weight: 700;
        text-align: center;
        border: 1px solid var(--line);
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    .otp-digit-input:focus {
        border-color: #181818;
        box-shadow: 0 0 0 3px rgba(24, 24, 24, 0.1);
        outline: none;
    }
    .modal-shopping-cart .modal-content {
        max-width: 480px !important;
        width: 100% !important;
        height: 100% !important;
        box-shadow: -10px 0 30px rgba(0, 0, 0, 0.15);
    }
    @media (min-width: 992px) {
        .modal-shopping-cart.has-recommendations .modal-content {
            max-width: 720px !important;
        }
    }
    @media (max-width: 575px) {
        .modal-shopping-cart .modal-content {
            max-width: 100% !important;
        }
    }
    .modal-shopping-cart .header .icon-close-popup {
        cursor: pointer;
        font-size: 16px;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background-color: #f3f4f6;
        color: #4b5563;
        transition: all 0.2s ease;
    }
    .modal-shopping-cart .header .icon-close-popup:hover {
        background-color: #181818;
        color: #ffffff;
    }
    .modal-shopping-cart .tf-mini-cart-info {
        min-width: 0;
        overflow: hidden;
    }
    .modal-shopping-cart .tf-mini-cart-info .text-title {
        min-width: 0;
        overflow: hidden;
    }
    .modal-shopping-cart .tf-mini-cart-info .text-title .link {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .modal-shopping-cart .tf-btn-remove {
        cursor: pointer;
        font-size: 13px;
        color: #dc3545 !important;
        text-decoration: underline !important;
        white-space: nowrap;
        flex-shrink: 0;
        transition: color 0.2s ease;
    }
    .modal-shopping-cart .tf-btn-remove:hover {
        color: #a71d2a !important;
    }
    .modal-shopping-cart .tf-mini-cart-item .tf-mini-cart-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    .modal-shopping-cart .tf-progress-bar .icon {
        font-size: 13px !important;
    }
    </style>

    <script>
    window.renderSideCart = function(cartItems, cartSummary) {
        var container = document.getElementById('side-cart-items-container');
        var bottomBar = document.getElementById('side-cart-bottom-bar');
        var subtotalEl = document.getElementById('side-cart-subtotal');
        var countBadges = document.querySelectorAll('#cart-counter, .cart-count, .count-box.cart-count, .count-cart, .side-cart-count');
        var currency = '<?= $currency_symbol; ?>';

        var items = Array.isArray(cartItems) ? cartItems : Object.values(cartItems || {});
        var totalCount = (cartSummary && typeof cartSummary.item_count !== 'undefined') 
            ? cartSummary.item_count 
            : items.reduce(function(acc, it) { return acc + (parseInt(it.quantity) || 1); }, 0);
        var subtotal = (cartSummary && typeof cartSummary.subtotal !== 'undefined') 
            ? parseFloat(cartSummary.subtotal) 
            : items.reduce(function(acc, it) { return acc + (parseFloat(it.total) || 0); }, 0);

        // Update count badges
        countBadges.forEach(function(b) {
            b.textContent = totalCount;
        });

        // Update subtotal
        if (subtotalEl) {
            subtotalEl.textContent = currency + subtotal.toFixed(2);
        }

        // Render items or empty message
        if (!container) return;

        if (items.length === 0) {
            container.innerHTML = '<div class="text-center py-5 empty-cart-msg" id="side-cart-empty-msg">' +
                '<div class="mb-3 text-muted" style="font-size: 48px;"><i class="fa-solid fa-basket-shopping"></i></div>' +
                '<h6 class="fw-bold mb-2">Your cart is currently empty</h6>' +
                '<p class="text-muted small mb-4">Discover our top products and start shopping.</p>' +
                '<a href="<?= site_url("shop"); ?>" class="tf-btn btn-fill radius-4 px-4 py-2" data-bs-dismiss="modal">' +
                '<span class="text">Explore Products</span>' +
                '</a>' +
                '</div>';
            if (bottomBar) bottomBar.style.display = 'none';
        } else {
            var html = '';
            items.forEach(function(item) {
                var itemImg = item.image 
                    ? (item.image.indexOf('http') === 0 ? item.image : '<?= base_url("assets/images/"); ?>' + item.image) 
                    : '<?= base_url("assets/images/products/womens/women-1.jpg"); ?>';
                var itemKey = item.cart_key || item.id;
                var variantText = item.variant_title ? '<div class="text-secondary-2 small text-truncate me-2" style="min-width: 0;">' + item.variant_title + '</div>' : '<div class="flex-grow-1"></div>';

                html += '<div class="tf-mini-cart-item file-delete" id="side-cart-item-' + itemKey + '">' +
                    '<div class="tf-mini-cart-image">' +
                        '<img src="' + itemImg + '" alt="' + (item.title || '') + '" onerror="this.src=\'<?= base_url("assets/images/products/womens/women-1.jpg"); ?>\'">' +
                    '</div>' +
                    '<div class="tf-mini-cart-info flex-grow-1" style="min-width: 0;">' +
                        '<div class="mb_12 d-flex align-items-center justify-content-between flex-nowrap gap-2">' +
                            '<div class="text-title me-2 flex-grow-1 text-truncate" style="min-width: 0;">' +
                                '<a href="<?= site_url("product/"); ?>' + (item.slug || '') + '" class="link fw-semibold text-dark text-decoration-none text-truncate d-block" title="' + (item.title || '') + '">' +
                                    (item.title || '') +
                                '</a>' +
                            '</div>' +
                            '<div class="text-button tf-btn-remove remove flex-shrink-0" style="white-space: nowrap;" onclick="removeSideCartItem(\'' + itemKey + '\')">Remove</div>' +
                        '</div>' +
                        '<div class="d-flex align-items-center justify-content-between flex-nowrap gap-2">' +
                            variantText +
                            '<div class="text-button fw-bold flex-shrink-0" style="white-space: nowrap;">' + (item.quantity || 1) + ' × ' + currency + parseFloat(item.price || 0).toFixed(2) + '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>';
            });
            container.innerHTML = html;
            if (bottomBar) bottomBar.style.display = 'block';
        }
    };

    window.removeSideCartItem = function(cartKey) {
        if (!cartKey) return;
        var el = document.getElementById('side-cart-item-' + cartKey);
        if (el) el.style.opacity = '0.5';

        fetch('<?= site_url("cart/remove"); ?>/' + encodeURIComponent(cartKey), {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                renderSideCart(data.cart_items || [], data.cart_summary || {});
            } else if (el) {
                el.style.opacity = '1';
            }
        })
        .catch(function(err) {
            console.error('Error removing item:', err);
            if (el) el.style.opacity = '1';
        });
    };

    window.openSideCartModal = function() {
        var modalEl = document.getElementById('shoppingCart');
        if (!modalEl) return;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else if (typeof $ !== 'undefined') {
            $('#shoppingCart').modal('show');
        }
    };

    // OTP Authentication & Registration Logic
    window.IS_USER_LOGGED_IN = <?= !empty($is_logged_in) ? 'true' : 'false'; ?>;
    window.REDIRECT_AFTER_LOGIN = '<?= site_url("home"); ?>';
    window.CUSTOM_REDIRECT_ACTIVE = false;
    window.AUTH_FLOW_DATA = {
        flow: 'phone_first', // 'phone_first' or 'email_first'
        email: '',
        phone: ''
    };
    var resendInterval = null;

    function resetAuthModalSteps() {
        clearInterval(resendInterval);
        hideLoginAlert();
        window.AUTH_FLOW_DATA = { flow: 'phone_first', email: '', phone: '' };

        var stepIds = [
            'login-step-identifier',
            'login-step-phone-for-email',
            'login-step-otp',
            'login-step-complete-email-name',
            'login-step-complete-name'
        ];
        stepIds.forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.classList.add('d-none');
        });

        var s1 = document.getElementById('login-step-identifier');
        if (s1) s1.classList.remove('d-none');

        var tTitle = document.getElementById('login-modal-step-title');
        var tDesc = document.getElementById('login-modal-step-desc');
        if (tTitle) tTitle.textContent = 'Welcome to <?= html_escape($site_name ?? ($store_settings["site_name"] ?? "our store")); ?>';
        if (tDesc) tDesc.textContent = 'Enter your Mobile Number or Email to sign in or create an account with a quick OTP.';

        var badge = document.getElementById('demo-otp-badge');
        if (badge) badge.style.display = 'none';

        var otpSub = document.getElementById('otp-email-sublabel');
        if (otpSub) otpSub.classList.add('d-none');
    }

    window.handleCheckoutClick = function(e, redirectUrl) {
        if (!window.IS_USER_LOGGED_IN) {
            if (e && e.preventDefault) e.preventDefault();
            if (typeof window.closeGrcCartDrawer === 'function') {
                window.closeGrcCartDrawer();
            }
            if (typeof window.jQuery !== 'undefined') {
                jQuery('.aq-cartmini-active, #grcCartMiniDrawer, .cartmini__area, .cartmini-area').removeClass('opened cartmini-opened active show');
                jQuery('.body-overlay, .cartmini-overlay').removeClass('opened active show');
                if (jQuery('#side-cart').length && typeof jQuery('#side-cart').modal === 'function') {
                    jQuery('#side-cart').modal('hide');
                }
            }
            var drawers = document.querySelectorAll('.aq-cartmini-active, #grcCartMiniDrawer, .cartmini__area, .cartmini-area, .body-overlay, .cartmini-overlay');
            drawers.forEach(function(d) {
                d.classList.remove('opened', 'cartmini-opened', 'active', 'show');
            });
            var sideCartModal = document.getElementById('side-cart');
            if (sideCartModal && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var inst = bootstrap.Modal.getInstance(sideCartModal);
                if (inst) inst.hide();
            }
            var targetUrl = redirectUrl || '<?= site_url("checkout"); ?>';
            if (typeof window.openLoginModal === 'function') {
                window.openLoginModal(targetUrl, 'Please sign in to proceed to checkout.');
            } else {
                window.location.href = '<?= site_url("cart?login=1"); ?>';
            }
            return false;
        }
        return true;
    };

    window.openLoginModal = function(redirectUrl, message) {
        if (redirectUrl) {
            window.REDIRECT_AFTER_LOGIN = redirectUrl;
            window.CUSTOM_REDIRECT_ACTIVE = true;
        } else {
            window.REDIRECT_AFTER_LOGIN = '<?= site_url("home"); ?>';
            window.CUSTOM_REDIRECT_ACTIVE = false;
        }

        resetAuthModalSteps();

        if (message) {
            showLoginAlert('info', message);
        } else {
            hideLoginAlert();
        }

        // Close side cart if open
        var sideCart = document.getElementById('shoppingCart');
        if (sideCart && sideCart.classList.contains('show')) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(sideCart).hide();
            } else if (typeof $ !== 'undefined') {
                $(sideCart).modal('hide');
            }
        }

        // Close address modal if open
        var addrModal = document.getElementById('addressModal');
        if (addrModal && addrModal.classList.contains('show')) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(addrModal).hide();
            } else if (typeof $ !== 'undefined') {
                $(addrModal).modal('hide');
            }
        }

        var loginModal = document.getElementById('loginModal');
        if (loginModal) {
            if (!loginModal.dataset.authListenerAttached) {
                loginModal.dataset.authListenerAttached = 'true';
                loginModal.addEventListener('show.bs.modal', function(event) {
                    var trigger = event ? event.relatedTarget : null;
                    if (trigger && trigger.dataset && trigger.dataset.redirectTo) {
                        window.REDIRECT_AFTER_LOGIN = trigger.dataset.redirectTo;
                        window.CUSTOM_REDIRECT_ACTIVE = true;
                        if (trigger.dataset.loginMessage) {
                            showLoginAlert('info', trigger.dataset.loginMessage);
                        }
                    } else if (!window.CUSTOM_REDIRECT_ACTIVE) {
                        window.REDIRECT_AFTER_LOGIN = '<?= site_url("home"); ?>';
                    }
                });
                loginModal.addEventListener('hidden.bs.modal', function() {
                    window.CUSTOM_REDIRECT_ACTIVE = false;
                    window.REDIRECT_AFTER_LOGIN = '<?= site_url("home"); ?>';
                    resetAuthModalSteps();
                });
            }
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(loginModal).show();
            } else if (typeof $ !== 'undefined') {
                $(loginModal).modal('show');
            }
            setTimeout(function() {
                var inp = document.getElementById('login-identifier-input');
                if (inp && !inp.offsetParent === false) inp.focus();
            }, 300);
        }
    };

    window.handleSendOtp = function(e) {
        if (e && e.preventDefault) e.preventDefault();

        var identifierInput = document.getElementById('login-identifier-input');
        var btnSend = document.getElementById('btn-send-otp');

        var identifier = identifierInput ? identifierInput.value.trim() : '';
        if (!identifier) return;

        hideLoginAlert();

        // Check if user entered an Email address first
        var isEmail = (identifier.indexOf('@') !== -1 && identifier.indexOf('.') !== -1);

        if (isEmail) {
            // Flow: User entered Email first -> Next ask for Mobile Number
            window.AUTH_FLOW_DATA.flow = 'email_first';
            window.AUTH_FLOW_DATA.email = identifier.toLowerCase();
            window.AUTH_FLOW_DATA.phone = '';

            document.getElementById('login-step-identifier').classList.add('d-none');
            document.getElementById('login-step-phone-for-email').classList.remove('d-none');

            document.getElementById('email-preview-label').textContent = window.AUTH_FLOW_DATA.email;
            document.getElementById('login-modal-step-title').textContent = 'Enter Mobile Number';
            document.getElementById('login-modal-step-desc').textContent = 'Please enter your mobile number to link with ' + window.AUTH_FLOW_DATA.email + '.';

            setTimeout(function() {
                var phoneInput = document.getElementById('login-phone-input');
                if (phoneInput) {
                    phoneInput.value = '';
                    phoneInput.focus();
                }
            }, 200);
            return;
        }

        // Flow: User entered Phone Number first
        window.AUTH_FLOW_DATA.flow = 'phone_first';
        window.AUTH_FLOW_DATA.phone = identifier;
        window.AUTH_FLOW_DATA.email = '';

        btnSend.disabled = true;
        btnSend.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending OTP...';

        var formData = new FormData();
        formData.append('identifier', identifier);

        fetch('<?= site_url("auth/send_otp"); ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btnSend.disabled = false;
            btnSend.innerHTML = '<span class="text">Continue <i class="fa-solid fa-arrow-right ms-1"></i></span>';

            if (data.success) {
                window.AUTH_FLOW_DATA.phone = data.phone || identifier;
                document.getElementById('otp-target-label').textContent = window.AUTH_FLOW_DATA.phone;

                var emailSub = document.getElementById('otp-email-sublabel');
                if (emailSub) emailSub.classList.add('d-none');

                var demoBadge = document.getElementById('demo-otp-badge');
                var demoCode = document.getElementById('demo-otp-code');
                if (data.demo_otp && demoBadge && demoCode) {
                    demoCode.textContent = data.demo_otp;
                    demoBadge.style.display = 'block';
                }

                document.getElementById('login-step-identifier').classList.add('d-none');
                document.getElementById('login-step-otp').classList.remove('d-none');
                document.getElementById('login-modal-step-title').textContent = 'Verify OTP';
                document.getElementById('login-modal-step-desc').textContent = 'Enter the 6-digit code sent to verify your identity.';

                startResendTimer();
                initOtpInputs();
            } else {
                showLoginAlert('danger', data.message || 'Failed to send OTP. Please try again.');
            }
        })
        .catch(function(err) {
            console.error('Error sending OTP:', err);
            btnSend.disabled = false;
            btnSend.innerHTML = '<span class="text">Continue <i class="fa-solid fa-arrow-right ms-1"></i></span>';
            showLoginAlert('danger', 'An error occurred. Please try again.');
        });
    };

    window.handlePhoneForEmail = function(e) {
        if (e && e.preventDefault) e.preventDefault();

        var phoneInput = document.getElementById('login-phone-input');
        var btnPhone = document.getElementById('btn-phone-for-email');

        var phone = phoneInput ? phoneInput.value.trim() : '';
        if (!phone) return;

        btnPhone.disabled = true;
        btnPhone.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending OTP...';
        hideLoginAlert();

        var formData = new FormData();
        formData.append('identifier', phone);
        formData.append('email', window.AUTH_FLOW_DATA.email || '');

        fetch('<?= site_url("auth/send_otp"); ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btnPhone.disabled = false;
            btnPhone.innerHTML = '<span class="text">Continue with OTP <i class="fa-solid fa-arrow-right ms-1"></i></span>';

            if (data.success) {
                window.AUTH_FLOW_DATA.phone = data.phone || phone;
                document.getElementById('otp-target-label').textContent = window.AUTH_FLOW_DATA.phone;

                var emailSub = document.getElementById('otp-email-sublabel');
                if (emailSub && window.AUTH_FLOW_DATA.email) {
                    emailSub.textContent = 'Email: ' + window.AUTH_FLOW_DATA.email;
                    emailSub.classList.remove('d-none');
                }

                var demoBadge = document.getElementById('demo-otp-badge');
                var demoCode = document.getElementById('demo-otp-code');
                if (data.demo_otp && demoBadge && demoCode) {
                    demoCode.textContent = data.demo_otp;
                    demoBadge.style.display = 'block';
                }

                document.getElementById('login-step-phone-for-email').classList.add('d-none');
                document.getElementById('login-step-otp').classList.remove('d-none');
                document.getElementById('login-modal-step-title').textContent = 'Verify Mobile OTP';
                document.getElementById('login-modal-step-desc').textContent = 'Enter the 6-digit code sent to verify ' + window.AUTH_FLOW_DATA.phone + '.';

                startResendTimer();
                initOtpInputs();
            } else {
                showLoginAlert('danger', data.message || 'Failed to send OTP. Please try again.');
            }
        })
        .catch(function(err) {
            console.error('Error sending OTP for phone:', err);
            btnPhone.disabled = false;
            btnPhone.innerHTML = '<span class="text">Continue with OTP <i class="fa-solid fa-arrow-right ms-1"></i></span>';
            showLoginAlert('danger', 'An error occurred. Please try again.');
        });
    };

    window.handleVerifyOtp = function(e) {
        if (e && e.preventDefault) e.preventDefault();

        var phone = window.AUTH_FLOW_DATA.phone || document.getElementById('otp-target-label').textContent.trim();
        var inputs = document.querySelectorAll('.otp-digit-input');
        var otpCode = '';
        inputs.forEach(function(inp) { otpCode += inp.value.trim(); });

        if (otpCode.length < 6) {
            showLoginAlert('warning', 'Please enter all 6 digits of the verification code.');
            return;
        }

        var btnVerify = document.getElementById('btn-verify-otp');
        btnVerify.disabled = true;
        btnVerify.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifying...';
        hideLoginAlert();

        var formData = new FormData();
        formData.append('phone', phone);
        formData.append('otp', otpCode);
        formData.append('redirect_to', window.REDIRECT_AFTER_LOGIN || '<?= site_url("home"); ?>');

        fetch('<?= site_url("auth/verify_otp"); ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btnVerify.disabled = false;
            btnVerify.innerHTML = '<span class="text">Verify & Continue <i class="fa-solid fa-check ms-1"></i></span>';

            if (data.success) {
                if (data.status === 'logged_in') {
                    // Existing User -> Immediate Login!
                    showLoginAlert('success', data.message || 'Login successful!');
                    window.IS_USER_LOGGED_IN = true;
                    setTimeout(function() {
                        window.location.href = data.redirect || window.REDIRECT_AFTER_LOGIN;
                    }, 600);
                } else if (data.status === 'need_email_name') {
                    // Phone was entered first & Phone is NEW -> Asked for Email and Enter Name
                    document.getElementById('login-step-otp').classList.add('d-none');
                    document.getElementById('login-step-complete-email-name').classList.remove('d-none');
                    document.getElementById('verified-phone-label-1').textContent = data.phone || phone;
                    document.getElementById('login-modal-step-title').textContent = 'Complete Your Profile';
                    document.getElementById('login-modal-step-desc').textContent = 'Enter your name and email address to finish registration.';

                    setTimeout(function() {
                        var inp = document.getElementById('reg-name-1');
                        if (inp) inp.focus();
                    }, 200);
                } else if (data.status === 'need_name') {
                    // Email entered first, Phone was NEW too -> OTP verified -> Asked for Enter Name
                    document.getElementById('login-step-otp').classList.add('d-none');
                    document.getElementById('login-step-complete-name').classList.remove('d-none');
                    document.getElementById('verified-email-label-2').textContent = data.email || window.AUTH_FLOW_DATA.email;
                    document.getElementById('verified-phone-label-2').textContent = data.phone || phone;
                    document.getElementById('login-modal-step-title').textContent = 'What Should We Call You?';
                    document.getElementById('login-modal-step-desc').textContent = 'Enter your name to complete your registration.';

                    setTimeout(function() {
                        var inp = document.getElementById('reg-name-2');
                        if (inp) inp.focus();
                    }, 200);
                }
            } else {
                showLoginAlert('danger', data.message || 'Invalid code. Please try again.');
            }
        })
        .catch(function(err) {
            console.error('Error verifying OTP:', err);
            btnVerify.disabled = false;
            btnVerify.innerHTML = '<span class="text">Verify & Continue <i class="fa-solid fa-check ms-1"></i></span>';
            showLoginAlert('danger', 'Verification error. Please try again.');
        });
    };

    window.handleCompleteEmailName = function(e) {
        if (e && e.preventDefault) e.preventDefault();

        var nameInput = document.getElementById('reg-name-1');
        var emailInput = document.getElementById('reg-email-1');
        var btn = document.getElementById('btn-complete-1');

        var name = nameInput ? nameInput.value.trim() : '';
        var email = emailInput ? emailInput.value.trim() : '';

        if (!name) {
            showLoginAlert('warning', 'Please enter your name.');
            return;
        }
        if (!email || email.indexOf('@') === -1) {
            showLoginAlert('warning', 'Please enter a valid email address.');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating Account...';
        hideLoginAlert();

        var formData = new FormData();
        formData.append('name', name);
        formData.append('email', email);
        formData.append('redirect_to', window.REDIRECT_AFTER_LOGIN || '<?= site_url("home"); ?>');

        fetch('<?= site_url("auth/complete_registration"); ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.innerHTML = '<span class="text">Complete Registration & Sign In <i class="fa-solid fa-arrow-right ms-1"></i></span>';

            if (data.success) {
                showLoginAlert('success', data.message || 'Registration successful! Redirecting...');
                window.IS_USER_LOGGED_IN = true;
                setTimeout(function() {
                    window.location.href = data.redirect || window.REDIRECT_AFTER_LOGIN;
                }, 600);
            } else {
                showLoginAlert('danger', data.message || 'Registration failed. Please try again.');
            }
        })
        .catch(function(err) {
            console.error('Error completing registration:', err);
            btn.disabled = false;
            btn.innerHTML = '<span class="text">Complete Registration & Sign In <i class="fa-solid fa-arrow-right ms-1"></i></span>';
            showLoginAlert('danger', 'An error occurred during registration. Please try again.');
        });
    };

    window.handleCompleteName = function(e) {
        if (e && e.preventDefault) e.preventDefault();

        var nameInput = document.getElementById('reg-name-2');
        var btn = document.getElementById('btn-complete-2');

        var name = nameInput ? nameInput.value.trim() : '';
        if (!name) {
            showLoginAlert('warning', 'Please enter your name.');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating Account...';
        hideLoginAlert();

        var formData = new FormData();
        formData.append('name', name);
        formData.append('email', window.AUTH_FLOW_DATA.email || '');
        formData.append('redirect_to', window.REDIRECT_AFTER_LOGIN || '<?= site_url("home"); ?>');

        fetch('<?= site_url("auth/complete_registration"); ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.innerHTML = '<span class="text">Complete Registration & Sign In <i class="fa-solid fa-arrow-right ms-1"></i></span>';

            if (data.success) {
                showLoginAlert('success', data.message || 'Registration successful! Redirecting...');
                window.IS_USER_LOGGED_IN = true;
                setTimeout(function() {
                    window.location.href = data.redirect || window.REDIRECT_AFTER_LOGIN;
                }, 600);
            } else {
                showLoginAlert('danger', data.message || 'Registration failed. Please try again.');
            }
        })
        .catch(function(err) {
            console.error('Error completing registration:', err);
            btn.disabled = false;
            btn.innerHTML = '<span class="text">Complete Registration & Sign In <i class="fa-solid fa-arrow-right ms-1"></i></span>';
            showLoginAlert('danger', 'An error occurred during registration. Please try again.');
        });
    };

    window.handleChangeFromOtp = function() {
        clearInterval(resendInterval);
        hideLoginAlert();
        document.getElementById('login-step-otp').classList.add('d-none');

        if (window.AUTH_FLOW_DATA && window.AUTH_FLOW_DATA.flow === 'email_first') {
            document.getElementById('login-step-phone-for-email').classList.remove('d-none');
            document.getElementById('login-modal-step-title').textContent = 'Enter Mobile Number';
            document.getElementById('login-modal-step-desc').textContent = 'Please enter your mobile number to link with ' + window.AUTH_FLOW_DATA.email + '.';
            setTimeout(function() {
                var phoneInp = document.getElementById('login-phone-input');
                if (phoneInp) phoneInp.focus();
            }, 200);
        } else {
            backToIdentifierStep();
        }
    };

    window.backToIdentifierStep = function() {
        clearInterval(resendInterval);
        hideLoginAlert();

        var stepIds = [
            'login-step-phone-for-email',
            'login-step-otp',
            'login-step-complete-email-name',
            'login-step-complete-name'
        ];
        stepIds.forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.classList.add('d-none');
        });

        document.getElementById('login-step-identifier').classList.remove('d-none');
        document.getElementById('login-modal-step-title').textContent = 'Welcome to <?= html_escape($site_name ?? ($store_settings['site_name'] ?? 'our store')); ?>';
        document.getElementById('login-modal-step-desc').textContent = 'Enter your Mobile Number or Email to sign in or create an account with a quick OTP.';

        setTimeout(function() {
            var inp = document.getElementById('login-identifier-input');
            if (inp) inp.focus();
        }, 200);
    };

    window.resendOtp = function() {
        var btn = document.getElementById('btn-resend-otp');
        if (btn.classList.contains('disabled')) return;
        var identifier = window.AUTH_FLOW_DATA.phone || document.getElementById('otp-target-label').textContent.trim();
        if (!identifier) return;

        var formData = new FormData();
        formData.append('identifier', identifier);
        if (window.AUTH_FLOW_DATA.email) {
            formData.append('email', window.AUTH_FLOW_DATA.email);
        }

        btn.classList.add('disabled');
        btn.textContent = 'Sending...';

        fetch('<?= site_url("auth/send_otp"); ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                if (data.demo_otp) {
                    var demoBadge = document.getElementById('demo-otp-badge');
                    var demoCode = document.getElementById('demo-otp-code');
                    if (demoBadge && demoCode) {
                        demoCode.textContent = data.demo_otp;
                        demoBadge.style.display = 'block';
                    }
                }
                showLoginAlert('success', 'A new OTP has been sent.');
                startResendTimer();
            } else {
                showLoginAlert('danger', data.message || 'Failed to resend OTP.');
                btn.classList.remove('disabled');
                btn.textContent = 'Resend OTP';
            }
        });
    };

    function startResendTimer() {
        var btn = document.getElementById('btn-resend-otp');
        if (!btn) return;
        var count = 30;

        btn.classList.add('disabled');
        btn.innerHTML = 'Resend in <span id="resend-timer-count">' + count + '</span>s';
        clearInterval(resendInterval);

        resendInterval = setInterval(function() {
            count--;
            var countEl = document.getElementById('resend-timer-count');
            if (countEl) countEl.textContent = count;
            if (count <= 0) {
                clearInterval(resendInterval);
                btn.classList.remove('disabled');
                btn.textContent = 'Resend OTP';
            }
        }, 1000);
    }

    function initOtpInputs() {
        var inputs = document.querySelectorAll('.otp-digit-input');
        inputs.forEach(function(input, index) {
            input.value = '';
            input.oninput = function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length >= 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            };
            input.onkeydown = function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    inputs[index - 1].focus();
                }
            };
            input.onpaste = function(e) {
                e.preventDefault();
                var pasted = (e.clipboardData || window.clipboardData).getData('text').trim().replace(/[^0-9]/g, '');
                if (pasted) {
                    for (var i = 0; i < inputs.length && i < pasted.length; i++) {
                        inputs[i].value = pasted[i];
                    }
                    var focusIdx = Math.min(pasted.length, inputs.length) - 1;
                    if (focusIdx >= 0) inputs[focusIdx].focus();
                }
            };
        });
        if (inputs.length > 0) {
            setTimeout(function() { inputs[0].focus(); }, 200);
        }
    }

    function showLoginAlert(type, msg) {
        var alertBox = document.getElementById('login-modal-alert');
        if (!alertBox) return;
        var icon = (type === 'success') ? 'fa-circle-check' : ((type === 'info') ? 'fa-circle-info' : 'fa-triangle-exclamation');
        alertBox.className = 'alert alert-' + type + ' py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2';
        alertBox.innerHTML = '<i class="fa-solid ' + icon + ' flex-shrink-0"></i> <span>' + msg + '</span>';
        alertBox.classList.remove('d-none');
    }

    function hideLoginAlert() {
        var alertBox = document.getElementById('login-modal-alert');
        if (alertBox) alertBox.classList.add('d-none');
    }

    // ==========================================
    // Delivery Address Side Drawer Functions
    // ==========================================
    window.openAddressModal = function() {
        if (!window.IS_USER_LOGGED_IN) {
            window.openLoginModal('<?= site_url("cart"); ?>', 'Please sign in to select or add your delivery address.');
            return;
        }
        var modalEl = document.getElementById('addressModal');
        if (!modalEl) return;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else if (typeof $ !== 'undefined') {
            $('#addressModal').modal('show');
        }
    };

    window.toggleNewAddressForm = function(forceState) {
        var formBox = document.getElementById('add-address-form-box');
        if (!formBox) return;
        if (typeof forceState === 'boolean') {
            formBox.style.display = forceState ? 'block' : 'none';
        } else {
            formBox.style.display = (formBox.style.display === 'none' || formBox.style.display === '') ? 'block' : 'none';
        }
        if (formBox.style.display === 'block') {
            var firstInp = formBox.querySelector('input[name="first_name"]');
            if (firstInp) setTimeout(function() { firstInp.focus(); }, 100);
        }
    };

    window.selectDeliveryAddress = function(addrId) {
        var formData = new FormData();
        formData.append('address_id', addrId);

        fetch('<?= site_url("checkout/select_address"); ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success && data.address) {
                updateDeliveryAddressUI(data.address);
                var modalEl = document.getElementById('addressModal');
                if (modalEl) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    } else if (typeof $ !== 'undefined') {
                        $(modalEl).modal('hide');
                    }
                }
            } else {
                showAddressAlert('danger', data.message || 'Failed to select address.');
            }
        })
        .catch(function(err) {
            console.error('Error selecting address:', err);
        });
    };

    window.handleSaveAddress = function(e) {
        if (e && e.preventDefault) e.preventDefault();
        var form = document.getElementById('newAddressForm');
        var btn = document.getElementById('btn-save-addr');
        if (!form || !btn) return;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
        hideAddressAlert();

        var formData = new FormData(form);

        fetch('<?= site_url("checkout/save_address"); ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.innerHTML = 'Save and Deliver Here';

            if (data.success && data.address) {
                updateDeliveryAddressUI(data.address);
                form.reset();
                toggleNewAddressForm(false);
                refreshAddressList();
                var modalEl = document.getElementById('addressModal');
                if (modalEl) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    } else if (typeof $ !== 'undefined') {
                        $(modalEl).modal('hide');
                    }
                }
            } else {
                showAddressAlert('danger', data.message || 'Failed to save address.');
            }
        })
        .catch(function(err) {
            console.error('Error saving address:', err);
            btn.disabled = false;
            btn.innerHTML = 'Save and Deliver Here';
            showAddressAlert('danger', 'An error occurred while saving address.');
        });
    };

    window.updateDeliveryAddressUI = function(addr) {
        if (!addr) return;
        var fullName = (addr.first_name + ' ' + (addr.last_name || '')).trim();
        var tag = (addr.company || 'HOME').toUpperCase();
        var fullAddr = [addr.address_1, addr.address_2, addr.city, (addr.state ? addr.state + ' - ' + addr.postcode : addr.postcode)].filter(Boolean).join(', ');
        var phone = addr.phone || '';

        // Update Cart & Checkout Page Delivery Cards
        var cartName = document.getElementById('display-address-name');
        if (cartName) cartName.textContent = fullName + (addr.postcode ? ', ' + addr.postcode : '');
        var cartTag = document.getElementById('display-address-tag');
        if (cartTag) cartTag.textContent = tag;
        var cartFull = document.getElementById('display-address-full');
        if (cartFull) cartFull.textContent = fullAddr;
        var cartPhone = document.getElementById('display-address-phone');
        if (cartPhone) cartPhone.textContent = phone;
        var cartPhoneWrap = document.getElementById('display-address-phone-wrap');
        if (cartPhoneWrap) cartPhoneWrap.style.display = phone ? '' : 'none';

        // Update Checkout Page Delivery Card toggle boxes
        var boxSelected = document.getElementById('address-box-selected');
        var boxEmpty = document.getElementById('address-box-empty');
        if (boxSelected) {
            boxSelected.classList.remove('d-none');
            boxSelected.classList.add('d-flex');
        }
        if (boxEmpty) {
            boxEmpty.classList.add('d-none');
            boxEmpty.classList.remove('d-flex');
        }

        var chkName = document.getElementById('checkout-step-address-name');
        if (chkName) chkName.textContent = fullName;
        var chkTag = document.getElementById('checkout-step-address-tag');
        if (chkTag) chkTag.textContent = tag;
        var chkPin = document.getElementById('checkout-step-address-pin');
        if (chkPin) chkPin.textContent = addr.postcode || '';
        var chkFull = document.getElementById('checkout-step-address-full');
        if (chkFull) chkFull.textContent = fullAddr;
        var chkPhone = document.getElementById('checkout-step-address-phone');
        if (chkPhone) chkPhone.textContent = phone;
        var chkPhoneWrap = document.getElementById('checkout-step-address-phone-wrap');
        if (chkPhoneWrap) chkPhoneWrap.style.display = phone ? '' : 'none';

        // Update Top Stepper 1 state to completed checkmark (Black style)
        var topStep1 = document.getElementById('top-stepper-1');
        var topText1 = document.getElementById('top-stepper-text-1');
        var topStepLine1 = document.getElementById('top-stepper-line-1');
        if (topStep1) {
            topStep1.innerHTML = '<i class="fa-solid fa-check"></i>';
            topStep1.className = 'rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold';
            topStep1.style.width = '26px';
            topStep1.style.height = '26px';
            topStep1.style.fontSize = '13px';
            topStep1.style.backgroundColor = '#000';
        }
        if (topText1) {
            topText1.className = 'small fw-semibold text-dark';
        }
        if (topStepLine1) {
            topStepLine1.style.backgroundColor = '#000';
        }

        // Update hidden inputs if present
        ['first_name', 'last_name', 'phone', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country'].forEach(function(field) {
            var inp = document.querySelector('input[name="' + field + '"]');
            if (inp && addr[field]) inp.value = addr[field];
        });

        // Mark radio card as active in modal
        document.querySelectorAll('.address-select-card').forEach(function(card) {
            card.classList.remove('border-primary', 'bg-light');
            card.classList.add('border-light-subtle');
            var r = card.querySelector('input[type="radio"]');
            if (r) r.checked = false;
            var b = card.querySelector('button');
            if (b) {
                b.className = 'btn btn-sm btn-outline-primary px-3 py-1 fw-bold';
                b.textContent = 'Deliver Here';
            }
        });
        var activeCard = document.getElementById('address-card-' + addr.id);
        if (activeCard) {
            activeCard.classList.remove('border-light-subtle');
            activeCard.classList.add('border-primary', 'bg-light');
            var rad = activeCard.querySelector('input[type="radio"]');
            if (rad) rad.checked = true;
            var btn = activeCard.querySelector('button');
            if (btn) {
                btn.className = 'btn btn-sm btn-primary px-3 py-1 fw-bold';
                btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Selected';
            }
        }
    };

    function showAddressAlert(type, msg) {
        var a = document.getElementById('address-modal-alert');
        if (!a) return;
        a.className = 'alert alert-' + type + ' py-2 px-3 small rounded-3 mb-3';
        a.textContent = msg;
        a.classList.remove('d-none');
    }

    function hideAddressAlert() {
        var a = document.getElementById('address-modal-alert');
        if (a) a.classList.add('d-none');
    }

    function refreshAddressList() {
        fetch('<?= site_url("checkout/get_addresses"); ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success && data.addresses) {
                var container = document.getElementById('saved-addresses-container');
                var countBadge = document.getElementById('saved-address-count');
                if (countBadge) countBadge.textContent = data.addresses.length;
                if (!container) return;

                if (data.addresses.length === 0) {
                    container.innerHTML = '<div class="text-center py-4 text-muted small" id="no-addresses-placeholder"><i class="fa-solid fa-location-dot fs-3 mb-2 text-secondary d-block"></i>No saved addresses found.<br>Click "Add a new address" above to set your delivery location.</div>';
                    return;
                }

                var html = '';
                data.addresses.forEach(function(addr) {
                    var isAct = (data.selected_id && addr.id == data.selected_id);
                    var tag = (addr.company || 'HOME').toUpperCase();
                    var full = [addr.address_1, addr.address_2, addr.city, (addr.state ? addr.state + ' - ' + addr.postcode : addr.postcode)].filter(Boolean).join(', ');
                    var name = (addr.first_name + ' ' + (addr.last_name || '')).trim();

                    html += '<div class="card border rounded-3 p-3 mb-3 address-select-card ' + (isAct ? 'border-primary bg-light' : 'border-light-subtle') + '" id="address-card-' + addr.id + '" onclick="selectDeliveryAddress(\'' + addr.id + '\')">';
                    html += '<div class="d-flex align-items-start gap-2">';
                    html += '<input class="form-check-input mt-1 flex-shrink-0" type="radio" name="selected_addr_radio" value="' + addr.id + '" ' + (isAct ? 'checked' : '') + '>';
                    html += '<div class="flex-grow-1">';
                    html += '<div class="d-flex align-items-center gap-2 mb-1 flex-wrap">';
                    html += '<strong class="text-dark">' + name + '</strong>';
                    html += '<span class="badge bg-secondary text-white text-uppercase" style="font-size: 10px;">' + tag + '</span>';
                    html += '<span class="text-muted small ms-auto fw-bold">' + (addr.phone || '') + '</span>';
                    html += '</div>';
                    html += '<div class="text-secondary small mb-2">' + full + '</div>';
                    html += '<div class="d-flex justify-content-between align-items-center">';
                    html += '<button type="button" class="btn btn-sm ' + (isAct ? 'btn-primary' : 'btn-outline-primary') + ' px-3 py-1 fw-bold" onclick="event.stopPropagation(); selectDeliveryAddress(\'' + addr.id + '\')">';
                    html += isAct ? '<i class="fa-solid fa-check me-1"></i> Selected' : 'Deliver Here';
                    html += '</button></div></div></div></div>';
                });
                container.innerHTML = html;
            }
        })
        .catch(function(err) {
            console.error('Error refreshing addresses:', err);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        var modalEl = document.getElementById('loginModal');
        if (modalEl) {
            if (!modalEl.dataset.authListenerAttached) {
                modalEl.dataset.authListenerAttached = 'true';
                modalEl.addEventListener('show.bs.modal', function(event) {
                    var trigger = event ? event.relatedTarget : null;
                    if (trigger && trigger.dataset && trigger.dataset.redirectTo) {
                        window.REDIRECT_AFTER_LOGIN = trigger.dataset.redirectTo;
                        window.CUSTOM_REDIRECT_ACTIVE = true;
                        if (trigger.dataset.loginMessage && typeof showLoginAlert === 'function') {
                            showLoginAlert('info', trigger.dataset.loginMessage);
                        }
                    } else if (!window.CUSTOM_REDIRECT_ACTIVE) {
                        window.REDIRECT_AFTER_LOGIN = '<?= site_url("home"); ?>';
                    }
                });
                modalEl.addEventListener('hidden.bs.modal', function() {
                    window.CUSTOM_REDIRECT_ACTIVE = false;
                    window.REDIRECT_AFTER_LOGIN = '<?= site_url("home"); ?>';
                    if (typeof resetAuthModalSteps === 'function') {
                        resetAuthModalSteps();
                    }
                });
            }
        }

        // Global interceptor: When any guest user clicks a wishlist heart or button anywhere in the project
        document.addEventListener('click', function(e) {
            var wishlistBtn = e.target.closest('.box-icon.wishlist, .btn-icon-action.wishlist, a[href*="wishlist/toggle"], .store-nav-wishlist > a[href="#loginModal"]');
            if (wishlistBtn && !window.IS_USER_LOGGED_IN) {
                e.preventDefault();
                e.stopPropagation();
                if (typeof window.openLoginModal === 'function') {
                    window.openLoginModal('<?= site_url("wishlist"); ?>', 'Please sign in to view and save items to your wishlist.');
                }
            }
        });
    });
    </script>
