<?php init_head(); ?>
<div id="wrapper">
 <div class="content">
  <div class="row">
   <div class="col-md-12">
    <?php if($fixedlist == false) { ?>
    <?php if(has_permission('surveys','','create')){ ?>
    <div class="panel_s">
     <div class="panel-body _buttons">
      <a href="#" class="btn btn-default pull-left mright10" data-toggle="modal" data-target="#import_emails"><?php echo _l('btn_import_emails'); ?></a>
      <a href="#" class="btn btn-default pull-left" data-toggle="modal" data-target="#add_email_to_list"><?php echo _l('btn_add_email_to_list'); ?></a>
    </div>
  </div>
  <?php } ?>
  <?php } ?>
  <div class="panel_s">
   <div class="panel-body">
    <?php if($fixedlist == true) { ?>
    <?php if($id == 'leads'){ ?>
    <div class="row">
      <div class="col-md-3">
       <?php
       echo render_select('view_status',$statuses,array('id','name'),'','',array('data-width'=>'100%','data-none-selected-text'=>_l('leads_dt_status')));
       ?>
     </div>
     <div class="col-md-3">
       <?php
       echo render_select('view_source',$sources,array('id','name'),'','',array('data-width'=>'100%','data-none-selected-text'=>_l('leads_source')));
       ?>
     </div>
     <div class="col-md-3">
   <div class="select-placeholder">
         <select name="custom_view" title="<?php echo _l('additional_filters'); ?>" id="custom_view" class="selectpicker" data-width="100%">
        <option value=""></option>
        <option value="lost"><?php echo _l('lead_lost'); ?></option>
        <option value="contacted_today"><?php echo _l('lead_add_edit_contacted_today'); ?></option>
        <option value="created_today"><?php echo _l('created_today'); ?></option>
      </select>
   </div>
    </div>
  </div>
  <hr class="no-mtop" />
  <?php } else if($id == 'clients'){ ?>
  <div class="row mbot15">
    <div class="col-md-3">
    <div class="select-placeholder">
       <select name="customer_groups" title="<?php echo _l('customer_groups'); ?> - <?php echo _l('customers_sort_all'); ?>" multiple id="customer_groups" class="selectpicker" data-width="100%">
      <?php foreach($groups as $group){ ?>
      <option value="<?php echo $group['id']; ?>"><?php echo $group['name']; ?></option>
      <?php } ?>
    </select>
    </div>
  </div>
  <div class="col-md-6 mtop10">
    <div class="radio radio-inline radio-info">
      <input type="radio" name="active_customers_filter" checked id="customers_filter_all" value="">
      <label for="customers_filter_all"><?php echo _l('customers_sort_all'); ?></label>
    </div>
    <div class="radio radio-inline radio-info">
      <input type="radio" name="active_customers_filter" id="active_customers" value="active_customers">
      <label for="active_customers"><?php echo _l('active_customers'); ?></label>
    </div>
    <div class="radio radio-inline radio-info">
      <input type="radio" name="active_customers_filter" id="active_contacts" value="active_contacts">
      <label for="active_contacts"><?php echo _l('customers_summary_active'); ?></label>
    </div>
  </div>
</div>
<hr />
<?php } ?>
<div class="clearfix"></div>
<?php } ?>
<div class="table-responsive">
 <table class="table table-mail-list-view">
  <thead>
   <th><?php echo _l('mail_lists_view_email_email_heading'); ?></th>
   <?php if($fixedlist == true){ ?>
   <?php if($id == 'leads'){
    echo '<th>'._l('leads_dt_name').'</th>';
    echo '<th>'._l('lead_company').'</th>';
  } else if($id == 'clients'){
    echo '<th>'._l('client_firstname').'</th>';
    echo '<th>'._l('client_lastname').'</th>';
    echo '<th>'._l('clients_list_full_name').'</th>';
    echo '<th>'._l('clients_company').'</th>';
  } else if($id == 'staff'){
    echo '<th>'._l('staff_dt_name').'</th>';
  }
  ?>
  <?php } ?>
  <th><?php echo _l('mail_lists_view_email_date_heading'); ?></th>
  <?php if(isset($custom_fields) && count($custom_fields) > 0){
    foreach($custom_fields as $field){ ?>
    <th><?php echo $field['fieldname']; ?></th>
    <?php
  }
}
?>
<th><?php echo _l('options'); ?></th>
</thead>
<tbody>
</tbody>
</table>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
<?php if($fixedlist == false) { ?>
<div class="modal fade" id="add_email_to_list" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
 <div class="modal-dialog" role="document">
  <div class="modal-content">
   <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel"><?php echo _l('add_new_email_to',$list->name); ?></h4>
  </div>
  <?php echo form_open('admin/surveys/add_email_to_list',array('id'=>'add_single_email_form')); ?>
  <div class="modal-body">
    <div class="row">
     <div class="col-md-12">
      <?php echo render_input('email','mail_list_new_email_edit_add_label'); ?>
      <?php echo form_hidden('listid',$list->listid); ?>
      <?php
      if(count($custom_fields) > 0){
       foreach($custom_fields as $field){ ?>
       <?php echo render_input('customfields['.$field['customfieldid'].']',$field['fieldname']); ?>
       <?php }
     }
     ?>
   </div>
 </div>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
  <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
  <?php echo form_close(); ?>
</div>
</div>
</div>
</div>
<div class="modal fade" id="import_emails" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
 <div class="modal-dialog" role="document">
  <div class="modal-content">
   <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel"><?php echo _l('import_emails_to',$list->name); ?></h4>
  </div>
  <?php echo form_open_multipart('admin/surveys/import_emails',array('id'=>'import_emails_form')); ?>
  <div class="modal-body">
    <div class="row">
     <div class="col-md-12">
      <?php echo render_input('file_xls','mail_list_import_file','','file'); ?>
      <?php echo form_hidden('listid',$list->listid); ?>
      <?php
      if(count($custom_fields) > 0){ ?>
      <p class="nomargin bold"><?php echo _l('mail_list_available_custom_fields'); ?></p>
      <?php foreach($custom_fields as $field){ ?>
      <p><?php echo $field['fieldname']; ?></p>
      <?php }
    }
    ?>
  </div>
</div>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
  <button type="submit" class="btn btn-info"><?php echo _l('submit_import_emails'); ?></button>
  <?php echo form_close(); ?>
</div>
</div>
</div>
</div>
<?php } ?>
<?php init_tail(); ?>
<script src="<?php echo base_url('assets/plugins/jquery-validation/additional-methods.min.js'); ?>"></script>
<script>
   // Find the last thead - dynamic table with custom fields headings
   var options_not_sortable = $('.table-mail-list-view').find('th').length - 1;
   var ServerParams = {};
   <?php if($id == 'leads'){ ?>
     ServerParams = {
      "custom_view": "[name='custom_view']",
      "status": "[name='view_status']",
      "source": "[name='view_source']",
    }
    $.each(ServerParams, function(i, obj) {
      $('select' + obj).on('change', function() {
        $('.table-mail-list-view').DataTable().ajax.reload();
      });
    });
    <?php } else if($id == 'clients'){ ?>
      ServerParams = {
        'customer_groups':'[name="customer_groups"]',
        'active_customers_filter':'[name="active_customers_filter"]:checked',
      }
      $('select[name="customer_groups"],input[name="active_customers_filter"]').on('change', function() {
        $('.table-mail-list-view').DataTable().ajax.reload();
      });
      <?php } ?>

      $(function(){
          initDataTable('.table-mail-list-view', window.location.href, [options_not_sortable], [options_not_sortable],ServerParams,[0,'ASC']);
          _validate_form($('#add_single_email_form'), {
           email: {
             required: true,
             email: true
           }
         }, add_single_email_to_mail_list);
          _validate_form($('#import_emails_form'), {
           file_xls: {
             required: true,
             extension: "xls|xlsx"
           }
         });
      });

   // Modal add single email to mail list
   function add_single_email_to_mail_list(form) {
     var data = $(form).serialize();
     var url = form.action;
     $.post(url, data).done(function(response) {
       response = JSON.parse(response);

       if (response.success == true) {
         $('.table-mail-list-view').DataTable().ajax.reload(null,false);
         alert_float('success', response.message);
       } else {
         alert_float('danger', response.error_message);
       }

       $('#add_email_to_list').modal('hide')
       $(form).find('input.form-control').val('');

     });
     return false;
   }
   // Remove single email from mail list
   function remove_email_from_mail_list(row, emailid) {
     $.get(admin_url + 'surveys/remove_email_from_mail_list/' + emailid, function(response) {
       if (response.success == true) {
         alert_float('success', response.message);
         $(row).parents('tr').remove();
       } else {
         alert_float('warning', response.message);
       }
     }, 'json');
   }
 </script>
</body>
</html>
