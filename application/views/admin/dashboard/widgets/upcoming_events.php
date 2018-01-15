<div class="widget<?php if(count($upcoming_events) == 0 || !is_staff_member()){echo ' hide';} ?>" id="widget-<?php echo basename(__FILE__,".php"); ?>">
   <?php if(count($upcoming_events) > 0 && is_staff_member()){ ?>
   <div class="row">
      <div class="col-md-12">
         <div class="panel_s events">
            <div class="panel-body padding-10">
               <div class="widget-dragger"></div>
              <p class="padding-5">
               <?php echo _l('home_this_week_events'); ?>
            </p>
            <hr class="hr-panel-heading-dashboard">
            <?php foreach($upcoming_events as $event){ ?>
            <div class="event padding-5">
               <span class="text-dark pull-left"><i class="fa fa-calendar-check-o"></i> <?php echo _dt($event['start']); ?></span>
               <?php if($event['public'] == 1) { ?>
               <span class="text-success pull-right"><?php echo _l('home_public_event'); ?></span>
               <?php } ?>
               <div class="clearfix"></div>
               <p class="event-title"><a href="#" onclick="view_event(<?php echo $event['eventid']; ?>); return false;"><?php echo $event['title']; ?></a></p>
               <p class="text-muted no-margin"><?php echo $event['description']; ?></p>
               <?php if($event['userid'] != get_staff_user_id()){ ?>
               <small class="display-block valign mtop5"><?php echo _l('home_event_added_by'); ?> <a href="<?php echo admin_url('profile/'.$event['userid']); ?>"><?php echo staff_profile_image($event['userid'],array('staff-profile-xs-image')); ?> <?php echo get_staff_full_name($event['userid']); ?></a></small>
               <?php } ?>
            </div>
            <?php } ?>
         </div>
         <div class="panel-footer">
            <?php echo _l('home_upcoming_events_next_week'); ?> : <?php echo $upcoming_events_next_week; ?>
         </div>
      </div>
   </div>
</div>
<?php } ?>
</div>

