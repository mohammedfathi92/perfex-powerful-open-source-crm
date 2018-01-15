<?php
$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column  = '';

$info_right_column = '<div style="color:#424242;">';
$info_right_column .= format_organization_info();
$info_right_column .= '</div>';

// write the first column
$info_left_column .= pdf_logo_url();
$pdf->MultiCell(($dimensions['wk'] / 2) - $dimensions['lm'], 0, $info_left_column, 0, 'J', 0, 0, '', '', true, 0, true, true, 0);
// write the second column
$pdf->MultiCell(($dimensions['wk'] / 2) - $dimensions['rm'], 0, $info_right_column, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(12);

// Get Y position for the separation
$y = $pdf->getY();

// Bill to
$client_details = '<b>' . _l('statement_bill_to') . '</b>';
$client_details .= '<div style="color:#424242;">';
$client_details .= format_customer_info($statement['client'], 'statement', 'billing');
$client_details .= '</div>';

$pdf->writeHTMLCell(($dimensions['wk'] / 2) - $dimensions['lm'] + 15, '', '', $y, $client_details, 0, 0, false, true, 'J', true);

$summary = '';
$summary .= '<h2>' . _l('account_summary') . '</h2>';
$summary .= '<div style="color:#676767;">' . _l('statement_from_to', array(
    _d($statement['from']),
    _d($statement['to'])
)) . '</div>';
$summary .= '<hr />';
$summary .= '
<table cellpadding="4" border="0" style="color:#424242;" width="100%">
   <tbody>
      <tr>
          <td align="left"><br /><br />' . _l('statement_beginning_balance') . ':</td>
          <td><br /><br />' . format_money($statement['beginning_balance'], $statement['currency']->symbol) . '</td>
      </tr>
      <tr>
          <td align="left">' . _l('invoiced_amount') . ':</td>
          <td>' . format_money($statement['invoiced_amount'], $statement['currency']->symbol) . '</td>
      </tr>
      <tr>
          <td align="left">' . _l('amount_paid') . ':</td>
          <td>' . format_money($statement['amount_paid'], $statement['currency']->symbol) . '</td>
      </tr>
  </tbody>
  <tfoot>
      <tr>
        <td align="left"><b>' . _l('balance_due') . '</b>:</td>
        <td>' . format_money($statement['balance_due'], $statement['currency']->symbol) . '</td>
    </tr>
  </tfoot>
</table>';

$pdf->writeHTMLCell(($dimensions['wk'] / 2) - $dimensions['rm'] - 15, '', '', '', $summary, 0, 1, false, true, 'R', true);


$summary_info = '
<div style="text-align: center;">
    ' . _l('customer_statement_info', array(
    _d($statement['from']),
    _d($statement['to'])
)) . '
</div>';

$pdf->ln(9);
$pdf->writeHTMLCell($dimensions['wk'] - ($dimensions['rm'] + $dimensions['lm']), '', '', $pdf->getY(), $summary_info, 0, 1, false, true, 'C', false);
$pdf->ln(9);

$tmpBeginningBalance = $statement['beginning_balance'];

$tblhtml = '<table width="100%" cellspacing="0" cellpadding="8" border="0">
<thead>
 <tr height="10" bgcolor="#e8e8e8" style="color:#424242;">
     <th width="13%"><b>' . _l('statement_heading_date') . '</b></th>
     <th width="27%"><b>' . _l('statement_heading_details') . '</b></th>
     <th align="right"><b>' . _l('statement_heading_amount') . '</b></th>
     <th align="right"><b>' . _l('statement_heading_payments') . '</b></th>
     <th align="right"><b>' . _l('statement_heading_balance') . '</b></th>
 </tr>
</thead>
<tbody>
 <tr>
     <td width="13%">' . _d($statement['from']) . '</td>
     <td width="27%">' . _l('statement_beginning_balance') . '</td>
     <td align="right">' . _format_number($statement['beginning_balance']) . '</td>
     <td></td>
     <td align="right">' . _format_number($statement['beginning_balance']) . '</td>
 </tr>';
$count   = 0;
foreach ($statement['result'] as $data) {
    $tblhtml .= '<tr' . (++$count % 2 ? " bgcolor=\"#f6f5f5\"" : "") . '>
  <td width="13%">' . _d($data['date']) . '</td>
  <td width="27%">';
    if (isset($data['invoice_id'])) {
        $tblhtml .= _l('statement_invoice_details', array(
            format_invoice_number($data['invoice_id']),
            _d($data['duedate'])
        ));
    } else if (isset($data['payment_id'])) {
        $tblhtml .= _l('statement_payment_details', array(
            '#' . $data['payment_id'],
            format_invoice_number($data['payment_invoice_id'])
        ));
    } elseif (isset($data['credit_note_id'])) {
        $tblhtml .= _l('statement_credit_note_details', format_credit_note_number($data['credit_note_id']));
    } elseif (isset($data['credit_id'])) {
        $tblhtml .= _l('statement_credits_applied_details', array(
            format_credit_note_number($data['credit_applied_credit_note_id']),
            _format_number($data['credit_amount']),
            format_invoice_number($data['credit_invoice_id'])
        ));
    }

    $tblhtml .= '</td>
    <td align="right">';
    if (isset($data['invoice_id'])) {
        $tblhtml .= _format_number($data['invoice_amount']);
    } else if (isset($data['credit_note_id'])) {
        $tblhtml .= _format_number($data['credit_note_amount']);
    }
    $tblhtml .= '</td>
        <td align="right">';
    if (isset($data['payment_id'])) {
        $tblhtml .= _format_number($data['payment_total']);
    }
    $tblhtml .= '</td>
            <td align="right">';
    if (isset($data['invoice_id'])) {
        $tmpBeginningBalance = ($tmpBeginningBalance + $data['invoice_amount']);
    } else if (isset($data['payment_id'])) {
        $tmpBeginningBalance = ($tmpBeginningBalance - $data['payment_total']);
    } else if (isset($data['credit_note_id'])) {
        $tmpBeginningBalance = ($tmpBeginningBalance - $data['credit_note_amount']);
    }
    if (!isset($data['credit_id'])) {
        $tblhtml .= _format_number($tmpBeginningBalance);
    }

    $tblhtml .= '</td>
            </tr>';
}
$tblhtml .= '</tbody>
        <tfoot>
         <tr style="color:#424242;">
             <td></td>
             <td></td>
             <td align="right"><b>' . _l('balance_due') . '</b></td>
             <td></td>
             <td align="right">
                 <b>' . format_money($statement['balance_due'], $statement['currency']->symbol) . '</b>
             </td>
         </tr>
     </tfoot>
 </table>';

$pdf->writeHTML($tblhtml, true, false, false, false, '');
