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
    <h4 class="fw-bold m-0"><span class="text-muted fw-light">Catalog /</span> Brands</h4>
  </div>

  <div class="row align-items-start">
    <!-- Add / Edit Form (Sticky on Scroll) -->
    <div class="col-md-4 split-sticky-card">
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0" id="brand-form-title">Add Brand</h5>
        </div>
        <div class="card-body">
          <form action="<?= site_url('brands'); ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="brand_id" value="" />
            <div class="mb-3">
              <label class="form-label" for="brand_name">Brand Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="brand_name" name="name" required placeholder="e.g. Nike" />
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold" for="brand_logo_file">Brand Logo</label>
              <div class="d-flex align-items-center gap-3 mb-2">
                <div class="border rounded p-1 bg-white shadow-sm d-flex align-items-center justify-content-center" style="width: 58px; height: 58px; flex-shrink: 0;">
                  <img
                    id="brand_preview_img"
                    src="<?= admin_storefront_asset_url('images/brand/brand-01.svg'); ?>"
                    class="w-100 h-100 object-fit-contain"
                    alt="Brand Logo Preview"
                    onerror="this.src='<?= base_url('assets/img/elements/1.jpg'); ?>'"
                  />
                </div>
                <div class="flex-grow-1">
                  <input
                    type="file"
                    class="form-control form-control-sm"
                    id="brand_logo_file"
                    name="logo_file"
                    accept="image/*,.svg"
                    onchange="previewBrandLogo(this)"
                  />
                  <input type="hidden" name="current_logo" id="brand_current_logo" value="brand/brand-01.svg" />
                  <small class="text-muted d-block mt-1" style="font-size: 11px;">Upload brand logo (SVG, PNG, JPG, WEBP)</small>
                </div>
              </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Save Brand</button>
            <button type="button" class="btn btn-outline-secondary w-100 mt-2" onclick="resetBrandForm()">Reset</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Brands List with Infinite Scroll -->
    <div class="col-md-8">
      <div class="card mb-3">
        <div class="card-body py-3">
          <div class="row g-2 align-items-center justify-content-between">
            <div class="col-sm-6">
              <h5 class="mb-0 fw-bold">Registered Brands</h5>
              <small class="text-muted">
                Showing <span id="displayedCount"><?= min(10, count($brands ?? [])); ?></span> of <span id="totalCount"><?= count($brands ?? []); ?></span> brands
              </small>
            </div>
            <div class="col-sm-6">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input
                  type="text"
                  id="infiniteSearchInput"
                  class="form-control"
                  placeholder="Search brand or slug..."
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
        <div class="table-responsive">
          <table class="table table-hover no-datatable">
            <thead>
              <tr>
                <th>Brand</th>
                <th>Slug</th>
                <th>Products</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="infiniteTableBody">
              <?php if (!empty($brands)): ?>
                <?php foreach ($brands as $b): ?>
                  <tr class="infinite-item-row" data-search="<?= html_escape(strtolower($b['name'] . ' ' . $b['slug'] . ' ' . $b['status'])); ?>">
                    <td><strong><?= html_escape($b['name']); ?></strong></td>
                    <td><code><?= html_escape($b['slug']); ?></code></td>
                    <td><span class="badge bg-label-info"><?= $b['product_count']; ?> Items</span></td>
                    <td><span class="badge bg-label-success"><?= ucfirst($b['status']); ?></span></td>
                    <td>
                      <button
                        class="btn btn-xs btn-outline-primary me-1"
                        onclick="editBrand(<?= htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8'); ?>)">
                        <i class="fa-solid fa-pen-to-square"></i>
                      </button>
                      <a
                        href="<?= site_url('brands/delete/' . $b['id']); ?>"
                        class="btn btn-xs btn-outline-danger"
                        onclick="return confirm('Delete this brand?');">
                        <i class="fa-solid fa-trash-can"></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
              <tr id="noFilterResultsRow" style="<?= !empty($brands) ? 'display:none;' : ''; ?>">
                <td colspan="5" class="text-center text-muted py-4">No brands registered yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Infinite Scroll Sentinel & Loading Indicator -->
      <div id="infiniteScrollSentinel" class="py-3 text-center">
        <div id="loadingSpinner" class="d-none">
          <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
          <span class="text-muted small fw-semibold">Loading more brands...</span>
        </div>
        <div id="endOfList" class="text-muted small d-none">
          <i class="fa-solid fa-check-double me-1 text-success"></i> All brands loaded
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function previewBrandLogo(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('brand_preview_img').src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function editBrand(b) {
  document.getElementById('brand-form-title').innerText = 'Edit Brand: ' + b.name;
  document.getElementById('brand_id').value = b.id;
  document.getElementById('brand_name').value = b.name;
  document.getElementById('brand_current_logo').value = b.logo || 'brand/brand-01.svg';
  document.getElementById('brand_preview_img').src = '<?= admin_storefront_asset_url('images/'); ?>' + (b.logo || 'brand/brand-01.svg');
  document.getElementById('brand_logo_file').value = '';
}

function resetBrandForm() {
  document.getElementById('brand-form-title').innerText = 'Add Brand';
  document.getElementById('brand_id').value = '';
  document.getElementById('brand_name').value = '';
  document.getElementById('brand_current_logo').value = 'brand/brand-01.svg';
  document.getElementById('brand_preview_img').src = '<?= admin_storefront_asset_url('images/brand/brand-01.svg'); ?>';
  document.getElementById('brand_logo_file').value = '';
}
</script>
