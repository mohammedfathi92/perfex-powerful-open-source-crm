<?php
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = array(
    'number',
    'date',
    get_sql_select_client_company(),
    'tblcreditnotes.status as status',
    'tblprojects.name as project_name',
    'reference_no',
    'total',
    '(SELECT tblcreditnotes.total - (SELECT COALESCE(SUM(amount),0) FROM tblcredits WHERE tblcredits.credit_id=tblcreditnotes.id)) as remaining_amount',
    );

$join = array(
    'LEFT JOIN tblclients ON tblclients.userid = tblcreditnotes.clientid',
    'LEFT JOIN tblcurrencies ON tblcurrencies.id = tblcreditnotes.currency',
    'LEFT JOIN tblprojects ON tblprojects.id = tblcreditnotes.project_id',
);

$sIndexColumn = "id";
$sTable       = 'tblcreditnotes';

$custom_fields = get_table_custom_fields('credit_note');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_'.$key);
    array_push($customFieldsColumns,$selectAs);
    array_push($aColumns, 'ctable_'.$key.'.value as '.$selectAs);
    array_push($join, 'LEFT JOIN tblcustomfieldsvalues as ctable_'.$key . ' ON tblcreditnotes.id = ctable_'.$key . '.relid AND ctable_'.$key . '.fieldto="'.$field['fieldto'].'" AND ctable_'.$key . '.fieldid='.$field['id']);
}

$where                    = array();
$filter = array();

if ($clientid != '') {
    array_push($where, 'AND tblcreditnotes.clientid='.$clientid);
}

if (!has_permission('credit_notes', '', 'view')) {
    array_push($where, 'AND tblcreditnotes.addedfrom='.get_staff_user_id());
}

$project_id = $this->_instance->input->get('project_id');
if($project_id) {
    array_push($where,'AND project_id='.$project_id);
}

$statuses = $this->_instance->credit_notes_model->get_statuses();
$statusIds = array();

foreach($statuses as $status){
    if($this->_instance->input->post('credit_notes_status_'.$status['id'])){
        array_push($statusIds,$status['id']);
    }
}

if (count($statusIds) > 0) {
    array_push($filter, 'AND tblcreditnotes.status IN (' . implode(', ', $statusIds) . ')');
}

$years = $this->_instance->credit_notes_model->get_credits_years();
$yearsArray = array();

foreach ($years as $year) {
    if ($this->_instance->input->post('year_'.$year['year'])) {
        array_push($yearsArray, $year['year']);
    }
}

if (count($yearsArray) > 0) {
    array_push($filter, 'AND YEAR(date) IN ('.implode(', ', $yearsArray).')');
}

if (count($filter) > 0) {
    array_push($where, 'AND ('.prepare_dt_filter($filter).')');
}

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->_instance->db->query('SET SQL_BIG_SELECTS=1');
}

$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, array(
    'tblcreditnotes.id',
    'tblcreditnotes.clientid',
    'symbol',
    'project_id',
));

$output       = $result['output'];
$rResult      = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = array();

    $numberOutput = '';
    // If is from client area table
    if (is_numeric($clientid) || $project_id) {
        $numberOutput = '<a href="' . admin_url('credit_notes/list_credit_notes/' . $aRow['id']) . '" target="_blank">' . format_credit_note_number($aRow['id']) . '</a>';
    } else {
        $numberOutput = '<a href="' . admin_url('credit_notes/list_credit_notes/' . $aRow['id']) . '" onclick="init_credit_note(' . $aRow['id'] . '); return false;">' . format_credit_note_number($aRow['id']) . '</a>';
    }

    $row[] = $numberOutput;

    $row[] = _d($aRow['date']);

    $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . $aRow['company'] . '</a>';

    $row[] = format_credit_note_status($aRow['status']);

    $row[] = '<a href="'.admin_url('projects/view/'.$aRow['project_id']).'">'.$aRow['project_name'].'</a>';

    $row[] = $aRow['reference_no'];

    $row[] = format_money($aRow['total'], $aRow['symbol']);

    $row[] = format_money($aRow['remaining_amount'], $aRow['symbol']);

     // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $output['aaData'][] = $row;
}

echo json_encode($output);
die();
