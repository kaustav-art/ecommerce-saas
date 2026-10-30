<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class order_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    private function tid()
    {
        $CI =& get_instance();
        return isset($CI->tenant_id) ? (int) $CI->tenant_id : 1;
    }

    public function create_order($order_data, $items)
    {
        $tid = $this->tid();

        // Validate max purchase quantity
        foreach ($items as $item) {
            $p_check = $this->db->select('title, max_purchase_quantity')->where('tenant_id', $tid)->where('id', (int)$item['id'])->get('products')->row_array();
            if ($p_check && isset($p_check['max_purchase_quantity']) && (int)$p_check['max_purchase_quantity'] > 0) {
                if ((int)$item['quantity'] > (int)$p_check['max_purchase_quantity']) {
                    return false;
                }
            }
        }

        // Generate unique human-readable order number
        $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
        $order_data['order_number'] = $order_number;
        $order_data['tenant_id']    = $tid;
        if (empty($order_data['user_id'])) {
            unset($order_data['user_id']);
        }

        $this->db->insert('orders', $order_data);
        $order_id = $this->db->insert_id();

        if ($order_id && !empty($items)) {
            $batch = [];
            foreach ($items as $item) {
                $variant_id = !empty($item['variant_id']) ? (int) $item['variant_id'] : NULL;
                $batch[] = [
                    'tenant_id'     => $tid,
                    'order_id'      => $order_id,
                    'product_id'    => $item['id'],
                    'variant_id'    => $variant_id,
                    'product_title' => $item['title'],
                    'variant_title' => $item['variant_title'] ?? NULL,
                    'product_sku'   => $item['sku'] ?? NULL,
                    'product_image' => $item['image'] ?? NULL,
                    'price'         => (float) $item['price'],
                    'quantity'      => (int) $item['quantity'],
                    'total'         => (float) $item['total']
                ];

                // Deduct main product stock quantity
                $this->db->set('stock_quantity', 'GREATEST(0, stock_quantity - ' . (int) $item['quantity'] . ')', FALSE)
                         ->where('tenant_id', $tid)
                         ->where('id', $item['id'])
                         ->update('products');

                // If variable product, also deduct variant stock
                if ($variant_id) {
                    $this->db->set('stock_quantity', 'GREATEST(0, stock_quantity - ' . (int) $item['quantity'] . ')', FALSE)
                             ->where('tenant_id', $tid)
                             ->where('id', $variant_id)
                             ->update('product_variants');

                    // Check if variant is now out of stock
                    $v = $this->db->select('stock_quantity')->where('tenant_id', $tid)->where('id', $variant_id)->get('product_variants')->row_array();
                    if ($v && $v['stock_quantity'] <= 0) {
                        $this->db->where('tenant_id', $tid)->where('id', $variant_id)->update('product_variants', ['stock_status' => 'out_of_stock']);
                    }
                }

                // Log stock adjustment if table exists
                if ($this->db->table_exists('inventory_transactions')) {
                    $this->db->insert('inventory_transactions', [
                        'product_id'     => $item['id'],
                        'variant_id'     => $variant_id,
                        'movement_type'  => 'ORDER_SHIPMENT',
                        'quantity'       => (int) $item['quantity'],
                        'reference_id'   => $order_number,
                        'reference_type' => 'order',
                        'note'           => "Order #{$order_number}",
                        'created_at'     => date('Y-m-d H:i:s')
                    ]);
                } elseif ($this->db->table_exists('stock_adjustments')) {
                    $this->db->insert('stock_adjustments', [
                        'tenant_id'  => $tid,
                        'product_id' => $item['id'],
                        'variant_id' => $variant_id,
                        'type'       => 'out',
                        'quantity'   => (int) $item['quantity'],
                        'reason'     => "Order #{$order_number}"
                    ]);
                }
            }
            $this->db->insert_batch('order_items', $batch);
        }

        return $order_number;
    }

    public function get_by_order_number($order_number)
    {
        $tid = $this->tid();
        $order = $this->db->where('tenant_id', $tid)->where('order_number', $order_number)->get('orders')->row_array();
        if ($order) {
            $order['items'] = $this->db->where('order_id', $order['id'])->get('order_items')->result_array();
            $order['returns'] = $this->db->table_exists('order_returns') 
                ? $this->db->where('order_id', $order['id'])->get('order_returns')->result_array() 
                : [];
        }
        return $order;
    }

    public function get_by_user_id($user_id)
    {
        $tid = $this->tid();
        $orders = $this->db->where('tenant_id', $tid)->where('user_id', (int) $user_id)->order_by('id', 'DESC')->get('orders')->result_array();
        foreach ($orders as &$ord) {
            $ord['items_count'] = $this->db->where('order_id', $ord['id'])->count_all_results('order_items');
            $ord['items'] = $this->db->where('order_id', $ord['id'])->get('order_items')->result_array();
            $ord['returns_count'] = $this->db->table_exists('order_returns') 
                ? $this->db->where('order_id', $ord['id'])->count_all_results('order_returns') 
                : 0;
        }
        return $orders;
    }

    public function update_payment($order_number, $payment_status, $transaction_id = NULL, $payment_details = NULL)
    {
        $update = ['payment_status' => $payment_status];

        if ($transaction_id) {
            $update['payment_transaction_id'] = $transaction_id;
        }
        if ($payment_details) {
            $update['payment_details'] = is_array($payment_details) ? json_encode($payment_details) : $payment_details;
        }
        return $this->db->where('tenant_id', $this->tid())->where('order_number', $order_number)->update('orders', $update);
    }

    public function update_status($order_number, $order_status)
    {
        return $this->db->where('tenant_id', $this->tid())
                        ->where('order_number', $order_number)
                        ->update('orders', [
                            'order_status' => $order_status,
                            'updated_at'   => date('Y-m-d H:i:s')
                        ]);
    }

    public function cancel_order($order_number, $user_id = NULL)
    {
        $tid = $this->tid();
        $this->db->where('tenant_id', $tid)->where('order_number', $order_number);
        if ($user_id !== NULL) {
            $this->db->where('user_id', (int) $user_id);
        }
        $order = $this->db->get('orders')->row_array();

        if (!$order) {
            return ['success' => false, 'message' => 'Order not found.'];
        }

        if (!in_array($order['order_status'], ['pending', 'on_hold'])) {
            return ['success' => false, 'message' => 'Only pending or on-hold orders can be cancelled.'];
        }

        // Update status
        $this->db->where('id', $order['id'])->update('orders', ['order_status' => 'cancelled']);

        // Restore stock
        $items = $this->db->where('order_id', $order['id'])->get('order_items')->result_array();
        foreach ($items as $item) {
            $this->db->set('stock_quantity', 'stock_quantity + ' . (int) $item['quantity'], FALSE)
                     ->where('tenant_id', $tid)
                     ->where('id', $item['product_id'])
                     ->update('products');

            if (!empty($item['variant_id'])) {
                $this->db->set('stock_quantity', 'stock_quantity + ' . (int) $item['quantity'], FALSE)
                         ->set('stock_status', 'in_stock')
                         ->where('tenant_id', $tid)
                         ->where('id', $item['variant_id'])
                         ->update('product_variants');
            }

            // Adjustment log
            $this->db->insert('stock_adjustments', [
                'tenant_id'  => $tid,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'] ?: NULL,
                'type'       => 'in',
                'quantity'   => (int) $item['quantity'],
                'reason'     => "Order #{$order_number} Cancelled - Restocked"
            ]);
        }

        return ['success' => true, 'message' => 'Order #' . $order_number . ' has been successfully cancelled.'];
    }

    public function create_return($data)
    {
        $data['tenant_id'] = $this->tid();
        return $this->db->insert('order_returns', $data);
    }

    public function get_returns_by_user($user_id)
    {
        $tid = $this->tid();
        return $this->db->select('r.*, o.order_number, o.created_at as order_date, o.total_amount')
                        ->from('order_returns r')
                        ->join('orders o', 'o.id = r.order_id AND o.tenant_id = ' . $tid)
                        ->where('r.tenant_id', $tid)
                        ->where('r.user_id', (int) $user_id)
                        ->order_by('r.id', 'DESC')
                        ->get()
                        ->result_array();
    }
}
