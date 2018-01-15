<?php if(isset($client)){ ?>
<h4 class="customer-profile-group-heading"><?php echo _l('proposals'); ?></h4>
<?php if(has_permission('proposals','','create')){ ?>
<a href="<?php echo admin_url('proposals/proposal?rel_type=customer&rel_id='.$client->userid); ?>" class="btn btn-info mbot25<?php if($client->active == 0){echo ' disabled';} ?>"><?php echo _l('new_proposal'); ?></a>
<?php } ?>
<?php if(total_rows('tblproposals',array('rel_type'=>'customer','rel_id'=>$client->userid))> 0 && (has_permission('proposals','','create') || has_permission('proposals','','edit'))){ ?>
<a href="#" class="btn btn-info mbot25" data-toggle="modal" data-target="#sync_data_proposal_data"><?php echo _l('sync_data'); ?></a>
<?php $this->load->view('admin/proposals/sync_data',array('related'=>$client,'rel_id'=>$client->userid,'rel_type'=>'customer')); ?>
<?php } ?>
<?php
$table_data = array(
 _l('proposal') . ' #',
 _l('proposal_subject'),
 _l('proposal_total'),
 _l('proposal_date'),
 _l('proposal_open_till'),
 _l('tags'),
 _l('proposal_date_created'),
 _l('proposal_status'));
$custom_fields = get_custom_fields('proposal',array('show_on_table'=>1));
foreach($custom_fields as $field){
 array_push($table_data,$field['name']);
}
$table_data = do_action('proposals_relation_table_columns',$table_data);
render_datatable($table_data,'proposals-client-profile');
?>
<?php } ?>
