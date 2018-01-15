<?php
$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column  = '';

$info_right_column .= '<span style="font-weight:bold;font-size:27px;">' . _l('invoice_pdf_heading') . '</span><br />';
$info_right_column .= '<b style="color:#4e4e4e;"># ' . $invoice_number . '</b>';

if (get_option('show_status_on_pdf_ei') == 1) {
    $info_right_column .= '<br /><span style="color:rgb('.invoice_status_color_pdf($status).');text-transform:uppercase;">' . format_invoice_status($status, '', false) . '</span>';
}

if ($status != 2 && $status != 5 && get_option('show_pay_link_to_invoice_pdf') == 1
    && found_invoice_mode($payment_modes,$invoice->id,false)) {
    $info_right_column .= ' - <a style="color:#84c529;text-decoration:none;text-transform:uppercase;" href="' . site_url('viewinvoice/' . $invoice->id . '/' . $invoice->hash) . '"><1b>' . _l('view_invoice_pdf_link_pay') . '</1b></a>';
}

// write the first column
$info_left_column .= pdf_logo_url();
$pdf->MultiCell(($dimensions['wk'] / 2) - $dimensions['lm'], 0, $info_left_column, 0, 'J', 0, 0, '', '', true, 0, true, true, 0);
// write the second column
$pdf->MultiCell(($dimensions['wk'] / 2) - $dimensions['rm'], 0, $info_right_column, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(10);

// Get Y position for the separation
$y            = $pdf->getY();
$organizaion_info = '<div style="color:#424242;">';

$organizaion_info .= format_organization_info();

$organizaion_info .= '</div>';

$pdf->writeHTMLCell(($swap == '1' ? ($dimensions['wk']) - ($dimensions['lm'] * 2) : ($dimensions['wk'] / 2) - $dimensions['lm']), '', '', $y, $organizaion_info, 0, 0, false, true, ($swap == '1' ? 'R' : 'J'), true);

// Bill to
$invoice_info = '<b>' . _l('invoice_bill_to') . '</b>';
$invoice_info .= '<div style="color:#424242;">';
    $invoice_info .= format_customer_info($invoice, 'invoice', 'billing');
$invoice_info .= '</div>';

// ship to to
if ($invoice->include_shipping == 1 && $invoice->show_shipping_on_invoice == 1) {
    $invoice_info .= '<br /><b>' . _l('ship_to') . '</b>';
    $invoice_info .= '<div style="color:#424242;">';
    $invoice_info .= format_customer_info($invoice, 'invoice', 'shipping');
    $invoice_info .= '</div>';
}

$invoice_info .= '<br />'._l('invoice_data_date') . ' ' . _d($invoice->date).'<br />';

if (!empty($invoice->duedate)) {
    $invoice_info .= _l('invoice_data_duedate') . ' ' . _d($invoice->duedate).'<br />';
}

if ($invoice->sale_agent != 0 && get_option('show_sale_agent_on_invoices') == 1) {
    $invoice_info .= _l('sale_agent_string') . ': ' . get_staff_full_name($invoice->sale_agent).'<br />';
}

if ($invoice->project_id != 0 && get_option('show_project_on_invoice') == 1) {
    $invoice_info .= _l('project') . ': ' . get_project_name_by_id($invoice->project_id).'<br />';
}

foreach ($pdf_custom_fields as $field) {
    $value = get_custom_field_value($invoice->id, $field['id'], 'invoice');
    if ($value == '') { continue; }
    $invoice_info .= $field['name'] . ': ' . $value.'<br />';
}

$pdf->writeHTMLCell(($dimensions['wk'] / 2) - $dimensions['rm'], '', '', ($swap == '1' ? $y : ''), $invoice_info, 0, 1, false, true, ($swap == '1' ? 'J' : 'R'), true);

// The Table
$pdf->Ln(6);
$item_width = 38;
// If show item taxes is disabled in PDF we should increase the item width table heading
$item_width = get_option('show_tax_per_item') == 0 ? $item_width+15 : $item_width;

// Header
$qty_heading = _l('invoice_table_quantity_heading');
if ($invoice->show_quantity_as == 2) {
    $qty_heading = _l('invoice_table_hours_heading');
} elseif ($invoice->show_quantity_as == 3) {
    $qty_heading = _l('invoice_table_quantity_heading') . '/' . _l('invoice_table_hours_heading');
}

$tblhtml = '
<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="8">
    <tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . ';">
        <th width="5%;" align="center">#</th>
        <th width="' . $item_width . '%" align="left">' . _l('invoice_table_item_heading') . '</th>
        <th width="12%" align="right">' . $qty_heading . '</th>
        <th width="15%" align="right">' . _l('invoice_table_rate_heading') . '</th>';

if (get_option('show_tax_per_item') == 1) {
    $tblhtml .= '<th width="15%" align="right">' . _l('invoice_table_tax_heading') . '</th>';
}

$tblhtml .= '<th width="15%" align="right">' . _l('invoice_table_amount_heading') . '</th>
</tr>';

// Items
$tblhtml .= '<tbody>';

$items_data = get_table_items_and_taxes($invoice->items, 'invoice');

$tblhtml .= $items_data['html'];
$taxes = $items_data['taxes'];

$tblhtml .= '</tbody>';
$tblhtml .= '</table>';
$pdf->writeHTML($tblhtml, true, false, false, false, '');

$pdf->Ln(8);
$tbltotal = '';

$tbltotal .= '<table cellpadding="6" style="font-size:'.($font_size+4).'px">';
$tbltotal .= '
<tr>
    <td align="right" width="85%"><strong>' . _l('invoice_subtotal') . '</strong></td>
    <td align="right" width="15%">' . format_money($invoice->subtotal, $invoice->symbol) . '</td>
</tr>';

if ($invoice->discount_percent != 0) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('invoice_discount') . '(' . _format_number($invoice->discount_percent, true) . '%)' . '</strong></td>
        <td align="right" width="15%">-' . format_money($invoice->discount_total, $invoice->symbol) . '</td>
    </tr>';
}

