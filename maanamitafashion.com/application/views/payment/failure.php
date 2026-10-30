        <!-- Payment Failure Section -->
        <section class="py-4 py-md-5 bg-light min-vh-100">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm p-3 p-md-5 bg-white text-center">
                            <div class="mb-3 text-danger">
                                <i class="fa-solid fa-circle-xmark fs-1"></i>
                            </div>
                            <h3 class="fw-bold mb-2">Payment Not Completed</h3>
                            <p class="text-muted mb-4">We were unable to process your payment for Order <strong>#<?= html_escape($order['order_number']); ?></strong>.</p>

                            <div class="alert alert-warning text-start small mb-4">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                Common reasons include insufficient funds, 3D secure authentication timeout, or bank network interruption. You may retry payment or select an alternative payment method.
                            </div>

                            <div class="d-flex flex-column gap-2">
                                <?php if ($order['payment_method'] === 'stripe'): ?>
                                    <a href="<?= site_url('payment/stripe/' . $order['order_number']); ?>" class="btn btn-primary btn-lg">
                                        Retry Stripe Payment
                                    </a>
                                <?php elseif ($order['payment_method'] === 'razorpay'): ?>
                                    <a href="<?= site_url('payment/razorpay/' . $order['order_number']); ?>" class="btn btn-info btn-lg text-white">
                                        Retry Razorpay Payment
                                    </a>
                                <?php elseif ($order['payment_method'] === 'payu'): ?>
                                    <a href="<?= site_url('payment/payu/' . $order['order_number']); ?>" class="btn btn-warning btn-lg">
                                        Retry PayU Payment
                                    </a>
                                <?php endif; ?>

                                <a href="<?= site_url('checkout'); ?>" class="btn btn-outline-secondary">
                                    Return to Checkout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
