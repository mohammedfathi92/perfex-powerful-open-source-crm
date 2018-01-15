<?php if(isset($client)){ ?>
<h4 class="customer-profile-group-heading"><?php echo _l('credit_notes'); ?></h4>
<div class="alert alert-warning">
  <?php echo _l('x_credits_available',format_money($credits_available,$customer_currency->symbol)); ?>
</div>
<?php if(has_permission('credit_notes','','create')){ ?>
<a href="<?php echo admin_url('credit_notes/credit_note?customer_id='.$client->userid); ?>" class="btn btn-info mbot25<?php if($client->active == 0){echo ' disabled';} ?>">
    <?php echo _l('new_credit_note'); ?>
</a>
<?php } ?>
<?php if(has_permission('credit_notes','','view') || has_permission('credit_notes','','view_own')){ ?>
<a href="#" class="btn btn-info mbot25" data-toggle="modal" data-target="#client_zip_credit_notes"><?php echo _l('zip_credit_notes'); ?></a>
<?php } ?>
<?php $this->load->view('admin/credit_notes/table_html');
include_once(APPPATH . 'views/admin/clients/modals/zip_credit_notes.php');
?>
<?php } ?>
