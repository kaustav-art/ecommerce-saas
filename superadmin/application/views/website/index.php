<?php
$settings = (isset($settings) && is_array($settings)) ? $settings : array();
$s = function($key, $default = '') use (&$settings) {
    return isset($settings[$key]) && $settings[$key] !== '' ? $settings[$key] : $default;
};
$curr_tab = isset($active_tab) ? $active_tab : 'hero';
?>
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="fa-solid fa-globe text-warning me-2"></i>SaaS Landing Website &amp; Template Gallery CMS
        </h4>
        <p class="text-muted mb-0">Customize the public SaaS landing page copy, storefront template gallery (Fashion, Electronics, Grocery), legal terms, and enquiries.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= main_site_url() ?>" target="_blank" class="btn btn-warning btn-sm px-3 fw-bold">
            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View Public Landing Page
        </a>
        <a href="<?= superadmin_url('plans') ?>" class="btn btn-outline-primary btn-sm px-3">
            <i class="fa-solid fa-tags me-1"></i> Pricing Plans
        </a>
    </div>
</div>

<!-- Navigation Tabs -->
<ul class="nav nav-pills gap-2 mb-4 p-2 rounded-3 bg-white border shadow-sm">
    <li class="nav-item">
        <a class="nav-link <?= $curr_tab === 'hero' ? 'active' : '' ?>" href="<?= superadmin_url('website?tab=hero') ?>">
            <i class="fa-solid fa-bullhorn me-1"></i> Hero, Branding &amp; Legal CMS
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $curr_tab === 'themes' ? 'active' : '' ?>" href="<?= superadmin_url('website?tab=themes') ?>">
            <i class="fa-solid fa-palette me-1"></i> Storefront Templates (<?= count($templates) ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $curr_tab === 'enquiries' ? 'active' : '' ?>" href="<?= superadmin_url('website?tab=enquiries') ?>">
            <i class="fa-solid fa-envelope me-1"></i> Contact Enquiries (<?= count($enquiries) ?>)
        </a>
    </li>
</ul>

<?php if ($curr_tab === 'hero'): ?>
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-dark text-white py-3">
            <h6 class="fw-bold mb-0"><i class="fa-solid fa-pen-nib text-warning me-2"></i>Landing Website Headlines, Contact &amp; Legal Content</h6>
        </div>
        <div class="card-body p-4">
            <form action="<?= superadmin_url('website') ?>" method="post">
                <input type="hidden" name="action" value="save_hero">
                <input type="hidden" name="active_tab" value="hero">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Platform Name</label>
                        <input type="text" class="form-control" name="platform_name" value="<?= htmlspecialchars($s('platform_name', 'Codeulas Cloud')) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">SEO Site Title</label>
                        <input type="text" class="form-control" name="site_title" value="<?= htmlspecialchars($s('site_title', 'Codeulas Cloud — Multi-Template SaaS E-Commerce Platform')) ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Hero Eyebrow Badge</label>
                        <input type="text" class="form-control" name="hero_badge" value="<?= htmlspecialchars($s('hero_badge', '🚀 Launch Your Online Store in 60 Seconds')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Hero Headline</label>
                        <input type="text" class="form-control" name="hero_heading" value="<?= htmlspecialchars($s('hero_heading', 'Build & Scale Your E-Commerce Brand With Industry-Crafted Templates')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Primary CTA Button Text</label>
                        <input type="text" class="form-control" name="cta_button_text" value="<?= htmlspecialchars($s('cta_button_text', 'Start Your Store Now')) ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Hero Subtitle Description</label>
                        <textarea class="form-control" name="hero_subheading" rows="2"><?= htmlspecialchars($s('hero_subheading', 'Choose from Fashion, Electronics, or Grocery storefronts.')) ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Support Email</label>
                        <input type="email" class="form-control" name="contact_email" value="<?= htmlspecialchars($s('contact_email', 'support@codeulas-saas.com')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Support Phone</label>
                        <input type="text" class="form-control" name="contact_phone" value="<?= htmlspecialchars($s('contact_phone', '+1 (800) 555-0199')) ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Terms &amp; Conditions Content</label>
                        <textarea class="form-control" name="terms_content" rows="4"><?= htmlspecialchars($s('terms_content')) ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Privacy Policy Content</label>
                        <textarea class="form-control" name="privacy_content" rows="4"><?= htmlspecialchars($s('privacy_content')) ?></textarea>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-warning fw-bold px-4">
                        <i class="fa-solid fa-save me-1"></i> Save Landing Website Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

<?php elseif ($curr_tab === 'themes'): ?>
    <div class="row g-4">
        <?php foreach ($templates as $tpl): ?>
            <?php
                $feats = json_decode($tpl->features, true) ?: array();
                $tpl_active = !empty($tpl->is_active);
            ?>
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-3">
                    <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-warning text-dark fw-bold"><?= htmlspecialchars($tpl->badge) ?></span>
                            <h5 class="fw-bold mb-0 mt-1"><?= htmlspecialchars($tpl->name) ?></h5>
                        </div>
                        <span class="badge <?= $tpl_active ? 'bg-success' : 'bg-secondary' ?> text-uppercase"><?= $tpl_active ? 'Active' : 'Inactive' ?></span>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted small mb-3"><?= htmlspecialchars($tpl->description) ?></p>
                        <div class="small mb-3">
                            <strong>Template Key:</strong> <code><?= htmlspecialchars($tpl->template_key) ?></code><br>
                            <strong>Demo URL:</strong> <code><?= htmlspecialchars($tpl->demo_url) ?></code>
                        </div>
                        <?php if (!empty($feats)): ?>
                            <ul class="list-unstyled small mb-0">
                                <?php foreach ($feats as $f): ?>
                                    <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i><?= htmlspecialchars($f) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-light border-top p-3 d-flex gap-2">
                        <a href="<?= main_site_url($tpl->demo_url) ?>" target="_blank" class="btn btn-outline-primary btn-sm flex-grow-1">
                            <i class="fa-solid fa-eye me-1"></i> Live Demo
                        </a>
                        <button type="button" class="btn btn-warning btn-sm flex-grow-1 fw-bold" data-bs-toggle="modal" data-bs-target="#editTplModal<?= $tpl->id ?>">
                            <i class="fa-solid fa-pen me-1"></i> Edit Template
                        </button>
                    </div>
                </div>
            </div>

            <!-- Edit Template Modal -->
            <div class="modal fade" id="editTplModal<?= $tpl->id ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <form action="<?= superadmin_url('website') ?>" method="post">
                            <input type="hidden" name="action" value="save_template">
                            <input type="hidden" name="active_tab" value="themes">
                            <input type="hidden" name="template_id" value="<?= $tpl->id ?>">
                            <div class="modal-header bg-dark text-white">
                                <h5 class="modal-title fw-bold">Edit <?= htmlspecialchars($tpl->name) ?></h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Template Name</label>
                                    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($tpl->name) ?>" required>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Badge</label>
                                        <input type="text" class="form-control" name="badge" value="<?= htmlspecialchars($tpl->badge) ?>">
                                    </div>
                                    <div class="col-3">
                                        <label class="form-label small fw-bold">Status</label>
                                        <select class="form-select" name="status">
                                            <option value="active" <?= $tpl_active ? 'selected' : '' ?>>Active</option>
                                            <option value="inactive" <?= !$tpl_active ? 'selected' : '' ?>>Inactive</option>
                                        </select>
                                    </div>
                                    <div class="col-3">
                                        <label class="form-label small fw-bold">Order</label>
                                        <input type="number" class="form-control" name="sort_order" value="<?= (int)$tpl->sort_order ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Preview Image Path</label>
                                    <input type="text" class="form-control font-monospace" name="preview_image" value="<?= htmlspecialchars($tpl->preview_image) ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Demo URL</label>
                                    <input type="text" class="form-control font-monospace" name="demo_url" value="<?= htmlspecialchars($tpl->demo_url) ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Description</label>
                                    <textarea class="form-control" name="description" rows="2"><?= htmlspecialchars($tpl->description) ?></textarea>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small fw-bold">Features (one per line)</label>
                                    <textarea class="form-control font-monospace small" name="features" rows="4"><?= htmlspecialchars(implode("\n", $feats)) ?></textarea>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-warning btn-sm fw-bold">Save Template</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<?php else: ?>
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-dark text-white py-3">
            <h6 class="fw-bold mb-0"><i class="fa-solid fa-envelope text-warning me-2"></i>Landing Page Contact Enquiries</h6>
        </div>
        <div class="table-responsive">
            <table class="table sa-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Email / Phone</th>
                        <th>Subject</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($enquiries)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">No enquiries submitted yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($enquiries as $eq): ?>
                            <tr>
                                <td><small class="text-muted"><?= format_custom_date($eq->created_at) ?></small></td>
                                <td class="fw-bold"><?= htmlspecialchars($eq->name) ?></td>
                                <td>
                                    <div><?= htmlspecialchars($eq->email) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($eq->phone) ?></small>
                                </td>
                                <td><?= htmlspecialchars($eq->subject) ?></td>
                                <td><small><?= htmlspecialchars($eq->message) ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
