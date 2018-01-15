<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_117 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        if(file_exists(FCPATH.'assets/js/sales-reports.js')){
            @unlink(FCPATH.'assets/js/sales-reports.js');
        }
        $this->db->query("ALTER TABLE `tblnotifications` ADD `additional_data` INT NULL AFTER `link`;");
        $this->db->query("ALTER TABLE `tblnotifications` CHANGE `additional_data` `additional_data` VARCHAR(600) NULL DEFAULT NULL;");
        $this->db->query("ALTER TABLE `tblinvoiceactivity` ADD `additional_data` VARCHAR(600) NULL AFTER `description`;");
        $this->db->query("ALTER TABLE `tblestimateactivity` ADD `additional_data` VARCHAR(600) NULL AFTER `description`;");
        $this->db->query("ALTER TABLE `tblleadactivitylog` ADD `additional_data` VARCHAR(600) NULL AFTER `description`;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tblnotes` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `rel_id` int(11) NOT NULL,
          `rel_type` varchar(20) NOT NULL,
          `description` text,
          `addedfrom` int(11) NOT NULL,
          `dateadded` datetime NOT NULL,
          PRIMARY KEY (`id`)
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tblitemsrelated` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `rel_id` int(11) NOT NULL,
          `rel_type` varchar(30) NOT NULL,
          `item_id` int(11) NOT NULL,
          PRIMARY KEY (`id`)
          ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

        $estimate_notes = $this->db->get('tblestimatenotes')->result_array();
        foreach ($estimate_notes as $note) {
            $data                = array();
            $data['rel_type']    = 'estimate';
            $data['rel_id']      = $note['estimate_id'];
            $data['addedfrom']   = $note['staffid'];
            $data['description'] = $note['description'];
            $data['dateadded']   = $note['dateadded'];
            $this->db->insert('tblnotes', $data);
        }

        $this->db->query("DROP TABLE tblestimatenotes");

        $lead_notes = $this->db->get('tblleadnotes')->result_array();
        foreach ($lead_notes as $note) {
            $data                = array();
            $data['rel_type']    = 'lead';
            $data['rel_id']      = $note['leadid'];
            $data['addedfrom']   = $note['staffid'];
            $data['description'] = $note['description'];
            $data['dateadded']   = $note['dateadded'];
            $this->db->insert('tblnotes', $data);
        }

        $this->db->query("DROP TABLE tblleadnotes");

        $customer_staff_notes = $this->db->get('tbluseradminnotes')->result_array();
        foreach ($customer_staff_notes as $note) {
            $data = array();
            if ($note['staff'] == 1) {
                $data['rel_type'] = 'staff';
            } else {
                $data['rel_type'] = 'customer';
            }
            $data['rel_id']      = $note['userid'];
            $data['addedfrom']   = $note['addedfrom'];
            $data['description'] = $note['description'];
            $data['dateadded']   = $note['dateadded'];
            $this->db->insert('tblnotes', $data);
        }

        $this->db->query("DROP TABLE tbluseradminnotes");

        $ticket_notes = $this->db->get('tblticketnotes')->result_array();
        foreach ($ticket_notes as $note) {
            $data                = array();
            $data['rel_type']    = 'ticket';
            $data['rel_id']      = $note['ticketid'];
            $data['addedfrom']   = $note['admin'];
            $data['description'] = $note['note'];
            $data['dateadded']   = $note['date'];
            $this->db->insert('tblnotes', $data);
        }

        $this->db->query("DROP TABLE tblticketnotes");

        add_option('auto_stop_tasks_timers_on_new_timer', 0);
        add_option('notification_when_customer_pay_invoice', 1);
        add_option('theme_style', '[]');

        // Calendar colors
        add_option('calendar_invoice_color', '#FF6F00');
        add_option('calendar_estimate_color', '#FF6F00');
        add_option('calendar_proposal_color', '#84c529');
        add_option('calendar_task_color', '#FC2D42');
        add_option('calendar_reminder_color', '#03A9F4');
        add_option('calendar_contract_color', '#B72974');
        add_option('calendar_project_color', '#B72974');
        // End calendar colors
        add_option('update_info_message', '');
        add_option('show_estimate_reminders_on_calendar', 1);
        add_option('show_invoice_reminders_on_calendar', 1);
        add_option('show_proposal_reminders_on_calendar', 1);
        add_option('proposal_due_after', 7);
        add_option('allow_customer_to_change_ticket_status', 1);
        add_option('lead_lock_after_convert_to_customer', 0);
        add_option('default_proposals_pipeline_sort', 'pipeline_order');
        add_option('defaut_proposals_pipeline_sort_type', 'asc');

        add_option('default_estimates_pipeline_sort', 'pipeline_order');
        add_option('defaut_estimates_pipeline_sort_type', 'asc');

        $this->db->query("ALTER TABLE `tblprojects` ADD `progress` INT NULL DEFAULT '0' AFTER `project_created`, ADD `progress_from_tasks` INT NOT NULL DEFAULT '1' AFTER `progress`;");

        $this->db->where('name', 'show_leads_reminders_on_calendar');
        $this->db->update('tbloptions', array(
            'name' => 'show_lead_reminders_on_calendar'
        ));

        $this->db->query("ALTER TABLE `tblleads` ADD `dateassigned` DATE NULL AFTER `lastcontact`;");

        $this->db->where('name', 'show_client_reminders_on_calendar');
        $this->db->update('tbloptions', array(
            'name' => 'show_customer_reminders_on_calendar'
        ));

        $menu = get_option('setup_menu_active');
        $menu = json_decode($menu);
        if (is_object($menu)) {
            if (count($menu->setup_menu_active) == 0) {
                $order = 1;
            } else {
                $order = count($menu->setup_menu_active);
            }
            add_setup_menu_item(array(
                'name' => 'theme_style',
                'permission' => 'is_admin',
                'url' => 'utilities/theme_style',
                'id' => 'theme-style',
                'order' => $order
            ));
        }

        $this->db->query("ALTER TABLE `tblleadactivitylog` DROP `noteid`;");
        $this->db->query("ALTER TABLE `tblleads` DROP `notes`;");
        $this->db->query("ALTER TABLE `tblcustomfields` ADD `slug` VARCHAR(150) NOT NULL AFTER `name`;");

        $custom_fields = $this->db->get('tblcustomfields')->result_array();
        foreach ($custom_fields as $field) {
            $this->db->where('id', $field['id']);
            $this->db->update('tblcustomfields', array(
                'slug'=>
                slug_it($field['name'], array(
                    'separator' => '_'
                ))
            ));
        }

        $this->db->query("ALTER TABLE `tblevents` ADD `color` VARCHAR(10) NULL AFTER `public`;");
        $events = $this->db->get('tblevents')->result_array();
        foreach ($events as $event) {
            $this->db->where('eventid', $event['eventid']);
            $this->db->update('tblevents', array(
                'color' => '#28B8DA'
            ));
        }
        $this->db->query('UPDATE `tbloptions` SET `value` = replace(value, "watchReports", "reports") WHERE name="aside_menu_active"');
        $this->db->query('UPDATE `tbloptions` SET `value` = replace(value, "watchReports", "reports") WHERE name="aside_menu_inactive"');
        $this->db->query("INSERT INTO `tblemailtemplates` (`type`, `slug`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
            ('invoice', 'invoice-payment-recorded-to-staff', 'Invoice Payment Recorded (Sent to staff)', 'New Invoice Payment', '<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">Customer recorded payment for invoice # {invoice_number}</span></div>\r\n<div>&nbsp;</div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">You can view the invoice on the following link:</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">&nbsp;</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">{invoice_link}</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">&nbsp;</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">Kind regards,</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">&nbsp;</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">{email_signature}</span></div>', '{companyname} | CRM', '', 0, 1, 0);");

        $this->db->query("INSERT INTO `tblemailtemplates` ( `type`, `slug`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
('ticket', 'auto-close-ticket', 'Auto Close Ticket', 'Ticket Auto Closed', '<p><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">Hi&nbsp;{contact_firstname}&nbsp;{contact_lastname}</span></p>\r\n<p><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">Ticket&nbsp;{ticket_subject} has been auto close due to inactivity.</span></p>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">Ticket #: <span style=\"background-color: inherit;\">{ticket_id}</span></span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt; background-color: inherit;\">Department: {ticket_department}</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">Priority: <span style=\"background-color: inherit;\">{ticket_priority}</span></span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">&nbsp;</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt; background-color: inherit;\">Ticket message:</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt; background-color: inherit;\">{ticket_message}</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">&nbsp;</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">Kind regards,</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">{email_signature}</span></div>', '{companyname} | CRM', '', 0, 1, 0);");

    update_option('update_info_message', '<script>window.location.reload();</script>');
    }
}
