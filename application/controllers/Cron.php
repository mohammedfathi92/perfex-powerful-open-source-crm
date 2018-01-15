<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Cron extends CRM_Controller
{
    public function __construct()
    {
        parent::__construct();
        update_option('cron_has_run_from_cli', 1);

    }

    public function index($key = "")
    {
        if(defined('APP_CRON_KEY') && (APP_CRON_KEY != $key)){
            header('HTTP/1.0 401 Unauthorized');
            die('Passed cron job key is not correct. The cron job key should be the same like the one defined in APP_CRON_KEY constant.');
        }

        $last_cron_run = get_option('last_cron_run');
        if ($last_cron_run == '' || (time() > ($last_cron_run + do_action('cron_functions_execute_seconds',300)))) {
            do_action('before_cron_run');

            $this->load->model('cron_model');
            $this->cron_model->run();

            do_action('after_cron_run');
        }

    }
}
