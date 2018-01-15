<?php
defined('BASEPATH') or exit('No direct script access allowed');
$custom_fields = get_custom_fields('staff', array(
    'show_on_table' => 1
    ));
$aColumns      = array(
    'firstname',
    'email',
    'last_login',
    'active'
    );
$sIndexColumn  = "staffid";
$sTable        = 'tblstaff';
$join          = array();
$i             = 0;
foreach ($custom_fields as $field) {
    $select_as = 'cvalue_'.$i;
    if ($field['type'] == 'date_picker' || $field['type'] == 'date_picker_time') {
        $select_as = 'date_picker_cvalue_'.$i;
    }
    array_push($aColumns, 'ctable_'.$i.'.value as '.$select_as);
    array_push($join, 'LEFT JOIN tblcustomfieldsvalues as ctable_' . $i . ' ON tblstaff.staffid = ctable_' . $i . '.relid AND ctable_' . $i . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $i . '.fieldid=' . $field['id']);
    $i++;
}
            // Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->_instance->db->query('SET SQL_BIG_SELECTS=1');
}

$where = do_action('staff_table_sql_where', array());

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, array(
    'profile_image',
    'lastname',
    'staffid',
    'admin'
    ));

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        if (strpos($aColumns[$i], 'as') !== false && !isset($aRow[$aColumns[$i]])) {
            $_data = $aRow[strafter($aColumns[$i], 'as ')];
        } else {
            $_data = $aRow[$aColumns[$i]];
        }
        if ($aColumns[$i] == 'last_login') {
            if ($_data != null) {
                $_data = '<span class="text-has-action" data-toggle="tooltip" data-title="'._dt($_data).'">'.time_ago($_data) . '</span>';
            } else {
                $_data = 'Never';
            }
        } elseif ($aColumns[$i] == 'active') {
            $checked = '';
            if ($aRow['active'] == 1) {
                $checked = 'checked';
            }

            $_data = '<div class="onoffswitch">
                <input type="checkbox" data-switch-url="'.admin_url().'staff/change_staff_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_'.$aRow['staffid'].'" data-id="'.$aRow['staffid'].'" ' . $checked . '>
                <label class="onoffswitch-label" for="c_'.$aRow['staffid'].'"></label>
            </div>';

            // For exporting
            $_data .= '<span class="hide">' . ($checked == 'checked' ? _l('is_active_export') : _l('is_not_active_export')) . '</span>';
        } elseif ($aColumns[$i] == 'firstname') {
            $_data = '<a href="' . admin_url('staff/profile/' . $aRow['staffid']) . '">' . staff_profile_image($aRow['staffid'], array(
                'staff-profile-image-small'
                )) . '</a>';
            $_data .= ' <a href="' . admin_url('staff/member/' . $aRow['staffid']) . '">' . $aRow['firstname'] . ' ' . $aRow['lastname'] . '</a>';
        } elseif ($aColumns[$i] == 'email') {
            $_data = '<a href="mailto:' . $_data . '">' . $_data . '</a>';
        } else {
            if (strpos($aColumns[$i], 'date_picker_') !== false) {
                $_data = (strpos($_data, ' ') !== false ? _dt($_data) : _d($_data));
            }
        }
        $row[] = $_data;
    }
    $options = icon_btn('staff/member/' . $aRow['staffid'], 'pencil-square-o');
    if (has_permission('staff', '', 'delete') && $output['iTotalRecords'] > 1 && $aRow['staffid'] != get_staff_user_id()) {
        $options .= icon_btn('#', 'remove', 'btn-danger', array(
            'onclick'=>'delete_staff_member('.$aRow['staffid'].'); return false;',
            ));
    }
    $row[]              = $options;
    $output['aaData'][] = $row;
}
