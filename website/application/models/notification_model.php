<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class notification_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    private function tid()
    {
        $CI =& get_instance();
        return isset($CI->tenant_id) ? (int) $CI->tenant_id : 1;
    }

    public function get_by_user($user_id)
    {
        $tid = $this->tid();
        $notifications = $this->db->where('tenant_id', $tid)
                                  ->where('user_id', (int) $user_id)
                                  ->order_by('id', 'DESC')
                                  ->get('notifications')
                                  ->result_array();

        // If user has no notifications yet, generate helpful welcome notifications!
        if (empty($notifications)) {
            $this->load->model('setting_model');
            $site_name = $this->setting_model->get('site_name', 'our store');
            $welcome = [
                'tenant_id'  => $tid,
                'user_id'    => (int) $user_id,
                'title'      => "Welcome to {$site_name}!",
                'message'    => "Thank you for joining {$site_name}. Explore our new collection and enjoy free standard shipping on your first order.",
                'type'       => 'account',
                'link'       => 'shop',
                'is_read'    => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('notifications', $welcome);

            $promo = [
                'tenant_id'  => $tid,
                'user_id'    => (int) $user_id,
                'title'      => 'Member Exclusive: 10% Off',
                'message'    => 'Use coupon code WELCOME10 at checkout to save 10% on trending styles this week.',
                'type'       => 'promo',
                'link'       => 'shop',
                'is_read'    => 0,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))
            ];
            $this->db->insert('notifications', $promo);

            $notifications = $this->db->where('tenant_id', $tid)
                                      ->where('user_id', (int) $user_id)
                                      ->order_by('id', 'DESC')
                                      ->get('notifications')
                                      ->result_array();
        }

        return $notifications;
    }

    public function mark_all_read($user_id)
    {
        return $this->db->where('tenant_id', $this->tid())->where('user_id', (int) $user_id)->update('notifications', ['is_read' => 1]);
    }

    public function add($user_id, $title, $message, $type = 'system', $link = NULL)
    {
        return $this->db->insert('notifications', [
            'tenant_id'  => $this->tid(),
            'user_id'    => (int) $user_id,
            'title'      => $title,
            'message'    => $message,
            'type'       => $type,
            'link'       => $link,
            'is_read'    => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
