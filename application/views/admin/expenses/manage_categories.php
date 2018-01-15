<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                      <div class="_buttons">
                        <a href="#" onclick="new_category(); return false;" class="btn btn-info pull-left display-block"><?php echo _l('new_expense_category'); ?></a>
                    </div>
                    <div class="clearfix"></div>
                    <hr class="hr-panel-heading" />
                    <div class="clearfix"></div>
                    <?php render_datatable(array(_l('name'),_l('dt_expense_description'),_l('options')),'expenses-categories'); ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<?php $this->load->view('admin/expenses/expense_category'); ?>
<?php init_tail(); ?>
<script>
    $(function(){
        initDataTable('.table-expenses-categories', window.location.href, [2], [2]);
    });
</script>
</body>
</html>
