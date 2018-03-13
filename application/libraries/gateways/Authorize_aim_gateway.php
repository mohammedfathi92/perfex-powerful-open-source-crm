<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Omnipay\Omnipay;

// require_once(APPPATH . 'third_party/omnipay/vendor/autoload.php');

class Authorize_aim_gateway extends App_gateway
{
    public function __construct()
    {
        /**
        * Call App_gateway __construct function
        */
        parent::__construct();
        /**
         * REQUIRED
         * Gateway unique id
         * The ID must be alpha/alphanumeric
         */
        $this->setId('authorize_aim');

        /**
         * REQUIRED
         * Gateway name
         */
        $this->setName('Authorize.net AIM');

        /**
         * Add gateway settings
        */
        $this->setSettings(array(
            array(
                'name' => 'api_login_id',
                'encrypted' => true,
                'label' => 'settings_paymentmethod_authorize_api_login_id'
            ),
            array(
                'name' => 'api_transaction_key',
                'label' => 'settings_paymentmethod_authorize_api_transaction_key',
                'encrypted' => true
            ),
            array(
                'name' => 'description_dashboard',
                'label' => 'settings_paymentmethod_description',
                'type'=>'textarea',
                'default_value'=>'Payment for Invoice {invoice_number}',
            ),
            array(
                'name' => 'currencies',
                'label' => 'currency',
                'default_value' => 'USD'
            ),
            array(
                'name' => 'test_mode_enabled',
                'type' => 'yes_no',
                'default_value' => 0,
                'label' => 'settings_paymentmethod_testing_mode'
            ),
            array(
                'name' => 'developer_mode_enabled',
                'type' => 'yes_no',
                'default_value' => 1,
                'label' => 'settings_paymentmethod_developer_mode'
            )
        ));

        /**
         * REQUIRED
         * Hook gateway with other online payment modes
         */
        add_action('before_add_online_payment_modes', array( $this, 'initMode' ));

        add_action('before_render_payment_gateway_settings', 'authorize_aim_notice');
    }

    public function process_payment($data)
    {
        $this->ci->session->set_userdata(array(
            'total_authorize' => $data['amount']
        ));

        redirect(site_url('gateways/authorize_aim/make_payment?invoiceid=' . $data['invoiceid'] . '&total=' . $data['amount'] . '&hash=' . $data['invoice']->hash));
    }

    public function finish_payment($data)
    {
        $gateway = Omnipay::create('AuthorizeNet_AIM');
        $gateway->setApiLoginId($this->decryptSetting('api_login_id'));
        $gateway->setTransactionKey($this->decryptSetting('api_transaction_key'));

        $gateway->setTestMode($this->getSetting('test_mode_enabled'));
        $gateway->setDeveloperMode($this->getSetting('developer_mode_enabled'));

        $billing_data = array();

        $billing_data['billingCompany']  = $data['invoice']->client->company;
        $billing_data['billingAddress1'] = $this->ci->input->post('billingAddress1');
        $billing_data['billingName']     = $this->ci->input->post('billingName');
        $billing_data['billingCity']     = $this->ci->input->post('billingCity');
        $billing_data['billingState']    = $this->ci->input->post('billingState');
        $billing_data['billingPostcode'] = $this->ci->input->post('billingPostcode');
        $billing_data['billingCountry']  = $this->ci->input->post('billingCountry');

        $billing_data['number']      = $this->ci->input->post('ccNo');
        $billing_data['expiryMonth'] = $this->ci->input->post('expMonth');
        $billing_data['expiryYear']  = $this->ci->input->post('expYear');
        $billing_data['cvv']         = $this->ci->input->post('cvv');

        $requestData = array(
            'amount' => number_format($data['amount'], 2, '.', ''),
            'currency' => $data['invoice']->currency_name,
            'description' => str_replace('{invoice_number}', format_invoice_number($data['invoice']->id) , $this->getSetting('description_dashboard')),
            'transactionId' => $data['invoice']->id,
            'invoiceNumber'=>format_invoice_number($data['invoice']->id),
            'card' => $billing_data
        );

        $oResponse = $gateway->purchase($requestData)->send();

        return $oResponse;
    }
}

function authorize_aim_notice($gateway)
{
    if ($gateway['id'] == 'authorize_aim') {
        echo '<p class="text-warning">' . _l('authorize_notice') . '</p>';
        echo '<p class="text-dark"><b>' . _l('currently_supported_currencies') . '</b>: USD, AUD, GBP, CAD, EUR, NZD</p>';
    }
}
