<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class marketing extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('marketing_model');
    }

    public function newsletter()
    {
        $this->require_permission('settings.manage');

        $data = [
            'title'          => 'Newsletter Subscribers | Admin',
            'active_menu'    => 'marketing',
            'active_submenu' => 'newsletter',
            'subscribers'    => $this->marketing_model->get_subscribers()
        ];
        $this->render('marketing/newsletter', $data);
    }

    public function delete_subscriber($id)
    {
        $this->require_permission('settings.manage');
        $this->marketing_model->delete_subscriber($id);
        $this->session->set_flashdata('success', 'Subscriber removed.');
        redirect('marketing/newsletter');
    }
}
