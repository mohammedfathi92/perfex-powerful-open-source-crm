<?php if(isset($client)){ ?>
<h4 class="customer-profile-group-heading"><?php echo _l('contracts_invoices_tab'); ?></h4>
<?php if(has_permission('contracts','','create')){ ?>
<a href="<?php echo admin_url('contracts/contract?customer_id='.$client->userid); ?>" class="btn btn-info mbot25<?php if($client->active == 0){echo ' disabled';} ?>"><?php echo _l('new_contract'); ?></a>
<div class="clearfix"></div>
<?php } ?>
<?php
$table_data = array(
 '#',
 _l('contract_list_subject'),
 array(
   'name'=>_l('contract_list_client'),
   'th_attrs'=>array('class'=>'not_visible')
   ),
 _l('contract_types_list_name'),
 _l('contract_value'),
 _l('contract_list_start_date'),
 _l('contract_list_end_date'),
 );
$custom_fields = get_custom_fields('contracts',array('show_on_table'=>1));
foreach($custom_fields as $field){
 array_push($table_data,$field['name']);
}
$table_data = do_action('contracts_table_columns',$table_data);

array_push($table_data,_l('options'));
render_datatable($table_data, 'contracts-single-client'); ?>
<?php } ?>
