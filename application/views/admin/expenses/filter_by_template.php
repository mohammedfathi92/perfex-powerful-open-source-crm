<?php
if(!isset($filter_table_name)){
    $filter_table_name = '.table-expenses';
}
?>
<div class="_filters _hidden_inputs hidden">
   <?php echo form_hidden('billable');
   echo form_hidden('non-billable');
   echo form_hidden('invoiced');
   echo form_hidden('unbilled');
   echo form_hidden('recurring');
   foreach($years as $year){
    echo form_hidden('year_'.$year['year'],$year['year']);
}
for ($m = 1; $m <= 12; $m++) {
   echo form_hidden('expenses_by_month_'.$m);
}
foreach($categories as $category){
 echo form_hidden('expenses_by_category_'.$category['id']);
}
?>
</div>
<div class="btn-group pull-right mleft4 btn-with-tooltip-group _filter_data" data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fa fa-filter" aria-hidden="true"></i>
    </button>

    <ul class="dropdown-menu dropdown-menu-right width300">
        <li>
            <a href="#" data-cview="all" onclick="dt_custom_view('','<?php echo $filter_table_name; ?>',''); return false;">
                <?php echo _l('expenses_list_all'); ?>
            </a>
        </li>
        <li class="divider"></li>
        <li>
            <a href="#" data-cview="billable" onclick="dt_custom_view('billable','<?php echo $filter_table_name; ?>','billable'); return false;">
                <?php echo _l('expenses_list_billable'); ?>
            </a>
        </li>
        <li>
            <a href="#" data-cview="non-billable" onclick="dt_custom_view('non-billable','<?php echo $filter_table_name; ?>','non-billable'); return false;">
                <?php echo _l('expenses_list_non_billable'); ?>
            </a>
        </li>
        <li>
        <a href="#" data-cview="invoiced" onclick="dt_custom_view('invoiced','<?php echo $filter_table_name; ?>','invoiced'); return false;">
                <?php echo _l('expenses_list_invoiced'); ?>
            </a>
        </li>
        <li>
            <a href="#" data-cview="unbilled" onclick="dt_custom_view('unbilled','<?php echo $filter_table_name; ?>','unbilled'); return false;">
                <?php echo _l('expenses_list_unbilled'); ?>
            </a>
        </li>
        <li>
            <a href="#" data-cview="recurring" onclick="dt_custom_view('recurring','<?php echo $filter_table_name; ?>','recurring'); return false;">
                <?php echo _l('expenses_list_recurring'); ?>
            </a>
        </li>
        <?php if(count($years) > 0){ ?>
            <li class="divider"></li>
            <?php foreach($years as $year){ ?>
                <li class="active">
                    <a href="#" data-cview="year_<?php echo $year['year']; ?>" onclick="dt_custom_view(<?php echo $year['year']; ?>,'<?php echo $filter_table_name; ?>','year_<?php echo $year['year']; ?>'); return false;"><?php echo $year['year']; ?>
                    </a>
                </li>
                <?php } ?>
                <?php } ?>
                <?php if(count($categories) > 0){ ?>
                   <div class="clearfix"></div>
                   <li class="divider"></li>
                   <li class="dropdown-submenu pull-left">
                     <a href="#" tabindex="-1"><?php echo _l('expenses_filter_by_categories'); ?></a>
                     <ul class="dropdown-menu dropdown-menu-left">
                        <?php foreach($categories as $category){ ?>
                            <li>
                                <a href="#" data-cview="expenses_by_category_<?php echo $category['id']; ?>" onclick="dt_custom_view(<?php echo $category['id']; ?>,'<?php echo $filter_table_name; ?>','expenses_by_category_<?php echo $category['id']; ?>'); return false;"><?php echo $category['name']; ?></a>
                            </li>
                            <?php } ?>
                        </ul>
                    </li>
                    <?php } ?>
                    <div class="clearfix"></div>
                    <li class="divider"></li>
                    <li class="dropdown-submenu pull-left">
                      <a href="#" tabindex="-1"><?php echo _l('months'); ?></a>
                      <ul class="dropdown-menu dropdown-menu-left">
                        <?php for ($m = 1; $m <= 12; $m++) { ?>
                          <li><a href="#" data-cview="expenses_by_month_<?php echo $m; ?>" onclick="dt_custom_view(<?php echo $m; ?>,'<?php echo $filter_table_name; ?>','expenses_by_month_<?php echo $m; ?>'); return false;"><?php echo _l(date('F', mktime(0, 0, 0, $m, 1))); ?></a></li>
                          <?php } ?>
                      </ul>
                  </li>
              </ul>
          </div>
