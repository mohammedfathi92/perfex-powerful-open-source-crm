<?php
defined('BASEPATH') or exit('No direct script access allowed');

$hasPermissionDelete = has_permission('customers', '', 'delete');

$custom_fields = get_table_custom_fields('customers');

$aColumns = array(
    '1',
    'tblclients.userid as userid',
    'company',
    'CONCAT(firstname, " ", lastname) as contact_fullname',
    'email',
    'tblclients.phonenumber as phonenumber',
    'tblclients.active',
    '(SELECT GROUP_CONCAT(name ORDER BY name ASC) FROM tblcustomersgroups LEFT JOIN tblcustomergroups_in ON tblcustomergroups_in.groupid = tblcustomersgroups.id WHERE customer_id = tblclients.userid) as groups'
    );

$sIndexColumn = "userid";
$sTable       = 'tblclients';
$where   = array();
// Add blank where all filter can be stored
$filter  = array();

$join = array('LEFT JOIN tblcontacts ON tblcontacts.userid=tblclients.userid AND tblcontacts.is_primary=1');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_'.$key);
    array_push($customFieldsColumns,$selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN tblcustomfieldsvalues as ctable_' . $key . ' ON tblclients.userid = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}
// Filter by custom groups
$groups  = $this->_instance->clients_model->get_groups();
$groupIds = array();
foreach ($groups as $group) {
    if ($this->_instance->input->post('customer_group_' . $group['id'])) {
        array_push($groupIds, $group['id']);
    }
}
if (count($groupIds) > 0) {
    array_push($filter, 'AND tblclients.userid IN (SELECT customer_id FROM tblcustomergroups_in WHERE groupid IN (' . implode(', ', $groupIds) . '))');
}

$this->_instance->load->model('invoices_model');
// Filter by invoices
$invoiceStatusIds = array();
foreach ($this->_instance->invoices_model->get_statuses() as $status) {
    if ($this->_instance->input->post('invoices_' . $status)) {
        array_push($invoiceStatusIds, $status);
    }
}
if (count($invoiceStatusIds) > 0) {
    array_push($filter, 'AND tblclients.userid IN (SELECT clientid FROM tblinvoices WHERE status IN (' . implode(', ', $invoiceStatusIds) . '))');
}

// Filter by estimates
$estimateStatusIds = array();
$this->_instance->load->model('estimates_model');
foreach ($this->_instance->estimates_model->get_statuses() as $status) {
    if ($this->_instance->input->post('estimates_' . $status)) {
        array_push($estimateStatusIds, $status);
    }
}
if (count($estimateStatusIds) > 0) {
    array_push($filter, 'AND tblclients.userid IN (SELECT clientid FROM tblestimates WHERE status IN (' . implode(', ', $estimateStatusIds) . '))');
}

// Filter by projects
$projectStatusIds = array();
$this->_instance->load->model('projects_model');
foreach ($this->_instance->projects_model->get_project_statuses() as $status) {
    if ($this->_instance->input->post('projects_' . $status['id'])) {
        array_push($projectStatusIds, $status['id']);
    }
}
if (count($projectStatusIds) > 0) {
    array_push($filter, 'AND tblclients.userid IN (SELECT clientid FROM tblprojects WHERE status IN (' . implode(', ', $projectStatusIds) . '))');
}

// Filter by proposals
$proposalStatusIds = array();
$this->_instance->load->model('proposals_model');
foreach ($this->_instance->proposals_model->get_statuses() as $status) {
    if ($this->_instance->input->post('proposals_' . $status)) {
        array_push($proposalStatusIds, $status);
    }
}
if (count($proposalStatusIds) > 0) {
    array_push($filter, 'AND tblclients.userid IN (SELECT rel_id FROM tblproposals WHERE status IN (' . implode(', ', $proposalStatusIds) . ') AND rel_type="customer")');
}

// Filter by having contracts by type
$this->_instance->load->model('contracts_model');
$contractTypesIds = array();
$contract_types  = $this->_instance->contracts_model->get_contract_types();

foreach ($contract_types as $type) {
    if ($this->_instance->input->post('contract_type_' . $type['id'])) {
        array_push($contractTypesIds, $type['id']);
    }
}
if (count($contractTypesIds) > 0) {
    array_push($filter, 'AND tblclients.userid IN (SELECT client FROM tblcontracts WHERE contract_type IN (' . implode(', ', $contractTypesIds) . '))');
}

