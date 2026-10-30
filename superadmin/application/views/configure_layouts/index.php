<?php
$curr_tab = isset($active_tab) && in_array($active_tab, array('template1', 'template2', 'template3'), TRUE) ? $active_tab : 'template1';
$curr_sec = isset($active_section) && in_array($active_section, array('hero', 'categories', 'products'), TRUE) ? $active_section : 'hero';

$templates_meta = array(
    'template1' => array(
        'name'        => 'Template 1: Fashion Storefront',
        'short_name'  => 'Template 1 (Fashion)',
        'preview_key' => 'fashion',
        'icon'        => 'fa-solid fa-shirt text-info',
        'badge_class' => 'bg-info text-white',
        'folder'      => 'website/application/views/template1/',
        'desc'        => 'Modave Luxury Fashion Storefront — Hero Slider, Circular Category Showcase & 4-Column Fashion Product Grid.',
    ),
    'template2' => array(
        'name'        => 'Template 2: Electronics Storefront',
        'short_name'  => 'Template 2 (Electronics)',
        'preview_key' => 'electronics',
        'icon'        => 'fa-solid fa-microchip text-primary',
        'badge_class' => 'bg-primary text-white',
        'folder'      => 'website/application/views/template2/',
        'desc'        => 'Bazaro High-Tech Electronics Storefront — Fullscreen Tech Slider, Department Carousel & Deal Showcases.',
    ),
    'template3' => array(
        'name'        => 'Template 3: Grocery Storefront',
        'short_name'  => 'Template 3 (Grocery)',
        'preview_key' => 'grocery',
        'icon'        => 'fa-solid fa-basket-shopping text-success',
        'badge_class' => 'bg-success text-white',
        'folder'      => 'website/application/views/template3/',
        'desc'        => 'Bazaro Organic Grocery Supermarket — Farm-Fresh Hero Banner, Category Aisles & Unit-Pack Product Cards.',
    ),
);

$current_meta = $templates_meta[$curr_tab];
$current_data = isset($tpl_data[$curr_tab]) ? $tpl_data[$curr_tab] : array(
    'hero_banners'       => array(),
    'categories'         => array(),
    'products'           => array(),
    'categories_tagline' => 'CURATED COLLECTIONS',
    'categories_title'   => 'Shop by Category',
    'products_tagline'   => 'TRENDING NOW',
    'products_title'     => 'Featured Arrivals',
);
$preview_url = tenant_site_url('home?preview_tpl=' . $current_meta['preview_key']);
?>

<!-- Page Header -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-white">
            <i class="fa-solid fa-palette text-warning me-2"></i> Storefront Template Customizer
        </h4>
        <p class="text-muted small mb-0">
            Manage static showcase <strong>Hero Banners</strong>, <strong>Categories</strong>, and <strong>Products</strong> for each template directly from Super Admin.
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="<?= $preview_url ?>" target="_blank" class="btn btn-warning fw-bold shadow-sm">
            <i class="fa-solid fa-up-right-from-square me-1"></i> Live Preview: <?= htmlspecialchars($current_meta['short_name']) ?>
        </a>
    </div>
</div>

