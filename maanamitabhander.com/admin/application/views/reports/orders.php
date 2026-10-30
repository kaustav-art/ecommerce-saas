<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><span class="text-muted fw-light">Reports /</span> Order Status Report</h4>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <form action="<?= site_url('reports/orders'); ?>" method="GET" class="row g-3 align-items-end">
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
      <h5 class="card-title mb-0">Order Distribution by Fulfillment Status</h5>
    </div>
    <div class="table-responsive text-nowrap">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Order Status</th>
            <th>Number of Orders</th>
            <th>Total Value (<?= html_escape($currency_symbol ?? '$'); ?>)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($orders_data)): ?>
            <?php 
              $tot_cnt = 0;
              $tot_val = 0;
              foreach ($orders_data as $row): 
                $tot_cnt += $row['count'];
                $tot_val += $row['total_value'];
            ?>
              <tr>
                <td><span class="badge bg-label-info fs-6"><?= ucfirst($row['order_status']); ?></span></td>
                <td><strong><?= $row['count']; ?> Orders</strong></td>
                <td><strong><?= $currency_symbol . number_format($row['total_value'], 2); ?></strong></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="3" class="text-center text-muted py-4">No orders found in this period.</td>
            </tr>
          <?php endif; ?>
        </tbody>
        <?php if (!empty($orders_data)): ?>
        <tfoot class="table-light fw-bold">
          <tr>
            <td>Total:</td>
            <td><?= $tot_cnt; ?> Orders</td>
            <td><?= $currency_symbol . number_format($tot_val, 2); ?></td>
          </tr>
        </tfoot>
        <?php endif; ?>
      </table>
    </div>
  </div>
</div>
