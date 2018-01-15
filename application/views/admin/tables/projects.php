<?php
defined('BASEPATH') or exit('No direct script access allowed');

$hasPermissionEdit = has_permission('projects', '', 'edit');
$hasPermissionDelete = has_permission('projects', '', 'delete');
$hasPermissionCreate = has_permission('projects', '', 'create');

$aColumns = array(
    'tblprojects.id as id',
    'name',
    get_sql_select_client_company(),
    '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM tbltags_in JOIN tbltags ON tbltags_in.tag_id = tbltags.id WHERE rel_id = tblprojects.id and rel_type="project" ORDER by tag_order ASC) as tags',
    'start_date',
    'deadline',
    '(SELECT GROUP_CONCAT(CONCAT(firstname, \' \', lastname) SEPARATOR ",") FROM tblprojectmembers JOIN tblstaff on tblstaff.staffid = tblprojectmembers.staff_id WHERE project_id=tblprojects.id ORDER BY staff_id) as members',
    );

$billingTypeVisible = false;
if (has_permission('projects', '', 'create') || has_permission('projects', '', 'edit')) {
    array_push($aColumns, 'billing_type');
    $billingTypeVisible = true;
}

array_push($aColumns, 'status');

$sIndexColumn = "id";
$sTable       = 'tblprojects';

$join             = array(
    'JOIN tblclients ON tblclients.userid = tblprojects.clientid'
);

$where  = array();
$filter = array();

if ($clientid != '') {
    array_push($where, ' AND clientid=' . $clientid);
}

if (!has_permission('projects', '', 'view') || $this->_instance->input->post('my_projects')) {
    array_push($where, ' AND tblprojects.id IN (SELECT project_id FROM tblprojectmembers WHERE staff_id=' . get_staff_user_id() . ')');
}

$statusIds = array();

foreach ($this->_instance->projects_model->get_project_statuses() as $status) {
    if ($this->_instance->input->post('project_status_' . $status['id'])) {
        array_push($statusIds, $status['id']);
    }
}

if (count($statusIds) > 0) {
    array_push($filter, 'OR status IN (' . implode(', ', $statusIds) . ')');
}

if (count($filter) > 0) {
    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
}

$custom_fields = get_table_custom_fields('projects');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_'.$key);
    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN tblcustomfieldsvalues as ctable_' . $key . ' ON tblprojects.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->_instance->db->query('SET SQL_BIG_SELECTS=1');
}

$aColumns = do_action('projects_table_sql_columns', $aColumns);

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, array(
    'clientid',
    '(SELECT GROUP_CONCAT(staff_id SEPARATOR ",") FROM tblprojectmembers WHERE project_id=tblprojects.id ORDER BY staff_id) as members_ids'
));

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = array();

    $row[] = '<a href="' . admin_url('projects/view/' . $aRow['id']) . '">' . $aRow['id'] . '</a>';

    $row[] = '<a href="' . admin_url('projects/view/' . $aRow['id']) . '">' . $aRow['name'] . '</a>';

    $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . $aRow['company'] . '</a>';

    $row[] = render_tags($aRow['tags']);

    $row[] = _d($aRow['start_date']);

    $row[] = _d($aRow['deadline']);

    $membersOutput = '';

    $members        = explode(',', $aRow['members']);
    $exportMembers = '';
    foreach ($members as $key=> $member) {
        if ($member != '') {
            $members_ids = explode(',', $aRow['members_ids']);
            $member_id   = $members_ids[$key];
            $membersOutput .= '<a href="' . admin_url('profile/' . $member_id) . '">' .
            staff_profile_image($member_id, array(
                'staff-profile-image-small mright5'
                ), 'small', array(
                'data-toggle' => 'tooltip',
                'data-title' => $member
                )) . '</a>';
                    // For exporting
            $exportMembers .= $member . ', ';
        }
    }

    $membersOutput .= '<span class="hide">' . trim($exportMembers, ', ') . '</span>';
    $row[] = $membersOutput;

    if ($billingTypeVisible) {
        if ($aRow['billing_type'] == 1) {
            $type_name = 'project_billing_type_fixed_cost';
        } elseif ($aRow['billing_type'] == 2) {
            $type_name = 'project_billing_type_project_hours';
        } else {
            $type_name = 'project_billing_type_project_task_hours';
        }
        $row[] = _l($type_name);
    }

    $status = get_project_status_by_id($aRow['status']);
    $row[] = '<span class="label label inline-block project-status-' . $aRow['status'] . '" style="color:'.$status['color'].';border:1px solid '.$status['color'].'">' . $status['name'] . '</span>';

    // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $hook = do_action('projects_table_row_data', array(
        'output' => $row,
        'row' => $aRow
    ));

    $row = $hook['output'];

    $options = '';

    if($hasPermissionCreate) {
        $options .= icon_btn('#', 'clone','btn-default',array('onclick'=>'copy_project('.$aRow['id'].');return false'));
    }

    if ($hasPermissionEdit) {
        $options .= icon_btn('projects/project/' . $aRow['id'], 'pencil-square-o');
    }

    if ($hasPermissionDelete) {
        $options .= icon_btn('projects/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');
    }

    $row[]              = $options;
    $output['aaData'][] = $row;
}
