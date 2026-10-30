<div class="container-xxl flex-grow-1 container-p-y">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h4 class="fw-bold m-0">
        <span class="text-muted fw-light">Catalog / <a href="<?= site_url('products'); ?>" class="text-muted">Products</a> /</span>
        Variants for <?= html_escape($product['title']); ?>
      </h4>
      <small class="text-muted">
        SKU: <code><?= html_escape($product['sku']); ?></code> | Base Price: <?= html_escape($currency_symbol ?? '$') . number_format($product['price'], 2); ?> | Total Stock: <strong><?= $product['stock_quantity']; ?> units</strong>
      </small>
    </div>
    <div class="d-flex align-items-center gap-2">
      <a href="<?= site_url('products/edit/' . $product['id']); ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-pen-to-square me-1"></i> Edit Product
      </a>
      <a href="<?= site_url('products'); ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Products
      </a>
    </div>
  </div>

  <!-- Flash Messages -->
  <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible mb-4" role="alert">
      <?= $this->session->flashdata('success'); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
  <?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible mb-4" role="alert">
      <?= $this->session->flashdata('error'); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <!-- Card: Existing Variants List (Full Width) -->
  <?php 
    $primary_multi_attr_name = !empty($variant_groups[0]['multi_attr_name']) ? $variant_groups[0]['multi_attr_name'] : 'Sizes';
  ?>
  <div class="card shadow-sm">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center py-3">
      <div>
        <h5 class="card-title mb-0">Existing Variants (<?= count($variant_groups); ?> Groups)</h5>
        <small class="text-muted"><?= count($variants); ?> individual <?= strtolower(html_escape($primary_multi_attr_name)); ?> variations total</small>
      </div>
      <div>
        <a href="<?= site_url('variants/add/' . $product['id']); ?>" class="btn btn-primary btn-sm">
          <i class="fa-solid fa-plus me-1"></i> Add New Variant
        </a>
      </div>
    </div>
    <div class="table-responsive text-nowrap">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="min-width: 240px;">Variant</th>
            <th>Base SKU</th>
            <th style="min-width: 250px;">Attributes & Grouped <?= html_escape($primary_multi_attr_name); ?></th>
            <th>Highlights & Specs</th>
            <th>Price</th>
            <th>Stock</th>
            <th class="text-center" style="min-width: 140px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($variant_groups)): ?>
            <?php foreach ($variant_groups as $grp): 
              $grp_highlights = !empty($grp['highlights']) ? (is_array($grp['highlights']) ? $grp['highlights'] : (json_decode($grp['highlights'], true) ?: [])) : [];
              $grp_specs      = !empty($grp['specifications']) ? (is_array($grp['specifications']) ? $grp['specifications'] : (json_decode($grp['specifications'], true) ?: [])) : [];
            ?>
              <tr>
                <!-- Variant Thumbnail & Title -->
                <td>
                  <div class="d-flex align-items-center">
                    <div class="position-relative me-3" style="width: 48px; height: 48px; flex-shrink: 0;">
                      <img
                        src="<?= base_url('../website/assets/images/' . ($grp['image'] ?: $product['main_image'])); ?>"
                        class="rounded w-100 h-100 object-fit-cover border shadow-xs"
                        alt="Variant"
                        onerror="this.src='<?= base_url('../website/assets/images/products/womens/women-1.jpg'); ?>'"
                      />
                      <?php 
                        $grp_gal = !empty($grp['gallery_images']) ? (is_array($grp['gallery_images']) ? $grp['gallery_images'] : (json_decode($grp['gallery_images'], true) ?: [])) : []; 
                        if (!empty($grp_gal)):
                      ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-info" style="font-size: 8px; padding: 2px 5px;" title="<?= count($grp_gal); ?> gallery photos">+<?= count($grp_gal); ?></span>
                      <?php endif; ?>
                    </div>
                    <div>
                      <strong class="d-block text-truncate text-dark" style="max-width: 240px;" title="<?= html_escape($grp['title']); ?>">
                        <?= html_escape($grp['title']); ?>
                      </strong>
                      <?php if (!empty($grp['non_size_attrs'])): ?>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                          <?php foreach ($grp['non_size_attrs'] as $nsa): ?>
                            <span class="badge bg-label-secondary d-inline-flex align-items-center" style="font-size: 11px;">
                              <?php if (!empty($nsa['color_code'])): ?>
                                <span class="rounded-circle me-1" style="width: 8px; height: 8px; background: <?= html_escape($nsa['color_code']); ?>; display: inline-block; border: 1px solid rgba(0,0,0,0.25);"></span>
                              <?php endif; ?>
                              <?= html_escape($nsa['attribute_name']); ?>: <strong><?= html_escape($nsa['attribute_value']); ?></strong>
                            </span>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </td>

                <!-- Base SKU -->
                <td>
                  <code class="fw-bold fs-7"><?= html_escape($grp['base_sku']); ?></code>
                  <?php if ($grp['has_sizes']): ?>
                    <small class="text-muted d-block" style="font-size: 11px;"><?= count($grp['sizes']); ?> <?= strtolower(html_escape($grp['multi_attr_name'] ?? 'size')); ?> variations</small>
                  <?php endif; ?>
                </td>

                <!-- Attributes & Grouped Sizes / Options -->
                <td>
                  <?php if ($grp['has_sizes'] && !empty($grp['sizes'])): ?>
                    <div class="d-flex flex-wrap gap-1 align-items-center mb-1">
                      <?php foreach ($grp['sizes'] as $sz): ?>
                        <?php if ($sz['stock'] > 0): ?>
                          <span class="badge bg-label-primary px-2 py-1" style="font-size: 11px;" title="SKU: <?= html_escape($sz['sku']); ?> | Stock: <?= $sz['stock']; ?>">
                            <strong><?= html_escape($sz['size_name']); ?>:</strong> <?= $sz['stock']; ?>
                          </span>
                        <?php else: ?>
                          <span class="badge bg-label-danger text-decoration-line-through px-2 py-1" style="font-size: 11px;" title="SKU: <?= html_escape($sz['sku']); ?> | Out of stock">
                            <strong><?= html_escape($sz['size_name']); ?>:</strong> 0
                          </span>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-link btn-xs p-0 text-primary text-decoration-none fw-semibold" style="font-size: 11px;" onclick="openQuickStockModal(<?= htmlspecialchars(json_encode($grp), ENT_QUOTES, 'UTF-8'); ?>)">
                      <i class="fa-solid fa-boxes-stacked me-1"></i>Quick Adjust Stocks
                    </button>
                  <?php else: ?>
                    <span class="text-muted small">Single Variant</span>
                  <?php endif; ?>
                </td>

                <!-- Highlights & Specs Badges -->
                <td>
                  <div class="d-flex flex-column gap-1">
                    <?php if (!empty($grp_highlights)): ?>
                      <span class="badge bg-label-info d-inline-flex align-items-center" style="font-size: 10px; width: fit-content;" title="<?= count($grp_highlights); ?> Highlights configured">
                        <i class="fa-solid fa-highlighter me-1"></i> <?= count($grp_highlights); ?> Highlights
                      </span>
                    <?php else: ?>
                      <span class="text-muted" style="font-size: 11px;">Highlights: None</span>
                    <?php endif; ?>

                    <?php if (!empty($grp_specs)): ?>
                      <span class="badge bg-label-secondary d-inline-flex align-items-center" style="font-size: 10px; width: fit-content;" title="<?= count($grp_specs); ?> Specifications configured">
                        <i class="fa-solid fa-list-check me-1"></i> <?= count($grp_specs); ?> Specs
                      </span>
                    <?php else: ?>
                      <span class="text-muted" style="font-size: 11px;">Specs: None</span>
                    <?php endif; ?>
                  </div>
                </td>

                <!-- Price -->
                <td>
                  <?php if (!empty($grp['sale_price'])): ?>
                    <strong class="text-danger fs-6"><?= html_escape($currency_symbol ?? '$') . number_format($grp['sale_price'], 2); ?></strong>
                    <small class="text-muted text-decoration-line-through d-block" style="font-size: 11px;"><?= html_escape($currency_symbol ?? '$') . number_format($grp['price'], 2); ?></small>
                  <?php else: ?>
                    <strong class="text-dark fs-6"><?= html_escape($currency_symbol ?? '$') . number_format($grp['price'], 2); ?></strong>
                  <?php endif; ?>
                </td>

                <!-- Stock -->
                <td>
                  <div class="d-flex flex-column">
                    <strong class="<?= ($grp['total_stock'] > 0) ? 'text-success' : 'text-danger'; ?>">
                      <?= $grp['total_stock']; ?> units
                    </strong>
                    <?php if ($grp['has_sizes']): ?>
                      <small class="text-muted" style="font-size: 10px;">
                        <?= $grp['in_stock_count']; ?> in stock, <?= $grp['out_of_stock_count']; ?> out
                      </small>
                    <?php endif; ?>
                  </div>
                </td>

                <!-- Actions -->
                <td class="text-center">
                  <div class="d-inline-flex align-items-center gap-1">
                    <a
                      href="<?= site_url('variants/edit/' . $product['id'] . '/' . $grp['primary_id']); ?>"
                      class="btn btn-sm btn-outline-primary"
                      title="Edit Variant"
                    >
                      <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <?php if ($grp['has_sizes']): ?>
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-info"
                        title="Manage Stock by <?= html_escape($grp['multi_attr_name'] ?? 'Option'); ?>"
                        onclick="openQuickStockModal(<?= htmlspecialchars(json_encode($grp), ENT_QUOTES, 'UTF-8'); ?>)"
                      >
                        <i class="fa-solid fa-boxes-stacked"></i>
                      </button>
                    <?php endif; ?>
                    <form
                      action="<?= site_url('variants/delete_group/' . $product['id']); ?>"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('Are you sure you want to delete this variant group (<?= count($grp['variant_ids']); ?> variations)?');"
                    >
                      <input type="hidden" name="variant_ids" value="<?= implode(',', $grp['variant_ids']); ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Variant Group">
                        <i class="fa-solid fa-trash-can"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center py-5">
                <div class="text-muted mb-3">
                  <i class="fa-solid fa-box-open fa-3x text-secondary mb-2 d-block"></i>
                  No variants defined yet for this product.
                </div>
                <a href="<?= site_url('variants/add/' . $product['id']); ?>" class="btn btn-primary">
                  <i class="fa-solid fa-plus me-1"></i> Add First Variant
                </a>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Quick Manage Stock Modal -->
