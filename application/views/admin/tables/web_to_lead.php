<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$aColumns = array('id','name','(SELECT COUNT(id) FROM tblleads WHERE tblleads.from_form_id = tblwebtolead.id)','dateadded');

$sIndexColumn = "id";
$sTable = 'tblwebtolead';

$result = data_tables_init($aColumns,$sIndexColumn,$sTable,array(),array(),array('form_key','id'));
$output = $result['output'];
$rResult = $result['rResult'];

foreach ( $rResult as $aRow )
{
    $row = array();
    for ( $i=0 ; $i<count($aColumns) ; $i++ )
    {
        $_data = $aRow[$aColumns[$i]];
        if($aColumns[$i] == 'name'){
            $_data = '<a href="'.admin_url('leads/form/'.$aRow['id']).'">'.$_data.'</a>';
        } else if($aColumns[$i] == 'dateadded'){
               $_data = '<span class="text-has-action" data-toggle="tooltip" data-title="'._dt($_data).'">'.time_ago($_data) . '</span>';
        }

        $row[] = $_data;
    }
    $options = icon_btn('leads/form/'.$aRow['id'],'pencil-square-o','btn-default');
    $row[]  = $options .= icon_btn('leads/delete_form/'.$aRow['id'],'remove','btn-danger _delete');

    $output['aaData'][] = $row;
}
