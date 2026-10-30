    <div class="position-relative">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-6 mx-4">
          <div class="card p-7">
            <!-- Logo -->
            <div class="app-brand justify-content-center mt-5 mb-2">
              <?php
                $login_logo = (!empty($store_settings['site_logo']))
                  ? base_url('../website/assets/images/logo/' . $store_settings['site_logo'])
                  : base_url('assets/img/branding/logo.webp');
              ?>
              <a href="<?= site_url('auth/login'); ?>" class="app-brand-link">
                <img src="<?= $login_logo; ?>" alt="Logo" style="max-height: 50px; width: auto; object-fit: contain;" onerror="this.src='<?= base_url('assets/img/branding/logo.webp'); ?>'">
              </a>
            </div>
            <!-- /Logo -->

            <div class="card-body mt-1">
              <h4 class="mb-1">Welcome to Admin Panel! 👋</h4>
              <p class="mb-5 text-muted">Role-Based Multipurpose eCommerce Admin</p>

              <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger py-2 small" role="alert">
                  <?= $this->session->flashdata('error'); ?>
                </div>
              <?php endif; ?>
              <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success py-2 small" role="alert">
                  <?= $this->session->flashdata('success'); ?>
                </div>
              <?php endif; ?>

              <form id="formAuthentication" class="mb-5" action="<?= site_url('auth/login'); ?>" method="POST">
                <div class="form-floating form-floating-outline mb-5">
                  <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    placeholder="Enter your email"
                    required
                    autofocus />
                  <label for="email">Email Address</label>
                </div>
                <div class="mb-5">
                  <div class="form-password-toggle">
                    <div class="input-group input-group-merge">
                      <div class="form-floating form-floating-outline">
                        <input
                          type="password"
                          id="password"
                          class="form-control"
                          name="password"
                          placeholder="············"
                          required />
                        <label for="password">Password</label>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="mb-5 d-flex justify-content-between mt-5">
                  <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="remember-me" checked />
                    <label class="form-check-label" for="remember-me"> Remember Me </label>
                  </div>
                </div>
                <div class="mb-5">
                  <button class="btn btn-primary d-grid w-100" type="submit">Sign In</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
