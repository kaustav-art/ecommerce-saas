<?php
$pct = !empty($p['total_discount_percent']) ? (int)$p['total_discount_percent'] : 0;
if ($pct <= 0 && !empty($p['sale_price']) && !empty($p['price']) && $p['sale_price'] < $p['price']) {
    $pct = round((($p['price'] - $p['sale_price']) / $p['price']) * 100);
}
$curr = $currency_symbol ?? '$';
$hover_img = !empty($p['gallery_images_decoded'][0]) ? $p['gallery_images_decoded'][0] : (!empty($p['hover_image']) ? $p['hover_image'] : $p['main_image']);

// Extract color swatches (from $p['color_swatches'] and/or $p['variants'])
$p_swatches = [];
$seen_colors = [];
if (!empty($p['color_swatches']) && is_array($p['color_swatches'])) {
    foreach ($p['color_swatches'] as $sw) {
        $c_name = trim((string)($sw['name'] ?? 'Color'));
        $c_key  = strtolower($c_name);
        if ($c_name !== '' && !isset($seen_colors[$c_key])) {
            $seen_colors[$c_key] = true;
            $sw_img = !empty($sw['image']) ? $sw['image'] : $p['main_image'];
            $sw_img_url = (strpos($sw_img, 'http://') === 0 || strpos($sw_img, 'https://') === 0)
                ? $sw_img
                : base_url('assets/images/' . ltrim($sw_img, '/'));
            $p_swatches[] = [
                'name'       => $c_name,
                'color_code' => !empty($sw['color_code']) ? $sw['color_code'] : '#111827',
                'image'      => $sw_img_url,
            ];
        }
    }
}
if (!empty($p['variants']) && is_array($p['variants'])) {
    foreach ($p['variants'] as $pv) {
        if (!empty($pv['values']) && is_array($pv['values'])) {
            foreach ($pv['values'] as $val) {
                $rSlug = strtolower(trim((string)($val['attribute_slug'] ?? '')));
                $rType = strtolower(trim((string)($val['attribute_type'] ?? '')));
                $rName = strtolower(trim((string)($val['attribute_name'] ?? '')));
                $isCol = ($rSlug === 'color' || $rType === 'color' || strpos($rSlug, 'color-') === 0 || $rName === 'color');
                if ($isCol) {
                    $c_name = trim((string)($val['attribute_value'] ?? 'Color'));
                    $c_key  = strtolower($c_name);
                    if ($c_name !== '' && !isset($seen_colors[$c_key])) {
                        $seen_colors[$c_key] = true;
                        $sw_img = !empty($pv['image']) ? $pv['image'] : $p['main_image'];
                        $sw_img_url = (strpos($sw_img, 'http://') === 0 || strpos($sw_img, 'https://') === 0)
                            ? $sw_img
                            : base_url('assets/images/' . ltrim($sw_img, '/'));
                        $p_swatches[] = [
                            'name'       => $c_name,
                            'color_code' => !empty($val['color_code']) ? $val['color_code'] : '#111827',
                            'image'      => $sw_img_url,
                        ];
                    }
                }
            }
        }
    }
}
?>
<div class="card-product animate-fade-in">
    <div class="card-product-wrapper">
        <a href="<?= site_url('product/' . $p['slug']); ?>" class="product-img">
            <img class="lazyload img-product" 
                 data-src="<?= base_url('assets/images/' . $p['main_image']); ?>" 
                 src="<?= base_url('assets/images/' . $p['main_image']); ?>" 
                 alt="<?= html_escape($p['title']); ?>" 
                 onerror="this.src='<?= base_url('assets/images/products/womens/women-1.jpg'); ?>'">
            <img class="lazyload img-hover" 
                 data-src="<?= base_url('assets/images/' . $hover_img); ?>" 
                 src="<?= base_url('assets/images/' . $hover_img); ?>" 
                 alt="<?= html_escape($p['title']); ?>" 
                 onerror="this.src='<?= base_url('assets/images/products/womens/women-2.jpg'); ?>'">
        </a>

        <!-- Discount / Flash Sale Tag -->
        <?php if (!empty($p['is_flash_sale']) || $pct > 0): ?>
            <div class="product-badge-group">
                <?php if ($pct > 0): ?>
                    <span class="product-badge badge-sale">-<?= $pct; ?>%</span>
                <?php endif; ?>
                <?php if (!empty($p['is_flash_sale'])): ?>
                    <span class="product-badge badge-flash-sale">SALE</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="list-product-btn">
            <a href="<?= site_url('wishlist/toggle/' . $p['id']); ?>" class="box-icon wishlist btn-icon-action" title="Wishlist">
                <i class="fa-regular fa-heart"></i>
                <span class="tooltip">Wishlist</span>
            </a>
        </div>
        <div class="list-btn-main">
            <form action="<?= site_url('cart/add'); ?>" method="POST" class="d-inline w-100">
                <input type="hidden" name="product_id" value="<?= $p['id']; ?>">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn-main-product w-100">Add To cart</button>
            </form>
        </div> 
    </div>
    <div class="card-product-info">
        <?php
        $p_rev_cnt = (isset($p['reviews']) && is_array($p['reviews'])) ? count($p['reviews']) : (int)($p['reviews_count'] ?? 0);
        $p_rating  = $p_rev_cnt > 0 ? (float)($p['rating'] ?? 0) : 0.0;
        $p_stars   = (int)round($p_rating);
        ?>
        <div class="d-flex align-items-center gap-1 mb-1 small text-warning">
            <?php for ($s = 1; $s <= 5; $s++): ?>
                <i class="fa-<?= ($s <= $p_stars) ? 'solid' : 'regular'; ?> fa-star" style="font-size: 11px;"></i>
            <?php endfor; ?>
            <span class="text-muted ms-1" style="font-size: 11px;">(<?= $p_rev_cnt; ?>)</span>
        </div>
        <a href="<?= site_url('product/' . $p['slug']); ?>" class="title link"><?= html_escape($p['title']); ?></a>
        <div class="price">
            <?php if (!empty($p['sale_price'])): ?>
                <span class="text-danger fw-bold"><?= $curr . number_format($p['sale_price'], 2); ?></span>
                <span class="text-muted text-decoration-line-through small ms-1"><?= $curr . number_format($p['price'], 2); ?></span>
            <?php else: ?>
                <span class="fw-bold"><?= $curr . number_format($p['price'], 2); ?></span>
            <?php endif; ?>
        </div>
        <?php if (!empty($p_swatches)): ?>
            <ul class="list-color-product">
                <?php foreach ($p_swatches as $sw_idx => $sw): ?>
                    <li class="list-color-item color-swatch line hover-tooltip<?= $sw_idx === 0 ? ' active' : ''; ?>">
                        <span class="tooltip color-filter"><?= html_escape($sw['name']); ?></span>
                        <span class="swatch-value" style="background-color: <?= html_escape($sw['color_code']); ?>;"></span>
                        <img class="lazyload" data-src="<?= html_escape($sw['image']); ?>" src="<?= html_escape($sw['image']); ?>" alt="<?= html_escape($sw['name']); ?>">
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
