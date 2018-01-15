<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_170 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {

        $this->db->query("ALTER TABLE `tblitems` ADD `tax2` INT NOT NULL DEFAULT '0' AFTER `tax`;");
        $this->db->query("ALTER TABLE `tblitems` ADD INDEX(`tax`);");
        $this->db->query("ALTER TABLE `tblitems` ADD INDEX(`tax2`);");

        $this->db->query("ALTER TABLE `tblitems` CHANGE `tax2` `tax2` INT(11) NULL DEFAULT NULL;");

        $this->db->query("ALTER TABLE `tblreminders` ADD INDEX(`rel_id`);");
        $this->db->query("ALTER TABLE `tblreminders` ADD INDEX(`rel_type`);");

        $this->db->query("ALTER TABLE `tblevents` ADD `reminder_before` INT NOT NULL DEFAULT '0' AFTER `isstartnotified`;");
        $this->db->query("ALTER TABLE `tblevents` ADD `reminder_before_type` VARCHAR(10) NULL AFTER `reminder_before`;");

        $this->db->update('tblevents',array('reminder_before_type'=>'minutes'));

        add_option('contract_expiry_reminder_enabled',1);
        add_option('time_format',24);
        add_option('delete_activity_log_older_then',2);
        add_option('disable_language',0);
        add_option('new_task_auto_assign_current_member',0);
        add_option('company_state','');

        $this->db->where('name','calendar_task_color');
        $this->db->delete('tbloptions');

        $default_tax = get_option('default_tax');
        if($default_tax != ''){
            $default_tax = explode('+',$default_tax);
            $i = 0;
            foreach($default_tax as $d){
                if($d == ''){
                    unset($default_tax[$i]);
                }
                $i++;
            }
            $default_tax = array_values($default_tax);
        } else {
            $default_tax = array();
        }
        update_option('default_tax',serialize($default_tax));

        $this->db->query("ALTER TABLE `tblnotifications` ADD `isread_inline` BOOLEAN NOT NULL DEFAULT FALSE AFTER `isread`;");
        $this->db->query("ALTER TABLE `tblexpenses` ADD `tax2` INT NOT NULL DEFAULT '0' AFTER `tax`;");

        $this->db->where('isread',1);
        $this->db->update('tblnotifications',array('isread_inline'=>1));

        $this->db->query("ALTER TABLE `tbltaskstimers` ADD `note` TEXT NULL AFTER `hourly_rate`;");
        $this->db->query("ALTER TABLE `tblleads` ADD `default_language` VARCHAR(40) NULL AFTER `is_public`;");
        $this->db->query("ALTER TABLE `tblleadsintegration` ADD `mark_public` INT NOT NULL DEFAULT '0' AFTER `notify_ids`;");
        $this->db->query("ALTER TABLE `tblwebtolead` ADD `mark_public` INT NOT NULL DEFAULT '0' AFTER `allow_duplicate`;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tblvault` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `customer_id` int(11) NOT NULL,
                      `server_address` varchar(400) NOT NULL,
                      `port` int(11) DEFAULT NULL,
                      `username` varchar(300) NOT NULL,
                      `password` text NOT NULL,
                      `description` text,
                      `creator` int(11) NOT NULL,
                      `creator_name` varchar(100) DEFAULT NULL,
                      `visibility` tinyint(1) NOT NULL DEFAULT '1',
                      `last_updated` datetime DEFAULT NULL,
                      `last_updated_from` varchar(100) DEFAULT NULL,
                      `date_created` datetime NOT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");


         update_option('update_info_message', '<div class="col-md-12">
            <div class="alert alert-success bold">
                <h4 class="bold">Hi! Thanks for updating Perfex CRM - You are using version 1.7.0</h4>
                <p>
                    This window will reload automaticaly in 10 seconds and will try to clear your browser cache, however its recomended to clear your browser cache manually.
                </p>
            </div>
        </div>
        <script>
            setTimeout(function(){
                window.location.reload();
            },10000);
        </script>
        ');
    }
}
