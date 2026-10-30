<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="fa-solid fa-receipt text-primary me-2"></i>SaaS Subscription Payments
        </h4>
        <p class="text-muted mb-0">Track all SaaS plan purchases, gateway transaction references, chosen templates, and provisioned tenant stores.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= superadmin_url('orders/export') ?>" class="btn btn-outline-secondary btn-sm px-3">
            <i class="fa-solid fa-file-csv me-1"></i> Export CSV
        </a>
        <a href="<?= superadmin_url('tenants') ?>" class="btn btn-primary btn-sm px-3 fw-bold">
            <i class="fa-solid fa-store me-1"></i> Provision Store
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="get" action="<?= superadmin_url('orders') ?>" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" class="form-control" name="search" placeholder="Search order #, business, email, txn ID..." value="<?= htmlspecialchars($search ?: '') ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="plan">
                    <option value="">All Subscription Plans</option>
                    <?php foreach ($plans as $p): ?>
                        <option value="<?= $p->id ?>" <?= (int)$filter_plan === (int)$p->id ? 'selected' : '' ?>><?= htmlspecialchars($p->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Payment Statuses</option>
                    <option value="paid" <?= $filter_status === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="failed" <?= $filter_status === 'failed' ? 'selected' : '' ?>>Failed</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-dark btn-sm w-100 fw-semibold">Filter</button>
                <a href="<?= superadmin_url('orders') ?>" class="btn btn-outline-secondary btn-sm" title="Clear Filters"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="table-responsive">
        <table class="table sa-table align-middle mb-0">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Business / Owner</th>
                    <th>Plan</th>
                    <th>Template</th>
                    <th>Amount</th>
                    <th>Gateway / Status</th>
                    <th>Transaction ID</th>
                    <th>Tenant Store</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-folder-open fs-2 d-block mb-2 text-secondary"></i>
                            No subscription payments match your search criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $ord): ?>
                        <tr>
                            <td>
                                <div class="fw-bold font-monospace text-dark"><?= htmlspecialchars($ord->order_number) ?></div>
                                <small class="text-muted"><?= format_custom_date($ord->created_at) ?></small>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($ord->business_name ?: $ord->customer_name) ?></div>
                                <div class="small text-muted"><i class="fa-regular fa-envelope me-1"></i><?= htmlspecialchars($ord->customer_email) ?></div>
                            </td>
                            <td><?= plan_badge($ord->plan_title ?: $ord->plan_name) ?></td>
                            <td><?= template_badge($ord->chosen_template) ?></td>
                            <td class="fw-bold text-success fs-6"><?= format_currency($ord->amount) ?></td>
                            <td>
                                <div><?= status_badge($ord->payment_status) ?></div>
                                <small class="text-muted text-uppercase" style="font-size:11px;"><?= htmlspecialchars($ord->gateway) ?></small>
                            </td>
                            <td>
                                <code class="small text-dark"><?= htmlspecialchars($ord->transaction_id ?: 'N/A') ?></code>
                            </td>
                            <td>
                                <?php if (!empty($ord->tenant_id)): ?>
                                    <a href="<?= main_site_url('website/s/' . ($ord->tenant_slug ?: $ord->store_slug)) ?>" target="_blank" class="badge bg-light text-primary border text-decoration-none">
                                        Tenant #<?= (int)$ord->tenant_id ?> <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary">Not Provisioned</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <form action="<?= superadmin_url('orders/update_status') ?>" method="post" class="d-inline">
                                    <input type="hidden" name="order_id" value="<?= $ord->id ?>">
                                    <input type="hidden" name="action" value="change_payment_status">
                                    <input type="hidden" name="payment_status" value="<?= $ord->payment_status === 'paid' ? 'pending' : 'paid' ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-<?= $ord->payment_status === 'paid' ? 'secondary' : 'success' ?>">
                                        <?= $ord->payment_status === 'paid' ? 'Mark Pending' : 'Approve & Provision' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
