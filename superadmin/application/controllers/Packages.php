<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Packages extends Superadmin_Controller {

    public function index() {
        // Packages overview
        $data['has_zip'] = class_exists('ZipArchive');
        $data['orders_with_downloads'] = $this->db->where('download_count >', 0)->order_by('id', 'DESC')->get('marketplace_orders')->result();
        $data['all_orders'] = $this->db->order_by('id', 'DESC')->get('marketplace_orders')->result();

        $data['total_downloads'] = (int)$this->db->select_sum('download_count')->get('marketplace_orders')->row()->download_count;

        $this->render('packages/index', $data, 'Software Packages & Downloads');
    }

    /**
     * Generate instant download link for an order
     */
    public function generate_link() {
        $order_id = (int)$this->input->post('order_id');
        $order = $this->db->where('id', $order_id)->get('marketplace_orders')->row();

        if ($order) {
            $base = main_site_url();
            $link = rtrim($base, '/') . '/download.php?order=' . urlencode($order->order_number) . '&token=' . urlencode($order->download_token);
            $this->session->set_flashdata('info', 'Secure package delivery URL: <a href="' . $link . '" target="_blank" class="fw-bold text-dark">' . $link . '</a>');
        }

        redirect(superadmin_url('packages'));
    }
}
