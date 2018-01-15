<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_108 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        // Merge mail lists to surveys
        if (file_exists(APPPATH . 'views/admin/mail_lists')) {
            @delete_dir(APPPATH . 'views/admin/mail_lists');
            @unlink(APPPATH . 'controllers/admin/Mail_lists.php');
            @unlink(APPPATH . 'models/Mail_lists_model.php');
        }

        $default_tax = get_option('default_tax');
        if($default_tax != ''){
          $tax = get_tax_by_id($default_tax);
          if($tax){
              $default_tax = $tax->name . '|' . $tax->taxrate;
              $this->db->where('name','default_tax');
              $this->db->update('tbloptions',array('value'=>$default_tax));
          }
        }

        $this->db->query("ALTER TABLE `tblclients` CHANGE `shipping_country` `shipping_country` INT(11) NULL DEFAULT '0';");
        $this->db->query("ALTER TABLE `tblclients` CHANGE `billing_country` `billing_country` INT(11) NULL DEFAULT '0';");
        $this->db->query("ALTER TABLE `tblclients` CHANGE `country` `country` INT(11) NULL DEFAULT '0';");
        $this->db->query("ALTER TABLE `tblclients` CHANGE `default_currency` `default_currency` INT(11) NULL DEFAULT '0';");

        $this->db->query('CREATE TABLE IF NOT EXISTS `tblestimateitemstaxes` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `itemid` int(11) NOT NULL,
                          `estimate_id` int(11) NOT NULL,
                          `taxrate` decimal(11,2) NOT NULL,
                          `taxname` varchar(100) NOT NULL,
                          PRIMARY KEY (`id`)
                        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

        $this->db->query('CREATE TABLE IF NOT EXISTS `tblinvoiceitemstaxes` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `itemid` int(11) NOT NULL,
                          `invoice_id` int(11) NOT NULL,
                          `taxrate` decimal(11,2) NOT NULL,
                          `taxname` varchar(100) NOT NULL,
                          PRIMARY KEY (`id`)
                        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');



        $this->db->where('name','aside_menu_inactive');
        $aside_menu_inactive = $this->db->get('tbloptions')->row()->value;
        $aside_menu_inactive_ = json_decode($aside_menu_inactive);

        $i = 0;
        foreach($aside_menu_inactive_->aside_menu_inactive as $menu){

            if(isset($menu->children)){
                $x = 0;
                foreach($menu->children as $sub_menu){
                    if(isset($sub_menu->id) && $sub_menu->id == 'child-mail-lists'){
                        $aside_menu_inactive_->aside_menu_inactive[$i]->children[$x]->url = 'surveys/mail_lists';
                    }
                    $x++;
                }
            } else {
                if(isset($menu->id) && $menu->id == 'child-mail-lists'){
                    $aside_menu_inactive_->aside_menu_inactive[$i]->url = 'surveys/mail_lists';
                }
            }
            $i++;
        }

        update_option('aside_menu_inactive',json_encode(array('aside_menu_inactive'=>$aside_menu_inactive_->aside_menu_inactive)));

        // active aside

        $this->db->where('name','aside_menu_active');
        $aside_menu_active = $this->db->get('tbloptions')->row()->value;
        $aside_menu_active_ = json_decode($aside_menu_active);

        $i = 0;
        foreach($aside_menu_active_->aside_menu_active as $menu){

            if(isset($menu->children)){
                $x = 0;
                foreach($menu->children as $sub_menu){
                    if(isset($sub_menu->id) && $sub_menu->id == 'child-mail-lists'){
                        $aside_menu_active_->aside_menu_active[$i]->children[$x]->url = 'surveys/mail_lists';
                    }
                    $x++;
                }
            } else {
                if(isset($menu->id) && $menu->id == 'child-mail-lists'){
                    $aside_menu_active_->aside_menu_active[$i]->url = 'surveys/mail_lists';
                }
            }
            $i++;
        }

        update_option('aside_menu_active',json_encode(array('aside_menu_active'=>$aside_menu_active_->aside_menu_active)));

        $items_invoices = $this->db->get('tblinvoiceitems')->result_array();

        foreach($items_invoices as $item){
            $tax = get_tax_by_id($item['taxid']);
            if($tax){
               $taxrate = $tax->taxrate;
               $taxname = $tax->name;
               $this->db->insert('tblinvoiceitemstaxes',array(
                'itemid'=>$item['id'],
                'taxrate'=>$taxrate,
                'taxname'=>$taxname,
                'invoice_id'=>$item['invoiceid'],
                ));
           }
         }

           $this->db->query('ALTER TABLE `tblinvoiceitems` DROP `taxid`;');

           $items_estimates = $this->db->get('tblestimateitems')->result_array();

        foreach($items_estimates as $item){
            $tax = get_tax_by_id($item['taxid']);
            if($tax){
             $taxrate = $tax->taxrate;
             $taxname = $tax->name;
             $this->db->insert('tblestimateitemstaxes',array(
                'itemid'=>$item['id'],
                'taxrate'=>$taxrate,
                'taxname'=>$taxname,
                'estimate_id'=>$item['estimateid'],
                ));
         }
       }

        $this->db->query('ALTER TABLE `tblestimateitems` DROP `taxid`;');

        $this->db->query('ALTER TABLE `tblcustomfieldsvalues` ADD INDEX(`relid`);');
        $this->db->query('ALTER TABLE `tblcustomfieldsvalues` ADD INDEX(`fieldid`);');
        $this->db->query('ALTER TABLE `tblcustomfieldsvalues` ADD INDEX(`fieldto`);');

        $this->db->where('shortname','manageMailLists');
        $mail_list_permission = $this->db->get('tblpermissions')->row();

        if($mail_list_permission){
            $this->db->where('permissionid',$mail_list_permission->permissionid);
            $this->db->delete('tblstaffpermissions');
        }

        $this->db->where('shortname','manageMailLists');
        $this->db->delete('tblpermissions');

        add_option('show_tax_per_item',1);

        add_option('last_survey_send_cron',time());
        add_option('total_to_words_enabled',0);
        add_option('total_to_words_lowercase',0);
    }
}
