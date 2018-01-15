<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12">
				<div class="panel_s">
					<div class="panel-body">
						<div class="_buttons">
							<a href="<?php echo admin_url('tickets/predefined_reply'); ?>" class="btn btn-info pull-left display-block"><?php echo _l('new_predefined_reply'); ?></a>
						</div>
						<div class="clearfix"></div>
						<hr class="hr-panel-heading" />
						<div class="clearfix"></div>
						<?php render_datatable(array(
							_l('predefined_replies_dt_name'),
							_l('options'),
							),'predefined-replies'); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php init_tail(); ?>
	<script>
		$(function(){
			initDataTable('.table-predefined-replies', window.location.href, [1], [1]);
		});
	</script>
</body>
</html>
