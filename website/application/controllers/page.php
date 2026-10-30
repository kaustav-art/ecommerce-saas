<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class page extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    public function about()
    {
        $data = [
            'title'       => 'About Us - ' . $this->site_name,
            'active_page' => 'about'
        ];
        $this->render('pages/about', $data);
    }

    public function contact()
    {
        if ($this->input->method() === 'post') {
            $this->session->set_flashdata('success', 'Thank you! Your message has been sent (Template Demo Mode).');
            redirect('contact');
        }

        $data = [
            'title'       => 'Contact Us - ' . $this->site_name,
            'active_page' => 'contact'
        ];
        $this->render('pages/contact', $data);
    }

    public function faq()
    {
        $data = [
            'title'       => 'Frequently Asked Questions - ' . $this->site_name,
            'active_page' => 'faq'
        ];
        $this->render('pages/faq', $data);
    }

    public function terms()
    {
        $data = [
            'title'       => 'Terms & Conditions - ' . $this->site_name,
            'active_page' => 'terms'
        ];
        $this->render('pages/terms', $data);
    }

    public function privacy()
    {
        $data = [
            'title'       => 'Privacy Policy - ' . $this->site_name,
            'active_page' => 'privacy'
        ];
        $this->render('pages/privacy', $data);
    }

    public function returns_policy()
    {
        $data = [
            'title'       => 'Return & Refund Policy - ' . $this->site_name,
            'active_page' => 'returns_policy'
        ];
        $this->render('pages/returns', $data);
    }

    public function subscribe_newsletter()
    {
        if ($this->input->is_ajax_request()) {
            $this->json_response(['success' => true, 'message' => 'Thank you for subscribing to our newsletter (Template Demo Mode)!']);
            return;
        }
        $this->session->set_flashdata('success', 'Thank you for subscribing to our newsletter (Template Demo Mode)!');
        redirect($_SERVER['HTTP_REFERER'] ?? 'home');
    }
}
