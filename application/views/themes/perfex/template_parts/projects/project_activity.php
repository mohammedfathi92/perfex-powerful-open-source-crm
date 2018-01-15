<div class="mtop15"></div>
<div class="activity-feed">
    <?php if($project->settings->view_activity_log == 1){ ?>
    <?php foreach($activity as $activity){
        ?>
        <div class="feed-item">
            <div class="date"><?php echo time_ago($activity['dateadded']); ?></div>
            <?php
            $fullname = $activity['fullname'];
            if($activity['staff_id'] != 0){ ?>
            <?php echo staff_profile_image($activity['staff_id'],array('staff-profile-image-small','pull-left mright10')); ?>
            <?php } else if($activity['contact_id'] != 0){ ?>
            <img src="<?php echo contact_profile_image_url($activity['contact_id']); ?>" class="client-profile-image-small pull-left mright10">
            <?php } ?>
            <div class="media-body">
                <div class="display-block">
                    <p class="mtop5 no-mbot">
                        <?php echo $fullname . ' - <b>'.$activity['description'].'</b>'; ?>
                    </p>
                    <p class="text-muted mtop5"><?php echo $activity['additional_data']; ?></p>
                </div>
            </div>
            <hr />
        </div>
        <?php } ?>
        <?php } ?>
    </div>
