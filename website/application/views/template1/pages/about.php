        <!-- Breadcrumb -->
        <div class="bg-light py-3 border-bottom">
            <div class="container">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="<?= site_url('home'); ?>">Home</a></li>
                        <li class="breadcrumb-item active">About Us</li>
                    </ol>
                </nav>
            </div>
        </div>

        <section class="py-4 py-md-5">
            <div class="container">
                <div class="row align-items-center mb-4 mb-md-5">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <span class="text-uppercase text-primary fw-bold small">Our Mission & Story</span>
                        <h2 class="fs-3 fs-md-2 fw-bold mb-3">Modern Multipurpose eCommerce Excellence</h2>
                        <p class="text-muted leading-relaxed mb-3">
                            <?= html_escape($site_name); ?> is built as a next-generation multipurpose eCommerce destination providing seamless shopping experiences across fashion, consumer tech, lifestyle, and home goods.
                        </p>
                        <p class="text-muted leading-relaxed">
                            Engineered with clean CodeIgniter 3 MVC architecture and enterprise-grade payment processing with Stripe, Razorpay, and PayU, we deliver unparalleled speed, security, and global convenience.
                        </p>
                    </div>
                    <div class="col-lg-6 text-center">
                        <img src="<?= base_url('assets/images/section/about-us.jpg'); ?>" alt="About <?= html_escape($site_name); ?>" class="img-fluid rounded shadow" onerror="this.src='<?= base_url('assets/images/products/womens/women-1.jpg'); ?>'">
                    </div>
                </div>

                <div class="row g-4 text-center mt-4">
                    <div class="col-md-4">
                        <div class="card border-0 bg-light p-4 h-100">
                            <div class="fs-1 text-primary mb-3"><i class="fa-solid fa-shield-halved"></i></div>
                            <h5 class="fw-bold">100% Secure Checkout</h5>
                            <p class="text-muted small">Every payment is processed through PCI-DSS Level 1 encrypted payment channels via Stripe, Razorpay, and PayU.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light p-4 h-100">
                            <div class="fs-1 text-primary mb-3"><i class="fa-solid fa-truck-fast"></i></div>
                            <h5 class="fw-bold">Fast Reliable Logistics</h5>
                            <p class="text-muted small">Real-time parcel tracking and complimentary free shipping on all eligible orders exceeding $150.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light p-4 h-100">
                            <div class="fs-1 text-primary mb-3"><i class="fa-solid fa-rotate-left"></i></div>
                            <h5 class="fw-bold">Customer Guarantee</h5>
                            <p class="text-muted small">30-day money-back guarantee, dedicated 24/7 customer service, and authenticated high quality goods.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
