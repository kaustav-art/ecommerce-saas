<?php if (!defined('ADDRESS_MODAL_RENDERED')): define('ADDRESS_MODAL_RENDERED', true); ?>
<!-- Delivery Address Selection Side Drawer Modal (Offcanvas Full Right) -->
<div class="modal fullRight fade modal-address-side" id="addressModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="header d-flex align-items-center justify-content-between p-3 border-bottom bg-white">
                <h5 class="title fw-bold mb-0 text-dark" style="font-size: 18px;">
                    <i class="fa-solid fa-location-dot me-2 text-primary"></i>Delivery Address
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 13px;"></button>
            </div>

            <div class="modal-body p-3 p-md-4" style="overflow-y: auto;">
                <!-- Alert Box for notifications -->
                <div id="address-modal-alert" class="alert d-none py-2 px-3 small rounded-3 mb-3"></div>

                <!-- Button to Toggle New Address Form -->
                <div class="mb-3">
                    <button type="button" class="btn btn-outline-primary w-100 py-2 fw-semibold" id="btn-toggle-add-address" onclick="toggleNewAddressForm()">
                        <i class="fa-solid fa-plus me-1"></i> Add a new address
                    </button>
                </div>

                <!-- Add New Address Form Container -->
                <div id="add-address-form-box" class="card border p-3 mb-4 rounded-3 bg-light shadow-sm" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-map-pin me-1"></i> Add New Delivery Address</h6>
                        <button type="button" class="btn-close small" onclick="toggleNewAddressForm(false)"></button>
                    </div>
                    <form id="newAddressForm" onsubmit="handleSaveAddress(event)">
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control form-control-sm" required placeholder="First Name">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">Last Name</label>
                                <input type="text" name="last_name" class="form-control form-control-sm" placeholder="Last Name">
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">Mobile Number <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control form-control-sm" required placeholder="10-digit number">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">Pincode <span class="text-danger">*</span></label>
                                <input type="text" name="postcode" class="form-control form-control-sm" required placeholder="6-digit Pincode">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1">Flat, House No., Building <span class="text-danger">*</span></label>
                            <input type="text" name="address_1" class="form-control form-control-sm" required placeholder="House/Flat No., Apartment">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1">Area, Street, Sector, Landmark</label>
                            <input type="text" name="address_2" class="form-control form-control-sm" placeholder="Area, Landmark (optional)">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">City / Town <span class="text-danger">*</span></label>
                                <input type="text" name="city" class="form-control form-control-sm" required placeholder="City">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">State <span class="text-danger">*</span></label>
                                <input type="text" name="state" class="form-control form-control-sm" required placeholder="State">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold d-block mb-1">Address Type</label>
                            <div class="btn-group btn-group-sm w-100" role="group">
                                <input type="radio" class="btn-check" name="company" id="addr-type-home" value="HOME" checked>
                                <label class="btn btn-outline-secondary" for="addr-type-home"><i class="fa-solid fa-house me-1"></i> HOME (All day)</label>
                                
                                <input type="radio" class="btn-check" name="company" id="addr-type-work" value="WORK">
                                <label class="btn btn-outline-secondary" for="addr-type-work"><i class="fa-solid fa-briefcase me-1"></i> WORK (10 AM - 6 PM)</label>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="is_default" value="1" id="save-as-default" checked>
                            <label class="form-check-label small" for="save-as-default">
                                Make this my default delivery address
                            </label>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-grow-1 py-2 fw-semibold" id="btn-save-addr">
                                Save and Deliver Here
                            </button>
                            <button type="button" class="btn btn-light btn-sm px-3" onclick="toggleNewAddressForm(false)">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Saved Addresses List Header -->
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-uppercase small fw-bold text-muted" style="letter-spacing: 0.5px;">Saved Addresses</span>
                    <span class="badge bg-light text-dark border" id="saved-address-count"><?= !empty($user_addresses) ? count($user_addresses) : (empty($active_address) ? '0' : '1'); ?></span>
                </div>

                <!-- Saved Addresses Container -->
                <div id="saved-addresses-container">
                    <?php 
                        $curr_selected_id = $active_address['id'] ?? null;
                        $addresses_to_show = !empty($user_addresses) ? $user_addresses : (!empty($active_address) ? [$active_address] : []);
                    ?>
                    <?php if (!empty($addresses_to_show)): ?>
                        <?php foreach ($addresses_to_show as $addr): 
                            $is_active = ($curr_selected_id && $addr['id'] == $curr_selected_id);
                            $addr_tag = !empty($addr['company']) ? strtoupper($addr['company']) : 'HOME';
                        ?>
                        <div class="card border rounded-3 p-3 mb-3 address-select-card <?= $is_active ? 'border-primary bg-light' : 'border-light-subtle'; ?>" id="address-card-<?= $addr['id']; ?>" onclick="selectDeliveryAddress('<?= $addr['id']; ?>')">
                            <div class="d-flex align-items-start gap-2">
                                <input class="form-check-input mt-1 flex-shrink-0" type="radio" name="selected_addr_radio" value="<?= $addr['id']; ?>" <?= $is_active ? 'checked' : ''; ?>>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                        <strong class="text-dark"><?= html_escape($addr['first_name'] . ' ' . ($addr['last_name'] ?? '')); ?></strong>
                                        <span class="badge bg-secondary text-white text-uppercase" style="font-size: 10px;"><?= html_escape($addr_tag); ?></span>
                                        <span class="text-muted small ms-auto fw-bold"><?= html_escape($addr['phone'] ?? ''); ?></span>
                                    </div>
                                    <div class="text-secondary small mb-2">
                                        <?= html_escape(implode(', ', array_filter([$addr['address_1'], $addr['address_2'] ?? '', $addr['city'], ($addr['state'] ?? '') . (!empty($addr['postcode']) ? ' - ' . $addr['postcode'] : '')]))); ?>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <button type="button" class="btn btn-sm <?= $is_active ? 'btn-primary' : 'btn-outline-primary'; ?> px-3 py-1 fw-bold" onclick="event.stopPropagation(); selectDeliveryAddress('<?= $addr['id']; ?>')">
                                            <?= $is_active ? '<i class="fa-solid fa-check me-1"></i> Selected' : 'Deliver Here'; ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted small" id="no-addresses-placeholder">
                            <i class="fa-solid fa-location-dot fs-3 mb-2 text-secondary d-block"></i>
                            No saved addresses found.<br>Click "Add a new address" above to set your delivery location.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.modal.fullRight .modal-dialog {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    margin: 0;
    width: 480px;
    max-width: 100%;
    height: 100%;
    transform: translate(100%, 0);
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
}
.modal.fullRight.show .modal-dialog {
    transform: none !important;
}
.modal-address-side .modal-content {
    width: 100% !important;
    height: 100% !important;
    border-radius: 0 !important;
    border: 0 !important;
    box-shadow: -10px 0 30px rgba(0, 0, 0, 0.15);
    background-color: #ffffff;
    display: flex;
    flex-direction: column;
}
.modal-address-side .header {
    border-bottom: 1px solid #e5e7eb;
}
.address-select-card {
    cursor: pointer;
    transition: all 0.2s ease;
}
.address-select-card:hover {
    border-color: #0c2340 !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
</style>

<script>
(function() {
    window.openAddressModal = function() {
        if (!window.IS_USER_LOGGED_IN) {
            if (typeof window.openLoginModal === 'function') {
                window.openLoginModal('<?= site_url("cart"); ?>', 'Please sign in or register to select your delivery address.');
            }
            return;
        }
        var modalEl = document.getElementById('addressModal');
        if (!modalEl) return;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else if (typeof $ !== 'undefined') {
            $('#addressModal').modal('show');
        }
    };

    window.toggleNewAddressForm = function(forceState) {
        var formBox = document.getElementById('add-address-form-box');
        if (!formBox) return;
        if (typeof forceState === 'boolean') {
            formBox.style.display = forceState ? 'block' : 'none';
        } else {
            formBox.style.display = (formBox.style.display === 'none' || formBox.style.display === '') ? 'block' : 'none';
        }
        if (formBox.style.display === 'block') {
            var firstInp = formBox.querySelector('input[name="first_name"]');
            if (firstInp) setTimeout(function() { firstInp.focus(); }, 100);
        }
    };

    window.selectDeliveryAddress = function(addrId) {
        var formData = new FormData();
        formData.append('address_id', addrId);

        fetch('<?= site_url("checkout/select_address"); ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success && data.address) {
                if (typeof window.updateDeliveryAddressUI === 'function') {
                    window.updateDeliveryAddressUI(data.address);
                }
                var modalEl = document.getElementById('addressModal');
                if (modalEl) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    } else if (typeof $ !== 'undefined') {
                        $(modalEl).modal('hide');
                    }
                }
            } else {
                if (typeof showAddressAlert === 'function') {
                    showAddressAlert('danger', data.message || 'Failed to select address.');
                }
            }
        })
        .catch(function(err) {
            console.error('Error selecting address:', err);
        });
    };

    window.handleSaveAddress = function(e) {
        if (e && e.preventDefault) e.preventDefault();
        var form = document.getElementById('newAddressForm');
        var btn = document.getElementById('btn-save-addr');
        if (!form || !btn) return;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
        if (typeof hideAddressAlert === 'function') hideAddressAlert();

        var formData = new FormData(form);

        fetch('<?= site_url("checkout/save_address"); ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.innerHTML = 'Save and Deliver Here';

            if (data.success && data.address) {
                if (typeof window.updateDeliveryAddressUI === 'function') {
                    window.updateDeliveryAddressUI(data.address);
                }
                form.reset();
                if (typeof toggleNewAddressForm === 'function') toggleNewAddressForm(false);
                if (typeof refreshAddressList === 'function') refreshAddressList();
                var modalEl = document.getElementById('addressModal');
                if (modalEl) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    } else if (typeof $ !== 'undefined') {
                        $(modalEl).modal('hide');
                    }
                }
            } else {
                if (typeof showAddressAlert === 'function') {
                    showAddressAlert('danger', data.message || 'Failed to save address.');
                }
            }
        })
        .catch(function(err) {
            console.error('Error saving address:', err);
            btn.disabled = false;
            btn.innerHTML = 'Save and Deliver Here';
            if (typeof showAddressAlert === 'function') {
                showAddressAlert('danger', 'An error occurred while saving address.');
            }
        });
    };

    window.updateDeliveryAddressUI = function(addr) {
        if (!addr) return;
        var fullName = (addr.first_name + ' ' + (addr.last_name || '')).trim();
        var tag = (addr.company || 'HOME').toUpperCase();
        var fullAddr = [addr.address_1, addr.address_2, addr.city, (addr.state ? addr.state + ' - ' + addr.postcode : addr.postcode)].filter(Boolean).join(', ');
        var phone = addr.phone || '';

        // Update Cart & Checkout Page Delivery Cards
        var cartName = document.getElementById('display-address-name');
        if (cartName) cartName.textContent = fullName + (addr.postcode ? ', ' + addr.postcode : '');
        var cartTag = document.getElementById('display-address-tag');
        if (cartTag) cartTag.textContent = tag;
        var cartFull = document.getElementById('display-address-full');
        if (cartFull) cartFull.textContent = fullAddr;
        var cartPhone = document.getElementById('display-address-phone');
        if (cartPhone) cartPhone.textContent = phone;
        var cartPhoneWrap = document.getElementById('display-address-phone-wrap');
        if (cartPhoneWrap) cartPhoneWrap.style.setProperty('display', phone ? 'flex' : 'none', 'important');

        // Update Change button in cart
        var btnAction = document.getElementById('btn-address-action');
        if (!btnAction) {
            btnAction = document.querySelector('.cart-address-card button');
        }
        if (btnAction) {
            btnAction.textContent = 'Change';
            btnAction.onclick = function(e) {
                if (e) e.stopPropagation();
                openAddressModal();
            };
        }

        // Mark radio card as active in modal
        document.querySelectorAll('.address-select-card').forEach(function(card) {
            card.classList.remove('border-primary', 'bg-light');
            card.classList.add('border-light-subtle');
            var r = card.querySelector('input[type="radio"]');
            if (r) r.checked = false;
            var b = card.querySelector('button');
            if (b) {
                b.className = 'btn btn-sm btn-outline-primary px-3 py-1 fw-bold';
                b.textContent = 'Deliver Here';
            }
        });
        var activeCard = document.getElementById('address-card-' + addr.id);
        if (activeCard) {
            activeCard.classList.remove('border-light-subtle');
            activeCard.classList.add('border-primary', 'bg-light');
            var rad = activeCard.querySelector('input[type="radio"]');
            if (rad) rad.checked = true;
            var btn = activeCard.querySelector('button');
            if (btn) {
                btn.className = 'btn btn-sm btn-primary px-3 py-1 fw-bold';
                btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Selected';
            }
        }
    };

    window.showAddressAlert = function(type, msg) {
        var a = document.getElementById('address-modal-alert');
        if (!a) return;
        a.className = 'alert alert-' + type + ' py-2 px-3 small rounded-3 mb-3';
        a.textContent = msg;
        a.classList.remove('d-none');
    };

    window.hideAddressAlert = function() {
        var a = document.getElementById('address-modal-alert');
        if (a) a.classList.add('d-none');
    };

    window.refreshAddressList = function() {
        fetch('<?= site_url("checkout/get_addresses"); ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success && data.addresses) {
                var container = document.getElementById('saved-addresses-container');
                var countBadge = document.getElementById('saved-address-count');
                if (countBadge) countBadge.textContent = data.addresses.length;
                if (!container) return;

                if (data.addresses.length === 0) {
                    container.innerHTML = '<div class="text-center py-4 text-muted small" id="no-addresses-placeholder"><i class="fa-solid fa-location-dot fs-3 mb-2 text-secondary d-block"></i>No saved addresses found.<br>Click "Add a new address" above to set your delivery location.</div>';
                    return;
                }

                var html = '';
                data.addresses.forEach(function(addr) {
                    var isAct = (data.selected_id && addr.id == data.selected_id);
                    var tag = (addr.company || 'HOME').toUpperCase();
                    var full = [addr.address_1, addr.address_2, addr.city, (addr.state ? addr.state + ' - ' + addr.postcode : addr.postcode)].filter(Boolean).join(', ');
                    var name = (addr.first_name + ' ' + (addr.last_name || '')).trim();

                    html += '<div class="card border rounded-3 p-3 mb-3 address-select-card ' + (isAct ? 'border-primary bg-light' : 'border-light-subtle') + '" id="address-card-' + addr.id + '" onclick="selectDeliveryAddress(\'' + addr.id + '\')">';
                    html += '<div class="d-flex align-items-start gap-2">';
                    html += '<input class="form-check-input mt-1 flex-shrink-0" type="radio" name="selected_addr_radio" value="' + addr.id + '" ' + (isAct ? 'checked' : '') + '>';
                    html += '<div class="flex-grow-1">';
                    html += '<div class="d-flex align-items-center gap-2 mb-1 flex-wrap">';
                    html += '<strong class="text-dark">' + name + '</strong>';
                    html += '<span class="badge bg-secondary text-white text-uppercase" style="font-size: 10px;">' + tag + '</span>';
                    html += '<span class="text-muted small ms-auto fw-bold">' + (addr.phone || '') + '</span>';
                    html += '</div>';
                    html += '<div class="text-secondary small mb-2">' + full + '</div>';
                    html += '<div class="d-flex justify-content-between align-items-center">';
                    html += '<button type="button" class="btn btn-sm ' + (isAct ? 'btn-primary' : 'btn-outline-primary') + ' px-3 py-1 fw-bold" onclick="event.stopPropagation(); selectDeliveryAddress(\'' + addr.id + '\')">';
                    html += isAct ? '<i class="fa-solid fa-check me-1"></i> Selected' : 'Deliver Here';
                    html += '</button></div></div></div></div>';
                });
                container.innerHTML = html;
            }
        })
        .catch(function(err) {
            console.error('Error refreshing addresses:', err);
        });
    };
})();
</script>
<?php endif; ?>
