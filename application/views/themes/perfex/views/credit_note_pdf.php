<?php
$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column  = '';

$info_right_column .= '<span style="font-weight:bold;font-size:27px;">' . _l('credit_note_pdf_heading') . '</span><br />';
$info_right_column .= '<b style="color:#4e4e4e;"># ' . $credit_note_number . '</b>';

if (get_option('show_status_on_pdf_ei') == 1) {
    $info_right_column .= '<br /><span style="color:rgb('.credit_note_status_color_pdf($credit_note->status).');text-transform:uppercase;">' . format_credit_note_status($credit_note->status,'',false) . '</span>';
}

// write the first column
$info_left_column .= pdf_logo_url();
$pdf->MultiCell(($dimensions['wk'] / 2) - $dimensions['lm'], 0, $info_left_column, 0, 'J', 0, 0, '', '', true, 0, true, true, 0);
// write the second column
$pdf->MultiCell(($dimensions['wk'] / 2) - $dimensions['rm'], 0, $info_right_column, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(10);

// Get Y position for the separation
$y            = $pdf->getY();
$organization_info = '<div style="color:#424242;">';

$organization_info .= format_organization_info();

$organization_info .= '</div>';

$pdf->writeHTMLCell(($swap == '1' ? ($dimensions['wk']) - ($dimensions['lm'] * 2) : ($dimensions['wk'] / 2) - $dimensions['lm']), '', '', $y, $organization_info, 0, 0, false, true, ($swap == '1' ? 'R' : 'J'), true);

// Bill to
$credit_note_info = '<b>' . _l('credit_note_bill_to') . '</b>';
$credit_note_info .= '<div style="color:#424242;">';
    $credit_note_info .= format_customer_info($credit_note, 'credit_note', 'billing');
$credit_note_info .= '</div>';

// ship to to
if ($credit_note->include_shipping == 1 && $credit_note->show_shipping_on_credit_note == 1) {
    $credit_note_info .= '<br /><b>' . _l('ship_to') . '</b>';
    $credit_note_info .= '<div style="color:#424242;">';
    $credit_note_info .= format_customer_info($credit_note, 'credit_note', 'shipping');
    $credit_note_info .= '</div>';
}

$credit_note_info .= '<br />'. _l('credit_note_date') . ': ' . _d($credit_note->date) .'<br />';

if (!empty($credit_note->reference_no)) {
    $credit_note_info .= _l('reference_no') . ': ' . $credit_note->reference_no .'<br />';
}

if ($credit_note->project_id != 0 && get_option('show_project_on_credit_note') == 1) {
    $credit_note_info .= _l('project') . ': ' . get_project_name_by_id($credit_note->project_id).'<br />';
}

foreach($pdf_custom_fields as $field){
    $value = get_custom_field_value($credit_note->id,$field['id'],'credit_note');
    if($value == ''){continue;}
    $credit_note_info .= $field['name'] . ': ' . $value .'<br />';
}

$pdf->writeHTMLCell(($dimensions['wk'] / 2) - $dimensions['rm'], '', '', ($swap == '1' ? $y : ''), $credit_note_info, 0, 1, false, true, ($swap == '1' ? 'J' : 'R'), true);

// The Table
$pdf->Ln(6);
$item_width = 38;
// If show item taxes is disabled in PDF we should increase the item width table heading
$item_width = get_option('show_tax_per_item') == 0 ? $item_width+15 : $item_width;

// Header
$qty_heading = _l('credit_note_table_quantity_heading');
if ($credit_note->show_quantity_as == 2) {
    $qty_heading = _l('credit_note_table_hours_heading');
} elseif ($credit_note->show_quantity_as == 3) {
    $qty_heading = _l('credit_note_table_quantity_heading') . '/' . _l('credit_note_table_hours_heading');
}

$tblhtml = '
<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="8">
    <tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . ';">
        <th width="5%;" align="center">#</th>
        <th width="' . $item_width . '%" align="left">' . _l('credit_note_table_item_heading') . '</th>
        <th width="12%" align="right">' . $qty_heading . '</th>
        <th width="15%" align="right">' . _l('credit_note_table_rate_heading') . '</th>';

if (get_option('show_tax_per_item') == 1) {
    $tblhtml .= '<th width="15%" align="right">' . _l('credit_note_table_tax_heading') . '</th>';
}

$tblhtml .= '<th width="15%" align="right">' . _l('credit_note_table_amount_heading') . '</th>
</tr>';

// Items
$tblhtml .= '<tbody>';

$items_data = get_table_items_and_taxes($credit_note->items, 'credit_note');

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
    <td align="right" width="85%"><strong>' . _l('credit_note_subtotal') . '</strong></td>
    <td align="right" width="15%">' . format_money($credit_note->subtotal, $credit_note->symbol) . '</td>
</tr>';

if ($credit_note->discount_percent != 0) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('credit_note_discount') . '(' . _format_number($credit_note->discount_percent, true) . '%)' . '</strong></td>
        <td align="right" width="15%">-' . format_money($credit_note->discount_total, $credit_note->symbol) . '</td>
    </tr>';
}

foreach ($taxes as $tax) {
    $total = array_sum($tax['total']);
    if ($credit_note->discount_percent != 0 && $credit_note->discount_type == 'before_tax') {
        $total_tax_calculated = ($total * $credit_note->discount_percent) / 100;
        $total                = ($total - $total_tax_calculated);
    }
    // The tax is in format TAXNAME|20
    $_tax_name = explode('|', $tax['tax_name']);
    $tbltotal .= '<tr>
    <td align="right" width="85%"><strong>' . $_tax_name[0] . ' (' . _format_number($tax['taxrate']) . '%)' . '</strong></td>
    <td align="right" width="15%">' . format_money($total, $credit_note->symbol) . '</td>
</tr>';
}

if ((int) $credit_note->adjustment != 0) {
    $tbltotal .= '<tr>
    <td align="right" width="85%"><strong>' . _l('credit_note_adjustment') . '</strong></td>
    <td align="right" width="15%">' . format_money($credit_note->adjustment, $credit_note->symbol) . '</td>
</tr>';
}

$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="right" width="85%"><strong>' . _l('credit_note_total') . '</strong></td>
    <td align="right" width="15%">' . format_money($credit_note->total, $credit_note->symbol) . '</td>
</tr>';

if ($credit_note->credits_used) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('credits_used') . '</strong></td>
        <td align="right" width="15%">' . '-' . format_money($credit_note->credits_used, $credit_note->symbol) . '</td>
    </tr>';
}

$tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('credits_remaining') . '</strong></td>
        <td align="right" width="15%">' . format_money($credit_note->remaining_credits, $credit_note->symbol) . '</td>
   </tr>';

$tbltotal .= '</table>';

$pdf->writeHTML($tbltotal, true, false, false, false, '');

if (get_option('total_to_words_enabled') == 1) {
    // Set the font bold
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('num_word') . ': ' . $CI->numberword->convert($credit_note->total, $credit_note->currency_name), 0, 1, 'C', 0, '', 0);
    // Set the font again to normal like the rest of the pdf
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(4);
}

if (!empty($credit_note->clientnote)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('credit_note_client_note'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $credit_note->clientnote, 0, 1, false, true, 'L', true);
}

if (!empty($credit_note->terms)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('terms_and_conditions'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $credit_note->terms, 0, 1, false, true, 'L', true);
}
