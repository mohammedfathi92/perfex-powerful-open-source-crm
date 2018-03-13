<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Stripe extends CRM_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function complete_purchase()
    {
        if ($this->input->post()) {
            $data      = $this->input->post();
            $total     = $this->input->post('total');
            $this->load->model('invoices_model');
            $invoice             = $this->invoices_model->get($this->input->post('invoiceid'));
            check_invoice_restrictions($invoice->id, $invoice->hash);
            load_client_language($invoice->clientid);

            $data['amount']      = $total;
            $data['description'] = str_replace('{invoice_number}', format_invoice_number($invoice->id) , $this->stripe_gateway->getSetting('description_dashboard'));

            $data['currency']    = $invoice->currency_name;
            $data['clientid']    = $invoice->clientid;
            $oResponse      = $this->stripe_gateway->finish_payment($data);
            if ($oResponse->isSuccessful()) {
                $transactionid  = $oResponse->getTransactionReference();
                $oResponse = $oResponse->getData();
                if ($oResponse['status'] == 'succeeded') {

                    $success = $this->stripe_gateway->addPayment(
                    array(
                      'amount'=>($oResponse['amount'] / 100),
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
        if(is_client_logged_in()){
            $data['contact'] = $this->clients_model->get_contact(get_contact_user_id());
        }
        $data['total']        = $this->input->get('total');
        echo $this->get_view($data);
    }

    public function get_view($data = array()){ ?>
        <?php echo payment_gateway_head(_l('payment_for_invoice') . ' ' . format_invoice_number($data['invoice']->id)); ?>
        <body class="gateway-stripe">
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
                              <?php
                              $form = '<form action="' . site_url('gateways/stripe/complete_purchase') . '" method="POST">
                                <script
                                src="https://checkout.stripe.com/checkout.js" class="stripe-button"
                                data-key="' . $this->stripe_gateway->getSetting('api_publishable_key') . '"
                                data-amount="' . ($data['total'] * 100). '"
                                data-name="' . get_option('companyname') . '"
                                data-description=" '. _l('payment_for_invoice') . ' ' . format_invoice_number($data['invoice']->id) . '";
                                data-locale="auto"
                                '.(is_client_logged_in() ? 'data-email="'.$data['contact']->email.'"' : '').'
                                '.($this->stripe_gateway->getSetting('bitcoin_enabled') == 1 ? 'data-bitcoin="true"': '').'
                                data-currency="'.$data['invoice']->currency_name.'"
                                >
                            </script>
                            '.form_hidden('invoiceid',$data['invoice']->id).'
                            '.form_hidden('total',$data['total']).'
                        </form>';
                        echo $form;
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php echo payment_gateway_scripts(); ?>
        <script>
            $(function(){
                $('.stripe-button-el').click();
            });
        </script>
        <?php echo payment_gateway_footer(); ?>
    <?php
    }
}