<!-- Template Selector Tabs (Template 1, Template 2, Template 3) -->
<div class="row g-3 mb-4">
    <?php foreach ($templates_meta as $tk => $tm): 
        $is_act = ($curr_tab === $tk);
        $t_cnt_h = isset($tpl_data[$tk]['hero_banners']) ? count($tpl_data[$tk]['hero_banners']) : 0;
        $t_cnt_c = isset($tpl_data[$tk]['categories']) ? count($tpl_data[$tk]['categories']) : 0;
        $t_cnt_p = isset($tpl_data[$tk]['products']) ? count($tpl_data[$tk]['products']) : 0;
    ?>
        <div class="col-md-4">
            <a href="<?= superadmin_url('layouts?tab=' . $tk . '&section=' . $curr_sec) ?>" class="text-decoration-none d-block h-100">
                <div class="sa-card p-3 h-100 transition-all" style="border: 2px solid <?= $is_act ? '#c29958' : 'rgba(255,255,255,0.08)' ?>; background: <?= $is_act ? 'rgba(194, 153, 88, 0.10)' : '#111827' ?>; border-radius: 14px;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="<?= $tm['icon'] ?> fs-5"></i>
                            <h6 class="fw-bold text-white mb-0"><?= htmlspecialchars($tm['short_name']) ?></h6>
                        </div>
                        <?php if ($is_act): ?>
                            <span class="badge bg-warning text-dark fw-bold"><i class="fa-solid fa-check me-1"></i>Editing</span>
                        <?php else: ?>
                            <span class="badge bg-dark border border-secondary text-muted">Select</span>
                        <?php endif; ?>
                    </div>
                    <div class="small text-muted mb-2 font-monospace" style="font-size: 11px;">
                        <i class="fa-regular fa-folder-open me-1 text-warning"></i> <?= htmlspecialchars($tm['folder']) ?>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-2 pt-2 border-top border-secondary border-opacity-25 small">
                        <span class="badge bg-dark border border-secondary text-light"><?= $t_cnt_h ?> Banners</span>
                        <span class="badge bg-dark border border-secondary text-light"><?= $t_cnt_c ?> Categories</span>
                        <span class="badge bg-dark border border-secondary text-light"><?= $t_cnt_p ?> Products</span>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<!-- Active Template Banner & Section Sub-Navigation -->
