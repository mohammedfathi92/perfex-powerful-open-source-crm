<?php init_head(); ?>
<?php if(isset($form)){
   echo form_hidden('form_id',$form->id);
   } ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
               <?php if(isset($form)){ ?>
                  <ul class="nav nav-tabs" role="tablist">
                     <li role="presentation" class="active">
                        <a href="#tab_form_build" aria-controls="tab_form_build" role="tab" data-toggle="tab">
                        <?php echo _l('form_builder'); ?>
                        </a>
                     </li>
                     <li role="presentation">
                        <a href="#tab_form_information" aria-controls="tab_form_information" role="tab" data-toggle="tab">
                        <?php echo _l('form_information'); ?>
                        </a>
                     </li>
                     <li role="presentation">
                        <a href="#tab_form_integration" aria-controls="tab_form_integration" role="tab" data-toggle="tab">
                        <?php echo _l('form_integration_code'); ?>
                        </a>
                     </li>
                  </ul>
                  <?php } ?>
                  <div class="tab-content">
                     <?php if(isset($form)){ ?>
                     <div role="tabpanel" class="tab-pane active" id="tab_form_build">
                        <div id="build-wrap"></div>
                     </div>
                     <div role="tabpanel" class="tab-pane" id="tab_form_integration">
                        <p><?php echo _l('form_integration_code_help'); ?></p>
                        <textarea readonly class="form-control" rows="5"><iframe width="600" height="850" src="<?php echo site_url('forms/wtl/'.$form->form_key); ?>" frameborder="0" allowfullscreen></iframe></textarea>
                     </div>
                     <?php } ?>
                     <div role="tabpanel" class="tab-pane<?php if(!isset($form)){echo ' active';} ?>" id="tab_form_information">
                        <?php if(!isset($form)){ ?>
                        <h4 class="font-medium-xs bold no-mtop"><?php echo _l('form_builder_create_form_first'); ?></h4>
                        <hr />
                        <?php } ?>
                        <?php echo form_open($this->uri->uri_string(),array('id'=>'form_info')); ?>
                        <div class="row">
                           <div class="col-md-6">
                              <?php $value = (isset($form) ? $form->name : ''); ?>
                              <?php echo render_input('name','form_name',$value); ?>
                              <?php
                                 if(get_option('recaptcha_secret_key') != '' && get_option('recaptcha_site_key') != ''){ ?>
                              <div class="form-group">
                                 <label for=""><?php echo _l('form_recaptcha'); ?></label><br />
                                 <div class="radio radio-inline radio-danger">
                                    <input type="radio" name="recaptcha" id="racaptcha_0" value="0"<?php if(isset($form) && $form->recaptcha == 0 || !isset($form)){echo ' checked';} ?>>
                                    <label for="recaptcha_0"><?php echo _l('settings_no'); ?></label>
                                 </div>
                                 <div class="radio radio-inline radio-success">
                                    <input type="radio" name="recaptcha" id="recaptcha_1" value="1"<?php if(isset($form) && $form->recaptcha == 1){echo ' checked';} ?>>
                                    <label for="recaptcha_1"><?php echo _l('settings_yes'); ?></label>
                                 </div>
                              </div>
                              <?php } ?>
                              <div class="form-group">
                                 <label for="language" class="control-label"><i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('form_lang_validation_help'); ?>"></i> <?php echo _l('form_lang_validation'); ?></label>
                                 <select name="language" id="language" class="form-control selectpicker" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                    <option value=""></option>
                                    <?php foreach($languages as $language){ ?>
                                    <option value="<?php echo $language; ?>"<?php if((isset($form) && $form->language == $language) || (!isset($form) && get_option('active_language') == $language)){echo ' selected'; } ?>><?php echo ucfirst($language); ?></option>
                                    <?php } ?>
                                 </select>
                              </div>
                              <?php $value = (isset($form) ? $form->submit_btn_name : 'Submit'); ?>
                              <?php echo render_input('submit_btn_name','form_btn_submit_text',$value); ?>
                              <?php $value = (isset($form) ? $form->success_submit_msg : ''); ?>
                              <?php echo render_textarea('success_submit_msg','form_success_submit_msg',$value); ?>

                             <div class="checkbox checkbox-primary">
                            <input type="checkbox" name="mark_public" id="mark_public" <?php if(isset($form) && $form->mark_public == 1){echo 'checked';} ?>>
                            <label for="mark_public">
                                <?php echo _l('auto_mark_as_public'); ?></label>
                            </div>
                              <div class="checkbox checkbox-primary">
                                 <input type="checkbox" name="allow_duplicate" id="allow_duplicate" <?php if(isset($form) && $form->allow_duplicate == 1 || !isset($form)){echo 'checked';} ?>>
                                 <label for="allow_duplicate"><?php echo _l('form_allow_duplicate',_l('leads')); ?></label>
                              </div>
                              <div class="duplicate-settings-wrapper row<?php if(isset($form) && $form->allow_duplicate == 1 || !isset($form)){echo ' hide';} ?>">
                                 <div class="col-md-12">
                                    <hr />
                                 </div>
                                 <div class="col-md-6">
                                    <div class="form-group">
                                       <label for="track_duplicate_field"><?php echo _l('track_duplicate_by_field'); ?></label><br />
                                       <select class="selectpicker track_duplicate_field" data-width="100%" name="track_duplicate_field" id="track_duplicate_field" data-none-selected-text="">
                                          <option value=""></option>
                                          <?php foreach($db_fields as $field){ ?>
                                          <option value="<?php echo $field->name; ?>"<?php if(isset($form) && $form->track_duplicate_field == $field->name){echo ' selected';} if(isset($form) && $form->track_duplicate_field_and == $field->name){echo 'disabled';} ?>><?php echo $field->label; ?></option>
                                          <?php } ?>
                                       </select>
                                    </div>
                                 </div>
                                 <div class="col-md-6">
                                    <div class="form-group">
                                       <label for="track_duplicate_field_and"><?php echo _l('and_track_duplicate_by_field'); ?></label><br />
                                       <select class="selectpicker track_duplicate_field_and" data-width="100%" name="track_duplicate_field_and" id="track_duplicate_field_and" data-none-selected-text="">
                                          <option value=""></option>
                                          <?php foreach($db_fields as $field){ ?>
                                          <option value="<?php echo $field->name; ?>"<?php if(isset($form) && $form->track_duplicate_field_and == $field->name){echo ' selected';} if(isset($form) && $form->track_duplicate_field == $field->name){echo 'disabled';} ?>><?php echo $field->label; ?></option>
                                          <?php } ?>
                                       </select>
                                    </div>
                                 </div>
                                 <div class="col-md-12">
                                    <div class="checkbox checkbox-primary">
                                       <input type="checkbox" name="create_task_on_duplicate" id="create_task_on_duplicate" <?php if(isset($form) && $form->create_task_on_duplicate == 1){echo 'checked';} ?>>
                                       <label for="create_task_on_duplicate"><i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('create_the_duplicate_form_data_as_task_help'); ?>"></i> <?php echo _l('create_the_duplicate_form_data_as_task',_l('lead_lowercase')); ?></label>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="col-md-6">
                              <?php

                                 echo render_leads_source_select($sources, (isset($form) ? $form->lead_source : get_option('leads_default_source')),'lead_import_source','lead_source');

                                  echo render_leads_status_select($statuses, (isset($form) ? $form->lead_status : get_option('leads_default_status')),'lead_import_status','lead_status');

                                 $selected = '';
                                 foreach($members as $staff){
                                   if(isset($form) && $form->responsible == $staff['staffid']){
                                     $selected = $staff['staffid'];
                                   }
                                 }
                                 ?>
                              <?php echo render_select('responsible',$members,array('staffid',array('firstname','lastname')),'leads_import_assignee',$selected); ?>
                              <hr />
                              <label for="" class="control-label"><?php echo _l('notification_settings'); ?></label>
                              <div class="clearfix"></div>
                              <div class="checkbox checkbox-primary">
                                 <input type="checkbox" name="notify_lead_imported" id="notify_lead_imported" <?php if(isset($form) && $form->notify_lead_imported == 1 || !isset($form)){echo 'checked';} ?>>
                                 <label for="notify_lead_imported"><?php echo _l('leads_email_integration_notify_when_lead_imported'); ?></label>
                              </div>
                              <div class="select-notification-settings<?php if(isset($form) && $form->notify_lead_imported == '0'){echo ' hide';} ?>">
                              <hr />
                              <div class="radio radio-primary radio-inline">
                                 <input type="radio" name="notify_type" value="specific_staff" id="specific_staff" <?php if(isset($form) && $form->notify_type == 'specific_staff' || !isset($form)){echo 'checked';} ?>>
                                 <label for="specific_staff"><?php echo _l('specific_staff_members'); ?></label>
                              </div>
                              <div class="radio radio-primary radio-inline">
                                 <input type="radio" name="notify_type" id="roles" value="roles" <?php if(isset($form) && $form->notify_type == 'roles'){echo 'checked';} ?>>
                                 <label for="roles"><?php echo _l('staff_with_roles'); ?></label>
                              </div>
                              <div class="radio radio-primary radio-inline">
                                 <input type="radio" name="notify_type" id="assigned" value="assigned" <?php if(isset($form) && $form->notify_type == 'assigned'){echo 'checked';} ?>>
                                 <label for="assigned"><?php echo _l('notify_assigned_user'); ?></label>
                              </div>
                              <div class="clearfix mtop15"></div>
                              <div id="specific_staff_notify" class="<?php if(isset($form) && $form->notify_type != 'specific_staff'){echo 'hide';} ?>">
                                 <?php
                                    $selected = array();
                                    if(isset($form) && $form->notify_type == 'specific_staff'){
                                      $selected = unserialize($form->notify_ids);
                                    }
                                    ?>
                                 <?php echo render_select('notify_ids_staff[]',$members,array('staffid',array('firstname','lastname')),'leads_email_integration_notify_staff',$selected,array('multiple'=>true)); ?>
                              </div>
                              <div id="role_notify" class="<?php if(isset($form) && $form->notify_type != 'roles' || !isset($form)){echo 'hide';} ?>">
                                 <?php
                                    $selected = array();
                                    if(isset($form) && $form->notify_type == 'roles'){
                                      $selected = unserialize($form->notify_ids);
                                    }
                                    ?>
                                 <?php echo render_select('notify_ids_roles[]',$roles,array('roleid',array('name')),'leads_email_integration_notify_roles',$selected,array('multiple'=>true)); ?>
                              </div>
                              </div>
                           </div>
                        </div>
                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                        </div>
                        <?php echo form_close(); ?>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
     <div class="btn-bottom-pusher"></div>
   </div>
