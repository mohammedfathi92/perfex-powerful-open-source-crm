<div class="panel_s<?php if(!isset($invoice) || (isset($invoice) && count($invoices_to_merge) == 0 && (isset($invoice) && !isset($invoice_from_project) && count($expenses_to_bill) == 0))){echo ' hide';} ?>" id="invoice_top_info">
   <div class="panel-body">
      <div class="row">
         <div id="merge" class="col-md-6">
            <?php if(isset($invoice)){
               $this->load->view('admin/invoices/merge_invoice',array('invoices_to_merge'=>$invoices_to_merge));
               } ?>
         </div>
         <?php
            // When invoicing from project area the expenses are not visible here because you can select to bill expenses while trying to invoice project
            if(!isset($invoice_from_project)){ ?>
         <div id="expenses_to_bill" class="col-md-6">
            <?php if(isset($invoice)){
               $this->load->view('admin/invoices/bill_expenses',array('expenses_to_bill'=>$expenses_to_bill));
               } ?>
         </div>
         <?php } ?>
      </div>
   </div>
</div>
<div class="panel_s invoice accounting-template">
   <div class="additional"></div>
   <div class="panel-body">
      <?php if(isset($invoice)){ ?>
      <?php  echo format_invoice_status($invoice->status); ?>
      <hr class="hr-panel-heading" />
      <?php } ?>
      <?php do_action('before_render_invoice_template'); ?>
      <?php if(isset($invoice)){
        echo form_hidden('merge_current_invoice',$invoice->id);
      } ?>
      <div class="row">
         <div class="col-md-6">
            <h4 class="no-margin">
               <?php echo _l('invoice_estimate_general_options'); ?>
            </h4>
            <hr class="hr-panel-heading" />
            <div class="f_client_id">
              <div class="form-group">
                <label for="clientid"><?php echo _l('invoice_select_customer'); ?></label>
                <select id="clientid" name="clientid" data-live-search="true" data-width="100%" class="ajax-search" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
               <?php $selected = (isset($invoice) ? $invoice->clientid : '');
                 if($selected == ''){
                   $selected = (isset($customer_id) ? $customer_id: '');
                 }
                 if($selected != ''){
                    $rel_data = get_relation_data('customer',$selected);
                    $rel_val = get_relation_values($rel_data,'customer');
                    echo '<option value="'.$rel_val['id'].'" selected>'.$rel_val['name'].'</option>';
                 } ?>
                </select>
              </div>
            </div>
            <?php
            if(!isset($invoice_from_project)){ ?>
            <div class="form-group projects-wrapper<?php if((!isset($invoice)) || (isset($invoice) && !customer_has_projects($invoice->clientid))){ echo ' hide';} ?>">
               <label for="project_id"><?php echo _l('project'); ?></label>
              <div id="project_ajax_search_wrapper">
                   <select name="project_id" id="project_id" class="projects ajax-search" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                   <?php
                     if(isset($invoice) && $invoice->project_id != 0){
                        echo '<option value="'.$invoice->project_id.'" selected>'.get_project_name_by_id($invoice->project_id).'</option>';
                     }
                   ?>
               </select>
               </div>
            </div>
            <?php } ?>
            <div class="row">
               <div class="col-md-12">
               <hr class="hr-10" />
                  <a href="#" class="edit_shipping_billing_info" data-toggle="modal" data-target="#billing_and_shipping_details"><i class="fa fa-pencil-square-o"></i></a>
                  <?php include_once(APPPATH .'views/admin/invoices/billing_and_shipping_template.php'); ?>
               </div>
               <div class="col-md-6">
                  <p class="bold"><?php echo _l('invoice_bill_to'); ?></p>
                  <address>
                     <span class="billing_street">
                     <?php $billing_street = (isset($invoice) ? $invoice->billing_street : '--'); ?>
                     <?php $billing_street = ($billing_street == '' ? '--' :$billing_street); ?>
                     <?php echo $billing_street; ?></span><br>
                     <span class="billing_city">
                     <?php $billing_city = (isset($invoice) ? $invoice->billing_city : '--'); ?>
                     <?php $billing_city = ($billing_city == '' ? '--' :$billing_city); ?>
                     <?php echo $billing_city; ?></span>,
                     <span class="billing_state">
                     <?php $billing_state = (isset($invoice) ? $invoice->billing_state : '--'); ?>
                     <?php $billing_state = ($billing_state == '' ? '--' :$billing_state); ?>
                     <?php echo $billing_state; ?></span>
                     <br/>
                     <span class="billing_country">
                     <?php $billing_country = (isset($invoice) ? get_country_short_name($invoice->billing_country) : '--'); ?>
                     <?php $billing_country = ($billing_country == '' ? '--' :$billing_country); ?>
                     <?php echo $billing_country; ?></span>,
                     <span class="billing_zip">
                     <?php $billing_zip = (isset($invoice) ? $invoice->billing_zip : '--'); ?>
                     <?php $billing_zip = ($billing_zip == '' ? '--' :$billing_zip); ?>
                     <?php echo $billing_zip; ?></span>
                  </address>
               </div>
               <div class="col-md-6">
                  <p class="bold"><?php echo _l('ship_to'); ?></p>
                  <address>
                     <span class="shipping_street">
                     <?php $shipping_street = (isset($invoice) ? $invoice->shipping_street : '--'); ?>
                     <?php $shipping_street = ($shipping_street == '' ? '--' :$shipping_street); ?>
                     <?php echo $shipping_street; ?></span><br>
                     <span class="shipping_city">
                     <?php $shipping_city = (isset($invoice) ? $invoice->shipping_city : '--'); ?>
                     <?php $shipping_city = ($shipping_city == '' ? '--' :$shipping_city); ?>
                     <?php echo $shipping_city; ?></span>,
                     <span class="shipping_state">
                     <?php $shipping_state = (isset($invoice) ? $invoice->shipping_state : '--'); ?>
                     <?php $shipping_state = ($shipping_state == '' ? '--' :$shipping_state); ?>
                     <?php echo $shipping_state; ?></span>
                     <br/>
                     <span class="shipping_country">
                     <?php $shipping_country = (isset($invoice) ? get_country_short_name($invoice->shipping_country) : '--'); ?>
                     <?php $shipping_country = ($shipping_country == '' ? '--' :$shipping_country); ?>
                     <?php echo $shipping_country; ?></span>,
                     <span class="shipping_zip">
                     <?php $shipping_zip = (isset($invoice) ? $invoice->shipping_zip : '--'); ?>
                     <?php $shipping_zip = ($shipping_zip == '' ? '--' :$shipping_zip); ?>
                     <?php echo $shipping_zip; ?></span>
                  </address>
               </div>
            </div>
            <?php
               $next_invoice_number = get_option('next_invoice_number');
               $format = get_option('invoice_number_format');
               if(isset($invoice)){$format = $invoice->number_format;}
               $prefix = get_option('invoice_prefix');
               if ($format == 1) {
               // Number based
                 $__number = $next_invoice_number;
                 if(isset($invoice)){
                   $__number = $invoice->number;
                   $prefix = '<span id="prefix">' . $invoice->prefix . '</span>';
                 }
               } else {
                 if(isset($invoice)){
                   $__number = $invoice->number;
                   $prefix = $invoice->prefix;
                   $prefix = '<span id="prefix">'. $prefix . '</span><span id="prefix_year">' .date('Y',strtotime($invoice->date)).'</span>/';
                 } else {
                  $__number = $next_invoice_number;
                  $prefix = $prefix.'<span id="prefix_year">'.date('Y').'</span>/';
                }
               }
               $_invoice_number = str_pad($__number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
               if(isset($invoice)){
               $isedit = 'true';
               $data_original_number = $invoice->number;
               } else {
               $isedit = 'false';
               $data_original_number = 'false';
               }
               ?>
            <div class="form-group">
               <label for="number"><?php echo _l('invoice_add_edit_number'); ?></label>
               <div class="input-group">
                  <span class="input-group-addon">
                  <?php if(isset($invoice)){ ?>
                  <a href="#" onclick="return false;" data-toggle="popover" data-container='._transaction_form' data-html="true" data-content="<label class='control-label'><?php echo _l('settings_sales_invoice_prefix'); ?></label><div class='input-group'><input name='s_prefix' type='text' class='form-control' value='<?php echo $invoice->prefix; ?>'></div><button type='button' onclick='save_sales_number_settings(this); return false;' data-url='<?php echo admin_url('invoices/update_number_settings/'.$invoice->id); ?>' class='btn btn-info btn-block mtop15'><?php echo _l('submit'); ?></button>"><i class="fa fa-cog"></i></a>
                  <?php } ?>
                  <?php echo $prefix; ?></span>
                  <input type="text" name="number" class="form-control" value="<?php echo $_invoice_number; ?>" data-isedit="<?php echo $isedit; ?>" data-original-number="<?php echo $data_original_number; ?>">
               </div>
            </div>
            <div class="row">
               <div class="col-md-6">
                  <?php $value = (isset($invoice) ? _d($invoice->date) : _d(date('Y-m-d')));
                  $date_attrs = array();
                  if(isset($invoice) && $invoice->recurring > 0 && $invoice->last_recurring_date != null) {
                    $date_attrs['disabled'] = true;
                  }
                  ?>
                  <?php echo render_date_input('date','invoice_add_edit_date',$value,$date_attrs); ?>
               </div>
               <div class="col-md-6">
                  <?php
                  $value = '';
                  if(isset($invoice)){
                    $value = _d($invoice->duedate);
                  } else {
                    if(get_option('invoice_due_after') != 0){
                        $value = _d(date('Y-m-d', strtotime('+' . get_option('invoice_due_after') . ' DAY', strtotime(date('Y-m-d')))));
                    }
                  }
                   ?>
                  <?php echo render_date_input('duedate','invoice_add_edit_duedate',$value); ?>
               </div>
            </div>
            <?php $rel_id = (isset($invoice) ? $invoice->id : false); ?>
            <?php echo render_custom_fields('invoice',$rel_id); ?>
         </div>
         <div class="col-md-6">
            <div class="panel_s no-shadow">
               <h4 class="no-margin"><?php echo _l('invoice_add_edit_advanced_options'); ?></h4>
               <hr class="hr-panel-heading" />
               <div class="form-group mbot15">
                  <label for="allowed_payment_modes" class="control-label"><?php echo _l('invoice_add_edit_allowed_payment_modes'); ?></label>
                  <br />
                  <?php if(count($payment_modes) > 0){ ?>
                  <select class="selectpicker" name="allowed_payment_modes[]" multiple="true" data-width="100%" data-title="<?php echo _l('dropdown_non_selected_tex'); ?>">
                  <?php foreach($payment_modes as $mode){
                     $selected = '';
                     if(isset($invoice)){
                       if($invoice->allowed_payment_modes){
                        $inv_modes = unserialize($invoice->allowed_payment_modes);
                        if(is_array($inv_modes)) {
                         foreach($inv_modes as $_allowed_payment_mode){
                           if($_allowed_payment_mode == $mode['id']){
                             $selected = ' selected';
                           }
                         }
                       }
                     }
                     } else {
                     if($mode['selected_by_default'] == 1){
                        $selected = ' selected';
                     }
                     }
                     ?>
                     <option value="<?php echo $mode['id']; ?>"<?php echo $selected; ?>><?php echo $mode['name']; ?></option>
                  <?php } ?>
                  </select>
                  <?php } else { ?>
                  <p><?php echo _l('invoice_add_edit_no_payment_modes_found'); ?></p>
                  <a class="btn btn-info" href="<?php echo admin_url('paymentmodes'); ?>">
                  <?php echo _l('new_payment_mode'); ?>
                  </a>
                  <?php } ?>
               </div>

               <div class="row">
                  <div class="col-md-6">
                     <?php
                        $s_attrs = array('disabled'=>true,'data-show-subtext'=>true);
                        $s_attrs = do_action('invoice_currency_disabled',$s_attrs);
                        foreach($currencies as $currency){
                         if($currency['isdefault'] == 1){
                           $s_attrs['data-base'] = $currency['id'];
                         }
                         if(isset($invoice)){
                          if($currency['id'] == $invoice->currency){
                           $selected = $currency['id'];
                         }
                        } else {
                         if($currency['isdefault'] == 1){
                           $selected = $currency['id'];
                         }
                        }
                        }
                        ?>
                     <?php echo render_select('currency',$currencies,array('id','name','symbol'),'invoice_add_edit_currency',$selected,$s_attrs); ?>
                  </div>
                  <div class="col-md-6">
                     <?php
                        $i = 0;
                        $selected = '';
                        foreach($staff as $member){
                         if(isset($invoice)){
                           if($invoice->sale_agent == $member['staffid']) {
                             $selected = $member['staffid'];
                           }
                         }
                         $i++;
                        }
                        echo render_select('sale_agent',$staff,array('staffid',array('firstname','lastname')),'sale_agent_string',$selected);
                        ?>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <label for="recurring" class="control-label">
                        <?php echo _l('invoice_add_edit_recurring'); ?>
                        </label>
                        <select class="selectpicker" data-width="100%" name="recurring" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                           <?php for($i = 0; $i <=12; $i++){ ?>
                           <?php
                              $selected = '';
                              if(isset($invoice)){
                                if($invoice->custom_recurring == 0){
                                 if($invoice->recurring == $i){
                                   $selected = 'selected';
                                 }
                               }
                              }
                              if($i == 0){
                               $reccuring_string =  _l('invoice_add_edit_recurring_no');
                              } else if($i == 1){
                               $reccuring_string = _l('invoice_add_edit_recurring_month',$i);
                              } else {
                               $reccuring_string = _l('invoice_add_edit_recurring_months',$i);
                              }
                              ?>
                           <option value="<?php echo $i; ?>" <?php echo $selected; ?>><?php echo $reccuring_string; ?></option>
                           <?php } ?>
                           <option value="custom" <?php if(isset($invoice) && $invoice->recurring != 0 && $invoice->custom_recurring == 1){echo 'selected';} ?>><?php echo _l('recurring_custom'); ?></option>
                        </select>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <label for="discount_type" class="control-label"><?php echo _l('discount_type'); ?></label>
                        <select name="discount_type" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                           <option value="" selected><?php echo _l('no_discount'); ?></option>
                           <option value="before_tax" <?php
                              if(isset($invoice)){ if($invoice->discount_type == 'before_tax'){ echo 'selected'; }} ?>><?php echo _l('discount_type_before_tax'); ?></option>
                           <option value="after_tax" <?php if(isset($invoice)){if($invoice->discount_type == 'after_tax'){echo 'selected';}} ?>><?php echo _l('discount_type_after_tax'); ?></option>
                        </select>
                     </div>
                  </div>
                  <div class="recurring_custom <?php if((isset($invoice) && $invoice->custom_recurring != 1) || (!isset($invoice))){echo 'hide';} ?>">
                     <div class="col-md-6">
                        <?php $value = (isset($invoice) && $invoice->custom_recurring == 1 ? $invoice->recurring : 1); ?>
                        <?php echo render_input('repeat_every_custom','',$value,'number',array('min'=>1)); ?>
                     </div>
                     <div class="col-md-6">
                        <select name="repeat_type_custom" id="repeat_type_custom" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                           <option value="day" <?php if(isset($invoice) && $invoice->custom_recurring == 1 && $invoice->recurring_type == 'day'){echo 'selected';} ?>><?php echo _l('invoice_recurring_days'); ?></option>
                           <option value="week" <?php if(isset($invoice) && $invoice->custom_recurring == 1 && $invoice->recurring_type == 'week'){echo 'selected';} ?>><?php echo _l('invoice_recurring_weeks'); ?></option>
                           <option value="month" <?php if(isset($invoice) && $invoice->custom_recurring == 1 && $invoice->recurring_type == 'month'){echo 'selected';} ?>><?php echo _l('invoice_recurring_months'); ?></option>
                           <option value="year" <?php if(isset($invoice) && $invoice->custom_recurring == 1 && $invoice->recurring_type == 'year'){echo 'selected';} ?>><?php echo _l('invoice_recurring_years'); ?></option>
                        </select>
                     </div>
                  </div>
                  <div id="recurring_ends_on" class="<?php if(!isset($invoice) || (isset($invoice) && $invoice->recurring == 0)){echo 'hide';}?>">
                     <div class="col-md-12">
                        <?php $value = (isset($invoice) ? _d($invoice->recurring_ends_on) : ''); ?>
                        <?php echo render_date_input('recurring_ends_on','recurring_ends_on',$value); ?>
                     </div>
                  </div>
               </div>
               <?php $value = (isset($invoice) ? $invoice->adminnote : ''); ?>
               <?php echo render_textarea('adminnote','invoice_add_edit_admin_note',$value); ?>
               <?php if(get_option('cron_send_invoice_overdue_reminder') == 1){ ?>
               <div class="form-group">
                  <div class="checkbox checkbox-danger">
                     <input type="checkbox" <?php if(isset($invoice) && $invoice->cancel_overdue_reminders == 1){echo 'checked';} ?> id="cancel_overdue_reminders" name="cancel_overdue_reminders">
                     <label for="cancel_overdue_reminders"><?php echo _l('cancel_overdue_reminders_invoice') ?></label>
                  </div>
               </div>
               <?php } ?>
            </div>
         </div>
      </div>
   </div>
   <div class="panel-body mtop10">
      <div class="row">
         <div class="col-md-4">
            <div class="form-group mbot25 items-wrapper">
               <select name="item_select" class="selectpicker no-margin<?php if($ajaxItems == true){echo ' ajax-search';} ?>" data-width="100%" id="item_select" data-none-selected-text="<?php echo _l('add_item'); ?>" data-live-search="true">
                  <option value=""></option>
                  <?php foreach($items as $group_id=>$_items){ ?>
                  <optgroup data-group-id="<?php echo $group_id; ?>" label="<?php echo $_items[0]['group_name']; ?>">
                     <?php foreach($_items as $item){ ?>
                     <option value="<?php echo $item['id']; ?>" data-subtext="<?php echo strip_tags(mb_substr($item['long_description'],0,200)).'...'; ?>">(<?php echo _format_number($item['rate']); ; ?>) <?php echo $item['description']; ?></option>
                     <?php } ?>
                  </optgroup>
                  <?php } ?>
                  <?php if(has_permission('items','','create')){ ?>
                  <option data-divider="true" class="newitem-divider"></option>
                  <option value="newitem" class="newitem" data-content="<span class='text-info'><?php echo _l('new_invoice_item'); ?></span>"></option>
                  <?php } ?>
               </select>
            </div>
         </div>
         <?php if(!isset($invoice_from_project) && isset($billable_tasks)){ ?>
         <div class="col-md-3">
            <div class="form-group" data-toggle="tooltip" data-title="<?php echo _l('invoice_task_item_project_tasks_not_included'); ?>">
               <select name="task_select" data-live-search="true" id="task_select" class="selectpicker no-margin" data-width="100%" data-none-selected-text="<?php echo _l('bill_tasks'); ?>">
                  <option value=""></option>
                  <?php foreach($billable_tasks as $task_billable){ ?>
                  <option  value="<?php echo $task_billable['id']; ?>"<?php if($this->tasks_model->is_timer_started_for_task($task_billable['id'])){ ?>disabled class="text-danger important" data-subtext="<?php echo _l('invoice_task_billable_timers_found'); ?>" <?php } else {
                     $task_rel_data = get_relation_data($task_billable['rel_type'],$task_billable['rel_id']);
                     $task_rel_value = get_relation_values($task_rel_data,$task_billable['rel_type']);
                     ?>
                     data-subtext="<?php echo $task_rel_value['name']; ?>" <?php } ?>><?php echo $task_billable['name']; ?></option>
                  <?php } ?>
               </select>
            </div>
         </div>
         <?php } ?>
         <div class="col-md-<?php if(!isset($invoice_from_project)){ echo 5; }else {echo 8;} ?> text-right show_quantity_as_wrapper">
            <div class="mtop10">
               <span><?php echo _l('show_quantity_as'); ?> </span>
               <div class="radio radio-primary radio-inline">
                  <input type="radio" value="1" id="sq_1" name="show_quantity_as" data-text="<?php echo _l('invoice_table_quantity_heading'); ?>" <?php if(isset($invoice) && $invoice->show_quantity_as == 1){echo 'checked';}else if(!isset($hours_quantity) && !isset($qty_hrs_quantity)){echo'checked';} ?>>
                  <label for="sq_1"><?php echo _l('quantity_as_qty'); ?></label>
               </div>
               <div class="radio radio-primary radio-inline">
                  <input type="radio" value="2" id="sq_2" name="show_quantity_as" data-text="<?php echo _l('invoice_table_hours_heading'); ?>" <?php if(isset($invoice) && $invoice->show_quantity_as == 2 || isset($hours_quantity)){echo 'checked';} ?>>
                  <label for="sq_2"><?php echo _l('quantity_as_hours'); ?></label>
               </div>
               <div class="radio radio-primary radio-inline">
                  <input type="radio" value="3" id="sq_3" name="show_quantity_as" data-text="<?php echo _l('invoice_table_quantity_heading'); ?>/<?php echo _l('invoice_table_hours_heading'); ?>" <?php if(isset($invoice) && $invoice->show_quantity_as == 3 || isset($qty_hrs_quantity)){echo 'checked';} ?>>
                  <label for="sq_3"><?php echo _l('invoice_table_quantity_heading'); ?>/<?php echo _l('invoice_table_hours_heading'); ?></label>
               </div>
            </div>
         </div>
      </div>
      <?php if(isset($invoice_from_project)){ echo '<hr class="no-mtop" />'; } ?>
      <div class="table-responsive s_table">
         <table class="table invoice-items-table items table-main-invoice-edit no-mtop">
            <thead>
               <tr>
                  <th></th>
                  <th width="20%" class="text-left"><i class="fa fa-exclamation-circle" aria-hidden="true" data-toggle="tooltip" data-title="<?php echo _l('item_description_new_lines_notice'); ?>"></i> <?php echo _l('invoice_table_item_heading'); ?></th>
                  <th width="25%" class="text-left"><?php echo _l('invoice_table_item_description'); ?></th>
                  <?php
                     $qty_heading = _l('invoice_table_quantity_heading');
                     if(isset($invoice) && $invoice->show_quantity_as == 2 || isset($hours_quantity)){
                      $qty_heading = _l('invoice_table_hours_heading');
                     } else if(isset($invoice) && $invoice->show_quantity_as == 3){
                      $qty_heading = _l('invoice_table_quantity_heading') .'/'._l('invoice_table_hours_heading');
                     }
                     ?>
                  <th width="10%" class="text-left qty"><?php echo $qty_heading; ?></th>
                  <th width="15%" class="text-left"><?php echo _l('invoice_table_rate_heading'); ?></th>
                  <th width="20%" class="text-left"><?php echo _l('invoice_table_tax_heading'); ?></th>
                  <th width="10%" class="text-left"><?php echo _l('invoice_table_amount_heading'); ?></th>
                  <th align="center"><i class="fa fa-cog"></i></th>
               </tr>
            </thead>
            <tbody>
               <tr class="main">
                  <td></td>
                  <td>
                     <textarea name="description" class="form-control" rows="4" placeholder="<?php echo _l('item_description_placeholder'); ?>"></textarea>
                  </td>
                  <td>
                     <textarea name="long_description" rows="4" class="form-control" placeholder="<?php echo _l('item_long_description_placeholder'); ?>"></textarea>
                  </td>
                  <td>
                     <input type="number" name="quantity" min="0" value="1" class="form-control" placeholder="<?php echo _l('item_quantity_placeholder'); ?>">
                     <input type="text" placeholder="<?php echo _l('unit'); ?>" name="unit" class="form-control input-transparent text-right">
                  </td>
                  <td>
                     <input type="number" name="rate" class="form-control" placeholder="<?php echo _l('item_rate_placeholder'); ?>">
                  </td>
                  <td>
                     <?php
                        $default_tax = unserialize(get_option('default_tax'));
                        $select = '<select class="selectpicker display-block tax main-tax" data-width="100%" name="taxname" multiple data-none-selected-text="'._l('no_tax').'">';
                      //  $select .= '<option value=""'.(count($default_tax) == 0 ? ' selected' : '').'>'._l('no_tax').'</option>';
                        foreach($taxes as $tax){
                        $selected = '';
                         if(is_array($default_tax)){
                             if(in_array($tax['name'] . '|' . $tax['taxrate'],$default_tax)){
                                  $selected = ' selected ';
                             }
                        }
                        $select .= '<option value="'.$tax['name'].'|'.$tax['taxrate'].'"'.$selected.'data-taxrate="'.$tax['taxrate'].'" data-taxname="'.$tax['name'].'" data-subtext="'.$tax['name'].'">'.$tax['taxrate'].'%</option>';
                        }
                        $select .= '</select>';
                        echo $select;
                        ?>
                  </td>
                  <td></td>
                  <td>
                     <?php
                        $new_item = 'undefined';
                        if(isset($invoice)){
                         $new_item = true;
                        }
                        ?>
                     <button type="button" onclick="add_item_to_table('undefined','undefined',<?php echo $new_item; ?>); return false;" class="btn pull-right btn-info"><i class="fa fa-check"></i></button>
                  </td>
               </tr>
               <?php if (isset($invoice) || isset($add_items)) {
                  $i               = 1;
                  $items_indicator = 'newitems';
                  if (isset($invoice)) {
                    $add_items       = $invoice->items;
                    $items_indicator = 'items';
                  }
                  foreach ($add_items as $item) {
                    $manual    = false;
                    $table_row = '<tr class="sortable item">';
                    $table_row .= '<td class="dragger">';
                    if (!is_numeric($item['qty'])) {
                      $item['qty'] = 1;
                    }
                    $invoice_item_taxes = get_invoice_item_taxes($item['id']);
                    // passed like string
                    if ($item['id'] == 0) {
                        $invoice_item_taxes = $item['taxname'];
                        $manual             = true;
                    }
                    $table_row .= form_hidden('' . $items_indicator . '[' . $i . '][itemid]', $item['id']);
                    $amount = $item['rate'] * $item['qty'];
                    $amount = _format_number($amount);
                    // order input
                    $table_row .= '<input type="hidden" class="order" name="' . $items_indicator . '[' . $i . '][order]">';
                    $table_row .= '</td>';
                    $table_row .= '<td class="bold description"><textarea name="' . $items_indicator . '[' . $i . '][description]" class="form-control" rows="5">' . clear_textarea_breaks($item['description']) . '</textarea></td>';
                    $table_row .= '<td><textarea name="' . $items_indicator . '[' . $i . '][long_description]" class="form-control" rows="5">' . clear_textarea_breaks($item['long_description']) . '</textarea></td>';
                    $table_row .= '<td><input type="number" min="0" onblur="calculate_total();" onchange="calculate_total();" data-quantity name="' . $items_indicator . '[' . $i . '][qty]" value="' . $item['qty'] . '" class="form-control">';
                    $unit_placeholder = '';
                    if(!$item['unit']){
                      $unit_placeholder = _l('unit');
                      $item['unit'] = '';
                    }
                    $table_row .= '<input type="text" placeholder="'.$unit_placeholder.'" name="'.$items_indicator.'['.$i.'][unit]" class="form-control input-transparent text-right" value="'.$item['unit'].'">';
                    $table_row .= '</td>';
                    $table_row .= '<td class="rate"><input type="number" data-toggle="tooltip" title="' . _l('numbers_not_formatted_while_editing') . '" onblur="calculate_total();" onchange="calculate_total();" name="' . $items_indicator . '[' . $i . '][rate]" value="' . $item['rate'] . '" class="form-control"></td>';
                    $table_row .= '<td class="taxrate">' . $this->misc_model->get_taxes_dropdown_template('' . $items_indicator . '[' . $i . '][taxname][]', $invoice_item_taxes, 'invoice', $item['id'], true, $manual) . '</td>';
                    $table_row .= '<td class="amount">' . $amount . '</td>';
                    $table_row .= '<td><a href="#" class="btn btn-danger pull-left" onclick="delete_item(this,' . $item['id'] . '); return false;"><i class="fa fa-times"></i></a></td>';
                    if (isset($item['task_id'])) {
                      if (!is_array($item['task_id'])) {
                        $table_row .= form_hidden('billed_tasks['.$i.'][]', $item['task_id']);
                      } else {
                        foreach ($item['task_id'] as $task_id) {
                          $table_row .= form_hidden('billed_tasks['.$i.'][]', $task_id);
                        }
                      }
                    } else if (isset($item['expense_id'])) {
                      $table_row .= form_hidden('billed_expenses['.$i.'][]', $item['expense_id']);
                    }
                    $table_row .= '</tr>';
                    echo $table_row;
                    $i++;
                  }
                  }
                  ?>
            </tbody>
         </table>
      </div>
      <div class="col-md-8 col-md-offset-4">
         <table class="table text-right">
            <tbody>
               <tr id="subtotal">
                  <td><span class="bold"><?php echo _l('invoice_subtotal'); ?> :</span>
                  </td>
                  <td class="subtotal">
                  </td>
               </tr>
               <tr id="discount_percent">
                  <td>
                     <div class="row">
                        <div class="col-md-7">
                           <span class="bold"><?php echo _l('invoice_discount'); ?> (%)</span>
                        </div>
                        <div class="col-md-5">
                           <?php
                              $discount_percent = 0;
                              if(isset($invoice)){
                               if($invoice->discount_percent != 0){
                                 $discount_percent =  $invoice->discount_percent;
                               }
                              }
                              ?>
                           <input type="number" value="<?php echo $discount_percent; ?>" class="form-control pull-left" min="0" max="100" name="discount_percent">
                        </div>
                     </div>
                  </td>
                  <td class="discount_percent"></td>
               </tr>
               <tr>
                  <td>
                     <div class="row">
                        <div class="col-md-7">
                           <span class="bold"><?php echo _l('invoice_adjustment'); ?></span>
                        </div>
                        <div class="col-md-5">
                           <input type="number" data-toggle="tooltip" data-title="<?php echo _l('numbers_not_formatted_while_editing'); ?>" value="<?php if(isset($invoice)){echo $invoice->adjustment; } else { echo 0; } ?>" class="form-control pull-left" name="adjustment">
                        </div>
                     </div>
                  </td>
                  <td class="adjustment"></td>
               </tr>
               <tr>
                  <td><span class="bold"><?php echo _l('invoice_total'); ?> :</span>
                  </td>
                  <td class="total">
                  </td>
               </tr>
            </tbody>
         </table>
      </div>
      <div id="removed-items"></div>
      <div id="billed-tasks"></div>
      <div id="billed-expenses"></div>
      <?php echo form_hidden('task_id'); ?>
      <?php echo form_hidden('expense_id'); ?>
   </div>
   <div class="row">
      <div class="col-md-12 mtop15">
         <div class="panel-body bottom-transaction">
            <?php $value = (isset($invoice) ? $invoice->clientnote : get_option('predefined_clientnote_invoice')); ?>
            <?php echo render_textarea('clientnote','invoice_add_edit_client_note',$value,array(),array(),'mtop15'); ?>
            <?php $value = (isset($invoice) ? $invoice->terms : get_option('predefined_terms_invoice')); ?>
            <?php echo render_textarea('terms','terms_and_conditions',$value,array(),array(),'mtop15'); ?>
            <div class="btn-bottom-toolbar text-right">
                <?php if(!isset($invoice)){ ?>
                <button class="btn-tr btn btn-default mleft10 text-right invoice-form-submit save-as-draft transaction-submit">
                <?php echo _l('save_as_draft'); ?>
                </button>
                <?php } ?>
                <button class="btn-tr btn btn-info mleft10 text-right invoice-form-submit save-and-send transaction-submit">
                  <?php echo _l('save_and_send'); ?>
                </button>
                <button class="btn-tr btn btn-info mleft10 text-right invoice-form-submit transaction-submit">
                  <?php echo _l('submit'); ?>
                </button>
             </div>
         </div>
        <div class="btn-bottom-pusher"></div>
      </div>
   </div>
</div>
