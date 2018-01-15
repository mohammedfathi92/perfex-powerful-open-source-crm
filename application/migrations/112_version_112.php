<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_112 extends CI_Migration
{
  function __construct()
  {
    parent::__construct();
}

public function up()
{
    $this->db->query("ALTER TABLE `tblcontracts` ADD `content` LONGTEXT NULL AFTER `id`;");

    $this->db->query("INSERT INTO `tblemailtemplates` (`type`, `slug`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
('contract', 'send-contract', 'Send contract to customer', 'Contract - {contract_subject}', '<p>Hi&nbsp;{client_company}</p>\r\n<p>Please find the {contract_subject}&nbsp;attached.</p>\r\n<p>&nbsp;</p>\r\n<p>Regards,</p>\r\n<p>{email_signature}</p>', '{companyname} | CRM', '', 0, 1, 0);");
}
}
