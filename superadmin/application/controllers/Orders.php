<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Orders extends Superadmin_Controller {

    public function index() {
        $filter_plan = $this->input->get('plan', TRUE);
        $filter_status = $this->input->get('status', TRUE);
        $search = $this->input->get('search', TRUE);

        $this->db->select('o.*, p.name as plan_title, t.slug as tenant_slug, t.license_key');
        $this->db->from('saas_payments o');
        $this->db->join('saas_plans p', 'p.id = o.plan_id', 'left');
        $this->db->join('saas_tenants t', 't.id = o.tenant_id', 'left');

        if (!empty($filter_plan)) {
            $this->db->where('o.plan_id', (int)$filter_plan);
        }
        if (!empty($filter_status)) {
            $this->db->where('o.payment_status', $filter_status);
        }
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('o.order_number', $search);
            $this->db->or_like('o.customer_name', $search);
            $this->db->or_like('o.customer_email', $search);
            $this->db->or_like('o.business_name', $search);
            $this->db->or_like('o.transaction_id', $search);
            $this->db->group_end();
        }

        $this->db->order_by('o.id', 'DESC');
        $data['orders'] = $this->db->get()->result();

        $data['plans'] = $this->db->order_by('sort_order', 'ASC')->get('saas_plans')->result();
        $data['filter_plan'] = $filter_plan;
        $data['filter_status'] = $filter_status;
        $data['search'] = $search;

        $rev_row = $this->db->select_sum('amount')->where('payment_status', 'paid')->get('saas_payments')->row();
        $data['total_revenue'] = $rev_row && $rev_row->amount ? (float)$rev_row->amount : 0.00;
        $data['total_orders_count'] = $this->db->count_all('saas_payments');

        $this->render('orders/index', $data, 'SaaS Subscription Payments');
    }

    /**
     * Update payment status & trigger tenant provisioning if marked Paid
     */
    public function update_status() {
        if ($this->input->method() === 'post') {
            $order_id = (int)$this->input->post('order_id');
            $action = $this->input->post('action', TRUE);

            if ($action === 'change_payment_status' && $order_id > 0) {
                $status = $this->input->post('payment_status', TRUE);
                if ($status === 'paid') {
                    require_once dirname(FCPATH) . '/saas_core.php';
                    $res = provision_saas_tenant($order_id, 'ADMIN-APPROVED-' . time(), 'manual');
                    if (!empty($res['success'])) {
                        $this->session->set_flashdata('success', 'Payment #' . $order_id . ' marked as PAID and tenant store provisioned (/website/s/' . $res['slug'] . ').');
                    } else {
                        $this->db->where('id', $order_id)->update('saas_payments', array('payment_status' => 'paid'));
                        $this->session->set_flashdata('success', 'Payment status updated to PAID.');
                    }
                } elseif (in_array($status, array('pending', 'failed', 'cancelled'))) {
                    $this->db->where('id', $order_id)->update('saas_payments', array('payment_status' => $status));
                    $this->session->set_flashdata('success', 'Payment status updated to ' . strtoupper($status));
                }
            }

            redirect(superadmin_url('orders'));
            return;
        }

        redirect(superadmin_url('orders'));
    }

    /**
     * Export Payments to CSV
     */
    public function export() {
        $orders = $this->db->order_by('id', 'DESC')->get('saas_payments')->result_array();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=saas_subscription_payments_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, array('ID', 'Order Number', 'Tenant ID', 'Customer Name', 'Customer Email', 'Phone', 'Business Name', 'Store Slug', 'Plan', 'Template', 'Amount', 'Currency', 'Gateway', 'Transaction ID', 'Payment Status', 'Paid At', 'Created At'));

        foreach ($orders as $row) {
            fputcsv($output, array(
                $row['id'],
                $row['order_number'],
                $row['tenant_id'],
                $row['customer_name'],
                $row['customer_email'],
                $row['customer_phone'],
                $row['business_name'],
                $row['store_slug'],
                $row['plan_name'],
                $row['chosen_template'],
                $row['amount'],
                $row['currency'],
                $row['gateway'],
                $row['transaction_id'],
                $row['payment_status'],
                $row['paid_at'],
                $row['created_at']
            ));
        }

        fclose($output);
        exit;
    }
}
