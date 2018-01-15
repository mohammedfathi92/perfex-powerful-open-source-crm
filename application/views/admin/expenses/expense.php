<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <?php
            if(isset($expense)){
             echo form_hidden('is_edit','true');
            }
            ?>
         <?php echo form_open_multipart($this->uri->uri_string(),array('id'=>'expense-form','class'=>'dropzone dropzone-manual')) ;?>
         <div class="col-md-6">
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin"><?php echo $title; ?></h4>
                  <hr class="hr-panel-heading" />
                  <?php if(isset($expense) && $expense->attachment !== ''){ ?>
                  <div class="row">
                     <div class="col-md-10">
                        <i class="<?php echo get_mime_class($expense->filetype); ?>"></i> <a href="<?php echo site_url('download/file/expense/'.$expense->expenseid); ?>"><?php echo $expense->attachment; ?></a>
                     </div>
                     <?php if($expense->attachment_added_from == get_staff_user_id() || is_admin()){ ?>
                     <div class="col-md-2 text-right">
                        <a href="<?php echo admin_url('expenses/delete_expense_attachment/'.$expense->expenseid); ?>" class="text-danger _delete"><i class="fa fa fa-times"></i></a>
                     </div>
                     <?php } ?>
                  </div>
                  <?php } ?>
                  <?php if(!isset($expense) || (isset($expense) && $expense->attachment == '')){ ?>
                  <div id="dropzoneDragArea" class="dz-default dz-message">
                     <span><?php echo _l('expense_add_edit_attach_receipt'); ?></span>
                  </div>
                  <div class="dropzone-previews"></div>
                  <?php } ?>
                  <hr class="hr-panel-heading" />
                  <i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('expense_name_help'); ?> - <?php echo _l('expense_field_billable_help',_l('expense_name')); ?>"></i>
                  <?php $value = (isset($expense) ? $expense->expense_name : ''); ?>
                  <?php echo render_input('expense_name','expense_name',$value); ?>
                  <i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('expense_field_billable_help',_l('expense_add_edit_note')); ?>"></i>
                  <?php $value = (isset($expense) ? $expense->note : ''); ?>
                  <?php echo render_textarea('note','expense_add_edit_note',$value,array('rows'=>4),array()); ?>
                   <?php
                     $selected = (isset($expense) ? $expense->category : '');
                     if(is_admin() || get_option('staff_members_create_inline_expense_categories') == '1'){
                       echo render_select_with_input_group('category',$categories,array('id','name'),'expense_category',$selected,'<a href="#" onclick="new_category();return false;"><i class="fa fa-plus"></i></a>');
                     } else {
                        echo render_select('category',$categories,array('id','name'),'expense_category',$selected);
                     }
                   ?>
                  <?php $value = (isset($expense) ? _d($expense->date) : _d(date('Y-m-d')));
                    $date_attrs = array();
                    if(isset($expense) && $expense->recurring > 0 && $expense->last_recurring_date != null) {
                      $date_attrs['disabled'] = true;
                    }
                   ?>
                  <?php echo render_date_input('date','expense_add_edit_date',$value,$date_attrs);
                     $value = (isset($expense) ? $expense->amount : ''); ?>
                  <?php echo render_input('amount','expense_add_edit_amount',$value,'number');
                     $_hide = 'hide';
                     if(!isset($expense) && !isset($customer_id)){
                       $_hide = 'hide';
                     } else {
                       if((isset($expense)&&($expense->billable == 1 || $expense->clientid != 0)) || isset($customer_id)){
                         $_hide = '';
                       }
                     }
                     ?>
                  <div class="checkbox checkbox-primary billable <?php echo $_hide; ?>">
                     <input type="checkbox" id="billable" <?php if(isset($expense) && $expense->invoiceid !== NULL){echo 'disabled'; } ?> name="billable" <?php if(isset($expense)){if($expense->billable == 1){echo 'checked';}}; ?>>
                     <label for="billable" <?php if(isset($expense) && $expense->invoiceid !== NULL){echo 'data-toggle="tooltip" title="'._l('expense_already_invoiced').'"'; } ?>><?php echo _l('expense_add_edit_billable'); ?></label>
                  </div>
                  <div class="form-group">
                     <label for="clientid"><?php echo _l('expense_add_edit_customer'); ?></label>
                     <select id="clientid" name="clientid" data-live-search="true" data-width="100%" class="ajax-search" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                     <?php $selected = (isset($expense) ? $expense->clientid : '');
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
                  <?php $hide_project_selector = ' hide';
                     // Show selector only if expense is already added and there is no client linked to the expense or isset customer id
                     if((isset($expense) && $expense->clientid != 0) || isset($customer_id)){
                     $hide_project_selector = '';
                     }
                     ?>
                  <div class="form-group projects-wrapper<?php echo $hide_project_selector; ?>">
                     <label for="project_id"><?php echo _l('project'); ?></label>
                    <div id="project_ajax_search_wrapper">
                     <select name="project_id" id="project_id" class="projects ajax-search" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                      <?php if(isset($expense) && $expense->project_id != 0){
                        echo '<option value="'.$expense->project_id.'" selected>'.get_project_name_by_id($expense->project_id).'</option>';
                      }
                      ?>
                     </select>
                     </div>
                  </div>
                  <?php $rel_id = (isset($expense) ? $expense->expenseid : false); ?>
                  <?php echo render_custom_fields('expenses',$rel_id); ?>
                   <div class="btn-bottom-toolbar text-right">
                  <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-md-6">
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin"><?php echo _l('advanced_options'); ?></h4>
                  <hr class="hr-panel-heading" />
                  <?php
                     $s_attrs = array('disabled'=>true,'data-show-subtext'=>true);
                     $s_attrs = do_action('expense_currency_disabled',$s_attrs);
                     foreach($currencies as $currency){
                      if($currency['isdefault'] == 1){
                        $s_attrs['data-base'] = $currency['id'];
                      }
                      if(isset($expense)){
                        if($currency['id'] == $expense->currency){
                          $selected = $currency['id'];
                        }
                        if($expense->billable == 0){
                          if($expense->clientid != 0){
                            $c = $this->clients_model->get_customer_default_currency($expense->clientid);
                            if($c != 0){
                              $customer_currency = $c;
                            }
                          }
                        }
                      } else {
                        if(isset($customer_id)){
                          $c = $this->clients_model->get_customer_default_currency($customer_id);
                          if($c != 0){
                            $customer_currency = $c;
                          }
                        }
                        if($currency['isdefault'] == 1){
                          $selected = $currency['id'];
                        }
                      }
                     }
                     ?>
                  <div id="expense_currency">
                     <?php echo render_select('currency',$currencies,array('id','name','symbol'),'expense_currency',$selected,$s_attrs); ?>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                           <label class="control-label" for="tax"><?php echo _l('tax_1'); ?></label>
                           <select class="selectpicker display-block" data-width="100%" name="tax" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                              <option value=""><?php echo _l('no_tax'); ?></option>
                              <?php foreach($taxes as $tax){
                                 $selected = '';
                                 if(isset($expense)){
                                   if($tax['id'] == $expense->tax){
                                     $selected = 'selected';
                                   }
                                 } ?>
                              <option value="<?php echo $tax['id']; ?>" <?php echo $selected; ?> data-subtext="<?php echo $tax['name']; ?>"><?php echo $tax['taxrate']; ?>%</option>
                              <?php } ?>
                           </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                          <div class="form-group">
                           <label class="control-label" for="tax2"><?php echo _l('tax_2'); ?></label>
                           <select class="selectpicker display-block" data-width="100%" name="tax2" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" <?php if(!isset($expense) || isset($expense) && $expense->tax == 0){echo 'disabled';} ?>>
                              <option value=""><?php echo _l('no_tax'); ?></option>
                              <?php foreach($taxes as $tax){
                                 $selected = '';
                                 if(isset($expense)){
                                   if($tax['id'] == $expense->tax2){
                                     $selected = 'selected';
                                   }
                                 } ?>
                              <option value="<?php echo $tax['id']; ?>" <?php echo $selected; ?> data-subtext="<?php echo $tax['name']; ?>"><?php echo $tax['taxrate']; ?>%</option>
                              <?php } ?>
                           </select>
                        </div>
                    </div>
                  </div>
                  <div class="clearfix mtop15"></div>
                  <div class="row">
                     <div class="col-md-6">
                        <?php $selected = (isset($expense) ? $expense->paymentmode : ''); ?>
                        <?php echo render_select('paymentmode',$payment_modes,array('id','name'),'payment_mode',$selected); ?>
                     </div>
                     <div class="col-md-6">
                    <?php $value = (isset($expense) ? $expense->reference_no : ''); ?>
                    <?php echo render_input('reference_no','expense_add_edit_reference_no',$value); ?>
                     </div>
                  </div>
                  <div class="form-group">
                     <label for="repeat_every" class="control-label"><?php echo _l('expense_repeat_every'); ?></label>
                     <select name="repeat_every" id="repeat_every" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                        <option value=""></option>
                        <option value="1-week" <?php if(isset($expense) && $expense->repeat_every == 1 && $expense->recurring_type == 'week'){echo 'selected';} ?>><?php echo _l('week'); ?></option>
                        <option value="2-week" <?php if(isset($expense) && $expense->repeat_every == 2 && $expense->recurring_type == 'week'){echo 'selected';} ?>>2 <?php echo _l('weeks'); ?></option>
                        <option value="1-month" <?php if(isset($expense) && $expense->repeat_every == 1 && $expense->recurring_type == 'month'){echo 'selected';} ?>>1 <?php echo _l('month'); ?></option>
                        <option value="2-month" <?php if(isset($expense) && $expense->repeat_every == 2 && $expense->recurring_type == 'month'){echo 'selected';} ?>>2 <?php echo _l('months'); ?></option>
                        <option value="3-month" <?php if(isset($expense) && $expense->repeat_every == 3 && $expense->recurring_type == 'month'){echo 'selected';} ?>>3 <?php echo _l('months'); ?></option>
                        <option value="6-month" <?php if(isset($expense) && $expense->repeat_every == 6 && $expense->recurring_type == 'month'){echo 'selected';} ?>>6 <?php echo _l('months'); ?></option>
                        <option value="1-year" <?php if(isset($expense) && $expense->repeat_every == 1 && $expense->recurring_type == 'year'){echo 'selected';} ?>>1 <?php echo _l('year'); ?></option>
                        <option value="custom" <?php if(isset($expense) && $expense->custom_recurring == 1){echo 'selected';} ?>><?php echo _l('recurring_custom'); ?></option>
                     </select>
                  </div>
                  <div class="recurring_custom <?php if((isset($expense) && $expense->custom_recurring != 1) || (!isset($expense))){echo 'hide';} ?>">
                     <div class="row">
                        <div class="col-md-6">
                           <?php $value = (isset($expense) && $expense->custom_recurring == 1 ? $expense->repeat_every : 1); ?>
                           <?php echo render_input('repeat_every_custom','',$value,'number',array('min'=>1)); ?>
                        </div>
                        <div class="col-md-6">
                           <select name="repeat_type_custom" id="repeat_type_custom" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                              <option value="day" <?php if(isset($expense) && $expense->custom_recurring == 1 && $expense->recurring_type == 'day'){echo 'selected';} ?>><?php echo _l('expense_recurring_days'); ?></option>
                              <option value="week" <?php if(isset($expense) && $expense->custom_recurring == 1 && $expense->recurring_type == 'week'){echo 'selected';} ?>><?php echo _l('expense_recurring_weeks'); ?></option>
                              <option value="month" <?php if(isset($expense) && $expense->custom_recurring == 1 && $expense->recurring_type == 'month'){echo 'selected';} ?>><?php echo _l('expense_recurring_months'); ?></option>
                              <option value="year" <?php if(isset($expense) && $expense->custom_recurring == 1 && $expense->recurring_type == 'year'){echo 'selected';} ?>><?php echo _l('expense_recurring_years'); ?></option>
                           </select>
                        </div>
                     </div>
                  </div>
                  <div id="recurring_ends_on" class="<?php if(!isset($expense) || (isset($expense) && $expense->recurring == 0)){echo 'hide';}?>">
                     <?php $value = (isset($expense) ? _d($expense->recurring_ends_on) : ''); ?>
                     <?php echo render_date_input('recurring_ends_on','recurring_ends_on',$value); ?>
                  </div>
                  <div>
                     <div class="checkbox checkbox-primary billable_recurring_options <?php echo $_hide; ?>">
                        <input type="checkbox" id="create_invoice_billable" name="create_invoice_billable" <?php if(isset($expense)){if($expense->create_invoice_billable == 1){echo 'checked';}}; ?>>
                        <label for="create_invoice_billable"><i class="fa fa-question-circle" data-toggle="tooltip" title="<?php echo _l('expense_recurring_autocreate_invoice_tooltip'); ?>"></i> <?php echo _l('expense_recurring_auto_create_invoice'); ?></label>
                     </div>
                  </div>
                  <div class="checkbox checkbox-primary billable_recurring_options <?php echo $_hide; ?>">
                     <input type="checkbox" name="send_invoice_to_customer" id="send_invoice_to_customer" <?php if(isset($expense)){if($expense->send_invoice_to_customer == 1){echo 'checked';}}; ?>>
                     <label for="send_invoice_to_customer"><?php echo _l('expense_recurring_send_custom_on_renew'); ?></label>
                  </div>
               </div>
            </div>
         </div>
         <?php echo form_close(); ?>
      </div>
      <div class="btn-bottom-pusher"></div>
   </div>
