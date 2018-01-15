   <h4 class="no-margin"><?php echo _l('tasks_summary'); ?></h4>
   <div class="row">
     <?php foreach(tasks_summary_data((isset($rel_id) ? $rel_id : null),(isset($rel_type) ? $rel_type : null)) as $summary){ ?>
      <div class="col-md-2 col-xs-6 border-right">
       <a href="#" onclick="dt_custom_view('task_status_<?php echo $summary['status_id']; ?>','<?php echo $table; ?>','task_status_<?php echo $summary['status_id']; ?>',true); return false;">
         <h3 class="bold"><?php echo $summary['total_tasks']; ?></h3>
         <span style="color:<?php echo $summary['color']; ?>">
          <?php echo $summary['name']; ?>
        </span>
        <small>
        </a>
        <br /><?php echo _l('tasks_view_assigned_to_user'); ?>: <?php echo $summary['total_my_tasks']; ?></small>
      </div>
      <?php } ?>
    </div>
    <hr class="hr-panel-heading" />
