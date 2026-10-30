<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class marketing_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    protected function tid()
    {
        $ci =& get_instance();
        return isset($ci->tenant_id) ? (int) $ci->tenant_id : 1;
    }

    // Coupons
    public function get_coupons()
    {
        return $this->db->where('tenant_id', $this->tid())->order_by('id', 'DESC')->get('coupons')->result_array();
    }

    public function get_coupon($id)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->get('coupons')->row_array();
    }

    public function save_coupon($data, $id = null)
    {
        if (!empty($id)) {
            return $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->update('coupons', $data);
        }
        $data['tenant_id'] = $this->tid();
        return $this->db->insert('coupons', $data);
    }

    public function delete_coupon($id)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->delete('coupons');
    }

    // Flash Sales
    public function compute_status($fs)
    {
        if (empty($fs) || !is_array($fs)) {
            return 'ended';
        }
        if (!empty($fs['status']) && $fs['status'] === 'terminated') {
            return 'terminated';
        }
        $now = time();
        $start = !empty($fs['start_time']) ? strtotime($fs['start_time']) : 0;
        $end   = !empty($fs['end_time']) ? strtotime($fs['end_time']) : 0;

        if ($now < $start) {
            return 'scheduled';
        } elseif ($now >= $start && $now <= $end) {
            return 'running';
        } else {
            return 'ended';
        }
    }

    public function get_flash_sales()
    {
        $sales = $this->db->where('tenant_id', $this->tid())->order_by('id', 'DESC')->get('flash_sales')->result_array();
        foreach ($sales as &$s) {
            $computed = $this->compute_status($s);
            if ($s['status'] !== 'terminated' && $s['status'] !== $computed) {
                $this->db->where('tenant_id', $this->tid())->where('id', (int)$s['id'])->update('flash_sales', ['status' => $computed]);
                $s['status'] = $computed;
            }
            $s['computed_status'] = $computed;
            $p_rows = $this->db->select('product_id')->where('flash_sale_id', $s['id'])->get('flash_sale_products')->result_array();
            $s['products_count'] = count($p_rows);
            $s['product_ids'] = array_map('intval', array_column($p_rows, 'product_id'));
        }
        return $sales;
    }

    public function get_flash_sale($id)
    {
        $sale = $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->get('flash_sales')->row_array();
        if ($sale) {
            $computed = $this->compute_status($sale);
            if ($sale['status'] !== 'terminated' && $sale['status'] !== $computed) {
                $this->db->where('tenant_id', $this->tid())->where('id', (int)$sale['id'])->update('flash_sales', ['status' => $computed]);
                $sale['status'] = $computed;
            }
            $sale['computed_status'] = $computed;
            $p_rows = $this->db->select('product_id')->where('flash_sale_id', $sale['id'])->get('flash_sale_products')->result_array();
            $sale['product_ids'] = array_map('intval', array_column($p_rows, 'product_id'));
            $sale['products'] = $this->db->select('fsp.*, p.title, p.price, p.main_image, p.sku')
                                         ->from('flash_sale_products fsp')
                                         ->join('products p', 'p.id = fsp.product_id')
                                         ->where('fsp.flash_sale_id', $sale['id'])
                                         ->get()
                                         ->result_array();
        }
        return $sale;
    }

    public function generate_unique_flash_sale_slug($title, $exclude_id = null)
    {
        $base_slug = url_title($title, 'dash', TRUE);
        if (empty($base_slug)) {
            $base_slug = 'flash-sale';
        }
        $slug = $base_slug;
        $i = 2;
        while (true) {
            $this->db->where('tenant_id', $this->tid())
                     ->where('slug', $slug);
            if (!empty($exclude_id)) {
                $this->db->where('id !=', (int)$exclude_id);
            }
            if ($this->db->count_all_results('flash_sales') === 0) {
                break;
            }
            $slug = $base_slug . '-' . $i++;
        }
        return $slug;
    }

    public function get_active_or_scheduled_campaign_by_title($title, $exclude_id = null)
    {
        $clean_title = strtolower(trim((string)$title));
        if ($clean_title === '') {
            return null;
        }

        $this->db->where('tenant_id', $this->tid())
                 ->where('status !=', 'terminated');
        if (!empty($exclude_id)) {
            $this->db->where('id !=', (int)$exclude_id);
        }
        $rows = $this->db->get('flash_sales')->result_array();
        foreach ($rows as $r) {
            if (strtolower(trim((string)$r['title'])) === $clean_title) {
                $st = $this->compute_status($r);
                if ($st === 'running' || $st === 'scheduled') {
                    $r['computed_status'] = $st;
                    return $r;
                }
            }
        }
        return null;
    }

    public function save_flash_sale($data, $id = null, $product_ids = [], $sale_prices = [])
    {
        if (!empty($id)) {
            $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->update('flash_sales', $data);
            $fs_id = $id;
        } else {
            $data['tenant_id'] = $this->tid();
            $this->db->insert('flash_sales', $data);
            $fs_id = $this->db->insert_id();
        }

        if (!empty($product_ids) && $fs_id) {
            $this->db->where('flash_sale_id', $fs_id)->delete('flash_sale_products');
            foreach ($product_ids as $pid) {
                $p_price = isset($sale_prices[$pid]) ? (float)$sale_prices[$pid] : 0.00;
                $this->db->insert('flash_sale_products', [
                    'flash_sale_id' => $fs_id,
                    'product_id'    => (int) $pid,
                    'sale_price'    => $p_price,
                    'quantity_limit'=> 50,
                    'sold_count'    => 0
                ]);
            }
        }
        return $fs_id;
    }

    public function terminate_flash_sale($id)
    {
        return $this->db->where('tenant_id', $this->tid())
                        ->where('id', (int) $id)
                        ->update('flash_sales', [
                            'status' => 'terminated',
                            'end_time' => date('Y-m-d H:i:s')
                        ]);
    }

    public function delete_flash_sale($id)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->delete('flash_sales');
    }

    // Newsletter Subscribers
    public function get_subscribers()
    {
        return $this->db->where('tenant_id', $this->tid())->order_by('id', 'DESC')->get('newsletter_subscribers')->result_array();
    }

    public function delete_subscriber($id)
    {
        return $this->db->where('tenant_id', $this->tid())->where('id', (int) $id)->delete('newsletter_subscribers');
    }
}
