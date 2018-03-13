<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Omnipay\Omnipay;

// require_once(APPPATH . 'third_party/omnipay/vendor/autoload.php');

class Authorize_sim_gateway extends App_gateway
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
        $this->setId('authorize_sim');

        /**
         * REQUIRED
         * Gateway name
         */
        $this->setName('Authorize.net SIM');

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
                'name' => 'api_secret_key',
                'label' => 'settings_paymentmethod_authorize_secret_key',
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
        add_action('before_render_payment_gateway_settings', 'authorize_sim_notice');
    }

    public function process_payment($data)
    {
        $gateway = Omnipay::create('AuthorizeNet_SIM');
        $gateway->setApiLoginId($this->decryptSetting('api_login_id'));
        $gateway->setTransactionKey($this->decryptSetting('api_transaction_key'));
        $gateway->setHashSecret($this->decryptSetting('api_secret_key'));
        $gateway->setTestMode($this->getSetting('test_mode_enabled'));
        $gateway->setDeveloperMode($this->getSetting('developer_mode_enabled'));

        $billing_data['billingCompany']  = $data['invoice']->client->company;
        $billing_data['billingAddress1'] = $data['invoice']->billing_street;
        $billing_data['billingName']     = '';
        $billing_data['billingCity']     = $data['invoice']->billing_city;
        $billing_data['billingState']    = $data['invoice']->billing_state;
        $billing_data['billingPostcode'] = $data['invoice']->billing_zip;

        $_country = '';
        $country = get_country($data['invoice']->billing_country);

        if ($country) {
            $_country = $country->short_name;
        }

        $billing_data['billingCountry']  = $_country;
        $trans_id = time();

        $requestData = array(
                'amount' => number_format($data['amount'], 2, '.', ''),
                'currency' => $data['invoice']->currency_name,
                'returnUrl'=>site_url('gateways/authorize_sim/complete_purchase'),
                'description' => str_replace('{invoice_number}', format_invoice_number($data['invoice']->id) , $this->getSetting('description_dashboard')),
                'transactionId' => $trans_id,
                'invoiceNumber'=>format_invoice_number($data['invoice']->id),
                'card' => $billing_data
            );


        $oResponse = $gateway->purchase($requestData)->send();
        if ($oResponse->isRedirect()) {
            $this->ci->db->where('id', $data['invoice']->id);
            $this->ci->db->update('tblinvoices', array('token'=>$trans_id));
            // redirect to offsite payment gateway
            $oResponse->redirect();
        } else {
            // payment failed: display message to customer
            echo $oResponse->getMessage();
        }
    }
}
function authorize_sim_notice($gateway)
{
    if ($gateway['id'] == 'authorize_sim') {
        echo '<p class="text-dark"><b>' . _l('currently_supported_currencies') . '</b>: USD, AUD, GBP, CAD, EUR, NZD</p>';
    }
}
