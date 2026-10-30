<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class banners extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('banner_model');
    }

    public function index()
    {
        $this->require_permission('banners.manage');

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('title', 'Banner Title', 'required|trim');

            if ($this->form_validation->run() === TRUE) {
                $id = $this->input->post('id');

                $data = [
                    'title'       => $this->input->post('title', TRUE),
                    'subtitle'    => $this->input->post('subtitle', TRUE),
                    'button_text' => $this->input->post('button_text', TRUE) ?: 'Explore Collection',
                    'button_link' => $this->input->post('button_link', TRUE) ?: 'shop',
                    'sort_order'  => (int) $this->input->post('sort_order'),
                    'status'      => $this->input->post('status', TRUE) ?: 'active'
                ];

                // Check for file upload
                $uploaded_banner = $this->upload_image_file('image_file', 'slider', 'slider');
                if ($uploaded_banner === false) {
                    $this->session->set_flashdata('error', 'Banner image upload error: ' . $this->upload->display_errors('', ''));
                    redirect('banners');
                    return;
                }

                if ($uploaded_banner) {
                    $data['image'] = $uploaded_banner;
                } elseif ($this->input->post('current_image')) {
                    $data['image'] = trim($this->input->post('current_image', TRUE));
                } else {
                    $data['image'] = 'slider/slider-women1.jpg';
                }

                if (!empty($id)) {
                    $this->banner_model->update($id, $data);
                    $this->session->set_flashdata('success', 'Banner updated successfully.');
                } else {
                    if (empty($data['image'])) {
                        $data['image'] = 'slider/slider-women1.jpg'; // default fallback
                    }
                    $this->banner_model->create($data);
                    $this->session->set_flashdata('success', 'Banner created successfully.');
                }
                redirect('banners');
            }
        }

        $data = [
            'title'          => 'Banner & Slider Management | Admin',
            'active_menu'    => 'products',
            'active_submenu' => 'banners',
            'banners'        => $this->banner_model->get_all()
        ];

        $this->render('banners/index', $data);
    }

    public function delete($id)
    {
        $this->require_permission('banners.manage');
        $this->banner_model->delete($id);
        $this->session->set_flashdata('success', 'Banner deleted successfully.');
        redirect('banners');
    }

    public function toggle($id)
    {
        $this->require_permission('banners.manage');
        $banner = $this->banner_model->get_by_id($id);
        if ($banner) {
            $new_status = ($banner['status'] === 'active') ? 'inactive' : 'active';
            $this->banner_model->update($id, ['status' => $new_status]);
            $this->session->set_flashdata('success', 'Banner status updated.');
        }
        redirect('banners');
    }
}
