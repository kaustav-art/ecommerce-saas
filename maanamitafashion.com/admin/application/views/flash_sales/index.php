<style>
  @media (min-width: 768px) {
    .split-sticky-card {
      position: sticky;
      top: 85px;
      z-index: 10;
    }
  }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><span class="text-muted fw-light">Marketing /</span> Flash Sales & Campaigns</h4>
  </div>

  <div class="row align-items-start">
    <!-- Form (Sticky on Scroll) -->
    <div class="col-md-5 mb-4 split-sticky-card">
      <div class="card">
        <div class="card-header pb-2">
          <h5 class="card-title mb-0" id="fs-form-title">Create Flash Sale Event</h5>
          <small class="text-muted">Set up time-limited flash promotions with automatic scheduling.</small>
        </div>
        <div class="card-body pt-3">
          <form id="fs_main_form" action="<?= site_url('flash_sales'); ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="fs_id" value="">

            <div class="mb-3">
              <label class="form-label" for="fs_title">Campaign Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="fs_title" name="title" placeholder="e.g. Weekend Flash Sale" required>
              <small id="fs_title_notice" class="text-muted d-block mt-1" style="display:none; font-size:11px;"></small>
            </div>

            <div class="row">
              <div class="col-6 mb-3">
                <label class="form-label" for="fs_disc">Discount (%) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control" id="fs_disc" name="discount_percent" value="30.00" required>
                <small id="fs_disc_notice" class="text-muted d-block mt-1" style="display:none; font-size:11px;"></small>
              </div>
              <div class="col-6 mb-3">
                <label class="form-label">Campaign Status</label>
                <div id="fs_status_display" class="pt-1">
                  <span class="badge bg-label-info" id="fs_status_badge"><i class="fa-regular fa-clock me-1"></i>New / Scheduled</span>
                </div>
                <small class="text-muted d-block mt-1" style="font-size:11px;">Starts & ends automatically.</small>
              </div>
            </div>

            <div class="row">
              <div class="col-6 mb-3">
                <label class="form-label" for="fs_start">Start Date/Time <span class="text-danger">*</span></label>
                <input type="datetime-local" class="form-control" id="fs_start" name="start_time" value="<?= date('Y-m-d\TH:i'); ?>" required>
                <small id="fs_start_notice" class="text-muted d-block mt-1" style="display:none; font-size:11px;"></small>
              </div>
              <div class="col-6 mb-3">
                <label class="form-label" for="fs_end">End Date/Time <span class="text-danger">*</span></label>
                <input type="datetime-local" class="form-control" id="fs_end" name="end_time" value="<?= date('Y-m-d\TH:i', strtotime('+7 days')); ?>" required>
                <small id="fs_end_notice" class="text-muted d-block mt-1" style="display:none; font-size:11px;"></small>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label" for="fs_banner">Campaign Banner Image</label>
              <input type="file" class="form-control" id="fs_banner" name="banner_file" accept="image/*" onchange="previewFsBanner(this)">
              <input type="hidden" name="current_banner" id="fs_current_banner" value="">
              <small id="fs_banner_notice" class="text-muted d-block mt-1" style="font-size:11px;">Recommended: Wide banner (e.g. 1200x400px).</small>
              <div class="mt-2" id="fs_preview_box" style="display: none;">
                <small class="text-muted d-block mb-1">Preview:</small>
                <img id="fs_preview_img" src="" alt="Campaign Banner" class="rounded border" style="max-width: 100%; max-height: 100px; object-fit: cover;">
              </div>
            </div>

            <!-- Products Multi-select -->
            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label fw-bold mb-0">Select Products for Flash Sale</label>
                <small id="fs_products_notice" class="text-muted" style="display:none; font-size:11px;"></small>
              </div>
              <div class="border rounded p-2" id="fs_products_box" style="max-height: 200px; overflow-y: auto;">
                <?php if (!empty($products)): ?>
                  <?php foreach ($products as $p): ?>
                    <div class="form-check mb-2">
                      <input class="form-check-input" type="checkbox" name="product_ids[]" value="<?= $p['id']; ?>" id="p_check_<?= $p['id']; ?>">
                      <label class="form-check-label d-flex justify-content-between small" for="p_check_<?= $p['id']; ?>">
                        <span class="text-truncate" style="max-width: 220px;"><?= html_escape($p['title']); ?></span>
                        <strong><?= html_escape($currency_symbol ?? '$') . number_format($p['price'], 2); ?></strong>
                      </label>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>

            <div id="fs_btn_group">
              <button type="submit" id="fs_submit_btn" class="btn btn-primary w-100 mb-2">Save Campaign</button>
              <button type="button" id="fs_terminate_btn" class="btn btn-outline-danger w-100 mb-2" style="display:none;" onclick="terminateCurrentFs()"><i class="fa-solid fa-ban me-1"></i>Terminate Campaign</button>
              <button type="button" id="fs_reset_btn" class="btn btn-outline-secondary w-100" onclick="resetFsForm()">Reset</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Table with Infinite Scroll -->
    <div class="col-md-7">
      <div class="card mb-3">
        <div class="card-body py-3">
          <div class="row g-2 align-items-center justify-content-between">
            <div class="col-sm-6">
              <h5 class="mb-0 fw-bold">Flash Sale Campaigns</h5>
              <small class="text-muted">
                Showing <span id="displayedCount"><?= min(10, count($flash_sales ?? [])); ?></span> of <span id="totalCount"><?= count($flash_sales ?? []); ?></span> campaigns
              </small>
            </div>
            <div class="col-sm-6">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input
                  type="text"
                  id="infiniteSearchInput"
                  class="form-control"
                  placeholder="Search campaign title..."
                  autocomplete="off" />
                <button type="button" id="clearInfiniteSearch" class="btn btn-outline-secondary d-none" title="Clear">
                  <i class="fa-solid fa-xmark"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="table-responsive text-nowrap">
          <table class="table table-hover no-datatable">
            <thead>
              <tr>
                <th>Title</th>
                <th>Discount</th>
                <th>Period</th>
                <th>Products</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="infiniteTableBody">
              <?php if (!empty($flash_sales)): ?>
                <?php foreach ($flash_sales as $fs): ?>
                  <?php 
                    $st = $fs['computed_status'] ?? $fs['status'];
                  ?>
                  <tr class="infinite-item-row" data-search="<?= html_escape(strtolower($fs['title'] . ' ' . $st)); ?>">
                    <td><strong><?= html_escape($fs['title']); ?></strong></td>
                    <td><span class="badge bg-label-danger"><?= (float)$fs['discount_percent']; ?>% OFF</span></td>
                    <td>
                      <small class="d-block text-muted">From: <?= date('M d, H:i', strtotime($fs['start_time'])); ?></small>
                      <small class="d-block text-muted">To: <?= date('M d, H:i', strtotime($fs['end_time'])); ?></small>
                    </td>
                    <td><span class="badge bg-label-primary"><?= $fs['products_count']; ?> Items</span></td>
                    <td>
                      <?php if ($st === 'running'): ?>
                        <span class="badge bg-label-success"><i class="fa-solid fa-bolt me-1"></i>Running</span>
                      <?php elseif ($st === 'scheduled'): ?>
                        <span class="badge bg-label-info"><i class="fa-regular fa-clock me-1"></i>Scheduled</span>
                      <?php elseif ($st === 'terminated'): ?>
                        <span class="badge bg-label-danger"><i class="fa-solid fa-ban me-1"></i>Terminated</span>
                      <?php else: ?>
                        <span class="badge bg-label-secondary"><i class="fa-solid fa-flag-checkered me-1"></i>Ended</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="dropdown">
                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                          <i class="fa-solid fa-ellipsis-vertical fs-5 text-muted px-2 py-1"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                          <?php if ($st === 'scheduled'): ?>
                            <a class="dropdown-item" href="javascript:void(0);" onclick="editFlashSale(<?= htmlspecialchars(json_encode($fs), ENT_QUOTES, 'UTF-8'); ?>)">
                              <i class="fa-solid fa-pen-to-square me-2 text-primary"></i> Edit Campaign
                            </a>
                            <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="promptTerminateSale('<?= site_url('flash_sales/terminate/' . $fs['id']); ?>', <?= htmlspecialchars(json_encode($fs['title']), ENT_QUOTES, 'UTF-8'); ?>, 'scheduled')">
                              <i class="fa-solid fa-ban me-2"></i> Terminate Sale
                            </a>
                          <?php elseif ($st === 'running'): ?>
                            <a class="dropdown-item text-primary" href="javascript:void(0);" onclick="editFlashSale(<?= htmlspecialchars(json_encode($fs), ENT_QUOTES, 'UTF-8'); ?>)">
                              <i class="fa-solid fa-clock-rotate-left me-2"></i> Extend End Date
                            </a>
                            <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="promptTerminateSale('<?= site_url('flash_sales/terminate/' . $fs['id']); ?>', <?= htmlspecialchars(json_encode($fs['title']), ENT_QUOTES, 'UTF-8'); ?>, 'running')">
                              <i class="fa-solid fa-ban me-2"></i> Terminate Sale
                            </a>
                          <?php elseif ($st === 'ended'): ?>
                            <a class="dropdown-item text-muted" href="javascript:void(0);" onclick="editFlashSale(<?= htmlspecialchars(json_encode($fs), ENT_QUOTES, 'UTF-8'); ?>)">
                              <i class="fa-solid fa-eye me-2"></i> View Details
                            </a>
                            <span class="dropdown-item text-muted disabled" style="cursor: not-allowed; opacity: 0.6;">
                              <i class="fa-solid fa-flag-checkered me-2"></i> Already Ended
                            </span>
                          <?php else: // terminated ?>
                            <a class="dropdown-item text-muted" href="javascript:void(0);" onclick="editFlashSale(<?= htmlspecialchars(json_encode($fs), ENT_QUOTES, 'UTF-8'); ?>)">
                              <i class="fa-solid fa-eye me-2"></i> View Details
                            </a>
                            <span class="dropdown-item text-muted disabled" style="cursor: not-allowed; opacity: 0.6;">
                              <i class="fa-solid fa-ban me-2"></i> Already Terminated
                            </span>
                          <?php endif; ?>
                        </div>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
              <tr id="noFilterResultsRow" style="<?= !empty($flash_sales) ? 'display:none;' : ''; ?>">
                <td colspan="6" class="text-center text-muted py-4">No flash sales created yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Infinite Scroll Sentinel & Loading Indicator -->
      <div id="infiniteScrollSentinel" class="py-3 text-center">
        <div id="loadingSpinner" class="d-none">
          <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
          <span class="text-muted small fw-semibold">Loading more campaigns...</span>
        </div>
        <div id="endOfList" class="text-muted small d-none">
          <i class="fa-solid fa-check-double me-1 text-success"></i> All campaigns loaded
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$active_or_sched = [];
if (!empty($flash_sales)) {
    foreach ($flash_sales as $f) {
        $st = $f['computed_status'] ?? $f['status'];
        if ($st === 'running' || $st === 'scheduled') {
            $active_or_sched[] = [
                'id'     => (int)$f['id'],
                'title'  => trim($f['title']),
                'status' => $st
            ];
        }
    }
}
?>
<script>
const activeOrScheduledCampaigns = <?= json_encode($active_or_sched); ?>;

