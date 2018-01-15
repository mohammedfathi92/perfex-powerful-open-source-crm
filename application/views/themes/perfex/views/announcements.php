<div class="panel_s">
    <div class="panel-body">
        <h4 class="no-margin"><?php echo _l('announcements'); ?></h4>
    </div>
</div>
<div class="panel_s">
    <div class="panel-body">
        <?php if(count($announcements) > 0){ ?>
            <table class="table dt-table" data-order-col="1" data-order-type="desc">
                <thead>
                    <tr>
                        <th><?php echo _l('announcement_name'); ?></th>
                        <th><?php echo _l('announcement_date_list'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($announcements as $announcement){ ?>
                    <tr>
                        <td><a href="<?php echo site_url('clients/announcement/'.$announcement['announcementid']); ?>"><?php echo $announcement['name']; ?></a></td>
                        <td data-order="<?php echo $announcement['dateadded']; ?>"><?php echo _d($announcement['dateadded']); ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
        <p class="no-margin"><?php echo _l('no_announcements'); ?></p>
        <?php } ?>
    </div>
</div>
