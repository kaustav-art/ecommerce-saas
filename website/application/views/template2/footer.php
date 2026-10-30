<?php
$bazaro = isset($tpl_assets) ? rtrim($tpl_assets, '/') . '/' : base_url('assets/templates/bazaro/');
$curr   = isset($currency_symbol) ? $currency_symbol : '$';
?>
   <footer>

      <!-- footer area start -->
      <div class="aq-footer-area pt-100" data-bg-color="#F1EFEE" style="background-color:#F1EFEE;">
         <div class="container">
            <div class="aq-footer-widget-wrap">
               <div class="row">
                  <div class="col-xl-3 col-lg-4 col-md-5 col-sm-6">
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
                              <a href="mailto:<?= html_escape($store_settings['site_email'] ?? 'support@electronics.com'); ?>"><?= html_escape($store_settings['site_email'] ?? 'support@electronics.com'); ?></a>
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
                              <a class="fs-bold" href="tel:<?= html_escape($store_settings['site_phone'] ?? '+18005550199'); ?>"><?= html_escape($store_settings['site_phone'] ?? '(+92) 413 90 762'); ?></a>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="col-xl-2 col-lg-4 col-md-3 col-sm-6">
                     <div class="aq-footer-widget mb-90">
                        <h4 class="aq-footer-widget-title">Shopping</h4>
                        <div class="aq-footer-widget-menu">
                           <ul>
                              <?php if (!empty($nav_categories)): ?>
                                 <?php $fc = 0; foreach ($nav_categories as $cat): if ($fc++ >= 5) break; ?>
                                    <li><a href="<?= site_url('shop/' . $cat['slug']); ?>"><?= html_escape($cat['name']); ?></a></li>
                                 <?php endforeach; ?>
                              <?php else: ?>
                                 <li><a href="<?= site_url('shop'); ?>">All Electronics</a></li>
                                 <li><a href="<?= site_url('wishlist'); ?>">Wishlist</a></li>
                                 <li><a href="<?= site_url('compare'); ?>">Compare</a></li>
                              <?php endif; ?>
                           </ul>
                        </div>
                     </div>
                  </div>
                  <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                     <div class="aq-footer-widget mb-90">
                        <h4 class="aq-footer-widget-title">Information</h4>
                        <div class="aq-footer-widget-menu">
                           <ul>
                              <li><a href="<?= site_url('order/track'); ?>">Track Order</a></li>
                              <li><a href="<?= site_url('returns-policy'); ?>">Shipping &amp; Returns</a></li>
                              <li><a href="<?= site_url('about'); ?>">About Us</a></li>
                              <li><a href="<?= site_url('contact'); ?>">Help &amp; Support</a></li>
                              <li><a href="<?= site_url('terms'); ?>">Terms of Service</a></li>
                           </ul>
                        </div>
                     </div>
                  </div>
                  <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                     <div class="aq-footer-widget mb-90">
                        <h4 class="aq-footer-widget-title">Account</h4>
                        <div class="aq-footer-widget-menu">
                           <ul>
                              <li><a href="<?= site_url('cart'); ?>">Shopping Cart</a></li>
                              <li><a href="<?= !empty($is_logged_in) ? site_url('account') : site_url('auth/login'); ?>">My Account</a></li>
                              <li><a href="<?= !empty($is_logged_in) ? site_url('account/orders') : site_url('order/track'); ?>">My Orders</a></li>
                              <li><a href="<?= site_url('wishlist'); ?>">Wishlist</a></li>
                              <li><a href="<?= site_url('compare'); ?>">Compare Products</a></li>
                           </ul>
                        </div>
                     </div>
                  </div>
                  <div class="col-xl-3 col-lg-6 col-md-7 col-sm-10">
                     <div class="aq-footer-widget mb-90">
                        <h4 class="aq-footer-widget-title">Subscribe.</h4>
                        <div class="aq-footer-widget-input-box mb-25">
                           <form action="<?= site_url('newsletter/subscribe'); ?>" method="POST">
                              <p class="mb-10">Get early access to tech drops &amp; flash deals</p>
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
            <div class="container">
               <div class="aq-copyright-border pt-15 pb-15">
                  <div class="row align-items-center">
                     <div class="col-xl-5 col-lg-5 col-md-6">
                        <div class="aq-copyright-text text-center text-md-start pb-20">
                           <p class="mb-0">All rights reserved — <?= date('Y'); ?> &copy; <a href="<?= site_url('home'); ?>"><?= html_escape($site_name); ?></a></p>
                        </div>
                     </div>
                     <div class="col-xl-7 col-lg-7 col-md-6">
                        <div class="aq-copyright-payment text-center text-md-end pb-20">
                           <a href="<?= site_url('checkout'); ?>"><img src="<?= $bazaro; ?>img/payment/payment.png" alt="Payment Gateways"></a>
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
   <script src="<?= $bazaro; ?>js/slider-active.js"></script>
   <script src="<?= $bazaro; ?>js/imagesloaded-pkgd.js"></script>
   <script src="<?= $bazaro; ?>js/ajax-form.js"></script>
   <script src="<?= $bazaro; ?>js/main.js"></script>

   <script>
   document.addEventListener('DOMContentLoaded', function() {
       var defaultEltImg = '<?= $bazaro; ?>img/electronics/arrival-product/product-1/product-1.jpg';
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
               a.setAttribute('href', baseHref + sep + 'preview_tpl=electronics' + hashPart);
           }
       });

       document.querySelectorAll('form[action]').forEach(function(f) {
           var act = f.getAttribute('action');
           if (act && act.indexOf(siteBase) === 0 && act.indexOf('preview_tpl=') === -1) {
               var sep = act.indexOf('?') === -1 ? '?' : '&';
               f.setAttribute('action', act + sep + 'preview_tpl=electronics');
           }
       });
