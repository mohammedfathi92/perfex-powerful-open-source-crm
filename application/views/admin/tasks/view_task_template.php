<div class="modal-header task-single-header" data-task-single-id="<?php echo $task->id; ?>" data-status="<?php echo $task->status; ?>">
   <?php if($this->input->get('opened_from_lead_id')){ ?>
   <a href="#" onclick="init_lead(<?php echo $this->input->get('opened_from_lead_id'); ?>); return false;" class="back-to-from-task" data-placement="left" data-toggle="tooltip" data-title="<?php echo _l('back_to_lead'); ?>">
      <i class="fa fa-tty" aria-hidden="true"></i>
   </a>
   <?php } ?>
   <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
   <h4 class="modal-title"><?php echo $task->name; ?></h4>
      <?php if($task->billed == 1){ ?>
   <?php  echo '<p class="no-margin">'._l('task_is_billed','<a href="'.admin_url('invoices/list_invoices/'.$task->invoice_id).'" target="_blank">'.format_invoice_number($task->invoice_id)). '</a></p>'; ?>
   <?php } ?>
   <?php if($task->is_public == 0){ ?>
   <small class="no-margin">
      <?php echo _l('task_is_private'); ?>
      <?php if(has_permission('tasks','','edit')) { ?> -
      <a href="#" onclick="make_task_public(<?php echo $task->id; ?>); return false;">
         <?php echo _l('task_view_make_public'); ?>
      </a>
      <?php } ?>
   </small>
   <br />
   <?php } ?>
