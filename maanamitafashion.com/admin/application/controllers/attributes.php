<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class attributes extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('attribute_model');
    }

    public function index()
    {
        $this->require_permission('products.manage');

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('name', 'Attribute Name', 'required|trim');
            if ($this->form_validation->run() === TRUE) {
                $name = $this->input->post('name', TRUE);
                $data = [
                    'name' => $name,
                    'slug' => url_title($name, 'dash', TRUE)
                ];
                $id = $this->input->post('id');
                if (!empty($id)) {
                    // Display type cannot be changed if attribute is already used in products
                    $is_locked = $this->attribute_model->is_used_in_products($id);
                    if ($is_locked) {
                        $existing = $this->attribute_model->get_by_id($id);
                        $data['type'] = $existing['type'];
                    } else {
                        $data['type'] = $this->input->post('type', TRUE) ?: 'select';
                    }
                    $this->attribute_model->update($id, $data);
                    $this->session->set_flashdata('success', 'Attribute updated successfully.');
                } else {
                    $data['type'] = $this->input->post('type', TRUE) ?: 'select';
                    $this->attribute_model->create($data);
                    $this->session->set_flashdata('success', 'Attribute created successfully.');
                }
                redirect('attributes');
            }
        }

        $data = [
            'title'          => 'Product Attributes | Admin',
            'active_menu'    => 'products',
            'active_submenu' => 'attributes',
            'attributes'     => $this->attribute_model->get_all()
        ];
        $this->render('attributes/index', $data);
    }

    public function values($attribute_id)
    {
        $this->require_permission('products.manage');
        $attribute = $this->attribute_model->get_by_id($attribute_id);
        if (!$attribute) {
            show_404();
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('value', 'Value', 'required|trim');
            if ($this->form_validation->run() === TRUE) {
                $val_id     = $this->input->post('id');
                $val        = $this->input->post('value', TRUE);
                $color_code = $this->input->post('color_code', TRUE);
                $sort_order = (int) $this->input->post('sort_order');

                if (!empty($val_id)) {
                    $this->attribute_model->update_value($val_id, $val, $color_code, $sort_order);
                    $this->session->set_flashdata('success', 'Attribute value updated successfully.');
                } else {
                    $this->attribute_model->add_value($attribute_id, $val, $color_code, $sort_order);
                    $this->session->set_flashdata('success', 'Attribute value added successfully.');
                }
                redirect('attributes/values/' . $attribute_id);
            }
        }

        $edit_id = $this->input->get('edit');
        $edit_value = null;
        if (!empty($edit_id)) {
            $edit_value = $this->attribute_model->get_value_by_id($edit_id);
        }

        $data = [
            'title'          => 'Values for ' . html_escape($attribute['name']) . ' | Admin',
            'active_menu'    => 'products',
            'active_submenu' => 'attributes',
            'attribute'      => $attribute,
            'edit_value'     => $edit_value
        ];
        $this->render('attributes/values', $data);
    }

    public function delete_value($attribute_id, $value_id)
    {
        $this->require_permission('products.manage');
        $this->attribute_model->delete_value($value_id);
        $this->session->set_flashdata('success', 'Value removed.');
        redirect('attributes/values/' . $attribute_id);
    }

    public function delete($id)
    {
        $this->require_permission('products.manage');
        if ($this->attribute_model->is_used_in_products($id)) {
            $this->session->set_flashdata('error', 'Cannot delete this attribute because it is currently used in products.');
            redirect('attributes');
            return;
        }
        $this->attribute_model->delete($id);
        $this->session->set_flashdata('success', 'Attribute deleted.');
        redirect('attributes');
    }
}
