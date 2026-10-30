<style>
@media (min-width: 992px) {
  .sticky-organization-wrapper {
    position: sticky;
    top: 80px;
    z-index: 10;
  }
  .sticky-organization-card {
    max-height: calc(100vh - 100px);
    display: flex;
    flex-direction: column;
  }
  .sticky-organization-card .card-header {
    flex-shrink: 0;
  }
  .sticky-organization-card .card-body {
    flex: 1 1 auto;
    overflow-y: auto;
    overflow-x: hidden;
  }
  .sticky-organization-card .card-footer {
    flex-shrink: 0;
  }
}

/* Elegant slim scrollbar for sticky organization card */
.sticky-organization-card .card-body::-webkit-scrollbar {
  width: 5px;
}
.sticky-organization-card .card-body::-webkit-scrollbar-track {
  background: transparent;
}
.sticky-organization-card .card-body::-webkit-scrollbar-thumb {
  background: #d4d7dc;
  border-radius: 4px;
}
.sticky-organization-card .card-body::-webkit-scrollbar-thumb:hover {
  background: #b5b9c0;
}
</style>

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><span class="text-muted fw-light">Products /</span> Edit Product</h4>
    <div class="d-flex gap-2">
      <a href="<?= site_url('products'); ?>" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Products
      </a>
      <button type="submit" form="productEditForm" class="btn btn-primary d-none d-sm-inline-flex align-items-center">
        <i class="fa-solid fa-floppy-disk me-1"></i> Update Product
      </button>
    </div>
  </div>

  <form action="<?= site_url('products/edit/' . $product['id']); ?>" method="POST" enctype="multipart/form-data" id="productEditForm">
    <div class="row">
      <div class="col-12 col-lg-8">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">Product Details</h5>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label" for="title">Product Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="title" name="title" value="<?= html_escape($product['title']); ?>" required />
            </div>
            <div class="mb-3">
              <label class="form-label" for="short_description">Short Description</label>
              <textarea class="form-control" id="short_description" name="short_description" rows="2"><?= html_escape($product['short_description']); ?></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label" for="description">Full Description</label>
              <textarea class="form-control" id="description" name="description" rows="5"><?= html_escape($product['description']); ?></textarea>
            </div>
          </div>
        </div>

        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">Pricing & Inventory</h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label" for="price">Regular Price <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text"><?= html_escape($currency_symbol ?? '$'); ?></span>
                  <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= $product['price']; ?>" required />
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label" for="sale_price">Sale Price (Optional)</label>
                <div class="input-group">
                  <span class="input-group-text"><?= html_escape($currency_symbol ?? '$'); ?></span>
                  <input type="number" step="0.01" class="form-control" id="sale_price" name="sale_price" value="<?= $product['sale_price']; ?>" />
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">SKU (Read-only)</label>
                <input type="text" class="form-control bg-light" value="<?= html_escape($product['sku']); ?>" readonly />
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label" for="stock_quantity">Stock Quantity <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" value="<?= $product['stock_quantity']; ?>" required />
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold" for="max_purchase_quantity">Max Purchase Quantity (Per Order)</label>
                <input type="number" min="1" class="form-control" id="max_purchase_quantity" name="max_purchase_quantity" value="<?= html_escape($product['max_purchase_quantity'] ?? 5); ?>" placeholder="e.g. 5" />
                <small class="text-muted d-block" style="font-size: 11px;">How many products user can purchase together in one order (e.g. 5).</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Product Type & Variants Card -->
        <div class="card mb-4 <?= ($product['product_type'] === 'variable') ? 'border-primary' : ''; ?>">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 d-flex align-items-center">
              <i class="fa-solid fa-code-fork <?= ($product['product_type'] === 'variable') ? 'text-primary' : 'text-secondary'; ?> me-2"></i>
              Product Type & Variants
            </h5>
            <span class="badge bg-<?= ($product['product_type'] === 'variable') ? 'primary' : 'secondary'; ?> text-uppercase">
              <?= html_escape($product['product_type'] ?? 'simple'); ?>
            </span>
          </div>
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold" for="product_type">Product Type</label>
                <select class="form-select" id="product_type" name="product_type">
                  <option value="simple" <?= ($product['product_type'] === 'simple') ? 'selected' : ''; ?>>Simple Product</option>
                  <option value="variable" <?= ($product['product_type'] === 'variable') ? 'selected' : ''; ?>>Variable Product (Variants & Sizes)</option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Variants Status</label>
                <div>
                  <span class="badge bg-label-primary fs-6 py-2 px-3">
                    <i class="fa-solid fa-boxes-stacked me-1"></i> <?= (int) $variants_count; ?> Variant<?= $variants_count == 1 ? '' : 's'; ?> Configured
                  </span>
                </div>
              </div>
            </div>

            <?php if (!empty($assigned_attributes)): ?>
              <div class="mb-3">
                <label class="form-label small fw-semibold text-muted d-block mb-1">Assigned Attributes:</label>
                <div class="d-flex flex-wrap gap-2">
                  <?php foreach ($assigned_attributes as $attr): ?>
                    <span class="badge bg-label-info py-2 px-3">
                      <i class="fa-solid fa-tag me-1"></i> <?= html_escape($attr['name']); ?>
                    </span>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>

            <div class="d-flex flex-wrap align-items-center justify-content-between p-3 rounded bg-light border mt-2">
              <div>
                <h6 class="mb-1 fw-bold text-dark">
                  <i class="fa-solid fa-sliders me-1 text-primary"></i> Variant & Stock Management
                </h6>
                <small class="text-muted">Manage size groups, per-size inventory, custom SKUs, and variant images.</small>
              </div>
              <a href="<?= site_url('variants/product/' . $product['id']); ?>" class="btn btn-primary mt-2 mt-sm-0">
                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Manage Variants & Sizes
              </a>
            </div>

            <?php if ($product['product_type'] === 'variable'): ?>
              <small class="text-muted d-block mt-2" style="font-size: 11px;">
                <i class="fa-solid fa-circle-info me-1 text-primary"></i> Note: For variable products, total stock quantity is automatically calculated and synchronized from the individual size stocks in the variants manager.
              </small>
            <?php endif; ?>
          </div>
        </div>

        <?php 
          $has_highlights = !empty($highlights);
          $has_specs = !empty($product['specifications']);
        ?>

        <!-- Product Highlights Card -->
        <div class="card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div>
              <h5 class="card-title mb-0 d-flex align-items-center text-dark">
                <i class="fa-solid fa-highlighter text-primary me-2"></i> Product Highlights
              </h5>
              <small class="text-muted">Highlight key attributes prominently on the product page (e.g. Sleeve: Full Sleeve, Fabric: Cotton Blend).</small>
            </div>
            <div class="form-check form-switch m-0">
              <input class="form-check-input" type="checkbox" id="enable_highlights" name="enable_highlights" value="1" <?= $has_highlights ? 'checked' : ''; ?> onchange="toggleHighlightsPanel(this.checked)" style="width: 2.5em; height: 1.3em; cursor: pointer;">
            </div>
          </div>
          <div class="card-body" id="highlights_panel" style="display: <?= $has_highlights ? 'block' : 'none'; ?>;">
            <div class="table-responsive">
              <table class="table table-bordered table-sm align-middle no-datatable" id="highlights_table">
                <thead class="table-light">
                  <tr>
                    <th style="width: 45%;">Highlight Key / Label</th>
                    <th style="width: 45%;">Value</th>
                    <th style="width: 10%;" class="text-center">Action</th>
                  </tr>
                </thead>
                <tbody id="highlights_body">
                  <?php if ($has_highlights): ?>
                    <?php foreach ($highlights as $hl): ?>
                      <tr>
                        <td>
                          <input type="text" name="highlight_keys[]" class="form-control form-control-sm" placeholder="e.g. Sleeve" value="<?= html_escape($hl['key'] ?? ''); ?>" required>
                        </td>
                        <td>
                          <input type="text" name="highlight_values[]" class="form-control form-control-sm" placeholder="e.g. Full Sleeve" value="<?= html_escape($hl['value'] ?? ''); ?>" required>
                        </td>
                        <td class="text-center">
                          <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeHighlightRow(this)" title="Delete row">
                            <i class="fa-solid fa-trash-can"></i>
                          </button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            <div class="mt-2">
              <button type="button" class="btn btn-sm btn-outline-primary" onclick="addHighlightRow()">
                <i class="fa-solid fa-plus me-1"></i> Add More Highlight
              </button>
            </div>
          </div>
        </div>

        <!-- Product Specifications Card -->
        <div class="card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div>
              <h5 class="card-title mb-0 d-flex align-items-center text-dark">
                <i class="fa-solid fa-list-check text-primary me-2"></i> Product Specifications
              </h5>
              <small class="text-muted">Detailed specifications (Brand, Category, Size, and Color are automatically included on the product page).</small>
            </div>
            <div class="form-check form-switch m-0">
              <input class="form-check-input" type="checkbox" id="enable_specifications" name="enable_specifications" value="1" <?= $has_specs ? 'checked' : ''; ?> onchange="toggleSpecificationsPanel(this.checked)" style="width: 2.5em; height: 1.3em; cursor: pointer;">
            </div>
          </div>
          <div class="card-body" id="specifications_panel" style="display: <?= $has_specs ? 'block' : 'none'; ?>;">
            <div class="table-responsive">
              <table class="table table-bordered table-sm align-middle no-datatable" id="specifications_table">
                <thead class="table-light">
                  <tr>
                    <th style="width: 45%;">Specification Name</th>
                    <th style="width: 45%;">Value</th>
                    <th style="width: 10%;" class="text-center">Action</th>
                  </tr>
                </thead>
                <tbody id="specifications_body">
                  <?php if ($has_specs): ?>
                    <?php foreach ($product['specifications'] as $sp): ?>
                      <tr>
                        <td>
                          <input type="text" name="spec_names[]" class="form-control form-control-sm" placeholder="e.g. Fabric" value="<?= html_escape($sp['spec_name'] ?? ''); ?>" required>
                        </td>
                        <td>
                          <input type="text" name="spec_values[]" class="form-control form-control-sm" placeholder="e.g. Cotton Blend" value="<?= html_escape($sp['spec_value'] ?? ''); ?>" required>
                        </td>
                        <td class="text-center">
                          <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSpecificationRow(this)" title="Delete row">
                            <i class="fa-solid fa-trash-can"></i>
                          </button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            <div class="mt-2">
              <button type="button" class="btn btn-sm btn-outline-primary" onclick="addSpecificationRow()">
                <i class="fa-solid fa-plus me-1"></i> Add Specification
              </button>
            </div>
          </div>
        </div>

      </div>

      <!-- Right Column: Organize, Category, Image (Sticky so only left side scrolls) -->
      <div class="col-12 col-lg-4">
        <div class="sticky-organization-wrapper">
          <div class="card mb-4 sticky-organization-card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
              <h5 class="card-title mb-0">Organization & Image</h5>
              <span class="badge bg-label-primary small">Media & Badges</span>
            </div>
            <div class="card-body">
              <?php $existing_gallery = json_decode($product['gallery_images'], true) ?: []; ?>
              <!-- Main Product Image -->
              <div class="mb-3">
                <label class="form-label fw-semibold" for="main_image_file">Main Product Image</label>
                <div class="border rounded p-2 mb-2 bg-light text-center">
                  <img
                    id="main_product_preview"
                    src="<?= base_url('../website/assets/images/' . $product['main_image']); ?>"
                    alt="Product Preview"
                    class="rounded img-fluid"
                    style="max-height: 140px; object-fit: contain;"
                    onerror="this.src='<?= base_url('assets/img/elements/1.jpg'); ?>'" />
                </div>
                <input
                  type="file"
                  class="form-control form-control-sm"
                  id="main_image_file"
                  name="main_image_file"
                  accept="image/*"
                  onchange="previewProductMainImage(this)" />
                <input type="hidden" name="current_main_image" value="<?= html_escape($product['main_image']); ?>" />
                <small class="text-muted d-block mt-1" style="font-size: 11px;">Upload a new image to replace the current main image.</small>
              </div>

              <!-- Additional Gallery Images -->
              <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <label class="form-label fw-semibold mb-0" for="gallery_files">Upload Gallery Images</label>
                </div>
                <input type="hidden" name="gallery_submitted" value="1" />
                <input
                  type="file"
                  class="form-control form-control-sm"
                  id="gallery_files"
                  name="gallery_files[]"
                  accept="image/*"
                  multiple
                  onchange="handleNewGalleryFiles(this)" />
                <small class="text-muted d-block mt-1" style="font-size: 11px;">Select multiple images for the product gallery. You can remove any image before saving.</small>

                <!-- New Uploads Preview Container with individual remove buttons -->
                <div id="new_gallery_container" class="mt-2" style="display: none;">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold text-primary" id="new_gallery_count_label">New Selected (0):</span>
                    <button type="button" class="btn btn-link text-danger p-0 small text-decoration-none" style="font-size: 11px;" onclick="clearAllNewGalleryFiles()">
                      <i class="fa-solid fa-trash-can me-1"></i>Clear All New
                    </button>
                  </div>
                  <div id="new_gallery_preview_list" class="d-flex flex-wrap gap-2"></div>
                </div>

                <!-- Existing Gallery Section -->
                <div class="mt-3">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-semibold text-secondary mb-0" id="existing_gallery_label">
                      Existing Gallery (<?= count($existing_gallery); ?>):
                    </label>
                  </div>
                  <div class="d-flex flex-wrap gap-2" id="existing_gallery_list">
                    <?php if (!empty($existing_gallery)): ?>
                      <?php foreach ($existing_gallery as $g_idx => $g_file): ?>
                        <div class="position-relative border rounded p-1 bg-white shadow-sm" id="gal_item_<?= $g_idx; ?>" style="width: 58px; height: 58px;">
                          <img src="<?= base_url('../website/assets/images/' . $g_file); ?>" class="w-100 h-100 object-fit-cover rounded" alt="Gallery" onerror="this.src='<?= base_url('assets/img/elements/1.jpg'); ?>'">
                          <input type="hidden" name="existing_gallery[]" value="<?= html_escape($g_file); ?>" id="gal_input_<?= $g_idx; ?>">
                          <button type="button" class="position-absolute d-flex align-items-center justify-content-center"
                            style="top: -6px; right: -6px; width: 20px; height: 20px; border-radius: 50%; background: #ff4d49; color: #fff; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.25); z-index: 10; cursor: pointer; padding: 0;"
                            onclick="removeExistingGallery(<?= $g_idx; ?>, '<?= html_escape($g_file); ?>')"
                            title="Remove from gallery">
                            <i class="fa-solid fa-xmark" style="font-size: 10px; line-height: 1;"></i>
                          </button>
                        </div>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <span class="text-muted small fst-italic" id="no_existing_gallery_msg">No existing gallery images.</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label" for="category_id">Category</label>
                <select class="form-select" id="category_id" name="category_id" required>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id']; ?>" <?= ($product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                      <?= html_escape($cat['breadcrumb_path'] ?? $cat['name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label" for="brand_id">Brand</label>
                <select class="form-select" id="brand_id" name="brand_id">
                  <option value="">None</option>
                  <?php foreach ($brands as $b): ?>
                    <option value="<?= $b['id']; ?>" <?= ($product['brand_id'] == $b['id']) ? 'selected' : ''; ?>>
                      <?= html_escape($b['name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label" for="status">Publication Status</label>
                <select class="form-select" id="status" name="status">
                  <option value="published" <?= ($product['status'] === 'published') ? 'selected' : ''; ?>>Published</option>
                  <option value="draft" <?= ($product['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                </select>
              </div>

              <div class="border-top pt-3">
                <div class="form-check mb-2">
                  <input class="form-check-input" type="checkbox" id="is_new" name="is_new" value="1" <?= ($product['is_new'] == 1) ? 'checked' : ''; ?> />
                  <label class="form-check-label" for="is_new">New Arrival</label>
                </div>
                <div class="form-check mb-2">
                  <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" <?= ($product['is_featured'] == 1 || $product['is_trending'] == 1) ? 'checked' : ''; ?> />
                  <label class="form-check-label" for="is_featured">Popular Products</label>
                </div>
                <div class="form-check mt-3 pt-2 border-top">
                  <input class="form-check-input" type="checkbox" id="is_cod_allowed" name="is_cod_allowed" value="1" <?= (!isset($product['is_cod_allowed']) || $product['is_cod_allowed'] == 1) ? 'checked' : ''; ?> />
                  <label class="form-check-label fw-semibold text-dark" for="is_cod_allowed">
                    <i class="fa-solid fa-truck-ramp-box text-success me-1"></i> Cash on Delivery (COD)
                  </label>
                  <small class="text-muted d-block" style="font-size: 11px;">Allow customers to order this product using Cash on Delivery.</small>
                </div>
              </div>
            </div>
            <div class="card-footer bg-white border-top py-3">
              <button type="submit" form="productEditForm" class="btn btn-primary w-100 shadow-sm py-2">
                <i class="fa-solid fa-floppy-disk me-1"></i> Update Product
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
function previewProductMainImage(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('main_product_preview').src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// DataTransfer container for newly selected gallery uploads
var newGalleryDT = new DataTransfer();

function handleNewGalleryFiles(input) {
  if (input.files && input.files.length > 0) {
    for (var i = 0; i < input.files.length; i++) {
      newGalleryDT.items.add(input.files[i]);
    }
    input.files = newGalleryDT.files;
    renderNewGalleryPreviews();
  }
}

function removeNewGalleryFile(idx) {
  newGalleryDT.items.remove(idx);
  var input = document.getElementById('gallery_files');
  if (input) {
    input.files = newGalleryDT.files;
  }
  renderNewGalleryPreviews();
}

function clearAllNewGalleryFiles() {
  newGalleryDT = new DataTransfer();
  var input = document.getElementById('gallery_files');
  if (input) {
    input.files = newGalleryDT.files;
    input.value = '';
  }
  renderNewGalleryPreviews();
}

function renderNewGalleryPreviews() {
  var container = document.getElementById('new_gallery_container');
  var list = document.getElementById('new_gallery_preview_list');
  var countLabel = document.getElementById('new_gallery_count_label');
  if (!container || !list) return;

  list.innerHTML = '';
  var count = newGalleryDT.files.length;

  if (count === 0) {
    container.style.display = 'none';
    return;
  }

  container.style.display = 'block';
  if (countLabel) {
    countLabel.textContent = 'New Selected (' + count + '):';
  }

  Array.from(newGalleryDT.files).forEach(function(file, idx) {
    var reader = new FileReader();
    reader.onload = function(e) {
      var thumb = document.createElement('div');
      thumb.className = 'position-relative border rounded p-1 bg-white shadow-sm';
      thumb.style.width = '58px';
      thumb.style.height = '58px';
      thumb.innerHTML = 
        '<img src="' + e.target.result + '" class="w-100 h-100 object-fit-cover rounded" alt="New Gallery Image">' +
        '<span class="badge bg-primary position-absolute bottom-0 start-0 p-0 text-center" style="font-size: 8px; width: 100%; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px; opacity: 0.9;">NEW</span>' +
        '<button type="button" class="position-absolute d-flex align-items-center justify-content-center" ' +
        'style="top: -6px; right: -6px; width: 20px; height: 20px; border-radius: 50%; background: #ff4d49; color: #fff; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.25); z-index: 10; cursor: pointer; padding: 0;" ' +
        'onclick="removeNewGalleryFile(' + idx + ')" title="Remove this file">' +
        '<i class="fa-solid fa-xmark" style="font-size: 10px; line-height: 1;"></i>' +
        '</button>';
      list.appendChild(thumb);
    };
    reader.readAsDataURL(file);
  });
}

function removeExistingGallery(idx, imagePath) {
  var item = document.getElementById('gal_item_' + idx);
  if (!item) return;

  if (!confirm('Are you sure you want to remove this image from the gallery?')) {
    return;
  }

  // Visual feedback
  item.style.opacity = '0.3';
  item.style.pointerEvents = 'none';

  // AJAX call to delete from DB and server
  var payload = new URLSearchParams();
  payload.append('product_id', '<?= $product["id"]; ?>');
  payload.append('image_path', imagePath);

  fetch('<?= site_url("products/delete_gallery_image"); ?>', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: payload.toString()
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    if (data.status === 'success') {
      item.remove();
      updateExistingGalleryUI(data.remaining_count);
    } else {
      alert(data.message || 'Error removing image');
      item.style.opacity = '1';
      item.style.pointerEvents = 'auto';
    }
  })
  .catch(function(err) {
    // If network fails, remove from DOM so form submit will still exclude it
    item.remove();
    updateExistingGalleryUI();
  });
}

function updateExistingGalleryUI(remaining) {
  var list = document.getElementById('existing_gallery_list');
  var label = document.getElementById('existing_gallery_label');
  var remainingItems = list ? list.querySelectorAll('[id^="gal_item_"]') : [];
  var count = (remaining !== undefined) ? remaining : remainingItems.length;

  if (label) {
    label.textContent = 'Existing Gallery (' + count + '):';
  }

  if (count === 0 && list) {
    if (!document.getElementById('no_existing_gallery_msg')) {
      var msg = document.createElement('span');
      msg.className = 'text-muted small fst-italic';
      msg.id = 'no_existing_gallery_msg';
      msg.textContent = 'No existing gallery images.';
      list.appendChild(msg);
    }
  }
}

// Highlights & Specifications dynamic management
function toggleHighlightsPanel(checked) {
  var panel = document.getElementById('highlights_panel');
  if (!panel) return;
  panel.style.display = checked ? 'block' : 'none';
  if (checked) {
    var tbody = document.getElementById('highlights_body');
    if (tbody && tbody.children.length === 0) {
      addHighlightRow('Sleeve', 'Full Sleeve');
    }
  }
}

function addHighlightRow(key = '', val = '') {
  var tbody = document.getElementById('highlights_body');
  if (!tbody) return;
  var tr = document.createElement('tr');
  tr.innerHTML = `
    <td>
      <input type="text" name="highlight_keys[]" class="form-control form-control-sm" placeholder="e.g. Sleeve" value="${escapeHtml(key)}" required>
    </td>
    <td>
      <input type="text" name="highlight_values[]" class="form-control form-control-sm" placeholder="e.g. Full Sleeve" value="${escapeHtml(val)}" required>
    </td>
    <td class="text-center">
      <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeHighlightRow(this)" title="Delete row">
        <i class="fa-solid fa-trash-can"></i>
      </button>
    </td>
  `;
  tbody.appendChild(tr);
}

function removeHighlightRow(btn) {
  var tr = btn.closest('tr');
  if (tr) tr.remove();
}

function toggleSpecificationsPanel(checked) {
  var panel = document.getElementById('specifications_panel');
  if (!panel) return;
  panel.style.display = checked ? 'block' : 'none';
  if (checked) {
    var tbody = document.getElementById('specifications_body');
    if (tbody && tbody.children.length === 0) {
      addSpecificationRow('Fabric', 'Cotton Blend');
    }
  }
}

function addSpecificationRow(name = '', val = '') {
  var tbody = document.getElementById('specifications_body');
  if (!tbody) return;
  var tr = document.createElement('tr');
  tr.innerHTML = `
    <td>
      <input type="text" name="spec_names[]" class="form-control form-control-sm" placeholder="e.g. Fabric" value="${escapeHtml(name)}" required>
    </td>
    <td>
      <input type="text" name="spec_values[]" class="form-control form-control-sm" placeholder="e.g. Cotton Blend" value="${escapeHtml(val)}" required>
    </td>
    <td class="text-center">
      <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSpecificationRow(this)" title="Delete row">
        <i class="fa-solid fa-trash-can"></i>
      </button>
    </td>
  `;
  tbody.appendChild(tr);
}

function removeSpecificationRow(btn) {
  var tr = btn.closest('tr');
  if (tr) tr.remove();
}

function escapeHtml(text) {
  if (!text) return '';
  return text
    .toString()
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}
</script>
