<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$aColumns     = array(
    'name',
    'email',
    'calendar_id',
    );
$sIndexColumn = "departmentid";
$sTable       = 'tbldepartments';

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable,array(),array(),array('departmentid','email','hidefromclient','host','encryption','password','delete_after_import','imap_username'));
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        $ps = '';
        if(!empty($aRow['password'])){
            $ps = $this->_instance->encryption->decrypt($aRow['password']);
        }
        if ($aColumns[$i] == 'name') {
            $_data = '<a href="#" onclick="edit_department(this,'.$aRow['departmentid'].'); return false" data-name="'.$aRow['name'].'" data-calendar-id="'.$aRow['calendar_id'].'" data-email="'.$aRow['email'].'" data-hide-from-client="'.$aRow['hidefromclient'].'" data-host="'.$aRow['host'].'" data-password="'.$ps.'" data-imap_username="'.$aRow['imap_username'].'" data-encryption="'.$aRow['encryption'].'" data-delete-after-import="'.$aRow['delete_after_import'].'">' . $_data . '</a>';
        }
        $row[] = $_data;
    }

    $options = icon_btn('departments/department/' . $aRow['departmentid'], 'pencil-square-o','btn-default',array(
        'onclick'=>'edit_department(this,'.$aRow['departmentid'].'); return false','data-name'=>$aRow['name'],'data-calendar-id'=>$aRow['calendar_id'],'data-email'=>$aRow['email'],'data-hide-from-client'=>$aRow['hidefromclient'], 'data-host'=>$aRow['host'],'data-password'=>$ps,'data-encryption'=>$aRow['encryption'],'data-imap_username'=>$aRow['imap_username'],'data-delete-after-import'=>$aRow['delete_after_import']
        ));
    $row[]   = $options .= icon_btn('departments/delete/' . $aRow['departmentid'], 'remove', 'btn-danger _delete');

    $output['aaData'][] = $row;
}
