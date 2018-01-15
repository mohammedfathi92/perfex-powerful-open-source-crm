<div class="row">
  <?php if(count($expenses_years) > 1 || isset($currencies)){ ?>
  <div class="col-md-12 simple-bootstrap-select mbot5">
   <?php if(isset($currencies)){ ?>
   <select class="selectpicker" data-width="auto" name="expenses_total_currency" onchange="init_expenses_total();">
    <?php foreach($currencies as $currency){
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
  <?php if(count($expenses_years) > 1){ ?>
  <select data-none-selected-text="<?php echo date('Y'); ?>" data-width="auto" class="selectpicker" multiple name="expenses_total_years" onchange="init_expenses_total();">
   <?php foreach($expenses_years as $year){ ?>
   <option value="<?php echo $year['year']; ?>"<?php if($this->input->post('years') && in_array($year['year'], $this->input->post('years')) || !$this->input->post('years') && date('Y') == $year['year']){echo ' selected'; } ?>><?php echo $year['year']; ?></option>
   <?php } ?>
 </select>
 <?php } ?>
</div>
<?php  } ?>
<div class="col-md-5ths col-xs-12 total-column">
  <div class="panel_s">
    <div class="panel-body">
      <h3 class="text-muted _total">
       <?php echo $totals['all']['total']; ?>
     </h3>
     <span class="text-warning"><?php echo _l('expenses_total'); ?></span>
   </div>
 </div>
</div>
<div class="col-md-5ths col-xs-12 total-column">
  <div class="panel_s">
    <div class="panel-body">
      <h3 class="text-muted _total">
       <?php echo $totals['billable']['total']; ?>
     </h3>
     <span class="text-success"><?php echo _l('expenses_list_billable'); ?></span>
   </div>
 </div>
</div>
<div class="col-md-5ths col-xs-12 total-column">
  <div class="panel_s">
    <div class="panel-body">
      <h3 class="text-muted _total">
       <?php echo $totals['non_billable']['total']; ?>
     </h3>
     <span class="text-warning"><?php echo _l('expenses_list_non_billable'); ?></span>
   </div>
 </div>
</div>
<div class="col-md-5ths col-xs-12 total-column">
  <div class="panel_s">
    <div class="panel-body">
      <h3 class="text-muted _total">
       <?php echo $totals['unbilled']['total']; ?>
     </h3>
     <span class="text-danger"><?php echo _l('expenses_list_unbilled'); ?></span>
   </div>
 </div>
</div>
<div class="col-md-5ths col-xs-12 total-column">
  <div class="panel_s">
    <div class="panel-body">
      <h3 class="text-muted _total">
       <?php echo $totals['billed']['total']; ?>
     </h3>
     <span class="text-success"><?php echo _l('expense_billed'); ?></span>
   </div>
 </div>
</div>
</div>
<div class="clearfix"></div>
<script>
  init_selectpicker();
</script>
