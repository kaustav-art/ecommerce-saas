<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold m-0"><span class="text-muted fw-light">Settings /</span> Payment Gateways</h4>
      <small class="text-muted">Select the single online payment gateway for your store and configure Cash on Delivery (COD)</small>
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
    $stripe = null;
    $razorpay = null;
    $payu = null;
    $cod = null;
    foreach ($gateways as $g) {
      if ($g['gateway_code'] === 'stripe') $stripe = $g;
      elseif ($g['gateway_code'] === 'razorpay') $razorpay = $g;
      elseif ($g['gateway_code'] === 'payu') $payu = $g;
      elseif ($g['gateway_code'] === 'cod') $cod = $g;
    }
    $curr_online = $active_online_gateway ?? 'none';
  ?>

  <!-- Primary Online Payment Gateway Selector (Only ONE online gateway allowed) -->
  <div class="card mb-4 shadow-sm border-0 border-start <?= ($curr_online === 'none') ? 'border-warning' : 'border-primary'; ?> border-4">
    <div class="card-body">
      <div class="row align-items-center">
        <div class="col-12 col-lg-7 mb-3 mb-lg-0">
          <h5 class="card-title mb-1 <?= ($curr_online === 'none') ? 'text-warning' : 'text-primary'; ?> fw-bold">
            <i class="fa-solid fa-building-columns me-2"></i>Active Online Payment Gateway
          </h5>
          <p class="text-muted small mb-2">
            Only <strong>one</strong> online payment gateway is active at a time between <strong>Stripe</strong>, <strong>Razorpay</strong>, and <strong>PayU</strong>. Choose Stripe if you are operating internationally/foreign, or Razorpay for domestic UPI & cards. Select <strong>Disable All Online Payments</strong> for COD only.
          </p>
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <?php if ($curr_online === 'none'): ?>
              <span class="badge bg-warning text-dark">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> Online Payments: DISABLED (COD Only)
              </span>
            <?php else: ?>
              <span class="badge <?= ($curr_online === 'razorpay') ? 'bg-success' : 'bg-label-secondary'; ?>">
                <i class="fa-solid <?= ($curr_online === 'razorpay') ? 'fa-check' : 'fa-minus'; ?> me-1"></i> Razorpay: <?= ($curr_online === 'razorpay') ? 'ACTIVE ONLINE' : 'Inactive'; ?>
              </span>
              <span class="badge <?= ($curr_online === 'stripe') ? 'bg-primary' : 'bg-label-secondary'; ?>">
                <i class="fa-solid <?= ($curr_online === 'stripe') ? 'fa-check' : 'fa-minus'; ?> me-1"></i> Stripe: <?= ($curr_online === 'stripe') ? 'ACTIVE ONLINE' : 'Inactive'; ?>
              </span>
              <span class="badge <?= ($curr_online === 'payu') ? 'bg-info' : 'bg-label-secondary'; ?>">
                <i class="fa-solid <?= ($curr_online === 'payu') ? 'fa-check' : 'fa-minus'; ?> me-1"></i> PayU: <?= ($curr_online === 'payu') ? 'ACTIVE ONLINE' : 'Inactive'; ?>
              </span>
            <?php endif; ?>
            <span class="badge <?= ($cod && $cod['is_active']) ? 'bg-label-success' : 'bg-label-secondary'; ?> ms-lg-auto">
              <i class="fa-solid fa-truck-ramp-box me-1"></i> COD: <?= ($cod && $cod['is_active']) ? 'Enabled' : 'Disabled'; ?>
            </span>
          </div>
        </div>

        <div class="col-12 col-lg-5">
          <form action="<?= site_url('settings/payment'); ?>" method="POST" class="d-flex flex-column flex-sm-row gap-2 align-items-sm-center">
            <input type="hidden" name="gateway" value="active_online_gateway" />
            <div class="flex-grow-1">
              <label class="form-label small fw-semibold text-muted mb-1">Select Active Online Gateway:</label>
              <select name="active_online_gateway" class="form-select form-select-md fw-semibold border-primary">
                <option value="razorpay" <?= ($curr_online === 'razorpay') ? 'selected' : ''; ?>>
                  Razorpay (UPI, QR, Cards, NetBanking)
                </option>
                <option value="stripe" <?= ($curr_online === 'stripe') ? 'selected' : ''; ?>>
                  Stripe (International Cards, Visa, MasterCard, Amex)
                </option>
                <option value="payu" <?= ($curr_online === 'payu') ? 'selected' : ''; ?>>
                  PayU Gateway (NetBanking & Wallets)
                </option>
                <option value="none" <?= ($curr_online === 'none') ? 'selected' : ''; ?>>
                  Disable All Online Payments (COD Only)
                </option>
              </select>
            </div>
            <div class="pt-sm-4">
              <button type="submit" class="btn btn-primary text-nowrap px-3">
                <i class="fa-solid fa-floppy-disk me-1"></i> Apply Gateway
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- 1. Razorpay Card -->
    <div class="col-12 col-lg-6 mb-4">
      <div class="card h-100 shadow-sm border <?= ($curr_online === 'razorpay') ? 'border-2 border-success' : ''; ?>">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
          <h5 class="card-title mb-0">
            <i class="fa-solid fa-credit-card text-info me-2"></i> Razorpay Gateway
          </h5>
          <div class="d-flex align-items-center gap-2">
            <?php if ($curr_online === 'razorpay'): ?>
              <span class="badge bg-success text-white"><i class="fa-solid fa-circle-check me-1"></i>Active Online Gateway</span>
            <?php else: ?>
              <span class="badge bg-label-secondary">Disabled</span>
            <?php endif; ?>
          </div>
        </div>
        <div class="card-body pt-3">
          <form action="<?= site_url('settings/payment'); ?>" method="POST">
            <input type="hidden" name="gateway" value="razorpay" />

            <div class="form-check form-switch mb-3">
              <input class="form-check-input" type="checkbox" id="razorpay_active" name="razorpay_active" value="1" <?= ($curr_online === 'razorpay') ? 'checked' : ''; ?> onchange="onGatewaySwitchChange(this, 'razorpay')" />
              <label class="form-check-label fw-semibold" for="razorpay_active">Set as Active Online Gateway</label>
            </div>

            <div class="mb-3">
              <label class="form-label">Environment</label>
              <select name="razorpay_env" class="form-select">
                <option value="test" <?= ($razorpay && $razorpay['environment'] === 'test') ? 'selected' : ''; ?>>Test / Sandbox</option>
                <option value="live" <?= ($razorpay && $razorpay['environment'] === 'live') ? 'selected' : ''; ?>>Production / Live</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label" for="razorpay_key_id">Key ID</label>
              <input
                type="text"
                class="form-control font-monospace small"
                id="razorpay_key_id"
                name="razorpay_key_id"
                value="<?= html_escape($razorpay['credentials_decoded']['key_id'] ?? ''); ?>"
                placeholder="rzp_test_..." />
            </div>

            <div class="mb-3">
              <label class="form-label" for="razorpay_key_secret">Key Secret</label>
              <input
                type="password"
                class="form-control font-monospace small"
                id="razorpay_key_secret"
                name="razorpay_key_secret"
                value="<?= html_escape($razorpay['credentials_decoded']['key_secret'] ?? ''); ?>"
                placeholder="Key Secret..." />
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-2">
              <i class="fa-solid fa-floppy-disk me-1"></i> Save Razorpay Keys
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- 2. Stripe Card -->
    <div class="col-12 col-lg-6 mb-4">
      <div class="card h-100 shadow-sm border <?= ($curr_online === 'stripe') ? 'border-2 border-primary' : ''; ?>">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
          <h5 class="card-title mb-0">
            <i class="fa-solid fa-shield-halved text-primary me-2"></i> Stripe Payments
          </h5>
          <div class="d-flex align-items-center gap-2">
            <?php if ($curr_online === 'stripe'): ?>
              <span class="badge bg-primary text-white"><i class="fa-solid fa-circle-check me-1"></i>Active Online Gateway</span>
            <?php else: ?>
              <span class="badge bg-label-secondary">Disabled</span>
            <?php endif; ?>
          </div>
        </div>
        <div class="card-body pt-3">
          <form action="<?= site_url('settings/payment'); ?>" method="POST">
            <input type="hidden" name="gateway" value="stripe" />

            <div class="form-check form-switch mb-3">
              <input class="form-check-input" type="checkbox" id="stripe_active" name="stripe_active" value="1" <?= ($curr_online === 'stripe') ? 'checked' : ''; ?> onchange="onGatewaySwitchChange(this, 'stripe')" />
              <label class="form-check-label fw-semibold" for="stripe_active">Set as Active Online Gateway</label>
            </div>

            <div class="mb-3">
              <label class="form-label">Environment</label>
              <select name="stripe_env" class="form-select">
                <option value="test" <?= ($stripe && $stripe['environment'] === 'test') ? 'selected' : ''; ?>>Test / Sandbox</option>
                <option value="live" <?= ($stripe && $stripe['environment'] === 'live') ? 'selected' : ''; ?>>Production / Live</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label" for="stripe_publishable_key">Publishable Key</label>
              <input
                type="text"
                class="form-control font-monospace small"
                id="stripe_publishable_key"
                name="stripe_publishable_key"
                value="<?= html_escape($stripe['credentials_decoded']['publishable_key'] ?? ''); ?>"
                placeholder="pk_test_..." />
            </div>

            <div class="mb-3">
              <label class="form-label" for="stripe_secret_key">Secret Key</label>
              <input
                type="password"
                class="form-control font-monospace small"
                id="stripe_secret_key"
                name="stripe_secret_key"
                value="<?= html_escape($stripe['credentials_decoded']['secret_key'] ?? ''); ?>"
                placeholder="sk_test_..." />
            </div>

            <div class="mb-3">
              <label class="form-label" for="stripe_webhook_secret">Webhook Secret (Optional)</label>
              <input
                type="password"
                class="form-control font-monospace small"
                id="stripe_webhook_secret"
                name="stripe_webhook_secret"
                value="<?= html_escape($stripe['credentials_decoded']['webhook_secret'] ?? ''); ?>"
                placeholder="whsec_..." />
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-2">
              <i class="fa-solid fa-floppy-disk me-1"></i> Save Stripe Keys
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- 3. PayU Card -->
    <div class="col-12 col-lg-6 mb-4">
      <div class="card h-100 shadow-sm border <?= ($curr_online === 'payu') ? 'border-2 border-info' : ''; ?>">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
          <h5 class="card-title mb-0">
            <i class="fa-solid fa-money-bill-transfer text-info me-2"></i> PayU Gateway
          </h5>
          <div class="d-flex align-items-center gap-2">
            <?php if ($curr_online === 'payu'): ?>
              <span class="badge bg-info text-white"><i class="fa-solid fa-circle-check me-1"></i>Active Online Gateway</span>
            <?php else: ?>
              <span class="badge bg-label-secondary">Disabled</span>
            <?php endif; ?>
          </div>
        </div>
        <div class="card-body pt-3">
          <form action="<?= site_url('settings/payment'); ?>" method="POST">
            <input type="hidden" name="gateway" value="payu" />

            <div class="form-check form-switch mb-3">
              <input class="form-check-input" type="checkbox" id="payu_active" name="payu_active" value="1" <?= ($curr_online === 'payu') ? 'checked' : ''; ?> onchange="onGatewaySwitchChange(this, 'payu')" />
              <label class="form-check-label fw-semibold" for="payu_active">Set as Active Online Gateway</label>
            </div>

            <div class="mb-3">
              <label class="form-label">Environment</label>
              <select name="payu_env" class="form-select">
                <option value="test" <?= ($payu && $payu['environment'] === 'test') ? 'selected' : ''; ?>>Test (test.payu.in)</option>
                <option value="live" <?= ($payu && $payu['environment'] === 'live') ? 'selected' : ''; ?>>Production (secure.payu.in)</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label" for="payu_merchant_key">Merchant Key</label>
              <input
                type="text"
                class="form-control font-monospace small"
                id="payu_merchant_key"
                name="payu_merchant_key"
                value="<?= html_escape($payu['credentials_decoded']['merchant_key'] ?? ''); ?>"
                placeholder="e.g. gtKFFx28" />
            </div>

            <div class="mb-3">
              <label class="form-label" for="payu_merchant_salt">Merchant Salt</label>
              <input
                type="password"
                class="form-control font-monospace small"
                id="payu_merchant_salt"
                name="payu_merchant_salt"
                value="<?= html_escape($payu['credentials_decoded']['merchant_salt'] ?? ''); ?>"
                placeholder="e.g. eCwWELxi" />
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-2">
              <i class="fa-solid fa-floppy-disk me-1"></i> Save PayU Keys
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- 4. Cash on Delivery (COD) Card -->
    <div class="col-12 col-lg-6 mb-4">
      <div class="card h-100 shadow-sm border <?= ($cod && $cod['is_active']) ? 'border-2 border-success' : ''; ?>">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
          <h5 class="card-title mb-0">
            <i class="fa-solid fa-truck-ramp-box text-success me-2"></i> Cash on Delivery (COD)
          </h5>
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-<?= ($cod && $cod['is_active']) ? 'success' : 'label-secondary'; ?>">
              <i class="fa-solid <?= ($cod && $cod['is_active']) ? 'fa-circle-check' : 'fa-circle-xmark'; ?> me-1"></i>
              <?= ($cod && $cod['is_active']) ? 'Enabled' : 'Disabled'; ?>
            </span>
          </div>
        </div>
        <div class="card-body pt-3">
          <form action="<?= site_url('settings/payment'); ?>" method="POST">
            <input type="hidden" name="gateway" value="cod" />

            <div class="form-check form-switch mb-3">
              <input class="form-check-input" type="checkbox" id="cod_active" name="cod_active" value="1" <?= ($cod && $cod['is_active']) ? 'checked' : ''; ?> />
              <label class="form-check-label fw-semibold" for="cod_active">Enable Cash on Delivery Globally</label>
              <small class="text-muted d-block" style="font-size: 11px;">Note: COD will only appear at checkout if all products in customer's cart have COD allowed.</small>
            </div>

            <div class="mb-3">
              <label class="form-label" for="cod_instructions">Checkout Instructions / Note</label>
              <textarea
                class="form-control small"
                rows="4"
                id="cod_instructions"
                name="cod_instructions"
                placeholder="Pay with cash or UPI scanner upon physical delivery..."><?= html_escape($cod['credentials_decoded']['instructions'] ?? 'Pay with cash upon physical delivery of your package.'); ?></textarea>
              <small class="text-muted">Information or instructions displayed to customers during checkout.</small>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-4">
              <i class="fa-solid fa-floppy-disk me-1"></i> Save COD Settings
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Hidden Quick Activation Form -->
<form id="quick_gateway_form" action="<?= site_url('settings/payment'); ?>" method="POST" style="display:none;">
  <input type="hidden" name="gateway" value="active_online_gateway">
  <input type="hidden" name="active_online_gateway" id="quick_gateway_code" value="">
</form>

<script>
  function activateOnlineGateway(code) {
    document.getElementById('quick_gateway_code').value = code;
    document.getElementById('quick_gateway_form').submit();
  }

  function deactivateOnlineGateway(code, checkbox) {
    if (confirm('Deactivate ' + code.toUpperCase() + ' and disable all online payments (switch to COD only)?')) {
      document.getElementById('quick_gateway_code').value = 'none';
      document.getElementById('quick_gateway_form').submit();
    } else if (checkbox) {
      checkbox.checked = true;
    }
  }

  function onGatewaySwitchChange(checkbox, code) {
    if (checkbox.checked) {
      activateOnlineGateway(code);
    } else {
      deactivateOnlineGateway(code, checkbox);
    }
  }
</script>
