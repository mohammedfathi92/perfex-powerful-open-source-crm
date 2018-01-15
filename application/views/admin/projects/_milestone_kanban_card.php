<li data-task-id="<?php echo $task['id']; ?>" class="task<?php if(has_permission('tasks','','create') || has_permission('tasks','','edit')){echo ' sortable';} ?><?php if($task['current_user_is_assigned']){echo ' current-user-task';} if((!empty($task['duedate']) && $task['duedate'] < date('Y-m-d')) && $task['status'] != 5){ echo ' overdue-task'; } ?>">
  <div class="panel-body">
    <div class="media">
      <?php
      $assignees = explode(',',$task['assignees_ids']);
      if(count($assignees) > 0 && $assignees[0] != ''){ ?>
      <div class="media-left">
        <?php if($task['current_user_is_assigned']){
         echo staff_profile_image(get_staff_user_id(),array('staff-profile-image-small pull-left'),'small',array('data-toggle'=>'tooltip','data-title'=>_l('project_task_assigned_to_user')));
       }
       foreach($assignees as $assigned){
        $assigned = trim($assigned);
        if($assigned != get_staff_user_id()){
         echo staff_profile_image($assigned,array('staff-profile-image-xs sub-staff-assigned-milestone pull-left'),'small',array('data-toggle'=>'tooltip','data-title'=>get_staff_full_name($assigned)));
       }
     }

     ?>
   </div>
   <?php } ?>
   <div class="media-body">
    <a href="<?php echo admin_url('tasks/view/'.$task['id']); ?>" class="task_milestone pull-left mbot5 mtop5<?php if($task['status'] == 5){echo ' text-muted line-throught';} ?>" onclick="init_task_modal(<?php echo $task['id']; ?>); return false;"><?php echo $task['name']; ?></a>
    <div class="clearfix"></div>
    <?php if(has_permission('tasks','','create')){ ?>
    <small>
      <?php echo _l('task_total_logged_time'); ?>
      <b>
        <?php echo seconds_to_time_format($task['total_logged_time']); ?>
      </b>
    </small> -
    <?php } ?>
    <small><?php echo _l('task_status'); ?>: <?php echo format_task_status($task['status'],true); ?></small>
    <br />
    <small><?php echo _l('tasks_dt_datestart'); ?>: <b><?php echo _d($task['startdate']); ?></b></small>
    <?php if(is_date($task['duedate'])){ ?>
    -
    <small><?php echo _l('task_duedate'); ?>: <b><?php echo _d($task['duedate']); ?></b></small>
    <?php } ?>
  </div>
</div>
</div>
</li>
