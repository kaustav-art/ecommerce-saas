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
.add-size-chip:hover, .add-multi-chip:hover {
  border-color: #666cff !important;
}
</style>

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><span class="text-muted fw-light">Products /</span> Add Product</h4>
    <div class="d-flex gap-2">
      <a href="<?= site_url('products'); ?>" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Products
      </a>
      <button type="submit" form="productAddForm" class="btn btn-primary d-none d-sm-inline-flex align-items-center">
        <i class="fa-solid fa-floppy-disk me-1"></i> Save Product
      </button>
    </div>
  </div>

  <form action="<?= site_url('products/add'); ?>" method="POST" enctype="multipart/form-data" id="productAddForm">
    <div class="row">
      <!-- Left Column: Main info -->
      <div class="col-12 col-lg-8">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">Product Information</h5>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label" for="title">Product Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="title" name="title" placeholder="e.g. Linen Summer Dress" required />
            </div>
            <div class="mb-3">
              <label class="form-label" for="short_description">Short Description</label>
              <textarea class="form-control" id="short_description" name="short_description" rows="2" placeholder="Brief summary displayed on listings..."></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label" for="description">Full Description</label>
              <textarea class="form-control" id="description" name="description" rows="5" placeholder="Detailed product specifications, materials, care instructions..."></textarea>
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
                  <input type="number" step="0.01" class="form-control" id="price" name="price" placeholder="99.00" required />
                </div>
              </div>
              <div class="col-md-6 mb-3" id="add_sale_price_wrapper">
                <label class="form-label" for="sale_price">Sale Price (Optional)</label>
                <div class="input-group">
                  <span class="input-group-text"><?= html_escape($currency_symbol ?? '$'); ?></span>
                  <input type="number" step="0.01" class="form-control" id="sale_price" name="sale_price" placeholder="79.00" />
                </div>
                <small class="text-muted d-block mt-1" style="font-size: 11px;">Active when no size attributes are selected.</small>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label" for="sku">SKU Code <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="sku" name="sku" placeholder="e.g. MOD-W-109" required oninput="syncAddSkuWithVariants()" />
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label" for="stock_quantity">Stock Quantity <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" value="50" required />
                <small class="text-muted d-block" id="stock_qty_helper" style="font-size: 11px;">Default product inventory. When sizes are selected below, this auto-sums all size stocks.</small>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold" for="max_purchase_quantity">Max Purchase Quantity (Per Order)</label>
                <input type="number" min="1" class="form-control" id="max_purchase_quantity" name="max_purchase_quantity" value="5" placeholder="e.g. 5" />
                <small class="text-muted d-block" style="font-size: 11px;">How many products user can purchase together in one order (e.g. 5).</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Assign Attributes & Product Variants Card -->
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0 d-flex align-items-center text-dark">
              <i class="fa-solid fa-tags text-primary me-2"></i> Assign Attributes & Variants
            </h5>
            <small class="text-muted">Assign attributes (Color, Size, etc.) and manage stock by size to create product variants automatically.</small>
          </div>
          <div class="card-body" id="variants_config_panel">
            <?php if (!empty($attributes)): ?>
              <div class="row">
                <?php 
                  $multi_select_attributes = [];
                  $single_attributes = [];

                  foreach ($attributes as $attr) {
                    $is_size = (strcasecmp($attr['slug'], 'size') === 0 || strcasecmp($attr['name'], 'size') === 0);
                    $is_multi = in_array(strtolower($attr['type'] ?? ''), ['multiple_select', 'multiselect', 'multiple']) || $is_size;

                    if ($is_multi) {
                      $multi_select_attributes[] = $attr;
                    } else {
                      $single_attributes[] = $attr;
                    }
                  }
                ?>

                <!-- Non-Size / Single-Select Attributes (Color, Material, etc.) -->
                <?php if (!empty($single_attributes)): ?>
                  <div class="col-12 mb-3">
                    <label class="form-label fw-semibold text-dark">Assign Attributes (e.g. Color, Material):</label>
                    <div class="row g-3">
                      <?php foreach ($single_attributes as $attr): ?>
                        <div class="col-md-6">
                          <div class="p-2 border rounded bg-light">
                            <label class="form-label small fw-bold mb-1" for="add_attr_val_<?= $attr['id']; ?>">
                              <?= html_escape($attr['name']); ?>
                            </label>
                            <select class="form-select form-select-sm" name="attr_vals[<?= $attr['id']; ?>]" id="add_attr_val_<?= $attr['id']; ?>" onchange="updateVariantNameAndSkuPreview()">
                              <option value="">-- None / Select <?= html_escape($attr['name']); ?> --</option>
                              <?php if (!empty($attr['values'])): ?>
                                <?php foreach ($attr['values'] as $v): ?>
                                  <option value="<?= $v['id']; ?>" data-name="<?= html_escape($v['value']); ?>" data-color="<?= html_escape($v['color_code'] ?? ''); ?>">
                                    <?= html_escape($v['value']); ?>
                                  </option>
                                <?php endforeach; ?>
                              <?php endif; ?>
                            </select>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                <?php endif; ?>

                <!-- Multiple-Selection Attributes (e.g. Size for Apparel, Storage for Phones) -->
                <?php if (!empty($multi_select_attributes)): ?>
                  <div class="col-12 mb-2">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                      <label class="form-label fw-semibold text-dark mb-0">
                        Multiple Selection Attribute (Variants & Stock Dimension):
                      </label>
                      <small class="text-muted" style="font-size: 11px;">
                        <i class="fa-solid fa-circle-info text-info me-1"></i> Only one Multiple Selection attribute can be active per product (e.g. Size for apparel or Storage for phones).
                      </small>
                    </div>
                  </div>

                  <?php foreach ($multi_select_attributes as $m_attr): 
                    $m_is_size = (strcasecmp($m_attr['slug'], 'size') === 0 || strcasecmp($m_attr['name'], 'size') === 0);
                    $m_icon = $m_is_size ? 'fa-ruler-combined' : (strcasecmp($m_attr['slug'], 'storage') === 0 ? 'fa-hard-drive' : 'fa-tags');
                  ?>
                    <div class="col-12 mb-3 multi-attr-section" id="multi_attr_section_<?= $m_attr['id']; ?>" data-attr-id="<?= $m_attr['id']; ?>" data-attr-name="<?= html_escape($m_attr['name']); ?>">
                      <div class="p-3 border rounded bg-light position-relative" id="multi_attr_card_<?= $m_attr['id']; ?>">
                        
                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                          <div>
                            <label class="form-label fw-bold mb-0 text-dark d-flex align-items-center flex-wrap gap-1">
                              <i class="fa-solid <?= $m_icon; ?> text-info me-1"></i> Assign <?= html_escape($m_attr['name']); ?>
                              <span class="badge bg-label-info ms-1" style="font-size: 10px;">Multiple Selection</span>
                              <span class="badge bg-label-success ms-1 active-multi-badge" id="active_multi_badge_<?= $m_attr['id']; ?>" style="font-size: 10px; display: none;">
                                <i class="fa-solid fa-check me-1"></i>Active for Product
                              </span>
                            </label>
                            <small class="text-muted d-block" style="font-size: 11px;">Select all <?= strtolower(html_escape($m_attr['name'])); ?> options available for this product.</small>
                          </div>
                          <div class="d-flex gap-2 align-items-center">
                            <a href="javascript:void(0);" class="small text-primary text-decoration-none fw-semibold multi-select-all-btn" id="multi_select_all_<?= $m_attr['id']; ?>" onclick="selectAllMultiAttr(<?= $m_attr['id']; ?>, true)">Select All</a>
                            <span class="text-muted small">|</span>
                            <a href="javascript:void(0);" class="small text-secondary text-decoration-none multi-clear-btn" id="multi_clear_<?= $m_attr['id']; ?>" onclick="selectAllMultiAttr(<?= $m_attr['id']; ?>, false)">Clear</a>
                          </div>
                        </div>

                        <!-- Locked notice if another multi attribute is active -->
                        <div class="alert alert-warning py-2 px-3 mb-2 small align-items-center justify-content-between multi-attr-locked-alert" id="multi_locked_alert_<?= $m_attr['id']; ?>" style="display: none;">
                          <div>
                            <i class="fa-solid fa-lock me-1 text-warning"></i>
                            <span id="multi_locked_msg_<?= $m_attr['id']; ?>">Another attribute is currently selected. Only one Multiple Selection attribute can be chosen per product.</span>
                          </div>
                          <button type="button" class="btn btn-xs btn-outline-warning ms-2" onclick="switchToMultiAttr(<?= $m_attr['id']; ?>)">
                            Switch to <?= html_escape($m_attr['name']); ?>
                          </button>
                        </div>

                        <!-- Interactive Chips -->
                        <div class="d-flex flex-wrap gap-2 mb-2" id="multi-chips-wrapper-<?= $m_attr['id']; ?>">
                          <?php if (!empty($m_attr['values'])): ?>
                            <?php foreach ($m_attr['values'] as $v): ?>
                              <div 
                                class="border rounded px-3 py-1 text-center add-multi-chip multi-chip-<?= $m_attr['id']; ?>" 
                                id="multi_chip_<?= $m_attr['id']; ?>_<?= $v['id']; ?>" 
                                style="cursor: pointer; min-width: 44px; font-size: 13px; font-weight: 600; user-select: none; background: #fff; border-color: #d4d5d9; color: #515569; transition: all 0.15s ease;"
                                onclick="handleMultiChipClick(<?= $m_attr['id']; ?>, <?= $v['id']; ?>, '<?= html_escape($v['value']); ?>')"
                              >
                                <input 
                                  type="checkbox" 
                                  name="multi_vals[]" 
                                  value="<?= $v['id']; ?>" 
                                  data-val="<?= html_escape($v['value']); ?>" 
                                  data-color="<?= html_escape($v['color_code'] ?? ''); ?>" 
                                  data-attr-id="<?= $m_attr['id']; ?>"
                                  data-attr-name="<?= html_escape($m_attr['name']); ?>"
                                  id="multi_input_<?= $m_attr['id']; ?>_<?= $v['id']; ?>" 
                                  class="d-none add-multi-checkbox multi-checkbox-<?= $m_attr['id']; ?>"
                                  disabled
                                />
                                <?php if (!empty($v['color_code'])): ?>
                                  <span class="d-inline-block rounded-circle me-1 border shadow-sm" style="width: 12px; height: 12px; vertical-align: middle; background-color: <?= html_escape($v['color_code']); ?>;"></span>
                                <?php endif; ?>
                                <span><?= html_escape($v['value']); ?></span>
                              </div>
                            <?php endforeach; ?>
                          <?php else: ?>
                            <small class="text-muted">No values created yet for <?= html_escape($m_attr['name']); ?>. <a href="<?= site_url('attributes/values/' . $m_attr['id']); ?>" target="_blank">Add values</a></small>
                          <?php endif; ?>
                        </div>

                      </div>
                    </div>
                  <?php endforeach; ?>

                  <!-- Hidden field to pass the single active multiple attribute ID -->
                  <input type="hidden" name="active_multi_attr_id" id="active_multi_attr_id" value="">
                <?php endif; ?>

                <!-- Dynamic Stock & Price Management Table for Active Multi-Select Dimension -->
                <div class="col-12 mb-3" id="add-multi-stock-manager" style="display: none;">
                  <div class="p-3 bg-white rounded border">
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom flex-wrap gap-2">
                      <div>
                        <span class="fw-bold text-dark fs-6">
                          <i class="fa-solid fa-boxes-stacked text-primary me-1"></i> <span id="add-multi-stock-title">Manage Stock by Option</span>
                        </span>
                        <small class="text-muted d-block" id="add-multi-stock-subtitle" style="font-size: 11px;">Specify individual inventory quantity and sale price for each selected option.</small>
                      </div>
                      <div class="d-flex align-items-center gap-2 flex-wrap">
                        <!-- Quick Bulk Stock -->
                        <div class="input-group input-group-sm" style="width: 150px;">
                          <input type="number" min="0" class="form-control" id="add_quick_stock_val" placeholder="Qty" value="10">
                          <button type="button" class="btn btn-outline-primary" onclick="applyAddStockToAll()" title="Apply to all selected">Apply All</button>
                        </div>
                        <!-- Quick Bulk Sale Price -->
                        <div class="input-group input-group-sm" style="width: 170px;">
                          <span class="input-group-text"><?= html_escape($currency_symbol ?? '$'); ?></span>
                          <input type="number" step="0.01" min="0" class="form-control" id="add_quick_sale_price_val" placeholder="Sale Price">
                          <button type="button" class="btn btn-outline-primary" onclick="applyAddSalePriceToAll()" title="Apply to all selected">Apply All</button>
                        </div>
                      </div>
                    </div>

                    <!-- Table of Selected Multi Attribute Values for Stock -->
                    <div class="table-responsive">
                      <table class="table table-sm table-bordered align-middle mb-0 no-datatable">
                        <thead class="table-light">
                          <tr>
                            <th style="width: 110px;" class="text-center" id="add-multi-stock-th-label">Option</th>
                            <th>Variant SKU</th>
                            <th style="width: 160px;">Stock Quantity</th>
                            <th style="width: 160px;">Sale Price (<?= html_escape($currency_symbol ?? '$'); ?>)</th>
                            <th style="width: 110px;" class="text-center">Status</th>
                          </tr>
                        </thead>
                        <tbody id="add-multi-stock-tbody">
                          <!-- dynamically populated by JS -->
                        </tbody>
                      </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top flex-wrap gap-2">
                      <span class="text-muted small" id="add-selected-multi-count">0 options selected</span>
                      <div class="text-end">
                        <small class="text-muted">Total Stock for this product: </small>
                        <strong class="text-primary fs-6" id="add-total-multi-stock">0 units</strong>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            <?php else: ?>
              <p class="text-muted mb-0">No attributes found. Create attributes in Catalog > Attributes first.</p>
            <?php endif; ?>
          </div>
        </div>

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
              <input class="form-check-input" type="checkbox" id="enable_highlights" name="enable_highlights" value="1" onchange="toggleHighlightsPanel(this.checked)" style="width: 2.5em; height: 1.3em; cursor: pointer;">
            </div>
          </div>
          <div class="card-body" id="highlights_panel" style="display: none;">
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
              <input class="form-check-input" type="checkbox" id="enable_specifications" name="enable_specifications" value="1" onchange="toggleSpecificationsPanel(this.checked)" style="width: 2.5em; height: 1.3em; cursor: pointer;">
            </div>
          </div>
          <div class="card-body" id="specifications_panel" style="display: none;">
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
              <h5 class="card-title mb-0">Organization</h5>
              <span class="badge bg-label-primary small">Media & Badges</span>
            </div>
            <div class="card-body">
              <div class="mb-3">
                <label class="form-label" for="category_id">Category <span class="text-danger">*</span></label>
                <select class="form-select" id="category_id" name="category_id" required>
                  <option value="">Select Category</option>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id']; ?>"><?= html_escape($cat['breadcrumb_path'] ?? $cat['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label" for="brand_id">Brand</label>
                <select class="form-select" id="brand_id" name="brand_id">
                  <option value="">Select Brand</option>
                  <?php foreach ($brands as $b): ?>
                    <option value="<?= $b['id']; ?>"><?= html_escape($b['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label" for="status">Publication Status</label>
                <select class="form-select" id="status" name="status">
                  <option value="published" selected>Published</option>
                  <option value="draft">Draft</option>
                </select>
              </div>

              <!-- Main Product Image File Upload -->
              <div class="mb-3">
                <label class="form-label fw-semibold" for="main_image_file">Main Product Image</label>
                <div class="border rounded p-2 mb-2 bg-light text-center">
                  <img
                    id="main_product_preview"
                    src="<?= base_url('../website/assets/images/products/womens/women-1.jpg'); ?>"
                    class="rounded img-fluid"
                    style="max-height: 140px; object-fit: contain;"
                    alt="Product Image Preview"
                  />
                </div>
                <input
                  type="file"
                  class="form-control form-control-sm"
                  id="main_image_file"
                  name="main_image_file"
                  accept="image/*"
                  onchange="previewMainProductFile(this)"
                />
                <input type="hidden" name="default_main_image" value="products/womens/women-1.jpg" />
                <small class="text-muted d-block mt-1" style="font-size: 11px;">Recommended: 800x1000px, JPG, PNG, WEBP (Max 10MB)</small>
              </div>

              <!-- Additional Gallery Images File Upload -->
              <div class="mb-3">
                <label class="form-label fw-semibold" for="gallery_files">Upload Gallery Images</label>
                <input
                  type="file"
                  class="form-control form-control-sm"
                  id="gallery_files"
                  name="gallery_files[]"
                  accept="image/*"
                  multiple
                  onchange="handleNewGalleryFiles(this)"
                />
                <small class="text-muted d-block mt-1" style="font-size: 11px;">Select multiple images for the product gallery.</small>
                <div id="new_gallery_container" class="mt-2" style="display: none;">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold text-primary" id="new_gallery_count_label">New Selected (0):</span>
                    <button type="button" class="btn btn-link text-danger p-0 small text-decoration-none" style="font-size: 11px;" onclick="clearAllNewGalleryFiles()">
                      <i class="fa-solid fa-trash-can me-1"></i>Clear All
                    </button>
                  </div>
                  <div id="new_gallery_preview_list" class="d-flex flex-wrap gap-2"></div>
                </div>
              </div>

              <div class="border-top pt-3">
                <div class="form-check mb-2">
                  <input class="form-check-input" type="checkbox" id="is_new" name="is_new" value="1" checked />
                  <label class="form-check-label" for="is_new">New Arrival</label>
                </div>
                <div class="form-check mb-2">
                  <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" />
                  <label class="form-check-label" for="is_featured">Popular Products</label>
                </div>
                <div class="form-check mt-3 pt-2 border-top">
                  <input class="form-check-input" type="checkbox" id="is_cod_allowed" name="is_cod_allowed" value="1" checked />
                  <label class="form-check-label fw-semibold text-dark" for="is_cod_allowed">
                    <i class="fa-solid fa-truck-ramp-box text-success me-1"></i> Cash on Delivery (COD)
                  </label>
                  <small class="text-muted d-block" style="font-size: 11px;">Allow customers to order this product using Cash on Delivery.</small>
                </div>
              </div>
            </div>
            <div class="card-footer bg-white border-top py-3">
              <button type="submit" form="productAddForm" class="btn btn-primary w-100 shadow-sm py-2">
                <i class="fa-solid fa-floppy-disk me-1"></i> Save Product
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
function previewMainProductFile(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('main_product_preview').src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}

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

// --- Exclusive Multiple-Selection Attribute Dimension & Dynamic Stock/Price Management ---
var currentActiveMultiAttrId = null;

function handleMultiChipClick(attrId, valId, valName) {
  // If another multi attribute has selections, enforce "only one Multiple Selection can select not both"
  if (currentActiveMultiAttrId && currentActiveMultiAttrId !== attrId) {
    var activeCard = document.getElementById('multi_attr_section_' + currentActiveMultiAttrId);
    var activeName = activeCard ? activeCard.getAttribute('data-attr-name') : 'another attribute';
    var targetCard = document.getElementById('multi_attr_section_' + attrId);
    var targetName = targetCard ? targetCard.getAttribute('data-attr-name') : 'this attribute';

    if (!confirm('Only one Multiple Selection attribute can be active per product (e.g. Size for apparel or Storage for phones).\n\nSwitching to "' + targetName + '" will clear your "' + activeName + '" selections. Proceed?')) {
      return;
    }
    clearMultiAttrSelections(currentActiveMultiAttrId);
  }

  var cb = document.getElementById('multi_input_' + attrId + '_' + valId);
  var chip = document.getElementById('multi_chip_' + attrId + '_' + valId);
  if (!cb || !chip) return;

  cb.checked = !cb.checked;
  cb.disabled = !cb.checked; // Only checked chips submit in POST
  updateChipStyle(chip, cb.checked);

  // Check how many chips are selected in this attribute
  var checkedInThis = document.querySelectorAll('.multi-checkbox-' + attrId + ':checked');
  if (checkedInThis.length > 0) {
    currentActiveMultiAttrId = attrId;
    document.getElementById('active_multi_attr_id').value = attrId;
  } else {
    currentActiveMultiAttrId = null;
    document.getElementById('active_multi_attr_id').value = '';
  }

  updateMultiAttrCardsState();
  renderMultiStockTable();
  updateVariantNameAndSkuPreview();
}

function selectAllMultiAttr(attrId, enable) {
  if (enable && currentActiveMultiAttrId && currentActiveMultiAttrId !== attrId) {
    var activeCard = document.getElementById('multi_attr_section_' + currentActiveMultiAttrId);
    var activeName = activeCard ? activeCard.getAttribute('data-attr-name') : 'another attribute';
    var targetCard = document.getElementById('multi_attr_section_' + attrId);
    var targetName = targetCard ? targetCard.getAttribute('data-attr-name') : 'this attribute';

    if (!confirm('Only one Multiple Selection attribute can be active per product.\n\nSwitching to "' + targetName + '" will clear "' + activeName + '". Proceed?')) {
      return;
    }
    clearMultiAttrSelections(currentActiveMultiAttrId);
  }

  document.querySelectorAll('.multi-checkbox-' + attrId).forEach(function(cb) {
    cb.checked = enable;
    cb.disabled = !enable;
    var chip = document.getElementById('multi_chip_' + attrId + '_' + cb.value);
    if (chip) updateChipStyle(chip, enable);
  });

  if (enable) {
    currentActiveMultiAttrId = attrId;
    document.getElementById('active_multi_attr_id').value = attrId;
  } else {
    if (currentActiveMultiAttrId === attrId) {
      currentActiveMultiAttrId = null;
      document.getElementById('active_multi_attr_id').value = '';
    }
  }

  updateMultiAttrCardsState();
  renderMultiStockTable();
  updateVariantNameAndSkuPreview();
}

function switchToMultiAttr(attrId) {
  if (currentActiveMultiAttrId && currentActiveMultiAttrId !== attrId) {
    clearMultiAttrSelections(currentActiveMultiAttrId);
  }
  currentActiveMultiAttrId = attrId;
  document.getElementById('active_multi_attr_id').value = attrId;
  selectAllMultiAttr(attrId, true);
}

function clearMultiAttrSelections(attrId) {
  document.querySelectorAll('.multi-checkbox-' + attrId).forEach(function(cb) {
    cb.checked = false;
    cb.disabled = true;
    var chip = document.getElementById('multi_chip_' + attrId + '_' + cb.value);
    if (chip) updateChipStyle(chip, false);
  });
}

function updateChipStyle(chip, isChecked) {
  if (isChecked) {
    chip.style.background = '#666cff';
    chip.style.borderColor = '#666cff';
    chip.style.color = '#fff';
  } else {
    chip.style.background = '#fff';
    chip.style.borderColor = '#d4d5d9';
    chip.style.color = '#515569';
  }
}

function updateMultiAttrCardsState() {
  var activeCard = currentActiveMultiAttrId ? document.getElementById('multi_attr_section_' + currentActiveMultiAttrId) : null;
  var activeName = activeCard ? activeCard.getAttribute('data-attr-name') : '';

  document.querySelectorAll('.multi-attr-section').forEach(function(sec) {
    var secAttrId = parseInt(sec.getAttribute('data-attr-id'), 10);
    var badge = document.getElementById('active_multi_badge_' + secAttrId);
    var alert = document.getElementById('multi_locked_alert_' + secAttrId);
    var lockedMsg = document.getElementById('multi_locked_msg_' + secAttrId);
    var card = document.getElementById('multi_attr_card_' + secAttrId);

    if (!currentActiveMultiAttrId) {
      if (badge) badge.style.display = 'none';
      if (alert) alert.style.display = 'none';
      if (card) { card.style.opacity = '1'; card.classList.remove('border-primary'); }
    } else if (currentActiveMultiAttrId === secAttrId) {
      if (badge) badge.style.display = 'inline-flex';
      if (alert) alert.style.display = 'none';
      if (card) { card.style.opacity = '1'; card.classList.add('border-primary'); }
    } else {
      if (badge) badge.style.display = 'none';
      if (alert) {
        alert.style.display = 'flex';
        if (lockedMsg) {
          lockedMsg.textContent = activeName + ' is currently selected. Only one Multiple Selection attribute can be chosen per product.';
        }
      }
      if (card) { card.style.opacity = '0.75'; card.classList.remove('border-primary'); }
    }
  });
}

// Render dynamic stock-by-attribute table (supports Size, Storage, RAM, etc.)
function renderMultiStockTable() {
  var container = document.getElementById('add-multi-stock-manager');
  var tbody = document.getElementById('add-multi-stock-tbody');
  var countEl = document.getElementById('add-selected-multi-count');
  var salePriceWrapper = document.getElementById('add_sale_price_wrapper');
  var titleEl = document.getElementById('add-multi-stock-title');
  var subtitleEl = document.getElementById('add-multi-stock-subtitle');
  var thLabel = document.getElementById('add-multi-stock-th-label');
  if (!container || !tbody) return;

  if (!currentActiveMultiAttrId) {
    container.style.display = 'none';
    if (salePriceWrapper) salePriceWrapper.style.display = 'block';
    if (countEl) countEl.textContent = '0 options selected';
    tbody.innerHTML = '';
    recalcAddTotalStock();
    return;
  }

  var activeCard = document.getElementById('multi_attr_section_' + currentActiveMultiAttrId);
  var attrName = activeCard ? activeCard.getAttribute('data-attr-name') : 'Option';
  var checkedCbs = document.querySelectorAll('.multi-checkbox-' + currentActiveMultiAttrId + ':checked');

  if (checkedCbs.length === 0) {
    container.style.display = 'none';
    if (salePriceWrapper) salePriceWrapper.style.display = 'block';
    if (countEl) countEl.textContent = '0 options selected';
    tbody.innerHTML = '';
    recalcAddTotalStock();
    return;
  }

  container.style.display = 'block';
  if (salePriceWrapper) salePriceWrapper.style.display = 'none';
  if (titleEl) titleEl.textContent = 'Manage Stock by ' + attrName;
  if (subtitleEl) subtitleEl.textContent = 'Specify individual inventory quantity and sale price for each selected ' + attrName.toLowerCase() + '.';
  if (thLabel) thLabel.textContent = attrName;
  if (countEl) countEl.textContent = checkedCbs.length + ' ' + attrName.toLowerCase() + ' option(s) selected';

  var baseSku = (document.getElementById('sku') ? document.getElementById('sku').value.trim() : '') || 'SKU';
  var defaultSalePrice = (document.getElementById('sale_price') ? document.getElementById('sale_price').value.trim() : '') || '';

  // Preserve existing inputs
  var currentVals = {};
  document.querySelectorAll('.add-multi-qty-input').forEach(function(inp) {
    var vid = inp.getAttribute('data-val-id');
    currentVals[vid] = currentVals[vid] || {};
    currentVals[vid].stock = inp.value;
  });
  document.querySelectorAll('.add-multi-sale-price-input').forEach(function(inp) {
    var vid = inp.getAttribute('data-val-id');
    currentVals[vid] = currentVals[vid] || {};
    currentVals[vid].sale_price = inp.value;
  });

  tbody.innerHTML = '';
  checkedCbs.forEach(function(cb) {
    var valId = cb.value;
    var valName = cb.getAttribute('data-val') || '';
    var existingQty = (currentVals[valId] && currentVals[valId].stock !== undefined && currentVals[valId].stock !== '') ? currentVals[valId].stock : '10';
    var existingSalePrice = (currentVals[valId] && currentVals[valId].sale_price !== undefined && currentVals[valId].sale_price !== '') ? currentVals[valId].sale_price : defaultSalePrice;
    
    var cleanSuffix = valName.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
    var vSku = baseSku + '-' + cleanSuffix;
    var isOutOfStock = parseInt(existingQty, 10) === 0;

    var tr = document.createElement('tr');
    tr.id = 'add_multi_row_' + valId;
    tr.innerHTML = 
      '<td class="text-center"><span class="badge bg-primary fw-bold fs-6">' + escapeHtml(valName) + '</span></td>' +
      '<td><code class="add-multi-sku-preview">' + escapeHtml(vSku) + '</code></td>' +
      '<td>' +
        '<div class="input-group input-group-sm">' +
          '<span class="input-group-text"><i class="fa-solid fa-boxes-stacked" style="font-size: 10px;"></i></span>' +
          '<input type="number" min="0" class="form-control text-end add-multi-qty-input" name="multi_stock[' + valId + ']" data-val-id="' + valId + '" value="' + escapeHtml(existingQty) + '" placeholder="0" oninput="recalcAddTotalStock()" onblur="if(this.value.trim()===\'\') { this.value=\'0\'; recalcAddTotalStock(); }">' +
        '</div>' +
      '</td>' +
      '<td>' +
        '<div class="input-group input-group-sm">' +
          '<span class="input-group-text">' + escapeHtml(<?= json_encode($currency_symbol ?? '$'); ?>) + '</span>' +
          '<input type="number" step="0.01" min="0" class="form-control text-end add-multi-sale-price-input" name="multi_sale_price[' + valId + ']" data-val-id="' + valId + '" value="' + escapeHtml(existingSalePrice) + '" placeholder="' + escapeHtml(defaultSalePrice || '0.00') + '">' +
        '</div>' +
      '</td>' +
      '<td class="text-center">' +
        '<span class="badge ' + (isOutOfStock ? 'bg-label-danger' : 'bg-label-success') + ' add-multi-status-badge">' +
          (isOutOfStock ? 'Out of stock' : 'In stock') +
        '</span>' +
      '</td>';
    tbody.appendChild(tr);
  });

  recalcAddTotalStock();
}

function applyAddStockToAll() {
  var val = document.getElementById('add_quick_stock_val') ? document.getElementById('add_quick_stock_val').value : '10';
  document.querySelectorAll('.add-multi-qty-input').forEach(function(inp) {
    inp.value = val;
  });
  recalcAddTotalStock();
}

function applyAddSalePriceToAll() {
  var val = document.getElementById('add_quick_sale_price_val') ? document.getElementById('add_quick_sale_price_val').value : '';
  document.querySelectorAll('.add-multi-sale-price-input').forEach(function(inp) {
    inp.value = val;
  });
}

function recalcAddTotalStock() {
  var total = 0;
  var qtyInputs = document.querySelectorAll('.add-multi-qty-input');
  if (qtyInputs.length > 0) {
    qtyInputs.forEach(function(inp) {
      var qty = parseInt(inp.value, 10) || 0;
      total += qty;
      var row = inp.closest('tr');
      if (row) {
        var badge = row.querySelector('.add-multi-status-badge');
        if (badge) {
          if (qty <= 0) {
            badge.className = 'badge bg-label-danger add-multi-status-badge';
            badge.textContent = 'Out of stock';
          } else {
            badge.className = 'badge bg-label-success add-multi-status-badge';
            badge.textContent = 'In stock';
          }
        }
      }
    });

    var totalEl = document.getElementById('add-total-multi-stock');
    if (totalEl) totalEl.textContent = total + ' units';

    var mainStockInp = document.getElementById('stock_quantity');
    if (mainStockInp) mainStockInp.value = total;
  } else {
    var totalEl = document.getElementById('add-total-multi-stock');
    if (totalEl) totalEl.textContent = '0 units';
  }
}

function syncAddSkuWithVariants() {
  var baseSku = (document.getElementById('sku') ? document.getElementById('sku').value.trim() : '') || 'SKU';
  document.querySelectorAll('#add-multi-stock-tbody tr').forEach(function(row) {
    var badge = row.querySelector('span.badge.bg-primary');
    var skuCode = row.querySelector('.add-multi-sku-preview');
    if (badge && skuCode) {
      var cleanSuffix = badge.textContent.trim().replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
      skuCode.textContent = baseSku + '-' + cleanSuffix;
    }
  });
}

function updateVariantNameAndSkuPreview() {
  syncAddSkuWithVariants();
}

// Backward compatibility wrappers
function toggleAddSizeChip(valId, valName) {
  handleMultiChipClick(currentActiveMultiAttrId || 2, valId, valName);
}
function selectAllAddSizes(enable) {
  selectAllMultiAttr(currentActiveMultiAttrId || 2, enable);
}
function toggleAddMultiChip(attrId, valId, valName) {
  handleMultiChipClick(attrId, valId, valName);
}
function selectAllAddMultiAttr(attrId, enable) {
  selectAllMultiAttr(attrId, enable);
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
