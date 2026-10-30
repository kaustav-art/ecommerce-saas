<?php
$pct = 0;
if (!empty($p['sale_price']) && !empty($p['price']) && $p['sale_price'] < $p['price']) {
    $pct = round((($p['price'] - $p['sale_price']) / $p['price']) * 100);
}
$curr = $currency_symbol ?? '$';
$hover_img = !empty($p['gallery_images_decoded'][0]) ? $p['gallery_images_decoded'][0] : $p['main_image'];
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

        <!-- Discount / On Sale Tag Only (New and Best Seller tags removed) -->
        <?php if ($pct > 0): ?>
            <div class="product-badge-group">
                <span class="product-badge badge-sale">-<?= $pct; ?>%</span>
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
    </div>
</div>
