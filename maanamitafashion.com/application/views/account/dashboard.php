        <!-- breadcrumb -->
        <div class="bg-light py-2 border-bottom">
            <div class="container" style="max-width: 1240px;">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 12px;">
                        <li class="breadcrumb-item"><a href="<?= site_url('home'); ?>" class="text-muted text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Dashboard</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- /breadcrumb -->

        <!-- Account Dashboard -->
        <section class="py-4" style="background-color: #f1f3f6; min-height: 80vh;">
            <div class="container" style="max-width: 1240px;">
                <div class="row g-3">
                    <!-- Left Menu -->
                    <div class="col-lg-3 col-md-4">
                        <?php $this->load->view('account/sidebar'); ?>
                    </div>

                    <!-- Right Content -->
                    <div class="col-lg-9 col-md-8">
                        <div class="card border rounded-1 shadow-sm bg-white p-3 p-md-4 mb-3" style="border-color: #f0f0f0 !important;">
                            <h4 class="fw-bold mb-1 fs-5">Welcome, <?= html_escape($user['first_name']); ?>!</h4>
                            <p class="text-muted small mb-3">From your account dashboard you can view your recent orders, manage your shipping and billing addresses, and track active deliveries.</p>

                            <div class="row g-2 g-md-3 my-1">
                                <div class="col-4">
                                    <div class="border rounded-2 p-2 p-md-3 bg-light text-center">
                                        <h3 class="fw-bold text-primary mb-0 fs-5 fs-md-3"><?= count($orders); ?></h3>
                                        <small class="text-muted" style="font-size: 11px;">Total Orders</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border rounded-2 p-2 p-md-3 bg-light text-center">
                                        <h3 class="fw-bold text-success mb-0 fs-5 fs-md-3"><?= count($addresses); ?></h3>
                                        <small class="text-muted" style="font-size: 11px;">Addresses</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border rounded-2 p-2 p-md-3 bg-light text-center">
                                        <h3 class="fw-bold text-dark mb-0 fs-5 fs-md-3"><?= date('M Y', strtotime($user['created_at'])); ?></h3>
                                        <small class="text-muted" style="font-size: 11px;">Member Since</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Orders -->
                        <div class="card border rounded-1 shadow-sm bg-white p-3 p-md-4" style="border-color: #f0f0f0 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0 text-dark" style="font-size: 16px;">Recent Orders</h5>
                                <a href="<?= site_url('account/orders'); ?>" class="btn btn-outline-primary btn-sm rounded-1 fw-semibold" style="font-size: 12px; border-color: #2874f0; color: #2874f0;">View All</a>
                            </div>

                            <?php if (!empty($orders)): ?>
                                <!-- Desktop Table -->
                                <div class="table-responsive d-none d-md-block">
                                    <table class="table align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Order #</th>
                                                <th>Date</th>
                                                <th>Total</th>
                                                <th>Payment</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($orders, 0, 3) as $ord): ?>
                                                <tr>
                                                    <td><strong>#<?= html_escape($ord['order_number']); ?></strong></td>
                                                    <td class="small text-muted"><?= date('M d, Y', strtotime($ord['created_at'])); ?></td>
                                                    <td><strong><?= $currency_symbol . number_format($ord['total_amount'], 2); ?></strong></td>
                                                    <td><span class="badge bg-success bg-opacity-10 text-success border border-success"><?= ucfirst($ord['payment_status']); ?></span></td>
                                                    <td><span class="badge bg-primary bg-opacity-10 text-primary border border-primary"><?= ucfirst($ord['order_status']); ?></span></td>
                                                    <td>
                                                        <a href="<?= site_url('account/order/' . $ord['order_number']); ?>" class="btn btn-sm btn-outline-dark rounded-1" style="font-size: 12px;">
                                                            View Details
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Mobile List -->
                                <div class="d-md-none">
                                    <div class="d-flex flex-column gap-2">
                                        <?php foreach (array_slice($orders, 0, 3) as $ord): ?>
                                            <div class="border rounded-2 p-3 bg-light bg-opacity-25">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <a href="<?= site_url('account/order/' . $ord['order_number']); ?>" class="fw-bold text-primary text-decoration-none" style="font-size: 13px;">
                                                        #<?= html_escape($ord['order_number']); ?>
                                                    </a>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary text-uppercase" style="font-size: 10px;">
                                                        <?= html_escape($ord['order_status']); ?>
                                                    </span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="text-muted small"><?= date('M d, Y', strtotime($ord['created_at'])); ?></span>
                                                    <span class="fw-bold text-dark"><?= $currency_symbol . number_format($ord['total_amount'], 2); ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success" style="font-size: 10px;">
                                                        Paid: <?= ucfirst($ord['payment_status']); ?>
                                                    </span>
                                                    <a href="<?= site_url('account/order/' . $ord['order_number']); ?>" class="btn btn-sm btn-primary py-1 px-2 rounded-1 text-white text-decoration-none" style="font-size: 11px; background-color: #2874f0; border-color: #2874f0;">
                                                        Details <i class="fa-solid fa-arrow-right ms-1"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-box-open fs-1 text-secondary mb-2"></i>
                                    <p class="small mb-0">No orders yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
