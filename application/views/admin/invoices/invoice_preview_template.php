<?php if((credits_can_be_applied_to_invoice($invoice->status) && $credits_available > 0)) { ?>
<div class="alert alert-warning mbot5">
   <?php echo _l('x_credits_available',format_money($credits_available,$customer_currency->symbol)); ?>
   <br />
   <a href="#" data-toggle="modal" data-target="#apply_credits"><?php echo _l('apply_credits'); ?></a>
</div>
<?php } ?>
<?php if(count($invoices_to_merge) > 0){ ?>
<div class="panel_s no-padding mbot5">
   <div class="panel-body">
      <h4 class="no-margin"><?php echo _l('invoices_available_for_merging'); ?></h4>
      <hr class="hr-panel-heading hr-10"/>
      <?php foreach($invoices_to_merge as $_inv){ ?>
      <p>
         <a href="<?php echo admin_url('invoices/list_invoices/'.$_inv->id); ?>" target="_blank"><?php echo format_invoice_number($_inv->id); ?></a> - <?php echo format_money($_inv->total,$_inv->symbol); ?>
         <span class="pull-right text-<?php echo get_invoice_status_label($_inv->status); ?>">
         <?php echo format_invoice_status($_inv->status,'',false); ?>
         </span>
      </p>
      <?php } ?>
   </div>
</div>
<?php } ?>
<?php echo form_hidden('_attachment_sale_id',$invoice->id); ?>
<?php echo form_hidden('_attachment_sale_type','invoice'); ?>
<div class="col-md-12 no-padding">
   <div class="panel_s">
      <div class="panel-body">
         <?php if($invoice->recurring > 0){
            echo '<div class="ribbon info"><span>'._l('invoice_recurring_indicator').'</span></div>';
            } ?>
         <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
               <a href="#tab_invoice" aria-controls="tab_invoice" role="tab" data-toggle="tab">
               <?php echo _l('invoice'); ?>
               </a>
            </li>
            <?php if(count($invoice->payments) > 0) { ?>
            <li role="presentation">
               <a href="#invoice_payments_received" aria-controler="invoice_payments_received" role="tab" data-toggle="tab">
               <?php echo _l('payments'); ?> <span class="badge"><?php echo count($invoice->payments); ?></span>
               </a>
            </li>
            <?php } ?>
            <?php if(count($applied_credits) > 0) { ?>
            <li role="presentation">
               <a href="#invoice_applied_credits" aria-controls="invoice_applied_credits" role="tab" data-toggle="tab">
               <?php echo _l('applied_credits'); ?> <span class="badge"><?php echo count($applied_credits); ?></span>
               </a>
            </li>
            <?php } ?>
            <?php if($invoice->recurring > 0) { ?>
            <li role="presentation">
               <a href="#tab_child_invoices" aria-controls="tab_child_invoices" role="tab" data-toggle="tab">
               <?php echo _l('child_invoices'); ?>
               </a>
            </li>
            <?php } ?>
            <li role="presentation">
               <a href="#tab_tasks" onclick="init_rel_tasks_table(<?php echo $invoice->id; ?>,'invoice'); return false;" aria-controls="tab_tasks" role="tab" data-toggle="tab">
               <?php echo _l('tasks'); ?>
               </a>
            </li>
            <li role="presentation">
               <a href="#tab_activity" aria-controls="tab_activity" role="tab" data-toggle="tab">
               <?php echo _l('invoice_view_activity_tooltip'); ?>
               </a>
            </li>
            <li role="presentation">
               <a href="#tab_reminders" onclick="initDataTable('.table-reminders', admin_url + 'misc/get_reminders/' + <?php echo $invoice->id ;?> + '/' + 'invoice', [4], [4],undefined,[1,'ASC']); return false;" aria-controls="tab_reminders" role="tab" data-toggle="tab">
               <?php echo _l('estimate_reminders'); ?>
               <?php
                  $total_reminders = total_rows('tblreminders',
                   array(
                    'isnotified'=>0,
                    'staff'=>get_staff_user_id(),
                    'rel_type'=>'invoice',
                    'rel_id'=>$invoice->id
                  )
                  );
                  if($total_reminders > 0){
                   echo '<span class="badge">'.$total_reminders.'</span>';
                  }
                  ?>
               </a>
            </li>
            <li role="presentation" class="tab-separator">
               <a href="#tab_notes" onclick="get_sales_notes(<?php echo $invoice->id; ?>,'invoices'); return false" aria-controls="tab_notes" role="tab" data-toggle="tab">
               <?php echo _l('estimate_notes'); ?> <span class="notes-total">
               <?php if($totalNotes > 0){ ?>
               <span class="badge"><?php echo $totalNotes; ?><span>
               <?php } ?>
               </span>
               </a>
            </li>
            <li role="presentation" data-toggle="tooltip" title="<?php echo _l('view_tracking'); ?>" class="tab-separator">
               <a href="#tab_views" aria-controls="tab_views" role="tab" data-toggle="tab">
               <i class="fa fa-eye"></i>
               </a>
            </li>
            <li role="presentation" data-toggle="tooltip" data-title="<?php echo _l('toggle_full_view'); ?>" class="tab-separator">
               <a href="#" onclick="small_table_full_view(); return false;" class="toggle_view">
               <i class="fa fa-expand"></i></a>
            </li>
         </ul>
         <div class="row">
            <div class="col-md-3">
               <?php echo format_invoice_status($invoice->status,'mtop5'); ?>
            </div>
            <div class="col-md-9 _buttons">
               <div class="visible-xs">
                  <div class="mtop10"></div>
               </div>
               <div class="pull-right">
                  <?php
                     $_tooltip = _l('invoice_sent_to_email_tooltip');
                     $_tooltip_already_send = '';
                     if($invoice->sent == 1 && is_date($invoice->datesend)){
                      $_tooltip_already_send = _l('invoice_already_send_to_client_tooltip',time_ago($invoice->datesend));
                     }
                     ?>
                  <?php if(has_permission('invoices','','edit')){ ?>
                  <a href="<?php echo admin_url('invoices/invoice/'.$invoice->id); ?>" data-toggle="tooltip" title="<?php echo _l('edit_invoice_tooltip'); ?>" class="btn btn-default btn-with-tooltip" data-placement="bottom"><i class="fa fa-pencil-square-o"></i></a>
                  <?php } ?>
                  <a href="<?php echo admin_url('invoices/pdf/'.$invoice->id.'?print=true'); ?>" target="_blank" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" title="<?php echo _l('print'); ?>" data-placement="bottom"><i class="fa fa-print"></i></a>
                  <a href="<?php echo admin_url('invoices/pdf/'.$invoice->id); ?>" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" title="<?php echo _l('view_pdf'); ?>" data-placement="bottom"><i class="fa fa-file-pdf-o"></i></a>
                  <span<?php if($invoice->status == '5'){ ?> data-toggle="tooltip" data-title="<?php echo _l('invoice_cancelled_email_disabled'); ?>"<?php } ?>>
                  <a href="#" class="invoice-send-to-client btn-with-tooltip btn btn-default<?php if($invoice->status == 5){echo ' disabled';} ?>" data-toggle="tooltip" title="<?php echo $_tooltip; ?>" data-placement="bottom"><span data-toggle="tooltip" data-title="<?php echo $_tooltip_already_send; ?>"><i class="fa fa-envelope"></i></span></a>
                  </span>
                  <!-- Single button -->
                  <div class="btn-group">
                     <button type="button" class="btn btn-default pull-left dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                     <?php echo _l('more'); ?> <span class="caret"></span>
                     </button>
                     <ul class="dropdown-menu dropdown-menu-right">
                        <li><a href="<?php echo site_url('viewinvoice/' . $invoice->id . '/' .  $invoice->hash) ?>" target="_blank"><?php echo _l('view_invoice_as_customer_tooltip'); ?></a></li>
                        <li>
                           <?php if($invoice->status == 4 || ($invoice->status == 3 && $invoice->duedate && date('Y-m-d') > date('Y-m-d',strtotime(to_sql_date($invoice->duedate))))){ ?>
                           <a href="<?php echo admin_url('invoices/send_overdue_notice/'.$invoice->id); ?>"><?php echo _l('send_overdue_notice_tooltip'); ?></a>
                           <?php } ?>
                        </li>
                        <?php if($invoice->status != 5 && has_permission('credit_notes','','create')) {?>
                        <li>
                           <a href="<?php echo admin_url('credit_notes/credit_note_from_invoice/'.$invoice->id); ?>" id="invoice_create_credit_note" data-status="<?php echo $invoice->status; ?>"><?php echo _l('create_credit_note'); ?></a>
                        </li>
                        <?php } ?>
                        <li>
                           <a href="#" data-toggle="modal" data-target="#sales_attach_file"><?php echo _l('invoice_attach_file'); ?></a>
                        </li>
                        <?php if(has_permission('invoices','','create')){ ?>
                        <li>
                           <a href="<?php echo admin_url('invoices/copy/'.$invoice->id); ?>"><?php echo _l('invoice_copy'); ?></a>
                        </li>
                        <?php } ?>
                        <?php if($invoice->sent == 0){ ?>
                        <li>
                           <a href="<?php echo admin_url('invoices/mark_as_sent/'.$invoice->id); ?>"><?php echo _l('invoice_mark_as_sent'); ?></a>
                        </li>
                        <?php } ?>
                        <?php if(has_permission('invoices','','edit') || has_permission('invoices','','create')){ ?>
                        <li>
                           <?php if($invoice->status != 5 && $invoice->status != 2 && $invoice->status != 3){ ?>
                           <a href="<?php echo admin_url('invoices/mark_as_cancelled/'.$invoice->id); ?>"><?php echo _l('invoice_mark_as',_l('invoice_status_cancelled')); ?></a>
                           <?php } else if($invoice->status == 5) { ?>
                           <a href="<?php echo admin_url('invoices/unmark_as_cancelled/'.$invoice->id); ?>"><?php echo _l('invoice_unmark_as',_l('invoice_status_cancelled')); ?></a>
                           <?php } ?>
                        </li>
                        <?php } ?>
                        <?php if(!in_array($invoice->status, array(2,5,6)) && has_permission('invoices','','edit') && get_option('cron_send_invoice_overdue_reminder') == 1 && $invoice->duedate){ ?>
                        <li>
                           <?php if($invoice->cancel_overdue_reminders == 1) { ?>
                           <a href="<?php echo admin_url('invoices/resume_overdue_reminders/'.$invoice->id); ?>"><?php echo _l('resume_overdue_reminders'); ?></a>
                           <?php } else { ?>
                           <a href="<?php echo admin_url('invoices/pause_overdue_reminders/'.$invoice->id); ?>"><?php echo _l('pause_overdue_reminders'); ?></a>
                           <?php } ?>
                        </li>
                        <?php } ?>
                        <?php
                           if((get_option('delete_only_on_last_invoice') == 1 && is_last_invoice($invoice->id)) || (get_option('delete_only_on_last_invoice') == 0)){ ?>
                        <?php if(has_permission('invoices','','delete')){ ?>
                        <li data-toggle="tooltip" data-title="<?php echo _l('delete_invoice_tooltip'); ?>">
                           <a href="<?php echo admin_url('invoices/delete/'.$invoice->id); ?>" class="text-danger delete-text _delete"><?php echo _l('delete_invoice'); ?></a>
                        </li>
                        <?php } ?>
                        <?php } ?>
                     </ul>
                  </div>
                  <?php if(has_permission('payments','','create') && abs($invoice->total) > 0){ ?>
                  <a href="#" onclick="record_payment(<?php echo $invoice->id; ?>); return false;"  class="mleft10 pull-right btn btn-success<?php if($invoice->status == 2 || $invoice->status == 5){echo ' disabled';} ?>"><?php echo _l('invoice_record_payment'); ?></a>
                  <?php } ?>
               </div>
            </div>
         </div>
         <div class="clearfix"></div>
         <hr class="hr-panel-heading" />
         <div class="tab-content">
            <div role="tabpanel" class="tab-pane ptop10 active" id="tab_invoice">
               <?php
                  if($invoice->status == 6){ ?>
               <div class="alert alert-info">
                  <?php echo _l('invoice_draft_status_info'); ?>
               </div>
               <?php } ?>
               <?php if($invoice->status == 3 || $invoice->status == 4){
                  if($invoice->duedate && date('Y-m-d') > date('Y-m-d',strtotime(to_sql_date($invoice->duedate)))){
                   echo '<p class="text-danger no-mbot">'._l('invoice_is_overdue',floor((abs(time() - strtotime(to_sql_date($invoice->duedate))))/(60*60*24))).'</p>';
                  }
                  } ?>
               <div id="invoice-preview">
                  <div class="row">
                     <?php
                        if($invoice->recurring > 0 || $invoice->is_recurring_from != NULL) {
                         $recurring_invoice = $invoice;
                         $next_recurring_date_compare = to_sql_date($recurring_invoice->date);
                         if($recurring_invoice->last_recurring_date){
                           $next_recurring_date_compare = $recurring_invoice->last_recurring_date;
                         }
                         if($invoice->is_recurring_from != NULL){
                           $recurring_invoice = $this->invoices_model->get($invoice->is_recurring_from);
                           $next_recurring_date_compare = $recurring_invoice->last_recurring_date;
                         }
                         if ($recurring_invoice->custom_recurring == 0) {
                           $recurring_invoice->recurring_type = 'MONTH';
                         }
                         $next_date = date('Y-m-d', strtotime('+' . $recurring_invoice->recurring . ' ' . strtoupper($recurring_invoice->recurring_type),strtotime($next_recurring_date_compare)));
                         ?>
                     <div class="col-md-12">
                        <h4 class="font-medium text-success">
                           <?php if(!$recurring_invoice->recurring_ends_on || ($next_date <= $recurring_invoice->recurring_ends_on)){ ?>
                           <?php echo '<i class="fa fa-question-circle" data-toggle="tooltip" data-title="'._l('recurring_recreate_hour_notice',_l('invoice')).'"></i> ' . _l('next_invoice_date',_d($next_date)); ?><br />
                           <?php if($invoice->status == 6){
                              echo '<p class="mtop5 no-mbot"><small class="text-warning ">'._l('recurring_invoice_draft_notice').'</small></p>';
                              } ?>
                           <?php } ?>
                           <?php if($invoice->is_recurring_from != NULL){ ?>
                           <?php echo '<small class="text-muted">'._l('invoice_recurring_from','<a href="'.admin_url('invoices/list_invoices/'.$invoice->is_recurring_from).'" onclick="init_invoice('.$invoice->is_recurring_from.');return false;">'.format_invoice_number($invoice->is_recurring_from).'</small></a>'); ?>
                           <?php } ?>
                        </h4>
                     </div>
                     <?php } ?>
                     <?php if($invoice->project_id != 0){ ?>
                     <div class="col-md-12">
                        <h4 class="font-medium no-mtop mbot20"><?php echo _l('related_to_project',array(
                           _l('invoice_lowercase'),
                           _l('project_lowercase'),
                           '<a href="'.admin_url('projects/view/'.$invoice->project_id).'" target="_blank">' . $invoice->project_data->name . '</a>',
                           )); ?></h4>
                     </div>
                     <?php } ?>
                     <div class="col-md-6">
                        <h4 class="bold">
                           <a href="<?php echo admin_url('invoices/invoice/'.$invoice->id); ?>">
                           <span id="invoice-number">
                           <?php echo format_invoice_number($invoice->id); ?>
                           </span>
                           </a>
                        </h4>
                        <address>
                           <?php echo format_organization_info(); ?>
                        </address>
                     </div>
                     <div class="col-sm-6 text-right">
                        <span class="bold"><?php echo _l('invoice_bill_to'); ?>:</span>
                        <address>
                           <?php echo format_customer_info($invoice, 'invoice', 'billing', true); ?>
                        </address>
                        <?php if($invoice->include_shipping == 1 && $invoice->show_shipping_on_invoice == 1){ ?>
                        <span class="bold"><?php echo _l('ship_to'); ?>:</span>
                        <address>
                           <?php echo format_customer_info($invoice, 'invoice', 'shipping'); ?>
                        </address>
                        <?php } ?>
                        <p class="no-mbot">
                           <span class="bold">
                           <?php echo _l('invoice_data_date'); ?>
                           </span>
                           <?php echo $invoice->date; ?>
                        </p>
                        <?php if(!empty($invoice->duedate)){ ?>
                        <p class="no-mbot">
                           <span class="bold">
                           <?php echo _l('invoice_data_duedate'); ?>
                           </span>
                           <?php echo $invoice->duedate; ?>
                        </p>
                        <?php } ?>
                        <?php if($invoice->sale_agent != 0 && get_option('show_sale_agent_on_invoices') == 1){ ?>
                        <p class="no-mbot">
                           <span class="bold"><?php echo _l('sale_agent_string'); ?>: </span>
                           <?php echo get_staff_full_name($invoice->sale_agent); ?>
                        </p>
                        <?php } ?>
                        <?php if($invoice->project_id != 0 && get_option('show_project_on_invoice') == 1){ ?>
                        <p class="no-mbot">
                           <span class="bold"><?php echo _l('project'); ?>:</span>
                           <?php echo get_project_name_by_id($invoice->project_id); ?>
                        </p>
                        <?php } ?>
                        <?php $pdf_custom_fields = get_custom_fields('invoice',array('show_on_pdf'=>1));
                           foreach($pdf_custom_fields as $field){
                             $value = get_custom_field_value($invoice->id,$field['id'],'invoice');
                             if($value == ''){continue;} ?>
                        <p class="no-mbot">
                           <span class="bold"><?php echo $field['name']; ?>: </span>
                           <?php echo $value; ?>
                        </p>
                        <?php } ?>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-12">
                        <div class="table-responsive">
                           <table class="table items invoice-items-preview">
                              <thead>
                                 <tr>
                                    <th>#</th>
                                    <th class="description" width="50%"><?php echo _l('invoice_table_item_heading'); ?></th>
                                    <?php
                                       $qty_heading = _l('invoice_table_quantity_heading');
                                       if($invoice->show_quantity_as == 2){
                                         $qty_heading = _l('invoice_table_hours_heading');
                                       } else if($invoice->show_quantity_as == 3){
                                         $qty_heading = _l('invoice_table_quantity_heading') .'/'._l('invoice_table_hours_heading');
                                       }
                                       ?>
                                    <th><?php echo $qty_heading; ?></th>
                                    <th><?php echo _l('invoice_table_rate_heading'); ?></th>
                                    <?php if(get_option('show_tax_per_item') == 1){ ?>
                                    <th><?php echo _l('invoice_table_tax_heading'); ?></th>
                                    <?php } ?>
                                    <th><?php echo _l('invoice_table_amount_heading'); ?></th>
                                 </tr>
                              </thead>
                              <tbody>
                                 <?php
                                    $items_data = get_table_items_and_taxes($invoice->items,'invoice',true);
                                    $taxes = $items_data['taxes'];
                                    echo $items_data['html'];
                                    ?>
                              </tbody>
                           </table>
                        </div>
                     </div>
                     <div class="col-md-4 col-md-offset-8">
                        <table class="table text-right">
                           <tbody>
                              <tr id="subtotal">
                                 <td><span class="bold"><?php echo _l('invoice_subtotal'); ?></span>
                                 </td>
                                 <td class="subtotal">
                                    <?php echo format_money($invoice->subtotal,$invoice->symbol); ?>
                                 </td>
                              </tr>
                              <?php if($invoice->discount_percent != 0){ ?>
                              <tr>
                                 <td>
                                    <span class="bold"><?php echo _l('invoice_discount'); ?> (<?php echo _format_number($invoice->discount_percent,true); ?>%)</span>
                                 </td>
                                 <td class="discount">
                                    <?php echo '-' . format_money($invoice->discount_total,$invoice->symbol); ?>
                                 </td>
                              </tr>
                              <?php } ?>
                              <?php foreach($taxes as $tax){
                                 $total = array_sum($tax['total']);
                                 if($invoice->discount_percent != 0 && $invoice->discount_type == 'before_tax'){
                                  $total_tax_calculated = ($total * $invoice->discount_percent) / 100;
                                  $total = ($total - $total_tax_calculated);
                                 }
                                 $_tax_name = explode('|',$tax['tax_name']);
                                 echo '<tr class="tax-area"><td class="bold">'.$_tax_name[0].' ('._format_number($tax['taxrate']).'%)</td><td>'.format_money($total,$invoice->symbol).'</td></tr>';
                                 } ?>
                              <?php if((int)$invoice->adjustment != 0){ ?>
                              <tr>
                                 <td>
                                    <span class="bold"><?php echo _l('invoice_adjustment'); ?></span>
                                 </td>
                                 <td class="adjustment">
                                    <?php echo format_money($invoice->adjustment,$invoice->symbol); ?>
                                 </td>
                              </tr>
                              <?php } ?>
                              <tr>
                                 <td><span class="bold"><?php echo _l('invoice_total'); ?></span>
                                 </td>
                                 <td class="total">
                                    <?php echo format_money($invoice->total,$invoice->symbol); ?>
                                 </td>
                              </tr>
                              <?php if(count($invoice->payments) > 0 && get_option('show_total_paid_on_invoice') == 1) { ?>
                              <tr>
                                 <td><span class="bold"><?php echo _l('invoice_total_paid'); ?></span></td>
                                 <td>
                                    <?php echo '-' . format_money(sum_from_table('tblinvoicepaymentrecords',array('field'=>'amount','where'=>array('invoiceid'=>$invoice->id))),$invoice->symbol); ?>
                                 </td>
                              </tr>
                              <?php } ?>
                              <?php if(get_option('show_credits_applied_on_invoice') == 1 && $credits_applied = total_credits_applied_to_invoice($invoice->id)){ ?>
                              <tr>
                                 <td><span class="bold"><?php echo _l('applied_credits'); ?></span></td>
                                 <td>
                                    <?php echo '-' . format_money($credits_applied,$invoice->symbol); ?>
                                 </td>
                              </tr>
                              <?php } ?>
                              <?php if(get_option('show_amount_due_on_invoice') == 1 && $invoice->status != 5) { ?>
                              <tr>
                                 <td><span class="<?php if($invoice->total_left_to_pay > 0){echo 'text-danger ';} ?>bold"><?php echo _l('invoice_amount_due'); ?></span></td>
                                 <td>
                                    <span class="<?php if($invoice->total_left_to_pay > 0){echo 'text-danger';} ?>">
                                    <?php echo format_money($invoice->total_left_to_pay,$invoice->symbol); ?>
                                    </span>
                                 </td>
                              </tr>
                              <?php } ?>
                           </tbody>
                        </table>
                     </div>
                  </div>
                  <?php if(count($invoice->attachments) > 0){ ?>
                  <div class="clearfix"></div>
                  <hr />
                  <p class="bold text-muted"><?php echo _l('invoice_files'); ?></p>
                  <?php foreach($invoice->attachments as $attachment){
                     $attachment_url = site_url('download/file/sales_attachment/'.$attachment['attachment_key']);
                     if(!empty($attachment['external'])){
                      $attachment_url = $attachment['external_link'];
                     }
                     ?>
                  <div class="mbot15 row inline-block full-width" data-attachment-id="<?php echo $attachment['id']; ?>">
                     <div class="col-md-8">
                        <div class="pull-left"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i></div>
                        <a href="<?php echo $attachment_url; ?>" target="_blank"><?php echo $attachment['file_name']; ?></a>
                        <br />
                        <small class="text-muted"> <?php echo $attachment['filetype']; ?></small>
                     </div>
                     <div class="col-md-4 text-right">
                        <?php if($attachment['visible_to_customer'] == 0){
                           $icon = 'fa-toggle-off';
                           $tooltip = _l('show_to_customer');
                           } else {
                           $icon = 'fa-toggle-on';
                           $tooltip = _l('hide_from_customer');
                           }
                           ?>
                        <a href="#" data-toggle="tooltip" onclick="toggle_file_visibility(<?php echo $attachment['id']; ?>,<?php echo $invoice->id; ?>,this); return false;" data-title="<?php echo $tooltip; ?>"><i class="fa <?php echo $icon; ?>" aria-hidden="true"></i></a>
                        <?php if($attachment['staffid'] == get_staff_user_id() || is_admin()){ ?>
                        <a href="#" class="text-danger" onclick="delete_invoice_attachment(<?php echo $attachment['id']; ?>); return false;"><i class="fa fa-times"></i></a>
                        <?php } ?>
                     </div>
                  </div>
                  <?php } ?>
                  <?php } ?>
                  <hr />
                  <?php if($invoice->clientnote != ''){ ?>
                  <div class="col-md-12 row mtop15">
                     <p class="bold text-muted"><?php echo _l('invoice_note'); ?></p>
                     <p><?php echo $invoice->clientnote; ?></p>
                  </div>
                  <?php } ?>
                  <?php if($invoice->terms != ''){ ?>
                  <div class="col-md-12 row mtop15">
                     <p class="bold text-muted"><?php echo _l('terms_and_conditions'); ?></p>
                     <p><?php echo $invoice->terms; ?></p>
                  </div>
                  <?php } ?>
               </div>
            </div>
            <?php if(count($invoice->payments) > 0) { ?>
            <div class="tab-pane" role="tabpanel" id="invoice_payments_received">
               <?php include_once(APPPATH . 'views/admin/invoices/invoice_payments_table.php'); ?>
            </div>
            <?php } ?>
            <?php if(count($applied_credits) > 0){ ?>
            <div class="tab-pane" role="tabpanel" id="invoice_applied_credits">
               <div class="table-responsive">
                  <table class="table table-bordered table-hover no-mtop">
                     <thead>
                        <th><span class="bold"><?php echo _l('credit_note'); ?> #</span></th>
                        <th><span class="bold"><?php echo _l('credit_date'); ?></span></th>
                        <th><span class="bold"><?php echo _l('credit_amount'); ?></span></th>
                     </thead>
                     <tbody>
                        <?php foreach($applied_credits as $credit) { ?>
                        <tr>
                           <td>
                              <a href="<?php echo admin_url('credit_notes/list_credit_notes/'.$credit['credit_id']); ?>"><?php echo format_credit_note_number($credit['credit_id']); ?></a>
                           </td>
                           <td><?php echo _d($credit['date']); ?></td>
                           <td><?php echo format_money($credit['amount'],$invoice->symbol) ?>
                              <?php if(has_permission('credit_notes','','delete')){ ?>
                              <a href="<?php echo admin_url('credit_notes/delete_invoice_applied_credit/'.$credit['id'].'/'.$credit['credit_id'].'/'.$invoice->id); ?>" class="pull-right text-danger _delete"><i class="fa fa-trash"></i></a>
                              <?php } ?>
                           </td>
                        </tr>
                        <?php } ?>
                     </tbody>
                  </table>
               </div>
            </div>
            <?php } ?>
            <div role="tabpanel" class="tab-pane" id="tab_tasks">
               <?php init_relation_tasks_table(array('data-new-rel-id'=>$invoice->id,'data-new-rel-type'=>'invoice')); ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_reminders">
               <a href="#" class="btn btn-info btn-xs" data-toggle="modal" data-target=".reminder-modal-invoice-<?php echo $invoice->id; ?>"><i class="fa fa-bell-o"></i> <?php echo _l('invoice_set_reminder_title'); ?></a>
               <hr />
               <?php render_datatable(array( _l( 'reminder_description'), _l( 'reminder_date'), _l( 'reminder_staff'), _l( 'reminder_is_notified'), _l( 'options'), ), 'reminders'); ?>
               <?php $this->load->view('admin/includes/modals/reminder',array('id'=>$invoice->id,'name'=>'invoice','members'=>$members,'reminder_title'=>_l('invoice_set_reminder_title'))); ?>
            </div>
            <?php if($invoice->recurring > 0){ ?>
            <div role="tabpanel" class="tab-pane" id="tab_child_invoices">
               <?php if(count($invoice_recurring_invoices)){ ?>
               <p class="mtop30 bold"><?php echo _l('invoice_add_edit_recurring_invoices_from_invoice'); ?></p>
               <br />
               <ul class="list-group">
                  <?php foreach($invoice_recurring_invoices as $recurring){ ?>
                  <li class="list-group-item">
                     <a href="<?php echo admin_url('invoices/list_invoices/'.$recurring->id); ?>" onclick="init_invoice(<?php echo $recurring->id; ?>); return false;" target="_blank"><?php echo format_invoice_number($recurring->id); ?>
                     <span class="pull-right bold"><?php echo format_money($recurring->total,$recurring->symbol); ?></span>
                     </a>
                     <br />
                     <span class="inline-block mtop10">
                     <?php echo '<span class="bold">'._d($recurring->date).'</span>'; ?><br />
                     <?php echo format_invoice_status($recurring->status,'',false); ?>
                     </span>
                  </li>
                  <?php } ?>
               </ul>
               <?php } else { ?>
               <p class="bold"><?php echo _l('no_child_found',_l('invoices')); ?></p>
               <?php } ?>
            </div>
            <?php } ?>
            <div role="tabpanel" class="tab-pane" id="tab_notes">
               <?php echo form_open(admin_url('invoices/add_note/'.$invoice->id),array('id'=>'sales-notes','class'=>'invoice-notes-form')); ?>
               <?php echo render_textarea('description'); ?>
               <div class="text-right">
                  <button type="submit" class="btn btn-info mtop15 mbot15"><?php echo _l('estimate_add_note'); ?></button>
               </div>
               <?php echo form_close(); ?>
               <hr />
               <div class="panel_s mtop20 no-shadow" id="sales_notes_area"></div>
            </div>
            <div role="tabpanel" class="tab-pane ptop10" id="tab_activity">
               <div class="row">
                  <div class="col-md-12">
                     <div class="activity-feed">
                        <?php foreach($activity as $activity){
                           $_custom_data = false;
                           ?>
                        <div class="feed-item" data-sale-activity-id="<?php echo $activity['id']; ?>">
                           <div class="date">
                              <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($activity['date']); ?>">
                                 <?php echo time_ago($activity['date']); ?>
                              </span>
                              </div>
                           <div class="text">
                              <?php if(is_numeric($activity['staffid']) && $activity['staffid'] != 0){ ?>
                              <a href="<?php echo admin_url('profile/'.$activity["staffid"]); ?>">
                              <?php echo staff_profile_image($activity['staffid'],array('staff-profile-xs-image pull-left mright5'));
                                 ?>
                              </a>
                              <?php } ?>
                              <?php
                                 $additional_data = '';
                                 if(!empty($activity['additional_data'])){
                                  $additional_data = unserialize($activity['additional_data']);
                                  $i = 0;
                                  foreach($additional_data as $data){
                                    if(strpos($data,'<original_status>') !== false){
                                      $original_status = get_string_between($data, '<original_status>', '</original_status>');
                                      $additional_data[$i] = format_invoice_status($original_status,'',false);
                                    } else if(strpos($data,'<new_status>') !== false){
                                      $new_status = get_string_between($data, '<new_status>', '</new_status>');
                                      $additional_data[$i] = format_invoice_status($new_status,'',false);
                                    } else if(strpos($data,'<custom_data>') !== false){
                                      $_custom_data = get_string_between($data, '<custom_data>', '</custom_data>');
                                      unset($additional_data[$i]);
                                    }
                                    $i++;
                                  }
                                 }
                                 $_formatted_activity = _l($activity['description'],$additional_data);
                                 if($_custom_data !== false){
                                  $_formatted_activity .= ' - ' .$_custom_data;
                                 }
                                 if(!empty($activity['full_name'])){
                                 $_formatted_activity = $activity['full_name'] . ' - ' . $_formatted_activity;
                                 }
                                 echo $_formatted_activity;
                                 if(is_admin()){
                                 echo '<a href="#" class="pull-right text-danger" onclick="delete_sale_activity('.$activity['id'].'); return false;"><i class="fa fa-remove"></i></a>';
                                 }
                                 ?>
                           </div>
                        </div>
                        <?php } ?>
                     </div>
                  </div>
               </div>
            </div>
            <div role="tabpanel" class="tab-pane ptop10" id="tab_views">
               <?php
                  $views_activity = get_views_tracking('invoice',$invoice->id);
                  foreach($views_activity as $activity){ ?>
               <p class="text-success no-margin">
                  <?php echo _l('view_date') . ': ' . _dt($activity['date']); ?>
               </p>
               <p class="text-muted">
                  <?php echo _l('view_ip') . ': ' . $activity['view_ip']; ?>
               </p>
               <hr />
               <?php } ?>
            </div>
         </div>
      </div>
   </div>
</div>
<?php $this->load->view('admin/invoices/invoice_send_to_client'); ?>
<?php $this->load->view('admin/credit_notes/apply_invoice_credits'); ?>
<?php $this->load->view('admin/credit_notes/invoice_create_credit_note_confirm'); ?>
<script>
   init_items_sortable(true);
   init_btn_with_tooltips();
   init_datepicker();
   init_selectpicker();
   init_form_reminder();
</script>
