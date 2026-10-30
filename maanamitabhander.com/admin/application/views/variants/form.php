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

.var-size-chip {
  cursor: pointer;
  min-width: 44px;
  font-size: 13px;
  font-weight: 600;
  user-select: none;
  background: #fff;
  border: 1px solid #d4d5d9;
  color: #515569;
  transition: all 0.15s ease;
}
.var-size-chip.active {
  background: #666cff !important;
  border-color: #666cff !important;
  color: #fff !important;
}
</style>

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h4 class="fw-bold m-0">
        <span class="text-muted fw-light">Catalog / <a href="<?= site_url('products'); ?>" class="text-muted">Products</a> / <a href="<?= site_url('variants/product/' . $product['id']); ?>" class="text-muted">Variants</a> /</span>
        <?= !empty($is_edit) ? 'Edit Variant' : 'Add Product Variant'; ?>
      </h4>
      <small class="text-muted">
        Base Product: <strong><?= html_escape($product['title']); ?></strong> | SKU: <code><?= html_escape($product['sku']); ?></code>
      </small>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= site_url('variants/product/' . $product['id']); ?>" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Variants
      </a>
      <button type="submit" form="variantForm" class="btn btn-primary d-none d-sm-inline-flex align-items-center">
        <i class="fa-solid fa-floppy-disk me-1"></i> <?= !empty($is_edit) ? 'Update Variant' : 'Save Variant'; ?>
      </button>
    </div>
  </div>

  <?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible mb-4" role="alert">
      <?= $this->session->flashdata('error'); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <?php
    // Determine active multi-select attribute (Storage for phones, Size for apparel, etc.)
    $size_attribute = null;
    $multi_attr_id = !empty($active_multi_attr['id']) ? (int)$active_multi_attr['id'] : (!empty($variant['multi_attr_id']) ? (int)$variant['multi_attr_id'] : null);

    if (!empty($active_multi_attr)) {
      $size_attribute = $active_multi_attr;
    } elseif ($multi_attr_id && !empty($attributes)) {
      foreach ($attributes as $a) {
        if ((int)$a['id'] === $multi_attr_id) {
          $size_attribute = $a;
          break;
        }
      }
    }

    if (!$size_attribute && !empty($attributes)) {
      foreach ($attributes as $a) {
        if (in_array(strtolower($a['type'] ?? ''), ['multiple_select', 'multiselect', 'multiple'])) {
          $size_attribute = $a;
          break;
        }
      }
    }
    if (!$size_attribute && !empty($attributes)) {
      foreach ($attributes as $a) {
        if (strcasecmp($a['slug'], 'storage') === 0 || strcasecmp($a['name'], 'storage') === 0) {
          $size_attribute = $a;
          break;
        }
      }
    }
    if (!$size_attribute && !empty($attributes)) {
      foreach ($attributes as $a) {
        if (strcasecmp($a['slug'], 'size') === 0 || strcasecmp($a['name'], 'size') === 0) {
          $size_attribute = $a;
          break;
        }
      }
    }

    $multi_name = !empty($size_attribute) ? $size_attribute['name'] : 'Size';
    $multi_slug = !empty($size_attribute) ? strtolower($size_attribute['slug']) : 'size';
    $multi_icon = ($multi_slug === 'storage') ? 'fa-hard-drive' : (($multi_slug === 'size') ? 'fa-ruler-combined' : 'fa-tags');

    $other_attributes = [];
    if (!empty($attributes)) {
      foreach ($attributes as $attr) {
        if (!empty($size_attribute) && (int)$attr['id'] === (int)$size_attribute['id']) {
          continue;
        }
        // Exclude other multi-select attributes to enforce single multiple selection rule
        $is_other_multi = in_array(strtolower($attr['type'] ?? ''), ['multiple_select', 'multiselect', 'multiple'])
                          || strcasecmp($attr['slug'], 'size') === 0 || strcasecmp($attr['name'], 'size') === 0
                          || strcasecmp($attr['slug'], 'storage') === 0 || strcasecmp($attr['name'], 'storage') === 0;
        if ($is_other_multi) {
          continue;
        }
        $other_attributes[] = $attr;
      }
    }

    // Pre-selected non-multi attribute values if editing
    $selected_non_size = [];
    if (!empty($variant['non_size_attrs'])) {
      foreach ($variant['non_size_attrs'] as $nsa) {
        $selected_non_size[$nsa['attribute_id']] = $nsa['attribute_value_id'];
      }
    } elseif (!empty($variant['values'])) {
      foreach ($variant['values'] as $val) {
        $is_multi_val = (!empty($size_attribute) && (int)$val['attribute_id'] === (int)$size_attribute['id'])
                        || (strcasecmp($val['attribute_slug'], $multi_slug) === 0 || strcasecmp($val['attribute_name'], $multi_name) === 0);
        if (!$is_multi_val) {
          $selected_non_size[$val['attribute_id']] = $val['attribute_value_id'];
        }
      }
    }

    // Pre-selected multi options map: id => [stock, sale_price, price]
    $selected_sizes_map = [];
    if (!empty($variant['sizes'])) {
      foreach ($variant['sizes'] as $sz) {
        $selected_sizes_map[$sz['size_id']] = [
          'stock'      => (isset($sz['stock']) && $sz['stock'] !== '' && $sz['stock'] !== null) ? $sz['stock'] : 0,
          'sale_price' => (isset($sz['sale_price']) && $sz['sale_price'] !== null) ? $sz['sale_price'] : '',
          'price'      => isset($sz['price']) ? $sz['price'] : ''
        ];
      }
    } elseif (!empty($variant['values'])) {
      foreach ($variant['values'] as $val) {
        $is_multi_val = (!empty($size_attribute) && (int)$val['attribute_id'] === (int)$size_attribute['id'])
                        || (strcasecmp($val['attribute_slug'], $multi_slug) === 0 || strcasecmp($val['attribute_name'], $multi_name) === 0);
        if ($is_multi_val) {
          $selected_sizes_map[$val['attribute_value_id']] = [
            'stock'      => (isset($variant['stock_quantity']) && $variant['stock_quantity'] !== '' && $variant['stock_quantity'] !== null) ? $variant['stock_quantity'] : 0,
            'sale_price' => (isset($variant['sale_price']) && $variant['sale_price'] !== null) ? $variant['sale_price'] : '',
            'price'      => isset($variant['price']) ? $variant['price'] : ''
          ];
        }
      }
    }
  ?>

  <form action="<?= $form_action; ?>" method="POST" enctype="multipart/form-data" id="variantForm">
    <input type="hidden" name="id" id="var_id" value="<?= !empty($variant['primary_id']) ? $variant['primary_id'] : (!empty($variant['id']) ? $variant['id'] : ''); ?>">
    <input type="hidden" name="group_variant_ids" id="var_group_variant_ids" value="<?= !empty($variant['variant_ids']) ? implode(',', $variant['variant_ids']) : ''; ?>">
    <input type="hidden" name="active_multi_attr_id" value="<?= !empty($size_attribute) ? $size_attribute['id'] : ''; ?>">
    <input type="hidden" name="gallery_submitted" value="1">

    <div class="row">
      <!-- Left Column: Main info & Configurations -->
      <div class="col-12 col-lg-8">
        
        <!-- Variant Information Card -->
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">Variant Information</h5>
            <small class="text-muted">Set specific variant name. Defaults to the base product title.</small>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label fw-semibold" for="var_title">Variant Title / Name <span class="text-danger">*</span></label>
              <input
                type="text"
                class="form-control"
                id="var_title"
                name="title"
                value="<?= html_escape(!empty($variant['title']) ? $variant['title'] : $product['title']); ?>"
                placeholder="e.g. <?= html_escape($product['title']); ?>"
                readonly
                style="background-color: #f5f5f9; cursor: not-allowed;"
                required
              />
              <small class="text-muted d-block mt-1" style="font-size: 11px;">
                <i class="fa-solid fa-lock text-muted me-1"></i> Read-only base title. When creating multiple <?= strtolower(html_escape($multi_name)); ?> variations below, each <?= strtolower(html_escape($multi_name)); ?> suffix will automatically append (e.g. / 128GB, / M).
              </small>
            </div>
          </div>
        </div>

        <!-- Pricing & Inventory Card -->
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">Pricing & Inventory</h5>
            <small class="text-muted">Configure pricing, SKU, and stock quantities for this variant.</small>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold" for="var_price">Regular Price <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text"><?= html_escape($currency_symbol ?? '$'); ?></span>
                  <input
                    type="number"
                    step="0.01"
                    class="form-control"
                    id="var_price"
                    name="price"
                    value="<?= !empty($variant['price']) ? $variant['price'] : $product['price']; ?>"
                    placeholder="49.99"
                    required
                  />
                </div>
                <small class="text-muted d-block mt-1" style="font-size: 11px;">
                  Standard listing price / MRP for this variant.
                </small>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold" for="max_purchase_quantity">Max Purchase Quantity (Per Order)</label>
                <input
                  type="number"
                  min="1"
                  class="form-control"
                  id="max_purchase_quantity"
                  name="max_purchase_quantity"
                  value="<?= !empty($variant['max_purchase_quantity']) ? $variant['max_purchase_quantity'] : (!empty($product['max_purchase_quantity']) ? $product['max_purchase_quantity'] : 5); ?>"
                  placeholder="e.g. 5"
                />
                <small class="text-muted d-block mt-1" style="font-size: 11px;">
                  Maximum units a customer can purchase in one order for this variant (e.g. 5).
                </small>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold" for="var_sku">Variant Base SKU <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="var_sku"
                  name="sku"
                  value="<?= html_escape(!empty($variant['base_sku']) ? $variant['base_sku'] : (!empty($variant['sku']) ? $variant['sku'] : $product['sku'])); ?>"
                  placeholder="e.g. <?= html_escape($product['sku']); ?>"
                  readonly
                  style="background-color: #f5f5f9; cursor: not-allowed;"
                  required
                  oninput="updateSkuPreview()"
                />
                <small class="text-muted d-block mt-1" style="font-size: 11px;">
                  <i class="fa-solid fa-lock text-muted me-1"></i> Read-only base SKU. When creating multiple <?= strtolower(html_escape($multi_name)); ?> variations below, the <?= strtolower(html_escape($multi_name)); ?> code will append (e.g. -128GB, -M).
                </small>
                <div id="sku-preview-helper" class="text-primary small mt-1" style="font-size: 11px; display: none;"></div>
              </div>
              <div class="col-md-6 mb-3" id="var_stock_wrapper">
                <label class="form-label fw-semibold" for="var_stock">Stock Quantity (per variant) <span class="text-danger">*</span></label>
                <input
                  type="number"
                  class="form-control"
                  id="var_stock"
                  name="stock_quantity"
                  value="<?= isset($variant['total_stock']) ? $variant['total_stock'] : (isset($variant['stock_quantity']) ? $variant['stock_quantity'] : '0'); ?>"
                  placeholder="0"
                  onblur="if(this.value.trim()==='') this.value='0';"
                  required
                />
                <small class="text-muted d-block mt-1" id="var_stock_helper" style="font-size: 11px;">
                  Default variant inventory. When <?= strtolower(html_escape($multi_name)); ?> options are selected below, this auto-sums all stocks.
                </small>
              </div>
              <div class="col-md-6 mb-3" id="var_sale_price_wrapper" style="<?= (!empty($selected_sizes_map) || (!empty($size_attribute) && !empty($size_attribute['values']))) ? 'display: none;' : ''; ?>">
                <label class="form-label fw-semibold" for="var_sale_price">Sale Price (Optional)</label>
                <div class="input-group">
                  <span class="input-group-text"><?= html_escape($currency_symbol ?? '$'); ?></span>
                  <input
                    type="number"
                    step="0.01"
                    class="form-control"
                    id="var_sale_price"
                    name="sale_price"
                    value="<?= isset($variant['sale_price']) ? $variant['sale_price'] : ($product['sale_price'] ?: ''); ?>"
                    placeholder="39.99"
                  />
                </div>
                <small class="text-muted d-block mt-1" style="font-size: 11px;">
                  Active when no <?= strtolower(html_escape($multi_name)); ?> attributes are defined for this product/variant.
                </small>
              </div>
            </div>
          </div>
        </div>

        <!-- Assign Attributes Card -->
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0 d-flex align-items-center text-dark">
              <i class="fa-solid fa-tags text-primary me-2"></i> Assign Attributes
            </h5>
            <small class="text-muted">Assign attributes (Color, <?= html_escape($multi_name); ?>, Material, etc.) and manage individual stock per <?= strtolower(html_escape($multi_name)); ?>.</small>
          </div>
          <div class="card-body">
            <?php if (!empty($attributes)): ?>
              <div class="row">

                <!-- Non-Multi Attributes (e.g. Color, Material) -->
                <?php if (!empty($other_attributes)): ?>
                  <div class="col-12 mb-3">
                    <label class="form-label fw-semibold text-dark">Assign Non-<?= html_escape($multi_name); ?> Attributes (e.g. Color, Material):</label>
                    <div class="row g-3">
                      <?php foreach ($other_attributes as $attr): ?>
                        <div class="col-md-6">
                          <div class="p-2 border rounded bg-light">
                            <label class="form-label small fw-bold mb-1" for="attr_val_<?= $attr['id']; ?>">
                              <?= html_escape($attr['name']); ?>
                            </label>
                            <select
                              class="form-select form-select-sm"
                              name="attr_vals[<?= $attr['id']; ?>]"
                              id="attr_val_<?= $attr['id']; ?>"
                              onchange="onNonSizeAttrChange(this, '<?= html_escape($attr['slug']); ?>')"
                            >
                              <option value="">-- None / N/A --</option>
                              <?php if (!empty($attr['values'])): ?>
                                <?php foreach ($attr['values'] as $v): 
                                  $isSelected = isset($selected_non_size[$attr['id']]) && ($selected_non_size[$attr['id']] == $v['id']);
                                ?>
                                  <option
                                    value="<?= $v['id']; ?>"
                                    data-name="<?= html_escape($v['value']); ?>"
                                    <?= $isSelected ? 'selected' : ''; ?>
                                  >
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

                <!-- Multi-Select Option (e.g. Storage, Size) & Stock Management -->
                <?php if ($size_attribute && !empty($size_attribute['values'])): ?>
                  <div class="col-12 mb-3">
                    <div class="p-3 border rounded bg-light">
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                          <label class="form-label fw-bold mb-0 text-dark">
                            <i class="fa-solid <?= $multi_icon; ?> text-info me-1"></i> Assign <?= html_escape($multi_name); ?> <span class="badge bg-label-info ms-1" style="font-size: 10px;">Multiple Selection</span>
                          </label>
                          <small class="text-muted d-block" style="font-size: 11px;">Select all <?= strtolower(html_escape($multi_name)); ?> options available for this variant group.</small>
                        </div>
                        <div class="d-flex gap-2">
                          <a href="javascript:void(0);" class="small text-primary text-decoration-none fw-semibold" onclick="selectAllSizes(true)">Select All</a>
                          <span class="text-muted small">|</span>
                          <a href="javascript:void(0);" class="small text-secondary text-decoration-none" onclick="selectAllSizes(false)">Clear</a>
                        </div>
                      </div>

                      <!-- Multi-Option Chips -->
                      <div class="d-flex flex-wrap gap-2 mb-2" id="size-chips-wrapper">
                        <?php foreach ($size_attribute['values'] as $v): 
                          $isSizeActive = isset($selected_sizes_map[$v['id']]);
                        ?>
                          <div
                            class="var-size-chip border rounded px-3 py-1 text-center <?= $isSizeActive ? 'active' : ''; ?>"
                            id="size_chip_label_<?= $v['id']; ?>"
                            onclick="toggleSizeChip(<?= $v['id']; ?>, '<?= html_escape($v['value']); ?>')"
                          >
                            <input
                              type="checkbox"
                              name="size_vals[]"
                              value="<?= $v['id']; ?>"
                              data-val="<?= html_escape($v['value']); ?>"
                              id="size_input_<?= $v['id']; ?>"
                              class="d-none size-chip-checkbox"
                              <?= $isSizeActive ? 'checked' : ''; ?>
                            />
                            <span><?= html_escape($v['value']); ?></span>
                          </div>
                        <?php endforeach; ?>
                      </div>

                      <!-- Manage Stock by Multi-Option Dynamic Container -->
                      <div id="var_size_stock_manager" class="mt-3 p-3 bg-white rounded border" style="<?= !empty($selected_sizes_map) ? 'display: block;' : 'display: none;'; ?>">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom flex-wrap gap-2">
                          <div>
                            <span class="fw-bold text-dark fs-6">
                              <i class="fa-solid fa-boxes-stacked text-primary me-1"></i> Manage Stock by <?= html_escape($multi_name); ?>
                            </span>
                            <small class="text-muted d-block" style="font-size: 11px;">Specify individual inventory quantity and sale price for each selected <?= strtolower(html_escape($multi_name)); ?>.</small>
                          </div>
                        </div>

                        <!-- Table of Selected Options for Stock -->
                        <div class="table-responsive">
                          <table class="table table-sm table-bordered align-middle mb-0 no-datatable">
                            <thead class="table-light">
                              <tr>
                                <th style="width: 100px;" class="text-center"><?= html_escape($multi_name); ?></th>
                                <th>Variant SKU</th>
                                <th style="width: 170px;">Stock Quantity</th>
                                <th style="width: 170px;">Sale Price (<?= html_escape($currency_symbol ?? '$'); ?>)</th>
                                <th style="width: 110px;" class="text-center">Status</th>
                              </tr>
                            </thead>
                            <tbody id="var-size-stock-tbody">
                              <!-- dynamically populated by JS -->
                            </tbody>
                          </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top flex-wrap gap-2">
                          <span class="text-muted small" id="selected-sizes-count">0 <?= strtolower(html_escape($multi_name)); ?> options selected</span>
                          <div class="text-end">
                            <small class="text-muted">Total Stock for this variant: </small>
                            <strong class="text-primary fs-6" id="var-total-sizes-stock">0 units</strong>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                <?php endif; ?>

              </div>
            <?php else: ?>
              <p class="text-muted mb-0">No attributes found. Create attributes in Catalog > Attributes first.</p>
            <?php endif; ?>
          </div>
        </div>

        <?php 
          $has_highlights = !empty($highlights);
          $has_specs      = !empty($specifications);
        ?>

        <!-- Product Highlights Card -->
        <div class="card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div>
              <h5 class="card-title mb-0 d-flex align-items-center text-dark">
                <i class="fa-solid fa-highlighter text-primary me-2"></i> Product Highlights
              </h5>
              <small class="text-muted">Highlight key features for this variant prominently (e.g. Sleeve: Full Sleeve, Fabric: Cotton Blend).</small>
            </div>
            <div class="form-check form-switch m-0">
              <input
                class="form-check-input"
                type="checkbox"
                id="enable_highlights"
                name="enable_highlights"
                value="1"
                <?= $has_highlights ? 'checked' : ''; ?>
                onchange="toggleHighlightsPanel(this.checked)"
                style="width: 2.5em; height: 1.3em; cursor: pointer;"
              />
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
              <small class="text-muted">Detailed technical specifications for this variant.</small>
            </div>
            <div class="form-check form-switch m-0">
              <input
                class="form-check-input"
                type="checkbox"
                id="enable_specifications"
                name="enable_specifications"
                value="1"
                <?= $has_specs ? 'checked' : ''; ?>
                onchange="toggleSpecificationsPanel(this.checked)"
                style="width: 2.5em; height: 1.3em; cursor: pointer;"
              />
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
                    <?php foreach ($specifications as $sp): 
                      $sp_name = $sp['name'] ?? ($sp['spec_name'] ?? '');
                      $sp_val  = $sp['value'] ?? ($sp['spec_value'] ?? '');
                    ?>
                      <tr>
                        <td>
                          <input type="text" name="spec_names[]" class="form-control form-control-sm" placeholder="e.g. Material" value="<?= html_escape($sp_name); ?>" required>
                        </td>
                        <td>
                          <input type="text" name="spec_values[]" class="form-control form-control-sm" placeholder="e.g. 100% Cotton" value="<?= html_escape($sp_val); ?>" required>
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

      <!-- Right Column: Media (Sticky Card) -->
      <div class="col-12 col-lg-4">
        <div class="sticky-organization-wrapper">
          <div class="card mb-4 sticky-organization-card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
              <h5 class="card-title mb-0">Variant Media</h5>
              <span class="badge bg-label-primary small">Images</span>
            </div>
            <div class="card-body">
              
              <!-- Main Variant Image File Upload -->
              <div class="mb-3">
                <label class="form-label fw-semibold" for="var_image_file">Main Product Image</label>
                <?php 
                  $current_main_img = !empty($variant['image']) ? $variant['image'] : (!empty($product['main_image']) ? $product['main_image'] : 'products/womens/women-1.jpg');
                ?>
                <div class="border rounded p-2 mb-2 bg-light text-center">
                  <img
                    id="var_preview_img"
                    src="<?= base_url('../website/assets/images/' . $current_main_img); ?>"
                    class="rounded img-fluid"
                    style="max-height: 140px; object-fit: contain;"
                    alt="Variant Image Preview"
                    onerror="this.src='<?= base_url('../website/assets/images/products/womens/women-1.jpg'); ?>'"
                  />
                </div>
                <input
                  type="file"
                  class="form-control form-control-sm"
                  id="var_image_file"
                  name="image_file"
                  accept="image/*"
                  onchange="previewVarImage(this)"
                />
                <input type="hidden" name="current_image" id="var_current_image" value="<?= html_escape($current_main_img); ?>" />
                <small class="text-muted d-block mt-1" style="font-size: 11px;">Primary photo for this variant (Max 10MB)</small>
              </div>

              <!-- Upload Gallery Images for Variant -->
              <div class="mb-3">
                <label class="form-label fw-semibold" for="var_gallery_files">Upload Gallery Images</label>
                <input
                  type="file"
                  class="form-control form-control-sm"
                  id="var_gallery_files"
                  name="gallery_files[]"
                  accept="image/*"
                  multiple
                  onchange="handleVarGalleryFiles(this)"
                />
                <small class="text-muted d-block mt-1" style="font-size: 11px;">Select multiple gallery photos for this variant (Max 10MB each).</small>

                <!-- New Uploads Preview Container with individual remove buttons -->
                <div id="var_new_gallery_container" class="mt-2" style="display: none;">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold text-primary" id="var_new_gallery_count_label">New Selected (0):</span>
                    <button type="button" class="btn btn-link text-danger p-0 small text-decoration-none" style="font-size: 11px;" onclick="clearAllVarGalleryFiles()">
                      <i class="fa-solid fa-trash-can me-1"></i>Clear All New
                    </button>
                  </div>
                  <div id="var_new_gallery_preview_list" class="d-flex flex-wrap gap-2"></div>
                </div>

                <!-- Existing Variant Gallery Section (When editing variant) -->
                <?php 
                  $existing_gal = [];
                  if (!empty($variant['gallery_images'])) {
                    $existing_gal = is_array($variant['gallery_images']) ? $variant['gallery_images'] : (json_decode($variant['gallery_images'], true) ?: []);
                  }
                ?>
                <div class="mt-2" id="var_existing_gallery_wrapper" style="<?= !empty($existing_gal) ? 'display: block;' : 'display: none;'; ?>">
                  <label class="form-label small fw-semibold text-secondary mb-1" id="var_existing_gallery_label">
                    Existing Variant Gallery (<?= count($existing_gal); ?>):
                  </label>
                  <div class="d-flex flex-wrap gap-2" id="var_existing_gallery_list">
                    <?php if (!empty($existing_gal)): ?>
                      <?php foreach ($existing_gal as $gIdx => $gFile): ?>
                        <div class="position-relative border rounded p-1 bg-white shadow-sm" id="var_gal_item_<?= $gIdx; ?>" style="width: 52px; height: 52px;">
                          <img src="<?= base_url('../website/assets/images/' . $gFile); ?>" class="w-100 h-100 object-fit-cover rounded" alt="Gallery" onerror="this.src='<?= base_url('../website/assets/images/products/womens/women-1.jpg'); ?>'">
                          <input type="hidden" name="existing_gallery[]" value="<?= html_escape($gFile); ?>">
                          <button
                            type="button"
                            class="position-absolute d-flex align-items-center justify-content-center"
                            style="top: -6px; right: -6px; width: 18px; height: 18px; border-radius: 50%; background: #ff4d49; color: #fff; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.25); z-index: 10; cursor: pointer; padding: 0;"
                            onclick="removeExistingVarGallery(<?= $gIdx; ?>, '<?= html_escape($gFile); ?>')"
                            title="Remove from variant gallery"
                          >
                            <i class="fa-solid fa-xmark" style="font-size: 9px; line-height: 1;"></i>
                          </button>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                </div>

              </div>

            </div>
            <div class="card-footer bg-white border-top py-3">
              <button type="submit" form="variantForm" class="btn btn-primary w-100 shadow-sm py-2">
                <i class="fa-solid fa-floppy-disk me-1"></i> <?= !empty($is_edit) ? 'Update Variant' : 'Save Variant'; ?>
              </button>
              <a href="<?= site_url('variants/product/' . $product['id']); ?>" class="btn btn-outline-secondary w-100 mt-2">
                Cancel
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
// DataTransfer container for new variant gallery uploads
var newVarGalleryDT = new DataTransfer();

