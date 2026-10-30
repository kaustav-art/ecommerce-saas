<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><span class="text-muted fw-light">Reports /</span> Inventory Valuation Report</h4>
    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
      <i class="fa-solid fa-print me-1"></i> Print Report
    </button>
  </div>

  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">Total Asset Valuation by Stock Holding</h5>
    </div>
    <div class="table-responsive text-nowrap">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Product</th>
            <th>SKU</th>
            <th>Category</th>
            <th>Unit Cost/Price</th>
            <th>Stock On Hand</th>
            <th>Asset Value</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($inventory_data)): ?>
            <?php 
              $tot_units = 0;
              $tot_val   = 0;
              foreach ($inventory_data as $inv): 
                $tot_units += $inv['stock_quantity'];
                $tot_val   += $inv['inventory_value'];
            ?>
              <tr>
                <td><strong><?= html_escape($inv['title']); ?></strong></td>
                <td><code><?= html_escape($inv['sku']); ?></code></td>
                <td><?= html_escape($inv['category_name'] ?? 'General'); ?></td>
                <td><?= $currency_symbol . number_format($inv['price'], 2); ?></td>
                <td><?= $inv['stock_quantity']; ?> units</td>
                <td><strong class="text-success"><?= $currency_symbol . number_format($inv['inventory_value'], 2); ?></strong></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center text-muted py-4">No inventory found in database.</td>
            </tr>
          <?php endif; ?>
        </tbody>
        <?php if (!empty($inventory_data)): ?>
        <tfoot class="table-light fw-bold fs-6">
          <tr>
            <td>TOTAL INVENTORY VALUE:</td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td><?= $tot_units; ?> Total Units</td>
            <td class="text-success"><?= $currency_symbol . number_format($tot_val, 2); ?></td>
          </tr>
        </tfoot>
        <?php endif; ?>
      </table>
    </div>
  </div>
</div>
