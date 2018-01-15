  <div id="customers-report" class="hide">
      <?php render_datatable(array(
       _l('reports_sales_dt_customers_client'),
       _l('reports_sales_dt_customers_total_invoices'),
       _l('reports_sales_dt_items_customers_amount'),
       _l('reports_sales_dt_items_customers_amount_with_tax'),
       ),'customers-report scroll-responsive'); ?>
   </div>
