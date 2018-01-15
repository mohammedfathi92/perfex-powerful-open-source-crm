<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_122 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up(){

        if(file_exists(APPPATH.'controllers/update.php')){
            @unlink(APPPATH.'controllers/update.php');
        }

        if (file_exists(APPPATH . 'views/themes/' . active_clients_theme() . '/views/stripe_payment.php')) {
            @unlink(APPPATH . 'views/themes/' . active_clients_theme() . '/views/stripe_payment.php');
        }

        $this->db->query("CREATE TABLE IF NOT EXISTS `tblsessions` (
            `id` varchar(40) NOT NULL,
            `ip_address` varchar(45) NOT NULL,
            `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
            `data` blob NOT NULL,
            KEY `ci_sessions_timestamp` (`timestamp`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $config_path = APPPATH . 'config/config.php';
        $this->load->helper('file');
        @chmod($config_path, FILE_WRITE_MODE);
        $config_file = read_file($config_path);
        $config_file = trim($config_file);
        $config_file = str_replace("\$config['sess_driver'] = 'files';", "\$config['sess_driver'] = 'database';", $config_file);
        $config_file = str_replace("\$config['sess_save_path'] = sys_get_temp_dir();", "\$config['sess_save_path'] = 'tblsessions';", $config_file);
        if (!$fp = fopen($config_path, FOPEN_WRITE_CREATE_DESTRUCTIVE)) {
            return FALSE;
        }
        flock($fp, LOCK_EX);
        fwrite($fp, $config_file, strlen($config_file));
        flock($fp, LOCK_UN);
        fclose($fp);
        @chmod($config_path, FILE_READ_MODE);


        $this->db->query("ALTER TABLE `tblmilestones` ADD `description` TEXT NULL AFTER `name`;");
        $this->db->query("ALTER TABLE `tblmilestones` ADD `description_visible_to_customer` BOOLEAN NULL DEFAULT FALSE AFTER `description`;");
        $this->db->query("ALTER TABLE `tblestimates` ADD `project_id` INT NOT NULL DEFAULT '0' AFTER `clientid`;");

        $this->db->query("ALTER TABLE `tblsalesattachments` CHANGE `file_name` `file_name` VARCHAR(300) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");

        $this->db->query("ALTER TABLE `tblcustomfields` ADD `allow_client_to_edit` INT NOT NULL DEFAULT '1' AFTER `show_on_client_portal`;");
        $this->db->query("ALTER TABLE `tblcustomfields` CHANGE `allow_client_to_edit` `disalow_client_to_edit` INT(11) NOT NULL DEFAULT '0';");

        $this->db->query("ALTER TABLE `tblinvoices` ADD `recurring_type` VARCHAR(10) NULL AFTER `recurring`, ADD `custom_recurring` BOOLEAN NOT NULL DEFAULT FALSE AFTER `recurring_type`;");

        update_option('update_info_message', '<script>window.location.reload();</script>');
    }
}