<div class="sa-card p-4 mb-4" style="border-radius: 16px;">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pb-3 mb-4 border-bottom border-secondary border-opacity-25">
        <div>
            <span class="badge <?= $current_meta['badge_class'] ?> mb-1"><?= strtoupper($curr_tab) ?></span>
            <h5 class="fw-bold text-white mb-1"><?= htmlspecialchars($current_meta['name']) ?></h5>
            <p class="text-muted small mb-0"><?= htmlspecialchars($current_meta['desc']) ?></p>
        </div>

        <!-- Section Pills: Hero Banners | Categories | Products -->
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= superadmin_url('layouts?tab=' . $curr_tab . '&section=hero') ?>" 
               class="btn <?= $curr_sec === 'hero' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary text-light' ?> px-3">
                <i class="fa-solid fa-panorama me-1"></i> 1. Hero Banners (<?= count($current_data['hero_banners']) ?>)
            </a>
            <a href="<?= superadmin_url('layouts?tab=' . $curr_tab . '&section=categories') ?>" 
               class="btn <?= $curr_sec === 'categories' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary text-light' ?> px-3">
                <i class="fa-solid fa-layer-group me-1"></i> 2. Categories (<?= count($current_data['categories']) ?>)
            </a>
            <a href="<?= superadmin_url('layouts?tab=' . $curr_tab . '&section=products') ?>" 
               class="btn <?= $curr_sec === 'products' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary text-light' ?> px-3">
                <i class="fa-solid fa-boxes-stacked me-1"></i> 3. Products (<?= count($current_data['products']) ?>)
            </a>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- SECTION 1: HERO BANNERS MANAGEMENT -->
    <!-- =================================================================== -->
    <?php if ($curr_sec === 'hero'): ?>
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h6 class="fw-bold text-warning mb-0"><i class="fa-solid fa-images me-2"></i>Hero Banner Slides</h6>
                <small class="text-muted">Manage the main top slideshow banners displayed on <code>?preview_tpl=<?= $current_meta['preview_key'] ?></code></small>
            </div>
            <button type="button" class="btn btn-warning fw-bold btn-sm px-3" onclick="openHeroModal()">
                <i class="fa-solid fa-plus me-1"></i> Add Hero Slide
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th style="width: 70px;">Order</th>
                        <th style="width: 140px;">Banner Image</th>
                        <th>Badge &amp; Title</th>
                        <th>CTA Button</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($current_data['hero_banners'])): ?>
                        <?php foreach ($current_data['hero_banners'] as $hb): 
                            $hb_img = resolve_tpl_image_url($hb->image);
                        ?>
                            <tr>
                                <td class="fw-bold font-monospace text-warning">#<?= (int)$hb->sort_order ?></td>
                                <td>
                                    <img src="<?= $hb_img ?>" alt="Slide" class="rounded border border-secondary" style="width: 115px; height: 60px; object-fit: cover;">
                                </td>
                                <td>
                                    <?php if (!empty($hb->badge)): ?>
                                        <span class="badge bg-dark border border-warning text-warning mb-1"><?= htmlspecialchars($hb->badge) ?></span>
                                    <?php endif; ?>
                                    <div class="fw-bold text-white"><?= htmlspecialchars($hb->title) ?></div>
                                    <?php if (!empty($hb->description)): ?>
                                        <div class="small text-muted text-truncate" style="max-width: 340px;"><?= htmlspecialchars($hb->description) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($hb->button_text ?: 'Shop Now') ?></span>
                                    <div class="small text-muted font-monospace mt-1"><?= htmlspecialchars($hb->button_url ?: 'shop') ?></div>
                                </td>
                                <td><?= status_badge($hb->status) ?></td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-info me-1" 
                                            onclick='editHeroSlide(<?= json_encode($hb, JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($hb_img) ?>)'>
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                    <form method="post" action="<?= superadmin_url('layouts?tab=' . $curr_tab . '&section=hero') ?>" class="d-inline" onsubmit="return confirm('Delete this Hero Banner slide?');">
                                        <input type="hidden" name="action" value="delete_hero_slide">
                                        <input type="hidden" name="target_tpl" value="<?= $curr_tab ?>">
                                        <input type="hidden" name="slide_id" value="<?= (int)$hb->id ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No Hero Banner slides found for this template. Click "Add Hero Slide" above.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- =================================================================== -->
    <!-- SECTION 2: CATEGORIES MANAGEMENT -->
    <!-- =================================================================== -->
    <?php if ($curr_sec === 'categories'): ?>
        <!-- Section Heading Form -->
        <form method="post" action="<?= superadmin_url('layouts?tab=' . $curr_tab . '&section=categories') ?>" class="p-3 rounded-3 mb-4" style="background: rgba(15, 23, 42, 0.75); border: 1px solid rgba(255,255,255,0.08);">
            <input type="hidden" name="action" value="save_categories_headers">
            <input type="hidden" name="target_tpl" value="<?= $curr_tab ?>">
            <input type="hidden" name="active_section" value="categories">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Categories Section Tagline / Subtitle</label>
                    <input type="text" name="categories_tagline" class="form-control form-control-sm bg-dark text-white border-secondary" value="<?= htmlspecialchars($current_data['categories_tagline']) ?>">
                </div>
                <div class="col-md-5">
                    <label class="form-label small text-muted mb-1">Categories Section Main Title</label>
                    <input type="text" name="categories_title" class="form-control form-control-sm bg-dark text-white border-secondary" value="<?= htmlspecialchars($current_data['categories_title']) ?>">
                </div>
                <div class="col-md-3 text-end">
                    <button type="submit" class="btn btn-sm btn-outline-warning w-100 fw-bold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Section Heading
                    </button>
                </div>
            </div>
        </form>

        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h6 class="fw-bold text-warning mb-0"><i class="fa-solid fa-layer-group me-2"></i>Template Categories</h6>
                <small class="text-muted">Manage the categories displayed in the navigation bar &amp; homepage category showcase for <code>?preview_tpl=<?= $current_meta['preview_key'] ?></code></small>
            </div>
            <button type="button" class="btn btn-warning fw-bold btn-sm px-3" onclick="openCategoryModal()">
                <i class="fa-solid fa-plus me-1"></i> Add Category
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th style="width: 70px;">Order</th>
                        <th style="width: 90px;">Image</th>
                        <th>Category Name &amp; Slug</th>
                        <th>Item Count / Subtitle</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($current_data['categories'])): ?>
                        <?php foreach ($current_data['categories'] as $cat): 
                            $c_img = resolve_tpl_image_url($cat->image);
                        ?>
                            <tr>
                                <td class="fw-bold font-monospace text-warning">#<?= (int)$cat->sort_order ?></td>
                                <td>
                                    <img src="<?= $c_img ?>" alt="Category" class="rounded-circle border border-secondary" style="width: 54px; height: 54px; object-fit: cover;">
                                </td>
                                <td>
                                    <div class="fw-bold text-white"><?= htmlspecialchars($cat->name) ?></div>
                                    <div class="small text-muted font-monospace">/shop/<?= htmlspecialchars($cat->slug) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-dark border border-secondary text-light"><?= htmlspecialchars($cat->subtitle ?: '12 Items') ?></span>
                                </td>
                                <td><?= status_badge($cat->status) ?></td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-info me-1"
                                            onclick='editCategory(<?= json_encode($cat, JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($c_img) ?>)'>
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                    <form method="post" action="<?= superadmin_url('layouts?tab=' . $curr_tab . '&section=categories') ?>" class="d-inline" onsubmit="return confirm('Delete this category?');">
                                        <input type="hidden" name="action" value="delete_category">
                                        <input type="hidden" name="target_tpl" value="<?= $curr_tab ?>">
                                        <input type="hidden" name="category_id" value="<?= (int)$cat->id ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No Categories found for this template. Click "Add Category" above.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- =================================================================== -->
    <!-- SECTION 3: PRODUCTS MANAGEMENT -->
    <!-- =================================================================== -->
    <?php if ($curr_sec === 'products'): ?>
        <!-- Section Heading Form -->
        <form method="post" action="<?= superadmin_url('layouts?tab=' . $curr_tab . '&section=products') ?>" class="p-3 rounded-3 mb-4" style="background: rgba(15, 23, 42, 0.75); border: 1px solid rgba(255,255,255,0.08);">
            <input type="hidden" name="action" value="save_products_headers">
            <input type="hidden" name="target_tpl" value="<?= $curr_tab ?>">
            <input type="hidden" name="active_section" value="products">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Products Section Tagline / Subtitle</label>
                    <input type="text" name="products_tagline" class="form-control form-control-sm bg-dark text-white border-secondary" value="<?= htmlspecialchars($current_data['products_tagline']) ?>">
                </div>
                <div class="col-md-5">
                    <label class="form-label small text-muted mb-1">Products Section Main Title</label>
                    <input type="text" name="products_title" class="form-control form-control-sm bg-dark text-white border-secondary" value="<?= htmlspecialchars($current_data['products_title']) ?>">
                </div>
                <div class="col-md-3 text-end">
                    <button type="submit" class="btn btn-sm btn-outline-warning w-100 fw-bold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Section Heading
                    </button>
                </div>
            </div>
        </form>

        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h6 class="fw-bold text-warning mb-0"><i class="fa-solid fa-boxes-stacked me-2"></i>Template Showcase Products</h6>
                <small class="text-muted">Manage the products displayed on <code>?preview_tpl=<?= $current_meta['preview_key'] ?></code></small>
            </div>
            <button type="button" class="btn btn-warning fw-bold btn-sm px-3" onclick="openProductModal()">
                <i class="fa-solid fa-plus me-1"></i> Add Product
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th style="width: 65px;">Order</th>
                        <th style="width: 85px;">Image</th>
                        <th>Product Name &amp; Category</th>
                        <th>Pricing</th>
                        <th>Badge / Unit</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($current_data['products'])): ?>
                        <?php foreach ($current_data['products'] as $prod): 
                            $p_img = resolve_tpl_image_url($prod->image);
                        ?>
                            <tr>
                                <td class="fw-bold font-monospace text-warning">#<?= (int)$prod->sort_order ?></td>
                                <td>
                                    <img src="<?= $p_img ?>" alt="Product" class="rounded border border-secondary" style="width: 56px; height: 56px; object-fit: cover;">
                                </td>
                                <td>
                                    <div class="fw-bold text-white"><?= htmlspecialchars($prod->name) ?></div>
                                    <span class="badge bg-dark border border-secondary text-info small"><?= htmlspecialchars($prod->category_name ?: 'Featured') ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($prod->sale_price) && (float)$prod->sale_price < (float)$prod->price): ?>
                                        <span class="fw-bold text-success">$<?= number_format((float)$prod->sale_price, 2) ?></span>
                                        <span class="text-muted text-decoration-line-through small ms-1">$<?= number_format((float)$prod->price, 2) ?></span>
                                    <?php else: ?>
                                        <span class="fw-bold text-white">$<?= number_format((float)$prod->price, 2) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($prod->badge_text)): ?>
                                        <span class="badge bg-danger"><?= htmlspecialchars($prod->badge_text) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($prod->unit_label)): ?>
                                        <span class="badge bg-dark border border-secondary text-light"><?= htmlspecialchars($prod->unit_label) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= status_badge($prod->status) ?></td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-info me-1"
                                            onclick='editProduct(<?= json_encode($prod, JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($p_img) ?>)'>
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                    <form method="post" action="<?= superadmin_url('layouts?tab=' . $curr_tab . '&section=products') ?>" class="d-inline" onsubmit="return confirm('Delete this template product?');">
                                        <input type="hidden" name="action" value="delete_product">
                                        <input type="hidden" name="target_tpl" value="<?= $curr_tab ?>">
                                        <input type="hidden" name="product_id" value="<?= (int)$prod->id ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No Products found for this template. Click "Add Product" above.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- =================================================================== -->
