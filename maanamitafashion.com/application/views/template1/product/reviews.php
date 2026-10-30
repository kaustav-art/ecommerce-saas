<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$prod_title = $product['title'] ?? ($product['name'] ?? 'Product');
$prod_slug  = $product['slug'] ?? 'common';
$grc_qs     = empty($is_user_project) && ($active_template ?? '') === 'grocery' ? '?preview_tpl=grocery' : '';

$raw_img = !empty($product['main_image']) ? $product['main_image'] : ($product['image'] ?? 'products/womens/women-1.jpg');
$clean_prod_img = ltrim(trim((string)$raw_img), '/');
if (strpos($clean_prod_img, 'assets/images/') === 0) {
    $clean_prod_img = substr($clean_prod_img, strlen('assets/images/'));
}
$prod_img_url = (strpos($clean_prod_img, 'http://') === 0 || strpos($clean_prod_img, 'https://') === 0)
    ? $clean_prod_img
    : base_url('assets/images/' . $clean_prod_img);

$reg_price  = (float)($product['price'] ?? 0);
$sale_price = !empty($product['sale_price']) && (float)$product['sale_price'] > 0 ? (float)$product['sale_price'] : null;
$eff_price  = $sale_price ?: $reg_price;

$actual_reviews = (!empty($product['reviews']) && is_array($product['reviews'])) ? $product['reviews'] : [];
$rating_counts  = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
$sum_rating     = 0;
foreach ($actual_reviews as $r_item) {
    $r_score = max(1, min(5, (int)($r_item['rating'] ?? 5)));
    $rating_counts[$r_score]++;
    $sum_rating += $r_score;
}
$total_review_count = count($actual_reviews);
$computed_avg       = $total_review_count > 0 ? round($sum_rating / $total_review_count, 1) : 0.0;
$bar_avg_round      = round($computed_avg * 2) / 2;
?>

<!-- Breadcrumb -->
<div class="bg-light py-2 border-bottom">
    <div class="container" style="max-width: 1240px;">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="font-size: 12px;">
                <li class="breadcrumb-item"><a href="<?= site_url('home' . $grc_qs); ?>" class="text-muted text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= site_url('shop' . $grc_qs); ?>" class="text-muted text-decoration-none">Shop</a></li>
                <li class="breadcrumb-item"><a href="<?= site_url('product/' . $prod_slug . $grc_qs); ?>" class="text-muted text-decoration-none"><?= html_escape($prod_title); ?></a></li>
                <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">All Customer Reviews</li>
            </ol>
        </nav>
    </div>
</div>