</div>
<?php $this->load->view('admin/expenses/expense_category'); ?>
<?php init_tail(); ?>
<script>
   var customer_currency = '';
   Dropzone.options.expenseForm = false;
   var expenseDropzone;
   init_ajax_project_search_by_customer_id();
   var selectCurrency = $('select[name="currency"]');
   <?php if(isset($customer_currency)){ ?>
     var customer_currency = '<?php echo $customer_currency; ?>';
   <?php } ?>
     $(function(){

     if($('#dropzoneDragArea').length > 0){
        expenseDropzone = new Dropzone("#expense-form",  $.extend({},_dropzone_defaults(),{
          autoProcessQueue: false,
          clickable: '#dropzoneDragArea',
          previewsContainer: '.dropzone-previews',
          addRemoveLinks: true,
          maxFiles: 1,
          success:function(file,response){
           response = JSON.parse(response);
           if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
             window.location.assign(response.url);
           }
         },
       }));
     }

     _validate_form($('#expense-form'),{category:'required',date:'required',amount:'required',currency:'required'},expenseSubmitHandler);

     $('input[name="billable"]').on('change',function(){
       do_billable_checkbox();
     });

      $('select[name="clientid"]').on('change',function(){
       customer_init();
       do_billable_checkbox();
       $('input[name="billable"]').trigger('change');
     });
    });
     function customer_init(){
        var customer_id = $('select[name="clientid"]').val();
        var projectAjax = $('select[name="project_id"]');
        var clonedProjectsAjaxSearchSelect = projectAjax.html('').clone();
        var projectsWrapper = $('.projects-wrapper');
        projectAjax.selectpicker('destroy').remove();
        projectAjax = clonedProjectsAjaxSearchSelect;
        $('#project_ajax_search_wrapper').append(clonedProjectsAjaxSearchSelect);
        init_ajax_project_search_by_customer_id();
        if(!customer_id){
           set_base_currency();
           projectsWrapper.addClass('hide');
         }
       $.get(admin_url + 'expenses/get_customer_change_data/'+customer_id,function(response){
         if(customer_id && response.customer_has_projects){
           projectsWrapper.removeClass('hide');
         } else {
           projectsWrapper.addClass('hide');
         }
         client_currency = parseInt(response.client_currency);
         if (client_currency != 0) {
           customer_currency = client_currency;
           do_billable_checkbox();
         } else {
           customer_currency = '';
           set_base_currency();
         }
       },'json');
     }
     function expenseSubmitHandler(form){

      selectCurrency.prop('disabled',false);

      $('select[name="tax2"]').prop('disabled',false);
      $('input[name="billable"]').prop('disabled',false);
      $('input[name="date"]').prop('disabled',false);

      $.post(form.action, $(form).serialize()).done(function(response) {
        response = JSON.parse(response);
        if (response.expenseid) {
         if(typeof(expenseDropzone) !== 'undefined'){
          if (expenseDropzone.getQueuedFiles().length > 0) {
            expenseDropzone.options.url = admin_url + 'expenses/add_expense_attachment/' + response.expenseid;
            expenseDropzone.processQueue();
          } else {
            window.location.assign(response.url);
          }
        } else {
          window.location.assign(response.url);
        }
      } else {
        window.location.assign(response.url);
      }
    });
      return false;
    }
    function do_billable_checkbox(){
      var val = $('select[name="clientid"]').val();
      if(val != ''){
        $('.billable').removeClass('hide');
        if ($('input[name="billable"]').prop('checked') == true) {
          $('.billable_recurring_options').removeClass('hide');
          if(customer_currency != ''){
            selectCurrency.val(customer_currency);
            selectCurrency.selectpicker('refresh');
          } else {
           set_base_currency();
         }
       } else {
        $('.billable_recurring_options').addClass('hide');
        set_base_currency();
      }
    } else {
      set_base_currency();
      $('.billable').addClass('hide');
      $('.billable_recurring_options').addClass('hide');
    }
   }
   function set_base_currency(){
    selectCurrency.val(selectCurrency.data('base'));
    selectCurrency.selectpicker('refresh');
   }
</script>
</body>
</html>
