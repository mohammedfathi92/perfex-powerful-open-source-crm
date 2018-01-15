<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$aColumns         = array(
    'description',
    'date',
    'tblactivitylog.staffid'
    );

$sWhere = array();
if($this->_instance->input->post('activity_log_date')){
    array_push($sWhere,'AND date LIKE "'.to_sql_date($this->_instance->input->post('activity_log_date')).'%"');
}
$sIndexColumn     = "id";
$sTable           = 'tblactivitylog';
$result           = data_tables_init($aColumns, $sIndexColumn, $sTable, array(), $sWhere);
$output           = $result['output'];
$rResult          = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'date') {
            $_data = _dt($_data);
        }
        $row[] = $_data;
    }
    $output['aaData'][] = $row;
}
