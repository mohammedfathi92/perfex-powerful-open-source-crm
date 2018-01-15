<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12">
				<div class="panel_s">
					<div class="panel-body">
						<div class="_buttons">
							<a href="#" onclick="new_service(); return false;" class="btn btn-info pull-left display-block"><?php echo _l('new_service'); ?></a>
						</div>
						<div class="clearfix"></div>
						<hr class="hr-panel-heading" />
						<div class="clearfix"></div>
						<?php render_datatable(array(
							_l('services_dt_name'),
							_l('options'),
							),'services'); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php $this->load->view('admin/tickets/services/service'); ?>
	<?php init_tail(); ?>
	<script>
		$(function(){
			initDataTable('.table-services', window.location.href, [1], [1]);
		});
	</script>
</body>
</html>
