<?php
$filter = array();

if ($this->_instance->input->post('my_tasks')) {
    array_push($filter,'OR (tblstafftasks.id IN (SELECT taskid FROM tblstafftaskassignees WHERE staffid = ' . get_staff_user_id() . '))');
}

$task_statuses = $this->_instance->tasks_model->get_statuses();
$_statuses = array();
foreach($task_statuses as $status){
    if($this->_instance->input->post('task_status_'.$status['id'])){
        array_push($_statuses,$status['id']);
    }
}
if(count($_statuses) > 0){
    array_push($filter,'AND status IN ('.implode(', ',$_statuses).')');
}
if ($this->_instance->input->post('not_assigned')) {
    array_push($filter,'AND tblstafftasks.id NOT IN (SELECT taskid FROM tblstafftaskassignees)');
}
if ($this->_instance->input->post('due_date_passed')) {
    array_push($filter,'AND (duedate < "' . date('Y-m-d') . '" AND duedate IS NOT NULL) AND status != 5');
}
if($this->_instance->input->post('recurring_tasks')){
    array_push($filter, 'AND recurring = 1');
}
if($this->_instance->input->post('today_tasks')){
    array_push($filter, 'AND startdate = "'.date('Y-m-d').'" AND status != 5');
}
if ($this->_instance->input->post('my_following_tasks')) {
    array_push($filter,'AND (tblstafftasks.id IN (SELECT taskid FROM tblstafftasksfollowers WHERE staffid = ' . get_staff_user_id() . '))');
}
if ($this->_instance->input->post('billable')) {
    array_push($filter,'AND billable = 1');
}
if ($this->_instance->input->post('billed')) {
    array_push($filter,'AND billed = 1');
}
if ($this->_instance->input->post('not_billed')) {
    array_push($filter,'AND billable =1 AND billed=0');
}
if ($this->_instance->input->post('upcoming_tasks')) {
    array_push($filter,'AND (startdate > "' . date('Y-m-d') . '") AND status != 5');
}

$assignees = $this->_instance->misc_model->get_tasks_distinct_assignees();
$_assignees = array();
foreach($assignees as $__assignee){
    if($this->_instance->input->post('task_assigned_'.$__assignee['assigneeid'])){
        array_push($_assignees,$__assignee['assigneeid']);
    }
}
if(count($_assignees) > 0){
     array_push($filter, 'AND (tblstafftasks.id IN (SELECT taskid FROM tblstafftaskassignees WHERE staffid IN (' . implode(', ', $_assignees) . ')))');
}

if (!has_permission('tasks', '', 'view')) {
    array_push($where, get_tasks_where_string());
}

if(count($filter) > 0){
    array_push($where,'AND ('.prepare_dt_filter($filter).')');
}

$where = do_action('tasks_table_sql_where',$where);
