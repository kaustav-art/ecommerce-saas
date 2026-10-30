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
                    <li class="py-2 border-bottom"><a href="#exampleModalToggle" data-bs-toggle="modal" data-bs-dismiss="offcanvas" class="text-dark text-decoration-none fw-bold">Sign In / Register</a></li>
                    <li class="py-2 border-bottom"><a href="#exampleModalToggle" data-bs-toggle="modal" data-bs-dismiss="offcanvas" data-redirect-to="<?= site_url('wishlist'); ?>" data-login-message="Please sign in to view your wishlist." onclick="if(typeof openLoginModal==='function'){openLoginModal('<?= site_url('wishlist'); ?>', 'Please sign in to view your wishlist.');}" class="text-dark text-decoration-none"><i class="fa-regular fa-heart text-primary me-2"></i>Wishlist</a></li>
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
    </script>
