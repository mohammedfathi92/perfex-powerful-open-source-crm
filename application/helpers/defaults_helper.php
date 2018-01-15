<?php

function get_sql_select_client_company()
{
    return 'CASE company WHEN "" THEN (SELECT CONCAT(firstname, " ", lastname) FROM tblcontacts WHERE userid = tblclients.userid and is_primary = 1) ELSE company END as company';
}

function get_sql_calc_task_logged_time($task_id)
{
    /**
    * Do not remove where task_id=
    * Used in tasks detailed_overview to overwrite the taskid
    */
    return "SELECT SUM(CASE
            WHEN end_time is NULL THEN ".time()."-start_time
            ELSE end_time-start_time
            END) as total_logged_time FROM tbltaskstimers WHERE task_id =" . $task_id;
}

function get_sql_select_task_assignees_ids()
{
    return '(SELECT GROUP_CONCAT(staffid SEPARATOR ",") FROM tblstafftaskassignees WHERE taskid=tblstafftasks.id ORDER BY tblstafftaskassignees.staffid)';
}

function get_sql_select_task_asignees_full_names()
{
    return '(SELECT GROUP_CONCAT(CONCAT(firstname, \' \', lastname) SEPARATOR ",") FROM tblstafftaskassignees JOIN tblstaff ON tblstaff.staffid = tblstafftaskassignees.staffid WHERE taskid=tblstafftasks.id ORDER BY tblstafftaskassignees.staffid)';
}

function get_sql_select_task_total_checklist_items(){
    return '(SELECT COUNT(id) FROM tbltaskchecklists WHERE taskid=tblstafftasks.id) as total_checklist_items';
}

function get_sql_select_task_total_finished_checklist_items(){
    return '(SELECT COUNT(id) FROM tbltaskchecklists WHERE taskid=tblstafftasks.id AND finished=1) as total_finished_checklist_items';
}

/**
 * This text is used in WHERE statements for tasks if the staff member don't have permission for tasks VIEW
 * This query will shown only tasks that are created from current user, public tasks or where this user is added is task follower.
 * Other statement will be included the tasks to be visible for this user only if Show All Tasks For Project Members is set to YES
 * @return [type] [description]
 */
function get_tasks_where_string($table = true)
{
    $_tasks_where = '(tblstafftasks.id IN (SELECT taskid FROM tblstafftaskassignees WHERE staffid = ' . get_staff_user_id() . ') OR tblstafftasks.id IN (SELECT taskid FROM tblstafftasksfollowers WHERE staffid = ' . get_staff_user_id() . ') OR (addedfrom=' . get_staff_user_id().' AND is_added_from_contact=0)';
    if (get_option('show_all_tasks_for_project_member') == 1) {
        $_tasks_where .= ' OR (rel_type="project" AND rel_id IN (SELECT project_id FROM tblprojectmembers WHERE staff_id=' . get_staff_user_id() . '))';
    }
    $_tasks_where .= ' OR is_public = 1)';
    if ($table == true) {
        $_tasks_where = 'AND ' . $_tasks_where;
    }

    return $_tasks_where;
}


function default_aside_menu_active()
{
    return '{"aside_menu_active":[{"name":"als_dashboard","url":"\/","permission":"","icon":"fa fa-tachometer","id":"dashboard"},{"name":"als_clients","url":"clients","permission":"customers","icon":"fa fa-user-o","id":"customers"},{"name":"als_sales","url":"#","permission":"","icon":"fa fa-balance-scale","id":"sales","children":[{"name":"proposals","url":"proposals","permission":"proposals","icon":"","id":"child-proposals"},{"name":"estimates","url":"estimates\/list_estimates","permission":"estimates","icon":"","id":"child-estimates"},{"name":"invoices","url":"invoices\/list_invoices","permission":"invoices","icon":"","id":"child-invoices"},{"name":"payments","url":"payments","permission":"payments","icon":"","id":"child-payments"},{"name":"credit_notes","url":"credit_notes","permission":"credit_notes","icon":"","id":"credit_notes"},{"name":"items","url":"invoice_items","permission":"items","icon":"","id":"child-items"}]},{"name":"als_expenses","url":"expenses\/list_expenses","permission":"expenses","icon":"fa fa-file-text-o","id":"expenses"},{"id":"contracts","name":"als_contracts","url":"contracts","permission":"contracts","icon":"fa fa-file"},{"id":"projects","name":"projects","url":"projects","permission":"","icon":"fa fa-bars"},{"name":"als_tasks","url":"tasks\/list_tasks","permission":"","icon":"fa fa-tasks","id":"tasks"},{"name":"support","url":"tickets","permission":"","icon":"fa fa-ticket","id":"tickets"},{"name":"als_leads","url":"leads","permission":"is_staff_member","icon":"fa fa-tty","id":"leads"},{"name":"als_kb","url":"knowledge_base","permission":"knowledge_base","icon":"fa fa-folder-open-o","id":"knowledge-base"},{"name":"als_utilities","url":"#","permission":"","icon":"fa fa-cogs","id":"utilities","children":[{"name":"als_media","url":"utilities\/media","permission":"","icon":"","id":"child-media"},{"name":"bulk_pdf_exporter","url":"utilities\/bulk_pdf_exporter","permission":"bulk_pdf_exporter","icon":"","id":"child-bulk-pdf-exporter"},{"name":"als_calendar_submenu","url":"utilities\/calendar","permission":"","icon":"","id":"child-calendar"},{"name":"als_goals_tracking","url":"goals","permission":"goals","icon":"","id":"child-goals-tracking"},{"name":"als_surveys","url":"surveys","permission":"surveys","icon":"","id":"child-surveys"},{"name":"als_announcements_submenu","url":"announcements","permission":"is_admin","icon":"","id":"child-announcements"},{"name":"utility_backup","url":"utilities\/backup","permission":"is_admin","icon":"","id":"child-database-backup"},{"name":"als_activity_log_submenu","url":"utilities\/activity_log","permission":"is_admin","icon":"","id":"child-activity-log"},{"name":"ticket_pipe_log","url":"utilities\/pipe_log","permission":"is_admin","icon":"","id":"ticket-pipe-log"}]},{"name":"als_reports","url":"#","permission":"reports","icon":"fa fa-area-chart","id":"reports","children":[{"name":"als_reports_sales_submenu","url":"reports\/sales","permission":"","icon":"","id":"child-sales"},{"name":"als_reports_expenses","url":"reports\/expenses","permission":"","icon":"","id":"child-expenses"},{"name":"als_expenses_vs_income","url":"reports\/expenses_vs_income","permission":"","icon":"","id":"child-expenses-vs-income"},{"name":"als_reports_leads_submenu","url":"reports\/leads","permission":"","icon":"","id":"child-leads"},{"name":"timesheets_overview","url":"staff\/timesheets?view=all","permission":"is_admin","icon":"","id":"reports_timesheets_overview"},{"name":"als_kb_articles_submenu","url":"reports\/knowledge_base_articles","permission":"","icon":"","id":"child-kb-articles"}]}]}';
}

