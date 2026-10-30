<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/apex-charts/apex-charts.css'); ?>" />

<style>
  .dashboard-banner {
    background: linear-gradient(135deg, rgba(102, 108, 255, 0.08) 0%, rgba(102, 108, 255, 0.02) 100%);
    border: 1px solid rgba(102, 108, 255, 0.15);
  }
  .stat-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.07);
  }
  .product-thumb-sm {
    width: 44px;
    height: 44px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid rgba(0,0,0,0.06);
  }
  .rank-pill {
    width: 26px;
    height: 26px;
    font-size: 11px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
  }
  .status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
  }
  .quick-action-btn {
    transition: all 0.2s ease;
    border-radius: 8px;
  }
  .quick-action-btn:hover {
    transform: translateY(-2px);
  }
</style>

<div class="container-xxl flex-grow-1 container-p-y">

  <!-- Welcome Banner & Quick Action Header -->
  <div class="card dashboard-banner mb-4">
    <div class="card-body p-4">
      <div class="row align-items-center">
        <div class="col-lg-7 mb-3 mb-lg-0">
          <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="mb-0 fw-bold">Hello, <?= html_escape($current_admin['name']); ?>! 👋</h4>
            <span class="badge bg-primary text-uppercase fs-tiny"><?= html_escape($current_admin['role_name']); ?></span>
          </div>
          <p class="text-muted mb-0">
            Welcome back to your eCommerce command center. Here is your store's live performance and operations snapshot for <strong><?= date('l, d F Y'); ?></strong>.
          </p>
        </div>
        <div class="col-lg-5 text-lg-end">
          <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
            <a href="<?= site_url('products/add'); ?>" class="btn btn-primary shadow-sm quick-action-btn">
              <i class="fa-solid fa-plus me-1"></i> Add Product
            </a>
            <a href="<?= site_url('orders'); ?>" class="btn btn-outline-primary bg-white quick-action-btn">
              <i class="fa-solid fa-bag-shopping me-1"></i> Orders
            </a>
            <a href="<?= site_url('reports'); ?>" class="btn btn-outline-secondary bg-white quick-action-btn">
              <i class="fa-solid fa-chart-line me-1"></i> Reports
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Primary KPI Metric Cards Row -->
  <div class="row g-4 mb-4">
    <!-- Total Revenue -->
    <div class="col-sm-6 col-xl-3">
      <div class="card stat-card card-border-shadow-primary h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="avatar avatar-md">
              <span class="avatar-initial rounded-3 bg-label-primary">
                <i class="fa-solid fa-wallet fs-5"></i>
              </span>
            </div>
            <span class="badge bg-label-success">
              <i class="fa-solid fa-arrow-trend-up me-1"></i> Paid (<?= $metrics['paid_orders']; ?>)
            </span>
          </div>
          <h3 class="mb-1 fw-bold text-heading"><?= $currency_symbol . number_format($metrics['total_sales'], 2); ?></h3>
          <p class="mb-2 text-muted fw-medium fs-tiny text-uppercase">Total Paid Revenue</p>
          <div class="d-flex align-items-center text-muted small">
            <span class="me-1 text-heading fw-semibold">Avg. Order:</span>
            <span><?= $currency_symbol . number_format($metrics['avg_order_value'], 2); ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Total Orders -->
    <div class="col-sm-6 col-xl-3">
      <div class="card stat-card card-border-shadow-warning h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="avatar avatar-md">
              <span class="avatar-initial rounded-3 bg-label-warning">
                <i class="fa-solid fa-cart-shopping fs-5"></i>
              </span>
            </div>
            <span class="badge bg-label-warning">
              <?= $metrics['pending_orders']; ?> Pending
            </span>
          </div>
          <h3 class="mb-1 fw-bold text-heading"><?= number_format($metrics['total_orders']); ?></h3>
          <p class="mb-2 text-muted fw-medium fs-tiny text-uppercase">Lifetime Orders</p>
          <div class="d-flex align-items-center text-muted small">
            <span class="text-success me-2"><i class="fa-solid fa-circle-check me-1"></i><?= $metrics['completed_orders']; ?> delivered</span>
            <span>· <?= $metrics['processing_orders']; ?> processing</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Total Customers -->
    <div class="col-sm-6 col-xl-3">
      <div class="card stat-card card-border-shadow-success h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="avatar avatar-md">
              <span class="avatar-initial rounded-3 bg-label-success">
                <i class="fa-solid fa-user-group fs-5"></i>
              </span>
            </div>
            <span class="badge bg-label-info">
              <i class="fa-solid fa-users me-1"></i> Buyers
            </span>
          </div>
          <h3 class="mb-1 fw-bold text-heading"><?= number_format($total_customers); ?></h3>
          <p class="mb-2 text-muted fw-medium fs-tiny text-uppercase">Registered Customers</p>
          <div class="d-flex align-items-center text-muted small">
            <a href="<?= site_url('customers'); ?>" class="text-primary text-decoration-none">
              View customer directory &rarr;
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Live Products & Inventory Health -->
    <div class="col-sm-6 col-xl-3">
      <div class="card stat-card card-border-shadow-<?= ($low_stock_count > 0) ? 'danger' : 'info'; ?> h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="avatar avatar-md">
              <span class="avatar-initial rounded-3 bg-label-<?= ($low_stock_count > 0) ? 'danger' : 'info'; ?>">
                <i class="fa-solid fa-boxes-stacked fs-5"></i>
              </span>
            </div>
            <?php if ($low_stock_count > 0): ?>
              <a href="<?= site_url('inventory/low_stock'); ?>" class="badge bg-label-danger text-decoration-none">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $low_stock_count; ?> Alert<?= $low_stock_count > 1 ? 's' : ''; ?>
              </a>
            <?php else: ?>
              <span class="badge bg-label-success">
                <i class="fa-solid fa-check me-1"></i> Stock Healthy
              </span>
            <?php endif; ?>
          </div>
          <h3 class="mb-1 fw-bold text-heading"><?= number_format($total_products); ?></h3>
          <p class="mb-2 text-muted fw-medium fs-tiny text-uppercase">Catalog Products</p>
          <div class="d-flex align-items-center text-muted small">
            <?php if ($low_stock_count > 0): ?>
              <span class="text-danger fw-semibold"><i class="fa-solid fa-circle-exclamation me-1"></i><?= $low_stock_count; ?> low stock items</span>
            <?php else: ?>
              <span class="text-success"><i class="fa-regular fa-circle-check me-1"></i>All stock thresholds OK</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Analytics & Distribution Row -->
  <div class="row g-4 mb-4">
    <!-- Revenue & Sales Analytics Chart -->
    <div class="col-12 col-xl-8">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between pb-2">
          <div>
            <h5 class="card-title m-0 fw-bold">Revenue & Sales Trajectory</h5>
            <small class="text-muted">Monthly revenue overview & total orders volume</small>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-label-primary rounded-pill px-3 py-2">Last 6 Months</span>
            <a href="<?= site_url('reports'); ?>" class="btn btn-sm btn-icon btn-outline-secondary" title="View Full Report">
              <i class="fa-solid fa-arrow-up-right-from-square"></i>
            </a>
          </div>
        </div>
        <div class="card-body pt-1">
          <!-- Summary Metric Pills -->
          <div class="row g-3 py-3 px-2 mb-3 bg-lightest rounded-3 border">
            <div class="col-6 col-md-3">
              <small class="text-muted d-block text-uppercase fs-tiny">Total Sales</small>
              <h6 class="mb-0 fw-bold text-primary"><?= $currency_symbol . number_format($metrics['total_sales'], 2); ?></h6>
            </div>
            <div class="col-6 col-md-3">
              <small class="text-muted d-block text-uppercase fs-tiny">Avg. Order Value</small>
              <h6 class="mb-0 fw-bold"><?= $currency_symbol . number_format($metrics['avg_order_value'], 2); ?></h6>
            </div>
            <div class="col-6 col-md-3">
              <small class="text-muted d-block text-uppercase fs-tiny">Total Orders</small>
              <h6 class="mb-0 fw-bold text-info"><?= $metrics['total_orders']; ?> Orders</h6>
            </div>
            <div class="col-6 col-md-3">
              <small class="text-muted d-block text-uppercase fs-tiny">Fulfillment Rate</small>
              <h6 class="mb-0 fw-bold text-success">
                <?= $metrics['total_orders'] > 0 ? round(($metrics['completed_orders'] / $metrics['total_orders']) * 100, 1) : 0; ?>%
              </h6>
            </div>
          </div>

          <!-- ApexChart Container -->
          <div id="salesAnalyticsChart" style="min-height: 310px;"></div>
        </div>
      </div>
    </div>

    <!-- Order Fulfillment Status Donut Chart -->
    <div class="col-12 col-xl-4">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between pb-2">
          <div>
            <h5 class="card-title m-0 fw-bold">Order Fulfillment</h5>
            <small class="text-muted">Lifecycle status distribution</small>
          </div>
          <a href="<?= site_url('orders'); ?>" class="btn btn-sm btn-outline-primary">View</a>
        </div>
        <div class="card-body d-flex flex-column justify-content-between pt-1">
          <!-- Donut Chart Container -->
          <div id="orderStatusChart" class="my-auto" style="min-height: 230px;"></div>

          <!-- Status Legend breakdown list -->
          <div class="pt-3 border-top mt-2">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="d-flex align-items-center">
                <span class="status-dot bg-success me-2"></span>
                <span class="text-muted small">Delivered</span>
              </div>
              <div>
                <span class="fw-bold me-2"><?= $status_counts['delivered']; ?></span>
                <span class="badge bg-label-success fs-tiny"><?= $metrics['total_orders'] > 0 ? round(($status_counts['delivered'] / $metrics['total_orders']) * 100) : 0; ?>%</span>
              </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="d-flex align-items-center">
                <span class="status-dot bg-info me-2"></span>
                <span class="text-muted small">Shipped</span>
              </div>
              <div>
                <span class="fw-bold me-2"><?= $status_counts['shipped']; ?></span>
                <span class="badge bg-label-info fs-tiny"><?= $metrics['total_orders'] > 0 ? round(($status_counts['shipped'] / $metrics['total_orders']) * 100) : 0; ?>%</span>
              </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="d-flex align-items-center">
                <span class="status-dot bg-primary me-2"></span>
                <span class="text-muted small">Processing</span>
              </div>
              <div>
                <span class="fw-bold me-2"><?= $status_counts['processing']; ?></span>
                <span class="badge bg-label-primary fs-tiny"><?= $metrics['total_orders'] > 0 ? round(($status_counts['processing'] / $metrics['total_orders']) * 100) : 0; ?>%</span>
              </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="d-flex align-items-center">
                <span class="status-dot bg-warning me-2"></span>
                <span class="text-muted small">Pending</span>
              </div>
              <div>
                <span class="fw-bold me-2"><?= $status_counts['pending']; ?></span>
                <span class="badge bg-label-warning fs-tiny"><?= $metrics['total_orders'] > 0 ? round(($status_counts['pending'] / $metrics['total_orders']) * 100) : 0; ?>%</span>
              </div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <span class="status-dot bg-danger me-2"></span>
                <span class="text-muted small">Cancelled</span>
              </div>
              <div>
                <span class="fw-bold me-2"><?= $status_counts['cancelled']; ?></span>
                <span class="badge bg-label-danger fs-tiny"><?= $metrics['total_orders'] > 0 ? round(($status_counts['cancelled'] / $metrics['total_orders']) * 100) : 0; ?>%</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Orders & Top Selling Products Row -->
  <div class="row g-4 mb-4">
    <!-- Recent Orders Table -->
    <div class="col-12 col-xl-8">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div>
            <h5 class="card-title m-0 fw-bold">Recent Orders</h5>
            <small class="text-muted">Latest customer transactions and fulfillment status</small>
          </div>
          <a href="<?= site_url('orders'); ?>" class="btn btn-sm btn-primary">
            View All Orders <i class="fa-solid fa-arrow-right ms-1"></i>
          </a>
        </div>
        <div class="table-responsive text-nowrap">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                <th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($recent_orders)): ?>
                <?php foreach ($recent_orders as $ord): ?>
                  <tr>
                    <td>
                      <a href="<?= site_url('orders/view/' . $ord['id']); ?>" class="fw-bold text-primary">
                        #<?= html_escape($ord['order_number']); ?>
                      </a>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="avatar avatar-xs me-2">
                          <span class="avatar-initial rounded-circle bg-label-secondary small">
                            <?= strtoupper(substr($ord['customer_name'] ?: 'G', 0, 1)); ?>
                          </span>
                        </div>
                        <div>
                          <div class="fw-semibold text-heading small"><?= html_escape($ord['customer_name']); ?></div>
                          <small class="text-muted fs-tiny"><?= html_escape($ord['customer_email']); ?></small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <small class="text-muted"><?= date('M d, Y', strtotime($ord['created_at'])); ?></small>
                    </td>
                    <td>
                      <strong class="text-heading"><?= $currency_symbol . number_format($ord['total_amount'], 2); ?></strong>
                    </td>
                    <td>
                      <?php
                        $pay_badge = 'warning';
                        if ($ord['payment_status'] === 'paid') $pay_badge = 'success';
                        elseif ($ord['payment_status'] === 'failed' || $ord['payment_status'] === 'refunded') $pay_badge = 'danger';
                      ?>
                      <div class="d-flex flex-column gap-1 align-items-start">
                        <span class="badge bg-label-dark fs-tiny text-uppercase"><?= html_escape($ord['payment_method']); ?></span>
                        <span class="badge bg-label-<?= $pay_badge; ?> fs-tiny"><?= ucfirst($ord['payment_status']); ?></span>
                      </div>
                    </td>
                    <td>
                      <?php
                        $badge_class = 'secondary';
                        if ($ord['order_status'] === 'delivered') $badge_class = 'success';
                        elseif ($ord['order_status'] === 'shipped') $badge_class = 'info';
                        elseif ($ord['order_status'] === 'processing') $badge_class = 'primary';
                        elseif ($ord['order_status'] === 'pending') $badge_class = 'warning';
                        elseif ($ord['order_status'] === 'cancelled') $badge_class = 'danger';
                      ?>
                      <span class="badge bg-<?= $badge_class; ?> rounded-pill px-2">
                        <?= ucfirst($ord['order_status']); ?>
                      </span>
                    </td>
                    <td class="text-end">
                      <a href="<?= site_url('orders/view/' . $ord['id']); ?>" class="btn btn-sm btn-icon btn-outline-secondary" title="View Details">
                        <i class="fa-solid fa-eye"></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="text-center text-muted py-5">
                    <i class="fa-solid fa-cart-arrow-down fs-1 text-muted d-block mb-2 opacity-50"></i>
                    No recent orders found.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Top Selling Products -->
    <div class="col-12 col-xl-4">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between pb-3">
          <div>
            <h5 class="card-title m-0 fw-bold">Top Products</h5>
            <small class="text-muted">By sales volume & revenue</small>
          </div>
          <a href="<?= site_url('products'); ?>" class="btn btn-sm btn-outline-primary">Catalog</a>
        </div>
        <div class="card-body pt-0">
          <?php if (!empty($top_products)): ?>
            <div class="d-flex flex-column gap-3">
              <?php foreach ($top_products as $idx => $tp): ?>
                <?php
                  $rank = $idx + 1;
                  $rank_bg = 'bg-label-secondary text-secondary';
                  if ($rank === 1) $rank_bg = 'bg-warning text-white';
                  elseif ($rank === 2) $rank_bg = 'bg-label-primary text-primary';
                  elseif ($rank === 3) $rank_bg = 'bg-label-info text-info';
                ?>
                <div class="d-flex align-items-center justify-content-between p-2 rounded hover-bg-light border-bottom pb-2">
                  <div class="d-flex align-items-center gap-3 overflow-hidden">
                    <span class="rank-pill <?= $rank_bg; ?>">#<?= $rank; ?></span>
                    <img
                      src="<?= base_url('../website/assets/images/' . ($tp['main_image'] ?: 'products/womens/women-1.jpg')); ?>"
                      alt="<?= html_escape($tp['title']); ?>"
                      class="product-thumb-sm"
                      onerror="this.src='<?= base_url('../website/assets/images/products/womens/women-1.jpg'); ?>'" />
                    <div class="text-truncate">
                      <a href="<?= site_url('products/edit/' . $tp['id']); ?>" class="fw-semibold text-heading text-truncate d-block small text-decoration-none">
                        <?= html_escape($tp['title']); ?>
                      </a>
                      <small class="text-muted fs-tiny"><?= html_escape($tp['category_name'] ?: 'General'); ?></small>
                    </div>
                  </div>
                  <div class="text-end ps-2 flex-shrink-0">
                    <div class="fw-bold text-heading small">
                      <?= $tp['units_sold'] > 0 ? ($tp['units_sold'] . ' sold') : ($tp['stock_quantity'] . ' in stock'); ?>
                    </div>
                    <small class="text-success fw-semibold fs-tiny">
                      <?= $tp['total_revenue'] > 0 ? ($currency_symbol . number_format($tp['total_revenue'], 2)) : ($currency_symbol . number_format($tp['price'], 2)); ?>
                    </small>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="text-center text-muted py-4">No products found in catalog.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Store Operations & Health Row (3 Columns) -->
  <div class="row g-4">
    <!-- Payment Gateways Status -->
    <div class="col-12 col-md-6 col-xl-4">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between pb-3">
          <div>
            <h5 class="card-title m-0 fw-bold">Payment Gateways</h5>
            <small class="text-muted">Gateway integrations & mode</small>
          </div>
          <a href="<?= site_url('settings'); ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-gear me-1"></i> Settings
          </a>
        </div>
        <div class="card-body">
          <div class="d-flex flex-column gap-3">
            <?php if (!empty($payment_gateways)): ?>
              <?php foreach ($payment_gateways as $gw): ?>
                <?php
                  $icon = 'fa-credit-card';
                  $icon_color = 'primary';
                  if ($gw['gateway_code'] === 'stripe') { $icon = 'fa-brands fa-stripe'; $icon_color = 'primary'; }
                  elseif ($gw['gateway_code'] === 'razorpay') { $icon = 'fa-solid fa-bolt'; $icon_color = 'info'; }
                  elseif ($gw['gateway_code'] === 'payu') { $icon = 'fa-solid fa-money-bill-transfer'; $icon_color = 'warning'; }
                  elseif ($gw['gateway_code'] === 'cod') { $icon = 'fa-solid fa-money-bill-wave'; $icon_color = 'success'; }
                ?>
                <div class="d-flex align-items-center justify-content-between p-2 border rounded-3">
                  <div class="d-flex align-items-center gap-2">
                    <div class="avatar avatar-sm">
                      <span class="avatar-initial rounded bg-label-<?= $icon_color; ?>">
                        <i class="<?= $icon; ?> fs-6"></i>
                      </span>
                    </div>
                    <div>
                      <div class="fw-semibold text-heading small"><?= html_escape($gw['gateway_name']); ?></div>
                      <small class="text-muted text-uppercase fs-tiny"><?= html_escape($gw['gateway_code']); ?></small>
                    </div>
                  </div>
                  <div class="text-end">
                    <?php if ($gw['is_active']): ?>
                      <span class="badge bg-label-success fs-tiny me-1">Active</span>
                      <span class="badge bg-label-<?= $gw['environment'] === 'live' ? 'primary' : 'warning'; ?> fs-tiny text-uppercase"><?= html_escape($gw['environment']); ?></span>
                    <?php else: ?>
                      <span class="badge bg-label-secondary fs-tiny">Disabled</span>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-muted small text-center py-3">No payment gateways configured.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Low Stock Inventory Watchlist -->
    <div class="col-12 col-md-6 col-xl-4">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between pb-3">
          <div>
            <h5 class="card-title m-0 fw-bold">Low Stock Watchlist</h5>
            <small class="text-muted">Items below restock threshold (&le; 10)</small>
          </div>
          <a href="<?= site_url('inventory/low_stock'); ?>" class="btn btn-sm btn-outline-danger">
            Alerts (<?= $low_stock_count; ?>)
          </a>
        </div>
        <div class="card-body">
          <?php if (!empty($low_stock_items)): ?>
            <div class="d-flex flex-column gap-3">
              <?php foreach ($low_stock_items as $lsi): ?>
                <div class="d-flex align-items-center justify-content-between p-2 border rounded-3">
                  <div class="d-flex align-items-center gap-2 overflow-hidden">
                    <img
                      src="<?= base_url('../website/assets/images/' . ($lsi['main_image'] ?: 'products/womens/women-1.jpg')); ?>"
                      class="product-thumb-sm"
                      onerror="this.src='<?= base_url('../website/assets/images/products/womens/women-1.jpg'); ?>'" />
                    <div class="text-truncate">
                      <div class="fw-semibold text-heading small text-truncate"><?= html_escape($lsi['title']); ?></div>
                      <small class="text-muted fs-tiny">SKU: <code><?= html_escape($lsi['sku']); ?></code></small>
                    </div>
                  </div>
                  <div class="text-end flex-shrink-0 ps-2">
                    <span class="badge bg-label-<?= ($lsi['stock_quantity'] <= 0) ? 'danger' : 'warning'; ?> mb-1">
                      <?= $lsi['stock_quantity']; ?> left
                    </span>
                    <div>
                      <a href="<?= site_url('inventory/adjust?product_id=' . $lsi['id']); ?>" class="btn btn-xs btn-primary">
                        Restock
                      </a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="text-center py-4">
              <div class="avatar avatar-md mx-auto mb-2">
                <span class="avatar-initial rounded-circle bg-label-success">
                  <i class="fa-solid fa-check fs-4"></i>
                </span>
              </div>
              <h6 class="mb-1 text-heading fw-bold">Stock Levels Healthy!</h6>
              <p class="text-muted small mb-0">All catalog products have sufficient inventory on hand.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Recent Customers & Fast Shortcuts -->
    <div class="col-12 col-md-12 col-xl-4">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between pb-3">
          <div>
            <h5 class="card-title m-0 fw-bold">Recent Customers</h5>
            <small class="text-muted">Newly registered customer accounts</small>
          </div>
          <a href="<?= site_url('customers'); ?>" class="btn btn-sm btn-outline-primary">All Users</a>
        </div>
        <div class="card-body">
          <ul class="list-unstyled mb-3">
            <?php if (!empty($recent_customers)): ?>
              <?php foreach ($recent_customers as $cust): ?>
                <li class="d-flex align-items-center mb-3 pb-2 border-bottom">
                  <div class="avatar avatar-sm me-3">
                    <span class="avatar-initial rounded-circle bg-label-primary fw-bold">
                      <?= strtoupper(substr($cust['first_name'], 0, 1)); ?>
                    </span>
                  </div>
                  <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2 overflow-hidden">
                    <div class="me-2 text-truncate">
                      <h6 class="mb-0 small fw-semibold text-heading text-truncate">
                        <?= html_escape($cust['first_name'] . ' ' . $cust['last_name']); ?>
                      </h6>
                      <small class="text-muted fs-tiny"><?= html_escape($cust['email']); ?></small>
                    </div>
                    <span class="badge bg-label-<?= ($cust['status'] === 'active') ? 'success' : 'danger'; ?> fs-tiny">
                      <?= ucfirst($cust['status']); ?>
                    </span>
                  </div>
                </li>
              <?php endforeach; ?>
            <?php else: ?>
              <li class="text-center text-muted py-3 small">No customers registered yet.</li>
            <?php endif; ?>
          </ul>

          <!-- Quick Navigation Shortcuts -->
          <h6 class="text-muted text-uppercase fs-tiny fw-bold mb-2">Fast Shortcuts</h6>
          <div class="d-grid gap-2 d-md-flex">
            <a href="<?= site_url('categories'); ?>" class="btn btn-xs btn-outline-secondary flex-fill text-nowrap">
              <i class="fa-solid fa-tags me-1"></i> Categories
            </a>
            <a href="<?= site_url('coupons'); ?>" class="btn btn-xs btn-outline-secondary flex-fill text-nowrap">
              <i class="fa-solid fa-ticket me-1"></i> Coupons
            </a>
            <a href="<?= site_url('reviews'); ?>" class="btn btn-xs btn-outline-secondary flex-fill text-nowrap">
              <i class="fa-solid fa-star me-1"></i> Reviews
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- ApexCharts Library -->
<script src="<?= base_url('assets/vendor/libs/apex-charts/apexcharts.js'); ?>"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // Chart Colors & Config
  const primaryColor = '#666cff';
  const infoColor    = '#00cfe8';
  const successColor = '#28c76f';
  const warningColor = '#ff9f43';
  const dangerColor  = '#ea5455';
  const labelColor   = '#828399';
  const borderColor  = '#f1f1f2';

  const currencySymbol = <?= json_encode($currency_symbol); ?>;
  const salesCategories = <?= json_encode($sales_chart['categories']); ?>;
  const revenueSeries   = <?= json_encode($sales_chart['revenue']); ?>;
  const ordersSeries    = <?= json_encode($sales_chart['orders']); ?>;

  // 1. Sales & Revenue Analytics Mixed Chart (Area + Column)
  const salesChartEl = document.querySelector("#salesAnalyticsChart");
  if (salesChartEl) {
    const salesChartOptions = {
      series: [
        {
          name: 'Revenue (' + currencySymbol + ')',
          type: 'area',
          data: revenueSeries
        },
        {
          name: 'Orders Count',
          type: 'column',
          data: ordersSeries
        }
      ],
      chart: {
        height: 310,
        type: 'line',
        stacked: false,
        parentHeightOffset: 0,
        toolbar: { show: false }
      },
      colors: [primaryColor, infoColor],
      stroke: {
        width: [3, 0],
        curve: 'smooth'
      },
      plotOptions: {
        bar: {
          columnWidth: '32%',
          borderRadius: 6
        }
      },
      fill: {
        type: ['gradient', 'solid'],
        gradient: {
          shade: 'light',
          type: 'vertical',
          shadeIntensity: 0.5,
          gradientToColors: ['#a3a6ff'],
          inverseColors: false,
          opacityFrom: 0.45,
          opacityTo: 0.05,
          stops: [0, 90, 100]
        }
      },
      labels: salesCategories,
      xaxis: {
        categories: salesCategories,
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: {
          style: {
            fontSize: '12px',
            colors: labelColor
          }
        }
      },
      yaxis: [
        {
          title: {
            text: 'Revenue (' + currencySymbol + ')',
            style: { color: primaryColor, fontWeight: 500, fontSize: '12px' }
          },
          labels: {
            formatter: function (val) {
              return currencySymbol + Number(val).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            },
            style: { colors: labelColor }
          }
        },
        {
          opposite: true,
          title: {
            text: 'Orders Count',
            style: { color: infoColor, fontWeight: 500, fontSize: '12px' }
          },
          labels: {
            formatter: function (val) {
              return Math.round(val);
            },
            style: { colors: labelColor }
          }
        }
      ],
      tooltip: {
        shared: true,
        intersect: false,
        y: {
          formatter: function (y, { seriesIndex }) {
            if (seriesIndex === 0) {
              return currencySymbol + (y ? Number(y).toFixed(2) : '0.00');
            }
            return (y ? Math.round(y) : 0) + ' orders';
          }
        }
      },
      legend: {
        position: 'top',
        horizontalAlign: 'right',
        offsetY: -8,
        fontSize: '13px',
        markers: { radius: 12 }
      },
      grid: {
        borderColor: borderColor,
        strokeDashArray: 4,
        padding: { left: 10, right: 10, bottom: 0 }
      }
    };

    const salesChart = new ApexCharts(salesChartEl, salesChartOptions);
    salesChart.render();
  }

  // 2. Order Status Donut Chart
  const orderStatusEl = document.querySelector("#orderStatusChart");
  if (orderStatusEl) {
    const statusCounts = <?= json_encode($status_counts); ?>;
    const donutLabels = ['Delivered', 'Shipped', 'Processing', 'Pending', 'Cancelled'];
    const donutSeries = [
      Number(statusCounts.delivered || 0),
      Number(statusCounts.shipped || 0),
      Number(statusCounts.processing || 0),
      Number(statusCounts.pending || 0),
      Number(statusCounts.cancelled || 0)
    ];
    const donutColors = [successColor, infoColor, primaryColor, warningColor, dangerColor];

    const totalOrdersCount = donutSeries.reduce(function (a, b) { return a + b; }, 0);

    const donutOptions = {
      chart: {
        height: 240,
        type: 'donut',
        parentHeightOffset: 0
      },
      labels: donutLabels,
      series: totalOrdersCount === 0 ? [1] : donutSeries,
      colors: totalOrdersCount === 0 ? ['#e0e0e0'] : donutColors,
      stroke: { width: 2, colors: ['#ffffff'] },
      dataLabels: {
        enabled: totalOrdersCount > 0,
        formatter: function (val) {
          return parseInt(val) + '%';
        }
      },
      legend: { show: false },
      plotOptions: {
        pie: {
          donut: {
            size: '72%',
            labels: {
              show: true,
              value: {
                fontSize: '1.25rem',
                fontWeight: 600,
                color: '#32475b',
                offsetY: -10,
                formatter: function (val) {
                  return totalOrdersCount === 0 ? '0' : val;
                }
              },
              name: {
                offsetY: 20,
                fontSize: '0.8rem',
                color: labelColor
              },
              total: {
                show: true,
                label: 'Total Orders',
                color: labelColor,
                fontSize: '0.8rem',
                formatter: function () {
                  return totalOrdersCount;
                }
              }
            }
          }
        }
      },
      tooltip: {
        custom: function ({ series, seriesIndex, w }) {
          if (totalOrdersCount === 0) {
            return '<div class="p-2"><small>No orders placed yet</small></div>';
          }
          const label = w.config.labels[seriesIndex];
          const val = series[seriesIndex];
          return '<div class="p-2 small"><strong>' + label + ':</strong> ' + val + ' orders</div>';
        }
      }
    };

    const orderStatusChart = new ApexCharts(orderStatusEl, donutOptions);
    orderStatusChart.render();
  }
});
</script>
