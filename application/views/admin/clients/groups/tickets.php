<h4 class="customer-profile-group-heading"><?php echo _l('contracts_tickets_tab'); ?></h4>
<div class="clearfix"></div>
<?php
if(isset($client)){
    $total_active_contacts = total_rows('tblcontacts',array('active'=>1,'userid'=>$client->userid));
    if(($total_active_contacts > 0)
        && ((get_option('access_tickets_to_none_staff_members') == 1 && !is_staff_member()) || is_staff_member())){
        echo '<div class="btn-group">';
        echo '<a href="'.($total_active_contacts > 1 ? '#' : admin_url('tickets/add?contact_id='.$contacts[0]['id'].'&userid='.$client->userid)).'" class="btn btn-info'.($total_active_contacts > 1 ? ' dropdown-toggle' : '').'"'.($total_active_contacts > 1 ? ' data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"' : '').'>';
        echo _l('new_ticket');
        if($total_active_contacts > 1) {
            echo ' <span class="caret"></span>';
        }
        echo '</a>';
        if($total_active_contacts > 1) {
            echo '<ul class="dropdown-menu width300">';
            foreach($contacts as $contact) {
                if($contact['active'] == 1) {
                    echo '<li><a href="'.admin_url('tickets/add?contact_id='.$contact['id'].'&userid='.$client->userid).'"> '.$contact['firstname'] .' ' . $contact['lastname'] . ' ('.$contact['email'].')</a></li>';
                }
            }
            echo '</ul>';
        }
        echo '</div>';
    }

    echo '<div class="row mtop20">';
     echo '<div class="col-md-12">';
         echo AdminTicketsTableStructure('table-tickets-single');
     echo '</div>';
    echo '</div>';
} ?>
