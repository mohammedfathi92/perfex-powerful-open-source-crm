<?php
$dimensions = $pdf->getPageDimensions();

// Get Y position for the separation
$y            = $pdf->getY();

$company_info = '<div style="color:#424242;">';
$company_info .= format_organization_info();
$company_info .= '</div>';

$pdf->writeHTMLCell(($swap == '0' ? (($dimensions['wk'] / 2) - $dimensions['rm']) : ''), '', '', ($swap == '0' ? $y : ''), $company_info, 0, 0, false, true, ($swap == '1' ? 'R' : 'J'), true);

// Bill to
$client_details = format_customer_info($payment->invoice_data, 'payment', 'billing');

// Write the client details
$pdf->writeHTMLCell(($dimensions['wk'] / 2) - $dimensions['lm'], '', '', ($swap == '1' ? $y : ''), $client_details, 0, 1, false, true, ($swap == '1' ? 'J' : 'R'), true);

$pdf->SetFontSize(15);
$pdf->Ln(8);
$receit_heading = '<div style="text-align:center">' . mb_strtoupper(_l('payment_receipt'),'UTF-8') . '</div>';
$pdf->writeHTML($receit_heading, true, false, false, false, '');
$pdf->SetFontSize($font_size);
$pdf->Ln(20);
$pdf->Cell(0, 0, _l('payment_date') . ' ' . _d($payment->date), 0, 1, 'L', 0, '', 0);
$pdf->Ln(2);
$pdf->writeHTMLCell(80, '', '', '', '<hr/>', 0, 1, false, true, 'L', true);
$payment_name = $payment->name;
if(!empty($payment->paymentmethod)){
    $payment_name .= ' - '. $payment->paymentmethod;
}
$pdf->Cell(0, 0, _l('payment_view_mode') . ' ' . $payment_name , 0, 1, 'L', 0, '', 0);
if(!empty($payment->transactionid)) {
    $pdf->Ln(2);
    $pdf->writeHTMLCell(80, '', '', '', '<hr/>', 0, 1, false, true, 'L', true);
    $pdf->Cell(0, 0, _l('payment_transaction_id') . ': ' . $payment->transactionid, 0, 1, 'L', 0, '', 0);
}
$pdf->Ln(2);
$pdf->writeHTMLCell(80, '', '', '', '<hr />', 0, 1, false, true, 'L', true);
$pdf->SetFillColor(132, 197, 41);
$pdf->SetTextColor(255);
$pdf->SetFontSize(12);
$pdf->Ln(3);
$pdf->Cell(80, 10, _l('payment_total_amount'), 0, 1, 'C', '1');
$pdf->SetFontSize(11);
$pdf->Cell(80, 10, format_money($payment->amount, $payment->invoice_data->symbol), 0, 1, 'C', '1');

$pdf->Ln(10);
$pdf->SetTextColor(0);
$pdf->SetFont($font_name, 'B', 14);
$pdf->Cell(0, 0, _l('payment_for_string'), 0, 1, 'L', 0, '', 0);
$pdf->SetFont($font_name, '', $font_size);
$pdf->Ln(5);

// Header
$tblhtml = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="0">
<tr height="30" style="color:#fff;" bgcolor="#3A4656">
    <th width="'.($amountDue ? 20 : 25).'%;">' . _l('payment_table_invoice_number') . '</th>
    <th width="'.($amountDue ? 20 : 25).'%;">' . _l('payment_table_invoice_date') . '</th>
    <th width="'.($amountDue ? 20 : 25).'%;">' . _l('payment_table_invoice_amount_total') . '</th>
    <th width="'.($amountDue ? 20 : 25).'%;">' . _l('payment_table_payment_amount_total') . '</th>';
    if($amountDue){
        $tblhtml .= '<th width="20%">'._l('invoice_amount_due').'</th>';
    }

$tblhtml .= '</tr>';

$tblhtml .= '<tbody>';
$tblhtml .= '<tr>';
$tblhtml .= '<td>' . format_invoice_number($payment->invoice_data->id) . '</td>';
$tblhtml .= '<td>' . _d($payment->invoice_data->date) . '</td>';
$tblhtml .= '<td>' . format_money($payment->invoice_data->total, $payment->invoice_data->symbol) . '</td>';
$tblhtml .= '<td>' . format_money($payment->amount, $payment->invoice_data->symbol) . '</td>';
if($amountDue){
    $tblhtml .= '<td style="color:#fc2d42">' . format_money($payment->invoice_data->total_left_to_pay, $payment->invoice_data->symbol) . '</td>';
}
$tblhtml .= '</tr>';
$tblhtml .= '</tbody>';
$tblhtml .= '</table>';
$pdf->writeHTML($tblhtml, true, false, false, false, '');
