<!-- Project Tasks -->
<?php
    if($project->settings->hide_tasks_on_main_tasks_table == '1') {
        echo '<i class="fa fa-exclamation fa-2x pull-left" data-toggle="tooltip" data-title="'._l('project_hide_tasks_settings_info').'"></i>';
    }
?>
<div class="tasks-table">
    <?php init_relation_tasks_table(array( 'data-new-rel-id'=>$project->id,'data-new-rel-type'=>'project')); ?>
</div>