function escapeHtml(text) {
  if (!text) return '';
  const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
  return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
}

function previewFsBanner(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('fs_preview_img').src = e.target.result;
      document.getElementById('fs_preview_box').style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

let activeEditFsId = null;
let currentEditStatus = null;
let origEndTimeMs = null;

function promptTerminateSale(url, title, status) {
  if (typeof Swal !== 'undefined') {
    Swal.fire({
      html: '<p class="text-muted mb-3">Are you sure you want to terminate <strong>' + escapeHtml(title) + '</strong>?</p>',
      showCancelButton: true,
      confirmButtonText: 'Yes, Terminate',
      cancelButtonText: 'Cancel',
      customClass: {
        confirmButton: 'btn btn-danger me-2 waves-effect waves-light',
        cancelButton: 'btn btn-label-secondary waves-effect'
      },
      buttonsStyling: false,
      reverseButtons: true
    }).then(function(result) {
      if (result.isConfirmed) {
        window.location.href = url;
      }
    });
  } else {
    window.location.href = url;
  }
}

function editFlashSale(fs) {
  activeEditFsId = fs.id;
  const nowMs = new Date().getTime();
  const startMs = new Date(fs.start_time.replace(' ', 'T')).getTime();
  const endMs = new Date(fs.end_time.replace(' ', 'T')).getTime();
  origEndTimeMs = endMs;

  let status = fs.computed_status || fs.status;
  if (status !== 'terminated') {
    if (nowMs < startMs) {
      status = 'scheduled';
    } else if (nowMs >= startMs && nowMs <= endMs) {
      status = 'running';
    } else {
      status = 'ended';
    }
  }
  currentEditStatus = status;

  const idInput = document.getElementById('fs_id');
  const titleInput = document.getElementById('fs_title');
  const titleNotice = document.getElementById('fs_title_notice');
  const discInput = document.getElementById('fs_disc');
  const discNotice = document.getElementById('fs_disc_notice');
  const startInput = document.getElementById('fs_start');
  const startNotice = document.getElementById('fs_start_notice');
  const endInput = document.getElementById('fs_end');
  const endNotice = document.getElementById('fs_end_notice');
  const bannerInput = document.getElementById('fs_banner');
  const bannerNotice = document.getElementById('fs_banner_notice');
  const productsNotice = document.getElementById('fs_products_notice');
  const statusBadge = document.getElementById('fs_status_badge');
  const submitBtn = document.getElementById('fs_submit_btn');
  const terminateBtn = document.getElementById('fs_terminate_btn');
  const resetBtn = document.getElementById('fs_reset_btn');
  const formTitle = document.getElementById('fs-form-title');

  idInput.value = fs.id;
  titleInput.value = fs.title;
  discInput.value = fs.discount_percent || '30.00';

  if (fs.start_time) {
    startInput.value = fs.start_time.replace(' ', 'T').substring(0, 16);
  }
  if (fs.end_time) {
    endInput.value = fs.end_time.replace(' ', 'T').substring(0, 16);
  }

  // Pre-select products
  const selectedProductIds = (fs.product_ids && Array.isArray(fs.product_ids)) ? fs.product_ids.map(Number) : [];
  document.querySelectorAll('input[name="product_ids[]"]').forEach(function(c) {
    c.checked = selectedProductIds.includes(parseInt(c.value, 10));
  });

  // Banner preview
  document.getElementById('fs_current_banner').value = fs.banner || '';
  bannerInput.value = '';
  const prevBox = document.getElementById('fs_preview_box');
  const prevImg = document.getElementById('fs_preview_img');
  if (fs.banner) {
    prevImg.src = '<?= admin_storefront_asset_url('images/'); ?>' + fs.banner;
    prevBox.style.display = 'block';
  } else {
    prevBox.style.display = 'none';
  }

  // START DATE IS ALWAYS LOCKED AFTER SAVING across all states
  startInput.readOnly = true;
  startInput.style.backgroundColor = '#f1f3f5';
  startInput.style.cursor = 'not-allowed';
  startNotice.style.display = 'block';
  startNotice.innerHTML = '<i class="fa-solid fa-lock me-1 text-secondary"></i>Start date/time is locked after saving.';

  if (status === 'scheduled') {
    // 1. SCHEDULED STATE
    formTitle.innerText = 'Edit Scheduled Campaign: ' + fs.title;
    statusBadge.className = 'badge bg-label-info';
    statusBadge.innerHTML = '<i class="fa-regular fa-clock me-1"></i>Scheduled';

    // Campaign Name, Discount, Products, Banner, End Date: EDITABLE
    titleInput.readOnly = false;
    titleInput.style.backgroundColor = '';
    titleInput.style.cursor = '';
    titleNotice.style.display = 'none';

    discInput.readOnly = false;
    discInput.style.backgroundColor = '';
    discInput.style.cursor = '';
    discNotice.style.display = 'none';

    document.querySelectorAll('input[name="product_ids[]"]').forEach(function(c) { c.disabled = false; });
    productsNotice.style.display = 'none';

    bannerInput.disabled = false;
    bannerNotice.innerHTML = 'Recommended: Wide banner (e.g. 1200x400px).';

    endInput.readOnly = false;
    endInput.style.backgroundColor = '';
    endInput.style.cursor = '';
    endInput.min = startInput.value;
    endNotice.style.display = 'block';
    endNotice.className = 'text-muted d-block mt-1';
    endNotice.innerHTML = '<i class="fa-solid fa-pen me-1"></i>Must be after start date/time.';

    submitBtn.style.display = 'block';
    submitBtn.disabled = false;
    submitBtn.className = 'btn btn-primary w-100 mb-2';
    submitBtn.innerText = 'Update Scheduled Campaign';

    terminateBtn.style.display = 'block';
    terminateBtn.innerText = 'Terminate Campaign';
    resetBtn.innerText = 'Cancel & Create New';

  } else if (status === 'running') {
    // 2. RUNNING STATE
    formTitle.innerText = 'Extend Running Campaign: ' + fs.title;
    statusBadge.className = 'badge bg-label-success';
    statusBadge.innerHTML = '<i class="fa-solid fa-bolt me-1"></i>Running (Active)';

    // Campaign Name, Discount, Products, Banner: LOCKED
    titleInput.readOnly = true;
    titleInput.style.backgroundColor = '#f1f3f5';
    titleInput.style.cursor = 'not-allowed';
    titleNotice.style.display = 'block';
    titleNotice.innerHTML = '<i class="fa-solid fa-lock me-1 text-secondary"></i>Locked while sale is running.';

    discInput.readOnly = true;
    discInput.style.backgroundColor = '#f1f3f5';
    discInput.style.cursor = 'not-allowed';
    discNotice.style.display = 'block';
    discNotice.innerHTML = '<i class="fa-solid fa-lock me-1 text-secondary"></i>Locked while sale is running.';

    document.querySelectorAll('input[name="product_ids[]"]').forEach(function(c) { c.disabled = true; });
    productsNotice.style.display = 'block';
    productsNotice.innerHTML = '<i class="fa-solid fa-lock me-1 text-warning"></i>Locked while sale is running.';

    bannerInput.disabled = true;
    bannerNotice.innerHTML = '<i class="fa-solid fa-lock me-1 text-secondary"></i>Banner locked while running.';

    startNotice.innerHTML = '<i class="fa-solid fa-lock me-1 text-secondary"></i>Sale is currently running: Start date/time is locked.';

    // End Date/Time: EXTEND ONLY
    endInput.readOnly = false;
    endInput.style.backgroundColor = '';
    endInput.style.cursor = '';
    endInput.min = fs.end_time.replace(' ', 'T').substring(0, 16);
    endNotice.style.display = 'block';
    endNotice.className = 'text-primary fw-semibold d-block mt-1';
    endNotice.innerHTML = '<i class="fa-solid fa-arrow-trend-up me-1"></i>Running campaign: End date/time can only be extended.';

    submitBtn.style.display = 'block';
    submitBtn.disabled = false;
    submitBtn.className = 'btn btn-success w-100 mb-2';
    submitBtn.innerText = 'Extend End Date';

    terminateBtn.style.display = 'block';
    terminateBtn.innerText = 'Terminate Campaign Early';
    resetBtn.innerText = 'Cancel & Create New';

  } else if (status === 'ended') {
    // 3. ENDED STATE
    formTitle.innerText = 'Campaign Details: ' + fs.title + ' (Ended)';
    statusBadge.className = 'badge bg-label-secondary';
    statusBadge.innerHTML = '<i class="fa-solid fa-flag-checkered me-1"></i>Ended';

    // ALL FIELDS LOCKED
    titleInput.readOnly = true;
    titleInput.style.backgroundColor = '#f1f3f5';
    titleInput.style.cursor = 'not-allowed';
    titleNotice.style.display = 'block';
    titleNotice.innerHTML = '<i class="fa-solid fa-lock me-1 text-secondary"></i>Campaign has ended.';

    discInput.readOnly = true;
    discInput.style.backgroundColor = '#f1f3f5';
    discInput.style.cursor = 'not-allowed';
    discNotice.style.display = 'block';
    discNotice.innerHTML = '<i class="fa-solid fa-lock me-1 text-secondary"></i>Campaign has ended.';

    document.querySelectorAll('input[name="product_ids[]"]').forEach(function(c) { c.disabled = true; });
    productsNotice.style.display = 'block';
    productsNotice.innerHTML = '<i class="fa-solid fa-lock me-1 text-secondary"></i>Campaign has ended.';

    bannerInput.disabled = true;
    bannerNotice.innerHTML = '<i class="fa-solid fa-lock me-1 text-secondary"></i>Campaign has ended.';

    endInput.readOnly = true;
    endInput.style.backgroundColor = '#f1f3f5';
    endInput.style.cursor = 'not-allowed';
    endNotice.style.display = 'block';
    endNotice.className = 'text-muted d-block mt-1';
    endNotice.innerHTML = '<i class="fa-solid fa-lock me-1 text-secondary"></i>Campaign has ended and cannot be edited.';

    submitBtn.style.display = 'none';
    terminateBtn.style.display = 'none';
    resetBtn.innerText = 'Back to Create New';



  } else {
    // 4. TERMINATED STATE
    formTitle.innerText = 'Campaign Details: ' + fs.title + ' (Terminated)';
    statusBadge.className = 'badge bg-label-danger';
    statusBadge.innerHTML = '<i class="fa-solid fa-ban me-1"></i>Terminated';

    titleInput.readOnly = true;
    titleInput.style.backgroundColor = '#f1f3f5';
    titleInput.style.cursor = 'not-allowed';
    titleNotice.style.display = 'block';
    titleNotice.innerHTML = '<i class="fa-solid fa-ban me-1 text-danger"></i>Campaign was terminated.';

    discInput.readOnly = true;
    discInput.style.backgroundColor = '#f1f3f5';
    discInput.style.cursor = 'not-allowed';
    discNotice.style.display = 'block';
    discNotice.innerHTML = '<i class="fa-solid fa-ban me-1 text-danger"></i>Campaign was terminated.';

    document.querySelectorAll('input[name="product_ids[]"]').forEach(function(c) { c.disabled = true; });
    productsNotice.style.display = 'block';
    productsNotice.innerHTML = '<i class="fa-solid fa-ban me-1 text-danger"></i>Campaign was terminated.';

    bannerInput.disabled = true;
    bannerNotice.innerHTML = '<i class="fa-solid fa-ban me-1 text-danger"></i>Campaign was terminated.';

    endInput.readOnly = true;
    endInput.style.backgroundColor = '#f1f3f5';
    endInput.style.cursor = 'not-allowed';
    endNotice.style.display = 'block';
    endNotice.className = 'text-muted d-block mt-1';
    endNotice.innerHTML = '<i class="fa-solid fa-ban me-1 text-danger"></i>Campaign was terminated.';

    submitBtn.style.display = 'none';
    terminateBtn.style.display = 'none';
    resetBtn.innerText = 'Back to Create New';


  }

  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function terminateCurrentFs() {
  if (!activeEditFsId) return;
  const title = document.getElementById('fs_title').value || 'this campaign';
  promptTerminateSale('<?= site_url("flash_sales/terminate/"); ?>' + activeEditFsId, title, currentEditStatus || 'running');
}

function resetFsForm() {
  activeEditFsId = null;
  currentEditStatus = null;
  origEndTimeMs = null;
  document.getElementById('fs-form-title').innerText = 'Create Flash Sale Event';
  document.getElementById('fs_id').value = '';

  const titleInput = document.getElementById('fs_title');
  titleInput.value = '';
  titleInput.readOnly = false;
  titleInput.style.backgroundColor = '';
  titleInput.style.cursor = '';
  document.getElementById('fs_title_notice').style.display = 'none';

  const discInput = document.getElementById('fs_disc');
  discInput.value = '30.00';
  discInput.readOnly = false;
  discInput.style.backgroundColor = '';
  discInput.style.cursor = '';
  document.getElementById('fs_disc_notice').style.display = 'none';

  const startInput = document.getElementById('fs_start');
  const endInput = document.getElementById('fs_end');
  const startNotice = document.getElementById('fs_start_notice');
  const endNotice = document.getElementById('fs_end_notice');

  startInput.readOnly = false;
  startInput.style.backgroundColor = '';
  startInput.style.cursor = '';
  startNotice.style.display = 'none';

  endInput.readOnly = false;
  endInput.style.backgroundColor = '';
  endInput.style.cursor = '';
  endNotice.style.display = 'none';

  // Format current local date/time YYYY-MM-DDTHH:mm
  const d = new Date();
  d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
  const nowLocal = d.toISOString().substring(0, 16);
  startInput.value = nowLocal;

  const d7 = new Date();
  d7.setDate(d7.getDate() + 7);
  d7.setMinutes(d7.getMinutes() - d7.getTimezoneOffset());
  endInput.value = d7.toISOString().substring(0, 16);
  endInput.min = nowLocal;

  document.getElementById('fs_banner').disabled = false;
  document.getElementById('fs_current_banner').value = '';
  document.getElementById('fs_banner').value = '';
  document.getElementById('fs_banner_notice').innerHTML = 'Recommended: Wide banner (e.g. 1200x400px).';
  document.getElementById('fs_preview_box').style.display = 'none';
  document.getElementById('fs_preview_img').src = '';

  document.querySelectorAll('input[name="product_ids[]"]').forEach(function(c) {
    c.disabled = false;
    c.checked = false;
  });
  document.getElementById('fs_products_notice').style.display = 'none';

  const statusBadge = document.getElementById('fs_status_badge');
  statusBadge.className = 'badge bg-label-info';
  statusBadge.innerHTML = '<i class="fa-regular fa-clock me-1"></i>New / Scheduled';

  const submitBtn = document.getElementById('fs_submit_btn');
  submitBtn.style.display = 'block';
  submitBtn.disabled = false;
  submitBtn.className = 'btn btn-primary w-100 mb-2';
  submitBtn.innerText = 'Save Campaign';

  document.getElementById('fs_terminate_btn').style.display = 'none';
  document.getElementById('fs_reset_btn').innerText = 'Reset';


}

document.addEventListener('DOMContentLoaded', function() {
  const titleInput = document.getElementById('fs_title');
  if (titleInput) {
    titleInput.addEventListener('input', function() {
      const enteredTitle = this.value.trim().toLowerCase();
      const editId = document.getElementById('fs_id').value ? parseInt(document.getElementById('fs_id').value, 10) : null;
      const titleNotice = document.getElementById('fs_title_notice');
      if (!titleNotice) return;

      const conflict = activeOrScheduledCampaigns.find(function(c) {
        return (!editId || c.id !== editId) && c.title.toLowerCase() === enteredTitle;
      });

      if (conflict) {
        const stLabel = conflict.status === 'running' ? 'Running' : 'Scheduled';
        titleNotice.style.display = 'block';
        titleNotice.className = 'text-danger small mt-1';
        titleNotice.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i>A ' + stLabel + ' campaign with this title already exists.';
      } else {
        if (!currentEditStatus || currentEditStatus === 'scheduled') {
          titleNotice.style.display = 'none';
        }
      }
    });
  }

  const startInput = document.getElementById('fs_start');
  const endInput = document.getElementById('fs_end');
  if (startInput && endInput) {
    startInput.addEventListener('change', function() {
      if (!document.getElementById('fs_id').value) {
        endInput.min = this.value;
        if (endInput.value && endInput.value < this.value) {
          endInput.value = this.value;
        }
      }
    });
  }

  // Form submit validation using SweetAlert2
  const form = document.getElementById('fs_main_form');
  if (form) {
    form.addEventListener('submit', function(e) {
      const id = document.getElementById('fs_id').value;
      const titleInput = document.getElementById('fs_title');
      const enteredTitle = titleInput ? titleInput.value.trim().toLowerCase() : '';
      const editId = id ? parseInt(id, 10) : null;

      const conflict = activeOrScheduledCampaigns.find(function(c) {
        return (!editId || c.id !== editId) && c.title.toLowerCase() === enteredTitle;
      });

      if (conflict) {
        e.preventDefault();
        const stLabel = conflict.status === 'running' ? 'Running' : 'Scheduled';
        const titleNotice = document.getElementById('fs_title_notice');
        if (titleNotice) {
          titleNotice.style.display = 'block';
          titleNotice.className = 'text-danger small mt-1';
          titleNotice.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i>A ' + stLabel + ' campaign with this title already exists.';
        }
        titleInput.focus();
        return false;
      }

      const sVal = document.getElementById('fs_start').value;
      const eVal = document.getElementById('fs_end').value;

      if (!sVal || !eVal) return;

      const startTs = new Date(sVal).getTime();
      const endTs = new Date(eVal).getTime();

      if (endTs <= startTs) {
        e.preventDefault();
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            title: 'Invalid Date Range',
            text: 'End Date/Time must be after the Start Date/Time.',
            icon: 'error',
            customClass: { confirmButton: 'btn btn-primary waves-effect waves-light' },
            buttonsStyling: false
          });
        }
        return false;
      }

      if (id && currentEditStatus === 'running') {
        if (origEndTimeMs && endTs <= origEndTimeMs) {
          e.preventDefault();
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              title: 'Extension Required',
              text: 'For running campaigns, End Date/Time can only be extended beyond the current end date/time.',
              icon: 'warning',
              customClass: { confirmButton: 'btn btn-primary waves-effect waves-light' },
              buttonsStyling: false
            });
          }
          return false;
        }
      }
    });
  }
});
</script>


