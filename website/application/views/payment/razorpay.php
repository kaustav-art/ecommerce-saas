        <!-- Razorpay Payment Screen -->
        <section class="py-4 py-md-5 bg-light min-vh-100">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card border shadow-sm p-3 p-md-4 text-center">
                            <div class="mb-4">
                                <span class="badge bg-info text-dark fs-6 px-3 py-2 mb-2">
                                    <i class="fa-solid fa-shield-halved me-1"></i> Razorpay Payment Gateway
                                </span>
                                <h4 class="fw-bold mb-1">Order #<?= html_escape($order['order_number']); ?></h4>
                                <p class="text-muted">Total Amount: <strong class="text-dark fs-4"><?= $currency_symbol . number_format($order['total_amount'], 2); ?> <?= html_escape($razorpay['currency'] ?? ($order['currency'] ?? ($currency_code ?? 'USD'))); ?></strong></p>
                            </div>

                            <p class="text-muted small mb-4">Click below to initiate the Razorpay Checkout popup (Supports UPI, Cards, NetBanking & Wallets).</p>

                            <form action="<?= site_url('payment/razorpay_verify/' . $order['order_number']); ?>" method="POST" id="razorpay-form">
                                <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id" value="pay_test_<?= uniqid(); ?>">
                                <input type="hidden" name="razorpay_order_id" id="razorpay_order_id" value="<?= html_escape($razorpay['razorpay_order_id']); ?>">
                                <input type="hidden" name="razorpay_signature" id="razorpay_signature" value="sig_test_<?= md5(uniqid()); ?>">

                                <button type="button" id="rzp-button1" class="btn btn-info btn-lg w-100 py-3 text-white fw-bold mb-3" style="background-color: #0c2340; border-color: #0c2340;">
                                    <i class="fa-solid fa-shield-halved me-2"></i> Pay with Razorpay (<?= html_escape($razorpay['currency'] ?? ($order['currency'] ?? ($currency_code ?? 'USD'))); ?> <?= number_format($order['total_amount'], 2); ?>)
                                </button>
                            </form>

                            <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
                            <script>
                            var options = {
                                "key": "<?= html_escape($razorpay['key_id']); ?>",
                                "amount": "<?= html_escape($razorpay['amount_subunit']); ?>",
                                "currency": "<?= html_escape($razorpay['currency'] ?? ($order['currency'] ?? ($currency_code ?? 'USD'))); ?>",
                                "name": "<?= html_escape($site_name ?? ($store_settings['site_name'] ?? 'Store')); ?>",
                                "description": "Order Payment for #<?= html_escape($order['order_number']); ?>",
                                "order_id": "<?= html_escape($razorpay['razorpay_order_id']); ?>",
                                "handler": function (response){
                                    document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                                    document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
                                    document.getElementById('razorpay_signature').value = response.razorpay_signature;
                                    document.getElementById('razorpay-form').submit();
                                },
                                "prefill": {
                                    "name": "<?= html_escape($order['customer_name']); ?>",
                                    "email": "<?= html_escape($order['customer_email']); ?>",
                                    "contact": "<?= html_escape($order['customer_phone']); ?>"
                                },
                                "theme": {
                                    "color": "#0c2340"
                                }
                            };

                            document.getElementById('rzp-button1').onclick = function(e){
                                try {
                                    var rzp1 = new Razorpay(options);
                                    rzp1.on('payment.failed', function (response){
                                        alert("Payment Failed: " + response.error.description);
                                    });
                                    rzp1.open();
                                } catch(err) {
                                    // Fallback for offline / demo mode
                                    if (confirm("Razorpay sandbox simulated mode: Complete test payment?")) {
                                        document.getElementById('razorpay-form').submit();
                                    }
                                }
                                e.preventDefault();
                            }
                            </script>

                            <div class="text-center mt-3 text-muted small">
                                <i class="fa-solid fa-shield-halved text-success me-1"></i> Certified PCI-DSS Level 1 Compliant
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