</div>
<div class="modal-body">
   <div class="row">
      <div class="col-md-8 task-single-col-left">
         <?php if(!empty($task->rel_id)){
            echo '<div class="task-single-related-wrapper">';
            $task_rel_data = get_relation_data($task->rel_type,$task->rel_id);
            $task_rel_value = get_relation_values($task_rel_data,$task->rel_type);
            echo '<h4 class="bold font-medium mbot15">'._l('task_single_related').': <a href="'.$task_rel_value['link'].'" target="_blank">'.$task_rel_value['name'].'</a>';
            if($task->rel_type == 'project' && $task->milestone != 0){
             echo '<div class="mtop5 mbot20 font-normal">' . _l('task_milestone') . ': ';
             $milestones = get_project_milestones($task->rel_id);
             if(has_permission('tasks','','edit') && count($milestones) > 1){ ?>
             <span class="task-single-menu task-menu-milestones">
               <span class="trigger pointer manual-popover text-has-action">
                  <?php echo $task->milestone_name; ?>
               </span>
               <span class="content-menu hide">
                  <ul>
                     <?php
                     foreach($milestones as $milestone){ ?>
                     <?php if($task->milestone != $milestone['id']){ ?>
                     <li>
                        <a href="#" onclick="task_change_milestone(<?php echo $milestone['id']; ?>,<?php echo $task->id; ?>); return false;">
                           <?php echo $milestone['name']; ?>
                        </a>
                     </li>
                     <?php } ?>
                     <?php } ?>
                  </ul>
               </span>
            </span>
            <?php } else {
               echo $task->milestone_name;
            }
            echo '</div>';
         }
         echo '</h4>';
         echo '</div>';
      } ?>
      <div class="clearfix"></div>
      <?php if($task->status != 5 && ($task->current_user_is_assigned || is_admin() || $task->current_user_is_creator)){ ?>
      <p class="no-margin pull-left" style="<?php echo 'margin-'.(is_rtl() ? 'left' : 'right').':5px !important'; ?>">
         <a href="#" class="btn btn-info" id="task-single-mark-complete-btn" autocomplete="off" data-loading-text="<?php echo _l('wait_text'); ?>" onclick="mark_complete(<?php echo $task->id; ?>); return false;" data-toggle="tooltip" title="<?php echo _l('task_single_mark_as_complete'); ?>">
            <i class="fa fa-check"></i>
         </a>
      </p>
      <?php } else if($task->status == 5 && ($task->current_user_is_assigned || is_admin() || $task->current_user_is_creator)){ ?>
      <p class="no-margin pull-left" style="<?php echo 'margin-'.(is_rtl() ? 'left' : 'right').':5px !important'; ?>">
         <a href="#" class="btn btn-default" id="task-single-unmark-complete-btn" autocomplete="off" data-loading-text="<?php echo _l('wait_text'); ?>" onclick="unmark_complete(<?php echo $task->id; ?>); return false;" data-toggle="tooltip" title="<?php echo _l('task_unmark_as_complete'); ?>">
            <i class="fa fa-check"></i>
         </a>
      </p>
      <?php } ?>
      <?php if(has_permission('tasks','','create')){ ?>
      <p class="no-margin pull-left mright5">
         <a href="#" class="btn btn-default mright5" data-toggle="tooltip" data-title="<?php echo _l('task_statistics'); ?>" onclick="task_tracking_stats(<?php echo $task->id; ?>); return false;">
            <i class="fa fa-bar-chart"></i>
         </a>
      </p>
      <?php } ?>
      <p class="no-margin pull-left mright5">
         <a href="#" class="btn btn-default mright5" data-toggle="tooltip" data-title="<?php echo _l('task_timesheets'); ?>"onclick="slideToggle('#task_single_timesheets'); return false;">
            <i class="fa fa-th-list"></i>
         </a>
      </p>
      <?php if($task->billed == 0){
         $is_assigned = $task->current_user_is_assigned;
         if(!$this->tasks_model->is_timer_started($task->id)) { ?>
         <p class="no-margin pull-left"<?php if(!$is_assigned){ ?> data-toggle="tooltip" data-title="<?php echo _l('task_start_timer_only_assignee'); ?>"<?php } ?>>
            <a href="#" class="mbot10 btn<?php if(!$is_assigned || $task->status == 5){echo ' disabled btn-default';}else {echo ' btn-success';} ?>" onclick="timer_action(this,<?php echo $task->id; ?>); return false;">
               <i class="fa fa-clock-o"></i> <?php echo _l('task_start_timer'); ?>
            </a>
         </p>
         <?php } else { ?>
         <p class="no-margin pull-left">
            <a href="#" data-toggle="popover" data-html="true" data-trigger="manual" data-title="<?php echo _l('note'); ?>" data-content='<?php echo render_textarea('timesheet_note'); ?><button type="button" onclick="timer_action(this,<?php echo $task->id; ?>,<?php echo $this->tasks_model->get_last_timer($task->id)->id; ?>);" class="btn btn-info btn-xs"><?php echo _l('save'); ?></button>' class="btn mbot10 btn-danger<?php if(!$is_assigned){echo ' disabled';} ?>" onclick="return false;">
               <i class="fa fa-clock-o"></i> <?php echo _l('task_stop_timer'); ?>
            </a>
         </p>
         <?php } ?>
         <?php } ?>
         <div class="clearfix"></div>
         <?php if(total_rows('tbltaskstimers',array('end_time'=>NULL,'staff_id !='=>get_staff_user_id(),'task_id'=>$task->id)) > 0){
            $startedTimers = $this->tasks_model->get_timers($task->id,array('staff_id !='=>get_staff_user_id(),'end_time'=>NULL));
            $usersWorking = '';
            foreach($startedTimers as $t){
              $usersWorking .= get_staff_full_name($t['staff_id']) . ', ';
           }
           $usersWorking = rtrim($usersWorking, ', ');
           ?>
           <p class="no-margin"><?php echo _l((count($startedTimers) == 1 ? 'task_users_working_on_tasks_single' : 'task_users_working_on_tasks_multiple'),$usersWorking); ?></p>
           <?php } ?>
           <hr />
           <div id="task_single_timesheets" class="<?php if(!$this->session->flashdata('task_single_timesheets_open')){echo 'hide';} ?>">
            <div class="table-responsive">
               <table class="table">
                  <thead>
                     <tr>
                        <th><?php echo _l('timesheet_user'); ?></th>
                        <th><?php echo _l('timesheet_start_time'); ?></th>
                        <th><?php echo _l('timesheet_end_time'); ?></th>
                        <th><?php echo _l('timesheet_time_spend'); ?></th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php
                     $timers_found = false;
                     foreach($task->timesheets as $timesheet){ ?>
                     <?php if(has_permission('tasks','','edit') || has_permission('tasks','','create') || has_permission('tasks','','delete') || $timesheet['staff_id'] == get_staff_user_id()){
                        $timers_found = true; ?>
                        <tr>
                           <td>
                              <?php if($timesheet['note']){
                                 echo '<i class="fa fa-comment" data-html="true" data-placement="right" data-toggle="tooltip" data-title="'.htmlspecialchars($timesheet['note'],ENT_COMPAT).'"></i>';
                              }
                              ?>
                              <a href="<?php echo admin_url('staff/profile/' . $timesheet['staff_id']); ?>" target="_blank">
                                 <?php echo $timesheet['full_name']; ?></a>
                              </td>
                              <td><?php echo _dt($timesheet['start_time'],true); ?></td>
                              <td>
                                 <?php if($timesheet['end_time'] !== NULL){
                                    echo _dt($timesheet['end_time'],true);
                                 }
                                 ?>
                              </td>
                              <td>
                                 <?php
                                 if(!$task_is_billed){
                                  echo '<a href="'.admin_url('tasks/delete_timesheet/'.$timesheet['id']).'" class="task-single-delete-timesheet pull-right text-danger mtop5" data-task-id="'.$task->id.'"><i class="fa fa-remove"></i></a>';
                               }
                               if($timesheet['time_spent'] == NULL){
                                  echo _l('time_h').': '. seconds_to_time_format(time() - $timesheet['start_time']).'<br />';
                                  echo _l('time_decimal').': '. sec2qty(time() - $timesheet['start_time']).'<br />';
                               } else {
                                  echo _l('time_h').': '. seconds_to_time_format($timesheet['time_spent']).'<br />';
                                  echo _l('time_decimal').': '. sec2qty($timesheet['time_spent']).'<br />';
                               }
                               ?>
                            </td>
                         </tr>
                         <?php } ?>
                         <?php } ?>
                         <?php if($timers_found == false){ ?>
                         <tr>
                           <td colspan="5" class="text-center bold"><?php echo _l('no_timers_found'); ?></td>
                        </tr>
                        <?php } ?>
                        <?php if($task->billed == 0 && ($is_assigned || (count($task->assignees) > 0 && is_admin())) && $task->status != 5){
                           ?>
                           <tr class="odd">
                              <td colspan="5">
                                 <div class="col-md-6">
                                    <?php echo render_datetime_input('timesheet_start_time','task_log_time_start'); ?>
                                 </div>
                                 <div class="col-md-6">
                                    <?php echo render_datetime_input('timesheet_end_time','task_log_time_end'); ?>
                                 </div>
                                 <div class="col-md-12">
                                    <div class="form-group">
                                       <label class="control-label">
                                          <?php echo _l('task_single_log_user'); ?>
                                       </label>
                                       <br />
                                       <select name="single_timesheet_staff_id" class="selectpicker" data-width="100%">
                                          <?php foreach($task->assignees as $assignee){
                                             if((!has_permission('tasks','','create') && !has_permission('tasks','','edit') && $assignee['assigneeid'] != get_staff_user_id()) || ($task->rel_type == 'project' && !has_permission('projects','','edit') && $assignee['assigneeid'] != get_staff_user_id())){continue;}
                                             $selected = '';
                                             if($assignee['assigneeid'] == get_staff_user_id()){
                                              $selected = ' selected';
                                           }
                                           ?>
                                           <option<?php echo $selected; ?> value="<?php echo $assignee['assigneeid']; ?>" >
                                           <?php echo $assignee['full_name']; ?>
                                        </option>
                                        <?php } ?>
                                     </select>
                                  </div>
                                  <?php echo render_textarea('task_single_timesheet_note','note'); ?>
                               </div>
                               <div class="col-md-12 text-right">
                                 <?php
                                 $disable_button = '';
                                 if($this->tasks_model->is_timer_started_for_task($task->id,array('staff_id'=>get_staff_user_id()))){
                                  $disable_button = 'disabled ';
                                  echo '<div class="text-right mbot15 text-danger">'. _l('add_task_timer_started_warning') . '</div>';
                               }
                               ?>
                               <button <?php echo $disable_button; ?>data-task-id="<?php echo $task->id; ?>" class="btn btn-success task-single-add-timesheet"><i class="fa fa-plus"></i> <?php echo _l('submit'); ?></button>
                            </div>
                         </td>
                      </tr>
                      <?php } ?>
                   </tbody>
                </table>
             </div>
             <hr />
          </div>
          <div class="clearfix"></div>
          <h4 class="th font-medium mbot15 pull-left"><?php echo _l('task_view_description'); ?></h4>
          <?php if(has_permission('tasks','','edit')){ ?><a href="#" onclick="edit_task_inline_description(this,<?php echo $task->id; ?>); return false;" class="pull-left mtop10 mleft5 font-medium-xs"><i class="fa fa-pencil-square-o"></i></a>
          <?php } ?>
          <div class="clearfix"></div>
          <?php if(!empty($task->description)){
            echo '<div class="tc-content"><div id="task_view_description">' .check_for_links($task->description) .'</div></div>';
         } else {
            echo '<div class="no-margin tc-content task-no-description" id="task_view_description"><span class="text-muted">' . _l('task_no_description') . '</span></div>';
         } ?>
         <div class="clearfix"></div>
         <hr />
         <a href="#" onclick="add_task_checklist_item('<?php echo $task->id; ?>'); return false" class="mbot10 inline-block">
            <span class="new-checklist-item"><i class="fa fa-plus-circle"></i>
               <?php echo _l('add_checklist_item'); ?>
            </span>
         </a>
         <div class="form-group checklist-templates-wrapper simple-bootstrap-select task-single-checklist-templates<?php if(count($checklistTemplates) == 0){echo ' hide';}  ?>">
            <select id="checklist_items_templates" class="selectpicker checklist-items-template-select" data-none-selected-text="<?php echo _l('insert_checklist_templates') ?>" data-width="100%" data-live-search="true">
               <option value=""></option>
               <?php foreach($checklistTemplates as $chkTemplate){ ?>
               <option value="<?php echo $chkTemplate['id']; ?>">
                  <?php echo $chkTemplate['description']; ?>
               </option>
               <?php } ?>
            </select>
         </div>
         <div class="clearfix"></div>
         <p class="hide text-muted no-margin" id="task-no-checklist-items"><?php echo _l('task_no_checklist_items_found'); ?></p>
         <div class="row checklist-items-wrapper">
            <div class="col-md-12 ">
               <div id="checklist-items">
                  <?php $this->load->view('admin/tasks/checklist_items_template',
                     array(
                       'task_id'=>$task->id,
                       'checklists'=>$task->checklist_items)); ?>
                    </div>
                 </div>
                 <div class="clearfix"></div>
              </div>
              <?php if(count($task->attachments) > 0){ ?>
              <div class="row task_attachments_wrapper">
               <div class="col-md-12" id="attachments">
                  <hr />
                  <h4 class="th font-medium mbot15"><?php echo _l('task_view_attachments'); ?></h4>
                  <div class="row">
                     <?php
                     $i = 1;
                                       // Store all url related data here
                     $attachments_data = array();
                     $show_more_link_task_attachments = do_action('show_more_link_task_attachments',2);
                     foreach($task->attachments as $attachment){ ?>
                     <div data-commentid="<?php echo $attachment['comment_file_id']; ?>" data-task-attachment-id="<?php echo $attachment['id']; ?>" class="task-attachment-col col-md-6<?php if($i > $show_more_link_task_attachments){echo ' hide task-attachment-col-more';} ?>">
                        <ul class="list-unstyled task-attachment-wrapper">
                           <li class="mbot10 task-attachment<?php if(strtotime($attachment['dateadded']) >= strtotime('-16 hours')){echo ' highlight-bg'; } ?>">
                              <div class="mbot10 pull-right task-attachment-user">
                                 <?php if($attachment['staffid'] == get_staff_user_id() || is_admin()){ ?>
                                 <a href="#" class="pull-right" onclick="remove_task_attachment(this,<?php echo $attachment['id']; ?>); return false;">
                                    <i class="fa fa fa-times"></i>
                                 </a>
                                 <?php }
                                 $externalPreview = false;
                                 $is_image = false;
                                 $path = get_upload_path_by_type('task') . $task->id . '/'. $attachment['file_name'];
                                 $href_url = site_url('download/file/taskattachment/'. $attachment['id']);
                                 $isHtml5Video = is_html5_video($path);
                                 if(empty($attachment['external'])){
                                  $is_image = is_image($path);
                                  $img_url = site_url('download/preview_image?path='.protected_file_url_by_path($path,true).'&type='.$attachment['filetype']);
                               } else if((!empty($attachment['thumbnail_link']) || !empty($attachment['external']))
                                && !empty($attachment['thumbnail_link'])){
                                $is_image = true;
                                $img_url = optimize_dropbox_thumbnail($attachment['thumbnail_link']);
                                $externalPreview = $img_url;
                                $href_url = $attachment['external_link'];
                             } else if(!empty($attachment['external']) && empty($attachment['thumbnail_link'])) {
                                $href_url = $attachment['external_link'];
                             }
                             if(!empty($attachment['external']) && $attachment['external'] == 'dropbox' && $is_image){ ?>
                             <a href="<?php echo $href_url; ?>" target="_blank" class="" data-toggle="tooltip" data-title="<?php echo _l('open_in_dropbox'); ?>"><i class="fa fa-dropbox" aria-hidden="true"></i></a>
                             <?php }
                             if($attachment['staffid'] != 0){
                               echo '<a href="'.admin_url('profile/'.$attachment['staffid']).'" target="_blank">'.get_staff_full_name($attachment['staffid']) .'</a> - ';
                            } else if($attachment['contact_id'] != 0) {
                               echo '<a href="'.admin_url('clients/client/'.get_user_id_by_contact_id($attachment['contact_id']).'?contactid='.$attachment['contact_id']).'" target="_blank">'.get_contact_full_name($attachment['contact_id']) .'</a> - ';
                            }
                            echo '<span class="text-has-action" data-toggle="tooltip" data-title="'._dt($attachment['dateadded']).'">'.time_ago($attachment['dateadded']).'</span>';
                            ?>
                         </div>
                         <div class="clearfix"></div>
                         <?php ob_start(); ?>
                         <div class="<?php if($is_image){echo 'preview-image';}else if(!$isHtml5Video){echo 'task-attachment-no-preview';} ?>">
                           <?php
                                 // Not link on video previews because on click on the video is opening new tab
                           if(!$isHtml5Video){ ?>
                           <a href="<?php echo (!$externalPreview ? $href_url : $externalPreview); ?>" target="_blank"<?php if($is_image){ ?> data-lightbox="task-attachment"<?php } ?> class="<?php if($isHtml5Video){echo 'video-preview';} ?>">
                              <?php } ?>
                              <?php if($is_image){ ?>
                              <img src="<?php echo $img_url; ?>" class="img img-responsive">
                              <?php } else if($isHtml5Video) { ?>
                              <video width="100%" height="100%" src="<?php echo site_url('download/preview_video?path='.protected_file_url_by_path($path).'&type='.$attachment['filetype']); ?>" controls>
                                 Your browser does not support the video tag.
                              </video>
                              <?php } else { ?>
                              <i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i>
                              <?php echo $attachment['file_name']; ?>
                              <?php } ?>
                              <?php if(!$isHtml5Video){ ?>
                           </a>
                           <?php } ?>
                        </div>
                        <?php
                        $attachments_data[$attachment['id']] = ob_get_contents();
                        ob_end_clean();
                        echo $attachments_data[$attachment['id']];
                        ?>
                        <div class="clearfix"></div>
                     </li>
                  </ul>
               </div>
               <?php
               $i++;
            } ?>
         </div>
      </div>
      <div class="clearfix"></div>
      <?php if(($i - 1) > $show_more_link_task_attachments){ ?>
      <div class="col-md-12" id="show-more-less-task-attachments-col">
         <a href="#" class="task-attachments-more" onclick="slideToggle('.task-attachment-col-more',task_attachments_toggle); return false;"><?php echo _l('show_more'); ?></a>
         <a href="#" class="task-attachments-less hide" onclick="slideToggle('.task-attachment-col-more',task_attachments_toggle); return false;"><?php echo _l('show_less'); ?></a>
      </div>
      <?php } ?>
   </div>
   <?php } ?>
   <hr />
   <a href="#" id="taskCommentSlide" onclick="slideToggle('.tasks-comments'); return false;" >
      <h4 class="mbot20 font-medium"><?php echo _l('task_comments'); ?></h4>
   </a>
   <div class="tasks-comments inline-block full-width simple-editor" <?php if(count($task->comments) == 0){echo 'style="display:none"';} ?>>
      <textarea name="comment" placeholder="<?php echo _l('task_single_add_new_comment'); ?>" id="task_comment" rows="3" class="form-control ays-ignore"></textarea>
      <button type="button" class="btn btn-info mtop30 pull-right" autocomplete="off" data-loading-text="<?php echo _l('wait_text'); ?>" onclick="add_task_comment('<?php echo $task->id; ?>');">
         <?php echo _l('task_single_add_new_comment'); ?>
      </button>
      <div class="clearfix"></div>
      <?php if(count($task->comments) > 0){echo '<hr />';} ?>
      <div id="task-comments" class="mtop10">
         <?php
         $comments = '';
         $len = count($task->comments);
         $i = 0;
         foreach ($task->comments as $comment) {
           $comments .= '<div id="comment_'.$comment['id'].'" data-commentid="' . $comment['id'] . '" data-task-attachment-id="'.$comment['file_id'].'" class="tc-content task-comment'.(strtotime($comment['dateadded']) >= strtotime('-16 hours') ? ' highlight-bg' : '').'">';
           $comments .= '<a data-task-comment-href-id="'.$comment['id'].'" href="'.admin_url('tasks/view/'.$task->id).'#comment_'.$comment['id'].'" class="task-date-as-comment-id"><small><span class="text-has-action inline-block mbot5" data-toggle="tooltip" data-title="'._dt($comment['dateadded']).'">' . time_ago($comment['dateadded']) . '</span></small></a>';
           if($comment['staffid'] != 0){
            $comments .= '<a href="' . admin_url('profile/' . $comment['staffid']) . '" target="_blank">' . staff_profile_image($comment['staffid'], array(
             'staff-profile-image-small',
             'media-object img-circle pull-left mright10'
          )) . '</a>';
         } elseif($comment['contact_id'] != 0) {
            $comments .= '<img src="'.contact_profile_image_url($comment['contact_id']).'" class="client-profile-image-small media-object img-circle pull-left mright10">';
         }
         if ($comment['staffid'] == get_staff_user_id() || is_admin()) {
            $comment_added = strtotime($comment['dateadded']);
            $minus_1_hour = strtotime('-1 hours');
            if(get_option('client_staff_add_edit_delete_task_comments_first_hour') == 0 || (get_option('client_staff_add_edit_delete_task_comments_first_hour') == 1 && $comment_added >= $minus_1_hour) || is_admin()){
              $comments .= '<span class="pull-right"><a href="#" onclick="remove_task_comment(' . $comment['id'] . '); return false;"><i class="fa fa-times text-danger"></i></span></a>';
              $comments .= '<span class="pull-right mright5"><a href="#" onclick="edit_task_comment(' . $comment['id'] . '); return false;"><i class="fa fa-pencil-square-o"></i></span></a>';
           }
        }
        $comments .= '<div class="media-body">';
        if($comment['staffid'] != 0){
          $comments .= '<a href="' . admin_url('profile/' . $comment['staffid']) . '" target="_blank">' . $comment['staff_full_name'] . '</a> <br />';
       } elseif($comment['contact_id'] != 0) {
          $comments .= '<span class="label label-info mtop5 mbot5 inline-block">'._l('is_customer_indicator').'</span><br /><a href="' . admin_url('clients/client/'.get_user_id_by_contact_id($comment['contact_id']) .'?contactid='.$comment['contact_id'] ) . '" class="pull-left" target="_blank">' . get_contact_full_name($comment['contact_id']) . '</a> <br />';
       }
       $comments .= '<div data-edit-comment="'.$comment['id'].'" class="hide edit-task-comment"><textarea rows="5" id="task_comment_'.$comment['id'].'" class="ays-ignore">'.$comment['content'].'</textarea>
       <div class="clearfix mtop20"></div>
       <button type="button" class="btn btn-info pull-right" onclick="save_edited_comment('.$comment['id'].','.$task->id.')">'._l('submit').'</button>
       <button type="button" class="btn btn-default pull-right mright5" onclick="cancel_edit_comment('.$comment['id'].')">'._l('cancel').'</button>
       </div>';
       if($comment['file_id'] != 0){
        $comment['content'] = str_replace('[task_attachment]',$attachments_data[$comment['file_id']],$comment['content']);
                           // Replace lightbox to prevent loading the image twice
        $comment['content'] = str_replace('data-lightbox="task-attachment"','data-lightbox="task-attachment-comment"',$comment['content']);
     }
     $comments .= '<div class="comment-content mtop10">'.app_happy_text(check_for_links($comment['content'])) . '</div>';
     $comments .= '</div>';
     if ($i >= 0 && $i != $len - 1) {
        $comments .= '<hr class="task-info-separator" />';
     }
     $comments .= '</div>';
     $i++;
  }
  echo $comments;
  ?>
