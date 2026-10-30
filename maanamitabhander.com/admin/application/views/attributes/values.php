<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold m-0"><span class="text-muted fw-light">Catalog / Attributes /</span> <?= html_escape($attribute['name']); ?></h4>
      <small class="text-muted">Type: <span class="badge bg-label-info"><?= ($attribute['type'] === 'multiselect') ? 'Multiple Select' : ucwords(str_replace('_', ' ', $attribute['type'])); ?></span></small>
    </div>
    <a href="<?= site_url('attributes'); ?>" class="btn btn-outline-secondary btn-sm">
      <i class="fa-solid fa-arrow-left me-1"></i> Back to Attributes
    </a>
  </div>

  <div class="row">
    <!-- Form: Add / Edit Value -->
    <div class="col-md-4 mb-4">
      <div class="card">
        <div class="card-header pb-2">
          <h5 class="card-title mb-0" id="val-form-title"><?= !empty($edit_value) ? 'Edit Value: ' . html_escape($edit_value['value']) : 'Add New Value'; ?></h5>
        </div>
        <div class="card-body pt-3">
          <form action="<?= site_url('attributes/values/' . $attribute['id']); ?>" method="POST" id="valueForm">
            <input type="hidden" name="id" id="val_id" value="<?= !empty($edit_value) ? (int)$edit_value['id'] : ''; ?>">

            <div class="mb-3">
              <label class="form-label" for="val_title">Value / Label <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="val_title" name="value" placeholder="e.g. Red, XL, 16GB, 500g" value="<?= !empty($edit_value) ? html_escape($edit_value['value']) : ''; ?>" required>
            </div>

            <?php if ($attribute['type'] === 'color'): ?>
              <?php $cur_color = !empty($edit_value['color_code']) ? $edit_value['color_code'] : '#0d6efd'; ?>
              <div class="mb-3">
                <label class="form-label" for="val_color">Color Hex Code</label>
                <div class="input-group">
                  <input type="color" class="form-control form-control-color" id="val_color_picker" value="<?= html_escape($cur_color); ?>" onchange="document.getElementById('val_color').value=this.value;">
                  <input type="text" class="form-control" id="val_color" name="color_code" placeholder="#0d6efd" value="<?= html_escape($cur_color); ?>" oninput="if(/^#[0-9A-F]{6}$/i.test(this.value)) document.getElementById('val_color_picker').value=this.value;">
                </div>
              </div>
            <?php endif; ?>

            <div class="mb-3">
              <label class="form-label" for="val_sort">Sort Order</label>
              <input type="number" class="form-control" id="val_sort" name="sort_order" value="<?= !empty($edit_value) ? (int)$edit_value['sort_order'] : '0'; ?>">
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-2" id="val-submit-btn"><?= !empty($edit_value) ? 'Update Value' : 'Add Value'; ?></button>
            <button type="button" class="btn btn-outline-secondary w-100" id="val-reset-btn" onclick="resetValForm()" style="<?= !empty($edit_value) ? '' : 'display: none;'; ?>">Cancel / Reset</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Table: Values List -->
    <div class="col-md-8">
      <div class="card">
        <div class="card-header border-bottom">
          <h5 class="card-title mb-0">Values for <?= html_escape($attribute['name']); ?></h5>
        </div>
        <div class="table-responsive text-nowrap">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Value</th>
                <?php if ($attribute['type'] === 'color'): ?>
                  <th>Preview Swatch</th>
                  <th>Color Code</th>
                <?php endif; ?>
                <th>Sort Order</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($attribute['values'])): ?>
                <?php foreach ($attribute['values'] as $v): ?>
                  <tr id="val-row-<?= $v['id']; ?>">
                    <td><strong><?= html_escape($v['value']); ?></strong></td>
                    <?php if ($attribute['type'] === 'color'): ?>
                      <td>
                        <span class="d-inline-block rounded-circle border shadow-sm" style="width: 24px; height: 24px; background-color: <?= html_escape($v['color_code'] ?: '#ccc'); ?>;"></span>
                      </td>
                      <td><code><?= html_escape($v['color_code']); ?></code></td>
                    <?php endif; ?>
                    <td><?= $v['sort_order']; ?></td>
                    <td>
                      <button type="button" class="btn btn-xs btn-outline-primary me-1" title="Edit Value" onclick="editVal(<?= htmlspecialchars(json_encode($v), ENT_QUOTES, 'UTF-8'); ?>)">
                        <i class="fa-solid fa-pen-to-square"></i>
                      </button>
                      <a href="<?= site_url('attributes/delete_value/' . $attribute['id'] . '/' . $v['id']); ?>" class="btn btn-xs btn-outline-danger" onclick="return confirm('Remove this value?');" title="Delete Value">
                        <i class="fa-solid fa-trash-can"></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4" class="text-center text-muted py-4">No values added yet for this attribute.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function editVal(v) {
  document.getElementById('val-form-title').innerText = 'Edit Value: ' + v.value;
  document.getElementById('val_id').value = v.id;
  document.getElementById('val_title').value = v.value;
  document.getElementById('val_sort').value = v.sort_order;
  document.getElementById('val-submit-btn').innerText = 'Update Value';
  document.getElementById('val-reset-btn').style.display = 'block';

  var colorInput = document.getElementById('val_color');
  var colorPicker = document.getElementById('val_color_picker');
  if (colorInput) {
    colorInput.value = v.color_code || '#0d6efd';
    if (colorPicker && v.color_code) {
      colorPicker.value = v.color_code;
    }
  }

  // Highlight selected table row
  document.querySelectorAll('tbody tr').forEach(function(r) { r.classList.remove('table-primary'); });
  var row = document.getElementById('val-row-' + v.id);
  if (row) row.classList.add('table-primary');

  // Focus input and scroll smoothly to form
  document.getElementById('val_title').focus();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetValForm() {
  document.getElementById('val-form-title').innerText = 'Add New Value';
  document.getElementById('val_id').value = '';
  document.getElementById('val_title').value = '';
  document.getElementById('val_sort').value = '0';
  document.getElementById('val-submit-btn').innerText = 'Add Value';
  document.getElementById('val-reset-btn').style.display = 'none';

  var colorInput = document.getElementById('val_color');
  var colorPicker = document.getElementById('val_color_picker');
  if (colorInput) {
    colorInput.value = '#0d6efd';
    if (colorPicker) {
      colorPicker.value = '#0d6efd';
    }
  }

  document.querySelectorAll('tbody tr').forEach(function(r) { r.classList.remove('table-primary'); });
}
</script>
