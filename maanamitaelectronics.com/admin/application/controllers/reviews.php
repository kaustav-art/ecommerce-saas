<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class reviews extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('review_model');
    }

    public function index()
    {
        $this->require_permission('products.manage');

        $filters = [
            'status' => $this->input->get('status'),
            'rating' => $this->input->get('rating'),
            'search' => $this->input->get('q')
        ];

        $page     = max(1, (int) $this->input->get('page'));
        $per_page = 20;
        $offset   = ($page - 1) * $per_page;

        $total_reviews = $this->review_model->count_all($filters);
        $reviews       = $this->review_model->get_all($filters, $per_page, $offset);

        $data = [
            'title'          => 'Product Reviews | Admin',
            'active_menu'    => 'products',
            'active_submenu' => 'reviews',
            'reviews'        => $reviews,
            'total_reviews'  => $total_reviews,
            'filters'        => $filters,
            'page'           => $page,
            'total_pages'    => ceil($total_reviews / $per_page)
        ];

        $this->render('reviews/index', $data);
    }

    public function status($id, $status)
    {
        $this->require_permission('products.manage');
        if (in_array($status, ['approved', 'rejected', 'pending'])) {
            $this->review_model->update_status($id, $status);
            $this->session->set_flashdata('success', 'Review status updated to ' . ucfirst($status) . '.');
        }
        redirect('reviews');
    }

    public function delete($id)
    {
        $this->require_permission('products.manage');
        $this->review_model->delete($id);
        $this->session->set_flashdata('success', 'Review deleted successfully.');
        redirect('reviews');
    }
}
