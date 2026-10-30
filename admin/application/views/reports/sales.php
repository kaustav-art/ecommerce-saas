<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><span class="text-muted fw-light">Reports /</span> Sales Report</h4>
  </div>

  <!-- Filter Card -->
  <div class="card mb-4">
    <div class="card-body">
      <form action="<?= site_url('reports/sales'); ?>" method="GET" class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Start Date</label>
          <input type="date" class="form-control" name="start_date" value="<?= html_escape($start_date); ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">End Date</label>
          <input type="date" class="form-control" name="end_date" value="<?= html_escape($end_date); ?>">
        </div>
        <div class="col-md-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary flex-grow-1">Generate Report</button>
          <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Print
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Table -->
  <div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">Sales Breakdown by Date</h5>
      <span class="text-muted small">Period: <?= date('M d, Y', strtotime($start_date)); ?> - <?= date('M d, Y', strtotime($end_date)); ?></span>
    </div>
    <div class="table-responsive text-nowrap">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Date</th>
            <th>Orders</th>
            <th>Gross Sales</th>
            <th>Shipping Collected</th>
            <th>Tax Collected</th>
            <th>Discounts</th>
            <th>Net Sales</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($sales_data)): ?>
            <?php 
              $tot_orders = 0;
              $tot_gross  = 0;
              $tot_ship   = 0;
              $tot_tax    = 0;
              $tot_disc   = 0;
              foreach ($sales_data as $row): 
                $tot_orders += $row['total_orders'];
                $tot_gross  += $row['gross_sales'];
                $tot_ship   += $row['total_shipping'];
                $tot_tax    += $row['total_tax'];
                $tot_disc   += $row['total_discounts'];
                $net = $row['gross_sales'] - $row['total_tax'] - $row['total_shipping'];
            ?>
              <tr>
                <td><strong><?= date('M d, Y', strtotime($row['sale_date'])); ?></strong></td>
                <td><span class="badge bg-label-primary"><?= $row['total_orders']; ?> Orders</span></td>
                <td><strong><?= $currency_symbol . number_format($row['gross_sales'], 2); ?></strong></td>
                <td><?= $currency_symbol . number_format($row['total_shipping'], 2); ?></td>
                <td><?= $currency_symbol . number_format($row['total_tax'], 2); ?></td>
                <td class="text-danger">-<?= $currency_symbol . number_format($row['total_discounts'], 2); ?></td>
                <td><strong class="text-success"><?= $currency_symbol . number_format($net, 2); ?></strong></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center text-muted py-4">No completed sales recorded in this date range.</td>
            </tr>
          <?php endif; ?>
        </tbody>
        <?php if (!empty($sales_data)): ?>
        <tfoot class="table-light fw-bold">
          <tr>
            <td>TOTALS:</td>
            <td><?= $tot_orders; ?> Orders</td>
            <td><?= $currency_symbol . number_format($tot_gross, 2); ?></td>
            <td><?= $currency_symbol . number_format($tot_ship, 2); ?></td>
            <td><?= $currency_symbol . number_format($tot_tax, 2); ?></td>
            <td class="text-danger">-<?= $currency_symbol . number_format($tot_disc, 2); ?></td>
            <td class="text-success"><?= $currency_symbol . number_format($tot_gross - $tot_tax - $tot_ship, 2); ?></td>
          </tr>
        </tfoot>
        <?php endif; ?>
      </table>
    </div>
  </div>
</div>
