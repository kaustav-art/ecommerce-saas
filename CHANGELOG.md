# Project Change Log & Database Audit

## Project: Multipurpose E-Commerce System
- **Framework**: CodeIgniter 3 (MVC Architecture)
- **Database**: MySQL (`ecommerce`)
- **Payment Gateways**: Stripe, Razorpay, PayU, and Cash on Delivery (COD)
- **Naming Rule**: Controllers and Models strictly start with lowercase letters (e.g. `dashboard.php`, `products.php`, `cart.php`, `user_model.php`, `payment_model.php`).
- **Modules**:
  - `website/`: Customer-facing storefront with catalog, categories, cart, checkout, payment gateways, customer account, order tracking.
  - `admin/`: Operator management panel with Role-Based Access Control (RBAC), catalog management, orders, customers, staff operators, roles, gateways, and settings.

---

## 1. Database Changes & Schema

### Database Configuration
- **Database Name**: `ecommerce`
- **Schema File**: `database/ecommerce.sql`
- **Tables Created (16 Tables)**:
  1. `roles`: Role definitions for Role-Based Access Control (RBAC) (`id`, `name`, `slug`, `description`, `is_system`, `created_at`, `updated_at`).
  2. `permissions`: System capabilities/slugs grouped by module (`id`, `module`, `name`, `slug`, `created_at`).
  3. `role_permissions`: Pivot table linking roles to granular permissions (`id`, `role_id`, `permission_id`).
  4. `admins`: Administrator and Operator user accounts with role assignment, status, credentials (`id`, `role_id`, `name`, `email`, `password`, `phone`, `avatar`, `status`, `last_login`, `created_at`, `updated_at`).
  5. `users`: Storefront customer accounts (`id`, `first_name`, `last_name`, `email`, `password`, `phone`, `avatar`, `status`, `created_at`, `updated_at`).
  6. `user_addresses`: Customer billing and shipping address book (`id`, `user_id`, `type`, `first_name`, `last_name`, `company`, `address_1`, `address_2`, `city`, `state`, `postcode`, `country`, `phone`, `is_default`, `created_at`).
  7. `categories`: Multipurpose product categories with slug, image, sorting, and featured flags (`id`, `parent_id`, `name`, `slug`, `description`, `image`, `is_featured`, `sort_order`, `status`, `created_at`, `updated_at`).
  8. `brands`: Product brand catalog (`id`, `name`, `slug`, `logo`, `status`, `created_at`).
  9. `products`: Comprehensive product catalog (`id`, `category_id`, `brand_id`, `title`, `slug`, `sku`, `price`, `sale_price`, `stock_quantity`, `stock_status`, `short_description`, `description`, `main_image`, `gallery_images`, `is_featured`, `is_trending`, `is_new`, `rating`, `reviews_count`, `status`, `created_at`, `updated_at`).
  10. `orders`: Customer orders tracking amounts, shipping/billing info, payment gateway, transaction ids, payment status, fulfillment status (`id`, `order_number`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `billing_address`, `subtotal`, `discount_amount`, `shipping_fee`, `tax_amount`, `total_amount`, `currency`, `payment_method`, `payment_status`, `payment_transaction_id`, `payment_details`, `order_status`, `notes`, `created_at`, `updated_at`).
  11. `order_items`: Order line items with price, quantity, snapshots (`id`, `order_id`, `product_id`, `product_title`, `product_sku`, `product_image`, `price`, `quantity`, `total`, `created_at`).
  12. `coupons`: Promo and discount vouchers (`id`, `code`, `discount_type`, `discount_value`, `min_spend`, `usage_limit`, `used_count`, `expires_at`, `status`, `created_at`).
  13. `reviews`: Customer reviews and star ratings per product (`id`, `product_id`, `user_id`, `customer_name`, `customer_email`, `rating`, `review`, `status`, `created_at`).
  14. `wishlists`: Customer product wishlists (`id`, `user_id`, `product_id`, `created_at`).
  15. `settings`: Global site and configuration parameters (`id`, `setting_key`, `setting_value`, `setting_group`).
  16. `payment_gateways`: Gateway credential configuration for Stripe, Razorpay, PayU, and COD (`id`, `gateway_code`, `gateway_name`, `is_active`, `environment`, `credentials`, `updated_at`).

### Seeded Credentials
- **Super Administrator**: `admin@ecommerce.com` / `admin123` (Full unrestricted system control)
- **Operations Manager**: `manager@ecommerce.com` / `manager123` (Catalog and orders management)
- **Storefront Customer**: `john@example.com` / `user123`
- **Customer Address**: `742 Evergreen Terrace, Springfield, OR 97477`
- **Categories**: Womens Fashion, Mens Collection, Consumer Electronics, Sneakers & Shoes, Beauty & Skincare, Home & Furniture.
- **Payment Gateways**:
  - `stripe`: Key configurations for test/live, publishable key, secret key, webhook secret.
  - `razorpay`: Key ID, Key Secret, test/live mode.
  - `payu`: Merchant Key, Merchant Salt, Test URL (`https://test.payu.in/_payment`), Live URL (`https://secure.payu.in/_payment`).
  - `cod`: Cash on delivery instructions.

