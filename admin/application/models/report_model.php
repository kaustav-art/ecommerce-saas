<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class report_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    protected function tid()
    {
        $ci =& get_instance();
        return isset($ci->tenant_id) ? (int) $ci->tenant_id : 1;
    }

    public function get_sales_report($start_date = null, $end_date = null)
    {
        if (!$start_date) $start_date = date('Y-m-01');
        if (!$end_date)   $end_date   = date('Y-m-d');

        return $this->db->select("DATE(created_at) as sale_date, COUNT(id) as total_orders, SUM(total_amount) as gross_sales, SUM(tax_amount) as total_tax, SUM(shipping_fee) as total_shipping, SUM(discount_amount) as total_discounts")
                        ->from('orders')
                        ->where('tenant_id', $this->tid())
                        ->where('payment_status', 'paid')
                        ->where("DATE(created_at) >= '$start_date'")
                        ->where("DATE(created_at) <= '$end_date'")
                        ->group_by("DATE(created_at)")
                        ->order_by("DATE(created_at)", 'DESC')
                        ->get()
                        ->result_array();
    }

    public function get_order_report($start_date = null, $end_date = null)
    {
        if (!$start_date) $start_date = date('Y-m-01');
        if (!$end_date)   $end_date   = date('Y-m-d');

        return $this->db->select("order_status, COUNT(id) as count, SUM(total_amount) as total_value")
                        ->from('orders')
                        ->where('tenant_id', $this->tid())
                        ->where("DATE(created_at) >= '$start_date'")
                        ->where("DATE(created_at) <= '$end_date'")
                        ->group_by("order_status")
                        ->get()
                        ->result_array();
    }

    public function get_product_report()
    {
        return $this->db->select("p.id, p.title, p.sku, p.price, p.stock_quantity, c.name as category_name, COALESCE(SUM(oi.quantity), 0) as units_sold, COALESCE(SUM(oi.total), 0) as total_revenue")
                        ->from('products p')
                        ->join('categories c', 'c.id = p.category_id', 'left')
                        ->join('order_items oi', 'oi.product_id = p.id', 'left')
                        ->where('p.tenant_id', $this->tid())
                        ->group_by('p.id')
                        ->order_by('units_sold', 'DESC')
                        ->limit(25)
                        ->get()
                        ->result_array();
    }

    public function get_customer_report()
    {
        return $this->db->select("u.id, u.first_name, u.last_name, u.email, u.phone, COUNT(o.id) as total_orders, COALESCE(SUM(o.total_amount), 0) as total_spent, MAX(o.created_at) as last_order_date")
                        ->from('users u')
                        ->join('orders o', 'o.user_id = u.id AND o.payment_status = "paid"', 'left')
                        ->where('u.tenant_id', $this->tid())
                        ->group_by('u.id')
                        ->order_by('total_spent', 'DESC')
                        ->limit(25)
                        ->get()
                        ->result_array();
    }

    public function get_tax_report($start_date = null, $end_date = null)
    {
        if (!$start_date) $start_date = date('Y-m-01');
        if (!$end_date)   $end_date   = date('Y-m-d');

        return $this->db->select("DATE_FORMAT(created_at, '%Y-%m') as period, COUNT(id) as orders_count, SUM(subtotal) as taxable_amount, SUM(tax_amount) as tax_collected")
                        ->from('orders')
                        ->where('tenant_id', $this->tid())
                        ->where('payment_status', 'paid')
                        ->where("DATE(created_at) >= '$start_date'")
                        ->where("DATE(created_at) <= '$end_date'")
                        ->group_by("DATE_FORMAT(created_at, '%Y-%m')")
                        ->order_by("period", 'DESC')
                        ->get()
                        ->result_array();
    }

    public function get_inventory_report()
    {
        return $this->db->select("p.id, p.title, p.sku, p.price, p.stock_quantity, p.stock_status, p.low_stock_threshold, c.name as category_name, (p.price * p.stock_quantity) as inventory_value")
                        ->from('products p')
                        ->join('categories c', 'c.id = p.category_id', 'left')
                        ->where('p.tenant_id', $this->tid())
                        ->order_by('p.stock_quantity', 'ASC')
                        ->get()
                        ->result_array();
    }
}