<div class="modal fade" id="quickStockModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header pb-2 border-bottom">
        <div>
          <h5 class="modal-title mb-0" id="quickStockModalTitle">
            <i class="fa-solid fa-boxes-stacked text-primary me-2"></i> <span id="qs-modal-heading">Manage Stock</span>
          </h5>
          <small class="text-muted" id="quickStockModalSubtitle"></small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="quickStockForm">
          <input type="hidden" id="qs_product_id" value="<?= $product['id']; ?>">
          
          <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <span class="small fw-semibold text-secondary" id="qs-adjust-label">Adjust quantities:</span>
            <div class="d-flex align-items-center gap-1">
              <input type="number" min="0" class="form-control form-control-sm text-end" id="qs_quick_set_all" placeholder="Qty" value="10" style="width: 65px;">
              <button type="button" class="btn btn-outline-primary btn-xs" onclick="applyQsStockToAll()">Set All</button>
            </div>
          </div>

          <div id="quickStockList" class="d-flex flex-column gap-2">
            <!-- Populated dynamically -->
          </div>

          <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
            <span class="text-muted small">Total Updated Stock:</span>
            <strong class="text-primary fs-6" id="qs_total_stock_display">0 units</strong>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="btnSaveQuickStock" onclick="submitQuickStock()">
          <i class="fa-solid fa-check me-1"></i> Save Stock Changes
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Quick Stock Management Modal
var activeQsGroup = null;
var qsModalInstance = null;

