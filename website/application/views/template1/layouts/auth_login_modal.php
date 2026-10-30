<?php
$modal_store_name = html_escape($site_name ?? ($store_settings['site_name'] ?? 'bazaro'));
?>
<style>
.aq-login-wrapper {
    padding: 40px;
    border-radius: 16px;
    padding-bottom: 35px;
    background: var(--aq-common-white);
    /* box-shadow: 0 30px 70px 0 rgba(1, 15, 28, 0.1); */
}
.aq-login-modal-style .modal-content {
    border-radius: 16px;
    border: none;
    padding: 24px 0px;
    position: relative;
    /* box-shadow: 0 25px 60px rgba(0, 0, 0, 0.22); */
    /* background: #ffffff; */
}
.aq-login-modal-style .btn-close {
    position: absolute;
    top: 18px;
    right: 18px;
    z-index: 10;
}
.aq-login-modal-style .aq-login-title {
    font-size: 26px;
    font-weight: 700;
    color: #010F1C;
    margin-bottom: 6px;
}
.aq-login-modal-style .aq-login-top p {
    font-size: 14px;
    color: #55585B;
    margin-bottom: 0;
}
.aq-login-modal-style .aq-login-input-box {
    margin-bottom: 18px;
    text-align: left;
}
.aq-login-modal-style .aq-form-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #010F1C;
    margin-bottom: 6px;
}
.aq-login-modal-style .aq-form-label span {
    color: #EF4444;
}
.aq-login-modal-style .aq-form-control {
    width: 100%;
    height: 50px;
    border: 1px solid #E0E2E3;
    border-radius: 8px;
    padding: 0 16px;
    font-size: 14px;
    color: #010F1C;
    background: #ffffff;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.aq-login-modal-style .aq-form-control:focus {
    border-color: #010F1C;
    outline: none;
    box-shadow: 0 0 0 3px rgba(1, 15, 28, 0.08);
}
.aq-login-modal-style .aq-login-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 50px;
    border-radius: 8px;
    background: #010F1C;
    color: #ffffff;
    font-size: 15px;
    font-weight: 600;
    border: 1px solid #010F1C;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}
