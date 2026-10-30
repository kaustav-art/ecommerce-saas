<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="fa-solid fa-tags text-warning me-2"></i>SaaS Pricing Plans &amp; Feature Limits
        </h4>
        <p class="text-muted mb-0">Configure subscription pricing, billing intervals, product/order limits, and permitted storefront templates (Fashion, Electronics, Grocery).</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= main_site_url('#pricing') ?>" target="_blank" class="btn btn-outline-primary btn-sm px-3">
            <i class="fa-solid fa-eye me-1"></i> Preview on Landing Page
        </a>
        <button type="button" class="btn btn-warning btn-sm px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#createPlanModal">
            <i class="fa-solid fa-plus me-1"></i> Create New Plan
        </button>
    </div>
</div>

<!-- Super Admin Notice Banner -->
<div class="alert alert-dark border-0 shadow-sm d-flex align-items-center mb-4 text-white">
    <div class="rounded-circle bg-warning text-dark p-2 me-3 flex-shrink-0">
        <i class="fa-solid fa-bolt"></i>
    </div>
    <div class="flex-grow-1 small">
        <strong>Instant Live Sync:</strong> Changes saved below immediately update the public SaaS pricing cards on <code>index.php</code>, registration checkout on <code>subscribe.php</code>, and tenant product/template permissions in <code>admin/</code>.
    </div>
</div>

<!-- Plan Cards Grid -->
<div class="row g-4">
    <?php foreach ($plans as $p): ?>
        <?php
            $features = json_decode($p->features, true);
            $allowed_tpls = json_decode((string)$p->allowed_templates, true);
            if (!is_array($allowed_tpls)) {
                $allowed_tpls = array_filter(array_map('trim', explode(',', (string)$p->allowed_templates)));
            }
            $p_status = !empty($p->is_active) ? 'active' : 'inactive';
        ?>
        <div class="col-lg-4">
            <div class="card h-100 border-2 rounded-3 shadow-sm <?= !empty($p->is_featured) ? 'border-warning' : 'border-light' ?>">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <span class="badge <?= !empty($p->is_featured) ? 'bg-warning text-dark' : 'bg-primary text-white' ?> text-uppercase fw-bold">
                            <?= !empty($p->is_featured) ? 'FEATURED / POPULAR' : strtoupper($p->billing_cycle) ?>
                        </span>
                        <h5 class="fw-bold text-dark mt-1 mb-0"><?= htmlspecialchars($p->name) ?></h5>
                    </div>
                    <span class="badge <?= $p_status === 'active' ? 'bg-success' : 'bg-secondary' ?> text-uppercase"><?= $p_status ?></span>
                </div>

                <div class="card-body p-4">
                    <div class="d-flex align-items-baseline gap-2 mb-2">
                        <h2 class="fw-bold text-dark mb-0"><?= format_currency($p->price) ?></h2>
                        <span class="text-muted small">/ <?= htmlspecialchars($p->billing_cycle) ?> (<?= (int)$p->duration_days ?> days)</span>
                    </div>
                    <p class="text-muted small mb-3"><?= htmlspecialchars($p->description) ?></p>

                    <div class="p-2 rounded bg-light border small mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Product Limit:</span>
                            <strong class="text-dark"><?= (int)$p->product_limit === -1 ? 'Unlimited' : number_format((int)$p->product_limit) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Order Limit:</span>
                            <strong class="text-dark"><?= (int)$p->order_limit === -1 ? 'Unlimited' : number_format((int)$p->order_limit) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Templates:</span>
                            <div class="d-flex gap-1 flex-wrap justify-content-end">
                                <?php foreach ($allowed_tpls as $tk): ?>
                                    <?= template_badge($tk) ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-3 mb-2">
                        <span class="small fw-bold text-dark text-uppercase d-block mb-2">Included Features:</span>
                        <?php if (!empty($features)): ?>
                            <ul class="list-unstyled small mb-0 lh-lg">
                                <?php foreach ($features as $f): ?>
                                    <li class="text-secondary d-flex align-items-start gap-2 mb-1">
                                        <i class="fa-solid fa-circle-check text-success mt-1"></i>
                                        <span><?= htmlspecialchars($f) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-footer bg-light border-top p-3 d-flex gap-2">
                    <button type="button" class="btn btn-warning btn-sm flex-grow-1 fw-bold" data-bs-toggle="modal" data-bs-target="#editPlanModal<?= $p->id ?>">
                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit Plan &amp; Limits
                    </button>
                    <form action="<?= superadmin_url('plans') ?>" method="post" onsubmit="return confirm('Delete plan <?= htmlspecialchars($p->name) ?>?');">
                        <input type="hidden" name="action" value="delete_plan">
                        <input type="hidden" name="plan_id" value="<?= $p->id ?>">
                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete Plan">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal for this Plan -->
        <div class="modal fade" id="editPlanModal<?= $p->id ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form action="<?= superadmin_url('plans') ?>" method="post">
                        <input type="hidden" name="action" value="save_plan">
                        <input type="hidden" name="plan_id" value="<?= $p->id ?>">

                        <div class="modal-header bg-dark text-white">
                            <h5 class="modal-title fw-bold">
                                <i class="fa-solid fa-pen-to-square text-warning me-2"></i>Edit <?= htmlspecialchars($p->name) ?>
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Plan Name *</label>
                                    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($p->name) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Plan Slug</label>
                                    <input type="text" class="form-control font-monospace" name="slug" value="<?= htmlspecialchars($p->slug) ?>">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Subscription Price *</label>
                                    <input type="number" step="0.01" class="form-control" name="price" value="<?= $p->price ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Billing Cycle</label>
                                    <select class="form-select" name="billing_cycle">
                                        <option value="monthly" <?= $p->billing_cycle === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                                        <option value="yearly" <?= $p->billing_cycle === 'yearly' ? 'selected' : '' ?>>Yearly</option>
                                        <option value="lifetime" <?= $p->billing_cycle === 'lifetime' ? 'selected' : '' ?>>Lifetime</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Duration (Days)</label>
                                    <input type="number" class="form-control" name="duration_days" value="<?= (int)$p->duration_days ?>" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Product Limit (-1 = Unlimited)</label>
                                    <input type="number" class="form-control" name="product_limit" value="<?= (int)$p->product_limit ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Order Limit (-1 = Unlimited)</label>
                                    <input type="number" class="form-control" name="order_limit" value="<?= (int)$p->order_limit ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Storage Limit (MB)</label>
                                    <input type="number" class="form-control" name="storage_limit_mb" value="<?= (int)$p->storage_limit_mb ?>" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-bold d-block">Permitted Storefront Templates</label>
                                    <div class="d-flex flex-wrap gap-3 p-2 border rounded bg-light">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="allowed_templates[]" value="fashion" id="tpl_f_<?= $p->id ?>" <?= in_array('fashion', $allowed_tpls) ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-semibold" for="tpl_f_<?= $p->id ?>">Template 1: Fashion Store</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="allowed_templates[]" value="electronics" id="tpl_e_<?= $p->id ?>" <?= in_array('electronics', $allowed_tpls) ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-semibold" for="tpl_e_<?= $p->id ?>">Template 2: Electronics Store</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="allowed_templates[]" value="grocery" id="tpl_g_<?= $p->id ?>" <?= in_array('grocery', $allowed_tpls) ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-semibold" for="tpl_g_<?= $p->id ?>">Template 3: Grocery Store</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="active" <?= $p_status === 'active' ? 'selected' : '' ?>>Active (Visible)</option>
                                        <option value="inactive" <?= $p_status === 'inactive' ? 'selected' : '' ?>>Inactive (Hidden)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Display Order</label>
                                    <input type="number" class="form-control" name="sort_order" value="<?= (int)$p->sort_order ?>">
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="feat_<?= $p->id ?>" <?= !empty($p->is_featured) ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-bold text-warning" for="feat_<?= $p->id ?>">Highlight as Popular Plan</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-bold">Plan Description</label>
                                    <textarea class="form-control" name="description" rows="2"><?= htmlspecialchars($p->description) ?></textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-bold">Features List (One feature per line)</label>
                                    <?php $f_text = !empty($features) ? implode("\n", $features) : ''; ?>
                                    <textarea class="form-control font-monospace small" name="features" rows="5"><?= htmlspecialchars($f_text) ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning btn-sm fw-bold px-3">
                                <i class="fa-solid fa-save me-1"></i> Save Plan Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Create Plan Modal -->
