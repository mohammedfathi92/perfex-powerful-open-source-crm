<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_109 extends CI_Migration
{
  function __construct()
  {
    parent::__construct();
  }

  public function up()
  {
    if (!is_dir(PROJECT_ATTACHMENTS_FOLDER)) {
      mkdir(PROJECT_ATTACHMENTS_FOLDER);
      fopen(PROJECT_ATTACHMENTS_FOLDER . 'index.html', 'w');
      $fp = fopen(PROJECT_ATTACHMENTS_FOLDER . 'index.html', 'a+');
      if ($fp) {
        fclose($fp);
      }
    }
    if (!is_dir(PROJECT_DISCUSSION_ATTACHMENT_FOLDER)) {
      mkdir(PROJECT_DISCUSSION_ATTACHMENT_FOLDER);
      fopen(PROJECT_DISCUSSION_ATTACHMENT_FOLDER . 'index.html', 'w');
      $fp = fopen(PROJECT_DISCUSSION_ATTACHMENT_FOLDER . 'index.html', 'a+');
      if ($fp) {
        fclose($fp);
      }
    }

    if (!is_dir(CONTACT_PROFILE_IMAGES_FOLDER)) {
      mkdir(CONTACT_PROFILE_IMAGES_FOLDER);
      fopen(CONTACT_PROFILE_IMAGES_FOLDER . 'index.html', 'w');
      $fp = fopen(CONTACT_PROFILE_IMAGES_FOLDER . 'index.html', 'a+');
      if ($fp) {
        fclose($fp);
      }
    }

    if (file_exists(APPPATH . 'views/admin/settings/includes/newsfeed.php')) {
      @unlink(APPPATH . 'views/admin/settings/includes/newsfeed.php');
    }

    if (file_exists(APPPATH . 'views/admin/contracts/contract_attachments_template.php')) {
      @unlink(APPPATH . 'views/admin/contracts/contract_attachments_template.php');
    }

    if (file_exists(APPPATH . 'views/admin/settings/includes/reminders.php')) {
      @unlink(APPPATH . 'views/admin/settings/includes/reminders.php');
    }

    if (file_exists(APPPATH . 'views/admin/clients/attachments_template.php')) {
      @unlink(APPPATH . 'views/admin/clients/attachments_template.php');
    }

    if (file_exists(APPPATH . 'views/admin/leads/kan-ban-lead-content.php')) {
      @unlink(APPPATH . 'views/admin/leads/kan-ban-lead-content.php');
    }

    $this->db->query("CREATE TABLE IF NOT EXISTS `tblviewstracking` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `rel_id` int(11) NOT NULL,
      `rel_type` varchar(40) NOT NULL,
      `date` datetime NOT NULL,
      `view_ip` varchar(40) NOT NULL,
      PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

    $this->db->query("CREATE TABLE IF NOT EXISTS `tblprojectactivity` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `project_id` int(11) NOT NULL,
      `staff_id` int(11) NOT NULL DEFAULT '0',
      `client_id` int(11) NOT NULL DEFAULT '0',
      `visible_to_customer` int(11) NOT NULL DEFAULT '0',
      `description_key` varchar(500) NOT NULL COMMENT 'Language file key',
      `additional_data` text,
      `dateadded` datetime NOT NULL,
      PRIMARY KEY (`id`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

    $this->db->query("CREATE TABLE IF NOT EXISTS `tblprojectdiscussioncomments` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `discussion_id` int(11) NOT NULL,
      `parent` int(11) DEFAULT NULL,
      `created` datetime NOT NULL,
      `modified` datetime DEFAULT NULL,
      `content` text NOT NULL,
      `staff_id` int(11) NOT NULL,
      `client_id` int(11) DEFAULT '0',
      `file_name` varchar(300) DEFAULT NULL,
      `file_mime_type` varchar(70) DEFAULT NULL,
      PRIMARY KEY (`id`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");


    $this->db->query("CREATE TABLE IF NOT EXISTS `tblprojectdiscussions` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `project_id` int(11) NOT NULL,
      `subject` varchar(500) NOT NULL,
      `description` text NOT NULL,
      `show_to_customer` tinyint(1) NOT NULL DEFAULT '0',
      `datecreated` datetime NOT NULL,
      `last_activity` datetime DEFAULT NULL,
      `staff_id` int(11) NOT NULL DEFAULT '0',
      `client_id` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");


    $this->db->query("CREATE TABLE IF NOT EXISTS `tblprojectfiles` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `file_name` mediumtext NOT NULL,
      `filetype` varchar(50) DEFAULT NULL,
      `dateadded` datetime NOT NULL,
      `project_id` int(11) NOT NULL,
      `visible_to_customer` tinyint(1) DEFAULT '0',
      `addedfrom` int(11) NOT NULL,
      PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

    $this->db->query("CREATE TABLE IF NOT EXISTS `tblprojectmembers` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `project_id` int(11) NOT NULL,
      `staff_id` int(11) NOT NULL,
      PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");


    $this->db->query("CREATE TABLE IF NOT EXISTS `tblprojectnotes` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `project_id` int(11) NOT NULL,
      `content` text NOT NULL,
      `staff_id` int(11) NOT NULL,
      PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

    $this->db->query("CREATE TABLE IF NOT EXISTS `tblprojects` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(600) NOT NULL,
      `description` text,
      `status` int(11) NOT NULL DEFAULT '0',
      `clientid` int(11) NOT NULL,
      `billing_type` int(11) NOT NULL,
      `start_date` date NOT NULL,
      `deadline` date NOT NULL,
      `project_created` date NOT NULL,
      `project_cost` decimal(11,2) DEFAULT NULL,
      `project_rate_per_hour` decimal(11,2) DEFAULT NULL,
      `addedfrom` int(11) NOT NULL,
      PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

    $this->db->query("CREATE TABLE IF NOT EXISTS `tblprojectsettings` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `project_id` int(11) NOT NULL,
      `name` varchar(100) NOT NULL,
      `value` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

    $this->db->query("CREATE TABLE IF NOT EXISTS `tblmilestones` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(400) NOT NULL,
      `due_date` date NOT NULL,
      `project_id` int(11) NOT NULL,
      `milestone_order` int(11) NOT NULL DEFAULT '0',
      `datecreated` date NOT NULL,
      PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

    $this->db->query("CREATE TABLE IF NOT EXISTS `tbltaskstimers` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `task_id` int(11) NOT NULL,
      `start_time` varchar(64) NOT NULL,
      `end_time` varchar(64) DEFAULT NULL,
      `staff_id` int(11) NOT NULL,
      PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

    $this->db->query("INSERT INTO `tblemailtemplates` (`type`, `slug`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
      ('tasks', 'task-deadline-notification', 'Task Deadline Reminder - Sent to assignees', 'Task Deadline Reminder', '<div>Hi {staff_firstname}</div><div><br></div><div>This is an automated email from <span style=\"font-family: Helvetica, Arial, sans-serif;\">{companyname}.</span></div><div>The task <span style=\"font-family: Helvetica, Arial, sans-serif;\">{task_name} deadline is on {task_duedate}. This task is still not finished.</span></div><div><br></div><div><span style=\"font-family: Helvetica, Arial, sans-serif;\">Please click on the following link to view: </span><span style=\"font-family: Helvetica, Arial, sans-serif; background-color: inherit;\">{task_link}</span></div><div><br></div><div>{email_signature}</div>', '{companyname} | CRM', '', 0, 1, 0)");

    $this->db->query("ALTER TABLE `tblinvoices` ADD `total_tax` DECIMAL(11,2) NOT NULL DEFAULT '0' AFTER `subtotal`;");
    $this->db->query("ALTER TABLE `tblestimates` ADD `total_tax` DECIMAL(11,2) NOT NULL DEFAULT '0' AFTER `subtotal`;");

    $invoice_ids = $this->db->get('tblinvoices')->result_array();
    $this->load->model('invoices_model');

    foreach($invoice_ids as $i){
     $total_tax = 0;
     $taxes = array();
     $_calculated_taxes = array();
     $invoice = $this->invoices_model->get($i['id']);
     foreach($invoice->items as $item){
       $item_taxes = get_invoice_item_taxes($item['id']);
       if(count($item_taxes) > 0){
         foreach($item_taxes as $tax){
           $calc_tax = 0;
           $tax_not_calc = false;
           if(!in_array($tax['taxname'],$_calculated_taxes)) {
            array_push($_calculated_taxes,$tax['taxname']);
            $tax_not_calc = true;
          }
          if($tax_not_calc == true){
            $taxes[$tax['taxname']] =array();
            $taxes[$tax['taxname']]['total'] = array();
            array_push($taxes[$tax['taxname']]['total'],(($item['qty'] * $item['rate']) / 100 * $tax['taxrate']));
            $taxes[$tax['taxname']]['tax_name'] = $tax['taxname'];
            $taxes[$tax['taxname']]['taxrate'] = $tax['taxrate'];
          } else {
            array_push($taxes[$tax['taxname']]['total'],(($item['qty'] * $item['rate']) / 100 * $tax['taxrate']));
          }
        }
      }
    }
    foreach($taxes as $tax){
      $total = array_sum($tax['total']);
      if($invoice->discount_percent != 0 && $invoice->discount_type == 'before_tax'){
       $total_tax_calculated = ($total * $invoice->discount_percent) / 100;
       $total = ($total - $total_tax_calculated);
     }

     $total_tax += $total;
   }

   $this->db->where('id',$i['id']);
   $this->db->update('tblinvoices',array('total_tax'=>$total_tax));

 }


 $estimate_ids = $this->db->get('tblestimates')->result_array();
 $this->load->model('estimates_model');

 foreach($estimate_ids as $i){
   $total_tax = 0;
   $taxes = array();
   $_calculated_taxes = array();
   $estimate = $this->estimates_model->get($i['id']);
   foreach($estimate->items as $item){
     $item_taxes = get_estimate_item_taxes($item['id']);
     if(count($item_taxes) > 0){
       foreach($item_taxes as $tax){
         $calc_tax = 0;
         $tax_not_calc = false;
         if(!in_array($tax['taxname'],$_calculated_taxes)) {
          array_push($_calculated_taxes,$tax['taxname']);
          $tax_not_calc = true;
        }
        if($tax_not_calc == true){
          $taxes[$tax['taxname']] =array();
          $taxes[$tax['taxname']]['total'] = array();
          array_push($taxes[$tax['taxname']]['total'],(($item['qty'] * $item['rate']) / 100 * $tax['taxrate']));
          $taxes[$tax['taxname']]['tax_name'] = $tax['taxname'];
          $taxes[$tax['taxname']]['taxrate'] = $tax['taxrate'];
        } else {
          array_push($taxes[$tax['taxname']]['total'],(($item['qty'] * $item['rate']) / 100 * $tax['taxrate']));
        }
      }
    }
  }
  foreach($taxes as $tax){
    $total = array_sum($tax['total']);
    if($estimate->discount_percent != 0 && $estimate->discount_type == 'before_tax'){
     $total_tax_calculated = ($total * $estimate->discount_percent) / 100;
     $total = ($total - $total_tax_calculated);
   }

   $total_tax += $total;
 }

 $this->db->where('id',$i['id']);
 $this->db->update('tblestimates',array('total_tax'=>$total_tax));

}


$this->db->query("ALTER TABLE `tblclients` CHANGE `firstname` `firstname` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT
  NULL;");
$this->db->query("ALTER TABLE `tblclients` CHANGE `lastname` `lastname` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;");

$clients = $this->db->get('tblclients')->result_array();
foreach ($clients as $client) {
  $this->db->insert('tblcustomerpermissions', array(
    'permission_id' => 6,
    'userid' => $client['userid']
    ));
}

add_option('media_max_file_size_upload', 50);
add_option('client_staff_add_edit_delete_task_comments_first_hour', 1);
add_option('show_projects_on_calendar', 1);
add_option('leads_kanban_limit', 500);
add_option('tasks_reminder_notification_before', 2);

$this->db->query("ALTER TABLE `tblnotifications` ADD `fromclientid` INT NOT NULL DEFAULT '0' AFTER `fromuserid`;");
$this->db->query("ALTER TABLE `tblstafftasksattachments` ADD `staffid` INT NOT NULL DEFAULT '0' AFTER `taskid`;");
$this->db->query("ALTER TABLE `tblstafftasksattachments` ADD `clientid` INT NOT NULL AFTER `staffid`;");

$this->db->query("ALTER TABLE `tblstafftaskcomments` ADD `clientid` INT NOT NULL DEFAULT '0' AFTER `staffid`;");

$this->db->query("ALTER TABLE `tblclients` ADD `profile_image` VARCHAR(150) NULL AFTER `default_currency`;");

$this->db->query("ALTER TABLE `tblstafftasks` ADD INDEX(`rel_id`);");
$this->db->query("ALTER TABLE `tblstafftasks` ADD INDEX(`rel_type`);");

$this->db->query("ALTER TABLE `tblstafftasks` ADD `billable` BOOLEAN NOT NULL DEFAULT FALSE AFTER `is_public`, ADD `billed` BOOLEAN NOT NULL DEFAULT FALSE AFTER `billable`;");

$this->db->query("ALTER TABLE `tblstafftasks` ADD `invoice_id` INT NOT NULL DEFAULT '0' AFTER `billed`;");
$this->db->query("ALTER TABLE `tblstafftasks` ADD `hourly_rate` INT NOT NULL DEFAULT '0' AFTER `invoice_id`;");
$this->db->query("ALTER TABLE `tblstafftasks` ADD `milestone` INT NULL DEFAULT '0' AFTER `hourly_rate`;");
$this->db->query("ALTER TABLE `tblstafftasks` ADD `visible_to_client` BOOLEAN NOT NULL DEFAULT FALSE AFTER `milestone`;");
$this->db->query("ALTER TABLE `tblstafftasks` ADD `deadline_notified` INT NOT NULL DEFAULT '0' AFTER `visible_to_client`;");
$this->db->query("ALTER TABLE `tblinvoices` ADD `show_quantity_as` INT NOT NULL DEFAULT '1' AFTER `show_shipping_on_invoice`;");
$this->db->query("ALTER TABLE `tblestimates` ADD `show_quantity_as` INT NOT NULL DEFAULT '1' AFTER `show_shipping_on_estimate`;");

$this->db->query("INSERT INTO `tblpermissions` (`permissionid`, `name`, `shortname`) VALUES (NULL, 'Manage Projects', 'manageProjects');");

$this->db->query("ALTER TABLE `tblinvoices` ADD `project_id` INT NULL DEFAULT '0' AFTER `show_quantity_as`;");

add_main_menu_item(array(
  'name' => 'projects',
  'permission' => '',
  'icon' => 'fa fa-bars',
  'url' => 'projects',
  'id' => 'projects'
  ));

$tasks = $this->db->get('tblstafftasks')->result_array();
foreach ($tasks as $task) {
  if ($task['priority'] == _l('task_priority_low')) {
    $priority = 1;
  } else if ($task['priority'] == _l('task_priority_medium')) {
    $priority = 2;
  } else if ($task['priority'] == _l('task_priority_high')) {
    $priority = 3;
  } else if ($task['priority'] == _l('task_priority_urgent')) {
    $priority = 4;
  } else {
    $priority = 2;
  }
  $this->db->where('id', $task['id']);
  $this->db->update('tblstafftasks', array(
    'priority' => $priority
    ));
}

}
}
