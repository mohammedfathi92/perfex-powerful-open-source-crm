<div class="modal fade" id="expense-category-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('expenses/category'),array('id'=>'expense-category-form')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="edit-title"><?php echo _l('edit_expense_category'); ?></span>
                    <span class="add-title"><?php echo _l('new_expense_category'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="additional"></div>
                        <?php echo render_input('name','expense_add_edit_name'); ?>
                        <?php echo render_textarea('description','expense_add_edit_description'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
        </div><!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script>
  window.addEventListener('load',function(){
     _validate_form($('#expense-category-form'),{name:'required'},manage_categories);
        $('#expense-category-modal').on('hidden.bs.modal', function(event) {
            $('#additional').html('');
            $('#expense-category-modal input[name="name"]').val('');
            $('#expense-category-modal textarea').val('');
            $('.add-title').removeClass('hide');
            $('.edit-title').removeClass('hide');
        });
  });
   function manage_categories(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function(response) {
            response = JSON.parse(response);

            if(response.success == true){
                alert_float('success',response.message);
                if($('body').hasClass('expense') && typeof(response.id) != 'undefined') {
                    var category = $('#category');
                    category.find('option:first').after('<option value="'+response.id+'">'+response.name+'</option>');
                    category.selectpicker('val',response.id);
                    category.selectpicker('refresh');
                }
            }

            if($.fn.DataTable.isDataTable('.table-expenses-categories')){
                $('.table-expenses-categories').DataTable().ajax.reload();
            }

            $('#expense-category-modal').modal('hide');
        });
        return false;
    }

    function new_category(){
        $('#expense-category-modal').modal('show');
        $('.edit-title').addClass('hide');
    }

    function edit_category(invoker,id){
        var name = $(invoker).data('name');
        var description = $(invoker).data('description');
        $('#additional').append(hidden_input('id',id));
        $('#expense-category-modal input[name="name"]').val(name);
        $('#expense-category-modal textarea').val(description);
        $('#expense-category-modal').modal('show');
        $('.add-title').addClass('hide');
    }
</script>
