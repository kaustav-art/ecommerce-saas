<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class orders extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('order_model');
        $this->load->model('setting_model');
    }

    public function index()
    {
        $this->require_permission('orders.view');

        $status = $this->input->get('status');
        $orders = $this->order_model->get_all(NULL, NULL, $status);

        $data = [
            'title'           => 'Orders Management | Admin',
            'active_menu'     => 'orders',
            'orders'          => $orders,
            'selected_status' => $status,
            'currency_symbol' => $this->setting_model->get('currency_symbol', '$')
        ];

        $this->render('orders/index', $data);
    }

    public function view($id)
    {
        $this->require_permission('orders.view');

        $order = $this->order_model->get_by_id($id);
        if (!$order) {
            $this->session->set_flashdata('error', 'Order not found.');
            redirect('orders');
        }

        $data = [
            'title'           => 'Order Details #' . $order['order_number'] . ' | Admin',
            'active_menu'     => 'orders',
            'order'           => $order,
            'currency_symbol' => $this->setting_model->get('currency_symbol', '$')
        ];

        $this->render('orders/view', $data);
    }

    public function update_status($id)
    {
        $this->require_permission('orders.manage');

        if ($this->input->method() === 'post') {
            $order = $this->order_model->get_by_id($id);
            if (!$order) {
                show_404();
            }

            $action_override = $this->input->post('action_override', TRUE);
            $action = !empty($action_override) ? $action_override : ($this->input->post('action', TRUE) ?: 'ship_items');
            $selected_items = $this->input->post('selected_items');
            $selected_item_ids = is_array($selected_items) ? array_map('intval', array_filter($selected_items)) : [];

            // 1. CANCEL SINGLE ITEM (From Ordered Products card - only pending items)
            if ($action === 'cancel_item') {
                $item_id = (int) $this->input->post('item_id');
                $item = $this->db->where('order_id', (int) $id)->where('id', $item_id)->get('order_items')->row_array();
                if ($item) {
                    if ($item['item_status'] === 'shipped' || $item['item_status'] === 'delivered') {
                        $this->session->set_flashdata('error', 'Shipped products cannot be cancelled from the Ordered Products card.');
                    } else {
                        $this->db->where('id', $item_id)->where('order_id', (int) $id)->update('order_items', [
                            'item_status' => 'cancelled'
                        ]);
                        $this->_sync_order_status($id);

                        // Restore stock for cancelled product
                        $qty = (int) $item['quantity'];
                        if ($qty > 0 && !empty($item['product_id'])) {
                            $this->db->set('stock_quantity', 'stock_quantity + ' . $qty, FALSE)
                                     ->where('id', (int) $item['product_id'])
                                     ->update('products');

                            if (!empty($item['variant_id'])) {
                                $this->db->set('stock_quantity', 'stock_quantity + ' . $qty, FALSE)
                                         ->set('stock_status', 'in_stock')
                                         ->where('id', (int) $item['variant_id'])
                                         ->update('product_variants');
                            }

                            if ($this->db->table_exists('stock_adjustments')) {
                                $this->db->insert('stock_adjustments', [
                                    'tenant_id'  => $this->tenant_id,
                                    'product_id' => $item['product_id'],
                                    'variant_id' => !empty($item['variant_id']) ? $item['variant_id'] : NULL,
                                    'type'       => 'in',
                                    'quantity'   => $qty,
                                    'reason'     => "Order #{$order['order_number']} Item Cancelled - Restocked"
                                ]);
                            }
                        }

                        $this->session->set_flashdata('success', '"' . html_escape($item['product_title']) . '" has been marked as Cancelled.');
                    }
                } else {
                    $this->session->set_flashdata('error', 'Product not found in this order.');
                }
            }
            // 2. UPDATE SHIPMENT STATUS (From Shipped card three dots: Delivered or Cancel)
            elseif ($action === 'update_shipment_status') {
                $tracking_number = trim($this->input->post('tracking_number', TRUE));
                $shipment_item_ids_raw = $this->input->post('shipment_item_ids', TRUE);
                if (is_array($shipment_item_ids_raw)) {
                    $item_ids = array_map('intval', array_filter($shipment_item_ids_raw));
                } elseif (!empty($shipment_item_ids_raw)) {
                    $item_ids = array_map('intval', array_filter(explode(',', $shipment_item_ids_raw)));
                } else {
                    $item_ids = [];
                }
                $new_status = strtolower($this->input->post('new_status', TRUE));

                if (in_array($new_status, ['delivered', 'cancelled'])) {
                    $this->db->where('order_id', (int) $id);
                    if (!empty($item_ids)) {
                        $this->db->where_in('id', $item_ids);
                    } elseif (!empty($tracking_number)) {
                        $this->db->where('tracking_number', $tracking_number);
                    }

                    $update_data = ['item_status' => $new_status];
                    if ($new_status === 'delivered') {
                        $update_data['delivered_at'] = date('Y-m-d H:i:s');
                    }
                    $this->db->update('order_items', $update_data);

                    $this->_sync_order_status($id);
                    $status_label = ($new_status === 'delivered') ? 'Delivered' : 'Cancelled';
                    $track_label = !empty($tracking_number) ? ('Shipment (AWB: ' . html_escape($tracking_number) . ')') : 'Shipment';
                    $this->session->set_flashdata('success', $track_label . ' marked as ' . $status_label . '.');
                } else {
                    $this->session->set_flashdata('error', 'Invalid shipment status update requested.');
                }
            }
            // 3. UPDATE INDIVIDUAL ITEM STATUS
            elseif ($action === 'update_item_status') {
                $item_id = (int) $this->input->post('item_id');
                $new_status = strtolower($this->input->post('new_status', TRUE));
                $item = $this->db->where('order_id', (int) $id)->where('id', $item_id)->get('order_items')->row_array();
                if ($item && in_array($new_status, ['delivered', 'cancelled', 'shipped', 'pending'])) {
                    $item_update = ['item_status' => $new_status];
                    if ($new_status === 'delivered') {
                        $item_update['delivered_at'] = date('Y-m-d H:i:s');
                    }
                    $this->db->where('id', $item_id)->where('order_id', (int) $id)->update('order_items', $item_update);
                    $this->_sync_order_status($id);
                    $status_label = ($new_status === 'delivered') ? 'Delivered' : (($new_status === 'cancelled') ? 'Cancelled' : ucfirst($new_status));
                    $this->session->set_flashdata('success', '"' . html_escape($item['product_title']) . '" marked as ' . $status_label . '.');
                } else {
                    $this->session->set_flashdata('error', 'Invalid status update requested.');
                }
            }
            // 3. SHIP SELECTED ITEMS
            elseif ($action === 'ship_items') {
                if (empty($selected_item_ids)) {
                    $this->session->set_flashdata('error', 'Please select at least one pending product from the table to dispatch.');
                    redirect('orders/view/' . $id);
                }

                // Enforce: only pending items can be shipped (cancelled or already shipped items cannot be chosen)
                $pending_items = $this->db->where('order_id', (int) $id)
                                         ->where_in('id', $selected_item_ids)
                                         ->where('item_status', 'pending')
                                         ->get('order_items')
                                         ->result_array();
                $valid_item_ids = array_column($pending_items, 'id');

                if (empty($valid_item_ids)) {
                    $this->session->set_flashdata('error', 'Selected product(s) cannot be shipped (they may already be shipped or cancelled).');
                    redirect('orders/view/' . $id);
                }

                $courier_name    = trim($this->input->post('courier_name', TRUE));
                $tracking_number = trim($this->input->post('tracking_number', TRUE));
                $tracking_url    = trim($this->input->post('tracking_url', TRUE));

                if (empty($courier_name) || empty($tracking_number)) {
                    $this->session->set_flashdata('error', 'Please provide both Courier Partner and AWB / Tracking Number to dispatch.');
                    redirect('orders/view/' . $id);
                }

                // Auto-generate standard tracking URL if courier and AWB provided but URL is empty
                if (empty($tracking_url)) {
                    $c_lower = strtolower($courier_name);
                    if (strpos($c_lower, 'xpress') !== false) {
                        $tracking_url = 'https://www.xpressbees.com/shipment/tracking?awbNo=' . urlencode($tracking_number);
                    } elseif (strpos($c_lower, 'delhivery') !== false) {
                        $tracking_url = 'https://www.delhivery.com/track/package/' . urlencode($tracking_number);
                    } elseif (strpos($c_lower, 'dtdc') !== false) {
                        $tracking_url = 'https://www.dtdc.in/tracking.asp';
                    } elseif (strpos($c_lower, 'blue') !== false) {
                        $tracking_url = 'https://www.bluedart.com/tracking';
                    } elseif (strpos($c_lower, 'ekart') !== false) {
                        $tracking_url = 'https://ekartlogistics.com/shipmenttrack/' . urlencode($tracking_number);
                    } elseif (strpos($c_lower, 'shadowfax') !== false) {
                        $tracking_url = 'https://tracker.shadowfax.in/#/track?awb=' . urlencode($tracking_number);
                    } elseif (strpos($c_lower, 'speed') !== false || strpos($c_lower, 'post') !== false) {
                        $tracking_url = 'https://www.indiapost.gov.in/_layouts/15/dpt.cept.tracking/trackconsignment.aspx';
                    }
                }

                $this->db->where('order_id', (int) $id)
                         ->where_in('id', $valid_item_ids)
                         ->update('order_items', [
                             'item_status'     => 'shipped',
                             'courier_name'    => $courier_name,
                             'tracking_number' => $tracking_number,
                             'tracking_url'    => !empty($tracking_url) ? $tracking_url : NULL,
                             'shipped_at'      => date('Y-m-d H:i:s')
                         ]);

                $this->_sync_order_status($id, $courier_name, $tracking_number, $tracking_url);
                $this->session->set_flashdata('success', count($valid_item_ids) . ' product(s) marked as Shipped via ' . $courier_name . '.');
            }
            // 4. DELIVER SELECTED ITEMS (BATCH)
            elseif ($action === 'deliver_items') {
                if (empty($selected_item_ids)) {
                    $this->session->set_flashdata('error', 'Please select at least one product to mark as Delivered.');
                    redirect('orders/view/' . $id);
                }

                $this->db->where('order_id', (int) $id)
                         ->where_in('id', $selected_item_ids)
                         ->update('order_items', [
                             'item_status'  => 'delivered',
                             'delivered_at' => date('Y-m-d H:i:s')
                         ]);

                $this->_sync_order_status($id);
                $this->session->set_flashdata('success', count($selected_item_ids) . ' product(s) marked as Delivered.');
            }
            // 5. UPDATE PAYMENT STATUS ONLY (Fulfillment is done per-product)
            elseif ($action === 'update_payment' || $action === 'update_order') {
                $payment_status = $this->input->post('payment_status', TRUE);
                if (in_array($payment_status, ['pending', 'paid', 'failed', 'refunded'])) {
                    $this->order_model->update($id, ['payment_status' => $payment_status]);
                    $this->session->set_flashdata('success', 'Payment status updated to ' . ucfirst($payment_status) . '.');
                } else {
                    $this->session->set_flashdata('error', 'Invalid payment status selected.');
                }
            }
        }
        redirect('orders/view/' . $id);
    }

    private function _sync_order_status($order_id, $courier_name = NULL, $tracking_number = NULL, $tracking_url = NULL)
    {
        $all_items = $this->db->where('order_id', (int) $order_id)->get('order_items')->result_array();
        if (empty($all_items)) {
            return;
        }

        $all_delivered = true;
        $all_cancelled = true;
        $has_shipped   = false;
        $has_delivered = false;

        foreach ($all_items as $it) {
            $st = !empty($it['item_status']) ? strtolower($it['item_status']) : 'pending';
            if ($st !== 'delivered' && $st !== 'cancelled') {
                $all_delivered = false;
            }
            if ($st !== 'cancelled') {
                $all_cancelled = false;
            }
            if ($st === 'shipped') {
                $has_shipped = true;
            }
            if ($st === 'delivered') {
                $has_delivered = true;
            }
        }

        if ($all_cancelled) {
            $new_order_status = 'cancelled';
        } elseif ($all_delivered && $has_delivered) {
            $new_order_status = 'delivered';
        } elseif ($has_shipped || $has_delivered) {
            $new_order_status = 'shipped';
        } else {
            $new_order_status = 'pending';
        }

        $order_update = ['order_status' => $new_order_status];
        if ($new_order_status === 'delivered') {
            $order_update['delivered_at'] = date('Y-m-d H:i:s');
        }
        if (!empty($courier_name)) {
            $order_update['courier_name'] = $courier_name;
        }
        if (!empty($tracking_number)) {
            $order_update['tracking_number'] = $tracking_number;
        }
        if (!empty($tracking_url)) {
            $order_update['tracking_url'] = $tracking_url;
        }

        $this->order_model->update($order_id, $order_update);
    }

    public function invoice($id)
    {
        $this->require_permission('orders.view');

        $order = $this->order_model->get_by_id($id);
        if (!$order) {
            $this->session->set_flashdata('error', 'Order not found.');
            redirect('orders');
        }

        if (strtolower($order['order_status']) === 'cancelled') {
            $this->session->set_flashdata('error', 'Cannot download invoice for cancelled orders.');
            redirect('orders/view/' . $id);
        }

        $data = [
            'title'           => 'Invoice #' . $order['order_number'],
            'order'           => $order,
            'site_name'       => $this->setting_model->get('site_name', 'Store'),
            'site_email'      => $this->setting_model->get('site_email', 'billing@ecommerce.com'),
            'site_address'    => $this->setting_model->get('site_address', '123 Commerce St'),
            'currency_symbol' => $this->setting_model->get('currency_symbol', '$')
        ];

        $this->render_blank('orders/invoice', $data);
    }

    public function cancel($id)
    {
        $this->require_permission('orders.manage');

        $order = $this->order_model->get_by_id($id);
        if (!$order) {
            $this->session->set_flashdata('error', 'Order not found.');
            redirect('orders');
        }

        if (strtolower($order['order_status']) === 'cancelled') {
            $this->session->set_flashdata('error', 'Order #' . html_escape($order['order_number']) . ' is already cancelled.');
            redirect('orders');
        }

        if (in_array(strtolower($order['order_status']), ['shipped', 'delivered']) || !empty($order['shipped_at'])) {
            $this->session->set_flashdata('error', 'Order #' . html_escape($order['order_number']) . ' cannot be cancelled because it has already been dispatched.');
            redirect('orders');
        }

        // Verify that NO product in this order has been shipped or delivered
        $has_shipped = false;
        if (!empty($order['items'])) {
            foreach ($order['items'] as $item) {
                $st = strtolower($item['item_status'] ?? 'pending');
                if (in_array($st, ['shipped', 'delivered']) || !empty($item['shipped_at'])) {
                    $has_shipped = true;
                    break;
                }
            }
        }

        if ($has_shipped) {
            $this->session->set_flashdata('error', 'Order #' . html_escape($order['order_number']) . ' cannot be cancelled because one or more products have already been shipped.');
            redirect('orders');
        }

        // Cancel all items in this order
        $this->db->where('order_id', (int) $id)->update('order_items', [
            'item_status' => 'cancelled'
        ]);

        // Update overall order status to cancelled
        $this->order_model->update($id, [
            'order_status' => 'cancelled'
        ]);

        // Restore stock for items that were not already cancelled
        if (!empty($order['items'])) {
            foreach ($order['items'] as $item) {
                if (($item['item_status'] ?? 'pending') !== 'cancelled') {
                    $qty = (int) $item['quantity'];
                    if ($qty > 0 && !empty($item['product_id'])) {
                        $this->db->set('stock_quantity', 'stock_quantity + ' . $qty, FALSE)
                                 ->where('id', (int) $item['product_id'])
                                 ->update('products');

                        if (!empty($item['variant_id'])) {
                            $this->db->set('stock_quantity', 'stock_quantity + ' . $qty, FALSE)
                                     ->set('stock_status', 'in_stock')
                                     ->where('id', (int) $item['variant_id'])
                                     ->update('product_variants');
                        }

                        if ($this->db->table_exists('stock_adjustments')) {
                            $this->db->insert('stock_adjustments', [
                                'tenant_id'  => $this->tenant_id,
                                'product_id' => $item['product_id'],
                                'variant_id' => !empty($item['variant_id']) ? $item['variant_id'] : NULL,
                                'type'       => 'in',
                                'quantity'   => $qty,
                                'reason'     => "Order #{$order['order_number']} Cancelled by Admin - Restocked"
                            ]);
                        }
                    }
                }
            }
        }

        $this->session->set_flashdata('success', 'Order #' . html_escape($order['order_number']) . ' has been cancelled successfully.');
        redirect('orders');
    }
}
