<!doctype html>
<html
  lang="en"
  class="layout-navbar-fixed layout-menu-fixed layout-compact"
  dir="ltr"
  data-skin="default"
  data-bs-theme="light"
  data-assets-path="<?= base_url('assets/'); ?>"
  data-template="vertical-menu-template">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <title><?= isset($title) ? html_escape($title) : 'Modave Admin Panel'; ?></title>

    <!-- Favicon -->
    <?php
      $admin_favicon = (!empty($store_settings['site_favicon']))
        ? base_url('../website/assets/images/logo/' . $store_settings['site_favicon'])
        : base_url('assets/img/favicon/codeulas_logo_small.webp');
    ?>
    <link rel="shortcut icon" href="<?= $admin_favicon; ?>" />
    <link rel="icon" href="<?= $admin_favicon; ?>" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
      rel="stylesheet" />

    <link rel="stylesheet" href="<?= base_url('assets/vendor/fonts/iconify-icons.css'); ?>" />
    <!-- Font Awesome 7 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/vendor/libs/node-waves/node-waves.css'); ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/vendor/libs/pickr/pickr-themes.css'); ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/vendor/css/core.css'); ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/css/demo.css'); ?>" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css'); ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css'); ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css'); ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/vendor/libs/animate-css/animate.css'); ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/vendor/libs/sweetalert2/sweetalert2.css'); ?>" />
    <style>
      .dataTables_wrapper .row:first-child {
        padding: 1rem 1.25rem 0.75rem;
        margin: 0;
        align-items: center;
      }
      .dataTables_wrapper .row:last-child {
        padding: 0.75rem 1.25rem 1rem;
        margin: 0;
        align-items: center;
      }
      .dataTables_wrapper .dataTables_filter input {
        margin-left: 0.5rem;
      }
      .dataTables_wrapper table.dataTable {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
      }
    </style>

    <!-- Helpers -->
    <script src="<?= base_url('assets/vendor/js/helpers.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/js/template-customizer.js'); ?>"></script>
    <script src="<?= base_url('assets/js/config.js'); ?>"></script>
  </head>

  <body>
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
