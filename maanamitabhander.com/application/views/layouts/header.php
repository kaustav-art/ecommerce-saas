<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
    <meta charset="utf-8">
    <title><?= isset($title) ? html_escape($title) : html_escape($site_name ?? 'Store'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="<?= html_escape($site_name ?? 'Store'); ?> - Multipurpose eCommerce Store">

    <!-- Fonts -->
    <link rel="stylesheet" href="<?= base_url('assets/fonts/fonts.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/fonts/font-icons.css'); ?>">
    <!-- Font Awesome 7 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css">

    <!-- Core CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/swiper-bundle.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/animate.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/css/styles.css?v=' . (file_exists(FCPATH . 'assets/css/styles.css') ? filemtime(FCPATH . 'assets/css/styles.css') : '1.1')); ?>">

    <!-- Font Awesome Integration Helpers & Category Circle Rules -->
    <style>
        /* Sticky Header */
        html {
            scroll-padding-top: 80px;
        }
        #header,
        header.header-default {
            position: relative !important;
            top: auto !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            z-index: 1020 !important;
            background-color: #ffffff !important;
        }
        #header.is-sticky,
        #header.header-bg {
            background-color: #ffffff !important;
            box-shadow: none !important;
        }

        #header .nav-icon .nav-icon-item i[class*="fa-"] {
            font-size: 20px;
            color: #111;
            transition: color 0.2s ease;
        }
        #header .nav-icon .nav-icon-item:hover i[class*="fa-"] {
            color: var(--primary, #000);
        }
        .box-icon i[class*="fa-"] {
            font-size: 16px;
            line-height: 1;
        }
        /* Hide compare and quick view icons on product cards */
        .card-product .box-icon.compare,
        .card-product .box-icon.quickview,
        .card-product .btn-icon-action.compare {
            display: none !important;
        }
        /* 1px border for Add to Cart and Wishlist buttons on product cards */
        .card-product .btn-main-product {
            border: 1px solid #d1d5db !important;
        }
        .card-product .btn-main-product:hover {
            border-color: var(--main, #181818) !important;
        }
        .card-product .box-icon.wishlist,
        .card-product .box-icon {
            border: 1px solid #d1d5db !important;
        }
        .card-product .box-icon.wishlist:hover,
        .card-product .box-icon:hover {
            border-color: var(--main, #181818) !important;
        }
        .tf-icon-box .icon-box i[class*="fa-"] {
            font-size: 38px;
            color: #111;
        }
        /* Perfect Circle Category Images */
        .collection-circle .img-style {
            aspect-ratio: 1 / 1 !important;
            border-radius: 50% !important;
            overflow: hidden !important;
            background-color: #f7f7f7;
            -webkit-mask-image: -webkit-radial-gradient(white, black);
            isolation: isolate;
        }
        .collection-circle .img-style img,
        .collection-circle .img-style > img {
            width: 100% !important;
            height: 100% !important;
            aspect-ratio: 1 / 1 !important;
            object-fit: cover !important;
            object-position: center center !important;
            border-radius: 50% !important;
            display: block !important;
        }
        /* Preloader Styles (Template Preload) */
        .preload-wrapper .preload-container {
            display: flex;
        }
        .preload-container {
            display: none;
            position: fixed;
            top: 0;
            bottom: 0;
            right: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #ffffff;
            z-index: 99999999999;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .preload-logo {
            position: relative;
            width: 65px;
            height: 65px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .preload-logo .spinner {
            width: 60px;
            height: 60px;
            border: 3px solid rgba(0, 0, 0, 0.08);
            border-top: 3px solid var(--primary, #111);
            border-radius: 50%;
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            margin: auto;
            animation: spin 0.8s infinite linear;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <!-- Favicon -->
    <?php
      $web_favicon = base_url('assets/images/logo/codeulas_logo_small.webp');
    ?>
    <link rel="shortcut icon" href="<?= $web_favicon; ?>" type="image/webp">
    <link rel="icon" type="image/webp" href="<?= $web_favicon; ?>">
    <link rel="apple-touch-icon-precomposed" href="<?= $web_favicon; ?>">

    <script>
        window.IS_USER_LOGGED_IN = <?= !empty($is_logged_in) ? 'true' : 'false'; ?>;
        window.BASE_URL = '<?= base_url(); ?>';
        window.SITE_URL = '<?= site_url(); ?>';
        window.STORE_NAME = '<?= html_escape($site_name ?? 'Store'); ?>';

        // Preloader failsafe: ensure overlay fades out even if external JS is delayed
        window.addEventListener('load', function() {
            setTimeout(function() {
                var p = document.querySelector('.preload');
                if (p) {
                    p.style.transition = 'opacity 0.4s ease';
                    p.style.opacity = '0';
                    setTimeout(function() { if (p && p.parentNode) p.parentNode.removeChild(p); }, 400);
                }
            }, 600);
        });
    </script>
</head>
<body class="preload-wrapper">
    <!-- Scroll Top -->
    <button id="scroll-top">
        <svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M3 11.9175L12 2.91748L21 11.9175H16.5V20.1675C16.5 20.3664 16.421 20.5572 16.2803 20.6978C16.1397 20.8385 15.9489 20.9175 15.75 20.9175H8.25C8.05109 20.9175 7.86032 20.8385 7.71967 20.6978C7.57902 20.5572 7.5 20.3664 7.5 20.1675V11.9175H3Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg> 
    </button>

    <!-- preload -->
    <div class="preload preload-container">
        <div class="preload-logo">
            <div class="spinner"></div>
        </div>
    </div>
    <!-- /preload -->

    <div id="wrapper">
