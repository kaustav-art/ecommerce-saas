<div class="row g-4">
    <div class="col-12">
        <h4 class="fw-bold mb-1"><i class="fa-solid fa-user-shield text-warning me-2"></i>Super Administrator Profile</h4>
        <p class="text-muted mb-0">Manage master administrator account credentials and personal security settings.</p>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-id-card text-primary me-2"></i>Account Information</h6>
            </div>
            <div class="card-body p-4">
                <form action="<?= superadmin_url('profile') ?>" method="post">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Name</label>
                        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user->name) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Master Email Address</label>
                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user->email) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Direct Phone</label>
                        <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($user->phone) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Role Assignment</label>
                        <input type="text" class="form-control bg-light" value="Super Administrator (Full Platform Root)" readonly>
                    </div>

                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="fa-solid fa-save me-1"></i> Save Profile Details
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-key text-warning me-2"></i>Change Master Password</h6>
            </div>
            <div class="card-body p-4">
                <form action="<?= superadmin_url('profile') ?>" method="post">
                    <input type="hidden" name="action" value="change_password">

                    <div class="mb-3">
                        <label class="form-label small fw-bold">New Password</label>
                        <input type="password" class="form-control" name="new_password" required minlength="6" placeholder="At least 6 characters">
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Confirm New Password</label>
                        <input type="password" class="form-control" name="confirm_password" required minlength="6" placeholder="Re-type new password">
                    </div>

                    <div class="alert alert-warning small border-0 py-2 px-3 mb-4">
                        <i class="fa-solid fa-shield me-1"></i> Ensure you store your master password in a secure password manager.
                    </div>

                    <button type="submit" class="btn btn-warning fw-bold px-4">
                        <i class="fa-solid fa-lock me-1"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