// Pre-existing sizes stock map for edit mode
var presetSizesMap = <?= !empty($selected_sizes_map) ? json_encode($selected_sizes_map) : '{}'; ?>;
var multiAttrName = <?= json_encode($multi_name); ?>;

function previewVarImage(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('var_preview_img').src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function handleVarGalleryFiles(input) {
  if (input.files && input.files.length > 0) {
    for (var i = 0; i < input.files.length; i++) {
      newVarGalleryDT.items.add(input.files[i]);
    }
    input.files = newVarGalleryDT.files;
    renderVarNewGalleryPreviews();
  }
}

function removeNewVarGalleryFile(idx) {
  newVarGalleryDT.items.remove(idx);
  var input = document.getElementById('var_gallery_files');
  if (input) {
    input.files = newVarGalleryDT.files;
  }
  renderVarNewGalleryPreviews();
}

function clearAllVarGalleryFiles() {
  newVarGalleryDT = new DataTransfer();
  var input = document.getElementById('var_gallery_files');
  if (input) {
    input.files = newVarGalleryDT.files;
    input.value = '';
  }
  renderVarNewGalleryPreviews();
}

function renderVarNewGalleryPreviews() {
  var container = document.getElementById('var_new_gallery_container');
  var list = document.getElementById('var_new_gallery_preview_list');
  var countLabel = document.getElementById('var_new_gallery_count_label');
  if (!container || !list) return;

  list.innerHTML = '';
  var count = newVarGalleryDT.files.length;

  if (count === 0) {
    container.style.display = 'none';
    return;
  }

  container.style.display = 'block';
  if (countLabel) {
    countLabel.textContent = 'New Selected (' + count + '):';
  }

  Array.from(newVarGalleryDT.files).forEach(function(file, idx) {
    var reader = new FileReader();
    reader.onload = function(e) {
      var thumb = document.createElement('div');
      thumb.className = 'position-relative border rounded p-1 bg-white shadow-sm';
      thumb.style.width = '52px';
      thumb.style.height = '52px';
      thumb.innerHTML = 
        '<img src="' + e.target.result + '" class="w-100 h-100 object-fit-cover rounded" alt="Gallery Preview">' +
        '<span class="badge bg-primary position-absolute bottom-0 start-0 p-0 text-center" style="font-size: 8px; width: 100%; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px; opacity: 0.9;">NEW</span>' +
        '<button type="button" class="position-absolute d-flex align-items-center justify-content-center" ' +
        'style="top: -6px; right: -6px; width: 18px; height: 18px; border-radius: 50%; background: #ff4d49; color: #fff; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.25); z-index: 10; cursor: pointer; padding: 0;" ' +
        'onclick="removeNewVarGalleryFile(' + idx + ')" title="Remove this file">' +
        '<i class="fa-solid fa-xmark" style="font-size: 9px; line-height: 1;"></i>' +
        '</button>';
      list.appendChild(thumb);
    };
    reader.readAsDataURL(file);
  });
}

function removeExistingVarGallery(idx, imagePath) {
  var item = document.getElementById('var_gal_item_' + idx);
  if (!item) return;

  var varId = document.getElementById('var_id') ? document.getElementById('var_id').value : '';

  if (varId) {
    if (!confirm('Are you sure you want to remove this gallery image from the variant?')) {
      return;
    }
    item.style.opacity = '0.3';
    item.style.pointerEvents = 'none';

    var payload = new URLSearchParams();
    payload.append('variant_id', varId);
    payload.append('image_path', imagePath);

    fetch('<?= site_url("variants/delete_gallery_image"); ?>', {
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
        updateVarExistingGalleryUI();
      } else {
        alert(data.message || 'Error removing image');
        item.style.opacity = '1';
        item.style.pointerEvents = 'auto';
      }
    })
    .catch(function() {
      item.remove();
      updateVarExistingGalleryUI();
    });
  } else {
    item.remove();
    updateVarExistingGalleryUI();
  }
}