<div class="modal fade" id="createPlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= superadmin_url('plans') ?>" method="post">
                <input type="hidden" name="action" value="save_plan">
                <input type="hidden" name="plan_id" value="0">

                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-plus-circle text-warning me-2"></i>Create New SaaS Subscription Plan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Plan Name *</label>
                            <input type="text" class="form-control" name="name" placeholder="e.g. Scale Plus Plan" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Plan Slug</label>
                            <input type="text" class="form-control font-monospace" name="slug" placeholder="scale-plus">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Subscription Price *</label>
                            <input type="number" step="0.01" class="form-control" name="price" value="49.00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Billing Cycle</label>
                            <select class="form-select" name="billing_cycle">
                                <option value="monthly" selected>Monthly</option>
                                <option value="yearly">Yearly</option>
                                <option value="lifetime">Lifetime</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Duration (Days)</label>
                            <input type="number" class="form-control" name="duration_days" value="30" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Product Limit (-1 = Unlimited)</label>
                            <input type="number" class="form-control" name="product_limit" value="500" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Order Limit (-1 = Unlimited)</label>
                            <input type="number" class="form-control" name="order_limit" value="-1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Storage Limit (MB)</label>
                            <input type="number" class="form-control" name="storage_limit_mb" value="2048" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold d-block">Permitted Storefront Templates</label>
                            <div class="d-flex flex-wrap gap-3 p-2 border rounded bg-light">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="allowed_templates[]" value="fashion" id="new_tpl_f" checked>
                                    <label class="form-check-label fw-semibold" for="new_tpl_f">Template 1: Fashion Store</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="allowed_templates[]" value="electronics" id="new_tpl_e" checked>
                                    <label class="form-check-label fw-semibold" for="new_tpl_e">Template 2: Electronics Store</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="allowed_templates[]" value="grocery" id="new_tpl_g" checked>
                                    <label class="form-check-label fw-semibold" for="new_tpl_g">Template 3: Grocery Store</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Status</label>
                            <select class="form-select" name="status">
                                <option value="active" selected>Active (Visible)</option>
                                <option value="inactive">Inactive (Hidden)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Display Order</label>
                            <input type="number" class="form-control" name="sort_order" value="4">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="new_feat">
                                <label class="form-check-label fw-bold text-warning" for="new_feat">Highlight as Popular Plan</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Plan Description</label>
                            <textarea class="form-control" name="description" rows="2" placeholder="Ideal for growing multi-category stores..."></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Features List (One feature per line)</label>
                            <textarea class="form-control font-monospace small" name="features" rows="4" placeholder="Up to 500 Products&#10;All 3 Storefront Templates&#10;Unlimited Orders"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm fw-bold px-3">
                        <i class="fa-solid fa-plus me-1"></i> Create Plan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