.aq-login-modal-style .aq-login-btn:hover:not(:disabled) {
    background: #1f2937;
    color: #ffffff;
}
.aq-login-modal-style .aq-login-btn.btn-transparent {
    background: transparent;
    color: #010F1C;
    border: 1px solid #E0E2E3;
}
.aq-login-modal-style .aq-login-btn.btn-transparent:hover {
    background: #F4F5F7;
    border-color: #010F1C;
}
.aq-login-modal-style .otp-digit-input {
    width: 46px !important;
    height: 52px !important;
    padding: 0 !important;
    font-size: 20px !important;
    font-weight: 700 !important;
    text-align: center !important;
    border: 1.5px solid #D1D5DB !important;
    border-radius: 8px !important;
    color: #010F1C !important;
    background: #ffffff !important;
}
.aq-login-modal-style .otp-digit-input:focus {
    border-color: #010F1C !important;
    box-shadow: 0 0 0 3px rgba(1, 15, 28, 0.1) !important;
    outline: none !important;
}
.aq-login-modal-style .aq-login-social {
    gap: 12px;
}
.aq-login-modal-style .aq-login-option-item a {
    width: 48px;
    height: 44px;
    border: 1px solid #E0E2E3;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}
.aq-login-modal-style .aq-login-option-item a:hover {
    border-color: #010F1C;
    background: #F9FAFB;
}
</style>

<div class="aq-login-modal-style">
   <div class="modal fade" id="exampleModalToggle" role="dialog" aria-labelledby="exampleModalToggle" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="aq-login-wrapper">
               <div class="aq-login-top text-center mb-30" style="margin-bottom: 24px;">
                  <h3 class="aq-login-title" id="login-modal-step-title">Sign in to <?= $modal_store_name; ?>.</h3>
                  <p id="login-modal-step-desc">Enter your Mobile Number or Email to sign in or create an account with OTP.</p>
               </div>

               <!-- Alert Box for Error / Success Notifications -->
               <div id="login-modal-alert" class="alert d-none py-2 px-3 small rounded-3 mb-3"></div>

               <div class="aq-login-option">
                  <!-- STEP 1: Mobile Number or Email Input -->
                  <div id="login-step-identifier">
                     <form id="otpRequestForm" onsubmit="handleSendOtp(event)">
                        <div class="aq-login-input-wrapper">
                           <div class="aq-login-input-box">
                              <label class="aq-form-label">Mobile Number or Email <span>*</span></label>
                              <input class="aq-form-control" type="text" id="login-identifier-input" placeholder="e.g. 9876543210 or email@address.com" autocomplete="username" required>
                           </div>
                        </div>
                        <div class="aq-login-suggetions d-sm-flex align-items-center justify-content-between mb-20" style="margin-bottom: 18px;">
                           <div class="aq-login-remeber d-flex align-items-center gap-2">
                              <input class="aq-form-checkbox form-check-input" id="remeber" type="checkbox" checked>
                              <label class="aq-form-checkbox-label small text-muted" for="remeber">Keep me signed in</label>
                           </div>
                           <div class="aq-login-forgot">
                              <span class="small text-success fw-semibold"><i class="fa-solid fa-shield-halved me-1"></i>Instant OTP Verify</span>
                           </div>
                        </div>
                        <div class="aq-login-bottom-wrap mb-30" style="margin-bottom: 22px;">
                           <button type="submit" id="btn-send-otp" class="aq-login-btn w-100">Continue with OTP</button>
                        </div>
                     </form>

                     <div class="aq-login-mail text-center mb-20" style="margin-bottom: 14px;">
                        <p class="small text-muted mb-0">Or continue with</p>
                     </div>
                     <div class="aq-login-social d-flex flex-wrap align-items-center justify-content-center">
                        <div class="aq-login-option-item">
                           <a href="javascript:void(0);" onclick="document.getElementById('login-identifier-input').focus();">
                              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                 <path d="M19.25 9.83876C19.25 9.04738 19.1845 8.46988 19.0427 7.871H9.82144V11.4429H15.2341C15.125 12.3306 14.5357 13.6674 13.2262 14.5657L13.2078 14.6852L16.1234 16.8987L16.3254 16.9185C18.1805 15.2395 19.25 12.769 19.25 9.83876Z" fill="#4285F4"></path>
                                 <path d="M9.82083 19.25C12.4726 19.25 14.6994 18.3943 16.3254 16.9185L13.2262 14.5657C12.3968 15.1325 11.2831 15.5282 9.82083 15.5282C7.22363 15.5282 5.01929 13.8493 4.23351 11.5285L4.11833 11.5381L1.08667 13.8375L1.04703 13.9455C2.66208 17.0896 5.97954 19.25 9.82083 19.25Z" fill="#34A853"></path>
                                 <path d="M4.23351 11.5285C4.02617 10.9297 3.90675 10.288 3.90675 9.62497C3.90675 8.96188 4.02674 8.32023 4.22317 7.72135L4.21768 7.59381L1.14803 5.25756L1.04759 5.30438C0.381948 6.60912 0 8.07428 0 9.62497C0 11.1757 0.381383 12.6407 1.04703 13.9455L4.23351 11.5285Z" fill="#FBBC05"></path>
                                 <path d="M9.82088 3.72164C11.6651 3.72164 12.9091 4.50233 13.6185 5.15474L16.3903 2.5025C14.688 0.951809 12.4726 0 9.82088 0C5.97957 0 2.66266 2.16024 1.04759 5.30438L4.22317 7.72135C5.01987 5.40066 7.22367 3.72164 9.82088 3.72164Z" fill="#EB4335"></path>
                              </svg>
                           </a>
                        </div>
                        <div class="aq-login-option-item">
                           <a href="javascript:void(0);" onclick="document.getElementById('login-identifier-input').focus();">
                              <svg xmlns="http://www.w3.org/2000/svg" width="17" height="20" viewBox="0 0 17 20" fill="none">
                                 <path fill-rule="evenodd" clip-rule="evenodd" d="M15.4121 17.1597C15.9996 16.2597 16.2186 15.7997 16.6667 14.7897C13.361 13.5298 12.8333 8.79989 16.0991 6.98994C15.1034 5.72997 13.6995 5 12.3753 5C11.4194 5 10.7623 5.25 10.1748 5.48C9.67699 5.66999 9.22894 5.83997 8.67135 5.83997C8.07395 5.83997 7.54623 5.65 6.98865 5.45C6.38129 5.23001 5.74405 5 4.9475 5C3.46394 5 1.88081 5.90998 0.875176 7.46994C-0.53869 9.66988 -0.289768 13.7898 1.99034 17.3097C2.8068 18.5696 3.90205 19.9796 5.32587 19.9996C5.92328 20.0096 6.31159 19.8296 6.73973 19.6396C7.22762 19.4196 7.75532 19.1796 8.68131 19.1796C9.60729 19.1696 10.125 19.4196 10.6129 19.6396C11.0311 19.8296 11.4095 20.0096 11.9969 19.9996C13.4407 19.9796 14.5956 18.4196 15.4121 17.1597Z" fill="#010F1C"></path>
                                 <path fill-rule="evenodd" clip-rule="evenodd" d="M12.1721 0C12.3314 1.09997 11.8833 2.18995 11.2959 2.94993C10.6686 3.76991 9.57333 4.40988 8.51791 4.36988C8.32873 3.30991 8.81661 2.21993 9.41402 1.48995C10.0811 0.689972 11.2062 0.0699982 12.1721 0Z" fill="#010F1C"></path>
                              </svg>
                           </a>
                        </div>
                        <div class="aq-login-option-item">
                           <a href="javascript:void(0);" onclick="document.getElementById('login-identifier-input').focus();">
                              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                 <path d="M20 10C20 4.5 15.5 0 10 0C4.5 0 0 4.5 0 10C0 15 3.625 19.125 8.375 19.875V12.875H5.875V10H8.375V7.75C8.375 5.25 9.875 3.875 12.125 3.875C13.25 3.875 14.375 4.125 14.375 4.125V6.625H13.125C11.875 6.625 11.5 7.375 11.5 8.125V10H14.25L13.75 12.875H11.375V20C16.375 19.25 20 15 20 10Z" fill="#2151F9"></path>
                              </svg>
                           </a>
                        </div>
                        <div class="aq-login-option-item">
                           <a href="javascript:void(0);" onclick="document.getElementById('login-identifier-input').focus();">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="15" viewBox="0 0 16 15" fill="none">
                                 <path fill-rule="evenodd" clip-rule="evenodd" d="M5.04202 0H0L5.97809 8.15716L0.38242 15H2.96778L7.19998 9.82455L10.958 14.9525H16L9.84822 6.55824L9.85911 6.57272L15.1559 0.0952909H12.5705L8.63704 4.90552L5.04202 0ZM2.78311 1.42858H4.35275L13.2169 13.5238H11.6472L2.78311 1.42858Z" fill="#141414"></path>
                              </svg>
                           </a>
                        </div>
                     </div>
                  </div>

                  <!-- STEP 2 (When User enters Email first): Enter Mobile Number Form -->
                  <div id="login-step-phone-for-email" class="d-none">
                     <div class="p-2 rounded-3 mb-3 border d-flex align-items-center justify-content-between" style="background:#F9FAFB;">
                        <div class="text-truncate small">
                           <i class="fa-solid fa-envelope text-dark me-2"></i>
                           <span class="text-muted">Email: </span>
                           <strong id="email-preview-label" class="text-dark"></strong>
                        </div>
                        <a href="javascript:void(0);" onclick="backToIdentifierStep()" class="small text-primary text-decoration-underline ms-2 flex-shrink-0">Change</a>
                     </div>

                     <form id="phoneForEmailForm" onsubmit="handlePhoneForEmail(event)">
                        <div class="aq-login-input-wrapper">
                           <div class="aq-login-input-box">
                              <label class="aq-form-label">Enter Mobile Number <span>*</span></label>
                              <input class="aq-form-control" type="tel" id="login-phone-input" placeholder="e.g. 9876543210" required>
                           </div>
                        </div>
                        <div class="aq-login-bottom-wrap">
                           <button type="submit" id="btn-phone-for-email" class="aq-login-btn w-100">Continue with OTP</button>
                        </div>
                     </form>
                  </div>

                  <!-- STEP 3: 6-Digit OTP Verification Form -->
                  <div id="login-step-otp" class="d-none">
                     <form id="otpVerifyForm" onsubmit="handleVerifyOtp(event)">
                        <div class="p-3 rounded-3 mb-3 border" style="background:#F9FAFB;">
                           <div class="d-flex align-items-center justify-content-between">
                              <div>
                                 <div class="text-muted small" style="font-size: 11px;">Verification code sent to</div>
                                 <div class="fw-bold text-dark" id="otp-target-label"></div>
                                 <div id="otp-email-sublabel" class="text-muted small d-none" style="font-size: 11px;"></div>
                              </div>
                              <a href="javascript:void(0);" onclick="handleChangeFromOtp()" class="small text-primary text-decoration-underline">Change</a>
                           </div>
                           <div id="demo-otp-badge" class="mt-2 py-2 px-3 bg-warning-subtle text-dark border border-warning rounded small d-flex align-items-center justify-content-between" style="font-size: 12px; display: none;">
                              <span><i class="fa-solid fa-key me-1 text-warning"></i> Demo OTP: <strong id="demo-otp-code"></strong></span>
                              <button type="button" class="btn btn-sm btn-dark py-0 px-2" style="font-size:11px;" onclick="autoFillStoreOtp()">Auto-Fill</button>
                           </div>
                        </div>

                        <div class="aq-login-input-box text-center">
                           <label class="aq-form-label text-center mb-2">Enter 6-Digit OTP Code <span>*</span></label>
                           <div class="d-flex justify-content-center gap-2 mb-2" id="otp-inputs-container">
                              <input type="text" maxlength="1" class="otp-digit-input" inputmode="numeric" placeholder="•" required>
                              <input type="text" maxlength="1" class="otp-digit-input" inputmode="numeric" placeholder="•" required>
                              <input type="text" maxlength="1" class="otp-digit-input" inputmode="numeric" placeholder="•" required>
                              <input type="text" maxlength="1" class="otp-digit-input" inputmode="numeric" placeholder="•" required>
                              <input type="text" maxlength="1" class="otp-digit-input" inputmode="numeric" placeholder="•" required>
                              <input type="text" maxlength="1" class="otp-digit-input" inputmode="numeric" placeholder="•" required>
                           </div>
                        </div>

                        <div class="aq-login-bottom-wrap">
                           <button type="submit" id="btn-verify-otp" class="aq-login-btn w-100">Verify &amp; Continue</button>
                        </div>

                        <div class="text-center mt-3 small">
                           <span class="text-muted">Didn't receive the code? </span>
                           <a href="javascript:void(0);" id="btn-resend-otp" onclick="resendOtp()" class="fw-semibold text-dark text-decoration-underline disabled">Resend in <span id="resend-timer-count">30</span>s</a>
                        </div>
                     </form>
                  </div>

                  <!-- STEP 4 (Phone entered first & is new): Ask for Full Name and Email -->
                  <div id="login-step-complete-email-name" class="d-none">
                     <div class="alert alert-success py-2 px-3 small rounded-3 mb-3 d-flex align-items-center">
                        <i class="fa-solid fa-circle-check fs-5 me-2 flex-shrink-0 text-success"></i>
                        <div>
                           <span class="d-block text-muted" style="font-size: 11px;">Mobile number verified:</span>
                           <strong id="verified-phone-label-1" class="text-dark"></strong>
                        </div>
                     </div>

                     <form id="completeEmailNameForm" onsubmit="handleCompleteEmailName(event)">
                        <div class="aq-login-input-wrapper">
                           <div class="aq-login-input-box">
                              <label class="aq-form-label">Full Name <span>*</span></label>
                              <input class="aq-form-control" type="text" id="reg-name-1" placeholder="your name" required>
                           </div>
                           <div class="aq-login-input-box">
                              <label class="aq-form-label">Your Email <span>*</span></label>
                              <input class="aq-form-control" type="email" id="reg-email-1" placeholder="email@address.com" required>
                           </div>
                        </div>
                        <div class="aq-login-bottom-wrap">
                           <button type="submit" id="btn-complete-1" class="aq-login-btn w-100">Complete Registration &amp; Login</button>
                        </div>
                     </form>
                  </div>

                  <!-- STEP 5 (Email entered first & Phone is new): Ask for Full Name -->
                  <div id="login-step-complete-name" class="d-none">
                     <div class="p-2 rounded-3 mb-3 border small" style="background:#F9FAFB;">
                        <div class="d-flex align-items-center text-muted mb-1">
                           <i class="fa-solid fa-circle-check text-success me-2"></i> Email: <strong id="verified-email-label-2" class="text-dark ms-1"></strong>
                        </div>
                        <div class="d-flex align-items-center text-muted">
                           <i class="fa-solid fa-circle-check text-success me-2"></i> Mobile: <strong id="verified-phone-label-2" class="text-dark ms-1"></strong>
                        </div>
                     </div>

                     <form id="completeNameForm" onsubmit="handleCompleteName(event)">
                        <div class="aq-login-input-wrapper">
                           <div class="aq-login-input-box">
                              <label class="aq-form-label">Full Name <span>*</span></label>
                              <input class="aq-form-control" type="text" id="reg-name-2" placeholder="your name" required>
                           </div>
                        </div>
                        <div class="aq-login-bottom-wrap">
                           <button type="submit" id="btn-complete-2" class="aq-login-btn w-100">Complete Registration &amp; Login</button>
                        </div>
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<script>
(function() {
    window.IS_USER_LOGGED_IN = <?= !empty($is_logged_in) ? 'true' : 'false'; ?>;
    window.REDIRECT_AFTER_LOGIN = window.location.href;
    window.CUSTOM_REDIRECT_ACTIVE = false;
    window.AUTH_FLOW_DATA = {
        flow: 'phone_first',
        email: '',
        phone: '',
        demo_otp: ''
    };
    var resendInterval = null;

    window.focusNewAccountIdentifier = function() {
        var tTitle = document.getElementById('login-modal-step-title');
        var tDesc = document.getElementById('login-modal-step-desc');
        if (tTitle) tTitle.textContent = 'Create Account';
        if (tDesc) tDesc.textContent = 'Enter your Mobile Number or Email below to register with OTP.';
        var inp = document.getElementById('login-identifier-input');
        if (inp) inp.focus();
    };

    window.autoFillStoreOtp = function() {
        var code = window.AUTH_FLOW_DATA.demo_otp || (document.getElementById('demo-otp-code') ? document.getElementById('demo-otp-code').textContent.trim() : '');
        if (!code) return;
        var inputs = document.querySelectorAll('#exampleModalToggle .otp-digit-input');
        for (var i = 0; i < inputs.length && i < code.length; i++) {
            inputs[i].value = code[i];
        }
    };

    window.resetAuthModalSteps = function() {
        clearInterval(resendInterval);
        hideLoginAlert();
        window.AUTH_FLOW_DATA = { flow: 'phone_first', email: '', phone: '', demo_otp: '' };

        ['login-step-identifier', 'login-step-phone-for-email', 'login-step-otp', 'login-step-complete-email-name', 'login-step-complete-name'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.classList.add('d-none');
        });

        var s1 = document.getElementById('login-step-identifier');
        if (s1) s1.classList.remove('d-none');

        var tTitle = document.getElementById('login-modal-step-title');
        var tDesc = document.getElementById('login-modal-step-desc');
        if (tTitle) tTitle.textContent = 'Sign in to <?= $modal_store_name; ?>.';
        if (tDesc) tDesc.textContent = 'Enter your Mobile Number or Email to sign in or create an account with OTP.';

        var badge = document.getElementById('demo-otp-badge');
        if (badge) badge.style.display = 'none';

        var otpSub = document.getElementById('otp-email-sublabel');
        if (otpSub) otpSub.classList.add('d-none');
    };

    window.openLoginModal = function(redirectUrl, message) {
        if (redirectUrl) {
            window.REDIRECT_AFTER_LOGIN = redirectUrl;
            window.CUSTOM_REDIRECT_ACTIVE = true;
        } else {
            window.REDIRECT_AFTER_LOGIN = window.location.href;
            window.CUSTOM_REDIRECT_ACTIVE = false;
        }

        resetAuthModalSteps();

        if (message) {
            showLoginAlert('info', message);
        } else {
            hideLoginAlert();
        }

        var modalEl = document.getElementById('exampleModalToggle');
        if (modalEl) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            } else if (typeof $ !== 'undefined' && $.fn.modal) {
                $(modalEl).modal('show');
            }
            setTimeout(function() {
                var inp = document.getElementById('login-identifier-input');
                if (inp) inp.focus();
            }, 250);
        }
    };

    if (typeof window.handleCheckoutClick !== "function") {
        window.handleCheckoutClick = function(e, redirectUrl) {
            if (!window.IS_USER_LOGGED_IN) {
                if (e && e.preventDefault) e.preventDefault();

                if (typeof window.closeGrcCartDrawer === 'function') {
                    window.closeGrcCartDrawer();
                }
                if (typeof window.jQuery !== 'undefined') {
                    jQuery('.aq-cartmini-active, #grcCartMiniDrawer, .cartmini__area, .cartmini-area').removeClass('opened cartmini-opened active show');
                    jQuery('.body-overlay, .cartmini-overlay').removeClass('opened active show');
                    if (jQuery('#side-cart').length && typeof jQuery('#side-cart').modal === 'function') {
                        jQuery('#side-cart').modal('hide');
                    }
                }
                var drawers = document.querySelectorAll('.aq-cartmini-active, #grcCartMiniDrawer, .cartmini__area, .cartmini-area, .body-overlay, .cartmini-overlay');
                drawers.forEach(function(d) {
                    d.classList.remove('opened', 'cartmini-opened', 'active', 'show');
                });
                var sideCartModal = document.getElementById('side-cart');
                if (sideCartModal && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    var inst = bootstrap.Modal.getInstance(sideCartModal);
                    if (inst) inst.hide();
                }

                var targetUrl = redirectUrl || '<?= site_url("checkout"); ?>';
                if (typeof window.openLoginModal === 'function') {
                    window.openLoginModal(targetUrl, 'Please sign in to proceed to checkout.');
                } else {
                    window.location.href = '<?= site_url("cart?login=1"); ?>';
                }
                return false;
            }
            return true;
        };
    }

    if (typeof window.openAddressModal !== "function") {
        window.openAddressModal = function() {
            if (!window.IS_USER_LOGGED_IN) {
                window.openLoginModal(window.location.href, "Please sign in or register to select your delivery address.");
                return;
            }
            var modalEl = document.getElementById("addressModal");
            if (modalEl) {
                if (typeof bootstrap !== "undefined" && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                } else if (typeof $ !== "undefined") {
                    $("#addressModal").modal("show");
                }
            } else {
                window.location.href = "<?= site_url('account/addresses'); ?>";
            }
        };
    }

    window.handleSendOtp = function(e) {
        if (e && e.preventDefault) e.preventDefault();

        var identifierInput = document.getElementById('login-identifier-input');
        var btnSend = document.getElementById('btn-send-otp');
        var identifier = identifierInput ? identifierInput.value.trim() : '';
        if (!identifier) return;

        hideLoginAlert();

        var isEmail = (identifier.indexOf('@') !== -1 && identifier.indexOf('.') !== -1);
        if (isEmail) {
            window.AUTH_FLOW_DATA.flow = 'email_first';
            window.AUTH_FLOW_DATA.email = identifier.toLowerCase();
            window.AUTH_FLOW_DATA.phone = '';

            document.getElementById('login-step-identifier').classList.add('d-none');
            document.getElementById('login-step-phone-for-email').classList.remove('d-none');

            document.getElementById('email-preview-label').textContent = window.AUTH_FLOW_DATA.email;
            document.getElementById('login-modal-step-title').textContent = 'Enter Mobile Number';
            document.getElementById('login-modal-step-desc').textContent = 'Please enter your mobile number to receive a 6-digit OTP.';

            setTimeout(function() {
                var phoneInput = document.getElementById('login-phone-input');
                if (phoneInput) {
                    phoneInput.value = '';
                    phoneInput.focus();
                }
            }, 200);
            return;
        }

        window.AUTH_FLOW_DATA.flow = 'phone_first';
        window.AUTH_FLOW_DATA.phone = identifier;
        window.AUTH_FLOW_DATA.email = '';

        btnSend.disabled = true;
        btnSend.textContent = 'Sending OTP...';

        var formData = new FormData();
        formData.append('identifier', identifier);

        fetch('<?= site_url("auth/send_otp"); ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btnSend.disabled = false;
            btnSend.textContent = 'Continue with OTP';

            if (data.success) {
                window.AUTH_FLOW_DATA.phone = data.phone || identifier;
                window.AUTH_FLOW_DATA.demo_otp = data.demo_otp || '';
                document.getElementById('otp-target-label').textContent = window.AUTH_FLOW_DATA.phone;

                var emailSub = document.getElementById('otp-email-sublabel');
                if (emailSub) emailSub.classList.add('d-none');

                var demoBadge = document.getElementById('demo-otp-badge');
                var demoCode = document.getElementById('demo-otp-code');
                if (data.demo_otp && demoBadge && demoCode) {
                    demoCode.textContent = data.demo_otp;
                    demoBadge.style.display = 'flex';
                }

                document.getElementById('login-step-identifier').classList.add('d-none');
                document.getElementById('login-step-otp').classList.remove('d-none');
                document.getElementById('login-modal-step-title').textContent = 'Verify OTP';
                document.getElementById('login-modal-step-desc').textContent = 'Enter the 6-digit code sent to verify your identity.';

                startResendTimer();
                initOtpInputs();
            } else {
                showLoginAlert('danger', data.message || 'Failed to send OTP.');
            }
        })
        .catch(function() {
            btnSend.disabled = false;
            btnSend.textContent = 'Continue with OTP';
            showLoginAlert('danger', 'Network error occurred. Please try again.');
        });
    };

    window.handlePhoneForEmail = function(e) {
        if (e && e.preventDefault) e.preventDefault();

        var phoneInput = document.getElementById('login-phone-input');
        var btnPhone = document.getElementById('btn-phone-for-email');
        var phone = phoneInput ? phoneInput.value.trim() : '';
        if (!phone) return;

        btnPhone.disabled = true;
        btnPhone.textContent = 'Sending OTP...';
        hideLoginAlert();

        var formData = new FormData();
        formData.append('identifier', phone);
        formData.append('email', window.AUTH_FLOW_DATA.email || '');

        fetch('<?= site_url("auth/send_otp"); ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btnPhone.disabled = false;
            btnPhone.textContent = 'Continue with OTP';

            if (data.success) {
                window.AUTH_FLOW_DATA.phone = data.phone || phone;
                window.AUTH_FLOW_DATA.demo_otp = data.demo_otp || '';
                document.getElementById('otp-target-label').textContent = window.AUTH_FLOW_DATA.phone;

                var emailSub = document.getElementById('otp-email-sublabel');
                if (emailSub && window.AUTH_FLOW_DATA.email) {
                    emailSub.textContent = 'Email: ' + window.AUTH_FLOW_DATA.email;
                    emailSub.classList.remove('d-none');
                }

                var demoBadge = document.getElementById('demo-otp-badge');
                var demoCode = document.getElementById('demo-otp-code');
                if (data.demo_otp && demoBadge && demoCode) {
                    demoCode.textContent = data.demo_otp;
                    demoBadge.style.display = 'flex';
                }

                document.getElementById('login-step-phone-for-email').classList.add('d-none');
                document.getElementById('login-step-otp').classList.remove('d-none');
                document.getElementById('login-modal-step-title').textContent = 'Verify Mobile OTP';
                document.getElementById('login-modal-step-desc').textContent = 'Enter the 6-digit code sent to ' + window.AUTH_FLOW_DATA.phone + '.';

                startResendTimer();
                initOtpInputs();
            } else {
                showLoginAlert('danger', data.message || 'Failed to send OTP.');
            }
        })
        .catch(function() {
            btnPhone.disabled = false;
            btnPhone.textContent = 'Continue with OTP';
            showLoginAlert('danger', 'Network error occurred. Please try again.');
        });
    };

    function dispatchLoginSuccess(data) {
        showLoginAlert('success', data.message || 'Login successful!');
        window.IS_USER_LOGGED_IN = true;
        window.CURRENT_USER = data.user || null;

        var targetUrl = (window.CUSTOM_REDIRECT_ACTIVE && window.REDIRECT_AFTER_LOGIN)
            ? window.REDIRECT_AFTER_LOGIN
            : (data.redirect || window.REDIRECT_AFTER_LOGIN || window.location.href);

        setTimeout(function() {
            window.location.href = targetUrl;
        }, 500);
    }

    window.handleVerifyOtp = function(e) {
        if (e && e.preventDefault) e.preventDefault();

        var phone = window.AUTH_FLOW_DATA.phone || document.getElementById('otp-target-label').textContent.trim();
        var inputs = document.querySelectorAll('#exampleModalToggle .otp-digit-input');
        var otpCode = '';
        inputs.forEach(function(inp) { otpCode += inp.value.trim(); });

        if (otpCode.length < 6) {
            showLoginAlert('warning', 'Please enter all 6 digits of the verification code.');
            return;
        }

        var btnVerify = document.getElementById('btn-verify-otp');
        btnVerify.disabled = true;
        btnVerify.textContent = 'Verifying...';
        hideLoginAlert();

        var formData = new FormData();
        formData.append('phone', phone);
        formData.append('otp', otpCode);
        formData.append('redirect_to', window.REDIRECT_AFTER_LOGIN || window.location.href);

        fetch('<?= site_url("auth/verify_otp"); ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btnVerify.disabled = false;
            btnVerify.textContent = 'Verify & Continue';

            if (data.success) {
                if (data.status === 'logged_in') {
                    dispatchLoginSuccess(data);
                } else if (data.status === 'need_email_name') {
                    document.getElementById('login-step-otp').classList.add('d-none');
                    document.getElementById('login-step-complete-email-name').classList.remove('d-none');
                    document.getElementById('verified-phone-label-1').textContent = data.phone || phone;
                    document.getElementById('login-modal-step-title').textContent = 'Create Account';
                    document.getElementById('login-modal-step-desc').textContent = 'Please enter your name and email address to finish registration.';
                    setTimeout(function() {
                        var inp = document.getElementById('reg-name-1');
                        if (inp) inp.focus();
                    }, 200);
                } else if (data.status === 'need_name') {
                    document.getElementById('login-step-otp').classList.add('d-none');
                    document.getElementById('login-step-complete-name').classList.remove('d-none');
                    document.getElementById('verified-email-label-2').textContent = data.email || window.AUTH_FLOW_DATA.email;
                    document.getElementById('verified-phone-label-2').textContent = data.phone || phone;
                    document.getElementById('login-modal-step-title').textContent = 'Create Account';
                    document.getElementById('login-modal-step-desc').textContent = 'Enter your full name to complete your registration.';
                    setTimeout(function() {
                        var inp = document.getElementById('reg-name-2');
                        if (inp) inp.focus();
                    }, 200);
                }
            } else {
                showLoginAlert('danger', data.message || 'Invalid OTP code.');
            }
        })
        .catch(function() {
            btnVerify.disabled = false;
            btnVerify.textContent = 'Verify & Continue';
            showLoginAlert('danger', 'Verification error. Please try again.');
        });
    };

    window.handleCompleteEmailName = function(e) {
        if (e && e.preventDefault) e.preventDefault();

        var name = (document.getElementById('reg-name-1').value || '').trim();
        var email = (document.getElementById('reg-email-1').value || '').trim();
        var btn = document.getElementById('btn-complete-1');

        if (!name || !email) {
            showLoginAlert('warning', 'Please enter your name and a valid email address.');
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Creating Account...';
        hideLoginAlert();

        var formData = new FormData();
        formData.append('name', name);
        formData.append('email', email);
        formData.append('redirect_to', window.REDIRECT_AFTER_LOGIN || window.location.href);

        fetch('<?= site_url("auth/complete_registration"); ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.textContent = 'Complete Registration & Login';
            if (data.success) {
                dispatchLoginSuccess(data);
            } else {
                showLoginAlert('danger', data.message || 'Registration failed.');
            }
        });
    };

    window.handleCompleteName = function(e) {
        if (e && e.preventDefault) e.preventDefault();

        var name = (document.getElementById('reg-name-2').value || '').trim();
        var btn = document.getElementById('btn-complete-2');
        if (!name) {
            showLoginAlert('warning', 'Please enter your name.');
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Creating Account...';
        hideLoginAlert();

        var formData = new FormData();
        formData.append('name', name);
        formData.append('email', window.AUTH_FLOW_DATA.email || '');
        formData.append('redirect_to', window.REDIRECT_AFTER_LOGIN || window.location.href);

        fetch('<?= site_url("auth/complete_registration"); ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.textContent = 'Complete Registration & Login';
            if (data.success) {
                dispatchLoginSuccess(data);
            } else {
                showLoginAlert('danger', data.message || 'Registration failed.');
            }
        });
    };

    window.handleChangeFromOtp = function() {
        clearInterval(resendInterval);
        hideLoginAlert();
        document.getElementById('login-step-otp').classList.add('d-none');
        if (window.AUTH_FLOW_DATA && window.AUTH_FLOW_DATA.flow === 'email_first') {
            document.getElementById('login-step-phone-for-email').classList.remove('d-none');
        } else {
            backToIdentifierStep();
        }
    };

    window.backToIdentifierStep = function() {
        clearInterval(resendInterval);
        hideLoginAlert();
        ['login-step-phone-for-email', 'login-step-otp', 'login-step-complete-email-name', 'login-step-complete-name'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.classList.add('d-none');
        });
        document.getElementById('login-step-identifier').classList.remove('d-none');
        document.getElementById('login-modal-step-title').textContent = 'Sign in to <?= $modal_store_name; ?>.';
        document.getElementById('login-modal-step-desc').textContent = 'Enter your Mobile Number or Email to sign in or create an account with OTP.';
    };

    window.resendOtp = function() {
        var btn = document.getElementById('btn-resend-otp');
        if (!btn || btn.classList.contains('disabled')) return;
        var identifier = window.AUTH_FLOW_DATA.phone || document.getElementById('otp-target-label').textContent.trim();
        if (!identifier) return;

        var formData = new FormData();
        formData.append('identifier', identifier);
        if (window.AUTH_FLOW_DATA.email) {
            formData.append('email', window.AUTH_FLOW_DATA.email);
        }

        btn.classList.add('disabled');
        btn.textContent = 'Sending...';

        fetch('<?= site_url("auth/send_otp"); ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                window.AUTH_FLOW_DATA.demo_otp = data.demo_otp || '';
                if (data.demo_otp) {
                    var demoCode = document.getElementById('demo-otp-code');
                    if (demoCode) demoCode.textContent = data.demo_otp;
                }
                showLoginAlert('success', 'A new 6-digit OTP has been sent.');
                startResendTimer();
            } else {
                showLoginAlert('danger', data.message || 'Failed to resend OTP.');
                btn.classList.remove('disabled');
                btn.textContent = 'Resend OTP';
            }
        });
    };

    function startResendTimer() {
        var btn = document.getElementById('btn-resend-otp');
        if (!btn) return;
        var count = 30;
        btn.classList.add('disabled');
        btn.innerHTML = 'Resend in <span id="resend-timer-count">' + count + '</span>s';
        clearInterval(resendInterval);
        resendInterval = setInterval(function() {
            count--;
            var countEl = document.getElementById('resend-timer-count');
            if (countEl) countEl.textContent = count;
            if (count <= 0) {
                clearInterval(resendInterval);
                btn.classList.remove('disabled');
                btn.textContent = 'Resend OTP';
            }
        }, 1000);
    }

    function initOtpInputs() {
        var inputs = document.querySelectorAll('#exampleModalToggle .otp-digit-input');
        inputs.forEach(function(input, index) {
            input.value = '';
            input.oninput = function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length >= 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            };
            input.onkeydown = function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    inputs[index - 1].focus();
                }
            };
        });
        if (inputs.length > 0) {
            setTimeout(function() { inputs[0].focus(); }, 200);
        }
    }

    function showLoginAlert(type, msg) {
        var alertBox = document.getElementById('login-modal-alert');
        if (!alertBox) return;
        alertBox.className = 'alert alert-' + type + ' py-2 px-3 small rounded-3 mb-3';
        alertBox.textContent = msg;
        alertBox.classList.remove('d-none');
    }

    function hideLoginAlert() {
        var alertBox = document.getElementById('login-modal-alert');
        if (alertBox) alertBox.classList.add('d-none');
    }

    document.addEventListener('DOMContentLoaded', function() {
        var modalEl = document.getElementById('exampleModalToggle');
        if (modalEl) {
            modalEl.addEventListener('show.bs.modal', function() {
                if (!window.CUSTOM_REDIRECT_ACTIVE) {
                    window.REDIRECT_AFTER_LOGIN = window.location.href;
                }
            });
            modalEl.addEventListener('hidden.bs.modal', function() {
                window.CUSTOM_REDIRECT_ACTIVE = false;
            });
        }

                // Intercept cart drawer checkout buttons when not logged in
        document.addEventListener('click', function(e) {
            var checkoutTrigger = e.target.closest('.aq-cartmini-main-btn a[href*="checkout"], .aq-cartmini-btn a[href*="checkout"], .cartmini__checkout a[href*="checkout"], a.side-cart-checkout-btn');
            if (checkoutTrigger && !window.IS_USER_LOGGED_IN) {
                e.preventDefault();
                window.handleCheckoutClick(e, checkoutTrigger.getAttribute('href') || '<?= site_url("checkout"); ?>');
            }
        });

        // Intercept any links pointing to auth/login or #loginModal when not logged in and open #exampleModalToggle
        document.addEventListener('click', function(e) {
            var trigger = e.target.closest('a[href*="/auth/login"], a[href="#loginModal"], a[href="#exampleModalToggle"]');
            if (trigger && !window.IS_USER_LOGGED_IN) {
                e.preventDefault();
                var redir = trigger.getAttribute('data-redirect-to') || window.location.href;
                var msg   = trigger.getAttribute('data-login-message') || '';
                window.openLoginModal(redir, msg);
            }
        });
    });
})();
</script>
