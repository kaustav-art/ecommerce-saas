<style>
  @media (min-width: 768px) {
    .split-sticky-card {
      position: sticky;
      top: 85px;
      z-index: 10;
    }
  }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><span class="text-muted fw-light">Marketing /</span> Coupons & Discounts</h4>
  </div>

  <div class="row align-items-start">
    <!-- Form (Sticky on Scroll) -->
    <div class="col-md-4 mb-4 split-sticky-card">
      <div class="card">
        <div class="card-header pb-2">
          <h5 class="card-title mb-0" id="coupon-form-title">Create Coupon</h5>
          <small class="text-muted">Generate promotional promo codes for cart checkout.</small>
        </div>
        <div class="card-body pt-3">
          <form action="<?= site_url('coupons'); ?>" method="POST">
            <input type="hidden" name="id" id="coupon_id" value="">

            <div class="mb-3">
              <label class="form-label" for="c_code">Coupon Code <span class="text-danger">*</span></label>
              <input type="text" class="form-control text-uppercase" id="c_code" name="code" placeholder="e.g. FLASH30, SAVE50" required>
            </div>

            <div class="row">
              <div class="col-6 mb-3">
                <label class="form-label" for="c_type">Discount Type</label>
                <select class="form-select" id="c_type" name="discount_type">
                  <option value="percent">Percentage (%)</option>
                  <option value="fixed">Fixed Amount (<?= html_escape($currency_symbol ?? '$'); ?>)</option>
                </select>
              </div>
              <div class="col-6 mb-3">
                <label class="form-label" for="c_val">Value <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control" id="c_val" name="discount_value" required placeholder="e.g. 20">
              </div>
            </div>

            <div class="row">
              <div class="col-6 mb-3">
                <label class="form-label" for="c_min">Min Spend (<?= html_escape($currency_symbol ?? '$'); ?>)</label>
                <input type="number" step="0.01" class="form-control" id="c_min" name="min_spend" value="0.00">
              </div>
              <div class="col-6 mb-3">
                <label class="form-label" for="c_limit">Usage Limit</label>
                <input type="number" class="form-control" id="c_limit" name="usage_limit" value="100">
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label" for="c_exp">Expiration Date</label>
              <input type="date" class="form-control" id="c_exp" name="expires_at">
            </div>

            <div class="mb-3">
              <label class="form-label" for="c_status">Status</label>
              <select class="form-select" id="c_status" name="status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-2">Save Coupon</button>
            <button type="button" class="btn btn-outline-secondary w-100" onclick="resetCouponForm()">Reset</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Table with Infinite Scroll -->
    <div class="col-md-8">
      <div class="card mb-3">
        <div class="card-body py-3">
          <div class="row g-2 align-items-center justify-content-between">
            <div class="col-sm-6">
              <h5 class="mb-0 fw-bold">Active Promotional Coupons</h5>
              <small class="text-muted">
                Showing <span id="displayedCount"><?= min(10, count($coupons ?? [])); ?></span> of <span id="totalCount"><?= count($coupons ?? []); ?></span> coupons
              </small>
            </div>
            <div class="col-sm-6">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input
                  type="text"
                  id="infiniteSearchInput"
                  class="form-control"
                  placeholder="Search coupon code or status..."
                  autocomplete="off" />
                <button type="button" id="clearInfiniteSearch" class="btn btn-outline-secondary d-none" title="Clear">
                  <i class="fa-solid fa-xmark"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="table-responsive text-nowrap">
          <table class="table table-hover no-datatable">
            <thead>
              <tr>
                <th>Code</th>
                <th>Discount</th>
                <th>Min Spend</th>
                <th>Redeemed</th>
                <th>Expires</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="infiniteTableBody">
              <?php if (!empty($coupons)): ?>
                <?php foreach ($coupons as $c): ?>
                  <tr class="infinite-item-row" data-search="<?= html_escape(strtolower($c['code'] . ' ' . $c['discount_type'] . ' ' . $c['status'])); ?>">
                    <td><code><?= html_escape($c['code']); ?></code></td>
                    <td>
                      <strong><?= ($c['discount_type'] === 'percent') ? (float)$c['discount_value'] . '%' : html_escape($currency_symbol ?? '$') . number_format($c['discount_value'], 2); ?> OFF</strong>
                    </td>
                    <td><?= html_escape($currency_symbol ?? '$') . number_format($c['min_spend'], 2); ?></td>
                    <td><span class="badge bg-label-secondary"><?= $c['used_count']; ?> / <?= $c['usage_limit']; ?></span></td>
                    <td>
                      <?php if (!empty($c['expires_at'])): ?>
                        <small class="<?= (strtotime($c['expires_at']) < time()) ? 'text-danger fw-bold' : 'text-muted'; ?>">
                          <?= date('M d, Y', strtotime($c['expires_at'])); ?>
                        </small>
                      <?php else: ?>
                        <span class="text-muted small">No Expiry</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?= ($c['status'] === 'active') ? '<span class="badge bg-label-success">Active</span>' : '<span class="badge bg-label-secondary">Inactive</span>'; ?>
                    </td>
                    <td>
                      <button type="button" class="btn btn-xs btn-outline-primary me-1" onclick="editCoupon(<?= htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8'); ?>)">
                        <i class="fa-solid fa-pen-to-square"></i>
                      </button>
                      <a href="<?= site_url('coupons/delete/' . $c['id']); ?>" class="btn btn-xs btn-outline-danger" onclick="return confirm('Delete this coupon?');">
                        <i class="fa-solid fa-trash-can"></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
              <tr id="noFilterResultsRow" style="<?= !empty($coupons) ? 'display:none;' : ''; ?>">
                <td colspan="7" class="text-center text-muted py-4">No coupons created yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Infinite Scroll Sentinel & Loading Indicator -->
      <div id="infiniteScrollSentinel" class="py-3 text-center">
        <div id="loadingSpinner" class="d-none">
          <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
          <span class="text-muted small fw-semibold">Loading more coupons...</span>
        </div>
        <div id="endOfList" class="text-muted small d-none">
          <i class="fa-solid fa-check-double me-1 text-success"></i> All coupons loaded
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function editCoupon(c) {
  document.getElementById('coupon-form-title').innerText = 'Edit Coupon: ' + c.code;
  document.getElementById('coupon_id').value = c.id;
  document.getElementById('c_code').value = c.code;
  document.getElementById('c_type').value = c.discount_type;
  document.getElementById('c_val').value = c.discount_value;
  document.getElementById('c_min').value = c.min_spend;
  document.getElementById('c_limit').value = c.usage_limit;
  document.getElementById('c_exp').value = c.expires_at || '';
  document.getElementById('c_status').value = c.status;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetCouponForm() {
  document.getElementById('coupon-form-title').innerText = 'Create Coupon';
  document.getElementById('coupon_id').value = '';
  document.getElementById('c_code').value = '';
  document.getElementById('c_type').value = 'percent';
  document.getElementById('c_val').value = '';
  document.getElementById('c_min').value = '0.00';
  document.getElementById('c_limit').value = '100';
  document.getElementById('c_exp').value = '';
  document.getElementById('c_status').value = 'active';
}
</script>
