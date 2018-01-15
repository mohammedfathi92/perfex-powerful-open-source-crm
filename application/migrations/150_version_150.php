<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_150 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {

     $this->db->query("ALTER TABLE `tblprojects` CHANGE `deadline` `deadline` DATE NULL DEFAULT NULL;");
     $this->db->query("ALTER TABLE `tblclients` ADD `website` VARCHAR(150) NULL AFTER `address`;");
     $this->db->query("ALTER TABLE  `tbldepartments` ADD  `imap_username` VARCHAR( 50 ) NULL AFTER  `name` ;");

     // Nothing to do here only to update database version number
     update_option('update_info_message', '<div class="col-md-12">
        <div class="alert alert-success bold">
            <h4 class="bold">Hi! Thanks for updating Perfex CRM - You are using version 1.5.0</h4>
            <p>
                This window will reload automaticaly in 10 seconds and will try to clear your browser cache, however its recomended to clear your browser cache manually.
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
