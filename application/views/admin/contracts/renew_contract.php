<div class="modal fade" id="renew_contract_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <?php echo form_open(admin_url('contracts/renew'),array('id'=>'renew-contract-form')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <?php echo _l('contract_renew_heading'); ?>
                </h4>
            </div>
            <div class="modal-body">
                <?php
                    $new_end_date_assume = '';
                    if(!empty($contract->dateend)){
                        $dStart                      = new DateTime($contract->datestart);
                        $dEnd                        = new DateTime($contract->dateend);
                        $dDiff                       = $dStart->diff($dEnd);
                        $new_end_date_assume = _d(date('Y-m-d', strtotime(date('Y-m-d', strtotime('+' . $dDiff->days . 'DAY')))));
                    }
                    ?>
                <?php echo render_date_input('new_start_date','contract_start_date',_d(date('Y-m-d'))); ?>
                <?php echo render_date_input('new_end_date','contract_end_date',_d($new_end_date_assume)); ?>
                <?php echo render_input('new_value','contract_value',$contract->contract_value,'number'); ?>
                <?php echo form_hidden('contractid',$contract->id); ?>
                <?php echo form_hidden('old_start_date',$contract->datestart); ?>
                <?php echo form_hidden('old_end_date',$contract->dateend); ?>
                <?php echo form_hidden('old_value',$contract->contract_value); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
