<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold m-0"><span class="text-muted fw-light">Roles /</span> Role-Based Access Control</h4>
      <small class="text-muted">Define custom roles and assign granular module permissions to operators</small>
    </div>
    <a href="<?= site_url('roles/add'); ?>" class="btn btn-primary">
      <i class="fa-solid fa-shield-halved me-1"></i> Create New Role
    </a>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Role Name</th>
            <th>Slug</th>
            <th>Description</th>
            <th>Assigned Operators</th>
            <th>Permissions Granted</th>
            <th>Type</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($roles)): ?>
            <?php foreach ($roles as $r): ?>
              <tr>
                <td><strong><?= html_escape($r['name']); ?></strong></td>
                <td><code><?= html_escape($r['slug']); ?></code></td>
                <td><?= html_escape($r['description'] ?? '—'); ?></td>
                <td><span class="badge bg-label-primary"><?= $r['user_count']; ?> Users</span></td>
                <td>
                  <?php if ($r['slug'] === 'super-admin'): ?>
                    <span class="badge bg-label-success">All Access (Super Admin)</span>
                  <?php else: ?>
                    <span class="badge bg-label-info"><?= $r['perm_count']; ?> Permissions</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?= ($r['is_system'] == 1) ? '<span class="badge bg-label-dark">System Protected</span>' : '<span class="badge bg-label-secondary">Custom</span>'; ?>
                </td>
                <td>
                  <a href="<?= site_url('roles/edit/' . $r['id']); ?>" class="btn btn-xs btn-outline-primary me-1">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Permissions
                  </a>
                  <?php if ($r['is_system'] != 1): ?>
                    <a
                      href="<?= site_url('roles/delete/' . $r['id']); ?>"
                      class="btn btn-xs btn-outline-danger"
                      onclick="return confirm('Delete this role?');">
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
