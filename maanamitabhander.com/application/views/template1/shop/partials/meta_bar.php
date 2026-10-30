<?php
$base_filter_url = $selected_category ? site_url('shop/' . $selected_category['slug']) : site_url('shop');
$has_active_filters = !empty($selected_brands) || !empty($filters['attr_value_ids']) || !empty($filters['min_price']) || !empty($filters['max_price']) || !empty($filters['on_sale']) || !empty($filters['search']);

$brand_map = [];
foreach ($brands as $b) {
    $brand_map[$b['slug']] = $b['name'];
}

$attr_val_map = [];
foreach ($filter_sizes as $s) {
    $attr_val_map[$s['id']] = ['type' => 'size', 'name' => 'Size: ' . $s['value']];
}
foreach ($filter_colors as $c) {
    $attr_val_map[$c['id']] = ['type' => 'color', 'name' => $c['value'], 'color_code' => $c['color_code'] ?? ''];
}
foreach ($other_variants as $ov) {
    foreach ($ov['values'] as $val) {
        $attr_val_map[$val['id']] = ['type' => 'other', 'name' => $ov['name'] . ': ' . $val['value']];
    }
}
?>
<div class="meta-filter-shop d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center flex-wrap gap-2">
        <div class="count-text text-secondary small">
            Showing <span class="count text-dark fw-bold" id="shopVisibleCount"><?= min($total_products, count($products)); ?></span> of <span class="count text-dark fw-bold" id="shopTotalCount"><?= $total_products; ?></span> products
            <?php if ($selected_category): ?>
                in <strong><?= html_escape($selected_category['name']); ?></strong>
            <?php endif; ?>
        </div>

        <!-- Applied filter pills -->
        <?php if ($has_active_filters): ?>
            <div id="applied-filters" class="d-flex align-items-center flex-wrap gap-2 ms-lg-2">
                <?php if (!empty($selected_brands)): ?>
                    <?php foreach ($selected_brands as $sb): ?>
                        <span class="filter-tag text-dark filter-tag-remove cursor-pointer" data-filter-type="brand" data-filter-value="<?= html_escape($sb); ?>" title="Remove brand filter">
                            <span>Brand: <?= html_escape($brand_map[$sb] ?? $sb); ?></span>
                            <span class="remove-tag"><i class="fa-solid fa-xmark"></i></span>
                        </span>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!empty($filters['attr_value_ids'])): ?>
                    <?php foreach ($filters['attr_value_ids'] as $avid): ?>
                        <?php $av_meta = $attr_val_map[$avid] ?? null; ?>
                        <span class="filter-tag text-dark filter-tag-remove cursor-pointer <?= (!empty($av_meta['type']) && $av_meta['type'] === 'color') ? 'color-tag' : ''; ?>" data-filter-type="attr" data-filter-value="<?= $avid; ?>" title="Remove filter">
                            <?php if (!empty($av_meta['color_code'])): ?>
                                <span class="color border" style="background-color: <?= html_escape($av_meta['color_code']); ?>;"></span>
                            <?php endif; ?>
                            <span><?= html_escape($av_meta['name'] ?? ('Option #' . $avid)); ?></span>
                            <span class="remove-tag"><i class="fa-solid fa-xmark"></i></span>
                        </span>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!empty($filters['min_price']) || !empty($filters['max_price'])): ?>
                    <span class="filter-tag text-dark filter-tag-remove cursor-pointer" data-filter-type="price" title="Remove price filter">
                        <span>Price: $<?= html_escape($filters['min_price'] ?: $min_catalog_price); ?> - $<?= html_escape($filters['max_price'] ?: $max_catalog_price); ?></span>
                        <span class="remove-tag"><i class="fa-solid fa-xmark"></i></span>
                    </span>
                <?php endif; ?>

                <?php if (!empty($filters['on_sale'])): ?>
                    <span class="filter-tag text-dark filter-tag-remove cursor-pointer" data-filter-type="on_sale" title="Remove sale filter">
                        <span>On Sale</span>
                        <span class="remove-tag"><i class="fa-solid fa-xmark"></i></span>
                    </span>
                <?php endif; ?>

                <?php if (!empty($filters['search'])): ?>
                    <span class="filter-tag text-dark filter-tag-remove cursor-pointer" data-filter-type="search" title="Remove search filter">
                        <span>Search: "<?= html_escape($filters['search']); ?>"</span>
                        <span class="remove-tag"><i class="fa-solid fa-xmark"></i></span>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($has_active_filters): ?>
        <div>
            <button type="button" class="btn-clear-all-ajax text-btn-uppercase text-decoration-none btn-xs border-0 bg-transparent text-danger fw-bold cursor-pointer d-flex align-items-center gap-1" title="Clear and reset all filters">
                <i class="fa-solid fa-rotate-left"></i> REMOVE ALL <i class="fa-solid fa-xmark ms-1"></i>
            </button>
        </div>
    <?php endif; ?>
</div>