<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo $title; ?></h4>
                        <hr class="hr-panel-heading" />
                        <?php echo form_open($this->uri->uri_string()); ?>
                        <div class="form-group">
                            <label for="export_type"><?php echo _l('bulk_pdf_export_select_type'); ?></label>
                            <select name="export_type" id="export_type" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <option value=""></option>
                                <?php if(has_permission('invoices','','view') || has_permission('invoices','','view_own')){ ?>
                                <option value="invoices"><?php echo _l('bulk_export_pdf_invoices'); ?></option>
                                <?php } ?>
                                <?php if(has_permission('estimates','','view') || has_permission('estimates','','view_own')){ ?>
                                <option value="estimates"><?php echo _l('bulk_export_pdf_estimates'); ?></option>
                                <?php } ?>
                                <?php if(has_permission('payments','','view') || has_permission('invoices','','view_own')){ ?>
                                <option value="payments"><?php echo _l('bulk_export_pdf_payments'); ?></option>
                                <?php } ?>
                                <?php if(has_permission('credit_notes','','view') || has_permission('credit_notes','','view_own')){ ?>
                                <option value="credit_notes"><?php echo _l('credit_notes'); ?></option>
                                <?php } ?>
                                <?php if(has_permission('proposals','','view') || has_permission('proposals','','view_own')){ ?>
                                <option value="proposals"><?php echo _l('bulk_export_pdf_proposals'); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <?php echo render_date_input('date-from','zip_from_date'); ?>
                        <?php echo render_date_input('date-to','zip_to_date'); ?>
                        <?php echo render_input('tag','bulk_export_include_tag','','text',array('data-toggle'=>'tooltip','title'=>'bulk_export_include_tag_help')); ?>
                        <div class="form-group hide shifter" id="estimates_status">
                            <label for="estimate_zip_status"><?php echo _l('bulk_export_status'); ?></label>
                            <div class="radio radio-primary">
                                <input type="radio" value="estimates_all" checked name="estimate_export_status">
                                <label for="estimates_all"><?php echo _l('bulk_export_status_all'); ?></label>
                            </div>
                            <?php foreach($estimate_statuses as $status){ ?>
                            <div class="radio radio-primary">
                                <input type="radio" id="<?php echo format_estimate_status($status,'',false); ?>" value="<?php echo $status; ?>" name="estimate_export_status">
                                <label for="<?php echo format_estimate_status($status,'',false); ?>"><?php echo format_estimate_status($status,'',false); ?></label>
                            </div>
                            <?php } ?>
                        </div>

                         <div class="form-group hide shifter" id="credit_notes_status">
                            <label for="credit_notes_status_export"><?php echo _l('bulk_export_status'); ?></label>
                            <div class="radio radio-primary">
                                <input type="radio" id="all" value="all" checked name="credit_notes_status_export">
                                <label for="all"><?php echo _l('bulk_export_status_all'); ?></label>
                            </div>
                            <?php foreach($credit_notes_statuses as $status){ ?>
                            <div class="radio radio-primary">
                                <input type="radio" id="credit_note_<?php echo $status['id']; ?>" value="<?php echo $status['id']; ?>" name="credit_notes_status_export">
                                <label for="credit_note_<?php echo $status['id']; ?>"><?php echo $status['name']; ?></label>
                            </div>
                            <?php } ?>
                        </div>

                        <div class="form-group hide shifter" id="invoices_status">
                            <label for="invoice_export_status"><?php echo _l('bulk_export_status'); ?></label>
                            <div class="radio radio-primary">
                                <input type="radio" id="all" value="all" checked name="invoice_export_status">
                                <label for="all"><?php echo _l('bulk_export_status_all'); ?></label>
                            </div>
                            <?php foreach($invoice_statuses as $status){ ?>
                            <div class="radio radio-primary">
                                <input type="radio" id="invoice_<?php echo format_invoice_status($status,'',false); ?>" value="<?php echo $status; ?>" name="invoice_export_status">
                                <label for="invoice_<?php echo format_invoice_status($status,'',false); ?>"><?php echo format_invoice_status($status,'',false); ?></label>
                            </div>
                            <?php } ?>
                        </div>
                        <div class="form-group hide shifter" id="proposal_status">
                            <label for="proposal_export_status"><?php echo _l('bulk_export_status'); ?></label>
                            <div class="radio radio-primary">
                                <input type="radio" value="all" checked name="proposal_export_status">
                                <label for="all"><?php echo _l('bulk_export_status_all'); ?></label>
                            </div>
                            <?php foreach($proposal_statuses as $status){
                                if($status == 0){continue;}
                                ?>
                                <div class="radio radio-primary">
                                    <input type="radio" value="<?php echo $status; ?>" name="proposal_export_status" id="proposal_<?php echo format_proposal_status($status,'',false); ?>">
                                    <label for="proposal_<?php echo format_proposal_status($status,'',false); ?>"><?php echo format_proposal_status($status,'',false); ?></label>
                                </div>
                                <?php } ?>
                            </div>
                            <div class="form-group hide shifter" id="payment_modes">
                                <?php
                                array_unshift($payment_modes,array('id'=>'','name'=>_l('bulk_export_status_all')));
                                echo render_select('paymentmode',$payment_modes,array('id','name'),'bulk_export_zip_payment_modes');
                                ?>
                            </div>
                            <button class="btn btn-info" type="submit"><?php echo _l('bulk_pdf_export_button'); ?></button>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php init_tail(); ?>
    <script>
        $(function(){
            _validate_form($('form'),{export_type:'required'});
            $('#export_type').on('change',function(){
                var val = $(this).val();
                $('.shifter').addClass('hide');
                if(val == 'invoices'){
                   $('#invoices_status').removeClass('hide');
               } else if(val == 'estimates'){
                   $('#estimates_status').removeClass('hide');
               } else if(val == 'payments'){
                $('#payment_modes').removeClass('hide');
            } else if(val == 'proposals'){
                $('#proposal_status').removeClass('hide');
            } else if(val == 'credit_notes'){
                $('#credit_notes_status').removeClass('hide');
            }
        });
        });
    </script>
</body>
</html>
