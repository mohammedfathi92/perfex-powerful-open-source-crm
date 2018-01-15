<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_116 extends CI_Migration
{
  function __construct()
  {
    parent::__construct();
}

public function up()
    {
        $this->db->query("ALTER TABLE `tbltaskchecklists` ADD `finished_from` INT NULL DEFAULT '0' AFTER `addedfrom`;");
    }
}
