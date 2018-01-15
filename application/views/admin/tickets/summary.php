<div class="row">
 <?php
 $statuses = $this->tickets_model->get_ticket_status();
 ?>
 <div class="_filters _hidden_inputs hidden tickets_filters">
  <?php
  echo form_hidden('my_tickets');
  if(is_admin()){
    $ticket_assignees = $this->tickets_model->get_tickets_assignes_disctinct();
    foreach($ticket_assignees as $assignee){
      echo form_hidden('ticket_assignee_'.$assignee['assigned']);
    }
  }
  foreach($statuses as $status){
    $val = '';
    if($chosen_ticket_status != ''){
      if($chosen_ticket_status == $status['ticketstatusid']){
        $val = $chosen_ticket_status;
      }
    } else {
      if(in_array($status['ticketstatusid'], $default_tickets_list_statuses)){
        $val = 1;
      }
    }
    echo form_hidden('ticket_status_'.$status['ticketstatusid'],$val);
  } ?>
</div>
<div class="col-md-12">
  <h4 class="no-margin"><?php echo _l('tickets_summary'); ?></h4>
  </div>
  <?php
  $where = '';
  if (!is_admin()) {
    if (get_option('staff_access_only_assigned_departments') == 1) {
      $departments_ids = array();
      if (count($staff_deparments_ids) == 0) {
        $departments = $this->departments_model->get();
        foreach($departments as $department){
          array_push($departments_ids,$department['departmentid']);
        }
      } else {
       $departments_ids = $staff_deparments_ids;
     }
     if(count($departments_ids) > 0){
      $where = 'AND department IN (SELECT departmentid FROM tblstaffdepartments WHERE departmentid IN (' . implode(',', $departments_ids) . ') AND staffid="'.get_staff_user_id().'")';
    }
  }
}
foreach($statuses as $status){
  $_where = '';
  if($where == ''){
    $_where = 'status='.$status['ticketstatusid'];
  } else{
    $_where = 'status='.$status['ticketstatusid'] . ' '.$where;
  }
  if(isset($project_id)){
    $_where = $_where . ' AND project_id='.$project_id;
  }
  ?>
  <div class="col-md-2 col-xs-6 mbot15 border-right">
    <a href="#" data-cview="ticket_status_<?php echo $status['ticketstatusid']; ?>" onclick="dt_custom_view('ticket_status_<?php echo $status['ticketstatusid']; ?>','.tickets-table','ticket_status_<?php echo $status['ticketstatusid']; ?>',true); return false;">
      <h3 class="bold"><?php echo total_rows('tbltickets',$_where); ?></h3>
      <span style="color:<?php echo $status['statuscolor']; ?>">
        <?php echo ticket_status_translate($status['ticketstatusid']); ?>
      </span>
    </a>
  </div>
  <?php } ?>
</div>
 <hr class="hr-panel-heading" />
