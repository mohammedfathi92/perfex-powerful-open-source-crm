<!-- Modal -->
<div class="modal fade" id="client_zip_payments" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open('admin/clients/zip_payments/'.$client->userid); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo _l('client_zip_payments'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php
                            if($client->company != ''){
                                $file_name = slug_it($client->company);
                            } else {
                                $file_name = slug_it(get_primary_contact_user_id($client->userid));
                            }
                            ?>
                        <?php
                            array_unshift($payment_modes,array('id'=>'','name'=>_l('client_zip_status_all')));
                            echo render_select('paymentmode',$payment_modes,array('id','name'),'client_zip_payment_modes'); ?>
                        <div class="clearfix mbot15"></div>
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
