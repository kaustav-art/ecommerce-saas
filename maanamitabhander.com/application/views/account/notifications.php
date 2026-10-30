<!-- breadcrumb -->
        <div class="bg-light py-2 border-bottom">
            <div class="container" style="max-width: 1240px;">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 12px;">
                        <li class="breadcrumb-item"><a href="<?= site_url('home'); ?>" class="text-muted text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url('account/profile'); ?>" class="text-muted text-decoration-none">My Account</a></li>
                        <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Notifications</li>
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
                                <h5 class="fw-bold mb-0 text-dark" style="font-size: 17px;">All Notifications</h5>
                                <span class="badge bg-light text-primary border" style="font-size: 12px;"><?= count($notifications); ?> Messages</span>
                            </div>

                            <?php if (!empty($notifications)): ?>
                                <div class="list-group">
                                    <?php foreach ($notifications as $notif): ?>
                                        <div class="list-group-item list-group-item-action p-3 mb-2 border rounded-1 shadow-none <?= empty($notif['is_read']) ? 'bg-light' : ''; ?>" style="border-color: #e0e0e0 !important;">
                                            <div class="d-flex w-100 justify-content-between align-items-start flex-column flex-sm-row gap-1 mb-2">
                                                <div class="d-flex align-items-center flex-wrap gap-2">
                                                    <i class="fa-solid fa-bell text-primary"></i>
                                                    <strong class="text-dark" style="font-size: 14px;"><?= html_escape($notif['title']); ?></strong>
                                                    <span class="badge bg-light text-muted border text-uppercase" style="font-size: 10px;"><?= html_escape($notif['type']); ?></span>
                                                </div>
                                                <small class="text-muted" style="font-size: 11px;"><?= date('M d, Y h:i A', strtotime($notif['created_at'])); ?></small>
                                            </div>
                                            <p class="mb-2 text-secondary small"><?= nl2br(html_escape($notif['message'])); ?></p>
                                            <?php if (!empty($notif['link'])): ?>
                                                <div>
                                                    <a href="<?= site_url($notif['link']); ?>" class="btn btn-outline-primary btn-sm py-1 px-2 rounded-1 fw-semibold" style="font-size: 11px; border-color: #2874f0; color: #2874f0;">
                                                        View Details <i class="fa-solid fa-arrow-right ms-1"></i>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <div class="mb-3 text-muted">
                                        <i class="fa-regular fa-bell fs-1" style="color: #b0bec5;"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">No notifications right now</h6>
                                    <p class="text-muted small mb-0">We will notify you about your order updates, delivery statuses, and offers here.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /my-account -->
