<div id="task">
   <div class="row">
      <div class="col-md-12">
         <div class="row">
            <div class="col-md-7">
               <h3 class="mtop5 bold pull-left">
                  <?php if($project->settings->edit_tasks == 1 && $view_task->is_added_from_contact == 1 && $view_task->addedfrom == get_contact_user_id()){ ?>
                  <a href="<?php echo site_url('clients/project/'.$project->id.'?group=edit_task&taskid='.$view_task->id); ?>">
                  <small><i class="fa fa-pencil-square-o"></i></small>
                  </a>
                  <?php } ?> <?php echo $view_task->name; ?>
               </h3>
               <div class="clearfix"></div>
               <?php if($project->settings->view_task_total_logged_time == 1){ ?>
               <p class="pull-left no-mbot"><?php echo _l('task_total_logged_time'); ?>
                  <?php echo seconds_to_time_format($this->tasks_model->calc_task_total_time($view_task->id)); ?>
               </p>
               <?php } ?>
               <?php if($view_task->billed == 1){ ?>
               <div class="clearfix"></div>
               <p class="text-success"><?php echo _l('task_is_billed',format_invoice_number($view_task->invoice_id)); ?></p>
               <?php } ?>
            </div>
            <div class="col-md-5">
               <div class="task-info pull-right">
                  <h5 class="no-margin"><i class="fa fa-bolt"></i>
                     <?php echo _l('task_single_priority'); ?>: <?php echo task_priority($view_task->priority); ?>
                  </h5>
               </div>
               <div class="task-info pull-right <?php if(!$view_task->status != 5){echo ' text-danger'; }else{echo 'text-info';} ?><?php if(!$view_task->duedate){ echo ' hide';} ?>">
                  <h5 class="no-margin"><i class="fa fa-hourglass-end"></i>
                     <?php echo _l('task_single_due_date'); ?>: <?php echo _d($view_task->duedate); ?>
                  </h5>
               </div>
               <div class="text-success task-info pull-right">
                  <h5 class="no-margin"><i class="fa fa-hourglass-start"></i>
                     <?php echo _l('task_single_start_date'); ?>: <?php echo _d($view_task->startdate); ?>
                  </h5>
               </div>
               <?php if($view_task->status == 5){ ?>
               <div class="pull-right task-info text-success">
                  <h5 class="no-margin"><i class="fa fa-check"></i>
                     <?php echo _l('task_single_finished'); ?>: <?php echo _dt($view_task->datefinished); ?>
                  </h5>
               </div>
               <?php } ?>
               <div class="clearfix"></div>
               <span class="task-single-status pull-right mright5"><?php echo format_task_status($view_task->status); ?></span>
            </div>
         </div>
         <?php if($project->settings->view_team_members == 1){ ?>
         <div class="clearfix"></div>
         <hr />
         <div class="row mbot20">
            <div class="col-md-3">
               <i class="fa fa-users"></i> <span class="bold"><?php echo _l('task_single_assignees'); ?></span>
            </div>
            <div class="col-md-9" id="assignees">
               <?php
                  $_assignees = '';
                  foreach ($view_task->assignees as $assignee) {
                    $_assignees .= '
                    <div data-toggle="tooltip" class="pull-left mleft5 task-user" data-title="'.get_staff_full_name($assignee['assigneeid']).'">'
                    .staff_profile_image($assignee['assigneeid'], array(
                      'staff-profile-image-small'
                    )) .'</div>';
                  }
                  if ($_assignees == '') {
                    $_assignees = '<div class="task-connectors-no-indicator display-block">'._l('task_no_assignees').'</div>';
                  }
                  echo $_assignees;
                  ?>
            </div>
         </div>
         <?php } ?>
         <?php if($project->settings->view_task_checklist_items == 1){ ?>
         <?php if(count($view_task->checklist_items) > 0){ ?>
         <hr />
         <h4 class="bold mbot15"><?php echo _l('task_checklist_items'); ?></h4>
         <?php } ?>
         <?php foreach($view_task->checklist_items as $list){ ?>
         <p class="<?php if($list['finished'] == 1){echo 'line-throught';} ?>">
            <span class="task-checklist-indicator <?php if($list['finished'] == 1){echo 'text-success';} else{echo 'text-muted';} ?>"><i class="fa fa-check"></i></span>&nbsp;
            <?php echo $list['description']; ?>
         </p>
         <?php } ?>
         <?php } ?>
         <?php
            $custom_fields = get_custom_fields('tasks',array('show_on_client_portal'=>1));
            if(count($custom_fields) > 0){ ?>
         <div class="row">
            <?php foreach($custom_fields as $field){ ?>
            <?php $value = get_custom_field_value($view_task->id,$field['id'],'tasks');
               if($value == ''){continue;} $custom_fields_found = true;?>
            <div class="col-md-9">
               <span class="bold"><?php echo ucfirst($field['name']); ?></span>
               <br />
               <div class="text-left">
                  <?php echo $value; ?>
               </div>
            </div>
            <?php } ?>
            <?php
               // Add separator if custom fields found for output
               if(isset($custom_fields_found)){?>
            <div class="clearfix"></div>
            <hr />
            <?php } ?>
         </div>
         <?php } ?>
         <?php if($project->settings->view_task_attachments == 1){ ?>
         <?php
            $attachments_data = array();
            if(count($view_task->attachments) > 0){ ?>
         <hr />
         <div class="row">
            <div class="col-md-12">
               <h4 class="bold font-medium"><?php echo _l('task_view_attachments'); ?></h4>
               <div class="row">
                  <?php foreach($view_task->attachments as $attachment){ ?>
                  <div class="col-md-4">
                     <ul class="list-unstyled">
                        <li class="mbot10 task-attachment">
                           <div class="mbot10 pull-right task-attachment-user">
                              <?php
                                 if($attachment['staffid'] != 0){
                                   echo _l('project_file_uploaded_by') . ' ' . get_staff_full_name($attachment['staffid']);
                                 } else {
                                   echo _l('project_file_uploaded_by') . ' ' . get_contact_full_name($attachment['contact_id']);
                                 }
                                 ?>
                              <?php if(get_option('allow_contact_to_delete_files') == 1 && $attachment['contact_id'] == get_contact_user_id()){ ?>
                              <a href="<?php echo site_url('clients/delete_file/'.$attachment['id'].'/task?project_id='.$project->id); ?>" class="text-danger _delete pull-right"><i class="fa fa-remove"></i></a>
                              <?php } ?>
                           </div>
                           <?php
                              $externalPreview = false;
                              $is_image = false;
                              $path = get_upload_path_by_type('task') . $view_task->id . '/'. $attachment['file_name'];
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
                           <a href="<?php echo $href_url; ?>" target="_blank" class="open-in-dropbox" data-toggle="tooltip" data-title="<?php echo _l('open_in_dropbox'); ?>"><i class="fa fa-dropbox" aria-hidden="true"></i></a>
                           <?php }
                              ob_start();
                              ?>
                           <div class="<?php if($is_image){echo 'preview-image';}else if(!$isHtml5Video){echo 'task-attachment-no-preview';} ?>">
                              <?php if(!$isHtml5Video){ ?>
                              <a href="<?php echo (!$externalPreview ? $href_url : $externalPreview); ?>" target="_blank"<?php if($is_image){ ?> data-lightbox="task-attachment"<?php } ?> class="<?php if($isHtml5Video){echo 'video-preview';} ?>">
                                 <?php } ?>
                                 <?php if($is_image){ ?>
                                 <img src="<?php echo $img_url; ?>" class="img img-responsive">
                                 <?php } else if($isHtml5Video) { ?>
                                 <video width="100%" height="100%" src="<?php echo site_url('download/preview_video?path='.protected_file_url_by_path($path).'&type='.$attachment['filetype']); ?>" controls>
                                    Your browser does not support the video tag.
                                 </video>
                                 <?php } else { ?>
                                 <i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i> <?php echo $attachment['file_name']; ?>
                                 <?php } ?>
                                 <?php if(!$isHtml5Video){ ?>
                              </a>
                              <?php }
                                 $attachments_data[$attachment['id']] = ob_get_contents();
                                 ob_end_clean();
                                 echo $attachments_data[$attachment['id']];
                                 ?>
                           </div>
                           <div class="clearfix"></div>
                        </li>
                     </ul>
                  </div>
                  <?php } ?>
               </div>
            </div>
         </div>
         <?php } ?>
         <?php } ?>
         <?php if(!empty($view_task->description)){ ?>
         <hr />
         <h4 class="bold"><?php echo _l('task_view_description'); ?></h4>
         <?php echo $view_task->description; ?>
         <?php } ?>
         <?php if($project->settings->upload_files == 1){ ?>
         <hr />
         <?php echo form_open_multipart(site_url('clients/project/'.$project->id),array('class'=>'dropzone mtop15','id'=>'task-file-upload')); ?>
         <input type="file" name="file" multiple class="hide"/>
         <input type="hidden" name="task_id" value="<?php echo $view_task->id; ?>" class="hide"/>
         <?php echo form_close(); ?>
         <?php if(get_option('dropbox_app_key') != ''){ ?>
         <div class="text-right mtop15">
            <div id="dropbox-chooser-task"></div>
         </div>
         <?php } ?>
         <?php } ?>
         <?php if($project->settings->view_task_comments == 1){ ?>
         <hr />
         <h4 class="bold mbot15"><?php echo _l('task_view_comments'); ?></h4>
         <?php
            if(count($view_task->comments) > 0){
              echo '<div id="task-comments">';
              foreach($view_task->comments as $comment){ ?>
         <div class="mbot10 mtop10" data-commentid="<?php echo $comment['id']; ?>" id="comment_<?php echo $comment['id']; ?>">
            <?php if($comment['staffid'] != 0){ ?>
            <?php echo staff_profile_image($comment['staffid'], array(
               'staff-profile-image-small',
               'media-object img-circle pull-left mright10'
               )); ?>
            <?php } else { ?>
            <img src="<?php echo contact_profile_image_url($comment['contact_id']); ?>" class="client-profile-image-small media-object img-circle pull-left mright10">
            <?php } ?>
            <div class="media-body">
               <?php if($comment['staffid'] != 0){ ?>
               <span class="bold"><?php echo $comment['staff_full_name']; ?></span><br />
               <?php } else { ?>
               <span class="pull-left bold">
               <?php echo get_contact_full_name($comment['contact_id']); ?></span>
               <br />
               <?php } ?>
               <small class="mtop10 text-muted"><?php echo _dt($comment['dateadded']); ?></small><br />
               <?php if($comment['contact_id'] != 0){ ?>
               <?php
                  $comment_added = strtotime($comment['dateadded']);
                  $minus_1_hour = strtotime('-1 hours');
                  if(get_option('client_staff_add_edit_delete_task_comments_first_hour') == 0 || (get_option('client_staff_add_edit_delete_task_comments_first_hour') == 1 && $comment_added >= $minus_1_hour)){ ?>
               <a href="#" onclick="remove_task_comment(<?php echo $comment['id']; ?>); return false;" class="pull-right">
               <i class="fa fa-times text-danger"></i>
               </a>
               <a href="#" onclick="edit_task_comment(<?php echo $comment['id']; ?>); return false;" class="pull-right mright5">
               <i class="fa fa-pencil-square-o"></i>
               </a>
               <div data-edit-comment="<?php echo $comment['id']; ?>" class="hide">
                  <textarea rows="5" class="form-control mtop10 mbot10"><?php echo clear_textarea_breaks($comment['content']); ?></textarea>
                  <button type="button" class="btn btn-info pull-right" onclick="save_edited_comment(<?php echo $comment['id']; ?>)">
                  <?php echo _l('submit'); ?>
                  </button>
                  <button type="button" class="btn btn-default pull-right mright5" onclick="cancel_edit_comment(<?php echo $comment['id']; ?>)">
                  <?php echo _l('cancel'); ?>
                  </button>
               </div>
               <?php } ?>
               <?php } ?>
               <div class="comment-content" data-comment-content="<?php echo $comment['id'] ;?>">
                  <?php
                     if($comment['file_id'] != 0 && $project->settings->view_task_attachments == 1){
                       $comment['content'] = str_replace('[task_attachment]','<br />'.$attachments_data[$comment['file_id']],$comment['content']);
                                 // Replace lightbox to prevent loading the image twice
                       $comment['content'] = str_replace('data-lightbox="task-attachment"','data-lightbox="task-attachment-comment"',$comment['content']);
                     }
                     echo check_for_links($comment['content']); ?>
               </div>
            </div>
            <hr />
         </div>
         <?php } }
            if($project->settings->comment_on_tasks == 1){
             echo form_open($this->uri->uri_string());
             echo form_hidden('action','new_task_comment');
             echo form_hidden('taskid',$view_task->id);
             ?>
         <textarea name="content" rows="5" class="form-control mtop15"></textarea>
         <button type="submit" class="btn btn-info mtop10 pull-right" data-loading-text="<?php echo _l('wait_text'); ?>" autocomplete="off"><?php echo _l('task_single_add_new_comment'); ?></button>
         <div class="clearfix"></div>
         <?php echo form_close(); } ?>
      </div>
      <?php } ?>
   </div>
</div>
</div>
<script>
   var task_comment_temp = window.location.href.split('#');
   if(task_comment_temp[1]){
     var task_comment_id = task_comment_temp[task_comment_temp.length-1];
     $('html,body').animate({
       scrollTop: $('#' + task_comment_id).offset().top},
       'slow');
   }
</script>
