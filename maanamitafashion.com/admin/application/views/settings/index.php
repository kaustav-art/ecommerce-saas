<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold m-0"><span class="text-muted fw-light">Settings /</span> <?= html_escape($page_heading); ?></h4>
      <small class="text-muted">
        <?php if ($active_tab === 'general'): ?>
          Manage your store's branding, logo & favicon, operating currency, and support contact information.
        <?php elseif ($active_tab === 'shipping'): ?>
          Configure delivery fee rules, express shipping rates, and free shipping thresholds.
        <?php elseif ($active_tab === 'tax'): ?>
          Set up tax calculation rules, tax percentages, and price inclusive/exclusive display options.
        <?php elseif ($active_tab === 'email'): ?>
          Configure transactional email credentials and outgoing SMTP mail server settings.
        <?php elseif ($active_tab === 'sms'): ?>
          Configure SMS gateway credentials for customer notifications and order status alerts.
        <?php elseif ($active_tab === 'seo'): ?>
          Configure search engine metadata, site keywords, and Google Analytics tracking.
        <?php endif; ?>
      </small>
    </div>
  </div>

  <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fa-solid fa-circle-check me-2"></i><?= $this->session->flashdata('success'); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fa-solid fa-triangle-exclamation me-2"></i><?= $this->session->flashdata('error'); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <?php
    $current_currency = $settings['currency_code'] ?? ($settings['currency'] ?? 'USD');
    $current_symbol   = $settings['currency_symbol'] ?? '$';
  ?>

  <div class="row">
    <div class="col-12">
      <!-- 1. General Settings -->
      <?php if ($active_tab === 'general'): ?>
        <?php
          $current_logo = (!empty($settings['site_logo']))
            ? base_url('../website/assets/images/logo/' . $settings['site_logo'])
            : base_url('assets/img/branding/logo.webp');

          $current_fav = (!empty($settings['site_favicon']))
            ? base_url('../website/assets/images/logo/' . $settings['site_favicon'])
            : base_url('assets/img/favicon/codeulas_logo_small.webp');

          $current_currency = $settings['currency_code'] ?? ($settings['currency'] ?? 'USD');
          $current_symbol   = $settings['currency_symbol'] ?? '$';

          $currency_options = [
            ['code' => 'USD', 'symbol' => '$',   'name' => 'USD - US Dollar ($)'],
            ['code' => 'EUR', 'symbol' => '€',   'name' => 'EUR - Euro (€)'],
            ['code' => 'GBP', 'symbol' => '£',   'name' => 'GBP - British Pound (£)'],
            ['code' => 'INR', 'symbol' => '₹',   'name' => 'INR - Indian Rupee (₹)'],
            ['code' => 'CAD', 'symbol' => 'CA$', 'name' => 'CAD - Canadian Dollar (CA$)'],
            ['code' => 'AUD', 'symbol' => 'AU$', 'name' => 'AUD - Australian Dollar (AU$)'],
            ['code' => 'JPY', 'symbol' => '¥',   'name' => 'JPY - Japanese Yen (¥)'],
            ['code' => 'CNY', 'symbol' => '¥',   'name' => 'CNY - Chinese Yuan (¥)'],
            ['code' => 'AED', 'symbol' => 'AED', 'name' => 'AED - UAE Dirham (AED)'],
            ['code' => 'SAR', 'symbol' => 'SAR', 'name' => 'SAR - Saudi Riyal (SAR)'],
            ['code' => 'SGD', 'symbol' => 'S$',  'name' => 'SGD - Singapore Dollar (S$)'],
            ['code' => 'CHF', 'symbol' => 'CHF', 'name' => 'CHF - Swiss Franc (CHF)'],
            ['code' => 'NZD', 'symbol' => 'NZ$', 'name' => 'NZD - New Zealand Dollar (NZ$)'],
            ['code' => 'MYR', 'symbol' => 'RM',  'name' => 'MYR - Malaysian Ringgit (RM)'],
            ['code' => 'THB', 'symbol' => '฿',   'name' => 'THB - Thai Baht (฿)'],
            ['code' => 'PHP', 'symbol' => '₱',   'name' => 'PHP - Philippine Peso (₱)'],
            ['code' => 'IDR', 'symbol' => 'Rp',  'name' => 'IDR - Indonesian Rupiah (Rp)'],
            ['code' => 'PKR', 'symbol' => '₨',   'name' => 'PKR - Pakistani Rupee (₨)'],
            ['code' => 'BDT', 'symbol' => '৳',   'name' => 'BDT - Bangladeshi Taka (৳)'],
            ['code' => 'BRL', 'symbol' => 'R$',  'name' => 'BRL - Brazilian Real (R$)'],
            ['code' => 'ZAR', 'symbol' => 'R',   'name' => 'ZAR - South African Rand (R)'],
            ['code' => 'KRW', 'symbol' => '₩',   'name' => 'KRW - South Korean Won (₩)'],
            ['code' => 'TRY', 'symbol' => '₺',   'name' => 'TRY - Turkish Lira (₺)'],
            ['code' => 'MXN', 'symbol' => 'MX$', 'name' => 'MXN - Mexican Peso (MX$)'],
            ['code' => 'EGP', 'symbol' => 'EGP', 'name' => 'EGP - Egyptian Pound (EGP)'],
            ['code' => 'NGN', 'symbol' => '₦',   'name' => 'NGN - Nigerian Naira (₦)'],
            ['code' => 'KES', 'symbol' => 'KSh', 'name' => 'KES - Kenyan Shilling (KSh)'],
            ['code' => 'QAR', 'symbol' => 'QAR', 'name' => 'QAR - Qatari Riyal (QAR)'],
            ['code' => 'KWD', 'symbol' => 'KWD', 'name' => 'KWD - Kuwaiti Dinar (KWD)'],
            ['code' => 'OMR', 'symbol' => 'OMR', 'name' => 'OMR - Omani Rial (OMR)']
          ];
        ?>
        <div class="card">
          <div class="card-header border-bottom">
            <h5 class="card-title mb-0"><i class="fa-solid fa-sliders me-2 text-primary"></i>General Store Information & Branding</h5>
          </div>
          <div class="card-body pt-4">
            <form action="<?= site_url('settings/general'); ?>" method="POST" enctype="multipart/form-data">
              <input type="hidden" name="setting_group" value="general">
              
              <!-- Store Branding / Logo & Favicon Update Section -->
              <div class="p-3 mb-4 rounded-3 border bg-lightest">
                <h6 class="fw-bold mb-3 text-heading">
                  <i class="fa-solid fa-palette me-2 text-primary"></i>Store Visual Identity & Logo
                </h6>
                <div class="row g-4">
                  <!-- Logo Upload -->
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Store Logo (Website & Admin)</label>
                    <div class="d-flex align-items-center gap-3 p-3 bg-white rounded border mb-2">
                      <div class="p-2 border rounded bg-light text-center" style="min-width: 140px; min-height: 60px; display: flex; align-items: center; justify-content: center;">
                        <img id="preview_logo" src="<?= $current_logo; ?>" alt="Store Logo" style="max-height: 48px; max-width: 160px; object-fit: contain;" onerror="this.src='<?= base_url('assets/img/branding/logo.webp'); ?>'">
                      </div>
                      <div class="flex-grow-1">
                        <small class="text-muted d-block mb-1">Choose a new logo to update both storefront & admin:</small>
                        <input type="file" class="form-control form-control-sm" id="site_logo_file" name="site_logo_file" accept=".webp,.png,.jpg,.jpeg,.svg">
                      </div>
                    </div>
                    <small class="text-muted fs-tiny">Formats: WebP, PNG, SVG, JPG (Max 5MB). Recommended: transparent background, ~250×60px.</small>
                  </div>

                  <!-- Favicon Upload -->
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Store Favicon (Browser Tab Icon)</label>
                    <div class="d-flex align-items-center gap-3 p-3 bg-white rounded border mb-2">
                      <div class="p-2 border rounded bg-light text-center" style="min-width: 60px; min-height: 60px; display: flex; align-items: center; justify-content: center;">
                        <img id="preview_favicon" src="<?= $current_fav; ?>" alt="Favicon" style="width: 36px; height: 36px; object-fit: contain;" onerror="this.src='<?= base_url('assets/img/favicon/codeulas_logo_small.webp'); ?>'">
                      </div>
                      <div class="flex-grow-1">
                        <small class="text-muted d-block mb-1">Choose a browser icon for website & admin tabs:</small>
                        <input type="file" class="form-control form-control-sm" id="site_favicon_file" name="site_favicon_file" accept=".webp,.png,.ico,.jpg,.jpeg,.svg">
                      </div>
                    </div>
                    <small class="text-muted fs-tiny">Formats: WebP, PNG, ICO, SVG (Max 2MB). Recommended: 32×32px or 64×64px square.</small>
                  </div>
                </div>
              </div>

              <!-- Store Details -->
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">Store Brand Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="site_name" value="<?= html_escape($settings['site_name'] ?? ''); ?>" required placeholder="e.g. Store Brand Name">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">Support Email Address <span class="text-danger">*</span></label>
                  <input type="email" class="form-control" name="admin_email" value="<?= html_escape($settings['admin_email'] ?? ($settings['site_email'] ?? '')); ?>" required placeholder="support@example.com">
                </div>
              </div>

              <!-- Currency Dropdown & Symbol -->
              <div class="row align-items-end">
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">Store Currency <span class="text-danger">*</span></label>
                  <select class="form-select" id="currency_code" name="currency_code" required>
                    <?php foreach ($currency_options as $cur): ?>
                      <option value="<?= $cur['code']; ?>" data-symbol="<?= html_escape($cur['symbol']); ?>" <?= (strtoupper($current_currency) === $cur['code']) ? 'selected' : ''; ?>>
                        <?= html_escape($cur['name']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <small class="text-muted">Select your primary store operating currency.</small>
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label fw-semibold">Currency Symbol <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="currency_symbol" name="currency_symbol" value="<?= html_escape($current_symbol); ?>" required placeholder="$">
                  <small class="text-muted">Symbol prefix for prices.</small>
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label fw-semibold d-block">Price Format Preview</label>
                  <div class="p-2 border rounded bg-light text-center">
                    <span id="currency_preview" class="badge bg-label-primary fs-6 fw-bold">
                      <?= html_escape($current_symbol); ?> 1,250.00
                    </span>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">Support Phone Number</label>
                  <input type="text" class="form-control" name="support_phone" value="<?= html_escape($settings['support_phone'] ?? ($settings['site_phone'] ?? '')); ?>" placeholder="+1 (800) 555-0199">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">Store Timezone</label>
                  <input type="text" class="form-control" name="timezone" value="<?= html_escape($settings['timezone'] ?? 'America/New_York'); ?>" placeholder="e.g. America/New_York, Asia/Kolkata">
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Physical Store / HQ Address</label>
                <textarea class="form-control" name="store_address" rows="3" placeholder="Street, City, State, ZIP, Country"><?= html_escape($settings['store_address'] ?? ($settings['site_address'] ?? '')); ?></textarea>
              </div>

              <div class="pt-2 border-top mt-3">
                <button type="submit" class="btn btn-primary me-2">
                  <i class="fa-solid fa-floppy-disk me-1"></i> Save General Settings
                </button>
              </div>
            </form>
          </div>
        </div>
      <?php endif; ?>

      <!-- 2. Shipping Settings -->
      <?php if ($active_tab === 'shipping'): ?>
        <?php
          $shipping_charges = [];
          if (!empty($settings['shipping_charges'])) {
              $decoded = json_decode($settings['shipping_charges'], true);
              if (is_array($decoded)) {
                  $shipping_charges = $decoded;
              }
          }
          if (empty($shipping_charges)) {
              $default_val = isset($settings['shipping_flat_rate']) ? (float)$settings['shipping_flat_rate'] : 50.00;
              $shipping_charges = [
                  ['name' => 'Courier Charges', 'value' => $default_val]
              ];
          }
        ?>
        <div class="card">
          <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <div>
              <h5 class="card-title mb-0"><i class="fa-solid fa-truck-fast me-2 text-primary"></i>Shipping Rates & Delivery Rules</h5>
              <small class="text-muted">Configure dynamic shipping fees and dispatch notice for single vendor store.</small>
            </div>
            <span class="badge bg-label-primary px-3 py-2 fs-tiny fw-bold">Dynamic Shipping Charges</span>
          </div>
          <div class="card-body pt-4">
            <form action="<?= site_url('settings/shipping'); ?>" method="POST" id="shipping-settings-form">
              <input type="hidden" name="setting_group" value="shipping">
              
              <!-- Dynamic Shipping Charges Repeater -->
              <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div>
                    <h6 class="fw-bold mb-1 text-heading">
                      <i class="fa-solid fa-layer-group me-2 text-primary"></i>Shipping Charges Configuration
                    </h6>
                    <small class="text-muted">Define custom charges (e.g. Courier Charges, Handling Fee). Currency (<strong><?= html_escape($current_symbol); ?></strong>) is dynamically loaded from General Settings.</small>
                  </div>
                  <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-shipping-charge">
                    <i class="fa-solid fa-plus me-1"></i> Add More Option
                  </button>
                </div>

                <div class="table-responsive border rounded-3 bg-white mb-2">
                  <table class="table table-hover align-middle mb-0 no-datatable" id="shipping-charges-table">
                    <thead class="table-light">
                      <tr>
                        <th style="min-width: 250px;">Charge Name <span class="text-danger">*</span></th>
                        <th style="min-width: 200px; width: 260px;">Charge Value (<?= html_escape($current_symbol); ?>) <span class="text-danger">*</span></th>
                        <th style="width: 80px; text-align: center;">Action</th>
                      </tr>
                    </thead>
                    <tbody id="shipping-charges-tbody">
                      <?php foreach ($shipping_charges as $charge): ?>
                        <tr class="shipping-charge-row">
                          <td>
                            <input type="text" class="form-control form-control-sm charge-name-input" name="shipping_charge_names[]" value="<?= html_escape($charge['name'] ?? ''); ?>" placeholder="e.g. Courier Charges, Handling Fee" required>
                          </td>
                          <td>
                            <div class="input-group input-group-sm">
                              <span class="input-group-text fw-bold bg-light"><?= html_escape($current_symbol); ?></span>
                              <input type="number" step="0.01" min="0" class="form-control charge-value-input" name="shipping_charge_values[]" value="<?= html_escape($charge['value'] ?? '0.00'); ?>" placeholder="50.00" required>
                            </div>
                          </td>
                          <td class="text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-charge" title="Remove this charge">
                              <i class="fa-solid fa-trash-can"></i>
                            </button>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                      <tr>
                        <td class="fw-bold text-end">Total Shipping Fee:</td>
                        <td colspan="2">
                          <span class="badge bg-label-primary fs-6 fw-bold" id="total-shipping-preview">
                            <?= html_escape($current_symbol); ?> 0.00
                          </span>
                        </td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
                <small class="text-muted"><i class="fa-solid fa-circle-info me-1"></i> These individual charges will be itemized and displayed as a breakdown under "Price Details" on the cart page.</small>
              </div>

              <!-- Estimated Delivery & Dispatch Notice (Unchanged) -->
              <div class="mb-3">
                <label class="form-label fw-semibold">Estimated Delivery & Dispatch Notice</label>
                <input type="text" class="form-control" name="shipping_dispatch_note" value="<?= html_escape($settings['shipping_dispatch_note'] ?? 'Dispatched within 24-48 hours with courier tracking.'); ?>" placeholder="e.g. Dispatched within 24-48 hours with courier tracking.">
                <small class="text-muted">Customer-facing message displayed on cart, checkout, and invoice pages.</small>
              </div>

              <div class="pt-2 border-top mt-3">
                <button type="submit" class="btn btn-primary me-2">
                  <i class="fa-solid fa-floppy-disk me-1"></i> Save Shipping Settings
                </button>
              </div>
            </form>
          </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
          const currencySymbol = <?= json_encode($current_symbol); ?>;
          const tbody = document.getElementById('shipping-charges-tbody');
          const btnAdd = document.getElementById('btn-add-shipping-charge');
          const totalPreview = document.getElementById('total-shipping-preview');

          function calculateShippingTotal() {
            if (!tbody || !totalPreview) return;
            let sum = 0;
            tbody.querySelectorAll('.charge-value-input').forEach(function(input) {
              const val = parseFloat(input.value) || 0;
              sum += val;
            });
            totalPreview.textContent = currencySymbol + ' ' + sum.toFixed(2);
          }

          function createRow(name, value) {
            name = name || '';
            value = value || '0.00';
            const tr = document.createElement('tr');
            tr.className = 'shipping-charge-row';
            tr.innerHTML = `
              <td>
                <input type="text" class="form-control form-control-sm charge-name-input" name="shipping_charge_names[]" value="${escapeHtml(name)}" placeholder="e.g. Courier Charges, Handling Fee" required>
              </td>
              <td>
                <div class="input-group input-group-sm">
                  <span class="input-group-text fw-bold bg-light">${escapeHtml(currencySymbol)}</span>
                  <input type="number" step="0.01" min="0" class="form-control charge-value-input" name="shipping_charge_values[]" value="${escapeHtml(value)}" placeholder="50.00" required>
                </div>
              </td>
              <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm btn-remove-charge" title="Remove this charge">
                  <i class="fa-solid fa-trash-can"></i>
                </button>
              </td>
            `;
            tbody.appendChild(tr);
            bindRowEvents(tr);
            calculateShippingTotal();
          }

          function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
          }

          function bindRowEvents(tr) {
            const btnRemove = tr.querySelector('.btn-remove-charge');
            if (btnRemove) {
              btnRemove.addEventListener('click', function() {
                const allRows = tbody.querySelectorAll('.shipping-charge-row');
                if (allRows.length <= 1) {
                  tr.querySelector('.charge-name-input').value = '';
                  tr.querySelector('.charge-value-input').value = '0.00';
                  calculateShippingTotal();
                  return;
                }
                tr.remove();
                calculateShippingTotal();
              });
            }

            const valInput = tr.querySelector('.charge-value-input');
            if (valInput) {
              valInput.addEventListener('input', calculateShippingTotal);
            }
          }

          if (tbody) {
            tbody.querySelectorAll('.shipping-charge-row').forEach(bindRowEvents);
            calculateShippingTotal();
          }

          if (btnAdd) {
            btnAdd.addEventListener('click', function() {
              createRow('', '0.00');
            });
          }
        });
        </script>
      <?php endif; ?>

      <!-- 3. Tax Settings -->
      <?php if ($active_tab === 'tax'): ?>
        <div class="card">
          <div class="card-header border-bottom">
            <h5 class="card-title mb-0"><i class="fa-solid fa-receipt me-2 text-primary"></i>Tax Calculation Rules</h5>
          </div>
          <div class="card-body pt-4">
            <form action="<?= site_url('settings/tax'); ?>" method="POST">
              <input type="hidden" name="setting_group" value="tax">
              
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">Enable Tax Calculation</label>
                  <select class="form-select" name="tax_enabled">
                    <option value="1" <?= (($settings['tax_enabled'] ?? '1') == '1') ? 'selected' : ''; ?>>Enabled</option>
                    <option value="0" <?= (($settings['tax_enabled'] ?? '') == '0') ? 'selected' : ''; ?>>Disabled</option>
                  </select>
                  <small class="text-muted">When enabled, tax is calculated on eligible checkout items.</small>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">Default Standard Tax Rate (%) <span class="text-danger">*</span></label>
                  <input type="number" step="0.01" class="form-control" name="tax_rate" value="<?= html_escape($settings['tax_rate'] ?? '8.5'); ?>" required>
                  <small class="text-muted">Standard percentage applied to taxable orders (e.g. 8.5 for 8.5%).</small>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Tax Price Display Mode</label>
                <select class="form-select" name="tax_inclusive">
                  <option value="0" <?= (($settings['tax_inclusive'] ?? '0') == '0') ? 'selected' : ''; ?>>Prices are Exclusive of Tax (Tax calculated & added at checkout)</option>
                  <option value="1" <?= (($settings['tax_inclusive'] ?? '') == '1') ? 'selected' : ''; ?>>Prices are Inclusive of Tax (Tax already included in product price)</option>
                </select>
                <small class="text-muted">When <strong>Inclusive</strong>: product prices on the storefront and cart include tax (e.g. a ₹100 product displays as ₹110 with 10% tax), and MRP displays "MRP (incl. of all taxes)". When <strong>Exclusive</strong>: product prices display the base price (₹100), and tax (₹10) is added as a separate breakdown line under Price Details on the cart page.</small>
              </div>

              <div class="pt-2 border-top mt-3">
                <button type="submit" class="btn btn-primary me-2">
                  <i class="fa-solid fa-floppy-disk me-1"></i> Save Tax Settings
                </button>
              </div>
            </form>
          </div>
        </div>
      <?php endif; ?>

      <!-- 4. Email (SMTP) Settings -->
      <?php if ($active_tab === 'email'): ?>
        <div class="card">
          <div class="card-header border-bottom">
            <h5 class="card-title mb-0"><i class="fa-solid fa-envelope me-2 text-primary"></i>Outgoing Mail (SMTP) Configuration</h5>
          </div>
          <div class="card-body pt-4">
            <form action="<?= site_url('settings/email'); ?>" method="POST">
              <input type="hidden" name="setting_group" value="email">
              
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">SMTP Host Server</label>
                  <input type="text" class="form-control font-monospace" name="smtp_host" value="<?= html_escape($settings['smtp_host'] ?? 'smtp.mailtrap.io'); ?>" placeholder="e.g. smtp.gmail.com, smtp.sendgrid.net">
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label fw-semibold">SMTP Port</label>
                  <input type="number" class="form-control" name="smtp_port" value="<?= html_escape($settings['smtp_port'] ?? '587'); ?>" placeholder="587, 465, or 25">
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label fw-semibold">Encryption</label>
                  <select class="form-select" name="smtp_crypto">
                    <option value="tls" <?= (($settings['smtp_crypto'] ?? 'tls') === 'tls') ? 'selected' : ''; ?>>TLS (Recommended)</option>
                    <option value="ssl" <?= (($settings['smtp_crypto'] ?? '') === 'ssl') ? 'selected' : ''; ?>>SSL</option>
                    <option value="" <?= (empty($settings['smtp_crypto'])) ? 'selected' : ''; ?>>None</option>
                  </select>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">SMTP Username</label>
                  <input type="text" class="form-control" name="smtp_user" value="<?= html_escape($settings['smtp_user'] ?? ''); ?>" placeholder="SMTP login username">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">SMTP Password</label>
                  <input type="password" class="form-control" name="smtp_pass" value="<?= html_escape($settings['smtp_pass'] ?? ''); ?>" placeholder="••••••••••••">
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">From Email Address</label>
                  <input type="email" class="form-control" name="from_email" value="<?= html_escape($settings['from_email'] ?? 'orders@example.com'); ?>" placeholder="orders@example.com">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">From Sender Name</label>
                  <input type="text" class="form-control" name="from_name" value="<?= html_escape($settings['from_name'] ?? ($settings['site_name'] ?? 'Store')); ?>" placeholder="Store Brand Name">
                </div>
              </div>

              <div class="pt-2 border-top mt-3">
                <button type="submit" class="btn btn-primary me-2">
                  <i class="fa-solid fa-floppy-disk me-1"></i> Save Email Settings
                </button>
              </div>
            </form>
          </div>
        </div>
      <?php endif; ?>

      <!-- 5. SMS Settings -->
      <?php if ($active_tab === 'sms'): ?>
        <div class="card">
          <div class="card-header border-bottom">
            <h5 class="card-title mb-0"><i class="fa-solid fa-comment-sms me-2 text-primary"></i>SMS Notification Gateway</h5>
          </div>
          <div class="card-body pt-4">
            <form action="<?= site_url('settings/sms'); ?>" method="POST">
              <input type="hidden" name="setting_group" value="sms">
              
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">SMS Provider Gateway</label>
                  <select class="form-select" name="sms_gateway">
                    <option value="twilio" <?= (($settings['sms_gateway'] ?? 'twilio') === 'twilio') ? 'selected' : ''; ?>>Twilio</option>
                    <option value="msg91" <?= (($settings['sms_gateway'] ?? '') === 'msg91') ? 'selected' : ''; ?>>MSG91</option>
                    <option value="nexmo" <?= (($settings['sms_gateway'] ?? '') === 'nexmo') ? 'selected' : ''; ?>>Vonage / Nexmo</option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">Sender Phone Number / Sender ID</label>
                  <input type="text" class="form-control" name="sms_sender_number" value="<?= html_escape($settings['sms_sender_number'] ?? '+18005550199'); ?>" placeholder="+1 (800) 555-0199 or ALPHANUMERIC">
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">Account SID / API Key</label>
                  <input type="text" class="form-control font-monospace" name="sms_account_sid" value="<?= html_escape($settings['sms_account_sid'] ?? ''); ?>" placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">Auth Token / API Secret</label>
                  <input type="password" class="form-control font-monospace" name="sms_auth_token" value="<?= html_escape($settings['sms_auth_token'] ?? ''); ?>" placeholder="••••••••••••">
                </div>
              </div>

              <div class="pt-2 border-top mt-3">
                <button type="submit" class="btn btn-primary me-2">
                  <i class="fa-solid fa-floppy-disk me-1"></i> Save SMS Settings
                </button>
              </div>
            </form>
          </div>
        </div>
      <?php endif; ?>

      <!-- 6. SEO Settings -->
      <?php if ($active_tab === 'seo'): ?>
        <div class="card">
          <div class="card-header border-bottom">
            <h5 class="card-title mb-0"><i class="fa-solid fa-globe me-2 text-primary"></i>Search Engine Optimization (SEO) & Analytics</h5>
          </div>
          <div class="card-body pt-4">
            <form action="<?= site_url('settings/seo'); ?>" method="POST">
              <input type="hidden" name="setting_group" value="seo">
              
              <div class="mb-3">
                <label class="form-label fw-semibold">Default Meta Title</label>
                <input type="text" class="form-control" name="meta_title" value="<?= html_escape($settings['meta_title'] ?? (($settings['site_name'] ?? 'Store') . ' - Multipurpose Modern eCommerce Platform')); ?>" placeholder="Store Title">
                <small class="text-muted">Primary title tag for search engines and browser tabs.</small>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Default Meta Description</label>
                <textarea class="form-control" name="meta_description" rows="3" placeholder="Brief summary of your store for Google snippet results"><?= html_escape($settings['meta_description'] ?? ''); ?></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Meta Keywords</label>
                <input type="text" class="form-control" name="meta_keywords" value="<?= html_escape($settings['meta_keywords'] ?? 'fashion, electronics, grocery, online shopping'); ?>" placeholder="comma, separated, keywords">
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Google Analytics / GTM Tracking ID</label>
                <input type="text" class="form-control font-monospace" name="google_analytics_id" value="<?= html_escape($settings['google_analytics_id'] ?? 'G-XXXXXXXXXX'); ?>" placeholder="G-XXXXXXXXXX or UA-XXXXXXXX-X">
              </div>

              <div class="pt-2 border-top mt-3">
                <button type="submit" class="btn btn-primary me-2">
                  <i class="fa-solid fa-floppy-disk me-1"></i> Save SEO Settings
                </button>
              </div>
            </form>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Live Currency Dropdown Preview & Symbol Synchronization
  const currencySelect = document.getElementById('currency_code');
  const currencySymbolInput = document.getElementById('currency_symbol');
  const currencyPreview = document.getElementById('currency_preview');

  function updateCurrencyPreview() {
    if (!currencyPreview || !currencySymbolInput) return;
    const sym = currencySymbolInput.value || '$';
    currencyPreview.textContent = sym + ' 1,250.00';
  }

  if (currencySelect && currencySymbolInput) {
    currencySelect.addEventListener('change', function() {
      const selectedOption = this.options[this.selectedIndex];
      const symbol = selectedOption.getAttribute('data-symbol');
      if (symbol) {
        currencySymbolInput.value = symbol;
      }
      updateCurrencyPreview();
    });
  }

  if (currencySymbolInput) {
    currencySymbolInput.addEventListener('input', updateCurrencyPreview);
  }

  // Live Logo File Preview
  const logoInput = document.getElementById('site_logo_file');
  const logoPreview = document.getElementById('preview_logo');
  if (logoInput && logoPreview) {
    logoInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(evt) {
          logoPreview.src = evt.target.result;
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // Live Favicon File Preview
  const favInput = document.getElementById('site_favicon_file');
  const favPreview = document.getElementById('preview_favicon');
  if (favInput && favPreview) {
    favInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(evt) {
          favPreview.src = evt.target.result;
        };
        reader.readAsDataURL(file);
      }
    });
  }
});
</script>
