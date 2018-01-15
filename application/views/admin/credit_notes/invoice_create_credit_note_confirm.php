<!-- Modal Confirm Credit Note Creation -->
<div class="modal fade" id="confirm_credit_note_create_from_invoice" data-balance-due="<?php echo $invoice->total_left_to_pay; ?>" tabindex="-1" role="dialog" aria-labelledby="modalLabelCreditNoteFromInvoice">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalLabelCreditNoteFromInvoice">
            <?php echo format_invoice_number($invoice->id); ?> - <?php echo _l('create_credit_note'); ?>
        </h4>
    </div>
    <div class="modal-body">
        <?php echo _l('confirm_invoice_credits_from_credit_note'); ?>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        <a href="#" class="btn btn-info" id="confirm-invoice-credit-note"><?php echo _l('confirm'); ?></a>
    </div>
</div>
</div>
</div>
