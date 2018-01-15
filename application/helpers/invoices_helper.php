<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Get invoice total left for paying if not payments found the original total from the invoice will be returned
 * @since  Version 1.0.1
 * @param  mixed $id     invoice id
 * @param  mixed $invoice_total
 * @return mixed  total left
 */
function get_invoice_total_left_to_pay($id, $invoice_total = null)
{
    $CI =& get_instance();

    if ($invoice_total === null) {
        $CI->db->select('total')
        ->where('id', $id);
        $invoice_total = $CI->db->get('tblinvoices')->row()->total;
    }

    if (!class_exists('payments_model')) {
        $CI->load->model('payments_model');
    }

    if (!class_exists('credit_notes_model')) {
        $CI->load->model('credit_notes_model');
    }

    $payments = $CI->payments_model->get_invoice_payments($id);
    $credits = $CI->credit_notes_model->get_applied_invoice_credits($id);

    $payments = array_merge($payments, $credits);

    $totalPayments = 0;

    $bcadd = function_exists('bcadd');

    foreach ($payments as $payment) {
        if ($bcadd) {
            $totalPayments = bcadd($totalPayments, $payment['amount'], get_decimal_places());
        } else {
            $totalPayments += $payment['amount'];
        }
    }

    if (function_exists('bcsub')) {
        return bcsub($invoice_total, $totalPayments, get_decimal_places());
    } else {
        return number_format($invoice_total - $totalPayments, get_decimal_places(), '.', '');
    }
}

/**
 * Check invoice restrictions - hash, clientid
 * @since  Version 1.0.1
 * @param  mixed $id   invoice id
 * @param  string $hash invoice hash
 */
function check_invoice_restrictions($id, $hash)
{
    $CI =& get_instance();
    $CI->load->model('invoices_model');
    if (!$hash || !$id) {
        show_404();
    }
    if (!is_client_logged_in() && !is_staff_logged_in()) {
        if (get_option('view_invoice_only_logged_in') == 1) {
            redirect(site_url('clients/login'));
        }
    }
    $invoice = $CI->invoices_model->get($id);
    if (!$invoice || ($invoice->hash != $hash)) {
        show_404();
    }

    // Do one more check
    if (!is_staff_logged_in()) {
        if (get_option('view_invoice_only_logged_in') == 1) {
            if ($invoice->clientid != get_client_user_id()) {
                show_404();
            }
        }
    }
}

/**
 * Format invoice status
 * @param  integer  $status
 * @param  string  $classes additional classes
 * @param  boolean $label   To include in html label or not
 * @return mixed
 */
function format_invoice_status($status, $classes = '', $label = true)
{
    $id          = $status;
    $label_class = get_invoice_status_label($status);
    if ($status == 1) {
        $status = _l('invoice_status_unpaid');
    } elseif ($status == 2) {
        $status = _l('invoice_status_paid');
    } elseif ($status == 3) {
        $status = _l('invoice_status_not_paid_completely');
    } elseif ($status == 4) {
        $status = _l('invoice_status_overdue');
    } elseif ($status == 5) {
        $status = _l('invoice_status_cancelled');
    } else {
        // status 6
        $status = _l('invoice_status_draft');
    }
    if ($label == true) {
        return '<span class="label label-' . $label_class . ' ' . $classes . ' s-status invoice-status-' . $id . '">' . $status . '</span>';
    } else {
        return $status;
    }
}
/**
 * Return invoice status label class baed on twitter bootstrap classses
 * @param  mixed $status invoice status id
 * @return string
 */
function get_invoice_status_label($status)
{
    $label_class = '';
    if ($status == 1) {
        $label_class = 'danger';
    } elseif ($status == 2) {
        $label_class = 'success';
    } elseif ($status == 3) {
        $label_class = 'warning';
    } elseif ($status == 4) {
        $label_class = 'warning';
    } elseif ($status == 5 || $status == 6) {
        $label_class = 'default';
    } else {
        if (!is_numeric($status)) {
            if ($status == 'not_sent') {
                $label_class = 'default';
            }
        }
    }

    return $label_class;
}

