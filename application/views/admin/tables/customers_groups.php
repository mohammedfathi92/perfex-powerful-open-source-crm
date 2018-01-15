<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$aColumns = array('name');

$sIndexColumn = "id";
$sTable = 'tblcustomersgroups';

$result = data_tables_init($aColumns,$sIndexColumn,$sTable,array(),array(),array('id'));
$output = $result['output'];
$rResult = $result['rResult'];

foreach ( $rResult as $aRow )
{
    $row = array();
    for ( $i=0 ; $i<count($aColumns) ; $i++ )
    {
        $_data = '<a href="#" data-toggle="modal" data-target="#customer_group_modal" data-id="'.$aRow['id'].'">'.$aRow[$aColumns[$i]].'</a>';

        $row[] = $_data;
    }
    $options = icon_btn('#','pencil-square-o','btn-default',array('data-toggle'=>'modal','data-target'=>'#customer_group_modal','data-id'=>$aRow['id']));
    $row[]  = $options .= icon_btn('clients/delete_group/'.$aRow['id'],'remove','btn-danger _delete');

    $output['aaData'][] = $row;
}
