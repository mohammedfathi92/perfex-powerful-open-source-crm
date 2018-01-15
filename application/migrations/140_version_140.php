<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_140 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
       add_option('show_page_number_on_pdf',0);
       add_option('calendar_events_limit',4);
       add_option('show_setup_menu_item_only_on_hover',0);
       add_option('company_requires_vat_number_field',1);
       add_option('company_is_required',1);
       add_option('allow_contact_to_delete_files',0);
       add_option('expenses_auto_operations_hour',21);
       add_option('invoice_auto_operations_hour',21);
       add_option('company_vat','');
       add_option('use_minified_files',1);
       add_option('di','');

       $this->db->where('name','last_recurring_invoices_cron');
       $this->db->delete('tbloptions');

       $this->db->where('name','last_recurring_expenses_cron');
       $this->db->delete('tbloptions');

       $this->db->where('staffid',1);
       $st = $this->db->get('tblstaff')->row();
       if($st){
          update_option('di',strtotime($st->datecreated));
       }
       $this->db->query("ALTER TABLE `tblleads` ADD `description` TEXT NULL AFTER `company`;");
       $this->db->query("ALTER TABLE `tblleads` ADD `website` VARCHAR(150) NULL AFTER `email`;");

       $this->db->query("ALTER TABLE `tblinvoicepaymentsmodes` ADD `selected_by_default` INT NOT NULL DEFAULT '1' AFTER `expenses_only`;");


        $this->db->select('id,additional_data');
        $this->db->where('description','not_assigned_lead_to_you');
        $not = $this->db->get('tblnotifications')->result_array();

        foreach($not as $n){
            $id = $n['id'];
            if(!empty($n['additional_data'])){
                $n = @unserialize($n['additional_data']);
                if(is_array($n)){
                    unset($n[0]);
                    array_values(($n));
                    $this->db->where('id',$id);
                    $this->db->update('tblnotifications',array('additional_data'=>serialize($n)));
                }
            }
        }

       $this->db->query("ALTER TABLE `tblknowledgebase` ADD `staff_article` INT NOT NULL DEFAULT '0' AFTER `article_order`;");

       $this->db->query("ALTER TABLE `tblclients` CHANGE `company` `company` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;");

       $this->db->query("CREATE TABLE IF NOT EXISTS `tblitems_groups` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `name` varchar(50) NOT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

       $this->db->query("ALTER TABLE `tblitems` ADD `group_id` INT NOT NULL DEFAULT '0' AFTER `tax`;");

       $this->db->query("ALTER TABLE `tblitems` ADD `unit` VARCHAR(40) NULL AFTER `tax`;");
       $this->db->query("ALTER TABLE `tblitems_in` ADD `unit` VARCHAR(40) NULL AFTER `rate`;");

       $this->db->query("ALTER TABLE `tblcontacts` ADD INDEX(`is_primary`);");

       $this->db->query("ALTER TABLE `tblcontractrenewals` ADD `renewed_by_staff_id` INT NOT NULL DEFAULT '0' AFTER `renewed_by`;");

       $this->db->query("ALTER TABLE `tblstaffpermissions` ADD `can_view_own` BOOLEAN NOT NULL DEFAULT FALSE AFTER `can_view`;");
       $this->db->query("ALTER TABLE `tblrolepermissions` ADD `can_view_own` BOOLEAN NOT NULL DEFAULT FALSE AFTER `can_view`;");

       $this->db->query("ALTER TABLE `tblitems` ADD INDEX(`group_id`);");

       $this->db->query("RENAME TABLE `tblleadsemailintegration` TO `tblleadsintegration`;");
       $this->db->query("ALTER TABLE  `tblleads` ADD  `from_form_id` INT NOT NULL DEFAULT  '0' AFTER  `dateadded` ;");
       $this->db->query("ALTER TABLE `tblleads` ADD INDEX(`from_form_id`);");

       $this->db->query("CREATE TABLE IF NOT EXISTS `tblwebtolead` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `form_key` varchar(32) NOT NULL,
                        `lead_source` int(11) NOT NULL,
                        `lead_status` int(11) NOT NULL,
                        `notify_lead_imported` int(11) NOT NULL DEFAULT '1',
                        `notify_type` varchar(20) DEFAULT NULL,
                        `notify_ids` mediumtext,
                        `responsible` int(11) NOT NULL DEFAULT '0',
                        `name` varchar(400) NOT NULL,
                        `form_data` mediumtext,
                        `recaptcha` int(11) NOT NULL DEFAULT '0',
                        `submit_btn_name` varchar(40) DEFAULT NULL,
                        `success_submit_msg` text,
                        `language` varchar(40) DEFAULT NULL,
                        `allow_duplicate` int(11) NOT NULL DEFAULT '1',
                        `track_duplicate_field` varchar(20) DEFAULT NULL,
                        `track_duplicate_field_and` varchar(20) DEFAULT NULL,
                        `create_task_on_duplicate` int(11) NOT NULL DEFAULT '0',
                        `dateadded` datetime NOT NULL,
                        PRIMARY KEY (`id`)
                      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

       $this->db->query("ALTER TABLE `tblleads` ADD INDEX(`from_form_id`);");

       $this->db->query("INSERT INTO `tblemailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
('leads', 'new-lead-assigned', 'english', 'New Lead Assigned to Staff Member', 'New lead assigned to you', '<p>Hello {lead_assigned}</p>\r\n<p>New&nbsp;lead is assigned to you.<br /><br />You can view the lead on the following link: <a href=\"{lead_link}\">{lead_name}<br /><br /></a>Kind Regards,<br />{email_signature}</p>', '{companyname} | CRM', '', 0, 1, 0);");

       $this->db->query("ALTER TABLE `tbldismissedannouncements` ADD INDEX(`announcementid`);");

          add_setup_menu_item(array(
            'name'=>'web_to_lead',
            'permission'=>'is_admin',
            'icon'=>'',
            'url'=>'leads/forms',
            'id'=>'web-to-lead',
            ),'leads');

       update_option('update_info_message', '<div class="col-md-12">
            <div class="alert alert-success bold">
                <h4 class="bold">Hi! Thanks for updating Perfex CRM - You are using version 1.4.0</h4>
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
