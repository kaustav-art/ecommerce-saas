<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class attribute_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    protected function tid()
    {
        $ci =& get_instance();
        return isset($ci->tenant_id) ? (int) $ci->tenant_id : 1;
    }

    public function get_all()
    {
        $attributes = $this->db->where('tenant_id', $this->tid())->order_by('name', 'ASC')->get('attributes')->result_array();
        foreach ($attributes as &$attr) {
            $attr['values'] = $this->get_values($attr['id']);
            $attr['values_count'] = count($attr['values']);
            $attr['products_count'] = $this->get_products_count($attr['id']);
            $attr['is_locked'] = ($attr['products_count'] > 0);
        }
        return $attributes;
    }

    public function get_by_id($id)
    {
        $attr = $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->get('attributes')->row_array();
        if ($attr) {
            $attr['values'] = $this->get_values($attr['id']);
            $attr['products_count'] = $this->get_products_count($attr['id']);
            $attr['is_locked'] = ($attr['products_count'] > 0);
        }
        return $attr;
    }

    public function get_products_count($attribute_id)
    {
        $pa_count = $this->db->where('attribute_id', (int) $attribute_id)->count_all_results('product_attributes');
        if ($pa_count > 0) {
            return (int) $pa_count;
        }
        $pvv_count = $this->db->where('attribute_id', (int) $attribute_id)->count_all_results('product_variant_values');
        return (int) $pvv_count;
    }

    public function is_used_in_products($attribute_id)
    {
        return ($this->get_products_count($attribute_id) > 0);
    }

    public function get_values($attribute_id)
    {
        return $this->db->where('attribute_id', (int) $attribute_id)
                        ->order_by('sort_order', 'ASC')
                        ->order_by('value', 'ASC')
                        ->get('attribute_values')
                        ->result_array();
    }

    public function create($data)
    {
        $data['tenant_id'] = $this->tid();
        $this->db->insert('attributes', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->update('attributes', $data);
    }

    public function delete($id)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->delete('attributes');
    }

    public function get_value_by_id($id)
    {
        return $this->db->where('id', (int) $id)->get('attribute_values')->row_array();
    }

    public function add_value($attribute_id, $value, $color_code = NULL, $sort_order = 0)
    {
        $data = [
            'tenant_id'    => $this->tid(),
            'attribute_id' => (int) $attribute_id,
            'value'        => trim($value),
            'color_code'   => $color_code ? trim($color_code) : NULL,
            'sort_order'   => (int) $sort_order
        ];
        $this->db->insert('attribute_values', $data);
        return $this->db->insert_id();
    }

    public function update_value($id, $value, $color_code = NULL, $sort_order = 0)
    {
        $data = [
            'value'      => trim($value),
            'color_code' => $color_code ? trim($color_code) : NULL,
            'sort_order' => (int) $sort_order
        ];
        return $this->db->where('id', (int) $id)->update('attribute_values', $data);
    }

    public function delete_value($id)
    {
        return $this->db->where('id', (int) $id)->delete('attribute_values');
    }

    public function get_product_attributes($product_id)
    {
        return $this->db->select('pa.*, a.name, a.slug, a.type')
                        ->from('product_attributes pa')
                        ->join('attributes a', 'a.id = pa.attribute_id')
                        ->where('pa.product_id', (int) $product_id)
                        ->get()
                        ->result_array();
    }

    public function save_product_attributes($product_id, $attribute_ids)
    {
        $this->db->where('product_id', (int) $product_id)->delete('product_attributes');
        if (!empty($attribute_ids) && is_array($attribute_ids)) {
            $unique_ids = array_unique(array_filter(array_map('intval', $attribute_ids)));
            foreach ($unique_ids as $attr_id) {
                if ($attr_id > 0) {
                    $this->db->insert('product_attributes', [
                        'product_id'   => (int) $product_id,
                        'attribute_id' => $attr_id
                    ]);
                }
            }
        }
    }
}
