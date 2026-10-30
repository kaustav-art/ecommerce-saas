<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class user_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    private function tid()
    {
        $CI =& get_instance();
        return isset($CI->tenant_id) ? (int) $CI->tenant_id : 1;
    }

    public function get_by_email($email)
    {
        $clean_email = strtolower(trim($email));
        return $this->db->where('tenant_id', $this->tid())->where('LOWER(email)', $clean_email)->get('users')->row_array();
    }

    public function get_by_phone($phone)
    {
        $tid = $this->tid();
        $raw = trim($phone);
        if (empty($raw)) return NULL;

        // 1. Direct exact match
        $user = $this->db->where('tenant_id', $tid)->where('phone', $raw)->get('users')->row_array();
        if ($user) return $user;

        // 2. Normalized digits match (last 10 digits or full digits)
        $clean = preg_replace('/[^0-9]/', '', $raw);
        if (strlen($clean) >= 7) {
            $last10 = (strlen($clean) >= 10) ? substr($clean, -10) : $clean;
            $all = $this->db->select('id, tenant_id, first_name, last_name, email, phone, avatar, status')->where('tenant_id', $tid)->get('users')->result_array();
            foreach ($all as $u) {
                if (!empty($u['phone'])) {
                    $u_clean = preg_replace('/[^0-9]/', '', $u['phone']);
                    if ($u_clean === $clean || (strlen($clean) >= 10 && strlen($u_clean) >= 10 && substr($u_clean, -10) === $last10)) {
                        return $u;
                    }
                }
            }
        }

        return NULL;
    }

    public function get_by_email_or_phone($identifier)
    {
        $identifier = trim($identifier);
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return $this->get_by_email($identifier);
        }
        return $this->get_by_phone($identifier);
    }

    public function get_by_id($id)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->get('users')->row_array();
    }

    public function register($data)
    {
        if (!isset($data['tenant_id'])) {
            $data['tenant_id'] = $this->tid();
        }
        $this->db->insert('users', $data);
        return $this->db->insert_id();
    }

    public function create($data)
    {
        return $this->register($data);
    }

    public function update($id, $data)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->update('users', $data);
    }

    public function get_addresses($user_id)
    {
        return $this->db->where('tenant_id', $this->tid())->where('user_id', (int) $user_id)->get('user_addresses')->result_array();
    }

    public function get_default_address($user_id, $type = 'shipping')
    {
        return $this->db->where('tenant_id', $this->tid())
                        ->where('user_id', (int) $user_id)
                        ->where('type', $type)
                        ->order_by('is_default', 'DESC')
                        ->get('user_addresses')
                        ->row_array();
    }

    public function save_address($data)
    {
        $tid = $this->tid();
        if (!empty($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
            $this->db->where('tenant_id', $tid)->where('id', $id)->update('user_addresses', $data);
            return $id;
        } else {
            $data['tenant_id'] = $tid;
            $this->db->insert('user_addresses', $data);
            return $this->db->insert_id();
        }
    }

    public function get_address_by_id($id, $user_id = NULL)
    {
        $this->db->where('tenant_id', $this->tid())->where('id', (int) $id);
        if ($user_id !== NULL) {
            $this->db->where('user_id', (int) $user_id);
        }
        return $this->db->get('user_addresses')->row_array();
    }

    public function set_default_address($id, $user_id)
    {
        $tid = $this->tid();
        $this->db->where('tenant_id', $tid)->where('user_id', (int) $user_id)->update('user_addresses', ['is_default' => 0]);
        return $this->db->where('tenant_id', $tid)->where('id', (int) $id)->where('user_id', (int) $user_id)->update('user_addresses', ['is_default' => 1]);
    }

    public function delete_address($id, $user_id)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->where('user_id', (int) $user_id)->delete('user_addresses');
    }
}
