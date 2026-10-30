<?php
$current_tab = $active_account_tab ?? 'profile';
$display_name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
if (empty($display_name)) {
    $display_name = 'Valued Customer';
}
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

<div class="fk-sidebar-wrapper">
    <!-- Desktop Sidebar (Hidden on Mobile) -->
    <div class="d-none d-md-block">
        <!-- User Greeting Card -->
        <div class="card border rounded-1 p-3 mb-3 bg-white d-flex flex-row align-items-center gap-3 shadow-sm" style="border-color: #f0f0f0 !important;">
            <?php if ($has_user_avatar): ?>
                <img src="<?= $user_avatar_url; ?>" alt="<?= html_escape($display_name); ?>" class="rounded-circle shadow-sm border" style="width: 50px; height: 50px; object-fit: cover; flex-shrink: 0;" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                <div class="rounded-circle align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 50px; height: 50px; background: linear-gradient(135deg, #108910, #1b52b3); font-size: 20px; flex-shrink: 0; display: none;">
                    <i class="fa-solid fa-user"></i>
                </div>
            <?php else: ?>
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 50px; height: 50px; background: linear-gradient(135deg, #108910, #1b52b3); font-size: 20px; flex-shrink: 0;">
                    <i class="fa-solid fa-user"></i>
                </div>
            <?php endif; ?>
            <div class="overflow-hidden">
                <div class="text-muted" style="font-size: 12px;">Hello,</div>
                <div class="fw-bold text-dark text-truncate" style="font-size: 16px;" title="<?= html_escape($display_name); ?>">
                    <?= html_escape($display_name); ?>
                </div>
            </div>
        </div>

        <!-- Navigation Menu Card -->
        <div class="card border rounded-1 bg-white overflow-hidden shadow-sm" style="border-color: #f0f0f0 !important;">
            <!-- MY ORDERS -->
            <a href="<?= site_url('account/orders'); ?>" class="fk-menu-group-header text-decoration-none d-flex align-items-center justify-content-between p-3 border-bottom <?= ($current_tab === 'orders') ? 'active' : ''; ?>">
                <div class="d-flex align-items-center gap-3">
                    <i class="fa-solid fa-box-archive text-primary fs-5" style="width: 20px;"></i>
                    <span class="fw-bold text-uppercase" style="font-size: 14px; letter-spacing: 0.3px;">MY ORDERS</span>
                </div>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 12px;"></i>
            </a>

            <!-- ACCOUNT SETTINGS -->
            <div class="fk-nav-section border-bottom">
                <div class="fk-section-title d-flex align-items-center gap-3 px-3 pt-3 pb-2 text-muted fw-bold text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-user text-primary fs-6" style="width: 20px;"></i>
                    <span>ACCOUNT SETTINGS</span>
                </div>
                <div class="fk-sub-links pb-2">
                    <a href="<?= site_url('account/profile'); ?>" class="fk-sub-link <?= ($current_tab === 'profile') ? 'active' : ''; ?>">
                        Profile Information
                    </a>
                    <a href="<?= site_url('account/address'); ?>" class="fk-sub-link <?= ($current_tab === 'address') ? 'active' : ''; ?>">
                        Manage Addresses
                    </a>
                    <a href="<?= site_url('account/returns'); ?>" class="fk-sub-link <?= ($current_tab === 'returns') ? 'active' : ''; ?>">
                        Returns & Refunds
                    </a>
                </div>
            </div>

            <!-- PAYMENTS -->
            <div class="fk-nav-section border-bottom">
                <div class="fk-section-title d-flex align-items-center gap-3 px-3 pt-3 pb-2 text-muted fw-bold text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-wallet text-primary fs-6" style="width: 20px;"></i>
                    <span>PAYMENTS</span>
                </div>
                <div class="fk-sub-links pb-2">
                    <a href="javascript:void(0);" class="fk-sub-link text-secondary" onclick="alert('Gift cards can be applied at checkout.');">
                        Gift Cards
                    </a>
                    <a href="javascript:void(0);" class="fk-sub-link text-secondary" onclick="alert('Saved payment options are securely tokenized at checkout.');">
                        Saved UPI
                    </a>
                    <a href="javascript:void(0);" class="fk-sub-link text-secondary" onclick="alert('Saved payment options are securely tokenized at checkout.');">
                        Saved Cards
                    </a>
                </div>
            </div>

            <!-- MY STUFF -->
            <div class="fk-nav-section border-bottom">
                <div class="fk-section-title d-flex align-items-center gap-3 px-3 pt-3 pb-2 text-muted fw-bold text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-folder-open text-primary fs-6" style="width: 20px;"></i>
                    <span>MY STUFF</span>
                </div>
                <div class="fk-sub-links pb-2">
                    <a href="<?= site_url('account/notifications'); ?>" class="fk-sub-link <?= ($current_tab === 'notifications') ? 'active' : ''; ?>">
                        All Notifications
                    </a>
                    <a href="<?= site_url('wishlist'); ?>" class="fk-sub-link <?= ($current_tab === 'wishlist') ? 'active' : ''; ?>">
                        My Wishlist
                    </a>
                </div>
            </div>

            <!-- LOGOUT -->
            <a href="<?= site_url('logout'); ?>" class="fk-menu-group-header text-decoration-none d-flex align-items-center gap-3 p-3 text-dark logout-link">
                <i class="fa-solid fa-power-off text-muted fs-5" style="width: 20px;"></i>
                <span class="fw-bold" style="font-size: 14px;">Logout</span>
            </a>
        </div>
    </div>

    <!-- Mobile Account Navigation Header (Visible only on screens < 768px) -->
    <div class="d-md-none mb-3">
        <!-- Compact User Greeting -->
        <div class="card border rounded-2 p-2 px-3 mb-2 bg-white d-flex flex-row align-items-center justify-content-between shadow-sm" style="border-color: #e0e0e0 !important;">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
                <?php if ($has_user_avatar): ?>
                    <img src="<?= $user_avatar_url; ?>" alt="<?= html_escape($display_name); ?>" class="rounded-circle shadow-sm border" style="width: 50px; height: 50px; object-fit: cover; flex-shrink: 0;" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                    <div class="rounded-circle align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 50px; height: 50px; background: linear-gradient(135deg, #108910, #1b52b3); font-size: 20px; flex-shrink: 0; display: none;">
                        <i class="fa-solid fa-user"></i>
                    </div>
                <?php else: ?>
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 50px; height: 50px; background: linear-gradient(135deg, #108910, #1b52b3); font-size: 20px; flex-shrink: 0;">
                        <i class="fa-solid fa-user"></i>
                    </div>
                <?php endif; ?>
                <div class="overflow-hidden">
                    <div class="text-muted" style="font-size: 11px; line-height: 1.1;">Hello,</div>
                    <div class="fw-bold text-dark text-truncate" style="font-size: 14px; max-width: 170px;">
                        <?= html_escape($display_name); ?>
                    </div>
                </div>
            </div>
            <button class="btn btn-sm btn-outline-secondary py-1 px-2 d-flex align-items-center gap-1 rounded-1" type="button" data-bs-toggle="collapse" data-bs-target="#fkMobileMenuCollapse" aria-expanded="false" style="font-size: 12px;">
                <i class="fa-solid fa-bars"></i>
                <span>Menu</span>
            </button>
        </div>

        <!-- Scrollable Horizontal Pill Tabs for Fast One-Tap Switching -->
        <div class="fk-mobile-nav-scroll-wrap p-1 bg-white rounded-2 border shadow-sm" style="border-color: #e0e0e0 !important;">
            <div class="fk-mobile-pills d-flex align-items-center gap-1 overflow-auto py-1 px-1" style="-webkit-overflow-scrolling: touch; scrollbar-width: none;">
                <a href="<?= site_url('account/orders'); ?>" class="btn btn-sm text-nowrap rounded-pill px-3 py-1 fw-semibold <?= ($current_tab === 'orders') ? 'btn-primary text-white shadow-sm' : 'btn-light text-secondary'; ?>" style="font-size: 12.5px;">
                    <i class="fa-solid fa-box-archive me-1"></i> Orders
                </a>
                <a href="<?= site_url('account/profile'); ?>" class="btn btn-sm text-nowrap rounded-pill px-3 py-1 fw-semibold <?= ($current_tab === 'profile') ? 'btn-primary text-white shadow-sm' : 'btn-light text-secondary'; ?>" style="font-size: 12.5px;">
                    <i class="fa-solid fa-user me-1"></i> Profile
                </a>
                <a href="<?= site_url('account/address'); ?>" class="btn btn-sm text-nowrap rounded-pill px-3 py-1 fw-semibold <?= ($current_tab === 'address') ? 'btn-primary text-white shadow-sm' : 'btn-light text-secondary'; ?>" style="font-size: 12.5px;">
                    <i class="fa-solid fa-location-dot me-1"></i> Addresses
                </a>
                <a href="<?= site_url('wishlist'); ?>" class="btn btn-sm text-nowrap rounded-pill px-3 py-1 fw-semibold <?= ($current_tab === 'wishlist') ? 'btn-primary text-white shadow-sm' : 'btn-light text-secondary'; ?>" style="font-size: 12.5px;">
                    <i class="fa-solid fa-heart me-1"></i> Wishlist
                </a>
                <a href="<?= site_url('account/returns'); ?>" class="btn btn-sm text-nowrap rounded-pill px-3 py-1 fw-semibold <?= ($current_tab === 'returns') ? 'btn-primary text-white shadow-sm' : 'btn-light text-secondary'; ?>" style="font-size: 12.5px;">
                    <i class="fa-solid fa-rotate-left me-1"></i> Returns
                </a>
                <a href="<?= site_url('account/notifications'); ?>" class="btn btn-sm text-nowrap rounded-pill px-3 py-1 fw-semibold <?= ($current_tab === 'notifications') ? 'btn-primary text-white shadow-sm' : 'btn-light text-secondary'; ?>" style="font-size: 12.5px;">
                    <i class="fa-solid fa-bell me-1"></i> Alerts
                </a>
                <a href="<?= site_url('logout'); ?>" class="btn btn-sm text-nowrap rounded-pill px-3 py-1 fw-semibold btn-light text-danger" style="font-size: 12.5px;">
                    <i class="fa-solid fa-power-off me-1"></i> Logout
                </a>
            </div>
        </div>

        <!-- Collapsible Full Dropdown Menu for Mobile -->
        <div class="collapse mt-2" id="fkMobileMenuCollapse">
            <div class="card border rounded-2 bg-white shadow-sm overflow-hidden" style="border-color: #e0e0e0 !important;">
                <div class="list-group list-group-flush" style="font-size: 13.5px;">
                    <a href="<?= site_url('account/orders'); ?>" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2 <?= ($current_tab === 'orders') ? 'active bg-primary text-white border-primary' : ''; ?>">
                        <span><i class="fa-solid fa-box-archive me-2 text-primary"></i>My Orders</span>
                        <i class="fa-solid fa-chevron-right small text-muted"></i>
                    </a>
                    <a href="<?= site_url('account/profile'); ?>" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2 <?= ($current_tab === 'profile') ? 'active bg-primary text-white border-primary' : ''; ?>">
                        <span><i class="fa-regular fa-user me-2 text-primary"></i>Profile Information</span>
                        <i class="fa-solid fa-chevron-right small text-muted"></i>
                    </a>
                    <a href="<?= site_url('account/address'); ?>" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2 <?= ($current_tab === 'address') ? 'active bg-primary text-white border-primary' : ''; ?>">
                        <span><i class="fa-solid fa-location-dot me-2 text-primary"></i>Manage Addresses</span>
                        <i class="fa-solid fa-chevron-right small text-muted"></i>
                    </a>
                    <a href="<?= site_url('wishlist'); ?>" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2 <?= ($current_tab === 'wishlist') ? 'active bg-primary text-white border-primary' : ''; ?>">
                        <span><i class="fa-regular fa-heart me-2 text-primary"></i>My Wishlist</span>
                        <i class="fa-solid fa-chevron-right small text-muted"></i>
                    </a>
                    <a href="<?= site_url('account/returns'); ?>" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2 <?= ($current_tab === 'returns') ? 'active bg-primary text-white border-primary' : ''; ?>">
                        <span><i class="fa-solid fa-rotate-left me-2 text-primary"></i>Returns & Refunds</span>
                        <i class="fa-solid fa-chevron-right small text-muted"></i>
                    </a>
                    <a href="<?= site_url('account/notifications'); ?>" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2 <?= ($current_tab === 'notifications') ? 'active bg-primary text-white border-primary' : ''; ?>">
                        <span><i class="fa-regular fa-bell me-2 text-primary"></i>Notifications</span>
                        <i class="fa-solid fa-chevron-right small text-muted"></i>
                    </a>
                    <a href="<?= site_url('logout'); ?>" class="list-group-item list-group-item-action text-danger d-flex align-items-center justify-content-between py-2">
                        <span><i class="fa-solid fa-power-off me-2"></i>Logout</span>
                        <i class="fa-solid fa-arrow-right-from-bracket small text-danger"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.fk-menu-group-header {
    color: #212121;
    background-color: #fff;
    transition: all 0.15s ease-in-out;
}
.fk-menu-group-header:hover {
    background-color: #f9f9f9;
    color: #2874f0;
}
.fk-menu-group-header.active {
    background-color: #f5faff;
    color: #2874f0;
    border-left: 4px solid #2874f0;
}
.fk-sub-link {
    display: block;
    padding: 9px 20px 9px 52px;
    font-size: 14px;
    color: #212121;
    text-decoration: none;
    transition: all 0.15s ease-in-out;
}
.fk-sub-link:hover {
    color: #2874f0;
    background-color: #f9f9f9;
}
.fk-sub-link.active {
    color: #2874f0;
    font-weight: 600;
    background-color: #f5faff;
    border-left: 4px solid #2874f0;
    padding-left: 48px;
}
.logout-link:hover {
    color: #dc3545 !important;
    background-color: #fff5f5;
}
.logout-link:hover i {
    color: #dc3545 !important;
}
</style>
