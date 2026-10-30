        <!-- Breadcrumb -->
        <div class="bg-light py-3 border-bottom">
            <div class="container">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="<?= site_url('home'); ?>">Home</a></li>
                        <li class="breadcrumb-item active">Frequently Asked Questions</li>
                    </ol>
                </nav>
            </div>
        </div>

        <section class="py-4 py-md-5">
            <div class="container">
                <div class="text-center max-w-700 mx-auto mb-4 mb-md-5">
                    <h2 class="fw-bold mb-2 fs-3 fs-md-2">Frequently Asked Questions</h2>
                    <p class="text-muted">Find quick answers to common questions regarding ordering, payment processing, shipping times, and returns.</p>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="accordion" id="faqAccordion">
                            <div class="accordion-item mb-3 border">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                        What payment methods do you accept?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-muted">
                                        We support multiple secure international and regional payment methods: <strong>Stripe</strong> (Credit / Debit cards including Visa, Mastercard, American Express), <strong>Razorpay</strong> (UPI, Indian NetBanking, Cards, and Wallets), <strong>PayU</strong>, as well as <strong>Cash on Delivery (COD)</strong> for select destinations.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item mb-3 border">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                        How can I track my order?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-muted">
                                        You can track any order directly via our <a href="<?= site_url('order/track'); ?>">Order Tracking page</a> using your Order Reference Number (e.g. <code>ORD-2026-1001</code>) and your billing email address. Logged-in customers can also inspect real-time statuses under their account order history.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item mb-3 border">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                        How long does delivery take?
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-muted">
                                        Orders are processed and dispatched within 24 to 48 business hours. Standard domestic delivery typically takes 3-5 business days. International express shipping delivers within 5-8 business days. Orders over $150 qualify for complimentary Free Shipping!
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item mb-3 border">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                        What is your return and refund policy?
                                    </button>
                                </h2>
                                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-muted">
                                        We offer a 30-day risk-free return policy. If you are not completely satisfied with your items, return them in original packaging and tags for a full refund or exchange.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
