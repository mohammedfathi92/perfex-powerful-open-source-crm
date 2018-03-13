<div class="row">
   <?php if(count($invoices_years) > 1 || isset($invoices_total_currencies)){ ?>
   <div class="col-md-12 simple-bootstrap-select mbot5">
      <?php if(isset($invoices_total_currencies)){ ?>
      <select data-show-subtext="true" data-width="auto" class="selectpicker" name="total_currency" onchange="init_invoices_total();">
         <?php foreach($invoices_total_currencies as $currency){
            $selected = '';
            if(!$this->input->post('currency')){
              if($currency['isdefault'] == 1 || isset($_currency) && $_currency == $currency['id']){
                $selected = 'selected';
              }
            } else {
              if($this->input->post('currency') == $currency['id']){
               $selected = 'selected';
             }
            }
            ?>
         <option value="<?php echo $currency['id']; ?>" <?php echo $selected; ?> data-subtext="<?php echo $currency['name']; ?>"><?php echo $currency['symbol']; ?></option>
         <?php } ?>
      </select>
      <?php } ?>
      <?php if(count($invoices_years) > 1){ ?>
      <select data-none-selected-text="<?php echo date('Y'); ?>" data-width="auto" class="selectpicker" name="invoices_total_years" onchange="init_invoices_total();" multiple="true" id="invoices_total_years">
         <?php foreach($invoices_years as $year){ ?>
         <option value="<?php echo $year['year']; ?>"<?php if($this->input->post('years') && in_array($year['year'], $this->input->post('years')) || !$this->input->post('years') && date('Y') == $year['year']){echo ' selected'; } ?>><?php echo $year['year']; ?></option>
         <?php } ?>
      </select>
      <?php } ?>
   </div>
   <div class="clearfix"></div>
   <?php } ?>
   <div class="col-lg-4 col-xs-12 col-md-12 total-column">
      <div class="panel_s">
         <div class="panel-body">
            <h3 class="text-muted _total">
               <?php echo format_money($total_result['due'],$total_result['symbol']); ?>
            </h3>
            <span class="text-warning"><?php echo _l('outstanding_invoices'); ?></span>
         </div>
      </div>
   </div>
   <div class="col-lg-4 col-xs-12 col-md-12 total-column">
      <div class="panel_s">
         <div class="panel-body">
            <h3 class="text-muted _total">
               <?php echo format_money($total_result['overdue'],$total_result['symbol']); ?>
            </h3>
            <span class="text-danger"><?php echo _l('past_due_invoices'); ?></span>
         </div>
      </div>
   </div>
   <div class="col-lg-4 col-xs-12 col-md-12 total-column">
      <div class="panel_s">
         <div class="panel-body">
            <h3 class="text-muted _total">
               <?php echo format_money($total_result['paid'],$total_result['symbol']); ?>
            </h3>
            <span class="text-success"><?php echo _l('paid_invoices'); ?></span>
         </div>
      </div>
   </div>
</div>
<div class="clearfix"></div>
<script>
   (function() {
     if(typeof(init_selectpicker) == 'function'){
       init_selectpicker();
     }
   })();
</script>
