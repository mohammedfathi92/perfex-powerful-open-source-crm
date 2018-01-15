<?php init_head(); ?>
<div id="wrapper">
 <div class="content">
  <div class="row">
   <div class="col-md-12">
    <div class="panel_s">
     <div class="panel-body">
       <div class="_buttons">
        <a href="<?php echo admin_url('leads/form'); ?>" class="btn btn-info pull-left"><?php echo _l('new_form'); ?></a>
      </div>
      <div class="clearfix"></div>
      <hr class="hr-panel-heading" />
      <?php do_action('forms_table_start'); ?>
      <div class="clearfix"></div>
      <?php render_datatable(array(
       _l('id'),
       _l('form_name'),
       _l('total_submissions'),
       _l('leads_dt_datecreated'),
       _l('options'),
       ),'web-to-lead'); ?>
     </div>
   </div>
 </div>
</div>
</div>
</div>
<?php init_tail(); ?>
<script>
 $(function(){
  initDataTable('.table-web-to-lead', window.location.href, [3], [3]);
});
</script>
</body>
</html>
