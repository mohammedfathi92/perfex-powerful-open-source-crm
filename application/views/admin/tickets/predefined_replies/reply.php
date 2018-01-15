<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				<div class="panel_s">
					<div class="panel-body">
					<h4 class="no-margin">
					<?php echo $title; ?>
					</h4>
					  <hr class="hr-panel-heading" />
						<?php if(isset($predefined_reply)){ ?>
						<a href="<?php echo admin_url('tickets/predefined_reply'); ?>" class="btn btn-success pull-left mbot20 display-block"><?php echo _l('new_predefined_reply'); ?></a>
						<div class="clearfix"></div>
						<?php } ?>
						<?php echo form_open($this->uri->uri_string()); ?>

						<?php $value = (isset($predefined_reply) ? $predefined_reply->name : ''); ?>
						<?php $attrs = (isset($predefined_reply) ? array() : array('autofocus'=>true)); ?>
						<?php echo render_input('name','predefined_reply_add_edit_name',$value,'text',$attrs); ?>
						<?php $contents = ''; if(isset($predefined_reply)){$contents = $predefined_reply->message;} ?>
						<?php echo render_textarea('message','',$contents,array(),array(),'','tinymce'); ?>
						<button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
						<?php echo form_close(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php init_tail(); ?>
<script>
	$(function(){
		_validate_form($('form'),{name:'required'});
	});
</script>
</body>
</html>
