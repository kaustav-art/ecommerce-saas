<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Loader extends CI_Loader {

    public function can($permission)
    {
        $CI =& get_instance();
        return $CI->can($permission);
    }

    public function is_logged_in()
    {
        $CI =& get_instance();
        return $CI->is_logged_in();
    }
}
