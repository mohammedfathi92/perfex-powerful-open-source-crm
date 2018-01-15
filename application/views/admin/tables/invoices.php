<?php
defined('BASEPATH') or exit('No direct script access allowed');

$project_id = $this->_instance->input->post('project_id');

$aColumns = array(
    'number',
    'total',
    'total_tax',
    'YEAR(date) as year',
    'date',
    get_sql_select_client_company(),
    'tblprojects.name as project_name',
    'duedate',
    'tblinvoices.status',
    );


$sIndexColumn = "id";
$sTable       = 'tblinvoices';

$join = array(
    'LEFT JOIN tblclients ON tblclients.userid = tblinvoices.clientid',
    'LEFT JOIN tblcurrencies ON tblcurrencies.id = tblinvoices.currency',
    'LEFT JOIN tblprojects ON tblprojects.id = tblinvoices.project_id',
);

$custom_fields = get_table_custom_fields('invoice');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_'.$key);

    array_push($customFieldsColumns,$selectAs);
    array_push($aColumns, 'ctable_'.$key.'.value as '.$selectAs);
    array_push($join, 'LEFT JOIN tblcustomfieldsvalues as ctable_'.$key . ' ON tblinvoices.id = ctable_'.$key . '.relid AND ctable_'.$key . '.fieldto="'.$field['fieldto'].'" AND ctable_'.$key . '.fieldid='.$field['id']);
}

$where = array();
$filter = array();

if ($this->_instance->input->post('not_sent')) {
    array_push($filter, 'AND sent = 0 AND tblinvoices.status NOT IN(2,5)');
}
if ($this->_instance->input->post('not_have_payment')) {
    array_push($filter, 'AND tblinvoices.id NOT IN(SELECT invoiceid FROM tblinvoicepaymentrecords) AND tblinvoices.status != 5');
}
if ($this->_instance->input->post('recurring')) {
    array_push($filter, 'AND recurring > 0');
}

$statuses = $this->_instance->invoices_model->get_statuses();
$statusIds = array();
foreach ($statuses as $status) {
    if ($this->_instance->input->post('invoices_'.$status)) {
        array_push($statusIds, $status);
    }
}
if (count($statusIds) > 0) {
    array_push($filter, 'AND tblinvoices.status IN (' . implode(', ', $statusIds) . ')');
}

$agents = $this->_instance->invoices_model->get_sale_agents();
$agentsIds = array();
foreach ($agents as $agent) {
    if ($this->_instance->input->post('sale_agent_'.$agent['sale_agent'])) {
        array_push($agentsIds, $agent['sale_agent']);
    }
}
if (count($agentsIds) > 0) {
    array_push($filter, 'AND sale_agent IN (' . implode(', ', $agentsIds) . ')');
}

$modesIds = array();
foreach ($data['payment_modes'] as $mode) {
    if ($this->_instance->input->post('invoice_payments_by_'.$mode['id'])) {
        array_push($modesIds, $mode['id']);
    }
}
if (count($modesIds) > 0) {
    array_push($where, 'AND tblinvoices.id IN (SELECT invoiceid FROM tblinvoicepaymentrecords WHERE paymentmode IN ("'. implode('", "', $modesIds) .'"))');
}

$years = $this->_instance->invoices_model->get_invoices_years();
$yearArray = array();
foreach ($years as $year) {
    if ($this->_instance->input->post('year_'.$year['year'])) {
        array_push($yearArray, $year['year']);
    }
}
if (count($yearArray) > 0) {
    array_push($where, 'AND YEAR(date) IN ('.implode(', ', $yearArray).')');
}

if (count($filter) > 0) {
    array_push($where, 'AND ('.prepare_dt_filter($filter).')');
}

if ($clientid != '') {
    array_push($where, 'AND tblinvoices.clientid='.$clientid);
}

if ($project_id) {
    array_push($where, 'AND project_id='.$project_id);
}

if (!has_permission('invoices', '', 'view')) {
    array_push($where, 'AND tblinvoices.addedfrom='.get_staff_user_id());
}

$aColumns = do_action('invoices_table_sql_columns', $aColumns);

$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, array(
    'tblinvoices.id',
    'tblinvoices.clientid',
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
        $numberOutput = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['id']) . '" target="_blank">' . format_invoice_number($aRow['id']) . '</a>';
    } else {
        $numberOutput = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['id']) . '" onclick="init_invoice(' . $aRow['id'] . '); return false;">' . format_invoice_number($aRow['id']) . '</a>';
    }

    $row[] = $numberOutput;

    $row[] = format_money($aRow['total'], $aRow['symbol']);

    $row[] = format_money($aRow['total_tax'], $aRow['symbol']);

    $row[] = $aRow['year'];

    $row[] = _d($aRow['date']);

    $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . $aRow['company'] . '</a>';

    $row[] = '<a href="'.admin_url('projects/view/'.$aRow['project_id']).'">'.$aRow['project_name'].'</a>';;

    $row[] = _d($aRow['duedate']);

    $row[] = format_invoice_status($aRow['tblinvoices.status']);

     // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $hook = do_action('invoices_table_row_data', array(
        'output' => $row,
        'row' => $aRow
    ));

    $row = $hook['output'];

    $output['aaData'][] = $row;
}
