<?php
/**
 * Template 3 — Bazaro Fresh Grocery Store About Our Organic Farms Page
 */
$bazaro = isset($tpl_assets) ? rtrim($tpl_assets, '/') . '/' : base_url('assets/templates/bazaro/');
?>

<main>
   <!-- Hero Banner -->
   <section style="background: linear-gradient(135deg, #003D29 0%, #07593e 100%); padding: 54px 0; color: #ffffff;">
      <div class="container container-1630 text-center">
         <span class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3" style="background: rgba(255,255,255,0.14); color: #bbf7d0; font-size: 12px; font-weight: 700; text-transform: uppercase;">
            <i class="fa-solid fa-seedling"></i> Farm-to-Doorstep Since 2018
         </span>
         <h1 class="fw-bold text-white mb-2" style="font-size: clamp(26px, 3.8vw, 42px);">About <?= html_escape($site_name ?? 'Fresh Grocery Store'); ?></h1>
         <p class="text-white-50 mx-auto mb-0" style="max-width: 660px; font-size: 15.5px;">
            Partnering directly with over 120 certified organic local orchards, pasture-raised dairies, and artisan bakeries to deliver peak-harvest nutrition within hours of picking.
         </p>
      </div>
   </section>

   <!-- Story & Mission Section -->
   <section class="py-5" style="background: #f4f8f5;">
      <div class="container container-1630">
         <div class="row g-4 align-items-center mb-5">
            <div class="col-lg-6">
               <div class="bg-white p-3 rounded-4 border" style="border-color: #d1e7dd !important;">
                  <img src="<?= $bazaro; ?>img/grocery/grocery-discount-1.jpg" alt="Fresh Organic Harvest" class="w-100 rounded-3" style="max-height: 380px; object-fit: cover;" onerror="this.src='<?= $bazaro; ?>img/product/grocery-p-3.jpg'">
               </div>
            </div>
            <div class="col-lg-6">
               <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill mb-3 fw-bold">
                  100% Certified Organic &amp; Pesticide-Free
               </span>
               <h2 class="fw-bold mb-3" style="color: #003D29; font-size: 30px;">Honest Per-Kg &amp; Per-Piece Farm Pricing, Zero Middlemen</h2>
               <p class="text-secondary mb-3" style="line-height: 1.75;">
                  At <strong><?= html_escape($site_name ?? 'Fresh Grocery Store'); ?></strong>, we believe families deserve complete transparency in what they eat and what they pay. Every fruit punnet, vegetable bunch, and dairy bottle clearly shows its exact <strong>Per Kg</strong> or <strong>Per Piece</strong> unit rate alongside pack savings.
               </p>
               <p class="text-secondary mb-4" style="line-height: 1.75;">
                  Our harvest teams collect produce at dawn, chill it immediately in our 2°C–4°C eco-facilities, and deliver it straight to your kitchen doorstep in insulated electric vans.
               </p>
               <div class="row g-3">
                  <div class="col-sm-6">
                     <div class="p-3 bg-white rounded-3 border d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:46px; height:46px; background:#ecfdf5; color:#108910;">
                           <i class="fa-solid fa-leaf fs-5"></i>
                        </div>
                        <div>
                           <h6 class="fw-bold mb-0" style="color:#003D29;">120+ Partner Farms</h6>
                           <small class="text-muted">100% Non-GMO &amp; Chemical-Free</small>
                        </div>
                     </div>
                  </div>
                  <div class="col-sm-6">
                     <div class="p-3 bg-white rounded-3 border d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:46px; height:46px; background:#ecfdf5; color:#108910;">
                           <i class="fa-solid fa-snowflake fs-5"></i>
                        </div>
                        <div>
                           <h6 class="fw-bold mb-0" style="color:#003D29;">2°C–4°C Cold Chain</h6>
                           <small class="text-muted">Locks in morning harvest crispness</small>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="mt-4">
                  <a href="<?= site_url('shop' . (empty($is_user_project) ? '?preview_tpl=grocery' : '')); ?>" class="btn btn-primary px-4 py-2">
                     <i class="fa-solid fa-basket-shopping me-2"></i>Browse Fresh Supermarket Aisles
                  </a>
               </div>
            </div>
         </div>
      </div>
   </section>
</main>
