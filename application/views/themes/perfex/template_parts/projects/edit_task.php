<div class="row">
  <div class="col-md-12 mtop10">
    <?php echo form_open_multipart('',array('id'=>'task-form')); ?>
    <?php echo form_hidden('action','new_task'); ?>
    <?php echo form_hidden('task_id',$task->id); ?>
    <h2 class="no-mtop" id="task-edit-heading"><?php echo $task->name; ?></h2>
    <hr />
    <div class="form-group">
      <label for="name"><?php echo _l('task_add_edit_subject'); ?></label>
      <input type="text" name="name" id="name" class="form-control" required value="<?php echo $task->name; ?>">
    </div>
    <div class="row">
      <div class="col-md-<?php if($project->settings->view_milestones == 1){ echo 6; } else {echo 12;} ?>">
        <div class="form-group">
          <label for="priority" class="control-label"><?php echo _l('task_add_edit_priority'); ?></label>
          <select name="priority" class="selectpicker" id="priority" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
            <option value="1" <?php if($task->priority == 1){echo 'selected';} ?>><?php echo _l('task_priority_low'); ?></option>
            <option value="2" <?php if($task->priority == 2){echo 'selected';} ?>><?php echo _l('task_priority_medium'); ?></option>
            <option value="3" <?php if($task->priority == 3){echo 'selected';} ?>><?php echo _l('task_priority_high'); ?></option>
            <option value="4" <?php if($task->priority == 4){echo 'selected';} ?>><?php echo _l('task_priority_urgent'); ?></option>
            <?php do_action('task_priorities_select',$task); ?>
          </select>
        </div>
      </div>
      <?php if($project->settings->view_milestones == 1){ ?>
      <div class="col-md-6">
        <div class="form-group">
          <label for="milestone"><?php echo _l('task_milestone'); ?></label>
          <select name="milestone" id="milestone" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
            <option value=""></option>
            <?php foreach($milestones as $milestone){ ?>
            <option value="<?php echo $milestone['id']; ?>"<?php if($milestone['id'] == $task->milestone){echo ' selected';} ?>><?php echo $milestone['name']; ?></option>
            <?php } ?>
          </select>
        </div>
      </div>
      <?php } else {
        echo form_hidden('milestone',$task->milestone);
      } ?>
    </div>
    <div class="row">
      <div class="col-md-6">
       <?php echo render_date_input('startdate','task_add_edit_start_date',_d($task->startdate),array('required'=>true)); ?>
     </div>
     <div class="col-md-6">
       <?php echo render_date_input('duedate','task_add_edit_due_date',$task->duedate,$project->deadline ? array('data-date-end-date'=>$project->deadline) : array()); ?>
     </div>
   </div>
   <?php if($project->settings->view_team_members == 1){ ?>
   <div class="form-group">
    <label for="assignees"><?php echo _l('task_single_assignees_select_title'); ?></label>
    <select class="selectpicker" multiple="true" name="assignees[]" id="assignees" data-width="100%" data-live-search="true">
      <?php foreach($members as $member){ ?>
      <option value="<?php echo $member['staff_id']; ?>"<?php if($this->tasks_model->is_task_assignee($member['staff_id'],$task->id)){echo ' selected';} ?>><?php echo get_staff_full_name($member['staff_id']); ?></option>
      <?php } ?>
    </select>
  </div>
  <?php } ?>
  <div class="form-group">
   <label for="description"><?php echo _l('task_add_edit_description'); ?></label>
   <textarea name="description" id="description" rows="10" class="form-control"><?php echo clear_textarea_breaks($task->description); ?></textarea>
 </div>
 <?php echo render_custom_fields('tasks',$task->id,array('show_on_client_portal'=>1)); ?>
 <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
 <?php echo form_close(); ?>
</div>
</div>
<script>
  $(function(){
    $("#name").on('paste keyup',function(){
      $('#task-edit-heading').html($(this).val());
    });
  });
</script>
