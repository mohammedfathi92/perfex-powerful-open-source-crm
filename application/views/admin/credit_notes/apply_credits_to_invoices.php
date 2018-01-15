<?php if($credit_note->status == 1) { ?>
<!-- Modal Apply Credits -->
<div class="modal fade apply-credits-to-invoice" id="apply_credits" data-credits-remaining="<?php echo $credit_note->remaining_credits; ?>" tabindex="-1" role="dialog" aria-labelledby="modalLabelApplyCredits">
  <div class="modal-dialog modal-lg" role="document">
    <?php echo form_open(admin_url('credit_notes/apply_credits_to_invoices/'.$credit_note->id),array('id'=>'apply_credits_form')); ?>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalLabelApplyCredits">
            <?php echo _l('apply_credits_from',format_credit_note_number($credit_note->id)); ?>
        </h4>
    </div>
    <div class="modal-body">
        <?php if(count($available_creditable_invoices) > 0) {?>
        <div class="table-responsive credits-table">
            <table class="table table-bordered no-mtop">
                <thead>
                   <tr>
                    <th><span class="bold"><?php echo _l('credit_invoice_number'); ?> #</span></th>
                    <th><span class="bold"><?php echo _l('credit_invoice_date'); ?></span></th>
                    <th><span class="bold"><?php echo _l('payment_table_invoice_amount_total'); ?></span></th>
                    <th><span class="bold"><?php echo _l('invoice'); ?> <?php echo _l('balance_due'); ?></span></th>
                    <th><span class="bold"><?php echo _l('amount_to_credit'); ?></span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($available_creditable_invoices as $invoice) { ?>
                <tr>
                    <td><a href="<?php echo admin_url('invoices/list_invoices/'.$invoice['id']); ?>" target="_blank"><?php echo format_invoice_number($invoice['id']); ?></a></td>
                    <td><?php echo _d($invoice['date']); ?></td>
                    <td><?php echo format_money($invoice['total'],$invoice['symbol']) ?></td>
                    <td><?php echo format_money($invoice['total_left_to_pay'],$invoice['symbol']) ?></td>
                    <td>
                        <input type="number" name="amount[<?php echo $invoice['id']; ?>]" class="form-control apply-credits-field" value="0">
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
                        <td class="bold"><?php echo _l('credit_note_remaining_credits'); ?>:</td>
                        <td class="credit-note-balance-due">
                            <?php echo _format_number($credit_note->remaining_credits); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php } else { ?>
<p class="bold no-mbot"><?php echo _l('credit_note_no_invoices_available'); ?></p>
<?php } ?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
    <?php if(count($available_creditable_invoices) > 0) { ?>
    <button type="submit" class="btn btn-info"><?php echo _l('apply'); ?></button>
    <?php } ?>
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
