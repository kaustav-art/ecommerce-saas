<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold m-0"><span class="text-muted fw-light">Staff /</span> Admin Operators</h4>
      <small class="text-muted">Manage Super Admins, Managers, and Custom Role Operators</small>
    </div>
    <a href="<?= site_url('admins/add'); ?>" class="btn btn-primary">
      <i class="fa-solid fa-user-plus me-1"></i> Add New Operator
    </a>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Phone</th>
            <th>Status</th>
            <th>Last Login</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($admins)): ?>
            <?php foreach ($admins as $adm): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm me-2">
                      <span class="avatar-initial rounded-circle bg-label-info">
                        <?= strtoupper(substr($adm['name'], 0, 1)); ?>
                      </span>
                    </div>
                    <strong><?= html_escape($adm['name']); ?></strong>
                  </div>
                </td>
                <td><?= html_escape($adm['email']); ?></td>
                <td>
                  <span class="badge bg-label-<?= ($adm['role_slug'] === 'super-admin') ? 'primary' : 'secondary'; ?>">
                    <?= html_escape($adm['role_name'] ?? 'No Role'); ?>
                  </span>
                </td>
                <td><?= html_escape($adm['phone'] ?? 'N/A'); ?></td>
                <td>
                  <span class="badge bg-label-<?= ($adm['status'] === 'active') ? 'success' : 'danger'; ?>">
                    <?= ucfirst($adm['status']); ?>
                  </span>
                </td>
                <td><small class="text-muted"><?= $adm['last_login'] ? date('M d, H:i', strtotime($adm['last_login'])) : 'Never'; ?></small></td>
                <td>
                  <a href="<?= site_url('admins/edit/' . $adm['id']); ?>" class="btn btn-xs btn-outline-primary me-1">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                  </a>
                  <?php if ($adm['id'] != 1 && $adm['id'] != $this->current_admin['id']): ?>
                    <a
                      href="<?= site_url('admins/delete/' . $adm['id']); ?>"
                      class="btn btn-xs btn-outline-danger"
                      onclick="return confirm('Remove this administrator?');">
                      <i class="fa-solid fa-trash-can"></i>
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
