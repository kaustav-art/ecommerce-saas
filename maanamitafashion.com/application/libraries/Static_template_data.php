<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Static_template_data (100% Database-Driven Provider)
 * Replaces the legacy hardcoded static arrays with live MySQL queries
 * scoped by active template / tenant_id.
 */
class Static_template_data {

    protected static $cache = [];

    /**
     * Resolve active database connection and tenant_id based on current context.
     */
    protected static function resolve_db_and_tenant($force_tpl = null) {
        $CI = function_exists('get_instance') ? get_instance() : null;
        $dbName = 'ecommerce_main';
        $activeTpl = 'fashion';
        $tenantId = 1;

        if ($CI && isset($CI->db) && !empty($CI->db->database)) {
            $dbName = $CI->db->database;
        }

        if ($force_tpl !== null) {
            $activeTpl = strtolower(trim((string)$force_tpl));
        } elseif ($CI && !empty($CI->active_template)) {
            $activeTpl = strtolower(trim((string)$CI->active_template));
        } elseif ($dbName === 'ecommerce_main' && !empty($_GET['preview_tpl'])) {
            $p = strtolower(trim((string)$_GET['preview_tpl']));
            if (in_array($p, ['electronics', 'template2'], true)) {
                $activeTpl = 'electronics';
            } elseif (in_array($p, ['grocery', 'template3'], true)) {
                $activeTpl = 'grocery';
            } else {
                $activeTpl = 'fashion';
            }
        }

        if ($dbName === 'ecommerce_main') {
            if ($CI && !empty($CI->tenant_id) && $force_tpl === null && empty($_GET['preview_tpl'])) {
                $tenantId = (int)$CI->tenant_id;
            } elseif (in_array($activeTpl, ['electronics', 'template2'], true)) {
                $tenantId = 3;
            } elseif (in_array($activeTpl, ['grocery', 'template3'], true)) {
                $tenantId = 2;
            } else {
                $tenantId = 1;
            }
        } else {
            // Dedicated tenant database always uses tenant_id = 1
            $tenantId = ($CI && !empty($CI->tenant_id)) ? (int)$CI->tenant_id : 1;
        }

        return [$dbName, $tenantId, $activeTpl];
    }

