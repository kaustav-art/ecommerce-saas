<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold m-0"><span class="text-muted fw-light">Catalog /</span> Categories & Subcategories</h4>
      <p class="text-muted small m-0">Supports multi-level category hierarchy: Category &gt; Subcategory &gt; Sub-subcategory (a &gt; b &gt; c &gt; d)</p>
    </div>
    <a href="<?= base_url('../website/shop'); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
      <i class="fa-solid fa-shop me-1"></i> View Shop Catalog
    </a>
  </div>

  <div class="row">
    <!-- Add / Edit Form (Fixed / Sticky) -->
    <div class="col-lg-4 col-md-5">
      <div class="card mb-4 category-sticky-card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0" id="form-title">Add New Category</h5>
          <span class="badge bg-label-info">Multilevel Tree</span>
        </div>
        <div class="card-body">
          <form action="<?= site_url('categories'); ?>" method="POST" enctype="multipart/form-data" id="categoryForm">
            <input type="hidden" name="id" id="cat_id" value="" />
            <input type="hidden" name="existing_image" id="cat_existing_image" value="" />
            <input type="hidden" name="remove_image" id="cat_remove_image" value="0" />

            <div class="mb-3">
              <label class="form-label" for="cat_parent">Parent Category</label>
              <select class="form-select" id="cat_parent" name="parent_id">
                <option value="0">None (Top-Level Main Category)</option>
                <?php if (!empty($parent_options)): ?>
                  <?php foreach ($parent_options as $p): ?>
                    <option value="<?= $p['id']; ?>"><?= html_escape($p['full_path']); ?></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
              <small class="text-muted">Select a parent to create a subcategory or sub-subcategory (a &gt; b &gt; c).</small>
            </div>

            <div class="mb-3">
              <label class="form-label" for="cat_name">Category Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="cat_name" name="name" required placeholder="e.g. Dresses or Smartphones" />
            </div>

            <div class="mb-3">
              <label class="form-label" for="cat_desc">Description</label>
              <textarea class="form-control" id="cat_desc" name="description" rows="3" placeholder="Category summary..."></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label d-block">Category Image</label>
              <div class="d-flex align-items-center gap-3 p-2 border rounded bg-light">
                <!-- Preview Thumbnail / Placeholder -->
                <div class="position-relative flex-shrink-0" id="cat_preview_wrapper" style="width: 64px; height: 64px; display: none;">
                  <img
                    id="cat_preview_img"
                    src=""
                    class="rounded-circle border shadow-sm w-100 h-100"
                    style="object-fit: cover; aspect-ratio: 1/1;"
                    alt="Category Preview"
                    onerror="this.style.display='none'; if(document.getElementById('cat_preview_wrapper')) document.getElementById('cat_preview_wrapper').style.display='none';"
                  />
                  <div
                    id="cat_preview_placeholder"
                    class="rounded-circle border border-dashed d-flex align-items-center justify-content-center bg-white text-muted shadow-sm w-100 h-100"
                    style="font-size: 22px; display: none !important;"
                  >
                    <i class="fa-solid fa-image"></i>
                  </div>
                </div>

                <!-- File Input & Controls -->
                <div class="flex-grow-1">
                  <input
                    type="file"
                    class="form-control form-control-sm"
                    id="cat_image_file"
                    name="image_file"
                    accept="image/*"
                    onchange="previewCatFile(this)"
                  />
                  <div class="d-flex justify-content-between align-items-center mt-1">
                    <small class="text-muted" style="font-size: 11px;">JPG, PNG, WEBP, SVG (Max 5MB)</small>
                    <button
                      type="button"
                      class="btn btn-link btn-xs text-danger p-0 text-decoration-none"
                      id="cat_clear_img_btn"
                      style="display: none; font-size: 11px;"
                      onclick="clearCatImage()"
                    >
                      <i class="fa-solid fa-xmark me-1"></i>Remove
                    </button>
                  </div>
                </div>
              </div>
              <small class="text-muted d-block mt-1">Image will always be displayed as a perfect circle on the website.</small>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label" for="cat_sort">Sort Order</label>
                <input type="number" class="form-control" id="cat_sort" name="sort_order" value="0" />
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label" for="cat_status">Status</label>
                <select class="form-select" id="cat_status" name="status">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>

            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="cat_featured" name="is_featured" value="1" />
              <label class="form-check-label" for="cat_featured">Feature on Home Page & Shop Top</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-2">Save Category</button>
            <button type="button" class="btn btn-outline-secondary w-100" onclick="resetForm()">Reset Form</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Category Cards List (Scrollable Infinite Scroll Container) -->
    <div class="col-lg-8 col-md-7">
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
          <h5 class="mb-0 fw-bold">All Categories</h5>
          <span class="text-muted small">Showing <span id="displayedCount"><?= count($categories); ?></span> of <span id="totalCategoriesCount"><?= $total_categories; ?></span> categories</span>
        </div>
        <div class="d-flex align-items-center gap-2">
          <div class="input-group input-group-merge input-group-sm" style="width: 240px;">
            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input type="text" id="categorySearchInput" class="form-control" placeholder="Search categories..." />
          </div>
        </div>
      </div>

      <!-- Categories Card Grid Container -->
      <div id="categoryCardsContainer" class="row g-3">
        <?php if (!empty($categories)): ?>
          <?php foreach ($categories as $cat): ?>
            <div class="col-12 col-sm-6 category-card-col" id="cat-card-<?= $cat['id']; ?>">
              <div class="card h-100 border shadow-none bg-white category-item-card transition-all">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                  <!-- Header: Avatar + Info -->
                  <div>
                    <div class="d-flex align-items-start gap-2 mb-2">
                      <div class="flex-shrink-0">
                        <?php if (!empty($cat['image'])): ?>
                          <img src="<?= base_url('../website/assets/images/' . $cat['image']); ?>" class="rounded-circle border shadow-sm" style="width: 42px; height: 42px; object-fit: cover; aspect-ratio: 1/1;" onerror="this.style.display='none'; this.nextElementSibling.style.setProperty('display', 'flex', 'important');" />
                          <div class="rounded-circle border bg-light d-flex align-items-center justify-content-center text-muted shadow-sm" style="width: 42px; height: 42px; font-size: 15px; display: none !important;">
                            <i class="fa-solid fa-folder"></i>
                          </div>
                        <?php else: ?>
                          <div class="rounded-circle border bg-light d-flex align-items-center justify-content-center text-muted shadow-sm" style="width: 42px; height: 42px; font-size: 15px;">
                            <i class="fa-solid fa-folder"></i>
                          </div>
                        <?php endif; ?>
                      </div>

                      <div class="flex-grow-1 overflow-hidden">
                        <h6 class="mb-1 text-truncate fw-bold text-dark" title="<?= html_escape($cat['name']); ?>">
                          <?= html_escape($cat['name']); ?>
                        </h6>
                        <div class="text-muted small text-truncate" style="font-size: 11px;">
                          <code><?= html_escape($cat['slug']); ?></code>
                        </div>
                      </div>
                    </div>

                    <!-- Breadcrumbs / Hierarchy -->
                    <div class="mb-2">
                      <?php if (!empty($cat['breadcrumb_path']) && $cat['breadcrumb_path'] !== $cat['name']): ?>
                        <div class="text-muted small text-truncate" title="<?= html_escape($cat['breadcrumb_path']); ?>" style="font-size: 11px;">
                          <i class="fa-solid fa-sitemap me-1 text-primary"></i><?= html_escape($cat['breadcrumb_path']); ?>
                        </div>
                      <?php else: ?>
                        <span class="badge bg-label-info" style="font-size: 10px;">Top-Level Main</span>
                      <?php endif; ?>
                    </div>

                    <?php if (!empty($cat['description'])): ?>
                      <p class="text-muted small mb-2 text-truncate" style="font-size: 11.5px;" title="<?= html_escape($cat['description']); ?>">
                        <?= html_escape($cat['description']); ?>
                      </p>
                    <?php endif; ?>
                  </div>

                  <!-- Footer / Badges & Actions -->
                  <div class="border-top pt-2 mt-2 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-1 flex-wrap">
                      <span class="badge bg-label-primary" style="font-size: 10px;"><?= (int)$cat['product_count']; ?> Items</span>
                      <?= ($cat['status'] === 'active') ? '<span class="badge bg-label-success" style="font-size: 10px;">Active</span>' : '<span class="badge bg-label-secondary" style="font-size: 10px;">Inactive</span>'; ?>
                      <?php if ($cat['is_featured'] == 1): ?>
                        <span class="badge bg-label-warning" style="font-size: 10px;"><i class="fa-solid fa-star me-1"></i>Featured</span>
                      <?php endif; ?>
                    </div>

                    <div class="d-flex align-items-center gap-1">
                      <button
                        type="button"
                        class="btn btn-xs btn-outline-primary"
                        onclick="editCategory(<?= htmlspecialchars(json_encode($cat), ENT_QUOTES, 'UTF-8'); ?>)"
                        title="Edit Category">
                        <i class="fa-solid fa-pen-to-square"></i>
                      </button>
                      <a
                        href="<?= site_url('categories/delete/' . $cat['id']); ?>"
                        class="btn btn-xs btn-outline-danger"
                        onclick="return confirm('Delete this category? Subcategories will be moved to root.');"
                        title="Delete Category">
                        <i class="fa-solid fa-trash-can"></i>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Infinite Scroll Sentinel & Loading Indicator -->
      <div id="infiniteScrollSentinel" class="text-center py-4 my-2">
        <div id="loadingSpinner" style="display: none;">
          <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
          <span class="text-muted small">Loading more categories...</span>
        </div>
        <div id="endOfCategories" style="<?= $has_more ? 'display: none;' : ''; ?>" class="text-muted small">
          <i class="fa-solid fa-check-circle text-success me-1"></i> All categories loaded (<span id="loadedCount"><?= count($categories); ?></span> total)
        </div>
        <div id="noCategoriesFound" style="display: none;" class="text-muted py-5">
          <i class="fa-solid fa-magnifying-glass fs-2 text-muted mb-2 d-block"></i>
          <p class="mb-0">No categories found matching your search.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
