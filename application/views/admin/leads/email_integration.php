<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                      <?php do_action('before_leads_email_integration_form'); ?>
                      <h4 class="no-margin">
                       <?php echo $title; ?>
                   </h4>
                   <?php echo form_open($this->uri->uri_string(),array('id'=>'leads-email-integration')); ?>
                   <hr class="hr-panel-heading no-mbot" />
                   <?php if(!function_exists('iconv')){ ?>
                   <div class="alert alert-danger">
                       You need to enable <b>iconv</b> php extension in order to use this feature. You can enable it via php.ini or contact your hosting provider to enable this extension.
                   </div>
                   <?php } ?>

                   <div class="btn-bottom-toolbar text-right">
                       <a href="<?php echo admin_url('leads/test_email_integration'); ?>" class="btn btn-default test-leads-email-integration"><?php echo _l('leads_email_integration_test_connection'); ?></a>
                       <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                   </div>
                   <!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
                   <input  type="text" class="fake-autofill-field" name="fakeusernameremembered" value='' tabindex="-1"/>
                   <input  type="password" class="fake-autofill-field" name="fakepasswordremembered" value='' tabindex="-1"/>
                   <div class="row">
                       <div class="col-md-12">
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" name="active" id="active" class="ays-ignore" <?php if($mail->active == 1){echo 'checked';} ?>>
                            <label for="active"><?php echo _l('leads_email_active'); ?></label>
                        </div>
                    </div>
                    <div class="col-md-6">

                        <?php echo render_input('imap_server','leads_email_integration_imap',$mail->imap_server); ?>

                        <?php echo render_input('email','leads_email_integration_email',$mail->email); ?>

                        <?php $ps = $mail->password;
                        if(!empty($ps)){
                           if(false == $this->encryption->decrypt($ps)){
                            $ps = $ps;
                        } else {
                            $ps = $this->encryption->decrypt($ps);
                        }
                    }
                    echo render_input('password','leads_email_integration_password',$ps,'password',array('autocomplete'=>'off')); ?>
                    <div class="form-group">
                        <label for="encryption"><?php echo _l('leads_email_encryption'); ?></label><br />
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" name="encryption" value="tls" id="tls" <?php if($mail->encryption == 'tls'){echo 'checked';} ?>>
                            <label for="tls">TLS</label>
                        </div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" name="encryption" value="ssl" id="ssl" <?php if($mail->encryption == 'ssl'){echo 'checked';} ?>>
                            <label for="ssl">SSL</label>
                        </div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" name="encryption" value="" id="no_enc" <?php if($mail->encryption == ''){echo 'checked';} ?>>
                            <label for="no_enc"><?php echo _l('leads_email_integration_folder_no_encryption'); ?></label>
                        </div>
                    </div>
                    <?php echo render_input('folder','leads_email_integration_folder',$mail->folder); ?>

                    <?php echo render_input('check_every','leads_email_integration_check_every',$mail->check_every,'number',array('min'=>do_action('leads_email_integration_check_every',10),'data-ays-ignore'=>true)); ?>

                    <div class="checkbox checkbox-primary">
                        <input type="checkbox" name="only_loop_on_unseen_emails" class="ays-ignore" id="only_loop_on_unseen_emails" <?php if($mail->only_loop_on_unseen_emails == 1){echo 'checked';} ?>>
                        <label for="only_loop_on_unseen_emails"><i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('leads_email_integration_only_check_unseen_emails_help'); ?>"></i>
                            <?php echo _l('leads_email_integration_only_check_unseen_emails'); ?></label>
                        </div>

                        <div class="checkbox checkbox-primary">
                         <input type="checkbox" class="ays-ignore" name="create_task_if_customer" id="create_task_if_customer" <?php if($mail->create_task_if_customer == 1){echo 'checked';} ?>>
                         <label for="create_task_if_customer"><i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('create_the_duplicate_form_data_as_task_help'); ?>"></i> <?php echo _l('lead_is_contact_create_task'); ?></label>
                     </div>

                     <div class="checkbox checkbox-primary">
                        <input type="checkbox" name="delete_after_import" class="ays-ignore" id="delete_after_import" <?php if($mail->delete_after_import == 1){echo 'checked';} ?>>
                        <label for="delete_after_import">
                            <?php echo _l('delete_mail_after_import'); ?></label>
                        </div>

                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" name="mark_public" class="ays-ignore" id="mark_public" <?php if($mail->mark_public == 1){echo 'checked';} ?>>
                            <label for="mark_public">
                                <?php echo _l('auto_mark_as_public'); ?></label>
                            </div>

                        </div>
                        <div class="col-md-6">
                            <?php

                            $selected = $mail->lead_status;
                            if($selected == 0){
                                $selected = get_option('leads_default_status');
                            }

                            echo render_leads_status_select($statuses, $selected,'leads_email_integration_default_status','lead_status',array('data-ays-ignore'=>true));

                            $selected = $mail->lead_source;
                            if($selected == 0){
                                $selected = get_option('leads_default_source');
                            }
                            echo render_leads_source_select($sources, $selected,'leads_email_integration_default_source','lead_source',array('data-ays-ignore'=>true));
                            $selected = '';
                            foreach($members as $staff){
                                if($mail->responsible == $staff['staffid']){
                                    $selected = $staff['staffid'];
                                }
                            }
                            ?>
                            <?php echo render_select('responsible',$members,array('staffid',array('firstname','lastname')),'leads_email_integration_default_assigned',$selected,array('data-ays-ignore'=>true)); ?>

                            <hr />
                            <label for="" class="control-label"><?php echo _l('notification_settings'); ?></label>
                            <div class="clearfix"></div>
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="notify_lead_imported" id="notify_lead_imported" class="ays-ignore" <?php if($mail->notify_lead_imported == 1){echo 'checked';} ?>>
                                <label for="notify_lead_imported"><?php echo _l('leads_email_integration_notify_when_lead_imported'); ?></label>
                            </div>
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="notify_lead_contact_more_times" class="ays-ignore" id="notify_lead_contact_more_times" <?php if($mail->notify_lead_contact_more_times == 1){echo 'checked';} ?>>
                                <label for="notify_lead_contact_more_times"><?php echo _l('leads_email_integration_notify_when_lead_contact_more_times'); ?></label>
                            </div>
                            <div class="select-notification-settings<?php if($mail->notify_lead_imported == '0' && $mail->notify_lead_contact_more_times == '0'){echo ' hide';} ?>">
                               <hr />
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" name="notify_type" class="ays-ignore" id="roles" value="roles" <?php if($mail->notify_type == 'roles'){echo 'checked';} ?>>
                                <label for="roles"><?php echo _l('staff_with_roles'); ?></label>
                            </div>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" name="notify_type" class="ays-ignore" value="specific_staff" id="specific_staff" <?php if($mail->notify_type == 'specific_staff'){echo 'checked';} ?>>
                                <label for="specific_staff"><?php echo _l('specific_staff_members'); ?></label>
                            </div>
                                <div class="radio radio-primary radio-inline">
                                 <input type="radio" class="ays-ignore" name="notify_type" id="assigned" value="assigned" <?php if($mail->notify_type == 'assigned'){echo 'checked';} ?>>
                                 <label for="assigned"><?php echo _l('notify_assigned_user'); ?></label>
                              </div>

                            <div class="clearfix mtop15"></div>

                            <div id="role_notify" class="<?php if($mail->notify_type != 'roles'){echo 'hide';} ?>">
                                <?php
                                $selected = array();
                                if($mail->notify_type == 'roles'){
                                    $selected = unserialize($mail->notify_ids);
                                }
                                ?>
                                <?php echo render_select('notify_ids_roles[]',$roles,array('roleid',array('name')),'leads_email_integration_notify_roles',$selected,array('multiple'=>true,'data-ays-ignore'=>true)); ?>
                            </div>
                            <div id="specific_staff_notify" class="<?php if($mail->notify_type != 'specific_staff'){echo 'hide';} ?>">
                                <?php
                                $selected = array();
                                if($mail->notify_type == 'specific_staff'){
                                    $selected = unserialize($mail->notify_ids);
                                }
                                ?>
                                <?php echo render_select('notify_ids_staff[]',$members,array('staffid',array('firstname','lastname')),'leads_email_integration_notify_staff',$selected,array('multiple'=>true,'data-ays-ignore'=>true)); ?>
                            </div>
                             </div>
                        </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="btn-bottom-pusher"></div>
