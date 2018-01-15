<?php
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = array('name', 'start_date', 'deadline', 'status');
$sIndexColumn = "id";
$sTable = 'tblprojects';
$additionalSelect = array('id');
$join = array(
    'JOIN tblclients ON tblclients.userid = tblprojects.clientid',
    );

$where = array();
$staff_id = get_staff_user_id();
if ($this->_instance->input->post('staff_id')) {
    $staff_id = $this->_instance->input->post('staff_id');
} else {
    // Request from home and finished not need to be shown
  array_push($where, ' AND status != 4');
}
array_push($where, ' AND tblprojects.id IN (SELECT project_id FROM tblprojectmembers WHERE staff_id='.$staff_id.')');

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = array();
    for ($i=0 ; $i<count($aColumns) ; $i++) {
        $_data = $aRow[ $aColumns[$i] ];

        if ($aColumns[$i] == 'start_date' || $aColumns[$i] == 'deadline') {
            $_data = _d($_data);
        } elseif ($aColumns[$i] == 'name') {
            $_data = '<a href="'.admin_url('projects/view/'.$aRow['id']).'">'.$_data.'</a>';
        } elseif ($aColumns[$i] == 'status') {
            $status = get_project_status_by_id($_data);
            $status = '<span class="label label inline-block project-status-' . $_data . '" style="color:'.$status['color'].';border:1px solid '.$status['color'].'">' . $status['name'] . '</span>';
            $_data  = $status;
        }

        $row[] = $_data;
    }
    $output['aaData'][] = $row;
}
