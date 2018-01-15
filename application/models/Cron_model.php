<?php
defined('BASEPATH') or exit('No direct script access allowed');

define('CRON', true);

class Cron_model extends CRM_Model
{
    private $manually = false;
    private $lock_handle;

    public function __construct()
    {
        if (!defined('APP_DISABLE_CRON_LOCK') || defined('APP_DISABLE_CRON_LOCK') && !APP_DISABLE_CRON_LOCK) {
            register_shutdown_function(array($this, '__destruct'));
            $f = fopen(get_temp_dir() . 'pcrm-cron-lock', 'c');
            if (!$f) {
                $this->lock_handle = fopen(TEMP_FOLDER . 'pcrm-cron-lock', 'c');
                // Again? Disable the lock
                if (!$this->lock_handle && !defined('APP_DISABLE_CRON_LOCK')) {
                    // Defined this constant manually here so the cron is able to run
                    // Used in method can_cron_run
                    define('APP_DISABLE_CRON_LOCK', true);
                }
            } else {
                $this->lock_handle = $f;
            }
        }

        parent::__construct();
        $this->load->model('emails_model');
        $this->load->model('staff_model');
    }

    public function run($manually = false)
    {
        update_option('last_cron_run', time());

        if ($this->can_cron_run()) {
            if ($manually == true) {
                $this->manually = true;

                if (!extension_loaded('suhosin')) {
                    @ini_set('memory_limit', '-1');
                }

                logActivity('Cron Invoked Manually');
            }

            $this->make_backup_db();
            $this->staff_reminders();
            $this->events();
            $this->tasks_reminders();
            $this->recurring_tasks();
            $this->proposals();
            $this->invoice_overdue();
            $this->estimate_expiration();
            $this->contracts_expiration_check();
            $this->autoclose_tickets();
            $this->recurring_invoices();
            $this->recurring_expenses();
            $this->goals_notification();
            $this->surveys();

            $this->auto_import_imap_tickets();
            $this->check_leads_email_integration();
            $this->delete_activity_log();

            /**
             * Finally send any emails in the email queue - if enabled and any
             */
            $this->email->send_queue();

            $last_email_queue_retry = get_option('last_email_queue_retry');

            // Retry queue failed emails every 10 minutes
            if ($last_email_queue_retry == '' || (time() > ($last_email_queue_retry + do_action('cron_retry_email_queue_seconds',600)))) {
                $this->email->retry_queue();
                update_option('last_email_queue_retry',time());
            }
        }
    }

    private function events()
    {
        // User events
        $this->db->where('isstartnotified', 0);
        $events         = $this->db->get('tblevents')->result_array();

        $notified_users = array();
        $notificationNotifiedUsers = array();
        $all_notified_events = array();

        foreach ($events as $event) {
            $date_compare = date('Y-m-d H:i:s', strtotime('+' . $event['reminder_before'] . ' ' . strtoupper($event['reminder_before_type'])));

            if ($event['start'] <= $date_compare) {
                array_push($all_notified_events, $event['eventid']);
                array_push($notified_users, $event['userid']);

                $eventNotifications = do_action('event_notifications', true);

                if ($eventNotifications) {
                    $notified = add_notification(array(
                        'description' => 'not_event',
                        'touserid' => $event['userid'],
                        'fromcompany' => true,
                        'link' => '#eventid=' . $event['eventid'],
                        'additional_data' => serialize(array(
                            $event['title'],
                        )),
                    ));
                    $this->db->select('email');
                    $this->db->where('staffid', $event['userid']);
                    $email = $this->db->get('tblstaff')->row()->email;
                    load_admin_language($event['userid']);
                    $this->emails_model->send_simple_email($email, _l('not_event', $event['title'] . ' - ' . _d($event['start'])), $event['description'] . '<br /><br />' . get_option('email_signature'));

                    if ($notified) {
                        array_push($notificationNotifiedUsers, $event['userid']);
                    }
                }
            }
        }
        load_admin_language();
        // Show public events

        $this->db->where('public', 1);
        if (count($notified_users) > 0) {
            $this->db->where('userid NOT IN (' . implode(',', $notified_users) . ')');
        }
        $this->db->where('isstartnotified', 0);
        $events = $this->db->get('tblevents')->result_array();

        $staff = $this->staff_model->get('', 1);

        foreach ($staff as $member) {
            if (is_staff_member($member['staffid'])) {
                foreach ($events as $event) {
                    $date_compare = date('Y-m-d H:i:s', strtotime('+' . $event['reminder_before'] . ' ' . strtoupper($event['reminder_before_type'])));
                    if ($event['start'] <= $date_compare) {
                        array_push($all_notified_events, $event['eventid']);

                        $eventNotifications = do_action('event_notifications', true);

                        if ($eventNotifications) {
                            $notified = add_notification(array(
                                'description' => 'not_event_public',
                                'touserid' => $member['staffid'],
                                'fromcompany' => true,
                                'link' => '#eventid=' . $event['eventid'],
                                'additional_data' => serialize(array(
                                    $event['title'],
                                )),
                            ));

                            load_admin_language($member['staffid']);

                            $this->emails_model->send_simple_email($member['email'], _l('not_event', $event['title'] . ' - ' . _d($event['start'])), $event['description'] . '<br /><br />' . get_option('email_signature'));

                            if ($notified) {
                                array_push($notificationNotifiedUsers, $member['staffid']);
                            }
                        }
                    }
                }
            }
        }
        load_admin_language();
        foreach ($all_notified_events as $id) {
            $this->db->where('eventid', $id);
            $this->db->update('tblevents', array(
                'isstartnotified' => 1,
            ));
        }

        pusher_trigger_notification($notificationNotifiedUsers);
    }

    private function autoclose_tickets()
    {
        $auto_close_after = get_option('autoclose_tickets_after');

        if ($auto_close_after == 0) {
            return;
        }

        $this->db->select('ticketid,lastreply,date,userid,contactid,email');
        $this->db->where('status !=', 5);
        $this->db->where('status !=', 4);
        $this->db->where('status !=', 2);
        $tickets = $this->db->get('tbltickets')->result_array();
        foreach ($tickets as $ticket) {
            $close_ticket = false;
            if (!is_null($ticket['lastreply'])) {
                $last_reply = strtotime($ticket['lastreply']);
                if ($last_reply <= strtotime('-' . $auto_close_after . ' hours')) {
                    $close_ticket = true;
                }
            } else {
                $created = strtotime($ticket['date']);
                if ($created <= strtotime('-' . $auto_close_after . ' hours')) {
                    $close_ticket = true;
                }
            }
            if ($close_ticket == true) {
                $this->db->where('ticketid', $ticket['ticketid']);
                $this->db->update('tbltickets', array(
                    'status' => 5,
                ));
                if ($this->db->affected_rows() > 0) {
                    if ($ticket['userid'] != 0 && $ticket['contactid'] != 0) {
                        $email = $this->clients_model->get_contact($ticket['contactid'])->email;
                    } else {
                        $email = $ticket['email'];
                    }
                    $merge_fields = array();
                    $merge_fields = array_merge($merge_fields, get_ticket_merge_fields('auto-close-ticket', $ticket['ticketid']));
                    $merge_fields = array_merge($merge_fields, get_client_contact_merge_fields($ticket['userid'], $ticket['contactid']));
                    $this->emails_model->send_email_template('auto-close-ticket', $email, $merge_fields, $ticket['ticketid']);
                }
            }
        }
    }

