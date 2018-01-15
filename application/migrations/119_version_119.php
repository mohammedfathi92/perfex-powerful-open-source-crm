<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_119 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        add_option('use_recaptcha_customers_area',0);
        add_option('remove_decimals_on_zero',0);
        add_option('remove_tax_name_from_item_table',0);

        $tallowed = get_option('ticket_attachments_file_extensions');
        $tallowed = explode('|',$tallowed);
        $tallowed_new = '';
        if(is_array($tallowed)){
            foreach($tallowed as $ext){
                $tallowed_new .= '.'.$ext.',';
            }
        }
        if($tallowed_new != ''){
            $tallowed_new = substr($tallowed_new,0,-1);
        }
        update_option('ticket_attachments_file_extensions',$tallowed_new);

        $this->db->query("ALTER TABLE  `tblestimates` CHANGE  `discount_percent`  `discount_percent` DECIMAL( 11, 2 ) NOT NULL DEFAULT '0.00' ;");

        $this->db->query("ALTER TABLE  `tblinvoices` CHANGE  `discount_percent`  `discount_percent` DECIMAL( 11, 2 ) NOT NULL DEFAULT '0.00' ;");
        $this->db->query("ALTER TABLE  `tblestimates` CHANGE  `discount_total`  `discount_total` DECIMAL( 11, 2 ) NOT NULL DEFAULT '0.00' ;");
        $this->db->query("ALTER TABLE  `tblinvoices` CHANGE  `discount_total`  `discount_total` DECIMAL( 11, 2 ) NOT NULL DEFAULT '0.00' ;");

        $this->db->query("ALTER TABLE `tblstafftasks` CHANGE `priority` `priority` INT(11) NULL DEFAULT NULL;");

        update_option('update_info_message', '<script>window.location.reload();</script>');
    }
}
