<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Layouts Controller - Superadmin Template Customizer
 * Manages Hero Banners, Categories, and Products for:
 *  - template1 (Fashion Storefront -> ?preview_tpl=fashion / ?preview_tpl=template1)
 *  - template2 (Electronics Storefront -> ?preview_tpl=electronics / ?preview_tpl=template2)
 *  - template3 (Grocery Storefront -> ?preview_tpl=grocery / ?preview_tpl=template3)
 */
class Layouts extends Superadmin_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('text');
    }

    public function index() {
        $active_tpl = strtolower(trim((string)$this->input->get('tab', TRUE)));
        if (!in_array($active_tpl, array('template1', 'template2', 'template3'), TRUE)) {
            $active_tpl = 'template1';
        }

        $active_section = strtolower(trim((string)$this->input->get('section', TRUE)));
        if (!in_array($active_section, array('hero', 'categories', 'products'), TRUE)) {
            $active_section = 'hero';
        }

        // Handle POST actions
        if ($this->input->method() === 'post') {
            $action     = $this->input->post('action', TRUE);
            $target_tpl = strtolower(trim((string)$this->input->post('target_tpl', TRUE)));
            if (!in_array($target_tpl, array('template1', 'template2', 'template3'), TRUE)) {
                $target_tpl = $active_tpl;
            }
            $sec_post = strtolower(trim((string)$this->input->post('active_section', TRUE))) ?: $active_section;

            $redirect_back = function($sec = null) use ($target_tpl, $sec_post) {
                $s = $sec ?: $sec_post;
                redirect(superadmin_url('layouts?tab=' . urlencode($target_tpl) . '&section=' . urlencode($s)));
                exit;
            };

            // File upload helper saving to website/assets/images/templates_custom/
            $handle_file_upload = function($field_name, $prefix = 'tpl') {
                if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = dirname(FCPATH) . DIRECTORY_SEPARATOR . 'website' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'templates_custom' . DIRECTORY_SEPARATOR;
                    if (!is_dir($upload_dir)) {
                        @mkdir($upload_dir, 0777, TRUE);
                    }
                    $ext = strtolower(pathinfo($_FILES[$field_name]['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, array('jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'), TRUE)) {
                        $filename = $prefix . '_' . time() . '_' . mt_rand(100, 999) . '.' . $ext;
                        if (move_uploaded_file($_FILES[$field_name]['tmp_name'], $upload_dir . $filename)) {
                            return 'assets/images/templates_custom/' . $filename;
                        }
                    }
                }
                return null;
            };

            // ---------------------------------------------------------
            // 1. HERO BANNERS CRUD
            // ---------------------------------------------------------
            if ($action === 'save_hero_slide') {
                $slide_id    = (int)$this->input->post('slide_id');
                $badge       = trim((string)$this->input->post('badge', TRUE));
                $title       = trim((string)$this->input->post('title', TRUE));
                $description = trim((string)$this->input->post('description', TRUE));
                $button_text = trim((string)$this->input->post('button_text', TRUE)) ?: 'Explore Collection';
                $button_url  = trim((string)$this->input->post('button_url', TRUE)) ?: 'shop';
                $sort_order  = (int)$this->input->post('sort_order') ?: 1;
                $status      = ($this->input->post('status', TRUE) === 'inactive') ? 'inactive' : 'active';

                $image = trim((string)$this->input->post('image_url', TRUE));
                $uploaded_img = $handle_file_upload('image_file', $target_tpl . '_hero');
                if ($uploaded_img) {
                    $image = $uploaded_img;
                }
                if (empty($image)) {
                    if ($target_tpl === 'template1') {
                        $image = 'assets/images/slider/slider-women1.jpg';
                    } elseif ($target_tpl === 'template2') {
                        $image = 'assets/templates/bazaro/img/electronics/slider/slide-1.jpg';
                    } else {
                        $image = 'assets/templates/bazaro/img/grocery/grocery-hero-thumb-1.png';
                    }
                }

                $slide_data = array(
                    'template_key'     => $target_tpl,
                    'layout_number'    => 1,
                    'badge'            => $badge,
                    'title'            => $title,
                    'description'      => $description,
                    'button_text'      => $button_text,
                    'button_url'       => $button_url,
                    'image'            => $image,
                    'background_image' => '',
                    'sort_order'       => $sort_order,
                    'status'           => $status,
                );

                if ($slide_id > 0) {
                    $this->db->where('id', $slide_id)->update('template_hero_banners', $slide_data);
                    $this->session->set_flashdata('success', 'Hero Banner slide updated successfully!');
                } else {
                    $this->db->insert('template_hero_banners', $slide_data);
                    $this->session->set_flashdata('success', 'New Hero Banner slide added successfully!');
                }
                $redirect_back('hero');
            }

            if ($action === 'delete_hero_slide') {
                $slide_id = (int)$this->input->post('slide_id');
                if ($slide_id > 0) {
                    $this->db->where('id', $slide_id)->delete('template_hero_banners');
                    $this->session->set_flashdata('success', 'Hero Banner slide deleted.');
                }
                $redirect_back('hero');
            }

            // ---------------------------------------------------------
            // 2. CATEGORIES CRUD & HEADERS
            // ---------------------------------------------------------
            if ($action === 'save_categories_headers') {
                $tagline = trim((string)$this->input->post('categories_tagline', TRUE));
                $title   = trim((string)$this->input->post('categories_title', TRUE));
                set_tpl_setting($target_tpl, 1, 'categories_header', 'tagline', $tagline);
                set_tpl_setting($target_tpl, 1, 'categories_header', 'title', $title);
                $this->session->set_flashdata('success', 'Categories section heading updated!');
                $redirect_back('categories');
            }

            if ($action === 'save_category') {
                $cat_id     = (int)$this->input->post('category_id');
                $name       = trim((string)$this->input->post('name', TRUE));
                $slug       = trim((string)$this->input->post('slug', TRUE));
                if (empty($slug)) {
                    $slug = url_title($name, 'dash', TRUE);
                }
                $subtitle   = trim((string)$this->input->post('subtitle', TRUE)) ?: '12 Items';
                $sort_order = (int)$this->input->post('sort_order') ?: 1;
                $status     = ($this->input->post('status', TRUE) === 'inactive') ? 'inactive' : 'active';

                $image = trim((string)$this->input->post('image_url', TRUE));
                $uploaded_img = $handle_file_upload('image_file', $target_tpl . '_cat');
                if ($uploaded_img) {
                    $image = $uploaded_img;
                }
                if (empty($image)) {
                    if ($target_tpl === 'template1') {
                        $image = 'assets/images/collections/collection-circle/cls-circle1.jpg';
                    } elseif ($target_tpl === 'template2') {
                        $image = 'assets/templates/bazaro/img/electronics/category/cat-1.jpg';
                    } else {
                        $image = 'assets/templates/bazaro/img/product/grocery-p-1.jpg';
                    }
                }

                $cat_data = array(
                    'template_key'  => $target_tpl,
                    'layout_number' => 1,
                    'name'          => $name,
                    'slug'          => $slug,
                    'subtitle'      => $subtitle,
                    'image'         => $image,
                    'sort_order'    => $sort_order,
                    'status'        => $status,
                );

                if ($cat_id > 0) {
                    $this->db->where('id', $cat_id)->update('template_categories', $cat_data);
                    $this->session->set_flashdata('success', 'Template Category "' . htmlspecialchars($name) . '" updated successfully!');
                } else {
                    $this->db->insert('template_categories', $cat_data);
                    $this->session->set_flashdata('success', 'Template Category "' . htmlspecialchars($name) . '" added successfully!');
                }
                $redirect_back('categories');
            }

            if ($action === 'delete_category') {
                $cat_id = (int)$this->input->post('category_id');
                if ($cat_id > 0) {
                    $this->db->where('id', $cat_id)->delete('template_categories');
                    $this->session->set_flashdata('success', 'Template Category deleted.');
                }
                $redirect_back('categories');
            }

            // ---------------------------------------------------------
            // 3. PRODUCTS CRUD & HEADERS
            // ---------------------------------------------------------
            if ($action === 'save_products_headers') {
                $tagline = trim((string)$this->input->post('products_tagline', TRUE));
                $title   = trim((string)$this->input->post('products_title', TRUE));
                set_tpl_setting($target_tpl, 1, 'products_header', 'tagline', $tagline);
                set_tpl_setting($target_tpl, 1, 'products_header', 'title', $title);
                $this->session->set_flashdata('success', 'Products section heading updated!');
                $redirect_back('products');
            }

            if ($action === 'save_product') {
                $prod_id       = (int)$this->input->post('product_id');
                $name          = trim((string)$this->input->post('name', TRUE));
                $slug          = trim((string)$this->input->post('slug', TRUE));
                if (empty($slug)) {
                    $slug = url_title($name, 'dash', TRUE);
                }
                $category_name = trim((string)$this->input->post('category_name', TRUE)) ?: 'Featured';
                $price         = (float)$this->input->post('price');
                $sale_raw      = trim((string)$this->input->post('sale_price', TRUE));
                $sale_price    = ($sale_raw !== '' && (float)$sale_raw > 0) ? (float)$sale_raw : null;
                $badge_text    = trim((string)$this->input->post('badge_text', TRUE));
                $unit_label    = trim((string)$this->input->post('unit_label', TRUE));
                $short_desc    = trim((string)$this->input->post('short_desc', TRUE));
                $rating        = max(1.0, min(5.0, (float)($this->input->post('rating') ?: 4.8)));
                $reviews_count = max(0, (int)($this->input->post('reviews_count') ?: 15));
                $sort_order    = (int)$this->input->post('sort_order') ?: 1;
                $status        = ($this->input->post('status', TRUE) === 'inactive') ? 'inactive' : 'active';

                $image = trim((string)$this->input->post('image_url', TRUE));
                $uploaded_img = $handle_file_upload('image_file', $target_tpl . '_prod');
                if ($uploaded_img) {
                    $image = $uploaded_img;
                }
                if (empty($image)) {
                    if ($target_tpl === 'template1') {
                        $image = 'assets/images/products/womens/women-1.jpg';
                    } elseif ($target_tpl === 'template2') {
                        $image = 'assets/templates/bazaro/img/electronics/arrival-product/product-1/product-1.jpg';
                    } else {
                        $image = 'assets/templates/bazaro/img/product/grocery-p-1.jpg';
                    }
                }

                $hover_image = trim((string)$this->input->post('hover_image_url', TRUE));
                $uploaded_hover = $handle_file_upload('hover_image_file', $target_tpl . '_prod_hover');
                if ($uploaded_hover) {
                    $hover_image = $uploaded_hover;
                }
                if (empty($hover_image)) {
                    $hover_image = $image;
                }

                $prod_data = array(
                    'template_key'  => $target_tpl,
                    'layout_number' => 1,
                    'category_name' => $category_name,
                    'name'          => $name,
                    'slug'          => $slug,
                    'price'         => $price,
                    'sale_price'    => $sale_price,
                    'image'         => $image,
                    'hover_image'   => $hover_image,
                    'badge_text'    => $badge_text,
                    'unit_label'    => $unit_label,
                    'short_desc'    => $short_desc,
                    'rating'        => $rating,
                    'reviews_count' => $reviews_count,
                    'sort_order'    => $sort_order,
                    'status'        => $status,
                );

                if ($prod_id > 0) {
                    $this->db->where('id', $prod_id)->update('template_products', $prod_data);
                    $this->session->set_flashdata('success', 'Template Product "' . htmlspecialchars($name) . '" updated successfully!');
                } else {
                    $this->db->insert('template_products', $prod_data);
                    $this->session->set_flashdata('success', 'Template Product "' . htmlspecialchars($name) . '" added successfully!');
                }
                $redirect_back('products');
            }

            if ($action === 'delete_product') {
                $prod_id = (int)$this->input->post('product_id');
                if ($prod_id > 0) {
                    $this->db->where('id', $prod_id)->delete('template_products');
                    $this->session->set_flashdata('success', 'Template Product deleted.');
                }
                $redirect_back('products');
            }
        }

        // Fetch data for all 3 templates
        $tpl_data = array();
        foreach (array('template1', 'template2', 'template3') as $tk) {
            $tpl_data[$tk] = array(
                'hero_banners'       => $this->db->where('template_key', $tk)->order_by('sort_order', 'ASC')->get('template_hero_banners')->result(),
                'categories'         => $this->db->where('template_key', $tk)->order_by('sort_order', 'ASC')->get('template_categories')->result(),
                'products'           => $this->db->where('template_key', $tk)->order_by('sort_order', 'ASC')->get('template_products')->result(),
                'categories_tagline' => get_tpl_setting($tk, 1, 'categories_header', 'tagline', 'CURATED COLLECTIONS'),
                'categories_title'   => get_tpl_setting($tk, 1, 'categories_header', 'title', 'Shop by Category'),
                'products_tagline'   => get_tpl_setting($tk, 1, 'products_header', 'tagline', 'TRENDING NOW'),
                'products_title'     => get_tpl_setting($tk, 1, 'products_header', 'title', 'Featured Arrivals'),
            );
        }

        $data['active_tab']     = $active_tpl;
        $data['active_section'] = $active_section;
        $data['tpl_data']       = $tpl_data;

        $this->render('configure_layouts/index', $data, 'Customize Storefront Templates (Hero Banners, Categories & Products)');
    }
}
