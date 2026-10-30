<?php
$g = function($key, $default = '') use ($settings) {
    return isset($settings[$key]) ? $settings[$key] : $default;
};
$curr_tab = isset($active_tab) ? $active_tab : 'currency';
$act_gw = isset($active_payment_gateway) ? $active_payment_gateway : 'stripe';
$root_url = rtrim(main_site_url(), '/') . '/';

$logo_val = $g('landing_site_logo', 'uploads/branding/logo.webp');
$logo_preview = !empty($logo_val) ? (strpos($logo_val, 'http') === 0 ? $logo_val : $root_url . ltrim($logo_val, '/')) : $root_url . 'uploads/branding/logo.webp';

$fav_val = $g('landing_site_favicon', 'uploads/branding/codeulas_logo_small.webp');
$fav_preview = !empty($fav_val) ? (strpos($fav_val, 'http') === 0 ? $fav_val : $root_url . ltrim($fav_val, '/')) : $root_url . 'uploads/branding/codeulas_logo_small.webp';
?>
<div class="container-fluid px-4 py-4">
    <!-- Top Action Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-3 border-bottom border-secondary border-opacity-25">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-warning text-dark fw-bold font-monospace px-2 py-1">PLATFORM GOVERNANCE</span>
                <span class="badge bg-secondary">GATEWAYS &amp; SYSTEM</span>
            </div>
            <h3 class="fw-bold text-white mb-0 font-serif">Platform Settings, Gateways &amp; Integrations</h3>
            <p class="text-muted small mb-0">Configure dynamic currency, single-active payment gateways, transactional SMTP email, Google OAuth, and system diagnostics.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= superadmin_url('settings/backup') ?>" class="btn btn-gold btn-sm fw-bold px-3">
                <i class="fa-solid fa-database me-1"></i> Export SQL Backup
            </a>
            <a href="<?= main_site_url() ?>" target="_blank" class="btn btn-outline-light btn-sm fw-semibold px-3">
                <i class="fa-solid fa-globe me-1"></i> Public Site
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills gap-2 mb-4 p-2 rounded-3 border border-secondary border-opacity-25" style="background: #0c1322;">
        <li class="nav-item">
            <a class="nav-link <?= $curr_tab === 'currency' ? 'active' : '' ?>" href="#tabCurrency" data-bs-toggle="pill">
                <i class="fa-solid fa-coins me-1"></i> Currency, Branding &amp; General
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $curr_tab === 'gateways' ? 'active' : '' ?>" href="#tabGateways" data-bs-toggle="pill">
                <i class="fa-solid fa-credit-card me-1"></i> Payment Gateways
                <span class="badge bg-dark border border-warning text-warning ms-1 font-monospace"><?= strtoupper($act_gw) ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $curr_tab === 'smtp' ? 'active' : '' ?>" href="#tabSmtp" data-bs-toggle="pill">
                <i class="fa-solid fa-envelope me-1"></i> SMTP Email Gateway
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $curr_tab === 'oauth' ? 'active' : '' ?>" href="#tabOauth" data-bs-toggle="pill">
                <i class="fa-brands fa-google me-1"></i> Google OAuth
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $curr_tab === 'system' ? 'active' : '' ?>" href="#tabSystem" data-bs-toggle="pill">
                <i class="fa-solid fa-server me-1"></i> Diagnostics &amp; Backups
            </a>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">

        <!-- TAB 1: DYNAMIC CURRENCY & GENERAL -->
        <div class="tab-pane fade <?= $curr_tab === 'currency' ? 'show active' : '' ?>" id="tabCurrency">
            <form action="<?= superadmin_url('settings') ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_general_currency">
                <input type="hidden" name="active_tab" value="currency">

                <!-- Dynamic Currency Configuration Card -->
                <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: #111a2e; border: 1px solid rgba(255,255,255,0.08) !important;">
                    <div class="card-header bg-black bg-opacity-25 py-3 border-bottom border-secondary border-opacity-25">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="text-white fw-bold mb-0">
                                <i class="fa-solid fa-money-bill-transfer text-warning me-2"></i>Dynamic Currency Management
                            </h5>
                            <span class="badge bg-warning text-dark fw-bold font-monospace">LIVE MULTI-CURRENCY</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted small mb-4">
                            All public purchase cards, pricing badges, buttons, checkout invoices, order confirmation emails, and receipts will dynamically display using this currency format.
                        </p>

                        <!-- Live Currency Display Simulation -->
                        <div class="p-3 rounded-3 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3" style="background: #080d19; border: 1px dashed rgba(194,153,88,0.4);">
                            <div>
                                <span class="small text-muted text-uppercase fw-bold d-block">Live Format Preview:</span>
                                <h3 class="fw-bold text-warning mb-0 font-monospace" id="currencyPreview">
                                    <?= $currency_position === 'left' ? htmlspecialchars($currency_symbol) . '89' . ($currency_decimals === '2' ? '.00' : '') : '89' . ($currency_decimals === '2' ? '.00' : '') . ' ' . htmlspecialchars($currency_symbol) ?>
                                    <span class="small text-light text-opacity-50 fs-6">(<?= htmlspecialchars($currency_code) ?>)</span>
                                </h3>
                            </div>
                            <div class="text-end text-muted small">
                                <div>Salon Script: <strong class="text-light" id="previewSalon"><?= $currency_position === 'left' ? htmlspecialchars($currency_symbol) . '49' : '49 ' . htmlspecialchars($currency_symbol) ?></strong></div>
                                <div>Spa Wellness: <strong class="text-light" id="previewSpa"><?= $currency_position === 'left' ? htmlspecialchars($currency_symbol) . '69' : '69 ' . htmlspecialchars($currency_symbol) ?></strong></div>
                                <div>Complete Edition: <strong class="text-warning" id="previewUnified"><?= $currency_position === 'left' ? htmlspecialchars($currency_symbol) . '89' : '89 ' . htmlspecialchars($currency_symbol) ?></strong></div>
                            </div>
                        </div>

                        <!-- Currency Selector Dropdown -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-white small fw-bold">
                                    <i class="fa-solid fa-coins text-warning me-1"></i> Currency Dropdown
                                </label>
                                <select id="curDropdown" class="form-select font-monospace">
                                    <option value="">-- Choose Pre-configured Currency --</option>
                                    <option value="USD" data-symbol="$" data-pos="left" <?= $currency_code === 'USD' ? 'selected' : '' ?>>USD ($) - US Dollar</option>
                                    <option value="EUR" data-symbol="€" data-pos="right" <?= $currency_code === 'EUR' ? 'selected' : '' ?>>EUR (€) - Euro</option>
                                    <option value="GBP" data-symbol="£" data-pos="left" <?= $currency_code === 'GBP' ? 'selected' : '' ?>>GBP (£) - British Pound</option>
                                    <option value="INR" data-symbol="₹" data-pos="left" <?= $currency_code === 'INR' ? 'selected' : '' ?>>INR (₹) - Indian Rupee</option>
                                    <option value="CAD" data-symbol="CA$" data-pos="left" <?= $currency_code === 'CAD' ? 'selected' : '' ?>>CAD (CA$) - Canadian Dollar</option>
                                    <option value="AUD" data-symbol="AU$" data-pos="left" <?= $currency_code === 'AUD' ? 'selected' : '' ?>>AUD (AU$) - Australian Dollar</option>
                                    <option value="AED" data-symbol="AED " data-pos="left" <?= $currency_code === 'AED' ? 'selected' : '' ?>>AED (AED) - UAE Dirham</option>
                                    <option value="SAR" data-symbol="SAR " data-pos="left" <?= $currency_code === 'SAR' ? 'selected' : '' ?>>SAR (SAR) - Saudi Riyal</option>
                                    <option value="JPY" data-symbol="¥" data-pos="left" <?= $currency_code === 'JPY' ? 'selected' : '' ?>>JPY (¥) - Japanese Yen</option>
                                    <option value="SGD" data-symbol="S$" data-pos="left" <?= $currency_code === 'SGD' ? 'selected' : '' ?>>SGD (S$) - Singapore Dollar</option>
                                    <option value="CHF" data-symbol="CHF " data-pos="left" <?= $currency_code === 'CHF' ? 'selected' : '' ?>>CHF (CHF) - Swiss Franc</option>
                                    <option value="CNY" data-symbol="¥" data-pos="left" <?= $currency_code === 'CNY' ? 'selected' : '' ?>>CNY (¥) - Chinese Yuan</option>
                                    <option value="MYR" data-symbol="RM " data-pos="left" <?= $currency_code === 'MYR' ? 'selected' : '' ?>>MYR (RM) - Malaysian Ringgit</option>
                                    <option value="NZD" data-symbol="NZ$" data-pos="left" <?= $currency_code === 'NZD' ? 'selected' : '' ?>>NZD (NZ$) - New Zealand Dollar</option>
                                    <option value="BRL" data-symbol="R$ " data-pos="left" <?= $currency_code === 'BRL' ? 'selected' : '' ?>>BRL (R$) - Brazilian Real</option>
                                    <option value="ZAR" data-symbol="R " data-pos="left" <?= $currency_code === 'ZAR' ? 'selected' : '' ?>>ZAR (R) - South African Rand</option>
                                    <option value="KWD" data-symbol="KD " data-pos="left" <?= $currency_code === 'KWD' ? 'selected' : '' ?>>KWD (KD) - Kuwaiti Dinar</option>
                                    <option value="QAR" data-symbol="QR " data-pos="left" <?= $currency_code === 'QAR' ? 'selected' : '' ?>>QAR (QR) - Qatari Riyal</option>
                                    <option value="THB" data-symbol="฿" data-pos="left" <?= $currency_code === 'THB' ? 'selected' : '' ?>>THB (฿) - Thai Baht</option>
                                    <option value="PHP" data-symbol="₱" data-pos="left" <?= $currency_code === 'PHP' ? 'selected' : '' ?>>PHP (₱) - Philippine Peso</option>
                                    <option value="IDR" data-symbol="Rp " data-pos="left" <?= $currency_code === 'IDR' ? 'selected' : '' ?>>IDR (Rp) - Indonesian Rupiah</option>
                                    <option value="TRY" data-symbol="₺" data-pos="left" <?= $currency_code === 'TRY' ? 'selected' : '' ?>>TRY (₺) - Turkish Lira</option>
                                    <option value="PKR" data-symbol="₨ " data-pos="left" <?= $currency_code === 'PKR' ? 'selected' : '' ?>>PKR (₨) - Pakistani Rupee</option>
                                    <option value="BDT" data-symbol="৳" data-pos="left" <?= $currency_code === 'BDT' ? 'selected' : '' ?>>BDT (৳) - Bangladeshi Taka</option>
                                    <option value="NGN" data-symbol="₦" data-pos="left" <?= $currency_code === 'NGN' ? 'selected' : '' ?>>NGN (₦) - Nigerian Naira</option>
                                </select>
                                <small class="text-muted">Select a currency to automatically set symbol, code, and placement</small>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label text-white small fw-bold">Currency Symbol</label>
                                <input type="text" name="currency_symbol" id="curSymbolInput" class="form-control font-monospace" value="<?= htmlspecialchars($currency_symbol) ?>" required placeholder="e.g. $, ₹, €, £, A$, د.إ">
                                <small class="text-muted">Symbol placed next to price</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-white small fw-bold">Currency ISO Code</label>
                                <input type="text" name="currency_code" id="curCodeInput" class="form-control font-monospace text-uppercase" value="<?= htmlspecialchars($currency_code) ?>" required placeholder="USD, INR, EUR, GBP">
                                <small class="text-muted">3-letter ISO code for gateways</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-white small fw-bold">Symbol Position</label>
                                <select name="currency_position" id="curPosInput" class="form-select">
                                    <option value="left" <?= $currency_position === 'left' ? 'selected' : '' ?>>Left ($89.00)</option>
                                    <option value="right" <?= $currency_position === 'right' ? 'selected' : '' ?>>Right (89.00 €)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-white small fw-bold">Decimal Display</label>
                                <select name="currency_decimals" id="curDecInput" class="form-select">
                                    <option value="2" <?= $currency_decimals === '2' ? 'selected' : '' ?>>2 Decimals ($89.00 / ₹6,999.00)</option>
                                    <option value="0" <?= $currency_decimals === '0' ? 'selected' : '' ?>>0 Decimals ($89 / ₹6,999)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Website Logo & Favicon Branding -->
                <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: #111a2e; border: 1px solid rgba(255,255,255,0.08) !important;">
                    <div class="card-header bg-black bg-opacity-25 py-3 border-bottom border-secondary border-opacity-25">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="text-white fw-bold mb-0"><i class="fa-solid fa-gem text-warning me-2"></i>Website Logo &amp; Favicon Branding</h5>
                            <span class="badge bg-warning text-dark fw-bold font-monospace">GLOBAL BRANDING</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <!-- Website Logo -->
                            <div class="col-lg-6">
                                <div class="p-3 rounded-3" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.06);">
                                    <label class="form-label fw-bold text-white d-flex align-items-center justify-content-between">
                                        <span><i class="fa-solid fa-image text-warning me-1"></i> Website Logo</span>
                                        <span class="badge bg-secondary small">PNG, SVG, WEBP</span>
                                    </label>
                                    
                                    <!-- Logo Live Preview Box -->
                                    <div class="mb-3 p-3 rounded text-center d-flex align-items-center justify-content-center" style="min-height: 90px; background: #080d19; border: 1px dashed rgba(194,153,88,0.4);">
                                        <div id="logoPreviewContainer">
                                            <?php if (!empty($logo_preview)): ?>
                                                <img src="<?= htmlspecialchars($logo_preview) ?>" id="logoPreviewImg" alt="Logo Preview" style="max-height: 55px; max-width: 100%; object-fit: contain;">
                                            <?php else: ?>
                                                <div id="logoPlaceholder" class="text-muted small">
                                                    <i class="fa-solid fa-image fa-2x mb-1 d-block opacity-50"></i>
                                                    No custom logo uploaded. Default text brand icon is active.
                                                </div>
                                                <img src="" id="logoPreviewImg" alt="Logo Preview" style="max-height: 55px; max-width: 100%; object-fit: contain; display: none;">
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label text-muted small mb-1">Upload New Logo File:</label>
                                        <input type="file" name="landing_site_logo_file" id="logoFileInput" class="form-control form-control-sm" accept="image/*">
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <label class="form-label text-muted small mb-0">Or Logo Image URL / Path:</label>
                                            <button type="button" class="btn btn-link btn-sm text-danger p-0 text-decoration-none small" id="clearLogoBtn">
                                                <i class="fa-solid fa-trash-can me-1"></i> Clear
                                            </button>
                                        </div>
                                        <input type="text" name="landing_site_logo" id="logoUrlInput" class="form-control form-control-sm font-monospace" value="<?= htmlspecialchars($logo_val) ?>" placeholder="uploads/branding/logo.webp">
                                        <input type="hidden" name="clear_landing_site_logo" id="clearLogoFlag" value="0">
                                    </div>
                                </div>
                            </div>

                            <!-- Website Favicon -->
                            <div class="col-lg-6">
                                <div class="p-3 rounded-3" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.06);">
                                    <label class="form-label fw-bold text-white d-flex align-items-center justify-content-between">
                                        <span><i class="fa-solid fa-globe text-info me-1"></i> Browser Favicon</span>
                                        <span class="badge bg-secondary small">ICO, PNG, SVG, WEBP</span>
                                    </label>

                                    <!-- Favicon Live Tab Simulation -->
                                    <div class="mb-3 p-3 rounded" style="background: #080d19; border: 1px dashed rgba(194,153,88,0.4);">
                                        <div class="small text-muted mb-2">Simulated Browser Tab:</div>
                                        <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-top bg-dark text-white border border-secondary border-bottom-0 shadow-sm" style="max-width: 280px;">
                                            <div id="faviconPreviewContainer" style="width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center;">
                                                <?php if (!empty($fav_preview)): ?>
                                                    <img src="<?= htmlspecialchars($fav_preview) ?>" id="favPreviewImg" alt="Favicon" style="width: 18px; height: 18px; object-fit: contain;">
                                                <?php else: ?>
                                                    <i class="fa-solid fa-spa text-warning" id="favPlaceholder"></i>
                                                    <img src="" id="favPreviewImg" alt="Favicon" style="width: 18px; height: 18px; object-fit: contain; display: none;">
                                                <?php endif; ?>
                                            </div>
                                            <span class="small text-truncate fw-semibold" id="tabTitlePreview"><?= htmlspecialchars($g('landing_site_title', 'Luxe Salon & Spa Management')) ?></span>
                                            <i class="fa-solid fa-xmark text-muted ms-auto small"></i>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label text-muted small mb-1">Upload New Favicon File:</label>
                                        <input type="file" name="landing_site_favicon_file" id="favFileInput" class="form-control form-control-sm" accept=".ico,image/png,image/svg+xml,image/webp">
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <label class="form-label text-muted small mb-0">Or Favicon URL / Path:</label>
                                            <button type="button" class="btn btn-link btn-sm text-danger p-0 text-decoration-none small" id="clearFavBtn">
                                                <i class="fa-solid fa-trash-can me-1"></i> Clear
                                            </button>
                                        </div>
                                        <input type="text" name="landing_site_favicon" id="favUrlInput" class="form-control form-control-sm font-monospace" value="<?= htmlspecialchars($fav_val) ?>" placeholder="uploads/branding/codeulas_logo_small.webp">
                                        <input type="hidden" name="clear_landing_site_favicon" id="clearFavFlag" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- General Platform Options -->
                <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: #111a2e; border: 1px solid rgba(255,255,255,0.08) !important;">
                    <div class="card-header bg-black bg-opacity-25 py-3 border-bottom border-secondary border-opacity-25">
                        <h5 class="text-white fw-bold mb-0">
                            <i class="fa-solid fa-sliders text-warning me-2"></i>Global Operations &amp; Support
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-white small fw-bold">Platform Support &amp; Inquiries Email</label>
                                <input type="email" name="support_email" class="form-control" value="<?= htmlspecialchars($support_email) ?>" required>
                                <small class="text-muted">Appears in license documentation and support headers.</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-white small fw-bold">Server Timezone</label>
                                <input type="text" name="timezone" class="form-control" value="<?= htmlspecialchars($timezone) ?>" placeholder="America/New_York">
                                <small class="text-muted">Standard PHP timezone identifier.</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-white small fw-bold">Marketplace Demo / Simulation Mode</label>
                                <select class="form-select" name="demo_mode">
                                    <option value="1" <?= $demo_mode === '1' ? 'selected' : '' ?>>Enabled (Instant Test Purchases)</option>
                                    <option value="0" <?= $demo_mode === '0' ? 'selected' : '' ?>>Disabled (Strict Gateway Transactions)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-warning btn-lg fw-bold px-4 shadow">
                    <i class="fa-solid fa-check me-2"></i> Save Currency &amp; General Settings
                </button>
            </form>
        </div>

        <!-- TAB 2: PAYMENT GATEWAYS (STRIPE, RAZORPAY, PAYU) -->
        <div class="tab-pane fade <?= $curr_tab === 'gateways' ? 'show active' : '' ?>" id="tabGateways">
            <form action="<?= superadmin_url('settings') ?>" method="post">
                <input type="hidden" name="action" value="update_payment_gateways">
                <input type="hidden" name="active_tab" value="gateways">

                <!-- Gateway Policy Alert Banner -->
                <div class="alert alert-warning border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-center justify-content-between" style="background: rgba(194,153,88,0.15); border: 1px solid rgba(194,153,88,0.4) !important;">
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-shield-halved fa-2x text-warning me-3"></i>
                        <div>
                            <strong class="d-block text-white">Single-Active Gateway Policy Enforced</strong>
                            <span class="small text-light text-opacity-75">Only <strong>ONE</strong> payment gateway can be active at a time. Whichever gateway you select below will be exclusively deployed to your public marketplace checkout wizard.</span>
                        </div>
                    </div>
                    <span class="badge bg-warning text-dark fw-bold px-3 py-2 font-monospace fs-6">
                        CURRENT: <?= strtoupper($act_gw) ?>
                    </span>
                </div>

                <!-- Active Gateway Selector Radio Cards -->
                <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: #111a2e; border: 1px solid rgba(255,255,255,0.08) !important;">
                    <div class="card-header bg-black bg-opacity-25 py-3 border-bottom border-secondary border-opacity-25">
                        <h5 class="text-white fw-bold mb-0">
                            <i class="fa-solid fa-toggle-on text-warning me-2"></i>Select Active Payment Gateway
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Stripe Option -->
                            <div class="col-md-3">
                                <label class="p-3 rounded-4 d-block position-relative cursor-pointer transition-all <?= $act_gw === 'stripe' ? 'border border-2 border-warning bg-black bg-opacity-50' : 'border border-secondary border-opacity-25 bg-black bg-opacity-25' ?>" style="cursor: pointer;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="active_payment_gateway" value="stripe" id="gw_stripe" <?= $act_gw === 'stripe' ? 'checked' : '' ?>>
                                        <label class="form-check-label text-white fw-bold d-block" for="gw_stripe">
                                            <i class="fa-brands fa-stripe fa-2x text-primary d-block mb-1"></i>
                                            Stripe Payments
                                        </label>
                                    </div>
                                    <span class="badge <?= $act_gw === 'stripe' ? 'bg-success' : 'bg-secondary' ?> mt-2 small">
                                        <?= $act_gw === 'stripe' ? 'ACTIVE GATEWAY' : 'INACTIVE' ?>
                                    </span>
                                </label>
                            </div>

                            <!-- Razorpay Option -->
                            <div class="col-md-3">
                                <label class="p-3 rounded-4 d-block position-relative cursor-pointer transition-all <?= $act_gw === 'razorpay' ? 'border border-2 border-warning bg-black bg-opacity-50' : 'border border-secondary border-opacity-25 bg-black bg-opacity-25' ?>" style="cursor: pointer;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="active_payment_gateway" value="razorpay" id="gw_razorpay" <?= $act_gw === 'razorpay' ? 'checked' : '' ?>>
                                        <label class="form-check-label text-white fw-bold d-block" for="gw_razorpay">
                                            <i class="fa-solid fa-bolt fa-2x text-info d-block mb-1"></i>
                                            Razorpay Gateway
                                        </label>
                                    </div>
                                    <span class="badge <?= $act_gw === 'razorpay' ? 'bg-success' : 'bg-secondary' ?> mt-2 small">
                                        <?= $act_gw === 'razorpay' ? 'ACTIVE GATEWAY' : 'INACTIVE' ?>
                                    </span>
                                </label>
                            </div>

                            <!-- PayU Option -->
                            <div class="col-md-3">
                                <label class="p-3 rounded-4 d-block position-relative cursor-pointer transition-all <?= $act_gw === 'payu' ? 'border border-2 border-warning bg-black bg-opacity-50' : 'border border-secondary border-opacity-25 bg-black bg-opacity-25' ?>" style="cursor: pointer;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="active_payment_gateway" value="payu" id="gw_payu" <?= $act_gw === 'payu' ? 'checked' : '' ?>>
                                        <label class="form-check-label text-white fw-bold d-block" for="gw_payu">
                                            <i class="fa-solid fa-money-bill-wave fa-2x text-success d-block mb-1"></i>
                                            PayU Money / Biz
                                        </label>
                                    </div>
                                    <span class="badge <?= $act_gw === 'payu' ? 'bg-success' : 'bg-secondary' ?> mt-2 small">
                                        <?= $act_gw === 'payu' ? 'ACTIVE GATEWAY' : 'INACTIVE' ?>
                                    </span>
                                </label>
                            </div>

                            <!-- Offline / Direct Demo Option -->
                            <div class="col-md-3">
                                <label class="p-3 rounded-4 d-block position-relative cursor-pointer transition-all <?= $act_gw === 'offline' ? 'border border-2 border-warning bg-black bg-opacity-50' : 'border border-secondary border-opacity-25 bg-black bg-opacity-25' ?>" style="cursor: pointer;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="active_payment_gateway" value="offline" id="gw_offline" <?= $act_gw === 'offline' ? 'checked' : '' ?>>
                                        <label class="form-check-label text-white fw-bold d-block" for="gw_offline">
                                            <i class="fa-solid fa-laptop-code fa-2x text-warning d-block mb-1"></i>
                                            Demo / Sandbox
                                        </label>
                                    </div>
                                    <span class="badge <?= $act_gw === 'offline' ? 'bg-success' : 'bg-secondary' ?> mt-2 small">
                                        <?= $act_gw === 'offline' ? 'ACTIVE GATEWAY' : 'INACTIVE' ?>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 1. Stripe Credentials Card -->
                <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: #111a2e; border: 1px solid rgba(255,255,255,0.08) !important;">
                    <div class="card-header bg-black bg-opacity-25 py-3 border-bottom border-secondary border-opacity-25 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-brands fa-stripe fa-2x text-primary"></i>
                            <h5 class="text-white fw-bold mb-0">Stripe Payment Gateway Configuration</h5>
                        </div>
                        <span class="badge <?= $act_gw === 'stripe' ? 'bg-success' : 'bg-secondary' ?> font-monospace">
                            <?= $act_gw === 'stripe' ? 'ACTIVE' : 'STANDBY' ?>
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label text-white small fw-bold">Environment Mode</label>
                                <select name="gateway_stripe_mode" class="form-select">
                                    <option value="test" <?= $g('gateway_stripe_mode') === 'test' ? 'selected' : '' ?>>Sandbox / Test Mode</option>
                                    <option value="live" <?= $g('gateway_stripe_mode') === 'live' ? 'selected' : '' ?>>Production / Live Mode</option>
                                </select>
                            </div>
                            <div class="col-md-9">
                                <label class="form-label text-white small fw-bold">Stripe Publishable Key</label>
                                <input type="text" name="gateway_stripe_publishable_key" class="form-control font-monospace" value="<?= htmlspecialchars($g('gateway_stripe_publishable_key')) ?>" placeholder="pk_test_... or pk_live_...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white small fw-bold">Stripe Secret Key</label>
                                <input type="password" name="gateway_stripe_secret_key" class="form-control font-monospace" value="<?= htmlspecialchars($g('gateway_stripe_secret_key')) ?>" placeholder="sk_test_... or sk_live_...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white small fw-bold">Webhook Signing Secret (Optional)</label>
                                <input type="text" name="gateway_stripe_webhook_secret" class="form-control font-monospace" value="<?= htmlspecialchars($g('gateway_stripe_webhook_secret')) ?>" placeholder="whsec_...">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Razorpay Credentials Card -->
                <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: #111a2e; border: 1px solid rgba(255,255,255,0.08) !important;">
                    <div class="card-header bg-black bg-opacity-25 py-3 border-bottom border-secondary border-opacity-25 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-bolt fa-lg text-info"></i>
                            <h5 class="text-white fw-bold mb-0">Razorpay Payment Gateway Configuration</h5>
                        </div>
                        <span class="badge <?= $act_gw === 'razorpay' ? 'bg-success' : 'bg-secondary' ?> font-monospace">
                            <?= $act_gw === 'razorpay' ? 'ACTIVE' : 'STANDBY' ?>
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-white small fw-bold">Environment Mode</label>
                                <select name="gateway_razorpay_mode" class="form-select">
                                    <option value="test" <?= $g('gateway_razorpay_mode') === 'test' ? 'selected' : '' ?>>Test / Sandbox (rzp_test_...)</option>
                                    <option value="live" <?= $g('gateway_razorpay_mode') === 'live' ? 'selected' : '' ?>>Production / Live (rzp_live_...)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-white small fw-bold">Razorpay Key ID</label>
                                <input type="text" name="gateway_razorpay_key_id" class="form-control font-monospace" value="<?= htmlspecialchars($g('gateway_razorpay_key_id')) ?>" placeholder="rzp_test_...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-white small fw-bold">Razorpay Key Secret</label>
                                <input type="password" name="gateway_razorpay_key_secret" class="form-control font-monospace" value="<?= htmlspecialchars($g('gateway_razorpay_key_secret')) ?>" placeholder="Key Secret">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. PayU Credentials Card -->
                <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: #111a2e; border: 1px solid rgba(255,255,255,0.08) !important;">
                    <div class="card-header bg-black bg-opacity-25 py-3 border-bottom border-secondary border-opacity-25 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-money-bill-wave fa-lg text-success"></i>
                            <h5 class="text-white fw-bold mb-0">PayU Money / Biz Gateway Configuration</h5>
                        </div>
                        <span class="badge <?= $act_gw === 'payu' ? 'bg-success' : 'bg-secondary' ?> font-monospace">
                            <?= $act_gw === 'payu' ? 'ACTIVE' : 'STANDBY' ?>
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-white small fw-bold">Environment Mode</label>
                                <select name="gateway_payu_mode" class="form-select">
                                    <option value="test" <?= $g('gateway_payu_mode') === 'test' ? 'selected' : '' ?>>Sandbox Test (test.payu.in)</option>
                                    <option value="live" <?= $g('gateway_payu_mode') === 'live' ? 'selected' : '' ?>>Production Live (secure.payu.in)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-white small fw-bold">PayU Merchant Key</label>
                                <input type="text" name="gateway_payu_merchant_key" class="form-control font-monospace" value="<?= htmlspecialchars($g('gateway_payu_merchant_key')) ?>" placeholder="Merchant Key">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-white small fw-bold">PayU Merchant Salt</label>
                                <input type="password" name="gateway_payu_merchant_salt" class="form-control font-monospace" value="<?= htmlspecialchars($g('gateway_payu_merchant_salt')) ?>" placeholder="Merchant Salt">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-warning btn-lg fw-bold px-4 shadow">
                    <i class="fa-solid fa-check me-2"></i> Save Payment Gateway Settings
                </button>
            </form>
        </div>

        <!-- TAB 3: SMTP EMAIL GATEWAY -->
        <div class="tab-pane fade <?= $curr_tab === 'smtp' ? 'show active' : '' ?>" id="tabSmtp">
            <div class="row g-4">
                <div class="col-lg-8">
                    <form action="<?= superadmin_url('settings') ?>" method="post">
                        <input type="hidden" name="action" value="update_smtp_email">
                        <input type="hidden" name="active_tab" value="smtp">

                        <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: #111a2e; border: 1px solid rgba(255,255,255,0.08) !important;">
                            <div class="card-header bg-black bg-opacity-25 py-3 border-bottom border-secondary border-opacity-25">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h5 class="text-white fw-bold mb-0">
                                        <i class="fa-solid fa-envelope-open-text text-warning me-2"></i>SMTP Transactional Mailer
                                    </h5>
                                    <span class="badge <?= $g('smtp_status') === 'enabled' ? 'bg-success' : 'bg-secondary' ?> font-monospace">
                                        <?= strtoupper($g('smtp_status', 'disabled')) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label text-white small fw-bold">SMTP Gateway Status</label>
                                        <select name="smtp_status" class="form-select">
                                            <option value="enabled" <?= $g('smtp_status') === 'enabled' ? 'selected' : '' ?>>Enabled (Send Real Emails)</option>
                                            <option value="disabled" <?= $g('smtp_status') === 'disabled' ? 'selected' : '' ?>>Disabled (Log / Offline)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label text-white small fw-bold">SMTP Host Server</label>
                                        <input type="text" name="smtp_host" class="form-control font-monospace" value="<?= htmlspecialchars($g('smtp_host', 'smtp.gmail.com')) ?>" placeholder="smtp.gmail.com or smtp.mailgun.org" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label text-white small fw-bold">SMTP Port</label>
                                        <input type="number" name="smtp_port" class="form-control font-monospace" value="<?= htmlspecialchars($g('smtp_port', '587')) ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-white small fw-bold">Encryption Security</label>
                                        <select name="smtp_crypto" class="form-select">
                                            <option value="tls" <?= $g('smtp_crypto') === 'tls' ? 'selected' : '' ?>>TLS (Port 587 Recommended)</option>
                                            <option value="ssl" <?= $g('smtp_crypto') === 'ssl' ? 'selected' : '' ?>>SSL (Port 465)</option>
                                            <option value="none" <?= $g('smtp_crypto') === 'none' ? 'selected' : '' ?>>None (Standard / Localhost)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-white small fw-bold">SMTP Username / Email</label>
                                        <input type="text" name="smtp_user" class="form-control font-monospace" value="<?= htmlspecialchars($g('smtp_user')) ?>" placeholder="notifications@domain.com">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-white small fw-bold">SMTP Password / App Key</label>
                                        <input type="password" name="smtp_pass" class="form-control font-monospace" value="<?= htmlspecialchars($g('smtp_pass')) ?>" placeholder="Leave blank to keep current">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-white small fw-bold">Sender From Email</label>
                                        <input type="email" name="smtp_from_email" class="form-control" value="<?= htmlspecialchars($g('smtp_from_email', 'sales@spasalon.com')) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-white small fw-bold">Sender From Name</label>
                                        <input type="text" name="smtp_from_name" class="form-control" value="<?= htmlspecialchars($g('smtp_from_name', 'Luxe Salon & Spa Software')) ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning btn-lg fw-bold px-4 shadow">
                            <i class="fa-solid fa-check me-2"></i> Save SMTP Configuration
                        </button>
                    </form>
                </div>

                <!-- Live Test Mailer Tool -->
                <div class="col-lg-4">
                    <div class="card border-0 rounded-4 shadow-sm" style="background: #111a2e; border: 1px solid rgba(194,153,88,0.3) !important;">
                        <div class="card-header bg-black bg-opacity-25 py-3 border-bottom border-secondary border-opacity-25">
                            <h6 class="text-white fw-bold mb-0">
                                <i class="fa-solid fa-paper-plane text-warning me-2"></i>Instant SMTP Test Dispatch
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted small mb-3">
                                Test your SMTP credentials immediately. We will initiate an authenticated handshake and dispatch a test verification email to your inbox.
                            </p>
                            <form action="<?= superadmin_url('settings') ?>" method="post">
                                <input type="hidden" name="action" value="test_smtp_email">
                                <input type="hidden" name="active_tab" value="smtp">

                                <div class="mb-3">
                                    <label class="form-label text-white small fw-bold">Recipient Test Email</label>
                                    <input type="email" name="test_email" class="form-control" placeholder="your-email@gmail.com" required>
                                </div>

                                <button type="submit" class="btn btn-outline-warning w-100 fw-bold">
                                    <i class="fa-solid fa-paper-plane me-1"></i> Send Test Verification
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 4: GOOGLE OAUTH -->
        <div class="tab-pane fade <?= $curr_tab === 'oauth' ? 'show active' : '' ?>" id="tabOauth">
            <form action="<?= superadmin_url('settings') ?>" method="post">
                <input type="hidden" name="action" value="update_google_oauth">
                <input type="hidden" name="active_tab" value="oauth">

                <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: #111a2e; border: 1px solid rgba(255,255,255,0.08) !important;">
                    <div class="card-header bg-black bg-opacity-25 py-3 border-bottom border-secondary border-opacity-25 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-brands fa-google fa-2x text-danger"></i>
                            <h5 class="text-white fw-bold mb-0">Google OAuth 2.0 Single Sign-On (SSO)</h5>
                        </div>
                        <span class="badge <?= $g('google_oauth_status') === 'enabled' ? 'bg-success' : 'bg-secondary' ?> font-monospace">
                            <?= strtoupper($g('google_oauth_status', 'disabled')) ?>
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted small mb-4">
                            Enable 1-click Google Single Sign-On for Super Admin authentication and customer account logins via the official Google Identity Services.
                        </p>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label text-white small fw-bold">Google OAuth Status</label>
                                <select name="google_oauth_status" class="form-select">
                                    <option value="enabled" <?= $g('google_oauth_status') === 'enabled' ? 'selected' : '' ?>>Enabled</option>
                                    <option value="disabled" <?= $g('google_oauth_status') === 'disabled' ? 'selected' : '' ?>>Disabled</option>
                                </select>
                            </div>
                            <div class="col-md-9">
                                <label class="form-label text-white small fw-bold">Google Client ID</label>
                                <input type="text" name="google_oauth_client_id" class="form-control font-monospace" value="<?= htmlspecialchars($g('google_oauth_client_id')) ?>" placeholder="xxxxxxxxxx-xxxxxxxxxxxxxxxxxxxxxxxx.apps.googleusercontent.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white small fw-bold">Google Client Secret</label>
                                <input type="password" name="google_oauth_client_secret" class="form-control font-monospace" value="<?= htmlspecialchars($g('google_oauth_client_secret')) ?>" placeholder="GOCSPX-xxxxxxxxxxxxxxxxxxxxxxxx">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white small fw-bold">Authorized Redirect URI (Copy to Google Console)</label>
                                <input type="url" name="google_oauth_redirect_uri" class="form-control font-monospace" value="<?= htmlspecialchars($g('google_oauth_redirect_uri', superadmin_url('auth/google_callback'))) ?>">
                            </div>
                        </div>

                        <div class="mt-4 p-3 rounded-3" style="background: rgba(0,0,0,0.3); border: 1px dashed rgba(255,255,255,0.1);">
                            <div class="small text-muted">
                                <strong>How to set up Google OAuth:</strong><br>
                                1. Visit <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="text-warning">Google Cloud Console &rarr; Credentials</a>.<br>
                                2. Create an <strong>OAuth 2.0 Client ID</strong> (Application type: Web application).<br>
                                3. Paste the <strong>Authorized Redirect URI</strong> shown above into your Google Console.<br>
                                4. Copy and paste the generated Client ID and Client Secret into the fields above, then click save.
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-warning btn-lg fw-bold px-4 shadow">
                    <i class="fa-solid fa-check me-2"></i> Save Google OAuth Settings
                </button>
            </form>
        </div>

        <!-- TAB 5: DIAGNOSTICS & SYSTEM -->
        <div class="tab-pane fade <?= $curr_tab === 'system' ? 'show active' : '' ?>" id="tabSystem">
            <!-- Diagnostics Metric Tiles -->
            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm p-3 rounded-4" style="background: #111a2e; border: 1px solid rgba(255,255,255,0.08) !important;">
                        <span class="text-muted small fw-bold text-uppercase">PHP Version</span>
                        <h4 class="fw-bold text-primary mt-1 mb-0">PHP <?= $php_version ?></h4>
                        <small class="text-muted">Compatible with 7.4 - 8.2+</small>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm p-3 rounded-4" style="background: #111a2e; border: 1px solid rgba(255,255,255,0.08) !important;">
                        <span class="text-muted small fw-bold text-uppercase">Database Size</span>
                        <h4 class="fw-bold text-success mt-1 mb-0"><?= $total_db_size_mb ?> MB</h4>
                        <small class="text-muted">MariaDB / MySQL Engine</small>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm p-3 rounded-4" style="background: #111a2e; border: 1px solid rgba(255,255,255,0.08) !important;">
                        <span class="text-muted small fw-bold text-uppercase">Memory Limit</span>
                        <h4 class="fw-bold text-warning mt-1 mb-0"><?= $memory_limit ?></h4>
                        <small class="text-muted">Max Exec: <?= $max_execution_time ?></small>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm p-3 rounded-4" style="background: #111a2e; border: 1px solid rgba(255,255,255,0.08) !important;">
                        <span class="text-muted small fw-bold text-uppercase">Max File Upload</span>
                        <h4 class="fw-bold text-info mt-1 mb-0"><?= $upload_max_filesize ?></h4>
                        <small class="text-muted">Max upload threshold</small>
                    </div>
                </div>
            </div>

            <!-- Maintenance & Database Tables -->
            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <div class="card border-0 rounded-4 shadow-sm h-100" style="background: #111a2e; border: 1px solid rgba(255,255,255,0.08) !important;">
                        <div class="card-header bg-black bg-opacity-25 py-3 border-bottom border-secondary border-opacity-25">
                            <h6 class="text-white fw-bold mb-0"><i class="fa-solid fa-wrench text-warning me-2"></i>Maintenance Utilities</h6>
                        </div>
                        <div class="card-body p-4">
                            <form action="<?= superadmin_url('settings') ?>" method="post" class="mb-3">
                                <input type="hidden" name="action" value="clear_cache">
                                <input type="hidden" name="active_tab" value="system">
                                <button type="submit" class="btn btn-outline-warning w-100 fw-bold">
                                    <i class="fa-solid fa-broom me-2"></i> Clear Application Cache
                                </button>
                            </form>
                            <a href="<?= superadmin_url('settings/backup') ?>" class="btn btn-gold w-100 fw-bold">
                                <i class="fa-solid fa-download me-2"></i> Download Complete SQL Backup
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card border-0 rounded-4 shadow-sm" style="background: #111a2e; border: 1px solid rgba(255,255,255,0.08) !important;">
                        <div class="card-header bg-black bg-opacity-25 py-3 border-bottom border-secondary border-opacity-25 d-flex align-items-center justify-content-between">
                            <h6 class="text-white fw-bold mb-0"><i class="fa-solid fa-database text-warning me-2"></i>Database Storage Footprint</h6>
                            <span class="badge bg-secondary font-monospace"><?= count($tables) ?> Tables Active</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 320px;">
                                <table class="table table-dark table-striped table-hover mb-0 align-middle small">
                                    <thead>
                                        <tr>
                                            <th>Table Name</th>
                                            <th>Rows</th>
                                            <th>Data Size</th>
                                            <th>Index Size</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tables as $t): ?>
                                            <tr>
                                                <td class="font-monospace text-warning"><?= $t->Name ?></td>
                                                <td><?= number_format($t->Rows) ?></td>
                                                <td><?= round($t->Data_length / 1024, 1) ?> KB</td>
                                                <td><?= round($t->Index_length / 1024, 1) ?> KB</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dynamic Currency Live Preview Calculation
    var curDropdown = document.getElementById('curDropdown');
    var curSymbolInput = document.getElementById('curSymbolInput');
    var curCodeInput = document.getElementById('curCodeInput');
    var curPosInput = document.getElementById('curPosInput');
    var curDecInput = document.getElementById('curDecInput');

    var curPreview = document.getElementById('currencyPreview');
    var pSalon = document.getElementById('previewSalon');
    var pSpa = document.getElementById('previewSpa');
    var pUnified = document.getElementById('previewUnified');

    function updateCurrencyPreview() {
        var sym = (curSymbolInput ? curSymbolInput.value : '$') || '$';
        var code = (curCodeInput ? curCodeInput.value.toUpperCase() : 'USD') || 'USD';
        var pos = (curPosInput ? curPosInput.value : 'left');
        var dec = (curDecInput ? curDecInput.value : '2');

        function fmt(n) {
            var val = (dec === '2') ? n.toFixed(2) : Math.round(n).toString();
            return (pos === 'left') ? (sym + val) : (val + ' ' + sym);
        }

        if (curPreview) {
            curPreview.innerHTML = fmt(89) + ' <span class="small text-light text-opacity-50 fs-6">(' + code + ')</span>';
        }
        if (pSalon) pSalon.textContent = fmt(49);
        if (pSpa) pSpa.textContent = fmt(69);
        if (pUnified) pUnified.textContent = fmt(89);
    }

    if (curDropdown) {
        curDropdown.addEventListener('change', function() {
            var opt = this.options[this.selectedIndex];
            if (this.value && opt) {
                var sym = opt.getAttribute('data-symbol');
                var pos = opt.getAttribute('data-pos') || 'left';
                if (curCodeInput) curCodeInput.value = this.value;
                if (curSymbolInput && sym) curSymbolInput.value = sym;
                if (curPosInput && pos) curPosInput.value = pos;
                updateCurrencyPreview();
            }
        });
    }

    if (curSymbolInput) curSymbolInput.addEventListener('input', updateCurrencyPreview);
    if (curCodeInput) curCodeInput.addEventListener('input', updateCurrencyPreview);
    if (curPosInput) curPosInput.addEventListener('change', updateCurrencyPreview);
    if (curDecInput) curDecInput.addEventListener('change', updateCurrencyPreview);

    // ==========================================
    // Branding Logo & Favicon Live Previews
    // ==========================================
    var logoFile = document.getElementById('logoFileInput');
    var logoUrl = document.getElementById('logoUrlInput');
    var logoImg = document.getElementById('logoPreviewImg');
    var logoPlaceholder = document.getElementById('logoPlaceholder');
    var clearLogoBtn = document.getElementById('clearLogoBtn');
    var clearLogoFlag = document.getElementById('clearLogoFlag');

    if (logoFile) {
        logoFile.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    if (logoImg) {
                        logoImg.src = e.target.result;
                        logoImg.style.display = 'inline-block';
                    }
                    if (logoPlaceholder) logoPlaceholder.style.display = 'none';
                    if (clearLogoFlag) clearLogoFlag.value = '0';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    if (logoUrl) {
        logoUrl.addEventListener('input', function() {
            var val = this.value.trim();
            if (val) {
                var fullSrc = (val.indexOf('http') === 0 || val.indexOf('/') === 0) ? val : ('<?= base_url() ?>' + val);
                if (logoImg) {
                    logoImg.src = fullSrc;
                    logoImg.style.display = 'inline-block';
                }
                if (logoPlaceholder) logoPlaceholder.style.display = 'none';
                if (clearLogoFlag) clearLogoFlag.value = '0';
            }
        });
    }

    if (clearLogoBtn) {
        clearLogoBtn.addEventListener('click', function() {
            if (logoUrl) logoUrl.value = '';
            if (logoFile) logoFile.value = '';
            if (logoImg) {
                logoImg.src = '';
                logoImg.style.display = 'none';
            }
            if (logoPlaceholder) logoPlaceholder.style.display = 'block';
            if (clearLogoFlag) clearLogoFlag.value = '1';
        });
    }

    var favFile = document.getElementById('favFileInput');
    var favUrl = document.getElementById('favUrlInput');
    var favImg = document.getElementById('favPreviewImg');
    var favPlaceholder = document.getElementById('favPlaceholder');
    var clearFavBtn = document.getElementById('clearFavBtn');
    var clearFavFlag = document.getElementById('clearFavFlag');

    if (favFile) {
        favFile.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    if (favImg) {
                        favImg.src = e.target.result;
                        favImg.style.display = 'inline-block';
                    }
                    if (favPlaceholder) favPlaceholder.style.display = 'none';
                    if (clearFavFlag) clearFavFlag.value = '0';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    if (favUrl) {
        favUrl.addEventListener('input', function() {
            var val = this.value.trim();
            if (val) {
                var fullSrc = (val.indexOf('http') === 0 || val.indexOf('/') === 0) ? val : ('<?= base_url() ?>' + val);
                if (favImg) {
                    favImg.src = fullSrc;
                    favImg.style.display = 'inline-block';
                }
                if (favPlaceholder) favPlaceholder.style.display = 'none';
                if (clearFavFlag) clearFavFlag.value = '0';
            }
        });
    }

    if (clearFavBtn) {
        clearFavBtn.addEventListener('click', function() {
            if (favUrl) favUrl.value = '';
            if (favFile) favFile.value = '';
            if (favImg) {
                favImg.src = '';
                favImg.style.display = 'none';
            }
            if (favPlaceholder) favPlaceholder.style.display = 'inline-block';
            if (clearFavFlag) clearFavFlag.value = '1';
        });
    }
});
</script>
