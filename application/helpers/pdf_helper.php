<?php

/**
 * Load PDF language for client
 * This is used eq if client have different language the system default language so in this case the PDF document will be on client language not on system language
 * @param  mixed $clientid client id
 * @return null
 */
function load_pdf_language($clientid)
{
    $CI =& get_instance();
    $lang     = get_option('active_language');
    // When cron or email sending pdf document the pdfs need to be on the client language
    $language = get_client_default_language($clientid);
    if (DEFINED('CRON') || DEFINED('EMAIL_TEMPLATE_SEND')) {
        if (!empty($language)) {
            $lang = $language;
        }
    } else {
        if (get_option('output_client_pdfs_from_admin_area_in_client_language') == 1) {
            if (!empty($language)) {
                $lang = $language;
            }
        }
    }

    if (file_exists(APPPATH . 'language/' . $lang)) {
        $CI->lang->load($lang . '_lang', $lang);
    }

    if (file_exists(APPPATH . 'language/' . $lang . '/custom_lang.php')) {
        $CI->lang->load('custom_lang', $lang);
    }
}

/**
 * Fetches custom pdf logo url for pdf or use the default logo uploaded for the company
 * Additional statements applied because this function wont work on all servers. All depends how the server is configured.
 * @return string
 */
function pdf_logo_url()
{
    $custom_pdf_logo_image_url = get_option('custom_pdf_logo_image_url');
    $width                     = get_option('pdf_logo_width');
    if ($width == '') {
        $width = 120;
    }
    if ($custom_pdf_logo_image_url != '') {
        if (strpos($custom_pdf_logo_image_url, 'localhost') !== false) {
            $cimg = $custom_pdf_logo_image_url;
        } elseif (strpos($custom_pdf_logo_image_url, 'http') === false) {
            $cimg = FCPATH . $custom_pdf_logo_image_url;
        } else {
            /*  $cimg = do_curl_pdf_image($custom_pdf_logo_image_url);
            $formImage = imagecreatefromstring(base64_decode(strafter($cimg,'base64,')));
            $w = imagesx($formImage);
            $h = imagesy($formImage);
            */
            if (_startsWith($custom_pdf_logo_image_url, site_url()) !== false) {
                $temp = str_replace(site_url(), '/', $custom_pdf_logo_image_url);
                $cimg = FCPATH . $temp;
                if (!file_exists($cimg)) {
                    $cimg = do_curl_pdf_image($custom_pdf_logo_image_url);
                }
            } else {
                $cimg = do_curl_pdf_image($custom_pdf_logo_image_url);
            }
        }
    } else {
        if (get_option('company_logo') != '') {
            $cimg = get_upload_path_by_type('company') . get_option('company_logo');
        } else {
            $cimg = '';
        }
    }
    $logo_url = '';
    if ($cimg != '') {
        $logo_url = '<img width="' . $width . 'px" src="' . $cimg . '">';
    }

    return do_action('pdf_logo_url', $logo_url);
}
/**
 * Fetch curl image and returns as data base64
 * @param  string $url
 * @return string
 */
function do_curl_pdf_image($url)
{
    $path_parts = pathinfo($url);

    if (!isset($path_parts['extension']) || (isset($path_parts['extension']) && $path_parts['extension'] == null)) {
        $extension = get_file_extension($url);
    } else {
        $extension = $path_parts['extension'];
    }
    // On some hosting providers you cant access directly the url and throwing error unable to get image size
    // Will simulate like browser access to get the image.
    $ch = curl_init();
    // set url
    curl_setopt($ch, CURLOPT_URL, $url);
    // Return the transfer as a image
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    // $output contains the output image
    $output = curl_exec($ch);
    // close curl resource to free up system resources
    curl_close($ch);

    return 'data:image/' . $extension . ';base64,' . base64_encode($output);
}
/**
 * Get PDF format page
 * Based on the options will return the formatted string that will be used in the PDF library
 * @param  string $option_name
 * @return array
 */
