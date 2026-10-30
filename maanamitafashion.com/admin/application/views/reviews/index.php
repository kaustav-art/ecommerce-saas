<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><span class="text-muted fw-light">Catalog /</span> Product Reviews</h4>
  </div>

  <!-- Filters Card -->
  <div class="card mb-4">
    <div class="card-body">
      <form action="<?= site_url('reviews'); ?>" method="GET" class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Search</label>
          <input type="text" class="form-control" name="q" value="<?= html_escape($filters['search']); ?>" placeholder="Search customer, email or product...">
        </div>
        <div class="col-md-3">
          <label class="form-label">Status</label>
          <select class="form-select" name="status">
            <option value="">All Statuses</option>
            <option value="pending" <?= ($filters['status'] === 'pending') ? 'selected' : ''; ?>>Pending Moderation</option>
            <option value="approved" <?= ($filters['status'] === 'approved') ? 'selected' : ''; ?>>Approved</option>
            <option value="rejected" <?= ($filters['status'] === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Rating</label>
          <select class="form-select" name="rating">
            <option value="">All Ratings</option>
            <option value="5" <?= ($filters['rating'] == '5') ? 'selected' : ''; ?>>5 Stars</option>
            <option value="4" <?= ($filters['rating'] == '4') ? 'selected' : ''; ?>>4 Stars</option>
            <option value="3" <?= ($filters['rating'] == '3') ? 'selected' : ''; ?>>3 Stars</option>
            <option value="2" <?= ($filters['rating'] == '2') ? 'selected' : ''; ?>>2 Stars</option>
            <option value="1" <?= ($filters['rating'] == '1') ? 'selected' : ''; ?>>1 Star</option>
          </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
          <a href="<?= site_url('reviews'); ?>" class="btn btn-outline-secondary">Clear</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Reviews Table -->
  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">Reviews List (<?= $total_reviews; ?>)</h5>
    </div>
    <div class="table-responsive text-nowrap">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Product</th>
            <th>Customer</th>
            <th>Rating</th>
            <th>Review Text</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($reviews)): ?>
            <?php foreach ($reviews as $r): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <img src="<?= admin_storefront_asset_url('images/' . ($r['product_image'] ?: 'products/womens/women-1.jpg')); ?>" class="rounded me-2" style="width: 38px; height: 38px; object-fit: cover;" onerror="this.src='<?= admin_storefront_asset_url('images/products/womens/women-1.jpg'); ?>'">
                    <a href="<?= admin_storefront_url('product/' . $r['product_slug']); ?>" target="_blank" class="fw-bold text-dark text-truncate" style="max-width: 180px;">
                      <?= html_escape($r['product_title']); ?>
                    </a>
                  </div>
                </td>
                <td>
                  <strong><?= html_escape($r['customer_name']); ?></strong>
                  <div class="text-muted small"><?= html_escape($r['customer_email']); ?></div>
                </td>
                <td>
                  <div class="text-warning small">
                    <?php for ($s = 1; $s <= 5; $s++): ?>
                      <i class="fa-<?= ($s <= $r['rating']) ? 'solid' : 'regular'; ?> fa-star"></i>
                    <?php endfor; ?>
                  </div>
                </td>
                <td>
                  <span class="d-inline-block text-truncate" style="max-width: 250px;" title="<?= html_escape($r['review']); ?>">
                    <?= html_escape($r['review']); ?>
                  </span>
                </td>
                <td>
                  <?php if ($r['status'] === 'approved'): ?>
                    <span class="badge bg-label-success">Approved</span>
                  <?php elseif ($r['status'] === 'pending'): ?>
                    <span class="badge bg-label-warning">Pending</span>
                  <?php else: ?>
                    <span class="badge bg-label-danger">Rejected</span>
                  <?php endif; ?>
                </td>
                <td><small class="text-muted"><?= date('M d, Y', strtotime($r['created_at'])); ?></small></td>
                <td>
                  <?php if ($r['status'] !== 'approved'): ?>
                    <a href="<?= site_url('reviews/status/' . $r['id'] . '/approved'); ?>" class="btn btn-xs btn-outline-success me-1" title="Approve">
                      <i class="fa-solid fa-check"></i> Approve
                    </a>
                  <?php endif; ?>
                  <?php if ($r['status'] !== 'rejected'): ?>
                    <a href="<?= site_url('reviews/status/' . $r['id'] . '/rejected'); ?>" class="btn btn-xs btn-outline-warning me-1" title="Reject">
                      <i class="fa-solid fa-ban"></i>
                    </a>
                  <?php endif; ?>
                  <a href="<?= site_url('reviews/delete/' . $r['id']); ?>" class="btn btn-xs btn-outline-danger" onclick="return confirm('Delete this review permanently?');">
                    <i class="fa-solid fa-trash-can"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center text-muted py-4">No reviews found matching criteria.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
