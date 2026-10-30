        <!-- Breadcrumb -->
        <div class="bg-light py-3 border-bottom">
            <div class="container">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="<?= site_url('home'); ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url('shop'); ?>">Shop</a></li>
                        <li class="breadcrumb-item active">Product Comparison</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Compare Section -->
        <section class="py-4 py-md-5">
            <div class="container">
                <div class="d-flex justify-content-between align-items-sm-center align-items-start flex-column flex-sm-row gap-3 mb-4">
                    <div>
                        <h3 class="fw-bold mb-1 fs-4 fs-md-3">Product Comparison</h3>
                        <p class="text-muted small mb-0">Compare specifications, prices, and features side by side.</p>
                    </div>
                    <?php if (!empty($products)): ?>
                        <div class="d-flex gap-2">
                            <a href="<?= site_url('shop'); ?>" class="btn btn-outline-dark btn-sm">
                                <i class="fa-solid fa-plus me-1"></i> Add Another
                            </a>
                            <a href="<?= site_url('compare/clear'); ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Clear entire comparison list?');">
                                <i class="fa-solid fa-trash me-1"></i> Clear All
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($products)): ?>
                    <div class="card border shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0 text-center">
                                <thead>
                                    <tr class="table-light">
                                        <th style="width: 180px;" class="text-start ps-3">Features & Specs</th>
                                        <?php foreach ($products as $p): ?>
                                            <th style="width: 250px; min-width: 220px;" class="position-relative p-3">
                                                <a href="<?= site_url('compare/remove/' . $p['id']); ?>" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2 rounded-circle p-1" title="Remove" style="width: 28px; height: 28px; line-height: 1;">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </a>
                                                <div class="bg-white p-2 rounded mb-2" style="height: 180px;">
                                                    <a href="<?= site_url('product/' . $p['slug']); ?>">
                                                        <img src="<?= base_url('assets/images/' . $p['main_image']); ?>" class="w-100 h-100 object-fit-contain" alt="<?= html_escape($p['title']); ?>" onerror="this.src='<?= base_url('assets/images/products/womens/women-1.jpg'); ?>'">
                                                    </a>
                                                </div>
                                                <h6 class="fw-bold mb-1">
                                                    <a href="<?= site_url('product/' . $p['slug']); ?>" class="text-dark text-decoration-none">
                                                        <?= html_escape($p['title']); ?>
                                                    </a>
                                                </h6>
                                                <div class="small text-muted"><?= html_escape($p['category_name'] ?? ''); ?></div>
                                            </th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Price Row -->
                                    <tr>
                                        <th class="text-start ps-3 bg-light text-muted small text-uppercase">Price</th>
                                        <?php foreach ($products as $p): ?>
                                            <td>
                                                <?php if (!empty($p['sale_price'])): ?>
                                                    <div class="fs-5 fw-bold text-danger"><?= $currency_symbol . number_format($p['sale_price'], 2); ?></div>
                                                    <div class="small text-muted text-decoration-line-through"><?= $currency_symbol . number_format($p['price'], 2); ?></div>
                                                <?php else: ?>
                                                    <div class="fs-5 fw-bold text-dark"><?= $currency_symbol . number_format($p['price'], 2); ?></div>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>

                                    <!-- Rating Row -->
                                    <tr>
                                        <th class="text-start ps-3 bg-light text-muted small text-uppercase">Rating</th>
                                        <?php foreach ($products as $p):
                                            $cp_rev_cnt = (isset($p['reviews']) && is_array($p['reviews'])) ? count($p['reviews']) : (int)($p['reviews_count'] ?? 0);
                                            $cp_rating  = $cp_rev_cnt > 0 ? (float)($p['rating'] ?? 0) : 0.0;
                                            $cp_stars   = (int)round($cp_rating);
                                        ?>
                                            <td>
                                                <div class="text-warning small d-inline-flex align-items-center gap-1">
                                                    <span>
                                                        <?php for ($s = 1; $s <= 5; $s++): ?>
                                                            <i class="fa-<?= ($s <= $cp_stars) ? 'solid' : 'regular'; ?> fa-star"></i>
                                                        <?php endfor; ?>
                                                    </span>
                                                    <span class="text-muted">(<?= $cp_rev_cnt; ?>)</span>
                                                </div>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>

                                    <!-- Brand Row -->
                                    <tr>
                                        <th class="text-start ps-3 bg-light text-muted small text-uppercase">Brand</th>
                                        <?php foreach ($products as $p): ?>
                                            <td><?= html_escape($p['brand_name'] ?? 'Generic'); ?></td>
                                        <?php endforeach; ?>
                                    </tr>

                                    <!-- SKU Row -->
                                    <tr>
                                        <th class="text-start ps-3 bg-light text-muted small text-uppercase">SKU</th>
                                        <?php foreach ($products as $p): ?>
                                            <td><code><?= html_escape($p['sku']); ?></code></td>
                                        <?php endforeach; ?>
                                    </tr>

                                    <!-- Availability Row -->
                                    <tr>
                                        <th class="text-start ps-3 bg-light text-muted small text-uppercase">Availability</th>
                                        <?php foreach ($products as $p): ?>
                                            <td>
                                                <?php if ($p['stock_quantity'] > 0): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1">In Stock (<?= $p['stock_quantity']; ?>)</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger px-2 py-1">Out of Stock</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>

                                    <!-- Specifications Rows -->
                                    <?php if (!empty($all_specs)): ?>
                                        <tr class="table-secondary">
                                            <th colspan="<?= count($products) + 1; ?>" class="text-start ps-3 fw-bold text-uppercase small">Technical Specifications</th>
                                        </tr>
                                        <?php foreach ($all_specs as $specName): ?>
                                            <tr>
                                                <th class="text-start ps-3 bg-light text-muted small"><?= html_escape($specName); ?></th>
                                                <?php foreach ($products as $p): ?>
                                                    <?php
                                                        $val = '—';
                                                        if (!empty($p['specifications'])) {
                                                            foreach ($p['specifications'] as $s) {
                                                                if ($s['spec_name'] === $specName) {
                                                                    $val = $s['spec_value'];
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                    ?>
                                                    <td><?= html_escape($val); ?></td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                    <!-- Buy Now / Add to Cart Row -->
                                    <tr>
                                        <th class="text-start ps-3 bg-light text-muted small text-uppercase">Action</th>
                                        <?php foreach ($products as $p): ?>
                                            <td>
                                                <form action="<?= site_url('cart/add'); ?>" method="POST">
                                                    <input type="hidden" name="product_id" value="<?= $p['id']; ?>">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit" class="btn btn-primary btn-sm w-100" <?= ($p['stock_quantity'] <= 0) ? 'disabled' : ''; ?>>
                                                        <i class="fa-solid fa-cart-shopping me-1"></i> Add to Cart
                                                    </button>
                                                </form>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card border p-5 text-center shadow-sm">
                        <div class="fs-1 text-muted mb-3"><i class="fa-solid fa-code-compare"></i></div>
                        <h4 class="fw-bold">No products to compare</h4>
                        <p class="text-muted mb-4">You haven't added any products to compare yet. Browse the catalog and click the compare icon on items you'd like to evaluate side by side.</p>
                        <a href="<?= site_url('shop'); ?>" class="btn btn-primary px-4 align-self-center">Browse Products</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
