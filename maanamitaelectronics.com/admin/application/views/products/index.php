<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><span class="text-muted fw-light">Catalog /</span> Products</h4>
    <?php if ($this->can('products.manage')): ?>
      <a href="<?= site_url('products/add'); ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i> Add Product
      </a>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="card-header pb-2">
      <form method="GET" action="<?= site_url('products'); ?>" class="row g-3 align-items-center">
        <div class="col-md-4">
          <input
            type="text"
            name="search"
            class="form-control"
            placeholder="Search by title or SKU..."
            value="<?= html_escape($search_query ?? ''); ?>" />
        </div>
        <div class="col-md-3">
          <select name="category_id" class="form-select">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id']; ?>" <?= ($selected_cat == $cat['id']) ? 'selected' : ''; ?>>
                <?= html_escape($cat['breadcrumb_path'] ?? $cat['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <button type="submit" class="btn btn-outline-primary me-2"><i class="fa-solid fa-filter me-1"></i> Filter</button>
          <a href="<?= site_url('products'); ?>" class="btn btn-outline-secondary">Reset</a>
        </div>
      </form>
    </div>

    <div class="table-responsive text-nowrap">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Product</th>
            <th>Category</th>
            <th>SKU</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          <?php if (!empty($products)): ?>
            <?php foreach ($products as $p): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <img
                      src="<?= admin_storefront_asset_url('images/' . $p['main_image']); ?>"
                      alt="<?= html_escape($p['title']); ?>"
                      class="rounded me-3"
                      style="width: 48px; height: 48px; object-fit: contain;"
                      onerror="this.src='<?= base_url('assets/img/elements/1.jpg'); ?>'" />
                      <div>
                        <h6 class="mb-0 text-truncate" style="max-width: 280px;"><?= html_escape($p['title']); ?></h6>
                        <small class="text-muted"><?= html_escape($p['brand_name'] ?? 'Generic'); ?></small>
                        <div class="mt-1">
                          <?php if (($p['product_type'] ?? 'simple') === 'variable'): ?>
                            <a href="<?= site_url('variants/product/' . $p['id']); ?>" class="badge bg-label-primary text-decoration-none">
                              <i class="fa-solid fa-code-fork me-1"></i><?= $p['variants_count']; ?> Variants
                            </a>
                          <?php else: ?>
                            <span class="badge bg-label-secondary">Simple</span>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td><span class="badge bg-label-info"><?= html_escape($p['category_name'] ?? 'Uncategorized'); ?></span></td>
                  <td><code><?= html_escape($p['sku']); ?></code></td>
                  <td>
                    <?php if (!empty($p['sale_price'])): ?>
                      <span class="text-danger fw-semibold"><?= $currency_symbol . number_format($p['sale_price'], 2); ?></span>
                      <small class="text-muted text-decoration-line-through ms-1"><?= $currency_symbol . number_format($p['price'], 2); ?></small>
                    <?php else: ?>
                      <span class="fw-semibold"><?= $currency_symbol . number_format($p['price'], 2); ?></span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($p['stock_quantity'] <= 0): ?>
                      <span class="badge bg-label-danger">Out of Stock (0)</span>
                    <?php elseif ($p['stock_quantity'] < 10): ?>
                      <span class="badge bg-label-warning">Low: <?= $p['stock_quantity']; ?></span>
                    <?php else: ?>
                      <span class="badge bg-label-success"><?= $p['stock_quantity']; ?> in stock</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="badge bg-label-<?= ($p['status'] === 'published') ? 'success' : 'secondary'; ?>">
                      <?= ucfirst($p['status']); ?>
                    </span>
                    <?php if (isset($p['is_cod_allowed']) && (int)$p['is_cod_allowed'] === 0): ?>
                      <span class="badge bg-label-secondary ms-1" title="Cash on Delivery Disabled"><i class="fa-solid fa-ban me-1 text-danger"></i>No COD</span>
                    <?php else: ?>
                      <span class="badge bg-label-success ms-1" title="Cash on Delivery Available"><i class="fa-solid fa-truck-ramp-box me-1"></i>COD</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div class="dropdown">
                      <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                      </button>
                      <div class="dropdown-menu">
                        <?php if ($this->can('products.manage')): ?>
                          <a class="dropdown-item" href="<?= site_url('products/edit/' . $p['id']); ?>">
                            <i class="fa-solid fa-pen-to-square me-1 text-primary"></i> Edit
                          </a>
                          <a class="dropdown-item" href="<?= site_url('variants/product/' . $p['id']); ?>">
                            <i class="fa-solid fa-code-fork me-1 text-info"></i> Manage Variants (<?= $p['variants_count']; ?>)
                          </a>
                          <a class="dropdown-item" href="<?= site_url('inventory/adjust?product_id=' . $p['id']); ?>">
                            <i class="fa-solid fa-arrows-rotate me-1 text-warning"></i> Adjust Stock
                          </a>
                        <?php endif; ?>
                        <a class="dropdown-item" href="<?= admin_storefront_url('product/' . $p['slug']); ?>" target="_blank">
                          <i class="fa-solid fa-arrow-up-right-from-square me-1 text-secondary"></i> View Live
                        </a>
                        <?php if ($this->can('products.delete')): ?>
                          <a class="dropdown-item text-danger" href="<?= site_url('products/delete/' . $p['id']); ?>" onclick="return confirm('Are you sure you want to delete this product?');">
                            <i class="fa-solid fa-trash-can me-1"></i> Delete
                          </a>
                        <?php endif; ?>
                      </div>
                    </div>
                  </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center text-muted py-4">No products found matching the criteria.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