</div>
</div>
</div>
<div class="col-md-4 task-single-col-right">
   <div class="pull-right mbot10 task-single-menu task-menu-options">
      <div class="content-menu hide">
         <ul>
            <?php if(has_permission('tasks','','edit')) { ?>
            <li>
               <a href="#" onclick="edit_task(<?php echo $task->id; ?>); return false;">
                  <?php echo _l('task_single_edit'); ?>
               </a>
            </li>
            <?php } ?>
            <?php if(has_permission('tasks','','create')){ ?>
            <?php
            $copy_template = "";
            if(count($task->assignees) > 0){
              $copy_template .= "<div class='checkbox checkbox-primary'><input type='checkbox' name='copy_task_assignees' id='copy_task_assignees' checked><label for='copy_task_assignees'>"._l('task_single_assignees')."</label></div>";
           }
           if(count($task->followers) > 0){
            $copy_template .= "<div class='checkbox checkbox-primary'><input type='checkbox' name='copy_task_followers' id='copy_task_followers' checked><label for='copy_task_followers'>"._l('task_single_followers')."</label></div>";
         }
         if(count($task->checklist_items) > 0){
            $copy_template .= "<div class='checkbox checkbox-primary'><input type='checkbox' name='copy_task_checklist_items' id='copy_task_checklist_items' checked><label for='copy_task_checklist_items'>"._l('task_checklist_items')."</label></div>";
         }
         if(count($task->attachments) > 0){
            $copy_template .= "<div class='checkbox checkbox-primary'><input type='checkbox' name='copy_task_attachments' id='copy_task_attachments'><label for='copy_task_attachments'>"._l('task_view_attachments')."</label></div>";
         }

         $copy_template .= "<p>"._l('task_status')."</p>";
         $task_copy_statuses = do_action('task_copy_statuses',$task_statuses);
         foreach($task_copy_statuses as $copy_status){
            $copy_template .= "<div class='radio radio-primary'><input type='radio' value='".$copy_status['id']."' name='copy_task_status' id='copy_task_status_".$copy_status['id']."'".($copy_status['id'] == do_action('copy_task_default_status',1) ? ' checked' : '')."><label for='copy_task_status_".$copy_status['id']."'>".$copy_status['name']."</label></div>";
         }

         $copy_template .= "<div class='text-center'>";
         $copy_template .= "<button type='button' data-task-copy-from='".$task->id."' class='btn btn-success copy_task_action'>"._l('copy_task_confirm')."</button>";
         $copy_template .= "</div>";
         ?>
         <li> <a href="#" onclick="return false;" data-placement="bottom" data-toggle="popover" data-content="<?php echo htmlspecialchars($copy_template); ?>" data-html="true"><?php echo _l('task_copy'); ?></span></a></li>
         <?php } ?>
         <?php if(has_permission('tasks','','delete')){ ?>
         <li>
            <a href="<?php echo admin_url('tasks/delete_task/'.$task->id); ?>" class="_delete task-delete">
               <?php echo _l('task_single_delete'); ?>
            </a>
         </li>
         <?php } ?>
      </ul>
   </div>
   <?php if(has_permission('tasks','','delete') || has_permission('tasks','','edit') || has_permission('tasks','','create')){ ?>
   <a href="#" onclick="return false;" class="trigger manual-popover mright5">
      <i class="fa fa-circle-thin" aria-hidden="true"></i>
      <i class="fa fa-circle-thin" aria-hidden="true"></i>
      <i class="fa fa-circle-thin" aria-hidden="true"></i>
   </a>
   <?php } ?>
