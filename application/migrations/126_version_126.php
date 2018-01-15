<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_126 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        // Nothing to do here only to update db version
        $this->db->query("ALTER TABLE  `tblinvoicepaymentrecords` ADD  `paymentmethod` VARCHAR( 200 ) NULL AFTER  `paymentmode` ;");
    }
}
