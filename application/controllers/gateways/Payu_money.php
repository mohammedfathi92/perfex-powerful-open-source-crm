<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Payu_money extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function make_payment()
    {
        check_invoice_restrictions($this->input->get('invoiceid'), $this->input->get('hash'));
        $this->load->model('invoices_model');
        $invoice      = $this->invoices_model->get($this->input->get('invoiceid'));

        load_client_language($invoice->clientid);

        $data['invoice'] = $invoice;
        $data['total'] = $this->session->userdata('payu_money_total');
        $data['key']            = $this->payu_money_gateway->getSetting('key');

        $posted = array();

        if ($this->input->post()) {
            $data['action_url'] = $this->payu_money_gateway->get_action_url();
            foreach ($this->input->post() as $key=>$value) {
                $posted[$key] = $value;
            }
            $data['txnid'] = $posted['txnid'];
            $data['firstname'] = $posted['firstname'];
            $data['lastname'] = $posted['lastname'];
            $data['email'] = $posted['email'];
            $data['phonenumber'] = $posted['phone'];
        } else {
            $data['txnid'] =  $this->payu_money_gateway->gen_transaction_id();
            $data['action_url'] = $this->uri->uri_string().'?invoiceid='.$invoice->id.'&hash='.$invoice->hash;

            $data['firstname'] = '';
            $data['lastname'] = '';
            $data['email'] = '';
            $data['phonenumber'] = '';

            if (is_client_logged_in()) {
              $contact = $this->clients_model->get_contact(get_contact_user_id());
            } else {
              if (total_rows('tblcontacts', array('userid'=>$invoice->clientid)) == 1) {
                $contact = $this->clients_model->get_contact(get_primary_contact_user_id($invoice->clientid));
              }
            }

            if (isset($contact) && $contact) {
              $data['firstname'] = $contact->firstname;
              $data['lastname'] = $contact->lastname;
              $data['email'] = $contact->email;
              $data['phonenumber'] = $contact->phonenumber;
            }
        }

        $data['hash']           = '';

        // there is post request
        if (count($posted) > 0) {
            $data['hash'] = $this->payu_money_gateway->get_hash(array(
                'key'=>$posted['key'],
                'txnid'=>$posted['txnid'],
                'amount'=>$posted['amount'],
                'productinfo'=>$posted['productinfo'],
                'firstname'=>$posted['firstname'],
                'email'=>$posted['email'],
                ));
        }

        echo $this->get_html($data);
    }

    public function get_html($data)
    {
       ob_start(); ?>
       <?php echo payment_gateway_head(_l('payment_for_invoice') . ' ' . format_invoice_number($data['invoice']->id)); ?>
           <body onload="submitPayuForm()" class="gateway-payu-money">
              <div class="container">
                 <div class="col-md-8 col-md-offset-2 mtop30">
                    <div class="mbot30 text-center">
                      <?php echo payment_gateway_logo(); ?>
                    </div>
                    <div class="row">
                       <div class="panel_s">
                          <div class="panel-body">
                             <h4 class="no-margin">
                                <?php echo _l('payment_for_invoice'); ?> <a href="<?php echo site_url('viewinvoice/'. $data['invoice']->id . '/' . $data['invoice']->hash); ?>">
                                    <?php echo format_invoice_number($data['invoice']->id); ?>
                                </a>
                             </h4>
                             <hr />
                             <h4 class="mbot20">
                                <?php echo _l('payment_total',format_money($data['total'],$data['invoice']->symbol)); ?>
                             </h4>

                            <?php echo form_open($data['action_url'],array('novalidate'=>true,'id'=>'payu_money_form')); ?>

                            <input type="hidden" name="key" value="<?php echo $data['key'] ?>" />
                            <input type="hidden" name="hash" value="<?php echo $data['hash'] ?>"/>
                            <input type="hidden" name="txnid" value="<?php echo $data['txnid'] ?>" />
                            <input type="hidden" name="amount" value="<?php echo $data['total'] ?>" />
                            <input type="hidden" name="surl" value="<?php echo site_url('gateways/payu_money/success?invoiceid='.$data['invoice']->id.'&hash='.$data['invoice']->hash); ?>" />
                            <input type="hidden" name="furl" value="<?php echo site_url('gateways/payu_money/failure?invoiceid='.$data['invoice']->id.'&hash='.$data['invoice']->hash); ?>" />
                            <input type="hidden" name="service_provider" value="payu_paisa" size="64" />
                            <input type="hidden" name="productinfo" value="<?php echo $this->payu_money_gateway->getSetting('description_dashboard') . ' - ' . format_invoice_number($data['invoice']->id); ?>" />

                            <div class="form-group">
                                <label for="first_name"> <?php echo _l('client_firstname'); ?></label>
                                <input type="text" class="form-control" id="first_name" name="firstname" value="<?php echo $data['firstname']; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="last_name"> <?php echo _l('client_lastname'); ?></label>
                                <input type="text" class="form-control" id="last_name" name="lastname" value="<?php echo $data['lastname']; ?>">
                            </div>

                            <div class="form-group">
                                <label for="email"> <?php echo _l('client_email'); ?> </label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo $data['email']; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="phone"> <?php echo _l('client_phonenumber'); ?></label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $data['phonenumber']; ?>" required>
                            </div>
                            <?php if(!$data['hash']){ ?>
                                 <input type="submit" class="btn btn-info" value="<?php echo _l('submit_payment'); ?>" />
                            <?php } ?>
                             </form>
                          </div>
                       </div>
                    </div>
                 </div>
              </div>
              <?php echo payment_gateway_scripts(); ?>
              <script>
               $(function(){
                    $('#payu_money_form').validate({submitHandler:function(form){
                      $('input[type="submit"]').prop('disabled',true);
                      return true;
                    }});
               });
                var hash = '<?php echo $data['hash']; ?>';
                function submitPayuForm() {
                  if(hash == '') {
                        return;
                  }
                  var payu_money_form = document.forms.payu_money_form;
                  payu_money_form.submit();
                }
              </script>
           <?php echo payment_gateway_footer(); ?>
        <?php
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }

    public function success()
    {
        $invoiceid             = $this->input->get('invoiceid');
        $hash                  = $this->input->get('hash');

        check_invoice_restrictions($invoiceid, $hash);
        $this->load->model('invoices_model');
        $invoice      = $this->invoices_model->get($this->input->get('invoiceid'));
        load_client_language($invoice->clientid);

        $hashInfo = $this->payu_money_gateway->get_valid_hash($_POST);
        if (!$hashInfo) {
            set_alert('warning', _l('invalid_transaction'));
        } else {
            if($hashInfo['status'] == 'success'){
                $success = $this->payu_money_gateway->addPayment(
                array(
                  'amount'=>$hashInfo['amount'],
                  'invoiceid'=>$invoiceid,
                  'transactionid'=>$hashInfo['txnid'],
                  'paymentmethod'=>$hashInfo['transaction_mode'],
                  )
                );
                if ($success) {
                    set_alert('success', _l('online_payment_recorded_success'));
                } else {
                    set_alert('danger', _l('online_payment_recorded_success_fail_database'));
                }
            } else {
               if($this->payu_money_gateway->getSetting('test_mode_enabled') == '1'){
                    logActivity('Payu Money Transaction Not With Status Success: ' . var_export($_POST,true));
               }
               set_alert('warning', 'Thank You. Your transaction status is ' .$hashInfo['status']);
            }
        }
        $this->session->unset_userdata('payu_money_total');
        redirect(site_url('viewinvoice/' . $invoiceid . '/' . $hash));
    }

    public function failure()
    {
        $invoiceid             = $this->input->get('invoiceid');
        $hash                  = $this->input->get('hash');

        check_invoice_restrictions($invoiceid, $hash);
        $this->load->model('invoices_model');
        $invoice      = $this->invoices_model->get($this->input->get('invoiceid'));
        load_client_language($invoice->clientid);

        $hashInfo = $this->payu_money_gateway->get_valid_hash($_POST);

        if (!$hashInfo) {
            set_alert('warning', _l('invalid_transaction'));
        } else {
            if($hashInfo['unmappedstatus'] != 'userCancelled'){
              set_alert('warning', $hashInfo['error_Message'] . ' - ' . $hashInfo['status']);
            }
        }

        $this->session->unset_userdata('payu_money_total');

        redirect(site_url('viewinvoice/' . $invoiceid . '/' . $hash));
    }
}
