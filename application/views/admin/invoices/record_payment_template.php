<div class="col-md-12 no-padding animated fadeIn">
    <div class="panel_s">
        <?php echo form_open('admin/invoices/record_payment',array('id'=>'record_payment_form')); ?>
        <?php echo form_hidden('invoiceid',$invoice->id); ?>
        <div class="panel-body">
            <h4 class="no-margin"><?php echo _l('record_payment_for_invoice'); ?> <?php echo format_invoice_number($invoice->id); ?></h4>
           <hr class="hr-panel-heading" />
            <div class="row">
                <div class="col-md-6">
                    <?php
                    $amount = $invoice->total_left_to_pay;
                    echo render_input('amount','record_payment_amount_received',$amount,'number',array('max'=>$amount)); ?>
                    <?php echo render_date_input('date','record_payment_date',_d(date('Y-m-d'))); ?>
                    <div class="form-group">
                        <label for="paymentmode" class="control-label"><?php echo _l('payment_mode'); ?></label>
                        <select class="selectpicker" name="paymentmode" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                            <option value=""></option>
                            <?php foreach($payment_modes as $mode){ ?>
                            <?php if(is_payment_mode_allowed_for_invoice($mode['id'],$invoice->id)){ ?>
                            <option value="<?php echo $mode['id']; ?>"><?php echo $mode['name']; ?></option>
                            <?php } ?>
                            <?php } ?>
                        </select>
                    </div>
                    <?php if(total_rows('tblemailtemplates',array('slug'=>'invoice-payment-recorded','active'=>0)) == 0){ ?>
                    <div class="checkbox checkbox-primary mtop15 inline-block">
                        <input type="checkbox" name="do_not_send_email_template" id="do_not_send_email_template">
                        <label for="do_not_send_email_template"><?php echo _l('do_not_send_invoice_payment_email_template_contact'); ?></label>
                    </div>
                    <?php } ?>
                    <div class="checkbox checkbox-primary mtop15 do_not_redirect hide inline-block">
                        <input type="checkbox" name="do_not_redirect" id="do_not_redirect" checked>
                        <label for="do_not_redirect"><?php echo _l('do_not_redirect_payment'); ?></label>
                    </div>

                </div>
                <div class="col-md-6">
                    <label for="note" class="control-label"><?php echo _l('record_payment_leave_note'); ?></label>
                    <textarea name="note" class="form-control" rows="8" placeholder="<?php echo _l('invoice_record_payment_note_placeholder'); ?>" id="note"></textarea>
                </div>
            </div>
            <div class="pull-right mtop15">
                <a href="#" class="btn btn-danger" onclick="init_invoice(<?php echo $invoice->id; ?>); return false;"><?php echo _l('cancel'); ?></a>
                <button type="submit" autocomplete="off" data-loading-text="<?php echo _l('wait_text'); ?>" data-form="#record_payment_form" class="btn btn-success"><?php echo _l('submit'); ?></button>
            </div>
            <?php
            if($payments){ ?>
            <div class="mtop25 inline-block full-width">
                <h5 class="bold"><?php echo _l('invoice_payments_received'); ?></h5>
                <?php include_once(APPPATH . 'views/admin/invoices/invoice_payments_table.php'); ?>
            </div>
            <?php } ?>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<script>
   $(function(){
     init_selectpicker();
     init_datepicker();
     _validate_form($('#record_payment_form'),{amount:'required',date:'required',paymentmode:'required'});
     var $sMode = $('select[name="paymentmode"]');
     var total_available_payment_modes = $sMode.find('option').length - 1;
     if(total_available_payment_modes == 1) {
        $sMode.selectpicker('val',$sMode.find('option').eq(1).attr('value'));
     }
 });
</script>