</div>
<h4 class="task-info-heading"><?php echo _l('task_info'); ?>
<?php
  if($task->recurring == 1){
     echo '<span class="label label-info inline-block mleft5">'._l('recurring_task').'</span>';
  }
?>
</h4>
<div class="clearfix"></div>
<h5 class="no-mtop task-info-created">
   <?php if(($task->addedfrom != 0 && $task->addedfrom != get_staff_user_id()) || $task->is_added_from_contact == 1){ ?>
   <small class="text-dark"><?php echo _l('task_created_by','<span class="text-dark">'.($task->is_added_from_contact == 0 ? get_staff_full_name($task->addedfrom) : get_contact_full_name($task->addedfrom)).'</span>'); ?> <i class="fa fa-clock-o" data-toggle="tooltip" data-title="<?php echo _l('task_created_at',_dt($task->dateadded)); ?>"></i></small>
   <br />
   <?php } else { ?>
   <small class="text-dark"><?php echo _l('task_created_at','<span class="text-dark">'._dt($task->dateadded).'</span>'); ?></small>
   <?php } ?>
</h5>
<hr class="task-info-separator" />
<div class="task-info task-status">
   <h5>
      <i class="fa fa-<?php if($task->status == 5){echo 'star';} else if($task->status == 1){echo 'star-o';} else {echo 'star-half-o';} ?> pull-left task-info-icon fa-fw fa-lg"></i><?php echo _l('task_status'); ?>:
      <?php if($task->current_user_is_assigned || $task->current_user_is_creator || has_permission('tasks','','edit')) { ?>
      <span class="task-single-menu task-menu-status">
         <span class="trigger pointer manual-popover text-has-action">
            <?php echo format_task_status($task->status,true); ?>
         </span>
         <span class="content-menu hide">
            <ul>
               <?php
               $task_single_mark_as_statuses = do_action('task_single_mark_as_statuses',$task_statuses);
               foreach($task_single_mark_as_statuses as $status){ ?>
               <?php if($task->status != $status['id']){ ?>
               <li>
                  <a href="#" onclick="task_mark_as(<?php echo $status['id']; ?>,<?php echo $task->id; ?>); return false;">
                     <?php echo _l('task_mark_as',$status['name']); ?>
                  </a>
               </li>
               <?php } ?>
               <?php } ?>
            </ul>
         </span>
      </span>
      <?php } else { ?>
      <?php echo format_task_status($task->status,true); ?>
      <?php } ?>
   </h5>
