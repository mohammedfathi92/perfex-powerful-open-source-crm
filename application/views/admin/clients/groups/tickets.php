<h4 class="customer-profile-group-heading"><?php echo _l('contracts_tickets_tab'); ?></h4>
<div class="clearfix"></div>
<?php
if(isset($client)){
    if((total_rows('tblcontacts',array('active'=>1,'userid'=>$client->userid)) == 1) && ((get_option('access_tickets_to_none_staff_members') == 1 && !is_staff_member()) || is_staff_member())){
        // $contacts in this case will be only 1 active
        echo '<a href="'.admin_url('tickets/add?contact_id='.$contacts[0]['id'].'&userid='.$client->userid).'" class="mbot20 btn btn-info">'._l('new_ticket').'</a>';
    }
 echo AdminTicketsTableStructure('table-tickets-single');
} ?>
