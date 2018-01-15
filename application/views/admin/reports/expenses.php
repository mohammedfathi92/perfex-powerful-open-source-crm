<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="pull-right">
                            <a href="<?php echo admin_url('reports/expenses/detailed_report'); ?>" class="btn btn-success"><?php echo _l('expenses_detailed_report'); ?></a>
                        </div>
                        <?php if($export_not_supported){ ?>
                        <p class="text-danger">Exporting not support in IE. To export this data please try another browser</p>
                        <?php } ?>
                        <a href="#" onclick="make_expense_pdf_export(); return false;" class="btn btn-default pull-left mright10<?php if($export_not_supported){echo ' disabled';} ?>"><i class="fa fa-file-pdf-o"></i></a>
                        <a download="expenses-report-<?php echo $current_year; ?>.xls" class="btn btn-default pull-left mright10<?php if($export_not_supported){echo ' disabled';} ?>" href="#" onclick="return ExcellentExport.excel(this, 'expenses-report-table', 'Expenses Report <?php echo $current_year; ?>');"><i class="fa fa-file-excel-o"></i></a>
                        <?php if(count($expense_years) > 0 ){ ?>
                        <select class="selectpicker" name="expense_year" onchange="filter_expenses();" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                            <?php foreach($expense_years as $year) { ?>
                            <option value="<?php echo $year['year']; ?>"<?php if($year['year'] == $current_year){echo 'selected';} ?>>
                                <?php echo $year['year']; ?>
                            </option>
                            <?php } ?>
                        </select>
                        <?php } ?>
                        <?php
                        $_currency = $base_currency;
                        if(is_using_multiple_currencies('tblexpenses')){ ?>
                        <div data-toggle="tooltip" class="pull-left mright5" title="<?php echo _l('report_expenses_base_currency_select_explanation'); ?>">
                            <select class="selectpicker" name="currencies" onchange="filter_expenses();"  data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" >
                                <?php foreach($currencies as $c) {
                                    $selected = '';
                                    if(!$this->input->get('currency')){
                                        if($c['id'] == $base_currency->id){
                                            $selected = 'selected';
                                            $_currency = $base_currency;
                                        }
                                    } else {
                                        if($this->input->get('currency') == $c['id']){
                                            $selected = 'selected';
                                            $_currency = $this->currencies_model->get($c['id']);
                                        }
                                    }
                                    ?>
                                    <option value="<?php echo $c['id']; ?>" <?php echo $selected; ?>>
                                        <?php echo $c['name']; ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="panel_s">
                        <div class="panel-body">
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="exclude_billable" onchange="filter_expenses();" id="exclude_billable" <?php if($this->input->get('exclude_billable')){echo 'checked';}; ?>>
                                <label for="exclude_billable"><?php echo _l('expenses_report_exclude_billable'); ?></label>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover expenses-report" id="expenses-report-table">
                                    <thead>
                                        <tr>
                                            <th class="bold"><?php echo _l('expense_report_category'); ?></th>
                                            <?php
                                            for ($m=1; $m<=12; $m++) {
                                                echo '  <th class="bold">' . _l(date('F', mktime(0,0,0,$m,1))) . '</th>';
                                            }
                                            ?>
                                            <th class="bold"> <?php echo _l('year'); ?> (<?php echo $current_year; ?>)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $taxTotal = array();
                                        $netAmount = array();
                                        $totalNetByExpenseCategory = array();
                                        foreach($categories as $category) { ?>
                                        <tr>
                                            <td class="bold"><?php echo $category['name']; ?></td>
                                            <?php
                                            for ($m=1; $m<=12; $m++) {
                                            // Set the monthly total expenses array
                                                if(!isset($netMonthlyTotal[$m])){
                                                    $netMonthlyTotal[$m] = array();
                                                }

                                                // Get the expenses
                                                $this->db->select('id')
                                                ->from('tblexpenses')
                                                ->where('MONTH(date)',$m)
                                                ->where('YEAR(date)',$current_year)
                                                ->where('category',$category['id'])
                                                ->where('currency',$_currency->id);

                                                if($this->input->get('exclude_billable')){
                                                    $this->db->where('billable',0);
                                                }

                                                $expenses = $this->db->get()->result_array();

                                                $total_expenses = array();
                                                echo '<td>';
                                                foreach($expenses as $expense){
                                                    $expense = $this->expenses_model->get($expense['id']);
                                                    $total = $expense->amount;

                                                    $totalTaxByExpense = 0;
                                                    // Check if tax is applied
                                                    if($expense->tax != 0){
                                                        $totalTaxByExpense += ($total / 100 * $expense->taxrate);
                                                    }

                                                    if($expense->tax2 != 0){
                                                        $totalTaxByExpense += ($expense->amount / 100 * $expense->taxrate2);
                                                    }

                                                    $taxTotal[$m][] = $totalTaxByExpense;
                                                    $total_expenses[] = $total;
                                                }
                                                $total_expenses = array_sum($total_expenses);
                                                // Add to total monthy expenses
                                                array_push($netMonthlyTotal[$m],$total_expenses);
                                                if(!isset($totalNetByExpenseCategory[$category['id']])){
                                                    $totalNetByExpenseCategory[$category['id']] = array();
                                                }
                                                array_push($totalNetByExpenseCategory[$category['id']],$total_expenses);
                                                // Output the total for this category
                                                if(count($categories) <= 8){
                                                    echo format_money($total_expenses,$_currency->symbol);
                                                } else {
                                                   // show tooltip for the month if more the 8 categories found. becuase when listing down you wont be able to see the month
                                                    echo '<span data-toggle="tooltip" title="'._l(date('F', mktime(0,0,0,$m,1))).'">'.format_money($total_expenses,$_currency->symbol) .'</span>';
                                                }
                                                echo '</td>';
                                                ?>
                                                <?php } ?>
                                                <td class="bg-odd">
                                                    <?php echo format_money(array_sum($totalNetByExpenseCategory[$category['id']]),$_currency->symbol); ?>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                            <?php $current_year_total = array(); ?>
                                            <tr class="bg-odd">
                                                <td class="bold text-info">
                                                    <?php echo _l('expenses_report_net'); ?>
                                                </td>
                                                <?php if(isset($netMonthlyTotal)) { ?>
                                                <?php foreach($netMonthlyTotal as $month => $total){
                                                    $total = array_sum($total);
                                                    $netMonthlyTotal[$month] = $total;
                                                    $current_year_total[] = $total;
                                                    ?>
                                                    <td class="bold">
                                                        <?php echo format_money($total,$_currency->symbol); ?>
                                                    </td>
                                                    <?php } ?>
                                                    <?php } ?>
                                                    <td class="bold bg-odd">
                                                        <?php
                                                        $totalNetByExpenseCategorySum = 0;
                                                        foreach($totalNetByExpenseCategory as $totalCat) {
                                                            $totalNetByExpenseCategorySum += array_sum($totalCat);
                                                        }
                                                        echo format_money($totalNetByExpenseCategorySum,$_currency->symbol);
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr class="bg-odd">
                                                    <td class="bold text-info"><?php echo _l('expenses_report_total_tax'); ?></td>
                                                    <?php
                                                    $taxYearlyTotal = 0;
                                                    for ($m=1; $m<=12; $m++) {
                                                        echo '<td class="bold">';
                                                        $taxMonth = isset($taxTotal[$m]) ? $taxTotal[$m] : array();
                                                        $t = array_sum($taxMonth);
                                                        $taxTotal[$m] = $t;
                                                        $taxYearlyTotal += $t;
                                                        echo format_money($t,$_currency->symbol);
                                                        echo '</td>';
                                                    }
                                                    echo '<td class="bold bg-odd">';
                                                    echo format_money($taxYearlyTotal,$_currency->symbol);
                                                    echo '</td>';
                                                    ?>
                                                </tr>
                                                <tr class="bg-odd">
                                                    <td class="bold text-info">
                                                        <?php echo _l('expenses_report_total'); ?>
                                                    </td>
                                                    <?php
                                                    if(isset($netMonthlyTotal)) {
                                                        for ($m=1; $m<=12; $m++) {
                                                            echo '<td class="bold">';
                                                            echo format_money($netMonthlyTotal[$m] + $taxTotal[$m],$_currency->symbol);
                                                            echo '</td>';
                                                        }
                                                        echo '<td class="bold bg-odd">';
                                                        echo format_money($totalNetByExpenseCategorySum + $taxYearlyTotal,$_currency->symbol);
                                                        echo '</td>';
                                                    }
                                                    ?>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <hr />
                                    <div class="row">
                                      <div class="col-md-6">
                                          <p class="text-muted mbot20"><?php echo _l('not_billable_expenses_by_categories'); ?></p>
                                      </div>
                                      <div class="col-md-6">
                                          <p class="text-muted mbot20"><?php echo _l('billable_expenses_by_categories'); ?></p>
                                      </div>
                                      <div class="col-md-6">
                                        <canvas id="expenses_chart_not_billable" height="390"></canvas>
                                    </div>
                                    <div class="col-md-6">
                                        <canvas id="expenses_chart_billable" height="390"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php init_tail(); ?>
        <script src="<?php echo base_url('assets/plugins/excellentexport/excellentexport.min.js'); ?>"></script>
        <script>
            new Chart($('#expenses_chart_not_billable'),{
                type:'bar',
                data: <?php echo $chart_not_billable; ?>,
                options:{
                    responsive:true,
                    maintainAspectRatio:false,
                    legend: {
                        display: false,
                    },
                    scales: {
                        yAxes: [{
                          ticks: {
                            beginAtZero: true,
                        }
                    }]
                }},
            });
            new Chart($('#expenses_chart_billable'),{
                type:'bar',
                data: <?php echo $chart_billable; ?>,
                options:{
                    responsive:true,
                    maintainAspectRatio:false,
                    legend: {
                        display: false,
                    },
                    scales: {
                        yAxes: [{
                          ticks: {
                            beginAtZero: true,
                        }
                    }]
                }},
            });
            function filter_expenses(){
                var parameters = new Array();
                var exclude_billable = ~~$('input[name="exclude_billable"]').prop('checked');
                var year = $('select[name="expense_year"]').val();
                var currency = ~~$('select[name="currencies"]').val();
                var location = window.location.href;
                location = location.split('?');
                if(exclude_billable){
                    parameters['exclude_billable'] = exclude_billable;
                }
                parameters['year'] = year;
                parameters['currency'] = currency;
                window.location.href = buildUrl(location[0], parameters);
            }
            function make_expense_pdf_export() {
                var body = [];
                var export_headings = [];
                var export_widths = [];
                var export_data = [];
                var headings = $('#expenses-report-table th');
                var data_tbody = $('#expenses-report-table tbody tr')
                var width = 47;
    // Prepare the pdf headings
    $.each(headings, function(i) {
        var heading = {};
        heading.text = stripTags($(this).text().trim());
        heading.fillColor = '#444A52';
        heading.color = '#fff';
        export_headings.push(heading);
        if(i == 0){
            export_widths.push(80);
        } else {
            export_widths.push(width);
        }
    });
    body.push(export_headings);
    // Categories total
    $.each(data_tbody, function() {
        var row = [];
        $.each($(this).find('td'), function() {
            var data = $(this);
            row.push($(data).text());
        });
       body.push(row);
    });


    // Pdf definition
    var docDefinition = {
        pageOrientation: 'landscape',
        pageMargins: [12, 12, 12, 12],
        "alignment":"center",
        content: [
        {
            text: '<?php echo _l("expenses_report_for"); ?> <?php echo $current_year; ?>:',
            bold: true,
            fontSize: 25,
            margin: [0, 5]
        },
        {
            text:'<?php echo get_option("companyname"); ?>',
            margin: [2,5]
        },
        {
            table: {
                headerRows: 1,
                widths: export_widths,
                body: body
            },
        }
        ],
        defaultStyle: {
            alignment: 'justify',
            fontSize: 10,
        }
    };
    // Open the pdf.
    pdfMake.createPdf(docDefinition).open();
}

</script>
</body>
</html>