</div>
</div>
<?php init_tail(); ?>
<script>
    $(function(){

      var $create_task_if_customer = $('#create_task_if_customer');

      $('#leads-email-integration').on('dirty.areYouSure', function() {
          // Enable save button only as the form is dirty.
          $('.test-leads-email-integration').addClass('disabled');
      });

      $('#leads-email-integration').on('clean.areYouSure', function() {
          // Form is clean so nothing to save - disable the save button.
          $('.test-leads-email-integration').removeClass('disabled');
      });

      $('#notify_lead_imported,#notify_lead_contact_more_times').on('change',function(){
          if($('#notify_lead_imported').prop('checked') == false && $('#notify_lead_contact_more_times').prop('checked') == false) {
            $('.select-notification-settings').addClass('hide');
          } else {
            $('.select-notification-settings').removeClass('hide');
          }
      });

       _validate_form($('#leads-email-integration'), {
        lead_source: 'required',
        lead_status: 'required',
        imap_server: 'required',
        password: 'required',
        port: 'required',
        email: {
            required: true
        },
        check_every: {
            required: true,
            number: true
        },
        folder: 'required',
        responsible: {
        required: {
           depends:function(element){
             var isRequiredByNotifyType = ($('input[name="notify_type"]:checked').val() == 'assigned') ? true : false;
             var isRequiredByAssignTask = ($create_task_if_customer.is(':checked')) ? true : false;
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
        $('#leads-email-integration').validate().checkForm()
     });

     $create_task_if_customer.on('change',function(){
        $('#leads-email-integration').validate().checkForm()
     });

     $create_task_if_customer.trigger('change');

   });
</script>
</body>
</html>
