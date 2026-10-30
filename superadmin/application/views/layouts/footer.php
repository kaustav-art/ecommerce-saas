    <!-- Footer Note -->
    <footer class="mt-auto py-3 px-4 text-center small" style="background: #080d19 !important; border-top: 1px solid rgba(194, 153, 88, 0.25) !important;">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
            <span class="text-light">
                &copy; <?= date('Y') ?> <strong class="text-warning fw-bold">Luxe Salon &amp; Spa</strong> &bull; <span class="text-white-50">Super Admin Control Panel v2.0</span>
            </span>
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill font-monospace px-3 py-1" style="background: rgba(194, 153, 88, 0.15); color: #fbbf24; border: 1px solid rgba(194, 153, 88, 0.4);">
                    <i class="fa-solid fa-crown me-1 text-warning"></i> Super Admin Master Node
                </span>
                <span class="badge rounded-pill font-monospace px-3 py-1" style="background: rgba(255, 255, 255, 0.07); color: #e2e8f0; border: 1px solid rgba(255, 255, 255, 0.15);">
                    PHP <?= phpversion() ?> &bull; CI <?= CI_VERSION ?> &bull; Self-Hosted Enterprise
                </span>
            </div>
        </div>
    </footer>
</div><!-- /sa-main -->

<!-- Core Scripts -->
<script>
    if (typeof jQuery === 'undefined') {
        document.write('<script src="<?= superadmin_asset("vendor/libs/jquery/jquery.js") ?>"><\/script>');
    }
    if (typeof bootstrap === 'undefined') {
        document.write('<script src="<?= superadmin_asset("js/bootstrap.bundle.min.js") ?>"><\/script>');
    }
</script>
<script src="<?= superadmin_asset('vendor/libs/sweetalert2/sweetalert2.js') ?>"></script>

<script>
$(document).ready(function() {
    // Initialize Bootstrap dropdowns
    if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
        document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(el) {
            bootstrap.Dropdown.getOrCreateInstance(el);
        });
    }

    // Mobile sidebar toggle
    $('#sidebarToggle').on('click', function() {
        $('#saSidebar').toggleClass('show');
    });

    // Copy to clipboard helper
    $(document).on('click', '.sa-copy-btn', function() {
        var textToCopy = $(this).data('copy');
        if (!textToCopy) {
            textToCopy = $(this).closest('.sa-code-badge').find('.copy-target').text().trim();
        }
        var btn = $(this);
        navigator.clipboard.writeText(textToCopy).then(function() {
            var origHtml = btn.html();
            btn.html('<i class="fa-solid fa-check text-success"></i>');
            setTimeout(function() {
                btn.html(origHtml);
            }, 2000);
        });
    });
});
</script>
</body>
</html>
