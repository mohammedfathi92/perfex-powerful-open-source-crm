<?php
defined('BASEPATH') or exit('No direct script access allowed');

$hasPermissionDelete = has_permission('payments', '', 'delete');

$aColumns = array(
    'tblinvoicepaymentrecords.id as id',
    'invoiceid',
    'paymentmode',
    'transactionid',
    get_sql_select_client_company(),
    'amount',
    'tblinvoicepaymentrecords.date as date'
    );

$join = array(
    'LEFT JOIN tblinvoices ON tblinvoices.id = tblinvoicepaymentrecords.invoiceid',
    'LEFT JOIN tblclients ON tblclients.userid = tblinvoices.clientid',
    'LEFT JOIN tblcurrencies ON tblcurrencies.id = tblinvoices.currency',
    'LEFT JOIN tblinvoicepaymentsmodes ON tblinvoicepaymentsmodes.id = tblinvoicepaymentrecords.paymentmode'
    );

$where = array();
if ($clientid != '') {
    array_push($where, 'AND tblclients.userid='.$clientid);
}

if (!has_permission('payments', '', 'view')) {
    array_push($where, 'AND invoiceid IN (SELECT id FROM tblinvoices WHERE addedfrom='.get_staff_user_id().')');
}

$sIndexColumn = "id";
$sTable       = 'tblinvoicepaymentrecords';

$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, array(
    'clientid',
    'symbol',
    'tblinvoicepaymentsmodes.name as payment_mode_name',
    'tblinvoicepaymentsmodes.id as paymentmodeid',
    'paymentmethod',
    ));

$output       = $result['output'];
$rResult      = $result['rResult'];

$this->_instance->load->model('payment_modes_model');
$online_modes = $this->_instance->payment_modes_model->get_online_payment_modes(true);

foreach ($rResult as $aRow) {

    $row = array();

    $row[] = '<a href="' . admin_url('payments/payment/' . $aRow['id']) . '">' . $aRow['id'] . '</a>';

    $row[] = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['invoiceid']) . '">' . format_invoice_number($aRow['invoiceid']) . '</a>';

    $outputPaymentMode = $aRow['payment_mode_name'];
           // Since version 1.0.1
     if (is_null($aRow['paymentmodeid'])) {
         foreach ($online_modes as $online_mode) {
             if ($aRow['paymentmode'] == $online_mode['id']) {
                 $outputPaymentMode = $online_mode['name'];
             }
         }
     }
    if (!empty($aRow['paymentmethod'])) {
        $outputPaymentMode .= ' - ' . $aRow['paymentmethod'];
    }
    $row[] = $outputPaymentMode;

    $row[] = $aRow['transactionid'];

    $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . $aRow['company'] . '</a>';

    $row[] = format_money($aRow['amount'], $aRow['symbol']);

    $row[] = _d($aRow['date']);

    $options            = icon_btn('payments/payment/' . $aRow['id'], 'pencil-square-o');

    if ($hasPermissionDelete) {
        $options .= icon_btn('payments/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');
    }

    $row[] = $options;
    $output['aaData'][] = $row;
}
