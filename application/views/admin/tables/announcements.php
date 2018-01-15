<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$aColumns     = array(
    'name',
    'dateadded',
    );
$sIndexColumn = "announcementid";
$sTable       = 'tblannouncements';
$where = array();
$is_admin = is_admin();
if(!is_admin()){
    $where = array('AND showtostaff=1');
}
$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, array(), $where, array(
    'announcementid',
    'showtostaff',
    ));
$output       = $result['output'];
$rResult      = $result['rResult'];
$is_admin = is_admin();
foreach ($rResult as $aRow) {
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
         if($aColumns[$i] == 'name'){
            if($is_admin){
               $_data = '<a href="'.admin_url('announcements/announcement/'.$aRow['announcementid']).'">'.$_data.'</a>';
            }
        } else if($aColumns[$i] == 'dateadded'){
            $_data = _d($_data);
        }
        $row[] = $_data;

    }
    $options = '';
    $options .= icon_btn('announcements/view/' . $aRow['announcementid'], 'eye','btn btn-info');
    if(total_rows('tbldismissedannouncements',array('announcementid'=>$aRow['announcementid'],'staff'=>1,'userid'=>get_staff_user_id())) == 0 && $aRow['showtostaff'] == 1){
        $options .= icon_btn('misc/dismiss_announcement/'. $aRow['announcementid'], 'check', 'btn-success',array('data-toggle'=>'tooltip','data-title'=>_l('dismiss_announcement')));
    }
    if(is_admin()){
        $options            .= icon_btn('announcements/announcement/' . $aRow['announcementid'], 'pencil-square-o');
        $options              .= icon_btn('announcements/delete/' . $aRow['announcementid'], 'remove', 'btn-danger _delete');
    }
    $row[] = $options;
    $output['aaData'][] = $row;
}
