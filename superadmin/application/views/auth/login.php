<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Portal Login - Luxe Platform</title>
    <!-- Favicon & Branding -->
    <?php
        $sa_fav_url = function_exists('superadmin_favicon_url') ? superadmin_favicon_url() : base_url('uploads/branding/codeulas_logo_small.webp');
        $sa_logo_url = function_exists('superadmin_logo_url') ? superadmin_logo_url() : base_url('uploads/branding/logo.webp');
    ?>
    <link rel="icon" type="image/webp" href="<?= $sa_fav_url ?>?v=<?= time() ?>">
    <link rel="shortcut icon" type="image/webp" href="<?= $sa_fav_url ?>?v=<?= time() ?>">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="<?= superadmin_asset('css/bootstrap.css') ?>">
    <style>
        body {
            background: linear-gradient(135deg, #090e1a 0%, #111a2e 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            padding: 1.5rem;
        }
        .login-card {
            background: rgba(17, 24, 39, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(194, 153, 88, 0.25);
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }
        .login-header {
            background: rgba(12, 19, 34, 0.8);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding: 2.25rem 2rem 1.75rem;
            text-align: center;
        }
        .brand-badge {
            width: 54px;
            height: 54px;
            background: linear-gradient(135deg, #c29958, #e5c388);
            color: #0c1322;
            font-size: 1.6rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(194, 153, 88, 0.4);
            margin-bottom: 1rem;
        }
        .form-control {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #ffffff;
            padding: 0.75rem 1rem;
            border-radius: 8px;
        }
        .form-control:focus {
            background: rgba(15, 23, 42, 0.95);
            border-color: #c29958;
            color: #ffffff;
            box-shadow: 0 0 0 3px rgba(194, 153, 88, 0.25);
        }
        .form-label {
            color: #cbd5e1;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .btn-gold {
            background: linear-gradient(135deg, #c29958, #d4aa69);
            color: #0c1322;
            font-weight: 700;
            border: none;
            padding: 0.8rem;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .btn-gold:hover {
            background: linear-gradient(135deg, #d4aa69, #e5c388);
            color: #000000;
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(194, 153, 88, 0.35);
        }
        /* Webkit autofill text visibility fix */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-text-fill-color: #ffffff !important;
            -webkit-box-shadow: 0 0 0 1000px #0f172a inset !important;
            transition: background-color 5000s ease-in-out 0s;
        }
        .input-group-text {
            color: #c29958 !important;
            background: rgba(15, 23, 42, 0.95) !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
        }
        .btn-outline-secondary {
            color: #e2e8f0 !important;
            border-color: rgba(255, 255, 255, 0.3) !important;
        }
        .btn-outline-secondary:hover {
            background-color: rgba(255, 255, 255, 0.12) !important;
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.5) !important;
        }
        .login-card .text-secondary {
            color: #cbd5e1 !important;
        }
        .login-card .text-muted {
            color: #94a3b8 !important;
        }
        .login-card code {
            color: #fbbf24 !important;
            background: rgba(255, 255, 255, 0.1);
            padding: 2px 6px;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header text-center py-4">
        <img src="<?= $sa_logo_url ?>?v=<?= time() ?>" alt="Logo" style="max-height: 55px; max-width: 220px; object-fit: contain;">
    </div>

    <div class="p-4 p-sm-5">
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger border-0 small py-2 px-3 mb-4 rounded-3 d-flex align-items-center">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                <div><?= $this->session->flashdata('error') ?></div>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success border-0 small py-2 px-3 mb-4 rounded-3 d-flex align-items-center">
                <i class="fa-solid fa-circle-check me-2"></i>
                <div><?= $this->session->flashdata('success') ?></div>
            </div>
        <?php endif; ?>

        <form action="<?= superadmin_url('auth/login') ?>" method="post">
            <div class="mb-3">
                <label class="form-label" for="email">Super Admin Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" value="superadmin@ecommerce.com" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label" for="password">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" value="admin123" required>
                </div>
            </div>

            <button type="submit" class="btn btn-gold w-100 mb-3">
                <i class="fa-solid fa-shield-halved me-2"></i> Access SaaS Super Admin
            </button>

            <!-- Quick Demo Credential Pills -->
            <div class="text-center pt-2 border-top border-secondary border-opacity-25 mt-3">
                <span class="text-secondary small d-block mb-2">Default Master Credentials:</span>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-warning text-nowrap" onclick="fillCreds('superadmin@ecommerce.com')">
                        superadmin@ecommerce.com
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-warning text-nowrap" onclick="fillCreds('admin@ecommerce.com')">
                        admin@ecommerce.com
                    </button>
                </div>
                <small class="text-muted d-block mt-2 font-monospace">Password: <code>admin123</code></small>
            </div>
        </form>
    </div>

    <div class="bg-black bg-opacity-25 p-3 text-center border-top border-secondary border-opacity-25">
        <div class="d-flex justify-content-center gap-3 small">
            <a href="<?= main_site_url() ?>" class="text-secondary text-decoration-none hover-text-white">
                <i class="fa-solid fa-arrow-left me-1"></i> SaaS Landing Website
            </a>
            <span class="text-secondary">&bull;</span>
            <a href="<?= tenant_admin_url('auth/login') ?>" class="text-warning text-decoration-none">
                Store Admin Login <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>

<script>
function fillCreds(email) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = 'admin123';
}
</script>
</body>
</html>
