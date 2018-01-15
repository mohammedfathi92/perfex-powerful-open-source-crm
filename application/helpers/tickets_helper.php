<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Render admin tickets table
 * @param string  $name        table name
 * @param boolean $bulk_action include checkboxes on the left side for bulk actions
 */
function AdminTicketsTableStructure($name = '', $bulk_action = false)
{
    $table = '<table class="table dt-table-loading '.($name == '' ? 'tickets-table' : $name).' table-tickets">';
    $table .= '<thead>';
    $table .= '<tr>';
    if ($bulk_action == true) {
        $table .= '<th>';
        $table .= '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="tickets"><label></label></div>';
        $table .= '</th>';
    }
    $table .= '<th>#</th>';
    $table .= '<th>'._l('ticket_dt_subject').'</th>';
    $table .= '<th>'._l('tags').'</th>';
    $table .= '<th>'._l('ticket_dt_department').'</th>';
    $services_th_attrs = '';
    if (get_option('services') == 0) {
        $services_th_attrs = ' class="not_visible"';
    }
    $table .= '<th'.$services_th_attrs.'>'._l('ticket_dt_service').'</th>';
    $table .= '<th>'._l('ticket_dt_submitter').'</th>';
    $table .= '<th>'._l('ticket_dt_status').'</th>';
    $table .= '<th>'._l('ticket_dt_priority').'</th>';
    $table .= '<th>'._l('ticket_dt_last_reply').'</th>';
    $table .= '<th class="ticket_created_column">'._l('ticket_date_created').'</th>';

   $custom_fields = get_table_custom_fields('tickets');

    foreach ($custom_fields as $field) {
        $table .= '<th>'.$field['name'].'</th>';
    }

    $table .= '<th>'._l('options').'</th>';
    $table .= '</tr>';
    $table .= '</thead>';
    $table .= '<tbody></tbody>';
    $table .= '</table>';

    return $table;
}

/**
 * Function to translate ticket status
 * The app offers ability to translate ticket status no matter if they are stored in database
 * @param  mixed $id
 * @return string
 */
function ticket_status_translate($id)
{
    if ($id == '' || is_null($id)) {
        return '';
    }

    $line = _l('ticket_status_db_' . $id, '', false);

    if ($line == 'db_translate_not_found') {
        $CI =& get_instance();
        $CI->db->where('ticketstatusid', $id);
        $status = $CI->db->get('tblticketstatus')->row();

        return !$status ? '' : $status->name;
    }

    return $line;
}

/**
 * Function to translate ticket priority
 * The apps offers ability to translate ticket priority no matter if they are stored in database
 * @param  mixed $id
 * @return string
 */
function ticket_priority_translate($id)
{
    if ($id == '' || is_null($id)) {
        return '';
    }

    $line = _l('ticket_priority_db_' . $id, '', false);

    if ($line == 'db_translate_not_found') {
        $CI =& get_instance();
        $CI->db->where('priorityid', $id);
        $priority = $CI->db->get('tblpriorities')->row();

        return !$priority ? '' : $priority->name;
    }

    return $line;
}

/**
 * When ticket will be opened automatically set to open
 * @param integer  $current Current status
 * @param integer  $id      ticketid
 * @param boolean $admin   Admin opened or client opened
 */
function set_ticket_open($current, $id, $admin = true)
{
    if ($current == 1) {
        return;
    }

    $field = ($admin == false ? 'clientread' : 'adminread');

    $CI =& get_instance();
    $CI->db->where('ticketid', $id);

    $CI->db->update('tbltickets', array(
        $field => 1,
    ));
}

/**
 * For html5 form accepted attributes
 * This function is used for the tickets form attachments
 * @return string
 */
function get_ticket_form_accepted_mimes()
{
    $ticket_allowed_extensions  = get_option('ticket_attachments_file_extensions');
    $_ticket_allowed_extensions = explode(',', $ticket_allowed_extensions);
    $all_form_ext               = $ticket_allowed_extensions;

    if (is_array($_ticket_allowed_extensions)) {
        foreach ($_ticket_allowed_extensions as $ext) {
            $all_form_ext .= ',' . get_mime_by_extension($ext);
        }
    }

    return $all_form_ext;
}
