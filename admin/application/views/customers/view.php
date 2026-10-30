<div class="container-xxl flex-grow-1 container-p-y">
  <!-- Page Header -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
      <h4 class="fw-bold m-0">
        <span class="text-muted fw-light"><a href="<?= site_url('customers'); ?>" class="text-muted text-decoration-none">Customers</a> /</span>
        <?= html_escape(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? '')); ?>
      </h4>
      <small class="text-muted">Customer Profile, Orders, Abandoned Cart, Wishlist, Addresses & Reviews</small>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= site_url('customers'); ?>" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Directory
      </a>
      <?php if ($this->can('customers.manage')): ?>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editCustomerModal">
          <i class="fa-solid fa-user-pen me-1"></i> Edit Profile
        </button>
        <a href="<?= site_url('customers/toggle_status/' . $customer['id'] . '?redirect=customers/view/' . $customer['id']); ?>" 
           class="btn btn-<?= ($customer['status'] === 'active') ? 'outline-warning' : 'outline-success'; ?>"
           onclick="return confirm('Are you sure you want to <?= ($customer['status'] === 'active') ? 'ban' : 'activate'; ?> this customer account?');">
          <i class="fa-solid <?= ($customer['status'] === 'active') ? 'fa-user-slash' : 'fa-user-check'; ?> me-1"></i>
          <?= ($customer['status'] === 'active') ? 'Ban Account' : 'Activate Account'; ?>
        </a>
      <?php endif; ?>
    </div>
  </div>

  <div class="row">
    <!-- Left Column: Customer Profile Card -->
    <div class="col-xl-4 col-lg-5 col-md-5 mb-4">
      <div class="card mb-4">
        <div class="card-body text-center pt-4">
          <div class="avatar avatar-xl mx-auto mb-3" style="width: 80px; height: 80px;">
            <span class="avatar-initial rounded-circle bg-label-primary fs-2 fw-bold d-flex align-items-center justify-content-center">
              <?= strtoupper(substr($customer['first_name'] ?? 'U', 0, 1)); ?>
            </span>
          </div>
          <h5 class="mb-1 text-heading fw-bold">
            <?= html_escape(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? '')); ?>
          </h5>
          <p class="text-muted mb-2"><i class="fa-regular fa-envelope me-1"></i> <?= html_escape($customer['email']); ?></p>

          <div class="d-flex justify-content-center gap-2 mb-3">
            <span class="badge bg-label-<?= ($customer['status'] === 'active') ? 'success' : 'danger'; ?> px-3 py-1">
              <i class="fa-solid <?= ($customer['status'] === 'active') ? 'fa-circle-check' : 'fa-ban'; ?> me-1"></i>
              <?= ucfirst($customer['status'] ?? 'active'); ?>
            </span>
            <?php if (!empty($customer['group_name'])): ?>
              <span class="badge bg-label-info px-3 py-1">
                <i class="fa-solid fa-users-rectangle me-1"></i> <?= html_escape($customer['group_name']); ?>
                <?php if (!empty($customer['group_discount'])): ?>
                  (<?= $customer['group_discount']; ?>% off)
                <?php endif; ?>
              </span>
            <?php else: ?>
              <span class="badge bg-label-secondary px-3 py-1">General Group</span>
            <?php endif; ?>
          </div>

          <!-- Quick Engagement KPI Grid -->
          <div class="row g-2 border-top border-bottom py-3 my-3 text-start">
            <div class="col-6 border-end pb-2">
              <small class="text-muted d-block">Lifetime Orders</small>
              <h5 class="mb-0 fw-bold text-primary"><?= (int)($customer['total_orders'] ?? 0); ?></h5>
            </div>
            <div class="col-6 pb-2 ps-3">
              <small class="text-muted d-block">Lifetime Spent</small>
              <h5 class="mb-0 fw-bold text-success"><?= $currency_symbol . number_format($customer['total_spent'] ?? 0, 2); ?></h5>
            </div>
            <div class="col-6 border-end pt-2">
              <small class="text-muted d-block">In Cart Now</small>
              <h6 class="mb-0 fw-bold text-warning">
                <?= count($customer['abandoned_cart'] ?? []); ?> items
                <small class="text-muted fw-normal">(<?= $currency_symbol . number_format($customer['abandoned_cart_total'] ?? 0, 2); ?>)</small>
              </h6>
            </div>
            <div class="col-6 pt-2 ps-3">
              <small class="text-muted d-block">Wishlist</small>
              <h6 class="mb-0 fw-bold text-danger"><?= count($customer['wishlist_items'] ?? []); ?> items</h6>
            </div>
            <div class="col-6 border-end pt-2">
              <small class="text-muted d-block">Reviews Written</small>
              <h6 class="mb-0 fw-bold text-info">
                <?= (int)($customer['reviews_count'] ?? 0); ?> reviews
              </h6>
            </div>
            <div class="col-6 pt-2 ps-3">
              <small class="text-muted d-block">Avg. Rating Given</small>
              <h6 class="mb-0 fw-bold text-warning">
                <?php if (!empty($customer['reviews_count']) && $customer['reviews_count'] > 0): ?>
                  <i class="fa-solid fa-star text-warning" style="font-size: 12px;"></i> <?= number_format($customer['avg_rating_given'] ?? 0, 1); ?> / 5.0
                <?php else: ?>
                  <span class="text-muted fw-normal">N/A</span>
                <?php endif; ?>
              </h6>
            </div>
          </div>

          <!-- Customer Contact & Details -->
          <div class="text-start">
            <h6 class="fw-semibold text-muted text-uppercase small mb-3">Customer Information</h6>
            <ul class="list-unstyled mb-0">
              <li class="mb-2">
                <span class="fw-semibold me-2"><i class="fa-solid fa-id-badge text-muted me-1"></i> Customer ID:</span>
                <span class="text-muted">#<?= $customer['id']; ?></span>
              </li>
              <li class="mb-2">
                <span class="fw-semibold me-2"><i class="fa-regular fa-envelope text-muted me-1"></i> Email:</span>
                <a href="mailto:<?= html_escape($customer['email']); ?>" class="text-body"><?= html_escape($customer['email']); ?></a>
              </li>
              <li class="mb-2">
                <span class="fw-semibold me-2"><i class="fa-solid fa-phone text-muted me-1"></i> Phone:</span>
                <span class="text-muted"><?= !empty($customer['phone']) ? html_escape($customer['phone']) : 'Not provided'; ?></span>
              </li>
              <li class="mb-2">
                <span class="fw-semibold me-2"><i class="fa-regular fa-calendar-check text-muted me-1"></i> Registered:</span>
                <span class="text-muted"><?= date('M d, Y h:i A', strtotime($customer['created_at'])); ?></span>
              </li>
              <?php if (!empty($customer['updated_at'])): ?>
                <li class="mb-2">
                  <span class="fw-semibold me-2"><i class="fa-regular fa-clock text-muted me-1"></i> Last Active:</span>
                  <span class="text-muted"><?= date('M d, Y h:i A', strtotime($customer['updated_at'])); ?></span>
                </li>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>

      <!-- Quick Actions / Danger Zone -->
      <?php if ($this->can('customers.manage')): ?>
        <div class="card mb-4 border-light shadow-sm">
          <div class="card-body">
            <h6 class="fw-semibold text-danger small text-uppercase mb-3"><i class="fa-solid fa-triangle-exclamation me-1"></i> Danger Zone</h6>
            <p class="text-muted small mb-3">Deleting a customer permanently deletes their saved addresses and dissociates their reviews.</p>
            <a href="<?= site_url('customers/delete/' . $customer['id']); ?>" 
               class="btn btn-outline-danger btn-sm w-100"
               onclick="return confirm('WARNING: Are you absolutely sure you want to permanently delete this customer? This action cannot be undone.');">
              <i class="fa-solid fa-trash-can me-1"></i> Delete Customer Account
            </a>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Right Column: Navigation Tabs & Tab Panes -->
    <div class="col-xl-8 col-lg-7 col-md-7">
      <div class="nav-align-top mb-4">
        <!-- Tabs Nav -->
        <ul class="nav nav-pills mb-3 flex-wrap gap-2" role="tablist" id="customerTabs">
          <li class="nav-item">
            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#tab-orders" aria-controls="tab-orders" aria-selected="true">
              <i class="fa-solid fa-receipt me-1"></i> Orders
              <span class="badge rounded-pill bg-label-primary ms-1"><?= count($customer['orders'] ?? []); ?></span>
            </button>
          </li>
          <li class="nav-item">
            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-cart" aria-controls="tab-cart" aria-selected="false">
              <i class="fa-solid fa-cart-arrow-down me-1"></i> Abandoned Cart
              <span class="badge rounded-pill bg-label-warning ms-1"><?= count($customer['abandoned_cart'] ?? []); ?></span>
            </button>
          </li>
          <li class="nav-item">
            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-wishlist" aria-controls="tab-wishlist" aria-selected="false">
              <i class="fa-solid fa-heart me-1"></i> Wishlist
              <span class="badge rounded-pill bg-label-danger ms-1"><?= count($customer['wishlist_items'] ?? []); ?></span>
            </button>
          </li>
          <li class="nav-item">
            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-addresses" aria-controls="tab-addresses" aria-selected="false">
              <i class="fa-solid fa-map-location-dot me-1"></i> Addresses
              <span class="badge rounded-pill bg-label-info ms-1"><?= count($customer['addresses'] ?? []); ?></span>
            </button>
          </li>
          <li class="nav-item">
            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-reviews" aria-controls="tab-reviews" aria-selected="false">
              <i class="fa-solid fa-star me-1"></i> Ratings & Reviews
              <span class="badge rounded-pill bg-label-success ms-1"><?= count($customer['reviews'] ?? []); ?></span>
            </button>
          </li>
        </ul>

        <!-- Tab Contents -->
        <div class="tab-content p-0 bg-transparent shadow-none border-0">
          
          <!-- TAB 1: ORDERS HISTORY -->
          <div class="tab-pane fade show active" id="tab-orders" role="tabpanel">
            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Order History (<?= count($customer['orders'] ?? []); ?>)</h5>
                <?php if (!empty($customer['orders'])): ?>
                  <span class="text-muted small">Total Spend: <strong class="text-success"><?= $currency_symbol . number_format($customer['total_spent'] ?? 0, 2); ?></strong></span>
                <?php endif; ?>
              </div>
              <div class="table-responsive">
                <table class="table table-hover align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Order #</th>
                      <th>Date</th>
                      <th>Items</th>
                      <th>Total</th>
                      <th>Payment</th>
                      <th>Status</th>
                      <th class="text-end">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($customer['orders'])): ?>
                      <?php foreach ($customer['orders'] as $ord): ?>
                        <tr>
                          <td>
                            <a href="<?= site_url('orders/view/' . $ord['id']); ?>" class="fw-bold text-primary">
                              #<?= html_escape($ord['order_number']); ?>
                            </a>
                          </td>
                          <td>
                            <small class="text-muted"><?= date('M d, Y', strtotime($ord['created_at'])); ?></small>
                            <small class="text-muted d-block" style="font-size: 11px;"><?= date('h:i A', strtotime($ord['created_at'])); ?></small>
                          </td>
                          <td>
                            <span class="badge bg-label-secondary"><?= $ord['items_count'] ?? 1; ?> item<?= ($ord['items_count'] ?? 1) > 1 ? 's' : ''; ?></span>
                          </td>
                          <td>
                            <strong><?= $currency_symbol . number_format($ord['total_amount'], 2); ?></strong>
                          </td>
                          <td>
                            <span class="badge bg-label-<?= ($ord['payment_status'] === 'paid') ? 'success' : 'warning'; ?>">
                              <?= ucfirst($ord['payment_status']); ?>
                            </span>
                          </td>
                          <td>
                            <?php
                              $badge_cls = 'secondary';
                              if ($ord['order_status'] === 'delivered') $badge_cls = 'success';
                              elseif ($ord['order_status'] === 'shipped') $badge_cls = 'primary';
                              elseif ($ord['order_status'] === 'pending') $badge_cls = 'warning';
                              elseif ($ord['order_status'] === 'cancelled') $badge_cls = 'danger';
                            ?>
                            <span class="badge bg-<?= $badge_cls; ?>"><?= ucfirst($ord['order_status']); ?></span>
                          </td>
                          <td class="text-end">
                            <a href="<?= site_url('orders/view/' . $ord['id']); ?>" class="btn btn-xs btn-outline-primary">
                              <i class="fa-solid fa-eye me-1"></i> View Order
                            </a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                          <i class="fa-solid fa-basket-shopping fs-1 text-muted d-block mb-3"></i>
                          This customer has not placed any orders yet.
                        </td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- TAB 2: ABANDONED CART -->
          <div class="tab-pane fade" id="tab-cart" role="tabpanel">
            <div class="card">
              <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                  <h5 class="card-title mb-0">Active Abandoned Cart</h5>
                  <small class="text-muted">Unpurchased items currently sitting in the customer's shopping cart</small>
                </div>
                <?php if (!empty($customer['abandoned_cart'])): ?>
                  <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-label-warning fs-6 px-3 py-2">
                      Total Cart Value: <strong><?= $currency_symbol . number_format($customer['abandoned_cart_total'] ?? 0, 2); ?></strong>
                    </span>
                    <?php if ($this->can('customers.manage')): ?>
                      <a href="<?= site_url('customers/clear_cart/' . $customer['id']); ?>" 
                         class="btn btn-sm btn-outline-danger"
                         onclick="return confirm('Are you sure you want to clear this customer\'s entire cart?');">
                        <i class="fa-solid fa-trash-can me-1"></i> Clear Cart
                      </a>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>

              <?php if (!empty($customer['abandoned_cart'])): ?>
                <div class="table-responsive">
                  <table class="table table-hover align-middle">
                    <thead class="table-light">
                      <tr>
                        <th>Product Details</th>
                        <th>Variant / Specs</th>
                        <th>Unit Price</th>
                        <th>Qty</th>
                        <th>Line Total</th>
                        <th>Time Added</th>
                        <?php if ($this->can('customers.manage')): ?>
                          <th class="text-end">Action</th>
                        <?php endif; ?>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($customer['abandoned_cart'] as $item): ?>
                        <?php
                          $img_src = base_url('assets/img/elements/1.jpg');
                          if (!empty($item['main_image'])) {
                              if (filter_var($item['main_image'], FILTER_VALIDATE_URL)) {
                                  $img_src = $item['main_image'];
                              } elseif (strpos($item['main_image'], 'uploads/') === 0) {
                                  $img_src = base_url('../' . $item['main_image']);
                              } else {
                                  $img_src = base_url('../website/assets/images/' . $item['main_image']);
                              }
                          }
                        ?>
                        <tr>
                          <td>
                            <div class="d-flex align-items-center">
                              <img src="<?= $img_src; ?>" 
                                   alt="<?= html_escape($item['product_title'] ?? 'Product'); ?>" 
                                   class="rounded me-3 border" 
                                   style="width: 50px; height: 50px; object-fit: contain; background: #fff;"
                                   onerror="this.src='<?= base_url('assets/img/elements/1.jpg'); ?>'">
                              <div>
                                <h6 class="mb-0 text-truncate" style="max-width: 250px;">
                                  <?= html_escape($item['product_title'] ?? 'Product #' . $item['product_id']); ?>
                                </h6>
                                <small class="text-muted">ID: #<?= $item['product_id']; ?></small>
                                <?php if (isset($item['product_stock'])): ?>
                                  <div>
                                    <?php if ($item['product_stock'] <= 0): ?>
                                      <span class="badge bg-label-danger py-0" style="font-size: 10px;">Out of stock</span>
                                    <?php else: ?>
                                      <span class="badge bg-label-success py-0" style="font-size: 10px;"><?= $item['product_stock']; ?> in stock</span>
                                    <?php endif; ?>
                                  </div>
                                <?php endif; ?>
                              </div>
                            </div>
                          </td>
                          <td>
                            <?php if (!empty($item['variant_attributes'])): ?>
                              <div class="d-flex flex-wrap gap-1">
                                <?php foreach ($item['variant_attributes'] as $vattr): ?>
                                  <span class="badge bg-label-secondary">
                                    <?= html_escape($vattr['attr_name']); ?>: <?= html_escape($vattr['attr_value']); ?>
                                  </span>
                                <?php endforeach; ?>
                              </div>
                              <?php if (!empty($item['variant_sku'])): ?>
                                <small class="text-muted d-block mt-1">SKU: <?= html_escape($item['variant_sku']); ?></small>
                              <?php endif; ?>
                            <?php elseif (!empty($item['variant_id'])): ?>
                              <span class="badge bg-label-secondary">Variant #<?= $item['variant_id']; ?></span>
                            <?php else: ?>
                              <span class="text-muted small">Standard (No Variant)</span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <strong class="text-body"><?= $currency_symbol . number_format($item['effective_price'], 2); ?></strong>
                          </td>
                          <td>
                            <span class="badge bg-label-primary px-2 py-1 fs-6"><?= (int)$item['quantity']; ?></span>
                          </td>
                          <td>
                            <strong class="text-success"><?= $currency_symbol . number_format($item['line_total'], 2); ?></strong>
                          </td>
                          <td>
                            <small class="text-muted"><?= date('M d, Y', strtotime($item['added_at'])); ?></small>
                            <small class="text-muted d-block" style="font-size: 11px;"><?= date('h:i A', strtotime($item['added_at'])); ?></small>
                          </td>
                          <?php if ($this->can('customers.manage')): ?>
                            <td class="text-end">
                              <a href="<?= site_url('customers/remove_cart_item/' . $customer['id'] . '/' . $item['cart_item_id']); ?>" 
                                 class="btn btn-sm btn-icon btn-outline-danger" 
                                 title="Remove item from customer's cart"
                                 onclick="return confirm('Remove this item from the cart?');">
                                <i class="fa-solid fa-trash-can"></i>
                              </a>
                            </td>
                          <?php endif; ?>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php else: ?>
                <div class="card-body text-center py-5">
                  <div class="avatar avatar-xl mx-auto mb-3">
                    <span class="avatar-initial rounded-circle bg-label-secondary fs-1">
                      <i class="fa-solid fa-cart-shopping"></i>
                    </span>
                  </div>
                  <h6 class="fw-semibold">No Abandoned Cart Items</h6>
                  <p class="text-muted small mb-0">This customer currently has no active unpurchased products in their shopping cart.</p>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- TAB 3: WISHLIST -->
          <div class="tab-pane fade" id="tab-wishlist" role="tabpanel">
            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Saved Wishlist Items (<?= count($customer['wishlist_items'] ?? []); ?>)</h5>
                <small class="text-muted">Products customer saved for future purchase</small>
              </div>

              <?php if (!empty($customer['wishlist_items'])): ?>
                <div class="table-responsive">
                  <table class="table table-hover align-middle">
                    <thead class="table-light">
                      <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Current Price</th>
                        <th>Stock Status</th>
                        <th>Date Saved</th>
                        <?php if ($this->can('customers.manage')): ?>
                          <th class="text-end">Action</th>
                        <?php endif; ?>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($customer['wishlist_items'] as $witem): ?>
                        <?php
                          $w_img = base_url('assets/img/elements/1.jpg');
                          if (!empty($witem['main_image'])) {
                              if (filter_var($witem['main_image'], FILTER_VALIDATE_URL)) {
                                  $w_img = $witem['main_image'];
                              } elseif (strpos($witem['main_image'], 'uploads/') === 0) {
                                  $w_img = base_url('../' . $witem['main_image']);
                              } else {
                                  $w_img = base_url('../website/assets/images/' . $witem['main_image']);
                              }
                          }
                        ?>
                        <tr>
                          <td>
                            <div class="d-flex align-items-center">
                              <img src="<?= $w_img; ?>" 
                                   alt="<?= html_escape($witem['product_title'] ?? 'Product'); ?>" 
                                   class="rounded me-3 border" 
                                   style="width: 48px; height: 48px; object-fit: contain; background: #fff;"
                                   onerror="this.src='<?= base_url('assets/img/elements/1.jpg'); ?>'">
                              <div>
                                <h6 class="mb-0 text-truncate" style="max-width: 260px;">
                                  <?= html_escape($witem['product_title'] ?? 'Product #' . $witem['product_id']); ?>
                                </h6>
                                <small class="text-muted">Product ID: #<?= $witem['product_id']; ?></small>
                              </div>
                            </div>
                          </td>
                          <td>
                            <span class="badge bg-label-info"><?= html_escape($witem['category_name'] ?? 'General'); ?></span>
                          </td>
                          <td>
                            <?php if (!empty($witem['product_sale_price']) && (float)$witem['product_sale_price'] > 0): ?>
                              <span class="fw-bold text-danger"><?= $currency_symbol . number_format($witem['product_sale_price'], 2); ?></span>
                              <small class="text-muted text-decoration-line-through ms-1"><?= $currency_symbol . number_format($witem['product_price'], 2); ?></small>
                            <?php else: ?>
                              <span class="fw-bold"><?= $currency_symbol . number_format($witem['product_price'] ?? 0, 2); ?></span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <?php if (isset($witem['product_stock']) && $witem['product_stock'] <= 0): ?>
                              <span class="badge bg-label-danger">Out of Stock</span>
                            <?php else: ?>
                              <span class="badge bg-label-success">In Stock</span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <small class="text-muted"><?= date('M d, Y', strtotime($witem['wishlisted_at'])); ?></small>
                          </td>
                          <?php if ($this->can('customers.manage')): ?>
                            <td class="text-end">
                              <a href="<?= site_url('customers/remove_wishlist_item/' . $customer['id'] . '/' . $witem['wishlist_id']); ?>" 
                                 class="btn btn-sm btn-icon btn-outline-danger" 
                                 title="Remove from wishlist"
                                 onclick="return confirm('Remove this item from the customer\'s wishlist?');">
                                <i class="fa-solid fa-trash-can"></i>
                              </a>
                            </td>
                          <?php endif; ?>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php else: ?>
                <div class="card-body text-center py-5">
                  <div class="avatar avatar-xl mx-auto mb-3">
                    <span class="avatar-initial rounded-circle bg-label-secondary fs-1">
                      <i class="fa-regular fa-heart"></i>
                    </span>
                  </div>
                  <h6 class="fw-semibold">Wishlist is Empty</h6>
                  <p class="text-muted small mb-0">This customer has not saved any items to their wishlist yet.</p>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- TAB 4: SAVED ADDRESSES -->
          <div class="tab-pane fade" id="tab-addresses" role="tabpanel">
            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                  <h5 class="card-title mb-0">Customer Addresses (<?= count($customer['addresses'] ?? []); ?>)</h5>
                  <small class="text-muted">Saved shipping and billing delivery destinations</small>
                </div>
                <?php if ($this->can('customers.manage')): ?>
                  <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                    <i class="fa-solid fa-plus me-1"></i> Add Address
                  </button>
                <?php endif; ?>
              </div>

              <div class="card-body">
                <?php if (!empty($customer['addresses'])): ?>
                  <div class="row g-3">
                    <?php foreach ($customer['addresses'] as $addr): ?>
                      <div class="col-md-6">
                        <div class="card border <?= !empty($addr['is_default']) ? 'border-primary shadow-sm' : 'border-light-subtle'; ?> h-100">
                          <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                              <div>
                                <span class="badge bg-label-<?= ($addr['type'] === 'shipping') ? 'info' : 'secondary'; ?> text-uppercase">
                                  <?= ucfirst($addr['type']); ?>
                                </span>
                                <?php if (!empty($addr['is_default'])): ?>
                                  <span class="badge bg-primary ms-1"><i class="fa-solid fa-star me-1"></i> Default</span>
                                <?php endif; ?>
                              </div>
                              <?php if ($this->can('customers.manage')): ?>
                                <div class="d-flex gap-1">
                                  <button type="button" class="btn btn-xs btn-icon btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editAddressModal_<?= $addr['id']; ?>" title="Edit Address">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                  </button>
                                  <a href="<?= site_url('customers/delete_address/' . $customer['id'] . '/' . $addr['id']); ?>" 
                                     class="btn btn-xs btn-icon btn-outline-danger" 
                                     title="Delete Address"
                                     onclick="return confirm('Are you sure you want to delete this address?');">
                                    <i class="fa-solid fa-trash-can"></i>
                                  </a>
                                </div>
                              <?php endif; ?>
                            </div>

                            <h6 class="mb-1 fw-bold"><?= html_escape(($addr['first_name'] ?? '') . ' ' . ($addr['last_name'] ?? '')); ?></h6>
                            <?php if (!empty($addr['company'])): ?>
                              <p class="text-muted small mb-1"><i class="fa-solid fa-building me-1"></i> <?= html_escape($addr['company']); ?></p>
                            <?php endif; ?>

                            <div class="text-muted small mb-2">
                              <div><?= html_escape($addr['address_1']); ?></div>
                              <?php if (!empty($addr['address_2'])): ?>
                                <div><?= html_escape($addr['address_2']); ?></div>
                              <?php endif; ?>
                              <div><?= html_escape($addr['city'] . ', ' . $addr['state'] . ' ' . $addr['postcode']); ?></div>
                              <div><strong><?= html_escape($addr['country']); ?></strong></div>
                            </div>

                            <?php if (!empty($addr['phone'])): ?>
                              <div class="small text-muted border-top pt-2">
                                <i class="fa-solid fa-phone me-1"></i> <?= html_escape($addr['phone']); ?>
                              </div>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <div class="text-center py-5">
                    <div class="avatar avatar-xl mx-auto mb-3">
                      <span class="avatar-initial rounded-circle bg-label-secondary fs-1">
                        <i class="fa-solid fa-map-location-dot"></i>
                      </span>
                    </div>
                    <h6 class="fw-semibold">No Saved Addresses</h6>
                    <p class="text-muted small mb-3">This customer hasn't saved any delivery addresses yet.</p>
                    <?php if ($this->can('customers.manage')): ?>
                      <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                        <i class="fa-solid fa-plus me-1"></i> Add Address Now
                      </button>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- TAB 5: RATINGS & REVIEWS -->
          <div class="tab-pane fade" id="tab-reviews" role="tabpanel">
            <div class="card">
              <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                  <h5 class="card-title mb-0">Customer Ratings & Reviews (<?= count($customer['reviews'] ?? []); ?>)</h5>
                  <small class="text-muted">Feedback, star ratings, and uploaded customer photos</small>
                </div>
                <?php if (!empty($customer['reviews'])): ?>
                  <div class="d-flex align-items-center gap-2">
                    <div class="badge bg-label-warning fs-6 px-3 py-2">
                      <i class="fa-solid fa-star text-warning me-1"></i> Average Rating: <strong><?= number_format($customer['avg_rating_given'] ?? 0, 1); ?> / 5.0</strong>
                    </div>
                  </div>
                <?php endif; ?>
              </div>

              <div class="card-body">
                <?php if (!empty($customer['reviews'])): ?>
                  <div class="d-flex flex-column gap-3">
                    <?php foreach ($customer['reviews'] as $rev): ?>
                      <?php
                        $r_img = base_url('assets/img/elements/1.jpg');
                        if (!empty($rev['main_image'])) {
                            if (filter_var($rev['main_image'], FILTER_VALIDATE_URL)) {
                                $r_img = $rev['main_image'];
                            } elseif (strpos($rev['main_image'], 'uploads/') === 0) {
                                $r_img = base_url('../' . $rev['main_image']);
                            } else {
                                $r_img = base_url('../website/assets/images/' . $rev['main_image']);
                            }
                        }
                      ?>
                      <div class="border rounded p-3 bg-light-subtle">
                        <div class="row g-3">
                          <!-- Product Info Column -->
                          <div class="col-md-3 border-end">
                            <div class="d-flex align-items-start gap-2">
                              <img src="<?= $r_img; ?>" 
                                   alt="<?= html_escape($rev['product_title'] ?? 'Product'); ?>" 
                                   class="rounded border" 
                                   style="width: 55px; height: 55px; object-fit: contain; background: #fff;"
                                   onerror="this.src='<?= base_url('assets/img/elements/1.jpg'); ?>'">
                              <div>
                                <h6 class="mb-1 text-truncate" style="max-width: 140px;">
                                  <?= html_escape($rev['product_title'] ?? 'Product #' . $rev['product_id']); ?>
                                </h6>
                                <small class="text-muted d-block">ID: #<?= $rev['product_id']; ?></small>
                                <?php if (!empty($rev['order_number'])): ?>
                                  <span class="badge bg-label-primary py-0" style="font-size: 10px;">
                                    Order #<?= html_escape($rev['order_number']); ?>
                                  </span>
                                <?php endif; ?>
                              </div>
                            </div>
                          </div>

                          <!-- Review Body Column -->
                          <div class="col-md-6">
                            <!-- Star Rating -->
                            <div class="d-flex align-items-center mb-1">
                              <div class="text-warning me-2">
                                <?php for ($s = 1; $s <= 5; $s++): ?>
                                  <i class="fa-star <?= ($s <= (int)$rev['rating']) ? 'fa-solid' : 'fa-regular text-muted'; ?>" style="font-size: 13px;"></i>
                                <?php endfor; ?>
                              </div>
                              <span class="fw-bold text-dark me-2"><?= $rev['rating']; ?>.0</span>
                              <small class="text-muted"><?= date('M d, Y', strtotime($rev['created_at'])); ?></small>
                            </div>

                            <!-- Title & Review Text -->
                            <?php if (!empty($rev['title'])): ?>
                              <h6 class="mb-1 fw-bold"><?= html_escape($rev['title']); ?></h6>
                            <?php endif; ?>
                            <p class="text-muted mb-2 small" style="white-space: pre-line;"><?= html_escape($rev['review']); ?></p>

                            <!-- Customer Uploaded Review Photos -->
                            <?php if (!empty($rev['images_decoded'])): ?>
                              <div class="mt-2">
                                <small class="fw-semibold text-muted d-block mb-1"><i class="fa-solid fa-camera me-1"></i> Customer Photos (<?= count($rev['images_decoded']); ?>):</small>
                                <div class="d-flex flex-wrap gap-2">
                                  <?php foreach ($rev['images_decoded'] as $photo): ?>
                                    <?php 
                                      $photo_url = base_url('../website/assets/images/' . ltrim($photo, '/')); 
                                    ?>
                                    <a href="javascript:void(0);" onclick="openReviewPhoto('<?= $photo_url; ?>')">
                                      <img src="<?= $photo_url; ?>" 
                                           alt="Review photo" 
                                           class="rounded border shadow-xs" 
                                           style="width: 55px; height: 55px; object-fit: cover; cursor: pointer; transition: transform 0.2s;"
                                           onmouseover="this.style.transform='scale(1.08)'" 
                                           onmouseout="this.style.transform='scale(1)'"
                                           onerror="this.style.display='none'">
                                    </a>
                                  <?php endforeach; ?>
                                </div>
                              </div>
                            <?php endif; ?>
                          </div>

                          <!-- Moderation & Status Actions Column -->
                          <div class="col-md-3 text-md-end border-start">
                            <div class="mb-3">
                              <?php
                                $r_cls = 'secondary';
                                if ($rev['status'] === 'approved') $r_cls = 'success';
                                elseif ($rev['status'] === 'pending') $r_cls = 'warning';
                                elseif ($rev['status'] === 'rejected') $r_cls = 'danger';
                              ?>
                              <span class="badge bg-label-<?= $r_cls; ?> px-2 py-1">
                                <i class="fa-solid <?= ($rev['status'] === 'approved') ? 'fa-circle-check' : (($rev['status'] === 'rejected') ? 'fa-circle-xmark' : 'fa-clock'); ?> me-1"></i>
                                <?= ucfirst($rev['status']); ?>
                              </span>
                            </div>

                            <?php if ($this->can('customers.manage')): ?>
                              <div class="d-flex flex-column gap-1 align-items-md-end">
                                <?php if ($rev['status'] !== 'approved'): ?>
                                  <a href="<?= site_url('customers/update_review_status/' . $customer['id'] . '/' . $rev['id'] . '/approved'); ?>" 
                                     class="btn btn-xs btn-outline-success">
                                    <i class="fa-solid fa-check me-1"></i> Approve
                                  </a>
                                <?php endif; ?>

                                <?php if ($rev['status'] !== 'rejected'): ?>
                                  <a href="<?= site_url('customers/update_review_status/' . $customer['id'] . '/' . $rev['id'] . '/rejected'); ?>" 
                                     class="btn btn-xs btn-outline-warning">
                                    <i class="fa-solid fa-ban me-1"></i> Reject
                                  </a>
                                <?php endif; ?>

                                <?php if ($rev['status'] !== 'pending'): ?>
                                  <a href="<?= site_url('customers/update_review_status/' . $customer['id'] . '/' . $rev['id'] . '/pending'); ?>" 
                                     class="btn btn-xs btn-outline-secondary">
                                    <i class="fa-solid fa-clock me-1"></i> Pending
                                  </a>
                                <?php endif; ?>

                                <a href="<?= site_url('customers/delete_review/' . $customer['id'] . '/' . $rev['id']); ?>" 
                                   class="btn btn-xs btn-outline-danger mt-1"
                                   onclick="return confirm('Are you sure you want to permanently delete this customer review?');">
                                  <i class="fa-solid fa-trash-can me-1"></i> Delete
                                </a>
                              </div>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <div class="text-center py-5">
                    <div class="avatar avatar-xl mx-auto mb-3">
                      <span class="avatar-initial rounded-circle bg-label-secondary fs-1">
                        <i class="fa-solid fa-star"></i>
                      </span>
                    </div>
                    <h6 class="fw-semibold">No Reviews Found</h6>
                    <p class="text-muted small mb-0">This customer has not posted any product ratings or reviews yet.</p>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<!-- ==================== MODALS ==================== -->

