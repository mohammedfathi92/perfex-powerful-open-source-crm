<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-5">
				<div class="panel_s">
					<div class="col-md-12 no-padding">
						<div class="panel_s">
							<?php echo form_open($this->uri->uri_string()); ?>
							<div class="panel-body">
								<h4 class="no-margin"><?php echo _l('payment_edit_for_invoice'); ?> <a href="<?php echo admin_url('invoices/list_invoices/'.$payment->invoiceid); ?>"><?php echo format_invoice_number($invoice->id); ?></a></h4>
								<hr class="hr-panel-heading" />
								<?php echo render_input('amount','payment_edit_amount_received',$payment->amount,'number'); ?>
								<?php echo render_date_input('date','payment_edit_date',_d($payment->date)); ?>
								<?php echo render_select('paymentmode',$payment_modes,array('id','name'),'payment_mode',$payment->paymentmode); ?>
								<i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('payment_method_info'); ?>"></i>
								<?php echo render_input('paymentmethod','payment_method',$payment->paymentmethod); ?>
								<?php echo render_input('transactionid','payment_transaction_id',$payment->transactionid); ?>
								<?php echo render_textarea('note','note',$payment->note,array('rows'=>7)); ?>
								<div class="btn-bottom-toolbar text-right">
									<button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
								</div>
							</div>
							<?php echo form_close(); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-7">
				<div class="panel_s">
					<div class="panel-body">
						<h4 class="pull-left "><?php echo _l('payment_view_heading'); ?></h4>
						<div class="pull-right">
							<a href="<?php echo admin_url('payments/pdf/'.$payment->paymentid.'?print=true'); ?>" target="_blank" class="btn btn-default" data-toggle="tooltip" title="<?php echo _l('print'); ?>" data-placement="bottom"><i class="fa fa-print"></i></a>
							<a href="<?php echo admin_url('payments/pdf/'.$payment->paymentid); ?>" class="btn btn-default" data-toggle="tooltip" title="<?php echo _l('view_pdf'); ?>" data-placement="bottom"><i class="fa fa-file-pdf-o"></i></a>
							<?php if(has_permission('managePayment','','delete')){ ?>
							<a href="<?php echo admin_url('payments/delete/'.$payment->paymentid); ?>" class="btn btn-danger _delete"><i class="fa fa-remove"></i></a>
							<?php } ?>
						</div>
						<div class="clearfix"></div>
						<hr class="hr-panel-heading" />
						<div class="row">
							<div class="col-md-6">
								<address>
									<?php echo format_organization_info(); ?>
								</address>
							</div>
							<div class="col-sm-6 text-right">
								<address>
									<span class="bold">
										<?php echo format_customer_info($invoice, 'payment', 'billing', true); ?>
									</address>
								</div>
							</div>
							<div class="col-md-12 text-center">
								<h3 class="text-uppercase"><?php echo _l('payment_receipt'); ?></h3>
							</div>
							<div class="col-md-12 mtop30">
								<div class="row">
									<div class="col-md-6">
										<p><?php echo _l('payment_date'); ?> <span class="pull-right bold"><?php echo _d($payment->date); ?></span></p>
										<hr />
										<p><?php echo _l('payment_view_mode'); ?>
											<span class="pull-right bold">
												<?php echo $payment->name; ?>
												<?php if(!empty($payment->paymentmethod)){
													echo ' - ' . $payment->paymentmethod;
												}
												?>
											</span></p>
											<?php if(!empty($payment->transactionid)) { ?>
											<hr />
											<p><?php echo _l('payment_transaction_id'); ?>: <span class="pull-right bold"><?php echo $payment->transactionid; ?></span></p>
											<?php } ?>
										</div>
										<div class="clearfix"></div>
										<div class="col-md-6">
											<div class="payment-preview-wrapper">
												<?php echo _l('payment_total_amount'); ?><br />
												<?php echo format_money($payment->amount,$invoice->symbol); ?>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-12 mtop30">
									<h4><?php echo _l('payment_for_string'); ?></h4>
									<div class="table-responsive">
										<table class="table table-borderd table-hover">
											<thead>
												<tr>
													<th><?php echo _l('payment_table_invoice_number'); ?></th>
													<th><?php echo _l('payment_table_invoice_date'); ?></th>
													<th><?php echo _l('payment_table_invoice_amount_total'); ?></th>
													<th><?php echo _l('payment_table_payment_amount_total'); ?></th>
													<?php if($invoice->status != 2 && $invoice->status != 5) { ?>
													<th><span class="text-danger"><?php echo _l('invoice_amount_due'); ?></span></th>
													<?php } ?>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td><?php echo format_invoice_number($invoice->id); ?></td>
													<td><?php echo _d($invoice->date); ?></td>
													<td><?php echo format_money($invoice->total,$invoice->symbol); ?></td>
													<td><?php echo format_money($payment->amount,$invoice->symbol); ?></td>
													<?php if($invoice->status != 2 && $invoice->status != 5) { ?>
													<td class="text-danger">
													<?php echo format_money(get_invoice_total_left_to_pay($invoice->id, $invoice->total), $invoice->symbol); ?>
													</td>
													<?php } ?>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-12">
				 <div class="btn-bottom-pusher"></div>
			</div>
		</div>
		<?php init_tail(); ?>
		<script>
			$(function(){
				_validate_form($('form'),{amount:'required',date:'required'});
			});
		</script>
	</body>
	</html>