// Filter by proposals
$customAdminIds = array();
foreach ($this->_instance->clients_model->get_customers_admin_unique_ids() as $cadmin) {
    if ($this->_instance->input->post('responsible_admin_' . $cadmin['staff_id'])) {
        array_push($customAdminIds, $cadmin['staff_id']);
    }
}

if (count($customAdminIds) > 0) {
    array_push($filter, 'AND tblclients.userid IN (SELECT customer_id FROM tblcustomeradmins WHERE staff_id IN (' . implode(', ', $customAdminIds) . '))');
}

if (count($filter) > 0) {
    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
}

if (!has_permission('customers', '', 'view')) {
    array_push($where, 'AND tblclients.userid IN (SELECT customer_id FROM tblcustomeradmins WHERE staff_id=' . get_staff_user_id() . ')');
}

if($this->_instance->input->post('exclude_inactive')){
    array_push($where,'AND tblclients.active=1');
}

if($this->_instance->input->post('my_customers')){
    array_push($where,'AND tblclients.userid IN (SELECT customer_id FROM tblcustomeradmins WHERE staff_id='.get_staff_user_id().')');
}

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->_instance->db->query('SET SQL_BIG_SELECTS=1');
}

$aColumns = do_action('customers_table_sql_columns', $aColumns);

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, array(
    'tblcontacts.id as contact_id',
    'tblclients.zip as zip'
));

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {

    $row = array();

    // Bulk actions
    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['userid'] . '"><label></label></div>';
    // User id
    $row[] = $aRow['userid'];

    // Company
    $company = $aRow['company'];

    if ($company == '') {
        $company = _l('no_company_view_profile');
    }

    $row[] = '<a href="' . admin_url('clients/client/' . $aRow['userid']) . '">' . $company . '</a>';

    // Primary contact
    $row[] = ($aRow['contact_id'] ? '<a href="' . admin_url('clients/client/' . $aRow['userid'] . '?contactid=' . $aRow['contact_id']) . '" target="_blank">' . $aRow['contact_fullname'] . '</a>' : '');

    // Primary contact email
    $row[] = ($aRow['email'] ? '<a href="mailto:' . $aRow['email'] . '">' . $aRow['email'] . '</a>' : '');

    // Primary contact phone
    $row[] = ($aRow['phonenumber'] ? '<a href="tel:' . $aRow['phonenumber'] . '">' . $aRow['phonenumber'] . '</a>' : '');

    // Toggle active/inactive customer
    $toggleActive = '<div class="onoffswitch" data-toggle="tooltip" data-title="' . _l('customer_active_inactive_help') . '">
        <input type="checkbox" data-switch-url="' . admin_url().'clients/change_client_status" name="onoffswitch" class="onoffswitch-checkbox" id="' . $aRow['userid'] . '" data-id="' . $aRow['userid'] . '" ' . ($aRow['tblclients.active'] == 1 ? 'checked' : '') . '>
        <label class="onoffswitch-label" for="' . $aRow['userid'] . '"></label>
    </div>';

    // For exporting
    $toggleActive .= '<span class="hide">' . ($aRow['tblclients.active'] == 1 ? _l('is_active_export') : _l('is_not_active_export')) . '</span>';

    $row[] = $toggleActive;

    // Customer groups parsing
    $groupsRow  = '';
    if ($aRow['groups']) {
        $groups = explode(',', $aRow['groups']);
        foreach ($groups as $group) {
            $groupsRow .= '<span class="label label-default mleft5 inline-block customer-group-list pointer">' . $group . '</span>';
        }
    }

    $row[] = $groupsRow;

    // Custom fields add values
    foreach($customFieldsColumns as $customFieldColumn){
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $hook = do_action('customers_table_row_data', array(
        'output' => $row,
        'row' => $aRow
    ));

    $row = $hook['output'];

    // Table options
    $options = icon_btn('clients/client/' . $aRow['userid'], 'pencil-square-o');

    // Show button delete if permission for delete exists
    if ($hasPermissionDelete) {
        $options .= icon_btn('clients/delete/' . $aRow['userid'], 'remove', 'btn-danger _delete');
    }

    $row[] = $options;
    $output['aaData'][] = $row;
}
