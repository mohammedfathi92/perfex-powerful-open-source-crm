<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_194 extends CI_Migration
{
    public function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        $this->db->query("ALTER TABLE `tblcontacts` ADD `invoice_emails` BOOLEAN NOT NULL DEFAULT TRUE AFTER `direction`, ADD `estimate_emails` BOOLEAN NOT NULL DEFAULT TRUE AFTER `invoice_emails`, ADD `contract_emails` BOOLEAN NOT NULL DEFAULT TRUE AFTER `estimate_emails`, ADD `task_emails` BOOLEAN NOT NULL DEFAULT TRUE AFTER `contract_emails`, ADD `project_emails` BOOLEAN NOT NULL DEFAULT TRUE AFTER `task_emails`;");

        $this->db->query("UPDATE tblcontacts SET invoice_emails=0 WHERE id NOT IN (SELECT userid FROM tblcontactpermissions WHERE permission_id=1)");

         $this->db->query("UPDATE tblcontacts SET estimate_emails=0 WHERE id NOT IN (SELECT userid FROM tblcontactpermissions WHERE permission_id=2)");

         $this->db->query("UPDATE tblcontacts SET contract_emails=0 WHERE id NOT IN (SELECT userid FROM tblcontactpermissions WHERE permission_id=3)");

         $this->db->query("UPDATE tblcontacts SET task_emails=0 WHERE id NOT IN (SELECT userid FROM tblcontactpermissions WHERE permission_id=6)");

         $this->db->query("UPDATE tblcontacts SET project_emails=0 WHERE id NOT IN (SELECT userid FROM tblcontactpermissions WHERE permission_id=6)");

        $this->db->query("ALTER TABLE `tblinvoices` ADD INDEX(`sale_agent`);");
        $this->db->query("ALTER TABLE `tblestimates` ADD INDEX(`sale_agent`);");
        $this->db->query("ALTER TABLE `tblpinnedprojects` ADD INDEX(`project_id`);");
        $send_renewed_invoice_from_recurring_to_email = get_option('send_renewed_invoice_from_recurring_to_email');
        add_option('new_recurring_invoice_action', 'generate_and_send', 0);

        if($send_renewed_invoice_from_recurring_to_email == '0'){
          update_option('new_recurring_invoice_action','generate_unpaid');
        }

        add_option('ticket_replies_order', 'asc', 0);
        add_option('default_task_status', 'auto');
        add_option('email_queue_enabled', '0');
        add_option('email_queue_skip_with_attachments', '1');
        add_option('auto_dismiss_desktop_notifications_after', '0');
        add_option('last_email_queue_retry', '');
        delete_option('newsfeed_maximum_file_size');
        delete_option('send_renewed_invoice_from_recurring_to_email');
        $proposalInfoFormat = "{proposal_to}<br />\r
{address}<br />\r
{city} {state}<br />\r
{country_code} {zip_code}<br />\r
{phone}<br />\r
{email}<br />";

        $cfProposals = get_custom_fields('proposal', array('show_on_pdf'=>1));
        foreach ($cfProposals as $f) {
            $proposalInfoFormat .= PHP_EOL.'{cf_'.$f['id'].'}<br />';
        }

        $proposalInfoFormat = preg_replace('/(<br \/>)+$/', '', $proposalInfoFormat);

        add_option('proposal_info_format', $proposalInfoFormat,0);

        $this->db->query("CREATE TABLE `tblemailqueue` (
                  `id` int(11) NOT NULL,
                  `email` varchar(500) NOT NULL,
                  `cc` varchar(500) DEFAULT NULL,
                  `bcc` varchar(500) DEFAULT NULL,
                  `message` mediumtext NOT NULL,
                  `alt_message` mediumtext,
                  `status` enum('pending','sending','sent','failed') DEFAULT NULL,
                  `date` datetime DEFAULT NULL,
                  `headers` text,
                  `attachments` mediumtext
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $this->db->query("ALTER TABLE `tblemailqueue` ADD PRIMARY KEY (`id`);");
        $this->db->query("ALTER TABLE `tblemailqueue` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");

        $this->db->where('fieldto','proposal');
        $this->db->where('show_on_pdf',1);
        $this->db->update('tblcustomfields',array('show_on_client_portal'=>1));

        $this->db->where('fieldto','invoice');
        $this->db->where('show_on_pdf',1);
        $this->db->update('tblcustomfields',array('show_on_client_portal'=>1));

        $this->db->where('fieldto','estimate');
        $this->db->where('show_on_pdf',1);
        $this->db->update('tblcustomfields',array('show_on_client_portal'=>1));

        update_option('update_info_message', '<div class="col-md-12">
            <div class="alert alert-success bold">
                <h4 class="bold">Hi! Thanks for updating Perfex CRM - You are using version 1.9.4</h4>
                <p>
                    This window will reload automaticaly in 10 seconds and will try to clear your browser/cloudflare cache, however its recomended to clear your browser cache manually.
                </p>
            </div>
        </div>
        <script>
            setTimeout(function(){
                window.location.reload();
            },10000);
        </script>');
    }
}
