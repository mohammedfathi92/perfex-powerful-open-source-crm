    <a href="#" onclick="new_timesheet();return false;" class="btn btn-info mbot25"><?php echo _l('record_timesheet'); ?></a>
    <?php if(has_permission('projects','','create')){ ?>
    <div class="_filters _hidden_inputs timesheets_filters hidden">
        <?php
        foreach($timesheets_staff_ids as $t_staff_id){
            echo form_hidden('staff_id_'.$t_staff_id['staff_id'],$t_staff_id['staff_id']);
        }
        ?>
    </div>
    <?php if(count($timesheets_staff_ids) > 0){ ?>
    <div class="btn-group pull-right mleft4 btn-with-tooltip-group _filter_data" data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-filter" aria-hidden="true"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-right width300">
           <?php foreach($timesheets_staff_ids as $t_staff_id){ ?>
           <li class="active">
            <a href="#" data-cview="staff_id_<?php echo $t_staff_id['staff_id']; ?>" onclick="dt_custom_view(<?php echo $t_staff_id['staff_id']; ?>,'.table-timesheets','staff_id_<?php echo $t_staff_id['staff_id']; ?>'); return false;"><?php echo get_staff_full_name($t_staff_id['staff_id']); ?>
            </a>
        </li>
        <?php } ?>
    </ul>
</div>
<?php } ?>
<?php } ?>
<?php $table_data = array(
    _l('project_timesheet_user'),
    _l('project_timesheet_task'),
    _l('timesheet_tags'),
    _l('project_timesheet_start_time'),
    _l('project_timesheet_end_time'),
    _l('note'),
    _l('time_h'),
    _l('time_decimal'));
$table_data = do_action('projects_timesheets_table_columns',$table_data);
array_push($table_data,_l('options'));
render_datatable($table_data,'timesheets'); ?>
<?php $this->load->view('admin/projects/timesheet'); ?>
