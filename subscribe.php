<?php
/**
 * SaaS Subscription & Project Setup Wizard (subscribe.php)
 * Following http://localhost/spasalonmanagement/subscribe.php?plan=SPA
 * 5-Step Flow:
 *   Step 1: Account (Full Name, Email, Phone, Password + Live Uniqueness Check)
 *   Step 2: OTP Verify (6-Digit OTP Verification + Demo Auto-Fill + Resend Timer)
 *   Step 3: Setup Info (Store Profile, Domain/Slug Availability Check, Logo & Favicon Upload)
 *   Step 4: Choose Template (Template 1: Fashion, Template 2: Electronics, Template 3: Grocery + Live Preview)
 *   Step 5: Payment (Order Summary, Stripe / Razorpay / Sandbox Checkout, 7-Step Live Deployment & Launch Hub)
 */
require_once __DIR__ . '/saas_core.php';

$pdo = get_saas_pdo();
$settings = get_all_saas_settings();

// Determine selected plan from ?plan=... (supports starter, growth, enterprise, or ID)
$requestedPlan = isset($_GET['plan']) ? trim($_GET['plan']) : 'starter';
$plan = get_saas_plan_by_id_or_slug($requestedPlan);
if (!$plan) {
    $plan = get_saas_plan_by_id_or_slug(1);
}

// Determine preselected template from ?tpl=... (or if ?plan=... was a template name)
$requestedTplRaw = isset($_GET['tpl']) ? trim($_GET['tpl']) : '';
if ($requestedTplRaw === '' && in_array(strtolower($requestedPlan), ['fashion', 'electronics', 'grocery', 'template1', 'template2', 'template3'], true)) {
    $requestedTplRaw = $requestedPlan;
}
$preselected_tpl_key = $requestedTplRaw !== '' ? get_saas_tpl_folder_key($requestedTplRaw) : 'template1';

$allPlans = get_active_saas_plans();
$plan_code = strtoupper($plan['code'] ?: ($plan['slug'] ?: 'STARTER'));
$plan_name = $plan['name'] ?? 'Starter Store Plan';
$plan_price = (float)($plan['effective_price'] ?? $plan['price'] ?? 29.00);
$plan_currency_sym = ($plan['currency'] ?? 'USD') === 'INR' ? '₹' : '$';
$allowed_templates = $plan['allowed_templates_list'] ?? ['template1', 'template2', 'template3', 'fashion', 'electronics', 'grocery'];

// Payment Gateway Configuration
$stripe_enabled = ($settings['stripe_enabled'] ?? '1') === '1';
$stripe_pk      = $settings['stripe_publishable_key'] ?? 'pk_test_51MockStripePublishableKey';
$razorpay_enabled = ($settings['razorpay_enabled'] ?? '1') === '1';
$razorpay_key_id  = $settings['razorpay_key_id'] ?? 'rzp_test_MockKeyId12345';
$sandbox_enabled  = ($settings['sandbox_enabled'] ?? '1') === '1';

// Check if returning from Stripe Checkout
$stripe_return_result = null;
if (isset($_GET['stripe_payment']) && $_GET['stripe_payment'] === 'success' && !empty($_SESSION['pending_wizard_setup'])) {
    require_once __DIR__ . '/subscribe_api.php';
    $pending_data = $_SESSION['pending_wizard_setup'];
    $pending_data['payment_method'] = 'stripe';
    $pending_data['transaction_id'] = isset($_GET['session_id']) ? trim($_GET['session_id']) : ('STRIPE-' . time());
    $stripe_return_result = execute_subscription_provisioning($pdo, $settings, $pending_data, []);
    unset($_SESSION['pending_wizard_setup']);
}

