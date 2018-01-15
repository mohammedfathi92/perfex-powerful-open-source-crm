<!-- Client send file modal -->
<div class="modal fade" id="send_file" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open('admin/misc/send_file/'); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo _l('send_file'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo render_input('send_file_subject','send_file_subject'); ?>
                        <?php echo render_input('send_file_email','send_file_email'); ?>
                        <?php echo render_textarea('send_file_message','send_file_message',get_option('email_signature')); ?>
                        <?php echo form_hidden('file_path',''); ?>
                        <?php echo form_hidden('filetype',''); ?>
                        <?php echo form_hidden('file_name',''); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('send_file'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
