        <?php
        $overall_status = strtolower($order['order_status']);
        $items = $order['items'] ?? [];
        if (empty($items)) {
            $items = [[
                'id'            => 0,
                'product_id'    => 0,
                'product_title' => 'Order #' . $order['order_number'],
                'product_image' => 'products/womens/women-1.jpg',
                'variant_title' => '',
                'price'         => $order['total_amount'],
                'quantity'      => 1,
                'total'         => $order['total_amount'],
                'item_status'   => $overall_status
            ]];
        }

        $seller_name = !empty($store_settings['site_name']) ? html_escape($store_settings['site_name']) : 'LifewayHygiene';

        // Function for auto-generating standard tracking URL if courier and AWB are provided
        if (!function_exists('resolve_courier_tracking_url')) {
            function resolve_courier_tracking_url($courier_name, $tracking_number, $custom_url = '') {
                if (!empty($custom_url)) return $custom_url;
                if (empty($tracking_number)) return '';
                $c = strtolower($courier_name ?? '');
                if (strpos($c, 'xpress') !== false) {
                    return 'https://www.xpressbees.com/shipment/tracking?awbNo=' . urlencode($tracking_number);
                } elseif (strpos($c, 'delhivery') !== false) {
                    return 'https://www.delhivery.com/track/package/' . urlencode($tracking_number);
                } elseif (strpos($c, 'dtdc') !== false) {
                    return 'https://www.dtdc.in/tracking.asp';
                } elseif (strpos($c, 'blue') !== false) {
                    return 'https://www.bluedart.com/tracking';
                } elseif (strpos($c, 'ekart') !== false) {
                    return 'https://ekartlogistics.com/shipmenttrack/' . urlencode($tracking_number);
                } elseif (strpos($c, 'shadowfax') !== false) {
                    return 'https://tracker.shadowfax.in/#/track?awb=' . urlencode($tracking_number);
                } elseif (strpos($c, 'speed') !== false || strpos($c, 'post') !== false) {
                    return 'https://www.indiapost.gov.in/_layouts/15/dpt.cept.tracking/trackconsignment.aspx';
                }
                return 'https://www.dtdc.in/tracking.asp';
            }
        }

        // Prepare item tracking map
        $selected_item_id = (int)(isset($this) && isset($this->input) ? $this->input->get('item_id') : (function_exists('get_instance') && isset(get_instance()->input) ? get_instance()->input->get('item_id') : ($_GET['item_id'] ?? 0)));
        $selected_item = null;

        $items_tracking_map = [];
        $is_any_delivered = false;

        foreach ($items as $it) {
            $it_id = (int)($it['id'] ?? 0);
            $it_st = !empty($it['item_status']) ? strtolower($it['item_status']) : $overall_status;
            if (in_array($it_st, ['delivered', 'completed'])) {
                $is_any_delivered = true;
            }

            $it_courier = !empty($it['courier_name']) ? trim($it['courier_name']) : (!empty($order['courier_name']) ? trim($order['courier_name']) : '');
            $it_awb = !empty($it['tracking_number']) ? trim($it['tracking_number']) : (!empty($order['tracking_number']) ? trim($order['tracking_number']) : '');
            $it_url = !empty($it['tracking_url']) ? trim($it['tracking_url']) : (!empty($order['tracking_url']) ? trim($order['tracking_url']) : '');
            $it_url = resolve_courier_tracking_url($it_courier, $it_awb, $it_url);

            $it_shipped_date = !empty($it['shipped_at']) ? date('M d', strtotime($it['shipped_at'])) : (!empty($order['shipped_at']) ? date('M d', strtotime($order['shipped_at'])) : '');
            $it_delivered_date = !empty($it['delivered_at']) ? date('M d', strtotime($it['delivered_at'])) : (!empty($order['delivered_at']) ? date('M d', strtotime($order['delivered_at'])) : date('M d', strtotime($order['updated_at'] ?? $order['created_at'])));

            $items_tracking_map[$it_id] = [
                'id'             => $it_id,
                'product_id'     => (int)($it['product_id'] ?? 0),
                'title'          => $it['product_title'] ?? '',
                'variant'        => $it['variant_title'] ?? '',
                'status'         => $it_st,
                'courier'        => $it_courier,
                'tracking_number'=> $it_awb,
                'tracking_url'   => $it_url,
                'shipped_date'   => $it_shipped_date,
                'delivered_date' => $it_delivered_date
            ];

            if ($selected_item_id > 0 && $it_id === $selected_item_id) {
                $selected_item = $items_tracking_map[$it_id];
            }
        }

        if (!$selected_item && !empty($items_tracking_map)) {
            $first_key = array_key_first($items_tracking_map);
            $selected_item = $items_tracking_map[$first_key];
            $selected_item_id = $selected_item['id'];
        }

        // Current item and Other items in this order
        $current_item = null;
        foreach ($items as $it) {
            if ((int)($it['id'] ?? 0) === (int)$selected_item_id) {
                $current_item = $it;
                break;
            }
        }
        if (!$current_item && !empty($items)) {
            $current_item = $items[0];
            $selected_item_id = (int)($current_item['id'] ?? 0);
        }

        $other_items = array_values(array_filter($items, function($it) use ($selected_item_id) {
            return (int)($it['id'] ?? 0) !== (int)$selected_item_id;
        }));

        if ($overall_status === 'delivered') {
            $is_any_delivered = true;
        }

        // Active tracked item attributes
        $active_st = $selected_item['status'];
        $active_courier = $selected_item['courier'];
        $active_awb = $selected_item['tracking_number'];
        $active_url = $selected_item['tracking_url'];
        $active_shipped_date = $selected_item['shipped_date'];
        $active_delivered_date = $selected_item['delivered_date'];

        $active_is_shipped_or_delivered = in_array($active_st, ['shipped', 'delivered', 'completed']);
        $active_is_delivered = in_array($active_st, ['delivered', 'completed']);
        $active_is_cancelled = ($active_st === 'cancelled');

        // Dates
        $date_placed     = date('M d', strtotime($order['created_at']));
        $time_placed     = date('h:i A', strtotime($order['created_at']));
        $date_cancelled  = date('M d', strtotime($order['updated_at'] ?? $order['created_at']));
        $return_end_date = date('M d', strtotime(($order['updated_at'] ?? $order['created_at']) . ' + 7 days'));

        // Pricing calculations
        $subtotal = (float) ($order['subtotal'] ?? $order['total_amount']);
        $discount = (float) ($order['discount_amount'] ?? 0);
        $shipping = (float) ($order['shipping_amount'] ?? 0);
        $total    = (float) $order['total_amount'];
        $listing_price = $subtotal + ($discount > 0 ? $discount : 150);

        $ci = function_exists('get_instance') ? get_instance() : (isset($this) ? $this : null);
        $csrf_name = ($ci && isset($ci->security)) ? $ci->security->get_csrf_token_name() : 'csrf_token';
        $csrf_hash = ($ci && isset($ci->security)) ? $ci->security->get_csrf_hash() : '';

        // Address & Address Type Resolution (Home vs Work)
        $addr_type = 'HOME';
        if (!empty($order['address_type'])) {
            $raw_type = strtoupper(trim($order['address_type']));
            if (in_array($raw_type, ['WORK', 'HOME'])) {
                $addr_type = $raw_type;
            }
        }

        // Fallback: Check if (WORK) or WORK exists in shipping_address
        if ($addr_type === 'HOME' && !empty($order['shipping_address'])) {
            if (preg_match('/\b(work|office|commercial)\b/i', $order['shipping_address'])) {
                $addr_type = 'WORK';
            }
        }

        // Static template mode: no database query needed for address type

        // Clean & Format Address
        $raw_address = trim($order['shipping_address'] ?? '');
        $lines = array_filter(array_map('trim', preg_split('/[\r\n]+/', $raw_address)));

        $clean_lines = [];
        $recipient_name = !empty($order['customer_name']) ? trim($order['customer_name']) : '';
        $recipient_phone = !empty($order['customer_phone']) ? trim($order['customer_phone']) : '';

        foreach ($lines as $line) {
            if (preg_match('/\((WORK|HOME)\)/i', $line, $m)) {
                $addr_type = strtoupper($m[1]);
                $line = trim(preg_replace('/\((WORK|HOME)\)/i', '', $line));
            }
            if (!empty($recipient_name) && strcasecmp($line, $recipient_name) === 0) {
                continue;
            }
            if (preg_match('/^(phone|mobile):\s*(.*)$/i', $line, $pm)) {
                if (empty($recipient_phone) && !empty($pm[2])) {
                    $recipient_phone = trim($pm[2]);
                }
                continue;
            }
            if (!empty($line)) {
                $clean_lines[] = $line;
            }
        }

        $display_address = !empty($clean_lines) ? implode(', ', $clean_lines) : $raw_address;

        $is_work = ($addr_type === 'WORK');
        $addr_type_label = $is_work ? 'Work' : 'Home';
        $addr_icon = $is_work ? 'fa-solid fa-briefcase' : 'fa-solid fa-house';
        ?>

        <!-- breadcrumb -->
        <div class="py-2 border-bottom" style="background-color: #f1f3f6;">
            <div class="fk-orders-container px-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 12px;">
                        <li class="breadcrumb-item"><a href="<?= site_url('home'); ?>" class="text-muted text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url('account/profile'); ?>" class="text-muted text-decoration-none">My Account</a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url('account/orders'); ?>" class="text-muted text-decoration-none">My Orders</a></li>
                        <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page"><?= html_escape($order['order_number']); ?></li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- /breadcrumb -->

        <!-- order-details-section -->
        <section class="py-3" style="background-color: #f1f3f6; min-height: 85vh;">
            <div class="fk-orders-container px-3">

                <!-- Flash Messages -->
                <?php 
                $flash_success = ($ci && isset($ci->session)) ? $ci->session->flashdata('success') : (isset($this->session) ? $this->session->flashdata('success') : null);
                $flash_error = ($ci && isset($ci->session)) ? $ci->session->flashdata('error') : (isset($this->session) ? $this->session->flashdata('error') : null);
                ?>
                <?php if ($flash_success): ?>
                    <div class="alert alert-success alert-dismissible fade show rounded-1 py-2 px-3 small mb-3" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i><?= $flash_success; ?>
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if ($flash_error): ?>
                    <div class="alert alert-danger alert-dismissible fade show rounded-1 py-2 px-3 small mb-3" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i><?= $flash_error; ?>
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row g-3">
                    <!-- Left Column: Individual Product Selector, Courier AWB Card, 3-Step Stepper, Items List (Col 8) -->
                    <div class="col-lg-8 col-md-8 col-12">

                        <!-- 1. Currently Tracked Product Card -->
                        <?php 
                        $cur_img = !empty($current_item['product_image']) ? base_url('assets/images/' . $current_item['product_image']) : base_url('assets/images/products/womens/women-1.jpg');
                        $cur_pid = (int) ($current_item['product_id'] ?? 0);
                        $cur_variant = '';
                        if (!empty($current_item['variant_title'])) {
                            $vt = trim($current_item['variant_title']);
                            if (strpos($vt, '/') !== false) {
                                $parts = explode('/', $vt);
                                $color_part = trim($parts[0]);
                                $size_part = trim($parts[1] ?? '');
                                $cur_variant = 'Color: ' . $color_part . (!empty($size_part) ? ' Size: ' . $size_part : '');
                            } else {
                                $cur_variant = $vt;
                            }
                        }

                        $cur_badge_class = 'bg-warning text-dark';
                        $cur_status_label = 'Order Placed';
                        if ($active_st === 'shipped') {
                            $cur_badge_class = 'bg-primary text-white';
                            $cur_status_label = 'Packaging & Courier Dispatch';
                        } elseif ($active_is_delivered) {
                            $cur_badge_class = 'bg-success text-white';
                            $cur_status_label = 'Delivered';
                        } elseif ($active_is_cancelled) {
                            $cur_badge_class = 'bg-danger text-white';
                            $cur_status_label = 'Cancelled';
                        }
                        ?>
                        <div class="card border rounded-1 bg-white shadow-none mb-3 p-3 p-md-4" style="border-color: #e0e0e0 !important;">
                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap flex-md-nowrap">
                                <div class="d-flex gap-3">
                                    <img src="<?= $cur_img; ?>" alt="<?= html_escape($current_item['product_title']); ?>" class="border rounded-1 flex-shrink-0" style="width: 75px; height: 75px; object-fit: contain; background-color: #fafafa;" onerror="this.src='<?= base_url('assets/images/products/womens/women-1.jpg'); ?>'">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1" style="font-size: 15px; line-height: 1.4;">
                                            <?= html_escape($current_item['product_title']); ?>
                                        </h6>
                                        <?php if (!empty($cur_variant)): ?>
                                            <div class="text-secondary small mb-1" style="font-size: 12px; color: #878787 !important;"><?= html_escape($cur_variant); ?></div>
                                        <?php endif; ?>
                                        <div class="text-secondary small mb-1" style="font-size: 12px; color: #878787 !important;">
                                            Seller: <?= $seller_name; ?>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="fw-bold text-dark" style="font-size: 16px;">
                                                <?= $currency_symbol . number_format($current_item['price'], 0); ?>
                                            </span>
                                            <?php if ((int)($current_item['quantity'] ?? 1) > 1): ?>
                                                <span class="text-muted small">(Qty: <?= (int)$current_item['quantity']; ?>)</span>
                                            <?php endif; ?>
                                            <span class="badge <?= $cur_badge_class; ?> px-2 py-0.5" style="font-size: 11px;">
                                                <?= $cur_status_label; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Courier Partner & AWB Live Tracking Card (Shown if shipped or delivered) -->
                        <div id="courier-live-card" class="card border rounded-1 p-3 mb-3 bg-white shadow-none <?= $active_is_shipped_or_delivered ? '' : 'd-none'; ?>" style="border-color: #b8daff !important; background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary text-white" style="width: 34px; height: 34px; font-size: 15px;">
                                        <i class="fa-solid fa-truck-fast"></i>
                                    </span>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0" style="font-size: 14px;">
                                            Courier Partner: <span class="text-primary" id="courier-partner-name"><?= !empty($active_courier) ? html_escape($active_courier) : 'DTDC / Third-Party Courier'; ?></span>
                                        </h6>
                                        <div class="text-muted small" style="font-size: 11px;">Dispatched via third-party courier service</div>
                                    </div>
                                </div>
                            </div>

                            <!-- AWB Box -->
                            <div class="p-3 border rounded-1 bg-white d-flex justify-content-between align-items-center flex-wrap gap-3" style="border-color: #e0e0e0 !important;">
                                <div>
                                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">AWB / Tracking Number</div>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <span class="fw-bold text-dark font-monospace fs-6" id="awb-code-text"><?= !empty($active_awb) ? html_escape($active_awb) : 'Assigned upon courier pickup'; ?></span>
                                        <button type="button" id="awb-copy-btn" class="btn btn-sm btn-outline-secondary py-0 px-2 rounded-1 <?= !empty($active_awb) ? '' : 'd-none'; ?>" onclick="copyAwbCode(document.getElementById('awb-code-text').innerText)" title="Copy AWB Number" style="font-size: 11px;">
                                            <i class="fa-regular fa-copy me-1"></i> Copy AWB
                                        </button>
                                        <span id="awb-copy-alert" class="text-success small fw-semibold d-none" style="font-size: 11px;">Copied!</span>
                                    </div>
                                </div>

                                <div id="courier-portal-container" class="<?= !empty($active_url) ? '' : 'd-none'; ?>">
                                    <a href="<?= html_escape($active_url ?: 'https://www.dtdc.in/tracking.asp'); ?>" target="_blank" id="courier-track-link-btn" class="btn btn-primary btn-sm px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2 rounded-1 <?= !empty($active_url) ? '' : 'd-none'; ?>" style="background-color: #2874f0; border-color: #2874f0; font-size: 12px;">
                                        <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 11px;"></i> Track on <span id="courier-btn-name"><?= !empty($active_courier) ? html_escape($active_courier) : 'Courier'; ?></span> Website
                                    </a>
                                </div>
                            </div>

                            <div class="text-secondary small mt-2" style="font-size: 12px; line-height: 1.5;">
                                <i class="fa-solid fa-circle-info text-primary me-1"></i>
                                Products are dispatched via trusted courier partners. You can copy your AWB number and visit the courier tracking portal to see live transit updates.
                            </div>
                        </div>

                        <!-- Preparation Notice Card (Shown when pending) -->
                        <div id="courier-pending-card" class="card border rounded-1 p-3 mb-3 bg-white shadow-none <?= ($active_st === 'pending') ? '' : 'd-none'; ?>" style="border-color: #e0e0e0 !important;">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center bg-light text-primary flex-shrink-0" style="width: 40px; height: 40px; font-size: 18px;">
                                    <i class="fa-solid fa-box-open"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">
                                        Order Placed - Packaging & Courier Dispatch Pending
                                    </h6>
                                    <div class="text-secondary small" style="font-size: 12px; line-height: 1.4;">
                                        Your item has been placed. The store owner will pack your product and hand it over to our courier partner (such as DTDC or Xpressbees). Once dispatched, your <strong>AWB Tracking Number</strong> and official tracking portal link will appear here.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Shipment Progress Stepper Card (Exactly 3 Statuses: Order Placed -> Packaging & Courier Dispatch -> Delivery) -->
                        <div class="card border rounded-1 bg-white shadow-none mb-3" style="border-color: #e0e0e0 !important;">
                            <div class="p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2" style="background-color: #fafafa;">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-route text-primary fs-5"></i>
                                    <span class="fw-bold text-dark" style="font-size: 14px;">Order Tracking Status</span>
                                    <span class="badge bg-light text-secondary border fw-normal" style="font-size: 11px;">
                                        <?= count($items); ?> <?= count($items) === 1 ? 'item' : 'items'; ?> in this order
                                    </span>
                                </div>
                                <div class="text-secondary small">
                                    Store: <strong class="text-dark"><?= $seller_name; ?></strong>
                                </div>
                            </div>

                            <!-- Stepper Container -->
                            <div class="p-4" id="order-stepper-container">
                                <?php if ($active_is_cancelled): ?>
                                    <!-- Cancelled Flow: Order Placed -> Cancelled -->
                                    <div class="d-flex align-items-start gap-3">
                                        <span class="rounded-circle d-inline-flex align-items-center justify-content-center text-white flex-shrink-0 mt-1" style="width: 22px; height: 22px; background-color: #26a541; font-size: 11px;">
                                            <i class="fa-solid fa-check"></i>
                                        </span>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size: 14px;">Order Placed, <?= $date_placed; ?></div>
                                            <div class="text-muted small" style="font-size: 12px;">Order received at <?= $time_placed; ?></div>
                                        </div>
                                    </div>

                                    <div style="width: 2px; height: 28px; background-color: #e53935; margin-left: 10px;"></div>

                                    <div class="d-flex align-items-start gap-3">
                                        <span class="rounded-circle d-inline-flex align-items-center justify-content-center text-white flex-shrink-0 mt-1" style="width: 22px; height: 22px; background-color: #e53935; font-size: 11px;">
                                            <i class="fa-solid fa-xmark"></i>
                                        </span>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size: 14px;">Cancelled, <?= $date_cancelled; ?></div>
                                            <div class="text-muted small" style="font-size: 12px;">This item / order was cancelled</div>
                                        </div>
                                    </div>
                                <?php else: 
                                    $step2_done = $active_is_shipped_or_delivered;
                                    $step3_done = $active_is_delivered;
                                ?>
                                    <!-- Status 1: Order Placed -->
                                    <div class="d-flex align-items-start gap-3">
                                        <span class="rounded-circle d-inline-flex align-items-center justify-content-center text-white flex-shrink-0 mt-1" style="width: 22px; height: 22px; background-color: #26a541; font-size: 11px;">
                                            <i class="fa-solid fa-check"></i>
                                        </span>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size: 14px;">Order Placed, <?= $date_placed; ?></div>
                                            <div class="text-muted small" style="font-size: 12px;">Order received at <?= $time_placed; ?></div>
                                        </div>
                                    </div>

                                    <!-- Connector 1 -->
                                    <div id="connector-1" style="width: 2px; height: 28px; background-color: <?= $step2_done ? '#26a541' : '#d1d5db'; ?>; margin-left: 10px;"></div>

                                    <!-- Status 2: Packaging & Courier Dispatch -->
                                    <div class="d-flex align-items-start gap-3">
                                        <span id="step-2-circle" class="rounded-circle d-inline-flex align-items-center justify-content-center text-white flex-shrink-0 mt-1" style="width: 22px; height: 22px; background-color: <?= $step2_done ? '#26a541' : '#9ca3af'; ?>; font-size: 11px;">
                                            <i id="step-2-icon" class="fa-solid <?= $step2_done ? 'fa-check' : 'fa-clock'; ?>"></i>
                                        </span>
                                        <div>
                                            <div class="fw-bold text-dark" id="step-2-title" style="font-size: 14px;">
                                                Packaging & Courier Dispatch
                                            </div>
                                            <div class="text-muted small" id="step-2-desc" style="font-size: 12px;">
                                                <?php if ($step2_done): ?>
                                                    Dispatched via <?= html_escape($active_courier ?: 'Courier'); ?><?= !empty($active_awb) ? ' (AWB: ' . html_escape($active_awb) . ')' : ''; ?><?= !empty($active_shipped_date) ? ' on ' . $active_shipped_date : ''; ?>
                                                <?php else: ?>
                                                    Preparing for packaging & courier dispatch
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Connector 2 -->
                                    <div id="connector-2" style="width: 2px; height: 28px; background-color: <?= $step3_done ? '#26a541' : '#d1d5db'; ?>; margin-left: 10px;"></div>

                                    <!-- Status 3: Delivery -->
                                    <div class="d-flex align-items-start gap-3">
                                        <span id="step-3-circle" class="rounded-circle d-inline-flex align-items-center justify-content-center text-white flex-shrink-0 mt-1" style="width: 22px; height: 22px; background-color: <?= $step3_done ? '#26a541' : '#9ca3af'; ?>; font-size: 11px;">
                                            <i id="step-3-icon" class="fa-solid <?= $step3_done ? 'fa-check' : 'fa-house-circle-check'; ?>"></i>
                                        </span>
                                        <div>
                                            <div class="fw-bold text-dark" id="step-3-title" style="font-size: 14px;">
                                                <?= $step3_done ? 'Delivered, ' . $active_delivered_date : 'Delivery'; ?>
                                            </div>
                                            <div class="text-muted small" id="step-3-desc" style="font-size: 12px;">
                                                <?= $step3_done ? 'Your package has been delivered by courier partner' : 'Awaiting delivery by courier partner'; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- See All Updates Link -->
                                <div class="mt-3 ps-1">
                                    <a href="javascript:void(0);" onclick="toggleTrackingUpdates()" class="text-primary fw-semibold small text-decoration-none d-inline-flex align-items-center gap-1" style="color: #2874f0 !important; font-size: 13px;">
                                        See All Updates <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
                                    </a>
                                </div>

                                <!-- Expandable Detailed Tracking Updates -->
                                <div id="tracking-updates-box" class="mt-3 p-3 bg-light border rounded-1 d-none" style="font-size: 13px;">
                                    <div id="tracking-updates-content">
                                        <?php if ($active_is_delivered): ?>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span><strong>Package Delivered</strong> by courier associate</span>
                                                <span class="text-muted"><?= $active_delivered_date; ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($active_is_shipped_or_delivered): ?>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span><strong>Handed over to <?= html_escape($active_courier ?: 'Courier'); ?></strong> (AWB: <?= html_escape($active_awb ?: 'Assigned'); ?>)</span>
                                                <span class="text-muted"><?= $active_shipped_date; ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="d-flex justify-content-between">
                                            <span><strong>Order Placed</strong> by customer</span>
                                            <span class="text-muted"><?= $date_placed; ?>, <?= $time_placed; ?></span>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($active_is_delivered): ?>
                                    <div class="text-muted small mt-3" style="font-size: 12px; color: #878787 !important;">
                                        Return policy ended on <?= $return_end_date; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <hr class="m-0" style="border-color: #f0f0f0;">

                            <!-- Card Footer: Chat with us -->
                            <div class="py-3 text-center">
                                <a href="javascript:void(0);" class="text-decoration-none text-dark fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 14px;" data-bs-toggle="modal" data-bs-target="#chatSupportModal">
                                    <i class="fa-regular fa-comment-dots text-primary fs-5" style="color: #2874f0 !important;"></i>
                                    <span>Chat with us</span>
                                </a>
                            </div>
                        </div>

                        <!-- 4. Rate your experience Card (Shown if current item is delivered, Matching quick_rating.png) -->
                        <?php if ($active_is_delivered): 
                            $cur_review = $user_reviews[$cur_pid] ?? null;
                            $cur_rating = !empty($cur_review['rating']) ? (int)$cur_review['rating'] : 0;
                            $rating_labels = [
                                1 => 'Very Bad',
                                2 => 'Bad',
                                3 => 'Good',
                                4 => 'Very Good',
                                5 => 'Excellent'
                            ];
                        ?>
                            <div class="card border rounded-1 p-4 mb-3 bg-white shadow-none" style="border-color: #e0e0e0 !important;">
                                <h6 class="fw-bold text-dark mb-3" style="font-size: 15px;">Rate your experience</h6>

                                <div class="p-3 rounded-2" style="background-color: #fafafa; border: 1px solid #f0f0f0;">
                                    <div class="d-flex align-items-center gap-2 mb-3 text-secondary" style="font-size: 13px;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                            <polyline points="9 11 12 14 22 4"></polyline>
                                        </svg>
                                        <span class="text-dark fw-medium">Write a product review</span>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <!-- Left: Star Rating Only with Tooltip -->
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="fk-order-stars d-inline-flex align-items-center gap-1" id="quick-stars-wrapper" onmouseleave="resetQuickStars(<?= $cur_rating; ?>)">
                                                <?php for ($s = 1; $s <= 5; $s++): ?>
                                                    <i class="<?= ($cur_rating >= $s) ? 'fa-solid' : 'fa-regular'; ?> fa-star quick-star-item" 
                                                       id="quick-star-<?= $s; ?>"
                                                       data-star="<?= $s; ?>"
                                                       data-bs-toggle="tooltip"
                                                       data-bs-placement="top"
                                                       title="<?= $rating_labels[$s]; ?>"
                                                       style="cursor: pointer; font-size: 20px; color: <?= ($cur_rating >= $s) ? '#26a541' : '#b0b8c1'; ?>; transition: color 0.15s, transform 0.15s;"
                                                       onmouseenter="hoverQuickStars(<?= $s; ?>)"
                                                       onclick="submitQuickRating(<?= $s; ?>, <?= $cur_pid; ?>, '<?= html_escape($order['order_number']); ?>')"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <span id="quick-rate-saved-alert" class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 ms-2 d-none" style="font-size: 11px;">Saved!</span>
                                        </div>

                                        <!-- Right: Write Review Button -->
                                        <a href="<?= site_url('account/rate_review/' . $order['order_number'] . '/' . $cur_pid . ($cur_rating > 0 ? '?rating=' . $cur_rating : '')); ?>" 
                                           id="quick-write-review-btn" 
                                           class="btn btn-outline-primary btn-sm rounded-2 fw-semibold px-3 py-1 d-inline-flex align-items-center gap-2" 
                                           style="color: #2874f0; border-color: #2874f0; font-size: 13px; background-color: #fff; height: 35px;">
                                            <i class="fa-solid fa-pen" style="font-size: 11px;"></i> Write review
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- 5. Items in this Order Card (Shows other products with same order id, excluding the current product, COMES LAST) -->
                        <?php if (!empty($other_items)): ?>
                        <div class="card border rounded-1 bg-white shadow-none mb-3 p-3 p-md-4" style="border-color: #e0e0e0 !important;">
                            <h6 class="fw-bold text-dark mb-3" style="font-size: 15px;">
                                Items in this Order (<?= count($other_items); ?>)
                            </h6>

                            <div class="d-flex flex-column gap-2">
                                <?php foreach ($other_items as $idx => $it): 
                                    $it_id = (int)$it['id'];
                                    $it_info = $items_tracking_map[$it_id] ?? [];
                                    $it_st = $it_info['status'] ?? 'pending';
                                    $it_img = !empty($it['product_image']) ? base_url('assets/images/' . $it['product_image']) : base_url('assets/images/products/womens/women-1.jpg');
                                    $it_pid = (int) ($it['product_id'] ?? 0);

                                    $it_variant = '';
                                    if (!empty($it['variant_title'])) {
                                        $vt = trim($it['variant_title']);
                                        if (strpos($vt, '/') !== false) {
                                            $parts = explode('/', $vt);
                                            $color_part = trim($parts[0]);
                                            $size_part = trim($parts[1] ?? '');
                                            $it_variant = 'Color: ' . $color_part . (!empty($size_part) ? ' Size: ' . $size_part : '');
                                        } else {
                                            $it_variant = $vt;
                                        }
                                    }

                                    $dot_color = '#f39c12';
                                    $it_status_label = 'Order Placed';
                                    if ($it_st === 'shipped') {
                                        $dot_color = '#2874f0';
                                        $it_status_label = 'Packaging & Courier Dispatch';
                                    } elseif (in_array($it_st, ['delivered', 'completed'])) {
                                        $dot_color = '#26a541';
                                        $it_status_label = 'Delivered';
                                    } elseif ($it_st === 'cancelled') {
                                        $dot_color = '#e53935';
                                        $it_status_label = 'Cancelled';
                                    }

                                    $oit_track_url = site_url('account/order/' . $order['order_number'] . '?item_id=' . $it_id);
                                ?>
                                    <div class="other-order-item-card p-3 rounded-2 border d-flex align-items-center justify-content-between gap-3 bg-white"
                                         onclick="window.location.href='<?= $oit_track_url; ?>'"
                                         style="border-color: #eceff1 !important; cursor: pointer; transition: all 0.2s ease;">
                                        
                                        <!-- Left Side: Thumbnail + Product Info -->
                                        <div class="d-flex align-items-center gap-3" style="min-width: 0; flex: 1;">
                                            <img src="<?= $it_img; ?>" 
                                                 alt="<?= html_escape($it['product_title']); ?>" 
                                                 class="border rounded-2 flex-shrink-0" 
                                                 style="width: 58px; height: 58px; object-fit: contain; background-color: #fafafa; border-color: #eceff1 !important;" 
                                                 onerror="this.src='<?= base_url('assets/images/products/womens/women-1.jpg'); ?>'">
                                            
                                            <div style="min-width: 0; flex: 1;">
                                                <div class="text-dark fw-semibold text-truncate other-item-title mb-1" 
                                                     style="font-size: 13px; line-height: 1.3;" 
                                                     title="<?= html_escape($it['product_title']); ?>">
                                                    <?= html_escape($it['product_title']); ?>
                                                </div>

                                                <?php if (!empty($it_variant)): ?>
                                                    <div class="text-muted small text-truncate mb-1" style="font-size: 12px; color: #878787 !important;">
                                                        <?= html_escape($it_variant); ?>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="fw-bold text-dark" style="font-size: 14px;">
                                                        <?= $currency_symbol . number_format($it['price'], 0); ?>
                                                    </span>
                                                    <?php if ((int)($it['quantity'] ?? 1) > 1): ?>
                                                        <span class="text-muted small" style="font-size: 12px;">(Qty: <?= (int)$it['quantity']; ?>)</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Right Side: Status with colored dot + Arrow -->
                                        <div class="d-flex align-items-center gap-3 flex-shrink-0 text-end">
                                            <div>
                                                <div class="d-inline-flex align-items-center gap-2">
                                                    <span class="rounded-circle d-inline-block flex-shrink-0" style="width: 8px; height: 8px; background-color: <?= $dot_color; ?>;"></span>
                                                    <span class="fw-semibold text-dark" style="font-size: 13px;"><?= $it_status_label; ?></span>
                                                </div>
                                                <?php if (!empty($it_info['courier']) || !empty($it_info['tracking_number'])): ?>
                                                    <div class="text-secondary small mt-0.5" style="font-size: 11px;">
                                                        <i class="fa-solid fa-truck-fast text-danger me-1"></i><?= html_escape($it_info['courier'] ?: 'Courier'); ?><?= !empty($it_info['tracking_number']) ? ' (AWB: ' . html_escape($it_info['tracking_number']) . ')' : ''; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="text-muted ps-1">
                                                <i class="fa-solid fa-chevron-right text-muted other-item-chevron" style="font-size: 12px; transition: transform 0.15s ease;"></i>
                                            </div>
                                        </div>

                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Order ID Footer Bar with Copy Button -->
                        <div class="d-flex align-items-center gap-2 text-muted small ps-1 pt-1">
                            <span>Order #<?= html_escape($order['order_number']); ?></span>
                            <button type="button" class="btn btn-sm btn-link p-0 text-muted text-decoration-none" onclick="copyOrderNumber('<?= html_escape($order['order_number']); ?>')" title="Copy Order ID">
                                <i class="fa-regular fa-copy"></i>
                            </button>
                            <span id="copy-success-msg" class="text-success small fw-semibold d-none">Copied!</span>
                        </div>
                    </div>

                    <!-- Right Column: Delivery Details & Price Details (Col 4) -->
                    <div class="col-lg-4 col-md-4 col-12">

                        <!-- Delivery details Card (Matching new_order_details.PNG) -->
                        <div class="card border rounded-1 p-3 mb-3 bg-white shadow-none" style="border-color: #e0e0e0 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold text-dark mb-0" style="font-size: 14px;">Delivery details</h6>
                                <button class="btn btn-sm p-0 d-inline-flex align-items-center justify-content-center border-0 rounded bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#deliveryDetailsBody" aria-expanded="true" style="width: 26px; height: 26px;">
                                    <i class="fa-solid fa-chevron-up text-muted" style="font-size: 11px;"></i>
                                </button>
                            </div>

                            <div class="collapse show" id="deliveryDetailsBody">
                                <div class="p-3 rounded-2" style="background-color: #f8f9fa; border: 1px solid #f0f0f0;">
                                    <!-- Address Line with Icon and Type (Home/Work) -->
                                    <div class="d-flex align-items-start gap-2 mb-2" style="font-size: 13px; line-height: 1.45;">
                                        <i class="<?= $addr_icon; ?> text-secondary mt-1 flex-shrink-0" style="font-size: 13px;"></i>
                                        <div>
                                            <strong class="text-dark me-1"><?= $addr_type_label; ?></strong>
                                            <span class="text-secondary"><?= html_escape($display_address); ?></span>
                                        </div>
                                    </div>

                                    <!-- Recipient Name and Phone -->
                                    <div class="d-flex align-items-center gap-2" style="font-size: 13px;">
                                        <i class="fa-regular fa-user text-secondary flex-shrink-0" style="font-size: 13px;"></i>
                                        <div>
                                            <strong class="text-dark me-2"><?= html_escape($recipient_name); ?></strong>
                                            <span class="text-secondary"><?= html_escape($recipient_phone); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Price details Card (Collapsible) -->
                        <div class="card border rounded-1 p-3 mb-3 bg-white shadow-none" style="border-color: #e0e0e0 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold text-dark mb-0" style="font-size: 14px;">Price details</h6>
                                <button class="btn btn-sm p-0 d-inline-flex align-items-center justify-content-center border-0 rounded bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#priceDetailsBody" aria-expanded="true" style="width: 26px; height: 26px;">
                                    <i class="fa-solid fa-chevron-up text-muted" style="font-size: 11px;"></i>
                                </button>
                            </div>

                            <div class="collapse show" id="priceDetailsBody">
                                <!-- Listing price -->
                                <div class="d-flex justify-content-between text-secondary mb-2" style="font-size: 13px;">
                                    <span>Listing price</span>
                                    <span><?= $currency_symbol . number_format($listing_price, 0); ?></span>
                                </div>

                                <!-- Selling price -->
                                <div class="d-flex justify-content-between text-secondary mb-2" style="font-size: 13px;">
                                    <span>Selling price <i class="fa-regular fa-circle-question text-muted" style="font-size: 11px;"></i></span>
                                    <span><?= $currency_symbol . number_format($subtotal, 0); ?></span>
                                </div>

                                <!-- Other discount -->
                                <?php if ($discount > 0): ?>
                                    <div class="d-flex justify-content-between text-success mb-2" style="font-size: 13px;">
                                        <span>Other discount <i class="fa-solid fa-chevron-down" style="font-size: 10px;"></i></span>
                                        <span>-<?= $currency_symbol . number_format($discount, 0); ?></span>
                                    </div>
                                <?php endif; ?>

                                <!-- Dotted Divider -->
                                <div class="my-2" style="border-top: 1px dashed #e0e0e0;"></div>

                                <!-- Total amount -->
                                <div class="d-flex justify-content-between fw-bold text-dark mb-3" style="font-size: 14px;">
                                    <span>Total amount</span>
                                    <span><?= $currency_symbol . number_format($total, 0); ?></span>
                                </div>

                                <!-- Paid By -->
                                <div class="d-flex justify-content-between align-items-center text-secondary <?= $is_any_delivered ? 'mb-3' : 'mb-0'; ?>" style="font-size: 13px;">
                                    <span>Paid By</span>
                                    <span class="badge bg-light text-dark border px-2 py-1 fw-bold">
                                        <i class="fa-solid fa-wallet text-primary me-1"></i> <?= strtoupper($order['payment_method'] ?? 'UPI'); ?>
                                    </span>
                                </div>

                                <?php // Download Invoice Button (Shown after order items are delivered) ?>
                                <?php if ($is_any_delivered): ?>
                                    <a href="<?= site_url('account/invoice/' . $order['order_number']); ?>" target="_blank" class="btn btn-outline-secondary w-100 py-2 d-flex align-items-center justify-content-center gap-2 fw-semibold rounded-1 mt-3" style="border-color: #d1d5db; color: #212121; font-size: 14px;">
                                        <i class="fa-solid fa-download"></i>
                                        <span>Download Invoice</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Offers earned Card (Collapsible) -->
                        <div class="card border rounded-1 p-3 bg-white shadow-none" style="border-color: #e0e0e0 !important;">
                            <div class="d-flex justify-content-between align-items-center" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#offersList">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-award text-success fs-5"></i>
                                    <span class="fw-semibold text-dark" style="font-size: 13px;">Offers earned</span>
                                </div>
                                <i class="fa-solid fa-chevron-down text-muted" style="font-size: 12px;"></i>
                            </div>
                            <div class="collapse mt-2 pt-2 border-top" id="offersList">
                                <div class="small text-muted"><i class="fa-solid fa-check text-success me-1"></i> Special platform discount applied</div>
                                <div class="small text-muted mt-1"><i class="fa-solid fa-check text-success me-1"></i> Free delivery on this order</div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>
        <!-- /order-details-section -->

        <!-- Chat Support Modal -->
        <div class="modal fade" id="chatSupportModal" tabindex="-1" aria-labelledby="chatSupportModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-1 border-0 shadow">
                    <div class="modal-header border-bottom py-3">
                        <h6 class="modal-title fw-bold text-dark" id="chatSupportModalLabel"><i class="fa-regular fa-comment-dots text-primary me-2"></i>Customer Support</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <div class="mb-3">
                            <i class="fa-solid fa-headset text-primary" style="font-size: 44px;"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Need help with Order #<?= html_escape($order['order_number']); ?>?</h6>
                        <p class="text-secondary small mb-3">Our dedicated customer assistance team is here to assist with tracking, returns, and refunds.</p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="mailto:support@ecommerce.com?subject=Help with Order <?= html_escape($order['order_number']); ?>" class="btn btn-outline-primary btn-sm rounded-1 px-3">
                                <i class="fa-regular fa-envelope me-1"></i> Email Us
                            </a>
                            <a href="https://wa.me/?text=Hi%2C%20I%20need%20help%20with%20Order%20<?= html_escape($order['order_number']); ?>" target="_blank" class="btn btn-success btn-sm rounded-1 px-3">
                                <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
        .fk-orders-container {
            max-width: 1680px;
            margin: 0 auto;
        }
        @media (min-width: 992px) {
            .fk-orders-container {
                min-width: 978px;
            }
        }
        @media (max-width: 991px) {
            .fk-orders-container {
                min-width: 100% !important;
                max-width: 100% !important;
            }
        }
        .order-star-btn:hover,
        .quick-star-item:hover {
            transform: scale(1.15);
        }
        .other-order-item-card:hover {
            border-color: #2874f0 !important;
            background-color: #fbfdff !important;
            box-shadow: 0 2px 8px rgba(40, 116, 240, 0.08);
        }
        .other-order-item-card:hover .other-item-title {
            color: #2874f0 !important;
        }
        .other-order-item-card:hover .other-item-chevron {
            transform: translateX(3px);
            color: #2874f0 !important;
        }
        </style>

        <script>
        var orderItemsData = <?= json_encode($items_tracking_map); ?>;
        var orderDatePlaced = '<?= $date_placed; ?>';
        var orderTimePlaced = '<?= $time_placed; ?>';

        function switchTrackedItem(id) {
            if (!orderItemsData || !orderItemsData[id]) return;
            var item = orderItemsData[id];

            // 1. Update selector chips
            document.querySelectorAll('.pit-track-chip').forEach(function(chip) {
                chip.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
                chip.classList.add('bg-light');
                chip.style.borderWidth = '1px';
            });
            var activeChip = document.getElementById('pit-chip-' + id);
            if (activeChip) {
                activeChip.classList.remove('bg-light');
                activeChip.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
                activeChip.style.borderWidth = '2px';
            }

            document.querySelectorAll('[id^="chip-active-indicator-"]').forEach(function(ind) {
                ind.classList.add('d-none');
            });
            var activeInd = document.getElementById('chip-active-indicator-' + id);
            if (activeInd) {
                activeInd.classList.remove('d-none');
            }

            // 2. Update Courier Card
            var isShippedOrDelivered = (item.status === 'shipped' || item.status === 'delivered' || item.status === 'completed');
            var isDelivered = (item.status === 'delivered' || item.status === 'completed');
            var isCancelled = (item.status === 'cancelled');

            var liveCard = document.getElementById('courier-live-card');
            var pendingCard = document.getElementById('courier-pending-card');

            if (isCancelled) {
                if (liveCard) liveCard.classList.add('d-none');
                if (pendingCard) pendingCard.classList.add('d-none');
            } else if (isShippedOrDelivered) {
                if (liveCard) liveCard.classList.remove('d-none');
                if (pendingCard) pendingCard.classList.add('d-none');

                var cName = item.courier || 'DTDC / Third-Party Courier';
                var partnerNameEl = document.getElementById('courier-partner-name');
                if (partnerNameEl) partnerNameEl.textContent = cName;

                var btnNameEl = document.getElementById('courier-btn-name');
                if (btnNameEl) btnNameEl.textContent = item.courier || 'Courier';

                var portalNameEl = document.getElementById('courier-portal-name');
                if (portalNameEl) portalNameEl.textContent = item.courier || 'Courier';

                var awbEl = document.getElementById('awb-code-text');
                if (awbEl) awbEl.textContent = item.tracking_number || 'Assigned upon courier pickup';

                var copyBtn = document.getElementById('awb-copy-btn');
                if (copyBtn) {
                    if (item.tracking_number) copyBtn.classList.remove('d-none');
                    else copyBtn.classList.add('d-none');
                }

                var trackBtn = document.getElementById('courier-track-link-btn');
                var portalContainer = document.getElementById('courier-portal-container');
                var portalLink = document.getElementById('courier-portal-link');

                if (item.tracking_url) {
                    if (trackBtn) {
                        trackBtn.href = item.tracking_url;
                        trackBtn.classList.remove('d-none');
                    }
                    if (portalContainer) portalContainer.classList.remove('d-none');
                    if (portalLink) portalLink.href = item.tracking_url;
                } else {
                    if (trackBtn) trackBtn.classList.add('d-none');
                    if (portalContainer) portalContainer.classList.add('d-none');
                }
            } else {
                if (liveCard) liveCard.classList.add('d-none');
                if (pendingCard) pendingCard.classList.remove('d-none');
            }

            // 3. Update Stepper (Step 2 and Step 3)
            var step2Done = isShippedOrDelivered;
            var step3Done = isDelivered;

            var conn1 = document.getElementById('connector-1');
            if (conn1) conn1.style.backgroundColor = step2Done ? '#26a541' : '#d1d5db';

            var s2Circle = document.getElementById('step-2-circle');
            if (s2Circle) s2Circle.style.backgroundColor = step2Done ? '#26a541' : '#9ca3af';

            var s2Icon = document.getElementById('step-2-icon');
            if (s2Icon) s2Icon.className = 'fa-solid ' + (step2Done ? 'fa-check' : 'fa-clock');

            var s2Desc = document.getElementById('step-2-desc');
            if (s2Desc) {
                if (step2Done) {
                    var txt = 'Dispatched via ' + (item.courier || 'Courier');
                    if (item.tracking_number) txt += ' (AWB: ' + item.tracking_number + ')';
                    if (item.shipped_date) txt += ' on ' + item.shipped_date;
                    s2Desc.textContent = txt;
                } else {
                    s2Desc.textContent = 'Preparing for packaging & courier dispatch';
                }
            }

            var conn2 = document.getElementById('connector-2');
            if (conn2) conn2.style.backgroundColor = step3Done ? '#26a541' : '#d1d5db';

            var s3Circle = document.getElementById('step-3-circle');
            if (s3Circle) s3Circle.style.backgroundColor = step3Done ? '#26a541' : '#9ca3af';

            var s3Icon = document.getElementById('step-3-icon');
            if (s3Icon) s3Icon.className = 'fa-solid ' + (step3Done ? 'fa-check' : 'fa-house-circle-check');

            var s3Title = document.getElementById('step-3-title');
            if (s3Title) s3Title.textContent = step3Done ? ('Delivered, ' + item.delivered_date) : 'Delivery';

            var s3Desc = document.getElementById('step-3-desc');
            if (s3Desc) s3Desc.textContent = step3Done ? 'Your package has been delivered by courier partner' : 'Awaiting delivery by courier partner';

            // 4. Update Detailed Tracking Updates box
            var upBox = document.getElementById('tracking-updates-content');
            if (upBox) {
                var html = '';
                if (isDelivered) {
                    html += '<div class="d-flex justify-content-between mb-2"><span><strong>Package Delivered</strong> by courier associate</span><span class="text-muted">' + item.delivered_date + '</span></div>';
                }
                if (step2Done) {
                    html += '<div class="d-flex justify-content-between mb-2"><span><strong>Handed over to ' + (item.courier || 'Courier') + '</strong> (AWB: ' + (item.tracking_number || 'Assigned') + ')</span><span class="text-muted">' + (item.shipped_date || '') + '</span></div>';
                }
                html += '<div class="d-flex justify-content-between"><span><strong>Order Placed</strong> by customer</span><span class="text-muted">' + orderDatePlaced + ', ' + orderTimePlaced + '</span></div>';
                upBox.innerHTML = html;
            }

            // 5. Update URL state without reload
            if (window.history && window.history.replaceState) {
                var newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?item_id=' + id;
                window.history.replaceState({ path: newUrl }, '', newUrl);
            }
        }
        function toggleTrackingUpdates() {
            var box = document.getElementById('tracking-updates-box');
            if (box) {
                box.classList.toggle('d-none');
            }
        }

        function copyOrderNumber(num) {
            navigator.clipboard.writeText(num).then(function() {
                var msg = document.getElementById('copy-success-msg');
                if (msg) {
                    msg.classList.remove('d-none');
                    setTimeout(function() {
                        msg.classList.add('d-none');
                    }, 2000);
                }
            });
        }

        function copyAwbCode(awb) {
            navigator.clipboard.writeText(awb).then(function() {
                var el = document.getElementById('awb-copy-alert');
                if (el) {
                    el.classList.remove('d-none');
                    setTimeout(function() {
                        el.classList.add('d-none');
                    }, 2000);
                }
            });
        }

        var currentQuickRating = <?= !empty($cur_rating) ? (int)$cur_rating : 0; ?>;
        var ratingLabels = {
            1: 'Very Bad',
            2: 'Bad',
            3: 'Good',
            4: 'Very Good',
            5: 'Excellent'
        };

        function hoverQuickStars(rating) {
            var labelEl = document.getElementById('quick-rating-label');
            if (labelEl && ratingLabels[rating]) {
                labelEl.innerText = ratingLabels[rating];
            }
            for (var s = 1; s <= 5; s++) {
                var starEl = document.getElementById('quick-star-' + s);
                if (starEl) {
                    if (s <= rating) {
                        starEl.className = 'fa-solid fa-star quick-star-item';
                        starEl.style.color = '#26a541';
                    } else {
                        starEl.className = 'fa-regular fa-star quick-star-item';
                        starEl.style.color = '#b0b8c1';
                    }
                }
            }
        }

        function resetQuickStars(savedRating) {
            var activeRating = currentQuickRating > 0 ? currentQuickRating : savedRating;
            var labelEl = document.getElementById('quick-rating-label');
            if (labelEl) {
                labelEl.innerText = activeRating > 0 ? (ratingLabels[activeRating] || 'Great') : 'Rate';
            }
            for (var s = 1; s <= 5; s++) {
                var starEl = document.getElementById('quick-star-' + s);
                if (starEl) {
                    if (s <= activeRating) {
                        starEl.className = 'fa-solid fa-star quick-star-item';
                        starEl.style.color = '#26a541';
                    } else {
                        starEl.className = 'fa-regular fa-star quick-star-item';
                        starEl.style.color = '#b0b8c1';
                    }
                }
            }
        }

        function submitQuickRating(rating, productId, orderNumber) {
            currentQuickRating = rating;
            resetQuickStars(rating);

            // Update write review button href
            var btn = document.getElementById('quick-write-review-btn');
            if (btn) {
                var baseHref = '<?= site_url('account/rate_review/' . $order['order_number'] . '/' . $cur_pid); ?>';
                btn.href = baseHref + '?rating=' + rating;
            }

            var csrfName = '<?= $csrf_name; ?>';
            var csrfHash = '<?= $csrf_hash; ?>';

            // Send rating via quick AJAX
            var fd = new FormData();
            fd.append('order_number', orderNumber);
            fd.append('product_id', productId);
            fd.append('rating', rating);
            fd.append(csrfName, csrfHash);

            fetch('<?= site_url('account/quick_rate'); ?>', {
                method: 'POST',
                body: fd
            }).then(function(res) {
                return res.json();
            }).then(function(data) {
                var alertEl = document.getElementById('quick-rate-saved-alert');
                if (alertEl) {
                    alertEl.classList.remove('d-none');
                    setTimeout(function() {
                        alertEl.classList.add('d-none');
                    }, 2500);
                }
            }).catch(function(err) {
                console.error('Rating error:', err);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }

            ['deliveryDetailsBody', 'priceDetailsBody'].forEach(function(id) {
                var el = document.getElementById(id);
                if (el) {
                    el.addEventListener('show.bs.collapse', function() {
                        var icon = document.querySelector('[data-bs-target="#' + id + '"] i');
                        if (icon) {
                            icon.classList.remove('fa-chevron-down');
                            icon.classList.add('fa-chevron-up');
                        }
                    });
                    el.addEventListener('hide.bs.collapse', function() {
                        var icon = document.querySelector('[data-bs-target="#' + id + '"] i');
                        if (icon) {
                            icon.classList.remove('fa-chevron-up');
                            icon.classList.add('fa-chevron-down');
                        }
                    });
                }
            });
        });
        </script>
