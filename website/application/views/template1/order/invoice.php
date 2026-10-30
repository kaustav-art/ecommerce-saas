<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= html_escape($title ?? 'Invoice'); ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #333;
        }
        .invoice-card {
            background: #fff;
            max-width: 860px;
            margin: 30px auto;
            padding: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        .invoice-header {
            border-bottom: 2px solid #f1f3f5;
            padding-bottom: 25px;
            margin-bottom: 25px;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: 800;
            color: #1a1a1a;
            letter-spacing: -0.5px;
        }
        .invoice-table th {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            font-weight: 700;
        }
        @media (max-width: 767px) {
            .invoice-card {
                padding: 18px;
                margin: 15px auto;
            }
            .invoice-title {
                font-size: 22px;
            }
        }
        @media print {
            body {
                background: #fff;
            }
            .invoice-card {
                box-shadow: none;
                border: none;
                margin: 0;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Action bar for user -->
    <div class="d-flex justify-content-between align-items-center my-3 no-print" style="max-width: 860px; margin: 0 auto;">
        <a href="<?= site_url('account/orders'); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Orders
        </a>
        <button onclick="window.print();" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-print me-1"></i> Print Invoice
        </button>
    </div>

    <div class="invoice-card">
        <!-- Invoice Header -->
        <div class="invoice-header d-flex justify-content-between align-items-start flex-column flex-sm-row gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1 fs-4 fs-md-3"><?= html_escape($site_name ?? ($store_settings['site_name'] ?? 'Store')); ?></h3>
                <p class="text-muted small mb-0"><?= html_escape($store_settings['site_address'] ?? '123 Commerce St'); ?></p>
                <p class="text-muted small mb-0">Phone: <?= html_escape($store_settings['site_phone'] ?? '+1 800 555-0199'); ?></p>
                <p class="text-muted small mb-0">Email: <?= html_escape($store_settings['site_email'] ?? 'support@example.com'); ?></p>
            </div>
            <div class="text-start text-sm-end">
                <div class="invoice-title">INVOICE</div>
                <div class="fw-bold text-primary fs-6">#<?= html_escape($order['order_number']); ?></div>
                <div class="text-muted small mt-1">Date: <?= date('F d, Y', strtotime($order['created_at'])); ?></div>
                <div class="mt-2">
                    <span class="badge bg-<?= ($order['payment_status'] === 'paid') ? 'success' : 'warning'; ?> text-uppercase px-2 py-1">
                        <?= ucfirst($order['payment_status']); ?>
                    </span>
                    <span class="badge bg-secondary text-uppercase px-2 py-1">
                        <?= ucfirst($order['order_status']); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Bill To / Ship To -->
        <div class="row mb-4 g-3">
            <div class="col-12 col-sm-6">
                <h6 class="text-uppercase text-muted fw-bold small mb-2">Billed To:</h6>
                <h6 class="fw-bold mb-1"><?= html_escape($order['customer_name']); ?></h6>
                <div class="text-muted small"><?= nl2br(html_escape($order['billing_address'])); ?></div>
                <div class="text-muted small mt-1"><i class="fa-solid fa-envelope me-1"></i> <?= html_escape($order['customer_email']); ?></div>
                <div class="text-muted small"><i class="fa-solid fa-phone me-1"></i> <?= html_escape($order['customer_phone']); ?></div>
            </div>
            <div class="col-12 col-sm-6 text-start text-sm-end">
                <h6 class="text-uppercase text-muted fw-bold small mb-2">Payment Details:</h6>
                <p class="mb-1 small"><strong>Method:</strong> <?= strtoupper($order['payment_method']); ?></p>
                <?php if (!empty($order['payment_transaction_id'])): ?>
                    <p class="mb-1 small"><strong>Txn ID:</strong> <code><?= html_escape($order['payment_transaction_id']); ?></code></p>
                <?php endif; ?>
                <p class="mb-0 small"><strong>Currency:</strong> <?= html_escape($order['currency'] ?? 'USD'); ?></p>
            </div>
        </div>

        <!-- Line Items Table -->
        <div class="table-responsive mb-4">
            <table class="table invoice-table table-bordered align-middle">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Item Description</th>
                        <th style="width: 120px;">SKU</th>
                        <th style="width: 100px;" class="text-end">Unit Price</th>
                        <th style="width: 80px;" class="text-center">Qty</th>
                        <th style="width: 120px;" class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($order['items'])): $idx = 1; ?>
                        <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td><?= $idx++; ?></td>
                                <td>
                                    <strong><?= html_escape($item['product_title']); ?></strong>
                                    <?php if (!empty($item['variant_title'])): ?>
                                        <div class="text-muted small"><i class="fa-solid fa-sliders me-1"></i> <?= html_escape($item['variant_title']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><code><?= html_escape($item['product_sku'] ?? 'N/A'); ?></code></td>
                                <td class="text-end"><?= ($store_settings['currency_symbol'] ?? '$') . number_format($item['price'], 2); ?></td>
                                <td class="text-center"><?= $item['quantity']; ?></td>
                                <td class="text-end fw-bold"><?= ($store_settings['currency_symbol'] ?? '$') . number_format($item['total'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Totals Calculation -->
        <div class="row g-3">
            <div class="col-12 col-sm-6">
                <?php if (!empty($order['notes'])): ?>
                    <div class="p-3 bg-light rounded border">
                        <h6 class="small fw-bold mb-1">Customer Order Notes:</h6>
                        <p class="small text-muted mb-0"><?= nl2br(html_escape($order['notes'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-12 col-sm-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted text-end">Subtotal:</td>
                        <td class="text-end fw-semibold" style="width: 120px;"><?= ($store_settings['currency_symbol'] ?? '$') . number_format($order['subtotal'], 2); ?></td>
                    </tr>
                    <?php if ($order['discount_amount'] > 0): ?>
                        <tr>
                            <td class="text-danger text-end">Discount:</td>
                            <td class="text-end text-danger fw-semibold">-<?= ($store_settings['currency_symbol'] ?? '$') . number_format($order['discount_amount'], 2); ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="text-muted text-end">Shipping Fee:</td>
                        <td class="text-end fw-semibold"><?= ($order['shipping_fee'] == 0) ? 'FREE' : ($store_settings['currency_symbol'] ?? '$') . number_format($order['shipping_fee'], 2); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted text-end">Tax:</td>
                        <td class="text-end fw-semibold"><?= ($store_settings['currency_symbol'] ?? '$') . number_format($order['tax_amount'], 2); ?></td>
                    </tr>
                    <tr class="border-top">
                        <td class="text-end fw-bold fs-5">Grand Total:</td>
                        <td class="text-end fw-bold fs-5 text-primary"><?= ($store_settings['currency_symbol'] ?? '$') . number_format($order['total_amount'], 2); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="border-top pt-4 mt-4 text-center text-muted small">
            <p class="mb-1">Thank you for your business! We hope you love your order.</p>
            <p class="mb-0">Questions? Contact us at <strong><?= html_escape($store_settings['site_email'] ?? 'support@example.com'); ?></strong> or call <strong><?= html_escape($store_settings['site_phone'] ?? '+1 800 555-0199'); ?></strong>.</p>
        </div>
    </div>
</div>

</body>
</html>
