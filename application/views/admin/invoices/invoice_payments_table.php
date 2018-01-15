<div class="table-responsive">
    <table class="table table-hover no-mtop">
        <thead>
            <tr>
                <th><span class="bold"><?php echo _l('payments_table_number_heading'); ?></span></th>
                <th><span class="bold"><?php echo _l('payments_table_mode_heading'); ?></span></th>
                <th><span class="bold"><?php echo _l('payments_table_date_heading'); ?></span></th>
                <th><span class="bold"><?php echo _l('payments_table_amount_heading'); ?></span></th>
                <th><span class="bold"><?php echo _l('options'); ?></span></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($invoice->payments as $payment){ ?>
            <tr class="payment">
                <td><?php echo $payment['paymentid']; ?>
                    <?php echo icon_btn('payments/pdf/' . $payment['paymentid'], 'file-pdf-o','btn-default pull-right'); ?>
                </td>
                <td><?php echo $payment['name']; ?>
                    <?php if(!empty($payment['paymentmethod'])){
                        echo ' - ' . $payment['paymentmethod'];
                    }
                    if($payment['transactionid']){
                        echo '<br />'._l('payments_table_transaction_id',$payment['transactionid']);
                    }
                    ?>
                </td>
                <td><?php echo _d($payment['date']); ?></td>
                <td><?php echo format_money($payment['amount'],$invoice->symbol); ?></td>
                <td>
                    <a href="<?php echo admin_url('payments/payment/'.$payment['paymentid']); ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square-o"></i></a>
                    <?php if(has_permission('payments','','delete')){ ?>
                    <a href="<?php echo admin_url('invoices/delete_payment/'.$payment['paymentid'] . '/' . $payment['invoiceid']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
