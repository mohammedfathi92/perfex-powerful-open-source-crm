<?php init_head(); ?>
<style>
#ribbon_project_<?php echo $project->id; ?> span::before {
  border-top: 3px solid <?php echo $project_status['color']; ?>;
  border-left: 3px solid <?php echo $project_status['color']; ?>;
}
</style>
<div id="wrapper">
  <?php echo form_hidden('project_id',$project->id) ?>
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s project-top-panel panel-full">
          <div class="panel-body _buttons">
            <div class="row">
              <div class="col-md-8 project-heading">
                <h3 class="hide project-name"><?php echo $project->name; ?></h3>
                <div id="project_view_name">
                 <select class="selectpicker" id="project_top" data-width="fit"<?php if(count($other_projects) > 4){ ?> data-live-search="true" <?php } ?>>
                   <option value="<?php echo $project->id; ?>" selected><?php echo $project->name; ?></option>
                   <?php foreach($other_projects as $op){ ?>
                   <option value="<?php echo $op['id']; ?>" data-subtext="<?php echo $op['company']; ?>">#<?php echo $op['id']; ?> - <?php echo $op['name']; ?></option>
                   <?php } ?>
                 </select>
               </div>
             </div>
             <div class="col-md-4 text-right">
              <?php if(has_permission('tasks','','create')){ ?>
              <a href="#" onclick="new_task_from_relation(undefined,'project',<?php echo $project->id; ?>); return false;" class="btn btn-info"><?php echo _l('new_task'); ?></a>
              <?php } ?>
              <?php
              $invoice_func = 'pre_invoice_project';
              ?>
              <?php if(has_permission('invoices','','create')){ ?>
              <a href="#" onclick="<?php echo $invoice_func; ?>(<?php echo $project->id; ?>); return false;" class="invoice-project btn btn-info<?php if($project->client_data->active == 0){echo ' disabled';} ?>"><?php echo _l('invoice_project'); ?></a>
              <?php } ?>
              <?php
              $project_pin_tooltip = _l('pin_project');
              if(total_rows('tblpinnedprojects',array('staff_id'=>get_staff_user_id(),'project_id'=>$project->id)) > 0){
                $project_pin_tooltip = _l('unpin_project');
              }
              ?>
              <div class="btn-group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <?php echo _l('more'); ?> <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right width200 project-actions">
                  <li>
                   <a href="<?php echo admin_url('projects/pin_action/'.$project->id); ?>">
                    <?php echo $project_pin_tooltip; ?>
                  </a>
                </li>
                <?php if(has_permission('projects','','edit')){ ?>
                <li>
                  <a href="<?php echo admin_url('projects/project/'.$project->id); ?>">
                    <?php echo _l('edit_project'); ?>
                  </a>
                </li>
                <?php } ?>
                <?php if(has_permission('projects','','create')){ ?>
                <li>
                  <a href="#" onclick="copy_project(); return false;">
                    <?php echo _l('copy_project'); ?>
                  </a>
                </li>
                <?php } ?>
                <?php if(has_permission('projects','','create') || has_permission('projects','','edit')){ ?>
                <li class="divider"></li>
                <?php foreach($statuses as $status){
                  if($status['id'] == $project->status){continue;}
                  ?>
                  <li>
                    <a href="#" onclick="project_mark_as_modal(<?php echo $status['id']; ?>,<?php echo $project->id; ?>); return false;"><?php echo _l('project_mark_as',$status['name']); ?></a>
                  </li>
                  <?php } ?>
                  <?php } ?>
                  <li class="divider"></li>
                  <?php if(has_permission('projects','','create')){ ?>
                  <li>
                   <a href="<?php echo admin_url('projects/export_project_data/'.$project->id); ?>" target="_blank"><?php echo _l('export_project_data'); ?></a>
                 </li>
                 <?php } ?>
                 <?php if(is_admin()){ ?>
                 <li>
                  <a href="<?php echo admin_url('projects/view_project_as_client/'.$project->id .'/'.$project->clientid); ?>" target="_blank"><?php echo _l('project_view_as_client'); ?></a>
                </li>
                <?php } ?>
                <?php if(has_permission('projects','','delete')){ ?>
                <li>
                  <a href="<?php echo admin_url('projects/delete/'.$project->id); ?>" class="_delete">
                    <span class="text-danger"><?php echo _l('delete_project'); ?></span>
                  </a>
                </li>
                <?php } ?>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="panel_s project-menu-panel">
      <div class="panel-body">
        <?php do_action('before_render_project_view',$project->id); ?>
        <?php echo '<div class="ribbon project-status-ribbon-'.$project->status.'" id="ribbon_project_'.$project->id.'"><span style="background:'.$project_status['color'].'">'.$project_status['name'].'</span></div>'; ?>
        <?php $this->load->view('admin/projects/project_tabs'); ?>
      </div>
    </div>
    <?php if($view == 'project_milestones') { ?>
    <a href="#" class="project-tabs-and-opts-toggler screen-options-btn bold"><?php echo _l('show_tabs_and_options'); ?></a>
    <?php } else { ?>
    <?php if((has_permission('projects','','create') || has_permission('projects','','edit')) && $project->status == 1 && $this->projects_model->timers_started_for_project($project->id)){ ?>
    <div class="alert alert-warning project-no-started-timers-found mbot15">
      <?php echo _l('project_not_started_status_tasks_timers_found'); ?>
    </div>
    <?php } ?>
    <?php if($project->deadline && date('Y-m-d') > $project->deadline && $project->status == 2){ ?>
    <div class="alert alert-warning bold project-due-notice mbot15">
      <?php echo _l('project_due_notice',floor((abs(time() - strtotime($project->deadline)))/(60*60*24))); ?>
    </div>
    <?php } ?>
    <?php if(!has_contact_permission('projects',get_primary_contact_user_id($project->clientid)) && total_rows('tblcontacts',array('userid'=>$project->clientid)) > 0){ ?>
    <div class="alert alert-warning project-permissions-warning mbot15">
      <?php echo _l('project_customer_permission_warning'); ?>
    </div>
    <?php } ?>
    <?php } ?>
    <div class="panel_s">
      <div class="panel-body">
        <?php echo $group_view; ?>
      </div>
    </div>
  </div>
