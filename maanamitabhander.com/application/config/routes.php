<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'home';
$route['shop'] = 'shop/index';
$route['shop/autocomplete'] = 'shop/autocomplete';
$route['shop/(:any)'] = 'shop/index/$1';
$route['products'] = 'shop/index';
$route['products/(:any)'] = 'shop/index/$1';
$route['product'] = 'product/detail';
$route['product/detail'] = 'product/detail';
$route['product/get_variant_ajax'] = 'product/get_variant_ajax';
$route['product/reviews/(:any)'] = 'product/reviews/$1';
$route['product/(:any)/reviews'] = 'product/reviews/$1';
$route['product/(:any)'] = 'product/detail/$1';
$route['cart'] = 'cart/index';
$route['checkout'] = 'checkout/index';
$route['checkout/payment'] = 'checkout/payment';
$route['order/track'] = 'order/track';
$route['login'] = 'auth/login';
$route['auth/send_otp'] = 'auth/send_otp';
$route['auth/verify_otp'] = 'auth/verify_otp';
$route['auth/complete_registration'] = 'auth/complete_registration';
$route['register'] = 'auth/register';
$route['logout'] = 'auth/logout';
$route['wishlist'] = 'account/wishlist';
$route['wishlist/toggle'] = 'account/wishlist_toggle';
$route['wishlist/toggle/(:num)'] = 'account/wishlist_toggle/$1';
$route['account'] = 'account/profile';
$route['account/profile'] = 'account/profile';
$route['account/orders'] = 'account/orders';
$route['account/order'] = 'account/order_detail';
$route['account/order_detail'] = 'account/order_detail';
$route['account/order_detail/(:any)'] = 'account/order_detail/$1';
$route['account/order/(:any)'] = 'account/order_detail/$1';
$route['account/address'] = 'account/address';
$route['account/get_address/(:num)'] = 'account/get_address_ajax/$1';
$route['account/delete_address/(:num)'] = 'account/delete_address/$1';
$route['account/notifications'] = 'account/notifications';
$route['compare'] = 'compare/index';
$route['compare/add/(:num)'] = 'compare/add/$1';
$route['compare/remove/(:num)'] = 'compare/remove/$1';
$route['compare/clear'] = 'compare/clear';
$route['account/cancel_order/(:any)'] = 'account/cancel_order/$1';
$route['account/invoice/(:any)'] = 'account/invoice/$1';
$route['order/invoice/(:any)'] = 'account/invoice/$1';
$route['account/returns'] = 'account/returns';
$route['account/request_return/(:any)'] = 'account/request_return/$1';
$route['account/rate_review/(:any)/(:num)'] = 'account/rate_review/$1/$2';
$route['account/rate_review/(:any)'] = 'account/rate_review/$1';
$route['account/quick_rate'] = 'account/quick_rate';
$route['about'] = 'page/about';
$route['contact'] = 'page/contact';
$route['faq'] = 'page/faq';
$route['terms'] = 'page/terms';
$route['privacy'] = 'page/privacy';
$route['returns-policy'] = 'page/returns_policy';
$route['newsletter/subscribe'] = 'page/subscribe_newsletter';
$route['payment/webhook'] = 'payment/webhook/stripe';
$route['payment/webhook/(:any)'] = 'payment/webhook/$1';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
