<?php
/**
 * Public SaaS Landing Website — Multi-Template E-Commerce SaaS Platform
 * Dynamically driven by saas_settings, saas_templates, and saas_plans (configurable in Superadmin).
 */
require_once __DIR__ . '/saas_core.php';

$pdo = get_saas_pdo();
$enquirySuccess = '';
$enquiryError   = '';

// Handle Contact / Enquiry Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_enquiry') {
    $name      = trim($_POST['name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $storeType = trim($_POST['store_type'] ?? 'fashion');
    $subject   = trim($_POST['subject'] ?? 'SaaS Platform Enquiry');
    $message   = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
        $enquiryError = 'Please enter a valid name, email address, and message.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO `saas_enquiries` (`name`, `email`, `phone`, `store_type`, `subject`, `message`, `status`) VALUES (?, ?, ?, ?, ?, ?, 'new')");
        $stmt->execute([$name, $email, $phone, $storeType, $subject, $message]);
        $enquirySuccess = 'Thank you! Your enquiry has been received. Our onboarding team will get back to you shortly.';
    }
}

$settings       = get_all_saas_settings();
$templates      = get_active_saas_templates();
$plans          = get_active_saas_plans();
$platformName   = $settings['platform_name'] ?? 'Codeulas Cloud';
$siteTitle      = $settings['site_title'] ?? 'Codeulas Cloud — Multi-Template SaaS E-Commerce Platform';
$heroBadge      = $settings['hero_badge'] ?? '🚀 Launch Your Online Store in 60 Seconds';
$heroHeading    = $settings['hero_heading'] ?? 'Build & Scale Your E-Commerce Brand With Industry-Crafted Templates';
$heroSubheading = $settings['hero_subheading'] ?? 'Choose from Fashion, Electronics, or Grocery storefronts. Select a flexible plan, connect your domain, and get a complete eCommerce storefront & inventory dashboard instantly.';
$ctaButtonText  = $settings['cta_button_text'] ?? 'Start Your Store Now';
$currencySym    = $settings['currency_symbol'] ?? '$';
$contactEmail   = $settings['contact_email'] ?? 'support@codeulas-saas.com';
$contactPhone   = $settings['contact_phone'] ?? '+1 (800) 555-0199';
$contactAddress = $settings['contact_address'] ?? '123 Commerce Way, Suite 500, New York, NY 10001';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteTitle) ?></title>
    <link rel="shortcut icon" type="image/webp" href="codeulas_logo_small.webp">
    <link rel="icon" type="image/webp" href="codeulas_logo_small.webp">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --bg-dark: #070b14;
            --bg-card: rgba(17, 24, 39, 0.78);
            --border-subtle: rgba(255, 255, 255, 0.08);
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --accent: #ec4899;
            --emerald: #10b981;
            --amber: #f59e0b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }
        * { box-sizing: border-box; scroll-behavior: smooth; }
        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            line-height: 1.6;
            overflow-x: hidden;
        }
        /* Navbar */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(7, 11, 20, 0.88);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-subtle);
            padding: 16px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            font-size: 1.35rem;
            color: #fff;
            text-decoration: none;
        }
        .nav-brand-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            color: #fff;
            box-shadow: 0 8px 20px -4px rgba(99, 102, 241, 0.5);
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 28px;
        }
        .nav-links a {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.94rem;
            transition: color 0.2s;
        }
        .nav-links a:hover { color: #fff; }
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.92rem;
            text-decoration: none;
            transition: all 0.22s ease;
            cursor: pointer;
            border: none;
        }
        .btn-outline {
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: rgba(255, 255, 255, 0.04);
            color: #fff;
        }
        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff;
            box-shadow: 0 10px 24px -6px rgba(99, 102, 241, 0.55);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px -6px rgba(236, 72, 153, 0.6);
        }
        .btn-emerald {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
        }
        .btn-emerald:hover { transform: translateY(-2px); }

        /* Hero Section */
        .hero {
            position: relative;
            padding: 96px 5% 80px;
            text-align: center;
            background: radial-gradient(circle at 50% 0%, rgba(99, 102, 241, 0.22), transparent 60%),
                        radial-gradient(circle at 80% 30%, rgba(236, 72, 153, 0.14), transparent 50%);
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 999px;
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.35);
            color: #a5b4fc;
            font-weight: 700;
            font-size: 0.86rem;
            margin-bottom: 24px;
        }
        .hero h1 {
            max-width: 920px;
            margin: 0 auto 22px;
            font-size: clamp(2.3rem, 5vw, 3.85rem);
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.02em;
        }
        .hero h1 span {
            background: linear-gradient(135deg, #818cf8, #f472b6, #34d399);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero p {
            max-width: 720px;
            margin: 0 auto 36px;
            font-size: 1.15rem;
            color: var(--text-muted);
        }
        .hero-cta-group {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 16px;
            margin-bottom: 56px;
        }
        .hero-cta-group .btn {
            padding: 15px 30px;
            font-size: 1rem;
            border-radius: 12px;
        }
        .stats-bar {
            max-width: 980px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            background: rgba(15, 23, 42, 0.72);
            border: 1px solid var(--border-subtle);
            border-radius: 20px;
            padding: 28px;
        }
        .stat-item h3 {
            margin: 0;
            font-size: 1.9rem;
            font-weight: 800;
            color: #fff;
        }
        .stat-item p {
            margin: 4px 0 0;
            font-size: 0.88rem;
            color: var(--text-muted);
        }

        /* Section Layout */
        .section {
            padding: 88px 5%;
            max-width: 1320px;
            margin: 0 auto;
        }
        .section-header {
            text-align: center;
            max-width: 720px;
            margin: 0 auto 56px;
        }
        .section-tag {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 999px;
            background: rgba(99, 102, 241, 0.14);
            color: #818cf8;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 12px;
        }
        .section-header h2 {
            margin: 0 0 14px;
            font-size: clamp(1.85rem, 3.5vw, 2.65rem);
            font-weight: 800;
        }
        .section-header p {
            margin: 0;
            color: var(--text-muted);
            font-size: 1.05rem;
        }

        /* Template Gallery */
        .templates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
            gap: 30px;
        }
        .template-card {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 22px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.28s ease, border-color 0.28s ease;
        }
        .template-card:hover {
            transform: translateY(-6px);
            border-color: rgba(99, 102, 241, 0.45);
        }
        .template-thumb {
            position: relative;
            height: 240px;
            background: #1e293b;
            overflow: hidden;
        }
        .template-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .template-card:hover .template-thumb img {
            transform: scale(1.05);
        }
        .template-badge {
            position: absolute;
            top: 16px;
            left: 16px;
            background: rgba(15, 23, 42, 0.88);
            backdrop-filter: blur(8px);
            color: #38bdf8;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
            border: 1px solid rgba(56, 189, 248, 0.35);
        }
        .template-body {
            padding: 28px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .template-body h3 {
            margin: 0 0 6px;
            font-size: 1.45rem;
            font-weight: 800;
        }
        .template-tagline {
            color: #a5b4fc;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 12px;
        }
        .template-desc {
            color: var(--text-muted);
            font-size: 0.92rem;
            margin-bottom: 20px;
        }
        .template-features {
            list-style: none;
            padding: 0;
            margin: 0 0 26px;
            flex: 1;
        }
        .template-features li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 0.88rem;
            color: #cbd5e1;
            margin-bottom: 9px;
        }
        .template-features li i {
            color: var(--emerald);
            margin-top: 4px;
        }
        .template-actions {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
            gap: 24px;
        }
        .feature-card {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 18px;
            padding: 28px;
        }
        .feature-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(99, 102, 241, 0.16);
            color: #818cf8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 18px;
        }
        .feature-card h4 {
            margin: 0 0 10px;
            font-size: 1.18rem;
            font-weight: 700;
        }
        .feature-card p {
            margin: 0;
            color: var(--text-muted);
            font-size: 0.92rem;
        }

        /* Pricing Section */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(330px, 1fr));
            gap: 28px;
            align-items: stretch;
        }
        .plan-card {
            position: relative;
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 24px;
            padding: 36px 30px;
            display: flex;
            flex-direction: column;
            transition: transform 0.25s ease;
        }
        .plan-card.featured {
            background: linear-gradient(180deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.95));
            border: 2px solid var(--primary);
            box-shadow: 0 20px 50px -15px rgba(99, 102, 241, 0.35);
        }
        .plan-ribbon {
            position: absolute;
            top: -14px;
            right: 24px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff;
            font-size: 0.76rem;
            font-weight: 800;
            text-transform: uppercase;
            padding: 6px 16px;
            border-radius: 999px;
        }
        .plan-name {
            font-size: 1.35rem;
            font-weight: 800;
            margin: 0 0 6px;
        }
        .plan-subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 22px;
        }
        .plan-price-wrap {
            display: flex;
            align-items: baseline;
            gap: 8px;
            margin-bottom: 18px;
        }
        .plan-price {
            font-size: 2.75rem;
            font-weight: 800;
            color: #fff;
        }
        .plan-old-price {
            text-decoration: line-through;
            color: #64748b;
            font-size: 1.2rem;
        }
        .plan-cycle {
            color: var(--text-muted);
            font-size: 0.95rem;
        }
        .plan-meta-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 22px;
        }
        .meta-pill {
            font-size: 0.78rem;
            font-weight: 600;
            padding: 4px 11px;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.06);
            color: #cbd5e1;
        }
        .plan-features {
            list-style: none;
            padding: 0;
            margin: 0 0 30px;
            flex: 1;
        }
        .plan-features li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 11px;
            font-size: 0.9rem;
            color: #e2e8f0;
        }
        .plan-features li i {
            color: var(--emerald);
            margin-top: 4px;
        }

        /* Contact / Enquiry Section */
        .contact-wrap {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 40px;
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 24px;
            padding: 44px;
        }
        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block;
            font-size: 0.86rem;
            font-weight: 600;
            margin-bottom: 6px;
            color: #cbd5e1;
        }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 10px;
            color: #fff;
            font-family: inherit;
            font-size: 0.95rem;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }

        /* Footer */
        .footer {
            border-top: 1px solid var(--border-subtle);
            padding: 56px 5% 36px;
            background: #050811;
        }
        .footer-grid {
            max-width: 1320px;
            margin: 0 auto 40px;
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr 1fr;
            gap: 32px;
        }
        .footer h5 {
            margin: 0 0 16px;
            font-size: 1rem;
            color: #fff;
        }
        .footer ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .footer ul li { margin-bottom: 10px; }
        .footer ul a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
        }
        .footer ul a:hover { color: #fff; }
        .footer-bottom {
            max-width: 1320px;
            margin: 0 auto;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            color: var(--text-muted);
            font-size: 0.86rem;
        }

        @media (max-width: 900px) {
            .nav-links { display: none; }
            .contact-wrap { grid-template-columns: 1fr; padding: 28px; }
            .footer-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

    <!-- Top Navigation -->
    <header class="navbar">
        <a href="index.php" class="nav-brand">
            <img src="logo.webp" alt="<?= htmlspecialchars($platformName) ?>" style="height:40px;width:auto;object-fit:contain;">
        </a>
        <nav class="nav-links">
            <a href="#templates">Store Templates</a>
            <a href="#features">Platform Features</a>
            <a href="#pricing">Pricing Plans</a>
            <a href="#workflow">How It Works</a>
            <a href="#contact">Contact & Enquiry</a>
        </nav>
        <div class="nav-actions">
            <a href="admin/auth/login" class="btn btn-outline"><i class="fa-solid fa-right-to-bracket"></i> Store Login</a>
            <a href="superadmin/login" class="btn btn-outline" title="SaaS Superadmin Portal"><i class="fa-solid fa-shield-halved"></i> Superadmin</a>
            <a href="subscribe.php" class="btn btn-primary"><i class="fa-solid fa-rocket"></i> Create Store</a>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-badge"><?= htmlspecialchars($heroBadge) ?></div>
        <h1><?= htmlspecialchars($heroHeading) ?></h1>
        <p><?= htmlspecialchars($heroSubheading) ?></p>
        <div class="hero-cta-group">
            <a href="subscribe.php" class="btn btn-primary"><i class="fa-solid fa-store"></i> <?= htmlspecialchars($ctaButtonText) ?></a>
            <a href="#templates" class="btn btn-outline"><i class="fa-solid fa-eye"></i> Preview Live Storefronts</a>
            <a href="#pricing" class="btn btn-outline"><i class="fa-solid fa-tags"></i> Compare Plans</a>
        </div>

        <div class="stats-bar">
            <div class="stat-item">
                <h3>3 Niche Themes</h3>
                <p>Fashion, Electronics & Grocery Storefronts</p>
            </div>
            <div class="stat-item">
                <h3>100% Isolated</h3>
                <p>Multi-Tenant Catalog, Orders & Customer Data</p>
            </div>
            <div class="stat-item">
                <h3>4 Gateways</h3>
                <p>Stripe, Razorpay, PayU & Cash on Delivery</p>
            </div>
            <div class="stat-item">
                <h3>Instant Setup</h3>
                <p>Automated Store & Tenant Admin Provisioning</p>
            </div>
        </div>
    </section>

    <!-- Template Gallery Section -->
    <section id="templates" class="section">
        <div class="section-header">
            <span class="section-tag">Multi-Template Storefront Engine</span>
            <h2>Explore Our 3 Industry Storefront Templates</h2>
            <p>Preview each live interactive storefront before registering. Every template connects seamlessly to the same powerful inventory, variant, cart, checkout, and order management engine.</p>
        </div>

        <div class="templates-grid">
            <?php foreach ($templates as $tpl):
                $tKey = $tpl['template_key'];
                $demoLink = 'launch.php?tpl=' . urlencode($tKey);
                $registerLink = 'subscribe.php?tpl=' . urlencode($tKey);
                $thumbUrl = !empty($tpl['preview_image']) ? $tpl['preview_image'] : 'website/assets/images/templates/' . $tKey . '.jpg';
            ?>
            <div class="template-card">
                <div class="template-thumb">
                    <img src="<?= htmlspecialchars($thumbUrl) ?>" alt="<?= htmlspecialchars($tpl['name']) ?>" onerror="this.src='logo.webp'">
                    <span class="template-badge"><?= htmlspecialchars($tpl['badge'] ?: ucfirst($tKey)) ?></span>
                </div>
                <div class="template-body">
                    <h3><?= htmlspecialchars($tpl['name']) ?></h3>
                    <div class="template-tagline"><?= htmlspecialchars($tpl['tagline']) ?></div>
                    <p class="template-desc"><?= htmlspecialchars($tpl['description']) ?></p>
                    <ul class="template-features">
                        <?php foreach ($tpl['features_list'] as $feat): ?>
                            <li><i class="fa-solid fa-circle-check"></i> <span><?= htmlspecialchars($feat) ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="template-actions">
                        <a href="<?= htmlspecialchars($demoLink) ?>" target="_blank" class="btn btn-outline">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Live Demo
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Platform Features Section -->
    <section id="features" class="section" style="padding-top:20px;">
        <div class="section-header">
            <span class="section-tag">Enterprise E-Commerce Core</span>
            <h2>Everything You Need to Run a High-Volume Online Store</h2>
            <p>Each tenant store gets a complete, isolated administration dashboard and storefront powered by our battle-tested eCommerce engine.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
                <h4>Dynamic Catalog & Variants</h4>
                <p>Manage simple and variable products with multi-attribute color swatches, sizes, storage capacities, grocery pack weights, warranty badges, and galleries.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-credit-card"></i></div>
                <h4>Multi-Gateway Checkout</h4>
                <p>Accept customer payments via Stripe, Razorpay, PayU, and Cash on Delivery (COD) with tenant-configurable API keys and webhook verification.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-truck-fast"></i></div>
                <h4>Order & Shipment Tracking</h4>
                <p>Process orders with item-level shipment dispatch, automated courier tracking links, printable GST/tax invoices, and customer return/refund workflows.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-Palette"></i></div>
                <h4>Switchable Storefront Themes</h4>
                <p>Start with Fashion, Electronics, or Grocery and customize banners, categories, brands, and store branding without touching a single line of HTML.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-bolt"></i></div>
                <h4>Coupons & Flash Deals</h4>
                <p>Boost conversions with percentage or fixed discount promo codes, minimum spend rules, countdown deal timers, and customer group discounts.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-lock"></i></div>
                <h4>Strict Multi-Tenant Security</h4>
                <p>Every tenant's products, inventory, customers, carts, orders, and reports are strictly isolated at the server and database level with RBAC staff permissions.</p>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="workflow" class="section" style="padding-top:20px;">
        <div class="section-header">
            <span class="section-tag">5-Step Onboarding Workflow</span>
            <h2>Launch Your Store in Minutes</h2>
            <p>From template preview to live storefront and merchant dashboard in five guided steps.</p>
        </div>
        <div class="features-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
            <div class="feature-card">
                <div class="feature-icon">1</div>
                <h4>Preview Templates</h4>
                <p>Test-drive the Fashion, Electronics, and Grocery demos live in your browser.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">2</div>
                <h4>Choose a Plan</h4>
                <p>Select a monthly or yearly subscription plan matching your catalog and order volume.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">3</div>
                <h4>Register Account</h4>
                <p>Enter your owner details, store name, and custom store slug with instant validation.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">4</div>
                <h4>Select Template & Pay</h4>
                <p>Pick your preferred storefront theme and complete secure payment verification.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">5</div>
                <h4>Start Selling</h4>
                <p>Your storefront and tenant admin dashboard are provisioned and ready immediately.</p>
            </div>
        </div>
    </section>

    <!-- Pricing Plans Section -->
    <section id="pricing" class="section">
        <div class="section-header">
            <span class="section-tag">Transparent SaaS Pricing</span>
            <h2>Flexible Plans for Every Business Stage</h2>
            <p>All plans include hosting, SSL security, storefront templates, and full access to the tenant merchant dashboard.</p>
        </div>

        <div class="pricing-grid">
            <?php foreach ($plans as $plan):
                $isFeatured = !empty($plan['is_featured']);
                $effectivePrice = $plan['effective_price'];
                $hasDiscount = ((float)$plan['price'] > $effectivePrice);
                $prodLimitText = ((int)$plan['product_limit'] === -1) ? 'Unlimited Products' : number_format((int)$plan['product_limit']) . ' Products';
                $ordLimitText  = ((int)$plan['order_limit'] === -1) ? 'Unlimited Orders' : number_format((int)$plan['order_limit']) . ' Orders/mo';
                $allowedTpls   = array_map('ucfirst', $plan['allowed_templates_list']);
            ?>
            <div class="plan-card <?= $isFeatured ? 'featured' : '' ?>">
                <?php if (!empty($plan['badge'])): ?>
                    <span class="plan-ribbon"><?= htmlspecialchars($plan['badge']) ?></span>
                <?php endif; ?>
                <h3 class="plan-name"><?= htmlspecialchars($plan['name']) ?></h3>
                <div class="plan-subtitle"><?= htmlspecialchars($plan['subtitle'] ?: $plan['tagline']) ?></div>
                <div class="plan-price-wrap">
                    <span class="plan-price"><?= htmlspecialchars($currencySym) ?><?= number_format($effectivePrice, 2) ?></span>
                    <?php if ($hasDiscount): ?>
                        <span class="plan-old-price"><?= htmlspecialchars($currencySym) ?><?= number_format((float)$plan['price'], 2) ?></span>
                    <?php endif; ?>
                    <span class="plan-cycle">/ <?= htmlspecialchars($plan['billing_cycle']) ?></span>
                </div>

                <div class="plan-meta-pills">
                    <span class="meta-pill"><i class="fa-solid fa-box"></i> <?= htmlspecialchars($prodLimitText) ?></span>
                    <span class="meta-pill"><i class="fa-solid fa-cart-shopping"></i> <?= htmlspecialchars($ordLimitText) ?></span>
                    <?php if ((int)$plan['trial_days'] > 0): ?>
                        <span class="meta-pill" style="color:#34d399;"><i class="fa-solid fa-clock"></i> <?= (int)$plan['trial_days'] ?>-Day Trial Supported</span>
                    <?php endif; ?>
                    <span class="meta-pill"><i class="fa-solid fa-palette"></i> <?= htmlspecialchars(implode(', ', $allowedTpls)) ?></span>
                </div>

                <ul class="plan-features">
                    <?php foreach ($plan['features_list'] as $f): ?>
                        <li><i class="fa-solid fa-check"></i> <span><?= htmlspecialchars($f) ?></span></li>
                    <?php endforeach; ?>
                </ul>

                <a href="subscribe.php?plan=<?= urlencode($plan['slug']) ?>" class="btn <?= $isFeatured ? 'btn-primary' : 'btn-outline' ?>" style="width:100%;padding:14px;">
                    Select <?= htmlspecialchars($plan['name']) ?> <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Contact & Enquiry Section -->
    <section id="contact" class="section">
        <div class="contact-wrap">
            <div>
                <span class="section-tag">Talk to Our Team</span>
                <h2 style="margin:0 0 14px;font-size:2rem;">Have Questions About Launching Your Store?</h2>
                <p style="color:var(--text-muted);margin-bottom:28px;">Send us an enquiry for custom enterprise onboarding, volume pricing, or template customization support.</p>

                <div style="margin-bottom:18px;">
                    <strong style="display:block;color:#fff;"><i class="fa-solid fa-envelope" style="color:var(--primary);margin-right:8px;"></i> Email Support</strong>
                    <span style="color:var(--text-muted);"><?= htmlspecialchars($contactEmail) ?></span>
                </div>
                <div style="margin-bottom:18px;">
                    <strong style="display:block;color:#fff;"><i class="fa-solid fa-phone" style="color:var(--primary);margin-right:8px;"></i> Sales & Onboarding</strong>
                    <span style="color:var(--text-muted);"><?= htmlspecialchars($contactPhone) ?></span>
                </div>
                <div>
                    <strong style="display:block;color:#fff;"><i class="fa-solid fa-location-dot" style="color:var(--primary);margin-right:8px;"></i> Headquarters</strong>
                    <span style="color:var(--text-muted);"><?= htmlspecialchars($contactAddress) ?></span>
                </div>
            </div>

            <div>
                <?php if ($enquirySuccess): ?>
                    <div style="padding:14px 18px;border-radius:10px;background:rgba(16,185,129,0.18);border:1px solid #10b981;color:#6ee7b7;margin-bottom:18px;">
                        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($enquirySuccess) ?>
                    </div>
                <?php endif; ?>
                <?php if ($enquiryError): ?>
                    <div style="padding:14px 18px;border-radius:10px;background:rgba(239,68,68,0.18);border:1px solid #ef4444;color:#fca5a5;margin-bottom:18px;">
                        <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($enquiryError) ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="#contact">
                    <input type="hidden" name="action" value="submit_enquiry">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group">
                            <label>Your Full Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="Jane Doe">
                        </div>
                        <div class="form-group">
                            <label>Email Address *</label>
                            <input type="email" name="email" class="form-control" required placeholder="jane@company.com">
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="+1 (555) 234-5678">
                        </div>
                        <div class="form-group">
                            <label>Interested Template</label>
                            <select name="store_type" class="form-control">
                                <option value="fashion">Fashion Store (Template 1)</option>
                                <option value="electronics">Electronics Store (Template 2)</option>
                                <option value="grocery">Grocery Store (Template 3)</option>
                                <option value="enterprise">All Templates / Enterprise</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Message / Enquiry *</label>
                        <textarea name="message" rows="3" class="form-control" required placeholder="Tell us about your business goals..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;">
                        <i class="fa-solid fa-paper-plane"></i> Send Enquiry
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-grid">
            <div>
                <a href="index.php" class="nav-brand" style="margin-bottom:14px;">
                    <img src="logo.webp" alt="<?= htmlspecialchars($platformName) ?>" style="height:40px;width:auto;object-fit:contain;">
                </a>
                <p style="color:var(--text-muted);font-size:0.9rem;max-width:340px;">
                    Multi-tenant, multi-template SaaS eCommerce platform empowering merchants to launch Fashion, Electronics, and Grocery stores in seconds.
                </p>
            </div>
            <div>
                <h5>Storefront Demos</h5>
                <ul>
                    <li><a href="launch.php?tpl=fashion" target="_blank">1. Fashion Store Demo</a></li>
                    <li><a href="launch.php?tpl=electronics" target="_blank">2. Electronics Store Demo</a></li>
                    <li><a href="launch.php?tpl=grocery" target="_blank">3. Grocery Store Demo</a></li>
                    <li><a href="website/home" target="_blank">Flagship Fashion Storefront</a></li>
                </ul>
            </div>
            <div>
                <h5>Merchant & Admin</h5>
                <ul>
                    <li><a href="subscribe.php">Register New Store</a></li>
                    <li><a href="admin/auth/login">Tenant Store Admin Login</a></li>
                    <li><a href="superadmin/login">SaaS Superadmin Portal</a></li>
                    <li><a href="#pricing">Subscription Plans</a></li>
                </ul>
            </div>
            <div>
                <h5>Legal & Support</h5>
                <ul>
                    <li><a href="terms.php">Terms & Conditions</a></li>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                    <li><a href="#contact">Contact & Enquiries</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>&copy; <?= date('Y') ?> <?= htmlspecialchars($platformName) ?>. All rights reserved.</span>
            <span>Powered by Multi-Tenant SaaS E-Commerce Engine</span>
        </div>
    </footer>

</body>
</html>
