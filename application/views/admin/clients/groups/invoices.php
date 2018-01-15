<?php if(isset($client)){ ?>
<h4 class="customer-profile-group-heading"><?php echo _l('client_invoices_tab'); ?></h4>
<div id="invoices_total" class="mbot20"></div>
<?php if(has_permission('invoices','','create')){ ?>
<a href="<?php echo admin_url('invoices/invoice?customer_id='.$client->userid); ?>" class="btn btn-info mbot25<?php if($client->active == 0){echo ' disabled';} ?>"><?php echo _l('create_new_invoice'); ?></a>
<?php } ?>
<?php if(has_permission('invoices','','view') || has_permission('invoices','','view_own')){ ?>
<a href="#" class="btn btn-info mbot25" data-toggle="modal" data-target="#client_zip_invoices"><?php echo _l('zip_invoices'); ?></a>
<?php

$table_data = array(
    _l('invoice_dt_table_heading_number'),
    _l('invoice_dt_table_heading_amount'),
    _l('invoice_total_tax'),
    array(
       'name'=>_l('invoice_estimate_year'),
       'th_attrs'=>array('class'=>'not_visible')
       ),
    _l('invoice_dt_table_heading_date'),
    array(
       'name'=>_l('invoice_dt_table_heading_client'),
       'th_attrs'=>array('class'=>'not_visible')
       ),
    _l('project'),
    _l('invoice_dt_table_heading_duedate'),
    _l('invoice_dt_table_heading_status'));

$custom_fields = get_custom_fields('invoice',array('show_on_table'=>1));
foreach($custom_fields as $field){
    array_push($table_data,$field['name']);
}

$table_data = do_action('invoices_table_columns',$table_data);

render_datatable($table_data, 'invoices-single-client');
include_once(APPPATH . 'views/admin/clients/modals/zip_invoices.php');
?>
<?php } ?>
<?php } ?>
