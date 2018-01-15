<div class="row">
    <div class="col-md-12 text-right">
        <div class="form-group pull-right">
            <select class="selectpicker" name="gantt_type" onchange="gantt_filter();">
                <option value="milestones"<?php if(!$this->input->get('gantt_type') || $this->input->get('gantt_type') == 'milestones'){echo ' selected';} ?>><?php echo _l('project_milestones'); ?></option>
                <option value="members"<?php if($this->input->get('gantt_type') == 'members'){echo ' selected';} ?>>
                   <?php
                   if(has_permission('tasks','','view') || (!has_permission('tasks','','view') && get_option('show_all_tasks_for_project_member') == 1)){
                    echo _l('project_members');
                } else {
                    echo _l('home_my_tasks');
                } ?>
            </option>
            <option value="status"<?php if($this->input->get('gantt_type') == 'status'){echo ' selected';} ?>><?php echo _l('task_status'); ?></option>
        </select>
    </div>
    <div class="form-group pull-right mright10">
        <select class="selectpicker" name="gantt_task_status" onchange="gantt_filter();" data-none-selected-text="<?php echo _l('task_status'); ?>">
            <option value=""><?php echo _l('task_list_all'); ?></option>
            <?php foreach($task_statuses as $status){ ?>
            <option value="<?php echo $status['id']; ?>"<?php if($this->input->get('gantt_task_status') == $status['id']){echo ' selected';} ?>>
                <?php echo $status['name']; ?>
            </option>
            <?php } ?>

        </select>
    </div>
</div>

</div>
<div class="clearfix"></div>
<div id="gantt"></div>
