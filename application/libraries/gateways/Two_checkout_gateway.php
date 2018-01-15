<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Omnipay\Omnipay;

require_once(APPPATH . 'third_party/omnipay/vendor/autoload.php');

class Two_checkout_gateway extends App_gateway
{
    private $required_address_line_2_country_codes = 'CHN, JPN, RUS';

    private $required_state_country_codes = ' ARG, AUS, BGR, CAN, CHN, CYP, EGY, FRA, IND, IDN, ITA, JPN, MYS, MEX, NLD, PAN, PHL, POL, ROU, RUS, SRB, SGP, ZAF, ESP, SWE, THA, TUR, GBR, USA';

    private $required_zip_code_country_codes = 'ARG, AUS, BGR, CAN, CHN, CYP, EGY, FRA, IND, IDN, ITA, JPN, MYS, MEX, NLD, PAN, PHL, POL, ROU, RUS, SRB, SGP, ZAF, ESP, SWE, THA, TUR, GBR, USA';

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
        $this->setId('two_checkout');

        /**
         * REQUIRED
         * Gateway name
         */
        $this->setName('2Checkout');

        /**
         * Add gateway settings
        */
        $this->setSettings(array(
            array(
                'name' => 'account_number',
                'label' => 'paymentmethod_two_checkout_account_number'
            ),
            array(
                'name' => 'private_key',
                'label' => 'paymentmethod_two_checkout_private_key',
                'encrypted' => true
            ),
            array(
                'name' => 'publishable_key',
                'label' => 'paymentmethod_two_checkout_publishable_key'
            ),
            array(
                'name' => 'currencies',
                'label' => 'settings_paymentmethod_currencies',
                'default_value' => 'USD,EUR'
            ),
            array(
                'name' => 'test_mode_enabled',
                'type' => 'yes_no',
                'label' => 'settings_paymentmethod_testing_mode',
                'default_value' => 1
            )
        ));

        /**
        * REQUIRED
        * Hook gateway with other online payment modes
        */
        add_action('before_add_online_payment_modes', array( $this, 'initMode' ));

        /**
         * Add ssl notice
         */
        add_action('before_render_payment_gateway_settings', 'two_checkout_ssl_notice');

        $line_address_2_required                     = $this->required_address_line_2_country_codes;
        $this->required_address_line_2_country_codes = array();
        foreach (explode(', ', $line_address_2_required) as $cn_code) {
            array_push($this->required_address_line_2_country_codes, $cn_code);
        }
        $state_country_codes_required       = $this->required_state_country_codes;
        $this->required_state_country_codes = array();
        foreach (explode(', ', $state_country_codes_required) as $cn_code) {
            array_push($this->required_state_country_codes, $cn_code);
        }
        $zip_code_country_codes_required       = $this->required_zip_code_country_codes;
        $this->required_zip_code_country_codes = array();
        foreach (explode(', ', $zip_code_country_codes_required) as $cn_code) {
            array_push($this->required_zip_code_country_codes, $cn_code);
        }
    }

    public function process_payment($data)
    {
        $this->ci->session->set_userdata(array(
            'total_2checkout' => $data['amount']
        ));
        redirect(site_url('gateways/two_checkout/make_payment?invoiceid=' . $data['invoiceid'] . '&hash=' . $data['invoice']->hash));
    }

    public function finish_payment($data)
    {
        $gateway = Omnipay::create('TwoCheckoutPlus_Token');
        $gateway->setAccountNumber($this->getSetting('account_number'));
        $gateway->setPrivateKey($this->decryptSetting('private_key'));
        $gateway->setTestMode($this->getSetting('test_mode_enabled'));

        $billing_data                    = array();
        $billing_data['billingName']     = $this->ci->input->post('billingName');
        $billing_data['billingAddress1'] = $this->ci->input->post('billingAddress1');

        if ($this->ci->input->post('billingAddress2')) {
            $billing_data['billingAddress2'] = $this->ci->input->post('billingAddress2');
        }
        $billing_data['billingCity'] = $this->ci->input->post('billingCity');

        if ($this->ci->input->post('billingState')) {
            $billing_data['billingState'] = $this->ci->input->post('billingState');
        }
        if ($this->ci->input->post('billingPostcode')) {
            $billing_data['billingPostcode'] = $this->ci->input->post('billingPostcode');
        }
        $billing_data['billingCountry'] = $this->ci->input->post('billingCountry');
        $billing_data['email']          = $this->ci->input->post('email');

        $oResponse = $gateway->purchase(array(
            'amount' => number_format($data['amount'], 2, '.', ''),
            'currency' => $data['currency'],
            'token' => $this->ci->input->post('token'),
            'transactionId' => $data['invoice']->id,
            'card' => $billing_data
        ))->send();

        return $oResponse;
    }

    public function get_required_address_2_by_country_code()
    {
        return $this->required_address_line_2_country_codes;
    }

    public function get_required_state_by_country_code()
    {
        return $this->required_state_country_codes;
    }

    public function get_required_zip_by_country_code()
    {
        return $this->required_zip_code_country_codes;
    }
}

function two_checkout_ssl_notice($gateway)
{
    if ($gateway['id'] == 'two_checkout') {
        echo '<p class="text-warning">' . _l('2checkout_usage_notice') . '</p>';
    }
}
