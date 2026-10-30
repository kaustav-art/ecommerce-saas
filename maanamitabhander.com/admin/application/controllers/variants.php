<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class variants extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('variant_model');
        $this->load->model('product_model');
        $this->load->model('attribute_model');
    }

    /**
     * Display list of variants for a product
     */
    public function product($product_id)
    {
        $this->require_permission('products.manage');
        $product = $this->product_model->get_by_id($product_id);
        if (!$product) {
            show_404();
        }

        // Support direct POST to variants/product/{id} for backward compatibility
        if ($this->input->method() === 'post') {
            $this->_save_variant($product);
            return;
        }

        $variants       = $this->variant_model->get_by_product($product_id);
        $variant_groups = $this->variant_model->get_grouped_by_product($product_id);
        $attributes     = $this->attribute_model->get_all();

        $data = [
            'title'          => 'Variants for ' . html_escape($product['title']) . ' | Admin',
            'active_menu'    => 'products',
            'active_submenu' => 'products_list',
            'product'        => $this->product_model->get_by_id($product_id), // fresh data with updated stock
            'variants'       => $variants,
            'variant_groups' => $variant_groups,
            'attributes'     => $attributes
        ];
        $this->render('variants/product', $data);
    }

    /**
     * Add new variant(s) page
     */
    public function add($product_id)
    {
        $this->require_permission('products.manage');
        $product = $this->product_model->get_by_id($product_id);
        if (!$product) {
            show_404();
        }

        if ($this->input->method() === 'post') {
            $this->_save_variant($product);
            return;
        }

        $attributes = $this->attribute_model->get_all();
        $assigned_product_attrs = $this->attribute_model->get_product_attributes($product_id);

        $active_multi_attr = null;
        $active_multi_attr_id = null;

        // 1. Detect from assigned product attributes
        foreach ($assigned_product_attrs as $apa) {
            $is_multi = (in_array(strtolower($apa['type'] ?? ''), ['multiple_select', 'multiselect', 'multiple'])
                         || strcasecmp($apa['slug'], 'size') === 0 || strcasecmp($apa['name'], 'size') === 0
                         || strcasecmp($apa['slug'], 'storage') === 0 || strcasecmp($apa['name'], 'storage') === 0);
            if ($is_multi) {
                $active_multi_attr_id = (int)$apa['attribute_id'];
                break;
            }
        }

        // 2. Detect from existing product variants if not in product_attributes
        if (!$active_multi_attr_id) {
            $product_variants = $this->variant_model->get_by_product($product_id);
            if (!empty($product_variants)) {
                foreach ($product_variants as $pv) {
                    if (!empty($pv['values'])) {
                        foreach ($pv['values'] as $pv_val) {
                            $is_multi = (in_array(strtolower($pv_val['type'] ?? ''), ['multiple_select', 'multiselect', 'multiple'])
                                         || strcasecmp($pv_val['attribute_slug'], 'size') === 0 || strcasecmp($pv_val['attribute_name'], 'size') === 0
                                         || strcasecmp($pv_val['attribute_slug'], 'storage') === 0 || strcasecmp($pv_val['attribute_name'], 'storage') === 0);
                            if ($is_multi) {
                                $active_multi_attr_id = (int)$pv_val['attribute_id'];
                                break 2;
                            }
                        }
                    }
                }
            }
        }

        // 3. Fallback to any attribute with type multiple_select or slug storage/size
        if (!$active_multi_attr_id && !empty($attributes)) {
            foreach ($attributes as $attr) {
                $is_multi = (in_array(strtolower($attr['type'] ?? ''), ['multiple_select', 'multiselect', 'multiple'])
                             || strcasecmp($attr['slug'], 'storage') === 0 || strcasecmp($attr['name'], 'storage') === 0
                             || strcasecmp($attr['slug'], 'size') === 0 || strcasecmp($attr['name'], 'size') === 0);
                if ($is_multi) {
                    $active_multi_attr_id = (int)$attr['id'];
                    break;
                }
            }
        }

        if ($active_multi_attr_id && !empty($attributes)) {
            foreach ($attributes as $attr) {
                if ((int)$attr['id'] === $active_multi_attr_id) {
                    $active_multi_attr = $attr;
                    break;
                }
            }
        }

        // Base highlights from product if available
        $product_highlights = !empty($product['highlights']) ? (json_decode($product['highlights'], true) ?: []) : [];

        // Base specifications from product if available
        $product_specs = !empty($product['specifications']) ? $product['specifications'] : [];

        $data = [
            'title'             => 'Add Product Variant | ' . html_escape($product['title']) . ' | Admin',
            'active_menu'       => 'products',
            'active_submenu'    => 'products_list',
            'product'           => $product,
            'variant'           => [],
            'is_edit'           => false,
            'form_action'       => site_url('variants/add/' . $product_id),
            'attributes'        => $attributes,
            'active_multi_attr' => $active_multi_attr,
            'highlights'        => $product_highlights,
            'specifications'    => $product_specs
        ];

        $this->render('variants/form', $data);
    }

    /**
     * Edit variant or variant group page
     */
    public function edit($product_id, $variant_id)
    {
        $this->require_permission('products.manage');
        $product = $this->product_model->get_by_id($product_id);
        if (!$product) {
            show_404();
        }

        if ($this->input->method() === 'post') {
            $this->_save_variant($product, (int) $variant_id);
            return;
        }

        // Look up variant group first
        $variant_data = $this->variant_model->get_group_by_variant_id($product_id, $variant_id);

        if (!$variant_data) {
            // Fallback: look up single variant
            $single = $this->variant_model->get_by_id($variant_id);
            if (!$single || (int)$single['product_id'] !== (int)$product_id) {
                $this->session->set_flashdata('error', 'Variant not found.');
                redirect('variants/product/' . $product_id);
                return;
            }

            $variant_data = [
                'primary_id'            => (int) $single['id'],
                'id'                    => (int) $single['id'],
                'variant_ids'           => [(int) $single['id']],
                'title'                 => $single['title'],
                'base_sku'              => $single['sku'],
                'price'                 => (float) $single['price'],
                'sale_price'            => !empty($single['sale_price']) ? (float) $single['sale_price'] : null,
                'total_stock'           => (int) $single['stock_quantity'],
                'stock_quantity'        => (int) $single['stock_quantity'],
                'max_purchase_quantity' => !empty($single['max_purchase_quantity']) ? (int) $single['max_purchase_quantity'] : 5,
                'image'                 => $single['image'],
                'gallery_images'        => $single['gallery_images'],
                'highlights'            => $single['highlights'] ?? null,
                'specifications'        => $single['specifications'] ?? null,
                'values'                => $single['values'] ?? [],
                'non_size_attrs'        => [],
                'sizes'                 => []
            ];

            $fallback_multi_id = null;
            $assigned_attrs = $this->attribute_model->get_product_attributes($product_id);
            foreach ($assigned_attrs as $aa) {
                if (in_array(strtolower($aa['type'] ?? ''), ['multiple_select', 'multiselect', 'multiple'])
                    || strcasecmp($aa['slug'], 'size') === 0 || strcasecmp($aa['name'], 'size') === 0
                    || strcasecmp($aa['slug'], 'storage') === 0 || strcasecmp($aa['name'], 'storage') === 0) {
                    $fallback_multi_id = (int)$aa['attribute_id'];
                    break;
                }
            }

            if (!empty($single['values'])) {
                foreach ($single['values'] as $val) {
                    $is_multi = false;
                    if ($fallback_multi_id && (int)$val['attribute_id'] === $fallback_multi_id) {
                        $is_multi = true;
                    } elseif (strcasecmp($val['attribute_slug'], 'size') === 0 || strcasecmp($val['attribute_name'], 'size') === 0
                              || strcasecmp($val['attribute_slug'], 'storage') === 0 || strcasecmp($val['attribute_name'], 'storage') === 0) {
                        $is_multi = true;
                    }
                    if ($is_multi) {
                        $variant_data['sizes'][] = [
                            'variant_id'   => (int) $single['id'],
                            'size_id'      => (int) $val['attribute_value_id'],
                            'size_name'    => $val['attribute_value'],
                            'sku'          => $single['sku'],
                            'price'        => (float) $single['price'],
                            'stock'        => (int) $single['stock_quantity'],
                            'stock_status' => $single['stock_status']
                        ];
                    } else {
                        $variant_data['non_size_attrs'][] = [
                            'attribute_id'       => (int) $val['attribute_id'],
                            'attribute_name'     => $val['attribute_name'],
                            'attribute_slug'     => $val['attribute_slug'],
                            'attribute_value_id' => (int) $val['attribute_value_id'],
                            'attribute_value'    => $val['attribute_value'],
                            'color_code'         => $val['color_code'] ?? null
                        ];
                    }
                }
            }
        }

        $attributes = $this->attribute_model->get_all();

        $active_multi_attr = null;
        $active_multi_attr_id = !empty($variant_data['multi_attr_id']) ? (int)$variant_data['multi_attr_id'] : null;

        if (!$active_multi_attr_id) {
            $assigned_product_attrs = $this->attribute_model->get_product_attributes($product_id);
            foreach ($assigned_product_attrs as $apa) {
                $is_multi = (in_array(strtolower($apa['type'] ?? ''), ['multiple_select', 'multiselect', 'multiple'])
                             || strcasecmp($apa['slug'], 'size') === 0 || strcasecmp($apa['name'], 'size') === 0
                             || strcasecmp($apa['slug'], 'storage') === 0 || strcasecmp($apa['name'], 'storage') === 0);
                if ($is_multi) {
                    $active_multi_attr_id = (int)$apa['attribute_id'];
                    break;
                }
            }
        }

        if ($active_multi_attr_id && !empty($attributes)) {
            foreach ($attributes as $attr) {
                if ((int)$attr['id'] === $active_multi_attr_id) {
                    $active_multi_attr = $attr;
                    break;
                }
            }
        }

        // Decode highlights and specifications
        $highlights = [];
        if (!empty($variant_data['highlights'])) {
            $highlights = is_array($variant_data['highlights']) ? $variant_data['highlights'] : (json_decode($variant_data['highlights'], true) ?: []);
        } elseif (!empty($product['highlights'])) {
            $highlights = json_decode($product['highlights'], true) ?: [];
        }

        $specifications = [];
        if (!empty($variant_data['specifications'])) {
            $specifications = is_array($variant_data['specifications']) ? $variant_data['specifications'] : (json_decode($variant_data['specifications'], true) ?: []);
        } elseif (!empty($product['specifications'])) {
            $specifications = $product['specifications'];
        }

        $data = [
            'title'             => 'Edit Variant: ' . html_escape($variant_data['title']) . ' | Admin',
            'active_menu'       => 'products',
            'active_submenu'    => 'products_list',
            'product'           => $product,
            'variant'           => $variant_data,
            'is_edit'           => true,
            'form_action'       => site_url('variants/edit/' . $product_id . '/' . $variant_id),
            'attributes'        => $attributes,
            'active_multi_attr' => $active_multi_attr,
            'highlights'        => $highlights,
            'specifications'    => $specifications
        ];

        $this->render('variants/form', $data);
    }

    /**
     * Shared save handler for adding or editing variants
     */
    private function _save_variant($product, $edit_variant_id = null)
    {
        $product_id = (int) $product['id'];

        $this->form_validation->set_rules('sku', 'Variant SKU', 'required|trim');
        $this->form_validation->set_rules('price', 'Variant Price', 'required|numeric');
        $this->form_validation->set_rules('stock_quantity', 'Stock Quantity', 'required|numeric');

        if ($this->form_validation->run() === TRUE) {
            $id = $this->input->post('id') ?: $edit_variant_id;

            // Handle variant main image file upload
            $uploaded_image = $this->upload_image_file('image_file', 'products', 'var');
            if ($uploaded_image === false) {
                $this->session->set_flashdata('error', 'Variant image upload error: ' . $this->upload->display_errors('', ''));
                redirect($id ? ('variants/edit/' . $product_id . '/' . $id) : ('variants/add/' . $product_id));
                return;
            }
            $image_path = $uploaded_image ?: ($this->input->post('current_image', TRUE) ?: $product['main_image']);

            // Handle variant gallery images file upload
            $new_gallery = $this->upload_multiple_images('gallery_files', 'products', 'var_gal');
            if ($this->input->post('gallery_submitted')) {
                $existing_gallery = $this->input->post('existing_gallery') ?: [];
            } else {
                $existing_gallery = $this->input->post('existing_gallery');
                if ($existing_gallery === null && !empty($id)) {
                    $cur_var = $this->variant_model->get_by_id($id);
                    $existing_gallery = !empty($cur_var['gallery_images']) ? (json_decode($cur_var['gallery_images'], true) ?: []) : [];
                } else {
                    $existing_gallery = $existing_gallery ?: [];
                }
            }
            $final_gallery = array_merge((array) $existing_gallery, $new_gallery);
            $final_gallery = array_values(array_unique(array_filter($final_gallery)));
            $gallery_json  = !empty($final_gallery) ? json_encode($final_gallery) : NULL;

            // Highlights processing
            $highlights = [];
            if ($this->input->post('enable_highlights')) {
                $h_keys = $this->input->post('highlight_keys') ?: [];
                $h_vals = $this->input->post('highlight_values') ?: [];
                foreach ($h_keys as $idx => $k) {
                    $k = trim($k);
                    $v = isset($h_vals[$idx]) ? trim($h_vals[$idx]) : '';
                    if ($k !== '' || $v !== '') {
                        $highlights[] = ['key' => $k, 'value' => $v];
                    }
                }
            }
            $highlights_json = !empty($highlights) ? json_encode($highlights) : NULL;

            // Specifications processing
            $specs = [];
            if ($this->input->post('enable_specifications')) {
                $spec_names  = $this->input->post('spec_names') ?: [];
                $spec_values = $this->input->post('spec_values') ?: [];
                foreach ($spec_names as $idx => $name) {
                    $name = trim($name);
                    $val  = isset($spec_values[$idx]) ? trim($spec_values[$idx]) : '';
                    if ($name !== '' || $val !== '') {
                        $specs[] = ['name' => $name, 'value' => $val];
                    }
                }
            }
            $specs_json = !empty($specs) ? json_encode($specs) : NULL;

            $max_purchase_quantity = $this->input->post('max_purchase_quantity') ? max(1, (int) $this->input->post('max_purchase_quantity')) : 5;

            $attr_vals = $this->input->post('attr_vals') ?: [];
            $size_vals = $this->input->post('size_vals');

            // Look up Multi-Select attribute definition (Storage, Size, etc.)
            $all_attributes = $this->attribute_model->get_all();
            $active_multi_id = (int) $this->input->post('active_multi_attr_id');
            $size_attr_id = null;
            $size_attr_name = 'Option';
            $size_map = [];

            if ($active_multi_id > 0) {
                foreach ($all_attributes as $attr) {
                    if ((int)$attr['id'] === $active_multi_id) {
                        $size_attr_id = (int) $attr['id'];
                        $size_attr_name = $attr['name'];
                        if (!empty($attr['values'])) {
                            foreach ($attr['values'] as $val) {
                                $size_map[$val['id']] = $val['value'];
                            }
                        }
                        break;
                    }
                }
            }

            // Fallback: check product_attributes
            if (!$size_attr_id) {
                $assigned_product_attrs = $this->attribute_model->get_product_attributes($product_id);
                foreach ($assigned_product_attrs as $apa) {
                    $is_multi = (in_array(strtolower($apa['type'] ?? ''), ['multiple_select', 'multiselect', 'multiple'])
                                 || strcasecmp($apa['slug'], 'size') === 0 || strcasecmp($apa['name'], 'size') === 0
                                 || strcasecmp($apa['slug'], 'storage') === 0 || strcasecmp($apa['name'], 'storage') === 0);
                    if ($is_multi) {
                        $size_attr_id = (int) $apa['attribute_id'];
                        $size_attr_name = $apa['name'];
                        foreach ($all_attributes as $attr) {
                            if ((int)$attr['id'] === $size_attr_id && !empty($attr['values'])) {
                                foreach ($attr['values'] as $val) {
                                    $size_map[$val['id']] = $val['value'];
                                }
                                break;
                            }
                        }
                        break;
                    }
                }
            }

            // Fallback: check all attributes for multiple_select or size/storage
            if (!$size_attr_id) {
                foreach ($all_attributes as $attr) {
                    $is_multi = (in_array(strtolower($attr['type'] ?? ''), ['multiple_select', 'multiselect', 'multiple'])
                                 || strcasecmp($attr['slug'], 'storage') === 0 || strcasecmp($attr['name'], 'storage') === 0
                                 || strcasecmp($attr['slug'], 'size') === 0 || strcasecmp($attr['name'], 'size') === 0);
                    if ($is_multi) {
                        $size_attr_id = (int) $attr['id'];
                        $size_attr_name = $attr['name'];
                        if (!empty($attr['values'])) {
                            foreach ($attr['values'] as $val) {
                                $size_map[$val['id']] = $val['value'];
                            }
                        }
                        break;
                    }
                }
            }

            $base_sku   = strtoupper(trim($this->input->post('sku', TRUE)));
            $base_title = trim($this->input->post('title', TRUE)) ?: ($product['title'] . ' Variant');
            $price      = (float) $this->input->post('price');
            $sale_price = $this->input->post('sale_price') ? (float) $this->input->post('sale_price') : NULL;
            $stock      = (int) $this->input->post('stock_quantity');

            $group_variant_ids_str = $this->input->post('group_variant_ids');
            $group_variant_ids     = !empty($group_variant_ids_str) ? array_filter(array_map('intval', explode(',', $group_variant_ids_str))) : [];
            $size_stocks           = $this->input->post('size_stock') ?: [];
            $size_sale_prices      = $this->input->post('size_sale_price') ?: [];
            $selected_sizes        = is_array($size_vals) ? array_filter($size_vals) : (!empty($size_vals) ? [$size_vals] : []);

            if (!empty($group_variant_ids)) {
                // --- EDIT GROUPED VARIANT (MULTIPLE SIZES) ---
                $existing_variants = $this->db->where_in('id', $group_variant_ids)->get('product_variants')->result_array();
                $existing_by_size  = [];
                foreach ($existing_variants as $ev) {
                    $pv_val = $this->db->where('variant_id', $ev['id'])->where('attribute_id', $size_attr_id)->get('product_variant_values')->row_array();
                    if ($pv_val) {
                        $existing_by_size[$pv_val['attribute_value_id']] = $ev;
                    }
                }

                $processed_var_ids = [];
                if ($size_attr_id && !empty($selected_sizes)) {
                    foreach ($selected_sizes as $s_id) {
                        $s_name = $size_map[$s_id] ?? '';
                        $v_sku = $base_sku;
                        if (!empty($s_name)) {
                            $clean_sname = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $s_name));
                            if (!empty($clean_sname) && !preg_match('/-' . preg_quote($clean_sname, '/') . '$/i', $v_sku)) {
                                $v_sku .= '-' . $clean_sname;
                            }
                        }
                        $v_title = $base_title;
                        if (!empty($s_name) && !preg_match('/\/\s*' . preg_quote($s_name, '/') . '$/i', $v_title)) {
                            $v_title .= ' / ' . $s_name;
                        }
                        $s_stock = isset($size_stocks[$s_id]) ? max(0, (int) $size_stocks[$s_id]) : $stock;
                        $s_sale_price = (isset($size_sale_prices[$s_id]) && $size_sale_prices[$s_id] !== '') ? (float) $size_sale_prices[$s_id] : $sale_price;

                        $v_data = [
                            'product_id'            => (int) $product_id,
                            'title'                 => $v_title,
                            'sku'                   => $v_sku,
                            'price'                 => $price,
                            'sale_price'            => $s_sale_price,
                            'stock_quantity'        => $s_stock,
                            'max_purchase_quantity' => $max_purchase_quantity,
                            'stock_status'          => $s_stock > 0 ? 'in_stock' : 'out_of_stock',
                            'image'                 => $image_path,
                            'gallery_images'        => $gallery_json,
                            'highlights'            => $highlights_json,
                            'specifications'        => $specs_json
                        ];
                        $v_attrs = $attr_vals;
                        $v_attrs[$size_attr_id] = $s_id;

                        if (isset($existing_by_size[$s_id])) {
                            $this->variant_model->update($existing_by_size[$s_id]['id'], $v_data, $v_attrs);
                            $processed_var_ids[] = (int) $existing_by_size[$s_id]['id'];
                        } else {
                            $new_v_id = $this->variant_model->create($v_data, $v_attrs);
                            $processed_var_ids[] = (int) $new_v_id;
                        }
                    }

                    // Remove sizes that were unchecked from the group
                    $removed_var_ids = array_diff($group_variant_ids, $processed_var_ids);
                    if (!empty($removed_var_ids)) {
                        $this->variant_model->delete_multiple($removed_var_ids, $product_id);
                    }
                } else {
                    // Group had sizes but now no sizes selected - update primary variant
                    $primary_id = reset($group_variant_ids);
                    $data = [
                        'product_id'            => (int) $product_id,
                        'title'                 => $base_title,
                        'sku'                   => $base_sku,
                        'price'                 => $price,
                        'sale_price'            => $sale_price,
                        'stock_quantity'        => $stock,
                        'max_purchase_quantity' => $max_purchase_quantity,
                        'stock_status'          => $stock > 0 ? 'in_stock' : 'out_of_stock',
                        'image'                 => $image_path,
                        'gallery_images'        => $gallery_json,
                        'highlights'            => $highlights_json,
                        'specifications'        => $specs_json
                    ];
                    $this->variant_model->update($primary_id, $data, $attr_vals);

                    $other_ids = array_diff($group_variant_ids, [$primary_id]);
                    if (!empty($other_ids)) {
                        $this->variant_model->delete_multiple($other_ids, $product_id);
                    }
                }

                $this->variant_model->sync_product_stock($product_id);
                $this->session->set_flashdata('success', 'Variant group updated successfully.');

            } elseif (!empty($id)) {
                // --- EDIT EXISTING SINGLE VARIANT ---
                $single_size_id = !empty($size_vals) ? (is_array($size_vals) ? reset($size_vals) : $size_vals) : null;
                if ($size_attr_id && !empty($single_size_id)) {
                    $attr_vals[$size_attr_id] = $single_size_id;
                    if (isset($size_stocks[$single_size_id])) {
                        $stock = max(0, (int) $size_stocks[$single_size_id]);
                    }
                    if (isset($size_sale_prices[$single_size_id]) && $size_sale_prices[$single_size_id] !== '') {
                        $sale_price = (float) $size_sale_prices[$single_size_id];
                    }
                }

                $data = [
                    'product_id'            => (int) $product_id,
                    'title'                 => $base_title,
                    'sku'                   => $base_sku,
                    'price'                 => $price,
                    'sale_price'            => $sale_price,
                    'stock_quantity'        => $stock,
                    'max_purchase_quantity' => $max_purchase_quantity,
                    'stock_status'          => $stock > 0 ? 'in_stock' : 'out_of_stock',
                    'image'                 => $image_path,
                    'gallery_images'        => $gallery_json,
                    'highlights'            => $highlights_json,
                    'specifications'        => $specs_json
                ];

                $this->variant_model->update($id, $data, $attr_vals);
                $this->variant_model->sync_product_stock($product_id);
                $this->session->set_flashdata('success', 'Variant updated successfully.');

            } else {
                // --- ADD NEW VARIANT(S) ---
                if ($size_attr_id && !empty($selected_sizes)) {
                    // SIZES SELECTED WITH INDIVIDUAL STOCKS
                    $created_count = 0;
                    foreach ($selected_sizes as $s_id) {
                        $s_name = $size_map[$s_id] ?? '';

                        // Append option to SKU if not already present
                        $v_sku = $base_sku;
                        if (!empty($s_name)) {
                            $clean_sname = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $s_name));
                            if (!empty($clean_sname) && !preg_match('/-' . preg_quote($clean_sname, '/') . '$/i', $v_sku)) {
                                $v_sku .= '-' . $clean_sname;
                            }
                        }

                        // Append option to title if not already present
                        $v_title = $base_title;
                        if (!empty($s_name) && !preg_match('/\/\s*' . preg_quote($s_name, '/') . '$/i', $v_title)) {
                            $v_title .= ' / ' . $s_name;
                        }

                        $s_stock = isset($size_stocks[$s_id]) ? max(0, (int) $size_stocks[$s_id]) : $stock;
                        $s_sale_price = (isset($size_sale_prices[$s_id]) && $size_sale_prices[$s_id] !== '') ? (float) $size_sale_prices[$s_id] : $sale_price;

                        $v_data = [
                            'product_id'            => (int) $product_id,
                            'title'                 => $v_title,
                            'sku'                   => $v_sku,
                            'price'                 => $price,
                            'sale_price'            => $s_sale_price,
                            'stock_quantity'        => $s_stock,
                            'max_purchase_quantity' => $max_purchase_quantity,
                            'stock_status'          => $s_stock > 0 ? 'in_stock' : 'out_of_stock',
                            'image'                 => $image_path,
                            'gallery_images'        => $gallery_json,
                            'highlights'            => $highlights_json,
                            'specifications'        => $specs_json
                        ];

                        $v_attrs = $attr_vals;
                        $v_attrs[$size_attr_id] = $s_id;

                        // Check if existing variant with this SKU exists
                        $existing = $this->db->where('product_id', (int) $product_id)
                                             ->where('sku', $v_sku)
                                             ->get('product_variants')
                                             ->row_array();
                        if ($existing) {
                            $this->variant_model->update($existing['id'], $v_data, $v_attrs);
                        } else {
                            $this->variant_model->create($v_data, $v_attrs);
                        }
                        $created_count++;
                    }
                    $this->variant_model->sync_product_stock($product_id);
                    $this->session->set_flashdata('success', $created_count . ' variants created successfully with managed stock by ' . strtolower($size_attr_name) . '!');
                } else {
                    // NO SIZE ATTRIBUTE SELECTED
                    $data = [
                        'product_id'            => (int) $product_id,
                        'title'                 => $base_title,
                        'sku'                   => $base_sku,
                        'price'                 => $price,
                        'sale_price'            => $sale_price,
                        'stock_quantity'        => $stock,
                        'max_purchase_quantity' => $max_purchase_quantity,
                        'stock_status'          => $stock > 0 ? 'in_stock' : 'out_of_stock',
                        'image'                 => $image_path,
                        'gallery_images'        => $gallery_json,
                        'highlights'            => $highlights_json,
                        'specifications'        => $specs_json
                    ];

                    $this->variant_model->create($data, $attr_vals);
                    $this->variant_model->sync_product_stock($product_id);
                    $this->session->set_flashdata('success', 'Variant created successfully.');
                }
            }

            // Ensure assigned attributes in product_attributes are kept in sync
            $assigned_attr_ids = array_keys($attr_vals);
            if (!empty($selected_sizes) && $size_attr_id) {
                $assigned_attr_ids[] = $size_attr_id;
            }
            if (!empty($assigned_attr_ids)) {
                $existing_attrs = array_column($this->attribute_model->get_product_attributes($product_id), 'attribute_id');
                $all_attrs = array_unique(array_merge($existing_attrs, $assigned_attr_ids));
                $this->attribute_model->save_product_attributes($product_id, $all_attrs);
            }

            // Ensure product type is marked as variable
            $this->product_model->update($product_id, ['product_type' => 'variable']);

            redirect('variants/product/' . $product_id);
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($edit_variant_id ? ('variants/edit/' . $product_id . '/' . $edit_variant_id) : ('variants/add/' . $product_id));
        }
    }

    public function delete($product_id, $variant_id)
    {
        $this->require_permission('products.manage');
        $this->variant_model->delete($variant_id);
        $this->variant_model->sync_product_stock($product_id);
        $this->session->set_flashdata('success', 'Variant deleted.');
        redirect('variants/product/' . $product_id);
    }

    public function delete_group($product_id)
    {
        $this->require_permission('products.manage');
        $var_ids_str = $this->input->post('variant_ids') ?: $this->input->get('variant_ids');
        $var_ids = array_filter(array_map('intval', explode(',', $var_ids_str)));

        if (!empty($var_ids)) {
            $this->variant_model->delete_multiple($var_ids, $product_id);
            $this->session->set_flashdata('success', 'Variant group (' . count($var_ids) . ' sizes) deleted successfully.');
        } else {
            $this->session->set_flashdata('error', 'No variant IDs provided.');
        }
        redirect('variants/product/' . $product_id);
    }

    public function update_size_stocks()
    {
        $this->require_permission('products.manage');
        $product_id  = (int) $this->input->post('product_id');
        $stocks      = $this->input->post('stocks'); // array: variant_id => qty
        $sale_prices = $this->input->post('sale_prices'); // optional array: variant_id => sale_price

        if (!$product_id || empty($stocks) || !is_array($stocks)) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
            exit;
        }

        foreach ($stocks as $v_id => $qty) {
            $int_v_id = (int) $v_id;
            $int_qty  = max(0, (int) $qty);
            $status   = $int_qty > 0 ? 'in_stock' : 'out_of_stock';
            $update_fields = [
                'stock_quantity' => $int_qty,
                'stock_status'   => $status,
                'updated_at'     => date('Y-m-d H:i:s')
            ];
            if ($sale_prices && is_array($sale_prices) && isset($sale_prices[$int_v_id]) && $sale_prices[$int_v_id] !== '') {
                $update_fields['sale_price'] = (float) $sale_prices[$int_v_id];
            }
            $this->db->where('id', $int_v_id)
                     ->where('product_id', $product_id)
                     ->update('product_variants', $update_fields);
        }

        $total_stock = $this->variant_model->sync_product_stock($product_id);

        header('Content-Type: application/json');
        echo json_encode([
            'status'      => 'success',
            'message'     => 'Stock quantities and prices updated successfully!',
            'total_stock' => $total_stock
        ]);
        exit;
    }

    public function delete_gallery_image()
    {
        $this->require_permission('products.manage');
        $variant_id = (int) $this->input->post('variant_id');
        $image_path = $this->input->post('image_path', TRUE);

        if (!$variant_id || empty($image_path)) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
            exit;
        }

        $variant = $this->variant_model->get_by_id($variant_id);
        if (!$variant) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Variant not found']);
            exit;
        }

        $gallery = json_decode($variant['gallery_images'], true) ?: [];
        $updated_gallery = [];
        $found = false;
        foreach ($gallery as $img) {
            if ($img === $image_path) {
                $found = true;
            } else {
                $updated_gallery[] = $img;
            }
        }

        if ($found) {
            $this->variant_model->update($variant_id, [
                'gallery_images' => !empty($updated_gallery) ? json_encode(array_values($updated_gallery)) : NULL,
                'updated_at'     => date('Y-m-d H:i:s')
            ]);

            if (strpos($image_path, 'products/var_gal_') === 0) {
                $file_path = admin_storefront_fs_path('assets/images/' . $image_path);
                if (file_exists($file_path)) {
                    @unlink($file_path);
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode([
            'status'          => 'success',
            'remaining_count' => count($updated_gallery),
            'message'         => 'Variant gallery image removed'
        ]);
        exit;
    }
}
