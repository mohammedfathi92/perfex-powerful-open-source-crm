<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_180 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        $this->db->query("ALTER TABLE `tbloptions` ADD `autoload` BOOLEAN NOT NULL DEFAULT TRUE AFTER `value`;");

        add_option('email_header','');
        add_option('email_footer','');
        add_option('allow_staff_view_proposals_assigned',0);

        add_option('pusher_cluster','');
        add_option('pusher_app_key','');
        add_option('pusher_app_secret','');
        add_option('pusher_app_id','');
        add_option('pusher_realtime_notifications','0');
        add_option('pdf_format_statement','A4-PORTRAIT');
        add_option('show_table_export_button','to_all');
        add_option('exclude_proposal_from_client_area_with_draft_status','0');
        add_option('show_cloudflare_notice','0',0);

        $this->db->query("ALTER TABLE `tblcustomfields` ADD `display_inline` BOOLEAN NOT NULL DEFAULT FALSE AFTER `options`;");
        $this->db->query("ALTER TABLE `tblvault` ADD `share_in_projects` BOOLEAN NOT NULL DEFAULT FALSE AFTER `visibility`;");

        $this->db->query("ALTER TABLE `tblsalesactivity` CHANGE `additional_data` `additional_data` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;");


        $this->db->query("ALTER TABLE `tblexpenses` ADD INDEX(`currency`);");

        $this->db->where('name','next_estimate_number');
        $this->db->update('tbloptions',array('autoload'=>0));

        $this->db->where('name','next_invoice_number');
        $this->db->update('tbloptions',array('autoload'=>0));

        $this->db->like('name','paymentmethod_','after');
        $this->db->update('tbloptions',array('autoload'=>0));

        $this->db->query("ALTER TABLE `tblcustomfields` ADD `show_on_ticket_form` BOOLEAN NOT NULL DEFAULT FALSE AFTER `show_on_pdf`;");

        $this->db->where('fieldto','tickets');
        $this->db->update('tblcustomfields',array('show_on_ticket_form'=>1));

        $this->db->query("INSERT INTO `tblemailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
('client', 'client-statement', 'english', 'Statement - Account Summary', 'Account Statement from {statement_from} to {statement_to}', 'Dear {contact_firstname} {contact_lastname}, <br /><br />Its been a great experience working with you.<br /><br />Attached with this email is a list of all transactions for the period between {statement_from} to {statement_to}<br /><br />For your information your account balance due is total:&nbsp;{statement_balance_due}<br /><br />Please contact us if you need more information.<br /> <br />Kind regards,<br /> <br />{email_signature}', '{companyname} | CRM', '', 0, 1, 0);");

    $this->db->query("INSERT INTO `tblemailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
('ticket', 'ticket-assigned-to-admin', 'english', 'New Ticket Assigned (Sent to Staff)', 'New support ticket has been assigned to you', '<p><span style=\"font-size: 12pt;\">Hi.</span></p>\r\n<p><span style=\"font-size: 12pt;\">A new support ticket&nbsp;has been assigned to you.</span><br /> <br /><span style=\"font-size: 12pt;\"><strong>Subject</strong>: {ticket_subject}</span><br /><span style=\"font-size: 12pt;\"><strong>Department</strong>: {ticket_department}</span><br /><span style=\"font-size: 12pt;\"><strong>Priority</strong>: {ticket_priority}</span><br /> <br /><span style=\"font-size: 12pt;\"><strong>Ticket message:</strong></span><br /><span style=\"font-size: 12pt;\">{ticket_message}</span><br /> <br /><span style=\"font-size: 12pt;\">You can view the ticket on the following link: <a href=\"{ticket_url}\">#{ticket_id}</a></span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span></p>', '{companyname} | CRM', '', 0, 1, 0);");

    $this->db->query("INSERT INTO `tblemailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
('client', 'new-client-registered-to-admin', 'english', 'New Customer Registration (Sent to admins)', 'New Customer Registration', 'Hello.<br /><br />New customer registration on your customer portal:<br /><br /><strong>Firstname:</strong>&nbsp;{contact_firstname}<br /><strong>Lastname:</strong>&nbsp;{contact_lastname}<br /><strong>Company:</strong>&nbsp;{client_company}<br /><strong>Email:</strong>&nbsp;{contact_email}<br /><br />Best Regards', '{companyname} | CRM', '', 0, 1, 0);");


        update_option('update_info_message', '<div class="col-md-12">
            <div class="alert alert-success bold">
                <h4 class="bold">Hi! Thanks for updating Perfex CRM - You are using version 1.8.0</h4>
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
