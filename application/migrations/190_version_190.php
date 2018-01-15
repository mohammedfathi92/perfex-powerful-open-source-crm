<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_190 extends CI_Migration
{
    public function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        $this->db->query("ALTER TABLE `tbloptions` ADD INDEX(`name`);");
        $this->db->query("ALTER TABLE `tblstaffpermissions` ADD INDEX(`permissionid`);");
        $this->db->query("ALTER TABLE `tblstaffpermissions` ADD INDEX(`staffid`);");
        $this->db->query("ALTER TABLE `tblprojects` ADD INDEX(`name`);");

         add_main_menu_item(array(
              'name' => 'timesheets_overview',
              'permission' => 'is_admin',
              'url' => 'staff/timesheets?view=all',
              'id' => 'reports_timesheets_overview',
              'order'=>5
        ),'reports');

        add_option('hide_notified_reminders_from_calendar','0',0);
        add_option('desktop_notifications','0');
        add_option('task_modal_class','modal-lg');
        add_option('lead_modal_class','modal-lg');
        add_option('show_timesheets_overview_all_members_notice_admins','1',0);

        $this->db->query("ALTER TABLE `tblstafftasks` ADD `is_added_from_contact` BOOLEAN NOT NULL DEFAULT FALSE AFTER `addedfrom`;");
        $this->db->query("ALTER TABLE `tblstafftaskassignees` ADD `is_assigned_from_contact` BOOLEAN NOT NULL DEFAULT FALSE AFTER `assigned_from`;");

        $this->db->query("ALTER TABLE `tblstaff` ADD `last_activity` DATETIME NULL AFTER `last_login`;");

        $this->db->query("ALTER TABLE `tblleadactivitylog` ADD `custom_activity` BOOLEAN NOT NULL DEFAULT FALSE AFTER `full_name`;");

        $this->db->query("ALTER TABLE `tblprojects` ADD `estimated_hours` DECIMAL(11,2) NULL DEFAULT NULL AFTER `project_rate_per_hour`;");

        $this->db->query("ALTER TABLE `tblstaff` ADD `two_factor_auth_enabled` BOOLEAN NULL DEFAULT FALSE AFTER `hourly_rate`, ADD `two_factor_auth_code` VARCHAR(100) NULL AFTER `two_factor_auth_enabled`;");

        $this->db->query("ALTER TABLE `tblstaff` ADD `two_factor_auth_code_requested` DATETIME NULL DEFAULT NULL AFTER `two_factor_auth_code`;");

        $this->db->query("INSERT INTO `tblemailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
('leads', 'new-web-to-lead-form-submitted', 'english', 'Web to lead form submitted - Sent to lead', '{lead_name} - We Received Your Request', 'Hello {lead_name}.<br /><br /><strong>Your request has been received.</strong><br /><br />This email is to let you know that we received your request and we will get back to you as soon as possible with more information.<br /><br />Best Regards,<br />{email_signature}', '{companyname} | CRM', '', 0, 0, 0);");


        $this->db->query("INSERT INTO `tblemailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
('staff', 'two-factor-authentication', 'english', 'Two Factor Authentication', 'Confirm Your Login', '<p>Hello {staff_firstname}</p>\r\n<p style=\"text-align: left;\">You received this email because you have enabled two factor authentication in your account.<br />Use the following code to confirm your login:</p>\r\n<p style=\"text-align: left;\"><span style=\"font-size: 18pt;\"><strong>{two_factor_auth_code}<br /><br /></strong><span style=\"font-size: 12pt;\">{email_signature}</span><strong><br /><br /><br /><br /></strong></span></p>', '{companyname} | CRM', '', 0, 1, 0);");

    $this->db->query("INSERT INTO `tblemailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
('project', 'project-finished-to-customer', 'english', 'Project Marked as Finished (Sent to Customer Contacts)', 'Project Marked as Finished', '<p>Hello&nbsp;{contact_firstname}&nbsp;{contact_lastname}</p>\r\n<p>You are receiving this email because project&nbsp;<strong>{project_name}</strong> has been marked as finished. This project is assigned under your company and we just wanted to keep you up to date.<br /><br />You can view the project on the following link:&nbsp;<a href=\"{project_link}\">{project_name}</a></p>\r\n<p>If you have any questions don''t hesitate to contact us.<br /><br />Kind Regards,<br />{email_signature}</p>', '{companyname} | CRM', '', 0, 1, 0);");

        update_option('update_info_message', '<div class="col-md-12">
        <div class="alert alert-success bold">
            <h4 class="bold">Hi! Thanks for updating Perfex CRM - You are using version 1.9.0</h4>
            <p>
                This window will reload automaticaly in 10 seconds and will try to clear your browser/cloudflare cache, however its recomended to clear your browser cache manually.
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
