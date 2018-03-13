<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Stripe_ideal extends CRM_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function response($id, $hash)
    {
        $this->load->model('invoices_model');
        check_invoice_restrictions($id, $hash);

        $invoice             = $this->invoices_model->get($id);
        load_client_language($invoice->clientid);
        try {
            $source = $this->stripe_ideal_gateway->get_source($this->input->get('source'));

            if ($source->status == 'chargeable') {
                try {
                    $charge = $this->stripe_ideal_gateway->charge($source->id, $source->amount, $source->metadata->invoice_id);

                    if ($charge->status == 'succeeded') {
                        $charge->invoice_id = $source->metadata->invoice_id;
                        $success = $this->stripe_ideal_gateway->finish_payment($charge);

                        set_alert('success', $success ? _l('online_payment_recorded_success') : _l('online_payment_recorded_success_fail_database'));
                    } elseif ($charge->status == 'pending') {
                        set_alert('success', _l('payment_received_awaiting_confirmation'));
                    } else {
                        $errMsg = _l('invoice_payment_record_failed');
                        if ($charge->failure_message) {
                            $errMsg.= ' - ' . $charge->failure_message;
                        }
                        set_alert('warning', $errMsg);
                    }
                } catch (Exception $e) {
                    set_alert('warning', $e->getMessage());
                }
            } else {
                set_alert('warning', _l('invoice_payment_record_failed'));
            }
        } catch (Exception $e) {
            set_alert('warning', $e->getMessage());
        }

        redirect(site_url('viewinvoice/'.$id.'/'.$hash));
    }

    public function webhook($key)
    {
        $saved_key = $this->stripe_ideal_gateway->getSetting('webhook_key');

        if ($saved_key == $key) {

            $input = json_decode(file_get_contents("php://input"), true);
            $data = $input['data']['object'];

            $pcrm_gateway = isset($data['metadata']['pcrm-stripe-ideal']) ? $data['metadata']['pcrm-stripe-ideal'] : false;

            if ($pcrm_gateway == true && $data['type'] == "ideal") {
                if ($data['status'] == "chargeable") {
                    $invoice_id = intval($data['metadata']['invoice_id']);
                    $charge = $this->stripe_ideal_gateway->charge($data['id'], $data['amount'], $invoice_id);
                    if ($charge->status == 'succeeded') {
                        $this->stripe_ideal_gateway->finish_payment($charge);
                    }
                }
            }
        } else {
            header('HTTP/1.0 403 Not Authorized');
            echo 'Webhook key is not matching';
        }
    }
}
