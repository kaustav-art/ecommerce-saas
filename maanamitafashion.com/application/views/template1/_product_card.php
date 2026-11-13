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
                 onerror="this.src='<?= base_url('assets/images/products/product-image.png'); ?>'">
            <img class="lazyload img-hover" 
                 data-src="<?= base_url('assets/images/' . $hover_img); ?>" 
                 src="<?= base_url('assets/images/' . $hover_img); ?>" 
                 alt="<?= html_escape($p['title']); ?>" 
                 onerror="this.src='<?= base_url('assets/images/products/product-image.png'); ?>'">
        </a>

        <!-- Discount / On Sale Tag Only (New and Best Seller tags removed) -->
        <?php if ($pct > 0): ?>
            <div class="product-badge-group">
                <span class="product-badge badge-sale">-<?= $pct; ?>%</span>
            </div>
        <?php endif; ?>

        <?php
        $is_p_wish  = !empty($user_wishlist_ids) && in_array((int)$p['id'], $user_wishlist_ids, true);
        $is_wl_page = !empty($is_wishlist_page) || !empty($show_close_btn);
        ?>
        <div class="list-product-btn">
            <?php if ($is_wl_page): ?>
                <a href="javascript:void(0)" 
                   class="box-icon wishlist-remove-btn" 
                   data-product-id="<?= (int)$p['id']; ?>"
                   onclick="event.preventDefault(); event.stopPropagation(); removeWishlistPageItem(<?= (int)$p['id']; ?>, event);"
                   title="Remove from Wishlist">
                    <i class="fa-solid fa-xmark fs-6"></i>
                    <span class="tooltip">Remove</span>
                </a>
            <?php else: ?>
                <a href="<?= site_url('wishlist/toggle/' . $p['id']); ?>" 
                   class="box-icon wishlist btn-icon-action <?= $is_p_wish ? 'active in-wishlist' : ''; ?>" 
                   data-product-id="<?= (int)$p['id']; ?>"
                   onclick="event.preventDefault(); toggleProductWishlist(<?= (int)$p['id']; ?>, this);"
                   title="<?= $is_p_wish ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>">
                    <i class="fa-<?= $is_p_wish ? 'solid' : 'regular'; ?> fa-heart <?= $is_p_wish ? 'text-danger' : ''; ?>"></i>
                    <span class="tooltip"><?= $is_p_wish ? 'Remove' : 'Wishlist'; ?></span>
                </a>
            <?php endif; ?>
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
                <span class="text-danger fw-bold"><?= $curr . number_format(round($p['sale_price'])); ?></span>
                <span class="text-muted text-decoration-line-through small ms-1"><?= $curr . number_format(round($p['price'])); ?></span>
            <?php else: ?>
                <span class="fw-bold"><?= $curr . number_format(round($p['price'])); ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>
