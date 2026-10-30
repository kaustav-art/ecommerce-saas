<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1">
        <i class="ri ri-layout-masonry-line text-primary me-2"></i>Storefront Template Manager
      </h4>
      <p class="text-muted mb-0">Switch your live storefront theme at any time while keeping your products, categories, and orders intact.</p>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= site_url('subscription'); ?>" class="btn btn-outline-primary">
        <i class="ri ri-vip-crown-2-line me-1"></i> Subscription Plan: <?= html_escape($current_plan['name'] ?? 'Standard'); ?>
      </a>
      <?php
        $storefront_url = ((int)($tenant['id'] ?? 1) === 1)
          ? base_url('../website/home')
          : base_url('../website/s/' . ($tenant['slug'] ?? 'fashion-store'));
      ?>
      <a href="<?= $storefront_url; ?>" target="_blank" class="btn btn-primary">
        <i class="ri ri-external-link-line me-1"></i> Open My Live Storefront
      </a>
    </div>
  </div>

  <div class="row g-4">
    <?php foreach ($templates as $tpl): ?>
      <?php
        $slug        = strtolower($tpl['template_key'] ?? $tpl['slug'] ?? 'fashion');
        $is_active   = (strtolower($tenant['template'] ?? 'fashion') === $slug);
        $is_unlocked = in_array($slug, $allowed_list, true);
        $preview_img = !empty($tpl['preview_image'])
          ? base_url('../' . ltrim($tpl['preview_image'], '/'))
          : base_url('../website/assets/images/templates/ecommerce_template1.png');
        $demo_url    = base_url('../website/home?preview_tpl=' . urlencode($slug));
      ?>
      <div class="col-md-4">
        <div class="card h-100 <?= $is_active ? 'border-primary border-2 shadow' : 'border shadow-sm'; ?>">
          <div class="position-relative overflow-hidden" style="height: 210px; background: #f8f9fa;">
            <img src="<?= $preview_img; ?>" alt="<?= html_escape($tpl['name']); ?>" class="w-100 h-100" style="object-fit: cover;" onerror="this.src='<?= base_url('../website/assets/images/templates/ecommerce_template1.png'); ?>'">
            <div class="position-absolute top-0 end-0 p-3 d-flex gap-2">
              <?php if ($is_active): ?>
                <span class="badge bg-primary px-3 py-2 shadow-sm">
                  <i class="ri ri-checkbox-circle-fill me-1"></i>Active Theme
                </span>
              <?php elseif ($is_unlocked): ?>
                <span class="badge bg-success px-3 py-2 shadow-sm">
                  <i class="ri ri-lock-unlock-line me-1"></i>Unlocked
                </span>
              <?php else: ?>
                <span class="badge bg-dark px-3 py-2 shadow-sm">
                  <i class="ri ri-lock-2-line me-1"></i>Upgrade Required
                </span>
              <?php endif; ?>
            </div>
          </div>

          <div class="card-body d-flex flex-column justify-content-between">
            <div>
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="fw-bold mb-0"><?= html_escape($tpl['name']); ?></h5>
                <span class="badge bg-label-info text-uppercase"><?= html_escape($slug); ?></span>
              </div>
              <p class="text-muted small mb-3"><?= html_escape($tpl['description'] ?? ''); ?></p>
            </div>

            <div class="d-flex gap-2 mt-2">
              <a href="<?= $demo_url; ?>" target="_blank" class="btn btn-outline-secondary flex-grow-1">
                <i class="ri ri-eye-line me-1"></i> Preview
              </a>
              <?php if ($is_active): ?>
                <button type="button" class="btn btn-primary flex-grow-1" disabled>
                  <i class="ri ri-check-line me-1"></i> Currently Active
                </button>
              <?php elseif ($is_unlocked): ?>
                <form method="post" action="<?= site_url('template_manager/activate'); ?>" class="flex-grow-1">
                  <input type="hidden" name="template_slug" value="<?= html_escape($slug); ?>">
                  <button type="submit" class="btn btn-primary w-100">
                    <i class="ri ri-magic-line me-1"></i> Activate Theme
                  </button>
                </form>
              <?php else: ?>
                <a href="<?= site_url('subscription'); ?>" class="btn btn-warning flex-grow-1">
                  <i class="ri ri-vip-crown-line me-1"></i> Upgrade to Unlock
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
