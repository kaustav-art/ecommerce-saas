<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class customer_group_model extends CI_Model {

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
        $tid = $this->tid();
        $groups = $this->db->where('tenant_id', $tid)->order_by('name', 'ASC')->get('customer_groups')->result_array();
        foreach ($groups as &$g) {
            $g['customers_count'] = $this->db->where('tenant_id', $tid)->where('customer_group_id', $g['id'])->count_all_results('users');
        }
        return $groups;
    }

    public function get_by_id($id)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->get('customer_groups')->row_array();
    }

    public function create($data)
    {
        $data['tenant_id'] = $this->tid();
        $this->db->insert('customer_groups', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->update('customer_groups', $data);
    }

    public function delete($id)
    {
        $tid = $this->tid();
        $this->db->where('tenant_id', $tid)->where('customer_group_id', (int) $id)->update('users', ['customer_group_id' => NULL]);
        return $this->db->where('tenant_id', $tid)->where('id', (int) $id)->delete('customer_groups');
    }
}