function updateVarExistingGalleryUI() {
  var list = document.getElementById('var_existing_gallery_list');
  var wrapper = document.getElementById('var_existing_gallery_wrapper');
  var label = document.getElementById('var_existing_gallery_label');
  if (!list || !wrapper) return;

  var items = list.querySelectorAll('[id^="var_gal_item_"]');
  if (items.length === 0) {
    wrapper.style.display = 'none';
  } else {
    wrapper.style.display = 'block';
    if (label) label.textContent = 'Existing Variant Gallery (' + items.length + '):';
  }
}

// SIZE CHIP SELECTION & STOCK BY SIZE
function toggleSizeChip(valId, sizeVal) {
  var cb = document.getElementById('size_input_' + valId);
  var label = document.getElementById('size_chip_label_' + valId);
  if (!cb || !label) return;

  cb.checked = !cb.checked;
  if (cb.checked) {
    label.classList.add('active');
  } else {
    label.classList.remove('active');
  }

  updateSizeSelectionInfo();
  renderVarSizeStockInputs();
}

function selectAllSizes(enable) {
  document.querySelectorAll('.size-chip-checkbox').forEach(function(cb) {
    cb.checked = enable;
    var label = document.getElementById('size_chip_label_' + cb.value);
    if (label) {
      if (enable) {
        label.classList.add('active');
      } else {
        label.classList.remove('active');
      }
    }
  });

  updateSizeSelectionInfo();
  renderVarSizeStockInputs();
}