</div>
<?php if($task->status == 5){ ?>
<div class="task-info">
   <h5><i class="fa task-info-icon fa-fw fa-lg pull-left fa-check"></i>
      <?php echo _l('task_single_finished'); ?>: <span data-toggle="tooltip" data-title="<?php echo _dt($task->datefinished); ?>" data-placement="bottom" class="text-has-action"><?php echo time_ago($task->datefinished); ?></span>
   </h5>
</div>
<?php } ?>
<div class="task-info task-single-inline-wrap">
   <h5><i class="fa task-info-icon fa-fw fa-lg fa-calendar-plus-o pull-left fa-margin"></i>
      <?php echo _l('task_single_start_date'); ?>:
      <?php if(has_permission('tasks','','edit') && $task->status !=5) { ?>
      <input name="startdate" value="<?php echo _d($task->startdate); ?>" id="task-single-startdate" class="task-info-inline-input-edit datepicker pointer task-single-inline-field">
      <?php } else { ?>
      <?php echo _d($task->startdate); ?>
      <?php } ?>
   </h5>
</div>
<div class="task-info task-single-inline-wrap<?php if(!$task->duedate && !has_permission('edit','','tasks')){echo ' hide';} ?>"<?php if(!$task->duedate){ echo ' style="opacity:0.5;"';} ?>>
   <h5><i class="fa fa-calendar-check-o task-info-icon fa-fw fa-lg pull-left"></i>
      <?php echo _l('task_single_due_date'); ?>:
      <?php if(has_permission('tasks','','edit') && $task->status !=5) { ?>
      <input name="duedate" value="<?php echo _d($task->duedate); ?>" id="task-single-duedate" class="task-info-inline-input-edit datepicker pointer task-single-inline-field">
      <?php } else { ?>
      <?php echo _d($task->duedate); ?>
      <?php } ?>
   </h5>
