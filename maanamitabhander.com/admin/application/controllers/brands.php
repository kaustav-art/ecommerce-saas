<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class brands extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('brand_model');
    }

    public function index()
    {
        $this->require_permission('brands.manage');

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('name', 'Brand Name', 'required|trim');

            if ($this->form_validation->run() === TRUE) {
                $id   = $this->input->post('id');
                $name = $this->input->post('name', TRUE);
                $slug = url_title($name, 'dash', TRUE);

                // Handle brand logo file upload
                $uploaded_logo = $this->upload_image_file('logo_file', 'brand', 'brand');
                if ($uploaded_logo === false) {
                    $this->session->set_flashdata('error', 'Brand logo upload error: ' . $this->upload->display_errors('', ''));
                    redirect('brands');
                    return;
                }
                $logo = $uploaded_logo ?: ($this->input->post('current_logo', TRUE) ?: 'brand/brand-01.svg');

                $data = [
                    'name'   => $name,
                    'slug'   => $slug,
                    'logo'   => $logo,
                    'status' => $this->input->post('status', TRUE) ?: 'active'
                ];

                if (!empty($id)) {
                    $this->brand_model->update($id, $data);
                    $this->session->set_flashdata('success', 'Brand updated successfully.');
                } else {
                    $this->brand_model->create($data);
                    $this->session->set_flashdata('success', 'Brand created successfully.');
                }
                redirect('brands');
            }
        }

        $data = [
            'title'          => 'Brand Management | Admin',
            'active_menu'    => 'products',
            'active_submenu' => 'brands',
            'brands'         => $this->brand_model->get_all()
        ];

        $this->render('brands/index', $data);
    }

    public function delete($id)
    {
        $this->require_permission('brands.manage');
        $this->brand_model->delete($id);
        $this->session->set_flashdata('success', 'Brand deleted successfully.');
        redirect('brands');
    }
}
