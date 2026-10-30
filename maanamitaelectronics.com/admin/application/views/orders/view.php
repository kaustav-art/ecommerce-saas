<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold m-0"><span class="text-muted fw-light">Orders /</span> Order #<?= html_escape($order['order_number']); ?></h4>
      <small class="text-muted">Placed on <?= date('F d, Y \a\t h:i A', strtotime($order['created_at'])); ?></small>
    </div>
    <div>
      <?php if ($order['order_status'] !== 'cancelled'): ?>
        <a href="<?= site_url('orders/invoice/' . $order['id']); ?>" target="_blank" class="btn btn-primary me-2">
          <i class="fa-solid fa-file-invoice me-1"></i> Download Invoice
        </a>
      <?php endif; ?>
      <a href="<?= site_url('orders'); ?>" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Orders
      </a>
    </div>
  </div>

  <form id="order-fulfillment-form" action="<?= site_url('orders/update_status/' . $order['id']); ?>" method="POST">
    <!-- Hidden fields for individual item and shipment actions -->
    <input type="hidden" name="action_override" id="action_field" value="">
    <input type="hidden" name="item_id" id="target_item_id" value="">
    <input type="hidden" name="tracking_number" id="target_tracking_number" value="">
    <input type="hidden" name="shipment_item_ids" id="target_shipment_item_ids" value="">
    <input type="hidden" name="new_status" id="target_item_status" value="">

    <div class="row">
      <!-- Left Column: Ordered Items, Shipped Items, and Order Summary -->
      <div class="col-12 col-lg-8">
        <?php if ($this->session->flashdata('success')): ?>
          <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i><?= $this->session->flashdata('success'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
          <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i><?= $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <!-- 1. All Ordered Products Card -->
        <div class="card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div>
              <h5 class="card-title mb-0">Order Products (Multi-Shipment Management)</h5>
              <small class="text-muted">Select pending products below to dispatch, or cancel any individual product.</small>
            </div>
            <div>
              <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllPendingItems()">
                Select All Pending
              </button>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead class="table-light">
                <tr>
                  <th style="width: 40px;">
                    <input type="checkbox" id="check-all-items" class="form-check-input" onclick="toggleSelectAllItems(this)" title="Select All Pending Items">
                  </th>
                  <th>Product</th>
                  <th>Status</th>
                  <th>Price</th>
                  <th>Qty</th>
                  <th>Total</th>
                  <th class="text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($order['items'])): ?>
                  <?php foreach ($order['items'] as $item): 
                    $item_st = !empty($item['item_status']) ? strtolower($item['item_status']) : 'pending';
                    $badge_class = 'warning';
                    if ($item_st === 'delivered') $badge_class = 'success';
                    elseif ($item_st === 'shipped') $badge_class = 'primary';
                    elseif ($item_st === 'cancelled') $badge_class = 'danger';
                    $is_pending = ($item_st === 'pending');
                  ?>
                    <tr id="item-row-<?= $item['id']; ?>" class="<?= ($item_st === 'cancelled') ? 'table-light opacity-75' : (($item_st === 'delivered') ? 'table-light' : ''); ?>">
                      <td>
                        <?php if ($is_pending): ?>
                          <input type="checkbox" name="selected_items[]" value="<?= $item['id']; ?>" class="form-check-input item-select-check" data-id="<?= $item['id']; ?>" data-status="pending" onchange="updateSelectedItemsUI()">
                        <?php elseif ($item_st === 'cancelled'): ?>
                          <span title="Cancelled product cannot be shipped"><i class="fa-solid fa-ban text-danger"></i></span>
                        <?php elseif ($item_st === 'shipped'): ?>
                          <span title="Already Shipped - manage in Shipped card below"><i class="fa-solid fa-truck-fast text-primary"></i></span>
                        <?php elseif ($item_st === 'delivered'): ?>
                          <span title="Delivered"><i class="fa-solid fa-circle-check text-success"></i></span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <?php if (!empty($item['product_image'])): ?>
                            <img src="<?= base_url('../website/assets/images/' . $item['product_image']); ?>" class="rounded me-2 border" style="width: 46px; height: 46px; object-fit: contain; background: #fafafa;" onerror="this.src='<?= base_url('assets/img/elements/1.jpg'); ?>'" />
                          <?php endif; ?>
                          <div>
                            <strong class="text-dark d-block"><?= html_escape($item['product_title']); ?></strong>
                            <?php if (!empty($item['variant_title'])): ?>
                              <div class="small text-muted"><?= html_escape($item['variant_title']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($item['product_sku'])): ?>
                              <div class="small text-muted font-monospace">SKU: <?= html_escape($item['product_sku']); ?></div>
                            <?php endif; ?>
                          </div>
                        </div>
                      </td>
                      <td>
                        <span class="badge bg-label-<?= $badge_class; ?> text-uppercase" style="font-size: 11px;">
                          <?= ucfirst($item_st); ?>
                        </span>
                      </td>
                      <td><?= $currency_symbol . number_format($item['price'], 2); ?></td>
                      <td><?= $item['quantity']; ?></td>
                      <td><strong><?= $currency_symbol . number_format($item['total'], 2); ?></strong></td>
                      <td class="text-end text-nowrap">
                        <?php if ($is_pending): ?>
                          <button type="button" class="btn btn-xs btn-outline-danger" onclick="cancelOrderItem(<?= $item['id']; ?>, '<?= html_escape(addslashes($item['product_title'])); ?>')" title="Cancel this product">
                            <i class="fa-solid fa-xmark me-1"></i> Cancel
                          </button>
                        <?php elseif ($item_st === 'cancelled'): ?>
                          <span class="badge bg-label-danger">Cancelled</span>
                        <?php elseif ($item_st === 'shipped'): ?>
                          <span class="badge bg-label-primary"><i class="fa-solid fa-truck-fast me-1"></i>Shipped</span>
                        <?php elseif ($item_st === 'delivered'): ?>
                          <span class="badge bg-label-success"><i class="fa-solid fa-circle-check me-1"></i>Delivered</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <div class="card-body border-top">
            <div class="row justify-content-end">
              <div class="col-md-5">
                <div class="d-flex justify-content-between mb-2">
                  <span>Subtotal:</span>
                  <span><?= $currency_symbol . number_format($order['subtotal'], 2); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span>Discount:</span>
                  <span class="text-danger">-<?= $currency_symbol . number_format($order['discount_amount'], 2); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span>Shipping Fee:</span>
                  <span><?= $currency_symbol . number_format($order['shipping_fee'], 2); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span>Tax:</span>
                  <span><?= $currency_symbol . number_format($order['tax_amount'], 2); ?></span>
                </div>
                <div class="d-flex justify-content-between border-top pt-2">
                  <strong class="fs-5">Grand Total:</strong>
                  <strong class="fs-5 text-primary"><?= $currency_symbol . number_format($order['total_amount'], 2); ?></strong>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. Shipped Products Management Card (Grouped by shipment / AWB) -->
        <?php 
          $shipments = [];
          if (!empty($order['items'])) {
            foreach ($order['items'] as $it) {
              $it_st = strtolower($it['item_status'] ?? 'pending');
              if (in_array($it_st, ['shipped', 'delivered']) || (!empty($it['shipped_at']) && $it_st === 'cancelled')) {
                $shipment_key = !empty($it['tracking_number']) ? trim($it['tracking_number']) : ('dispatch_' . ($it['shipped_at'] ?? $it['id']));
                if (!isset($shipments[$shipment_key])) {
                  $shipments[$shipment_key] = [
                    'tracking_number' => $it['tracking_number'] ?? '',
                    'courier_name'    => $it['courier_name'] ?? 'Courier',
                    'tracking_url'    => $it['tracking_url'] ?? '',
                    'shipped_at'      => $it['shipped_at'] ?? '',
                    'delivered_at'    => $it['delivered_at'] ?? '',
                    'items'           => [],
                    'item_ids'        => []
                  ];
                }
                $shipments[$shipment_key]['items'][] = $it;
                $shipments[$shipment_key]['item_ids'][] = (int) $it['id'];
                if (!empty($it['delivered_at']) && empty($shipments[$shipment_key]['delivered_at'])) {
                  $shipments[$shipment_key]['delivered_at'] = $it['delivered_at'];
                }
              }
            }
          }

          foreach ($shipments as $k => &$sh) {
            $all_delivered = true;
            $all_cancelled = true;
            $has_shipped   = false;
            $has_delivered = false;
            foreach ($sh['items'] as $s_item) {
              $st = strtolower($s_item['item_status'] ?? 'shipped');
              if ($st !== 'delivered' && $st !== 'cancelled') {
                $all_delivered = false;
              }
              if ($st !== 'cancelled') {
                $all_cancelled = false;
              }
              if ($st === 'shipped') {
                $has_shipped = true;
              }
              if ($st === 'delivered') {
                $has_delivered = true;
              }
            }
            if ($all_cancelled) {
              $sh['status'] = 'cancelled';
            } elseif ($all_delivered && $has_delivered) {
              $sh['status'] = 'delivered';
            } else {
              $sh['status'] = 'shipped';
            }
          }
          unset($sh);
        ?>
        <?php if (!empty($shipments)): ?>
          <div class="card mb-4 border shadow-none" style="border-color: #2874f0 !important;">
            <div class="card-header bg-label-primary py-3 d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title mb-0 fs-6 fw-bold"><i class="fa-solid fa-truck-fast me-2 text-primary"></i>Shipped Products</h5>
                <small class="text-muted">Dispatched shipments. Click the three dots to view products, mark delivered, or cancel.</small>
              </div>
              <span class="badge bg-primary"><?= count($shipments); ?> shipment<?= (count($shipments) > 1) ? 's' : ''; ?></span>
            </div>
            <div class="table-responsive">
              <table class="table align-middle">
                <thead class="table-light">
                  <tr>
                    <th>AWB / Tracking Number</th>
                    <th>Courier Partner</th>
                    <th>Dispatched On</th>
                    <th>Status</th>
                    <th class="text-end" style="width: 80px;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $shipment_idx = 0; ?>
                  <?php foreach ($shipments as $shipment): 
                    $shipment_idx++;
                    $sh_st = $shipment['status'];
                    $sh_badge = 'primary';
                    if ($sh_st === 'delivered') $sh_badge = 'success';
                    elseif ($sh_st === 'cancelled') $sh_badge = 'danger';
                    $item_count = count($shipment['items']);
                  ?>
                    <tr>
                      <td>
                        <div class="d-flex align-items-center">
                          <i class="fa-solid fa-barcode text-primary me-2 fs-5"></i>
                          <div>
                            <?php if (!empty($shipment['tracking_number'])): ?>
                              <span class="font-monospace fw-bold text-dark fs-6 d-block"><?= html_escape($shipment['tracking_number']); ?></span>
                            <?php else: ?>
                              <span class="text-muted d-block font-monospace">N/A</span>
                            <?php endif; ?>
                            <span class="badge bg-label-secondary" style="font-size: 11px;">
                              <?= $item_count; ?> product<?= ($item_count > 1) ? 's' : ''; ?>
                            </span>
                            <?php if (!empty($shipment['tracking_url'])): ?>
                              <a href="<?= html_escape($shipment['tracking_url']); ?>" target="_blank" class="small text-primary ms-1 text-decoration-none" title="Track Online">
                                <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 10px;"></i>
                              </a>
                            <?php endif; ?>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="fw-bold text-dark">
                          <i class="fa-solid fa-truck-fast text-primary me-1"></i>
                          <?= html_escape($shipment['courier_name'] ?: 'Courier'); ?>
                        </div>
                      </td>
                      <td>
                        <small class="text-muted">
                          <?= !empty($shipment['shipped_at']) ? date('M d, Y \a\t h:i A', strtotime($shipment['shipped_at'])) : '-'; ?>
                        </small>
                      </td>
                      <td>
                        <span class="badge bg-label-<?= $sh_badge; ?> text-uppercase" style="font-size: 11px;">
                          <?= ucfirst($sh_st); ?>
                        </span>
                        <?php if ($sh_st === 'delivered' && !empty($shipment['delivered_at'])): ?>
                          <div class="small text-success mt-0.5"><i class="fa-solid fa-check me-1"></i><?= date('M d, Y', strtotime($shipment['delivered_at'])); ?></div>
                        <?php endif; ?>
                      </td>
                      <td class="text-end">
                        <div class="dropdown">
                          <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false" title="Shipment Actions">
                            <i class="fa-solid fa-ellipsis-vertical fs-5"></i>
                          </button>
                          <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#shipmentModal_<?= $shipment_idx; ?>">
                              <i class="fa-solid fa-eye me-2 text-primary"></i> View
                            </a>
                            <?php if ($sh_st !== 'delivered'): ?>
                              <a class="dropdown-item text-success" href="javascript:void(0)" onclick='changeShipmentStatus("<?= html_escape(addslashes($shipment['tracking_number'])); ?>", <?= json_encode($shipment['item_ids']); ?>, "delivered")'>
                                <i class="fa-solid fa-circle-check me-2"></i> Delivered
                              </a>
                            <?php endif; ?>
                            <?php if ($sh_st !== 'cancelled'): ?>
                              <a class="dropdown-item text-danger" href="javascript:void(0)" onclick='changeShipmentStatus("<?= html_escape(addslashes($shipment['tracking_number'])); ?>", <?= json_encode($shipment['item_ids']); ?>, "cancelled")'>
                                <i class="fa-solid fa-ban me-2"></i> Cancel
                              </a>
                            <?php endif; ?>
                          </div>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Modals for each shipment to show shipped products -->
          <?php $modal_idx = 0; ?>
          <?php foreach ($shipments as $shipment): 
            $modal_idx++;
          ?>
            <div class="modal fade" id="shipmentModal_<?= $modal_idx; ?>" tabindex="-1" aria-labelledby="modalLabel_<?= $modal_idx; ?>" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header bg-light border-bottom">
                    <div>
                      <h5 class="modal-title fw-bold" id="modalLabel_<?= $modal_idx; ?>">
                        <i class="fa-solid fa-boxes-packing text-primary me-2"></i>
                        Shipped Products (AWB: <?= html_escape($shipment['tracking_number'] ?: 'N/A'); ?>)
                      </h5>
                      <small class="text-muted">
                        Courier: <strong><?= html_escape($shipment['courier_name']); ?></strong>
                        <?php if (!empty($shipment['shipped_at'])): ?>
                          &bull; Dispatched on <?= date('M d, Y \a\t h:i A', strtotime($shipment['shipped_at'])); ?>
                        <?php endif; ?>
                      </small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body p-0">
                    <div class="table-responsive">
                      <table class="table align-middle mb-0">
                        <thead class="table-light">
                          <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Item Status</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($shipment['items'] as $m_item): 
                            $m_st = strtolower($m_item['item_status'] ?? 'shipped');
                            $m_badge = 'primary';
                            if ($m_st === 'delivered') $m_badge = 'success';
                            elseif ($m_st === 'cancelled') $m_badge = 'danger';
                          ?>
                            <tr>
                              <td>
                                <div class="d-flex align-items-center">
                                  <?php if (!empty($m_item['product_image'])): ?>
                                    <img src="<?= base_url('../website/assets/images/' . $m_item['product_image']); ?>" class="rounded me-2 border" style="width: 44px; height: 44px; object-fit: contain; background: #fafafa;" onerror="this.src='<?= base_url('assets/img/elements/1.jpg'); ?>'" />
                                  <?php endif; ?>
                                  <div>
                                    <strong class="text-dark d-block"><?= html_escape($m_item['product_title']); ?></strong>
                                    <?php if (!empty($m_item['variant_title'])): ?>
                                      <div class="small text-muted"><?= html_escape($m_item['variant_title']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($m_item['product_sku'])): ?>
                                      <div class="small text-muted font-monospace">SKU: <?= html_escape($m_item['product_sku']); ?></div>
                                    <?php endif; ?>
                                  </div>
                                </div>
                              </td>
                              <td><?= $currency_symbol . number_format($m_item['price'], 2); ?></td>
                              <td><span class="badge bg-label-dark"><?= $m_item['quantity']; ?></span></td>
                              <td><strong><?= $currency_symbol . number_format($m_item['total'], 2); ?></strong></td>
                              <td>
                                <span class="badge bg-label-<?= $m_badge; ?> text-uppercase" style="font-size: 11px;">
                                  <?= ucfirst($m_st); ?>
                                </span>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                  <div class="modal-footer bg-light border-top d-flex justify-content-between">
                    <div>
                      <?php if (!empty($shipment['tracking_url'])): ?>
                        <a href="<?= html_escape($shipment['tracking_url']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                          <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Track on Courier Website
                        </a>
                      <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Right Column: Dispatch Panel, Payment Status, and Customer Details -->
      <div class="col-12 col-lg-4">
        <?php if ($this->can('orders.manage')): ?>
          <?php 
            $pending_items = array_filter($order['items'], function($it) {
              return (strtolower($it['item_status'] ?? 'pending')) === 'pending';
            });
            $pending_count = count($pending_items);
          ?>

          <!-- Split Shipment / Courier Dispatch Actions Card -->
          <div class="card mb-4 border shadow-none" style="border-color: #2874f0 !important;">
            <div class="card-header bg-label-primary py-3">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fs-6 fw-bold"><i class="fa-solid fa-boxes-packing me-1"></i> Courier Dispatch Actions</h5>
                <span id="selected-count-badge" class="badge bg-primary">0 of <?= $pending_count; ?> selected</span>
              </div>
            </div>
            <div class="card-body pt-3">
              <?php if ($pending_count > 0): ?>
                <p class="small text-muted mb-3">
                  Check the pending product(s) in the table you want to courier, enter the tracking info, and click Dispatch.
                </p>

                <!-- Fresh, Empty Courier & AWB Details Form (Refreshed on each load) -->
                <div class="border rounded p-3 mb-3 bg-light" id="courier-details-box">
                  <h6 class="fw-bold mb-2 text-dark small">
                    <i class="fa-solid fa-truck-fast text-primary me-1"></i> Courier & AWB Details
                  </h6>

                  <div class="mb-2">
                    <label class="form-label small fw-semibold" for="courier_select">Courier Partner</label>
                    <select id="courier_select" class="form-select form-select-sm" onchange="onCourierChange(this.value)">
                      <option value="" selected>-- Select Courier Partner --</option>
                      <option value="DTDC">DTDC Express</option>
                      <option value="Xpressbees">Xpressbees</option>
                      <option value="Delhivery">Delhivery</option>
                      <option value="Blue Dart">Blue Dart</option>
                      <option value="Ekart Logistics">Ekart Logistics</option>
                      <option value="India Post / Speed Post">India Post / Speed Post</option>
                      <option value="Shadowfax">Shadowfax</option>
                      <option value="custom">Other / Custom</option>
                    </select>
                    <input type="text" name="courier_name" id="courier_name" class="form-control form-control-sm mt-1 d-none" placeholder="Courier Company Name" value="">
                  </div>

                  <div class="mb-2">
                    <label class="form-label small fw-semibold" for="tracking_number">AWB / Tracking Number</label>
                    <input type="text" name="tracking_number" id="tracking_number" class="form-control form-control-sm" placeholder="e.g. DTDC123456789 or 14324234" value="" oninput="onAwbInput(this.value)">
                  </div>

                  <div class="mb-3">
                    <label class="form-label small fw-semibold" for="tracking_url">Website Tracking Link</label>
                    <input type="url" name="tracking_url" id="tracking_url" class="form-control form-control-sm" placeholder="https://www.dtdc.in/tracking.asp" value="">
                    <small class="text-muted" style="font-size: 11px;">Customer uses this link to track their package.</small>
                  </div>

                  <button type="submit" name="action" value="ship_items" class="btn btn-primary w-100">
                    <i class="fa-solid fa-truck-fast me-1"></i> Dispatch Selected Products
                  </button>
                </div>
              <?php else: ?>
                <div class="alert alert-info py-2 px-3 small mb-0">
                  <i class="fa-solid fa-circle-check me-1 text-primary"></i> All products in this order have been dispatched or cancelled. No pending items left to ship.
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Payment Status Card (Only Payment Status here - fulfillment is managed per-product) -->
          <div class="card mb-4">
            <div class="card-header py-3">
              <h5 class="card-title mb-0 fs-6 fw-bold"><i class="fa-solid fa-credit-card me-1 text-primary"></i> Payment Status</h5>
            </div>
            <div class="card-body">
              <div class="mb-3">
                <label class="form-label fw-semibold" for="payment_status">Order Payment Status</label>
                <select name="payment_status" id="payment_status" class="form-select">
                  <option value="pending" <?= ($order['payment_status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                  <option value="paid" <?= ($order['payment_status'] === 'paid') ? 'selected' : ''; ?>>Paid</option>
                  <option value="failed" <?= ($order['payment_status'] === 'failed') ? 'selected' : ''; ?>>Failed</option>
                  <option value="refunded" <?= ($order['payment_status'] === 'refunded') ? 'selected' : ''; ?>>Refunded</option>
                </select>
              </div>

              <button type="submit" name="action" value="update_payment" class="btn btn-outline-primary w-100">
                <i class="fa-solid fa-floppy-disk me-1"></i> Update Payment Status
              </button>
            </div>
          </div>
        <?php endif; ?>

        <!-- Customer Details Card -->
        <div class="card mb-4">
          <div class="card-header py-3">
            <h5 class="card-title mb-0 fs-6 fw-bold">Customer & Shipping Info</h5>
          </div>
          <div class="card-body">
            <h6 class="mb-1 text-dark fw-bold"><?= html_escape($order['customer_name']); ?></h6>
            <p class="mb-2 text-muted small">
              <i class="fa-solid fa-envelope me-1"></i> <?= html_escape($order['customer_email']); ?><br>
              <i class="fa-solid fa-phone me-1"></i> <?= html_escape($order['customer_phone']); ?>
            </p>

            <h6 class="mt-3 mb-1 small fw-bold text-uppercase text-secondary">Shipping Address:</h6>
            <p class="text-secondary mb-2 small"><?= nl2br(html_escape($order['shipping_address'])); ?></p>

            <h6 class="mt-3 mb-1 small fw-bold text-uppercase text-secondary">Payment Method:</h6>
            <span class="badge bg-label-dark text-uppercase"><?= strtoupper($order['payment_method']); ?></span>
            <?php if (!empty($order['payment_transaction_id'])): ?>
              <div class="small text-muted mt-1 font-monospace">Txn: <?= html_escape($order['payment_transaction_id']); ?></div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
function toggleSelectAllItems(master) {
  var checkboxes = document.querySelectorAll('.item-select-check:not(:disabled)');
  checkboxes.forEach(function(cb) {
    cb.checked = master.checked;
  });
  updateSelectedItemsUI();
}

function selectAllPendingItems() {
  var checkboxes = document.querySelectorAll('.item-select-check');
  var count = 0;
  checkboxes.forEach(function(cb) {
    if (cb.getAttribute('data-status') === 'pending' && !cb.disabled) {
      cb.checked = true;
      count++;
    }
  });
  var master = document.getElementById('check-all-items');
  if (master && count > 0) {
    master.checked = true;
  }
  updateSelectedItemsUI();
}

function updateSelectedItemsUI() {
  var checked = document.querySelectorAll('.item-select-check:checked');
  var countBadge = document.getElementById('selected-count-badge');
  if (countBadge) {
    var totalPending = document.querySelectorAll('.item-select-check').length;
    countBadge.textContent = checked.length + ' of ' + totalPending + ' selected';
  }
}

function cancelOrderItem(itemId, title) {
  if (confirm('Are you sure you want to cancel "' + title + '" from this order? Cancelled products cannot be shipped.')) {
    document.getElementById('action_field').value = 'cancel_item';
    document.getElementById('target_item_id').value = itemId;
    document.getElementById('order-fulfillment-form').submit();
  }
}

function changeShippedItemStatus(itemId, newStatus, title) {
  var label = newStatus === 'delivered' ? 'Delivered' : 'Cancelled';
  if (confirm('Are you sure you want to mark "' + title + '" as ' + label + '?')) {
    document.getElementById('action_field').value = 'update_item_status';
    document.getElementById('target_item_id').value = itemId;
    document.getElementById('target_item_status').value = newStatus;
    document.getElementById('order-fulfillment-form').submit();
  }
}

function onCourierChange(val) {
  var nameInput = document.getElementById('courier_name');
  var awbElem = document.getElementById('tracking_number');
  var awb = awbElem ? awbElem.value.trim() : '';

  if (val === 'custom') {
    nameInput.classList.remove('d-none');
    nameInput.focus();
  } else if (val) {
    nameInput.value = val;
    nameInput.classList.add('d-none');
    updateCourierUrl(val, awb);
  } else {
    nameInput.value = '';
    nameInput.classList.add('d-none');
    var urlInput = document.getElementById('tracking_url');
    if (urlInput) urlInput.value = '';
  }
}

function onAwbInput(awb) {
  var courier = document.getElementById('courier_name') ? document.getElementById('courier_name').value.trim() : '';
  if (courier) {
    updateCourierUrl(courier, awb);
  }
}

function updateCourierUrl(courier, awb) {
  var urlInput = document.getElementById('tracking_url');
  if (!urlInput) return;
  var c = courier.toLowerCase();
  if (c.indexOf('xpress') !== -1) {
    urlInput.value = awb ? 'https://www.xpressbees.com/shipment/tracking?awbNo=' + encodeURIComponent(awb) : 'https://www.xpressbees.com/';
  } else if (c.indexOf('delhivery') !== -1) {
    urlInput.value = awb ? 'https://www.delhivery.com/track/package/' + encodeURIComponent(awb) : 'https://www.delhivery.com/';
  } else if (c.indexOf('dtdc') !== -1) {
    urlInput.value = 'https://www.dtdc.in/tracking.asp';
  } else if (c.indexOf('blue') !== -1) {
    urlInput.value = 'https://www.bluedart.com/tracking';
  } else if (c.indexOf('ekart') !== -1) {
    urlInput.value = awb ? 'https://ekartlogistics.com/shipmenttrack/' + encodeURIComponent(awb) : 'https://ekartlogistics.com/';
  } else if (c.indexOf('shadowfax') !== -1) {
    urlInput.value = awb ? 'https://tracker.shadowfax.in/#/track?awb=' + encodeURIComponent(awb) : 'https://tracker.shadowfax.in/';
  } else if (c.indexOf('speed') !== -1 || c.indexOf('post') !== -1) {
    urlInput.value = 'https://www.indiapost.gov.in/_layouts/15/dpt.cept.tracking/trackconsignment.aspx';
  }
}

function changeShipmentStatus(trackingNumber, itemIdsArray, newStatus) {
  var label = newStatus === 'delivered' ? 'Delivered' : 'Cancelled';
  var msg = 'Are you sure you want to mark shipment ' + (trackingNumber ? ('(AWB: ' + trackingNumber + ')') : '') + ' as ' + label + '?';
  if (confirm(msg)) {
    document.getElementById('action_field').value = 'update_shipment_status';
    document.getElementById('target_tracking_number').value = trackingNumber;
    document.getElementById('target_shipment_item_ids').value = itemIdsArray ? itemIdsArray.join(',') : '';
    document.getElementById('target_item_status').value = newStatus;
    document.getElementById('order-fulfillment-form').submit();
  }
}

// Client-side dispatch validation
var orderForm = document.getElementById('order-fulfillment-form');
if (orderForm) {
  orderForm.addEventListener('submit', function(e) {
    var submitter = e.submitter;
    var actionVal = submitter ? submitter.value : '';
    var overrideVal = document.getElementById('action_field').value;
    
    if (overrideVal === 'cancel_item' || overrideVal === 'update_item_status' || overrideVal === 'update_shipment_status' || actionVal === 'update_payment') {
      return true;
    }
    
    if (actionVal === 'ship_items') {
      var checked = document.querySelectorAll('.item-select-check:checked');
      if (checked.length === 0) {
        e.preventDefault();
        alert('Please select at least one pending product from the table to dispatch.');
        return false;
      }
      var courierVal = document.getElementById('courier_select').value;
      var courierName = document.getElementById('courier_name').value.trim();
      var tracking = document.getElementById('tracking_number').value.trim();
      if (!courierVal || (courierVal === 'custom' && !courierName)) {
        e.preventDefault();
        alert('Please select a Courier Partner.');
        document.getElementById('courier_select').focus();
        return false;
      }
      if (!tracking) {
        e.preventDefault();
        alert('Please enter an AWB / Tracking Number.');
        document.getElementById('tracking_number').focus();
        return false;
      }
    }
  });
}
</script>
