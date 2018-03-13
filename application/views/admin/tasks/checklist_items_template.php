<div class="clearfix"></div>
<?php if(count($checklists) > 0){ ?>
<h4 class="bold chk-heading th font-medium"><?php echo _l('task_checklist_items'); ?></h4>
<?php } ?>
<div class="progress mtop15 hide">
    <div class="progress-bar not-dynamic progress-bar-default task-progress-bar" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width:0%">
    </div>
</div>
<?php foreach($checklists as $list){ ?>
<div class="checklist relative<?php if(($list['finished'] == 1 && $list['finished_from'] != get_staff_user_id()) || ($list['addedfrom'] != get_staff_user_id())){echo ' mbot25';} ?>" data-checklist-id="<?php echo $list['id']; ?>">
    <div class="checkbox checkbox-success checklist-checkbox" data-toggle="tooltip" title="">
        <input type="checkbox" <?php if($list['finished'] == 1 && $list['finished_from'] != get_staff_user_id() && !is_admin()){echo 'disabled';} ?> name="checklist-box" <?php if($list['finished'] == 1){echo 'checked';}; ?>>
        <label for=""><span class="hide"><?php echo $list['description']; ?></span></label>
        <textarea data-taskid="<?php echo $task_id; ?>" name="checklist-description" rows="1"<?php if($list['addedfrom'] != get_staff_user_id() && !has_permission('tasks','','edit')){echo ' disabled';} ?>><?php echo clear_textarea_breaks($list['description']); ?></textarea>
        <?php if(has_permission('tasks','','delete') || $list['addedfrom'] == get_staff_user_id()){ ?>
        <a href="#" class="pull-right text-muted remove-checklist" onclick="delete_checklist_item(<?php echo $list['id']; ?>,this); return false;"><i class="fa fa-remove"></i>
        </a>
        <?php } ?>
        <?php if(has_permission('checklist_templates','','create')){ ?>
            <a href="#" class="pull-right text-muted mright5 save-checklist-template<?php if($list['description'] == '' || total_rows('tblcheckliststemplates',array('description'=>$list['description'])) > 0){echo ' hide';} ?>" data-toggle="tooltip" data-title="<?php echo _l('save_as_template'); ?>" onclick="save_checklist_item_template(<?php echo $list['id']; ?>,this); return false;">
            <i class="fa fa-level-up" aria-hidden="true"></i>
            </a>
        <?php } ?>
    </div>
    <?php if($list['finished'] == 1 || $list['addedfrom'] != get_staff_user_id()){ ?>
    <p class="font-medium-xs mtop15 text-muted checklist-item-completed-by">
        <?php
            if($list['addedfrom'] != get_staff_user_id()) {
                echo _l('task_created_by',get_staff_full_name($list['addedfrom']));
            }
            if($list['addedfrom'] != get_staff_user_id() && $list['finished'] == 1) {
                echo ' - ';
            }
            if($list['finished'] == 1){
                echo _l('task_checklist_item_completed_by',get_staff_full_name($list['finished_from']));
            }
        ?>
        </p>
    <?php } ?>
</div>

<?php } ?>
<script>
    $(function(){
       $("#checklist-items").sortable({
        helper: 'clone',
        items: 'div.checklist',
        update: function(event, ui) {
            update_checklist_order();
        }
    });
    setTimeout(function(){
        do_task_checklist_items_height();
    },200);
   });
</script>
