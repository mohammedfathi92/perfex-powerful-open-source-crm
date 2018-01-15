<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_120 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        if(!is_dir(ESTIMATE_ATTACHMENTS_FOLDER)){
            mkdir(ESTIMATE_ATTACHMENTS_FOLDER);
            fopen(ESTIMATE_ATTACHMENTS_FOLDER . '.htaccess', 'w');
            $fp = fopen(ESTIMATE_ATTACHMENTS_FOLDER.'.htaccess','a+');
            if($fp)
            {
                fwrite($fp,'Order Deny,Allow'.PHP_EOL.'Deny from all');
                fclose($fp);
            }
        }
        if(!is_dir(PROPOSAL_ATTACHMENTS_FOLDER)){
            mkdir(PROPOSAL_ATTACHMENTS_FOLDER);
            fopen(PROPOSAL_ATTACHMENTS_FOLDER . '.htaccess', 'w');
            $fp = fopen(PROPOSAL_ATTACHMENTS_FOLDER.'.htaccess','a+');
            if($fp)
            {
                fwrite($fp,'Order Deny,Allow'.PHP_EOL.'Deny from all');
                fclose($fp);
            }
        }

        add_option('pdf_format_invoice','A4');
        add_option('pdf_format_estimate','A4');
        add_option('pdf_format_proposal','A4');
        add_option('pdf_format_payment','A4');
        add_option('pdf_format_contract','A4');
        add_option('pdf_text_color','#000000');
        add_option('auto_check_for_new_notifications','0');
        add_option('swap_pdf_info','0');

        $this->db->query("ALTER TABLE `tblestimates` CHANGE `discount_percent` `discount_percent` DECIMAL(11,2) NULL DEFAULT '0.00';");
        $this->db->query("ALTER TABLE `tblestimates` CHANGE `discount_total` `discount_total` DECIMAL(11,2) NULL DEFAULT '0.00';");

        $this->db->query("ALTER TABLE `tblinvoices` CHANGE `discount_percent` `discount_percent` DECIMAL(11,2) NULL DEFAULT '0.00';");
        $this->db->query("ALTER TABLE `tblinvoices` CHANGE `discount_total` `discount_total` DECIMAL(11,2) NULL DEFAULT '0.00';");

        $this->db->query("ALTER TABLE `tblinvoices` ADD `recurring_ends_on` DATE NULL AFTER `recurring`;");
        $this->db->query("ALTER TABLE `tblexpenses` ADD `recurring_ends_on` DATE NULL AFTER `recurring`;");

        $this->db->query("ALTER TABLE `tblstafftaskassignees` ADD `assigned_from` INT NOT NULL DEFAULT '0' AFTER `taskid`;");
        $this->db->query("ALTER TABLE `tblestimates` CHANGE `currency` `currency` INT NOT NULL;");

        $this->db->query("ALTER TABLE `tblsurveysemailsendcron` ADD `log_id` INT NOT NULL AFTER `listid`;");
        $this->db->query("ALTER TABLE `tblcontacts` ADD INDEX(`userid`);");
        $this->db->query("ALTER TABLE `tblstafftaskassignees` ADD INDEX(`taskid`);");
        $this->db->query("ALTER TABLE `tblstafftaskassignees` ADD INDEX(`staffid`);");
        $this->db->query("ALTER TABLE `tblcustomergroups_in` ADD INDEX(`groupid`);");
        $this->db->query("ALTER TABLE `tblcustomergroups_in` ADD INDEX(`customer_id`);");

        $this->db->query("ALTER TABLE `tblinvoices` ADD INDEX(`currency`);");
        $this->db->query("ALTER TABLE `tblinvoices` ADD INDEX(`clientid`);");
        $this->db->query("ALTER TABLE `tblexpenses` ADD INDEX(`clientid`);");
        $this->db->query("ALTER TABLE `tblexpenses` ADD INDEX(`project_id`);");
        $this->db->query("ALTER TABLE `tblexpenses` ADD INDEX(`category`);");
        $this->db->query("ALTER TABLE `tblestimates` ADD INDEX(`clientid`);");
        $this->db->query("ALTER TABLE `tblestimates` ADD INDEX(`currency`);");
        $this->db->query("ALTER TABLE `tblactivitylog` ADD INDEX(`staffid`);");
        $this->db->query("ALTER TABLE `tblcontracts` ADD INDEX(`client`);");
        $this->db->query("ALTER TABLE `tblcontracts` ADD INDEX(`contract_type`);");
        $this->db->query("ALTER TABLE `tblleads` ADD INDEX(`status`);");
        $this->db->query("ALTER TABLE `tblleads` ADD INDEX(`assigned`);");
        $this->db->query("ALTER TABLE `tblprojects` ADD INDEX(`clientid`);");
        $this->db->query("ALTER TABLE `tbltickets` ADD INDEX(`service`);");
        $this->db->query("ALTER TABLE `tbltickets` ADD INDEX(`department`);");
        $this->db->query("ALTER TABLE `tbltickets` ADD INDEX(`status`);");
        $this->db->query("ALTER TABLE `tbltickets` ADD INDEX(`userid`);");
        $this->db->query("ALTER TABLE `tbltickets` ADD INDEX(`priority`);");
        $this->db->query("ALTER TABLE `tblleads` ADD INDEX(`source`);");

        $this->db->query("ALTER TABLE `tblinvoiceattachments` ADD `attachment_key` VARCHAR(32) NOT NULL AFTER `datecreated`;");
        $this->db->query("ALTER TABLE `tblinvoiceattachments` CHANGE `invoiceid` `rel_id` INT(11) NOT NULL;");
        $this->db->query("ALTER TABLE `tblinvoiceattachments` ADD `rel_type` VARCHAR(15) NOT NULL AFTER `rel_id`;");

        $attachments = $this->db->get('tblinvoiceattachments')->result_array();
        foreach($attachments as $attachment){
            $this->db->where('id',$attachment['id']);
            $this->db->update('tblinvoiceattachments',array('rel_type'=>'invoice','attachment_key'=>md5(date('Y-m-d H:i:s').$attachment['rel_id'])));
        }
        $this->db->query("ALTER TABLE `tblinvoiceattachments` ADD `visible_to_customer` INT NOT NULL DEFAULT '0' AFTER `attachment_key`;");

        $this->db->query("RENAME TABLE tblinvoiceattachments TO tblsalesattachments");


        if(file_exists(APPPATH.'views/admin/invoices/invoice_attach_file.php')){
            @unlink(APPPATH.'views/admin/invoices/invoice_attach_file.php');
        }

        update_option('update_info_message', '<div class="col-md-12">
            <div class="alert alert-success bold">
                <h4 class="bold">Hi! Thanks for updating Perfex CRM - You are using version 1.2.0</h4>
                <p>
                    This window will reload automaticaly in 5 seconds and will try to clear your browser cache, however its recomended to clear your browser cache manually.
                </p>
            </div>
        </div>
        <script>
            setTimeout(function(){
                window.location.reload();
            },5000);
        </script>
        ');
    }
}
