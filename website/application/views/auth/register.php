        <!-- Customer Register -->
        <section class="py-4 py-md-5 bg-light min-vh-100">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-7 col-lg-6">
                        <div class="card border-0 shadow-sm p-3 p-md-5 bg-white rounded-3">
                            <h3 class="fw-bold mb-2 text-center">Create an Account</h3>
                            <p class="text-muted text-center small mb-4">Join <?= html_escape($site_name ?? ($store_settings['site_name'] ?? 'us')); ?> for personalized shopping & fast checkout</p>

                            <div class="card border-primary-subtle bg-light-subtle p-3 mb-4 rounded-3 text-center">
                                <div class="d-flex align-items-center justify-content-center gap-2 mb-1 text-primary fw-bold small">
                                    <i class="fa-solid fa-bolt"></i> Instant OTP Registration (Recommended)
                                </div>
                                <p class="text-muted small mb-2" style="font-size: 11.5px;">Register with your mobile or email using a quick 6-digit OTP verification.</p>
                                <?php $auth_redirect = $this->session->userdata('redirect_url') ?: site_url('home'); ?>
                                <button type="button" class="btn btn-primary btn-sm w-100 fw-bold" onclick="openLoginModal('<?= $auth_redirect; ?>')">
                                    Register with Mobile / Email OTP <i class="fa-solid fa-arrow-right ms-1"></i>
                                </button>
                            </div>

                            <form action="<?= site_url('auth/register'); ?>" method="POST">
                                <div class="row g-2 g-sm-3 mb-3">
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label small fw-bold">First Name <span class="text-danger">*</span></label>
                                        <input type="text" name="first_name" class="form-control" required placeholder="John">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label small fw-bold">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" name="last_name" class="form-control" required placeholder="Doe">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" required placeholder="john@example.com">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Phone Number</label>
                                    <input type="text" name="phone" class="form-control" placeholder="+1 555 0199">
                                </div>
                                <div class="mb-4">
                                    <label class="form-label small fw-bold">Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control" required placeholder="Minimum 6 characters">
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">Register Account</button>
                            </form>

                            <div class="text-center small mt-3">
                                Already have an account? <a href="<?= site_url('login'); ?>" class="fw-bold text-primary">Sign in here</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
