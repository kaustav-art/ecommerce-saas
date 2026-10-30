<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Website extends Superadmin_Controller {

    public function index() {
        if ($this->input->method() === 'post') {
            $active_tab = $this->input->post('active_tab', TRUE) ?: 'hero';
            $action = $this->input->post('action', TRUE);

            if ($action === 'save_hero') {
                $fields = array(
                    'platform_name',
                    'site_title',
                    'hero_badge',
                    'hero_heading',
                    'hero_subheading',
                    'cta_button_text',
                    'contact_email',
                    'contact_phone',
                    'terms_content',
                    'privacy_content'
                );
                foreach ($fields as $f) {
                    if ($this->input->post($f) !== NULL) {
                        set_setting($f, trim((string)$this->input->post($f)), 'landing');
                    }
                }
                $this->session->set_flashdata('success', 'SaaS Landing Page CMS & Legal Content updated successfully!');
                redirect(superadmin_url('website?tab=' . urlencode($active_tab)));
                return;
            }

            if ($action === 'save_template') {
                $tpl_id = (int)$this->input->post('template_id');
                $name = trim((string)$this->input->post('name', TRUE));
                $badge = trim((string)$this->input->post('badge', TRUE));
                $description = trim((string)$this->input->post('description', TRUE));
                $preview_image = trim((string)$this->input->post('preview_image', TRUE));
                $demo_url = trim((string)$this->input->post('demo_url', TRUE));
                $is_active = ($this->input->post('status', TRUE) === 'inactive') ? 0 : 1;
                $sort_order = (int)$this->input->post('sort_order');

                $features_raw = (string)$this->input->post('features', TRUE);
                $features_arr = array_values(array_filter(array_map('trim', explode("\n", $features_raw))));

                if ($tpl_id > 0) {
                    $this->db->where('id', $tpl_id)->update('saas_templates', array(
                        'name'          => $name,
                        'badge'         => $badge,
                        'description'   => $description,
                        'preview_image' => $preview_image,
                        'demo_url'      => $demo_url,
                        'features'      => json_encode($features_arr),
                        'is_active'     => $is_active,
                        'sort_order'    => $sort_order
                    ));
                    $this->session->set_flashdata('success', 'Storefront template "' . $name . '" updated successfully!');
                }
                redirect(superadmin_url('website?tab=themes'));
                return;
            }
        }

        $settings_rows = $this->db->get('saas_settings')->result();
        $settings = array();
        foreach ($settings_rows as $r) {
            if (isset($r->key_name)) {
                $settings[$r->key_name] = $r->key_value;
            }
        }

        $data['settings'] = $settings;
        $data['active_tab'] = $this->input->get('tab', TRUE) ?: 'hero';
        $data['templates'] = $this->db->order_by('sort_order', 'ASC')->get('saas_templates')->result();
        $data['enquiries'] = $this->db->table_exists('saas_enquiries')
            ? $this->db->order_by('id', 'DESC')->limit(50)->get('saas_enquiries')->result()
            : array();

        $this->render('website/index', $data, 'SaaS Landing Website & Templates CMS');
    }
}
