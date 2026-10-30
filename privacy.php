<?php
require_once __DIR__ . '/saas_core.php';
$settings = get_all_saas_settings();
$platformName = $settings['platform_name'] ?? 'Codeulas Cloud';
$privacyHtml = $settings['privacy_content'] ?? '<p>Privacy policy details.</p>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy — <?= htmlspecialchars($platformName) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0b0f19; color: #e2e8f0; margin: 0; line-height: 1.7; }
        .top-nav { background: rgba(15,23,42,0.9); border-bottom: 1px solid rgba(255,255,255,0.08); padding: 18px 5%; display: flex; justify-content: space-between; align-items: center; }
        .brand { color: #fff; font-weight: 800; font-size: 1.25rem; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .brand span { background: linear-gradient(135deg, #6366f1, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .btn-back { color: #cbd5e1; text-decoration: none; padding: 8px 16px; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; font-size: 0.9rem; }
        .container { max-width: 880px; margin: 60px auto; padding: 40px; background: rgba(30,41,59,0.65); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; }
        h1 { font-size: 2.2rem; margin-top: 0; color: #fff; }
        h3 { color: #818cf8; margin-top: 28px; }
        p { color: #cbd5e1; }
    </style>
</head>
<body>
    <nav class="top-nav">
        <a href="index.php" class="brand">⚡ <span><?= htmlspecialchars($platformName) ?></span></a>
        <a href="index.php" class="btn-back">← Back to Landing Page</a>
    </nav>
    <div class="container">
        <h1>Privacy Policy</h1>
        <p style="color:#94a3b8;font-size:0.9rem;">Last Updated: <?= date('F Y') ?></p>
        <hr style="border:0;border-top:1px solid rgba(255,255,255,0.08);margin:24px 0;">
        <div><?= $privacyHtml ?></div>
    </div>
</body>
</html>
