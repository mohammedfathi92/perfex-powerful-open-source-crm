<div class="panel_s">
    <div class="panel-body">
        <h4 class="no-margin"><?php echo _l('clients_contracts'); ?></h4>
    </div>
</div>
<div class="panel_s">
    <div class="panel-body">
        <div class="col-md-12">
            <p><?php echo _l('contract_summary_by_type'); ?></p>
           <div class="relative" style="max-height:300px;">
                <canvas class="chart" height="300" id="contracts-by-type-chart"></canvas>
           </div>
        </div>
        <div class="clearfix"></div>
            <table class="table dt-table" data-order-col="3" data-order-type="asc">
                <thead>
                    <tr>
                        <th><?php echo _l('clients_contracts_dt_subject'); ?></th>
                        <th><?php echo _l('clients_contracts_type'); ?></th>
                        <th><?php echo _l('clients_contracts_dt_start_date'); ?></th>
                        <th><?php echo _l('clients_contracts_dt_end_date'); ?></th>
                        <th><?php echo _l('contract_attachments'); ?></th>
                        <?php
                        $custom_fields = get_custom_fields('contracts',array('show_on_client_portal'=>1));
                        foreach($custom_fields as $field){ ?>
                        <th><?php echo $field['name']; ?></th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($contracts as $contract){
                        $expiry_class = '';
                        if (!empty($contract['dateend'])) {
                            $_date_end = date('Y-m-d', strtotime($contract['dateend']));
                            if ($_date_end < date('Y-m-d')) {
                                $expiry_class = 'alert-danger';
                            }
                        }
                        ?>
                        <tr class="<?php echo $expiry_class; ?>">
                            <td>
                                <?php
                                if(empty($contract['content'])){
                                   echo $contract['subject'];
                               } else {
                                echo '<a href="'.site_url('clients/contract_pdf/'.$contract['id']).'"><i class="fa fa-file-pdf-o"></i> '.$contract['subject'].'</a>';
                            }
                            ?>
                        </td>
                        <td><?php echo $contract['type_name']; ?></td>
                        <td data-order="<?php echo $contract['datestart']; ?>"><?php echo _d($contract['datestart']); ?></td>
                        <td data-order="<?php echo $contract['dateend']; ?>"><?php echo _d($contract['dateend']); ?></td>
                        <td>
                            <?php foreach($contract['attachments'] as $attachment){
                              $attachment_url = site_url('download/file/contract/' . $attachment['id']);
                              if(!empty($attachment['external'])){
                                $attachment_url = $attachment['external_link'];
                            }
                            ?>
                            <div class="mbot5"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i>
                                <a href="<?php echo $attachment_url; ?>"><?php echo $attachment['file_name']; ?></a></div>
                                <?php } ?>
                            </td>
                            <?php foreach($custom_fields as $field){ ?>
                            <td><?php echo get_custom_field_value($contract['id'],$field['id'],'contracts'); ?></td>
                            <?php } ?>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
        </div>
    </div>
