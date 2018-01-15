<h4 class="customer-profile-group-heading"><?php echo _l('tasks'); ?></h4>
<?php if(isset($client)){
    init_relation_tasks_table(array( 'data-new-rel-id'=>$client->userid,'data-new-rel-type'=>'customer'));
} ?>