</div>
</div>
</div>
</div>
</div>
<?php if(isset($discussion)){
  echo form_hidden('discussion_id',$discussion->id);
  echo form_hidden('discussion_user_profile_image_url',$discussion_user_profile_image_url);
  echo form_hidden('current_user_is_admin',$current_user_is_admin);
}
echo form_hidden('project_percent',$percent);
?>
<div id="invoice_project"></div>
<div id="pre_invoice_project"></div>
<?php $this->load->view('admin/projects/milestone'); ?>
<?php $this->load->view('admin/projects/copy_settings'); ?>
<?php $this->load->view('admin/projects/_mark_tasks_finished'); ?>
<?php init_tail(); ?>
<?php $discussion_lang = get_project_discussions_language_array(); ?>
<?php echo app_script('assets/js','projects.js'); ?>
<!-- For invoices table -->
<script>
  taskid = '<?php echo $this->input->get('taskid'); ?>';
</script>
<script>
  var gantt_data = {};
  <?php if(isset($gantt_data)){ ?>
    gantt_data = <?php echo json_encode($gantt_data); ?>;
    <?php } ?>
    var discussion_id = $('input[name="discussion_id"]').val();
    var discussion_user_profile_image_url = $('input[name="discussion_user_profile_image_url"]').val();
    var current_user_is_admin = $('input[name="current_user_is_admin"]').val();
    var project_id = $('input[name="project_id"]').val();
    if(typeof(discussion_id) != 'undefined'){
      discussion_comments('#discussion-comments',discussion_id,'regular');
    }
    $(function(){
     var project_progress_color = '<?php echo do_action('admin_project_progress_color','#84c529'); ?>';
     var circle = $('.project-progress').circleProgress({fill: {
      gradient: [project_progress_color, project_progress_color]
    }}).on('circle-animation-progress', function(event, progress, stepValue) {
      $(this).find('strong.project-percent').html(parseInt(100 * stepValue) + '<i>%</i>');
    });
  });

    function discussion_comments(selector,discussion_id,discussion_type){
     $(selector).comments({
       roundProfilePictures: true,
       textareaRows: 4,
       textareaRowsOnFocus: 6,
       profilePictureURL:discussion_user_profile_image_url,
       enableUpvoting: false,
       enableAttachments:true,
       popularText:'',
       enableDeletingCommentWithReplies:false,
       textareaPlaceholderText:"<?php echo $discussion_lang['discussion_add_comment']; ?>",
       newestText:"<?php echo $discussion_lang['discussion_newest']; ?>",
       oldestText:"<?php echo $discussion_lang['discussion_oldest']; ?>",
       attachmentsText:"<?php echo $discussion_lang['discussion_attachments']; ?>",
       sendText:"<?php echo $discussion_lang['discussion_send']; ?>",
       replyText:"<?php echo $discussion_lang['discussion_reply']; ?>",
       editText:"<?php echo $discussion_lang['discussion_edit']; ?>",
       editedText:"<?php echo $discussion_lang['discussion_edited']; ?>",
       youText:"<?php echo $discussion_lang['discussion_you']; ?>",
       saveText:"<?php echo $discussion_lang['discussion_save']; ?>",
       deleteText:"<?php echo $discussion_lang['discussion_delete']; ?>",
       viewAllRepliesText:"<?php echo $discussion_lang['discussion_view_all_replies'] . ' (__replyCount__)'; ?>",
       hideRepliesText:"<?php echo $discussion_lang['discussion_hide_replies']; ?>",
       noCommentsText:"<?php echo $discussion_lang['discussion_no_comments']; ?>",
       noAttachmentsText:"<?php echo $discussion_lang['discussion_no_attachments']; ?>",
       attachmentDropText:"<?php echo $discussion_lang['discussion_attachments_drop']; ?>",
       currentUserIsAdmin:current_user_is_admin,
       getComments: function(success, error) {
         $.get(admin_url + 'projects/get_discussion_comments/'+discussion_id+'/'+discussion_type,function(response){
           success(response);
         },'json');
       },
       postComment: function(commentJSON, success, error) {
         $.ajax({
           type: 'post',
           url: admin_url + 'projects/add_discussion_comment/'+discussion_id+'/'+discussion_type,
           data: commentJSON,
           success: function(comment) {
             comment = JSON.parse(comment);
             success(comment)
           },
           error: error
         });
       },
       putComment: function(commentJSON, success, error) {
         $.ajax({
           type: 'post',
           url: admin_url + 'projects/update_discussion_comment',
           data: commentJSON,
           success: function(comment) {
             comment = JSON.parse(comment);
             success(comment)
           },
           error: error
         });
       },
       deleteComment: function(commentJSON, success, error) {
         $.ajax({
           type: 'post',
           url: admin_url + 'projects/delete_discussion_comment/'+commentJSON.id,
           success: success,
           error: error
         });
       },
       timeFormatter: function(time) {
         return moment(time).fromNow();
       },
       uploadAttachments: function(commentArray, success, error) {
         var responses = 0;
         var successfulUploads = [];
         var serverResponded = function() {
           responses++;
             // Check if all requests have finished
             if(responses == commentArray.length) {
                 // Case: all failed
                 if(successfulUploads.length == 0) {
                   error();
                 // Case: some succeeded
               } else {
                 successfulUploads = JSON.parse(successfulUploads);
                 success(successfulUploads)
               }
             }
           }
           $(commentArray).each(function(index, commentJSON) {
             // Create form data
             var formData = new FormData();
             if(commentJSON.file.size && commentJSON.file.size > max_php_ini_upload_size_bytes){
              alert_float('danger',"<?php echo _l("file_exceeds_max_filesize"); ?>");
              serverResponded();
            } else {
             $(Object.keys(commentJSON)).each(function(index, key) {
               var value = commentJSON[key];
               if(value) formData.append(key, value);
             });

             if (typeof(csrfData) !== 'undefined') {
                formData.append(csrfData['token_name'], csrfData['hash']);
             }
             $.ajax({
               url: admin_url + 'projects/add_discussion_comment/'+discussion_id+'/'+discussion_type,
               type: 'POST',
               data: formData,
               cache: false,
               contentType: false,
               processData: false,
               success: function(commentJSON) {
                 successfulUploads.push(commentJSON);
                 serverResponded();
               },
               error: function(data) {
                var error = JSON.parse(data.responseText);
                alert_float('danger',error.message);
                serverResponded();
              },
            });
           }
         });
         }
       });
}
</script>
</body>
</html>
