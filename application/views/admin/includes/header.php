<?php
   ob_start();
   ?>
<li id="top_search" class="dropdown" data-toggle="tooltip" data-placement="bottom" data-title="<?php echo _l('search_by_tags'); ?>">
   <input type="search" id="search_input" class="form-control" placeholder="<?php echo _l('top_search_placeholder'); ?>">
   <div id="search_results">
   </div>
</li>
<li id="top_search_button">
   <button class="btn"><i class="fa fa-search"></i></button>
</li>
<?php
   $top_search_area = ob_get_contents();
   ob_end_clean();
   ?>
<div id="header">
   <div class="hide-menu"><i class="fa fa-bars"></i></div>
   <div id="logo">
      <?php get_company_logo(get_admin_uri().'/') ?>
   </div>
   <nav>
      <div class="small-logo">
         <span class="text-primary">
         <?php get_company_logo(get_admin_uri().'/') ?>
         </span>
      </div>
      <div class="mobile-menu">
         <button type="button" class="navbar-toggle visible-md visible-sm visible-xs mobile-menu-toggle collapsed" data-toggle="collapse" data-target="#mobile-collapse" aria-expanded="false">
         <i class="fa fa-chevron-down"></i>
         </button>
         <ul class="mobile-icon-menu">
            <?php
               // To prevent not loading the timers twice
               if(is_mobile()){ ?>
            <li class="dropdown notifications-wrapper header-notifications">
               <?php $this->load->view('admin/includes/notifications'); ?>
            </li>
            <li class="header-timers">
               <a href="#" id="top-timers" class="dropdown-toggle top-timers" data-toggle="dropdown"><i class="fa fa-clock-o fa-fw fa-lg"></i>
               <span class="label bg-success icon-total-indicator icon-started-timers<?php if ($totalTimers = count($startedTimers) == 0){ echo ' hide'; }?>"><?php echo count($startedTimers); ?></span>
               </a>
               <ul class="dropdown-menu animated fadeIn started-timers-top width300" id="started-timers-top">
                  <?php $this->load->view('admin/tasks/started_timers',array('startedTimers'=>$startedTimers)); ?>
               </ul>
               <?php if(is_staff_member()){ ?>
            <li class="header-newsfeed">
               <a href="#" class="open_newsfeed"><i class="fa fa-share fa-fw fa-lg" aria-hidden="true"></i></a>
            </li>
            <?php } ?>
            </li>
            <?php } ?>
         </ul>
         <div class="mobile-navbar collapse" id="mobile-collapse" aria-expanded="false" style="height: 0px;" role="navigation" >
            <ul class="nav navbar-nav">
               <li class="header-my-profile"><a href="<?php echo admin_url('profile'); ?>"><?php echo _l('nav_my_profile'); ?></a></li>
               <li class="header-my-timesheets"><a href="<?php echo admin_url('staff/timesheets'); ?>"><?php echo _l('my_timesheets'); ?></a></li>
               <li class="header-edit-profile"><a href="<?php echo admin_url('staff/edit_profile'); ?>"><?php echo _l('nav_edit_profile'); ?></a></li>
               <li class="header-logout"><a href="#" onclick="logout(); return false;"><?php echo _l('nav_logout'); ?></a></li>
            </ul>
         </div>
      </div>
      <ul class="nav navbar-nav navbar-right">
         <?php
            if(!is_mobile()){
              echo $top_search_area;
            } ?>
         <?php do_action('after_render_top_search'); ?>
         <!--  <li class="icon header-business-news">
            <a href="<?php  // echo admin_url('business_news'); ?>" data-toggle="tooltip" data-placement="bottom" title="<?php // echo _l('business_news'); ?>"><i class="fa fa-newspaper-o"></i></a>
            </li> -->
         <li class="icon header-user-profile" data-toggle="tooltip" title="<?php echo get_staff_full_name(); ?>" data-placement="bottom">
            <a href="#" class="dropdown-toggle profile" data-toggle="dropdown" aria-expanded="false">
            <?php echo staff_profile_image($current_user->staffid,array('img','img-responsive','staff-profile-image-small','pull-left')); ?>
            </a>
            <ul class="dropdown-menu animated fadeIn">
               <li class="header-my-profile"><a href="<?php echo admin_url('profile'); ?>"><?php echo _l('nav_my_profile'); ?></a></li>
               <li class="header-my-timesheets"><a href="<?php echo admin_url('staff/timesheets'); ?>"><?php echo _l('my_timesheets'); ?></a></li>
               <li class="header-edit-profile"><a href="<?php echo admin_url('staff/edit_profile'); ?>"><?php echo _l('nav_edit_profile'); ?></a></li>
               <?php if(get_option('disable_language') == 0){ ?>
               <li class="dropdown-submenu pull-left header-languages">
                  <a href="#" tabindex="-1"><?php echo _l('language'); ?></a>
                  <ul class="dropdown-menu dropdown-menu">
                     <li class="<?php if($current_user->default_language == ""){echo 'active';} ?>"><a href="<?php echo admin_url('staff/change_language'); ?>"><?php echo _l('system_default_string'); ?></a></li>
                     <?php foreach($this->perfex_base->get_available_languages() as $user_lang) { ?>
                     <li<?php if($current_user->default_language == $user_lang){echo ' class="active"';} ?>>
                        <a href="<?php echo admin_url('staff/change_language/'.$user_lang); ?>"><?php echo ucfirst($user_lang); ?></a>
                        <?php } ?>
                  </ul>
               </li>
               <?php } ?>
               <li class="header-logout">
                  <a href="#" onclick="logout(); return false;"><?php echo _l('nav_logout'); ?></a>
               </li>
            </ul>
         </li>
         <?php if(is_staff_member()){ ?>
         <li class="icon header-newsfeed">
            <a href="#" class="open_newsfeed" data-toggle="tooltip" title="<?php echo _l('whats_on_your_mind'); ?>" data-placement="bottom"><i class="fa fa-share fa-fw fa-lg" aria-hidden="true"></i></a>
         </li>
         <?php } ?>
         <li class="icon header-todo">
            <a href="<?php echo admin_url('todo'); ?>" data-toggle="tooltip" title="<?php echo _l('nav_todo_items'); ?>" data-placement="bottom"><i class="fa fa-check-square-o fa-fw fa-lg"></i>
            <?php $_unfinished_todos = total_rows('tbltodoitems',array('finished'=>0,'staffid'=>get_staff_user_id())); ?>
            <span class="label bg-warning icon-total-indicator nav-total-todos<?php if($_unfinished_todos == 0){echo ' hide';} ?>"><?php echo $_unfinished_todos; ?></span>
            </a>
         </li>
         <li class="icon header-timers timer-button" data-placement="bottom" data-toggle="tooltip" data-title="<?php echo _l('my_timesheets'); ?>">
            <a href="#" id="top-timers" class="dropdown-toggle top-timers" data-toggle="dropdown">
            <i class="fa fa-clock-o fa-fw fa-lg" aria-hidden="true"></i>
            <span class="label bg-success icon-total-indicator icon-started-timers<?php if ($totalTimers = count($startedTimers) == 0){ echo ' hide'; }?>">
            <?php echo count($startedTimers); ?>
            </span>
            </a>
            <ul class="dropdown-menu animated fadeIn started-timers-top width350" id="started-timers-top">
               <?php $this->load->view('admin/tasks/started_timers',array('startedTimers'=>$startedTimers)); ?>
            </ul>
         </li>
         <li class="dropdown notifications-wrapper header-notifications" data-toggle="tooltip" title="<?php echo _l('nav_notifications'); ?>" data-placement="bottom">
            <?php $this->load->view('admin/includes/notifications'); ?>
         </li>
      </ul>
   </nav>
</div>
<div id="mobile-search" class="<?php if(!is_mobile()){echo 'hide';} ?>">
   <ul>
      <?php
         if(is_mobile()){
           echo $top_search_area;
         } ?>
   </ul>
</div>
