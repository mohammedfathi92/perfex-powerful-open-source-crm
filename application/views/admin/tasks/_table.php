<?php

$table_data = array(
    _l('tasks_dt_name'),
    _l('tasks_dt_datestart'),
    array(
        'name'=>_l('task_duedate'),
        'th_attrs'=>array('class'=>'duedate')
    ),
    _l('tags'),
    _l('task_assigned'),
    _l('tasks_list_priority'),
    _l('task_status')
);

if(isset($bulk_actions)){
    array_unshift($table_data,'<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="tasks"><label></label></div>');
}

$custom_fields = get_custom_fields('tasks', array(
    'show_on_table' => 1
));

foreach ($custom_fields as $field) {
    array_push($table_data, $field['name']);
}

$table_data = do_action('tasks_table_columns',$table_data);

array_push($table_data, _l('options'));

render_datatable($table_data, 'tasks');
?>
