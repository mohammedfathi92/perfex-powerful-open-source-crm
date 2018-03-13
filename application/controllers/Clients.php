<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Clients extends Clients_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->form_validation->set_error_delimiters('<p class="text-danger alert-validation">', '</p>');
        do_action('after_clients_area_init', $this);
    }

    public function index()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('clients/login'));
        }
        $data['is_home'] = true;
        $this->load->model('reports_model');
        $data['payments_years'] = $this->reports_model->get_distinct_customer_invoices_years();

        $data['title'] = get_company_name(get_client_user_id());
        $this->data    = $data;
        $this->view    = 'home';
        $this->layout();
    }

    public function announcements()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('clients/login'));
        }
        $data['title']         = _l('announcements');
        $data['announcements'] = $this->announcements_model->get();
        $this->data            = $data;
        $this->view            = 'announcements';
        $this->layout();
    }

    public function announcement($id)
    {
        if (!is_client_logged_in()) {
            redirect(site_url('clients/login'));
        }
        $data['announcement'] = $this->announcements_model->get($id);
        $data['title']        = $data['announcement']->name;
        $this->data           = $data;
        $this->view           = 'announcement';
        $this->layout();
    }

    public function calendar()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('clients/login'));
        }
        $data['title'] = _l('calendar');
        $this->view            = 'calendar';
        $this->data            = $data;
        $this->layout();
    }

    public function get_calendar_data()
    {
        if (!is_client_logged_in()) {
            echo json_encode(array());
            die;
        }
        $this->load->model('utilities_model');
        $data = $this->utilities_model->get_calendar_data(
            $this->input->get('start'),
            $this->input->get('end'),
            get_user_id_by_contact_id(get_contact_user_id()),
            get_contact_user_id()
        );

        echo json_encode($data);
    }

    public function projects($status = '')
    {
        if (!is_client_logged_in()) {
            redirect(site_url('clients/login'));
        }

        if (!has_contact_permission('projects')) {
            set_alert('warning', _l('access_denied'));
            redirect(site_url());
        }


        $data['project_statuses'] = $this->projects_model->get_project_statuses();

        $where = 'clientid='.get_client_user_id();

        if (is_numeric($status)) {
            $where .= ' AND status='.$status;
        } else {
            $where .= ' AND status IN (';
            foreach ($data['project_statuses'] as $projectStatus) {
                if (isset($projectStatus['filter_default']) && $projectStatus['filter_default'] == true) {
                    $where .= $projectStatus['id'] . ',';
                }
            }
            $where = rtrim($where, ',');
            $where .= ')';
        }
        $data['projects']         = $this->projects_model->get('', $where);
        $data['title']            = _l('clients_my_projects');
        $this->data               = $data;
        $this->view               = 'projects';
        $this->layout();
    }

    public function project($id)
    {
        if (!is_client_logged_in()) {
            redirect_after_login_to_current_url();
            redirect(site_url('clients/login'));
        }
        if (!has_contact_permission('projects')) {
            set_alert('warning', _l('access_denied'));
            redirect(site_url());
        }
        $project = $this->projects_model->get($id, array(
            'clientid' => get_client_user_id(),
        ));

        $data['project'] = $project;
        $data['project']->settings->available_features = unserialize($data['project']->settings->available_features);

        $data['title'] = $data['project']->name;
        if ($this->input->post('action')) {
            $action = $this->input->post('action');

            switch ($action) {
                  case 'new_task':
                  case 'edit_task':

                    $data = $this->input->post();
                    $task_id = false;
                    if (isset($data['task_id'])) {
                        $task_id = $data['task_id'];
                        unset($data['task_id']);
                    }

                    $data['rel_type'] = 'project';
                    $data['rel_id'] = $project->id;
                    $data['description'] = nl2br($data['description']);

                    $assignees = isset($data['assignees']) ? $data['assignees'] : array();
                    if (isset($data['assignees'])) {
                        unset($data['assignees']);
                    }
                    unset($data['action']);

                    if (!$task_id) {
                        $task_id = $this->tasks_model->add($data, true);
                        if ($task_id) {
                            foreach ($assignees as $assignee) {
                                $this->tasks_model->add_task_assignees(array('taskid'=>$task_id, 'assignee'=>$assignee), false, true);
                            }
                            $uploadedFiles = handle_task_attachments_array($task_id);
                            if ($uploadedFiles && is_array($uploadedFiles)) {
                                foreach ($uploadedFiles as $file) {
                                    $file['contact_id'] = get_contact_user_id();
                                    $this->misc_model->add_attachment_to_database($task_id, 'task', array($file));
                                }
                            }
                            set_alert('success', _l('added_successfully', _l('task')));
                            redirect(site_url('clients/project/' . $project->id . '?group=project_tasks&taskid='.$task_id));
                        }
                    } else {
                        if ($project->settings->edit_tasks == 1
                            && total_rows('tblstafftasks', array('is_added_from_contact'=>1, 'addedfrom'=>get_contact_user_id())) > 0) {
                            $affectedRows = 0;
                            $updated = $this->tasks_model->update($data, $task_id, true);
                            if ($updated) {
                                $affectedRows++;
                            }

                            $currentAssignees = $this->tasks_model->get_task_assignees($task_id);
                            $currentAssigneesIds = array();
                            foreach ($currentAssignees as $assigned) {
                                array_push($currentAssigneesIds, $assigned['assigneeid']);
                            }

                            $totalAssignees = count($assignees);

                            /**
                             * In case when contact created the task and then was able to view team members
                             * Now in this case he still can view team members and can edit them
                             */
                            if ($totalAssignees == 0 && $project->settings->view_team_members == 1) {
                                $this->db->where('taskid', $task_id);
                                $this->db->delete('tblstafftaskassignees');
                            } elseif ($totalAssignees > 0 && $project->settings->view_team_members == 1) {
                                foreach ($currentAssignees as $assigned) {
                                    if (!in_array($assigned['assigneeid'], $assignees)) {
                                        if ($this->tasks_model->remove_assignee($assigned['id'], $task_id)) {
                                            $affectedRows++;
                                        }
                                    }
                                }
                                foreach ($assignees as $assignee) {
                                    if (!$this->tasks_model->is_task_assignee($assignee, $task_id)) {
                                        if ($this->tasks_model->add_task_assignees(array('taskid'=>$task_id, 'assignee'=>$assignee), false, true)) {
                                            $affectedRows++;
                                        }
                                    }
                                }
                            }
                            if ($affectedRows > 0) {
                                set_alert('success', _l('updated_successfully', _l('task')));
                            }
                            redirect(site_url('clients/project/' . $project->id . '?group=project_tasks&taskid='.$task_id));
                        }
                    }

                    redirect(site_url('clients/project/' . $project->id . '?group=project_tasks'));
                    break;
                case 'discussion_comments':
                    echo json_encode($this->projects_model->get_discussion_comments($this->input->post('discussion_id'), $this->input->post('discussion_type')));
                    die;
                case 'new_discussion_comment':
                    echo json_encode($this->projects_model->add_discussion_comment($this->input->post(), $this->input->post('discussion_id'), $this->input->post('discussion_type')));
                    die;
                    break;
                case 'update_discussion_comment':
                    echo json_encode($this->projects_model->update_discussion_comment($this->input->post(), $this->input->post('discussion_id')));
                    die;
                    break;
                case 'delete_discussion_comment':
                    echo json_encode($this->projects_model->delete_discussion_comment($this->input->post('id')));
                    die;
                    break;
                case 'new_discussion':
                    $discussion_data = $this->input->post();
                    unset($discussion_data['action']);
                    $success = $this->projects_model->add_discussion($discussion_data);
                    if ($success) {
                        set_alert('success', _l('added_successfully', _l('project_discussion')));
                    }
                    redirect(site_url('clients/project/' . $id . '?group=project_discussions'));
                    break;
                case 'upload_file':
                    handle_project_file_uploads($id);
                    die;
                    break;
                case 'project_file_dropbox':
                        $data = array();
                        $data['project_id'] = $id;
                        $data['files'] = $this->input->post('files');
                        $data['external'] = $this->input->post('external');
                        $data['visible_to_customer'] = 1;
                        $data['contact_id'] = get_contact_user_id();
                        $this->projects_model->add_external_file($data);
                die;
                break;
                case 'get_file':
                    $file_data['discussion_user_profile_image_url'] = contact_profile_image_url(get_contact_user_id());
                    $file_data['current_user_is_admin']             = false;
                    $file_data['file']                              = $this->projects_model->get_file($this->input->post('id'), $this->input->post('project_id'));

                    if (!$file_data['file']) {
                        header("HTTP/1.0 404 Not Found");
                        die;
                    }
                    echo get_template_part('projects/file', $file_data, true);
                    die;
                    break;
                case 'update_file_data':
                    $file_data = $this->input->post();
                    unset($file_data['action']);
                    $this->projects_model->update_file_data($file_data);
                    break;
                case 'upload_task_file':
                    $taskid = $this->input->post('task_id');
                    $files   = handle_task_attachments_array($taskid, 'file');
                    if ($files) {
                        $i = 0;
                        $len = count($files);
                        foreach ($files as $file) {
                            $file['contact_id'] = get_contact_user_id();
                            $file['staffid'] = 0;
                            $this->tasks_model->add_attachment_to_database($taskid, array($file), false, ($i == $len - 1 ? true : false));
                            $i++;
                        }
                    }
                    die;
                    break;
                case 'add_task_external_file':
                    $taskid                = $this->input->post('task_id');
                    $file                  = $this->input->post('files');
                    $file[0]['contact_id'] = get_contact_user_id();
                    $file[0]['staffid']    = 0;
                    $this->tasks_model->add_attachment_to_database($this->input->post('task_id'), $file, $this->input->post('external'));
                    die;
                    break;
                case 'new_task_comment':
                    $comment_data = $this->input->post();
                    $comment_data['content'] = nl2br($comment_data['content']);
                    $comment_id      = $this->tasks_model->add_task_comment($comment_data);
                    $url = site_url('clients/project/' . $id . '?group=project_tasks&taskid=' . $comment_data['taskid']);

                    if ($comment_id) {
                        set_alert('success', _l('task_comment_added'));
                        $url .= '#comment_'.$comment_id;
                    }

                    redirect($url);
                    break;
                default:
                    redirect(site_url('clients/project/' . $id));
                    break;
            }
        }
        if (!$this->input->get('group')) {
            $group = 'project_overview';
        } else {
            $group = $this->input->get('group');
        }
        if ($group != 'edit_task') {
            if ($group == 'project_overview') {
                $data['project_status'] =  get_project_status_by_id($data['project']->status);
                $percent          = $this->projects_model->calc_progress($id);
                @$data['percent'] = $percent / 100;
                $this->load->helper('date');
                $data['project_total_days']        = round((human_to_unix($data['project']->deadline . ' 00:00') - human_to_unix($data['project']->start_date . ' 00:00')) / 3600 / 24);
                $data['project_days_left']         = $data['project_total_days'];
                $data['project_time_left_percent'] = 100;
                if ($data['project']->deadline) {
                    if (human_to_unix($data['project']->start_date . ' 00:00') < time() && human_to_unix($data['project']->deadline . ' 00:00') > time()) {
                        $data['project_days_left']         = round((human_to_unix($data['project']->deadline . ' 00:00') - time()) / 3600 / 24);
                        $data['project_time_left_percent'] = $data['project_days_left'] / $data['project_total_days'] * 100;
                    }
                    if (human_to_unix($data['project']->deadline . ' 00:00') < time()) {
                        $data['project_days_left']         = 0;
                        $data['project_time_left_percent'] = 0;
                    }
                }
                $total_tasks = total_rows('tblstafftasks', array(
            'rel_id' => $id,
            'rel_type' => 'project',
            'visible_to_client' => 1,
        ));

                $data['tasks_not_completed'] = total_rows('tblstafftasks', array(
            'status !=' => 5,
            'rel_id' => $id,
            'rel_type' => 'project',
            'visible_to_client' => 1,
        ));

                $data['tasks_completed'] = total_rows('tblstafftasks', array(
            'status' => 5,
            'rel_id' => $id,
            'rel_type' => 'project',
            'visible_to_client' => 1,
        ));

                $data['total_tasks']                  = $total_tasks;
                $data['tasks_not_completed_progress'] = ($total_tasks > 0 ? number_format(($data['tasks_completed'] * 100) / $total_tasks, 2) : 0);
            } elseif ($group == 'new_task') {
                if ($project->settings->create_tasks == 0) {
                    redirect(site_url('clients/project/'.$project->id));
                }
                $data['milestones']  = $this->projects_model->get_milestones($id);
            } elseif ($group == 'project_gantt') {
                $data['gantt_data']  = $this->projects_model->get_gantt_data($id);
            } elseif ($group == 'project_discussions') {
                if ($this->input->get('discussion_id')) {
                    $data['discussion_user_profile_image_url'] = contact_profile_image_url(get_contact_user_id());
                    $data['discussion']                        = $this->projects_model->get_discussion($this->input->get('discussion_id'), $id);
                    $data['current_user_is_admin']             = false;
                }
                $data['discussions'] = $this->projects_model->get_discussions($id);
            } elseif ($group == 'project_files') {
                $data['files']       = $this->projects_model->get_files($id);
            } elseif ($group == 'project_tasks') {
                $data['tasks_statuses'] = $this->tasks_model->get_statuses();
                $data['project_tasks'] = $this->projects_model->get_tasks($id);
            } elseif ($group == 'project_activity') {
                $data['activity']   = $this->projects_model->get_activity($id);
            } elseif ($group == 'project_milestones') {
                $data['milestones']  = $this->projects_model->get_milestones($id);
            } elseif ($group == 'project_invoices') {
                $data['invoices'] = array();
                if (has_contact_permission('invoices')) {
                    $data['invoices'] = $this->invoices_model->get('', array(
                            'clientid' => get_client_user_id(),
                            'project_id' => $id,
                        ));
                }
            } elseif ($group == 'project_tickets') {
                $data['tickets'] = array();
                if (has_contact_permission('support')) {
                    $where_tickets = array(
                        'tbltickets.userid' => get_client_user_id(),
                        'project_id' => $id,
                    );

                    if (!is_primary_contact() && get_option('only_show_contact_tickets') == 1) {
                        $where_tickets['tbltickets.contactid'] = get_contact_user_id();
                    }

                    $data['tickets'] = $this->tickets_model->get('', $where_tickets);
                }
            } elseif ($group == 'project_estimates') {
                $data['estimates'] = array();
                if (has_contact_permission('estimates')) {
                    $data['estimates'] = $this->estimates_model->get('', array(
                            'clientid' => get_client_user_id(),
                            'project_id' => $id,
                        ));
                }
            } elseif ($group == 'project_timesheets') {
                $data['timesheets'] = $this->projects_model->get_timesheets($id);
            }

            if ($this->input->get('taskid')) {
                $data['view_task'] = $this->tasks_model->get($this->input->get('taskid'), array(
                    'rel_id' => $project->id,
                    'rel_type' => 'project',
                ));

                $data['title'] = $data['view_task']->name;
            }
        } elseif ($group == 'edit_task') {
            $data['task'] = $this->tasks_model->get($this->input->get('taskid'), array(
                    'rel_id' => $project->id,
                    'rel_type' => 'project',
                    'addedfrom'=>get_contact_user_id(),
                    'is_added_from_contact'=>1,
                ));
        }

        $data['group'] = $group;
        $data['currency'] = $this->projects_model->get_currency($id);
        $data['members']     = $this->projects_model->get_project_members($id);

        $this->data            = $data;
        $this->view            = 'project';
        $this->layout();
    }

    public function files()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('clients/login'));
        }

        $files_where = 'visible_to_customer = 1 AND id IN (SELECT file_id FROM tblcustomerfiles_shares WHERE contact_id =' . get_contact_user_id() . ')';

        $files_where = do_action('customers_area_files_where', $files_where);

        $files = $this->clients_model->get_customer_files(get_client_user_id(), $files_where);

        $data['files'] = $files;
        $data['title'] = _l('customer_attachments');
        $this->data    = $data;
        $this->view    = 'files';
        $this->layout();
    }

    public function upload_files()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('clients/login'));
        }

        if ($this->input->post('external')) {
            $file                        = $this->input->post('files');
            $file[0]['staffid']          = 0;
            $file[0]['contact_id']       = get_contact_user_id();
            $file['visible_to_customer'] = 1;
            $this->misc_model->add_attachment_to_database(get_client_user_id(), 'customer', $file, $this->input->post('external'));
        } else {
            handle_client_attachments_upload(get_client_user_id(), true);
        }
    }

    public function delete_file($id, $type = '')
    {
        if (!is_client_logged_in()) {
            redirect(site_url('clients/login'));
        }

        if (get_option('allow_contact_to_delete_files') == 1) {
            if ($type == 'general') {
                $file = $this->misc_model->get_file($id);
                if ($file->contact_id == get_contact_user_id()) {
                    $this->clients_model->delete_attachment($id);
                    set_alert('success', _l('deleted', _l('file')));
                }
                redirect(site_url('clients/files'));
            } elseif ($type == 'project') {
                $this->load->model('projects_model');
                $file = $this->projects_model->get_file($id);
                if ($file->contact_id == get_contact_user_id()) {
                    $this->projects_model->remove_file($id);
                    set_alert('success', _l('deleted', _l('file')));
                }
                redirect(site_url('clients/project/' . $file->project_id . '?group=project_files'));
            } elseif ($type == 'task') {
                $file = $this->misc_model->get_file($id);
                if ($file->contact_id == get_contact_user_id()) {
                    $this->tasks_model->remove_task_attachment($id);
                    set_alert('success', _l('deleted', _l('file')));
                }
                redirect(site_url('clients/project/' . $this->input->get('project_id') . '?group=project_tasks&taskid=' . $file->rel_id));
            }
        }
        redirect(site_url());
    }

    public function remove_task_comment($id)
    {
        if (!is_client_logged_in()) {
            redirect(site_url('clients/login'));
        }
        echo json_encode(array(
            'success' => $this->tasks_model->remove_comment($id),
        ));
    }

    public function edit_comment()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('clients/login'));
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            $data['content'] = nl2br($data['content']);
            $success = $this->tasks_model->edit_comment($data);
            if ($success) {
                set_alert('success', _l('task_comment_updated'));
            }
            echo json_encode(array(
                'success' => $success,
            ));
        }
    }

    public function tickets($status = '')
    {
        if (!is_client_logged_in()) {
            redirect(site_url('clients/login'));
        }

        if (!has_contact_permission('support')) {
            set_alert('warning', _l('access_denied'));
            redirect(site_url());
        }

        $where = array(
            'tbltickets.userid' => get_client_user_id(),
        );

        if (!is_primary_contact() && get_option('only_show_contact_tickets') == 1) {
            $where['tbltickets.contactid'] = get_contact_user_id();
        }

        // By default only open tickets
        if (!is_numeric($status)) {
            $status = 1;
        }

        $where['status'] = $status;

        $data['list_status'] = $status;
        $data['bodyclass'] = 'tickets';
        $data['tickets']   = $this->tickets_model->get('', $where);
        $data['title']     = _l('clients_tickets_heading');
        $this->data        = $data;
        $this->view        = 'tickets';
        $this->layout();
    }

    public function change_ticket_status()
    {
        if (is_client_logged_in() && has_contact_permission('support')) {
            $post_data = $this->input->post();
            $response  = $this->tickets_model->change_ticket_status($post_data['ticket_id'], $post_data['status_id']);
            set_alert('alert-' . $response['alert'], $response['message']);
        }
    }

    public function viewproposal($id, $hash)
    {
        check_proposal_restrictions($id, $hash);
        $proposal = $this->proposals_model->get($id);
        if ($proposal->rel_type == 'customer' && !is_client_logged_in()) {
            load_client_language($proposal->rel_id);
        }
        $identity_confirmation_enabled = get_option('proposal_accept_identity_confirmation');
        if ($this->input->post()) {
            $action = $this->input->post('action');
            switch ($action) {
                case 'proposal_pdf':

                    $proposal_number = format_proposal_number($id);
                    $companyname     = get_option('invoice_company_name');
                    if ($companyname != '') {
                        $proposal_number .= '-' . mb_strtoupper(slug_it($companyname), 'UTF-8');
                    }

                    try {
                        $pdf = proposal_pdf($proposal);
                    } catch (Exception $e) {
                        echo $e->getMessage();
                        die;
                    }

                    $pdf->Output($proposal_number . '.pdf', 'D');
                    break;
                case 'proposal_comment':
                    // comment is blank
                    if (!$this->input->post('content')) {
                        redirect($this->uri->uri_string());
                    }
                    $data               = $this->input->post();
                    $data['proposalid'] = $id;
                    $this->proposals_model->add_comment($data, true);
                    redirect($this->uri->uri_string());
                    break;
                case 'accept_proposal':
                    $success = $this->proposals_model->mark_action_status(3, $id, true);
                    if ($success) {
                        $this->db->where('id', $id);
                        $this->db->update('tblproposals', get_acceptance_info_array());
                        redirect($this->uri->uri_string(), 'refresh');
                    }
                    break;
                case 'decline_proposal':
                    $success = $this->proposals_model->mark_action_status(2, $id, true);
                    if ($success) {
                        redirect($this->uri->uri_string(), 'refresh');
                    }
                    break;
            }
        }

        $number_word_lang_rel_id = 'unknown';
        if ($proposal->rel_type == 'customer') {
            $number_word_lang_rel_id = $proposal->rel_id;
        }
        $this->load->library('numberword', array(
            'clientid' => $number_word_lang_rel_id,
        ));

        $this->use_footer     = false;
        $this->use_navigation = false;
        $this->use_submenu    = false;

        $data['title']        = $proposal->subject;
        $data['proposal']     = do_action('proposal_html_pdf_data', $proposal);
        $data['bodyclass']    = 'proposal proposal-view';

        $data['identity_confirmation_enabled'] = $identity_confirmation_enabled;
        if ($identity_confirmation_enabled == '1') {
            $data['bodyclass'] .= ' identity-confirmation';
        }

        $data['comments']     = $this->proposals_model->get_comments($id);
        add_views_tracking('proposal', $id);
        do_action('proposal_html_viewed', $id);
        $data['exclude_reset_css'] = true;
        $data = do_action('proposal_customers_area_view_data', $data);
        $this->data                = $data;
        $this->view                = 'viewproposal';
        $this->layout();
    }

    public function proposals()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('clients/login'));
        }
        if (!has_contact_permission('proposals')) {
            set_alert('warning', _l('access_denied'));
            redirect(site_url());
        }

        $where  = 'rel_id =' . get_client_user_id() . ' AND rel_type ="customer"';

        if (get_option('exclude_proposal_from_client_area_with_draft_status') == 1) {
            $where .= ' AND status != 6';
        }

        $client = $this->clients_model->get(get_client_user_id());

        if (!is_null($client->leadid)) {
            $where .= ' OR rel_type="lead" AND rel_id=' . $client->leadid;
        }

        $data['proposals'] = $this->proposals_model->get('', $where);
        $data['title']     = _l('proposals');
        $this->data        = $data;
        $this->view        = 'proposals';
        $this->layout();
    }

    public function open_ticket()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('clients/login'));
        }
        if (!has_contact_permission('support')) {
            set_alert('warning', _l('access_denied'));
            redirect(site_url());
        }
        if ($this->input->post()) {
            $this->form_validation->set_rules('subject', _l('customer_ticket_subject'), 'required');
            $this->form_validation->set_rules('department', _l('clients_ticket_open_departments'), 'required');
            $this->form_validation->set_rules('priority', _l('priority'), 'required');
            $custom_fields = get_custom_fields('tickets', array(
                'show_on_client_portal' => 1,
                'required' => 1,
            ));
            foreach ($custom_fields as $field) {
                $field_name = 'custom_fields[' . $field['fieldto'] . '][' . $field['id'] . ']';
                if ($field['type'] == 'checkbox' || $field['type'] == 'multiselect') {
                    $field_name .= '[]';
                }
                $this->form_validation->set_rules($field_name, $field['name'], 'required');
            }
            if ($this->form_validation->run() !== false) {
                $id = $this->tickets_model->add($this->input->post());
                if ($id) {
                    set_alert('success', _l('new_ticket_added_successfully', $id));
                    redirect(site_url('clients/ticket/' . $id));
                }
            }
        }
        $data                   = array();
        $data['projects']       = $this->projects_model->get_projects_for_ticket(get_client_user_id());
        $data['title']          = _l('new_ticket');
        $this->data             = $data;
        $this->view             = 'open_ticket';
        $this->layout();
    }

    public function ticket($id)
    {
        if (!is_client_logged_in()) {
            redirect_after_login_to_current_url();
            redirect(site_url('clients/login'));
        }
        if (!has_contact_permission('support')) {
            set_alert('warning', _l('access_denied'));
            redirect(site_url());
        }
        if (!$id) {
            redirect(site_url());
        }
        if ($this->input->post()) {
            $this->form_validation->set_rules('message', _l('ticket_reply'), 'required');
            if ($this->form_validation->run() !== false) {
                $replyid = $this->tickets_model->add_reply($this->input->post(), $id);
                if ($replyid) {
                    set_alert('success', _l('replied_to_ticket_successfully', $id));
                    redirect(site_url('clients/ticket/' . $id));
                }
            }
        }
        $data['ticket'] = $this->tickets_model->get_ticket_by_id($id, get_client_user_id());
        if ($data['ticket']->userid != get_client_user_id()) {
            redirect(site_url());
        }
        $data['ticket_replies'] = $this->tickets_model->get_ticket_replies($id);
        $data['title']          = $data['ticket']->subject;
        $this->data             = $data;
        $this->view             = 'single_ticket';
        $this->layout();
    }

    public function contracts()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('clients/login'));
        }
        if (!has_contact_permission('contracts')) {
            set_alert('warning', _l('access_denied'));
            redirect(site_url());
        }
        $this->load->model('contracts_model');
        $data['contracts'] = $this->contracts_model->get('', array(
            'client' => get_client_user_id(),
            'not_visible_to_client' => 0,
            'trash' => 0,
        ));

        $data['contracts_by_type_chart'] = json_encode($this->contracts_model->get_contracts_types_chart_data());
        $data['title']                   = _l('clients_contracts');
        $this->data                      = $data;
        $this->view                      = 'contracts';
        $this->layout();
    }

    public function contract_pdf($id)
    {
        if (!is_client_logged_in()) {
            redirect(site_url('clients/login'));
        }

        if (!has_contact_permission('contracts')) {
            set_alert('warning', _l('access_denied'));
            redirect(site_url());
        }

        $this->load->model('contracts_model');
        $contract = $this->contracts_model->get($id, array(
            'client' => get_client_user_id(),
            'not_visible_to_client' => 0,
            'trash' => 0,
        ));

        try {
            $pdf      = contract_pdf($contract);
        } catch (Exception $e) {
            echo $e->getMessage();
            die;
        }

        $pdf->Output(slug_it($contract->subject) . '.pdf', 'D');
    }

    public function invoices($status = false)
    {
        if (!is_client_logged_in()) {
            redirect(site_url('clients/login'));
        }
        if (!has_contact_permission('invoices')) {
            set_alert('warning', _l('access_denied'));
            redirect(site_url());
        }
        $where = array(
            'clientid' => get_client_user_id(),
        );
        if (is_numeric($status)) {
            $where['status'] = $status;
        }

        if (isset($where['status'])) {
            if ($where['status'] == 6 && get_option('exclude_invoice_from_client_area_with_draft_status') == 1) {
                unset($where['status']);
                $where['status !='] = 6;
            }
        } else {
            if (get_option('exclude_invoice_from_client_area_with_draft_status') == 1) {
                $where['status !='] = 6;
            }
        }

        $data['invoices'] = $this->invoices_model->get('', $where);
        $data['title']    = _l('clients_my_invoices');
        $this->data       = $data;
        $this->view       = 'invoices';
        $this->layout();
    }

    public function viewinvoice($id = '', $hash = '')
    {
        check_invoice_restrictions($id, $hash);
        $invoice = $this->invoices_model->get($id);

        $invoice = do_action('before_client_view_invoice', $invoice);

        if (!is_client_logged_in()) {
            load_client_language($invoice->clientid);
        }
        // Handle Invoice PDF generator
        if ($this->input->post('invoicepdf')) {
            try {
                $pdf            = invoice_pdf($invoice);
            } catch (Exception $e) {
                echo $e->getMessage();
                die;
            }

            $invoice_number = format_invoice_number($invoice->id);
            $companyname    = get_option('invoice_company_name');
            if ($companyname != '') {
                $invoice_number .= '-' . mb_strtoupper(slug_it($companyname), 'UTF-8');
            }
            $pdf->Output(mb_strtoupper(slug_it($invoice_number), 'UTF-8') . '.pdf', 'D');
            die();
        }
        // Handle $_POST payment
        if ($this->input->post('make_payment')) {
            $this->load->model('payments_model');
            if (!$this->input->post('paymentmode')) {
                set_alert('warning', _l('invoice_html_payment_modes_not_selected'));
                redirect(site_url('viewinvoice/' . $id . '/' . $hash));
            } elseif ((!$this->input->post('amount') || $this->input->post('amount') == 0) && get_option('allow_payment_amount_to_be_modified') == 1) {
                set_alert('warning', _l('invoice_html_amount_blank'));
                redirect(site_url('viewinvoice/' . $id . '/' . $hash));
            }
            $this->payments_model->process_payment($this->input->post(), $id);
        }
        if ($this->input->post('paymentpdf')) {
            $id                    = $this->input->post('paymentpdf');
            $payment               = $this->payments_model->get($id);
            $payment->invoice_data = $this->invoices_model->get($payment->invoiceid);
            $paymentpdf            = payment_pdf($payment);
            $paymentpdf->Output(mb_strtoupper(slug_it(_l('payment') . '-' . $payment->paymentid), 'UTF-8') . '.pdf', 'D');
            die;
        }
        $this->load->library('numberword', array(
            'clientid' => $invoice->clientid,
        ));
        $this->load->model('payment_modes_model');
        $this->load->model('payments_model');
        $data['payments']      = $this->payments_model->get_invoice_payments($id);
        $data['payment_modes'] = $this->payment_modes_model->get();
        $data['title']         = format_invoice_number($invoice->id);
        $this->use_navigation  = false;
        $this->use_submenu     = false;
        $data['hash']          = $hash;
        $data['invoice']       = do_action('invoice_html_pdf_data', $invoice);
        $data['bodyclass']     = 'viewinvoice';
        $this->data            = $data;
        $this->view            = 'invoicehtml';
        add_views_tracking('invoice', $id);
        do_action('invoice_html_viewed', $id);
        $this->layout();
    }

    public function statement()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('clients/login'));
        }
        if (!has_contact_permission('invoices')) {
            set_alert('warning', _l('access_denied'));
            redirect(site_url());
        }

        $data = array();
        // Default to this month
        $from = _d(date('Y-m-01'));
        $to = _d(date('Y-m-t'));

        if ($this->input->get('from') && $this->input->get('to')) {
            $from = $this->input->get('from');
            $to = $this->input->get('to');
        }

        $data['statement'] = $this->clients_model->get_statement(get_client_user_id(), to_sql_date($from), to_sql_date($to));

        $data['from'] = $from;
        $data['to'] = $to;

        $data['period_today'] = json_encode(
                     array(
                     _d(date('Y-m-d')),
                     _d(date('Y-m-d')),
                     )
        );
        $data['period_this_week'] = json_encode(
                     array(
                     _d(date('Y-m-d', strtotime('monday this week'))),
                     _d(date('Y-m-d', strtotime('sunday this week'))),
                     )
        );
        $data['period_this_month'] = json_encode(
                     array(
                     _d(date('Y-m-01')),
                     _d(date('Y-m-t')),
                     )
        );

        $data['period_last_month'] = json_encode(
                     array(
                     _d(date('Y-m-01', strtotime("-1 MONTH"))),
                     _d(date('Y-m-t', strtotime('-1 MONTH'))),
                     )
        );

        $data['period_this_year'] = json_encode(
                     array(
                     _d(date('Y-m-d', strtotime(date('Y-01-01')))),
                     _d(date('Y-m-d', strtotime(date('Y-12-31')))),
                     )
        );
        $data['period_last_year'] = json_encode(
                     array(
                     _d(date('Y-m-d', strtotime(date(date('Y', strtotime('last year')).'-01-01')))),
                     _d(date('Y-m-d', strtotime(date(date('Y', strtotime('last year')). '-12-31')))),
                     )
        );

        $data['period_selected'] = json_encode(array($from, $to));

        $data['custom_period'] = ($this->input->get('custom_period') ? true : false);

        $data['title']         = _l('customer_statement');
        $this->data            = $data;
        $this->view            = 'statement';
        $this->layout();
    }

    public function statement_pdf()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('clients/login'));
        }
        if (!has_contact_permission('invoices')) {
            set_alert('warning', _l('access_denied'));
            redirect(site_url());
        }

        $from = $this->input->get('from');
        $to = $this->input->get('to');

        $data['statement'] = $this->clients_model->get_statement(get_client_user_id(), to_sql_date($from), to_sql_date($to));

        try {
            $pdf            = statement_pdf($data['statement']);
        } catch (Exception $e) {
            echo $e->getMessage();
            die;
        }

        $type           = 'D';
        if ($this->input->get('print')) {
            $type = 'I';
        }

        $pdf_name = slug_it(_l('customer_statement') . '_' .get_option('companyname'));
        $pdf->Output($pdf_name . '.pdf', $type);
    }

    public function viewestimate($id, $hash)
    {
        check_estimate_restrictions($id, $hash);
        $estimate = $this->estimates_model->get($id);
        if (!is_client_logged_in()) {
            load_client_language($estimate->clientid);
        }

        $identity_confirmation_enabled = get_option('estimate_accept_identity_confirmation');

        if ($this->input->post('estimate_action')) {
            $action = $this->input->post('estimate_action');
            // Only decline and accept allowed
            if ($action == 4 || $action == 3) {
                $success = $this->estimates_model->mark_action_status($action, $id, true);

                $redURL = $this->uri->uri_string();
                $accepted = false;
                if (is_array($success) && $success['invoiced'] == true) {
                    $accepted = true;
                    $invoice = $this->invoices_model->get($success['invoiceid']);
                    set_alert('success', _l('clients_estimate_invoiced_successfully'));
                    $redURL = site_url('viewinvoice/' . $invoice->id . '/' . $invoice->hash);
                } elseif (is_array($success) && $success['invoiced'] == false || $success === true) {
                    if ($action == 4) {
                        $accepted = true;
                        set_alert('success', _l('clients_estimate_accepted_not_invoiced'));
                    } else {
                        set_alert('success', _l('clients_estimate_declined'));
                    }
                } else {
                    set_alert('warning', _l('clients_estimate_failed_action'));
                }
                if ($action == 4 && $accepted = true) {
                    $this->db->where('id', $id);
                    $this->db->update('tblestimates', get_acceptance_info_array());
                }
            }
            redirect($redURL);
        }
        // Handle Estimate PDF generator
        if ($this->input->post('estimatepdf')) {
            try {
                $pdf             = estimate_pdf($estimate);
            } catch (Exception $e) {
                echo $e->getMessage();
                die;
            }

            $estimate_number = format_estimate_number($estimate->id);
            $companyname     = get_option('invoice_company_name');
            if ($companyname != '') {
                $estimate_number .= '-' . mb_strtoupper(slug_it($companyname), 'UTF-8');
            }
            $pdf->Output(mb_strtoupper(slug_it($estimate_number), 'UTF-8') . '.pdf', 'D');
            die();
        }
        $this->load->library('numberword', array(
            'clientid' => $estimate->clientid,
        ));

        $data['title']        = format_estimate_number($estimate->id);
        $this->use_navigation = false;
        $this->use_submenu    = false;
        $data['hash']         = $hash;
        $data['can_be_accepted'] = false;
        $data['estimate']     = do_action('estimate_html_pdf_data', $estimate);
        $data['bodyclass']    = 'viewestimate';
        $data['identity_confirmation_enabled'] = $identity_confirmation_enabled;
        if ($identity_confirmation_enabled == '1') {
            $data['bodyclass'] .= ' identity-confirmation';
        }
        $this->data           = $data;
        $this->view           = 'estimatehtml';
        add_views_tracking('estimate', $id);
        do_action('estimate_html_viewed', $id);
        $this->layout();
    }

    public function estimates($status = '')
    {
        if (!is_client_logged_in()) {
            redirect(site_url('clients/login'));
        }
        if (!has_contact_permission('estimates')) {
            set_alert('warning', _l('access_denied'));
            redirect(site_url());
        }
        $where = array(
            'clientid' => get_client_user_id(),
        );
        if (is_numeric($status)) {
            $where['status'] = $status;
        }
        if (isset($where['status'])) {
            if ($where['status'] == 1 && get_option('exclude_estimate_from_client_area_with_draft_status') == 1) {
                unset($where['status']);
                $where['status !='] = 1;
            }
        } else {
            if (get_option('exclude_estimate_from_client_area_with_draft_status') == 1) {
                $where['status !='] = 1;
            }
        }
        $data['estimates'] = $this->estimates_model->get('', $where);
        $data['title']     = _l('clients_my_estimates');
        $this->data        = $data;
        $this->view        = 'estimates';
        $this->layout();
    }

    public function survey($id, $hash)
    {
        if (!$hash || !$id) {
            die('No survey specified');
        }
        $this->load->model('surveys_model');
        $survey = $this->surveys_model->get($id);
        if (!$survey || ($survey->hash != $hash)) {
            show_404();
        }
        if ($survey->active == 0) {
            // Allow users with permission manage surveys to preview the survey even if is not active
            if (!has_permission('surveys', '', 'view')) {
                die('Survey not active');
            }
        }
        // Check if survey is only for logged in participants / staff / clients
        if ($survey->onlyforloggedin == 1) {
            if (!is_logged_in()) {
                die('This survey is only for logged in users');
            }
        }
        // Ip Restrict check
        if ($survey->iprestrict == 1) {
            $this->db->where('surveyid', $id);
            $this->db->where('ip', $this->input->ip_address());
            $total = $this->db->count_all_results('tblsurveyresultsets');
            if ($total > 0) {
                die('Already participated on this survey. Thanks');
            }
        }
        if ($this->input->post()) {
            $success = $this->surveys_model->add_survey_result($id, $this->input->post());
            if ($success) {
                $survey = $this->surveys_model->get($id);
                if ($survey->redirect_url !== '') {
                    redirect($survey->redirect_url);
                }
                set_alert('success', 'Thank you for participating in this survey. Your answers are very important to us.');
                $default_redirect = do_action('survey_default_redirect', site_url());
                redirect($default_redirect);
            }
        }
        $this->use_navigation = false;
        $this->use_submenu    = false;
        $data['survey']       = $survey;
        $data['title']        = $data['survey']->subject;
        $this->data           = $data;
        $this->view           = 'survey_view';
        $this->layout();
    }

    public function company()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('clients/login'));
        }

        if ($this->input->post()) {
            if (get_option('company_is_required') == 1) {
                $this->form_validation->set_rules('company', _l('clients_company'), 'required');
            }

            if (active_clients_theme() == 'perfex') {
                // Fix for custom fields checkboxes validation
                $this->form_validation->set_rules('company_form', '', 'required');
            }

            $custom_fields = get_custom_fields('customers', array(
                'show_on_client_portal' => 1,
                'required' => 1,
                'disalow_client_to_edit' => 0,
            ));

            foreach ($custom_fields as $field) {
                $field_name = 'custom_fields[' . $field['fieldto'] . '][' . $field['id'] . ']';
                if ($field['type'] == 'checkbox' || $field['type'] == 'multiselect') {
                    $field_name .= '[]';
                }
                $this->form_validation->set_rules($field_name, $field['name'], 'required');
            }
            if ($this->form_validation->run() !== false) {
                $data    = $this->input->post();

                if (isset($data['company_form'])) {
                    unset($data['company_form']);
                }

                $success = $this->clients_model->update_company_details($data, get_client_user_id());
                if ($success == true) {
                    set_alert('success', _l('clients_profile_updated'));
                }
                redirect(site_url('clients/company'));
            }
        }
        $data['title'] = _l('client_company_info');
        $this->data    = $data;
        $this->view    = 'company_profile';
        $this->layout();
    }

    public function profile()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('clients/login'));
        }
        if ($this->input->post('profile')) {
            $this->form_validation->set_rules('firstname', _l('client_firstname'), 'required');
            $this->form_validation->set_rules('lastname', _l('client_lastname'), 'required');
            $custom_fields = get_custom_fields('contacts', array(
                'show_on_client_portal' => 1,
                'required' => 1,
                'disalow_client_to_edit' => 0,
            ));
            foreach ($custom_fields as $field) {
                $field_name = 'custom_fields[' . $field['fieldto'] . '][' . $field['id'] . ']';
                if ($field['type'] == 'checkbox' || $field['type'] == 'multiselect') {
                    $field_name .= '[]';
                }
                $this->form_validation->set_rules($field_name, $field['name'], 'required');
            }
            if ($this->form_validation->run() !== false) {
                handle_contact_profile_image_upload();
                $data = $this->input->post();
                // Unset the form indicator so we wont send it to the model
                unset($data['profile']);

                $contact = $this->clients_model->get_contact(get_contact_user_id());

                if (has_contact_permission('invoices')) {
                    $data['invoice_emails'] = isset($data['invoice_emails']) ? 1 : 0;
                    $data['credit_note_emails'] = isset($data['credit_note_emails']) ? 1 : 0;
                } else {
                    $data['invoice_emails'] = $contact->invoice_emails;
                    $data['credit_note_emails'] = $contact->credit_note_emails;
                }

                if (has_contact_permission('estimates')) {
                    $data['estimate_emails'] = isset($data['estimate_emails']) ? 1 : 0;
                } else {
                    $data['estimate_emails'] = $contact->estimate_emails;
                }

                if (has_contact_permission('contracts')) {
                    $data['contract_emails'] = isset($data['contract_emails']) ? 1 : 0;
                } else {
                    $data['contract_emails'] = $contact->contract_emails;
                }

                if (has_contact_permission('projects')) {
                    $data['project_emails'] = isset($data['project_emails']) ? 1 : 0;
                    $data['task_emails'] = isset($data['task_emails']) ? 1 : 0;
                } else {
                    $data['project_emails'] = $contact->project_emails;
                    $data['task_emails'] = $contact->task_emails;
                }
                // For all cases
                if (isset($data['password'])) {
                    unset($data['password']);
                }
                $success = $this->clients_model->update_contact($data, get_contact_user_id(), true);

                if ($success == true) {
                    set_alert('success', _l('clients_profile_updated'));
                }
                redirect(site_url('clients/profile'));
            }
        } elseif ($this->input->post('change_password')) {
            $this->form_validation->set_rules('oldpassword', _l('clients_edit_profile_old_password'), 'required');
            $this->form_validation->set_rules('newpassword', _l('clients_edit_profile_new_password'), 'required');
            $this->form_validation->set_rules('newpasswordr', _l('clients_edit_profile_new_password_repeat'), 'required|matches[newpassword]');
            if ($this->form_validation->run() !== false) {
                $success = $this->clients_model->change_contact_password($this->input->post(null, false));
                if (is_array($success) && isset($success['old_password_not_match'])) {
                    set_alert('danger', _l('client_old_password_incorrect'));
                } elseif ($success == true) {
                    set_alert('success', _l('client_password_changed'));
                }
                redirect(site_url('clients/profile'));
            }
        }
        $data['title'] = _l('clients_profile_heading');
        $this->data    = $data;
        $this->view    = 'profile';
        $this->layout();
    }

    public function remove_profile_image()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('clients/login'));
        }
        do_action('before_remove_contact_profile_image');
        if (file_exists(get_upload_path_by_type('contact_profile_images') . get_contact_user_id())) {
            delete_dir(get_upload_path_by_type('contact_profile_images') . get_contact_user_id());
        }
        $this->db->where('id', get_contact_user_id());
        $this->db->update('tblcontacts', array(
            'profile_image' => null,
        ));
        if ($this->db->affected_rows() > 0) {
            redirect(site_url('clients/profile'));
        }
    }

    public function register()
    {
        if (get_option('allow_registration') != 1 || is_client_logged_in()) {
            redirect(site_url());
        }
        if (get_option('company_is_required') == 1) {
            $this->form_validation->set_rules('company', _l('client_company'), 'required');
        }
        $this->form_validation->set_rules('firstname', _l('client_firstname'), 'required');
        $this->form_validation->set_rules('lastname', _l('client_lastname'), 'required');
        $this->form_validation->set_rules('email', _l('client_email'), 'trim|required|is_unique[tblcontacts.email]|valid_email');
        $this->form_validation->set_rules('password', _l('clients_register_password'), 'required');
        $this->form_validation->set_rules('passwordr', _l('clients_register_password_repeat'), 'required|matches[password]');

        if (get_option('use_recaptcha_customers_area') == 1 && get_option('recaptcha_secret_key') != '' && get_option('recaptcha_site_key') != '') {
            $this->form_validation->set_rules('g-recaptcha-response', 'Captcha', 'callback_recaptcha');
        }

        $custom_fields = get_custom_fields('customers', array(
            'show_on_client_portal' => 1,
            'required' => 1,
        ));

        $custom_fields_contacts = get_custom_fields('contacts', array(
            'show_on_client_portal' => 1,
            'required' => 1,
        ));

        foreach ($custom_fields as $field) {
            $field_name = 'custom_fields[' . $field['fieldto'] . '][' . $field['id'] . ']';
            if ($field['type'] == 'checkbox' || $field['type'] == 'multiselect') {
                $field_name .= '[]';
            }
            $this->form_validation->set_rules($field_name, $field['name'], 'required');
        }
        foreach ($custom_fields_contacts as $field) {
            $field_name = 'custom_fields[' . $field['fieldto'] . '][' . $field['id'] . ']';
            if ($field['type'] == 'checkbox' || $field['type'] == 'multiselect') {
                $field_name .= '[]';
            }
            $this->form_validation->set_rules($field_name, $field['name'], 'required');
        }
        if ($this->input->post()) {
            if ($this->form_validation->run() !== false) {
                $data = $this->input->post();
                // Unset recaptchafield
                if (isset($data['g-recaptcha-response'])) {
                    unset($data['g-recaptcha-response']);
                }
                $clientid = $this->clients_model->add($data, true);
                if ($clientid) {
                    do_action('after_client_register', $clientid);
                    $this->load->model('authentication_model');
                    $logged_in = $this->authentication_model->login($this->input->post('email'), $this->input->post('password', false), false, false);

                    $redUrl = site_url();

                    if ($logged_in) {
                        do_action('after_client_register_logged_in', $clientid);
                        set_alert('success', _l('clients_successfully_registered'));
                    } else {
                        set_alert('warning', _l('clients_account_created_but_not_logged_in'));
                        $redUrl = site_url('clients/login');
                    }

                    $admins = $this->db->select('email')->
                    where('admin', 1)
                    ->get('tblstaff')->result_array();

                    $this->load->model('emails_model');
                    foreach ($admins as $admin) {
                        $merge_fields = get_client_contact_merge_fields($clientid, get_primary_contact_user_id($clientid));
                        $this->emails_model->send_email_template('new-client-registered-to-admin', $admin['email'], $merge_fields);
                    }

                    redirect($redUrl);
                }
            }
        }

        $data['title'] = _l('clients_register_heading');
        $data['bodyclass'] = 'register';
        $this->data    = $data;
        $this->view    = 'register';
        $this->layout();
    }

    public function forgot_password()
    {
        if (is_client_logged_in()) {
            redirect(site_url());
        }

        $this->form_validation->set_rules('email', _l('customer_forgot_password_email'), 'trim|required|valid_email|callback_contact_email_exists');

        if ($this->input->post()) {
            if ($this->form_validation->run() !== false) {
                $this->load->model('Authentication_model');
                $success = $this->Authentication_model->forgot_password($this->input->post('email'));
                if (is_array($success) && isset($success['memberinactive'])) {
                    set_alert('danger', _l('inactive_account'));
                } elseif ($success == true) {
                    set_alert('success', _l('check_email_for_resetting_password'));
                } else {
                    set_alert('danger', _l('error_setting_new_password_key'));
                }
                redirect(site_url('clients/forgot_password'));
            }
        }
        $data['title'] = _l('customer_forgot_password');
        $this->data    = $data;
        $this->view    = 'forgot_password';

        $this->layout();
    }

    public function reset_password($staff, $userid, $new_pass_key)
    {
        $this->load->model('Authentication_model');
        if (!$this->Authentication_model->can_reset_password($staff, $userid, $new_pass_key)) {
            set_alert('danger', _l('password_reset_key_expired'));
            redirect(site_url('clients/login'));
        }

        $this->form_validation->set_rules('password', _l('customer_reset_password'), 'required');
        $this->form_validation->set_rules('passwordr', _l('customer_reset_password_repeat'), 'required|matches[password]');
        if ($this->input->post()) {
            if ($this->form_validation->run() !== false) {
                do_action('before_user_reset_password', array(
                    'staff' => $staff,
                    'userid' => $userid,
                ));
                $success = $this->Authentication_model->reset_password(0, $userid, $new_pass_key, $this->input->post('passwordr', false));
                if (is_array($success) && $success['expired'] == true) {
                    set_alert('danger', _l('password_reset_key_expired'));
                } elseif ($success == true) {
                    do_action('after_user_reset_password', array(
                        'staff' => $staff,
                        'userid' => $userid,
                    ));
                    set_alert('success', _l('password_reset_message'));
                } else {
                    set_alert('danger', _l('password_reset_message_fail'));
                }
                redirect(site_url('clients/login'));
            }
        }
        $data['title'] = _l('admin_auth_reset_password_heading');
        $this->data = $data;
        $this->view = 'reset_password';
        $this->layout();
    }

    public function dismiss_announcement($id)
    {
        if (!is_client_logged_in()) {
            redirect(site_url('clients/login'));
        }
        $this->misc_model->dismiss_announcement($id, false);
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function knowledge_base($slug = '')
    {
        if ((get_option('use_knowledge_base') == 1 && !is_client_logged_in() && get_option('knowledge_base_without_registration') == 1) || (get_option('use_knowledge_base') == 1 && is_client_logged_in()) || is_staff_logged_in()) {
            if (is_staff_logged_in() && get_option('use_knowledge_base') == 0) {
                set_alert('warning', 'Knowledge base is disabled, navigate to Setup->Settings->Customers and set Use Knowledge Base to YES.');
            }

            $data     = array();
            $where_kb = array();
            if ($this->input->get('groupid')) {
                $where_kb = 'articlegroup =' . $this->input->get('groupid');
            } elseif ($this->input->get('kb_q')) {
                $where_kb = '(subject LIKE "%' . $this->input->get('kb_q') . '%" OR description LIKE "%' . $this->input->get('kb_q') . '%")';
            }

            $data['groups']                = get_all_knowledge_base_articles_grouped(true, $where_kb);
            $data['knowledge_base_search'] = true;
            if ($slug == '' || $this->input->get('groupid')) {
                $data['title'] = _l('clients_knowledge_base');
                $this->view    = 'knowledge_base';
            } else {
                $data['article'] = $this->knowledge_base_model->get(false, $slug);
                if ($data['article']) {
                    $data['related_articles'] = $this->knowledge_base_model->get_related_articles($data['article']->articleid);
                    add_views_tracking('kb_article', $data['article']->articleid);
                    if ($data['article']->active_article == 0) {
                        redirect(site_url('knowledge_base'));
                    }
                    $data['title'] = $data['article']->subject;

                    $this->view = 'knowledge_base_article';
                } else {
                    show_404();
                }
            }
            $this->data = $data;
            $this->layout();
        } else {
            show_404();
        }
    }

    public function add_kb_answer()
    {
        // This is for did you find this answer useful
        if (($this->input->post() && $this->input->is_ajax_request())) {
            echo json_encode($this->knowledge_base_model->add_article_answer($this->input->post()));
            die();
        }
    }

    public function login()
    {
        if (is_client_logged_in()) {
            redirect(site_url());
        }
        $this->form_validation->set_rules('password', _l('clients_login_password'), 'required');
        $this->form_validation->set_rules('email', _l('clients_login_email'), 'trim|required|valid_email');
        if (get_option('use_recaptcha_customers_area') == 1 && get_option('recaptcha_secret_key') != '' && get_option('recaptcha_site_key') != '') {
            $this->form_validation->set_rules('g-recaptcha-response', 'Captcha', 'callback_recaptcha');
        }
        if ($this->form_validation->run() !== false) {
            $this->load->model('Authentication_model');
            $success = $this->Authentication_model->login($this->input->post('email'), $this->input->post('password', false), $this->input->post('remember'), false);
            if (is_array($success) && isset($success['memberinactive'])) {
                set_alert('danger', _l('inactive_account'));
                redirect(site_url('clients/login'));
            } elseif ($success == false) {
                set_alert('danger', _l('client_invalid_username_or_password'));
                redirect(site_url('clients/login'));
            }

            maybe_redirect_to_previous_url();

            do_action('after_contact_login');
            redirect(site_url());
        }
        if (get_option('allow_registration') == 1) {
            $data['title'] = _l('clients_login_heading_register');
        } else {
            $data['title'] = _l('clients_login_heading_no_register');
        }
        $data['bodyclass'] = 'customers_login';

        $this->data        = $data;
        $this->view        = 'login';
        $this->layout();
    }

    public function logout()
    {
        $this->load->model('authentication_model');
        $this->authentication_model->logout(false);
        do_action('after_client_logout');
        redirect(site_url('clients/login'));
    }

    public function contact_email_exists($email = '')
    {
        if ($email == '') {
            $email = $this->input->post('email');
        }

        $this->db->where('email', $email);
        $total_rows = $this->db->count_all_results('tblcontacts');
        if ($this->input->post() && $this->input->is_ajax_request()) {
            if ($total_rows > 0) {
                echo json_encode(false);
            } else {
                echo json_encode(true);
            }
            die();
        } elseif ($this->input->post()) {
            if ($total_rows == 0) {
                $this->form_validation->set_message('contact_email_exists', _l('auth_reset_pass_email_not_found'));

                return false;
            }

            return true;
        }
    }

    public function change_language($lang = '')
    {
        if (!is_client_logged_in() || !is_primary_contact()) {
            redirect(site_url());
        }
        $lang = do_action('before_customer_change_language', $lang);
        $this->db->where('userid', get_client_user_id());
        $this->db->update('tblclients', array('default_language'=>$lang));
        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(site_url());
        }
    }

    /**
     * Client home chart
     * @return mixed
     */
    public function client_home_chart()
    {
        if (is_client_logged_in()) {
            $statuses        = array(
                1,
                2,
                4,
                3,
            );
            $months          = array();
            $months_original = array();
            for ($m = 1; $m <= 12; $m++) {
                array_push($months, _l(date('F', mktime(0, 0, 0, $m, 1))));
                array_push($months_original, date('F', mktime(0, 0, 0, $m, 1)));
            }
            $chart = array(
                'labels' => $months,
                'datasets' => array(),
            );
            foreach ($statuses as $status) {
                $this->db->select('total as amount, date');
                $this->db->from('tblinvoices');
                $this->db->where('clientid', get_client_user_id());
                $this->db->where('status', $status);
                $by_currency = $this->input->post('report_currency');
                if ($by_currency) {
                    $this->db->where('currency', $by_currency);
                }
                if ($this->input->post('year')) {
                    $this->db->where('YEAR(tblinvoices.date)', $this->input->post('year'));
                }
                $payments      = $this->db->get()->result_array();
                $data          = array();
                $data['temp']  = $months_original;
                $data['total'] = array();
                $i             = 0;
                foreach ($months_original as $month) {
                    $data['temp'][$i] = array();
                    foreach ($payments as $payment) {
                        $_month = date('F', strtotime($payment['date']));
                        if ($_month == $month) {
                            $data['temp'][$i][] = $payment['amount'];
                        }
                    }
                    $data['total'][] = array_sum($data['temp'][$i]);
                    $i++;
                }

                if ($status == 1) {
                    $borderColor = '#fc142b';
                } elseif ($status == 2) {
                    $borderColor = '#84c529';
                } elseif ($status == 4 || $status == 3) {
                    $borderColor = '#ff6f00';
                }

                $backgroundColor = 'rgba('.implode(',', hex2rgb($borderColor)).',0.3)';

                array_push($chart['datasets'], array(
                    'label' => format_invoice_status($status, '', false, true),
                    'backgroundColor' => $backgroundColor,
                    'borderColor' => $borderColor,
                    'borderWidth' => 1,
                    'tension' => false,
                    'data' => $data['total'],
                ));
            }
            echo json_encode($chart);
        }
    }

    public function recaptcha($str = '')
    {
        return do_recaptcha_validation($str);
    }
}
