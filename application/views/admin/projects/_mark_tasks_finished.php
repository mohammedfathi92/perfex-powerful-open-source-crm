<div class="modal fade" id="mark_tasks_finished_modal" tabindex="-1" role="dialog" data-toggle="modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4><?php echo _l('additional_action_required'); ?></h4>
        </div>
        <div class="modal-body">
          <div class="checkbox checkbox-primary">
            <input type="checkbox" name="notify_project_members_status_change" id="notify_project_members_status_change">
            <label for="notify_project_members_status_change"><?php echo _l('notify_project_members_status_change'); ?></label>
        </div>
        <div class="checkbox checkbox-primary">
            <input type="checkbox" name="mark_all_tasks_as_completed" checked id="mark_all_tasks_as_completed">
            <label for="mark_all_tasks_as_completed"><?php echo _l('project_mark_all_tasks_as_completed'); ?></label>
        </div>
        <?php if(total_rows('tblemailtemplates',array('slug'=>'project-finished-to-customer','active'=>0)) == 0 && total_rows('tblcontacts',array('userid'=>$project->clientid,'active'=>1)) > 0){ ?>
        <div class="form-group project_marked_as_finished hide no-mbot">
            <hr />
            <div class="checkbox checkbox-primary">
                <input type="checkbox" name="project_marked_as_finished_email_to_contacts" id="project_marked_as_finished_email_to_contacts">
                <label for="project_marked_as_finished_email_to_contacts"><?php echo _l('project_marked_as_finished_to_contacts'); ?></label>
            </div>
        </div>
        <?php } ?>
    </div>
    <div class="modal-footer">
        <button class="btn btn-info" id="project_mark_status_confirm" onclick="confirm_project_status_change(this); return false;"><?php echo _l('project_mark_tasks_finished_confirm'); ?></button>
    </div>
</div>
</div>
</div>
