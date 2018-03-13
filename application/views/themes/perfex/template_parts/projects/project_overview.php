<div class="row mtop15">
 <div class="col-md-6">
  <div class="panel-heading project-info-bg no-radius"><?php echo _l('project_overview'); ?></div>
  <div class="panel-body no-radius">
   <table class="table table-borded no-margin">
    <tbody>
     <tr>
      <td class="bold"><?php echo _l('project_billing_type'); ?></td>
      <td>
       <?php
       if($project->billing_type == 1){
        $type_name = 'project_billing_type_fixed_cost';
      } else if($project->billing_type == 2){
        $type_name = 'project_billing_type_project_hours';
      } else {
        $type_name = 'project_billing_type_project_task_hours';
      }
      echo _l($type_name);
      ?>
    </td>
    <?php if($project->billing_type == 1 || $project->billing_type == 2){
     echo '<tr class="project-cost">';
     if($project->billing_type == 1){
       echo '<td class="bold">'._l('project_total_cost').'</td>';
       echo '<td>'.format_money($project->project_cost,$currency->symbol).'</td>';
     } else {
       echo '<td class="bold">'._l('project_rate_per_hour').'</td>';
       echo '<td>'.format_money($project->project_rate_per_hour,$currency->symbol).'</td>';
     }
     echo '<tr>';
   }
   ?>
   <tr>
    <td class="bold"><?php echo _l('project_status'); ?></td>
    <td><?php echo $project_status['name']; ?></td>
  </tr>
  <tr>
    <td class="bold"><?php echo _l('project_start_date'); ?></td>
    <td><?php echo _d($project->start_date); ?></td>
  </tr>
  <?php if($project->deadline){ ?>
  <tr>
    <td class="bold"><?php echo _l('project_deadline'); ?></td>
    <td><?php echo _d($project->deadline); ?></td>
  </tr>
  <?php } ?>
  <?php if($project->date_finished){ ?>
  <tr class="text-success">
    <td class="bold"><?php echo _l('project_completed_date'); ?></td>
    <td><?php echo _d($project->date_finished); ?></td>
  </tr>
  <?php } ?>
  <?php if($project->billing_type == 1 && $project->settings->view_task_total_logged_time == 1){ ?>
  <tr class="project-total-logged-hours">
    <td class="bold"><?php echo _l('project_overview_total_logged_hours'); ?></td>
    <td><?php echo seconds_to_time_format($this->projects_model->total_logged_time($project->id)); ?></td>
  </tr>
  <?php } ?>
  <?php $custom_fields = get_custom_fields('projects',array('show_on_client_portal'=>1));
  if(count($custom_fields) > 0){ ?>
  <?php foreach($custom_fields as $field){ ?>
  <?php $value = get_custom_field_value($project->id,$field['id'],'projects');
  if($value == ''){continue;} ?>
  <tr>
    <td class="bold"><?php echo ucfirst($field['name']); ?></td>
    <td><?php echo $value; ?></td>
  </tr>
  <?php } ?>
  <?php } ?>
</tbody>
</table>
</div>
</div>
<div class="col-md-6 text-center">
 <p class="bold"><?php echo _l('project'). ' ' . _l('project_progress'); ?></p>
 <div class="project-progress relative mtop30" data-thickness="22" data-reverse="true" data-size="170" data-value="<?php echo $percent; ?>">
   <strong class="project-percent"></strong>
 </div>
</div>
<div class="clearfix"></div>
<div class="col-md-12">
  <hr />
</div>
<div class="col-md-<?php echo ($project->deadline ? 6 : 12); ?> project-progress-bars">
  <?php $tasks_not_completed_progress = round($tasks_not_completed_progress,2); ?>
  <?php $project_time_left_percent = round($project_time_left_percent,2); ?>
  <div class="row">
   <div class="col-md-9">
    <p class="text-uppercase bold text-dark font-medium">
     <?php echo $tasks_not_completed; ?> / <?php echo $total_tasks; ?> <?php echo _l('project_open_tasks'); ?>
   </p>
   <p class="text-muted bold"><?php echo $tasks_not_completed_progress; ?>%</p>
 </div>
 <div class="col-md-3 text-right">
  <i class="fa fa-check-circle<?php if($tasks_not_completed_progress >= 100){echo ' text-success';} ?>" aria-hidden="true"></i>
</div>
<div class="col-md-12 mtop5">
  <div class="progress no-margin progress-bar-mini">
   <div class="progress-bar light-green-bg no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo $tasks_not_completed_progress; ?>" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $tasks_not_completed_progress; ?>">
   </div>
 </div>
</div>
</div>
</div>
<?php if($project->deadline){ ?>
<div class="col-md-6 project-progress-bars">
  <div class="row">
   <div class="col-md-9">
    <p class="text-uppercase bold text-dark font-medium">
     <?php echo $project_days_left; ?> / <?php echo $project_total_days; ?> <?php echo _l('project_days_left'); ?>
   </p>
   <p class="text-muted bold"><?php echo $project_time_left_percent; ?>%</p>
 </div>
 <div class="col-md-3 text-right">
  <i class="fa fa-calendar-check-o<?php if($project_time_left_percent >= 100){echo ' text-success';} ?>" aria-hidden="true"></i>
</div>
<div class="col-md-12 mtop5">
  <div class="progress no-margin progress-bar-mini">
   <div class="progress-bar<?php if($project_time_left_percent == 0){echo ' progress-bar-warning ';} else { echo ' progress-bar-success ';} ?>no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo $project_time_left_percent; ?>" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $project_time_left_percent; ?>">
   </div>
 </div>
</div>
</div>
</div>
<?php } ?>

