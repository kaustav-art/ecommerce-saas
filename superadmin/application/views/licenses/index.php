<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="fa-solid fa-key text-warning me-2"></i>Subscriptions &amp; Tenant Licenses
        </h4>
        <p class="text-muted mb-0">Monitor, extend, suspend, or reactivate tenant SaaS subscriptions and license keys.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-dark btn-sm px-3" data-bs-toggle="modal" data-bs-target="#verifyLicenseModal">
            <i class="fa-solid fa-barcode me-1"></i> Verify License Key
        </button>
    </div>
</div>

<!-- Subscription Overview KPIs -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-primary border-4 p-3 bg-white">
            <span class="text-muted small fw-bold text-uppercase">Total Subscriptions</span>
            <h3 class="fw-bold text-dark mt-1 mb-0"><?= $total_count ?></h3>
            <small class="text-muted">Provisioned tenant subscriptions</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-success border-4 p-3 bg-white">
            <span class="text-muted small fw-bold text-uppercase">Active Subscriptions</span>
            <h3 class="fw-bold text-success mt-1 mb-0"><?= $active_count ?></h3>
            <small class="text-muted">Currently active &amp; valid stores</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-danger border-4 p-3 bg-white">
            <span class="text-muted small fw-bold text-uppercase">Expired / Suspended</span>
            <h3 class="fw-bold text-danger mt-1 mb-0"><?= $suspended_count ?></h3>
            <small class="text-muted">Deactivated or expired subscriptions</small>
        </div>
    </div>
</div>

<!-- Subscriptions Table -->
<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="table-responsive">
        <table class="table sa-table align-middle mb-0">
            <thead>
                <tr>
                    <th>License Key</th>
                    <th>Tenant Store</th>
                    <th>Plan</th>
                    <th>Template</th>
                    <th>Billing / Amount</th>
                    <th>Valid Until</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($licenses)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">No subscriptions found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($licenses as $lic): ?>
                        <tr>
                            <td>
                                <div class="sa-code-badge">
                                    <span class="copy-target"><?= htmlspecialchars($lic->license_key ?: 'SAAS-SUB-' . $lic->id) ?></span>
                                    <i class="fa-regular fa-copy sa-copy-btn" title="Copy Key"></i>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($lic->business_name ?: 'Tenant #' . $lic->tenant_id) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($lic->admin_email) ?></small>
                            </td>
                            <td><?= plan_badge($lic->plan_name ?: 'Plan #' . $lic->plan_id) ?></td>
                            <td><?= template_badge($lic->template ?: 'fashion') ?></td>
                            <td>
                                <span class="fw-bold text-success"><?= format_currency($lic->amount) ?></span>
                                <small class="text-muted">/ <?= htmlspecialchars($lic->billing_cycle) ?></small>
                            </td>
                            <td>
                                <div class="small fw-semibold"><?= format_custom_date($lic->end_date, 'd M Y') ?></div>
                            </td>
                            <td><?= status_badge($lic->status) ?></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <form action="<?= superadmin_url('licenses/manage') ?>" method="post" class="d-inline">
                                        <input type="hidden" name="action" value="extend_subscription">
                                        <input type="hidden" name="subscription_id" value="<?= $lic->id ?>">
                                        <input type="hidden" name="tenant_id" value="<?= $lic->tenant_id ?>">
                                        <input type="hidden" name="extend_days" value="30">
                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Extend +30 Days">+30d</button>
                                    </form>
                                    <form action="<?= superadmin_url('licenses/manage') ?>" method="post" class="d-inline">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="subscription_id" value="<?= $lic->id ?>">
                                        <input type="hidden" name="tenant_id" value="<?= $lic->tenant_id ?>">
                                        <input type="hidden" name="status" value="<?= $lic->status === 'active' ? 'suspended' : 'active' ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-<?= $lic->status === 'active' ? 'danger' : 'success' ?>">
                                            <?= $lic->status === 'active' ? 'Suspend' : 'Activate' ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Verify License Key -->
<div class="modal fade" id="verifyLicenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-barcode text-primary me-2"></i>Verify Tenant License Key
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Enter License Key</label>
                    <input type="text" class="form-control font-monospace" id="verifyKeyInput" placeholder="LIC-FASHION-2026-0001">
                </div>
                <button type="button" class="btn btn-primary w-100 fw-bold" id="btnRunVerify">
                    <i class="fa-solid fa-shield-halved me-1"></i> Check License Status
                </button>
                <div id="verifyResult" class="mt-3 d-none"></div>
            </div>
        </div>
    </div>
</div>