</div>
<div class="task-info">
   <h5>
      <i class="fa task-info-icon fa-fw fa-lg pull-left fa-bolt"></i>
      <?php echo _l('task_single_priority'); ?>:
      <?php if(has_permission('tasks','','edit') && $task->status != 5) { ?>
      <span class="task-single-menu task-menu-priority">
         <span class="trigger pointer manual-popover text-has-action" style="color:<?php echo task_priority_color($task->priority); ?>;">
            <?php echo task_priority($task->priority); ?>
         </span>
         <span class="content-menu hide">
            <ul>
               <?php
               foreach(get_tasks_priorities() as $priority){ ?>
               <?php if($task->priority != $priority['id']){ ?>
               <li>
                  <a href="#" onclick="task_change_priority(<?php echo $priority['id']; ?>,<?php echo $task->id; ?>); return false;">
                     <?php echo $priority['name']; ?>
                  </a>
               </li>
               <?php } ?>
               <?php } ?>
            </ul>
         </span>
      </span>
      <?php } else { ?>
      <span style="color:<?php echo task_priority_color($task->priority); ?>;">
         <?php echo task_priority($task->priority); ?>
      </span>
      <?php } ?>
   </h5>
</div>
<?php if((has_permission('tasks','','create') || has_permission('tasks','','edit'))){ ?>
<div class="task-info">
   <h5><i class="fa task-info-icon fa-fw fa-lg pull-left fa-clock-o"></i>
      <?php echo _l('task_hourly_rate'); ?>: <?php if($task->rel_type == 'project' && $task->project_data->billing_type == 2){
         echo _format_number($task->project_data->project_rate_per_hour);
      } else {
         echo _format_number($task->hourly_rate);
      }
      ?>
   </h5>
</div>
<div class="task-info">
   <h5><i class="fa task-info-icon fa-fw fa-lg pull-left fa fa-credit-card"></i>
      <?php echo _l('task_billable'); ?>: <?php echo ($task->billable == 1 ? _l('task_billable_yes') : _l('task_billable_no')) ?>
      <?php if($task->billable == 1){ ?>
      - <?php echo ($task->billed == 1 ? _l('task_billed_yes') : _l('task_billed_no')) ?>
      <?php } ?>
      <?php if($task->rel_type == 'project' && $task->project_data->billing_type == 1){echo '<small>('._l('project'). ' ' . _l('project_billing_type_fixed_cost').')</small>';} ?>
   </h5>
</div>
<?php } ?>
<?php if($task->current_user_is_assigned || total_rows('tbltaskstimers',array('task_id'=>$task->id,'staff_id'=>get_staff_user_id())) > 0){ ?>
<div class="task-info">
   <h5>
      <i class="fa fa-asterisk task-info-icon fa-fw fa-lg" aria-hidden="true"></i><?php echo _l('task_user_logged_time'); ?>
      <?php echo seconds_to_time_format($this->tasks_model->calc_task_total_time($task->id,' AND staff_id='.get_staff_user_id())); ?>
   </h5>
</div>
<?php } ?>
<?php if(has_permission('tasks','','create')){ ?>
<div class="task-info">
   <h5>
      <i class="fa task-info-icon fa-fw fa-lg fa-clock-o"></i><?php echo _l('task_total_logged_time'); ?>
      <span class="text-success">
         <?php echo seconds_to_time_format($this->tasks_model->calc_task_total_time($task->id)); ?>
      </span>
   </h5>
</div>
<?php } ?>
<?php $custom_fields = get_custom_fields('tasks');
foreach($custom_fields as $field){ ?>
<?php $value = get_custom_field_value($task->id,$field['id'],'tasks');
if($value == ''){continue;}?>
<div class="task-info">
   <h5 class="task-info-custom-field">
     <?php echo $field['name']; ?>: <?php echo $value; ?>
   </h5>
</div>
<?php } ?>