</div>
<?php init_tail(); ?>
<script>
var buildWrap = document.getElementById('build-wrap');
var formData = <?php echo json_encode($formData); ?>;
</script>
<?php $this->load->view('admin/includes/_form_js_formatter'); ?>
<script>

   $(function(){

     var formBuilder = $(buildWrap).formBuilder(fbOptions).data('formBuilder');
     var $create_task_on_duplicate = $('#create_task_on_duplicate');

     $('#allow_duplicate').on('change',function(){
       $('.duplicate-settings-wrapper').toggleClass('hide');
     });

     $('#notify_lead_imported').on('change',function(){
          $('.select-notification-settings').toggleClass('hide');
     });

     $('#track_duplicate_field,#track_duplicate_field_and').on('change',function(){
       var selector = ($(this).hasClass('track_duplicate_field') ? 'track_duplicate_field_and' : 'track_duplicate_field')
         $('#'+selector+' option').removeAttr('disabled',true);
         $('#'+selector+' option[value="'+$(this).val()+'"]').attr('disabled',true);
         $('#'+selector+'').selectpicker('refresh');
     });

     setTimeout(function(){
         $( ".form-builder-save" ).wrap( "<div class='btn-bottom-toolbar text-right'></div>" );
         $btnToolbar = $('body').find('#tab_form_build .btn-bottom-toolbar');
         $btnToolbar = $('#tab_form_build').append($btnToolbar);
         $btnToolbar.find('.btn').addClass('btn-info');
     },100);

     $('body').on('click','.form-builder-save',function() {
       $.post(admin_url+'leads/save_form_data',{
        formData:formBuilder.formData,
        id:$('input[name="form_id"]').val()
      }).done(function(response){
         response = JSON.parse(response);
         if(response.success == true){
           alert_float('success',response.message);
         }
       });
     });

     _validate_form('#form_info',{
       name:'required',
       lead_source: 'required',
       lead_status: 'required',
       language:'required',
       success_submit_msg:'required',
       submit_btn_name:'required',
       responsible: {
         required: {
            depends:function(element){
             var isRequiredByNotifyType = ($('input[name="notify_type"]:checked').val() == 'assigned') ? true : false;
             var isRequiredByAssignTask = ($create_task_on_duplicate.is(':checked')) ? true : false;
             var isRequired = isRequiredByNotifyType || isRequiredByAssignTask;
             if(isRequired) {
                $('[for="responsible"]').find('.req').removeClass('hide');
             } else {
                $(element).next('p.text-danger').remove();
                $('[for="responsible"]').find('.req').addClass('hide');
             }
             return isRequired;
           }
         }
       }
     });

     var $notifyTypeInput = $('input[name="notify_type"]');
     $notifyTypeInput.on('change',function(){
        $('#form_info').validate().checkForm()
     });
     $notifyTypeInput.trigger('change');

     $create_task_on_duplicate.on('change',function(){
        $('#form_info').validate().checkForm()
     });

     $create_task_on_duplicate.trigger('change');
   });

</script>
</body>
</html>
