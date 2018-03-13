<div class="row">
  <div class="col-md-12 mtop10">
    <?php echo form_open_multipart('',array('id'=>'task-form')); ?>
    <?php echo form_hidden('action','new_task'); ?>
    <h2 class="no-mtop"><?php echo _l('new_task'); ?></h2>
    <hr />
    <div class="form-group">
      <label for="name"><?php echo _l('task_add_edit_subject'); ?></label>
      <input type="text" name="name" id="name" class="form-control" required>
    </div>
    <div class="row">
      <div class="col-md-<?php if($project->settings->view_milestones == 1){ echo 6; } else {echo 12;} ?>">
        <div class="form-group">
          <label for="priority" class="control-label"><?php echo _l('task_add_edit_priority'); ?></label>
          <select name="priority" class="selectpicker" id="priority" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
            <option value="1" <?php if(get_option('default_task_priority') == 1){echo 'selected';} ?>><?php echo _l('task_priority_low'); ?></option>
            <option value="2" <?php if(get_option('default_task_priority') == 2){echo 'selected';} ?>><?php echo _l('task_priority_medium'); ?></option>
            <option value="3" <?php if(get_option('default_task_priority') == 3){echo 'selected';} ?>><?php echo _l('task_priority_high'); ?></option>
            <option value="4" <?php if(get_option('default_task_priority') == 4){echo 'selected';} ?>><?php echo _l('task_priority_urgent'); ?></option>
            <?php do_action('task_priorities_select',0); ?>
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
            <option value="<?php echo $milestone['id']; ?>"><?php echo $milestone['name']; ?></option>
            <?php } ?>
          </select>
        </div>
      </div>
      <?php } ?>
    </div>
    <div class="row">
      <div class="col-md-6">
       <?php echo render_date_input('startdate','task_add_edit_start_date',_d(date('Y-m-d')),array('required'=>true)); ?>
     </div>
     <div class="col-md-6">
       <?php echo render_date_input('duedate','task_add_edit_due_date','',$project->deadline ? array('data-date-end-date'=>$project->deadline) : array()); ?>
     </div>
   </div>
   <?php if($project->settings->view_team_members == 1){ ?>
   <div class="form-group">
    <label for="assignees"><?php echo _l('task_single_assignees_select_title'); ?></label>
    <select class="selectpicker" multiple="true" name="assignees[]" id="assignees" data-width="100%" data-live-search="true">
      <?php foreach($members as $member){ ?>
      <option value="<?php echo $member['staff_id']; ?>"<?php if(count($members) == 1){echo ' selected';} ?>><?php echo get_staff_full_name($member['staff_id']); ?></option>
      <?php } ?>
    </select>
  </div>
  <?php } ?>
  <div class="form-group">
   <label for="description"><?php echo _l('task_add_edit_description'); ?></label>
   <textarea name="description" id="description" rows="10" class="form-control"></textarea>
 </div>
 <?php echo render_custom_fields('tasks','',array('show_on_client_portal'=>1)); ?>
 <?php if($project->settings->upload_on_tasks == 1){ ?>
 <hr />
 <div class="row attachments">
  <div class="attachment">
    <div class="col-md-12">
      <div class="form-group">
        <label for="attachment" class="control-label"><?php echo _l('add_task_attachments'); ?></label>
        <div class="input-group">
          <input type="file" extension="<?php echo str_replace('.','',get_option('allowed_files')); ?>" filesize="<?php echo file_upload_max_size(); ?>" class="form-control" name="attachments[0]">
          <span class="input-group-btn">
            <button class="btn btn-success add_more_attachments p8-half" type="button"><i class="fa fa-plus"></i></button>
          </span>
        </div>
      </div>
    </div>
  </div>
</div>
<?php } ?>

<button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
<?php echo form_close(); ?>
</div>
</div>