<!-- All Customer Reviews Section -->
<section class="py-4" style="background-color: #f8fafc; min-height: 75vh;">
    <div class="container" style="max-width: 1240px;">

        <!-- Product Summary Bar -->
        <div class="card border rounded-3 bg-white shadow-none p-3 mb-4" style="border-color: #e5e7eb !important;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <a href="<?= site_url('product/' . $prod_slug . $grc_qs); ?>" class="d-inline-block border rounded-2 overflow-hidden flex-shrink-0" style="width: 64px; height: 64px; background-color: #fafafa; border-color: #e5e7eb !important;">
                        <img src="<?= $prod_img_url; ?>" alt="<?= html_escape($prod_title); ?>" style="width: 100%; height: 100%; object-fit: contain;" onerror="this.src='<?= base_url('assets/images/products/womens/women-1.jpg'); ?>'">
                    </a>
                    <div>
                        <h5 class="fw-bold mb-1" style="font-size: 16px; color: #003D29;">
                            <a href="<?= site_url('product/' . $prod_slug . $grc_qs); ?>" class="text-decoration-none" style="color: inherit;">
                                <?= html_escape($prod_title); ?>
                            </a>
                        </h5>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold text-dark" style="font-size: 15px;"><?= ($currency_symbol ?? '₹') . number_format($eff_price, 2); ?></span>
                            <?php if ($sale_price && $reg_price > $sale_price): ?>
                                <del class="text-muted small"><?= ($currency_symbol ?? '₹') . number_format($reg_price, 2); ?></del>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <a href="<?= site_url('product/' . $prod_slug . $grc_qs); ?>" class="btn btn-outline-secondary btn-sm fw-semibold px-3 py-2 rounded-2">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Product
                </a>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: Product Rating Summary & Breakdown Bar -->
            <div class="col-lg-4 col-md-5">
                <div class="p-4 rounded-3 border bg-white" style="border-color:#e5e7eb !important; position: sticky; top: 90px;">
                    <h5 class="fw-bold mb-3" style="color:#003D29; font-size:17px;">Customer Ratings</h5>
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-center px-2">
                            <div class="fw-bold" style="font-size:38px; line-height:1.1; color:#003D29;">
                                <?= number_format($computed_avg, 1); ?>
                            </div>
                            <div class="text-warning my-1" style="font-size:14px;">
                                <?php for ($s = 1; $s <= 5; $s++): ?>
                                    <?php if ($computed_avg > 0 && $s <= floor($bar_avg_round)): ?>
                                        <i class="fa-solid fa-star"></i>
                                    <?php elseif ($computed_avg > 0 && ($s - 0.5) == $bar_avg_round): ?>
                                        <i class="fa-solid fa-star-half-stroke"></i>
                                    <?php else: ?>
                                        <i class="fa-regular fa-star" style="color:#d1d5db;"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <div class="text-muted small" style="font-size:12px;">
                                <?= number_format($total_review_count); ?> <?= ($total_review_count === 1) ? 'Rating' : 'Ratings'; ?>
                            </div>
                        </div>

                        <div class="flex-grow-1 ps-2 border-start">
                            <?php
                            $bar_colors = [
                                5 => '#108910',
                                4 => '#26a541',
                                3 => '#26a541',
                                2 => '#ff9f00',
                                1 => '#ff6161'
                            ];
                            for ($star = 5; $star >= 1; $star--):
                                $s_cnt = (int)($rating_counts[$star] ?? 0);
                                $s_pct = $total_review_count > 0 ? round(($s_cnt / $total_review_count) * 100, 1) : 0;
                                $s_col = $bar_colors[$star];
                            ?>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="d-inline-flex align-items-center gap-1 fw-semibold text-dark" style="width:32px; font-size:12px;">
                                        <span><?= $star; ?></span>
                                        <i class="fa-solid fa-star" style="font-size:10px; color:#f59e0b;"></i>
                                    </div>
                                    <div class="flex-grow-1 rounded-pill overflow-hidden" style="height:8px; background-color:#f1f5f9;">
                                        <div class="h-100 rounded-pill" style="width:<?= $s_pct; ?>%; background-color:<?= $s_col; ?>; transition:width 0.3s ease;"></div>
                                    </div>
                                    <div class="text-muted text-end" style="width:32px; font-size:12px;">
                                        <?= number_format($s_cnt); ?>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: All Customer Reviews -->
            <div class="col-lg-8 col-md-7">
                <h4 class="fw-bold mb-3" style="color:#003D29; font-size:18px;">
                    All Customer Reviews (<?= number_format($total_review_count); ?>)
                </h4>

                <?php if (!empty($actual_reviews)): ?>
                    <?php foreach ($actual_reviews as $rev):
                        $r_name   = $rev['customer_name'] ?? ($rev['name'] ?? 'Customer');
                        $r_stars  = max(1, min(5, (int)($rev['rating'] ?? 5)));
                        $r_title  = trim((string)($rev['title'] ?? ''));
                        $r_text   = trim((string)($rev['comment'] ?? ($rev['review'] ?? '')));
                        $r_date   = !empty($rev['created_at']) ? date('d M, Y', strtotime($rev['created_at'])) : '';
                        $r_images = [];
                        if (!empty($rev['images'])) {
                            $decoded_imgs = is_array($rev['images']) ? $rev['images'] : json_decode($rev['images'], true);
                            if (is_array($decoded_imgs)) {
                                $r_images = array_values(array_filter($decoded_imgs));
                            }
                        }
                        $raw_r_avatar = trim((string)($rev['user_avatar'] ?? ($rev['avatar'] ?? '')));
                        $has_r_avatar = ($raw_r_avatar !== '' && !in_array(strtolower($raw_r_avatar), ['default-user.png', 'user-default.jpg', 'default.png'], true));
                        $r_avatar_url = '';
                        if ($has_r_avatar) {
                            $clean_r_av = ltrim($raw_r_avatar, '/');
                            if (strpos($clean_r_av, 'assets/images/') === 0) {
                                $clean_r_av = substr($clean_r_av, strlen('assets/images/'));
                            }
                            $r_avatar_url = (strpos($clean_r_av, 'http://') === 0 || strpos($clean_r_av, 'https://') === 0)
                                ? $clean_r_av
                                : base_url('assets/images/' . $clean_r_av);
                        }
                    ?>
                        <div class="p-3 mb-3 rounded-3 border bg-white" style="border-color:#e5e7eb !important;">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($has_r_avatar): ?>
                                        <img src="<?= $r_avatar_url; ?>" alt="<?= html_escape($r_name); ?>" class="rounded-circle shadow-sm border" style="width: 30px; height: 30px; object-fit: cover; flex-shrink: 0;" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                        <div class="rounded-circle align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 30px; height: 30px; background: linear-gradient(135deg, #108910, #1b52b3); font-size: 13px; flex-shrink: 0; display: none;">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 30px; height: 30px; background: linear-gradient(135deg, #108910, #1b52b3); font-size: 13px; flex-shrink: 0;">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="fw-semibold text-dark" style="font-size:14px;"><?= html_escape($r_name); ?></div>
                                </div>
                                <?php if ($r_date !== ''): ?>
                                    <span class="text-muted small ms-auto text-nowrap" style="font-size:12px;"><?= html_escape($r_date); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex align-items-center gap-1 flex-wrap mb-2">
                                <span class="d-inline-flex align-items-center gap-1" style="color:#f59e0b; font-size:13px;">
                                    <?php for ($si = 1; $si <= 5; $si++): ?>
                                        <i class="fa-<?= $si <= $r_stars ? 'solid' : 'regular'; ?> fa-star"></i>
                                    <?php endfor; ?>
                                    <span class="fw-bold text-dark ms-1" style="font-size:13.5px;"><?= number_format($r_stars, 1); ?></span>
                                </span>
                                <?php if ($r_title !== ''): ?>
                                    <span class="text-muted mx-1" style="font-size:14px; line-height:1;">&bull;</span>
                                    <strong class="text-dark" style="font-size:14px;"><?= html_escape($r_title); ?></strong>
                                <?php endif; ?>
                            </div>

                            <?php if ($r_text !== ''): ?>
                                <p class="mb-2 text-secondary" style="font-size:13.5px; line-height:1.55;"><?= nl2br(html_escape($r_text)); ?></p>
                            <?php endif; ?>

                            <?php if (!empty($r_images)): ?>
                                <div class="d-flex align-items-center gap-2 flex-wrap mt-2">
                                    <?php foreach ($r_images as $img_rel):
                                        $clean_rel = ltrim(trim((string)$img_rel), '/');
                                        if (strpos($clean_rel, 'assets/images/') === 0) {
                                            $clean_rel = substr($clean_rel, strlen('assets/images/'));
                                        }
                                        $img_full_url = base_url('assets/images/' . $clean_rel);
                                        $img_fallback = base_url('website/assets/images/' . $clean_rel);
                                    ?>
                                        <a href="<?= $img_full_url; ?>" target="_blank" class="d-inline-block border rounded-2 overflow-hidden" style="width:120px; height:120px; border-color:#e5e7eb !important;">
                                            <img src="<?= $img_full_url; ?>" alt="Review photo" style="width:120px; height:120px; object-fit:cover;" onerror="this.onerror=null;this.src='<?= $img_fallback; ?>';">
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-4 rounded-3 border bg-white text-center text-muted">
                        <i class="fa-regular fa-comment-dots fs-2 mb-2 text-success"></i>
                        <p class="mb-0">No customer reviews yet for <strong><?= html_escape($prod_title); ?></strong>.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
