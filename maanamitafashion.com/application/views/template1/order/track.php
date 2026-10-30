        <!-- Breadcrumb -->
        <div class="bg-light py-3 border-bottom">
            <div class="container">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="<?= site_url('home'); ?>">Home</a></li>
                        <li class="breadcrumb-item active">Order Tracking</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Order Tracking Section -->
        <section class="py-4 py-md-5 bg-light">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card border shadow-sm p-3 p-md-5 bg-white mb-4">
                            <h3 class="fw-bold mb-2 text-center fs-4 fs-md-3">Track Your Order</h3>
                            <p class="text-muted text-center small mb-4">Enter your Order ID (found in your receipt/confirmation) and Billing Email Address to track shipping and delivery status.</p>

                            <form action="<?= site_url('order/track'); ?>" method="GET" class="row g-3 justify-content-center mb-4">
                                <div class="col-md-5">
                                    <label class="form-label small fw-bold">Order Number</label>
                                    <input type="text" name="order_number" class="form-control" placeholder="e.g. ORD-2026-1001" value="<?= html_escape($this->input->get('order_number') ?? ''); ?>" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small fw-bold">Billing Email</label>
                                    <input type="email" name="email" class="form-control" placeholder="john@example.com" value="<?= html_escape($this->input->get('email') ?? ''); ?>">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100 py-2">Track</button>
                                </div>
                            </form>

                            <?php if ($searched): ?>
                                <?php if ($order): ?>
                                    <div class="border rounded p-4 text-start bg-light mt-4">
                                        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                            <h5 class="fw-bold mb-0">Order #<?= html_escape($order['order_number']); ?></h5>
                                            <span class="badge bg-primary fs-6"><?= ucfirst($order['order_status']); ?></span>
                                        </div>

                                        <div class="row g-3 mb-4">
                                            <div class="col-sm-4">
                                                <small class="text-muted text-uppercase fw-bold">Placed On:</small>
                                                <div><?= date('M d, Y', strtotime($order['created_at'])); ?></div>
                                            </div>
                                            <div class="col-sm-4">
                                                <small class="text-muted text-uppercase fw-bold">Payment Method:</small>
                                                <div><?= strtoupper($order['payment_method']); ?> (<?= ucfirst($order['payment_status']); ?>)</div>
                                            </div>
                                            <div class="col-sm-4">
                                                <small class="text-muted text-uppercase fw-bold">Grand Total:</small>
                                                <div class="fw-bold text-dark"><?= $currency_symbol . number_format($order['total_amount'], 2); ?></div>
                                            </div>
                                        </div>

                                        <!-- 3-Step Tracking Timeline -->
                                        <div class="py-3">
                                            <?php 
                                                $t_status = strtolower($order['order_status']);
                                                $t_placed_date = date('M d', strtotime($order['created_at']));
                                                $t_delivered_date = !empty($order['delivered_at']) ? date('M d', strtotime($order['delivered_at'])) : date('M d', strtotime($order['updated_at'] ?? $order['created_at']));
                                                $t_cancelled_date = date('M d', strtotime($order['updated_at'] ?? $order['created_at']));

                                                $is_cancelled = ($t_status === 'cancelled');
                                                $is_shipped = in_array($t_status, ['shipped', 'delivered', 'completed']);
                                                $is_delivered = in_array($t_status, ['delivered', 'completed']);
                                            ?>

                                            <?php if ($is_cancelled): ?>
                                                <div class="progress mb-2" style="height: 8px;">
                                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 100%"></div>
                                                </div>
                                                <div class="d-flex justify-content-between small text-muted">
                                                    <span class="text-success fw-bold"><i class="fa-solid fa-check me-1"></i>Order Placed, <?= $t_placed_date; ?></span>
                                                    <span class="text-danger fw-bold"><i class="fa-solid fa-xmark me-1"></i>Cancelled, <?= $t_cancelled_date; ?></span>
                                                </div>
                                            <?php else: ?>
                                                <?php
                                                    $pct = 33;
                                                    if ($is_delivered) $pct = 100;
                                                    elseif ($is_shipped) $pct = 66;
                                                ?>
                                                <div class="progress mb-2" style="height: 8px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $pct; ?>%"></div>
                                                </div>
                                                <div class="d-flex justify-content-between small text-muted flex-wrap gap-2">
                                                    <span class="text-success fw-bold">
                                                        <i class="fa-solid fa-check me-1"></i>Order Placed, <?= $t_placed_date; ?>
                                                    </span>
                                                    <span class="<?= ($pct >= 66) ? 'text-success fw-bold' : ''; ?>">
                                                        <i class="fa-solid <?= ($pct >= 66) ? 'fa-check' : 'fa-clock'; ?> me-1"></i>Packaging &amp; Courier Dispatch
                                                    </span>
                                                    <span class="<?= ($pct >= 100) ? 'text-success fw-bold' : ''; ?>">
                                                        <i class="fa-solid <?= ($pct >= 100) ? 'fa-check' : 'fa-house-circle-check'; ?> me-1"></i><?= $is_delivered ? 'Delivered, ' . $t_delivered_date : 'Delivery'; ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Third-Party Courier & AWB Tracking -->
                                        <?php
                                        $courier_name    = !empty($order['courier_name']) ? trim($order['courier_name']) : '';
                                        $tracking_number = !empty($order['tracking_number']) ? trim($order['tracking_number']) : '';
                                        $tracking_url    = !empty($order['tracking_url']) ? trim($order['tracking_url']) : '';

                                        if (empty($tracking_url) && !empty($tracking_number)) {
                                            $c = strtolower($courier_name);
                                            if (strpos($c, 'xpress') !== false) {
                                                $tracking_url = 'https://www.xpressbees.com/shipment/tracking?awbNo=' . urlencode($tracking_number);
                                            } elseif (strpos($c, 'delhivery') !== false) {
                                                $tracking_url = 'https://www.delhivery.com/track/package/' . urlencode($tracking_number);
                                            } elseif (strpos($c, 'dtdc') !== false) {
                                                $tracking_url = 'https://www.dtdc.in/tracking.asp';
                                            } elseif (strpos($c, 'blue') !== false) {
                                                $tracking_url = 'https://www.bluedart.com/tracking';
                                            } elseif (strpos($c, 'ekart') !== false) {
                                                $tracking_url = 'https://ekartlogistics.com/shipmenttrack/' . urlencode($tracking_number);
                                            } elseif (strpos($c, 'shadowfax') !== false) {
                                                $tracking_url = 'https://tracker.shadowfax.in/#/track?awb=' . urlencode($tracking_number);
                                            } elseif (strpos($c, 'speed') !== false || strpos($c, 'post') !== false) {
                                                $tracking_url = 'https://www.indiapost.gov.in/_layouts/15/dpt.cept.tracking/trackconsignment.aspx';
                                            } else {
                                                $tracking_url = 'https://www.dtdc.in/tracking.asp';
                                            }
                                        }
                                        ?>

                                        <?php if (!empty($tracking_number) || in_array($order['order_status'], ['shipped', 'delivered', 'completed'])): ?>
                                            <div class="card border rounded-2 p-3 my-3 shadow-none" style="border-color: #b8daff !important; background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);">
                                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary text-white" style="width: 32px; height: 32px; font-size: 14px;">
                                                            <i class="fa-solid fa-truck-fast"></i>
                                                        </span>
                                                        <div>
                                                            <strong class="text-dark">Courier Partner:</strong>
                                                            <span class="text-primary fw-bold"><?= !empty($courier_name) ? html_escape($courier_name) : 'DTDC / Third-Party Courier'; ?></span>
                                                        </div>
                                                    </div>
                                                    <?php if (!empty($tracking_url)): ?>
                                                        <a href="<?= html_escape($tracking_url); ?>" target="_blank" class="btn btn-primary btn-sm px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1">
                                                            <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 11px;"></i> Track on <?= !empty($courier_name) ? html_escape($courier_name) : 'Courier'; ?> Website
                                                        </a>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="p-2 border rounded bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 my-2">
                                                    <div>
                                                        <span class="text-muted small fw-semibold text-uppercase">AWB Number:</span>
                                                        <span class="fw-bold font-monospace ms-1 text-dark"><?= !empty($tracking_number) ? html_escape($tracking_number) : 'Assigned upon courier pickup'; ?></span>
                                                    </div>
                                                    <?php if (!empty($tracking_number)): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="navigator.clipboard.writeText('<?= html_escape($tracking_number); ?>'); this.innerText='Copied!'; setTimeout(() => this.innerText='Copy AWB', 2000);">
                                                            <i class="fa-regular fa-copy me-1"></i> Copy AWB
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                                <small class="text-muted">
                                                    <i class="fa-solid fa-circle-info text-primary me-1"></i>
                                                    This package is dispatched via third-party courier. Use the AWB number above on the courier website link for real-time transit checkpoints.
                                                </small>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Items in this shipment -->
                                        <?php if (!empty($order['items'])): ?>
                                            <div class="mt-4 pt-3 border-top">
                                                <h6 class="fw-bold mb-3"><i class="fa-solid fa-boxes-packing me-1 text-primary"></i> Items in this Order:</h6>
                                                <div class="d-flex flex-column gap-2">
                                                    <?php foreach ($order['items'] as $it): 
                                                        $img = !empty($it['product_image']) ? base_url('assets/images/' . $it['product_image']) : base_url('assets/images/products/womens/women-1.jpg');
                                                        $it_st = !empty($it['item_status']) ? strtolower($it['item_status']) : $order['order_status'];
                                                        $it_c = !empty($it['courier_name']) ? $it['courier_name'] : $courier_name;
                                                        $it_trk = !empty($it['tracking_number']) ? $it['tracking_number'] : $tracking_number;

                                                        $badge_class = 'bg-warning text-dark';
                                                        if ($it_st === 'shipped') $badge_class = 'bg-primary text-white';
                                                        elseif (in_array($it_st, ['delivered', 'completed'])) $badge_class = 'bg-success text-white';
                                                        elseif ($it_st === 'cancelled') $badge_class = 'bg-danger text-white';
                                                    ?>
                                                        <div class="d-flex align-items-center gap-3 p-2 border rounded bg-white flex-wrap flex-sm-nowrap">
                                                            <img src="<?= $img; ?>" alt="<?= html_escape($it['product_title']); ?>" style="width: 50px; height: 50px; object-fit: contain;" class="border rounded bg-light" onerror="this.src='<?= base_url('assets/images/products/womens/women-1.jpg'); ?>'">
                                                            <div class="flex-grow-1">
                                                                <div class="fw-bold text-dark small"><?= html_escape($it['product_title']); ?></div>
                                                                <?php if (!empty($it['variant_title'])): ?>
                                                                    <div class="text-muted small" style="font-size: 11px;"><?= html_escape($it['variant_title']); ?></div>
                                                                <?php endif; ?>
                                                                <div class="text-secondary small">Qty: <?= (int) $it['quantity']; ?> &times; <?= $currency_symbol . number_format($it['price'], 2); ?></div>
                                                                <?php if (!empty($it_c) || !empty($it_trk)): ?>
                                                                    <div class="text-muted small" style="font-size: 11px;">
                                                                        <i class="fa-solid fa-truck-fast text-primary me-1"></i><?= html_escape($it_c ?: 'Courier'); ?><?= !empty($it_trk) ? ' (AWB: ' . html_escape($it_trk) . ')' : ''; ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="text-end">
                                                                <span class="badge <?= $badge_class; ?> mb-1"><?= ($it_st === 'shipped') ? 'Shipped' : ucfirst($it_st); ?></span>
                                                                <div class="fw-bold text-dark small">
                                                                    <?= $currency_symbol . number_format($it['total'], 2); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php
                                        // Address Type & Cleaning in track view
                                        $t_addr_type = !empty($order['address_type']) ? strtoupper(trim($order['address_type'])) : 'HOME';
                                        if ($t_addr_type === 'HOME' && !empty($order['shipping_address']) && preg_match('/\b(work|office|commercial)\b/i', $order['shipping_address'])) {
                                            $t_addr_type = 'WORK';
                                        }
                                        $t_is_work = ($t_addr_type === 'WORK');
                                        $t_addr_label = $t_is_work ? 'Work' : 'Home';
                                        $t_addr_icon = $t_is_work ? 'fa-solid fa-briefcase' : 'fa-solid fa-house';

                                        $t_raw = trim($order['shipping_address'] ?? '');
                                        $t_lines = array_filter(array_map('trim', preg_split('/[\r\n]+/', $t_raw)));
                                        $t_clean = [];
                                        foreach ($t_lines as $tl) {
                                            if (preg_match('/\((WORK|HOME)\)/i', $tl)) {
                                                $tl = trim(preg_replace('/\((WORK|HOME)\)/i', '', $tl));
                                            }
                                            if (!empty($order['customer_name']) && strcasecmp($tl, trim($order['customer_name'])) === 0) continue;
                                            if (preg_match('/^(phone|mobile):/i', $tl)) continue;
                                            if (!empty($tl)) $t_clean[] = $tl;
                                        }
                                        $t_display_addr = !empty($t_clean) ? implode(', ', $t_clean) : $t_raw;
                                        ?>
                                        <div class="mt-4 pt-3 border-top">
                                            <h6 class="fw-bold mb-2">Delivery Address:</h6>
                                            <div class="p-3 rounded-2 bg-white border">
                                                <div class="d-flex align-items-start gap-2 mb-1" style="font-size: 13px;">
                                                    <i class="<?= $t_addr_icon; ?> text-secondary mt-1 flex-shrink-0"></i>
                                                    <div>
                                                        <strong class="text-dark me-1"><?= $t_addr_label; ?></strong>
                                                        <span class="text-secondary"><?= html_escape($t_display_addr); ?></span>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-2" style="font-size: 13px;">
                                                    <i class="fa-regular fa-user text-secondary flex-shrink-0"></i>
                                                    <div>
                                                        <strong class="text-dark me-2"><?= html_escape($order['customer_name'] ?? ''); ?></strong>
                                                        <span class="text-secondary"><?= html_escape($order['customer_phone'] ?? ''); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning text-center mt-4">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i> No matching order was found with those details. Please double-check your Order Number and Email Address.
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
