<?php
defined('BASEPATH') or exit('No direct script access allowed');
$aColumns     = array(
    'description',
    'date',
    'staff',
    'isnotified'
    );
$sIndexColumn = "id";
$sTable       = 'tblreminders';
$where        = array(
    'AND rel_id=' . $id . ' AND rel_type="' . $rel_type . '"'
    );
$join = array(
    'JOIN tblstaff ON tblstaff.staffid = tblreminders.staff'
    );
$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, array(
    'firstname',
    'lastname',
    'id',
    'creator',
    'rel_type'
    ));
$output       = $result['output'];
$rResult      = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'staff') {
            $_data = '<a href="' . admin_url('staff/profile/' . $aRow['staff']) . '">' . staff_profile_image($aRow['staff'], array(
                'staff-profile-image-small'
                )) . ' ' . $aRow['firstname'] . ' ' . $aRow['lastname'] . '</a>';
        } elseif ($aColumns[$i] == 'isnotified') {
            if ($_data == 1) {
                $_data = _l('reminder_is_notified_boolean_yes');
            } else {
                $_data = _l('reminder_is_notified_boolean_no');
            }
        } elseif ($aColumns[$i] == 'date') {
            $_data = _dt($_data);
        }
        $row[] = $_data;
    }
    if ($aRow['creator'] == get_staff_user_id() || is_admin()) {
        $opts = '';
        if($aRow['isnotified'] == 0){
            $opts .= icon_btn('#', 'pencil-square-o', 'btn-default edit-reminder',array('onclick'=>'edit_reminder('.$aRow['id'].',this)'));
        }
        $opts .= icon_btn('misc/delete_reminder/' . $id . '/' . $aRow['id'] . '/' . $aRow['rel_type'], 'remove', 'btn-danger delete-reminder');
        $row[] = $opts;
    } else {
        $row[] = '';
    }
    $output['aaData'][] = $row;
}
