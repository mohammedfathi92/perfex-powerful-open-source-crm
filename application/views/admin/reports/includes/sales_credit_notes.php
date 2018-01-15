  <div id="credit-notes" class="hide">
       <div class="row">
         <div class="col-md-4">
            <div class="form-group">
               <label for="credit_note_status"><?php echo _l('credit_note_status'); ?></label>
               <select name="credit_note_status" class="selectpicker" multiple data-width="100%">
                  <option value="" selected><?php echo _l('invoice_status_report_all'); ?></option>
                  <?php foreach($credit_notes_statuses as $status){ ?>
                  <option value="<?php echo $status['id']; ?>"><?php echo format_credit_note_status($status['id'],false) ?></option>
                  <?php } ?>
               </select>
            </div>
         </div>
      </div>

      <table class="table table-striped table-credit-notes-report scroll-responsive">
        <thead>
           <tr>
              <th><?php echo _l('credit_note_number'); ?></th>
              <th><?php echo _l('credit_note_date'); ?></th>
              <th><?php echo _l('client'); ?></th>
              <th><?php echo _l('reference_no'); ?></th>
              <th><?php echo _l('credit_note_amount'); ?></th>
              <th><?php echo _l('report_invoice_amount_with_tax'); ?></th>
              <th><?php echo _l('report_invoice_total_tax'); ?></th>
              <?php foreach($credit_note_taxes as $tax){ ?>
              <th><?php echo $tax['taxname']; ?> <small><?php echo $tax['taxrate']; ?>%</small></th>
              <?php } ?>
              <th><?php echo _l('invoice_discount'); ?></th>
              <th><?php echo _l('invoice_adjustment'); ?></th>
              <th><?php echo _l('credit_note_remaining_credits'); ?></th>
              <th><?php echo _l('credit_note_status'); ?></th>
          </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
       <tr>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td class="subtotal"></td>
          <td class="total"></td>
          <td class="total_tax"></td>
          <?php foreach($credit_note_taxes as $key => $tax){ ?>
          <td class="total_tax_single_<?php echo $key; ?>"></td>
          <?php } ?>
          <td class="discount_total"></td>
          <td class="adjustment"></td>
          <td class="remaining_amount"></td>
          <td></td>
      </tr>
  </tfoot>
</table>
</div>