/**
 * Function used in invoice PDF, this function will return RGBa color for PDF dcouments
 * @param  mixed $status_id current invoice status id
 * @return string
 */
function invoice_status_color_pdf($status_id)
{
    $statusColor = '';

    if ($status_id == 1) {
        $statusColor = '252, 45, 66';
    } elseif ($status_id == 2) {
        $statusColor = '0, 191, 54';
    } elseif ($status_id == 3) {
        $statusColor = '255, 111, 0';
    } elseif ($status_id == 4) {
        $statusColor = '255, 111, 0';
    } elseif ($status_id == 5 || $status_id == 6) {
        $statusColor = '114, 123, 144';
    }

    return $statusColor;
}

/**
 * Update invoice status
 * @param  mixed $id invoice id
 * @return mixed invoice updates status / if no update return false
 * @return boolean $prevent_logging do not log changes if the status is updated for the invoice activity log
 */
function update_invoice_status($id, $force_update = false, $prevent_logging = false)
{
    $CI =& get_instance();

    $CI->load->model('invoices_model');
    $invoice = $CI->invoices_model->get($id);

    $original_status = $invoice->status;

    if (($original_status == 6 && $force_update == false) || ($original_status == 5 && $force_update == false)) {
        return false;
    }

    $CI->db->select('amount')
    ->where('invoiceid', $id)
    ->order_by('tblinvoicepaymentrecords.id', 'asc');
    $payments = $CI->db->get('tblinvoicepaymentrecords')->result_array();

    if (!class_exists('credit_notes_model')) {
        $CI->load->model('credit_notes_model');
    }

    $credits = $CI->credit_notes_model->get_applied_invoice_credits($id);
    // Merge credits applied with payments, credits in this function are casted as payments directly to invoice
    // This merge will help to update the status
    $payments = array_merge($payments, $credits);

    $totalPayments = array();
    $status         = 1;

    // Check if the first payments is equal to invoice total
    if (isset($payments[0])) {
        if ($payments[0]['amount'] == $invoice->total) {
            // Paid status
            $status = 2;
        } else {
            foreach ($payments as $payment) {
                array_push($totalPayments, $payment['amount']);
            }

            $totalPayments = array_sum($totalPayments);

            if ((function_exists('bccomp')
                ?  bccomp($invoice->total, $totalPayments, get_decimal_places()) === 0
                || bccomp($invoice->total, $totalPayments, get_decimal_places()) === -1
                : number_format(($invoice->total-$totalPayments), get_decimal_places(), '.', '') == '0')
                || $totalPayments > $invoice->total) {
                // Paid status
                $status = 2;
            } elseif ($totalPayments == 0) {
                // Unpaid status
                $status = 1;
            } else {
                if ($invoice->duedate != null) {
                    if ($totalPayments > 0) {
                        // Not paid completely status
                        $status = 3;
                    } elseif (date('Y-m-d', strtotime($invoice->duedate)) < date('Y-m-d')) {
                        $status = 4;
                    }
                } else {
                    // Not paid completely status
                    $status = 3;
                }
            }
        }
    } else {
        if ($invoice->total == 0) {
            $status = 2;
        } else {
            if ($invoice->duedate != null) {
                if (date('Y-m-d', strtotime($invoice->duedate)) < date('Y-m-d')) {
                    // Overdue status
                    $status = 4;
                }
            }
        }
    }

    $CI->db->where('id', $id);
    $CI->db->update('tblinvoices', array(
        'status' => $status,
    ));

    if ($CI->db->affected_rows() > 0) {
        do_action('invoice_status_changed', array('invoice_id'=>$id, 'status'=>$status));
        if ($prevent_logging == true) {
            return $status;
        }

        $log = 'Invoice Status Updated [Invoice Number: ' . format_invoice_number($invoice->id) . ', From: ' . format_invoice_status($original_status, '', false) . ' To: ' . format_invoice_status($status, '', false) . ']';

        logActivity($log, null);

        $additional_activity = serialize(array(
            '<original_status>' . $original_status . '</original_status>',
            '<new_status>' . $status . '</new_status>',
        ));

        $CI->invoices_model->log_invoice_activity($invoice->id, 'invoice_activity_status_updated', false, $additional_activity);

        return $status;
    }

    return false;
}


