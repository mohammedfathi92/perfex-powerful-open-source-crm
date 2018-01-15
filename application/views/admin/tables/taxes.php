<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$aColumns     = array(
    'name',
    'taxrate'
    );
$sIndexColumn = "id";
$sTable       = 'tbltaxes';

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, array(), array(), array(
    'id'
    ));
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        $is_referenced = (total_rows('tblexpenses',array('tax'=>$aRow['id'])) > 0 || total_rows('tblexpenses',array('tax2'=>$aRow['id'])) > 0 ? 1 : 0);
        if($aColumns[$i] == 'name'){
            $_data = '<a href="#" data-toggle="modal" data-is-referenced="'.$is_referenced.'" data-target="#tax_modal" data-id="'.$aRow['id'].'">'.$_data.'</a>';
        }
        $row[] = $_data;
    }

    $options = icon_btn('#' . $aRow['id'], 'pencil-square-o', 'btn-default', array(
        'data-toggle' => 'modal',
        'data-target' => '#tax_modal',
        'data-id' => $aRow['id'],
        'data-is-referenced'=>$is_referenced
        ));
    $row[]   = $options .= icon_btn('taxes/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');

    $output['aaData'][] = $row;
}