---

## 2. Admin Panel Files (`admin/`)

### Configuration & Core Extensions
- `admin/.htaccess`: Mod-rewrite clean URL rewriting.
- `admin/application/config/config.php`: Dynamic `base_url` resolution (supporting Apache and CLI execution), empty `index_page`, and custom encryption key.
- `admin/application/config/database.php`: MySQL database connection configured for `ecommerce`.
- `admin/application/config/autoload.php`: Autoloaded `database`, `session`, `form_validation` libraries and `url`, `file`, `form`, `html`, `text`, `string`, `security` helpers.
- `admin/application/config/routes.php`: Default route set to `dashboard`, with aliases for `auth/login` and `auth/logout`.
- `admin/application/core/MY_Controller.php`: Base Admin Controller with RBAC checking (`can()`, `require_permission()`), session verification, layout rendering helpers (`render()`, `render_blank()`), and JSON output helper.
- `admin/application/core/MY_Loader.php`: Extended loader providing `can()` and `is_logged_in()` directly to all view files.

### Models (All names start with small letter)
- `admin/application/models/admin_model.php`: Administrator and staff CRUD, email lookup, last login tracker.
- `admin/application/models/role_model.php`: RBAC role management, granular permission assignment, module-grouped permissions.
- `admin/application/models/product_model.php`: Catalog product CRUD, stock tracker, low-stock alerts.
- `admin/application/models/category_model.php`: Category management and product counts.
- `admin/application/models/brand_model.php`: Brand management and product counts.
- `admin/application/models/order_model.php`: Sales metrics calculation, order status updates, recent orders fetching.
- `admin/application/models/customer_model.php`: Customer profile, orders history, status toggling.
- `admin/application/models/setting_model.php`: Global store settings and Stripe/Razorpay/PayU configuration.

### Controllers (All names start with small letter)
- `admin/application/controllers/auth.php`: Login, logout, bcrypt authentication, session setup with role permissions.
- `admin/application/controllers/dashboard.php`: Analytics metrics, revenue stats, low-stock counts, recent orders.
- `admin/application/controllers/products.php`: Product catalog CRUD, image paths, stock management, filter/search.
- `admin/application/controllers/categories.php`: Category CRUD.
- `admin/application/controllers/brands.php`: Brand CRUD.
- `admin/application/controllers/orders.php`: Order listing, order view, status and payment updates, printable invoice.
- `admin/application/controllers/customers.php`: Customer management and ban/activate status toggling.
- `admin/application/controllers/admins.php`: Staff operator management and role assignment.
- `admin/application/controllers/roles.php`: Role creation and permission matrix management.
- `admin/application/controllers/settings.php`: Store configuration and Stripe, Razorpay, PayU gateway credentials.

### Layouts & Views
- `admin/application/views/layouts/header.php`: HTML header, fonts, CSS dependencies.
- `admin/application/views/layouts/sidebar.php`: Vertical navigation with RBAC permission filtering.
- `admin/application/views/layouts/navbar.php`: Top navbar with operator role badge, profile menu, logout.
- `admin/application/views/layouts/footer.php`: Admin footer and core JS libraries.
- `admin/application/views/layouts/auth_header.php`: Clean authentication layout header.
- `admin/application/views/layouts/auth_footer.php`: Authentication scripts.
- `admin/application/views/auth/login.php`: Materialize-style login view with demo account pills.
- `admin/application/views/dashboard/index.php`: Metrics cards, revenue display, recent orders, gateway status.
- `admin/application/views/products/index.php`: Product list table with filters and search.
- `admin/application/views/products/add.php`: Product creation form.
- `admin/application/views/products/edit.php`: Product editing form.
- `admin/application/views/categories/index.php`: Category management view.
- `admin/application/views/brands/index.php`: Brand management view.
- `admin/application/views/orders/index.php`: Order management list with status filters.
- `admin/application/views/orders/view.php`: Order item detail, shipping info, status update controls.
- `admin/application/views/orders/invoice.php`: Print-ready invoice.
- `admin/application/views/customers/index.php`: Customers list table.
- `admin/application/views/customers/view.php`: Customer profile, addresses, order history.
- `admin/application/views/admins/index.php`: Staff operator accounts table.
- `admin/application/views/admins/add.php`: Create operator account.
- `admin/application/views/admins/edit.php`: Edit operator account and role.
- `admin/application/views/roles/index.php`: Roles overview.
- `admin/application/views/roles/edit.php`: Role permission matrix with module checkboxes.
- `admin/application/views/settings/index.php`: Store name, phone, address, currency, tax, shipping rates.
- `admin/application/views/settings/payment.php`: Stripe, Razorpay, PayU API keys, secrets, test/live toggles.

### Assets
- `admin/assets/`: Copied from `admin-template/assets/` (complete CSS, JS, fonts, images, vendors).

---

## 3. Storefront Website Files (`website/`)

