        <!-- Page Title & Breadcrumbs (Hidden on Mobile) -->
        <div class="page-title py-3 bg-light border-bottom d-none d-md-block">
            <div class="container" style="max-width: 1240px;">
                <ul class="breadcrumbs d-flex align-items-center mb-0 p-0" style="font-size: 13px; list-style: none;">
                    <li><a class="link text-muted text-decoration-none" href="<?= site_url('home'); ?>">Home</a></li>
                    <li class="mx-2 text-muted">/</li>
                    <li><a class="link text-muted text-decoration-none" href="<?= site_url('shop'); ?>">Shop</a></li>
                    <li class="mx-2 text-muted">/</li>
                    <li class="fw-semibold text-dark">Shopping Cart</li>
                </ul>
            </div>
        </div>
        <!-- /Page Title -->

        <!-- Flash messages -->
        <?php if ($this->session->flashdata('success')): ?>
            <div class="container mt-3" style="max-width: 1240px;">
                <div class="alert alert-success alert-dismissible fade show rounded-2 py-2 d-flex align-items-center justify-content-between" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-circle-check me-2 fs-5 text-success"></i>
                        <span><?= $this->session->flashdata('success'); ?></span>
                    </div>
                    <button type="button" class="btn-close-alert" onclick="this.closest('.alert').remove();" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="container mt-3" style="max-width: 1240px;">
                <div class="alert alert-danger alert-dismissible fade show rounded-2 py-2 d-flex align-items-center justify-content-between" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-triangle-exclamation me-2 fs-5 text-danger"></i>
                        <span><?= $this->session->flashdata('error'); ?></span>
                    </div>
                    <button type="button" class="btn-close-alert" onclick="this.closest('.alert').remove();" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <style>
        /* Cart Page Styles */
        .btn-close-alert {
            background: transparent !important;
            border: none !important;
            padding: 4px 8px !important;
            margin: 0 !important;
            color: inherit !important;
            opacity: 0.6 !important;
            font-size: 16px !important;
            line-height: 1 !important;
            cursor: pointer !important;
            transition: opacity 0.2s ease !important;
        }
        .btn-close-alert:hover {
            opacity: 1 !important;
        }
        .cart-page-wrapper {
            background-color: #f1f3f6;
            min-height: 80vh;
        }
        .tf-delivery-address-card,
        .cart-address-card {
            background-color: #ffffff !important;
            border-radius: 16px !important;
            border: 1px solid #e5e7eb !important;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .tf-delivery-address-card:hover,
        .cart-address-card:hover {
            border-color: #2874f0 !important;
            box-shadow: 0 2px 8px rgba(40, 116, 240, 0.08) !important;
        }

        /* Utility */
        .min-w-0 {
            min-width: 0 !important;
        }

        #cart-items-container {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
        }

        /* Cart Product Card */
        .cart-product-card {
            border-color: #e5e7eb !important;
            transition: border-color 0.2s ease;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
        }
        .cart-product-row {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            overflow: hidden !important;
        }
        .cart-product-info {
            flex: 1 1 0% !important;
            min-width: 0 !important;
            max-width: 100% !important;
            width: 0 !important;
            overflow: hidden !important;
        }
        .cart-product-card .cart-item-title {
            display: block !important;
            width: 100% !important;
            max-width: 100% !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            font-size: 14px !important;
            font-weight: 700 !important;
            line-height: 1.35 !important;
            color: #111827 !important;
            text-decoration: none !important;
            margin-bottom: 4px !important;
        }
        .cart-product-card .cart-item-title:hover {
            color: #2874f0 !important;
        }
        .cart-product-card .cart-item-short-desc {
            display: -webkit-box !important;
            -webkit-line-clamp: 2 !important;
            line-clamp: 2 !important;
            -webkit-box-orient: vertical !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            word-break: break-word !important;
            overflow-wrap: anywhere !important;
            font-size: 12px !important;
            line-height: 1.35 !important;
            color: #6b7280 !important;
            max-width: 100% !important;
            width: 100% !important;
            margin-bottom: 6px !important;
            max-height: 2.7em !important;
        }
        .cart-qty-stepper {
            display: inline-flex;
            align-items: center;
            border: 1px solid #d1d5db;
            border-radius: 999px;
            background: #ffffff;
            height: 32px;
            overflow: hidden;
            width: 100%;
            max-width: 90px;
            justify-content: space-between;
        }
        .cart-qty-stepper .qty-step-btn {
            width: 28px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            font-size: 16px;
            font-weight: 700;
            color: #4b5563;
            cursor: pointer;
            transition: background 0.15s ease, color 0.15s ease;
            padding: 0;
            user-select: none;
        }
        .cart-qty-stepper .qty-step-btn:hover {
            background: #f3f4f6;
            color: #111827;
        }
        .cart-qty-stepper .qty-val {
            min-width: 24px;
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            color: #111827;
        }

        /* Three Action Buttons */
        .cart-action-btn {
            border: 1px solid #d1d5db !important;
            background: #ffffff !important;
            color: #1f2937 !important;
            border-radius: 8px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            padding: 8px 10px !important;
            transition: all 0.2s ease !important;
            text-align: center;
        }
        .cart-action-btn:hover {
            border-color: #111827 !important;
            background: #f9fafb !important;
            color: #111827 !important;
        }

        /* Proceed to Buy Button - Theme Color with Simple Hover Effect */
        .btn-proceed-buy {
            background: #108910 !important;
            background-color: #108910 !important;
            border: 1px solid #108910 !important;
            border-radius: 10px !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 15px !important;
            padding: 12px 24px !important;
            box-shadow: 0 4px 14px rgba(16, 137, 16, 0.2) !important;
            transition: background-color 0.2s ease, border-color 0.2s ease !important;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            letter-spacing: 0.2px;
        }
        .btn-proceed-buy:hover,
        .btn-proceed-buy:focus {
            background: #003D29 !important;
            background-color: #003D29 !important;
            border-color: #003D29 !important;
            color: #ffffff !important;
        }

        @media (max-width: 767px) {
            .page-title,
            .breadcrumbs,
            .tf-breadcrumb {
                display: none !important;
            }
        }

        /* Product recommendations card 1px border */
        .cart-rec-card {
            border: 1px solid #e5e7eb !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .cart-rec-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.06) !important;
        }
        .btn-cart-secondary-action {
            border: 1px solid #d1d5db !important;
            background: #ffffff;
            color: #1e2022;
            font-weight: 600;
            font-size: 12px;
            border-radius: 999px;
            padding: 5px 10px;
            transition: all 0.2s ease;
        }
        .btn-cart-secondary-action:hover {
            border-color: #111827 !important;
            background: #111827 !important;
            color: #ffffff !important;
        }
        </style>

        <!-- Section cart -->
        <section class="cart-page-wrapper py-3 py-md-4">
            <div class="container cart-page-content-container" style="max-width: 1240px;">
                
                <!-- Cart Content Container -->
                <div class="row <?= empty($cart_items) ? 'd-none' : ''; ?>" id="cart-content-wrapper">
                    
                    <!-- Left Column: Delivery Address & Cart Items -->
                    <div class="col-lg-8">
                        
                        <!-- Delivering to Header & Address Card (Matches address_style.jpeg) -->
                        <?php 
                            $disp_name = trim(($active_address['first_name'] ?? '') . ' ' . ($active_address['last_name'] ?? ''));
                            $disp_pin  = $active_address['postcode'] ?? '';
                            $disp_tag  = !empty($active_address['company']) ? strtoupper($active_address['company']) : 'HOME';
                            $disp_addr = implode(', ', array_filter([$active_address['address_1'] ?? '', $active_address['address_2'] ?? '', $active_address['city'] ?? '', $active_address['state'] ?? '']));
                            $disp_phone = !empty($active_address['phone']) ? $active_address['phone'] : ($current_user['phone'] ?? '');
                        ?>
                        <div class="cart-address-section mb-3">
                            <h5 class="fw-bold text-dark mb-2" style="font-size: 18px; letter-spacing: -0.2px;">Delivering to</h5>
                            <div class="cart-address-card p-3 p-sm-4 bg-white border shadow-sm" style="background-color: #ffffff !important; border-radius: 16px; border-color: #e5e7eb !important;">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2 min-w-0" id="cart-address-header-box" style="cursor: pointer;" onclick="handleAddressAction()">
                                        <i class="fa-solid fa-house text-dark flex-shrink-0" style="font-size: 16px;"></i>
                                        <strong class="fw-bold text-dark text-truncate" id="display-address-name" style="font-size: 17px;">
                                            <?= html_escape($disp_name ?: (!empty($is_logged_in) ? 'Select Address' : 'Sign In to Select Address')); ?>
                                        </strong>
                                        <span class="badge bg-light text-secondary border text-uppercase d-none" id="display-address-tag"><?= html_escape($disp_tag); ?></span>
                                    </div>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none fw-bold flex-shrink-0 ms-2" id="btn-address-action" style="color: #1a73e8; font-size: 16px; cursor: pointer;" onclick="handleAddressAction(); event.stopPropagation();">
                                        <?= !empty($is_logged_in) ? 'Change' : 'Sign In'; ?>
                                    </button>
                                </div>
                                <div class="text-dark mb-2" style="font-size: 14px; line-height: 1.45; color: #212529 !important; <?= empty($is_logged_in) ? 'cursor: pointer;' : ''; ?>" id="display-address-full" <?= empty($is_logged_in) ? 'onclick="handleAddressAction()"' : ''; ?>>
                                    <?= !empty($is_logged_in) ? html_escape($disp_addr ?: 'Choose or add your shipping address') : 'Please sign in or register to set your delivery address'; ?><?= (!empty($disp_pin) && !empty($is_logged_in)) ? ', ' . html_escape($disp_pin) : ''; ?>
                                </div>
                                <div class="d-flex align-items-center gap-2 text-dark" style="font-size: 14px; color: #212529 !important; <?= empty($disp_phone) ? 'display: none !important;' : ''; ?>" id="display-address-phone-wrap">
                                    <i class="fa-solid fa-phone text-dark" style="font-size: 13px; transform: rotate(15deg);"></i>
                                    <span id="display-address-phone" class="fw-normal"><?= html_escape($disp_phone); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Cart Items List (Mobile & Desktop Card Layout from cart_page.jpeg) -->
                        <div id="cart-items-container">
                            <?php if (!empty($cart_items)): ?>
                                <?php foreach ($cart_items as $item): 
                                    $item_img = !empty($item['image']) 
                                        ? (strpos($item['image'], 'http') === 0 ? $item['image'] : base_url('assets/images/' . $item['image']))
                                        : base_url('assets/images/products/womens/women-1.jpg');
                                    $item_key = $item['cart_key'] ?? $item['id'];
                                    $reg_price = !empty($item['regular_price']) ? (float)$item['regular_price'] : round($item['price'] * 1.3, 2);
                                    if ($reg_price <= $item['price']) $reg_price = round($item['price'] * 1.3, 2);
                                    $disc_pct = round((($reg_price - $item['price']) / $reg_price) * 100);
                                ?>
                                <div class="card border rounded-3 p-3 bg-white shadow-sm mb-3 cart-product-card" id="cart-row-<?= html_escape($item_key); ?>" style="width: 100%; max-width: 100%; min-width: 0; overflow: hidden; box-sizing: border-box;">
                                    <div class="d-flex gap-3 cart-product-row align-items-start" style="width: 100%; max-width: 100%; min-width: 0; overflow: hidden;">
                                        <!-- Left Column: Thumbnail + Quantity Stepper -->
                                        <div class="d-flex flex-column align-items-center flex-shrink-0" style="width: 92px;">
                                            <a href="<?= site_url('product/' . $item['slug']); ?>" class="d-block rounded overflow-hidden mb-2 bg-light text-center border" style="width: 90px; height: 90px;">
                                                <img src="<?= $item_img; ?>" alt="<?= html_escape($item['title']); ?>" class="w-100 h-100 object-fit-contain" onerror="this.src='<?= base_url('assets/images/products/womens/women-1.jpg'); ?>'">
                                            </a>
                                            <!-- Quantity Stepper -->
                                            <div class="cart-qty-stepper">
                                                <button type="button" class="qty-step-btn btn-decrease" onclick="updateCartPageQty('<?= html_escape($item_key); ?>', -1, event)">-</button>
                                                <span class="qty-val" id="cart-page-qty-val-<?= html_escape($item_key); ?>"><?= $item['quantity']; ?></span>
                                                <input type="hidden" id="cart-page-qty-<?= html_escape($item_key); ?>" value="<?= $item['quantity']; ?>" data-max="<?= !empty($item['stock_max']) ? (int) $item['stock_max'] : 999; ?>">
                                                <button type="button" class="qty-step-btn btn-increase" onclick="updateCartPageQty('<?= html_escape($item_key); ?>', 1, event)">+</button>
                                            </div>
                                        </div>

                                        <!-- Right Column: Details & Pricing -->
                                        <div class="cart-product-info min-w-0" style="flex: 1 1 0%; min-width: 0; max-width: calc(100% - 108px); width: 0; overflow: hidden;">
                                            <div class="mb-1" style="min-width: 0; max-width: 100%; width: 100%; overflow: hidden;">
                                                <a href="<?= site_url('product/' . $item['slug']); ?>" class="cart-item-title text-truncate" style="display: block; width: 100%; max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 14px; line-height: 1.35; font-weight: 700; color: #111827; text-decoration: none;" title="<?= html_escape($item['title']); ?>">
                                                    <?= html_escape($item['title']); ?>
                                                </a>
                                                <?php 
                                                    $short_desc = !empty($item['short_description']) ? trim(strip_tags($item['short_description'])) : (!empty($item['variant_title']) ? trim(strip_tags($item['variant_title'])) : '');
                                                ?>
                                                <?php if (!empty($short_desc)): ?>
                                                    <div class="cart-item-short-desc" style="display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; word-break: break-word; overflow-wrap: anywhere; font-size: 12px; line-height: 1.35; color: #6b7280; width: 100%; max-width: 100%; max-height: 2.7em; margin-bottom: 6px;" title="<?= html_escape($short_desc); ?>">
                                                        <?= html_escape($short_desc); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Rating (5 Stars) -->
                                            <?php 
                                                $item_rating = isset($item['rating']) && (float)$item['rating'] > 0 ? (float)$item['rating'] : 5.0;
                                                $item_reviews = isset($item['reviews_count']) ? (int)$item['reviews_count'] : 0;
                                                $r_score = round($item_rating * 2) / 2;
                                            ?>
                                            <div class="d-flex align-items-center gap-1 mb-1">
                                                <div class="d-inline-flex align-items-center gap-1" style="font-size: 11px;">
                                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                                        <?php if ($s <= $r_score): ?>
                                                            <i class="fa-solid fa-star" style="color: #ff9f00;"></i>
                                                        <?php elseif ($s - 0.5 == $r_score): ?>
                                                            <i class="fa-solid fa-star-half-stroke" style="color: #ff9f00;"></i>
                                                        <?php else: ?>
                                                            <i class="fa-regular fa-star" style="color: #d1d5db;"></i>
                                                        <?php endif; ?>
                                                    <?php endfor; ?>
                                                </div>
                                                <span class="text-muted" style="font-size: 11px;">(<?= number_format($item_reviews); ?>)</span>
                                            </div>

                                            <!-- Price Row -->
                                            <div class="d-flex align-items-center gap-2 flex-wrap mt-1">
                                                <?php if ($disc_pct > 0): ?>
                                                    <span class="text-success fw-bold" style="font-size: 14px;"><i class="fa-solid fa-arrow-down small"></i><?= $disc_pct; ?>%</span>
                                                    <span class="text-muted text-decoration-line-through small" id="cart-item-reg-<?= html_escape($item_key); ?>"><?= $currency_symbol . number_format($reg_price * $item['quantity'], 2); ?></span>
                                                <?php endif; ?>
                                                <span class="fw-bold text-dark fs-6" id="cart-page-total-<?= html_escape($item_key); ?>"><?= $currency_symbol . number_format($item['total'], 2); ?></span>
                                            </div>

                                            <!-- Stock Alert -->
                                            <?php if (!empty($item['stock_max']) && $item['stock_max'] < 10): ?>
                                                <div class="text-danger small fw-medium mt-1" style="font-size: 11px;">Only <?= $item['stock_max']; ?> left</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Dispatch Note Row -->
                                    <div class="p-2 px-3 rounded-2 bg-light d-flex align-items-center gap-2 mt-3 mb-3 text-secondary small border" style="font-size: 12px; line-height: 1.35;">
                                        <i class="fa-solid fa-truck-fast text-primary fs-6"></i>
                                        <span><?= !empty($cart_summary['shipping_dispatch_note']) ? html_escape($cart_summary['shipping_dispatch_note']) : 'Dispatched within 24-48 hours with courier tracking.'; ?></span>
                                    </div>

                                    <!-- Three Action Buttons Row: Remove, Move to Wishlist, Buy Now (1px border) -->
                                    <div class="d-flex gap-2 mt-3 pt-2 border-top">
                                        <button type="button" class="btn btn-sm flex-fill cart-action-btn" onclick="removeCartPageItem('<?= html_escape($item_key); ?>', event)">
                                            Remove
                                        </button>
                                        <button type="button" class="btn btn-sm flex-fill cart-action-btn" onclick="moveCartPageItemToWishlist('<?= html_escape($item_key); ?>', <?= (int)$item['id']; ?>, event)">
                                            Move to Wishlist
                                        </button>
                                        <button type="button" class="btn btn-sm flex-fill cart-action-btn" onclick="buyNowFromCart('<?= html_escape($item_key); ?>', event)">
                                            Buy Now
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Savings Zone / Coupon Card (from cart_page.jpeg) -->
                        <div class="card border rounded-3 p-3 bg-white shadow-sm mb-4">
                            <div class="mb-2">
                                <span class="text-muted small">Have a promo code?</span>
                            </div>
                            <form action="<?= site_url('cart/apply_coupon'); ?>" method="POST" class="d-flex gap-2 mt-1" id="cartCouponForm">
                                <input type="text" name="coupon_code" id="cartCouponInput" class="form-control rounded-2" placeholder="Enter coupon code (e.g. WELCOME10)" value="<?= html_escape($cart_summary['coupon']['code'] ?? ''); ?>" required style="font-size: 13px;">
                                <button type="submit" class="btn btn-dark fw-semibold px-3 flex-shrink-0" style="font-size: 13px; border-radius: 8px;">Apply</button>
                            </form>
                        </div>
                    </div>

                    <!-- Right Column: Price Details Sidebar -->
                    <div class="col-lg-4">
                        <div class="sticky-top" style="top: 20px; z-index: 10;">
                            
                            <!-- Price Details Card (Matches cart_page.jpeg) -->
                            <div class="card border rounded-3 p-3 bg-white shadow-sm mb-3" id="cart-price-details-card">
                                <h6 class="fw-bold text-secondary text-uppercase border-bottom pb-2 mb-3" style="font-size: 13px; letter-spacing: 0.5px;">Price Details</h6>
                                
                                <div class="d-flex justify-content-between align-items-center mb-2" style="font-size: 14px;">
                                    <span class="text-secondary" id="order-summary-mrp-label">
                                        <?= (!empty($cart_summary['tax_inclusive']) && (int)$cart_summary['tax_inclusive'] === 1) ? 'MRP (incl. of all taxes)' : 'MRP'; ?>
                                    </span>
                                    <span class="text-dark fw-medium" id="order-summary-mrp"><?= $currency_symbol . number_format($cart_summary['mrp_total'], 2); ?></span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-2 text-success <?= (empty($cart_summary['mrp_discount']) || $cart_summary['mrp_discount'] <= 0) ? 'd-none' : ''; ?>" style="font-size: 14px;" id="order-summary-mrp-discount-row">
                                    <span>Discount on MRP</span>
                                    <span class="fw-semibold" id="order-summary-mrp-discount">-<?= $currency_symbol . number_format($cart_summary['mrp_discount'], 2); ?></span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-2 text-success <?= (empty($cart_summary['discount']) || $cart_summary['discount'] <= 0) ? 'd-none' : ''; ?>" style="font-size: 14px;" id="order-summary-discount-row">
                                    <span>Coupon Discount (<span id="applied-coupon-code"><?= html_escape($cart_summary['coupon']['code'] ?? ''); ?></span>) <a href="<?= site_url('cart/remove_coupon'); ?>" class="small text-danger text-decoration-underline ms-1">Remove</a></span>
                                    <span class="fw-semibold" id="order-summary-discount">-<?= $currency_symbol . number_format($cart_summary['discount'] ?? 0, 2); ?></span>
                                </div>

                                <!-- Dynamic Shipping Charges Breakdown -->
                                <div id="order-summary-shipping-breakdown">
                                    <?php if (!empty($cart_summary['shipping_charges']) && is_array($cart_summary['shipping_charges'])): ?>
                                        <?php foreach ($cart_summary['shipping_charges'] as $s_charge): ?>
                                            <div class="d-flex justify-content-between align-items-center mb-2" style="font-size: 14px;">
                                                <span class="text-secondary"><?= html_escape($s_charge['name']); ?></span>
                                                <span class="<?= ((float)$s_charge['value'] <= 0) ? 'text-success fw-semibold' : 'text-dark fw-medium'; ?>">
                                                    <?= ((float)$s_charge['value'] <= 0) ? 'FREE' : $currency_symbol . number_format($s_charge['value'], 2); ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2" style="font-size: 14px;">
                                            <span class="text-secondary">Delivery Charges</span>
                                            <span class="<?= ($cart_summary['shipping'] <= 0) ? 'text-success fw-semibold' : 'text-dark fw-medium'; ?>" id="standard-shipping-price">
                                                <?= ($cart_summary['shipping'] <= 0) ? 'FREE' : $currency_symbol . number_format($cart_summary['shipping'], 2); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Tax Breakdown (Exclusive display mode) -->
                                <?php
                                    $show_tax_row = (empty($cart_summary['tax_inclusive']) && !empty($cart_summary['tax_enabled']) && !empty($cart_summary['tax']) && $cart_summary['tax'] > 0);
                                ?>
                                <div class="d-flex justify-content-between align-items-center mb-2 <?= $show_tax_row ? '' : 'd-none'; ?>" style="font-size: 14px;" id="order-summary-tax-row">
                                    <span class="text-secondary" id="order-summary-tax-label">Tax (<?= (float)($cart_summary['tax_rate_percent'] ?? 0); ?>%)</span>
                                    <span class="text-dark fw-medium" id="order-summary-tax"><?= $currency_symbol . number_format($cart_summary['tax'] ?? 0, 2); ?></span>
                                </div>

                                <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-bold text-dark" style="font-size: 16px;">Total Amount</span>
                                    <span class="fw-bold text-dark" style="font-size: 18px;" id="order-summary-grandtotal"><?= $currency_symbol . number_format($cart_summary['total'], 2); ?></span>
                                </div>

                                <div class="p-2 px-3 rounded-2 d-flex align-items-center gap-2 mb-3 <?= (empty($cart_summary['total_savings']) || $cart_summary['total_savings'] <= 0) ? 'd-none' : ''; ?>" style="background-color: #e8f8f0; color: #16a34a; font-size: 13px; font-weight: 600;" id="order-summary-savings-banner">
                                    <i class="fa-solid fa-tag"></i>
                                    <span>You'll Save <span id="order-summary-savings"><?= $currency_symbol . number_format($cart_summary['total_savings'], 2); ?></span> on this order</span>
                                </div>

                                <!-- Proceed to Buy Button -->
                                <div class="mb-3">
                                    <button type="button" class="btn btn-proceed-buy w-100 py-3 fw-bold" onclick="return handleCartCheckout(event)" style="font-size: 16px;">
                                        Proceed To Buy
                                    </button>
                                </div>

                                <!-- Safe & Secure Payments Trust Badge -->
                                <div class="d-flex align-items-center gap-2 pt-3 border-top text-secondary small" style="font-size: 12px; line-height: 1.35;">
                                    <i class="fa-solid fa-shield-halved text-muted fs-4"></i>
                                    <span>Safe and secure payments. Easy returns. 100% Authentic products.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty Cart State -->
                <div class="<?= !empty($cart_items) ? 'd-none' : ''; ?> text-center py-5" id="cart-empty-wrapper">
                    <div class="card border rounded-4 p-5 bg-white shadow-sm d-inline-block w-100 text-center" style="max-width: 650px;">
                        <div class="mb-3 text-muted" style="font-size: 64px;">
                            <i class="fa-solid fa-cart-shopping text-secondary opacity-50"></i>
                        </div>
                        <h4 class="fw-bold mb-2 text-dark">Your shopping cart is currently empty!</h4>
                        <p class="text-secondary mb-4">Discover our top-rated collections and start adding items to your cart.</p>
                        <a href="<?= site_url('shop'); ?>" class="btn btn-proceed-buy px-5 py-2">
                            Explore Products
                        </a>
                    </div>
                </div>

                <!-- Recently Viewed Products Section -->
                <?php if (!empty($recently_viewed_products)): ?>
                <div class="cart-recently-viewed-section mt-4 pt-2">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold text-dark m-0" style="font-size: 18px;">Recently Viewed</h5>
                    </div>
                    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                        <?php foreach ($recently_viewed_products as $rvp): 
                            $rvp_price = !empty($rvp['sale_price']) ? (float) $rvp['sale_price'] : (float) $rvp['price'];
                            $rvp_reg   = !empty($rvp['price']) ? (float) $rvp['price'] : round($rvp_price * 1.3, 2);
                            $rvp_img   = !empty($rvp['main_image']) ? (strpos($rvp['main_image'], 'http') === 0 ? $rvp['main_image'] : base_url('assets/images/' . $rvp['main_image'])) : base_url('assets/images/products/womens/women-1.jpg');
                            $in_cart   = isset($cart_items[$rvp['id']]);
                        ?>
                        <div class="col">
                            <div class="card h-100 border rounded-3 p-2 bg-white cart-rec-card d-flex flex-column">
                                <a href="<?= site_url('product/' . $rvp['slug']); ?>" class="d-block mb-2 text-center overflow-hidden rounded bg-light" style="aspect-ratio: 1/1;">
                                    <img src="<?= $rvp_img; ?>" alt="<?= html_escape($rvp['title']); ?>" class="w-100 h-100 object-fit-contain" onerror="this.src='<?= base_url('assets/images/products/womens/women-1.jpg'); ?>'">
                                </a>
                                <div class="flex-grow-1 d-flex flex-column">
                                    <div class="d-flex align-items-center gap-1 mb-1">
                                        <span class="badge bg-success text-white px-1 rounded" style="font-size: 10px;">
                                            <?= number_format($rvp['rating'] ?? 4.2, 1); ?> ★
                                        </span>
                                    </div>
                                    <a href="<?= site_url('product/' . $rvp['slug']); ?>" class="text-dark fw-semibold text-truncate d-block mb-1 text-decoration-none" style="font-size: 13px;" title="<?= html_escape($rvp['title']); ?>">
                                        <?= html_escape($rvp['title']); ?>
                                    </a>
                                    <div class="d-flex align-items-center gap-1 mb-2">
                                        <?php if ($rvp_reg > $rvp_price): ?>
                                            <span class="text-muted text-decoration-line-through" style="font-size: 11px;"><?= $currency_symbol . number_format($rvp_reg, 2); ?></span>
                                        <?php endif; ?>
                                        <span class="fw-bold text-dark" style="font-size: 14px;"><?= $currency_symbol . number_format($rvp_price, 2); ?></span>
                                    </div>
                                    <form action="<?= site_url('cart/add'); ?>" method="POST" class="mt-auto">
                                        <input type="hidden" name="product_id" value="<?= $rvp['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-sm w-100 btn-cart-secondary-action">
                                            <?= $in_cart ? 'Add More to cart' : 'Add to cart'; ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Your Wishlist Products (Shown ONLY if user is logged in) -->
                <?php if (!empty($is_logged_in) && !empty($wishlist_items)): ?>
                <div class="cart-wishlist-section mt-4 pt-2">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold text-dark m-0 d-flex align-items-center gap-1" style="font-size: 18px;">
                            Your Wishlist <i class="fa-solid fa-heart text-danger ms-1" style="font-size: 15px;"></i>
                        </h5>
                        <a href="<?= site_url('wishlist'); ?>" class="btn btn-sm btn-link text-dark p-0 text-decoration-none fw-semibold" style="font-size: 13px;">
                            View all <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                        <?php foreach ($wishlist_items as $w_item): 
                            $w_price = !empty($w_item['sale_price']) ? (float) $w_item['sale_price'] : (float) $w_item['price'];
                            $w_reg   = !empty($w_item['price']) ? (float) $w_item['price'] : round($w_price * 1.3, 2);
                            $w_img   = !empty($w_item['main_image']) ? (strpos($w_item['main_image'], 'http') === 0 ? $w_item['main_image'] : base_url('assets/images/' . $w_item['main_image'])) : base_url('assets/images/products/womens/women-1.jpg');
                        ?>
                        <div class="col">
                            <div class="card h-100 border rounded-3 p-2 bg-white cart-rec-card d-flex flex-column">
                                <a href="<?= site_url('product/' . $w_item['slug']); ?>" class="d-block mb-2 text-center overflow-hidden rounded bg-light" style="aspect-ratio: 1/1;">
                                    <img src="<?= $w_img; ?>" alt="<?= html_escape($w_item['title']); ?>" class="w-100 h-100 object-fit-contain" onerror="this.src='<?= base_url('assets/images/products/womens/women-1.jpg'); ?>'">
                                </a>
                                <div class="flex-grow-1 d-flex flex-column">
                                    <div class="d-flex align-items-center gap-1 mb-1">
                                        <span class="badge bg-success text-white px-1 rounded" style="font-size: 10px;">4.3 ★</span>
                                    </div>
                                    <a href="<?= site_url('product/' . $w_item['slug']); ?>" class="text-dark fw-semibold text-truncate d-block mb-1 text-decoration-none" style="font-size: 13px;" title="<?= html_escape($w_item['title']); ?>">
                                        <?= html_escape($w_item['title']); ?>
                                    </a>
                                    <div class="d-flex align-items-center gap-1 mb-2">
                                        <?php if ($w_reg > $w_price): ?>
                                            <span class="text-muted text-decoration-line-through" style="font-size: 11px;"><?= $currency_symbol . number_format($w_reg, 2); ?></span>
                                        <?php endif; ?>
                                        <span class="fw-bold text-dark" style="font-size: 14px;"><?= $currency_symbol . number_format($w_price, 2); ?></span>
                                    </div>
                                    <form action="<?= site_url('cart/add'); ?>" method="POST" class="mt-auto">
                                        <input type="hidden" name="product_id" value="<?= $w_item['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-sm w-100 rounded-pill fw-semibold py-1" style="font-size: 12px; border: 1px solid #2874f0; color: #2874f0; background: #ffffff;">
                                            Move to cart
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </section>
        <!-- /Section cart -->

        <script>
        var CURRENCY = '<?= $currency_symbol; ?>';

        window.onCartLoginSuccess = function(data) {
            window.IS_USER_LOGGED_IN = true;

            var btnAction = document.getElementById('btn-address-action');
            if (!btnAction) {
                btnAction = document.querySelector('.cart-address-card button');
            }
            if (btnAction) {
                btnAction.textContent = 'Change';
                btnAction.onclick = function(e) {
                    if (e) e.stopPropagation();
                    openAddressModal();
                };
            }

            var headerBox = document.getElementById('cart-address-header-box');
            if (headerBox) {
                headerBox.onclick = function() { openAddressModal(); };
            }
            var fullBox = document.getElementById('display-address-full');
            if (fullBox) {
                fullBox.onclick = function() { openAddressModal(); };
            }

            var hasAddr = data && data.has_address && data.default_address;

            if (hasAddr) {
                if (typeof window.updateDeliveryAddressUI === 'function') {
                    window.updateDeliveryAddressUI(data.default_address);
                }
                if (typeof window.refreshAddressList === 'function') {
                    window.refreshAddressList();
                }
            } else {
                var dispName = document.getElementById('display-address-name');
                if (dispName) dispName.textContent = 'Select Address';
                if (fullBox) fullBox.textContent = 'Please choose or add your delivery address';
                var phoneWrap = document.getElementById('display-address-phone-wrap');
                if (phoneWrap) phoneWrap.style.setProperty('display', 'none', 'important');

                if (typeof window.refreshAddressList === 'function') {
                    window.refreshAddressList();
                }

                setTimeout(function() {
                    if (typeof window.openAddressModal === 'function') {
                        window.openAddressModal();
                        if (typeof window.toggleNewAddressForm === 'function') {
                            window.toggleNewAddressForm(true);
                        }
                    }
                }, 200);
            }
        };

        function handleAddressAction() {
            if (!window.IS_USER_LOGGED_IN) {
                if (typeof window.openLoginModal === 'function') {
                    window.openLoginModal('<?= site_url("cart"); ?>', 'Please sign in or register to select your delivery address.');
                } else {
                    window.location.href = '<?= site_url("auth/login?redirect=" . urlencode("cart")); ?>';
                }
                return;
            }
            openAddressModal();
        }
        window.handleAddressAction = handleAddressAction;

        function openAddressModal() {
            if (!window.IS_USER_LOGGED_IN) {
                handleAddressAction();
                return;
            }
            var modalEl = document.getElementById('addressModal');
            if (modalEl) {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                } else if (typeof $ !== 'undefined') {
                    $('#addressModal').modal('show');
                }
            } else {
                window.location.href = '<?= site_url("account/addresses"); ?>';
            }
        }
        window.openAddressModal = openAddressModal;

        function showCartToast(message, type) {
            type = type || 'warning';
            var existingToast = document.getElementById('cart-toast-notice');
            if (existingToast) existingToast.remove();

            var toast = document.createElement('div');
            toast.id = 'cart-toast-notice';
            var bgClass = (type === 'success') ? 'bg-success text-white' : ((type === 'danger') ? 'bg-danger text-white' : 'bg-dark text-white');
            var icon = (type === 'success') ? 'fa-circle-check' : 'fa-circle-info';
            toast.className = 'position-fixed bottom-0 end-0 p-3';
            toast.style.zIndex = '99999';
            toast.innerHTML = 
                '<div class="toast show ' + bgClass + ' border-0 shadow-lg rounded-3" role="alert" style="max-width: 400px;">' +
                    '<div class="d-flex align-items-center justify-content-between py-2 ps-3 pe-2 gap-2">' +
                        '<div class="toast-body d-flex align-items-center gap-2 p-0 flex-grow-1" style="font-size: 13.5px; line-height: 1.45;">' +
                            '<i class="fa-solid ' + icon + ' fs-5 text-warning flex-shrink-0"></i>' +
                            '<span>' + message + '</span>' +
                        '</div>' +
                        '<button type="button" class="fk-toast-close-btn" aria-label="Close" onclick="this.closest(\'#cart-toast-notice\').remove()" style="position: static !important; width: 28px !important; height: 28px !important; min-width: 28px !important; min-height: 28px !important; padding: 0 !important; margin: 0 !important; border: none !important; border-radius: 6px !important; background: transparent !important; color: inherit !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0 !important; opacity: 0.85 !important; cursor: pointer !important; box-shadow: none !important; transform: none !important;">' +
                            '<i class="fa-solid fa-xmark" style="font-size: 15px; line-height: 1;"></i>' +
                        '</button>' +
                    '</div>' +
                '</div>';
            document.body.appendChild(toast);
            setTimeout(function() {
                if (toast && toast.parentNode) toast.remove();
            }, 4000);
        }

        // 1. UPDATE QUANTITY STEPPER
        function updateCartPageQty(cartKey, delta, event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            var qtyInput = document.getElementById('cart-page-qty-' + cartKey);
            var qtyValEl = document.getElementById('cart-page-qty-val-' + cartKey);
            if (!qtyInput) return;

            if (qtyInput.dataset.busy === '1') {
                return;
            }

            var currentVal = parseInt(qtyInput.value) || 1;
            var maxVal = parseInt(qtyInput.getAttribute('data-max')) || 999;
            var newVal = currentVal + delta;

            if (newVal < 1) {
                if (confirm('Remove this item from your cart?')) {
                    removeCartPageItem(cartKey, event);
                }
                return;
            }
            if (newVal > maxVal) {
                showCartToast('Maximum purchase limit of ' + maxVal + ' units reached for this item.', 'warning');
                return;
            }

            qtyInput.dataset.busy = '1';
            qtyInput.value = newVal;
            if (qtyValEl) qtyValEl.textContent = newVal;

            var formData = new FormData();
            formData.append('cart_key', cartKey);
            formData.append('quantity', newVal);

            fetch('<?= site_url("cart/update"); ?>', {
                method: 'POST',
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                qtyInput.dataset.busy = '0';

                if (data.success) {
                    var updatedItem = null;
                    if (data.cart_items && Array.isArray(data.cart_items)) {
                        updatedItem = data.cart_items.find(function(it) {
                            return (it.cart_key === cartKey || String(it.id) === String(cartKey));
                        });
                    }

                    if (updatedItem) {
                        qtyInput.value = updatedItem.quantity;
                        if (qtyValEl) qtyValEl.textContent = updatedItem.quantity;
                        var itemTotalEl = document.getElementById('cart-page-total-' + cartKey);
                        if (itemTotalEl && updatedItem.total !== undefined) {
                            itemTotalEl.textContent = CURRENCY + parseFloat(updatedItem.total).toFixed(2);
                        }
                        var itemRegEl = document.getElementById('cart-item-reg-' + cartKey);
                        if (itemRegEl && updatedItem.regular_price !== undefined) {
                            itemRegEl.textContent = CURRENCY + (parseFloat(updatedItem.regular_price) * updatedItem.quantity).toFixed(2);
                        }
                    } else if (data.item_total !== undefined) {
                        var itemTotalEl = document.getElementById('cart-page-total-' + cartKey);
                        if (itemTotalEl) {
                            itemTotalEl.textContent = CURRENCY + parseFloat(data.item_total).toFixed(2);
                        }
                    }

                    applySummaryUpdate(data.cart_summary, data.cart_count);

                    var badges = document.querySelectorAll('.cart-count, .cart-badge');
                    badges.forEach(function(b) { b.textContent = data.cart_count || 0; });

                    if (window.renderSideCart) {
                        window.renderSideCart(data.cart_items, data.cart_summary);
                    }
                } else {
                    qtyInput.value = currentVal;
                    if (qtyValEl) qtyValEl.textContent = currentVal;
                    if (data.message) {
                        showCartToast(data.message, 'warning');
                    }
                }
            })
            .catch(function(err) {
                qtyInput.dataset.busy = '0';
                qtyInput.value = currentVal;
                if (qtyValEl) qtyValEl.textContent = currentVal;
                console.error('Error updating cart:', err);
                showCartToast('Unable to update quantity. Please try again.', 'danger');
            });
        }

        // 2. REMOVE ITEM FROM CART
        function removeCartPageItem(cartKey, event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            var row = document.getElementById('cart-row-' + cartKey);
            if (row) {
                row.style.opacity = '0.4';
                row.style.pointerEvents = 'none';
            }

            var formData = new FormData();
            formData.append('cart_key', cartKey);

            fetch('<?= site_url("cart/remove"); ?>', {
                method: 'POST',
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    if (row) {
                        row.remove();
                    }

                    applySummaryUpdate(data.cart_summary, data.cart_count);

                    var badges = document.querySelectorAll('.cart-count, .cart-badge');
                    badges.forEach(function(b) { b.textContent = data.cart_count || 0; });

                    if (!data.cart_count || data.cart_count === 0) {
                        var cartWrapper = document.getElementById('cart-content-wrapper');
                        var emptyWrapper = document.getElementById('cart-empty-wrapper');
                        if (cartWrapper) cartWrapper.classList.add('d-none');
                        if (emptyWrapper) emptyWrapper.classList.remove('d-none');
                        var fixedBar = document.getElementById('mobile-cart-fixed-bar');
                        if (fixedBar) fixedBar.remove();
                    }

                    if (window.renderSideCart) {
                        window.renderSideCart(data.cart_items, data.cart_summary);
                    }
                }
            })
            .catch(function(err) {
                console.error('Error removing cart item:', err);
                if (row) {
                    row.style.opacity = '1';
                    row.style.pointerEvents = 'auto';
                }
            });
        }

        // 3. MOVE ITEM TO WISHLIST
        function moveCartPageItemToWishlist(cartKey, productId, event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            if (!window.IS_USER_LOGGED_IN) {
                if (typeof window.openLoginModal === 'function') {
                    window.openLoginModal('<?= site_url("cart"); ?>', 'Please sign in to save items to your wishlist.');
                } else {
                    window.location.href = '<?= site_url("login"); ?>';
                }
                return;
            }

            var row = document.getElementById('cart-row-' + cartKey);
            if (row) {
                row.style.opacity = '0.4';
                row.style.pointerEvents = 'none';
            }

            var formData = new FormData();
            formData.append('cart_key', cartKey);
            formData.append('product_id', productId);

            fetch('<?= site_url("cart/move_to_wishlist"); ?>', {
                method: 'POST',
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    if (row) row.remove();
                    showCartToast('Item moved to your wishlist!', 'success');
                    applySummaryUpdate(data.cart_summary, data.cart_count);

                    var badges = document.querySelectorAll('.cart-count, .cart-badge');
                    badges.forEach(function(b) { b.textContent = data.cart_count || 0; });

                    if (!data.cart_count || data.cart_count === 0) {
                        var cartWrapper = document.getElementById('cart-content-wrapper');
                        var emptyWrapper = document.getElementById('cart-empty-wrapper');
                        if (cartWrapper) cartWrapper.classList.add('d-none');
                        if (emptyWrapper) emptyWrapper.classList.remove('d-none');
                        var fixedBar = document.getElementById('mobile-cart-fixed-bar');
                        if (fixedBar) fixedBar.remove();
                    }

                    if (window.renderSideCart) {
                        window.renderSideCart(data.cart_items, data.cart_summary);
                    }
                } else if (data.require_login) {
                    if (row) {
                        row.style.opacity = '1';
                        row.style.pointerEvents = 'auto';
                    }
                    if (typeof window.openLoginModal === 'function') {
                        window.openLoginModal('<?= site_url("cart"); ?>', data.message || 'Please sign in.');
                    }
                } else {
                    if (row) {
                        row.style.opacity = '1';
                        row.style.pointerEvents = 'auto';
                    }
                    showCartToast(data.message || 'Could not move item to wishlist.', 'danger');
                }
            })
            .catch(function(err) {
                console.error('Error moving item to wishlist:', err);
                if (row) {
                    row.style.opacity = '1';
                    row.style.pointerEvents = 'auto';
                }
            });
        }

        // 4. BUY NOW FROM CART
        function buyNowFromCart(cartKey, event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            handleCartCheckout(event);
        }

        function escapeCartHtml(str) {
            if (!str) return '';
            var div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        // 5. UPDATE SUMMARY AND BARS
        function applySummaryUpdate(summary, count) {
            if (!summary) return;

            // MRP Label & Value
            var mrpLabel = document.getElementById('order-summary-mrp-label');
            if (mrpLabel) {
                mrpLabel.textContent = (summary.tax_inclusive == 1) ? 'MRP (incl. of all taxes)' : 'MRP';
            }
            var mrpEl = document.getElementById('order-summary-mrp');
            if (mrpEl) {
                mrpEl.textContent = CURRENCY + parseFloat(summary.mrp_total || summary.subtotal || 0).toFixed(2);
            }

            // Discount on MRP
            var mrpDiscRow = document.getElementById('order-summary-mrp-discount-row');
            var mrpDiscVal = document.getElementById('order-summary-mrp-discount');
            if (summary.mrp_discount && summary.mrp_discount > 0) {
                if (mrpDiscRow) mrpDiscRow.classList.remove('d-none');
                if (mrpDiscVal) mrpDiscVal.textContent = '-' + CURRENCY + parseFloat(summary.mrp_discount).toFixed(2);
            } else if (mrpDiscRow) {
                mrpDiscRow.classList.add('d-none');
            }

            // Coupon Discount
            var discountRow = document.getElementById('order-summary-discount-row');
            var discountVal = document.getElementById('order-summary-discount');
            var discountCode = document.getElementById('applied-coupon-code');
            if (summary.discount && summary.discount > 0) {
                if (discountRow) discountRow.classList.remove('d-none');
                if (discountVal) discountVal.textContent = '-' + CURRENCY + parseFloat(summary.discount).toFixed(2);
                if (discountCode && summary.coupon) discountCode.textContent = summary.coupon.code;
            } else if (discountRow) {
                discountRow.classList.add('d-none');
            }

            // Dynamic Shipping Breakdown
            var shipBreakdown = document.getElementById('order-summary-shipping-breakdown');
            if (shipBreakdown) {
                if (summary.shipping_charges && summary.shipping_charges.length > 0) {
                    var sHtml = '';
                    summary.shipping_charges.forEach(function(sc) {
                        var scVal = parseFloat(sc.value || 0);
                        var valHtml = (scVal <= 0) 
                            ? '<span class="text-success fw-semibold">FREE</span>' 
                            : '<span class="text-dark fw-medium">' + CURRENCY + scVal.toFixed(2) + '</span>';
                        sHtml += '<div class="d-flex justify-content-between align-items-center mb-2" style="font-size: 14px;">' +
                                 '  <span class="text-secondary">' + escapeCartHtml(sc.name) + '</span>' +
                                 '  ' + valHtml +
                                 '</div>';
                    });
                    shipBreakdown.innerHTML = sHtml;
                } else {
                    var isFree = (!summary.shipping || summary.shipping <= 0);
                    var feeHtml = isFree ? '<span class="text-success fw-semibold">FREE</span>' : '<span class="text-dark fw-medium">' + CURRENCY + parseFloat(summary.shipping || 0).toFixed(2) + '</span>';
                    shipBreakdown.innerHTML = '<div class="d-flex justify-content-between align-items-center mb-2" style="font-size: 14px;"><span class="text-secondary">Delivery Charges</span>' + feeHtml + '</div>';
                }
            }

            // Tax Breakdown (Exclusive display mode)
            var taxRow = document.getElementById('order-summary-tax-row');
            var taxLabel = document.getElementById('order-summary-tax-label');
            var taxVal = document.getElementById('order-summary-tax');
            if (taxRow) {
                if (!summary.tax_inclusive && summary.tax_enabled && summary.tax && summary.tax > 0) {
                    taxRow.classList.remove('d-none');
                    if (taxLabel) taxLabel.textContent = 'Tax (' + parseFloat(summary.tax_rate_percent || 0) + '%)';
                    if (taxVal) taxVal.textContent = CURRENCY + parseFloat(summary.tax).toFixed(2);
                } else {
                    taxRow.classList.add('d-none');
                }
            }

            // Total Amount
            var grandTotalEl = document.getElementById('order-summary-grandtotal');
            if (grandTotalEl) {
                grandTotalEl.textContent = CURRENCY + parseFloat(summary.total || 0).toFixed(2);
            }

            // Total Savings
            var savingsVal = document.getElementById('order-summary-savings');
            var savingsBanner = document.getElementById('order-summary-savings-banner');
            if (summary.total_savings && summary.total_savings > 0) {
                if (savingsBanner) savingsBanner.classList.remove('d-none');
                if (savingsVal) savingsVal.textContent = CURRENCY + parseFloat(summary.total_savings).toFixed(2);
            } else if (savingsBanner) {
                savingsBanner.classList.add('d-none');
            }

            // Badges
            document.querySelectorAll('#cart-counter, .cart-count, .count-box.cart-count, .count-cart, .side-cart-count').forEach(function(badge) {
                badge.textContent = count !== undefined ? count : (summary.item_count || 0);
            });
        }

        // 6. CHECKOUT FLOW
        function handleCartCheckout(e) {
            if (e && e.preventDefault) e.preventDefault();

            if (!window.IS_USER_LOGGED_IN) {
                if (typeof window.openLoginModal === 'function') {
                    window.openLoginModal('<?= site_url("cart"); ?>', 'Please sign in to proceed to buy.');
                } else {
                    window.location.href = '<?= site_url("auth/login?redirect=" . urlencode("cart")); ?>';
                }
                return false;
            }

            window.location.href = '<?= site_url("checkout"); ?>';
            return true;
        }

        // Auto-open login modal if user was redirected with auth prompt
        if (window.location.search.indexOf('login=1') !== -1 || window.location.search.indexOf('auth=required') !== -1) {
            window.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    if (typeof window.openLoginModal === 'function') {
                        window.openLoginModal('<?= site_url("cart"); ?>', 'Please sign in to proceed to buy.');
                    }
                }, 350);
            });
        }
        </script>
