        <!-- Stripe Payment Screen -->
        <section class="py-4 py-md-5 bg-light min-vh-100">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card border shadow-sm p-3 p-md-4">
                            <div class="text-center mb-4">
                                <span class="badge bg-primary fs-6 px-3 py-2 mb-2">
                                    <i class="fa-solid fa-shield-halved me-1"></i> Stripe Secure Checkout
                                </span>
                                <h4 class="fw-bold mb-1">Pay for Order #<?= html_escape($order['order_number']); ?></h4>
                                <p class="text-muted">Total Due: <strong class="text-primary fs-4"><?= $currency_symbol . number_format($order['total_amount'], 2); ?></strong></p>
                            </div>

                            <form action="<?= site_url('payment/stripe_confirm/' . $order['order_number']); ?>" method="POST" id="stripe-payment-form">
                                <input type="hidden" name="payment_intent_id" value="<?= html_escape($stripe['intent_id']); ?>">

                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Cardholder Name</label>
                                    <input type="text" class="form-control" value="<?= html_escape($order['customer_name']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Credit / Debit Card Number</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control font-monospace" placeholder="4242 •••• •••• 4242" value="4242 4242 4242 4242" required>
                                        <span class="input-group-text"><i class="fa-solid fa-credit-card"></i></span>
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Expiry Date</label>
                                        <input type="text" class="form-control font-monospace" placeholder="MM / YY" value="12/28" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">CVC / CVV</label>
                                        <input type="password" class="form-control font-monospace" placeholder="123" value="123" maxlength="4" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 py-3" id="pay-btn">
                                    <i class="fa-solid fa-shield-halved me-2"></i> Authorize & Pay <?= $currency_symbol . number_format($order['total_amount'], 2); ?>
                                </button>
                            </form>

                            <div class="text-center mt-3 text-muted small">
                                <i class="fa-solid fa-shield-halved text-success me-1"></i> Powered by Stripe Payments
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
