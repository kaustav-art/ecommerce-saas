        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->
          <nav
            class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
            id="layout-navbar">
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
              <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
                <i class="fa-solid fa-bars icon-md"></i>
              </a>
            </div>

            <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
              <!-- Search -->
              <div class="navbar-nav align-items-center">
                <div class="nav-item d-flex align-items-center">
                  <i class="fa-solid fa-magnifying-glass me-2 text-muted"></i>
                  <input
                    type="text"
                    class="form-control border-0 shadow-none ps-1 ps-sm-2"
                    placeholder="Search orders, products..."
                    aria-label="Search..." />
                </div>
              </div>
              <!-- /Search -->

              <ul class="navbar-nav flex-row align-items-center ms-auto">
                <?php if (!empty($tenant)): ?>
                  <?php
                    $nav_store_url = ((int)($tenant['id'] ?? 1) > 1 && !empty($tenant['slug']))
                      ? base_url('../website/s/' . $tenant['slug'])
                      : base_url('../website/home');
                  ?>
                  <li class="nav-item me-3 d-none d-md-flex align-items-center gap-2">
                    <span class="badge bg-label-primary px-3 py-2">
                      <i class="ri ri-store-2-line me-1"></i><?= html_escape($tenant['business_name'] ?? 'Fashion Store'); ?>
                      <span class="opacity-75 ms-1">(<?= html_escape(ucfirst($tenant['template'] ?? 'fashion')); ?>)</span>
                    </span>
                    <a href="<?= $nav_store_url; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                      <i class="ri ri-external-link-line me-1"></i>Live Store
                    </a>
                  </li>
                <?php endif; ?>
                <!-- User Profile Dropdown -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                  <a
                    class="nav-link dropdown-toggle hide-arrow p-0"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                      <img src="<?= base_url('assets/img/avatars/1.png'); ?>" alt class="w-px-40 h-auto rounded-circle" />
                    </div>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end mt-3 py-2">
                    <li>
                      <a class="dropdown-item" href="#">
                        <div class="d-flex align-items-center">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-online">
                              <img src="<?= base_url('assets/img/avatars/1.png'); ?>" alt class="w-px-40 h-auto rounded-circle" />
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <h6 class="mb-0 small"><?= html_escape($current_admin['name'] ?? 'Admin'); ?></h6>
                            <small class="badge bg-label-primary"><?= html_escape($current_admin['role_name'] ?? 'Super Admin'); ?></small>
                          </div>
                        </div>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?= site_url('settings'); ?>">
                        <i class="fa-solid fa-gear me-2"></i>
                        <span class="align-middle">Settings</span>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>
                      <a class="dropdown-item text-danger" href="<?= site_url('auth/logout'); ?>">
                        <i class="fa-solid fa-power-off me-2"></i>
                        <span class="align-middle">Log Out</span>
                      </a>
                    </li>
                  </ul>
                </li>
                <!--/ User -->
              </ul>
            </div>
          </nav>
          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Flash Message Banner (Excluded on pages that render their own flash messages like settings, orders/view, variants) -->
            <?php 
              $current_class  = strtolower($this->router->fetch_class() ?? '');
              $current_method = strtolower($this->router->fetch_method() ?? '');
              $skip_navbar_flash = (
                ($active_menu ?? '') === 'settings' || 
                $current_class === 'settings' ||
                ($current_class === 'orders' && $current_method === 'view') ||
                $current_class === 'variants'
              );
              if (!$skip_navbar_flash): 
            ?>
            <div class="container-xxl mt-3">
              <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <i class="fa-solid fa-circle-check me-2"></i>
                  <?= $this->session->flashdata('success'); ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              <?php endif; ?>
              <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <i class="fa-solid fa-triangle-exclamation me-2"></i>
                  <?= $this->session->flashdata('error'); ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              <?php endif; ?>
            </div>
            <?php endif; ?>
