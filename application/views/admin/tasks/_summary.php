<h4 class="mbot15"><?php echo _l('tasks_summary'); ?></h4>
<div class="row">
  <?php foreach(tasks_summary_data((isset($rel_id) ? $rel_id : null),(isset($rel_type) ? $rel_type : null)) as $summary){ ?>
    <div class="col-md-2 col-xs-6 border-right">
      <h3 class="bold no-mtop"><?php echo $summary['total_tasks']; ?></h3>
      <p style="color:<?php echo $summary['color']; ?>" class="font-medium no-mbot">
        <?php echo $summary['name']; ?>
      </p>
      <p class="font-medium-xs no-mbot text-muted">
        <?php echo _l('tasks_view_assigned_to_user'); ?>: <?php echo $summary['total_my_tasks']; ?>
      </p>
    </div>
    <?php } ?>
  </div>
  <hr class="hr-panel-heading" />
