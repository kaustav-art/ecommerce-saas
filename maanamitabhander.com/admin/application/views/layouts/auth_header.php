<!doctype html>
<html
  lang="en"
  class="layout-wide customizer-hide"
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
    <title><?= isset($title) ? html_escape($title) : 'Sign In | Admin'; ?></title>

    <!-- Favicon -->
    <?php
      $auth_favicon = (!empty($store_settings['site_favicon']))
        ? base_url('../website/assets/images/logo/' . $store_settings['site_favicon'])
        : base_url('assets/img/favicon/codeulas_logo_small.webp');
    ?>
    <link rel="shortcut icon" href="<?= $auth_favicon; ?>" />
    <link rel="icon" href="<?= $auth_favicon; ?>" />

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
    <link rel="stylesheet" href="<?= base_url('assets/vendor/css/core.css'); ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/css/demo.css'); ?>" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/vendor/css/pages/page-auth.css'); ?>" />

    <!-- Helpers -->
    <script src="<?= base_url('assets/vendor/js/helpers.js'); ?>"></script>
    <script src="<?= base_url('assets/js/config.js'); ?>"></script>
  </head>

  <body>