/**
 * Check if the invoice id is last invoice
 * @param  mixed  $id invoice id
 * @return boolean
 */
function is_last_invoice($id)
{
    $CI =& get_instance();
    $CI->db->select('id')->from('tblinvoices')->order_by('id', 'desc')->limit(1);
    $query           = $CI->db->get();
    $last_invoice_id = $query->row()->id;
    if ($last_invoice_id == $id) {
        return true;
    }

    return false;
}

/**
 * Format invoice number based on description
 * @param  mixed $id
 * @return string
 */
function format_invoice_number($id)
{
    $CI =& get_instance();
    $CI->db->select('date,number,prefix,number_format')->from('tblinvoices')->where('id', $id);
    $invoice = $CI->db->get()->row();

    if (!$invoice) {
        return '';
    }

    $format = $invoice->number_format;
    $prefix = $invoice->prefix;
    $number = $invoice->number;
    $date = $invoice->date;
    $prefixPadding = get_option('number_padding_prefixes');

    if ($format == 1) {
        // Number based
        return $prefix . str_pad($number, $prefixPadding, '0', STR_PAD_LEFT);
    } elseif ($format == 2) {
        return $prefix . date('Y', strtotime($date)) . '/' . str_pad($number, $prefixPadding, '0', STR_PAD_LEFT);
    }

    return $number;
}

/**
 * Function that return invoice item taxes based on passed item id
 * @param  mixed $itemid
 * @return array
 */
function get_invoice_item_taxes($itemid)
{
    $CI =& get_instance();
    $CI->db->where('itemid', $itemid);
    $CI->db->where('rel_type', 'invoice');
    $taxes = $CI->db->get('tblitemstax')->result_array();
    $i     = 0;
    foreach ($taxes as $tax) {
        $taxes[$i]['taxname'] = $tax['taxname'] . '|' . $tax['taxrate'];
        $i++;
    }

    return $taxes;
}

/**
 * Check if payment mode is allowed for specific invoice
 * @param  mixed  $id payment mode id
 * @param  mixed  $invoiceid invoice id
 * @return boolean
 */
function is_payment_mode_allowed_for_invoice($id, $invoiceid)
{
    $CI =& get_instance();
    $CI->db->select('tblcurrencies.name as currency_name,allowed_payment_modes')->from('tblinvoices')->join('tblcurrencies', 'tblcurrencies.id = tblinvoices.currency', 'left')->where('tblinvoices.id', $invoiceid);
    $invoice       = $CI->db->get()->row();
    $allowed_modes = $invoice->allowed_payment_modes;
    if (!is_null($allowed_modes)) {
        $allowed_modes = unserialize($allowed_modes);
        if (count($allowed_modes) == 0) {
            return false;
        } else {
            foreach ($allowed_modes as $mode) {
                if ($mode == $id) {
                    // is offline payment mode
                    if (is_numeric($id)) {
                        return true;
                    }
                    // check currencies
                    $currencies = explode(',', get_option('paymentmethod_' . $id . '_currencies'));
                    foreach ($currencies as $currency) {
                        $currency = trim($currency);
                        if (mb_strtoupper($currency) == mb_strtoupper($invoice->currency_name)) {
                            return true;
                        }
                    }

                    return false;
                }
            }
        }
    } else {
        return false;
    }

    return false;
}
/**
 * Check if invoice mode exists in invoice
 * @since  Version 1.0.1
 * @param  array  $modes     all invoice modes
 * @param  mixed  $invoiceid invoice id
 * @param  boolean $offline   should check offline or online modes
 * @return boolean
 */
