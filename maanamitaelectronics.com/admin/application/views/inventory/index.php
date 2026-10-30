<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><span class="text-muted fw-light">Inventory /</span> Current Stock</h4>
    <div>
      <a href="<?= site_url('inventory/adjust'); ?>" class="btn btn-primary btn-sm me-2">
        <i class="fa-solid fa-plus me-1"></i> Stock Adjustment
      </a>
      <a href="<?= site_url('inventory/history'); ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-clock-rotate-left me-1"></i> Stock History
      </a>
    </div>
  </div>

  <!-- Filter Card -->
  <div class="card mb-4">
    <div class="card-body">
      <form action="<?= site_url('inventory'); ?>" method="GET" class="row g-3">
        <div class="col-md-5">
          <label class="form-label">Search Product / SKU</label>
          <input type="text" class="form-control" name="q" value="<?= html_escape($filters['search']); ?>" placeholder="Search product title or SKU...">
        </div>
        <div class="col-md-3">
          <label class="form-label">Stock Status</label>
          <select class="form-select" name="status">
            <option value="">All Statuses</option>
            <option value="in_stock" <?= ($filters['status'] === 'in_stock') ? 'selected' : ''; ?>>In Stock</option>
            <option value="out_of_stock" <?= ($filters['status'] === 'out_of_stock') ? 'selected' : ''; ?>>Out of Stock</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">&nbsp;</label>
          <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="low_stock" value="1" id="low_stock_chk" <?= !empty($filters['low_stock']) ? 'checked' : ''; ?>>
            <label class="form-check-label text-danger fw-bold small" for="low_stock_chk">Low Stock Only</label>
          </div>
        </div>
        <div class="col-md-2 d-flex align-items-end gap-2">
          <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
          <a href="<?= site_url('inventory'); ?>" class="btn btn-outline-secondary">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Table -->
  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">Warehouse Inventory (<?= $total_items; ?> Products)</h5>
    </div>
    <div class="table-responsive text-nowrap">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Product</th>
            <th>SKU</th>
            <th>Category</th>
            <th>Stock Level</th>
            <th>Threshold</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($items)): ?>
            <?php foreach ($items as $it): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <img src="<?= admin_storefront_asset_url('images/' . ($it['main_image'] ?: 'products/womens/women-1.jpg')); ?>" class="rounded me-2" style="width: 36px; height: 36px; object-fit: cover;" onerror="this.src='<?= admin_storefront_asset_url('images/products/womens/women-1.jpg'); ?>'">
                    <strong><?= html_escape($it['product_title']); ?></strong>
                  </div>
                </td>
                <td><code><?= html_escape($it['product_sku']); ?></code></td>
                <td><?= html_escape($it['category_name'] ?? 'General'); ?></td>
                <td>
                  <span class="fs-6 fw-bold <?= ($it['product_stock'] <= $it['low_stock_threshold']) ? 'text-danger' : 'text-dark'; ?>">
                    <?= $it['product_stock']; ?> units
                  </span>
                </td>
                <td><span class="badge bg-label-secondary">&le; <?= $it['low_stock_threshold']; ?></span></td>
                <td>
                  <?php if ($it['product_stock'] <= 0): ?>
                    <span class="badge bg-label-danger">Out of Stock</span>
                  <?php elseif ($it['product_stock'] <= $it['low_stock_threshold']): ?>
                    <span class="badge bg-label-warning">Low Stock</span>
                  <?php else: ?>
                    <span class="badge bg-label-success">In Stock</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="<?= site_url('inventory/adjust?product_id=' . $it['product_id']); ?>" class="btn btn-xs btn-outline-primary me-1">
                    <i class="fa-solid fa-arrows-rotate me-1"></i> Adjust
                  </a>
                  <a href="<?= site_url('products/edit/' . $it['product_id']); ?>" class="btn btn-xs btn-outline-secondary">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center text-muted py-4">No inventory records found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
