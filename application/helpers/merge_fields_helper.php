<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * General merge fields not linked to any features
 * @return array
 */
function get_other_merge_fields()
{
    $CI =& get_instance();
    $fields                          = array();
    $fields['{logo_url}']            = base_url('uploads/company/' . get_option('company_logo'));

    $logo_width = do_action('merge_field_logo_img_width', '');
    $fields['{logo_image_with_url}'] = '<a href="' . site_url() . '" target="_blank"><img src="' . base_url('uploads/company/' . get_option('company_logo')) . '"'.($logo_width != '' ? ' width="'.$logo_width.'"' : '').'></a>';

    $fields['{crm_url}']             = site_url();
    $fields['{admin_url}']           = admin_url();
    $fields['{main_domain}']         = get_option('main_domain');
    $fields['{companyname}']         = get_option('companyname');

    if (!is_staff_logged_in() || is_client_logged_in()) {
        $fields['{email_signature}'] = get_option('email_signature');
    } else {
        $CI->db->select('email_signature')->from('tblstaff')->where('staffid', get_staff_user_id());
        $signature = $CI->db->get()->row()->email_signature;
        if (empty($signature)) {
            $fields['{email_signature}'] = get_option('email_signature');
        } else {
            $fields['{email_signature}'] = $signature;
        }
    }

    $hook_data['merge_fields'] = $fields;
    $hook_data['fields_to']    = 'other';
    $hook_data['id']           = '';

    $hook_data = do_action('other_merge_fields', $hook_data);
    $fields    = $hook_data['merge_fields'];

    return $fields;
}
/**
 * Lead merge fields
 * @param  mixed $id lead id
 * @return array
 */
function get_lead_merge_fields($id)
{
    $CI =& get_instance();
    $fields                       = array();
    $fields['{lead_name}']        = '';
    $fields['{lead_email}']       = '';
    $fields['{lead_position}']    = '';
    $fields['{lead_company}']     = '';
    $fields['{lead_country}']     = '';
    $fields['{lead_zip}']         = '';
    $fields['{lead_city}']        = '';
    $fields['{lead_state}']       = '';
    $fields['{lead_address}']     = '';
    $fields['{lead_assigned}']    = '';
    $fields['{lead_status}']      = '';
    $fields['{lead_source}']      = '';
    $fields['{lead_phonenumber}'] = '';
    $fields['{lead_link}']        = '';
    $fields['{lead_website}']     = '';
    $fields['{lead_description}'] = '';

    $CI->db->where('id', $id);
    $lead = $CI->db->get('tblleads')->row();

    if (!$lead) {
        return $fields;
    }

    $fields['{lead_link}']        = admin_url('leads/index/' . $lead->id);
    $fields['{lead_name}']        = $lead->name;
    $fields['{lead_email}']       = $lead->email;
    $fields['{lead_position}']    = $lead->title;
    $fields['{lead_phonenumber}'] = $lead->phonenumber;
    $fields['{lead_company}']     = $lead->company;
    $fields['{lead_zip}']         = $lead->zip;
    $fields['{lead_city}']        = $lead->city;
    $fields['{lead_state}']       = $lead->state;
    $fields['{lead_address}']     = $lead->address;
    $fields['{lead_website}']     = $lead->website;
    $fields['{lead_description}'] = $lead->description;

    if ($lead->assigned != 0) {
        $fields['{lead_assigned}'] = get_staff_full_name($lead->assigned);
    }
    if ($lead->country != 0) {
        $country                  = get_country($lead->country);
        $fields['{lead_country}'] = $country->short_name;
    }

    if ($lead->junk == 1) {
        $fields['{lead_status}'] = _l('lead_junk');
    } elseif ($lead->lost == 1) {
        $fields['{lead_status}'] = _l('lead_lost');
    } else {
        $CI->db->select('name');
        $CI->db->from('tblleadsstatus');
        $CI->db->where('id', $lead->status);
        $status = $CI->db->get()->row();
        if ($status) {
            $fields['{lead_status}'] = $status->name;
        }
    }

    $custom_fields = get_custom_fields('leads');
    foreach ($custom_fields as $field) {
        $fields['{' . $field['slug'] . '}'] = get_custom_field_value($id, $field['id'], 'leads');
    }

    $hook_data['merge_fields'] = $fields;
    $hook_data['fields_to']    = 'lead';
    $hook_data['id']           = $id;

    $hook_data = do_action('lead_merge_fields', $hook_data);
    $fields    = $hook_data['merge_fields'];

    return $fields;
}
/**
 * Project merge fields
 * @param  mixed $project_id      project id
 * @param  array  $additional_data option to pass additional data for the templates eq is staff template or customer template
 * This field is also used for the project discussion files and regular discussions
 * @return array
 */
function get_project_merge_fields($project_id, $additional_data = array())
{
    $fields = array();

    $fields['{project_name}']           = '';
    $fields['{project_deadline}']       = '';
    $fields['{project_start_date}']     = '';
    $fields['{project_description}']    = '';
    $fields['{project_link}']           = '';
    $fields['{discussion_link}']        = '';
    $fields['{discussion_creator}']     = '';
    $fields['{comment_creator}']        = '';
    $fields['{file_creator}']           = '';
    $fields['{discussion_subject}']     = '';
    $fields['{discussion_description}'] = '';
    $fields['{discussion_comment}']     = '';

    $CI =& get_instance();

    $CI->db->where('id', $project_id);
    $project = $CI->db->get('tblprojects')->row();

    $fields['{project_name}']        = $project->name;
    $fields['{project_deadline}']    = _d($project->deadline);
    $fields['{project_start_date}']  = _d($project->start_date);
    $fields['{project_description}'] = $project->description;

    $custom_fields = get_custom_fields('projects');
    foreach ($custom_fields as $field) {
        $fields['{' . $field['slug'] . '}'] = get_custom_field_value($project_id, $field['id'], 'projects');
    }

    if (is_client_logged_in()) {
        $cf                             = get_contact_full_name(get_contact_user_id());
    } else {
        $cf                             = get_staff_full_name(get_staff_user_id());
    }

    $fields['{file_creator}']       = $cf;
    $fields['{discussion_creator}'] = $cf;
    $fields['{comment_creator}']    = $cf;

    if (isset($additional_data['discussion_id'])) {
        $CI->db->where('id', $additional_data['discussion_id']);

        if (isset($additional_data['discussion_type']) && $additional_data['discussion_type'] == 'regular') {
            $table = 'tblprojectdiscussions';
        } else {
            // is file
            $table = 'tblprojectfiles';
        }

        $discussion = $CI->db->get($table)->row();

        $fields['{discussion_subject}']     = $discussion->subject;
        $fields['{discussion_description}'] = $discussion->description;

        if (isset($additional_data['discussion_comment_id'])) {
            $CI->db->where('id', $additional_data['discussion_comment_id']);
            $discussion_comment             = $CI->db->get('tblprojectdiscussioncomments')->row();
            $fields['{discussion_comment}'] = $discussion_comment->content;
        }
    }
    if (isset($additional_data['customer_template'])) {
        $fields['{project_link}'] = site_url('clients/project/' . $project_id);

        if (isset($additional_data['discussion_id']) && isset($additional_data['discussion_type']) && $additional_data['discussion_type'] == 'regular') {
            $fields['{discussion_link}'] = site_url('clients/project/' . $project_id . '?group=project_discussions&discussion_id=' . $additional_data['discussion_id']);
        } elseif (isset($additional_data['discussion_id']) && isset($additional_data['discussion_type']) && $additional_data['discussion_type'] == 'file') {
            // is file
            $fields['{discussion_link}'] = site_url('clients/project/' . $project_id . '?group=project_files&file_id=' . $additional_data['discussion_id']);
        }
    } else {
        $fields['{project_link}'] = admin_url('projects/view/' . $project_id);
        if (isset($additional_data['discussion_type']) && $additional_data['discussion_type'] == 'regular' && isset($additional_data['discussion_id'])) {
            $fields['{discussion_link}'] = admin_url('projects/view/' . $project_id . '?group=project_discussions&discussion_id=' . $additional_data['discussion_id']);
        } else {
            if (isset($additional_data['discussion_id'])) {
                // is file
                $fields['{discussion_link}'] = admin_url('projects/view/' . $project_id . '?group=project_files&file_id=' . $additional_data['discussion_id']);
            }
        }
    }

    $custom_fields = get_custom_fields('projects');
    foreach ($custom_fields as $field) {
        $fields['{' . $field['slug'] . '}'] = get_custom_field_value($project_id, $field['id'], 'projects');
    }

    $hook_data['merge_fields']    = $fields;
    $hook_data['fields_to']       = 'project';
    $hook_data['id']              = $project_id;
    $hook_data['additional_data'] = $additional_data;

    $hook_data = do_action('project_merge_fields', $hook_data);
    $fields    = $hook_data['merge_fields'];

    return $fields;
}

