        <!-- Breadcrumb -->
        <div class="bg-light py-3 border-bottom">
            <div class="container">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="<?= site_url('home'); ?>">Home</a></li>
                        <li class="breadcrumb-item active">Contact Us</li>
                    </ol>
                </nav>
            </div>
        </div>

        <section class="py-4 py-md-5">
            <div class="container">
                <div class="row gx-lg-5">
                    <div class="col-lg-5 mb-4 mb-lg-0">
                        <span class="text-uppercase text-primary fw-bold small">Get In Touch</span>
                        <h3 class="fw-bold mb-3 fs-4 fs-md-3">We'd Love to Hear From You</h3>
                        <p class="text-muted mb-4">Have questions about an order, our products, or payment options? Reach out and our support staff will assist you promptly.</p>

                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="fs-4 text-primary"><i class="fa-solid fa-location-dot"></i></div>
                            <div>
                                <h6 class="mb-0 fw-bold">Store Location</h6>
                                <p class="text-muted small mb-0"><?= html_escape($store_settings['site_address'] ?? '123 Commerce Way, New York, NY 10001'); ?></p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="fs-4 text-primary"><i class="fa-solid fa-phone"></i></div>
                            <div>
                                <h6 class="mb-0 fw-bold">Telephone Support</h6>
                                <p class="text-muted small mb-0"><?= html_escape($store_settings['site_phone'] ?? '+1 800 555-0199'); ?></p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3">
                            <div class="fs-4 text-primary"><i class="fa-solid fa-envelope"></i></div>
                            <div>
                                <h6 class="mb-0 fw-bold">Email Inquiries</h6>
                                <p class="text-muted small mb-0"><?= html_escape($store_settings['site_email'] ?? 'support@example.com'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="card border p-3 p-md-5 bg-white shadow-sm rounded-3">
                            <h4 class="fw-bold mb-3 fs-5 fs-md-4">Send a Message</h4>
                            <form action="<?= site_url('contact'); ?>" method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Your Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" placeholder="John Doe" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Subject</label>
                                        <input type="text" name="subject" class="form-control" placeholder="Order inquiry, shipping question, etc.">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Message <span class="text-danger">*</span></label>
                                        <textarea name="message" class="form-control" rows="4" placeholder="How can we assist you today?" required></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary btn-lg w-100">Submit Message</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
