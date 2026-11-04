<style>

        /* Home Page Product Card Styles */
        .product-badge-group {
            position: absolute;
            top: 16px;
            left: 16px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
            z-index: 5;
            pointer-events: none;
        }
        .product-badge {
            display: inline-block;
            font-size: 13px;
            font-weight: 700;
            line-height: 1;
            padding: 5px 12px;
            border-radius: 40px;
            color: #ffffff;
            background-color: #DA3F3F;
        }
        .product-badge.badge-sale {
            background-color: #DA3F3F;
        }
        .product-badge.badge-flash-sale {
            background-color: #DA3F3F;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .animate-fade-in {
            animation: productFadeIn 0.35s ease-out;
        }
        @keyframes productFadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Uniform Product Card Size (Same as Home Page) */
        .card-product {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .card-product .card-product-wrapper {
            aspect-ratio: 3 / 4 !important;
            width: 100% !important;
            overflow: hidden !important;
            border-radius: 8px;
            background-color: #f8f9fa;
            position: relative;
        }
        .card-product .card-product-wrapper .product-img {
            display: block;
            width: 100% !important;
            height: 100% !important;
        }
        .card-product .card-product-wrapper .product-img img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            display: block;
        }
        @media (max-width: 767.98px) {
            .product-badge-group {
                top: 10px;
                left: 10px;
            }
            .product-badge {
                font-size: 10px;
                padding: 4px 9px;
            }
        }


        .wishlist-close-btn {
            position: absolute;
            top: 8px;
            left: 8px;
            z-index: 10;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.12);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4b5563;
            cursor: pointer;
            transition: all 0.2s ease;
            padding: 0;
        }
        .wishlist-close-btn:hover {
            background: #ef4444;
            color: #ffffff;
            border-color: #ef4444;
        }
        .wishlist-item-col {
            position: relative;
        }

</style>
<?php
$theme_primary = '#003D29';
$theme_accent  = '#108910';
if ($active_template === 'electronics') {
    $theme_primary = '#0f172a';
    $theme_accent  = '#2563eb';
} elseif ($active_template === 'fashion') {
    $theme_primary = '#181818';
    $theme_accent  = '#000000';
}
?>
<!-- breadcrumb -->
<div class="bg-light py-2 border-bottom">
    <div class="container container-1630" style="max-width: 1400px;">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="font-size: 13px;">
                <li class="breadcrumb-item"><a href="<?= site_url('home'); ?>" class="text-muted text-decoration-none"><i class="fa-solid fa-house me-1"></i>Home</a></li>
                <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">My Wishlist</li>
            </ol>
        </nav>
    </div>
</div>
<!-- /breadcrumb -->

<!-- wishlist content -->
<section class="py-4 py-md-5" style="background-color: #f8fafc; min-height: 75vh;">
    <div class="container container-1630" style="max-width: 1400px;">
        <div class="card border-0 shadow-sm rounded-3 p-3 p-md-4 bg-white mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pb-3 border-bottom">
                <div>
                    <h3 class="fw-bold text-dark m-0 d-flex align-items-center gap-2" style="font-size: 22px;">
                        <i class="fa-solid fa-heart text-danger"></i> My Wishlist
                        <span class="badge rounded-pill bg-light text-dark border px-2 py-1 fs-6 wishlist-count"><?= count($wishlist); ?></span>
                    </h3>
                    <p class="text-muted small m-0 mt-1">Manage and purchase products you saved for later</p>
                </div>
                <div>
                    <a href="<?= site_url('shop'); ?>" class="btn btn-outline-dark btn-sm rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                        <i class="fa-solid fa-bag-shopping"></i> Continue Shopping
                    </a>
                </div>
            </div>

            <?php if (!empty($wishlist)):  ?>
                <div class="row g-3 g-md-4 row-cols-xl-4 row-cols-lg-3 row-cols-sm-2 row-cols-2 pt-4" id="wishlist-items-grid">
                    <?php foreach ($wishlist as $item):
                        $item_id = (int)($item['id'] ?? 0);
                        $card_view = file_exists(APPPATH . 'views/template1/home/_product_card.php') ? 'template1/home/_product_card' : 'home/_product_card';
                     ?>
                        <div class="col position-relative wishlist-item-col" id="wishlist-item-col-<?= $item_id; ?>">
                            <!-- Wishlist Close Button -->
                            <button type="button"
                               onclick="removeWishlistPageItem(<?= $item_id; ?>, event);"
                               class="wishlist-close-btn"
                               title="Remove from Wishlist"
                               aria-label="Remove from Wishlist">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                            <?php $this->load->view($card_view, [
                                'p'                 => $item,
                                'currency_symbol'   => $currency_symbol ?? '$',
                                'user_wishlist_ids' => $user_wishlist_ids ?? array_column($wishlist, 'id')
                            ]);  ?>
                        </div>
                    <?php endforeach;  ?>
                </div>
            <?php endif;  ?>

            

            <div class="text-center py-5 my-4 <?= !empty($wishlist) ? 'd-none' : ''; ?>" id="wishlist-empty-state">
                <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle bg-light shadow-sm" style="width: 80px; height: 80px;">
                    <i class="fa-regular fa-heart fs-1 text-muted" style="opacity: 0.6;"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Your Wishlist is Empty</h4>
                <p class="text-muted mx-auto mb-4" style="max-width: 460px; font-size: 14px;">
                    Explore our catalog, discover fresh products and deals, and click the heart icon on any product to save it here for later.
                </p>
                <a href="<?= site_url('shop'); ?>" class="btn rounded-pill px-4 py-2 fw-semibold text-white shadow-sm" style="background-color: <?= $theme_primary; ?>; font-size: 14px;">
                    <i class="fa-solid fa-store me-2"></i> Explore Store Products
                </a>
            </div>
        </div>
    </div>