/**
 * Password merge fields
 * @param  array $data
 * @param  boolean $staff is field for staff or contact
 * @param  string $type  template type
 * @return array
 */
function get_password_merge_field($data, $staff, $type)
{
    $fields['{reset_password_url}'] = '';
    $fields['{set_password_url}']   = '';

    if ($staff == true) {
        if ($type == 'forgot') {
            $fields['{reset_password_url}'] = site_url('authentication/reset_password/' . floatval($staff) . '/' . $data['userid'] . '/' . $data['new_pass_key']);
        }
    } else {
        if ($type == 'forgot') {
            $fields['{reset_password_url}'] = site_url('clients/reset_password/' . floatval($staff) . '/' . $data['userid'] . '/' . $data['new_pass_key']);
        } elseif ($type == 'set') {
            $fields['{set_password_url}'] = site_url('authentication/set_password/' . $staff . '/' . $data['userid'] . '/' . $data['new_pass_key']);
        }
    }

    return $fields;
}
/**
 * Merge fields for Contacts and Customers
 * @param  mixed $client_id
 * @param  string $contact_id
 * @param  string $password   password is used when sending welcome email, only 1 time
 * @return array
 */
function get_client_contact_merge_fields($client_id, $contact_id = '', $password = '')
{
    $fields = array();

    if ($contact_id == '') {
        $contact_id = get_primary_contact_user_id($client_id);
    }

    $fields['{contact_firstname}']  = '';
    $fields['{contact_lastname}']   = '';
    $fields['{contact_email}']      = '';
    $fields['{client_company}']     = '';
    $fields['{client_phonenumber}'] = '';
    $fields['{client_country}']     = '';
    $fields['{client_city}']        = '';
    $fields['{client_zip}']         = '';
    $fields['{client_state}']       = '';
    $fields['{client_address}']     = '';
    $fields['{password}']           = '';
    $fields['{client_vat_number}']  = '';

    $CI =& get_instance();

    if($client_id == ''){
        return $fields;
    }

    $client = $CI->clients_model->get($client_id);

    if (!$client) {
        return $fields;
    }

    $CI->db->where('userid', $client_id);
    $CI->db->where('id', $contact_id);
    $contact = $CI->db->get('tblcontacts')->row();

    if ($contact) {
        $fields['{contact_firstname}'] = $contact->firstname;
        $fields['{contact_lastname}']  = $contact->lastname;
        $fields['{contact_email}']     = $contact->email;
    }
    if (!empty($client->vat)) {
        $fields['{client_vat_number}'] = $client->vat;
    }

    $fields['{client_company}']     = $client->company;
    $fields['{client_phonenumber}'] = $client->phonenumber;
    $fields['{client_country}']     = get_country_short_name($client->country);
    $fields['{client_city}']        = $client->city;
    $fields['{client_zip}']         = $client->zip;
    $fields['{client_state}']       = $client->state;
    $fields['{client_address}']     = $client->address;
    $fields['{client_id}']     = $client_id;

    if ($password != '') {
        $fields['{password}'] = $password;
    }

    $custom_fields = get_custom_fields('customers');
    foreach ($custom_fields as $field) {
        $fields['{' . $field['slug'] . '}'] = get_custom_field_value($client_id, $field['id'], 'customers');
    }

    $custom_fields = get_custom_fields('contacts');
    foreach ($custom_fields as $field) {
        $fields['{' . $field['slug'] . '}'] = get_custom_field_value($contact_id, $field['id'], 'contacts');
    }

    $hook_data['merge_fields'] = $fields;
    $hook_data['fields_to']    = 'client_contact';
    $hook_data['id']           = $client_id;
    $hook_data['contact_id']   = $contact_id;

    $hook_data = do_action('client_contact_merge_fields', $hook_data);
    $fields    = $hook_data['merge_fields'];

    return $fields;
}

/**
 * Statement merge fields
 * @param  array $statement
 * @return array
 */
function get_statement_merge_fields($statement)
{
    $fields = array();

    $fields['{statement_from}'] = _d($statement['from']);
    $fields['{statement_to}'] = _d($statement['to']);
    $fields['{statement_balance_due}'] = format_money($statement['balance_due'], $statement['currency']->symbol);
    $fields['{statement_amount_paid}'] = format_money($statement['amount_paid'], $statement['currency']->symbol);
    $fields['{statement_invoiced_amount}'] = format_money($statement['invoiced_amount'], $statement['currency']->symbol);
    $fields['{statement_beginning_balance}'] = format_money($statement['beginning_balance'], $statement['currency']->symbol);

    $hook_data['fields_to'] = 'statement';
    $hook_data['merge_fields'] = $fields;
    $hook_data['statement'] = $statement;

    $hook_data = do_action('client_statement_merge_fields', $hook_data);
    $fields    = $hook_data['merge_fields'];

    return $fields;
}

/**
 * Merge fields for estimates
 * @param  mixed $estimate_id estimate id
 * @return array
 */
