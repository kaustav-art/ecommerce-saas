        <!-- page-title -->
        <div class="page-title" style="background-image: url('<?= base_url('assets/images/section/page-title.jpg'); ?>');">
            <div class="container-full">
                <div class="row">
                    <div class="col-12 text-center">
                        <?php if (!empty($is_brand_store) && !empty($brand_info['logo'])): ?>
                            <div class="mb-2">
                                <img src="<?= base_url('assets/images/' . $brand_info['logo']); ?>" alt="<?= html_escape($brand_info['name']); ?>" style="width: 64px; height: 64px; object-fit: contain; background: #fff; border-radius: 50%; padding: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.12);" onerror="this.style.display='none'">
                            </div>
                        <?php endif; ?>
                        <h3 class="heading"><?= html_escape($page_heading); ?></h3>
                        <ul class="breadcrumbs d-flex align-items-center justify-content-center flex-wrap">
                            <li><a class="link" href="<?= site_url('home'); ?>">Homepage</a></li>
                            <li><i class="fa-solid fa-chevron-right mx-2 text-muted" style="font-size: 11px;"></i></li>
                            <li><a class="link" href="<?= site_url('shop'); ?>">Shop</a></li>
                            <?php if (!empty($breadcrumbs)): ?>
                                <?php foreach ($breadcrumbs as $idx => $bc): ?>
                                    <li><i class="fa-solid fa-chevron-right mx-2 text-muted" style="font-size: 11px;"></i></li>
                                    <?php if ($idx === count($breadcrumbs) - 1): ?>
                                        <li class="active"><?= html_escape($bc['name']); ?></li>
                                    <?php else: ?>
                                        <li><a class="link" href="<?= site_url('shop/' . $bc['slug']); ?>"><?= html_escape($bc['name']); ?></a></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- /page-title -->


        <!-- Section product -->
        <section class="flat-spacing pt-4 position-relative">
            <!-- Whole Page Section Loading Overlay (Project Preloader) -->
            <div id="shopLoader" class="shop-preload-overlay preload-container d-none">
                <div class="preload-logo">
                    <div class="spinner"></div>
                </div>
            </div>

            <div class="container">
                <?php
                    $base_filter_url = $selected_category ? site_url('shop/' . $selected_category['slug']) : site_url('shop');
                    $sort_labels = [
                        'a-z'            => 'Alphabetically, A-Z',
                        'z-a'            => 'Alphabetically, Z-A',
                        'price-low-high' => 'Price, low to high',
                        'price-high-low' => 'Price, high to low',
                    ];
                ?>

                <!-- Control Bar (Filter button on mobile + Sale toggle + 4-option Sort dropdown) -->
                <div class="tf-shop-control d-flex justify-content-between mb-4">
                    <div class="tf-control-filter">
                        <button type="button" id="filterShop" class="filterShop tf-btn-filter d-xl-none">
                            <i class="fa-solid fa-sliders me-2"></i>
                            <span class="text">Filters</span>
                        </button>
                        <div class="d-none d-lg-flex shop-sale-text align-items-center">
                            <a href="javascript:void(0);" id="saleToggleLink" class="text-decoration-none <?= !empty($filters['on_sale']) ? 'fw-bold text-danger is-sale-active' : 'text-secondary'; ?>">
                                <i class="fa-solid fa-circle-check me-1"></i>
                                <span class="text-caption-1">Shop sale items only</span>
                            </a>
                        </div>
                    </div>

                    <!-- Sort By Dropdown (Strictly 4 options: A-Z, Z-A, price low to high, price high to low) -->
                    <div class="tf-control-sorting">
                        <p class="d-none d-lg-block text-caption-1 me-2 mb-0">Sort by:</p>
                        <div class="tf-dropdown-sort" data-bs-toggle="dropdown">
                            <div class="btn-select">
                                <span class="text-sort-value"><?= $sort_labels[$filters['sort']] ?? 'Alphabetically, A-Z'; ?></span>
                                <i class="fa-solid fa-chevron-down ms-2" style="font-size: 11px;"></i>
                            </div>
                            <div class="dropdown-menu">
                                <div class="select-item <?= ($filters['sort'] === 'a-z') ? 'active' : ''; ?>" data-sort-val="a-z" onclick="updateSort('a-z', 'Alphabetically, A-Z')">
                                    <span class="text-value-item">Alphabetically, A-Z</span>
                                </div>
                                <div class="select-item <?= ($filters['sort'] === 'z-a') ? 'active' : ''; ?>" data-sort-val="z-a" onclick="updateSort('z-a', 'Alphabetically, Z-A')">
                                    <span class="text-value-item">Alphabetically, Z-A</span>
                                </div>
                                <div class="select-item <?= ($filters['sort'] === 'price-low-high') ? 'active' : ''; ?>" data-sort-val="price-low-high" onclick="updateSort('price-low-high', 'Price, low to high')">
                                    <span class="text-value-item">Price, low to high</span>
                                </div>
                                <div class="select-item <?= ($filters['sort'] === 'price-high-low') ? 'active' : ''; ?>" data-sort-val="price-high-low" onclick="updateSort('price-high-low', 'Price, high to low')">
                                    <span class="text-value-item">Price, high to low</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Applied filters meta bar -->
                <div class="wrapper-control-shop mb-4" id="metaFilterShop">
                    <?php $this->load->view('shop/partials/meta_bar'); ?>
                </div>

                <!-- 2-Column Row: Left Sidebar (.col-xl-3) + Products Grid (.col-xl-9) -->
                <div class="row">
                    <!-- Left Sidebar Filter (Follows shop-left-sidebar.html) -->
                    <div class="col-xl-3">
                        <div class="sidebar-filter canvas-filter left" id="sidebarFilter">
                            <div class="canvas-wrapper">
                                <div class="canvas-header d-flex d-xl-none justify-content-between align-items-center p-3 border-bottom">
                                    <h5 class="m-0 fw-bold">Filters</h5>
                                    <div class="d-flex align-items-center gap-3">
                                        <button type="button" class="btn-clear-all-ajax text-danger border-0 bg-transparent p-0 small fw-semibold cursor-pointer">Remove All</button>
                                        <span class="icon-close icon-close-popup close-filter cursor-pointer" style="font-size: 16px;"></span>
                                    </div>
                                </div>
                                <div class="canvas-body p-3 p-xl-0">
                                    <form action="<?= $base_filter_url; ?>" method="GET" id="shopFilterForm" onsubmit="return false;">
                                        <input type="hidden" name="sort" id="filter-sort-input" value="<?= html_escape($filters['sort']); ?>">
                                        <input type="hidden" name="q" id="filter-search-input" value="<?= html_escape($filters['search'] ?? ''); ?>">

                                        <!-- Sidebar Header with Remove All Reset Button (Desktop only) -->
                                        <div class="d-none d-xl-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                                            <span class="fw-bold text-dark text-uppercase small" style="letter-spacing: 0.5px;">
                                                <i class="fa-solid fa-sliders me-1"></i> Filters
                                            </span>
                                            <button type="button" class="btn-clear-all-ajax text-danger border-0 bg-transparent p-0 small fw-semibold cursor-pointer d-flex align-items-center gap-1" title="Reset all filters">
                                                <i class="fa-solid fa-rotate-left"></i> Remove All
                                            </button>
                                        </div>

                                        <!-- Price Filter -->
                                        <div class="widget-facet facet-price">
                                            <h6 class="facet-title fw-bold">Price</h6>
                                            <div class="price-val-range" id="price-value-range"
                                                 data-min="<?= $min_catalog_price; ?>"
                                                 data-max="<?= $max_catalog_price; ?>"
                                                 data-current-min="<?= !empty($filters['min_price']) ? (int)$filters['min_price'] : $min_catalog_price; ?>"
                                                 data-current-max="<?= !empty($filters['max_price']) ? (int)$filters['max_price'] : $max_catalog_price; ?>"></div>
                                            <div class="box-price-product">
                                                <div class="box-price-item">
                                                    <span class="title-price">Min price</span>
                                                    <div class="price-val" id="price-min-value" data-currency="$"><?= !empty($filters['min_price']) ? (int)$filters['min_price'] : $min_catalog_price; ?></div>
                                                    <input type="hidden" name="min_price" id="input-min-price" value="<?= !empty($filters['min_price']) ? html_escape($filters['min_price']) : ''; ?>">
                                                </div>
                                                <div class="box-price-item">
                                                    <span class="title-price">Max price</span>
                                                    <div class="price-val" id="price-max-value" data-currency="$"><?= !empty($filters['max_price']) ? (int)$filters['max_price'] : $max_catalog_price; ?></div>
                                                    <input type="hidden" name="max_price" id="input-max-price" value="<?= !empty($filters['max_price']) ? html_escape($filters['max_price']) : ''; ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Size Filter (Strictly ONE size applicable) -->
                                        <?php if (!empty($filter_sizes)): ?>
                                            <?php
                                                $active_size_id = null;
                                                if (!empty($filters['attr_value_ids'])) {
                                                    foreach ($filter_sizes as $sz) {
                                                        if (in_array((int)$sz['id'], $filters['attr_value_ids'])) {
                                                            $active_size_id = (int)$sz['id'];
                                                            break;
                                                        }
                                                    }
                                                }
                                            ?>
                                            <div class="widget-facet facet-size">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="facet-title fw-bold m-0">Size</h6>
                                                    <?php if ($active_size_id): ?>
                                                        <span class="text-danger small cursor-pointer clear-size-link" style="font-size: 11px;">Clear</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="facet-size-box size-box">
                                                    <?php foreach ($filter_sizes as $sz): ?>
                                                        <?php $is_checked = ((int)$sz['id'] === $active_size_id); ?>
                                                        <span class="size-item size-check <?= strlen($sz['value']) > 3 ? 'free-size' : ''; ?> <?= $is_checked ? 'active' : ''; ?>" data-val-id="<?= $sz['id']; ?>" title="Size: <?= html_escape($sz['value']); ?>">
                                                            <input type="checkbox" name="attrs[]" value="<?= $sz['id']; ?>" class="d-none size-attr-checkbox" id="attr_size_<?= $sz['id']; ?>" <?= $is_checked ? 'checked' : ''; ?>>
                                                            <?= html_escape($sz['value']); ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Color Filter -->
                                        <?php if (!empty($filter_colors)): ?>
                                            <div class="widget-facet facet-color">
                                                <h6 class="facet-title fw-bold">Colors</h6>
                                                <div class="facet-color-box">
                                                    <?php foreach ($filter_colors as $clr): ?>
                                                        <?php 
                                                            $is_checked = in_array((int)$clr['id'], $filters['attr_value_ids'] ?? []);
                                                            $color_code = !empty($clr['color_code']) ? $clr['color_code'] : '#cccccc';
                                                            $is_white = strtolower($color_code) === '#ffffff' || strtolower($clr['value']) === 'white';
                                                        ?>
                                                        <div class="color-item color-check <?= $is_checked ? 'active' : ''; ?>" data-val-id="<?= $clr['id']; ?>">
                                                            <input type="checkbox" name="attrs[]" value="<?= $clr['id']; ?>" class="d-none attr-checkbox" id="attr_color_<?= $clr['id']; ?>" <?= $is_checked ? 'checked' : ''; ?>>
                                                            <span class="color <?= $is_white ? 'line-black border' : ''; ?>" style="background-color: <?= html_escape($color_code); ?>;"></span>
                                                            <?= html_escape($clr['value']); ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Other Variants (e.g. Material, Storage, etc.) -->
                                        <?php if (!empty($other_variants)): ?>
                                            <?php foreach ($other_variants as $ov_attr): ?>
                                                <div class="widget-facet facet-fieldset">
                                                    <h6 class="facet-title fw-bold"><?= html_escape($ov_attr['name']); ?></h6>
                                                    <div class="box-fieldset-item" style="max-height: 200px; overflow-y: auto;">
                                                        <?php foreach ($ov_attr['values'] as $ov_val): ?>
                                                            <?php $is_checked = in_array((int)$ov_val['id'], $filters['attr_value_ids'] ?? []); ?>
                                                            <fieldset class="fieldset-item">
                                                                <input type="checkbox" name="attrs[]" value="<?= $ov_val['id']; ?>" class="tf-check attr-checkbox" id="attr_ov_<?= $ov_val['id']; ?>" <?= $is_checked ? 'checked' : ''; ?>>
                                                                <label for="attr_ov_<?= $ov_val['id']; ?>" class="cursor-pointer">
                                                                    <?= html_escape($ov_val['value']); ?>
                                                                    <?php if (isset($ov_val['product_count'])): ?>
                                                                        <span class="count-stock text-muted small">(<?= $ov_val['product_count']; ?>)</span>
                                                                    <?php endif; ?>
                                                                </label>
                                                            </fieldset>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                        <!-- Brands Filter (Respect to the selected product category) -->
                                        <?php if (!empty($brands)): ?>
                                            <div class="widget-facet facet-fieldset">
                                                <h6 class="facet-title fw-bold">Brands</h6>
                                                <div class="box-fieldset-item" style="max-height: 240px; overflow-y: auto;">
                                                    <?php foreach ($brands as $b): ?>
                                                        <?php $is_checked = in_array($b['slug'], $selected_brands); ?>
                                                        <fieldset class="fieldset-item">
                                                            <input type="checkbox" name="brand[]" value="<?= html_escape($b['slug']); ?>" class="tf-check brand-checkbox" id="brand_<?= $b['id']; ?>" <?= $is_checked ? 'checked' : ''; ?>>
                                                            <label for="brand_<?= $b['id']; ?>" class="cursor-pointer">
                                                                <?= html_escape($b['name']); ?>
                                                                <span class="count-brand text-muted small">(<?= $b['product_count'] ?? 0; ?>)</span>
                                                            </label>
                                                        </fieldset>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Products Grid (.col-xl-9) -->
                    <div class="col-xl-9">
                        <div class="shop-products-container position-relative">
                            <!-- Products Grid (2 columns on mobile, 4 columns on desktop like home page) -->
                            <div class="tf-grid-layout tf-col-2 lg-col-3 xl-col-4 wrapper-shop" id="productGridContainer">
                                <?php $this->load->view('shop/partials/product_grid'); ?>
                            </div>

                            <!-- Infinite Scroll Sentinel & Bottom Loader (like home page) -->
                            <div id="shopInfiniteScrollSentinel" class="text-center py-4 my-3 <?= ($total_products <= 16) ? 'd-none' : ''; ?>">
                                <div id="shopScrollLoader" class="d-none text-center py-2">
                                    <div class="preload-logo mx-auto mb-2" style="width: 44px; height: 44px;">
                                        <div class="spinner" style="width: 38px; height: 38px; border-width: 2.5px;"></div>
                                    </div>
                                    <div class="text-muted small fw-semibold" style="letter-spacing: 0.5px;">Loading more products...</div>
                                </div>
                                <div id="shopScrollEndMessage" class="<?= ($total_products <= 16 && $total_products > 0) ? '' : 'd-none'; ?> text-muted small py-2">
                                    <i class="fa-solid fa-circle-check text-success me-1"></i> You've viewed all <span id="shopTotalProductsCount"><?= (int)$total_products; ?></span> products
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Section product -->

        <!-- Mobile Drawer Backdrop Overlay -->
        <div class="overlay-filter"></div>

        <!-- Custom Sidebar & Filter CSS -->
        <style>
            @media (min-width: 1200px) {
                .sidebar-filter.canvas-filter.left {
                    position: static !important;
                    transform: none !important;
                    max-width: 100% !important;
                    box-shadow: none !important;
                    z-index: auto !important;
                    width: 100% !important;
                }
                .sidebar-filter .canvas-wrapper {
                    display: block !important;
                }
                .sidebar-filter .canvas-body {
                    padding: 0 !important;
                    overflow: visible !important;
                }
            }
            .sidebar-filter .facet-price .price-val-range {
                padding-left: 10px;
                padding-right: 10px;
            }
            .size-item.size-check, .color-item.color-check {
                user-select: none;
            }
            .cursor-pointer {
                cursor: pointer;
            }
            .btn-xs {
                padding: 3px 8px;
                font-size: 11px;
            }
            .noUi-target {
                background: #e9ecef;
                border-radius: 4px;
                border: none;
                box-shadow: none;
                height: 6px;
            }
            .noUi-connect {
                background: #111111;
            }
            .noUi-handle {
                border: 2px solid #111111;
                border-radius: 50%;
                background: #ffffff;
                cursor: pointer;
                box-shadow: 0 1px 3px rgba(0,0,0,0.2);
                width: 16px !important;
                height: 16px !important;
                right: -8px !important;
                top: -5px !important;
            }
            .noUi-handle:before, .noUi-handle:after {
                display: none !important;
            }

            /* Loader Overlay Styles */
            .shop-products-container {
                position: relative;
                min-height: 380px;
            }
            /* Project Preloader Overlay Styles */
            .shop-preload-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(255, 255, 255, 0.82);
                backdrop-filter: blur(3px);
                -webkit-backdrop-filter: blur(3px);
                z-index: 9999999999;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: opacity 0.2s ease;
            }
            .shop-preload-overlay.d-none {
                display: none !important;
            }
            .shop-preload-overlay .preload-logo {
                position: relative;
                width: 65px;
                height: 65px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .shop-preload-overlay .spinner {
                width: 60px;
                height: 60px;
                border: 3px solid rgba(0, 0, 0, 0.08);
                border-top: 3px solid var(--primary, #111);
                border-radius: 50%;
                position: absolute;
                top: 0;
                bottom: 0;
                left: 0;
                right: 0;
                margin: auto;
                animation: spin 0.8s infinite linear;
            }

            .animate-fade-in {
                animation: productFadeIn 0.35s ease-out;
            }
            @keyframes productFadeIn {
                from {
                    opacity: 0;
                    transform: translateY(12px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Product Badge Styles (Same as Home Page) */
            .product-badge-group {
                position: absolute;
                top: 10px;
                left: 10px;
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
                z-index: 5;
                pointer-events: none;
            }
            .product-badge {
                display: inline-block;
                font-size: 10px;
                font-weight: 700;
                line-height: 1.1;
                padding: 3px 7px;
                border-radius: 3px;
                text-transform: uppercase;
                letter-spacing: 0.3px;
                color: #ffffff;
                box-shadow: 0 1px 3px rgba(0,0,0,0.12);
            }
            .product-badge.badge-sale {
                background-color: #ef4444;
            }
        </style>

        <!-- Load noUiSlider JS -->
        <script src="<?= base_url('assets/js/nouislider.min.js'); ?>"></script>

        <script>
        let isFilterRequestInProgress = false;
        let isLoadingScroll = false;
        let scrollOffset = <?= count($products); ?>;
        const scrollLimit = 16;
        let hasMoreProducts = <?= ($has_more ?? ($total_products > 16)) ? 'true' : 'false'; ?>;
        let totalCatalogProducts = <?= (int)$total_products; ?>;
        let scrollObserver = null;

        // Collect current filter parameters
        function getFilterParams() {
            const params = new URLSearchParams();
<?php if (empty($is_user_project)): ?>
            params.set('preview_tpl', 'fashion');
<?php endif; ?>

            // Sort
            const sortInput = document.getElementById('filter-sort-input');
            const sortVal = sortInput ? sortInput.value : 'a-z';
            if (sortVal && sortVal !== 'a-z') {
                params.set('sort', sortVal);
            }

            // Brands
            document.querySelectorAll('.brand-checkbox:checked').forEach(cb => {
                params.append('brand[]', cb.value);
            });

            // Attributes (Color & Other variants)
            document.querySelectorAll('.attr-checkbox:checked').forEach(cb => {
                params.append('attrs[]', cb.value);
            });

            // Single Size Attribute (strictly only one size applicable)
            const activeSizeCheckbox = document.querySelector('.size-attr-checkbox:checked');
            if (activeSizeCheckbox) {
                params.append('attrs[]', activeSizeCheckbox.value);
            }

            // Price range
            const minInput = document.getElementById('input-min-price');
            const maxInput = document.getElementById('input-max-price');
            const slider = document.getElementById('price-value-range');
            const minLimit = slider ? parseInt(slider.getAttribute('data-min')) : null;
            const maxLimit = slider ? parseInt(slider.getAttribute('data-max')) : null;

            if (minInput && minInput.value !== '' && (minLimit === null || parseInt(minInput.value) > minLimit)) {
                params.set('min_price', minInput.value);
            }
            if (maxInput && maxInput.value !== '' && (maxLimit === null || parseInt(maxInput.value) < maxLimit)) {
                params.set('max_price', maxInput.value);
            }

            // On Sale toggle
            const saleLink = document.getElementById('saleToggleLink');
            if (saleLink && saleLink.classList.contains('is-sale-active')) {
                params.set('on_sale', '1');
            }

            // Search query
            const searchInput = document.getElementById('filter-search-input');
            if (searchInput && searchInput.value) {
                params.set('q', searchInput.value);
            }

            return params;
        }

        // Helper to refresh lazyload images
        function refreshLazyload() {
            if (window.lazySizes && typeof window.lazySizes.autoSizer === 'object') {
                document.querySelectorAll('.lazyload').forEach(img => {
                    const dataSrc = img.getAttribute('data-src');
                    if (dataSrc && (!img.src || img.src === window.location.href)) {
                        img.src = dataSrc;
                    }
                });
            }
        }

        // Trigger instant AJAX filter (resets the grid with first batch of 16 products)
        function triggerAjaxFilter() {
            if (isFilterRequestInProgress) return;
            isFilterRequestInProgress = true;

            const loader = document.getElementById('shopLoader');
            if (loader) loader.classList.remove('d-none');

            const params = getFilterParams();
            const baseUrl = '<?= $base_filter_url; ?>';
            const qs = params.toString();
            const browserUrl = baseUrl + (qs ? '?' + qs : '');

            // 16 products initial batch
            params.set('offset', '0');
            params.set('limit', String(scrollLimit));
            params.set('ajax', '1');
            const fetchUrl = baseUrl + '?' + params.toString();

            fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Filter request failed with status ' + res.status);
                return res.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    // Update Products Grid
                    const grid = document.getElementById('productGridContainer');
                    if (grid) grid.innerHTML = data.products_html;

                    // Update Meta Bar
                    const metaBar = document.getElementById('metaFilterShop');
                    if (metaBar) metaBar.innerHTML = data.meta_html;

                    // Update Infinite Scroll State
                    totalCatalogProducts = data.total_products || 0;
                    scrollOffset = data.count || 0;
                    hasMoreProducts = data.has_more;

                    const sentinel = document.getElementById('shopInfiniteScrollSentinel');
                    const scrollLoader = document.getElementById('shopScrollLoader');
                    const endMsg = document.getElementById('shopScrollEndMessage');
                    const totalCountSpan = document.getElementById('shopTotalProductsCount');
                    if (totalCountSpan) totalCountSpan.innerText = totalCatalogProducts;
                    if (scrollLoader) scrollLoader.classList.add('d-none');

                    if (totalCatalogProducts === 0) {
                        if (sentinel) sentinel.classList.add('d-none');
                        if (endMsg) endMsg.classList.add('d-none');
                        if (scrollObserver && sentinel) scrollObserver.unobserve(sentinel);
                    } else if (!hasMoreProducts) {
                        if (sentinel) sentinel.classList.remove('d-none');
                        if (endMsg) endMsg.classList.remove('d-none');
                        if (scrollObserver && sentinel) scrollObserver.unobserve(sentinel);
                    } else {
                        if (sentinel) sentinel.classList.remove('d-none');
                        if (endMsg) endMsg.classList.add('d-none');
                        if (scrollObserver && sentinel) {
                            scrollObserver.unobserve(sentinel);
                            scrollObserver.observe(sentinel);
                        }
                    }

                    // Push browser history state
                    window.history.pushState({}, '', browserUrl);

                    refreshLazyload();
                }
            })
            .catch(err => {
                console.error('AJAX Filter error:', err);
            })
            .finally(() => {
                isFilterRequestInProgress = false;
                if (loader) loader.classList.add('d-none');
            });
        }

        // Infinite Scroll: Load Next 16 Products on Scroll
        function loadMoreScrollProducts() {
            if (isLoadingScroll || isFilterRequestInProgress || !hasMoreProducts) return;
            isLoadingScroll = true;

            const scrollLoader = document.getElementById('shopScrollLoader');
            if (scrollLoader) scrollLoader.classList.remove('d-none');

            const params = getFilterParams();
            const baseUrl = '<?= $base_filter_url; ?>';

            params.set('offset', String(scrollOffset));
            params.set('limit', String(scrollLimit));
            params.set('append', '1');
            params.set('ajax', '1');
            const fetchUrl = baseUrl + '?' + params.toString();

            fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Scroll load failed with status ' + res.status);
                return res.json();
            })
            .then(data => {
                if (data.status === 'success' && data.products_html) {
                    const grid = document.getElementById('productGridContainer');
                    if (grid) {
                        const temp = document.createElement('div');
                        temp.innerHTML = data.products_html;
                        while (temp.firstChild) {
                            if (temp.firstChild.nodeType === 1) {
                                grid.appendChild(temp.firstChild);
                            } else {
                                temp.removeChild(temp.firstChild);
                            }
                        }
                    }

                    scrollOffset += (data.count || 0);
                    hasMoreProducts = data.has_more;

                    // Update meta bar count
                    const visCount = document.getElementById('shopVisibleCount');
                    if (visCount) visCount.innerText = Math.min(totalCatalogProducts, scrollOffset);
                    const totCount = document.getElementById('shopTotalCount');
                    if (totCount) totCount.innerText = totalCatalogProducts;

                    const sentinel = document.getElementById('shopInfiniteScrollSentinel');
                    const endMsg = document.getElementById('shopScrollEndMessage');

                    if (!hasMoreProducts) {
                        if (scrollObserver && sentinel) scrollObserver.unobserve(sentinel);
                        if (endMsg) endMsg.classList.remove('d-none');
                    }

                    refreshLazyload();
                } else {
                    hasMoreProducts = false;
                    const sentinel = document.getElementById('shopInfiniteScrollSentinel');
                    if (scrollObserver && sentinel) scrollObserver.unobserve(sentinel);
                    const endMsg = document.getElementById('shopScrollEndMessage');
                    if (endMsg) endMsg.classList.remove('d-none');
                }
            })
            .catch(err => {
                console.error('AJAX Scroll load error:', err);
            })
            .finally(() => {
                isLoadingScroll = false;
                if (scrollLoader) scrollLoader.classList.add('d-none');
            });
        }

        // Sort Handler
        function updateSort(val, label) {
            const sortInput = document.getElementById('filter-sort-input');
            if (sortInput) sortInput.value = val;

            const sortValueText = document.querySelector('.text-sort-value');
            if (sortValueText && label) sortValueText.innerText = label;

            document.querySelectorAll('.tf-dropdown-sort .select-item').forEach(item => {
                item.classList.remove('active');
            });
            const activeItem = document.querySelector(`.tf-dropdown-sort .select-item[data-sort-val="${val}"]`);
            if (activeItem) activeItem.classList.add('active');

            triggerAjaxFilter();
        }

        // Clear all filters helper
        function clearAllFilters() {
            // Uncheck all brands
            document.querySelectorAll('.brand-checkbox').forEach(cb => cb.checked = false);

            // Uncheck and deactivate size items
            document.querySelectorAll('.size-item.size-check').forEach(function(si) {
                si.classList.remove('active');
                const cb = si.querySelector('input.size-attr-checkbox');
                if (cb) cb.checked = false;
            });

            // Uncheck all other attrs and remove active classes
            document.querySelectorAll('.attr-checkbox').forEach(cb => cb.checked = false);
            document.querySelectorAll('.color-item').forEach(el => el.classList.remove('active'));

            // Reset price slider
            const slider = document.getElementById('price-value-range');
            if (slider && slider.noUiSlider) {
                const minLimit = parseInt(slider.getAttribute('data-min')) || 0;
                const maxLimit = parseInt(slider.getAttribute('data-max')) || 500;
                slider.noUiSlider.set([minLimit, maxLimit]);
            }
            const minInput = document.getElementById('input-min-price');
            const maxInput = document.getElementById('input-max-price');
            if (minInput) minInput.value = '';
            if (maxInput) maxInput.value = '';

            // Reset sale link
            const saleLink = document.getElementById('saleToggleLink');
            if (saleLink) {
                saleLink.classList.remove('is-sale-active', 'text-danger', 'fw-bold');
                saleLink.classList.add('text-secondary');
            }

            // Clear search input
            const searchInput = document.getElementById('filter-search-input');
            if (searchInput) searchInput.value = '';

            // Close mobile filter if open
            const sidebarFilter = document.getElementById('sidebarFilter');
            const overlayFilter = document.querySelector('.overlay-filter');
            if (sidebarFilter) sidebarFilter.classList.remove('show');
            if (overlayFilter) overlayFilter.classList.remove('show');
            document.body.style.overflow = '';

            triggerAjaxFilter();
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Mobile offcanvas filter toggle
            const filterShopBtn = document.getElementById('filterShop');
            const sidebarFilter = document.getElementById('sidebarFilter');
            const overlayFilter = document.querySelector('.overlay-filter');
            const closeFilterBtn = document.querySelector('.close-filter');

            function openMobileFilter() {
                if (sidebarFilter) sidebarFilter.classList.add('show');
                if (overlayFilter) overlayFilter.classList.add('show');
                document.body.style.overflow = 'hidden';
            }

            function closeMobileFilter() {
                if (sidebarFilter) sidebarFilter.classList.remove('show');
                if (overlayFilter) overlayFilter.classList.remove('show');
                document.body.style.overflow = '';
            }

            if (filterShopBtn) filterShopBtn.addEventListener('click', e => { e.preventDefault(); openMobileFilter(); });
            if (closeFilterBtn) closeFilterBtn.addEventListener('click', e => { e.preventDefault(); closeMobileFilter(); });
            if (overlayFilter) overlayFilter.addEventListener('click', closeMobileFilter);

            // Size items instant click (Strictly ONE size applicable)
            document.querySelectorAll('.size-item.size-check').forEach(function(item) {
                item.addEventListener('click', function(e) {
                    const checkbox = this.querySelector('input.size-attr-checkbox');
                    const wasActive = this.classList.contains('active');

                    // Deselect ALL size items and uncheck their inputs
                    document.querySelectorAll('.size-item.size-check').forEach(function(si) {
                        si.classList.remove('active');
                        const cb = si.querySelector('input.size-attr-checkbox');
                        if (cb) cb.checked = false;
                    });

                    // If it wasn't active before, select this single size
                    if (!wasActive) {
                        this.classList.add('active');
                        if (checkbox) checkbox.checked = true;
                    }

                    closeMobileFilter();
                    triggerAjaxFilter();
                });
            });

            // Clear size link shortcut
            document.addEventListener('click', function(e) {
                if (e.target.closest('.clear-size-link')) {
                    e.preventDefault();
                    document.querySelectorAll('.size-item.size-check').forEach(function(si) {
                        si.classList.remove('active');
                        const cb = si.querySelector('input.size-attr-checkbox');
                        if (cb) cb.checked = false;
                    });
                    closeMobileFilter();
                    triggerAjaxFilter();
                }
            });

            // Color items instant click
            document.querySelectorAll('.color-item.color-check').forEach(function(item) {
                item.addEventListener('click', function(e) {
                    const checkbox = this.querySelector('input[type="checkbox"]');
                    if (checkbox && !e.target.isSameNode(checkbox)) {
                        checkbox.checked = !checkbox.checked;
                    }
                    if (checkbox && checkbox.checked) {
                        this.classList.add('active');
                    } else {
                        this.classList.remove('active');
                    }
                    triggerAjaxFilter();
                });
            });

            // Brand checkboxes change
            document.querySelectorAll('.brand-checkbox').forEach(function(cb) {
                cb.addEventListener('change', function() {
                    triggerAjaxFilter();
                });
            });

            // Other variant checkboxes change
            document.querySelectorAll('input[id^="attr_ov_"]').forEach(function(cb) {
                cb.addEventListener('change', function() {
                    triggerAjaxFilter();
                });
            });

            // Sale Toggle Link instant click
            const saleLink = document.getElementById('saleToggleLink');
            if (saleLink) {
                saleLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    this.classList.toggle('is-sale-active');
                    this.classList.toggle('text-danger');
                    this.classList.toggle('fw-bold');
                    this.classList.toggle('text-secondary');
                    triggerAjaxFilter();
                });
            }

            // Price range slider initialization with noUiSlider
            const rangeSlider = document.getElementById('price-value-range');
            if (rangeSlider && typeof noUiSlider !== 'undefined') {
                const minLimit = parseInt(rangeSlider.getAttribute('data-min')) || 0;
                const maxLimit = parseInt(rangeSlider.getAttribute('data-max')) || 500;
                const currentMin = parseInt(rangeSlider.getAttribute('data-current-min')) || minLimit;
                const currentMax = parseInt(rangeSlider.getAttribute('data-current-max')) || maxLimit;

                noUiSlider.create(rangeSlider, {
                    start: [currentMin, currentMax],
                    connect: true,
                    step: 1,
                    range: {
                        'min': minLimit,
                        'max': maxLimit
                    },
                    format: {
                        to: function(val) { return Math.round(val); },
                        from: function(val) { return Number(val); }
                    }
                });

                const minValDisplay = document.getElementById('price-min-value');
                const maxValDisplay = document.getElementById('price-max-value');
                const minValInput = document.getElementById('input-min-price');
                const maxValInput = document.getElementById('input-max-price');

                // Update text while dragging
                rangeSlider.noUiSlider.on('update', function(values, handle) {
                    if (handle === 0) {
                        if (minValDisplay) minValDisplay.innerText = values[0];
                        if (minValInput) minValInput.value = values[0];
                    } else {
                        if (maxValDisplay) maxValDisplay.innerText = values[1];
                        if (maxValInput) maxValInput.value = values[1];
                    }
                });

                // Trigger instant filter when user finishes sliding handle
                rangeSlider.noUiSlider.on('change', function() {
                    triggerAjaxFilter();
                });
            }

            // Delegated click handling for meta bar (remove tag pills & remove all)
            const metaBarContainer = document.getElementById('metaFilterShop');
            if (metaBarContainer) {
                metaBarContainer.addEventListener('click', function(e) {
                    const removeBtn = e.target.closest('.filter-tag-remove');
                    const clearAllBtn = e.target.closest('.btn-clear-all-ajax');

                    if (removeBtn) {
                        e.preventDefault();
                        const type = removeBtn.getAttribute('data-filter-type');
                        const val = removeBtn.getAttribute('data-filter-value');

                        if (type === 'brand') {
                            const cb = document.querySelector(`.brand-checkbox[value="${val}"]`);
                            if (cb) cb.checked = false;
                        } else if (type === 'attr') {
                            const cb = document.querySelector(`.attr-checkbox[value="${val}"], .size-attr-checkbox[value="${val}"]`);
                            if (cb) {
                                cb.checked = false;
                                const parent = cb.closest('.size-item, .color-item');
                                if (parent) parent.classList.remove('active');
                            }
                        } else if (type === 'price') {
                            const slider = document.getElementById('price-value-range');
                            if (slider && slider.noUiSlider) {
                                const minLimit = parseInt(slider.getAttribute('data-min')) || 0;
                                const maxLimit = parseInt(slider.getAttribute('data-max')) || 500;
                                slider.noUiSlider.set([minLimit, maxLimit]);
                            }
                            const minInput = document.getElementById('input-min-price');
                            const maxInput = document.getElementById('input-max-price');
                            if (minInput) minInput.value = '';
                            if (maxInput) maxInput.value = '';
                        } else if (type === 'on_sale') {
                            const saleLink = document.getElementById('saleToggleLink');
                            if (saleLink) {
                                saleLink.classList.remove('is-sale-active', 'text-danger', 'fw-bold');
                                saleLink.classList.add('text-secondary');
                            }
                        } else if (type === 'search') {
                            const searchInput = document.getElementById('filter-search-input');
                            if (searchInput) searchInput.value = '';
                        }

                        triggerAjaxFilter();
                    } else if (clearAllBtn) {
                        e.preventDefault();
                        clearAllFilters();
                    }
                });
            }

            // Initialize Infinite Scroll Observer (like home page)
            const sentinel = document.getElementById('shopInfiniteScrollSentinel');
            if (sentinel) {
                if ('IntersectionObserver' in window) {
                    scrollObserver = new IntersectionObserver(function(entries) {
                        entries.forEach(function(entry) {
                            if (entry.isIntersecting) {
                                loadMoreScrollProducts();
                            }
                        });
                    }, { rootMargin: '350px' });

                    if (hasMoreProducts) {
                        scrollObserver.observe(sentinel);
                    }
                } else {
                    window.addEventListener('scroll', function() {
                        if (isLoadingScroll || isFilterRequestInProgress || !hasMoreProducts) return;
                        const rect = sentinel.getBoundingClientRect();
                        if (rect.top <= window.innerHeight + 350) {
                            loadMoreScrollProducts();
                        }
                    });
                }
            }

            // Global click for empty state clear all button
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-clear-all-ajax') && !e.target.closest('#metaFilterShop')) {
                    e.preventDefault();
                    clearAllFilters();
                }
            });

            // Browser back/forward navigation
            window.addEventListener('popstate', function() {
                location.reload();
            });
        });
        </script>
