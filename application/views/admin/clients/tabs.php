<?php
    $customer_tabs = get_customer_profile_tabs($client->userid);
?>
<ul class="nav navbar-pills nav-tabs nav-stacked customer-tabs" role="tablist">
   <?php
   $visible_customer_profile_tabs = get_option('visible_customer_profile_tabs');
   if($visible_customer_profile_tabs != 'all') {
      $visible_customer_profile_tabs = unserialize($visible_customer_profile_tabs);
   }
   foreach($customer_tabs as $tab){
      if((isset($tab['visible']) && $tab['visible'] == true) || !isset($tab['visible'])){

        // Check visibility from settings too
        if(is_array($visible_customer_profile_tabs) && $tab['name'] != 'profile') {
          if(!in_array($tab['name'], $visible_customer_profile_tabs)) {
            continue;
          }
        }
        ?>
      <li class="<?php if($tab['name'] == 'profile'){echo 'active ';} ?>customer_tab_<?php echo $tab['name']; ?>">
        <a data-group="<?php echo $tab['name']; ?>" href="<?php echo $tab['url']; ?>"><i class="<?php echo $tab['icon']; ?> menu-icon" aria-hidden="true"></i><?php echo $tab['lang']; ?>
            <?php if(isset($tab['id']) && $tab['id'] == 'reminders'){
              $total_reminders = total_rows('tblreminders',
                  array(
                   'isnotified'=>0,
                   'staff'=>get_staff_user_id(),
                   'rel_type'=>'customer',
                   'rel_id'=>$client->userid
                   )
                  );
              if($total_reminders > 0){
                echo '<span class="badge">'.$total_reminders.'</span>';
              }
          }
          ?>
      </a>
  </li>
  <?php } ?>
  <?php } ?>
</ul>