function get_estimate_merge_fields($estimate_id)
{
    $fields = array();
    $CI =& get_instance();
    $CI->db->where('id', $estimate_id);
    $estimate = $CI->db->get('tblestimates')->row();
    if (!$estimate) {
        return $fields;
    }

    $CI->db->where('id', $estimate->currency);
    $symbol = $CI->db->get('tblcurrencies')->row()->symbol;

    $fields['{estimate_sale_agent}']    = get_staff_full_name($estimate->sale_agent);
    $fields['{estimate_total}']        = format_money($estimate->total, $symbol);
    $fields['{estimate_subtotal}']     = format_money($estimate->subtotal, $symbol);
    $fields['{estimate_link}']         = site_url('viewestimate/' . $estimate_id . '/' . $estimate->hash);
    $fields['{estimate_number}']       = format_estimate_number($estimate_id);
    $fields['{estimate_reference_no}'] = $estimate->reference_no;
    $fields['{estimate_expirydate}']   = _d($estimate->expirydate);
    $fields['{estimate_date}']         = _d($estimate->date);
    $fields['{estimate_status}']       = format_estimate_status($estimate->status, '', false);

    $custom_fields = get_custom_fields('estimate');
    foreach ($custom_fields as $field) {
        $fields['{' . $field['slug'] . '}'] = get_custom_field_value($estimate_id, $field['id'], 'estimate');
    }

    $hook_data['merge_fields'] = $fields;
    $hook_data['fields_to']    = 'estimate';
    $hook_data['id']           = $estimate_id;

    $hook_data = do_action('estimate_merge_fields', $hook_data);
    $fields    = $hook_data['merge_fields'];

    return $fields;
}

/**
 * Credit notes merge fields
 * @param  mixed $id credit note id
 * @return array
 */
function get_credit_note_merge_fields($id)
{
    $fields = array();
    $CI =& get_instance();

    if (!class_exists('credit_notes_model')) {
        $CI->load->model('credit_notes_model');
    }

    $credit_note = $CI->credit_notes_model->get($id);

    if (!$credit_note) {
        return $fields;
    }

    $fields['{credit_note_number}']    = format_credit_note_number($id);
    $fields['{credit_note_total}']    = format_money($credit_note->total, $credit_note->symbol);
    $fields['{credit_note_subtotal}'] = format_money($credit_note->subtotal, $credit_note->symbol);
    $fields['{credit_note_credits_remaining}'] = format_money($credit_note->remaining_credits, $credit_note->symbol);
    $fields['{credit_note_credits_used}'] = format_money($credit_note->credits_used, $credit_note->symbol);
    $fields['{credit_note_date}']    = _d($credit_note->date);
    $fields['{credit_note_status}']  = format_credit_note_status($credit_note->status, true);

    $custom_fields = get_custom_fields('credit_note');

    foreach ($custom_fields as $field) {
        $fields['{' . $field['slug'] . '}'] = get_custom_field_value($id, $field['id'], 'credit_note');
    }

    $hook_data['merge_fields'] = $fields;
    $hook_data['fields_to']    = 'credit_note';
    $hook_data['id']           = $id;

    $hook_data = do_action('credit_note_merge_fields', $hook_data);
    $fields    = $hook_data['merge_fields'];

    return $fields;
}

/**
 * Merge fields for invoices
 * @param  mixed $invoice_id invoice id
 * @param  mixed $payment_id invoice id
 * @return array
 */
function get_invoice_merge_fields($invoice_id, $payment_id = false)
{
    $fields = array();
    $CI =& get_instance();
    $CI->db->where('id', $invoice_id);
    $invoice = $CI->db->get('tblinvoices')->row();

    if (!$invoice) {
        return $fields;
    }

    $CI->db->where('id', $invoice->currency);
    $symbol = $CI->db->get('tblcurrencies')->row()->symbol;

    $fields['{payment_total}'] = '';
    $fields['{payment_date}']  = '';

    if ($payment_id) {
        $CI->db->where('id', $payment_id);
        $payment = $CI->db->get('tblinvoicepaymentrecords')->row();

        $fields['{payment_total}'] = format_money($payment->amount, $symbol);
        $fields['{payment_date}']  = _d($payment->date);
    }

    $fields['{invoice_sale_agent}']    = get_staff_full_name($invoice->sale_agent);
    $fields['{invoice_total}']    = format_money($invoice->total, $symbol);
    $fields['{invoice_subtotal}'] = format_money($invoice->subtotal, $symbol);

    $fields['{invoice_link}']    = site_url('viewinvoice/' . $invoice_id . '/' . $invoice->hash);
    $fields['{invoice_number}']  = format_invoice_number($invoice_id);
    $fields['{invoice_duedate}'] = _d($invoice->duedate);
    $fields['{invoice_date}']    = _d($invoice->date);
    $fields['{invoice_status}']  = format_invoice_status($invoice->status, '', false);

    $custom_fields = get_custom_fields('invoice');
    foreach ($custom_fields as $field) {
        $fields['{' . $field['slug'] . '}'] = get_custom_field_value($invoice_id, $field['id'], 'invoice');
    }

    $hook_data['merge_fields'] = $fields;
    $hook_data['fields_to']    = 'invoice';
    $hook_data['id']           = $invoice_id;

    $hook_data = do_action('invoice_merge_fields', $hook_data);
    $fields    = $hook_data['merge_fields'];

    return $fields;
}

/**
 * Merge fields for proposals
 * @param  mixed $proposal_id proposal id
 * @return array
 */
function get_proposal_merge_fields($proposal_id)
{
    $fields = array();
    $CI =& get_instance();
    $CI->db->where('id', $proposal_id);
    $CI->db->join('tblcountries', 'tblcountries.country_id=tblproposals.country', 'left');
    $proposal = $CI->db->get('tblproposals')->row();


    if (!$proposal) {
        return $fields;
    }

    $CI->load->model('currencies_model');
    if ($proposal->currency != 0) {
        $currency = $CI->currencies_model->get($proposal->currency);
    } else {
        $currency = $CI->currencies_model->get_base_currency();
    }

    $fields['{proposal_id}']          = $proposal_id;
    $fields['{proposal_number}']      = format_proposal_number($proposal_id);
    $fields['{proposal_link}']        = site_url('viewproposal/' . $proposal_id . '/' . $proposal->hash);
    $fields['{proposal_subject}']     = $proposal->subject;
    $fields['{proposal_total}']       = format_money($proposal->total, $currency->symbol);
    $fields['{proposal_subtotal}']    = format_money($proposal->subtotal, $currency->symbol);
    $fields['{proposal_open_till}']   = _d($proposal->open_till);
    $fields['{proposal_proposal_to}'] = $proposal->proposal_to;
    $fields['{proposal_address}']     = $proposal->address;
    $fields['{proposal_email}']       = $proposal->email;
    $fields['{proposal_phone}']       = $proposal->phone;

    $fields['{proposal_city}']    = $proposal->city;
    $fields['{proposal_state}']   = $proposal->state;
    $fields['{proposal_zip}']     = $proposal->zip;
    $fields['{proposal_country}'] = $proposal->short_name;
    $fields['{proposal_assigned}'] = get_staff_full_name($proposal->assigned);

    $custom_fields = get_custom_fields('proposal');
    foreach ($custom_fields as $field) {
        $fields['{' . $field['slug'] . '}'] = get_custom_field_value($proposal_id, $field['id'], 'proposal');
    }

    $hook_data['merge_fields'] = $fields;
    $hook_data['fields_to']    = 'proposal';
    $hook_data['id']           = $proposal_id;

    $hook_data = do_action('proposal_merge_fields', $hook_data);
    $fields    = $hook_data['merge_fields'];

    return $fields;
}

