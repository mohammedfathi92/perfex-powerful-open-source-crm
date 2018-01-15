<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Projects extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('projects_model');
        $this->load->model('currencies_model');
        $this->load->helper('date');
    }

    public function index()
    {
        close_setup_menu();
        $data['statuses'] = $this->projects_model->get_project_statuses();
        $data['title']    = _l('projects');
        $this->load->view('admin/projects/manage', $data);
    }

    public function table($clientid = '')
    {
        $this->perfex_base->get_table_data('projects', array(
            'clientid' => $clientid,
        ));
    }

    public function staff_projects()
    {
        $this->perfex_base->get_table_data('staff_projects');
    }

    public function expenses($id)
    {
        $this->load->model('expenses_model');
        $this->perfex_base->get_table_data('project_expenses', array(
            'project_id' => $id,
        ));
    }

    public function add_expense()
    {
        if ($this->input->post()) {
            $this->load->model('expenses_model');
            $id = $this->expenses_model->add($this->input->post());
            if ($id) {
                set_alert('success', _l('added_successfully', _l('expense')));
                echo json_encode(array(
                    'url' => admin_url('projects/view/' . $this->input->post('project_id') . '/?group=project_expenses'),
                    'expenseid' => $id,
                ));
                die;
            }
            echo json_encode(array(
                'url' => admin_url('projects/view/' . $this->input->post('project_id') . '/?group=project_expenses'),
            ));
            die;
        }
    }

    public function project($id = '')
    {
        if (!has_permission('projects', '', 'edit') && !has_permission('projects', '', 'create')) {
            access_denied('Projects');
        }
        if ($this->input->post()) {
            $data                = $this->input->post();
            $data['description'] = $this->input->post('description', false);
            if ($id == '') {
                if (!has_permission('projects', '', 'create')) {
                    access_denied('Projects');
                }
                $id = $this->projects_model->add($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('project')));
                    redirect(admin_url('projects/view/' . $id));
                }
            } else {
                if (!has_permission('projects', '', 'edit')) {
                    access_denied('Projects');
                }
                $success = $this->projects_model->update($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('project')));
                }
                redirect(admin_url('projects/view/' . $id));
            }
        }
        if ($id == '') {
            $title                            = _l('add_new', _l('project_lowercase'));
            $data['auto_select_billing_type'] = $this->projects_model->get_most_used_billing_type();
        } else {
            $data['project']         = $this->projects_model->get($id);
            $data['project']->settings->available_features = unserialize($data['project']->settings->available_features);

            $data['project_members'] = $this->projects_model->get_project_members($id);
            $title                   = _l('edit', _l('project_lowercase'));
        }

        if ($this->input->get('customer_id')) {
            $data['customer_id']        = $this->input->get('customer_id');
        }

        $data['last_project_settings'] = $this->projects_model->get_last_project_settings();

        if (count($data['last_project_settings'])) {
            $key = array_search('available_features', array_column($data['last_project_settings'], 'name'));
            $data['last_project_settings'][$key]['value'] = unserialize($data['last_project_settings'][$key]['value']);
        }

        $data['settings']              = $this->projects_model->get_settings();
        $data['statuses']              = $this->projects_model->get_project_statuses();
        $data['staff']                 = $this->staff_model->get('', 1);

        $data['title'] = $title;
        $this->load->view('admin/projects/project', $data);
    }

    public function view($id)
    {
        if ($this->projects_model->is_member($id) || has_permission('projects', '', 'view')) {
            close_setup_menu();
            $project = $this->projects_model->get($id);

            if (!$project) {
                blank_page(_l('project_not_found'));
            }

            $project->settings->available_features = unserialize($project->settings->available_features);
            $data['statuses'] = $this->projects_model->get_project_statuses();

            if (!$this->input->get('group')) {
                $view = 'project_overview';
            } else {
                $view = $this->input->get('group');
            }

            $this->load->model('payment_modes_model');
            $data['payment_modes'] = $this->payment_modes_model->get('', array(), true);

            $data['project']              = $project;
            $data['currency'] = $this->projects_model->get_currency($id);

            $data['project_total_logged_time'] = $this->projects_model->total_logged_time($id);

            $data['staff']         = $this->staff_model->get('', 1);
            $percent             = $this->projects_model->calc_progress($id);
            $data['bodyclass'] = '';
            if ($view == 'project_overview') {
                $data['members']       = $this->projects_model->get_project_members($id);
                $i = 0;
                foreach ($data['members'] as $member) {
                    $data['members'][$i]['total_logged_time'] = 0;
                    $member_timesheets = $this->tasks_model->get_unique_member_logged_task_ids($member['staff_id'], ' AND task_id IN (SELECT id FROM tblstafftasks WHERE rel_type="project" AND rel_id="'.$id.'")');

                    foreach ($member_timesheets as $member_task) {
                        $data['members'][$i]['total_logged_time'] += $this->tasks_model->calc_task_total_time($member_task->task_id, ' AND staff_id='.$member['staff_id']);
                    }

                    $i++;
                }

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

                $__total_where_tasks = 'rel_type = "project" AND rel_id=' . $id;
                if (!has_permission('tasks', '', 'view')) {
                    $__total_where_tasks .= ' AND tblstafftasks.id IN (SELECT taskid FROM tblstafftaskassignees WHERE staffid = ' . get_staff_user_id() . ')';

                    if (get_option('show_all_tasks_for_project_member') == 1) {
                        $__total_where_tasks .= ' AND (rel_type="project" AND rel_id IN (SELECT project_id FROM tblprojectmembers WHERE staff_id=' . get_staff_user_id() . '))';
                    }
                }
                $where = ($__total_where_tasks == '' ? '' : $__total_where_tasks . ' AND ') . 'status != 5';

                $data['tasks_not_completed'] = total_rows('tblstafftasks', $where);
                $total_tasks                 = total_rows('tblstafftasks', $__total_where_tasks);
                $data['total_tasks']         = $total_tasks;

                $where = ($__total_where_tasks == '' ? '' : $__total_where_tasks . ' AND ') . 'status = 5 AND rel_type="project" AND rel_id="' . $id . '"';

                $data['tasks_completed'] = total_rows('tblstafftasks', $where);

                $data['tasks_not_completed_progress'] = ($total_tasks > 0 ? number_format(($data['tasks_completed'] * 100) / $total_tasks, 2) : 0);

                @$percent_circle = $percent / 100;
                $data['percent_circle'] = $percent_circle;


                $data['project_overview_chart'] = $this->projects_model->get_project_overview_weekly_chart_data($id, ($this->input->get('overview_chart') ? $this->input->get('overview_chart'):'this_week'));
            } elseif ($view == 'project_invoices') {
                $this->load->model('invoices_model');

                $data['invoiceid']   = '';
                $data['status']      = '';
                $data['custom_view'] = '';

                $data['invoices_years']       = $this->invoices_model->get_invoices_years();
                $data['invoices_sale_agents'] = $this->invoices_model->get_sale_agents();
                $data['invoices_statuses']    = $this->invoices_model->get_statuses();
            } elseif ($view == 'project_gantt') {
                $gantt_type          = (!$this->input->get('gantt_type') ? 'milestones' : $this->input->get('gantt_type'));
                $taskStatus = (!$this->input->get('gantt_task_status') ? null : $this->input->get('gantt_task_status'));
                $data['gantt_data']  = $this->projects_model->get_gantt_data($id, $gantt_type, $taskStatus);
            } elseif ($view == 'project_milestones') {

                $data['bodyclass'] .= 'project-milestones ';
                $data['milestones_exclude_completed_tasks'] = $this->input->get('exclude_completed') && $this->input->get('exclude_completed') == 'yes' || !$this->input->get('exclude_completed');

                $data['total_milestones'] = total_rows('tblmilestones',array('project_id'=>$id));
                $data['milestones_found'] = $data['total_milestones'] > 0 || (!$data['total_milestones'] && total_rows('tblstafftasks',array('rel_id'=>$id,'rel_type'=>'project','milestone'=>0)) > 0);

            } elseif ($view == 'project_files') {
                $data['files']       = $this->projects_model->get_files($id);
            } elseif ($view == 'project_expenses') {
                $this->load->model('taxes_model');
                $this->load->model('expenses_model');
                $data['taxes']              = $this->taxes_model->get();
                $data['expense_categories'] = $this->expenses_model->get_category();
                $data['currencies']         = $this->currencies_model->get();
            } elseif ($view == 'project_activity') {
                $data['activity']             = $this->projects_model->get_activity($id);
            } elseif ($view == 'project_notes') {
                $data['staff_notes']          = $this->projects_model->get_staff_notes($id);
            } elseif ($view == 'project_estimates') {
                $this->load->model('estimates_model');
                $data['estimates_years']       = $this->estimates_model->get_estimates_years();
                $data['estimates_sale_agents'] = $this->estimates_model->get_sale_agents();
                $data['estimate_statuses']     = $this->estimates_model->get_statuses();
                $data['estimateid']            = '';
                $data['switch_pipeline']       = '';
            } elseif ($view == 'project_tickets') {
                $data['chosen_ticket_status'] = '';
                $this->load->model('tickets_model');
                $data['ticket_assignees'] = $this->tickets_model->get_tickets_assignes_disctinct();

                $this->load->model('departments_model');
                $data['staff_deparments_ids'] = $this->departments_model->get_staff_departments(get_staff_user_id(), true);
                $data['default_tickets_list_statuses'] = do_action('default_tickets_list_statuses', array(1, 2, 4));
            } elseif ($view == 'project_timesheets') {
                // Tasks are used in the timesheet dropdown
                // Completed tasks are excluded from this list because you can't add timesheet on completed task.
                $data['tasks'] = $this->projects_model->get_tasks($id, 'status != 5 AND billed=0');
                $data['timesheets_staff_ids'] = $this->projects_model->get_distinct_tasks_timesheets_staff($id);
            }

            // Discussions
            if ($this->input->get('discussion_id')) {
                $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
                $data['discussion']                        = $this->projects_model->get_discussion($this->input->get('discussion_id'), $id);
                $data['current_user_is_admin']             = is_admin();
            }

            $data['percent']              = $percent;

            $data['projects_assets']       = true;
            $data['circle_progress_asset'] = true;

            $other_projects = array();
            $other_projects_where = 'id !='.$id. ' and status = 2';

            if (!has_permission('projects', '', 'view')) {
                $other_projects_where .= ' AND tblprojects.id IN (SELECT project_id FROM tblprojectmembers WHERE staff_id=' . get_staff_user_id() .')';
            }

            $data['other_projects'] =  $this->projects_model->get('', $other_projects_where);
            $data['title']       = $data['project']->name;
            $data['bodyclass']  .= 'project invoices_total_manual estimates_total_manual';
            $data['project_status'] =  get_project_status_by_id($project->status);

            $hook_data                     = do_action('project_group_access_admin', array(
                'id' => $project->id,
                'view' => $view,
                'all_data' => $data,
            ));

            $data                          = $hook_data['all_data'];
            $view                          = $hook_data['view'];

            // Unable to load the requested file: admin/projects/project_tasks#.php - FIX
            if (strpos($view, '#') !== false) {
                $view = str_replace('#', '', $view);
            }

            $view = trim($view);
            $data['view'] = $view;
            $data['group_view']            = $this->load->view('admin/projects/' . $view, $data, true);

            $this->load->view('admin/projects/view', $data);
        } else {
            access_denied('Project View');
        }
    }

    public function mark_as()
    {
        $success = false;
        $message = '';
        if ($this->input->is_ajax_request()) {
            if (has_permission('projects', '', 'create') || has_permission('projects', '', 'edit')) {
                $status = get_project_status_by_id($this->input->post('status_id'));

                $message = _l('project_marked_as_failed', $status['name']);
                $success = $this->projects_model->mark_as($this->input->post());

                if ($success) {
                    $message = _l('project_marked_as_success', $status['name']);
                }
            }
        }
        echo json_encode(array(
            'success' => $success,
            'message' => $message,
        ));
    }

    public function file($id, $project_id)
    {
        $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
        $data['current_user_is_admin']             = is_admin();

        $data['file'] = $this->projects_model->get_file($id, $project_id);
        if (!$data['file']) {
            header("HTTP/1.0 404 Not Found");
            die;
        }
        $this->load->view('admin/projects/_file', $data);
    }

    public function update_file_data()
    {
        if ($this->input->post()) {
            $this->projects_model->update_file_data($this->input->post());
        }
    }

    public function add_external_file()
    {
        if ($this->input->post()) {
            $data = array();
            $data['project_id'] = $this->input->post('project_id');
            $data['files'] = $this->input->post('files');
            $data['external'] = $this->input->post('external');
            $data['visible_to_customer'] =($this->input->post('visible_to_customer') == 'true' ? 1 : 0);
            $data['staffid'] = get_staff_user_id();
            $this->projects_model->add_external_file($data);
        }
    }

    public function export_project_data($id)
    {
        if (has_permission('projects', '', 'create')) {
            $project = $this->projects_model->get($id);
            $this->load->library('pdf');
            $members                = $this->projects_model->get_project_members($id);
            $project->currency_data = $this->projects_model->get_currency($id);

            // Add <br /> tag and wrap over div element every image to prevent overlaping over text
            $project->description = preg_replace('/(<img[^>]+>(?:<\/img>)?)/i', '<br><br><div>$1</div><br><br>', $project->description);

            $data['project']    = $project;
            $data['milestones'] = $this->projects_model->get_milestones($id);
            $data['timesheets'] = $this->projects_model->get_timesheets($id);

            $data['tasks']             = $this->projects_model->get_tasks($id, array(), false);
            $data['total_logged_time'] = seconds_to_time_format($this->projects_model->total_logged_time($project->id));
            if ($project->deadline) {
                $data['total_days'] = round((human_to_unix($project->deadline . ' 00:00') - human_to_unix($project->start_date . ' 00:00')) / 3600 / 24);
            } else {
                $data['total_days'] = '/';
            }
            $data['total_members']  = count($members);
            $data['total_tickets']  = total_rows('tbltickets', array(
                'project_id' => $id,
            ));
            $data['total_invoices'] = total_rows('tblinvoices', array(
                'project_id' => $id,
            ));

            $this->load->model('invoices_model');

            $data['invoices_total_data'] = $this->invoices_model->get_invoices_total(array(
                'currency' => $project->currency_data->id,
                'project_id' => $project->id,
            ));

            $data['total_milestones']     = count($data['milestones']);
            $data['total_files_attached'] = total_rows('tblprojectfiles', array(
                'project_id' => $project->id,
            ));
            $data['total_discussion']     = total_rows('tblprojectdiscussions', array(
                'project_id' => $project->id,
            ));
            $data['members']              = $members;
            $this->load->view('admin/projects/export_data_pdf', $data);
        }
    }

    public function update_task_milestone()
    {
        if ($this->input->post()) {
            $this->projects_model->update_task_milestone($this->input->post());
        }
    }

    public function update_milestones_order(){
        if ($post_data = $this->input->post()) {
            $this->projects_model->update_milestones_order($post_data);
        }
    }

    public function pin_action($project_id)
    {
        $this->projects_model->pin_action($project_id);
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function add_edit_members($project_id)
    {
        if (has_permission('projects', '', 'edit') || has_permission('projects', '', 'create')) {
            $this->projects_model->add_edit_members($this->input->post(), $project_id);
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function discussions($project_id)
    {
        if ($this->projects_model->is_member($project_id) || has_permission('projects', '', 'view')) {
            if ($this->input->is_ajax_request()) {
                $this->perfex_base->get_table_data('project_discussions', array(
                    'project_id' => $project_id,
                ));
            }
        }
    }

    public function discussion($id = '')
    {
        if ($this->input->post()) {
            $message = '';
            $success = false;
            if (!$this->input->post('id')) {
                $id = $this->projects_model->add_discussion($this->input->post());
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('project_discussion'));
                }
                echo json_encode(array(
                    'success' => $success,
                    'message' => $message,
                ));
            } else {
                $data = $this->input->post();
                $id   = $data['id'];
                unset($data['id']);
                $success = $this->projects_model->edit_discussion($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', _l('project_discussion'));
                }
                echo json_encode(array(
                    'success' => $success,
                    'message' => $message,
                ));
            }
            die;
        }
    }

    public function get_discussion_comments($id, $type)
    {
        echo json_encode($this->projects_model->get_discussion_comments($id, $type));
    }

    public function add_discussion_comment($discussion_id, $type)
    {
        echo json_encode($this->projects_model->add_discussion_comment($this->input->post(), $discussion_id, $type));
    }

    public function update_discussion_comment()
    {
        echo json_encode($this->projects_model->update_discussion_comment($this->input->post()));
    }

    public function delete_discussion_comment($id)
    {
        echo json_encode($this->projects_model->delete_discussion_comment($id));
    }

    public function delete_discussion($id)
    {
        $success = false;
        if (has_permission('projects', '', 'delete')) {
            $success = $this->projects_model->delete_discussion($id);
        }
        $alert_type = 'warning';
        $message    = _l('project_discussion_failed_to_delete');
        if ($success) {
            $alert_type = 'success';
            $message    = _l('project_discussion_deleted');
        }
        echo json_encode(array(
            'alert_type' => $alert_type,
            'message' => $message,
        ));
    }

    public function change_milestone_color()
    {
        if ($this->input->post()) {
            $this->projects_model->update_milestone_color($this->input->post());
        }
    }

    public function upload_file($project_id)
    {
        handle_project_file_uploads($project_id);
    }

    public function change_file_visibility($id, $visible)
    {
        if ($this->input->is_ajax_request()) {
            $this->projects_model->change_file_visibility($id, $visible);
        }
    }

    public function change_activity_visibility($id, $visible)
    {
        if (has_permission('projects', '', 'create')) {
            if ($this->input->is_ajax_request()) {
                $this->projects_model->change_activity_visibility($id, $visible);
            }
        }
    }

    public function remove_file($project_id, $id)
    {
        $this->projects_model->remove_file($id);
        redirect(admin_url('projects/view/' . $project_id . '?group=project_files'));
    }

    public function milestones_kanban()
    {
        $data['milestones_exclude_completed_tasks'] = $this->input->get('exclude_completed_tasks') && $this->input->get('exclude_completed_tasks') == 'yes';

        $data['project_id'] = $this->input->get('project_id');
        $data['milestones'] = array();

        $data['milestones'][] = array(
          'name'=>_l('milestones_uncategorized'),
          'id'=>0,
          'total_logged_time'=>$this->projects_model->calc_milestone_logged_time($data['project_id'], 0),
          'color'=>null,
          );

        $_milestones = $this->projects_model->get_milestones($data['project_id']);

        foreach ($_milestones as $m) {
            $data['milestones'][] = $m;
        }

        echo $this->load->view('admin/projects/milestones_kan_ban', $data, true);
    }

    public function milestones_kanban_load_more()
    {
        $milestones_exclude_completed_tasks = $this->input->get('exclude_completed_tasks') && $this->input->get('exclude_completed_tasks') == 'yes';

        $status = $this->input->get('status');
        $page   = $this->input->get('page');
        $project_id = $this->input->get('project_id');
        $where = array();
        if ($milestones_exclude_completed_tasks) {
            $where['status !='] = 5;
        }
        $tasks = $this->projects_model->do_milestones_kanban_query($status, $project_id, $page, $where);
        foreach ($tasks as $task) {
            $this->load->view('admin/projects/_milestone_kanban_card', array('task'=>$task, 'milestone'=>$status));
        }
    }

    public function milestones($project_id)
    {
        if ($this->projects_model->is_member($project_id) || has_permission('projects', '', 'view')) {
            if ($this->input->is_ajax_request()) {
                $this->perfex_base->get_table_data('milestones', array(
                    'project_id' => $project_id,
                ));
            }
        }
    }

    public function milestone($id = '')
    {
        if ($this->input->post()) {
            $message = '';
            $success = false;
            if (!$this->input->post('id')) {
                $id = $this->projects_model->add_milestone($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('project_milestone')));
                }
            } else {
                $data = $this->input->post();
                $id   = $data['id'];
                unset($data['id']);
                $success = $this->projects_model->update_milestone($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('project_milestone')));
                }
            }
        }

        redirect(admin_url('projects/view/'.$this->input->post('project_id').'?group=project_milestones'));
    }

    public function delete_milestone($project_id, $id)
    {
        if (has_permission('projects', '', 'delete')) {
            if ($this->projects_model->delete_milestone($id)) {
                set_alert('deleted', 'project_milestone');
            }
        }
        redirect(admin_url('projects/view/' . $project_id . '?group=project_milestones'));
    }

    public function bulk_action_files()
    {
        do_action('before_do_bulk_action_for_project_files');
        $total_deleted = 0;
        $hasPermissionDelete = has_permission('projects', '', 'delete');
        // bulk action for projects currently only have delete button
        if ($this->input->post()) {
            $fVisibility = $this->input->post('visible_to_customer') == 'true' ? 1 : 0;
            $ids    = $this->input->post('ids');
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if ($hasPermissionDelete && $this->input->post('mass_delete') && $this->projects_model->remove_file($id)) {
                        $total_deleted++;
                    } else {
                        $this->projects_model->change_file_visibility($id, $fVisibility);
                    }
                }
            }
        }
        if ($this->input->post('mass_delete')) {
            set_alert('success', _l('total_files_deleted', $total_deleted));
        }
    }

    public function timesheets($project_id)
    {
        if ($this->projects_model->is_member($project_id) || has_permission('projects', '', 'view')) {
            if ($this->input->is_ajax_request()) {
                $this->perfex_base->get_table_data('timesheets', array(
                    'project_id' => $project_id,
                ));
            }
        }
    }

    public function timesheet()
    {
        if ($this->input->post()) {
            $message = '';
            $success = false;
            $success = $this->tasks_model->timesheet($this->input->post());
            if ($success === true) {
                $message = _l('added_successfully', _l('project_timesheet'));
            } elseif (is_array($success) && isset($success['end_time_smaller'])) {
                $message = _l('failed_to_add_project_timesheet_end_time_smaller');
            } else {
                $message = _l('project_timesheet_not_updated');
            }
            echo json_encode(array(
                'success' => $success,
                'message' => $message,
            ));
            die;
        }
    }

    public function timesheet_task_assignees($task_id, $project_id, $staff_id = 'undefined')
    {
        $assignees             = $this->tasks_model->get_task_assignees($task_id);
        $data                  = '';
        $has_permission_edit   = has_permission('projects', '', 'edit');
        $has_permission_create = has_permission('projects', '', 'edit');
        // The second condition if staff member edit their own timesheet
        if ($staff_id == 'undefined' || $staff_id != 'undefined' && (!$has_permission_edit || !$has_permission_create)) {
            $staff_id     = get_staff_user_id();
            $current_user = true;
        }
        foreach ($assignees as $staff) {
            $selected = '';
            // maybe is admin and not project member
            if ($staff['assigneeid'] == $staff_id && $this->projects_model->is_member($project_id, $staff_id)) {
                $selected = ' selected';
            }
            if ((!$has_permission_edit || !$has_permission_create) && isset($current_user)) {
                if ($staff['assigneeid'] != $staff_id) {
                    continue;
                }
            }
            $data .= '<option value="' . $staff['assigneeid'] . '"' . $selected . '>' . get_staff_full_name($staff['assigneeid']) . '</option>';
        }
        echo $data;
    }

    public function remove_team_member($project_id, $staff_id)
    {
        if (has_permission('projects', '', 'edit') || has_permission('projects', '', 'create')) {
            if ($this->projects_model->remove_team_member($project_id, $staff_id)) {
                set_alert('success', _l('project_member_removed'));
            }
        }
        redirect(admin_url('projects/view/' . $project_id));
    }

    public function save_note($project_id)
    {
        if ($this->input->post()) {
            $success = $this->projects_model->save_note($this->input->post(null, false), $project_id);
            if ($success) {
                set_alert('success', _l('updated_successfully', _l('project_note')));
            }
            redirect(admin_url('projects/view/' . $project_id . '?group=project_notes'));
        }
    }

    public function delete($project_id)
    {
        if (has_permission('projects', '', 'delete')) {
            $project = $this->projects_model->get($project_id);
            $success = $this->projects_model->delete($project_id);
            if ($success) {
                set_alert('success', _l('deleted', _l('project')));
                redirect(admin_url('projects'));
            } else {
                set_alert('warning', _l('problem_deleting', _l('project_lowercase')));
                redirect(admin_url('projects/view/' . $project_id));
            }
        }
    }

    public function copy($project_id)
    {
        if (has_permission('projects', '', 'create')) {
            $id = $this->projects_model->copy($project_id, $this->input->post());
            if ($id) {
                set_alert('success', _l('project_copied_successfully'));
                redirect(admin_url('projects/view/' . $id));
            } else {
                set_alert('danger', _l('failed_to_copy_project'));
                redirect(admin_url('projects/view/' . $project_id));
            }
        }
    }

    public function mass_stop_timers($project_id, $billable = 'false')
    {
        if (has_permission('invoices', '', 'create')) {
            $where = array(
                'billed' => 0,
                'startdate <=' => date('Y-m-d'),
            );
            if ($billable == 'true') {
                $where['billable'] = true;
            }
            $tasks                = $this->projects_model->get_tasks($project_id, $where);
            $total_timers_stopped = 0;
            foreach ($tasks as $task) {
                $this->db->where('task_id', $task['id']);
                $this->db->where('end_time IS NULL');
                $this->db->update('tbltaskstimers', array(
                    'end_time' => time(),
                ));
                $total_timers_stopped += $this->db->affected_rows();
            }
            $message = _l('project_tasks_total_timers_stopped', $total_timers_stopped);
            $type    = 'success';
            if ($total_timers_stopped == 0) {
                $type = 'warning';
            }
            echo json_encode(array(
                'type' => $type,
                'message' => $message,
            ));
        }
    }

    public function get_pre_invoice_project_info($project_id)
    {
        if (has_permission('invoices', '', 'create')) {
            $data['billable_tasks']     = $this->projects_model->get_tasks($project_id, array(
                'billable' => 1,
                'billed' => 0,
                'startdate <=' => date('Y-m-d'),
            ));

            $data['not_billable_tasks'] = $this->projects_model->get_tasks($project_id, array(
                'billable' => 1,
                'billed' => 0,
                'startdate >' => date('Y-m-d'),
            ));

            $data['project_id']         = $project_id;
            $data['billing_type']       = get_project_billing_type($project_id);

            $this->load->model('expenses_model');
            $this->db->where('invoiceid IS NULL');
            $data['expenses'] = $this->expenses_model->get('', array(
                'project_id' => $project_id,
                'billable' => 1,
            ));

            $this->load->view('admin/projects/project_pre_invoice_settings', $data);
        }
    }

    public function get_invoice_project_data()
    {
        if (has_permission('invoices', '', 'create')) {
            $type       = $this->input->post('type');
            $project_id = $this->input->post('project_id');
            // Check for all cases
            if ($type == '') {
                $type == 'single_line';
            }
            $this->load->model('payment_modes_model');
            $data['payment_modes'] = $this->payment_modes_model->get('', array(
                'expenses_only !=' => 1,
            ));
            $this->load->model('taxes_model');
            $data['taxes']      = $this->taxes_model->get();
            $data['currencies'] = $this->currencies_model->get();
            $data['base_currency'] = $this->currencies_model->get_base_currency();
            $this->load->model('invoice_items_model');

            $data['ajaxItems'] = false;
            if (total_rows('tblitems') <= ajax_on_total_items()) {
                $data['items']        = $this->invoice_items_model->get_grouped();
            } else {
                $data['items'] = array();
                $data['ajaxItems'] = true;
            }

            $data['items_groups'] = $this->invoice_items_model->get_groups();
            $data['staff']    = $this->staff_model->get('', 1);
            $project          = $this->projects_model->get($project_id);
            $data['project']  = $project;
            $items            = array();

            $project         = $this->projects_model->get($project_id);
            $item['id']      = 0;

            $default_tax      = unserialize(get_option('default_tax'));
            $item['taxname'] = $default_tax;

            $tasks           = $this->input->post('tasks');
            if ($tasks) {
                $item['long_description'] = '';
                $item['qty']              = 0;
                $item['task_id']          = array();
                if ($type == 'single_line') {
                    $item['description'] = $project->name;
                    foreach ($tasks as $task_id) {
                        $task = $this->tasks_model->get($task_id);
                        $sec = $this->tasks_model->calc_task_total_time($task_id);
                        $item['long_description'] .= $task->name . ' - ' . seconds_to_time_format($sec) . ' ' . _l('hours') . "\r\n";
                        $item['task_id'][] = $task_id;
                        if ($project->billing_type == 2) {
                            if ($sec < 60) {
                                $sec = 0;
                            }
                            $item['qty'] += sec2qty($sec);
                        }
                    }
                    if ($project->billing_type == 1) {
                        $item['qty']  = 1;
                        $item['rate'] = $project->project_cost;
                    } elseif ($project->billing_type == 2) {
                        $item['rate'] = $project->project_rate_per_hour;
                    }
                    $item['unit'] = '';
                    $items[]      = $item;
                } elseif ($type == 'task_per_item') {
                    foreach ($tasks as $task_id) {
                        $task                     = $this->tasks_model->get($task_id);
                        $sec = $this->tasks_model->calc_task_total_time($task_id);
                        $item['description']      = $project->name . ' - ' . $task->name;
                        $item['qty']              = floatVal(sec2qty($sec));
                        $item['long_description'] = seconds_to_time_format($sec) . ' ' . _l('hours');
                        if ($project->billing_type == 2) {
                            $item['rate'] = $project->project_rate_per_hour;
                        } elseif ($project->billing_type == 3) {
                            $item['rate'] = $task->hourly_rate;
                        }
                        $item['task_id'] = $task_id;
                        $item['unit']    = '';
                        $items[]         = $item;
                    }
                } elseif ($type == 'timesheets_individualy') {
                    $timesheets     = $this->projects_model->get_timesheets($project_id, $tasks);
                    $added_task_ids = array();
                    foreach ($timesheets as $timesheet) {
                        if ($timesheet['task_data']->billed == 0 && $timesheet['task_data']->billable == 1) {
                            $item['description'] = $project->name . ' - ' . $timesheet['task_data']->name;
                            if (!in_array($timesheet['task_id'], $added_task_ids)) {
                                $item['task_id'] = $timesheet['task_id'];
                            }

                            array_push($added_task_ids, $timesheet['task_id']);

                            $item['qty']              = floatVal(sec2qty($timesheet['total_spent']));
                            $item['long_description'] = _l('project_invoice_timesheet_start_time', _dt($timesheet['start_time'], true)) . "\r\n" . _l('project_invoice_timesheet_end_time', _dt($timesheet['end_time'], true)) . "\r\n" . _l('project_invoice_timesheet_total_logged_time', seconds_to_time_format($timesheet['total_spent'])) . ' ' . _l('hours');

                            if ($this->input->post('timesheets_include_notes') && $timesheet['note']) {
                                $item['long_description'] .= "\r\n\r\n" . _l('note').': ' . $timesheet['note'];
                            }

                            if ($project->billing_type == 2) {
                                $item['rate'] = $project->project_rate_per_hour;
                            } elseif ($project->billing_type == 3) {
                                $item['rate'] = $timesheet['task_data']->hourly_rate;
                            }
                            $item['unit'] = '';
                            $items[]      = $item;
                        }
                    }
                }
            }
            if ($project->billing_type != 1) {
                $data['hours_quantity'] = true;
            }
            if ($this->input->post('expenses')) {
                if (isset($data['hours_quantity'])) {
                    unset($data['hours_quantity']);
                }
                if (count($tasks) > 0) {
                    $data['qty_hrs_quantity'] = true;
                }
                $expenses = $this->input->post('expenses');
                $addExpenseNote = $this->input->post('expenses_add_note');
                $addExpenseName = $this->input->post('expenses_add_name');

                if (!$addExpenseNote) {
                    $addExpenseNote = array();
                }

                if (!$addExpenseName) {
                    $addExpenseName = array();
                }

                $this->load->model('expenses_model');
                foreach ($expenses as $expense_id) {
                    // reset item array
                    $item                     = array();
                    $item['id']               = 0;
                    $expense                  = $this->expenses_model->get($expense_id);
                    $item['expense_id']       = $expense->expenseid;
                    $item['description']      = _l('item_as_expense') . ' ' . $expense->name;
                    $item['long_description'] = $expense->description;

                    if (in_array($expense_id, $addExpenseNote) && !empty($expense->note)) {
                        $item['long_description'] .= PHP_EOL . $expense->note;
                    }

                    if (in_array($expense_id, $addExpenseName) && !empty($expense->expense_name)) {
                        $item['long_description'] .= PHP_EOL . $expense->expense_name;
                    }

                    $item['qty']              = 1;

                    $item['taxname'] = array();
                    if ($expense->tax != 0) {
                        array_push($item['taxname'], $expense->tax_name . '|' . $expense->taxrate);
                    }
                    if ($expense->tax2 != 0) {
                        array_push($item['taxname'], $expense->tax_name2 . '|' . $expense->taxrate2);
                    }
                    $item['rate']  = $expense->amount;
                    $item['order'] = 1;
                    $item['unit']  = '';
                    $items[]       = $item;
                }
            }
            $data['customer_id']          = $project->clientid;
            $data['invoice_from_project'] = true;
            $data['add_items']            = $items;
            $this->load->view('admin/projects/invoice_project', $data);
        }
    }

    public function get_rel_project_data($id, $task_id = '')
    {
        if ($this->input->is_ajax_request()) {
            $selected_milestone = '';
            if ($task_id != '' && $task_id != 'undefined') {
                $task               = $this->tasks_model->get($task_id);
                $selected_milestone = $task->milestone;
            }

            $allow_to_view_tasks = 0;
            $this->db->where('project_id', $id);
            $this->db->where('name', 'view_tasks');
            $project_settings = $this->db->get('tblprojectsettings')->row();
            if ($project_settings) {
                $allow_to_view_tasks = $project_settings->value;
            }

            echo json_encode(array(
                'allow_to_view_tasks' => $allow_to_view_tasks,
                'billing_type' => get_project_billing_type($id),
                'milestones' => render_select('milestone', $this->projects_model->get_milestones($id), array(
                    'id',
                    'name',
                ), 'task_milestone', $selected_milestone),
            ));
        }
    }

    public function invoice_project($project_id)
    {
        if (has_permission('invoices', '', 'create')) {
            $this->load->model('invoices_model');
            $data               = $this->input->post();
            $data['project_id'] = $project_id;
            $invoice_id         = $this->invoices_model->add($data);
            if ($invoice_id) {
                $this->projects_model->log_activity($project_id, 'project_activity_invoiced_project', format_invoice_number($invoice_id));
                set_alert('success', _l('project_invoiced_successfully'));
            }
            redirect(admin_url('projects/view/' . $project_id . '?group=project_invoices'));
        }
    }

    public function view_project_as_client($id, $clientid)
    {
        if (is_admin()) {
            login_as_client($clientid);
            redirect(site_url('clients/project/' . $id));
        }
    }
}
