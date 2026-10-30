<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class inventory extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('inventory_model');
        $this->load->model('product_model');
    }

    public function index()
    {
        $this->require_permission('products.view');

        $filters = [
            'search'    => $this->input->get('q'),
            'low_stock' => $this->input->get('low_stock') ? 1 : NULL,
            'status'    => $this->input->get('status')
        ];

        $page     = max(1, (int) $this->input->get('page'));
        $per_page = 20;
        $offset   = ($page - 1) * $per_page;

        $total_items = $this->inventory_model->count_stock_items($filters);
        $items       = $this->inventory_model->get_stock_items($filters, $per_page, $offset);

        $data = [
            'title'          => 'Inventory Management | Admin',
            'active_menu'    => 'inventory',
            'active_submenu' => 'stock_list',
            'items'          => $items,
            'total_items'    => $total_items,
            'filters'        => $filters,
            'page'           => $page,
            'total_pages'    => ceil($total_items / $per_page)
        ];

        $this->render('inventory/index', $data);
    }

    public function low_stock()
    {
        $this->require_permission('products.view');

        $filters = ['low_stock' => 1];
        $items   = $this->inventory_model->get_stock_items($filters, 50, 0);

        $data = [
            'title'          => 'Low Stock Alerts | Admin',
            'active_menu'    => 'inventory',
            'active_submenu' => 'low_stock',
            'items'          => $items,
            'filters'        => $filters
        ];

        $this->render('inventory/low_stock', $data);
    }

    public function adjust()
    {
        $this->require_permission('products.manage');

        if ($this->input->method() === 'post') {
            $product_id = (int) $this->input->post('product_id');
            $variant_id = $this->input->post('variant_id') ? (int) $this->input->post('variant_id') : NULL;
            $type       = $this->input->post('type', TRUE);
            $quantity   = (int) $this->input->post('quantity');
            $reason     = $this->input->post('reason', TRUE) ?: 'Manual Inventory Adjustment';
            $admin_id   = $this->current_admin['id'];

            if ($product_id && $quantity > 0) {
                $this->inventory_model->adjust_stock($product_id, $variant_id, $type, $quantity, $reason, $admin_id);
                $this->session->set_flashdata('success', 'Stock adjustment recorded successfully.');
            }
            redirect('inventory');
        }

        $data = [
            'title'          => 'Stock Adjustment | Admin',
            'active_menu'    => 'inventory',
            'active_submenu' => 'stock_adjust',
            'products'       => $this->product_model->get_all()
        ];
        $this->render('inventory/adjust', $data);
    }

    public function history()
    {
        $this->require_permission('products.view');

        $data = [
            'title'          => 'Stock Adjustment History | Admin',
            'active_menu'    => 'inventory',
            'active_submenu' => 'stock_history',
            'history'        => $this->inventory_model->get_history(50, 0)
        ];
        $this->render('inventory/history', $data);
    }
}
