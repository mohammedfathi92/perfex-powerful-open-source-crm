<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Statement_model extends CRM_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get customer statement formatted
     * @param  mixed $customer_id customer id
     * @param  string $from        date from
     * @param  string $to          date to
     * @return array
     */
    public function get_statement($customer_id, $from, $to)
    {
        $sql = 'SELECT
        tblinvoices.id as invoice_id,
        hash,
        tblinvoices.date as date,
        tblinvoices.duedate,
        concat(tblinvoices.date, \' \', RIGHT(tblinvoices.datecreated,LOCATE(\' \',tblinvoices.datecreated) - 3)) as tmp_date,
        tblinvoices.duedate as duedate,
        tblinvoices.total as invoice_amount
        FROM tblinvoices WHERE clientid ='.$customer_id;

        if ($from == $to) {
            $sqlDate = 'date="'.$from.'"';
        } else {
            $sqlDate = '(date BETWEEN "' . $from . '" AND "' . $to . '")';
        }

        $sql .= ' AND ' . $sqlDate;

        $invoices = $this->db->query($sql . '
            AND status != 6
            AND status != 5
            ORDER By date DESC')->result_array();

        // Credit notes
        $sql_credit_notes = 'SELECT
        tblcreditnotes.id as credit_note_id,
        tblcreditnotes.date as date,
        concat(tblcreditnotes.date, \' \', RIGHT(tblcreditnotes.datecreated,LOCATE(\' \',tblcreditnotes.datecreated) - 3)) as tmp_date,
        tblcreditnotes.total as credit_note_amount
        FROM tblcreditnotes WHERE clientid ='.$customer_id .' AND status != 3';

        $sql_credit_notes .= ' AND ' . $sqlDate;

        $credit_notes = $this->db->query($sql_credit_notes)->result_array();

        // Credits applied
        $sql_credits_applied = 'SELECT
        tblcredits.id as credit_id,
        invoice_id as credit_invoice_id,
        tblcredits.credit_id as credit_applied_credit_note_id,
        tblcredits.date as date,
        concat(tblcredits.date, \' \', RIGHT(tblcredits.date_applied,LOCATE(\' \',tblcredits.date_applied) - 3)) as tmp_date,
        tblcredits.amount as credit_amount
        FROM tblcredits
        JOIN tblcreditnotes ON tblcreditnotes.id = tblcredits.credit_id
        ';

        $sql_credits_applied .= '
        WHERE clientid ='.$customer_id;

        $sqlDateCreditsAplied = str_replace('date', 'tblcredits.date', $sqlDate);

        $sql_credits_applied .= ' AND ' . $sqlDateCreditsAplied;
        $credits_applied = $this->db->query($sql_credits_applied)->result_array();

        // Replace error ambigious column in where clause
        $sqlDatePayments = str_replace('date', 'tblinvoicepaymentrecords.date', $sqlDate);

        $sql_payments = 'SELECT
        tblinvoicepaymentrecords.id as payment_id,
        tblinvoicepaymentrecords.date as date,
        concat(tblinvoicepaymentrecords.date, \' \', RIGHT(tblinvoicepaymentrecords.daterecorded,LOCATE(\' \',tblinvoicepaymentrecords.daterecorded) - 3)) as tmp_date,
        tblinvoicepaymentrecords.invoiceid as payment_invoice_id,
        tblinvoicepaymentrecords.amount as payment_total
        FROM tblinvoicepaymentrecords
        JOIN tblinvoices ON tblinvoices.id = tblinvoicepaymentrecords.invoiceid
        WHERE '.$sqlDatePayments.' AND tblinvoices.clientid = '.$customer_id.'
        ORDER by tblinvoicepaymentrecords.date DESC';

        $payments = $this->db->query($sql_payments)->result_array();

        // merge results
        $merged = array_merge($invoices, $payments, $credit_notes, $credits_applied);

        // sort by date
        usort($merged, function ($a, $b) {
            // fake date select sorting
            return strtotime($a['tmp_date']) - strtotime($b['tmp_date']);
        });

        // Define final result variable
        $result = array();
        // Store in result array key
        $result['result'] = $merged;

        // Invoiced amount during the period
        $result['invoiced_amount'] = $this->db->query('SELECT
        SUM(tblinvoices.total) as invoiced_amount
        FROM tblinvoices
        WHERE clientid = '.$customer_id . '
        AND ' . $sqlDate . ' AND status != 5 and status != 6')
            ->row()->invoiced_amount;

        if ($result['invoiced_amount'] === null) {
            $result['invoiced_amount'] = 0;
        }

        $result['credit_notes_amount'] = $this->db->query('SELECT
        SUM(tblcreditnotes.total) as credit_notes_amount
        FROM tblcreditnotes
        WHERE clientid = '.$customer_id . '
        AND ' . $sqlDate . ' AND status != 3')
            ->row()->credit_notes_amount;

        if ($result['credit_notes_amount'] === null) {
            $result['credit_notes_amount'] = 0;
        }

        $result['invoiced_amount'] =  $result['invoiced_amount'] - $result['credit_notes_amount'];

        // Amount paid during the period
        $result['amount_paid'] = $this->db->query('SELECT
        SUM(tblinvoicepaymentrecords.amount) as amount_paid
        FROM tblinvoicepaymentrecords
        JOIN tblinvoices ON tblinvoices.id = tblinvoicepaymentrecords.invoiceid
        WHERE '.$sqlDatePayments.' AND tblinvoices.clientid = '.$customer_id)
            ->row()->amount_paid;

        if ($result['amount_paid'] === null) {
            $result['amount_paid'] = 0;
        }

        // Beginning balance is all invoices amount before the FROM date - payments received before FROM date
        $result['beginning_balance'] = $this->db->query('
            SELECT (
            COALESCE(SUM(tblinvoices.total),0) - (
            (
            SELECT COALESCE(SUM(tblinvoicepaymentrecords.amount),0)
            FROM tblinvoicepaymentrecords
            JOIN tblinvoices ON tblinvoices.id = tblinvoicepaymentrecords.invoiceid
            WHERE tblinvoicepaymentrecords.date < "' . $from . '"
            AND tblinvoices.clientid='.$customer_id.'
            ) + (
                SELECT COALESCE(SUM(tblcreditnotes.total),0)
                FROM tblcreditnotes
                WHERE tblcreditnotes.date < "' . $from . '"
                AND tblcreditnotes.clientid='.$customer_id.'
            )
        )
            )
            as beginning_balance FROM tblinvoices
            WHERE date < "' . $from . '"
            AND clientid = '.$customer_id .'
            AND status != 6
            AND status != 5')
              ->row()->beginning_balance;

        if ($result['beginning_balance'] === null) {
            $result['beginning_balance'] = 0;
        }

        $dec = get_decimal_places();

        if (function_exists('bcsub')) {
            $result['balance_due'] = bcsub($result['invoiced_amount'], $result['amount_paid'], $dec);
            $result['balance_due'] = bcadd($result['balance_due'], $result['beginning_balance'], $dec);
        } else {
            $result['balance_due'] = number_format($result['invoiced_amount'] - $result['amount_paid'], $dec, '.', '');
            $result['balance_due'] = $result['balance_due'] + number_format($result['beginning_balance'], $dec, '.', '');
        }

        $result['client_id'] = $customer_id;
        $result['client'] = $this->clients_model->get($customer_id);
        $result['from'] = $from;
        $result['to'] = $to;

        $customer_currency = $this->clients_model->get_customer_default_currency($customer_id);
        $this->load->model('currencies_model');

        if ($customer_currency != 0) {
            $currency = $this->currencies_model->get($customer_currency);
        } else {
            $currency = $this->currencies_model->get_base_currency();
        }

        $result['currency'] = $currency;

        return $result;
    }

    /**
     * Send customer statement to email
     * @param  mixed $customer_id customer id
     * @param  array $send_to     array of contact emails to send
     * @param  string $from        date from
     * @param  string $to          date to
     * @param  string $cc          email CC
     * @return boolean
     */
    public function send_statement_to_email($customer_id, $send_to, $from, $to, $cc = '')
    {
        $send = false;
        if (is_array($send_to) && count($send_to) > 0) {
            $this->load->model('emails_model');

            $statement = $this->get_statement($customer_id, to_sql_date($from), to_sql_date($to));

            $pdf    = statement_pdf($statement);

            $pdf_file_name = slug_it(_l('customer_statement').'-'.$statement['client']->company);

            $attach = $pdf->Output($pdf_file_name . '.pdf', 'S');

            $i              = 0;
            foreach ($send_to as $contact_id) {
                if ($contact_id != '') {
                    $this->emails_model->add_attachment(array(
                            'attachment' => $attach,
                            'filename' => $pdf_file_name . '.pdf',
                            'type' => 'application/pdf',
                        ));

                    $contact      = $this->clients_model->get_contact($contact_id);
                    $merge_fields = array();
                    $merge_fields = array_merge(
                        $merge_fields,
                        get_client_contact_merge_fields(
                            $statement['client']->userid,
                        $contact_id
                        )
                    );

                    $merge_fields = array_merge($merge_fields, get_statement_merge_fields($statement));

                    // Send cc only for the first contact
                    if (!empty($cc) && $i > 0) {
                        $cc = '';
                    }
                    if ($this->emails_model->send_email_template('client-statement', $contact->email, $merge_fields, '', $cc)) {
                        $send = true;
                    }
                }
                $i++;
            }

            if ($send) {
                return true;
            }
        }

        return false;
    }
}