function get_pdf_format($option_name)
{
    $oFormat = strtoupper(get_option($option_name));
    $data    = array(
        'orientation' => '',
        'format' => '',
    );

    if ($oFormat == 'A4-PORTRAIT') {
        $data['orientation'] = 'P';
        $data['format']      = 'A4';
    } elseif ($oFormat == 'A4-LANDSCAPE') {
        $data['orientation'] = 'L';
        $data['format']      = 'A4';
    } elseif ($oFormat == 'LETTER-PORTRAIT') {
        $data['orientation'] = 'P';
        $data['format']      = 'LETTER';
    } else {
        // LETTER-LANDSCAPE
        $data['orientation'] = 'L';
        $data['format']      = 'LETTER';
    }

    return do_action('pdf_format_data', $data);
}

/**
 * Prepare general invoice pdf
 * @param  object $invoice Invoice as object with all necessary fields
 * @param  string $tag     tag for bulk pdf exporter
 * @return mixed object
 */
function invoice_pdf($invoice, $tag = '')
{
    $GLOBALS['invoice_pdf'] = $invoice;
    $CI =& get_instance();
    load_pdf_language($invoice->clientid);
    $CI->load->library('pdf');
    $invoice_number = format_invoice_number($invoice->id);

    $font_name      = get_option('pdf_font');
    $font_size      = get_option('pdf_font_size');

    if ($font_size == '') {
        $font_size = 10;
    }

    $CI->load->model('payment_modes_model');
    $payment_modes = $CI->payment_modes_model->get();

    // In case user want to include {invoice_number} in PDF offline mode description
    foreach ($payment_modes as $key => $mode) {
        if (isset($mode['description'])) {
            $payment_modes[$key]['description'] = str_replace('{invoice_number}', $invoice_number, $mode['description']);
        }
    }

    $whereCF = array('show_on_pdf'=>1);
    if (is_custom_fields_for_customers_portal()) {
        $whereCF['show_on_client_portal'] = 1;
    }

    // check for invoice custom fields which is checked show on pdf
    $pdf_custom_fields = get_custom_fields('invoice', $whereCF);

    $formatArray = get_pdf_format('pdf_format_invoice');
    if (!file_exists(APPPATH.'libraries/Invoice_pdf.php')) {
        $pdf         = new Pdf($formatArray['orientation'], 'mm', $formatArray['format'], true, 'UTF-8', false, false, 'invoice');
    } else {
        include_once(APPPATH.'libraries/Invoice_pdf.php');
        $pdf         = new Invoice_pdf($formatArray['orientation'], 'mm', $formatArray['format'], true, 'UTF-8', false, false);
    }

    if (defined('APP_PDF_MARGIN_LEFT') && defined('APP_PDF_MARGIN_TOP') && defined('APP_PDF_MARGIN_RIGHT')) {
        $pdf->SetMargins(APP_PDF_MARGIN_LEFT, APP_PDF_MARGIN_TOP, APP_PDF_MARGIN_RIGHT);
    }

    $pdf->SetTitle($invoice_number);

    $pdf->SetAutoPageBreak(true, (defined('APP_PDF_MARGIN_BOTTOM') ? APP_PDF_MARGIN_BOTTOM : PDF_MARGIN_BOTTOM));

    $pdf->SetAuthor(get_option('company'));
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->setImageScale(1.53);
    $pdf->setJPEGQuality(100);
    $pdf->AddPage($formatArray['orientation'], $formatArray['format']);

    if ($CI->input->get('print') == 'true') {
        // force print dialog
        $js = 'print(true);';
        $pdf->IncludeJS($js);
    }

    $status = $invoice->status;
    $swap   = get_option('swap_pdf_info');
    $CI->load->library('numberword', array(
        'clientid' => $invoice->clientid,
    ));
    $invoice = do_action('invoice_html_pdf_data', $invoice);

    _bulk_pdf_export_maybe_tag($tag, $pdf);
    if (file_exists(APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_invoicepdf.php')) {
        include(APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_invoicepdf.php');
    } else {
        include(APPPATH . 'views/themes/' . active_clients_theme() . '/views/invoicepdf.php');
    }

    if (ob_get_length() > 0 && ENVIRONMENT == 'production') {
        ob_end_clean();
    }

    return $pdf;
}

/**
 * Prepare general credit note pdf
 * @param  object $credit_note Credit note as object with all necessary fields
 * @param  string $tag tag for bulk pdf exported
 * @return mixed object
 */
function credit_note_pdf($credit_note, $tag = '')
{
    $GLOBALS['credit_note_pdf'] = $credit_note;
    $CI =& get_instance();
    load_pdf_language($credit_note->clientid);
    $CI->load->library('pdf');
    $credit_note_number = format_credit_note_number($credit_note->id);

    $font_name      = get_option('pdf_font');
    $font_size      = get_option('pdf_font_size');

    if ($font_size == '') {
        $font_size = 10;
    }

    $whereCF = array('show_on_pdf'=>1);
    if (is_custom_fields_for_customers_portal()) {
        $whereCF['show_on_client_portal'] = 1;
    }

    // check for credit note custom fields which is checked show on pdf
    $pdf_custom_fields = get_custom_fields('credit_note', $whereCF);

    $formatArray = get_pdf_format('pdf_format_credit_note');

    if (!file_exists(APPPATH.'libraries/Credit_note_pdf.php')) {
        $pdf         = new Pdf($formatArray['orientation'], 'mm', $formatArray['format'], true, 'UTF-8', false, false, 'credit_note');
    } else {
        include_once(APPPATH.'libraries/Credit_note_pdf.php');
        $pdf         = new Credit_note_pdf($formatArray['orientation'], 'mm', $formatArray['format'], true, 'UTF-8', false, false);
    }

    if (defined('APP_PDF_MARGIN_LEFT') && defined('APP_PDF_MARGIN_TOP') && defined('APP_PDF_MARGIN_RIGHT')) {
        $pdf->SetMargins(APP_PDF_MARGIN_LEFT, APP_PDF_MARGIN_TOP, APP_PDF_MARGIN_RIGHT);
    }

    $pdf->SetTitle($credit_note_number);

    $pdf->SetAutoPageBreak(true, (defined('APP_PDF_MARGIN_BOTTOM') ? APP_PDF_MARGIN_BOTTOM : PDF_MARGIN_BOTTOM));

    $pdf->SetAuthor(get_option('company'));
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->setImageScale(1.53);
    $pdf->setJPEGQuality(100);
    $pdf->AddPage($formatArray['orientation'], $formatArray['format']);

    if ($CI->input->get('print') == 'true') {
        // force print dialog
        $js = 'print(true);';
        $pdf->IncludeJS($js);
    }

    $swap   = get_option('swap_pdf_info');

    $CI->load->library('numberword', array(
        'clientid' => $credit_note->clientid,
    ));

    $credit_note = do_action('credit_note_html_pdf_data', $credit_note);

    _bulk_pdf_export_maybe_tag($tag, $pdf);
     if (file_exists(APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_credit_note_pdf.php')) {
        include(APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_credit_note_pdf.php');
    } else {
        include(APPPATH . 'views/themes/' . active_clients_theme() . '/views/credit_note_pdf.php');
    }

    if (ob_get_length() > 0 && ENVIRONMENT == 'production') {
        ob_end_clean();
    }

    return $pdf;
}

/**
 * Prepare general estimate pdf
 * @since  Version 1.0.2
 * @param  object $estimate estimate as object with all necessary fields
 * @param  string $tag tag for bulk pdf exporter
 * @return mixed object
 */
function estimate_pdf($estimate, $tag = '')
{
    $GLOBALS['estimate_pdf'] = $estimate;
    $CI =& get_instance();
    load_pdf_language($estimate->clientid);
    $CI->load->library('pdf');
    $estimate_number = format_estimate_number($estimate->id);
    $font_name       = get_option('pdf_font');
    $font_size       = get_option('pdf_font_size');

    if ($font_size == '') {
        $font_size = 10;
    }

    $whereCF = array('show_on_pdf'=>1);
    if (is_custom_fields_for_customers_portal()) {
        $whereCF['show_on_client_portal'] = 1;
    }

    $pdf_custom_fields = get_custom_fields('estimate', $whereCF);

    $formatArray = get_pdf_format('pdf_format_estimate');

    if (!file_exists(APPPATH.'libraries/Estimate_pdf.php')) {
        $pdf         = new Pdf($formatArray['orientation'], 'mm', $formatArray['format'], true, 'UTF-8', false, false, 'estimate');
    } else {
        include_once(APPPATH.'libraries/Estimate_pdf.php');
        $pdf         = new Estimate_pdf($formatArray['orientation'], 'mm', $formatArray['format'], true, 'UTF-8', false, false);
    }

    if (defined('APP_PDF_MARGIN_LEFT') && defined('APP_PDF_MARGIN_TOP') && defined('APP_PDF_MARGIN_RIGHT')) {
        $pdf->SetMargins(APP_PDF_MARGIN_LEFT, APP_PDF_MARGIN_TOP, APP_PDF_MARGIN_RIGHT);
    }

    $pdf->SetTitle($estimate_number);

    $pdf->SetAutoPageBreak(true, (defined('APP_PDF_MARGIN_BOTTOM') ? APP_PDF_MARGIN_BOTTOM : PDF_MARGIN_BOTTOM));
    $pdf->setImageScale(1.53);
    $pdf->SetAuthor(get_option('company'));
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->setJPEGQuality(100);
    $pdf->AddPage($formatArray['orientation'], $formatArray['format']);
    if ($CI->input->get('print') == 'true') {
        // force print dialog
        $js = 'print(true);';
        $pdf->IncludeJS($js);
    }
    $status = $estimate->status;
    $swap   = get_option('swap_pdf_info');
    $CI->load->library('numberword', array(
        'clientid' => $estimate->clientid,
    ));
    $estimate = do_action('estimate_html_pdf_data', $estimate);

    _bulk_pdf_export_maybe_tag($tag, $pdf);
    if (file_exists(APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_estimatepdf.php')) {
        include(APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_estimatepdf.php');
    } else {
        include(APPPATH . 'views/themes/' . active_clients_theme() . '/views/estimatepdf.php');
    }

    if (ob_get_length() > 0 && ENVIRONMENT == 'production') {
        ob_end_clean();
    }

    return $pdf;
}

/**
 * Function that generates proposal pdf for admin and clients area
 * @param  object $proposal
 * @param  string $tag      tag for bulk pdf exporter
 * @return object
 */
function proposal_pdf($proposal, $tag = '')
{
    $GLOBALS['proposal_pdf'] = $proposal;
    $CI =& get_instance();

    if ($proposal->rel_id != null && $proposal->rel_type == 'customer') {
        load_pdf_language($proposal->rel_id);
    }

    $CI->load->library('pdf');

    $number_word_lang_rel_id = 'unknown';
    if ($proposal->rel_type == 'customer') {
        $number_word_lang_rel_id = $proposal->rel_id;
    }
    $CI->load->library('numberword', array(
        'clientid' => $number_word_lang_rel_id,
    ));

    $formatArray = get_pdf_format('pdf_format_proposal');

    if (!file_exists(APPPATH.'libraries/Proposal_pdf.php')) {
        $pdf         = new Pdf($formatArray['orientation'], 'mm', $formatArray['format'], true, 'UTF-8', false, false, 'proposal');
    } else {
        include_once(APPPATH.'libraries/Proposal_pdf.php');
        $pdf         = new Proposal_pdf($formatArray['orientation'], 'mm', $formatArray['format'], true, 'UTF-8', false, false);
    }


    $font_name = get_option('pdf_font');
    $font_size = get_option('pdf_font_size');
    if ($font_size == '') {
        $font_size = 10;
    }

    $proposal_url = site_url('viewproposal/' . $proposal->id . '/' . $proposal->hash);
    $number       = format_proposal_number($proposal->id);

    $pdf->setImageScale(1.53);
    $pdf->SetAutoPageBreak(true, (defined('APP_PDF_MARGIN_BOTTOM') ? APP_PDF_MARGIN_BOTTOM : PDF_MARGIN_BOTTOM));
    $pdf->setJPEGQuality(100);

    if (defined('APP_PDF_MARGIN_LEFT') && defined('APP_PDF_MARGIN_TOP') && defined('APP_PDF_MARGIN_RIGHT')) {
        $pdf->SetMargins(APP_PDF_MARGIN_LEFT, APP_PDF_MARGIN_TOP, APP_PDF_MARGIN_RIGHT);
    }

    $pdf->SetTitle($number);
    $pdf->SetDisplayMode('default', 'OneColumn');
    $pdf->SetAuthor(get_option('company'));
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->AddPage($formatArray['orientation'], $formatArray['format']);
    if ($CI->input->get('print') == 'true') {
        // force print dialog
        $js = 'print(true);';
        $pdf->IncludeJS($js);
    }
    $swap = get_option('swap_pdf_info');
    $CI->load->model('currencies_model');
    $total = '';
    if ($proposal->total != 0) {
        $total = format_money($proposal->total, $CI->currencies_model->get($proposal->currency)->symbol);
        $total = _l('proposal_total') . ': ' . $total;
    }
    # Don't remove these lines - important for the PDF layout
    // Add <br /> tag and wrap over div element every image to prevent overlaping over text
    $proposal->content = preg_replace('/(<img[^>]+>(?:<\/img>)?)/i', '<div>$1</div>', $proposal->content);
    // Add cellpadding to all tables inside the html
    $proposal->content = preg_replace('/(<table\b[^><]*)>/i', '$1 cellpadding="4">', $proposal->content);
    // Remove white spaces cased by the html editor ex. <td>  item</td>
    $proposal->content = preg_replace('/[\t\n\r\0\x0B]/', '', $proposal->content);
    $proposal->content = preg_replace('/([\s])\1+/', ' ', $proposal->content);

    // Tcpdf does not support float css we need to adjust this here
    $proposal->content = str_replace('float: right', 'text-align: right', $proposal->content);
    $proposal->content = str_replace('float: left', 'text-align: left', $proposal->content);
    // Image center
    $proposal->content = str_replace('margin-left: auto; margin-right: auto;', 'text-align:center;', $proposal->content);

    $proposal          = do_action('proposal_html_pdf_data', $proposal);

    _bulk_pdf_export_maybe_tag($tag, $pdf);
    if (file_exists(APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_proposalpdf.php')) {
        include(APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_proposalpdf.php');
    } else {
        include(APPPATH . 'views/themes/' . active_clients_theme() . '/views/proposalpdf.php');
    }

    if (ob_get_length() > 0 && ENVIRONMENT == 'production') {
        ob_end_clean();
    }

    return $pdf;
}
/**
 * Generate contract pdf
 * @param  object $contract object db
 * @return mixed object
 */
function contract_pdf($contract)
{
    $GLOBALS['contract_pdf'] = $contract;
    $CI =& get_instance();
    $CI->load->library('pdf');

    $formatArray = get_pdf_format('pdf_format_contract');

    if (!file_exists(APPPATH.'libraries/Contract_pdf.php')) {
        $pdf         = new Pdf($formatArray['orientation'], 'mm', $formatArray['format'], true, 'UTF-8', false, false, 'contract');
    } else {
        include_once(APPPATH.'libraries/Contract_pdf.php');
        $pdf         = new Contract_pdf($formatArray['orientation'], 'mm', $formatArray['format'], true, 'UTF-8', false, false);
    }

    $font_name = get_option('pdf_font');
    $font_size = get_option('pdf_font_size');
    if ($font_size == '') {
        $font_size = 10;
    }

    $pdf->SetAutoPageBreak(true, (defined('APP_PDF_MARGIN_BOTTOM') ? APP_PDF_MARGIN_BOTTOM : PDF_MARGIN_BOTTOM));
    $pdf->setImageScale(1.53);

    if (defined('APP_PDF_MARGIN_LEFT') && defined('APP_PDF_MARGIN_TOP') && defined('APP_PDF_MARGIN_RIGHT')) {
        $pdf->SetMargins(APP_PDF_MARGIN_LEFT, APP_PDF_MARGIN_TOP, APP_PDF_MARGIN_RIGHT);
    }

    $pdf->SetTitle($contract->subject);
    $pdf->SetAuthor(get_option('company'));
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->AddPage($formatArray['orientation'], $formatArray['format']);
    if ($CI->input->get('print') == 'true') {
        // force print dialog
        $js = 'print(true);';
        $pdf->IncludeJS($js);
    }
    # Don't remove these lines - important for the PDF layout
    // Add <br /> tag and wrap over div element every image to prevent overlaping over text
    $contract->content = preg_replace('/(<img[^>]+>(?:<\/img>)?)/i', '<div>$1</div>', $contract->content);
    // Add cellpadding to all tables inside the html
    $contract->content = preg_replace('/(<table\b[^><]*)>/i', '$1 cellpadding="4">', $contract->content);
    // Remove white spaces cased by the html editor ex. <td>  item</td>
    $contract->content = preg_replace('/[\t\n\r\0\x0B]/', '', $contract->content);
    $contract->content = preg_replace('/([\s])\1+/', ' ', $contract->content);

    // Tcpdf does not support float css we need to adjust this here
    $contract->content = str_replace('float: right', 'text-align: right', $contract->content);
    $contract->content = str_replace('float: left', 'text-align: left', $contract->content);
    // Image center
    $contract->content = str_replace('margin-left: auto; margin-right: auto;', 'text-align:center;', $contract->content);

    if (file_exists(APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_contractpdf.php')) {
        include(APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_contractpdf.php');
    } else {
        include(APPPATH . 'views/themes/' . active_clients_theme() . '/views/contractpdf.php');
    }

    if (ob_get_length() > 0 && ENVIRONMENT == 'production') {
        ob_end_clean();
    }

    return $pdf;
}
/**
 * Generate payment pdf
 * @param  mixed $payment payment from database
 * @param  string $tag     tag for bulk pdf exporter
 * @return object
 */
function payment_pdf($payment, $tag = '')
{
    $GLOBALS['payment_pdf'] = $payment;
    $CI =& get_instance();
    load_pdf_language($payment->invoice_data->clientid);
    $CI->load->library('pdf');

    $formatArray = get_pdf_format('pdf_format_payment');

    if (!file_exists(APPPATH.'libraries/Payment_pdf.php')) {
        $pdf         = new Pdf($formatArray['orientation'], 'mm', $formatArray['format'], true, 'UTF-8', false, false, 'payment');
    } else {
        include_once(APPPATH.'libraries/Payment_pdf.php');
        $pdf         = new Payment_pdf($formatArray['orientation'], 'mm', $formatArray['format'], true, 'UTF-8', false, false);
    }

    $amountDue = ($payment->invoice_data->status != 2 && $payment->invoice_data->status != 5 ? true : false);

    $font_name = get_option('pdf_font');
    $font_size = get_option('pdf_font_size');

    if ($font_size == '') {
        $font_size = 10;
    }

    $swap = get_option('swap_pdf_info');

    if (defined('APP_PDF_MARGIN_LEFT') && defined('APP_PDF_MARGIN_TOP') && defined('APP_PDF_MARGIN_RIGHT')) {
        $pdf->SetMargins(APP_PDF_MARGIN_LEFT, APP_PDF_MARGIN_TOP, APP_PDF_MARGIN_RIGHT);
    }
    $pdf->SetTitle(_l('payment') . ' #' . $payment->paymentid);

    $pdf->SetAutoPageBreak(true, (defined('APP_PDF_MARGIN_BOTTOM') ? APP_PDF_MARGIN_BOTTOM : PDF_MARGIN_BOTTOM));
    $pdf->setImageScale(1.53);
    $pdf->setJPEGQuality(100);
    $pdf->SetAuthor(get_option('company'));
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->AddPage($formatArray['orientation'], $formatArray['format']);

    if ($CI->input->get('print') == 'true') {
        // force print dialog
        $js = 'print(true);';
        $pdf->IncludeJS($js);
    }

    _bulk_pdf_export_maybe_tag($tag, $pdf);
    if (file_exists(APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_paymentpdf.php')) {
        include(APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_paymentpdf.php');
    } else {
        include(APPPATH . 'views/themes/' . active_clients_theme() . '/views/paymentpdf.php');
    }

    if (ob_get_length() > 0 && ENVIRONMENT == 'production') {
        ob_end_clean();
    }

    return $pdf;
}

/**
 * Prepare customer statement pdf
 * @param  object $statement statement
 * @return mixed
 */
function statement_pdf($statement)
{
    $GLOBALS['statement_pdf'] = $statement;
    $CI =& get_instance();
    load_pdf_language($statement['client_id']);
    $CI->load->library('pdf');

    $font_name      = get_option('pdf_font');
    $font_size      = get_option('pdf_font_size');

    if ($font_size == '') {
        $font_size = 10;
    }

    $formatArray = get_pdf_format('pdf_format_statement');
    if (!file_exists(APPPATH.'libraries/Statement_pdf.php')) {
        $pdf         = new Pdf($formatArray['orientation'], 'mm', $formatArray['format'], true, 'UTF-8', false, false, 'statement');
    } else {
        include_once(APPPATH.'libraries/Statement_pdf.php');
        $pdf         = new Statement_pdf($formatArray['orientation'], 'mm', $formatArray['format'], true, 'UTF-8', false, false);
    }

    if (defined('APP_PDF_MARGIN_LEFT') && defined('APP_PDF_MARGIN_TOP') && defined('APP_PDF_MARGIN_RIGHT')) {
        $pdf->SetMargins(APP_PDF_MARGIN_LEFT, APP_PDF_MARGIN_TOP, APP_PDF_MARGIN_RIGHT);
    }
    $pdf->SetTitle(_l('account_summary'));

    $pdf->SetAutoPageBreak(true, (defined('APP_PDF_MARGIN_BOTTOM') ? APP_PDF_MARGIN_BOTTOM : PDF_MARGIN_BOTTOM));

    $pdf->SetAuthor(get_option('company'));
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->setImageScale(1.53);
    $pdf->setJPEGQuality(100);
    $pdf->AddPage($formatArray['orientation'], $formatArray['format']);

    if ($CI->input->get('print') == 'true') {
        // force print dialog
        $js = 'print(true);';
        $pdf->IncludeJS($js);
    }

    if (file_exists(APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_statementpdf.php')) {
        include(APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_statementpdf.php');
    } else {
        include(APPPATH . 'views/themes/' . active_clients_theme() . '/views/statementpdf.php');
    }

    if (ob_get_length() > 0 && ENVIRONMENT == 'production') {
        ob_end_clean();
    }

    return $pdf;
}

/**
 * This will add tag to PDF at the top right side
 * Only used when bulk pdf exporter feature is used from admin area
 * @param  string $tag  tag to check
 * @param  object &$pdf pdf instance
 * @return null
 */
function _bulk_pdf_export_maybe_tag($tag, &$pdf) {
    // Tag - used in BULK pdf exporter
    if ($tag != '') {
        $font_name      = get_option('pdf_font');
        $font_size      = get_option('pdf_font_size');

        if ($font_size == '') {
            $font_size = 10;
        }
        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetDrawColor(245, 245, 245);
        $pdf->SetXY(0, 0);
        $pdf->SetFont($font_name, 'B', 15);
        $pdf->SetTextColor(0);
        $pdf->SetLineWidth(0.75);
        $pdf->StartTransform();
        $pdf->Rotate(-35, 109, 235);
        $pdf->Cell(100, 1, mb_strtoupper($tag, 'UTF-8'), 'TB', 0, 'C', '1');
        $pdf->StopTransform();
        $pdf->SetFont($font_name, '', $font_size);
        $pdf->setX(10);
        $pdf->setY(10);
    }
}
