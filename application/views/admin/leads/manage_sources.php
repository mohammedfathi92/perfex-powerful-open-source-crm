<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                      <div class="_buttons">
                        <a href="#" onclick="new_source(); return false;" class="btn btn-info pull-left display-block"><?php echo _l('lead_new_source'); ?></a>
                    </div>
                    <div class="clearfix"></div>
                    <hr class="hr-panel-heading" />
                    <?php if(count($sources) > 0){ ?>
                    <table class="table dt-table scroll-responsive">
                        <thead>
                            <th><?php echo _l('leads_sources_table_name'); ?></th>
                            <th><?php echo _l('options'); ?></th>
                        </thead>
                        <tbody>
                            <?php foreach($sources as $source){ ?>
                            <tr>
                                <td><a href="#" onclick="edit_source(this,<?php echo $source['id']; ?>); return false" data-name="<?php echo $source['name']; ?>"><?php echo $source['name']; ?></a><br />
                                    <span class="text-muted">
                                        <?php echo _l('leads_table_total',total_rows('tblleads',array('source'=>$source['id']))); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="#" onclick="edit_source(this,<?php echo $source['id']; ?>); return false" data-name="<?php echo $source['name']; ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square-o"></i></a>
                                    <a href="<?php echo admin_url('leads/delete_source/'.$source['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <?php } else { ?>
                    <p class="no-margin"><?php echo _l('leads_sources_not_found'); ?></p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<div class="modal fade" id="source" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('leads/source')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="edit-title"><?php echo _l('edit_source'); ?></span>
                    <span class="add-title"><?php echo _l('lead_new_source'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="additional"></div>
                        <?php echo render_input('name','leads_source_add_edit_name'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<?php init_tail(); ?>
<script>
    $(function(){
    	_validate_form($('form'),{name:'required'},manage_leads_sources);
    	$('#source').on('hidden.bs.modal', function(event) {
    		$('#additional').html('');
    		$('#source input[name="name"]').val('');
    		$('.add-title').removeClass('hide');
    		$('.edit-title').removeClass('hide');
    	});
    });
    function manage_leads_sources(form) {
    	var data = $(form).serialize();
    	var url = form.action;
    	$.post(url, data).done(function(response) {
    		window.location.reload();
    	});
    	return false;
    }
    function new_source(){
    	$('#source').modal('show');
    	$('.edit-title').addClass('hide');
    }
    function edit_source(invoker,id){
    	var name = $(invoker).data('name');
    	$('#additional').append(hidden_input('id',id));
    	$('#source input[name="name"]').val(name);
    	$('#source').modal('show');
    	$('.add-title').addClass('hide');
    }
</script>
</body>
</html>
