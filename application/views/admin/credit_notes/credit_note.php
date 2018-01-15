<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <?php
            echo form_open($this->uri->uri_string(),array('id'=>'credit-note-form','class'=>'_transaction_form credit-note-form'));
            if(isset($credit_note)){
              echo form_hidden('isedit');
            }
            ?>
         <div class="col-md-12">
            <div class="panel_s credit_note accounting-template">
               <div class="additional"></div>
               <div class="panel-body">
                  <?php if(isset($credit_note)){ ?>
                  <?php echo format_credit_note_status($credit_note->status); ?>
                  <hr class="hr-panel-heading" />
                  <?php } ?>
                  <div class="row">
                     <div class="col-md-6">
                        <div class="f_client_id">
                           <div class="form-group">
                              <label for="clientid"><?php echo _l('client'); ?></label>
                              <select id="clientid" name="clientid" data-live-search="true" data-width="100%" class="ajax-search" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                              <?php $selected = (isset($credit_note) ? $credit_note->clientid : '');
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
                        <div class="form-group projects-wrapper<?php if((!isset($credit_note)) || (isset($credit_note) && !customer_has_projects($credit_note->clientid))){ echo ' hide';} ?>">
                          <label for="project_id"><?php echo _l('project'); ?></label>
                          <div id="project_ajax_search_wrapper">
                           <select name="project_id" id="project_id" class="projects ajax-search" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                             <?php
                             if(isset($credit_note) && $credit_note->project_id != 0){
                               echo '<option value="'.$credit_note->project_id.'" selected>'.get_project_name_by_id($credit_note->project_id).'</option>';
                            }
                            ?>
                         </select>
                      </div>
                   </div>
                        <div class="row">
                           <div class="col-md-12">
                              <hr class="hr-10" />
                              <a href="#" class="edit_shipping_billing_info" data-toggle="modal" data-target="#billing_and_shipping_details"><i class="fa fa-pencil-square-o"></i></a>
                              <?php include_once(APPPATH .'views/admin/credit_notes/billing_and_shipping_template.php'); ?>
                           </div>
                           <div class="col-md-6">
                              <p class="bold"><?php echo _l('credit_note_bill_to'); ?></p>
                              <address>
                                 <span class="billing_street">
                                 <?php $billing_street = (isset($credit_note) ? $credit_note->billing_street : '--'); ?>
                                 <?php $billing_street = ($billing_street == '' ? '--' :$billing_street); ?>
                                 <?php echo $billing_street; ?></span><br>
                                 <span class="billing_city">
                                 <?php $billing_city = (isset($credit_note) ? $credit_note->billing_city : '--'); ?>
                                 <?php $billing_city = ($billing_city == '' ? '--' :$billing_city); ?>
                                 <?php echo $billing_city; ?></span>,
                                 <span class="billing_state">
                                 <?php $billing_state = (isset($credit_note) ? $credit_note->billing_state : '--'); ?>
                                 <?php $billing_state = ($billing_state == '' ? '--' :$billing_state); ?>
                                 <?php echo $billing_state; ?></span>
                                 <br/>
                                 <span class="billing_country">
                                 <?php $billing_country = (isset($credit_note) ? get_country_short_name($credit_note->billing_country) : '--'); ?>
                                 <?php $billing_country = ($billing_country == '' ? '--' :$billing_country); ?>
                                 <?php echo $billing_country; ?></span>,
                                 <span class="billing_zip">
                                 <?php $billing_zip = (isset($credit_note) ? $credit_note->billing_zip : '--'); ?>
                                 <?php $billing_zip = ($billing_zip == '' ? '--' :$billing_zip); ?>
                                 <?php echo $billing_zip; ?></span>
                              </address>
                           </div>
                           <div class="col-md-6">
                              <p class="bold"><?php echo _l('ship_to'); ?></p>
                              <address>
                                 <span class="shipping_street">
                                 <?php $shipping_street = (isset($credit_note) ? $credit_note->shipping_street : '--'); ?>
                                 <?php $shipping_street = ($shipping_street == '' ? '--' :$shipping_street); ?>
                                 <?php echo $shipping_street; ?></span><br>
                                 <span class="shipping_city">
                                 <?php $shipping_city = (isset($credit_note) ? $credit_note->shipping_city : '--'); ?>
                                 <?php $shipping_city = ($shipping_city == '' ? '--' :$shipping_city); ?>
                                 <?php echo $shipping_city; ?></span>,
                                 <span class="shipping_state">
                                 <?php $shipping_state = (isset($credit_note) ? $credit_note->shipping_state : '--'); ?>
                                 <?php $shipping_state = ($shipping_state == '' ? '--' :$shipping_state); ?>
                                 <?php echo $shipping_state; ?></span>
                                 <br/>
                                 <span class="shipping_country">
                                 <?php $shipping_country = (isset($credit_note) ? get_country_short_name($credit_note->shipping_country) : '--'); ?>
                                 <?php $shipping_country = ($shipping_country == '' ? '--' :$shipping_country); ?>
                                 <?php echo $shipping_country; ?></span>,
                                 <span class="shipping_zip">
                                 <?php $shipping_zip = (isset($credit_note) ? $credit_note->shipping_zip : '--'); ?>
                                 <?php $shipping_zip = ($shipping_zip == '' ? '--' :$shipping_zip); ?>
                                 <?php echo $shipping_zip; ?></span>
                              </address>
                           </div>
                        </div>
                        <div class="row">
                           <div class="col-md-6">
                              <?php
                                 $next_credit_note_number = get_option('next_credit_note_number');
                                 $prefix = get_option('credit_note_prefix');
                                 $__number = $next_credit_note_number;
                                 if(isset($credit_note)){
                                   $__number = $credit_note->number;
                                   $prefix = '<span id="prefix">' . $credit_note->prefix . '</span>';
                                 }
                                 $_credit_note_number = str_pad($__number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
                                 if(isset($credit_note)){
                                   $isedit = 'true';
                                   $data_original_number = $credit_note->number;
                                 } else {
                                   $isedit = 'false';
                                   $data_original_number = 'false';
                                 }
                                 ?>
                              <div class="form-group">
                                 <label for="number"><?php echo _l('credit_note_number'); ?></label>
                                 <div class="input-group">
                                    <span class="input-group-addon">
                                    <?php if(isset($credit_note)){ ?>
                                    <a href="#" onclick="return false;" data-toggle="popover" data-container='._transaction_form' data-html="true" data-content="<label class='control-label'><?php echo _l('credit_note_prefix'); ?></label><div class='input-group'><input name='s_prefix' type='text' class='form-control' value='<?php echo $credit_note->prefix; ?>'></div><button type='button' onclick='save_sales_number_settings(this); return false;' data-url='<?php echo admin_url('credit_notes/update_number_settings/'.$credit_note->id); ?>' class='btn btn-info btn-block mtop15'><?php echo _l('submit'); ?></button>"><i class="fa fa-cog"></i></a>
                                    <?php } ?>
                                    <?php echo $prefix; ?></span>
                                    <input type="text" name="number" class="form-control" value="<?php echo $_credit_note_number; ?>" data-isedit="<?php echo $isedit; ?>" data-original-number="<?php echo $data_original_number; ?>">
                                 </div>
                              </div>
                           </div>
                           <div class="col-md-6">
                              <?php $value = (isset($credit_note) ? _d($credit_note->date) : _d(date('Y-m-d'))); ?>
                              <?php echo render_date_input('date','credit_note_date',$value); ?>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="panel_s no-shadow">
                           <div class="row">
                              <div class="col-md-6">
                                 <?php
                                    $s_attrs = array('disabled'=>true,'data-show-subtext'=>true);
                                    $s_attrs = do_action('credit_note_currency_disabled',$s_attrs);
                                    foreach($currencies as $currency){
                                      if($currency['isdefault'] == 1){
                                       $s_attrs['data-base'] = $currency['id'];
                                     }
                                     if(isset($credit_note)){
                                       if($currency['id'] == $credit_note->currency){
                                        $selected = $currency['id'];
                                      }
                                    } else {
                                      if($currency['isdefault'] == 1){
                                        $selected = $currency['id'];
                                      }
                                    }
                                    }
                                    ?>
                                 <?php echo render_select('currency',$currencies,array('id','name','symbol'),'currency',$selected,$s_attrs); ?>
                              </div>
                              <div class="col-md-6">
                                 <div class="form-group">
                                    <label for="discount_type" class="control-label"><?php echo _l('discount_type'); ?></label>
                                    <select name="discount_type" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                       <option value="" selected><?php echo _l('no_discount'); ?></option>
                                       <option value="before_tax" <?php
                                          if(isset($credit_note)){ if($credit_note->discount_type == 'before_tax'){ echo 'selected'; }} ?>><?php echo _l('discount_type_before_tax'); ?></option>
                                       <option value="after_tax" <?php if(isset($credit_note)){if($credit_note->discount_type == 'after_tax'){echo 'selected';}} ?>><?php echo _l('discount_type_after_tax'); ?></option>
                                    </select>
                                 </div>
                              </div>
                           </div>
                           <?php $value = (isset($credit_note) ? $credit_note->reference_no : ''); ?>
                           <?php echo render_input('reference_no','reference_no',$value); ?>
                           <?php $value = (isset($credit_note) ? $credit_note->adminnote : ''); ?>
                           <?php echo render_textarea('adminnote','credit_note_admin_note',$value); ?>
                            <?php $rel_id = (isset($credit_note) ? $credit_note->id : false); ?>
                            <?php echo render_custom_fields('credit_note',$rel_id); ?>
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
                     <div class="col-md-8 text-right show_quantity_as_wrapper">
                        <div class="mtop10">
                           <span><?php echo _l('show_quantity_as'); ?> </span>
                           <div class="radio radio-primary radio-inline">
                              <input type="radio" value="1" id="sq_1" name="show_quantity_as" data-text="<?php echo _l('credit_note_table_quantity_heading'); ?>" <?php if(isset($credit_note) && $credit_note->show_quantity_as == 1){echo 'checked';}else if(!isset($hours_quantity) && !isset($qty_hrs_quantity)){echo'checked';} ?>>
                              <label for="sq_1"><?php echo _l('quantity_as_qty'); ?></label>
                           </div>
                           <div class="radio radio-primary radio-inline">
                              <input type="radio" value="2" id="sq_2" name="show_quantity_as" data-text="<?php echo _l('credit_note_table_hours_heading'); ?>" <?php if(isset($credit_note) && $credit_note->show_quantity_as == 2 || isset($hours_quantity)){echo 'checked';} ?>>
                              <label for="sq_2"><?php echo _l('quantity_as_hours'); ?></label>
                           </div>
                           <div class="radio radio-primary radio-inline">
                              <input type="radio" value="3" id="sq_3" name="show_quantity_as" data-text="<?php echo _l('credit_note_table_quantity_heading'); ?>/<?php echo _l('credit_note_table_hours_heading'); ?>" <?php if(isset($credit_note) && $credit_note->show_quantity_as == 3 || isset($qty_hrs_quantity)){echo 'checked';} ?>>
                              <label for="sq_3"><?php echo _l('credit_note_table_quantity_heading'); ?>/<?php echo _l('credit_note_table_hours_heading'); ?></label>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="table-responsive s_table">
                     <table class="table credite-note-items-table items table-main-credit-note-edit no-mtop">
                        <thead>
                           <tr>
                              <th></th>
                              <th width="20%" class="text-left"><i class="fa fa-exclamation-circle" aria-hidden="true" data-toggle="tooltip" data-title="<?php echo _l('item_description_new_lines_notice'); ?>"></i> <?php echo _l('credit_note_table_item_heading'); ?></th>
                              <th width="25%" class="text-left"><?php echo _l('credit_note_table_item_description'); ?></th>
                              <?php
                                 $qty_heading = _l('credit_note_table_quantity_heading');
                                 if(isset($credit_note) && $credit_note->show_quantity_as == 2 || isset($hours_quantity)){
                                   $qty_heading = _l('credit_note_table_hours_heading');
                                 } else if(isset($credit_note) && $credit_note->show_quantity_as == 3){
                                   $qty_heading = _l('credit_note_table_quantity_heading') .'/'._l('credit_note_table_hours_heading');
                                 }
                                 ?>
                              <th width="10%" class="text-left qty"><?php echo $qty_heading; ?></th>
                              <th width="15%" class="text-left"><?php echo _l('credit_note_table_rate_heading'); ?></th>
                              <th width="20%" class="text-left"><?php echo _l('credit_note_table_tax_heading'); ?></th>
                              <th width="10%" class="text-left"><?php echo _l('credit_note_table_amount_heading'); ?></th>
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
                                    if(isset($credit_note)){
                                      $new_item = true;
                                    }
                                    ?>
                                 <button type="button" onclick="add_item_to_table('undefined','undefined',<?php echo $new_item; ?>); return false;" class="btn pull-right btn-info"><i class="fa fa-check"></i></button>
                              </td>
                           </tr>
                           <?php if (isset($credit_note) || isset($add_items)) {
                              $i               = 1;
                              $items_indicator = 'newitems';
                              if (isset($credit_note)) {
                                $add_items       = $credit_note->items;
                                $items_indicator = 'items';
                              }
                              foreach ($add_items as $item) {
                                $manual    = false;
                                $table_row = '<tr class="sortable item">';
                                $table_row .= '<td class="dragger">';
                                if (!is_numeric($item['qty'])) {
                                  $item['qty'] = 1;
                                }
                                $credit_note_item_taxes = get_credit_note_item_taxes($item['id']);
                              // passed like string
                                if ($item['id'] == 0) {
                                  $credit_note_item_taxes = $item['taxname'];
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
                                $table_row .= '<td class="taxrate">' . $this->misc_model->get_taxes_dropdown_template('' . $items_indicator . '[' . $i . '][taxname][]', $credit_note_item_taxes, 'credit_note', $item['id'], true, $manual) . '</td>';
                                $table_row .= '<td class="amount">' . $amount . '</td>';
                                $table_row .= '<td><a href="#" class="btn btn-danger pull-left" onclick="delete_item(this,' . $item['id'] . '); return false;"><i class="fa fa-times"></i></a></td>';
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
                              <td><span class="bold"><?php echo _l('credit_note_subtotal'); ?> :</span>
                              </td>
                              <td class="subtotal">
                              </td>
                           </tr>
                           <tr id="discount_percent">
                              <td>
                                 <div class="row">
                                    <div class="col-md-7">
                                       <span class="bold"><?php echo _l('credit_note_discount'); ?> (%)</span>
                                    </div>
                                    <div class="col-md-5">
                                       <?php
                                          $discount_percent = 0;
                                          if(isset($credit_note)){
                                            if($credit_note->discount_percent != 0){
                                              $discount_percent =  $credit_note->discount_percent;
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
                                       <span class="bold"><?php echo _l('credit_note_adjustment'); ?></span>
                                    </div>
                                    <div class="col-md-5">
                                       <input type="number" data-toggle="tooltip" data-title="<?php echo _l('numbers_not_formatted_while_editing'); ?>" value="<?php if(isset($credit_note)){echo $credit_note->adjustment; } else { echo 0; } ?>" class="form-control pull-left" name="adjustment">
                                    </div>
                                 </div>
                              </td>
                              <td class="adjustment"></td>
                           </tr>
                           <tr>
                              <td><span class="bold"><?php echo _l('credit_note_total'); ?> :</span>
                              </td>
                              <td class="total">
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
                  <div id="removed-items"></div>
               </div>
               <div class="row">
                  <div class="col-md-12 mtop15">
                     <div class="panel-body bottom-transaction">
                        <?php $value = (isset($credit_note) ? $credit_note->clientnote : get_option('predefined_clientnote_credit_note')); ?>
                        <?php echo render_textarea('clientnote','credit_note_add_edit_client_note',$value,array(),array(),'mtop15'); ?>
                        <?php $value = (isset($credit_note) ? $credit_note->terms : get_option('predefined_terms_credit_note')); ?>
                        <?php echo render_textarea('terms','terms_and_conditions',$value,array(),array(),'mtop15'); ?>
                        <div class="btn-bottom-toolbar text-right">
                            <button type="button" class="btn-tr btn btn-info mleft10 credit-note-form-submit save-and-send transaction-submit">
                            <?php echo _l('save_and_send'); ?>
                            </button>
                           <button class="btn-tr btn btn-info mleft10 text-right credit-note-form-submit transaction-submit">
                              <?php echo _l('submit'); ?>
                           </button>
                        </div>
                     </div>
                     <div class="btn-bottom-pusher"></div>
                  </div>
               </div>
            </div>
         </div>
         <?php echo form_close(); ?>
         <?php $this->load->view('admin/invoice_items/item'); ?>
      </div>
   </div>
</div>
<?php init_tail(); ?>
<script>
   $(function(){
     validate_credit_note_form();
       // Init accountacy currency symbol
       init_currency_symbol();
       init_ajax_project_search_by_customer_id();
       // Maybe items ajax search
       init_ajax_search('items','#item_select.ajax-search',undefined,admin_url+'items/search');
     });
</script>
</body>
</html>
