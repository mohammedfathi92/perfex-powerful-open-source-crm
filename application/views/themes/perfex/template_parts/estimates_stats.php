<?php
$where_total = array('clientid'=>get_client_user_id());

if(get_option('exclude_estimate_from_client_area_with_draft_status') == 1){
     $where_total['status !='] = 1;
}
$total_estimates = total_rows('tblestimates',$where_total);

$total_sent = total_rows('tblestimates',array('status'=>2,'clientid'=>get_client_user_id()));
$total_declined = total_rows('tblestimates',array('status'=>3,'clientid'=>get_client_user_id()));
$total_accepted = total_rows('tblestimates',array('status'=>4,'clientid'=>get_client_user_id()));
$total_expired = total_rows('tblestimates',array('status'=>5,'clientid'=>get_client_user_id()));
$percent_sent = ($total_estimates > 0 ? number_format(($total_sent * 100) / $total_estimates,2) : 0);
$percent_declined = ($total_estimates > 0 ? number_format(($total_declined * 100) / $total_estimates,2) : 0);
$percent_accepted = ($total_estimates > 0 ? number_format(($total_accepted * 100) / $total_estimates,2) : 0);
$percent_expired = ($total_estimates > 0 ? number_format(($total_expired * 100) / $total_estimates,2) : 0);
if(get_option('exclude_estimate_from_client_area_with_draft_status') == 0){
    $col_class = 'col-md-5ths col-xs-12';
    $total_draft = total_rows('tblestimates',array('status'=>1,'clientid'=>get_client_user_id()));
    $percent_draft = ($total_estimates > 0 ? number_format(($total_draft * 100) / $total_estimates,2) : 0);
} else {
    $col_class = 'col-md-3';
}
?>
<div class="row text-left">
<?php if(get_option('exclude_estimate_from_client_area_with_draft_status') == 0){ ?>
    <div class="<?php echo $col_class; ?>">
        <div class="row">
            <div class="col-md-8">
                <a href="<?php echo site_url('clients/estimates/1'); ?>">
                <h5 class="no-margin bold no-margin"><?php echo _l('estimate_status_draft'); ?></h5>
                </a>
            </div>
            <div class="col-md-4 text-right bold">
                <?php echo $total_draft; ?> / <?php echo $total_estimates; ?>
            </div>
            <div class="col-md-12">
                <div class="progress no-margin">
                    <div class="progress-bar progress-bar-<?php echo estimate_status_color_class(1); ?>" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_draft; ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
    <div class="<?php echo $col_class; ?>">
        <div class="row">
            <div class="col-md-8">
                <a href="<?php echo site_url('clients/estimates/2'); ?>">
                    <h5 class="no-margin bold"><?php echo _l('estimate_status_sent'); ?></h5>
                </a>
            </div>
            <div class="col-md-4 text-right bold">
                <?php echo $total_sent; ?> / <?php echo $total_estimates; ?>
            </div>
            <div class="col-md-12">
                <div class="progress no-margin">
                    <div class="progress-bar progress-bar-<?php echo estimate_status_color_class(2); ?>" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_sent; ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
      <div class="<?php echo $col_class; ?>">
        <div class="row">
            <div class="col-md-8">
                <a href="<?php echo site_url('clients/estimates/5'); ?>">
                    <h5 class="no-margin bold"><?php echo _l('estimate_status_expired'); ?></h5>
                </a>
            </div>
            <div class="col-md-4 text-right bold">
                <?php echo $total_expired; ?> / <?php echo $total_estimates; ?>
            </div>
            <div class="col-md-12">
                <div class="progress no-margin">
                    <div class="progress-bar progress-bar-<?php echo estimate_status_color_class(5); ?>" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_expired; ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="<?php echo $col_class; ?>">
        <div class="row">
            <div class="col-md-8">
                <a href="<?php echo site_url('clients/estimates/3'); ?>">
                    <h5 class="no-margin bold"><?php echo _l('estimate_status_declined'); ?></h5>
                </a>
            </div>
            <div class="col-md-4 text-right bold">
                <?php echo $total_declined; ?> / <?php echo $total_estimates; ?>
            </div>
            <div class="col-md-12">
                <div class="progress no-margin">
                    <div class="progress-bar progress-bar-<?php echo estimate_status_color_class(3); ?>" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_declined; ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="<?php echo $col_class; ?>">
        <div class="row">
            <div class="col-md-8">
                <a href="<?php echo site_url('clients/estimates/4'); ?>">
                    <h5 class="no-margin bold"><?php echo _l('estimate_status_accepted'); ?></h5>
                </a>
            </div>
            <div class="col-md-4 text-right bold">
                <?php echo $total_accepted; ?> / <?php echo $total_estimates; ?>
            </div>
            <div class="col-md-12">
                <div class="progress no-margin">
                    <div class="progress-bar progress-bar-<?php echo estimate_status_color_class(4); ?>" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_accepted; ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