function updateSizeSelectionInfo() {
  var checked = document.querySelectorAll('.size-chip-checkbox:checked');
  var infoEl = document.getElementById('selected-sizes-count');
  var names = [];
  checked.forEach(function(c) {
    names.push(c.getAttribute('data-val'));
  });

  var dimName = (multiAttrName || 'option').toLowerCase();
  if (infoEl) {
    if (checked.length === 0) {
      infoEl.textContent = '0 ' + dimName + ' options selected';
    } else if (checked.length === 1) {
      infoEl.textContent = '1 ' + dimName + ' selected: ' + names[0];
    } else {
      infoEl.innerHTML = '<strong class="text-primary">' + checked.length + ' ' + dimName + ' options selected:</strong> ' + names.join(', ');
    }
  }

  updateSkuPreview();
}

// Render dynamic Stock by Size table
function renderVarSizeStockInputs(presetStocks) {
  var checkedCbs = document.querySelectorAll('.size-chip-checkbox:checked');
  var container = document.getElementById('var_size_stock_manager');
  var tbody = document.getElementById('var-size-stock-tbody');
  var stockWrapper = document.getElementById('var_stock_wrapper');
  var salePriceWrapper = document.getElementById('var_sale_price_wrapper');
  if (!container || !tbody) return;

  var baseSku = (document.getElementById('var_sku') ? document.getElementById('var_sku').value.trim() : '') || 'SKU';
  var defaultSalePrice = (document.getElementById('var_sale_price') ? document.getElementById('var_sale_price').value.trim() : '') || '';

  if (checkedCbs.length === 0) {
    container.style.display = 'none';
    if (stockWrapper) stockWrapper.style.display = 'block';
    if (salePriceWrapper) {
      salePriceWrapper.style.display = <?= (!empty($size_attribute) && !empty($size_attribute['values'])) ? "'none'" : "'block'" ?>;
    }
    return;
  }

  container.style.display = 'block';
  if (stockWrapper) stockWrapper.style.display = 'none';
  if (salePriceWrapper) salePriceWrapper.style.display = 'none';

  // Read existing input values unless presetStocks provided
  var existingData = presetStocks || presetSizesMap || {};
  if (!presetStocks) {
    document.querySelectorAll('.var-size-qty-input').forEach(function(inp) {
      var sid = inp.getAttribute('data-size-id');
      var val = inp.value.trim();
      if (val === '') val = '0';
      if (!existingData[sid] || typeof existingData[sid] !== 'object') {
        existingData[sid] = { stock: val };
      } else {
        existingData[sid].stock = val;
      }
    });
    document.querySelectorAll('.var-size-sale-price-input').forEach(function(inp) {
      var sid = inp.getAttribute('data-size-id');
      if (!existingData[sid] || typeof existingData[sid] !== 'object') {
        existingData[sid] = { sale_price: inp.value };
      } else {
        existingData[sid].sale_price = inp.value;
      }
    });
  }

  tbody.innerHTML = '';
  checkedCbs.forEach(function(cb) {
    var sizeId = cb.value;
    var sizeName = cb.getAttribute('data-val') || '';
    var sizeObj = existingData[sizeId];
    var existingQty = '0';
    var existingSalePrice = defaultSalePrice;

    if (sizeObj !== undefined && sizeObj !== null) {
      if (typeof sizeObj === 'object') {
        if (sizeObj.stock !== undefined && sizeObj.stock !== null && sizeObj.stock !== '') {
          existingQty = String(sizeObj.stock);
        } else {
          existingQty = '0';
        }
        if (sizeObj.sale_price !== undefined && sizeObj.sale_price !== null && sizeObj.sale_price !== '') {
          existingSalePrice = sizeObj.sale_price;
        }
      } else {
        existingQty = (sizeObj !== '' && sizeObj !== null && sizeObj !== undefined) ? String(sizeObj) : '0';
      }
    }

    var isOut = parseInt(existingQty, 10) === 0;
    var vSku = baseSku;
    if (!new RegExp('-' + sizeName + '$', 'i').test(vSku)) {
      vSku += '-' + sizeName.toUpperCase();
    }

    var tr = document.createElement('tr');
    tr.id = 'var_size_row_' + sizeId;
    tr.innerHTML = 
      '<td class="text-center align-middle">' +
        '<span class="badge bg-primary fs-6 fw-bold px-2 py-1">' + escapeHtml(sizeName) + '</span>' +
      '</td>' +
      '<td class="align-middle">' +
        '<code class="var-size-sku-preview fw-bold" style="font-size: 12px;">' + escapeHtml(vSku) + '</code>' +
      '</td>' +
      '<td class="align-middle">' +
        '<div class="input-group input-group-sm">' +
          '<span class="input-group-text"><i class="fa-solid fa-boxes-stacked" style="font-size: 10px;"></i></span>' +
          '<input type="number" min="0" class="form-control text-end var-size-qty-input" name="size_stock[' + sizeId + ']" data-size-id="' + sizeId + '" value="' + escapeHtml(existingQty) + '" placeholder="0" oninput="recalcVarTotalStock()" onblur="if(this.value.trim()===\'\') { this.value=\'0\'; recalcVarTotalStock(); }">' +
        '</div>' +
      '</td>' +
      '<td class="align-middle">' +
        '<div class="input-group input-group-sm">' +
          '<span class="input-group-text">' + escapeHtml(<?= json_encode($currency_symbol ?? '$'); ?>) + '</span>' +
          '<input type="number" step="0.01" min="0" class="form-control text-end var-size-sale-price-input" name="size_sale_price[' + sizeId + ']" data-size-id="' + sizeId + '" value="' + escapeHtml(existingSalePrice) + '" placeholder="' + escapeHtml(defaultSalePrice || '0.00') + '">' +
        '</div>' +
      '</td>' +
      '<td class="text-center align-middle">' +
        '<span class="badge ' + (isOut ? 'bg-label-danger' : 'bg-label-success') + ' var-size-status-pill">' + (isOut ? 'Out of stock' : 'In stock') + '</span>' +
      '</td>';
    tbody.appendChild(tr);
  });

  recalcVarTotalStock();
}

