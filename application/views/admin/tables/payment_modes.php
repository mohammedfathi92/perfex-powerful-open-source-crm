<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$aColumns     = array(
    'name',
    'description',
    'active',
    );
$sIndexColumn = "id";
$sTable       = 'tblinvoicepaymentsmodes';

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, array(), array(), array(
    'id',
    'expenses_only',
    'invoices_only',
    'show_on_pdf',
    'selected_by_default',
    ));
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];

        if($aColumns[$i] == 'active'){
            $checked = '';
            if($aRow['active'] == 1){
                $checked = 'checked';
            }
            $_data = '<div class="onoffswitch">
                <input type="checkbox" data-switch-url="'.admin_url().'paymentmodes/change_payment_mode_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_'.$aRow['id'].'" data-id="'.$aRow['id'].'" ' . $checked . '>
                <label class="onoffswitch-label" for="c_'.$aRow['id'].'"></label>
            </div>';
            // For exporting
            $_data .=  '<span class="hide">' . ($checked == 'checked' ? _l('is_active_export') : _l('is_not_active_export')) .'</span>';
        } else if($aColumns[$i] == 'name'){
            $_data = '<a href="#" data-toggle="modal" data-default-selected="'.$aRow['selected_by_default'].'" data-show-on-pdf="'.$aRow['show_on_pdf'].'" data-target="#payment_mode_modal" data-expenses-only="'.$aRow['expenses_only'].'" data-invoices-only="'.$aRow['invoices_only'].'" data-id="'.$aRow['id'].'">'.$_data.'</a>';
        }

        $row[] = $_data;
    }

    $options = icon_btn('#' . $aRow['id'], 'pencil-square-o', 'btn-default', array(
        'data-toggle' => 'modal',
        'data-target' => '#payment_mode_modal',
        'data-id' => $aRow['id'],
        'data-expenses-only' => $aRow['expenses_only'],
        'data-invoices-only' => $aRow['invoices_only'],
        'data-show-on-pdf' => $aRow['show_on_pdf'],
        'data-default-selected' => $aRow['selected_by_default'],
        ));
    $row[]   = $options .= icon_btn('paymentmodes/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');

    $output['aaData'][] = $row;
}
