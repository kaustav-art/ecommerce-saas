        <!-- breadcrumb -->
        <div class="py-2 border-bottom" style="background-color: #f1f3f6;">
            <div class="fk-orders-container px-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 12px;">
                        <li class="breadcrumb-item"><a href="<?= site_url('home'); ?>" class="text-muted text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url('account/profile'); ?>" class="text-muted text-decoration-none">My Account</a></li>
                        <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">My Orders</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- /breadcrumb -->

        <!-- orders-section -->
        <section class="py-3" style="background-color: #f1f3f6; min-height: 85vh;">
            <div class="fk-orders-container px-3">
                <div class="row g-3">
                    <!-- Left Filters Sidebar (Matching new_order_page.PNG) -->
                    <div class="col-lg-3 col-md-4 col-12 fk-filter-sidebar-col">
                        <!-- Mobile Filter Toggle Button (Screens < 768px) -->
                        <button class="btn btn-white border w-100 d-md-none mb-2 d-flex align-items-center justify-content-between py-2 px-3 bg-white shadow-sm rounded-1" type="button" data-bs-toggle="collapse" data-bs-target="#orderFilterCollapse" aria-expanded="false" style="border-color: #e0e0e0 !important;">
                            <span class="fw-semibold text-dark"><i class="fa-solid fa-sliders text-primary me-2"></i>Filter Orders</span>
                            <i class="fa-solid fa-chevron-down text-muted small"></i>
                        </button>

                        <div class="collapse d-md-block" id="orderFilterCollapse">
                            <div class="card border rounded-1 shadow-sm bg-white p-3 mb-3 mb-md-0" style="border-color: #e0e0e0 !important;">
                                <h5 class="fw-bold mb-3 text-dark" style="font-size: 16px;">Filters</h5>

                                <!-- ORDER STATUS -->
                                <div class="filter-section mb-3">
                                    <div class="fw-bold text-uppercase text-dark mb-2" style="font-size: 12px; letter-spacing: 0.3px;">
                                        ORDER STATUS
                                    </div>
                                    <div class="d-flex flex-column gap-2">
                                        <label class="d-flex align-items-center gap-2 mb-0" style="cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" class="fk-filter-status form-check-input mt-0 rounded-0" value="on_the_way" onchange="filterOrders()">
                                            <span>On the way</span>
                                        </label>
                                        <label class="d-flex align-items-center gap-2 mb-0" style="cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" class="fk-filter-status form-check-input mt-0 rounded-0" value="delivered" onchange="filterOrders()">
                                            <span>Delivered</span>
                                        </label>
                                        <label class="d-flex align-items-center gap-2 mb-0" style="cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" class="fk-filter-status form-check-input mt-0 rounded-0" value="cancelled" onchange="filterOrders()">
                                            <span>Cancelled</span>
                                        </label>
                                        <label class="d-flex align-items-center gap-2 mb-0" style="cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" class="fk-filter-status form-check-input mt-0 rounded-0" value="returned" onchange="filterOrders()">
                                            <span>Returned</span>
                                        </label>
                                    </div>
                                </div>

                                <hr class="my-3" style="border-color: #f0f0f0;">

                                <!-- ORDER TIME -->
                                <div class="filter-section">
                                    <div class="fw-bold text-uppercase text-dark mb-2" style="font-size: 12px; letter-spacing: 0.3px;">
                                        ORDER TIME
                                    </div>
                                    <div class="d-flex flex-column gap-2">
                                        <label class="d-flex align-items-center gap-2 mb-0" style="cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" class="fk-filter-time form-check-input mt-0 rounded-0" value="last_30" onchange="filterOrders()">
                                            <span>Last 30 days</span>
                                        </label>
                                        <label class="d-flex align-items-center gap-2 mb-0" style="cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" class="fk-filter-time form-check-input mt-0 rounded-0" value="2026" onchange="filterOrders()">
                                            <span>2026</span>
                                        </label>
                                        <label class="d-flex align-items-center gap-2 mb-0" style="cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" class="fk-filter-time form-check-input mt-0 rounded-0" value="2025" onchange="filterOrders()">
                                            <span>2025</span>
                                        </label>
                                        <label class="d-flex align-items-center gap-2 mb-0" style="cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" class="fk-filter-time form-check-input mt-0 rounded-0" value="2024" onchange="filterOrders()">
                                            <span>2024</span>
                                        </label>
                                        <label class="d-flex align-items-center gap-2 mb-0" style="cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" class="fk-filter-time form-check-input mt-0 rounded-0" value="older" onchange="filterOrders()">
                                            <span>Older</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Orders Content (Matching new_order_page.PNG) -->
                    <div class="col-lg-9 col-md-8 col-12" style="flex: 1;">
                        <!-- Search Bar matching new_order_page.PNG -->
                        <div class="card border rounded-1 shadow-none p-0 mb-3 bg-white" style="border-color: #e0e0e0 !important;">
                            <div class="d-flex align-items-center">
                                <input type="text" id="order-search-input" class="form-control border-0 shadow-none px-3" placeholder="Search your orders here" style="font-size: 14px; height: 44px;">
                                <button type="button" class="btn btn-primary px-3 px-sm-4 fw-semibold rounded-0 rounded-end d-flex align-items-center gap-2 flex-shrink-0" onclick="filterOrders()" style="background-color: #2874f0; border-color: #2874f0; height: 44px; font-size: 14px;">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                    <span class="d-none d-sm-inline">Search Orders</span>
                                    <span class="d-sm-none">Search</span>
                                </button>
                            </div>
                        </div>

                                <!-- Orders Items List -->
                        <div class="order-items-list" id="order-items-container">
                            <?php
                            $has_items = false;
                            if (!empty($orders)):
                                foreach ($orders as $ord):
                                    $status = strtolower($ord['order_status']);
                                    $items = $ord['items'] ?? [];
                                    if (empty($items)) {
                                        $items = [[
                                            'id'            => 0,
                                            'product_id'    => 0,
                                            'product_title' => 'Order #' . $ord['order_number'],
                                            'product_image' => 'products/womens/women-1.jpg',
                                            'variant_title' => '',
                                            'price'         => $ord['total_amount'],
                                            'quantity'      => 1,
                                            'total'         => $ord['total_amount'],
                                            'item_status'   => $status
                                        ]];
                                    }

                                    $order_year = date('Y', strtotime($ord['created_at']));
                                    $order_ts = strtotime($ord['created_at']);
                                    $order_date_placed = date('d M, Y', strtotime($ord['created_at']));

                                    foreach ($items as $item):
                                        $has_items = true;
                                        $img_src = !empty($item['product_image']) ? base_url('assets/images/' . $item['product_image']) : base_url('assets/images/products/womens/women-1.jpg');
                                        $pid = (int) ($item['product_id'] ?? 0);
                                        $it_id = (int) ($item['id'] ?? 0);

                                        // Individual Item Status & Courier Details
                                        $it_status = !empty($item['item_status']) ? strtolower($item['item_status']) : $status;
                                        $it_courier = !empty($item['courier_name']) ? trim($item['courier_name']) : (!empty($ord['courier_name']) ? trim($ord['courier_name']) : '');
                                        $it_tracking = !empty($item['tracking_number']) ? trim($item['tracking_number']) : (!empty($ord['tracking_number']) ? trim($ord['tracking_number']) : '');
                                        $it_delivered_at = !empty($item['delivered_at']) ? $item['delivered_at'] : (!empty($ord['delivered_at']) ? $ord['delivered_at'] : ($ord['updated_at'] ?? $ord['created_at']));
                                        $it_track_url = site_url('account/order/' . $ord['order_number'] . ($it_id > 0 ? '?item_id=' . $it_id : ''));

                                        // Determine status group for sidebar checkbox filtering
                                        $status_group = 'on_the_way';
                                        if ($it_status === 'cancelled') {
                                            $status_group = 'cancelled';
                                        } elseif (in_array($it_status, ['delivered', 'completed'])) {
                                            $status_group = 'delivered';
                                        } elseif ($it_status === 'returned') {
                                            $status_group = 'returned';
                                        }

                                        // Format color & size variant line nicely
                                        $variant_str = '';
                                        if (!empty($item['variant_title'])) {
                                            $vt = trim($item['variant_title']);
                                            if (strpos($vt, '/') !== false) {
                                                $parts = explode('/', $vt);
                                                $color_part = trim($parts[0]);
                                                $size_part = trim($parts[1] ?? '');
                                                $variant_str = 'Color: ' . $color_part . (!empty($size_part) ? ' Size: ' . $size_part : '');
                                            } else {
                                                $variant_str = $vt;
                                            }
                                        }

                                        // Search keywords for this item
                                        $item_search_keywords = strtolower($ord['order_number'] . ' ' . ($item['product_title'] ?? '') . ' ' . ($item['variant_title'] ?? ''));
                            ?>
                                        <!-- Individual Order Item Card (Matching order_page.png) -->
                                        <div class="card border rounded-1 mb-3 bg-white shadow-none fk-order-card" 
                                             onclick="window.location.href='<?= $it_track_url; ?>'"
                                             style="border-color: #e0e0e0 !important; overflow: hidden; cursor: pointer; transition: box-shadow 0.2s, border-color 0.2s;" 
                                             data-search-text="<?= html_escape($item_search_keywords); ?>"
                                             data-status-group="<?= html_escape($status_group); ?>"
                                             data-year="<?= $order_year; ?>"
                                             data-created-ts="<?= $order_ts; ?>">

                                            <div class="p-3 px-md-4">
                                                <div class="row align-items-center g-3">
                                                    <!-- Left Column: Product Thumbnail + Title + Variant + Order # & Placed on -->
                                                    <div class="col-12 col-md-6 d-flex align-items-start gap-3">
                                                        <a href="<?= $it_track_url; ?>" class="flex-shrink-0" onclick="event.stopPropagation();">
                                                            <img src="<?= $img_src; ?>" alt="<?= html_escape($item['product_title']); ?>" class="border rounded-1" style="width: 70px; height: 70px; object-fit: contain; background-color: #fafafa;" onerror="this.src='<?= base_url('assets/images/products/womens/women-1.jpg'); ?>'">
                                                        </a>
                                                        <div class="flex-grow-1" style="min-width: 0;">
                                                            <a href="<?= $it_track_url; ?>" class="text-decoration-none text-dark fw-bold d-block mb-1 fk-item-title text-line-clamp-2" style="font-size: 14px; line-height: 1.4;" onclick="event.stopPropagation();">
                                                                <?= html_escape($item['product_title']); ?>
                                                            </a>
                                                            <?php if (!empty($variant_str)): ?>
                                                                <div class="text-muted small mb-1" style="font-size: 12px; color: #878787 !important;"><?= html_escape($variant_str); ?></div>
                                                            <?php endif; ?>
                                                            <div class="text-secondary small" style="font-size: 12px; color: #878787 !important;">
                                                                Order <a href="<?= $it_track_url; ?>" class="text-secondary text-decoration-none fw-semibold" onclick="event.stopPropagation();">#<?= html_escape($ord['order_number']); ?></a> | Placed on <?= $order_date_placed; ?>
                                                            </div>
                                                            <?php if ((int)($item['quantity'] ?? 1) > 1): ?>
                                                                <div class="text-muted small mt-1" style="font-size: 11px;">Qty: <?= (int)$item['quantity']; ?></div>
                                                            <?php endif; ?>
                                                            <!-- Mobile Price -->
                                                            <div class="fw-bold text-dark d-md-none mt-1" style="font-size: 14px;">
                                                                <?= $currency_symbol . number_format($item['price'], 0); ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Middle Column: Desktop Price -->
                                                    <div class="col-md-2 d-none d-md-block text-center">
                                                        <div class="fw-bold text-dark" style="font-size: 15px;">
                                                            <?= $currency_symbol . number_format($item['price'], 0); ?>
                                                        </div>
                                                    </div>

                                                    <!-- Right Column: Status & Courier & Rate/Review Button -->
                                                    <div class="col-12 col-md-4 text-start pt-2 pt-md-0 border-top border-top-md-0 border-light">
                                                        <?php if ($it_status === 'cancelled'): ?>
                                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                                <span class="rounded-circle d-inline-block flex-shrink-0" style="width: 10px; height: 10px; background-color: #e53935;"></span>
                                                                <span class="fw-bold text-dark" style="font-size: 14px;">Cancelled</span>
                                                            </div>
                                                            <div class="text-muted small" style="font-size: 12px;">This item was cancelled</div>
                                                        <?php elseif (in_array($it_status, ['delivered', 'completed'])): ?>
                                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                                <span class="rounded-circle d-inline-block flex-shrink-0" style="width: 10px; height: 10px; background-color: #26a541;"></span>
                                                                <span class="fw-bold text-dark" style="font-size: 14px;">Delivered on <?= date('d M, Y', strtotime($it_delivered_at)); ?></span>
                                                            </div>
                                                            <div class="text-muted small mb-1" style="font-size: 12px;">Your item has been delivered</div>
                                                            <!-- After delivered: Track Item button is NOT shown, ONLY Rate & Review -->
                                                            <div class="mt-2">
                                                                <a href="<?= site_url('account/rate_review/' . $ord['order_number'] . '/' . $pid); ?>" onclick="event.stopPropagation();" class="fw-semibold text-decoration-none d-inline-flex align-items-center gap-1" style="color: #2874f0 !important; font-size: 13px;">
                                                                    <i class="fa-solid fa-star" style="color: #2874f0;"></i> Rate & Review Product
                                                                </a>
                                                            </div>
                                                        <?php elseif ($it_status === 'shipped'): ?>
                                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                                <span class="rounded-circle d-inline-block flex-shrink-0" style="width: 10px; height: 10px; background-color: #2874f0;"></span>
                                                                <span class="fw-bold text-dark" style="font-size: 14px;">Packaging & Courier Dispatch</span>
                                                            </div>
                                                            <div class="text-secondary small" style="font-size: 12px;">
                                                                <?php if (!empty($it_courier)): ?>
                                                                    <i class="fa-solid fa-truck-fast text-danger me-1"></i><?= html_escape($it_courier); ?><?= !empty($it_tracking) ? ' (AWB: ' . html_escape($it_tracking) . ')' : ''; ?>
                                                                <?php else: ?>
                                                                    <i class="fa-solid fa-truck-fast text-danger me-1"></i>Dispatched via courier partner
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                                <span class="rounded-circle d-inline-block flex-shrink-0" style="width: 10px; height: 10px; background-color: #ff9800;"></span>
                                                                <span class="fw-bold text-dark" style="font-size: 14px;">Order Placed, <?= date('d M', strtotime($ord['created_at'])); ?></span>
                                                            </div>
                                                            <div class="text-muted small" style="font-size: 12px;">Preparing for packaging & courier dispatch</div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                            <?php
                                    endforeach;
                                endforeach;
                            endif;

                            if (!$has_items):
                            ?>
                                <div class="card border rounded-1 p-5 bg-white text-center shadow-none" style="border-color: #e0e0e0 !important;">
                                    <div class="mb-3 text-muted">
                                        <i class="fa-solid fa-box-open" style="font-size: 48px; color: #b0bec5;"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1">No orders found</h5>
                                    <p class="text-muted small mb-4">You haven't placed any orders yet. Discover items and place your first order.</p>
                                    <div>
                                        <a href="<?= site_url('shop'); ?>" class="btn btn-primary px-4 py-2 rounded-1 fw-semibold" style="background-color: #2874f0; border-color: #2874f0;">
                                            Shop Now
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- No Search Results Container -->
                        <div id="no-search-results" class="card border rounded-1 p-5 bg-white text-center shadow-none d-none" style="border-color: #e0e0e0 !important;">
                            <div class="mb-2 text-muted">
                                <i class="fa-solid fa-magnifying-glass fs-1 text-muted"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">No matching orders found</h6>
                            <p class="text-muted small mb-3">Try searching with a different product name or adjusting your filters.</p>
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-1" onclick="resetSearch()">Clear All Filters</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /orders-section -->

        <style>
        .fk-orders-container {
            max-width: 1680px;
            margin: 0 auto;
        }
        @media (min-width: 992px) {
            .fk-orders-container {
                min-width: 978px;
            }
            .fk-filter-sidebar-col {
                max-width: 270px;
            }
        }
        @media (max-width: 991px) {
            .fk-orders-container {
                min-width: 100% !important;
                max-width: 100% !important;
            }
        }
        .fk-order-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
            border-color: #2874f0 !important;
        }
        .fk-item-title:hover {
            color: #2874f0 !important;
        }
        </style>

        <script>
        function filterOrders() {
            var query = document.getElementById('order-search-input').value.trim().toLowerCase();

            // Checked status filters
            var checkedStatuses = [];
            document.querySelectorAll('.fk-filter-status:checked').forEach(function(el) {
                checkedStatuses.push(el.value);
            });

            // Checked time filters
            var checkedTimes = [];
            document.querySelectorAll('.fk-filter-time:checked').forEach(function(el) {
                checkedTimes.push(el.value);
            });

            var now = Math.floor(Date.now() / 1000);
            var thirtyDaysAgo = now - (30 * 86400);

            var cards = document.querySelectorAll('.fk-order-card');
            var visibleCount = 0;

            cards.forEach(function(card) {
                var cardText = card.getAttribute('data-search-text') || '';
                var cardStatus = card.getAttribute('data-status-group') || '';
                var cardYear = card.getAttribute('data-year') || '';
                var cardTs = parseInt(card.getAttribute('data-created-ts'), 10) || 0;

                // Check query
                var matchQuery = (!query || cardText.indexOf(query) !== -1);

                // Check status
                var matchStatus = (checkedStatuses.length === 0 || checkedStatuses.some(function(s) {
                    return cardStatus.indexOf(s) !== -1;
                }));

                // Check time
                var matchTime = true;
                if (checkedTimes.length > 0) {
                    matchTime = false;
                    for (var i = 0; i < checkedTimes.length; i++) {
                        var t = checkedTimes[i];
                        if (t === 'last_30' && cardTs >= thirtyDaysAgo) {
                            matchTime = true;
                            break;
                        } else if (t === cardYear) {
                            matchTime = true;
                            break;
                        } else if (t === 'older' && parseInt(cardYear, 10) < 2024) {
                            matchTime = true;
                            break;
                        }
                    }
                }

                if (matchQuery && matchStatus && matchTime) {
                    card.classList.remove('d-none');
                    visibleCount++;
                } else {
                    card.classList.add('d-none');
                }
            });

            var noRes = document.getElementById('no-search-results');
            if (noRes) {
                if (visibleCount === 0 && cards.length > 0) {
                    noRes.classList.remove('d-none');
                } else {
                    noRes.classList.add('d-none');
                }
            }
        }

        document.getElementById('order-search-input').addEventListener('keyup', function(e) {
            filterOrders();
        });

        function resetSearch() {
            document.getElementById('order-search-input').value = '';
            document.querySelectorAll('.fk-filter-status:checked, .fk-filter-time:checked').forEach(function(el) {
                el.checked = false;
            });
            filterOrders();
        }
        </script>


