<div class="panel_s">
   <div class="panel-body">
      <h4 class="no-margin"><?php echo _l('clients_my_projects'); ?></h4>
   </div>
</div>
<div class="panel_s">
   <div class="panel-body">
      <div class="row mbot15">
         <div class="col-md-12">
            <h3 class="text-success no-mtop"><?php echo _l('projects_summary'); ?></h3>
         </div>
         <?php
            $where = array('clientid'=>get_client_user_id());
            foreach($project_statuses as $status){ ?>
         <div class="col-md-2 border-right">
            <?php $where['status'] = $status['id']; ?>
            <h3 class="bold"><a href="<?php echo site_url('clients/projects/'.$status['id']); ?>"><?php echo total_rows('tblprojects',$where); ?></a></h3>
            <span style="color:<?php echo $status['color']; ?>">
            <?php echo $status['name']; ?>
         </div>
         <?php } ?>
      </div>
      <hr />
         <table class="table dt-table" data-order-col="2" data-order-type="desc">
            <thead>
               <tr>
                  <th><?php echo _l('project_name'); ?></th>
                  <th><?php echo _l('project_start_date'); ?></th>
                  <th><?php echo _l('project_deadline'); ?></th>
                  <th><?php echo _l('project_billing_type'); ?></th>
                  <?php
                     $custom_fields = get_custom_fields('projects',array('show_on_client_portal'=>1));
                     foreach($custom_fields as $field){ ?>
                  <th><?php echo $field['name']; ?></th>
                  <?php } ?>
                  <th><?php echo _l('project_status'); ?></th>
               </tr>
            </thead>
            <tbody>
               <?php foreach($projects as $project){ ?>
               <tr>
                  <td><a href="<?php echo site_url('clients/project/'.$project['id']); ?>"><?php echo $project['name']; ?></a></td>
                  <td data-order="<?php echo $project['start_date']; ?>"><?php echo _d($project['start_date']); ?></td>
                  <td data-order="<?php echo $project['deadline']; ?>"><?php echo _d($project['deadline']); ?></td>
                  <td>
                     <?php
                        if($project['billing_type'] == 1){
                          $type_name = 'project_billing_type_fixed_cost';
                        } else if($project['billing_type'] == 2){
                          $type_name = 'project_billing_type_project_hours';
                        } else {
                          $type_name = 'project_billing_type_project_task_hours';
                        }
                        echo _l($type_name);
                        ?>
                  </td>
                  <?php foreach($custom_fields as $field){ ?>
                  <td><?php echo get_custom_field_value($project['id'],$field['id'],'projects'); ?></td>
                  <?php } ?>
                  <td>
                     <?php
                        $status = get_project_status_by_id($project['status']);
                        echo '<span class="label inline-block" style="color:'.$status['color'].';border:1px solid '.$status['color'].'">'.$status['name'].'</span>';
                        ?>
                  </td>
               </tr>
               <?php } ?>
            </tbody>
         </table>
   </div>
</div>
