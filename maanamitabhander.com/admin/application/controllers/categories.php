<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class categories extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('category_model');
    }

    public function index()
    {
        $this->require_permission('categories.manage');

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('name', 'Category Name', 'required|trim');

            if ($this->form_validation->run() === TRUE) {
                $id        = $this->input->post('id');
                $parent_id = (int) $this->input->post('parent_id');
                $name      = $this->input->post('name', TRUE);
                $slug      = url_title($name, 'dash', TRUE);

                // Prevent circular parent relationship
                if (!empty($id)) {
                    if ((int)$id === $parent_id) {
                        $parent_id = 0;
                    } else {
                        $descendants = $this->category_model->get_descendant_ids($id);
                        if (in_array($parent_id, $descendants, true)) {
                            $parent_id = 0;
                        }
                    }
                }

                $data = [
                    'parent_id'   => $parent_id,
                    'name'        => $name,
                    'slug'        => $slug,
                    'description' => $this->input->post('description', TRUE),
                    'is_featured' => $this->input->post('is_featured') ? 1 : 0,
                    'sort_order'  => (int) $this->input->post('sort_order'),
                    'status'      => $this->input->post('status', TRUE) ?: 'active'
                ];

                // Handle category image file upload
                if (!empty($_FILES['image_file']['name'])) {
                    $upload_path = admin_storefront_fs_path('assets/images/categories/');
                    if (!is_dir($upload_path)) {
                        @mkdir($upload_path, 0777, TRUE);
                    }

                    $config['upload_path']   = $upload_path;
                    $config['allowed_types'] = 'gif|jpg|jpeg|png|webp|svg';
                    $config['max_size']      = 5120; // 5MB
                    $config['file_name']     = 'cat_' . time() . '_' . rand(100, 999);

                    if (!isset($this->upload)) {
                        $this->load->library('upload', $config);
                    } else {
                        $this->upload->initialize($config);
                    }

                    if ($this->upload->do_upload('image_file')) {
                        $upload_data = $this->upload->data();
                        $data['image'] = 'categories/' . $upload_data['file_name'];
                    } else {
                        $this->session->set_flashdata('error', $this->upload->display_errors('', ''));
                        redirect('categories');
                        return;
                    }
                } elseif ($this->input->post('existing_image')) {
                    $data['image'] = trim($this->input->post('existing_image', TRUE));
                } elseif ($this->input->post('remove_image') === '1') {
                    $data['image'] = NULL;
                }

                if (!empty($id)) {
                    $this->category_model->update($id, $data);
                    $this->session->set_flashdata('success', 'Category updated successfully.');
                } else {
                    $this->category_model->create($data);
                    $this->session->set_flashdata('success', 'Category created successfully.');
                }
                redirect('categories');
            }
        }

        $initial_data = $this->category_model->get_paginated(0, 10);
        $data = [
            'title'            => 'Category Management | Admin',
            'active_menu'      => 'products',
            'active_submenu'   => 'categories',
            'categories'       => $initial_data['categories'],
            'total_categories' => $initial_data['total'],
            'has_more'         => $initial_data['has_more'],
            'parent_options'   => $this->category_model->get_parent_options()
        ];

        $this->render('categories/index', $data);
    }

    public function load_more()
    {
        $this->require_permission('categories.manage');
        $offset = (int) $this->input->get('offset');
        $limit  = (int) ($this->input->get('limit') ?: 10);
        $search = trim($this->input->get('search') ?: '');

        $result = $this->category_model->get_paginated($offset, $limit, $search);
        return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($result));
    }

    public function delete($id)
    {
        $this->require_permission('categories.manage');
        $this->category_model->delete($id);
        $this->session->set_flashdata('success', 'Category deleted successfully.');
        redirect('categories');
    }
}
