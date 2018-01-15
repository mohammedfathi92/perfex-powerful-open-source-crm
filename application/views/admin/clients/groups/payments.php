<?php if(isset($client)){ ?>
<h4 class="customer-profile-group-heading"><?php echo _l('client_payments_tab'); ?></h4>
<a href="#" class="btn btn-info mbot25" data-toggle="modal" data-target="#client_zip_payments"><?php echo _l('zip_payments'); ?></a>
<?php render_datatable(array(
   _l('payments_table_number_heading'),
   _l('payments_table_invoicenumber_heading'),
   _l('payments_table_mode_heading'),
   _l('payment_transaction_id'),
   array(
       'name'=>_l('payments_table_client_heading'),
       'th_attrs'=>array('class'=>'not_visible')
   ),
   _l('payments_table_amount_heading'),
   _l('payments_table_date_heading'),
   _l('options')
   ),'payments-single-client'); ?>
<?php include_once(APPPATH . 'views/admin/clients/modals/zip_payments.php'); ?>
<?php } ?>
