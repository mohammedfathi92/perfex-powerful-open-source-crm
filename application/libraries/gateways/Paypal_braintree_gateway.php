<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Omnipay\Omnipay;

// require_once(APPPATH . 'third_party/omnipay/vendor/autoload.php');

class Paypal_braintree_gateway extends App_gateway
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
        $this->setId('paypal_braintree');

        /**
         * REQUIRED
         * Gateway name
         */
        $this->setName('Braintree');

        /**
         * Add gateway settings
        */
        $this->setSettings(array(
            array(
                'name' => 'merchant_id',
                'encrypted' => true,
                'label' => 'paymentmethod_braintree_merchant_id'
            ),
            array(
                'name' => 'api_public_key',
                'label' => 'paymentmethod_braintree_public_key'
            ),
            array(
                'name' => 'api_private_key',
                'encrypted' => true,
                'label' => 'paymentmethod_braintree_private_key'
            ),
            array(
                'name' => 'currencies',
                'label' => 'settings_paymentmethod_currencies',
                'default_value' => 'USD'
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
        redirect(site_url('gateways/braintree/make_payment?invoiceid=' . $data['invoiceid'] . '&total=' . $data['amount'] . '&hash=' . $data['invoice']->hash));
    }

    public function fetch_payment($transaction_id)
    {
        $gateway = Omnipay::create('Braintree');
        $gateway->setMerchantId($this->decryptSetting('merchant_id'));
        $gateway->setPrivateKey($this->decryptSetting('api_private_key'));
        $gateway->setPublicKey($this->getSetting('api_public_key'));
        $gateway->setTestMode($this->getSetting('test_mode_enabled'));

        return $gateway->find(array('transactionReference'=>$transaction_id))->send();
    }

    public function generate_token()
    {
        $gateway = Omnipay::create('Braintree');
        $gateway->setMerchantId($this->decryptSetting('merchant_id'));
        $gateway->setPrivateKey($this->decryptSetting('api_private_key'));
        $gateway->setPublicKey($this->getSetting('api_public_key'));
        $gateway->setTestMode($this->getSetting('test_mode_enabled'));

        return $gateway->clientToken()->send()->getToken();
    }

    public function finish_payment($data)
    {
        // Process online for PayPal payment start
        $gateway = Omnipay::create('Braintree');
        $gateway->setMerchantId($this->decryptSetting('merchant_id'));
        $gateway->setPrivateKey($this->decryptSetting('api_private_key'));
        $gateway->setPublicKey($this->getSetting('api_public_key'));
        $gateway->setTestMode($this->getSetting('test_mode_enabled'));

        $response = $gateway->purchase(array(
            'amount' => number_format($data['amount'], 2, '.', ''),
            'currency' => $data['currency'],
            'token' => $data['nonce'],
            ))->send();

        return $response;
    }
}
