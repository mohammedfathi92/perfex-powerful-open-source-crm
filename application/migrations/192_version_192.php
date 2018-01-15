<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_192 extends CI_Migration
{
    public function __construct()
    {
        parent::__construct();
    }

    public function up()
    {

        add_option('paymentmethod_authorize_sim_description_dashboard','Payment For Invoice',0);
        add_option('paymentmethod_paypal_description_dashboard','Payment For Invoice',0);
        add_option('paymentmethod_mollie_description_dashboard','Payment For Invoice',0);
        add_option('paymentmethod_authorize_aim_description_dashboard','Payment For Invoice',0);
        add_option('paymentmethod_stripe_description_dashboard','Payment For Invoice',0);
        add_option('timer_started_change_status_in_progress','1',0);


         $addressFormat = "{company_name}<br />
{address} {city} {state}<br />\r
{country_code} {zip_code}<br />";

        if(get_option('invoice_company_phonenumber') != ''){
            $addressFormat .= PHP_EOL.'{phone}<br />';
        }

        if(get_option('company_vat') != ''){
            $addressFormat .= PHP_EOL.'{vat_number_with_label}<br />';
        }

        $custom_company_fields = get_company_custom_fields();

        foreach($custom_company_fields as $field){
            $addressFormat .= PHP_EOL.'{cf_'.$field['id'].'}<br />';
        }

        $addressFormat = preg_replace('/(<br \/>)+$/', '', $addressFormat);

        add_option('company_info_format',$addressFormat,0);

        $customerAddressFormat = "{company_name}<br />
{street} {city} {state}<br />\r
{country_code} {zip_code}<br />";

        if(get_option('company_requires_vat_number_field') == '1'){
            $customerAddressFormat .= PHP_EOL.'{vat_number_with_label}<br />';
        }

        $pdf_custom_fields = get_custom_fields('customers',array('show_on_pdf'=>1));

        foreach($pdf_custom_fields as $f){
            $customerAddressFormat .= PHP_EOL.'{cf_'.$f['id'].'}<br />';
        }

        $customerAddressFormat = preg_replace('/(<br \/>)+$/', '', $customerAddressFormat);

        add_option('customer_info_format',$customerAddressFormat,0);

        add_option('show_pdf_signature_invoice',0,0);
        add_option('show_pdf_signature_estimate',0,0);
        add_option('signature_image','',0);
        add_option('upgraded_from_version','',0);

        add_option('scroll_responsive_tables','0');

        $this->db->query("INSERT INTO `tblpermissions` (`name`, `shortname`) VALUES ('Tasks Checklist Templates', 'checklist_templates');");

        $this->db->query("CREATE TABLE `tblcheckliststemplates` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `description` text,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

        update_option('update_info_message', '<div class="col-md-12">
        <div class="alert alert-success bold">
            <h4 class="bold">Hi! Thanks for updating Perfex CRM - You are using version 1.9.2</h4>
            <p>
                This window will reload automaticaly in 10 seconds and will try to clear your browser/cloudflare cache, however its recomended to clear your browser cache manually.
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
