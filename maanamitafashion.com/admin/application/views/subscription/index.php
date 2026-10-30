<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1">
        <i class="ri ri-vip-crown-2-line text-primary me-2"></i>Subscription & Billing
      </h4>
      <p class="text-muted mb-0">Manage your SaaS store plan, resource limits, renewals, and billing history.</p>
    </div>
  </div>

  <?php if (!empty($subscription_expired)): ?>
    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
      <i class="ri ri-error-warning-line fs-3 me-3"></i>
      <div>
        <h6 class="alert-heading fw-bold mb-1">Your Store Subscription Has Expired!</h6>
        <span>Your public storefront is temporarily paused until you renew or upgrade your subscription below.</span>
      </div>
    </div>
  <?php endif; ?>

  <!-- Active Plan & Usage Overview -->
  <div class="row g-4 mb-4">
    <div class="col-lg-6">
      <div class="card h-100 border-primary border-opacity-25 shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
              <span class="badge bg-label-primary text-uppercase mb-2">Current Active Plan</span>
              <h3 class="fw-bold mb-1"><?= html_escape($current_plan['name'] ?? 'Standard Plan'); ?></h3>
              <p class="text-muted small mb-0"><?= html_escape($current_plan['description'] ?? 'Multi-template SaaS Ecommerce Plan'); ?></p>
            </div>
            <div class="text-end">
              <h3 class="fw-bold text-primary mb-0">
                $<?= number_format((float)($current_plan['price'] ?? 0), 0); ?>
              </h3>
              <small class="text-muted">/ <?= html_escape($current_plan['billing_cycle'] ?? 'monthly'); ?></small>
            </div>
          </div>

          <hr class="my-3">

          <div class="row g-3">
            <div class="col-sm-6">
              <div class="p-3 rounded bg-lighter">
                <small class="text-muted d-block mb-1">Store Status</small>
                <?php
                  $st = $tenant['status'] ?? 'active';
                  $is_exp = !empty($tenant['subscription_expires_at']) && strtotime($tenant['subscription_expires_at']) < time();
                ?>
                <?php if ($st === 'active' && !$is_exp): ?>
                  <span class="badge bg-success"><i class="ri ri-checkbox-circle-line me-1"></i>Active</span>
                <?php elseif ($is_exp): ?>
                  <span class="badge bg-danger"><i class="ri ri-time-line me-1"></i>Expired</span>
                <?php else: ?>
                  <span class="badge bg-warning"><?= html_escape(ucfirst($st)); ?></span>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="p-3 rounded bg-lighter">
                <small class="text-muted d-block mb-1">Valid Until / Renewal Date</small>
                <span class="fw-semibold">
                  <?= !empty($tenant['subscription_expires_at']) ? date('d M Y', strtotime($tenant['subscription_expires_at'])) : 'Lifetime / Unlimited'; ?>
                </span>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="p-3 rounded bg-lighter">
                <small class="text-muted d-block mb-1">Active Storefront Template</small>
                <span class="fw-semibold text-capitalize">
                  <i class="ri ri-palette-line text-primary me-1"></i><?= html_escape($tenant['template'] ?? 'fashion'); ?> Store
                </span>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="p-3 rounded bg-lighter">
                <small class="text-muted d-block mb-1">Store Domain / Slug</small>
                <code class="small"><?= html_escape($tenant['domain'] ?? ($tenant['slug'] ?? 'store')); ?></code>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Resource Usage Card -->
    <div class="col-lg-6">
      <div class="card h-100 shadow-sm">
        <div class="card-header pb-2">
          <h5 class="card-title mb-0"><i class="ri ri-bar-chart-box-line text-info me-2"></i>Plan Limits & Resource Usage</h5>
        </div>
        <div class="card-body">
          <?php
            $prod_limit = isset($current_plan['product_limit']) && $current_plan['product_limit'] !== null ? (int)$current_plan['product_limit'] : 0;
            $prod_pct = ($prod_limit > 0) ? min(100, round(($products_used / $prod_limit) * 100)) : 15;

            $ord_limit = isset($current_plan['order_limit']) && $current_plan['order_limit'] !== null ? (int)$current_plan['order_limit'] : 0;
            $ord_pct = ($ord_limit > 0) ? min(100, round(($orders_used / $ord_limit) * 100)) : 10;

            $allowed_tpls = !empty($current_plan['allowed_templates']) ? json_decode($current_plan['allowed_templates'], true) : null;
            if (!is_array($allowed_tpls)) {
              $allowed_tpls = !empty($current_plan['allowed_templates']) ? array_map('trim', explode(',', $current_plan['allowed_templates'])) : ['fashion'];
            }
          ?>
          <div class="mb-4">
            <div class="d-flex justify-content-between mb-1">
              <span class="fw-semibold">Catalog Products</span>
              <span class="fw-bold">
                <?= (int)$products_used; ?> / <?= ($prod_limit > 0) ? number_format($prod_limit) : 'Unlimited'; ?>
              </span>
            </div>
            <div class="progress" style="height: 10px;">
              <div class="progress-bar <?= ($prod_pct >= 90 && $prod_limit > 0) ? 'bg-danger' : 'bg-primary'; ?>" role="progressbar" style="width: <?= $prod_pct; ?>%"></div>
            </div>
            <small class="text-muted mt-1 d-block">
              <?= ($prod_limit > 0) ? max(0, $prod_limit - $products_used) . ' products remaining on your current plan.' : 'Unlimited products allowed on your current plan.'; ?>
            </small>
          </div>

          <div class="mb-4">
            <div class="d-flex justify-content-between mb-1">
              <span class="fw-semibold">Customer Orders Processed</span>
              <span class="fw-bold">
                <?= (int)$orders_used; ?> / <?= ($ord_limit > 0) ? number_format($ord_limit) : 'Unlimited'; ?>
              </span>
            </div>
            <div class="progress" style="height: 10px;">
              <div class="progress-bar bg-success" role="progressbar" style="width: <?= $ord_pct; ?>%"></div>
            </div>
          </div>

          <div>
            <span class="fw-semibold d-block mb-2">Permitted Storefront Templates on This Plan:</span>
            <div class="d-flex flex-wrap gap-2">
              <?php foreach ($allowed_tpls as $at): ?>
                <span class="badge bg-label-info text-capitalize px-3 py-2">
                  <i class="ri ri-check-line me-1"></i><?= html_escape($at); ?> Template
                </span>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Upgrade / Renew Pricing Plans -->
  <div class="card mb-4 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-0 fw-bold">Available SaaS Plans (Upgrade or Renew)</h5>
        <small class="text-muted">Extend your subscription duration or unlock more templates and product capacity.</small>
      </div>
    </div>
    <div class="card-body">
      <div class="row g-4">
        <?php
          $cur_price = (float)($current_plan['price'] ?? 0);
          $cur_sort  = (int)($current_plan['sort_order'] ?? ($current_plan['id'] ?? 0));
        ?>
        <?php foreach ($all_plans as $pl): ?>
          <?php
            $is_current = ((int)($current_plan['id'] ?? 0) === (int)$pl['id']);
            $pl_price   = (float)($pl['price'] ?? 0);
            $pl_sort    = (int)($pl['sort_order'] ?? ($pl['id'] ?? 0));
            $is_downgrade = !$is_current && ($pl_price < $cur_price || ($pl_price == $cur_price && $pl_sort < $cur_sort));
            $pl_feats = !empty($pl['features']) ? (json_decode($pl['features'], true) ?: []) : [];
            $pl_tpls = !empty($pl['allowed_templates']) ? json_decode($pl['allowed_templates'], true) : null;
            if (!is_array($pl_tpls)) {
              $pl_tpls = !empty($pl['allowed_templates']) ? array_map('trim', explode(',', $pl['allowed_templates'])) : ['fashion'];
            }
          ?>
          <div class="col-md-4">
            <div class="card h-100 <?= $is_current ? 'border-primary border-2' : ($is_downgrade ? 'border bg-light opacity-75' : 'border'); ?>">
              <div class="card-body d-flex flex-column justify-content-between">
                <div>
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="fw-bold mb-0"><?= html_escape($pl['name']); ?></h5>
                    <?php if ($is_current): ?>
                      <span class="badge bg-primary">Current Plan</span>
                    <?php elseif ($is_downgrade): ?>
                      <span class="badge bg-label-secondary">Lower Tier</span>
                    <?php elseif (!empty($pl['is_featured'])): ?>
                      <span class="badge bg-label-warning">Popular</span>
                    <?php endif; ?>
                  </div>
                  <p class="text-muted small mb-3"><?= html_escape($pl['description'] ?? ''); ?></p>
                  <div class="mb-3">
                    <span class="h2 fw-bold text-dark">$<?= number_format((float)$pl['price'], 0); ?></span>
                    <span class="text-muted">/ <?= html_escape($pl['billing_cycle']); ?> (<?= (int)$pl['duration_days']; ?> days)</span>
                  </div>
                  <ul class="list-unstyled small mb-4">
                    <li class="mb-2">
                      <i class="ri ri-check-double-line text-success me-2"></i>
                      <strong>Products:</strong> <?= ((int)$pl['product_limit'] > 0) ? number_format((int)$pl['product_limit']) . ' Products' : 'Unlimited Products'; ?>
                    </li>
                    <li class="mb-2">
                      <i class="ri ri-check-double-line text-success me-2"></i>
                      <strong>Templates:</strong> <?= html_escape(ucwords(implode(', ', $pl_tpls))); ?>
                    </li>
                    <?php foreach ($pl_feats as $f): ?>
                      <li class="mb-2">
                        <i class="ri ri-check-line text-primary me-2"></i><?= html_escape($f); ?>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>

                <?php if ($is_downgrade): ?>
                  <button type="button" class="btn btn-outline-secondary w-100" disabled>
                    <i class="ri ri-lock-line me-1"></i> Downgrade Not Allowed
                  </button>
                <?php else: ?>
                  <form method="post" action="<?= site_url('subscription/upgrade'); ?>">
                    <input type="hidden" name="plan_id" value="<?= (int)$pl['id']; ?>">
                    <input type="hidden" name="gateway" value="sandbox">
                    <button type="submit" class="btn <?= $is_current ? 'btn-outline-primary' : 'btn-primary'; ?> w-100">
                      <i class="ri ri-flashlight-line me-1"></i>
                      <?= $is_current ? 'Extend / Renew Plan' : 'Upgrade to ' . html_escape($pl['name']); ?>
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Payment & Billing History -->
  <div class="card shadow-sm">
    <div class="card-header">
      <h5 class="mb-0 fw-bold"><i class="ri ri-receipt-line text-primary me-2"></i>Subscription Payment History</h5>
    </div>
    <div class="table-responsive text-nowrap">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Order / Txn Reference</th>
            <th>Plan</th>
            <th>Gateway</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($payments)): ?>
            <tr>
              <td colspan="6" class="text-center py-4 text-muted">No subscription payment records found.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($payments as $pay): ?>
              <?php $p_st = $pay['payment_status'] ?? 'paid'; ?>
              <tr>
                <td>
                  <div class="fw-semibold"><code><?= html_escape($pay['order_number'] ?? ('ORD-' . $pay['id'])); ?></code></div>
                  <small class="text-muted"><?= html_escape($pay['transaction_id'] ?? ''); ?></small>
                </td>
                <td><span class="fw-semibold"><?= html_escape(!empty($pay['plan_name']) ? $pay['plan_name'] : ($pay['resolved_plan_name'] ?? 'SaaS Plan')); ?></span></td>
                <td><span class="badge bg-label-secondary text-uppercase"><?= html_escape($pay['gateway']); ?></span></td>
                <td class="fw-bold">$<?= number_format((float)$pay['amount'], 2); ?></td>
                <td>
                  <?php if ($p_st === 'paid'): ?>
                    <span class="badge bg-label-success">Paid</span>
                  <?php elseif ($p_st === 'pending'): ?>
                    <span class="badge bg-label-warning">Pending</span>
                  <?php else: ?>
                    <span class="badge bg-label-danger"><?= html_escape(ucfirst($p_st)); ?></span>
                  <?php endif; ?>
                </td>
                <td><?= !empty($pay['paid_at']) ? date('d M Y, h:i A', strtotime($pay['paid_at'])) : date('d M Y, h:i A', strtotime($pay['created_at'])); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
