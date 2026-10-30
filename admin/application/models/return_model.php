<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class return_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    protected function tid()
    {
        $ci =& get_instance();
        return isset($ci->tenant_id) ? (int) $ci->tenant_id : 1;
    }

    public function get_all($filters = [])
    {
        $this->db->select('r.*, o.order_number, o.total_amount, o.customer_name, o.customer_email, o.customer_phone')
                 ->from('order_returns r')
                 ->join('orders o', 'o.id = r.order_id', 'left')
                 ->where('r.tenant_id', $this->tid());

        if (!empty($filters['status'])) {
            $this->db->where('r.status', $filters['status']);
        }
        if (!empty($filters['type'])) {
            $this->db->where('r.type', $filters['type']);
        }
        return $this->db->order_by('r.id', 'DESC')->get()->result_array();
    }

    public function get_by_id($id)
    {
        return $this->db->select('r.*, o.order_number, o.total_amount, o.customer_name, o.customer_email, o.customer_phone')
                        ->from('order_returns r')
                        ->join('orders o', 'o.id = r.order_id', 'left')
                        ->where('r.tenant_id', $this->tid())
                        ->where('r.id', (int) $id)
                        ->get()
                        ->row_array();
    }

    public function update_status($id, $status, $admin_notes = null)
    {
        $data = ['status' => $status];
        if ($admin_notes !== null) {
            $data['admin_notes'] = $admin_notes;
        }
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->update('order_returns', $data);
    }
}