    public function contracts_expiration_check()
    {
        $contracts_auto_operations_hour = get_option('contracts_auto_operations_hour');

        if ($contracts_auto_operations_hour == '') {
            $contracts_auto_operations_hour = 9;
        }

        $hour_now = date('G');
        if ($hour_now < $contracts_auto_operations_hour && $this->manually == false) {
            return;
        }

        $this->db->select('id,client,dateend,subject,addedfrom,not_visible_to_client');
        $this->db->where('isexpirynotified', 0);
        $this->db->where('dateend is NOT NULL');
        $this->db->where('trash', 0);
        $contracts = $this->db->get('tblcontracts')->result_array();
        $now       = new DateTime(date('Y-m-d'));

        $notifiedUsers = array();
        if (count($contracts) > 0) {
            $staff = $this->staff_model->get('', 1);
        }
        foreach ($contracts as $contract) {
            if ($contract['dateend'] > date('Y-m-d')) {
                $dateend = new DateTime($contract['dateend']);
                $diff    = $dateend->diff($now)->format("%a");
                if ($diff <= get_option('contract_expiration_before') && get_option('contract_expiry_reminder_enabled') == 1) {
                    $merge_fields = array();
                    $merge_fields = array_merge($merge_fields, get_client_contact_merge_fields($contract['client']));
                    $merge_fields = array_merge($merge_fields, get_contract_merge_fields($contract['id']));

                    foreach ($staff as $member) {
                        if ($member['staffid'] == $contract['addedfrom'] || is_admin($member['staffid'])) {
                            $notified = add_notification(array(
                                'description' => 'not_contract_expiry_reminder',
                                'touserid' => $member['staffid'],
                                'fromcompany' => 1,
                                'fromuserid' => null,
                                'link' => 'contracts/contract/' . $contract['id'],
                                'additional_data' => serialize(array(
                                    $contract['subject'],
                                )),
                            ));
                            if ($notified) {
                                array_push($notifiedUsers, $member['staffid']);
                            }
                            $this->emails_model->send_email_template('contract-expiration', $member['email'], $merge_fields);
                        }
                    }

                    if ($contract['not_visible_to_client'] == 0) {
                        $contacts = $this->clients_model->get_contacts($contract['client'], array('active'=>1, 'contract_emails'=>1));
                        foreach ($contacts as $contact) {
                            $merge_fields = array();
                            $merge_fields = array_merge($merge_fields, get_client_contact_merge_fields($contract['client'], $contact['id']));
                            $merge_fields = array_merge($merge_fields, get_contract_merge_fields($contract['id']));
                            $this->emails_model->send_email_template('contract-expiration', $contact['email'], $merge_fields);
                        }
                    }
                    $this->db->where('id', $contract['id']);
                    $this->db->update('tblcontracts', array(
                        'isexpirynotified' => 1,
                    ));
                }
            }
        }

        pusher_trigger_notification($notifiedUsers);
    }

    public function recurring_tasks()
    {
        $this->db->select('id,addedfrom,recurring_ends_on,recurring_type,repeat_every,last_recurring_date,startdate');
        $this->db->where('recurring', 1);
        $recurring_tasks = $this->db->get('tblstafftasks')->result_array();


        foreach ($recurring_tasks as $task) {
            if (!is_null($task['recurring_ends_on'])) {
                if (date('Y-m-d') > date('Y-m-d', strtotime($task['recurring_ends_on']))) {
                    continue;
                }
            }

            $type                = $task['recurring_type'];
            $repeat_every        = $task['repeat_every'];
            $last_recurring_date = $task['last_recurring_date'];
            $task_date           = $task['startdate'];

            // Current date
            $date = new DateTime(date('Y-m-d'));
            // Check if is first recurring
            if (!$last_recurring_date) {
                $last_recurring_date = date('Y-m-d', strtotime($task_date));
            } else {
                $last_recurring_date = date('Y-m-d', strtotime($last_recurring_date));
            }

            $calculated_date_difference = date('Y-m-d', strtotime('+' . $repeat_every . ' ' . strtoupper($type), strtotime($last_recurring_date)));

            if (date('Y-m-d') >= $calculated_date_difference) {
                $copy_task_data['copy_task_followers']       = 'true';
                $copy_task_data['copy_task_checklist_items'] = 'true';
                $copy_task_data['copy_from']                 = $task['id'];

                $newTaskID = $this->tasks_model->copy($copy_task_data, array(
                    'status' => do_action('recurring_task_status', 1),
                    'recurring_type' => null,
                    'repeat_every' => 0,
                    'recurring' => 0,
                    'recurring_ends_on' => null,
                    'custom_recurring' => 0,
                    'last_recurring_date' => null,
                ));
                if ($newTaskID) {
                    $this->db->where('id', $task['id']);
                    $this->db->update('tblstafftasks', array(
                        'last_recurring_date' => date('Y-m-d'),
                    ));
                    $this->db->where('taskid', $task['id']);
                    $assigned = $this->db->get('tblstafftaskassignees')->result_array();
                    foreach ($assigned as $assignee) {
                        $assigneeId = $this->tasks_model->add_task_assignees(array(
                            'taskid'=>$newTaskID,
                            'assignee'=>$assignee['staffid'],
                        ), true);

                        if ($assigneeId) {
                            $this->db->where('id', $assigneeId);
                            $this->db->update('tblstafftaskassignees', array('assigned_from'=>$task['addedfrom']));
                        }
                    }
                }
            }
        }
    }

