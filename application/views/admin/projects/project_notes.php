<p><?php echo _l('project_note_private'); ?></p>
<hr />
<?php echo form_open(admin_url('projects/save_note/'.$project->id)); ?>
<?php echo render_textarea('content','',$staff_notes,array(),array(),'','tinymce'); ?>
<button type="submit" class="btn btn-info"><?php echo _l('project_save_note'); ?></button>
<?php echo form_close(); ?>
