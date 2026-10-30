<?php if (!empty($products)): ?>
    <?php foreach ($products as $p): ?>
        <?php $this->load->view('home/_product_card', ['p' => $p, 'currency_symbol' => $currency_symbol ?? '$']); ?>
    <?php endforeach; ?>
<?php elseif (empty($is_append)): ?>
    <?php
        $has_filters = !empty($filters['search']) || !empty($filters['min_price']) || !empty($filters['max_price']) || !empty($filters['on_sale']) || !empty($filters['attr_value_ids']) || !empty($selected_brands);
    ?>
    <div class="empty-products-state text-center" style="grid-column: 1 / -1; width: 100%; padding: 60px 24px; background: #fafbfc; border: 1px dashed #e5e7eb; border-radius: 12px; margin: 10px 0 30px;">
        <div class="empty-icon-box mb-3 d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px; border-radius: 50%; background: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #f1f3f5;">
            <i class="fa-solid fa-box-open" style="font-size: 28px; color: #9ca3af;"></i>
        </div>
        <h5 class="fw-bold mb-2" style="font-size: 19px; color: #1f2937; letter-spacing: -0.2px;">No products found</h5>
        <p class="text-secondary mb-0" style="font-size: 14px; max-width: 440px; margin: 0 auto; line-height: 1.55;">
            <?php if (!empty($selected_category) && !$has_filters): ?>
                There are currently no products available in <?= html_escape($selected_category['name']); ?>.
            <?php elseif ($has_filters): ?>
                We couldn't find any products matching your selected filters.
            <?php else: ?>
                There are currently no products available in this section.
            <?php endif; ?>
        </p>
    </div>
<?php endif; ?>