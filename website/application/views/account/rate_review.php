        <?php
        $img_src = !empty($item['product_image']) ? base_url('assets/images/' . $item['product_image']) : base_url('assets/images/products/womens/women-1.jpg');
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_hash = $this->security->get_csrf_hash();
        ?>

        <!-- breadcrumb -->
        <div class="py-2 border-bottom" style="background-color: #f1f3f6;">
            <div class="fk-orders-container px-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 12px;">
                        <li class="breadcrumb-item"><a href="<?= site_url('home'); ?>" class="text-muted text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url('account/profile'); ?>" class="text-muted text-decoration-none">My Account</a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url('account/orders'); ?>" class="text-muted text-decoration-none">My Orders</a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url('account/order/' . $order['order_number']); ?>" class="text-muted text-decoration-none">#<?= html_escape($order['order_number']); ?></a></li>
                        <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Ratings & Reviews</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- /breadcrumb -->

        <!-- rate-review-section -->
        <section class="py-3" style="background-color: #f1f3f6; min-height: 85vh;">
            <div class="fk-orders-container px-3">

                <!-- Flash Messages -->
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show rounded-1 py-2 px-3 small mb-3" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i><?= $this->session->flashdata('success'); ?>
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show rounded-1 py-2 px-3 small mb-3" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i><?= $this->session->flashdata('error'); ?>
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Top Header Card (Ratings & Reviews + Product Info) -->
                <div class="card border rounded-1 p-3 mb-3 bg-white shadow-none" style="border-color: #e0e0e0 !important;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="fw-bold text-dark mb-0" style="font-size: 16px;">Ratings & Reviews</h5>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-truncate text-secondary fw-semibold small" style="max-width: 320px; font-size: 13px;" title="<?= html_escape($item['product_title']); ?>">
                                <?= html_escape($item['product_title']); ?>
                            </span>
                            <img src="<?= $img_src; ?>" alt="<?= html_escape($item['product_title']); ?>" class="border rounded-1 flex-shrink-0" style="width: 44px; height: 44px; object-fit: contain; background-color: #fafafa;" onerror="this.src='<?= base_url('assets/images/products/womens/women-1.jpg'); ?>'">
                        </div>
                    </div>
                </div>

                <!-- Main Form Card (Matching rate_review_form.PNG) -->
                <div class="card border rounded-1 bg-white shadow-none overflow-hidden" style="border-color: #e0e0e0 !important;">
                    <div class="row g-0">
                        <!-- Left Guidelines Column (order-2 on mobile, order-md-1 on desktop) -->
                        <div class="col-lg-3 col-md-4 col-12 border-end order-2 order-md-1 p-3 p-md-4 bg-white">
                            <h6 class="fw-bold text-dark mb-3 mb-md-4" style="font-size: 14px;">What makes a good review</h6>

                            <div class="mb-3">
                                <div class="fw-bold text-dark mb-1" style="font-size: 13px;">Have you used this product?</div>
                                <div class="text-secondary" style="font-size: 12px; line-height: 1.5;">
                                    Your review should be about your experience with the product.
                                </div>
                            </div>
                            <hr class="my-3" style="border-color: #f0f0f0;">

                            <div class="mb-3">
                                <div class="fw-bold text-dark mb-1" style="font-size: 13px;">Why review a product?</div>
                                <div class="text-secondary" style="font-size: 12px; line-height: 1.5;">
                                    Your valuable feedback will help fellow shoppers decide!
                                </div>
                            </div>
                            <hr class="my-3" style="border-color: #f0f0f0;">

                            <div class="mb-3">
                                <div class="fw-bold text-dark mb-1" style="font-size: 13px;">How to review a product?</div>
                                <div class="text-secondary" style="font-size: 12px; line-height: 1.5;">
                                    Your review should include facts. An honest opinion is always appreciated. If you have an issue with the product or service please contact us from the <a href="<?= site_url('contact'); ?>" class="text-primary text-decoration-none">help centre</a>.
                                </div>
                            </div>
                        </div>

                        <!-- Right Form Column (order-1 on mobile, order-md-2 on desktop) -->
                        <div class="col-lg-9 col-md-8 col-12 order-1 order-md-2 p-3 p-md-4 bg-white border-bottom border-bottom-md-0">
                            <form action="<?= site_url('account/rate_review/' . $order['order_number'] . '/' . $item['product_id']); ?>" method="POST" enctype="multipart/form-data" id="rate-review-form">
                                <input type="hidden" name="<?= $csrf_name; ?>" value="<?= $csrf_hash; ?>">

                                <!-- Rate this product -->
                                <div class="mb-4">
                                    <h6 class="fw-bold text-dark mb-4" style="font-size: 14px;">Rate this product</h6>
                                    <div class="position-relative d-inline-block" style="padding-top: 26px;">
                                        <!-- Tooltip Box directly matching rateing_review.png -->
                                        <div id="star-tooltip-badge" class="position-absolute" style="top: 0; transform: translateX(-50%); background-color: #212121; color: #fff; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 3px; pointer-events: none; white-space: nowrap; z-index: 10; transition: left 0.15s ease-in-out;">
                                            <span id="star-tooltip-text">Excellent</span>
                                            <div style="position: absolute; top: 100%; left: 50%; transform: translateX(-50%); width: 0; height: 0; border-left: 5px solid transparent; border-right: 5px solid transparent; border-top: 5px solid #212121;"></div>
                                        </div>

                                        <div class="fk-star-row d-inline-flex gap-2" id="star-rating-row" style="font-size: 26px;">
                                            <?php 
                                            $labels = [1 => 'Very Bad', 2 => 'Bad', 3 => 'Good', 4 => 'Very Good', 5 => 'Excellent'];
                                            for ($s = 1; $s <= 5; $s++): 
                                            ?>
                                                <i class="fa-solid fa-star star-clickable" data-star="<?= $s; ?>" title="<?= $labels[$s]; ?>" style="cursor: pointer; color: <?= ($s <= $prefill_rating) ? '#ff9f00' : '#d1d5db'; ?>; transition: color 0.15s ease-in-out;"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <input type="hidden" name="rating" id="hidden-rating-input" value="<?= $prefill_rating; ?>">
                                    </div>
                                </div>

                                <hr class="my-4" style="border-color: #f0f0f0;">

                                <!-- Review this product -->
                                <div class="mb-4">
                                    <h6 class="fw-bold text-dark mb-3" style="font-size: 14px;">Review this product</h6>

                                    <!-- Framed Box matching rate_review_form.PNG -->
                                    <div class="card border rounded-1 p-3 bg-white" style="border-color: #d1d5db !important;">
                                        <div class="text-secondary small fw-semibold mb-1" style="font-size: 12px;">Description</div>
                                        <textarea name="review" id="review-description" rows="6" class="form-control rounded-1" placeholder="Description..." required style="height: auto !important; min-height: 136px !important; resize: vertical; font-size: 14px; line-height: 1.5 !important; border: 1px solid #d1d5db !important; padding: 10px 12px !important; background-color: #ffffff !important;"><?= html_escape($existing_review['review'] ?? ''); ?></textarea>

                                        <div class="text-secondary small fw-semibold mt-3 mb-1" style="font-size: 12px;">Title (optional)</div>
                                        <input type="text" name="title" id="review-title-input" class="form-control rounded-1" placeholder="Review title..." value="<?= html_escape($existing_review['title'] ?? ''); ?>" style="font-size: 14px; height: 44px !important; line-height: 1.5 !important; border: 1px solid #d1d5db !important; padding: 8px 12px !important; background-color: #ffffff !important;">
                                    </div>

                                    <!-- Camera Upload Button -->
                                    <div class="mt-3">
                                        <label class="btn btn-light border d-inline-flex align-items-center justify-content-center rounded-1" style="width: 44px; height: 44px; border-color: #d1d5db !important; cursor: pointer;" title="Add Photos">
                                            <i class="fa-solid fa-camera text-secondary fs-5"></i>
                                            <input type="file" name="review_images[]" id="review-photos-input" accept="image/*" multiple class="d-none" onchange="handleImageSelection(this)">
                                        </label>
                                        <div id="selected-photos-preview" class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                                            <?php
                                            if (!empty($existing_review['images'])):
                                                $old_imgs = json_decode($existing_review['images'], true) ?: [];
                                                foreach ($old_imgs as $oi):
                                            ?>
                                                <div class="position-relative">
                                                    <img src="<?= base_url('assets/images/' . $oi); ?>" class="border rounded-1" style="width: 50px; height: 50px; object-fit: cover;">
                                                </div>
                                            <?php
                                                endforeach;
                                            endif;
                                            ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Orange Submit Button at Bottom Right (Full-width on mobile) -->
                                <div class="d-flex justify-content-end mt-4 pt-2">
                                    <button type="submit" class="btn text-white fw-bold px-5 py-2 w-100 w-sm-auto rounded-1 text-uppercase" style="background-color: #fb641b; border-color: #fb641b; font-size: 15px; letter-spacing: 0.5px; box-shadow: 0 1px 3px rgba(0,0,0,0.12); min-height: 46px;">
                                        SUBMIT
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /rate-review-section -->

        <style>
        .fk-orders-container {
            max-width: 1680px;
            margin: 0 auto;
        }
        @media (min-width: 992px) {
            .fk-orders-container {
                min-width: 978px;
            }
        }
        @media (max-width: 991px) {
            .fk-orders-container {
                min-width: 100% !important;
                max-width: 100% !important;
            }
        }
        .star-clickable:hover {
            transform: scale(1.1);
        }
        #review-description {
            height: auto !important;
            min-height: 136px !important;
            line-height: 1.5 !important;
            border: 1px solid #d1d5db !important;
            border-radius: 6px !important;
            box-shadow: none !important;
            padding: 10px 12px !important;
            background-color: #ffffff !important;
        }
        #review-title-input {
            height: 44px !important;
            line-height: 1.5 !important;
            border: 1px solid #d1d5db !important;
            border-radius: 6px !important;
            box-shadow: none !important;
            padding: 8px 12px !important;
            background-color: #ffffff !important;
        }
        #review-description:focus,
        #review-title-input:focus {
            border-color: var(--bs-primary, #108910) !important;
            box-shadow: 0 0 0 3px rgba(16, 137, 16, 0.12) !important;
        }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var stars = document.querySelectorAll('.star-clickable');
            var ratingInput = document.getElementById('hidden-rating-input');
            var tooltipBadge = document.getElementById('star-tooltip-badge');
            var tooltipText = document.getElementById('star-tooltip-text');

            var labelMap = {
                1: 'Very Bad',
                2: 'Bad',
                3: 'Good',
                4: 'Very Good',
                5: 'Excellent'
            };

            function positionTooltip(val) {
                var targetStar = document.querySelector('.star-clickable[data-star="' + val + '"]');
                if (targetStar && tooltipBadge) {
                    var offsetCenter = targetStar.offsetLeft + (targetStar.offsetWidth / 2);
                    tooltipBadge.style.left = offsetCenter + 'px';
                    if (tooltipText && labelMap[val]) {
                        tooltipText.textContent = labelMap[val];
                    }
                }
            }

            function highlightStars(val) {
                stars.forEach(function(st) {
                    var sVal = parseInt(st.getAttribute('data-star'), 10);
                    if (sVal <= val) {
                        st.style.color = '#ff9f00';
                    } else {
                        st.style.color = '#d1d5db';
                    }
                });
                positionTooltip(val);
            }

            stars.forEach(function(star) {
                star.addEventListener('mouseenter', function() {
                    var val = parseInt(this.getAttribute('data-star'), 10);
                    highlightStars(val);
                });

                star.addEventListener('click', function() {
                    var val = parseInt(this.getAttribute('data-star'), 10);
                    ratingInput.value = val;
                    highlightStars(val);
                });
            });

            var starContainer = document.getElementById('star-rating-row');
            if (starContainer) {
                starContainer.addEventListener('mouseleave', function() {
                    var current = parseInt(ratingInput.value, 10) || 5;
                    highlightStars(current);
                });
            }

            // Initial position based on prefill rating
            setTimeout(function() {
                var initialRating = parseInt(ratingInput.value, 10) || 5;
                highlightStars(initialRating);
            }, 50);
        });

        function handleImageSelection(input) {
            var container = document.getElementById('selected-photos-preview');
            container.innerHTML = '';
            if (input.files && input.files.length > 0) {
                for (var i = 0; i < input.files.length; i++) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'border rounded-1';
                        img.style = 'width: 50px; height: 50px; object-fit: cover;';
                        container.appendChild(img);
                    };
                    reader.readAsDataURL(input.files[i]);
                }
            }
        }
        </script>
