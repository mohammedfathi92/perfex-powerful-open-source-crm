<div class="modal fade email-template" data-editor-id=".<?php echo 'tinymce-'.$proposal->id; ?>" id="proposal_send_to_customer" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <?php echo form_open('admin/proposals/send_to_email/'.$proposal->id); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close close-send-template-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="edit-title"><?php echo _l('proposal_send_to_email_title'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php
                        if($template_disabled){
                            echo '<div class="alert alert-danger">';
                            echo 'The email template <b><a href="'.admin_url('emails/email_template/'.$template_id).'" target="_blank">'.$template_system_name.'</a></b> is disabled. Click <a href="'.admin_url('emails/email_template/'.$template_id).'" target="_blank">here</a> to enable the email template in order to be sent successfully.';
                            echo '</div>';
                        }
                        ?>
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" name="attach_pdf" id="attach_pdf" checked>
                            <label for="attach_pdf"><?php echo _l('proposal_attach_pdf'); ?></label>
                        </div>
                        <?php echo render_input('cc','CC'); ?>
                        <h5 class="bold"><?php echo _l('proposal_preview_template'); ?></h5>
                        <hr />
                        <?php echo render_textarea('email_template_custom','',$template->message,array(),array(),'','tinymce-'.$proposal->id); ?>
                        <?php echo form_hidden('template_name',$template_name); ?>
                    </div>

                </div>
                <?php if(count($proposal->attachments) > 0){ ?>
                <hr />
                <div class="row">
                    <div class="col-md-12">
                        <h5 class="bold no-margin"><?php echo _l('include_attachments_to_email'); ?></h5>
                        <hr />
                        <?php foreach($proposal->attachments as $attachment) { ?>
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" <?php if(!empty($attachment['external'])){echo 'disabled';}; ?> value="<?php echo $attachment['id']; ?>" name="email_attachments[]">
                            <label for=""><a href="<?php echo site_url('download/file/sales_attachment/'.$attachment['attachment_key']); ?>"><?php echo $attachment['file_name']; ?></a></label>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default close-send-template-modal"><?php echo _l('close'); ?></button>
                <button type="submit" autocomplete="off" data-loading-text="<?php echo _l('wait_text'); ?>" class="btn btn-info"><?php echo _l('send'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
