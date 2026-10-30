<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class review_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    protected function tid()
    {
        $ci =& get_instance();
        return isset($ci->tenant_id) ? (int) $ci->tenant_id : 1;
    }

    public function get_all($filters = [], $limit = 20, $offset = 0)
    {
        $this->db->select('r.*, p.title as product_title, p.slug as product_slug, p.main_image as product_image')
                 ->from('reviews r')
                 ->join('products p', 'p.id = r.product_id', 'left')
                 ->where('r.tenant_id', $this->tid());

        if (!empty($filters['status'])) {
            $this->db->where('r.status', $filters['status']);
        }
        if (!empty($filters['rating'])) {
            $this->db->where('r.rating', (int)$filters['rating']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start()
                     ->like('r.customer_name', $filters['search'])
                     ->or_like('r.customer_email', $filters['search'])
                     ->or_like('p.title', $filters['search'])
                     ->group_end();
        }

        return $this->db->order_by('r.id', 'DESC')
                        ->limit($limit, $offset)
                        ->get()
                        ->result_array();
    }

    public function count_all($filters = [])
    {
        $this->db->from('reviews r')
                 ->join('products p', 'p.id = r.product_id', 'left')
                 ->where('r.tenant_id', $this->tid());
        if (!empty($filters['status'])) {
            $this->db->where('r.status', $filters['status']);
        }
        return $this->db->count_all_results();
    }

    public function update_status($id, $status)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->update('reviews', ['status' => $status]);
    }

    public function delete($id)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->delete('reviews');
    }
}