### Configuration & Core Extensions
- `website/.htaccess`: Mod-rewrite clean URL rewriting.
- `website/application/config/config.php`: Dynamic `base_url` resolution, empty `index_page`, and custom encryption key.
- `website/application/config/database.php`: MySQL database connection configured for `ecommerce`.
- `website/application/config/autoload.php`: Autoloaded `database`, `session`, `form_validation` libraries and helpers.
- `website/application/config/routes.php`: Clean SEO routes for catalog, categories, products, cart, checkout, payments, account, order tracking, and static pages.
- `website/application/core/MY_Controller.php`: Base storefront controller providing dynamic cart counter, categories menu, store settings, customer session, and layout renderer.
- `website/application/core/MY_Loader.php`: Extended loader providing `is_logged_in()` to all storefront views.

### Models (All names start with small letter)
- `website/application/models/setting_model.php`: Store configuration and active payment gateways.
- `website/application/models/user_model.php`: Customer registration, login, profile, addresses.
- `website/application/models/category_model.php`: Category catalog and featured categories.
- `website/application/models/brand_model.php`: Brand catalog.
- `website/application/models/product_model.php`: Product catalog with filtering (category, brand, price min/max, search, sorting), detail with gallery, related products, reviews.
- `website/application/models/cart_model.php`: Shopping cart session management, item add/update/remove, promo coupon verification, shipping and tax calculation.
- `website/application/models/order_model.php`: Order creation with item batching, stock reduction, order tracking, payment status updating.
- `website/application/models/payment_model.php`: Gateway processing:
  - **Stripe**: PaymentIntent initialization and API integration.
  - **Razorpay**: Order creation and HMAC-SHA256 signature verification.
  - **PayU**: SHA-512 payment hash generation and return hash verification.

### Controllers (All names start with small letter)
- `website/application/controllers/home.php`: Homepage with hero banner, service perks, featured categories, trending products, promo banners, brands.
- `website/application/controllers/shop.php`: Catalog view with category filter, brand filter, price range, search, and sorting.
- `website/application/controllers/product.php`: Product detail, thumbnail gallery, customer reviews, review submission, related products.
- `website/application/controllers/cart.php`: Cart overview, add item, update quantity, remove item, apply coupon.
- `website/application/controllers/checkout.php`: Customer details, address book selection, payment method selection, order creation.
- `website/application/controllers/payment.php`:
  - `stripe()` & `stripe_confirm()`: Stripe credit/debit card payment.
  - `razorpay()` & `razorpay_verify()`: Razorpay popup launcher and signature validation.
  - `payu()` & `payu_return()`: PayU checkout form with SHA-512 hash and return verification.
  - `success()`: Order confirmation screen.
  - `failure()`: Payment failure and retry screen.
- `website/application/controllers/auth.php`: Customer login, registration, logout.
- `website/application/controllers/account.php`: Customer dashboard, order history, order details, saved addresses.
- `website/application/controllers/order.php`: Order tracking by Order Reference Number and Email.
- `website/application/controllers/page.php`: About Us, Contact Us, and FAQs.

### Layouts & Views
- `website/application/views/layouts/header.php`: HTML head, fonts, Bootstrap and theme CSS.
- `website/application/views/layouts/topbar.php`: Store phone, email, order tracking link, admin portal shortcut.
- `website/application/views/layouts/navbar.php`: Responsive navbar with brand logo, categories menu, search, user dropdown, live cart count badge.
- `website/application/views/layouts/footer.php`: Links, category list, payment badges, SSL guarantees, scripts.
- `website/application/views/layouts/modals.php`: Search modal and mobile menu offcanvas.
- `website/application/views/home/index.php`: Complete homepage view.
- `website/application/views/shop/index.php`: Catalog grid with sidebar filters and sort options.
- `website/application/views/product/detail.php`: Product detail view with gallery, specifications, reviews.
- `website/application/views/cart/index.php`: Shopping cart table with coupon input and totals.
- `website/application/views/checkout/index.php`: Checkout address and payment gateway selector.
- `website/application/views/payment/stripe.php`: Stripe payment screen.
- `website/application/views/payment/razorpay.php`: Razorpay checkout launcher.
- `website/application/views/payment/payu.php`: PayU redirect form.
- `website/application/views/payment/success.php`: Order confirmation and summary.
- `website/application/views/payment/failure.php`: Payment failure and retry view.
- `website/application/views/auth/login.php`: Customer login.
- `website/application/views/auth/register.php`: Customer registration.
- `website/application/views/account/dashboard.php`: Customer account dashboard.
- `website/application/views/account/orders.php`: Customer order history.
- `website/application/views/account/order_detail.php`: Customer order detail.
- `website/application/views/account/address.php`: Customer address book.
- `website/application/views/order/track.php`: Order tracking view with progress bar.
- `website/application/views/pages/about.php`: About Us view.
- `website/application/views/pages/contact.php`: Contact Us view.
- `website/application/views/pages/faq.php`: FAQs accordion view.

### Assets
- `website/assets/`: Copied from `website/templates/` (`css/`, `fonts/`, `images/`, `js/`).
