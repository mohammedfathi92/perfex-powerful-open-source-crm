<h4 class="customer-profile-group-heading"><?php echo _l('vault'); ?></h4>
<button class="btn btn-info mbot25" data-toggle="modal" data-target="#entryModal"><i class="fa fa-lock" aria-hidden="true"></i> <?php echo _l('new_vault_entry'); ?></button>
<?php if(count($vault_entries) == 0){ ?>
<div class="alert alert-info text-center">
   <?php echo _l('no_vault_entries'); ?>
</div>
<?php } ?>
<?php foreach($vault_entries as $entry){ ?>
<div class="panel panel-default panel-vault">
   <div class="panel-heading" role="tab" id="<?php echo 'vaultEntryHeading-'.$entry['id']; ?>">
      <h4 class="panel-title pull-left">
         <?php echo $entry['server_address']; ?>
      </h4>
      <?php if($entry['creator'] == get_staff_user_id() || is_admin()){ ?>
      <a href="<?php echo admin_url('clients/vault_entry_delete/'.$entry['id']); ?>" class="pull-right text-danger _delete"><i class="fa fa-remove"></i></a>
      <a href="#" onclick="edit_vault_entry(<?php echo $entry['id']; ?>); return false;" class="pull-right text-muted"><i class="fa fa-pencil-square-o mright10"></i></a>
      <?php } ?>
      <div class="clearfix"></div>
   </div>
   <div id="<?php echo 'vaultEntry-'.$entry['id']; ?>">
      <div class="panel-body">
         <div class="row">
            <div class="col-md-6 border-right">
                <p>
                  <b><?php echo _l('server_address'); ?>: </b><?php echo $entry['server_address']; ?>
               </p>
               <p>
                  <b><?php echo _l('port'); ?>: </b><?php echo !empty($entry['port']) ? $entry['port'] : _l('no_port_provided'); ?>
               </p>
               <p>
                  <b><?php echo _l('vault_username'); ?>: </b><?php echo $entry['username']; ?>
               </p>
               <p>
                  <b><?php echo _l('vault_password'); ?>: </b><span class="vault-password-fake">
                  <?php echo str_repeat('&bull;',10);?>  </span><span class="vault-password-encrypted"></span> <a href="#" class="vault-view-password mleft10" data-toggle="tooltip" data-title="<?php echo _l('view_password'); ?>" onclick="vault_re_enter_password(<?php echo $entry['id']; ?>,this); return false;"><i class="fa fa-lock" aria-hidden="true"></i></a>
               </p>
            </div>
            <div class="col-md-6 text-center">
               <?php if(!empty($entry['description'])){ ?>
               <p>
                  <b><?php echo _l('vault_description'); ?>: </b><br /><?php echo $entry['description']; ?>
               </p>
               <hr />
               <?php } ?>
               <p class="text-muted"><?php echo _l('vault_entry_created_from',$entry['creator_name']); ?> -
                <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($entry['date_created']); ?>">
                  <?php echo time_ago($entry['date_created']); ?>
                </span></p>
               <p>
              <?php if(!empty($entry['last_updated_from'])){ ?>
               <p class="text-muted no-mbot"><?php echo _l('vault_entry_last_update',$entry['last_updated_from']); ?> -
                <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($entry['last_updated']); ?>">
                <?php echo time_ago($entry['last_updated']); ?></p>
              </span>
               <p>
                  <?php } ?>
            </div>
         </div>
      </div>
   </div>
</div>
<?php } ?>
<div class="modal fade" id="entryModal" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
      <?php echo form_open(admin_url('clients/vault_entry_create/'.$client->userid),array('data-create-url'=>admin_url('clients/vault_entry_create/'.$client->userid),'data-update-url'=>admin_url('clients/vault_entry_update'))); ?>
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?php echo _l('vault_entry'); ?></h4>
         </div>
         <div class="modal-body">
             <!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
            <input  type="text" class="fake-autofill-field" name="fakeusernameremembered" value='' tabindex="-1"/>
            <input  type="password" class="fake-autofill-field" name="fakepasswordremembered" value='' tabindex="-1"/>
            <?php echo render_input('server_address','server_address'); ?>
            <?php echo render_input('port','port','','number'); ?>
            <?php echo render_input('username','vault_username'); ?>
            <?php echo render_input('password','vault_password','','password'); ?>
            <div id="vault_password_change_notice" class="help-block text-muted vault_password_change_notice hide"><small><?php echo _l('password_change_fill_notice'); ?></small></div>
            <?php echo render_textarea('description','vault_description'); ?>
            <hr />
            <div class="radio radio-info">
               <input type="radio" name="visibility" value="1" id="only_creator_visible_all" checked>
               <label for="only_creator_visible_all"><?php echo _l('vault_entry_visible_to_all'); ?></label>
            </div>
            <div class="radio radio-info">
               <input type="radio" name="visibility" value="2" id="only_creator_visible_administrators">
               <label for="only_creator_visible_administrators"><?php echo _l('vault_entry_visible_administrators'); ?></label>
            </div>
            <div class="radio radio-info">
               <input type="radio" name="visibility" value="3" id="only_creator_visible_me">
               <label for="only_creator_visible_me"><?php echo _l('vault_entry_visible_creator'); ?></label>
            </div>
            <hr />
            <div class="checkbox checkbox-info">
                <input type="checkbox" id="share_in_projects" name="share_in_projects">
                <label for="share_in_projects"><?php echo _l('vault_entry_share_on_projects'); ?></label>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
         </div>
      </div>
      <!-- /.modal-content -->
      <?php echo form_close(); ?>
   </div>
   <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<?php $this->load->view('admin/clients/vault_confirm_password'); ?>
<?php add_action('after_js_scripts_render','vault_form_validate');
function vault_form_validate(){ ?>
<script>
   var $entryModal = $('#entryModal');
   $(function(){

        _validate_form($entryModal.find('form'),{
           server_address:'required',
           username:'required',
           password:'required',
       });
        setTimeout(function(){
          $($entryModal.find('form')).trigger('reinitialize.areYouSure');
        },1000)
       $entryModal.on('hidden.bs.modal',function(){
           var $form = $entryModal.find('form');
           $form.attr('action',$form.data('create-url'));
           $form.find('input[type="text"]').val('');
           $form.find('input[type="radio"]:first').prop('checked',true);
           $form.find('textarea').val('');
           $('#vault_password_change_notice').addClass('hide');
           $form.find('#password').rules('add',{required:true});
           $form.find('#password').parents().find('.req').removeClass('hide');
           $form.find('#share_in_projects').prop('checked',false);
       });
   });
   function edit_vault_entry(id){
       $.get(admin_url+'clients/get_vault_entry/'+id,function(response){
           var $form = $entryModal.find('form');
           $form.attr('action',$form.data('update-url')+'/'+id);
           $form.find('#server_address').val(response.server_address);
           $form.find('#port').val(response.port);
           $form.find('#username').val(response.username);
           $form.find('#description').val(response.description);
           $form.find('#password').rules('remove');
           $form.find('#password').parents().find('.req').addClass('hide');
           $form.find('input[value="'+response.visibility+'"]').prop('checked',true);
           $form.find('#share_in_projects').prop('checked',(response.share_in_projects == 1 ? true : false));
           $('#vault_password_change_notice').removeClass('hide');
           $entryModal.modal('show');
       },'json');
   }
</script>
<?php } ?>