/* Sticky Add / Edit Form */
@media (min-width: 768px) {
  .category-sticky-card {
    position: sticky;
    top: 85px;
    z-index: 10;
  }
  .category-sticky-card .card-body {
    max-height: calc(100vh - 165px);
    overflow-y: auto;
  }
  .category-sticky-card .card-body::-webkit-scrollbar {
    width: 4px;
  }
  .category-sticky-card .card-body::-webkit-scrollbar-thumb {
    background: #dcdcdc;
    border-radius: 4px;
  }
}

/* Category Card Styling */
.category-item-card {
  border: 1px solid #e7e7e7 !important;
  border-radius: 8px;
  transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
}
.category-item-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08) !important;
  border-color: #d0d5dd !important;
}

.category-card-col {
  animation: fadeInCard 0.25s ease-in-out;
}

@keyframes fadeInCard {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
var imageBasePath = '<?= base_url("../website/assets/images/"); ?>/';
var deleteBaseUrl = '<?= site_url("categories/delete"); ?>';
var loadMoreUrl = '<?= site_url("categories/load_more"); ?>';

var currentOffset = <?= count($categories); ?>;
var currentLimit = 10;
var isLoading = false;
var hasMore = <?= $has_more ? 'true' : 'false'; ?>;
var currentSearch = '';
var searchTimer = null;

// Infinite scroll via IntersectionObserver
var sentinel = document.getElementById('infiniteScrollSentinel');
var scrollObserver = new IntersectionObserver(function(entries) {
  if (entries[0].isIntersecting && hasMore && !isLoading) {
    loadNextCategories();
  }
}, { rootMargin: '300px' });

if (sentinel) {
  scrollObserver.observe(sentinel);
}

// Window scroll listener to ensure smooth detection
window.addEventListener('scroll', function() {
  if (hasMore && !isLoading && sentinel) {
    var rect = sentinel.getBoundingClientRect();
    if (rect.top <= window.innerHeight + 300) {
      loadNextCategories();
    }
  }
});

function loadNextCategories() {
  if (isLoading || !hasMore) return;
  isLoading = true;
  document.getElementById('loadingSpinner').style.display = 'block';

  var url = loadMoreUrl + '?offset=' + currentOffset + '&limit=' + currentLimit + '&search=' + encodeURIComponent(currentSearch);

  fetch(url)
    .then(function(res) { return res.json(); })
    .then(function(data) {
      isLoading = false;
      document.getElementById('loadingSpinner').style.display = 'none';

      if (data.categories && data.categories.length > 0) {
        appendCategoryCards(data.categories);
        currentOffset += data.categories.length;
        hasMore = data.has_more;

        document.getElementById('displayedCount').innerText = document.querySelectorAll('.category-card-col').length;
        document.getElementById('totalCategoriesCount').innerText = data.total;
        document.getElementById('loadedCount').innerText = document.querySelectorAll('.category-card-col').length;
      } else {
        hasMore = false;
      }

      if (!hasMore) {
        var cardCount = document.querySelectorAll('.category-card-col').length;
        if (cardCount === 0) {
          document.getElementById('noCategoriesFound').style.display = 'block';
          document.getElementById('endOfCategories').style.display = 'none';
        } else {
          document.getElementById('endOfCategories').style.display = 'block';
          document.getElementById('noCategoriesFound').style.display = 'none';
        }
      }
    })
    .catch(function(err) {
      isLoading = false;
      document.getElementById('loadingSpinner').style.display = 'none';
      console.error('Error fetching categories:', err);
    });
}

function appendCategoryCards(items) {
  var container = document.getElementById('categoryCardsContainer');
  items.forEach(function(cat) {
    if (document.getElementById('cat-card-' + cat.id)) return;

    var col = document.createElement('div');
    col.className = 'col-12 col-sm-6 category-card-col';
    col.id = 'cat-card-' + cat.id;

    var imageHtml = '';
    if (cat.image && cat.image.trim() !== '') {
      var imgSrc = cat.image.startsWith('http') ? cat.image : imageBasePath + cat.image.replace(/^\/+/, '');
      imageHtml = '<img src="' + escapeHtml(imgSrc) + '" class="rounded-circle border shadow-sm" style="width: 42px; height: 42px; object-fit: cover; aspect-ratio: 1/1;" onerror="this.style.display=\'none\'; this.nextElementSibling.style.setProperty(\'display\', \'flex\', \'important\');" />' +
                  '<div class="rounded-circle border bg-light d-flex align-items-center justify-content-center text-muted shadow-sm" style="width: 42px; height: 42px; font-size: 15px; display: none !important;"><i class="fa-solid fa-folder"></i></div>';
    } else {
      imageHtml = '<div class="rounded-circle border bg-light d-flex align-items-center justify-content-center text-muted shadow-sm" style="width: 42px; height: 42px; font-size: 15px;"><i class="fa-solid fa-folder"></i></div>';
    }

    var hierarchyHtml = '';
    if (cat.breadcrumb_path && cat.breadcrumb_path !== cat.name) {
      hierarchyHtml = '<div class="text-muted small text-truncate" title="' + escapeHtml(cat.breadcrumb_path) + '" style="font-size: 11px;"><i class="fa-solid fa-sitemap me-1 text-primary"></i>' + escapeHtml(cat.breadcrumb_path) + '</div>';
    } else {
      hierarchyHtml = '<span class="badge bg-label-info" style="font-size: 10px;">Top-Level Main</span>';
    }

    var descHtml = '';
    if (cat.description && cat.description.trim() !== '') {
      descHtml = '<p class="text-muted small mb-2 text-truncate" style="font-size: 11.5px;" title="' + escapeHtml(cat.description) + '">' + escapeHtml(cat.description) + '</p>';
    }

    var statusBadge = (cat.status === 'active')
      ? '<span class="badge bg-label-success" style="font-size: 10px;">Active</span>'
      : '<span class="badge bg-label-secondary" style="font-size: 10px;">Inactive</span>';

    var featuredBadge = (cat.is_featured == 1)
      ? '<span class="badge bg-label-warning" style="font-size: 10px;"><i class="fa-solid fa-star me-1"></i>Featured</span>'
      : '';

    var jsonCat = JSON.stringify(cat).replace(/"/g, '&quot;');

    col.innerHTML =
      '<div class="card h-100 border shadow-none bg-white category-item-card transition-all">' +
        '<div class="card-body p-3 d-flex flex-column justify-content-between">' +
          '<div>' +
            '<div class="d-flex align-items-start gap-2 mb-2">' +
              '<div class="flex-shrink-0">' + imageHtml + '</div>' +
              '<div class="flex-grow-1 overflow-hidden">' +
                '<h6 class="mb-1 text-truncate fw-bold text-dark" title="' + escapeHtml(cat.name) + '">' + escapeHtml(cat.name) + '</h6>' +
                '<div class="text-muted small text-truncate" style="font-size: 11px;"><code>' + escapeHtml(cat.slug) + '</code></div>' +
              '</div>' +
            '</div>' +
            '<div class="mb-2">' + hierarchyHtml + '</div>' +
            descHtml +
          '</div>' +
          '<div class="border-top pt-2 mt-2 d-flex align-items-center justify-content-between">' +
            '<div class="d-flex align-items-center gap-1 flex-wrap">' +
              '<span class="badge bg-label-primary" style="font-size: 10px;">' + (cat.product_count || 0) + ' Items</span>' +
              statusBadge +
              featuredBadge +
            '</div>' +
            '<div class="d-flex align-items-center gap-1">' +
              '<button type="button" class="btn btn-xs btn-outline-primary" onclick="editCategory(' + jsonCat + ')" title="Edit Category">' +
                '<i class="fa-solid fa-pen-to-square"></i>' +
              '</button>' +
              '<a href="' + deleteBaseUrl + '/' + cat.id + '" class="btn btn-xs btn-outline-danger" onclick="return confirm(\'Delete this category? Subcategories will be moved to root.\');" title="Delete Category">' +
                '<i class="fa-solid fa-trash-can"></i>' +
              '</a>' +
            '</div>' +
          '</div>' +
        '</div>' +
      '</div>';

    container.appendChild(col);
  });
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

// Live Search with Debounce
var searchInput = document.getElementById('categorySearchInput');
if (searchInput) {
  searchInput.addEventListener('input', function() {
    clearTimeout(searchTimer);
    var q = this.value.trim();
    searchTimer = setTimeout(function() {
      currentSearch = q;
      currentOffset = 0;
      hasMore = true;
      document.getElementById('categoryCardsContainer').innerHTML = '';
      document.getElementById('noCategoriesFound').style.display = 'none';
      document.getElementById('endOfCategories').style.display = 'none';
      loadNextCategories();
    }, 300);
  });
}

function previewCatFile(input) {
  var previewWrapper = document.getElementById('cat_preview_wrapper');
  var previewImg = document.getElementById('cat_preview_img');
  var placeholder = document.getElementById('cat_preview_placeholder');
  var clearBtn = document.getElementById('cat_clear_img_btn');
  document.getElementById('cat_remove_image').value = '0';

  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      previewImg.src = e.target.result;
      previewImg.style.display = 'block';
      if (previewWrapper) previewWrapper.style.display = 'block';
      if (placeholder) placeholder.style.setProperty('display', 'none', 'important');
      clearBtn.style.display = 'inline-block';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function clearCatImage() {
  var fileInput = document.getElementById('cat_image_file');
  fileInput.value = '';
  document.getElementById('cat_existing_image').value = '';
  document.getElementById('cat_remove_image').value = '1';

  var previewWrapper = document.getElementById('cat_preview_wrapper');
  var previewImg = document.getElementById('cat_preview_img');
  var placeholder = document.getElementById('cat_preview_placeholder');
  var clearBtn = document.getElementById('cat_clear_img_btn');

  previewImg.src = '';
  previewImg.style.display = 'none';
  if (previewWrapper) previewWrapper.style.display = 'none';
  if (placeholder) placeholder.style.setProperty('display', 'none', 'important');
  clearBtn.style.display = 'none';
}

function editCategory(cat) {
  document.getElementById('form-title').innerText = 'Edit Category: ' + cat.name;
  document.getElementById('cat_id').value = cat.id;
  document.getElementById('cat_name').value = cat.name;
  document.getElementById('cat_parent').value = cat.parent_id || 0;
  document.getElementById('cat_desc').value = cat.description || '';
  document.getElementById('cat_sort').value = cat.sort_order || 0;
  document.getElementById('cat_status').value = cat.status || 'active';
  document.getElementById('cat_featured').checked = (cat.is_featured == 1);

  // Reset file input & remove flag
  document.getElementById('cat_image_file').value = '';
  document.getElementById('cat_remove_image').value = '0';
  document.getElementById('cat_existing_image').value = cat.image || '';

  var previewWrapper = document.getElementById('cat_preview_wrapper');
  var previewImg = document.getElementById('cat_preview_img');
  var placeholder = document.getElementById('cat_preview_placeholder');
  var clearBtn = document.getElementById('cat_clear_img_btn');

  if (cat.image && cat.image.trim() !== '') {
    var src = cat.image.startsWith('http') ? cat.image : imageBasePath + cat.image.replace(/^\/+/, '');
    previewImg.src = src;
    previewImg.style.display = 'block';
    if (previewWrapper) previewWrapper.style.display = 'block';
    if (placeholder) placeholder.style.setProperty('display', 'none', 'important');
    clearBtn.style.display = 'inline-block';
  } else {
    previewImg.src = '';
    previewImg.style.display = 'none';
    if (previewWrapper) previewWrapper.style.display = 'none';
    if (placeholder) placeholder.style.setProperty('display', 'none', 'important');
    clearBtn.style.display = 'none';
  }

  // Smooth scroll form card into view if needed
  var formCard = document.querySelector('.category-sticky-card');
  if (formCard) {
    formCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
}

function resetForm() {
  document.getElementById('form-title').innerText = 'Add New Category';
  document.getElementById('cat_id').value = '';
  document.getElementById('cat_name').value = '';
  document.getElementById('cat_parent').value = '0';
  document.getElementById('cat_desc').value = '';
  document.getElementById('cat_sort').value = 0;
  document.getElementById('cat_status').value = 'active';
  document.getElementById('cat_featured').checked = false;

  clearCatImage();
  document.getElementById('cat_remove_image').value = '0';
}
</script>