    private function recurring_expenses()
    {
        $expenses_hour_auto_operations = get_option('expenses_auto_operations_hour');
        if ($expenses_hour_auto_operations == '') {
            $expenses_hour_auto_operations = 9;
        }

        $hour_now = date('G');
        if ($hour_now < $expenses_hour_auto_operations && $this->manually == false) {
            return;
        }

        $this->db->where('recurring', 1);
        $recurring_expenses = $this->db->get('tblexpenses')->result_array();
        // Load the necessary models
        $this->load->model('invoices_model');
        $this->load->model('expenses_model');

        $_renewals_ids_data = array();
        $total_renewed      = 0;

        foreach ($recurring_expenses as $expense) {
            if (!is_null($expense['recurring_ends_on'])) {
                if (date('Y-m-d') > date('Y-m-d', strtotime($expense['recurring_ends_on']))) {
                    continue;
                }
            }
            $type                     = $expense['recurring_type'];
            $repeat_every             = $expense['repeat_every'];
            $last_recurring_date      = $expense['last_recurring_date'];
            $create_invoice_billable  = $expense['create_invoice_billable'];
            $send_invoice_to_customer = $expense['send_invoice_to_customer'];
            $expense_date             = $expense['date'];
            // Current date
            $date                     = new DateTime(date('Y-m-d'));
            // Check if is first recurring
            if (!$last_recurring_date) {
                $last_recurring_date = date('Y-m-d', strtotime($expense_date));
            } else {
                $last_recurring_date = date('Y-m-d', strtotime($last_recurring_date));
            }
            $calculated_date_difference = date('Y-m-d', strtotime('+' . $repeat_every . ' ' . strtoupper($type), strtotime($last_recurring_date)));

            if (date('Y-m-d') >= $calculated_date_difference) {
                // Ok we can repeat the expense now
                $new_expense_data = array();
                $expense_fields   = $this->db->list_fields('tblexpenses');
                foreach ($expense_fields as $field) {
                    if (isset($expense[$field])) {
                        // We dont need the invoiceid field
                        if ($field != 'invoiceid' && $field != 'id' && $field != 'recurring_from') {
                            $new_expense_data[$field] = $expense[$field];
                        }
                    }
                }

                $new_expense_data['dateadded']      = date('Y-m-d H:i:s');
                $new_expense_data['date']           = date('Y-m-d');
                $new_expense_data['recurring_from'] = $expense['id'];
                $new_expense_data['addedfrom']      = $expense['addedfrom'];

                $new_expense_data['recurring_type']      = null;
                $new_expense_data['repeat_every']        = 0;
                $new_expense_data['recurring']           = 0;
                $new_expense_data['recurring_ends_on']   = null;
                $new_expense_data['custom_recurring']    = 0;
                $new_expense_data['last_recurring_date'] = null;

                $this->db->insert('tblexpenses', $new_expense_data);
                $insert_id = $this->db->insert_id();
                if ($insert_id) {
                    // Get the old expense custom field and add to the new
                    $custom_fields = get_custom_fields('expenses');
                    foreach ($custom_fields as $field) {
                        $value = get_custom_field_value($expense['id'], $field['id'], 'expenses');
                        if ($value != '') {
                            $this->db->insert('tblcustomfieldsvalues', array(
                                'relid' => $insert_id,
                                'fieldid' => $field['id'],
                                'fieldto' => 'expenses',
                                'value' => $value,
                            ));
                        }
                    }
                    $total_renewed++;
                    $this->db->where('id', $expense['id']);
                    $this->db->update('tblexpenses', array(
                        'last_recurring_date' => date('Y-m-d'),
                    ));
                    $sent               = false;
                    $created_invoice_id = '';
                    if ($expense['create_invoice_billable'] == 1) {
                        $invoiceid = $this->expenses_model->convert_to_invoice($insert_id);
                        if ($invoiceid) {
                            $created_invoice_id = $invoiceid;
                            if ($expense['send_invoice_to_customer'] == 1) {
                                $sent = $this->invoices_model->send_invoice_to_client($invoiceid, 'invoice-send-to-client', true);
                            }
                        }
                    }
                    $_renewals_ids_data[] = array(
                        'from' => $expense['id'],
                        'renewed' => $insert_id,
                        'send_invoice_to_customer' => $expense['send_invoice_to_customer'],
                        'create_invoice_billable' => $expense['create_invoice_billable'],
                        'is_sent' => $sent,
                        'addedfrom' => $expense['addedfrom'],
                        'created_invoice_id' => $created_invoice_id,
                    );
                }
            }
        }

        $send_recurring_expenses_email = do_action('send_recurring_system_expenses_email', 'true');
        if ($total_renewed > 0 && $send_recurring_expenses_email == 'true') {
            $this->load->model('currencies_model');
            $email_send_to_by_staff_and_expense = array();
            $date                               = _dt(date('Y-m-d H:i:s'));
            // Get all active staff members
            $staff                              = $this->staff_model->get('', 1);
            foreach ($staff as $member) {
                $send = false;
                load_admin_language($member['staffid']);
                $recurring_expenses_email_data = _l('not_recurring_expense_cron_activity_heading') . ' - ' . $date . '<br /><br />';
                foreach ($_renewals_ids_data as $data) {
                    if ($data['addedfrom'] == $member['staffid'] || is_admin($member['staffid'])) {
                        $unique_send = '[' . $member['staffid'] . '-' . $data['from'] . ']';
                        $send        = true;
                        // Prevent sending the email twice if the same staff is added is sale agent and is creator for this invoice.
                        if (in_array($unique_send, $email_send_to_by_staff_and_expense)) {
                            $send = false;
                        }
                        $expense  = $this->expenses_model->get($data['from']);
                        $currency = $this->currencies_model->get($expense->currency);
                        $recurring_expenses_email_data .= _l('not_recurring_expenses_action_taken_from') . ': <a href="' . admin_url('expenses/list_expenses/' . $data['from']) . '">' . $expense->category_name . (!empty($expense->expense_name) ? ' (' . $expense->expense_name . ')' : '') . '</a> - ' . _l('expense_amount') . ' ' . format_money($expense->amount, $currency->symbol) . '<br />';
                        $recurring_expenses_email_data .= _l('not_expense_renewed') . ' <a href="' . admin_url('expenses/list_expenses/' . $data['renewed']) . '">' . _l('id') . ' ' . $data['renewed'] . '</a>';
                        if ($data['create_invoice_billable'] == 1) {
                            $recurring_expenses_email_data .= '<br />' . _l('not_invoice_created') . ' ';
                            if (is_numeric($data['created_invoice_id'])) {
                                $recurring_expenses_email_data .= _l('not_invoice_sent_yes');
                                if ($data['send_invoice_to_customer'] == 1) {
                                    if ($data['is_sent']) {
                                        $invoice_sent = 'not_invoice_sent_yes';
                                    } else {
                                        $invoice_sent = 'not_invoice_sent_no';
                                    }
                                    $recurring_expenses_email_data .= '<br />' . _l('not_invoice_sent_to_customer', _l($invoice_sent));
                                }
                            } else {
                                $recurring_expenses_email_data .= _l('not_invoice_sent_no');
                            }
                        }
                        $recurring_expenses_email_data .= '<br /><br />';
                    }
                }
                if ($send == true) {
                    array_push($email_send_to_by_staff_and_expense, $unique_send);
                    $this->emails_model->send_simple_email($member['email'], _l('not_recurring_expense_cron_activity_heading'), $recurring_expenses_email_data);
                }
                load_admin_language();
            }
        }
    }

