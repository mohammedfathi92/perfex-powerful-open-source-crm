<div class="modal fade" id="vaultConfirmPassword" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
      <?php echo form_open(admin_url('clients/vault_encrypt_password')); ?>
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?php echo _l('view_password'); ?></h4>
         </div>
         <div class="modal-body">
            <p class="bold"><?php echo _l('security_reasons_re_enter_password'); ?></p>
            <?php echo render_input('user_password','','','password',array('data-ays-ignore'=>'true')); ?>
            <input type="hidden" name="id" data-ays-ignore="true">
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <button type="submit" class="btn btn-info"><?php echo _l('confirm'); ?></button>
         </div>
      </div>
      <!-- /.modal-content -->
      <?php echo form_close(); ?>
   </div>
   <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<script>
// Show vault entry modal user to re-enter his password
function vault_re_enter_password(id, e) {
    var invoker = $(e);
    var vaultEntry = $('#vaultEntry-' + id);
    var $confirmPasswordVaultModal = $('#vaultConfirmPassword');

    _validate_form($confirmPasswordVaultModal.find('form'), {
        user_password: 'required'
    }, vault_encrypt_password);

    if (!invoker.hasClass('decrypted')) {
        $confirmPasswordVaultModal.find('form input[name="id"]').val(id);
        $confirmPasswordVaultModal.modal('show');
    } else {
        invoker.removeClass('decrypted');
        vaultEntry.find('.vault-password-fake').removeClass('hide');
        vaultEntry.find('.vault-password-encrypted').addClass('hide');
    }
}

// Used to encrypt vault entry password
function vault_encrypt_password(form) {

    var $form = $(form);
    var vaultEntry = $('#vaultEntry-' + $form.find('input[name="id"]').val());
    var data = $form.serialize();
    var $confirmPasswordVaultModal = $('#vaultConfirmPassword');

    $.post($form.attr('action'), data).done(function(response) {
        response = JSON.parse(response);
        vaultEntry.find('.vault-password-fake').addClass('hide');
        vaultEntry.find('.vault-view-password').addClass('decrypted');
        vaultEntry.find('.vault-password-encrypted').removeClass('hide').html(response.password);
        $confirmPasswordVaultModal.modal('hide');
        $confirmPasswordVaultModal.find('input[name="user_password"]').val('');
    }).fail(function(error) {
        alert_float('danger', JSON.parse(error.responseText).error_msg);
    });

    return false;
}
</script>
