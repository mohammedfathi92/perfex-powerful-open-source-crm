<?php
defined('BASEPATH') or exit('No direct script access allowed');
set_time_limit(0);
class Surveys extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('surveys_model');
    }

    /* List all surveys */
    public function index()
    {
        if (!has_permission('surveys', '', 'view')) {
            access_denied('surveys');
        }
        if ($this->input->is_ajax_request()) {
            $this->perfex_base->get_table_data('surveys');
        }
        $data['title'] = _l('surveys');
        $this->load->view('admin/surveys/all', $data);
    }

    /* Add new survey or update existing */
    public function survey($id = '')
    {
        if (!has_permission('surveys', '', 'view')) {
            access_denied('surveys');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            $data['description'] = $this->input->post('description', false);
            $data['viewdescription'] = $this->input->post('viewdescription', false);
            if ($id == '') {
                if (!has_permission('surveys', '', 'create')) {
                    access_denied('surveys');
                }
                $id = $this->surveys_model->add($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('survey')));
                    redirect(admin_url('surveys/survey/' . $id));
                }
            } else {
                if (!has_permission('surveys', '', 'edit')) {
                    access_denied('surveys');
                }
                $success = $this->surveys_model->update($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('survey')));
                }
                redirect(admin_url('surveys/survey/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('survey_lowercase'));
        } else {
            $this->load->model('leads_model');
            $data['leads_statuses'] = $this->leads_model->get_status();
            $data['customers_groups'] = $this->clients_model->get_groups();
            $survey           = $this->surveys_model->get($id);
            $data['send_log'] = $this->surveys_model->get_survey_send_log($id);
            $data['survey']   = $survey;
            $title            = $survey->subject;
        }
        $this->load->model('surveys_model');
        $data['mail_lists']          = $this->surveys_model->get_mail_lists();
        $data['found_custom_fields'] = false;
        $i                           = 0;
        foreach ($data['mail_lists'] as $mail_list) {
            $fields = $this->surveys_model->get_list_custom_fields($mail_list['listid']);
            if (count($fields) > 0) {
                $data['found_custom_fields'] = true;
            }
            $data['mail_lists'][$i]['customfields'] = $fields;
            $i++;
        }
        $data['title'] = $title;
        $this->load->view('admin/surveys/survey', $data);
    }

    /* Send survey to mail list */
    public function send($surveyid)
    {
        if (!has_permission('surveys', '', 'edit')) {
            access_denied('surveys');
        }
        if (!$surveyid) {
            redirect(admin_url('surveys'));
        }
        $this->load->model('surveys_model');

        $_lists      = array();
        $_all_emails = array();
        if ($this->input->post('send_survey_to')) {
            $lists = $this->input->post('send_survey_to');
            foreach ($lists as $key => $val) {
                // is mail list
                if (is_int($key)) {
                    $list   = $this->surveys_model->get_mail_lists($key);
                    $emails = $this->surveys_model->get_mail_list_emails($key);
                    foreach ($emails as $email) {
                        // We don't need to validate emails becuase email are already validated when added to mail list
                        array_push($_all_emails, array(
                            'listid' => $key,
                            'emailid' => $email['emailid'],
                            'email' => $email['email']
                        ));
                    }

                    if (count($emails) > 0) {
                        array_push($_lists, $list->name);
                    }
                } else {
                    if ($key == 'staff') {
                        // Pass second paramter to get all active staff, we don't need inactive staff
                        // If you want adjustments feel free to pass 0 or '' for all
                        $staff = $this->staff_model->get('', 1);
                        foreach ($staff as $email) {
                            array_push($_all_emails, $email['email']);
                        }
                        if (count($staff) > 0) {
                            array_push($_lists, 'survey_send_mail_list_staff');
                        }
                    } elseif ($key == 'clients') {
                        if ($this->input->post('ml_customers_all')) {
                            $clients = $this->clients_model->get_contacts();
                            foreach ($clients as $email) {
                                $added = true;
                                array_push($_all_emails, $email['email']);
                            }
                        } else {
                            foreach ($this->input->post('customer_group') as $group_id => $val) {
                                $clients = $this->clients_model->get_contacts('', 'active=1 AND userid IN (select customer_id from tblcustomergroups_in where groupid ='.$group_id.')');
                                foreach ($clients as $email) {
                                    $added = true;
                                    array_push($_all_emails, $email['email']);
                                }
                            }
                            $_all_emails = array_unique($_all_emails, SORT_REGULAR);
                        }

                        if (isset($added) > 0) {
                            array_push($_lists, 'survey_send_mail_list_clients');
                        }
                    } elseif ($key == 'leads') {
                        $this->load->model('leads_model');
                        if ($this->input->post('leads_status')) {
                            foreach ($this->input->post('leads_status') as $status_id =>$val) {
                                $leads = $this->leads_model->get('', array('status'=>$status_id));
                                foreach ($leads as $lead) {
                                    $added = true;
                                    if (!empty($lead['email']) && filter_var($lead['email'], FILTER_VALIDATE_EMAIL)) {
                                        array_push($_all_emails, $lead['email']);
                                    }
                                }
                            }
                            if (isset($added)) {
                                array_push($_lists, _l('leads'));
                            }
                        } else {
                            $leads = $this->leads_model->get('', array('lost'=>0));
                            foreach ($leads as $lead) {
                                if (!empty($lead['email']) && filter_var($lead['email'], FILTER_VALIDATE_EMAIL)) {
                                    array_push($_all_emails, $lead['email']);
                                }
                            }
                            if (count($leads)) {
                                array_push($_lists, 'leads');
                            }
                        }
                    }
                }
            }
        } else {
            set_alert('warning', _l('survey_no_mail_lists_selected'));
            redirect(admin_url('surveys/survey/' . $surveyid));
        }

        // We don't need to include in query CRON if 0 emails found
        $iscronfinished = 0;
        if (count($_all_emails) == 0) {
            $iscronfinished = 1;
        }
        $log_id = $this->surveys_model->init_survey_send_log($surveyid, $iscronfinished, $_lists);

        foreach ($_all_emails as $email) {
            // Is not from email lists
            if (!is_array($email)) {
                $this->db->insert('tblsurveysemailsendcron', array(
                    'email' => $email,
                    'surveyid' => $surveyid,
                    'log_id' => $log_id,
                ));
            } else {
                // Yay its a mail list
                // We will need this info for the custom fields when sending the survey
                $this->db->insert('tblsurveysemailsendcron', array(
                    'email' => $email['email'],
                    'surveyid' => $surveyid,
                    'listid' => $email['listid'],
                    'emailid' => $email['emailid'],
                    'log_id' => $log_id,
                ));
            }
        }

        set_alert('success', _l('survey_send_success_note', count($_all_emails)));
        redirect(admin_url('surveys/survey/' . $surveyid));
    }

    public function remove_survey_send($id)
    {
        if (!has_permission('surveys', '', 'delete')) {
            access_denied('Surveys');
        }
        $this->surveys_model->remove_survey_send($id);
        redirect($_SERVER['HTTP_REFERER']);
    }

    /* View survey participating results*/
    public function results($id)
    {
        if (!$id) {
            redirect(admin_url('surveys'));
        }
        $data['surveyid']  = $id;
        $data['bodyclass'] = 'survey_results';
        $survey            = $this->surveys_model->get($id);
        $data['survey']    = $survey;
        $data['title']     = _l('survey_result', $survey->subject);
        $this->load->view('admin/surveys/results', $data);
    }

    /* Delete survey from database */
    public function delete($id)
    {
        if (!has_permission('surveys', '', 'delete')) {
            access_denied('surveys');
        }
        if (!$id) {
            redirect(admin_url('surveys'));
        }
        $success = $this->surveys_model->delete($id);
        if ($success) {
            set_alert('success', _l('deleted', _l('survey')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('survey')));
        }
        redirect(admin_url('surveys'));
    }

    // Ajax
    /* Remove survey question */
    public function remove_question($questionid)
    {
        if (!has_permission('surveys', '', 'edit')) {
            echo json_encode(array(
                'success' => false,
                'message' => _l('access_denied')
            ));
            die();
        }
        if ($this->input->is_ajax_request()) {
            echo json_encode(array(
                'success' => $this->surveys_model->remove_question($questionid),
            ));
        }
    }

    /* Removes survey checkbox/radio description*/
    public function remove_box_description($questionboxdescriptionid)
    {
        if (!has_permission('surveys', '', 'edit')) {
            echo json_encode(array(
                'success' => false,
                'message' => _l('access_denied')
            ));
            die();
        }
        if ($this->input->is_ajax_request()) {
            echo json_encode(array(
                'success' => $this->surveys_model->remove_box_description($questionboxdescriptionid),
            ));
        }
    }

    /* Add box description */
    public function add_box_description($questionid, $boxid)
    {
        if (!has_permission('surveys', '', 'edit')) {
            echo json_encode(array(
                'success' => false,
                'message' => _l('access_denied')
            ));
            die();
        }
        if ($this->input->is_ajax_request()) {
            $boxdescriptionid = $this->surveys_model->add_box_description($questionid, $boxid);
            echo json_encode(array(
                'boxdescriptionid' => $boxdescriptionid,
            ));
        }
    }

    /* New survey question */
    public function add_survey_question()
    {
        if (!has_permission('surveys', '', 'edit')) {
            echo json_encode(array(
                'success' => false,
                'message' => _l('access_denied')
            ));
            die();
        }
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                echo json_encode(array(
                    'data' => $this->surveys_model->add_survey_question($this->input->post()),
                    'survey_question_only_for_preview' => _l('survey_question_only_for_preview'),
                    'survey_question_required' => _l('survey_question_required'),
                    'survey_question_string' => _l('question_string')
                ));
                die();
            }
        }
    }

    /* Update question */
    public function update_question()
    {
        if (!has_permission('surveys', '', 'edit')) {
            echo json_encode(array(
                'success' => false,
                'message' => _l('access_denied')
            ));
            die();
        }
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $this->surveys_model->update_question($this->input->post());
            }
        }
    }

    /* Reorder surveys */
    public function update_survey_questions_orders()
    {
        if (has_permission('surveys', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                if ($this->input->post()) {
                    $this->surveys_model->update_survey_questions_orders($this->input->post());
                }
            }
        }
    }

    /* Change survey status active or inactive*/
    public function change_survey_status($id, $status)
    {
        if (has_permission('surveys', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->surveys_model->change_survey_status($id, $status);
            }
        }
    }

    // MAIL LISTS
    /* List all mail lists */
    public function mail_lists()
    {
        if (!has_permission('surveys', '', 'view')) {
            access_denied('surveys');
        }
        if ($this->input->is_ajax_request()) {
            $this->perfex_base->get_table_data('mail_lists');
        }
        $data['title'] = _l('mail_lists');
        $this->load->view('admin/surveys/mail_lists/manage', $data);
    }

    /* Add or update mail list */
    public function mail_list($id = '')
    {
        if ($this->input->post()) {
            if ($id == '') {
                if (!has_permission('surveys', '', 'create')) {
                    access_denied('surveys');
                }
                $id = $this->surveys_model->add_mail_list($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('mail_list')));
                    redirect(admin_url('surveys/mail_list/' . $id));
                }
            } else {
                if (!has_permission('surveys', '', 'edit')) {
                    access_denied('surveys');
                }
                $success = $this->surveys_model->update_mail_list($this->input->post(), $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('mail_list')), 'refresh');
                }
                redirect(admin_url('surveys/mail_list/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('mail_list_lowercase'));
        } else {
            $list                  = $this->surveys_model->get_mail_lists($id);
            $data['list']          = $list;
            $data['custom_fields'] = $this->surveys_model->get_list_custom_fields($list->listid);
            $title                 = _l('edit', _l('mail_list_lowercase')) . ' ' . $list->name;
        }
        $data['title'] = $title;
        $this->load->view('admin/surveys/mail_lists/list', $data);
    }

    /* View mail list all added emails */
    public function mail_list_view($id)
    {
        if (!has_permission('surveys', '', 'view')) {
            access_denied('surveys');
        }
        if (!$id) {
            redirect(admin_url('surveys/mail_lists'));
        }
        $data = array();
        $data['id'] = $id;
        if (is_numeric($id)) {
            $data['custom_fields'] = $this->surveys_model->get_list_custom_fields($id);
        }
        if ($this->input->is_ajax_request()) {
            $this->perfex_base->get_table_Data('mail_list_view', array(
                'id' => $id,
                'data' => $data
            ));
        }
        if ($id == 'staff' || $id == 'clients' || $id == 'leads') {
            $list   = new stdClass();
            $title  = _l('clients_mail_lists');
            if ($id == 'clients') {
                $emails = $this->clients_model->get_contacts();
                $data['groups']         = $this->clients_model->get_groups();
            } elseif ($id == 'staff') {
                $title  = _l('staff_mail_lists');

                $emails = $this->staff_model->get();
            } elseif ($id == 'leads') {
                $this->load->model('leads_model');

                $data['statuses'] = $this->leads_model->get_status();
                $data['sources']  = $this->leads_model->get_source();

                $emails = $this->leads_model->get('', array('lost'=>0));
            }
            $list->emails = array();
            $i            = 0;
            foreach ($emails as $email) {
                if (empty($email['email'])) {
                    continue;
                }
                if ($id == 'leads') {
                    $list->emails[$i]['dateadded'] = $email['dateadded'];
                } else {
                    $list->emails[$i]['dateadded'] = $email['datecreated'];
                }
                $list->emails[$i]['email']     = $email['email'];
                $i++;
            }
            $data['list']  = $list;
            $data['title'] = $title;
            $fixed_list    = true;
        } else {
            $list          = $this->surveys_model->get_data_for_view_list($id);
            $data['title'] = $list->name;
            $data['list']  = $list;
            $fixed_list    = false;
        }
        $data['fixedlist'] = $fixed_list;
        $this->load->view('admin/surveys/mail_lists/list_view', $data);
    }

    /* Add single email to mail list / ajax*/
    public function add_email_to_list()
    {
        if (!has_permission('surveys', '', 'create')) {
            echo json_encode(array(
                'success' => false,
                'error_message' => _l('access_denied')
            ));
            die();
        }
        if ($this->input->post()) {
            if ($this->input->is_ajax_request()) {
                echo json_encode($this->surveys_model->add_email_to_list($this->input->post()));
                die();
            }
        }
    }

    /* Remove single email from mail list / ajax */
    public function remove_email_from_mail_list($emailid)
    {
        if (!has_permission('surveys', '', 'delete')) {
            echo json_encode(array(
                'success' => false,
                'message' => _l('access_denied')
            ));
            die();
        }
        if (!$emailid) {
            echo json_encode(array(
                'success' => false
            ));
            die();
        }
        echo json_encode($this->surveys_model->remove_email_from_mail_list($emailid));
        die();
    }

    /* Remove mail list custom field */
    public function remove_list_custom_field($fieldid)
    {
        if (!has_permission('surveys', '', 'delete')) {
            echo json_encode(array(
                'success' => false,
                'message' => _l('access_denied')
            ));
            die;
        }
        if ($this->input->is_ajax_request()) {
            echo json_encode($this->surveys_model->remove_list_custom_field($fieldid));
            die();
        }
    }

    /* Import .xls file with emails */
    public function import_emails()
    {
        if (!has_permission('surveys', '', 'create')) {
            access_denied('surveys');
        }
        require_once(APPPATH . 'third_party/Excel_reader/php-excel-reader/excel_reader2.php');
        require_once(APPPATH . 'third_party/Excel_reader/SpreadsheetReader.php');
        $filename = uniqid() . '_' . $_FILES["file_xls"]["name"];
        $temp_url = TEMP_FOLDER . $filename;
        if (move_uploaded_file($_FILES["file_xls"]["tmp_name"], $temp_url)) {
            try {
                $xls_emails = new SpreadsheetReader($temp_url);
            } catch (Exception $e) {
                die('Error loading file "' . pathinfo($temp_url, PATHINFO_BASENAME) . '": ' . $e->getMessage());
            }
            $total_duplicate_emails = 0;
            $total_invalid_address  = 0;
            $total_added_emails     = 0;
            $mails_failed_to_insert = 0;
            $listid                 = $this->input->post('listid');
            foreach ($xls_emails as $email) {
                if (isset($email[0]) && $email[0] !== '') {
                    $data['email'] = $email[0];
                    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                        $total_invalid_address++;
                        continue;
                    }
                    $data['listid'] = $listid;
                    if (count($email) > 1) {
                        $custom_fields       = $this->surveys_model->get_list_custom_fields($listid);
                        $total_custom_fields = count($custom_fields);
                        for ($i = 0; $i < $total_custom_fields; $i++) {
                            if ($email[$i + 1] !== '') {
                                $data['customfields'][$custom_fields[$i]['customfieldid']] = $email[$i + 1];
                            }
                        }
                    }
                    $success = $this->surveys_model->add_email_to_list($data);
                    if ($success['success'] == false && $success['duplicate'] == true) {
                        $total_duplicate_emails++;
                    } elseif ($success['success'] == false) {
                        $mails_failed_to_insert++;
                    } else {
                        $total_added_emails++;
                    }
                }
                if ($total_added_emails > 0 && $mails_failed_to_insert == 0) {
                    $_alert_type = 'success';
                } elseif ($total_added_emails == 0 && $mails_failed_to_insert > 0) {
                    $_alert_type = 'danger';
                } elseif ($total_added_emails > 0 && $mails_failed_to_insert > 0) {
                    $_alert_type = 'warning';
                } else {
                    $_alert_type = 'success';
                }
            }
            // Delete uploaded file
            unlink($temp_url);
            set_alert($_alert_type, _l('mail_list_total_imported', $total_added_emails) . '<br />' . _l('mail_list_total_duplicate', $total_duplicate_emails) . '<br />' . _l('mail_list_total_failed_to_insert', $mails_failed_to_insert) . '<br />' . _l('mail_list_total_invalid', $total_invalid_address));
        } else {
            set_alert('danger', _l('error_uploading_file'));
        }
        redirect(admin_url('surveys/mail_list_view/' . $listid));
    }

    /* Delete mail list from database */
    public function delete_mail_list($id)
    {
        if (!has_permission('surveys', '', 'delete')) {
            access_denied('surveys');
        }
        if (!$id) {
            redirect(admin_url('surveys/mail_lists'));
        }
        $success = $this->surveys_model->delete_mail_list($id);
        if ($success) {
            set_alert('success', _l('deleted', _l('mail_list')));
        }
        redirect(admin_url('surveys/mail_lists'));
    }
}
