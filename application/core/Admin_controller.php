<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin_controller extends CRM_Controller
{
    private $_current_version;

    public function __construct()
    {
        parent::__construct();

        $this->_current_version = $this->misc_model->get_current_db_version();
        if ($this->misc_model->is_db_upgrade_required($this->_current_version)) {
            if ($this->input->post('upgrade_database')) {
                $this->misc_model->upgrade_database();
            }
            include_once(APPPATH . 'views/admin/includes/db_update_required.php');
            die;
        }

        if (CI_VERSION != '3.1.6') {
            echo '<h2>Additionally you will need to replace the <b>system</b> folder. We updated Codeigniter to 3.1.6.</h2>';
            echo '<p>From the newest downloaded files upload the <b>system</b> folder to your Perfex CRM installation directory.';
            die;
        }

        if(!extension_loaded('mbstring') && (!function_exists('mb_strtoupper') || !function_exists('mb_strtolower'))){
            die('<h1>"mbstring" PHP extension is not loaded. Enable this extension from cPanel or consult with your hosting provider to assist you enabling "mbstring" extension.</h4>');
        }

        $this->load->model('authentication_model');
        $this->authentication_model->autologin();

        if (!is_staff_logged_in()) {
            if (strpos(current_full_url(), 'authentication/admin') === false) {
                $this->session->set_userdata(array(
                    'red_url' => current_full_url()
                    ));
            }
            redirect(site_url('authentication/admin'));
        }

        // In case staff have setup logged in as client - This is important don't change it
        $this->session->unset_userdata('client_user_id');
        $this->session->unset_userdata('contact_user_id');
        $this->session->unset_userdata('client_logged_in');
        $this->session->unset_userdata('logged_in_as_client');

        // Update staff last activity
        $this->db->where('staffid',get_staff_user_id());
        $this->db->update('tblstaff',array('last_activity'=>date('Y-m-d H:i:s')));

        $this->load->model('staff_model');

        $is_ajax_request = $this->input->is_ajax_request();
        // Do not check on ajax requests
        if (!$is_ajax_request) {
            if (ENVIRONMENT == 'production' && is_admin()) {
                if ($this->config->item('encryption_key') === '') {
                    die('<h1>Encryption key not sent in application/config/config.php</h1>For more info visit <a href="http://www.perfexcrm.com/knowledgebase/encryption-key/">Encryption key explained</a> FAQ3');
                } elseif (strlen($this->config->item('encryption_key')) != 32) {
                    die('<h1>Encryption key length should be 32 charachters</h1>For more info visit <a href="https://help.perfexcrm.com/encryption-key-explained/">Encryption key explained</a>');
                }
            }
            _maybe_system_setup_warnings();
        }

        if (is_mobile()) {
            $this->session->set_userdata(array('is_mobile' => true));
        } else {
            $this->session->unset_userdata('is_mobile');
        }

        $currentUser = $this->staff_model->get(get_staff_user_id());

        // Deleted or inactive but have session
        if(!$currentUser || $currentUser->active == 0){
            $this->load->model('authentication_model');
            $this->authentication_model->logout();
            redirect(site_url('authentication/admin'));
        }

        $GLOBALS['current_user'] = $currentUser;
        $language = load_admin_language();

        $auto_loaded_vars = array(
            'current_user' => $currentUser,
            'app_language'=>$language,
            'locale' => get_locale_key($language),
            'unread_notifications' => total_rows('tblnotifications',array('touserid'=>get_staff_user_id(),'isread'=>0)),
            'google_api_key' => get_option('google_api_key'),
            'current_version' => $this->_current_version,
            'tasks_filter_assignees' => $this->get_tasks_distinct_assignees(),
            'task_statuses' => $this->tasks_model->get_statuses(),
        );

        $auto_loaded_vars = do_action('before_set_auto_loaded_vars_admin_area', $auto_loaded_vars);

        $this->load->vars($auto_loaded_vars);

        if(!$is_ajax_request){
            $this->init_quick_actions_links();
        }
    }

    public function get_tasks_distinct_assignees()
    {
        return $this->misc_model->get_tasks_distinct_assignees();
    }

    private function init_quick_actions_links()
    {
        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('invoice'),
            'permission' => 'invoices',
            'url' => 'invoices/invoice'
            ));

        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('estimate'),
            'permission' => 'estimates',
            'url' => 'estimates/estimate'
            ));

        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('proposal'),
            'permission' => 'proposals',
            'url' => 'proposals/proposal'
            ));

         $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('credit_note'),
            'permission' => 'credit_notes',
            'url' => 'credit_notes/credit_note'
            ));


        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('client'),
            'permission' => 'customers',
            'url' => 'clients/client'
            ));


        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('project'),
            'url' => 'projects/project',
            'permission' => 'projects'
            ));


        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('task'),
            'url' => '#',
            'custom_url' => true,
            'href_attributes' => array(
                'onclick' => 'new_task();return false;'
                ),
            'permission' => 'tasks'
            ));

          $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('lead'),
            'url' => '#',
            'custom_url' => true,
            'permission' => 'is_staff_member',
            'href_attributes' => array(
                'onclick' => 'init_lead(); return false;'
                )
            ));

           $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('expense'),
            'permission' => 'expenses',
            'url' => 'expenses/expense'
            ));


        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('contract'),
            'permission' => 'contracts',
            'url' => 'contracts/contract'
            ));


        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('goal'),
            'url' => 'goals/goal',
            'permission' => 'goals'
            ));

        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('kb_article'),
            'permission' => 'knowledge_base',
            'url' => 'knowledge_base/article'
            ));

        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('survey'),
            'permission' => 'surveys',
            'url' => 'surveys/survey'
            ));

        $tickets = array(
            'name' => _l('ticket'),
            'url' => 'tickets/add'
            );
        if (get_option('access_tickets_to_none_staff_members') == 0 && !is_staff_member()) {
            $tickets['permission'] = 'is_staff_member';
        }

        $this->perfex_base->add_quick_actions_link($tickets);

        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('staff_member'),
            'url' => 'staff/member',
            'permission' => 'staff'
            ));

        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('calendar_event'),
            'url' => 'utilities/calendar?new_event=true&date='._d(date('Y-m-d')),
            'permission' => ''
            ));
    }
}
