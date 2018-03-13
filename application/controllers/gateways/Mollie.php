<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mollie extends CRM_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function verify_payment()
    {
        $invoiceid = $this->input->get('invoiceid');
        $hash      = $this->input->get('hash');
        check_invoice_restrictions($invoiceid, $hash);

        $this->db->where('id', $invoiceid);
        $invoice = $this->db->get('tblinvoices')->row();

        $oResponse = $this->mollie_gateway->fetch_payment(array(
            'transaction_id' => $invoice->token
        ));
        if ($oResponse->isSuccessful()) {
            $data = $oResponse->getData();
            if ($data['status'] == 'paid') {
                set_alert('success', _l('online_payment_recorded_success'));
            }
        } else {
            set_alert('danger', $oResponse->getMessage());
        }
        redirect(site_url('viewinvoice/' . $invoice->id . '/' . $invoice->hash));
    }

    public function webhook()
    {
        $ip = $this->input->ip_address();
        if (ip_in_range($ip, '87.233.229.26-87.233.229.27')) {
            $trans_id  = $this->input->post('id');
            $oResponse = $this->mollie_gateway->fetch_payment(array(
                'transaction_id' => $trans_id
            ));
            $data      = $oResponse->getData();
            if ($data['status'] == 'paid') {

                $this->mollie_gateway->addPayment(
                    array(
                      'amount'=>$data['amount'],
                      'invoiceid'=>$data['metadata']['order_id'],
                      'paymentmethod'=>$data['method'],
                      'transactionid'=>$trans_id,
                 ));

            } elseif ($data['status'] == 'refunded' || $data['status'] == 'cancelled' || $data['status'] == 'charged_back') {
                $this->db->where('invoiceid', $data['metadata']['order_id']);
                $this->db->where('transactionid', $trans_id);
                $this->db->delete('tblinvoicepaymentrecords');
                update_invoice_status($data['metadata']['order_id']);
            }

            header("HTTP/1.1 200 OK");
        }
    }
}
