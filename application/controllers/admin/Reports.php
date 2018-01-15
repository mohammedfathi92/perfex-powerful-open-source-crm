<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Reports extends Admin_controller
{
    private $_instance;

    public function __construct()
    {
        parent::__construct();
        if (!has_permission('reports', '', 'view')) {
            access_denied('reports');
        }
        $this->_instance =& get_instance();
        $this->load->model('reports_model');
    }

    /* No access on this url */
    public function index()
    {
        redirect(admin_url());
    }

    /* See knowledge base article reports*/
    public function knowledge_base_articles()
    {
        $this->load->model('knowledge_base_model');
        $data['groups'] = $this->knowledge_base_model->get_kbg();
        $data['title']  = _l('kb_reports');
        $this->load->view('admin/reports/knowledge_base_articles', $data);
    }

/*
    public function tax_summary(){
       $this->load->model('taxes_model');
       $this->load->model('payments_model');
       $this->load->model('invoices_model');
       $data['taxes'] = $this->db->query("SELECT DISTINCT taxname,taxrate FROM tblitemstax WHERE rel_type='invoice'")->result_array();
        $this->load->view('admin/reports/tax_summary',$data);
    }*/
    /* Repoert leads conversions */
    public function leads()
    {
        $type = 'leads';
        if ($this->input->get('type')) {
            $type                       = $type . '_' . $this->input->get('type');
            $data['leads_staff_report'] = json_encode($this->reports_model->leads_staff_report());
        }
        $this->load->model('leads_model');
        $data['statuses']               = $this->leads_model->get_status();
        $data['leads_this_week_report'] = json_encode($this->reports_model->leads_this_week_report());
        $data['leads_sources_report']   = json_encode($this->reports_model->leads_sources_report());
        $this->load->view('admin/reports/' . $type, $data);
    }

    /* Sales reportts */
    public function sales()
    {

        $data['mysqlVersion'] = $this->db->query('SELECT VERSION() as version')->row();
        $data['sqlMode'] = $this->db->query('SELECT @@sql_mode as mode')->row();

        if (is_using_multiple_currencies() || is_using_multiple_currencies('tblcreditnotes') || is_using_multiple_currencies('tblestimates') || is_using_multiple_currencies('tblproposals')) {
            $this->load->model('currencies_model');
            $data['currencies'] = $this->currencies_model->get();
        }
        $this->load->model('invoices_model');
        $this->load->model('estimates_model');
        $this->load->model('proposals_model');
        $this->load->model('credit_notes_model');

        $data['credit_notes_statuses']      = $this->credit_notes_model->get_statuses();
        $data['invoice_statuses']      = $this->invoices_model->get_statuses();
        $data['estimate_statuses']     = $this->estimates_model->get_statuses();
        $data['payments_years']        = $this->reports_model->get_distinct_payments_years();
        $data['estimates_sale_agents'] = $this->estimates_model->get_sale_agents();

        $data['invoices_sale_agents']  = $this->invoices_model->get_sale_agents();

        $data['proposals_sale_agents']  = $this->proposals_model->get_sale_agents();
        $data['proposals_statuses'] = $this->proposals_model->get_statuses();

        $data['invoice_taxes'] = $this->distinct_taxes('invoice');
        $data['estimate_taxes'] = $this->distinct_taxes('estimate');
        $data['proposal_taxes'] = $this->distinct_taxes('proposal');
        $data['credit_note_taxes'] = $this->distinct_taxes('credit_note');


        $data['title']                 = _l('sales_reports');
        $this->load->view('admin/reports/sales', $data);
    }

    /* Customer report */
    public function customers_report()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');
            $select = array(
                get_sql_select_client_company(),
                '(SELECT COUNT(clientid) FROM tblinvoices WHERE tblinvoices.clientid = tblclients.userid AND status != 5)',
                '(SELECT SUM(subtotal) - SUM(discount_total) FROM tblinvoices WHERE tblinvoices.clientid = tblclients.userid AND status != 5)',
                '(SELECT SUM(total) FROM tblinvoices WHERE tblinvoices.clientid = tblclients.userid AND status != 5)',
            );

            $custom_date_select = $this->get_where_report_period();
            if ($custom_date_select != '') {
                $i = 0;
                foreach ($select as $_select) {
                    if ($i !== 0) {
                        $_temp = substr($_select, 0, -1);
                        $_temp .= ' ' . $custom_date_select . ')';
                        $select[$i] = $_temp;
                    }
                    $i++;
                }
            }
            $by_currency     = $this->input->post('report_currency');
            $currency        = $this->currencies_model->get_base_currency();
            $currency_symbol = $this->currencies_model->get_currency_symbol($currency->id);
            if ($by_currency) {
                $i = 0;
                foreach ($select as $_select) {
                    if ($i !== 0) {
                        $_temp = substr($_select, 0, -1);
                        $_temp .= ' AND currency =' . $by_currency . ')';
                        $select[$i] = $_temp;
                    }
                    $i++;
                }
                $currency        = $this->currencies_model->get($by_currency);
                $currency_symbol = $this->currencies_model->get_currency_symbol($currency->id);
            }
            $aColumns     = $select;
            $sIndexColumn = "userid";
            $sTable       = 'tblclients';
            $where        = array();

            $result  = data_tables_init($aColumns, $sIndexColumn, $sTable, array(), $where, array(
                'userid'
            ));
            $output  = $result['output'];
            $rResult = $result['rResult'];
            $x       = 0;
            foreach ($rResult as $aRow) {
                $row = array();
                for ($i = 0; $i < count($aColumns); $i++) {
                    if (strpos($aColumns[$i], 'as') !== false && !isset($aRow[$aColumns[$i]])) {
                        $_data = $aRow[strafter($aColumns[$i], 'as ')];
                    } else {
                        $_data = $aRow[$aColumns[$i]];
                    }
                    if ($i == 0) {
                        $_data = '<a href="' . admin_url('clients/client/' . $aRow['userid']) . '" target="_blank">' . $aRow['company'] . '</a>';
                    } elseif ($aColumns[$i] == $select[2] || $aColumns[$i] == $select[3]) {
                        if ($_data == null) {
                            $_data = 0;
                        }
                        $_data = format_money($_data, $currency_symbol);
                    }
                    $row[] = $_data;
                }
                $output['aaData'][] = $row;
                $x++;
            }
            echo json_encode($output);
            die();
        }
    }

    public function payments_received()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');
            $this->load->model('payment_modes_model');
            $online_modes = $this->payment_modes_model->get_online_payment_modes(true);
            $select       = array(
                'tblinvoicepaymentrecords.id',
                'tblinvoicepaymentrecords.date',
                'invoiceid',
                get_sql_select_client_company(),
                'paymentmode',
                'transactionid',
                'note',
                'amount'
            );
            $where        = array(
                'AND status != 5'
            );

            $custom_date_select = $this->get_where_report_period('tblinvoicepaymentrecords.date');
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            $by_currency = $this->input->post('report_currency');
            if ($by_currency) {
                $currency = $this->currencies_model->get($by_currency);
                array_push($where, 'AND currency=' . $by_currency);
            } else {
                $currency = $this->currencies_model->get_base_currency();
            }

            $currency_symbol = $this->currencies_model->get_currency_symbol($currency->id);

            $aColumns     = $select;
            $sIndexColumn = "id";
            $sTable       = 'tblinvoicepaymentrecords';
            $join         = array(
                'JOIN tblinvoices ON tblinvoices.id = tblinvoicepaymentrecords.invoiceid',
                'LEFT JOIN tblclients ON tblclients.userid = tblinvoices.clientid',
                'LEFT JOIN tblinvoicepaymentsmodes ON tblinvoicepaymentsmodes.id = tblinvoicepaymentrecords.paymentmode'
            );

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, array(
                'number',
                'clientid',
                'tblinvoicepaymentsmodes.name',
                'tblinvoicepaymentsmodes.id as paymentmodeid',
                'paymentmethod'
            ));

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data['total_amount'] = 0;
            foreach ($rResult as $aRow) {
                $row = array();
                for ($i = 0; $i < count($aColumns); $i++) {
                    if (strpos($aColumns[$i], 'as') !== false && !isset($aRow[$aColumns[$i]])) {
                        $_data = $aRow[strafter($aColumns[$i], 'as ')];
                    } else {
                        $_data = $aRow[$aColumns[$i]];
                    }
                    if ($aColumns[$i] == 'paymentmode') {
                        $_data = $aRow['name'];
                        if (is_null($aRow['paymentmodeid'])) {
                            foreach ($online_modes as $online_mode) {
                                if ($aRow['paymentmode'] == $online_mode['id']) {
                                    $_data = $online_mode['name'];
                                }
                            }
                        }
                        if (!empty($aRow['paymentmethod'])) {
                            $_data .= ' - ' . $aRow['paymentmethod'];
                        }
                    } elseif ($aColumns[$i] == 'tblinvoicepaymentrecords.id') {
                        $_data = '<a href="' . admin_url('payments/payment/' . $_data) . '" target="_blank">' . $_data . '</a>';
                    } elseif ($aColumns[$i] == 'tblinvoicepaymentrecords.date') {
                        $_data = _d($_data);
                    } elseif ($aColumns[$i] == 'invoiceid') {
                        $_data = '<a href="' . admin_url('invoices/list_invoices/' . $aRow[$aColumns[$i]]) . '" target="_blank">' . format_invoice_number($aRow['invoiceid']) . '</a>';
                    } elseif ($i == 3) {
                        $_data = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '" target="_blank">' . $aRow['company'] . '</a>';
                    } elseif ($aColumns[$i] == 'amount') {
                        $footer_data['total_amount'] += $_data;
                        $_data = format_money($_data, $currency_symbol);
                    }

                    $row[] = $_data;
                }
                $output['aaData'][] = $row;
            }

            $footer_data['total_amount'] = format_money($footer_data['total_amount'], $currency_symbol);
            $output['sums']              = $footer_data;
            echo json_encode($output);
            die();
        }
    }

    public function proposals_report()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');
            $this->load->model('proposals_model');

            $proposalsTaxes = $this->distinct_taxes('proposal');
            $totalTaxesColumns = count($proposalsTaxes);

            $select = array(
                'id',
                'subject',
                'proposal_to',
                'date',
                'open_till',
                'subtotal',
                'total',
                'total_tax',
                'discount_total',
                'adjustment',
                'status'
            );

            $proposalsTaxesSelect = array_reverse($proposalsTaxes);

            foreach($proposalsTaxesSelect as $key => $tax){
                 array_splice($select, 8, 0, '(
                    SELECT CASE
                    WHEN discount_percent != 0 AND discount_type = "before_tax" THEN ROUND(SUM((qty*rate/100*tblitemstax.taxrate) - (qty*rate/100*tblitemstax.taxrate * discount_percent/100)),'.get_decimal_places().')
                    ELSE ROUND(SUM(qty*rate/100*tblitemstax.taxrate),'.get_decimal_places().')
                    END
                    FROM tblitems_in
                    INNER JOIN tblitemstax ON tblitemstax.itemid=tblitems_in.id
                    WHERE tblitems_in.rel_type="proposal" AND taxname="'.$tax['taxname'].'" AND taxrate="'.$tax['taxrate'].'" AND tblitems_in.rel_id=tblproposals.id) as total_tax_single_'.$key);
            }

            $where              = array();
            $custom_date_select = $this->get_where_report_period();
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            if ($this->input->post('proposal_status')) {
                $statuses  = $this->input->post('proposal_status');
                $_statuses = array();
                if (is_array($statuses)) {
                    foreach ($statuses as $status) {
                        if ($status != '') {
                            array_push($_statuses, $status);
                        }
                    }
                }
                if (count($_statuses) > 0) {
                    array_push($where, 'AND status IN (' . implode(', ', $_statuses) . ')');
                }
            }

            if ($this->input->post('proposals_sale_agents')) {
                $agents  = $this->input->post('proposals_sale_agents');
                $_agents = array();
                if (is_array($agents)) {
                    foreach ($agents as $agent) {
                        if ($agent != '') {
                            array_push($_agents, $agent);
                        }
                    }
                }
                if (count($_agents) > 0) {
                    array_push($where, 'AND assigned IN (' . implode(', ', $_agents) . ')');
                }
            }


            $by_currency = $this->input->post('report_currency');
            if ($by_currency) {
                $currency = $this->currencies_model->get($by_currency);
                array_push($where, 'AND currency=' . $by_currency);
            } else {
                $currency = $this->currencies_model->get_base_currency();
            }

            $currency_symbol = $this->currencies_model->get_currency_symbol($currency->id);

            $aColumns     = $select;
            $sIndexColumn = "id";
            $sTable       = 'tblproposals';
            $join         = array();

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, array(
                'rel_id',
                'rel_type',
                'discount_percent'
            ));

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = array(
                'total' => 0,
                'subtotal' => 0,
                'total_tax' => 0,
                'discount_total' => 0,
                'adjustment' => 0
            );

            foreach($proposalsTaxes as $key => $tax){
                $footer_data['total_tax_single_'.$key] = 0;
            }

            foreach ($rResult as $aRow) {
                $row = array();

                $row[] = '<a href="'.admin_url('proposals/list_proposals/'.$aRow['id']).'" target="_blank">' . format_proposal_number($aRow['id']) . '</a>';

                $row[] = '<a href="'.admin_url('proposals/list_proposals/'.$aRow['id']).'" target="_blank">' . $aRow['subject'] . '</a>';

                if ($aRow['rel_type'] == 'lead') {
                    $row[] = '<a href="#" onclick="init_lead('.$aRow['rel_id'].');return false;" target="_blank" data-toggle="tooltip" data-title="'._l('lead').'">'.$aRow['proposal_to'].'</a>'. '<span class="hide">'._l('lead').'</span>';
                } elseif ($aRow['rel_type'] == 'customer') {
                    $row[] = '<a href="'.admin_url('clients/client/'.$aRow['rel_id']).'" target="_blank" data-toggle="tooltip" data-title="'._l('client').'">'.$aRow['proposal_to'].'</a>' . '<span class="hide">'._l('client').'</span>';
                } else {
                    $row[] = '';
                }

                $row[] = _d($aRow['date']);

                $row[] = _d($aRow['open_till']);

                $row[] = format_money($aRow['subtotal'],$currency_symbol);
                $footer_data['subtotal'] += $aRow['subtotal'];

                $row[] = format_money($aRow['total'],$currency_symbol);
                $footer_data['total'] += $aRow['total'];

                $row[] = format_money($aRow['total_tax'],$currency_symbol);
                $footer_data['total_tax'] += $aRow['total_tax'];

                $t = $totalTaxesColumns - 1;
                $i = 0;
                foreach($proposalsTaxes as $tax){
                    $row[] = format_money(($aRow['total_tax_single_'.$t] == null ? 0 : $aRow['total_tax_single_'.$t]),$currency_symbol);
                    $footer_data['total_tax_single_'.$i] += ($aRow['total_tax_single_'.$t] == null ? 0 : $aRow['total_tax_single_'.$t]);
                    $t--;
                    $i++;
                }

                $row[] = format_money($aRow['discount_total'],$currency_symbol);
                $footer_data['discount_total'] += $aRow['discount_total'];

                $row[] = format_money($aRow['adjustment'],$currency_symbol);
                $footer_data['adjustment'] += $aRow['adjustment'];

                $row[] = format_proposal_status($aRow['status']);
                $output['aaData'][] = $row;
            }

            foreach ($footer_data as $key => $total) {
                $footer_data[$key] = format_money($total, $currency_symbol);
            }

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();
        }
    }

    public function estimates_report()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');
            $this->load->model('estimates_model');

            $estimateTaxes = $this->distinct_taxes('estimate');
            $totalTaxesColumns = count($estimateTaxes);

            $select = array(
                'number',
                get_sql_select_client_company(),
                'invoiceid',
                'YEAR(date) as year',
                'date',
                'expirydate',
                'subtotal',
                'total',
                'total_tax',
                'discount_total',
                'adjustment',
                'reference_no',
                'status'
            );

            $estimatesTaxesSelect = array_reverse($estimateTaxes);

            foreach($estimatesTaxesSelect as $key => $tax){
                 array_splice($select, 9, 0, '(
                    SELECT CASE
                    WHEN discount_percent != 0 AND discount_type = "before_tax" THEN ROUND(SUM((qty*rate/100*tblitemstax.taxrate) - (qty*rate/100*tblitemstax.taxrate * discount_percent/100)),'.get_decimal_places().')
                    ELSE ROUND(SUM(qty*rate/100*tblitemstax.taxrate),'.get_decimal_places().')
                    END
                    FROM tblitems_in
                    INNER JOIN tblitemstax ON tblitemstax.itemid=tblitems_in.id
                    WHERE tblitems_in.rel_type="estimate" AND taxname="'.$tax['taxname'].'" AND taxrate="'.$tax['taxrate'].'" AND tblitems_in.rel_id=tblestimates.id) as total_tax_single_'.$key);
            }

            $where              = array();
            $custom_date_select = $this->get_where_report_period();
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            if ($this->input->post('estimate_status')) {
                $statuses  = $this->input->post('estimate_status');
                $_statuses = array();
                if (is_array($statuses)) {
                    foreach ($statuses as $status) {
                        if ($status != '') {
                            array_push($_statuses, $status);
                        }
                    }
                }
                if (count($_statuses) > 0) {
                    array_push($where, 'AND status IN (' . implode(', ', $_statuses) . ')');
                }
            }

            if ($this->input->post('sale_agent_estimates')) {
                $agents  = $this->input->post('sale_agent_estimates');
                $_agents = array();
                if (is_array($agents)) {
                    foreach ($agents as $agent) {
                        if ($agent != '') {
                            array_push($_agents, $agent);
                        }
                    }
                }
                if (count($_agents) > 0) {
                    array_push($where, 'AND sale_agent IN (' . implode(', ', $_agents) . ')');
                }
            }

            $by_currency = $this->input->post('report_currency');
            if ($by_currency) {
                $currency = $this->currencies_model->get($by_currency);
                array_push($where, 'AND currency=' . $by_currency);
            } else {
                $currency = $this->currencies_model->get_base_currency();
            }
            $currency_symbol = $this->currencies_model->get_currency_symbol($currency->id);

            $aColumns     = $select;
            $sIndexColumn = "id";
            $sTable       = 'tblestimates';
            $join         = array(
                'JOIN tblclients ON tblclients.userid = tblestimates.clientid'
            );

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, array(
                'userid',
                'clientid',
                'tblestimates.id',
                'discount_percent'
            ));

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = array(
                'total' => 0,
                'subtotal' => 0,
                'total_tax' => 0,
                'discount_total' => 0,
                'adjustment' => 0
            );

            foreach($estimateTaxes as $key => $tax){
                $footer_data['total_tax_single_'.$key] = 0;
            }

            foreach ($rResult as $aRow) {
                $row = array();

                $row[] = '<a href="' . admin_url('estimates/list_estimates/' . $aRow['id']) . '" target="_blank">' . format_estimate_number($aRow['id']) . '</a>';

                $row[] = '<a href="' . admin_url('clients/client/' . $aRow['userid']) . '" target="_blank">' . $aRow['company'] . '</a>';

                if($aRow['invoiceid'] === null){
                    $row[] = '';
                } else {
                    $row[] = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['invoiceid']) . '" target="_blank">' . format_invoice_number($aRow['invoiceid']) . '</a>';
                }

                $row[] = $aRow['year'];

                $row[] = _d($aRow['date']);

                $row[] = _d($aRow['expirydate']);

                $row[] = format_money($aRow['subtotal'],$currency_symbol);
                $footer_data['subtotal'] += $aRow['subtotal'];

                $row[] = format_money($aRow['total'],$currency_symbol);
                $footer_data['total'] += $aRow['total'];

                $row[] = format_money($aRow['total_tax'],$currency_symbol);
                $footer_data['total_tax'] += $aRow['total_tax'];

                $t = $totalTaxesColumns - 1;
                $i = 0;
                foreach($estimateTaxes as $tax){
                    $row[] = format_money(($aRow['total_tax_single_'.$t] == null ? 0 : $aRow['total_tax_single_'.$t]),$currency_symbol);
                    $footer_data['total_tax_single_'.$i] += ($aRow['total_tax_single_'.$t] == null ? 0 : $aRow['total_tax_single_'.$t]);
                    $t--;
                    $i++;
                }

                $row[] = format_money($aRow['discount_total'],$currency_symbol);
                $footer_data['discount_total'] += $aRow['discount_total'];

                $row[] = format_money($aRow['adjustment'],$currency_symbol);
                $footer_data['adjustment'] += $aRow['adjustment'];


                $row[] = $aRow['reference_no'];

                $row[] = format_estimate_status($aRow['status']);

                $output['aaData'][] = $row;
            }
            foreach ($footer_data as $key => $total) {
                $footer_data[$key] = format_money($total, $currency_symbol);
            }
            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();
        }
    }

    private function get_where_report_period($field = 'date')
    {
        $months_report      = $this->input->post('report_months');
        $custom_date_select = '';
        if ($months_report != '') {
            if (is_numeric($months_report)) {
                // Last month
               if($months_report == '1'){
                   $beginMonth = date('Y-m-01', strtotime("-$months_report MONTH"));
                   $endMonth   = date('Y-m-t', strtotime('-1 MONTH'));
               } else {
                   $months_report = (int) $months_report;
                   $months_report--;
                   $beginMonth = date('Y-m-01', strtotime("-$months_report MONTH"));
                   $endMonth   = date('Y-m-t');
               }
               $custom_date_select = 'AND (' . $field . ' BETWEEN "' . $beginMonth . '" AND "' . $endMonth . '")';
            } elseif($months_report == 'this_month'){
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' . date('Y-m-01') . '" AND "' . date('Y-m-t') . '")';
            } elseif($months_report == 'this_year'){
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' .
                date('Y-m-d',strtotime(date('Y-01-01'))) .
                '" AND "' .
                date('Y-m-d',strtotime(date('Y-12-'.date('d',strtotime('last day of this year'))))) . '")';
            } elseif($months_report == 'last_year'){
             $custom_date_select = 'AND (' . $field . ' BETWEEN "' .
                date('Y-m-d',strtotime(date(date('Y',strtotime('last year')).'-01-01'))) .
                '" AND "' .
                date('Y-m-d',strtotime(date(date('Y',strtotime('last year')). '-12-'.date('d',strtotime('last day of last year'))))) . '")';
            } elseif ($months_report == 'custom') {
                $from_date = to_sql_date($this->input->post('report_from'));
                $to_date   = to_sql_date($this->input->post('report_to'));
                if ($from_date == $to_date) {
                    $custom_date_select = 'AND ' . $field . ' = "' . $from_date . '"';
                } else {
                    $custom_date_select = 'AND (' . $field . ' BETWEEN "' . $from_date . '" AND "' . $to_date . '")';
                }
            }
        }

        return $custom_date_select;
    }

    public function items(){
        if ($this->input->is_ajax_request()) {

            $this->load->model('currencies_model');
            $v = $this->db->query('SELECT VERSION() as version')->row();
            // 5.6 mysql version don't have the ANY_VALUE function implemented.

            if($v && strpos($v->version,'5.7') !== FALSE) {
                   $aColumns = array(
                        'ANY_VALUE(description) as description',
                        'ANY_VALUE((SUM(tblitems_in.qty))) as quantity_sold',
                        'ANY_VALUE(SUM(rate*qty)) as rate',
                        'ANY_VALUE(AVG(rate*qty)) as avg_price'
                    );
            } else {
                    $aColumns = array(
                        'description as description',
                        '(SUM(tblitems_in.qty)) as quantity_sold',
                        'SUM(rate*qty) as rate',
                        'AVG(rate*qty) as avg_price'
                    );
            }

            $sIndexColumn = "id";
            $sTable       = 'tblitems_in';
            $join         = array('JOIN tblinvoices ON tblinvoices.id = tblitems_in.rel_id');

            $where = array('AND rel_type="invoice"','AND status != 5','AND status=2');

            $custom_date_select = $this->get_where_report_period();
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }
            $by_currency = $this->input->post('report_currency');
            if ($by_currency) {
                $currency = $this->currencies_model->get($by_currency);
                array_push($where, 'AND currency=' . $by_currency);
            } else {
                $currency = $this->currencies_model->get_base_currency();
            }

            if ($this->input->post('sale_agent_items')) {
                $agents  = $this->input->post('sale_agent_items');
                $_agents = array();
                if (is_array($agents)) {
                    foreach ($agents as $agent) {
                        if ($agent != '') {
                            array_push($_agents, $agent);
                        }
                    }
                }
                if (count($_agents) > 0) {
                    array_push($where, 'AND sale_agent IN (' . implode(', ', $_agents) . ')');
                }
            }

            $currency_symbol = $this->currencies_model->get_currency_symbol($currency->id);
            $result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, array(),"GROUP by description");

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = array(
                'total_amount' => 0,
                'total_qty' => 0,
            );

            foreach ($rResult as $aRow) {
                $row = array();

                $row[] = $aRow['description'];
                $row[] = $aRow['quantity_sold'];
                $row[] = format_money($aRow['rate'],$currency_symbol);
                $row[] = format_money($aRow['avg_price'],$currency_symbol);
                $footer_data['total_amount'] += $aRow['rate'];
                $footer_data['total_qty'] += $aRow['quantity_sold'];
                $output['aaData'][] = $row;

            }

            $footer_data['total_amount'] = format_money($footer_data['total_amount'],$currency_symbol);

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();
        }
    }

     public function credit_notes()
    {
        if ($this->input->is_ajax_request()) {
            $credit_note_taxes = $this->distinct_taxes('credit_note');
            $totalTaxesColumns = count($credit_note_taxes);

            $this->load->model('currencies_model');

            $select = array(
                'number',
                'date',
                get_sql_select_client_company(),
                'reference_no',
                'subtotal',
                'total',
                'total_tax',
                'discount_total',
                'adjustment',
                '(SELECT tblcreditnotes.total - (SELECT COALESCE(SUM(amount),0) FROM tblcredits WHERE tblcredits.credit_id=tblcreditnotes.id)) as remaining_amount',
                'status'
            );

            $where  = array();

            $credit_note_taxes_select = array_reverse($credit_note_taxes);

            foreach($credit_note_taxes_select as $key => $tax){
                 array_splice($select, 5, 0, '(
                    SELECT CASE
                    WHEN discount_percent != 0 AND discount_type = "before_tax" THEN ROUND(SUM((qty*rate/100*tblitemstax.taxrate) - (qty*rate/100*tblitemstax.taxrate * discount_percent/100)),'.get_decimal_places().')
                    ELSE ROUND(SUM(qty*rate/100*tblitemstax.taxrate),'.get_decimal_places().')
                    END
                    FROM tblitems_in
                    INNER JOIN tblitemstax ON tblitemstax.itemid=tblitems_in.id
                    WHERE tblitems_in.rel_type="credit_note" AND taxname="'.$tax['taxname'].'" AND taxrate="'.$tax['taxrate'].'" AND tblitems_in.rel_id=tblcreditnotes.id) as total_tax_single_'.$key);
            }

            $custom_date_select = $this->get_where_report_period();

            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            $by_currency = $this->input->post('report_currency');

            if ($by_currency) {
                $currency = $this->currencies_model->get($by_currency);
                array_push($where, 'AND currency=' . $by_currency);
            } else {
                $currency = $this->currencies_model->get_base_currency();
            }

            if ($this->input->post('credit_note_status')) {
                $statuses  = $this->input->post('credit_note_status');
                $_statuses = array();
                if (is_array($statuses)) {
                    foreach ($statuses as $status) {
                        if ($status != '') {
                            array_push($_statuses, $status);
                        }
                    }
                }
                if (count($_statuses) > 0) {
                    array_push($where, 'AND status IN (' . implode(', ', $_statuses) . ')');
                }
            }

            $aColumns     = $select;
            $sIndexColumn = "id";
            $sTable       = 'tblcreditnotes';
            $join         = array(
                'JOIN tblclients ON tblclients.userid = tblcreditnotes.clientid'
            );

            $result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, array(
                'userid',
                'clientid',
                'tblcreditnotes.id',
                'discount_percent'
            ));

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = array(
                'total' => 0,
                'subtotal' => 0,
                'total_tax' => 0,
                'discount_total' => 0,
                'adjustment' => 0,
                'remaining_amount' => 0,
            );

            foreach($credit_note_taxes as $key => $tax){
                $footer_data['total_tax_single_'.$key] = 0;
            }

            foreach ($rResult as $aRow) {
                $row = array();

                $row[] = '<a href="' . admin_url('credit_notes/list_credit_notes/' . $aRow['id']) . '" target="_blank">' . format_credit_note_number($aRow['id']) . '</a>';

                $row[] = _d($aRow['date']);

                $row[] = '<a href="' . admin_url('clients/client/' . $aRow['userid']) . '" target="_blank">' . $aRow['company'] . '</a>';

                $row[] = $aRow['reference_no'];

                $row[] = format_money($aRow['subtotal'],$currency->symbol);
                $footer_data['subtotal'] += $aRow['subtotal'];

                $row[] = format_money($aRow['total'],$currency->symbol);
                $footer_data['total'] += $aRow['total'];

                $row[] = format_money($aRow['total_tax'],$currency->symbol);
                $footer_data['total_tax'] += $aRow['total_tax'];

                $t = $totalTaxesColumns - 1;
                $i = 0;
                foreach($credit_note_taxes as $tax){
                    $row[] = format_money(($aRow['total_tax_single_'.$t] == null ? 0 : $aRow['total_tax_single_'.$t]),$currency->symbol);
                    $footer_data['total_tax_single_'.$i] += ($aRow['total_tax_single_'.$t] == null ? 0 : $aRow['total_tax_single_'.$t]);
                    $t--;
                    $i++;
                }

                $row[] = format_money($aRow['discount_total'],$currency->symbol);
                $footer_data['discount_total'] += $aRow['discount_total'];

                $row[] = format_money($aRow['adjustment'],$currency->symbol);
                $footer_data['adjustment'] += $aRow['adjustment'];

                $row[] = format_money($aRow['remaining_amount'],$currency->symbol);
                $footer_data['remaining_amount'] += $aRow['remaining_amount'];

                $row[] = format_credit_note_status($aRow['status']);

                $output['aaData'][] = $row;
            }

            foreach ($footer_data as $key => $total) {
                $footer_data[$key] = format_money($total, $currency->symbol);
            }

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();
        }
    }

    public function invoices_report()
    {
        if ($this->input->is_ajax_request()) {
            $invoice_taxes = $this->distinct_taxes('invoice');
            $totalTaxesColumns = count($invoice_taxes);

            $this->load->model('currencies_model');
            $this->load->model('invoices_model');

            $select = array(
                'number',
                get_sql_select_client_company(),
                'YEAR(date) as year',
                'date',
                'duedate',
                'subtotal',
                'total',
                'total_tax',
                'discount_total',
                'adjustment',
                '(SELECT COALESCE(SUM(amount),0) FROM tblcredits WHERE tblcredits.invoice_id=tblinvoices.id) as credits_applied',
                '(SELECT total - (SELECT COALESCE(SUM(amount),0) FROM tblinvoicepaymentrecords WHERE invoiceid = tblinvoices.id) - (SELECT COALESCE(SUM(amount),0) FROM tblcredits WHERE tblcredits.invoice_id=tblinvoices.id))',
                'status'
            );

            $where  = array(
                'AND status != 5'
            );

            $invoiceTaxesSelect = array_reverse($invoice_taxes);

            foreach($invoiceTaxesSelect as $key => $tax){
                 array_splice($select, 8, 0, '(
                    SELECT CASE
                    WHEN discount_percent != 0 AND discount_type = "before_tax" THEN ROUND(SUM((qty*rate/100*tblitemstax.taxrate) - (qty*rate/100*tblitemstax.taxrate * discount_percent/100)),'.get_decimal_places().')
                    ELSE ROUND(SUM(qty*rate/100*tblitemstax.taxrate),'.get_decimal_places().')
                    END
                    FROM tblitems_in
                    INNER JOIN tblitemstax ON tblitemstax.itemid=tblitems_in.id
                    WHERE tblitems_in.rel_type="invoice" AND taxname="'.$tax['taxname'].'" AND taxrate="'.$tax['taxrate'].'" AND tblitems_in.rel_id=tblinvoices.id) as total_tax_single_'.$key);
            }

            $custom_date_select = $this->get_where_report_period();
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            if ($this->input->post('sale_agent_invoices')) {
                $agents  = $this->input->post('sale_agent_invoices');
                $_agents = array();
                if (is_array($agents)) {
                    foreach ($agents as $agent) {
                        if ($agent != '') {
                            array_push($_agents, $agent);
                        }
                    }
                }
                if (count($_agents) > 0) {
                    array_push($where, 'AND sale_agent IN (' . implode(', ', $_agents) . ')');
                }
            }

            $by_currency = $this->input->post('report_currency');
            $totalPaymentsColumnIndex = (12+$totalTaxesColumns-1);

            if ($by_currency) {
                $_temp = substr($select[$totalPaymentsColumnIndex], 0, -2);
                $_temp .= ' AND currency =' . $by_currency . ')) as amount_open';
                $select[$totalPaymentsColumnIndex] = $_temp;

                $currency = $this->currencies_model->get($by_currency);
                array_push($where, 'AND currency=' . $by_currency);
            } else {
                $currency = $this->currencies_model->get_base_currency();
                $select[$totalPaymentsColumnIndex] = $select[$totalPaymentsColumnIndex] .= ' as amount_open';
            }

            $currency_symbol = $currency->symbol;

            if ($this->input->post('invoice_status')) {
                $statuses  = $this->input->post('invoice_status');
                $_statuses = array();
                if (is_array($statuses)) {
                    foreach ($statuses as $status) {
                        if ($status != '') {
                            array_push($_statuses, $status);
                        }
                    }
                }
                if (count($_statuses) > 0) {
                    array_push($where, 'AND status IN (' . implode(', ', $_statuses) . ')');
                }
            }

            $aColumns     = $select;
            $sIndexColumn = "id";
            $sTable       = 'tblinvoices';
            $join         = array(
                'JOIN tblclients ON tblclients.userid = tblinvoices.clientid'
            );

            $result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, array(
                'userid',
                'clientid',
                'tblinvoices.id',
                'discount_percent'
            ));

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = array(
                'total' => 0,
                'subtotal' => 0,
                'total_tax' => 0,
                'discount_total' => 0,
                'adjustment' => 0,
                'applied_credits' => 0,
                'amount_open' => 0
            );

            foreach($invoice_taxes as $key => $tax){
                $footer_data['total_tax_single_'.$key] = 0;
            }

            foreach ($rResult as $aRow) {
                $row = array();

                $row[] = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['id']) . '" target="_blank">' . format_invoice_number($aRow['id']) . '</a>';

                $row[] = '<a href="' . admin_url('clients/client/' . $aRow['userid']) . '" target="_blank">' . $aRow['company'] . '</a>';

                $row[] = $aRow['year'];

                $row[] = _d($aRow['date']);

                $row[] = _d($aRow['duedate']);

                $row[] = format_money($aRow['subtotal'],$currency_symbol);
                $footer_data['subtotal'] += $aRow['subtotal'];

                $row[] = format_money($aRow['total'],$currency_symbol);
                $footer_data['total'] += $aRow['total'];

                $row[] = format_money($aRow['total_tax'],$currency_symbol);
                $footer_data['total_tax'] += $aRow['total_tax'];

                $t = $totalTaxesColumns - 1;
                $i = 0;
                foreach($invoice_taxes as $tax){
                    $row[] = format_money(($aRow['total_tax_single_'.$t] == null ? 0 : $aRow['total_tax_single_'.$t]),$currency_symbol);
                    $footer_data['total_tax_single_'.$i] += ($aRow['total_tax_single_'.$t] == null ? 0 : $aRow['total_tax_single_'.$t]);
                    $t--;
                    $i++;
                }

                $row[] = format_money($aRow['discount_total'],$currency_symbol);
                $footer_data['discount_total'] += $aRow['discount_total'];

                $row[] = format_money($aRow['adjustment'],$currency_symbol);
                $footer_data['adjustment'] += $aRow['adjustment'];

                $row[] = format_money($aRow['credits_applied'],$currency_symbol);
                $footer_data['applied_credits'] += $aRow['credits_applied'];

                $amountOpen = $aRow['amount_open'];
                $row[] = format_money($amountOpen,$currency_symbol);
                $footer_data['amount_open'] += $amountOpen;

                $row[] = format_invoice_status($aRow['status']);

                $output['aaData'][] = $row;
            }

            foreach ($footer_data as $key => $total) {
                $footer_data[$key] = format_money($total, $currency_symbol);
            }

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();
        }
    }

    public function expenses($type = 'simple_report')
    {
        $this->load->model('currencies_model');
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $data['currencies']    = $this->currencies_model->get();

        $data['title'] = _l('expenses_report');
        if ($type != 'simple_report') {
            $this->load->model('expenses_model');
            $data['categories'] = $this->expenses_model->get_category();
            $data['years']      = $this->expenses_model->get_expenses_years();

            if ($this->input->is_ajax_request()) {
                $aColumns = array(
                    'category',
                    'amount',
                    'tax',
                    'tax2',
                    '(SELECT taxrate FROM tbltaxes WHERE id=tblexpenses.tax)',
                    'amount as amount_with_tax',
                    'billable',
                    'date',
                    get_sql_select_client_company(),
                    'invoiceid',
                    'reference_no',
                    'paymentmode'
                );
                $join     = array(
                    'LEFT JOIN tblclients ON tblclients.userid = tblexpenses.clientid',
                    'LEFT JOIN tblexpensescategories ON tblexpensescategories.id = tblexpenses.category'
                );
                $where    = array();
                $filter   = array();
                include_once(APPPATH . 'views/admin/tables/includes/expenses_filter.php');
                if (count($filter) > 0) {
                    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
                }

                $by_currency = $this->input->post('currency');
                if ($by_currency) {
                    $currency = $this->currencies_model->get($by_currency);
                    array_push($where, 'AND currency=' . $by_currency);
                } else {
                    $currency = $this->currencies_model->get_base_currency();
                }
                $currency_symbol = $this->currencies_model->get_currency_symbol($currency->id);

                $sIndexColumn = "id";
                $sTable       = 'tblexpenses';
                $result       = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, array(
                    'tblexpensescategories.name as category_name',
                    'tblexpenses.id',
                    'tblexpenses.clientid',
                    'currency'
                ));
                $output       = $result['output'];
                $rResult      = $result['rResult'];
                $this->load->model('currencies_model');
                $this->load->model('payment_modes_model');

                $footer_data = array(
                    'amount' => 0,
                    'total_tax' => 0,
                    'amount_with_tax' => 0
                );

                foreach ($rResult as $aRow) {
                    $row = array();
                    for ($i = 0; $i < count($aColumns); $i++) {
                        if (strpos($aColumns[$i], 'as') !== false && !isset($aRow[$aColumns[$i]])) {
                            $_data = $aRow[strafter($aColumns[$i], 'as ')];
                        } else {
                            $_data = $aRow[$aColumns[$i]];
                        }
                        if ($aRow['tax'] != 0) {
                            $_tax = get_tax_by_id($aRow['tax']);
                        }
                        if ($aRow['tax2'] != 0) {
                            $_tax2 = get_tax_by_id($aRow['tax2']);
                        }
                        if ($aColumns[$i] == 'category') {
                            $_data = '<a href="' . admin_url('expenses/list_expenses/' . $aRow['id']) . '" target="_blank">' . $aRow['category_name'] . '</a>';
                        } elseif ($aColumns[$i] == 'amount' || $i == 5) {
                            $total = $_data;
                            if ($i != 5) {
                                $footer_data['amount'] += $total;
                            } else {
                                if ($aRow['tax'] != 0 && $i == 5) {
                                    $total += ($total / 100 * $_tax->taxrate);
                                }
                                if ($aRow['tax2'] != 0 && $i == 5) {
                                    $total += ($aRow['amount'] / 100 * $_tax2->taxrate);
                                }
                                $footer_data['amount_with_tax'] += $total;
                            }

                            $_data = format_money($total, $currency_symbol);
                        } elseif ($i == 8) {
                            $_data = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . $aRow['company'] . '</a>';
                        } elseif ($aColumns[$i] == 'paymentmode') {
                            $_data = '';
                            if ($aRow['paymentmode'] != '0' && !empty($aRow['paymentmode'])) {
                                $payment_mode = $this->payment_modes_model->get($aRow['paymentmode'], array(), false, true);
                                if ($payment_mode) {
                                    $_data = $payment_mode->name;
                                }
                            }
                        } elseif ($aColumns[$i] == 'date') {
                            $_data = _d($_data);
                        } elseif ($aColumns[$i] == 'tax') {
                            if ($aRow['tax'] != 0) {
                                $_data = $_tax->name . ' - ' . _format_number($_tax->taxrate) . '%';
                            } else {
                                $_data = '';
                            }
                        } elseif ($aColumns[$i] == 'tax2') {
                            if ($aRow['tax2'] != 0) {
                                $_data = $_tax2->name . ' - ' . _format_number($_tax2->taxrate) . '%';
                            } else {
                                $_data = '';
                            }
                        } elseif ($i == 4) {
                            if ($aRow['tax'] != 0 || $aRow['tax2'] != 0) {
                                if($aRow['tax'] != 0){
                                    $total = ($total / 100 * $_tax->taxrate);
                                }
                                if($aRow['tax2'] != 0){
                                    $total += ($aRow['amount'] / 100 * $_tax2->taxrate);
                                }
                                $_data = format_money($total, $currency_symbol);
                                $footer_data['total_tax'] += $total;
                            } else {
                                $_data = _format_number(0);
                            }
                        } elseif ($aColumns[$i] == 'billable') {
                            if ($aRow['billable'] == 1) {
                                $_data = _l('expenses_list_billable');
                            } else {
                                $_data = _l('expense_not_billable');
                            }
                        } elseif ($aColumns[$i] == 'invoiceid') {
                            if ($_data) {
                                $_data = '<a href="' . admin_url('invoices/list_invoices/' . $_data) . '">' . format_invoice_number($_data) . '</a>';
                            } else {
                                $_data = '';
                            }
                        }
                        $row[] = $_data;
                    }
                    $output['aaData'][] = $row;
                }

                foreach ($footer_data as $key => $total) {
                    $footer_data[$key] = format_money($total, $currency_symbol);
                }

                $output['sums'] = $footer_data;
                echo json_encode($output);
                die;
            }
            $this->load->view('admin/reports/expenses_detailed', $data);
        } else {
            if (!$this->input->get('year')) {
                $data['current_year'] = date('Y');
            } else {
                $data['current_year'] = $this->input->get('year');
            }


            $data['export_not_supported'] = ($this->agent->browser() == 'Internet Explorer' || $this->agent->browser() == 'Spartan');

            $this->load->model('expenses_model');

            $data['chart_not_billable'] = json_encode($this->reports_model->get_stats_chart_data(_l('not_billable_expenses_by_categories'), array(
                'billable' => 0
            ), array(
                'backgroundColor' => 'rgba(252,45,66,0.4)',
                'borderColor' => '#fc2d42'
            ), $data['current_year']));

            $data['chart_billable'] = json_encode($this->reports_model->get_stats_chart_data(_l('billable_expenses_by_categories'), array(
                'billable' => 1
            ), array(
                'backgroundColor' => 'rgba(37,155,35,0.2)',
                'borderColor' => '#84c529'
            ), $data['current_year']));

            $data['expense_years'] = $this->expenses_model->get_expenses_years();
            $data['categories']    = $this->expenses_model->get_category();

            $this->load->view('admin/reports/expenses', $data);
        }
    }

    public function expenses_vs_income($year = '')
    {
        $_expenses_years = array();
        $_years          = array();
        $this->load->model('expenses_model');
        $expenses_years = $this->expenses_model->get_expenses_years();
        $payments_years = $this->reports_model->get_distinct_payments_years();
        foreach ($expenses_years as $y) {
            array_push($_years, $y['year']);
        }
        foreach ($payments_years as $y) {
            array_push($_years, $y['year']);
        }
        $_years                                  = array_map("unserialize", array_unique(array_map("serialize", $_years)));
        $data['years']                           = $_years;
        $data['chart_expenses_vs_income_values'] = json_encode($this->reports_model->get_expenses_vs_income_report($year));
        $data['title']                           = _l('als_expenses_vs_income');
        $this->load->view('admin/reports/expenses_vs_income', $data);
    }

    /* Total income report / ajax chart*/
    public function total_income_report()
    {
        echo json_encode($this->reports_model->total_income_report());
    }

    public function report_by_payment_modes()
    {
        echo json_encode($this->reports_model->report_by_payment_modes());
    }

    public function report_by_customer_groups()
    {
        echo json_encode($this->reports_model->report_by_customer_groups());
    }

    /* Leads conversion monthly report / ajax chart*/
    public function leads_monthly_report($month)
    {
        echo json_encode($this->reports_model->leads_monthly_report($month));
    }

    private function distinct_taxes($rel_type){
        return $this->db->query("SELECT DISTINCT taxname,taxrate FROM tblitemstax WHERE rel_type='".$rel_type."' ORDER BY taxname ASC")->result_array();
    }
}
