<div id="proposals-reports" class="hide">
 <div class="row">
   <div class="col-md-4">
      <div class="form-group">
         <label for="proposal_status"><?php echo _l('proposal_status'); ?></label>
         <select name="proposal_status" class="selectpicker" multiple data-width="100%">
            <option value="" selected><?php echo _l('invoice_status_report_all'); ?></option>
            <?php foreach($proposals_statuses as $status){ ?>
            <option value="<?php echo $status; ?>"><?php echo format_proposal_status($status,'',false) ?></option>
            <?php } ?>
         </select>
      </div>
   </div>
   <?php if(count($proposals_sale_agents) > 0 ) { ?>
   <div class="col-md-4">
      <div class="form-group">
         <label for="proposals_sale_agents"><?php echo _l('sale_agent_string'); ?> (<?php echo _l('proposal_assigned'); ?>)</label>
         <select name="proposals_sale_agents" class="selectpicker" multiple data-width="100%">
            <option value="" selected><?php echo _l('invoice_status_report_all'); ?></option>
            <?php foreach($proposals_sale_agents as $agent){ ?>
            <option value="<?php echo $agent['sale_agent']; ?>"><?php echo get_staff_full_name($agent['sale_agent']); ?></option>
            <?php } ?>
         </select>
      </div>
   </div>
   <?php } ?>
</div>
   <table class="table table-proposals-report scroll-responsive">
      <thead>
         <tr>
            <th><?php echo _l('proposal'); ?> #</th>
            <th><?php echo _l('proposal_subject'); ?></th>
            <th><?php echo _l('proposal_to'); ?></th>
            <th><?php echo _l('proposal_date'); ?></th>
            <th><?php echo _l('proposal_open_till'); ?></th>
            <th><?php echo _l('estimate_dt_table_heading_amount'); ?></th>
            <th><?php echo _l('report_invoice_amount_with_tax'); ?></th>
            <th><?php echo _l('report_invoice_total_tax'); ?></th>
            <?php foreach($proposal_taxes as $tax){ ?>
            <th><?php echo $tax['taxname']; ?> <small><?php echo $tax['taxrate']; ?>%</small></th>
            <?php } ?>
            <th><?php echo _l('estimate_discount'); ?></th>
            <th><?php echo _l('estimate_adjustment'); ?></th>
            <th><?php echo _l('proposal_status'); ?></th>
         </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td class="subtotal"></td>
         <td class="total"></td>
         <td class="total_tax"></td>
         <?php foreach($proposal_taxes as $key => $tax){ ?>
         <td class="total_tax_single_<?php echo $key; ?>"></td>
         <?php } ?>
         <td class="discount"></td>
         <td class="adjustment"></td>
         <td></td>
      </tfoot>
   </table>
</div>
