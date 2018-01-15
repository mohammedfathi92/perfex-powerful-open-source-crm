<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function make()
    {
        $this->load->config('migration');
        if ($this->config->item('migration_enabled') == true) {
            if (!$this->input->get('old_base_url')) {
                echo '<h1>You need to pass old base url in the url like: ' . site_url('migration/make?old_base_url=http://myoldbaseurl.com/') . '</h1>';
                die;
            }

            $old_url = $this->input->get('old_base_url');
            $new_url = $this->config->item('base_url');
            if (!endsWith($old_url, '/')) {
                $old_url = $old_url . '/';
            }

            $tables       = array(
                array(
                    'table' => 'tblnotifications',
                    'field' => 'description'
                ),
                array(
                    'table' => 'tblnotifications',
                    'field' => 'additional_data'
                ),
                array(
                    'table' => 'tblnotes',
                    'field' => 'description'
                ),
                array(
                    'table' => 'tblemailtemplates',
                    'field' => 'message'
                ),
                array(
                    'table' => 'tblposts',
                    'field' => 'content'
                ),
                array(
                    'table' => 'tblpostcomments',
                    'field' => 'content'
                ),
                array(
                    'table' => 'tbloptions',
                    'field' => 'value'
                ),
                array(
                    'table' => 'tblstaff',
                    'field' => 'email_signature'
                ),
                array(
                    'table' => 'tblpredefinedreplies',
                    'field' => 'message'
                ),
                array(
                    'table' => 'tblprojectdiscussioncomments',
                    'field' => 'content'
                ),
                array(
                    'table' => 'tblprojectdiscussions',
                    'field' => 'description'
                ),
                array(
                    'table' => 'tblprojectnotes',
                    'field' => 'content'
                ),
                array(
                    'table' => 'tblprojects',
                    'field' => 'description'
                ),
                array(
                    'table' => 'tblreminders',
                    'field' => 'description'
                ),
                array(
                    'table' => 'tblstafftasks',
                    'field' => 'description'
                ),
                array(
                    'table' => 'tblstafftaskcomments',
                    'field' => 'content'
                ),
                array(
                    'table' => 'tblsurveys',
                    'field' => 'description'
                ),
                array(
                    'table' => 'tblsurveys',
                    'field' => 'viewdescription'
                ),
                array(
                    'table' => 'tblticketreplies',
                    'field' => 'message'
                ),
                array(
                    'table' => 'tbltickets',
                    'field' => 'message'
                ),
                array(
                    'table' => 'tbltodoitems',
                    'field' => 'description'
                ),
                array(
                    'table' => 'tblproposalcomments',
                    'field' => 'content'
                ),
                array(
                    'table' => 'tblproposals',
                    'field' => 'content'
                ),
                array(
                    'table' => 'tblleadactivitylog',
                    'field' => 'description'
                ),
                array(
                    'table' => 'tblknowledgebasegroups',
                    'field' => 'description'
                ),
                array(
                    'table' => 'tblknowledgebase',
                    'field' => 'description'
                ),
                array(
                    'table' => 'tblinvoices',
                    'field' => 'terms'
                ),
                array(
                    'table' => 'tblinvoices',
                    'field' => 'clientnote'
                ),
                array(
                    'table' => 'tblinvoices',
                    'field' => 'adminnote'
                ),
                array(
                    'table' => 'tblsalesactivity',
                    'field' => 'description'
                ),
                array(
                    'table' => 'tblsalesactivity',
                    'field' => 'additional_data'
                ),
                array(
                    'table' => 'tblestimates',
                    'field' => 'terms'
                ),
                array(
                    'table' => 'tblestimates',
                    'field' => 'clientnote'
                ),
                array(
                    'table' => 'tblestimates',
                    'field' => 'adminnote'
                ),
                array(
                    'table' => 'tblgoals',
                    'field' => 'description'
                ),
                array(
                    'table' => 'tblcontracts',
                    'field' => 'description'
                ),
                array(
                    'table' => 'tblcontracts',
                    'field' => 'content'
                )
            );
            $affectedRows = 0;
            foreach ($tables as $t) {
                $this->db->query('UPDATE `'.$t['table'].'` SET `'.$t['field'].'` = replace('.$t['field'].', "' . $old_url . '", "' . $new_url . '")');
                $affectedRows += $this->db->affected_rows();
            }
            echo '<h1>Total links replaced: ' . $affectedRows . '</h1>';
        } else {
            echo '<h1>Set migration_enabled to TRUE in application/config/migration.php</h1>';
        }
    }
}