    private function recurring_invoices()
    {
        $invoice_hour_auto_operations = get_option('invoice_auto_operations_hour');
        if ($invoice_hour_auto_operations == '') {
            $invoice_hour_auto_operations = 9;
        }
        $hour_now = date('G');
        if ($hour_now < $invoice_hour_auto_operations && $this->manually == false) {
           return;
        }

        $new_recurring_invoice_action = get_option('new_recurring_invoice_action');

        $invoices_create_invoice_from_recurring_only_on_paid_invoices = get_option('invoices_create_invoice_from_recurring_only_on_paid_invoices');
        $this->load->model('invoices_model');
        $this->db->select('id,recurring,date,last_recurring_date,number,duedate,recurring_ends_on,recurring_type,custom_recurring,addedfrom,sale_agent,clientid');
        $this->db->from('tblinvoices');
        $this->db->where('recurring !=', 0);
        if ($invoices_create_invoice_from_recurring_only_on_paid_invoices == 1) {
            // Includes all recurring invoices with paid status if this option set to Yes
            $this->db->where('status', 2);
        }
        $this->db->where('status !=', 6);
        $invoices = $this->db->get()->result_array();

        $_renewals_ids_data = array();
        $total_renewed      = 0;
        foreach ($invoices as $invoice) {
            if (!is_null($invoice['recurring_ends_on'])) {
                if (date('Y-m-d') > date('Y-m-d', strtotime($invoice['recurring_ends_on']))) {
                    continue;
                }
            }
            // Current date
            $date = new DateTime(date('Y-m-d'));
            // Check if is first recurring
            if (!$invoice['last_recurring_date']) {
                $last_recurring_date = date('Y-m-d', strtotime($invoice['date']));
            } else {
                $last_recurring_date = date('Y-m-d', strtotime($invoice['last_recurring_date']));
            }
            if ($invoice['custom_recurring'] == 0) {
                $invoice['recurring_type'] = 'MONTH';
            }

            $calculated_date_difference = date('Y-m-d', strtotime('+' . $invoice['recurring'] . ' ' . strtoupper($invoice['recurring_type']), strtotime($last_recurring_date)));

            if (date('Y-m-d') >= $calculated_date_difference) {

                // Recurring invoice date is okey lets convert it to new invoice
                $_invoice                     = $this->invoices_model->get($invoice['id']);
                $new_invoice_data             = array();
                $new_invoice_data['clientid'] = $_invoice->clientid;
                $new_invoice_data['number']   = get_option('next_invoice_number');
                $new_invoice_data['date']     = _d(date('Y-m-d'));
                $new_invoice_data['duedate'] = null;

                if ($_invoice->duedate) {
                    // Now we need to get duedate from the old invoice and calculate the time difference and set new duedate
                    // Ex. if the first invoice had duedate 20 days from now we will add the same duedate date but starting from now
                    $dStart                      = new DateTime($invoice['date']);
                    $dEnd                        = new DateTime($invoice['duedate']);
                    $dDiff                       = $dStart->diff($dEnd);
                    $new_invoice_data['duedate'] = _d(date('Y-m-d', strtotime(date('Y-m-d', strtotime('+' . $dDiff->days . 'DAY')))));
                } else {
                    if (get_option('invoice_due_after') != 0) {
                        $new_invoice_data['duedate'] = _d(date('Y-m-d', strtotime('+' . get_option('invoice_due_after') . ' DAY', strtotime(date('Y-m-d')))));
                    }
                }

                $new_invoice_data['project_id']       = $_invoice->project_id;
                $new_invoice_data['show_quantity_as'] = $_invoice->show_quantity_as;
                $new_invoice_data['currency']         = $_invoice->currency;
                $new_invoice_data['subtotal']         = $_invoice->subtotal;
                $new_invoice_data['total']            = $_invoice->total;
                $new_invoice_data['adjustment']       = $_invoice->adjustment;
                $new_invoice_data['discount_percent'] = $_invoice->discount_percent;
                $new_invoice_data['discount_total']   = $_invoice->discount_total;
                $new_invoice_data['discount_type']    = $_invoice->discount_type;
                $new_invoice_data['terms']            = clear_textarea_breaks($_invoice->terms);
                $new_invoice_data['sale_agent']       = $_invoice->sale_agent;
                // Since version 1.0.6
                $new_invoice_data['billing_street']   = clear_textarea_breaks($_invoice->billing_street);
                $new_invoice_data['billing_city']     = $_invoice->billing_city;
                $new_invoice_data['billing_state']    = $_invoice->billing_state;
                $new_invoice_data['billing_zip']      = $_invoice->billing_zip;
                $new_invoice_data['billing_country']  = $_invoice->billing_country;
                $new_invoice_data['shipping_street']  = clear_textarea_breaks($_invoice->shipping_street);
                $new_invoice_data['shipping_city']    = $_invoice->shipping_city;
                $new_invoice_data['shipping_state']   = $_invoice->shipping_state;
                $new_invoice_data['shipping_zip']     = $_invoice->shipping_zip;
                $new_invoice_data['shipping_country'] = $_invoice->shipping_country;
                if ($_invoice->include_shipping == 1) {
                    $new_invoice_data['include_shipping'] = $_invoice->include_shipping;
                }
                $new_invoice_data['include_shipping']         = $_invoice->include_shipping;
                $new_invoice_data['show_shipping_on_invoice'] = $_invoice->show_shipping_on_invoice;
                // Determine status based on settings
                if ($new_recurring_invoice_action == 'generate_and_send' || $new_recurring_invoice_action == 'generate_unpaid') {
                    $new_invoice_data['status']                   = 1;
                } elseif ($new_recurring_invoice_action == 'generate_draft') {
                    $new_invoice_data['save_as_draft']        = true;
                }
                $new_invoice_data['clientnote']               = clear_textarea_breaks($_invoice->clientnote);
                $new_invoice_data['adminnote']                = '';
                $new_invoice_data['allowed_payment_modes']    = unserialize($_invoice->allowed_payment_modes);
                $new_invoice_data['is_recurring_from']        = $_invoice->id;
                $new_invoice_data['newitems']                 = array();
                $key                                          = 1;
                foreach ($_invoice->items as $item) {
                    $new_invoice_data['newitems'][$key]['description']      = $item['description'];
                    $new_invoice_data['newitems'][$key]['long_description'] = clear_textarea_breaks($item['long_description']);
                    $new_invoice_data['newitems'][$key]['qty']              = $item['qty'];
                    $new_invoice_data['newitems'][$key]['unit']             = $item['unit'];
                    $new_invoice_data['newitems'][$key]['taxname']          = array();
                    $taxes                                                  = get_invoice_item_taxes($item['id']);
                    foreach ($taxes as $tax) {
                        // tax name is in format TAX1|10.00
                        array_push($new_invoice_data['newitems'][$key]['taxname'], $tax['taxname']);
                    }
                    $new_invoice_data['newitems'][$key]['rate']  = $item['rate'];
                    $new_invoice_data['newitems'][$key]['order'] = $item['item_order'];
                    $key++;
                }
                $id = $this->invoices_model->add($new_invoice_data);
                if ($id) {
                    $this->db->where('id', $id);
                    $this->db->update('tblinvoices', array(
                        'addedfrom' => $_invoice->addedfrom,
                        'sale_agent' => $_invoice->sale_agent,
                        'cancel_overdue_reminders' => $_invoice->cancel_overdue_reminders,
                    ));
                    // Get the old expense custom field and add to the new
                    $custom_fields = get_custom_fields('invoice');
                    foreach ($custom_fields as $field) {
                        $value = get_custom_field_value($invoice['id'], $field['id'], 'invoice');
                        if ($value != '') {
                            $this->db->insert('tblcustomfieldsvalues', array(
                                'relid' => $id,
                                'fieldid' => $field['id'],
                                'fieldto' => 'invoice',
                                'value' => $value,
                            ));
                        }
                    }
                    // Increment total renewed invoices
                    $total_renewed++;
                    // Update last recurring date to this invoice
                    $this->db->where('id', $invoice['id']);
                    $this->db->update('tblinvoices', array(
                        'last_recurring_date' => date('Y-m-d'),
                    ));

                    if ($new_recurring_invoice_action == 'generate_and_send') {
                        $this->invoices_model->send_invoice_to_client($id, 'invoice-send-to-client', true);
                    }

                    $_renewals_ids_data[] = array(
                        'from' => $invoice['id'],
                        'clientid'=>$invoice['clientid'],
                        'renewed' => $id,
                        'addedfrom' => $invoice['addedfrom'],
                        'sale_agent' => $invoice['sale_agent'],
                    );
                }
            }
        }

        $send_recurring_invoices_email = do_action('send_recurring_invoices_system_email', 'true');
        if ($total_renewed > 0 && $send_recurring_invoices_email == 'true') {
            $date                               = _dt(date('Y-m-d H:i:s'));
            $email_send_to_by_staff_and_invoice = array();
            // Get all active staff members
            $staff                              = $this->staff_model->get('', 1);
            foreach ($staff as $member) {
                $send = false;
                load_admin_language($member['staffid']);
                $recurring_invoices_email_data = _l('not_recurring_invoices_cron_activity_heading') . ' - ' . $date . '<br /><br />';
                foreach ($_renewals_ids_data as $renewed_invoice_data) {
                    if ($renewed_invoice_data['addedfrom'] == $member['staffid'] || $renewed_invoice_data['sale_agent'] == $member['staffid'] || is_admin($member['staffid'])) {
                        $unique_send = '[' . $member['staffid'] . '-' . $renewed_invoice_data['from'] . ']';
                        $send        = true;
                        // Prevent sending the email twice if the same staff is added is sale agent and is creator for this invoice.
                        if (in_array($unique_send, $email_send_to_by_staff_and_invoice)) {
                            $send = false;
                        }
                        $recurring_invoices_email_data .= _l('not_action_taken_from_recurring_invoice') . ' <a href="' . admin_url('invoices/list_invoices/' . $renewed_invoice_data['from']) . '">' . format_invoice_number($renewed_invoice_data['from']) . '</a><br />';
                        $recurring_invoices_email_data .= _l('not_invoice_renewed') . ' <a href="' . admin_url('invoices/list_invoices/' . $renewed_invoice_data['renewed']) . '">' . format_invoice_number($renewed_invoice_data['renewed']) . '</a> - <a href="'.admin_url('clients/client/'.$renewed_invoice_data['clientid']).'">'.get_company_name($renewed_invoice_data['clientid']).'</a><br /><br />';
                    }
                }
                if ($send == true) {
                    array_push($email_send_to_by_staff_and_invoice, $unique_send);
                    $this->emails_model->send_simple_email($member['email'], _l('not_recurring_invoices_cron_activity_heading'), $recurring_invoices_email_data);
                }
            }
            load_admin_language();
        }
    }

