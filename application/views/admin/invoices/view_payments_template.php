<div class="panel_s">
    <div class="panel-body">
        <?php
        $total_payments = count($invoice->payments);
        if($total_payments > 0){ ?>
        <h4 class="bold"><?php echo _l('invoice_payments_received'); ?></h4>
        <?php include_once(APPPATH . 'views/admin/invoices/invoice_payments_table.php'); ?>
        <?php } else { ?>
        <h5 class="bold mtop15 pull-left"><?php echo _l('no_payments_found'); ?></h5>
        <?php } ?>
        <a href="#" class="btn btn-default pull-right" onclick="init_invoice(<?php echo $invoiceid; ?>); return false;"><?php echo _l('go_back'); ?></a>
    </div>
</div>
