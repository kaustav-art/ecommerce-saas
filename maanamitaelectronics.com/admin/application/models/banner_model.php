<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class banner_model extends CI_Model {

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
        return $this->db->where('tenant_id', $this->tid())
                        ->order_by('sort_order', 'ASC')
                        ->order_by('id', 'DESC')
                        ->get('banners')
                        ->result_array();
    }

    public function get_by_id($id)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->get('banners')->row_array();
    }

    public function create($data)
    {
        $data['tenant_id'] = $this->tid();
        $this->db->insert('banners', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->update('banners', $data);
    }

    public function delete($id)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->delete('banners');
    }
}
