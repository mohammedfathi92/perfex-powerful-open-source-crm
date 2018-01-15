<?php
defined('BASEPATH') OR exit('No direct script access allowed');

@ini_set('memory_limit', '128M');
@ini_set('max_execution_time', 240);

class Migration_Version_162 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {

            $this->db->query("RENAME TABLE `tblpredifinedreplies` TO `tblpredefinedreplies`;");

            $this->db->query('UPDATE `tbloptions` SET `value` = replace(value, "predifined", "predefined") WHERE name="setup_menu_active"');
            $this->db->query('UPDATE `tbloptions` SET `value` = replace(value, "predifined", "predefined") WHERE name="setup_menu_inactive"');

            $this->db->query("ALTER TABLE `tblstafftaskcomments` ADD `file_id` INT NOT NULL DEFAULT '0' AFTER `contact_id`;");
            $this->db->query("ALTER TABLE `tblstafftaskcomments` ADD INDEX(`file_id`);");

            $this->db->where('name', 'defaut_leads_kanban_sort');
            $this->db->update('tbloptions', array(
                'name' => 'default_leads_kanban_sort'
                ));

            $this->db->where('name', 'defaut_leads_kanban_sort_type');
            $this->db->update('tbloptions', array(
                'name' => 'default_leads_kanban_sort_type'
                ));

            $this->db->where('name', 'defaut_proposals_pipeline_sort_type');
            $this->db->update('tbloptions', array(
                'name' => 'default_proposals_pipeline_sort_type'
                ));

            $this->db->where('name', 'defaut_estimates_pipeline_sort_type');
            $this->db->update('tbloptions', array(
                'name' => 'default_estimates_pipeline_sort_type'
                ));

            $this->db->where('name','pdf_text_color');
            $this->db->delete('tbloptions');

            update_option('update_info_message', '<div class="col-md-12">
                <div class="alert alert-success bold">
                    <h4 class="bold">Hi! Thanks for upgrading. You are using version 1.6.2</h4>
                    <p>
                        This window will reload automaticaly in 10 seconds and will try to clear your browser cache, however its recomended to clear your browser cache (inclufing Cloudflare if you are using) manually.
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
