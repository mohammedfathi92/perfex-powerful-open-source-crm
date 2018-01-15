<div class="row">
	<div class="col-md-12">
		<div class="panel_s">
			<?php
			if(has_contact_permission('invoices')){ ?>
			<div class="panel-body">
				<p class="bold"><?php echo _l('clients_quick_invoice_info'); ?></p>
				<?php if(has_contact_permission('invoices')){ ?>
					<a href="<?php echo site_url('clients/statement'); ?>"><?php echo _l('view_account_statement'); ?></a>
				<?php } ?>
				<hr />
				<?php get_template_part('invoices_stats'); ?>
				<hr />
				<div class="row">
					<div class="col-md-3">
						<?php if(count($payments_years) > 0){ ?>
						<div class="form-group">
							<select data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" class="form-control" id="payments_year" name="payments_years" data-width="100%" onchange="total_income_bar_report();" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
								<?php foreach($payments_years as $year) { ?>
								<option value="<?php echo $year['year']; ?>"<?php if($year['year'] == date('Y')){echo 'selected';} ?>>
									<?php echo $year['year']; ?>
								</option>
								<?php } ?>
							</select>
						</div>
						<?php } ?>
						<?php if(is_client_using_multiple_currencies()){ ?>
						<div id="currency" class="form-group mtop15" data-toggle="tooltip" title="<?php echo _l('clients_home_currency_select_tooltip'); ?>">
							<select data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" class="form-control" name="currency">
								<?php foreach($currencies as $currency){
									$selected = '';
									if($currency['isdefault'] == 1){
										$selected = 'selected';
									}
									?>
									<option value="<?php echo $currency['id']; ?>" <?php echo $selected; ?>><?php echo $currency['symbol']; ?> - <?php echo $currency['name']; ?></option>
									<?php } ?>
								</select>
							</div>
							<?php } ?>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<div class="relative" style="max-height:400px;">
								<canvas id="client-home-chart" height="400" class="animated fadeIn"></canvas>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
