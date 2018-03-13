<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Forms extends CRM_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        show_404();
    }

    public function wtl($key)
    {
        $this->load->model('leads_model');
        $form = $this->leads_model->get_form(array(
            'form_key' => $key
            ));

        if (!$form) {
            show_404();
        }

        $data['form_fields'] = json_decode($form->form_data);
        if (!$data['form_fields']) {
            $data['form_fields'] = array();
        }
        if ($this->input->post('key')) {
            if ($this->input->post('key') == $key) {
                $post_data = $this->input->post();

                if (get_option('recaptcha_secret_key') != '' && get_option('recaptcha_site_key') != '' && $form->recaptcha == 1) {
                    if (!do_recaptcha_validation($post_data['g-recaptcha-response'])) {
                        echo json_encode(array(
                            'success' => false,
                            'message' => _l('recaptcha_error')
                            ));
                        die;
                    }
                }

                if (isset($post_data['g-recaptcha-response'])) {
                    unset($post_data['g-recaptcha-response']);
                }

                unset($post_data['key']);

                $regular_fields = array();
                $custom_fields  = array();
                foreach ($post_data as $name => $val) {
                    if (strpos($name, 'form-cf-') !== false) {
                        array_push($custom_fields, array(
                            'name' => $name,
                            'value' => $val
                            ));
                    } else {
                        if ($this->db->field_exists($name, 'tblleads')) {
                            if ($name == 'country') {
                                if (!is_numeric($val)) {
                                    if ($val == '') {
                                        $val = 0;
                                    } else {
                                        $this->db->where('iso2', $val);
                                        $this->db->or_where('short_name', $val);
                                        $this->db->or_where('long_name', $val);
                                        $country = $this->db->get('tblcountries')->row();
                                        if ($country) {
                                            $val = $country->country_id;
                                        } else {
                                            $val = 0;
                                        }
                                    }
                                }
                            } else if($name == 'address'){
                                $val = trim($val);
                                $val = nl2br($val);
                            }

                            $regular_fields[$name] = $val;
                        }
                    }
                }
                $success      = false;
                $insert_to_db = true;

                if ($form->allow_duplicate == 0) {
                    $where = array();
                    if (!empty($form->track_duplicate_field) && isset($regular_fields[$form->track_duplicate_field])) {
                        $where[$form->track_duplicate_field] = $regular_fields[$form->track_duplicate_field];
                    }
                    if (!empty($form->track_duplicate_field_and) && isset($regular_fields[$form->track_duplicate_field_and])) {
                        $where[$form->track_duplicate_field_and] = $regular_fields[$form->track_duplicate_field_and];
                    }

                    if (count($where) > 0) {

                        $total = total_rows('tblleads', $where);

                        $duplicateLead = false;
                        /**
                         * Check if the lead is only 1 time duplicate
                         * Because we wont be able to know how user is tracking duplicate and to send the email template for
                         * the request
                         */
                        if($total == 1){

                            $this->db->select('email,id')
                            ->where($where);

                            $duplicateLead = $this->db->get('tblleads')->row();
                        }

                        if ($total > 0) {
                            // Success set to true for the response.
                            $success      = true;
                            $insert_to_db = false;
                            if ($form->create_task_on_duplicate == 1) {
                                $task_name_from_form_name = false;
                                $task_name                = '';
                                if (isset($regular_fields['name'])) {
                                    $task_name = $regular_fields['name'];
                                } elseif (isset($regular_fields['email'])) {
                                    $task_name = $regular_fields['email'];
                                } elseif (isset($regular_fields['company'])) {
                                    $task_name = $regular_fields['company'];
                                } else {
                                    $task_name_from_form_name = true;
                                    $task_name                = $form->name;
                                }
                                if ($task_name_from_form_name == false) {
                                    $task_name .= ' - ' . $form->name;
                                }

                                $description          = '';
                                $custom_fields_parsed = array();
                                foreach ($custom_fields as $key => $field) {
                                    $custom_fields_parsed[$field['name']] = $field['value'];
                                }

                                $all_fields    = array_merge($regular_fields, $custom_fields_parsed);
                                $fields_labels = array();
                                foreach ($data['form_fields'] as $f) {
                                    if ($f->type != 'header' && $f->type != 'paragraph' && $f->type != 'file') {
                                        $fields_labels[$f->name] = $f->label;
                                    }
                                }

                                $description .= $form->name . '<br /><br />';
                                foreach ($all_fields as $name => $val) {
                                    if (isset($fields_labels[$name])) {
                                        if ($name == 'country' && is_numeric($val)) {
                                            $c = get_country($val);
                                            if ($c) {
                                                $val = $c->short_name;
                                            } else {
                                                $val = 'Unknown';
                                            }
                                        }

                                        $description .= $fields_labels[$name] . ': ' . $val . '<br />';
                                    }
                                }

                                $task_data = array(
                                    'name' => $task_name,
                                    'priority' => get_option('default_task_priority'),
                                    'dateadded' => date('Y-m-d H:i:s'),
                                    'startdate' => date('Y-m-d'),
                                    'addedfrom' => $form->responsible,
                                    'status' => 1,
                                    'description' => $description
                                    );

                                $task_data = do_action('before_add_task', $task_data);
                                $this->db->insert('tblstafftasks', $task_data);
                                $task_id = $this->db->insert_id();
                                if ($task_id) {


                                    $attachment = handle_task_attachments_array($task_id, 'file-input');

                                    if ($attachment && count($attachment) > 0) {
                                        $this->tasks_model->add_attachment_to_database($task_id, $attachment,false,false);
                                    }

                                    $assignee_data = array(
                                        'taskid' => $task_id,
                                        'assignee' => $form->responsible
                                        );
                                    $this->tasks_model->add_task_assignees($assignee_data, true);

                                    do_action('after_add_task', $task_id);

                                    if($duplicateLead && $duplicateLead->email != ''){
                                        $this->load->model('emails_model');
                                        $merge_fields = get_lead_merge_fields($duplicateLead->id);
                                        $this->emails_model->send_email_template('new-web-to-lead-form-submitted', $duplicateLead->email, $merge_fields);
                                    }
                                }
                            }
                        }
                    }
                }

                if ($insert_to_db == true) {
                    $regular_fields['status'] = $form->lead_status;
                    if ((isset($regular_fields['name']) && empty($regular_fields['name'])) || !isset($regular_fields['name'])) {
                        $regular_fields['name'] = 'Unknown';
                    }
                    $regular_fields['source']       = $form->lead_source;
                    $regular_fields['addedfrom']    = 0;
                    $regular_fields['lastcontact']  = null;
                    $regular_fields['assigned']     = $form->responsible;
                    $regular_fields['dateadded']    = date('Y-m-d H:i:s');
                    $regular_fields['from_form_id'] = $form->id;
                    $regular_fields['is_public'] = $form->mark_public;
                    $this->db->insert('tblleads', $regular_fields);
                    $lead_id = $this->db->insert_id();

                    do_action('lead_created', array(
                        'lead_id' => $lead_id,
                        'web_to_lead_form' => true
                        ));

                    $success = false;
                    if ($lead_id) {
                        $success = true;

                        $this->leads_model->log_lead_activity($lead_id, 'not_lead_imported_from_form', true, serialize(array(
                            $form->name
                            )));
                        // /handle_custom_fields_post
                        $custom_fields_build['leads'] = array();
                        foreach ($custom_fields as $cf) {
                            $cf_id                                = strafter($cf['name'], 'form-cf-');
                            $custom_fields_build['leads'][$cf_id] = $cf['value'];
                        }

                        $this->leads_model->lead_assigned_member_notification($lead_id, $form->responsible, true);
                        handle_custom_fields_post($lead_id, $custom_fields_build);
                        handle_lead_attachments($lead_id, 'file-input', $form->name);

                        if ($form->notify_lead_imported != 0) {
                            if ($form->notify_type == 'assigned') {
                                $to_responsible = true;
                            } else {
                                $ids = @unserialize($form->notify_ids);
                                $to_responsible = false;
                                if ($form->notify_type == 'specific_staff') {
                                    $field = 'staffid';
                                } elseif ($form->notify_type == 'roles') {
                                    $field = 'role';
                                }
                            }

                            if ($to_responsible == false && is_array($ids) && count($ids) > 0) {
                                $this->db->where('active', 1);
                                $this->db->where_in($field, $ids);
                                $staff = $this->db->get('tblstaff')->result_array();
                            } else {
                                $staff = array(
                                            array(
                                                'staffid' => $form->responsible
                                                )
                                        );
                            }
                            $notifiedUsers = array();
                            foreach ($staff as $member) {
                                if ($member['staffid'] != 0) {
                                    $notified = add_notification(array(
                                        'description' => 'not_lead_imported_from_form',
                                        'touserid' => $member['staffid'],
                                        'fromcompany' => 1,
                                        'fromuserid' => null,
                                        'additional_data' => serialize(array(
                                            $form->name
                                            )),
                                        'link' => '#leadid=' . $lead_id
                                        ));
                                    if ($notified) {
                                        array_push($notifiedUsers, $member['staffid']);
                                    }
                                }
                            }
                            pusher_trigger_notification($notifiedUsers);
                        }
                        if(isset($regular_fields['email']) && $regular_fields['email'] != ''){
                            // System default language will be used here when sending this email template
                            // No need to set rel id and rel type
                            $this->load->model('emails_model');
                            $merge_fields = get_lead_merge_fields($lead_id);
                            $this->emails_model->send_email_template('new-web-to-lead-form-submitted', $regular_fields['email'], $merge_fields);
                        }
                    }
                } // end insert_to_db
                if ($success == true) {
                    if (!isset($lead_id)) {
                        $lead_id = 0;
                    }
                    if (!isset($task_id)) {
                        $task_id = 0;
                    }
                    do_action('web_to_lead_form_submitted', array(
                        'lead_id' => $lead_id,
                        'form_id' => $form->id,
                        'task_id' => $task_id
                        ));
                }
                echo json_encode(array(
                    'success' => $success,
                    'message' => $form->success_submit_msg
                    ));
                die;
            }
        }

        $data['form'] = $form;
        $this->load->view('forms/web_to_lead', $data);
    }

    public function ticket()
    {
        $form = new stdClass();
        $form->language = get_option('active_language');
        $form->recaptcha = 1;

        $this->lang->load($form->language . '_lang', $form->language);
        if (file_exists(APPPATH . 'language/' . $form->language . '/custom_lang.php')) {
            $this->lang->load('custom_lang', $form->language);
        }

        $form->success_submit_msg = _l('success_submit_msg');

        $form = do_action('ticket_form_settings',$form);

        if ($this->input->post() && $this->input->is_ajax_request()) {

                $post_data = $this->input->post();

                if(!isset($post_data['subject']) || isset($post_data['subject']) && empty($post_data['subject'])){
                    die('Busted');
                }

                if (get_option('recaptcha_secret_key') != '' && get_option('recaptcha_site_key') != '' && $form->recaptcha == 1) {
                    if (!do_recaptcha_validation($post_data['g-recaptcha-response'])) {
                        echo json_encode(array(
                            'success' => false,
                            'message' => _l('recaptcha_error')
                            ));
                        die;
                    }
                }

                if (isset($post_data['g-recaptcha-response'])) {
                    unset($post_data['g-recaptcha-response']);
                }


                $success      = false;

                $this->db->where('email', $post_data['email']);
                $result = $this->db->get('tblcontacts')->row();

                if ($result) {
                    $post_data['userid']    = $result->userid;
                    $post_data['contactid']    = $result->id;
                    unset($post_data['email']);
                    unset($post_data['name']);
                }

                $this->load->model('tickets_model');
                $ticket_id = $this->tickets_model->add($post_data);

                if ($ticket_id) {
                    $success = true;
                }

                if ($success == true) {
                    do_action('ticket_form_submitted', array(
                        'ticket_id' => $ticket_id,
                     ));
                }

                echo json_encode(array(
                    'success' => $success,
                    'message' => $form->success_submit_msg
                    ));

                die;
        }

        $this->load->model('tickets_model');
        $this->load->model('departments_model');
        $data['departments']          = $this->departments_model->get();
        $data['priorities']           = $this->tickets_model->get_priority();

        $data['priorities']['callback_translate'] = 'ticket_priority_translate';
        $data['services']             = $this->tickets_model->get_service();

        $data['form'] = $form;
        $this->load->view('forms/ticket', $data);
    }
}
