<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_110 extends CI_Migration
{
  function __construct()
  {
    parent::__construct();
  }

  public function up()
  {
     $this->db->query("ALTER TABLE `tblleads` ADD `company` VARCHAR(300) NULL AFTER `name`;");
  }
}
