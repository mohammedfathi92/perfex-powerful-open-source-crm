<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CRM_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        /**
         * Fix for users who don't replace all files during update !!!
         */
        if(!class_exists('ForceUTF8\Encoding') && file_exists(APPPATH.'vendor/autoload.php')) {
            require_once(APPPATH.'vendor/autoload.php');
        }

        if (is_dir(FCPATH . 'install') && ENVIRONMENT != 'development') {
            echo '<h3>Delete the install folder</h3>';
            die;
        }

        $this->db->reconnect();
        $timezone = get_option('default_timezone');
        if ($timezone != '') {
            date_default_timezone_set($timezone);
        }

        do_action('app_init');
    }
}
