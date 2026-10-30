<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Invoice #<?= html_escape($order['order_number']); ?></title>
  <link rel="stylesheet" href="<?= base_url('assets/vendor/css/core.css'); ?>" />
  <style>
    body { background: #fff; padding: 40px; font-family: sans-serif; color: #333; }
    @media print {
      .no-print { display: none !important; }
      body { padding: 0; }
    }
  </style>
</head>
<body>
  <div class="no-print mb-4 d-flex justify-content-between">
    <a href="javascript:window.history.back()" class="btn btn-outline-secondary">Back</a>
    <button onclick="window.print()" class="btn btn-primary">Print / Save as PDF</button>
  </div>

  <div class="border p-5 rounded">
    <div class="d-flex justify-content-between align-items-center mb-5 border-bottom pb-4">
      <div>
        <h2 class="fw-bold mb-1"><?= html_escape($site_name); ?></h2>
        <div class="text-muted"><?= html_escape($site_address); ?></div>
        <div class="text-muted">Email: <?= html_escape($site_email); ?></div>
      </div>
      <div class="text-end">
        <h3 class="text-primary mb-1">INVOICE</h3>
        <div class="fw-bold">#<?= html_escape($order['order_number']); ?></div>
        <div class="text-muted">Date: <?= date('M d, Y', strtotime($order['created_at'])); ?></div>
        <div class="text-muted">Payment: <strong><?= strtoupper($order['payment_method']); ?> (<?= ucfirst($order['payment_status']); ?>)</strong></div>
      </div>
    </div>

    <div class="row mb-5">
      <div class="col-6">
        <h6 class="text-uppercase text-muted small fw-bold">Invoiced To:</h6>
        <h5><?= html_escape($order['customer_name']); ?></h5>
        <div>Email: <?= html_escape($order['customer_email']); ?></div>
        <div>Phone: <?= html_escape($order['customer_phone']); ?></div>
      </div>
      <div class="col-6 text-end">
        <h6 class="text-uppercase text-muted small fw-bold">Shipped To:</h6>
        <div><?= nl2br(html_escape($order['shipping_address'])); ?></div>
      </div>
    </div>

    <table class="table table-bordered mb-4">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Item Description</th>
          <th>SKU</th>
          <th>Unit Price</th>
          <th>Qty</th>
          <th class="text-end">Total</th>
        </tr>
      </thead>
      <tbody>
        <?php $i = 1; foreach ($order['items'] as $item): ?>
          <tr>
            <td><?= $i++; ?></td>
            <td><strong><?= html_escape($item['product_title']); ?></strong></td>
            <td><?= html_escape($item['product_sku']); ?></td>
            <td><?= $currency_symbol . number_format($item['price'], 2); ?></td>
            <td><?= $item['quantity']; ?></td>
            <td class="text-end"><?= $currency_symbol . number_format($item['total'], 2); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="row justify-content-end">
      <div class="col-md-5">
        <table class="table table-sm">
          <tr>
            <td>Subtotal:</td>
            <td class="text-end"><?= $currency_symbol . number_format($order['subtotal'], 2); ?></td>
          </tr>
          <?php if ($order['discount_amount'] > 0): ?>
          <tr>
            <td>Discount:</td>
            <td class="text-end text-danger">-<?= $currency_symbol . number_format($order['discount_amount'], 2); ?></td>
          </tr>
          <?php endif; ?>
          <tr>
            <td>Shipping Fee:</td>
            <td class="text-end"><?= $currency_symbol . number_format($order['shipping_fee'], 2); ?></td>
          </tr>
          <tr>
            <td>Taxes:</td>
            <td class="text-end"><?= $currency_symbol . number_format($order['tax_amount'], 2); ?></td>
          </tr>
          <tr class="table-light">
            <th>Total Amount:</th>
            <th class="text-end fs-5 text-primary"><?= $currency_symbol . number_format($order['total_amount'], 2); ?></th>
          </tr>
        </table>
      </div>
    </div>

    <div class="mt-5 border-top pt-3 text-center text-muted small">
      Thank you for your business! If you have questions concerning this invoice, contact us at <?= html_escape($site_email); ?>
    </div>
  </div>
</body>
</html>
