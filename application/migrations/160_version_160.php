<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_160 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        add_option('default_view_calendar','month');
        add_option('delete_backups_older_then',0);
        add_option('staff_members_open_tickets_to_all_contacts',1);

        $this->db->query("CREATE TABLE IF NOT EXISTS `tbltags` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(100) NOT NULL,
          PRIMARY KEY (`id`)
          ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tbltags_in` (
          `rel_id` int(11) NOT NULL,
          `rel_type` varchar(20) NOT NULL,
          `tag_id` int(11) NOT NULL,
          `tag_order` int(11) NOT NULL DEFAULT '0',
          KEY `rel_id` (`rel_id`),
          KEY `rel_type` (`rel_type`),
          KEY `tag_id` (`tag_id`)
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $this->db->query("ALTER TABLE `tblprojectfiles` ADD `external` VARCHAR(40) NULL AFTER `contact_id`, ADD `external_link` TEXT NULL AFTER `external`, ADD `thumbnail_link` TEXT NULL AFTER `external_link`;");
        $this->db->query("ALTER TABLE `tblclients` ADD `show_primary_contact` INT NOT NULL DEFAULT '0' AFTER `default_currency`;");

        $this->db->query("ALTER TABLE `tbltaskstimers` ADD INDEX(`staff_id`);");
        $this->db->query("ALTER TABLE `tblprojects` ADD `date_finished` DATETIME NULL AFTER `project_created`;");

        $this->db->where('name','email_piping_enabled');
        $this->db->delete('tbloptions');

        update_option('update_info_message', '<div class="col-md-12">
            <div class="alert alert-success bold">
                <h4 class="bold">Hi! Thanks for updating Perfex CRM - You are using version 1.6.0</h4>
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
