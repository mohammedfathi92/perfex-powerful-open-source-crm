<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Authorize_sim extends CRM_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function complete_purchase()
    {
        if ($this->input->post()) {
            $data = $this->input->post();

            $this->db->where('token', $data['omnipay_transaction_id']);
            $invoice = $this->db->get('tblinvoices')->row();
            $success = false;
            if ($invoice) {
                check_invoice_restrictions($invoice->id, $invoice->hash);
                load_client_language($invoice->clientid);
                if ($data['x_response_code'] == '1') {

                    $success = $this->authorize_sim_gateway->addPayment(
                    array(
                      'amount'=>$data['x_amount'],
                      'invoiceid'=>$invoice->id,
                      'transactionid'=>$data['x_trans_id']
                    ));

                    if ($success) {
                        $message = _l('online_payment_recorded_success');
                        $success = true;
                    } else {
                        $message = _l('online_payment_recorded_success_fail_database');
                        $success = true;
                    }
                } else {
                    $message = $data['x_response_reason_text'];
                }

                $this->db->where('id', $invoice->id);
                $this->db->update('tblinvoices', array(
                    'token' => ''
                ));
            } else {
                $success = false;
                $message = 'Invoice not found';
            }
            $this->receipt($success, $invoice, $message, $data);
        }
    }

    private function receipt($success, $invoice, $message, $data)
    {
        echo '<div style="width:600px;margin:0 auto;display:block; text-center">';
        if ($success) {
            $message_styling = 'color:#84c529';
        } else {
            $message_styling = 'color:#ff6f00';
        }
        echo '<h1 style="' . $message_styling . '">' . $message . '</h1>';
        do_action('after_authorize_sim_receipt_is_shown', array(
            'success' => $success,
            'invoice' => $invoice,
            'message' => $message
        ));
        if ($invoice) {
            echo '<a href="' . site_url('viewinvoice/' . $invoice->id . '/' . $invoice->hash) . '">Back to invoice</a>';
        } else {
            echo '<a href="' . site_url() . '">Back to merchant</a>';
        }
        echo '</div>';
    }
}