/**
 * Merge field for contacts
 * @param  mixed $contract_id contract id
 * @return array
 */
function get_contract_merge_fields($contract_id)
{
    $fields = array();
    $CI =& get_instance();
    $CI->db->where('id', $contract_id);
    $contract = $CI->db->get('tblcontracts')->row();

    if (!$contract) {
        return $fields;
    }

    $CI->load->model('currencies_model');
    $currency = $CI->currencies_model->get_base_currency();

    $fields['{contract_id}']             = $contract->id;
    $fields['{contract_subject}']        = $contract->subject;
    $fields['{contract_description}']    = $contract->description;
    $fields['{contract_datestart}']      = _d($contract->datestart);
    $fields['{contract_dateend}']        = _d($contract->dateend);
    $fields['{contract_contract_value}'] = format_money($contract->contract_value, $currency->symbol);

    $custom_fields = get_custom_fields('contracts');
    foreach ($custom_fields as $field) {
        $fields['{' . $field['slug'] . '}'] = get_custom_field_value($contract_id, $field['id'], 'contracts');
    }

    $hook_data['merge_fields'] = $fields;
    $hook_data['fields_to']    = 'contract';
    $hook_data['id']           = $contract_id;

    $hook_data = do_action('contract_merge_fields', $hook_data);
    $fields    = $hook_data['merge_fields'];

    return $fields;
}

/**
 * Merge fields for tasks
 * @param  mixed  $task_id         task id
 * @param  boolean $client_template is client template or staff template
 * @return array
 */
function get_task_merge_fields($task_id, $client_template = false)
{
    $fields = array();

    $CI =& get_instance();
    $CI->db->where('id', $task_id);
    $task = $CI->db->get('tblstafftasks')->row();

    if (!$task) {
        return $fields;
    }

    // Client templateonly passed when sending to tasks related to project and sending email template to contacts
    // Passed from tasks_model  _send_task_responsible_users_notification function
    if ($client_template == false) {
        $fields['{task_link}'] = admin_url('tasks/view/' . $task_id);
    } else {
        $fields['{task_link}'] = site_url('clients/project/' . $task->rel_id . '?group=project_tasks&taskid=' . $task_id);
    }

    if (is_client_logged_in()) {
        $fields['{task_user_take_action}'] = get_contact_full_name(get_contact_user_id());
    } else {
        $fields['{task_user_take_action}'] = get_staff_full_name(get_staff_user_id());
    }

    $fields['{task_comment}'] = '';
    $fields['{task_related}'] = '';
    $fields['{project_name}'] = '';

    if ($task->rel_type == 'project') {
        $CI->db->select('name');
        $CI->db->from('tblprojects');
        $CI->db->where('id', $task->rel_id);
        $project = $CI->db->get()->row();
        if ($project) {
            $fields['{project_name}'] = $project->name;
        }
    }

    if (!empty($task->rel_id)) {
        $rel_data   = get_relation_data($task->rel_type, $task->rel_id);
        $rel_values = get_relation_values($rel_data, $task->rel_type);
        $fields['{task_related}'] = $rel_values['name'];
    }

    $fields['{task_name}']        = $task->name;
    $fields['{task_description}'] = $task->description;
    $fields['{task_status}']      = format_task_status($task->status, false, true);
    $fields['{task_priority}']    = task_priority($task->priority);
    $fields['{task_startdate}']   = _d($task->startdate);
    $fields['{task_duedate}']     = _d($task->duedate);
    $fields['{comment_link}']     = '';

    $CI->db->where('taskid', $task_id);
    $CI->db->limit(1);
    $CI->db->order_by('dateadded', 'desc');
    $comment = $CI->db->get('tblstafftaskcomments')->row();

    if ($comment) {
        $fields['{task_comment}'] = $comment->content;
        $fields['{comment_link}'] = $fields['{task_link}'].'#comment_'.$comment->id;
    }

    $custom_fields = get_custom_fields('tasks');
    foreach ($custom_fields as $field) {
        $fields['{' . $field['slug'] . '}'] = get_custom_field_value($task_id, $field['id'], 'tasks');
    }

    $hook_data['merge_fields']    = $fields;
    $hook_data['fields_to']       = 'task';
    $hook_data['id']              = $task_id;
    $hook_data['client_template'] = $client_template;

    $hook_data = do_action('task_merge_fields', $hook_data);
    $fields    = $hook_data['merge_fields'];

    return $fields;
}

/**
 * Merge field for staff members
 * @param  mixed $staff_id staff id
 * @param  string $password password is used only when sending welcome email, 1 time
 * @return array
 */
function get_staff_merge_fields($staff_id, $password = '')
{
    $fields = array();

    $CI =& get_instance();
    $CI->db->where('staffid', $staff_id);
    $staff = $CI->db->get('tblstaff')->row();

    $fields['{password}']          = '';
    $fields['{staff_firstname}']   = '';
    $fields['{staff_lastname}']    = '';
    $fields['{staff_email}']       = '';
    $fields['{staff_datecreated}'] = '';

    if (!$staff) {
        return $fields;
    }

    if ($password != '') {
        $fields['{password}'] = $password;
    }

    if ($staff->two_factor_auth_code) {
        $fields['{two_factor_auth_code}'] = $staff->two_factor_auth_code;
    }

    $fields['{staff_firstname}']   = $staff->firstname;
    $fields['{staff_lastname}']    = $staff->lastname;
    $fields['{staff_email}']       = $staff->email;
    $fields['{staff_datecreated}'] = $staff->datecreated;


    $custom_fields = get_custom_fields('staff');
    foreach ($custom_fields as $field) {
        $fields['{' . $field['slug'] . '}'] = get_custom_field_value($staff_id, $field['id'], 'staff');
    }

    $hook_data['merge_fields'] = $fields;
    $hook_data['fields_to']    = 'staff';
    $hook_data['id']           = $staff_id;

    $hook_data = do_action('staff_merge_fields', $hook_data);
    $fields    = $hook_data['merge_fields'];

    return $fields;
}

/**
 * Merge fields for tickets
 * @param  string $template  template name, used to identify url
 * @param  mixed $ticket_id ticket id
 * @param  mixed $reply_id  reply id
 * @return array
 */
