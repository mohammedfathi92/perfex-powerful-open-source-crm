<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$aColumns     = array(
    'CASE tblreminders.rel_type
        WHEN \'customer\' THEN tblclients.company
        WHEN \'lead\' THEN tblleads.name
        WHEN \'estimate\' THEN tblestimates.id
        WHEN \'invoice\' THEN tblinvoices.id
        WHEN \'proposal\' THEN tblproposals.subject
        WHEN \'expense\' THEN tblexpenses.id
        WHEN \'credit_note\' THEN tblcreditnotes.id
        ELSE tblreminders.rel_type END as rel_type_name',
    'tblreminders.description',
    'tblreminders.date',
    );

$sIndexColumn = "id";

$sTable       = 'tblreminders';
$where        = array('AND staff = '.get_staff_user_id() . ' AND isnotified = 0');

$join = array(
    'LEFT JOIN tblclients ON tblclients.userid = tblreminders.rel_id AND tblreminders.rel_type="customer"',
    'LEFT JOIN tblleads ON tblleads.id = tblreminders.rel_id AND tblreminders.rel_type="lead"',
    'LEFT JOIN tblestimates ON tblestimates.id = tblreminders.rel_id AND tblreminders.rel_type="estimate"',
    'LEFT JOIN tblinvoices ON tblinvoices.id = tblreminders.rel_id AND tblreminders.rel_type="invoice"',
    'LEFT JOIN tblproposals ON tblproposals.id = tblreminders.rel_id AND tblreminders.rel_type="proposal"',
    'LEFT JOIN tblexpenses ON tblexpenses.id = tblreminders.rel_id AND tblreminders.rel_type="expense"',
    'LEFT JOIN tblcreditnotes ON tblcreditnotes.id = tblreminders.rel_id AND tblreminders.rel_type="credit_note"',
    );

$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, array(
    'tblreminders.id',
    'tblreminders.creator',
    'tblreminders.rel_type',
    'tblreminders.rel_id'
    ));

$output       = $result['output'];
$rResult      = $result['rResult'];
foreach ($rResult as $aRow) {

    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        if (strpos($aColumns[$i], 'as') !== false && !isset($aRow[$aColumns[$i]])) {
            $_data = $aRow[strafter($aColumns[$i], 'as ')];
        } else {
            $_data = $aRow[$aColumns[$i]];
        }

        if($aColumns[$i] == 'tblreminders.date'){
            $_data = _dt($_data);
        } else if($i == 0){
            // rel type name
            $rel_data   = get_relation_data($aRow['rel_type'], $aRow['rel_id']);
            $rel_values = get_relation_values($rel_data, $aRow['rel_type']);
            $_data = '<a href="' . $rel_values['link'] . '">' . $rel_values['name'] . '</a>';
        }

        $row[] = $_data;
    }

    if ($aRow['creator'] == get_staff_user_id() || is_admin()) {
        $row[] = icon_btn('misc/delete_reminder/' . $aRow['rel_id'] . '/' . $aRow['id'] . '/' . $aRow['rel_type'], 'remove', 'btn-danger delete-reminder');
    } else {
        $row[] = '';
    }

    $output['aaData'][] = $row;
}
