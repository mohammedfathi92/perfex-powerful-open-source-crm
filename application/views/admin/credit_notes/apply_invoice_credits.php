<?php if((credits_can_be_applied_to_invoice($invoice->status) && $credits_available > 0)) { ?>
<!-- Modal Apply Credits -->
<div class="modal fade apply-credits-from-invoice" id="apply_credits" data-balance-due="<?php echo $invoice->total_left_to_pay; ?>" tabindex="-1" role="dialog" aria-labelledby="modalLabelApplyCredits">
  <div class="modal-dialog modal-lg" role="document">
    <?php echo form_open(admin_url('invoices/apply_credits/'.$invoice->id),array('id'=>'apply_credits_form')); ?>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalLabelApplyCredits">
            <?php echo format_invoice_number($invoice->id); ?> - <?php echo _l('apply_credits'); ?>
        </h4>
    </div>
    <div class="modal-body">
        <div class="table-responsive credits-table">
            <table class="table table-bordered no-mtop">
                <thead>
                   <tr>
                    <th><span class="bold"><?php echo _l('credit_note'); ?> #</span></th>
                    <th><span class="bold"><?php echo _l('credit_note_date'); ?></span></th>
                    <?php
                        $custom_fields = get_custom_fields('credit_note',array('show_on_table'=>1));
                        foreach($custom_fields as $field){
                          echo '<td class="bold">' . $field['name'] .'</td>';
                        }
                    ?>
                    <th><span class="bold"><?php echo _l('credit_amount'); ?></span></th>
                    <th><span class="bold"><?php echo _l('credits_available'); ?></span></th>
                    <th><span class="bold"><?php echo _l('amount_to_credit'); ?></span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($open_credits as $credit) { ?>
                <tr>
                    <td><a href="<?php echo admin_url('credit_notes/list_credit_notes/'.$credit['id']); ?>" target="_blank"><?php echo format_credit_note_number($credit['id']); ?></a></td>
                    <td><?php echo _d($credit['date']); ?></td>
                    <?php
                        foreach($custom_fields as $field){
                          echo '<td>' .get_custom_field_value($credit['id'],$field['id'],'credit_note') .'</td>';
                        }
                    ?>
                    <td><?php echo format_money($credit['total'],$customer_currency->symbol) ?></td>
                    <td><?php echo format_money($credit['available_credits'],$customer_currency->symbol) ?></td>
                    <td>
                        <input type="number" max="<?php echo $credit['available_credits']; ?>" name="amount[<?php echo $credit['id']; ?>]" class="form-control apply-credits-field" value="0">
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <div class="row">
        <div class="col-md-6 col-md-offset-6">
            <div class="text-right">
                <table class="table">
                    <tbody>
                     <tr>
                        <td class="bold"><?php echo _l('amount_to_credit'); ?>:</td>
                        <td class="amount-to-credit">
                            <?php echo _format_number(0); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="bold"><?php echo _l('balance_due'); ?>:</td>
                        <td class="invoice-balance-due">
                            <?php echo _format_number($invoice->total_left_to_pay); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
    <button type="submit" class="btn btn-info"><?php echo _l('apply'); ?></button>
</div>
</div>
<?php echo form_close(); ?>
</div>
</div>
<script>
    $(function(){
        _validate_form('#apply_credits_form');
    });
</script>
<?php } ?>
