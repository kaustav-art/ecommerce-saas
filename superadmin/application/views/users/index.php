<?php
$curr_tab = isset($active_tab) ? $active_tab : 'admins';
$sym = isset($currency_symbol) ? $currency_symbol : '$';
$pos = isset($currency_position) ? $currency_position : 'left';
$fmt_curr = function($amt) use ($sym, $pos) {
    $formatted = number_format((float)$amt, 2);
    return $pos === 'left' ? ($sym . $formatted) : ($formatted . ' ' . $sym);
};
?>
<div class="container-fluid px-4 py-4">
    <!-- Top Action Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-3 border-bottom border-secondary border-opacity-25">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-warning text-dark fw-bold font-monospace px-2 py-1">ACCESS CONTROL &amp; CLIENTS</span>
                <span class="badge bg-secondary">USERS &amp; ACCOUNTS</span>
            </div>
            <h3 class="fw-bold text-white mb-0 font-serif">Users &amp; Accounts Management</h3>
            <p class="text-muted small mb-0">Oversee Super Administrators, staff accounts, and commercial marketplace customer client accounts.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-warning btn-sm fw-bold px-3" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="fa-solid fa-user-plus me-1"></i> Add Administrator
            </button>
            <a href="<?= superadmin_url('orders') ?>" class="btn btn-outline-light btn-sm fw-semibold px-3">
                <i class="fa-solid fa-receipt me-1"></i> View Orders
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills gap-2 mb-4 p-2 rounded-3 border border-secondary border-opacity-25" style="background: #0c1322;">
        <li class="nav-item">
            <a class="nav-link <?= $curr_tab === 'admins' ? 'active' : '' ?>" href="#tabAdmins" data-bs-toggle="pill">
                <i class="fa-solid fa-user-shield me-1"></i> Platform Administrators &amp; Staff (<?= count($users) ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $curr_tab === 'customers' ? 'active' : '' ?>" href="#tabCustomers" data-bs-toggle="pill">
                <i class="fa-solid fa-users me-1"></i> Customer &amp; Client Accounts (<?= count($customers) ?>)
            </a>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">

        <!-- TAB 1: PLATFORM ADMINISTRATORS & STAFF -->
        <div class="tab-pane fade <?= $curr_tab === 'admins' ? 'show active' : '' ?>" id="tabAdmins">
            <div class="card border-0 rounded-4 shadow-sm overflow-hidden" style="background: #111a2e; border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="card-header bg-black bg-opacity-25 py-3 border-bottom border-secondary border-opacity-25 d-flex align-items-center justify-content-between">
                    <h5 class="text-white fw-bold mb-0">System Administrators Roster</h5>
                    <span class="badge bg-secondary font-monospace"><?= count($users) ?> Registered</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead>
                            <tr class="border-secondary border-opacity-25">
                                <th>Administrator</th>
                                <th>Email Address</th>
                                <th>Role</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle <?= $u->role_id == 1 ? 'bg-warning text-dark' : 'bg-primary text-white' ?> fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width:38px; height:38px;">
                                                <?= strtoupper(substr($u->name, 0, 2)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-white"><?= htmlspecialchars($u->name) ?></div>
                                                <small class="text-muted">User #<?= $u->id ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-light"><i class="fa-regular fa-envelope text-muted me-1"></i><?= htmlspecialchars($u->email) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($u->role_id == 1): ?>
                                            <span class="badge bg-warning text-dark fw-bold px-2 py-1"><i class="fa-solid fa-crown me-1"></i>Administrator</span>
                                        <?php else: ?>
                                            <span class="badge bg-info text-white fw-bold px-2 py-1"><?= htmlspecialchars($u->role_name ? $u->role_name : 'Staff') ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($u->phone ? $u->phone : 'N/A') ?></td>
                                    <td>
                                        <span class="badge <?= $u->status === 'active' ? 'bg-success' : 'bg-secondary' ?> text-capitalize">
                                            <?= htmlspecialchars($u->status) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $u->id ?>">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <?php if ($u->id != $this->session->userdata('superadmin_user_id')): ?>
                                            <form action="<?= superadmin_url('users') ?>" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this administrator?');">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="user_id" value="<?= $u->id ?>">
                                                <input type="hidden" name="active_tab" value="admins">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <!-- Edit Administrator Modal -->
                                <div class="modal fade" id="editUserModal<?= $u->id ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow" style="background: #111a2e; color: white;">
                                            <form action="<?= superadmin_url('users') ?>" method="post">
                                                <input type="hidden" name="action" value="update_user">
                                                <input type="hidden" name="user_id" value="<?= $u->id ?>">
                                                <input type="hidden" name="active_tab" value="admins">
                                                <div class="modal-header border-secondary border-opacity-25">
                                                    <h5 class="modal-title fw-bold text-white">Edit Administrator: <?= htmlspecialchars($u->name) ?></h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="mb-3">
                                                        <label class="form-label text-white small fw-bold">Full Name</label>
                                                        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($u->name) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label text-white small fw-bold">Email Address</label>
                                                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($u->email) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label text-white small fw-bold">Phone Number</label>
                                                        <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($u->phone) ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label text-white small fw-bold">Role Assignment</label>
                                                        <select class="form-select" name="role_id">
                                                            <?php foreach ($roles as $r): ?>
                                                                <option value="<?= $r->id ?>" <?= $u->role_id == $r->id ? 'selected' : '' ?>><?= htmlspecialchars($r->role_name) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label text-white small fw-bold">Account Status</label>
                                                        <select class="form-select" name="status">
                                                            <option value="active" <?= $u->status === 'active' ? 'selected' : '' ?>>Active</option>
                                                            <option value="inactive" <?= $u->status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label text-white small fw-bold">Reset Password (Leave blank to keep current)</label>
                                                        <input type="password" class="form-control" name="new_password" placeholder="Enter new password">
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-secondary border-opacity-25">
                                                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-warning fw-bold">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 2: CUSTOMER & CLIENT ACCOUNTS -->
        <div class="tab-pane fade <?= $curr_tab === 'customers' ? 'show active' : '' ?>" id="tabCustomers">
            <div class="card border-0 rounded-4 shadow-sm overflow-hidden" style="background: #111a2e; border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="card-header bg-black bg-opacity-25 py-3 border-bottom border-secondary border-opacity-25 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div>
                        <h5 class="text-white fw-bold mb-0">Commercial Marketplace Customer Accounts</h5>
                        <p class="text-muted small mb-0">Purchasers of self-hosted salon and spa scripts with license keys and order histories.</p>
                    </div>
                    <span class="badge bg-warning text-dark fw-bold font-monospace"><?= count($customers) ?> Verified Customers</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead>
                            <tr class="border-secondary border-opacity-25">
                                <th>Customer &amp; Business</th>
                                <th>Contact Information</th>
                                <th>Total Purchases</th>
                                <th>Total Spend</th>
                                <th>License Key(s)</th>
                                <th>Latest Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($customers)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-users fa-3x mb-3 d-block opacity-25"></i>
                                        No customer marketplace orders recorded yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($customers as $c): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded-circle bg-warning text-dark fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width:38px; height:38px;">
                                                    <i class="fa-solid fa-user"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-white"><?= htmlspecialchars($c->customer_name) ?></div>
                                                    <small class="text-warning"><i class="fa-solid fa-shop me-1"></i><?= htmlspecialchars($c->business_name ? $c->business_name : 'Salon / Spa Client') ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-light small"><i class="fa-regular fa-envelope text-muted me-1"></i><?= htmlspecialchars($c->customer_email) ?></div>
                                            <?php if (!empty($c->customer_phone)): ?>
                                                <div class="text-muted small"><i class="fa-solid fa-phone text-muted me-1"></i><?= htmlspecialchars($c->customer_phone) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary font-monospace"><?= $c->total_orders ?> Order(s)</span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-warning font-monospace"><?= $fmt_curr($c->total_spent) ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $keys = explode(',', $c->license_keys);
                                            foreach (array_slice($keys, 0, 2) as $k):
                                                $k = trim($k);
                                                if (empty($k)) continue;
                                            ?>
                                                <div class="d-inline-flex align-items-center gap-1 bg-black bg-opacity-50 px-2 py-1 rounded font-monospace small mb-1 border border-secondary border-opacity-25">
                                                    <span class="text-light"><?= htmlspecialchars($k) ?></span>
                                                    <button type="button" class="btn btn-link btn-sm text-warning p-0 ms-1 copy-key-btn" data-key="<?= htmlspecialchars($k) ?>" title="Copy Key">
                                                        <i class="fa-regular fa-copy"></i>
                                                    </button>
                                                </div><br>
                                            <?php endforeach; ?>
                                            <?php if (count($keys) > 2): ?>
                                                <small class="text-muted">+<?= count($keys) - 2 ?> more</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="small text-muted"><?= date('M d, Y', strtotime($c->latest_order_date)) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Create Administrator Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="background: #111a2e; color: white;">
            <form action="<?= superadmin_url('users') ?>" method="post">
                <input type="hidden" name="action" value="create_user">
                <input type="hidden" name="active_tab" value="admins">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title fw-bold text-white"><i class="fa-solid fa-user-plus text-warning me-2"></i>Add New Administrator</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-white small fw-bold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required placeholder="e.g. Alexander Vance">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white small fw-bold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" required placeholder="e.g. alex@spasalon.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white small fw-bold">Phone Number</label>
                        <input type="text" class="form-control" name="phone" placeholder="e.g. +1 555-0199">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white small fw-bold">Role Assignment</label>
                        <select class="form-select" name="role_id">
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r->id ?>" <?= $r->id == 1 ? 'selected' : '' ?>><?= htmlspecialchars($r->role_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white small fw-bold">Initial Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" required placeholder="Min 6 characters">
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.copy-key-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var key = this.getAttribute('data-key');
            if (navigator.clipboard) {
                navigator.clipboard.writeText(key).then(function() {
                    alert('License Key copied to clipboard:\n' + key);
                });
            } else {
                prompt('Copy license key:', key);
            }
        });
    });
});
</script>
