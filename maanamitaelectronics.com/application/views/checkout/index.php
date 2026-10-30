        <!-- Checkout Section (Matches IMG_20260922_115257.jpeg and Flipkart theme) -->
        <section class="py-3 py-md-4 bg-light min-vh-100 tf-checkout-page checkout-content-container">
            <div class="container">

                <!-- Desktop Breadcrumb (Hidden on Mobile) -->
                <nav aria-label="breadcrumb" class="mb-3 d-none d-md-block checkout-breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="<?= site_url('home'); ?>" class="text-muted text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url('cart'); ?>" class="text-muted text-decoration-none">Cart</a></li>
                        <li class="breadcrumb-item active text-dark fw-semibold">Confirm details</li>
                    </ol>
                </nav>

                <?php 
                    $curr_user_name = $this->is_logged_in() 
                        ? trim(($current_user['first_name'] ?? '') . ' ' . ($current_user['last_name'] ?? ''))
                        : 'Guest Shopper';
                    $curr_user_contact = $this->is_logged_in()
                        ? (!empty($current_user['phone']) ? '+91 ' . $current_user['phone'] : ($current_user['email'] ?? ''))
                        : 'Sign in for saved addresses';

                    $target_addr = !empty($active_address) ? $active_address : (!empty($default_address) ? $default_address : NULL);
                    $disp_name   = $target_addr ? trim(($target_addr['first_name'] ?? '') . ' ' . ($target_addr['last_name'] ?? '')) : '';
                    $disp_pin    = $target_addr['postcode'] ?? '';
                    $disp_tag    = !empty($target_addr['company']) ? strtoupper($target_addr['company']) : 'HOME';
                    $disp_addr   = $target_addr 
                        ? implode(', ', array_filter([$target_addr['address_1'] ?? '', $target_addr['address_2'] ?? '', $target_addr['city'] ?? '', ($target_addr['state'] ?? '') . ($disp_pin ? ' - ' . $disp_pin : '')]))
                        : '';
                    $disp_phone  = !empty($target_addr['phone']) ? $target_addr['phone'] : ($current_user['phone'] ?? '');

                    $item_count = $cart_summary['item_count'] ?? count($cart_items);
                ?>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show rounded-2 py-2 px-3 small mb-3" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $this->session->flashdata('error'); ?>
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show rounded-2 py-2 px-3 small mb-3" role="alert">
                        <i class="fa-solid fa-circle-check me-1"></i> <?= $this->session->flashdata('success'); ?>
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

        <style>
        /* Flipkart Exact Stepper (Matches IMG_20260922_115257.jpeg) */
        .fk-checkout-stepper-container {
            background: #ffffff;
            border-bottom: 1px solid #f0f0f0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
            padding: 14px 12px 12px 12px;
            margin-bottom: 16px;
            border-radius: 4px;
        }
        @media (min-width: 768px) {
            .fk-checkout-stepper-container {
                border-radius: 8px;
                border: 1px solid #e5e7eb;
                padding: 16px 24px 14px 24px;
                margin-bottom: 20px;
            }
        }
        .fk-stepper-track {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            position: relative;
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
        }
        @media (min-width: 768px) {
            .fk-stepper-track {
                max-width: 720px;
            }
        }
        @media (min-width: 992px) {
            .fk-stepper-track {
                max-width: 860px;
            }
        }
        /* Connecting line between steps running behind badges */
        .fk-stepper-track::before {
            content: '';
            position: absolute;
            top: 12px;
            left: 36px;
            right: 36px;
            height: 1px;
            background-color: #e0e0e0;
            z-index: 1;
        }
        .fk-step-item {
            flex: 0 0 72px;
            width: 72px;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            z-index: 2;
        }
        .fk-step-item:nth-child(2) {
            flex: 0 0 96px;
            width: 96px;
        }
        .fk-step-badge-wrap {
            position: relative;
            z-index: 2;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            background-color: #ffffff;
            padding: 0 8px;
        }
        .fk-step-badge {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            line-height: 1;
            box-sizing: border-box;
        }
        /* Step 1: Address (completed) */
        .fk-step-item.is-completed .fk-step-badge {
            border: 1.5px solid #2874f0;
            background-color: #ffffff;
            color: #2874f0;
        }
        .fk-step-item.is-completed .fk-step-badge i {
            font-size: 11px;
        }
        .fk-step-item.is-completed .fk-step-title {
            color: #475569;
            font-weight: 500;
        }

        /* Step 2: Confirm details (current active) */
        .fk-step-item.is-active .fk-step-badge {
            border: none;
            background-color: #2874f0;
            color: #ffffff;
            font-weight: 700;
            font-size: 12px;
        }
        .fk-step-item.is-active .fk-step-title {
            color: #111827;
            font-weight: 700;
        }

        /* Step 3: Payment (pending) */
        .fk-step-item.is-pending .fk-step-badge {
            border: 1px solid #d1d5db;
            background-color: #ffffff;
            color: #94a3b8;
            font-weight: 400;
            font-size: 12px;
        }
        .fk-step-item.is-pending .fk-step-title {
            color: #94a3b8;
            font-weight: 400;
        }

        .fk-step-title {
            margin-top: 6px;
            font-size: 12px;
            line-height: 1.2;
            letter-spacing: -0.1px;
            white-space: nowrap;
        }
        </style>

        <!-- Top Horizontal Stepper (Exact match to IMG_20260922_115257.jpeg) -->
        <div class="fk-checkout-stepper-container">
            <div class="fk-stepper-track">
                <!-- Step 1: Address (Completed) -->
                <div class="fk-step-item is-completed">
                    <div class="fk-step-badge-wrap">
                        <div class="fk-step-badge">
                            <i class="fa-solid fa-check"></i>
                        </div>
                    </div>
                    <span class="fk-step-title">Address</span>
                </div>

                <!-- Step 2: Confirm details (Active) -->
                <div class="fk-step-item is-active">
                    <div class="fk-step-badge-wrap">
                        <div class="fk-step-badge">2</div>
                    </div>
                    <span class="fk-step-title">Confirm details</span>
                </div>

                <!-- Step 3: Payment (Pending) -->
                <div class="fk-step-item is-pending">
                    <div class="fk-step-badge-wrap">
                        <div class="fk-step-badge">3</div>
                    </div>
                    <span class="fk-step-title">Payment</span>
                </div>
            </div>
        </div>

                <div class="row g-4">
                    <!-- Left Column: Checkout Content -->
                    <div class="col-lg-8">
                        <div class="d-flex flex-column gap-3">

                            <!-- Delivering to Header & Address Card (Same as cart page) -->
                            <div class="cart-address-section mb-3">
                                <h5 class="fw-bold text-dark mb-2" style="font-size: 18px; letter-spacing: -0.2px;">Delivering to</h5>
                                <div class="cart-address-card p-3 p-sm-4 bg-white border shadow-sm tf-delivery-address-card" id="step-card-address" style="background-color: #ffffff !important; border-radius: 16px; border-color: #e5e7eb !important;">
                                    <div id="address-box-selected">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <div class="d-flex align-items-center gap-2 min-w-0" <?= empty($this->is_logged_in()) ? 'style="cursor: pointer;" onclick="openAddressModal()"' : ''; ?>>
                                                <i class="fa-solid fa-house text-dark flex-shrink-0" style="font-size: 16px;"></i>
                                                <strong class="fw-bold text-dark text-truncate" id="display-address-name" style="font-size: 17px;">
                                                    <?= html_escape($disp_name ?: (!empty($this->is_logged_in()) ? 'Select Address' : 'Sign In to Select Address')); ?>
                                                </strong>
                                                <span class="badge bg-light text-secondary border text-uppercase d-none" id="display-address-tag"><?= html_escape($disp_tag); ?></span>
                                            </div>
                                            <button type="button" class="btn btn-link p-0 text-decoration-none fw-bold flex-shrink-0 ms-2" style="color: #1a73e8; font-size: 16px;" onclick="openAddressModal()">
                                                <?= !empty($this->is_logged_in()) ? 'Change' : 'Sign In'; ?>
                                            </button>
                                        </div>
                                        <div class="text-dark mb-2" style="font-size: 14px; line-height: 1.45; color: #212529 !important;" id="display-address-full">
                                            <?= !empty($this->is_logged_in()) ? html_escape($disp_addr ?: 'Choose or add your shipping address') : 'Please sign in or register to set your delivery address'; ?>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 text-dark" style="font-size: 14px; color: #212529 !important; <?= empty($disp_phone) ? 'display: none !important;' : ''; ?>" id="display-address-phone-wrap">
                                            <i class="fa-solid fa-phone text-dark" style="font-size: 13px; transform: rotate(15deg);"></i>
                                            <span id="display-address-phone" class="fw-normal"><?= html_escape($disp_phone); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Products List (Matches IMG_20260922_115257.jpeg) -->
                            <div class="d-flex flex-column gap-3" id="checkout-items-list">
                                <?php foreach ($cart_items as $item): 
                                    $item_img = !empty($item['image']) 
                                        ? (strpos($item['image'], 'http') === 0 ? $item['image'] : base_url('assets/images/' . $item['image']))
                                        : base_url('assets/images/products/womens/women-1.jpg');
                                    $item_key = $item['cart_key'] ?? $item['id'];
                                    $item_price = (float) $item['price'];
                                    $item_reg = !empty($item['regular_price']) ? (float) $item['regular_price'] : round($item_price * 1.30, 2);
                                    if ($item_reg <= $item_price) {
                                        $item_reg = round($item_price * 1.30, 2);
                                    }
                                    $disc_pct = !empty($item['discount_percent']) ? $item['discount_percent'] : round((($item_reg - $item_price) / $item_reg) * 100);

                                    // Shipping delivery date calculation
                                    $delivery_days = ($shipping_method === 'express') ? 2 : 4;
                                    $delivery_date = date('D, j M', strtotime("+{$delivery_days} days"));
                                ?>
                                        <div class="card border rounded-3 p-3 bg-white shadow-sm checkout-product-row cart-product-card" id="checkout-item-<?= html_escape($item_key); ?>" style="width: 100%; max-width: 100%; min-width: 0; overflow: hidden; box-sizing: border-box;">
                                    <div class="d-flex gap-3 cart-product-row align-items-start" style="width: 100%; max-width: 100%; min-width: 0; overflow: hidden;">
                                        <!-- Left Column: Thumbnail + Quantity Stepper -->
                                        <div class="d-flex flex-column align-items-center flex-shrink-0" style="width: 92px;">
                                            <a href="<?= site_url('product/' . $item['slug']); ?>" class="d-block rounded overflow-hidden mb-2 bg-light text-center border" style="width: 90px; height: 90px;">
                                                <img src="<?= $item_img; ?>" alt="<?= html_escape($item['title']); ?>" class="w-100 h-100 object-fit-contain" onerror="this.src='<?= base_url('assets/images/products/womens/women-1.jpg'); ?>'">
                                            </a>
                                            <!-- Quantity Stepper -->
                                            <div class="cart-qty-stepper">
                                                <button type="button" class="qty-step-btn btn-decrease" onclick="updateCheckoutItemQty('<?= html_escape($item_key); ?>', -1, event)">-</button>
                                                <span class="qty-val" id="checkout-qty-val-<?= html_escape($item_key); ?>"><?= $item['quantity']; ?></span>
                                                <input type="hidden" id="checkout-qty-<?= html_escape($item_key); ?>" value="<?= $item['quantity']; ?>" data-max="<?= !empty($item['stock_max']) ? (int) $item['stock_max'] : 999; ?>">
                                                <button type="button" class="qty-step-btn btn-increase" onclick="updateCheckoutItemQty('<?= html_escape($item_key); ?>', 1, event)">+</button>
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
                                                    <span class="text-muted text-decoration-line-through small" id="checkout-item-reg-<?= html_escape($item_key); ?>"><?= $currency_symbol . number_format($item_reg * $item['quantity'], 2); ?></span>
                                                <?php endif; ?>
                                                <span class="fw-bold text-dark fs-6" id="checkout-item-total-<?= html_escape($item_key); ?>"><?= $currency_symbol . number_format($item_price * $item['quantity'], 2); ?></span>
                                            </div>

                                            <!-- Stock Alert -->
                                            <?php if (!empty($item['stock_max']) && $item['stock_max'] < 10): ?>
                                                <div class="text-danger small fw-medium mt-1" style="font-size: 11px;">Only <?= $item['stock_max']; ?> left</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Dispatch Note Row (Same as cart page) -->
                                    <div class="p-2 px-3 rounded-2 bg-light d-flex align-items-center gap-2 mt-3 mb-3 text-secondary small border" style="font-size: 12px; line-height: 1.35;">
                                        <i class="fa-solid fa-truck-fast text-primary fs-6"></i>
                                        <span><?= !empty($cart_summary['shipping_dispatch_note']) ? html_escape($cart_summary['shipping_dispatch_note']) : 'Dispatched within 24-48 hours with courier tracking.'; ?></span>
                                    </div>

                                    <!-- Cancellation Policy Strip (Matching IMG_20260922_115257.jpeg) -->
                                    <div class="rounded-2 p-2 px-3 mt-2 d-flex align-items-baseline gap-2 small border" style="background-color: #fffbeb; border-color: #fef3c7 !important; font-size: 12px; color: #92400e;">
                                        <i class="fa-solid fa-circle-info"></i>
                                        <span style="line-height: normal;">Cancellation is allowed up to 24 hours after placing the order.</span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Savings zone / Coupon Card (Matches IMG_20260922_115257.jpeg) -->
                            <div class="card border rounded-3 p-3 bg-white shadow-sm">
                                <div class="mb-2">
                                    <span class="text-muted small">Have a promo code?</span>
                                </div>
                                <form action="<?= site_url('cart/apply_coupon'); ?>" method="POST" class="d-flex gap-2 mt-1" id="checkoutCouponForm">
                                    <input type="text" name="coupon_code" class="form-control rounded-2" placeholder="Enter coupon code" value="<?= html_escape($cart_summary['coupon']['code'] ?? ''); ?>" required style="font-size: 13px;">
                                    <button type="submit" class="btn btn-dark fw-semibold px-3 flex-shrink-0" style="font-size: 13px; border-radius: 8px;">Apply</button>
                                </form>
                            </div>

                        </div>
                    </div>

                    <!-- Right Column: Price Details Sidebar (Matches IMG_20260922_115257.jpeg & cart_price.png) -->
                    <div class="col-lg-4">
                        <div class="sticky-top position-relative" style="top: 20px;" id="price-details-card">
                            <!-- Loader Overlay for Realtime Updates -->
                            <div id="price-details-loader" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none align-items-center justify-content-center rounded-3" style="z-index: 20;">
                                <div class="text-center p-3">
                                    <div class="spinner-border text-primary mb-2" role="status" style="width: 2rem; height: 2rem;">
                                        <span class="visually-hidden">Updating...</span>
                                    </div>
                                    <div class="small fw-bold text-secondary">Updating prices...</div>
                                </div>
                            </div>

                            <!-- Price Details Card (Same as cart page) -->
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

                                <!-- Continue Button (Same as cart page Proceed To Buy) -->
                                <div class="mb-3">
                                    <button type="button" class="btn btn-proceed-buy w-100 py-3 fw-bold" id="btn-sidebar-continue" onclick="proceedToPaymentPage()" style="font-size: 16px;">
                                        Continue
                                    </button>
                                </div>

                                <!-- Safe & Secure Payments Trust Badge -->
                                <div class="d-flex align-items-center gap-2 pt-3 border-top text-secondary small" style="font-size: 12px; line-height: 1.35;">
                                    <i class="fa-solid fa-shield-halved text-muted fs-4"></i>
                                    <span>Safe and secure payments. Easy returns. 100% Authentic products.</span>
                                </div>
                            </div>

                            <!-- Disclaimer below card -->
                            <div class="text-muted small px-1 text-center text-lg-start mb-3" style="font-size: 11px; line-height: 1.4;">
                                By proceeding, you confirm that you're above 18 years of age and you agree to our Terms of Use and Privacy Policy.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <style>
        .breadcrumb-item + .breadcrumb-item::before {
            content: var(--bs-breadcrumb-divider, "/") !important;
        }

        /* Yellow Proceed to Buy / Continue Button matching cart and Flipkart theme */
        .btn-proceed-buy {
            background: #ffc200 !important;
            border: none !important;
            border-radius: 8px !important;
            color: #1e2022 !important;
            font-weight: 700 !important;
            transition: all 0.2s ease !important;
        }
        .btn-proceed-buy:hover {
            background: #e5ae00 !important;
            color: #1e2022 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 194, 0, 0.35) !important;
        }
        .btn-proceed-buy:active {
            background: #cca000 !important;
            transform: translateY(0);
        }

        /* Mobile Sticky Bottom Bar */
        .mobile-checkout-fixed-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1040;
            background: #ffffff;
            box-shadow: 0 -4px 18px rgba(0, 0, 0, 0.12);
        }

        @media (max-width: 767px) {
            .checkout-breadcrumb,
            .page-title,
            .breadcrumbs,
            .tf-breadcrumb {
                display: none !important;
            }
        }

        /* Cart Product Card & Stepper (Matches cart page) */
        .cart-product-card {
            border-color: #e5e7eb !important;
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
            width: 100% !important;
            max-width: 100% !important;
            max-height: 2.7em !important;
            margin-bottom: 6px !important;
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

        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        </style>

        <script>
        var SITE_NAME = <?= json_encode($site_name); ?>;
        var SITE_URL = <?= json_encode(site_url()); ?>;
        var CURRENCY = <?= json_encode($currency_symbol); ?>;

        function hasDeliveryAddress() {
            var fullAddr = document.getElementById('display-address-full');
            if (fullAddr) {
                var txt = fullAddr.textContent.trim();
                return txt.length > 0 && txt.indexOf('Choose or add') === -1 && txt.indexOf('sign in or register') === -1;
            }
            return false;
        }

        function proceedToPaymentPage() {
            var hasAddr = <?= !empty($target_addr) ? 'true' : 'false'; ?> || hasDeliveryAddress();

            if (!hasAddr) {
                showCheckoutToast('Please select or add a delivery address before proceeding to payment.', 'warning');
                var addrCard = document.getElementById('step-card-address');
                if (addrCard) {
                    addrCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    addrCard.classList.add('border-warning', 'shadow-sm');
                    setTimeout(function() {
                        addrCard.classList.remove('border-warning', 'shadow-sm');
                    }, 2500);
                }
                if (typeof window.openAddressModal === 'function') {
                    setTimeout(function() {
                        window.openAddressModal();
                    }, 300);
                }
                return;
            }

            var btnSide = document.getElementById('btn-sidebar-continue');
            if (btnSide) {
                btnSide.disabled = true;
                btnSide.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading Payment...';
            }

            window.location.href = SITE_URL + 'checkout/payment';
        }

        function showCheckoutToast(message, type) {
            type = type || 'danger';
            var existingToast = document.getElementById('checkout-toast-notice');
            if (existingToast) existingToast.remove();

            var toast = document.createElement('div');
            toast.id = 'checkout-toast-notice';
            var bgClass = (type === 'success') ? 'bg-success text-white' : ((type === 'warning') ? 'bg-warning text-dark' : 'bg-danger text-white');
            var icon = (type === 'success') ? 'fa-circle-check' : ((type === 'warning') ? 'fa-triangle-exclamation' : 'fa-circle-exclamation');
            toast.className = 'position-fixed bottom-0 end-0 p-3';
            toast.style.zIndex = '99999';
            toast.innerHTML = 
                '<div class="toast show ' + bgClass + ' border-0 shadow-lg rounded-3" role="alert" style="max-width: 400px;">' +
                    '<div class="d-flex align-items-center justify-content-between py-2 ps-3 pe-2 gap-2">' +
                        '<div class="toast-body d-flex align-items-center gap-2 p-0 flex-grow-1" style="font-size: 13.5px; line-height: 1.45;">' +
                            '<i class="fa-solid ' + icon + ' fs-5 flex-shrink-0"></i>' +
                            '<span>' + message + '</span>' +
                        '</div>' +
                        '<button type="button" class="fk-toast-close-btn" aria-label="Close" onclick="this.closest(\'#checkout-toast-notice\').remove()" style="position: static !important; width: 28px !important; height: 28px !important; min-width: 28px !important; min-height: 28px !important; padding: 0 !important; margin: 0 !important; border: none !important; border-radius: 6px !important; background: transparent !important; color: inherit !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0 !important; opacity: 0.85 !important; cursor: pointer !important; box-shadow: none !important; transform: none !important;">' +
                            '<i class="fa-solid fa-xmark" style="font-size: 15px; line-height: 1;"></i>' +
                        '</button>' +
                    '</div>' +
                '</div>';
            document.body.appendChild(toast);
            setTimeout(function() {
                if (toast && toast.parentNode) toast.remove();
            }, 5000);
        }

        function showPriceDetailsLoader(show) {
            var loader = document.getElementById('price-details-loader');
            if (!loader) return;
            if (show) {
                loader.classList.remove('d-none');
                loader.classList.add('d-flex');
            } else {
                loader.classList.remove('d-flex');
                loader.classList.add('d-none');
            }
        }

        function formatPrice(val, cur) {
            cur = cur || CURRENCY || '₹';
            var num = parseFloat(val) || 0;
            return cur + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function applyCartSummary(summary) {
            if (!summary) return;
            var cur = summary.currency_symbol || CURRENCY || '₹';

            // MRP Total
            var mrpEl = document.getElementById('order-summary-mrp') || document.getElementById('side-mrp-total');
            if (mrpEl && summary.mrp_total !== undefined) {
                mrpEl.textContent = formatPrice(summary.mrp_total, cur);
            }

            // Discount on MRP
            var discEl = document.getElementById('order-summary-mrp-discount') || document.getElementById('side-discount-total');
            var discRow = document.getElementById('order-summary-mrp-discount-row') || document.getElementById('side-discount-row');
            if (summary.mrp_discount !== undefined) {
                var dVal = parseFloat(summary.mrp_discount) || 0;
                if (discEl) discEl.textContent = '-' + formatPrice(dVal, cur);
                if (discRow) {
                    if (dVal > 0) discRow.classList.remove('d-none');
                    else discRow.classList.add('d-none');
                }
            }

            // Coupon Row
            var couponRow = document.getElementById('order-summary-discount-row') || document.getElementById('side-coupon-row');
            var couponVal = document.getElementById('order-summary-discount') || document.getElementById('side-coupon-val');
            var couponCode = document.getElementById('applied-coupon-code');
            if (summary.coupon && summary.coupon.code && couponCode) {
                couponCode.textContent = summary.coupon.code;
            }
            if (couponRow && couponVal) {
                var couponAmt = parseFloat(summary.discount) || 0;
                if (couponAmt > 0) {
                    couponVal.textContent = '-' + formatPrice(couponAmt, cur);
                    couponRow.classList.remove('d-none');
                } else {
                    couponRow.classList.add('d-none');
                }
            }

            // Delivery charges
            var delivEl = document.getElementById('standard-shipping-price') || document.getElementById('side-delivery-charge');
            if (delivEl) {
                if (parseFloat(summary.shipping) === 0) {
                    delivEl.innerHTML = '<span class="text-success fw-semibold">FREE</span>';
                } else {
                    delivEl.innerHTML = '<span class="text-dark fw-medium">' + formatPrice(summary.shipping, cur) + '</span>';
                }
            }

            // Tax
            var taxEl = document.getElementById('order-summary-tax') || document.getElementById('side-tax-fee');
            var taxRow = document.getElementById('order-summary-tax-row') || document.getElementById('side-tax-row');
            if (taxEl && summary.tax !== undefined) {
                var tVal = parseFloat(summary.tax) || 0;
                taxEl.textContent = formatPrice(tVal, cur);
                if (taxRow) {
                    if (tVal > 0) taxRow.classList.remove('d-none');
                    else taxRow.classList.add('d-none');
                }
            }

            // Total Payable
            var totalEl = document.getElementById('order-summary-grandtotal') || document.getElementById('side-total-payable');
            if (totalEl) totalEl.textContent = formatPrice(summary.total, cur);

            // Savings Banner
            var savingsAlert = document.getElementById('order-summary-savings-banner') || document.getElementById('side-savings-alert');
            var savingsVal = document.getElementById('order-summary-savings') || document.getElementById('side-savings-val');
            var savings = parseFloat(summary.total_savings) || 0;
            if (savingsVal) savingsVal.textContent = formatPrice(savings, cur);
            if (savingsAlert) {
                if (savings > 0) savingsAlert.classList.remove('d-none');
                else savingsAlert.classList.add('d-none');
            }
        }

        function updateCheckoutItemQty(cartKey, delta) {
            var qtyInput = document.getElementById('checkout-qty-' + cartKey);
            if (!qtyInput) return;
            var cur = parseInt(qtyInput.value) || 1;
            var next = cur + delta;
            if (next < 1) {
                showCheckoutToast('Minimum order quantity is 1.', 'warning');
                return;
            }

            showPriceDetailsLoader(true);

            var formData = new FormData();
            formData.append('cart_key', cartKey);
            formData.append('quantity', next);

            fetch(SITE_URL + 'cart/update', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                showPriceDetailsLoader(false);
                if (data.success) {
                    var updatedItem = null;
                    if (data.cart_items && Array.isArray(data.cart_items)) {
                        updatedItem = data.cart_items.find(function(it) {
                            return (it.cart_key === cartKey || it.id == cartKey);
                        });
                    }
                    var finalQty = updatedItem ? updatedItem.quantity : next;
                    qtyInput.value = finalQty;
                    var qtyVal = document.getElementById('checkout-qty-val-' + cartKey);
                    if (qtyVal) qtyVal.textContent = finalQty;

                    if (updatedItem) {
                        var totalEl = document.getElementById('checkout-item-total-' + cartKey);
                        if (totalEl) totalEl.textContent = formatPrice(updatedItem.total, CURRENCY);
                        var regEl = document.getElementById('checkout-item-reg-' + cartKey);
                        if (regEl && updatedItem.regular_price) {
                            regEl.textContent = formatPrice(updatedItem.regular_price * finalQty, CURRENCY);
                        }
                    }

                    applyCartSummary(data.cart_summary);

                    if (data.message) {
                        showCheckoutToast(data.message, 'warning');
                    }

                    var badges = document.querySelectorAll('.cart-count, .cart-badge');
                    badges.forEach(function(b) { b.textContent = data.cart_count || 0; });
                } else if (data.message) {
                    showCheckoutToast(data.message, 'warning');
                }
            })
            .catch(function(err) {
                showPriceDetailsLoader(false);
                console.error('Error updating quantity:', err);
                showCheckoutToast('Unable to update quantity. Please try again.', 'danger');
            });
        }

        function removeCheckoutItem(cartKey) {
            showPriceDetailsLoader(true);

            fetch(SITE_URL + 'cart/remove/' + encodeURIComponent(cartKey), {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                showPriceDetailsLoader(false);
                if (data.success) {
                    var row = document.getElementById('checkout-item-' + cartKey);
                    if (row) {
                        row.style.transition = 'all 0.3s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'scale(0.97)';
                        setTimeout(function() {
                            row.remove();
                            var remaining = document.querySelectorAll('.checkout-product-row');
                            if (remaining.length === 0) {
                                window.location.href = SITE_URL + 'cart';
                            }
                        }, 300);
                    }

                    applyCartSummary(data.cart_summary);

                    var badges = document.querySelectorAll('.cart-count, .cart-badge');
                    badges.forEach(function(b) { b.textContent = data.cart_count || 0; });

                    showCheckoutToast('Item removed from your order.', 'success');
                } else if (data.message) {
                    showCheckoutToast(data.message, 'danger');
                }
            })
            .catch(function(err) {
                showPriceDetailsLoader(false);
                console.error('Error removing item:', err);
                showCheckoutToast('Unable to remove item. Please try again.', 'danger');
            });
        }
        </script>
