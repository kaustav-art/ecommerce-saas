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
    <h4 class="fw-bold m-0"><span class="text-muted fw-light">Catalog /</span> Product Attributes</h4>
  </div>

  <div class="row align-items-start">
    <!-- Form: Add/Edit Attribute (Sticky on Scroll) -->
    <div class="col-md-4 mb-4 split-sticky-card">
      <div class="card">
        <div class="card-header pb-2">
          <h5 class="card-title mb-0" id="attr-form-title">Add Attribute</h5>
          <small class="text-muted">Define generic attributes for any category (Fashion, Electronics, Grocery, etc.)</small>
        </div>
        <div class="card-body pt-3">
          <form action="<?= site_url('attributes'); ?>" method="POST">
            <input type="hidden" name="id" id="attr_id" value="">

            <div class="mb-3">
              <label class="form-label" for="attr_name">Attribute Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="attr_name" name="name" placeholder="e.g. Color, Size, RAM, Storage, Weight" required>
            </div>

            <div class="mb-3">
              <label class="form-label" for="attr_type">Display Type</label>
              <select class="form-select" id="attr_type" name="type">
                <option value="select">Dropdown Select</option>
                <option value="multiple_select">Multiple Select</option>
                <option value="button">Button / Swatch (Pill)</option>
                <option value="color">Color Swatch (Hex / Visual)</option>
                <option value="text">Custom Text</option>
              </select>
              <div id="attr_type_locked_alert" class="alert alert-warning py-2 px-3 mt-2 small" style="display: none;">
                <i class="fa-solid fa-lock me-1"></i>
                <span>Display Type is <strong>locked</strong> because this attribute is currently used in <strong id="attr_locked_product_count">0</strong> product(s).</span>
              </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-2">Save Attribute</button>
            <button type="button" class="btn btn-outline-secondary w-100" onclick="resetAttrForm()">Reset</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Table: Attributes List with Infinite Scroll -->
    <div class="col-md-8">
      <div class="card mb-3">
        <div class="card-body py-3">
          <div class="row g-2 align-items-center justify-content-between">
            <div class="col-sm-6">
              <h5 class="mb-0 fw-bold">Dynamic Attributes List</h5>
              <small class="text-muted">
                Showing <span id="displayedCount"><?= min(10, count($attributes ?? [])); ?></span> of <span id="totalCount"><?= count($attributes ?? []); ?></span> attributes
              </small>
            </div>
            <div class="col-sm-6">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input
                  type="text"
                  id="infiniteSearchInput"
                  class="form-control"
                  placeholder="Search attribute or slug..."
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
                <th>Attribute</th>
                <th>Slug</th>
                <th>Type</th>
                <th>Values</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="infiniteTableBody">
              <?php if (!empty($attributes)): ?>
                <?php foreach ($attributes as $a): ?>
                  <tr class="infinite-item-row" data-search="<?= html_escape(strtolower($a['name'] . ' ' . $a['slug'] . ' ' . $a['type'])); ?>">
                    <td><strong><?= html_escape($a['name']); ?></strong></td>
                    <td><code><?= html_escape($a['slug']); ?></code></td>
                    <td>
                      <span class="badge bg-label-info"><?= ($a['type'] === 'multiselect') ? 'Multiple Select' : ucwords(str_replace('_', ' ', $a['type'])); ?></span>
                      <?php if (!empty($a['is_locked'])): ?>
                        <span class="badge bg-label-warning ms-1" title="In use by <?= $a['products_count']; ?> product(s) - Display type locked">
                          <i class="fa-solid fa-lock" style="font-size: 10px;"></i> <?= $a['products_count']; ?> Prod
                        </span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <a href="<?= site_url('attributes/values/' . $a['id']); ?>" class="badge bg-primary text-white text-decoration-none">
                        <?= $a['values_count']; ?> Values &raquo;
                      </a>
                    </td>
                    <td>
                      <a href="<?= site_url('attributes/values/' . $a['id']); ?>" class="btn btn-xs btn-outline-info me-1" title="Manage Values">
                        <i class="fa-solid fa-list-check me-1"></i> Values
                      </a>
                      <button type="button" class="btn btn-xs btn-outline-primary me-1" onclick="editAttr(<?= htmlspecialchars(json_encode($a), ENT_QUOTES, 'UTF-8'); ?>)">
                        <i class="fa-solid fa-pen-to-square"></i>
                      </button>
                      <a href="<?= site_url('attributes/delete/' . $a['id']); ?>" class="btn btn-xs btn-outline-danger" onclick="return confirm('Delete this attribute and all associated values?');">
                        <i class="fa-solid fa-trash-can"></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
              <tr id="noFilterResultsRow" style="<?= !empty($attributes) ? 'display:none;' : ''; ?>">
                <td colspan="5" class="text-center text-muted py-4">No attributes found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Infinite Scroll Sentinel & Loading Indicator -->
      <div id="infiniteScrollSentinel" class="py-3 text-center">
        <div id="loadingSpinner" class="d-none">
          <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
          <span class="text-muted small fw-semibold">Loading more attributes...</span>
        </div>
        <div id="endOfList" class="text-muted small d-none">
          <i class="fa-solid fa-check-double me-1 text-success"></i> All attributes loaded
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function editAttr(a) {
  document.getElementById('attr-form-title').innerText = 'Edit Attribute: ' + a.name;
  document.getElementById('attr_id').value = a.id;
  document.getElementById('attr_name').value = a.name;
  var typeSelect = document.getElementById('attr_type');
  typeSelect.value = a.type;
  if (!typeSelect.value && (a.type === 'multiselect' || a.type === 'multiple')) {
    typeSelect.value = 'multiple_select';
  }

  // Lock Display Type if attribute is used in products
  var lockedAlert = document.getElementById('attr_type_locked_alert');
  var lockedCount = document.getElementById('attr_locked_product_count');
  if (a.is_locked || (a.products_count && parseInt(a.products_count, 10) > 0)) {
    typeSelect.disabled = true;
    typeSelect.title = "Display type cannot be changed because this attribute is used in products";
    if (lockedAlert) {
      lockedAlert.style.display = 'block';
      if (lockedCount) lockedCount.innerText = a.products_count;
    }
  } else {
    typeSelect.disabled = false;
    typeSelect.title = "";
    if (lockedAlert) lockedAlert.style.display = 'none';
  }

  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetAttrForm() {
  document.getElementById('attr-form-title').innerText = 'Add Attribute';
  document.getElementById('attr_id').value = '';
  document.getElementById('attr_name').value = '';
  var typeSelect = document.getElementById('attr_type');
  typeSelect.value = 'select';
  typeSelect.disabled = false;
  typeSelect.title = "";
  var lockedAlert = document.getElementById('attr_type_locked_alert');
  if (lockedAlert) lockedAlert.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
  var rows = Array.from(document.querySelectorAll('.infinite-item-row'));
  var searchInput = document.getElementById('infiniteSearchInput');
  var clearBtn = document.getElementById('clearInfiniteSearch');
  var displayedCountEl = document.getElementById('displayedCount');
  var totalCountEl = document.getElementById('totalCount');
  var noResultsRow = document.getElementById('noFilterResultsRow');
  var sentinel = document.getElementById('infiniteScrollSentinel');
  var spinner = document.getElementById('loadingSpinner');
  var endOfList = document.getElementById('endOfList');
  var batchSize = 10;
  var visibleLimit = batchSize;
  var isLoading = false;

  function renderRows() {
    var q = (searchInput ? searchInput.value : '').trim().toLowerCase();
    if (clearBtn) clearBtn.classList.toggle('d-none', q === '');
    var matched = rows.filter(function(r) {
      return !q || (r.getAttribute('data-search') || '').indexOf(q) !== -1;
    });
    rows.forEach(function(r) { r.style.display = 'none'; });
    var toShow = matched.slice(0, visibleLimit);
    toShow.forEach(function(r) { r.style.display = ''; });
    if (displayedCountEl) displayedCountEl.textContent = toShow.length;
    if (totalCountEl) totalCountEl.textContent = matched.length;
    if (noResultsRow) noResultsRow.style.display = matched.length === 0 ? '' : 'none';
    if (endOfList) {
      endOfList.classList.toggle('d-none', matched.length === 0 || toShow.length < matched.length);
    }
    return { shown: toShow.length, total: matched.length };
  }

  function loadMore() {
    if (isLoading) return;
    var state = renderRows();
    if (state.shown >= state.total) return;
    isLoading = true;
    if (spinner) spinner.classList.remove('d-none');
    setTimeout(function() {
      visibleLimit += batchSize;
      if (spinner) spinner.classList.add('d-none');
      isLoading = false;
      renderRows();
    }, 180);
  }

  if (searchInput) {
    searchInput.addEventListener('input', function() {
      visibleLimit = batchSize;
      renderRows();
    });
  }
  if (clearBtn) {
    clearBtn.addEventListener('click', function() {
      searchInput.value = '';
      visibleLimit = batchSize;
      renderRows();
    });
  }
  if ('IntersectionObserver' in window && sentinel) {
    var obs = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) loadMore();
      });
    }, { rootMargin: '150px' });
    obs.observe(sentinel);
  }
  renderRows();
});
</script>