function get_ticket_merge_fields($template, $ticket_id, $reply_id = '')
{
    $fields = array();

    $CI =& get_instance();
    $CI->db->where('ticketid', $ticket_id);
    $ticket = $CI->db->get('tbltickets')->row();

    if (!$ticket) {
        return $fields;
    }

    // Replace contact firstname with the ticket name in case the ticket is not linked to any contact.
    // eq email or form imported.
    if ($ticket->name != null && $ticket->name != "") {
        $fields['{contact_firstname}'] = $ticket->name;
    }

    $fields['{ticket_priority}'] = '';
    $fields['{ticket_service}']  = '';


    $CI->db->where('departmentid', $ticket->department);
    $department = $CI->db->get('tbldepartments')->row();
    if ($department) {
        $fields['{ticket_department}'] = $department->name;
    }

    $fields['{ticket_status}'] = ticket_status_translate($ticket->status);
    $CI->db->where('serviceid', $ticket->service);
    $service = $CI->db->get('tblservices')->row();
    if ($service) {
        $fields['{ticket_service}'] = $service->name;
    }

    $fields['{ticket_id}']       = $ticket_id;
    $fields['{ticket_priority}'] = ticket_priority_translate($ticket->priority);

    $customerTemplates = array(
        'new-ticket-opened-admin',
        'ticket-reply',
        'ticket-autoresponse',
        'auto-close-ticket',
        );

    if (in_array($template, $customerTemplates)) {
        $fields['{ticket_url}'] = site_url('clients/ticket/' . $ticket_id);
    } else {
        $fields['{ticket_url}'] = admin_url('tickets/ticket/' . $ticket_id);
    }

    if ($template == 'ticket-reply-to-admin' || $template == 'ticket-reply') {
        $CI->db->where('ticketid', $ticket_id);
        $CI->db->limit(1);
        $CI->db->order_by('date', 'desc');
        $reply                      = $CI->db->get('tblticketreplies')->row();
        $fields['{ticket_message}'] = $reply->message;
    } else {
        $fields['{ticket_message}'] = $ticket->message;
    }

    $fields['{ticket_date}']    = _dt($ticket->date);
    $fields['{ticket_subject}'] = $ticket->subject;

    $custom_fields = get_custom_fields('tickets');
    foreach ($custom_fields as $field) {
        $fields['{' . $field['slug'] . '}'] = get_custom_field_value($ticket_id, $field['id'], 'tickets');
    }

    $hook_data['merge_fields'] = $fields;
    $hook_data['fields_to']    = 'ticket';
    $hook_data['id']           = $ticket_id;
    $hook_data['reply_id']     = $reply_id;
    $hook_data['template']     = $template;

    $hook_data = do_action('ticket_merge_fields', $hook_data);
    $fields    = $hook_data['merge_fields'];

    return $fields;
}

/**
 * Merge fields reminders helper function
 * @param  string $rel_type reminder relation type
 * @param  mixed $rel_id   rel id of relation eq id of invoice
 * @param  string $text     reminder description
 * @return string
 */
function get_staff_reminder_merge_fields($reminder)
{
    $reminder = (object) $reminder;

    $rel_data   = get_relation_data($reminder->rel_type, $reminder->rel_id);
    $rel_values = get_relation_values($rel_data, $reminder->rel_type);

    $fields['{staff_reminder_description}'] = $reminder->description;
    $fields['{staff_reminder_date}'] = _dt($reminder->date);
    $fields['{staff_reminder_relation_name}'] = $rel_values['name'];
    $fields['{staff_reminder_relation_link}'] = $rel_values['link'];

    $hook_data['merge_fields'] = $fields;
    $hook_data['fields_to']    = 'reminder';
    $hook_data['reminder']           = $reminder;

    $hook_data = do_action('staff_reminder_merge_fields', $hook_data);
    $fields    = $hook_data['merge_fields'];

    return $fields;
}
/**
 * @return array
 * All available merge fields for templates are defined here
 */
