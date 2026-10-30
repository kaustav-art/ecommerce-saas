<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0">
      <span class="text-muted fw-light">Roles /</span>
      <?= isset($role) ? 'Edit Role: ' . html_escape($role['name']) : 'Create Role'; ?>
    </h4>
    <a href="<?= site_url('roles'); ?>" class="btn btn-outline-secondary">
      <i class="fa-solid fa-arrow-left me-1"></i> Back to Roles
    </a>
  </div>

  <form action="<?= isset($role) ? site_url('roles/edit/' . $role['id']) : site_url('roles/add'); ?>" method="POST">
    <div class="row">
      <!-- Role Metadata Card -->
      <div class="col-12 col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">Role Details</h5>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label" for="name">Role Title <span class="text-danger">*</span></label>
              <input
                type="text"
                class="form-control"
                id="name"
                name="name"
                value="<?= isset($role) ? html_escape($role['name']) : ''; ?>"
                required
                placeholder="e.g. Catalog Editor" />
            </div>

            <div class="mb-3">
              <label class="form-label" for="description">Role Description</label>
              <textarea
                class="form-control"
                id="description"
                name="description"
                rows="4"
                placeholder="Explain what this role is responsible for..."><?= isset($role) ? html_escape($role['description']) : ''; ?></textarea>
            </div>

            <?php if (isset($role) && $role['slug'] === 'super-admin'): ?>
              <div class="alert alert-info small">
                <i class="fa-solid fa-circle-info me-1"></i> The Super Admin role inherently holds all system permissions and cannot have permissions revoked.
              </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary w-100 mt-3">
              <i class="fa-solid fa-floppy-disk me-1"></i> Save Role
            </button>
          </div>
        </div>
      </div>

      <!-- Permissions Checkboxes Matrix -->
      <div class="col-12 col-md-8 mb-4">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Module Permissions</h5>
            <small class="text-muted">Check privileges granted to this operator role</small>
          </div>
          <div class="card-body">
            <?php if (isset($role) && $role['slug'] === 'super-admin'): ?>
              <p class="text-success fw-bold"><i class="fa-solid fa-circle-check me-1"></i> All permissions enabled unconditionally for Super Administrator.</p>
            <?php else: ?>
              <div class="row gy-4">
                <?php foreach ($permissions_module as $module_name => $perms): ?>
                  <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                      <h6 class="text-uppercase fw-bold text-primary mb-3 pb-2 border-bottom">
                        <i class="fa-solid fa-folder me-1"></i> <?= ucfirst($module_name); ?>
                      </h6>
                      <?php foreach ($perms as $p): ?>
                        <div class="form-check mb-2">
                          <input
                            class="form-check-input"
                            type="checkbox"
                            id="perm_<?= $p['id']; ?>"
                            name="permissions[]"
                            value="<?= $p['id']; ?>"
                            <?= in_array($p['id'], $role_perms) ? 'checked' : ''; ?> />
                          <label class="form-check-label" for="perm_<?= $p['id']; ?>">
                            <strong><?= html_escape($p['name']); ?></strong><br>
                            <small class="text-muted"><code><?= html_escape($p['slug']); ?></code></small>
                          </label>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