// Template Showcase Metadata for Step 4
$templates_catalog = [
    'template1' => [
        'key'         => 'template1',
        'slug'        => 'fashion',
        'badge'       => 'TEMPLATE 1 — FASHION',
        'title'       => 'Template 1 (fashion)',
        'subtitle'    => 'Luxury Apparel, Footwear, Handbags & Designer Lookbooks',
        'preview_img' => saas_base_url('website/assets/images/templates/ecommerce_template1.png'),
        'demo_url'    => saas_base_url('website/home?preview_tpl=fashion'),
        'features'    => ['Hero Banner Slider', 'Circular Category Showcase', 'QuickView & Size Swatches', 'Superadmin Customizable']
    ],
    'template2' => [
        'key'         => 'template2',
        'slug'        => 'electronics',
        'badge'       => 'TEMPLATE 2 — ELECTRONICS',
        'title'       => 'Template 2 (Electronics)',
        'subtitle'    => 'Smartphones, Laptops, Audio Gear & High-Tech Accessories',
        'preview_img' => saas_base_url('website/assets/images/templates/electronics_template2.png'),
        'demo_url'    => saas_base_url('website/home?preview_tpl=electronics'),
        'features'    => ['Interactive Tech Hero Slider', 'Category & Deal Showcases', 'Specs & Rating Badges', 'Superadmin Customizable']
    ],
    'template3' => [
        'key'         => 'template3',
        'slug'        => 'grocery',
        'badge'       => 'TEMPLATE 3 — GROCERY',
        'title'       => 'Template 3 (Grocery)',
        'subtitle'    => 'Organic Produce, Daily Essentials, Dairy & Pantry Delivery',
        'preview_img' => saas_base_url('website/assets/images/templates/grocery_template3.png'),
        'demo_url'    => saas_base_url('website/home?preview_tpl=grocery'),
        'features'    => ['Fresh Organic Hero Banners', 'Unit/Weight Badges (500g, 1kg)', 'Fast Add-to-Cart Controls', 'Superadmin Customizable']
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscribe & Setup Store — <?php echo htmlspecialchars($plan_name); ?></title>
    <link rel="shortcut icon" type="image/webp" href="codeulas_logo_small.webp">
    <link rel="icon" type="image/webp" href="codeulas_logo_small.webp">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <style>
        :root {
            --gold-primary: #c29958;
            --gold-light: #d4af37;
            --gold-dark: #9e7a3c;
            --dark-bg: #0b0f19;
            --dark-card: #131926;
            --dark-surface: #1c2436;
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
            --border-gold: rgba(194, 153, 88, 0.25);
        }

        body, h1, h2, h3, h4, h5, h6, .serif-title, input, select, button, textarea {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: var(--dark-bg);
            background-image:
                radial-gradient(circle at 15% 15%, rgba(194, 153, 88, 0.08) 0%, transparent 35%),
                radial-gradient(circle at 85% 85%, rgba(194, 153, 88, 0.06) 0%, transparent 40%);
            color: var(--text-light);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Navbar */
        .wizard-navbar {
            background: rgba(11, 15, 25, 0.9);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        /* 5-Step Progress Bar */
        .wizard-steps-bar {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin: 0 auto 38px;
        }
        .wizard-steps-bar::before {
            content: '';
            position: absolute;
            top: 21px;
            left: 40px;
            right: 40px;
            height: 2px;
            background: rgba(255, 255, 255, 0.1);
            z-index: 0;
        }
        .step-node {
            position: relative;
            z-index: 1;
            text-align: center;
            flex: 1;
        }
        .step-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--dark-card);
            border: 2px solid rgba(255, 255, 255, 0.15);
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-weight: 700;
            font-size: 0.95rem;
            transition: all 0.35s ease;
        }
        .step-label {
            font-size: 0.76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text-muted);
            transition: all 0.35s ease;
        }
        .step-node.active .step-circle {
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-dark));
            border-color: var(--gold-light);
            color: #fff;
            box-shadow: 0 0 20px rgba(194, 153, 88, 0.45);
            transform: scale(1.06);
        }
        .step-node.active .step-label {
            color: var(--gold-light);
        }
        .step-node.completed .step-circle {
            background: #10b981;
            border-color: #10b981;
            color: #fff;
        }
        .step-node.completed .step-label {
            color: #10b981;
        }

        /* Wizard Main Card */
        .wizard-card {
            background: var(--dark-card);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 38px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.45);
            margin: 0 auto 60px;
        }
        @media (max-width: 768px) {
            .wizard-card { padding: 24px 18px; }
        }

        /* Inputs */
        .wiz-label {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            color: #cbd5e1;
            margin-bottom: 7px;
            display: block;
        }
        .wiz-input {
            background: var(--dark-surface) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            color: #fff !important;
            border-radius: 11px;
            padding: 12px 15px;
            font-size: 0.95rem;
            transition: all 0.25s;
        }
        .wiz-input::placeholder,
        .form-control::placeholder {
            color: #94a3b8 !important;
            opacity: 1 !important;
        }
        .wiz-input::-webkit-input-placeholder {
            color: #94a3b8 !important;
            opacity: 1 !important;
        }
        .wiz-input::-moz-placeholder {
            color: #94a3b8 !important;
            opacity: 1 !important;
        }
        .otp-digit::placeholder {
            color: rgba(148, 163, 184, 0.45) !important;
            opacity: 1 !important;
        }
        .wiz-input:focus {
            border-color: var(--gold-primary) !important;
            box-shadow: 0 0 0 3px rgba(194, 153, 88, 0.18) !important;
        }
        .wiz-input.is-invalid {
            border-color: #ef4444 !important;
        }
        .wiz-input.is-valid {
            border-color: #10b981 !important;
        }

        /* Gold Buttons */
        .btn-gold {
            background: linear-gradient(135deg, var(--gold-primary) 0%, var(--gold-dark) 100%);
            color: #fff !important;
            border: none;
            border-radius: 11px;
            padding: 13px 28px;
            font-weight: 700;
            letter-spacing: 0.4px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(194, 153, 88, 0.28);
        }
        .btn-gold:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px rgba(194, 153, 88, 0.4);
        }
        .btn-gold:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .btn-outline-wiz {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #cbd5e1;
            border-radius: 11px;
            padding: 12px 22px;
            font-weight: 600;
            transition: all 0.25s;
        }
        .btn-outline-wiz:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        /* OTP Boxes */
        .otp-box-group {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin: 24px 0;
        }
        .otp-digit {
            width: 54px;
            height: 62px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 800;
            background: var(--dark-surface);
            border: 2px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            color: var(--gold-light);
            transition: all 0.2s;
        }
        .otp-digit:focus {
            border-color: var(--gold-primary);
            outline: none;
            box-shadow: 0 0 15px rgba(194, 153, 88, 0.3);
        }

        /* Template Cards (Step 4) */
        .tpl-select-card {
            background: var(--dark-surface);
            border: 2px solid rgba(255, 255, 255, 0.09);
            border-radius: 16px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
        }
        .tpl-select-card:hover:not(.locked) {
            border-color: rgba(194, 153, 88, 0.55);
            transform: translateY(-4px);
        }
        .tpl-select-card.selected {
            border-color: var(--gold-primary);
            box-shadow: 0 12px 32px rgba(194, 153, 88, 0.28);
            background: linear-gradient(180deg, rgba(194, 153, 88, 0.1), var(--dark-surface));
        }
        .tpl-select-card.locked {
            opacity: 0.45;
            cursor: not-allowed;
        }
        .tpl-thumb-wrap {
            height: 185px;
            overflow: hidden;
            position: relative;
            background: #090d16;
        }
        .tpl-thumb-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .tpl-select-card:hover .tpl-thumb-wrap img {
            transform: scale(1.05);
        }
        .tpl-check-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(15, 23, 42, 0.8);
            border: 2px solid rgba(255,255,255,0.3);
            color: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.25s;
        }
        .tpl-select-card.selected .tpl-check-badge {
            background: var(--gold-primary);
            border-color: #fff;
            color: #fff;
        }

        /* Upload Dropzone */
        .upload-zone {
            background: rgba(255,255,255,0.02);
            border: 1.5px dashed rgba(255,255,255,0.2);
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s;
        }
        .upload-zone:hover {
            border-color: var(--gold-primary);
            background: rgba(194, 153, 88, 0.05);
        }

        /* Payment Gateway Pills */
        .gw-pill {
            background: var(--dark-surface);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 14px 16px;
            cursor: pointer;
            transition: all 0.25s;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .gw-pill.active {
            border-color: var(--gold-primary);
            background: rgba(194, 153, 88, 0.12);
        }

        .step-section { display: none; }
        .step-section.active { display: block; animation: fadeIn 0.35s ease; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <!-- Top Navigation -->
    <nav class="wizard-navbar">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="index.php" class="text-decoration-none d-flex align-items-center gap-2">
                <img src="logo.webp" alt="Logo" style="height:40px;width:auto;object-fit:contain;">
            </a>

            <div class="d-flex align-items-center gap-3">
                <div class="d-none d-md-flex align-items-center gap-2 px-3 py-1 rounded-pill" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">
                    <span class="text-white-50 small">Selected Plan:</span>
                    <strong class="text-warning small" id="navPlanBadge"><?php echo htmlspecialchars($plan_name); ?> (<?php echo $plan_currency_sym . number_format($plan_price, 2); ?>)</strong>
                </div>
                <a href="index.php" class="btn btn-sm btn-outline-wiz py-2 px-3">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Plans
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <!-- 5-Step Progress Indicator -->
        <div class="wizard-steps-bar" id="wizardStepBar">
            <div class="step-node active" id="stepNode1">
                <div class="step-circle"><span>1</span></div>
                <div class="step-label">Account</div>
            </div>
            <div class="step-node" id="stepNode2">
                <div class="step-circle"><span>2</span></div>
                <div class="step-label">OTP Verify</div>
            </div>
            <div class="step-node" id="stepNode3">
                <div class="step-circle"><span>3</span></div>
                <div class="step-label">Setup Info</div>
            </div>
            <div class="step-node" id="stepNode4">
                <div class="step-circle"><span>4</span></div>
                <div class="step-label">Choose Template</div>
            </div>
            <div class="step-node" id="stepNode5">
                <div class="step-circle"><span>5</span></div>
                <div class="step-label">Payment</div>
            </div>
        </div>

        <!-- Main Wizard Card -->
        <div class="wizard-card">

            <!-- Global Alert Box -->
            <div id="wizardAlert" class="alert d-none mb-4" role="alert"></div>

            <!-- ==========================================
                 STEP 1: ACCOUNT REGISTRATION
            =========================================== -->
            <div class="step-section active" id="sectionStep1">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                    <div>
                        <span class="badge mb-2" style="background:rgba(194,153,88,0.18);color:var(--gold-light);">STEP 1 OF 5 — OWNER ACCOUNT</span>
                        <h2 class="serif-title fw-bold mb-1">Create Your Store Owner Account</h2>
                        <p class="text-white-50 small mb-0">Enter your administrator details to begin setting up your multi-template eCommerce store.</p>
                    </div>
                    <!-- Plan Switcher Dropdown -->
                    <div class="mt-3 mt-md-0">
                        <label class="wiz-label mb-1">Subscription Plan</label>
                        <select id="wizPlanSelector" class="form-select wiz-input py-2" onchange="onPlanSelectorChange(this.value)">
                            <?php foreach ($allPlans as $p):
                                $pCode = strtoupper($p['code'] ?: $p['slug']);
                                $pPrice = (float)$p['effective_price'];
                                $sel = ((int)$p['id'] === (int)$plan['id']) ? 'selected' : '';
                            ?>
                            <option value="<?php echo htmlspecialchars($pCode); ?>"
                                    data-id="<?php echo (int)$p['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($p['name']); ?>"
                                    data-price="<?php echo $pPrice; ?>"
                                    data-allowed='<?php echo htmlspecialchars(json_encode($p['allowed_templates_list'])); ?>'
                                    <?php echo $sel; ?>>
                                <?php echo htmlspecialchars($p['name']); ?> — $<?php echo number_format($pPrice, 2); ?>/<?php echo htmlspecialchars($p['billing_cycle']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="wiz-label">Full Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary border-opacity-25 text-white-50"><i class="fa-regular fa-user"></i></span>
                            <input type="text" id="wizName" class="form-control wiz-input" placeholder="e.g. Alexander Sterling" required>
                        </div>
                        <div class="small mt-1 d-none" id="errName"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="wiz-label">Official Email Address <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary border-opacity-25 text-white-50"><i class="fa-regular fa-envelope"></i></span>
                            <input type="email" id="wizEmail" class="form-control wiz-input" placeholder="owner@yourstore.com" onblur="validateUniqueField('email')" required>
                        </div>
                        <div class="small mt-1 d-none" id="errEmail"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="wiz-label">Mobile / WhatsApp Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary border-opacity-25 text-white-50"><i class="fa-solid fa-phone"></i></span>
                            <input type="tel" id="wizPhone" class="form-control wiz-input" placeholder="+1 555 234 5678" onblur="validateUniqueField('phone')" required>
                        </div>
                        <div class="small mt-1 d-none" id="errPhone"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="wiz-label">Admin Login Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary border-opacity-25 text-white-50"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" id="wizPassword" class="form-control wiz-input" placeholder="Minimum 6 characters" required>
                            <button type="button" class="btn btn-outline-secondary border-opacity-25 text-white-50" onclick="togglePassVisibility()">
                                <i class="fa-regular fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                        <div class="small mt-1 d-none" id="errPassword"></div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4 pt-3 border-top border-secondary border-opacity-25">
                    <button type="button" id="btnStep1Next" class="btn btn-gold px-4" onclick="processStep1()">
                        Proceed to OTP Verification <i class="fa-solid fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>

            <!-- ==========================================
                 STEP 2: OTP VERIFICATION
            =========================================== -->
            <div class="step-section" id="sectionStep2">
                <div class="text-center py-2">
                    <span class="badge mb-2" style="background:rgba(194,153,88,0.18);color:var(--gold-light);">STEP 2 OF 5 — SECURITY VERIFICATION</span>
                    <h2 class="serif-title fw-bold mb-2">Verify Your Identity</h2>
                    <p class="text-white-50 small mb-3">
                        We've sent a 6-digit verification code to <strong class="text-white" id="otpTargetEmail"></strong> and <strong class="text-white" id="otpTargetPhone"></strong>
                    </p>

                    <!-- Demo OTP Fast Fill Banner (Matching Reference Project) -->
                    <div class="d-inline-flex align-items-center gap-3 px-4 py-2 rounded-3 mb-3" style="background:rgba(16, 185, 129, 0.12);border:1px solid rgba(16, 185, 129, 0.35);">
                        <div class="text-start">
                            <div class="text-success fw-bold" style="font-size:0.72rem;letter-spacing:0.6px;">INSTANT DEMO VERIFICATION CODE</div>
                            <div class="fs-5 fw-bold text-white" style="letter-spacing:4px;" id="demoOtpDisplay">------</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-success fw-bold px-3" onclick="autoFillDemoOtp()">
                            <i class="fa-solid fa-bolt me-1"></i> Auto-Fill Code
                        </button>
                    </div>

                    <div class="otp-box-group">
                        <input type="text" maxlength="1" class="otp-digit" id="otpDigit1" placeholder="•" oninput="otpNext(this, 1)" onkeydown="otpBack(event, 1)">
                        <input type="text" maxlength="1" class="otp-digit" id="otpDigit2" placeholder="•" oninput="otpNext(this, 2)" onkeydown="otpBack(event, 2)">
                        <input type="text" maxlength="1" class="otp-digit" id="otpDigit3" placeholder="•" oninput="otpNext(this, 3)" onkeydown="otpBack(event, 3)">
                        <input type="text" maxlength="1" class="otp-digit" id="otpDigit4" placeholder="•" oninput="otpNext(this, 4)" onkeydown="otpBack(event, 4)">
                        <input type="text" maxlength="1" class="otp-digit" id="otpDigit5" placeholder="•" oninput="otpNext(this, 5)" onkeydown="otpBack(event, 5)">
                        <input type="text" maxlength="1" class="otp-digit" id="otpDigit6" placeholder="•" oninput="otpNext(this, 6)" onkeydown="otpBack(event, 6)">
                    </div>

                    <div class="small text-white-50 mb-4">
                        Didn't receive the code?
                        <button type="button" id="btnResendOtp" class="btn btn-link text-warning p-0 ms-1 text-decoration-none fw-bold" onclick="resendOtpCode()">Resend OTP</button>
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top border-secondary border-opacity-25">
                        <button type="button" class="btn btn-outline-wiz" onclick="goToWizardStep(1)">
                            <i class="fa-solid fa-arrow-left me-1"></i> Change Account Info
                        </button>
                        <button type="button" id="btnVerifyOtp" class="btn btn-gold px-4" onclick="submitOtpVerification()">
                            Verify & Continue <i class="fa-solid fa-check-circle ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 STEP 3: BUSINESS & STORE SETUP INFO
            =========================================== -->
            <div class="step-section" id="sectionStep3">
                <div class="mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                    <span class="badge mb-2" style="background:rgba(194,153,88,0.18);color:var(--gold-light);">STEP 3 OF 5 — STORE CONFIGURATION</span>
                    <h2 class="serif-title fw-bold mb-1">Configure Your Store Profile & Domain</h2>
                    <p class="text-white-50 small mb-0">Customize your store name, public storefront slug/domain, default currency, and branding assets.</p>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="wiz-label">Store / Business Name <span class="text-danger">*</span></label>
                        <input type="text" id="wizBusinessName" class="form-control wiz-input" placeholder="e.g. Velvet & Vogue Boutique" oninput="autoSuggestDomain(this.value)">
                    </div>

                    <div class="col-md-6">
                        <label class="wiz-label">Business Tagline</label>
                        <input type="text" id="wizTagline" class="form-control wiz-input" placeholder="e.g. Curated Luxury Fashion & Lifestyle">
                    </div>

                    <div class="col-md-6">
                        <label class="wiz-label">Official Business Email</label>
                        <input type="email" id="wizCompanyEmail" class="form-control wiz-input" placeholder="support@yourstore.com">
                    </div>

                    <div class="col-md-6">
                        <label class="wiz-label">Official Business Phone</label>
                        <input type="text" id="wizCompanyPhone" class="form-control wiz-input" placeholder="+1 800 555 0199">
                    </div>

                    <div class="col-md-8">
                        <label class="wiz-label">Physical Store / Warehouse Address</label>
                        <input type="text" id="wizCompanyAddress" class="form-control wiz-input" placeholder="742 Commerce Avenue, Suite 200, New York">
                    </div>

                    <div class="col-md-4">
                        <label class="wiz-label">Default Store Currency</label>
                        <select id="wizCurrencySymbol" class="form-select wiz-input">
                            <option value="$">USD ($) — US Dollar</option>
                            <option value="₹">INR (₹) — Indian Rupee</option>
                            <option value="€">EUR (€) — Euro</option>
                            <option value="£">GBP (£) — British Pound</option>
                            <option value="AED ">AED — UAE Dirham</option>
                        </select>
                    </div>

                    <!-- Domain / Store Slug -->
                    <div class="col-12">
                        <div class="p-3 rounded-3" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);">
                            <label class="wiz-label">Custom Domain / Store Folder Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary border-opacity-25 text-white-50">
                                    <?php echo htmlspecialchars(saas_base_url('')); ?>
                                </span>
                                <input type="text" id="wizDomain" class="form-control wiz-input" placeholder="example.com" oninput="debounceDomainCheck(true)">
                                <button type="button" class="btn btn-outline-wiz" onclick="checkDomainAvailability()">
                                    Check Availability
                                </button>
                            </div>
                            <div class="small mt-2" id="domainStatusMsg">
                                <span class="text-white-50">Your live standalone store &amp; admin panel will be provisioned at: <code class="text-warning" id="liveSlugPreview"><?php echo htmlspecialchars(saas_base_url('example.com')); ?></code></span>
                            </div>
                        </div>
                    </div>

                    <!-- Branding Uploads -->
                    <div class="col-md-6">
                        <label class="wiz-label">Company Logo (Optional)</label>
                        <div class="upload-zone" onclick="document.getElementById('wizLogoFile').click()">
                            <input type="file" id="wizLogoFile" accept=".png,.jpg,.jpeg,.webp,.svg" class="d-none" onchange="previewUpload(this, 'logoPreviewLabel')">
                            <i class="fa-solid fa-cloud-arrow-up fs-4 text-warning mb-1"></i>
                            <div class="small text-white-50" id="logoPreviewLabel">Click to upload Store Logo (PNG, JPG, WEBP)</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="wiz-label">Browser Favicon (Optional)</label>
                        <div class="upload-zone" onclick="document.getElementById('wizFaviconFile').click()">
                            <input type="file" id="wizFaviconFile" accept=".ico,.png,.webp" class="d-none" onchange="previewUpload(this, 'favPreviewLabel')">
                            <i class="fa-solid fa-icons fs-4 text-warning mb-1"></i>
                            <div class="small text-white-50" id="favPreviewLabel">Click to upload Favicon (ICO, PNG, WEBP)</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4 pt-3 border-top border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-wiz" onclick="goToWizardStep(2)">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back
                    </button>
                    <button type="button" id="btnStep3Next" class="btn btn-gold px-4" onclick="processStep3()">
                        Continue to Choose Template <i class="fa-solid fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>

            <!-- ==========================================
                 STEP 4: CHOOSE STOREFRONT TEMPLATE
            =========================================== -->
            <div class="step-section" id="sectionStep4">
                <div class="mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                    <span class="badge mb-2" style="background:rgba(194,153,88,0.18);color:var(--gold-light);">STEP 4 OF 5 — STOREFRONT DESIGN</span>
                    <h2 class="serif-title fw-bold mb-1">Choose Your Website Theme Template</h2>
                    <p class="text-white-50 small mb-0">Select your preferred eCommerce storefront template. Each template comes pre-loaded with Superadmin-curated Hero Banners, Categories, and Products.</p>
                </div>

                <input type="hidden" id="wizChosenTemplate" value="<?php echo htmlspecialchars($preselected_tpl_key); ?>">

                <div class="row g-4">
                    <?php foreach ($templates_catalog as $tKey => $tpl):
                        $isAllowed = in_array($tKey, $allowed_templates, true) || in_array($tpl['slug'], $allowed_templates, true);
                    ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="tpl-select-card <?php echo $tKey === $preselected_tpl_key ? 'selected' : ''; ?> <?php echo !$isAllowed ? 'locked' : ''; ?>"
                             id="tplCard_<?php echo $tKey; ?>"
                             data-tpl="<?php echo $tKey; ?>"
                             data-slug="<?php echo $tpl['slug']; ?>"
                             data-title="<?php echo htmlspecialchars($tpl['title']); ?>"
                             onclick="selectWizardTemplate('<?php echo $tKey; ?>')">
                            <div class="tpl-thumb-wrap">
                                <img src="<?php echo htmlspecialchars($tpl['preview_img']); ?>" alt="<?php echo htmlspecialchars($tpl['title']); ?>">
                                <div class="tpl-check-badge"><i class="fa-solid fa-check"></i></div>
                                <span class="badge position-absolute top-0 start-0 m-2" style="background:rgba(11,15,25,0.85);color:var(--gold-light);font-size:0.68rem;">
                                    <?php echo htmlspecialchars($tpl['badge']); ?>
                                </span>
                            </div>
                            <div class="p-3">
                                <h5 class="fw-bold text-white mb-1"><?php echo htmlspecialchars($tpl['title']); ?></h5>
                                <p class="text-white-50 small mb-2"><?php echo htmlspecialchars($tpl['subtitle']); ?></p>
                                <ul class="list-unstyled small text-white-50 mb-3">
                                    <?php foreach ($tpl['features'] as $f): ?>
                                    <li class="mb-1"><i class="fa-solid fa-circle-check text-success me-1"></i> <?php echo htmlspecialchars($f); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <div class="d-flex gap-2">
                                    <a href="<?php echo htmlspecialchars($tpl['demo_url']); ?>" target="_blank" class="btn btn-sm btn-outline-wiz w-100" onclick="event.stopPropagation();">
                                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Live Demo Preview
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="d-flex justify-content-between mt-4 pt-3 border-top border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-wiz" onclick="goToWizardStep(3)">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back to Setup Info
                    </button>
                    <button type="button" class="btn btn-gold px-4" onclick="processStep4()">
                        Review & Proceed to Payment <i class="fa-solid fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>

            <!-- ==========================================
                 STEP 5: SUMMARY & PAYMENT CHECKOUT
            =========================================== -->
            <div class="step-section" id="sectionStep5">
                <div class="mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                    <span class="badge mb-2" style="background:rgba(194,153,88,0.18);color:var(--gold-light);">STEP 5 OF 5 — FINALIZE & DEPLOY</span>
                    <h2 class="serif-title fw-bold mb-1">Subscription Summary & Payment</h2>
                    <p class="text-white-50 small mb-0">Review your store configuration and complete payment to automatically provision your store and admin panel.</p>
                </div>

                <div class="row g-4">
                    <!-- Left: Order & Deployment Summary -->
                    <div class="col-lg-6">
                        <div class="p-4 rounded-4 h-100" style="background:var(--dark-surface);border:1px solid rgba(255,255,255,0.08);">
                            <h5 class="fw-bold text-warning mb-3"><i class="fa-solid fa-receipt me-2"></i>Subscription & Deployment Summary</h5>
                            <div class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-25">
                                <span class="text-white-50 small">Software Plan</span>
                                <strong class="text-white small" id="sumPlanName"><?php echo htmlspecialchars($plan_name); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-25">
                                <span class="text-white-50 small">Store Business Name</span>
                                <strong class="text-white small" id="sumBusinessName">-</strong>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-25">
                                <span class="text-white-50 small">Storefront URL Slug</span>
                                <strong class="text-warning small" id="sumDomain">-</strong>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-25">
                                <span class="text-white-50 small">Admin Login Account</span>
                                <strong class="text-white small" id="sumAdminEmail">-</strong>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-25">
                                <span class="text-white-50 small">Selected Theme Template</span>
                                <strong class="text-info small" id="sumTemplate"><?php echo htmlspecialchars($templates_catalog[$preselected_tpl_key]['title'] ?? 'Template 1 (fashion)'); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-3 mt-2">
                                <span class="fw-bold text-white">Total Subscription Rate</span>
                                <span class="fs-3 fw-bold text-warning" id="sumTotalAmount"><?php echo $plan_currency_sym . number_format($plan_price, 2); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Payment Gateway Selection -->
                    <div class="col-lg-6">
                        <div class="p-4 rounded-4 h-100" style="background:var(--dark-surface);border:1px solid rgba(255,255,255,0.08);">
                            <h5 class="fw-bold text-white mb-3"><i class="fa-solid fa-shield-halved text-success me-2"></i>Select Payment Gateway</h5>

                            <input type="hidden" id="selectedGateway" value="sandbox">

                            <div class="d-flex flex-column gap-3 mb-3">
                                <?php if ($stripe_enabled): ?>
                                <div class="gw-pill" id="gwPill_stripe" onclick="selectPaymentGateway('stripe')">
                                    <i class="fa-brands fa-cc-stripe fs-3 text-info"></i>
                                    <div>
                                        <div class="fw-bold text-white small">Stripe Official Checkout</div>
                                        <div class="text-white-50" style="font-size:0.75rem;">Pay securely via Credit / Debit Card or Apple Pay</div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if ($razorpay_enabled): ?>
                                <div class="gw-pill" id="gwPill_razorpay" onclick="selectPaymentGateway('razorpay')">
                                    <i class="fa-solid fa-building-columns fs-4 text-primary"></i>
                                    <div>
                                        <div class="fw-bold text-white small">Razorpay Standard</div>
                                        <div class="text-white-50" style="font-size:0.75rem;">UPI, Cards, NetBanking & International Payments</div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if ($sandbox_enabled): ?>
                                <div class="gw-pill active" id="gwPill_sandbox" onclick="selectPaymentGateway('sandbox')">
                                    <i class="fa-solid fa-flask fs-4 text-success"></i>
                                    <div>
                                        <div class="fw-bold text-white small">Sandbox / Instant Demo Checkout</div>
                                        <div class="text-white-50" style="font-size:0.75rem;">Instant verified test payment & automated store deployment</div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="toggleSandboxSim" checked>
                                <label class="form-check-label small text-white-50" for="toggleSandboxSim">
                                    Simulate instant payment (Sandbox / Demo Mode for testing)
                                </label>
                            </div>

                            <button type="button" id="btnSubmitPayment" class="btn btn-gold w-100 py-3 fs-6" onclick="executeFinalPayment()">
                                <i class="fa-solid fa-lock me-2"></i> Payment
                            </button>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-start mt-4 pt-3 border-top border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-wiz" onclick="goToWizardStep(4)">
                        <i class="fa-solid fa-arrow-left me-1"></i> Change Template
                    </button>
                </div>
            </div>

            <!-- ==========================================
                 LIVE 7-STEP DEPLOYMENT PROGRESSION
            =========================================== -->
            <div class="step-section" id="sectionDeploying">
                <div class="text-center py-4">
                    <div class="spinner-border text-warning mb-3" style="width:3.2rem;height:3.2rem;" role="status"></div>
                    <h2 class="serif-title fw-bold mb-1">Provisioning Your eCommerce Cloud Instance...</h2>
                    <p class="text-white-50 small mb-4">Please wait while we configure your tenant storefront, Superadmin template catalog, and admin panel.</p>

                    <div class="progress mx-auto mb-4" style="max-width:540px;height:12px;background:rgba(255,255,255,0.08);border-radius:20px;">
                        <div id="deployProgressBar" class="progress-bar bg-warning progress-bar-striped progress-bar-animated" style="width:15%;"></div>
                    </div>

                    <div class="text-start mx-auto p-4 rounded-4" style="max-width:560px;background:var(--dark-surface);border:1px solid rgba(255,255,255,0.08);">
                        <div class="small mb-2 text-success" id="dpStep1"><i class="fa-solid fa-check-circle me-2"></i>1. Verifying subscription payment & generating license key...</div>
                        <div class="small mb-2 text-white-50" id="dpStep2"><i class="fa-regular fa-circle me-2"></i>2. Registering tenant slug & domain routing...</div>
                        <div class="small mb-2 text-white-50" id="dpStep3"><i class="fa-regular fa-circle me-2"></i>3. Applying selected storefront theme template (<span id="dpTplLabel">template1</span>)...</div>
                        <div class="small mb-2 text-white-50" id="dpStep4"><i class="fa-regular fa-circle me-2"></i>4. Seeding Superadmin-customized Hero Banners, Categories & Products...</div>
                        <div class="small mb-2 text-white-50" id="dpStep5"><i class="fa-regular fa-circle me-2"></i>5. Creating Store Owner Administrator credentials...</div>
                        <div class="small mb-2 text-white-50" id="dpStep6"><i class="fa-regular fa-circle me-2"></i>6. Configuring store currency, tax, shipping & payment gateways...</div>
                        <div class="small mb-0 text-white-50" id="dpStep7"><i class="fa-regular fa-circle me-2"></i>7. Finalizing Launch Hub & activating storefront!</div>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 DEPLOYMENT COMPLETE / LAUNCH HUB
            =========================================== -->
            <div class="step-section" id="sectionComplete">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:72px;height:72px;background:rgba(16,185,129,0.15);color:#10b981;font-size:2rem;">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <h2 class="serif-title fw-bold mb-1">Your eCommerce Store is Live!</h2>
                    <p class="text-white-50 small">Your storefront and tenant administration panel have been provisioned and seeded with your chosen template.</p>
                </div>

                <div class="row g-4 mb-4">
                    <!-- Launch Storefront Card -->
                    <div class="col-md-6">
                        <div class="p-4 rounded-4 h-100 text-center" style="background:var(--dark-surface);border:1px solid rgba(16,185,129,0.3);">
                            <i class="fa-solid fa-store fs-2 text-success mb-2"></i>
                            <h5 class="fw-bold text-white">Frontend Customer Website</h5>
                            <p class="text-white-50 small mb-3">Visit your live customer-facing multi-template storefront.</p>
                            <a href="#" id="hubLinkSite" target="_blank" class="btn btn-success fw-bold w-100 py-2">
                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open Storefront
                            </a>
                        </div>
                    </div>

                    <!-- Launch Admin Panel Card -->
                    <div class="col-md-6">
                        <div class="p-4 rounded-4 h-100 text-center" style="background:var(--dark-surface);border:1px solid rgba(194,153,88,0.4);">
                            <i class="fa-solid fa-gauge-high fs-2 text-warning mb-2"></i>
                            <h5 class="fw-bold text-white">Admin Management Panel</h5>
                            <p class="text-white-50 small mb-3">Manage your products, categories, orders, coupons & settings.</p>
                            <a href="<?php echo htmlspecialchars(saas_base_url('admin/auth/login')); ?>" id="hubLinkAdmin" target="_blank" class="btn btn-gold w-100 py-2">
                                <i class="fa-solid fa-key me-1"></i> Login to Admin Panel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Credentials Summary -->
                <div class="p-4 rounded-4" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.1);">
                    <h6 class="fw-bold text-warning mb-3"><i class="fa-solid fa-user-shield me-2"></i>Dedicated Administrator Credentials</h6>
                    <div class="row g-3 small">
                        <div class="col-md-4">
                            <span class="text-white-50 d-block">Administrator Email:</span>
                            <strong class="text-white" id="credAdminEmail">-</strong>
                        </div>
                        <div class="col-md-4">
                            <span class="text-white-50 d-block">Administrator Password:</span>
                            <strong class="text-warning" id="credAdminPass">-</strong>
                        </div>
                        <div class="col-md-4">
                            <span class="text-white-50 d-block">License Key & Order:</span>
                            <strong class="text-success" id="credLicenseKey">-</strong>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        let currentPlanCode = <?php echo json_encode($plan_code); ?>;
        let currentPlanName = <?php echo json_encode($plan_name); ?>;
        let currentPlanPrice = <?php echo json_encode($plan_price); ?>;
        let currentAllowedTemplates = <?php echo json_encode($allowed_templates); ?>;
        let currentDemoOtp = '';
        let domainValid = false;
        let domainTimer = null;

        function showAlert(msg, type = 'danger') {
            const box = document.getElementById('wizardAlert');
            box.className = `alert alert-${type} mb-4`;
            box.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'} me-2"></i>${msg}`;
            box.classList.remove('d-none');
        }

        function hideAlert() {
            document.getElementById('wizardAlert').classList.add('d-none');
        }

        function goToWizardStep(stepNum) {
            hideAlert();
            document.querySelectorAll('.step-section').forEach(s => s.classList.remove('active'));
            const target = document.getElementById('sectionStep' + stepNum);
            if (target) target.classList.add('active');

            for (let i = 1; i <= 5; i++) {
                const node = document.getElementById('stepNode' + i);
                node.classList.remove('active', 'completed');
                if (i < stepNum) node.classList.add('completed');
                else if (i === stepNum) node.classList.add('active');
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function togglePassVisibility() {
            const inp = document.getElementById('wizPassword');
            const icon = document.getElementById('eyeIcon');
            if (inp.type === 'password') {
                inp.type = 'text';
                icon.className = 'fa-regular fa-eye-slash';
            } else {
                inp.type = 'password';
                icon.className = 'fa-regular fa-eye';
            }
        }

        function onPlanSelectorChange(code) {
            const sel = document.getElementById('wizPlanSelector');
            const opt = sel.options[sel.selectedIndex];
            currentPlanCode = code;
            currentPlanName = opt.getAttribute('data-name');
            currentPlanPrice = parseFloat(opt.getAttribute('data-price') || '29');
            try {
                currentAllowedTemplates = JSON.parse(opt.getAttribute('data-allowed') || '["template1","template2","template3"]');
            } catch (e) {
                currentAllowedTemplates = ['template1', 'template2', 'template3'];
            }
            document.getElementById('navPlanBadge').textContent = `${currentPlanName} ($${currentPlanPrice.toFixed(2)})`;
            document.getElementById('sumPlanName').textContent = currentPlanName;
            document.getElementById('sumTotalAmount').textContent = `$${currentPlanPrice.toFixed(2)}`;
            refreshTemplateLocks();
        }

        function refreshTemplateLocks() {
            ['template1', 'template2', 'template3'].forEach(tKey => {
                const card = document.getElementById('tplCard_' + tKey);
                if (!card) return;
                const slug = card.getAttribute('data-slug');
                if (currentAllowedTemplates.includes(tKey) || currentAllowedTemplates.includes(slug)) {
                    card.classList.remove('locked');
                } else {
                    card.classList.add('locked');
                }
            });
        }

        async function validateUniqueField(field) {
            const val = document.getElementById(field === 'email' ? 'wizEmail' : 'wizPhone').value.trim();
            if (!val) return true;
            const fd = new FormData();
            fd.append('action', 'check_unique');
            fd.append(field, val);
            try {
                const res = await fetch('subscribe_api.php', { method: 'POST', body: fd });
                const data = await res.json();
                const errEl = document.getElementById(field === 'email' ? 'errEmail' : 'errPhone');
                const inpEl = document.getElementById(field === 'email' ? 'wizEmail' : 'wizPhone');
                if (data.status === 'error' || data.valid === false) {
                    errEl.textContent = data.message;
                    errEl.className = 'small mt-1 text-danger';
                    inpEl.classList.add('is-invalid');
                    inpEl.classList.remove('is-valid');
                    return false;
                } else {
                    errEl.textContent = '✓ Available';
                    errEl.className = 'small mt-1 text-success';
                    inpEl.classList.remove('is-invalid');
                    inpEl.classList.add('is-valid');
                    return true;
                }
            } catch (e) {
                return true;
            }
        }

        async function processStep1() {
            hideAlert();
            const name = document.getElementById('wizName').value.trim();
            const email = document.getElementById('wizEmail').value.trim();
            const phone = document.getElementById('wizPhone').value.trim();
            const password = document.getElementById('wizPassword').value.trim();

            if (!name || !email || !phone || password.length < 6) {
                showAlert('Please fill in your Full Name, valid Email, Phone Number, and a Password of at least 6 characters.');
                return;
            }

            const btn = document.getElementById('btnStep1Next');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Sending OTP...';

            const fd = new FormData();
            fd.append('action', 'send_otp');
            fd.append('name', name);
            fd.append('email', email);
            fd.append('phone', phone);
            fd.append('password', password);

            try {
                const res = await fetch('subscribe_api.php', { method: 'POST', body: fd });
                const data = await res.json();
                btn.disabled = false;
                btn.innerHTML = 'Proceed to OTP Verification <i class="fa-solid fa-arrow-right ms-2"></i>';

                if (data.status === 'success') {
                    currentDemoOtp = String(data.demo_otp || '');
                    document.getElementById('otpTargetEmail').textContent = email;
                    document.getElementById('otpTargetPhone').textContent = phone;
                    document.getElementById('demoOtpDisplay').textContent = currentDemoOtp;
                    // Pre-fill Step 3 company email & phone if empty
                    if (!document.getElementById('wizCompanyEmail').value) document.getElementById('wizCompanyEmail').value = email;
                    if (!document.getElementById('wizCompanyPhone').value) document.getElementById('wizCompanyPhone').value = phone;
                    goToWizardStep(2);
                } else {
                    showAlert(data.message || 'Unable to send OTP.');
                }
            } catch (e) {
                btn.disabled = false;
                btn.innerHTML = 'Proceed to OTP Verification <i class="fa-solid fa-arrow-right ms-2"></i>';
                showAlert('Network error occurred. Please try again.');
            }
        }

        function otpNext(el, idx) {
            el.value = el.value.replace(/[^0-9]/g, '');
            if (el.value && idx < 6) {
                document.getElementById('otpDigit' + (idx + 1)).focus();
            }
        }

        function otpBack(e, idx) {
            if (e.key === 'Backspace' && !e.target.value && idx > 1) {
                document.getElementById('otpDigit' + (idx - 1)).focus();
            }
        }

        function autoFillDemoOtp() {
            if (!currentDemoOtp) return;
            for (let i = 0; i < 6; i++) {
                const d = document.getElementById('otpDigit' + (i + 1));
                if (d) d.value = currentDemoOtp[i] || '';
            }
        }

        async function resendOtpCode() {
            const fd = new FormData();
            fd.append('action', 'resend_otp');
            const res = await fetch('subscribe_api.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.status === 'success') {
                currentDemoOtp = String(data.demo_otp || '');
                document.getElementById('demoOtpDisplay').textContent = currentDemoOtp;
                showAlert('A new 6-digit verification code has been generated.', 'success');
            }
        }

        async function submitOtpVerification() {
            hideAlert();
            let otp = '';
            for (let i = 1; i <= 6; i++) {
                otp += (document.getElementById('otpDigit' + i).value || '');
            }
            if (otp.length !== 6) {
                showAlert('Please enter all 6 digits of your verification code.');
                return;
            }

            const fd = new FormData();
            fd.append('action', 'verify_otp');
            fd.append('otp', otp);

            const res = await fetch('subscribe_api.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.status === 'success') {
                goToWizardStep(3);
            } else {
                showAlert(data.message || 'Invalid OTP code.');
            }
        }

        const defaultDomainMsgHtml = `<span class="text-white-50">Your live standalone store &amp; admin panel will be provisioned at: <code class="text-warning" id="liveSlugPreview"><?php echo htmlspecialchars(saas_base_url('example.com')); ?></code></span>`;

        function autoSuggestDomain(val) {
            const slug = val.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            const domInp = document.getElementById('wizDomain');
            if (!domInp.dataset.touched) {
                domInp.value = slug ? (slug + '.com') : '';
                debounceDomainCheck(false);
            }
        }

        function debounceDomainCheck(manualEdit = false) {
            const domInp = document.getElementById('wizDomain');
            if (manualEdit) {
                if (domInp.value.trim() === '') {
                    delete domInp.dataset.touched;
                } else {
                    domInp.dataset.touched = '1';
                }
            }
            clearTimeout(domainTimer);
            if (!domInp.value.trim()) {
                domainValid = false;
                document.getElementById('domainStatusMsg').innerHTML = defaultDomainMsgHtml;
                return;
            }
            domainTimer = setTimeout(() => checkDomainAvailability(), 350);
        }

        async function checkDomainAvailability() {
            const raw = document.getElementById('wizDomain').value.trim();
            const msgBox = document.getElementById('domainStatusMsg');
            if (!raw) {
                domainValid = false;
                msgBox.innerHTML = defaultDomainMsgHtml;
                return false;
            }

            const fd = new FormData();
            fd.append('action', 'check_domain');
            fd.append('domain', raw);

            try {
                const res = await fetch('subscribe_api.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.status === 'success') {
                    domainValid = true;
                    document.getElementById('wizDomain').value = data.clean_domain;
                    msgBox.innerHTML = `<span class="text-success fw-bold">${data.message}</span> <span class="text-white-50 ms-2">URL: <code>${data.store_url}</code></span>`;
                    return true;
                } else {
                    domainValid = false;
                    msgBox.innerHTML = `<span class="text-danger fw-bold"><i class="fa-solid fa-circle-xmark me-1"></i>${data.message}</span>`;
                    return false;
                }
            } catch (e) {
                return false;
            }
        }

        function previewUpload(input, labelId) {
            if (input.files && input.files[0]) {
                document.getElementById(labelId).innerHTML = `<strong class="text-success"><i class="fa-solid fa-check me-1"></i>${input.files[0].name}</strong>`;
            }
        }

        async function processStep3() {
            hideAlert();
            const bName = document.getElementById('wizBusinessName').value.trim();
            const dom   = document.getElementById('wizDomain').value.trim();
            if (!bName) {
                showAlert('Please enter your Store / Business Name.');
                return;
            }
            if (!dom || dom.length < 3) {
                showAlert('Please enter a valid Store Folder / Domain Slug (at least 3 characters).');
                return;
            }
            const avail = await checkDomainAvailability();
            if (!avail) {
                showAlert('Please choose an available Store Folder / Domain Slug before continuing.');
                return;
            }
            goToWizardStep(4);
        }

        function selectWizardTemplate(tKey) {
            const card = document.getElementById('tplCard_' + tKey);
            if (!card || card.classList.contains('locked')) {
                showAlert('This template requires a higher plan tier. Please switch your plan in Step 1 or select an unlocked template.');
                return;
            }
            document.querySelectorAll('.tpl-select-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            document.getElementById('wizChosenTemplate').value = tKey;
        }

        function processStep4() {
            hideAlert();
            const tplKey = document.getElementById('wizChosenTemplate').value;
            const card = document.getElementById('tplCard_' + tplKey);
            const tplTitle = card ? card.getAttribute('data-title') : tplKey;

            document.getElementById('sumPlanName').textContent = currentPlanName;
            document.getElementById('sumBusinessName').textContent = document.getElementById('wizBusinessName').value.trim();
            document.getElementById('sumDomain').textContent = document.getElementById('wizDomain').value.trim();
            document.getElementById('sumAdminEmail').textContent = document.getElementById('wizEmail').value.trim();
            document.getElementById('sumTemplate').textContent = tplTitle;
            document.getElementById('sumTotalAmount').textContent = `$${currentPlanPrice.toFixed(2)}`;

            goToWizardStep(5);
        }

        function selectPaymentGateway(gw) {
            document.getElementById('selectedGateway').value = gw;
            document.querySelectorAll('.gw-pill').forEach(p => p.classList.remove('active'));
            const target = document.getElementById('gwPill_' + gw);
            if (target) target.classList.add('active');
        }

        function collectWizardFormData(actionName) {
            const fd = new FormData();
            fd.append('action', actionName);
            fd.append('plan_code', currentPlanCode);
            fd.append('name', document.getElementById('wizName').value.trim());
            fd.append('email', document.getElementById('wizEmail').value.trim());
            fd.append('phone', document.getElementById('wizPhone').value.trim());
            fd.append('password', document.getElementById('wizPassword').value.trim());
            fd.append('business_name', document.getElementById('wizBusinessName').value.trim());
            fd.append('tagline', document.getElementById('wizTagline').value.trim());
            fd.append('company_email', document.getElementById('wizCompanyEmail').value.trim());
            fd.append('company_phone', document.getElementById('wizCompanyPhone').value.trim());
            fd.append('company_address', document.getElementById('wizCompanyAddress').value.trim());
            fd.append('currency_symbol', document.getElementById('wizCurrencySymbol').value);
            fd.append('domain', document.getElementById('wizDomain').value.trim());
            fd.append('chosen_template', document.getElementById('wizChosenTemplate').value);

            const logoFile = document.getElementById('wizLogoFile');
            if (logoFile && logoFile.files[0]) {
                fd.append('company_logo', logoFile.files[0]);
            }
            const favFile = document.getElementById('wizFaviconFile');
            if (favFile && favFile.files[0]) {
                fd.append('favicon', favFile.files[0]);
            }
            return fd;
        }

        async function executeFinalPayment() {
            hideAlert();
            const gw = document.getElementById('selectedGateway').value;
            const simChecked = document.getElementById('toggleSandboxSim').checked;

            if (gw === 'sandbox' || simChecked) {
                runLiveDeploymentAndProvision('sandbox', 'SANDBOX-' + Date.now());
                return;
            }

            if (gw === 'razorpay' && typeof Razorpay !== 'undefined') {
                const options = {
                    key: <?php echo json_encode($razorpay_key_id); ?>,
                    amount: Math.round(currentPlanPrice * 100),
                    currency: "USD",
                    name: "LuxeCommerce SaaS",
                    description: currentPlanName + " Subscription",
                    handler: function (response) {
                        runLiveDeploymentAndProvision('razorpay', response.razorpay_payment_id || ('RZP-' + Date.now()));
                    }
                };
                const rzp = new Razorpay(options);
                rzp.open();
                return;
            }

            // Fallback / Stripe simulation
            runLiveDeploymentAndProvision(gw, gw.toUpperCase() + '-' + Date.now());
        }

        async function runLiveDeploymentAndProvision(paymentMethod, transactionId) {
            document.querySelectorAll('.step-section').forEach(s => s.classList.remove('active'));
            document.getElementById('sectionDeploying').classList.add('active');
            document.getElementById('dpTplLabel').textContent = document.getElementById('wizChosenTemplate').value;

            const bar = document.getElementById('deployProgressBar');
            const markStep = (n, pct) => {
                bar.style.width = pct + '%';
                const el = document.getElementById('dpStep' + n);
                if (el) {
                    el.className = 'small mb-2 text-success fw-semibold';
                    el.innerHTML = el.innerHTML.replace('fa-regular fa-circle', 'fa-solid fa-check-circle');
                }
            };

            const fd = collectWizardFormData('process_subscription');
            fd.append('payment_method', paymentMethod);
            fd.append('transaction_id', transactionId);

            setTimeout(() => markStep(2, 30), 250);
            setTimeout(() => markStep(3, 50), 550);
            setTimeout(() => markStep(4, 70), 850);

            try {
                const res = await fetch('subscribe_api.php', { method: 'POST', body: fd });
                const data = await res.json();

                markStep(5, 85);
                markStep(6, 95);
                markStep(7, 100);

                setTimeout(() => {
                    if (data.status === 'success' || data.success) {
                        document.getElementById('sectionDeploying').classList.remove('active');
                        document.getElementById('sectionComplete').classList.add('active');
                        document.getElementById('hubLinkSite').href = data.website_url || data.store_url;
                        document.getElementById('hubLinkAdmin').href = data.admin_url;
                        document.getElementById('credAdminEmail').textContent = data.admin_email;
                        document.getElementById('credAdminPass').textContent = data.admin_password;
                        document.getElementById('credLicenseKey').textContent = `${data.license_key} (${data.order_number})`;
                        for (let i = 1; i <= 5; i++) {
                            const node = document.getElementById('stepNode' + i);
                            node.classList.remove('active');
                            node.classList.add('completed');
                        }
                    } else {
                        goToWizardStep(5);
                        showAlert(data.message || 'Provisioning failed.');
                    }
                }, 600);
            } catch (e) {
                goToWizardStep(5);
                showAlert('Network error during deployment.');
            }
        }
    </script>
</body>
</html>