<?php if(has_permission('tasks','','create') || has_permission('tasks','','edit')){ ?>
<div class="mtop5 clearfix"></div>
<div id="inputTagsWrapper" class="taskSingleTasks">
   <input type="text" class="tagsinput" id="taskTags" data-taskid="<?php echo $task->id; ?>" value="<?php echo prep_tags_input(get_tags_in($task->id,'task')); ?>" data-role="tagsinput">
</div>
<div class="clearfix"></div>
<?php } else { ?>
<div class="mtop5 clearfix"></div>
<?php echo render_tags(get_tags_in($task->id,'task')); ?>
<div class="clearfix"></div>
<?php } ?>
<hr class="task-info-separator" />
<div class="clearfix"></div>
<?php if($task->current_user_is_assigned){
   foreach($task->assignees as $assignee){
     if($assignee['assigneeid'] == get_staff_user_id() && get_staff_user_id() != $assignee['assigned_from'] && $assignee['assigned_from'] != 0 || $assignee['is_assigned_from_contact'] == 1){
      if($assignee['is_assigned_from_contact'] == 0){
        echo '<p class="text-muted task-assigned-from">'._l('task_assigned_from','<a href="'.admin_url('profile/'.$assignee['assigned_from']).'" target="_blank">'.get_staff_full_name($assignee['assigned_from'])).'</a></p>';
     } else {
      echo '<p class="text-muted task-assigned-from task-assigned-from-contact">'._l('task_assigned_from',get_contact_full_name($assignee['assigned_from'])).'<br /><span class="label inline-block mtop5 label-info">'._l('is_customer_indicator').'</span></p>';
   }
   break;
}
}
} ?>
<h4 class="task-info-heading font-normal font-medium-xs"><i class="fa fa-user-o" aria-hidden="true"></i> <?php echo _l('task_single_assignees'); ?></h4>
<?php if(has_permission('tasks','','edit') || has_permission('tasks','','create')){ ?>
<div class="simple-bootstrap-select">
   <select data-width="100%" <?php if($task->rel_type=='project'){ ?> data-live-search-placeholder="<?php echo _l('search_project_members'); ?>" <?php } ?> data-task-id="<?php echo $task->id; ?>" id="add_task_assignees" class="text-muted task-action-select selectpicker" name="select-assignees" data-live-search="true" title='<?php echo _l('task_single_assignees_select_title'); ?>' data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
      <?php
      $options = '';
      foreach ($staff as $assignee) {
        if (!in_array($assignee['staffid'],$task->assignees_ids)) {
          if ($task->rel_type == 'project'
            && total_rows('tblprojectmembers', array('project_id' => $task->rel_id,'staff_id' => $assignee['staffid'])) == 0) {
           continue;
     }
     $options .= '<option value="' . $assignee['staffid'] . '">' . $assignee['full_name'] . '</option>';
  }
}
echo $options;
?>
</select>
</div>
<?php } ?>
<div class="task_users_wrapper">
   <?php
   $_assignees = '';
   foreach ($task->assignees as $assignee) {
    $_remove_assigne = '';
    if(has_permission('tasks','','edit') || has_permission('tasks','','create')){
      $_remove_assigne = ' <a href="#" class="remove-task-user text-danger" onclick="remove_assignee(' . $assignee['id'] . ',' . $task->id . '); return false;"><i class="fa fa-remove"></i></a>';
   }
   $_assignees .= '
   <div class="task-user"  data-toggle="tooltip" data-title="'.$assignee['full_name'].'">
   <a href="' . admin_url('profile/' . $assignee['assigneeid']) . '" target="_blank">' . staff_profile_image($assignee['assigneeid'], array(
    'staff-profile-image-small'
 )) .'</a> ' . $_remove_assigne . '</span>
   </div>';
}
if ($_assignees == '') {
   $_assignees = '<div class="text-danger display-block">'._l('task_no_assignees').'</div>';
}
echo $_assignees;
?>
</div>
<hr class="task-info-separator" />
<div class="clearfix"></div>
<h4 class="task-info-heading font-normal font-medium-xs">
   <i class="fa fa-user-o" aria-hidden="true"></i>
   <?php echo _l('task_single_followers'); ?>
