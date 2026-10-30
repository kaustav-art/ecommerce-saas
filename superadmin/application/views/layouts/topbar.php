<!-- Super Admin Topbar -->
<header class="sa-topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button" id="sidebarToggle">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="d-none d-sm-block">
            <span class="badge bg-dark text-warning border border-warning border-opacity-50 px-3 py-1 font-monospace">
                <i class="fa-solid fa-shield-halved me-1"></i> SAAS ECOMMERCE SUPER ADMIN
            </span>
        </div>
    </div>

    <!-- Right Controls -->
    <div class="d-flex align-items-center gap-3">
        <?php
            $active_gw = strtoupper(get_setting('active_payment_gateway', 'sandbox'));
        ?>
        <div class="d-none d-md-flex align-items-center gap-2 border rounded-pill px-3 py-1 bg-light small">
            <span class="text-muted"><i class="fa-solid fa-credit-card text-success me-1"></i> SaaS Gateway:</span>
            <span class="fw-bold text-dark"><?= htmlspecialchars($active_gw) ?></span>
            <span class="text-muted">|</span>
            <span class="badge bg-primary">3 Templates Active</span>
        </div>

        <a href="<?= superadmin_url('tenants') ?>" class="btn btn-warning btn-sm fw-bold px-3 shadow-sm">
            <i class="fa-solid fa-store me-1"></i> Manage Tenants
        </a>

        <!-- Profile Dropdown -->
        <div class="dropdown">
            <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 rounded-pill px-3 border" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-user-circle text-primary fs-5"></i>
                <span class="small fw-semibold d-none d-sm-inline"><?= isset($current_user) && $current_user->name ? htmlspecialchars($current_user->name) : 'Super Admin' ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                <li class="dropdown-header small text-uppercase fw-bold text-muted">SaaS Platform Admin</li>
                <li><a class="dropdown-item" href="<?= superadmin_url('profile') ?>"><i class="fa-solid fa-user-gear me-2 text-muted"></i>Admin Profile</a></li>
                <li><a class="dropdown-item" href="<?= superadmin_url('settings') ?>"><i class="fa-solid fa-sliders me-2 text-muted"></i>Gateways &amp; Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?= superadmin_url('logout') ?>"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i>Sign Out</a></li>
            </ul>
        </div>
    </div>
</header>
