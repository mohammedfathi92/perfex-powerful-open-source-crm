<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="_filters _hidden_inputs hidden">
                        <?php
                        echo form_hidden('exclude_trashed_contracts',true);
                        echo form_hidden('expired');
                        echo form_hidden('without_dateend');
                        echo form_hidden('trash');
                        foreach($years as $year){
                         echo form_hidden('year_'.$year['year'],$year['year']);
                     }
                     for ($m = 1; $m <= 12; $m++) {
                        echo form_hidden('contracts_by_month_'.$m);
                    }
                    foreach($contract_types as $type){
                        echo form_hidden('contracts_by_type_'.$type['id']);
                    }
                    ?>
                </div>
                <div class="panel-body _buttons">
                    <?php if(has_permission('contracts','','create')){ ?>
                    <a href="<?php echo admin_url('contracts/contract'); ?>" class="btn btn-info pull-left display-block"><?php echo _l('new_contract'); ?></a>
                    <?php } ?>
                    <div class="btn-group pull-right btn-with-tooltip-group _filter_data" data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-filter" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-left width300 height500">
                            <li class="active">
                                <a href="#" data-cview="exclude_trashed_contracts" onclick="dt_custom_view('exclude_trashed_contracts','.table-contracts','exclude_trashed_contracts'); return false;">
                                    <?php echo _l('contracts_view_exclude_trashed'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="#" data-cview="all" onclick="dt_custom_view('','.table-contracts',''); return false;">
                                    <?php echo _l('contracts_view_all'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="#" data-cview="expired"  onclick="dt_custom_view('expired','.table-contracts','expired'); return false;">
                                    <?php echo _l('contracts_view_expired'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="#" data-cview="without_dateend"  onclick="dt_custom_view('without_dateend','.table-contracts','without_dateend'); return false;">
                                    <?php echo _l('contracts_view_without_dateend'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="#" data-cview="trash"  onclick="dt_custom_view('trash','.table-contracts','trash'); return false;">
                                    <?php echo _l('contracts_view_trash'); ?>
                                </a>
                            </li>
                            <?php if(count($years) > 0){ ?>
                            <li class="divider"></li>
                            <?php foreach($years as $year){ ?>
                            <li class="active">
                                <a href="#" data-cview="year_<?php echo $year['year']; ?>" onclick="dt_custom_view(<?php echo $year['year']; ?>,'.table-contracts','year_<?php echo $year['year']; ?>'); return false;"><?php echo $year['year']; ?>
                                </a>
                            </li>
                            <?php } ?>
                            <?php } ?>
                            <div class="clearfix"></div>
                            <li class="divider"></li>
                            <li class="dropdown-submenu pull-left">
                                <a href="#" tabindex="-1"><?php echo _l('months'); ?></a>
                                <ul class="dropdown-menu dropdown-menu-left">
                                    <?php for ($m = 1; $m <= 12; $m++) { ?>
                                    <li><a href="#" data-cview="contracts_by_month_<?php echo $m; ?>" onclick="dt_custom_view(<?php echo $m; ?>,'.table-contracts','contracts_by_month_<?php echo $m; ?>'); return false;"><?php echo _l(date('F', mktime(0, 0, 0, $m, 1))); ?></a></li>
                                    <?php } ?>
                                </ul>
                            </li>
                            <div class="clearfix"></div>
                            <?php if(count($contract_types) > 0){ ?>
                            <li class="divider"></li>
                            <?php foreach($contract_types as $type){ ?>
                            <li>
                                <a href="#" data-cview="contracts_by_type_<?php echo $type['id']; ?>" onclick="dt_custom_view('contracts_by_type_<?php echo $type['id']; ?>','.table-contracts','contracts_by_type_<?php echo $type['id']; ?>'); return false;">
                                    <?php echo $type['name']; ?>
                                </a>
                            </li>
                            <?php } ?>
                            <?php } ?>
                        </ul>
                    </div>
                    <div class="clearfix"></div>
                    <hr class="hr-panel-heading" />
                    <div class="row" id="contract_summary">

                        <?php $minus_7_days = date('Y-m-d', strtotime("-7 days")); ?>
                        <?php $plus_7_days = date('Y-m-d', strtotime("+7 days"));
                        $where_own = array();
                        if(!has_permission('contracts','','view')){
                            $where_own = array('addedfrom'=>get_staff_user_id());
                        }
                        ?>
                        <div class="col-md-12">
                            <h4 class="no-margin text-success"><?php echo _l('contract_summary_heading'); ?></h4>
                        </div>
                        <div class="col-md-2 col-xs-6 border-right">
                            <h3 class="bold">
                            <?php echo total_rows('tblcontracts','(DATE(dateend) >"'.date('Y-m-d').'" AND trash=0' . (count($where_own) > 0 ? ' AND addedfrom='.get_staff_user_id() : '').') OR (DATE(dateend) IS NULL AND trash=0'.(count($where_own) > 0 ? ' AND addedfrom='.get_staff_user_id() : '').')'); ?>
                            </h3>
                            <span class="text-info"><?php echo _l('contract_summary_active'); ?></span>
                        </div>
                        <div class="col-md-2 col-xs-6 border-right">
                            <h3 class="bold"><?php echo total_rows('tblcontracts',array_merge(array('DATE(dateend) <'=>date('Y-m-d'),'trash'=>0),$where_own)); ?></h3>
                            <span class="text-danger"><?php echo _l('contract_summary_expired'); ?></span>
                        </div>
                        <div class="col-md-2 col-xs-6 border-right">
                            <h3 class="bold"><?php
                                echo total_rows(
                                'tblcontracts','dateend BETWEEN "'.$minus_7_days.'" AND "'.$plus_7_days.'" AND trash=0 AND dateend is NOT NULL AND dateend >"'.date('Y-m-d').'"' . (count($where_own) > 0 ? ' AND addedfrom='.get_staff_user_id() : '')); ?></h3>
                                <span class="text-warning"><?php echo _l('contract_summary_about_to_expire'); ?></span>
                            </div>
                            <div class="col-md-2 col-xs-6 border-right">
                                <h3 class="bold"><?php
                                    echo total_rows('tblcontracts','dateadded BETWEEN "'.$minus_7_days.'" AND "'.$plus_7_days.'" AND trash=0' . (count($where_own) > 0 ? ' AND addedfrom='.get_staff_user_id() : '')); ?></h3>
                                    <span class="text-success"><?php echo _l('contract_summary_recently_added'); ?></span>
                                </div>
                                <div class="col-md-2 col-xs-6">
                                    <h3 class="bold"><?php echo total_rows('tblcontracts',array_merge(array('trash'=>1),$where_own)); ?></h3>
                                    <span class="text-muted"><?php echo _l('contract_summary_trash'); ?></span>
                                </div>
                                <div class="clearfix"></div>
                                <hr class="hr-panel-heading" />
                                <div class="col-md-6 border-right">
                                    <h4><?php echo _l('contract_summary_by_type'); ?></h4>
                                    <div class="relative" style="max-height:400px">
                                        <canvas class="chart" height="400" id="contracts-by-type-chart"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h4><?php echo _l('contract_summary_by_type_value'); ?></h4>
                                    <div class="relative" style="max-height:400px">
                                        <canvas class="chart" height="400" id="contracts-value-by-type-chart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel_s">
                        <?php echo form_hidden('custom_view'); ?>
                        <div class="panel-body">
                            <?php
                            $table_data = array(
                               '#',
                               _l('contract_list_subject'),
                               _l('contract_list_client'),
                               _l('contract_types_list_name'),
                               _l('contract_value'),
                               _l('contract_list_start_date'),
                               _l('contract_list_end_date'),
                               );
                            $custom_fields = get_custom_fields('contracts',array('show_on_table'=>1));
                            foreach($custom_fields as $field){
                               array_push($table_data,$field['name']);
                           }
                           $table_data = do_action('contracts_table_columns',$table_data);
                           array_push($table_data,_l('options'));
                           render_datatable($table_data,'contracts'); ?>
                       </div>
                   </div>
               </div>
           </div>
       </div>
   </div>
   <?php init_tail(); ?>
   <script>
    $(function(){

        var ContractsServerParams = {};
        $.each($('._hidden_inputs._filters input'),function(){
            ContractsServerParams[$(this).attr('name')] = '[name="'+$(this).attr('name')+'"]';
        });

        var headers_contracts = $('.table-contracts').find('th');
        var not_sortable_contracts = (headers_contracts.length - 1);

        initDataTable('.table-contracts', admin_url+'contracts/table', [not_sortable_contracts], [not_sortable_contracts], ContractsServerParams,<?php echo do_action('contracts_table_default_order',json_encode(array(6,'ASC'))); ?>);
        new Chart($('#contracts-by-type-chart'), {
            type: 'bar',
            data: <?php echo $chart_types; ?>,
            options: {
                legend: {
                    display: false,
                },
                responsive: true,
                maintainAspectRatio:false,
                scales: {
                    yAxes: [{
                        display: true,
                        ticks: {
                            suggestedMin: 0,
                        }
                    }]
                }
            }
        });
        new Chart($('#contracts-value-by-type-chart'), {
            type: 'line',
            data: <?php echo $chart_types_values; ?>,
            options: {
                responsive: true,
                legend: {
                    display: false,
                },
                maintainAspectRatio:false,
                scales: {
                    yAxes: [{
                        display: true,
                        ticks: {
                            suggestedMin: 0,
                        }
                    }]
                }
            }
        });
    });
</script>
</body>
</html>
