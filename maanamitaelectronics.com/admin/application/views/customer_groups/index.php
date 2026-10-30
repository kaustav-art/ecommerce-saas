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
    <h4 class="fw-bold m-0"><span class="text-muted fw-light">Customers /</span> Customer Groups & Tiers</h4>
  </div>

  <div class="row align-items-start">
    <!-- Form (Sticky on Scroll) -->
    <div class="col-md-4 mb-4 split-sticky-card">
      <div class="card">
        <div class="card-header pb-2">
          <h5 class="card-title mb-0" id="group-form-title">Add Customer Group</h5>
          <small class="text-muted">Create membership tiers with automatic checkout discounts.</small>
        </div>
        <div class="card-body pt-3">
          <form action="<?= site_url('customer_groups'); ?>" method="POST">
            <input type="hidden" name="id" id="group_id" value="">

            <div class="mb-3">
              <label class="form-label" for="group_name">Group Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="group_name" name="name" placeholder="e.g. VIP Club, Wholesale B2B" required>
            </div>

            <div class="mb-3">
              <label class="form-label" for="group_disc">Discount Percentage (%) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control" id="group_disc" name="discount_percent" value="0.00" required>
              <small class="text-muted">Discount automatically deducted at checkout for members.</small>
            </div>

            <div class="mb-3">
              <label class="form-label" for="group_desc">Description</label>
              <textarea class="form-control" id="group_desc" name="description" rows="3" placeholder="Tier perks and eligibility..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-2">Save Group</button>
            <button type="button" class="btn btn-outline-secondary w-100" onclick="resetGroupForm()">Reset</button>
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
              <h5 class="mb-0 fw-bold">Customer Tiers List</h5>
              <small class="text-muted">
                Showing <span id="displayedCount"><?= min(10, count($groups ?? [])); ?></span> of <span id="totalCount"><?= count($groups ?? []); ?></span> groups
              </small>
            </div>
            <div class="col-sm-6">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input
                  type="text"
                  id="infiniteSearchInput"
                  class="form-control"
                  placeholder="Search group name or description..."
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
                <th>Group Name</th>
                <th>Discount</th>
                <th>Members</th>
                <th>Description</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="infiniteTableBody">
              <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $g): ?>
                  <tr class="infinite-item-row" data-search="<?= html_escape(strtolower($g['name'] . ' ' . ($g['description'] ?? '') . ' ' . $g['discount_percent'])); ?>">
                    <td><strong><?= html_escape($g['name']); ?></strong></td>
                    <td><span class="badge bg-label-success fs-6"><?= number_format($g['discount_percent'], 1); ?>% OFF</span></td>
                    <td><span class="badge bg-label-primary"><?= $g['customers_count']; ?> Customers</span></td>
                    <td><span class="text-muted small"><?= html_escape($g['description']); ?></span></td>
                    <td>
                      <button type="button" class="btn btn-xs btn-outline-primary me-1" onclick="editGroup(<?= htmlspecialchars(json_encode($g), ENT_QUOTES, 'UTF-8'); ?>)">
                        <i class="fa-solid fa-pen-to-square"></i>
                      </button>
                      <a href="<?= site_url('customer_groups/delete/' . $g['id']); ?>" class="btn btn-xs btn-outline-danger" onclick="return confirm('Delete this group? Member customers will be reset to default tier.');">
                        <i class="fa-solid fa-trash-can"></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
              <tr id="noFilterResultsRow" style="<?= !empty($groups) ? 'display:none;' : ''; ?>">
                <td colspan="5" class="text-center text-muted py-4">No customer groups created yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Infinite Scroll Sentinel & Loading Indicator -->
      <div id="infiniteScrollSentinel" class="py-3 text-center">
        <div id="loadingSpinner" class="d-none">
          <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
          <span class="text-muted small fw-semibold">Loading more groups...</span>
        </div>
        <div id="endOfList" class="text-muted small d-none">
          <i class="fa-solid fa-check-double me-1 text-success"></i> All customer groups loaded
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function editGroup(g) {
  document.getElementById('group-form-title').innerText = 'Edit Group: ' + g.name;
  document.getElementById('group_id').value = g.id;
  document.getElementById('group_name').value = g.name;
  document.getElementById('group_disc').value = g.discount_percent;
  document.getElementById('group_desc').value = g.description || '';
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetGroupForm() {
  document.getElementById('group-form-title').innerText = 'Add Customer Group';
  document.getElementById('group_id').value = '';
  document.getElementById('group_name').value = '';
  document.getElementById('group_disc').value = '0.00';
  document.getElementById('group_desc').value = '';
}
</script>
