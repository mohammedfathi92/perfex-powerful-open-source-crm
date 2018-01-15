<?php echo form_hidden('_attachment_sale_id',$credit_note->id); ?>
<?php echo form_hidden('_attachment_sale_type','credit_note'); ?>
<div class="col-md-12 no-padding">
   <div class="panel_s">
      <div class="panel-body">
         <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
               <a href="#tab_credit_note" aria-controls="tab_credit_note" role="tab" data-toggle="tab">
               <?php echo _l('credit_note'); ?>
               </a>
            </li>
            <li role="presentation">
               <a href="#invoices_credited" aria-controls="invoices_credited" role="tab" data-toggle="tab">
               <?php echo _l('invoices_credited'); ?>
               <?php if(count($credit_note->applied_credits) > 0) {
                  echo '<span class="badge">'.count($credit_note->applied_credits).'</span>';
                  }
                  ?>
               </a>
            </li>
            <li role="presentation" class="tab-separator">
               <a href="#tab_reminders" onclick="initDataTable('.table-reminders', admin_url + 'misc/get_reminders/' + <?php echo $credit_note->id ;?> + '/' + 'credit_note', [4], [4],undefined,[1,'ASC']); return false;" aria-controls="tab_reminders" role="tab" data-toggle="tab">
               <?php echo _l('reminders'); ?>
               <?php
                  $total_reminders = total_rows('tblreminders',
                   array(
                     'isnotified'=>0,
                     'staff'=>get_staff_user_id(),
                     'rel_type'=>'credit_note',
                     'rel_id'=>$credit_note->id
                   )
                  );
                  if($total_reminders > 0){
                   echo '<span class="badge">'.$total_reminders.'</span>';
                  }
                  ?>
               </a>
            </li>
            <li role="presentation" class="tab-separator">
               <a href="#" onclick="small_table_full_view(); return false;" data-placement="left" data-toggle="tooltip" data-title="<?php echo _l('toggle_full_view'); ?>" class="toggle_view">
               <i class="fa fa-expand"></i></a>
            </li>
         </ul>
         <div class="row">
            <div class="col-md-3">
               <?php echo format_credit_note_status($credit_note->status);  ?>
            </div>
            <div class="col-md-9">
               <div class="visible-xs">
                  <div class="mtop10"></div>
               </div>
               <div class="pull-right _buttons">
                  <?php if(has_permission('credit_notes','','edit') && $credit_note->status != 3){ ?>
                  <a href="<?php echo admin_url('credit_notes/credit_note/'.$credit_note->id); ?>" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" title="<?php echo _l('edit',_l('credit_note_lowercase')); ?>" data-placement="bottom">
                  <i class="fa fa-pencil-square-o"></i>
                  </a>
                  <?php } ?>
                  <a href="<?php echo admin_url('credit_notes/pdf/'.$credit_note->id.'?print=true'); ?>" target="_blank" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" title="<?php echo _l('print'); ?>" data-placement="bottom">
                  <i class="fa fa-print"></i>
                  </a>
                  <a href="<?php echo admin_url('credit_notes/pdf/'.$credit_note->id); ?>" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" title="<?php echo _l('view_pdf'); ?>" data-placement="bottom">
                  <i class="fa fa-file-pdf-o"></i>
                  </a>
                  <?php if($credit_note->status != 3) { ?>
                  <a href="#" class="credit-note-send-to-client btn btn-default" data-toggle="modal" data-target="#credit_note_send_to_client_modal">
                  <i class="fa fa-envelope"></i>
                  </a>
                  <?php } ?>
                  <?php if($credit_note->status == 1){ ?>
                  <a href="#" data-toggle="modal" data-target="#apply_credits" class="btn btn-info">
                  <?php echo _l('apply_to_invoice'); ?>
                  </a>
                  <?php } ?>
                  <?php if(($credit_note->status != 2 && $credit_note->status != 3 && !$credit_note->credits_used && has_permission('credit_notes','','edit'))
                     || ($credit_note->status == 3 && has_permission('credit_notes','','edit'))
                     || has_permission('credit_notes','','delete')
                     ) { ?>
                  <div class="btn-group">
                     <button type="button" class="btn btn-default pull-left dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                     <?php echo _l('more'); ?> <span class="caret"></span>
                     </button>
                     <ul class="dropdown-menu dropdown-menu-right">
                        <?php
                           // is not closed and is not void
                           if($credit_note->status != 2 && $credit_note->status != 3 && !$credit_note->credits_used && has_permission('credit_notes','','edit')) { ?>
                        <li>
                           <a href="<?php echo admin_url('credit_notes/mark_void/'.$credit_note->id); ?>">
                           <?php echo _l('credit_note_status_void'); ?>
                           </a>
                        </li>
                        <?php } else if($credit_note->status == 3 && has_permission('credit_notes','','edit')) { ?>
                        <li>
                           <a href="<?php echo admin_url('credit_notes/mark_open/'.$credit_note->id); ?>">
                           <?php echo _l('credit_note_mark_as_open'); ?>
                           </a>
                        </li>
                        <?php } ?>
                        <li>
                           <a href="#" data-toggle="modal" data-target="#sales_attach_file"><?php echo _l('invoice_attach_file'); ?></a>
                        </li>
                        <?php
                           if(has_permission('credit_notes','','delete')){
                             $delete_tooltip = '';
                             if($credit_note->status == 2) {
                              $delete_tooltip = _l('credits_applied_cant_delete_status_closed');
                            } else if($credit_note->credits_used) {
                              $delete_tooltip = _l('credits_applied_cant_delete_credit_note');
                            }
                            ?>
                        <li>
                           <a data-toggle="tooltip" data-title="<?php echo $delete_tooltip; ?>" href="<?php echo admin_url('credit_notes/delete/'.$credit_note->id); ?>" class="text-danger delete-text <?php if(!$credit_note->credits_used && $credit_note->status != 2){ echo ' _delete'; }?>"<?php if($credit_note->credits_used || $credit_note->status == 2){ echo ' style="cursor:not-allowed;" onclick="return false;" ';}; ?>><?php echo _l('delete'); ?></a>
                        </li>
                        <?php } ?>
                     </ul>
                  </div>
                  <?php } ?>
               </div>
            </div>
         </div>
         <div class="clearfix"></div>
         <hr class="hr-panel-heading" />
         <div class="tab-content">
            <div role="tabpanel" class="tab-pane ptop10 active" id="tab_credit_note">
               <div id="credit-note-preview">
                  <div class="row">
                     <div class="col-md-6">
                        <h4 class="bold">
                           <a href="<?php echo admin_url('credit_notes/credit_note/'.$credit_note->id); ?>">
                           <span id="credit-note-number">
                           <?php echo format_credit_note_number($credit_note->id); ?>
                           </span>
                           </a>
                        </h4>
                        <address>
                           <?php echo format_organization_info(); ?>
                        </address>
                     </div>
                     <div class="col-sm-6 text-right">
                        <span class="bold"><?php echo _l('credit_note_bill_to'); ?>:</span>
                        <address>
                           <?php echo format_customer_info($credit_note, 'credit_note', 'billing', true); ?>
                        </address>
                        <?php if($credit_note->include_shipping == 1 && $credit_note->show_shipping_on_credit_note == 1){ ?>
                        <span class="bold"><?php echo _l('ship_to'); ?>:</span>
                        <address>
                           <?php echo format_customer_info($credit_note, 'credit_note', 'shipping'); ?>
                        </address>
                        <?php } ?>
                        <p class="no-mbot">
                           <span class="bold">
                           <?php echo _l('credit_note_date'); ?>:
                           </span>
                           <?php echo _d($credit_note->date)?>
                        </p>
                        <?php if(!empty($credit_note->reference_no)){ ?>
                        <p class="no-mbot">
                           <span class="bold"><?php echo _l('reference_no'); ?>:</span>
                           <?php echo $credit_note->reference_no; ?>
                        </p>
                        <?php } ?>
                        <?php if($credit_note->project_id != 0 && get_option('show_project_on_credit_note') == '1'){ ?>
                        <p class="no-mbot">
                           <span class="bold"><?php echo _l('project'); ?>:</span>
                           <?php echo get_project_name_by_id($credit_note->project_id); ?>
                        </p>
                        <?php } ?>
                        <?php $pdf_custom_fields = get_custom_fields('credit_note',array('show_on_pdf'=>1));
                        foreach($pdf_custom_fields as $field){
                           $value = get_custom_field_value($credit_note->id,$field['id'],'credit_note');
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
                           <table class="table items credit-note-items-preview">
                              <thead>
                                 <tr>
                                    <th>#</th>
                                    <th class="description" width="50%"><?php echo _l('credit_note_table_item_heading'); ?></th>
                                    <?php
                                       $qty_heading = _l('credit_note_table_quantity_heading');
                                       if($credit_note->show_quantity_as == 2){
                                        $qty_heading = _l('credit_note_table_hours_heading');
                                       } else if($credit_note->show_quantity_as == 3){
                                        $qty_heading = _l('credit_note_table_quantity_heading') .'/'._l('credit_note_table_hours_heading');
                                       }
                                       ?>
                                    <th><?php echo $qty_heading; ?></th>
                                    <th><?php echo _l('credit_note_table_rate_heading'); ?></th>
                                    <?php if(get_option('show_tax_per_item') == 1){ ?>
                                    <th><?php echo _l('credit_note_table_tax_heading'); ?></th>
                                    <?php } ?>
                                    <th><?php echo _l('credit_note_table_amount_heading'); ?></th>
                                 </tr>
                              </thead>
                              <tbody>
                                 <?php
                                    $items_data = get_table_items_and_taxes($credit_note->items,'credit_note',true);
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
                                 <td><span class="bold"><?php echo _l('credit_note_subtotal'); ?></span>
                                 </td>
                                 <td class="subtotal">
                                    <?php echo format_money($credit_note->subtotal,$credit_note->symbol); ?>
                                 </td>
                              </tr>
                              <?php if($credit_note->discount_percent != 0){ ?>
                              <tr>
                                 <td>
                                    <span class="bold"><?php echo _l('credit_note_discount'); ?> (<?php echo _format_number($credit_note->discount_percent,true); ?>%)</span>
                                 </td>
                                 <td class="discount">
                                    <?php echo '-' . format_money($credit_note->discount_total,$credit_note->symbol); ?>
                                 </td>
                              </tr>
                              <?php } ?>
                              <?php
                                 foreach($taxes as $tax){
                                   $total = array_sum($tax['total']);
                                   if($credit_note->discount_percent != 0 && $credit_note->discount_type == 'before_tax'){
                                    $total_tax_calculated = ($total * $credit_note->discount_percent) / 100;
                                    $total = ($total - $total_tax_calculated);
                                  }
                                  $_tax_name = explode('|',$tax['tax_name']);
                                  echo '<tr class="tax-area"><td class="bold">'.$_tax_name[0].' ('._format_number($tax['taxrate']).'%)</td><td>'.format_money($total,$credit_note->symbol).'</td></tr>';
                                 }
                                 ?>
                              <?php if((int)$credit_note->adjustment != 0){ ?>
                              <tr>
                                 <td>
                                    <span class="bold"><?php echo _l('credit_note_adjustment'); ?></span>
                                 </td>
                                 <td class="adjustment">
                                    <?php echo format_money($credit_note->adjustment,$credit_note->symbol); ?>
                                 </td>
                              </tr>
                              <?php } ?>
                              <tr>
                                 <td><span class="bold"><?php echo _l('credit_note_total'); ?></span>
                                 </td>
                                 <td class="total">
                                    <?php echo format_money($credit_note->total,$credit_note->symbol); ?>
                                 </td>
                              </tr>
                              <?php if($credit_note->credits_used){ ?>
                              <tr>
                                 <td>
                                    <span class="bold">
                                    <?php echo _l('credits_used'); ?>
                                    </span>
                                 </td>
                                 <td>
                                    <?php echo '-' . format_money($credit_note->credits_used,$credit_note->symbol); ?>
                                 </td>
                              </tr>
                              <?php } ?>
                              <tr>
                                 <td>
                                    <span class="bold">
                                    <?php echo _l('credits_remaining'); ?>
                                    </span>
                                 </td>
                                 <td>
                                    <?php echo format_money($credit_note->remaining_credits,$credit_note->symbol); ?>
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                     <?php if($credit_note->clientnote != ''){ ?>
                     <div class="col-md-12 mtop15">
                        <p class="bold text-muted"><?php echo _l('credit_note_client_note'); ?></p>
                        <p><?php echo $credit_note->clientnote; ?></p>
                     </div>
                     <?php } ?>
                     <?php if($credit_note->terms != ''){ ?>
                     <div class="col-md-12 mtop15">
                        <p class="bold text-muted"><?php echo _l('terms_and_conditions'); ?></p>
                        <p><?php echo $credit_note->terms; ?></p>
                     </div>
                     <?php } ?>
                  </div>
                  <?php
                     if(count($credit_note->attachments) > 0){ ?>
                  <div class="clearfix"></div>
                  <hr />
                  <p class="bold text-muted"><?php echo _l('credit_note_files'); ?></p>
                  <?php foreach($credit_note->attachments as $attachment){
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
                        <?php if($attachment['staffid'] == get_staff_user_id() || is_admin()){ ?>
                        <a href="#" class="text-danger" onclick="delete_credit_note_attachment(<?php echo $attachment['id']; ?>); return false;"><i class="fa fa-times"></i></a>
                        <?php } ?>
                     </div>
                  </div>
                  <?php } ?>
                  <?php } ?>
               </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="invoices_credited">
               <?php if(count($credit_note->applied_credits) == 0) {
                  echo '<div class="alert alert-info no-mbot">';
                  echo _l('credited_invoices_not_found');
                  echo '</div>';
                  } else { ?>
               <table class="table table-bordered no-mtop">
                  <thead>
                     <tr>
                        <th><span class="bold"><?php echo _l('credit_invoice_number'); ?></span></th>
                        <th><span class="bold"><?php echo _l('amount_credited'); ?></span></th>
                        <th><span class="bold"><?php echo _l('credit_date'); ?></span></th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php foreach($credit_note->applied_credits as $credit) { ?>
                     <tr>
                        <td>
                           <a href="<?php echo admin_url('invoices/list_invoices/'.$credit['invoice_id']); ?>"><?php echo format_invoice_number($credit['invoice_id']); ?></a>
                        </td>
                        <td>
                           <?php echo format_money($credit['amount'],$credit_note->symbol); ?>
                        </td>
                        <td>
                           <?php echo _d($credit['date']); ?>
                           <?php if(has_permission('credit_notes','','delete')) { ?>
                           <a href="<?php echo admin_url('credit_notes/delete_credit_note_applied_credit/'.$credit['id'].'/'.$credit['credit_id'].'/'.$credit['invoice_id']); ?>" class="pull-right text-danger _delete"><i class="fa fa-trash"></i></a>
                           <?php } ?>
                        </td>
                     </tr>
                     <?php } ?>
                  </tbody>
               </table>
               <?php }  ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_reminders">
               <a href="#" class="btn btn-info btn-xs" data-toggle="modal" data-target=".reminder-modal-credit_note-<?php echo $credit_note->id; ?>"><i class="fa fa-bell-o"></i> <?php echo _l('credit_note_set_reminder_title'); ?></a>
               <hr />
               <?php render_datatable(array( _l( 'reminder_description'), _l( 'reminder_date'), _l( 'reminder_staff'), _l( 'reminder_is_notified'), _l( 'options'), ), 'reminders'); ?>
               <?php $this->load->view('admin/includes/modals/reminder',array('id'=>$credit_note->id,'name'=>'credit_note','members'=>$members,'reminder_title'=>_l('credit_note_set_reminder_title'))); ?>
            </div>
         </div>
      </div>
   </div>
</div>
</div>
<?php $this->load->view('admin/credit_notes/send_to_client'); ?>
<?php $this->load->view('admin/credit_notes/apply_credits_to_invoices'); ?>
<script>
   init_items_sortable(true);
   init_btn_with_tooltips();
   init_datepicker();
   init_selectpicker();
   init_form_reminder();
</script>