function get_available_merge_fields()
{
    $available_merge_fields = array(
        array(
            'staff' => array(
                array(
                    'name' => 'Staff Firstname',
                    'key' => '{staff_firstname}',
                    'available' => array(
                        'staff',
                        'tasks',
                        'project',
                    ),
                ),
                array(
                    'name' => 'Staff Lastname',
                    'key' => '{staff_lastname}',
                    'available' => array(
                        'staff',
                        'tasks',
                        'project',
                    ),
                ),
                array(
                    'name' => 'Staff Email',
                    'key' => '{staff_email}',
                    'available' => array(
                        'staff',
                        'project',
                    ),
                ),
                array(
                    'name' => 'Staff Date Created',
                    'key' => '{staff_datecreated}',
                    'available' => array(
                        'staff',
                    ),
                ),
                array(
                    'name' => 'Reset Password Url',
                    'key' => '{reset_password_url}',
                    'available' => array(
                        'staff',
                    ),
                ),
                array(
                    'name' => 'Reminder Text',
                    'key' => '{staff_reminder_description}',
                    'available' => array(
                        'staff',
                    ),
                ),
                array(
                    'name' => 'Reminder Date',
                    'key' => '{staff_reminder_date}',
                    'available' => array(
                        'staff',
                    ),
                ),
                 array(
                    'name' => 'Reminder Relation Name',
                    'key' => '{staff_reminder_relation_name}',
                    'available' => array(
                        'staff',
                    ),
                ),
                array(
                    'name' => 'Reminder Relation Link',
                    'key' => '{staff_reminder_relation_link}',
                    'available' => array(
                        'staff',
                    ),
                ),
            ),
        ),
        array(
            'clients' => array(
                array(
                    'name' => 'Contact Firstname',
                    'key' => '{contact_firstname}',
                    'available' => array(
                        'client',
                        'ticket',
                        'invoice',
                        'estimate',
                        'contract',
                        'project',
                        'tasks',
                        'credit_note',
                    ),
                ),
                array(
                    'name' => 'Contact Lastname',
                    'key' => '{contact_lastname}',
                    'available' => array(
                        'client',
                        'ticket',
                        'invoice',
                        'estimate',
                        'contract',
                        'project',
                        'tasks',
                        'credit_note',
                    ),
                ),
                array(
                    'name' => 'Set New Password Url',
                    'key' => '{set_password_url}',
                    'available' => array(
                        'client',
                    ),
                ),
                array(
                    'name' => 'Reset Password Url',
                    'key' => '{reset_password_url}',
                    'available' => array(
                        'client',
                    ),
                ),
                array(
                    'name' => 'Contact Email',
                    'key' => '{contact_email}',
                    'available' => array(
                        'client',
                        'invoice',
                        'estimate',
                        'ticket',
                        'contract',
                        'project',
                        'credit_note',
                    ),
                ),
                array(
                    'name' => 'Client Company',
                    'key' => '{client_company}',
                    'available' => array(
                        'client',
                        'invoice',
                        'estimate',
                        'ticket',
                        'contract',
                        'project',
                        'credit_note',
                    ),
                ),
                array(
                    'name' => 'Client Phone Number',
                    'key' => '{client_phonenumber}',
                    'available' => array(
                        'client',
                        'invoice',
                        'estimate',
                        'ticket',
                        'contract',
                        'project',
                        'credit_note',
                    ),
                ),
                array(
                    'name' => 'Client Country',
                    'key' => '{client_country}',
                    'available' => array(
                        'client',
                        'invoice',
                        'estimate',
                        'ticket',
                        'contract',
                        'project',
                        'credit_note',
                    ),
                ),
                array(
                    'name' => 'Client City',
                    'key' => '{client_city}',
                    'available' => array(
                        'client',
                        'invoice',
                        'estimate',
                        'ticket',
                        'contract',
                        'project',
                        'credit_note',
                    ),
                ),
                array(
                    'name' => 'Client Zip',
                    'key' => '{client_zip}',
                    'available' => array(
                        'client',
                        'invoice',
                        'estimate',
                        'ticket',
                        'contract',
                        'project',
                        'credit_note',
                    ),
                ),
                array(
                    'name' => 'Client State',
                    'key' => '{client_state}',
                    'available' => array(
                        'client',
                        'invoice',
                        'estimate',
                        'ticket',
                        'contract',
                        'project',
                        'credit_note',
                    ),
                ),
                array(
                    'name' => 'Client Address',
                    'key' => '{client_address}',
                    'available' => array(
                        'client',
                        'invoice',
                        'estimate',
                        'ticket',
                        'contract',
                        'project',
                        'credit_note',
                    ),
                ),
                array(
                    'name' => 'Client Vat Number',
                    'key' => '{client_vat_number}',
                    'available' => array(
                        'client',
                        'invoice',
                        'estimate',
                        'ticket',
                        'contract',
                        'project',
                        'credit_note',
                    ),
                ),
                array(
                    'name' => 'Client ID',
                    'key' => '{client_id}',
                    'available' => array(
                        'client',
                        'invoice',
                        'estimate',
                        'ticket',
                        'contract',
                        'project',
                        'credit_note',
                    ),
                ),
                 array(
                    'name' => 'Statement From',
                    'key' => '{statement_from}',
                    'available' => array(
                        'client',
                    ),
                ),
                 array(
                    'name' => 'Statement To',
                    'key' => '{statement_to}',
                    'available' => array(
                        'client',
                    ),
                ),
                array(
                    'name' => 'Statement Balance Due',
                    'key' => '{statement_balance_due}',
                    'available' => array(
                        'client',
                    ),
                ),
                array(
                    'name' => 'Statement Amount Paid',
                    'key' => '{statement_amount_paid}',
                    'available' => array(
                        'client',
                    ),
                ),
                array(
                    'name' => 'Statement Invoiced Amount',
                    'key' => '{statement_invoiced_amount}',
                    'available' => array(
                        'client',
                    ),
                ),
                array(
                    'name' => 'Statement Beginning Balance',
                    'key' => '{statement_beginning_balance}',
                    'available' => array(
                        'client',
                    ),
                ),
            ),
        ),
array(
    'credit_note'=>array(
         array(
                    'name' => 'Credit Note Number',
                    'key' => '{credit_note_number}',
                    'available' => array(
                        'credit_note',
                    ),
                ),
                array(
                    'name' => 'Date',
                    'key' => '{credit_note_date}',
                    'available' => array(
                        'credit_note',
                    ),
                ),
                array(
                    'name' => 'Status',
                    'key' => '{credit_note_status}',
                    'available' => array(
                        'credit_note',
                    ),
                ),
                array(
                    'name' => 'Total',
                    'key' => '{credit_note_total}',
                    'available' => array(
                        'credit_note',
                    ),
                ),
                  array(
                    'name' => 'Subtotal',
                    'key' => '{credit_note_subtotal}',
                    'available' => array(
                        'credit_note',
                    ),
                ),
                    array(
                    'name' => 'Credits Used',
                    'key' => '{credit_note_credits_used}',
                    'available' => array(
                        'credit_note',
                    ),
                ),
                  array(
                    'name' => 'Credits Remaining',
                    'key' => '{credit_note_credits_remaining}',
                    'available' => array(
                        'credit_note',
                    ),
                ),
    ),
),
        array(
            'ticket' => array(
                array(
                    'name' => 'Ticket ID',
                    'key' => '{ticket_id}',
                    'available' => array(
                        'ticket',
                    ),
                ),
                array(
                    'name' => 'Ticket URL',
                    'key' => '{ticket_url}',
                    'available' => array(
                        'ticket',
                    ),
                ),
                array(
                    'name' => 'Department',
                    'key' => '{ticket_department}',
                    'available' => array(
                        'ticket',
                    ),
                ),
                array(
                    'name' => 'Date Opened',
                    'key' => '{ticket_date}',
                    'available' => array(
                        'ticket',
                    ),
                ),
                array(
                    'name' => 'Ticket Subject',
                    'key' => '{ticket_subject}',
                    'available' => array(
                        'ticket',
                    ),
                ),
                array(
                    'name' => 'Ticket Message',
                    'key' => '{ticket_message}',
                    'available' => array(
                        'ticket',
                    ),
                ),
                array(
                    'name' => 'Ticket Status',
                    'key' => '{ticket_status}',
                    'available' => array(
                        'ticket',
                    ),
                ),
                array(
                    'name' => 'Ticket Priority',
                    'key' => '{ticket_priority}',
                    'available' => array(
                        'ticket',
                    ),
                ),
                array(
                    'name' => 'Ticket Service',
                    'key' => '{ticket_service}',
                    'available' => array(
                        'ticket',
                    ),
                ),
            ),
        ),
        array(
            'contract' => array(
                array(
                    'name' => 'Contract ID',
                    'key' => '{contract_id}',
                    'available' => array(
                        'contract',
                    ),
                ),
                array(
                    'name' => 'Contract Subject',
                    'key' => '{contract_subject}',
                    'available' => array(
                        'contract',
                    ),
                ),
                array(
                    'name' => 'Contract Description',
                    'key' => '{contract_description}',
                    'available' => array(
                        'contract',
                    ),
                ),
                array(
                    'name' => 'Contract Date Start',
                    'key' => '{contract_datestart}',
                    'available' => array(
                        'contract',
                    ),
                ),
                array(
                    'name' => 'Contract Date End',
                    'key' => '{contract_dateend}',
                    'available' => array(
                        'contract',
                    ),
                ),
                array(
                    'name' => 'Contract Value',
                    'key' => '{contract_contract_value}',
                    'available' => array(
                        'contract',
                    ),
                ),
            ),
        ),
        array(
            'invoice' => array(
                array(
                    'name' => 'Invoice Link',
                    'key' => '{invoice_link}',
                    'available' => array(
                        'invoice',
                    ),
                ),
                array(
                    'name' => 'Invoice Number',
                    'key' => '{invoice_number}',
                    'available' => array(
                        'invoice',
                    ),
                ),
                array(
                    'name' => 'Invoice Duedate',
                    'key' => '{invoice_duedate}',
                    'available' => array(
                        'invoice',
                    ),
                ),
                array(
                    'name' => 'Invoice Date',
                    'key' => '{invoice_date}',
                    'available' => array(
                        'invoice',
                    ),
                ),
                array(
                    'name' => 'Invoice Status',
                    'key' => '{invoice_status}',
                    'available' => array(
                        'invoice',
                    ),
                ),
                array(
                    'name' => 'Invoice Sale Agent',
                    'key' => '{invoice_sale_agent}',
                    'available' => array(
                        'invoice',
                    ),
                ),
                array(
                    'name' => 'Invoice Total',
                    'key' => '{invoice_total}',
                    'available' => array(
                        'invoice',
                    ),
                ),
                array(
                    'name' => 'Invoice Subtotal',
                    'key' => '{invoice_subtotal}',
                    'available' => array(
                        'invoice',
                    ),
                ),
                array(
                    'name' => 'Payment Recorded Total',
                    'key' => '{payment_total}',
                    'available' => array(
                        'invoice',
                    ),
                ),
                array(
                    'name' => 'Payment Recorded Date',
                    'key' => '{payment_date}',
                    'available' => array(
                        'invoice',
                    ),
                ),
            ),
        ),
        array(
            'estimate' => array(
                array(
                    'name' => 'Estimate Link',
                    'key' => '{estimate_link}',
                    'available' => array(
                        'estimate',
                    ),
                ),
                array(
                    'name' => 'Estimate Number',
                    'key' => '{estimate_number}',
                    'available' => array(
                        'estimate',
                    ),
                ),
                array(
                    'name' => 'Reference no.',
                    'key' => '{estimate_reference_no}',
                    'available' => array(
                        'estimate',
                    ),
                ),
                array(
                    'name' => 'Estimate Expiry Date',
                    'key' => '{estimate_expirydate}',
                    'available' => array(
                        'estimate',
                    ),
                ),
                array(
                    'name' => 'Estimate Date',
                    'key' => '{estimate_date}',
                    'available' => array(
                        'estimate',
                    ),
                ),
                array(
                    'name' => 'Estimate Status',
                    'key' => '{estimate_status}',
                    'available' => array(
                        'estimate',
                    ),
                ),
                array(
                    'name' => 'Estimate Sale Agent',
                    'key' => '{estimate_sale_agent}',
                    'available' => array(
                        'estimate',
                    ),
                ),
                array(
                    'name' => 'Estimate Total',
                    'key' => '{estimate_total}',
                    'available' => array(
                        'estimate',
                    ),
                ),
                array(
                    'name' => 'Estimate Subtotal',
                    'key' => '{estimate_subtotal}',
                    'available' => array(
                        'estimate',
                    ),
                ),
            ),
        ),
        array(
            'tasks' => array(
                array(
                    'name' => 'Staff/Contact who take action on task',
                    'key' => '{task_user_take_action}',
                    'available' => array(
                        'tasks',
                    ),
                ),
                array(
                    'name' => 'Task Link',
                    'key' => '{task_link}',
                    'available' => array(
                        'tasks',
                    ),
                ),
                array(
                    'name' => 'Comment Link',
                    'key' => '{comment_link}',
                    'available' => array(
                        'tasks',
                    ),
                ),
                array(
                    'name' => 'Task Name',
                    'key' => '{task_name}',
                    'available' => array(
                        'tasks',
                    ),
                ),
                array(
                    'name' => 'Task Description',
                    'key' => '{task_description}',
                    'available' => array(
                        'tasks',
                    ),
                ),
                array(
                    'name' => 'Task Status',
                    'key' => '{task_status}',
                    'available' => array(
                        'tasks',
                    ),
                ),
                array(
                    'name' => 'Task Comment',
                    'key' => '{task_comment}',
                    'available' => array(
                        'tasks',
                    ),
                ),
                array(
                    'name' => 'Task Priority',
                    'key' => '{task_priority}',
                    'available' => array(
                        'tasks',
                    ),
                ),
                array(
                    'name' => 'Task Start Date',
                    'key' => '{task_startdate}',
                    'available' => array(
                        'tasks',
                    ),
                ),
                array(
                    'name' => 'Task Due Date',
                    'key' => '{task_duedate}',
                    'available' => array(
                        'tasks',
                    ),
                ),
                array(
                    'name' => 'Related to',
                    'key' => '{task_related}',
                    'available' => array(
                        'tasks',
                    ),
                ),
            ),
        ),
        array(
            'proposals' => array(
                array(
                    'name' => 'Proposal ID',
                    'key' => '{proposal_id}',
                    'available' => array(
                        'proposals',
                    ),
                ),
                array(
                    'name' => 'Proposal Number',
                    'key' => '{proposal_number}',
                    'available' => array(
                        'proposals',
                    ),
                ),
                array(
                    'name' => 'Subject',
                    'key' => '{proposal_subject}',
                    'available' => array(
                        'proposals',
                    ),
                ),
                array(
                    'name' => 'Proposal Total',
                    'key' => '{proposal_total}',
                    'available' => array(
                        'proposals',
                    ),
                ),
                array(
                    'name' => 'Proposal Subtotal',
                    'key' => '{proposal_subtotal}',
                    'available' => array(
                        'proposals',
                    ),
                ),
                array(
                    'name' => 'Open Till',
                    'key' => '{proposal_open_till}',
                    'available' => array(
                        'proposals',
                    ),
                ),
                array(
                    'name' => 'Proposal Assigned',
                    'key' => '{proposal_assigned}',
                    'available' => array(
                        'proposals',
                    ),
                ),
                array(
                    'name' => 'Company Name',
                    'key' => '{proposal_proposal_to}',
                    'available' => array(
                        'proposals',
                    ),
                ),
                array(
                    'name' => 'Address',
                    'key' => '{proposal_address}',
                    'available' => array(
                        'proposals',
                    ),
                ),
                array(
                    'name' => 'City',
                    'key' => '{proposal_city}',
                    'available' => array(
                        'proposals',
                    ),
                ),
                array(
                    'name' => 'State',
                    'key' => '{proposal_state}',
                    'available' => array(
                        'proposals',
                    ),
                ),
                array(
                    'name' => 'Zip Code',
                    'key' => '{proposal_zip}',
                    'available' => array(
                        'proposals',
                    ),
                ),
                array(
                    'name' => 'Country',
                    'key' => '{proposal_country}',
                    'available' => array(
                        'proposals',
                    ),
                ),
                array(
                    'name' => 'Email',
                    'key' => '{proposal_email}',
                    'available' => array(
                        'proposals',
                    ),
                ),
                array(
                    'name' => 'Phone',
                    'key' => '{proposal_phone}',
                    'available' => array(
                        'proposals',
                    ),
                ),
                array(
                    'name' => 'Proposal Link',
                    'key' => '{proposal_link}',
                    'available' => array(
                        'proposals',
                    ),
                ),
            ),
        ),
        array(
            'leads' => array(
                array(
                    'name' => 'Lead Name',
                    'key' => '{lead_name}',
                    'available' => array(
                        'leads',
                    ),
                ),
                array(
                    'name' => 'Lead Email',
                    'key' => '{lead_email}',
                    'available' => array(
                        'leads',
                    ),
                ),
                array(
                    'name' => 'Lead Position',
                    'key' => '{lead_position}',
                    'available' => array(
                        'leads',
                    ),
                ),
                array(
                    'name' => 'Lead Website',
                    'key' => '{lead_website}',
                    'available' => array(
                        'leads',
                    ),
                ),
                array(
                    'name' => 'Lead Description',
                    'key' => '{lead_description}',
                    'available' => array(
                        'leads',
                    ),
                ),
                array(
                    'name' => 'Lead Phone Number',
                    'key' => '{lead_phonenumber}',
                    'available' => array(
                        'leads',
                    ),
                ),
                array(
                    'name' => 'Lead Company',
                    'key' => '{lead_company}',
                    'available' => array(
                        'leads',
                    ),
                ),
                array(
                    'name' => 'Lead Country',
                    'key' => '{lead_country}',
                    'available' => array(
                        'leads',
                    ),
                ),
                array(
                    'name' => 'Lead Zip',
                    'key' => '{lead_zip}',
                    'available' => array(
                        'leads',
                    ),
                ),
                array(
                    'name' => 'Lead City',
                    'key' => '{lead_city}',
                    'available' => array(
                        'leads',
                    ),
                ),
                array(
                    'name' => 'Lead State',
                    'key' => '{lead_state}',
                    'available' => array(
                        'leads',
                    ),
                ),
                array(
                    'name' => 'Lead Address',
                    'key' => '{lead_address}',
                    'available' => array(
                        'leads',
                    ),
                ),
                array(
                    'name' => 'Lead Assigned',
                    'key' => '{lead_assigned}',
                    'available' => array(
                        'leads',
                    ),
                ),
                array(
                    'name' => 'Lead Status',
                    'key' => '{lead_status}',
                    'available' => array(
                        'leads',
                    ),
                ),
                array(
                    'name' => 'Lead Souce',
                    'key' => '{lead_source}',
                    'available' => array(
                        'leads',
                    ),
                ),
                array(
                    'name' => 'Lead Link',
                    'key' => '{lead_link}',
                    'available' => array(
                        'leads',
                    ),
                ),
            ),
        ),
        array(
            'projects' => array(
                array(
                    'name' => 'Project Name',
                    'key' => '{project_name}',
                    'available' => array(
                        'project',
                    ),
                ),
                array(
                    'name' => 'Project Description',
                    'key' => '{project_description}',
                    'available' => array(
                        'project',
                    ),
                ),
                array(
                    'name' => 'Project Start Date',
                    'key' => '{project_start_date}',
                    'available' => array(
                        'project',
                    ),
                ),
                array(
                    'name' => 'Project Deadline',
                    'key' => '{project_deadline}',
                    'available' => array(
                        'project',
                    ),
                ),
                array(
                    'name' => 'Project Link',
                    'key' => '{project_link}',
                    'available' => array(
                        'project',
                    ),
                ),
                array(
                    'name' => 'Discussion Link',
                    'key' => '{discussion_link}',
                    'available' => array(
                        'project',
                    ),
                ),
                array(
                    'name' => 'File Creator',
                    'key' => '{file_creator}',
                    'available' => array(
                        'project',
                    ),
                ),
                array(
                    'name' => 'Discussion Creator',
                    'key' => '{discussion_creator}',
                    'available' => array(
                        'project',
                    ),
                ),
                array(
                    'name' => 'Comment Creator',
                    'key' => '{comment_creator}',
                    'available' => array(
                        'project',
                    ),
                ),
                array(
                    'name' => 'Discussion Subject',
                    'key' => '{discussion_subject}',
                    'available' => array(
                        'project',
                    ),
                ),
                array(
                    'name' => 'Discussion Description',
                    'key' => '{discussion_description}',
                    'available' => array(
                        'project',
                    ),
                ),
                array(
                    'name' => 'Discussion Comment',
                    'key' => '{discussion_comment}',
                    'available' => array(
                        'project',
                    ),
                ),
            ),
        ),
        array(
            'other' => array(
                array(
                    'name' => 'Logo URL',
                    'key' => '{logo_url}',
                    'fromoptions' => true,
                    'available' => array(
                        'ticket',
                        'client',
                        'staff',
                        'invoice',
                        'estimate',
                        'contract',
                        'tasks',
                        'proposals',
                        'project',
                        'leads',
                        'credit_note',
                    ),
                ),
                array(
                    'name' => 'Logo image with URL',
                    'key' => '{logo_image_with_url}',
                    'fromoptions' => true,
                    'available' => array(
                        'ticket',
                        'client',
                        'staff',
                        'invoice',
                        'estimate',
                        'contract',
                        'tasks',
                        'proposals',
                        'project',
                        'leads',
                        'credit_note',
                    ),
                ),
                array(
                    'name' => 'CRM URL',
                    'key' => '{crm_url}',
                    'fromoptions' => true,
                    'available' => array(
                        'ticket',
                        'client',
                        'staff',
                        'invoice',
                        'estimate',
                        'contract',
                        'tasks',
                        'proposals',
                        'project',
                        'leads',
                        'credit_note',
                    ),
                ),
                array(
                    'name' => 'Admin URL',
                    'key' => '{admin_url}',
                    'fromoptions' => true,
                    'available' => array(
                        'ticket',
                        'client',
                        'staff',
                        'invoice',
                        'estimate',
                        'contract',
                        'tasks',
                        'proposals',
                        'project',
                        'leads',
                        'credit_note',
                    ),
                ),
                array(
                    'name' => 'Main Domain',
                    'key' => '{main_domain}',
                    'fromoptions' => true,
                    'available' => array(
                        'ticket',
                        'client',
                        'staff',
                        'invoice',
                        'estimate',
                        'contract',
                        'tasks',
                        'proposals',
                        'project',
                        'leads',
                        'credit_note',
                    ),
                ),
                array(
                    'name' => 'Company Name',
                    'key' => '{companyname}',
                    'fromoptions' => true,
                    'available' => array(
                        'ticket',
                        'client',
                        'staff',
                        'invoice',
                        'estimate',
                        'contract',
                        'tasks',
                        'proposals',
                        'project',
                        'leads',
                        'credit_note',
                    ),
                ),
                array(
                    'name' => 'Email Signature',
                    'key' => '{email_signature}',
                    'fromoptions' => true,
                    'available' => array(
                        'ticket',
                        'client',
                        'staff',
                        'invoice',
                        'estimate',
                        'contract',
                        'tasks',
                        'proposals',
                        'project',
                        'leads',
                        'credit_note',
                    ),
                ),
            ),
        ),
    );
    $i                      = 0;
    foreach ($available_merge_fields as $fields) {
        $f = 0;
        // Fix for merge fields as custom fields not matching the names
        foreach ($fields as $key => $_fields) {
            switch ($key) {
                case 'clients':
                    $_key = 'customers';
                    break;
                case 'proposals':
                    $_key = 'proposal';
                    break;
                case 'contract':
                    $_key = 'contracts';
                    break;
                case 'ticket':
                    $_key = 'tickets';
                    break;
                default:
                    $_key = $key;
                    break;
            }

            $custom_fields = get_custom_fields($_key, array(), true);
            foreach ($custom_fields as $field) {
                array_push($available_merge_fields[$i][$key], array(
                    'name' => $field['name'],
                    'key' => '{' . $field['slug'] . '}',
                    'available' => $available_merge_fields[$i][$key][$f]['available'],
                ));
            }

            $f++;
        }
        $i++;
    }

    return do_action('available_merge_fields', $available_merge_fields);
}
