<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Authorize_aim extends CRM_Controller
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

            try {
                $oResponse      = $this->authorize_aim_gateway->finish_payment($data);
            } catch (Exception $e) {
                $message = $e->getMessage();
                set_alert('danger', (string) $message);
                redirect(site_url('viewinvoice/' . $invoice->id . '/' . $invoice->hash));
            }

            $oResponseData = $oResponse->getData();

            if (isset($oResponseData->messages->resultCode) && $oResponseData->messages->resultCode == 'Error') {
                $message = $oResponseData->messages->message->text;
                set_alert('danger', (string) $message);
                redirect(site_url('viewinvoice/' . $invoice->id . '/' . $invoice->hash));
            }

            if ($oResponse->isSuccessful()) {
                if ($oResponseData->transactionResponse->responseCode == '1') {
                    $success = $this->authorize_aim_gateway->addPayment(
                    array(
                      'amount'=>$data['amount'],
                      'invoiceid'=>$invoice->id,
                      'transactionid'=>$oResponseData->transactionResponse->transId,
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
        $billing_country = get_country($invoice->billing_country);
        $data['total']        = $this->session->userdata('total_authorize');
        $data['billing_name'] = '';
        if (is_client_logged_in()) {
            $contact = $this->clients_model->get_contact(get_contact_user_id());
            $data['billing_name'] = $contact->firstname . ' '  . $contact->lastname;
        } else {
            if (total_rows('tblcontacts', array('userid'=>$invoice->clientid)) == 1) {
                $contact = $this->clients_model->get_contact(get_primary_contact_user_id($invoice->clientid));
                if ($contact) {
                    $data['billing_name'] = $contact->firstname . ' '  . $contact->lastname;
                }
            }
        }
        echo $this->get_html($data);
    }

    public function get_html($data = array()){
        ob_start(); ?>
        <?php echo payment_gateway_head(_l('payment_for_invoice') . ' ' . format_invoice_number($data['invoice']->id)); ?>
           <body class="gateway-authorize-aim">
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
                             <h4 class="mbot20"><?php echo _l('payment_total',format_money($data['total'],$data['invoice']->symbol)); ?></h4>
                             <?php echo form_open(site_url('gateways/authorize_aim/complete_purchase'),array('novalidate'=>true,'id'=>'authorize_form')); ?>
                             <?php echo form_hidden('invoiceid',$data['invoice']->id); ?>
                             <?php echo form_hidden('total',$data['total']); ?>
                             <div>
                                <div class="form-group mtop15">
                                   <label class="control-label">
                                   <?php echo _l('payment_credit_card_number'); ?>
                                   </label>
                                   <input class="form-control" name="ccNo" id="ccNo" type="text" autocomplete="off" required />
                                </div>
                             </div>
                             <div>
                               <div class="form-group">
                                <label class="control-label" for="expMonth">
                                 <?php echo _l('card_expiration_month'); ?> (MM)
                               </label>
                               <input class="form-control" name="expMonth" id="expMonth" type="number" maxlength="2" required />
                             </div>
                             <div class="form-group">
                              <label class="control-label" for="expYear">
                               <?php echo _l('card_expiration_year'); ?> (YYYY)
                             </label>
                             <input class="form-control" name="expYear" id="expYear" type="number" maxlength="4" required />
                           </div>
                             </div>
                             <div>
                                <div class="form-group mtop15">
                                   <label class="control-label">
                                   CVC
                                   </label>
                                   <input class="form-control" name="cvv" id="cvv" type="text" autocomplete="off" required />
                                </div>
                                <hr />
                                <h4><?php echo _l('billing_address'); ?></h4>
                                <div class="form-group mtop15">
                                   <label class="control-label">
                                   <?php echo _l('payment_cardholder_name'); ?>
                                   </label>
                                   <input type="text" name="billingName" class="form-control" value="<?php echo $data['billing_name']; ?>" required>
                                </div>
                                <div class="row">
                                   <div class="col-md-12">
                                      <div class="form-group">
                                         <label class="control-label">
                                         <?php echo _l('billing_address'); ?>
                                         </label>
                                         <input type="text" name="billingAddress1" class="form-control" required value="<?php echo $data['invoice']->billing_street; ?>">
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
                                         <input type="text" name="billingState" class="form-control" value="<?php echo $data['invoice']->billing_state; ?>">
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
                                         <input type="text" name="billingPostcode" class="form-control" value="<?php echo $data['invoice']->billing_zip; ?>">
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
              <?php echo payment_gateway_scripts(); ?>
              <script>
                 $(function(){
                    $('#authorize_form').validate();
                 });
              </script>
              <?php echo payment_gateway_footer(); ?>
        <?php
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
      }
}
