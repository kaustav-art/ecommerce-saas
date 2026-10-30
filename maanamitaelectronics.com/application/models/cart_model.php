<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class cart_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('product_model');
        $this->load->model('setting_model');
    }

    private function tid()
    {
        $CI =& get_instance();
        return isset($CI->tenant_id) ? (int) $CI->tenant_id : 1;
    }

    private function active_user_id()
    {
        $CI =& get_instance();
        if (!empty($CI->is_preview_mode) || empty($CI->is_user_project)) {
            return 0;
        }
        $tid = $this->tid();
        $uid = (int) $this->session->userdata('user_id');
        $user_tid = (int) $this->session->userdata('user_tenant_id');
        if ($uid <= 0 || !$this->session->userdata('user_logged_in') || ($user_tid > 0 && $user_tid !== $tid)) {
            return 0;
        }
        $u_row = $this->db->select('id')->where('id', $uid)->where('tenant_id', $tid)->get('users')->row_array();
        return !empty($u_row['id']) ? (int) $u_row['id'] : 0;
    }

    private function is_valid_tenant_product($product_id)
    {
        $pid = (int) $product_id;
        if ($pid <= 0) {
            return false;
        }
        $p_row = $this->db->select('id')->where('id', $pid)->where('tenant_id', $this->tid())->get('products')->row_array();
        return !empty($p_row['id']);
    }

    private function get_session_cart()
    {
        $tid = $this->tid();
        $key = 'cart_t_' . $tid;
        $cart = $this->session->userdata($key);
        return is_array($cart) ? $cart : [];
    }

    private function set_session_cart($cart)
    {
        $tid = $this->tid();
        $this->session->set_userdata('cart_t_' . $tid, $cart);
    }

    public function get_items()
    {
        $tid = $this->tid();
        $user_id = $this->active_user_id();
        $cart = $this->get_session_cart();

        if ($user_id) {
            $cart = $this->sync_db_with_session($user_id, $cart);
        }

        if (empty($cart)) {
            return [];
        }

        $tax_mult = $this->product_model->get_tax_multiplier();
        $cart_changed = false;

        foreach ($cart as $k => &$item) {
            // Ensure product belongs to current tenant
            if (!empty($item['id'])) {
                $p_check = $this->db->select('id, tenant_id')->where('id', (int)$item['id'])->get('products')->row_array();
                if (!$p_check || (int)$p_check['tenant_id'] !== $tid) {
                    unset($cart[$k]);
                    $cart_changed = true;
                    continue;
                }
            }
            // Ensure base prices exist
            if (!isset($item['base_price']) || !is_numeric($item['base_price'])) {
                if (!empty($item['variant_id'])) {
                    $v_row = $this->db->where('tenant_id', $tid)->where('id', (int) $item['variant_id'])->get('product_variants')->row_array();
                    if ($v_row) {
                        $item['base_price'] = (!empty($v_row['sale_price']) && (float)$v_row['sale_price'] > 0) ? (float) $v_row['sale_price'] : (float) $v_row['price'];
                        $item['base_regular_price'] = (float) $v_row['price'];
                    }
                }
                if (!isset($item['base_price']) && !empty($item['id'])) {
                    $p_row = $this->db->where('tenant_id', $tid)->where('id', (int) $item['id'])->get('products')->row_array();
                    if ($p_row) {
                        $item['base_price'] = (!empty($p_row['sale_price']) && (float)$p_row['sale_price'] > 0) ? (float) $p_row['sale_price'] : (float) $p_row['price'];
                        $item['base_regular_price'] = (float) $p_row['price'];
                    }
                }
                if (!isset($item['base_price'])) {
                    $item['base_price'] = (float) $item['price'];
                    $item['base_regular_price'] = !empty($item['regular_price']) ? (float) $item['regular_price'] : round($item['price'] * 1.30, 2);
                }
                $cart_changed = true;
            }

            if (!isset($item['base_regular_price']) || !is_numeric($item['base_regular_price']) || $item['base_regular_price'] <= 0) {
                $item['base_regular_price'] = round($item['base_price'] * 1.30, 2);
                $cart_changed = true;
            }

            // Dynamically compute current price and regular price based on active flash sale & tax mode
            $fs = $this->product_model->get_active_flash_sale_info();
            $in_fs = ($fs && !empty($fs['product_ids']) && in_array((int)$item['id'], $fs['product_ids'], true));

            $cur_selling = (isset($item['original_selling_price']) && is_numeric($item['original_selling_price']) && (float)$item['original_selling_price'] > 0)
                ? (float)$item['original_selling_price']
                : ((isset($item['base_price']) && is_numeric($item['base_price'])) ? (float)$item['base_price'] : (float)$item['price']);

            $cur_reg = (isset($item['base_regular_price']) && is_numeric($item['base_regular_price']) && (float)$item['base_regular_price'] > 0)
                ? (float)$item['base_regular_price']
                : (!empty($item['regular_price']) ? (float)$item['regular_price'] : round($cur_selling * 1.30, 2));

            if ($in_fs && !empty($fs['discount_percent'])) {
                $disc_pct = (float)$fs['discount_percent'];
                $item['is_flash_sale'] = 1;
                $new_price = round($cur_selling * (1 - ($disc_pct / 100)), 2);
                $new_reg   = $cur_reg;
            } else {
                $item['is_flash_sale'] = 0;
                if ($tax_mult > 1.0) {
                    $new_price = round($cur_selling * $tax_mult, 2);
                    $new_reg   = round($cur_reg * $tax_mult, 2);
                } else {
                    $new_price = (float) $cur_selling;
                    $new_reg   = (float) $cur_reg;
                }
            }

            if ($new_reg <= $new_price) {
                $new_reg = round($new_price * 1.30, 2);
            }

            if (!isset($item['price']) || abs((float)$item['price'] - $new_price) > 0.001) {
                $item['price'] = $new_price;
                $cart_changed = true;
            }
            if (!isset($item['regular_price']) || abs((float)$item['regular_price'] - $new_reg) > 0.001) {
                $item['regular_price'] = $new_reg;
                $cart_changed = true;
            }

            $new_total = round($item['price'] * (int) $item['quantity'], 2);
            if (!isset($item['total']) || abs((float)$item['total'] - $new_total) > 0.001) {
                $item['total'] = $new_total;
                $cart_changed = true;
            }

            if (!empty($item['regular_price']) && $item['regular_price'] > $item['price']) {
                $item['discount_percent'] = round((($item['regular_price'] - $item['price']) / $item['regular_price']) * 100);
            } else {
                $item['discount_percent'] = 0;
            }

            // Extract variant attributes (size, color) if not already set
            if (empty($item['size']) || empty($item['color'])) {
                if (!empty($item['variant_id'])) {
                    $attr_rows = $this->db->select('a.slug as attr_slug, a.name as attr_name, av.value as attr_val')
                                          ->from('product_variant_values pvv')
                                          ->join('attributes a', 'a.id = pvv.attribute_id')
                                          ->join('attribute_values av', 'av.id = pvv.attribute_value_id')
                                          ->where('pvv.variant_id', (int) $item['variant_id'])
                                          ->get()
                                          ->result_array();
                    foreach ($attr_rows as $ar) {
                        $slug = strtolower($ar['attr_slug'] ?: $ar['attr_name']);
                        if ($slug === 'size' && empty($item['size'])) {
                            $item['size'] = $ar['attr_val'];
                        } elseif ($slug === 'color' && empty($item['color'])) {
                            $item['color'] = $ar['attr_val'];
                        }
                    }
                }

                // Fallback parsing from variant_title if size or color still empty
                if ((empty($item['size']) || empty($item['color'])) && !empty($item['variant_title'])) {
                    $opt_str = $item['variant_title'];
                    if (strpos($opt_str, '-') !== false) {
                        $dash_parts = explode('-', $opt_str);
                        $opt_str = trim(end($dash_parts));
                    }
                    if (strpos($opt_str, '/') !== false) {
                        $parts = array_map('trim', explode('/', $opt_str));
                        $sizes_known = ['xs', 's', 'm', 'l', 'xl', 'xxl', 'xxxl', '28', '30', '32', '34', '36', '38', '40', '42'];
                        foreach ($parts as $p) {
                            if (in_array(strtolower($p), $sizes_known)) {
                                if (empty($item['size'])) $item['size'] = $p;
                            } else {
                                if (empty($item['color'])) $item['color'] = $p;
                            }
                        }
                    } else {
                        if (empty($item['size'])) $item['size'] = $opt_str;
                    }
                }
            }

            // Ensure brand_name, max_purchase_quantity, is_cod_allowed, rating, reviews_count, short_description
            if (!empty($item['id'])) {
                $p_info = $this->db->select('b.name as brand_name, p.max_purchase_quantity, p.stock_quantity, p.is_cod_allowed, p.rating, p.reviews_count, p.short_description')
                                   ->from('products p')
                                   ->join('brands b', 'b.id = p.brand_id', 'left')
                                   ->where('p.tenant_id', $tid)
                                   ->where('p.id', (int) $item['id'])
                                   ->get()
                                   ->row_array();
                if (empty($item['brand_name'])) {
                    $item['brand_name'] = !empty($p_info['brand_name']) ? $p_info['brand_name'] : 'VTEXX';
                }
                $max_p = !empty($p_info['max_purchase_quantity']) ? (int) $p_info['max_purchase_quantity'] : 5;
                $item['stock_max'] = isset($item['stock_max']) ? min((int)$item['stock_max'], $max_p) : $max_p;
                $item['is_cod_allowed'] = isset($p_info['is_cod_allowed']) ? (int) $p_info['is_cod_allowed'] : 1;
                $item['short_description'] = !empty($p_info['short_description']) ? $p_info['short_description'] : '';

                // Dynamic rating & reviews_count from product table and approved reviews
                $rev_stats = $this->db->select('COUNT(*) as total_count, AVG(rating) as avg_rating')
                                      ->where('tenant_id', $tid)
                                      ->where('product_id', (int)$item['id'])
                                      ->where('status', 'approved')
                                      ->get('reviews')
                                      ->row_array();
                if (!empty($rev_stats) && (int)$rev_stats['total_count'] > 0) {
                    $item['rating'] = round((float)$rev_stats['avg_rating'], 1);
                    $item['reviews_count'] = (int)$rev_stats['total_count'];
                } else {
                    $item['rating'] = (isset($p_info['rating']) && (float)$p_info['rating'] > 0) ? (float)$p_info['rating'] : 5.0;
                    $item['reviews_count'] = isset($p_info['reviews_count']) ? (int)$p_info['reviews_count'] : 0;
                }
            }
        }

        if ($cart_changed) {
            $this->set_session_cart($cart);
        }

        return $cart;
    }

    public function add_item($product_id, $quantity = 1, $variant_id = NULL)
    {
        $tid = $this->tid();
        $product = $this->product_model->get_by_id($product_id);
        if (!$product) {
            return ['success' => false, 'message' => 'Product not found.'];
        }

        $variant = NULL;
        if (!empty($variant_id)) {
            $variant = $this->product_model->get_variant_by_id($variant_id);
            if (!$variant || $variant['product_id'] != $product_id) {
                return ['success' => false, 'message' => 'Selected product variant was not found.'];
            }
        } elseif (!empty($product['variants']) && is_array($product['variants'])) {
            // Auto-select primary/default variant (prefer 1 kg or first variant)
            $default_var = $product['variants'][0];
            foreach ($product['variants'] as $v_cand) {
                $v_title = strtolower($v_cand['title'] ?? '');
                if (strpos($v_title, '1 kg') !== false || strpos($v_title, '1kg') !== false) {
                    $default_var = $v_cand;
                    break;
                }
            }
            $variant = $default_var;
            $variant_id = (int)$default_var['id'];
        }

        // Cart item unique key
        $cart_key = $product_id . ($variant ? '_' . $variant['id'] : '');
        $cart = $this->get_items();

        $available_stock = $variant ? (int) $variant['stock_quantity'] : (int) $product['stock_quantity'];
        $max_limit = !empty($product['max_purchase_quantity']) ? (int) $product['max_purchase_quantity'] : 999;
        if ($max_limit > 0) {
            $available_stock = min($available_stock, $max_limit);
        }
        $existing_qty = isset($cart[$cart_key]) ? (int) $cart[$cart_key]['quantity'] : 0;
        $target_qty = $existing_qty + $quantity;

        if ($target_qty > $available_stock) {
            if ($available_stock <= 0) {
                return ['success' => false, 'message' => 'This item is currently out of stock.'];
            }
            if ($max_limit > 0 && $target_qty > $max_limit) {
                return ['success' => false, 'message' => "You can only purchase a maximum of {$max_limit} units of this product per order."];
            }
            return ['success' => false, 'message' => "Only {$available_stock} units available in stock."];
        }

        // Price calculation
        $is_fs = (!empty($variant['is_flash_sale']) || !empty($product['is_flash_sale'])) ? 1 : 0;
        if ($variant) {
            $base_reg = (isset($variant['base_price']) && is_numeric($variant['base_price']) && (float)$variant['base_price'] > 0)
                ? (float)$variant['base_price']
                : (!empty($variant['price']) ? (float)$variant['price'] : 0);
            $orig_selling = (isset($variant['original_selling_price']) && is_numeric($variant['original_selling_price']) && (float)$variant['original_selling_price'] > 0)
                ? (float)$variant['original_selling_price']
                : ((!empty($variant['sale_price']) && (float)$variant['sale_price'] > 0) ? (float)$variant['sale_price'] : $base_reg);

            if ($is_fs) {
                $price = (float)$variant['sale_price'];
                $regular_price = $base_reg;
                $base_p = $orig_selling;
            } else {
                $price = $orig_selling;
                $regular_price = $base_reg;
                $base_p = $orig_selling;
            }
            $image = !empty($variant['image']) ? $variant['image'] : $product['main_image'];
            $sku   = !empty($variant['sku']) ? $variant['sku'] : $product['sku'];
        } else {
            $base_reg = (isset($product['base_price']) && is_numeric($product['base_price']) && (float)$product['base_price'] > 0)
                ? (float)$product['base_price']
                : (!empty($product['price']) ? (float)$product['price'] : 0);
            $orig_selling = (isset($product['original_selling_price']) && is_numeric($product['original_selling_price']) && (float)$product['original_selling_price'] > 0)
                ? (float)$product['original_selling_price']
                : ((!empty($product['sale_price']) && (float)$product['sale_price'] > 0) ? (float)$product['sale_price'] : $base_reg);

            if ($is_fs) {
                $price = (float)$product['sale_price'];
                $regular_price = $base_reg;
                $base_p = $orig_selling;
            } else {
                $price = $orig_selling;
                $regular_price = $base_reg;
                $base_p = $orig_selling;
            }
            $image = $product['main_image'];
            $sku   = $product['sku'];
        }

        if ($regular_price <= $price && $base_reg <= 0) {
            $regular_price = round($price * 1.30, 2);
        }
        if ($base_reg <= $base_p && $base_reg <= 0) {
            $base_reg = round($base_p * 1.30, 2);
        }
        if ($regular_price <= $price) {
            $regular_price = $price;
        }
        $disc_pct = ($regular_price > $price) ? round((($regular_price - $price) / $regular_price) * 100) : 0;

        $item_size = '';
        $item_color = '';
        if ($variant && !empty($variant['values'])) {
            foreach ($variant['values'] as $vval) {
                $aslug = strtolower($vval['attribute_slug'] ?? ($vval['attribute_name'] ?? ''));
                if ($aslug === 'size' && empty($item_size)) {
                    $item_size = $vval['attribute_value'];
                } elseif ($aslug === 'color' && empty($item_color)) {
                    $item_color = $vval['attribute_value'];
                }
            }
        }

        $cart[$cart_key] = [
            'cart_key'           => $cart_key,
            'id'                 => (int) $product['id'],
            'variant_id'         => $variant ? (int) $variant['id'] : NULL,
            'variant_title'      => $variant ? $variant['title'] : NULL,
            'size'               => $item_size,
            'color'              => $item_color,
            'brand_name'         => !empty($product['brand_name']) ? $product['brand_name'] : 'VTEXX',
            'title'              => $product['title'],
            'slug'               => $product['slug'],
            'sku'                => $sku,
            'image'              => $image,
            'base_price'         => (float) $base_p,
            'base_regular_price' => (float) $base_reg,
            'price'              => (float) $price,
            'regular_price'      => (float) $regular_price,
            'discount_percent'   => $disc_pct,
            'is_flash_sale'      => $is_fs,
            'original_selling_price' => $orig_selling ?? $base_p,
            'quantity'           => $target_qty,
            'total'              => (float) ($price * $target_qty),
            'stock_max'          => $available_stock,
            'is_cod_allowed'     => isset($product['is_cod_allowed']) ? (int) $product['is_cod_allowed'] : 1,
            'short_description'  => !empty($product['short_description']) ? $product['short_description'] : ''
        ];

        $this->set_session_cart($cart);

        // Synchronize with database for logged-in user
        $user_id = $this->active_user_id();
        if ($user_id && $this->is_valid_tenant_product($product_id)) {
            $vid = $variant ? (int)$variant['id'] : (!empty($variant_id) ? (int)$variant_id : 0);
            $existing = $this->db->where('tenant_id', $tid)
                                 ->where('user_id', (int)$user_id)
                                 ->where('product_id', (int)$product_id)
                                 ->where('variant_id', $vid)
                                 ->get('cart_items')
                                 ->row_array();
            if ($existing) {
                $this->db->where('id', $existing['id'])->update('cart_items', [
                    'quantity'   => $target_qty,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                $this->db->query(
                    "INSERT INTO cart_items (tenant_id, user_id, product_id, variant_id, quantity, created_at, updated_at) " .
                    "VALUES (?, ?, ?, ?, ?, NOW(), NOW()) " .
                    "ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), updated_at = NOW()",
                    [(int)$tid, (int)$user_id, (int)$product_id, (int)$vid, (int)$target_qty]
                );
            }
        }

        $summary = $this->get_cart_summary();
        return [
            'success'      => true,
            'message'      => 'Item added to your shopping cart.',
            'cart_count'   => $summary['item_count'],
            'cart_summary' => $summary,
            'item'         => $cart[$cart_key]
        ];
    }

    public function update_item($cart_key, $quantity)
    {
        $tid = $this->tid();
        $cart = $this->get_items();
        $quantity = (int) $quantity;
        $limit_msg = '';

        if ($quantity <= 0) {
            unset($cart[$cart_key]);
        } elseif (isset($cart[$cart_key])) {
            $item = $cart[$cart_key];
            $stock_max = (!empty($item['stock_max']) && (int) $item['stock_max'] > 0) ? (int) $item['stock_max'] : 0;
            if ($stock_max <= 0) {
                // Fetch fresh stock and limit from database if missing from old session
                $prod = $this->product_model->get_by_id($item['id']);
                if ($prod) {
                    $stock_max = (int) $prod['stock_quantity'];
                    if (!empty($prod['max_purchase_quantity']) && (int) $prod['max_purchase_quantity'] > 0) {
                        $stock_max = min($stock_max, (int) $prod['max_purchase_quantity']);
                    }
                }
                if ($stock_max <= 0) $stock_max = 999;
                $cart[$cart_key]['stock_max'] = $stock_max;
            }

            if ($quantity > $stock_max) {
                $limit_msg = "Maximum purchase limit of {$stock_max} units reached for this item.";
                $quantity = $stock_max;
            }
            $cart[$cart_key]['quantity'] = $quantity;
            $cart[$cart_key]['total']    = round($cart[$cart_key]['price'] * $quantity, 2);
        }

        $this->set_session_cart($cart);

        // Synchronize with database for logged-in user
        $user_id = $this->active_user_id();
        if ($user_id) {
            $parts = explode('_', (string)$cart_key);
            $pid = (int) $parts[0];
            $vid = isset($parts[1]) ? (int)$parts[1] : 0;

            if ($quantity <= 0) {
                $this->db->where('tenant_id', $tid)
                         ->where('user_id', (int)$user_id)
                         ->where('product_id', $pid)
                         ->where('variant_id', $vid)
                         ->delete('cart_items');
            } else {
                $this->db->where('tenant_id', $tid)
                         ->where('user_id', (int)$user_id)
                         ->where('product_id', $pid)
                         ->where('variant_id', $vid)
                         ->update('cart_items', [
                             'quantity'   => $quantity,
                             'updated_at' => date('Y-m-d H:i:s')
                         ]);
            }
        }

        $summary = $this->get_cart_summary();
        return [
            'success'      => true,
            'message'      => $limit_msg,
            'cart_count'   => $summary['item_count'],
            'cart_summary' => $summary
        ];
    }

    public function remove_item($cart_key)
    {
        $tid = $this->tid();
        $cart = $this->get_items();
        if (isset($cart[$cart_key])) {
            $item = $cart[$cart_key];
            unset($cart[$cart_key]);
            $this->set_session_cart($cart);

            $user_id = $this->active_user_id();
            if ($user_id) {
                $pid = (int) $item['id'];
                $vid = !empty($item['variant_id']) ? (int)$item['variant_id'] : 0;
                $this->db->where('tenant_id', $tid)
                         ->where('user_id', (int) $user_id)
                         ->where('product_id', $pid)
                         ->where('variant_id', $vid)
                         ->delete('cart_items');
            }
        }
        $summary = $this->get_cart_summary();
        return ['success' => true, 'cart_count' => $summary['item_count'], 'cart_summary' => $summary];
    }

    public function clear_cart()
    {
        $tid = $this->tid();
        $user_id = $this->active_user_id();
        if ($user_id) {
            $this->db->where('tenant_id', $tid)->where('user_id', (int) $user_id)->delete('cart_items');
        }
        $this->session->unset_userdata('cart_t_' . $tid);
        $this->session->unset_userdata('cart');
        $this->session->unset_userdata('applied_coupon');
    }

    public function apply_coupon($code)
    {
        $tid = $this->tid();
        $code = strtoupper(trim($code));
        $coupon = $this->db->where('tenant_id', $tid)
                           ->where('code', $code)
                           ->where('status', 'active')
                           ->get('coupons')
                           ->row_array();

        if (!$coupon) {
            return ['success' => false, 'message' => 'Invalid discount coupon code.'];
        }

        if (!empty($coupon['expires_at']) && strtotime($coupon['expires_at']) < time()) {
            return ['success' => false, 'message' => 'This coupon has expired.'];
        }

        $summary = $this->get_cart_summary();
        if ($summary['subtotal'] < $coupon['min_spend']) {
            return ['success' => false, 'message' => 'Minimum spend of $' . number_format($coupon['min_spend'], 2) . ' required for this coupon.'];
        }

        $discount_amount = 0.00;
        if ($coupon['discount_type'] === 'percent') {
            $discount_amount = ($summary['subtotal'] * $coupon['discount_value']) / 100;
        } else {
            $discount_amount = (float) $coupon['discount_value'];
        }

        $this->session->set_userdata('applied_coupon', [
            'id'       => $coupon['id'],
            'code'     => $coupon['code'],
            'discount' => $discount_amount
        ]);

        return ['success' => true, 'message' => 'Coupon ' . $coupon['code'] . ' applied successfully!'];
    }

    public function remove_coupon()
    {
        $this->session->unset_userdata('applied_coupon');
        return ['success' => true];
    }

    public function get_cart_summary($shipping_method = 'standard')
    {
        $items = $this->get_items();
        $subtotal = 0.00;
        $item_count = 0;
        $mrp_total = 0.00;

        foreach ($items as $item) {
            $subtotal += (float) $item['total'];
            $item_count += (int) $item['quantity'];
            $reg = !empty($item['regular_price']) ? (float)$item['regular_price'] : round(((float)$item['price'] * 1.30), 2);
            if ($reg <= (float)$item['price']) {
                $reg = round((float)$item['price'] * 1.30, 2);
            }
            $mrp_total += $reg * (int)$item['quantity'];
        }

        // Applied coupon
        $coupon = $this->session->userdata('applied_coupon');
        $discount = 0.00;
        if ($coupon && !empty($coupon['discount'])) {
            $discount = min($subtotal, (float) $coupon['discount']);
        }

        // Settings - Dynamic Shipping Charges
        $shipping_charges_raw = $this->setting_model->get('shipping_charges');
        $configured_charges = [];
        if (!empty($shipping_charges_raw)) {
            $decoded = json_decode($shipping_charges_raw, true);
            if (is_array($decoded)) {
                $configured_charges = $decoded;
            }
        }

        $flat_shipping = (float) $this->setting_model->get('shipping_flat_rate', 50.00);
        if (empty($configured_charges)) {
            $configured_charges = [
                ['name' => 'Courier Charges', 'value' => $flat_shipping]
            ];
        }

        $shipping_charges = [];
        $shipping = 0.00;
        if ($subtotal > 0) {
            foreach ($configured_charges as $c_item) {
                $c_name = trim($c_item['name'] ?? '');
                $c_val  = (float) ($c_item['value'] ?? 0);
                if ($c_name !== '') {
                    $shipping_charges[] = [
                        'name'  => $c_name,
                        'value' => $c_val
                    ];
                    $shipping += $c_val;
                }
            }
        }

        $shipping_dispatch_note = $this->setting_model->get('shipping_dispatch_note', 'Dispatched within 24-48 hours with courier tracking.');

        // Tax settings
        $tax_enabled      = (int) $this->setting_model->get('tax_enabled', 0);
        $tax_rate_percent = (float) ($this->setting_model->get('tax_rate') ?: $this->setting_model->get('tax_rate_percent', 0.00));
        $tax_inclusive    = (int) $this->setting_model->get('tax_inclusive', 0);

        // Tax calculation
        $taxable = max(0, $subtotal - $discount);
        $tax = 0.00;

        if ($tax_enabled && $tax_rate_percent > 0 && $taxable > 0) {
            if ($tax_inclusive == 1) {
                // Prices are Inclusive of Tax: tax is already included in product price
                $tax = round($taxable - ($taxable / (1 + ($tax_rate_percent / 100))), 2);
                $total = $taxable + $shipping;
            } else {
                // Prices are Exclusive of Tax: tax is calculated & added at checkout
                $tax = round(($taxable * $tax_rate_percent) / 100, 2);
                $total = $taxable + $shipping + $tax;
            }
        } else {
            $total = $taxable + $shipping;
        }

        $mrp_discount = max(0, $mrp_total - $subtotal);
        $total_savings = $mrp_discount + $discount;

        return [
            'item_count'             => $item_count,
            'subtotal'               => $subtotal,
            'mrp_total'              => $mrp_total,
            'mrp_discount'           => $mrp_discount,
            'total_savings'          => $total_savings,
            'discount'               => $discount,
            'coupon'                 => $coupon,
            'shipping'               => $shipping,
            'shipping_charges'       => $shipping_charges,
            'shipping_dispatch_note' => $shipping_dispatch_note,
            'shipping_method'        => $shipping_method,
            'shipping_flat'          => $flat_shipping,
            'tax'                    => $tax,
            'tax_enabled'            => $tax_enabled,
            'tax_inclusive'          => $tax_inclusive,
            'tax_rate_percent'       => $tax_rate_percent,
            'total'                  => $total,
            'currency_symbol'        => $this->setting_model->get('currency_symbol', '$')
        ];
    }

    // Save for Later
    public function save_for_later($cart_key)
    {
        $tid = $this->tid();
        $cart = $this->get_items();
        if (!isset($cart[$cart_key])) {
            return false;
        }

        $item = $cart[$cart_key];
        $user_id = $this->active_user_id() ?: NULL;
        $session_id = $this->session->session_id ?: session_id();

        // Insert into cart_saved_items only if product belongs to current tenant
        if ($this->is_valid_tenant_product($item['id'] ?? 0)) {
            $this->db->insert('cart_saved_items', [
                'tenant_id'  => $tid,
                'user_id'    => $user_id,
                'session_id' => $session_id,
                'product_id' => $item['id'],
                'variant_id' => $item['variant_id'] ?? NULL,
                'quantity'   => $item['quantity']
            ]);
        }

        // Remove from current cart
        unset($cart[$cart_key]);
        $this->set_session_cart($cart);

        // Also remove from cart_items table in database if user is logged in
        if ($user_id) {
            $pid = (int) $item['id'];
            $vid = !empty($item['variant_id']) ? (int)$item['variant_id'] : 0;
            $this->db->where('tenant_id', $tid)
                     ->where('user_id', (int) $user_id)
                     ->where('product_id', $pid)
                     ->where('variant_id', $vid)
                     ->delete('cart_items');
        }

        return true;
    }

    public function get_saved_items()
    {
        $tid = $this->tid();
        $user_id = $this->active_user_id() ?: NULL;
        $session_id = $this->session->session_id ?: session_id();

        $this->db->select('csi.*, p.title as product_title, p.slug as product_slug, p.main_image as product_image, p.price as product_price, p.sale_price as product_sale_price, pv.title as variant_title, pv.price as variant_price, pv.sale_price as variant_sale_price, pv.image as variant_image')
                 ->from('cart_saved_items csi')
                 ->join('products p', 'p.id = csi.product_id AND p.tenant_id = ' . $tid)
                 ->join('product_variants pv', 'pv.id = csi.variant_id', 'left')
                 ->where('csi.tenant_id', $tid);

        if ($user_id) {
            $this->db->group_start()
                     ->where('csi.user_id', $user_id)
                     ->or_where('csi.session_id', $session_id)
                     ->group_end();
        } else {
            $this->db->where('csi.session_id', $session_id);
        }

        $this->db->order_by('csi.id', 'DESC');
        $rows = $this->db->get()->result_array();
        $tax_mult = $this->product_model->get_tax_multiplier();
        if ($tax_mult > 1.0) {
            foreach ($rows as &$r) {
                if (isset($r['product_price'])) $r['product_price'] = round($r['product_price'] * $tax_mult, 2);
                if (!empty($r['product_sale_price'])) $r['product_sale_price'] = round($r['product_sale_price'] * $tax_mult, 2);
                if (isset($r['variant_price'])) $r['variant_price'] = round($r['variant_price'] * $tax_mult, 2);
                if (!empty($r['variant_sale_price'])) $r['variant_sale_price'] = round($r['variant_sale_price'] * $tax_mult, 2);
            }
        }
        return $rows;
    }

    public function move_to_cart($saved_id)
    {
        $tid = $this->tid();
        $saved = $this->db->where('tenant_id', $tid)->where('id', (int) $saved_id)->get('cart_saved_items')->row_array();
        if (!$saved) {
            return false;
        }

        $res = $this->add_item($saved['product_id'], $saved['quantity'], $saved['variant_id']);
        if ($res['success']) {
            $this->db->where('tenant_id', $tid)->where('id', (int) $saved_id)->delete('cart_saved_items');
            return true;
        }
        return false;
    }

    public function remove_saved_item($saved_id)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $saved_id)->delete('cart_saved_items');
    }

    /**
     * Check if Cash on Delivery is allowed for all items in the cart.
     * If even one item has is_cod_allowed == 0, COD is disabled for the whole order.
     */
    public function check_cod_eligibility()
    {
        $items = $this->get_items();
        if (empty($items)) {
            return [
                'eligible'      => false,
                'non_cod_items' => []
            ];
        }

        $non_cod_items = [];
        foreach ($items as $item) {
            if (isset($item['is_cod_allowed']) && (int) $item['is_cod_allowed'] === 0) {
                $non_cod_items[] = $item['title'];
            }
        }

        return [
            'eligible'      => empty($non_cod_items),
            'non_cod_items' => array_values(array_unique($non_cod_items))
        ];
    }

    /**
     * Synchronize database cart_items table with session cart for a logged-in user.
     * Ensures cross-device synchronization (Mobile <-> PC).
     */
    public function sync_db_with_session($user_id, $cart)
    {
        $user_id = $this->active_user_id();
        if (empty($user_id)) {
            return $cart;
        }

        $tid = $this->tid();
        $db_rows = $this->db->where('tenant_id', $tid)->where('user_id', (int) $user_id)->get('cart_items')->result_array();
        $db_map = [];
        foreach ($db_rows as $row) {
            $vid = !empty($row['variant_id']) ? (int)$row['variant_id'] : null;
            $ckey = (string) $row['product_id'] . ($vid ? '_' . $vid : '');
            $db_map[$ckey] = [
                'product_id' => (int) $row['product_id'],
                'variant_id' => $vid,
                'quantity'   => (int) $row['quantity']
            ];
        }

        $is_synced = true;
        if (count($db_map) !== count($cart)) {
            $is_synced = false;
        } else {
            foreach ($db_map as $ckey => $info) {
                if (!isset($cart[$ckey]) || (int)$cart[$ckey]['quantity'] !== (int)$info['quantity']) {
                    $is_synced = false;
                    break;
                }
            }
        }

        if (!$is_synced) {
            $new_cart = [];
            foreach ($db_map as $ckey => $info) {
                if (!$this->is_valid_tenant_product($info['product_id'])) {
                    $this->db->where('tenant_id', $tid)
                             ->where('user_id', (int)$user_id)
                             ->where('product_id', (int)$info['product_id'])
                             ->where('variant_id', !empty($info['variant_id']) ? (int)$info['variant_id'] : 0)
                             ->delete('cart_items');
                    continue;
                }
                if (isset($cart[$ckey])) {
                    $item = $cart[$ckey];
                    $item['quantity'] = $info['quantity'];
                    $item['total'] = round((float)$item['price'] * $info['quantity'], 2);
                    $new_cart[$ckey] = $item;
                } else {
                    $built = $this->build_item_data($info['product_id'], $info['quantity'], $info['variant_id']);
                    if ($built) {
                        $new_cart[$ckey] = $built;
                    } else {
                        $this->db->where('tenant_id', $tid)
                                 ->where('user_id', (int)$user_id)
                                 ->where('product_id', (int)$info['product_id'])
                                 ->where('variant_id', !empty($info['variant_id']) ? (int)$info['variant_id'] : 0)
                                 ->delete('cart_items');
                    }
                }
            }
            // Also preserve and safely merge any items that were in guest session cart into cart_items DB
            if (!empty($cart) && is_array($cart)) {
                foreach ($cart as $ckey => $citem) {
                    $pid = (int) ($citem['id'] ?? 0);
                    if ($pid <= 0 || !$this->is_valid_tenant_product($pid)) {
                        continue;
                    }
                    $vid = !empty($citem['variant_id']) ? (int) $citem['variant_id'] : 0;
                    $actual_key = (string) $pid . ($vid ? '_' . $vid : '');

                    if (!isset($new_cart[$actual_key])) {
                        $existing_db = $this->db->where('tenant_id', $tid)
                                                ->where('user_id', (int) $user_id)
                                                ->where('product_id', $pid)
                                                ->where('variant_id', $vid)
                                                ->get('cart_items')
                                                ->row_array();
                        if ($existing_db) {
                            $built = $this->build_item_data($pid, (int)$existing_db['quantity'], $vid ?: null);
                            if ($built) {
                                $new_cart[$actual_key] = $built;
                            }
                        } else {
                            $qty = max(1, (int) ($citem['quantity'] ?? 1));
                            $this->db->insert('cart_items', [
                                'tenant_id'  => $tid,
                                'user_id'    => (int) $user_id,
                                'product_id' => $pid,
                                'variant_id' => $vid,
                                'quantity'   => $qty,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                            $new_cart[$actual_key] = $citem;
                        }
                    }
                }
            }

            $cart = $new_cart;
            $this->set_session_cart($cart);
        }

        return $cart;
    }

    /**
     * Merge guest session cart into database when user logs in or registers.
     */
    public function sync_session_to_db($user_id)
    {
        $user_id = $this->active_user_id() ?: (int) $user_id;
        if (empty($user_id)) {
            return;
        }

        $tid = $this->tid();
        $session_cart = $this->get_session_cart();

        // 1. Group & aggregate guest cart by (product_id, variant_id) to avoid duplicate iterations
        $normalized_guest_items = [];
        if (!empty($session_cart) && is_array($session_cart)) {
            foreach ($session_cart as $ckey => $item) {
                $pid = (int) ($item['id'] ?? $item['product_id'] ?? 0);
                if ($pid <= 0 || !$this->is_valid_tenant_product($pid)) continue;
                $vid = !empty($item['variant_id']) ? (int) $item['variant_id'] : 0;
                $ukey = $pid . '_' . $vid;
                $qty = max(1, (int) ($item['quantity'] ?? 1));
                $stock_max = !empty($item['stock_max']) ? (int)$item['stock_max'] : 999;

                if (isset($normalized_guest_items[$ukey])) {
                    $normalized_guest_items[$ukey]['quantity'] += $qty;
                } else {
                    $normalized_guest_items[$ukey] = [
                        'product_id' => $pid,
                        'variant_id' => $vid,
                        'quantity'   => $qty,
                        'stock_max'  => $stock_max
                    ];
                }
            }
        }

        // 2. Merge guest items into database cart_items table
        if (!empty($normalized_guest_items)) {
            foreach ($normalized_guest_items as $item) {
                $pid = $item['product_id'];
                $vid = $item['variant_id'];
                $qty = $item['quantity'];

                if (!$this->is_valid_tenant_product($pid)) {
                    continue;
                }

                $existing = $this->db->where('tenant_id', $tid)
                                     ->where('user_id', (int) $user_id)
                                     ->where('product_id', $pid)
                                     ->where('variant_id', $vid)
                                     ->get('cart_items')
                                     ->row_array();
                if ($existing) {
                    $max_limit = !empty($item['stock_max']) ? (int)$item['stock_max'] : 999;
                    $merged_qty = min($max_limit, (int)$existing['quantity'] + $qty);
                    $this->db->where('id', $existing['id'])->update('cart_items', [
                        'quantity'   => $merged_qty,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                } else {
                    $this->db->query(
                        "INSERT INTO cart_items (tenant_id, user_id, product_id, variant_id, quantity, created_at, updated_at) " .
                        "VALUES (?, ?, ?, ?, ?, NOW(), NOW()) " .
                        "ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity), updated_at = NOW()",
                        [(int)$tid, (int)$user_id, (int)$pid, (int)$vid, (int)$qty]
                    );
                }
            }
        }

        // 3. Also associate any saved_for_later items from session_id to user_id
        $sess_id = $this->session->session_id ?: session_id();
        if ($sess_id) {
            $this->db->where('tenant_id', $tid)
                     ->where('session_id', $sess_id)
                     ->where('user_id IS NULL', null, false)
                     ->update('cart_saved_items', ['user_id' => (int) $user_id]);
        }

        // 4. Rebuild session cart fresh with all merged items from database
        $new_cart = [];
        $db_rows = $this->db->where('tenant_id', $tid)->where('user_id', (int) $user_id)->get('cart_items')->result_array();
        foreach ($db_rows as $row) {
            $vid = !empty($row['variant_id']) ? (int)$row['variant_id'] : null;
            $ckey = (string) $row['product_id'] . ($vid ? '_' . $vid : '');
            $built = $this->build_item_data($row['product_id'], (int)$row['quantity'], $vid);
            if ($built) {
                $new_cart[$ckey] = $built;
            }
        }
        $this->set_session_cart($new_cart);
    }

    /**
     * Build standard cart item structure from product and variant IDs.
     */
    public function build_item_data($product_id, $quantity = 1, $variant_id = NULL)
    {
        $product = $this->product_model->get_by_id($product_id);
        if (!$product) {
            return NULL;
        }

        $variant = NULL;
        if (!empty($variant_id)) {
            $variant = $this->product_model->get_variant_by_id($variant_id);
            if (!$variant || $variant['product_id'] != $product_id) {
                $variant = NULL;
                $variant_id = NULL;
            }
        } elseif (!empty($product['variants']) && is_array($product['variants'])) {
            // Auto-select primary/default variant (prefer 1 kg or first variant)
            $default_var = $product['variants'][0];
            foreach ($product['variants'] as $v_cand) {
                $v_title = strtolower($v_cand['title'] ?? '');
                if (strpos($v_title, '1 kg') !== false || strpos($v_title, '1kg') !== false) {
                    $default_var = $v_cand;
                    break;
                }
            }
            $variant = $default_var;
            $variant_id = (int)$default_var['id'];
        }

        $cart_key = $product_id . ($variant ? '_' . $variant['id'] : '');

        $available_stock = $variant ? (int) $variant['stock_quantity'] : (int) $product['stock_quantity'];
        $max_limit = !empty($product['max_purchase_quantity']) ? (int) $product['max_purchase_quantity'] : 999;
        if ($max_limit > 0) {
            $available_stock = min($available_stock, $max_limit);
        }
        $target_qty = min($quantity, max(1, $available_stock));

        $is_fs = (!empty($variant['is_flash_sale']) || !empty($product['is_flash_sale'])) ? 1 : 0;
        if ($variant) {
            $base_reg = (isset($variant['base_price']) && is_numeric($variant['base_price']) && (float)$variant['base_price'] > 0)
                ? (float)$variant['base_price']
                : (!empty($variant['price']) ? (float)$variant['price'] : 0);
            $orig_selling = (isset($variant['original_selling_price']) && is_numeric($variant['original_selling_price']) && (float)$variant['original_selling_price'] > 0)
                ? (float)$variant['original_selling_price']
                : ((!empty($variant['sale_price']) && (float)$variant['sale_price'] > 0) ? (float)$variant['sale_price'] : $base_reg);

            if ($is_fs) {
                $price = (float)$variant['sale_price'];
                $regular_price = $base_reg;
                $base_p = $orig_selling;
            } else {
                $price = $orig_selling;
                $regular_price = $base_reg;
                $base_p = $orig_selling;
            }
            $image = !empty($variant['image']) ? $variant['image'] : $product['main_image'];
            $sku   = !empty($variant['sku']) ? $variant['sku'] : $product['sku'];
        } else {
            $base_reg = (isset($product['base_price']) && is_numeric($product['base_price']) && (float)$product['base_price'] > 0)
                ? (float)$product['base_price']
                : (!empty($product['price']) ? (float)$product['price'] : 0);
            $orig_selling = (isset($product['original_selling_price']) && is_numeric($product['original_selling_price']) && (float)$product['original_selling_price'] > 0)
                ? (float)$product['original_selling_price']
                : ((!empty($product['sale_price']) && (float)$product['sale_price'] > 0) ? (float)$product['sale_price'] : $base_reg);

            if ($is_fs) {
                $price = (float)$product['sale_price'];
                $regular_price = $base_reg;
                $base_p = $orig_selling;
            } else {
                $price = $orig_selling;
                $regular_price = $base_reg;
                $base_p = $orig_selling;
            }
            $image = $product['main_image'];
            $sku   = $product['sku'];
        }

        if ($regular_price <= $price && $base_reg <= 0) {
            $regular_price = round($price * 1.30, 2);
        }
        if ($base_reg <= $base_p && $base_reg <= 0) {
            $base_reg = round($base_p * 1.30, 2);
        }
        if ($regular_price <= $price) {
            $regular_price = $price;
        }
        $disc_pct = ($regular_price > $price) ? round((($regular_price - $price) / $regular_price) * 100) : 0;

        $item_size = '';
        $item_color = '';
        if ($variant && !empty($variant['values'])) {
            foreach ($variant['values'] as $vval) {
                $aslug = strtolower($vval['attribute_slug'] ?? ($vval['attribute_name'] ?? ''));
                if ($aslug === 'size' && empty($item_size)) {
                    $item_size = $vval['attribute_value'];
                } elseif ($aslug === 'color' && empty($item_color)) {
                    $item_color = $vval['attribute_value'];
                }
            }
        }

        if ($variant && (empty($item_size) || empty($item_color)) && !empty($variant['title'])) {
            $opt_str = $variant['title'];
            if (strpos($opt_str, '-') !== false) {
                $dash_parts = explode('-', $opt_str);
                $opt_str = trim(end($dash_parts));
            }
            if (strpos($opt_str, '/') !== false) {
                $parts = array_map('trim', explode('/', $opt_str));
                $sizes_known = ['xs', 's', 'm', 'l', 'xl', 'xxl', 'xxxl', '28', '30', '32', '34', '36', '38', '40', '42'];
                foreach ($parts as $p) {
                    if (in_array(strtolower($p), $sizes_known)) {
                        if (empty($item_size)) $item_size = $p;
                    } else {
                        if (empty($item_color)) $item_color = $p;
                    }
                }
            } else {
                if (empty($item_size)) $item_size = $opt_str;
            }
        }

        $p_info = $this->db->select('b.name as brand_name, p.max_purchase_quantity, p.stock_quantity, p.is_cod_allowed, p.rating, p.reviews_count, p.short_description')
                           ->from('products p')
                           ->join('brands b', 'b.id = p.brand_id', 'left')
                           ->where('p.id', (int) $product['id'])
                           ->get()
                           ->row_array();

        $rev_stats = $this->db->select('COUNT(*) as total_count, AVG(rating) as avg_rating')
                              ->where('tenant_id', $this->tid())
                              ->where('product_id', (int)$product['id'])
                              ->where('status', 'approved')
                              ->get('reviews')
                              ->row_array();
        if (!empty($rev_stats) && (int)$rev_stats['total_count'] > 0) {
            $prod_rating = round((float)$rev_stats['avg_rating'], 1);
            $prod_reviews = (int)$rev_stats['total_count'];
        } else {
            $prod_rating = (isset($p_info['rating']) && (float)$p_info['rating'] > 0) ? (float)$p_info['rating'] : 5.0;
            $prod_reviews = isset($p_info['reviews_count']) ? (int)$p_info['reviews_count'] : 0;
        }

        return [
            'cart_key'           => $cart_key,
            'id'                 => (int) $product['id'],
            'variant_id'         => $variant ? (int) $variant['id'] : NULL,
            'variant_title'      => $variant ? $variant['title'] : NULL,
            'size'               => $item_size,
            'color'              => $item_color,
            'brand_name'         => !empty($p_info['brand_name']) ? $p_info['brand_name'] : (!empty($product['brand_name']) ? $product['brand_name'] : 'VTEXX'),
            'title'              => $product['title'],
            'slug'               => $product['slug'],
            'sku'                => $sku,
            'image'              => $image,
            'base_price'         => (float) $base_p,
            'base_regular_price' => (float) $base_reg,
            'price'              => (float) $price,
            'regular_price'      => (float) $regular_price,
            'discount_percent'   => $disc_pct,
            'quantity'           => $target_qty,
            'total'              => (float) ($price * $target_qty),
            'stock_max'          => $available_stock,
            'is_cod_allowed'     => isset($p_info['is_cod_allowed']) ? (int) $p_info['is_cod_allowed'] : 1,
            'rating'             => $prod_rating,
            'reviews_count'      => $prod_reviews,
            'short_description'  => !empty($p_info['short_description']) ? $p_info['short_description'] : '',
            'is_flash_sale'      => $is_fs,
            'original_selling_price' => $orig_selling ?? $base_p
        ];
    }
}