</h4>
<?php if(has_permission('tasks','','edit') || has_permission('tasks','','create')){ ?>
<div class="simple-bootstrap-select">
   <select data-width="100%" data-task-id="<?php echo $task->id; ?>" class="text-muted selectpicker task-action-select" name="select-followers" data-live-search="true" title='<?php echo _l('task_single_followers_select_title'); ?>' data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
      <?php
      $options = '';
      foreach ($staff as $follower) {
       if (!in_array($follower['staffid'],$task->followers_ids)) {
         $options .= '<option value="' . $follower['staffid'] . '">' . $follower['full_name'] . '</option>';
      }
   }
   echo $options;
   ?>
</select>
</div>
<?php } ?>
<div class="task_users_wrapper">
   <?php
   $_followers        = '';
   foreach ($task->followers as $follower) {
     $_remove_follower = '';
     if(has_permission('tasks','','edit') || has_permission('tasks','','create')){
       $_remove_follower = ' <a href="#" class="remove-task-user text-danger" onclick="remove_follower(' . $follower['id'] . ',' . $task->id . '); return false;"><i class="fa fa-remove"></i></a>';
    }
    $_followers .= '
    <span class="task-user" data-toggle="tooltip" data-title="'.$follower['full_name'].'">
    <a href="' . admin_url('profile/' . $follower['followerid']) . '" target="_blank">' . staff_profile_image($follower['followerid'], array(
     'staff-profile-image-small'
  )) . '</a> ' . $_remove_follower . '</span>
    </span>';
 }
 if ($_followers == '') {
   $_followers = '<div class="display-block text-muted mbot15">'._l('task_no_followers').'</div>';
}
echo $_followers;
?>
</div>
<hr class="task-info-separator" />
<?php echo form_open_multipart('admin/tasks/upload_file',array('id'=>'task-attachment','class'=>'dropzone')); ?>
<?php echo form_close(); ?>
<?php if(get_option('dropbox_app_key') != ''){ ?>
<div class="text-center mtop10">
   <div id="dropbox-chooser-task"></div>
</div>
<?php } ?>
</div>
</div>
</div>
<script>
var commonTaskPopoverMenuOptions = {
   html: true,
   placement: 'bottom',
   trigger: 'click',
   template: '<div class="popover"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"></div></div></div>',
};

var taskPopoverMenus = [{
      selector: '.task-menu-options',
      title: "<?php echo _l('actions'); ?>",
   },
   {
      selector: '.task-menu-status',
      title: "<?php echo _l('task_status'); ?>",
   },
   {
      selector: '.task-menu-priority',
      title: "<?php echo _l('task_single_priority'); ?>",
   },
   {
      selector: '.task-menu-milestones',
      title: "<?php echo _l('task_milestone'); ?>",
   },
];

for (var i = 0; i < taskPopoverMenus.length; i++) {
   var taskMenuContent = $('body').find(taskPopoverMenus[i].selector + ' .content-menu').html();
   $(taskPopoverMenus[i].selector + ' .trigger').popover($.extend({}, commonTaskPopoverMenuOptions, {
      title: taskPopoverMenus[i].title,
      content: taskMenuContent
   }));
}

if (typeof (Dropbox) != 'undefined') {
   document.getElementById("dropbox-chooser-task").appendChild(Dropbox.createChooseButton({
      success: function (files) {
         $.post(admin_url + 'tasks/add_external_attachment', {
            files: files,
            task_id: '<?php echo $task->id; ?>',
            external: 'dropbox'
         }).done(function () {
            init_task_modal('<?php echo $task->id; ?>');
         });
      },
      linkType: "preview",
      extensions: app_allowed_files.split(','),
   }));
}

init_selectpicker();
init_datepicker();
init_lightbox();

tinyMCE.remove('#task_view_description');

if (typeof (taskAttachmentDropzone) != 'undefined') {
   taskAttachmentDropzone.destroy();
}

taskAttachmentDropzone = new Dropzone("#task-attachment", $.extend({}, _dropzone_defaults(), {
   uploadMultiple: true,
   parallelUploads: 20,
   maxFiles: 20,
   paramName: 'file',
   sending: function (file, xhr, formData) {
      formData.append("taskid", '<?php echo $task->id; ?>');
   },
   success: function (files, response) {
      response = JSON.parse(response);
      if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
         _task_append_html(response.taskHtml);
      }
   }
}));
</script>