function applyVarStockToAll() {
  var val = document.getElementById('quick_var_stock_val') ? document.getElementById('quick_var_stock_val').value : '10';
  document.querySelectorAll('.var-size-qty-input').forEach(function(inp) {
    inp.value = val;
  });
  recalcVarTotalStock();
}

function applyVarSalePriceToAll() {
  var val = document.getElementById('quick_var_sale_price_val') ? document.getElementById('quick_var_sale_price_val').value : '';
  document.querySelectorAll('.var-size-sale-price-input').forEach(function(inp) {
    inp.value = val;
  });
}

function recalcVarTotalStock() {
  var total = 0;
  document.querySelectorAll('.var-size-qty-input').forEach(function(inp) {
    var qty = parseInt(inp.value, 10) || 0;
    total += qty;
    var row = inp.closest('#var_size_row_' + inp.getAttribute('data-size-id'));
    if (row) {
      var pill = row.querySelector('.var-size-status-pill');
      if (pill) {
        if (qty <= 0) {
          pill.className = 'badge bg-label-danger var-size-status-pill';
          pill.textContent = 'Out of stock';
        } else {
          pill.className = 'badge bg-label-success var-size-status-pill';
          pill.textContent = 'In stock';
        }
      }
    }
  });

  var totalEl = document.getElementById('var-total-sizes-stock');
  if (totalEl) totalEl.textContent = total + ' units';

  var stockInp = document.getElementById('var_stock');
  if (stockInp) stockInp.value = total;
}

