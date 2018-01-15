<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_102 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        // Add the invoice discount fields
        $this->db->query("ALTER TABLE `tblinvoices` ADD `discount_percent` INT NOT NULL DEFAULT '0' AFTER `token`, ADD `discount_type` VARCHAR(30) NOT NULL AFTER `discount_percent`;");
        $this->db->query("ALTER TABLE `tblinvoices` ADD `discount_total` DECIMAL(11,2) NULL DEFAULT '0' AFTER `discount_percent`;");

        // Add RTL option
        add_option('rtl_support_admin', 0);
        add_option('rtl_support_client', 0);
        add_option('allow_payment_amount_to_be_modified', 1);
        add_option('survey_send_emails_per_cron_run', 250);
        add_option('delete_only_on_last_invoice', 1);
        add_option('last_cron_run', '');
        add_option('last_recurring_invoices_cron', '');

        // Estimates - new feature
        $this->db->query("CREATE TABLE IF NOT EXISTS `tblestimates` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `sent` tinyint(1) NOT NULL DEFAULT '0',
                    `datesend` datetime DEFAULT NULL,
                    `clientid` int(11) NOT NULL,
                    `number` int(11) NOT NULL,
                    `year` int(11) NOT NULL,
                    `datecreated` datetime NOT NULL,
                    `date` date NOT NULL,
                    `expirydate` date DEFAULT NULL,
                    `currency` decimal(11,2) NOT NULL,
                    `subtotal` decimal(11,2) NOT NULL,
                    `total` decimal(11,2) NOT NULL,
                    `adjustment` decimal(11,2) DEFAULT NULL,
                    `addedfrom` int(11) NOT NULL,
                    `status` int(11) NOT NULL DEFAULT '1',
                    `clientnote` text,
                    `adminnote` text,
                    `discount_percent` int(11) DEFAULT NULL,
                    `discount_total` int(11) DEFAULT NULL,
                    `discount_type` int(11) DEFAULT NULL,
                    `invoiceid` int(11) DEFAULT NULL,
                    `invoiced_date` datetime DEFAULT NULL,
                    `terms` text,
                    `reference_no` varchar(100) DEFAULT NULL,
                    PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        $this->db->query("UPDATE `tbloptions` SET `name` = 'decimal_separator' WHERE `tbloptions`.`name` = 'invoice_decimal_separator';");
        $this->db->query("UPDATE `tbloptions` SET `name` = 'thousand_separator' WHERE `tbloptions`.`name` = 'invoice_thousand_separator';");
        $this->db->query("UPDATE `tbloptions` SET `name` = 'currency_placement' WHERE `tbloptions`.`name` = 'invoice_currency_placement';");

        // Add options for estimates
        add_option('delete_only_on_last_estimate', 1);
        add_option('estimate_prefix', 'EST-');
        add_option('next_estimate_number', 1);
        add_option('estimate_number_decrement_on_delete', 1);
        add_option('estimate_number_format', 1);
        add_option('estimate_year', date('Y'));
        add_option('estimate_auto_convert_to_invoice_on_client_accept', 1);
        add_option('exclude_estimate_from_client_area_with_draft_status', 1);

        $this->db->query("CREATE TABLE IF NOT EXISTS `tblestimateitems` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `estimateid` int(11) NOT NULL,
                    `itemid` int(11) NOT NULL,
                    `qty` int(11) NOT NULL,
                    PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tblestimateactivity` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `estimateid` int(11) NOT NULL,
                    `description` text NOT NULL,
                    `staffid` varchar(11) DEFAULT NULL,
                    `date` datetime NOT NULL,
                    PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        // add terms to invoices
        $this->db->query('ALTER TABLE `tblinvoices` ADD `terms` TEXT NULL AFTER `last_recurring_date`;');
        // add translator permission
        $this->db->query("INSERT INTO `tblpermissions` (`permissionid`, `name`, `shortname`) VALUES (NULL, 'Translate', 'isTranslator');");

        // New feature admin client notifications
        $this->db->query('CREATE TABLE IF NOT EXISTS `tbladminclientreminders` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `description` text,
                      `date` date NOT NULL,
                      `isnotified` int(11) NOT NULL DEFAULT "0",
                      `clientid` int(11) NOT NULL,
                      `staff` int(11) NOT NULL,
                      `notify_by_email` int(11) NOT NULL DEFAULT "1",
                      `creator` int(11) NOT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;');

        // Add 3 new email templates
        $this->db->query("INSERT INTO `tblemailtemplates` (`emailtemplateid`, `type`, `slug`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
            (10, 'estimate', 'estimate-send-to-client', 'When sending estimate to client', '{estimate_number} - {companyname}', '<p>Dear {client_firstname}&nbsp;{client_lastname}<br /><br />Find the estimate with number {estimate_number} on attach.<br />This estimate&nbsp;is with status:&nbsp;<strong>{estimate_status}</strong><br /><br />We look forward to doing more business with you.<br />Best Regards</p>\r\n<p>{email_signature}</p>', 'Company', 'company@test.com', 0, 1, 0),
            (11, 'estimate', 'estimate-already-send', 'Estimate Already Send to Client', 'On your command here is the estimate', '<p>On your command here is the estimate you asked for.<br />{estimate_number}<br />{email_signature}</p>', 'Company', 'sales@test.com', 0, 1, 0),
            (12, 'ticket', 'ticket-reply-to-admin', 'Ticket Reply (To admin)', 'New Ticket Reply', '{signature}', 'Company', 'info@test.com', 0, 1, 0);");

    }

}
