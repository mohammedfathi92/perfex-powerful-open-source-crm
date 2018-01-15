 <div class="activity-feed">
    <?php foreach($activity as $activity){
        ?>
        <div class="feed-item">
            <div class="row">
                <div class="col-md-8">
                   <div class="date"><span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($activity['dateadded']); ?>"><?php echo time_ago($activity['dateadded']); ?></span></div>
                   <div class="text">
                      <?php
                      $fullname = $activity['fullname'];
                      if($activity['staff_id'] != 0){ ?>
                      <a href="<?php echo admin_url('profile/'.$activity['staff_id']); ?>"><?php echo staff_profile_image($activity['staff_id'],array('staff-profile-xs-image','pull-left mright10')); ?></a>
                      <?php } else if($activity['contact_id'] != 0){
                        $fullname = '<span class="label label-info inline-block mbot5">'._l('is_customer_indicator') . '</span> ' . $fullname = $activity['fullname']; ?>
                        <a href="<?php echo admin_url('clients/client/'.get_user_id_by_contact_id($activity['contact_id']).'?contactid='.$activity['contact_id']); ?>">
                            <img src="<?php echo contact_profile_image_url($activity['contact_id']); ?>" class="staff-profile-xs-image pull-left mright10">
                        </a>
                        <?php } ?>
                        <?php if($activity['visible_to_customer'] == 1){
                            $checked = 'checked';
                        } else {
                            $checked = '';
                        }
                        ?>
                        <p class="mtop10 no-mbot"><?php echo $fullname . ' - <b>'.$activity['description'].'</b>'; ?></p>
                        <p class="no-mbot text-muted mleft30 mtop5"><?php echo $activity['additional_data']; ?></p>
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <p class="text-muted"><?php echo _l('project_activity_visible_to_customer'); ?></p>
                    <div class="pull-right">
                        <div class="onoffswitch">
                            <input type="checkbox" <?php if(!has_permission('projects','','create')){echo 'disabled';} ?> id="<?php echo $activity['id']; ?>" data-id="<?php echo $activity['id']; ?>" class="onoffswitch-checkbox" data-switch-url="<?php echo admin_url(); ?>projects/change_activity_visibility" <?php echo $checked; ?>>
                            <label class="onoffswitch-label" for="<?php echo $activity['id']; ?>"></label>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-12">
                    <hr />
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