</section>

<script>
function removeWishlistPageItem(productId, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    var col = document.getElementById('wishlist-item-col-' + productId);
    if (col) {
        col.style.opacity = '0.4';
        col.style.pointerEvents = 'none';
    }

    var fd = new FormData();
    fd.append('product_id', productId);

    fetch('<?= site_url("wishlist/toggle"); ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res && res.success) {
            if (col) col.remove();
            if (typeof window.showAppToast === 'function') {
                window.showAppToast('Removed from your wishlist.', 'success');
            }
            var remaining = document.querySelectorAll('#wishlist-items-grid .col').length;
            document.querySelectorAll('.wishlist-count').forEach(function(el) {
                el.textContent = res.wishlist_count !== undefined ? res.wishlist_count : remaining;
            });
            var headerCounter = document.getElementById('wishlist-counter');
            if (headerCounter) {
                var cnt = res.wishlist_count !== undefined ? res.wishlist_count : remaining;
                headerCounter.textContent = cnt;
                headerCounter.style.display = cnt > 0 ? '' : 'none';
            }
            if (remaining === 0) {
                var grid = document.getElementById('wishlist-items-grid');
                if (grid) grid.remove();
                var emptyState = document.getElementById('wishlist-empty-state');
                if (emptyState) emptyState.classList.remove('d-none');
            }
        } else {
            if (col) {
                col.style.opacity = '1';
                col.style.pointerEvents = 'auto';
            }
            if (typeof window.showAppToast === 'function') {
                window.showAppToast((res && res.message) ? res.message : 'Could not remove item.', 'danger');
            }
        }
    })
    .catch(function(err) {
        if (col) {
            col.style.opacity = '1';
            col.style.pointerEvents = 'auto';
        }
        if (typeof window.showAppToast === 'function') {
            window.showAppToast('Network error removing item.', 'danger');
        }
    });
}
window.grcToggleWishlist = removeWishlistPageItem;

        // Delegated Color Swatch Hover/Click Preview for all .card-product (including Swiper loop clones)
        if (!window._fshColorSwatchBound) {
            window._fshColorSwatchBound = true;
            ['click', 'mouseover'].forEach(function(evtType) {
                document.addEventListener(evtType, function(e) {
                    var swatch = e.target.closest('.card-product .color-swatch');
                    if (!swatch) return;
                    var card = swatch.closest('.card-product');
                    var swatchImg = swatch.querySelector('img');
                    if (card && swatchImg) {
                        var newSrc = swatchImg.getAttribute('src') || swatchImg.getAttribute('data-src');
                        var prodImg = card.querySelector('.img-product');
                        if (prodImg && newSrc) {
                            prodImg.setAttribute('src', newSrc);
                        }
                    }
                    if (swatch.parentElement) {
                        swatch.parentElement.querySelectorAll('.color-swatch.active').forEach(function(sib) {
                            sib.classList.remove('active');
                        });
                    }
                    swatch.classList.add('active');
                });
            });
        }

</script>
<!-- /wishlist content -->