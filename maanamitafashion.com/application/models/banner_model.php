<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class banner_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    public function get_active_banners()
    {
        $CI =& get_instance();
        $tid = isset($CI->tenant_id) ? (int) $CI->tenant_id : 1;
        return $this->db->where('tenant_id', $tid)
                        ->where('status', 'active')
                        ->order_by('sort_order', 'ASC')
                        ->order_by('id', 'DESC')
                        ->get('banners')
                        ->result_array();
    }
}
