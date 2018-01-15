<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_196 extends CI_Migration
{
    public function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        $this->db->query("ALTER TABLE `tblgoals` ADD `staff_id` INT NOT NULL DEFAULT '0' AFTER `notified`;");
        $this->db->query("ALTER TABLE `tblgoals` ADD INDEX(`staff_id`);");

        $this->db->where('datecreated','0000-00-00 00:00:00');
        $total = $this->db->count_all_results('tblclients');

        // Will cause problems to execute the query below
        if($total > 0) {
            $this->db->where('datecreated','0000-00-00 00:00:00');
            $this->db->update('tblclients',array('datecreated'=>date('Y-m-d H:i:s')));
        }

        $this->db->query("ALTER TABLE `tblclients` ADD `addedfrom` INT NOT NULL DEFAULT '0' AFTER `show_primary_contact`;");

        update_option('update_info_message', '<div class="col-md-12">
            <div class="alert alert-success bold">
                <h4 class="bold">Hi! Thanks for updating Perfex CRM - You are using version 1.9.6</h4>
                <p>
                    This window will reload automaticaly in 10 seconds and will try to clear your browser/cloudflare cache, however its recomended to clear your browser cache manually.
                </p>
            </div>
        </div>
        <script>
            setTimeout(function(){
                window.location.reload();
            },10000);
        </script>');
    }
}
