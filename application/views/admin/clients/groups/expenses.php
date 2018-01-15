<?php if(isset($client)){ ?>
<h4 class="customer-profile-group-heading"><?php echo _l('client_expenses_tab'); ?></h4>
<?php if(has_permission('expenses','','create')){ ?>
<a href="<?php echo admin_url('expenses/expense?customer_id='.$client->userid); ?>" class="btn btn-info mbot25<?php if($client->active == 0){echo ' disabled';} ?>"><?php echo _l('new_expense'); ?></a>
<?php } ?>
<div id="expenses_total"></div>
<?php
$table_data = array(
    '#',
    _l('expense_dt_table_heading_category'),
    _l('expense_dt_table_heading_amount'),
    _l('expense_name'),
    _l('expense_receipt'),
    _l('expense_dt_table_heading_date'),
    _l('project'),
     array(
       'name'=>_l('expense_dt_table_heading_customer'),
       'th_attrs'=>array('class'=>'not_visible')
       ),
    _l('invoice'),
    _l('expense_dt_table_heading_reference_no'),
    _l('expense_dt_table_heading_payment_mode'));

$custom_fields = get_custom_fields('expenses',array('show_on_table'=>1));
foreach($custom_fields as $field){
    array_push($table_data,$field['name']);
}
$table_data = do_action('expenses_table_columns',$table_data);
render_datatable($table_data, 'expenses-single-client');
?>

<?php } ?>