    private function tasks_reminders()
    {
        $reminder_before = get_option('tasks_reminder_notification_before');
        $this->db->where('status !=', 5);
        $this->db->where('duedate IS NOT NULL');
        $this->db->where('deadline_notified', 0);
        $tasks = $this->db->get('tblstafftasks')->result_array();
        $now   = new DateTime(date('Y-m-d'));

        $notifiedUsers = array();

        foreach ($tasks as $task) {
            if (date('Y-m-d', strtotime($task['duedate'])) > date('Y-m-d')) {
                $duedate                 = new DateTime($task['duedate']);
                $diff                    = $duedate->diff($now)->format("%a");
                // Check if difference between start date and duedate is the same like the reminder before
                // In this case reminder wont be sent becuase the task it too short
                $start_date              = strtotime($task['startdate']);
                $duedate                 = strtotime($task['duedate']);
                $start_and_due_date_diff = $duedate - $start_date;
                $start_and_due_date_diff = floor($start_and_due_date_diff / (60 * 60 * 24));
                if ($diff <= $reminder_before && $start_and_due_date_diff > $reminder_before) {
                    $assignees = $this->tasks_model->get_task_assignees($task['id']);

                    foreach ($assignees as $member) {
                        $this->db->select('email');
                        $this->db->where('staffid', $member['assigneeid']);
                        $row = $this->db->get('tblstaff')->row();
                        if ($row) {
                            $notified = add_notification(array(
                                'description' => 'not_task_deadline_reminder',
                                'touserid' => $member['assigneeid'],
                                'fromcompany' => 1,
                                'fromuserid' => null,
                                'link' => '#taskid=' . $task['id'],
                                'additional_data' => serialize(array(
                                    $task['name'],
                                )),
                            ));

                            if ($notified) {
                                array_push($notifiedUsers, $member['assigneeid']);
                            }

                            $merge_fields = array();
                            $merge_fields = array_merge($merge_fields, get_staff_merge_fields($member['assigneeid']));
                            $merge_fields = array_merge($merge_fields, get_task_merge_fields($task['id']));
                            $this->emails_model->send_email_template('task-deadline-notification', $row->email, $merge_fields);
                            $this->db->where('id', $task['id']);
                            $this->db->update('tblstafftasks', array(
                                'deadline_notified' => 1,
                            ));
                        }
                    }
                }
            }
        }

        pusher_trigger_notification($notifiedUsers);
    }

    private function surveys()
    {
        $last_survey_cron = get_option('last_survey_send_cron');
        if ($last_survey_cron == '' || (time() > ($last_survey_cron + 3600))) {
            $found_emails = $this->db->count_all_results('tblsurveysemailsendcron');
            if ($found_emails > 0) {
                $total_emails_per_cron = get_option('survey_send_emails_per_cron_run');
                // Initialize mail library
                $this->email->initialize();
                $this->load->library('email');
                // Load survey model
                $this->load->model('surveys_model');
                // Get all surveys send log where sending emails is not finished
                $this->db->where('iscronfinished', 0);
                $unfinished_surveys_send_log = $this->db->get('tblsurveysendlog')->result_array();
                foreach ($unfinished_surveys_send_log as $_survey) {
                    $surveyid = $_survey['surveyid'];
                    // Get survey emails that has been not sent yet.
                    $this->db->where('surveyid', $surveyid);
                    $this->db->limit($total_emails_per_cron);
                    $emails = $this->db->get('tblsurveysemailsendcron')->result_array();
                    $survey = $this->surveys_model->get($surveyid);
                    if ($survey->fromname == '' || $survey->fromname == null) {
                        $survey->fromname = get_option('companyname');
                    }
                    if (stripos($survey->description, '{survey_link}') !== false) {
                        $survey->description = str_ireplace('{survey_link}', '<a href="' . site_url('survey/' . $survey->surveyid . '/' . $survey->hash) . '" target="_blank">' . $survey->subject . '</a>', $survey->description);
                    }
                    $total = 0;
                    foreach ($emails as $data) {
                        if (isset($data['emailid']) && isset($data['listid'])) {
                            $customfields = $this->surveys_model->get_list_custom_fields($data['listid']);
                            foreach ($customfields as $custom_field) {
                                $value                     = $this->surveys_model->get_email_custom_field_value($data['emailid'], $data['listid'], $custom_field['customfieldid']);
                                $custom_field['fieldslug'] = '{' . $custom_field['fieldslug'] . '}';
                                if ($value != '') {
                                    if (stripos($survey->description, $custom_field['fieldslug']) !== false) {
                                        $survey->description = str_ireplace($custom_field['fieldslug'], $value, $survey->description);
                                    }
                                }
                            }
                        }
                        $this->email->clear();
                        $this->email->from(get_option('smtp_email'), $survey->fromname);
                        $this->email->to($data['email']);
                        $this->email->subject($survey->subject);
                        $this->email->message($survey->description);

                        if ($this->email->send(true)) {
                            $total++;
                        }

                        $this->db->where('id', $data['id']);
                        $this->db->delete('tblsurveysemailsendcron');
                    }
                    // Update survey send log
                    $this->db->where('id', $_survey['id']);
                    $this->db->update('tblsurveysendlog', array(
                        'total' => $total,
                    ));
                    // Check if all emails send
                    $this->db->where('surveyid', $surveyid);
                    $found_emails = $this->db->count_all_results('tblsurveysemailsendcron');
                    if ($found_emails == 0) {
                        // Update that survey send is finished
                        $this->db->where('id', $_survey['id']);
                        $this->db->update('tblsurveysendlog', array(
                            'iscronfinished' => 1,
                        ));
                    }
                }
                update_option('last_survey_send_cron', time());
            }
        }
    }

    private function staff_reminders()
    {
        $this->db->select('tblreminders.*, email');
        $this->db->join('tblstaff', 'tblstaff.staffid=tblreminders.staff');
        $this->db->where('isnotified', 0);
        $reminders = $this->db->get('tblreminders')->result_array();
        $notifiedUsers = array();

        foreach ($reminders as $reminder) {
            if (date('Y-m-d H:i:s') >= $reminder['date']) {
                $rel_data   = get_relation_data($reminder['rel_type'], $reminder['rel_id']);
                $rel_values = get_relation_values($rel_data, $reminder['rel_type']);

                $notificationLink = str_replace(admin_url(), '', $rel_values['link']);
                $notificationLink = ltrim($notificationLink, '/');

                $notified = add_notification(array(
                    'fromcompany' => true,
                    'touserid' => $reminder['staff'],
                    'description' => 'not_new_reminder_for',
                    'link' => $notificationLink,
                    'additional_data' => serialize(array(
                        $rel_values['name'] . ' - ' . strip_tags(mb_substr($reminder['description'], 0, 50)) . '...',
                    )),
                ));

                if ($notified) {
                    array_push($notifiedUsers, $reminder['staff']);
                }

                if ($reminder['notify_by_email'] == 1) {
                    $merge_fields = array();
                    $merge_fields = array_merge($merge_fields, get_staff_merge_fields($reminder['staff']));
                    $merge_fields = array_merge($merge_fields, get_staff_reminder_merge_fields($reminder));
                    $this->emails_model->send_email_template('reminder-email-staff', $reminder['email'], $merge_fields);
                }

                $this->db->where('id', $reminder['id']);
                $this->db->update('tblreminders', array(
                    'isnotified' => 1,
                ));
            }
        }

        pusher_trigger_notification($notifiedUsers);
    }

