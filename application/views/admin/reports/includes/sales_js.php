<script>
 var salesChart;
 var groupsChart;
 var paymentMethodsChart;
 var customersTable;
 var report_from = $('input[name="report-from"]');
 var report_to = $('input[name="report-to"]');
 var report_customers = $('#customers-report');
 var report_customers_groups = $('#customers-group');
 var report_invoices = $('#invoices-report');
 var report_estimates = $('#estimates-report');
 var report_proposals = $('#proposals-reports');
 var report_items = $('#items-report');
 var report_credit_notes = $('#credit-notes');
 var report_payments_received = $('#payments-received-report');
 var date_range = $('#date-range');
 var report_from_choose = $('#report-time');
 var fnServerParams = {
   "report_months": '[name="months-report"]',
   "report_from": '[name="report-from"]',
   "report_to": '[name="report-to"]',
   "report_currency": '[name="currency"]',
   "invoice_status": '[name="invoice_status"]',
   "estimate_status": '[name="estimate_status"]',
   "sale_agent_invoices": '[name="sale_agent_invoices"]',
   "sale_agent_items": '[name="sale_agent_items"]',
   "sale_agent_estimates": '[name="sale_agent_estimates"]',
   "proposals_sale_agents": '[name="proposals_sale_agents"]',
   "proposal_status": '[name="proposal_status"]',
   "credit_note_status": '[name="credit_note_status"]',
 }
 $(function() {
   $('select[name="currency"],select[name="invoice_status"],select[name="estimate_status"],select[name="sale_agent_invoices"],select[name="sale_agent_items"],select[name="sale_agent_estimates"],select[name="payments_years"],select[name="proposals_sale_agents"],select[name="proposal_status"],select[name="credit_note_status"]').on('change', function() {
     gen_reports();
   });

   $('select[name="invoice_status"],select[name="estimate_status"],select[name="sale_agent_invoices"],select[name="sale_agent_items"],select[name="sale_agent_estimates"],select[name="proposals_sale_agents"],select[name="proposal_status"],select[name="credit_note_status"]').on('change', function() {
     var value = $(this).val();
     if (value != null) {
       if (value.indexOf('') > -1) {
         if (value.length > 1) {
           value.splice(0, 1);
           $(this).selectpicker('val', value);
         }
       }
     }
   });
   report_from.on('change', function() {
     var val = $(this).val();
     var report_to_val = report_to.val();
     if (val != '') {
       report_to.attr('disabled', false);
       if (report_to_val != '') {
         gen_reports();
       }
     } else {
       report_to.attr('disabled', true);
     }
   });

   report_to.on('change', function() {
     var val = $(this).val();
     if (val != '') {
       gen_reports();
     }
   });

   $('select[name="months-report"]').on('change', function() {
     var val = $(this).val();
     report_to.attr('disabled', true);
     report_to.val('');
     report_from.val('');
     if (val == 'custom') {
       date_range.addClass('fadeIn').removeClass('hide');
       return;
     } else {
       if (!date_range.hasClass('hide')) {
         date_range.removeClass('fadeIn').addClass('hide');
       }
     }
     gen_reports();
   });

   $('.table-payments-received-report').on('draw.dt', function() {
     var paymentReceivedReportsTable = $(this).DataTable();
     var sums = paymentReceivedReportsTable.ajax.json().sums;
     $(this).find('tfoot').addClass('bold');
     $(this).find('tfoot td').eq(0).html("<?php echo _l('invoice_total'); ?>");
     $(this).find('tfoot td.total').html(sums.total_amount);
   });

   $('.table-proposals-report').on('draw.dt', function() {
     var proposalsReportTable = $(this).DataTable();
     var sums = proposalsReportTable.ajax.json().sums;
      add_common_footer_sums($(this),sums);
      <?php foreach($proposal_taxes as $key => $tax){ ?>
        $(this).find('tfoot td.total_tax_single_<?php echo $key; ?>').html(sums['total_tax_single_<?php echo $key; ?>']);
     <?php } ?>
   });

   $('.table-invoices-report').on('draw.dt', function() {
     var invoiceReportsTable = $(this).DataTable();
     var sums = invoiceReportsTable.ajax.json().sums;
     add_common_footer_sums($(this),sums);
     $(this).find('tfoot td.amount_open').html(sums.amount_open);
     $(this).find('tfoot td.applied_credits').html(sums.applied_credits);
     <?php foreach($invoice_taxes as $key => $tax){ ?>
        $(this).find('tfoot td.total_tax_single_<?php echo $key; ?>').html(sums['total_tax_single_<?php echo $key; ?>']);
     <?php } ?>
   });

    $('.table-credit-notes-report').on('draw.dt', function() {
       var creditNotesTable = $(this).DataTable();
       var sums = creditNotesTable.ajax.json().sums;
       add_common_footer_sums($(this),sums);
       $(this).find('tfoot td.remaining_amount').html(sums.remaining_amount);
       <?php foreach($credit_note_taxes as $key => $tax){ ?>
          $(this).find('tfoot td.total_tax_single_<?php echo $key; ?>').html(sums['total_tax_single_<?php echo $key; ?>']);
       <?php } ?>
   });

   $('.table-estimates-report').on('draw.dt', function() {
     var estimatesReportsTable = $(this).DataTable();
     var sums = estimatesReportsTable.ajax.json().sums;
     add_common_footer_sums($(this),sums);
     <?php foreach($estimate_taxes as $key => $tax){ ?>
        $(this).find('tfoot td.total_tax_single_<?php echo $key; ?>').html(sums['total_tax_single_<?php echo $key; ?>']);
     <?php } ?>
   });

   $('.table-items-report').on('draw.dt', function() {
     var itemsTable = $(this).DataTable();
     var sums = itemsTable.ajax.json().sums;
     $(this).find('tfoot').addClass('bold');
     $(this).find('tfoot td').eq(0).html("<?php echo _l('invoice_total'); ?>");
     $(this).find('tfoot td.amount').html(sums.total_amount);
     $(this).find('tfoot td.qty').html(sums.total_qty);
   });

 });

  function add_common_footer_sums(table,sums) {
       table.find('tfoot').addClass('bold');
       table.find('tfoot td').eq(0).html("<?php echo _l('invoice_total'); ?>");
       table.find('tfoot td.subtotal').html(sums.subtotal);
       table.find('tfoot td.total').html(sums.total);
       table.find('tfoot td.total_tax').html(sums.total_tax);
       table.find('tfoot td.discount_total').html(sums.discount_total);
       table.find('tfoot td.adjustment').html(sums.adjustment);
  }

 function init_report(e, type) {
   var report_wrapper = $('#report');

   if (report_wrapper.hasClass('hide')) {
        report_wrapper.removeClass('hide');
   }

   $('head title').html($(e).text());
   $('.customers-group-gen').addClass('hide');

   report_credit_notes.addClass('hide');
   report_customers_groups.addClass('hide');
   report_customers.addClass('hide');
   report_invoices.addClass('hide');
   report_estimates.addClass('hide');
   report_payments_received.addClass('hide');
   report_items.addClass('hide');
   report_proposals.addClass('hide');

   $('#income-years').addClass('hide');
   $('.chart-income').addClass('hide');
   $('.chart-payment-modes').addClass('hide');


   report_from_choose.addClass('hide');

   $('select[name="months-report"]').selectpicker('val', 'this_month');
   // Clear custom date picker
       report_to.val('');
       report_from.val('');
       $('#currency').removeClass('hide');

       if (type != 'total-income' && type != 'payment-modes') {
         report_from_choose.removeClass('hide');
       }

       if (type == 'total-income') {
         $('.chart-income').removeClass('hide');
         $('#income-years').removeClass('hide');
         date_range.addClass('hide');
       } else if (type == 'customers-report') {
         report_customers.removeClass('hide');
       } else if (type == 'customers-group') {
         $('.customers-group-gen').removeClass('hide');
       } else if (type == 'invoices-report') {
         report_invoices.removeClass('hide');
       } else if (type == 'credit-notes') {
         report_credit_notes.removeClass('hide');
       } else if (type == 'payment-modes') {
         $('.chart-payment-modes').removeClass('hide');
         $('#income-years').removeClass('hide');
       } else if (type == 'payments-received') {
         report_payments_received.removeClass('hide');
       } else if (type == 'estimates-report') {
         report_estimates.removeClass('hide');
       } else if(type == 'proposals-report'){
        report_proposals.removeClass('hide');
      } else if(type == 'items-report'){
          report_items.removeClass('hide');
      }
      gen_reports();
    }


   // Generate total income bar
   function total_income_bar_report() {
     if (typeof(salesChart) !== 'undefined') {
       salesChart.destroy();
     }
     var data = {};
     data.year = $('select[name="payments_years"]').val();
     var currency = $('#currency');
     if (currency.length > 0) {
       data.report_currency = $('select[name="currency"]').val();
     }
     $.post(admin_url + 'reports/total_income_report', data).done(function(response) {
       response = JSON.parse(response);
       salesChart = new Chart($('#chart-income'), {
         type: 'bar',
         data: response,
         options: {
           responsive: true,
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
          },
        }
      })
     });
   }

   function report_by_payment_modes() {
     if (typeof(paymentMethodsChart) !== 'undefined') {
       paymentMethodsChart.destroy();
     }
     var data = {};
     data.year = $('select[name="payments_years"]').val();
     var currency = $('#currency');
     if (currency.length > 0) {
       data.report_currency = $('select[name="currency"]').val();
     }
     $.post(admin_url + 'reports/report_by_payment_modes', data).done(function(response) {
       response = JSON.parse(response);
       paymentMethodsChart = new Chart($('#chart-payment-modes'), {
         type: 'bar',
         data: response,
         options: {
           responsive: true,
           maintainAspectRatio:false,
           scales: {
            yAxes: [{
              ticks: {
                beginAtZero: true,
              }
            }]
          },
        }
      })
     });
   }
   // Generate customers report
   function customers_report() {
     if ($.fn.DataTable.isDataTable('.table-customers-report')) {
       $('.table-customers-report').DataTable().destroy();
     }
     initDataTable('.table-customers-report', admin_url + 'reports/customers_report', false, false, fnServerParams, [0, 'ASC']);
   }

   function report_by_customer_groups() {
     if (typeof(groupsChart) !== 'undefined') {
       groupsChart.destroy();
     }
     var data = {};
     data.months_report = $('select[name="months-report"]').val();
     data.report_from = report_from.val();
     data.report_to = report_to.val();

     var currency = $('#currency');
     if (currency.length > 0) {
       data.report_currency = $('select[name="currency"]').val();
     }
     $.post(admin_url + 'reports/report_by_customer_groups', data).done(function(response) {
       response = JSON.parse(response);
       groupsChart = new Chart($('#customers-group-gen'), {
         type: 'line',
         data: response,
         options:{
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
          }}
        });
     });
   }
   function invoices_report() {
     if ($.fn.DataTable.isDataTable('.table-invoices-report')) {
       $('.table-invoices-report').DataTable().destroy();
     }
     _table_api = initDataTable('.table-invoices-report', admin_url + 'reports/invoices_report', false, false, fnServerParams, [
       [2, 'DESC'],
       [0, 'DESC']
       ]).column(2).visible(false, false).columns.adjust();
   }

   function credit_notes_report(){

     if ($.fn.DataTable.isDataTable('.table-credit-notes-report')) {
       $('.table-credit-notes-report').DataTable().destroy();
     }
     _table_api = initDataTable('.table-credit-notes-report', admin_url + 'reports/credit_notes', false, false, fnServerParams,[1, 'DESC']);

   }

   function estimates_report() {
     if ($.fn.DataTable.isDataTable('.table-estimates-report')) {
       $('.table-estimates-report').DataTable().destroy();
     }
     _table_api = initDataTable('.table-estimates-report', admin_url + 'reports/estimates_report', false, false, fnServerParams, [
       [3, 'DESC'],
       [0, 'DESC']
       ]).column(3).visible(false, false).columns.adjust();
   }

   function payments_received_reports() {
     if ($.fn.DataTable.isDataTable('.table-payments-received-report')) {
       $('.table-payments-received-report').DataTable().destroy();
     }
     initDataTable('.table-payments-received-report', admin_url + 'reports/payments_received', false, false, fnServerParams, [1, 'DESC']);
   }

   function proposals_report(){
   if ($.fn.DataTable.isDataTable('.table-proposals-report')) {
     $('.table-proposals-report').DataTable().destroy();
   }

   initDataTable('.table-proposals-report', admin_url + 'reports/proposals_report', false, false, fnServerParams, [0, 'DESC']);
 }

 function items_report(){
   if ($.fn.DataTable.isDataTable('.table-items-report')) {
     $('.table-items-report').DataTable().destroy();
   }
   initDataTable('.table-items-report', admin_url + 'reports/items', false, false, fnServerParams, [0, 'ASC']);
 }

   // Main generate report function
   function gen_reports() {

     if (!$('.chart-income').hasClass('hide')) {
       total_income_bar_report();
     } else if (!$('.chart-payment-modes').hasClass('hide')) {
       report_by_payment_modes();
     } else if (!report_customers.hasClass('hide')) {
       customers_report();
     } else if (!$('.customers-group-gen').hasClass('hide')) {
       report_by_customer_groups();
     } else if (!report_invoices.hasClass('hide')) {
       invoices_report();
     } else if (!report_payments_received.hasClass('hide')) {
       payments_received_reports();
     } else if (!report_estimates.hasClass('hide')) {
       estimates_report();
     } else if(!report_proposals.hasClass('hide')){
      proposals_report();
    } else if(!report_items.hasClass('hide')) {
      items_report();
    } else if(!report_credit_notes.hasClass('hide')) {
      credit_notes_report();
    }
  }
</script>
