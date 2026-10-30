<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class flash_sales extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('marketing_model');
        $this->load->model('product_model');
    }

    public function index()
    {
        $this->require_permission('settings.manage');

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('title', 'Campaign Title', 'required|trim');
            $this->form_validation->set_rules('start_time', 'Start Date/Time', 'required|trim');
            $this->form_validation->set_rules('end_time', 'End Date/Time', 'required|trim');

            if ($this->form_validation->run() === TRUE) {
                $id         = $this->input->post('id');
                $title      = $this->input->post('title', TRUE);
                $start_time = $this->input->post('start_time');
                $end_time   = $this->input->post('end_time');
                $disc_pct   = (float) $this->input->post('discount_percent');

                $now        = time();
                $start_ts   = strtotime($start_time);
                $end_ts     = strtotime($end_time);

                if (empty($id)) {
                    // 1. Create New Flash Sale
                    if ($end_ts <= $start_ts) {
                        $this->session->set_flashdata('error', 'End Date/Time must be after Start Date/Time.');
                        redirect('flash_sales');
                        return;
                    }

                    // Check if another running or scheduled campaign with the same title already exists
                    $conflict = $this->marketing_model->get_active_or_scheduled_campaign_by_title($title);
                    if ($conflict) {
                        $st = ucfirst($conflict['computed_status'] ?? $conflict['status']);
                        $this->session->set_flashdata('error', 'A ' . $st . ' flash sale campaign with the title "' . html_escape($title) . '" already exists.');
                        redirect('flash_sales');
                        return;
                    }

                    // Auto status: if start_time is now or in the past, it starts immediately (running)
                    $status = ($start_ts <= $now) ? 'running' : 'scheduled';

                    $uploaded_banner = $this->upload_image_file('banner_file', 'collections', 'fs');
                    if ($uploaded_banner === false) {
                        $this->session->set_flashdata('error', 'Banner upload error: ' . $this->upload->display_errors('', ''));
                        redirect('flash_sales');
                        return;
                    }
                    $banner = $uploaded_banner ?: ($this->input->post('current_banner', TRUE) ?: null);

                    $slug = $this->marketing_model->generate_unique_flash_sale_slug($title);

                    $data = [
                        'title'            => $title,
                        'slug'             => $slug,
                        'discount_percent' => $disc_pct,
                        'start_time'       => date('Y-m-d H:i:s', $start_ts),
                        'end_time'         => date('Y-m-d H:i:s', $end_ts),
                        'status'           => $status
                    ];
                    if ($banner !== null) {
                        $data['banner'] = $banner;
                    }

                    $product_ids = $this->input->post('product_ids') ?: [];
                    $sale_prices = $this->input->post('sale_prices') ?: [];

                    $this->marketing_model->save_flash_sale($data, null, $product_ids, $sale_prices);
                    $this->session->set_flashdata('success', $status === 'running' 
                        ? 'Flash sale campaign created and is now Running.' 
                        : 'Flash sale campaign created and Scheduled.');
                    redirect('flash_sales');
                    return;
                } else {
                    // 2. Edit Existing Flash Sale
                    $existing = $this->marketing_model->get_flash_sale($id);
                    if (!$existing) {
                        $this->session->set_flashdata('error', 'Campaign not found.');
                        redirect('flash_sales');
                        return;
                    }

                    $cur_status = $this->marketing_model->compute_status($existing);

                    if ($cur_status === 'ended') {
                        $this->session->set_flashdata('error', 'This flash sale campaign has ended and cannot be edited.');
                        redirect('flash_sales');
                        return;
                    }

                    if ($cur_status === 'terminated') {
                        $this->session->set_flashdata('error', 'This flash sale campaign was terminated and cannot be edited.');
                        redirect('flash_sales');
                        return;
                    }

                    // Start Date/Time is ALWAYS LOCKED once saved
                    $start_time = $existing['start_time'];
                    $exist_start_ts = strtotime($existing['start_time']);
                    $exist_end_ts   = strtotime($existing['end_time']);

                    if ($cur_status === 'running') {
                        // Running: Title, Discount, Products, Banner are LOCKED. End Date/Time is EXTEND ONLY.
                        if ($end_ts <= $exist_end_ts) {
                            $this->session->set_flashdata('error', 'For running campaigns, End Date/Time can only be extended beyond the current end date (' . date('M d, Y H:i', $exist_end_ts) . ').');
                            redirect('flash_sales');
                            return;
                        }

                        $data = [
                            'end_time' => date('Y-m-d H:i:s', $end_ts),
                            'status'   => 'running'
                        ];

                        $this->marketing_model->save_flash_sale($data, $id);
                        $this->session->set_flashdata('success', 'Running campaign end date/time extended successfully.');
                        redirect('flash_sales');
                        return;
                    } else {
                        // Scheduled: Title, Discount, Products, Banner, End Date/Time are Editable. Start Date/Time is LOCKED.
                        if ($end_ts <= $exist_start_ts) {
                            $this->session->set_flashdata('error', 'End Date/Time must be after the start date/time (' . date('M d, Y H:i', $exist_start_ts) . ').');
                            redirect('flash_sales');
                            return;
                        }

                        $conflict = $this->marketing_model->get_active_or_scheduled_campaign_by_title($title, $id);
                        if ($conflict) {
                            $st = ucfirst($conflict['computed_status'] ?? $conflict['status']);
                            $this->session->set_flashdata('error', 'Another ' . $st . ' flash sale campaign with the title "' . html_escape($title) . '" already exists.');
                            redirect('flash_sales');
                            return;
                        }

                        $uploaded_banner = $this->upload_image_file('banner_file', 'collections', 'fs');
                        if ($uploaded_banner === false) {
                            $this->session->set_flashdata('error', 'Banner upload error: ' . $this->upload->display_errors('', ''));
                            redirect('flash_sales');
                            return;
                        }

                        $banner = null;
                        if ($uploaded_banner) {
                            $banner = $uploaded_banner;
                        } elseif ($this->input->post('current_banner')) {
                            $banner = trim($this->input->post('current_banner', TRUE));
                        }

                        $status = ($exist_start_ts <= $now) ? 'running' : 'scheduled';
                        $slug   = $this->marketing_model->generate_unique_flash_sale_slug($title, $id);

                        $data = [
                            'title'            => $title,
                            'slug'             => $slug,
                            'discount_percent' => $disc_pct,
                            'start_time'       => $start_time, // preserved
                            'end_time'         => date('Y-m-d H:i:s', $end_ts),
                            'status'           => $status
                        ];
                        if ($banner !== null) {
                            $data['banner'] = $banner;
                        }

                        $product_ids = $this->input->post('product_ids') ?: [];
                        $sale_prices = $this->input->post('sale_prices') ?: [];

                        $this->marketing_model->save_flash_sale($data, $id, $product_ids, $sale_prices);
                        $this->session->set_flashdata('success', 'Scheduled flash sale campaign updated successfully.');
                        redirect('flash_sales');
                        return;
                    }
                }
            }
        }

        $data = [
            'title'          => 'Flash Sales & Promotional Campaigns | Admin',
            'active_menu'    => 'marketing',
            'active_submenu' => 'flash_sales',
            'flash_sales'    => $this->marketing_model->get_flash_sales(),
            'products'       => $this->product_model->get_all(30)
        ];
        $this->render('flash_sales/index', $data);
    }

    public function terminate($id)
    {
        $this->require_permission('settings.manage');
        $existing = $this->marketing_model->get_flash_sale($id);
        if (!$existing) {
            $this->session->set_flashdata('error', 'Campaign not found.');
            redirect('flash_sales');
            return;
        }

        $status = $this->marketing_model->compute_status($existing);
        if ($status === 'ended') {
            $this->session->set_flashdata('error', 'This campaign has already ended.');
            redirect('flash_sales');
            return;
        }
        if ($status === 'terminated') {
            $this->session->set_flashdata('error', 'This campaign is already terminated.');
            redirect('flash_sales');
            return;
        }

        $this->marketing_model->terminate_flash_sale($id);
        $this->session->set_flashdata('success', 'Flash sale campaign terminated.');
        redirect('flash_sales');
    }

    public function delete($id)
    {
        // Delete option removed in favor of termination
        $this->terminate($id);
    }
}
