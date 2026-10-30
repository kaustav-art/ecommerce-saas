<?php
/**
 * SaaS Template Demo Switcher (launch.php)
 * Launches isolated live demo previews for Fashion, Electronics, and Grocery templates
 * without creating a tenant or modifying real tenant data.
 */
require_once __DIR__ . '/saas_core.php';

$tpl = strtolower(trim($_GET['tpl'] ?? 'fashion'));
if (!in_array($tpl, ['fashion', 'electronics', 'grocery'], true)) {
    $tpl = 'fashion';
}

$target = strtolower(trim($_GET['target'] ?? 'website'));
if ($target === 'admin') {
    header('Location: ' . saas_base_url('admin/auth/login'));
    exit;
}

header('Location: ' . saas_base_url('website/home?preview_tpl=' . urlencode($tpl)));
exit;