    private function invoice_overdue()
    {
        $invoice_auto_operations_hour = get_option('invoice_auto_operations_hour');
        if ($invoice_auto_operations_hour == '') {
            $invoice_auto_operations_hour = 9;
        }

        $hour_now = date('G');
        if ($hour_now < $invoice_auto_operations_hour && $this->manually == false) {
            return;
        }

        $this->load->model('invoices_model');
        $send_invoice_overdue_reminder = get_option('cron_send_invoice_overdue_reminder');
        $this->db->select('id,date,status,last_overdue_reminder,duedate,cancel_overdue_reminders');
        $this->db->from('tblinvoices');
        $this->db->where('(duedate != "" AND duedate IS NOT NULL)'); // We dont need invoices with no duedate
        $this->db->where('status !=', 2); // We dont need paid status
        $this->db->where('status !=', 5); // We dont need cancelled status
        $this->db->where('status !=', 6); // We dont need draft status
        $invoices = $this->db->get()->result_array();

        $now      = time();
        foreach ($invoices as $invoice) {
            $statusid = update_invoice_status($invoice['id']);

            if ($send_invoice_overdue_reminder == '1' && $invoice['cancel_overdue_reminders'] == 0) {
                if ($invoice['status'] == '4' || $statusid == '4' || $invoice['status'] == 3) {
                    if ($invoice['status'] == 3) {
                        // Invoice is with status partialy paid and its not due
                        if (date('Y-m-d') <= date('Y-m-d', strtotime($invoice['duedate']))) {
                            continue;
                        }
                    }
                    // Check if already sent invoice reminder
                    if ($invoice['last_overdue_reminder']) {
                        // We already have sent reminder, check for resending
                        $resend_days = get_option('automatically_resend_invoice_overdue_reminder_after');
                        // If resend_days from options is 0 means that the admin dont want to resend the mails.
                        if ($resend_days != 0) {
                            $datediff  = $now - strtotime($invoice['last_overdue_reminder']);
                            $days_diff = floor($datediff / (60 * 60 * 24));
                            if ($days_diff >= $resend_days) {
                                $this->invoices_model->send_invoice_overdue_notice($invoice['id']);
                            }
                        }
                    } else {
                        $datediff  = $now - strtotime($invoice['duedate']);
                        $days_diff = floor($datediff / (60 * 60 * 24));
                        if ($days_diff >= get_option('automatically_send_invoice_overdue_reminder_after')) {
                            $this->invoices_model->send_invoice_overdue_notice($invoice['id']);
                        }
                    }
                }
            }
        }
    }

    public function proposals()
    {
        $proposals_auto_operations_hour = get_option('proposals_auto_operations_hour');

        if ($proposals_auto_operations_hour == '') {
            $proposals_auto_operations_hour = 9;
        }

        $hour_now = date('G');
        if ($hour_now < $proposals_auto_operations_hour && $this->manually == false) {
            return;
        }

        $this->load->model('proposals_model');

        $this->db->select('open_till,date,id');
        // Only 1 = open, 4 = sent
        $this->db->where('status IN (1,4)');
        $this->db->where('is_expiry_notified', 0);
        $proposals = $this->db->get('tblproposals')->result_array();
        $now       = new DateTime(date('Y-m-d'));

        foreach ($proposals as $proposal) {
            if ($proposal['open_till'] != null
                && date('Y-m-d') < $proposal['open_till']
                && get_option('proposal_expiry_reminder_enabled') == 1) {
                $reminder_before        = get_option('send_proposal_expiry_reminder_before');
                $open_till              = new DateTime($proposal['open_till']);
                $diff                   = $open_till->diff($now)->format("%a");
                $date                   = strtotime($proposal['date']);
                $open_till              = strtotime($proposal['open_till']);
                $date_and_due_date_diff = $open_till - $date;
                $date_and_due_date_diff = floor($date_and_due_date_diff / (60 * 60 * 24));

                if ($diff <= $reminder_before && $date_and_due_date_diff > $reminder_before) {
                    $this->proposals_model->send_expiry_reminder($proposal['id']);
                }
            }
        }
    }

    private function estimate_expiration()
    {
        $estimates_auto_operations_hour = get_option('estimates_auto_operations_hour');

        if ($estimates_auto_operations_hour == '') {
            $estimates_auto_operations_hour = 9;
        }

        $hour_now = date('G');
        if ($hour_now < $estimates_auto_operations_hour && $this->manually == false) {
            return;
        }

        $this->db->select('id,expirydate,status,is_expiry_notified,date');
        $this->db->from('tblestimates');
        // Only get sent estimates
        $this->db->where('status', 2);
        $estimates = $this->db->get()->result_array();
        $this->load->model('estimates_model');
        $now = new DateTime(date('Y-m-d'));
        foreach ($estimates as $estimate) {
            if ($estimate['expirydate'] != null) {
                if (date('Y-m-d') > $estimate['expirydate']) {
                    $this->db->where('id', $estimate['id']);
                    $this->db->update('tblestimates', array(
                        'status' => 5,
                    ));
                    if ($this->db->affected_rows() > 0) {
                        $additional_activity = serialize(array(
                            '<original_status>' . $estimate['status'] . '</original_status>',
                            '<new_status>5</new_status>',
                        ));
                        $this->estimates_model->log_estimate_activity($estimate['id'], 'not_estimate_status_updated', false, $additional_activity);
                    }
                } else {
                    if (get_option('estimate_expiry_reminder_enabled') == 1) {
                        $reminder_before        = get_option('send_estimate_expiry_reminder_before');
                        $expirydate             = new DateTime($estimate['expirydate']);
                        $diff                   = $expirydate->diff($now)->format("%a");
                        $date                   = strtotime($estimate['date']);
                        $expirydate             = strtotime($estimate['expirydate']);
                        $date_and_due_date_diff = $expirydate - $date;
                        $date_and_due_date_diff = floor($date_and_due_date_diff / (60 * 60 * 24));
                        if ($estimate['is_expiry_notified'] == 0
                            && $diff <= $reminder_before
                            && $date_and_due_date_diff > $reminder_before) {
                            $this->estimates_model->send_expiry_reminder($estimate['id']);
                        }
                    }
                }
            }
        }
    }

    public function goals_notification()
    {
        $this->load->model('goals_model');
        $goals = $this->goals_model->get('', true);
        foreach ($goals as $goal) {
            $achievement = $this->goals_model->calculate_goal_achievement($goal['id']);
            if ($achievement['percent'] >= 100) {
                if ($goal['notify_when_achieve'] == 1) {
                    if (date('Y-m-d') >= $goal['end_date']) {
                        $this->goals_model->notify_staff_members($goal['id'], 'success', $achievement);
                    }
                }
            } else {
                // not yet achieved, check for end date
                if ($goal['notify_when_fail'] == 1) {
                    if (date('Y-m-d') > $goal['end_date']) {
                        $this->goals_model->notify_staff_members($goal['id'], 'failed', $achievement);
                    }
                }
            }
        }
    }

