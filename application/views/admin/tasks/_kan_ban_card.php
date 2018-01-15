   <li data-task-id="<?php echo $task['id']; ?>" class="task<?php if($task['current_user_is_assigned']){echo ' current-user-task';} if((!empty($task['duedate']) && $task['duedate'] < date('Y-m-d')) && $task['status'] != 5){ echo ' overdue-task'; } ?>">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-12 task-name">
          <a href="#" onclick="init_task_modal(<?php echo $task['id']; ?>);return false;">
            <span class="inline-block full-width mtop10 mbot10"><?php echo $task['name']; ?></span>
          </a>
        </div>
        <div class="col-md-6 text-muted">
         <?php
            echo format_members_by_ids_and_names($task['assignees_ids'],$task['assignees'],false,'staff-profile-image-xs');
         ?>
      </div>
      <div class="col-md-6 text-right text-muted">
        <?php if($task['total_checklist_items'] > 0){ ?>
        <span class="mright5 inline-block text-muted" data-toggle="tooltip" data-title="<?php echo _l('task_checklist_items'); ?>">
          <i class="fa fa-check-square-o" aria-hidden="true"></i>
          <?php echo $task['total_finished_checklist_items']; ?>
          /
          <?php echo $task['total_checklist_items']; ?>
        </span>
        <?php } ?>
        <span class="mright5 inline-block text-muted" data-toggle="tooltip" data-title="<?php echo _l('task_comments'); ?>">
          <i class="fa fa-comments"></i> <?php echo $task['total_comments']; ?>
        </span>
        <span class="inline-block text-muted" data-toggle="tooltip" data-title="<?php echo _l('task_view_attachments'); ?>">
         <i class="fa fa-paperclip"></i>
         <?php echo $task['total_files']; ?>
       </span>
     </div>
     <?php $tags = get_tags_in($task['id'],'task');
     if(count($tags) > 0){ ?>
     <div class="col-md-12">
      <div class="mtop5 kanban-tags">
        <?php echo render_tags($tags); ?>
      </div>
    </div>
    <?php } ?>

  </div>
</div>
</li>
