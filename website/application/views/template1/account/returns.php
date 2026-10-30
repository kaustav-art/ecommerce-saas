        <!-- breadcrumb -->
        <div class="bg-light py-2 border-bottom">
            <div class="container" style="max-width: 1240px;">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 12px;">
                        <li class="breadcrumb-item"><a href="<?= site_url('home'); ?>" class="text-muted text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url('account/profile'); ?>" class="text-muted text-decoration-none">My Account</a></li>
                        <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Returns & Refunds</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- /breadcrumb -->

        <!-- my-account -->
        <section class="py-4" style="background-color: #f1f3f6; min-height: 80vh;">
            <div class="container" style="max-width: 1240px;">
                <div class="row g-3">
                    <!-- Left Sidebar -->
                    <div class="col-lg-3 col-md-4">
                        <?php $this->load->view('account/sidebar'); ?>
                    </div>

                    <!-- Right Content -->
                    <div class="col-lg-9 col-md-8">
                        <div class="card border rounded-1 shadow-sm bg-white p-3 p-md-4" style="border-color: #f0f0f0 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold mb-0 text-dark" style="font-size: 17px;">Returns & Refund Requests</h5>
                                <a href="<?= site_url('account/orders'); ?>" class="btn btn-outline-primary btn-sm rounded-1 fw-semibold" style="font-size: 13px; border-color: #2874f0; color: #2874f0;">
                                    <i class="fa-solid fa-box-archive me-1"></i> View Orders
                                </a>
                            </div>

                            <?php if (!empty($returns)): ?>
                                <!-- Desktop Table View -->
                                <div class="table-responsive d-none d-md-block">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Request #</th>
                                                <th>Order #</th>
                                                <th>Type</th>
                                                <th>Reason</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($returns as $ret): ?>
                                                <tr>
                                                    <td><strong>#RET-<?= str_pad($ret['id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                                                    <td>
                                                        <a href="<?= site_url('account/order/' . $ret['order_number']); ?>" class="fw-bold text-primary text-decoration-none">
                                                            #<?= html_escape($ret['order_number']); ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark border text-uppercase" style="font-size: 11px;">
                                                            <?= html_escape($ret['type']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate" style="max-width: 200px;" title="<?= html_escape($ret['reason']); ?>">
                                                            <?= html_escape($ret['reason']); ?>
                                                        </div>
                                                        <?php if (!empty($ret['admin_notes'])): ?>
                                                            <div class="small text-info mt-1"><i class="fa-solid fa-comment-dots me-1"></i> <strong>Admin:</strong> <?= html_escape($ret['admin_notes']); ?></div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><strong class="text-dark"><?= $currency_symbol . number_format($ret['amount'], 2); ?></strong></td>
                                                    <td>
                                                        <?php
                                                            $badge_class = 'secondary';
                                                            if ($ret['status'] === 'approved') $badge_class = 'info';
                                                            elseif ($ret['status'] === 'refunded' || $ret['status'] === 'completed') $badge_class = 'success';
                                                            elseif ($ret['status'] === 'rejected') $badge_class = 'danger';
                                                            elseif ($ret['status'] === 'pending') $badge_class = 'warning';
                                                        ?>
                                                        <span class="badge bg-<?= $badge_class; ?> text-uppercase" style="font-size: 11px;">
                                                            <?= html_escape($ret['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="small text-muted"><?= date('M d, Y', strtotime($ret['created_at'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Mobile Card List View -->
                                <div class="d-md-none">
                                    <div class="d-flex flex-column gap-3">
                                        <?php foreach ($returns as $ret): ?>
                                            <?php
                                                $badge_class = 'secondary';
                                                if ($ret['status'] === 'approved') $badge_class = 'info';
                                                elseif ($ret['status'] === 'refunded' || $ret['status'] === 'completed') $badge_class = 'success';
                                                elseif ($ret['status'] === 'rejected') $badge_class = 'danger';
                                                elseif ($ret['status'] === 'pending') $badge_class = 'warning';
                                            ?>
                                            <div class="border rounded-2 p-3 bg-light bg-opacity-25 shadow-sm">
                                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                                    <span class="fw-bold text-dark font-monospace" style="font-size: 13px;">#RET-<?= str_pad($ret['id'], 4, '0', STR_PAD_LEFT); ?></span>
                                                    <span class="badge bg-<?= $badge_class; ?> text-uppercase" style="font-size: 10px;">
                                                        <?= html_escape($ret['status']); ?>
                                                    </span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div>
                                                        <span class="text-muted small">Order: </span>
                                                        <a href="<?= site_url('account/order/' . $ret['order_number']); ?>" class="fw-bold text-primary text-decoration-none small">
                                                            #<?= html_escape($ret['order_number']); ?>
                                                        </a>
                                                    </div>
                                                    <span class="badge bg-light text-dark border text-uppercase" style="font-size: 10px;">
                                                        <?= html_escape($ret['type']); ?>
                                                    </span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-baseline mb-2">
                                                    <span class="text-muted small">Refund Amount:</span>
                                                    <span class="fw-bold text-dark fs-6"><?= $currency_symbol . number_format($ret['amount'], 2); ?></span>
                                                </div>
                                                <div class="small text-muted bg-white p-2 rounded border mb-2">
                                                    <strong>Reason:</strong> <?= html_escape($ret['reason']); ?>
                                                    <?php if (!empty($ret['admin_notes'])): ?>
                                                        <div class="text-info mt-1 pt-1 border-top"><i class="fa-solid fa-comment-dots me-1"></i> <strong>Admin:</strong> <?= html_escape($ret['admin_notes']); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-end text-muted" style="font-size: 11px;">
                                                    Requested on <?= date('M d, Y', strtotime($ret['created_at'])); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <div class="mb-3 text-muted">
                                        <i class="fa-solid fa-rotate-left fs-1" style="color: #b0bec5;"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">No return requests found</h6>
                                    <p class="text-muted small mb-3">You currently do not have any open return, refund, or replacement requests.</p>
                                    <a href="<?= site_url('account/orders'); ?>" class="btn btn-primary btn-sm rounded-1 px-3" style="background-color: #2874f0; border-color: #2874f0;">Go to Orders</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /my-account -->