function openQuickStockModal(grp) {
  activeQsGroup = grp;
  var optName = grp.multi_attr_name || 'Option';
  var headingEl = document.getElementById('qs-modal-heading');
  if (headingEl) headingEl.textContent = 'Manage Stock by ' + optName;
  var adjustLabelEl = document.getElementById('qs-adjust-label');
  if (adjustLabelEl) adjustLabelEl.textContent = 'Adjust quantities for each ' + optName.toLowerCase() + ':';

  document.getElementById('quickStockModalSubtitle').textContent = grp.title + ' (' + grp.base_sku + ')';
  var list = document.getElementById('quickStockList');
  list.innerHTML = '';

  if (!grp.sizes || grp.sizes.length === 0) {
    list.innerHTML = '<div class="text-muted small py-2">No ' + optName.toLowerCase() + ' options configured for this variant.</div>';
    return;
  }

  grp.sizes.forEach(function(sz) {
    var isOut = sz.stock <= 0;
    var row = document.createElement('div');
    row.className = 'd-flex align-items-center justify-content-between p-2 border rounded bg-white shadow-sm';
    row.id = 'qs_size_row_' + sz.variant_id;
    row.innerHTML = 
      '<div class="d-flex align-items-center gap-2">' +
        '<span class="badge bg-primary fs-6 fw-bold" style="min-width: 40px;">' + sz.size_name + '</span>' +
        '<div>' +
          '<code class="d-block" style="font-size: 11px;">' + sz.sku + '</code>' +
          '<span class="badge ' + (isOut ? 'bg-label-danger' : 'bg-label-success') + ' qs-status-badge" style="font-size: 9px;">' +
            (isOut ? 'Out of stock' : 'In stock') +
          '</span>' +
        '</div>' +
      '</div>' +
      '<div class="d-flex align-items-center gap-2">' +
        '<div class="input-group input-group-sm" style="width: 110px;" title="Stock Quantity">' +
          '<span class="input-group-text">Qty</span>' +
          '<input type="number" min="0" class="form-control text-end qs-stock-input" data-var-id="' + sz.variant_id + '" value="' + (sz.stock !== null && sz.stock !== undefined && sz.stock !== '' ? sz.stock : 0) + '" placeholder="0" oninput="recalcQsTotal()" onblur="if(this.value.trim()===\'\') { this.value=\'0\'; recalcQsTotal(); }">' +
        '</div>' +
        '<div class="input-group input-group-sm" style="width: 120px;" title="Sale Price">' +
          '<span class="input-group-text"><?= html_escape($currency_symbol ?? '$'); ?></span>' +
          '<input type="number" step="0.01" min="0" class="form-control text-end qs-sale-price-input" data-var-id="' + sz.variant_id + '" value="' + (sz.sale_price !== null && sz.sale_price !== undefined ? sz.sale_price : '') + '" placeholder="Price">' +
        '</div>' +
      '</div>';
    list.appendChild(row);
  });

  recalcQsTotal();

  var modalEl = document.getElementById('quickStockModal');
  qsModalInstance = new bootstrap.Modal(modalEl);
  qsModalInstance.show();
}

