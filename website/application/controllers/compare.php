<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class compare extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $all_products = Static_template_data::get('products');
        $products     = array_slice($all_products, 0, 3);
        $all_specs    = [];

        foreach ($products as $p) {
            if (!empty($p['specifications'])) {
                foreach ($p['specifications'] as $s) {
                    if (!in_array($s['spec_name'], $all_specs)) {
                        $all_specs[] = $s['spec_name'];
                    }
                }
            }
        }

        $data = [
            'title'       => 'Product Comparison - ' . $this->site_name,
            'active_page' => 'compare',
            'products'    => $products,
            'all_specs'   => $all_specs
        ];

        $this->render('compare/index', $data);
    }

    public function add($product_id = 1)
    {
        $this->session->set_flashdata('success', 'Product added to comparison (Template Demo Mode).');
        redirect('compare');
    }

    public function remove($product_id = 1)
    {
        $this->session->set_flashdata('success', 'Product removed from comparison (Template Demo Mode).');
        redirect('compare');
    }

    public function clear()
    {
        $this->session->set_flashdata('success', 'Comparison list cleared (Template Demo Mode).');
        redirect('compare');
    }
}
