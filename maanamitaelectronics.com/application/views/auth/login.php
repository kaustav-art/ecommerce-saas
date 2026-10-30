        <!-- Customer Login -->
        <section class="py-4 py-md-5 bg-light min-vh-100">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-5">
                        <div class="card border-0 shadow-sm p-3 p-md-5 bg-white rounded-3">
                            <h3 class="fw-bold mb-2 text-center">Customer Sign In</h3>
                            <p class="text-muted text-center small mb-4">Access your orders, addresses, and wishlist</p>

                            <div class="card border-primary-subtle bg-light-subtle p-3 mb-4 rounded-3 text-center">
                                <div class="d-flex align-items-center justify-content-center gap-2 mb-1 text-primary fw-bold small">
                                    <i class="fa-solid fa-bolt"></i> Fast OTP Sign In / Register
                                </div>
                                <p class="text-muted small mb-2" style="font-size: 11.5px;">Sign in or create account without password using OTP.</p>
                                <?php $auth_redirect = $this->session->userdata('redirect_url') ?: site_url('home'); ?>
                                <button type="button" class="btn btn-outline-primary btn-sm w-100 fw-bold" onclick="openLoginModal('<?= $auth_redirect; ?>')">
                                    Continue with Mobile / Email OTP <i class="fa-solid fa-arrow-right ms-1"></i>
                                </button>
                            </div>

                            <form action="<?= site_url('auth/login'); ?>" method="POST">
                                <input type="hidden" name="redirect_to" value="<?= html_escape($auth_redirect); ?>">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Email Address</label>
                                    <input type="email" name="email" class="form-control" value="john@example.com" placeholder="name@example.com" required autofocus>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Password</label>
                                    <input type="password" name="password" class="form-control" value="user123" placeholder="••••••••" required>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-4 small">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember" checked>
                                        <label class="form-check-label" for="remember">Remember me</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">Sign In</button>
                            </form>

                            <div class="alert alert-light border small">
                                <div class="fw-bold"><i class="fa-solid fa-user me-1"></i> Preloaded Customer Login:</div>
                                <code>john@example.com</code> / <code>user123</code>
                            </div>

                            <div class="text-center small mt-3">
                                Don't have an account? <a href="<?= site_url('register'); ?>" class="fw-bold text-primary">Register here</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
