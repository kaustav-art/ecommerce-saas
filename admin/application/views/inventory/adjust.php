<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><span class="text-muted fw-light">Inventory /</span> Stock Adjustment</h4>
    <a href="<?= site_url('inventory'); ?>" class="btn btn-outline-secondary btn-sm">
      <i class="fa-solid fa-arrow-left me-1"></i> Back to Inventory
    </a>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header pb-2">
          <h5 class="card-title mb-0">Record Stock Adjustment</h5>
          <small class="text-muted">Log physical count reconciliation, warehouse restocking, or damage write-offs.</small>
        </div>
        <div class="card-body pt-3">
          <form action="<?= site_url('inventory/adjust'); ?>" method="POST">
            <div class="mb-3">
              <label class="form-label" for="adj_product">Select Product <span class="text-danger">*</span></label>
              <select class="form-select" id="adj_product" name="product_id" required>
                <option value="">-- Choose Product --</option>
                <?php if (!empty($products)): ?>
                  <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id']; ?>" <?= ($this->input->get('product_id') == $p['id']) ? 'selected' : ''; ?>>
                      <?= html_escape($p['title']); ?> (SKU: <?= html_escape($p['sku']); ?> | Current: <?= $p['stock_quantity']; ?>)
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>

            <div class="row">
              <div class="col-6 mb-3">
                <label class="form-label" for="adj_type">Adjustment Type <span class="text-danger">*</span></label>
                <select class="form-select" id="adj_type" name="type" required>
                  <option value="in">Stock In (Restock / Received from Vendor)</option>
                  <option value="out">Stock Out (Damaged / Missing / Return to Vendor)</option>
                  <option value="adjustment">Count Reconcile (Inventory Audit)</option>
                </select>
              </div>
              <div class="col-6 mb-3">
                <label class="form-label" for="adj_qty">Quantity to Adjust <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="adj_qty" name="quantity" min="1" value="10" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label" for="adj_reason">Reason / Audit Memo <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="adj_reason" name="reason" placeholder="e.g. Received PO-8823 from distributor, or Quarterly warehouse audit" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Submit Adjustment</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
