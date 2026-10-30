<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class shop extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    public function index($category_slug = NULL)
    {
        $raw_category_slug = $category_slug ?: $this->input->get('category');
        $tpl_key           = $this->template_registry->get_tpl_key($this->active_template);
        $db_all_categories = Static_template_data::get('all_categories');

        if ($this->active_template !== 'fashion') {
            $dyn_cats        = $this->get_dynamic_template_categories($tpl_key);
            $all_categories  = $dyn_cats;
            $root_categories = $dyn_cats;
            $all_products    = $this->get_dynamic_template_products($tpl_key);
        } else {
            $dyn_cats        = $this->get_dynamic_template_categories($tpl_key);
            $all_categories  = !empty($db_all_categories) ? $db_all_categories : $dyn_cats;
            $root_categories = Static_template_data::get('root_categories');
            if (empty($root_categories)) {
                $root_categories = $dyn_cats;
            }
            $all_products    = Static_template_data::get('products');
            if (empty($all_products)) {
                $all_products = $this->get_dynamic_template_products($tpl_key);
            }
        }
        $brands            = Static_template_data::get('brands');
        $filter_sizes      = Static_template_data::get('filter_sizes');
        $filter_colors     = Static_template_data::get('filter_colors');

        $selected_category = NULL;
        $breadcrumbs       = [];
        $top_subcategories = $root_categories;

        if (!empty($raw_category_slug)) {
            foreach ($all_categories as $cat) {
                if (strcasecmp($cat['slug'], $raw_category_slug) === 0) {
                    $selected_category = $cat;
                    break;
                }
            }
            // Also search full category hierarchy (including child & grandchild categories)
            if (!$selected_category && !empty($db_all_categories)) {
                foreach ($db_all_categories as $cat) {
                    if (strcasecmp($cat['slug'], $raw_category_slug) === 0) {
                        $selected_category = $cat;
                        break;
                    }
                }
            }
            if (!$selected_category && !$this->is_user_project && $this->active_template !== 'fashion') {
                $selected_category = [
                    'id'            => 0,
                    'name'          => ucwords(str_replace('-', ' ', $raw_category_slug)),
                    'slug'          => $raw_category_slug,
                    'product_count' => 0,
                ];
            }
        }

        if ($selected_category) {
            // Build ancestor breadcrumb trail if selected_category is a subcategory
            $cat_by_id = [];
            foreach ($db_all_categories as $c_row) {
                $cat_by_id[(int)$c_row['id']] = $c_row;
            }
            $trail = [$selected_category];
            $curr_pid = (int)($selected_category['parent_id'] ?? 0);
            $guard = 0;
            while ($curr_pid > 0 && isset($cat_by_id[$curr_pid]) && $guard < 10) {
                array_unshift($trail, $cat_by_id[$curr_pid]);
                $curr_pid = (int)($cat_by_id[$curr_pid]['parent_id'] ?? 0);
                $guard++;
            }
            $breadcrumbs = $trail;

            $subcats = [];
            foreach ($db_all_categories as $cat) {
                if ((int)($cat['parent_id'] ?? 0) === (int)($selected_category['id'] ?? -1) && !empty($selected_category['id'])) {
                    $subcats[] = $cat;
                }
            }
            if (!empty($subcats)) {
                $top_subcategories = $subcats;
            }
        }

        // Parse brand filter
        $brand_param = $this->input->get('brand');
        $selected_brands = [];
        if (!empty($brand_param)) {
            if (is_array($brand_param)) {
                $selected_brands = array_values(array_filter($brand_param));
            } else {
                $selected_brands = [trim($brand_param)];
            }
        }

        $is_brand_store = FALSE;
        $brand_info     = NULL;
        if (!empty($selected_brands) && count($selected_brands) === 1) {
            foreach ($brands as $b) {
                if (strcasecmp($b['slug'], $selected_brands[0]) === 0) {
                    $brand_info = $b;
                    break;
                }
            }
            if ($brand_info && empty($raw_category_slug)) {
                $is_brand_store = TRUE;
            }
        }

        // Parse attribute value filters
        $attr_params = $this->input->get('attrs');
        $attr_value_ids = [];
        if (!empty($attr_params) && is_array($attr_params)) {
            $attr_value_ids = array_values(array_filter(array_map('intval', $attr_params)));
        } elseif (!empty($attr_params) && is_numeric($attr_params)) {
            $attr_value_ids = [(int) $attr_params];
        }

        // Sorting
        $sort_param   = $this->input->get('sort');
        $valid_sorts  = ['a-z', 'z-a', 'price-low-high', 'price-high-low'];
        $current_sort = in_array($sort_param, $valid_sorts) ? $sort_param : 'a-z';

        $search_q  = trim((string)$this->input->get('q'));
        $min_price = $this->input->get('min_price') !== NULL && $this->input->get('min_price') !== '' ? (float)$this->input->get('min_price') : NULL;
        $max_price = $this->input->get('max_price') !== NULL && $this->input->get('max_price') !== '' ? (float)$this->input->get('max_price') : NULL;
        $on_sale        = $this->input->get('on_sale') ? TRUE : FALSE;
        $in_stock       = $this->input->get('in_stock') ? TRUE : FALSE;
        $filter_popular = ($this->input->get('filter') === 'popular' || $this->input->get('popular') == '1');

        // Build allowed category IDs/slugs/names (including all descendant subcategories)
        $allowed_cat_ids   = [];
        $allowed_cat_slugs = [];
        $allowed_cat_names = [];
        if ($selected_category) {
            if (!empty($selected_category['descendant_ids'])) {
                $allowed_cat_ids = array_map('intval', $selected_category['descendant_ids']);
            } elseif (!empty($selected_category['id'])) {
                $allowed_cat_ids = [(int)$selected_category['id']];
            }
            if (!empty($selected_category['descendant_slugs'])) {
                $allowed_cat_slugs = array_map('strtolower', $selected_category['descendant_slugs']);
            } elseif (!empty($selected_category['slug'])) {
                $allowed_cat_slugs = [strtolower($selected_category['slug'])];
            }
            if (!empty($selected_category['descendant_names'])) {
                $allowed_cat_names = array_map('strtolower', $selected_category['descendant_names']);
            } elseif (!empty($selected_category['name'])) {
                $allowed_cat_names = [strtolower($selected_category['name'])];
            }
        }

        $selected_brands_lower = array_map('strtolower', $selected_brands);

        // Filter products dynamically
        $products = array_values(array_filter($all_products, function($p) use (
            $selected_category, $allowed_cat_ids, $allowed_cat_slugs, $allowed_cat_names,
            $selected_brands_lower, $search_q, $min_price, $max_price, $on_sale, $in_stock, $filter_popular, $attr_value_ids
        ) {
            // 1. Category Filter
            if ($selected_category) {
                $p_cid   = isset($p['category_id']) ? (int)$p['category_id'] : 0;
                $p_cslug = strtolower(trim((string)($p['category_slug'] ?? '')));
                $p_cname = strtolower(trim((string)($p['category_name'] ?? '')));

                $cat_match = FALSE;
                if ($p_cid > 0 && !empty($allowed_cat_ids) && in_array($p_cid, $allowed_cat_ids, true)) {
                    $cat_match = TRUE;
                } elseif ($p_cslug !== '' && !empty($allowed_cat_slugs) && in_array($p_cslug, $allowed_cat_slugs, true)) {
                    $cat_match = TRUE;
                } elseif ($p_cid === 0 && $p_cname !== '' && !empty($allowed_cat_names) && in_array($p_cname, $allowed_cat_names, true)) {
                    $cat_match = TRUE;
                }
                if (!$cat_match) {
                    return FALSE;
                }
            }

            // 2. Brand Filter
            if (!empty($selected_brands_lower)) {
                $p_bslug = strtolower(trim((string)($p['brand_slug'] ?? '')));
                $p_bname = strtolower(trim((string)($p['brand_name'] ?? '')));
                if (!in_array($p_bslug, $selected_brands_lower, true) && !in_array($p_bname, $selected_brands_lower, true)) {
                    return FALSE;
                }
            }

            // 3. Search Query Filter
            if ($search_q !== '') {
                $haystack = strtolower(
                    ($p['title'] ?? '') . ' ' .
                    ($p['name'] ?? '') . ' ' .
                    ($p['category_name'] ?? '') . ' ' .
                    ($p['brand_name'] ?? '') . ' ' .
                    ($p['sku'] ?? '') . ' ' .
                    ($p['short_description'] ?? '')
                );
                if (stripos($haystack, $search_q) === FALSE) {
                    return FALSE;
                }
            }

            // 4. Price Filter
            $eff_price = (!empty($p['sale_price']) && (float)$p['sale_price'] > 0) ? (float)$p['sale_price'] : (float)($p['price'] ?? 0);
            if ($min_price !== NULL && $eff_price < $min_price) {
                return FALSE;
            }
            if ($max_price !== NULL && $eff_price > $max_price) {
                return FALSE;
            }

            // Popular Filter
            if ($filter_popular) {
                if (empty($p['is_featured']) && empty($p['is_trending'])) {
                    return FALSE;
                }
            }

            // 5. On Sale Filter
            if ($on_sale) {
                $reg_p = (float)($p['regular_price'] ?? $p['price'] ?? 0);
                $sal_p = (!empty($p['sale_price']) && (float)$p['sale_price'] > 0) ? (float)$p['sale_price'] : 0;
                if ($sal_p <= 0 || $sal_p >= $reg_p) {
                    return FALSE;
                }
            }

            // 6. In Stock Filter
            if ($in_stock) {
                if (($p['stock_status'] ?? 'in_stock') === 'out_of_stock' || (isset($p['stock_quantity']) && (int)$p['stock_quantity'] <= 0)) {
                    return FALSE;
                }
            }

            // 7. Attribute Value IDs Filter
            if (!empty($attr_value_ids)) {
                $has_attr = FALSE;
                if (!empty($p['variants'])) {
                    foreach ($p['variants'] as $v) {
                        foreach ($v['values'] ?? [] as $vv) {
                            if (in_array((int)($vv['attribute_value_id'] ?? 0), $attr_value_ids, true)) {
                                $has_attr = TRUE;
                                break 2;
                            }
                        }
                    }
                }
                if (!$has_attr) {
                    return FALSE;
                }
            }

            return TRUE;
        }));

        // Sort in memory
        usort($products, function($a, $b) use ($current_sort) {
            $pa = !empty($a['sale_price']) ? (float)$a['sale_price'] : (float)$a['price'];
            $pb = !empty($b['sale_price']) ? (float)$b['sale_price'] : (float)$b['price'];
            $ta = $a['title'] ?? ($a['name'] ?? '');
            $tb = $b['title'] ?? ($b['name'] ?? '');
            if ($current_sort === 'price-low-high') return $pa <=> $pb;
            if ($current_sort === 'price-high-low') return $pb <=> $pa;
            if ($current_sort === 'z-a') return strcasecmp($tb, $ta);
            return strcasecmp($ta, $tb);
        });

        // Compute dynamic catalog min & max prices
        $catalog_prices = [];
        foreach ($all_products as $ap) {
            $ep = (!empty($ap['sale_price']) && (float)$ap['sale_price'] > 0) ? (float)$ap['sale_price'] : (float)($ap['price'] ?? 0);
            if ($ep > 0) {
                $catalog_prices[] = $ep;
            }
        }
        $calc_min_price = !empty($catalog_prices) ? floor(min($catalog_prices)) : 0;
        $calc_max_price = !empty($catalog_prices) ? ceil(max($catalog_prices)) : 300;
        if ($calc_max_price <= $calc_min_price) {
            $calc_max_price = $calc_min_price + 100;
        }

        $filters = [
            'category_slug'  => $raw_category_slug,
            'category_ids'   => !empty($allowed_cat_ids) ? $allowed_cat_ids : NULL,
            'brand_slugs'    => $selected_brands,
            'brand_slug'     => !empty($selected_brands) ? $selected_brands[0] : NULL,
            'search'         => $this->input->get('q'),
            'min_price'      => $this->input->get('min_price'),
            'max_price'      => $this->input->get('max_price'),
            'sort'           => $current_sort,
            'on_sale'        => $this->input->get('on_sale') ? 1 : NULL,
            'in_stock'       => $this->input->get('in_stock') ? 1 : NULL,
            'rating'         => $this->input->get('rating') ? (float) $this->input->get('rating') : NULL,
            'attr_value_ids' => $attr_value_ids
        ];

        // Dynamic Pagination & Infinite Scrolling (default limit: 9 for 3-column grocery grid)
        $limit          = $this->input->get('limit') ? max(1, (int)$this->input->get('limit')) : 9;
        $page           = $this->input->get('page') ? max(1, (int)$this->input->get('page')) : 1;
        $offset         = $this->input->get('offset') !== NULL && $this->input->get('offset') !== '' ? max(0, (int)$this->input->get('offset')) : (($page - 1) * $limit);
        $per_page       = $limit;
        $total_products = count($products);
        $paged_products = array_slice($products, $offset, $limit);
        $has_more       = ($offset + count($paged_products)) < $total_products;
        $next_offset    = $offset + count($paged_products);
        $total_pages    = max(1, (int) ceil($total_products / $limit));
        $is_append      = ($offset > 0);

        if ($is_brand_store && $brand_info) {
            $page_heading  = $brand_info['name'] . ' Store';
            $breadcrumbs[] = ['name' => $brand_info['name'] . ' Store', 'slug' => ''];
        } elseif ($filter_popular) {
            $page_heading  = 'Popular Products';
            $breadcrumbs[] = ['name' => 'Popular Products', 'slug' => ''];
        } else {
            $page_heading = $selected_category ? $selected_category['name'] : 'Shop All Products';
        }

        $data = [
            'title'              => $page_heading . ' - ' . $this->site_name,
            'active_page'        => 'shop',
            'currency_symbol'    => $this->store_settings['currency_symbol'] ?? '$',
            'products'           => $paged_products,
            'all_products'       => $products,
            'all_filtered_count' => $total_products,
            'total_all_products' => count($all_products),
            'all_categories'     => $all_categories,
            'db_all_categories'  => $db_all_categories,
            'total_products'     => $total_products,
            'has_more'           => $has_more,
            'offset'             => $offset,
            'next_offset'        => $next_offset,
            'limit'              => $limit,
            'is_append'          => $is_append,
            'brands'             => $brands,
            'selected_brands'    => $selected_brands,
            'filter_sizes'       => $filter_sizes,
            'filter_colors'      => $filter_colors,
            'other_variants'     => [],
            'min_catalog_price'  => $calc_min_price,
            'max_catalog_price'  => $calc_max_price,
            'filters'            => $filters,
            'selected_category'  => $selected_category,
            'breadcrumbs'        => $breadcrumbs,
            'top_subcategories'  => $top_subcategories,
            'page_heading'       => $page_heading,
            'is_brand_store'     => $is_brand_store,
            'brand_info'         => $brand_info,
            'current_page'       => $page,
            'total_pages'        => $total_pages,
            'per_page'           => $per_page
        ];

        if ($this->input->is_ajax_request() || $this->input->get('ajax') == '1') {
            $tpl_key = $this->template_registry->get_tpl_key($this->active_template);
            if ($tpl_key === 'template3' || $this->active_template === 'grocery') {
                $products_html = $this->load->view('template3/shop/partials/_product_items', [
                    'shop_products'     => $paged_products,
                    'user_wishlist_ids' => $this->get_user_wishlist_ids(),
                    'curr'              => $this->store_settings['currency_symbol'] ?? '$',
                    'bazaro'            => base_url('assets/templates/bazaro/'),
                    'grc_qs'            => empty($this->is_user_project) ? '?preview_tpl=grocery' : '',
                    'grc_brands'        => ['NatureFresh Organic', 'GreenValley Farms', 'HarvestGold Dairy', 'Artisan Bakehouse', 'EarthBounty Organics', 'PureOrchard Juices'],
                    'selected_category' => $selected_category,
                    'offset'            => $offset
                ], TRUE);
                $meta_html = '';
            } else {
                $products_html = $this->load->view('template1/shop/partials/product_grid', $data, TRUE);
                $meta_html     = $is_append ? '' : $this->load->view('template1/shop/partials/meta_bar', $data, TRUE);
            }

            $this->json_response([
                'status'         => 'success',
                'total_products' => $total_products,
                'count'          => count($paged_products),
                'offset'         => $offset,
                'next_offset'    => $next_offset,
                'has_more'       => $has_more,
                'products_html'  => $products_html,
                'html'           => $products_html,
                'meta_html'      => $meta_html
            ]);
            return;
        }

        $this->render('shop/index', $data);
    }

    public function autocomplete()
    {
        $q = trim((string) $this->input->get('q', TRUE));
        if (strlen($q) < 1) {
            $this->json_response(['results' => []]);
            return;
        }

        $symbol  = $this->store_settings['currency_symbol'] ?? '$';
        $results = [];
        $tpl_key = $this->template_folder ?? 'template1';

        $main_db_name = !empty($this->master_db_name) ? $this->master_db_name : $this->db->database;
        $check_p = (!$this->is_user_project) ? $this->db->query("SHOW TABLES FROM `{$main_db_name}` LIKE 'template_products'")->row_array() : NULL;
        if (!empty($check_p)) {
            $like_q = '%' . $this->db->escape_like_str($q) . '%';
            $tpl_rows = $this->db->query(
                "SELECT p.*, c.name AS cat_name
                 FROM `{$main_db_name}`.`template_products` p
                 LEFT JOIN `{$main_db_name}`.`template_categories` c ON c.id = p.category_id
                 WHERE p.template_key = ? AND p.status = 1
                   AND (p.title LIKE ? OR c.name LIKE ?)
                 ORDER BY p.sort_order ASC, p.id ASC
                 LIMIT 8",
                [$tpl_key, $like_q, $like_q]
            )->result_array();

            if (!empty($tpl_rows)) {
                foreach ($tpl_rows as $r) {
                    $price = (!empty($r['sale_price']) && (float) $r['sale_price'] > 0) ? (float) $r['sale_price'] : (float) $r['price'];
                    $img = $r['image'] ?? '';
                    $clean = ltrim($img, '/');
                    if (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0) {
                        $img_url = $img;
                    } elseif (strpos($clean, 'assets/') === 0) {
                        $img_url = base_url($clean);
                    } elseif (file_exists(FCPATH . 'assets/images/' . $clean)) {
                        $img_url = base_url('assets/images/' . $clean);
                    } elseif (file_exists(FCPATH . 'assets/templates/bazaro/img/' . $clean)) {
                        $img_url = base_url('assets/templates/bazaro/img/' . $clean);
                    } else {
                        $img_url = base_url('assets/images/' . $clean);
                    }
                    $results[] = [
                        'id'            => (int) $r['id'],
                        'title'         => $r['title'],
                        'sku'           => 'SKU-' . $r['id'],
                        'category_name' => $r['cat_name'] ?? 'Products',
                        'price_html'    => $symbol . number_format($price, 2),
                        'url'           => site_url('product/' . $r['slug']),
                        'image'         => $img_url
                    ];
                }
                $this->json_response(['results' => $results]);
                return;
            }
        }

        $all_products = Static_template_data::get('products');
        foreach ($all_products as $item) {
            if (stripos($item['title'], $q) !== FALSE || stripos($item['category_name'] ?? '', $q) !== FALSE) {
                $price = !empty($item['sale_price']) ? (float) $item['sale_price'] : (float) $item['price'];
                $results[] = [
                    'id'            => $item['id'],
                    'title'         => $item['title'],
                    'sku'           => $item['sku'],
                    'category_name' => $item['category_name'],
                    'price_html'    => $symbol . number_format($price, 2),
                    'url'           => site_url('product/' . $item['slug']),
                    'image'         => base_url('assets/images/' . $item['main_image'])
                ];
            }
        }

        $this->json_response(['results' => array_slice($results, 0, 8)]);
    }
}
