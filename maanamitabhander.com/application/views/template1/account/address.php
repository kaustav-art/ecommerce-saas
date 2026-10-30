        <!-- breadcrumb -->
        <div class="bg-light py-2 border-bottom">
            <div class="container" style="max-width: 1240px;">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 12px;">
                        <li class="breadcrumb-item"><a href="<?= site_url('home'); ?>" class="text-muted text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url('account/profile'); ?>" class="text-muted text-decoration-none">My Account</a></li>
                        <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Manage Addresses</li>
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
                        <div class="card border rounded-1 shadow-sm bg-white p-3 p-md-4" style="border-color: #f0f0f0 !important;">
                            <h5 class="fw-bold mb-3 text-dark" style="font-size: 17px;">Manage Addresses</h5>

                            <!-- Flash messages -->
                            <?php if ($this->session->flashdata('success')): ?>
                                <div class="alert alert-success alert-dismissible fade show rounded-1 py-2 px-3 small" role="alert">
                                    <i class="fa-solid fa-circle-check me-2"></i><?= $this->session->flashdata('success'); ?>
                                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            <?php if ($this->session->flashdata('error')): ?>
                                <div class="alert alert-danger alert-dismissible fade show rounded-1 py-2 px-3 small" role="alert">
                                    <i class="fa-solid fa-circle-exclamation me-2"></i><?= $this->session->flashdata('error'); ?>
                                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <!-- + ADD A NEW ADDRESS Trigger -->
                            <div id="add-address-trigger" class="card border rounded-1 p-3 mb-3 d-flex flex-row align-items-center gap-2 shadow-none" style="cursor: pointer; border-color: #e0e0e0 !important; background-color: #fff; transition: background 0.15s;" onclick="openAddressForm()">
                                <i class="fa-solid fa-plus text-primary fw-bold fs-6"></i>
                                <span class="fw-bold text-primary text-uppercase" style="font-size: 14px; letter-spacing: 0.3px;">ADD A NEW ADDRESS</span>
                            </div>

                            <!-- Add / Edit Address Form Container (Collapsible) -->
                            <div id="address-form-box" class="card border rounded-1 p-3 p-md-4 mb-4 d-none" style="background-color: #f7faff; border-color: #2874f0 !important;">
                                <div class="fw-bold text-uppercase text-primary mb-3" style="font-size: 14px; letter-spacing: 0.5px;" id="address-form-title">
                                    ADD A NEW ADDRESS
                                </div>

                                <!-- Use my current location Button -->
                                <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2 mb-3 px-4 py-2 fw-semibold rounded-1 w-100 w-sm-auto" id="btn-current-location" onclick="useCurrentLocation()" style="background-color: #2874f0; border-color: #2874f0; font-size: 14px;">
                                    <i class="fa-solid fa-location-crosshairs"></i>
                                    <span id="loc-btn-text">Use my current location</span>
                                </button>

                                <form action="<?= site_url('account/address'); ?>" method="POST" id="fk-address-form">
                                    <input type="hidden" name="id" id="addr_id" value="">
                                    <div class="row g-3">
                                        <!-- Name & Mobile -->
                                        <div class="col-md-6">
                                            <input type="text" name="name" id="addr_name" class="form-control rounded-1 bg-white" placeholder="Name" required style="height: 48px; border-color: #e0e0e0;">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="tel" name="phone" id="addr_phone" class="form-control rounded-1 bg-white" placeholder="10-digit mobile number" maxlength="10" pattern="[0-9]{10}" required style="height: 48px; border-color: #e0e0e0;">
                                        </div>

                                        <!-- Pincode & Locality -->
                                        <div class="col-md-6">
                                            <input type="text" name="postcode" id="addr_postcode" class="form-control rounded-1 bg-white" placeholder="Pincode" maxlength="6" pattern="[0-9]{6}" required style="height: 48px; border-color: #e0e0e0;">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" name="locality" id="addr_locality" class="form-control rounded-1 bg-white" placeholder="Locality" style="height: 48px; border-color: #e0e0e0;">
                                        </div>

                                        <!-- Address (Area and Street) -->
                                        <div class="col-12">
                                            <textarea name="address_1" id="addr_address_1" class="form-control rounded-1 bg-white" rows="3" placeholder="Address (Area and Street)" required style="border-color: #e0e0e0; resize: vertical; height: auto !important; min-height: 84px !important; line-height: 1.5 !important;"></textarea>
                                        </div>

                                        <!-- City & State -->
                                        <div class="col-md-6">
                                            <input type="text" name="city" id="addr_city" class="form-control rounded-1 bg-white" placeholder="City/District/Town" required style="height: 48px; border-color: #e0e0e0;">
                                        </div>
                                        <div class="col-md-6">
                                            <select name="state" id="addr_state" class="form-select rounded-1 bg-white" required style="height: 48px; border-color: #e0e0e0;">
                                                <option value="" disabled selected>--Select State--</option>
                                                <option value="Andhra Pradesh">Andhra Pradesh</option>
                                                <option value="Arunachal Pradesh">Arunachal Pradesh</option>
                                                <option value="Assam">Assam</option>
                                                <option value="Bihar">Bihar</option>
                                                <option value="Chhattisgarh">Chhattisgarh</option>
                                                <option value="Goa">Goa</option>
                                                <option value="Gujarat">Gujarat</option>
                                                <option value="Haryana">Haryana</option>
                                                <option value="Himachal Pradesh">Himachal Pradesh</option>
                                                <option value="Jharkhand">Jharkhand</option>
                                                <option value="Karnataka">Karnataka</option>
                                                <option value="Kerala">Kerala</option>
                                                <option value="Madhya Pradesh">Madhya Pradesh</option>
                                                <option value="Maharashtra">Maharashtra</option>
                                                <option value="Manipur">Manipur</option>
                                                <option value="Meghalaya">Meghalaya</option>
                                                <option value="Mizoram">Mizoram</option>
                                                <option value="Nagaland">Nagaland</option>
                                                <option value="Odisha">Odisha</option>
                                                <option value="Punjab">Punjab</option>
                                                <option value="Rajasthan">Rajasthan</option>
                                                <option value="Sikkim">Sikkim</option>
                                                <option value="Tamil Nadu">Tamil Nadu</option>
                                                <option value="Telangana">Telangana</option>
                                                <option value="Tripura">Tripura</option>
                                                <option value="Uttar Pradesh">Uttar Pradesh</option>
                                                <option value="Uttarakhand">Uttarakhand</option>
                                                <option value="West Bengal">West Bengal</option>
                                                <option value="Delhi">Delhi</option>
                                                <option value="Jammu and Kashmir">Jammu and Kashmir</option>
                                                <option value="Ladakh">Ladakh</option>
                                                <option value="Chandigarh">Chandigarh</option>
                                            </select>
                                        </div>

                                        <!-- Landmark & Alternate Phone -->
                                        <div class="col-md-6">
                                            <input type="text" name="landmark" id="addr_landmark" class="form-control rounded-1 bg-white" placeholder="Landmark (Optional)" style="height: 48px; border-color: #e0e0e0;">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="tel" name="alternate_phone" id="addr_alternate_phone" class="form-control rounded-1 bg-white" placeholder="Alternate Phone (Optional)" maxlength="15" style="height: 48px; border-color: #e0e0e0;">
                                        </div>

                                        <!-- Address Type Radio -->
                                        <div class="col-12 mt-3">
                                            <div class="text-muted small fw-semibold mb-2">Address Type</div>
                                            <div class="d-flex align-items-center gap-4 flex-wrap">
                                                <label class="d-flex align-items-center gap-2" style="cursor: pointer;">
                                                    <input type="radio" name="address_type" id="type_home" value="HOME" checked style="width: 18px; height: 18px;">
                                                    <span class="small fw-semibold text-dark">Home (All day delivery)</span>
                                                </label>
                                                <label class="d-flex align-items-center gap-2" style="cursor: pointer;">
                                                    <input type="radio" name="address_type" id="type_work" value="WORK" style="width: 18px; height: 18px;">
                                                    <span class="small fw-semibold text-dark">Work (Delivery between 10 AM - 5 PM)</span>
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Form Action Buttons -->
                                        <div class="col-12 mt-4 d-flex align-items-center gap-2 gap-sm-3 flex-wrap">
                                            <button type="submit" class="btn btn-primary text-uppercase fw-bold px-4 px-sm-5 py-2 rounded-1 shadow-sm flex-grow-1 flex-sm-grow-0" style="background-color: #2874f0; border-color: #2874f0; height: 46px; letter-spacing: 0.5px;">
                                                SAVE
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary text-uppercase fw-bold px-4 py-2 rounded-1" onclick="closeAddressForm()" style="letter-spacing: 0.5px; height: 46px;">
                                                CANCEL
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Saved Addresses List (Cards matching address_page.PNG) -->
                            <div class="address-list">
                                <?php if (!empty($addresses)): ?>
                                    <?php foreach ($addresses as $addr): ?>
                                        <?php
                                        $type_tag = strtoupper($addr['company'] ?: 'HOME');
                                        if (!in_array($type_tag, ['HOME', 'WORK'])) {
                                            $type_tag = 'HOME';
                                        }
                                        $full_name = trim($addr['first_name'] . ' ' . $addr['last_name']);
                                        $loc = $addr['address_2'] ?? '';
                                        $lmk = $addr['landmark'] ?? '';
                                        $addr_line = html_escape($addr['address_1']);
                                        if (!empty($loc)) {
                                            $addr_line .= ', ' . html_escape($loc);
                                        }
                                        if (!empty($lmk)) {
                                            $addr_line .= ', Near ' . html_escape($lmk);
                                        }
                                        $addr_line .= ', ' . html_escape($addr['city']) . ', ' . html_escape($addr['state']) . ' - ' . html_escape($addr['postcode']);
                                        ?>
                                        <div class="card border rounded-1 p-3 mb-3 position-relative bg-white shadow-none address-card-item" style="border-color: #e0e0e0 !important;">
                                            <!-- Three-dot options menu on top-right -->
                                            <div class="dropdown position-absolute top-0 end-0 m-3">
                                                <button class="btn btn-sm btn-link text-muted p-0 text-decoration-none" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 28px; height: 28px; border: none; background: none;">
                                                    <i class="fa-solid fa-ellipsis-vertical fs-5"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border rounded-1 py-1" style="min-width: 130px; border-color: #f0f0f0 !important;">
                                                    <li>
                                                        <a class="dropdown-item py-2 small fw-semibold" href="javascript:void(0);" onclick='editAddress(<?= json_encode($addr, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>
                                                            <i class="fa-solid fa-pen-to-square me-2 text-primary"></i> Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item py-2 small fw-semibold text-danger" href="<?= site_url('account/delete_address/' . $addr['id']); ?>" onclick="return confirm('Are you sure you want to delete this address?');">
                                                            <i class="fa-solid fa-trash-can me-2"></i> Delete
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>

                                            <!-- Tag: HOME or WORK -->
                                            <div class="mb-2">
                                                <span class="badge text-uppercase fw-bold" style="background-color: #f0f0f0; color: #878787; font-size: 11px; padding: 3px 8px; border-radius: 2px; letter-spacing: 0.5px;">
                                                    <?= $type_tag; ?>
                                                </span>
                                            </div>

                                            <!-- Name and Phone -->
                                            <div class="d-flex align-items-center gap-3 mb-2 flex-wrap pe-4">
                                                <span class="fw-bold text-dark" style="font-size: 14px;"><?= html_escape($full_name); ?></span>
                                                <span class="fw-bold text-dark" style="font-size: 14px;"><?= html_escape($addr['phone']); ?></span>
                                            </div>

                                            <!-- Full Address -->
                                            <div class="text-dark pe-4" style="font-size: 14px; line-height: 1.5; color: #383838;">
                                                <?= $addr_line; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-5 border rounded-1 bg-light">
                                        <div class="text-muted mb-2"><i class="fa-solid fa-location-dot fs-1"></i></div>
                                        <div class="fw-bold mb-1">No Addresses Saved Yet</div>
                                        <p class="text-muted small mb-3">Add your delivery address to proceed with seamless orders.</p>
                                        <button type="button" class="btn btn-primary btn-sm rounded-1" onclick="openAddressForm()">Add Address Now</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /my-account -->

        <style>
        #fk-address-form .form-control,
        #fk-address-form .form-select {
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
        }
        #fk-address-form .form-control:focus,
        #fk-address-form .form-select:focus {
            border-color: var(--bs-primary, #108910) !important;
            box-shadow: 0 0 0 3px rgba(16, 137, 16, 0.12) !important;
        }
        </style>

        <script>
        function openAddressForm() {
            var box = document.getElementById('address-form-box');
            var trigger = document.getElementById('add-address-trigger');
            var title = document.getElementById('address-form-title');
            var form = document.getElementById('fk-address-form');

            title.innerText = 'ADD A NEW ADDRESS';
            form.reset();
            document.getElementById('addr_id').value = '';
            document.getElementById('type_home').checked = true;

            box.classList.remove('d-none');
            trigger.classList.add('d-none');
            box.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function closeAddressForm() {
            var box = document.getElementById('address-form-box');
            var trigger = document.getElementById('add-address-trigger');
            box.classList.add('d-none');
            trigger.classList.remove('d-none');
        }

        function editAddress(addr) {
            var box = document.getElementById('address-form-box');
            var trigger = document.getElementById('add-address-trigger');
            var title = document.getElementById('address-form-title');

            title.innerText = 'EDIT ADDRESS';
            document.getElementById('addr_id').value = addr.id || '';
            document.getElementById('addr_name').value = ((addr.first_name || '') + ' ' + (addr.last_name || '')).trim();

            var rawPhone = String(addr.phone || '').replace(/\D/g, '');
            if (rawPhone.length > 10) {
                rawPhone = rawPhone.slice(-10);
            }
            document.getElementById('addr_phone').value = rawPhone || (addr.phone || '');
            document.getElementById('addr_postcode').value = addr.postcode || '';

            var rawAddr2 = addr.address_2 || '';
            var rawLandmark = addr.landmark || '';
            if (!rawLandmark && rawAddr2.indexOf(', Near ') !== -1) {
                var splitParts = rawAddr2.split(', Near ');
                rawAddr2 = splitParts[0] || '';
                rawLandmark = splitParts.slice(1).join(', Near ') || '';
            } else if (!rawLandmark && rawAddr2.indexOf('Near ') === 0) {
                rawLandmark = rawAddr2.substring(5);
                rawAddr2 = '';
            }

            document.getElementById('addr_locality').value = rawAddr2;
            document.getElementById('addr_address_1').value = addr.address_1 || '';
            document.getElementById('addr_city').value = addr.city || '';

            var stateSelect = document.getElementById('addr_state');
            var targetState = (addr.state || '').trim();
            if (stateSelect) {
                var matchedState = false;
                for (var i = 0; i < stateSelect.options.length; i++) {
                    if (stateSelect.options[i].value.toLowerCase() === targetState.toLowerCase()) {
                        stateSelect.selectedIndex = i;
                        matchedState = true;
                        break;
                    }
                }
                if (!matchedState && targetState !== '') {
                    var customOpt = document.createElement('option');
                    customOpt.value = targetState;
                    customOpt.text = targetState;
                    stateSelect.appendChild(customOpt);
                    stateSelect.value = targetState;
                } else if (!matchedState) {
                    stateSelect.selectedIndex = 0;
                }
            }

            document.getElementById('addr_landmark').value = rawLandmark;
            document.getElementById('addr_alternate_phone').value = addr.alternate_phone || '';

            var tag = (addr.company || '').toUpperCase();
            if (tag === 'WORK') {
                document.getElementById('type_work').checked = true;
            } else {
                document.getElementById('type_home').checked = true;
            }

            box.classList.remove('d-none');
            trigger.classList.add('d-none');
            box.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function useCurrentLocation() {
            var btnText = document.getElementById('loc-btn-text');
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser.');
                return;
            }

            btnText.innerText = 'Detecting location...';

            navigator.geolocation.getCurrentPosition(function(pos) {
                var lat = pos.coords.latitude;
                var lon = pos.coords.longitude;

                // Reverse geocode via OpenStreetMap Nominatim
                fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lon)
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        btnText.innerText = 'Use my current location';
                        if (data && data.address) {
                            var a = data.address;
                            if (a.postcode) document.getElementById('addr_postcode').value = a.postcode;
                            if (a.suburb || a.neighbourhood || a.road) {
                                document.getElementById('addr_locality').value = a.suburb || a.neighbourhood || a.road;
                            }
                            if (a.city || a.town || a.state_district) {
                                document.getElementById('addr_city').value = a.city || a.town || a.state_district;
                            }
                            if (a.state) {
                                var stateSelect = document.getElementById('addr_state');
                                for (var i = 0; i < stateSelect.options.length; i++) {
                                    if (stateSelect.options[i].value.toLowerCase() === a.state.toLowerCase()) {
                                        stateSelect.selectedIndex = i;
                                        break;
                                    }
                                }
                            }
                            if (data.display_name && !document.getElementById('addr_address_1').value) {
                                document.getElementById('addr_address_1').value = data.display_name.split(',').slice(0, 3).join(',');
                            }
                        }
                    })
                    .catch(function(err) {
                        btnText.innerText = 'Use my current location';
                        alert('Could not auto-fetch address details. Please fill manually.');
                    });
            }, function(err) {
                btnText.innerText = 'Use my current location';
                alert('Unable to retrieve your location: ' + err.message);
            }, { timeout: 10000 });
        }
        </script>

