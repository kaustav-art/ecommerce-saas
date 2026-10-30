<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Product Controller
 * Renders the database-driven Product Details page across all 3 templates.
 */
class product extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->detail('common');
    }

    public function detail($slug = 'common')
    {
        $tpl_key      = $this->template_registry->get_tpl_key($this->active_template);
        $all_products = Static_template_data::get('products');
        if (empty($all_products)) {
            $all_products = $this->get_dynamic_template_products($tpl_key);
        }
        $product      = NULL;

        $clean_slug = trim((string)$slug);
        if ($clean_slug !== '' && $clean_slug !== 'common' && !empty($all_products)) {
            foreach ($all_products as $p) {
                if ((!empty($p['slug']) && strcasecmp($p['slug'], $clean_slug) === 0) || (is_numeric($clean_slug) && (int)$p['id'] === (int)$clean_slug)) {
                    $product = $p;
                    break;
                }
            }
        }

        // Only merge template_products showcase overrides when running in master demo mode (not user project)
        if (!$this->is_user_project) {
            $tpl_products = $this->get_dynamic_template_products($tpl_key);
            if (!empty($tpl_products)) {
                $matched_tpl = null;
                foreach ($tpl_products as $gp) {
                    if ((!empty($gp['slug']) && strcasecmp($gp['slug'], $clean_slug) === 0) || (is_numeric($clean_slug) && (int)$gp['id'] === (int)$clean_slug)) {
                        $matched_tpl = $gp;
                        break;
                    }
                }
                if ($matched_tpl) {
                    $product = $product ? array_merge($product, $matched_tpl) : $matched_tpl;
                }
            }
        }

        if (!$product) {
            $product = !empty($all_products[0]) ? $all_products[0] : Static_template_data::get('common_product');
        }

        // Normalize image paths for active template
        if (!empty($product['main_image'])) {
            $product['primary_image'] = $this->normalize_tpl_image($product['main_image'], $tpl_key);
            $product['image']         = $product['primary_image'];
        }

        $other_products = array_values(array_filter($all_products, function($p) use ($product) {
            return (int)($p['id'] ?? 0) !== (int)($product['id'] ?? -1);
        }));

        // Prioritize related products from the same category / parent category tree
        $prod_cat_id = (int)($product['category_id'] ?? 0);
        $related_cat_ids = $prod_cat_id > 0 ? [$prod_cat_id] : [];
        if ($prod_cat_id > 0) {
            $all_cats = Static_template_data::get('all_categories');
            $cat_by_id = [];
            foreach ($all_cats as $c) {
                $cat_by_id[(int)$c['id']] = $c;
            }
            // Walk up to root category so siblings in the same department are also considered related
            $root_id = $prod_cat_id;
            $g = 0;
            while ($root_id > 0 && !empty($cat_by_id[$root_id]['parent_id']) && $g < 10) {
                $root_id = (int)$cat_by_id[$root_id]['parent_id'];
                $g++;
            }
            if (!empty($cat_by_id[$root_id]['descendant_ids'])) {
                $related_cat_ids = $cat_by_id[$root_id]['descendant_ids'];
            }
        }

        $same_cat_products = [];
        $diff_cat_products = [];
        foreach ($other_products as $op) {
            if (!empty($related_cat_ids) && in_array((int)($op['category_id'] ?? 0), $related_cat_ids, true)) {
                $same_cat_products[] = $op;
            } else {
                $diff_cat_products[] = $op;
            }
        }

        $related_products           = !empty($same_cat_products) ? array_slice($same_cat_products, 0, 8) : array_slice($other_products, 0, 8);
        $frequently_bought_together = array_slice(array_merge($same_cat_products, $diff_cat_products), 0, 2);
        $recently_viewed_products   = array_slice(array_merge($same_cat_products, $diff_cat_products), 2, 4);

        $is_in_wishlist = FALSE;
        $prod_id = (int)($product['id'] ?? 0);
        if ($prod_id > 0 && $this->is_logged_in() && !empty($this->current_user['id'])) {
            $this->load->model('wishlist_model');
            $is_in_wishlist = $this->wishlist_model->is_wishlisted((int)$this->current_user['id'], $prod_id);
        }

        $data = [
            'title'                      => ($product['title'] ?? ($product['name'] ?? 'Product Details')) . ' - ' . $this->site_name,
            'active_page'                => 'shop',
            'product'                    => $product,
            'related_products'           => $related_products,
            'frequently_bought_together' => $frequently_bought_together,
            'recently_viewed_products'   => $recently_viewed_products,
            'is_in_wishlist'             => $is_in_wishlist,
            'is_in_compare'              => FALSE
        ];

        $this->render('product/detail', $data);
    }

    public function reviews($slug = 'common')
    {
        $all_products = Static_template_data::get('products');
        $product      = NULL;

        $clean_slug = trim((string) $slug);
        if ($clean_slug !== '' && $clean_slug !== 'common' && !empty($all_products)) {
            foreach ($all_products as $p) {
                if ((!empty($p['slug']) && strcasecmp($p['slug'], $clean_slug) === 0) || (is_numeric($clean_slug) && (int) $p['id'] === (int) $clean_slug)) {
                    $product = $p;
                    break;
                }
            }
        }

        if (!$this->is_user_project && $this->active_template === 'grocery') {
            $grc_products = $this->get_dynamic_template_products('template3');
            if (!empty($grc_products)) {
                $matched_grc = null;
                foreach ($grc_products as $gp) {
                    if (!empty($gp['slug']) && strcasecmp($gp['slug'], $clean_slug) === 0) {
                        $matched_grc = $gp;
                        break;
                    }
                }
                if ($matched_grc) {
                    $product = $product ? array_merge($product, $matched_grc) : $matched_grc;
                }
            }
        }

        if (!$product) {
            $product = Static_template_data::get('common_product');
        }

        $tpl_key = $this->template_registry->get_tpl_key($this->active_template);
        if (!empty($product['main_image'])) {
            $product['primary_image'] = $this->normalize_tpl_image($product['main_image'], $tpl_key);
            $product['image']         = $product['primary_image'];
        }

        $data = [
            'title'       => 'All Reviews - ' . ($product['title'] ?? ($product['name'] ?? 'Product')) . ' - ' . $this->site_name,
            'active_page' => 'shop',
            'product'     => $product,
        ];

        $this->render('product/reviews', $data);
    }

    public function get_variant_ajax()
    {
        $variant_id   = (int) $this->input->get('variant_id');
        $all_products = Static_template_data::get('products');
        $matched      = NULL;

        foreach ($all_products as $p) {
            if (!empty($p['variants'])) {
                foreach ($p['variants'] as $v) {
                    if ((int)$v['id'] === $variant_id) {
                        $matched = $v;
                        break 2;
                    }
                }
            }
        }

        if (!$matched) {
            $product  = Static_template_data::get('common_product');
            $variants = $product['variants'] ?? [];
            if (!empty($variants[0])) {
                $matched = $variants[0];
            }
        }

        if (!$matched) {
            $this->json_response(['success' => false, 'message' => 'Variant not found.'], 404);
            return;
        }

        $this->json_response([
            'success' => true,
            'variant' => [
                'id'             => $matched['id'],
                'product_id'     => $matched['product_id'],
                'title'          => $matched['title'],
                'sku'            => $matched['sku'],
                'price'          => (float) $matched['price'],
                'sale_price'     => !empty($matched['sale_price']) ? (float) $matched['sale_price'] : null,
                'stock_quantity' => (int) $matched['stock_quantity'],
                'stock_status'   => $matched['stock_status'],
                'image'          => !empty($matched['image']) ? base_url('assets/images/' . $matched['image']) : null,
                'highlights'     => !empty($matched['highlights']) ? (is_array($matched['highlights']) ? $matched['highlights'] : (json_decode($matched['highlights'], true) ?: [])) : [],
                'specifications' => !empty($matched['specifications']) ? (is_array($matched['specifications']) ? $matched['specifications'] : (json_decode($matched['specifications'], true) ?: [])) : []
            ]
        ]);
    }

    public function review()
    {
        $slug = trim((string) ($this->input->post('product_slug', TRUE) ?: 'common'));
        $product_id = (int) $this->input->post('product_id');
        $tid = (int) $this->tenant_id;

        if ($product_id <= 0 && $slug !== '' && $slug !== 'common') {
            $prod_row = $this->db->select('id')
                                 ->where('tenant_id', $tid)
                                 ->where('slug', $slug)
                                 ->get('products')
                                 ->row_array();
            if ($prod_row) {
                $product_id = (int) $prod_row['id'];
            }
        }

        if ($product_id > 0 && $this->input->method() === 'post') {
            $this->load->model('product_model');
            $user_id = !empty($this->current_user['id']) ? (int) $this->current_user['id'] : null;
            $rating  = max(1, min(5, (int) ($this->input->post('rating') ?: 5)));
            $title   = trim((string) $this->input->post('title', TRUE));
            $review  = trim((string) ($this->input->post('review', TRUE) ?: $this->input->post('comment', TRUE)));
            $name    = trim((string) ($this->input->post('name', TRUE) ?: ($this->current_user['name'] ?? 'Verified Customer')));
            $email   = trim((string) ($this->input->post('email', TRUE) ?: ($this->current_user['email'] ?? '')));

            $this->product_model->add_review([
                'product_id'     => $product_id,
                'user_id'        => $user_id,
                'customer_name'  => $name !== '' ? $name : 'Verified Customer',
                'customer_email' => $email,
                'rating'         => $rating,
                'title'          => $title !== '' ? $title : null,
                'review'         => $review,
                'status'         => 'approved',
                'created_at'     => date('Y-m-d H:i:s')
            ]);
            Static_template_data::clear_cache();
        }

        $this->session->set_flashdata('success', 'Thank you! Your review has been submitted.');
        redirect('product/' . $slug);
    }
}
