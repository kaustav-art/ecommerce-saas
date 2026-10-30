<!-- Dashboard Header -->
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1 text-dark">
            <i class="fa-solid fa-crown text-warning me-2"></i>SaaS Platform Control Panel
        </h3>
        <p class="text-muted mb-0">Master operational control for SaaS subscription plans, multi-template storefronts, payments, and tenant stores.</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="<?= main_site_url() ?>" target="_blank" class="btn btn-outline-secondary btn-sm px-3">
            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> SaaS Landing Website
        </a>
        <a href="<?= superadmin_url('plans') ?>" class="btn btn-outline-warning btn-sm px-3 fw-bold">
            <i class="fa-solid fa-tags me-1"></i> Subscription Plans
        </a>
        <a href="<?= superadmin_url('tenants') ?>" class="btn btn-primary btn-sm px-3 fw-bold">
            <i class="fa-solid fa-store me-1"></i> Manage Tenants
        </a>
    </div>
</div>

<!-- 4 Key SaaS Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="sa-stat-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase">Total SaaS Revenue</span>
                <div class="sa-stat-icon revenue">
                    <i class="fa-solid fa-dollar-sign"></i>
                </div>
            </div>
            <h2 class="fw-bold text-success mb-1"><?= format_currency($total_revenue) ?></h2>
            <div class="d-flex align-items-center text-muted small">
                <i class="fa-solid fa-shield-check text-success me-1"></i> Verified subscription payments
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="sa-stat-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase">Paid Subscriptions</span>
                <div class="sa-stat-icon orders">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>
            <h2 class="fw-bold text-primary mb-1"><?= $total_orders ?></h2>
            <div class="d-flex align-items-center text-muted small">
                <i class="fa-solid fa-circle-check text-primary me-1"></i> Completed subscription orders
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="sa-stat-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase">Active Tenant Stores</span>
                <div class="sa-stat-icon licenses">
                    <i class="fa-solid fa-store"></i>
                </div>
            </div>
            <h2 class="fw-bold text-warning mb-1"><?= $active_tenants_count ?></h2>
            <div class="d-flex align-items-center text-muted small">
                <i class="fa-solid fa-certificate text-warning me-1"></i> Live ecommerce storefronts
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="sa-stat-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase">Storefront Templates</span>
                <div class="sa-stat-icon tenants">
                    <i class="fa-solid fa-palette"></i>
                </div>
            </div>
            <h2 class="fw-bold text-info mb-1"><?= $total_templates ?></h2>
            <div class="d-flex align-items-center text-muted small">
                <i class="fa-solid fa-layer-group text-info me-1"></i> Fashion, Electronics &amp; Grocery
            </div>
        </div>
    </div>
</div>