function applyQsStockToAll() {
  var val = document.getElementById('qs_quick_set_all') ? document.getElementById('qs_quick_set_all').value : '10';
  document.querySelectorAll('.qs-stock-input').forEach(function(inp) {
    inp.value = val;
  });
  recalcQsTotal();
}

function recalcQsTotal() {
  var total = 0;
  document.querySelectorAll('.qs-stock-input').forEach(function(inp) {
    var qty = parseInt(inp.value, 10) || 0;
    total += qty;
    var row = inp.closest('#qs_size_row_' + inp.getAttribute('data-var-id'));
    if (row) {
      var badge = row.querySelector('.qs-status-badge');
      if (badge) {
        if (qty <= 0) {
          badge.className = 'badge bg-label-danger qs-status-badge';
          badge.textContent = 'Out of stock';
        } else {
          badge.className = 'badge bg-label-success qs-status-badge';
          badge.textContent = 'In stock';
        }
      }
    }
  });

  var totalEl = document.getElementById('qs_total_stock_display');
  if (totalEl) totalEl.textContent = total + ' units';
}

function submitQuickStock() {
  if (!activeQsGroup) return;

  var btn = document.getElementById('btnSaveQuickStock');
  var origText = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

  var payload = new URLSearchParams();
  payload.append('product_id', '<?= $product["id"]; ?>');

  document.querySelectorAll('.qs-stock-input').forEach(function(inp) {
    var vId = inp.getAttribute('data-var-id');
    var qty = inp.value;
    payload.append('stocks[' + vId + ']', qty);
  });

  document.querySelectorAll('.qs-sale-price-input').forEach(function(inp) {
    var vId = inp.getAttribute('data-var-id');
    var sp = inp.value;
    payload.append('sale_prices[' + vId + ']', sp);
  });

  fetch('<?= site_url("variants/update_size_stocks"); ?>', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: payload.toString()
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    btn.disabled = false;
    btn.innerHTML = origText;
    if (data.status === 'success') {
      if (qsModalInstance) qsModalInstance.hide();
      window.location.reload();
    } else {
      alert(data.message || 'Error updating stocks');
    }
  })
  .catch(function(err) {
    btn.disabled = false;
    btn.innerHTML = origText;
    alert('Request failed');
  });
}
</script>
