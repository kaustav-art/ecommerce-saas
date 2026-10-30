<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><span class="text-muted fw-light">Reports /</span> Customer Spending Report</h4>
    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
      <i class="fa-solid fa-print me-1"></i> Print Report
    </button>
  </div>

  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">Highest Spending Customers (LTV)</h5>
    </div>
    <div class="table-responsive text-nowrap">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Customer</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Completed Orders</th>
            <th>Total Lifetime Value</th>
            <th>Last Order Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($customers_data)): ?>
            <?php foreach ($customers_data as $c): ?>
              <tr>
                <td><strong><?= html_escape($c['first_name'] . ' ' . $c['last_name']); ?></strong></td>
                <td><?= html_escape($c['email']); ?></td>
                <td><?= html_escape($c['phone'] ?? 'N/A'); ?></td>
                <td><span class="badge bg-label-primary"><?= (int)$c['total_orders']; ?> Orders</span></td>
                <td><strong class="text-success fs-6"><?= $currency_symbol . number_format($c['total_spent'], 2); ?></strong></td>
                <td>
                  <?php if ($c['last_order_date']): ?>
                    <small class="text-muted"><?= date('M d, Y', strtotime($c['last_order_date'])); ?></small>
                  <?php else: ?>
                    <span class="text-muted small">None</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center text-muted py-4">No customer transactions recorded yet.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