    public function check_leads_email_integration()
    {
        $this->load->model('leads_model');
        $mail = $this->leads_model->get_email_integration();
        if ($mail->active == 0) {
            return false;
        }
        require_once(APPPATH . 'third_party/php-imap/Imap.php');

        if (empty($mail->last_run) || (time() > $mail->last_run + ($mail->check_every * 60))) {
            $this->db->where('id', 1);
            $this->db->update('tblleadsintegration', array(
                'last_run' => time(),
            ));
            $ps = $this->encryption->decrypt($mail->password);
            if (!$ps) {
                if (ENVIRONMENT !== 'production') {
                    logActivity('Failed to decrypt email integration password', null);
                }

                return false;
            }
            $mailbox    = $mail->imap_server;
            $username   = $mail->email;
            $password   = $ps;
            $encryption = $mail->encryption;
            // open connection
            $imap       = new Imap($mailbox, $username, $password, $encryption);
            if ($imap->isConnected() === false) {
                return false;
            }
            if ($mail->folder == '') {
                $mail->folder = 'INBOX';
            }
            $imap->selectFolder($mail->folder);
            if ($mail->only_loop_on_unseen_emails == 1) {
                $emails = $imap->getUnreadMessages();
            } else {
                $emails = $imap->getMessages();
            }

            foreach ($emails as $email) {
                $from      = $email['from'];
                $fromname  = preg_replace("/(.*)<(.*)>/", "\\1", $from);
                $fromname  = trim(str_replace("\"", "", $fromname));
                $fromemail = trim(preg_replace("/(.*)<(.*)>/", "\\2", $from));
                $body = $this->prepare_imap_email_body_html($email['body']);
                // Okey everything good now let make some statements
                // Check if this email exists in customers table first
                $this->db->select('id,userid');
                $this->db->where('email', $fromemail);
                $contact = $this->db->get('tblcontacts')->row();
                if ($contact) {

                    // Set message to seen to in the next time we dont need to loop over this message
                    $imap->setUnseenMessage($email['uid']);

                    if($mail->create_task_if_customer == '1'){

                    load_admin_language($mail->responsible);

                    $body = '<b>'._l('leads_email_integration') .' (' . _l('existing_customer') . ')</b> - <a href="'.admin_url('clients/client/'.$contact->userid.'?contactid='.$contact->id).'" target="_blank"><b>' . get_company_name($contact->userid) .'</b></a><br /><br />'.$body;

                    load_admin_language();

                        $task_data = array(
                                        'name' => $fromname . ' - ' . $fromemail,
                                        'priority' => get_option('default_task_priority'),
                                        'dateadded' => date('Y-m-d H:i:s'),
                                        'startdate' => date('Y-m-d'),
                                        'addedfrom' => $mail->responsible,
                                        'status' => 1,
                                        'description' => $body,
                                        );

                        $task_data = do_action('before_add_task', $task_data);
                        $this->db->insert('tblstafftasks', $task_data);

                        $task_id = $this->db->insert_id();
                        if ($task_id) {
                            $assignee_data = array(
                                            'taskid' => $task_id,
                                            'assignee' => $mail->responsible,
                                            );

                            $this->tasks_model->add_task_assignees($assignee_data, true);
                            $this->_check_lead_email_integration_attachments($email, false, $imap, $task_id);
                            do_action('after_add_task', $task_id);
                        }
                    }

                    // Exists no need to do anything
                    continue;
                } else {
                    // Not exists its okey.
                    // Now we need to check the leads table
                    $this->db->where('email', $fromemail);
                    $lead = $this->db->get('tblleads')->row();

                    if ($lead) {
                        // Check if the lead uid is the same with the email uid
                        if ($lead->email_integration_uid == $email['uid']) {
                            // Set message to seen to in the next time we dont need to loop over this message
                            $imap->setUnseenMessage($email['uid']);
                            continue;
                        }
                        // Check if this uid exists in the emails data log table
                        $this->db->where('emailid', $email['uid']);
                        $exists_in_emails = $this->db->count_all_results('tblleadsemailintegrationemails');
                        if ($exists_in_emails > 0) {
                            // Set message to seen to in the next time we dont need to loop over this message
                            $imap->setUnseenMessage($email['uid']);
                            continue;
                        }
                        // We dont need the junk leads
                        if ($lead->junk == 1) {
                            // Set message to seen to in the next time we dont need to loop over this message
                            $imap->setUnseenMessage($email['uid']);
                            continue;
                        }
                        // More the one time email from this lead, insert into the lead emails log table
                        $this->db->insert('tblleadsemailintegrationemails', array(
                            'leadid' => $lead->id,
                            'subject' => trim($email['subject']),
                            'body' => $body,
                            'dateadded' => date('Y-m-d H:i:s'),
                            'emailid' => $email['uid'],
                        ));
                        // Set message to seen to in the next time we dont need to loop over this message
                        $imap->setUnseenMessage($email['uid']);
                        $this->_notification_lead_email_integration('not_received_one_or_more_messages_lead', $mail, $lead->id);
                        $this->_check_lead_email_integration_attachments($email, $lead->id, $imap);
                        // Exists not need to do anything except to add the email
                        continue;
                    }

                    // Lets insert into the leads table
                    $lead_data = array(
                        'name' => $fromname,
                        'assigned' => $mail->responsible,
                        'dateadded' => date('Y-m-d H:i:s'),
                        'status' => $mail->lead_status,
                        'source' => $mail->lead_source,
                        'addedfrom' => 0,
                        'email' => $fromemail,
                        'is_imported_from_email_integration' => 1,
                        'email_integration_uid' => $email['uid'],
                        'lastcontact' => null,
                        'is_public'=>$mail->mark_public,
                    );

                    $lead_data = do_action('before_insert_lead_from_email_integration', $lead_data);

                    $this->db->insert('tblleads', $lead_data);
                    $insert_id = $this->db->insert_id();
                    if ($insert_id) {
                        $this->leads_model->lead_assigned_member_notification($insert_id, $mail->responsible, true);
                        $this->load->helper('simple_html_dom');
                        $html                      = str_get_html($email['body']);
                        $insert_lead_fields        = array();
                        $insert_lead_custom_fields = array();
                        if ($html) {
                            foreach ($html->find('[id^="field_"],[id^="custom_field_"]') as $data) {
                                if (isset($data->plaintext)) {
                                    $value = trim($data->plaintext);
                                    $value = strip_tags($value);
                                    if ($value) {
                                        if (isset($data->attr['id']) && !empty($data->attr['id'])) {
                                            $insert_lead_fields[$data->attr['id']] = $value;
                                        }
                                    }
                                }
                            }
                        }

                        foreach ($insert_lead_fields as $key => $val) {
                            $field = (strpos($key, 'custom_field_') !== false ? strafter($key, 'custom_field_') : strafter($key, 'field_'));

                            if (strpos($key, 'custom_field_') !== false) {
                                $insert_lead_custom_fields[$field] = $val;
                            } elseif ($this->db->field_exists($field, 'tblleads')) {
                                $insert_lead_fields[$field] = $val;
                            }

                            unset($insert_lead_fields[$key]);
                        }

                        foreach ($insert_lead_fields as $field => $value) {
                            if ($field == 'country') {
                                if ($value == '') {
                                    $value = 0;
                                } else {
                                    $this->db->where('iso2', $value);
                                    $this->db->or_where('short_name', $value);
                                    $this->db->or_where('long_name', $value);
                                    $country = $this->db->get('tblcountries')->row();
                                    if ($country) {
                                        $value = $country->country_id;
                                    } else {
                                        $value = 0;
                                    }
                                }
                            }
                            if ($field == 'address') {
                                $value = nl2br($value);
                            }
                            $value = $this->security->xss_clean($value);
                            $this->db->where('id', $insert_id);
                            $this->db->update('tblleads', array(
                                $field => $value,
                            ));
                        }

                        foreach ($insert_lead_custom_fields as $cf_id => $value) {
                            $value = $this->security->xss_clean($value);
                            $this->db->insert('tblcustomfieldsvalues', array(
                                'relid' => $insert_id,
                                'fieldto' => 'leads',
                                'fieldid' => $cf_id,
                                'value' => $value,
                            ));
                        }

                        $this->db->insert('tblleadsemailintegrationemails', array(
                            'leadid' => $insert_id,
                            'subject' => trim($email['subject']),
                            'body' =>   $body,
                            'dateadded' => date('Y-m-d H:i:s'),
                            'emailid' => $email['uid'],
                        ));

                        if ($mail->delete_after_import == 1) {
                            $imap->deleteMessage($email['uid']);
                        } else {
                            $imap->setUnseenMessage($email['uid']);
                        }

                        // Set message to seen to in the next time we dont need to loop over this message
                        $this->_notification_lead_email_integration('not_received_lead_imported_email_integration', $mail, $insert_id);
                        $this->leads_model->log_lead_activity($insert_id, 'not_received_lead_imported_email_integration', true);
                        $this->_check_lead_email_integration_attachments($email, $insert_id, $imap);
                        do_action('lead_created', $insert_id);
                    }
                }
            }
        }
    }

