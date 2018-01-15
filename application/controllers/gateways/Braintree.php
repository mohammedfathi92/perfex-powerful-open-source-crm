<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Braintree extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function complete_purchase()
    {
        if ($this->input->post()) {
            $data      = $this->input->post();
            $total     = $this->input->post('amount');
            $this->load->model('invoices_model');
            $invoice             = $this->invoices_model->get($this->input->post('invoiceid'));
            check_invoice_restrictions($invoice->id, $invoice->hash);

            load_client_language($invoice->clientid);
            $data['amount']      = $total;
            $data['nonce']      =  $this->input->post('payment_method_nonce');
            $data['currency']    = $invoice->currency_name;
            $oResponse      = $this->paypal_braintree_gateway->finish_payment($data);
            if ($oResponse->isSuccessful()) {
                $transactionid  = $oResponse->getTransactionReference();
                $paymentResponse = $this->paypal_braintree_gateway->fetch_payment($transactionid);
                $paymentData      = $paymentResponse->getData();

                $success = $this->paypal_braintree_gateway->addPayment(
                    array(
                      'amount'=>$data['amount'],
                      'invoiceid'=>$invoice->id,
                      'paymentmethod'=>$paymentData->paymentInstrumentType,
                      'transactionid'=>$transactionid,
                 ));

                if ($success) {
                    set_alert('success', _l('online_payment_recorded_success'));
                } else {
                    set_alert('danger', _l('online_payment_recorded_success_fail_database'));
                }
                redirect(site_url('viewinvoice/' . $invoice->id . '/' . $invoice->hash));
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
        $data['total']        = $this->input->get('total');
        $data['client_token'] = $this->paypal_braintree_gateway->generate_token();
        echo $this->get_view($data);
    }

public function get_view($data = array()){ ?>
  <?php echo payment_gateway_head(_l('payment_for_invoice') . ' ' . format_invoice_number($data['invoice']->id)); ?>
  <body class="gateway-braintree">
    <div class="container">
      <div class="col-md-8 col-md-offset-2 mtop30">
        <div class="mbot30 text-center">
          <?php echo payment_gateway_logo(); ?>
        </div>
        <div class="row">
          <div class="panel_s">
            <div class="panel-body">
             <h4 class="no-margin">
              <?php echo _l('payment_for_invoice'); ?>
              <a href="<?php echo site_url('viewinvoice/'. $data['invoice']->id . '/' . $data['invoice']->hash); ?>">
              <?php echo format_invoice_number($data['invoice']->id); ?>
              </a>
            </h4>
            <hr />
            <p>
              <span class="bold">
                <?php echo _l('payment_total',format_money($data['total'],$data['invoice']->symbol)); ?>
              </span>
            </p>
            <form method="post" id="payment-form" action="<?php echo site_url('gateways/braintree/complete_purchase'); ?>">
              <section>
                <div class="bt-drop-in-wrapper">
                  <div id="bt-dropin"></div>
                </div>
                <input id="amount" name="amount" type="hidden" value="<?php echo number_format($data['total'], 2, '.', ''); ?>">
                <input type="hidden" name="invoiceid" value="<?php echo $data['invoice']->id; ?>">
              </section>
              <div class="text-center" style="margin-top:15px;">
                <button class="btn btn-info" type="submit"><?php echo _l('submit_payment'); ?></button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <script src="https://js.braintreegateway.com/js/braintree-2.30.0.min.js"></script>
    <script>
      braintree.setup('<?php echo $data['client_token']; ?>', 'dropin', {
        container: 'bt-dropin'
      });
    </script>
    <?php echo payment_gateway_footer(); ?>
  <?php }
}
