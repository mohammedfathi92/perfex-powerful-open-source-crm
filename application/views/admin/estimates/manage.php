<?php init_head(); ?>
<div id="wrapper">
 <div class="content">
  <div class="row">
   <?php $this->load->view('admin/estimates/list_template'); ?>
</div>
</div>
</div>
<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<script>var hidden_columns = [2,5,6,8,9];</script>
<?php init_tail(); ?>
<script>
 $(function(){
    init_estimate();
});
</script>
</body>
</html>
