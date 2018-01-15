<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
           <div class="_buttons">
            <a href="#" class="btn btn-info pull-left display-block" onclick="new_spam_filter(); return false">
              <?php echo _l('new_spam_filter'); ?>
            </a>
          </div>
          <div class="clearfix"></div>
          <hr class="hr-panel-heading" />
          <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#senders" aria-controls="senders" role="tab" data-toggle="tab"><?php echo _l('spam_filter_blocked_senders'); ?></a></li>
            <li role="presentation"><a href="#subjects" aria-controls="subjects" role="tab" data-toggle="tab"><?php echo _l('spam_filter_blocked_subjects'); ?></a></li>
            <li role="presentation"><a href="#phrases" aria-controls="phrases" role="tab" data-toggle="tab"><?php echo _l('spam_filter_blocked_phrases'); ?></a></li>
          </ul>
          <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="senders">
              <?php render_datatable(array(_l('spam_filter_content'),_l('options')),'sender'); ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="subjects">
             <?php render_datatable(array(_l('spam_filter_content'),_l('options')),'subject'); ?>
           </div>
           <div role="tabpanel" class="tab-pane" id="phrases">
             <?php render_datatable(array(_l('spam_filter_content'),_l('options')),'phrase'); ?>
           </div>
         </div>
       </div>
     </div>
   </div>
 </div>
</div>
</div>
<div class="modal fade" id="spam_filter" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <?php echo form_open(admin_url('tickets/spam_filter')); ?>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">
          <span class="edit-title"><?php echo _l('spamfilter_edit_heading'); ?></span>
          <span class="add-title"><?php echo _l('spamfilter_add_heading'); ?></span>
        </h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div id="spam_filter_additional"></div>
            <div class="form-group">
              <label for="type"><?php echo _l('spamfilter_type'); ?></label>
              <select name="type" id="type" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                <option value=""></option>
                <option value="sender"><?php echo _l('spamfilter_type_sender'); ?></option>
                <option value="subject"><?php echo _l('spamfilter_type_subject'); ?></option>
                <option value="phrase"><?php echo _l('spamfilter_type_phrase'); ?></option>
              </select>
            </div>
            <?php echo render_textarea('value','spam_filter_content'); ?>
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
<?php init_tail(); ?>
<script>
  $(function(){
   initDataTable('.table-sender', window.location.href + '/sender', [1], [1]);
   initDataTable('.table-subject', window.location.href + '/subject', [1], [1]);
   initDataTable('.table-phrase', window.location.href + '/phrase', [1], [1]);
   _validate_form($('form'),{value:'required',type:'required'},manage_spam_filters);

   $('#spam_filter').on('hidden.bs.modal', function(event) {
    $('#spam_filter select').selectpicker('val','');
    $('#spam_filter textarea').val('');
    $('#spam_filter #spam_filter_additional').html('');
    $('.add-title').removeClass('hide');
    $('.edit-title').removeClass('hide');
  });
 });
  function manage_spam_filters(form) {
    var original_type = $('input[name="original_type"]').val();

    $('input[name="original_type"]').remove();
    var data = $(form).serialize();
    var url = form.action;
    $.post(url, data).done(function(response) {
      response = JSON.parse(response);
      if (response.success == true) {
        var type = $('select[name="type"]').selectpicker('val');
        $('.table-'+type).DataTable().ajax.reload();
        if(type != original_type){
          $('.table-'+original_type).DataTable().ajax.reload();
        }
        alert_float('success', response.message);
      }
      $('#spam_filter').modal('hide');
    });
    return false;
  }

  function new_spam_filter(){
    $('#spam_filter').modal('show');
    $('.edit-title').addClass('hide');
  }
  function edit_spam_filter(invoker,id){
    var type = $(invoker).data('type');
    var value = $(invoker).data('value');
    $('#spam_filter_additional').append(hidden_input('id',id));
    $('#spam_filter_additional').append(hidden_input('original_type',type));
    $('#spam_filter select').selectpicker('val',type);
    $('#spam_filter textarea').val(value);
    $('#spam_filter').modal('show');
    $('.add-title').addClass('hide');
  }

</script>
</body>
</html>
