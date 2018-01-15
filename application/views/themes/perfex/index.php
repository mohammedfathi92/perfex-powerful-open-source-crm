<?php
echo $head;
if($use_navigation == true){
	get_template_part('navigation');
}
?>
<div id="wrapper">
	<div id="content">
		<div class="container">
			<div class="row">
				<?php get_template_part('alerts'); ?>
			</div>
		</div>
		<?php if(isset($knowledge_base_search)){ ?>
		<?php get_template_part('knowledge_base_search'); ?>
		<?php } ?>
		<div class="container">
			<div class="row">
					<?php // Dont show calendar for invoices,estimates,proposals etc.. views where no navigation is included or in kb area
					if(is_client_logged_in() && $use_submenu == true && !isset($knowledge_base_search)){ ?>
					<ul class="submenu customer-top-submenu">
						<li class="customers-top-submenu-files"><a href="<?php echo site_url('clients/files'); ?>"><i class="fa fa-file" aria-hidden="true"></i> <?php echo _l('customer_profile_files'); ?></a></li>
						<li class="customers-top-submenu-calendar"><a href="<?php echo site_url('clients/calendar'); ?>"><i class="fa fa-calendar-minus-o" aria-hidden="true"></i> <?php echo _l('calendar'); ?></a></li>
					</ul>
					<div class="clearfix"></div>
					<?php } ?>
					<?php echo $view; ?>
				</div>
			</div>
		</div>
		<?php
		echo $footer;
		echo $scripts;
		?>
	</div>
</body>
</html>
