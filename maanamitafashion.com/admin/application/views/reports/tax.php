<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><span class="text-muted fw-light">Reports /</span> Tax Collection Report</h4>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <form action="<?= site_url('reports/tax'); ?>" method="GET" class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Start Date</label>
          <input type="date" class="form-control" name="start_date" value="<?= html_escape($start_date); ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">End Date</label>
          <input type="date" class="form-control" name="end_date" value="<?= html_escape($end_date); ?>">
        </div>
        <div class="col-md-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
          <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print me-1"></i> Print</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">Sales Tax Summary by Period</h5>
    </div>
    <div class="table-responsive text-nowrap">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Period (Month)</th>
            <th>Paid Orders</th>
            <th>Taxable Sales Base</th>
            <th>Sales Tax Collected</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($tax_data)): ?>
            <?php 
              $tot_tx_base = 0;
              $tot_tx_col  = 0;
              foreach ($tax_data as $row): 
                $tot_tx_base += $row['taxable_amount'];
                $tot_tx_col  += $row['tax_collected'];
            ?>
              <tr>
                <td><strong><?= html_escape($row['period']); ?></strong></td>
                <td><span class="badge bg-label-primary"><?= $row['orders_count']; ?> Orders</span></td>
                <td><?= $currency_symbol . number_format($row['taxable_amount'], 2); ?></td>
                <td><strong class="text-success"><?= $currency_symbol . number_format($row['tax_collected'], 2); ?></strong></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="text-center text-muted py-4">No tax collections recorded for this period.</td>
            </tr>
          <?php endif; ?>
        </tbody>
        <?php if (!empty($tax_data)): ?>
        <tfoot class="table-light fw-bold">
          <tr>
            <td>TOTALS:</td>
            <td>-</td>
            <td><?= $currency_symbol . number_format($tot_tx_base, 2); ?></td>
            <td class="text-success"><?= $currency_symbol . number_format($tot_tx_col, 2); ?></td>
          </tr>
        </tfoot>
        <?php endif; ?>
      </table>
    </div>
  </div>
</div>
