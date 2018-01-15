<div class="row">
<?php if(count($estimates_years) > 1 || isset($currencies)){ ?>
  <div class="col-md-12 simple-bootstrap-select mbot5">
   <?php if(isset($currencies)){ ?>
    <select class="selectpicker" data-width="auto" name="total_currency" onchange="init_estimates_total();">
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
      <?php if(count($estimates_years) > 1){ ?>
      <select data-none-selected-text="<?php echo date('Y'); ?>" data-width="auto" class="selectpicker" multiple name="estimates_total_years" onchange="init_estimates_total();">
         <?php foreach($estimates_years as $year){ ?>
         <option value="<?php echo $year['year']; ?>"<?php if($this->input->post('years') && in_array($year['year'], $this->input->post('years')) || !$this->input->post('years') && date('Y') == $year['year']){echo ' selected'; } ?>><?php echo $year['year']; ?></option>
         <?php } ?>
      </select>
      <?php } ?>
  </div>
  <?php }
  foreach($totals as $key => $data){
    $class = estimate_status_color_class($data['status']);
    $name = estimate_status_by_id($data['status']);
    ?>
    <div class="col-md-5ths col-xs-12 total-column">
      <div class="panel_s">
        <div class="panel-body">
          <h3 class="text-muted _total">
            <?php echo format_money($data['total'],$data['symbol']); ?>
          </h3>
          <span class="text-<?php echo $class; ?>"><?php echo $name; ?></span>
        </div>
      </div>
    </div>
    <?php } ?>
  </div>
  <div class="clearfix"></div>
  <script>
  $(function(){
    init_selectpicker();
  });
  </script>
