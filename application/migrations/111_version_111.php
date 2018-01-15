<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_111 extends CI_Migration
{
  function __construct()
  {
    parent::__construct();
  }

  public function up()
  {
    if(file_exists(APPPATH.'views/admin/clients/modals/add_reminder.php')){
      @unlink(APPPATH.'views/admin/clients/modals/add_reminder.php');
    }
    if(file_exists(APPPATH.'views/admin/proposals/add_items_template.php')){
      @unlink(APPPATH.'views/admin/proposals/add_items_template.php');
    }

    if(file_exists(APPPATH.'views/admin/clients/contracts.php')){
      @unlink(APPPATH.'views/admin/clients/contracts.php');
    }

    if(file_exists(FCPATH.'assets/js/editor.js')){
      @unlink(FCPATH.'assets/js/editor.js');
    }

    if(is_dir(FCPATH.'assets/plugins/ContentTools')){
      if(is_dir(FCPATH.'assets/plugins/ContentTools/images')){
        @delete_dir(FCPATH.'assets/plugins/ContentTools/images');
      }

      if(is_dir(FCPATH.'assets/plugins/ContentTools/build')){
        if(is_dir(FCPATH.'assets/plugins/ContentTools/build/images')){
          @delete_dir(FCPATH.'assets/plugins/ContentTools/build/images');
        }
        @delete_dir(FCPATH.'assets/plugins/ContentTools/build');
      }

      if(is_dir(FCPATH.'assets/plugins/ContentTools')){
        @delete_dir(FCPATH.'assets/plugins/ContentTools');
      }
    }

    $this->db->query("RENAME TABLE tblcustomerpermissions TO tblcontactpermissions");


    $this->db->query("CREATE TABLE IF NOT EXISTS `tblcontacts` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `userid` int(11) NOT NULL,
      `is_primary` int(11) NOT NULL DEFAULT '1',
      `firstname` varchar(300) NOT NULL,
      `lastname` varchar(300) NOT NULL,
      `email` varchar(100) NOT NULL,
      `phonenumber` varchar(100) NOT NULL,
      `title` varchar(200) NOT NULL,
      `datecreated` datetime NOT NULL,
      `password` varchar(255) DEFAULT NULL,
      `new_pass_key` varchar(32) DEFAULT NULL,
      `new_pass_key_requested` datetime DEFAULT NULL,
      `last_ip` varchar(40) DEFAULT NULL,
      `last_login` datetime DEFAULT NULL,
      `last_password_change` datetime DEFAULT NULL,
      `active` tinyint(1) NOT NULL DEFAULT '1',
      `profile_image` varchar(300) DEFAULT NULL,
      PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

    $this->db->query("CREATE TABLE IF NOT EXISTS `tblestimatenotes` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `estimate_id` int(11) NOT NULL,
      `staffid` int(11) NOT NULL,
      `description` text NOT NULL,
      `dateadded` datetime NOT NULL,
      PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

    $this->db->query("ALTER TABLE `tblstaff` ADD `media_path_slug` VARCHAR(300) NULL AFTER `default_language`;");

    $staff = $this->db->get('tblstaff')->result_array();
    foreach($staff as $staff){
      $sl = $staff['firstname'].' '.$staff['lastname'];
      if($sl == ' '){
        $sl = 'unknown-'.$staff['staffid'];
      }
      $this->db->where('staffid',$staff['staffid']);
      $this->db->update('tblstaff',array('media_path_slug'=>slug_it($sl)));
    }

    add_option('autoclose_tickets_after',72);
    add_option('receive_notification_on_new_ticket',0);
    add_option('pdf_font','droidsansfallback');
    add_option('pdf_table_heading_color','#323a45');
    add_option('pdf_table_heading_text_color','#ffffff');
    add_option('pdf_font_size','10');
    add_option('defaut_leads_kanban_sort','dateadded');
    add_option('defaut_leads_kanban_sort_type','desc');
    add_option('allowed_files','.gif,.png,.jpeg,.jpg,.pdf,.doc,.txt,.docx,.xls,.zip,.rar,.xlsx,.mp4');

    delete_option('newsfeed_upload_file_extensions');

    $this->db->where('shortname','isTranslator');
    $perm_not_used = $this->db->get('tblpermissions')->row();
    if($perm_not_used){
      $this->db->where('permissionid',$perm_not_used->permissionid);
      $this->db->delete('tblstaffpermissions');

      $this->db->where('permissionid',$perm_not_used->permissionid);
      $this->db->delete('tblpermissions');
    }



    $this->db->query("ALTER TABLE `tbltickets` ADD `contactid` INT NOT NULL DEFAULT '0' AFTER `userid`;");
    $this->db->query("ALTER TABLE `tblticketreplies` ADD `contactid` INT NOT NULL DEFAULT '0' AFTER `userid`;");

    $this->db->query("ALTER TABLE `tblevents` ADD `description` TEXT NULL AFTER `title`;");

    $this->db->query("ALTER TABLE `tblevents` CHANGE `end` `end` DATETIME NULL DEFAULT NULL;");


    $this->db->query("ALTER TABLE `tblevents` CHANGE `start` `start` DATETIME NULL DEFAULT NULL;");
    $this->db->query("ALTER TABLE `tblreminders` CHANGE `date` `date` DATETIME NOT NULL;");

    $this->db->query("ALTER TABLE `tblprojectdiscussioncomments` CHANGE `client_id` `contact_id` INT(11) NULL DEFAULT '0';");
    $this->db->query("ALTER TABLE `tblprojectdiscussioncomments` ADD `full_name` VARCHAR(300) NULL AFTER `contact_id`;");
    $this->db->query("ALTER TABLE `tblprojectdiscussions` CHANGE `client_id` `contact_id` INT(11) NOT NULL DEFAULT '0';");
    $this->db->query("ALTER TABLE `tblprojectactivity` CHANGE `client_id` `contact_id` INT(11) NOT NULL DEFAULT '0';");
    $this->db->query("ALTER TABLE `tblstafftaskcomments` CHANGE `clientid` `contact_id` INT(11) NOT NULL DEFAULT '0';");
    $this->db->query("ALTER TABLE `tblstafftasksattachments` CHANGE `clientid` `contact_id` INT(11) NOT NULL;");
    $this->db->query("ALTER TABLE `tblprojectfiles` CHANGE `addedfrom` `staffid` INT(11) NOT NULL;");
    $this->db->query("ALTER TABLE `tblprojectfiles` ADD `contact_id` INT NOT NULL DEFAULT '0' AFTER `staffid`;");

    $this->db->query("ALTER TABLE `tblestimates` ADD `pipeline_order` INT NOT NULL DEFAULT '0' AFTER `show_quantity_as`;");
    $this->db->query("CREATE TABLE IF NOT EXISTS `tblestimatenotes` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `estimate_id` int(11) NOT NULL,
      `staffid` int(11) NOT NULL,
      `description` text NOT NULL,
      `dateadded` datetime NOT NULL,
      PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

    $templates = array(
      'new-client-created',
      'invoice-send-to-client',
      'new-ticket-opened-admin',
      'ticket-reply',
      'ticket-autoresponse',
      'invoice-overdue-notice',
      'invoice-already-send',
      'estimate-send-to-client',
      'ticket-reply-to-admin',
      'estimate-already-send',
      'contract-expiration',
      'estimate-declined-to-staff',
      'estimate-accepted-to-staff',
      'estimate-thank-you-to-customer',
      );

    foreach($templates as $_template){
      $this->db->where('slug',$_template);
      $template = $this->db->get('tblemailtemplates')->row();
      if($template){

        $template->message = str_replace('client_firstname','contact_firstname',$template->message);
        $template->message = str_replace('client_lastname','contact_lastname',$template->message);
        $template->message = str_replace('client_email','contact_email',$template->message);
        $this->db->where('emailtemplateid',$template->emailtemplateid);
        $this->db->update('tblemailtemplates',array('message'=>$template->message));
      }
    }


    $clients = $this->db->get('tblclients')->result_array();
    foreach($clients as $client){
      $this->db->insert('tblcontacts',array(
        'is_primary'=>1,
        'userid'=>$client['userid'],
        'firstname'=>$client['firstname'],
        'lastname'=>$client['lastname'],
        'email'=>$client['email'],
        'phonenumber'=>$client['phonenumber'],
        'datecreated'=>$client['datecreated'],
        'password'=>$client['password'],
        'new_pass_key'=>$client['new_pass_key'],
        'new_pass_key_requested'=>$client['new_pass_key_requested'],
        'last_ip'=>$client['last_ip'],
        'last_login'=>$client['last_login'],
        'last_password_change'=>$client['last_password_change'],
        'active'=>$client['active'],
        'profile_image'=>$client['profile_image'],
        'title'=>'',
        ));

      $contact_id = $this->db->insert_id();
      if($contact_id){

        $this->db->where('userid',$client['userid']);
        $this->db->update('tbltickets',array('contactid'=>$contact_id));
        $this->db->where('userid',$client['userid']);
        $this->db->update('tblticketreplies',array('contactid'=>$contact_id));

        $this->db->where('userid',$client['userid']);
        $this->db->update('tblcontactpermissions',array('userid'=>$contact_id));

        $this->db->where('fromclientid',$client['userid']);
        $this->db->update('tblnotifications',array('fromclientid'=>$contact_id));

        $this->db->where('contact_id',$client['userid']);
        $this->db->update('tblprojectdiscussioncomments',array('full_name'=>$client['firstname'] . ' ' . $client['lastname']));

      }
    }
    $this->db->query("ALTER TABLE `tblclients` DROP `firstname`;");
    $this->db->query("ALTER TABLE `tblclients` DROP `lastname`;");
    $this->db->query("ALTER TABLE `tblclients` DROP `email`;");
    $this->db->query("ALTER TABLE `tblclients` DROP `password`;");
    $this->db->query("ALTER TABLE `tblclients` DROP `new_pass_key`;");
    $this->db->query("ALTER TABLE `tblclients` DROP `new_pass_key_requested`;");
    $this->db->query("ALTER TABLE `tblclients` DROP `last_ip`;");
    $this->db->query("ALTER TABLE `tblclients` DROP `last_login`;");
    $this->db->query("ALTER TABLE `tblclients` DROP `last_password_change`;");
    $this->db->query("ALTER TABLE `tblclients` DROP `active`;");
    $this->db->query("ALTER TABLE `tblclients` DROP `profile_image`;");
  }
}