foreach ($taxes as $tax) {
    $total = array_sum($tax['total']);
    if ($invoice->discount_percent != 0 && $invoice->discount_type == 'before_tax') {
        $total_tax_calculated = ($total * $invoice->discount_percent) / 100;
        $total                = ($total - $total_tax_calculated);
    }
    // The tax is in format TAXNAME|20
    $_tax_name = explode('|', $tax['tax_name']);
    $tbltotal .= '<tr>
    <td align="right" width="85%"><strong>' . $_tax_name[0] . ' (' . _format_number($tax['taxrate']) . '%)' . '</strong></td>
    <td align="right" width="15%">' . format_money($total, $invoice->symbol) . '</td>
</tr>';
}

if ((int) $invoice->adjustment != 0) {
    $tbltotal .= '<tr>
    <td align="right" width="85%"><strong>' . _l('invoice_adjustment') . '</strong></td>
    <td align="right" width="15%">' . format_money($invoice->adjustment, $invoice->symbol) . '</td>
</tr>';
}

$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="right" width="85%"><strong>' . _l('invoice_total') . '</strong></td>
    <td align="right" width="15%">' . format_money($invoice->total, $invoice->symbol) . '</td>
</tr>';

if (count($invoice->payments) > 0 && get_option('show_total_paid_on_invoice') == 1) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('invoice_total_paid') . '</strong></td>
        <td align="right" width="15%">-' . format_money(sum_from_table('tblinvoicepaymentrecords', array(
        'field' => 'amount',
        'where' => array(
            'invoiceid' => $invoice->id,
        ),
    )), $invoice->symbol) . '</td>
    </tr>';
}

if (get_option('show_credits_applied_on_invoice') == 1 && $credits_applied = total_credits_applied_to_invoice($invoice->id)) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('applied_credits') . '</strong></td>
        <td align="right" width="15%">-' . format_money($credits_applied, $invoice->symbol) . '</td>
    </tr>';
}

if (get_option('show_amount_due_on_invoice') == 1 && $invoice->status != 5) {
    $tbltotal .= '<tr style="background-color:#f0f0f0;">
       <td align="right" width="85%"><strong>' . _l('invoice_amount_due') . '</strong></td>
       <td align="right" width="15%">' . format_money($invoice->total_left_to_pay, $invoice->symbol) . '</td>
   </tr>';
}


$tbltotal .= '</table>';

$pdf->writeHTML($tbltotal, true, false, false, false, '');

if (get_option('total_to_words_enabled') == 1) {
    // Set the font bold
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('num_word') . ': ' . $CI->numberword->convert($invoice->total, $invoice->currency_name), 0, 1, 'C', 0, '', 0);
    // Set the font again to normal like the rest of the pdf
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(4);
}

if (count($invoice->payments) > 0 && get_option('show_transactions_on_invoice_pdf') == 1) {
    $pdf->Ln(4);
    $border = 'border-bottom-color:#000000;border-bottom-width:1px;border-bottom-style:solid; 1px solid black;';
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('invoice_received_payments'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(4);
    $tblhtml = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="0">
        <tr height="20"  style="color:#000;border:1px solid #000;">
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_number_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_mode_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_date_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_amount_heading') . '</th>
    </tr>';
    $tblhtml .= '<tbody>';
    foreach ($invoice->payments as $payment) {
        $payment_name = $payment['name'];
        if (!empty($payment['paymentmethod'])) {
            $payment_name .= ' - ' . $payment['paymentmethod'];
        }
        $tblhtml .= '
            <tr>
            <td>' . $payment['paymentid'] . '</td>
            <td>' . $payment_name . '</td>
            <td>' . _d($payment['date']) . '</td>
            <td>' . format_money($payment['amount'], $invoice->symbol) . '</td>
            </tr>
        ';
    }
    $tblhtml .= '</tbody>';
    $tblhtml .= '</table>';
    $pdf->writeHTML($tblhtml, true, false, false, false, '');
}

if (found_invoice_mode($payment_modes, $invoice->id, true, true)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', 10);
    $pdf->Cell(0, 0, _l('invoice_html_offline_payment'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', 10);
    foreach ($payment_modes as $mode) {
        if (is_numeric($mode['id'])) {
            if (!is_payment_mode_allowed_for_invoice($mode['id'], $invoice->id)) {
                continue;
            }
        }
        if (isset($mode['show_on_pdf']) && $mode['show_on_pdf'] == 1) {
            $pdf->Ln(1);
            $pdf->Cell(0, 0, $mode['name'], 0, 1, 'L', 0, '', 0);
            $pdf->Ln(2);
            $pdf->writeHTMLCell('', '', '', '', $mode['description'], 0, 1, false, true, 'L', true);
        }
    }
}

if (!empty($invoice->clientnote)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('invoice_note'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $invoice->clientnote, 0, 1, false, true, 'L', true);
}

if (!empty($invoice->terms)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('terms_and_conditions'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $invoice->terms, 0, 1, false, true, 'L', true);
}
