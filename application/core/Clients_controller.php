<?php
defined('BASEPATH') or exit('No direct script access allowed');

define('CLIENTS_AREA', true);

class Clients_controller extends CRM_Controller
{
    public $template = array();
    public $data = array();
    public $use_footer = true;
    public $use_head = true;
    public $add_scripts = true;
    public $use_submenu = true;
    public $use_navigation = true;

    public function __construct()
    {
        parent::__construct();

        $language = load_client_language();

        $this->load->library('form_validation');
        $this->form_validation->set_message('required', _l('form_validation_required'));
        $this->form_validation->set_message('valid_email', _l('form_validation_valid_email'));
        $this->form_validation->set_message('matches', _l('form_validation_matches'));
        $this->form_validation->set_message('is_unique', _l('form_validation_is_unique'));

        $this->load->model('authentication_model');
        $this->authentication_model->autologin();

        $this->load->model('tickets_model');
        $this->load->model('departments_model');
        $this->load->model('currencies_model');
        $this->load->model('invoices_model');
        $this->load->model('estimates_model');
        $this->load->model('proposals_model');
        $this->load->model('projects_model');
        $this->load->model('announcements_model');

        $_auto_loaded_vars = array(
            'departments' => $this->departments_model->get(false, true),
            'priorities' => $this->tickets_model->get_priority(),
            'ticket_statuses' => $this->tickets_model->get_ticket_status(),
            'currencies' => $this->currencies_model->get(),
            'locale'=>get_locale_key($language),
            'language'=>$language,
            );

        if (get_option('services') == 1) {
            $_auto_loaded_vars['services'] = $this->tickets_model->get_service();
        }

        $this->load->model('knowledge_base_model');

        if (is_client_logged_in()) {
            $contact = $this->clients_model->get_contact(get_contact_user_id());

            if (!$contact || $contact->active == 0) {
                $this->load->model('authentication_model');
                $this->authentication_model->logout(true);
                redirect(site_url());
            }

            $_auto_loaded_vars['total_undismissed_announcements'] = $this->announcements_model->get_total_undismissed_announcements();
            $_auto_loaded_vars['client'] = $this->clients_model->get(get_client_user_id());
            $_auto_loaded_vars['contact'] = $contact;
        }

        $this->load->vars($_auto_loaded_vars);
    }

    public function layout()
    {
        $this->data['use_navigation'] = true;
        if ($this->use_navigation == false) {
            $this->data['use_navigation'] = false;
        }

        $this->data['use_submenu'] = true;
        if ($this->use_submenu == false) {
            $this->data['use_submenu'] = false;
        }

        $this->template['head'] = '';
        if ($this->use_head == true) {
            $this->template['head'] = $this->load->view('themes/' . active_clients_theme() . '/head', $this->data, true);
        }

        $this->template['view'] = $this->load->view('themes/' . active_clients_theme() . '/views/' . $this->view, $this->data, true);

        $this->template['footer'] = '';
        if ($this->use_footer == true) {
            $this->template['footer'] = $this->load->view('themes/' . active_clients_theme() . '/footer', $this->data, true);
        }

        $this->template['scripts'] = '';
        if ($this->add_scripts == true) {
            $this->template['scripts'] = $this->load->view('themes/' . active_clients_theme() . '/scripts', $this->data, true);
        }

        $this->load->view('themes/' . active_clients_theme() . '/index', $this->template);
    }
}