<?php if($project->settings->view_finance_overview == 1){ ?>
<div class="col-md-12 project-overview-column">
  <div class="row">
   <div class="col-md-12">
     <hr />
     <?php
     if($project->billing_type == 3 || $project->billing_type == 2){ ?>

     <div class="row">
      <div class="col-md-3">
        <?php
        $data = $this->projects_model->total_logged_time_by_billing_type($project->id);
        ?>
        <p class="text-uppercase text-muted"><?php echo _l('project_overview_logged_hours'); ?> <span class="bold"><?php echo $data['logged_time']; ?></span></p>
        <p class="bold font-medium"><?php echo format_money($data['total_money'],$currency->symbol); ?></p>
      </div>
      <div class="col-md-3">
        <?php
        $data = $this->projects_model->data_billable_time($project->id);
        ?>
        <p class="text-uppercase text-info"><?php echo _l('project_overview_billable_hours'); ?> <span class="bold"><?php echo $data['logged_time'] ?></span></p>
        <p class="bold font-medium"><?php echo format_money($data['total_money'],$currency->symbol); ?></p>
      </div>
      <div class="col-md-3">
        <?php
        $data = $this->projects_model->data_billed_time($project->id);
        ?>
        <p class="text-uppercase text-success"><?php echo _l('project_overview_billed_hours'); ?> <span class="bold"><?php echo $data['logged_time']; ?></span></p>
        <p class="bold font-medium"><?php echo format_money($data['total_money'],$currency->symbol); ?></p>
      </div>
      <div class="col-md-3">
        <?php
        $data = $this->projects_model->data_unbilled_time($project->id);
        ?>
        <p class="text-uppercase text-danger"><?php echo _l('project_overview_unbilled_hours'); ?> <span class="bold"><?php echo $data['logged_time']; ?></span></p>
        <p class="bold font-medium"><?php echo format_money($data['total_money'],$currency->symbol); ?></p>
      </div>
    </div>
    <hr />
    <?php } ?>
  </div>
</div>
<?php if($project->settings->available_features['project_expenses'] == 1){ ?>
<div class="row">
 <div class="col-md-3">
  <p class="text-uppercase text-muted"><?php echo _l('project_overview_expenses'); ?></span></p>
  <p class="bold font-medium"><?php echo format_money(sum_from_table('tblexpenses',array('where'=>array('project_id'=>$project->id),'field'=>'amount')),$currency->symbol); ?></p>
</div>
<div class="col-md-3">
  <p class="text-uppercase text-info"><?php echo _l('project_overview_expenses_billable'); ?></span></p>
  <p class="bold font-medium"><?php echo format_money(sum_from_table('tblexpenses',array('where'=>array('project_id'=>$project->id,'billable'=>1),'field'=>'amount')),$currency->symbol); ?></p>
</div>
<div class="col-md-3">
  <p class="text-uppercase text-success"><?php echo _l('project_overview_expenses_billed'); ?></span></p>
  <p class="bold font-medium"><?php echo format_money(sum_from_table('tblexpenses',array('where'=>array('project_id'=>$project->id,'invoiceid !='=>'NULL','billable'=>1),'field'=>'amount')),$currency->symbol); ?></p>
</div>
<div class="col-md-3">
  <p class="text-uppercase text-danger"><?php echo _l('project_overview_expenses_unbilled'); ?></span></p>
  <p class="bold font-medium"><?php echo format_money(sum_from_table('tblexpenses',array('where'=>array('project_id'=>$project->id,'invoiceid IS NULL','billable'=>1),'field'=>'amount')),$currency->symbol); ?></p>
</div>
</div>
<?php } ?>
</div>
<?php } ?>
<div class="col-md-12">
  <hr />
</div>
<div class="clearfix"></div>
<div class="col-md-<?php if($project->settings->view_team_members == 1){ echo 6; } else {echo 12;} ?>">
  <div class="panel-heading project-info-bg no-radius"><?php echo _l('project_description'); ?></div>
  <div class="panel-body no-radius tc-content project-description">
    <?php if(empty($project->description)){
     echo '<p class="text-muted text-center no-mbot">' . _l('no_description_project') . '</p>';
   }
   echo check_for_links($project->description); ?>
 </div>
</div>
<?php if($project->settings->view_team_members == 1){ ?>
<div class="col-md-6 team-members project-overview-column">
  <div class="panel-heading project-info-bg no-radius"><?php echo _l('project_members'); ?></div>
  <div class="panel-body">
   <?php
   if(count($members) == 0){
    echo '<div class="media-body text-center text-muted"><p>'._l('no_project_members').'</p></div>';
  }
  foreach($members as $member){ ?>
  <div class="media">
    <div class="media-left">
     <?php echo staff_profile_image($member['staff_id'],array('staff-profile-image-small','media-object')); ?>
   </div>
   <div class="media-body">
     <h5 class="media-heading mtop5"><?php echo get_staff_full_name($member['staff_id']); ?></h5>
   </div>
 </div>
 <?php } ?>
</div>
</div>
<?php } ?>
</div>
