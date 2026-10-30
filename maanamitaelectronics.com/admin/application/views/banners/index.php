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
    <h4 class="fw-bold m-0"><span class="text-muted fw-light">Catalog /</span> Home Page Banners & Sliders</h4>
    <a href="<?= admin_storefront_url(); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
      <i class="fa-solid fa-eye me-1"></i> Preview Homepage
    </a>
  </div>

  <div class="row align-items-start">
    <!-- Add / Edit Form (Sticky on Scroll) -->
    <div class="col-lg-4 col-md-5 split-sticky-card">
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0" id="form-title">Add New Slider Banner</h5>
          <span class="badge bg-label-primary">Dynamic Homepage</span>
        </div>
        <div class="card-body">
          <form action="<?= site_url('banners'); ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="banner_id" value="" />

            <div class="mb-3">
              <label class="form-label" for="banner_title">Main Heading / Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="banner_title" name="title" required placeholder="e.g. Flash Sale Madness" />
            </div>

            <div class="mb-3">
              <label class="form-label" for="banner_subtitle">Subheading / Badge Text</label>
              <input type="text" class="form-control" id="banner_subtitle" name="subtitle" placeholder="e.g. BIKINIS & SWIMSUITS" />
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label" for="banner_btn_text">Button Label</label>
                <input type="text" class="form-control" id="banner_btn_text" name="button_text" value="Explore Collection" />
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label" for="banner_btn_link">Button Link</label>
                <input type="text" class="form-control" id="banner_btn_link" name="button_link" value="shop" placeholder="shop or shop/category-slug" />
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label" for="banner_file">Banner Image <span class="text-danger">*</span></label>
              <input type="file" class="form-control" id="banner_file" name="image_file" accept="image/*" onchange="previewBannerFile(this)" />
              <input type="hidden" name="current_image" id="banner_current_image" value="" />
              <small class="text-muted">Recommended: 1920x800px or wide aspect ratio.</small>
              <div class="mt-2" id="banner_preview_box" style="display: none;">
                <small class="text-muted d-block mb-1">Preview:</small>
                <img id="banner_preview_img" src="" alt="Banner Preview" class="rounded border" style="max-width: 100%; max-height: 120px; object-fit: cover;">
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label" for="banner_sort">Sort Order</label>
                <input type="number" class="form-control" id="banner_sort" name="sort_order" value="0" />
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label" for="banner_status">Status</label>
                <select class="form-select" id="banner_status" name="status">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-2">Save Banner</button>
            <button type="button" class="btn btn-outline-secondary w-100" onclick="resetForm()">Reset</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Banner List Table with Infinite Scroll -->
    <div class="col-lg-8 col-md-7">
      <div class="card mb-3">
        <div class="card-body py-3">
          <div class="row g-2 align-items-center justify-content-between">
            <div class="col-sm-6">
              <h5 class="mb-0 fw-bold">Active Sliders & Banners</h5>
              <small class="text-muted">
                Showing <span id="displayedCount"><?= min(10, count($banners ?? [])); ?></span> of <span id="totalCount"><?= count($banners ?? []); ?></span> banners
              </small>
            </div>
            <div class="col-sm-6">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input
                  type="text"
                  id="infiniteSearchInput"
                  class="form-control"
                  placeholder="Search banner title or subtitle..."
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
          <table class="table table-hover align-middle no-datatable">
            <thead>
              <tr>
                <th style="width: 120px;">Preview</th>
                <th>Content</th>
                <th>Button</th>
                <th>Sort</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="infiniteTableBody">
              <?php if (!empty($banners)): ?>
                <?php foreach ($banners as $b): ?>
                  <tr class="infinite-item-row" data-search="<?= html_escape(strtolower($b['title'] . ' ' . ($b['subtitle'] ?? '') . ' ' . ($b['button_text'] ?? '') . ' ' . $b['status'])); ?>">
                    <td>
                      <div class="rounded overflow-hidden border" style="width: 100px; height: 55px; background: #f8f9fa;">
                        <img
                          src="<?= admin_storefront_asset_url('images/' . $b['image']); ?>"
                          alt="<?= html_escape($b['title']); ?>"
                          class="w-100 h-100"
                          style="object-fit: cover;"
                          onerror="this.src='<?= admin_storefront_asset_url('images/slider/slider-women1.jpg'); ?>'" />
                      </div>
                    </td>
                    <td>
                      <?php if (!empty($b['subtitle'])): ?>
                        <span class="badge bg-label-secondary small mb-1"><?= html_escape($b['subtitle']); ?></span><br>
                      <?php endif; ?>
                      <strong><?= html_escape($b['title']); ?></strong>
                    </td>
                    <td>
                      <span class="badge bg-label-info"><?= html_escape($b['button_text']); ?></span>
                      <div class="text-muted small"><code><?= html_escape($b['button_link']); ?></code></div>
                    </td>
                    <td><span class="badge bg-label-dark"><?= (int)$b['sort_order']; ?></span></td>
                    <td>
                      <a href="<?= site_url('banners/toggle/' . $b['id']); ?>" class="text-decoration-none">
                        <?= ($b['status'] === 'active')
                            ? '<span class="badge bg-label-success">Active</span>'
                            : '<span class="badge bg-label-danger">Inactive</span>'; ?>
                      </a>
                    </td>
                    <td>
                      <button
                        type="button"
                        class="btn btn-xs btn-outline-primary me-1"
                        onclick="editBanner(<?= htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8'); ?>)"
                        title="Edit Banner">
                        <i class="fa-solid fa-pen-to-square"></i>
                      </button>
                      <a
                        href="<?= site_url('banners/delete/' . $b['id']); ?>"
                        class="btn btn-xs btn-outline-danger"
                        onclick="return confirm('Delete this banner from homepage?');"
                        title="Delete Banner">
                        <i class="fa-solid fa-trash-can"></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
              <tr id="noFilterResultsRow" style="<?= !empty($banners) ? 'display:none;' : ''; ?>">
                <td colspan="6" class="text-center text-muted py-4">No banners found. Add a banner to display on the homepage slider.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Infinite Scroll Sentinel & Loading Indicator -->
      <div id="infiniteScrollSentinel" class="py-3 text-center">
        <div id="loadingSpinner" class="d-none">
          <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
          <span class="text-muted small fw-semibold">Loading more banners...</span>
        </div>
        <div id="endOfList" class="text-muted small d-none">
          <i class="fa-solid fa-check-double me-1 text-success"></i> All banners loaded
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function previewBannerFile(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('banner_preview_img').src = e.target.result;
      document.getElementById('banner_preview_box').style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function editBanner(b) {
  document.getElementById('form-title').innerText = 'Edit Slider: ' + b.title;
  document.getElementById('banner_id').value = b.id;
  document.getElementById('banner_title').value = b.title;
  document.getElementById('banner_subtitle').value = b.subtitle || '';
  document.getElementById('banner_btn_text').value = b.button_text || 'Explore Collection';
  document.getElementById('banner_btn_link').value = b.button_link || 'shop';
  document.getElementById('banner_current_image').value = b.image || '';
  document.getElementById('banner_file').value = '';

  const prevBox = document.getElementById('banner_preview_box');
  const prevImg = document.getElementById('fs_preview_img') || document.getElementById('banner_preview_img');
  if (b.image) {
    prevImg.src = '<?= admin_storefront_asset_url('images/'); ?>' + b.image;
    prevBox.style.display = 'block';
  } else {
    prevBox.style.display = 'none';
  }

  document.getElementById('banner_sort').value = b.sort_order || 0;
  document.getElementById('banner_status').value = b.status || 'active';
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetForm() {
  document.getElementById('form-title').innerText = 'Add New Slider Banner';
  document.getElementById('banner_id').value = '';
  document.getElementById('banner_title').value = '';
  document.getElementById('banner_subtitle').value = '';
  document.getElementById('banner_btn_text').value = 'Explore Collection';
  document.getElementById('banner_btn_link').value = 'shop';
  document.getElementById('banner_current_image').value = '';
  document.getElementById('banner_file').value = '';
  document.getElementById('banner_preview_box').style.display = 'none';
  document.getElementById('banner_preview_img').src = '';
  document.getElementById('banner_sort').value = 0;
  document.getElementById('banner_status').value = 'active';
}
</script>
