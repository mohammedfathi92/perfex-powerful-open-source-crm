<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-7">
                <div class="panel_s">
                    <div class="panel-body tc-content">
                      <h4 class="bold no-margin"><?php echo $announcement->name; ?></h4>
                      <p class="text-muted mtop10 no-mbot"><?php echo _l('announcement_date',_dt($announcement->dateadded)); ?></p>
                      <?php if($announcement->showname == 1){ ?>
                      <p class="text-muted no-margin"><?php echo _l('announcement_from') . ' ' . $announcement->userid; ?></p>
                      <?php } ?>
                      <hr class="hr-panel-heading" />
                      <div class="clearfix"></div>
                      <?php echo $announcement->message; ?>
                  </div>
              </div>
          </div>
          <?php if(count($recent_announcements) > 0){ ?>
          <div class="col-md-5">
            <div class="panel_s">
                <div class="panel-body">
                    <h4 class="bold no-margin"><?php echo _l('announcements_recent'); ?></h4>
                    <hr class="hr-panel-heading" />
                    <?php foreach($recent_announcements as $announcement){ ?>
                    <a class="bold" href="<?php echo admin_url('announcements/view/'.$announcement['announcementid']); ?>">
                        <?php echo $announcement['name']; ?></a>
                        <p class="text-muted no-margin"><?php echo _l('announcement_date',_dt($announcement['dateadded'])); ?></p>
                        <?php if($announcement['showname'] == 1){ ?>
                        <p class="text-muted no-margin"><?php echo _l('announcement_from') . ' ' . $announcement['userid']; ?></p>
                        <?php } ?>
                        <div class="mtop15">
                            <?php echo strip_tags(mb_substr($announcement['message'],0,250)) . '...'; ?>
                            <hr class="hr-panel-heading" />
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php init_tail(); ?>
</body>
</html>
