        <!-- Breadcrumbs -->
        <section class="py-3 bg-light border-bottom d-none d-md-block">
            <div class="container">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="<?= site_url(); ?>" class="text-secondary text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url('cart'); ?>" class="text-secondary text-decoration-none">Cart</a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url('checkout'); ?>" class="text-secondary text-decoration-none">Checkout</a></li>
                        <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Payment</li>
                    </ol>
                </nav>
            </div>
        </section>

        <!-- Main Payment View -->
        <section class="py-3 py-md-4 bg-light min-vh-100">
            <div class="container">
                <?php
                    $currency_symbol = $this->store_settings['currency_symbol'] ?? '₹';

                    $target_addr = $default_address;
                    $disp_name   = $target_addr ? trim(($target_addr['first_name'] ?? '') . ' ' . ($target_addr['last_name'] ?? '')) : '';
                    $disp_pin    = $target_addr['postcode'] ?? '';
                    $disp_tag    = !empty($target_addr['company']) ? strtoupper($target_addr['company']) : 'HOME';
                    $disp_addr   = $target_addr 
                        ? implode(', ', array_filter([$target_addr['address_1'] ?? '', $target_addr['address_2'] ?? '', $target_addr['city'] ?? '', ($target_addr['state'] ?? '') . ($disp_pin ? ' - ' . $disp_pin : '')]))
                        : '';
                    $disp_phone  = $target_addr['phone'] ?? '';

                    $item_count = $cart_summary['item_count'] ?? count($cart_items);

                    $cod_allowed = isset($cod_eligible) ? $cod_eligible : true;
                    $non_cod = !empty($non_cod_items) ? $non_cod_items : [];

                    // Dynamic COD instructions from admin "Checkout Instructions / Note" field
                    $cod_instructions = 'Pay with cash upon physical delivery of your package.';
                    foreach ($gateways as $g) {
                        if ($g['gateway_code'] === 'cod') {
                            if (!empty($g['credentials']['instructions'])) {
                                $cod_instructions = trim($g['credentials']['instructions']);
                            }
                            break;
                        }
                    }

                    $gateway_codes = array_column($gateways, 'gateway_code');
                    $selected_gateway = (!empty($default_gateway) && in_array($default_gateway, $gateway_codes))
                        ? $default_gateway
                        : (!empty($gateway_codes) ? $gateway_codes[0] : 'cod');

                    // If COD is disabled and default happens to be COD, switch to first available online gateway
                    if (!$cod_allowed && $selected_gateway === 'cod') {
                        foreach ($gateway_codes as $gc) {
                            if ($gc !== 'cod') {
                                $selected_gateway = $gc;
                                break;
                            }
                        }
                    }

                    // Build unified dynamic configuration for each gateway
                    $payment_configs = [];
                    foreach ($gateways as $gw) {
                        $code = $gw['gateway_code'];
                        $is_cod = ($code === 'cod');

                        if ($is_cod) {
                            $name = !empty($gw['gateway_name']) ? $gw['gateway_name'] : 'Cash on Delivery';
                            $sub  = '';
                            $icon = 'fa-regular fa-money-bill-1 text-success';
                            $msg  = $cod_instructions;
                        } else {
                            // Uses the dynamic Payment Gateway name from settings/database
                            $name = !empty($gw['gateway_name']) ? $gw['gateway_name'] : 'Payment Gateway';
                            $sub  = 'Pay securely via ' . $name;
                            if ($code === 'razorpay') {
                                $icon = 'fa-solid fa-credit-card text-primary';
                            } elseif ($code === 'stripe') {
                                $icon = 'fa-regular fa-credit-card text-primary';
                            } elseif ($code === 'payu') {
                                $icon = 'fa-solid fa-building-columns text-info';
                            } else {
                                $icon = 'fa-solid fa-wallet text-secondary';
                            }
                            $msg  = 'Safe and secure payment powered by ' . $name . '. Complete your order using UPI, Debit & Credit Cards, NetBanking, or digital wallets.';
                        }

                        $payment_configs[$code] = [
                            'code'    => $code,
                            'name'    => $name,
                            'sub'     => $sub,
                            'icon'    => $icon,
                            'is_cod'  => $is_cod,
                            'message' => $msg
                        ];
                    }

                    $formatted_total_disp = $currency_symbol . (floor($cart_summary['total']) == $cart_summary['total'] ? number_format($cart_summary['total'], 0) : number_format($cart_summary['total'], 2));
                ?>

                <style>
                /* Flipkart Exact Stepper */
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

                /* Mobile Top Amount Card (Matches c:\CMR\SS\amount.png) */
                .fk-mobile-amount-card {
                    background-color: #f0f5ff;
                    border: 1px solid #dbeafe;
                    border-radius: 8px;
                    padding: 12px 16px;
                    cursor: pointer;
                    user-select: none;
                    transition: background-color 0.2s ease;
                }
                .fk-mobile-amount-card:hover {
                    background-color: #e6effe;
                }
                .fk-amount-label {
                    color: #2874f0;
                    font-size: 15px;
                    font-weight: 600;
                }
                .fk-amount-chevron {
                    color: #2874f0;
                    font-size: 13px;
                    transition: transform 0.25s ease;
                }
                .fk-amount-chevron.is-open {
                    transform: rotate(180deg);
                }
                .fk-amount-value {
                    color: #2874f0;
                    font-size: 18px;
                    font-weight: 700;
                }

                /* Complete Payment Unified Card */
                .fk-complete-payment-card {
                    background: #ffffff;
                    border: 1px solid #e5e7eb;
                    border-radius: 12px;
                    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
                    overflow: hidden;
                }
                .fk-card-header {
                    background: #ffffff;
                    padding: 16px 20px;
                    border-bottom: 1px solid #f1f5f9;
                }
                .fk-back-arrow {
                    color: #111827;
                    font-size: 18px;
                    padding: 4px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    transition: color 0.15s ease;
                }
                .fk-back-arrow:hover {
                    color: #2874f0;
                }
                @media (max-width: 767.98px) {
                    .fk-back-arrow {
                        display: none !important;
                    }
                }
                .fk-header-title {
                    font-size: 18px;
                    font-weight: 700;
                    color: #111827;
                    letter-spacing: -0.2px;
                    margin: 0;
                }
                .fk-secure-pill {
                    border: 1px solid #e2e8f0;
                    border-radius: 6px;
                    padding: 0px 4px;
                    background-color: #f8fafc;
                    font-size: 12px;
                    font-weight: 700;
                    color: #475569;
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                }

                /* Payment Method Item */
                .fk-pay-method-item {
                    border: 1px solid #e5e7eb;
                    border-radius: 8px;
                    padding: 14px 16px;
                    background-color: #ffffff;
                    display: flex;
                    align-items: center;
                    gap: 14px;
                    cursor: pointer;
                    transition: all 0.15s ease;
                    user-select: none;
                }
                .fk-pay-method-item:hover {
                    background-color: #f9fafb;
                    border-color: #d1d5db;
                }
                .fk-pay-method-item.is-active {
                    background-color: #f0f2f5 !important;
                    border-color: #cbd5e1 !important;
                }
                .fk-pay-icon-box {
                    width: 32px;
                    height: 32px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 18px;
                    flex-shrink: 0;
                }
                .fk-pay-name {
                    font-size: 14px;
                    font-weight: 600;
                    color: #1e293b;
                    line-height: 1.3;
                }
                .fk-pay-sub {
                    font-size: 12px;
                    color: #64748b;
                    margin-top: 3px;
                    line-height: 1.3;
                }
                .fk-unavailable-tag {
                    font-size: 11.5px;
                    color: #64748b;
                    background-color: #f1f5f9;
                    border: 1px solid #e2e8f0;
                    border-radius: 4px;
                    padding: 2px 6px;
                    font-weight: 400;
                }
                .is-disabled-method .fk-pay-name {
                    color: #64748b;
                }

                /* Desktop Middle Column Action Box */
                .fk-middle-action-card {
                    background-color: #f8fafc;
                    border: 1px solid #edf2f7;
                    border-radius: 8px;
                    padding: 20px 18px;
                    height: 100%;
                    display: flex;
                    flex-direction: column;
                }
                .fk-middle-msg {
                    font-size: 13.5px;
                    color: #4b5563;
                    line-height: 1.5;
                    margin-bottom: 16px;
                    white-space: pre-line;
                }
                .btn-fk-place-order {
                    background-color: #ffd814 !important;
                    border: 1px solid #ffd814 !important;
                    border-radius: 8px !important;
                    color: #111827 !important;
                    font-weight: 700 !important;
                    font-size: 15px !important;
                    padding: 12px 20px !important;
                    width: 100% !important;
                    transition: all 0.2s ease !important;
                    cursor: pointer !important;
                    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
                }
                .btn-fk-place-order:hover {
                    background-color: #f7ca00 !important;
                    border-color: #f7ca00 !important;
                    color: #111827 !important;
                }
                .btn-fk-place-order:disabled,
                .btn-fk-place-order.btn-disabled {
                    background-color: #e2e8f0 !important;
                    border-color: #e2e8f0 !important;
                    color: #94a3b8 !important;
                    cursor: not-allowed !important;
                    box-shadow: none !important;
                }

                /* Right Column Price Details */
                .fk-price-details-card {
                    border: 1px solid #e5e7eb;
                    border-radius: 8px;
                    background: #ffffff;
                }

                /* Mobile Accordion Styles (Matches c:\CMR\SS\cod.png) */
                @media (max-width: 991.98px) {
                    .fk-pay-method-item {
                        flex-direction: column;
                        align-items: stretch;
                        padding: 14px 16px;
                        border-radius: 12px;
                    }
                    .fk-pay-method-item.is-active {
                        background-color: #f4f6f8 !important;
                        border-color: #cbd5e1 !important;
                    }
                    .fk-pay-header-row {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        width: 100%;
                        cursor: pointer;
                    }
                    .fk-pay-chevron {
                        display: inline-block;
                        font-size: 13px;
                        color: #1e293b;
                        transition: transform 0.25s ease;
                        flex-shrink: 0;
                    }
                    .fk-pay-method-item.is-active .fk-pay-chevron {
                        transform: rotate(180deg);
                    }
                    .fk-pay-mobile-body {
                        display: none;
                        background-color: #ffffff;
                        border-radius: 8px;
                        padding: 16px;
                        margin-top: 14px;
                        border: 1px solid #eef2f6;
                    }
                    .fk-pay-method-item.is-active .fk-pay-mobile-body {
                        display: block;
                    }
                }

                /* Hide mobile-only elements on desktop */
                @media (min-width: 992px) {
                    .fk-pay-chevron {
                        display: none !important;
                    }
                    .fk-pay-mobile-body {
                        display: none !important;
                    }
                }

                /* Toast Close Button Normalization (Overrides template global button / .btn-close styles) */
                .fk-toast-close-btn,
                #checkout-toast-notice .btn-close {
                    position: static !important;
                    top: auto !important;
                    right: auto !important;
                    inset-inline-end: auto !important;
                    transform: none !important;
                    width: 28px !important;
                    height: 28px !important;
                    min-width: 28px !important;
                    min-height: 28px !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    border: none !important;
                    border-radius: 6px !important;
                    background: transparent !important;
                    color: inherit !important;
                    display: inline-flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    flex-shrink: 0 !important;
                    opacity: 0.8 !important;
                    box-shadow: none !important;
                    cursor: pointer !important;
                }
                .fk-toast-close-btn:hover,
                #checkout-toast-notice .btn-close:hover {
                    opacity: 1 !important;
                    transform: none !important;
                    background: rgba(0, 0, 0, 0.12) !important;
                }
                </style>

                <!-- Top Horizontal Stepper (Step 3: Payment active) -->
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

                        <!-- Step 2: Confirm details (Completed) -->
                        <div class="fk-step-item is-completed">
                            <div class="fk-step-badge-wrap">
                                <div class="fk-step-badge">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                            </div>
                            <span class="fk-step-title">Confirm details</span>
                        </div>

                        <!-- Step 3: Payment (Active) -->
                        <div class="fk-step-item is-active">
                            <div class="fk-step-badge-wrap">
                                <div class="fk-step-badge">3</div>
                            </div>
                            <span class="fk-step-title">Payment</span>
                        </div>
                    </div>
                </div>

                <!-- Mobile Top Amount Dropdown Bar (Matches c:\CMR\SS\amount.png) -->
                <div class="d-block d-lg-none mb-3">
                    <div class="fk-mobile-amount-card d-flex justify-content-between align-items-center" 
                         id="fk-mobile-amount-toggle" 
                         onclick="toggleMobilePriceDetails()" 
                         role="button">
                        <div class="d-flex align-items-center fk-amount-label">
                            <span>Total Amount</span>
                            <i class="fa-solid fa-chevron-down ms-2 fk-amount-chevron" id="fk-amount-chevron"></i>
                        </div>
                        <div class="fk-amount-value">
                            <?= $formatted_total_disp; ?>
                        </div>
                    </div>
                    
                    <!-- Collapsible Full Price Details on Mobile -->
                    <div class="collapse mt-2" id="mobilePriceDetailsCollapse">
                        <div class="card border rounded-3 p-3 bg-white shadow-sm fk-price-details-card" id="cart-price-details-card-mob">
                            <h6 class="fw-bold text-secondary text-uppercase border-bottom pb-2 mb-3" style="font-size: 13px; letter-spacing: 0.5px;">Price Details</h6>
                            
                            <div class="d-flex justify-content-between align-items-center mb-2" style="font-size: 14px;">
                                <span class="text-secondary">
                                    <?= (!empty($cart_summary['tax_inclusive']) && (int)$cart_summary['tax_inclusive'] === 1) ? 'MRP (incl. of all taxes)' : 'MRP'; ?>
                                </span>
                                <span class="text-dark fw-medium"><?= $currency_symbol . number_format($cart_summary['mrp_total'], 2); ?></span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 text-success <?= (empty($cart_summary['mrp_discount']) || $cart_summary['mrp_discount'] <= 0) ? 'd-none' : ''; ?>" style="font-size: 14px;">
                                <span>Discount on MRP</span>
                                <span class="fw-semibold">-<?= $currency_symbol . number_format($cart_summary['mrp_discount'], 2); ?></span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 text-success <?= (empty($cart_summary['discount']) || $cart_summary['discount'] <= 0) ? 'd-none' : ''; ?>" style="font-size: 14px;">
                                <span>Coupon Discount (<span id="applied-coupon-code-mob"><?= html_escape($cart_summary['coupon']['code'] ?? ''); ?></span>)</span>
                                <span class="fw-semibold">-<?= $currency_symbol . number_format($cart_summary['discount'] ?? 0, 2); ?></span>
                            </div>

                            <!-- Dynamic Shipping Charges Breakdown -->
                            <div>
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
                                        <span class="<?= ($cart_summary['shipping'] <= 0) ? 'text-success fw-semibold' : 'text-dark fw-medium'; ?>">
                                            <?= ($cart_summary['shipping'] <= 0) ? 'FREE' : $currency_symbol . number_format($cart_summary['shipping'], 2); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tax Breakdown (Exclusive display mode) -->
                            <?php
                                $show_tax_row = (empty($cart_summary['tax_inclusive']) && !empty($cart_summary['tax_enabled']) && !empty($cart_summary['tax']) && $cart_summary['tax'] > 0);
                            ?>
                            <div class="d-flex justify-content-between align-items-center mb-2 <?= $show_tax_row ? '' : 'd-none'; ?>" style="font-size: 14px;">
                                <span class="text-secondary">Tax (<?= (float)($cart_summary['tax_rate_percent'] ?? 0); ?>%)</span>
                                <span class="text-dark fw-medium"><?= $currency_symbol . number_format($cart_summary['tax'] ?? 0, 2); ?></span>
                            </div>

                            <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold text-dark" style="font-size: 16px;">Total Amount</span>
                                <span class="fw-bold text-dark" style="font-size: 18px;"><?= $currency_symbol . number_format($cart_summary['total'], 2); ?></span>
                            </div>

                            <div class="p-2 px-3 rounded-2 d-flex align-items-center gap-2 mb-3 <?= (empty($cart_summary['total_savings']) || $cart_summary['total_savings'] <= 0) ? 'd-none' : ''; ?>" style="background-color: #e8f8f0; color: #16a34a; font-size: 13px; font-weight: 600;">
                                <i class="fa-solid fa-tag"></i>
                                <span>You'll Save <?= $currency_symbol . number_format($cart_summary['total_savings'], 2); ?> on this order</span>
                            </div>

                            <!-- Safe & Secure Payments Trust Badge -->
                            <div class="d-flex align-items-center gap-2 pt-3 border-top text-secondary small" style="font-size: 12px; line-height: 1.35;">
                                <i class="fa-solid fa-shield-halved text-muted fs-4"></i>
                                <span>Safe and secure payments. Easy returns. 100% Authentic products.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Complete Payment Card (Matches c:\CMR\SS\payment_page.png and c:\CMR\SS\cod.png) -->
                <div class="fk-complete-payment-card mb-4">
                    <!-- Header with Back Arrow and Title -->
                    <div class="fk-card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <a href="<?= site_url('checkout'); ?>" class="fk-back-arrow d-none d-md-inline-flex" title="Back to confirm details">
                                <i class="fa-solid fa-arrow-left"></i>
                            </a>
                            <h5 class="fk-header-title">Complete Payment</h5>
                        </div>
                        <div class="fk-secure-pill">
                            <i class="fa-solid fa-lock text-secondary"></i>
                            <span>100% Secure</span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-3 p-lg-4">
                        <div class="row g-3 g-xl-4 align-items-start">
                            
                            <!-- PART 1: Payment Methods (Accordion on mobile, left column on desktop) -->
                            <div class="col-12 col-lg-4">
                                <div class="fk-payment-methods-list d-flex flex-column gap-2">
                                    <?php 
                                        // Sort to ensure online gateways come first and COD is ordered cleanly
                                        $sorted_gateways = [];
                                        $cod_entry = null;
                                        foreach ($gateways as $g) {
                                            if ($g['gateway_code'] === 'cod') {
                                                $cod_entry = $g;
                                            } else {
                                                $sorted_gateways[] = $g;
                                            }
                                        }
                                        if ($cod_entry) {
                                            $sorted_gateways[] = $cod_entry;
                                        }

                                        if (empty($sorted_gateways)):
                                    ?>
                                        <div class="alert alert-warning border rounded-2 p-3 mb-0 small">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> No payment gateways are currently active.
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($sorted_gateways as $gw): 
                                            $code = $gw['gateway_code'];
                                            $cfg = $payment_configs[$code] ?? null;
                                            if (!$cfg) continue;

                                            $is_cod = $cfg['is_cod'];
                                            $is_disabled = ($is_cod && !$cod_allowed);
                                            $is_active = ($code === $selected_gateway);
                                        ?>
                                            <div class="fk-pay-method-item <?= $is_active ? 'is-active' : ''; ?> <?= $is_disabled ? 'is-disabled-method' : ''; ?>"
                                                 id="fk-pay-method-<?= $code; ?>"
                                                 data-method="<?= $code; ?>">
                                                
                                                <!-- Clickable Header Row -->
                                                <div class="fk-pay-header-row" onclick="selectPaymentMethod('<?= $code; ?>')">
                                                    <div class="d-flex align-items-center gap-3 flex-grow-1">
                                                        <div class="fk-pay-icon-box">
                                                            <i class="<?= $cfg['icon']; ?> fs-5"></i>
                                                        </div>

                                                        <div class="fk-pay-info flex-grow-1">
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <span class="fk-pay-name"><?= html_escape($cfg['name']); ?></span>
                                                                <?php if ($is_disabled): ?>
                                                                    <span class="fk-unavailable-tag">Unavailable <i class="fa-regular fa-circle-question ms-1"></i></span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <?php if (!empty($cfg['sub'])): ?>
                                                                <div class="fk-pay-sub"><?= html_escape($cfg['sub']); ?></div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <!-- Dropdown Chevron Icon (Mobile only, matches c:\CMR\SS\cod.png) -->
                                                    <i class="fa-solid fa-chevron-down fk-pay-chevron ms-2"></i>
                                                </div>

                                                <!-- Mobile Collapsible Body (Matches c:\CMR\SS\cod.png) -->
                                                <div class="fk-pay-mobile-body" id="fk-mobile-body-<?= $code; ?>">
                                                    <p class="fk-middle-msg mb-3" id="fk-mobile-msg-<?= $code; ?>">
                                                        <?= html_escape($cfg['message']); ?>
                                                    </p>
                                                    <?php if ($is_cod && !$cod_allowed): ?>
                                                        <div class="alert alert-danger border-0 bg-danger-subtle text-danger py-2 px-3 small rounded-2 mb-3">
                                                            <i class="fa-solid fa-circle-exclamation me-1"></i> Cash on Delivery is unavailable because item(s) in your cart (<?= !empty($non_cod) ? '<strong>' . html_escape(implode(', ', $non_cod)) . '</strong>' : 'certain items'; ?>) do not support COD. Please select an online payment option.
                                                        </div>
                                                    <?php endif; ?>
                                                    <button type="button" 
                                                            class="btn btn-fk-place-order btn-mobile-place-order <?= $is_disabled ? 'btn-disabled' : ''; ?>" 
                                                            onclick="triggerSelectedPayment('<?= $code; ?>')"
                                                            <?= $is_disabled ? 'disabled' : ''; ?>>
                                                        Place Order
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- PART 2: Desktop Middle Action Column (Hidden on mobile) -->
                            <div class="col-lg-4 d-none d-lg-block">
                                <div class="fk-middle-action-card">
                                    <div class="mb-3">
                                        <p class="fk-middle-msg" id="fk-middle-msg-text">
                                            <!-- Dynamic text updated by JS -->
                                        </p>
                                        <div class="alert alert-danger border-0 bg-danger-subtle text-danger py-2 px-3 small rounded-2 mb-3 d-none" id="fk-middle-alert-box">
                                            <!-- Error notice if COD disabled -->
                                        </div>
                                        <div id="fk-middle-banner-slot">
                                            <!-- Sandbox test banners if needed -->
                                        </div>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-fk-place-order" id="btn-middle-place-order" onclick="triggerSelectedPayment()">
                                            Place Order
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- PART 3: Desktop Right Price Details Sidebar (Hidden on mobile) -->
                            <div class="col-lg-4 d-none d-lg-block">
                                <div class="card border rounded-3 p-3 bg-white shadow-sm fk-price-details-card" id="cart-price-details-card">
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
                                        <span>Coupon Discount (<span id="applied-coupon-code"><?= html_escape($cart_summary['coupon']['code'] ?? ''); ?></span>)</span>
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

                                    <!-- Safe & Secure Payments Trust Badge -->
                                    <div class="d-flex align-items-center gap-2 pt-3 border-top text-secondary small" style="font-size: 12px; line-height: 1.35;">
                                        <i class="fa-solid fa-shield-halved text-muted fs-4"></i>
                                        <span>Safe and secure payments. Easy returns. 100% Authentic products.</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- Hidden PayU Auto-submit Form Container -->
        <div id="payu-dynamic-container" style="display: none;"></div>

        <!-- External SDK Scripts -->
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

        <script>
        var SITE_NAME = <?= json_encode($site_name); ?>;
        var SITE_URL = <?= json_encode(site_url()); ?>;
        var CURRENCY = <?= json_encode($currency_symbol); ?>;
        var COD_ELIGIBLE = <?= $cod_allowed ? 'true' : 'false'; ?>;
        var NON_COD_ITEMS = <?= json_encode($non_cod); ?>;
        var CURRENT_METHOD = <?= json_encode($selected_gateway); ?>;

        var PAYMENT_METHODS_CONFIG = <?= json_encode($payment_configs); ?>;

        function toggleMobilePriceDetails() {
            var collapseEl = document.getElementById('mobilePriceDetailsCollapse');
            var chevronEl = document.getElementById('fk-amount-chevron');
            if (!collapseEl) return;

            if (collapseEl.classList.contains('show')) {
                collapseEl.classList.remove('show');
                if (chevronEl) chevronEl.classList.remove('is-open');
            } else {
                collapseEl.classList.add('show');
                if (chevronEl) chevronEl.classList.add('is-open');
            }
        }

        function selectPaymentMethod(method) {
            var conf = PAYMENT_METHODS_CONFIG[method];
            if (!conf) return;

            CURRENT_METHOD = method;

            // Highlight selected item in Left Column and open its accordion on mobile
            document.querySelectorAll('.fk-pay-method-item').forEach(function(item) {
                if (item.getAttribute('data-method') === method) {
                    item.classList.add('is-active');
                } else {
                    item.classList.remove('is-active');
                }
            });

            // Update desktop middle column elements
            var msgEl = document.getElementById('fk-middle-msg-text');
            var alertEl = document.getElementById('fk-middle-alert-box');
            var btnEl = document.getElementById('btn-middle-place-order');

            if (msgEl) {
                msgEl.textContent = conf.message || '';
            }

            if (conf.is_cod) {
                if (!COD_ELIGIBLE) {
                    if (alertEl) {
                        alertEl.classList.remove('d-none');
                        var itemsStr = NON_COD_ITEMS.join(', ');
                        alertEl.innerHTML = '<i class="fa-solid fa-circle-exclamation me-1"></i> Cash on Delivery is unavailable because item(s) in your cart (' + (itemsStr ? '<strong>' + itemsStr + '</strong>' : 'certain items') + ') do not support COD. Please select an online payment option.';
                    }
                    if (btnEl) {
                        btnEl.disabled = true;
                        btnEl.classList.add('btn-disabled');
                        btnEl.innerHTML = 'Place Order';
                    }
                } else {
                    if (alertEl) alertEl.classList.add('d-none');
                    if (btnEl) {
                        btnEl.disabled = false;
                        btnEl.classList.remove('btn-disabled');
                        btnEl.innerHTML = 'Place Order';
                    }
                }
            } else {
                if (alertEl) alertEl.classList.add('d-none');
                if (btnEl) {
                    btnEl.disabled = false;
                    btnEl.classList.remove('btn-disabled');
                    btnEl.innerHTML = 'Place Order';
                }
            }
        }

        function triggerSelectedPayment(method) {
            if (method) {
                CURRENT_METHOD = method;
            }
            var conf = PAYMENT_METHODS_CONFIG[CURRENT_METHOD];
            if (!conf) {
                showCheckoutToast('Please select a payment method before proceeding.', 'warning');
                return;
            }

            if (conf.is_cod && !COD_ELIGIBLE) {
                showCheckoutToast('Cash on Delivery is unavailable for your cart items. Please select an online payment method.', 'danger');
                return;
            }

            initiateGatewayPayment(CURRENT_METHOD);
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

        function initiateGatewayPayment(method) {
            var allBtns = document.querySelectorAll('#btn-middle-place-order, .btn-mobile-place-order');
            allBtns.forEach(function(b) {
                b.disabled = true;
                b.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
            });

            var formData = new FormData();
            formData.append('payment_method', method);

            fetch(SITE_URL + 'checkout/create_order_ajax', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (!data.success) {
                    if (data.require_login) {
                        showCheckoutToast(data.message || 'Please sign in to complete your purchase.', 'warning');
                        setTimeout(function() {
                            window.location.href = SITE_URL + 'cart?login=1';
                        }, 800);
                        allBtns.forEach(function(b) {
                            var isCod = b.closest('#fk-pay-method-cod') !== null;
                            b.disabled = (isCod && !COD_ELIGIBLE);
                            b.innerHTML = 'Place Order';
                        });
                        return;
                    }
                    showCheckoutToast(data.message || 'Unable to place order. Please check address details.', 'danger');
                    allBtns.forEach(function(b) {
                        var isCod = b.closest('#fk-pay-method-cod') !== null;
                        b.disabled = (isCod && !COD_ELIGIBLE);
                        b.innerHTML = 'Place Order';
                    });
                    return;
                }

                if (method === 'cod') {
                    window.location.href = data.redirect_url;
                } else if (method === 'razorpay') {
                    launchRazorpaySDK(data);
                } else if (method === 'stripe') {
                    launchStripePayment(data);
                } else if (method === 'payu') {
                    launchPayUGateway(data);
                }
            })
            .catch(function(err) {
                console.error('Error initiating payment:', err);
                showCheckoutToast('An error occurred. Please try again.', 'danger');
                allBtns.forEach(function(b) {
                    var isCod = b.closest('#fk-pay-method-cod') !== null;
                    b.disabled = (isCod && !COD_ELIGIBLE);
                    b.innerHTML = 'Place Order';
                });
            });
        }

        // ==========================================
        // 1. RAZORPAY SDK LAUNCHER
        // ==========================================
        function launchRazorpaySDK(data) {
            var rzpData = data.razorpay;
            var order = data.order || {};

            var options = {
                "key": rzpData.key_id,
                "amount": rzpData.amount_subunit,
                "currency": rzpData.currency || (order.currency || "<?= html_escape($currency_code ?? 'USD'); ?>"),
                "name": SITE_NAME,
                "description": "Payment for Order #" + data.order_number,
                "prefill": {
                    "name": order.customer_name || "",
                    "email": order.customer_email || "",
                    "contact": order.customer_phone || ""
                },
                "theme": {
                    "color": "#2874f0"
                },
                "handler": function(response) {
                    var verifyForm = new FormData();
                    verifyForm.append('order_number', data.order_number);
                    verifyForm.append('razorpay_payment_id', response.razorpay_payment_id);
                    verifyForm.append('razorpay_order_id', response.razorpay_order_id || (rzpData.razorpay_order_id || ''));
                    verifyForm.append('razorpay_signature', response.razorpay_signature || '');

                    fetch(SITE_URL + 'checkout/verify_razorpay_ajax', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: verifyForm
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(verifyRes) {
                        if (verifyRes.success && verifyRes.redirect_url) {
                            window.location.href = verifyRes.redirect_url;
                        } else {
                            handleRazorpayFailure(data, verifyRes.message || 'Payment verification failed.');
                        }
                    })
                    .catch(function(err) {
                        handleRazorpayFailure(data, 'Verification network error.');
                    });
                },
                "modal": {
                    "ondismiss": function() {
                        var allBtns = document.querySelectorAll('#btn-middle-place-order, .btn-mobile-place-order');
                        allBtns.forEach(function(b) {
                            var isCod = b.closest('#fk-pay-method-cod') !== null;
                            b.disabled = (isCod && !COD_ELIGIBLE);
                            b.innerHTML = 'Place Order';
                        });
                    }
                }
            };

            if (rzpData.razorpay_order_id && rzpData.razorpay_order_id.indexOf('order_') === 0 && rzpData.razorpay_order_id.indexOf('order_test_') !== 0) {
                options.order_id = rzpData.razorpay_order_id;
            }

            try {
                var rzp = new Razorpay(options);
                rzp.on('payment.failed', function(response) {
                    var desc = (response && response.error && response.error.description) 
                        ? response.error.description 
                        : 'Razorpay payment was not completed.';
                    handleRazorpayFailure(data, desc);
                });
                rzp.open();
            } catch(e) {
                handleRazorpayFailure(data, e.message || 'Razorpay checkout could not be opened.');
            }
        }

        function handleRazorpayFailure(data, errDesc) {
            var allBtns = document.querySelectorAll('#btn-middle-place-order, .btn-mobile-place-order');
            allBtns.forEach(function(b) {
                b.disabled = false;
                b.innerHTML = 'Place Order';
            });

            var container = document.getElementById('fk-middle-banner-slot');
            if (container) {
                var oldBanner = document.getElementById('rzp-failure-banner');
                if (oldBanner) oldBanner.remove();

                var banner = document.createElement('div');
                banner.id = 'rzp-failure-banner';
                banner.className = 'alert alert-warning border rounded-2 p-3 mb-3 shadow-sm text-start';
                banner.innerHTML = 
                    '<div class="d-flex align-items-start gap-2 mb-2">' +
                        '<i class="fa-solid fa-triangle-exclamation text-warning fs-5 mt-1"></i>' +
                        '<div>' +
                            '<strong class="text-dark d-block">Razorpay Sandbox / Test Notice</strong>' +
                            '<span class="small text-secondary">' + (errDesc || 'Payment could not be completed with the current test keys.') + '</span>' +
                        '</div>' +
                    '</div>' +
                    '<div class="d-flex gap-2 flex-wrap mt-2">' +
                        '<button type="button" class="btn btn-sm btn-dark fw-bold" onclick="simulateSandboxPayment(\'' + data.order_number + '\')">' +
                            '<i class="fa-solid fa-circle-check me-1"></i> Complete in Sandbox Mode' +
                        '</button>' +
                        '<button type="button" class="btn btn-sm btn-outline-secondary" onclick="triggerSelectedPayment()">' +
                            '<i class="fa-solid fa-rotate-right me-1"></i> Retry' +
                        '</button>' +
                    '</div>';
                container.appendChild(banner);
            }
        }

        function simulateSandboxPayment(orderNumber) {
            var allBtns = document.querySelectorAll('#btn-middle-place-order, .btn-mobile-place-order');
            allBtns.forEach(function(b) {
                b.disabled = true;
                b.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Confirming Test Payment...';
            });

            var simPaymentId = 'pay_sim_' + Math.random().toString(36).substring(2, 12);
            var verifyForm = new FormData();
            verifyForm.append('order_number', orderNumber);
            verifyForm.append('razorpay_payment_id', simPaymentId);
            verifyForm.append('razorpay_order_id', '');
            verifyForm.append('razorpay_signature', 'sig_sim');

            fetch(SITE_URL + 'checkout/verify_razorpay_ajax', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: verifyForm
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success && res.redirect_url) {
                    window.location.href = res.redirect_url;
                }
            })
            .catch(function(err) {
                console.error('Error in sandbox payment:', err);
                allBtns.forEach(function(b) {
                    var isCod = b.closest('#fk-pay-method-cod') !== null;
                    b.disabled = (isCod && !COD_ELIGIBLE);
                    b.innerHTML = 'Place Order';
                });
            });
        }

        // ==========================================
        // 2. STRIPE GATEWAY REDIRECT
        // ==========================================
        function launchStripePayment(data) {
            if (data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                window.location.href = SITE_URL + 'payment/stripe/' + data.order_number;
            }
        }

        // ==========================================
        // 3. PAYU GATEWAY AUTO-SUBMISSION
        // ==========================================
        function launchPayUGateway(data) {
            var p = data.payu;
            var container = document.getElementById('payu-dynamic-container');
            container.innerHTML = 
                '<form action="' + p.action_url + '" method="POST" id="payu-dynamic-form">' +
                '<input type="hidden" name="key" value="' + p.key + '">' +
                '<input type="hidden" name="hash" value="' + p.hash + '">' +
                '<input type="hidden" name="txnid" value="' + p.txnid + '">' +
                '<input type="hidden" name="amount" value="' + p.amount + '">' +
                '<input type="hidden" name="currency" value="' + (p.currency || "INR") + '">' +
                '<input type="hidden" name="firstname" value="' + p.firstname + '">' +
                '<input type="hidden" name="email" value="' + p.email + '">' +
                '<input type="hidden" name="phone" value="' + p.phone + '">' +
                '<input type="hidden" name="productinfo" value="' + p.productinfo + '">' +
                '<input type="hidden" name="surl" value="' + p.surl + '">' +
                '<input type="hidden" name="furl" value="' + p.furl + '">' +
                '</form>';
            document.getElementById('payu-dynamic-form').submit();
        }

        // Initialize state on page load
        document.addEventListener('DOMContentLoaded', function() {
            selectPaymentMethod(CURRENT_METHOD);
        });
        </script>
