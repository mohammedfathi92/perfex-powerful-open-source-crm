<!-- Zip Credit Note -->
<div class="modal fade" id="client_zip_credit_notes" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open('admin/clients/zip_credit_notes/'.$client->userid); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo _l('zip_credit_notes'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="credit_note_zip_status"><?php echo _l('credit_note_status'); ?></label>
                            <div class="radio radio-primary">
                                <input type="radio" value="all" id="all" checked name="credit_note_zip_status">
                                <label for="all"><?php echo _l('client_zip_status_all'); ?></label>
                            </div>
                            <?php foreach($credit_notes_statuses as $status){ ?>
                            <div class="radio radio-primary">
                                <input type="radio" value="<?php echo $status['id']; ?>" id="crn_<?php echo $status['id']; ?>" name="credit_note_zip_status">
                                <label for="crn_<?php echo $status['id']; ?>"><?php echo $status['name']; ?></label>
                            </div>
                            <?php } ?>
                        </div>
                        <?php
                        if($client->company != ''){
                            $file_name = slug_it($client->company);
                        } else {
                            $file_name = slug_it(get_contact_full_name(get_primary_contact_user_id($client->userid)));
                        }
                        ?>
                        <?php include(APPPATH .'views/admin/clients/modals/modal_zip_date_picker.php'); ?>
                        <?php echo form_hidden('file_name',$file_name); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
