<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$aColumns     = array(
    'id',
    'name',
    'fieldto',
    'type',
    'slug',
    'active',
    );
$sIndexColumn = "id";
$sTable       = 'tblcustomfields';

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'name' || $aColumns[$i] == 'id') {
            $_data = '<a href="' . admin_url('custom_fields/field/' . $aRow['id']) . '">' . $_data . '</a>';
        } else if ($aColumns[$i] == 'active') {
            $checked = '';
            if ($aRow['active'] == 1) {
                $checked = 'checked';
            }
            $_data = '<div class="onoffswitch">
                <input type="checkbox" data-switch-url="'.admin_url().'custom_fields/change_custom_field_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_'.$aRow['id'].'" data-id="'.$aRow['id'].'" ' . $checked . '>
                <label class="onoffswitch-label" for="c_'.$aRow['id'].'"></label>
            </div>';
                        // For exporting
            $_data .=  '<span class="hide">' . ($checked == 'checked' ? _l('is_active_export') : _l('is_not_active_export')) .'</span>';
        }

        $row[] = $_data;
    }

    $options = icon_btn('custom_fields/field/' . $aRow['id'], 'pencil-square-o');
    $row[]   = $options .= icon_btn('custom_fields/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');

    $output['aaData'][] = $row;
}
