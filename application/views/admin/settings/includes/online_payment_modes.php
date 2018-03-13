<ul class="nav nav-tabs" role="tablist">
 <li role="presentation" class="active">
  <a href="#payment_modes_general" aria-controls="payment_modes_general" role="tab" data-toggle="tab"><?php echo _l('settings_group_general'); ?></a>
</li>
<?php
foreach($payment_gateways as $gateway){
  $class_name = $gateway['id'].'_gateway'; ?>
  <li role="presentation">
    <a href="#online_payments_<?php echo $gateway['id']; ?>_tab" aria-controls="online_payments_paypal_tab" role="tab" data-toggle="tab"><?php echo $this->$class_name->get_name(); ?></a>
  </li>
  <?php } ?>
</ul>
<div class="tab-content mtop30">
 <div role="tabpanel" class="tab-pane active" id="payment_modes_general">
  <?php render_yes_no_option('notification_when_customer_pay_invoice','notification_when_customer_pay_invoice'); ?>
  <hr />
  <?php render_yes_no_option('allow_payment_amount_to_be_modified','settings_allow_payment_amount_to_be_modified'); ?>
</div>
<?php
foreach($payment_gateways as $gateway){
  $class_name = $gateway['id'].'_gateway'; ?>
  <div role="tabpanel" class="tab-pane" id="online_payments_<?php echo $gateway['id']; ?>_tab">
   <h4><?php echo $this->$class_name->get_name(); ?></h4>
   <?php do_action('before_render_payment_gateway_settings',$gateway); ?>
   <hr />
   <?php $settings = $this->$class_name->get_settings();
   foreach($settings as $option){
    $value = get_option($option['name']);
    $value = isset($option['encrypted']) && $option['encrypted'] == true ? $this->encryption->decrypt($value) : $value;
    if(!isset($option['type'])){$option['type'] = 'input';};
    if($option['type'] == 'yes_no'){
      render_yes_no_option($option['name'], $option['label']);
    } else if($option['type'] == 'input') {
      echo render_input('settings['.$option['name'].']', $option['label'],$value,(isset($option['input_type']) ? $option['input_type'] : 'text'),(isset($option['field_attributes']) ? $option['field_attributes'] : array()));
    } else if($option['type'] == 'textarea') {
      echo render_textarea('settings['.$option['name'].']', $option['label'],$value);
    } else {
      echo '<p>Input Type For This Option Not Specific</p>';
    }
     if(isset($option['after'])) {
        echo $option['after'];
      }
  }
  ?>
</div>
<?php } ?>
</div>
