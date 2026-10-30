<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><span class="text-muted fw-light">Sales /</span> Orders</h4>
  </div>

  <!-- Filter tabs -->
  <div class="card mb-4">
    <div class="card-body py-2">
      <div class="d-flex flex-wrap gap-2">
        <a href="<?= site_url('orders'); ?>" class="btn btn-sm <?= empty($selected_status) ? 'btn-primary' : 'btn-outline-secondary'; ?>">All Orders</a>
        <a href="<?= site_url('orders?status=pending'); ?>" class="btn btn-sm <?= ($selected_status === 'pending') ? 'btn-warning' : 'btn-outline-secondary'; ?>">Pending</a>
        <a href="<?= site_url('orders?status=shipped'); ?>" class="btn btn-sm <?= ($selected_status === 'shipped') ? 'btn-primary' : 'btn-outline-secondary'; ?>">Shipped</a>
        <a href="<?= site_url('orders?status=delivered'); ?>" class="btn btn-sm <?= ($selected_status === 'delivered') ? 'btn-success' : 'btn-outline-secondary'; ?>">Delivered</a>
        <a href="<?= site_url('orders?status=cancelled'); ?>" class="btn btn-sm <?= ($selected_status === 'cancelled') ? 'btn-danger' : 'btn-outline-secondary'; ?>">Cancelled</a>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Order #</th>
            <th>Date</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Gateway</th>
            <th>Payment Status</th>
            <th>Order Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $ord): ?>
              <tr>
                <td><strong>#<?= html_escape($ord['order_number']); ?></strong></td>
                <td><small class="text-muted"><?= date('M d, Y h:i A', strtotime($ord['created_at'])); ?></small></td>
                <td>
                  <div><?= html_escape($ord['customer_name']); ?></div>
                  <small class="text-muted"><?= html_escape($ord['customer_email']); ?></small>
                </td>
                <td><strong><?= $currency_symbol . number_format($ord['total_amount'], 2); ?></strong></td>
                <td><span class="badge bg-label-dark"><?= strtoupper($ord['payment_method']); ?></span></td>
                <td>
                  <span class="badge bg-label-<?= ($ord['payment_status'] === 'paid') ? 'success' : 'warning'; ?>">
                    <?= ucfirst($ord['payment_status']); ?>
                  </span>
                </td>
                <td>
                  <?php
                    $cls = 'secondary';
                    if ($ord['order_status'] === 'delivered') $cls = 'success';
                    elseif ($ord['order_status'] === 'shipped') $cls = 'primary';
                    elseif ($ord['order_status'] === 'pending') $cls = 'warning';
                    elseif ($ord['order_status'] === 'cancelled') $cls = 'danger';
                  ?>
                  <span class="badge bg-<?= $cls; ?>"><?= ucfirst($ord['order_status']); ?></span>
                </td>
                <td>
                  <?php
                    $is_dispatched = in_array(strtolower($ord['order_status']), ['shipped', 'delivered', 'cancelled']) 
                                     || !empty($ord['shipped_at']) 
                                     || ((int) ($ord['shipped_items_count'] ?? 0) > 0);
                    $can_cancel = !$is_dispatched;
                  ?>
                  <div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                      <a class="dropdown-item" href="<?= site_url('orders/view/' . $ord['id']); ?>">
                        <i class="fa-solid fa-eye me-2 text-primary"></i> View Details
                      </a>
                      <a class="dropdown-item" href="<?= site_url('orders/invoice/' . $ord['id']); ?>" target="_blank">
                        <i class="fa-solid fa-file-invoice me-2 text-secondary"></i> Download Invoice
                      </a>
                      <?php if ($this->can('orders.manage') && $can_cancel): ?>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="cancelWholeOrder(<?= $ord['id']; ?>, '<?= html_escape(addslashes($ord['order_number'])); ?>')">
                          <i class="fa-solid fa-ban me-2"></i> Cancel
                        </a>
                      <?php endif; ?>
                    </div>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center text-muted py-4">No orders found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<form id="cancel-order-form" method="POST" action="" style="display: none;">
  <input type="hidden" name="order_id" id="cancel_order_id" value="">
</form>

<script>
function cancelWholeOrder(orderId, orderNumber) {
  if (confirm('Are you sure you want to cancel Order #' + orderNumber + '? All items in this order will be marked as cancelled.')) {
    var form = document.getElementById('cancel-order-form');
    form.action = '<?= site_url("orders/cancel/"); ?>' + orderId;
    document.getElementById('cancel_order_id').value = orderId;
    form.submit();
  }
}
</script>
