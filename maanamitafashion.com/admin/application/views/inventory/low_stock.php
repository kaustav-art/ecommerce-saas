<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold m-0 text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i>Low Stock Inventory Alerts</h4>
      <small class="text-muted">Products requiring restocking based on minimum threshold.</small>
    </div>
    <a href="<?= site_url('inventory'); ?>" class="btn btn-outline-secondary btn-sm">
      <i class="fa-solid fa-arrow-left me-1"></i> Back to All Inventory
    </a>
  </div>

  <div class="card">
    <div class="table-responsive text-nowrap">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Product</th>
            <th>SKU</th>
            <th>Current Units</th>
            <th>Threshold</th>
            <th>Urgency</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($items)): ?>
            <?php foreach ($items as $it): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <img src="<?= admin_storefront_asset_url('images/' . ($it['main_image'] ?: 'products/womens/women-1.jpg')); ?>" class="rounded me-2" style="width: 38px; height: 38px; object-fit: cover;" onerror="this.src='<?= admin_storefront_asset_url('images/products/womens/women-1.jpg'); ?>'">
                    <strong><?= html_escape($it['product_title']); ?></strong>
                  </div>
                </td>
                <td><code><?= html_escape($it['product_sku']); ?></code></td>
                <td>
                  <strong class="text-danger fs-6"><?= $it['product_stock']; ?> units</strong>
                </td>
                <td>&le; <?= $it['low_stock_threshold']; ?></td>
                <td>
                  <?php if ($it['product_stock'] <= 0): ?>
                    <span class="badge bg-danger">Critical (0 Stock)</span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark">Replenish Soon</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="<?= site_url('inventory/adjust?product_id=' . $it['product_id']); ?>" class="btn btn-sm btn-primary">
                    <i class="fa-solid fa-plus me-1"></i> Restock
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center text-muted py-5">
                <i class="fa-regular fa-circle-check text-success fs-1 d-block mb-2"></i>
                All products have healthy stock levels!
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