function default_setup_menu_active()
{
    return '{"setup_menu_active":[{"name":"als_staff","url":"staff","permission":"staff","icon":"","id":"staff"},{"name":"clients","url":"#","permission":"is_admin","icon":"","id":"customers","children":[{"name":"customer_groups","url":"clients\/groups","permission":"","icon":"","id":"groups"}]},{"name":"support","url":"#","permission":"","icon":"","id":"tickets","children":[{"name":"acs_departments","url":"departments","permission":"is_admin","icon":"","id":"departments"},{"name":"acs_ticket_predefined_replies_submenu","url":"tickets\/predefined_replies","permission":"is_admin","icon":"","id":"predefined-replies"},{"name":"acs_ticket_priority_submenu","url":"tickets\/priorities","permission":"is_admin","icon":"","id":"ticket-priority"},{"name":"acs_ticket_statuses_submenu","url":"tickets\/statuses","permission":"is_admin","icon":"","id":"ticket-statuses"},{"name":"acs_ticket_services_submenu","url":"tickets\/services","permission":"is_admin","icon":"","id":"services"},{"name":"spam_filters","url":"tickets\/spam_filters","permission":"is_admin","icon":"","id":"spam-filters"}]},{"name":"acs_leads","url":"#","permission":"is_admin","icon":"","id":"leads","children":[{"name":"acs_leads_sources_submenu","url":"leads\/sources","permission":"","icon":"","id":"sources"},{"name":"acs_leads_statuses_submenu","url":"leads\/statuses","permission":"","icon":"","id":"statuses"},{"name":"leads_email_integration","url":"leads\/email_integration","permission":"","icon":"","id":"email-integration"},{"name":"web_to_lead","url":"leads\/forms","permission":"is_admin","icon":"","id":"web-to-lead"}]},{"name":"acs_finance","url":"#","permission":"is_admin","icon":"","id":"finance","children":[{"name":"acs_sales_taxes_submenu","url":"taxes","permission":"","icon":"","id":"taxes"},{"name":"acs_sales_currencies_submenu","url":"currencies","permission":"","icon":"","id":"currencies"},{"name":"acs_sales_payment_modes_submenu","url":"paymentmodes","permission":"","icon":"","id":"payment-modes"},{"name":"acs_expense_categories","url":"expenses\/categories","permission":"","icon":"","id":"expenses-categories"}]},{"name":"acs_contracts","url":"#","permission":"is_admin","icon":"","id":"contracts","children":[{"name":"acs_contract_types","url":"contracts\/types","permission":"","icon":"","id":"contract-types"}]},{"name":"acs_email_templates","url":"emails","permission":"email_templates","icon":"","id":"email-templates"},{"name":"asc_custom_fields","url":"custom_fields","permission":"is_admin","icon":"","id":"custom-fields"},{"name":"acs_roles","url":"roles","permission":"roles","icon":"","id":"roles"},{"name":"menu_builder","url":"#","permission":"is_admin","icon":"","id":"menu-builder","children":[{"name":"main_menu","url":"utilities\/main_menu","permission":"is_admin","icon":"","id":"organize-sidebar"},{"name":"setup_menu","url":"utilities\/setup_menu","permission":"is_admin","icon":"","id":"setup-menu"}]},{"name":"theme_style","url":"utilities\/theme_style","permission":"is_admin","icon":"","id":"theme-style"},{"name":"acs_settings","url":"settings","permission":"settings","icon":"","id":"settings"}]}';
}
