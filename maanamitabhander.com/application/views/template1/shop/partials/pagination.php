<?php if ($total_pages > 1): ?>
    <div class="tf-pagination-wrap mt-5 text-center">
        <ul class="tf-pagination-list d-flex justify-content-center align-items-center gap-2">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li>
                    <a href="javascript:void(0);" class="pagination-link shop-page-btn <?= ($current_page == $i) ? 'active' : ''; ?>" data-page="<?= $i; ?>">
                        <?= $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </div>
<?php endif; ?>