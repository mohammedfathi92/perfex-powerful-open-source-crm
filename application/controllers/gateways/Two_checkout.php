<?php
defined('BASEPATH') or exit('No direct script access allowed');

// require_once(APPPATH . 'third_party/omnipay/vendor/autoload.php');

class Two_checkout extends CRM_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function complete_purchase()
    {
        if ($this->input->post()) {
            $data      = $this->input->post();
            $this->load->model('invoices_model');
            $invoice    = $this->invoices_model->get($this->input->post('invoiceid'));
            check_invoice_restrictions($invoice->id, $invoice->hash);
            load_client_language($invoice->clientid);
            $data['amount']      = $this->input->post('total');
            $data['currency']    = $invoice->currency_name;
            $data['invoice']    = $invoice;
            $oResponse      = $this->two_checkout_gateway->finish_payment($data);
            if ($oResponse->isSuccessful()) {
                $transactionid  = $oResponse->getTransactionReference();
                $oResponse = $oResponse->getData();
                if ($oResponse['response']['responseCode'] == 'APPROVED') {

                    $success = $this->two_checkout_gateway->addPayment(
                    array(
                      'amount'=>$oResponse['response']['total'],
                      'invoiceid'=>$invoice->id,
                      'transactionid'=>$transactionid,
                      ));

                    if ($success) {
                        set_alert('success', _l('online_payment_recorded_success'));
                    } else {
                        set_alert('danger', _l('online_payment_recorded_success_fail_database'));
                    }

                    redirect(site_url('viewinvoice/' . $invoice->id . '/' . $invoice->hash));
                }
            } elseif ($oResponse->isRedirect()) {
                $oResponse->redirect();
            } else {
                set_alert('danger', $oResponse->getMessage());
                redirect(site_url('viewinvoice/' . $invoice->id . '/' . $invoice->hash));
            }
        }
    }

    public function make_payment()
    {
        check_invoice_restrictions($this->input->get('invoiceid'), $this->input->get('hash'));
        $this->load->model('invoices_model');
        $invoice      = $this->invoices_model->get($this->input->get('invoiceid'));
        load_client_language($invoice->clientid);

        $data['invoice']      = $invoice;
        $data['address_2_required'] = false;
        $data['state_required'] = false;
        $data['zip_code_required'] = false;
        $billing_country = get_country($invoice->billing_country);

        if ($billing_country) {
            if (in_array($billing_country->iso3, $this->two_checkout_gateway->get_required_address_2_by_country_code())) {
                $data['address_2_required'] = true;
            }
            if (in_array($billing_country->iso3, $this->two_checkout_gateway->get_required_state_by_country_code())) {
                $data['state_required'] = true;
            }
            if (in_array($billing_country->iso3, $this->two_checkout_gateway->get_required_zip_by_country_code())) {
                $data['zip_code_required'] = true;
            }
        }
        $data['total']        = $this->session->userdata('total_2checkout');
        $data['billing_email'] = '';
        if (is_client_logged_in()) {
            $contact = $this->clients_model->get_contact(get_contact_user_id());
            $data['billing_email'] = $contact->email;
        } else {
            $contact = $this->clients_model->get_contact(get_primary_contact_user_id($invoice->clientid));
            if ($contact) {
                $data['billing_email'] = $contact->email;
            }
        }
        echo $this->get_html($data);
    }

    public function get_html($data = array()){ ?>
       <?php echo payment_gateway_head(_l('payment_for_invoice') . ' ' . format_invoice_number($data['invoice']->id)); ?>
       <body class="gateway-2checkout">
          <div class="container">
             <div class="col-md-8 col-md-offset-2 mtop30">
                <div class="mbot30 text-center">
                      <?php echo payment_gateway_logo(); ?>
                </div>
                <div class="row">
                   <div class="panel_s">
                      <div class="panel-body">
                         <h4 class="no-margin">
                            <?php echo _l('payment_for_invoice'); ?> <a href="<?php echo site_url('viewinvoice/'. $data['invoice']->id . '/' . $data['invoice']->hash); ?>"><?php echo format_invoice_number($data['invoice']->id); ?></a>
                         </h4>
                         <hr />
                         <p class="text-info"><?php echo _l('2checkout_notice_payment'); ?></p>
                         <p><span class="bold"><?php echo _l('payment_total',format_money($data['total'],$data['invoice']->symbol)); ?></span></p>
                         <?php echo form_open(site_url('gateways/two_checkout/complete_purchase'),array('id'=>'2checkout_form','novalidate'=>true)); ?>
                         <?php echo form_hidden('invoiceid',$data['invoice']->id); ?>
                         <?php echo form_hidden('total',$data['total']); ?>
                         <input name="token" type="hidden" value="" />
                         <div>
                            <div class="form-group">
                               <label class="control-label">
                               <?php echo _l('payment_credit_card_number'); ?>
                               </label>
                               <input class="form-control" id="ccNo" type="text" autocomplete="off" required />
                            </div>
                         </div>
                         <div>
                            <div class="row">
                               <div class="form-group">
                                  <div class="col-md-12">
                                     <label class="control-label">
                                     <?php echo _l('payment_credit_card_expiration_date'); ?> (MM/YYYY)
                                     </label>
                                  </div>
                                  <div class="col-md-6">
                                     <input class="form-control" id="expMonth" type="number" maxlength="2" required />
                                  </div>
                                  <div class="col-md-6">
                                     <input class="form-control" id="expYear" type="number" maxlength="4" required />
                                  </div>
                               </div>
                            </div>
                         </div>
                         <div>
                            <div class="form-group mtop15">
                               <label class="control-label">
                               CVC
                               </label>
                               <input class="form-control" id="cvv" type="text" autocomplete="off" required />
                            </div>
                            <p class="bold"><?php echo _l('billing_address'); ?></p>
                            <div class="form-group">
                               <label class="control-label">
                               <?php echo _l('payment_billing_email'); ?>
                               </label>
                               <input type="email" name="email" class="form-control" required value="<?php echo $data['billing_email']; ?>">
                            </div>
                            <div class="form-group">
                               <label class="control-label">
                               <?php echo _l('payment_cardholder_name'); ?>
                               </label>
                               <input type="text" name="billingName" class="form-control" required>
                            </div>
                            <div class="row">
                               <div class="col-md-6">
                                  <div class="form-group">
                                     <label class="control-label">
                                     <?php echo _l('billing_address'); ?>
                                     </label>
                                     <input type="text" name="billingAddress1" class="form-control" required value="<?php echo $data['invoice']->billing_street; ?>">
                                  </div>
                               </div>
                               <div class="col-md-6">
                                  <div class="form-group">
                                     <label class="control-label">
                                     <?php echo _l('billing_address'); ?> 2
                                     </label>
                                     <input type="text" name="billingAddress2" class="form-control" <?php if($data['address_2_required'] == true){echo 'required';} ?>>
                                  </div>
                               </div>
                               <div class="clearfix"></div>
                               <div class="col-md-6">
                                  <div class="form-group">
                                     <label class="control-label">
                                     <?php echo _l('billing_city'); ?>
                                     </label>
                                     <input type="text" name="billingCity" class="form-control" required value="<?php echo $data['invoice']->billing_city; ?>">
                                  </div>
                               </div>
                               <div class="col-md-6">
                                  <div class="form-group">
                                     <label class="control-label">
                                     <?php echo _l('billing_state'); ?>
                                     </label>
                                     <input type="text" name="billingState" class="form-control" <?php if($data['state_required'] == true){echo 'required';} ?> value="<?php echo $data['invoice']->billing_state; ?>">
                                  </div>
                               </div>
                               <div class="clearfix"></div>
                               <div class="col-md-6">
                                  <div class="form-group">
                                     <label class="control-label">
                                     <?php echo _l('billing_country'); ?>
                                     </label>
                                     <select name="billingCountry" class="form-control" required>
                                        <option value=""></option>
                                        <?php foreach(get_all_countries() as $country){
                                           $selected = '';
                                           if($data['invoice']->billing_country == $country['country_id']){
                                             $selected = 'selected';
                                           }
                                           echo '<option '.$selected.' value="'.$country['iso3'].'">'.$country['short_name'].'</option>';
                                           }
                                           ?>
                                     </select>
                                  </div>
                               </div>
                               <div class="col-md-6">
                                  <div class="form-group">
                                     <label class="control-label">
                                     <?php echo _l('billing_zip'); ?>
                                     </label>
                                     <input type="text" name="billingPostcode" class="form-control" <?php if($data['zip_code_required'] == true){echo 'required';} ?> value="<?php echo $data['invoice']->billing_zip; ?>">
                                  </div>
                               </div>
                            </div>
                         </div>
                         <input type="submit" class="btn btn-info" value="<?php echo _l('submit_payment'); ?>" />
                         </form>
                      </div>
                   </div>
                </div>
             </div>
          </div>
          <script type="text/javascript" src="https://www.2checkout.com/checkout/api/2co.min.js"></script>
          <?php echo payment_gateway_scripts(); ?>
          <script>
             var required_zip_by_country_code = <?php echo json_encode($this->two_checkout_gateway->get_required_zip_by_country_code()); ?>;
             var required_state_by_country_code = <?php echo json_encode($this->two_checkout_gateway->get_required_state_by_country_code()); ?>;
             var required_address2_by_country_code = <?php echo json_encode($this->two_checkout_gateway->get_required_address_2_by_country_code()); ?>;
             $.validator.setDefaults({
               errorElement: 'span',
               errorClass: 'text-danger',
             });
             $(function(){
               $('#2checkout_form').validate();
               $('select[name="billingCountry"]').on('change',function(){
                  var iso3 = $(this).val();
                   if($.inArray(iso3,required_zip_by_country_code) > -1){
                       $('input[name="billingPostcode"]').rules('add','required');
                   } else {
                       $('input[name="billingPostcode"]').rules('add',{required:false});
                   }
                   if($.inArray(iso3,required_state_by_country_code) > -1){
                       $('input[name="billingState"]').rules('add','required');
                   } else {
                       $('input[name="billingState"]').rules('add',{required:false});
                   }
                   if($.inArray(iso3,required_address2_by_country_code) > -1){
                       $('input[name="billingAddress2"]').rules('add','required');
                   } else {
                       $('input[name="billingAddress2"]').rules('add',{required:false});
                   }
             });
             });
               // Called when token created successfully.
             var successCallback = function(data) {
                 var myForm = document.getElementById('2checkout_form');
                   // Set the token as the value for the token input
                 myForm.token.value = data.response.token.token;
                   // IMPORTANT: Here we call `submit()` on the form element directly instead of using jQuery to prevent and infinite token request loop.
                  $('#2checkout_form').find('input[type="submit"]').addClass('disabled');
                 myForm.submit();
             };
               // Called when token creation fails.
             var errorCallback = function(data) {
                // Retry the token request if ajax call fails
                if (data.errorCode === 200) {
                     tokenRequest();
                     // This error code indicates that the ajax call failed. We recommend that you retry the token request.
                 } else {
                   alert(data.errorMsg);
                 }
             };
             var tokenRequest = function() {
                 // Setup token request arguments
                 var args = {
                     sellerId: "<?php echo $this->two_checkout_gateway->getSetting('account_number'); ?>",
                     publishableKey: "<?php echo $this->two_checkout_gateway->getSetting('publishable_key'); ?>",
                     ccNo: $("#ccNo").val(),
                     cvv: $("#cvv").val(),
                     expMonth: $("#expMonth").val(),
                     expYear: $("#expYear").val()
                 };
                 // Make the token request
                 TCO.requestToken(successCallback, errorCallback, args);
             };
             $(function() {
               TCO.loadPubKey('<?php echo ($this->two_checkout_gateway->getSetting('test_mode_enabled') == 1 ? 'sandbox' : 'production'); ?>');
                 $("#2checkout_form").submit(function(e) {
                     if($("#2checkout_form").valid() == false){return;}
                     // Call our token request function
                     tokenRequest();
                     // Prevent form from submitting
                     return false;
                 });
             });
          </script>
        <?php echo payment_gateway_footer(); ?>
      <?php }
}
