<div class="modal fade email-template" data-editor-id=".<?php echo 'tinymce-'.$credit_note->id; ?>" id="credit_note_send_to_client_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <?php echo form_open('admin/credit_notes/send_to_email/'.$credit_note->id); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <?php echo _l('credit_note_send_to_client_modal_heading'); ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php
                            if($template_disabled){
                                echo '<div class="alert alert-danger">';
                                echo 'The email template <b><a href="'.admin_url('emails/email_template/'.$template_id).'" target="_blank">'.$template_system_name.'</a></b> is disabled. Click <a href="'.admin_url('emails/email_template/'.$template_id).'" target="_blank">here</a> to enable the email template in order to be sent successfully.';
                                echo '</div>';
                            }

                            $selected = array();
                            $contacts = $this->clients_model->get_contacts($credit_note->clientid,array('active'=>1,'credit_note_emails'=>1));

                            foreach($contacts as $contact){
                                array_push($selected,$contact['id']);
                            }

                            echo render_select('sent_to[]',$contacts,array('id','email','firstname,lastname'),'invoice_estimate_sent_to_email',$selected,array('multiple'=>true),array(),'','',false);

                            ?>
                        </div>
                        <?php echo render_input('cc','CC'); ?>
                        <hr />
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" name="attach_pdf" id="attach_pdf" checked>
                            <label for="attach_pdf"><?php echo _l('invoice_send_to_client_attach_pdf'); ?></label>
                        </div>
                        <h5 class="bold"><?php echo _l('invoice_send_to_client_preview_template'); ?></h5>
                        <hr />
                        <?php echo render_textarea('email_template_custom','',$template->message,array(),array(),'','tinymce-'.$credit_note->id); ?>
                        <?php echo form_hidden('template_name',$template_name); ?>
                    </div>
                </div>

                <?php if(count($credit_note->attachments) > 0){ ?>
                <hr />
                <div class="row">
                    <div class="col-md-12">
                        <h5 class="bold no-margin"><?php echo _l('include_attachments_to_email'); ?></h5>
                        <hr />
                        <?php foreach($credit_note->attachments as $attachment) {
                            ?>
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
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <button type="submit" autocomplete="off" data-loading-text="<?php echo _l('wait_text'); ?>" class="btn btn-info"><?php echo _l('send'); ?></button>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
