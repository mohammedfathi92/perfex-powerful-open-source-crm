<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Stripe_ideal_gateway extends App_gateway
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
        $this->setId('stripe_ideal');

        /**
         * REQUIRED
         * Gateway name
         */
        $this->setName('Stripe iDEAL');

        /**
         * Add gateway settings
        */
        $this->setSettings(array(
            array(
                'name' => 'api_secret_key',
                'encrypted' => true,
                'label' => 'settings_paymentmethod_stripe_api_secret_key',
            ),
            array(
                'name' => 'api_publishable_key',
                'label' => 'settings_paymentmethod_stripe_api_publishable_key',
            ),
            array(
                'name' => 'description_dashboard',
                'label' => 'settings_paymentmethod_description',
                'type'=>'textarea',
                'default_value'=>'Payment for Invoice {invoice_number}'
            ),
            array(
                'name' => 'statement_descriptor',
                'label' => 'ideal_customer_statement_descriptor',
                'type'=>'textarea',
                'default_value'=>'Payment for Invoice {invoice_number}',
            ),
            array(
                'name' => 'webhook_key',
                'label' => 'Stripe Webhook Key',
                'default_value'=>app_generate_hash(),
                'after'=>'<p class="mbot15">Secret key to protect your webhook, webhook URL: ' . site_url('gateways/stripe_ideal/YOUR_WEBHOOK_KEY</p>'),
                'field_attributes'=>array('required'=>true),
            ),
            array(
                'name' => 'currencies',
                'label' => 'settings_paymentmethod_currencies',
                'default_value' => 'EUR',
                'field_attributes'=>array('disabled'=>true),
            ),
            array(
                'name' => 'test_mode_enabled',
                'type' => 'yes_no',
                'default_value' => 1,
                'label' => 'settings_paymentmethod_testing_mode',
            ),
        ));

        /**
         * REQUIRED
         * Hook gateway with other online payment modes
         */
        add_action('before_add_online_payment_modes', array( $this, 'initMode' ));
    }

    public function get_source($source)
    {
        $stripe = \Stripe\Stripe::setApiKey($this->decryptSetting('api_secret_key'));
        $source = \Stripe\Source::retrieve($source);

        return $source;
    }

    public function charge($source, $amount, $invoice_id)
    {
        $stripe = \Stripe\Stripe::setApiKey($this->decryptSetting('api_secret_key'));
        $charge = \Stripe\Charge::create(array(
            'currency'=>'eur',
            'amount'=>$amount,
            'source'=>$source,
            'description' => str_replace('{invoice_number}', format_invoice_number($invoice_id) , $this->getSetting('description_dashboard')),
            'metadata' => array(
                'invoice_id' => $invoice_id,
                'pcrm-stripe-ideal' => true,
            ),
        ));

        return $charge;
    }

    public function finish_payment($charge)
    {
        $success = $this->addPayment(
            array(
                      'amount'=>($charge->amount / 100),
                      'invoiceid'=>$charge->metadata->invoice_id,
                      'transactionid'=>$charge->id,
                      'paymentmethod'=>strtoupper($charge->source->ideal->bank),
                      )
         );

        return $success ? true : false;
    }

    public function process_payment($data)
    {
        $stripe = \Stripe\Stripe::setApiKey($this->decryptSetting('api_secret_key'));
        $name = $data['invoice']->client->company;
        // Address information
        $country = '';

        $db_country = get_country_short_name($data['invoice']->billing_country);
        if ($db_country != '') {
            $country = $db_country;
        }

        $city = $data['invoice']->billing_city;
        $line1 = $data['invoice']->billing_street;
        $postal_code = $data['invoice']->billing_zip;
        $state = $data['invoice']->billing_state;

        $address = array(
            'city' => "$city",
            'country' => "$country",
            'line1' => "$line1",
            'postal_code' => "$postal_code",
            'state' => "$state",
        );

        $stripe_data = array(
            'type' => 'ideal',
            'amount' => $data['amount'] * 100,
            'currency' => 'eur',

            'owner' => array(
                'name'=>$name,
                'address' => $address,
            ),

            'redirect' => array(
                'return_url' => site_url('gateways/stripe_ideal/response/'.$data['invoice']->id.'/'.$data['invoice']->hash),
            ),

            'statement_descriptor' => str_replace('{invoice_number}', format_invoice_number($data['invoice']->id) , $this->getSetting('statement_descriptor')),

            'metadata' => array(
                'invoice_id' => $data['invoice']->id,
                'pcrm-stripe-ideal' => true,
            ),
        );

        try {
            $source = \Stripe\Source::create($stripe_data);
            if ($source->created != "") {
                redirect($source->redirect->url);
            } else {
                if (!empty($source->failure_reason)) {
                    set_alert('warning', $source->failure_reason);
                }
            }
        } catch (Exception $e) {
            set_alert('warning', $e->getMessage());
        }

        redirect(site_url('viewinvoice/'.$data['invoice']->id.'/'.$data['invoice']->hash));
    }
}
