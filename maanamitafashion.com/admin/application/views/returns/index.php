<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><span class="text-muted fw-light">Sales /</span> Returns & Refunds</h4>
  </div>

  <!-- Filter Card -->
  <div class="card mb-4">
    <div class="card-body">
      <form action="<?= site_url('returns'); ?>" method="GET" class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Type</label>
          <select class="form-select" name="type">
            <option value="">All Types</option>
            <option value="return" <?= ($filters['type'] === 'return') ? 'selected' : ''; ?>>Return & Refund</option>
            <option value="replacement" <?= ($filters['type'] === 'replacement') ? 'selected' : ''; ?>>Replacement / Exchange</option>
            <option value="refund" <?= ($filters['type'] === 'refund') ? 'selected' : ''; ?>>Direct Refund</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Status</label>
          <select class="form-select" name="status">
            <option value="">All Statuses</option>
            <option value="pending" <?= ($filters['status'] === 'pending') ? 'selected' : ''; ?>>Pending Review</option>
            <option value="approved" <?= ($filters['status'] === 'approved') ? 'selected' : ''; ?>>Approved</option>
            <option value="rejected" <?= ($filters['status'] === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
            <option value="refunded" <?= ($filters['status'] === 'refunded') ? 'selected' : ''; ?>>Refunded</option>
            <option value="completed" <?= ($filters['status'] === 'completed') ? 'selected' : ''; ?>>Completed</option>
          </select>
        </div>
        <div class="col-md-4 d-flex align-items-end gap-2">
          <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
          <a href="<?= site_url('returns'); ?>" class="btn btn-outline-secondary">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Table -->
  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">Return & Replacement Requests</h5>
    </div>
    <div class="table-responsive text-nowrap">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>ID</th>
            <th>Order #</th>
            <th>Customer</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($returns)): ?>
            <?php foreach ($returns as $ret): ?>
              <tr>
                <td>#<?= $ret['id']; ?></td>
                <td>
                  <a href="<?= site_url('orders/detail/' . $ret['order_id']); ?>" class="fw-bold">
                    <?= html_escape($ret['order_number']); ?>
                  </a>
                </td>
                <td>
                  <div><?= html_escape($ret['customer_name']); ?></div>
                  <small class="text-muted"><?= html_escape($ret['customer_email']); ?></small>
                </td>
                <td><span class="badge bg-label-info"><?= ucfirst($ret['type']); ?></span></td>
                <td><strong><?= html_escape($currency_symbol ?? '$') . number_format($ret['amount'], 2); ?></strong></td>
                <td>
                  <span class="d-inline-block text-truncate" style="max-width: 200px;" title="<?= html_escape($ret['reason']); ?>">
                    <?= html_escape($ret['reason']); ?>
                  </span>
                </td>
                <td>
                  <?php if ($ret['status'] === 'pending'): ?>
                    <span class="badge bg-label-warning">Pending</span>
                  <?php elseif ($ret['status'] === 'approved'): ?>
                    <span class="badge bg-label-primary">Approved</span>
                  <?php elseif ($ret['status'] === 'refunded' || $ret['status'] === 'completed'): ?>
                    <span class="badge bg-label-success"><?= ucfirst($ret['status']); ?></span>
                  <?php else: ?>
                    <span class="badge bg-label-danger">Rejected</span>
                  <?php endif; ?>
                </td>
                <td><small class="text-muted"><?= date('M d, Y', strtotime($ret['created_at'])); ?></small></td>
                <td>
                  <button type="button" class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#resolveModal<?= $ret['id']; ?>">
                    <i class="fa-solid fa-sliders me-1"></i> Resolve
                  </button>

                  <!-- Modal -->
                  <div class="modal fade" id="resolveModal<?= $ret['id']; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <form action="<?= site_url('returns/update_status/' . $ret['id']); ?>" method="POST">
                          <div class="modal-header">
                            <h5 class="modal-title">Resolve Return #<?= $ret['id']; ?> (Order: <?= html_escape($ret['order_number']); ?>)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <div class="mb-3">
                              <label class="form-label fw-bold">Customer Reason:</label>
                              <div class="p-2 border rounded bg-light"><?= nl2br(html_escape($ret['reason'])); ?></div>
                            </div>
                            <div class="mb-3">
                              <label class="form-label" for="res_status_<?= $ret['id']; ?>">Update Status</label>
                              <select class="form-select" id="res_status_<?= $ret['id']; ?>" name="status">
                                <option value="pending" <?= ($ret['status'] === 'pending') ? 'selected' : ''; ?>>Pending Review</option>
                                <option value="approved" <?= ($ret['status'] === 'approved') ? 'selected' : ''; ?>>Approve Return / Replacement</option>
                                <option value="refunded" <?= ($ret['status'] === 'refunded') ? 'selected' : ''; ?>>Mark as Refunded (<?= html_escape($currency_symbol ?? '$') . number_format($ret['amount'], 2); ?>)</option>
                                <option value="completed" <?= ($ret['status'] === 'completed') ? 'selected' : ''; ?>>Completed / Closed</option>
                                <option value="rejected" <?= ($ret['status'] === 'rejected') ? 'selected' : ''; ?>>Reject Request</option>
                              </select>
                            </div>
                            <div class="mb-3">
                              <label class="form-label">Internal Staff Notes</label>
                              <textarea class="form-control" name="admin_notes" rows="3" placeholder="Resolution notes, refund transaction id, return tracking number..."><?= html_escape($ret['admin_notes']); ?></textarea>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Status</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" class="text-center text-muted py-4">No returns or refund requests found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