<!-- MODAL 1: ADD / EDIT HERO BANNER SLIDE -->
<!-- =================================================================== -->
<div class="modal fade" id="heroSlideModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-secondary">
            <form method="post" action="<?= superadmin_url('layouts?tab=' . $curr_tab . '&section=hero') ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="save_hero_slide">
                <input type="hidden" name="target_tpl" value="<?= $curr_tab ?>">
                <input type="hidden" name="active_section" value="hero">
                <input type="hidden" name="slide_id" id="heroSlideId" value="0">

                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-warning fw-bold" id="heroModalTitle">Add Hero Banner Slide</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Badge / Top Subtitle</label>
                            <input type="text" name="badge" id="heroBadge" class="form-control bg-dark text-white border-secondary" placeholder="e.g. SUMMER 2026 COLLECTION">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Sort Order</label>
                            <input type="number" name="sort_order" id="heroSortOrder" class="form-control bg-dark text-white border-secondary" value="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Status</label>
                            <select name="status" id="heroStatus" class="form-select bg-dark text-white border-secondary">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Main Banner Heading / Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="heroTitle" class="form-control bg-dark text-white border-secondary" required placeholder="e.g. Flash Sale Madness">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Description / Subtext</label>
                            <textarea name="description" id="heroDesc" rows="2" class="form-control bg-dark text-white border-secondary" placeholder="Optional banner subtitle or price callout"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Button Text</label>
                            <input type="text" name="button_text" id="heroBtnText" class="form-control bg-dark text-white border-secondary" value="Explore Collection">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Button Link</label>
                            <input type="text" name="button_url" id="heroBtnUrl" class="form-control bg-dark text-white border-secondary" value="shop">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Upload Banner Image</label>
                            <input type="file" name="image_file" class="form-control bg-dark text-white border-secondary" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Or Image Path / URL</label>
                            <input type="text" name="image_url" id="heroImageUrl" class="form-control bg-dark text-white border-secondary" placeholder="assets/images/slider/slider-women1.jpg">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary text-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark">Save Hero Slide</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =================================================================== -->
