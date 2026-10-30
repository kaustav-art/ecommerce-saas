<?php
$bazaro = isset($tpl_assets) ? rtrim($tpl_assets, '/') . '/' : base_url('assets/templates/bazaro/');
$curr   = isset($currency_symbol) ? $currency_symbol : '$';
?>
   <footer>

      <!-- footer area start -->
      <div class="aq-footer-area grc-footer-style pt-100" data-bg-color="#003D29" style="background-color:#003D29;">
         <div class="container container-1630">
            <div class="aq-footer-widget-wrap">
               <div class="row">
                  <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6">
                     <div class="aq-footer-widget footer-col-6-1 mb-90">
                        <div class="aq-footer-logo mb-25">
                           <a href="<?= site_url('home'); ?>">
                              <img data-width="120" src="<?= base_url('assets/images/logo/logo.webp'); ?>" alt="<?= html_escape($site_name); ?>" style="max-height:42px;width:auto;object-fit:contain;">
                           </a>
                        </div>
                        <div class="aq-footer-contact-wrap">
                           <div class="aq-footer-contact-item d-flex align-items-start">
                              <span>
                                 <svg xmlns="http://www.w3.org/2000/svg" width="18" height="16" viewBox="0 0 18 16" fill="none">
                                    <path d="M0.75 4.75C0.75 1.95 2.35 0.75 4.75 0.75H12.75C15.15 0.75 16.75 1.95 16.75 4.75V10.35C16.75 13.15 15.15 14.35 12.75 14.35H4.75M12.75 5.15028L10.246 7.15003C9.422 7.80596 8.07 7.80596 7.246 7.15003L4.75 5.15028M0.75 11.1503H5.55M0.75 7.94945H3.15" stroke="currentcolor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                 </svg>
                              </span>
                              <a href="mailto:<?= html_escape($store_settings['site_email'] ?? 'fresh@grocery.com'); ?>"><?= html_escape($store_settings['site_email'] ?? 'fresh@grocery.com'); ?></a>
                           </div>
                           <div class="aq-footer-contact-item d-flex align-items-start">
                              <span>
                                 <svg xmlns="http://www.w3.org/2000/svg" width="17" height="19" viewBox="0 0 17 19" fill="none">
                                    <path d="M8.25099 10.6922C9.74975 10.6922 10.9647 9.47717 10.9647 7.9784C10.9647 6.47964 9.74975 5.26465 8.25099 5.26465C6.75222 5.26465 5.53723 6.47964 5.53723 7.9784C5.53723 9.47717 6.75222 10.6922 8.25099 10.6922Z" stroke="currentcolor" stroke-width="1.5"/>
                                    <path d="M0.962124 6.39496C2.67562 -1.13745 13.8351 -1.12875 15.5399 6.40366C16.5401 10.8222 13.7916 14.5623 11.3822 16.876C9.63396 18.5634 6.86802 18.5634 5.11104 16.876C2.71041 14.5623 -0.0381381 10.8135 0.962124 6.39496Z" stroke="currentcolor" stroke-width="1.5"/>
                                 </svg>
                              </span>
                              <a href="<?= site_url('contact'); ?>">
                                 <?= html_escape($store_settings['site_address'] ?? '29 SE 2nd Ave, Miami Florida 44041, United States'); ?>
                              </a>
                           </div>
                           <div class="aq-footer-contact-item d-flex align-items-start">
                              <a class="fs-bold" href="tel:<?= html_escape($store_settings['site_phone'] ?? '40276328246'); ?>"><?= html_escape($store_settings['site_phone'] ?? '(+92) 413 90 762'); ?></a>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                     <div class="aq-footer-widget mb-90">
                        <h4 class="aq-footer-widget-title">Departments</h4>
                        <div class="aq-footer-widget-menu">
                           <ul>
                              <?php if (!empty($nav_categories)): ?>
                                 <?php $fc = 0; foreach ($nav_categories as $cat): if ($fc++ >= 5) break; ?>
                                    <li><a href="<?= site_url('shop/' . $cat['slug']); ?>"><?= html_escape($cat['name']); ?></a></li>
                                 <?php endforeach; ?>
                              <?php else: ?>
                                 <li><a href="<?= site_url('shop'); ?>">Fresh Produce</a></li>
                                 <li><a href="<?= site_url('shop'); ?>">Organic Dairy</a></li>
                                 <li><a href="<?= site_url('shop'); ?>">Artisan Bakery</a></li>
                              <?php endif; ?>
                           </ul>
                        </div>
                     </div>
                  </div>
                  <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                     <div class="aq-footer-widget mb-90">
                        <h4 class="aq-footer-widget-title">Information</h4>
                        <div class="aq-footer-widget-menu">
                           <ul>
                              <li><a href="<?= site_url('order/track'); ?>">Track Order</a></li>
                              <li><a href="<?= site_url('returns-policy'); ?>">Delivery &amp; Returns</a></li>
                              <li><a href="<?= site_url('about'); ?>">About Our Farms</a></li>
                              <li><a href="<?= site_url('contact'); ?>">Help &amp; Support</a></li>
                              <li><a href="<?= site_url('privacy'); ?>">Privacy Policy</a></li>
                           </ul>
                        </div>
                     </div>
                  </div>
                  <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                     <div class="aq-footer-widget mb-90">
                        <h4 class="aq-footer-widget-title">Account</h4>
                        <div class="aq-footer-widget-menu">
                           <ul>
                              <li><a href="<?= site_url('cart'); ?>">Grocery Bag</a></li>
                              <li><a href="<?= !empty($is_logged_in) ? site_url('account') : site_url('auth/login'); ?>">My Account</a></li>
                              <li><a href="<?= !empty($is_logged_in) ? site_url('account/orders') : site_url('order/track'); ?>">My Orders</a></li>
                              <li><a href="<?= site_url('wishlist'); ?>">Saved Items</a></li>
                              <li><a href="<?= site_url('checkout'); ?>" onclick="return handleCheckoutClick(event, '<?= site_url('checkout'); ?>')">Express Checkout</a></li>
                           </ul>
                        </div>
                     </div>
                  </div>
                  <div class="col-xl-3 col-lg-6 col-md-7 col-sm-10">
                     <div class="aq-footer-widget mb-90">
                        <h4 class="aq-footer-widget-title">Subscribe.</h4>
                        <div class="aq-footer-widget-input-box mb-25">
                           <form action="<?= site_url('newsletter/subscribe'); ?>" method="POST">
                              <p class="mb-10">Get weekly organic harvest specials &amp; recipes</p>
                              <div class="aq-footer-widget-input p-relative">
                                 <input class="aq-form-control h-56 brr-0" type="email" name="email" placeholder="Enter Your Email" required>
                                 <button class="aq-btn-subscribe" type="submit">Subscribe</button>
                              </div>
                           </form>
                        </div>
                        <div class="aq-footer-widget-social-box">
                           <h4 class="aq-footer-widget-social-title mb-10">Follow Us On</h4>
                           <div class="aq-footer-widget-social">
                              <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                              <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
                              <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
                              <a href="#"><i class="fa-brands fa-instagram"></i></a>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="aq-copyright-area">
            <div class="container container-1630">
               <div class="aq-copyright-border pt-15 pb-15">
                  <div class="row align-items-center">
                     <div class="col-xl-5 col-lg-5 col-md-6">
                        <div class="aq-copyright-text text-center text-md-start pb-20">
                           <p class="mb-0">All rights reserved — <?= date('Y'); ?> &copy; <a href="<?= site_url('home'); ?>"><?= html_escape($site_name); ?></a></p>
                        </div>
                     </div>
                     <div class="col-xl-7 col-lg-7 col-md-6">
                        <div class="aq-copyright-payment text-center text-md-end pb-20">
                           <a href="<?= site_url('checkout'); ?>"><img src="<?= $bazaro; ?>img/payment/payment.png" alt="Payment Methods"></a>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- footer area end -->

   </footer>

   <!-- Bazaro JS Core -->
   <script src="<?= $bazaro; ?>js/vendor/jquery.js"></script>
   <script src="<?= $bazaro; ?>js/bootstrap-bundle.js"></script>
   <script src="<?= $bazaro; ?>js/swiper-bundle.js"></script>
   <script src="<?= $bazaro; ?>js/slick.min.js"></script>
   <script src="<?= $bazaro; ?>js/magnific-popup.js"></script>
   <script src="<?= $bazaro; ?>js/nice-select.js"></script>
   <script src="<?= $bazaro; ?>js/purecounter.js"></script>
   <script src="<?= $bazaro; ?>js/isotope-pkgd.js"></script>
   <script src="<?= $bazaro; ?>js/lazysize.min.js"></script>
   <script src="<?= $bazaro; ?>js/slider-active.js?v=<?= filemtime(FCPATH . 'assets/templates/bazaro/js/slider-active.js'); ?>"></script>
   <script src="<?= $bazaro; ?>js/imagesloaded-pkgd.js"></script>
   <script src="<?= $bazaro; ?>js/ajax-form.js"></script>
   <script src="<?= $bazaro; ?>js/main.js"></script>

   <script>
   function showGrcToast(msg, isError) {
       var t = document.getElementById('grcToast');
       var txt = document.getElementById('grcToastText');
       if (!t || !txt) return;
       txt.textContent = msg;
       t.style.background = isError ? '#dc2626' : '#003D29';
       t.style.display = 'inline-flex';
       clearTimeout(window._grcToastTimer);
       window._grcToastTimer = setTimeout(function() {
           t.style.display = 'none';
       }, 3000);
   }

   function openGrcCartDrawer() {
       var d = document.getElementById('grcCartMiniDrawer');
       var ov = document.querySelector('.body-overlay');
       if (d) {
           d.classList.add('opened');
           d.classList.add('cartmini-opened');
       }
       if (ov) ov.classList.add('opened');
       if (window.jQuery) {
           jQuery('.aq-cartmini-active').addClass('opened');
           jQuery('.body-overlay').addClass('opened');
       }
   }

   function closeGrcCartDrawer() {
       var d = document.getElementById('grcCartMiniDrawer');
       var ov = document.querySelector('.body-overlay');
       if (d) {
           d.classList.remove('opened');
           d.classList.remove('cartmini-opened');
       }
       if (ov) ov.classList.remove('opened');
       if (window.jQuery) {
           jQuery('.aq-cartmini-active').removeClass('opened');
           jQuery('.body-overlay').removeClass('opened');
       }
   }

   function renderGrcCartDrawer(data) {
       if (!data) return;
       var count = (data.cart_summary && typeof data.cart_summary.item_count !== 'undefined')
           ? data.cart_summary.item_count
           : (typeof data.cart_count !== 'undefined' ? data.cart_count : null);
       var subtotal = (data.cart_summary && typeof data.cart_summary.subtotal !== 'undefined')
           ? parseFloat(data.cart_summary.subtotal)
           : (typeof data.cart_total !== 'undefined' ? parseFloat(data.cart_total) : 0);

       if (count !== null) {
           document.querySelectorAll('.cart-count, .count-box.cart-count').forEach(function(el) {
               el.textContent = count;
           });
       }
       var subEl = document.getElementById('grcCartMiniSubtotal');
       if (subEl) {
           subEl.textContent = window.CURRENCY_SYMBOL + subtotal.toFixed(2);
       }

       var body = document.getElementById('grcCartMiniBody');
       if (body && Array.isArray(data.cart_items)) {
           if (data.cart_items.length === 0) {
               body.innerHTML = '<div class="cartmini-empty text-center py-5">' +
                   '<p class="text-muted">Your grocery bag is empty</p>' +
                   '<a href="' + window.SITE_URL + '/shop" class="aq-btn-black border-btn mt-2">Browse Fresh Aisles</a></div>';
           } else {
               var html = '';
               data.cart_items.forEach(function(item) {
                   var rawImg = (item.image || '').replace(/^\/+/, '');
                   var img = '<?= $bazaro; ?>img/product/grocery-p-1.jpg';
                   if (rawImg) {
                       if (rawImg.indexOf('http') === 0) img = rawImg;
                       else if (rawImg.indexOf('assets/') === 0) img = window.BASE_URL + rawImg;
                       else if (rawImg.indexOf('products/') === 0) img = window.BASE_URL + 'assets/images/' + rawImg;
                       else img = window.BASE_URL + 'assets/images/products/' + rawImg;
                   }
                   var itemName = item.name || item.title || 'Fresh Grocery Item';
                   var pUrl = window.SITE_URL + '/product/' + (item.slug || item.product_id) + (<?= empty($is_user_project) ? "'?preview_tpl=grocery'" : "''"; ?>);
                   var key = item.cart_key || item.product_id;
                   var varLabel = item.size || item.variant_title || '';
                   if (varLabel && varLabel.indexOf('/') !== -1) {
                       var parts = varLabel.split('/');
                       varLabel = parts[parts.length - 1].trim();
                   }
                   var varHtml = varLabel ? '<span class="aq-cartmini-product-variant text-muted" style="font-size: 12px; display: block; margin-bottom: 2px;">' + varLabel + '</span>' : '';
                   html += '<div class="aq-cartmini-product-item mb-15 item-delete d-flex align-items-center">' +
                       '<div class="aq-cartmini-product-thumbnail"><a href="' + pUrl + '"><img src="' + img + '" alt=""></a></div>' +
                       '<div class="aq-cartmini-product-summary">' +
                       '<h4 class="aq-product-title"><a href="' + pUrl + '">' + itemName + '</a></h4>' +
                       varHtml +
                       '<span class="aq-cartmini-product-price">' + window.CURRENCY_SYMBOL + parseFloat(item.price).toFixed(2) + ' &times; ' + item.quantity + '</span>' +
                       '<div class="aq-product-details-quantity d-flex align-items-center mt-1">' +
                       '<button type="button" onclick="grcRemoveCartItem(\'' + key + '\')" class="aq-line-anim aq-cartmini-remove">Remove</button>' +
                       '</div></div></div>';
               });
               body.innerHTML = html;
           }
       }
   }

   function grcAddToCart(productId, quantity, variantId) {
       quantity = quantity || 1;
       var fd = new FormData();
       fd.append('product_id', productId);
       fd.append('quantity', quantity);
       if (variantId) {
           fd.append('variant_id', variantId);
       }

       fetch('<?= site_url("cart/add"); ?>', {
           method: 'POST',
           headers: { 'X-Requested-With': 'XMLHttpRequest' },
           body: fd
       })
       .then(function(r) { return r.json(); })
       .then(function(res) {
           if (res && res.success) {
               renderGrcCartDrawer(res);
               showGrcToast(res.message || 'Added to grocery bag!');
               openGrcCartDrawer();
           } else {
               showGrcToast((res && res.message) ? res.message : 'Could not add item', true);
           }
       })
       .catch(function() {
           showGrcToast('Network error adding to bag', true);
       });
   }
   window.grcAddToCart = grcAddToCart;
   window.renderGrcCartDrawer = renderGrcCartDrawer;
   window.openGrcCartDrawer = openGrcCartDrawer;
   window.closeGrcCartDrawer = closeGrcCartDrawer;
   window.showGrcToast = showGrcToast;

   function grcRemoveCartItem(cartKey) {
       var fd = new FormData();
       fd.append('cart_key', cartKey);
       fetch('<?= site_url("cart/remove"); ?>', {
           method: 'POST',
           headers: { 'X-Requested-With': 'XMLHttpRequest' },
           body: fd
       })
       .then(function(r) { return r.json(); })
       .then(function(res) {
           if (res.success) {
               renderGrcCartDrawer(res);
               showGrcToast('Item removed from bag');
           }
       });
   }
   function grcToggleWishlist(productId, btnEl) {
       if (typeof event !== 'undefined' && event) {
           event.preventDefault();
           event.stopPropagation();
       }
       productId = parseInt(productId, 10);
       if (!productId || isNaN(productId)) return;

       if (!btnEl && typeof event !== 'undefined' && event) {
           btnEl = event.currentTarget || (event.target ? event.target.closest('button') : null);
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
           if (res.require_login) {
               showGrcToast(res.message || 'Please login first to add items to your wishlist.', true);
               if (typeof window.openLoginModal === 'function') {
                   setTimeout(function() {
                       window.openLoginModal(res.redirect || window.location.href, res.message || 'Please login first.');
                   }, 350);
               }
               return;
           }

           if (res.success || res.status === 'success') {
               var isAdded = (res.action === 'added' || res.is_wishlisted === true);

               var selectors = [
                   '.btn-grc-wish[data-product-id="' + productId + '"]',
                   'button[data-product-id="' + productId + '"]',
                   'button[onclick*="grcToggleWishlist(' + productId + ')"]',
                   'button[onclick*="grcToggleWishlist(' + productId + ',"]'
               ];
               var buttons = document.querySelectorAll(selectors.join(', '));

               buttons.forEach(function(btn) {
                   btn.setAttribute('data-product-id', productId);
                   var icon = btn.querySelector('i');
                   var svg = btn.querySelector('svg');
                   var tip = btn.querySelector('.aq-tooltip-item');
                   if (isAdded) {
                       btn.classList.add('active', 'in-wishlist');
                       btn.setAttribute('title', 'Remove from Wishlist');
                       if (icon) {
                           icon.className = 'fa-solid fa-heart text-danger';
                           icon.style.color = '#ef4444';
                       }
                       if (svg) {
                           svg.style.fill = '#ef4444';
                           svg.style.stroke = '#ef4444';
                       }
                       if (tip) tip.textContent = 'Remove';
                   } else {
                       btn.classList.remove('active', 'in-wishlist');
                       btn.setAttribute('title', 'Add to Wishlist');
                       if (icon) {
                           icon.className = 'fa-regular fa-heart';
                           icon.style.color = '';
                       }
                       if (svg) {
                           svg.style.fill = 'none';
                           svg.style.stroke = 'currentColor';
                       }
                       if (tip) tip.textContent = 'Wishlist';
                   }
               });

               if (btnEl) {
                   btnEl.setAttribute('data-product-id', productId);
                   var bIcon = btnEl.querySelector('i');
                   var bSvg = btnEl.querySelector('svg');
                   var bTip = btnEl.querySelector('.aq-tooltip-item');
                   if (isAdded) {
                       btnEl.classList.add('active', 'in-wishlist');
                       btnEl.setAttribute('title', 'Remove from Wishlist');
                       if (bIcon) {
                           bIcon.className = 'fa-solid fa-heart text-danger';
                           bIcon.style.color = '#ef4444';
                       }
                       if (bSvg) {
                           bSvg.style.fill = '#ef4444';
                           bSvg.style.stroke = '#ef4444';
                       }
                       if (bTip) bTip.textContent = 'Remove';
                   } else {
                       btnEl.classList.remove('active', 'in-wishlist');
                       btnEl.setAttribute('title', 'Add to Wishlist');
                       if (bIcon) {
                           bIcon.className = 'fa-regular fa-heart';
                           bIcon.style.color = '';
                       }
                       if (bSvg) {
                           bSvg.style.fill = 'none';
                           bSvg.style.stroke = 'currentColor';
                       }
                       if (bTip) bTip.textContent = 'Wishlist';
                   }
               }

               if (typeof res.wishlist_count !== 'undefined') {
                   document.querySelectorAll('.wishlist-count').forEach(function(el) {
                       el.textContent = res.wishlist_count;
                   });
               }

               showGrcToast(res.message || (isAdded ? 'Added to your wishlist!' : 'Removed from your wishlist.'));
           } else {
               showGrcToast((res && res.message) ? res.message : 'Unable to update wishlist.', true);
           }
       })
       .catch(function(err) {
           console.error('Wishlist toggle error:', err);
           showGrcToast('Failed to update wishlist. Please try again.', true);
       });
   }
   window.grcToggleWishlist = grcToggleWishlist;

   // Wishlist state sync on page load
   window.USER_WISHLIST_IDS = <?= json_encode(array_values(array_map('intval', $user_wishlist_ids ?? []))); ?>;
   function syncGrcWishlistStates() {
       if (!window.USER_WISHLIST_IDS || !Array.isArray(window.USER_WISHLIST_IDS)) return;
       window.USER_WISHLIST_IDS.forEach(function(pId) {
           pId = parseInt(pId, 10);
           if (!pId) return;
           var selectors = [
               '.btn-grc-wish[data-product-id="' + pId + '"]',
               'button[data-product-id="' + pId + '"]',
               'button[onclick*="grcToggleWishlist(' + pId + ')"]',
               'button[onclick*="grcToggleWishlist(' + pId + ',"]'
           ];
           document.querySelectorAll(selectors.join(', ')).forEach(function(btn) {
               btn.classList.add('active', 'in-wishlist');
               btn.setAttribute('title', 'Remove from Wishlist');
               var icon = btn.querySelector('i');
               if (icon) {
                   icon.className = 'fa-solid fa-heart text-danger';
                   icon.style.color = '#ef4444';
               }
               var svg = btn.querySelector('svg');
               if (svg) {
                   svg.style.fill = '#ef4444';
                   svg.style.stroke = '#ef4444';
               }
               var tip = btn.querySelector('.aq-tooltip-item');
               if (tip) tip.textContent = 'Remove';
           });
       });
   }
   if (document.readyState === 'loading') {
       document.addEventListener('DOMContentLoaded', syncGrcWishlistStates);
   } else {
       syncGrcWishlistStates();
   }

   // Countdown handled dynamically by active flash sale

   // Normalize Grocery theme accents on Template 3
   document.addEventListener('DOMContentLoaded', function() {
       var defaultGrcImg = '<?= $bazaro; ?>img/product/grocery-p-3.jpg';
<?php if (empty($is_user_project)): ?>
       var siteBase = '<?= rtrim(base_url(), "/"); ?>';
       document.querySelectorAll('a[href]').forEach(function(a) {
           var href = a.getAttribute('href');
           if (!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0 || href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0) return;
           if (href.indexOf(siteBase) === 0 && href.indexOf('preview_tpl=') === -1) {
               var hashIdx = href.indexOf('#');
               var baseHref = hashIdx !== -1 ? href.substring(0, hashIdx) : href;
               var hashPart = hashIdx !== -1 ? href.substring(hashIdx) : '';
               var sep = baseHref.indexOf('?') === -1 ? '?' : '&';
               a.setAttribute('href', baseHref + sep + 'preview_tpl=grocery' + hashPart);
           }
       });

       document.querySelectorAll('form[action]').forEach(function(f) {
           var act = f.getAttribute('action');
           if (act && act.indexOf(siteBase) === 0 && act.indexOf('preview_tpl=') === -1) {
               var sep = act.indexOf('?') === -1 ? '?' : '&';
               f.setAttribute('action', act + sep + 'preview_tpl=grocery');
           }
       });
<?php endif; ?>

       // Convert any residual inline Flipkart-blue (#2874f0) or fashion fallback images on shared views to Grocery theme
       document.querySelectorAll('[style*="#2874f0"], [style*="rgb(40, 116, 240)"], [style*="#f0f5ff"], [style*="#e1ecff"]').forEach(function(el) {
           var st = el.getAttribute('style') || '';
           st = st.replace(/#2874f0/gi, '#108910')
                  .replace(/rgb\(40,\s*116,\s*240\)/gi, '#108910')
                  .replace(/#f0f5ff/gi, '#ecfdf5')
                  .replace(/#e1ecff/gi, '#bbf7d0');
           el.setAttribute('style', st);
       });

       document.querySelectorAll('img').forEach(function(img) {
           var onerr = img.getAttribute('onerror');
           if (onerr && onerr.indexOf('women-1.jpg') !== -1) {
               img.setAttribute('onerror', "this.src='" + defaultGrcImg + "'");
           }
           var src = img.getAttribute('src') || '';
           if (src.indexOf('womens/women-') !== -1) {
               img.setAttribute('src', defaultGrcImg);
           }
       });
   });
   </script>

</body>
</html>
