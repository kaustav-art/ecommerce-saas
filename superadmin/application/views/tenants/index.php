<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="fa-solid fa-store text-warning me-2"></i>SaaS Multi-Tenant Store Management
        </h4>
        <p class="text-muted mb-0">Manage registered tenant ecommerce stores, active storefront templates, subscription plans, product counts, and status.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <button type="button" class="btn btn-warning btn-sm px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#createTenantModal">
            <i class="fa-solid fa-plus-circle me-1"></i> Provision New Tenant Store
        </button>
        <a href="<?= tenant_admin_url() ?>" target="_blank" class="btn btn-outline-primary btn-sm px-3 fw-semibold">
            <i class="fa-solid fa-user-shield me-1"></i> Tenant Admin Login
        </a>
        <a href="<?= tenant_site_url() ?>" target="_blank" class="btn btn-outline-success btn-sm px-3 fw-semibold">
            <i class="fa-solid fa-globe me-1"></i> Default Fashion Store
        </a>
    </div>
</div>

<!-- SaaS Multi-Tenant Provisioned Instances Table -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">
            <i class="fa-solid fa-server text-warning me-2"></i>Provisioned SaaS Tenant Stores (<?= count($saas_tenants) ?> Total)
        </h6>
        <span class="badge bg-warning text-dark fw-bold">Multi-Tenant Scoped (tenant_id)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase fw-bold text-muted">
                    <tr>
                        <th>ID / Code</th>
                        <th>Business &amp; Store Slug</th>
                        <th>Plan</th>
                        <th>Active Template</th>
                        <th>Products / Orders</th>
                        <th>Owner Account</th>
                        <th>Subscription Expiry</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($saas_tenants)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <strong>No tenant stores found.</strong>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($saas_tenants as $t): ?>
                            <?php
                                $store_url = !empty($t->website_url) ? $t->website_url : main_site_url('website/s/' . $t->slug);
                                if ((int)$t->id === 1) {
                                    $store_url = main_site_url('website/home');
                                }
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark font-monospace">#<?= (int)$t->id ?></div>
                                    <small class="text-muted font-monospace"><?= htmlspecialchars($t->tenant_code) ?></small>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($t->business_name) ?></div>
                                    <a href="<?= htmlspecialchars($store_url) ?>" target="_blank" class="small text-primary text-decoration-none font-monospace">
                                        /website/s/<?= htmlspecialchars($t->slug) ?> <i class="fa-solid fa-arrow-up-right-from-square ms-1" style="font-size:10px;"></i>
                                    </a>
                                </td>
                                <td><?= plan_badge($t->plan_name ?: 'Plan #' . $t->plan_id) ?></td>
                                <td><?= template_badge($t->template) ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="fa-solid fa-box text-primary me-1"></i><?= (int)$t->products_count ?>
                                        <?= (isset($t->product_limit) && (int)$t->product_limit > 0) ? '/ ' . (int)$t->product_limit : '' ?>
                                    </span>
                                    <span class="badge bg-light text-dark border">
                                        <i class="fa-solid fa-bag-shopping text-success me-1"></i><?= (int)$t->orders_count ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark"><?= htmlspecialchars($t->admin_name) ?></div>
                                    <small class="text-muted font-monospace"><?= htmlspecialchars($t->admin_email) ?></small>
                                </td>
                                <td>
                                    <div class="small fw-semibold"><?= format_custom_date($t->subscription_expires_at, 'd M Y') ?></div>
                                    <?= status_badge($t->subscription_status ?: $t->status) ?>
                                </td>
                                <td><?= status_badge($t->status) ?></td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= htmlspecialchars($store_url) ?>" target="_blank" class="btn btn-outline-primary" title="Visit Live Storefront">
                                            <i class="fa-solid fa-store"></i> Store
                                        </a>
                                        <a href="<?= htmlspecialchars(superadmin_impersonate_url($t->id)) ?>" target="_blank" class="btn btn-outline-dark" title="One-Click Login as Tenant Admin">
                                            <i class="fa-solid fa-key text-warning"></i> Login Admin
                                        </a>
                                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editTenantModal<?= $t->id ?>" title="Edit Tenant">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <?php if ((int)$t->id !== 1): ?>
                                            <a href="<?= superadmin_url('tenants/toggle_status/' . $t->id) ?>" class="btn btn-outline-<?= $t->status === 'active' ? 'danger' : 'success' ?>" title="<?= $t->status === 'active' ? 'Suspend Tenant' : 'Activate Tenant' ?>">
                                                <i class="fa-solid fa-power-off"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Tenant Modal -->
                            <div class="modal fade" id="editTenantModal<?= $t->id ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <form action="<?= superadmin_url('tenants') ?>" method="post">
                                            <input type="hidden" name="action" value="edit_tenant">
                                            <input type="hidden" name="tenant_id" value="<?= $t->id ?>">
                                            <div class="modal-header bg-dark text-white">
                                                <h5 class="modal-title fw-bold">
                                                    <i class="fa-solid fa-store text-warning me-2"></i>Edit Tenant #<?= (int)$t->id ?> (<?= htmlspecialchars($t->business_name) ?>)
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Business / Store Name *</label>
                                                    <input type="text" class="form-control" name="business_name" value="<?= htmlspecialchars($t->business_name) ?>" required>
                                                </div>
                                                <div class="row g-3 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label small fw-bold">Subscription Plan</label>
                                                        <select class="form-select" name="plan_id">
                                                            <?php foreach ($plans as $p): ?>
                                                                <option value="<?= $p->id ?>" <?= (int)$t->plan_id === (int)$p->id ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($p->name) ?> (<?= format_currency($p->price) ?>)
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small fw-bold">Active Storefront Template</label>
                                                        <select class="form-select" name="template">
                                                            <option value="fashion" <?= $t->template === 'fashion' ? 'selected' : '' ?>>Template 1: Fashion Store</option>
                                                            <option value="electronics" <?= $t->template === 'electronics' ? 'selected' : '' ?>>Template 2: Electronics Store</option>
                                                            <option value="grocery" <?= $t->template === 'grocery' ? 'selected' : '' ?>>Template 3: Grocery Store</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row g-3 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label small fw-bold">Store Status</label>
                                                        <select class="form-select" name="status">
                                                            <option value="active" <?= $t->status === 'active' ? 'selected' : '' ?>>Active</option>
                                                            <option value="suspended" <?= $t->status === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small fw-bold">Subscription Status</label>
                                                        <select class="form-select" name="subscription_status">
                                                            <option value="active" <?= $t->subscription_status === 'active' ? 'selected' : '' ?>>Active</option>
                                                            <option value="expired" <?= $t->subscription_status === 'expired' ? 'selected' : '' ?>>Expired</option>
                                                            <option value="suspended" <?= $t->subscription_status === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mb-0">
                                                    <label class="form-label small fw-bold">Subscription Expiry Date</label>
                                                    <input type="date" class="form-control" name="subscription_expires_at" value="<?= !empty($t->subscription_expires_at) ? date('Y-m-d', strtotime($t->subscription_expires_at)) : date('Y-m-d', strtotime('+30 days')) ?>">
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-warning btn-sm fw-bold px-3">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Provision New Tenant Modal -->
<div class="modal fade" id="createTenantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= superadmin_url('tenants') ?>" method="post">
                <input type="hidden" name="action" value="create_tenant">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-plus-circle text-warning me-2"></i>Provision New Tenant Ecommerce Store
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Business / Store Name *</label>
                            <input type="text" class="form-control" name="business_name" placeholder="e.g. Urban Trend Boutique" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Store URL Slug (optional)</label>
                            <input type="text" class="form-control font-monospace" name="store_slug" placeholder="urban-trend-boutique">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Owner Full Name *</label>
                            <input type="text" class="form-control" name="admin_name" placeholder="Jane Doe" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Owner Login Email *</label>
                            <input type="email" class="form-control" name="admin_email" placeholder="owner@urbantrend.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Owner Phone Number</label>
                            <input type="text" class="form-control" name="admin_phone" placeholder="+1 555 234 5678">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Initial Admin Password *</label>
                            <input type="text" class="form-control font-monospace" name="admin_password" value="admin123" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Subscription Plan *</label>
                            <select class="form-select" name="plan_id" required>
                                <?php foreach ($plans as $p): ?>
                                    <option value="<?= $p->id ?>" <?= !empty($p->is_featured) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p->name) ?> (<?= format_currency($p->price) ?> / <?= htmlspecialchars($p->billing_cycle) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Storefront Template *</label>
                            <select class="form-select" name="template" required>
                                <option value="fashion">Template 1: Fashion Storefront</option>
                                <option value="electronics">Template 2: Electronics Storefront</option>
                                <option value="grocery">Template 3: Grocery Storefront</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm fw-bold px-4">
                        <i class="fa-solid fa-rocket me-1"></i> Provision Store Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
