<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_151 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        $this->db->query("ALTER TABLE `tbltickets` ADD INDEX(`project_id`);");
        add_option('only_own_files_contacts',0);
        add_option('allow_primary_contact_to_view_edit_billing_and_shipping',0);
        add_option('estimate_due_after',7);

        $this->db->query("CREATE TABLE IF NOT EXISTS `tblcustomerfiles_shares` (
          `file_id` int(11) NOT NULL,
          `contact_id` int(11) NOT NULL
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $this->db->select('rel_id,id');
        $this->db->where('rel_type','customer');
        $this->db->where('visible_to_customer',1);
        $files = $this->db->get('tblfiles')->result_array();

        foreach($files as $file){
            $this->db->select('id');
            $this->db->where('userid',$file['rel_id']);
            $contacts = $this->db->get('tblcontacts')->result_array();
            foreach($contacts as $contact){
             $this->db->insert('tblcustomerfiles_shares',array('file_id'=>$file['id'],'contact_id'=>$contact['id']));
         }
     }
    // Nothing to do here only to update database version number
     update_option('update_info_message', '<div class="col-md-12">
        <div class="alert alert-success bold">
            <h4 class="bold">Hi! Thanks for updating Perfex CRM - You are using version 1.5.1</h4>
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
