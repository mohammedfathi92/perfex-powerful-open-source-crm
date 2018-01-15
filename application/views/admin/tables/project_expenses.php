<?php
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns      = array(
    'tblexpenses.id',
    'category',
    'amount',
    'expense_name',
    'file_name',
    'date',
    'invoiceid',
    'reference_no',
    'paymentmode'
);
$join          = array(
    'LEFT JOIN tblclients ON tblclients.userid = tblexpenses.clientid',
    'LEFT JOIN tblexpensescategories ON tblexpensescategories.id = tblexpenses.category',
    'LEFT JOIN tblfiles ON tblfiles.rel_id = tblexpenses.id AND rel_type="expense"',
);
$custom_fields = get_custom_fields('expenses', array(
    'show_on_table' => 1
));
$i             = 0;
foreach ($custom_fields as $field) {
    array_push($aColumns, 'ctable_' . $i . '.value as cvalue_' . $i);
    array_push($join, 'LEFT JOIN tblcustomfieldsvalues as ctable_' . $i . ' ON tblexpenses.id = ctable_' . $i . '.relid AND ctable_' . $i . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $i . '.fieldid=' . $field['id']);
    $i++;
}
$where = array();
$filter = array();
include_once(APPPATH.'views/admin/tables/includes/expenses_filter.php');

array_push($where, 'AND project_id='.$project_id);

if (!has_permission('expenses', '', 'view')) {
    array_push($where, 'AND addedfrom='.get_staff_user_id());
}
$sIndexColumn = "id";
$sTable       = 'tblexpenses';
$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, array(
    'name',
    'billable',
    'invoiceid',
    'currency',
    'tax',
    'tax2'
));
$output       = $result['output'];
$rResult      = $result['rResult'];
$this->_instance->load->model('currencies_model');
$this->_instance->load->model('payment_modes_model');
foreach ($rResult as $aRow) {
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        if (strpos($aColumns[$i], 'as') !== false && !isset($aRow[$aColumns[$i]])) {
            $_data = $aRow[strafter($aColumns[$i], 'as ')];
        } else {
            $_data = $aRow[$aColumns[$i]];
        }
        if ($aColumns[$i] == 'tblexpenses.id') {
            $_data = '<span class="label label-default inline-block">'.$_data.'</span>';
        } elseif ($aColumns[$i] == 'category') {
            $_data = '<a href="' . admin_url('expenses/list_expenses/' . $aRow['tblexpenses.id']) . '" target="_blank">' . $aRow['name'] . '</a>';
            if ($aRow['billable'] == 1) {
                if ($aRow['invoiceid'] == null) {
                    $_data .= '<p class="text-danger">' . _l('expense_list_unbilled') . '</p>';
                } else {
                    if (total_rows('tblinvoices', array(
                        'id' => $aRow['invoiceid'],
                        'status' => 2
                    )) > 0) {
                        $_data .= '<br /><p class="text-success">' . _l('expense_list_billed') . '</p>';
                    } else {
                        $_data .= '<p class="text-success">' . _l('expense_list_invoice') . '</p>';
                    }
                }
            }
        } elseif ($aColumns[$i] == 'amount') {
            $total = $_data;
            $tmp_total = $total;
            if ($aRow['tax'] != 0) {
                $_tax = get_tax_by_id($aRow['tax']);
                $total += ($total / 100 * $_tax->taxrate);
            }
            if ($aRow['tax2'] != 0) {
                $_tax = get_tax_by_id($aRow['tax2']);
                $total += ($tmp_total / 100 * $_tax->taxrate);
            }
            $_data = format_money($total, $this->_instance->currencies_model->get($aRow['currency'])->symbol);
        } elseif ($aColumns[$i] == 'paymentmode') {
            $_data = '';
            if ($aRow['paymentmode'] != '0' && !empty($aRow['paymentmode'])) {
                $_data = $this->_instance->payment_modes_model->get($aRow['paymentmode'])->name;
            }
        } elseif ($aColumns[$i] == 'file_name') {
            if (!empty($_data)) {
                $_data=  '<a href="'.site_url('download/file/expense/'.$aRow['tblexpenses.id']).'">'.$_data.'</a>';
            }
        } elseif ($aColumns[$i] == 'date') {
            $_data = _d($_data);
        } elseif ($aColumns[$i] == 'invoiceid') {
            if ($_data) {
                $_data = '<a href="'.admin_url('invoices/list_invoices/'.$_data).'">'.format_invoice_number($_data).'</a>';
            } else {
                $_data = '';
            }
        } else {
            if (_startsWith($aColumns[$i], 'ctable_') && is_date($_data)) {
                $_data = _d($_data);
            }
        }
        $row[] = $_data;
    }
    $output['aaData'][] = $row;
}
