<div class="panel_s">
    <div class="panel-body">
        <h4 class="bold no-margin"><?php echo $announcement->name; ?></h4>
        <div class="mtop5"><?php echo _l('announcement_date',_d($announcement->dateadded)); ?></div>
        <?php if($announcement->showname == 1){ echo _l('announcement_from') . ' ' . $announcement->userid; } ?>
    </div>
</div>
<div class="panel_s">
    <div class="panel-body tc-content">
        <?php echo $announcement->message; ?>
    </div>
</div>
