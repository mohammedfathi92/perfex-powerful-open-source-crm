<div class="col-md-12 page-pdf-html-logo">
    <?php get_company_logo('','pull-left'); ?>
    <?php if(is_staff_logged_in()){ ?>
    <a href="<?php echo admin_url(); ?>invoices/list_invoices/<?php echo $invoice->id; ?>" class="btn btn-info pull-right"><?php echo _l('goto_admin_area'); ?></a>
    <?php } else if(is_client_logged_in() && has_contact_permission('invoices')){ ?>
    <a href="<?php echo site_url('clients/invoices/'); ?>" class="btn btn-info pull-right"><?php echo _l('client_go_to_dashboard'); ?></a>
    <?php } ?>
</div>
<div class="clearfix"></div>
<div class="panel_s mtop20">
    <div class="panel-body">
        <div class="col-md-10 col-md-offset-1">
            <div class="row">
                <div class="col-md-6">
                    <div class="mtop10 display-block">
                        <?php echo format_invoice_status($invoice->status,'',true); ?>
                    </div>
                </div>
                <div class="col-md-6 text-right _buttons">
                    <div class="visible-xs">
                        <div class="mtop10"></div>
                    </div>
                    <a href="#" style="display:none;" class="btn btn-success pull-right mleft5<?php if (($invoice->status != 2 && $invoice->status != 5 && $invoice->total > 0) && found_invoice_mode($payment_modes,$invoice->id,false)){ echo ' pay-now-top'; } ?>"><?php echo _l('invoice_html_online_payment_button_text'); ?></a>
                    <?php echo form_open($this->uri->uri_string()); ?>
                    <button type="submit" name="invoicepdf" value="invoicepdf" class="btn btn-info"><i class='fa fa-file-pdf-o'></i> <?php echo _l('clients_invoice_html_btn_download'); ?></button>
                    <?php echo form_close(); ?>
                </div>
            </div>
            <div class="row mtop40">
                <div class="col-md-6">
                    <h4 class="bold"><?php echo format_invoice_number($invoice->id); ?></h4>
                    <address>
                        <?php echo format_organization_info(); ?>
                    </address>
                </div>
                <div class="col-sm-6 text-right">
                    <span class="bold"><?php echo _l('invoice_bill_to'); ?>:</span>
                    <address>
                        <?php echo format_customer_info($invoice, 'invoice', 'billing'); ?>
                    </address>
                    <!-- shipping details -->
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
                    <?php echo _d($invoice->date); ?>
                </p>
                <?php if(!empty($invoice->duedate)){ ?>
                <p class="no-mbot">
                    <span class="bold"><?php echo _l('invoice_data_duedate'); ?></span>
                    <?php echo _d($invoice->duedate); ?>
                </p>
                <?php } ?>
                <?php if($invoice->sale_agent != 0 && get_option('show_sale_agent_on_invoices') == 1){ ?>
                <p class="no-mbot">
                    <span class="bold"><?php echo _l('sale_agent_string'); ?>:</span>
                    <?php echo get_staff_full_name($invoice->sale_agent); ?>
                </p>
                <?php } ?>
                <?php if($invoice->project_id != 0 && get_option('show_project_on_invoice') == 1){ ?>
                <p class="no-mbot">
                    <span class="bold"><?php echo _l('project'); ?>:</span>
                    <?php echo get_project_name_by_id($invoice->project_id); ?>
                </p>
                <?php } ?>
                <?php $pdf_custom_fields = get_custom_fields('invoice',array('show_on_pdf'=>1,'show_on_client_portal'=>1));
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
                        <table class="table items">
                            <thead>
                                <tr>
                                    <th align="center">#</th>
                                    <th class="description" width="50%" align="left"><?php echo _l('invoice_table_item_heading'); ?></th>
                                    <?php
                                       $custom_fields = get_items_custom_fields_for_table_html($invoice->id,'invoice');
                                       foreach($custom_fields as $cf){
                                         echo '<th class="custom_field" align="left">' . $cf['name'] . '</th>';
                                       }
                                    ?>
                                    <?php
                                        $qty_heading = _l('invoice_table_quantity_heading');
                                        if($invoice->show_quantity_as == 2){
                                            $qty_heading = _l('invoice_table_hours_heading');
                                        } else if($invoice->show_quantity_as == 3){
                                            $qty_heading = _l('invoice_table_quantity_heading') .'/'._l('invoice_table_hours_heading');
                                        }
                                    ?>
                                    <th align="right"><?php echo $qty_heading; ?></th>
                                    <th align="right"><?php echo _l('invoice_table_rate_heading'); ?></th>
                                    <?php if(get_option('show_tax_per_item') == 1){ ?>
                                    <th align="right"><?php echo _l('invoice_table_tax_heading'); ?></th>
                                    <?php } ?>
                                    <th align="right"><?php echo _l('invoice_table_amount_heading'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                               <?php
                                   $items_data = get_table_items_and_taxes($invoice->items,'invoice');
                                   $taxes = $items_data['taxes'];
                                   echo $items_data['html'];
                               ?>
                           </tbody>
                       </table>
                   </div>
               </div>
               <div class="col-md-6 col-md-offset-6">
                <table class="table text-right">
                    <tbody>
                        <tr id="subtotal">
                            <td><span class="bold"><?php echo _l('invoice_subtotal'); ?></span>
                            </td>
                            <td class="subtotal">
                                <?php echo format_money($invoice->subtotal,$invoice->symbol); ?>
                            </td>
                        </tr>
                         <?php if(is_sale_discount_applied($invoice)){ ?>
                         <tr>
                               <td>
                                <span class="bold"><?php echo _l('invoice_discount'); ?>
                                 <?php if(is_sale_discount($invoice,'percent')){ ?>
                                 (<?php echo _format_number($invoice->discount_percent,true); ?>%)
                                 <?php } ?></span>
                             </td>
                             <td class="discount">
                                <?php echo '-' . format_money($invoice->discount_total,$invoice->symbol); ?>
                            </td>
                        </tr>
                        <?php } ?>
                        <?php
                        foreach($taxes as $tax){
                            echo '<tr class="tax-area"><td class="bold">'.$tax['taxname'].' ('._format_number($tax['taxrate']).'%)</td><td>'.format_money($tax['total_tax'], $invoice->symbol).'</td></tr>';
                        }
                        ?>
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
                        <?php if(count($invoice->payments) > 0 && get_option('show_total_paid_on_invoice') == 1){ ?>
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
            <?php if(get_option('total_to_words_enabled') == 1){ ?>
            <div class="col-md-12 text-center">
                <p class="bold no-margin"><?php echo  _l('num_word').': '.$this->numberword->convert($invoice->total,$invoice->currency_name); ?></p>
            </div>
            <?php } ?>
            <?php if(count($invoice->attachments) > 0 && $invoice->visible_attachments_to_customer_found == true){ ?>
            <div class="clearfix"></div>
            <div class="col-md-12">
                <hr />
                <p class="bold mbot15 font-medium"><?php echo _l('invoice_files'); ?></p>
            </div>
            <?php foreach($invoice->attachments as $attachment){
                    // Do not show hidden attachments to customer
                if($attachment['visible_to_customer'] == 0){continue;}
                $attachment_url = site_url('download/file/sales_attachment/'.$attachment['attachment_key']);
                if(!empty($attachment['external'])){
                    $attachment_url = $attachment['external_link'];
                }
                ?>
                <div class="col-md-12 mbot10">
                    <div class="pull-left"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i></div>
                    <a href="<?php echo $attachment_url; ?>"><?php echo $attachment['file_name']; ?></a>
                </div>
                <?php } ?>
                <?php } ?>
                <?php if(!empty($invoice->clientnote)){ ?>
                <div class="col-md-12">
                    <b><?php echo _l('invoice_note'); ?></b><br /><br /><?php echo $invoice->clientnote; ?>
                </div>
                <?php } ?>
                <?php if(!empty($invoice->terms)){ ?>
                <div class="col-md-12">
                    <hr />
                    <b><?php echo _l('terms_and_conditions'); ?></b><br /><br /><?php echo $invoice->terms; ?>
                </div>
                <?php } ?>
                <div class="col-md-12">
                  <hr />
              </div>
              <div class="col-md-12">
                <?php
                $total_payments = count($invoice->payments);
                if($total_payments > 0){ ?>
                <p class="bold mbot15 font-medium"><?php echo _l('invoice_received_payments'); ?></p>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><?php echo _l('invoice_payments_table_number_heading'); ?></th>
                            <th><?php echo _l('invoice_payments_table_mode_heading'); ?></th>
                            <th><?php echo _l('invoice_payments_table_date_heading'); ?></th>
                            <th><?php echo _l('invoice_payments_table_amount_heading'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($invoice->payments as $payment){ ?>
                        <tr>
                            <td>
                                <span class="pull-left"><?php echo $payment['paymentid']; ?></span>
                                <?php echo form_open($this->uri->uri_string()); ?>
                                <button type="submit" value="<?php echo $payment['paymentid']; ?>" class="btn btn-icon btn-default pull-right" name="paymentpdf"><i class="fa fa-file-pdf-o"></i></button>
                                <?php echo form_close(); ?>
                            </td>
                            <td><?php echo $payment['name']; ?> <?php if(!empty($payment['paymentmethod'])){echo ' - '.$payment['paymentmethod']; } ?></td>
                            <td><?php echo _d($payment['date']); ?></td>
                            <td><?php echo format_money($payment['amount'],$invoice->symbol); ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <hr />
                <?php } else { ?>
                <h5 class="bold pull-left"><?php echo _l('invoice_no_payments_found'); ?></h5>
                <div class="clearfix"></div>
                <hr />
                <?php } ?>
            </div>
            <?php
                    // No payments for paid and cancelled
            if (($invoice->status != 2 && $invoice->status != 5 && $invoice->total > 0)){ ?>
            <div class="col-md-12">
                <div class="row">
                    <?php
                    $found_online_mode = false;
                    if(found_invoice_mode($payment_modes,$invoice->id,false)) {
                        $found_online_mode = true;
                        ?>
                        <div class="col-md-6 text-left">
                            <p class="bold mbot15 font-medium"><?php echo _l('invoice_html_online_payment'); ?></p>
                            <?php echo form_open($this->uri->uri_string(),array('id'=>'online_payment_form','novalidate'=>true)); ?>
                            <?php foreach($payment_modes as $mode){
                                if(!is_numeric($mode['id']) && !empty($mode['id'])) {
                                    if(!is_payment_mode_allowed_for_invoice($mode['id'],$invoice->id)){
                                        continue;
                                    }
                                    ?>
                                    <div class="radio radio-success online-payment-radio">
                                        <input type="radio" value="<?php echo $mode['id']; ?>" id="pm_<?php echo $mode['id']; ?>" name="paymentmode">
                                        <label for="pm_<?php echo $mode['id']; ?>"><?php echo $mode['name']; ?></label>
                                    </div>
                                    <?php if(!empty($mode['description'])){ ?>
                                    <div class="mbot15">
                                        <?php echo $mode['description']; ?>
                                    </div>
                                    <?php }
                                }
                            } ?>
                            <div class="form-group mtop25">
                                <?php if(get_option('allow_payment_amount_to_be_modified') == 1){ ?>
                                <label for="amount" class="control-label"><?php echo _l('invoice_html_amount'); ?></label>
                                <div class="input-group">
                                    <input type="number" required max="<?php echo $invoice->total_left_to_pay; ?>" data-total="<?php echo $invoice->total_left_to_pay; ?>" name="amount" class="form-control" value="<?php echo $invoice->total_left_to_pay; ?>">
                                    <span class="input-group-addon">
                                       <?php echo $invoice->symbol; ?>
                                   </span>
                               </div>
                               <?php } else {
                                echo '<span class="bold">' . _l('invoice_html_total_pay',format_money($invoice->total_left_to_pay,$invoice->symbol)) . '</span>';
                            } ?>
                        </div>
                        <input type="submit" name="make_payment" class="btn btn-success" value="<?php echo _l('invoice_html_online_payment_button_text'); ?>">
                        <input type="hidden" name="hash" value="<?php echo $hash; ?>">
                        <?php echo form_close(); ?>
                    </div>
                    <?php } ?>
                    <?php if(found_invoice_mode($payment_modes,$invoice->id)) { ?>
                    <div class="<?php if($found_online_mode == true){echo 'col-md-6 text-right';}else{echo 'col-md-12';};?>">
                        <p class="bold mbot15 font-medium"><?php echo _l('invoice_html_offline_payment'); ?></p>
                        <?php foreach($payment_modes as $mode){
                            if(is_numeric($mode['id'])) {
                                if(!is_payment_mode_allowed_for_invoice($mode['id'],$invoice->id)){
                                    continue;
                                }
                                ?>
                                <p class="bold"><?php echo $mode['name']; ?></p>
                                <?php if(!empty($mode['description'])){ ?>
                                <div class="mbot15">
                                    <?php echo $mode['description']; ?>
                                </div>
                                <?php }
                            }
                        } ?>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
</div>
<script>
    $(function(){

        var pay_now_top = $('.pay-now-top');
        if(pay_now_top.length) {
            if ($(document).height() > $(window).height() + 40) {
                pay_now_top.css('display','block');
            }
            $('.pay-now-top').on('click',function(e){
                e.preventDefault();
                $('html,body').animate({
                    scrollTop: $("#online_payment_form").offset().top},
                    'slow');
            });
        }

        $('#online_payment_form').validate();

        var online_payments = $('.online-payment-radio');
        if(online_payments.length == 1){
            online_payments.find('input').prop('checked',true);
        }

    });
</script>
