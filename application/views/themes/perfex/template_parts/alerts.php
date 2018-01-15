<?php if(is_client_logged_in() && $use_navigation == true){
    $_announcements = get_announcements_for_user(false);
    if(count($_announcements) > 0){ ?>
    <div class="panel_s">
        <?php foreach($_announcements as $__announcement){ ?>
        <div class="panel-body announcement mbot15 tc-content">
            <div class="text-info alert-dismissible" role="alert">
                <a href="<?php echo site_url('clients/dismiss_announcement/'.$__announcement['announcementid']); ?>" class="close"><span aria-hidden="true">&times;</span></a>
                <h4 class="bold no-margin font-medium"><?php echo _l('announcement'); ?>! <?php if($__announcement['showname'] == 1){ echo '<br /><small>'._l('announcement_from').' '. $__announcement['userid']; } ?></small><br />
                    <small>
                        <?php echo _l('announcement_date',_dt($__announcement['dateadded'])); ?></small></h4>
                    </div>
                    <hr />
                    <h4 class="bold font-medium"><?php echo $__announcement['name']; ?></h4>
                    <?php echo check_for_links($__announcement['message']); ?>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
            <?php } ?>
