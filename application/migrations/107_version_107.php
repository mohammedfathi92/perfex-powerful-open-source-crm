<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_107 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {

        if (file_exists(FCPATH . 'media/.htaccess')) {
            @unlink(FCPATH . 'media/.htaccess');
        }

        if (file_exists(FCPATH.'media/')) {
            if (!file_exists(FCPATH.'media/' . 'index.html')) {
                fopen(FCPATH.'media/' . 'index.html', 'w');
            }
        }

        if (file_exists(APPPATH . 'views/themes/' . active_clients_theme() . '/template_parts/contract_attachments.php')) {
            @unlink(APPPATH . 'views/themes/' . active_clients_theme() . '/template_parts/contract_attachments.php');
        }

        if (file_exists(APPPATH . 'views/themes/perfex/template_parts/contract_attachments.php')) {
            @unlink(APPPATH . 'views/themes/perfex/template_parts/contract_attachments.php');
        }

        if (file_exists(APPPATH . 'views/admin/tickets/priorities/priority.php')) {
            @unlink(APPPATH . 'views/admin/tickets/priorities/priority.php');
        }
        if (file_exists(APPPATH . 'views/admin/tickets/tickets_statuses/status.php')) {
            @unlink(APPPATH . 'views/admin/tickets/tickets_statuses/status.php');
        }

        if (file_exists(APPPATH . 'views/admin/tickets/services/service.php')) {
            @unlink(APPPATH . 'views/admin/tickets/services/service.php');
        }

        if (file_exists(APPPATH . 'views/admin/departments/department.php')) {
            @unlink(APPPATH . 'views/admin/departments/department.php');
        }

        if (file_exists(APPPATH . 'views/admin/expenses/category.php')) {
            @unlink(APPPATH . 'views/admin/expenses/category.php');
        }

        if (file_exists(APPPATH . 'views/admin/contracts/type.php')) {
            @unlink(APPPATH . 'views/admin/contracts/type.php');
        }

        if (file_exists(APPPATH . 'views/admin/includes/modals/payment_modes_modal.php')) {
            @unlink(APPPATH . 'views/admin/includes/modals/payment_modes_modal.php');
        }

        if (file_exists(APPPATH . 'views/admin/includes/modals/currency_modal.php')) {
            @unlink(APPPATH . 'views/admin/includes/modals/currency_modal.php');
        }

        if (file_exists(APPPATH . 'views/admin/includes/modals/tax_modal.php')) {
            @unlink(APPPATH . 'views/admin/includes/modals/tax_modal.php');
        }

        if (file_exists(APPPATH . 'views/admin/includes/modals/sales_item_modal.php')) {
            @unlink(APPPATH . 'views/admin/includes/modals/sales_item_modal.php');
        }

        if (file_exists(APPPATH . 'views/admin/leads/source.php')) {
            @unlink(APPPATH . 'views/admin/leads/source.php');
        }

        if (!is_dir(CLIENT_ATTACHMENTS_FOLDER)) {
            mkdir(CLIENT_ATTACHMENTS_FOLDER);
            fopen(CLIENT_ATTACHMENTS_FOLDER . '.htaccess', 'w');
            $fp = fopen(CLIENT_ATTACHMENTS_FOLDER . '.htaccess', 'a+');
            if ($fp) {
                fwrite($fp, 'Order Deny,Allow' . PHP_EOL . 'Deny from all');
                fclose($fp);
            }
        }

        $this->db->query("ALTER TABLE `tblinvoiceitems` CHANGE `qty` `qty` DECIMAL(11,2) NOT NULL;");
        $this->db->query("ALTER TABLE `tblestimateitems` CHANGE `qty` `qty` DECIMAL(11,2) NOT NULL;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tblcustomerpermissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        $customers   = $this->db->get('tblclients')->result_array();
        $permissions = $this->perfex_base->get_contact_permissions();

        foreach ($customers as $customer) {
            foreach ($permissions as $permission) {
                $this->db->insert('tblcustomerpermissions', array(
                    'permission_id' => $permission['id'],
                    'userid' => $customer['userid']
                ));
            }
        }

        $this->db->where('type', 'editor');
        $editor_fields = $this->db->get('tblcustomfields')->result_array();

        foreach ($editor_fields as $field) {
            $this->db->where('id', $field['id']);
            $this->db->update('tblcustomfields', array(
                'type' => 'textarea'
            ));
        }

        $this->db->query("INSERT INTO `tblemailtemplates` (`type`, `slug`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
  ('estimate', 'estimate-declined-to-staff', 'Estimate Declined (Sent to Staff)', 'Customer Declined Estimate', '<div>Hello.</div><div><br></div><div>Customer (<span style=\"font-family: Helvetica, Arial, sans-serif; background-color: inherit;\">{client_firstname} {client_lastname}</span>) declined estimate with number <span style=\"font-family: Helvetica, Arial, sans-serif; background-color: inherit;\">{estimate_number}</span></div><div><br></div><div>You can view the estimate on the following link <span style=\"font-family: Helvetica, Arial, sans-serif; background-color: inherit;\">{estimate_link}</span></div><div><br></div><div>{email_signature}</div><div><br></div>', '{client_company}', '', 0, 1, 0),
  ('estimate', 'estimate-accepted-to-staff', 'Estimate Accepted (Sent to Staff)', 'Customer Accepted Estimate', '<div>Hello.</div><div><br></div><div>Customer (<span style=\"font-family: Helvetica, Arial, sans-serif; background-color: inherit;\">{client_firstname} {client_lastname}</span>) accepted estimate with number <span style=\"font-family: Helvetica, Arial, sans-serif; background-color: inherit;\">{estimate_number}</span></div><div><br></div><div>You can view the estimate on the following link <span style=\"font-family: Helvetica, Arial, sans-serif; background-color: inherit;\">{estimate_link}</span></div><div><br></div><div>{email_signature}</div>', '{companyname}', '', 0, 1, 0),
  ('proposals', 'proposal-client-accepted', 'Customer Action - Accepted (Sent to Staff)', 'Customer Accepted Proposal', '<div>Customer <span style=\"font-family: Helvetica, Arial, sans-serif;\">{proposal_proposal_to}</span> Accepted Proposal</div><div><br></div><div>View the proposal on the following link: {proposal_link}</div><div><br></div><div>{email_signature}</div><div><br></div>', '{companyname}', '', 0, 1, 0),
  ('proposals', 'proposal-send-to-customer', 'Proposal Send to Customer', 'Proposal', '', '{companyname}', '', 0, 1, 0),
  ('proposals', 'proposal-client-declined', 'Customer Action - Declined (Sent to Staff)', 'Client Declined Proposal', '<div>Client Declined proposal</div>', '{companyname}', '', 0, 1, 0),
  ('proposals', 'proposal-client-thank-you', 'Customer Thank You Email (After Accept)', 'Thank for you accepting proposal', '<div>Hello <span style=\"font-family: Helvetica, Arial, sans-serif;\">{proposal_proposal_to}</span></div><div><br></div><div>Thank for for accepting the proposal.</div><div><br></div><div>We look forward doing business with you.</div><div><br></div><div>Our staff will call you asap.</div><div><br></div><div>{email_signature}</div>', '{companyname}', '', 0, 1, 0),
  ('proposals', 'proposal-comment-to-client', 'New Comment (Customer)', 'New Proposal Comment', '<div>New Proposal Comment</div>', '{companyname}', '', 0, 1, 0),
  ('proposals', 'proposal-comment-to-admin', 'New Comment (Sent to Staff) ', 'New Proposal Comment', '<div>New Proposal Comment</div>', '{companyname}', '', 0, 1, 0),
  ('estimate', 'estimate-thank-you-to-customer', 'Customer Thank You Email (After Accept)', 'Thank for you accepting estimate', '<div>Hello <span style=\"font-family: Helvetica, Arial, sans-serif;\">{client_firstname} {client_lastname}</span></div><div><br></div><div>Thank for for accepting the estimate.</div><div><br></div><div>We look forward doing business with you.</div><div><br></div><div>Our staff will call you asap.</div><div><br></div><div>{email_signature}</div><div><br></div><div><br></div><div><br></div>', '{companyname}', '', 0, 1, 0);
  ");

        $this->db->query("ALTER TABLE `tbloptions` CHANGE `value` `value` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tblproposals` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `subject` varchar(500) DEFAULT NULL,
    `content` longtext,
    `addedfrom` int(11) NOT NULL,
    `datecreated` datetime NOT NULL,
    `total` decimal(11,2) DEFAULT NULL,
    `currency` int(11) NOT NULL,
    `open_till` date DEFAULT NULL,
    `date` date NOT NULL,
    `rel_id` int(11) DEFAULT NULL,
    `rel_type` varchar(40) DEFAULT NULL,
    `assigned` int(11) DEFAULT NULL,
    `hash` varchar(32) NOT NULL,
    `proposal_to` varchar(600) DEFAULT NULL,
    `address` varchar(200) DEFAULT NULL,
    `email` varchar(150) DEFAULT NULL,
    `phone` varchar(50) DEFAULT NULL,
    `allow_comments` tinyint(1) NOT NULL DEFAULT '1',
    `status` int(11) NOT NULL,
    `estimate_id` int(11) DEFAULT NULL,
    `invoice_id` int(11) DEFAULT NULL,
    `date_converted` datetime DEFAULT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tblproposalcomments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `content` mediumtext,
    `proposalid` int(11) NOT NULL,
    `staffid` int(11) NOT NULL,
    `dateadded` datetime NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tblticketsspamcontrol` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `type` varchar(40) NOT NULL,
    `value` text NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tblticketpipelog` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `date` datetime NOT NULL,
    `email_to` varchar(500) NOT NULL,
    `name` varchar(200) NOT NULL,
    `subject` varchar(500) NOT NULL,
    `message` mediumtext NOT NULL,
    `email` varchar(300) NOT NULL,
    `status` varchar(100) NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        add_option('show_leads_reminders_on_calendar', 1);
        add_option('customer_default_country', '');
        add_option('view_estimate_only_logged_in', 0);
        add_option('show_status_on_pdf_ei', 1);
        add_option('email_piping_only_replies', 1);
        add_option('email_piping_only_registered', 0);
        add_option('email_piping_enabled', 0);
        add_option('email_piping_default_priority', 2);

        // Setup Menu
        add_option('setup_menu_inactive', '{"setup_menu_inactive":[]}');
        add_option('aside_menu_inactive', '{"aside_menu_inactive":[]}');

        add_option('aside_menu_active', '{"aside_menu_active":[{"id":"dashboard","name":"als_dashboard","url":"\/","permission":"","icon":"fa fa-tachometer"},{"id":"customers","name":"als_clients","url":"clients","permission":"manageClients","icon":"fa fa-users"},{"id":"sales","name":"als_sales","url":"#","permission":"manageSales","icon":"fa fa-balance-scale","children":[{"id":"child-proposals","name":"proposals","url":"proposals","permission":"","icon":""},{"name":"estimates","url":"estimates\/list_estimates","permission":"","icon":"","id":"child-estimates"},{"name":"invoices","url":"invoices\/list_invoices","permission":"","icon":"","id":"child-invoices"},{"name":"payments","url":"payments","permission":"","icon":"","id":"child-payments"},{"name":"items","url":"invoice_items","permission":"","icon":"","id":"child-items"}]},{"id":"expenses","name":"als_expenses","url":"expenses\/list_expenses","permission":"manageExpenses","icon":"fa fa-heartbeat"},{"id":"leads","name":"als_leads","url":"leads","permission":"","icon":"fa fa-tty"},{"id":"tickets","name":"support","url":"#","permission":"","icon":"fa fa-ticket","children":[{"id":"child-new-ticket","name":"new_ticket","url":"tickets\/add","permission":"","icon":""},{"id":"child-open","name":"Open","url":"tickets\/index\/1","permission":"","icon":""},{"id":"child-in-progress","name":"In progress","url":"tickets\/index\/2","permission":"","icon":""},{"id":"child-answered","name":"Answered","url":"tickets\/index\/3","permission":"","icon":""},{"name":"On Hold","url":"tickets\/index\/4","permission":"","icon":"","id":"child-on-hold"},{"name":"Closed","url":"tickets\/index\/5","permission":"","icon":"","id":"child-closed"},{"id":"child-all-tickets","name":"als_all_tickets","url":"tickets","permission":"","icon":""}]},{"id":"contracts","name":"als_contracts","url":"contracts","permission":"manageContracts","icon":"fa fa-file"},{"id":"tasks","name":"als_tasks","url":"tasks\/list_tasks","permission":"","icon":"fa fa-tasks"},{"id":"knowledge-base","name":"als_kb","url":"#","permission":"manageKnowledgeBase","icon":"fa fa-folder-open-o","children":[{"id":"child-add-article","name":"als_add_article","url":"knowledge_base\/article","permission":"","icon":""},{"name":"als_all_articles","url":"knowledge_base","permission":"","icon":"","id":"child-all-articles"},{"name":"als_kb_groups","url":"knowledge_base\/manage_groups","permission":"","icon":"","id":"child-groups"}]},{"id":"reports","name":"als_reports","url":"#","permission":"reports","icon":"fa fa-area-chart","children":[{"id":"child-sales","name":"als_reports_sales_submenu","url":"reports\/sales","permission":"","icon":""},{"name":"als_reports_expenses","url":"reports\/expenses","permission":"","icon":"","id":"child-expenses"},{"name":"als_expenses_vs_income","url":"reports\/expenses_vs_income","permission":"","icon":"","id":"child-expenses-vs-income"},{"name":"als_reports_leads_submenu","url":"reports\/leads","permission":"","icon":"","id":"child-leads"},{"name":"als_kb_articles_submenu","url":"reports\/knowledge_base_articles","permission":"","icon":"","id":"child-kb-articles"}]},{"id":"utilities","name":"als_utilities","url":"#","permission":"","icon":"fa fa-cogs","children":[{"name":"als_media","url":"utilities\/media","permission":"","icon":"","id":"child-media"},{"id":"child-bulk-pdf-exporter","name":"bulk_pdf_exporter","url":"utilities\/bulk_pdf_exporter","permission":"useBulkPdfExporter","icon":""},{"id":"child-calendar","name":"als_calendar_submenu","url":"utilities\/calendar","permission":"","icon":""},{"id":"child-goals-tracking","name":"als_goals_tracking","url":"goals","permission":"manageGoals","icon":""},{"name":"als_surveys","url":"surveys","permission":"manageSurveys","icon":"","id":"child-surveys"},{"id":"child-announcements","name":"als_announcements_submenu","url":"announcements","permission":"is_admin","icon":""},{"name":"als_mail_lists_submenu","url":"mail_lists","permission":"manageMailLists","icon":"","id":"child-mail-lists"},{"id":"child-database-backup","name":"utility_backup","url":"utilities\/backup","permission":"is_admin","icon":""},{"id":"child-activity-log","name":"als_activity_log_submenu","url":"utilities\/activity_log","permission":"is_admin","icon":""},{"id":"ticket-pipe-log","name":"Ticket Pipe Log","url":"utilities\/pipe_log","permission":"is_admin","icon":""}]}]}');

        add_option('setup_menu_active', '{"setup_menu_active":[{"id":"staff","name":"als_staff","url":"staff","permission":"manageStaff","icon":""},{"id":"customers","name":"clients","url":"#","permission":"manageClients","icon":"","children":[{"id":"groups","name":"customer_groups","url":"clients\/groups","permission":"","icon":""}]},{"id":"tickets","name":"support","url":"#","permission":"manageDepartments","icon":"","children":[{"id":"departments","name":"acs_departments","url":"departments","permission":"manageDepartments","icon":""},{"id":"predifined-replies","name":"acs_ticket_predefined_replies_submenu","url":"tickets\/predifined_replies","permission":"is_admin","icon":""},{"id":"ticket-priority","name":"acs_ticket_priority_submenu","url":"tickets\/priorities","permission":"is_admin","icon":""},{"id":"ticket-statuses","name":"acs_ticket_statuses_submenu","url":"tickets\/statuses","permission":"is_admin","icon":""},{"id":"services","name":"acs_ticket_services_submenu","url":"tickets\/services","permission":"is_admin","icon":""},{"id":"spam-filters","name":"spam_filters","url":"tickets\/spam_filters","permission":"is_admin","icon":""}]},{"id":"leads","name":"acs_leads","url":"#","permission":"is_admin","icon":"","children":[{"id":"sources","name":"acs_leads_sources_submenu","url":"leads\/sources","permission":"","icon":""},{"id":"statuses","name":"acs_leads_statuses_submenu","url":"leads\/statuses","permission":"","icon":""},{"id":"email-integration","name":"leads_email_integration","url":"leads\/email_integration","permission":"","icon":""}]},{"id":"finance","name":"acs_finance","url":"#","permission":"manageSales","icon":"","children":[{"id":"taxes","name":"acs_sales_taxes_submenu","url":"taxes","permission":"","icon":""},{"id":"currencies","name":"acs_sales_currencies_submenu","url":"currencies","permission":"","icon":""},{"id":"payment-modes","name":"acs_sales_payment_modes_submenu","url":"paymentmodes","permission":"","icon":""},{"id":"expenses-categories","name":"acs_expense_categories","url":"expenses\/categories","permission":"","icon":""}]},{"id":"contracts","name":"acs_contracts","url":"#","permission":"manageContracts","icon":"","children":[{"id":"contract-types","name":"acs_contract_types","url":"contracts\/types","permission":"","icon":""}]},{"id":"email-templates","name":"acs_email_templates","url":"emails","permission":"editEmailTemplates","icon":""},{"id":"custom-fields","name":"asc_custom_fields","url":"custom_fields","permission":"is_admin","icon":""},{"name":"acs_roles","url":"roles","permission":"manageRoles","icon":"","id":"roles"},{"id":"menu-builder","name":"menu_builder","url":"#","permission":"is_admin","icon":"","children":[{"name":"main_menu","url":"utilities\/main_menu","permission":"","icon":"","id":"organize-sidebar"},{"name":"setup_menu","url":"utilities\/setup_menu","permission":"is_admin","icon":"","id":"setup-menu"}]},{"id":"settings","name":"acs_settings","url":"settings","permission":"editSettings","icon":""}]}');

        // FILES
        $this->db->query("ALTER TABLE `tblinvoiceattachments` DROP `original_file_name`;");
        $this->db->query("ALTER TABLE `tblcontractattachments` DROP `original_file_name`;");
        $this->db->query("ALTER TABLE `tblstafftasksattachments` DROP `original_file_name`;");
        $this->db->query("ALTER TABLE `tblleadattachments` DROP `original_file_name`;");
        $this->db->query("ALTER TABLE `tblpostattachments` DROP `original_name`;");
        $this->db->query("ALTER TABLE `tblticketattachments` DROP `original_file_name`;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tbltaskchecklists` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `taskid` int(11) NOT NULL,
    `description` varchar(500) NOT NULL,
    `finished` int(11) NOT NULL DEFAULT '0',
    `dateadded` datetime NOT NULL,
    `addedfrom` int(11) NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        $this->db->query("ALTER TABLE `tbltickets` ADD `email` TEXT NULL AFTER `userid`, ADD `name` TEXT NULL AFTER `email`;");
        $this->db->query("ALTER TABLE `tblticketreplies` ADD `name` TEXT NULL AFTER `userid`, ADD `email` TEXT NULL AFTER `name`;");

        $this->db->query("ALTER TABLE `tbltaskchecklists` ADD `list_order` INT NOT NULL DEFAULT '0' AFTER `addedfrom`;");

        $this->db->query("ALTER TABLE `tblcustomfields` ADD `show_on_table` BOOLEAN NOT NULL DEFAULT FALSE AFTER `show_on_pdf`;");
        $this->db->query("ALTER TABLE `tblcustomfields` ADD `show_on_client_portal` INT NOT NULL DEFAULT '0' AFTER `show_on_table`;");

        $this->db->query("RENAME TABLE `tbladminclientreminders` TO `tblreminders`;");
        $this->db->query("ALTER TABLE `tblreminders` CHANGE `clientid` `rel_id` INT(11) NOT NULL;");
        $this->db->query("ALTER TABLE `tblreminders` ADD `rel_type` VARCHAR(40) NOT NULL AFTER `rel_id`;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tblcustomersgroups` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(600) NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tblcustomergroups_in` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `groupid` int(11) NOT NULL,
    `customer_id` int(11) NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");


        $this->db->query("ALTER TABLE `tblestimates` ADD `hash` VARCHAR(32) NULL AFTER `year`;");

        // get all estimates and insert hash
        $estimates = $this->db->get('tblestimates')->result_array();
        foreach ($estimates as $estimate) {
            $hash = md5(rand() . microtime());
            // Check if the key exists
            $this->db->where('hash', $hash);
            $exists = $this->db->get('tblinvoices')->row();
            if ($exists) {
                $hash = md5(rand() . microtime());
            }

            $this->db->where('id', $estimate['id']);
            $this->db->update('tblestimates', array(
                'hash' => $hash
            ));
        }

        $this->db->query("CREATE TABLE IF NOT EXISTS `tblclientattachments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `clientid` int(11) NOT NULL,
    `file_name` varchar(50) NOT NULL,
    `filetype` varchar(25) NOT NULL,
    `datecreated` datetime NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
        // Get all reminders and set rel_type to client
        $reminders = $this->db->get('tblreminders')->result_array();
        foreach ($reminders as $reminder) {
            $this->db->where('id', $reminder['id']);
            $this->db->update('tblreminders', array(
                'rel_type' => 'customer'
            ));
        }
    }
}
