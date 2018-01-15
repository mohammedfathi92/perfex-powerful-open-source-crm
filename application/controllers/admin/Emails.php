<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Emails extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('emails_model');
    }

    /* List all email templates */
    public function index()
    {
        if (!has_permission('email_templates', '', 'view')) {
            access_denied('email_templates');
        }
        $langCheckings = get_option('email_templates_language_checks');
        if($langCheckings == '') {
            $langCheckings = array();
        } else {
            $langCheckings = unserialize($langCheckings);
        }

        $this->db->where('language', 'english');
        $email_templates_english = $this->db->get('tblemailtemplates')->result_array();
        foreach ($this->perfex_base->get_available_languages() as $avLanguage) {
            if ($avLanguage != 'english') {
                foreach ($email_templates_english as $template) {

                    // Result is cached and stored in database
                    // This page may perform 1000 queries per request
                    if(isset($langCheckings[$template['slug'].'-'.$avLanguage])) {
                        continue;
                    }

                    $notExists = total_rows('tblemailtemplates', array(
                        'slug' => $template['slug'],
                        'language' => $avLanguage
                    )) == 0;

                    $langCheckings[$template['slug'].'-'.$avLanguage] = 1;

                    if ($notExists) {
                        $data              = array();
                        $data['slug']      = $template['slug'];
                        $data['type']      = $template['type'];
                        $data['language']  = $avLanguage;
                        $data['name']      = $template['name'] . ' [' . $avLanguage . ']';
                        $data['subject']   = $template['subject'];
                        $data['message']   = '';
                        $data['fromname']  = $template['fromname'];
                        $data['plaintext'] = $template['plaintext'];
                        $data['active']    = $template['active'];
                        $data['order']     = $template['order'];
                        $this->db->insert('tblemailtemplates', $data);

                    }
                }
            }
        }

        update_option('email_templates_language_checks',serialize($langCheckings));

        $data['staff']     = $this->emails_model->get(array(
            'type' => 'staff',
            'language' => 'english'
        ));

        $data['credit_notes']     = $this->emails_model->get(array(
            'type' => 'credit_note',
            'language' => 'english'
        ));

        $data['tasks']     = $this->emails_model->get(array(
            'type' => 'tasks',
            'language' => 'english'
        ));
        $data['client']    = $this->emails_model->get(array(
            'type' => 'client',
            'language' => 'english'
        ));
        $data['tickets']   = $this->emails_model->get(array(
            'type' => 'ticket',
            'language' => 'english'
        ));
        $data['invoice']   = $this->emails_model->get(array(
            'type' => 'invoice',
            'language' => 'english'
        ));
        $data['estimate']  = $this->emails_model->get(array(
            'type' => 'estimate',
            'language' => 'english'
        ));
        $data['contracts'] = $this->emails_model->get(array(
            'type' => 'contract',
            'language' => 'english'
        ));
        $data['proposals'] = $this->emails_model->get(array(
            'type' => 'proposals',
            'language' => 'english'
        ));
        $data['projects']  = $this->emails_model->get(array(
            'type' => 'project',
            'language' => 'english'
        ));
        $data['leads']     = $this->emails_model->get(array(
            'type' => 'leads',
            'language' => 'english'
        ));
        $data['title']     = _l('email_templates');

        $data['hasPermissionEdit'] = has_permission('email_templates','','edit');

        $this->load->view('admin/emails/email_templates', $data);
    }

    /* Edit email template */
    public function email_template($id)
    {
        if (!has_permission('email_templates', '', 'view')) {
            access_denied('email_templates');
        }
        if (!$id) {
            redirect(admin_url('emails'));
        }

        if ($this->input->post()) {
            if (!has_permission('email_templates', '', 'edit')) {
                access_denied('email_templates');
            }

            $data = $this->input->post();
            $tmp = $this->input->post(null,false);

            foreach($data['message'] as $key=>$contents){
                $data['message'][$key] = $tmp['message'][$key];
            }

            foreach($data['subject'] as $key=>$contents){
                $data['subject'][$key] = $tmp['subject'][$key];
            }

            $data['fromname'] = $tmp['fromname'];

            $success = $this->emails_model->update($data, $id);

            if ($success) {
                set_alert('success', _l('updated_successfully', _l('email_template')));
            }

            redirect(admin_url('emails/email_template/' . $id));
        }

        // English is not included here
        $data['available_languages'] = $this->perfex_base->get_available_languages();

        if (($key = array_search('english', $data['available_languages'])) !== false) {
            unset($data['available_languages'][$key]);
        }

        $data['available_merge_fields'] = get_available_merge_fields();
        $data['template']               = $this->emails_model->get_email_template_by_id($id);
        $title                          = $data['template']->name;
        $data['title']                  = $title;
        $this->load->view('admin/emails/template', $data);
    }

    public function enable_by_type($type){
        if(has_permission('email_templates','','edit')){
            $this->emails_model->mark_as_by_type($type,1);
        }
        redirect(admin_url('emails'));
    }

    public function disable_by_type($type){
        if(has_permission('email_templates','','edit')){
            $this->emails_model->mark_as_by_type($type,0);
        }
        redirect(admin_url('emails'));
    }

    public function enable($id){
        if(has_permission('email_templates','','edit')){
            $template = $this->emails_model->get_email_template_by_id($id);
            $this->emails_model->mark_as($template->slug,1);
        }
        redirect(admin_url('emails'));
    }

    public function disable($id){
        if(has_permission('email_templates','','edit')){
            $template = $this->emails_model->get_email_template_by_id($id);
            $this->emails_model->mark_as($template->slug,0);
        }

        redirect(admin_url('emails'));
    }

    /* Since version 1.0.1 - test your smtp settings */
    public function sent_smtp_test_email()
    {
        if ($this->input->post()) {
            // Simulate fake template to be parsed
            $template = new StdClass();
            $template->message = get_option('email_header').'This is test SMTP email. <br />If you received this message that means that your SMTP settings is set correctly.'.get_option('email_footer');
            $template->fromname = get_option('companyname');
            $template->subject = 'SMTP Setup Testing';

            $template = parse_email_template($template);

            do_action('before_send_test_smtp_email');
            $this->email->initialize();
            $this->email->set_newline("\r\n");
            $this->email->from(get_option('smtp_email'), $template->fromname);
            $this->email->to($this->input->post('test_email'));

            $systemBCC = get_option('bcc_emails');

            if($systemBCC != ""){
                $this->email->bcc($systemBCC);
            }

            $this->email->subject($template->subject);
            $this->email->message($template->message);
            if ($this->email->send(true)) {
                set_alert('success', 'Seems like your SMTP settings is set correctly. Check your email now.');
                do_action('smtp_test_email_success');
            } else {
                set_debug_alert('<h1>Your SMTP settings are not set correctly here is the debug log.</h1><br />' . $this->email->print_debugger());
                do_action('smtp_test_email_failed');
            }
        }
    }
}
