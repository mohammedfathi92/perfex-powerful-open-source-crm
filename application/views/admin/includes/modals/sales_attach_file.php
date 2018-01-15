<div class="modal fade" tabindex="-1" id="sales_attach_file" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('invoice_attach_file'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo form_open_multipart('admin/misc/upload_sales_file',array('id'=>'sales-upload','class'=>'dropzone')); ?>
                        <input type="file" name="file" multiple />
                        <?php echo form_close(); ?>
                        <div class="row mtop15" id="sales_uploaded_files_preview">
                        </div>
                        <div class="text-center">
                        <div id="dropbox-chooser-sales"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
