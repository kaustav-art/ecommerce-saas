<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class variant_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    protected function tid()
    {
        $ci =& get_instance();
        return isset($ci->tenant_id) ? (int) $ci->tenant_id : 1;
    }

    public function get_by_product($product_id)
    {
        $variants = $this->db->where('product_id', (int) $product_id)
                             ->order_by('id', 'ASC')
                             ->get('product_variants')
                             ->result_array();

        foreach ($variants as &$v) {
            $v['values'] = $this->db->select('pvv.*, a.name as attribute_name, a.slug as attribute_slug, av.value as attribute_value, av.color_code')
                                    ->from('product_variant_values pvv')
                                    ->join('attributes a', 'a.id = pvv.attribute_id')
                                    ->join('attribute_values av', 'av.id = pvv.attribute_value_id')
                                    ->where('pvv.variant_id', $v['id'])
                                    ->get()
                                    ->result_array();
        }
        return $variants;
    }

    public function get_by_id($id)
    {
        $variant = $this->db->where('id', (int) $id)->get('product_variants')->row_array();
        if ($variant) {
            $variant['values'] = $this->db->select('pvv.*, a.name as attribute_name, a.slug as attribute_slug, av.value as attribute_value, av.color_code')
                                          ->from('product_variant_values pvv')
                                          ->join('attributes a', 'a.id = pvv.attribute_id')
                                          ->join('attribute_values av', 'av.id = pvv.attribute_value_id')
                                          ->where('pvv.variant_id', $variant['id'])
                                          ->get()
                                          ->result_array();
        }
        return $variant;
    }

    public function create($data, $attr_val_ids = [])
    {
        $data['tenant_id'] = $this->tid();
        $this->db->insert('product_variants', $data);
        $variant_id = $this->db->insert_id();

        if ($variant_id && !empty($attr_val_ids)) {
            foreach ($attr_val_ids as $attr_id => $val_id) {
                if (is_array($val_id)) {
                    foreach ($val_id as $single_vid) {
                        if (!empty($single_vid)) {
                            $this->db->insert('product_variant_values', [
                                'variant_id'         => $variant_id,
                                'attribute_id'       => (int) $attr_id,
                                'attribute_value_id' => (int) $single_vid
                            ]);
                        }
                    }
                } elseif (!empty($val_id)) {
                    $this->db->insert('product_variant_values', [
                        'variant_id'         => $variant_id,
                        'attribute_id'       => (int) $attr_id,
                        'attribute_value_id' => (int) $val_id
                    ]);
                }
            }
        }
        return $variant_id;
    }

    public function update($id, $data, $attr_val_ids = null)
    {
        $res = $this->db->where('id', (int) $id)->update('product_variants', $data);

        if ($attr_val_ids !== null) {
            $this->db->where('variant_id', (int) $id)->delete('product_variant_values');
            foreach ($attr_val_ids as $attr_id => $val_id) {
                if (!empty($val_id)) {
                    $this->db->insert('product_variant_values', [
                        'variant_id'         => (int) $id,
                        'attribute_id'       => (int) $attr_id,
                        'attribute_value_id' => (int) $val_id
                    ]);
                }
            }
        }
        return $res;
    }

    public function delete($id)
    {
        $variant = $this->get_by_id($id);
        $product_id = $variant ? (int) $variant['product_id'] : null;
        $this->db->where('variant_id', (int) $id)->delete('product_variant_values');
        $res = $this->db->where('id', (int) $id)->delete('product_variants');
        if ($product_id) {
            $this->sync_product_stock($product_id);
        }
        return $res;
    }

    public function delete_multiple($variant_ids, $product_id = null)
    {
        if (empty($variant_ids) || !is_array($variant_ids)) {
            return false;
        }
        $clean_ids = array_map('intval', array_filter($variant_ids));
        if (empty($clean_ids)) {
            return false;
        }

        $this->db->where_in('variant_id', $clean_ids)->delete('product_variant_values');
        $res = $this->db->where_in('id', $clean_ids)->delete('product_variants');

        if ($product_id) {
            $this->sync_product_stock($product_id);
        }
        return $res;
    }

    public function sync_product_stock($product_id)
    {
        $product_id = (int) $product_id;
        $variants = $this->db->where('product_id', $product_id)->get('product_variants')->result_array();
        if (!empty($variants)) {
            $total_stock = 0;
            foreach ($variants as $v) {
                $total_stock += (int) $v['stock_quantity'];
            }
            $this->db->where('id', $product_id)->update('products', [
                'stock_quantity' => $total_stock,
                'stock_status'   => ($total_stock > 0 ? 'in_stock' : 'out_of_stock'),
                'product_type'   => 'variable'
            ]);
            return $total_stock;
        }
        return null;
    }

    public function get_grouped_by_product($product_id)
    {
        $variants = $this->get_by_product($product_id);
        $groups = [];

        // 1. Identify which attribute is the multi-select dimension for this product
        $multi_attr_id   = null;
        $multi_attr_name = 'Size';
        $multi_attr_slug = 'size';

        // Check product_attributes first
        $assigned_attrs = $this->db->select('a.id, a.name, a.slug, a.type')
                                   ->from('product_attributes pa')
                                   ->join('attributes a', 'a.id = pa.attribute_id')
                                   ->where('pa.product_id', (int) $product_id)
                                   ->get()
                                   ->result_array();

        foreach ($assigned_attrs as $aa) {
            $is_multi = (in_array(strtolower($aa['type'] ?? ''), ['multiple_select', 'multiselect', 'multiple'])
                         || strcasecmp($aa['slug'], 'size') === 0 || strcasecmp($aa['name'], 'size') === 0
                         || strcasecmp($aa['slug'], 'storage') === 0 || strcasecmp($aa['name'], 'storage') === 0);
            if ($is_multi) {
                $multi_attr_id   = (int) $aa['id'];
                $multi_attr_name = $aa['name'];
                $multi_attr_slug = $aa['slug'];
                break;
            }
        }

        // 2. If not found in product_attributes, detect from variants' values
        if (!$multi_attr_id && !empty($variants)) {
            foreach ($variants as $v) {
                if (!empty($v['values'])) {
                    foreach ($v['values'] as $val) {
                        $is_multi = (strcasecmp($val['attribute_slug'], 'size') === 0 || strcasecmp($val['attribute_name'], 'size') === 0
                                     || strcasecmp($val['attribute_slug'], 'storage') === 0 || strcasecmp($val['attribute_name'], 'storage') === 0);
                        if ($is_multi) {
                            $multi_attr_id   = (int) $val['attribute_id'];
                            $multi_attr_name = $val['attribute_name'];
                            $multi_attr_slug = $val['attribute_slug'];
                            break 2;
                        }
                    }
                }
            }
        }

        foreach ($variants as $v) {
            $size_info = null;
            $non_size_attrs = [];

            if (!empty($v['values'])) {
                foreach ($v['values'] as $val) {
                    $is_multi_val = false;
                    if ($multi_attr_id) {
                        if ((int)$val['attribute_id'] === $multi_attr_id) {
                            $is_multi_val = true;
                        }
                    } elseif (strcasecmp($val['attribute_slug'], 'size') === 0 || strcasecmp($val['attribute_name'], 'size') === 0) {
                        $is_multi_val = true;
                    } elseif (strcasecmp($val['attribute_slug'], 'storage') === 0 || strcasecmp($val['attribute_name'], 'storage') === 0) {
                        $is_multi_val = true;
                    }

                    if ($is_multi_val) {
                        $size_info = [
                            'variant_id'   => (int) $v['id'],
                            'size_id'      => (int) $val['attribute_value_id'],
                            'size_name'    => $val['attribute_value'],
                            'attr_id'      => (int) $val['attribute_id'],
                            'attr_name'    => $val['attribute_name'],
                            'sku'          => $v['sku'],
                            'price'        => (float) $v['price'],
                            'sale_price'   => !empty($v['sale_price']) ? (float) $v['sale_price'] : null,
                            'stock'        => (int) $v['stock_quantity'],
                            'stock_status' => $v['stock_status']
                        ];
                    } else {
                        $non_size_attrs[] = [
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

            // Derive base title and SKU without size/storage suffix
            $base_title = $v['title'];
            if ($size_info && !empty($size_info['size_name'])) {
                $base_title = preg_replace('/\s*\/\s*' . preg_quote($size_info['size_name'], '/') . '$/i', '', $base_title);
            }
            if (empty($base_title)) {
                $base_title = $v['title'];
            }

            $base_sku = $v['sku'];
            if ($size_info && !empty($size_info['size_name'])) {
                $clean_sname = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $size_info['size_name']));
                $base_sku = preg_replace('/-' . preg_quote($size_info['size_name'], '/') . '$/i', '', $base_sku);
                if (!empty($clean_sname)) {
                    $base_sku = preg_replace('/-' . preg_quote($clean_sname, '/') . '$/i', '', $base_sku);
                }
            }

            if (!empty($non_size_attrs)) {
                usort($non_size_attrs, function($a, $b) {
                    return $a['attribute_id'] - $b['attribute_id'];
                });
                $non_size_key = 'attr_' . implode('_', array_map(function($a) {
                    return $a['attribute_id'] . '-' . $a['attribute_value_id'];
                }, $non_size_attrs));
            } elseif ($size_info !== null) {
                $non_size_key = 'multi_group_' . md5($base_sku . '_' . $base_title);
            } else {
                $non_size_key = 'single_' . $v['id'];
            }

            if (!isset($groups[$non_size_key])) {
                $groups[$non_size_key] = [
                    'group_key'             => $non_size_key,
                    'product_id'            => (int) $v['product_id'],
                    'primary_id'            => (int) $v['id'],
                    'title'                 => $base_title,
                    'base_sku'              => $base_sku,
                    'price'                 => (float) $v['price'],
                    'sale_price'            => !empty($v['sale_price']) ? (float) $v['sale_price'] : null,
                    'image'                 => $v['image'],
                    'gallery_images'        => $v['gallery_images'],
                    'max_purchase_quantity' => isset($v['max_purchase_quantity']) ? (int) $v['max_purchase_quantity'] : 5,
                    'highlights'            => !empty($v['highlights']) ? $v['highlights'] : null,
                    'specifications'        => !empty($v['specifications']) ? $v['specifications'] : null,
                    'non_size_attrs'        => $non_size_attrs,
                    'multi_attr_id'         => $multi_attr_id,
                    'multi_attr_name'       => $multi_attr_name,
                    'multi_attr_slug'       => $multi_attr_slug,
                    'has_sizes'             => false,
                    'sizes'                 => [],
                    'total_stock'           => 0,
                    'in_stock_count'        => 0,
                    'out_of_stock_count'    => 0,
                    'variant_ids'           => []
                ];
            } else {
                if (empty($groups[$non_size_key]['highlights']) && !empty($v['highlights'])) {
                    $groups[$non_size_key]['highlights'] = $v['highlights'];
                }
                if (empty($groups[$non_size_key]['specifications']) && !empty($v['specifications'])) {
                    $groups[$non_size_key]['specifications'] = $v['specifications'];
                }
                if (empty($groups[$non_size_key]['max_purchase_quantity']) && !empty($v['max_purchase_quantity'])) {
                    $groups[$non_size_key]['max_purchase_quantity'] = (int) $v['max_purchase_quantity'];
                }
            }

            $groups[$non_size_key]['variant_ids'][] = (int) $v['id'];
            $groups[$non_size_key]['total_stock'] += (int) $v['stock_quantity'];

            if ($size_info !== null) {
                $groups[$non_size_key]['has_sizes'] = true;
                $groups[$non_size_key]['sizes'][] = $size_info;
                if ($size_info['stock'] > 0) {
                    $groups[$non_size_key]['in_stock_count']++;
                } else {
                    $groups[$non_size_key]['out_of_stock_count']++;
                }
            } else {
                if ((int) $v['stock_quantity'] > 0) {
                    $groups[$non_size_key]['in_stock_count']++;
                } else {
                    $groups[$non_size_key]['out_of_stock_count']++;
                }
            }
        }

        return array_values($groups);
    }

    public function get_group_by_variant_id($product_id, $variant_id)
    {
        $groups = $this->get_grouped_by_product($product_id);
        foreach ($groups as $grp) {
            if (in_array((int) $variant_id, $grp['variant_ids'], true)) {
                return $grp;
            }
        }
        return null;
    }
}
