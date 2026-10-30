        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
          <div class="app-brand demo">
            <a href="<?= site_url('dashboard'); ?>" class="app-brand-link">
              <?php
                $sidebar_logo = (!empty($store_settings['site_logo']))
                  ? base_url('../website/assets/images/logo/' . $store_settings['site_logo'])
                  : base_url('assets/img/branding/logo.webp');
              ?>
              <img src="<?= $sidebar_logo; ?>" alt="Logo" style="max-height: 40px; width: auto; max-width: 180px; object-fit: contain;" onerror="this.src='<?= base_url('assets/img/branding/logo.webp'); ?>'">
            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
              <i class="icon-base ri ri-close-line d-block d-xl-none"></i>
            </a>
          </div>

          <div class="menu-inner-shadow"></div>

          <ul class="menu-inner py-1">
            <!-- Dashboard -->
            <?php if ($this->can('dashboard.view')): ?>
            <li class="menu-item <?= ($active_menu === 'dashboard') ? 'active' : ''; ?>">
              <a href="<?= site_url('dashboard'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-dashboard-line"></i>
                <div data-i18n="Dashboard">Dashboard</div>
              </a>
            </li>
            <?php endif; ?>

            <!-- Catalog -->
            <?php if ($this->can('products.view') || $this->can('categories.manage') || $this->can('brands.manage') || $this->can('products.manage') || $this->can('reviews.manage')): ?>
            <li class="menu-header mt-3">
              <span class="menu-header-text">Catalog</span>
            </li>

            <!-- Products (Merged All Products & Add Product) -->
            <?php if ($this->can('products.view')): ?>
            <li class="menu-item <?= ($active_menu === 'products' && in_array($active_submenu, ['products_list', 'products_add', ''])) ? 'active' : ''; ?>">
              <a href="<?= site_url('products'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-shopping-bag-3-line"></i>
                <div data-i18n="Products">Products</div>
              </a>
            </li>
            <?php endif; ?>

            <!-- Attributes & Values -->
            <?php if ($this->can('products.manage')): ?>
            <li class="menu-item <?= ($active_menu === 'products' && $active_submenu === 'attributes') ? 'active' : ''; ?>">
              <a href="<?= site_url('attributes'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-price-tag-3-line"></i>
                <div data-i18n="Attributes & Values">Attributes & Values</div>
              </a>
            </li>
            <?php endif; ?>

            <!-- Categories -->
            <?php if ($this->can('categories.manage')): ?>
            <li class="menu-item <?= ($active_menu === 'products' && $active_submenu === 'categories') ? 'active' : ''; ?>">
              <a href="<?= site_url('categories'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-folder-3-line"></i>
                <div data-i18n="Categories">Categories</div>
              </a>
            </li>
            <?php endif; ?>

            <!-- Brands -->
            <?php if ($this->can('brands.manage')): ?>
            <li class="menu-item <?= ($active_menu === 'products' && $active_submenu === 'brands') ? 'active' : ''; ?>">
              <a href="<?= site_url('brands'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-award-line"></i>
                <div data-i18n="Brands">Brands</div>
              </a>
            </li>
            <?php endif; ?>

            <!-- Product Reviews -->
            <?php if ($this->can('products.manage') || $this->can('reviews.manage')): ?>
            <li class="menu-item <?= ($active_menu === 'products' && $active_submenu === 'reviews') ? 'active' : ''; ?>">
              <a href="<?= site_url('reviews'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-feedback-line"></i>
                <div data-i18n="Product Reviews">Product Reviews</div>
              </a>
            </li>
            <?php endif; ?>
            <?php endif; ?>

            <!-- Sales & Orders -->
            <?php if ($this->can('orders.view') || $this->can('orders.manage')): ?>
            <li class="menu-header mt-3">
              <span class="menu-header-text">Sales</span>
            </li>

            <?php 
              $order_status_filter = $this->input->get('status'); 
              $is_orders_page = ($active_menu === 'orders' && $active_submenu !== 'returns');
            ?>

            <!-- All Orders -->
            <?php if ($this->can('orders.view')): ?>
            <li class="menu-item <?= ($is_orders_page && empty($order_status_filter)) ? 'active' : ''; ?>">
              <a href="<?= site_url('orders'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-file-list-3-line"></i>
                <div data-i18n="All Orders">All Orders</div>
              </a>
            </li>

            <!-- Pending Orders -->
            <li class="menu-item <?= ($is_orders_page && $order_status_filter === 'pending') ? 'active' : ''; ?>">
              <a href="<?= site_url('orders?status=pending'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-time-line"></i>
                <div data-i18n="Pending Orders">Pending Orders</div>
              </a>
            </li>


            <!-- Shipped Orders -->
            <li class="menu-item <?= ($is_orders_page && $order_status_filter === 'shipped') ? 'active' : ''; ?>">
              <a href="<?= site_url('orders?status=shipped'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-truck-line"></i>
                <div data-i18n="Shipped Orders">Shipped Orders</div>
              </a>
            </li>

            <!-- Delivered Orders -->
            <li class="menu-item <?= ($is_orders_page && $order_status_filter === 'delivered') ? 'active' : ''; ?>">
              <a href="<?= site_url('orders?status=delivered'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-checkbox-circle-line"></i>
                <div data-i18n="Delivered Orders">Delivered Orders</div>
              </a>
            </li>

            <!-- Cancelled Orders -->
            <li class="menu-item <?= ($is_orders_page && $order_status_filter === 'cancelled') ? 'active' : ''; ?>">
              <a href="<?= site_url('orders?status=cancelled'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-close-circle-line"></i>
                <div data-i18n="Cancelled Orders">Cancelled Orders</div>
              </a>
            </li>
            <?php endif; ?>

            <!-- Returns & Refunds -->
            <?php if ($this->can('orders.manage')): ?>
            <li class="menu-item <?= ($active_submenu === 'returns' || $active_menu === 'returns') ? 'active' : ''; ?>">
              <a href="<?= site_url('returns'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-refund-2-line"></i>
                <div data-i18n="Returns & Refunds">Returns & Refunds</div>
              </a>
            </li>
            <?php endif; ?>
            <?php endif; ?>

            <!-- Customers -->
            <?php if ($this->can('customers.view')): ?>
            <li class="menu-header mt-3">
              <span class="menu-header-text">Customers</span>
            </li>

            <!-- Customer List -->
            <li class="menu-item <?= ($active_menu === 'customers' && $active_submenu !== 'customer_groups') ? 'active' : ''; ?>">
              <a href="<?= site_url('customers'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-user-line"></i>
                <div data-i18n="Customer List">Customer List</div>
              </a>
            </li>

            <!-- Customer Groups -->
            <li class="menu-item <?= ($active_menu === 'customers' && $active_submenu === 'customer_groups') ? 'active' : ''; ?>">
              <a href="<?= site_url('customer_groups'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-group-line"></i>
                <div data-i18n="Customer Groups">Customer Groups</div>
              </a>
            </li>
            <?php endif; ?>

            <!-- Marketing -->
            <?php if ($this->can('settings.manage') || $this->can('banners.manage') || $this->can('marketing.manage')): ?>
            <li class="menu-header mt-3">
              <span class="menu-header-text">Marketing</span>
            </li>

            <!-- Coupons & Discounts -->
            <li class="menu-item <?= ($active_menu === 'marketing' && $active_submenu === 'coupons') ? 'active' : ''; ?>">
              <a href="<?= site_url('coupons'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-coupon-3-line"></i>
                <div data-i18n="Coupons & Discounts">Coupons & Discounts</div>
              </a>
            </li>

            <!-- Flash Sales -->
            <li class="menu-item <?= ($active_menu === 'marketing' && $active_submenu === 'flash_sales') ? 'active' : ''; ?>">
              <a href="<?= site_url('flash_sales'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-fire-line"></i>
                <div data-i18n="Flash Sales">Flash Sales</div>
              </a>
            </li>

            <!-- Banners & Sliders -->
            <?php if ($this->can('banners.manage')): ?>
            <li class="menu-item <?= ($active_submenu === 'banners') ? 'active' : ''; ?>">
              <a href="<?= site_url('banners'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-image-2-line"></i>
                <div data-i18n="Banners & Sliders">Banners & Sliders</div>
              </a>
            </li>
            <?php endif; ?>

            <!-- Newsletter Subscribers -->
            <li class="menu-item <?= ($active_menu === 'marketing' && $active_submenu === 'newsletter') ? 'active' : ''; ?>">
              <a href="<?= site_url('marketing/newsletter'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-mail-send-line"></i>
                <div data-i18n="Newsletter">Newsletter Subscribers</div>
              </a>
            </li>
            <?php endif; ?>

            <!-- Inventory -->
            <?php if ($this->can('products.view') || $this->can('inventory.view') || $this->can('inventory.manage')): ?>
            <li class="menu-header mt-3">
              <span class="menu-header-text">Inventory</span>
            </li>

            <!-- Current Stock -->
            <li class="menu-item <?= ($active_menu === 'inventory' && ($active_submenu === 'stock_list' || empty($active_submenu))) ? 'active' : ''; ?>">
              <a href="<?= site_url('inventory'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-archive-line"></i>
                <div data-i18n="Current Stock">Current Stock</div>
              </a>
            </li>

            <!-- Low Stock Alerts -->
            <li class="menu-item <?= ($active_menu === 'inventory' && $active_submenu === 'low_stock') ? 'active' : ''; ?>">
              <a href="<?= site_url('inventory/low_stock'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-alarm-warning-line"></i>
                <div data-i18n="Low Stock">Low Stock Alerts</div>
              </a>
            </li>

            <!-- Stock Adjustments -->
            <?php if ($this->can('products.manage') || $this->can('inventory.manage')): ?>
            <li class="menu-item <?= ($active_menu === 'inventory' && $active_submenu === 'stock_adjust') ? 'active' : ''; ?>">
              <a href="<?= site_url('inventory/adjust'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-equalizer-line"></i>
                <div data-i18n="Stock Adjustments">Stock Adjustments</div>
              </a>
            </li>
            <?php endif; ?>

            <!-- Stock History -->
            <li class="menu-item <?= ($active_menu === 'inventory' && $active_submenu === 'stock_history') ? 'active' : ''; ?>">
              <a href="<?= site_url('inventory/history'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-history-line"></i>
                <div data-i18n="Stock History">Stock History</div>
              </a>
            </li>
            <?php endif; ?>

            <!-- Reports -->
            <?php if ($this->can('dashboard.view') || $this->can('reports.view')): ?>
            <li class="menu-header mt-3">
              <span class="menu-header-text">Reports</span>
            </li>

            <!-- Sales Report -->
            <li class="menu-item <?= ($active_menu === 'reports' && $active_submenu === 'sales_report') ? 'active' : ''; ?>">
              <a href="<?= site_url('reports/sales'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-line-chart-line"></i>
                <div data-i18n="Sales Report">Sales Report</div>
              </a>
            </li>

            <!-- Order Report -->
            <li class="menu-item <?= ($active_menu === 'reports' && $active_submenu === 'order_report') ? 'active' : ''; ?>">
              <a href="<?= site_url('reports/orders'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-bar-chart-2-line"></i>
                <div data-i18n="Order Report">Order Report</div>
              </a>
            </li>

            <!-- Product Report -->
            <li class="menu-item <?= ($active_menu === 'reports' && $active_submenu === 'product_report') ? 'active' : ''; ?>">
              <a href="<?= site_url('reports/products'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-pie-chart-line"></i>
                <div data-i18n="Product Report">Product Report</div>
              </a>
            </li>

            <!-- Customer Report -->
            <li class="menu-item <?= ($active_menu === 'reports' && $active_submenu === 'customer_report') ? 'active' : ''; ?>">
              <a href="<?= site_url('reports/customers'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-user-3-line"></i>
                <div data-i18n="Customer Report">Customer Report</div>
              </a>
            </li>

            <!-- Tax Report -->
            <li class="menu-item <?= ($active_menu === 'reports' && $active_submenu === 'tax_report') ? 'active' : ''; ?>">
              <a href="<?= site_url('reports/tax'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-percent-line"></i>
                <div data-i18n="Tax Report">Tax Report</div>
              </a>
            </li>

            <!-- Inventory Report -->
            <li class="menu-item <?= ($active_menu === 'reports' && $active_submenu === 'inventory_report') ? 'active' : ''; ?>">
              <a href="<?= site_url('reports/inventory'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-database-2-line"></i>
                <div data-i18n="Inventory Report">Inventory Report</div>
              </a>
            </li>
            <?php endif; ?>

            <!-- RBAC & Staff Administration -->
            <?php if ($this->can('admins.manage') || $this->can('roles.manage')): ?>
            <li class="menu-header mt-3">
              <span class="menu-header-text">Staff & Access Control</span>
            </li>

            <!-- Admin Operators -->
            <?php if ($this->can('admins.manage')): ?>
            <li class="menu-item <?= ($active_menu === 'users_management' && in_array($active_submenu, ['admins_list', 'admins_add'])) ? 'active' : ''; ?>">
              <a href="<?= site_url('admins'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-shield-user-line"></i>
                <div data-i18n="Admin Staff">Admin Staff</div>
              </a>
            </li>
            <?php endif; ?>

            <!-- Roles & Permissions -->
            <?php if ($this->can('roles.manage')): ?>
            <li class="menu-item <?= ($active_menu === 'users_management' && $active_submenu === 'roles') ? 'active' : ''; ?>">
              <a href="<?= site_url('roles'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-shield-keyhole-line"></i>
                <div data-i18n="Roles & Permissions">Roles & Permissions</div>
              </a>
            </li>
            <?php endif; ?>
            <?php endif; ?>

            <!-- Settings -->
            <?php if ($this->can('settings.manage') || $this->can('gateways.manage')): ?>
            <li class="menu-header mt-3">
              <span class="menu-header-text">Settings</span>
            </li>

            <!-- General Settings -->
            <?php if ($this->can('settings.manage')): ?>
            <li class="menu-item <?= ($active_menu === 'settings' && ($active_submenu === 'general' || empty($active_submenu))) ? 'active' : ''; ?>">
              <a href="<?= site_url('settings/general'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-settings-3-line"></i>
                <div data-i18n="General Settings">General Settings</div>
              </a>
            </li>

            <!-- Shipping Settings -->
            <li class="menu-item <?= ($active_menu === 'settings' && $active_submenu === 'shipping') ? 'active' : ''; ?>">
              <a href="<?= site_url('settings/shipping'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-truck-line"></i>
                <div data-i18n="Shipping Settings">Shipping Settings</div>
              </a>
            </li>

            <!-- Tax Settings -->
            <li class="menu-item <?= ($active_menu === 'settings' && $active_submenu === 'tax') ? 'active' : ''; ?>">
              <a href="<?= site_url('settings/tax'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-percent-line"></i>
                <div data-i18n="Tax Settings">Tax Settings</div>
              </a>
            </li>

            <!-- Email Settings -->
            <li class="menu-item <?= ($active_menu === 'settings' && $active_submenu === 'email') ? 'active' : ''; ?>">
              <a href="<?= site_url('settings/email'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-mail-settings-line"></i>
                <div data-i18n="Email Settings">Email (SMTP) Settings</div>
              </a>
            </li>

            <!-- SMS Settings -->
            <li class="menu-item <?= ($active_menu === 'settings' && $active_submenu === 'sms') ? 'active' : ''; ?>">
              <a href="<?= site_url('settings/sms'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-message-3-line"></i>
                <div data-i18n="SMS Settings">SMS Settings</div>
              </a>
            </li>

            <!-- SEO & Meta Settings -->
            <li class="menu-item <?= ($active_menu === 'settings' && $active_submenu === 'seo') ? 'active' : ''; ?>">
              <a href="<?= site_url('settings/seo'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-global-line"></i>
                <div data-i18n="SEO Settings">SEO & Meta Settings</div>
              </a>
            </li>
            <?php endif; ?>

            <!-- Payment Gateways -->
            <?php if ($this->can('gateways.manage')): ?>
            <li class="menu-item <?= ($active_menu === 'settings' && $active_submenu === 'payment_gateways') ? 'active' : ''; ?>">
              <a href="<?= site_url('settings/payment'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-bank-card-line"></i>
                <div data-i18n="Payment Gateways">Payment Gateways</div>
              </a>
            </li>
            <?php endif; ?>
            <?php endif; ?>

            <!-- SaaS Plan & Billing -->
            <li class="menu-header mt-3">
              <span class="menu-header-text">SaaS Plan & Billing</span>
            </li>
            <li class="menu-item <?= ($active_menu === 'saas_subscription' && $active_submenu === 'subscription') ? 'active' : ''; ?>">
              <a href="<?= site_url('subscription'); ?>" class="menu-link">
                <i class="menu-icon icon-base ri ri-vip-crown-2-line"></i>
                <div data-i18n="Subscription & Billing">Subscription & Billing</div>
              </a>
            </li>
            <li class="menu-item mt-2">
              <a href="<?= site_url('auth/logout'); ?>" class="menu-link text-danger">
                <i class="menu-icon icon-base ri ri-logout-box-r-line text-danger"></i>
                <div data-i18n="Logout">Logout</div>
              </a>
            </li>
          </ul>
        </aside>