function onNonSizeAttrChange(selectEl, slug) {
  if (slug === 'color') {
    var opt = selectEl.options[selectEl.selectedIndex];
    var colorName = opt ? opt.getAttribute('data-name') : '';
    if (colorName) {
      var baseTitleInput = document.getElementById('var_title');
      var baseSkuInput = document.getElementById('var_sku');
      var prodTitle = <?= json_encode($product["title"]); ?>;
      var prodSku = <?= json_encode($product["sku"]); ?>;

      // In Add mode, auto-fill/suggest variant title and SKU
      if (!document.getElementById('var_id') || !document.getElementById('var_id').value) {
        if (baseTitleInput && (!baseTitleInput.value || baseTitleInput.value.indexOf(prodTitle) === 0)) {
          baseTitleInput.value = prodTitle + ' - ' + colorName;
        }
        if (baseSkuInput && (!baseSkuInput.value || baseSkuInput.value.indexOf(prodSku) === 0)) {
          var code = colorName.toUpperCase().replace(/[^A-Z0-9]/g, '').substr(0, 3);
          baseSkuInput.value = prodSku + '-' + code;
        }
      }
    }
  }
  updateSkuPreview();
}

function updateSkuPreview() {
  var helper = document.getElementById('sku-preview-helper');
  var baseSku = document.getElementById('var_sku') ? document.getElementById('var_sku').value.trim() : '';
  var checked = document.querySelectorAll('.size-chip-checkbox:checked');

  // Update dynamic size table SKU previews
  document.querySelectorAll('#var-size-stock-tbody .var-size-sku-preview').forEach(function(el) {
    var row = el.closest('[id^="var_size_row_"]');
    var badge = row ? row.querySelector('.badge.bg-primary') : null;
    if (badge && baseSku) {
      var sVal = badge.textContent.trim();
      var vSku = baseSku;
      if (!new RegExp('-' + sVal + '$', 'i').test(vSku)) {
        vSku += '-' + sVal.toUpperCase();
      }
      el.textContent = vSku;
    }
  });

  if (!helper) return;

  if (!baseSku || checked.length <= 1) {
    helper.style.display = 'none';
    return;
  }

  var skus = [];
  checked.forEach(function(cb) {
    var sVal = cb.getAttribute('data-val');
    var sSku = baseSku;
    if (!new RegExp('-' + sVal + '$', 'i').test(sSku)) {
      sSku += '-' + sVal.toUpperCase();
    }
    skus.push(sSku);
  });

  helper.style.display = 'block';
  helper.innerHTML = '<i class="fa-solid fa-layer-group me-1"></i>Group of ' + checked.length + ' ' + (multiAttrName || 'option').toLowerCase() + ' variations: <code class="text-dark">' + skus.join(', ') + '</code>';
}

