<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_195 extends CI_Migration
{
    public function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        if (active_clients_theme() != 'perfex') {
            xcopy(VIEWPATH.'themes/perfex/views/credit_note_pdf.php', VIEWPATH.'themes/'.active_clients_theme().'/views/credit_note_pdf.php');
        }

        $this->db->query("ALTER TABLE `tblitemstax` ADD INDEX(`itemid`);");
        $this->db->query("ALTER TABLE `tblitems_in` ADD INDEX(`id`);");
        $this->db->query("ALTER TABLE `tblprojectsettings` ADD INDEX(`project_id`);");

        $this->db->query("ALTER TABLE `tblcontacts` ADD `credit_note_emails` BOOLEAN NOT NULL DEFAULT TRUE AFTER `estimate_emails`;");
        $this->db->query("INSERT INTO `tblpermissions` ( `name`, `shortname`) VALUES ('Credit notes', 'credit_notes');");

        add_option('show_credits_applied_on_invoice', 1);
        add_option('show_total_paid_on_invoice', 1);
        add_option('show_amount_due_on_invoice', 1);
        add_option('show_credit_note_reminders_on_calendar', 1);
        add_option('show_pdf_signature_credit_note', 1, 0);
        add_option('pdf_format_credit_note', 'A4-PORTRAIT');
        add_option('credit_note_number_decrement_on_delete', 1);
        add_option('credit_note_prefix', 'CN-');
        add_option('next_credit_note_number', 1);

        add_option('predefined_terms_credit_note', '');
        add_option('predefined_clientnote_credit_note', '');


        $this->db->query("ALTER TABLE `tblproposals` ADD `acceptance_firstname` VARCHAR(50) NULL AFTER `is_expiry_notified`, ADD `acceptance_lastname` VARCHAR(50) NULL AFTER `acceptance_firstname`, ADD `acceptance_email` VARCHAR(100) NULL AFTER `acceptance_lastname`, ADD `acceptance_date` DATETIME NULL AFTER `acceptance_email`, ADD `acceptance_ip` VARCHAR(40) NULL AFTER `acceptance_date`;");

        $this->db->query("ALTER TABLE `tblestimates` ADD `acceptance_firstname` VARCHAR(50) NULL AFTER `is_expiry_notified`, ADD `acceptance_lastname` VARCHAR(50) NULL AFTER `acceptance_firstname`, ADD `acceptance_email` VARCHAR(100) NULL AFTER `acceptance_lastname`, ADD `acceptance_date` DATETIME NULL AFTER `acceptance_email`, ADD `acceptance_ip` VARCHAR(40) NULL AFTER `acceptance_date`;");

        add_option('proposal_accept_identity_confirmation', '1', 0);
        add_option('estimate_accept_identity_confirmation', '1', 0);
        add_option('new_task_auto_follower_current_member', '0');
        add_option('task_biillable_checked_on_creation', '1');

        add_option('email_templates_language_checks', '', 0);
        add_option('bcc_emails', '', 0);

        $this->db->where('language', 'english');
        $this->db->where('slug', 'task-added-attachment');
        $this->db->update('tblemailtemplates', array('name'=>'New Attachment(s) on Task (Sent to Staff)'));

        $this->db->where('language', 'english');
        $this->db->where('slug', 'new-project-file-uploaded-to-customer');
        $this->db->update('tblemailtemplates', array('name'=>'New Project File(s) Uploaded (Sent to Customer Contacts)'));

        $this->db->where('language', 'english');
        $this->db->where('slug', 'new-project-file-uploaded-to-staff');
        $this->db->update('tblemailtemplates', array('name'=>'New Project File(s) Uploaded (Sent to Project Members)'));

        $this->db->where('language', 'english');
        $this->db->where('slug', 'task-added-attachment-to-contacts');
        $this->db->update('tblemailtemplates', array('name'=>'New Attachment(s) on Task (Sent to Customer Contacts)'));

        $this->db->query("INSERT INTO `tblemailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
('credit_note', 'credit-note-send-to-client', 'english', 'Send Credit Note To Email', 'Credit Note With Number #{credit_note_number} Created', 'Dear&nbsp;{contact_firstname}&nbsp;{contact_lastname}<br /><br />We have attached the credit note with number <strong>#{credit_note_number} </strong>for your reference.<br /><br /><strong>Date:</strong>&nbsp;{credit_note_date}<br /><strong>Total Amount:</strong>&nbsp;{credit_note_total}<br /><br /><span style=\"font-size: 12pt;\">Please contact us for more information.</span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 1);");

        $this->db->query("CREATE TABLE `tblcreditnotes` (
  `id` int(11) NOT NULL,
  `clientid` int(11) NOT NULL,
  `number` int(11) NOT NULL,
  `prefix` varchar(50) DEFAULT NULL,
  `datecreated` datetime NOT NULL,
  `date` date NOT NULL,
  `adminnote` text,
  `terms` text,
  `clientnote` text,
  `currency` int(11) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `total_tax` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total` decimal(15,2) NOT NULL,
  `adjustment` decimal(15,2) DEFAULT NULL,
  `addedfrom` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT '1',
  `discount_percent` decimal(15,2) DEFAULT '0.00',
  `discount_total` decimal(15,2) DEFAULT '0.00',
  `discount_type` varchar(30) NOT NULL,
  `billing_street` varchar(200) DEFAULT NULL,
  `billing_city` varchar(100) DEFAULT NULL,
  `billing_state` varchar(100) DEFAULT NULL,
  `billing_zip` varchar(100) DEFAULT NULL,
  `billing_country` int(11) DEFAULT NULL,
  `shipping_street` varchar(200) DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_state` varchar(100) DEFAULT NULL,
  `shipping_zip` varchar(100) DEFAULT NULL,
  `shipping_country` int(11) DEFAULT NULL,
  `include_shipping` tinyint(1) NOT NULL,
  `show_shipping_on_credit_note` tinyint(1) NOT NULL DEFAULT '1',
  `show_quantity_as` int(11) NOT NULL DEFAULT '1',
  `reference_no` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $this->db->query("ALTER TABLE `tblcreditnotes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `currency` (`currency`),
  ADD KEY `clientid` (`clientid`);");

        $this->db->query("ALTER TABLE `tblcreditnotes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;");

        $this->db->query("CREATE TABLE `tblcredits` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `credit_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $this->db->query("ALTER TABLE `tblcredits`
  ADD PRIMARY KEY (`id`);");

        $this->db->query("ALTER TABLE `tblcredits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;");

        $this->db->query("ALTER TABLE `tblcreditnotes` ADD `project_id` INT NOT NULL DEFAULT '0' AFTER `status`;");
        $this->db->query("ALTER TABLE `tblcreditnotes` ADD INDEX(`project_id`);");
        $this->db->query("ALTER TABLE `tblcredits` ADD `date_applied` DATETIME NOT NULL AFTER `date`;");
        add_main_menu_item(array(
              'name' => 'credit_notes',
              'permission' => 'credit_notes',
              'url' => 'credit_notes',
              'icon'=>'',
              'id' => 'credit_notes',
              'order'=>4,
        ), 'sales');

        if (!is_dir(CREDIT_NOTES_ATTACHMENTS_FOLDER)) {
            mkdir(CREDIT_NOTES_ATTACHMENTS_FOLDER);
            fopen(CREDIT_NOTES_ATTACHMENTS_FOLDER . '.htaccess', 'w');
            $fp = fopen(CREDIT_NOTES_ATTACHMENTS_FOLDER.'.htaccess', 'a+');
            if ($fp) {
                fwrite($fp, 'Order Deny,Allow'.PHP_EOL.'Deny from all');
                fclose($fp);
            }
        }

        update_option('update_info_message', '<div class="col-md-12">
            <div class="alert alert-success bold">
                <h4 class="bold">Hi! Thanks for updating Perfex CRM - You are using version 1.9.5</h4>
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
