<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><span class="text-muted fw-light">Operators /</span> Add Staff Member</h4>
    <a href="<?= site_url('admins'); ?>" class="btn btn-outline-secondary">
      <i class="fa-solid fa-arrow-left me-1"></i> Back to List
    </a>
  </div>

  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Operator Account Information</h5>
        </div>
        <div class="card-body">
          <form action="<?= site_url('admins/add'); ?>" method="POST">
            <div class="mb-3">
              <label class="form-label" for="name">Full Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="name" name="name" required placeholder="e.g. Sarah Jenkins" />
            </div>

            <div class="mb-3">
              <label class="form-label" for="email">Email Address <span class="text-danger">*</span></label>
              <input type="email" class="form-control" id="email" name="email" required placeholder="sarah@ecommerce.com" />
            </div>

            <div class="mb-3">
              <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
              <input type="password" class="form-control" id="password" name="password" required placeholder="Minimum 6 characters" />
            </div>

            <div class="mb-3">
              <label class="form-label" for="role_id">Assigned Role <span class="text-danger">*</span></label>
              <select class="form-select" id="role_id" name="role_id" required>
                <option value="">Select a Role</option>
                <?php foreach ($roles as $r): ?>
                  <option value="<?= $r['id']; ?>">
                    <?= html_escape($r['name']); ?> <?= !empty($r['description']) ? '— ' . html_escape($r['description']) : ''; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label" for="phone">Phone Number</label>
              <input type="text" class="form-control" id="phone" name="phone" placeholder="+1 555 0123" />
            </div>

            <div class="mb-4">
              <label class="form-label" for="status">Account Status</label>
              <select class="form-select" id="status" name="status">
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

            <button type="submit" class="btn btn-primary w-100">
              <i class="fa-solid fa-user-plus me-1"></i> Create Operator Account
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
