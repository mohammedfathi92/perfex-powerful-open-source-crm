<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_114 extends CI_Migration
{
  function __construct()
  {
    parent::__construct();
}

public function up()
{
    update_option('setup_menu_inactive','{"setup_menu_inactive":[]}');
    update_option('aside_menu_inactive','{"aside_menu_inactive":[]}');

    update_option('setup_menu_active','{"setup_menu_active":[{"permission":"staff","name":"als_staff","url":"staff","icon":"","id":"staff"},{"permission":"is_admin","name":"clients","url":"#","icon":"","id":"customers","children":[{"permission":"","name":"customer_groups","url":"clients\/groups","icon":"","id":"groups"}]},{"permission":"","name":"support","url":"#","icon":"","id":"tickets","children":[{"permission":"is_admin","name":"acs_departments","url":"departments","icon":"","id":"departments"},{"permission":"is_admin","name":"acs_ticket_predefined_replies_submenu","url":"tickets\/predifined_replies","icon":"","id":"predifined-replies"},{"permission":"is_admin","name":"acs_ticket_priority_submenu","url":"tickets\/priorities","icon":"","id":"ticket-priority"},{"permission":"is_admin","name":"acs_ticket_statuses_submenu","url":"tickets\/statuses","icon":"","id":"ticket-statuses"},{"permission":"is_admin","name":"acs_ticket_services_submenu","url":"tickets\/services","icon":"","id":"services"},{"permission":"is_admin","name":"spam_filters","url":"tickets\/spam_filters","icon":"","id":"spam-filters"}]},{"permission":"is_admin","name":"acs_leads","url":"#","icon":"","id":"leads","children":[{"permission":"","name":"acs_leads_sources_submenu","url":"leads\/sources","icon":"","id":"sources"},{"permission":"","name":"acs_leads_statuses_submenu","url":"leads\/statuses","icon":"","id":"statuses"},{"permission":"","name":"leads_email_integration","url":"leads\/email_integration","icon":"","id":"email-integration"}]},{"permission":"is_admin","name":"acs_finance","url":"#","icon":"","id":"finance","children":[{"permission":"","name":"acs_sales_taxes_submenu","url":"taxes","icon":"","id":"taxes"},{"permission":"","name":"acs_sales_currencies_submenu","url":"currencies","icon":"","id":"currencies"},{"permission":"","name":"acs_sales_payment_modes_submenu","url":"paymentmodes","icon":"","id":"payment-modes"},{"permission":"","name":"acs_expense_categories","url":"expenses\/categories","icon":"","id":"expenses-categories"}]},{"permission":"is_admin","name":"acs_contracts","url":"#","icon":"","id":"contracts","children":[{"permission":"","name":"acs_contract_types","url":"contracts\/types","icon":"","id":"contract-types"}]},{"permission":"email_templates","name":"acs_email_templates","url":"emails","icon":"","id":"email-templates"},{"permission":"is_admin","name":"asc_custom_fields","url":"custom_fields","icon":"","id":"custom-fields"},{"permission":"roles","name":"acs_roles","url":"roles","icon":"","id":"roles"},{"permission":"is_admin","name":"menu_builder","url":"#","icon":"","id":"menu-builder","children":[{"permission":"","name":"main_menu","url":"utilities\/main_menu","icon":"","id":"organize-sidebar"},{"permission":"is_admin","name":"setup_menu","url":"utilities\/setup_menu","icon":"","id":"setup-menu"}]},{"permission":"settings","name":"acs_settings","url":"settings","icon":"","id":"settings"}]}');

    update_option('aside_menu_active','{"aside_menu_active":[{"name":"als_dashboard","url":"\/","permission":"","icon":"fa fa-tachometer","id":"dashboard"},{"name":"als_clients","url":"clients","permission":"customers","icon":"fa fa-users","id":"customers"},{"name":"projects","url":"projects","permission":"","icon":"fa fa-bars","id":"projects"},{"name":"als_sales","url":"#","permission":"","icon":"fa fa-balance-scale","id":"sales","children":[{"name":"proposals","url":"proposals","permission":"proposals","icon":"","id":"child-proposals"},{"name":"estimates","url":"estimates\/list_estimates","permission":"estimates","icon":"","id":"child-estimates"},{"name":"invoices","url":"invoices\/list_invoices","permission":"invoices","icon":"","id":"child-invoices"},{"name":"payments","url":"payments","permission":"payments","icon":"","id":"child-payments"},{"name":"items","url":"invoice_items","permission":"is_admin","icon":"","id":"child-items"}]},{"name":"als_expenses","url":"expenses\/list_expenses","permission":"expenses","icon":"fa fa-heartbeat","id":"expenses"},{"name":"support","url":"#","permission":"","icon":"fa fa-ticket","id":"tickets","children":[{"name":"new_ticket","url":"tickets\/add","permission":"","icon":"","id":"child-new-ticket"},{"name":"Open","url":"tickets\/index\/1","permission":"","icon":"","id":"child-open"},{"name":"In progress","url":"tickets\/index\/2","permission":"","icon":"","id":"child-in-progress"},{"name":"Answered","url":"tickets\/index\/3","permission":"","icon":"","id":"child-answered"},{"name":"On Hold","url":"tickets\/index\/4","permission":"","icon":"","id":"child-on-hold"},{"name":"Closed","url":"tickets\/index\/5","permission":"","icon":"","id":"child-closed"},{"name":"als_all_tickets","url":"tickets","permission":"","icon":"","id":"child-all-tickets"}]},{"name":"als_contracts","url":"contracts","permission":"contracts","icon":"fa fa-file","id":"contracts"},{"name":"als_leads","url":"leads","permission":"is_staff_member","icon":"fa fa-tty","id":"leads"},{"name":"als_tasks","url":"tasks\/list_tasks","permission":"","icon":"fa fa-tasks","id":"tasks"},{"name":"als_kb","url":"#","permission":"knowledge_base","icon":"fa fa-folder-open-o","id":"knowledge-base","children":[{"name":"als_add_article","url":"knowledge_base\/article","permission":"","icon":"","id":"child-add-article"},{"name":"als_all_articles","url":"knowledge_base","permission":"","icon":"","id":"child-all-articles"},{"name":"als_kb_groups","url":"knowledge_base\/manage_groups","permission":"","icon":"","id":"child-groups"}]},{"name":"als_reports","url":"#","permission":"watchReports","icon":"fa fa-area-chart","id":"reports","children":[{"name":"als_reports_sales_submenu","url":"reports\/sales","permission":"","icon":"","id":"child-sales"},{"name":"als_reports_expenses","url":"reports\/expenses","permission":"","icon":"","id":"child-expenses"},{"name":"als_expenses_vs_income","url":"reports\/expenses_vs_income","permission":"","icon":"","id":"child-expenses-vs-income"},{"name":"als_reports_leads_submenu","url":"reports\/leads","permission":"","icon":"","id":"child-leads"},{"name":"als_kb_articles_submenu","url":"reports\/knowledge_base_articles","permission":"","icon":"","id":"child-kb-articles"}]},{"name":"als_utilities","url":"#","permission":"","icon":"fa fa-cogs","id":"utilities","children":[{"name":"als_media","url":"utilities\/media","permission":"","icon":"","id":"child-media"},{"name":"bulk_pdf_exporter","url":"utilities\/bulk_pdf_exporter","permission":"bulk_pdf_exporter","icon":"","id":"child-bulk-pdf-exporter"},{"name":"als_calendar_submenu","url":"utilities\/calendar","permission":"","icon":"","id":"child-calendar"},{"name":"als_goals_tracking","url":"goals","permission":"goals","icon":"","id":"child-goals-tracking"},{"name":"als_surveys","url":"surveys","permission":"surveys","icon":"","id":"child-surveys"},{"name":"als_announcements_submenu","url":"announcements","permission":"is_admin","icon":"","id":"child-announcements"},{"name":"utility_backup","url":"utilities\/backup","permission":"is_admin","icon":"","id":"child-database-backup"},{"name":"als_activity_log_submenu","url":"utilities\/activity_log","permission":"is_admin","icon":"","id":"child-activity-log"},{"name":"ticket_pipe_log","url":"utilities\/pipe_log","permission":"is_admin","icon":"","id":"ticket-pipe-log"}]}]}');

    $this->db->where('name','view_tasks_overview');
    $this->db->update('tblprojectsettings',array('name'=>'view_finance_overview'));

    add_option('show_proposals_on_calendar',1);
    add_option('show_help_on_setup_menu',1);
    add_option('email_protocol','smtp');
    add_option('smtp_encryption','');
    add_option('calendar_first_day',0);
    add_option('access_tickets_to_none_staff_members',1);
    add_option('recaptcha_secret_key','');
    add_option('recaptcha_site_key','');
    add_option('smtp_username','');
    add_option('show_all_tasks_for_project_member',1);

    $this->db->query("ALTER TABLE `tblexpenses` ADD `currency` INT NOT NULL AFTER `category`;");

    $expenses = $this->db->get('tblexpenses')->result_array();
    $this->load->model('currencies_model');
    $currency = $this->currencies_model->get_base_currency();
    foreach($expenses as $expense){
        $this->db->where('id',$expense['id']);
        $this->db->update('tblexpenses',array('currency'=>$currency->id));
    }

    $this->db->query("CREATE TABLE IF NOT EXISTS `tblpinnedprojects` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `project_id` int(11) NOT NULL,
              `staff_id` int(11) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

    $this->db->query("ALTER TABLE `tblexpenses` CHANGE `paymentmode` `paymentmode` VARCHAR(50) NULL DEFAULT NULL;");
    $this->db->query("ALTER TABLE `tblproposals` ADD `pipeline_order` INT NOT NULL AFTER `date_converted`;");
    $this->db->query("ALTER TABLE `tblexpenses` ADD `project_id` INT NOT NULL DEFAULT '0' AFTER `clientid`;");

    $this->db->empty_table('tblpermissions');
    $this->db->query('ALTER TABLE tblpermissions AUTO_INCREMENT = 0');

    $this->db->empty_table('tblrolepermissions');
    $this->db->query('ALTER TABLE tblrolepermissions AUTO_INCREMENT = 0');

    $this->db->empty_table('tblstaffpermissions');
    $this->db->query('ALTER TABLE tblstaffpermissions AUTO_INCREMENT = 0');


    $this->db->query("INSERT INTO `tblpermissions` (`permissionid`, `name`, `shortname`) VALUES
                    (1, 'Contracts', 'contracts'),
                    (2, 'Tasks', 'tasks'),
                    (3, 'Reports', 'reports'),
                    (4, 'Settings', 'settings'),
                    (5, 'Projects', 'projects'),
                    (6, 'Surveys', 'surveys'),
                    (7, 'Staff', 'staff'),
                    (8, 'Customers', 'customers'),
                    (9, 'Email Templates', 'email_templates'),
                    (10, 'Roles', 'roles'),
                    (11, 'Estimates', 'estimates'),
                    (12, 'Knowledge base', 'knowledge_base'),
                    (13, 'Proposals', 'proposals'),
                    (14, 'Goals', 'goals'),
                    (15, 'Expenses', 'expenses'),
                    (16, 'Bulk PDF Exporter', 'bulk_pdf_exporter'),
                    (17, 'Payments', 'payments'),
                    (18, 'Invoices', 'invoices');");


    $this->db->query("ALTER TABLE `tblstaffpermissions` ADD `can_view` BOOLEAN NOT NULL DEFAULT FALSE AFTER `permissionid`, ADD `can_edit` BOOLEAN NOT NULL DEFAULT FALSE AFTER `can_view`, ADD `can_create` BOOLEAN NOT NULL DEFAULT FALSE AFTER `can_edit`, ADD `can_delete` BOOLEAN NOT NULL DEFAULT FALSE AFTER `can_create`;");

    $this->db->query("ALTER TABLE `tblrolepermissions` ADD `can_view` BOOLEAN NOT NULL DEFAULT FALSE AFTER `roleid`, ADD `can_edit` BOOLEAN NULL DEFAULT FALSE AFTER `can_view`, ADD `can_create` BOOLEAN NOT NULL DEFAULT FALSE AFTER `can_edit`, ADD `can_delete` BOOLEAN NOT NULL DEFAULT FALSE AFTER `can_create`;");

    $this->db->query("ALTER TABLE `tblstaff` ADD `is_not_staff` INT NOT NULL DEFAULT '0' AFTER `media_path_slug`;");
}
}
