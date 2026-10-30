<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="fa-solid fa-box-archive text-warning me-2"></i>Software Packages &amp; Deliveries
        </h4>
        <p class="text-muted mb-0">Manage customer zip package generation, pre-configured license injection, and download audit logs.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-server text-primary me-2"></i>Packaging Engine Diagnostics
                </h6>
            </div>
            <div class="card-body p-4">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                        <span class="text-muted">PHP ZipArchive Extension:</span>
                        <?php if ($has_zip): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fa-solid fa-check me-1"></i>Active &amp; Ready</span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fa-solid fa-times me-1"></i>Missing Extension</span>
                        <?php endif; ?>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                        <span class="text-muted">Script Release Version:</span>
                        <strong class="text-dark">Luxe Salon &amp; Spa v2.0 Enterprise</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                        <span class="text-muted">Bundled Template Sets:</span>
                        <span>Template 1 + Template 2</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                        <span class="text-muted">Dynamic Injectors:</span>
                        <span class="badge bg-light text-dark border">LICENSE_KEY.txt + install_config.json</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                        <span class="text-muted">Total Lifetime Deliveries:</span>
                        <span class="fw-bold text-success fs-6"><?= $total_downloads ?> Package Downloads</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-link text-warning me-2"></i>Generate Secure Customer Download Link
                </h6>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small">Select an existing verified customer order to generate a one-click authenticated package download link:</p>
                <form action="<?= superadmin_url('packages/generate_link') ?>" method="post">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select Order</label>
                        <select class="form-select" name="order_id" required>
                            <?php foreach ($all_orders as $o): ?>
                                <option value="<?= $o->id ?>">
                                    <?= htmlspecialchars($o->order_number) ?> &bull; <?= htmlspecialchars($o->customer_name) ?> (<?= $o->plan_code ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-warning fw-bold px-4">
                        <i class="fa-solid fa-bolt me-1"></i> Retrieve Download Link
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Downloaded Orders Audit Log -->
<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="card-header bg-white py-3 border-bottom">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-download text-success me-2"></i>Download Activity Ledger
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table sa-table align-middle mb-0">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer Name</th>
                    <th>Customer Email</th>
                    <th>Plan Edition</th>
                    <th>Download Count</th>
                    <th>License Key</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders_with_downloads)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No downloads recorded yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders_with_downloads as $d): ?>
                        <tr>
                            <td><strong class="font-monospace text-dark"><?= htmlspecialchars($d->order_number) ?></strong></td>
                            <td><?= htmlspecialchars($d->customer_name) ?></td>
                            <td><small class="text-muted"><?= htmlspecialchars($d->customer_email) ?></small></td>
                            <td><?= plan_badge($d->plan_code) ?></td>
                            <td><span class="badge bg-success px-2 py-1"><?= $d->download_count ?> downloads</span></td>
                            <td>
                                <div class="sa-code-badge">
                                    <span class="copy-target"><?= htmlspecialchars($d->license_key) ?></span>
                                    <i class="fa-regular fa-copy sa-copy-btn"></i>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
