<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class reports extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('report_model');
        $this->load->model('setting_model');
    }

    public function index()
    {
        redirect('reports/sales');
    }

    public function sales()
    {
        $this->require_permission('dashboard.view');

        $start_date = $this->input->get('start_date') ?: date('Y-m-01');
        $end_date   = $this->input->get('end_date') ?: date('Y-m-d');

        $sales_data = $this->report_model->get_sales_report($start_date, $end_date);

        $data = [
            'title'           => 'Sales Analytics Report | Admin',
            'active_menu'     => 'reports',
            'active_submenu'  => 'sales_report',
            'sales_data'      => $sales_data,
            'start_date'      => $start_date,
            'end_date'        => $end_date,
            'currency_symbol' => $this->setting_model->get('currency_symbol', '$')
        ];
        $this->render('reports/sales', $data);
    }

    public function orders()
    {
        $this->require_permission('dashboard.view');

        $start_date = $this->input->get('start_date') ?: date('Y-m-01');
        $end_date   = $this->input->get('end_date') ?: date('Y-m-d');

        $orders_data = $this->report_model->get_order_report($start_date, $end_date);

        $data = [
            'title'           => 'Order Status Report | Admin',
            'active_menu'     => 'reports',
            'active_submenu'  => 'order_report',
            'orders_data'     => $orders_data,
            'start_date'      => $start_date,
            'end_date'        => $end_date,
            'currency_symbol' => $this->setting_model->get('currency_symbol', '$')
        ];
        $this->render('reports/orders', $data);
    }

    public function products()
    {
        $this->require_permission('dashboard.view');

        $products_data = $this->report_model->get_product_report();

        $data = [
            'title'           => 'Best Selling Products Report | Admin',
            'active_menu'     => 'reports',
            'active_submenu'  => 'product_report',
            'products_data'   => $products_data,
            'currency_symbol' => $this->setting_model->get('currency_symbol', '$')
        ];
        $this->render('reports/products', $data);
    }

    public function customers()
    {
        $this->require_permission('dashboard.view');

        $customers_data = $this->report_model->get_customer_report();

        $data = [
            'title'           => 'Top Customer Spending Report | Admin',
            'active_menu'     => 'reports',
            'active_submenu'  => 'customer_report',
            'customers_data'  => $customers_data,
            'currency_symbol' => $this->setting_model->get('currency_symbol', '$')
        ];
        $this->render('reports/customers', $data);
    }

    public function tax()
    {
        $this->require_permission('dashboard.view');

        $start_date = $this->input->get('start_date') ?: date('Y-01-01');
        $end_date   = $this->input->get('end_date') ?: date('Y-m-d');

        $tax_data = $this->report_model->get_tax_report($start_date, $end_date);

        $data = [
            'title'           => 'Tax Collection Report | Admin',
            'active_menu'     => 'reports',
            'active_submenu'  => 'tax_report',
            'tax_data'        => $tax_data,
            'start_date'      => $start_date,
            'end_date'        => $end_date,
            'currency_symbol' => $this->setting_model->get('currency_symbol', '$')
        ];
        $this->render('reports/tax', $data);
    }

    public function inventory()
    {
        $this->require_permission('dashboard.view');

        $inventory_data = $this->report_model->get_inventory_report();

        $data = [
            'title'           => 'Inventory Valuation Report | Admin',
            'active_menu'     => 'reports',
            'active_submenu'  => 'inventory_report',
            'inventory_data'  => $inventory_data,
            'currency_symbol' => $this->setting_model->get('currency_symbol', '$')
        ];
        $this->render('reports/inventory', $data);
    }
}
