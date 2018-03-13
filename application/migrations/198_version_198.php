<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_198 extends CI_Migration
{
    public function __construct()
    {
        parent::__construct();
    }

    public function up()
    {

        add_option('credit_note_number_format',1);
        add_option('allow_non_admin_members_to_import_leads',0);

        if (get_option('cron_send_invoice_overdue_reminder') == '0') {
            $this->db->where('slug', 'invoice-overdue-notice');
            $this->db->update('tblemailtemplates', array('active'=>0));
        }

        $this->db->where('name', 'cron_send_invoice_overdue_reminder');
        $this->db->delete('tbloptions');

        if (get_option('estimate_expiry_reminder_enabled') == '0') {
            $this->db->where('slug', 'estimate-expiry-reminder');
            $this->db->update('tblemailtemplates', array('active'=>0));
        }

        $this->db->where('name', 'estimate_expiry_reminder_enabled');
        $this->db->delete('tbloptions');

        if (get_option('contract_expiry_reminder_enabled') == '0') {
            $this->db->where('slug', 'contract-expiration');
            $this->db->update('tblemailtemplates', array('active'=>0));
        }

        $this->db->where('name', 'contract_expiry_reminder_enabled');
        $this->db->delete('tbloptions');

        $this->db->where('name', 'auto_check_for_new_notifications');
        $this->db->delete('tbloptions');

        $this->db->where('slug', 'contract-expiration');
        $this->db->where('language', 'english');
        $this->db->update('tblemailtemplates', array('name'=>'Contract Expiration Reminder (Sent to Customer Contacts and Staff)'));

        $this->db->query("INSERT INTO `tblpermissions` (`name`, `shortname`) VALUES ('Leads', 'leads');");
        $this->db->query("ALTER TABLE `tblcreditnotes` ADD `number_format` INT NOT NULL DEFAULT '1' AFTER `prefix`;");

        if(is_dir(APPPATH.'vendor/tecnickcom/tcpdf/fonts/') && is_dir(APPPATH.'third_party/tcpdf/fonts/')){
            @xcopy(APPPATH.'third_party/tcpdf/fonts/',APPPATH.'vendor/tecnickcom/tcpdf/fonts/');
        }

        if(file_exists(FCPATH.'pipe.php')){
            @chmod(FCPATH.'pipe.php', 0755);
        }

        update_option('update_info_message', '<div class="col-md-12">
        <div class="alert alert-success bold">
        <h4 class="bold">Hi! Thanks for updating Perfex CRM - You are using version 1.9.8</h4>
        <p>
        This window will reload automaticaly in 10 seconds and will try to clear your browser/cloudflare cache, however its recomended to clear your browser cache manually.
        </p>
        </div>
        </div>
        <script>
        setTimeout(function(){
            window.location.reload();
        },10000);
        </script>');
    }
}
