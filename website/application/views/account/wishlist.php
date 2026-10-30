<?php
$theme_primary = '#003D29';
$theme_accent  = '#108910';
if ($active_template === 'electronics') {
    $theme_primary = '#0f172a';
    $theme_accent  = '#2563eb';
} elseif ($active_template === 'fashion') {
    $theme_primary = '#181818';
    $theme_accent  = '#000000';
}
?>
<!-- breadcrumb -->
<div class="bg-light py-2 border-bottom">
    <div class="container container-1630" style="max-width: 1400px;">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="font-size: 13px;">
                <li class="breadcrumb-item"><a href="<?= site_url('home'); ?>" class="text-muted text-decoration-none"><i class="fa-solid fa-house me-1"></i>Home</a></li>
                <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">My Wishlist</li>
            </ol>
        </nav>
    </div>
</div>
<!-- /breadcrumb -->

<!-- wishlist content -->
<section class="py-4 py-md-5" style="background-color: #f8fafc; min-height: 75vh;">
    <div class="container container-1630" style="max-width: 1400px;">
        <div class="card border-0 shadow-sm rounded-3 p-3 p-md-4 bg-white mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pb-3 border-bottom">
                <div>
                    <h3 class="fw-bold text-dark m-0 d-flex align-items-center gap-2" style="font-size: 22px;">
                        <i class="fa-solid fa-heart text-danger"></i> My Wishlist
                        <span class="badge rounded-pill bg-light text-dark border px-2 py-1 fs-6 wishlist-count"><?= count($wishlist); ?></span>
                    </h3>
                    <p class="text-muted small m-0 mt-1">Manage and purchase products you saved for later</p>
                </div>
                <div>
                    <a href="<?= site_url('shop'); ?>" class="btn btn-outline-dark btn-sm rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                        <i class="fa-solid fa-bag-shopping"></i> Continue Shopping
                    </a>
                </div>
            </div>

            <?php if (!empty($wishlist)): ?>
                <div class="row g-3 g-md-4 row-cols-xl-4 row-cols-lg-3 row-cols-sm-2 row-cols-1 pt-4">
                    <?php foreach ($wishlist as $item):
                        $item_id   = (int)($item['id'] ?? 0);
                        $item_slug = $item['slug'] ?? '';
                        $item_name = $item['title'] ?? ($item['name'] ?? 'Product');
                        $item_raw  = $item['main_image'] ?? ($item['image'] ?? '');
                        if (!empty($item_raw)) {
                            if (strpos($item_raw, 'http') === 0) {
                                $item_img = $item_raw;
                            } elseif (strpos($item_raw, 'assets/') === 0) {
                                $item_img = base_url($item_raw);
                            } else {
                                $item_img = base_url('assets/images/' . ltrim($item_raw, '/'));
                            }
                        } else {
                            $item_img = base_url('assets/images/placeholder.jpg');
                        }
                        $reg_price = (float)($item['price'] ?? 0);
                        $sal_price = (!empty($item['sale_price']) && (float)$item['sale_price'] > 0 && (float)$item['sale_price'] < $reg_price) ? (float)$item['sale_price'] : 0;
                        $cur_sym   = $currency_symbol ?? '$';
                        $cat_name  = $item['category_name'] ?? 'General';
                        $prod_url  = site_url('product/' . $item_slug);
                    ?>
                        <div class="col" id="wishlist-item-col-<?= $item_id; ?>">
                            <div class="card h-100 border rounded-3 p-3 position-relative shadow-none product-wishlist-card transition-all" style="border-color: #e2e8f0 !important; background: #ffffff;">
                                <!-- Remove from Wishlist Button -->
                                <a href="<?= site_url('wishlist/toggle/' . $item_id); ?>"
                                   onclick="event.preventDefault(); grcToggleWishlist(<?= $item_id; ?>, this); document.getElementById('wishlist-item-col-<?= $item_id; ?>').remove();"
                                   class="position-absolute top-0 end-0 m-2 text-danger bg-light rounded-circle shadow-sm d-flex align-items-center justify-content-center text-decoration-none border"
                                   style="width: 32px; height: 32px; z-index: 2;"
                                   title="Remove from Wishlist">
                                    <i class="fa-solid fa-xmark text-muted fs-6"></i>
                                </a>

                                <!-- Product Image -->
                                <a href="<?= $prod_url; ?>" class="d-block text-center mb-3 overflow-hidden rounded-2" style="height: 180px; background: #f8fafc;">
                                    <img src="<?= $item_img; ?>" alt="<?= html_escape($item_name); ?>" class="w-100 h-100 object-fit-contain p-2" onerror="this.src='<?= base_url('assets/images/logo/logo.webp'); ?>'">
                                </a>

                                <!-- Details -->
                                <div class="d-flex flex-column flex-grow-1">
                                    <span class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 11px; letter-spacing: 0.5px;"><?= html_escape($cat_name); ?></span>
                                    <h5 class="fw-bold text-dark text-truncate mb-1" style="font-size: 14.5px;" title="<?= html_escape($item_name); ?>">
                                        <a href="<?= $prod_url; ?>" class="text-dark text-decoration-none"><?= html_escape($item_name); ?></a>
                                    </h5>
                                    <?php
                                    $w_rev_cnt = (isset($item['reviews']) && is_array($item['reviews'])) ? count($item['reviews']) : (int)($item['reviews_count'] ?? 0);
                                    $w_rating  = $w_rev_cnt > 0 ? (float)($item['rating'] ?? 0) : 0.0;
                                    $w_stars   = (int)round($w_rating);
                                    ?>
                                    <div class="d-flex align-items-center gap-1 text-warning small mb-2">
                                        <span>
                                            <?php for ($si = 1; $si <= 5; $si++): ?>
                                                <i class="fa-<?= ($si <= $w_stars) ? 'solid' : 'regular'; ?> fa-star" style="font-size: 12px;"></i>
                                            <?php endfor; ?>
                                        </span>
                                        <span class="text-muted" style="font-size: 12px;">(<?= $w_rev_cnt; ?>)</span>
                                    </div>

                                    <div class="d-flex align-items-baseline gap-2 mb-3 mt-auto">
                                        <?php if ($sal_price > 0): ?>
                                            <span class="fw-bold text-dark fs-5"><?= $cur_sym . number_format($sal_price, 2); ?></span>
                                            <span class="text-muted text-decoration-line-through small"><?= $cur_sym . number_format($reg_price, 2); ?></span>
                                            <span class="badge bg-danger-subtle text-danger small px-2 py-1 ms-auto" style="font-size: 11px;">
                                                -<?= round((($reg_price - $sal_price) / $reg_price) * 100); ?>%
                                            </span>
                                        <?php else: ?>
                                            <span class="fw-bold text-dark fs-5"><?= $cur_sym . number_format($reg_price, 2); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Add to Cart Form -->
                                    <form action="<?= site_url('cart/add'); ?>" method="POST" class="w-100">
                                        <input type="hidden" name="product_id" value="<?= $item_id; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn w-100 rounded-pill fw-semibold py-2 d-flex align-items-center justify-content-center gap-2 shadow-sm text-white" style="background-color: <?= $theme_primary; ?>; border: none; font-size: 13.5px;">
                                            <i class="fa-solid fa-basket-shopping"></i> Add To Cart
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5 my-4">
                    <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle bg-light shadow-sm" style="width: 80px; height: 80px;">
                        <i class="fa-regular fa-heart fs-1 text-muted" style="opacity: 0.6;"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-2">Your Wishlist is Empty</h4>
                    <p class="text-muted mx-auto mb-4" style="max-width: 460px; font-size: 14px;">
                        Explore our catalog, discover fresh groceries and deals, and click the heart icon on any product to save it here for later.
                    </p>
                    <a href="<?= site_url('shop'); ?>" class="btn rounded-pill px-4 py-2 fw-semibold text-white shadow-sm" style="background-color: <?= $theme_primary; ?>; font-size: 14px;">
                        <i class="fa-solid fa-store me-2"></i> Explore Store Products
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<!-- /wishlist content -->
