  <div id="estimates-report" class="hide">
   <div class="row">
      <div class="col-md-4">
         <div class="form-group">
            <label for="estimate_status"><?php echo _l('estimate_status'); ?></label>
            <select name="estimate_status" class="selectpicker" multiple data-width="100%">
               <option value="" selected><?php echo _l('invoice_status_report_all'); ?></option>
               <?php foreach($estimate_statuses as $status){ ?>
               <option value="<?php echo $status; ?>"><?php echo format_estimate_status($status,'',false) ?></option>
               <?php } ?>
            </select>
         </div>
      </div>
      <?php if(count($estimates_sale_agents) > 0 ) { ?>
      <div class="col-md-4">
         <div class="form-group">
            <label for="sale_agent_estimates"><?php echo _l('sale_agent_string'); ?></label>
            <select name="sale_agent_estimates" class="selectpicker" multiple data-width="100%">
               <option value="" selected><?php echo _l('invoice_status_report_all'); ?></option>
               <?php foreach($estimates_sale_agents as $agent){ ?>
               <option value="<?php echo $agent['sale_agent']; ?>"><?php echo get_staff_full_name($agent['sale_agent']); ?></option>
               <?php } ?>
            </select>
         </div>
      </div>
      <?php } ?>
   </div>
   <div class="clearfix"></div>
      <table class="table table-estimates-report scroll-responsive">
         <thead>
          <tr>
            <th><?php echo _l('estimate_dt_table_heading_number'); ?></th>
            <th><?php echo _l('estimate_dt_table_heading_client'); ?></th>
            <th><?php echo _l('report_invoice_number'); ?></th>
            <th><?php echo _l('invoice_estimate_year'); ?></th>
            <th><?php echo _l('estimate_dt_table_heading_date'); ?></th>
            <th><?php echo _l('estimate_dt_table_heading_expirydate'); ?></th>
            <th><?php echo _l('estimate_dt_table_heading_amount'); ?></th>
            <th><?php echo _l('report_invoice_amount_with_tax'); ?></th>
            <th><?php echo _l('report_invoice_total_tax'); ?></th>
            <?php foreach($estimate_taxes as $tax){ ?>
            <th><?php echo $tax['taxname']; ?> <small><?php echo $tax['taxrate']; ?>%</small></th>
            <?php } ?>
            <th><?php echo _l('estimate_discount'); ?></th>
            <th><?php echo _l('estimate_adjustment'); ?></th>
            <th><?php echo _l('reference_no'); ?></th>
            <th><?php echo _l('estimate_dt_table_heading_status'); ?></th>
         </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
         <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td class="subtotal"></td>
            <td class="total"></td>
            <td class="total_tax"></td>
            <?php foreach($estimate_taxes as $key => $tax){ ?>
            <td class="total_tax_single_<?php echo $key; ?>"></td>
            <?php } ?>
            <td class="discount_total"></td>
            <td class="adjustment"></td>
            <td></td>
            <td></td>
         </tr>
      </tfoot>
   </table>
</div>
