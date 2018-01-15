<?php
   $total_qa_removed = 0;
   $quickActions = $this->perfex_base->get_quick_actions_links();
   foreach($quickActions as $key => $item){
     if(isset($item['permission'])){
      if(!has_permission($item['permission'],'','create')){
        $total_qa_removed++;
      }
    }
   }
   ?>
<aside id="menu" class="sidebar">
   <ul class="nav metis-menu" id="side-menu">
      <li class="dashboard_user<?php if($total_qa_removed == count($quickActions)){echo ' dashboard-user-no-qa';}?>">
         <?php echo _l('welcome_top',$current_user->firstname); ?> <i class="fa fa-power-off top-left-logout pull-right" data-toggle="tooltip" data-title="<?php echo _l('nav_logout'); ?>" data-placement="right" onclick="logout(); return false;"></i>
      </li>
      <?php if($total_qa_removed != count($quickActions)){ ?>
      <li class="quick-links">
         <div class="dropdown dropdown-quick-links">
            <a href="#" class="dropdown-toggle" id="dropdownQuickLinks" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
            <i class="fa fa-gavel" aria-hidden="true"></i>
            </a>
            <ul class="dropdown-menu" aria-labelledby="dropdownQuickLinks">
               <?php
                  foreach($quickActions as $key => $item){
                    $url = '';
                    if(isset($item['permission'])){
                      if(!has_permission($item['permission'],'','create')){
                       continue;
                     }
                   }
                   if(isset($item['custom_url'])){
                     $url = $item['url'];
                   } else {
                     $url = admin_url(''.$item['url']);
                   }
                   $href_attributes = '';
                   if(isset($item['href_attributes'])){
                     foreach ($item['href_attributes'] as $key => $val) {
                       $href_attributes .= $key . '=' . '"' . $val . '"';
                     }
                   }
                   ?>
               <li>
                  <a href="<?php echo $url; ?>" <?php echo $href_attributes; ?>>
                  <i class="fa fa-plus-square-o"></i>
                  <?php echo $item['name']; ?></a>
               </li>
               <?php } ?>
            </ul>
         </div>
      </li>
      <?php } ?>
      <?php
         do_action('before_render_aside_menu');
         $menu_active = get_option('aside_menu_active');
         $menu_active = json_decode($menu_active);
         $m = 0;
         foreach($menu_active->aside_menu_active as $item){
           if($item->id == 'tickets' && (get_option('access_tickets_to_none_staff_members') == 0 && !is_staff_member())){
            continue;
          } elseif($item->id == 'customers'){
            if(!has_permission('customers','','view') && (have_assigned_customers() || (!have_assigned_customers() && has_permission('customers','','create')))){
              $item->permission = '';
            }
          } elseif($item->id == 'child-proposals'){
           if((total_rows('tblproposals',array('assigned'=>get_staff_user_id())) > 0
             && get_option('allow_staff_view_proposals_assigned') == 1)
             && (!has_permission('proposals','','view')
               && !has_permission('proposals','','view_own'))){
             $item->permission = '';
         }
         }
         if(!empty($item->permission)
         && !has_permission($item->permission,'','view')
         && !has_permission($item->permission,'','view_own')){
         continue;
         }
         $submenu = false;
         $remove_main_menu = false;
         $url = '';
         if(isset($item->children)){
         $submenu = true;
         $total_sub_items_removed = 0;
         foreach($item->children as $_sub_menu_check){
         if(!empty($_sub_menu_check->permission)
          && ($_sub_menu_check->permission != 'payments'
            && $_sub_menu_check->permission != 'tickets'
            && $_sub_menu_check->permission != 'customers'
            && $_sub_menu_check->permission != 'proposals')
         ){
           if(!has_permission($_sub_menu_check->permission,'','view')
            && !has_permission($_sub_menu_check->permission, '', 'view_own')){
             $total_sub_items_removed++;
         }
         } elseif($_sub_menu_check->permission == 'payments' && (!has_permission('payments','','view') && !has_permission('invoices','','view_own'))){
         $total_sub_items_removed++;
         } elseif($_sub_menu_check->id == 'tickets' && (get_option('access_tickets_to_none_staff_members') == 0 && !is_staff_member())){
         $total_sub_items_removed++;
         } elseif($_sub_menu_check->id == 'customers'){
         if(!has_permission('customers','','view') && !have_assigned_customers() && !has_permission('customers','','create')){
         $total_sub_items_removed++;
         }
         } elseif($_sub_menu_check->id == 'child-proposals'){
         if((get_option('allow_staff_view_proposals_assigned') == 0
           || (get_option('allow_staff_view_proposals_assigned') == 1 && total_rows('tblproposals',array('assigned'=>get_staff_user_id())) == 0))
           && !has_permission('proposals','','view')
           && !has_permission('proposals','','view_own')){
           $total_sub_items_removed++;
         }
         }
         }
         if($total_sub_items_removed == count($item->children)){
         $submenu = false;
         $remove_main_menu = true;
         }
         } else {
         if($item->url == '#'){continue;}
         $url = $item->url;
         }
         if($remove_main_menu == true){
         continue;
         }
         $url = $item->url;
         if(!_startsWith($url,'http://') && !_startsWith($url,'https://') && $url != '#'){
         $url = admin_url($url);
         }
         ?>
      <li class="menu-item-<?php echo $item->id; ?>">
         <a href="<?php echo $url; ?>" aria-expanded="false"><i class="<?php echo $item->icon; ?> menu-icon"></i>
         <?php echo _l($item->name); ?>
         <?php if($submenu == true){ ?>
         <span class="fa arrow"></span>
         <?php } ?>
         </a>
         <?php if(isset($item->children)){ ?>
         <ul class="nav nav-second-level collapse" aria-expanded="false">
            <?php foreach($item->children as $submenu){
               if(
                !empty($submenu->permission)
                && ($submenu->permission != 'payments'
                  && $submenu->permission != 'tickets'
                  && $submenu->permission != 'proposals'
                  && $submenu->permission != 'customers')
                && (!has_permission($submenu->permission,'','view') && !has_permission($submenu->permission, '', 'view_own'))
               ){
                continue;
               } elseif(
               $submenu->permission == 'payments'
               && (!has_permission('payments','','view') && !has_permission('invoices','','view_own'))
               ){
                continue;
               } elseif($submenu->id == 'tickets' && (get_option('access_tickets_to_none_staff_members') == 0 && !is_staff_member())){
                continue;
               } elseif($submenu->id == 'customers'){
               if(!has_permission('customers','','view') && !have_assigned_customers() && !has_permission('customers','','create')){
                continue;
               }
               } elseif($submenu->id == 'child-proposals'){
               if((get_option('allow_staff_view_proposals_assigned') == 0
                || (get_option('allow_staff_view_proposals_assigned') == 1 && total_rows('tblproposals',array('assigned'=>get_staff_user_id())) == 0))
                && !has_permission('proposals','','view')
                && !has_permission('proposals','','view_own')){
                continue;
               }
               }
               $url = $submenu->url;
               if(!_startsWith($url,'http://') && !_startsWith($url,'https://')){
               $url = admin_url($url);
               }
               ?>
            <li class="sub-menu-item-<?php echo $submenu->id; ?>"><a href="<?php echo $url; ?>">
               <?php if(!empty($submenu->icon)){ ?>
               <i class="<?php echo $submenu->icon; ?> menu-icon"></i>
               <?php } ?>
               <?php echo _l($submenu->name); ?></a>
            </li>
            <?php } ?>
         </ul>
         <?php } ?>
      </li>
      <?php
         $m++;
         do_action('after_render_single_aside_menu',$m); ?>
      <?php } ?>
      <?php if((is_staff_member() || is_admin()) && $this->perfex_base->show_setup_menu() == true){ ?>
      <li<?php if(get_option('show_setup_menu_item_only_on_hover') == 1) { echo ' style="display:none;"'; } ?> id="setup-menu-item">
         <a href="#" class="open-customizer"><i class="fa fa-cog menu-icon"></i>
         <?php echo _l('setting_bar_heading'); ?></a>
         <?php } ?>
      </li>
      <?php do_action('after_render_aside_menu'); ?>
      <?php
         $pinnedProjects = get_user_pinned_projects();
         if(count($pinnedProjects) > 0){ ?>
      <li class="pinned-separator"></li>
      <?php foreach($pinnedProjects as $pinnedProject){ ?>
      <li class="pinned_project">
         <a href="<?php echo admin_url('projects/view/'.$pinnedProject['id']); ?>" data-toggle="tooltip" data-title="<?php echo _l('pinned_project'); ?>"><?php echo $pinnedProject['name']; ?><br><small><?php echo get_company_name($pinnedProject["clientid"]); ?></small></a></a>
         <div class="col-md-12">
            <div class="progress progress-bar-mini">
               <div class="progress-bar no-percent-text not-dynamic" role="progressbar" data-percent="<?php echo $pinnedProject['progress']; ?>" style="width: <?php echo $pinnedProject['progress']; ?>%;">
               </div>
            </div>
         </div>
      </li>
      <?php } ?>
      <?php } ?>
   </ul>
</aside>
