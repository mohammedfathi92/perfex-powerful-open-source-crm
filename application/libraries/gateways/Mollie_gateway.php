<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Omnipay\Omnipay;

require_once(APPPATH . 'third_party/omnipay/vendor/autoload.php');

class Mollie_gateway extends App_gateway
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
        $this->setId('mollie');

        /**
         * REQUIRED
         * Gateway name
         */
        $this->setName('Mollie');

        /**
         * Add gateway settings
        */
        $this->setSettings(array(
            array(
                'name' => 'api_key',
                'encrypted' => true,
                'label' => 'settings_paymentmethod_mollie_api_key'
            ),
            array(
                'name' => 'description_dashboard',
                'label' => 'settings_paymentmethod_description',
                'type'=>'textarea',
                'default_value'=>'Payment for Invoice'
            ),
            array(
                'name' => 'currencies',
                'label' => 'currency',
                'default_value' => 'EUR'
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
        $gateway = Omnipay::create('Mollie');
        $gateway->setApiKey($this->decryptSetting('api_key'));

        $oResponse = $gateway->purchase(array(
            'amount' => number_format($data['amount'], 2, '.', ''),
            'description' => $this->getSetting('description_dashboard') . ' - ' . format_invoice_number($data['invoice']->id),
            'returnUrl' => site_url('gateways/mollie/verify_payment?invoiceid=' . $data['invoice']->id . '&hash=' . $data['invoice']->hash),
            'notifyUrl' => site_url('gateways/mollie/webhook'),
            'metadata' => array(
                'order_id' => $data['invoice']->id
            )
        ))->send();

        // Add the token to database
        $this->ci->db->where('id', $data['invoiceid']);
        $this->ci->db->update('tblinvoices', array(
            'token' => $oResponse->getTransactionReference()
        ));

        if ($oResponse->isRedirect()) {
            $oResponse->redirect();
        } elseif ($oResponse->isPending()) {
            echo "Pending, Reference: " . $oResponse->getTransactionReference();
        } else {
            echo "<p class=\"text-danger\">Error " . $oResponse->getCode() . ': ' . $oResponse->getMessage().'</p>';
        }
    }

    public function fetch_payment($data)
    {
        $gateway = Omnipay::create('Mollie');
        $gateway->setApiKey($this->decryptSetting('api_key'));

        return $gateway->fetchTransaction(array(
            'transactionReference' => $data['transaction_id']
        ))->send();
    }
}
