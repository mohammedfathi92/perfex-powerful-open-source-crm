<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_104 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        add_option('last_cron_run','');
        add_option('show_sale_agent_on_invoices',1);
        add_option('show_sale_agent_on_estimates',1);
        add_option('predefined_terms_invoice','');
        add_option('predefined_terms_estimate','');
        add_option('predefined_clientnote_invoice','');
        add_option('predefined_clientnote_estimate','');
        add_option('custom_pdf_logo_image_url','');
        add_option('last_recurring_expenses_cron',time());
        add_option('number_padding_invoice_and_estimate',6);

        // Add contract mime type
        $this->db->query("ALTER TABLE `tblcontractattachments` ADD `filetype` VARCHAR(50) NULL AFTER `file_name`;");

        // Add ticket attachment mime type
        $this->db->query("ALTER TABLE `tblticketattachments` ADD `filetype` VARCHAR(50) NULL AFTER `filename`;");
        // Invoice item long description
        $this->db->query("ALTER TABLE `tblinvoiceitemslist` ADD `long_description` TEXT NULL AFTER `description`;");

        // Add custom fields show on pdf for customers/invoice/estimate etc..
        $this->db->query("ALTER TABLE  `tblcustomfields` ADD  `show_on_pdf` INT NULL DEFAULT  '0' AFTER  `active` ;");

        $this->db->query("ALTER TABLE `tblcontracts` CHANGE `dateadded` `dateadded` DATETIME NOT NULL;");
        $this->db->query("ALTER TABLE `tblcontracts` ADD `contract_value` DECIMAL(11,2) NULL AFTER `isexpirynotified`;");
        $this->db->query("ALTER TABLE `tblcontracts` ADD `trash` BOOLEAN NULL DEFAULT FALSE AFTER `contract_value`;");
        // Add date converted to leads
        $this->db->query("ALTER TABLE `tblleads` ADD `date_converted` DATETIME NULL AFTER `notes`;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tblcontractrenewals` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `contractid` int(11) NOT NULL,
                          `old_start_date` date NOT NULL,
                          `new_start_date` date NOT NULL,
                          `old_end_date` date NOT NULL,
                          `new_end_date` date NOT NULL,
                          `old_value` decimal(11,2) DEFAULT NULL,
                          `new_value` decimal(11,2) DEFAULT NULL,
                          `date_renewed` datetime NOT NULL,
                          `renewed_by` int(11) NOT NULL,
                          PRIMARY KEY (`id`)
                          ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        $this->db->query("ALTER TABLE `tblinvoices` ADD `sale_agent` INT NOT NULL DEFAULT '0' AFTER `terms`;");
        $this->db->query("ALTER TABLE `tblestimates` ADD `sale_agent` INT NOT NULL DEFAULT '0' AFTER `reference_no`;");

        // Add permission goals
        $this->db->query("INSERT INTO `tblpermissions` (`permissionid`, `name`, `shortname`) VALUES (NULL, 'Manage Goals', 'manageGoals');");

        $this->db->query("INSERT INTO `tblpermissions` (`permissionid`, `name`, `shortname`) VALUES (NULL, 'Manage Expenses', 'manageExpenses');");

        if(!is_dir(INVOICE_ATTACHMENTS_FOLDER)){
            mkdir(INVOICE_ATTACHMENTS_FOLDER);
            fopen(INVOICE_ATTACHMENTS_FOLDER . '.htaccess', 'w');
            $fp = fopen(INVOICE_ATTACHMENTS_FOLDER.'.htaccess','a+');
            if($fp)
            {
                fwrite($fp,'Order Deny,Allow'.PHP_EOL.'Deny from all');
                fclose($fp);
            }
        }

        if(!is_dir(EXPENSE_ATTACHMENTS_FOLDER)){
          mkdir(EXPENSE_ATTACHMENTS_FOLDER);
          fopen(EXPENSE_ATTACHMENTS_FOLDER . '.htaccess', 'w');
          $fp = fopen(EXPENSE_ATTACHMENTS_FOLDER.'.htaccess','a+');
          if($fp)
          {
            fwrite($fp,'Order Deny,Allow'.PHP_EOL.'Deny from all');
            fclose($fp);
          }
        }

         if(!is_dir(LEAD_ATTACHMENTS_FOLDER)){
          mkdir(LEAD_ATTACHMENTS_FOLDER);
          fopen(LEAD_ATTACHMENTS_FOLDER . '.htaccess', 'w');
          $fp = fopen(LEAD_ATTACHMENTS_FOLDER.'.htaccess','a+');
          if($fp)
          {
            fwrite($fp,'Order Deny,Allow'.PHP_EOL.'Deny from all');
            fclose($fp);
          }
        }

        // Add expense category id column to invoiec items
        $this->db->query("ALTER TABLE `tblinvoiceitems` ADD `expenseid` INT NOT NULL DEFAULT '0' AFTER `itemid`;");
        // Add taxes decimal
        $this->db->query("ALTER TABLE `tbltaxes` CHANGE `taxrate` `taxrate` DECIMAL(11,2) NOT NULL;");
        // Add notifications link
        $this->db->query("ALTER TABLE `tblnotifications` ADD `link` MEDIUMTEXT NULL AFTER `fromcompany`;");

        // Remove the translator
        if(is_dir(APPPATH . 'views/admin/translator')){
          delete_dir(APPPATH . 'views/admin/translator');
        }
        if(is_file(APPPATH . 'controllers/admin/Translator.php')){
          unlink(APPPATH . 'controllers/admin/Translator.php');
        }
        if(is_file(APPPATH . 'controllers/admin/._translator.php')){
          unlink(APPPATH . 'controllers/admin/._translator.php');
        }
        if(is_file(APPPATH . 'config/translator.php')){
          unlink(APPPATH . 'config/translator.php');
        }

        if(is_file(APPPATH . 'controllers/Reset_demo.php')){
          unlink(APPPATH . 'controllers/Reset_demo.php');
        }

        // Add invoice attachments table
        $this->db->query("CREATE TABLE IF NOT EXISTS `tblinvoiceattachments` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `invoiceid` int(11) NOT NULL,
            `file_name` varchar(50) NOT NULL,
            `original_file_name` mediumtext NOT NULL,
            `filetype` varchar(25) NOT NULL,
            `datecreated` datetime NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
        // Add expenses main table
        $this->db->query("CREATE TABLE IF NOT EXISTS `tblexpenses` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `category` int(11) NOT NULL,
            `amount` decimal(11,2) NOT NULL,
            `tax` int(11) DEFAULT NULL,
            `reference_no` varchar(100) DEFAULT NULL,
            `note` text,
            `attachment` mediumtext NOT NULL,
            `filetype` varchar(50) DEFAULT NULL,
            `clientid` int(11) NOT NULL,
            `billable` int(11) DEFAULT '0',
            `invoiceid` int(11) DEFAULT NULL,
            `paymentmode` int(11) DEFAULT NULL,
            `date` date NOT NULL,
            `recurring_type` varchar(10) DEFAULT NULL,
            `repeat_every` int(11) DEFAULT NULL,
            `recurring` int(11) NOT NULL DEFAULT '0',
            `custom_recurring` int(11) NOT NULL DEFAULT '0',
            `last_recurring_date` date DEFAULT NULL,
            `create_invoice_billable` tinyint(1) DEFAULT NULL,
            `send_invoice_to_customer` tinyint(1) NOT NULL,
            `recurring_from` int(11) DEFAULT NULL,
            `dateadded` datetime NOT NULL,
            `addedfrom` int(11) NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        // Add expenses categories table
        $this->db->query("CREATE TABLE IF NOT EXISTS `tblexpensescategories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(300) NOT NULL,
            `description` text,
            PRIMARY KEY (`id`)
          ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        // Add lead attachments table
        $this->db->query("CREATE TABLE IF NOT EXISTS `tblleadattachments` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `leadid` int(11) NOT NULL,
            `file_name` mediumtext NOT NULL,
            `filetype` varchar(50) DEFAULT NULL,
            `original_file_name` mediumtext NOT NULL,
            `addedfrom` int(11) NOT NULL,
            `dateadded` datetime NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        // Add goals tracking table
        $this->db->query("CREATE TABLE IF NOT EXISTS `tblgoals` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `subject` varchar(400) NOT NULL,
            `description` text NOT NULL,
            `start_date` date NOT NULL,
            `end_date` date NOT NULL,
            `goal_type` int(11) NOT NULL,
            `contract_type` int(11) NOT NULL DEFAULT '0',
            `achievement` int(11) NOT NULL,
            `addedfrom` int(11) NOT NULL,
            `assigned` int(11) DEFAULT NULL COMMENT 'test',
            `notify_when_fail` tinyint(1) NOT NULL DEFAULT '1',
            `notify_when_achieve` tinyint(1) NOT NULL DEFAULT '1',
            `notified` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
          ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        // Set description for contracts ability to be null
        $this->db->query("ALTER TABLE `tblcontracts` CHANGE `description` `description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL;");

    }
}