    public function auto_import_imap_tickets()
    {
        $this->db->select('host,encryption,password,email,delete_after_import,imap_username')->from('tbldepartments')->where('host !=', '')->where('password !=', '')->where('email !=', '');
        $dep_emails = $this->db->get()->result_array();
        foreach ($dep_emails as $e) {
            $password = $this->encryption->decrypt($e['password']);
            if (!$password) {
                logActivity('Failed to decrypt department password', null);
                continue;
            }
            require_once(APPPATH . 'third_party/php-imap/Imap.php');
            $mailbox  = $e['host'];
            $username = $e['email'];
            if (!empty($e['imap_username'])) {
                $username = $e['imap_username'];
            }
            $password   = $password;
            $encryption = $e['encryption'];
            // open connection
            $imap       = new Imap($mailbox, $username, $password, $encryption);
            if ($imap->isConnected() === false) {
                logActivity('Failed to connect to IMAP auto importing tickets from departments.', null);
                continue;
            }
            $imap->selectFolder('INBOX');
            $emails = $imap->getUnreadMessages();
            $this->load->model('tickets_model');
            foreach ($emails as $email) {
                // Check if empty body
                if (isset($email['body']) && $email['body'] == '' || !isset($email['body'])) {
                    $email['body'] = 'No message found';
                }

                $email['body'] = $this->prepare_imap_email_body_html($email['body']);

                $data['attachments'] = array();

                if (isset($email['attachments'])) {
                    foreach ($email['attachments'] as $key => $at) {
                        $_at_name = $email['attachments'][$key]['name'];
                        // Rename the name to filename the model expects filename not name
                        unset($email['attachments'][$key]['name']);
                        $email['attachments'][$key]['filename'] = $_at_name;
                        $_attachment                            = $imap->getAttachment($email['uid'], $key);
                        $email['attachments'][$key]['data']     = $_attachment['content'];
                    }
                    // Add the attchments to data
                    $data['attachments'] = $email['attachments'];
                } else {
                    // No attachments
                    $data['attachments'] = array();
                }

                $data['subject']  = $email['subject'];
                $data['body']     = $email['body'];
                $data['email']    = preg_replace("/(.*)<(.*)>/", "\\2", $email['from']);
                $data['fromname'] = preg_replace("/(.*)<(.*)>/", "\\1", $email['from']);
                $data['fromname'] = str_replace("\"", "", $data['fromname']);
                // To is the department name
                $data['to']       = $e['email'];

                $hookData = do_action('imap_auto_import_ticket_data', array('data'=>$data, 'email_contents'=>$email));
                $data = $hookData['data'];

                $status           = $this->tickets_model->insert_piped_ticket($data);
                if ($status == 'Ticket Imported Successfully' || $status == 'Ticket Reply Imported Successfully') {
                    if ($e['delete_after_import'] == 0) {
                        $imap->setUnseenMessage($email['uid']);
                    } else {
                        $imap->deleteMessage($email['uid']);
                    }
                } else {
                    // Set unseen message in all cases to prevent looping throught the message again
                    $imap->setUnseenMessage($email['uid']);
                }
            }
        }
    }

    public function delete_activity_log()
    {
        $older_then_months = get_option('delete_activity_log_older_then');

        if ($older_then_months == 0) {
            return;
        }

        $this->db->query('DELETE FROM tblactivitylog WHERE date < DATE_SUB(NOW(), INTERVAL '.$older_then_months.' MONTH);');
    }

    public function make_backup_db($manual = false)
    {
        if ((get_option('auto_backup_enabled') == "1" && time() > (get_option('last_auto_backup') + get_option('auto_backup_every') * 24 * 60 * 60)) || $manual == true) {
            $this->load->dbutil();
            $prefs       = array(
                'format' => 'zip',
                'filename' => date("Y-m-d-H-i-s") . '_backup.sql',
            );
            $backup      = $this->dbutil->backup($prefs);
            $backup_name = 'database_backup_' . date("Y-m-d-H-i-s") . '.zip';
            $backup_name = unique_filename(BACKUPS_FOLDER, $backup_name);
            $save        = BACKUPS_FOLDER . $backup_name;
            $this->load->helper('file');
            if (write_file($save, $backup)) {
                if ($manual == false) {
                    logActivity('Database Backup [' . $backup_name . ']', null);
                    update_option('last_auto_backup', time());
                } else {
                    logActivity('Database Backup [' . $backup_name . ']');
                }

                $delete_backups = get_option('delete_backups_older_then');
                // After write backup check for delete
                if ($delete_backups != '0') {
                    $backups = list_files(BACKUPS_FOLDER);
                    $backups_days_to_seconds = ($delete_backups * 24 * 60 * 60);
                    foreach ($backups as $b) {
                        if ((time()-filectime(BACKUPS_FOLDER.$b)) > $backups_days_to_seconds) {
                            @unlink(BACKUPS_FOLDER.$b);
                        }
                    }
                }

                return true;
            }
        }

        return false;
    }

    private function _notification_lead_email_integration($description, $mail, $leadid)
    {
        if (!empty($mail->notify_type)) {
            if($mail->notify_type == 'assigned') {
                $ids = array($mail->responsible);
                $field = 'staffid';
            } else {
                $ids = unserialize($mail->notify_ids);
                if (!is_array($ids) || count($ids) == 0) {
                    return;
                }
                if ($mail->notify_type == 'specific_staff') {
                    $field = 'staffid';
                } elseif ($mail->notify_type == 'roles') {
                    $field = 'role';
                } else {
                    return;
                }
            }

            $this->db->where('active', 1);
            $this->db->where_in($field, $ids);
            $staff = $this->db->get('tblstaff')->result_array();

            $notifiedUsers = array();

            foreach ($staff as $member) {
                $notified = add_notification(array(
                    'description' => $description,
                    'touserid' => $member['staffid'],
                    'fromcompany' => 1,
                    'fromuserid' => null,
                    'link' => '#leadid=' . $leadid,
                ));
                if ($notified) {
                    array_push($notifiedUsers, $member['staffid']);
                }
            }
            pusher_trigger_notification($notifiedUsers);
        }
    }

    private function _check_lead_email_integration_attachments($email, $leadid, &$imap, $task_id = false)
    {
        // Check for any attachments
        if (isset($email['attachments'])) {
            foreach ($email['attachments'] as $key => $attachment) {
                $email_attachment = $imap->getAttachment($email['uid'], $key);
                if($task_id != false) {
                    $path        = get_upload_path_by_type('task') . $task_id . '/';
                } else {
                    $path        = get_upload_path_by_type('lead') . $leadid . '/';
                }
                $file_name   = unique_filename($path, $attachment['name']);
                if (!file_exists($path)) {
                    mkdir($path);
                    fopen($path . 'index.html', 'w');
                }
                $path = $path . $file_name;
                $fp   = fopen($path, "w+");
                if (fwrite($fp, $email_attachment['content'])) {
                    $db_attachment   = array();
                    $db_attachment[] = array(
                        'file_name' => $file_name,
                        'filetype' => get_mime_by_extension($attachment['name']),
                        'staffid' => 0,
                    );

                    $attachment_id     = $this->misc_model->add_attachment_to_database(($task_id ? $task_id : $leadid), ($task_id ? 'task' : 'lead'), $db_attachment);

                    if ($attachment_id && $task_id === false) {
                        $this->leads_model->log_lead_activity($leadid, 'not_lead_imported_attachment', true);
                    }
                }
                fclose($fp);
            }
        }
    }

    public function __destruct()
    {
        if ($this->lock_handle) {
            flock($this->lock_handle, LOCK_UN);
            $this->lock_handle = null;
        }
    }

    private function can_cron_run()
    {
        return ($this->lock_handle && flock($this->lock_handle, LOCK_EX | LOCK_NB))
        || (defined('APP_DISABLE_CRON_LOCK') && APP_DISABLE_CRON_LOCK);
    }

    private function prepare_imap_email_body_html($body)
    {
        // Trim message
        $body = trim($body);
        $body = str_replace("&nbsp;", " ", $body);
        // Remove html tags - strips inline styles also
        $body = trim(strip_html_tags($body, "<br/>, <br>, <a>"));
        // Remove duplicate new lines
        $body = preg_replace("/[\r\n]+/", "\n", $body);
        // new lines with <br />
        $body = preg_replace('/\n(\s*\n)+/', '<br />', $body);
        $body = preg_replace('/\n/', '<br>', $body);

        return $body;
    }
}
