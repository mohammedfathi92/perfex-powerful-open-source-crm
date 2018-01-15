  <div class="modal fade" tabindex="-1" role="dialog" id="identityConfirmationModal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
       <?php echo form_open((isset($formAction) ? $formAction : $this->uri->uri_string()), array('id'=>'identityConfirmationForm')); ?>
       <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo _l('confirmation_of_identity'); ?></h4>
      </div>
      <div class="modal-body">
        <?php do_action('before_confirmation_identity_fields'); ?>
        <?php if(isset($formData)){echo $formData;}; ?>
        <div id="identity_fields">
          <div class="form-group">
            <label for="acceptance_firstname" class="control-label"><?php echo _l('client_firstname'); ?></label>
            <input type="text" name="acceptance_firstname" id="acceptance_firstname" class="form-control" required="true" value="<?php echo (isset($contact) ? $contact->firstname : '') ?>">
          </div>
          <div class="form-group">
            <label for="acceptance_lastname" class="control-label"><?php echo _l('client_lastname'); ?></label>
            <input type="text" name="acceptance_lastname" id="acceptance_lastname" class="form-control" required="true" value="<?php echo (isset($contact) ? $contact->lastname : '') ?>">
          </div>
          <div class="form-group">
            <label for="acceptance_email" class="control-label"><?php echo _l('client_email'); ?></label>
            <input type="email" name="acceptance_email" id="acceptance_email" class="form-control" required="true" value="<?php echo (isset($contact) ? $contact->email : '') ?>">
          </div>
        </div>
        <?php do_action('after_confirmation_identity_fields'); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        <button type="submit" data-loading-text="<?php echo _l('wait_text'); ?>" autocomplete="off" data-form="#identityConfirmationForm" class="btn btn-info"><?php echo _l('confirm'); ?></button>
      </div>
      <?php echo form_close(); ?>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
