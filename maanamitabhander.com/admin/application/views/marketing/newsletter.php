<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><span class="text-muted fw-light">Marketing /</span> Newsletter Subscribers</h4>
  </div>

  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">Subscribed Leads (<?= count($subscribers); ?>)</h5>
    </div>
    <div class="table-responsive text-nowrap">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>ID</th>
            <th>Email Address</th>
            <th>Status</th>
            <th>Subscribed Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($subscribers)): ?>
            <?php foreach ($subscribers as $sub): ?>
              <tr>
                <td>#<?= $sub['id']; ?></td>
                <td><strong><?= html_escape($sub['email']); ?></strong></td>
                <td><span class="badge bg-label-success"><?= ucfirst($sub['status']); ?></span></td>
                <td><small class="text-muted"><?= date('M d, Y H:i', strtotime($sub['created_at'])); ?></small></td>
                <td>
                  <a href="<?= site_url('marketing/delete_subscriber/' . $sub['id']); ?>" class="btn btn-xs btn-outline-danger" onclick="return confirm('Remove this subscriber?');">
                    <i class="fa-solid fa-trash-can"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="text-center text-muted py-4">No newsletter subscribers yet.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
