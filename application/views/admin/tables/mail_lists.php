<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$aColumns         = array(
    'listid',
    'name',
    'tblemaillists.datecreated',
    'creator',
    );

$sIndexColumn     = "listid";
$sTable           = 'tblemaillists';
$result           = data_tables_init($aColumns, $sIndexColumn, $sTable, array(), array(), array());
$output           = $result['output'];
$rResult          = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'name') {
            $_data = '<a href="' . admin_url('surveys/mail_list_view/' . $aRow['listid']) . '">' . $_data . '</a>';
            $_data .= '<p>Total emails: ' . total_rows('tbllistemails', 'listid=' . $aRow['listid']) . '</p>';
        } else if ($aColumns[$i] == 'tblemaillists.datecreated') {
            $_data = _dt($_data);
        }
        $row[] = $_data;
    }
    $options = '';
    $options .= icon_btn('surveys/mail_list_view/' . $aRow['listid'], 'eye');
        if(has_permission('surveys','','edit')){

    $options .= icon_btn('surveys/mail_list/' . $aRow['listid'], 'pencil-square-o');
}
    if(has_permission('surveys','','delete')){

    $options .= icon_btn('surveys/delete_mail_list/' . $aRow['listid'], 'remove', 'btn-danger _delete');
}
     $row[] = $options;

    $output['aaData'][] = $row;
}
$staff_mail_list_row   = array(
    '--',
    '<a href="' . site_url("admin/surveys/mail_list_view/staff") . '" data-toggle="tooltip" title="' . _l('cant_edit_mail_list') . '">'._l('survey_send_mail_list_staff').'</a>',
    '--',
    '--',
    '<a href="' . site_url("admin/surveys/mail_list_view/staff") . '" class="btn btn-default btn-icon" ><i class="fa fa-eye"></i>'
    );
$clients_mail_list_row = array(
    '--',
    '<a href="' . site_url("admin/surveys/mail_list_view/clients") . '" data-toggle="tooltip" title="' . _l('cant_edit_mail_list') . '">'._l('customer_contacts').'</a>',
    '--',
    '--',
    '<a href="' . site_url("admin/surveys/mail_list_view/clients") . '" class="btn btn-default btn-icon" ><i class="fa fa-eye"></i>'
    );
$leads_mail_list_row = array(
    '--',
    '<a href="' . site_url("admin/surveys/mail_list_view/leads") . '" data-toggle="tooltip" title="' . _l('cant_edit_mail_list') . '">'._l('leads').'</a>',
    '--',
    '--',
    '<a href="' . site_url("admin/surveys/mail_list_view/leads") . '" class="btn btn-default btn-icon" ><i class="fa fa-eye"></i>'
    );
// Add clients and staff mail lists to top always
array_unshift($output['aaData'], $staff_mail_list_row);
array_unshift($output['aaData'], $clients_mail_list_row);
array_unshift($output['aaData'], $leads_mail_list_row);
