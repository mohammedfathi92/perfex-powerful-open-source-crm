<?php
do_action('before_sms_gateways_settings');

$gateways = $this->sms->get_gateways();
$triggers = $this->sms->get_available_triggers();
$total_gateways = count($gateways);

if($total_gateways > 1) { ?>
    <div class="alert alert-info">
        <?php echo _l('notice_only_one_active_sms_gateway'); ?>
    </div>
<?php } ?>

<div class="panel-group" id="sms_gateways_options" role="tablist" aria-multiselectable="false">
    <?php foreach($gateways as $gateway) { ?>
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="<?php echo 'heading'.$gateway['id']; ?>">
          <h4 class="panel-title">
            <a role="button" data-toggle="collapse" data-parent="#sms_gateways_options" href="#sms_<?php echo $gateway['id']; ?>" aria-expanded="true" aria-controls="sms_<?php echo $gateway['id']; ?>">
                <?php echo $gateway['name']; ?> <span class="pull-right"><i class="fa fa-sort-down"></i></span>
            </a>
        </h4>
    </div>
    <div id="sms_<?php echo $gateway['id']; ?>" class="panel-collapse collapse<?php if($this->sms->get_option($gateway['id'],'active') == 1 || $total_gateways == 1){echo ' in';} ?>" role="tabpanel" aria-labelledby="<?php echo 'heading'.$gateway['id']; ?>">
      <div class="panel-body no-br-tlr no-border-color">

        <?php
        if(isset($gateway['info']) && $gateway['info'] != '') {
            echo $gateway['info'];
        }

        foreach($gateway['options'] as $g_option){
            echo render_input('settings['.$this->sms->option_name($gateway['id'],$g_option['name']).']',$g_option['label'],$this->sms->get_option($gateway['id'],$g_option['name']));
        }
        echo '<div class="sms_gateway_active">';

        echo render_yes_no_option($this->sms->option_name($gateway['id'],'active'),'Active');
        echo '</div>';
        ?>
    </div>
</div>
</div>
<?php } ?>
<hr />
<h4 class="mbot15">
    <i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('sms_trigger_disable_tip'); ?>"></i>
    <?php echo _l('triggers'); ?>
</h4>
<?php
foreach($triggers as $trigger_name => $trigger_opts) {

    echo '<a href="#" onclick="slideToggle(\'#sms_merge_fields_'.$trigger_name.'\'); return false;" class="pull-right"><small>' . _l('available_merge_fields') . '</small></a>';

    $label = '<b>'.$trigger_opts['label'].'</b>';
    if(isset($trigger_opts['info']) && $trigger_opts['info'] != ''){
     $label .= '<p>'.$trigger_opts['info'].'</p>';
    }
    echo render_textarea('settings[' .$this->sms->trigger_option_name($trigger_name).']',$label,$trigger_opts['value']);

    $merge_fields = '';

    foreach($trigger_opts['merge_fields'] as $merge_field) {
        $merge_fields .= $merge_field .', ';
    }

    if($merge_fields != ''){
        echo '<div id="sms_merge_fields_'.$trigger_name.'" style="display:none;" class="mbot10">';
        echo substr($merge_fields,0,-2);
        echo '<hr class="hr-10" />';
        echo '</div>';
    }
    echo '<hr />';
}
?>
</div>
