<div class="widget" id="widget-<?php echo basename(__FILE__,".php"); ?>" data-name="<?php echo _l('user_widget'); ?>">
   <div class="panel_s user-data">
      <div class="panel-body home-activity">
        <div class="widget-dragger"></div>
        <ul class="nav nav-tabs" role="tablist">
         <li role="presentation" class="active">
            <a href="#home_tab_tasks" aria-controls="home_tab_tasks" role="tab" data-toggle="tab">
               <i class="fa fa-tasks menu-icon"></i><?php echo _l('home_my_tasks'); ?>
            </a>
         </li>
         <li role="presentation">
            <a href="#home_my_projects" onclick="init_table_staff_projects(true);" aria-controls="home_my_projects" role="tab" data-toggle="tab">
               <i class="fa fa-bars menu-icon"></i><?php echo _l('home_my_projects'); ?>
            </a>
         </li>
         <li role="presentation">
            <a href="#home_my_reminders" onclick="initDataTable('.table-my-reminders', admin_url + 'misc/my_reminders', [3], [3],undefined,[2,'ASC']);" aria-controls="home_my_reminders" role="tab" data-toggle="tab">
               <i class="fa fa-clock-o menu-icon"></i><?php echo _l('my_reminders'); ?>
               <?php
               $total_reminders = total_rows('tblreminders',
                array(
                  'isnotified'=>0,
                  'staff'=>get_staff_user_id(),
               )
             );
               if($total_reminders > 0){
                echo '<span class="badge">'.$total_reminders.'</span>';
             }
             ?>
          </a>
       </li>
       <?php if((get_option('access_tickets_to_none_staff_members') == 1 && !is_staff_member()) || is_staff_member()){ ?>
       <li role="presentation">
         <a href="#home_tab_tickets" onclick="init_table_tickets(true);" aria-controls="home_tab_tickets" role="tab" data-toggle="tab">
            <i class="fa fa-ticket menu-icon"></i><?php echo _l('home_tickets'); ?>
         </a>
      </li>
      <?php } ?>
      <?php if(is_staff_member()){ ?>
      <li role="presentation">
         <a href="#home_announcements" onclick="init_table_announcements(true);" aria-controls="home_announcements" role="tab" data-toggle="tab">
            <i class="fa fa-bullhorn menu-icon"></i><?php echo _l('home_announcements'); ?>
            <?php if($total_undismissed_announcements != 0){ echo '<span class="badge">'.$total_undismissed_announcements.'</span>';} ?>
         </a>
      </li>
      <?php } ?>
      <?php if(is_admin()){ ?>
      <li role="presentation">
         <a href="#home_tab_activity" aria-controls="home_tab_activity" role="tab" data-toggle="tab">
            <i class="fa fa-window-maximize menu-icon"></i><?php echo _l('home_latest_activity'); ?>
         </a>
      </li>
      <?php } ?>
   </ul>
   <hr class="hr-panel-heading no-mtop" />
   <div class="tab-content">
      <div role="tabpanel" class="tab-pane active" id="home_tab_tasks">
         <a href="<?php echo admin_url('tasks/list_tasks'); ?>" class="mbot20 inline-block full-width"><?php echo _l('home_widget_view_all'); ?></a>
         <div class="clearfix"></div>
         <div class="_hidden_inputs _filters _tasks_filters">
            <?php
            echo form_hidden('my_tasks',true);
            foreach($task_statuses as $status){
              $val = 'true';
              if($status['id'] == 5){
               $val = '';
            }
            echo form_hidden('task_status_'.$status['id'],$val);
         }
         ?>
      </div>
      <?php $this->load->view('admin/tasks/_table'); ?>
   </div>
   <?php if((get_option('access_tickets_to_none_staff_members') == 1 && !is_staff_member()) || is_staff_member()){ ?>
   <div role="tabpanel" class="tab-pane" id="home_tab_tickets">
      <a href="<?php echo admin_url('tickets'); ?>" class="mbot20 inline-block full-width"><?php echo _l('home_widget_view_all'); ?></a>
      <div class="clearfix"></div>
      <div class="_filters _hidden_inputs hidden tickets_filters">
         <?php
                        // On home only show on hold, open and in progress
         echo form_hidden('ticket_status_1',true);
         echo form_hidden('ticket_status_2',true);
         echo form_hidden('ticket_status_4',true);
         ?>
      </div>
      <?php echo AdminTicketsTableStructure(); ?>
   </div>
   <?php } ?>
   <div role="tabpanel" class="tab-pane" id="home_my_projects">
      <a href="<?php echo admin_url('projects'); ?>" class="mbot20 inline-block full-width"><?php echo _l('home_widget_view_all'); ?></a>
      <div class="clearfix"></div>
      <?php render_datatable(array(
         _l('project_name'),
         _l('project_start_date'),
         _l('project_deadline'),
         _l('project_status'),
         ),'staff-projects'); ?>
      </div>
      <div role="tabpanel" class="tab-pane" id="home_my_reminders">
         <a href="<?php echo admin_url('misc/reminders'); ?>" class="mbot20 inline-block full-width">
            <?php echo _l('home_widget_view_all'); ?>
         </a>
         <?php render_datatable(array(
            _l( 'reminder_related'),
            _l('reminder_description'),
            _l( 'reminder_date'),
            _l( 'options')
            ), 'my-reminders'); ?>
         </div>
         <?php if(is_staff_member()){ ?>
         <div role="tabpanel" class="tab-pane" id="home_announcements">
            <?php if(is_admin()){ ?>
            <a href="<?php echo admin_url('announcements'); ?>" class="mbot20 inline-block full-width"><?php echo _l('home_widget_view_all'); ?></a>
            <div class="clearfix"></div>
            <?php } ?>
            <?php render_datatable(array(_l('announcement_name'),_l('announcement_date_list'),_l('options')),'announcements'); ?>
         </div>
         <?php } ?>
         <?php if(is_admin()){ ?>
         <div role="tabpanel" class="tab-pane" id="home_tab_activity">
            <a href="<?php echo admin_url('utilities/activity_log'); ?>" class="mbot20 inline-block full-width"><?php echo _l('home_widget_view_all'); ?></a>
            <div class="clearfix"></div>
            <div class="activity-feed">
               <?php foreach($activity_log as $log){ ?>
               <div class="feed-item">
                  <div class="date">
                     <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($log['date']); ?>">
                        <?php echo time_ago($log['date']); ?>
                     </span>
                  </div>
                  <div class="text">
                     <?php echo $log['staffid']; ?><br />
                     <?php echo $log['description']; ?>
                  </div>
               </div>
               <?php } ?>
            </div>
         </div>
         <?php } ?>
      </div>
   </div>
</div>
</div>
