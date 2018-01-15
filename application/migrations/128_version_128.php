<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_128 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {

        add_option('show_transactions_on_invoice_pdf', 1);
        add_option('show_pay_link_to_invoice_pdf', 1);
        add_option('tasks_kanban_limit', 50);

        $this->db->query("ALTER TABLE tblsessions CHANGE id id varchar(128) NOT NULL;");
        $this->db->query("ALTER TABLE  `tblstafftasks` ADD  `kanban_order` INT NOT NULL DEFAULT  '0' AFTER  `milestone` ,
ADD  `milestone_order` INT NOT NULL DEFAULT  '0' AFTER  `kanban_order` ;");

        $this->db->query("ALTER TABLE  `tblinvoicepaymentsmodes` ADD  `show_on_pdf` INT NOT NULL DEFAULT  '0' AFTER  `description` ;");
        $this->db->query("ALTER TABLE  `tblnotes` ADD  `date_contacted` DATETIME NULL DEFAULT NULL AFTER  `description` ;
");
        $this->db->query("ALTER TABLE  `tblcustomfields` ADD  `bs_column` INT NOT NULL DEFAULT  '12' AFTER  `disalow_client_to_edit` ;");

        $this->db->where('fieldto', 'leads');
        $cf_leads = $this->db->get('tblcustomfields')->result_array();
        foreach ($cf_leads as $cf) {
            $col = 12;
            if ($cf['type'] != 'textarea') {
                $col = 6;
            }
            $this->db->where('id', $cf['id']);
            $this->db->update('tblcustomfields', array(
                'bs_column' => $col
            ));
        }

        $this->db->query("ALTER TABLE `tblstafftasks` ADD `status` INT NOT NULL DEFAULT '0' AFTER `finished`;");

        $tasks = $this->db->get('tblstafftasks')->result_array();

        foreach ($tasks as $task) {
            $status = 1;
            if ($task['finished'] == 0) {
                if (date('Y-m-d') >= $task['startdate']) {
                    $status = 4;
                }
            } else {
                $status = 5;
            }

            $this->db->where('id', $task['id']);
            $this->db->update('tblstafftasks', array(
                'status' => $status
            ));

        }


        $this->db->query("ALTER TABLE `tblstafftasks` DROP `finished`;");


        $pdf_format_invoice = get_option('pdf_format_invoice');
        update_option('pdf_format_invoice', ($pdf_format_invoice == 'A4' ? 'A4-PORTRAIT' : 'A4-LANDSCAPE'));

        $pdf_format_estimate = get_option('pdf_format_estimate');
        update_option('pdf_format_estimate', ($pdf_format_estimate == 'A4' ? 'A4-PORTRAIT' : 'A4-LANDSCAPE'));

        $pdf_format_proposal = get_option('pdf_format_proposal');
        update_option('pdf_format_proposal', ($pdf_format_proposal == 'A4' ? 'A4-PORTRAIT' : 'A4-LANDSCAPE'));

        $pdf_format_payment = get_option('pdf_format_payment');
        update_option('pdf_format_payment', ($pdf_format_payment == 'A4' ? 'A4-PORTRAIT' : 'A4-LANDSCAPE'));

        $pdf_format_contract = get_option('pdf_format_contract');
        update_option('pdf_format_contract', ($pdf_format_contract == 'A4' ? 'A4-PORTRAIT' : 'A4-LANDSCAPE'));

        $this->db->query("ALTER TABLE  `tblstaff` ADD  `hourly_rate` DECIMAL( 11, 2 ) NOT NULL DEFAULT  '0' AFTER  `is_not_staff` ;");
        $this->db->query("ALTER TABLE  `tbltaskstimers` ADD  `hourly_rate` DECIMAL( 11, 2 ) NOT NULL DEFAULT  '0' AFTER  `staff_id` ;");

        $this->db->query("ALTER TABLE  `tbltaskstimers` ADD INDEX (  `task_id` ) ;");


        update_option('update_info_message', '<div class="col-md-12">
            <div class="alert alert-success bold">
                <h4 class="bold">Hi! Thanks for updating Perfex CRM - You are using version 1.2.8</h4>
                <p>
                    This window will reload automaticaly in 10 seconds and will try to clear your browser cache, however its recomended to clear your browser cache manually.
                </p>
            </div>
        </div>
        <script>
            setTimeout(function(){
                window.location.reload();
            },10000);
        </script>
        ');

    }
}
