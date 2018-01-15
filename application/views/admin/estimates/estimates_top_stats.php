<div id="stats-top" class="hide">
    <div id="estimates_total"></div>
    <div class="panel_s">
        <div class="panel-body">
         <div class="_filters _hidden_inputs hidden">
            <?php
            if(isset($estimates_sale_agents)){
                foreach($estimates_sale_agents as $agent){
                    echo form_hidden('sale_agent_'.$agent['sale_agent']);
                }
            }
            if(isset($estimate_statuses)){
                foreach($estimate_statuses as $_status){
                    $val = '';
                    if($_status == $this->input->get('status')){
                        $val = $_status;
                    }
                    echo form_hidden('estimates_'.$_status,$val);
                }
            }
            if(isset($estimates_years)){
                foreach($estimates_years as $year){
                    echo form_hidden('year_'.$year['year'],$year['year']);
                }
            }
            echo form_hidden('not_sent',$this->input->get('filter'));
            echo form_hidden('project_id');
            echo form_hidden('invoiced');
            echo form_hidden('not_invoiced');
            ?>
        </div>
        <?php $total_estimates = total_rows('tblestimates'); ?>
        <div class="row text-left quick-top-stats">
            <?php foreach($estimate_statuses as $status){
              $percent_data = get_estimates_percent_by_status($status);
              ?>
              <div class="col-md-5ths col-xs-12">
                <div class="row">
                    <div class="col-md-7">
                        <a href="#" data-cview="estimates_<?php echo $status; ?>" onclick="dt_custom_view('estimates_<?php echo $status; ?>','.table-estimates','estimates_<?php echo $status; ?>',true); return false;">
                            <h5><?php echo format_estimate_status($status,'',false); ?></h5>
                        </a>
                    </div>
                    <div class="col-md-5 text-right">
                        <?php echo $percent_data['total_by_status']; ?> / <?php echo $percent_data['total']; ?>
                    </div>
                    <div class="col-md-12">
                        <div class="progress no-margin">
                            <div class="progress-bar progress-bar-<?php echo estimate_status_color_class($status); ?>" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_data['percent']; ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
<hr />
</div>