<!-- MODAL 2: ADD / EDIT CATEGORY -->
<!-- =================================================================== -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-secondary">
            <form method="post" action="<?= superadmin_url('layouts?tab=' . $curr_tab . '&section=categories') ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="save_category">
                <input type="hidden" name="target_tpl" value="<?= $curr_tab ?>">
                <input type="hidden" name="active_section" value="categories">
                <input type="hidden" name="category_id" id="catId" value="0">

                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-warning fw-bold" id="catModalTitle">Add Template Category</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="catName" class="form-control bg-dark text-white border-secondary" required placeholder="e.g. Women's Wear">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">URL Slug (auto-generated if blank)</label>
                            <input type="text" name="slug" id="catSlug" class="form-control bg-dark text-white border-secondary" placeholder="e.g. womens-wear">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Item Count / Subtitle</label>
                            <input type="text" name="subtitle" id="catSubtitle" class="form-control bg-dark text-white border-secondary" value="24 Items">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Sort Order</label>
                            <input type="number" name="sort_order" id="catSortOrder" class="form-control bg-dark text-white border-secondary" value="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Status</label>
                            <select name="status" id="catStatus" class="form-select bg-dark text-white border-secondary">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Upload Category Image</label>
                            <input type="file" name="image_file" class="form-control bg-dark text-white border-secondary" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Or Image Path / URL</label>
                            <input type="text" name="image_url" id="catImageUrl" class="form-control bg-dark text-white border-secondary" placeholder="assets/images/collections/collection-circle/cls-circle1.jpg">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary text-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =================================================================== -->
