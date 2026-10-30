<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><span class="text-muted fw-light">Reports /</span> Best Selling Products</h4>
    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
      <i class="fa-solid fa-print me-1"></i> Print Report
    </button>
  </div>

  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">Top Performing Products by Volume & Revenue</h5>
    </div>
    <div class="table-responsive text-nowrap">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Product</th>
            <th>SKU</th>
            <th>Category</th>
            <th>Price</th>
            <th>Current Stock</th>
            <th>Units Sold</th>
            <th>Total Revenue</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($products_data)): ?>
            <?php foreach ($products_data as $p): ?>
              <tr>
                <td><strong><?= html_escape($p['title']); ?></strong></td>
                <td><code><?= html_escape($p['sku']); ?></code></td>
                <td><?= html_escape($p['category_name'] ?? 'Uncategorized'); ?></td>
                <td><?= $currency_symbol . number_format($p['price'], 2); ?></td>
                <td>
                  <span class="badge <?= ($p['stock_quantity'] > 0) ? 'bg-label-success' : 'bg-label-danger'; ?>">
                    <?= $p['stock_quantity']; ?> left
                  </span>
                </td>
                <td><strong class="text-primary"><?= (int)$p['units_sold']; ?> sold</strong></td>
                <td><strong class="text-success"><?= $currency_symbol . number_format($p['total_revenue'], 2); ?></strong></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center text-muted py-4">No product sales records yet.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
