<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class customers extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('customer_model');
        $this->load->model('customer_group_model');
        $this->load->model('setting_model');
    }

    public function index()
    {
        $this->require_permission('customers.view');

        $filters = [
            'search'   => $this->input->get('search', TRUE),
            'status'   => $this->input->get('status', TRUE),
            'group_id' => $this->input->get('group_id', TRUE)
        ];

        $customers       = $this->customer_model->get_all(NULL, NULL, $filters);
        $total_customers = $this->customer_model->count_all($filters);
        $stats           = $this->customer_model->get_summary_stats();
        $groups          = $this->customer_model->get_customer_groups();

        $data = [
            'title'           => 'Customers Management | Admin',
            'active_menu'     => 'customers',
            'active_submenu'  => 'customers_list',
            'customers'       => $customers,
            'total_customers' => $total_customers,
            'stats'           => $stats,
            'groups'          => $groups,
            'filters'         => $filters,
            'currency_symbol' => $this->setting_model->get('currency_symbol', '$')
        ];

        $this->render('customers/index', $data);
    }

    public function create()
    {
        $this->require_permission('customers.manage');

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
            $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim');
            $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[users.email]');
            $this->form_validation->set_rules('phone', 'Phone Number', 'trim');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');

            if ($this->form_validation->run() === TRUE) {
                $group_id = $this->input->post('customer_group_id');
                $data = [
                    'first_name'        => $this->input->post('first_name', TRUE),
                    'last_name'         => $this->input->post('last_name', TRUE),
                    'email'             => strtolower(trim($this->input->post('email', TRUE))),
                    'phone'             => $this->input->post('phone', TRUE),
                    'password'          => password_hash($this->input->post('password'), PASSWORD_BCRYPT),
                    'status'            => $this->input->post('status', TRUE) ?: 'active',
                    'customer_group_id' => !empty($group_id) ? (int) $group_id : NULL,
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s')
                ];

                $id = $this->customer_model->create($data);
                $this->session->set_flashdata('success', 'Customer account created successfully.');
                redirect('customers/view/' . $id);
            } else {
                $this->session->set_flashdata('error', validation_errors());
                redirect('customers');
            }
        }

        redirect('customers');
    }

    public function view($id)
    {
        $this->require_permission('customers.view');

        $customer = $this->customer_model->get_by_id($id);
        if (!$customer) {
            $this->session->set_flashdata('error', 'Customer not found.');
            redirect('customers');
        }

        $groups = $this->customer_model->get_customer_groups();

        $data = [
            'title'           => 'Customer: ' . $customer['first_name'] . ' ' . $customer['last_name'] . ' | Admin',
            'active_menu'     => 'customers',
            'active_submenu'  => 'customers_list',
            'customer'        => $customer,
            'groups'          => $groups,
            'currency_symbol' => $this->setting_model->get('currency_symbol', '$')
        ];

        $this->render('customers/view', $data);
    }

    public function update($id)
    {
        $this->require_permission('customers.manage');

        $customer = $this->customer_model->get_by_id($id);
        if (!$customer) {
            $this->session->set_flashdata('error', 'Customer not found.');
            redirect('customers');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
            $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim');
            
            $email = strtolower(trim($this->input->post('email', TRUE)));
            if ($email !== strtolower($customer['email'])) {
                $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[users.email]');
            } else {
                $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
            }

            if ($this->form_validation->run() === TRUE) {
                $group_id = $this->input->post('customer_group_id');
                $update_data = [
                    'first_name'        => $this->input->post('first_name', TRUE),
                    'last_name'         => $this->input->post('last_name', TRUE),
                    'email'             => $email,
                    'phone'             => $this->input->post('phone', TRUE),
                    'status'            => $this->input->post('status', TRUE) ?: 'active',
                    'customer_group_id' => !empty($group_id) ? (int) $group_id : NULL,
                    'updated_at'        => date('Y-m-d H:i:s')
                ];

                $new_pass = $this->input->post('new_password');
                if (!empty($new_pass)) {
                    if (strlen($new_pass) < 6) {
                        $this->session->set_flashdata('error', 'Password must be at least 6 characters long.');
                        redirect('customers/view/' . $id);
                    }
                    $update_data['password'] = password_hash($new_pass, PASSWORD_BCRYPT);
                }

                $this->customer_model->update($id, $update_data);
                $this->session->set_flashdata('success', 'Customer profile updated successfully.');
            } else {
                $this->session->set_flashdata('error', validation_errors());
            }
        }

        redirect('customers/view/' . $id);
    }

    public function toggle_status($id)
    {
        $this->require_permission('customers.manage');

        $customer = $this->customer_model->get_by_id($id);
        if ($customer) {
            $new_status = ($customer['status'] === 'active') ? 'banned' : 'active';
            $this->customer_model->update($id, [
                'status'     => $new_status,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $this->session->set_flashdata('success', 'Customer status updated to ' . ucfirst($new_status));
        }

        $redirect = $this->input->get('redirect') ?: 'customers';
        redirect($redirect);
    }

    public function delete($id)
    {
        $this->require_permission('customers.manage');

        $customer = $this->customer_model->get_by_id($id);
        if ($customer) {
            $this->customer_model->delete($id);
            $this->session->set_flashdata('success', 'Customer deleted successfully.');
        } else {
            $this->session->set_flashdata('error', 'Customer not found.');
        }

        redirect('customers');
    }

    // --- Address Management ---
    public function add_address($customer_id)
    {
        $this->require_permission('customers.manage');

        if ($this->input->method() === 'post') {
            $data = [
                'user_id'    => (int) $customer_id,
                'type'       => $this->input->post('type', TRUE) ?: 'shipping',
                'first_name' => $this->input->post('first_name', TRUE),
                'last_name'  => $this->input->post('last_name', TRUE),
                'company'    => $this->input->post('company', TRUE),
                'address_1'  => $this->input->post('address_1', TRUE),
                'address_2'  => $this->input->post('address_2', TRUE),
                'city'       => $this->input->post('city', TRUE),
                'state'      => $this->input->post('state', TRUE),
                'postcode'   => $this->input->post('postcode', TRUE),
                'country'    => $this->input->post('country', TRUE) ?: 'India',
                'phone'      => $this->input->post('phone', TRUE),
                'is_default' => $this->input->post('is_default') ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->customer_model->add_address($data);
            $this->session->set_flashdata('success', 'New address added successfully.');
        }

        redirect('customers/view/' . $customer_id . '#tab-addresses');
    }

    public function update_address($customer_id, $address_id)
    {
        $this->require_permission('customers.manage');

        if ($this->input->method() === 'post') {
            $data = [
                'user_id'    => (int) $customer_id,
                'type'       => $this->input->post('type', TRUE) ?: 'shipping',
                'first_name' => $this->input->post('first_name', TRUE),
                'last_name'  => $this->input->post('last_name', TRUE),
                'company'    => $this->input->post('company', TRUE),
                'address_1'  => $this->input->post('address_1', TRUE),
                'address_2'  => $this->input->post('address_2', TRUE),
                'city'       => $this->input->post('city', TRUE),
                'state'      => $this->input->post('state', TRUE),
                'postcode'   => $this->input->post('postcode', TRUE),
                'country'    => $this->input->post('country', TRUE),
                'phone'      => $this->input->post('phone', TRUE),
                'is_default' => $this->input->post('is_default') ? 1 : 0
            ];

            $this->customer_model->update_address($address_id, $data);
            $this->session->set_flashdata('success', 'Address updated successfully.');
        }

        redirect('customers/view/' . $customer_id . '#tab-addresses');
    }

    public function delete_address($customer_id, $address_id)
    {
        $this->require_permission('customers.manage');

        $this->customer_model->delete_address($address_id, $customer_id);
        $this->session->set_flashdata('success', 'Address deleted successfully.');
        redirect('customers/view/' . $customer_id . '#tab-addresses');
    }

    // --- Abandoned Cart Management ---
    public function remove_cart_item($customer_id, $cart_item_id)
    {
        $this->require_permission('customers.manage');

        $this->customer_model->remove_cart_item($cart_item_id, $customer_id);
        $this->session->set_flashdata('success', 'Item removed from customer cart.');
        redirect('customers/view/' . $customer_id . '#tab-cart');
    }

    public function clear_cart($customer_id)
    {
        $this->require_permission('customers.manage');

        $this->customer_model->clear_cart($customer_id);
        $this->session->set_flashdata('success', 'Customer cart cleared successfully.');
        redirect('customers/view/' . $customer_id . '#tab-cart');
    }

    // --- Wishlist Management ---
    public function remove_wishlist_item($customer_id, $wishlist_id)
    {
        $this->require_permission('customers.manage');

        $this->customer_model->remove_wishlist_item($wishlist_id, $customer_id);
        $this->session->set_flashdata('success', 'Item removed from customer wishlist.');
        redirect('customers/view/' . $customer_id . '#tab-wishlist');
    }

    // --- Reviews Management ---
    public function update_review_status($customer_id, $review_id, $status)
    {
        $this->require_permission('customers.manage');

        if (in_array($status, ['approved', 'pending', 'rejected'])) {
            $this->customer_model->update_review_status($review_id, $status);
            $this->session->set_flashdata('success', 'Review status updated to ' . ucfirst($status));
        }

        redirect('customers/view/' . $customer_id . '#tab-reviews');
    }

    public function delete_review($customer_id, $review_id)
    {
        $this->require_permission('customers.manage');

        $this->customer_model->delete_review($review_id);
        $this->session->set_flashdata('success', 'Review deleted successfully.');
        redirect('customers/view/' . $customer_id . '#tab-reviews');
    }
}