// Highlights dynamic management
function toggleHighlightsPanel(checked) {
  var panel = document.getElementById('highlights_panel');
  if (!panel) return;
  panel.style.display = checked ? 'block' : 'none';
  if (checked) {
    var tbody = document.getElementById('highlights_body');
    if (tbody && tbody.children.length === 0) {
      addHighlightRow('Fabric', '100% Cotton');
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

// Specifications dynamic management
function toggleSpecificationsPanel(checked) {
  var panel = document.getElementById('specifications_panel');
  if (!panel) return;
  panel.style.display = checked ? 'block' : 'none';
  if (checked) {
    var tbody = document.getElementById('specifications_body');
    if (tbody && tbody.children.length === 0) {
      addSpecificationRow('Material', 'Cotton Blend');
    }
  }
}

function addSpecificationRow(name = '', val = '') {
  var tbody = document.getElementById('specifications_body');
  if (!tbody) return;
  var tr = document.createElement('tr');
  tr.innerHTML = `
    <td>
      <input type="text" name="spec_names[]" class="form-control form-control-sm" placeholder="e.g. Occasion" value="${escapeHtml(name)}" required>
    </td>
    <td>
      <input type="text" name="spec_values[]" class="form-control form-control-sm" placeholder="e.g. Casual, Party" value="${escapeHtml(val)}" required>
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

// On page load: initialize sizes table & SKU preview if any pre-selected
document.addEventListener('DOMContentLoaded', function() {
  updateSizeSelectionInfo();
  renderVarSizeStockInputs(presetSizesMap);
  updateSkuPreview();
});
</script>