<!-- MODAL 3: ADD / EDIT PRODUCT -->
<!-- =================================================================== -->
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-secondary">
            <form method="post" action="<?= superadmin_url('layouts?tab=' . $curr_tab . '&section=products') ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="save_product">
                <input type="hidden" name="target_tpl" value="<?= $curr_tab ?>">
                <input type="hidden" name="active_section" value="products">
                <input type="hidden" name="product_id" id="prodId" value="0">

                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-warning fw-bold" id="prodModalTitle">Add Template Product</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="prodName" class="form-control bg-dark text-white border-secondary" required placeholder="e.g. Ribbed Modal Fitted T-Shirt">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Category Name</label>
                            <input type="text" name="category_name" id="prodCategory" class="form-control bg-dark text-white border-secondary" placeholder="e.g. Women's Wear">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Regular Price ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price" id="prodPrice" class="form-control bg-dark text-white border-secondary" required value="68.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Sale Price ($)</label>
                            <input type="number" step="0.01" name="sale_price" id="prodSalePrice" class="form-control bg-dark text-white border-secondary" placeholder="Optional">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Badge Text</label>
                            <input type="text" name="badge_text" id="prodBadge" class="form-control bg-dark text-white border-secondary" placeholder="Sale / New / Hot">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Unit Label (Grocery)</label>
                            <input type="text" name="unit_label" id="prodUnit" class="form-control bg-dark text-white border-secondary" placeholder="e.g. 500g Pack">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Upload Main Image</label>
                            <input type="file" name="image_file" class="form-control bg-dark text-white border-secondary" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Or Main Image Path / URL</label>
                            <input type="text" name="image_url" id="prodImageUrl" class="form-control bg-dark text-white border-secondary" placeholder="assets/images/products/womens/women-1.jpg">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Upload Hover Image (Optional)</label>
                            <input type="file" name="hover_image_file" class="form-control bg-dark text-white border-secondary" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Or Hover Image Path / URL</label>
                            <input type="text" name="hover_image_url" id="prodHoverUrl" class="form-control bg-dark text-white border-secondary" placeholder="assets/images/products/womens/women-2.jpg">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Short Description</label>
                            <input type="text" name="short_desc" id="prodShortDesc" class="form-control bg-dark text-white border-secondary" placeholder="Brief product summary">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Sort Order</label>
                            <input type="number" name="sort_order" id="prodSortOrder" class="form-control bg-dark text-white border-secondary" value="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Status</label>
                            <select name="status" id="prodStatus" class="form-select bg-dark text-white border-secondary">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary text-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openHeroModal() {
    document.getElementById('heroModalTitle').textContent = 'Add Hero Banner Slide';
    document.getElementById('heroSlideId').value = '0';
    document.getElementById('heroBadge').value = '';
    document.getElementById('heroTitle').value = '';
    document.getElementById('heroDesc').value = '';
    document.getElementById('heroBtnText').value = 'Explore Collection';
    document.getElementById('heroBtnUrl').value = 'shop';
    document.getElementById('heroImageUrl').value = '';
    document.getElementById('heroSortOrder').value = '1';
    document.getElementById('heroStatus').value = 'active';
    new bootstrap.Modal(document.getElementById('heroSlideModal')).show();
}