    /**
     * Get shared PDO connection for fast querying
     */
    protected static function get_pdo($dbName = 'ecommerce_main') {
        static $pdos = [];
        if (!isset($pdos[$dbName])) {
            $pdos[$dbName] = new PDO("mysql:host=localhost;dbname={$dbName};charset=utf8mb4", 'root', '', [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
        return $pdos[$dbName];
    }

    public static function clear_cache() {
        self::$cache = [];
    }

    public static function is_electronics_context() {
        list($dbName, $tenantId, $activeTpl) = self::resolve_db_and_tenant();
        return in_array($activeTpl, ['electronics', 'template2'], true) || ($dbName === 'ecommerce_main' && $tenantId === 3);
    }

    public static function is_grocery_context() {
        list($dbName, $tenantId, $activeTpl) = self::resolve_db_and_tenant();
        return in_array($activeTpl, ['grocery', 'template3'], true) || ($dbName === 'ecommerce_main' && $tenantId === 2);
    }

    public static function get($key, $default = null) {
        list($dbName, $tenantId, $activeTpl) = self::resolve_db_and_tenant();
        $dataset = self::load_tenant_dataset($dbName, $tenantId, $activeTpl);
        return array_key_exists($key, $dataset) ? $dataset[$key] : $default;
    }

    public static function get_all() {
        list($dbName, $tenantId, $activeTpl) = self::resolve_db_and_tenant();
        return self::load_tenant_dataset($dbName, $tenantId, $activeTpl);
    }

    public static function get_grocery_overrides() {
        list($dbName, $tenantId, $activeTpl) = self::resolve_db_and_tenant('grocery');
        return self::load_tenant_dataset($dbName, $tenantId, 'grocery');
    }

    public static function get_electronics_overrides() {
        list($dbName, $tenantId, $activeTpl) = self::resolve_db_and_tenant('electronics');
        return self::load_tenant_dataset($dbName, $tenantId, 'electronics');
    }

    protected static function normalize_img($img) {
        $img = ltrim(trim((string)$img), '/');
        if (strpos($img, 'assets/images/') === 0) {
            return substr($img, strlen('assets/images/'));
        }
        return $img;
    }

    /**
     * Load all storefront entities from MySQL for the given database and tenant_id.
     */
    public static function load_tenant_dataset($dbName, $tenantId, $activeTpl = 'fashion') {
        $cacheKey = $dbName . ':' . $tenantId . ':' . $activeTpl;
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $pdo = self::get_pdo($dbName);

        // 1. Settings
        $settings = [
            'site_name'               => 'Store',
            'currency_code'           => 'INR',
            'currency_symbol'         => '₹',
            'shipping_flat_rate'      => '0',
            'free_shipping_min'       => '150',
            'shipping_free_threshold' => '150',
            'tax_rate_percent'        => '0',
            'tax_enabled'             => '0',
            'tax_inclusive'           => '1',
            'enable_cod'              => '1',
            'enable_razorpay'         => '1',
            'enable_stripe'           => '1',
            'active_online_gateway'   => 'razorpay',
        ];
        $stStmt = $pdo->prepare("SELECT `setting_key`, `setting_value` FROM `settings` WHERE `tenant_id` = ?");
        $stStmt->execute([$tenantId]);
        foreach ($stStmt->fetchAll() as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        // 2. Banners
        $banStmt = $pdo->prepare("SELECT * FROM `banners` WHERE `tenant_id` = ? AND `status` = 'active' ORDER BY `sort_order` ASC, `id` ASC");
        $banStmt->execute([$tenantId]);
        $banners = [];
        foreach ($banStmt->fetchAll() as $b) {
            $b['image'] = self::normalize_img($b['image']);
            $banners[] = $b;
        }

        // 3. Categories (all_categories, root_categories, category_tree) with recursive descendant rollup
        $catStmt = $pdo->prepare("SELECT c.*, (SELECT COUNT(*) FROM `products` p WHERE p.category_id = c.id AND p.tenant_id = c.tenant_id AND p.status = 'published') AS direct_product_count
            FROM `categories` c WHERE c.tenant_id = ? AND c.status = 'active' ORDER BY c.is_featured DESC, c.sort_order DESC, c.id DESC");
        $catStmt->execute([$tenantId]);
        $rawCats = $catStmt->fetchAll();

        $catById = [];
        $childrenIdsByParent = [];
        foreach ($rawCats as $c) {
            $cid = (int)$c['id'];
            $pid = (int)$c['parent_id'];
            $c['id'] = $cid;
            $c['parent_id'] = $pid;
            $c['direct_product_count'] = (int)$c['direct_product_count'];
            $c['raw_image'] = $c['image'];
            if ($activeTpl === 'fashion' || $activeTpl === 'template1') {
                $c['image'] = self::normalize_img($c['image']);
            }
            $catById[$cid] = $c;
            $childrenIdsByParent[$pid][] = $cid;
        }

        // Helper closure to collect all descendant IDs & slugs and sum product_count recursively
        $computeDescendantInfo = function($cid, &$visited = []) use (&$computeDescendantInfo, &$catById, &$childrenIdsByParent) {
            if (isset($visited[$cid]) || !isset($catById[$cid])) {
                return ['ids' => [], 'slugs' => [], 'names' => [], 'count' => 0];
            }
            $visited[$cid] = true;
            $node = $catById[$cid];
            $ids   = [$cid];
            $slugs = !empty($node['slug']) ? [strtolower($node['slug'])] : [];
            $names = !empty($node['name']) ? [strtolower($node['name'])] : [];
            $count = (int)$node['direct_product_count'];

            foreach ($childrenIdsByParent[$cid] ?? [] as $childId) {
                $sub = $computeDescendantInfo($childId, $visited);
                $ids   = array_merge($ids, $sub['ids']);
                $slugs = array_merge($slugs, $sub['slugs']);
                $names = array_merge($names, $sub['names']);
                $count += $sub['count'];
            }
            return [
                'ids'   => array_values(array_unique($ids)),
                'slugs' => array_values(array_unique($slugs)),
                'names' => array_values(array_unique($names)),
                'count' => $count,
            ];
        };

        $allCategories = [];
        $rootCategories = [];
        $childrenByParent = [];
        foreach ($catById as $cid => $c) {
            $desc = $computeDescendantInfo($cid);
            $c['descendant_ids']   = $desc['ids'];
            $c['descendant_slugs'] = $desc['slugs'];
            $c['descendant_names'] = $desc['names'];
            $c['product_count']    = ($tenantId <= 3) ? max(1, (int)$desc['count']) : (int)$desc['count'];
            $catById[$cid]         = $c;
            $allCategories[]       = $c;
            if ($c['parent_id'] === 0) {
                $rootCategories[] = $c;
            } else {
                $childrenByParent[$c['parent_id']][] = $c;
            }
        }

        $buildTree = function($parentId) use (&$buildTree, &$childrenByParent) {
            $branch = [];
            foreach ($childrenByParent[$parentId] ?? [] as $child) {
                $child['children'] = $buildTree($child['id']);
                $branch[] = $child;
            }
            return $branch;
        };

        $categoryTree = [];
        foreach ($rootCategories as $rc) {
            $rc['children'] = $buildTree($rc['id']);
            $categoryTree[] = $rc;
        }

        // 4. Brands
        $brStmt = $pdo->prepare("SELECT b.*, (SELECT COUNT(*) FROM `products` p WHERE p.brand_id = b.id AND p.tenant_id = b.tenant_id AND p.status = 'published') AS product_count
            FROM `brands` b WHERE b.tenant_id = ? AND b.status = 'active' ORDER BY b.id ASC");
        $brStmt->execute([$tenantId]);
        $brands = [];
        foreach ($brStmt->fetchAll() as $br) {
            $br['id'] = (int)$br['id'];
            $br['product_count'] = ($tenantId <= 3) ? max(1, (int)$br['product_count']) : (int)$br['product_count'];
            $brands[] = $br;
        }

        // 5. Products (with variants, attributes, specifications, reviews)
        $prodStmt = $pdo->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug, b.name AS brand_name, b.slug AS brand_slug
            FROM `products` p
            LEFT JOIN `categories` c ON c.id = p.category_id AND c.tenant_id = p.tenant_id
            LEFT JOIN `brands` b ON b.id = p.brand_id AND b.tenant_id = p.tenant_id
            WHERE p.tenant_id = ? AND p.status = 'published'
            ORDER BY p.id ASC");
        $prodStmt->execute([$tenantId]);
        $rawProducts = $prodStmt->fetchAll();

        // Preload variants, specs, reviews for all products of this tenant
        $prodIds = array_map(function($r) { return (int)$r['id']; }, $rawProducts);
        $specsByProd = [];
        $reviewsByProd = [];
        $variantsByProd = [];

        if (!empty($prodIds)) {
            $inSql = implode(',', $prodIds);
            // Specifications
            foreach ($pdo->query("SELECT * FROM `product_specifications` WHERE `product_id` IN ($inSql) ORDER BY `sort_order` ASC, `id` ASC") as $sp) {
                $specsByProd[(int)$sp['product_id']][] = $sp;
            }
            // Reviews
            $revStmt = $pdo->prepare("SELECT r.*, u.avatar AS user_avatar
                FROM `reviews` r
                LEFT JOIN `users` u ON u.tenant_id = r.tenant_id AND (u.id = r.user_id OR (r.user_id IS NULL AND r.customer_email != '' AND LOWER(u.email) = LOWER(r.customer_email)))
                WHERE r.tenant_id = ? AND r.product_id IN ($inSql) AND r.status = 'approved'
                ORDER BY r.id DESC");
            $revStmt->execute([$tenantId]);
            foreach ($revStmt->fetchAll() as $rv) {
                $reviewsByProd[(int)$rv['product_id']][] = $rv;
            }
            // Variants + values
            $varStmt = $pdo->prepare("SELECT * FROM `product_variants` WHERE `tenant_id` = ? AND `product_id` IN ($inSql) ORDER BY `id` ASC");
            $varStmt->execute([$tenantId]);
            $allVars = $varStmt->fetchAll();
            $varIds = array_map(function($v) { return (int)$v['id']; }, $allVars);
            $valsByVar = [];
            if (!empty($varIds)) {
                $vInSql = implode(',', $varIds);
                $vvSql = "SELECT pvv.*, av.value AS attribute_value, av.color_code, a.name AS attribute_name, a.slug AS attribute_slug, a.type AS attribute_type
                          FROM `product_variant_values` pvv
                          JOIN `attribute_values` av ON av.id = pvv.attribute_value_id
                          JOIN `attributes` a ON a.id = pvv.attribute_id
                          WHERE pvv.variant_id IN ($vInSql)
                          ORDER BY pvv.id ASC";
                foreach ($pdo->query($vvSql) as $vv) {
                    $valsByVar[(int)$vv['variant_id']][] = $vv;
                }
            }
            foreach ($allVars as $v) {
                $v['image'] = self::normalize_img($v['image']);
                $v['values'] = $valsByVar[(int)$v['id']] ?? [];
                $variantsByProd[(int)$v['product_id']][] = $v;
            }
        }

        // Preload active flash sale for this tenant
        $activeFlashSale = null;
        $fsProductIds = [];
        $fsDiscountPct = 0.0;
        try {
            $nowStr = date('Y-m-d H:i:s');
            $fsStmt = $pdo->prepare("SELECT * FROM `flash_sales` WHERE `tenant_id` = ? AND `status` != 'terminated' AND `start_time` <= ? AND `end_time` >= ? ORDER BY `start_time` ASC LIMIT 1");
            $fsStmt->execute([$tenantId, $nowStr, $nowStr]);
            $activeFlashSale = $fsStmt->fetch();
            if ($activeFlashSale && !empty($activeFlashSale['id'])) {
                $fsDiscountPct = (float)($activeFlashSale['discount_percent'] ?? 0);
                $fspStmt = $pdo->prepare("SELECT product_id, sale_price FROM `flash_sale_products` WHERE `flash_sale_id` = ?");
                $fspStmt->execute([$activeFlashSale['id']]);
                foreach ($fspStmt->fetchAll() as $fspRow) {
                    $fsProductIds[(int)$fspRow['product_id']] = (float)$fspRow['sale_price'];
                }
            }
        } catch (\Exception $e) {
            // Flash sale tables may not exist in some setups
        }

        $products = [];
        foreach ($rawProducts as $p) {
            $pid = (int)$p['id'];
            $mainImg = self::normalize_img($p['main_image']);
            $galDecoded = !empty($p['gallery_images']) ? (json_decode($p['gallery_images'], true) ?: []) : [];
            $galNormalized = array_values(array_map([self::class, 'normalize_img'], $galDecoded));
            $hoverImg = !empty($galNormalized[0]) ? $galNormalized[0] : $mainImg;
            $hlDecoded = !empty($p['highlights']) ? (json_decode($p['highlights'], true) ?: []) : [];

            $pVars = $variantsByProd[$pid] ?? [];
            $pAttrs = [];
            $colorSwatches = [];
            $colorsList = [];
            $sizesList = [];

            foreach ($pVars as &$pv) {
                $varLabelParts = [];
                foreach ($pv['values'] as $val) {
                    $aSlug = $val['attribute_slug'];
                    $varLabelParts[] = $val['attribute_value'];
                    if (!isset($pAttrs[$aSlug])) {
                        $pAttrs[$aSlug] = [
                            'id'     => (int)$val['attribute_id'],
                            'name'   => $val['attribute_name'],
                            'slug'   => $aSlug,
                            'type'   => $val['attribute_type'],
                            'values' => [],
                        ];
                    }
                    $avId = (int)$val['attribute_value_id'];
                    if (!isset($pAttrs[$aSlug]['values'][$avId])) {
                        $pAttrs[$aSlug]['values'][$avId] = [
                            'id'         => $avId,
                            'value'      => $val['attribute_value'],
                            'color_code' => $val['color_code'],
                        ];
                    }
                    if ($aSlug === 'color') {
                        if (!in_array($val['attribute_value'], $colorsList, true)) {
                            $colorsList[] = $val['attribute_value'];
                            $colorSwatches[] = [
                                'name'       => $val['attribute_value'],
                                'color_code' => $val['color_code'] ?: '#111827',
                                'image'      => $pv['image'] ?: $mainImg,
                            ];
                        }
                    } else {
                        if (!in_array($val['attribute_value'], $sizesList, true)) {
                            $sizesList[] = $val['attribute_value'];
                        }
                    }
                }
                if (!empty($varLabelParts)) {
                    $pv['variant_label'] = implode(' / ', $varLabelParts);
                } elseif (!empty($pv['title']) && strpos($pv['title'], '/') !== false) {
                    $parts = explode('/', $pv['title']);
                    $pv['variant_label'] = trim((string)end($parts));
                } else {
                    $pv['variant_label'] = !empty($pv['title']) ? $pv['title'] : ('Variant #' . $pv['id']);
                }
            }
            unset($pv);
            foreach ($pAttrs as &$attrInfo) {
                $attrInfo['values'] = array_values($attrInfo['values']);
            }
            unset($attrInfo);

            $primaryVar = null;
            $isFlashSaleProd = isset($fsProductIds[$pid]);
            $regPrice  = (float)$p['price'];
            $salePrice = (!empty($p['sale_price']) && (float)$p['sale_price'] > 0) ? (float)$p['sale_price'] : null;
            $unitLabel = trim((string)($p['unit_label'] ?? ''));

            // Process variants for flash sale & pricing
            if (!empty($pVars)) {
                foreach ($pVars as &$pvItem) {
                    $vReg = (!empty($pvItem['price']) && (float)$pvItem['price'] > 0) ? (float)$pvItem['price'] : $regPrice;
                    $vSelling = (!empty($pvItem['sale_price']) && (float)$pvItem['sale_price'] > 0) ? (float)$pvItem['sale_price'] : $vReg;
                    if ($isFlashSaleProd && $fsDiscountPct > 0) {
                        $vFlashPrice = round($vSelling * (1 - ($fsDiscountPct / 100)), 2);
                        $vTotalPct   = ($vReg > 0 && $vFlashPrice < $vReg) ? round((($vReg - $vFlashPrice) / $vReg) * 100) : 0;
                        $pvItem['original_selling_price'] = $vSelling;
                        $pvItem['original_regular_price'] = $vReg;
                        $pvItem['price']                  = $vReg;
                        $pvItem['regular_price']          = $vReg;
                        $pvItem['sale_price']             = $vFlashPrice;
                        $pvItem['final_price']            = $vFlashPrice;
                        $pvItem['total_discount_percent'] = $vTotalPct;
                        $pvItem['is_flash_sale']          = 1;
                    } else {
                        $vTotalPct = ($vReg > 0 && $vSelling < $vReg) ? round((($vReg - $vSelling) / $vReg) * 100) : 0;
                        $pvItem['original_selling_price'] = $vSelling;
                        $pvItem['price']                  = $vReg;
                        $pvItem['regular_price']          = $vReg;
                        $pvItem['sale_price']             = ($vSelling < $vReg) ? $vSelling : null;
                        $pvItem['total_discount_percent'] = $vTotalPct;
                        $pvItem['is_flash_sale']          = 0;
                    }
                }
                unset($pvItem);

                // Select primary variant: prefer '1 kg' if present, else first variant
                $primaryVar = $pVars[0];
                foreach ($pVars as $pvCandidate) {
                    if (stripos($pvCandidate['variant_label'] ?? '', '1 kg') !== false || stripos($pvCandidate['variant_label'] ?? '', '1kg') !== false) {
                        $primaryVar = $pvCandidate;
                        break;
                    }
                }
                if ($unitLabel === '' && !empty($primaryVar['variant_label'])) {
                    $unitLabel = $primaryVar['variant_label'];
                }
                $regPrice = (float)$primaryVar['price'];
                $salePrice = (!empty($primaryVar['sale_price']) && (float)$primaryVar['sale_price'] > 0) ? (float)$primaryVar['sale_price'] : null;
            } elseif ($isFlashSaleProd && $fsDiscountPct > 0) {
                // Simple product under flash sale
                $prodSelling = ($salePrice !== null && $salePrice > 0) ? $salePrice : $regPrice;
                $salePrice   = round($prodSelling * (1 - ($fsDiscountPct / 100)), 2);
            }

            $totalDiscountPct = ($regPrice > 0 && $salePrice !== null && $salePrice < $regPrice)
                ? round((($regPrice - $salePrice) / $regPrice) * 100)
                : 0;

            $p['id']                          = $pid;
            $p['name']                        = $p['title'];
            $p['main_image']                  = $mainImg;
            $p['primary_image']               = $mainImg;
            $p['image']                       = $mainImg;
            $p['hover_image']                 = $hoverImg;
            $p['regular_price']               = $regPrice;
            $p['price']                       = $regPrice;
            $p['sale_price']                  = $salePrice;
            $p['unit_label']                  = $unitLabel;
            $p['unit_value']                  = $unitLabel;
            $p['badge_text']                  = $isFlashSaleProd ? 'SALE' : ($salePrice ? 'Sale' : 'Featured');
            $p['is_bestseller']               = (int)($p['is_trending'] ?? 1);
            $p['is_sale']                     = ($salePrice !== null) ? 1 : 0;
            $p['is_flash_sale']               = $isFlashSaleProd ? 1 : 0;
            $p['flash_sale_discount_percent'] = $isFlashSaleProd ? $fsDiscountPct : 0;
            $p['total_discount_percent']      = $totalDiscountPct;
            $p['primary_variant_id']          = !empty($primaryVar['id']) ? (int)$primaryVar['id'] : null;
            $p['default_variant_id']          = !empty($primaryVar['id']) ? (int)$primaryVar['id'] : null;
            $p['gallery_images_decoded']      = $galNormalized;
            $p['highlights_decoded']          = $hlDecoded;
            $p['variants']                    = $pVars;
            $p['attributes']                  = $pAttrs;
            $p['color_swatches']              = $colorSwatches;
            $p['colors']                      = $colorsList;
            $p['sizes']                       = $sizesList;
            $p['specifications']              = $specsByProd[$pid] ?? [];
            $pRevs                            = $reviewsByProd[$pid] ?? [];
            $p['reviews']                     = $pRevs;
            $revCnt                           = count($pRevs);
            $revSum                           = 0;
            foreach ($pRevs as $rv) {
                $revSum += (float)($rv['rating'] ?? 0);
            }
            $p['reviews_count']               = $revCnt;
            $p['rating']                      = $revCnt > 0 ? round($revSum / $revCnt, 1) : 0.0;

            $products[] = $p;
        }

        // 6. Filter Sizes & Filter Colors
        $filterSizes = [];
        $filterColors = [];
        $avStmt = $pdo->prepare("SELECT av.*, a.slug AS attr_slug FROM `attribute_values` av JOIN `attributes` a ON a.id = av.attribute_id WHERE av.tenant_id = ? ORDER BY av.sort_order ASC, av.id ASC");
        $avStmt->execute([$tenantId]);
        foreach ($avStmt->fetchAll() as $av) {
            if ($av['attr_slug'] === 'color') {
                $filterColors[] = $av;
            } else {
                $filterSizes[] = $av;
            }
        }

        // 7. Customer Profile & Addresses
        $uStmt = $pdo->prepare("SELECT * FROM `users` WHERE `tenant_id` = ? ORDER BY `id` ASC LIMIT 1");
        $uStmt->execute([$tenantId]);
        $user = $uStmt->fetch();
        if (!$user) {
            if ($tenantId <= 3) {
                $user = [
                    'id'         => 1,
                    'first_name' => 'Alex',
                    'last_name'  => 'Morgan',
                    'email'      => 'alex.morgan@example.com',
                    'phone'      => '9876543210',
                    'avatar'     => 'default-user.png',
                    'status'     => 'active',
                ];
            } else {
                $user = [];
            }
        }
        if (!empty($user)) {
            $user['name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        }

        $addrStmt = $pdo->prepare("SELECT * FROM `user_addresses` WHERE `tenant_id` = ? ORDER BY `is_default` DESC, `id` ASC");
        $addrStmt->execute([$tenantId]);
        $addresses = $addrStmt->fetchAll();

        // 8. Cart Items & Cart Summary
        $cartStmt = $pdo->prepare("SELECT ci.*, p.title, p.slug, p.sku, p.main_image, p.price AS reg_price, p.sale_price, p.unit_label, p.stock_quantity, p.max_purchase_quantity, p.is_cod_allowed
            FROM `cart_items` ci
            JOIN `products` p ON p.id = ci.product_id AND p.tenant_id = ci.tenant_id
            WHERE ci.tenant_id = ?
            ORDER BY ci.id ASC");
        $cartStmt->execute([$tenantId]);
        $cartRows = $cartStmt->fetchAll();

        $cartItems = [];
        $itemCount = 0;
        $mrpTotal = 0.0;
        $subtotal = 0.0;
        foreach ($cartRows as $cr) {
            $pid = (int)$cr['product_id'];
            $qty = max(1, (int)$cr['quantity']);
            $regP = (float)$cr['reg_price'];
            $effP = (!empty($cr['sale_price']) && (float)$cr['sale_price'] > 0) ? (float)$cr['sale_price'] : $regP;
            $lineTotal = round($effP * $qty, 2);
            $lineMrp = round($regP * $qty, 2);
            $img = self::normalize_img($cr['main_image']);

            $cartItems[$pid] = [
                'cart_key'        => (string)$pid,
                'id'              => $pid,
                'product_id'      => $pid,
                'variant_id'      => $cr['variant_id'] ? (int)$cr['variant_id'] : null,
                'title'           => $cr['title'],
                'name'            => $cr['title'],
                'slug'            => $cr['slug'],
                'sku'             => $cr['sku'],
                'image'           => $img,
                'price'           => $effP,
                'regular_price'   => $regP,
                'unit_base_price' => $effP,
                'unit_tax_amount' => 0.0,
                'tax_rate'        => 0.0,
                'quantity'        => $qty,
                'stock_quantity'  => (int)$cr['stock_quantity'],
                'max_qty'         => (int)($cr['max_purchase_quantity'] ?: 10),
                'variant_name'    => !empty($cr['unit_label']) ? ('Pack: ' . $cr['unit_label']) : 'Standard Edition',
                'color'           => '',
                'size'            => $cr['unit_label'] ?: 'M',
                'is_cod_allowed'  => (int)$cr['is_cod_allowed'],
                'total'           => $lineTotal,
                'mrp_total'       => $lineMrp,
            ];
            $itemCount += $qty;
            $subtotal += $lineTotal;
            $mrpTotal += $lineMrp;
        }
        $savings = max(0.0, round($mrpTotal - $subtotal, 2));
        $freeShipMin = (float)($settings['free_shipping_min'] ?? 99.0);
        $amtToFree = max(0.0, round($freeShipMin - $subtotal, 2));
        $freeShipPct = $freeShipMin > 0 ? min(100, (int)round(($subtotal / $freeShipMin) * 100)) : 100;

        $cartSummary = [
            'item_count'          => $itemCount,
            'mrp_total'           => round($mrpTotal, 2),
            'mrp_discount'        => $savings,
            'subtotal'            => round($subtotal, 2),
            'base_subtotal'       => round($subtotal, 2),
            'tax'                 => 0.0,
            'tax_inclusive'       => true,
            'discount'            => 0.0,
            'coupon_code'         => '',
            'shipping'            => 0.0,
            'shipping_method'     => 'standard',
            'shipping_breakdown'  => [['name' => 'Standard Express Delivery', 'value' => 0]],
            'free_shipping_min'   => $freeShipMin,
            'amount_to_free_ship' => $amtToFree,
            'free_ship_percent'   => $freeShipPct,
            'total'               => round($subtotal, 2),
            'total_savings'       => $savings,
        ];

        // 9. Orders & Order Items
        $ordStmt = $pdo->prepare("SELECT * FROM `orders` WHERE `tenant_id` = ? ORDER BY `id` DESC LIMIT 20");
        $ordStmt->execute([$tenantId]);
        $rawOrders = $ordStmt->fetchAll();
        $orders = [];
        if (!empty($rawOrders)) {
            $ordIds = array_map(function($o) { return (int)$o['id']; }, $rawOrders);
            $oInSql = implode(',', $ordIds);
            $oiStmt = $pdo->prepare("SELECT oi.*, p.slug AS product_slug, p.price AS reg_price
                FROM `order_items` oi
                LEFT JOIN `products` p ON p.id = oi.product_id AND p.tenant_id = oi.tenant_id
                WHERE oi.tenant_id = ? AND oi.order_id IN ($oInSql)
                ORDER BY oi.id ASC");
            $oiStmt->execute([$tenantId]);
            $itemsByOrd = [];
            foreach ($oiStmt->fetchAll() as $oi) {
                $oi['id'] = (int)$oi['id'];
                $oi['order_id'] = (int)$oi['order_id'];
                $oi['product_id'] = (int)$oi['product_id'];
                $oi['sku'] = $oi['product_sku'] ?? '';
                $oi['product_slug'] = $oi['product_slug'] ?: 'product';
                $oi['product_image'] = self::normalize_img($oi['product_image']);
                $oi['price'] = (float)$oi['price'];
                $oi['regular_price'] = !empty($oi['reg_price']) ? (float)$oi['reg_price'] : $oi['price'];
                $oi['quantity'] = (int)$oi['quantity'];
                $oi['total'] = (float)$oi['total'];
                $itemsByOrd[$oi['order_id']][] = $oi;
            }
            foreach ($rawOrders as $o) {
                $oid = (int)$o['id'];
                $o['id'] = $oid;
                $o['subtotal'] = (float)$o['subtotal'];
                $o['discount_amount'] = (float)$o['discount_amount'];
                $o['shipping_fee'] = (float)$o['shipping_fee'];
                $o['shipping_amount'] = (float)$o['shipping_fee'];
                $o['tax_amount'] = (float)$o['tax_amount'];
                $o['total_amount'] = (float)$o['total_amount'];
                $o['items'] = $itemsByOrd[$oid] ?? [];
                $orders[] = $o;
            }
        }

        // 10. Notifications
        $notifStmt = $pdo->prepare("SELECT * FROM `notifications` WHERE `tenant_id` = ? ORDER BY `id` DESC LIMIT 10");
        $notifStmt->execute([$tenantId]);
        $notifications = $notifStmt->fetchAll();

        // 11. Payment Gateways
        $gwStmt = $pdo->prepare("SELECT `gateway_code`, `gateway_name`, `is_active` FROM `payment_gateways` WHERE `tenant_id` = ? AND `is_active` = 1 ORDER BY `id` ASC");
        $gwStmt->execute([$tenantId]);
        $gateways = $gwStmt->fetchAll();

        $dataset = [
            'settings'        => $settings,
            'banners'         => $banners,
            'all_categories'  => $allCategories,
            'root_categories' => $rootCategories,
            'category_tree'   => $categoryTree,
            'brands'          => $brands,
            'products'        => $products,
            'common_product'  => !empty($products[0]) ? $products[0] : [],
            'filter_sizes'    => $filterSizes,
            'filter_colors'   => $filterColors,
            'user'            => $user,
            'addresses'       => $addresses,
            'cart_items'      => $cartItems,
            'cart_summary'    => $cartSummary,
            'common_order'    => !empty($orders[0]) ? $orders[0] : [],
            'orders'          => $orders,
            'notifications'   => $notifications,
            'gateways'        => $gateways,
        ];

        self::$cache[$cacheKey] = $dataset;
        return $dataset;
    }
}