function found_invoice_mode($modes, $invoiceid, $offline = true, $show_on_pdf = false)
{
    $CI =& get_instance();
    $CI->db->select('tblcurrencies.name as currency_name,allowed_payment_modes')->from('tblinvoices')->join('tblcurrencies', 'tblcurrencies.id = tblinvoices.currency', 'left')->where('tblinvoices.id', $invoiceid);
    $invoice = $CI->db->get()->row();
    if (!is_null($invoice->allowed_payment_modes)) {
        $invoice->allowed_payment_modes = unserialize($invoice->allowed_payment_modes);
        if (count($invoice->allowed_payment_modes) == 0) {
            return false;
        } else {
            foreach ($modes as $mode) {
                if ($offline == true) {
                    if (is_numeric($mode['id']) && is_array($invoice->allowed_payment_modes)) {
                        foreach ($invoice->allowed_payment_modes as $allowed_mode) {
                            if ($allowed_mode == $mode['id']) {
                                if ($show_on_pdf == false) {
                                    return true;
                                } else {
                                    if ($mode['show_on_pdf'] == 1) {
                                        return true;
                                    } else {
                                        return false;
                                    }
                                }
                            }
                        }
                    }
                } else {
                    if (!is_numeric($mode['id']) && !empty($mode['id'])) {
                        foreach ($invoice->allowed_payment_modes as $allowed_mode) {
                            if ($allowed_mode == $mode['id']) {
                                // Check for currencies
                                $currencies = explode(',', get_option('paymentmethod_' . $mode['id'] . '_currencies'));
                                foreach ($currencies as $currency) {
                                    $currency = trim($currency);
                                    if (strtoupper($currency) == strtoupper($invoice->currency_name)) {
                                        return true;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    return false;
}

/**
 * This function do not work with cancelled status
 * Calculate invoices percent by status
 * @param  mixed $status          estimate status
 * @param  mixed $total_invoices in case the total is calculated in other place
 * @return array
 */
function get_invoices_percent_by_status($status, $total_invoices = '')
{
    $has_permission_view = has_permission('invoices', '', 'view');

    if (!is_numeric($total_invoices)) {
        $where_total = 'status NOT IN(5)';
        if (!$has_permission_view) {
            $where_total .= ' AND addedfrom=' . get_staff_user_id();
        }
        $total_invoices = total_rows('tblinvoices', $where_total);
    }
    $data            = array();
    $total_by_status = 0;
    if (!is_numeric($status)) {
        if ($status == 'not_sent') {
            $total_by_status = total_rows('tblinvoices', 'sent=0 AND status NOT IN(2,5)' . (!$has_permission_view ? ' AND addedfrom=' . get_staff_user_id() : ''));
        }
    } else {
        $total_by_status = total_rows('tblinvoices', 'status = ' . $status . ' AND status NOT IN(5)' . (!$has_permission_view ? ' AND addedfrom=' . get_staff_user_id() : ''));
    }
    $percent                 = ($total_invoices > 0 ? number_format(($total_by_status * 100) / $total_invoices, 2) : 0);
    $data['total_by_status'] = $total_by_status;
    $data['percent']         = $percent;
    $data['total']           = $total_invoices;

    return $data;
}
/**
 * Load invoices total templates
 * This is the template where is showing the panels Outstanding Invoices, Paid Invoices and Past Due invoices
 * @return string
 */
function load_invoices_total_template()
{
    $CI = &get_instance();
    $CI->load->model('invoices_model');
    $_data = $CI->input->post();
    if (!$CI->input->post('customer_id')) {
        $multiple_currencies = call_user_func('is_using_multiple_currencies');
    } else {
        $_data['customer_id'] = $CI->input->post('customer_id');
        $multiple_currencies  = call_user_func('is_client_using_multiple_currencies', $CI->input->post('customer_id'));
    }

    if ($CI->input->post('project_id')) {
        $_data['project_id'] = $CI->input->post('project_id');
    }

    if ($multiple_currencies) {
        $CI->load->model('currencies_model');
        $data['invoices_total_currencies'] = $CI->currencies_model->get();
    }
    $data['invoices_years']       = $CI->invoices_model->get_invoices_years();
    $data['total_result'] = $CI->invoices_model->get_invoices_total($_data);
    $data['_currency']    = $data['total_result']['currencyid'];
    $CI->load->view('admin/invoices/invoices_total_template', $data);
}
