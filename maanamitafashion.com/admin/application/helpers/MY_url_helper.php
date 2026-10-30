<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('admin_storefront_fs_path')) {
    function admin_storefront_fs_path($subpath = '') {
        $base = is_dir(FCPATH . '../website') ? (FCPATH . '../website/') : (FCPATH . '../');
        return $base . ltrim((string)$subpath, '/');
    }
}

if (!function_exists('admin_storefront_url')) {
    function admin_storefront_url($subpath = '') {
        $sub = ltrim((string)$subpath, '/');
        return base_url('../website' . ($sub !== '' ? '/' . $sub : ''));
    }
}

if (!function_exists('admin_storefront_asset_url')) {
    function admin_storefront_asset_url($subpath = '') {
        $sub = ltrim((string)$subpath, '/');
        return base_url('../website/assets' . ($sub !== '' ? '/' . $sub : ''));
    }
}

if (!function_exists('base_url')) {
    function base_url($uri = '', $protocol = NULL) {
        $uri_str = (string)$uri;
        if (!is_dir(FCPATH . '../website') && is_dir(FCPATH . '../assets')) {
            if (strpos($uri_str, '../website/') === 0) {
                $uri_str = '../' . substr($uri_str, strlen('../website/'));
            } elseif ($uri_str === '../website') {
                $uri_str = '..';
            }
        }
        if (strpos($uri_str, 'assets/images/logo/assets/') !== false) {
            $uri_str = str_replace('assets/images/logo/assets/', 'assets/', $uri_str);
        }
        if (strpos($uri_str, 'assets/images/assets/') !== false) {
            $uri_str = str_replace('assets/images/assets/', 'assets/', $uri_str);
        }
        return get_instance()->config->base_url($uri_str, $protocol);
    }
}