<!-- Active SaaS Pricing Plans Summary -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-tags text-warning me-2"></i>SaaS Subscription Plans (Live on Landing Website)
        </h6>
        <a href="<?= superadmin_url('plans') ?>" class="btn btn-sm btn-outline-primary fw-semibold">
            <i class="fa-solid fa-pen-to-square me-1"></i> Manage Plans &amp; Limits
        </a>
    </div>
    <div class="card-body p-4">
        <div class="row g-4">
            <?php foreach ($plans as $p): ?>
                <div class="col-md-4">
                    <div class="card h-100 border-2 rounded-3 <?= !empty($p->is_featured) ? 'border-warning shadow-sm bg-warning bg-opacity-10' : 'border-light shadow-sm' ?>">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge <?= !empty($p->is_featured) ? 'bg-warning text-dark' : 'bg-primary text-white' ?> text-uppercase fw-bold">
                                    <?= !empty($p->is_featured) ? 'MOST POPULAR' : htmlspecialchars($p->billing_cycle) ?>
                                </span>
                                <span class="badge <?= !empty($p->is_active) ? 'bg-success' : 'bg-secondary' ?> small text-uppercase"><?= !empty($p->is_active) ? 'Active' : 'Inactive' ?></span>
                            </div>
                            <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($p->name) ?></h5>
                            <p class="text-muted small mb-3"><?= htmlspecialchars($p->description ?: $p->subtitle) ?></p>

                            <div class="d-flex align-items-baseline gap-2 mb-3">
                                <h3 class="fw-bold text-dark mb-0"><?= format_currency($p->price) ?></h3>
                                <span class="text-muted small">/ <?= htmlspecialchars($p->billing_cycle) ?> (<?= (int)$p->duration_days ?>d)</span>
                            </div>

                            <div class="mb-3 d-flex flex-wrap gap-1">
                                <span class="badge bg-light text-dark border">
                                    Products: <?= (int)$p->product_limit === -1 ? 'Unlimited' : (int)$p->product_limit ?>
                                </span>
                                <?php
                                    $tpls = json_decode((string)$p->allowed_templates, true);
                                    if (!is_array($tpls)) {
                                        $tpls = array_filter(array_map('trim', explode(',', (string)$p->allowed_templates)));
                                    }
                                    foreach ($tpls as $tpl):
                                ?>
                                    <?= template_badge($tpl) ?>
                                <?php endforeach; ?>
                            </div>

                            <?php
                                $feats = json_decode($p->features, true);
                                if (!empty($feats)):
                            ?>
                                <ul class="list-unstyled small mb-0 lh-lg">
                                    <?php foreach (array_slice($feats, 0, 4) as $f): ?>
                                        <li class="text-secondary"><i class="fa-solid fa-check text-success me-2"></i><?= htmlspecialchars($f) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-white border-top text-center py-2">
                            <a href="<?= superadmin_url('plans') ?>" class="btn btn-sm btn-link text-decoration-none text-primary fw-bold">
                                Edit Plan <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Two Columns: Recent Subscription Payments & Recent Tenant Stores -->
<div class="row g-4">
    <!-- Left Column: Recent Payments -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-receipt text-primary me-2"></i>Recent Subscription Payments
                </h6>
                <a href="<?= superadmin_url('orders') ?>" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table sa-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Business / Owner</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_orders)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No subscription orders recorded yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_orders as $ord): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold font-monospace text-dark"><?= htmlspecialchars($ord->order_number) ?></div>
                                        <small class="text-muted"><?= format_custom_date($ord->created_at, 'd M, h:i A') ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($ord->business_name ?: $ord->customer_name) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($ord->customer_email) ?></small>
                                    </td>
                                    <td><?= plan_badge($ord->plan_name ?: $ord->plan_id) ?></td>
                                    <td class="fw-bold text-success"><?= format_currency($ord->amount) ?></td>
                                    <td><?= status_badge($ord->payment_status) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Recent Tenant Stores -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-store text-warning me-2"></i>Recent Tenant Stores
                </h6>
                <a href="<?= superadmin_url('tenants') ?>" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body p-3">
                <?php if (empty($recent_tenants)): ?>
                    <p class="text-center text-muted py-4 mb-0">No tenant stores provisioned yet.</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($recent_tenants as $t): ?>
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($t->business_name) ?></div>
                                    <div class="d-flex gap-1">
                                        <?= template_badge($t->template) ?>
                                        <?= status_badge($t->status) ?>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center small text-muted mb-2">
                                    <span><i class="fa-solid fa-link me-1"></i><code><?= htmlspecialchars($t->slug) ?></code></span>
                                    <span><?= plan_badge($t->plan_name ?: $t->plan_id) ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted"><i class="fa-regular fa-envelope me-1"></i><?= htmlspecialchars($t->admin_email) ?></small>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= htmlspecialchars($t->website_url ?: (main_site_url('website/s/' . $t->slug))) ?>" target="_blank" class="btn btn-outline-primary btn-sm py-0 px-2">Store</a>
                                        <a href="<?= htmlspecialchars(superadmin_impersonate_url($t->id)) ?>" target="_blank" class="btn btn-outline-dark btn-sm py-0 px-2">Admin</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
