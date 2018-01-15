<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_103 extends CI_Migration
{
  function __construct()
  {
    parent::__construct();
  }

  public function up()
  {
    add_option('last_recurring_invoices_cron','');
        // add the table custom fields
    $this->db->query("CREATE TABLE IF NOT EXISTS `tblcustomfields` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `fieldto` varchar(50) NOT NULL,
      `name` varchar(150) NOT NULL,
      `required` tinyint(1) NOT NULL DEFAULT '0',
      `type` varchar(20) NOT NULL,
      `options` mediumtext,
      `field_order` int(11) DEFAULT '0',
      `active` int(11) NOT NULL DEFAULT '1',
      PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        // add the table custom fields values
    $this->db->query("CREATE TABLE IF NOT EXISTS `tblcustomfieldsvalues` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `relid` int(11) NOT NULL,
      `fieldid` int(11) NOT NULL,
      `fieldto` varchar(50) NOT NULL,
      `value` text NOT NULL,
      PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        // Add contract types
    $this->db->query("CREATE TABLE IF NOT EXISTS `tblcontracttypes` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` mediumtext NOT NULL,
      PRIMARY KEY (`id`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        // add type field to contracts table
    $this->db->query("ALTER TABLE `tblcontracts` ADD `contract_type` INT NULL AFTER `dateend`;");

        // Estimate discount type fix
    $this->db->query('ALTER TABLE `tblestimates` CHANGE `discount_type` `discount_type` VARCHAR(30) NULL DEFAULT NULL;');

  }
}