<?php endif; ?>

       // Convert any residual inline Flipkart-blue (#2874f0) or fashion fallback images on shared views to Electronics theme
       document.querySelectorAll('[style*="#2874f0"], [style*="rgb(40, 116, 240)"], [style*="#f0f5ff"], [style*="#e1ecff"]').forEach(function(el) {
           var st = el.getAttribute('style') || '';
           st = st.replace(/#2874f0/gi, '#0B0D0E')
                  .replace(/rgb\(40,\s*116,\s*240\)/gi, '#0B0D0E')
                  .replace(/#f0f5ff/gi, '#f1f5f9')
                  .replace(/#e1ecff/gi, '#cbd5e1');
           el.setAttribute('style', st);
       });

       document.querySelectorAll('img').forEach(function(img) {
           var onerr = img.getAttribute('onerror');
           if (onerr && onerr.indexOf('women-1.jpg') !== -1) {
               img.setAttribute('onerror', "this.src='" + defaultEltImg + "'");
           }
           var src = img.getAttribute('src') || '';
           if (src.indexOf('womens/women-') !== -1) {
               img.setAttribute('src', defaultEltImg);
           }
       });
   });
   function showEltToast(msg, isError) {
       var t = document.getElementById('eltToast');
       var txt = document.getElementById('eltToastText');
       if (!t || !txt) return;
       txt.textContent = msg;
       t.style.background = isError ? '#dc2626' : '#111827';
       t.style.display = 'inline-flex';
       clearTimeout(window._eltToastTimer);
       window._eltToastTimer = setTimeout(function() {
           t.style.display = 'none';
       }, 3000);
   }

   function renderBazaroCartDrawer(data) {
       if (!data) return;
       var count = (data.cart_summary && typeof data.cart_summary.item_count !== 'undefined')
           ? data.cart_summary.item_count
           : (typeof data.cart_count !== 'undefined' ? data.cart_count : null);
       var subtotal = (data.cart_summary && typeof data.cart_summary.subtotal !== 'undefined')
           ? parseFloat(data.cart_summary.subtotal)
           : (typeof data.cart_total !== 'undefined' ? parseFloat(data.cart_total) : 0);

       if (count !== null) {
           document.querySelectorAll('.cart-count').forEach(function(el) {
               el.textContent = count;
           });
       }
       var subEl = document.getElementById('eltCartMiniSubtotal');
       if (subEl) {
           subEl.textContent = window.CURRENCY_SYMBOL + subtotal.toFixed(2);
       }

       var widget = document.getElementById('eltCartMiniBody');
       if (widget && Array.isArray(data.cart_items)) {
           if (data.cart_items.length === 0) {
               widget.innerHTML = '<div class="text-center py-5">' +
                   '<p class="mb-3 text-muted">Your Cart is empty</p>' +
                   '<a href="' + window.SITE_URL + '/shop" class="aq-btn-black radius-30">Go to Shop</a></div>';
           } else {
               var html = '';
               data.cart_items.forEach(function(item) {
                   var cleanImg = (item.image || item.main_image || '').replace(/^\/+/, '');
                   var img = cleanImg
                       ? (cleanImg.indexOf('http') === 0 ? cleanImg : (cleanImg.indexOf('electronics/') === 0 || cleanImg.indexOf('grocery/') === 0 ? window.ASSET_URL + 'assets/images/' + cleanImg : window.ASSET_URL + 'assets/images/products/' + cleanImg))
                       : '<?= $bazaro; ?>img/electronics/arrival-product/product-1/product-1.jpg';
                   var pUrl = window.SITE_URL + '/product/' + (item.slug || item.product_id);
                   var key = item.cart_key || item.product_id;
                   html += '<div class="aq-cartmini-product-item d-flex align-items-center justify-content-between">' +
                       '<div class="aq-cartmini-product-info d-flex align-items-center">' +
                       '<div class="aq-cartmini-product-img"><a href="' + pUrl + '"><img src="' + img + '" alt=""></a></div>' +
                       '<div class="aq-cartmini-product-content">' +
                       '<h4 class="aq-cartmini-product-title"><a href="' + pUrl + '">' + item.name + '</a></h4>' +
                       '<span class="aq-cartmini-product-price">' + item.quantity + ' &times; ' + window.CURRENCY_SYMBOL + parseFloat(item.price).toFixed(2) + '</span>' +
                       '</div></div>' +
                       '<div class="aq-cartmini-product-Action">' +
                       '<button type="button" onclick="removeCartItemAjax(\'' + key + '\')">' +
                       '<svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                       '<path d="M9 1L1 9M1 1L9 9" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>' +
                       '</button></div></div>';
               });
               widget.innerHTML = html;
           }
       }
   }

   function bazaroAddToCart(productId, quantity, openMiniCart) {
       quantity = quantity || 1;
       if (typeof openMiniCart === 'undefined') openMiniCart = true;
       var fd = new FormData();
       fd.append('product_id', productId);
       fd.append('quantity', quantity);

       fetch('<?= site_url("cart/add"); ?>', {
           method: 'POST',
           headers: { 'X-Requested-With': 'XMLHttpRequest' },
           body: fd
       })
       .then(function(r) { return r.json(); })
       .then(function(res) {
           if (res.success) {
               renderBazaroCartDrawer(res);
               showEltToast(res.message || 'Added to cart!');
               if (openMiniCart) {
                   var cm = document.querySelector('.aq-cartmini-active');
                   var ov = document.querySelector('.body-overlay');
                   if (cm) cm.classList.add('opened');
                   if (ov) ov.classList.add('opened');
               }
           } else {
               showEltToast(res.message || 'Could not add item', true);
           }
       })
       .catch(function() {
           showEltToast('Network error adding to cart', true);
       });
   }

   function bazaroBuyNow(productId, quantity) {
       quantity = quantity || 1;
       var fd = new FormData();
       fd.append('product_id', productId);
       fd.append('quantity', quantity);

       fetch('<?= site_url("cart/add"); ?>', {
           method: 'POST',
           headers: { 'X-Requested-With': 'XMLHttpRequest' },
           body: fd
       })
       .then(function(r) { return r.json(); })
       .then(function(res) {
           if (res.success) {
               window.location.href = '<?= site_url("checkout"); ?>';
           } else {
               showEltToast(res.message || 'Could not proceed to checkout', true);
           }
       });
   }

   function removeCartItemAjax(cartKey) {
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
               renderBazaroCartDrawer(res);
               showEltToast('Item removed from cart');
           }
       });
   }

   function bazaroToggleWishlist(productId, btnEl) {
       if (typeof event !== 'undefined' && event) {
           event.preventDefault();
           event.stopPropagation();
       }
       productId = parseInt(productId, 10);
       if (!productId || isNaN(productId)) return;
       if (!btnEl && typeof event !== 'undefined' && event) {
           btnEl = event.currentTarget || (event.target ? event.target.closest('button') : null);
       }

       // Product cannot be added to wishlist without login
       if (!window.IS_USER_LOGGED_IN) {
           document.cookie = "pending_wishlist=" + productId + "; path=/; max-age=3600; SameSite=Lax";
           showEltToast('Please login first to add items to your wishlist.', true);
           if (typeof window.openLoginModal === 'function') {
               setTimeout(function() {
                   window.openLoginModal(window.location.href, 'Please login first to add items to your wishlist.');
               }, 350);
           }
           return;
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
               showEltToast(res.message || 'Please login first to add items to your wishlist.', true);
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
                   'button[data-product-id="' + productId + '"]',
                   'button[onclick*="bazaroToggleWishlist(' + productId + ')"]',
                   'button[onclick*="bazaroToggleWishlist(' + productId + ',"]'
               ];
               document.querySelectorAll(selectors.join(', ')).forEach(function(btn) {
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
               if (typeof res.wishlist_count !== 'undefined') {
                   document.querySelectorAll('.wishlist-count').forEach(function(el) {
                       el.textContent = res.wishlist_count;
                   });
               }
               showEltToast(res.message || (isAdded ? 'Added to your wishlist!' : 'Removed from your wishlist.'));
           } else if (res.redirect) {
               window.location.href = res.redirect;
           }
       });
   }

   window.USER_WISHLIST_IDS = <?= json_encode(array_values(array_map('intval', $user_wishlist_ids ?? []))); ?>;
   function syncBazaroWishlistStates() {
       if (!window.USER_WISHLIST_IDS || !Array.isArray(window.USER_WISHLIST_IDS)) return;
       window.USER_WISHLIST_IDS.forEach(function(pId) {
           pId = parseInt(pId, 10);
           if (!pId) return;
           var selectors = [
               'button[data-product-id="' + pId + '"]',
               'button[onclick*="bazaroToggleWishlist(' + pId + ')"]',
               'button[onclick*="bazaroToggleWishlist(' + pId + ',"]'
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
       document.addEventListener('DOMContentLoaded', syncBazaroWishlistStates);
   } else {
       syncBazaroWishlistStates();
   }

   function bazaroQuickView(item) {
       if (!item) return;
       var imgEl = document.getElementById('eltQvImage');
       var titleEl = document.getElementById('eltQvTitle');
       var priceEl = document.getElementById('eltQvPrice');
       var catEl = document.getElementById('eltQvCat');
       var descEl = document.getElementById('eltQvDesc');
       var skuEl = document.getElementById('eltQvSku');
       var linkEl = document.getElementById('eltQvLink');
       var qtyEl = document.getElementById('eltQvQty');
       var addBtn = document.getElementById('eltQvAddBtn');

       if (imgEl) imgEl.src = item.image;
       if (titleEl) titleEl.textContent = item.title;
       if (priceEl) priceEl.textContent = item.price;
       if (catEl) catEl.textContent = item.category || 'Electronics';
       if (descEl) descEl.textContent = item.desc || '';
       if (skuEl) skuEl.textContent = item.sku || '';
       if (linkEl) linkEl.href = item.url || '#';
       if (qtyEl) qtyEl.value = 1;
       if (addBtn) {
           addBtn.onclick = function() {
               var q = qtyEl ? parseInt(qtyEl.value || 1) : 1;
               var modalEl = document.getElementById('producQuickViewModal');
               if (modalEl && typeof bootstrap !== 'undefined') {
                   var inst = bootstrap.Modal.getInstance(modalEl);
                   if (inst) inst.hide();
               }
               bazaroAddToCart(item.id, q, true);
           };
       }

       var modalEl = document.getElementById('producQuickViewModal');
       if (modalEl && typeof bootstrap !== 'undefined') {
           var m = new bootstrap.Modal(modalEl);
           m.show();
       }
   }
   </script>

</body>
</html>
