<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_197 extends CI_Migration
{
    public function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        $this->db->query("ALTER TABLE `tblstaff` ADD `dashboard_widgets_order` TEXT NULL AFTER `email_signature`;");
        $this->db->query("ALTER TABLE `tblstaff` ADD `dashboard_widgets_visibility` TEXT NULL AFTER `dashboard_widgets_order`;");
        $this->db->query("ALTER TABLE `tblprojectsettings` CHANGE `value` `value` TEXT NULL;");
        $this->db->query("ALTER TABLE `tblreminders` ADD INDEX(`staff`);");
        $this->db->query("ALTER TABLE `tbltaskchecklists` ADD INDEX(`taskid`);");
        $this->db->query("ALTER TABLE `tblstafftaskcomments` ADD INDEX(`taskid`);");
        $this->db->query("ALTER TABLE `tblclients` ADD INDEX(`leadid`);");
        $this->db->query("ALTER TABLE `tblnotes` ADD INDEX(`rel_id`);");
        $this->db->query("ALTER TABLE `tblnotes` ADD INDEX(`rel_type`);");
        $this->db->query("ALTER TABLE `tbldismissedannouncements` ADD INDEX(`staff`);");
        $this->db->query("ALTER TABLE `tbldismissedannouncements` ADD INDEX(`userid`);");
        $this->db->query("ALTER TABLE `tbltags` ADD INDEX(`name`);");
        $this->db->query("ALTER TABLE `tblstaff` ADD INDEX(`firstname`);");
        $this->db->query("ALTER TABLE `tblstaff` ADD INDEX(`lastname`);");
        $this->db->query("ALTER TABLE `tbldepartments` ADD INDEX(`name`);");
        $this->db->query("ALTER TABLE `tblclients` ADD INDEX(`company`);");
        $this->db->query("ALTER TABLE `tblinvoices` ADD INDEX(`total`);");
        $this->db->query("ALTER TABLE `tblitems_in` ADD INDEX(`qty`);");
        $this->db->query("ALTER TABLE `tblitems_in` ADD INDEX(`rate`);");
        $this->db->query("ALTER TABLE `tblleadsstatus` ADD INDEX(`name`);");
        $this->db->query("ALTER TABLE `tblleadssources` ADD INDEX(`name`);");

        add_option('show_project_on_estimate', 1);
        add_option('show_project_on_invoice', 1);
        add_option('show_project_on_credit_note', 1);
        add_option('visible_customer_profile_tabs', 'all', 0);

        add_option('estimates_auto_operations_hour',21);
        add_option('proposals_auto_operations_hour',21);
        add_option('contracts_auto_operations_hour',21);
        add_option('staff_members_create_inline_lead_status',0);
        add_option('staff_members_create_inline_lead_source',0);
        add_option('staff_members_create_inline_customer_groups',0);
        add_option('staff_members_create_inline_ticket_services',0);
        add_option('staff_members_save_tickets_predefined_replies',0);
        add_option('staff_members_create_inline_contract_types',0);
        add_option('staff_members_create_inline_expense_categories',0);

        $this->db->query("INSERT INTO `tblemailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
('staff', 'reminder-email-staff', 'english', 'Staff Reminder Email', 'You Have a New Reminder!', '<p>Hello&nbsp;{staff_firstname}<br /><br /><strong>You have a new reminder&nbsp;linked to&nbsp;{staff_reminder_relation_name}!<br /><br />Reminder description:</strong><br />{staff_reminder_description}<br /><br />Click <a href=\"{staff_reminder_relation_link}\">here</a> to view&nbsp;<a href=\"{staff_reminder_relation_link}\">{staff_reminder_relation_name}</a><br /><br />Best Regards<br /><br /></p>', '{companyname} | CRM', '', 0, 1, 0);");

        $this->db->where('slug', 'task-marked-as-finished');
        $this->db->where('active', 0);
        $this->db->where('language', 'english');
        $task_marked_as_finished_inactive = $this->db->get('tblemailtemplates')->row();

        $this->db->where('slug', 'task-marked-as-finished-to-contacts');
        $this->db->where('active', 0);
        $this->db->where('language', 'english');
        $task_marked_as_finished_inactive_contacts = $this->db->get('tblemailtemplates')->row();

        $this->db->query("DELETE FROM tblemailtemplates WHERE slug IN ('task-marked-as-finished','task-unmarked-as-finished','task-marked-as-finished-to-contacts')");

        $this->db->query("INSERT INTO `tblemailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
('tasks', 'task-status-change-to-contacts', 'english', 'Task Status Changed (Sent to Customer Contacts)', 'Task Status Changed', '<span style=\"font-size: 12pt;\">Hi {contact_firstname} {contact_lastname}</span><br /> <br /><span style=\"font-size: 12pt;\"><strong>{task_user_take_action}</strong> marked task as <strong>{task_status}</strong></span><br /> <br /><span style=\"font-size: 12pt;\"><strong>Name:</strong> {task_name}</span><br /><span style=\"font-size: 12pt;\"><strong>Due date:</strong> {task_duedate}</span><br /> <br /><span style=\"font-size: 12pt;\">You can view the task on the following link: <a href=\"{task_link}\">{task_name}</a></span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('tasks', 'task-status-change-to-staff', 'english', 'Task Status Changed (Sent to Staff)', 'Task Status Changed', '<span style=\"font-size: 12pt;\">Hi {staff_firstname} {staff_lastname}</span><br /> <br /><span style=\"font-size: 12pt;\"><strong>{task_user_take_action}</strong> marked task as <strong>{task_status}</strong></span><br /> <br /><span style=\"font-size: 12pt;\"><strong>Name:</strong> {task_name}</span><br /><span style=\"font-size: 12pt;\"><strong>Due date:</strong> {task_duedate}</span><br /> <br /><span style=\"font-size: 12pt;\">You can view the task on the following link: <a href=\"{task_link}\">{task_name}</a></span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0);");

        if ($task_marked_as_finished_inactive_contacts) {
            $this->db->where('slug', 'task-status-change-to-contacts');
            $this->db->update('tblemailtemplates', array('active'=>0));
        }

        if ($task_marked_as_finished_inactive) {
            $this->db->where('slug', 'task-status-change-to-staff');
            $this->db->update('tblemailtemplates', array('active'=>0));
        }

        $this->db->query("ALTER TABLE `tbldepartments` ADD `email_from_header` BOOLEAN NOT NULL DEFAULT FALSE AFTER `email`;");
        $this->db->query("ALTER TABLE `tblleadsintegration` ADD `create_task_if_customer` INT NOT NULL DEFAULT '0' AFTER `delete_after_import`;");

        $this->db->select('id');
        $this->db->from('tblprojects');

        $projects = $this->db->get()->result_array();

        foreach ($projects as $project) {
            $this->db->insert('tblprojectsettings', array('project_id'=>$project['id'], 'name'=>'available_features','value'=> 'a:14:{s:13:"project_tasks";i:1;s:18:"project_timesheets";i:1;s:18:"project_milestones";i:1;s:13:"project_files";i:1;s:19:"project_discussions";i:1;s:13:"project_gantt";i:1;s:15:"project_tickets";i:1;s:16:"project_invoices";i:1;s:17:"project_estimates";i:1;s:16:"project_expenses";i:1;s:20:"project_credit_notes";i:1;s:13:"project_notes";i:1;s:16:"project_activity";i:1;s:16:"project_overview";i:1;}'));
        }
        if(file_exists(FCPATH.'pipe.php')){
             @chmod(FCPATH.'pipe.php', 0755);
         }
           update_option('update_info_message', '<div class="col-md-12">
            <div class="alert alert-success bold">
                <h4 class="bold">Hi! Thanks for updating Perfex CRM - You are using version 1.9.7</h4>
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