function editHeroSlide(hb) {
    document.getElementById('heroModalTitle').textContent = 'Edit Hero Banner Slide';
    document.getElementById('heroSlideId').value = hb.id;
    document.getElementById('heroBadge').value = hb.badge || '';
    document.getElementById('heroTitle').value = hb.title || '';
    document.getElementById('heroDesc').value = hb.description || '';
    document.getElementById('heroBtnText').value = hb.button_text || 'Explore Collection';
    document.getElementById('heroBtnUrl').value = hb.button_url || 'shop';
    document.getElementById('heroImageUrl').value = hb.image || '';
    document.getElementById('heroSortOrder').value = hb.sort_order || 1;
    document.getElementById('heroStatus').value = hb.status || 'active';
    new bootstrap.Modal(document.getElementById('heroSlideModal')).show();
}

function openCategoryModal() {
    document.getElementById('catModalTitle').textContent = 'Add Template Category';
    document.getElementById('catId').value = '0';
    document.getElementById('catName').value = '';
    document.getElementById('catSlug').value = '';
    document.getElementById('catSubtitle').value = '20 Items';
    document.getElementById('catImageUrl').value = '';
    document.getElementById('catSortOrder').value = '1';
    document.getElementById('catStatus').value = 'active';
    new bootstrap.Modal(document.getElementById('categoryModal')).show();
}

function editCategory(cat) {
    document.getElementById('catModalTitle').textContent = 'Edit Template Category';
    document.getElementById('catId').value = cat.id;
    document.getElementById('catName').value = cat.name || '';
    document.getElementById('catSlug').value = cat.slug || '';
    document.getElementById('catSubtitle').value = cat.subtitle || '';
    document.getElementById('catImageUrl').value = cat.image || '';
    document.getElementById('catSortOrder').value = cat.sort_order || 1;
    document.getElementById('catStatus').value = cat.status || 'active';
    new bootstrap.Modal(document.getElementById('categoryModal')).show();
}

function openProductModal() {
    document.getElementById('prodModalTitle').textContent = 'Add Template Product';
    document.getElementById('prodId').value = '0';
    document.getElementById('prodName').value = '';
    document.getElementById('prodCategory').value = '';
    document.getElementById('prodPrice').value = '68.00';
    document.getElementById('prodSalePrice').value = '';
    document.getElementById('prodBadge').value = '';
    document.getElementById('prodUnit').value = '';
    document.getElementById('prodImageUrl').value = '';
    document.getElementById('prodHoverUrl').value = '';
    document.getElementById('prodShortDesc').value = '';
    document.getElementById('prodSortOrder').value = '1';
    document.getElementById('prodStatus').value = 'active';
    new bootstrap.Modal(document.getElementById('productModal')).show();
}

function editProduct(prod) {
    document.getElementById('prodModalTitle').textContent = 'Edit Template Product';
    document.getElementById('prodId').value = prod.id;
    document.getElementById('prodName').value = prod.name || '';
    document.getElementById('prodCategory').value = prod.category_name || '';
    document.getElementById('prodPrice').value = prod.price || '0.00';
    document.getElementById('prodSalePrice').value = prod.sale_price || '';
    document.getElementById('prodBadge').value = prod.badge_text || '';
    document.getElementById('prodUnit').value = prod.unit_label || '';
    document.getElementById('prodImageUrl').value = prod.image || '';
    document.getElementById('prodHoverUrl').value = prod.hover_image || '';
    document.getElementById('prodShortDesc').value = prod.short_desc || '';
    document.getElementById('prodSortOrder').value = prod.sort_order || 1;
    document.getElementById('prodStatus').value = prod.status || 'active';
    new bootstrap.Modal(document.getElementById('productModal')).show();
}
</script>
