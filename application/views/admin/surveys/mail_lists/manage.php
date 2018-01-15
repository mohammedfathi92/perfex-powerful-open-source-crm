<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12">

				<div class="panel_s">
					<div class="panel-body">
						<?php if(has_permission('surveys','','create')){ ?>
						<div class="_buttons">
							<a href="<?php echo admin_url('surveys/mail_list'); ?>" class="btn btn-info pull-left display-block"><?php echo _l('new_mail_list'); ?></a>
						</div>
						<div class="clearfix"></div>
						<hr class="hr-panel-heading" />
						<?php } ?>
						<?php render_datatable(array(
							_l('id'),
							_l('mail_lists_dt_list_name'),
							_l('mail_lists_dt_datecreated'),
							_l('mail_lists_dt_creator'),
							_l('options'),
							),'mail-lists'); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php init_tail(); ?>
	<script>
		$(function(){
			initDataTable('.table-mail-lists', window.location.href, [4], [4]);
		});
	</script>
</body>
</html>
