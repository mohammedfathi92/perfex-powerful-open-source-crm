<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Payu_money_gateway extends App_gateway
{
    protected $hash_sequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";

    protected $sandbox_url = 'https://test.payu.in/_payment';
    protected $production_url = 'https://secure.payu.in/_payment';

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
        $this->setId('payu_money');

        /**
         * REQUIRED
         * Gateway name
         */
        $this->setName('PayU Money');

        /**
         * Add gateway settings
        */
        $this->setSettings(array(
            array(
                'name' => 'key',
                'label' => 'payment_gateway_payu_money_key'
                ),
            array(
                'name' => 'salt',
                'label' => 'payment_gateway_payu_money_salt',
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
                'default_value' => 'INR'
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
        $this->ci->session->set_userdata(array('payu_money_total'=>number_format($data['amount'], 2, '.', '')));
        redirect(site_url('gateways/payu_money/make_payment?invoiceid=' . $data['invoiceid'] . '&hash=' . $data['invoice']->hash));
    }

    public function get_action_url()
    {
        return $this->getSetting('test_mode_enabled') == '1' ? $this->sandbox_url : $this->production_url;
    }

    public function gen_transaction_id()
    {
        return substr(hash('sha256', mt_rand() . microtime()), 0, 20);
    }

    public function get_hash($posted)
    {
        $hash_sequence = $this->hash_sequence;
        $hash_vars_seq = explode('|', $hash_sequence);
        $hash_string = '';
        foreach ($hash_vars_seq as $hash_var) {
            $hash_string .= isset($posted[$hash_var]) ? $posted[$hash_var] : '';
            $hash_string .= '|';
        }

        $hash_string .= $this->decryptSetting('salt');

        $hash   = strtolower(hash('sha512', $hash_string));

        return $hash;
    }

    public function get_valid_hash($posted)
    {
        $salt       = $this->decryptSetting('salt');

        $status       = $posted["status"];
        $unmappedstatus       = $posted["unmappedstatus"];

        $firstname    = $posted["firstname"];
        $amount       = $posted["amount"];
        $txnid        = $posted["txnid"];
        $posted_hash  = $posted["hash"];
        $key          = $posted["key"];
        $productinfo  = $posted["productinfo"];
        $email        = $posted["email"];

        $transaction_mode      = $posted["mode"];

        if (isset($posted["additionalCharges"])) {
            $additional_charges     = $posted["additionalCharges"];
            $retHashSeq             = $additional_charges.'|'. $salt.'|'.$status.'|||||||||||'.$email.'|'.$firstname.'|'.$productinfo.'|'.$amount.'|'.$txnid.'|'.$key;
        } else {
            $retHashSeq = $salt.'|'.$status.'|||||||||||'.$email.'|'.$firstname.'|'.$productinfo.'|'.$amount.'|'.$txnid.'|'.$key;
        }
        $hash = hash("sha512", $retHashSeq);

        if ($hash != $posted_hash) {
            return false;
        } else {
            return array(
                'status' => $status,
                'unmappedstatus'=>$unmappedstatus,
                'txnid' => $txnid,
                'amount' => $amount,
                'transaction_mode' => $transaction_mode,
                'error_Message'=>$posted['error_Message']
                );
        }
    }
}
