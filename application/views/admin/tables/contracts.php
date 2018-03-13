<?php
defined('BASEPATH') or exit('No direct script access allowed');

$this->ci->load->model('currencies_model');
$baseCurrencySymbol = $this->ci->currencies_model->get_base_currency()->symbol;

$aColumns = array(
    'tblcontracts.id as id',
    'subject',
    get_sql_select_client_company(),
    'tblcontracttypes.name as type_name',
    'contract_value',
    'datestart',
    'dateend'
    );

$sIndexColumn = "id";
$sTable = 'tblcontracts';

$join = array(
    'LEFT JOIN tblclients ON tblclients.userid = tblcontracts.client',
    'LEFT JOIN tblcontracttypes ON tblcontracttypes.id = tblcontracts.contract_type'
);

$custom_fields = get_table_custom_fields('contracts');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_'.$key);
    array_push($customFieldsColumns,$selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);

    array_push($join, 'LEFT JOIN tblcustomfieldsvalues as ctable_'.$key . ' ON tblcontracts.id = ctable_'.$key . '.relid AND ctable_'.$key . '.fieldto="'.$field['fieldto'].'" AND ctable_'.$key . '.fieldid='.$field['id']);
}

$where = array();
$filter = array();

if ($this->ci->input->post('exclude_trashed_contracts')) {
    array_push($filter, 'AND trash = 0');
}
if ($this->ci->input->post('trash')) {
    array_push($filter, 'OR trash = 1');
}
if ($this->ci->input->post('expired')) {
    array_push($filter, 'OR dateend IS NOT NULL AND dateend <"'.date('Y-m-d').'" and trash = 0');
}

if ($this->ci->input->post('without_dateend')) {
    array_push($filter, 'OR dateend IS NULL AND trash = 0');
}

$types = $this->ci->contracts_model->get_contract_types();
$typesIds = array();
foreach ($types as $type) {
    if ($this->ci->input->post('contracts_by_type_'.$type['id'])) {
        array_push($typesIds, $type['id']);
    }
}
if (count($typesIds) > 0) {
    array_push($filter, 'AND contract_type IN ('.implode(', ', $typesIds).')');
}
$years = $this->ci->contracts_model->get_contracts_years();
$yearsArray = array();
foreach ($years as $year) {
    if ($this->ci->input->post('year_'.$year['year'])) {
        array_push($yearsArray, $year['year']);
    }
}
if (count($yearsArray) > 0) {
    array_push($filter, 'AND YEAR(datestart) IN ('.implode(', ', $yearsArray).')');
}

$monthArray = array();
for ($m = 1; $m <= 12; $m++) {
    if ($this->ci->input->post('contracts_by_month_'.$m)) {
        array_push($monthArray, $m);
    }
}
if (count($monthArray) > 0) {
    array_push($filter, 'AND MONTH(datestart) IN ('.implode(', ', $monthArray).')');
}

if (count($filter) > 0) {
    array_push($where, 'AND ('.prepare_dt_filter($filter).')');
}

if ($clientid != '') {
    array_push($where, 'AND client='.$clientid);
}

if (!has_permission('contracts', '', 'view')) {
    array_push($where, 'AND tblcontracts.addedfrom='.get_staff_user_id());
}

$aColumns = do_action('contracts_table_sql_columns',$aColumns);

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, array('tblcontracts.id', 'trash', 'client'));

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = array();

    $row[] = $aRow['id'];

    $subjectOutput = '<a href="'.admin_url('contracts/contract/'.$aRow['id']).'">'.$aRow['subject'].'</a>';
    if ($aRow['trash'] == 1) {
        $subjectOutput .= '<span class="label label-danger mleft5 inline-block">'._l('contract_trash').'</span>';
    }

    $row[] = $subjectOutput;

    $row[] = '<a href="'.admin_url('clients/client/'.$aRow['client']).'">'. $aRow['company'] . '</a>';

    $row[] = $aRow['type_name'];

    $row[] = format_money($aRow['contract_value'], $baseCurrencySymbol);

    $row[] = _d($aRow['datestart']);

    $row[] = _d($aRow['dateend']);

    // Custom fields add values
    foreach($customFieldsColumns as $customFieldColumn){
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $hook = do_action('contracts_table_row_data', array(
        'output' => $row,
        'row' => $aRow
    ));

    $row = $hook['output'];

    $options = icon_btn('contracts/contract/'.$aRow['id'], 'pencil-square-o');
    if (has_permission('contracts', '', 'delete')) {
        $options .= icon_btn('contracts/delete/'.$aRow['id'], 'remove', 'btn-danger _delete');
    }
    $row[] = $options;

    if (!empty($aRow['dateend'])) {
        $_date_end = date('Y-m-d', strtotime($aRow['dateend']));
        if ($_date_end < date('Y-m-d')) {
            $row['DT_RowClass'] = 'alert-danger';
        }
    }

    $output['aaData'][] = $row;
}