<!-- Modal: Edit Customer Profile -->
<?php if ($this->can('customers.manage')): ?>
  <div class="modal fade" id="editCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="post" action="<?= site_url('customers/update/' . $customer['id']); ?>">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fa-solid fa-user-pen me-2 text-primary"></i>Edit Customer Profile</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                <input type="text" name="first_name" class="form-control" value="<?= html_escape($customer['first_name']); ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                <input type="text" name="last_name" class="form-control" value="<?= html_escape($customer['last_name']); ?>" required>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" value="<?= html_escape($customer['email']); ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Phone Number</label>
                <input type="text" name="phone" class="form-control" value="<?= html_escape($customer['phone'] ?? ''); ?>" placeholder="+91 9876543210">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Account Status</label>
                <select name="status" class="form-select">
                  <option value="active" <?= ($customer['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                  <option value="banned" <?= ($customer['status'] === 'banned') ? 'selected' : ''; ?>>Banned / Suspended</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Customer Group</label>
                <select name="customer_group_id" class="form-select">
                  <option value="">General / None</option>
                  <?php if (!empty($groups)): ?>
                    <?php foreach ($groups as $g): ?>
                      <option value="<?= $g['id']; ?>" <?= ((string)$customer['customer_group_id'] === (string)$g['id']) ? 'selected' : ''; ?>>
                        <?= html_escape($g['name']); ?> (<?= $g['discount_percent']; ?>% discount)
                      </option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>
              <div class="col-12 border-top pt-3">
                <label class="form-label fw-semibold">Reset Password (leave empty to keep current)</label>
                <input type="password" name="new_password" class="form-control" minlength="6" placeholder="Enter new password (optional)">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal: Add Address -->
  <div class="modal fade" id="addAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <form method="post" action="<?= site_url('customers/add_address/' . $customer['id']); ?>">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fa-solid fa-map-location-dot me-2 text-primary"></i>Add New Address</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Address Type</label>
                <select name="type" class="form-select">
                  <option value="shipping">Shipping Address</option>
                  <option value="billing">Billing Address</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Company (optional)</label>
                <input type="text" name="company" class="form-control" placeholder="Company Name">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                <input type="text" name="first_name" class="form-control" value="<?= html_escape($customer['first_name']); ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                <input type="text" name="last_name" class="form-control" value="<?= html_escape($customer['last_name']); ?>" required>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Street Address 1 <span class="text-danger">*</span></label>
                <input type="text" name="address_1" class="form-control" required placeholder="House number, street name">
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Street Address 2 (Apartment, suite, unit)</label>
                <input type="text" name="address_2" class="form-control" placeholder="Apartment, suite, etc.">
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">City <span class="text-danger">*</span></label>
                <input type="text" name="city" class="form-control" required>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">State / Province <span class="text-danger">*</span></label>
                <input type="text" name="state" class="form-control" required>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Postcode / ZIP <span class="text-danger">*</span></label>
                <input type="text" name="postcode" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Country <span class="text-danger">*</span></label>
                <input type="text" name="country" class="form-control" value="India" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Phone Number</label>
                <input type="text" name="phone" class="form-control" value="<?= html_escape($customer['phone'] ?? ''); ?>">
              </div>
              <div class="col-12">
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" name="is_default" value="1" id="isDefaultCheck">
                  <label class="form-check-label fw-semibold" for="isDefaultCheck">
                    Set as default address
                  </label>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Address</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Address Modals for each address -->
  <?php if (!empty($customer['addresses'])): ?>
    <?php foreach ($customer['addresses'] as $eaddr): ?>
      <div class="modal fade" id="editAddressModal_<?= $eaddr['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
            <form method="post" action="<?= site_url('customers/update_address/' . $customer['id'] . '/' . $eaddr['id']); ?>">
              <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Edit Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Address Type</label>
                    <select name="type" class="form-select">
                      <option value="shipping" <?= ($eaddr['type'] === 'shipping') ? 'selected' : ''; ?>>Shipping Address</option>
                      <option value="billing" <?= ($eaddr['type'] === 'billing') ? 'selected' : ''; ?>>Billing Address</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Company (optional)</label>
                    <input type="text" name="company" class="form-control" value="<?= html_escape($eaddr['company'] ?? ''); ?>">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" class="form-control" value="<?= html_escape($eaddr['first_name'] ?? ''); ?>" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="last_name" class="form-control" value="<?= html_escape($eaddr['last_name'] ?? ''); ?>" required>
                  </div>
                  <div class="col-12">
                    <label class="form-label fw-semibold">Street Address 1 <span class="text-danger">*</span></label>
                    <input type="text" name="address_1" class="form-control" value="<?= html_escape($eaddr['address_1'] ?? ''); ?>" required>
                  </div>
                  <div class="col-12">
                    <label class="form-label fw-semibold">Street Address 2 (Apartment, suite, unit)</label>
                    <input type="text" name="address_2" class="form-control" value="<?= html_escape($eaddr['address_2'] ?? ''); ?>">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">City <span class="text-danger">*</span></label>
                    <input type="text" name="city" class="form-control" value="<?= html_escape($eaddr['city'] ?? ''); ?>" required>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">State / Province <span class="text-danger">*</span></label>
                    <input type="text" name="state" class="form-control" value="<?= html_escape($eaddr['state'] ?? ''); ?>" required>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Postcode / ZIP <span class="text-danger">*</span></label>
                    <input type="text" name="postcode" class="form-control" value="<?= html_escape($eaddr['postcode'] ?? ''); ?>" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Country <span class="text-danger">*</span></label>
                    <input type="text" name="country" class="form-control" value="<?= html_escape($eaddr['country'] ?? 'India'); ?>" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="<?= html_escape($eaddr['phone'] ?? ''); ?>">
                  </div>
                  <div class="col-12">
                    <div class="form-check mt-2">
                      <input class="form-check-input" type="checkbox" name="is_default" value="1" id="isDefaultCheck_<?= $eaddr['id']; ?>" <?= !empty($eaddr['is_default']) ? 'checked' : ''; ?>>
                      <label class="form-check-label fw-semibold" for="isDefaultCheck_<?= $eaddr['id']; ?>">
                        Set as default address
                      </label>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Address</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
<?php endif; ?>

<!-- Review Photo Lightbox Modal -->
<div class="modal fade" id="reviewPhotoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-transparent border-0 shadow-none">
      <div class="modal-header border-0 pb-0 justify-content-end">
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center p-2">
        <img id="reviewPhotoModalImg" src="" alt="Review Photo" class="img-fluid rounded shadow" style="max-height: 80vh; object-fit: contain;">
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  // Support tab switching via URL hash (e.g. #tab-cart, #tab-wishlist, #tab-addresses, #tab-reviews)
  var hash = window.location.hash;
  if (hash) {
    var triggerEl = document.querySelector('button[data-bs-target="' + hash + '"]');
    if (triggerEl) {
      var tab = new bootstrap.Tab(triggerEl);
      tab.show();
    }
  }

  // Update hash in URL when tab changes
  var tabButtons = document.querySelectorAll('#customerTabs button[data-bs-toggle="tab"]');
  tabButtons.forEach(function (btn) {
    btn.addEventListener('shown.bs.tab', function (e) {
      var target = e.target.getAttribute('data-bs-target');
      if (history.pushState) {
        history.pushState(null, null, target);
      } else {
        location.hash = target;
      }
    });
  });
});

function openReviewPhoto(url) {
  var img = document.getElementById('reviewPhotoModalImg');
  if (img) {
    img.src = url;
    var modal = new bootstrap.Modal(document.getElementById('reviewPhotoModal'));
    modal.show();
  }
}
</script>
