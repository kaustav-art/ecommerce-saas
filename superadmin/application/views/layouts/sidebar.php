<?php
$act_c = isset($active_controller) ? $active_controller : 'dashboard';
?>
<!-- Super Admin Sidebar -->
<aside class="sa-sidebar" id="saSidebar">
    <!-- Brand Header -->
    <?php
        $sa_logo_url = function_exists('superadmin_logo_url') ? superadmin_logo_url() : base_url('../admin/assets/img/branding/logo.webp');
    ?>
    <div class="brand d-flex align-items-center justify-content-center p-3" style="min-height: 70px;">
        <a href="<?= superadmin_url('dashboard') ?>" class="d-flex align-items-center justify-content-center w-100">
            <img src="<?= $sa_logo_url ?>" alt="Logo" style="max-height: 44px; max-width: 180px; width: auto; object-fit: contain;" onerror="this.src='<?= main_site_url('admin/assets/img/branding/logo.webp') ?>'">
        </a>
    </div>

    <!-- Navigation Menu -->
    <ul class="sa-sidebar-menu">
        <li class="sa-menu-heading">SaaS Overview</li>
        <li class="sa-menu-item <?= $act_c === 'dashboard' ? 'active' : '' ?>">
            <a href="<?= superadmin_url('dashboard') ?>" class="sa-menu-link">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="sa-menu-heading">SaaS Plans & Billing</li>
        <li class="sa-menu-item <?= $act_c === 'plans' ? 'active' : '' ?>">
            <a href="<?= superadmin_url('plans') ?>" class="sa-menu-link">
                <i class="fa-solid fa-tags"></i>
                <span>Pricing Plans</span>
                <?php if (!empty($sidebar_plans_count)): ?>
                    <span class="badge bg-warning text-dark ms-auto font-monospace"><?= $sidebar_plans_count ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="sa-menu-item <?= $act_c === 'orders' ? 'active' : '' ?>">
            <a href="<?= superadmin_url('orders') ?>" class="sa-menu-link">
                <i class="fa-solid fa-receipt"></i>
                <span>Subscription Payments</span>
                <?php if (!empty($sidebar_orders_count)): ?>
                    <span class="badge bg-primary ms-auto font-monospace"><?= $sidebar_orders_count ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="sa-menu-item <?= $act_c === 'licenses' ? 'active' : '' ?>">
            <a href="<?= superadmin_url('licenses') ?>" class="sa-menu-link">
                <i class="fa-solid fa-key"></i>
                <span>Subscriptions & Licenses</span>
                <?php if (!empty($sidebar_licenses_count)): ?>
                    <span class="badge bg-success ms-auto font-monospace"><?= $sidebar_licenses_count ?></span>
                <?php endif; ?>
            </a>
        </li>

        <li class="sa-menu-heading">Tenants & Templates</li>
        <li class="sa-menu-item <?= $act_c === 'tenants' ? 'active' : '' ?>">
            <a href="<?= superadmin_url('tenants') ?>" class="sa-menu-link">
                <i class="fa-solid fa-store text-info"></i>
                <span>Tenant Stores</span>
            </a>
        </li>
        <li class="sa-menu-item <?= $act_c === 'layouts' ? 'active' : '' ?>">
            <a href="<?= superadmin_url('layouts') ?>" class="sa-menu-link">
                <i class="fa-solid fa-palette text-warning"></i>
                <span>Customize Templates</span>
            </a>
        </li>
        <li class="sa-menu-item <?= $act_c === 'website' ? 'active' : '' ?>">
            <a href="<?= superadmin_url('website') ?>" class="sa-menu-link">
                <i class="fa-solid fa-globe text-primary"></i>
                <span>Landing CMS & Themes</span>
            </a>
        </li>
        <li class="sa-menu-item <?= $act_c === 'settings' ? 'active' : '' ?>">
            <a href="<?= superadmin_url('settings') ?>" class="sa-menu-link">
                <i class="fa-solid fa-sliders text-success"></i>
                <span>Payment Gateways & Settings</span>
            </a>
        </li>

        <li class="sa-menu-heading">Live Storefront Demos</li>
        <li class="sa-menu-item">
            <a href="<?= main_site_url() ?>" target="_blank" class="sa-menu-link">
                <i class="fa-solid fa-rocket text-warning"></i>
                <span>SaaS Landing Website</span>
            </a>
        </li>
        <li class="sa-menu-item">
            <a href="<?= tenant_site_url('home?preview_tpl=fashion') ?>" target="_blank" class="sa-menu-link">
                <i class="fa-solid fa-shirt text-info"></i>
                <span>Fashion Template (T1)</span>
            </a>
        </li>
        <li class="sa-menu-item">
            <a href="<?= tenant_site_url('home?preview_tpl=electronics') ?>" target="_blank" class="sa-menu-link">
                <i class="fa-solid fa-microchip text-primary"></i>
                <span>Electronics Template (T2)</span>
            </a>
        </li>
        <li class="sa-menu-item">
            <a href="<?= tenant_site_url('home?preview_tpl=grocery') ?>" target="_blank" class="sa-menu-link">
                <i class="fa-solid fa-basket-shopping text-success"></i>
                <span>Grocery Template (T3)</span>
            </a>
        </li>
        <li class="sa-menu-item">
            <a href="<?= tenant_admin_url('auth/login') ?>" target="_blank" class="sa-menu-link">
                <i class="fa-solid fa-arrow-up-right-from-square text-secondary"></i>
                <span>Tenant Admin Login</span>
            </a>
        </li>
    </ul>

    <!-- Footer Profile in Sidebar -->
    <div class="p-3 border-top border-secondary border-opacity-25 mt-auto">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
                <div class="rounded-circle bg-warning text-dark fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px; height:34px; font-size:12px;">
                    SA
                </div>
                <div class="text-truncate" style="max-width: 140px;">
                    <div class="text-white small fw-bold text-truncate"><?= isset($current_user) && $current_user->name ? htmlspecialchars($current_user->name) : 'Super Admin' ?></div>
                    <div class="text-muted" style="font-size: 11px;">SaaS Super Admin</div>
                </div>
            </div>
            <a href="<?= superadmin_url('logout') ?>" class="btn btn-outline-danger btn-sm p-1 px-2" title="Sign Out">
                <i class="fa-solid fa-power-off"></i>
            </a>
        </div>
    </div>
</aside>
