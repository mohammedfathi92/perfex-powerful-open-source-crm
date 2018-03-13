<div class="modal-header">
   <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
   <h4 class="modal-title">
      <?php if(isset($lead)){
         if(!empty($lead->name)){
           $name = $lead->name;
         } else if(!empty($lead->company)){
           $name = $lead->company;
         } else {
           $name = _l('lead');
         }
         echo '#'.$lead->id . ' - ' .  $name;
         } else {
         echo _l('add_new',_l('lead'));
         }
         ?>
   </h4>
</div>
<div class="modal-body">
   <?php
      if(isset($lead)){
           if($lead->lost == 1){
              echo '<div class="ribbon danger"><span>'._l('lead_lost').'</span></div>';
           } else if($lead->junk == 1){
              echo '<div class="ribbon warning"><span>'._l('lead_junk').'</span></div>';
           } else {
              if (total_rows('tblclients', array(
                'leadid' => $lead->id))) {
                echo '<div class="ribbon success"><span>'._l('lead_is_client').'</span></div>';
             }
          }
      }
   ?>
  <div class="row">
     <div class="col-md-12">
         <?php if(isset($lead)){
             echo form_hidden('leadid',$lead->id);
         } ?>
   <div class="top-lead-menu">
      <ul class="nav nav-tabs<?php if(!isset($lead)){echo ' lead-new';} ?>" role="tablist">
         <li role="presentation" class="active" >
            <a href="#tab_lead_profile" aria-controls="tab_lead_profile" role="tab" data-toggle="tab">
            <?php echo _l('lead_profile'); ?>
            </a>
         </li>
         <?php if(isset($lead)){ ?>
         <?php if(count($mail_activity) > 0 || isset($show_email_activity) && $show_email_activity){ ?>
         <li role="presentation">
            <a href="#tab_email_activity" aria-controls="tab_email_activity" role="tab" data-toggle="tab">
                <?php echo do_action('lead_email_activity_subject',_l('lead_email_activity')); ?>
            </a>
         </li>
         <?php } ?>
         <li role="presentation">
            <a href="#tab_proposals_leads" onclick="initDataTable('.table-proposals-lead', admin_url + 'proposals/proposal_relations/' + <?php echo $lead->id; ?> + '/lead','undefined', 'undefined','undefined',[6,'DESC']);" aria-controls="tab_proposals_leads" role="tab" data-toggle="tab">
            <?php echo _l('proposals'); ?>
            </a>
         </li>
         <li role="presentation">
            <a href="#tab_tasks_leads" onclick="init_rel_tasks_table(<?php echo $lead->id; ?>,'lead','.table-rel-tasks-leads');" aria-controls="tab_tasks_leads" role="tab" data-toggle="tab">
            <?php echo _l('tasks'); ?>
            </a>
         </li>
         <li role="presentation">
            <a href="#attachments" aria-controls="attachments" role="tab" data-toggle="tab">
            <?php echo _l('lead_attachments'); ?>
            </a>
         </li>
         <li role="presentation">
            <a href="#lead_reminders" onclick="initDataTable('.table-reminders-leads', admin_url + 'misc/get_reminders/' + <?php echo $lead->id; ?> + '/' + 'lead', [4], [4],undefined,[1,'ASC']);" aria-controls="lead_reminders" role="tab" data-toggle="tab">
            <?php echo _l('leads_reminders_tab'); ?>
            <?php
               $total_reminders = total_rows('tblreminders',
                  array(
                     'isnotified'=>0,
                     'staff'=>get_staff_user_id(),
                     'rel_type'=>'lead',
                     'rel_id'=>$lead->id
                     )
                  );
               if($total_reminders > 0){
                  echo '<span class="badge">'.$total_reminders.'</span>';
               }
               ?>
            </a>
         </li>
         <li role="presentation">
            <a href="#lead_notes" aria-controls="lead_notes" role="tab" data-toggle="tab">
            <?php echo _l('lead_add_edit_notes'); ?>
            </a>
         </li>
         <li role="presentation">
            <a href="#lead_activity" aria-controls="lead_activity" role="tab" data-toggle="tab">
            <?php echo _l('lead_add_edit_activity'); ?>
            </a>
         </li>
         <?php } ?>
      </ul>
   </div>
   <!-- Tab panes -->
   <div class="tab-content">
      <!-- from leads modal -->
      <div role="tabpanel" class="tab-pane active" id="tab_lead_profile">
         <?php $this->load->view('admin/leads/profile'); ?>
      </div>
      <?php if(isset($lead)){ ?>
      <?php if(count($mail_activity) > 0 || isset($show_email_activity) && $show_email_activity){ ?>
      <div role="tabpanel" class="tab-pane" id="tab_email_activity">
         <?php do_action('before_lead_email_activity',array('lead'=>$lead,'email_activity'=>$mail_activity)); ?>
         <?php foreach($mail_activity as $_mail_activity){ ?>
         <div class="lead-email-activity">
            <div class="media-left">
               <i class="fa fa-envelope"></i>
            </div>
            <div class="media-body">
               <h4 class="bold no-margin lead-mail-activity-subject">
                  <?php echo $_mail_activity['subject']; ?>
                  <br />
                  <small class="text-muted display-block mtop5 font-medium-xs"><?php echo _dt($_mail_activity['dateadded']); ?></small>
               </h4>
               <div class="lead-mail-activity-body">
                  <hr />
                  <?php echo $_mail_activity['body']; ?>
               </div>
               <hr />
            </div>
         </div>
         <div class="clearfix"></div>
         <?php } ?>
         <?php do_action('after_lead_email_activity',array('lead_id'=>$lead->id,'emails'=>$mail_activity)); ?>
      </div>
      <?php } ?>
      <div role="tabpanel" class="tab-pane" id="lead_activity">
         <div class="panel_s no-shadow">
            <div class="activity-feed">
               <?php foreach($activity_log as $log){ ?>
               <div class="feed-item">
                  <div class="date">
                    <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($log['date']); ?>">
                    <?php echo time_ago($log['date']); ?>
                  </span>
                  </div>
                  <div class="text">
                     <?php if($log['staffid'] != 0){ ?>
                     <a href="<?php echo admin_url('profile/'.$log["staffid"]); ?>">
                     <?php echo staff_profile_image($log['staffid'],array('staff-profile-xs-image pull-left mright5'));
                        ?>
                     </a>
                     <?php
                        }
                        $additional_data = '';
                        if(!empty($log['additional_data'])){
                         $additional_data = unserialize($log['additional_data']);
                         echo ($log['staffid'] == 0) ? _l($log['description'],$additional_data) : $log['full_name'] .' - '._l($log['description'],$additional_data);
                        } else {
                            echo $log['full_name'] . ' - ';
                           if($log['custom_activity'] == 0){
                              echo _l($log['description']);
                           } else {
                              echo _l($log['description'],'',false);
                           }
                        }
                        ?>
                  </div>
               </div>
               <?php } ?>
            </div>
            <div class="col-md-12">
               <?php echo render_textarea('lead_activity_textarea','','',array('placeholder'=>_l('enter_activity')),array(),'mtop15'); ?>
               <div class="text-right">
                  <button id="lead_enter_activity" class="btn btn-info"><?php echo _l('submit'); ?></button>
               </div>
            </div>
            <div class="clearfix"></div>
         </div>
      </div>
      <div role="tabpanel" class="tab-pane" id="tab_proposals_leads">
         <?php if(has_permission('proposals','','create')){ ?>
         <a href="<?php echo admin_url('proposals/proposal?rel_type=lead&rel_id='.$lead->id); ?>" class="btn btn-info mbot25"><?php echo _l('new_proposal'); ?></a>
         <?php } ?>
         <?php if(total_rows('tblproposals',array('rel_type'=>'lead','rel_id'=>$lead->id))> 0 && (has_permission('proposals','','create') || has_permission('proposals','','edit'))){ ?>
         <a href="#" class="btn btn-info mbot25" data-toggle="modal" data-target="#sync_data_proposal_data"><?php echo _l('sync_data'); ?></a>
         <?php $this->load->view('admin/proposals/sync_data',array('related'=>$lead,'rel_id'=>$lead->id,'rel_type'=>'lead')); ?>
         <?php } ?>
         <?php
            $table_data = array(
             _l('proposal') . ' #',
             _l('proposal_subject'),
             _l('proposal_total'),
             _l('proposal_date'),
             _l('proposal_open_till'),
             _l('tags'),
             _l('proposal_date_created'),
             _l('proposal_status'));
            $custom_fields = get_custom_fields('proposal',array('show_on_table'=>1));
            foreach($custom_fields as $field){
             array_push($table_data,$field['name']);
            }
            $table_data = do_action('proposals_relation_table_columns',$table_data);
            render_datatable($table_data,'proposals-lead'); ?>
      </div>
      <div role="tabpanel" class="tab-pane" id="tab_tasks_leads">
         <?php init_relation_tasks_table(array('data-new-rel-id'=>$lead->id,'data-new-rel-type'=>'lead')); ?>
      </div>
      <div role="tabpanel" class="tab-pane" id="lead_reminders">
         <a href="#" data-toggle="modal" class="btn btn-info" data-target=".reminder-modal-lead-<?php echo $lead->id; ?>"><i class="fa fa-bell-o"></i> <?php echo _l('lead_set_reminder_title'); ?></a>
         <hr />
         <?php render_datatable(array( _l( 'reminder_description'), _l( 'reminder_date'), _l( 'reminder_staff'), _l( 'reminder_is_notified'), _l( 'options'), ), 'reminders-leads'); ?>
      </div>
      <div role="tabpanel" class="tab-pane" id="attachments">
         <?php echo form_open('admin/leads/add_lead_attachment',array('class'=>'dropzone mtop15 mbot15','id'=>'lead-attachment-upload')); ?>
         <?php echo form_close(); ?>
         <?php if(get_option('dropbox_app_key') != ''){ ?>
         <hr />
         <div class="text-center">
            <div id="dropbox-chooser-lead"></div>
         </div>
         <?php } ?>
         <?php if(count($lead->attachments) > 0) { ?>
         <div class="mtop20" id="lead_attachments">
            <?php $this->load->view('admin/leads/leads_attachments_template', array('attachments'=>$lead->attachments)); ?>
         </div>
         <?php } ?>
      </div>
      <div role="tabpanel" class="tab-pane" id="lead_notes">
         <?php echo form_open(admin_url('leads/add_note/'.$lead->id),array('id'=>'lead-notes')); ?>
         <div class="form-group">
                <textarea id="lead_note_description" name="lead_note_description" class="form-control" rows="4"></textarea>
         </div>
         <button type="submit" class="btn btn-info pull-right"><?php echo _l('lead_add_edit_add_note'); ?></button>
         <div class="clearfix"></div>
         <div class="lead-select-date-contacted hide">
            <?php echo render_datetime_input('custom_contact_date','lead_add_edit_datecontacted','',array('data-date-end-date'=>date('Y-m-d'))); ?>
         </div>
         <div class="radio radio-primary">
            <input type="radio" name="contacted_indicator" id="contacted_indicator_yes" value="yes">
            <label for="contacted_indicator_yes"><?php echo _l('lead_add_edit_contacted_this_lead'); ?></label>
         </div>
         <div class="radio radio-primary">
            <input type="radio" name="contacted_indicator" id="contacted_indicator_no" value="no" checked>
            <label for="contacted_indicator_no"><?php echo _l('lead_not_contacted'); ?></label>
         </div>
         <?php echo form_close(); ?>
         <hr />
         <div class="panel_s mtop20 no-shadow">
            <?php
               $len = count($notes);
               $i = 0;
               foreach($notes as $note){ ?>
            <div class="media lead-note">
               <a href="<?php echo admin_url('profile/'.$note["addedfrom"]); ?>" target="_blank">
               <?php echo staff_profile_image($note['addedfrom'],array('staff-profile-image-small','pull-left mright10')); ?>
               </a>
               <div class="media-body">
                  <?php if($note['addedfrom'] == get_staff_user_id() || is_admin()){ ?>
                  <a href="#" class="pull-right text-danger" onclick="delete_lead_note(this,<?php echo $note['id']; ?>);return false;"><i class="fa fa fa-times"></i></a>
                  <a href="#" class="pull-right mright5" onclick="toggle_edit_note(<?php echo $note['id']; ?>);return false;"><i class="fa fa-pencil-square-o"></i></a>
                  <?php } ?>
                  <?php if(!empty($note['date_contacted'])){ ?>
                  <span data-toggle="tooltip" data-title="<?php echo _dt($note['date_contacted']); ?>">
                  <i class="fa fa-phone-square text-success font-medium valign" aria-hidden="true"></i>
                  </span>
                  <?php } ?>
                  <small><?php echo _l('lead_note_date_added',_dt($note['dateadded'])); ?></small>
                  <a href="<?php echo admin_url('profile/'.$note["addedfrom"]); ?>" target="_blank">
                     <h5 class="media-heading bold"><?php echo get_staff_full_name($note['addedfrom']); ?></h5>
                  </a>
                  <div data-note-description="<?php echo $note['id']; ?>" class="text-muted">
                     <?php echo app_happy_text($note['description']); ?>
                  </div>
                  <div data-note-edit-textarea="<?php echo $note['id']; ?>" class="hide mtop15">
                     <?php echo render_textarea('note','',$note['description']); ?>
                     <div class="text-right">
                        <button type="button" class="btn btn-default" onclick="toggle_edit_note(<?php echo $note['id']; ?>);return false;"><?php echo _l('cancel'); ?></button>
                        <button type="button" class="btn btn-info" onclick="edit_note(<?php echo $note['id']; ?>);"><?php echo _l('update_note'); ?></button>
                     </div>
                  </div>
               </div>
               <?php if ($i >= 0 && $i != $len - 1) {
                  echo '<hr />';
                  }
                  ?>
            </div>
            <?php $i++; } ?>
         </div>
      </div>
      <?php } ?>
   </div>
     </div>
  </div>
</div>
<?php do_action('lead_modal_profile_bottom',(isset($lead) ? $lead->id : '')); ?>
