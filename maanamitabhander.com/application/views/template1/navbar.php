        <!-- Header -->
        <header id="header" class="header-default store-header-nav">
            <!-- Top Navbar Row -->
            <div class="store-top-navbar bg-white border-bottom">
                <div class="container px-3 px-lg-4">
                    <div class="store-nav-header-row d-flex flex-wrap align-items-center justify-content-between py-2">

                        <!-- Brand Logo -->
                        <div class="store-logo-col flex-shrink-0 order-1">
                            <a href="<?= site_url('home'); ?>" class="logo-header d-inline-block">
                                <?php
                                  $web_logo = !empty($store_settings['site_logo'])
                                    ? base_url('assets/images/logo/' . $store_settings['site_logo'])
                                    : base_url('assets/images/logo/logo.webp');
                                ?>
                                <img src="<?= $web_logo; ?>" alt="<?= html_escape($site_name ?? ($store_settings['site_name'] ?? 'Store')); ?>" class="logo" style="max-height: 42px; width: auto; object-fit: contain;" onerror="this.src='<?= base_url('assets/images/logo/logo.webp'); ?>'">
                            </a>
                        </div>

                        <!-- Right Actions (Login / User Account, Wishlist & Cart) -->
                        <div class="store-actions-col flex-shrink-0 d-flex align-items-center gap-3 gap-md-4 order-2 order-md-3">
                            
                            <!-- User Account (Always shows After-Login Dropdown Menu in Template Mode) -->
                            <div class="store-nav-account position-relative">
                                <?php
                                    $u_first = $current_user['first_name'] ?? 'Alex';
                                    $u_name  = trim(($current_user['first_name'] ?? 'Alex') . ' ' . ($current_user['last_name'] ?? 'Morgan'));
                                    $u_email = $current_user['email'] ?? 'alex.morgan@example.com';
                                ?>
                                <a href="<?= site_url('account/profile'); ?>" class="store-nav-action-link d-flex align-items-center text-decoration-none" title="<?= html_escape($u_name); ?>">
                                    <i class="fa-regular fa-circle-user store-action-icon"></i>
                                    <span class="store-action-text d-none d-md-inline"><?= html_escape($u_first); ?></span>
                                    <i class="fa-solid fa-chevron-down store-action-chevron d-none d-md-inline"></i>
                                </a>

                                <!-- User Account Drawer Menu (Opens on hover) -->
                                <div class="after-login-drawer-menu shadow-lg">
                                    <div class="drawer-user-header p-3 border-bottom d-flex align-items-center">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0 shadow-sm overflow-hidden me-2" style="width: 40px; height: 40px; background: linear-gradient(135deg, #2874f0, #1b52b3); font-size: 16px; font-weight: bold;">
                                            <?= strtoupper(substr($u_first, 0, 1)); ?>
                                        </div>
                                        <div class="overflow-hidden" style="min-width: 0;">
                                            <div class="text-muted text-uppercase" style="font-size: 10.5px; letter-spacing: 0.5px; font-weight: 600;">Welcome</div>
                                            <div class="fw-bold text-dark text-truncate" style="font-size: 14px;" title="<?= html_escape($u_name); ?>">
                                                <?= html_escape($u_name); ?>
                                            </div>
                                            <div class="text-muted small text-truncate" style="font-size: 11.5px;" title="<?= html_escape($u_email); ?>">
                                                <?= html_escape($u_email); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="py-2">
                                        <a class="dropdown-item py-2 px-3 d-flex align-items-center" href="<?= site_url('account/profile'); ?>"><i class="fa-regular fa-user text-primary me-3" style="width: 16px;"></i>My Profile</a>
                                        <a class="dropdown-item py-2 px-3 d-flex align-items-center" href="<?= site_url('account/orders'); ?>"><i class="fa-solid fa-box-archive text-primary me-3" style="width: 16px;"></i>Orders</a>
                                        <a class="dropdown-item py-2 px-3 d-flex align-items-center" href="<?= site_url('account/address'); ?>"><i class="fa-solid fa-location-dot text-primary me-3" style="width: 16px;"></i>Saved Address</a>
                                        <a class="dropdown-item py-2 px-3 d-flex align-items-center" href="<?= site_url('wishlist'); ?>"><i class="fa-regular fa-heart text-primary me-3" style="width: 16px;"></i>Wishlist</a>
                                        <a class="dropdown-item py-2 px-3 d-flex align-items-center" href="<?= site_url('account/notifications'); ?>"><i class="fa-regular fa-bell text-primary me-3" style="width: 16px;"></i>Notification</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Wishlist -->
                            <div class="store-nav-wishlist">
                                <a href="<?= site_url('wishlist'); ?>" class="store-nav-action-link d-flex align-items-center text-decoration-none position-relative" title="Wishlist">
                                    <i class="fa-regular fa-heart store-action-icon"></i>
                                    <span class="store-action-text d-none d-md-inline">Wishlist</span>
                                    <?php if (!empty($wishlist_count) && $wishlist_count > 0): ?>
                                        <span class="store-cart-badge" id="wishlist-counter"><?= $wishlist_count; ?></span>
                                    <?php endif; ?>
                                </a>
                            </div>

                            <!-- Cart -->
                            <div class="store-nav-cart">
                                <a href="#shoppingCart" data-bs-toggle="modal" class="store-nav-action-link d-flex align-items-center text-decoration-none position-relative" title="Cart">
                                    <i class="fa-solid fa-cart-shopping store-action-icon"></i>
                                    <span class="store-action-text d-none d-md-inline">Cart</span>
                                    <?php if (!empty($cart_count) && $cart_count > 0): ?>
                                        <span class="store-cart-badge" id="cart-counter"><?= $cart_count; ?></span>
                                    <?php else: ?>
                                        <span class="store-cart-badge d-none" id="cart-counter">0</span>
                                    <?php endif; ?>
                                </a>
                            </div>

                        </div>

                        <!-- Centered Wide Search Box (Store style, wraps to 2nd row on mobile) -->
                        <div class="store-search-col flex-grow-1 order-3 order-md-2 position-relative">
                            <form action="<?= site_url('shop'); ?>" method="GET" class="store-search-form" id="storeNavSearchForm">
                                <div class="store-search-input-wrap">
                                    <button type="submit" class="store-search-icon-btn" aria-label="Search">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </button>
                                    <input type="text" 
                                           name="q" 
                                           id="storeNavSearchInput" 
                                           class="store-search-input" 
                                           placeholder="Search for sarees, t-shirts &amp; more" 
                                           value="<?= html_escape($search_query ?? ($this->input->get('q') ?: '')); ?>" 
                                           autocomplete="off">
                                </div>
                            </form>
                            <!-- Search Autocomplete Dropdown -->
                            <div id="storeAutocompleteResults" class="store-autocomplete-dropdown shadow-lg d-none"></div>
                        </div>

                    </div>
                </div>
            </div>
            <!-- /Top Navbar Row -->

            <!-- Category Bar (Only displayed on Home and Shop pages) -->
            <?php 
            $current_controller = strtolower($this->router->fetch_class());
            if (in_array($current_controller, ['home', 'shop'])): 
            ?>
            <div class="store-category-bar bg-white border-bottom">
                <div class="container px-2 px-lg-3 position-relative store-cat-container">
                    <!-- Left Arrow Button -->
                    <button type="button" class="store-cat-slide-btn store-cat-slide-prev" id="catSlidePrev" aria-label="Previous categories">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>

                    <!-- Scroll Track -->
                    <div class="store-cat-track" id="catSlideTrack">
                        <?php 
                        $header_categories = !empty($category_tree) ? $category_tree : [];
                        if (!empty($header_categories)): 
                            $c_idx = 1;
                            foreach ($header_categories as $root_c): 
                                $fallback_circle = 'collections/collection-circle/cls-circle' . (($c_idx % 17) ?: 1) . '.jpg';
                                $c_idx++;
                                $cat_img = !empty($root_c['image']) 
                                    ? base_url('assets/images/' . $root_c['image']) 
                                    : base_url('assets/images/' . $fallback_circle);
                        ?>
                            <div class="store-cat-item" data-cat-id="<?= $root_c['id']; ?>">
                                <a href="<?= site_url('shop/' . $root_c['slug']); ?>" class="store-cat-card">
                                    <div class="store-cat-thumb-outer">
                                        <img src="<?= $cat_img; ?>" 
                                             alt="<?= html_escape($root_c['name']); ?>" 
                                             class="store-cat-thumb-img" 
                                             onerror="this.src='<?= base_url('assets/images/' . $fallback_circle); ?>'">
                                    </div>
                                    <div class="store-cat-name-row">
                                        <span class="store-cat-name-text"><?= html_escape($root_c['name']); ?></span>
                                    </div>
                                </a>
                            </div>
                        <?php 
                            endforeach; 
                        endif; 
                        ?>
                    </div>

                    <!-- Right Arrow Button -->
                    <button type="button" class="store-cat-slide-btn store-cat-slide-next" id="catSlideNext" aria-label="Next categories">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            <?php endif; ?>
            <!-- /Category Bar -->
        </header>
        <!-- /Header -->

        <!-- Flash Messages (Only on pages that do not have their own contextual message container) -->
        <?php if (!isset($active_page) || !in_array($active_page, ['account', 'cart', 'checkout'])): ?>
        <div class="container">
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-check me-2"></i> <?= $this->session->flashdata('success'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-xmark me-2"></i> <?= $this->session->flashdata('error'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <style>
        /* ==========================================================
           Store / Flipkart Navbar & Category Bar Styles
           ========================================================== */

        /* Header Container */
        header.store-header-nav,
        #header.store-header-nav,
        .store-header-nav {
            position: relative !important;
            top: auto !important;
            background-color: #ffffff !important;
            box-shadow: none !important;
            z-index: 1020 !important;
            transition: none !important;
            padding-top: 61px !important; /* Preserves layout height for fixed store-top-navbar */
        }
        header.store-header-nav.is-sticky,
        #header.store-header-nav.is-sticky {
            position: relative !important;
            box-shadow: none !important;
        }

        /* Top Navbar Row - FIXED / STICKY AT TOP */
        .store-top-navbar {
            background-color: #ffffff !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            z-index: 1040 !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
        }

        /* Search Box */
        .store-search-col {
            max-width: 680px;
            margin: 0 1.25rem;
        }
        .store-search-input-wrap {
            display: flex;
            align-items: center;
            background-color: #f0f5ff;
            border-radius: 8px;
            height: 44px;
            padding: 0 14px;
            transition: background-color 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            border: 1px solid transparent;
        }
        .store-search-input-wrap:focus-within {
            background-color: #ffffff;
            border-color: #2874f0;
            box-shadow: 0 0 0 3px rgba(40, 116, 240, 0.12);
        }
        .store-search-icon-btn {
            background: transparent;
            border: none;
            color: #717478;
            font-size: 15px;
            padding: 0;
            margin-right: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .store-search-icon-btn:hover {
            color: #2874f0;
        }
        .store-search-input {
            height: 34px;
            border: 0 !important;
            background-color: #f0f5ff;
            outline: none;
            width: 100%;
            font-size: 14px;
            color: #212121;
            font-weight: 400;
            padding: 0 8px;
            border-radius: 4px;
            transition: background-color 0.2s ease;
        }
        @media (min-width: 768px) {
            .store-search-input {
                background-color: #f0f5ff;
            }
            .store-search-input:focus,
            #storeNavSearchInput:focus,
            .store-search-input-wrap:focus-within .store-search-input {
                background-color: #ffffff !important;
            }
        }
        .store-search-input::placeholder {
            color: #717478;
            font-size: 14px;
        }

        /* Autocomplete Dropdown */
        .store-autocomplete-dropdown {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            z-index: 1100;
            max-height: 420px;
            overflow-y: auto;
        }
        .store-auto-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 9px 14px;
            text-decoration: none;
            border-bottom: 1px solid #f5f5f5;
            transition: background-color 0.15s ease;
        }
        .store-auto-item:hover {
            background-color: #f4f8ff;
        }
        .store-auto-img {
            width: 44px;
            height: 44px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #eee;
            flex-shrink: 0;
        }
        .store-auto-title {
            font-size: 13.5px;
            font-weight: 500;
            color: #212121;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .store-auto-cat {
            font-size: 11.5px;
            color: #888;
            margin-top: 2px;
        }
        .store-auto-price {
            font-size: 13.5px;
            font-weight: 700;
            color: #2874f0;
            flex-shrink: 0;
        }
        .store-auto-viewall {
            display: block;
            text-align: center;
            padding: 10px;
            font-size: 13px;
            font-weight: 600;
            color: #2874f0;
            text-decoration: none;
            background: #fbfcfe;
        }
        .store-auto-viewall:hover {
            background-color: #f0f5ff;
            text-decoration: underline;
        }

        /* Action items (Login & Cart) */
        .store-nav-action-link {
            color: #212121;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            padding: 6px 4px;
            transition: color 0.15s ease;
        }
        .store-nav-action-link:hover {
            color: #2874f0;
        }
        .store-action-icon {
            font-size: 18px;
            margin-right: 6px;
        }
        .store-action-chevron {
            font-size: 10px;
            color: #666666;
            margin-left: 4px;
            transition: transform 0.2s ease, color 0.2s ease;
        }
        .store-nav-account:hover .store-action-chevron {
            transform: rotate(180deg);
            color: #2874f0;
        }
        .store-cart-badge {
            background-color: #dc3545;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            border-radius: 10px;
            padding: 1px 6px;
            margin-left: 5px;
            line-height: 1.3;
        }

        /* User Profile Drawer (Hover) */
        .after-login-drawer-menu {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: 255px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            border: 1px solid #f0f0f0;
            z-index: 1050;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translateY(8px);
            transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s ease;
        }
        @media (min-width: 992px) {
            .store-nav-account::after {
                content: '';
                position: absolute;
                top: 100%;
                right: 0;
                width: 100%;
                height: 16px;
                display: none;
            }
            .store-nav-account:hover::after {
                display: block;
            }
            .store-nav-account:hover > .after-login-drawer-menu,
            .store-nav-account > .after-login-drawer-menu:hover {
                opacity: 1 !important;
                visibility: visible !important;
                pointer-events: auto !important;
                transform: translateY(0) !important;
            }
        }
        .after-login-drawer-menu .dropdown-item {
            font-size: 13.5px;
            font-weight: 500;
            color: #333;
            transition: background-color 0.15s, color 0.15s, padding-left 0.15s;
        }
        .after-login-drawer-menu .dropdown-item:hover {
            background-color: #f4f8ff;
            color: #2874f0;
            padding-left: 1.25rem !important;
        }
        .after-login-drawer-menu .dropdown-item.text-danger:hover {
            background-color: #fff5f5;
            color: #dc3545 !important;
        }

        /* ==========================================================
           Category Bar Styles (Slider + Circular Icons + Hover Subcategories)
           ========================================================== */
        .store-category-bar {
            background-color: #ffffff;
            border-top: 1px solid #f0f0f0;
            position: relative !important;
            top: auto !important;
            z-index: 1010;
        }
        .store-cat-container {
            display: flex;
            align-items: center;
        }

        /* Slider Track */
        .store-cat-track {
            display: flex;
            align-items: flex-start;
            gap: 24px;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
            scroll-behavior: smooth;
            padding: 12px 16px 10px;
            width: 100%;
        }
        .store-cat-track::-webkit-scrollbar {
            display: none;
        }

        /* Slide Arrow Buttons (Matching navbar.PNG & category_drawer.png) */
        .store-cat-slide-btn {
            position: absolute;
            top: 0;
            bottom: 0;
            height: 100%;
            width: 34px;
            background: #ffffff;
            border: 1px solid #ebebeb;
            z-index: 1030;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #444444;
            font-size: 13px;
            transition: background-color 0.2s, color 0.2s, box-shadow 0.2s, opacity 0.2s;
            padding: 0;
        }
        .store-cat-slide-btn:hover {
            background-color: #fafafa;
            color: #5428e0;
        }
        .store-cat-slide-prev {
            left: 0;
            border-top: none;
            border-bottom: none;
            border-left: none;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.08);
        }
        .store-cat-slide-next {
            right: 0;
            border-top: none;
            border-bottom: none;
            border-right: none;
            box-shadow: -2px 0 8px rgba(0, 0, 0, 0.08);
        }
        .store-cat-slide-btn.disabled,
        .store-cat-slide-btn[disabled] {
            opacity: 0;
            pointer-events: none;
        }

        /* Category Item */
        .store-cat-item {
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            cursor: pointer;
        }
        .store-cat-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: inherit;
            text-align: center;
        }

        /* Circular Thumbnail with Tan/Gold Ring */
        .store-cat-thumb-outer {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            border: 2px solid #ecd8ab;
            padding: 3px;
            background-color: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .store-cat-item:hover .store-cat-thumb-outer,
        .store-cat-item.is-active .store-cat-thumb-outer {
            border-color: #d6b77c;
            transform: scale(1.06);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.12);
        }
        .store-cat-thumb-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            display: block;
        }

        /* Category Title & Chevron */
        .store-cat-name-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            margin-top: 6px;
            max-width: 105px;
        }
        .store-cat-name-text {
            font-size: 12.5px;
            font-weight: 600;
            color: #212121;
            line-height: 1.2;
            text-align: center;
            transition: color 0.15s ease;
        }
        .store-cat-arrow-icon {
            font-size: 9px;
            color: #444444;
            transition: transform 0.2s ease, color 0.2s ease;
            flex-shrink: 0;
        }
        .store-cat-item:hover .store-cat-name-text,
        .store-cat-item.is-active .store-cat-name-text {
            color: #5428e0;
        }
        .store-cat-item:hover .store-cat-arrow-icon,
        .store-cat-item.is-active .store-cat-arrow-icon {
            transform: rotate(180deg);
            color: #5428e0;
        }

        /* ==========================================================
           Multi-Column Category Drawer (Matching category_drawer.png)
           ========================================================== */
        .store-cat-drawers-container {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 1060;
            pointer-events: none;
        }
        .store-cat-drawer {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-top: none;
            border-radius: 0 0 16px 16px;
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.12);
            overflow: hidden;
            pointer-events: none;
            z-index: 1060;
        }
        .store-cat-drawer::before {
            content: '';
            position: absolute;
            top: -18px;
            left: 0;
            right: 0;
            height: 20px;
            background: transparent;
            pointer-events: auto;
        }
        .store-cat-drawer.is-open {
            display: block;
            pointer-events: auto;
            animation: storeDrawerFade 0.16s ease-out;
        }
        @keyframes storeDrawerFade {
            from {
                opacity: 0;
                transform: translateY(4px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .store-cat-drawer-inner {
            display: flex;
            flex-wrap: nowrap;
            align-items: stretch;
            min-height: 280px;
            max-height: 75vh;
            overflow-y: auto;
            overflow-x: auto;
            scrollbar-width: thin;
            background-color: #ffffff;
        }
        .store-drawer-col {
            min-width: 175px;
            max-width: 215px;
            flex: 1 0 auto;
            padding: 22px 20px 28px;
            background-color: #ffffff;
            box-sizing: border-box;
            border-right: 1px solid rgba(0, 0, 0, 0.04);
        }
        .store-drawer-col:last-child {
            border-right: none;
        }
        /* Alternating column backgrounds as seen in category_drawer.png */
        .store-drawer-col:nth-child(even) {
            background-color: #f8f9fa;
        }
        .store-drawer-col:nth-child(odd) {
            background-color: #ffffff;
        }
        .store-drawer-col-title {
            display: block;
            font-size: 13.5px;
            font-weight: 700;
            color: #1a1a1a;
            text-decoration: none;
            margin-bottom: 12px;
            line-height: 1.3;
            transition: color 0.15s ease;
        }
        .store-drawer-col-title:hover {
            color: #5428e0;
        }
        .store-drawer-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .store-drawer-item {
            margin: 0;
            padding: 0;
        }
        .store-drawer-link {
            display: block;
            font-size: 12.5px;
            color: #333333;
            text-decoration: none;
            padding: 4px 0;
            line-height: 1.4;
            transition: color 0.15s ease, transform 0.15s ease;
        }
        .store-drawer-link:hover {
            color: #5428e0;
            transform: translateX(2px);
        }

        @media (max-width: 767.98px) {
            /* Responsive header padding fallback */
            header.store-header-nav,
            #header.store-header-nav,
            .store-header-nav {
                padding-top: 112px !important;
            }

            /* Brand logo mobile sizing */
            .store-logo-col .logo {
                max-height: 36px !important;
            }

            /* Actions (User, Wishlist, Cart only) */
            .store-actions-col {
                gap: 16px !important;
            }
            .store-action-icon {
                font-size: 20px !important;
                margin-right: 0 !important;
                color: #212121 !important;
            }
            .store-action-text,
            .store-action-chevron {
                display: none !important;
            }
            .store-nav-action-link {
                padding: 4px 0 !important;
                position: relative !important;
                display: flex !important;
                align-items: center !important;
            }
            .store-cart-badge {
                position: absolute !important;
                top: -3px !important;
                right: -7px !important;
                font-size: 9px !important;
                min-width: 15px !important;
                height: 15px !important;
                line-height: 15px !important;
                padding: 0 4px !important;
                border-radius: 8px !important;
                margin-left: 0 !important;
            }

            /* Search bar matching mobie_view.jpeg */
            .store-search-col {
                width: 100% !important;
                max-width: 100% !important;
                flex-basis: 100% !important;
                margin: 16px 0 2px 0 !important;
                padding: 0 !important;
            }
            .store-search-input-wrap {
                background-color: #ffffff !important;
                border: 1px solid #dcdcdc !important;
                border-radius: 6px !important;
                height: 42px !important;
                padding: 0 12px !important;
                box-shadow: none !important;
            }
            .store-search-input-wrap:focus-within {
                border-color: #2874f0 !important;
                box-shadow: 0 0 0 2px rgba(40, 116, 240, 0.15) !important;
            }
            .store-search-icon-btn {
                color: #2b2b2b !important;
                font-size: 16px !important;
                margin-right: 10px !important;
            }
            .store-search-input {
                border: 0px !important;
                height: 34px !important;
                background-color: rgb(255 255 255) !important;
                font-size: 13.5px !important;
                color: #212121 !important;
                padding: 0 8px !important;
                border-radius: 4px;
            }
            .store-search-input:focus,
            #storeNavSearchInput:focus,
            .store-search-input-wrap:focus-within .store-search-input {
                background-color: rgb(255 255 255) !important;
            }
            .store-search-input::placeholder {
                color: #8c8c8c !important;
                font-size: 13.5px !important;
                font-weight: 400 !important;
            }

            /* Category slider mobile view */
            .store-cat-track {
                gap: 0px !important;
            }
            .store-cat-name-row {
                max-width: 80px !important;
            }
            .store-cat-slide-btn {
                display: none !important;
            }
            .store-cat-arrow-icon {
                display: none !important;
            }
            .store-cat-drawers-container,
            .store-cat-drawer {
                display: none !important;
            }
        }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Slider Scroll Navigation
            var track = document.getElementById('catSlideTrack');
            var prevBtn = document.getElementById('catSlidePrev');
            var nextBtn = document.getElementById('catSlideNext');

            function updateSlideArrows() {
                if (!track || !prevBtn || !nextBtn) return;
                var scrollLeft = track.scrollLeft;
                var maxScroll = track.scrollWidth - track.clientWidth;

                if (scrollLeft <= 5) {
                    prevBtn.classList.add('disabled');
                } else {
                    prevBtn.classList.remove('disabled');
                }

                if (scrollLeft >= maxScroll - 5) {
                    nextBtn.classList.add('disabled');
                } else {
                    nextBtn.classList.remove('disabled');
                }
            }

            if (track && prevBtn && nextBtn) {
                prevBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    track.scrollBy({ left: -320, behavior: 'smooth' });
                });

                nextBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    track.scrollBy({ left: 320, behavior: 'smooth' });
                });

                track.addEventListener('scroll', updateSlideArrows);
                window.addEventListener('resize', updateSlideArrows);
                updateSlideArrows();
            }

            // 2. Main Category Hover Multi-Column Drawer (Matching category_drawer.png)
            if (window.innerWidth >= 768) {
                var catItems = document.querySelectorAll('.store-cat-item');
                var catDrawers = document.querySelectorAll('.store-cat-drawer');
                var activeDrawer = null;
                var activeItem = null;
                var catCloseTimer = null;

                function openCategoryDrawer(catId, itemEl) {
                    clearTimeout(catCloseTimer);
                    var targetDrawer = document.getElementById('catDrawer-' + catId);
                    if (!targetDrawer) return;

                    if (activeDrawer && activeDrawer !== targetDrawer) {
                        activeDrawer.classList.remove('is-open');
                    }
                    if (activeItem && activeItem !== itemEl) {
                        activeItem.classList.remove('is-active');
                    }

                    itemEl.classList.add('is-active');
                    targetDrawer.classList.add('is-open');
                    activeDrawer = targetDrawer;
                    activeItem = itemEl;
                }

                function scheduleCloseCategoryDrawer() {
                    clearTimeout(catCloseTimer);
                    catCloseTimer = setTimeout(function() {
                        if (activeDrawer) {
                            activeDrawer.classList.remove('is-open');
                            activeDrawer = null;
                        }
                        if (activeItem) {
                            activeItem.classList.remove('is-active');
                            activeItem = null;
                        }
                    }, 180);
                }

                catItems.forEach(function(item) {
                    var catId = item.getAttribute('data-cat-id');
                    var hasSubs = item.classList.contains('has-subcategories');

                    item.addEventListener('mouseenter', function() {
                        if (hasSubs) {
                            openCategoryDrawer(catId, item);
                        } else {
                            if (activeDrawer) activeDrawer.classList.remove('is-open');
                            if (activeItem) activeItem.classList.remove('is-active');
                            activeDrawer = null;
                            activeItem = null;
                        }
                    });

                    item.addEventListener('mouseleave', function() {
                        if (hasSubs) {
                            scheduleCloseCategoryDrawer();
                        }
                    });
                });

                catDrawers.forEach(function(drawer) {
                    drawer.addEventListener('mouseenter', function() {
                        clearTimeout(catCloseTimer);
                    });
                    drawer.addEventListener('mouseleave', function() {
                        scheduleCloseCategoryDrawer();
                    });
                });

                // Hide drawer on track scroll or window scroll
                if (track) {
                    track.addEventListener('scroll', function() {
                        clearTimeout(catCloseTimer);
                        if (activeDrawer) {
                            activeDrawer.classList.remove('is-open');
                            activeDrawer = null;
                        }
                        if (activeItem) {
                            activeItem.classList.remove('is-active');
                            activeItem = null;
                        }
                    });
                }
                window.addEventListener('scroll', function() {
                    clearTimeout(catCloseTimer);
                    if (activeDrawer) {
                        activeDrawer.classList.remove('is-open');
                        activeDrawer = null;
                    }
                    if (activeItem) {
                        activeItem.classList.remove('is-active');
                        activeItem = null;
                    }
                });
            }

            // 3. User Account Drawer Hover Handling (Desktop)
            var navAccount = document.querySelector('.store-nav-account');
            if (navAccount) {
                var drawerMenu = navAccount.querySelector('.after-login-drawer-menu');
                if (drawerMenu) {
                    var drawerTimer = null;
                    var showDrawer = function() {
                        if (window.innerWidth >= 992) {
                            clearTimeout(drawerTimer);
                            drawerMenu.style.opacity = '1';
                            drawerMenu.style.visibility = 'visible';
                            drawerMenu.style.pointerEvents = 'auto';
                            drawerMenu.style.transform = 'translateY(0)';
                        }
                    };
                    var hideDrawer = function() {
                        if (window.innerWidth >= 992) {
                            drawerTimer = setTimeout(function() {
                                drawerMenu.style.opacity = '0';
                                drawerMenu.style.visibility = 'hidden';
                                drawerMenu.style.pointerEvents = 'none';
                                drawerMenu.style.transform = 'translateY(8px)';
                            }, 160);
                        }
                    };

                    navAccount.addEventListener('mouseenter', showDrawer);
                    navAccount.addEventListener('mouseleave', hideDrawer);
                    drawerMenu.addEventListener('mouseenter', showDrawer);
                    drawerMenu.addEventListener('mouseleave', hideDrawer);
                }
            }

            // 4. Search Autocomplete in Top Navbar
            var searchInput = document.getElementById('storeNavSearchInput');
            var resultsDropdown = document.getElementById('storeAutocompleteResults');
            var autoTimer = null;

            if (searchInput && resultsDropdown) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(autoTimer);
                    var query = this.value.trim();
                    if (query.length < 2) {
                        resultsDropdown.classList.add('d-none');
                        resultsDropdown.innerHTML = '';
                        return;
                    }

                    autoTimer = setTimeout(function() {
                        fetch('<?= site_url("shop/autocomplete"); ?>?q=' + encodeURIComponent(query))
                            .then(function(res) { return res.json(); })
                            .then(function(data) {
                                if (data.results && data.results.length > 0) {
                                    var html = '';
                                    data.results.forEach(function(item) {
                                        html += '<a href="' + item.url + '" class="store-auto-item">';
                                        html += '  <img src="' + item.image + '" class="store-auto-img" alt="' + item.title + '" onerror="this.src=\'<?= base_url("assets/images/collections/collection-circle/cls-circle1.jpg"); ?>\'">';
                                        html += '  <div class="flex-grow-1 overflow-hidden">';
                                        html += '    <div class="store-auto-title">' + item.title + '</div>';
                                        html += '    <div class="store-auto-cat">' + (item.category_name || 'Category') + '</div>';
                                        html += '  </div>';
                                        html += '  <div class="store-auto-price">' + item.price_html + '</div>';
                                        html += '</a>';
                                    });
                                    html += '<a href="<?= site_url("shop"); ?>?q=' + encodeURIComponent(query) + '" class="store-auto-viewall">';
                                    html += '  View all results for "<strong>' + query.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</strong>" <i class="fa-solid fa-arrow-right ms-1"></i>';
                                    html += '</a>';

                                    resultsDropdown.innerHTML = html;
                                    resultsDropdown.classList.remove('d-none');
                                } else {
                                    resultsDropdown.innerHTML = '<div class="p-3 text-center text-muted small">No products found for "' + query.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '"</div>';
                                    resultsDropdown.classList.remove('d-none');
                                }
                            })
                            .catch(function() {
                                resultsDropdown.classList.add('d-none');
                            });
                    }, 220);
                });

                // Hide autocomplete when clicking outside
                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !resultsDropdown.contains(e.target)) {
                        resultsDropdown.classList.add('d-none');
                    }
                });
            }

            // 5. Sync Top Navbar Height with Header Padding
            var topNav = document.querySelector('.store-top-navbar');
            var headerEl = document.querySelector('header.store-header-nav');
            function syncTopNavHeight() {
                if (topNav && headerEl) {
                    var h = topNav.offsetHeight;
                    if (h > 0) {
                        headerEl.style.setProperty('padding-top', h + 'px', 'important');
                    }
                }
            }
            syncTopNavHeight();
            window.addEventListener('resize', syncTopNavHeight);
            window.addEventListener('load', syncTopNavHeight);
            window.addEventListener('orientationchange', syncTopNavHeight);

            // 6. Responsive Search Input & Placeholder (Matching mobie_view.jpeg on mobile)
            var navSearchInput = document.getElementById('storeNavSearchInput');
            function updateSearchStyles() {
                if (navSearchInput) {
                    if (window.innerWidth < 768) {
                        navSearchInput.style.setProperty('border', '0px', 'important');
                        navSearchInput.style.setProperty('height', '34px', 'important');
                        navSearchInput.style.setProperty('background-color', 'rgb(255, 255, 255)', 'important');
                        if (!navSearchInput.value) {
                            navSearchInput.placeholder = 'Search for sarees, t-shirts & more';
                        }
                    } else {
                        navSearchInput.style.removeProperty('border');
                        navSearchInput.style.removeProperty('height');
                        if (document.activeElement === navSearchInput) {
                            navSearchInput.style.setProperty('background-color', '#ffffff', 'important');
                        } else {
                            navSearchInput.style.setProperty('background-color', '#f0f5ff', 'important');
                        }
                        if (!navSearchInput.value) {
                            navSearchInput.placeholder = 'Search for Products, Brands and More';
                        }
                    }
                }
            }
            updateSearchStyles();
            window.addEventListener('resize', updateSearchStyles);

            // 7. Desktop Focus & Blur Background Toggle
            if (navSearchInput) {
                navSearchInput.addEventListener('focus', function() {
                    if (window.innerWidth >= 768) {
                        this.style.setProperty('background-color', '#ffffff', 'important');
                    }
                });
                navSearchInput.addEventListener('blur', function() {
                    if (window.innerWidth >= 768) {
                        this.style.setProperty('background-color', '#f0f5ff', 'important');
                    } else {
                        this.style.setProperty('background-color', 'rgb(255, 255, 255)', 'important');
                    }
                });
            }
        });
        </script>

