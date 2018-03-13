<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Omnipay\Omnipay;

// require_once(APPPATH . 'third_party/omnipay/vendor/autoload.php');

class Stripe_gateway extends App_gateway
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
        $this->setId('stripe');

        /**
         * REQUIRED
         * Gateway name
         */
        $this->setName('Stripe Checkout');

        /**
         * Add gateway settings
        */
        $this->setSettings(array(
            array(
                'name' => 'api_secret_key',
                'encrypted' => true,
                'label' => 'settings_paymentmethod_stripe_api_secret_key'
            ),
            array(
                'name' => 'api_publishable_key',
                'label' => 'settings_paymentmethod_stripe_api_publishable_key'
            ),
            array(
                'name' => 'description_dashboard',
                'label' => 'settings_paymentmethod_description',
                'type'=>'textarea',
                'default_value'=>'Payment for Invoice {invoice_number}',
            ),
            array(
                'name' => 'currencies',
                'label' => 'settings_paymentmethod_currencies',
                'default_value' => 'USD,CAD'
            ),
            array(
                'name' => 'bitcoin_enabled',
                'type' => 'yes_no',
                'default_value' => 0,
                'label' => 'Bitcoin'
            ),
            array(
                'name' => 'test_mode_enabled',
                'type' => 'yes_no',
                'default_value' => 1,
                'label' => 'settings_paymentmethod_testing_mode'
            )
        ));

        /**
         * REQUIRED
         * Hook gateway with other online payment modes
         */
        add_action('before_add_online_payment_modes', array( $this, 'initMode' ));


    }

    public function process_payment($data)
    {
        redirect(site_url('gateways/stripe/make_payment?invoiceid=' . $data['invoiceid'] . '&total=' . $data['amount'] . '&hash=' . $data['invoice']->hash));
    }

    public function finish_payment($data)
    {
        // Process online for PayPal payment start
        $gateway = Omnipay::create('Stripe');
        $gateway->setApiKey($this->decryptSetting('api_secret_key'));
        $oResponse = $gateway->purchase(array(
            'amount' => number_format($data['amount'], 2, '.', ''),
            'metadata' => array(
                'ClientID' => $data['clientid']
            ),
            'description' => $data['description'],
            'currency' => $data['currency'],
            'token' => $data['stripeToken']
        ))->send();

        return $oResponse;
    }
}
