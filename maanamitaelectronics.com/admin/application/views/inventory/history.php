<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><span class="text-muted fw-light">Inventory /</span> Stock Adjustment Audit History</h4>
    <a href="<?= site_url('inventory'); ?>" class="btn btn-outline-secondary btn-sm">
      <i class="fa-solid fa-arrow-left me-1"></i> Back to Inventory
    </a>
  </div>

  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">Stock Transaction Log</h5>
    </div>
    <div class="table-responsive text-nowrap">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Log ID</th>
            <th>Product</th>
            <th>Type</th>
            <th>Units</th>
            <th>Reason</th>
            <th>Logged By</th>
            <th>Date & Time</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($history)): ?>
            <?php foreach ($history as $h): ?>
              <tr>
                <td>#<?= $h['id']; ?></td>
                <td>
                  <strong><?= html_escape($h['product_title']); ?></strong>
                  <div class="text-muted small"><code><?= html_escape($h['product_sku']); ?></code></div>
                </td>
                <td>
                  <?php if ($h['type'] === 'in'): ?>
                    <span class="badge bg-label-success"><i class="fa-solid fa-arrow-down me-1"></i> Stock In</span>
                  <?php elseif ($h['type'] === 'out'): ?>
                    <span class="badge bg-label-danger"><i class="fa-solid fa-arrow-up me-1"></i> Stock Out</span>
                  <?php else: ?>
                    <span class="badge bg-label-info"><i class="fa-solid fa-rotate me-1"></i> Adjustment</span>
                  <?php endif; ?>
                </td>
                <td>
                  <strong class="<?= ($h['type'] === 'in') ? 'text-success' : (($h['type'] === 'out') ? 'text-danger' : 'text-primary'); ?>">
                    <?= ($h['type'] === 'in') ? '+' : (($h['type'] === 'out') ? '-' : '±'); ?><?= $h['quantity']; ?>
                  </strong>
                </td>
                <td><?= html_escape($h['reason']); ?></td>
                <td><?= html_escape($h['admin_name'] ?? 'System Operator'); ?></td>
                <td><small class="text-muted"><?= date('M d, Y H:i', strtotime($h['created_at'])); ?></small></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center text-muted py-4">No stock adjustment entries logged yet.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
