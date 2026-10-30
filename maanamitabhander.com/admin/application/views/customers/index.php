<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold m-0"><span class="text-muted fw-light">Users /</span> Customers</h4>
      <small class="text-muted">Manage registered customers, customer groups, cart activity, and account status</small>
    </div>
    <?php if ($this->can('customers.manage')): ?>
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
        <i class="fa-solid fa-user-plus me-1"></i> Add Customer
      </button>
    <?php endif; ?>
  </div>

  <!-- Summary KPI Cards -->
  <div class="row mb-4 g-3">
    <div class="col-sm-6 col-lg-3">
      <div class="card card-border-shadow-primary h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2 pb-1">
            <div class="avatar me-3">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="fa-solid fa-users fs-4"></i>
              </span>
            </div>
            <h4 class="ms-1 mb-0"><?= number_format($stats['total_customers'] ?? 0); ?></h4>
          </div>
          <p class="mb-0 text-muted small">Total Customers</p>
          <small class="text-success fw-semibold">
            <i class="fa-solid fa-check-circle me-1"></i><?= number_format($stats['active_customers'] ?? 0); ?> Active
          </small>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="card card-border-shadow-warning h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2 pb-1">
            <div class="avatar me-3">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="fa-solid fa-cart-arrow-down fs-4"></i>
              </span>
            </div>
            <h4 class="ms-1 mb-0"><?= number_format($stats['abandoned_carts_count'] ?? 0); ?></h4>
          </div>
          <p class="mb-0 text-muted small">Abandoned Carts</p>
          <small class="text-warning fw-semibold">Users with pending items</small>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="card card-border-shadow-success h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2 pb-1">
            <div class="avatar me-3">
              <span class="avatar-initial rounded bg-label-success">
                <i class="fa-solid fa-sack-dollar fs-4"></i>
              </span>
            </div>
            <h4 class="ms-1 mb-0"><?= $currency_symbol . number_format($stats['total_revenue'] ?? 0, 2); ?></h4>
          </div>
          <p class="mb-0 text-muted small">Total Paid Revenue</p>
          <small class="text-success fw-semibold">From completed customer orders</small>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="card card-border-shadow-danger h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2 pb-1">
            <div class="avatar me-3">
              <span class="avatar-initial rounded bg-label-danger">
                <i class="fa-solid fa-user-slash fs-4"></i>
              </span>
            </div>
            <h4 class="ms-1 mb-0"><?= number_format($stats['banned_customers'] ?? 0); ?></h4>
          </div>
          <p class="mb-0 text-muted small">Banned / Suspended</p>
          <small class="text-danger fw-semibold">Restricted customer accounts</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter & Search Card -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="get" action="<?= site_url('customers'); ?>" class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label small fw-semibold text-muted">Search Customers</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input type="text" name="search" class="form-control" placeholder="Name, email, phone..." value="<?= html_escape($filters['search'] ?? ''); ?>">
          </div>
        </div>

        <div class="col-md-3">
          <label class="form-label small fw-semibold text-muted">Account Status</label>
          <select name="status" class="form-select">
            <option value="">All Statuses</option>
            <option value="active" <?= (($filters['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Active</option>
            <option value="banned" <?= (($filters['status'] ?? '') === 'banned') ? 'selected' : ''; ?>>Banned</option>
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label small fw-semibold text-muted">Customer Group</label>
          <select name="group_id" class="form-select">
            <option value="">All Groups</option>
            <?php if (!empty($groups)): ?>
              <?php foreach ($groups as $g): ?>
                <option value="<?= $g['id']; ?>" <?= ((string)($filters['group_id'] ?? '') === (string)$g['id']) ? 'selected' : ''; ?>>
                  <?= html_escape($g['name']); ?> (<?= $g['discount_percent']; ?>% off)
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-primary w-100">
            <i class="fa-solid fa-filter me-1"></i> Filter
          </button>
          <a href="<?= site_url('customers'); ?>" class="btn btn-outline-secondary" title="Reset Filters">
            <i class="fa-solid fa-rotate-right"></i>
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Customers Table Card -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">Customer Directory (<?= count($customers); ?>)</h5>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Customer</th>
            <th>Contact Details</th>
            <th>Group</th>
            <th>Orders & Spent</th>
            <th>Abandoned Cart</th>
            <th>Wishlist</th>
            <th>Status</th>
            <th>Registered</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($customers)): ?>
            <?php foreach ($customers as $c): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar avatar-md me-3">
                      <span class="avatar-initial rounded-circle bg-label-primary fw-bold">
                        <?= strtoupper(substr($c['first_name'] ?? 'U', 0, 1)); ?>
                      </span>
                    </div>
                    <div>
                      <a href="<?= site_url('customers/view/' . $c['id']); ?>" class="text-heading fw-semibold text-decoration-none">
                        <?= html_escape(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? '')); ?>
                      </a>
                      <div class="small text-muted">ID: #<?= $c['id']; ?></div>
                    </div>
                  </div>
                </td>
                <td>
                  <div><i class="fa-regular fa-envelope me-1 text-muted"></i> <a href="mailto:<?= html_escape($c['email']); ?>" class="text-body"><?= html_escape($c['email']); ?></a></div>
                  <?php if (!empty($c['phone'])): ?>
                    <small class="text-muted"><i class="fa-solid fa-phone me-1"></i> <?= html_escape($c['phone']); ?></small>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($c['group_name'])): ?>
                    <span class="badge bg-label-info">
                      <i class="fa-solid fa-users-rectangle me-1"></i> <?= html_escape($c['group_name']); ?>
                    </span>
                  <?php else: ?>
                    <span class="badge bg-label-secondary">General</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div><span class="badge bg-label-primary"><?= (int)($c['orders_count'] ?? 0); ?> Orders</span></div>
                  <strong class="text-success small"><?= $currency_symbol . number_format($c['total_spent'] ?? 0, 2); ?></strong>
                </td>
                <td>
                  <?php if (!empty($c['cart_items_count']) && $c['cart_items_count'] > 0): ?>
                    <a href="<?= site_url('customers/view/' . $c['id'] . '#tab-cart'); ?>" class="badge bg-label-warning text-decoration-none" title="View Abandoned Cart">
                      <i class="fa-solid fa-cart-arrow-down me-1"></i> <?= $c['cart_items_count']; ?> item<?= $c['cart_items_count'] > 1 ? 's' : ''; ?>
                      <div class="fw-semibold mt-1"><?= $currency_symbol . number_format($c['cart_total'] ?? 0, 2); ?></div>
                    </a>
                  <?php else: ?>
                    <span class="text-muted small"><i class="fa-regular fa-circle-check text-muted me-1"></i> Empty</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($c['wishlist_count']) && $c['wishlist_count'] > 0): ?>
                    <a href="<?= site_url('customers/view/' . $c['id'] . '#tab-wishlist'); ?>" class="badge bg-label-danger text-decoration-none">
                      <i class="fa-regular fa-heart me-1"></i> <?= $c['wishlist_count']; ?> item<?= $c['wishlist_count'] > 1 ? 's' : ''; ?>
                    </a>
                  <?php else: ?>
                    <span class="text-muted small">0 items</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge bg-label-<?= ($c['status'] === 'active') ? 'success' : 'danger'; ?>">
                    <i class="fa-solid <?= ($c['status'] === 'active') ? 'fa-check' : 'fa-ban'; ?> me-1"></i>
                    <?= ucfirst($c['status'] ?? 'active'); ?>
                  </span>
                </td>
                <td>
                  <small class="text-muted d-block"><?= date('M d, Y', strtotime($c['created_at'])); ?></small>
                  <small class="text-muted" style="font-size: 11px;"><?= date('h:i A', strtotime($c['created_at'])); ?></small>
                </td>
                <td class="text-end">
                  <div class="d-inline-flex gap-1">
                    <a href="<?= site_url('customers/view/' . $c['id']); ?>" class="btn btn-sm btn-icon btn-outline-primary" title="View Profile & Insights">
                      <i class="fa-solid fa-eye"></i>
                    </a>
                    <?php if ($this->can('customers.manage')): ?>
                      <a href="<?= site_url('customers/toggle_status/' . $c['id']); ?>" 
                         class="btn btn-sm btn-icon btn-outline-<?= ($c['status'] === 'active') ? 'warning' : 'success'; ?>" 
                         title="<?= ($c['status'] === 'active') ? 'Ban Customer' : 'Activate Customer'; ?>"
                         onclick="return confirm('Are you sure you want to <?= ($c['status'] === 'active') ? 'ban' : 'activate'; ?> this customer?');">
                        <i class="fa-solid <?= ($c['status'] === 'active') ? 'fa-ban' : 'fa-check'; ?>"></i>
                      </a>
                      <a href="<?= site_url('customers/delete/' . $c['id']); ?>" 
                         class="btn btn-sm btn-icon btn-outline-danger" 
                         title="Delete Customer"
                         onclick="return confirm('Are you sure you want to delete this customer? This will also remove their addresses and review links.');">
                        <i class="fa-solid fa-trash-can"></i>
                      </a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" class="text-center text-muted py-5">
                <i class="fa-solid fa-users-slash fs-1 text-muted d-block mb-3"></i>
                No customers found matching your criteria.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal: Add New Customer -->
<?php if ($this->can('customers.manage')): ?>
  <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="post" action="<?= site_url('customers/create'); ?>">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fa-solid fa-user-plus me-2 text-primary"></i>Add New Customer</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                <input type="text" name="first_name" class="form-control" required placeholder="John">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                <input type="text" name="last_name" class="form-control" required placeholder="Doe">
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" required placeholder="john.doe@example.com">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Phone Number</label>
                <input type="text" name="phone" class="form-control" placeholder="+91 9876543210">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control" required minlength="6" placeholder="Min. 6 characters">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Customer Group</label>
                <select name="customer_group_id" class="form-select">
                  <option value="">General / None</option>
                  <?php if (!empty($groups)): ?>
                    <?php foreach ($groups as $g): ?>
                      <option value="<?= $g['id']; ?>"><?= html_escape($g['name']); ?> (<?= $g['discount_percent']; ?>% discount)</option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Status</label>
                <select name="status" class="form-select">
                  <option value="active">Active</option>
                  <option value="banned">Banned</option>
                </select>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Create Customer</button>
          </div>
        </form>
      </div>
    </div>
  </div>
<?php endif; ?>
