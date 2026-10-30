        <!-- breadcrumb -->
        <div class="bg-light py-2 border-bottom">
            <div class="container" style="max-width: 1240px;">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 12px;">
                        <li class="breadcrumb-item"><a href="<?= site_url('home'); ?>" class="text-muted text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url('account/profile'); ?>" class="text-muted text-decoration-none">My Account</a></li>
                        <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Profile Information</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- /breadcrumb -->

        <!-- my-account -->
        <section class="py-4" style="background-color: #f1f3f6; min-height: 80vh;">
            <div class="container" style="max-width: 1240px;">
                <div class="row g-3">
                    <!-- Left Sidebar -->
                    <div class="col-lg-3 col-md-4">
                        <?php $this->load->view('account/sidebar'); ?>
                    </div>

                    <!-- Right Content -->
                    <div class="col-lg-9 col-md-8">
                        <div class="card border rounded-1 shadow-sm bg-white p-3 p-md-4 mb-3" style="border-color: #f0f0f0 !important;">
                            <!-- Flash messages -->
                            <?php if ($this->session->flashdata('success')): ?>
                                <div class="alert alert-success alert-dismissible fade show rounded-1 py-2 px-3 small mb-4" role="alert">
                                    <i class="fa-solid fa-circle-check me-2"></i><?= $this->session->flashdata('success'); ?>
                                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            <?php if ($this->session->flashdata('error')): ?>
                                <div class="alert alert-danger alert-dismissible fade show rounded-1 py-2 px-3 small mb-4" role="alert">
                                    <i class="fa-solid fa-circle-exclamation me-2"></i><?= $this->session->flashdata('error'); ?>
                                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <?php
                            $raw_user_avatar = trim((string)($user['avatar'] ?? ''));
                            $has_user_avatar = ($raw_user_avatar !== '' && !in_array(strtolower($raw_user_avatar), ['default-user.png', 'user-default.jpg', 'default.png'], true));
                            $user_avatar_url = '';
                            if ($has_user_avatar) {
                                $clean_u_av = ltrim($raw_user_avatar, '/');
                                if (strpos($clean_u_av, 'assets/images/') === 0) {
                                    $clean_u_av = substr($clean_u_av, strlen('assets/images/'));
                                }
                                $user_avatar_url = (strpos($clean_u_av, 'http://') === 0 || strpos($clean_u_av, 'https://') === 0)
                                    ? $clean_u_av
                                    : base_url('assets/images/' . $clean_u_av);
                            }
                            ?>

                            <!-- Personal Information -->
                            <form action="<?= site_url('account/profile'); ?>" method="POST" enctype="multipart/form-data" class="mb-4" id="profile-update-form">
                                <input type="hidden" name="action" value="update_profile">
                                <input type="hidden" name="remove_avatar" id="remove-avatar-input" value="0">
                                <h5 class="fw-bold mb-3 text-dark" style="font-size: 17px;">Personal Information</h5>

                                <!-- Profile Picture Section -->
                                <div class="d-flex align-items-center gap-3 p-3 mb-4 rounded-2 border" style="background-color: #f8fafc; border-color: #e5e7eb !important;">
                                    <div id="profile-avatar-container" class="flex-shrink-0">
                                        <img id="profile-avatar-preview" src="<?= $has_user_avatar ? $user_avatar_url : ''; ?>" alt="Profile Picture" class="rounded-circle shadow-sm border" style="width: 50px; height: 50px; object-fit: cover; flex-shrink: 0; <?= $has_user_avatar ? '' : 'display: none;'; ?>" onerror="this.style.display='none';document.getElementById('profile-avatar-fallback').style.display='flex';">
                                        <div id="profile-avatar-fallback" class="rounded-circle align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 50px; height: 50px; background: linear-gradient(135deg, #108910, #1b52b3); font-size: 20px; flex-shrink: 0; display: <?= $has_user_avatar ? 'none' : 'flex'; ?>;">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold text-dark mb-1" style="font-size: 14px;">Profile Picture</div>
                                        <div class="d-flex align-items-center flex-wrap gap-2">
                                            <label for="profile-avatar-file" class="btn btn-outline-primary btn-sm rounded-1 fw-semibold px-3 py-1" style="cursor: pointer; font-size: 12.5px; border-color: #2874f0; color: #2874f0;">
                                                <i class="fa-solid fa-camera me-1"></i> Change Profile Picture
                                            </label>
                                            <input type="file" name="avatar" id="profile-avatar-file" accept="image/jpeg,image/png,image/webp,image/gif" class="d-none" onchange="handleProfileAvatarChange(this)">
                                            <?php if ($has_user_avatar): ?>
                                                <button type="button" id="remove-avatar-btn" class="btn btn-outline-danger btn-sm rounded-1 fw-semibold px-2.5 py-1" style="font-size: 12px;" onclick="removeProfileAvatar()">
                                                    <i class="fa-regular fa-trash-can me-1"></i> Remove
                                                </button>
                                            <?php endif; ?>
                                            <span id="profile-avatar-filename" class="text-success small fw-semibold d-none" style="font-size: 12px;"></span>
                                        </div>
                                        <div class="text-muted mt-1" style="font-size: 11px;">Allowed formats: JPG, PNG, WEBP, GIF</div>
                                    </div>
                                </div>
                                
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-dark">First Name <span class="text-danger">*</span></label>
                                        <input class="form-control rounded-1" type="text" name="first_name" value="<?= html_escape($user['first_name'] ?? ''); ?>" required style="height: 46px; border-color: #e0e0e0;">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-dark">Last Name <span class="text-danger">*</span></label>
                                        <input class="form-control rounded-1" type="text" name="last_name" value="<?= html_escape($user['last_name'] ?? ''); ?>" required style="height: 46px; border-color: #e0e0e0;">
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-dark">Email Address</label>
                                        <input class="form-control bg-light rounded-1" type="email" value="<?= html_escape($user['email'] ?? ''); ?>" disabled readonly style="height: 46px; border-color: #e0e0e0;">
                                        <small class="text-muted" style="font-size: 11px;">Email cannot be modified directly.</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-dark">Mobile Number</label>
                                        <input class="form-control rounded-1" type="tel" name="phone" value="<?= html_escape($user['phone'] ?? ''); ?>" placeholder="10-digit mobile number" style="height: 46px; border-color: #e0e0e0;">
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary fw-bold text-uppercase px-4 py-2 w-100 w-sm-auto rounded-1 shadow-sm" style="background-color: #2874f0; border-color: #2874f0; height: 44px; letter-spacing: 0.5px;">
                                        Save Changes
                                    </button>
                                </div>
                            </form>

                            <script>
                            function handleProfileAvatarChange(input) {
                                if (input.files && input.files[0]) {
                                    var file = input.files[0];
                                    var reader = new FileReader();
                                    reader.onload = function(e) {
                                        var img = document.getElementById('profile-avatar-preview');
                                        var fb = document.getElementById('profile-avatar-fallback');
                                        var rmInput = document.getElementById('remove-avatar-input');
                                        var fnSpan = document.getElementById('profile-avatar-filename');
                                        if (img) {
                                            img.src = e.target.result;
                                            img.style.display = 'block';
                                        }
                                        if (fb) {
                                            fb.style.display = 'none';
                                        }
                                        if (rmInput) {
                                            rmInput.value = '0';
                                        }
                                        if (fnSpan) {
                                            fnSpan.textContent = 'Selected: ' + file.name;
                                            fnSpan.classList.remove('d-none');
                                        }
                                    };
                                    reader.readAsDataURL(file);
                                }
                            }
                            function removeProfileAvatar() {
                                var img = document.getElementById('profile-avatar-preview');
                                var fb = document.getElementById('profile-avatar-fallback');
                                var fileInput = document.getElementById('profile-avatar-file');
                                var rmInput = document.getElementById('remove-avatar-input');
                                var rmBtn = document.getElementById('remove-avatar-btn');
                                var fnSpan = document.getElementById('profile-avatar-filename');
                                if (fileInput) fileInput.value = '';
                                if (rmInput) rmInput.value = '1';
                                if (img) {
                                    img.src = '';
                                    img.style.display = 'none';
                                }
                                if (fb) {
                                    fb.style.display = 'flex';
                                }
                                if (rmBtn) {
                                    rmBtn.classList.add('d-none');
                                }
                                if (fnSpan) {
                                    fnSpan.textContent = 'Photo will be removed on Save Changes';
                                    fnSpan.classList.remove('d-none');
                                }
                            }
                            </script>
                        </div>

                        <!-- Change Password Card -->
                        <div class="card border rounded-1 shadow-sm bg-white p-3 p-md-4" style="border-color: #f0f0f0 !important;">
                            <form action="<?= site_url('account/profile'); ?>" method="POST">
                                <input type="hidden" name="action" value="change_password">
                                <h5 class="fw-bold mb-3 text-dark" style="font-size: 17px;">Change Password</h5>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-dark">Current Password <span class="text-danger">*</span></label>
                                    <input class="form-control rounded-1" type="password" name="current_password" required placeholder="Enter current password" style="height: 46px; border-color: #e0e0e0; max-width: 450px;">
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-dark">New Password <span class="text-danger">*</span></label>
                                        <input class="form-control rounded-1" type="password" name="new_password" required placeholder="Minimum 6 characters" style="height: 46px; border-color: #e0e0e0;">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-dark">Confirm New Password <span class="text-danger">*</span></label>
                                        <input class="form-control rounded-1" type="password" name="confirm_password" required placeholder="Re-enter new password" style="height: 46px; border-color: #e0e0e0;">
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary fw-bold text-uppercase px-4 py-2 w-100 w-sm-auto rounded-1 shadow-sm" style="background-color: #2874f0; border-color: #2874f0; height: 44px; letter-spacing: 0.5px;">
                                        Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /my-account -->
