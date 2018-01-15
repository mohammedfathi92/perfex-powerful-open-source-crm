<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_106 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        // Version 1.0.6
        if (file_exists(APPPATH . 'views/admin/invoices/invoice_activity_log.php')) {
            @unlink(APPPATH . 'views/admin/invoices/invoice_activity_log.php');
        }

        if (file_exists(APPPATH . 'views/admin/estimates/estimate_activity_log.php')) {
            @unlink(APPPATH . 'views/admin/estimates/estimate_activity_log.php');
        }

        if(!is_dir(BACKUPS_FOLDER)){
            mkdir(BACKUPS_FOLDER);
            fopen(BACKUPS_FOLDER . '.htaccess', 'w');
            $fp = fopen(BACKUPS_FOLDER.'.htaccess','a+');
            if($fp)
            {
                fwrite($fp,'Order Deny,Allow'.PHP_EOL.'Deny from all');
                fclose($fp);
            }
        }

        $this->db->where('name','clients_default_theme');
        $theme = $this->db->get('tbloptions')->row()->value;

        if($theme != 'perfex'){
            copy(APPPATH . 'views/themes/perfex/template_parts/contract_attachments.php',APPPATH . 'views/themes/' . active_clients_theme() . '/template_parts/contract_attachments.php');
        }

        $this->db->query("INSERT INTO `tblpermissions` (`permissionid`, `name`, `shortname`) VALUES (NULL, 'Use Bulk PDF Exporter', 'useBulkPdfExporter');");
        $this->db->query("INSERT INTO `tblemailtemplates` (`type`, `slug`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
        ('tasks', 'task-assigned', 'Task Assigned', 'New Task Assigned to You', 'Hello {staff_firstname} a new task is assigned to you.', 'Company Name', NULL, 0, 0, 0),
        ('tasks', 'task-added-as-follower', 'Added as follower', 'You are added as follower on task', 'Hello {staff_firstname} you are added as follower on task', 'Company Name', NULL, 0, 0, 0),
        ('tasks', 'task-commented', 'Commented on task', 'Someone commented on task', 'New task comment', 'Company Name', NULL, 0, 0, 0),
        ('tasks', 'task-marked-as-finished', 'Task marked as finished', 'Task marked as finished', '{staff_firstname} marked task as finished', 'Company Name', NULL, 0, 0, 0),
        ('tasks', 'task-added-attachment', 'Added attachment', 'New attachment on task', '{staff_firstname} added attachment on task', 'Company Name', NULL, 0, 0, 0),
        ('tasks', 'task-unmarked-as-finished', 'Task unmarked as finished', 'Task unmarked as finished', '{staff_firstname} unmarked task as finished', 'Company Name', NULL, 0, 0, 0);");

        $this->db->query("ALTER TABLE `tblleads` ADD `lost` BOOLEAN NOT NULL DEFAULT FALSE AFTER `date_converted`;");

        $this->db->query("ALTER TABLE `tblleadsstatus` ADD `color` VARCHAR(10) NULL DEFAULT '#28B8DA' AFTER `statusorder`;");
        $this->db->query("ALTER TABLE `tblknowledgebasegroups` ADD `color` VARCHAR(10) NULL DEFAULT '#28B8DA' AFTER `active`;");
        $this->db->query("ALTER TABLE `tblknowledgebase` ADD `article_order` INT NOT NULL DEFAULT '0' AFTER `datecreated`;");
        $this->db->query("ALTER TABLE `tbldepartments` ADD `calendar_id` MEDIUMTEXT NULL AFTER `email`;");

        $this->db->query("ALTER TABLE `tblknowledgebasegroups` ADD `group_order` INT NULL DEFAULT '0' AFTER `color`;");

        // Change the ticket attachment primary key to ID
        $this->db->query("ALTER TABLE `tblticketattachments` CHANGE `attachmentid` `id` INT(11) NOT NULL AUTO_INCREMENT;");
        $this->db->query("ALTER TABLE `tblticketattachments` CHANGE `filename` `file_name` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
        // Add tasks relations
        $this->db->query("ALTER TABLE `tblstafftasks` ADD `rel_id` INT NULL AFTER `finished`, ADD `rel_type` VARCHAR(30) NULL AFTER `rel_id`;");
        $this->db->query("ALTER TABLE `tblstafftasks` ADD `is_public` BOOLEAN NOT NULL DEFAULT FALSE AFTER `rel_type`;");
        $this->db->query("ALTER TABLE `tblcontractattachments` ADD `dateadded` DATETIME NULL AFTER `original_file_name`;");

        $this->db->query("ALTER TABLE `tblcontracts` ADD `not_visible_to_client` BOOLEAN NOT NULL DEFAULT FALSE AFTER `trash`;");
        // Add default invoice due date
        add_option('invoice_due_after', 7);
        add_option('google_api_key', '');
        add_option('google_calendar_main_calendar', '');

        add_option('show_invoices_on_calendar', 1);
        add_option('show_estimates_on_calendar', 1);
        add_option('show_contracts_on_calendar', 1);
        add_option('show_tasks_on_calendar', 1);
        add_option('show_client_reminders_on_calendar', 1);
        add_option('output_client_pdfs_from_admin_area_in_client_language', 1);

        add_option('auto_backup_enabled',0);
        add_option('auto_backup_every',7);
        add_option('last_auto_backup','');
        add_option('default_tax', '');
        add_option('favicon', '');

        // add is importef from email address to leads table
        $this->db->query("ALTER TABLE `tblleads` ADD `is_imported_from_email_integration` BOOLEAN NOT NULL DEFAULT FALSE AFTER `lost`;");
        // add email integration UID
        $this->db->query("ALTER TABLE `tblleads` ADD `email_integration_uid` VARCHAR(30) NULL AFTER `is_imported_from_email_integration`;");
        // Add leads field to junk
        $this->db->query("ALTER TABLE `tblleads` ADD `junk` INT NOT NULL DEFAULT '0' AFTER `lost`;");

        $this->db->query("ALTER TABLE `tblleads` ADD `is_public` BOOLEAN NOT NULL DEFAULT FALSE AFTER `email_integration_uid`;");

        // Add billing and shipping details for customer
        $this->db->query("ALTER TABLE `tblclients` ADD `billing_street` VARCHAR(200) NULL AFTER `leadid`, ADD `billing_city` VARCHAR(100) NULL AFTER `billing_street`, ADD `billing_state` VARCHAR(100) NULL AFTER `billing_city`, ADD `billing_zip` VARCHAR(100) NULL AFTER `billing_state`, ADD `billing_country` INT NULL AFTER `billing_zip`, ADD `shipping_street` VARCHAR(200) NULL AFTER `billing_country`, ADD `shipping_city` VARCHAR(100) NULL AFTER `shipping_street`, ADD `shipping_state` VARCHAR(100) NULL AFTER `shipping_city`, ADD `shipping_zip` VARCHAR(100) NULL AFTER `shipping_state`, ADD `shipping_country` INT NULL AFTER `shipping_zip`;");

        $this->db->query("ALTER TABLE `tblclients` ADD `longitude` VARCHAR(300) NULL AFTER `shipping_country`, ADD `latitude` VARCHAR(300) NULL AFTER `longitude`;");

        // Add billing and shipping details for invoices
        $this->db->query("ALTER TABLE `tblinvoices` ADD `billing_street` VARCHAR(200) NULL AFTER `sale_agent`, ADD `billing_city` VARCHAR(100) NULL AFTER `billing_street`, ADD `billing_state` VARCHAR(100) NULL AFTER `billing_city`, ADD `billing_zip` VARCHAR(100) NULL AFTER `billing_state`, ADD `billing_country` INT NULL AFTER `billing_zip`, ADD `shipping_street` VARCHAR(200) NULL AFTER `billing_country`, ADD `shipping_city` VARCHAR(100) NULL AFTER `shipping_street`, ADD `shipping_state` VARCHAR(100) NULL AFTER `shipping_city`, ADD `shipping_zip` VARCHAR(100) NULL AFTER `shipping_state`, ADD `shipping_country` INT NULL AFTER `shipping_zip`;");

        // Add billing and shipping details for invoices
        $this->db->query("ALTER TABLE `tblestimates` ADD `billing_street` VARCHAR(200) NULL AFTER `sale_agent`, ADD `billing_city` VARCHAR(100) NULL AFTER `billing_street`, ADD `billing_state` VARCHAR(100) NULL AFTER `billing_city`, ADD `billing_zip` VARCHAR(100) NULL AFTER `billing_state`, ADD `billing_country` INT NULL AFTER `billing_zip`, ADD `shipping_street` VARCHAR(200) NULL AFTER `billing_country`, ADD `shipping_city` VARCHAR(100) NULL AFTER `shipping_street`, ADD `shipping_state` VARCHAR(100) NULL AFTER `shipping_city`, ADD `shipping_zip` VARCHAR(100) NULL AFTER `shipping_state`, ADD `shipping_country` INT NULL AFTER `shipping_zip`;");

        $this->db->query("ALTER TABLE `tblestimates` ADD `include_shipping` BOOLEAN NOT NULL AFTER `shipping_country`, ADD `show_shipping_on_estimate` BOOLEAN NOT NULL DEFAULT TRUE AFTER `include_shipping`;");

        $this->db->query("ALTER TABLE `tblinvoices` ADD `include_shipping` BOOLEAN NOT NULL AFTER `shipping_country`, ADD `show_shipping_on_invoice` BOOLEAN NOT NULL DEFAULT TRUE AFTER `include_shipping`;");

        // create the options for leads email integration table
        $this->db->query("CREATE TABLE IF NOT EXISTS `tblleadsemailintegrationemails` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `subject` mediumtext,
                          `body` mediumtext,
                          `dateadded` datetime NOT NULL,
                          `leadid` int(11) NOT NULL,
                          `emailid` int(11) NOT NULL,
                          PRIMARY KEY (`id`)
                        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");


        $this->db->query("CREATE TABLE IF NOT EXISTS `tblleadsemailintegration` (
                  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'the ID always must be 1',
                  `active` int(11) NOT NULL,
                  `email` varchar(100) NOT NULL,
                  `imap_server` varchar(100) NOT NULL,
                  `port` int(11) NOT NULL,
                  `password` mediumtext NOT NULL,
                  `check_every` int(11) NOT NULL DEFAULT '5',
                  `responsible` int(11) NOT NULL,
                  `lead_source` int(11) NOT NULL,
                  `lead_status` int(11) NOT NULL,
                  `encryption` varchar(3) DEFAULT NULL,
                  `folder` varchar(100) NOT NULL,
                  `last_run` varchar(50) DEFAULT NULL,
                  `notify_lead_imported` tinyint(1) NOT NULL DEFAULT '1',
                  `notify_lead_contact_more_times` tinyint(1) NOT NULL DEFAULT '1',
                  `notify_type` varchar(20) DEFAULT NULL,
                  `notify_ids` mediumtext,
                  `only_loop_on_unseen_emails` tinyint(1) NOT NULL DEFAULT '1',
                  PRIMARY KEY (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;");


            $this->db->query("INSERT INTO `tblleadsemailintegration` (`id`, `active`, `email`, `imap_server`, `port`, `password`, `check_every`, `responsible`, `lead_source`, `lead_status`, `encryption`, `folder`, `last_run`, `notify_lead_imported`, `notify_lead_contact_more_times`, `notify_type`, `notify_ids`, `only_loop_on_unseen_emails`) VALUES
            (1, 0, '', '', 0, '', 10, 0, 0, 0, '', 'inbox', '', 1, 1, 'specific_staff', '', 1);");

        $this->db->query("ALTER TABLE `tblleads` CHANGE `lastcontact` `lastcontact` DATETIME NULL DEFAULT NULL;");

        $this->db->query("ALTER TABLE `tblstaff` ADD `default_language` VARCHAR(40) NULL AFTER `active`");
        $this->db->query("ALTER TABLE `tblclients` ADD `default_language` VARCHAR(40) NULL AFTER `latitude`;");
        $this->db->query("ALTER TABLE `tblclients` ADD `default_currency` INT NOT NULL DEFAULT '0' AFTER `default_language`;");

        $this->db->query("ALTER TABLE `tblcontractrenewals` ADD `is_on_old_expiry_notified` INT NULL DEFAULT '0' AFTER `renewed_by`;");
        $this->db->query("ALTER TABLE `tblcontractrenewals` CHANGE `new_end_date` `new_end_date` DATE NULL;");
        $this->db->query("ALTER TABLE `tblcontractrenewals` CHANGE `old_end_date` `old_end_date` DATE NULL;");
        // Okey copy the customer contact details into the invoice
        $invoices = $this->db->get('tblinvoices')->result_array();
        foreach ($invoices as $invoice) {
            $this->db->where('userid', $invoice['clientid']);
            $client = $this->db->get('tblclients')->row();

            $this->db->where('id', $invoice['id']);
            $this->db->update('tblinvoices', array(
                'billing_street' => $client->address,
                'billing_city' => $client->city,
                'billing_state' => $client->state,
                'billing_zip' => $client->zip,
                'billing_country' => $client->country
            ));
        }

        // Okey copy the customer contact details into the estimate
        $invoices = $this->db->get('tblestimates')->result_array();
        foreach ($invoices as $estimate) {
            $this->db->where('userid', $estimate['clientid']);
            $client = $this->db->get('tblclients')->row();

            $this->db->where('id', $estimate['id']);
            $this->db->update('tblestimates', array(
                'billing_street' => $client->address,
                'billing_city' => $client->city,
                'billing_state' => $client->state,
                'billing_zip' => $client->zip,
                'billing_country' => $client->country
            ));
        }

        // Now get all clients and update data for billing
        $clients = $this->db->get('tblclients')->result_array();
        foreach ($clients as $client) {
            $this->db->where('userid', $client['userid']);
            $this->db->update('tblclients', array(
                'billing_street' => $client['address'],
                'billing_city' => $client['city'],
                'billing_state' => $client['state'],
                'billing_zip' => $client['zip'],
                'billing_country' => $client['country']
            ));
        }

    }
}
