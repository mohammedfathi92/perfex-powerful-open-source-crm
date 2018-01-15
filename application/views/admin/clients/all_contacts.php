<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <div class="clearfix"></div>
            <?php
            $table_data = array(
              _l('client_firstname'),
              _l('client_lastname'),
              _l('client_email'),
              _l('clients_list_company'),
              _l('client_phonenumber'),
              _l('contact_position'),
              _l('clients_list_last_login'),
              _l('contact_active'),
              );
            $custom_fields = get_custom_fields('contacts',array('show_on_table'=>1));
            foreach($custom_fields as $field){
              array_push($table_data,$field['name']);
            }
            array_push($table_data,_l('options'));
            render_datatable($table_data,'all-contacts');
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<?php $this->load->view('admin/clients/client_js'); ?>
<div id="contact_data"></div>
<script>
 $(function(){
  var optionsHeading = $('.table-all-contacts').find('th').length - 1;
  initDataTable('.table-all-contacts', window.location.href, [optionsHeading], [optionsHeading],'undefined',[0,'ASC']);
});
</script>
</body>
</html>
